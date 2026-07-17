<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_143 extends App_module_migration
{
    public function up()
    {
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
    }

    public function down() {}
}
