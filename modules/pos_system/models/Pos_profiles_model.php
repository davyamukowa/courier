<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * POS Profiles Model — config hierarchy: global → branch → user override.
 *
 * A "profile" is a named set of POS behaviour flags. Resolution order:
 *   1. User-assigned profile (from pos_profile_users)
 *   2. Branch-assigned profile (branch_id matches)
 *   3. Global default profile (branch_id IS NULL)
 */
class Pos_profiles_model extends App_Model
{
    private string $table;
    private string $users_table;

    public function __construct()
    {
        parent::__construct();
        $this->table       = db_prefix() . 'pos_profiles';
        $this->users_table = db_prefix() . 'pos_profile_users';
    }

    // ─── Config resolution ────────────────────────────────────────────────────

    /**
     * Resolve the effective profile for a staff member at a branch.
     * Hierarchy: user-specific → branch → global default.
     */
    public function resolve(int $staff_id, int $branch_id): array
    {
        if (!$this->db->table_exists($this->table)) {
            return $this->defaults();
        }

        // 1. User-specific profile for this branch
        $profile = $this->db
            ->select('p.*')
            ->from($this->table . ' p')
            ->join($this->users_table . ' pu', 'pu.profile_id = p.id')
            ->where('pu.staff_id', $staff_id)
            ->where('p.branch_id', $branch_id)
            ->where('p.is_active', 1)
            ->limit(1)
            ->get()->row_array();

        if ($profile) {
            return $this->enrich($profile);
        }

        // 2. Branch-level profile
        $profile = $this->db
            ->where('branch_id', $branch_id)
            ->where('is_active', 1)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get($this->table)->row_array();

        if ($profile) {
            return $this->enrich($profile);
        }

        // 3. Global default
        $profile = $this->db
            ->where('branch_id IS NULL')
            ->where('is_active', 1)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get($this->table)->row_array();

        return $profile ? $this->enrich($profile) : $this->defaults();
    }

    // ─── CRUD ────────────────────────────────────────────────────────────────

    public function get_all(array $filters = []): array
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        if (isset($filters['branch_id'])) {
            $this->db->group_start()
                     ->where('branch_id', $filters['branch_id'])
                     ->or_where('branch_id IS NULL')
                     ->group_end();
        }

        if (isset($filters['is_active'])) {
            $this->db->where('is_active', $filters['is_active']);
        }

        return $this->db->order_by('branch_id', 'ASC')
                        ->order_by('name', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }

    public function get(int $id): ?array
    {
        $row = $this->db->where('id', $id)->get($this->table)->row_array();
        if (!$row) {
            return null;
        }
        $row['assigned_users']          = $this->get_users($id);
        $row['item_group_ids']          = $this->get_item_group_ids($id);
        $row['customer_group_ids']      = $this->get_customer_group_ids($id);
        return $row;
    }

    public function create(array $data): int
    {
        $item_groups     = $data['item_group_ids']     ?? [];
        $customer_groups = $data['customer_group_ids'] ?? [];
        unset($data['item_group_ids'], $data['customer_group_ids'], $data['assigned_users']);

        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        $this->_sync_item_groups($id, $item_groups);
        $this->_sync_customer_groups($id, $customer_groups);

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $item_groups     = $data['item_group_ids']     ?? null;
        $customer_groups = $data['customer_group_ids'] ?? null;
        unset($data['item_group_ids'], $data['customer_group_ids'], $data['assigned_users']);

        $result = (bool) $this->db->where('id', $id)->update($this->table, $data);

        if ($item_groups !== null) {
            $this->_sync_item_groups($id, $item_groups);
        }
        if ($customer_groups !== null) {
            $this->_sync_customer_groups($id, $customer_groups);
        }

        return $result;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->where('id', $id)->set('is_active', 0)->update($this->table);
    }

    // ─── User assignments ─────────────────────────────────────────────────────

    public function assign_user(int $profile_id, int $staff_id): bool
    {
        $exists = $this->db
            ->where('profile_id', $profile_id)
            ->where('staff_id', $staff_id)
            ->get($this->users_table)->row();

        if ($exists) {
            return true;
        }

        return (bool) $this->db->insert($this->users_table, [
            'profile_id' => $profile_id,
            'staff_id'   => $staff_id,
        ]);
    }

    public function remove_user(int $profile_id, int $staff_id): bool
    {
        return (bool) $this->db
            ->where('profile_id', $profile_id)
            ->where('staff_id', $staff_id)
            ->delete($this->users_table);
    }

    public function get_users(int $profile_id): array
    {
        return $this->db
            ->select('s.staffid as id, s.firstname, s.lastname, s.email, pu.is_default')
            ->from(db_prefix() . 'staff s')
            ->join($this->users_table . ' pu', 'pu.staff_id = s.staffid')
            ->where('pu.profile_id', $profile_id)
            ->order_by('pu.is_default', 'DESC')
            ->order_by('s.firstname', 'ASC')
            ->get()->result_array();
    }

    public function set_default_user(int $profile_id, int $staff_id): bool
    {
        $this->db->where('profile_id', $profile_id)->set('is_default', 0)->update($this->users_table);
        return (bool) $this->db
            ->where('profile_id', $profile_id)
            ->where('staff_id', $staff_id)
            ->set('is_default', 1)
            ->update($this->users_table);
    }

    public function get_payment_method_ids(int $profile_id): array
    {
        $tbl = db_prefix() . 'pos_profile_payment_methods';
        if (!$this->db->table_exists($tbl)) {
            return [];
        }
        return array_column(
            $this->db->select('payment_method_id')->where('profile_id', $profile_id)->get($tbl)->result_array(),
            'payment_method_id'
        );
    }

    public function sync_payment_methods(int $profile_id, array $method_ids): void
    {
        $tbl = db_prefix() . 'pos_profile_payment_methods';
        if (!$this->db->table_exists($tbl)) {
            return;
        }
        $this->db->where('profile_id', $profile_id)->delete($tbl);
        foreach (array_unique(array_filter(array_map('intval', $method_ids))) as $mid) {
            $this->db->insert($tbl, ['profile_id' => $profile_id, 'payment_method_id' => $mid]);
        }
    }

    // ─── Price lists + groups ─────────────────────────────────────────────────

    public function get_price_lists(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_price_lists')) {
            return [];
        }
        return $this->db->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_price_lists')->result_array();
    }

    public function get_item_groups(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_item_groups')) {
            return [];
        }
        return $this->db->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_item_groups')->result_array();
    }

    public function get_customer_groups(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_customer_groups')) {
            return [];
        }
        return $this->db->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_customer_groups')->result_array();
    }

    public function get_print_templates(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_print_templates')) {
            return [];
        }
        return $this->db->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_print_templates')->result_array();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function enrich(array $profile): array
    {
        // Cast booleans
        $bool_fields = [
            'hide_images', 'hide_unavailable_items', 'auto_add_item_to_cart',
            'validate_stock_on_save', 'print_receipt_on_order_complete',
            'ignore_pricing_rule', 'allow_rate_change', 'allow_discount_change',
            'set_grand_total_to_default_mop', 'allow_partial_payment',
            'enable_cash_rounding', 'disable_rounded_total', 'auto_create_invoice',
        ];
        foreach ($bool_fields as $f) {
            if (isset($profile[$f])) {
                $profile[$f] = (bool) $profile[$f];
            }
        }
        return $profile;
    }

    /** Hard-coded safe defaults when no profile or tables exist yet. */
    private function defaults(): array
    {
        return [
            'id'                              => 0,
            'name'                            => 'Default',
            'default_currency'                => 'KES',
            'hide_images'                     => false,
            'hide_unavailable_items'          => false,
            'auto_add_item_to_cart'           => false,
            'validate_stock_on_save'          => true,
            'print_receipt_on_order_complete' => true,
            'action_on_new_invoice'           => 'ask',
            'ignore_pricing_rule'             => false,
            'allow_rate_change'               => true,
            'allow_discount_change'           => true,
            'set_grand_total_to_default_mop'  => false,
            'allow_partial_payment'           => true,
            'apply_discount_on'               => 'net_total',
            'enable_cash_rounding'            => false,
            'cash_rounding_increment'         => 0.05,
            'cash_rounding_type'              => 'nearest',
            'disable_rounded_total'           => false,
            'auto_create_invoice'             => false,
            'invoice_prefix'                  => 'POS-',
        ];
    }

    private function get_item_group_ids(int $profile_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_item_groups')) {
            return [];
        }
        return array_column(
            $this->db->select('group_id')->where('profile_id', $profile_id)
                     ->get(db_prefix() . 'pos_profile_item_groups')->result_array(),
            'group_id'
        );
    }

    private function get_customer_group_ids(int $profile_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_customer_groups')) {
            return [];
        }
        return array_column(
            $this->db->select('group_id')->where('profile_id', $profile_id)
                     ->get(db_prefix() . 'pos_profile_customer_groups')->result_array(),
            'group_id'
        );
    }

    private function _sync_item_groups(int $profile_id, array $group_ids): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_item_groups')) {
            return;
        }
        $this->db->where('profile_id', $profile_id)->delete(db_prefix() . 'pos_profile_item_groups');
        foreach (array_unique(array_filter(array_map('intval', $group_ids))) as $gid) {
            $this->db->insert(db_prefix() . 'pos_profile_item_groups', ['profile_id' => $profile_id, 'group_id' => $gid]);
        }
    }

    private function _sync_customer_groups(int $profile_id, array $group_ids): void
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_customer_groups')) {
            return;
        }
        $this->db->where('profile_id', $profile_id)->delete(db_prefix() . 'pos_profile_customer_groups');
        foreach (array_unique(array_filter(array_map('intval', $group_ids))) as $gid) {
            $this->db->insert(db_prefix() . 'pos_profile_customer_groups', ['profile_id' => $profile_id, 'group_id' => $gid]);
        }
    }
}
