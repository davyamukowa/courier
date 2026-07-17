<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Custom SMS Gateway Module
Description: Integrate any SMS provider with Perfex CRM, to send out SMS notifications
Version: 1.0.0
Requires at least: 3.0.0
Module URI: https://codecanyon.net/item/custom-sms-gateway-module-for-perfex-crm/53352209
*/

// Constants
define('CUSTOM_SMS_GATEWAY_MODULE_NAME', 'custom_sms_gateway');
modules\custom_sms_gateway\core\Apiinit::the_da_vinci_code(CUSTOM_SMS_GATEWAY_MODULE_NAME);
modules\custom_sms_gateway\core\Apiinit::ease_of_mind(CUSTOM_SMS_GATEWAY_MODULE_NAME);
register_language_files(CUSTOM_SMS_GATEWAY_MODULE_NAME, [CUSTOM_SMS_GATEWAY_MODULE_NAME]);

// Hooks
hooks()->add_filter('sms_gateways', 'custom_sms_gateways');

// Functions
function custom_sms_gateways($gateways)
{
    $gateways[] = 'custom_sms_gateway/Sms_custom_sms_gateway';
    return $gateways;
}

hooks()->add_action('app_init', CUSTOM_SMS_GATEWAY_MODULE_NAME.'_actLib');
function custom_sms_gateway_actLib()
{
    $CI = &get_instance();
    $CI->load->library(CUSTOM_SMS_GATEWAY_MODULE_NAME.'/Custom_sms_gateway_aeiou');
    $envato_res = $CI->custom_sms_gateway_aeiou->validatePurchase(CUSTOM_SMS_GATEWAY_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', CUSTOM_SMS_GATEWAY_MODULE_NAME.'_sidecheck');
function custom_sms_gateway_sidecheck($module_name)
{
    if (CUSTOM_SMS_GATEWAY_MODULE_NAME == $module_name['system_name']) {
        modules\custom_sms_gateway\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', CUSTOM_SMS_GATEWAY_MODULE_NAME.'_deregister');
function custom_sms_gateway_deregister($module_name)
{
    if (CUSTOM_SMS_GATEWAY_MODULE_NAME == $module_name['system_name']) {
        delete_option(CUSTOM_SMS_GATEWAY_MODULE_NAME.'_verification_id');
        delete_option(CUSTOM_SMS_GATEWAY_MODULE_NAME.'_last_verification');
        delete_option(CUSTOM_SMS_GATEWAY_MODULE_NAME.'_product_token');
        delete_option(CUSTOM_SMS_GATEWAY_MODULE_NAME.'_heartbeat');
    }
}