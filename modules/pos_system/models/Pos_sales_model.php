<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_sales_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_sales';
    }

    /**
     * Create a complete sale with items and payments in a transaction.
     *
     * @param array $sale     Sale header fields
     * @param array $items    Array of line items
     * @param array $payments Array of payment records
     */
    public function create_sale(array $sale, array $items, array $payments): array
    {
        $this->db->trans_begin();

        try {
            // Generate unique receipt number
            $sale['receipt_number'] = $sale['receipt_number']
                ?? pos_generate_receipt_number((int) $sale['branch_id']);
            $sale['sale_uid'] = $sale['sale_uid'] ?? pos_uuid();

            // Compute totals server-side to prevent client tampering
            $totals = $this->compute_totals($items);
            $sale   = array_merge($sale, $totals);

            $this->db->insert($this->table, $sale);
            $sale_id = $this->db->insert_id();

            // Insert line items and update inventory
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert(db_prefix() . 'pos_sale_items', $item);
                $this->deduct_inventory($item, (int) $sale['branch_id']);
            }

            // Insert payment records
            $total_paid = 0;
            foreach ($payments as $payment) {
                $payment['sale_id']    = $sale_id;
                $payment['branch_id']  = $sale['branch_id'];
                $payment['session_id'] = $sale['session_id'] ?? null;
                $payment['payment_uid'] = pos_uuid();
                $this->db->insert(db_prefix() . 'pos_payments', $payment);
                $total_paid += (float) $payment['amount'];
            }

            // Stamp amount_paid on the sale row
            $this->db->where('id', $sale_id)->update($this->table, ['amount_paid' => $total_paid]);

            // Update session totals
            if (!empty($sale['session_id'])) {
                $this->db->set('total_sales_amount', 'total_sales_amount + ' . $totals['total'], false)
                         ->set('total_sales_count',  'total_sales_count + 1', false)
                         ->where('id', $sale['session_id'])
                         ->update(db_prefix() . 'pos_sessions');
            }

            // Update customer lifetime value
            if (!empty($sale['customer_id'])) {
                $this->db->set('total_spent', 'total_spent + ' . $totals['total'], false)
                         ->where('id', $sale['customer_id'])
                         ->update(db_prefix() . 'pos_customers');
            }

            $this->db->trans_commit();

            $full_sale = $this->get($sale_id);

            // Fire integration hook — non-blocking; sale is already committed
            try {
                hooks()->do_action('pos_sale_completed', [
                    'sale'     => $full_sale,
                    'items'    => $full_sale['items']    ?? [],
                    'payments' => $full_sale['payments'] ?? [],
                    'staff_id' => (int)($sale['staff_id'] ?? 0),
                ]);
            } catch (Exception $hookEx) {
                log_message('error', '[POS] pos_sale_completed hook failed for sale #'
                    . ($full_sale['receipt_number'] ?? $sale_id) . ': ' . $hookEx->getMessage());
            }

            return ['success' => true, 'sale_id' => $sale_id, 'sale' => $full_sale];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', '[POS] create_sale failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Sale creation failed: ' . $e->getMessage()];
        }
    }

    public function get(int $id): ?array
    {
        $sale = $this->db
            ->select('s.*, CONCAT(st.firstname,\' \',st.lastname) AS cashier_name, c.name AS customer_name, b.name AS branch_name')
            ->from($this->table . ' s')
            ->join(db_prefix() . 'staff st',           'st.staffid = s.staff_id',      'left')
            ->join(db_prefix() . 'pos_customers c',    'c.id  = s.customer_id',   'left')
            ->join(db_prefix() . 'pos_branches b',     'b.id  = s.branch_id',     'left')
            ->where('s.id', $id)
            ->get()
            ->row_array();

        if (!$sale) {
            return null;
        }

        $sale['items']    = $this->get_sale_items($id);
        $sale['payments'] = $this->get_sale_payments($id);

        return $sale;
    }

    public function get_by_branch(int $branch_id, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $this->db->select('s.*, CONCAT(st.firstname,\' \',st.lastname) AS cashier_name, c.name AS customer_name')
                 ->from($this->table . ' s')
                 ->join(db_prefix() . 'staff st',        'st.staffid = s.staff_id',    'left')
                 ->join(db_prefix() . 'pos_customers c', 'c.id  = s.customer_id', 'left')
                 ->where('s.branch_id', $branch_id);

        if (!empty($filters['status'])) {
            $this->db->where('s.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(s.date_created) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(s.date_created) <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('s.receipt_number', $term)
                     ->or_like('c.name', $term)
                     ->group_end();
        }

        return $this->db->order_by('s.date_created', 'DESC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    /**
     * Process a full or partial refund.
     */
    public function refund(int $sale_id, array $items_to_refund, string $reason = ''): array
    {
        $sale = $this->get($sale_id);
        if (!$sale) {
            return ['success' => false, 'message' => 'Sale not found'];
        }

        $this->db->trans_begin();

        try {
            foreach ($items_to_refund as $refund_item) {
                $original = $this->db
                    ->where('id', $refund_item['item_id'])
                    ->where('sale_id', $sale_id)
                    ->get(db_prefix() . 'pos_sale_items')
                    ->row_array();

                if (!$original) {
                    continue;
                }

                $qty = min((float) $refund_item['quantity'], (float) $original['quantity'] - (float) $original['refunded_qty']);
                if ($qty <= 0) {
                    continue;
                }

                $this->db->set('refunded_qty', 'refunded_qty + ' . $qty, false)
                         ->where('id', $refund_item['item_id'])
                         ->update(db_prefix() . 'pos_sale_items');

                // Return inventory
                $this->restore_inventory($original, $qty, (int) $sale['branch_id']);
            }

            // Determine new status
            $remaining = $this->db
                ->select('SUM(quantity - refunded_qty) AS remaining')
                ->where('sale_id', $sale_id)
                ->get(db_prefix() . 'pos_sale_items')
                ->row()->remaining ?? 0;

            $new_status = (float) $remaining <= 0 ? 'refunded' : 'partial_refund';

            $this->db->where('id', $sale_id)
                     ->update($this->table, ['status' => $new_status, 'notes' => $reason]);

            $this->db->trans_commit();
            return ['success' => true, 'sale' => $this->get($sale_id)];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Refund failed'];
        }
    }

    /**
     * Process pending offline sales from SPA queue.
     */
    public function sync_offline_sales(array $sales, int $branch_id, int $staff_id): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($sales as $payload) {
            $payload['sale']['branch_id'] = $branch_id;
            $payload['sale']['staff_id']  = $staff_id;
            $payload['sale']['sync_status'] = 'synced';
            $payload['sale']['synced_at']   = date('Y-m-d H:i:s');

            // Skip if already synced (idempotency via sale_uid)
            $exists = $this->db->where('sale_uid', $payload['sale']['sale_uid'])
                               ->get($this->table)->row();
            if ($exists) {
                $results['success'][] = $payload['sale']['sale_uid'];
                continue;
            }

            $result = $this->create_sale($payload['sale'], $payload['items'], $payload['payments']);
            if ($result['success']) {
                $results['success'][] = $payload['sale']['sale_uid'];
            } else {
                $results['failed'][] = $payload['sale']['sale_uid'];
            }
        }

        return $results;
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function compute_totals(array $items): array
    {
        $subtotal = 0;
        $tax      = 0;

        foreach ($items as $item) {
            $qty        = (float) $item['quantity'];
            $price      = (float) $item['unit_price'];
            $disc_amt   = (float) ($item['discount_amt'] ?? 0);
            $tax_rate   = (float) ($item['tax_rate'] ?? 0);
            $line_pre   = ($qty * $price) - $disc_amt;
            $line_tax   = $line_pre * $tax_rate;
            $subtotal  += $line_pre;
            $tax       += $line_tax;
        }

        return [
            'subtotal'   => round($subtotal, 4),
            'tax_amount' => round($tax, 4),
            'total'      => round($subtotal + $tax, 4),
        ];
    }

    private function deduct_inventory(array $item, int $branch_id): void
    {
        $where = ['branch_id' => $branch_id, 'product_id' => $item['product_id']];
        if (!empty($item['variation_id'])) {
            $where['variation_id'] = $item['variation_id'];
        } else {
            $this->db->where('variation_id IS NULL');
        }

        $inv = $this->db->where($where)->get(db_prefix() . 'pos_inventory')->row_array();
        if (!$inv) {
            return;
        }

        $qty_before = (float) $inv['quantity'];
        $qty_after  = $qty_before - (float) $item['quantity'];

        $this->db->where($where)->set('quantity', $qty_after)->update(db_prefix() . 'pos_inventory');

        $this->db->insert(db_prefix() . 'pos_inventory_movements', [
            'branch_id'     => $branch_id,
            'product_id'    => $item['product_id'],
            'variation_id'  => $item['variation_id'] ?? null,
            'type'          => 'sale',
            'reference_id'  => $item['sale_id'],
            'reference_type' => 'pos_sale',
            'qty_before'    => $qty_before,
            'qty_change'    => -((float) $item['quantity']),
            'qty_after'     => $qty_after,
            'unit_cost'     => $item['cost_price'] ?? 0,
            'date_created'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function restore_inventory(array $item, float $qty, int $branch_id): void
    {
        $where = ['branch_id' => $branch_id, 'product_id' => $item['product_id']];
        if (!empty($item['variation_id'])) {
            $where['variation_id'] = $item['variation_id'];
        } else {
            $this->db->where('variation_id IS NULL');
        }

        $inv = $this->db->where($where)->get(db_prefix() . 'pos_inventory')->row_array();
        if (!$inv) {
            return;
        }

        $qty_before = (float) $inv['quantity'];
        $qty_after  = $qty_before + $qty;

        $this->db->where($where)->set('quantity', $qty_after)->update(db_prefix() . 'pos_inventory');

        $this->db->insert(db_prefix() . 'pos_inventory_movements', [
            'branch_id'    => $branch_id,
            'product_id'   => $item['product_id'],
            'variation_id' => $item['variation_id'] ?? null,
            'type'         => 'refund',
            'qty_before'   => $qty_before,
            'qty_change'   => $qty,
            'qty_after'    => $qty_after,
            'date_created' => date('Y-m-d H:i:s'),
        ]);
    }

    private function get_sale_items(int $sale_id): array
    {
        return $this->db
            ->select('si.*, po.perfex_item_id, po.name AS product_name')
            ->from(db_prefix() . 'pos_sale_items si')
            ->join(db_prefix() . 'pos_products po', 'po.id = si.product_id', 'left')
            ->where('si.sale_id', $sale_id)
            ->get()
            ->result_array();
    }

    private function get_sale_payments(int $sale_id): array
    {
        return $this->db
            ->select('p.*, pm.name AS method_name, pm.type AS payment_type, pm.icon AS method_icon')
            ->from(db_prefix() . 'pos_payments p')
            ->join(db_prefix() . 'pos_payment_methods pm', 'pm.id = p.payment_method_id', 'left')
            ->where('p.sale_id', $sale_id)
            ->get()
            ->result_array();
    }
}
