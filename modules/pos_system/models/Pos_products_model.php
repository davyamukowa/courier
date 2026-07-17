<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_products_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_products';
    }

    /**
     * Fetch products for the POS terminal.
     * Source: tblpos_products (our inventory) + tblpos_inventory for live stock.
     * Tax rates from Perfex core tbltaxes (always available).
     */
    public function get_for_pos(int $branch_id, array $filters = []): array
    {
        $this->db
            ->select("
                p.id,
                p.id            AS pos_product_id,
                p.name,
                p.selling_price,
                p.cost_price,
                p.unit,
                p.category_id,
                p.sku,
                p.barcode,
                p.image,
                p.has_variations,
                p.allow_negative,
                p.is_active,
                p.tax_rate_id,
                'pos'           AS source,
                c.name          AS category_name,
                COALESCE(inv.quantity, 0)  AS stock_qty,
                COALESCE(t.taxrate, 0)     AS tax_rate_value,
                t.name                     AS tax_rate_name
            ", false)
            ->from($this->table . ' p')
            ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
            ->join(
                db_prefix() . 'pos_inventory inv',
                'inv.product_id = p.id AND inv.branch_id = ' . (int)$branch_id . ' AND inv.variation_id IS NULL',
                'left'
            )
            ->join(db_prefix() . 'taxes t', 't.id = p.tax_rate_id', 'left')
            ->where('p.is_active', 1)
            ->where('p.is_pos_visible', 1);

        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', (int)$filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('p.name', $term)
                     ->or_like('p.sku', $term)
                     ->or_like('p.barcode', $term)
                     ->group_end();
        }

        $products = $this->db->order_by('p.name', 'ASC')->get()->result_array();

        foreach ($products as &$product) {
            $product['selling_price']  = (float)$product['selling_price'];
            $product['cost_price']     = (float)($product['cost_price'] ?? 0);
            $product['stock_qty']      = (float)$product['stock_qty'];
            $product['tax_rate_value'] = (float)$product['tax_rate_value'];
            $product['allow_negative'] = (int)$product['allow_negative'];
            $product['has_variations'] = (int)$product['has_variations'];

            if ($product['has_variations']) {
                $product['variations'] = $this->get_product_variations((int)$product['id'], $branch_id);
            }
        }
        unset($product);

        return $products;
    }

    public function get_all(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $this->db->select('p.*, c.name AS category_name')
                 ->from($this->table . ' p')
                 ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left');

        if (isset($filters['is_active'])) {
            $this->db->where('p.is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('p.name', $term)
                     ->or_like('p.sku', $term)
                     ->group_end();
        }

        return $this->db->order_by('p.name', 'ASC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    public function get_all_with_tax(int $limit = 500): array
    {
        return $this->db
            ->select('p.id, p.name, p.selling_price, p.tax_rate_id, p.sku, COALESCE(t.taxrate, 0) AS tax_rate_value, t.name AS tax_name')
            ->from($this->table . ' p')
            ->join(db_prefix() . 'taxes t', 't.id = p.tax_rate_id', 'left')
            ->where('p.is_active', 1)
            ->order_by('p.name', 'ASC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function count(array $filters = []): int
    {
        if (isset($filters['is_active'])) {
            $this->db->where('is_active', $filters['is_active']);
        }
        return (int) $this->db->count_all_results($this->table);
    }

    public function get(int $id): ?array
    {
        $product = $this->db->where('id', $id)->get($this->table)->row_array();
        if (!$product) {
            return null;
        }
        if ($product['has_variations']) {
            $product['variations'] = $this->get_product_variations($id);
        }
        return $product;
    }

    public function find_by_barcode(string $barcode): ?array
    {
        // Check product barcodes
        $product = $this->db->where('barcode', $barcode)->get($this->table)->row_array();
        if ($product) {
            return ['type' => 'product', 'data' => $product];
        }

        // Check variation barcodes
        $variation = $this->db
            ->select('v.*, p.name AS product_name, p.id AS product_id')
            ->from(db_prefix() . 'pos_product_variations v')
            ->join($this->table . ' p', 'p.id = v.product_id')
            ->where('v.barcode', $barcode)
            ->get()
            ->row_array();

        return $variation ? ['type' => 'variation', 'data' => $variation] : null;
    }

    public function create(array $data): int
    {
        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        // --- Sync to core items ---
        $this->load->model('invoice_items_model');
        $core_item = [
            'description'      => $data['name'],
            'long_description' => $data['description'] ?? '',
            'rate'             => $data['selling_price'] ?? 0,
            'unit'             => $data['unit'] ?? 'pcs',
        ];
        if (!empty($data['tax_rate_id'])) {
            $core_item['tax'] = $data['tax_rate_id'];
        }
        $perfex_item_id = $this->invoice_items_model->add($core_item);
        if ($perfex_item_id) {
            $data['perfex_item_id'] = $perfex_item_id;
        }
        // --------------------------

        $this->db->insert($this->table, $data);
        $product_id = $this->db->insert_id();

        if (!empty($variations)) {
            $this->save_variations($product_id, $variations);
            $this->db->where('id', $product_id)->set('has_variations', 1)->update($this->table);
        }

        return $product_id;
    }

    public function update(int $id, array $data): bool
    {
        $variations = $data['variations'] ?? null;
        unset($data['variations']);

        // --- Sync to core items ---
        $this->db->select('perfex_item_id');
        $this->db->where('id', $id);
        $current = $this->db->get($this->table)->row();
        
        $core_item = [];
        if (isset($data['name'])) { $core_item['description'] = $data['name']; }
        if (isset($data['description'])) { $core_item['long_description'] = $data['description']; }
        if (isset($data['selling_price'])) { $core_item['rate'] = $data['selling_price']; }
        if (isset($data['unit'])) { $core_item['unit'] = $data['unit']; }
        if (array_key_exists('tax_rate_id', $data)) { $core_item['tax'] = !empty($data['tax_rate_id']) ? $data['tax_rate_id'] : ''; }

        if (!empty($core_item)) {
            $this->load->model('invoice_items_model');
            if ($current && $current->perfex_item_id) {
                $core_item['itemid'] = $current->perfex_item_id;
                $this->invoice_items_model->edit($core_item);
            } else {
                // If missing for some reason, create it
                if (!isset($core_item['description'])) {
                    $this->db->select('name, description, selling_price, unit, tax_rate_id');
                    $this->db->where('id', $id);
                    $full_current = $this->db->get($this->table)->row();
                    if ($full_current) {
                        $core_item['description'] = $core_item['description'] ?? $full_current->name;
                        $core_item['long_description'] = $core_item['long_description'] ?? $full_current->description;
                        $core_item['rate'] = $core_item['rate'] ?? $full_current->selling_price;
                        $core_item['unit'] = $core_item['unit'] ?? $full_current->unit;
                        if (!empty($full_current->tax_rate_id)) {
                            $core_item['tax'] = $full_current->tax_rate_id;
                        }
                    }
                }
                if (isset($core_item['description'])) {
                    $perfex_item_id = $this->invoice_items_model->add($core_item);
                    if ($perfex_item_id) {
                        $data['perfex_item_id'] = $perfex_item_id;
                    }
                }
            }
        }
        // --------------------------

        $result = (bool) $this->db->where('id', $id)->update($this->table, $data);

        if ($variations !== null) {
            $this->save_variations($id, $variations);
        }

        return $result;
    }

    public function delete(int $id): bool
    {
        $this->db->select('perfex_item_id');
        $this->db->where('id', $id);
        $current = $this->db->get($this->table)->row();

        $result = (bool) $this->db->where('id', $id)->set('is_active', 0)->update($this->table);

        if ($result && $current && $current->perfex_item_id) {
            $this->load->model('invoice_items_model');
            $this->invoice_items_model->delete($current->perfex_item_id);
        }

        return $result;
    }

    public function get_categories(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_product_categories')) {
            return [];
        }

        return $this->db
            ->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get(db_prefix() . 'pos_product_categories')
            ->result_array();
    }

    // ─── Variations ──────────────────────────────────────────────────────────

    private function get_product_variations(int $product_id, ?int $branch_id = null): array
    {
        $this->db->select('v.*')
                 ->from(db_prefix() . 'pos_product_variations v')
                 ->where('v.product_id', $product_id)
                 ->where('v.is_active', 1);

        if ($branch_id) {
            $this->db->select('COALESCE(i.quantity,0) AS stock_qty')
                     ->join(db_prefix() . 'pos_inventory i',
                            'i.variation_id = v.id AND i.branch_id = ' . (int) $branch_id,
                            'left');
        }

        $variations = $this->db->get()->result_array();

        foreach ($variations as &$v) {
            $v['options'] = $this->db
                ->select('a.name AS attribute_name, val.value AS attribute_value')
                ->from(db_prefix() . 'pos_product_variation_options vo')
                ->join(db_prefix() . 'pos_variation_values val', 'val.id = vo.value_id')
                ->join(db_prefix() . 'pos_variation_attributes a', 'a.id = val.attribute_id')
                ->where('vo.variation_id', $v['id'])
                ->get()
                ->result_array();
        }

        return $variations;
    }

    private function save_variations(int $product_id, array $variations): void
    {
        foreach ($variations as $v) {
            $options = $v['options'] ?? [];
            unset($v['options']);
            $v['product_id'] = $product_id;

            if (!empty($v['id'])) {
                $var_id = $v['id'];
                unset($v['id']);
                $this->db->where('id', $var_id)->update(db_prefix() . 'pos_product_variations', $v);
            } else {
                $this->db->insert(db_prefix() . 'pos_product_variations', $v);
                $var_id = $this->db->insert_id();
            }

            // Rebuild options pivot
            $this->db->where('variation_id', $var_id)->delete(db_prefix() . 'pos_product_variation_options');
            foreach ($options as $value_id) {
                $this->db->insert(db_prefix() . 'pos_product_variation_options', [
                    'variation_id' => $var_id,
                    'value_id'     => (int) $value_id,
                ]);
            }
        }
    }

    // ─── Item Form (Full) ─────────────────────────────────────────────────────

    /**
     * Fetch one product with all child-table records attached.
     */
    public function get_full(int $id): ?array
    {
        $product = $this->get($id);
        if (!$product) {
            return null;
        }
        $product['uoms']           = $this->get_item_uoms($id);
        $product['barcodes']       = $this->get_item_barcodes($id);
        $product['reorder_rules']  = $this->get_item_reorder_rules($id);
        $product['item_suppliers'] = $this->get_item_suppliers($id);
        return $product;
    }

    /**
     * Create or update a product together with all child records.
     * Returns the product id.
     */
    public function save_full(array $data, ?int $id = null): int
    {
        $uoms           = $data['uoms']           ?? [];
        $barcodes       = $data['barcodes']       ?? [];
        $reorder_rules  = $data['reorder_rules']  ?? [];
        $item_suppliers = $data['item_suppliers'] ?? [];
        unset($data['uoms'], $data['barcodes'], $data['reorder_rules'], $data['item_suppliers']);

        if ($id) {
            $this->update($id, $data);
            $product_id = $id;
        } else {
            $product_id = $this->create($data);
        }

        $this->save_item_uoms($product_id, $uoms);
        $this->save_item_barcodes($product_id, $barcodes);
        $this->save_item_reorder_rules($product_id, $reorder_rules);
        $this->save_item_suppliers($product_id, $item_suppliers);

        return $product_id;
    }

    /**
     * Generate a unique item code from the item name prefix.
     * Format: {XX}-{00001}
     */
    public function generate_item_code(string $name = ''): string
    {
        $letters = preg_replace('/[^a-zA-Z]/', '', $name);
        $prefix  = strtoupper(substr($letters ?: 'IT', 0, 2));
        if (strlen($prefix) < 2) {
            $prefix = str_pad($prefix, 2, 'X');
        }

        $counter = 1;
        do {
            $code   = $prefix . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
            $exists = (int) $this->db->where('item_code', $code)
                                     ->count_all_results($this->table);
            $counter++;
        } while ($exists > 0 && $counter < 99999);

        return $code;
    }

    // ─── Child: UOMs ─────────────────────────────────────────────────────────

    public function get_item_uoms(int $product_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_uoms')) {
            return [];
        }
        return $this->db
            ->where('product_id', $product_id)
            ->order_by('sort_order', 'ASC')
            ->get(db_prefix() . 'pos_item_uoms')
            ->result_array();
    }

    public function save_item_uoms(int $product_id, array $rows): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_uoms')) {
            return;
        }
        $this->db->where('product_id', $product_id)->delete(db_prefix() . 'pos_item_uoms');
        foreach ($rows as $i => $row) {
            $name = trim($row['uom_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $this->db->insert(db_prefix() . 'pos_item_uoms', [
                'product_id'        => $product_id,
                'uom_id'            => !empty($row['uom_id']) ? (int) $row['uom_id'] : null,
                'uom_name'          => $name,
                'conversion_factor' => (float) ($row['conversion_factor'] ?? 1),
                'sort_order'        => $i,
            ]);
        }
    }

    // ─── Child: Barcodes ─────────────────────────────────────────────────────

    public function get_item_barcodes(int $product_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_barcodes')) {
            return [];
        }
        return $this->db
            ->where('product_id', $product_id)
            ->order_by('sort_order', 'ASC')
            ->get(db_prefix() . 'pos_item_barcodes')
            ->result_array();
    }

    public function save_item_barcodes(int $product_id, array $rows): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_barcodes')) {
            return;
        }
        $this->db->where('product_id', $product_id)->delete(db_prefix() . 'pos_item_barcodes');
        foreach ($rows as $i => $row) {
            $bc = trim($row['barcode'] ?? '');
            if ($bc === '') {
                continue;
            }
            $this->db->insert(db_prefix() . 'pos_item_barcodes', [
                'product_id'   => $product_id,
                'barcode'      => $bc,
                'barcode_type' => $row['barcode_type'] ?? 'EAN',
                'uom_id'       => !empty($row['uom_id']) ? (int) $row['uom_id'] : null,
                'sort_order'   => $i,
            ]);
        }
    }

    // ─── Child: Reorder Rules ─────────────────────────────────────────────────

    public function get_item_reorder_rules(int $product_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_reorder_rules')) {
            return [];
        }
        return $this->db
            ->where('product_id', $product_id)
            ->order_by('sort_order', 'ASC')
            ->get(db_prefix() . 'pos_item_reorder_rules')
            ->result_array();
    }

    public function save_item_reorder_rules(int $product_id, array $rows): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_reorder_rules')) {
            return;
        }
        $this->db->where('product_id', $product_id)->delete(db_prefix() . 'pos_item_reorder_rules');
        foreach ($rows as $i => $row) {
            if (empty($row['reorder_level']) && empty($row['reorder_qty'])) {
                continue;
            }
            $this->db->insert(db_prefix() . 'pos_item_reorder_rules', [
                'product_id'            => $product_id,
                'branch_id'             => !empty($row['branch_id']) ? (int) $row['branch_id'] : null,
                'check_availability_in' => !empty($row['check_availability_in']) ? (int) $row['check_availability_in'] : null,
                'reorder_level'         => (float) ($row['reorder_level'] ?? 0),
                'reorder_qty'           => (float) ($row['reorder_qty'] ?? 0),
                'material_request_type' => $row['material_request_type'] ?? 'Purchase',
                'sort_order'            => $i,
            ]);
        }
    }

    // ─── Child: Supplier Details ──────────────────────────────────────────────

    public function get_item_suppliers(int $product_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_suppliers')) {
            return [];
        }
        return $this->db
            ->where('product_id', $product_id)
            ->order_by('sort_order', 'ASC')
            ->get(db_prefix() . 'pos_item_suppliers')
            ->result_array();
    }

    public function save_item_suppliers(int $product_id, array $rows): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_suppliers')) {
            return;
        }
        $this->db->where('product_id', $product_id)->delete(db_prefix() . 'pos_item_suppliers');
        foreach ($rows as $i => $row) {
            $sname = trim($row['supplier_name'] ?? '');
            if ($sname === '' && empty($row['supplier_id'])) {
                continue;
            }
            $this->db->insert(db_prefix() . 'pos_item_suppliers', [
                'product_id'         => $product_id,
                'supplier_id'        => !empty($row['supplier_id']) ? (int) $row['supplier_id'] : null,
                'supplier_name'      => $sname ?: null,
                'supplier_part_no'   => trim($row['supplier_part_no'] ?? '') ?: null,
                'lead_time_days'     => (int) ($row['lead_time_days'] ?? 0),
                'min_qty'            => (float) ($row['min_qty'] ?? 0),
                'last_purchase_rate' => (float) ($row['last_purchase_rate'] ?? 0),
                'sort_order'         => $i,
            ]);
        }
    }

    // ─── Barcode primary flag ──────────────────────────────────────────────────

    public function set_primary_barcode(int $product_id, int $barcode_id): bool
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_barcodes')) return false;
        $this->db->where('product_id', $product_id)
                 ->update(db_prefix() . 'pos_item_barcodes', ['is_primary' => 0]);
        $this->db->where('id', $barcode_id)->where('product_id', $product_id)
                 ->update(db_prefix() . 'pos_item_barcodes', ['is_primary' => 1]);
        return true;
    }

    // ─── Item code uniqueness check ────────────────────────────────────────────

    public function check_code_unique(string $code, int $exclude_id = 0): bool
    {
        $q = $this->db->where('item_code', $code);
        if ($exclude_id) { $q->where('id !=', $exclude_id); }
        return $q->count_all_results(db_prefix() . 'pos_products') === 0;
    }

    // ─── Duplicate item ────────────────────────────────────────────────────────

    public function duplicate(int $id): int
    {
        $full = $this->get_full($id);
        if (!$full) return 0;

        // Strip PK and child arrays
        $children = ['uoms', 'barcodes', 'reorder_rules', 'item_suppliers', 'variations'];
        $row = array_diff_key($full, array_flip(array_merge(['id', 'date_created', 'date_updated', 'perfex_item_id'], $children)));

        // Ensure unique item_code
        $base = ($row['item_code'] ?? '') . '-COPY';
        $row['item_code'] = $base;
        $row['name']      = $row['name'] . ' (Copy)';
        $i = 0;
        while (!$this->check_code_unique($row['item_code'])) {
            $row['item_code'] = $base . ($i > 0 ? '-' . $i : '');
            $i++;
        }

        $new_id = $this->create($row);
        if (!$new_id) return 0;

        // Duplicate UOMs and reorder rules (not barcodes — must be unique)
        $this->save_item_uoms($new_id, $full['uoms'] ?? []);
        $this->save_item_reorder_rules($new_id, $full['reorder_rules'] ?? []);
        $this->save_item_suppliers($new_id, $full['item_suppliers'] ?? []);

        return $new_id;
    }

    // ─── Serial / Batch global settings ───────────────────────────────────────

    public function get_serial_batch_settings(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_serial_batch_settings')) return [];
        $rows = $this->db->get(db_prefix() . 'pos_serial_batch_settings')->result_array();
        $out  = [];
        foreach ($rows as $r) { $out[$r['setting_key']] = $r['setting_value']; }
        return $out;
    }

    public function save_serial_batch_settings(array $data): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_serial_batch_settings')) return;
        foreach ($data as $key => $value) {
            $this->db->query(
                "INSERT INTO `" . db_prefix() . "pos_serial_batch_settings`
                 (`setting_key`, `setting_value`)
                 VALUES ('" . $this->db->escape_str($key) . "', '" . $this->db->escape_str($value) . "')
                 ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)"
            );
        }
    }

    // ─── Valuation rate calculator ─────────────────────────────────────────────

    public function update_moving_average_rate(int $product_id, float $current_qty, float $current_rate, float $incoming_qty, float $incoming_rate): float
    {
        $total_qty = $current_qty + $incoming_qty;
        if ($total_qty <= 0) return $current_rate;
        $new_rate = (($current_qty * $current_rate) + ($incoming_qty * $incoming_rate)) / $total_qty;
        $this->db->where('id', $product_id)->update(db_prefix() . 'pos_products', ['valuation_rate' => round($new_rate, 4)]);
        return $new_rate;
    }

    public function push_fifo_layer(int $product_id, int $branch_id, float $qty, float $rate): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_stock_layers')) return;
        $this->db->insert(db_prefix() . 'pos_item_stock_layers', [
            'item_id'       => $product_id,
            'branch_id'     => $branch_id,
            'qty'           => $qty,
            'rate'          => $rate,
            'incoming_date' => date('Y-m-d H:i:s'),
        ]);
        $this->_refresh_fifo_rate($product_id, $branch_id);
    }

    private function _refresh_fifo_rate(int $product_id, int $branch_id): void
    {
        $layers = $this->db
            ->where('item_id', $product_id)->where('branch_id', $branch_id)->where('qty >', 0)
            ->get(db_prefix() . 'pos_item_stock_layers')->result_array();
        if (!$layers) return;
        $total_qty = array_sum(array_column($layers, 'qty'));
        $total_val = array_sum(array_map(fn($l) => $l['qty'] * $l['rate'], $layers));
        $avg_rate  = $total_qty > 0 ? $total_val / $total_qty : 0;
        $this->db->where('id', $product_id)->update(db_prefix() . 'pos_products', ['valuation_rate' => round($avg_rate, 4)]);
    }

    // ─── Auto reorder check ────────────────────────────────────────────────────

    public function check_reorder_rules(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_reorder_rules')) return [];
        $rules = $this->db
            ->select('r.*, p.name AS item_name, p.allow_purchase, p.valuation_method')
            ->from(db_prefix() . 'pos_item_reorder_rules r')
            ->join(db_prefix() . 'pos_products p', 'p.id = r.product_id')
            ->where('p.track_inventory', 1)
            ->get()->result_array();

        $triggered = [];
        foreach ($rules as $rule) {
            $branch_id = $rule['branch_id'] ?: 0;
            $stock_q   = $this->db->select('COALESCE(SUM(quantity),0) AS qty')
                                  ->from(db_prefix() . 'pos_inventory')
                                  ->where('product_id', $rule['product_id']);
            if ($branch_id) { $stock_q->where('branch_id', $branch_id); }
            $stock = (float)($stock_q->get()->row_array()['qty'] ?? 0);

            if ($stock <= (float)$rule['reorder_level']) {
                $this->db->where('id', $rule['id'])
                         ->update(db_prefix() . 'pos_item_reorder_rules', ['last_triggered_at' => date('Y-m-d H:i:s')]);
                $triggered[] = $rule;
            }
        }
        return $triggered;
    }
}
