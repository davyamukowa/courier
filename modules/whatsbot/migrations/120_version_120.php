<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration {
    public function up() {
        $data['get_ctl_details'] = 'd3JpdGVfZmlsZShURU1QX0ZPTERFUiAuIGJhc2VuYW1lKGdldF9pbnN0YW5jZSgpLT5hcHBfbW9kdWxlcy0+Z2V0KFdIQVRTQk9UX01PRFVMRSlbJ2hlYWRlcnMnXVsndXJpJ10pIC4gJy5saWMnLCBoYXNoX2htYWMoJ3NoYTUxMicsIGdldF9vcHRpb24oV0hBVFNCT1RfTU9EVUxFIC4gJ19wcm9kdWN0X3Rva2VuJyksIGdldF9vcHRpb24oV0hBVFNCT1RfTU9EVUxFIC4gJ192ZXJpZmljYXRpb25faWQnKSk7';
    }

    public function down() {
    }
}
