<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Xetuu HR Enterprise Module
Description: Comprehensive HR Management — Employees, Recruitment, Attendance,
             Performance, Expenses, Tenure + HR Consultancy / Employer-of-Record mode.
Version: 1.0
Requires at least: 2.3.*
*/

define('XETUU_HR_MODULE_NAME', 'xetuu_hr');
define('XETUU_HR_VERSION', '2.4.1');

// ── Activation / Deactivation / Uninstall hooks ──────────────────────────────

register_activation_hook(XETUU_HR_MODULE_NAME, 'xetuu_hr_activate');
function xetuu_hr_activate()
{
    require_once(__DIR__ . '/install.php');
}

register_deactivation_hook(XETUU_HR_MODULE_NAME, 'xetuu_hr_deactivate');
function xetuu_hr_deactivate() {}

register_uninstall_hook(XETUU_HR_MODULE_NAME, 'xetuu_hr_uninstall');
function xetuu_hr_uninstall() {}

// ── Language files ────────────────────────────────────────────────────────────

register_language_files(XETUU_HR_MODULE_NAME, [XETUU_HR_MODULE_NAME]);

// ── Admin init: sidebar + run DB migrations on every HR page load ────────────

hooks()->add_action('admin_init', 'xetuu_hr_init_sidebar');

function xetuu_hr_init_sidebar()
{
    $CI = &get_instance();

    // Run DB column migrations whenever we're on an HR page
    if ($CI->uri->segment(2) === 'xetuu_hr') {
        require_once(__DIR__ . '/install.php');
        xetuu_hr_bootstrap_payroll_addons();
    }

    // ONE item on the Perfex sidebar — "Xetuu HR"
    $CI->app_menu->add_sidebar_menu_item('xetuu_hr', [
        'name'     => 'Xetuu HR',
        'href'     => admin_url('xetuu_hr'),
        'position' => 14,
        'icon'     => 'fa fa-users',
    ]);
}

// ── Head hook: inject assets only on Xetuu HR pages ─────────────────────────

hooks()->add_action('app_admin_head', 'xetuu_hr_head_assets');

function xetuu_hr_head_assets()
{
    $CI = &get_instance();

    // Only apply when the controller segment matches
    if ($CI->uri->segment(2) !== 'xetuu_hr') {
        return;
    }

    $css_url = module_dir_url(XETUU_HR_MODULE_NAME, 'assets/css/xetuu_hr.css');
    $gfonts  = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap';
    $micons  = 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap';

    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="stylesheet" href="' . $gfonts . '">';
    echo '<link rel="stylesheet" href="' . $micons . '">';
    echo '<link rel="stylesheet" href="' . $css_url . '?v=' . XETUU_HR_VERSION . '">';
}

// ── Footer hook: inject JS only on Xetuu HR pages ────────────────────────────

hooks()->add_action('app_admin_footer', 'xetuu_hr_footer_assets');

function xetuu_hr_footer_assets()
{
    $CI = &get_instance();

    if ($CI->uri->segment(2) !== 'xetuu_hr') {
        return;
    }

    $js_url = module_dir_url(XETUU_HR_MODULE_NAME, 'assets/js/xetuu_hr.js');
    echo '<script src="' . $js_url . '?v=' . XETUU_HR_VERSION . '"></script>';
}

// ── Payroll addon bootstrap: load active PHP addons so their hooks register ──
//
// Each installed PHP addon lives in its own folder with a manifest.json describing
// its main class file. On every HR page load we include the active addons and call
// their static register() method, which wires up the payroll_* hooks/filters.

function xetuu_hr_bootstrap_payroll_addons()
{
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $CI = &get_instance();
    $p  = db_prefix();

    if (!$CI->db->table_exists($p . 'hr_payroll_addons')) return;

    $addons = $CI->db->where('status', 'active')
                     ->where('addon_type', 'php')
                     ->get($p . 'hr_payroll_addons')->result();

    foreach ($addons as $addon) {
        $manifest = json_decode($addon->manifest, true);
        if (!$manifest || empty($manifest['main']) || empty($manifest['class'])) continue;

        $main_file = rtrim($addon->file_path, '/\\') . DIRECTORY_SEPARATOR . $manifest['main'];
        if (!file_exists($main_file)) continue;

        require_once $main_file;
        $class = $manifest['class'];
        if (class_exists($class) && method_exists($class, 'register')) {
            $class::register($addon);
        }
    }
}

// ── Client init: add Careers menu tab to public portal ─────────────────────────

hooks()->add_action('clients_init', 'xetuu_hr_init_clients_menu');

function xetuu_hr_init_clients_menu()
{
    add_theme_menu_item('careers', [
        'name'     => 'Careers',
        'href'     => site_url('xetuu_hr/jobs'),
        'position' => 6,
    ]);
}
