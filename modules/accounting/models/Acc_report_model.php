<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_report_model extends App_Model
{
    // ── helpers ──────────────────────────────────────────────────────────

    private function _posted_lines_base($date_from = null, $date_to = null)
    {
        $this->db->select('ml.*, a.code as account_code, a.name as account_name, a.type as account_type, m.date as move_date, m.name as move_name, m.ref as move_ref')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->join(db_prefix() . 'acc_gl_accounts a', 'a.id = ml.account_id')
            ->where('m.state', 'posted');
        if ($date_from) { $this->db->where('m.date >=', $date_from); }
        if ($date_to)   { $this->db->where('m.date <=', $date_to); }
    }

    private function _get_account_totals($account_ids, $date_from = null, $date_to = null)
    {
        if (empty($account_ids)) { return []; }
        $this->db->select('ml.account_id, SUM(ml.debit) as total_debit, SUM(ml.credit) as total_credit')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->where('m.state', 'posted')
            ->where_in('ml.account_id', $account_ids)
            ->group_by('ml.account_id');
        if ($date_from) { $this->db->where('m.date >=', $date_from); }
        if ($date_to)   { $this->db->where('m.date <=', $date_to); }
        $rows = $this->db->get()->result();
        $map  = [];
        foreach ($rows as $r) {
            $map[$r->account_id] = ['debit' => (float)$r->total_debit, 'credit' => (float)$r->total_credit, 'balance' => (float)$r->total_debit - (float)$r->total_credit];
        }
        return $map;
    }

    // ── Trial Balance ─────────────────────────────────────────────────────

    public function trial_balance($date_from, $date_to, $show_zero = false)
    {
        $accounts = $this->db->select('id, code, name, type')->where('active', 1)->order_by('code', 'ASC')->get(db_prefix() . 'acc_gl_accounts')->result();
        $ids      = array_column($accounts, 'id');
        $totals   = $this->_get_account_totals($ids, $date_from, $date_to);
        $ob       = $this->_get_account_totals($ids, null, $date_from ? date('Y-m-d', strtotime($date_from . ' -1 day')) : null);

        $rows = [];
        foreach ($accounts as $a) {
            $ob_bal  = isset($ob[$a->id])     ? $ob[$a->id]['balance']     : 0;
            $d       = isset($totals[$a->id]) ? $totals[$a->id]['debit']   : 0;
            $c       = isset($totals[$a->id]) ? $totals[$a->id]['credit']  : 0;
            $cl_bal  = $ob_bal + $d - $c;
            if (!$show_zero && $d == 0 && $c == 0 && $ob_bal == 0) { continue; }
            $rows[] = ['code' => $a->code, 'name' => $a->name, 'type' => $a->type, 'opening_balance' => $ob_bal, 'debit' => $d, 'credit' => $c, 'closing_balance' => $cl_bal];
        }
        return $rows;
    }

    // ── General Ledger ────────────────────────────────────────────────────

    public function general_ledger($date_from, $date_to, $account_ids = [], $partner_id = null)
    {
        $this->_posted_lines_base($date_from, $date_to);
        if (!empty($account_ids)) { $this->db->where_in('ml.account_id', $account_ids); }
        if ($partner_id)          { $this->db->where('ml.partner_id', $partner_id); }
        $this->db->order_by('a.code', 'ASC')->order_by('m.date', 'ASC');
        $lines = $this->db->get()->result();

        $grouped = [];
        foreach ($lines as $l) {
            $grouped[$l->account_id]['meta'] = ['code' => $l->account_code, 'name' => $l->account_name, 'type' => $l->account_type];
            $grouped[$l->account_id]['lines'][] = $l;
        }

        $result = [];
        foreach ($grouped as $acct_id => $g) {
            $running = 0;
            foreach ($g['lines'] as &$ln) {
                $running    += (float)$ln->debit - (float)$ln->credit;
                $ln->running = $running;
            }
            $result[] = ['account' => $g['meta'], 'lines' => $g['lines']];
        }
        return $result;
    }

    // ── Balance Sheet ─────────────────────────────────────────────────────

    public function balance_sheet($as_of_date)
    {
        $asset_types  = ['Current Asset', 'Fixed Asset', 'Bank', 'Cash', 'Asset'];
        $liab_types   = ['Current Liability', 'Long-term Liability', 'Liability', 'Payable', 'Tax'];
        $equity_types = ['Equity'];

        $accounts = $this->db->select('id, code, name, type')->where('active', 1)->order_by('code', 'ASC')->get(db_prefix() . 'acc_gl_accounts')->result();
        $ids      = array_column($accounts, 'id');
        $totals   = $this->_get_account_totals($ids, null, $as_of_date);

        $assets   = $liabilities = $equity = [];

        foreach ($accounts as $a) {
            $d = isset($totals[$a->id]) ? $totals[$a->id]['debit']  : 0;
            $c = isset($totals[$a->id]) ? $totals[$a->id]['credit'] : 0;
            $b = $d - $c;
            if ($b == 0) { continue; }

            $row = ['id' => $a->id, 'code' => $a->code, 'name' => $a->name, 'type' => $a->type, 'balance' => $b];
            if (in_array($a->type, $asset_types))  { $assets[]      = $row; }
            elseif (in_array($a->type, $liab_types)) { $liabilities[] = $row; }
            elseif (in_array($a->type, $equity_types)) { $equity[]   = $row; }
        }

        // Retained Earnings = all P&L accounts rolled up
        $pl_balance = $this->_net_income_to_date($as_of_date);
        if ($pl_balance != 0) {
            $equity[] = ['code' => '', 'name' => 'Retained Earnings (Current Period)', 'type' => 'Equity', 'balance' => $pl_balance];
        }

        return [
            'assets'      => $assets,
            'liabilities' => $liabilities,
            'equity'      => $equity,
            'totals'      => [
                'total_assets'      => array_sum(array_column($assets, 'balance')),
                'total_liabilities' => abs(array_sum(array_column($liabilities, 'balance'))),
                'total_equity'      => abs(array_sum(array_column($equity, 'balance'))),
            ],
        ];
    }

    private function _net_income_to_date($as_of_date)
    {
        $pl_types = ['Revenue', 'Cost of Revenue', 'Expense', 'Other Income', 'Other Expense'];
        $accounts = $this->db->select('id, type')->where_in('type', $pl_types)->get(db_prefix() . 'acc_gl_accounts')->result();
        if (empty($accounts)) { return 0; }
        $ids    = array_column($accounts, 'id');
        $totals = $this->_get_account_totals($ids, null, $as_of_date);
        $net    = 0;
        foreach ($accounts as $a) {
            if (!isset($totals[$a->id])) { continue; }
            $b   = $totals[$a->id]['balance'];
            $net += in_array($a->type, ['Revenue', 'Other Income']) ? -$b : $b;
        }
        return $net;
    }

    // ── Profit & Loss ─────────────────────────────────────────────────────

    public function profit_loss($date_from, $date_to)
    {
        $sections = [
            'Revenue'           => ['Revenue', 'Other Income'],
            'Cost of Revenue'   => ['Cost of Revenue'],
            'Expense'           => ['Expense', 'Other Expense'],
        ];

        $accounts = $this->db->select('id, code, name, type')->order_by('code', 'ASC')->get(db_prefix() . 'acc_gl_accounts')->result();
        $ids      = array_column($accounts, 'id');
        $totals   = $this->_get_account_totals($ids, $date_from, $date_to);

        $result = [];
        foreach ($sections as $label => $types) {
            $lines = [];
            foreach ($accounts as $a) {
                if (!in_array($a->type, $types)) { continue; }
                $d = isset($totals[$a->id]) ? $totals[$a->id]['debit']  : 0;
                $c = isset($totals[$a->id]) ? $totals[$a->id]['credit'] : 0;
                if ($d == 0 && $c == 0) { continue; }
                // Revenue types: credit-heavy means positive revenue
                $b = in_array($a->type, ['Revenue', 'Other Income']) ? $c - $d : $d - $c;
                $lines[] = ['code' => $a->code, 'name' => $a->name, 'amount' => $b];
            }
            $result[$label] = ['lines' => $lines, 'total' => array_sum(array_column($lines, 'amount'))];
        }

        $gross_profit = ($result['Revenue']['total'] ?? 0) - ($result['Cost of Revenue']['total'] ?? 0);
        $net_income   = $gross_profit - ($result['Expense']['total'] ?? 0);

        $result['gross_profit'] = $gross_profit;
        $result['net_income']   = $net_income;
        return $result;
    }

    // ── Aged Receivable ───────────────────────────────────────────────────

    public function aged_receivable($as_of_date, $buckets = [0, 30, 60, 90])
    {
        return $this->_aged_report('customer', $as_of_date, $buckets);
    }

    public function aged_payable($as_of_date, $buckets = [0, 30, 60, 90])
    {
        return $this->_aged_report('vendor', $as_of_date, $buckets);
    }

    private function _aged_report($partner_type, $as_of_date, $buckets)
    {
        $move_type = ($partner_type === 'customer') ? ['out_invoice', 'out_refund'] : ['in_invoice', 'in_refund'];

        $moves = $this->db->select('m.id, m.name, m.partner_id, m.partner_type, m.invoice_date_due, m.amount_residual, m.amount_total, m.currency_id')
            ->from(db_prefix() . 'acc_moves m')
            ->where('m.state', 'posted')
            ->where_in('m.move_type', $move_type)
            ->where('m.payment_state !=', 'paid')
            ->where('m.invoice_date_due <=', $as_of_date)
            ->get()->result();

        $rows      = [];
        $as_of_ts  = strtotime($as_of_date);

        foreach ($moves as $m) {
            $due_ts  = strtotime($m->invoice_date_due ?: $as_of_date);
            $age     = max(0, (int)(($as_of_ts - $due_ts) / 86400));
            $bucket  = $this->_assign_bucket($age, $buckets);

            $partner_name = $this->_get_partner_name($m->partner_id, $partner_type);

            if (!isset($rows[$m->partner_id])) {
                $rows[$m->partner_id] = ['partner_name' => $partner_name, 'total' => 0];
                foreach ($buckets as $b) { $rows[$m->partner_id]["b{$b}"] = 0; }
                $rows[$m->partner_id]['b_over'] = 0;
            }
            $rows[$m->partner_id]["b{$bucket}"] += (float)$m->amount_residual;
            $rows[$m->partner_id]['total']       += (float)$m->amount_residual;
        }

        return array_values($rows);
    }

    private function _assign_bucket($age, $buckets)
    {
        $sorted = array_values($buckets);
        sort($sorted);
        $prev = 0;
        foreach ($sorted as $b) {
            if ($age <= $b) { return $prev; }
            $prev = $b;
        }
        return 'over';
    }

    private function _get_partner_name($partner_id, $partner_type)
    {
        if ($partner_type === 'customer') {
            $c = $this->db->select('CONCAT(firstname," ",lastname) as name')->get_where(db_prefix() . 'clients', ['id' => $partner_id])->row();
            return $c ? $c->name : '(Unknown)';
        }
        if ($this->db->table_exists(db_prefix() . 'pur_vendor')) {
            $v = $this->db->select('vname as name')->get_where(db_prefix() . 'pur_vendor', ['id' => $partner_id])->row();
            return $v ? $v->name : '(Unknown)';
        }
        return '(Unknown)';
    }

    // ── Tax Report ────────────────────────────────────────────────────────

    public function tax_report($date_from, $date_to)
    {
        $taxes = $this->db->select('t.id, t.name, t.amount, t.amount_type, t.type_tax_use')
            ->where('t.active', 1)
            ->get(db_prefix() . 'acc_taxes t')->result();

        $result = [];
        foreach ($taxes as $tax) {
            // Sum tax lines in posted moves
            $row = $this->db->select('SUM(ml.debit) as total_debit, SUM(ml.credit) as total_credit')
                ->from(db_prefix() . 'acc_move_lines ml')
                ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
                ->where('m.state', 'posted')
                ->where('ml.tax_id', $tax->id)
                ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
                ->get()->row();

            $tax_collected = ($row && $tax->type_tax_use === 'sale')    ? (float)$row->total_credit : 0;
            $tax_paid      = ($row && $tax->type_tax_use === 'purchase') ? (float)$row->total_debit  : 0;

            // Base amount from invoice lines
            $base = $this->db->select('SUM(ml.debit) as d, SUM(ml.credit) as c')
                ->from(db_prefix() . 'acc_move_lines ml')
                ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
                ->where('m.state', 'posted')
                ->where('ml.tax_line_id', $tax->id)
                ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
                ->get()->row();

            $net_base = $base ? abs((float)$base->d - (float)$base->c) : 0;

            $result[] = [
                'tax_name'      => $tax->name,
                'rate'          => $tax->amount,
                'type'          => $tax->type_tax_use,
                'net_amount'    => $net_base,
                'tax_amount'    => $tax->type_tax_use === 'sale' ? $tax_collected : $tax_paid,
            ];
        }
        return $result;
    }

    // ── Cash Flow ─────────────────────────────────────────────────────────

    public function cash_flow($date_from, $date_to)
    {
        $bank_types = ['Bank', 'Cash'];
        $accounts   = $this->db->select('id, code, name')->where_in('type', $bank_types)->get(db_prefix() . 'acc_gl_accounts')->result();
        $ids        = array_column($accounts, 'id');
        if (empty($ids)) { return ['inflows' => [], 'outflows' => [], 'net' => 0]; }

        $inflows  = $this->db->select_sum('ml.debit', 'total')->select('m.move_type, m.date')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->where('m.state', 'posted')->where_in('ml.account_id', $ids)
            ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
            ->group_by('m.move_type')->get()->result();

        $outflows = $this->db->select_sum('ml.credit', 'total')->select('m.move_type, m.date')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->where('m.state', 'posted')->where_in('ml.account_id', $ids)
            ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
            ->group_by('m.move_type')->get()->result();

        $in  = array_sum(array_column($inflows, 'total'));
        $out = array_sum(array_column($outflows, 'total'));

        return ['inflows' => $inflows, 'outflows' => $outflows, 'net' => $in - $out];
    }

    // ── Invoice / Bill Summary ────────────────────────────────────────────

    public function invoice_summary($date_from, $date_to, $move_type = 'out_invoice')
    {
        return $this->db->select('m.*, m.name as move_name')
            ->from(db_prefix() . 'acc_moves m')
            ->where('m.state !=', 'cancel')
            ->where('m.move_type', $move_type)
            ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
            ->order_by('m.date', 'DESC')
            ->get()->result();
    }

    // ── Partner Ledger ────────────────────────────────────────────────────

    public function partner_ledger($partner_id, $partner_type, $date_from, $date_to)
    {
        $ar_types = $partner_type === 'customer' ? ['Receivable'] : ['Payable'];
        $accounts = $this->db->select('id')->where_in('type', $ar_types)->get(db_prefix() . 'acc_gl_accounts')->result();
        $acct_ids = array_column($accounts, 'id');
        if (empty($acct_ids)) { return []; }

        return $this->db->select('ml.*, m.name as move_name, m.date as move_date, a.code as account_code, a.name as account_name')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->join(db_prefix() . 'acc_gl_accounts a', 'a.id = ml.account_id')
            ->where('m.state', 'posted')
            ->where('ml.partner_id', $partner_id)
            ->where_in('ml.account_id', $acct_ids)
            ->where('m.date >=', $date_from)->where('m.date <=', $date_to)
            ->order_by('m.date', 'ASC')
            ->get()->result();
    }

    // ── Bank Reconciliation Summary ───────────────────────────────────────

    public function unreconciled_lines($journal_id, $date_to = null)
    {
        $this->db->select('ml.*, m.date as move_date, m.name as move_name, m.ref as move_ref')
            ->from(db_prefix() . 'acc_move_lines ml')
            ->join(db_prefix() . 'acc_moves m', 'm.id = ml.move_id')
            ->where('ml.journal_id', $journal_id)
            ->where('m.state', 'posted')
            ->where('ml.reconciled', 0);
        if ($date_to) { $this->db->where('m.date <=', $date_to); }
        return $this->db->order_by('m.date', 'ASC')->get()->result();
    }

    // ── Dispatcher used by the controller ────────────────────────────────────

    public function get_report_data($report, $params)
    {
        $df = $params['date_from'] ?? date('Y-01-01');
        $dt = $params['date_to']   ?? date('Y-m-d');
        $out = ['filters' => $params];

        switch ($report) {
            case 'trial_balance':
                $out['rows']        = $this->trial_balance($df, $dt, $params['show_zero'] ?? false);
                break;
            case 'general_ledger':
                $out['ledger_data'] = $this->general_ledger($df, $dt);
                break;
            case 'balance_sheet':
                $out['report_data'] = $this->balance_sheet($dt);
                break;
            case 'profit_loss':
                $out['report_data'] = $this->profit_loss($df, $dt);
                break;
            case 'aged_receivable':
                $out['rows']        = $this->aged_receivable($dt);
                break;
            case 'aged_payable':
                $out['rows']        = $this->aged_payable($dt);
                break;
            case 'tax_report':
                $out['rows']        = $this->tax_report($df, $dt);
                break;
            case 'cash_flow':
                $out['report_data'] = $this->cash_flow($df, $dt);
                break;
            case 'partner_ledger':
                $partner_id   = (int)($params['partner_id'] ?? 0);
                $partner_type = $params['partner_type'] ?? 'customer';
                $out['lines'] = $this->partner_ledger($partner_id, $partner_type, $df, $dt);
                break;
        }

        return $out;
    }

    public function get_receivables_aging_chart()
    {
        $buckets = [0, 30, 60, 90];
        $rows    = $this->aged_receivable(date('Y-m-d'), $buckets);
        $sums    = ['b0' => 0, 'b30' => 0, 'b60' => 0, 'b90' => 0, 'b_over' => 0];
        foreach ($rows as $r) {
            foreach ($sums as $k => &$v) { $v += $r[$k] ?? 0; } unset($v);
        }
        return $sums;
    }

    public function export_report($report, $format, $params)
    {
        $data = $this->get_report_data($report, $params);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report . '_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');

        switch ($report) {
            case 'trial_balance':
                fputcsv($out, ['Code', 'Name', 'Type', 'Opening', 'Debit', 'Credit', 'Closing']);
                foreach ($data['rows'] ?? [] as $r) {
                    fputcsv($out, [$r['code'], $r['name'], $r['type'], $r['opening_balance'], $r['debit'], $r['credit'], $r['closing_balance']]);
                }
                break;
            case 'aged_receivable':
            case 'aged_payable':
                fputcsv($out, ['Partner', 'Current', '1-30', '31-60', '61-90', '90+', 'Total']);
                foreach ($data['rows'] ?? [] as $r) {
                    fputcsv($out, [$r['partner_name'], $r['b0'], $r['b30'], $r['b60'], $r['b90'], $r['b_over'], $r['total']]);
                }
                break;
            default:
                fputcsv($out, ['Report: ' . $report . ' — CSV export']);
        }

        fclose($out);
        exit;
    }
}
