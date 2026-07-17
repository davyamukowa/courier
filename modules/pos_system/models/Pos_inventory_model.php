<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_inventory_model
 *
 * Manages real-time stock with FIFO batch deduction, transfers, and adjustments.
 * All stock mutations are wrapped in transactions and logged to pos_inventory_movements.
 */
class Pos_inventory_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─── Stock queries ────────────────────────────────────────────────────────

    /**
     * Get current stock for a product at a branch.
     * If $variation_id is null, returns base product stock.
     */
    public function get_stock(int $branch_id, int $product_id, ?int $variation_id = null): float
    {
        $this->db->where('branch_id', $branch_id)
                 ->where('product_id', $product_id);

        if ($variation_id !== null) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL');
        }

        $row = $this->db->get(db_prefix() . 'pos_inventory')->row();
        return $row ? (float) $row->quantity : 0.0;
    }

    /**
     * Get stock for all products at a branch (for POS catalog).
     * Returns assoc: product_id => qty (variation_id = null rows only).
     */
    public function get_branch_stock_map(int $branch_id): array
    {
        $rows = $this->db
            ->select('product_id, variation_id, quantity')
            ->where('branch_id', $branch_id)
            ->get(db_prefix() . 'pos_inventory')
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $key = $row['variation_id']
                ? "p{$row['product_id']}_v{$row['variation_id']}"
                : "p{$row['product_id']}";
            $map[$key] = (float) $row['quantity'];
        }
        return $map;
    }

    /**
     * Get low-stock items for a branch.
     */
    public function get_low_stock(int $branch_id, int $threshold_override = 0): array
    {
        return $this->db
            ->select('i.*, p.name AS product_name, p.sku, p.reorder_point, c.name AS category_name')
            ->from(db_prefix() . 'pos_inventory i')
            ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
            ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
            ->where('i.branch_id', $branch_id)
            ->where('p.track_inventory', 1)
            ->where('p.is_active', 1)
            ->where('i.quantity <=', $threshold_override ?: 'p.reorder_point', false)
            ->order_by('i.quantity', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Get batch details for a product at a branch (FIFO order by date_created).
     */
    public function get_batches(int $branch_id, int $product_id, ?int $variation_id = null): array
    {
        $this->db->where('branch_id', $branch_id)
                 ->where('product_id', $product_id)
                 ->where('quantity >', 0);

        if ($variation_id !== null) {
            $this->db->where('variation_id', $variation_id);
        }

        return $this->db->order_by('date_created', 'ASC')
                        ->get(db_prefix() . 'pos_inventory_batches')
                        ->result_array();
    }

    /**
     * Get items nearing expiry within $days days.
     */
    public function get_expiring_soon(int $branch_id, int $days = 30): array
    {
        return $this->db
            ->select('b.*, p.name AS product_name, p.sku')
            ->from(db_prefix() . 'pos_inventory_batches b')
            ->join(db_prefix() . 'pos_products p', 'p.id = b.product_id')
            ->where('b.branch_id', $branch_id)
            ->where('b.quantity >', 0)
            ->where('b.expiry_date IS NOT NULL')
            ->where('b.expiry_date <=', date('Y-m-d', strtotime("+{$days} days")))
            ->order_by('b.expiry_date', 'ASC')
            ->get()
            ->result_array();
    }

    // ─── Stock mutations ──────────────────────────────────────────────────────

    /**
     * FIFO deduction for a sale line item.
     * Deducts from the oldest batches first.
     * Returns COGS (cost of goods sold) for the quantity deducted.
     */
    public function deduct_fifo(
        int $branch_id,
        int $product_id,
        float $quantity,
        int $sale_id,
        ?int $variation_id = null,
        ?int $staff_id = null
    ): float {
        $this->db->trans_begin();

        try {
            $remaining_qty = $quantity;
            $total_cogs    = 0.0;

            // Update aggregate inventory row
            $inv = $this->_get_inventory_row($branch_id, $product_id, $variation_id);
            if (!$inv) {
                $this->db->trans_rollback();
                return 0.0;
            }

            $qty_before = (float) $inv['quantity'];
            $qty_after  = $qty_before - $quantity;

            $this->_upsert_inventory($branch_id, $product_id, $variation_id, $qty_after);

            // FIFO: consume oldest batches
            if ($remaining_qty > 0) {
                $batches = $this->get_batches($branch_id, $product_id, $variation_id);

                foreach ($batches as $batch) {
                    if ($remaining_qty <= 0) {
                        break;
                    }

                    $take  = min((float) $batch['quantity'], $remaining_qty);
                    $total_cogs   += $take * (float) $batch['cost_price'];
                    $remaining_qty -= $take;

                    $this->db
                        ->where('id', $batch['id'])
                        ->set('quantity', (float) $batch['quantity'] - $take)
                        ->update(db_prefix() . 'pos_inventory_batches');
                }
            }

            // Log movement
            $this->_log_movement($branch_id, $product_id, $variation_id, 'sale', $sale_id, 'pos_sale',
                $qty_before, -$quantity, $qty_after, $total_cogs / max($quantity, 1), $staff_id);

            $this->db->trans_commit();
            return $total_cogs;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', '[POS Inventory] FIFO deduction failed: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Restore stock on refund.
     */
    public function restore(
        int $branch_id,
        int $product_id,
        float $quantity,
        int $sale_id,
        float $unit_cost = 0.0,
        ?int $variation_id = null,
        ?int $staff_id = null
    ): void {
        $inv        = $this->_get_inventory_row($branch_id, $product_id, $variation_id);
        $qty_before = $inv ? (float) $inv['quantity'] : 0.0;
        $qty_after  = $qty_before + $quantity;

        $this->_upsert_inventory($branch_id, $product_id, $variation_id, $qty_after);
        $this->_log_movement($branch_id, $product_id, $variation_id, 'refund', $sale_id, 'pos_sale',
            $qty_before, $quantity, $qty_after, $unit_cost, $staff_id);
    }

    /**
     * Manual stock adjustment (positive = add, negative = remove).
     */
    public function adjust(
        int $branch_id,
        int $product_id,
        float $adjustment,
        string $reason,
        ?int $variation_id = null,
        float $unit_cost = 0.0,
        ?int $staff_id = null
    ): array {
        $inv        = $this->_get_inventory_row($branch_id, $product_id, $variation_id);
        $qty_before = $inv ? (float) $inv['quantity'] : 0.0;
        $qty_after  = max(0, $qty_before + $adjustment);

        $this->_upsert_inventory($branch_id, $product_id, $variation_id, $qty_after);
        $this->_log_movement($branch_id, $product_id, $variation_id, 'adjustment', null, $reason,
            $qty_before, $adjustment, $qty_after, $unit_cost, $staff_id);

        return ['qty_before' => $qty_before, 'qty_after' => $qty_after, 'adjustment' => $adjustment];
    }

    /**
     * Transfer stock between branches.
     */
    public function transfer(
        int $from_branch,
        int $to_branch,
        int $product_id,
        float $quantity,
        ?int $variation_id = null,
        float $unit_cost = 0.0,
        ?int $staff_id = null
    ): array {
        $this->db->trans_begin();

        try {
            // Deduct from source
            $src_inv   = $this->_get_inventory_row($from_branch, $product_id, $variation_id);
            $src_before = $src_inv ? (float) $src_inv['quantity'] : 0.0;

            if ($src_before < $quantity) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Insufficient stock at source branch'];
            }

            $src_after = $src_before - $quantity;
            $this->_upsert_inventory($from_branch, $product_id, $variation_id, $src_after);
            $this->_log_movement($from_branch, $product_id, $variation_id, 'transfer_out', null, "To branch {$to_branch}",
                $src_before, -$quantity, $src_after, $unit_cost, $staff_id);

            // Add to destination
            $dst_inv    = $this->_get_inventory_row($to_branch, $product_id, $variation_id);
            $dst_before = $dst_inv ? (float) $dst_inv['quantity'] : 0.0;
            $dst_after  = $dst_before + $quantity;
            $this->_upsert_inventory($to_branch, $product_id, $variation_id, $dst_after);
            $this->_log_movement($to_branch, $product_id, $variation_id, 'transfer_in', null, "From branch {$from_branch}",
                $dst_before, $quantity, $dst_after, $unit_cost, $staff_id);

            $this->db->trans_commit();
            return ['success' => true, 'transferred' => $quantity];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Add a new inventory batch (purchase receive).
     */
    public function receive_batch(
        int $branch_id,
        int $product_id,
        float $quantity,
        float $cost_price,
        string $batch_number,
        ?string $expiry_date = null,
        ?int $variation_id = null,
        ?int $staff_id = null
    ): int {
        $this->db->insert(db_prefix() . 'pos_inventory_batches', [
            'branch_id'    => $branch_id,
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'batch_number' => $batch_number,
            'quantity'     => $quantity,
            'cost_price'   => $cost_price,
            'expiry_date'  => $expiry_date,
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        $batch_id = $this->db->insert_id();

        // Update aggregate stock
        $inv        = $this->_get_inventory_row($branch_id, $product_id, $variation_id);
        $qty_before = $inv ? (float) $inv['quantity'] : 0.0;
        $qty_after  = $qty_before + $quantity;
        $this->_upsert_inventory($branch_id, $product_id, $variation_id, $qty_after);
        $this->_log_movement($branch_id, $product_id, $variation_id, 'purchase', $batch_id, 'pos_inventory_batches',
            $qty_before, $quantity, $qty_after, $cost_price, $staff_id);

        return $batch_id;
    }

    /**
     * Inventory valuation for a branch (using weighted average cost).
     */
    public function valuation_report(int $branch_id): array
    {
        return $this->db
            ->select('
                p.id AS product_id, p.name, p.sku,
                i.quantity AS stock_qty,
                COALESCE(b.avg_cost, p.cost_price) AS avg_cost,
                (i.quantity * COALESCE(b.avg_cost, p.cost_price)) AS total_value
            ')
            ->from(db_prefix() . 'pos_inventory i')
            ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
            ->join(
                "(SELECT product_id, AVG(cost_price) AS avg_cost
                  FROM " . db_prefix() . "pos_inventory_batches
                  WHERE branch_id = {$branch_id} AND quantity > 0
                  GROUP BY product_id) b",
                'b.product_id = i.product_id',
                'left'
            )
            ->where('i.branch_id', $branch_id)
            ->where('i.variation_id IS NULL')
            ->where('p.track_inventory', 1)
            ->where('i.quantity >', 0)
            ->order_by('total_value DESC', false)
            ->get()
            ->result_array();
    }

    // ─── Movement history ─────────────────────────────────────────────────────

    public function get_movements(int $branch_id, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $this->db
            ->select('m.*, p.name AS product_name, p.sku, CONCAT(s.firstname,\' \',s.lastname) AS staff_name')
            ->from(db_prefix() . 'pos_inventory_movements m')
            ->join(db_prefix() . 'pos_products p', 'p.id = m.product_id', 'left')
            ->join(db_prefix() . 'staff s', 's.id = m.staff_id', 'left')
            ->where('m.branch_id', $branch_id);

        if (!empty($filters['product_id'])) {
            $this->db->where('m.product_id', $filters['product_id']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('m.type', $filters['type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(m.date_created) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(m.date_created) <=', $filters['date_to']);
        }

        return $this->db->order_by('m.date_created', 'DESC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function _get_inventory_row(int $branch_id, int $product_id, ?int $variation_id): ?array
    {
        $this->db->where('branch_id', $branch_id)->where('product_id', $product_id);
        if ($variation_id !== null) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL');
        }
        $row = $this->db->get(db_prefix() . 'pos_inventory')->row_array();
        return $row ?: null;
    }

    private function _upsert_inventory(int $branch_id, int $product_id, ?int $variation_id, float $qty): void
    {
        $data = ['quantity' => $qty, 'date_updated' => date('Y-m-d H:i:s')];

        $this->db->where('branch_id', $branch_id)->where('product_id', $product_id);
        if ($variation_id !== null) {
            $this->db->where('variation_id', $variation_id);
        } else {
            $this->db->where('variation_id IS NULL');
        }

        if ($this->db->get(db_prefix() . 'pos_inventory')->num_rows() > 0) {
            $this->db->where('branch_id', $branch_id)->where('product_id', $product_id);
            if ($variation_id !== null) {
                $this->db->where('variation_id', $variation_id);
            } else {
                $this->db->where('variation_id IS NULL');
            }
            $this->db->update(db_prefix() . 'pos_inventory', $data);
        } else {
            $this->db->insert(db_prefix() . 'pos_inventory', array_merge($data, [
                'branch_id'    => $branch_id,
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
            ]));
        }
    }

    private function _log_movement(
        int $branch_id, int $product_id, ?int $variation_id,
        string $type, ?int $ref_id, string $ref_type,
        float $before, float $change, float $after,
        float $unit_cost = 0.0, ?int $staff_id = null
    ): void {
        $this->db->insert(db_prefix() . 'pos_inventory_movements', [
            'branch_id'      => $branch_id,
            'product_id'     => $product_id,
            'variation_id'   => $variation_id,
            'type'           => $type,
            'reference_id'   => $ref_id,
            'reference_type' => $ref_type,
            'qty_before'     => $before,
            'qty_change'     => $change,
            'qty_after'      => $after,
            'unit_cost'      => $unit_cost,
            'staff_id'       => $staff_id,
            'date_created'   => date('Y-m-d H:i:s'),
        ]);
    }
}
