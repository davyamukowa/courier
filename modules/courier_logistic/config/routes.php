<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * General Routes
 **/
$route['admin/courier_logistic'] = 'courier_logistic/courier_logistic/index';
$route['admin/courier_logistic/dashboard'] = 'courier_logistic/courier_logistic/dashboard';
$route['admin/courier_logistic/states'] = 'courier_logistic/courier_logistic/states';
$route['admin/courier_logistic/fulfilment'] = 'courier_logistic/Fulfilment/index';
$route['admin/courier_logistic/Fulfilment/dashboard'] = 'courier_logistic/Fulfilment/dashboard';
$route['admin/courier_logistic/Fulfilment/orders'] = 'courier_logistic/Fulfilment/orders';
$route['admin/courier_logistic/Fulfilment/salibay_order_list'] = 'courier_logistic/Fulfilment/salibay_order_list';
$route['admin/courier_logistic/Fulfilment/get_salibay_order_list_datatable'] = 'courier_logistic/Fulfilment/get_salibay_order_list_datatable';
$route['admin/courier_logistic/Fulfilment/inventory'] = 'courier_logistic/Fulfilment/inventory';
$route['admin/courier_logistic/Fulfilment/health'] = 'courier_logistic/Fulfilment/health';
$route['admin/courier_logistic/Fulfilment/settings'] = 'courier_logistic/Fulfilment/settings';
$route['admin/courier_logistic/Fulfilment/save_settings'] = 'courier_logistic/Fulfilment/save_settings';
$route['admin/courier_logistic/Fulfilment/register_webhooks'] = 'courier_logistic/Fulfilment/register_webhooks';
$route['admin/courier_logistic/Fulfilment/delete_webhooks'] = 'courier_logistic/Fulfilment/delete_webhooks';
$route['admin/courier_logistic/Fulfilment/test_connection'] = 'courier_logistic/Fulfilment/test_connection';
$route['admin/courier_logistic/Fulfilment/ensure_virtual_warehouse_ajax'] = 'courier_logistic/Fulfilment/ensure_virtual_warehouse_ajax';
$route['admin/courier_logistic/Fulfilment/get_orders_datatable'] = 'courier_logistic/Fulfilment/get_orders_datatable';
$route['admin/courier_logistic/Fulfilment/get_order_detail/(:num)'] = 'courier_logistic/Fulfilment/get_order_detail/$1';
$route['admin/courier_logistic/Fulfilment/create_shipment/(:num)'] = 'courier_logistic/Fulfilment/create_shipment/$1';
$route['admin/courier_logistic/Fulfilment/get_inventory_datatable'] = 'courier_logistic/Fulfilment/get_inventory_datatable';
$route['admin/courier_logistic/Fulfilment/run_inventory_sync'] = 'courier_logistic/Fulfilment/run_inventory_sync';
$route['admin/courier_logistic/Fulfilment/get_product_mappings'] = 'courier_logistic/Fulfilment/get_product_mappings';
$route['admin/courier_logistic/Fulfilment/save_product_mapping'] = 'courier_logistic/Fulfilment/save_product_mapping';
$route['admin/courier_logistic/Fulfilment/delete_product_mapping/(:num)'] = 'courier_logistic/Fulfilment/delete_product_mapping/$1';
$route['admin/courier_logistic/Fulfilment/import_shopify_products'] = 'courier_logistic/Fulfilment/import_shopify_products';
$route['admin/courier_logistic/Fulfilment/sync_shopify_locations'] = 'courier_logistic/Fulfilment/sync_shopify_locations';
$route['admin/courier_logistic/Fulfilment/save_location_branch_map'] = 'courier_logistic/Fulfilment/save_location_branch_map';
$route['admin/courier_logistic/Fulfilment/get_webhook_events_datatable'] = 'courier_logistic/Fulfilment/get_webhook_events_datatable';
$route['admin/courier_logistic/Fulfilment/get_logs_datatable'] = 'courier_logistic/Fulfilment/get_logs_datatable';
$route['admin/courier_logistic/Fulfilment/requeue_webhook_event/(:num)'] = 'courier_logistic/Fulfilment/requeue_webhook_event/$1';
$route['admin/courier_logistic/Fulfilment/retry_all_failed_webhooks'] = 'courier_logistic/Fulfilment/retry_all_failed_webhooks';
$route['admin/courier_logistic/Fulfilment/clear_done_events'] = 'courier_logistic/Fulfilment/clear_done_events';
$route['admin/courier_logistic/Fulfilment/get_raw_data/(:any)/(:num)'] = 'courier_logistic/Fulfilment/get_raw_data/$1/$2';
$route['admin/courier_logistic/Fulfilment/clear_logs'] = 'courier_logistic/Fulfilment/clear_logs';
$route['admin/courier_logistic/Fulfilment/generate_test_log'] = 'courier_logistic/Fulfilment/generate_test_log';
$route['admin/courier_logistic/Fulfilment/export_logs_csv'] = 'courier_logistic/Fulfilment/export_logs_csv';
$route['admin/courier_logistic/Fulfilment/get_health_status'] = 'courier_logistic/Fulfilment/get_health_status';
$route['courier_logistic/tracking'] = 'courier_logistic/Tracker/tracking';
$route['courier_logistic/tracking/shipment_info'] = 'courier_logistic/Tracker/shipment_info';

/*
 * Client Portal — dedicated per-tab URLs (same page, tab pre-selected server-side)
 **/
$route['courier_logistic/track'] = 'courier_logistic/Tracker/tracking/track';
$route['courier_logistic/get-a-quote'] = 'courier_logistic/Tracker/tracking/quote';
$route['courier_logistic/schedule-delivery'] = 'courier_logistic/Tracker/tracking/pickup';
$route['courier_logistic/find-service-point'] = 'courier_logistic/Tracker/tracking/service';
$route['courier_logistic/create-shipment'] = 'courier_logistic/Tracker/tracking/shipment';
$route['courier_logistic/call-for-booking'] = 'courier_logistic/Tracker/tracking/call';

/*
 * Shipments Routes
 **/
$route['admin/courier_logistic/Shipments/main'] = 'courier_logistic/Shipments/main';
$route['admin/courier_logistic/Shipments/dashboard'] = 'courier_logistic/Shipments/dashboard';
$route['admin/courier_logistic/shipments'] = 'courier_logistic/Shipments/index';
$route['admin/courier_logistic/Shipments/create'] = 'courier_logistic/Shipments/create';
$route['admin/courier_logistic/Shipments/delete'] = 'courier_logistic/Shipments/delete';
$route['admin/courier_logistic/Shipments/store'] = 'courier_logistic/Shipments/store';
$route['admin/courier_logistic/Shipments/list_invoices'] = 'courier_logistic/Shipments/list_invoices';
$route['admin/courier_logistic/Shipments/list_commercial_invoices'] = 'courier_logistic/Shipments/list_commercial_invoices';
$route['admin/courier_logistic/Shipments/quotation/(:num)']        = 'courier_logistic/Shipments/quotation/$1';
$route['admin/courier_logistic/Shipments/courier_invoice/(:num)']           = 'courier_logistic/Shipments/courier_invoice/$1';
$route['admin/courier_logistic/Shipments/record_courier_payment/(:num)']    = 'courier_logistic/Shipments/record_courier_payment/$1';
$route['admin/courier_logistic/Shipments/consignment_note/(:num)'] = 'courier_logistic/Shipments/consignment_note/$1';
$route['admin/courier_logistic/Shipments/waybill/(:num)'] = 'courier_logistic/Shipments/waybill/$1';
$route['admin/courier_logistic/Shipments/send_waybill_email/(:num)'] = 'courier_logistic/Shipments/send_waybill_email/$1';
$route['admin/courier_logistic/Shipments/commercial_invoice/(:num)'] = 'courier_logistic/Shipments/commercial_invoice/$1';
$route['admin/courier_logistic/Shipments/update_status/(:num)'] = 'courier_logistic/Shipments/update_status/$1';
$route['admin/courier_logistic/Shipments/manifest'] = 'courier_logistic/Shipments/manifest';
$route['admin/courier_logistic/Shipments/generate_manifest'] = 'courier_logistic/Shipments/generate_manifest';
$route['admin/courier_logistic/Shipments/insert_manifest'] = 'courier_logistic/Shipments/insert_manifest';
$route['admin/courier_logistic/Shipments/filter_shipments'] = 'courier_logistic/Shipments/filter_shipments';
$route['admin/courier_logistic/Shipments/clear_filters'] = 'courier_logistic/Shipments/clear_filters';
$route['admin/courier_logistic/Shipments/portal_request_data/(:num)'] = 'courier_logistic/Shipments/portal_request_data/$1';
$route['admin/courier_logistic/Shipments/confirm_portal_request/(:num)'] = 'courier_logistic/Shipments/confirm_portal_request/$1';


/*
 * Pickups Routes
 **/
$route['admin/courier_logistic/Pickups/main'] = 'courier_logistic/Pickups/main';
$route['admin/courier_logistic/Pickups/dashboard'] = 'courier_logistic/Pickups/dashboard';
$route['admin/courier_logistic/pickups'] = 'courier_logistic/Pickups/index';
$route['admin/courier_logistic/Pickups/create'] = 'courier_logistic/Pickups/create';
$route['admin/courier_logistic/Pickups/store'] = 'courier_logistic/Pickups/store';
$route['admin/courier_logistic/Pickups/delete/(:num)'] = 'courier_logistic/Pickups/delete/$1';
$route['admin/courier_logistic/Pickups/bulk_delete']   = 'courier_logistic/Pickups/bulk_delete';
$route['admin/courier_logistic/Pickups/update_status'] = 'courier_logistic/Pickups/update_status';
$route['admin/courier_logistic/Pickups/view/(:num)'] = 'courier_logistic/Pickups/view/$1';



/*
 * Branches / Offices Routes
 **/
$route['admin/courier_logistic/branches'] = 'courier_logistic/Branches/main';
$route['admin/courier_logistic/Branches/main'] = 'courier_logistic/Branches/main';
$route['admin/courier_logistic/Branches/store'] = 'courier_logistic/Branches/store';
$route['admin/courier_logistic/Branches/update/(:num)'] = 'courier_logistic/Branches/update/$1';
$route['admin/courier_logistic/Branches/delete/(:num)'] = 'courier_logistic/Branches/delete/$1';

/*
 * Courier Companies Routes
 **/
$route['admin/courier_logistic/companies'] = 'courier_logistic/Companies/index';
$route['admin/courier_logistic/Companies/main'] = 'courier_logistic/Companies/main';
$route['admin/courier_logistic/Companies/dashboard'] = 'courier_logistic/Companies/dashboard';
$route['admin/courier_logistic/Companies/create'] = 'courier_logistic/Companies/create';
$route['admin/courier_logistic/Companies/store'] = 'courier_logistic/Companies/store';
$route['admin/courier_logistic/Companies/delete/(:num)'] = 'courier_logistic/Companies/delete/$1';



/*
 * Agents Routes
 **/
$route['admin/courier_logistic/Agents/main'] = 'courier_logistic/Agents/main';
$route['admin/courier_logistic/agents'] = 'courier_logistic/Agents/index';
$route['admin/courier_logistic/Agents/create'] = 'courier_logistic/Agents/create';
$route['admin/courier_logistic/Agents/store'] = 'courier_logistic/Agents/store';


/*
 * Manifests Routes
 **/
$route['admin/courier_logistic/manifests'] = 'courier_logistic/Manifests/index';
$route['admin/courier_logistic/Manifests/store'] = 'courier_logistic/Manifests/store';
$route['admin/courier_logistic/Manifests/view/(:num)'] = 'courier_logistic/Manifests/view/$i';



/*
 * Agents Routes
 **/
$route['admin/courier_logistic/Agents/main'] = 'courier_logistic/Agents/main';
$route['admin/courier_logistic/agents'] = 'courier_logistic/Agents/index';
$route['admin/courier_logistic/Agents/create'] = 'courier_logistic/Agents/create';
$route['admin/courier_logistic/Agents/store'] = 'courier_logistic/Agents/store';
$route['admin/courier_logistic/Agents/agent_number'] = 'courier_logistic/Agents/agent_number';
$route['admin/courier_logistic/Agents/update_status'] = 'courier_logistic/Agents/update_status';
$route['admin/courier_logistic/Agents/sync_role_permissions'] = 'courier_logistic/Agents/sync_role_permissions';
$route['admin/courier_logistic/Agents/delete/(:num)'] = 'courier_logistic/Agents/delete/$i';




/*
 * Settings Routes
 **/
$route['admin/courier_logistic/Settings/main'] =  'courier_logistic/Settings/main';
$route['admin/courier_logistic/Settings/dimensional_factor'] =  'courier_logistic/Settings/dimensional_factor';
$route['admin/courier_logistic/Settings/general'] =  'courier_logistic/Settings/general';

/*
 * Public Customer Portal Routes
 **/
$route['courier_logistic/portal'] = 'courier_logistic/Tracker/portal';
$route['courier_logistic/portal/quote'] = 'courier_logistic/Tracker/quote';
$route['courier_logistic/portal/schedule_pickup'] = 'courier_logistic/Tracker/schedule_pickup';
$route['courier_logistic/portal/store_pickup'] = 'courier_logistic/Tracker/store_pickup';
$route['courier_logistic/portal/service_points'] = 'courier_logistic/Tracker/service_points';
$route['courier_logistic/portal/create_shipment'] = 'courier_logistic/Tracker/public_create_shipment';
$route['courier_logistic/portal/store_shipment'] = 'courier_logistic/Tracker/store_public_shipment';
$route['courier_logistic/portal/tariff_zones'] = 'courier_logistic/Tracker/tariff_zones';
$route['courier_logistic/portal/calculate_quote'] = 'courier_logistic/Tracker/calculate_quote';
$route['courier_logistic/portal/send_quote_email'] = 'courier_logistic/Tracker/send_quote_email';
$route['courier_logistic/portal/get_countries'] = 'courier_logistic/Tracker/get_countries';
$route['courier_logistic/portal/get_cities'] = 'courier_logistic/Tracker/get_cities';
$route['courier_logistic/portal/get_domestic_cities'] = 'courier_logistic/Tracker/get_domestic_cities';

/*
 * Tariff Settings Routes
 **/
$route['admin/courier_logistic/Settings/tariff'] = 'courier_logistic/Settings/main';
$route['admin/courier_logistic/Settings/download_tariff_template'] = 'courier_logistic/Settings/download_tariff_template';
$route['admin/courier_logistic/Settings/upload_tariff_csv'] = 'courier_logistic/Settings/upload_tariff_csv';
$route['admin/courier_logistic/Settings/delete_tariff_rate/(:num)'] = 'courier_logistic/Settings/delete_tariff_rate/$1';
$route['admin/courier_logistic/Settings/update_tariff_zone'] = 'courier_logistic/Settings/update_tariff_zone';
$route['admin/courier_logistic/Settings/download_domestic_tariff_template'] = 'courier_logistic/Settings/download_domestic_tariff_template';
$route['admin/courier_logistic/Settings/upload_domestic_tariff_csv'] = 'courier_logistic/Settings/upload_domestic_tariff_csv';
$route['admin/courier_logistic/Settings/delete_domestic_tariff/(.+)'] = 'courier_logistic/Settings/delete_domestic_tariff/$1';
$route['admin/courier_logistic/Settings/delete_domestic_tariff_rate/(:num)'] = 'courier_logistic/Settings/delete_domestic_tariff_rate/$1';
$route['admin/courier_logistic/Settings/domestic_tariff_rates_json'] = 'courier_logistic/Settings/domestic_tariff_rates_json';

