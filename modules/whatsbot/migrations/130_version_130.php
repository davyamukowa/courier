<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration {
    public function up() {
        _maybe_create_upload_path('uploads/whatsbot/csv');

        if (table_exists('wtc_interaction_messages')) {
            if (!get_instance()->db->field_exists('ref_message_id', db_prefix() . 'wtc_interaction_messages')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `ref_message_id` TEXT NULL;");
            }
        }

        if (!get_instance()->db->table_exists(db_prefix() . 'wtc_canned_reply')) {
            get_instance()->db->query(
                'CREATE TABLE `' . db_prefix() . 'wtc_canned_reply` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    `is_public` tinyint(1) NOT NULL DEFAULT "0",
                    `added_from` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . get_instance()->db->char_set . ';'
            );
        }

        if (!get_instance()->db->table_exists(db_prefix() . 'wtc_ai_prompts')) {
            get_instance()->db->query(
                'CREATE TABLE `' . db_prefix() . 'wtc_ai_prompts` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `action` text NOT NULL,
                    `added_from` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . get_instance()->db->char_set . ';'
            );
        }

        if (!get_instance()->db->table_exists(db_prefix() . 'wtc_bot_flow')) {
            get_instance()->db->query(
                'CREATE TABLE `' . db_prefix() . 'wtc_bot_flow` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `flow_name` varchar(50) NOT NULL,
                    `flow_data` longtext NOT NULL,
                    `is_active` tinyint(1) DEFAULT "1",
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . get_instance()->db->char_set . ';'
            );
        }
    }

    public function down() {
    }
}
