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
 * Maps a "GSC-AE-DXB" style route tag to a Go Shipping branch, via the
 * staff-configured tbl_courier_route_branch_map (Fulfilment settings ->
 * Route Map). Shared by both the Shopify webhook auto-create path and the
 * manual "Create Shipment" button so branch resolution can't drift between
 * the two — a route tag, when present, always wins over the SKU-based guess.
 *
 * Route tags are "GSC-{country}-{port}" (e.g. GSC-US-ORD, GSC-US-EWR) — the
 * sourcing app can introduce a brand-new port code for an already-mapped
 * country at any time (a US order via Newark instead of Chicago, say), and
 * staff shouldn't have to notice and add a settings row before it routes
 * correctly. So when there's no exact match, fall back to any other mapped
 * tag sharing the same "GSC-{country}-" prefix and reuse its branch.
 */
if (!function_exists('courier_resolve_branch_from_route_tag')) {
    function courier_resolve_branch_from_route_tag($route_tag)
    {
        if (empty($route_tag)) {
            return null;
        }

        $CI = &get_instance();
        $table = db_prefix() . 'courier_route_branch_map';
        if (!$CI->db->table_exists($table)) {
            return null;
        }

        $map = $CI->db->where('route_tag', $route_tag)->get($table)->row();
        if ($map) {
            return (int) $map->branch_id;
        }

        if (preg_match('/^(GSC-[A-Za-z]{2}-)/', $route_tag, $m)) {
            $sibling = $CI->db->like('route_tag', $m[1], 'after')->get($table)->row();
            if ($sibling) {
                return (int) $sibling->branch_id;
            }
        }

        return null;
    }
}

/**
 * Builds the full sourcing-to-doorstep journey for a shipment, for the
 * client portal tracker: the external sourcing pipeline's own progress tags
 * (tbl_courier_sourcing_events, captured by
 * Shopify_connector::record_sourcing_milestone_tags()) merged with Go
 * Shipping's own shipment status history, across BOTH legs of a two-leg international
 * order (the international air-freight leg and its linked domestic last-mile
 * leg — see run_db_upgrades_v36()/parent_shipment_id) if one exists. Works
 * the same whether $shipment_id is the leg the customer happens to be
 * tracking or its sibling — the family is always resolved first.
 *
 * Returns a flat array of ['label' => string, 'changed_at' => 'Y-m-d H:i:s']
 * sorted newest-first, ready to hand straight to the tracker view.
 */
/**
 * Customer-friendly wording for the client portal's journey timeline — the
 * international air-freight stages (10-13) in particular read like internal
 * ops jargon ("Arrived Destination Airport") rather than something a
 * customer waiting on a parcel wants to read, so they get a warmer
 * rewrite here. Everything else (normal domestic statuses, sourcing tags)
 * falls back to its existing description/status_name unchanged, since
 * those are already reasonably customer-appropriate.
 */
if (!function_exists('courier_customer_facing_status_label')) {
    function courier_customer_facing_status_label($status_id, $description, $status_name)
    {
        $friendly = [
            10 => 'Your order has arrived at the airport and is being prepared for its flight.',
            11 => 'Your order has been shipped and is currently in the air, on its way to Kenya.',
            12 => 'Your order has landed in Kenya and is clearing customs.',
            13 => "Your order has arrived at our warehouse. We'll keep you updated as it heads to you.",
        ];

        return $friendly[(int) $status_id] ?? ($description ?: ucfirst(str_replace('_', ' ', (string) $status_name)));
    }
}

if (!function_exists('courier_get_shipment_journey')) {
    function courier_get_shipment_journey($shipment_id)
    {
        $CI = &get_instance();
        $shipments_table = db_prefix() . '_shipments';

        $shipment = $CI->db->where('id', (int) $shipment_id)->get($shipments_table)->row();
        if (!$shipment) {
            return [];
        }

        $leg_ids = [(int) $shipment_id];
        if (!empty($shipment->parent_shipment_id)) {
            $leg_ids[] = (int) $shipment->parent_shipment_id;
        }
        $child = $CI->db->where('parent_shipment_id', (int) $shipment_id)->get($shipments_table)->row();
        if ($child) {
            $leg_ids[] = (int) $child->id;
        }
        $leg_ids = array_values(array_unique($leg_ids));

        $events = [];

        $status_desc_col = $CI->db->field_exists('status_description', db_prefix() . '_shipment_statuses')
            ? 'ss.status_description'
            : 'ss.description';

        $history = $CI->db->select("h.status_id, h.changed_at, ss.status_name, {$status_desc_col} AS status_description")
            ->from(db_prefix() . '_shipment_status_history h')
            ->join(db_prefix() . '_shipment_statuses ss', 'ss.id = h.status_id', 'left')
            ->where_in('h.shipment_id', $leg_ids)
            ->get()
            ->result();

        foreach ($history as $row) {
            $events[] = [
                'label'      => courier_customer_facing_status_label((int) $row->status_id, $row->status_description, $row->status_name),
                'changed_at' => $row->changed_at,
            ];
        }

        if ($CI->db->table_exists(db_prefix() . 'shopify_orders')) {
            $order = $CI->db->where_in('gs_shipment_id', $leg_ids)->get(db_prefix() . 'shopify_orders')->row();
            if ($order && $CI->db->table_exists(db_prefix() . 'courier_sourcing_events')) {
                $sourcing_events = $CI->db->where('shopify_order_id', $order->id)
                    ->get(db_prefix() . 'courier_sourcing_events')
                    ->result();
                foreach ($sourcing_events as $row) {
                    $events[] = [
                        'label'      => $row->tag,
                        'changed_at' => $row->changed_at,
                    ];
                }
            }
        }

        usort($events, function ($a, $b) {
            return strcmp($b['changed_at'], $a['changed_at']);
        });

        return $events;
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






