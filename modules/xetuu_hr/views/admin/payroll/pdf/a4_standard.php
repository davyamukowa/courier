<?php
defined('BASEPATH') or exit('No direct script access allowed');
// $pdf, $payslip, $lines, $contract are available via extract($view_vars) + $pdf = $this in build()

/* ── Safe date helper ── */
function _ps_date($v, $fmt = 'd/m/Y', $fb = '—') {
    if (empty($v)) return $fb;
    $ts = strtotime($v);
    return ($ts && $ts > strtotime('1900-01-01')) ? date($fmt, $ts) : $fb;
}

/* ── Company info ── */
$company_name  = get_option('invoice_company_name') ?: '';
$company_addr  = get_option('invoice_company_address') ?: '';
$company_city  = get_option('invoice_company_city') ?: '';
$company_zip   = get_option('invoice_company_postal_code') ?: '';
$company_phone = get_option('invoice_company_phonenumber') ?: '';
$company_email = get_option('invoice_company_email') ?: '';
$company_web   = get_option('invoice_company_website') ?: '';
$currency      = get_option('default_currency') ?: 'KSh';

$period       = _ps_date($payslip->date_from, 'F Y');
$period_from  = _ps_date($payslip->date_from, 'd/m/Y');
$period_to    = _ps_date($payslip->date_to, 'd/m/Y');
$computed_on  = _ps_date($payslip->updated_at ?? $payslip->date_created ?? null, 'd/m/Y');
$contract_start = _ps_date($contract->date_start ?? null, 'd/m/Y');

/* ── Parse lines — skip zeros ── */
$earn_lines = $ded_pre = $ded_post = $tax_lines = [];
$basic_wage = $gross = $net = 0.0;
foreach ($lines as $ln) {
    if (!($ln['appears_on_payslip'] ?? 1)) continue;
    $amt = (float)$ln['amount'];
    if (abs($amt) < 0.001) continue;
    switch ($ln['category']) {
        case 'EARN':
            $earn_lines[] = $ln; $gross += $amt;
            if ($ln['rule_code'] === 'BASIC') $basic_wage = $amt;
            break;
        case 'DED':
            if ((int)($ln['sequence'] ?? 999) < 200) $ded_pre[]  = $ln;
            else                                      $ded_post[] = $ln;
            break;
        case 'TAX':  $tax_lines[] = $ln; break;
        case 'NET':  $net = $amt;        break;
    }
}
$total_pre   = array_sum(array_column($ded_pre,   'amount'));
$total_post  = array_sum(array_column($ded_post,  'amount'));
$total_tax   = array_sum(array_column($tax_lines, 'amount'));
$total_ded   = $total_pre + $total_post + $total_tax;
$taxable     = $gross - $total_pre;
$net         = $net ?: ($gross - $total_ded);
$worked_days = (float)($payslip->worked_days ?: 0);
$worked_hrs  = $worked_days * 8;

function _ps_money($v, $c) { return $c . ' ' . number_format((float)$v, 2); }
$C = $currency;

/* ── Logo ── */
$logo = pdf_logo_url(); // returns an <img> tag or empty string

/* ── Build HTML string (no echo — will be passed to $pdf->writeHTML()) ── */
$html  = '<style>';
$html .= 'table { border-collapse: collapse; width: 100%; }';
$html .= 'td, th { font-size: 9pt; font-family: Helvetica, Arial, sans-serif; padding: 4px 7px; }';
$html .= '.no-border td { border: none; padding: 2px 0; }';
$html .= '.info-tbl td { border: 1px solid #d1d5db; padding: 5px 8px; }';
$html .= '.lbl { font-size: 7.5pt; color: #6b7280; font-weight: bold; display: block; }';
$html .= '.val { font-size: 9pt; color: #111827; }';
$html .= '.basic-row td { background-color: #eff6ff; border: 1px solid #bfdbfe; }';
$html .= '.col-hdr th { background-color: #1e3a5f; color: #ffffff; font-size: 8pt; font-weight: bold; padding: 6px 8px; border: none; text-align: center; }';
$html .= '.lines-tbl td { border-bottom: 1px solid #f3f4f6; }';
$html .= '.cat-earn td { background-color: #f0fdf4; color: #15803d; font-size: 7.5pt; font-weight: bold; padding: 3px 8px; }';
$html .= '.cat-ded  td { background-color: #fff7ed; color: #c2410c; font-size: 7.5pt; font-weight: bold; padding: 3px 8px; }';
$html .= '.cat-tax  td { background-color: #f5f3ff; color: #7c3aed; font-size: 7.5pt; font-weight: bold; padding: 3px 8px; }';
$html .= '.earn-amt { color: #15803d; text-align: right; }';
$html .= '.ded-amt  { color: #c2410c; text-align: right; }';
$html .= '.tax-amt  { color: #7c3aed; text-align: right; }';
$html .= '.subtotal-row td { background-color: #f9fafb; font-weight: bold; border-top: 1px solid #9ca3af; border-bottom: 1px solid #9ca3af; }';
$html .= '.total-row td { background-color: #fef2f2; color: #991b1b; font-weight: bold; border-top: 2px solid #dc2626; }';
$html .= '.net-row td { background-color: #1e3a5f; color: #ffffff; font-size: 11pt; font-weight: bold; padding: 9px 10px; }';
$html .= '.right { text-align: right; } .center { text-align: center; } .gray { color: #9ca3af; }';
$html .= '</style>';

/* ── Header: logo left, PAYSLIP right ── */
$company_right  = '<span style="font-size:16pt; font-weight:bold; color:#1e3a5f;">PAYSLIP</span><br>';
$company_right .= '<span style="font-size:10pt; color:#374151;">' . htmlspecialchars($period) . '</span><br>';
$company_right .= '<span style="font-size:8.5pt; color:#6b7280;">' . $period_from . ' &ndash; ' . $period_to . '</span>';
if ($company_phone) $company_right .= '<br><span style="font-size:8pt; color:#9ca3af;">' . htmlspecialchars($company_phone) . '</span>';

$company_left  = $logo ? $logo . '<br>' : '';
$company_left .= '<b style="font-size:10.5pt;">' . htmlspecialchars($company_name) . '</b>';
if ($company_addr) $company_left .= '<br><span style="font-size:8pt; color:#6b7280;">' . htmlspecialchars($company_addr) . '</span>';
if ($company_city) $company_left .= '<br><span style="font-size:8pt; color:#6b7280;">' . htmlspecialchars($company_city . ($company_zip ? ' ' . $company_zip : '')) . '</span>';

$html .= '<table class="no-border" style="margin-bottom:10px;">';
$html .= '<tr>';
$html .= '<td width="50%">' . $company_left . '</td>';
$html .= '<td width="50%" style="text-align:right; vertical-align:top; border:none;">' . $company_right . '</td>';
$html .= '</tr></table>';

/* ── Employee / Contract info grid ── */
$tax_id_pdf = $contract->tax_id ?? $payslip->employee_tax_id ?? '';
$html .= '<table class="info-tbl" style="margin-bottom:8px;">';
$html .= '<tr>';
$html .= '<td width="30%"><span class="lbl">EMPLOYEE</span><span class="val"><b>' . htmlspecialchars($payslip->employee_name ?? '') . '</b></span></td>';
$html .= '<td width="25%"><span class="lbl">DESIGNATION</span><span class="val">' . htmlspecialchars($contract->job_title ?? '&mdash;') . '</span></td>';
$html .= '<td width="20%"><span class="lbl">CONTRACT START</span><span class="val">' . $contract_start . '</span></td>';
$html .= '<td width="25%"><span class="lbl">PAY PERIOD</span><span class="val">' . $period_from . ' &ndash; ' . $period_to . '</span></td>';
$html .= '</tr><tr>';
$html .= '<td><span class="lbl">EMAIL</span><span class="val gray">' . htmlspecialchars($contract->employee_email ?? '') . '</span></td>';
$html .= '<td><span class="lbl">ID NUMBER</span><span class="val">' . htmlspecialchars($contract->id_number ?? '&mdash;') . '</span></td>';
$html .= '<td><span class="lbl">CONTRACT TYPE</span><span class="val">' . htmlspecialchars($contract->contract_type ?? 'Permanent') . '</span></td>';
$html .= '<td><span class="lbl">COMPUTED ON</span><span class="val">' . $computed_on . '</span></td>';
$html .= '</tr></table>';

/* ── Statutory / Compliance row ── */
$stat_parts = [];
if (!empty($payslip->social_sec_number))  $stat_parts[] = '<b>NSSF#:</b> ' . htmlspecialchars($payslip->social_sec_number);
if (!empty($payslip->health_fund_number)) $stat_parts[] = '<b>SHA#:</b> '  . htmlspecialchars($payslip->health_fund_number);
if (!empty($tax_id_pdf))                  $stat_parts[] = '<b>KRA PIN:</b> '  . htmlspecialchars($tax_id_pdf);
if (!empty($payslip->passport_number))    $stat_parts[] = '<b>Passport:</b> '  . htmlspecialchars($payslip->passport_number)
    . ($payslip->passport_expiry ? ' (exp. ' . _ps_date($payslip->passport_expiry, 'd/m/Y') . ')' : '');
if ($stat_parts) {
    $html .= '<table style="margin-bottom:8px;"><tr><td style="font-size:7.5pt; color:#6b7280; background:#f9fafb; border:1px solid #e5e7eb; padding:4px 8px;">';
    $html .= implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $stat_parts);
    $html .= '</td></tr></table>';
}

/* ── Basic Salary bar ── */
$html .= '<table class="basic-row" style="margin-bottom:10px;">';
$html .= '<tr>';
$html .= '<td width="20%"><b style="color:#1e40af;">Basic Salary</b></td>';
$html .= '<td style="text-align:right;"><b style="color:#1e40af;">' . _ps_money($basic_wage, $C) . '</b></td>';
$html .= '</tr></table>';

/* ── Lines table ── */
$html .= '<table class="lines-tbl">';
// Header
$html .= '<tr class="col-hdr"><th width="55%">DESCRIPTION</th><th width="15%" style="text-align:center;">HOURS</th><th width="15%" style="text-align:center;">DAYS</th><th width="15%" style="text-align:right; padding-right:8px;">AMOUNT</th></tr>';

// Attendance row
if ($worked_days > 0) {
    $html .= '<tr class="cat-earn"><td colspan="4">ATTENDANCE</td></tr>';
    $html .= '<tr class="lines-tbl">';
    $html .= '<td style="color:#15803d;">Work Attendance</td>';
    $html .= '<td class="center" style="color:#15803d;">' . number_format($worked_hrs, 1) . '</td>';
    $html .= '<td class="center" style="color:#15803d;">' . number_format($worked_days, 1) . '</td>';
    $html .= '<td class="earn-amt">' . _ps_money($basic_wage, $C) . '</td>';
    $html .= '</tr>';
}

// Earnings
if ($earn_lines) {
    $html .= '<tr class="cat-earn"><td colspan="4">EARNINGS</td></tr>';
    foreach ($earn_lines as $ln) {
        $html .= '<tr class="lines-tbl">';
        $html .= '<td style="color:#15803d;">' . htmlspecialchars($ln['rule_name']) . '</td>';
        $html .= '<td></td><td></td>';
        $html .= '<td class="earn-amt">' . _ps_money($ln['amount'], $C) . '</td>';
        $html .= '</tr>';
    }
    $html .= '<tr class="subtotal-row">';
    $html .= '<td colspan="3"><b>Gross Pay</b></td>';
    $html .= '<td style="text-align:right; color:#15803d;"><b>' . _ps_money($gross, $C) . '</b></td>';
    $html .= '</tr>';
}

// Pre-tax deductions
if ($ded_pre) {
    $html .= '<tr class="cat-ded"><td colspan="4">STATUTORY DEDUCTIONS</td></tr>';
    foreach ($ded_pre as $ln) {
        $html .= '<tr class="lines-tbl">';
        $html .= '<td>' . htmlspecialchars($ln['rule_name']) . '</td>';
        $html .= '<td></td><td></td>';
        $html .= '<td class="ded-amt">' . _ps_money($ln['amount'], $C) . '</td>';
        $html .= '</tr>';
    }
    if ($tax_lines) {
        $html .= '<tr class="lines-tbl">';
        $html .= '<td style="font-size:8pt; color:#6b7280;">Gross Taxable</td>';
        $html .= '<td></td><td></td>';
        $html .= '<td style="text-align:right; font-size:8pt; color:#6b7280;">' . _ps_money($taxable, $C) . '</td>';
        $html .= '</tr>';
    }
}

// Tax
if ($tax_lines) {
    $html .= '<tr class="cat-tax"><td colspan="4">INCOME TAX</td></tr>';
    foreach ($tax_lines as $ln) {
        $html .= '<tr class="lines-tbl">';
        $html .= '<td>' . htmlspecialchars($ln['rule_name']) . '</td>';
        $html .= '<td></td><td></td>';
        $html .= '<td class="tax-amt">' . _ps_money($ln['amount'], $C) . '</td>';
        $html .= '</tr>';
    }
}

// Post-tax deductions
if ($ded_post) {
    $html .= '<tr class="cat-ded"><td colspan="4">OTHER DEDUCTIONS</td></tr>';
    foreach ($ded_post as $ln) {
        $html .= '<tr class="lines-tbl">';
        $html .= '<td>' . htmlspecialchars($ln['rule_name']) . '</td>';
        $html .= '<td></td><td></td>';
        $html .= '<td class="ded-amt">' . _ps_money($ln['amount'], $C) . '</td>';
        $html .= '</tr>';
    }
}

// Total Deductions
$html .= '<tr class="total-row">';
$html .= '<td colspan="3"><b>Total Deductions</b></td>';
$html .= '<td style="text-align:right; color:#991b1b;"><b>' . _ps_money($total_ded, $C) . '</b></td>';
$html .= '</tr>';

// Net Pay
$html .= '<tr class="net-row">';
$html .= '<td colspan="3"><b>NET PAY</b></td>';
$html .= '<td style="text-align:right; font-size:13pt;"><b>' . _ps_money($net, $C) . '</b></td>';
$html .= '</tr>';
$html .= '</table>';

// Payment / notes
$bank  = $contract->bank_account ?? $payslip->bank_account ?? '';
$bname = $contract->bank_name ?? '';
if ($bank) {
    $html .= '<p style="font-size:8.5pt; color:#374151; margin-top:8px;">';
    $html .= '<b>Pay to:</b> ';
    if ($bname) $html .= htmlspecialchars($bname) . ' &mdash; ';
    $html .= htmlspecialchars($bank);
    $html .= '&nbsp;&nbsp;|&nbsp;&nbsp;<b>Amount:</b> ' . _ps_money($net, $C);
    $html .= '</p>';
}
if (!empty($payslip->notes)) {
    $html .= '<p style="font-size:8pt; color:#6b7280; font-style:italic;">' . htmlspecialchars($payslip->notes) . '</p>';
}

// Footer
$footer_parts = array_filter([$company_name, $company_addr, $company_city, $company_email, $company_web]);
$html .= '<p style="font-size:7.5pt; color:#9ca3af; text-align:center; border-top:1px solid #e5e7eb; padding-top:5px; margin-top:8px;">';
$html .= htmlspecialchars(implode(' · ', $footer_parts));
$html .= '</p>';

/* ── Write to PDF ── */
$pdf->writeHTML($html, true, false, true, false, '');
