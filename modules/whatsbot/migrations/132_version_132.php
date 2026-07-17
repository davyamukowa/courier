<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_132 extends App_module_migration {
    public function up() {
        if (table_exists('wtc_campaigns')) {
            if (!get_instance()->db->field_exists('rel_data', db_prefix() . 'wtc_campaigns')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `rel_data` TEXT NULL DEFAULT NULL;");
            }
        }

        if (table_exists('wtc_templates')) {
            if (get_instance()->db->field_exists('buttons_data', db_prefix() . 'wtc_templates')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_templates` CHANGE `buttons_data` `buttons_data` TEXT NOT NULL;");
            }
        }
    }

    public function down() {
        // Write your code here
    }
}
