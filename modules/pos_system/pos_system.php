<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: POS System
Module URI:  https://github.com/your-org/perfex-pos
Description: Production-grade Point of Sale for East African markets — multi-branch, mobile money, offline-ready
Version:     1.0.0
Requires at least: 2.9.0
Author:      Your Organisation
*/

define('POS_SYSTEM_MODULE_NAME', 'pos_system');
define('POS_SYSTEM_VERSION',     '1.0.0');
define('POS_SYSTEM_PATH',        module_dir_path(POS_SYSTEM_MODULE_NAME));
define('POS_SYSTEM_URL',         module_dir_url(POS_SYSTEM_MODULE_NAME));

// Load the module helper directly by path — CI's load->helper() only
// searches application/helpers/, not module subdirectories.
require_once POS_SYSTEM_PATH . 'helpers/pos_helper.php';

// ─── Auto-install: create tables if missing (handles failed first activation) ──
hooks()->add_action('admin_init', 'pos_system_maybe_install');

// ─── Admin menu ─────────────────────────────────────────────────────────────
hooks()->add_action('admin_init', 'pos_system_init_menu_items');
hooks()->add_action('admin_init', 'pos_system_load_language');
hooks()->add_action('admin_init', 'pos_system_register_permissions');

// ─── Frontend/SPA assets ────────────────────────────────────────────────────
hooks()->add_action('app_admin_head', 'pos_system_add_head_components');

// ─── Staff login/logout ─────────────────────────────────────────────────────
hooks()->add_action('after_staff_logout', 'pos_system_staff_logout_handler');

// ─── Perfex CRM item sync ───────────────────────────────────────────────────
hooks()->add_action('after_item_added',   'pos_system_sync_item_added');
hooks()->add_action('after_item_updated', 'pos_system_sync_item_updated');
hooks()->add_action('after_item_deleted', 'pos_system_sync_item_deleted');

// Warehouse module fires these when commodities change
hooks()->add_action('after_commodity_added',   'pos_system_sync_item_added');
hooks()->add_action('after_commodity_updated', 'pos_system_sync_item_updated');

// POS fires pos_sale_completed — accounting + warehouse listen here
hooks()->add_action('pos_sale_completed', 'pos_system_post_sale_to_accounting');
hooks()->add_action('pos_sale_completed', 'pos_system_deduct_warehouse_stock');

// ─── Dashboard widget ───────────────────────────────────────────────────────
hooks()->add_action('dashboard_stats', 'pos_system_dashboard_stats');

// ─────────────────────────────────────────────────────────────────────────────

function pos_system_maybe_install()
{
    $CI = &get_instance();
    // Re-run install when base tables are missing OR when newer feature tables
    // (e.g. pos_profiles) haven't been created yet. All CREATE TABLEs use
    // IF NOT EXISTS, so re-running is always safe.
    $needs_install = !$CI->db->table_exists(db_prefix() . 'pos_activity_logs')
                  || !$CI->db->table_exists(db_prefix() . 'pos_profiles')
                  || !$CI->db->table_exists(db_prefix() . 'pos_inv_receipts')
                  || !$CI->db->table_exists(db_prefix() . 'pos_inv_purchase_orders')
                  || !$CI->db->table_exists(db_prefix() . 'pos_inv_approval_settings')
                  || !$CI->db->table_exists(db_prefix() . 'pos_sales_orders')
                  || !$CI->db->table_exists(db_prefix() . 'pos_restaurant_kots')
                  || !$CI->db->table_exists(db_prefix() . 'pos_restaurant_recipes')
                  || !$CI->db->field_exists('accounting_date', db_prefix() . 'pos_inv_deliveries')
                  || !$CI->db->field_exists('discount_pct',    db_prefix() . 'pos_inv_delivery_items')
                  || !$CI->db->field_exists('sales_order_id',  db_prefix() . 'pos_inv_deliveries');
    if ($needs_install) {
        @include POS_SYSTEM_PATH . 'install.php';
    }
}

function pos_system_load_language()
{
    $CI = &get_instance();
    $CI->lang->load('pos_system', 'english', false, true, POS_SYSTEM_PATH);
}

function pos_system_init_menu_items()
{
    $CI = &get_instance();

    $is_super = is_admin();

    // Must have at least terminal access (branch assignment OR explicit permission)
    if (!$is_super && !pos_can_access('cashier') && !pos_perm('pos_terminal')) {
        return;
    }

    // Role weight from branch assignment only — no permission lifting
    $role_w = $is_super ? 4 : pos_role_weight(pos_get_staff_role() ?? 'cashier');

    // Per-item visibility: branch role OR the specific Perfex permission
    $see_reports      = $is_super || $role_w >= 2 || pos_perm('pos_reports');
    $see_invoices     = $is_super || $role_w >= 2 || pos_perm('pos_invoices');
    $see_products     = $is_super || $role_w >= 3 || pos_perm('pos_products');
    $see_inventory    = $is_super || $role_w >= 3 || pos_perm('pos_inventory');
    $see_profiles     = $is_super || $role_w >= 3 || pos_perm('pos_profiles');
    $see_pay_methods  = $is_super || $role_w >= 3 || pos_perm('pos_payment_methods');
    $see_dashboard    = $see_reports || $see_invoices;

    $landing = $see_dashboard ? admin_url('pos_system') : admin_url('pos_system/terminal');

    $CI->app_menu->add_sidebar_menu_item('pos-system', [
        'name' => _l('pos_system'), 'href' => $landing,
        'position' => 20, 'icon' => 'fa fa-cash-register',
    ]);

    if ($see_dashboard) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-dashboard', 'name' => _l('pos_dashboard'),
            'href' => admin_url('pos_system'), 'position' => 1,
        ]);
    }

    $CI->app_menu->add_sidebar_children_item('pos-system', [
        'slug' => 'pos-terminal', 'name' => _l('pos_terminal'),
        'href' => admin_url('pos_system/terminal'), 'position' => 2,
    ]);

    if ($is_super) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-branches', 'name' => _l('pos_branches'),
            'href' => admin_url('pos_system/branches'), 'position' => 3,
        ]);
    }

    // "Sales Orders" renamed to "Invoices" in sidebar
    if ($see_products) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-sales-orders', 'name' => 'Invoices',
            'href' => admin_url('pos_system/sales_orders'), 'position' => 4,
        ]);
    }

    if ($see_inventory) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-inventory', 'name' => _l('pos_inventory'),
            'href' => admin_url('pos_system/inventory'), 'position' => 5,
        ]);
    }

    if ($see_reports) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-reports', 'name' => _l('pos_reports'),
            'href' => admin_url('pos_system/reports'), 'position' => 6,
        ]);
    }

    // Restaurant Management section — visible when restaurant mode on
    $restaurant_on = pos_get_setting('pos_restaurant_mode') == '1';
    if ($restaurant_on && ($is_super || $role_w >= 2)) {
        // Parent: Restaurant Management
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-restaurant', 'name' => 'Restaurant',
            'href' => admin_url('pos_system/restaurant'), 'position' => 7,
        ]);
        $CI->app_menu->add_sidebar_children_item('pos-restaurant', [
            'slug' => 'pos-restaurant-overview', 'name' => 'Overview',
            'href' => admin_url('pos_system/restaurant'), 'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item('pos-restaurant', [
            'slug' => 'pos-restaurant-tables', 'name' => 'Tables',
            'href' => admin_url('pos_system/restaurant/tables'), 'position' => 2,
        ]);
        $CI->app_menu->add_sidebar_children_item('pos-restaurant', [
            'slug' => 'pos-restaurant-areas', 'name' => 'Production Areas',
            'href' => admin_url('pos_system/restaurant/areas'), 'position' => 3,
        ]);
        $CI->app_menu->add_sidebar_children_item('pos-restaurant', [
            'slug' => 'pos-restaurant-recipes', 'name' => 'Recipes',
            'href' => admin_url('pos_system/restaurant/recipes'), 'position' => 4,
        ]);
        $CI->app_menu->add_sidebar_children_item('pos-restaurant', [
            'slug' => 'pos-restaurant-kitchen', 'name' => 'Kitchen Display',
            'href' => admin_url('pos_system/restaurant/kitchen'), 'position' => 5,
        ]);
    }

    // Invoices, Profiles, Payment Methods removed from sidebar (moved to settings / Invoices page)

    if ($is_super) {
        $CI->app_menu->add_sidebar_children_item('pos-system', [
            'slug' => 'pos-settings', 'name' => 'POS Settings',
            'href' => admin_url('pos_system/settings'), 'position' => 10,
        ]);
    }
}

/**
 * Register POS capabilities so they appear on the Perfex staff permissions page.
 *
 * Role mapping (for reference — branch assignment is the primary access mechanism):
 *   pos_terminal  view  → Cashier  (can open POS terminal)
 *   pos_reports   view  → Supervisor (+ dashboard, reports, invoices)
 *   pos_products  view  → Manager  (+ products, inventory, profiles, payment methods)
 */
function pos_system_register_permissions()
{
    $view_only   = ['view'   => _l('permission_view')];
    $view_create = ['view'   => _l('permission_view'), 'create' => _l('permission_create')];
    $full        = ['view'   => _l('permission_view'), 'create' => _l('permission_create'),
                    'edit'   => _l('permission_edit'),  'delete' => _l('permission_delete')];
    $view_edit   = ['view'   => _l('permission_view'), 'edit'   => _l('permission_edit')];

    register_staff_capabilities('pos_terminal',  ['capabilities' => $view_only],  'POS Terminal');
    register_staff_capabilities('pos_reports',   ['capabilities' => $view_only],  'POS Reports');
    register_staff_capabilities('pos_invoices',  ['capabilities' => $full],       'POS Invoices');
    register_staff_capabilities('pos_products',  ['capabilities' => $full],       'POS Products');
    register_staff_capabilities('pos_inventory', ['capabilities' => $full],       'POS Inventory');
    register_staff_capabilities('pos_profiles',  ['capabilities' => $full],       'POS Profiles');
    register_staff_capabilities('pos_payment_methods', ['capabilities' => $full], 'POS Payment Methods');
    register_staff_capabilities('pos_settings',  ['capabilities' => $view_edit],  'POS Settings');
}

function pos_system_add_head_components()
{
    // Perfex admin routes: /admin/pos_system/... → segment(1)='admin', segment(2)='pos_system'
    $CI  = get_instance();
    $seg1 = $CI->uri->segment(1); // 'pos_system' on direct module routes
    $seg2 = $CI->uri->segment(2); // 'pos_system' under /admin/ prefix
    if ($seg1 === 'pos_system' || $seg2 === 'pos_system') {
        echo '<link rel="stylesheet" href="' . POS_SYSTEM_URL . 'assets/css/pos.css">';
    }
}

function pos_system_staff_logout_handler($staff_id)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $staff_id)
           ->where('is_active', 1)
           ->set('is_active', 0)
           ->update(db_prefix() . 'pos_api_tokens');
}

function pos_system_sync_item_added($item_id)
{
    pos_sync_perfex_item($item_id, 'add');
}

function pos_system_sync_item_updated($item_id)
{
    pos_sync_perfex_item($item_id, 'update');
}

function pos_system_sync_item_deleted($item_id)
{
    $CI = &get_instance();
    $CI->db->where('perfex_item_id', $item_id)
           ->set('is_active', 0)
           ->update(db_prefix() . 'pos_products');
}

/**
 * Hook: pos_sale_completed — post GL entries to accounting module.
 * $payload = ['sale' => [...], 'items' => [...], 'payments' => [...], 'staff_id' => int]
 */
function pos_system_post_sale_to_accounting($payload)
{
    if (!is_array($payload) || empty($payload['sale'])) {
        return;
    }

    if (pos_get_setting('pos_accounting_sync', $payload['sale']['branch_id'] ?? 0) === '0') {
        return;
    }

    $CI = &get_instance();
    $CI->load->library('pos_system/Pos_integrations');
    $CI->pos_integrations->post_sale_to_accounting(
        $payload['sale'],
        $payload['items']    ?? [],
        $payload['payments'] ?? [],
        $payload['staff_id'] ?? 0
    );
}

/**
 * Hook: pos_sale_completed — deduct stock from warehouse inventory_manage.
 * $payload = ['sale' => [...], 'items' => [...], 'staff_id' => int]
 */
function pos_system_deduct_warehouse_stock($payload)
{
    if (!is_array($payload) || empty($payload['items'])) {
        return;
    }

    if (pos_get_setting('pos_warehouse_sync', $payload['sale']['branch_id'] ?? 0) === '0') {
        return;
    }

    $CI = &get_instance();
    $CI->load->library('pos_system/Pos_integrations');

    $sale_id   = (int)($payload['sale']['id']        ?? 0);
    $branch_id = (int)($payload['sale']['branch_id'] ?? 0);

    foreach ($payload['items'] as $item) {
        $perfex_item_id = (int)($item['perfex_item_id'] ?? 0);
        if ($perfex_item_id > 0) {
            $CI->pos_integrations->deduct_warehouse_stock(
                $perfex_item_id,
                (float)($item['quantity'] ?? 1),
                $branch_id,
                $sale_id
            );
        }
    }
}

function pos_system_dashboard_stats()
{
    if (!has_permission('pos_system', '', 'view')) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('pos_system/Pos_model');

    $branch_id   = pos_get_staff_branch();
    $today_sales = $CI->pos_model->get_today_totals($branch_id);
    ?>
    <div class="col-md-3">
        <a href="<?php echo admin_url('pos_system'); ?>" class="block">
            <div class="panel_s">
                <div class="panel-body">
                    <div class="clearfix">
                        <div class="pull-left">
                            <span class="text-muted bold"><?php echo _l('pos_today_sales'); ?></span>
                            <h4 class="no-margin bold">
                                <?php echo pos_format_currency($today_sales['total'] ?? 0); ?>
                                <small class="text-muted">(<?php echo (int)($today_sales['count'] ?? 0); ?> txns)</small>
                            </h4>
                        </div>
                        <div class="pull-right">
                            <i class="fa fa-cash-register text-success" style="font-size:28px;opacity:.7"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php
}
