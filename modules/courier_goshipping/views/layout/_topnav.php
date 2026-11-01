<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$cgs_active = isset($cgs_active) ? $cgs_active : '';
$base = admin_url(COURIER_MODULE_NAME);

$CI =& get_instance();
$CI->load->model('courier_goshipping/Driver_model');
$cgs_admin = is_admin();
$cgs_user_role = $CI->Driver_model->get_staff_role(get_staff_user_id());
$cgs_driver_only = ($cgs_user_role === 'Fleet: Driver' && !$cgs_admin);

// Nav visibility mirrors the actual capability checks each page enforces —
// an agent (or any restricted staff) should never see a menu item leading
// to something they don't actually have permission for.
$cgs_can_create_courier          = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_courier');
$cgs_can_create_road             = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_road');
$cgs_can_create_domestic         = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_domestic');
$cgs_can_create_fcl              = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_fcl');
$cgs_can_create_lcl              = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_lcl');
$cgs_can_create_consolidation    = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_consolidation');
$cgs_can_create_air_freight      = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_air_freight');
$cgs_can_create_air_consol       = $cgs_admin || has_permission('courier-shipments', '', 'create_shipments') || has_permission('courier-shipments', '', 'create_shipment_air_consolidation');
$cgs_can_view_shipments          = $cgs_admin || has_permission('courier-shipments', '', 'view_own_shipments') || has_permission('courier-shipments', '', 'view_all_shipments');
$cgs_can_view_all_shipments      = $cgs_admin || has_permission('courier-shipments', '', 'view_all_shipments');

$cgs_can_view_pickups   = $cgs_admin || has_permission('courier-pickups', '', 'view_own_pickups') || has_permission('courier-pickups', '', 'view_all_pickups');
$cgs_can_view_companies = $cgs_admin || has_permission('courier-companies', '', 'view_companies');
$cgs_can_view_agents    = $cgs_admin || has_permission('courier-agents', '', 'view_agents');
$cgs_can_view_branches  = $cgs_admin || has_permission('courier-branches', '', 'view_branches');
// Client_quotes.php itself requires view_all_shipments specifically.
$cgs_can_view_quotes    = $cgs_can_view_all_shipments;

$cgs_can_view_manifests = $cgs_admin || has_permission('courier-manifests', '', 'view_own_manifests') || has_permission('courier-manifests', '', 'view_manifests');
$cgs_can_view_waybills  = $cgs_admin || has_permission('courier-waybills', '', 'view_own_waybills') || has_permission('courier-waybills', '', 'view_waybills');
$cgs_can_view_invoices  = $cgs_admin || has_permission('courier-invoices', '', 'view_own_invoices') || has_permission('courier-invoices', '', 'view_invoices');

$cgs_can_view_settings  = $cgs_admin || has_permission('courier-settings', '', 'view_settings');

$cgs_can_view_documents_menu = $cgs_can_view_manifests || $cgs_can_view_waybills || $cgs_can_view_invoices || $cgs_can_view_quotes;
$cgs_is_country_admin        = function_exists('courier_is_country_admin') && courier_is_country_admin();
$cgs_can_view_network_menu   = $cgs_admin || $cgs_is_country_admin || $cgs_can_view_pickups || $cgs_can_view_companies || $cgs_can_view_agents || $cgs_can_view_branches || $cgs_can_view_quotes;
$cgs_can_view_reporting_menu = $cgs_can_view_shipments || $cgs_can_view_manifests;
?>
<div id="wrapper">
<div class="cgs-page">
<nav class="cgs-topnav">
    <div class="cgs-topnav__nav">
        <?php if ($cgs_driver_only): ?>
        <div class="cgs-topnav__item">
            <a href="<?php echo $base . '/pickups/main'; ?>" class="cgs-topnav__link <?php echo $cgs_active === 'pickups' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-truck"></i> Pickups
            </a>
        </div>
        <?php else: ?>
        <div class="cgs-topnav__item">
            <a href="<?php echo $base . '/dashboard'; ?>" class="cgs-topnav__link <?php echo $cgs_active === 'dashboard' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-home"></i> Dashboard
            </a>
        </div>

        <div class="cgs-topnav__item" data-menu="cgs-menu-shipments">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'shipments' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-globe"></i> Shipments <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-shipments" class="cgs-mega-menu">
                <div class="cgs-mega-menu__column">
                    <p class="cgs-mega-menu__section-label">Shipment Dashboard</p>
                    <a href="<?php echo $base . '/shipments/main'; ?>" class="cgs-mega-menu__setup-link">
                        <div class="cgs-mega-menu__icon-wrap cgs-icon-swatch cgs-icon-swatch--indigo"><i class="fa fa-bar-chart"></i></div>
                        <span>Shipment Dashboard</span>
                    </a>
                    <?php if ($cgs_can_create_domestic): ?>
                    <p class="cgs-mega-menu__section-label">Domestic Shipment</p>
                    <a href="<?php echo $base . '/shipments?type=domestic'; ?>" class="cgs-mega-menu__setup-link">
                        <div class="cgs-mega-menu__icon-wrap cgs-icon-swatch cgs-icon-swatch--teal"><i class="fa fa-home"></i></div>
                        <span>Domestic Shipment</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php if ($cgs_can_create_courier || $cgs_can_create_road): ?>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International</p>
                    <?php if ($cgs_can_create_courier): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=courier&mode_type=none'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-truck cgs-icon-swatch cgs-icon-swatch--purple"></i><span>International Courier</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_create_road): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=road&mode_type=none'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-road cgs-icon-swatch cgs-icon-swatch--red"></i><span>International Road</span></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($cgs_can_create_air_freight || $cgs_can_create_air_consol): ?>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International By Air</p>
                    <?php if ($cgs_can_create_air_freight): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=air&mode_type=air_freight'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-plane cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Air Freight</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_create_air_consol): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=air&mode_type=air_consolidation'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-paper-plane cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Air Consolidation</span></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($cgs_can_create_fcl || $cgs_can_create_lcl || $cgs_can_create_consolidation): ?>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International By Sea</p>
                    <?php if ($cgs_can_create_fcl): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=fcl'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-ship cgs-icon-swatch cgs-icon-swatch--amber"></i><span>FCL</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_create_lcl): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=lcl'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-anchor cgs-icon-swatch cgs-icon-swatch--teal"></i><span>LCL</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_create_consolidation): ?>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=sea_consolidation'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cubes cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Consolidation</span></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($cgs_can_view_documents_menu): ?>
        <div class="cgs-topnav__item" data-menu="cgs-menu-documents">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'documents' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-folder-open"></i> Documents <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-documents" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Documents</p>
                    <?php if ($cgs_can_view_manifests): ?>
                    <a href="<?php echo $base . '/cargo_manifest'; ?>"><i class="fa fa-file cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Manifests</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_waybills): ?>
                    <?php // Was hardcoded to ?type=domestic, which silently
                    // hid every international/Salibay waybill (shipping_
                    // category='international') from this page — Waybills
                    // should list every waybill, not just domestic ones. ?>
                    <a href="<?php echo $base . '/shipments'; ?>"><i class="fa fa-file-text cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Waybills</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_invoices): ?>
                    <a href="<?php echo $base . '/shipments/list_commercial_invoices'; ?>"><i class="fa fa-file-invoice cgs-icon-swatch cgs-icon-swatch--red"></i><span>Commercial Invoices</span></a>
                    <a href="<?php echo $base . '/shipments/list_invoices'; ?>"><i class="fa fa-file-text-o cgs-icon-swatch cgs-icon-swatch--amber"></i><span>Courier Invoices</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_quotes): ?>
                    <a href="<?php echo $base . '/client_quotes'; ?>"><i class="fa fa-list-alt cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Client Quotes</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($cgs_can_view_network_menu): ?>
        <div class="cgs-topnav__item" data-menu="cgs-menu-network">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo in_array($cgs_active, ['network', 'branches'], true) ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-sitemap"></i> Network <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-network" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Network</p>
                    <?php if ($cgs_can_view_pickups): ?>
                    <a href="<?php echo $base . '/pickups/main'; ?>"><i class="fa fa-truck cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Pickups</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_companies): ?>
                    <a href="<?php echo $base . '/companies/main'; ?>"><i class="fa fa-building cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Courier Companies</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_agents): ?>
                    <a href="<?php echo $base . '/agents/main'; ?>"><i class="fa fa-users cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Agents</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_is_country_admin): ?>
                    <a href="<?php echo $base . '/country_team'; ?>"><i class="fa fa-user-plus cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Country Team</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_admin): ?>
                    <a href="<?php echo $base . '/country_admins'; ?>"><i class="fa fa-flag cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Country Admins</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_branches): ?>
                    <a href="<?php echo $base . '/branches/main'; ?>"><i class="fa fa-globe cgs-icon-swatch cgs-icon-swatch--red"></i><span>Branches / Offices</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_quotes): ?>
                    <a href="<?php echo $base . '/client_quotes'; ?>"><i class="fa fa-list-alt cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Client Quotes</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($cgs_can_view_reporting_menu): ?>
        <div class="cgs-topnav__item" data-menu="cgs-menu-reporting">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'reporting' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-bar-chart"></i> Reporting <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-reporting" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Reporting & Analytics</p>
                    <?php if ($cgs_can_view_shipments): ?>
                    <a href="<?php echo $base . '/shipments/main?group=dashboard'; ?>"><i class="fa fa-line-chart cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Shipment KPIs</span></a>
                    <a href="<?php echo $base . '/shipments?type=domestic'; ?>"><i class="fa fa-filter cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Status Analytics</span></a>
                    <?php endif; ?>
                    <?php if ($cgs_can_view_manifests): ?>
                    <a href="<?php echo $base . '/cargo_manifest'; ?>"><i class="fa fa-area-chart cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Manifest Performance</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // Same gate as Fulfilment::can_view_fulfilment() — deliberately NOT
        // implied by any courier-shipments capability, so agents (who get
        // view_own_shipments by default) don't see this menu unless an
        // admin explicitly grants one of the Courier - Salibay Fulfilment
        // capabilities (or, for backward compatibility, the older
        // Shopify Connector permission some staff may already have).
        $can_view_salibay_fulfilment = is_admin()
            || has_permission('shopify_connector', '', 'view_shopify_connector')
            || has_permission('shopify_connector', '', 'manage_shopify_connector')
            || staff_can('view_salibay', 'courier-salibay')
            || staff_can('create_salibay_shipments', 'courier-salibay')
            || staff_can('manage_salibay_riders', 'courier-salibay')
            || staff_can('manage_salibay_settings', 'courier-salibay')
            || staff_can('manage_salibay_logs', 'courier-salibay');

        // Mirror Fulfilment's SPECIFIC per-action gates — NOT one broad OR.
        // This exact spot had the same bug already fixed once in the
        // controller: using one umbrella "any manage permission" check here
        // meant a staffer granted only Manage Riders still saw the
        // Integration Health and Configuration columns (which sit behind
        // can_manage_salibay_logs()/can_manage_salibay_settings()
        // specifically at the controller level, not "any manage
        // permission"). Keep these matched 1:1 to the controller checks.
        $cgs_can_manage_salibay_settings = is_admin()
            || has_permission('shopify_connector', '', 'manage_shopify_connector')
            || staff_can('manage_salibay_settings', 'courier-salibay');
        $cgs_can_manage_salibay_logs = is_admin()
            || has_permission('shopify_connector', '', 'manage_shopify_connector')
            || staff_can('manage_salibay_logs', 'courier-salibay');
        ?>
        <?php if ($can_view_salibay_fulfilment): ?>
        <div class="cgs-topnav__item" data-menu="cgs-menu-fulfilment">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'fulfilment' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-cubes"></i> Salibay Fulfilment <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-fulfilment" class="cgs-mega-menu">
                <div class="cgs-mega-menu__column">
                    <p class="cgs-mega-menu__section-label">Orders</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/dashboard'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-dashboard cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Orders Dashboard</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/orders'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-shopping-cart cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Orders from Salibay</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/orders?status=processing'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-truck cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Pending Dispatch</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/salibay_order_list'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-list-alt cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Salibay Order List</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/riders'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-motorcycle cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Riders</span></a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Inventory</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/inventory'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cubes cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Inventory Overview</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/inventory'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-refresh cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Last Inventory Sync</span></a>
                    <?php if ($cgs_can_manage_salibay_logs): ?>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-exchange cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Inventory Sync Logs</span></a>
                    <?php endif; ?>
                </div>
                <?php if ($cgs_can_manage_salibay_logs): ?>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Integration Health</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-heartbeat cgs-icon-swatch cgs-icon-swatch--red"></i><span>Webhook Events Pending</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-warning cgs-icon-swatch cgs-icon-swatch--amber"></i><span>Webhook Events Failed</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-check-circle cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Integration Health</span></a>
                </div>
                <?php endif; ?>
                <?php if ($cgs_can_manage_salibay_settings): ?>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Configuration</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cog cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Connector Settings</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings#webhooks'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-link cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Webhooks</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings#advanced_settings'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-sliders cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Advanced Settings</span></a>
                    <a href="<?php echo site_url('courier_goshipping/rider'); ?>" target="_blank" class="cgs-mega-menu__setup-link"><i class="fa fa-mobile cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Rider App Link</span></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="cgs-topnav__item">
            <a href="<?php echo base_url('courier_goshipping/tracking'); ?>" target="_blank" class="cgs-topnav__link">
                <i class="fa fa-external-link"></i> Client Portal
            </a>
        </div>

        <?php if ($cgs_can_view_settings): ?>
        <div class="cgs-topnav__item" data-menu="cgs-menu-settings">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'settings' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-cogs"></i> Settings <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-settings" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Settings</p>
                    <a href="<?php echo $base . '/settings/main?group=customization'; ?>"><i class="fa fa-paint-brush cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Customization</span></a>
                    <a href="<?php echo $base . '/settings/main?group=service_points'; ?>"><i class="fa fa-map-marker cgs-icon-swatch cgs-icon-swatch--red"></i><span>Service Points</span></a>
                    <a href="<?php echo $base . '/settings/main?group=tariff'; ?>"><i class="fa fa-money cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Tariffs</span></a>
                    <a href="<?php echo $base . '/settings/main?group=international_tariffs'; ?>"><i class="fa fa-globe cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>International Tariffs</span></a>
                    <a href="<?php echo $base . '/settings/main?group=invoice_info'; ?>"><i class="fa fa-file-text-o cgs-icon-swatch cgs-icon-swatch--amber"></i><span>Invoice Info</span></a>
                    <a href="<?php echo $base . '/settings/main?group=appearance'; ?>"><i class="fa fa-tint cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Appearance</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div><!-- /.cgs-topnav__nav -->
</nav>
<script>
/* Form keep-alive: when a form POST comes back from the server with validation
 * errors, put back everything the user had typed/selected so a missed field
 * never wipes the rest. Server-side set_value() stays the primary mechanism;
 * this is the safety net for any field a view forgot to repopulate. Only
 * restores a draft saved <2 min ago, only on the page that is the POST
 * response (same path as the form action) and only when the page shows an
 * error — a normal visit or a successful save never restores anything. */
(function () {
    if (window.__cgsFormKeep) { return; }
    window.__cgsFormKeep = true;
    var PFX = 'cgs_formkeep:', TTL = 2 * 60 * 1000;
    try { sessionStorage.setItem('__cgs_t', '1'); sessionStorage.removeItem('__cgs_t'); } catch (e) { return; }

    function actionPath(f) {
        try { return new URL(f.getAttribute('action') || location.href, location.href).pathname; } catch (e) { return ''; }
    }
    function keyOf(f) { return PFX + actionPath(f) + '#' + (f.id || ''); }
    function skip(el) {
        var t = (el.type || '').toLowerCase();
        return !el.name || el.disabled || /^(hidden|password|file|submit|button|image|reset)$/.test(t) || /csrf|token/i.test(el.name);
    }
    function snapshot(f) {
        var d = [];
        Array.prototype.forEach.call(f.elements, function (el) {
            if (skip(el)) { return; }
            var t = (el.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio') { d.push({ n: el.name, v: el.value, c: el.checked ? 1 : 0, k: 'c' }); }
            else if (el.tagName === 'SELECT' && el.multiple) {
                d.push({ n: el.name, v: Array.prototype.filter.call(el.options, function (o) { return o.selected; }).map(function (o) { return o.value; }), k: 'm' });
            } else { d.push({ n: el.name, v: el.value, k: 'v' }); }
        });
        return d;
    }

    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || !f.tagName || f.tagName !== 'FORM' || (f.method || '').toLowerCase() !== 'post' || e.defaultPrevented) { return; }
        try { sessionStorage.setItem(keyOf(f), JSON.stringify({ t: Date.now(), d: snapshot(f) })); } catch (x) {}
    }, false);

    function refresh(el) {
        try {
            if (el._flatpickr) { el._flatpickr.setDate(el.value, false); }
            if (window.jQuery) { window.jQuery(el).trigger('change.select2'); }
        } catch (x) {}
    }

    function restore(f, saved) {
        var byName = {};
        saved.d.forEach(function (s) { (byName[s.n] = byName[s.n] || []).push(s); });
        var used = {}, pending = [];
        Array.prototype.forEach.call(f.elements, function (el) {
            if (skip(el)) { return; }
            var list = byName[el.name]; if (!list) { return; }
            var t = (el.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio') {
                var s = list.filter(function (x) { return x.k === 'c' && x.v === el.value; })[0];
                if (s) { var want = !!s.c; if (el.checked !== want) { el.checked = want; } }
                return;
            }
            var i = used[el.name] || 0; used[el.name] = i + 1;
            var sv = list.filter(function (x) { return x.k !== 'c'; })[i];
            if (!sv) { return; }
            if (sv.k === 'm' && el.tagName === 'SELECT') {
                Array.prototype.forEach.call(el.options, function (o) { o.selected = sv.v.indexOf(o.value) !== -1; });
                refresh(el);
            } else if (el.tagName === 'SELECT') {
                var has = Array.prototype.some.call(el.options, function (o) { return o.value === sv.v; });
                if (has) { if (el.value !== sv.v) { el.value = sv.v; refresh(el); } }
                else if (sv.v !== '') { pending.push([el, sv.v]); }
            } else if (el.value !== sv.v && sv.v !== '') {
                el.value = sv.v; refresh(el);
            }
        });
        // selects filled by AJAX (e.g. states after a country is chosen)
        [1200, 3000].forEach(function (ms) {
            setTimeout(function () {
                pending.forEach(function (p) {
                    var el = p[0], has = Array.prototype.some.call(el.options, function (o) { return o.value === p[1]; });
                    if (has && el.value !== p[1]) { el.value = p[1]; refresh(el); }
                });
            }, ms);
        });
    }

    window.addEventListener('load', function () {
        setTimeout(function () {
            try {
                var hasError = document.querySelector('.error-message:not(:empty), .cgs-field-error-msg, #cgs-pickup-server-alert, .alert-danger, .has-error');
                Array.prototype.forEach.call(document.forms, function (f) {
                    if ((f.method || '').toLowerCase() !== 'post') { return; }
                    var key = keyOf(f), raw = null;
                    try { raw = sessionStorage.getItem(key); } catch (x) {}
                    if (!raw) { return; }
                    var saved = JSON.parse(raw);
                    // single use either way, so a draft can never come back later
                    try { sessionStorage.removeItem(key); } catch (x) {}
                    if (!hasError || location.pathname !== actionPath(f) || !saved || Date.now() - saved.t > TTL) { return; }
                    restore(f, saved);
                });
            } catch (x) {}
        }, 60);
    });
})();
</script>
