<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Sidebar Enhance
Description: Sticky sidebar + keeps active module expanded. No core files modified.
Version: 2.3.0
Requires at least: 2.3.*
*/

define('SIDEBAR_ENHANCE_MODULE_NAME', 'sidebar_enhance');

// Inject CSS into <head>
hooks()->add_action('app_admin_head', 'sidebar_enhance_head');
function sidebar_enhance_head()
{
    $v   = '2.3.0';
    $url = base_url('modules/sidebar_enhance/assets/');
    echo '<link rel="stylesheet" href="' . $url . 'sidebar_enhance.css?v=' . $v . '">' . "\n";
}

// Inject JS after all Perfex core scripts (main.js, metisMenu)
hooks()->add_action('app_admin_footer', 'sidebar_enhance_footer');
function sidebar_enhance_footer()
{
    $v   = '2.3.0';
    $url = base_url('modules/sidebar_enhance/assets/');
    echo '<script src="' . $url . 'sidebar_enhance.js?v=' . $v . '"></script>' . "\n";
}
