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
$cgs_can_view_network_menu   = $cgs_can_view_pickups || $cgs_can_view_companies || $cgs_can_view_agents || $cgs_can_view_branches || $cgs_can_view_quotes;
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
                    <p class="cgs-mega-menu__section-label">Domestic Shipment</p>
                    <a href="<?php echo $base . '/shipments?type=domestic'; ?>" class="cgs-mega-menu__setup-link">
                        <div class="cgs-mega-menu__icon-wrap cgs-icon-swatch cgs-icon-swatch--teal"><i class="fa fa-home"></i></div>
                        <span>Domestic Shipment</span>
                    </a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International</p>
                    <a href="<?php echo $base . '/shipments?type=international&mode=courier&mode_type=none'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-truck cgs-icon-swatch cgs-icon-swatch--purple"></i><span>International Courier</span></a>
                    <a href="<?php echo $base . '/shipments?type=international&mode=road&mode_type=none'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-road cgs-icon-swatch cgs-icon-swatch--red"></i><span>International Road</span></a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International By Air</p>
                    <a href="<?php echo $base . '/shipments?type=international&mode=air&mode_type=air_freight'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-plane cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Air Freight</span></a>
                    <a href="<?php echo $base . '/shipments?type=international&mode=air&mode_type=air_consolidation'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-paper-plane cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Air Consolidation</span></a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">International By Sea</p>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=fcl'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-ship cgs-icon-swatch cgs-icon-swatch--amber"></i><span>FCL</span></a>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=lcl'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-anchor cgs-icon-swatch cgs-icon-swatch--teal"></i><span>LCL</span></a>
                    <a href="<?php echo $base . '/shipments?type=international&mode=sea&mode_type=sea_consolidation'; ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cubes cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Consolidation</span></a>
                </div>
            </div>
        </div>

        <div class="cgs-topnav__item" data-menu="cgs-menu-documents">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'documents' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-folder-open"></i> Documents <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-documents" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Documents</p>
                    <a href="<?php echo $base . '/shipments/main?group=manifests'; ?>"><i class="fa fa-file cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Manifests</span></a>
                    <a href="<?php echo $base . '/shipments?type=domestic'; ?>"><i class="fa fa-file-text cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Waybills</span></a>
                    <a href="<?php echo $base . '/shipments/list_commercial_invoices'; ?>"><i class="fa fa-file-invoice cgs-icon-swatch cgs-icon-swatch--red"></i><span>Commercial Invoices</span></a>
                    <a href="<?php echo $base . '/shipments/list_invoices'; ?>"><i class="fa fa-file-text-o cgs-icon-swatch cgs-icon-swatch--amber"></i><span>Courier Invoices</span></a>
                    <a href="<?php echo $base . '/client_quotes'; ?>"><i class="fa fa-list-alt cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Client Quotes</span></a>
                </div>
            </div>
        </div>

        <div class="cgs-topnav__item" data-menu="cgs-menu-network">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo in_array($cgs_active, ['network', 'branches'], true) ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-sitemap"></i> Network <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-network" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Network</p>
                    <a href="<?php echo $base . '/pickups/main'; ?>"><i class="fa fa-truck cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Pickups</span></a>
                    <a href="<?php echo $base . '/companies/main'; ?>"><i class="fa fa-building cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Courier Companies</span></a>
                    <a href="<?php echo $base . '/agents/main'; ?>"><i class="fa fa-users cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Agents</span></a>
                    <a href="<?php echo $base . '/branches/main'; ?>"><i class="fa fa-globe cgs-icon-swatch cgs-icon-swatch--red"></i><span>Branches / Offices</span></a>
                    <a href="<?php echo $base . '/client_quotes'; ?>"><i class="fa fa-list-alt cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Client Quotes</span></a>
                </div>
            </div>
        </div>

        <div class="cgs-topnav__item" data-menu="cgs-menu-reporting">
            <a href="javascript:void(0);" class="cgs-topnav__link cgs-topnav__link--has-menu <?php echo $cgs_active === 'reporting' ? 'cgs-topnav__link--active' : ''; ?>">
                <i class="fa fa-bar-chart"></i> Reporting <i class="fa fa-angle-down cgs-chevron"></i>
            </a>
            <div id="cgs-menu-reporting" class="cgs-dropdown-menu">
                <div class="cgs-dropdown-menu__group">
                    <p class="cgs-dropdown-menu__label">Reporting & Analytics</p>
                    <a href="<?php echo $base . '/shipments/main?group=dashboard'; ?>"><i class="fa fa-line-chart cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Shipment KPIs</span></a>
                    <a href="<?php echo $base . '/shipments?type=domestic'; ?>"><i class="fa fa-filter cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Status Analytics</span></a>
                    <a href="<?php echo $base . '/shipments/main?group=manifests'; ?>"><i class="fa fa-area-chart cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Manifest Performance</span></a>
                </div>
            </div>
        </div>

        <?php
        // Same gate as Fulfilment::can_view_fulfilment() — deliberately NOT
        // implied by any courier-shipments capability, so agents (who get
        // view_own_shipments by default) don't see this menu unless an
        // admin explicitly grants the shopify_connector permission.
        $can_view_salibay_fulfilment = is_admin()
            || has_permission('shopify_connector', '', 'view_shopify_connector')
            || has_permission('shopify_connector', '', 'manage_shopify_connector');
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
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Inventory</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/inventory'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cubes cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Inventory Overview</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/inventory'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-refresh cgs-icon-swatch cgs-icon-swatch--emerald"></i><span>Last Inventory Sync</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-exchange cgs-icon-swatch cgs-icon-swatch--indigo"></i><span>Inventory Sync Logs</span></a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Integration Health</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-heartbeat cgs-icon-swatch cgs-icon-swatch--red"></i><span>Webhook Events Pending</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health#tab_logs'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-warning cgs-icon-swatch cgs-icon-swatch--amber"></i><span>Webhook Events Failed</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/health'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-check-circle cgs-icon-swatch cgs-icon-swatch--blue"></i><span>Integration Health</span></a>
                </div>
                <div class="cgs-mega-menu__column cgs-mega-menu__column--bordered">
                    <p class="cgs-mega-menu__section-label">Configuration</p>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-cog cgs-icon-swatch cgs-icon-swatch--purple"></i><span>Connector Settings</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings#webhooks'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-link cgs-icon-swatch cgs-icon-swatch--teal"></i><span>Webhooks</span></a>
                    <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings#advanced_settings'); ?>" class="cgs-mega-menu__setup-link"><i class="fa fa-sliders cgs-icon-swatch cgs-icon-swatch--orange"></i><span>Advanced Settings</span></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="cgs-topnav__item">
            <a href="<?php echo base_url('courier_goshipping/tracking'); ?>" target="_blank" class="cgs-topnav__link">
                <i class="fa fa-external-link"></i> Client Portal
            </a>
        </div>

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
    </div><!-- /.cgs-topnav__nav -->
</nav>
