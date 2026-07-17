<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * ArrayAccess wrapper that returns 0.0 for any undefined key.
 * Used for `benefit` and `deduction` context variables in salary rule formulas
 * so that old/unknown keys never produce PHP 8 undefined-key warnings.
 */
class SafeContextArray implements ArrayAccess
{
    private $data;
    public function __construct(array $data) { $this->data = $data; }
    public function offsetExists($offset): bool  { return true; }
    public function offsetGet($offset): mixed    { return $this->data[$offset] ?? 0.0; }
    public function offsetSet($offset, $value): void { $this->data[$offset] = $value; }
    public function offsetUnset($offset): void   { unset($this->data[$offset]); }
}

class Payroll_engine
{
    private $CI;
    private $p;
    private $expr;

    public function __construct()
    {
        $this->CI   = &get_instance();
        $this->p    = db_prefix();
        $this->expr = new ExpressionLanguage();
        $this->_register_custom_functions();
        $this->CI->load->model('xetuu_hr/Xr_payroll_model', 'payroll_mdl');
        $this->CI->load->model('xetuu_hr/Xr_loans_model',   'loans_mdl');
    }

    /**
     * Register custom functions available in salary rule formulas.
     *
     * graduated_paye(taxable, bands_json) — KRA-style progressive tax.
     *   bands_json: JSON array of {to, rate} objects; "to" = null means "and above".
     *   Falls back to 2024/25 KRA bands when bands_json is empty/invalid.
     */
    private function _register_custom_functions()
    {
        // Register common PHP math functions that formulas use
        $mathFuncs = [
            'round' => function($args, $val, $precision = 0) { return round((float)$val, (int)$precision); },
            'min'   => function($args, $a, $b) { return min((float)$a, (float)$b); },
            'max'   => function($args, $a, $b) { return max((float)$a, (float)$b); },
            'abs'   => function($args, $a) { return abs((float)$a); },
            'floor' => function($args, $a) { return floor((float)$a); },
            'ceil'  => function($args, $a) { return ceil((float)$a); },
        ];
        foreach ($mathFuncs as $name => $evaluator) {
            $this->expr->register($name, function() use ($name) { return $name.'()'; }, $evaluator);
        }

        $this->expr->register(
            'graduated_paye',
            function ($taxable, $bands) { return '0'; }, // compiler stub (not used)
            function ($args, $taxable, $bands_json) {
                $taxable = (float) $taxable;
                if ($taxable <= 0) return 0.0;
                $bands = is_string($bands_json) ? json_decode($bands_json, true) : (array) $bands_json;
                if (!is_array($bands) || empty($bands)) {
                    $bands = [
                        ['to' => 24000,  'rate' => 10],
                        ['to' => 32333,  'rate' => 25],
                        ['to' => 500000, 'rate' => 30],
                        ['to' => 800000, 'rate' => 32.5],
                        ['to' => null,   'rate' => 35],
                    ];
                }
                $tax = 0.0; $prev = 0.0;
                foreach ($bands as $b) {
                    $upper = ($b['to'] === null) ? PHP_INT_MAX : (float) $b['to'];
                    if ($taxable <= $prev) break;
                    $tax += (min($taxable, $upper) - $prev) * ((float) $b['rate'] / 100);
                    $prev = $upper;
                }
                return round($tax, 2);
            }
        );
    }

    // ── Compute a single payslip ───────────────────────────────────────────────
    public function compute_payslip($payslip_id)
    {
        $p    = $this->p;
        $slip = $this->CI->payroll_mdl->get_payslip((int)$payslip_id);
        if (!$slip) return ['success' => false, 'message' => 'Payslip not found.'];
        if (!in_array($slip->state, ['draft','computed'])) {
            return ['success' => false, 'message' => 'Cannot recompute a confirmed payslip.'];
        }

        $contract = $slip->contract_id
            ? $this->CI->payroll_mdl->get_payroll_contract($slip->contract_id)
            : $this->CI->payroll_mdl->get_active_payroll_contract($slip->employee_id);

        if (!$contract) return ['success' => false, 'message' => 'No active contract found.'];

        // Use payslip's own structure_id; fall back to the contract's if payslip was
        // created before a structure was assigned to the contract.
        $structure_id = $slip->structure_id ?: ($contract ? $contract->structure_id : null);
        $rules = $structure_id
            ? $this->CI->payroll_mdl->get_salary_rules((int)$structure_id)
            : [];

        $lines    = $this->_evaluate_rules($rules, $contract, $slip);

        // Gross = sum of all EARN lines produced by the core rules
        $gross = 0.0;
        foreach ($lines as $ln) {
            if ($ln['category'] === 'EARN') $gross += (float)$ln['amount'];
        }

        // Fire addon hook — a country addon injects statutory lines (PAYE, NSSF, SHIF…)
        // $lines is passed by reference so the addon can append to it.
        $ctx = [
            'payslip'    => $slip,
            'contract'   => $contract,
            'company_id' => (int)$slip->company_id,
            'basic'      => (float)$contract->wage,
            'gross'      => $gross,
            'lines'      => &$lines,
        ];
        hooks()->do_action('payroll_compute_statutory', $ctx);

        // Inject unpaid leave deductions BEFORE net (reduces taxable gross)
        $this->_inject_unpaid_leave_lines($lines, $slip, $contract);

        // Append the NET line AFTER statutory injection so it reflects everything
        $this->_append_net_line($lines);

        // Inject active loan repayment lines (POST-NET; reduces cash payable, not PAYE base)
        $period_date = $slip->date_from ?: date('Y-m-d');
        $this->_inject_loan_lines($lines, $slip, $period_date);

        // Clear old lines and write new ones
        $this->CI->payroll_mdl->clear_payslip_lines($payslip_id);
        $totals = $this->_write_lines($payslip_id, $lines);

        // cash_payable = net_wage minus all LOAN deductions
        $cash_payable = $totals['net'] - $totals['loans'];

        // Update payslip summary
        $this->CI->payroll_mdl->save_payslip([
            'gross_wage'       => $totals['gross'],
            'net_wage'         => $totals['net'],
            'total_deductions' => $totals['deductions'],
            'total_employer'   => $totals['employer'],
            'total_tax'        => $totals['tax'],
            'cash_payable'     => $cash_payable,
            'state'            => 'computed',
        ], (int)$payslip_id);

        return ['success' => true, 'message' => 'Payslip computed.', 'totals' => $totals];
    }

    // ── Compute next chunk of a batch run ─────────────────────────────────────
    public function compute_chunk($run_id, $chunk_size = 200)
    {
        $p   = $this->p;
        $run = $this->CI->payroll_mdl->get_payroll_run((int)$run_id);
        if (!$run) return ['success' => false, 'done' => true, 'message' => 'Run not found.'];

        // Concurrency guard
        $me = get_staff_user_id();
        if ($run->locked_by && $run->locked_by != $me) {
            return ['success' => false, 'done' => false, 'message' => 'Another user is computing this run.'];
        }

        if ($run->state === 'computed') {
            return ['success' => true, 'done' => true, 'progress' => 100,
                    'computed' => $run->computed_count, 'total' => $run->employee_count];
        }

        // Mark as computing
        if ($run->state === 'draft') {
            $this->CI->payroll_mdl->save_payroll_run([
                'state' => 'computing', 'locked_by' => $me,
                'computing_started_at' => date('Y-m-d H:i:s'), 'computing_chunk' => 0,
            ], (int)$run_id);
        }

        // Get next chunk of uncomputed payslips
        $slips = $this->CI->db->where('run_id',(int)$run_id)
                              ->where('state','draft')
                              ->limit($chunk_size)
                              ->get($p.'hr_payslips')->result();

        foreach ($slips as $slip) {
            $this->compute_payslip($slip->id);
        }

        $computed = (int)$this->CI->db->where('run_id',(int)$run_id)
                                      ->where('state','computed')
                                      ->count_all_results($p.'hr_payslips');
        $total    = (int)$run->employee_count;
        $done     = ($computed >= $total);
        $progress = $total > 0 ? round($computed / $total * 100) : 100;

        if ($done) {
            // Aggregate totals onto run
            $agg = $this->CI->db->select('SUM(gross_wage) AS tg, SUM(net_wage) AS tn, SUM(total_deductions) AS td, SUM(total_employer) AS te')
                               ->where('run_id',(int)$run_id)->get($p.'hr_payslips')->row();
            $this->CI->payroll_mdl->save_payroll_run([
                'state'          => 'computed',
                'computed_count' => $computed,
                'locked_by'      => null,
                'total_gross'    => $agg->tg ?? 0,
                'total_net'      => $agg->tn ?? 0,
                'total_deductions'=> $agg->td ?? 0,
                'total_employer' => $agg->te ?? 0,
            ], (int)$run_id);
        } else {
            $this->CI->payroll_mdl->save_payroll_run([
                'computed_count'  => $computed,
                'computing_chunk' => ($run->computing_chunk ?? 0) + count($slips),
            ], (int)$run_id);
        }

        return ['success' => true, 'done' => $done, 'progress' => $progress,
                'computed' => $computed, 'total' => $total];
    }

    // ── Evaluate core salary rules via Symfony ExpressionLanguage ────────────
    private function _evaluate_rules($rules, $contract, $slip)
    {
        $lines    = [];
        $benefits = [];
        $deds     = [];
        if (!empty($contract->lines)) {
            foreach ($contract->lines as $cl) {
                if ($cl->line_type === 'benefit')   $benefits[$cl->code ?? $cl->name] = (float)$cl->amount;
                if ($cl->line_type === 'deduction')  $deds[$cl->code ?? $cl->name]    = (float)$cl->amount;
            }
        }

        // Wrap in SafeContextArray so any unknown key (including old addon keys
        // still in the DB) silently returns 0.0 instead of a PHP 8 warning.
        $benefits = new SafeContextArray($benefits);
        $deds     = new SafeContextArray($deds);

        $period_days = max(1, (strtotime($slip->date_to) - strtotime($slip->date_from)) / 86400 + 1);
        $worked_days = (float)($slip->worked_days ?: $period_days);
        $proration   = $worked_days / $period_days;

        // Default statutory rate variables — addons override via payroll_engine_context filter
        $default_bands = json_encode([
            ['to' => 24000,  'rate' => 10],
            ['to' => 32333,  'rate' => 25],
            ['to' => 500000, 'rate' => 30],
            ['to' => 800000, 'rate' => 32.5],
            ['to' => null,   'rate' => 35],
        ]);
        $ctx = [
            'BASIC'          => (float)$contract->wage,
            'GROSS'          => 0.0,
            'TAXABLE'        => 0.0,
            'TOTAL_DED'      => 0.0,
            'TOTAL_TAX'      => 0.0,
            'NET'            => 0.0,
            'PRORATION'      => $proration,
            'DAYS'           => $worked_days,
            'benefit'        => $benefits,
            'deduction'      => $deds,
            // Statutory rate variables (Kenya defaults; other countries override via filter)
            'NSSF_RATE'      => 6.0,
            'NSSF_UEL'       => 36000.0,
            'SHIF_RATE'      => 2.75,
            'SHIF_MIN'       => 300.0,
            'AHL_RATE'       => 1.5,
            'PERSONAL_RELIEF'=> 2400.0,
            'PAYE_BANDS'     => $default_bands,
        ];

        // Let active country addons inject their configured rate variables
        $ctx = hooks()->apply_filters('payroll_engine_context', $ctx, $slip, $contract);

        foreach ($rules as $rule) {
            if (!empty($rule->condition_formula)) {
                try {
                    $cond = $this->expr->evaluate($rule->condition_formula, $ctx);
                    if (!$cond) continue;
                } catch (\Exception $e) { continue; }
            }

            $amount = 0.0;
            try {
                $amount = (float)$this->expr->evaluate($rule->amount_formula, $ctx);
            } catch (\Exception $e) {
                $amount = 0.0;
            }

            $lines[] = [
                'rule_id'            => $rule->id,
                'rule_code'          => $rule->code,
                'rule_name'          => $rule->name,
                'category'           => $rule->category,
                'sequence'           => $rule->sequence,
                'quantity'           => 1,
                'rate'               => $amount,
                'amount'             => $amount,
                'appears_on_payslip' => $rule->appears_on_payslip,
            ];

            // Update running totals — TAXABLE stays in sync for PAYE formula
            if ($rule->category === 'EARN') {
                $ctx['GROSS']   += $amount;
                $ctx['TAXABLE']  = $ctx['GROSS'] - $ctx['TOTAL_DED'];
            }
            if ($rule->category === 'DED') {
                $ctx['TOTAL_DED'] += $amount;
                $ctx['TAXABLE']    = $ctx['GROSS'] - $ctx['TOTAL_DED'];
            }
            if ($rule->category === 'TAX') { $ctx['TOTAL_TAX'] += $amount; }
        }

        return $lines;
    }

    // ── Append the NET line, derived from all EARN / DED / TAX lines ──────────
    private function _append_net_line(&$lines)
    {
        $gross = $ded = $tax = 0.0;
        foreach ($lines as $ln) {
            switch ($ln['category']) {
                case 'EARN': $gross += (float)$ln['amount']; break;
                case 'DED':  $ded   += (float)$ln['amount']; break;
                case 'TAX':  $tax   += (float)$ln['amount']; break;
            }
        }
        $net = $gross - $ded - $tax;
        $lines[] = [
            'rule_id'            => null,
            'rule_code'          => 'NET',
            'rule_name'          => 'Net Pay',
            'category'           => 'NET',
            'sequence'           => 999,
            'quantity'           => 1,
            'rate'               => $net,
            'amount'             => $net,
            'appears_on_payslip' => 1,
        ];
    }

    // ── Inject unpaid leave deductions (PRE-NET, reduces taxable gross) ──────
    private function _inject_unpaid_leave_lines(&$lines, $slip, $contract)
    {
        $p          = $this->p;
        $date_from  = $slip->date_from ?: date('Y-m-01');
        $date_to    = $slip->date_to   ?: date('Y-m-t');

        // Query approved leave requests for this employee that fall within the pay period
        // for UNPAID leave types only
        $unpaid_leave = $this->CI->db
            ->select('lr.id, lr.total_days, lt.name AS leave_type_name')
            ->from($p . 'hr_leave_requests lr')
            ->join($p . 'hr_leave_types lt', 'lt.id = lr.leave_type_id', 'inner')
            ->where('lr.employee_id', (int) $slip->employee_id)
            ->where('lr.status', 'approved')
            ->where('lt.is_paid', 0)
            ->where("lr.date_from <=", $date_to)
            ->where("lr.date_to >=", $date_from)
            ->get()
            ->result();

        if (empty($unpaid_leave)) { return; }

        // Daily rate based on basic / standard working days per month (22 default)
        $working_days = 22;
        $basic        = (float) ($contract->wage ?? 0);
        $daily_rate   = $basic > 0 ? $basic / $working_days : 0;

        foreach ($unpaid_leave as $leave) {
            $days   = (float) $leave->total_days;
            $amount = round($days * $daily_rate, 2);
            if ($amount <= 0) { continue; }

            $lines[] = [
                'rule_id'            => null,
                'rule_code'          => 'UNPAID_LEAVE',
                'rule_name'          => 'Unpaid Leave — ' . $leave->leave_type_name . ' (' . $days . 'd)',
                'category'           => 'DED',
                'sequence'           => 500,
                'quantity'           => $days,
                'rate'               => $daily_rate,
                'amount'             => $amount,
                'appears_on_payslip' => 1,
            ];
        }
    }

    // ── Inject active loan repayment lines (POST-NET) ────────────────────────
    private function _inject_loan_lines(&$lines, $slip, $period_date)
    {
        $loans = $this->CI->loans_mdl->get_active_loans_for_payroll(
            $slip->employee_id,
            $slip->company_id,
            $period_date
        );
        $seq = 1000;
        foreach ($loans as $loan) {
            $installment = (float)$loan->monthly_installment;
            // Cap at remaining balance so last payment is exact
            $amount = min($installment, (float)$loan->balance_remaining);
            if ($amount <= 0) { continue; }

            $lines[] = [
                'rule_id'            => null,
                'rule_code'          => 'LOAN_' . $loan->id,
                'rule_name'          => $loan->loan_reference
                                          ? $loan->loan_reference
                                          : ucfirst(str_replace('_', ' ', $loan->loan_type)) . ' Repayment',
                'category'           => 'LOAN',
                'sequence'           => $seq++,
                'quantity'           => 1,
                'rate'               => $amount,
                'amount'             => $amount,
                'appears_on_payslip' => 1,
            ];
        }
    }

    // ── Write computed lines and return totals ────────────────────────────────
    private function _write_lines($payslip_id, $lines)
    {
        $totals = ['gross' => 0, 'net' => 0, 'deductions' => 0, 'employer' => 0, 'tax' => 0, 'loans' => 0];
        foreach ($lines as $line) {
            $line['payslip_id'] = $payslip_id;
            $this->CI->payroll_mdl->insert_payslip_line($line);
            switch ($line['category']) {
                case 'EARN':     $totals['gross']      += $line['amount']; break;
                case 'DED':      $totals['deductions'] += $line['amount']; break;
                case 'TAX':      $totals['tax']        += $line['amount']; break;
                case 'EMPLOYER': $totals['employer']   += $line['amount']; break;
                case 'NET':      $totals['net']         = $line['amount']; break;
                case 'LOAN':     $totals['loans']      += $line['amount']; break;
            }
        }
        return $totals;
    }
}
