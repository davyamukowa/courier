<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_payment_model extends App_Model
{
    private $payments_table = 'acc_xb_payments';
    private $moves_table    = 'acc_moves';
    private $lines_table    = 'acc_move_lines';

    public function get($id)
    {
        return $this->db->get_where(db_prefix() . $this->payments_table, ['id' => $id])->row();
    }

    public function get_list($filters = [])
    {
        $this->db->select('p.*, j.name as journal_name');
        $this->db->from(db_prefix() . $this->payments_table . ' p');
        $this->db->join(db_prefix() . 'acc_journals j', 'j.id = p.journal_id', 'left');

        if (!empty($filters['partner_type'])) { $this->db->where('p.partner_type', $filters['partner_type']); }
        if (!empty($filters['state']))        { $this->db->where('p.state', $filters['state']); }
        if (!empty($filters['date_from']))    { $this->db->where('p.date >=', $filters['date_from']); }
        if (!empty($filters['date_to']))      { $this->db->where('p.date <=', $filters['date_to']); }
        if (!empty($filters['search'])) {
            $s = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()->like('p.name', $s)->or_like('p.memo', $s)->or_like('p.ref', $s)->group_end();
        }

        $this->db->order_by('p.date', 'DESC');
        return $this->db->get()->result();
    }

    public function get_for_move($move_id)
    {
        // Returns payments linked to a specific invoice move via reconciliation
        $move = $this->db->get_where(db_prefix() . $this->moves_table, ['id' => $move_id])->row();
        if (!$move) { return []; }

        return $this->db
            ->select('p.*, j.name as journal_name')
            ->from(db_prefix() . $this->payments_table . ' p')
            ->join(db_prefix() . 'acc_journals j', 'j.id = p.journal_id', 'left')
            ->where('p.partner_id', $move->partner_id)
            ->where('p.state', 'posted')
            ->order_by('p.date', 'DESC')
            ->limit(20)
            ->get()->result();
    }

    /**
     * Register a payment against an invoice/bill move.
     * Creates: acc_xb_payments record + journal entry + reconciliation.
     */
    public function register_payment($post)
    {
        $move_id      = (int)($post['move_id'] ?? 0);
        $journal_id   = (int)($post['journal_id'] ?? 0);
        $amount       = round((float)str_replace(',', '', $post['amount'] ?? 0), 4);
        $date         = $post['date'] ?? date('Y-m-d');
        $memo         = $post['memo'] ?? '';
        $writeoff_acct= (int)($post['writeoff_account_id'] ?? 0);
        $writeoff_lbl = $post['writeoff_label'] ?? 'Write-off';
        $mark_paid    = (bool)($post['mark_as_paid'] ?? false);

        if (!$move_id || !$journal_id || $amount <= 0) {
            throw new Exception('Payment requires move, journal and positive amount.');
        }

        $this->load->model('accounting/Acc_invoice_model', 'acc_invoice');
        $this->load->model('accounting/Acc_engine_model',  'acc_engine');
        $this->load->model('accounting/Acc_journal_model', 'acc_journal');

        $move    = $this->acc_invoice->get($move_id);
        if (!$move) { throw new Exception('Invoice/Bill not found.'); }
        if ($move->state !== 'posted') { throw new Exception('Cannot pay an unposted document.'); }

        $journal = $this->acc_journal->get($journal_id);
        if (!$journal) { throw new Exception('Journal not found.'); }

        $is_out      = in_array($move->move_type, ['out_invoice', 'out_refund']);
        $partner_type= $is_out ? 'customer' : 'vendor';
        $pay_type    = $is_out ? 'inbound' : 'outbound';

        // 1. Create payment record
        $pay_name = $this->_get_payment_sequence($journal_id, $date);
        $this->db->insert(db_prefix() . $this->payments_table, [
            'name'         => $pay_name,
            'state'        => 'draft',
            'payment_type' => $pay_type,
            'partner_type' => $partner_type,
            'partner_id'   => $move->partner_id,
            'journal_id'   => $journal_id,
            'amount'       => $amount,
            'date'         => $date,
            'memo'         => $memo ?: $move->name,
            'created_by'   => get_staff_user_id(),
        ]);
        $payment_id = $this->db->insert_id();

        // 2. Create journal entry for the payment
        $ar_ap_account_id = $this->_get_ar_ap_account($move->journal_id);
        $bank_account_id  = $journal->account_id;

        if (!$ar_ap_account_id || !$bank_account_id) {
            throw new Exception('Cannot determine AR/AP or bank account for this payment.');
        }

        $jentry_data = [
            'move_type'  => 'entry',
            'journal_id' => $journal_id,
            'date'       => $date,
            'ref'        => $pay_name,
            'narration'  => $memo,
            'state'      => 'draft',
            'created_by' => get_staff_user_id(),
        ];
        $this->db->insert(db_prefix() . $this->moves_table, $jentry_data);
        $jentry_id = $this->db->insert_id();

        // Bank/cash side
        $bank_debit  = $is_out ? $amount : 0;
        $bank_credit = $is_out ? 0 : $amount;

        // AR/AP side (opposite)
        $arap_debit  = $is_out ? 0 : $amount;
        $arap_credit = $is_out ? $amount : 0;

        $lines = [
            ['account_id' => $bank_account_id,  'name' => $pay_name, 'partner_id' => $move->partner_id, 'debit' => $bank_debit, 'credit' => $bank_credit, 'balance' => $bank_debit - $bank_credit, 'display_type' => 'product'],
            ['account_id' => $ar_ap_account_id,  'name' => $pay_name, 'partner_id' => $move->partner_id, 'debit' => $arap_debit, 'credit' => $arap_credit, 'balance' => $arap_debit - $arap_credit, 'display_type' => 'product'],
        ];

        // Write-off line if partial + mark paid
        $residual = (float)$move->amount_residual;
        if ($mark_paid && $writeoff_acct && abs($residual - $amount) > 0.001) {
            $diff = $residual - $amount;
            $lines[] = [
                'account_id' => $writeoff_acct,
                'name'       => $writeoff_lbl,
                'partner_id' => $move->partner_id,
                'debit'      => $is_out ? max(0, -$diff) : max(0, $diff),
                'credit'     => $is_out ? max(0, $diff)  : max(0, -$diff),
                'balance'    => $is_out ? -$diff : $diff,
                'display_type' => 'product',
            ];
        }

        $this->acc_engine->_save_move_lines($jentry_id, $lines);
        $this->acc_engine->post_move($jentry_id);

        // 3. Link payment to move
        $this->db->update(db_prefix() . $this->payments_table, ['state' => 'posted', 'move_id' => $jentry_id], ['id' => $payment_id]);

        // 4. Update invoice residual
        $payment_applied = min($amount, $residual);
        $new_residual    = max(0, $residual - $payment_applied);
        $this->db->update(db_prefix() . $this->moves_table, ['amount_residual' => $new_residual], ['id' => $move_id]);
        $this->acc_invoice->update_payment_state($move_id);

        // 5. Reconcile AR/AP lines
        $inv_ar_line = $this->db->select('id')->from(db_prefix() . 'acc_move_lines')
            ->where('move_id', $move_id)->where('display_type', 'payment_term')->limit(1)->get()->row();
        $pay_ar_line = $this->db->select('id')->from(db_prefix() . 'acc_move_lines')
            ->where('move_id', $jentry_id)->where('account_id', $ar_ap_account_id)->limit(1)->get()->row();

        if ($inv_ar_line && $pay_ar_line) {
            try {
                $this->acc_engine->reconcile_lines([$inv_ar_line->id], [$pay_ar_line->id]);
            } catch (Exception $e) {
                // reconciliation non-fatal
            }
        }

        return $payment_id;
    }

    private function _get_ar_ap_account($journal_id)
    {
        $j = $this->db->get_where(db_prefix() . 'acc_journals', ['id' => $journal_id])->row();
        return $j ? $j->account_id : null;
    }

    private function _get_payment_sequence($journal_id, $date)
    {
        $this->load->model('accounting/Acc_engine_model', 'acc_engine_tmp');
        return $this->acc_engine_tmp->_save_move_lines && false ? '' :
            $this->_build_pay_seq($journal_id, $date);
    }

    private function _build_pay_seq($journal_id, $date)
    {
        $year = date('Y', strtotime($date));
        $j    = $this->db->get_where(db_prefix() . 'acc_journals', ['id' => $journal_id])->row();
        $pfx  = $j ? $j->code . '-PAY' : 'PAY';
        $row  = $this->db->where('journal_id', $journal_id)->where('year', $year)->get(db_prefix() . 'acc_sequences')->row();
        $next = $row ? $row->last_number + 1 : 1;
        if ($row) { $this->db->update(db_prefix() . 'acc_sequences', ['last_number' => $next], ['id' => $row->id]); }
        else { $this->db->insert(db_prefix() . 'acc_sequences', ['journal_id' => $journal_id, 'prefix' => $pfx, 'year' => $year, 'last_number' => 1]); }
        return $pfx . '/' . $year . '/' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
