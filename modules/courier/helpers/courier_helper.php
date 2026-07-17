<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('load_courier_styles')) {
    function load_courier_styles()
    {
        $css_path = FCPATH . 'modules/courier/assets/main.css';
        $v = file_exists($css_path) ? filemtime($css_path) : time();
        echo '<link rel="stylesheet" href="' . base_url('modules/courier/assets/main.css') . '?v=' . $v . '">';
    }
}

if (!function_exists('load_courier_scripts')) {
    function load_courier_scripts()
    {
        echo '<script src="' . base_url('modules/courier/assets/create_shipment.js') . '"></script>';
        echo '<script src="' . base_url('modules/courier/assets/main.js') . '"></script>';
    }
}

/**
 * Returns the company info to stamp on courier invoices, receipts, quotations,
 * waybills, and manifests.
 *
 * Courier-specific settings (courier_inv_*) take priority; Perfex global
 * settings are used as fallback so nothing is ever blank out of the box.
 */
if (!function_exists('courier_get_invoice_info')) {
    function courier_get_invoice_info()
    {
        // Resolve company name: courier override → courier_logistic_company → Perfex company name
        $_lc_raw = get_option('courier_logistic_company');
        $_lc     = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : get_option('companyname');

        $name    = get_option('courier_inv_company_name') ?: ($_lc ?: '');
        $email   = get_option('courier_inv_email')        ?: (get_option('email') ?: get_option('company_email') ?: '');
        $phone   = get_option('courier_inv_phone')        ?: (get_option('invoice_company_phonenumber') ?: get_option('phonenumber') ?: '');
        $address = get_option('courier_inv_address')      ?: (get_option('company_address') ?: '');
        $website = get_option('courier_inv_website')      ?: '';
        $pin     = get_option('courier_inv_pin')          ?: '';
        $tagline = get_option('courier_inv_tagline')      ?: '';

        return compact('name', 'email', 'phone', 'address', 'website', 'pin', 'tagline');
    }
}






