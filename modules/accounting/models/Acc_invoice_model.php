<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_invoice_model extends App_Model
{
    private $moves_table   = 'acc_moves';
    private $lines_table   = 'acc_move_lines';
    private $accounts_table= 'acc_gl_accounts';

    // ─────────────────────────────────────────────────────────────────────────
    // GET
    // ─────────────────────────────────────────────────────────────────────────
    public function get($id)
    {
        $row = $this->db
            ->select('m.*, j.name as journal_name, j.code as journal_code, j.type as journal_type')
            ->from(db_prefix() . $this->moves_table . ' m')
            ->join(db_prefix() . 'acc_journals j', 'j.id = m.journal_id', 'left')
            ->where('m.id', $id)
            ->get()->row();

        if ($row) {
            $row->partner_name = $this->_get_partner_name($row->partner_id, $row->partner_type ?? 'customer');
        }
        return $row;
    }

    public function get_lines($move_id)
    {
        return $this->db
            ->select('ml.*, ga.code as account_code, ga.name as account_name')
            ->from(db_prefix() . $this->lines_table . ' ml')
            ->join(db_prefix() . $this->accounts_table . ' ga', 'ga.id = ml.account_id', 'left')
            ->where('ml.move_id', $move_id)
            ->order_by('ml.sequence')
            ->get()->result();
    }

    public function get_list($move_type, $filters = [])
    {
        $this->db->select('m.*, j.name as journal_name');
        $this->db->from(db_prefix() . $this->moves_table . ' m');
        $this->db->join(db_prefix() . 'acc_journals j', 'j.id = m.journal_id', 'left');
        $this->db->where('m.move_type', $move_type);

        if (!empty($filters['state']))      { $this->db->where('m.state', $filters['state']); }
        if (!empty($filters['partner_id'])) { $this->db->where('m.partner_id', $filters['partner_id']); }
        if (!empty($filters['journal_id'])) { $this->db->where('m.journal_id', $filters['journal_id']); }
        if (!empty($filters['date_from']))  { $this->db->where('m.date >=', $filters['date_from']); }
        if (!empty($filters['date_to']))    { $this->db->where('m.date <=', $filters['date_to']); }
        if (!empty($filters['search'])) {
            $s = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()->like('m.name', $s)->or_like('m.ref', $s)->group_end();
        }

        $this->db->order_by('m.date', 'DESC');
        if (!empty($filters['limit'])) { $this->db->limit((int)$filters['limit']); }
        $moves = $this->db->get()->result();

        foreach ($moves as &$m) {
            $m->partner_name = $this->_get_partner_name($m->partner_id, $m->partner_type ?? 'customer');
        }
        return $moves;
    }

    public function get_list_totals($move_type, $filters = [])
    {
        $this->db->select('SUM(amount_total) as total_amount, SUM(amount_residual) as total_residual, COUNT(*) as total_count');
        $this->db->from(db_prefix() . $this->moves_table);
        $this->db->where('move_type', $move_type);
        if (!empty($filters['state']))     { $this->db->where('state', $filters['state']); }
        if (!empty($filters['date_from'])) { $this->db->where('date >=', $filters['date_from']); }
        if (!empty($filters['date_to']))   { $this->db->where('date <=', $filters['date_to']); }
        return $this->db->get()->row();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE (create or update)
    // ─────────────────────────────────────────────────────────────────────────
    public function save_invoice($post, $id = null, $default_type = 'out_invoice')
    {
        $move_type = $post['move_type'] ?? $default_type;
        $partner_type = in_array($move_type, ['out_invoice', 'out_refund', 'out_receipt']) ? 'customer' : 'vendor';

        $move_data = [
            'move_type'              => $move_type,
            'journal_id'             => (int)($post['journal_id'] ?? 0),
            'date'                   => $post['date'] ?? date('Y-m-d'),
            'invoice_date'           => $post['invoice_date'] ?? $post['date'] ?? date('Y-m-d'),
            'invoice_date_due'       => $post['invoice_date_due'] ?? null,
            'partner_id'             => (int)($post['partner_id'] ?? 0) ?: null,
            'partner_type'           => $partner_type,
            'ref'                    => $post['ref'] ?? '',
            'narration'              => $post['narration'] ?? '',
            'invoice_origin'         => $post['invoice_origin'] ?? '',
            'invoice_payment_term_id'=> (int)($post['invoice_payment_term_id'] ?? 0) ?: null,
            'currency_id'            => (int)($post['currency_id'] ?? 1),
        ];

        if (!$move_data['journal_id']) { return 'Journal is required.'; }

        if ($id) {
            $existing = $this->get($id);
            if ($existing && $existing->state === 'posted') { return 'Cannot edit a posted document.'; }
            $move_data['updated_by'] = get_staff_user_id();
            $this->db->update(db_prefix() . $this->moves_table, $move_data, ['id' => $id]);
        } else {
            $move_data['state']      = 'draft';
            $move_data['created_by'] = get_staff_user_id();
            $this->db->insert(db_prefix() . $this->moves_table, $move_data);
            $id = $this->db->insert_id();
        }

        // Save invoice lines
        $this->load->model('accounting/Acc_engine_model', 'acc_engine');
        $lines = $this->_prepare_invoice_lines($post['lines'] ?? [], $move_type, $move_data['partner_id']);
        $this->acc_engine->_save_move_lines($id, $lines);
        $this->_recompute_totals($id);

        return ['id' => $id];
    }

    public function delete($id)
    {
        $move = $this->get($id);
        if (!$move || $move->state === 'posted') { return false; }
        $this->db->delete(db_prefix() . $this->moves_table, ['id' => $id]);
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOTALS COMPUTATION (used by AJAX live compute)
    // ─────────────────────────────────────────────────────────────────────────
    public function compute_totals($post)
    {
        $lines       = $post['lines'] ?? [];
        $currency_id = (int)($post['currency_id'] ?? 1);
        $untaxed     = 0;
        $tax_amounts = [];
        $line_results= [];

        foreach ($lines as $line) {
            if (!empty($line['display_type']) && in_array($line['display_type'], ['line_section', 'line_note'])) {
                $line_results[] = ['subtotal' => 0, 'total' => 0]; continue;
            }
            $qty      = (float)($line['quantity']   ?? 1);
            $price    = (float)($line['price_unit']  ?? 0);
            $disc     = (float)($line['discount']    ?? 0);
            $subtotal = round($qty * $price * (1 - $disc / 100), 4);
            $tax_ids  = isset($line['tax_ids']) ? (array)$line['tax_ids'] : [];
            $line_tax = 0;

            if (!empty($tax_ids)) {
                $taxes = $this->db->where_in('id', $tax_ids)->get(db_prefix() . 'acc_taxes')->result();
                foreach ($taxes as $t) {
                    if ($t->amount_type === 'percent') {
                        $ta = round($subtotal * $t->amount / 100, 4);
                    } elseif ($t->amount_type === 'fixed') {
                        $ta = round($qty * $t->amount, 4);
                    } else { $ta = 0; }
                    $line_tax += $ta;
                    $tax_amounts[$t->name] = ($tax_amounts[$t->name] ?? 0) + $ta;
                }
            }
            $untaxed += $subtotal;
            $line_results[] = ['subtotal' => $subtotal, 'total' => $subtotal + $line_tax];
        }

        $tax_total = array_sum($tax_amounts);
        return [
            'untaxed'     => round($untaxed, 2),
            'tax_total'   => round($tax_total, 2),
            'total'       => round($untaxed + $tax_total, 2),
            'tax_lines'   => $tax_amounts,
            'line_results'=> $line_results,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD STATS
    // ─────────────────────────────────────────────────────────────────────────
    public function get_dashboard_stats()
    {
        $stats = [];
        // Invoices to validate
        $stats['to_validate'] = $this->db->where('move_type', 'out_invoice')->where('state', 'draft')->count_all_results(db_prefix() . $this->moves_table);
        // Unpaid
        $r = $this->db->select('COUNT(*) as cnt, SUM(amount_residual) as total')
            ->where('move_type', 'out_invoice')->where('state', 'posted')
            ->where('payment_state !=', 'paid')
            ->get(db_prefix() . $this->moves_table)->row();
        $stats['unpaid_count']  = (int)($r->cnt ?? 0);
        $stats['unpaid_amount'] = (float)($r->total ?? 0);
        // Overdue
        $r2 = $this->db->select('COUNT(*) as cnt, SUM(amount_residual) as total')
            ->where('move_type', 'out_invoice')->where('state', 'posted')
            ->where('payment_state !=', 'paid')
            ->where('invoice_date_due <', date('Y-m-d'))
            ->get(db_prefix() . $this->moves_table)->row();
        $stats['overdue_count']  = (int)($r2->cnt ?? 0);
        $stats['overdue_amount'] = (float)($r2->total ?? 0);
        return $stats;
    }

    public function get_bill_dashboard_stats()
    {
        $stats = [];
        $stats['to_validate'] = $this->db->where('move_type', 'in_invoice')->where('state', 'draft')->count_all_results(db_prefix() . $this->moves_table);
        $r = $this->db->select('COUNT(*) as cnt, SUM(amount_residual) as total')
            ->where('move_type', 'in_invoice')->where('state', 'posted')
            ->where('payment_state !=', 'paid')
            ->get(db_prefix() . $this->moves_table)->row();
        $stats['unpaid_count']  = (int)($r->cnt ?? 0);
        $stats['unpaid_amount'] = (float)($r->total ?? 0);
        $r2 = $this->db->select('COUNT(*) as cnt, SUM(amount_residual) as total')
            ->where('move_type', 'in_invoice')->where('state', 'posted')
            ->where('payment_state !=', 'paid')
            ->where('invoice_date_due <', date('Y-m-d'))
            ->get(db_prefix() . $this->moves_table)->row();
        $stats['overdue_count']  = (int)($r2->cnt ?? 0);
        $stats['overdue_amount'] = (float)($r2->total ?? 0);
        return $stats;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function _prepare_invoice_lines($raw_lines, $move_type, $partner_id)
    {
        $lines = [];
        $is_out = in_array($move_type, ['out_invoice', 'out_refund']);

        foreach ($raw_lines as $rl) {
            $display_type = $rl['display_type'] ?? 'product';
            if (in_array($display_type, ['line_section', 'line_note'])) {
                $lines[] = ['name' => $rl['name'] ?? '', 'display_type' => $display_type,
                    'account_id' => 0, 'debit' => 0, 'credit' => 0];
                continue;
            }

            $qty      = (float)($rl['quantity']  ?? 1);
            $price    = (float)($rl['price_unit'] ?? 0);
            $disc     = (float)($rl['discount']   ?? 0);
            $subtotal = round($qty * $price * (1 - $disc / 100), 4);
            $tax_ids  = isset($rl['tax_ids']) ? (array)$rl['tax_ids'] : [];

            $line_tax = 0;
            $tax_lines = [];
            if (!empty($tax_ids)) {
                $taxes = $this->db->where_in('id', $tax_ids)->get(db_prefix() . 'acc_taxes')->result();
                foreach ($taxes as $t) {
                    $ta = $t->amount_type === 'percent' ? round($subtotal * $t->amount / 100, 4) : (float)$t->amount;
                    $line_tax += $ta;
                    if ($t->account_id) {
                        $tax_lines[] = [
                            'account_id'   => $t->account_id,
                            'name'         => $t->name,
                            'partner_id'   => $partner_id,
                            'debit'        => $is_out ? 0 : $ta,
                            'credit'       => $is_out ? $ta : 0,
                            'balance'      => $is_out ? -$ta : $ta,
                            'tax_line_id'  => $t->id,
                            'display_type' => 'tax',
                            'quantity'     => 1,
                            'price_unit'   => $ta,
                        ];
                    }
                }
            }

            $lines[] = [
                'account_id'   => (int)($rl['account_id'] ?? 0),
                'name'         => $rl['name'] ?? $rl['description'] ?? '',
                'partner_id'   => $partner_id,
                'quantity'     => $qty,
                'price_unit'   => $price,
                'discount'     => $disc,
                'price_subtotal'=> $subtotal,
                'price_total'  => $subtotal + $line_tax,
                'debit'        => $is_out ? 0 : $subtotal,
                'credit'       => $is_out ? $subtotal : 0,
                'balance'      => $is_out ? -$subtotal : $subtotal,
                'tax_ids'      => json_encode($tax_ids),
                'product_id'   => (int)($rl['product_id'] ?? 0) ?: null,
                'display_type' => 'product',
            ];
            $lines = array_merge($lines, $tax_lines);
        }
        return $lines;
    }

    private function _recompute_totals($move_id)
    {
        $lines = $this->db->select('price_subtotal, price_total, tax_line_id, display_type')
            ->get_where(db_prefix() . $this->lines_table, ['move_id' => $move_id])->result();

        $untaxed = 0; $tax = 0;
        foreach ($lines as $l) {
            if (in_array($l->display_type, ['line_section', 'line_note'])) { continue; }
            if ($l->tax_line_id) {
                $tax += (float)$l->price_total;
            } elseif ($l->display_type === 'product') {
                $untaxed += (float)$l->price_subtotal;
            }
        }
        $total = round($untaxed + $tax, 4);
        $this->db->update(db_prefix() . $this->moves_table, [
            'amount_untaxed'  => round($untaxed, 4),
            'amount_tax'      => round($tax, 4),
            'amount_total'    => $total,
            'amount_residual' => $total,
        ], ['id' => $move_id]);
    }

    private function _get_partner_name($partner_id, $type = 'customer')
    {
        if (!$partner_id) { return '—'; }
        if ($type === 'customer') {
            $row = $this->db->select('company')->get_where(db_prefix() . 'clients', ['userid' => $partner_id])->row();
            return $row ? $row->company : ('Client #' . $partner_id);
        }
        $row = $this->db->select('company')->get_where(db_prefix() . 'pur_vendor', ['userid' => $partner_id])->row();
        if (!$row) {
            $row = $this->db->select('company')->get_where(db_prefix() . 'clients', ['userid' => $partner_id])->row();
        }
        return $row ? $row->company : ('Vendor #' . $partner_id);
    }

    public function get_by_origin($origin)
    {
        return $this->db->get_where(db_prefix() . $this->moves_table, ['invoice_origin' => $origin])->row();
    }

    public function update_payment_state($move_id)
    {
        $move = $this->get($move_id);
        if (!$move) { return; }
        if ($move->amount_total <= 0) { $state = 'not_paid'; }
        elseif ($move->amount_residual <= 0) { $state = 'paid'; }
        elseif ($move->amount_residual < $move->amount_total) { $state = 'partial'; }
        else { $state = 'not_paid'; }
        $this->db->update(db_prefix() . $this->moves_table, ['payment_state' => $state], ['id' => $move_id]);
    }
}
