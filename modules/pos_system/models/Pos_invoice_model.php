<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_invoice_model — Invoice lifecycle for POS sales.
 *
 * Manages the pos_invoices table and optionally creates/links
 * Perfex CRM invoices (tblinvoices) when auto_create_invoice is on.
 *
 * States: draft → submitted → paid | partial | cancelled | credit_note
 */
class Pos_invoice_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_invoices';
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    /**
     * Create a POS invoice from a completed sale.
     * Optionally pushes it to Perfex CRM's invoice system.
     */
    public function create_from_sale(array $sale, bool $push_to_perfex = false): array
    {
        if (!$this->db->table_exists($this->table)) {
            return ['success' => false, 'message' => 'Invoice tables not yet installed'];
        }

        // Prevent duplicate
        $existing = $this->db->where('sale_id', $sale['id'])->get($this->table)->row_array();
        if ($existing) {
            return ['success' => true, 'invoice' => $existing, 'created' => false];
        }

        $prefix  = pos_get_setting('invoice_prefix') ?? 'POS-';
        $inv_num = $this->_next_invoice_number($prefix, (int) $sale['branch_id']);

        $data = [
            'sale_id'         => $sale['id'],
            'branch_id'       => $sale['branch_id'],
            'invoice_number'  => $inv_num,
            'status'          => 'submitted',
            'subtotal'        => $sale['subtotal']       ?? 0,
            'tax_amount'      => $sale['tax_amount']     ?? 0,
            'discount_amount' => $sale['discount_amount']?? 0,
            'total'           => $sale['total']          ?? 0,
            'amount_paid'     => $sale['amount_paid']    ?? 0,
            'currency'        => $sale['currency']       ?? 'KES',
            'customer_id'     => $sale['customer_id']    ?? null,
            'customer_name'   => $sale['customer_name']  ?? 'Walk-In',
        ];

        // Determine status from payment
        if ((float)$data['amount_paid'] >= (float)$data['total']) {
            $data['status'] = 'paid';
        } elseif ((float)$data['amount_paid'] > 0) {
            $data['status'] = 'partial';
        }

        $this->db->insert($this->table, $data);
        $invoice_id = $this->db->insert_id();

        $invoice = $this->get($invoice_id);

        // Optionally push to Perfex CRM
        if ($push_to_perfex && $invoice) {
            $perfex_id = $this->_push_to_perfex($sale, $invoice);
            if ($perfex_id) {
                $this->db->where('id', $invoice_id)->update($this->table, ['perfex_invoice_id' => $perfex_id]);
                $invoice['perfex_invoice_id'] = $perfex_id;
            }
        }

        return ['success' => true, 'invoice' => $invoice, 'created' => true];
    }

    /**
     * Generate a credit note for a refunded sale.
     */
    public function create_credit_note(int $original_invoice_id, array $refund_data): array
    {
        $original = $this->get($original_invoice_id);
        if (!$original) {
            return ['success' => false, 'message' => 'Original invoice not found'];
        }

        $cn_num = 'CN-' . $original['invoice_number'];

        // Check for duplicate CN
        $exists = $this->db->where('invoice_number', $cn_num)->get($this->table)->row();
        if ($exists) {
            return ['success' => false, 'message' => 'Credit note already exists for this invoice'];
        }

        $data = [
            'sale_id'         => $refund_data['sale_id']    ?? $original['sale_id'],
            'branch_id'       => $original['branch_id'],
            'invoice_number'  => $cn_num,
            'status'          => 'credit_note',
            'subtotal'        => -abs($refund_data['subtotal']       ?? $original['subtotal']),
            'tax_amount'      => -abs($refund_data['tax_amount']     ?? $original['tax_amount']),
            'discount_amount' => 0,
            'total'           => -abs($refund_data['total']          ?? $original['total']),
            'amount_paid'     => -abs($refund_data['amount_paid']    ?? $original['total']),
            'currency'        => $original['currency'],
            'customer_id'     => $original['customer_id'],
            'customer_name'   => $original['customer_name'],
            'credit_note_for' => $original_invoice_id,
        ];

        $this->db->insert($this->table, $data);
        $cn_id = $this->db->insert_id();

        // Mark original as refunded
        $this->db->where('id', $original_invoice_id)
                 ->update($this->table, ['status' => 'credit_note']);

        return ['success' => true, 'invoice' => $this->get($cn_id)];
    }

    public function cancel(int $id, string $reason = ''): bool
    {
        return (bool) $this->db->where('id', $id)->update($this->table, [
            'status' => 'cancelled',
            'notes'  => $reason,
        ]);
    }

    // ─── Queries ─────────────────────────────────────────────────────────────

    public function get(int $id): ?array
    {
        return $this->db->where('id', $id)->get($this->table)->row_array() ?: null;
    }

    public function get_by_sale(int $sale_id): ?array
    {
        return $this->db->where('sale_id', $sale_id)->get($this->table)->row_array() ?: null;
    }

    public function get_by_branch(int $branch_id, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }
        $this->db->where('branch_id', $branch_id);

        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(date_created) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(date_created) <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('invoice_number', $term)
                     ->or_like('customer_name', $term)
                     ->group_end();
        }

        return $this->db->order_by('date_created', 'DESC')
                        ->limit($limit, $offset)
                        ->get($this->table)
                        ->result_array();
    }

    public function count_by_branch(int $branch_id, array $filters = []): int
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }
        $this->db->where('branch_id', $branch_id);
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        return (int) $this->db->count_all_results($this->table);
    }

    // ─── Perfex CRM integration ────────────────────────────────────────────────

    /**
     * Push a POS invoice to Perfex CRM's tblinvoices + tblitemable.
     * Returns the Perfex invoice ID or null on failure/no client.
     *
     * Perfex invoice structure:
     *   tblinvoices — header row (clientid, number, currency INT, status, totals, hash)
     *   tblitemable — line items (rel_type='invoice', rel_id=invoice_id)
     */
    private function _push_to_perfex(array $sale, array $pos_invoice): ?int
    {
        try {
            // Resolve Perfex client ID from POS customer, fall back to walk-in client
            $client_id = null;
            if (!empty($sale['customer_id'])) {
                $cust = $this->db
                    ->where('id', $sale['customer_id'])
                    ->get(db_prefix() . 'pos_customers')
                    ->row();
                $client_id = ($cust && !empty($cust->perfex_client_id))
                    ? (int)$cust->perfex_client_id
                    : null;
            }

            if (!$client_id) {
                $client_id = $this->_get_or_create_walkin_client();
            }

            if (!$client_id) {
                return null;
            }

            // Resolve currency ID (tblcurrencies.name = 'KES')
            $currency_code = $sale['currency'] ?? 'KES';
            $currency_row  = $this->db->where('name', $currency_code)
                                       ->get(db_prefix() . 'currencies')->row();
            $currency_id   = $currency_row ? (int)$currency_row->id : 1;

            // Next invoice number
            $next_num      = $this->_next_perfex_invoice_number();
            $prefix        = 'POS-';
            $formatted_num = $prefix . str_pad($next_num, 5, '0', STR_PAD_LEFT);

            $now  = date('Y-m-d H:i:s');
            $date = date('Y-m-d');

            // Derive amount_paid from payments array if the sale column is still 0
            $total      = (float)($sale['total'] ?? 0);
            $paid       = (float)($sale['amount_paid'] ?? 0);
            if ($paid == 0 && !empty($sale['payments'])) {
                foreach ($sale['payments'] as $p) {
                    $paid += (float)($p['amount'] ?? 0);
                }
            }

            // Perfex status: 1=Unpaid, 2=Paid, 3=Partial
            $inv_status = 1;
            if ($paid >= $total && $total > 0) {
                $inv_status = 2;
            } elseif ($paid > 0) {
                $inv_status = 3;
            }

            $invoice_row = [
                'clientid'         => $client_id,
                'number'           => $next_num,
                'prefix'           => $prefix,
                'number_format'    => 1,
                'formatted_number' => $formatted_num,
                'datecreated'      => $now,
                'date'             => $date,
                'duedate'          => $date,
                'currency'         => $currency_id,
                'subtotal'         => $sale['subtotal']        ?? 0,
                'total_tax'        => $sale['tax_amount']      ?? 0,
                'total'            => $total,
                'adjustment'       => 0,
                'discount_percent' => 0,
                'discount_total'   => $sale['discount_amount'] ?? 0,
                'discount_type'    => 'before_tax',
                'status'           => $inv_status,
                'addedfrom'        => $sale['staff_id']        ?? 0,
                'hash'             => md5(uniqid((string)$client_id, true)),
                'adminnote'        => 'POS Receipt: ' . ($sale['receipt_number'] ?? $pos_invoice['invoice_number'] ?? ''),
            ];

            $this->db->insert(db_prefix() . 'invoices', $invoice_row);
            $invoice_id = $this->db->insert_id();

            if (!$invoice_id) {
                return null;
            }

            // Insert line items into tblitemable
            $sale_items = $this->db
                ->select('si.product_name, si.quantity, si.unit_price, si.sku')
                ->from(db_prefix() . 'pos_sale_items si')
                ->where('si.sale_id', $sale['id'])
                ->get()->result_array();

            foreach ($sale_items as $order => $item) {
                $this->db->insert(db_prefix() . 'itemable', [
                    'rel_id'          => $invoice_id,
                    'rel_type'        => 'invoice',
                    'description'     => $item['product_name'],
                    'long_description'=> $item['sku'] ?? '',
                    'qty'             => $item['quantity'],
                    'rate'            => $item['unit_price'],
                    'unit'            => '',
                    'item_order'      => $order,
                ]);
            }

            return $invoice_id;

        } catch (\Throwable $e) {
            log_message('error', 'POS _push_to_perfex: ' . $e->getMessage());
            return null;
        }
    }

    private function _next_invoice_number(string $prefix, int $branch_id): string
    {
        $date = date('Ym');
        $like_prefix = $prefix . $date . '-';

        $last = $this->db
            ->like('invoice_number', $like_prefix, 'after')
            ->where('branch_id', $branch_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->invoice_number);
            $seq   = (int) end($parts) + 1;
        }

        return $like_prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    private function _next_perfex_invoice_number(): int
    {
        $row = $this->db->select('MAX(number) AS max_num')->get(db_prefix() . 'invoices')->row();
        return (int)($row->max_num ?? 0) + 1;
    }

    /**
     * Find or create the generic "POS Walk-In Customer" Perfex client used for
     * all POS sales that have no mapped Perfex client.
     */
    private function _get_or_create_walkin_client(): ?int
    {
        $existing = $this->db
            ->where('company', 'POS Walk-In Customer')
            ->get(db_prefix() . 'clients')
            ->row();

        if ($existing) {
            return (int) $existing->userid;
        }

        $this->db->insert(db_prefix() . 'clients', [
            'company'                => 'POS Walk-In Customer',
            'active'                 => 1,
            'country'                => 0,
            'billing_country'        => 0,
            'datecreated'            => date('Y-m-d H:i:s'),
            'addedfrom'              => 0,
            'registration_confirmed' => 1,
        ]);

        $id = (int) $this->db->insert_id();
        return $id ?: null;
    }
}
