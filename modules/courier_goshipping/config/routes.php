<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * General Routes
 **/
$route['admin/courier_goshipping'] = 'courier_goshipping/courier/dashboard';
$route['admin/courier_goshipping/dashboard'] = 'courier_goshipping/courier/dashboard';
$route['admin/courier_goshipping/states'] = 'courier_goshipping/courier/states';
$route['admin/courier_goshipping/fulfilment'] = 'courier_goshipping/fulfilment/index';
$route['admin/courier_goshipping/fulfilment/dashboard'] = 'courier_goshipping/fulfilment/dashboard';
$route['admin/courier_goshipping/fulfilment/orders'] = 'courier_goshipping/fulfilment/orders';
$route['admin/courier_goshipping/fulfilment/salibay_order_list'] = 'courier_goshipping/fulfilment/salibay_order_list';
$route['admin/courier_goshipping/fulfilment/get_salibay_order_list_datatable'] = 'courier_goshipping/fulfilment/get_salibay_order_list_datatable';
$route['admin/courier_goshipping/fulfilment/inventory'] = 'courier_goshipping/fulfilment/inventory';
$route['admin/courier_goshipping/fulfilment/health'] = 'courier_goshipping/fulfilment/health';
$route['admin/courier_goshipping/fulfilment/settings'] = 'courier_goshipping/fulfilment/settings';
$route['admin/courier_goshipping/fulfilment/save_settings'] = 'courier_goshipping/fulfilment/save_settings';
$route['admin/courier_goshipping/fulfilment/register_webhooks'] = 'courier_goshipping/fulfilment/register_webhooks';
$route['admin/courier_goshipping/fulfilment/delete_webhooks'] = 'courier_goshipping/fulfilment/delete_webhooks';
$route['admin/courier_goshipping/fulfilment/test_connection'] = 'courier_goshipping/fulfilment/test_connection';
$route['admin/courier_goshipping/fulfilment/ensure_virtual_warehouse_ajax'] = 'courier_goshipping/fulfilment/ensure_virtual_warehouse_ajax';
$route['admin/courier_goshipping/fulfilment/get_orders_datatable'] = 'courier_goshipping/fulfilment/get_orders_datatable';
$route['admin/courier_goshipping/fulfilment/get_order_detail/(:num)'] = 'courier_goshipping/fulfilment/get_order_detail/$1';
$route['admin/courier_goshipping/fulfilment/create_shipment/(:num)'] = 'courier_goshipping/fulfilment/create_shipment/$1';
$route['admin/courier_goshipping/fulfilment/get_inventory_datatable'] = 'courier_goshipping/fulfilment/get_inventory_datatable';
$route['admin/courier_goshipping/fulfilment/run_inventory_sync'] = 'courier_goshipping/fulfilment/run_inventory_sync';
$route['admin/courier_goshipping/fulfilment/get_product_mappings'] = 'courier_goshipping/fulfilment/get_product_mappings';
$route['admin/courier_goshipping/fulfilment/save_product_mapping'] = 'courier_goshipping/fulfilment/save_product_mapping';
$route['admin/courier_goshipping/fulfilment/delete_product_mapping/(:num)'] = 'courier_goshipping/fulfilment/delete_product_mapping/$1';
$route['admin/courier_goshipping/fulfilment/import_shopify_products'] = 'courier_goshipping/fulfilment/import_shopify_products';
$route['admin/courier_goshipping/fulfilment/sync_shopify_locations'] = 'courier_goshipping/fulfilment/sync_shopify_locations';
$route['admin/courier_goshipping/fulfilment/save_location_branch_map'] = 'courier_goshipping/fulfilment/save_location_branch_map';
$route['admin/courier_goshipping/fulfilment/get_webhook_events_datatable'] = 'courier_goshipping/fulfilment/get_webhook_events_datatable';
$route['admin/courier_goshipping/fulfilment/get_logs_datatable'] = 'courier_goshipping/fulfilment/get_logs_datatable';
$route['admin/courier_goshipping/fulfilment/requeue_webhook_event/(:num)'] = 'courier_goshipping/fulfilment/requeue_webhook_event/$1';
$route['admin/courier_goshipping/fulfilment/retry_all_failed_webhooks'] = 'courier_goshipping/fulfilment/retry_all_failed_webhooks';
$route['admin/courier_goshipping/fulfilment/clear_done_events'] = 'courier_goshipping/fulfilment/clear_done_events';
$route['admin/courier_goshipping/fulfilment/get_raw_data/(:any)/(:num)'] = 'courier_goshipping/fulfilment/get_raw_data/$1/$2';
$route['admin/courier_goshipping/fulfilment/clear_logs'] = 'courier_goshipping/fulfilment/clear_logs';
$route['admin/courier_goshipping/fulfilment/generate_test_log'] = 'courier_goshipping/fulfilment/generate_test_log';
$route['admin/courier_goshipping/fulfilment/export_logs_csv'] = 'courier_goshipping/fulfilment/export_logs_csv';
$route['admin/courier_goshipping/fulfilment/get_health_status'] = 'courier_goshipping/fulfilment/get_health_status';

/*
 * Salibay Delivery — short public rider flow (no login), see
 * controllers/Salibay_delivery.php
 **/
$route['admin/courier_goshipping/salibay_delivery/rider/(:any)'] = 'courier_goshipping/salibay_delivery/rider/$1';
$route['admin/courier_goshipping/salibay_delivery/start'] = 'courier_goshipping/salibay_delivery/start';
$route['admin/courier_goshipping/salibay_delivery/deliver'] = 'courier_goshipping/salibay_delivery/deliver';
$route['admin/courier_goshipping/salibay_delivery/cancel'] = 'courier_goshipping/salibay_delivery/cancel';

/*
 * Rider PWA — installable app for riders, see controllers/Rider_app.php
 * (shell/manifest/sw) and Rider_api.php (JSON API).
 *
 * URL note: MX only ever loads a module's own config/routes.php when the
 * URL's FIRST segment already equals that module's folder name (see
 * MX_Router::locate() -> Modules::parse_routes($segments[0], ...)) — a
 * bare top-level path like "/rider" has no matching module folder and
 * 404s before this file is even consulted. "admin/..." URLs work through
 * a separate, unrelated fallback (a hardcoded admin-prefix strip that
 * dispatches straight to module/controller/method) that never reads this
 * file either. So the deployable URL has to keep the "courier_goshipping/"
 * prefix — a truly bare "/rider" would require an entry in the *core*
 * application/config/routes.php, which today's cron deploy script
 * (modules/* only) never copies to the server.
 **/
$route['courier_goshipping/rider'] = 'courier_goshipping/rider_app/index';
$route['courier_goshipping/rider/manifest'] = 'courier_goshipping/rider_app/manifest';
$route['courier_goshipping/rider/sw'] = 'courier_goshipping/rider_app/sw';
$route['courier_goshipping/rider/icon/(:num)'] = 'courier_goshipping/rider_app/icon/$1';

$route['courier_goshipping/rider-api/register'] = 'courier_goshipping/rider_api/register';
$route['courier_goshipping/rider-api/login'] = 'courier_goshipping/rider_api/login';
$route['courier_goshipping/rider-api/logout'] = 'courier_goshipping/rider_api/logout';
$route['courier_goshipping/rider-api/me'] = 'courier_goshipping/rider_api/me';
$route['courier_goshipping/rider-api/deliveries'] = 'courier_goshipping/rider_api/deliveries';
$route['courier_goshipping/rider-api/deliveries/(:num)/start'] = 'courier_goshipping/rider_api/delivery_start/$1';
$route['courier_goshipping/rider-api/deliveries/(:num)/deliver'] = 'courier_goshipping/rider_api/delivery_deliver/$1';
$route['courier_goshipping/rider-api/deliveries/(:num)/cancel'] = 'courier_goshipping/rider_api/delivery_cancel/$1';
$route['courier_goshipping/rider-api/pickups'] = 'courier_goshipping/rider_api/pickups';
$route['courier_goshipping/rider-api/pickups/(:num)/update'] = 'courier_goshipping/rider_api/pickup_update/$1';

$route['courier_goshipping/tracking'] = 'tracker/tracking';
$route['courier_goshipping/tracking/shipment_info'] = 'tracker/shipment_info';

/*
 * Client Portal — dedicated per-tab URLs (same page, tab pre-selected server-side)
 **/
$route['courier_goshipping/track'] = 'tracker/tracking/track';
$route['courier_goshipping/get-a-quote'] = 'tracker/tracking/quote';
$route['courier_goshipping/schedule-delivery'] = 'tracker/tracking/pickup';
$route['courier_goshipping/find-service-point'] = 'tracker/tracking/service';
$route['courier_goshipping/create-shipment'] = 'tracker/tracking/shipment';
$route['courier_goshipping/call-for-booking'] = 'tracker/tracking/call';

/*
 * Shipments Routes
 **/
$route['admin/courier_goshipping/shipments/main'] = 'courier_goshipping/shipments/main';
$route['admin/courier_goshipping/shipments/dashboard'] = 'courier_goshipping/shipments/dashboard';
$route['admin/courier_goshipping/shipments'] = 'courier_goshipping/shipments/index';
$route['admin/courier_goshipping/shipments/create'] = 'courier_goshipping/shipments/create';
$route['admin/courier_goshipping/shipments/delete'] = 'courier_goshipping/shipments/delete';
$route['admin/courier_goshipping/shipments/store'] = 'courier_goshipping/shipments/store';
$route['admin/courier_goshipping/shipments/list_invoices'] = 'courier_goshipping/shipments/list_invoices';
$route['admin/courier_goshipping/shipments/list_commercial_invoices'] = 'courier_goshipping/shipments/list_commercial_invoices';
$route['admin/courier_goshipping/shipments/quotation/(:num)']        = 'courier_goshipping/shipments/quotation/$1';
$route['admin/courier_goshipping/shipments/courier_invoice/(:num)']           = 'courier_goshipping/shipments/courier_invoice/$1';
$route['admin/courier_goshipping/shipments/record_courier_payment/(:num)']    = 'courier_goshipping/shipments/record_courier_payment/$1';
$route['admin/courier_goshipping/shipments/consignment_note/(:num)'] = 'courier_goshipping/shipments/consignment_note/$1';
$route['admin/courier_goshipping/shipments/waybill/(:num)'] = 'courier_goshipping/shipments/waybill/$1';
$route['admin/courier_goshipping/shipments/send_waybill_email/(:num)'] = 'courier_goshipping/shipments/send_waybill_email/$1';
$route['admin/courier_goshipping/shipments/commercial_invoice/(:num)'] = 'courier_goshipping/shipments/commercial_invoice/$1';
$route['admin/courier_goshipping/shipments/update_status/(:num)'] = 'courier_goshipping/shipments/update_status/$1';
$route['admin/courier_goshipping/shipments/manifest'] = 'courier_goshipping/shipments/manifest';
$route['admin/courier_goshipping/shipments/generate_manifest'] = 'courier_goshipping/shipments/generate_manifest';
$route['admin/courier_goshipping/shipments/insert_manifest'] = 'courier_goshipping/shipments/insert_manifest';
$route['admin/courier_goshipping/shipments/filter_shipments'] = 'courier_goshipping/shipments/filter_shipments';
$route['admin/courier_goshipping/shipments/clear_filters'] = 'courier_goshipping/shipments/clear_filters';
$route['admin/courier_goshipping/shipments/portal_request_data/(:num)'] = 'courier_goshipping/shipments/portal_request_data/$1';
$route['admin/courier_goshipping/shipments/confirm_portal_request/(:num)'] = 'courier_goshipping/shipments/confirm_portal_request/$1';


/*
 * Pickups Routes
 **/
$route['admin/courier_goshipping/pickups/main'] = 'courier_goshipping/pickups/main';
$route['admin/courier_goshipping/pickups/dashboard'] = 'courier_goshipping/pickups/dashboard';
$route['admin/courier_goshipping/pickups'] = 'courier_goshipping/pickups/index';
$route['admin/courier_goshipping/pickups/create'] = 'courier_goshipping/pickups/create';
$route['admin/courier_goshipping/pickups/store'] = 'courier_goshipping/pickups/store';
$route['admin/courier_goshipping/pickups/delete/(:num)'] = 'courier_goshipping/pickups/delete/$1';
$route['admin/courier_goshipping/pickups/bulk_delete']   = 'courier_goshipping/pickups/bulk_delete';
$route['admin/courier_goshipping/pickups/update_status'] = 'courier_goshipping/pickups/update_status';
$route['admin/courier_goshipping/pickups/view/(:num)'] = 'courier_goshipping/pickups/view/$1';



/*
 * Branches / Offices Routes
 **/
$route['admin/courier_goshipping/branches'] = 'courier_goshipping/branches/main';
$route['admin/courier_goshipping/branches/main'] = 'courier_goshipping/branches/main';
$route['admin/courier_goshipping/branches/store'] = 'courier_goshipping/branches/store';
$route['admin/courier_goshipping/branches/update/(:num)'] = 'courier_goshipping/branches/update/$1';
$route['admin/courier_goshipping/branches/delete/(:num)'] = 'courier_goshipping/branches/delete/$1';

/*
 * Courier Companies Routes
 **/
$route['admin/courier_goshipping/companies'] = 'courier_goshipping/companies/index';
$route['admin/courier_goshipping/companies/main'] = 'courier_goshipping/companies/main';
$route['admin/courier_goshipping/companies/dashboard'] = 'courier_goshipping/companies/dashboard';
$route['admin/courier_goshipping/companies/create'] = 'courier_goshipping/companies/create';
$route['admin/courier_goshipping/companies/store'] = 'courier_goshipping/companies/store';
$route['admin/courier_goshipping/companies/delete/(:num)'] = 'courier_goshipping/companies/delete/$1';



/*
 * Agents Routes
 **/
$route['admin/courier_goshipping/agents/main'] = 'courier_goshipping/agents/main';
$route['admin/courier_goshipping/agents'] = 'courier_goshipping/agents/index';
$route['admin/courier_goshipping/agents/create'] = 'courier_goshipping/agents/create';
$route['admin/courier_goshipping/agents/store'] = 'courier_goshipping/agents/store';


/*
 * Manifests Routes
 **/
$route['admin/courier_goshipping/manifests'] = 'courier_goshipping/manifests/index';
$route['admin/courier_goshipping/manifests/store'] = 'courier_goshipping/manifests/store';
$route['admin/courier_goshipping/manifests/view/(:num)'] = 'courier_goshipping/manifests/view/$1';



/*
 * Agents Routes
 **/
$route['admin/courier_goshipping/agents/main'] = 'courier_goshipping/agents/main';
$route['admin/courier_goshipping/agents'] = 'courier_goshipping/agents/index';
$route['admin/courier_goshipping/agents/create'] = 'courier_goshipping/agents/create';
$route['admin/courier_goshipping/agents/store'] = 'courier_goshipping/agents/store';
$route['admin/courier_goshipping/agents/agent_number'] = 'courier_goshipping/agents/agent_number';
$route['admin/courier_goshipping/agents/update_status'] = 'courier_goshipping/agents/update_status';
$route['admin/courier_goshipping/agents/sync_role_permissions'] = 'courier_goshipping/agents/sync_role_permissions';
$route['admin/courier_goshipping/agents/delete/(:num)'] = 'courier_goshipping/agents/delete/$1';




/*
 * Settings Routes
 **/
$route['admin/courier_goshipping/settings/main'] =  'courier_goshipping/settings/main';
$route['admin/courier_goshipping/settings/dimensional_factor'] =  'courier_goshipping/settings/dimensional_factor';
$route['admin/courier_goshipping/settings/general'] =  'courier_goshipping/settings/general';

/*
 * Public Customer Portal Routes
 **/
$route['courier_goshipping/portal'] = 'tracker/portal';
$route['courier_goshipping/portal/quote'] = 'tracker/quote';
$route['courier_goshipping/portal/schedule_pickup'] = 'tracker/schedule_pickup';
$route['courier_goshipping/portal/store_pickup'] = 'tracker/store_pickup';
$route['courier_goshipping/portal/service_points'] = 'tracker/service_points';
$route['courier_goshipping/portal/create_shipment'] = 'tracker/public_create_shipment';
$route['courier_goshipping/portal/store_shipment'] = 'tracker/store_public_shipment';
$route['courier_goshipping/portal/tariff_zones'] = 'tracker/tariff_zones';
$route['courier_goshipping/portal/calculate_quote'] = 'tracker/calculate_quote';
$route['courier_goshipping/portal/send_quote_email'] = 'tracker/send_quote_email';
$route['courier_goshipping/portal/get_countries'] = 'tracker/get_countries';
$route['courier_goshipping/portal/get_cities'] = 'tracker/get_cities';
$route['courier_goshipping/portal/get_domestic_cities'] = 'tracker/get_domestic_cities';

/*
 * Tariff Settings Routes
 **/
$route['admin/courier_goshipping/settings/tariff'] = 'courier_goshipping/settings/main';
$route['admin/courier_goshipping/settings/download_tariff_template'] = 'courier_goshipping/settings/download_tariff_template';
$route['admin/courier_goshipping/settings/upload_tariff_csv'] = 'courier_goshipping/settings/upload_tariff_csv';
$route['admin/courier_goshipping/settings/delete_tariff_rate/(:num)'] = 'courier_goshipping/settings/delete_tariff_rate/$1';
$route['admin/courier_goshipping/settings/update_tariff_zone'] = 'courier_goshipping/settings/update_tariff_zone';
$route['admin/courier_goshipping/settings/download_domestic_tariff_template'] = 'courier_goshipping/settings/download_domestic_tariff_template';
$route['admin/courier_goshipping/settings/upload_domestic_tariff_csv'] = 'courier_goshipping/settings/upload_domestic_tariff_csv';
$route['admin/courier_goshipping/settings/delete_domestic_tariff/(.+)'] = 'courier_goshipping/settings/delete_domestic_tariff/$1';
$route['admin/courier_goshipping/settings/delete_domestic_tariff_rate/(:num)'] = 'courier_goshipping/settings/delete_domestic_tariff_rate/$1';
$route['admin/courier_goshipping/settings/domestic_tariff_rates_json'] = 'courier_goshipping/settings/domestic_tariff_rates_json';
