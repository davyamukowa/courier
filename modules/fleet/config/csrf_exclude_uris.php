<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Public, token-authenticated driver endpoints — the driver's phone posts
 * here from their own tracker page (no staff login, so no CSRF token
 * available).
 */
return [
    'admin/fleet/trips/record_location(.*)',
    'admin/fleet/trips/driver_start_trip(.*)',
    'admin/fleet/trips/driver_deliver_shipment(.*)',
    'admin/fleet/trips/driver_cancel_shipment(.*)',
];
