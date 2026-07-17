<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_integrations — bridges POS with the Warehouse and Accounting modules.
 *
 * Warehouse integration:
 *   - Reads stock levels from {prefix}inventory_manage (warehouse module table)
 *   - Reads sellable items from {prefix}items (core, extended by warehouse)
 *   - Deducts stock from inventory_manage when a sale is made
 *
 * Accounting integration:
 *   - Posts double-entry GL entries to {prefix}acc_account_history
 *   - Resolves account IDs from {prefix}acc_accounts by key_name
 *   - Fallback: uses POS settings for custom account keys
 */
class Pos_integrations
{
    /** @var CI_DB_active_record */
    private $db;

    /** @var bool  */
    private $warehouse_active = false;

    /** @var bool */
    private $accounting_active = false;

    public function __construct()
    {
        $CI = &get_instance();
        $this->db = $CI->db;

        $this->warehouse_active  = $this->db->table_exists(db_prefix() . 'inventory_manage');
        $this->accounting_active = $this->db->table_exists(db_prefix() . 'acc_accounts')
                                && $this->db->table_exists(db_prefix() . 'acc_account_history');
    }

    // ─── Warehouse: item/product catalog ────────────────────────────────────

    /**
     * Return sellable items from the shared items table (+ warehouse stock).
     * Merges with pos_products overrides (price, category, visibility flags).
     *
     * @param  int    $branch_id   POS branch — used to pick the warehouse
     * @param  array  $filters     search, category_id, stock (low|out)
     * @param  int    $limit
     * @param  int    $offset
     * @return array
     */
    public function get_sellable_items(int $branch_id, array $filters = [], int $limit = 200, int $offset = 0): array
    {
        $warehouse_id = $this->_branch_warehouse($branch_id);

        // Stock qty expression depends on whether warehouse module is installed
        $stock_expr = $this->warehouse_active
            ? 'COALESCE(CAST(im.inventory_number AS DECIMAL(15,2)), 0)'
            : '0';

        // Base: core items table (shared across invoices/warehouse/POS)
        $this->db
            ->select("
                i.id,
                i.description AS name,
                i.rate        AS selling_price,
                i.unit,
                i.group_id    AS category_id,
                i.commodity_code AS sku_code,
                'warehouse'   AS source,
                {$stock_expr} AS warehouse_qty,
                {$stock_expr} AS stock_qty,
                COALESCE(po.id, 0)       AS pos_product_id,
                COALESCE(po.sku, i.sku_code, CONCAT('ITEM-', i.id)) AS sku,
                COALESCE(po.barcode, i.commodity_barcode, '') AS barcode,
                COALESCE(po.is_pos_visible, 1)  AS is_pos_visible,
                COALESCE(po.selling_price, i.rate) AS final_price,
                po.tax_rate_id,
                ig.name AS category_name,
                t.taxrate AS tax_rate_value,
                t.name    AS tax_rate_name,
                i.tax AS perfex_tax_id,
                i.purchase_price AS cost_price,
                0 AS has_variations,
                COALESCE(po.allow_negative, IF(i.without_checking_warehouse = 1, 1, 0), 0) AS allow_negative
            ", false)
            ->from(db_prefix() . 'items i');

        // Warehouse stock — column is inventory_number (varchar), cast to decimal
        if ($this->warehouse_active) {
            if ($warehouse_id) {
                $this->db->join(
                    db_prefix() . 'inventory_manage im',
                    'im.commodity_id = i.id AND im.warehouse_id = ' . (int)$warehouse_id,
                    'left'
                );
            } else {
                // Sum across all warehouses
                $this->db->join(
                    '(SELECT commodity_id, SUM(CAST(inventory_number AS DECIMAL(15,2))) AS inventory_number FROM ' . db_prefix() . 'inventory_manage GROUP BY commodity_id) im',
                    'im.commodity_id = i.id',
                    'left'
                );
            }
        }

        // POS-specific overrides (price, category, visibility flags)
        $this->db->join(
            db_prefix() . 'pos_products po',
            'po.perfex_item_id = i.id',
            'left'
        );

        // Item groups/categories
        $this->db->join(
            db_prefix() . 'items_groups ig',
            'ig.id = i.group_id',
            'left'
        );

        // Tax rate (Perfex core taxes table)
        $this->db->join(
            db_prefix() . 'taxes t',
            't.id = i.tax',
            'left'
        );

        // Only active items
        $this->db->where('i.active', 1);

        // Sellable: warehouse uses VARCHAR 'can_be_sold' as flag value ('can_be_sold' or '1'),
        // empty string means unchecked but still included, NULL means column absent (include)
        $this->db->group_start()
                 ->where('i.can_be_sold', 'can_be_sold')
                 ->or_where('i.can_be_sold', '1')
                 ->or_where('i.can_be_sold', '')
                 ->or_where('i.can_be_sold IS NULL', null, false)
                 ->group_end();

        // Exclude parent (bundle) items — leaf nodes only
        $this->db->where('i.id NOT IN (SELECT DISTINCT parent_id FROM ' . db_prefix() . 'items WHERE parent_id IS NOT NULL AND parent_id != 0)', null, false);

        // POS-visibility: not explicitly hidden
        $this->db->group_start()
                 ->where('po.is_pos_visible', 1)
                 ->or_where('po.is_pos_visible IS NULL', null, false)
                 ->group_end();

        $this->_apply_filters($filters);

        $items = $this->db
            ->order_by('i.description', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();

        // Lazy-sync: auto-create tblpos_products rows for warehouse items that don't have one yet.
        // This ensures pos_product_id is always non-zero so the FK on tblpos_sale_items is satisfied.
        foreach ($items as &$item) {
            if ((int) $item['pos_product_id'] === 0) {
                $data = [
                    'name'            => $item['name'],
                    'selling_price'   => $item['selling_price'],
                    'unit'            => $item['unit'] ?? 'pcs',
                    'barcode'         => $item['barcode'] ?: null,
                    'sku'             => $item['sku'] ?: 'ITEM-' . $item['id'],
                    'is_active'       => 1,
                    'is_pos_visible'  => 1,
                    'type'            => 'simple',
                    'perfex_item_id'  => $item['id'],
                    'date_created'    => date('Y-m-d H:i:s'),
                ];
                $this->db->insert(db_prefix() . 'pos_products', $data);
                $item['pos_product_id'] = $this->db->insert_id();
            }
        }
        unset($item);

        // Also include POS-only products (no perfex_item_id)
        $pos_only = $this->_get_pos_only_products($branch_id, $filters, $limit, $offset);

        return array_merge($items, $pos_only);
    }

    /**
     * Count for pagination.
     */
    public function count_sellable_items(int $branch_id, array $filters = []): int
    {
        $warehouse_id = $this->_branch_warehouse($branch_id);

        $this->db->from(db_prefix() . 'items i');

        if ($this->warehouse_active) {
            if ($warehouse_id) {
                $this->db->join(
                    db_prefix() . 'inventory_manage im',
                    'im.commodity_id = i.id AND im.warehouse_id = ' . (int)$warehouse_id,
                    'left'
                );
            } else {
                $this->db->join(
                    '(SELECT commodity_id, SUM(CAST(inventory_number AS DECIMAL(15,2))) AS inventory_number FROM ' . db_prefix() . 'inventory_manage GROUP BY commodity_id) im',
                    'im.commodity_id = i.id',
                    'left'
                );
            }
        }

        $this->db->join(db_prefix() . 'pos_products po', 'po.perfex_item_id = i.id', 'left');

        $this->db->where('i.active', 1)
                 ->group_start()
                 ->where('i.can_be_sold', 'can_be_sold')
                 ->or_where('i.can_be_sold', '')
                 ->or_where('i.can_be_sold IS NULL', null, false)
                 ->group_end()
                 ->group_start()
                 ->where('po.is_pos_visible', 1)
                 ->or_where('po.is_pos_visible IS NULL', null, false)
                 ->group_end();

        $this->_apply_filters($filters);

        return (int) $this->db->count_all_results();
    }

    // ─── Warehouse: stock deduction ──────────────────────────────────────────

    /**
     * Deduct stock from warehouse inventory_manage when a POS sale is completed.
     * Skips gracefully if the warehouse module is not installed.
     *
     * @param  int    $item_id     The core items.id (perfex_item_id)
     * @param  float  $qty         Quantity to deduct
     * @param  int    $branch_id   POS branch
     * @param  int    $sale_id     For movement tracking
     * @return bool
     */
    public function deduct_warehouse_stock(int $item_id, float $qty, int $branch_id, int $sale_id = 0): bool
    {
        if (!$this->warehouse_active || !$item_id) {
            return false;
        }

        $warehouse_id = $this->_branch_warehouse($branch_id);

        $query = $this->db->where('commodity_id', $item_id);
        if ($warehouse_id) {
            $query = $query->where('warehouse_id', $warehouse_id);
        }

        $record = $this->db->get(db_prefix() . 'inventory_manage')->row();

        if (!$record) {
            return false;
        }

        // Column is 'inventory_number' (varchar) in the warehouse module
        $new_qty = max(0, (float)$record->inventory_number - $qty);

        $this->db->where('id', $record->id)
                 ->update(db_prefix() . 'inventory_manage', ['inventory_number' => (string)$new_qty]);

        return true;
    }

    /**
     * Restore stock to warehouse on refund.
     */
    public function restore_warehouse_stock(int $item_id, float $qty, int $branch_id, int $sale_id = 0): bool
    {
        if (!$this->warehouse_active || !$item_id) {
            return false;
        }

        $warehouse_id = $this->_branch_warehouse($branch_id);

        $query = $this->db->where('commodity_id', $item_id);
        if ($warehouse_id) {
            $query = $query->where('warehouse_id', $warehouse_id);
        }

        $record = $this->db->get(db_prefix() . 'inventory_manage')->row();

        if (!$record) {
            return false;
        }

        // Column is 'inventory_number' (varchar) in the warehouse module
        $this->db->where('id', $record->id)
                 ->set('inventory_number', 'CAST(inventory_number AS DECIMAL(15,4)) + ' . (float)$qty, false)
                 ->update(db_prefix() . 'inventory_manage');

        return true;
    }

    /**
     * Sync all warehouse items into pos_products for the POS catalog.
     * Creates/updates pos_products rows for every sellable warehouse item.
     *
     * @return int  Number of items synced
     */
    public function sync_warehouse_items_to_pos(): int
    {
        if (!$this->warehouse_active) {
            return 0;
        }

        $items = $this->db
            ->select('i.id, i.description, i.rate, i.unit, i.group_id, i.commodity_barcode, i.sku_code, i.active')
            ->from(db_prefix() . 'items i')
            ->where('i.active', 1)
            ->group_start()
            ->where('i.can_be_sold', 'can_be_sold')
            ->or_where('i.can_be_sold IS NULL')
            ->group_end()
            ->where('i.id NOT IN (SELECT DISTINCT parent_id FROM ' . db_prefix() . 'items WHERE parent_id IS NOT NULL AND parent_id != 0)', null, false)
            ->get()
            ->result_array();

        $synced = 0;
        foreach ($items as $item) {
            $existing = $this->db
                ->where('perfex_item_id', $item['id'])
                ->get(db_prefix() . 'pos_products')
                ->row();

            $data = [
                'name'          => $item['description'],
                'selling_price' => $item['rate'],
                'unit'          => $item['unit'] ?? 'pcs',
                'barcode'       => $item['commodity_barcode'] ?? null,
                'sku'           => $item['sku_code'] ?: 'ITEM-' . $item['id'],
                'is_active'     => 1,
                'is_pos_visible'=> 1,
                'type'          => 'simple',
            ];

            if ($existing) {
                $this->db->where('id', $existing->id)->update(db_prefix() . 'pos_products', $data);
            } else {
                $data['perfex_item_id'] = $item['id'];
                $data['date_created']   = date('Y-m-d H:i:s');
                $this->db->insert(db_prefix() . 'pos_products', $data);
            }
            $synced++;
        }

        return $synced;
    }

    // ─── Accounting: GL posting ──────────────────────────────────────────────

    /**
     * Post a completed POS sale to the accounting module's general ledger.
     *
     * Journal:
     *   DR  Cash/Mobile Money/Card account   = payment amount per method
     *   CR  Revenue account                  = subtotal
     *   CR  Tax Payable account              = tax amount
     *
     * @param  array $sale      Row from pos_sales
     * @param  array $items     Rows from pos_sale_items
     * @param  array $payments  Rows from pos_payments + payment_method type
     * @param  int   $staff_id
     * @return bool
     */
    public function post_sale_to_accounting(array $sale, array $items, array $payments, int $staff_id = 0): bool
    {
        if (!$this->accounting_active) {
            return false;
        }

        $branch_id  = (int) $sale['branch_id'];
        $sale_id    = (int) $sale['id'];
        $date       = date('Y-m-d H:i:s');
        $description = 'POS Sale #' . ($sale['receipt_number'] ?? $sale_id);

        $revenue_key   = pos_get_setting('pos_revenue_account_key', $branch_id) ?: 'acc_sales_of_product_income';
        $revenue_acct  = $this->_resolve_account($revenue_key);

        $tax_acct      = $this->_resolve_account('acc_tax_payable');
        $cash_acct     = $this->_resolve_account(pos_get_setting('pos_cash_account_key', $branch_id) ?: 'acc_petty_cash');

        if (!$revenue_acct) {
            // Accounting module not configured; skip silently
            return false;
        }

        // CR Revenue (subtotal excluding tax)
        $subtotal = (float)($sale['subtotal'] ?? 0);
        if ($subtotal > 0) {
            $this->_gl_entry($revenue_acct, 0, $subtotal, $description, $sale_id, 'pos_sale', $staff_id);
        }

        // CR Tax Payable
        $tax_total = (float)($sale['tax_amount'] ?? 0);
        if ($tax_total > 0 && $tax_acct) {
            $this->_gl_entry($tax_acct, 0, $tax_total, $description . ' (VAT)', $sale_id, 'pos_sale', $staff_id);
        }

        // DR asset accounts per payment method
        foreach ($payments as $payment) {
            $amount  = (float)($payment['amount'] ?? 0);
            $type    = strtolower($payment['payment_type'] ?? $payment['type'] ?? 'cash');
            $acct    = $this->_debit_account_for_type($type, $branch_id);

            if ($amount > 0 && $acct) {
                $this->_gl_entry($acct, $amount, 0, $description . ' via ' . strtoupper($type), $sale_id, 'pos_sale', $staff_id);
            }
        }

        return true;
    }

    /**
     * Reverse accounting entries on refund.
     */
    public function reverse_sale_accounting(int $sale_id, float $amount, int $staff_id = 0): bool
    {
        if (!$this->accounting_active) {
            return false;
        }

        // Mark existing entries as split/reversed rather than deleting
        $this->db->where('rel_id', $sale_id)
                 ->where('rel_type', 'pos_sale')
                 ->set('split', 1)
                 ->update(db_prefix() . 'acc_account_history');

        return true;
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function _branch_warehouse(int $branch_id): ?int
    {
        $val = pos_get_setting('pos_branch_warehouse_' . $branch_id, $branch_id)
            ?: pos_get_setting('pos_branch_warehouse_' . $branch_id);

        return $val ? (int)$val : null;
    }

    private function _apply_filters(array $filters): void
    {
        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('i.description', $term)
                     ->or_like('i.commodity_barcode', $term)
                     ->group_end();
        }

        if (!empty($filters['category_id'])) {
            $this->db->where('i.group_id', (int)$filters['category_id']);
        }

        if (!empty($filters['stock'])) {
            // inventory_manage uses inventory_number (varchar); cast to decimal
            $stock_col = 'COALESCE(CAST(im.inventory_number AS DECIMAL(15,2)), 0)';
            if ($filters['stock'] === 'out') {
                $this->db->where("{$stock_col} <= 0", null, false);
            } elseif ($filters['stock'] === 'low') {
                $threshold = (int)(pos_get_setting('pos_low_stock_threshold') ?: 5);
                $this->db->where("{$stock_col} <= {$threshold}", null, false)
                         ->where("{$stock_col} > 0", null, false);
            }
        }
    }

    private function _get_pos_only_products(int $branch_id, array $filters, int $limit, int $offset): array
    {
        $this->db
            ->select("
                p.id,
                p.name,
                p.selling_price,
                p.unit,
                p.category_id,
                'pos' AS source,
                COALESCE(inv.quantity, 0) AS warehouse_qty,
                COALESCE(inv.quantity, 0) AS stock_qty,
                COALESCE(inv.quantity, 0) AS pos_qty,
                p.id                      AS pos_product_id,
                p.sku,
                p.barcode,
                1  AS is_pos_visible,
                p.selling_price AS final_price,
                p.tax_rate_id,
                c.name AS category_name,
                NULL AS tax_rate_value,
                NULL AS tax_rate_name,
                p.has_variations,
                COALESCE(p.allow_negative, 0) AS allow_negative
            ", false)
            ->from(db_prefix() . 'pos_products p')
            ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
            ->join(
                db_prefix() . 'pos_inventory inv',
                'inv.product_id = p.id AND inv.branch_id = ' . (int)$branch_id . ' AND inv.variation_id IS NULL',
                'left'
            )
            ->where('p.perfex_item_id IS NULL')
            ->where('p.is_active', 1)
            ->where('p.is_pos_visible', 1);

        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('p.name', $term)
                     ->or_like('p.sku', $term)
                     ->or_like('p.barcode', $term)
                     ->group_end();
        }

        return $this->db->order_by('p.name', 'ASC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    /**
     * Find an account ID from acc_accounts by key_name (exact) or name (partial).
     */
    private function _resolve_account(string $key): ?int
    {
        // Try key_name first
        $row = $this->db
            ->where('key_name', $key)
            ->where('active', 1)
            ->get(db_prefix() . 'acc_accounts')
            ->row();

        if ($row) {
            return (int)$row->id;
        }

        // Try by Perfex option (accounting module stores defaults as options)
        $opt_id = get_option($key);
        if ($opt_id && (int)$opt_id > 0) {
            return (int)$opt_id;
        }

        return null;
    }

    /**
     * Map payment type → debit asset account key.
     */
    private function _debit_account_for_type(string $type, int $branch_id): ?int
    {
        $map = [
            'cash'     => pos_get_setting('pos_cash_account_key', $branch_id) ?: 'acc_petty_cash',
            'card'     => 'acc_undeposited_funds',
            'mpesa'    => 'acc_undeposited_funds',
            'airtel'   => 'acc_undeposited_funds',
            'mtn'      => 'acc_undeposited_funds',
            'telebirr' => 'acc_undeposited_funds',
            'bank'     => 'acc_checking',
            'credit'   => 'acc_accounts_receivable',
        ];

        $key = $map[$type] ?? 'acc_undeposited_funds';
        return $this->_resolve_account($key);
    }

    /**
     * Insert one row into acc_account_history.
     */
    private function _gl_entry(int $account_id, float $debit, float $credit, string $desc, int $rel_id, string $rel_type, int $staff_id): void
    {
        $this->db->insert(db_prefix() . 'acc_account_history', [
            'account'     => $account_id,
            'debit'       => $debit,
            'credit'      => $credit,
            'description' => $desc,
            'rel_id'      => $rel_id,
            'rel_type'    => $rel_type,
            'datecreated' => date('Y-m-d H:i:s'),
            'addedfrom'   => $staff_id,
            'paid'        => 1,
        ]);
    }
}
