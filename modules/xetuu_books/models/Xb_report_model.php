<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_report_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROUTER
    // ─────────────────────────────────────────────────────────────────────────

    public function get_report_data($report, $params = [])
    {
        $date_from  = $params['date_from']  ?? date('Y-01-01');
        $date_to    = $params['date_to']    ?? date('Y-12-31');
        $partner_id = $params['partner_id'] ?? 0;

        switch ($report) {
            case 'balance_sheet':   return $this->_get_balance_sheet($date_from, $date_to);
            case 'profit_loss':     return $this->_get_profit_loss($date_from, $date_to);
            case 'general_ledger':  return $this->_get_general_ledger($date_from, $date_to, (int)($params['account_id'] ?? 0));
            case 'trial_balance':   return $this->_get_trial_balance($date_from, $date_to);
            case 'aged_receivable': return $this->_get_aged_receivable($date_to);
            case 'aged_payable':    return $this->_get_aged_payable($date_to);
            case 'tax_report':      return $this->_get_tax_report($date_from, $date_to);
            case 'cash_flow':       return $this->_get_cash_flow($date_from, $date_to);
            case 'partner_ledger':      return $this->_get_partner_ledger($date_from, $date_to, $partner_id);
            case 'executive_summary':   return $this->_get_executive_summary($date_from, $date_to);
            default:                    return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BALANCE SHEET
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_balance_sheet($date_from, $date_to)
    {
        $types    = ['Asset','Current Asset','Fixed Asset','Bank','Cash',
                     'Liability','Current Liability','Long-term Liability',
                     'Equity','Receivable','Payable','Tax'];
        $accounts = $this->_get_accounts_balance('1970-01-01', $date_to, $types);

        $assets      = []; $liabilities = []; $equity = [];
        $total_assets = 0; $total_liabilities = 0; $total_equity = 0;

        foreach ($accounts as $acc) {
            if (in_array($acc->type, ['Asset','Current Asset','Fixed Asset','Bank','Cash','Receivable'])) {
                $assets[] = $acc;
                $total_assets += $acc->balance;
            } elseif (in_array($acc->type, ['Liability','Current Liability','Long-term Liability','Payable','Tax'])) {
                $liabilities[] = $acc;
                $total_liabilities += abs($acc->balance);
            } elseif ($acc->type === 'Equity') {
                $equity[] = $acc;
                $total_equity += abs($acc->balance);
            }
        }

        // Current year net profit (retained earnings contribution)
        $pl   = $this->_get_profit_loss($date_from, $date_to);
        $total_equity += $pl['net_profit'];

        return [
            'assets'                   => $assets,
            'liabilities'              => $liabilities,
            'equity'                   => $equity,
            'retained_earnings'        => $pl['net_profit'],
            'total_assets'             => $total_assets,
            'total_liabilities'        => $total_liabilities,
            'total_equity'             => $total_equity,
            'total_liabilities_equity' => $total_liabilities + $total_equity,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROFIT & LOSS
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_profit_loss($date_from, $date_to)
    {
        $types    = ['Revenue','Other Income','Cost of Revenue','Expense','Other Expense'];
        $accounts = $this->_get_accounts_balance($date_from, $date_to, $types);

        $revenue = []; $cost_of_revenue = []; $expense = [];
        $total_revenue = 0; $total_cor = 0; $total_expense = 0;

        foreach ($accounts as $acc) {
            if (in_array($acc->type, ['Revenue','Other Income'])) {
                $revenue[] = $acc;
                $total_revenue += abs($acc->balance);
            } elseif ($acc->type === 'Cost of Revenue') {
                $cost_of_revenue[] = $acc;
                $total_cor += $acc->balance;
            } else {
                $expense[] = $acc;
                $total_expense += $acc->balance;
            }
        }

        $gross_profit = $total_revenue - $total_cor;
        $net_profit   = $gross_profit - $total_expense;

        return [
            'revenue'         => $revenue,
            'cost_of_revenue' => $cost_of_revenue,
            'expense'         => $expense,
            'total_revenue'   => $total_revenue,
            'total_cor'       => $total_cor,
            'total_expense'   => $total_expense,
            'gross_profit'    => $gross_profit,
            'net_profit'      => $net_profit,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERAL LEDGER  (Step 4)
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_general_ledger($date_from, $date_to, $account_id = 0)
    {
        $this->db->select('
            aml.id, aml.account_id, aml.partner_id, aml.name,
            aml.debit, aml.credit, aml.balance, aml.reconciled,
            aml.currency_id, aml.amount_currency,
            aa.code      AS account_code,
            aa.name      AS account_name,
            aa.type      AS account_type,
            am.id        AS move_id,
            am.date      AS move_date,
            am.name      AS move_name,
            am.ref       AS move_ref,
            am.move_type AS move_type,
            am.currency_id AS move_currency_id,
            am.exchange_rate AS move_exchange_rate,
            j.name       AS journal_name,
            c.company    AS partner_name
        ');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id');
        $this->db->join('acc_moves am',    'am.id = aml.move_id');
        $this->db->join('acc_journals j',  'j.id = am.journal_id', 'left');
        $this->db->join('tblclients c',    'c.userid = aml.partner_id', 'left');
        $this->db->where('am.state', 'posted');
        $this->db->where('am.date >=', $date_from);
        $this->db->where('am.date <=', $date_to);

        if ($account_id) {
            $this->db->where('aml.account_id', $account_id);
        }

        $this->db->order_by('aa.code',  'ASC');
        $this->db->order_by('am.date',  'ASC');
        $this->db->order_by('aml.id',   'ASC');

        $lines  = $this->db->get()->result();
        $ledger = [];

        foreach ($lines as $line) {
            $key = $line->account_code . ' — ' . $line->account_name;
            if (!isset($ledger[$key])) {
                // Compute opening balance: all posted lines for this account BEFORE date_from
                $this->db->select('COALESCE(SUM(ml2.debit),0) - COALESCE(SUM(ml2.credit),0) AS opening_balance');
                $this->db->from('acc_move_lines ml2');
                $this->db->join('acc_moves m2', 'm2.id = ml2.move_id');
                $this->db->where('ml2.account_id', $line->account_id);
                $this->db->where('m2.state', 'posted');
                $this->db->where('m2.date <', $date_from);
                $ob_row = $this->db->get()->row();

                $ledger[$key] = [
                    'account_id'      => $line->account_id,
                    'account_code'    => $line->account_code,
                    'account_name'    => $line->account_name,
                    'opening_balance' => $ob_row ? (float)$ob_row->opening_balance : 0,
                    'lines'           => [],
                    'total_debit'     => 0,
                    'total_credit'    => 0,
                    'closing_balance' => $ob_row ? (float)$ob_row->opening_balance : 0,
                ];
            }
            $ledger[$key]['lines'][]          = $line;
            $ledger[$key]['total_debit']      += (float)$line->debit;
            $ledger[$key]['total_credit']     += (float)$line->credit;
            $ledger[$key]['closing_balance']  += (float)$line->debit - (float)$line->credit;
        }

        return ['ledger' => $ledger, 'date_from' => $date_from, 'date_to' => $date_to];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRIAL BALANCE
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_trial_balance($date_from, $date_to)
    {
        $open_date = date('Y-m-d', strtotime($date_from . ' -1 day'));

        // Opening balances (all posted moves before date_from)
        $ob_rows = $this->_get_accounts_balance('1970-01-01', $open_date);
        $ob_map  = [];
        foreach ($ob_rows as $r) {
            $ob_map[$r->id] = ['ob_debit' => (float)$r->debit, 'ob_credit' => (float)$r->credit];
        }

        // Period activity
        $accounts = $this->_get_accounts_balance($date_from, $date_to);

        // Merge opening into period data; also include accounts that only have opening balance
        $all_ids = array_unique(array_merge(
            array_map(fn($a) => $a->id, $accounts),
            array_keys($ob_map)
        ));

        // Re-fetch all accounts that appear in either set
        $all_accounts_raw = $this->_get_accounts_balance('1970-01-01', $date_to);
        $end_map = [];
        foreach ($all_accounts_raw as $r) {
            $end_map[$r->id] = ['end_debit' => (float)$r->debit, 'end_credit' => (float)$r->credit, 'end_balance' => (float)$r->balance];
        }

        $period_map = [];
        foreach ($accounts as $r) {
            $period_map[$r->id] = $r;
        }

        // Build merged list
        $merged = [];
        foreach ($all_accounts_raw as $acc) {
            $id  = $acc->id;
            $ob  = $ob_map[$id]  ?? ['ob_debit' => 0, 'ob_credit' => 0];
            $per = $period_map[$id] ?? null;
            $end = $end_map[$id]  ?? ['end_debit' => 0, 'end_credit' => 0, 'end_balance' => 0];

            $acc->ob_debit    = $ob['ob_debit'];
            $acc->ob_credit   = $ob['ob_credit'];
            $acc->per_debit   = $per ? (float)$per->debit  : 0;
            $acc->per_credit  = $per ? (float)$per->credit : 0;
            $acc->end_debit   = $end['end_debit'];
            $acc->end_credit  = $end['end_credit'];
            $acc->end_balance = $end['end_balance'];
            $merged[] = $acc;
        }

        $total_ob_debit  = array_sum(array_column($merged, 'ob_debit'));
        $total_ob_credit = array_sum(array_column($merged, 'ob_credit'));
        $total_per_debit = array_sum(array_column($merged, 'per_debit'));
        $total_per_credit = array_sum(array_column($merged, 'per_credit'));
        $total_end_debit  = array_sum(array_column($merged, 'end_debit'));
        $total_end_credit = array_sum(array_column($merged, 'end_credit'));

        // legacy totals for is_balanced check
        $total_debit  = array_sum(array_map(fn($a) => $a->debit, $accounts));
        $total_credit = array_sum(array_map(fn($a) => $a->credit, $accounts));
        $variance     = round(abs($total_debit - $total_credit), 4);

        return [
            'accounts'         => $merged,
            'total_debit'      => $total_debit,
            'total_credit'     => $total_credit,
            'total_ob_debit'   => $total_ob_debit,
            'total_ob_credit'  => $total_ob_credit,
            'total_per_debit'  => $total_per_debit,
            'total_per_credit' => $total_per_credit,
            'total_end_debit'  => $total_end_debit,
            'total_end_credit' => $total_end_credit,
            'variance'         => $variance,
            'is_balanced'      => $variance < 0.01,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AGED RECEIVABLE
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_aged_receivable($as_of_date)
    {
        return $this->_get_aged_partner('customer', 'out_invoice', $as_of_date);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AGED PAYABLE
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_aged_payable($as_of_date)
    {
        return $this->_get_aged_partner('vendor', 'in_invoice', $as_of_date);
    }

    private function _get_aged_partner($partner_type, $move_type, $as_of_date)
    {
        $this->db->select('
            am.partner_id,
            am.id as move_id,
            am.name as move_name,
            am.invoice_date_due as due_date,
            am.amount_residual as balance
        ');
        $this->db->from('acc_moves am');
        $this->db->where('am.move_type', $move_type);
        $this->db->where('am.state', 'posted');
        $this->db->where('am.payment_state !=', 'paid');
        $this->db->where('am.date <=', $as_of_date);
        $rows = $this->db->get()->result();

        $partners = [];
        $buckets  = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, '91_120' => 0, 'over_120' => 0, 'total' => 0];

        foreach ($rows as $row) {
            $pid    = $row->partner_id ?: 0;
            $due    = $row->due_date   ?: $as_of_date;
            $days   = (int)((strtotime($as_of_date) - strtotime($due)) / 86400);
            $bucket = $this->_aging_bucket($days);

            if (!isset($partners[$pid])) {
                $partners[$pid] = [
                    'partner_id'   => $pid,
                    'partner_name' => $this->_partner_name($pid),
                    'current'  => 0, '1_30'  => 0, '31_60' => 0,
                    '61_90'    => 0, '91_120'=> 0, 'over_120' => 0,
                    'total'    => 0, 'lines' => [],
                ];
            }

            $partners[$pid][$bucket]   += $row->balance;
            $partners[$pid]['total']   += $row->balance;
            $partners[$pid]['lines'][]  = $row;
            $buckets[$bucket]           += $row->balance;
            $buckets['total']           += $row->balance;
        }

        return [
            'partners' => array_values($partners),
            'totals'   => $buckets,
        ];
    }

    private function _aging_bucket($days)
    {
        if ($days <= 0)   return 'current';
        if ($days <= 30)  return '1_30';
        if ($days <= 60)  return '31_60';
        if ($days <= 90)  return '61_90';
        if ($days <= 120) return '91_120';
        return 'over_120';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TAX REPORT
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_tax_report($date_from, $date_to)
    {
        // Output VAT — from sale invoice tax lines
        $this->db->select('
            at.name as tax_name,
            at.amount as tax_rate,
            COALESCE(SUM(aml.credit - aml.debit),0) as tax_amount,
            COALESCE(SUM(aml.tax_base_amount),0) as base_amount
        ');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->join('acc_taxes at', 'at.id = aml.tax_line_id', 'left');
        $this->db->where('am.state', 'posted');
        $this->db->where('am.date >=', $date_from);
        $this->db->where('am.date <=', $date_to);
        $this->db->where("am.move_type IN ('out_invoice','out_refund')", null, false);
        $this->db->where('aml.tax_line_id IS NOT NULL', null, false);
        $this->db->group_by('at.id');
        $output_tax = $this->db->get()->result();

        // Input VAT — from purchase invoice tax lines
        $this->db->select('
            at.name as tax_name,
            at.amount as tax_rate,
            COALESCE(SUM(aml.debit - aml.credit),0) as tax_amount,
            COALESCE(SUM(aml.tax_base_amount),0) as base_amount
        ');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->join('acc_taxes at', 'at.id = aml.tax_line_id', 'left');
        $this->db->where('am.state', 'posted');
        $this->db->where('am.date >=', $date_from);
        $this->db->where('am.date <=', $date_to);
        $this->db->where("am.move_type IN ('in_invoice','in_refund')", null, false);
        $this->db->where('aml.tax_line_id IS NOT NULL', null, false);
        $this->db->group_by('at.id');
        $input_tax = $this->db->get()->result();

        $total_output = array_sum(array_column($output_tax, 'tax_amount'));
        $total_input  = array_sum(array_column($input_tax,  'tax_amount'));

        return [
            'output_tax'   => $output_tax,
            'input_tax'    => $input_tax,
            'total_output' => $total_output,
            'total_input'  => $total_input,
            'net_payable'  => $total_output - $total_input,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CASH FLOW STATEMENT (indirect method)
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_cash_flow($date_from, $date_to)
    {
        // Net profit from P&L
        $pl          = $this->_get_profit_loss($date_from, $date_to);
        $net_profit  = $pl['net_profit'];

        // Opening cash balance (all Bank/Cash accounts at date_from - 1 day)
        $open_date   = date('Y-m-d', strtotime($date_from . ' -1 day'));
        $open        = $this->_get_accounts_balance('1970-01-01', $open_date, ['Bank','Cash']);
        $opening_cash = array_sum(array_column($open, 'balance'));

        // Closing cash balance
        $close       = $this->_get_accounts_balance('1970-01-01', $date_to, ['Bank','Cash']);
        $closing_cash = array_sum(array_column($close, 'balance'));

        // Change in receivables
        $ar_open  = $this->_account_type_balance('Receivable', '1970-01-01', $open_date);
        $ar_close = $this->_account_type_balance('Receivable', '1970-01-01', $date_to);
        $delta_ar = ($ar_open - $ar_close);

        // Change in payables
        $ap_open  = $this->_account_type_balance('Payable', '1970-01-01', $open_date);
        $ap_close = $this->_account_type_balance('Payable', '1970-01-01', $date_to);
        $delta_ap = ($ap_close - $ap_open);

        // Change in inventory
        $inv_open  = $this->_account_type_balance('Current Asset', '1970-01-01', $open_date);
        $inv_close = $this->_account_type_balance('Current Asset', '1970-01-01', $date_to);
        $delta_inv = ($inv_open - $inv_close);

        // Depreciation from P&L expense accounts named "Depreciation"
        $this->db->select('COALESCE(SUM(aml.debit - aml.credit),0) as dep');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id');
        $this->db->like('aa.name', 'Depreciation');
        $this->db->where('am.state', 'posted');
        $this->db->where('am.date >=', $date_from);
        $this->db->where('am.date <=', $date_to);
        $dep_row     = $this->db->get()->row();
        $depreciation = $dep_row ? abs((float)$dep_row->dep) : 0;

        $operating_cf = $net_profit + $depreciation + $delta_ar + $delta_ap + $delta_inv;

        // Investing: Fixed asset movements
        $fa_open  = $this->_account_type_balance('Fixed Asset', '1970-01-01', $open_date);
        $fa_close = $this->_account_type_balance('Fixed Asset', '1970-01-01', $date_to);
        $investing_cf = $fa_open - $fa_close;

        // Financing: Equity and long-term liabilities changes
        $eq_open  = $this->_account_type_balance('Equity', '1970-01-01', $open_date);
        $eq_close = $this->_account_type_balance('Equity', '1970-01-01', $date_to);
        $lt_open  = $this->_account_type_balance('Long-term Liability', '1970-01-01', $open_date);
        $lt_close = $this->_account_type_balance('Long-term Liability', '1970-01-01', $date_to);
        $financing_cf = abs($eq_close - $eq_open) + abs($lt_close - $lt_open);

        return [
            'net_profit'    => $net_profit,
            'depreciation'  => $depreciation,
            'delta_ar'      => $delta_ar,
            'delta_ap'      => $delta_ap,
            'delta_inv'     => $delta_inv,
            'operating_cf'  => $operating_cf,
            'investing_cf'  => $investing_cf,
            'financing_cf'  => $financing_cf,
            'net_change'    => $operating_cf + $investing_cf + $financing_cf,
            'opening_cash'  => $opening_cash,
            'closing_cash'  => $closing_cash,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTNER LEDGER
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_partner_ledger($date_from, $date_to, $partner_id = 0)
    {
        $this->db->select('
            aml.*, am.name as move_name, am.date as move_date, am.move_type,
            aa.code as account_code, aa.name as account_name
        ');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id');
        $this->db->where('am.state', 'posted');
        $this->db->where('am.date >=', $date_from);
        $this->db->where('am.date <=', $date_to);
        $this->db->where_in('aa.type', ['Receivable', 'Payable']);

        if ($partner_id) {
            $this->db->where('aml.partner_id', $partner_id);
        }

        $this->db->order_by('aml.partner_id', 'ASC');
        $this->db->order_by('am.date', 'ASC');
        $lines = $this->db->get()->result();

        $partners = [];
        foreach ($lines as $line) {
            $pid = $line->partner_id ?: 0;
            if (!isset($partners[$pid])) {
                $partners[$pid] = [
                    'partner_id'   => $pid,
                    'partner_name' => $this->_partner_name($pid),
                    'lines'        => [],
                    'total_debit'  => 0,
                    'total_credit' => 0,
                    'balance'      => 0,
                ];
            }
            $partners[$pid]['lines'][]      = $line;
            $partners[$pid]['total_debit']  += $line->debit;
            $partners[$pid]['total_credit'] += $line->credit;
            $partners[$pid]['balance']      += $line->balance;
        }

        return ['partners' => array_values($partners)];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_accounts_balance($date_from, $date_to, $types = null)
    {
        $this->db->select('
            aa.id, aa.code, aa.name, aa.type,
            COALESCE(SUM(aml.debit),0)   AS debit,
            COALESCE(SUM(aml.credit),0)  AS credit,
            COALESCE(SUM(aml.balance),0) AS balance
        ');
        $this->db->from('acc_accounts aa');
        $this->db->join('acc_move_lines aml', 'aml.account_id = aa.id', 'left');
        $this->db->join('acc_moves am', 'am.id = aml.move_id AND am.state = \'posted\' AND am.date >= \'' . $date_from . '\' AND am.date <= \'' . $date_to . '\'', 'left');
        // Exclude draft/out-of-range move_lines: aml.id IS NULL means no lines at all (keep zero-balance accounts)
        $this->db->where('(aml.id IS NULL OR am.id IS NOT NULL)', null, false);

        if ($types) {
            $this->db->where_in('aa.type', (array)$types);
        }

        $this->db->group_by('aa.id');
        $this->db->order_by('aa.code', 'ASC');
        $results = $this->db->get()->result();

        return array_values(array_filter($results, fn($a) =>
            abs($a->balance) > 0.001 || $a->debit > 0 || $a->credit > 0
        ));
    }

    private function _account_type_balance($type, $date_from, $date_to)
    {
        $rows = $this->_get_accounts_balance($date_from, $date_to, [$type]);
        return array_sum(array_column($rows, 'balance'));
    }

    private function _partner_name($partner_id)
    {
        if (!$partner_id) return 'Unknown';
        // Try Perfex CRM tblclients
        $this->db->select('company');
        $this->db->where('userid', $partner_id);
        $row = $this->db->get('tblclients')->row();
        return $row ? $row->company : 'Partner #' . $partner_id;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXECUTIVE SUMMARY
    // ─────────────────────────────────────────────────────────────────────────

    private function _get_executive_summary($date_from, $date_to)
    {
        $pl = $this->_get_profit_loss($date_from, $date_to);
        $bs = $this->_get_balance_sheet($date_from, $date_to);
        $today = date('Y-m-d');

        // Unpaid AR
        $this->db->select('COALESCE(SUM(amount_residual),0) as val, COUNT(*) as cnt', false);
        $this->db->from('acc_moves');
        $this->db->where('state', 'posted')->where('move_type', 'out_invoice');
        $this->db->where_in('payment_state', ['not_paid', 'partial']);
        $ar_row    = $this->db->get()->row();
        $unpaid_ar = $ar_row ? (float)$ar_row->val : 0;
        $ar_count  = $ar_row ? (int)$ar_row->cnt  : 0;

        // Unpaid AP
        $this->db->select('COALESCE(SUM(amount_residual),0) as val, COUNT(*) as cnt', false);
        $this->db->from('acc_moves');
        $this->db->where('state', 'posted')->where('move_type', 'in_invoice');
        $this->db->where_in('payment_state', ['not_paid', 'partial']);
        $ap_row    = $this->db->get()->row();
        $unpaid_ap = $ap_row ? (float)$ap_row->val : 0;
        $ap_count  = $ap_row ? (int)$ap_row->cnt  : 0;

        // Overdue AR (past due date, not paid)
        $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_residual),0) as val', false);
        $this->db->from('acc_moves');
        $this->db->where('state', 'posted')->where('move_type', 'out_invoice');
        $this->db->where_in('payment_state', ['not_paid', 'partial']);
        $this->db->where('invoice_date_due <', $today);
        $ov_row      = $this->db->get()->row();
        $overdue_ar  = $ov_row ? (float)$ov_row->val : 0;
        $overdue_cnt = $ov_row ? (int)$ov_row->cnt   : 0;

        // Cash position: sum of Bank + Cash account balances (all time)
        $cash_accounts = $this->_get_accounts_balance('1970-01-01', $today, ['Bank', 'Cash']);
        $cash_position = array_sum(array_column((array)$cash_accounts, 'balance'));

        // Monthly revenue for current year (for chart data)
        $year    = date('Y', strtotime($date_from));
        $monthly_revenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $m_from = sprintf('%d-%02d-01', $year, $m);
            $m_to   = date('Y-m-t', strtotime($m_from));
            $this->db->select('COALESCE(SUM(ml.credit - ml.debit),0) as revenue', false);
            $this->db->from('acc_move_lines ml');
            $this->db->join('acc_moves am',    'am.id = ml.move_id');
            $this->db->join('acc_accounts aa', 'aa.id = ml.account_id');
            $this->db->where('am.state', 'posted');
            $this->db->where('am.date >=', $m_from)->where('am.date <=', $m_to);
            $this->db->where('aa.type', 'Revenue');
            $row = $this->db->get()->row();
            $monthly_revenue[$m] = $row ? round((float)$row->revenue, 2) : 0;
        }

        return array_merge($pl, [
            'total_assets'   => $bs['total_assets'],
            'cash_position'  => $cash_position,
            'unpaid_ar'      => $unpaid_ar,
            'ar_count'       => $ar_count,
            'unpaid_ap'      => $unpaid_ap,
            'ap_count'       => $ap_count,
            'overdue_ar'     => $overdue_ar,
            'overdue_count'  => $overdue_cnt,
            'revenue_top'    => array_slice((array)$pl['revenue'], 0, 5),
            'expense_top'    => array_slice((array)$pl['expense'], 0, 5),
            'monthly_revenue'=> $monthly_revenue,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT (CSV)
    // ─────────────────────────────────────────────────────────────────────────

    public function export_report($report, $format, $params)
    {
        $data = $this->get_report_data($report, $params);
        $title = ucwords(str_replace('_', ' ', $report));

        if ($format === 'csv') {
            $this->_export_csv($report, $title, $data);
        } elseif ($format === 'pdf') {
            $this->_export_pdf($report, $title, $data, $params);
        } else {
            $this->_export_csv($report, $title, $data);
        }
    }

    private function _export_csv($report, $title, $data)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report . '_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');

        fputcsv($out, [$title, 'Exported: ' . date('Y-m-d H:i')]);
        fputcsv($out, []);

        switch ($report) {
            case 'trial_balance':
                fputcsv($out, ['Code', 'Account', 'Type', 'Debit', 'Credit']);
                foreach ($data['accounts'] as $acc) {
                    fputcsv($out, [$acc->code, $acc->name, $acc->type,
                        number_format($acc->debit, 2), number_format($acc->credit, 2)]);
                }
                fputcsv($out, ['', 'TOTAL', '', number_format($data['total_debit'], 2), number_format($data['total_credit'], 2)]);
                break;

            case 'general_ledger':
                fputcsv($out, ['Account', 'Date', 'Entry', 'Label', 'Debit', 'Credit']);
                foreach ($data['ledger'] as $acc_name => $acc_data) {
                    fputcsv($out, [$acc_name]);
                    foreach ($acc_data['lines'] as $line) {
                        fputcsv($out, ['', $line->move_date, $line->move_name, $line->name,
                            number_format($line->debit, 2), number_format($line->credit, 2)]);
                    }
                    fputcsv($out, ['', '', 'Subtotal', '', number_format($acc_data['total_debit'], 2), number_format($acc_data['total_credit'], 2)]);
                }
                break;

            case 'aged_receivable':
            case 'aged_payable':
                fputcsv($out, ['Partner', 'Current', '1-30 Days', '31-60 Days', '61-90 Days', '91-120 Days', '120+ Days', 'Total']);
                foreach ($data['partners'] as $p) {
                    fputcsv($out, [
                        $p['partner_name'],
                        number_format($p['current'],  2),
                        number_format($p['1_30'],     2),
                        number_format($p['31_60'],    2),
                        number_format($p['61_90'],    2),
                        number_format($p['91_120'],   2),
                        number_format($p['over_120'], 2),
                        number_format($p['total'],    2),
                    ]);
                }
                $t = $data['totals'];
                fputcsv($out, ['TOTAL',
                    number_format($t['current'],0), number_format($t['1_30'],0),
                    number_format($t['31_60'],0),   number_format($t['61_90'],0),
                    number_format($t['91_120'],0),  number_format($t['over_120'],0),
                    number_format($t['total'],0)]);
                break;

            case 'profit_loss':
                fputcsv($out, ['Section', 'Account', 'Type', 'Amount']);
                foreach ($data['revenue'] as $a) {
                    fputcsv($out, ['Revenue', $a->name, $a->type, number_format(abs($a->balance), 2)]);
                }
                fputcsv($out, ['', 'Total Revenue', '', number_format($data['total_revenue'], 2)]);
                foreach ($data['cost_of_revenue'] as $a) {
                    fputcsv($out, ['Cost of Revenue', $a->name, $a->type, number_format($a->balance, 2)]);
                }
                fputcsv($out, ['', 'Gross Profit', '', number_format($data['gross_profit'], 2)]);
                foreach ($data['expense'] as $a) {
                    fputcsv($out, ['Expenses', $a->name, $a->type, number_format($a->balance, 2)]);
                }
                fputcsv($out, ['', 'Net Profit', '', number_format($data['net_profit'], 2)]);
                break;

            default:
                fputcsv($out, ['No export formatter for this report.']);
        }

        fclose($out);
        exit;
    }

    private function _export_pdf($report, $title, $data, $params)
    {
        // Falls back to CSV if no PDF library available
        if (class_exists('mPDF') || class_exists('Mpdf\Mpdf')) {
            // mPDF path
            $html  = '<h2>' . $title . '</h2>';
            $html .= '<p>Period: ' . ($params['date_from'] ?? '') . ' — ' . ($params['date_to'] ?? '') . '</p>';
            $html .= $this->_report_to_html_table($report, $data);

            try {
                if (class_exists('Mpdf\Mpdf')) {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
                } else {
                    $mpdf = new mPDF('utf-8', 'A4-L');
                }
                $mpdf->WriteHTML($html);
                $mpdf->Output($report . '_' . date('Ymd') . '.pdf', 'D');
                exit;
            } catch (Exception $e) {
                // fall through to CSV
            }
        }
        $this->_export_csv($report, $title, $data);
    }

    private function _report_to_html_table($report, $data)
    {
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:10px;">';
        switch ($report) {
            case 'trial_balance':
                $html .= '<tr style="background:#1a6b3a;color:#fff"><th>Code</th><th>Account</th><th>Debit</th><th>Credit</th></tr>';
                foreach ($data['accounts'] as $acc) {
                    $html .= '<tr><td>' . $acc->code . '</td><td>' . htmlspecialchars($acc->name) . '</td><td align="right">' . number_format($acc->debit,2) . '</td><td align="right">' . number_format($acc->credit,2) . '</td></tr>';
                }
                $html .= '<tr style="font-weight:bold"><td colspan="2">TOTAL</td><td align="right">' . number_format($data['total_debit'],2) . '</td><td align="right">' . number_format($data['total_credit'],2) . '</td></tr>';
                break;
            default:
                $html .= '<tr><td>See CSV export for detailed data.</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }

    public function get_dashboard_financials()
    {
        $year = date('Y');
        
        // 1. AR & AP Balances
        $this->db->select('SUM(aml.balance) as bal');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where("aa.type IN ('Receivable')");
        $ar = $this->db->get()->row()->bal ?? 0;

        $this->db->select('SUM(aml.balance) as bal');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where("aa.type IN ('Payable')");
        $ap = $this->db->get()->row()->bal ?? 0;

        // 2. Cash & Bank Balances
        $this->db->select('SUM(aml.balance) as bal');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where("aa.type IN ('Bank', 'Cash')");
        $bank = $this->db->get()->row()->bal ?? 0;

        // 3. Income vs Expense by Month (Current Year)
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $income_data = array_fill(0, 12, 0);
        $expense_data = array_fill(0, 12, 0);

        $this->db->select('MONTH(aml.date) as m, SUM(aml.credit - aml.debit) as bal');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where("aa.type IN ('Revenue', 'Other Income')");
        $this->db->where('YEAR(aml.date)', $year);
        $this->db->group_by('MONTH(aml.date)');
        foreach($this->db->get()->result() as $row) {
            $income_data[$row->m - 1] = (float)$row->bal;
        }

        $this->db->select('MONTH(aml.date) as m, SUM(aml.debit - aml.credit) as bal');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where("aa.type IN ('Expense', 'Cost of Revenue', 'Other Expense')");
        $this->db->where('YEAR(aml.date)', $year);
        $this->db->group_by('MONTH(aml.date)');
        foreach($this->db->get()->result() as $row) {
            $expense_data[$row->m - 1] = (float)$row->bal;
        }

        return [
            'ar' => $ar,
            'ap' => abs($ap),
            'bank' => $bank,
            'chart_labels' => $months,
            'chart_income' => $income_data,
            'chart_expense' => $expense_data,
        ];
    }
}
