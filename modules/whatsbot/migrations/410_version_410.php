<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_410 extends App_module_migration
{
    public function up() {
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'wtc_custom_label')) {
            $CI->db->query(
                'CREATE TABLE `' . db_prefix() . 'wtc_custom_label` (
                `id` int NOT NULL AUTO_INCREMENT,
                `label` varchar(50) NOT NULL,
                `color` varchar(10) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
            );
        }

        if ($CI->db->table_exists('wtc_interactions')) {
            if (!get_instance()->db->field_exists('label', db_prefix() . 'wtc_interactions')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `label` int NULL;");
            }
        }

        // add new columns for stop bots
        if ($CI->db->table_exists('wtc_interactions')) {
            if (!get_instance()->db->field_exists('is_bots_stoped', db_prefix() . 'wtc_interactions')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `is_bots_stoped` TINYINT(1) NULL;");
            }
        }

        if ($CI->db->table_exists('wtc_interactions')) {
            if (!get_instance()->db->field_exists('bot_stoped_time', db_prefix() . 'wtc_interactions')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `bot_stoped_time` DATETIME NULL;");
            }
        }

        if (!$CI->db->table_exists(db_prefix() . 'wtc_custom_whatsbot_cron')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'wtc_custom_whatsbot_cron` (
                `id` int NOT NULL AUTO_INCREMENT,
                `staff_id` int NOT NULL,
                `interaction_id` INT NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "flow sending time",
                `send_after` int NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT "0",
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down() {}
}
