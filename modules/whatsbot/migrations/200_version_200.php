<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration {
    public function up() {
        if (get_instance()->db->table_exists('wtc_interactions')) {
            if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interactions')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
            }
            if (!get_instance()->db->field_exists('ai_message_json', db_prefix() . 'wtc_interactions')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `ai_message_json` TEXT NULL DEFAULT NULL ;");
            }
        }

        if (get_instance()->db->table_exists('wtc_interaction_messages')) {
            if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interaction_messages')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
            }
        }

        if (!get_instance()->db->table_exists(db_prefix() . 'wtc_personal_assistants')) {
            get_instance()->db->query('CREATE TABLE `' . db_prefix() . 'wtc_personal_assistants` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . get_instance()->db->char_set . ';');
        }

        if (!get_instance()->db->table_exists(db_prefix() . 'wtc_pa_files')) {
            get_instance()->db->query('CREATE TABLE `' . db_prefix() . 'wtc_pa_files` (
                `id` int NOT NULL AUTO_INCREMENT,
                `pa_id` int NOT NULL,
                `file_name` text NOT NULL,
                `filetype` text NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . get_instance()->db->char_set . ';');
        }

        if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
            if (!get_instance()->db->field_exists('personal_assistants', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `personal_assistants` INT NULL DEFAULT NULL;");
            }
        }

        add_option('pa_temperature', 0.5, 0);
        add_option('pa_max_token', 200, 0);
    }

    public function down() {
    }
}
