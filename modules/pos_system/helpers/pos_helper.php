<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Return the active branch for the currently logged-in staff member.
 * Prefers the row marked is_default=1; falls back to the first assignment
 * when no default has been set (common after a fresh assign without the checkbox).
 */
function pos_get_staff_branch($staff_id = null)
{
    $CI       = &get_instance();
    $staff_id = $staff_id ?? get_staff_user_id();

    // Guard: table may not exist if install is still pending
    if (!$CI->db->table_exists(db_prefix() . 'pos_staff_branches')) {
        return null;
    }

    // Try the explicitly-marked default first
    $row = $CI->db
        ->where('staff_id', $staff_id)
        ->where('is_default', 1)
        ->get(db_prefix() . 'pos_staff_branches')
        ->row();

    if ($row) {
        return (int) $row->branch_id;
    }

    // Fallback: any assignment (covers staff assigned without checking "Set as Default")
    $row = $CI->db
        ->where('staff_id', $staff_id)
        ->order_by('id', 'ASC')
        ->limit(1)
        ->get(db_prefix() . 'pos_staff_branches')
        ->row();

    return $row ? (int) $row->branch_id : null;
}

/**
 * Generate a unique receipt number for a branch.
 * Format: {BRANCH_CODE}-{YYYYMMDD}-{SEQUENCE}
 */
function pos_generate_receipt_number($branch_id)
{
    $CI = &get_instance();

    $branch = $CI->db->where('id', $branch_id)
                     ->get(db_prefix() . 'pos_branches')
                     ->row();

    $code   = $branch ? strtoupper($branch->code) : 'POS';
    $date   = date('Ymd');
    $prefix = $code . '-' . $date . '-';

    $CI->db->like('receipt_number', $prefix, 'after')
           ->where('branch_id', $branch_id)
           ->order_by('id', 'DESC')
           ->limit(1);

    $last = $CI->db->get(db_prefix() . 'pos_sales')->row();

    if ($last) {
        $parts    = explode('-', $last->receipt_number);
        $sequence = (int) end($parts) + 1;
    } else {
        $sequence = 1;
    }

    return $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
}

/**
 * Role weights — higher = more privilege.
 */
function pos_role_weight(string $role): int
{
    return ['cashier' => 1, 'supervisor' => 2, 'manager' => 3, 'admin' => 4][$role] ?? 0;
}

/**
 * Can the currently logged-in staff member access POS at this minimum role level?
 * Checks branch assignment role only. Perfex admins always pass.
 *
 * For Perfex-permission-based access use pos_perm() alongside this function —
 * do NOT conflate the two systems here; that causes permission bleed.
 */
function pos_can_access(string $min_role = 'cashier'): bool
{
    if (is_admin()) {
        return true;
    }

    $CI       = &get_instance();
    $staff_id = get_staff_user_id();
    if (!$staff_id) {
        return false;
    }

    if (!$CI->db->table_exists(db_prefix() . 'pos_staff_branches')) {
        return false;
    }

    $min_w = pos_role_weight($min_role);
    $rows  = $CI->db->select('role')
                    ->where('staff_id', $staff_id)
                    ->get(db_prefix() . 'pos_staff_branches')
                    ->result_array();

    foreach ($rows as $r) {
        if (pos_role_weight($r['role']) >= $min_w) {
            return true;
        }
    }

    return false;
}

/**
 * Check a specific POS Perfex permission (Setup → Staff → Permissions).
 * Perfex admins always pass. Use alongside pos_can_access() with OR logic.
 *
 * Usage:  pos_can_access('supervisor') || pos_perm('pos_reports', 'view')
 */
function pos_perm(string $feature, string $cap = 'view'): bool
{
    if (is_admin()) {
        return true;
    }
    return (bool) has_permission($feature, '', $cap);
}

/**
 * Return the highest POS role the current staff member holds across all branch assignments.
 */
function pos_get_staff_role(?int $staff_id = null): ?string
{
    $CI       = &get_instance();
    $staff_id = $staff_id ?? get_staff_user_id();

    if (!$CI->db->table_exists(db_prefix() . 'pos_staff_branches')) {
        return null;
    }

    $rows   = $CI->db->select('role')->where('staff_id', $staff_id)
                     ->get(db_prefix() . 'pos_staff_branches')->result_array();
    $best   = null;
    $best_w = 0;
    foreach ($rows as $r) {
        $w = pos_role_weight($r['role']);
        if ($w > $best_w) { $best_w = $w; $best = $r['role']; }
    }

    return $best;
}

/**
 * Branch filter for data queries.
 * Returns null  → admin, no WHERE clause, all branches visible.
 * Returns int>0 → staff assigned to that branch, scoped to it.
 * Returns 0     → staff with no branch assignment, WHERE b.id=0 → zero rows.
 *
 * Callers MUST use ($branch_filter !== null) not ($branch_filter) as the condition,
 * so that 0 ("no branch") is treated the same as a real branch id.
 */
function pos_get_auth_branch(): ?int
{
    if (is_admin()) {
        return null;
    }
    $branch = pos_get_staff_branch();
    return $branch ?? 0;
}

/**
 * Validate that a staff member has access to a branch.
 */
function pos_staff_can_access_branch($staff_id, $branch_id)
{
    $CI  = &get_instance();
    $row = $CI->db
        ->where('staff_id', $staff_id)
        ->where('branch_id', $branch_id)
        ->get(db_prefix() . 'pos_staff_branches')
        ->row();

    return $row !== null;
}

/**
 * Get branch-level setting, falling back to global.
 */
function pos_get_setting($key, $branch_id = null)
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(db_prefix() . 'pos_settings')) {
        return null;
    }

    if ($branch_id) {
        $row = $CI->db
            ->where('branch_id', $branch_id)
            ->where('setting_key', $key)
            ->get(db_prefix() . 'pos_settings')
            ->row();
        if ($row) {
            return $row->setting_value;
        }
    }

    $row = $CI->db
        ->where('branch_id IS NULL')
        ->where('setting_key', $key)
        ->get(db_prefix() . 'pos_settings')
        ->row();

    return $row ? $row->setting_value : null;
}

/**
 * Set a branch-level (or global) setting.
 */
function pos_set_setting($key, $value, $branch_id = null)
{
    $CI   = &get_instance();
    $data = ['setting_key' => $key, 'setting_value' => $value, 'branch_id' => $branch_id];

    $CI->db->where('setting_key', $key);
    if ($branch_id) {
        $CI->db->where('branch_id', $branch_id);
    } else {
        $CI->db->where('branch_id IS NULL');
    }

    if ($CI->db->get(db_prefix() . 'pos_settings')->num_rows() > 0) {
        $CI->db->where('setting_key', $key);
        if ($branch_id) {
            $CI->db->where('branch_id', $branch_id);
        } else {
            $CI->db->where('branch_id IS NULL');
        }
        return $CI->db->update(db_prefix() . 'pos_settings', ['setting_value' => $value]);
    }

    return $CI->db->insert(db_prefix() . 'pos_settings', $data);
}

/**
 * Format currency for display using East African conventions.
 */
function pos_format_currency($amount, $currency = 'KES')
{
    $symbols = [
        'KES' => 'KSh',
        'UGX' => 'USh',
        'TZS' => 'TSh',
        'RWF' => 'RWF',
        'ETB' => 'ETB',
        'USD' => '$',
    ];

    $symbol = $symbols[$currency] ?? $currency;
    // East African currencies typically show 0 or 2 decimal places
    $decimals = in_array($currency, ['UGX', 'RWF']) ? 0 : 2;

    return $symbol . ' ' . number_format((float) $amount, $decimals);
}

/**
 * Sync a Perfex CRM item into pos_products.
 */
function pos_sync_perfex_item($item_id, $action = 'update')
{
    $CI   = &get_instance();
    $item = $CI->db->where('id', $item_id)
                   ->get(db_prefix() . 'items')
                   ->row();

    if (!$item) {
        return false;
    }

    $data = [
        'perfex_item_id' => $item_id,
        'name'           => $item->description,
        'selling_price'  => $item->rate,
        'unit'           => $item->unit ?? 'pcs',
    ];

    $exists = $CI->db->where('perfex_item_id', $item_id)
                     ->get(db_prefix() . 'pos_products')
                     ->row();

    if ($exists) {
        $CI->db->where('perfex_item_id', $item_id)
               ->update(db_prefix() . 'pos_products', $data);
    } elseif ($action === 'add') {
        $data['sku']  = 'PERFEX-' . $item_id;
        $data['type'] = 'simple';
        $CI->db->insert(db_prefix() . 'pos_products', $data);
    }

    return true;
}

/**
 * Respond with JSON and exit. Used by API controllers.
 */
function pos_json_response($data, $status_code = 200)
{
    $CI = &get_instance();
    $CI->output
       ->set_status_header($status_code)
       ->set_content_type('application/json')
       ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $CI->output->_display();
    exit;
}

/**
 * Decode JSON from raw php://input, returning array or false.
 */
function pos_get_json_input()
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : false;
}

/**
 * Generate a version-4 UUID.
 */
function pos_uuid()
{
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
