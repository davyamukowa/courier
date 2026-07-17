<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Public courier portal endpoints excluded from CSRF verification.
 * This file is loaded by InitModules during the pre_system hook,
 * before App_Security::csrf_verify() runs, so these exclusions take effect.
 * (The hook-based exclusion in courier.php fires too late — after CSRF runs.)
 */
return [
    'courier/tracking/shipment_info',
    'courier/portal',
    'courier/portal/quote',
    'courier/portal/schedule_pickup',
    'courier/portal/store_pickup',
    'courier/portal/service_points',
    'courier/portal/create_shipment',
    'courier/portal/store_shipment',
    'courier/portal/tariff_zones',
    'courier/portal/calculate_quote',
    'courier/portal/send_quote_email',
    'courier/portal/get_countries',
    'courier/portal/get_cities',
];
