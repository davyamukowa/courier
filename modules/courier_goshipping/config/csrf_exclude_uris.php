<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Public courier portal endpoints excluded from CSRF verification.
 * This file is loaded by InitModules during the pre_system hook,
 * before App_Security::csrf_verify() runs, so these exclusions take effect.
 * (The hook-based exclusion in courier.php fires too late — after CSRF runs.)
 */
return [
    'courier_goshipping/tracking/shipment_info',
    'courier_goshipping/portal',
    'courier_goshipping/portal/quote',
    'courier_goshipping/portal/schedule_pickup',
    'courier_goshipping/portal/store_pickup',
    'courier_goshipping/portal/service_points',
    'courier_goshipping/portal/create_shipment',
    'courier_goshipping/portal/store_shipment',
    'courier_goshipping/portal/tariff_zones',
    'courier_goshipping/portal/calculate_quote',
    'courier_goshipping/portal/send_quote_email',
    'courier_goshipping/portal/get_countries',
    'courier_goshipping/portal/get_cities',

    // Rider PWA JSON API — plain fetch() calls from a no-login mobile app,
    // so there's no staff session/CSRF cookie to send a token with.
    'courier_goshipping/rider-api/(.*)',

    // Salibay short driver delivery flow (public, per-shipment token link).
    'admin/courier_goshipping/salibay_delivery/(.*)',
];
