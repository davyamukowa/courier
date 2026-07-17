<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_301 extends App_module_migration {
    public function up() {

        if (get_instance()->db->table_exists(db_prefix() . 'wtc_campaigns')) {
            if (!get_instance()->db->field_exists('sender_phone', db_prefix() . 'wtc_campaigns')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `sender_phone` VARCHAR(25) NULL DEFAULT NULL AFTER `name`;");
            }
        }

        if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
            if (!get_instance()->db->field_exists('option', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `option` tinyint DEFAULT '1';");
            }
            if (!get_instance()->db->field_exists('sections', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `sections` mediumtext NULL DEFAULT NULL;");
            }
        }

        $bots = get_instance()->db->where("option", 1)->get(db_prefix() . "wtc_bot")->result_array();
        foreach ($bots as $bot) {
            if (!empty($bot['button1_id']) || !empty($bot['button2_id']) || !empty($bot['button3_id'])) {
                $set['option'] = 2;
            } elseif (!empty($bot['button_name']) && !empty($bot['button_url']) && filter_var($bot['button_url'], \FILTER_VALIDATE_URL)) {
                $set['option'] = 3;
            } elseif (!empty($bot['filename'])) {
                $set['option'] = 4;
            } else {
                $set['option'] = 1;
            }
            get_instance()->db->update(db_prefix() . "wtc_bot", $set, ["id" => $bot['id']]);
        }
    }

    public function down() {
    }
}
