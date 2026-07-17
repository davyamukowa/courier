<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Driver GPS ping endpoint — the driver's phone posts here from a public,
 * token-authenticated page (no staff login, so no CSRF token available).
 */
return [
    'admin/fleet/trips/record_location(.*)',
];
