<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kenya Payroll Addon
 * ───────────────────
 * Provides Kenyan statutory computation by injecting rate variables into the
 * salary rule formula context. All actual computation happens through the
 * "Kenya: Regular Pay" salary structure (seeded on install) whose rules are
 * visible and editable in Payroll → Config → Salary Structures.
 *
 * Rate variables injected into every formula evaluation:
 *   NSSF_RATE        — 6 (% of pensionable pay)
 *   NSSF_UEL         — 36000 (Upper Earnings Limit, KES)
 *   SHIF_RATE        — 2.75 (% of gross)
 *   SHIF_MIN         — 300 (floor, KES)
 *   AHL_RATE         — 1.5 (% of gross — employee; employer matches)
 *   PERSONAL_RELIEF  — 2400 (KES/month)
 *   PAYE_BANDS       — JSON of graduated KRA bands (2024/25)
 *
 * The graduated_paye(taxable, bands_json) function is registered in Payroll_engine.
 *
 * Computation order (Tax Laws (Amendment) Act 2024):
 *   gross    = sum of EARN lines
 *   NSSF     = 6% × min(gross, 36 000)         → deductible
 *   SHIF     = max(2.75% × gross, 300)          → deductible
 *   AHL      = 1.5% × gross                     → deductible
 *   taxable  = gross − NSSF − SHIF − AHL
 *   PAYE     = graduated_paye(taxable) − 2 400  → floor 0
 */
class KenyaPayrollAddon
{
    const ADDON_ID = 'kenya_payroll';

    /** Wire up hooks/filters. Called once per request by the addon bootstrapper. */
    public static function register($addon = null)
    {
        // Inject live rate variables into every salary rule formula evaluation
        hooks()->add_filter('payroll_engine_context',       [__CLASS__, 'inject_context'], 10, 3);

        // Rename generic company statutory fields to Kenya-specific labels
        hooks()->add_filter('payroll_company_field_labels', [__CLASS__, 'company_field_labels']);

        // Inject Kenya-specific report cards into the Reports page
        hooks()->add_filter('payroll_report_cards',         [__CLASS__, 'report_cards']);

        // Handle CSV download for each statutory report type
        foreach (['p9a','p10','nssf_schedule','sha_schedule','housing_levy'] as $r) {
            hooks()->add_filter('payroll_download_report_' . $r,     [__CLASS__, 'download_'     . $r]);
            hooks()->add_filter('payroll_render_report_html_' . $r,  [__CLASS__, 'preview_html_' . $r]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FORMULA CONTEXT INJECTION  (filter: payroll_engine_context)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Overwrite the engine's default rate variables with values from the addon
     * settings table, so changes made in Payroll → Settings take effect immediately.
     */
    public static function inject_context($ctx, $slip, $contract)
    {
        $ctx['NSSF_RATE']       = (float) self::setting('nssf_rate',         6);
        $ctx['NSSF_UEL']        = (float) self::setting('nssf_tier2_limit',  36000);
        $ctx['SHIF_RATE']       = (float) self::setting('shif_rate',         2.75);
        $ctx['SHIF_MIN']        = (float) self::setting('shif_min',          300);
        $ctx['AHL_RATE']        = (float) self::setting('housing_levy_rate', 1.5);
        $ctx['PERSONAL_RELIEF'] = (float) self::setting('personal_relief',   2400);

        $bands_json = self::setting('paye_bands', null);
        if ($bands_json) {
            $ctx['PAYE_BANDS'] = $bands_json;
        }

        return $ctx;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UI INJECTION  (filters)
    // ══════════════════════════════════════════════════════════════════════════

    public static function company_field_labels($labels)
    {
        $labels['tax_reg_number']     = 'KRA PIN';
        $labels['social_sec_number']  = 'NSSF Employer No.';
        $labels['health_fund_number'] = 'SHA Employer Code';
        return $labels;
    }

    public static function report_cards($cards)
    {
        $cards['p9a']           = ['P9A — Annual Tax Cert',  'Annual P9A certificates aggregating all payslips per employee',  'fa-file-text', '#dc2626'];
        $cards['p10']           = ['P10 — PAYE Return',      'Monthly PAYE return for upload to KRA iTax',                     'fa-file-text', '#dc2626'];
        $cards['nssf_schedule'] = ['NSSF Schedule',           'Monthly NSSF Tier I & II contributions schedule',               'fa-file-text', '#16a34a'];
        $cards['sha_schedule']  = ['SHA / SHIF Schedule',     'Monthly SHIF (2.75%) contributions schedule',                   'fa-file-text', '#0891b2'];
        $cards['housing_levy']  = ['Housing Levy Return',     'Affordable Housing Levy (1.5%) monthly return',                 'fa-home',      '#d97706'];
        return $cards;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATUTORY REPORTS  (filters: payroll_download_report_*)
    // ══════════════════════════════════════════════════════════════════════════

    public static function download_p10($handled = false)
    {
        $rows = self::statutory_rows();
        self::stream_csv('P10_PAYE_Return_' . date('Ym'),
            ['KRA PIN', 'Employee Name', 'Gross Pay', 'NSSF', 'SHIF', 'Housing Levy', 'Taxable Pay', 'PAYE'],
            array_map(function ($r) {
                $taxable = $r->gross - $r->nssf - $r->shif - $r->ahl;
                return [$r->kra_pin, $r->name, self::n($r->gross), self::n($r->nssf), self::n($r->shif), self::n($r->ahl), self::n($taxable), self::n($r->paye)];
            }, $rows),
            ['', 'TOTAL', null, null, null, null, null, null]);
        return true;
    }

    public static function download_nssf_schedule($handled = false)
    {
        $rows = self::statutory_rows();
        self::stream_csv('NSSF_Schedule_' . date('Ym'),
            ['NSSF No.', 'Employee Name', 'Gross Pay', 'Employee NSSF', 'Employer NSSF', 'Total NSSF'],
            array_map(function ($r) {
                return [$r->kra_pin, $r->name, self::n($r->gross), self::n($r->nssf), self::n($r->nssf), self::n($r->nssf * 2)];
            }, $rows));
        return true;
    }

    public static function download_sha_schedule($handled = false)
    {
        $rows = self::statutory_rows();
        self::stream_csv('SHA_SHIF_Schedule_' . date('Ym'),
            ['KRA PIN', 'Employee Name', 'Gross Pay', 'SHIF (2.75%)'],
            array_map(function ($r) {
                return [$r->kra_pin, $r->name, self::n($r->gross), self::n($r->shif)];
            }, $rows));
        return true;
    }

    public static function download_housing_levy($handled = false)
    {
        $rows = self::statutory_rows();
        self::stream_csv('Housing_Levy_Return_' . date('Ym'),
            ['KRA PIN', 'Employee Name', 'Gross Pay', 'Employee 1.5%', 'Employer 1.5%', 'Total Levy'],
            array_map(function ($r) {
                return [$r->kra_pin, $r->name, self::n($r->gross), self::n($r->ahl), self::n($r->ahl), self::n($r->ahl * 2)];
            }, $rows));
        return true;
    }

    public static function download_p9a($handled = false)
    {
        $year = (int)(get_instance()->input->get('year') ?: date('Y'));
        $rows = self::statutory_rows($year . '-01-01', $year . '-12-31');
        self::stream_csv('P9A_' . $year,
            ['KRA PIN', 'Employee Name', 'Total Gross', 'Total NSSF', 'Total SHIF', 'Total Housing Levy', 'Total Taxable', 'Total PAYE'],
            array_map(function ($r) {
                $taxable = $r->gross - $r->nssf - $r->shif - $r->ahl;
                return [$r->kra_pin, $r->name, self::n($r->gross), self::n($r->nssf), self::n($r->shif), self::n($r->ahl), self::n($taxable), self::n($r->paye)];
            }, $rows),
            ['', 'TOTAL', null, null, null, null, null, null]);
        return true;
    }

    // ── Aggregate statutory amounts per employee for a period ──────────────────
    private static function statutory_rows($from = null, $to = null)
    {
        $CI = get_instance();
        $p  = db_prefix();
        $from = $from ?: ($CI->input->get('date_from') ?: date('Y-m-01'));
        $to   = $to   ?: ($CI->input->get('date_to')   ?: date('Y-m-t'));
        $cid  = (int)$CI->input->get('company_id');

        $CI->db->select("e.id AS emp_id,
                         CONCAT(e.first_name,' ',e.last_name) AS name,
                         con.tax_id AS kra_pin,
                         SUM(CASE WHEN l.category='EARN'       THEN l.amount ELSE 0 END) AS gross,
                         SUM(CASE WHEN l.rule_code='NSSF'      THEN l.amount ELSE 0 END) AS nssf,
                         SUM(CASE WHEN l.rule_code='SHIF'      THEN l.amount ELSE 0 END) AS shif,
                         SUM(CASE WHEN l.rule_code='AHL'       THEN l.amount ELSE 0 END) AS ahl,
                         SUM(CASE WHEN l.rule_code='PAYE'      THEN l.amount ELSE 0 END) AS paye", false)
            ->from($p . 'hr_payslips ps')
            ->join($p . 'hr_employees e',          'e.id = ps.employee_id')
            ->join($p . 'hr_payroll_contracts con', 'con.id = ps.contract_id', 'left')
            ->join($p . 'hr_payslip_lines l',       'l.payslip_id = ps.id', 'left')
            ->where('ps.date_from >=', $from)
            ->where('ps.date_to <=',   $to)
            ->where('ps.state !=',     'draft')
            ->group_by('e.id');
        if ($cid) $CI->db->where('ps.company_id', $cid);

        return $CI->db->order_by('name')->get()->result();
    }

    private static function stream_csv($filename, $header, $rows, $total_label = null)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $header);

        $sums = array_fill(0, count($header), 0);
        foreach ($rows as $r) {
            fputcsv($out, $r);
            foreach ($r as $i => $v) {
                if (is_numeric(str_replace(',', '', (string)$v))) {
                    $sums[$i] += (float)str_replace(',', '', (string)$v);
                }
            }
        }
        if ($total_label) {
            $footer = [];
            foreach ($header as $i => $_) {
                if (isset($total_label[$i]) && $total_label[$i] !== null) {
                    $footer[$i] = $total_label[$i];
                } else {
                    $footer[$i] = ($i >= 2) ? self::n($sums[$i]) : '';
                }
            }
            fputcsv($out, $footer);
        }
        fclose($out);
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATUTORY REPORT HTML PREVIEWS  (filters: payroll_render_report_html_*)
    // ══════════════════════════════════════════════════════════════════════════

    public static function preview_html_p9a($handled, $ctx = [])
    {
        $year = date('Y');
        $rows = self::statutory_rows($year.'-01-01', $year.'-12-31');
        self::_statutory_html(
            'P9A — Annual Tax Certificates ('.$year.')',
            ['KRA PIN','Employee','Total Gross','NSSF','SHIF','Housing Levy','Taxable Pay','PAYE'],
            array_map(function($r) {
                $taxable = $r->gross - $r->nssf - $r->shif - $r->ahl;
                return [htmlspecialchars($r->kra_pin ?? '—'), htmlspecialchars($r->name),
                        self::n($r->gross), self::n($r->nssf), self::n($r->shif),
                        self::n($r->ahl), self::n($taxable), '<strong>'.self::n($r->paye).'</strong>'];
            }, $rows)
        );
        return true;
    }

    public static function preview_html_p10($handled, $ctx = [])
    {
        $rows = self::statutory_rows();
        self::_statutory_html(
            'P10 — PAYE Return',
            ['KRA PIN','Employee','Gross Pay','NSSF','SHIF','Housing Levy','Taxable Pay','PAYE'],
            array_map(function($r) {
                $taxable = $r->gross - $r->nssf - $r->shif - $r->ahl;
                return [htmlspecialchars($r->kra_pin ?? '—'), htmlspecialchars($r->name),
                        self::n($r->gross), self::n($r->nssf), self::n($r->shif),
                        self::n($r->ahl), self::n($taxable), '<strong>'.self::n($r->paye).'</strong>'];
            }, $rows)
        );
        return true;
    }

    public static function preview_html_nssf_schedule($handled, $ctx = [])
    {
        $rows = self::statutory_rows();
        self::_statutory_html(
            'NSSF Schedule — Tier I & II Contributions',
            ['NSSF No.','Employee','Gross Pay','Employee NSSF','Employer NSSF','Total NSSF'],
            array_map(function($r) {
                return [htmlspecialchars($r->kra_pin ?? '—'), htmlspecialchars($r->name),
                        self::n($r->gross), self::n($r->nssf), self::n($r->nssf),
                        '<strong>'.self::n($r->nssf * 2).'</strong>'];
            }, $rows)
        );
        return true;
    }

    public static function preview_html_sha_schedule($handled, $ctx = [])
    {
        $rows = self::statutory_rows();
        self::_statutory_html(
            'SHA / SHIF Schedule — 2.75% Contributions',
            ['KRA PIN','Employee','Gross Pay','SHIF (2.75%)'],
            array_map(function($r) {
                return [htmlspecialchars($r->kra_pin ?? '—'), htmlspecialchars($r->name),
                        self::n($r->gross), '<strong>'.self::n($r->shif).'</strong>'];
            }, $rows)
        );
        return true;
    }

    public static function preview_html_housing_levy($handled, $ctx = [])
    {
        $rows = self::statutory_rows();
        self::_statutory_html(
            'Affordable Housing Levy — 1.5% Return',
            ['KRA PIN','Employee','Gross Pay','Employee 1.5%','Employer 1.5%','Total Levy'],
            array_map(function($r) {
                return [htmlspecialchars($r->kra_pin ?? '—'), htmlspecialchars($r->name),
                        self::n($r->gross), self::n($r->ahl), self::n($r->ahl),
                        '<strong>'.self::n($r->ahl * 2).'</strong>'];
            }, $rows)
        );
        return true;
    }

    private static function _statutory_html($title, $headers, $rows)
    {
        $numeric_from = 2; // columns 0-1 are text (PIN + Name), rest are numeric
        $ncols = count($headers);
        $sums  = array_fill(0, $ncols, 0);

        $html  = '<div style="font-size:11px;color:#6b7280;margin-bottom:8px;">'.htmlspecialchars($title).'</div>';
        $html .= '<div style="overflow-x:auto;"><table class="table table-condensed table-bordered" style="font-size:12px;margin:0;">';
        $html .= '<thead><tr style="background:#f9fafb;">';
        foreach ($headers as $h) {
            $html .= '<th style="font-size:11px;text-transform:uppercase;color:#6b7280;white-space:nowrap;padding:8px 10px;">'.htmlspecialchars($h).'</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (array_values($row) as $i => $cell) {
                $a = $i >= $numeric_from ? 'text-align:right;' : '';
                $plain = strip_tags((string)$cell);
                if ($i >= $numeric_from && is_numeric(str_replace(',', '', $plain))) {
                    $sums[$i] += (float)str_replace(',', '', $plain);
                }
                $html .= '<td style="padding:7px 10px;'.$a.'">'.$cell.'</td>';
            }
            $html .= '</tr>';
        }

        if (!empty($rows)) {
            $html .= '<tr style="background:#f0fdf4;font-weight:700;">';
            $html .= '<td colspan="2" style="padding:7px 10px;">TOTAL ('.count($rows).')</td>';
            for ($i = 2; $i < $ncols; $i++) {
                $html .= '<td style="padding:7px 10px;text-align:right;">'.number_format($sums[$i], 2).'</td>';
            }
            $html .= '</tr>';
        } else {
            $html .= '<tr><td colspan="'.$ncols.'" style="padding:30px;text-align:center;color:#9ca3af;">No confirmed payslips found for the selected period.</td></tr>';
        }

        $html .= '</tbody></table></div>';
        echo $html;
    }

    // ── Setting reader with fallback default ───────────────────────────────────
    private static function setting($key, $default)
    {
        $CI = get_instance();
        $CI->load->model('xetuu_hr/Xr_payroll_model', 'payroll_mdl');
        $v = $CI->payroll_mdl->get_addon_setting(self::ADDON_ID, $key, 0, null);
        return ($v === null || $v === '') ? $default : $v;
    }

    private static function n($v)
    {
        return number_format((float)$v, 2, '.', '');
    }
}
