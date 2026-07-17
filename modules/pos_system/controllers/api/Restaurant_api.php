<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /api/restaurant/tables          → list available tables for branch
 * POST /api/restaurant/kot             → create a KOT from terminal
 * POST /api/restaurant/kot/{id}/status → update KOT status
 */
class Restaurant_api extends Pos_api
{
    public function tables()
    {
        $this->require_auth();

        $tables = $this->db
            ->where('branch_id', $this->auth_branch_id)
            ->where('is_active', 1)
            ->order_by('table_number', 'ASC')
            ->get(db_prefix() . 'pos_restaurant_tables')
            ->result_array();

        $this->ok($tables);
    }

    public function kot()
    {
        $this->require_auth();

        $body = $this->json_body();
        $this->validate($body, [
            'table_id' => 'required|integer',
            'items'    => 'required',
        ]);


        if (empty($body['items']) || !is_array($body['items'])) {
            $this->error('Items array is required', 400, self::ERR_VALIDATION);
        }

        $table_id = (int) $body['table_id'];

        // Verify table belongs to this branch
        $table = $this->db
            ->where('id', $table_id)
            ->where('branch_id', $this->auth_branch_id)
            ->get(db_prefix() . 'pos_restaurant_tables')
            ->row_array();

        if (!$table) {
            $this->error('Table not found', 404, self::ERR_NOT_FOUND);
        }

        // Resolve default area for branch (first active area, fallback 0)
        $area_id = (int)($body['area_id'] ?? 0);
        if (!$area_id) {
            $area_row = $this->db
                ->select('id')
                ->where('branch_id', $this->auth_branch_id)
                ->where('is_active', 1)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get(db_prefix() . 'pos_restaurant_areas')
                ->row_array();
            $area_id = (int)($area_row['id'] ?? 0);
        }

        // Generate KOT number: KOT-{branchId}-{YYYYMMDD}-{seq}
        $today_prefix = 'KOT-' . $this->auth_branch_id . '-' . date('Ymd') . '-';
        $last = $this->db
            ->select('kot_number')
            ->like('kot_number', $today_prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get(db_prefix() . 'pos_restaurant_kots')
            ->row_array();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last['kot_number']);
            $seq   = (int) end($parts) + 1;
        }
        $kot_number = $today_prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

        // Waiter name
        $staff = $this->db
            ->select('firstname, lastname')
            ->where('staffid', $this->auth_staff_id)
            ->get(db_prefix() . 'staff')
            ->row_array();
        $waiter_name = $staff ? trim($staff['firstname'] . ' ' . $staff['lastname']) : '';

        // Resolve customer name if provided
        $customer_id   = (int)($body['customer_id'] ?? 0) ?: null;
        $customer_name = '';
        if ($customer_id) {
            $cust = $this->db->select('name')->where('id', $customer_id)->get(db_prefix() . 'pos_customers')->row_array();
            $customer_name = $cust ? $cust['name'] : '';
        }

        $kot_data = [
            'kot_number'    => $kot_number,
            'branch_id'     => $this->auth_branch_id,
            'area_id'       => $area_id,
            'table_id'      => $table_id,
            'table_number'  => $table['table_number'],
            'covers'        => max(1, (int)($body['covers'] ?? 1)),
            'customer_id'   => $customer_id,
            'customer_name' => substr(trim($customer_name), 0, 200),
            'order_type'    => in_array($body['order_type'] ?? '', ['dine-in','takeaway','bar']) ? $body['order_type'] : 'dine-in',
            'waiter_id'     => $this->auth_staff_id,
            'waiter_name'   => $waiter_name,
            'status'        => 'pending',
            'notes'         => substr(trim((string)($body['notes'] ?? '')), 0, 500),
            'date_created'  => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'pos_restaurant_kots', $kot_data);
        $kot_id = $this->db->insert_id();

        foreach ($body['items'] as $item) {
            $this->db->insert(db_prefix() . 'pos_restaurant_kot_items', [
                'kot_id'       => $kot_id,
                'product_id'   => (int)($item['product_id'] ?? 0),
                'product_name' => substr(trim((string)($item['product_name'] ?? '')), 0, 255),
                'quantity'     => max(1, (float)($item['quantity'] ?? 1)),
                'notes'        => substr(trim((string)($item['notes'] ?? '')), 0, 255),
            ]);
        }

        // Mark table as occupied
        $this->db->where('id', $table_id)
                 ->update(db_prefix() . 'pos_restaurant_tables', ['status' => 'occupied']);

        $this->log_activity('restaurant.kot.create', [
            'kot_id'     => $kot_id,
            'table_id'   => $table_id,
            'item_count' => count($body['items']),
        ]);

        $this->ok(['kot_id' => $kot_id, 'kot_number' => $kot_number], 201);
    }

    public function kot_status(int $id)
    {
        $this->require_auth();

        $body    = $this->json_body();
        $allowed = ['pending', 'preparing', 'ready', 'served', 'cancelled'];

        if (empty($body['status']) || !in_array($body['status'], $allowed, true)) {
            $this->error('Invalid status', 400, self::ERR_VALIDATION);
        }

        $kot = $this->db
            ->where('id', $id)
            ->where('branch_id', $this->auth_branch_id)
            ->get(db_prefix() . 'pos_restaurant_kots')
            ->row_array();

        if (!$kot) {
            $this->error('KOT not found', 404, self::ERR_NOT_FOUND);
        }

        $update = ['status' => $body['status']];
        if ($body['status'] === 'preparing') $update['started_at'] = date('Y-m-d H:i:s');
        if ($body['status'] === 'ready')     $update['ready_at']   = date('Y-m-d H:i:s');
        if ($body['status'] === 'served')    $update['served_at']  = date('Y-m-d H:i:s');

        $this->db->where('id', $id)->update(db_prefix() . 'pos_restaurant_kots', $update);

        // Deduct ingredients from inventory when KOT is marked ready
        if ($body['status'] === 'ready' && $kot['status'] !== 'ready') {
            $this->_deduct_kot_ingredients($id, $kot['branch_id']);
        }

        $this->ok(['success' => true, 'status' => $body['status']]);
    }

    /**
     * Public helper — called by Sales_api after a sale is committed.
     * Creates a KOT without requiring HTTP auth (caller must be trusted server code).
     */
    public function create_kot_from_sale(int $branch_id, int $staff_id, int $table_id, array $items, array $opts = []): int
    {
        $table = $this->db
            ->where('id', $table_id)
            ->where('branch_id', $branch_id)
            ->get(db_prefix() . 'pos_restaurant_tables')
            ->row_array();

        if (!$table) return 0;

        // Default area
        $area_row = $this->db
            ->select('id')->where('branch_id', $branch_id)->where('is_active', 1)
            ->order_by('id', 'ASC')->limit(1)
            ->get(db_prefix() . 'pos_restaurant_areas')->row_array();
        $area_id = (int) ($area_row['id'] ?? 0);

        // KOT number
        $today_prefix = 'KOT-' . $branch_id . '-' . date('Ymd') . '-';
        $last = $this->db
            ->select('kot_number')->like('kot_number', $today_prefix, 'after')
            ->order_by('id', 'DESC')->limit(1)
            ->get(db_prefix() . 'pos_restaurant_kots')->row_array();
        $seq = 1;
        if ($last) { $parts = explode('-', $last['kot_number']); $seq = (int) end($parts) + 1; }
        $kot_number = $today_prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

        // Waiter
        $staff       = $this->db->select('firstname, lastname')->where('staffid', $staff_id)->get(db_prefix() . 'staff')->row_array();
        $waiter_name = $staff ? trim($staff['firstname'] . ' ' . $staff['lastname']) : '';

        // Customer name
        $customer_id   = (int) ($opts['customer_id'] ?? 0) ?: null;
        $customer_name = '';
        if ($customer_id) {
            $cust = $this->db->select('name')->where('id', $customer_id)->get(db_prefix() . 'pos_customers')->row_array();
            $customer_name = $cust ? $cust['name'] : '';
        }

        $order_type = in_array($opts['order_type'] ?? '', ['dine-in', 'takeaway', 'bar']) ? $opts['order_type'] : 'dine-in';

        $this->db->insert(db_prefix() . 'pos_restaurant_kots', [
            'kot_number'    => $kot_number,
            'branch_id'     => $branch_id,
            'area_id'       => $area_id,
            'table_id'      => $table_id,
            'table_number'  => $table['table_number'],
            'covers'        => max(1, (int) ($opts['covers'] ?? 1)),
            'customer_id'   => $customer_id,
            'customer_name' => substr(trim($customer_name), 0, 200),
            'order_type'    => $order_type,
            'waiter_id'     => $staff_id,
            'waiter_name'   => $waiter_name,
            'status'        => 'pending',
            'notes'         => substr(trim((string) ($opts['notes'] ?? '')), 0, 500),
            'date_created'  => date('Y-m-d H:i:s'),
        ]);
        $kot_id = (int) $this->db->insert_id();

        foreach ($items as $item) {
            $this->db->insert(db_prefix() . 'pos_restaurant_kot_items', [
                'kot_id'       => $kot_id,
                'product_id'   => (int) ($item['product_id'] ?? 0),
                'product_name' => substr(trim((string) ($item['product_name'] ?? '')), 0, 255),
                'quantity'     => max(1, (float) ($item['quantity'] ?? 1)),
                'notes'        => substr(trim((string) ($item['notes'] ?? '')), 0, 255),
            ]);
        }

        // Mark table occupied
        $this->db->where('id', $table_id)->update(db_prefix() . 'pos_restaurant_tables', ['status' => 'occupied']);

        return $kot_id;
    }

    /**
     * Deduct recipe ingredients from inventory for all items in a KOT.
     * Called once when KOT transitions to "ready".
     */
    private function _deduct_kot_ingredients(int $kot_id, int $branch_id): void
    {
        $items = $this->db
            ->where('kot_id', $kot_id)
            ->get(db_prefix() . 'pos_restaurant_kot_items')
            ->result_array();

        foreach ($items as $item) {
            $product_id = (int)$item['product_id'];
            $kot_qty    = (float)$item['quantity'];

            // Find active recipe for this product
            $recipe = $this->db
                ->select('id')
                ->where('product_id', $product_id)
                ->where('is_active', 1)
                ->limit(1)
                ->get(db_prefix() . 'pos_restaurant_recipes')
                ->row_array();

            if (!$recipe) continue;

            // Get recipe ingredients
            $ing_items = $this->db
                ->where('recipe_id', $recipe['id'])
                ->get(db_prefix() . 'pos_restaurant_recipe_items')
                ->result_array();

            foreach ($ing_items as $ri) {
                $ing_id  = (int)$ri['ingredient_id'];
                $deduct  = (float)$ri['quantity'] * $kot_qty;

                $inv = $this->db
                    ->where('product_id', $ing_id)
                    ->where('branch_id', $branch_id)
                    ->where('variation_id', null)
                    ->limit(1)
                    ->get(db_prefix() . 'pos_inventory')
                    ->row_array();

                if ($inv) {
                    $new_qty = max(0, (float)$inv['quantity'] - $deduct);
                    $this->db->where('id', $inv['id'])
                             ->update(db_prefix() . 'pos_inventory', [
                                 'quantity'     => $new_qty,
                                 'date_updated' => date('Y-m-d H:i:s'),
                             ]);
                }
            }
        }
    }
}
