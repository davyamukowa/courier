<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_142 extends App_module_migration {
    public function up() {
        update_option('whatsapp_auto_lead_settings', 1, 0);
        update_option('enable_wtc_notification_sound', 1, 0);
    }

    public function down() {
    }
}
