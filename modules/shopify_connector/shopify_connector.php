<?php
/**
 * c:\wamp64\www\perfex_crm\modules\shopify_connector\shopify_connector.php
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Shopify Connector
Description: Integrates Shopify with Go Shipping courier, inventory, and accounting modules.
Version: 1.0.1
Requires at least: 2.3.*
*/

define('SHOPIFY_CONNECTOR_MODULE_NAME', 'shopify_connector');
define('SHOPIFY_CONNECTOR_VERSION', '1.0.1');
// sync test marker: 2026-07-17 12:30

// Register activation and deactivation hooks
register_activation_hook(SHOPIFY_CONNECTOR_MODULE_NAME, 'shopify_connector_activation_hook');
register_deactivation_hook(SHOPIFY_CONNECTOR_MODULE_NAME, 'shopify_connector_deactivation_hook');

// Add hooks for menu, permissions, and assets
hooks()->add_action('admin_init', 'shopify_connector_permissions');
hooks()->add_action('admin_init', 'shopify_connector_init_menu_items');
hooks()->add_action('app_admin_head', 'shopify_connector_add_head_components');
hooks()->add_action('app_admin_footer', 'shopify_connector_load_js');

/**
 * Activation hook
 */
function shopify_connector_activation_hook()
{
    $CI = &get_instance();
    // Run migrations (if any)
    require_once(__DIR__ . '/install.php');
}

/**
 * Deactivation hook
 */
function shopify_connector_deactivation_hook()
{
    // Rollback migrations or logic if needed
}

/**
 * Register module permissions
 */
function shopify_connector_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view_shopify_connector'   => 'View',
        'manage_shopify_connector' => 'Manage',
    ];

    register_staff_capabilities('shopify_connector', $capabilities, 'Shopify Connector');
}

/**
 * Initialize sidebar menu items
 */
function shopify_connector_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('shopify_connector', '', 'view_shopify_connector') || has_permission('shopify_connector', '', 'manage_shopify_connector')) {
        
        // Add parent 'Integrations'
        $CI->app_menu->add_sidebar_menu_item('integrations', [
            'name'     => 'Integrations',
            'collapse' => true,
            'position' => 45,
            'icon'     => 'fa fa-link',
        ]);

        $CI->app_menu->add_sidebar_children_item('integrations', [
            'slug'     => 'shopify-connector',
            'name'     => 'Shopify Connector',
            'href'     => admin_url('shopify_connector/dashboard'),
            'position' => 5,
            'icon'     => 'fa fa-shopping-bag'
        ]);

        // Add Settings child item
        $CI->app_menu->add_sidebar_children_item('integrations', [
            'slug'     => 'shopify-connector-settings',
            'name'     => 'Shopify Settings',
            'href'     => admin_url('shopify_connector/settings'),
            'position' => 6,
            'icon'     => 'fa fa-cog'
        ]);
    }
}

// Disable CSRF for webhook
hooks()->add_filter('csrf_exclude_uris', 'shopify_connector_csrf_exclude_uris');
function shopify_connector_csrf_exclude_uris($uris)
{
    $uris[] = '.*shopify_connector/webhook.*';
    return $uris;
}

/**
 * Enqueue CSS in the head
 */
function shopify_connector_add_head_components()
{
    echo '<link href="' . module_dir_url(SHOPIFY_CONNECTOR_MODULE_NAME, 'assets/css/shopify_connector.css') . '?v=' . SHOPIFY_CONNECTOR_VERSION . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
}

/**
 * Enqueue JS in the footer, after jQuery has loaded — the head fires before
 * jQuery is available, which broke every $(...) call in shopify_connector.js.
 */
function shopify_connector_load_js()
{
    echo '<script src="' . module_dir_url(SHOPIFY_CONNECTOR_MODULE_NAME, 'assets/js/shopify_connector.js') . '?v=' . SHOPIFY_CONNECTOR_VERSION . '"></script>' . PHP_EOL;
}
