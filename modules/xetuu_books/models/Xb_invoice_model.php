<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_invoice_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // READ
    // ─────────────────────────────────────────────────────────────────────────

    public function get($id)
    {
        $this->db->select('am.*, IF(am.partner_type = "vendor", COALESCE(pv.company,""), COALESCE(c.company,"")) as partner_name, j.name as journal_name, j.code as journal_code');
        $this->db->from('acc_moves am');
        $this->db->join('tblclients c',   'c.userid  = am.partner_id', 'left');
        $this->db->join('tblpur_vendor pv', 'pv.userid = am.partner_id', 'left');
        $this->db->join('acc_journals j', 'j.id = am.journal_id', 'left');
        $this->db->where('am.id', $id);
        return $this->db->get()->row();
    }

    public function get_lines($move_id)
    {
        $this->db->select('aml.*, aa.name as account_name, aa.code as account_code, at.name as tax_name, at.amount as tax_rate, at.price_include');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->join('acc_taxes at', 'at.id = aml.tax_line_id', 'left');
        $this->db->where('aml.move_id', $move_id);
        $this->db->where('aml.display_type', 'product');
        $this->db->order_by('aml.sequence', 'ASC');
        $this->db->order_by('aml.id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_all_lines($move_id)
    {
        $this->db->select('aml.*, aa.name as account_name, aa.code as account_code');
        $this->db->from('acc_move_lines aml');
        $this->db->join('acc_accounts aa', 'aa.id = aml.account_id', 'left');
        $this->db->where('aml.move_id', $move_id);
        $this->db->order_by('aml.sequence', 'ASC');
        $this->db->order_by('aml.id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_list($move_type, $filters = [])
    {
        $this->db->select('am.*, IF(am.partner_type = "vendor", COALESCE(pv.company,""), COALESCE(c.company,"")) as partner_name, j.name as journal_name');
        $this->db->from('acc_moves am');
        $this->db->join('tblclients c',   'c.userid  = am.partner_id', 'left');
        $this->db->join('tblpur_vendor pv', 'pv.userid = am.partner_id', 'left');
        $this->db->join('acc_journals j', 'j.id = am.journal_id', 'left');

        if (is_array($move_type)) {
            $this->db->where_in('am.move_type', $move_type);
        } else {
            $this->db->where('am.move_type', $move_type);
        }

        if (!empty($filters['state'])) {
            $this->db->where('am.state', $filters['state']);
        }
        if (!empty($filters['partner_id'])) {
            $this->db->where('am.partner_id', $filters['partner_id']);
        }
        if (!empty($filters['journal_id'])) {
            $this->db->where('am.journal_id', $filters['journal_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('am.date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('am.date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('am.name', $filters['search']);
            $this->db->or_like('am.ref', $filters['search']);
            $this->db->or_like('c.company', $filters['search']);
            $this->db->group_end();
        }

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 500;
        $this->db->order_by('am.date', 'DESC');
        $this->db->order_by('am.id', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function get_list_totals($move_type, $filters = [])
    {
        $this->db->select('COALESCE(SUM(am.amount_untaxed),0) as amount_untaxed,
            COALESCE(SUM(am.amount_tax),0) as amount_tax,
            COALESCE(SUM(am.amount_total),0) as amount_total,
            COALESCE(SUM(am.amount_residual),0) as amount_residual');
        $this->db->from('acc_moves am');
        $this->db->where('am.move_type', $move_type);
        if (!empty($filters['state'])) {
            $this->db->where('am.state', $filters['state']);
        }
        return $this->db->get()->row();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WRITE
    // ─────────────────────────────────────────────────────────────────────────

    public function save_invoice($data, $id = null, $move_type = 'out_invoice')
    {
        $partner_type = in_array($move_type, ['out_invoice', 'out_refund', 'out_receipt']) ? 'customer' : 'vendor';
        $items        = isset($data['lines']) && is_array($data['lines']) ? $data['lines']
                      : (isset($data['items']) && is_array($data['items']) ? $data['items'] : []);

        // Compute totals from submitted line items
        list($amount_untaxed, $amount_tax) = $this->_compute_totals_from_items($items);
        $amount_total = $amount_untaxed + $amount_tax;

        $move_data = [
            'move_type'               => $move_type,
            'journal_id'              => !empty($data['journal_id']) ? (int)$data['journal_id'] : $this->_default_journal_id($move_type),
            'partner_id'              => !empty($data['partner_id']) ? (int)$data['partner_id'] : null,
            'partner_type'            => $partner_type,
            'date'                    => !empty($data['date']) ? $data['date'] : date('Y-m-d'),
            'invoice_date_due'        => !empty($data['invoice_date_due']) ? $data['invoice_date_due']
                                       : (!empty($data['duedate']) ? $data['duedate'] : date('Y-m-d')),
            'ref'                     => !empty($data['ref']) ? $data['ref'] : null,
            'narration'               => !empty($data['narration']) ? $data['narration'] : null,
            'invoice_payment_term_id' => !empty($data['invoice_payment_term_id']) ? (int)$data['invoice_payment_term_id'] : null,
            'currency_id'             => !empty($data['currency_id']) ? (int)$data['currency_id'] : 1,
            'exchange_rate'           => !empty($data['exchange_rate']) ? (float)$data['exchange_rate'] : 1.0,
            'amount_untaxed'          => $amount_untaxed,
            'amount_tax'              => $amount_tax,
            'amount_total'            => $amount_total,
            'amount_residual'         => $amount_total,
            'payment_state'           => 'not_paid',
            'source_move_id'          => !empty($data['source_move_id']) ? (int)$data['source_move_id'] : null,
        ];

        if (!$id) {
            $move_data['state']      = 'draft';
            $move_data['created_by'] = get_staff_user_id();
            $this->db->insert('acc_moves', $move_data);
            $id = $this->db->insert_id();
            if (!$id) return 'Failed to create accounting document.';
        } else {
            $existing = $this->get($id);
            if ($existing && $existing->state !== 'draft') {
                return 'Cannot edit a posted document. Reset to draft first.';
            }
            // Preserve source_move_id if not explicitly sent in the form
            if (!isset($data['source_move_id']) && $existing) {
                $move_data['source_move_id'] = $existing->source_move_id ?? null;
            }
            $move_data['updated_by'] = get_staff_user_id();
            $this->db->where('id', $id)->update('acc_moves', $move_data);
        }

        // Replace product lines (preserve non-product accounting lines if already posted)
        $this->db->where('move_id', $id)->where('display_type', 'product')->delete('acc_move_lines');

        $seq = 10;
        foreach ($items as $item) {
            $desc = trim($item['description'] ?? '');
            if ($desc === '') continue;

            $qty      = (float)($item['qty'] ?? 1);
            $price    = (float)($item['price_unit'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $sub      = $qty * $price * (1 - $discount / 100);
            $tax_id   = !empty($item['tax_id']) ? (int)$item['tax_id'] : null;

            // For price_subtotal, store the ex-tax base
            $price_subtotal = $sub;
            $price_total    = $sub;
            if ($tax_id) {
                $tax = $this->db->where('id', $tax_id)->get('acc_taxes')->row();
                if ($tax) {
                    if ($tax->price_include) {
                        $price_subtotal = $sub / (1 + abs((float)$tax->amount) / 100);
                    } else {
                        $price_total = $sub * (1 + abs((float)$tax->amount) / 100);
                    }
                }
            }

            $this->db->insert('acc_move_lines', [
                'move_id'        => $id,
                'display_type'   => 'product',
                'account_id'     => !empty($item['account_id']) ? (int)$item['account_id'] : 0,
                'partner_id'     => $move_data['partner_id'],
                'name'           => $desc,
                'quantity'       => $qty,
                'price_unit'     => $price,
                'discount'       => $discount,
                'price_subtotal' => round($price_subtotal, 4),
                'price_total'    => round($price_total, 4),
                'tax_line_id'    => $tax_id,
                'debit'          => 0,
                'credit'         => 0,
                'balance'        => 0,
                'date'           => $move_data['date'],
                'sequence'       => $seq,
                'product_id'     => !empty($item['item_id']) ? (int)$item['item_id'] : null,
            ]);
            $seq += 10;
        }

        return ['id' => $id];
    }

    public function save_receipt($data, $move_type = 'out_receipt')
    {
        // 1. Create the base document
        $res = $this->save_invoice($data, null, $move_type);
        if (isset($res['id'])) {
            $move_id = $res['id'];
            
            // 2. Post the move
            $this->load->model('xetuu_books/Xb_engine_model');
            try {
                $this->Xb_engine_model->post_move($move_id);
            } catch (Exception $e) {
                return $e->getMessage();
            }

            // 3. Register full payment instantly
            $move = $this->get($move_id);
            if ($move && !empty($data['payment_journal_id'])) {
                $this->load->model('xetuu_books/Xb_payment_model');
                $payment_data = [
                    'move_id'      => $move_id,
                    'partner_type' => in_array($move_type, ['out_receipt']) ? 'customer' : 'vendor',
                    'amount'       => $move->amount_total,
                    'journal_id'   => $data['payment_journal_id'],
                    'date'         => $data['payment_date'] ?? $move->date,
                    'partner_id'   => $move->partner_id,
                    'memo'         => 'Instant Receipt Payment',
                ];
                $this->Xb_payment_model->register_payment($payment_data);
            }
        }
        return $res;
    }

    public function delete($id)
    {
        $move = $this->get($id);
        if (!$move) return false;
        if ($move->state === 'posted') return false;
        $this->db->where('id', $id)->delete('acc_moves'); // CASCADE deletes move_lines
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD STATS — reads from acc_moves
    // ─────────────────────────────────────────────────────────────────────────

    public function get_dashboard_stats()
    {
        $today = date('Y-m-d');

        $this->db->where('move_type', 'out_invoice')->where('state', 'draft');
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_total),0) as amount')->get('acc_moves')->row();
        $to_validate = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        $this->db->where('move_type', 'out_invoice')->where('state', 'posted')->where('payment_state !=', 'paid');
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_residual),0) as amount')->get('acc_moves')->row();
        $unpaid = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        $this->db->where('move_type', 'out_invoice')->where('state', 'posted')
                 ->where('payment_state !=', 'paid')->where('invoice_date_due <', $today);
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_residual),0) as amount')->get('acc_moves')->row();
        $late = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        return compact('to_validate', 'unpaid', 'late');
    }

    public function get_bill_dashboard_stats()
    {
        $today = date('Y-m-d');

        $this->db->where('move_type', 'in_invoice')->where('state', 'draft');
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_total),0) as amount')->get('acc_moves')->row();
        $to_validate = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        $this->db->where('move_type', 'in_invoice')->where('state', 'posted')->where('payment_state !=', 'paid');
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_total),0) as amount')->get('acc_moves')->row();
        $to_pay = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        $this->db->where('move_type', 'in_invoice')->where('state', 'posted')
                 ->where('payment_state !=', 'paid')->where('invoice_date_due <', $today);
        $row = $this->db->select('COUNT(*) as cnt, COALESCE(SUM(amount_total),0) as amount')->get('acc_moves')->row();
        $late = ['count' => (int)$row->cnt, 'amount' => (float)$row->amount];

        return compact('to_validate', 'to_pay', 'late');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOTALS AJAX HELPER
    // ─────────────────────────────────────────────────────────────────────────

    public function compute_totals($data)
    {
        $items = $data['lines'] ?? $data['items'] ?? [];
        list($amount_untaxed, $amount_tax) = $this->_compute_totals_from_items($items);
        return [
            'amount_untaxed' => round($amount_untaxed, 2),
            'amount_tax'     => round($amount_tax, 2),
            'amount_total'   => round($amount_untaxed + $amount_tax, 2),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function _compute_totals_from_items(array $items)
    {
        $amount_untaxed = 0;
        $amount_tax     = 0;

        foreach ($items as $item) {
            $qty      = (float)($item['qty'] ?? $item['quantity'] ?? 1);
            $price    = (float)($item['price_unit'] ?? $item['rate'] ?? 0);
            $discount = (float)($item['discount'] ?? 0);
            $sub      = $qty * $price * (1 - $discount / 100);
            $tax_id   = !empty($item['tax_id']) ? (int)$item['tax_id'] : 0;

            if ($tax_id) {
                $tax = $this->db->where('id', $tax_id)->get('acc_taxes')->row();
                if ($tax && (float)$tax->amount != 0) {
                    $rate = abs((float)$tax->amount);
                    if ($tax->price_include) {
                        $sub_ex  = $sub / (1 + $rate / 100);
                        $tax_amt = $sub - $sub_ex;
                        $amount_untaxed += $sub_ex;
                        $amount_tax     += $tax_amt;
                    } else {
                        $amount_untaxed += $sub;
                        $amount_tax     += $sub * $rate / 100;
                    }
                } else {
                    $amount_untaxed += $sub;
                }
            } else {
                $amount_untaxed += $sub;
            }
        }

        return [$amount_untaxed, $amount_tax];
    }

    private function _default_journal_id($move_type)
    {
        $this->load->model('xetuu_books/Xb_config_model', 'xb_cfg_inv');
        switch ($move_type) {
            case 'out_invoice':
            case 'out_refund':
            case 'out_receipt':
                return (int)($this->xb_cfg_inv->get_setting('default_sale_journal_id') ?: 1);
            case 'in_invoice':
            case 'in_refund':
            case 'in_receipt':
                return (int)($this->xb_cfg_inv->get_setting('default_purchase_journal_id') ?: 2);
            default:
                return 6;
        }
    }
}
