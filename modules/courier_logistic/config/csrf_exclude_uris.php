<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Public courier portal endpoints excluded from CSRF verification.
 * This file is loaded by InitModules during the pre_system hook,
 * before App_Security::csrf_verify() runs, so these exclusions take effect.
 * (The hook-based exclusion in courier.php fires too late — after CSRF runs.)
 */
return [
    'courier_logistic/tracking/shipment_info',
    'courier_logistic/portal',
    'courier_logistic/portal/quote',
    'courier_logistic/portal/schedule_pickup',
    'courier_logistic/portal/store_pickup',
    'courier_logistic/portal/service_points',
    'courier_logistic/portal/create_shipment',
    'courier_logistic/portal/store_shipment',
    'courier_logistic/portal/tariff_zones',
    'courier_logistic/portal/calculate_quote',
    'courier_logistic/portal/send_quote_email',
    'courier_logistic/portal/get_countries',
    'courier_logistic/portal/get_cities',
];

