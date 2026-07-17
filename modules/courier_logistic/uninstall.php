<?php

// uninstall.php

defined('BASEPATH') or exit('No direct script access allowed');

// Declare $CI as global
global $CI;
$CI =& get_instance();

// Disable foreign key checks to prevent DROP TABLE constraint issues
$CI->db->query('SET FOREIGN_KEY_CHECKS = 0;');

// Drop tables if they exist
$tables_to_drop = [
    'shipment_packages',
    'shipment_fcl_packages',
    'commercial_values_items',
    'pickups',
    'pickup_contacts',
    'contact_persons',
    'third_party_shipments',
    'shipment_status_history',
    'agents',
    'shipment_stops',
    'deliveries',
    'shipments',
    'shipment_companies',
    'shipment_recipients',
    'shipment_senders',
    'shipment_statuses',
    'courier_companies',
    'dimensional_factor',
    'manifests',
    'country_states',
    'manifest_period',
    'courier_audit_logs',
    'destination_offices',
    'courier_routes',
    'courier_route_stops',
    'courier_service_points',
    'courier_tariff_zones',
    'courier_tariff_rates',
    'courier_origin_tariffs',
    'courier_domestic_tariffs',
    'courier_branches',
    'courier_staff_branches',
    'courier_shopify_locations',
    'courier_quotations',
    'courier_staff_countries',
];

foreach ($tables_to_drop as $table) {
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . '_' . $table . '`;');
}

// Re-enable foreign key checks
$CI->db->query('SET FOREIGN_KEY_CHECKS = 1;');
