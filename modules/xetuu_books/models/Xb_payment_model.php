<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_payment_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('xetuu_books/Xb_engine_model',  'xb_engine');
        $this->load->model('xetuu_books/Xb_config_model',  'xb_config');
    }

    public function get_list($filters = [])
    {
        $this->db->select('p.*, j.name as journal_name, IF(p.partner_type="vendor", COALESCE(pv.company,""), COALESCE(c.company,"")) as partner_name');
        $this->db->from('acc_payments p');
        $this->db->join('acc_journals j',   'j.id = p.journal_id', 'left');
        $this->db->join('tblclients c',     'c.userid  = p.partner_id', 'left');
        $this->db->join('tblpur_vendor pv', 'pv.userid = p.partner_id', 'left');

        if (!empty($filters['partner_type'])) {
            $this->db->where('p.partner_type', $filters['partner_type']);
        }
        if (!empty($filters['state'])) {
            $this->db->where('p.state', $filters['state']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('p.date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('p.date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.name', $filters['search']);
            $this->db->or_like('p.memo', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('p.date', 'DESC');
        $this->db->order_by('p.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_for_move($move_id)
    {
        $this->db->where('move_id', $move_id);
        return $this->db->get('acc_payments')->result();
    }

    /**
     * Step 7: Register a payment against an invoice/bill.
     *
     * Flow:
     *  1. Create acc_payments record
     *  2. Create a journal entry (acc_moves) in the payment journal
     *  3. Build payment lines via engine (DR Bank/CR AR or DR AP/CR Bank)
     *  4. Post the journal entry
     *  5. Auto-reconcile payment line against the invoice AR/AP line
     *  6. Update invoice amount_residual + payment_state
     */
    public function register_payment($data)
    {
        $move_id      = !empty($data['move_id']) ? (int)$data['move_id'] : null;
        $partner_type = $data['partner_type'] ?? 'customer';
        $amount       = (float)$data['amount'];
        $journal_id   = (int)$data['journal_id'];
        $date         = $data['date'] ?? date('Y-m-d');
        $partner_id   = !empty($data['partner_id']) ? (int)$data['partner_id'] : null;
        // Explicit payment_type from caller wins (e.g. inbound for vendor credit note receipts)
        $payment_type = $data['payment_type'] ?? ($partner_type === 'customer' ? 'inbound' : 'outbound');

        $settings = $this->xb_config->get_all_settings();

        // 1. Insert acc_payments record
        $payment_name = $this->_next_payment_sequence($journal_id, $date, $partner_type);
        $this->db->insert('acc_payments', [
            'name'         => $payment_name,
            'journal_id'   => $journal_id,
            'date'         => $date,
            'amount'       => $amount,
            'memo'         => $data['memo'] ?? '',
            'state'        => 'posted',
            'payment_type' => $payment_type,
            'partner_type' => $partner_type,
            'partner_id'   => $partner_id,
            'move_id'      => $move_id,
            'created_by'   => get_staff_user_id(),
        ]);
        $payment_id = $this->db->insert_id();

        // 2+3. Create and post the payment journal entry
        $payment_data = [
            'amount'       => $amount,
            'partner_id'   => $partner_id,
            'partner_type' => $partner_type,
            'payment_type' => $payment_type,
            'journal_id'   => $journal_id,
            'date'         => $date,
        ];
        $lines = $this->xb_engine->build_payment_journal_lines($payment_data, $settings);

        // 4. Determine accounting rule for AR/AP account used in this entry
        $ar_ap_account_id = $this->_resolve_ar_ap_account($partner_type, $settings);

        $move_type = ($partner_type === 'customer') ? 'payment' : 'payment';
        $this->db->insert('acc_moves', [
            'move_type'    => $move_type,
            'journal_id'   => $journal_id,
            'date'         => $date,
            'ref'          => $payment_name,
            'narration'    => $data['memo'] ?? 'Payment',
            'partner_id'   => $partner_id,
            'partner_type' => $partner_type,
            'amount_total' => $amount,
            'state'        => 'draft',
            'created_by'   => get_staff_user_id(),
        ]);
        $journal_move_id = $this->db->insert_id();

        foreach ($lines as $line) {
            $line['move_id'] = $journal_move_id;
            $this->db->insert('acc_move_lines', $line);
        }

        // Post the payment move (no accounting line generation needed — lines already built)
        try {
            // Directly update state to posted (payment entries are pre-balanced by build_payment_journal_lines)
            $seq = $this->xb_engine->get_next_sequence($journal_id, $date);
            $this->db->where('id', $journal_move_id)->update('acc_moves', [
                'state' => 'posted',
                'name'  => $seq,
            ]);
        } catch (Exception $e) {
            // Non-fatal: payment is registered even if journal posting fails
        }

        // 5. Auto-reconcile payment line against invoice AR/AP line
        if ($move_id && $ar_ap_account_id) {
            $this->_reconcile_payment_against_invoice($move_id, $journal_move_id, $ar_ap_account_id, $partner_type);
        }

        // 6. Update invoice residual and payment_state
        if ($move_id) {
            $invoice = $this->xb_engine->get_move($move_id);
            if ($invoice) {
                $new_residual = max(0, (float)$invoice->amount_residual - $amount);
                $new_state    = $new_residual <= 0.005 ? 'paid' : 'partial';
                $this->db->where('id', $move_id)->update('acc_moves', [
                    'amount_residual' => round($new_residual, 4),
                    'payment_state'   => $new_state,
                ]);
            }
        }

        return $payment_id;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function _reconcile_payment_against_invoice($invoice_move_id, $payment_move_id, $ar_ap_account_id, $partner_type)
    {
        // Invoice line: DR AR (customer) or CR AP (vendor)
        if ($partner_type === 'customer') {
            $this->db->where('move_id', $invoice_move_id)->where('account_id', $ar_ap_account_id)->where('debit >', 0)->where('reconciled', 0);
            $inv_line = $this->db->get('acc_move_lines')->row();

            $this->db->where('move_id', $payment_move_id)->where('account_id', $ar_ap_account_id)->where('credit >', 0)->where('reconciled', 0);
            $pay_line = $this->db->get('acc_move_lines')->row();
        } else {
            $this->db->where('move_id', $invoice_move_id)->where('account_id', $ar_ap_account_id)->where('credit >', 0)->where('reconciled', 0);
            $inv_line = $this->db->get('acc_move_lines')->row();

            $this->db->where('move_id', $payment_move_id)->where('account_id', $ar_ap_account_id)->where('debit >', 0)->where('reconciled', 0);
            $pay_line = $this->db->get('acc_move_lines')->row();
        }

        if ($inv_line && $pay_line) {
            $this->xb_engine->reconcile_lines([$inv_line->id], [$pay_line->id]);
        }
    }

    private function _resolve_ar_ap_account($partner_type, array $settings)
    {
        if ($partner_type === 'customer') {
            return $this->_get_account_by_setting('default_receivable_account', $settings)
                ?: $this->_get_account_by_setting('sales_receivable_account', $settings);
        }
        return $this->_get_account_by_setting('default_payable_account', $settings)
            ?: $this->_get_account_by_setting('purchase_payable_account', $settings);
    }

    private function _get_account_by_setting($key, array $settings)
    {
        $val = $settings[$key] ?? null;
        if (!$val) return null;
        if (is_numeric($val) && (int)$val > 0) {
            $acc = $this->db->where('id', (int)$val)->get('acc_accounts')->row();
            if ($acc) return (int)$acc->id;
        }
        $acc = $this->db->where('code', $val)->get('acc_accounts')->row();
        return $acc ? (int)$acc->id : null;
    }

    private function _next_payment_sequence($journal_id, $date, $partner_type)
    {
        $journal = $this->db->where('id', $journal_id)->get('acc_journals')->row();
        $code    = $journal ? $journal->code : 'PAY';
        $prefix  = $code . '/' . date('Y', strtotime($date)) . '/';

        $this->db->like('name', $prefix, 'after')->order_by('id', 'DESC')->limit(1);
        $last = $this->db->get('acc_payments')->row();

        if ($last && $last->name && strpos($last->name, $prefix) === 0) {
            $n = (int)str_replace($prefix, '', $last->name);
            return $prefix . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }
}
