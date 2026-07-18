<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('courier_load_model')) {
    /**
     * Loads a courier_goshipping model by its exact filename, bypassing MX's
     * model loader ($this->load->model('courier_goshipping/Xxx_model')) —
     * MX lowercases the whole path then only ucfirst()'s the first letter
     * before checking is_file(), so any model filename with more than one
     * internal capital (CourierBranch_model, CountryState_model, etc.) silently
     * fails to resolve on case-sensitive (Linux) filesystems, even though it
     * works by accident on case-insensitive Windows dev boxes.
     */
    function courier_load_model($model_name, $alias = null)
    {
        $CI = &get_instance();
        $alias = $alias ?: $model_name;

        if (!isset($CI->$alias)) {
            if (!class_exists($model_name, false)) {
                require_once(module_dir_path('courier_goshipping', 'models/' . $model_name . '.php'));
            }
            $CI->$alias = new $model_name();
        }
    }
}

if (!function_exists('load_courier_styles')) {
    function load_courier_styles()
    {
        $css_path = FCPATH . 'modules/courier_goshipping/assets/main.css';
        $v = file_exists($css_path) ? filemtime($css_path) : time();
        echo '<link rel="stylesheet" href="' . base_url('modules/courier_goshipping/assets/main.css') . '?v=' . $v . '">';
    }
}

if (!function_exists('load_courier_scripts')) {
    function load_courier_scripts()
    {
        $js_path = FCPATH . 'modules/courier_goshipping/assets/create_shipment.js';
        $v = file_exists($js_path) ? filemtime($js_path) : time();
        echo '<script src="' . base_url('modules/courier_goshipping/assets/create_shipment.js') . '?v=' . $v . '"></script>';
    }
}

/**
 * Whether the current (or given) staff member can see every branch's data,
 * bypassing branch isolation. Admins always bypass.
 */
if (!function_exists('courier_staff_can_view_all_branches')) {
    function courier_staff_can_view_all_branches($staff_id = '')
    {
        return is_admin($staff_id) || staff_can('view_all_branches', 'courier-branches', $staff_id);
    }
}

/**
 * Branch IDs assigned to a staff member via tbl_courier_staff_branches.
 * Returns an empty array if the staff has no branch assignments.
 */
if (!function_exists('courier_get_staff_branch_ids')) {
    function courier_get_staff_branch_ids($staff_id = null)
    {
        $CI = &get_instance();
        $staff_id = $staff_id ?: get_staff_user_id();

        $rows = $CI->db->select('branch_id')
            ->where('staff_id', (int) $staff_id)
            ->get(db_prefix() . '_courier_staff_branches')
            ->result_array();

        return array_map('intval', array_column($rows, 'branch_id'));
    }
}

/**
 * The staff member's default branch (used for stamping new records and as a
 * single-branch fallback), or null if none assigned.
 */
if (!function_exists('courier_get_default_staff_branch_id')) {
    function courier_get_default_staff_branch_id($staff_id = null)
    {
        $CI = &get_instance();
        $staff_id = $staff_id ?: get_staff_user_id();

        $row = $CI->db->where('staff_id', (int) $staff_id)
            ->where('is_default', 1)
            ->get(db_prefix() . '_courier_staff_branches')
            ->row();

        if (!$row) {
            $row = $CI->db->where('staff_id', (int) $staff_id)
                ->order_by('id', 'asc')
                ->limit(1)
                ->get(db_prefix() . '_courier_staff_branches')
                ->row();
        }

        return $row ? (int) $row->branch_id : null;
    }
}

/**
 * The branch a staff member is currently "operating as" this session — the
 * one they picked at login if they have multiple, otherwise their sole/default
 * branch. Used to stamp branch_id on records created during this session.
 */
if (!function_exists('courier_get_session_branch_id')) {
    function courier_get_session_branch_id()
    {
        $CI = &get_instance();
        $session_branch = (int) $CI->session->userdata('courier_active_branch_id');
        if ($session_branch > 0) {
            return $session_branch;
        }

        return courier_get_default_staff_branch_id();
    }
}

/**
 * The org-wide fallback branch (flagged is_default=1 in tbl_courier_branches)
 * used to route orders/shipments when no branch could otherwise be resolved.
 */
if (!function_exists('courier_get_fallback_branch_id')) {
    function courier_get_fallback_branch_id()
    {
        $CI = &get_instance();
        $row = $CI->db->where('is_default', 1)
            ->where('is_active', 1)
            ->limit(1)
            ->get(db_prefix() . '_courier_branches')
            ->row();

        return $row ? (int) $row->id : null;
    }
}

/**
 * Applies branch isolation to the current CI query builder: restricts to the
 * staff's assigned branches unless they're an admin or hold 'view_all_branches'.
 * Call this right before ->get()/->count_all_results() on a table/alias that
 * has a branch_id column.
 */
if (!function_exists('courier_apply_branch_scope')) {
    function courier_apply_branch_scope($column = 'branch_id')
    {
        if (courier_staff_can_view_all_branches()) {
            return;
        }

        $CI = &get_instance();
        $ids = courier_get_staff_branch_ids();
        // No branches assigned at all — must not see any branch-scoped rows.
        $CI->db->where_in($column, !empty($ids) ? $ids : [0]);
    }
}

/**
 * Returns the company info to stamp on courier invoices, receipts, quotations,
 * waybills, and manifests.
 *
 * Courier-specific settings (courier_inv_*) take priority; Perfex global
 * settings are used as fallback so nothing is ever blank out of the box.
 */
if (!function_exists('courier_get_invoice_info')) {
    function courier_get_invoice_info($branch_id = null)
    {
        // A branch's own name/address/phone/email (set on Branches / Offices)
        // takes priority over the global letterhead settings, so a Dubai
        // shipment's documents show Dubai's office details instead of
        // whichever branch happens to be configured globally.
        $branch = null;
        if (!empty($branch_id)) {
            $CI = &get_instance();
            if ($CI->db->table_exists(db_prefix() . '_courier_branches')) {
                $branch = $CI->db->where('id', (int) $branch_id)->get(db_prefix() . '_courier_branches')->row();
            }
        }

        // Resolve company name: branch override → courier override → courier_logistic_company → Perfex company name
        $_lc_raw = get_option('courier_logistic_company');
        $_lc     = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : get_option('companyname');

        $name    = ($branch->name ?? '')    ?: (get_option('courier_inv_company_name') ?: ($_lc ?: ''));
        $email   = ($branch->email ?? '')   ?: (get_option('courier_inv_email')        ?: (get_option('email') ?: get_option('company_email') ?: ''));
        $phone   = ($branch->phone ?? '')   ?: (get_option('courier_inv_phone')        ?: (get_option('invoice_company_phonenumber') ?: get_option('phonenumber') ?: ''));
        $address = ($branch->address ?? '') ?: (get_option('courier_inv_address')      ?: (get_option('company_address') ?: ''));
        $website = get_option('courier_inv_website')      ?: '';
        $pin     = get_option('courier_inv_pin')          ?: '';
        $tagline = get_option('courier_inv_tagline')      ?: '';

        return compact('name', 'email', 'phone', 'address', 'website', 'pin', 'tagline');
    }
}






