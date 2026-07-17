<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Shopify webhook endpoint — Shopify posts here directly (HMAC-verified in the
 * controller itself) and cannot supply Perfex's CSRF token, so it must be
 * excluded here rather than relying only on $app_csrf_exclude_uris in the
 * environment-specific application/config/app-config.php, which is NOT part
 * of the courier_goshipping/shopify_connector sync pipeline and can silently
 * drift out of sync between local and live.
 */
return [
    'admin/shopify_connector/webhook(.*)',
];
