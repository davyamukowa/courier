<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * General Routes
 **/
$route['admin/courier/dashboard'] = 'courier/dashboard';
$route['admin/courier/states'] = 'courier/states';
$route['courier/tracking'] = 'tracker/tracking';
$route['courier/tracking/shipment_info'] = 'tracker/shipment_info';

/*
 * Client Portal — dedicated per-tab URLs (same page, tab pre-selected server-side)
 **/
$route['courier/track'] = 'tracker/tracking/track';
$route['courier/get-a-quote'] = 'tracker/tracking/quote';
$route['courier/schedule-delivery'] = 'tracker/tracking/pickup';
$route['courier/find-service-point'] = 'tracker/tracking/service';
$route['courier/create-shipment'] = 'tracker/tracking/shipment';
$route['courier/call-for-booking'] = 'tracker/tracking/call';

/*
 * Shipments Routes
 **/
$route['admin/courier/shipments/main'] = 'courier/shipments/main';
$route['admin/courier/shipments/dashboard'] = 'courier/shipments/dashboard';
$route['admin/courier/shipments'] = 'courier/shipments/index';
$route['admin/courier/shipments/create'] = 'courier/shipments/create';
$route['admin/courier/shipments/delete'] = 'courier/shipments/delete';
$route['admin/courier/shipments/store'] = 'courier/shipments/store';
$route['admin/courier/shipments/list_invoices'] = 'courier/shipments/list_invoices';
$route['admin/courier/shipments/list_commercial_invoices'] = 'courier/shipments/list_commercial_invoices';
$route['admin/courier/shipments/quotation/(:num)']        = 'courier/shipments/quotation/$1';
$route['admin/courier/shipments/courier_invoice/(:num)']           = 'courier/shipments/courier_invoice/$1';
$route['admin/courier/shipments/record_courier_payment/(:num)']    = 'courier/shipments/record_courier_payment/$1';
$route['admin/courier/shipments/consignment_note/(:num)'] = 'courier/shipments/consignment_note/$1';
$route['admin/courier/shipments/waybill/(:num)'] = 'courier/shipments/waybill/$1';
$route['admin/courier/shipments/send_waybill_email/(:num)'] = 'courier/shipments/send_waybill_email/$1';
$route['admin/courier/shipments/commercial_invoice/(:num)'] = 'courier/shipments/commercial_invoice/$1';
$route['admin/courier/shipments/update_status/(:num)'] = 'courier/shipments/update_status/$1';
$route['admin/courier/shipments/manifest'] = 'courier/shipments/manifest';
$route['admin/courier/shipments/generate_manifest'] = 'courier/shipments/generate_manifest';
$route['admin/courier/shipments/insert_manifest'] = 'courier/shipments/insert_manifest';
$route['admin/courier/shipments/filter_shipments'] = 'courier/shipments/filter_shipments';
$route['admin/courier/shipments/clear_filters'] = 'courier/shipments/clear_filters';
$route['admin/courier/shipments/portal_request_data/(:num)'] = 'courier/shipments/portal_request_data/$1';
$route['admin/courier/shipments/confirm_portal_request/(:num)'] = 'courier/shipments/confirm_portal_request/$1';


/*
 * Pickups Routes
 **/
$route['admin/courier/pickups/main'] = 'courier/pickups/main';
$route['admin/courier/pickups/dashboard'] = 'courier/pickups/dashboard';
$route['admin/courier/pickups'] = 'courier/pickups/index';
$route['admin/courier/pickups/create'] = 'courier/pickups/create';
$route['admin/courier/pickups/store'] = 'courier/pickups/store';
$route['admin/courier/pickups/delete/(:num)'] = 'courier/pickups/delete/$1';
$route['admin/courier/pickups/bulk_delete']   = 'courier/pickups/bulk_delete';
$route['admin/courier/pickups/update_status'] = 'courier/pickups/update_status';
$route['admin/courier/pickups/view/(:num)'] = 'courier/pickups/view/$1';



/*
 * Courier Companies Routes
 **/
$route['admin/courier/companies'] = 'courier/companies/index';
$route['admin/courier/companies/main'] = 'courier/companies/main';
$route['admin/courier/companies/dashboard'] = 'courier/companies/dashboard';
$route['admin/courier/companies/create'] = 'courier/companies/create';
$route['admin/courier/companies/store'] = 'courier/companies/store';
$route['admin/courier/companies/delete/(:num)'] = 'courier/companies/delete/$1';



/*
 * Agents Routes
 **/
$route['admin/courier/agents/main'] = 'courier/agents/main';
$route['admin/courier/agents'] = 'courier/agents/index';
$route['admin/courier/agents/create'] = 'courier/agents/create';
$route['admin/courier/agents/store'] = 'courier/agents/store';


/*
 * Manifests Routes
 **/
$route['admin/courier/manifests'] = 'courier/manifests/index';
$route['admin/courier/manifests/store'] = 'courier/manifests/store';
$route['admin/courier/manifests/view/(:num)'] = 'courier/manifests/view/$i';



/*
 * Agents Routes
 **/
$route['admin/courier/agents/main'] = 'courier/agents/main';
$route['admin/courier/agents'] = 'courier/agents/index';
$route['admin/courier/agents/create'] = 'courier/agents/create';
$route['admin/courier/agents/store'] = 'courier/agents/store';
$route['admin/courier/agents/agent_number'] = 'courier/agents/agent_number';
$route['admin/courier/agents/update_status'] = 'courier/agents/update_status';
$route['admin/courier/agents/sync_role_permissions'] = 'courier/agents/sync_role_permissions';
$route['admin/courier/agents/delete/(:num)'] = 'courier/agents/delete/$i';




/*
 * Settings Routes
 **/
$route['admin/courier/settings/main'] =  'courier/settings/main';
$route['admin/courier/settings/dimensional_factor'] =  'courier/settings/dimensional_factor';
$route['admin/courier/settings/general'] =  'courier/settings/general';

/*
 * Public Customer Portal Routes
 **/
$route['courier/portal'] = 'tracker/portal';
$route['courier/portal/quote'] = 'tracker/quote';
$route['courier/portal/schedule_pickup'] = 'tracker/schedule_pickup';
$route['courier/portal/store_pickup'] = 'tracker/store_pickup';
$route['courier/portal/service_points'] = 'tracker/service_points';
$route['courier/portal/create_shipment'] = 'tracker/public_create_shipment';
$route['courier/portal/store_shipment'] = 'tracker/store_public_shipment';
$route['courier/portal/tariff_zones'] = 'tracker/tariff_zones';
$route['courier/portal/calculate_quote'] = 'tracker/calculate_quote';
$route['courier/portal/send_quote_email'] = 'tracker/send_quote_email';
$route['courier/portal/get_countries'] = 'tracker/get_countries';
$route['courier/portal/get_cities'] = 'tracker/get_cities';
$route['courier/portal/get_domestic_cities'] = 'tracker/get_domestic_cities';

/*
 * Tariff Settings Routes
 **/
$route['admin/courier/settings/tariff'] = 'courier/settings/main';
$route['admin/courier/settings/download_tariff_template'] = 'courier/settings/download_tariff_template';
$route['admin/courier/settings/upload_tariff_csv'] = 'courier/settings/upload_tariff_csv';
$route['admin/courier/settings/delete_tariff_rate/(:num)'] = 'courier/settings/delete_tariff_rate/$1';
$route['admin/courier/settings/update_tariff_zone'] = 'courier/settings/update_tariff_zone';
$route['admin/courier/settings/download_domestic_tariff_template'] = 'courier/settings/download_domestic_tariff_template';
$route['admin/courier/settings/upload_domestic_tariff_csv'] = 'courier/settings/upload_domestic_tariff_csv';
$route['admin/courier/settings/delete_domestic_tariff/(.+)'] = 'courier/settings/delete_domestic_tariff/$1';
$route['admin/courier/settings/delete_domestic_tariff_rate/(:num)'] = 'courier/settings/delete_domestic_tariff_rate/$1';
$route['admin/courier/settings/domestic_tariff_rates_json'] = 'courier/settings/domestic_tariff_rates_json';
