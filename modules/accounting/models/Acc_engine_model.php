<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_engine_model extends App_Model
{
    private $moves_table       = 'acc_moves';
    private $lines_table       = 'acc_move_lines';
    private $accounts_table    = 'acc_gl_accounts';
    private $reconcile_table   = 'acc_full_reconcile';
    private $partial_rec_table = 'acc_partial_reconcile';
    private $seq_table         = 'acc_sequences';
    private $stmt_lines_table  = 'acc_bank_statement_lines';

    // ─────────────────────────────────────────────────────────────────────────
    // MOVE CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function get_move($id)
    {
        return $this->db->get_where(db_prefix() . $this->moves_table, ['id' => $id])->row();
    }

    public function get_move_lines($move_id)
    {
        return $this->db
            ->select('ml.*, ga.code as account_code, ga.name as account_name, ga.type as account_type')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->accounts_table . ' ga', 'ga.id = ml.account_id', 'left')
            ->where('ml.move_id', $move_id)
            ->order_by('ml.sequence', 'ASC')
            ->get()->result();
    }

    public function get_moves_list($filters = [])
    {
        $this->db->select('m.*, j.name as journal_name, j.code as journal_code,
            (SELECT SUM(ml.debit) FROM ' . db_prefix() . 'acc_move_lines ml WHERE ml.move_id = m.id) as total_debit,
            (SELECT SUM(ml.credit) FROM ' . db_prefix() . 'acc_move_lines ml WHERE ml.move_id = m.id) as total_credit');
        $this->db->from(db_prefix() . $this->moves_table . ' m');
        $this->db->join(db_prefix() . 'acc_journals j', 'j.id = m.journal_id', 'left');
        $this->db->where('m.move_type', 'entry');
        $this->_apply_move_filters($filters);
        $this->db->order_by('m.date', 'DESC');
        return $this->db->get()->result();
    }

    public function get_recent_moves($limit = 10)
    {
        return $this->db
            ->select('m.id, m.name, m.move_type, m.state, m.date, m.amount_total, m.partner_id, j.name as journal_name')
            ->from(db_prefix() . $this->moves_table . ' m')
            ->join(db_prefix() . 'acc_journals j', 'j.id = m.journal_id', 'left')
            ->order_by('m.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function save_entry($data, $id = null)
    {
        $move_data = [
            'move_type'  => 'entry',
            'journal_id' => (int)($data['journal_id'] ?? 0),
            'date'       => $data['date'] ?? date('Y-m-d'),
            'ref'        => $data['ref'] ?? '',
            'narration'  => $data['narration'] ?? '',
        ];
        if (!$move_data['journal_id']) { return 'Journal is required.'; }

        if ($id) {
            $move = $this->get_move($id);
            if ($move && $move->state === 'posted') { return 'Cannot edit a posted entry.'; }
            $this->db->update(db_prefix() . $this->moves_table, array_merge($move_data, ['updated_by' => get_staff_user_id()]), ['id' => $id]);
        } else {
            $move_data['state']      = 'draft';
            $move_data['created_by'] = get_staff_user_id();
            $this->db->insert(db_prefix() . $this->moves_table, $move_data);
            $id = $this->db->insert_id();
        }

        $this->_save_move_lines($id, $data['lines'] ?? []);
        $this->_recompute_move_totals($id);

        return ['id' => $id];
    }

    public function delete_move($id)
    {
        $move = $this->get_move($id);
        if (!$move || $move->state === 'posted') { return false; }
        $this->db->delete(db_prefix() . $this->moves_table, ['id' => $id]);
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POSTING ENGINE (double-entry validation + sequence assignment)
    // ─────────────────────────────────────────────────────────────────────────
    public function post_move($move_id)
    {
        $move  = $this->get_move($move_id);
        if (!$move) { throw new Exception("Move #{$move_id} not found."); }
        if ($move->state === 'posted') { throw new Exception("Move is already posted."); }
        if ($move->state === 'cancel') { throw new Exception("Cannot post a cancelled move."); }

        $lines = $this->get_move_lines($move_id);
        $this->_validate_balance($lines);

        $sequence = $this->_get_next_sequence($move->journal_id, $move->date);

        $this->db->update(db_prefix() . $this->moves_table, [
            'state'          => 'posted',
            'name'           => $sequence,
            'sequence_prefix'=> $this->_get_sequence_prefix($move->journal_id),
            'updated_by'     => get_staff_user_id(),
        ], ['id' => $move_id]);

        foreach ($lines as $line) {
            $this->db->update(db_prefix() . $this->lines_table, ['date' => $move->date], ['id' => $line->id]);
        }

        $this->_update_account_balances($lines);
        return $sequence;
    }

    public function cancel_move($move_id)
    {
        $move = $this->get_move($move_id);
        if (!$move || $move->state === 'draft') { return false; }
        $this->db->update(db_prefix() . $this->moves_table, ['state' => 'cancel', 'updated_by' => get_staff_user_id()], ['id' => $move_id]);
        return true;
    }

    public function reset_move_to_draft($move_id)
    {
        $move = $this->get_move($move_id);
        if (!$move || $move->state === 'posted') { return false; }
        $this->db->update(db_prefix() . $this->moves_table, ['state' => 'draft', 'name' => null, 'updated_by' => get_staff_user_id()], ['id' => $move_id]);
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SEQUENCE NUMBERING  (INV/2025/00001)
    // ─────────────────────────────────────────────────────────────────────────
    private function _get_next_sequence($journal_id, $date)
    {
        $year   = date('Y', strtotime($date));
        $prefix = $this->_get_sequence_prefix($journal_id);

        $row = $this->db->get_where(db_prefix() . $this->seq_table, [
            'journal_id' => $journal_id,
            'year'       => $year,
        ])->row();

        if ($row) {
            $next = $row->last_number + 1;
            $this->db->update(db_prefix() . $this->seq_table, ['last_number' => $next], ['id' => $row->id]);
        } else {
            $next = 1;
            $this->db->insert(db_prefix() . $this->seq_table, [
                'journal_id'  => $journal_id,
                'prefix'      => $prefix,
                'year'        => $year,
                'last_number' => 1,
            ]);
        }

        return $prefix . '/' . $year . '/' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function _get_sequence_prefix($journal_id)
    {
        $j = $this->db->get_where(db_prefix() . 'acc_journals', ['id' => $journal_id])->row();
        return $j ? $j->code : 'JNL';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BALANCE VALIDATION
    // ─────────────────────────────────────────────────────────────────────────
    private function _validate_balance($lines)
    {
        $total_debit  = 0;
        $total_credit = 0;
        foreach ($lines as $line) {
            if (in_array($line->display_type, ['line_section', 'line_note', 'payment_term'])) { continue; }
            $total_debit  += (float)$line->debit;
            $total_credit += (float)$line->credit;
        }
        if (abs($total_debit - $total_credit) > 0.01) {
            throw new Exception(sprintf(
                'Journal entry is not balanced (Debit: %.2f | Credit: %.2f | Diff: %.2f)',
                $total_debit, $total_credit, abs($total_debit - $total_credit)
            ));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MOVE LINES SAVE
    // ─────────────────────────────────────────────────────────────────────────
    public function _save_move_lines($move_id, $lines_data)
    {
        $this->db->delete(db_prefix() . $this->lines_table, ['move_id' => $move_id]);
        if (empty($lines_data)) { return; }

        $seq = 10;
        foreach ($lines_data as $ld) {
            if (empty($ld['account_id']) && empty($ld['display_type'])) { continue; }
            $debit    = (float)($ld['debit']    ?? 0);
            $credit   = (float)($ld['credit']   ?? 0);
            $qty      = (float)($ld['quantity']  ?? 1);
            $price    = (float)($ld['price_unit']?? 0);
            $disc     = (float)($ld['discount']  ?? 0);
            $subtotal = round($qty * $price * (1 - $disc / 100), 4);

            $this->db->insert(db_prefix() . $this->lines_table, [
                'move_id'      => $move_id,
                'sequence'     => $seq,
                'account_id'   => (int)($ld['account_id'] ?? 0),
                'partner_id'   => (int)($ld['partner_id'] ?? 0) ?: null,
                'name'         => $ld['name'] ?? '',
                'ref'          => $ld['ref'] ?? '',
                'quantity'     => $qty,
                'price_unit'   => $price,
                'discount'     => $disc,
                'price_subtotal'=> $subtotal,
                'price_total'  => (float)($ld['price_total'] ?? $subtotal),
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $debit - $credit,
                'tax_ids'      => isset($ld['tax_ids']) ? (is_array($ld['tax_ids']) ? json_encode($ld['tax_ids']) : $ld['tax_ids']) : null,
                'tax_line_id'  => (int)($ld['tax_line_id'] ?? 0) ?: null,
                'product_id'   => (int)($ld['product_id'] ?? 0) ?: null,
                'display_type' => $ld['display_type'] ?? 'product',
                'date_maturity'=> $ld['date_maturity'] ?? null,
                'currency_id'  => (int)($ld['currency_id'] ?? 1),
            ]);
            $seq += 10;
        }
    }

    private function _recompute_move_totals($move_id)
    {
        $lines = $this->db->select('debit, credit, price_subtotal, price_total, tax_line_id, display_type')
            ->get_where(db_prefix() . $this->lines_table, ['move_id' => $move_id])->result();

        $untaxed = 0; $tax = 0; $total = 0;
        foreach ($lines as $l) {
            if (in_array($l->display_type, ['line_section', 'line_note'])) { continue; }
            if ($l->tax_line_id) {
                $tax += (float)($l->credit ?: $l->debit);
            } else if ($l->display_type === 'product') {
                $untaxed += (float)$l->price_subtotal;
            }
        }
        $total = $untaxed + $tax;

        $this->db->update(db_prefix() . $this->moves_table, [
            'amount_untaxed'  => round($untaxed, 4),
            'amount_tax'      => round($tax, 4),
            'amount_total'    => round($total, 4),
            'amount_residual' => round($total, 4),
        ], ['id' => $move_id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCOUNT BALANCE
    // ─────────────────────────────────────────────────────────────────────────
    public function get_account_balance($account_id, $date_from = null, $date_to = null)
    {
        $this->db->select('SUM(ml.debit) as total_debit, SUM(ml.credit) as total_credit')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->moves_table . ' m', 'm.id = ml.move_id')
            ->where('ml.account_id', $account_id)
            ->where('m.state', 'posted');
        if ($date_from) { $this->db->where('m.date >=', $date_from); }
        if ($date_to)   { $this->db->where('m.date <=', $date_to); }
        $row = $this->db->get()->row();
        $debit  = (float)($row->total_debit  ?? 0);
        $credit = (float)($row->total_credit ?? 0);
        return ['debit' => $debit, 'credit' => $credit, 'balance' => $debit - $credit];
    }

    public function get_account_balances_bulk($account_ids, $date_from = null, $date_to = null)
    {
        if (empty($account_ids)) { return []; }
        $this->db->select('ml.account_id, SUM(ml.debit) as total_debit, SUM(ml.credit) as total_credit')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->moves_table . ' m', 'm.id = ml.move_id')
            ->where_in('ml.account_id', $account_ids)
            ->where('m.state', 'posted')
            ->group_by('ml.account_id');
        if ($date_from) { $this->db->where('m.date >=', $date_from); }
        if ($date_to)   { $this->db->where('m.date <=', $date_to); }
        $rows = $this->db->get()->result();
        $out  = [];
        foreach ($rows as $r) {
            $out[$r->account_id] = [
                'debit'   => (float)$r->total_debit,
                'credit'  => (float)$r->total_credit,
                'balance' => (float)$r->total_debit - (float)$r->total_credit,
            ];
        }
        return $out;
    }

    private function _update_account_balances($lines)
    {
        // For reporting purposes account balances are computed on-the-fly from move_lines.
        // This is a no-op placeholder for future denormalization.
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECONCILIATION
    // ─────────────────────────────────────────────────────────────────────────
    public function reconcile_lines($debit_line_ids, $credit_line_ids)
    {
        $debit_ids  = is_array($debit_line_ids)  ? $debit_line_ids  : [$debit_line_ids];
        $credit_ids = is_array($credit_line_ids) ? $credit_line_ids : [$credit_line_ids];

        $total_debit  = $this->_sum_line_amounts($debit_ids,  'debit');
        $total_credit = $this->_sum_line_amounts($credit_ids, 'credit');
        $amount       = min($total_debit, $total_credit);

        if ($amount <= 0) { throw new Exception('No amount to reconcile.'); }

        $full_reconcile = null;
        if (abs($total_debit - $total_credit) < 0.01) {
            $name = 'FULL/' . date('Ymd') . '/' . time();
            $this->db->insert(db_prefix() . $this->reconcile_table, ['name' => $name]);
            $full_reconcile = $this->db->insert_id();
        }

        foreach ($debit_ids as $did) {
            foreach ($credit_ids as $cid) {
                $this->db->insert(db_prefix() . $this->partial_rec_table, [
                    'debit_move_id'     => $did,
                    'credit_move_id'    => $cid,
                    'full_reconcile_id' => $full_reconcile,
                    'amount'            => $amount,
                    'max_date'          => date('Y-m-d'),
                ]);
            }
        }

        $all_ids = array_merge($debit_ids, $credit_ids);
        $this->db->update(db_prefix() . $this->lines_table,
            ['reconciled' => 1, 'full_reconcile_id' => $full_reconcile, 'matching_number' => $full_reconcile ? 'FULL' : 'PARTIAL'],
            $this->db->where_in('id', $all_ids)
        );

        return true;
    }

    private function _sum_line_amounts($ids, $field)
    {
        if (empty($ids)) { return 0; }
        $row = $this->db->select("SUM({$field}) as total")
            ->where_in('id', $ids)
            ->get(db_prefix() . $this->lines_table)->row();
        return (float)($row->total ?? 0);
    }

    public function get_unreconciled_move_lines($journal_id)
    {
        return $this->db
            ->select('ml.*, m.name as move_name, m.date, m.partner_id, ga.name as account_name')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->moves_table . ' m', 'm.id = ml.move_id')
            ->join(db_prefix() . $this->accounts_table . ' ga', 'ga.id = ml.account_id', 'left')
            ->where('m.state', 'posted')
            ->where('ml.reconciled', 0)
            ->where('m.journal_id', $journal_id)
            ->where('ml.display_type', 'product')
            ->order_by('m.date', 'ASC')
            ->get()->result();
    }

    public function get_unreconciled_statement_lines($journal_id)
    {
        return $this->db
            ->where('journal_id', $journal_id)
            ->where('is_reconciled', 0)
            ->order_by('date', 'ASC')
            ->get(db_prefix() . $this->stmt_lines_table)->result();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BANK STATEMENT IMPORT (CSV)
    // ─────────────────────────────────────────────────────────────────────────
    public function import_bank_statement($journal_id, $file)
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') { throw new Exception('Only CSV files are supported.'); }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) { throw new Exception('Cannot read uploaded file.'); }

        $headers = null;
        $count   = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (!$headers) { $headers = array_map('trim', $row); continue; }
            if (count($row) < 3) { continue; }
            $mapped = array_combine($headers, $row);
            $date   = $this->_parse_date($mapped['date'] ?? $mapped['Date'] ?? '');
            $amount = (float)str_replace([',', ' '], '', $mapped['amount'] ?? $mapped['Amount'] ?? 0);
            $ref    = $mapped['reference'] ?? $mapped['Reference'] ?? $mapped['description'] ?? '';
            if (!$date || $amount == 0) { continue; }
            $this->db->insert(db_prefix() . $this->stmt_lines_table, [
                'journal_id'  => $journal_id,
                'date'        => $date,
                'amount'      => $amount,
                'payment_ref' => $ref,
                'partner_name'=> $mapped['payee'] ?? $mapped['Payee'] ?? '',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }
        fclose($handle);
        return $count;
    }

    private function _parse_date($str)
    {
        if (empty($str)) { return null; }
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT TERMS COMPUTATION
    // ─────────────────────────────────────────────────────────────────────────
    public function compute_payment_terms($amount, $payment_term_id, $date)
    {
        $term  = $this->db->get_where(db_prefix() . 'acc_payment_terms', ['id' => $payment_term_id])->row();
        $lines = $this->db->get_where(db_prefix() . 'acc_payment_term_lines', ['payment_term_id' => $payment_term_id])->result();
        if (!$term || empty($lines)) { return [['date' => $date, 'amount' => $amount]]; }

        $results  = [];
        $remaining= $amount;
        foreach ($lines as $line) {
            $due_date = $this->_compute_due_date($date, $line->delay_type, $line->nb_days);
            if ($line->value === 'percent') {
                $line_amount = round($amount * $line->value_amount / 100, 2);
            } elseif ($line->value === 'fixed') {
                $line_amount = (float)$line->value_amount;
            } else {
                $line_amount = $remaining;
            }
            $remaining -= $line_amount;
            $results[] = ['date_maturity' => $due_date, 'amount' => $line_amount];
        }
        return $results;
    }

    private function _compute_due_date($from_date, $delay_type, $nb_days)
    {
        $ts = strtotime($from_date);
        switch ($delay_type) {
            case 'days_after':
                return date('Y-m-d', strtotime("+{$nb_days} days", $ts));
            case 'days_after_end_of_month':
                $eom = strtotime('last day of this month', $ts);
                return date('Y-m-d', strtotime("+{$nb_days} days", $eom));
            default:
                return date('Y-m-d', strtotime("+{$nb_days} days", $ts));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INVOICE-SPECIFIC: AR/AP LINES BUILDER
    // ─────────────────────────────────────────────────────────────────────────
    public function build_invoice_accounting_lines($move_id)
    {
        $move  = $this->get_move($move_id);
        if (!$move) { return []; }

        $lines = $this->db->select('ml.*, ga.type as account_type')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->accounts_table . ' ga', 'ga.id = ml.account_id', 'left')
            ->where('ml.move_id', $move_id)
            ->where_in('ml.display_type', ['product', 'tax'])
            ->get()->result();

        $is_out = in_array($move->move_type, ['out_invoice', 'out_refund']);

        // Determine receivable/payable account from journal
        $journal  = $this->db->get_where(db_prefix() . 'acc_journals', ['id' => $move->journal_id])->row();
        $ar_ap_id = $journal ? $journal->account_id : null;

        $total      = (float)$move->amount_total;
        $sign       = in_array($move->move_type, ['out_invoice', 'in_invoice']) ? 1 : -1;
        $result     = [];

        // AR/AP line
        if ($ar_ap_id) {
            $result[] = [
                'account_id'   => $ar_ap_id,
                'name'         => $move->name ?? '/',
                'partner_id'   => $move->partner_id,
                'debit'        => $is_out ? $total * $sign : 0,
                'credit'       => $is_out ? 0 : $total * $sign,
                'balance'      => $is_out ? $total * $sign : -$total * $sign,
                'display_type' => 'payment_term',
                'date_maturity'=> $move->invoice_date_due,
            ];
        }

        // Revenue/expense + tax lines (already in move_lines)
        foreach ($lines as $l) {
            $result[] = (array)$l;
        }

        return $result;
    }

    private function _apply_move_filters($filters)
    {
        if (!empty($filters['journal_id'])) { $this->db->where('m.journal_id', $filters['journal_id']); }
        if (!empty($filters['state']))      { $this->db->where('m.state', $filters['state']); }
        if (!empty($filters['partner_id'])) { $this->db->where('m.partner_id', $filters['partner_id']); }
        if (!empty($filters['date_from']))  { $this->db->where('m.date >=', $filters['date_from']); }
        if (!empty($filters['date_to']))    { $this->db->where('m.date <=', $filters['date_to']); }
        if (!empty($filters['search'])) {
            $s = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()->like('m.name', $s)->or_like('m.ref', $s)->or_like('m.narration', $s)->group_end();
        }
    }
}
