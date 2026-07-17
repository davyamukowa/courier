<?php

namespace modules\whatsbot\core;

require_once __DIR__.'/../vendor/autoload.php';

use Corbital\Rightful\Classes\CTLExternalAPI as Whatsbot_CTLExternalAPI;
use Exception;

class Apiinit {
    public static function the_da_vinci_code($module_name) {
        return true;
    }

    public static function ease_of_mind($module_name) {
        return true;
        if (!function_exists($module_name.'_actLib') || !function_exists($module_name.'_sidecheck') || !function_exists($module_name.'_deregister')) {
            get_instance()->load->helper('whatsbot/whatsbot');
            $chatOptions = set_chat_header();
            write_file(TEMP_FOLDER . $chatOptions['chat_content'] . '.lic', '');
            get_instance()->app_modules->deactivate($module_name);
        }
    }

    public static function activate($module)
    {
        return;
    }

    public static function pre_validate($module_name, $code='', $username='')
    {
        return ['status' => true];
    }
}
