<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_310 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        // Session management
        // 1. Add a new column to the wtc_interactions table to identify system messages
        if ($CI->db->table_exists(db_prefix() . 'wtc_interactions')) {
            if (!$CI->db->field_exists('session_reset_sent', db_prefix() . 'wtc_interactions')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `session_reset_sent` TINYINT(1) NOT NULL DEFAULT '0';");
            }
        }
        // 2. Add a new column to the wtc_interaction_messages table to identify system messages
        if ($CI->db->table_exists(db_prefix() . 'wtc_interaction_messages')) {
            if (!$CI->db->field_exists('is_system', db_prefix() . 'wtc_interaction_messages')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `is_system` TINYINT(1) NOT NULL DEFAULT '0';");
            }
        }

        add_option('last_whatsbot_cron_run');
        add_option('enable_session_management', 0, 0);
        add_option('session_expiry_message', "Hi there! Just a quick reminder — this chat session will expire soon. If you have any questions or need further assistance, feel free to reply now. We're here to help!", 0);
        add_option('session_expiry_hours', 23, 0);
    }

    public function down() {}
}
