<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_engine_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MOVE READ
    // ─────────────────────────────────────────────────────────────────────────

    public function get_move($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('acc_moves')->row();
    }

    public function get_move_lines($move_id)
    {
        $this->db->where('move_id', $move_id);
        $this->db->order_by('sequence', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('acc_move_lines')->result();
    }

    public function get_moves_list($filters = [])
    {
        $this->db->select('acc_moves.*, acc_journals.name as journal_name');
        $this->db->from('acc_moves');
        $this->db->join('acc_journals', 'acc_journals.id = acc_moves.journal_id', 'left');

        if (!empty($filters['journal_id'])) {
            $this->db->where('acc_moves.journal_id', $filters['journal_id']);
        }
        if (!empty($filters['state'])) {
            $this->db->where('acc_moves.state', $filters['state']);
        }
        if (!empty($filters['move_type'])) {
            if (is_array($filters['move_type'])) {
                $this->db->where_in('acc_moves.move_type', $filters['move_type']);
            } else {
                $this->db->where('acc_moves.move_type', $filters['move_type']);
            }
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('acc_moves.date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('acc_moves.date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('acc_moves.name', $filters['search']);
            $this->db->or_like('acc_moves.ref', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('acc_moves.date', 'DESC');
        $this->db->order_by('acc_moves.id', 'DESC');

        return $this->db->get()->result();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MANUAL JOURNAL ENTRY SAVE (for journal_entry_form — type='entry')
    // ─────────────────────────────────────────────────────────────────────────

    public function save_entry($data, $id = null)
    {
        // Server-side balance check: total debits must equal total credits
        if (!empty($data['lines']) && is_array($data['lines'])) {
            $total_dr = 0;
            $total_cr = 0;
            foreach ($data['lines'] as $line) {
                if (empty($line['account_id'])) continue;
                $total_dr += (float)($line['debit']  ?? 0);
                $total_cr += (float)($line['credit'] ?? 0);

                // Reject posting to parent/group accounts (accounts that have sub-accounts)
                $acc = $this->db->select('id,code,name')->where('id', (int)$line['account_id'])->get(db_prefix() . 'acc_accounts')->row();
                if ($acc) {
                    $child_count = $this->db->where('parent_id', (int)$acc->id)->count_all_results(db_prefix() . 'acc_accounts');
                    if ($child_count > 0) {
                        return sprintf(
                            "Cannot post to group account %s '%s'. It has %d sub-accounts. Select a specific leaf account instead.",
                            $acc->code, $acc->name, $child_count
                        );
                    }
                }
            }
            if (abs($total_dr - $total_cr) > 0.01) {
                return sprintf(
                    'Journal entry is not balanced. Total Debit: %s  Total Credit: %s  Difference: %s',
                    number_format($total_dr, 2),
                    number_format($total_cr, 2),
                    number_format(abs($total_dr - $total_cr), 2)
                );
            }
        }

        $cur_id     = !empty($data['currency_id']) ? (int)$data['currency_id'] : 1;
        $exch_rate  = !empty($data['exchange_rate']) ? (float)$data['exchange_rate'] : 1.0;
        $is_foreign = ($cur_id !== 1);

        $move_data = [
            'journal_id'   => $data['journal_id'],
            'date'         => $data['date'],
            'ref'          => $data['ref'] ?? null,
            'narration'    => $data['narration'] ?? null,
            'move_type'    => $data['move_type'] ?? 'entry',
            'partner_id'   => !empty($data['partner_id']) ? (int)$data['partner_id'] : null,
            'partner_type' => $data['partner_type'] ?? null,
            'currency_id'  => $cur_id,
            'exchange_rate'=> $exch_rate,
            'amount_untaxed' => (float)($data['amount_untaxed'] ?? 0),
            'amount_tax'     => (float)($data['amount_tax'] ?? 0),
            'amount_total'   => (float)($data['amount_total'] ?? 0),
            'amount_residual'=> (float)($data['amount_residual'] ?? ($data['amount_total'] ?? 0)),
            'invoice_payment_term_id' => !empty($data['invoice_payment_term_id']) ? (int)$data['invoice_payment_term_id'] : null,
        ];

        if (!$id) {
            $move_data['state']      = 'draft';
            $move_data['created_by'] = get_staff_user_id();
            $this->db->insert('acc_moves', $move_data);
            $id = $this->db->insert_id();
        } else {
            $move_data['updated_by'] = get_staff_user_id();
            $this->db->where('id', $id)->update('acc_moves', $move_data);
        }

        if (isset($data['lines']) && is_array($data['lines'])) {
            $existing_ids = array_column($this->get_move_lines($id), 'id');
            $kept_ids     = [];

            foreach ($data['lines'] as $line) {
                if (empty($line['account_id'])) continue;

                $dr_fc = (float)($line['debit']  ?? 0);
                $cr_fc = (float)($line['credit'] ?? 0);
                // When foreign currency: amounts entered in foreign, stored as KES (×rate), amount_currency = foreign
                $dr_kes = $is_foreign ? round($dr_fc * $exch_rate, 4) : $dr_fc;
                $cr_kes = $is_foreign ? round($cr_fc * $exch_rate, 4) : $cr_fc;

                $line_data = [
                    'move_id'    => $id,
                    'account_id' => $line['account_id'],
                    'partner_id' => !empty($line['partner_id']) ? $line['partner_id'] : null,
                    'name'       => $line['name'] ?? '',
                    'quantity'   => (float)($line['quantity'] ?? 1),
                    'price_unit' => (float)($line['price_unit'] ?? 0),
                    'debit'      => $dr_kes,
                    'credit'     => $cr_kes,
                    'balance'    => $dr_kes - $cr_kes,
                ];
                if ($is_foreign) {
                    $line_data['currency_id']     = $cur_id;
                    $line_data['amount_currency'] = $dr_fc - $cr_fc;
                }

                if (!empty($line['id'])) {
                    $this->db->where('id', $line['id'])->update('acc_move_lines', $line_data);
                    $kept_ids[] = (int)$line['id'];
                } else {
                    $this->db->insert('acc_move_lines', $line_data);
                    $kept_ids[] = $this->db->insert_id();
                }
            }

            $to_delete = array_diff($existing_ids, $kept_ids);
            if (!empty($to_delete)) {
                $this->db->where_in('id', $to_delete)->delete('acc_move_lines');
            }
        }

        return ['id' => $id];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATE MACHINE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post a move: for invoice types, auto-generates accounting lines from product lines
     * then validates DR = CR before stamping state = 'posted'.
     */
    public function post_move($move_id)
    {
        $move = $this->get_move($move_id);

        if (!$move || $move->state !== 'draft') {
            throw new Exception('Move is not in draft state.');
        }

        // Auto-generate accounting lines for invoice-type documents
        $invoice_types = ['out_invoice', 'in_invoice', 'out_refund', 'in_refund', 'out_receipt', 'in_receipt'];
        if (in_array($move->move_type, $invoice_types)) {
            $this->_generate_accounting_lines($move_id);
        }

        // Validate balance
        $lines = $this->get_move_lines($move_id);
        $total_debit  = 0;
        $total_credit = 0;
        foreach ($lines as $line) {
            $total_debit  += (float)$line->debit;
            $total_credit += (float)$line->credit;
        }

        if (abs($total_debit - $total_credit) > 0.01) {
            throw new Exception(sprintf(
                'Journal entry not balanced. Debit: %s, Credit: %s',
                number_format($total_debit, 2),
                number_format($total_credit, 2)
            ));
        }

        // Enforce bookkeeping discipline: reject posting to group/parent accounts
        foreach ($lines as $line) {
            if (empty($line->account_id)) continue;
            $child_count = $this->db->where('parent_id', (int)$line->account_id)->count_all_results(db_prefix() . 'acc_accounts');
            if ($child_count > 0) {
                $acc = $this->db->select('code,name')->where('id', (int)$line->account_id)->get(db_prefix() . 'acc_accounts')->row();
                throw new Exception(sprintf(
                    "Cannot post to group account %s '%s'. It has %d sub-accounts. Use a specific leaf account.",
                    $acc ? $acc->code : $line->account_id,
                    $acc ? $acc->name : '',
                    $child_count
                ));
            }
        }

        $sequence = $this->get_next_sequence($move->journal_id, $move->date);

        $this->db->where('id', $move_id)->update('acc_moves', [
            'state' => 'posted',
            'name'  => $sequence,
        ]);

        // Auto-reconcile vendor credit note with its source bill
        if ($move->move_type === 'in_refund' && !empty($move->source_move_id)) {
            $this->_reconcile_credit_note_with_bill($move_id, (int)$move->source_move_id);
        }

        // Post analytic lines from analytic_distribution on product lines
        $this->load->model('xetuu_books/Xb_analytic_model', 'xb_analytic_eng');
        $this->xb_analytic_eng->post_analytic_lines($move_id);

        return true;
    }

    private function _reconcile_credit_note_with_bill($credit_note_id, $bill_id)
    {
        $this->load->model('xetuu_books/Xb_config_model', 'xb_cfg_rec');
        $settings = $this->xb_cfg_rec->get_all_settings();

        $ap_id = $this->_get_setting_account_id('default_payable_account', $settings)
              ?: $this->_get_setting_account_id('purchase_payable_account', $settings);
        if (!$ap_id) return;

        // Credit note posts DR AP (debit > 0 on AP account)
        $this->db->where('move_id', $credit_note_id)->where('account_id', $ap_id)->where('debit >', 0)->where('reconciled', 0);
        $cn_ap_line = $this->db->get('acc_move_lines')->row();

        // Original bill posts CR AP (credit > 0 on AP account)
        $this->db->where('move_id', $bill_id)->where('account_id', $ap_id)->where('credit >', 0)->where('reconciled', 0);
        $bill_ap_line = $this->db->get('acc_move_lines')->row();

        if (!$cn_ap_line || !$bill_ap_line) return;

        $this->reconcile_lines([$cn_ap_line->id], [$bill_ap_line->id]);

        // Update bill residual and payment_state
        $bill        = $this->get_move($bill_id);
        $credit_note = $this->get_move($credit_note_id);
        if ($bill && $credit_note) {
            $new_residual = max(0, (float)$bill->amount_residual - (float)$credit_note->amount_total);
            $new_state    = $new_residual <= 0.005 ? 'paid' : 'partial';
            $this->db->where('id', $bill_id)->update('acc_moves', [
                'amount_residual' => round($new_residual, 4),
                'payment_state'   => $new_state,
            ]);
        }
    }

    public function cancel_move($move_id)
    {
        $this->db->where('id', $move_id)->update('acc_moves', ['state' => 'cancel']);
        return true;
    }

    public function reset_move_to_draft($move_id)
    {
        $this->db->where('id', $move_id)->update('acc_moves', [
            'state' => 'draft',
            'name'  => null,
        ]);
        return true;
    }

    public function delete_move($move_id)
    {
        $move = $this->get_move($move_id);
        if ($move && $move->state === 'draft') {
            $this->db->where('id', $move_id)->delete('acc_moves'); // CASCADE removes lines
            return true;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCOUNTING LINE GENERATION (Step 3)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Wipes existing non-product lines, then rebuilds them from product lines + settings.
     * Called automatically by post_move() for invoice-type documents.
     */
    private function _generate_accounting_lines($move_id)
    {
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');

        $move = $this->get_move($move_id);
        if (!$move) throw new Exception('Move not found.');

        $settings = $this->xb_config_eng->get_all_settings();

        // Remove previously generated accounting lines (keep product lines)
        $this->db->where('move_id', $move_id)
                 ->where('display_type !=', 'product')
                 ->delete('acc_move_lines');

        $this->db->where('move_id', $move_id)->where('display_type', 'product');
        $product_lines = $this->db->get('acc_move_lines')->result();

        if (empty($product_lines)) {
            throw new Exception('No line items found on this document.');
        }

        switch ($move->move_type) {
            case 'out_invoice':
            case 'out_receipt':
                $lines = $this->build_invoice_journal_lines($move, $product_lines, $settings);
                break;
            case 'in_invoice':
            case 'in_receipt':
                $lines = $this->build_bill_journal_lines($move, $product_lines, $settings);
                break;
            case 'out_refund':
                $lines = $this->build_credit_note_journal_lines($move, $product_lines, $settings);
                break;
            case 'in_refund':
                $lines = $this->build_vendor_refund_journal_lines($move, $product_lines, $settings);
                break;
            default:
                return; // manual entries: lines already set by save_entry()
        }

        foreach ($lines as $line) {
            $line['move_id'] = $move_id;
            $this->db->insert('acc_move_lines', $line);
        }
    }

    /**
     * Customer Invoice (out_invoice):
     *   DR  Accounts Receivable       amount_total
     *   CR  Sales Revenue             amount per line (ex-tax)
     *   CR  VAT Output                tax amount
     */
    public function build_invoice_journal_lines($move, array $product_lines, array $settings)
    {
        $ar_id        = $this->_get_setting_account_id('default_receivable_account', $settings)
                     ?: $this->_get_setting_account_id('sales_receivable_account', $settings);
        $default_rev  = $this->_get_setting_account_id('sales_revenue_account', $settings)
                     ?: $this->_get_setting_account_id('default_income_account', $settings);

        $rate       = max((float)($move->exchange_rate ?? 1), 0.000001);
        $cur_id     = (int)($move->currency_id ?? 1);
        $is_foreign = ($cur_id !== 1);

        $lines    = [];
        $tax_pool = [];

        foreach ($product_lines as $pl) {
            $subtotal_fc  = (float)$pl->price_subtotal; // foreign-currency ex-tax
            $subtotal_kes = round($subtotal_fc * $rate, 4);
            $rev_acc      = ((int)$pl->account_id > 0) ? (int)$pl->account_id : $default_rev;

            $line = [
                'display_type'    => '',
                'account_id'      => $rev_acc,
                'partner_id'      => $move->partner_id,
                'name'            => $pl->name,
                'quantity'        => (float)$pl->quantity,
                'price_unit'      => (float)$pl->price_unit,
                'price_subtotal'  => $subtotal_fc,
                'debit'           => 0,
                'credit'          => $subtotal_kes,
                'balance'         => -$subtotal_kes,
                'date'            => $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']      = $cur_id;
                $line['amount_currency']  = -$subtotal_fc;
            }
            $lines[] = $line;

            if ($pl->tax_line_id) {
                $this->_accumulate_tax($pl, $move, $settings, 'output', $tax_pool);
            }
        }

        foreach ($tax_pool as $key => $t) {
            $tax_fc  = $t['amount'];
            $tax_kes = round($tax_fc * $rate, 4);
            $line = [
                'display_type' => 'tax',
                'account_id'   => $t['account_id'],
                'partner_id'   => $move->partner_id,
                'name'         => 'Tax',
                'tax_line_id'  => $t['tax_id'],
                'debit'        => 0,
                'credit'       => $tax_kes,
                'balance'      => -$tax_kes,
                'date'         => $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']     = $cur_id;
                $line['amount_currency'] = -$tax_fc;
            }
            $lines[] = $line;
        }

        if ($ar_id) {
            $total_fc  = (float)$move->amount_total;
            $total_kes = round($total_fc * $rate, 4);
            $line = [
                'display_type'  => 'payment_term',
                'account_id'    => $ar_id,
                'partner_id'    => $move->partner_id,
                'name'          => 'Accounts Receivable',
                'debit'         => $total_kes,
                'credit'        => 0,
                'balance'       => $total_kes,
                'date'          => $move->date,
                'date_maturity' => $move->invoice_date_due ?: $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']     = $cur_id;
                $line['amount_currency'] = $total_fc;
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Vendor Bill (in_invoice):
     *   DR  Expense Account           amount per line (ex-tax)
     *   DR  VAT Input                 tax amount
     *   CR  Accounts Payable          amount_total
     */
    public function build_bill_journal_lines($move, array $product_lines, array $settings)
    {
        $ap_id       = $this->_get_setting_account_id('default_payable_account', $settings)
                    ?: $this->_get_setting_account_id('purchase_payable_account', $settings);
        $default_exp = $this->_get_setting_account_id('purchase_expense_account', $settings)
                    ?: $this->_get_setting_account_id('default_expense_account', $settings);

        $rate       = max((float)($move->exchange_rate ?? 1), 0.000001);
        $cur_id     = (int)($move->currency_id ?? 1);
        $is_foreign = ($cur_id !== 1);

        $lines    = [];
        $tax_pool = [];

        foreach ($product_lines as $pl) {
            $subtotal_fc  = (float)$pl->price_subtotal;
            $subtotal_kes = round($subtotal_fc * $rate, 4);
            $exp_acc      = ((int)$pl->account_id > 0) ? (int)$pl->account_id : $default_exp;

            $line = [
                'display_type'   => '',
                'account_id'     => $exp_acc,
                'partner_id'     => $move->partner_id,
                'name'           => $pl->name,
                'quantity'       => (float)$pl->quantity,
                'price_unit'     => (float)$pl->price_unit,
                'price_subtotal' => $subtotal_fc,
                'debit'          => $subtotal_kes,
                'credit'         => 0,
                'balance'        => $subtotal_kes,
                'date'           => $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']     = $cur_id;
                $line['amount_currency'] = $subtotal_fc;
            }
            $lines[] = $line;

            if ($pl->tax_line_id) {
                $this->_accumulate_tax($pl, $move, $settings, 'input', $tax_pool);
            }
        }

        foreach ($tax_pool as $t) {
            $tax_fc  = $t['amount'];
            $tax_kes = round($tax_fc * $rate, 4);
            $line = [
                'display_type' => 'tax',
                'account_id'   => $t['account_id'],
                'partner_id'   => $move->partner_id,
                'name'         => 'Tax',
                'tax_line_id'  => $t['tax_id'],
                'debit'        => $tax_kes,
                'credit'       => 0,
                'balance'      => $tax_kes,
                'date'         => $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']     = $cur_id;
                $line['amount_currency'] = $tax_fc;
            }
            $lines[] = $line;
        }

        if ($ap_id) {
            $total_fc  = (float)$move->amount_total;
            $total_kes = round($total_fc * $rate, 4);
            $line = [
                'display_type'  => 'payment_term',
                'account_id'    => $ap_id,
                'partner_id'    => $move->partner_id,
                'name'          => 'Accounts Payable',
                'debit'         => 0,
                'credit'        => $total_kes,
                'balance'       => -$total_kes,
                'date'          => $move->date,
                'date_maturity' => $move->invoice_date_due ?: $move->date,
            ];
            if ($is_foreign) {
                $line['currency_id']     = $cur_id;
                $line['amount_currency'] = -$total_fc;
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Customer Credit Note (out_refund) — reverse of invoice:
     *   DR  Sales Revenue             amount per line (ex-tax)
     *   DR  VAT Output                tax amount
     *   CR  Accounts Receivable       amount_total
     */
    public function build_credit_note_journal_lines($move, array $product_lines, array $settings)
    {
        $invoice_lines = $this->build_invoice_journal_lines($move, $product_lines, $settings);
        foreach ($invoice_lines as &$line) {
            list($line['debit'], $line['credit']) = [$line['credit'], $line['debit']];
            $line['balance'] = $line['debit'] - $line['credit'];
            if (isset($line['amount_currency'])) {
                $line['amount_currency'] = -$line['amount_currency'];
            }
        }
        return $invoice_lines;
    }

    /**
     * Vendor Refund (in_refund) — reverse of bill:
     *   CR  Expense Account           amount per line (ex-tax)
     *   CR  VAT Input                 tax amount
     *   DR  Accounts Payable          amount_total
     */
    public function build_vendor_refund_journal_lines($move, array $product_lines, array $settings)
    {
        $bill_lines = $this->build_bill_journal_lines($move, $product_lines, $settings);
        foreach ($bill_lines as &$line) {
            list($line['debit'], $line['credit']) = [$line['credit'], $line['debit']];
            $line['balance'] = $line['debit'] - $line['credit'];
        }
        return $bill_lines;
    }

    /**
     * Payment journal lines (used by Xb_payment_model):
     * Inbound (customer payment):
     *   DR  Bank/Cash                 amount
     *   CR  Accounts Receivable       amount
     *
     * Outbound (vendor payment):
     *   DR  Accounts Payable          amount
     *   CR  Bank/Cash                 amount
     */
    public function build_payment_journal_lines($payment_data, array $settings)
    {
        $amount       = (float)$payment_data['amount'];
        $partner_id   = !empty($payment_data['partner_id']) ? (int)$payment_data['partner_id'] : null;
        $partner_type = $payment_data['partner_type'] ?? 'customer';
        $date         = $payment_data['date'] ?? date('Y-m-d');

        // Explicit payment_type wins; fall back to convention (customer=inbound, supplier=outbound)
        $payment_type = $payment_data['payment_type'] ?? ($partner_type === 'customer' ? 'inbound' : 'outbound');

        // Resolve bank/cash account from the selected journal
        $journal_id  = (int)($payment_data['journal_id'] ?? 0);
        $bank_acc_id = null;
        if ($journal_id) {
            $j = $this->db->where('id', $journal_id)->get('acc_journals')->row();
            $bank_acc_id = $j ? (int)$j->account_id : null;
        }
        if (!$bank_acc_id) {
            $bank_acc_id = $this->_get_setting_account_id('default_bank_account', $settings)
                        ?: $this->_get_setting_account_id('outstanding_receipts_account', $settings);
        }

        // Resolve the AR or AP control account based on partner type
        if ($partner_type === 'customer') {
            $ctrl_acc_id = $this->_get_setting_account_id('default_receivable_account', $settings)
                        ?: $this->_get_setting_account_id('sales_receivable_account', $settings);
            $ctrl_name   = 'Accounts Receivable';
        } else {
            $ctrl_acc_id = $this->_get_setting_account_id('default_payable_account', $settings)
                        ?: $this->_get_setting_account_id('purchase_payable_account', $settings);
            $ctrl_name   = 'Accounts Payable';
        }

        $lines = [];

        if ($payment_type === 'inbound') {
            // Money coming IN: DR Bank / CR Control-Account
            // Used for: customer payments, vendor credit note receipts (vendor sends cash back)
            if ($bank_acc_id) {
                $lines[] = ['display_type' => '', 'account_id' => $bank_acc_id, 'partner_id' => $partner_id,
                    'name' => 'Payment Received', 'debit' => $amount, 'credit' => 0, 'balance' => $amount, 'date' => $date];
            }
            if ($ctrl_acc_id) {
                $lines[] = ['display_type' => '', 'account_id' => $ctrl_acc_id, 'partner_id' => $partner_id,
                    'name' => $ctrl_name, 'debit' => 0, 'credit' => $amount, 'balance' => -$amount, 'date' => $date];
            }
        } else {
            // Money going OUT: DR Control-Account / CR Bank
            // Used for: vendor payments, customer refunds (paying back a customer)
            if ($ctrl_acc_id) {
                $lines[] = ['display_type' => '', 'account_id' => $ctrl_acc_id, 'partner_id' => $partner_id,
                    'name' => $ctrl_name, 'debit' => $amount, 'credit' => 0, 'balance' => $amount, 'date' => $date];
            }
            if ($bank_acc_id) {
                $lines[] = ['display_type' => '', 'account_id' => $bank_acc_id, 'partner_id' => $partner_id,
                    'name' => 'Payment Sent', 'debit' => 0, 'credit' => $amount, 'balance' => -$amount, 'date' => $date];
            }
        }

        return $lines;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SEQUENCE
    // ─────────────────────────────────────────────────────────────────────────

    public function get_next_sequence($journal_id, $date)
    {
        $journal = $this->db->where('id', $journal_id)->get('acc_journals')->row();

        $code = $journal ? $journal->code : 'MISC';
        $year = date('Y', strtotime($date));
        $prefix = $code . '/' . $year . '/';

        $this->db->like('name', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get('acc_moves')->row();

        if ($last && $last->name && strpos($last->name, $prefix) === 0) {
            $last_num = (int)str_replace($prefix, '', $last->name);
            return $prefix . str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCOUNT BALANCE
    // ─────────────────────────────────────────────────────────────────────────

    public function get_account_balance($account_id, $date_from = null, $date_to = null)
    {
        $this->db->select('
            COALESCE(SUM(aml.debit),0)   AS debit,
            COALESCE(SUM(aml.credit),0)  AS credit,
            COALESCE(SUM(aml.balance),0) AS balance
        ');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->where('aml.account_id', $account_id);
        $this->db->where('am.state', 'posted');
        if ($date_from) { $this->db->where('am.date >=', $date_from); }
        if ($date_to)   { $this->db->where('am.date <=', $date_to); }
        return $this->db->get()->row();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECONCILIATION
    // ─────────────────────────────────────────────────────────────────────────

    public function get_unreconciled_statement_lines($journal_id)
    {
        $this->db->where('journal_id', $journal_id)
                 ->where('is_reconciled', 0)
                 ->order_by('date', 'DESC');
        return $this->db->get('acc_bank_statement_lines')->result();
    }

    public function get_unreconciled_move_lines($journal_id)
    {
        $journal = $this->db->where('id', $journal_id)->get('acc_journals')->row();
        if (!$journal || !$journal->account_id) return [];

        $this->db->select('aml.*, am.name as move_name, am.date as move_date, aa.code as account_code, aa.name as account_name');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_moves am', 'am.id = aml.move_id');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id');
        $this->db->where('aml.account_id', $journal->account_id);
        $this->db->where('am.state', 'posted');
        $this->db->where('aml.reconciled', 0);
        $this->db->order_by('am.date', 'DESC');
        return $this->db->get()->result();
    }

    public function reconcile_lines($debit_line_ids, $credit_line_ids)
    {
        if (empty($debit_line_ids) || empty($credit_line_ids)) return false;

        $all_ids = array_merge((array)$debit_line_ids, (array)$credit_line_ids);
        $this->db->where_in('id', $all_ids)->update('acc_move_lines', ['reconciled' => 1]);

        $recon_name = 'RECON/' . date('Y') . '/' . str_pad($this->_next_recon_seq(), 4, '0', STR_PAD_LEFT);
        $this->db->insert('acc_full_reconcile', ['name' => $recon_name]);

        return true;
    }

    public function reconcile_bank_statement($statement_line_ids, $ledger_line_ids)
    {
        if (empty($statement_line_ids) || empty($ledger_line_ids)) return false;

        $st_ids = (array)$statement_line_ids;
        $ll_ids = (array)$ledger_line_ids;

        // Verify totals match (sanity check)
        $this->db->select('SUM(amount) as s_total')->where_in('id', $st_ids);
        $st_total = round($this->db->get('acc_bank_statement_lines')->row()->s_total ?? 0, 4);

        $this->db->select('SUM(debit - credit) as l_total')->where_in('id', $ll_ids);
        $ll_total = round($this->db->get('acc_move_lines')->row()->l_total ?? 0, 4);

        if (abs($st_total - $ll_total) > 0.01) {
            throw new Exception("Reconciliation failed: The totals do not match exactly. (Bank: $st_total, Ledger: $ll_total)");
        }

        // Mark ledger lines
        $this->db->where_in('id', $ll_ids)->update('acc_move_lines', ['reconciled' => 1]);

        // Mark statement lines
        $this->db->where_in('id', $st_ids)->update('acc_bank_statement_lines', ['is_reconciled' => 1, 'move_id' => $ll_ids[0]]);

        return true;
    }

    public function import_bank_statement($journal_id, $file)
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'ofx', 'txt'])) {
            throw new Exception('Unsupported file format. Use CSV or OFX.');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) throw new Exception('Could not read uploaded file.');

        $count   = 0;
        $headers = null;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (!$headers) {
                $headers = array_map(fn($h) => strtolower(trim($h)), $row);
                continue;
            }
            $line   = array_combine($headers, array_pad($row, count($headers), ''));
            $date   = $line['date']        ?? $line['transaction date'] ?? $line['value date'] ?? null;
            $desc   = $line['description'] ?? $line['narrative']       ?? $line['memo']       ?? '';
            $amount = $line['amount']       ?? $line['credit']          ?? $line['debit']      ?? 0;
            $ref    = $line['reference']    ?? $line['cheque no']       ?? '';

            if (!$date || !is_numeric(str_replace([',', ' '], '', $amount))) continue;

            $ts = strtotime($date);
            if (!$ts) continue;

            $amount = (float)str_replace([',', ' '], '', $amount);
            if (isset($line['debit']) && !isset($line['amount'])) $amount *= -1;

            $this->db->insert('acc_bank_statement_lines', [
                'journal_id'  => $journal_id,
                'date'        => date('Y-m-d', $ts),
                'payment_ref' => $desc,
                'ref'         => $ref,
                'amount'      => $amount,
            ]);
            $count++;
        }

        fclose($handle);
        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT TERMS
    // ─────────────────────────────────────────────────────────────────────────

    public function compute_payment_terms($amount, $payment_term_id, $date)
    {
        $lines     = $this->db->where('payment_term_id', $payment_term_id)->order_by('id', 'ASC')->get('acc_payment_term_lines')->result();
        $result    = [];
        $remaining = (float)$amount;

        foreach ($lines as $line) {
            if ($line->value === 'percent') {
                $term_amount = ($amount * $line->value_amount) / 100;
            } elseif ($line->value === 'fixed') {
                $term_amount = (float)$line->value_amount;
            } else {
                $term_amount = $remaining;
            }

            $term_date = $line->delay_type === 'days_after_end_of_month'
                ? date('Y-m-d', strtotime(date('Y-m-t', strtotime($date)) . ' + ' . $line->nb_days . ' days'))
                : date('Y-m-d', strtotime($date . ' + ' . $line->nb_days . ' days'));

            $result[]   = ['date_maturity' => $term_date, 'amount' => $term_amount];
            $remaining -= $term_amount;
        }

        if (empty($lines)) {
            $result[] = ['date_maturity' => $date, 'amount' => $remaining];
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PERFEX HOOKS SYNC (backward-compat — keep old hook methods working)
    // ─────────────────────────────────────────────────────────────────────────

    public function sync_invoice_to_journal($invoice_id)
    {
        $this->load->model('invoices_model');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');

        $invoice = $this->invoices_model->get($invoice_id);
        if (!$invoice || in_array($invoice->status, [5, 6])) return false;

        $settings   = $this->xb_config_eng->get_all_settings();
        $journal_id = (int)($settings['default_sale_journal_id'] ?? 1);

        $this->db->where('move_type', 'out_invoice')->where('ref', 'INV-' . $invoice_id);
        $existing = $this->db->get('acc_moves')->row();

        $move_data = [
            'journal_id'     => $journal_id,
            'date'           => $invoice->date,
            'ref'            => 'INV-' . $invoice_id,
            'narration'      => 'Customer Invoice ' . format_invoice_number($invoice_id),
            'move_type'      => 'out_invoice',
            'partner_id'     => $invoice->clientid,
            'partner_type'   => 'customer',
            'amount_untaxed' => $invoice->subtotal,
            'amount_tax'     => $invoice->total_tax,
            'amount_total'   => $invoice->total,
            'amount_residual'=> $invoice->total,
            'lines'          => $this->_build_perfex_invoice_lines($invoice, $settings),
        ];

        if ($existing && $existing->state === 'posted') {
            $this->reset_move_to_draft($existing->id);
        }

        if (!is_array($move_data)) { return false; }
        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        if (!is_array($res)) { return false; }
        // Lines are already final accounting entries; bypass _generate_accounting_lines.
        try {
            $this->_post_move_direct($res['id']);
        } catch (Exception $e) {
            log_message('error', '[xetuu_books] sync_invoice_to_journal post failed for INV-' . $invoice_id . ': ' . $e->getMessage());
        }
        return $res;
    }

    public function cancel_invoice_journal($invoice_id)
    {
        $this->db->where('move_type', 'out_invoice')->where('ref', 'INV-' . $invoice_id);
        $move = $this->db->get('acc_moves')->row();
        if ($move) $this->cancel_move($move->id);
    }

    public function sync_payment_to_journal($payment_id)
    {
        $this->load->model('payments_model');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');

        $payment = $this->payments_model->get($payment_id);
        if (!$payment) return false;

        $settings   = $this->xb_config_eng->get_all_settings();
        $journal_id = (int)($settings['default_cash_journal_id'] ?? 3);

        $this->db->where('move_type', 'payment')->where('ref', 'PAY-' . $payment_id);
        $existing = $this->db->get('acc_moves')->row();

        $pdata = [
            'amount'       => $payment->amount,
            'partner_id'   => $payment->invoice_data?->clientid ?? null,
            'partner_type' => 'customer',
            'journal_id'   => $journal_id,
            'date'         => $payment->date,
        ];

        $move_data = [
            'journal_id'   => $journal_id,
            'date'         => $payment->date,
            'ref'          => 'PAY-' . $payment_id,
            'narration'    => 'Customer Payment ' . $payment->paymentid,
            'move_type'    => 'payment',
            'partner_id'   => $pdata['partner_id'],
            'partner_type' => 'customer',
            'amount_total' => $payment->amount,
            'lines'        => $this->build_payment_journal_lines($pdata, $settings),
        ];

        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        try {
            $this->post_move($res['id']);
            $ar_id = $this->_get_setting_account_id('default_receivable_account', $settings)
                  ?: $this->_get_setting_account_id('sales_receivable_account', $settings);
            if ($ar_id) {
                $this->_auto_reconcile_invoice_payment($payment->invoiceid, $res['id'], $ar_id);
            }
        } catch (Exception $e) {}

        return $res;
    }

    public function sync_expense_to_journal($expense_id)
    {
        $this->load->model('expenses_model');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');

        $expense = $this->expenses_model->get($expense_id);
        if (!$expense) return false;

        $settings   = $this->xb_config_eng->get_all_settings();
        $journal_id = (int)($settings['default_purchase_journal_id'] ?? 2);

        $this->db->where('move_type', 'in_invoice')->where('ref', 'EXP-' . $expense_id);
        $existing = $this->db->get('acc_moves')->row();

        $amount_tax   = $expense->tax > 0 ? ($expense->amount * ($expense->tax / 100)) : 0;
        $amount_total = $expense->amount + $amount_tax;

        $ap_id  = $this->_get_setting_account_id('purchase_payable_account', $settings)
               ?: $this->_get_setting_account_id('default_payable_account', $settings);
        $exp_id = $this->_get_setting_account_id('purchase_expense_account', $settings)
               ?: $this->_get_setting_account_id('default_expense_account', $settings);
        $vat_id = $this->_get_setting_account_id('vat_input_account', $settings);

        $lines = [];
        if ($exp_id) {
            $lines[] = ['account_id' => $exp_id, 'partner_id' => $expense->clientid,
                'name' => 'Expense', 'debit' => $expense->amount, 'credit' => 0];
        }
        if ($vat_id && $amount_tax > 0) {
            $lines[] = ['account_id' => $vat_id, 'partner_id' => $expense->clientid,
                'name' => 'VAT Input', 'debit' => $amount_tax, 'credit' => 0];
        }
        if ($ap_id) {
            $lines[] = ['account_id' => $ap_id, 'partner_id' => $expense->clientid,
                'name' => 'Accounts Payable', 'debit' => 0, 'credit' => $amount_total];
        }

        $move_data = [
            'journal_id'     => $journal_id,
            'date'           => $expense->date,
            'ref'            => 'EXP-' . $expense_id,
            'narration'      => 'Expense ' . $expense->expense_name,
            'move_type'      => 'in_invoice',
            'partner_id'     => $expense->clientid,
            'partner_type'   => 'vendor',
            'amount_untaxed' => $expense->amount,
            'amount_tax'     => $amount_tax,
            'amount_total'   => $amount_total,
            'lines'          => $lines,
        ];

        if ($existing && $existing->state === 'posted') {
            $this->reset_move_to_draft($existing->id);
        }

        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        if (!is_array($res)) { return false; }
        // Lines are already final accounting entries; bypass _generate_accounting_lines.
        try {
            $this->_post_move_direct($res['id']);
        } catch (Exception $e) {
            log_message('error', '[xetuu_books] sync_expense_to_journal post failed for EXP-' . $expense_id . ': ' . $e->getMessage());
        }
        return $res;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE MODULE SYNC
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sync a purchase invoice (tblpur_invoices) to an in_invoice acc_move.
     * DR Purchases/Expense + DR VAT Input | CR Accounts Payable
     */
    public function sync_purchase_invoice_to_journal($invoice_id)
    {
        if (!$this->db->table_exists('tblpur_invoices')) return false;

        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');
        $settings   = $this->xb_config_eng->get_all_settings();
        $journal_id = (int)($settings['default_purchase_journal_id'] ?? 2);

        $invoice = $this->db->where('id', $invoice_id)->get('tblpur_invoices')->row();
        if (!$invoice) return false;

        $this->db->where('move_type', 'in_invoice')->where('ref', 'PURINV-' . $invoice_id);
        $existing = $this->db->get('acc_moves')->row();

        $amount_untaxed = (float)($invoice->subtotal ?? 0);
        $amount_tax     = (float)($invoice->tax ?? 0);
        $amount_total   = (float)($invoice->total ?? ($amount_untaxed + $amount_tax));

        $ap_id  = $this->_get_setting_account_id('purchase_payable_account', $settings)
               ?: $this->_get_setting_account_id('default_payable_account', $settings);
        $exp_id = $this->_get_setting_account_id('purchase_account', $settings)
               ?: $this->_get_setting_account_id('default_expense_account', $settings);
        $vat_id = $this->_get_setting_account_id('vat_input_account', $settings);

        $lines = [];
        if ($exp_id) {
            $lines[] = ['account_id' => $exp_id, 'partner_id' => $invoice->vendor,
                'name' => 'Purchases', 'debit' => $amount_untaxed, 'credit' => 0];
        }
        if ($vat_id && $amount_tax > 0) {
            $lines[] = ['account_id' => $vat_id, 'partner_id' => $invoice->vendor,
                'name' => 'VAT Input', 'debit' => $amount_tax, 'credit' => 0];
        }
        if ($ap_id) {
            $lines[] = ['account_id' => $ap_id, 'partner_id' => $invoice->vendor,
                'name' => 'Accounts Payable', 'debit' => 0, 'credit' => $amount_total];
        }
        if (empty($lines)) return false;

        $invoice_number = $invoice->invoice_number ?: ($invoice->number ?: $invoice_id);
        $move_data = [
            'journal_id'     => $journal_id,
            'date'           => $invoice->invoice_date ?: date('Y-m-d'),
            'ref'            => 'PURINV-' . $invoice_id,
            'narration'      => 'Purchase Invoice #' . $invoice_number,
            'move_type'      => 'in_invoice',
            'partner_id'     => $invoice->vendor,
            'partner_type'   => 'vendor',
            'amount_untaxed' => $amount_untaxed,
            'amount_tax'     => $amount_tax,
            'amount_total'   => $amount_total,
            'lines'          => $lines,
        ];

        if ($existing && $existing->state === 'posted') { $this->reset_move_to_draft($existing->id); }

        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        if (!is_array($res)) return false;
        try {
            $this->_post_move_direct($res['id']);
        } catch (Exception $e) {
            log_message('error', '[xetuu_books] sync_purchase_invoice failed for PURINV-' . $invoice_id . ': ' . $e->getMessage());
        }
        return $res;
    }

    /**
     * Sync a purchase invoice payment (tblpur_invoice_payment) to a payment acc_move.
     * DR Accounts Payable | CR Bank/Cash
     */
    public function sync_purchase_payment_to_journal($payment_id)
    {
        if (!$this->db->table_exists('tblpur_invoice_payment')) return false;

        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');
        $settings = $this->xb_config_eng->get_all_settings();

        $payment = $this->db->where('id', $payment_id)->get('tblpur_invoice_payment')->row();
        if (!$payment) return false;

        $invoice   = $this->db->where('id', $payment->pur_invoice)->get('tblpur_invoices')->row();
        $vendor_id = $invoice ? (int)$invoice->vendor : null;

        $this->db->where('move_type', 'payment')->where('ref', 'PURPAY-' . $payment_id);
        $existing = $this->db->get('acc_moves')->row();

        $journal_id = (int)($settings['default_cash_journal_id'] ?? 3);
        $pdata = [
            'amount'       => (float)$payment->amount,
            'partner_id'   => $vendor_id,
            'partner_type' => 'vendor',
            'payment_type' => 'outbound',
            'journal_id'   => $journal_id,
            'date'         => $payment->date ?: date('Y-m-d'),
        ];

        $move_data = [
            'journal_id'   => $journal_id,
            'date'         => $payment->date ?: date('Y-m-d'),
            'ref'          => 'PURPAY-' . $payment_id,
            'narration'    => 'Purchase Payment',
            'move_type'    => 'payment',
            'partner_id'   => $vendor_id,
            'partner_type' => 'vendor',
            'amount_total' => (float)$payment->amount,
            'lines'        => $this->build_payment_journal_lines($pdata, $settings),
        ];

        if ($existing && $existing->state === 'posted') { $this->reset_move_to_draft($existing->id); }

        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        if (!is_array($res)) return false;
        try {
            $this->_post_move_direct($res['id']);
        } catch (Exception $e) {
            log_message('error', '[xetuu_books] sync_purchase_payment failed for PURPAY-' . $payment_id . ': ' . $e->getMessage());
        }
        return $res;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WAREHOUSE MODULE SYNC
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sync an approved goods receipt (tblgoods_receipt) to a journal entry.
     * DR Stock In Hand | CR Accounts Payable
     */
    public function sync_goods_receipt_to_journal($receipt_id)
    {
        if (!$this->db->table_exists('tblgoods_receipt')) return false;

        $this->load->model('xetuu_books/Xb_config_model', 'xb_config_eng');
        $settings = $this->xb_config_eng->get_all_settings();

        $receipt = $this->db->where('id', $receipt_id)->get('tblgoods_receipt')->row();
        if (!$receipt || (int)$receipt->approval !== 1) return false;

        $this->db->where('move_type', 'entry')->where('ref', 'GRN-' . $receipt_id);
        $existing = $this->db->get('acc_moves')->row();

        $amount_total = (float)($receipt->total_money ?: $receipt->value_of_inventory ?: 0);
        if ($amount_total <= 0) return false;

        // DR Stock In Hand (inventory asset), CR Accounts Payable
        $inv_id = $this->_get_account_id_by_code('11810'); // Stock In Hand leaf
        $ap_id  = $this->_get_setting_account_id('purchase_payable_account', $settings)
               ?: $this->_get_setting_account_id('default_payable_account', $settings);

        $lines = [];
        if ($inv_id) {
            $lines[] = ['account_id' => $inv_id, 'name' => 'Stock In Hand', 'debit' => $amount_total, 'credit' => 0];
        }
        if ($ap_id) {
            $lines[] = ['account_id' => $ap_id, 'name' => 'Accounts Payable', 'debit' => 0, 'credit' => $amount_total];
        }
        if (empty($lines)) return false;

        $journal_id = (int)($settings['default_purchase_journal_id'] ?? 2);
        $move_data = [
            'journal_id'   => $journal_id,
            'date'         => $receipt->date_add ? date('Y-m-d', strtotime($receipt->date_add)) : date('Y-m-d'),
            'ref'          => 'GRN-' . $receipt_id,
            'narration'    => 'Goods Receipt Note #' . $receipt_id,
            'move_type'    => 'entry',
            'amount_total' => $amount_total,
            'lines'        => $lines,
        ];

        if ($existing && $existing->state === 'posted') { $this->reset_move_to_draft($existing->id); }

        $res = $this->save_entry($move_data, $existing ? $existing->id : null);
        if (!is_array($res)) return false;
        try {
            $this->_post_move_direct($res['id']);
        } catch (Exception $e) {
            log_message('error', '[xetuu_books] sync_goods_receipt failed for GRN-' . $receipt_id . ': ' . $e->getMessage());
        }
        return $res;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post a move whose lines are already final accounting entries (no product lines).
     * Validates balance, gets sequence, stamps state='posted'.
     * Does NOT call _generate_accounting_lines — used by Perfex sync hooks.
     */
    private function _post_move_direct($move_id)
    {
        $move = $this->get_move($move_id);
        if (!$move || $move->state !== 'draft') {
            throw new \Exception('Move is not in draft state.');
        }

        $lines = $this->get_move_lines($move_id);
        if (empty($lines)) {
            throw new \Exception('No accounting lines found on this document.');
        }

        $dr = 0.0;
        $cr = 0.0;
        foreach ($lines as $l) {
            $dr += (float)$l->debit;
            $cr += (float)$l->credit;
        }
        if (abs($dr - $cr) > 0.01) {
            throw new \Exception(sprintf(
                'Entry not balanced. DR: %s  CR: %s',
                number_format($dr, 2),
                number_format($cr, 2)
            ));
        }

        $seq = $this->get_next_sequence($move->journal_id, $move->date);
        $this->db->where('id', $move_id)->update('acc_moves', ['state' => 'posted', 'name' => $seq]);
        return true;
    }

    /**
     * Accumulate tax amounts by account from a product line.
     * $side = 'output' (invoices) or 'input' (bills).
     */
    private function _accumulate_tax($product_line, $move, array $settings, $side, array &$pool)
    {
        $this->db->where('id', $product_line->tax_line_id);
        $tax = $this->db->get('acc_taxes')->row();
        if (!$tax || (float)$tax->amount == 0) return;

        $subtotal   = (float)$product_line->price_subtotal;
        $rate       = abs((float)$tax->amount);
        $tax_amount = $subtotal * $rate / 100;

        $setting_key = ($side === 'output') ? 'vat_output_account' : 'vat_input_account';
        $acc_id      = ($tax->account_id > 0) ? $tax->account_id
                     : $this->_get_setting_account_id($setting_key, $settings);

        if (!$acc_id) return;

        $key = $acc_id . '_' . $product_line->tax_line_id;
        if (!isset($pool[$key])) {
            $pool[$key] = ['account_id' => $acc_id, 'amount' => 0, 'tax_id' => $product_line->tax_line_id];
        }
        $pool[$key]['amount'] += $tax_amount;
    }

    private function _get_setting_account_id($key, array $settings)
    {
        $code_or_id = $settings[$key] ?? null;
        if (!$code_or_id) return null;

        if (is_numeric($code_or_id) && (int)$code_or_id > 0) {
            // Could be a direct account ID stored in settings
            $this->db->where('id', (int)$code_or_id);
            $acc = $this->db->get('acc_accounts')->row();
            if ($acc) return (int)$acc->id;
        }

        // Otherwise treat as account code
        $this->db->where('code', $code_or_id);
        $acc = $this->db->get('acc_accounts')->row();
        return $acc ? (int)$acc->id : null;
    }

    private function _get_account_id_by_code($code)
    {
        if (!$code) return null;
        $this->db->where('code', $code);
        $acc = $this->db->get('acc_accounts')->row();
        return $acc ? (int)$acc->id : null;
    }

    private function _build_perfex_invoice_lines($invoice, array $settings)
    {
        $ar_id  = $this->_get_account_id_by_code($settings['sales_receivable_account'] ?? $settings['default_receivable_account'] ?? '1100');
        $rev_id = $this->_get_account_id_by_code($settings['sales_revenue_account']    ?? $settings['default_income_account']    ?? '4000');
        $vat_id = $this->_get_account_id_by_code($settings['vat_output_account'] ?? '2100');

        $lines = [];
        if ($ar_id) {
            $lines[] = ['account_id' => $ar_id, 'partner_id' => $invoice->clientid,
                'name' => 'Accounts Receivable', 'debit' => $invoice->total, 'credit' => 0];
        }
        if ($rev_id) {
            $lines[] = ['account_id' => $rev_id, 'partner_id' => $invoice->clientid,
                'name' => 'Sales Revenue', 'debit' => 0, 'credit' => $invoice->subtotal];
        }
        if ($vat_id && $invoice->total_tax > 0) {
            $lines[] = ['account_id' => $vat_id, 'partner_id' => $invoice->clientid,
                'name' => 'VAT Output', 'debit' => 0, 'credit' => $invoice->total_tax];
        }
        return $lines;
    }

    private function _auto_reconcile_invoice_payment($invoice_id, $payment_move_id, $rec_account_id)
    {
        $this->db->where('ref', 'INV-' . $invoice_id)->where('move_type', 'out_invoice');
        $inv_move = $this->db->get('acc_moves')->row();
        if (!$inv_move) return;

        $this->db->where('move_id', $inv_move->id)->where('account_id', $rec_account_id)
                 ->where('debit >', 0)->where('reconciled', 0);
        $inv_line = $this->db->get('acc_move_lines')->row();

        $this->db->where('move_id', $payment_move_id)->where('account_id', $rec_account_id)
                 ->where('credit >', 0)->where('reconciled', 0);
        $pay_line = $this->db->get('acc_move_lines')->row();

        if ($inv_line && $pay_line) {
            $this->reconcile_lines([$inv_line->id], [$pay_line->id]);
        }
    }

    private function _next_recon_seq()
    {
        $row = $this->db->select('COUNT(*) as cnt')->get('acc_full_reconcile')->row();
        return ($row ? (int)$row->cnt : 0) + 1;
    }
}
