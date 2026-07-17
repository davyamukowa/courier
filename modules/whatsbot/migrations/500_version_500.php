<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_500 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Catalog Products
        if (!$CI->db->table_exists(db_prefix() . 'wtc_product_metadata')) {
            $CI->db->query(
                'CREATE TABLE `' . db_prefix() . 'wtc_product_metadata` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `product_id` int NOT NULL,
                    `whatsapp_catalog_id` varchar(255) DEFAULT NULL,
                    `product_url` varchar(255) DEFAULT NULL,
                    `image_url` varchar(255) DEFAULT NULL,
                    `currency` varchar(10) DEFAULT NULL,
                    `pending_sync` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
            );
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_product_metadata')) {
            if (!$CI->db->field_exists('pending_sync', db_prefix() . 'wtc_product_metadata')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_product_metadata` ADD `pending_sync` TINYINT(1) NOT NULL DEFAULT 0;");
            }
        }

        if (!$CI->db->table_exists(db_prefix() . 'wtc_catalog_sync_logs')) {
            $CI->db->query(
                "CREATE TABLE `" . db_prefix() . "wtc_catalog_sync_logs` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `sync_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `direction` enum('perfex_to_whatsapp','whatsapp_to_perfex','bidirectional') NOT NULL,
                    `status` enum('success','partial','failed') NOT NULL DEFAULT 'success',
                    `items_processed` text NOT NULL,
                    `details` text,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

        if (!$CI->db->table_exists(db_prefix() . 'wtc_orders_response')) {
            $CI->db->query(
                "CREATE TABLE `" . db_prefix() . "wtc_orders_response` (
                        `id` INT NOT NULL AUTO_INCREMENT ,
                        `catalog_id` varchar(50) NOT NULL,
                        `user_message` text,
                        `cart_products` text,
                        `receiver_id` varchar(20) NOT NULL,
                        `name` varchar(100) NOT NULL,
                        `response_data` text,
                        `submit_time` timestamp NULL DEFAULT NULL,
                        `wa_no` varchar(20) DEFAULT NULL,
                        `wa_no_id` varchar(20) DEFAULT NULL,
                        `type` varchar(500) DEFAULT NULL,
                        `type_id` varchar(500) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

        // Catelog management options
        add_option('whatsbot_last_catalog_sync', 'Never');

        // Ad tracking columns on interactions
        if ($CI->db->table_exists(db_prefix() . 'wtc_interactions')) {
            if (!$CI->db->field_exists('ctwa_clid', db_prefix() . 'wtc_interactions')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `ctwa_clid` VARCHAR(255) NULL;");
            }
            if (!$CI->db->field_exists('referral_source', db_prefix() . 'wtc_interactions')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `referral_source` TEXT NULL;");
            }
            if (!$CI->db->field_exists('referral_headline', db_prefix() . 'wtc_interactions')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `referral_headline` VARCHAR(255) NULL;");
            }
        }

        // Frequently forwarded flag on messages
        if ($CI->db->table_exists(db_prefix() . 'wtc_interaction_messages')) {
            if (!$CI->db->field_exists('is_frequently_forwarded', db_prefix() . 'wtc_interaction_messages')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `is_frequently_forwarded` TINYINT(1) DEFAULT 0;");
            }
        }

        // New options
        if (!option_exists('enable_typing_indicator')) {
            add_option('enable_typing_indicator', '0');
        }
        if (!option_exists('wac_catalog_id')) {
            add_option('wac_catalog_id', '');
        }
        if (!option_exists('enable_business_hours')) {
            add_option('enable_business_hours', '0');
        }
        if (!option_exists('business_hours_start')) {
            add_option('business_hours_start', '09:00');
        }
        if (!option_exists('business_hours_end')) {
            add_option('business_hours_end', '18:00');
        }
        if (!option_exists('business_days')) {
            add_option('business_days', '["Monday","Tuesday","Wednesday","Thursday","Friday"]');
        }
        if (!option_exists('business_hours_message')) {
            add_option('business_hours_message', '');
        }

        // Agent routing
        if (!option_exists('wb_auto_routing_mode')) {
            add_option('wb_auto_routing_mode', 'off');
        }
        if (!option_exists('wb_routing_specific_staff')) {
            add_option('wb_routing_specific_staff', '');
        }
        if (!option_exists('wb_last_assigned_staff_index')) {
            add_option('wb_last_assigned_staff_index', '0');
        }

        // CRM event triggers
        if (!option_exists('wb_enable_crm_triggers')) {
            add_option('wb_enable_crm_triggers', '0');
        }

        // Drip exit on reply
        if (!option_exists('wb_drip_exit_on_reply')) {
            add_option('wb_drip_exit_on_reply', '1');
        }

        // Drip Sequences table
        if (!$CI->db->table_exists(db_prefix() . 'wtc_drip_sequences')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "wtc_drip_sequences` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `rel_type` VARCHAR(50) DEFAULT 'leads',
                `is_active` TINYINT(1) DEFAULT 1,
                `created_by` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        // Drip Steps table
        if (!$CI->db->table_exists(db_prefix() . 'wtc_drip_steps')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "wtc_drip_steps` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `sequence_id` INT NOT NULL,
                `step_number` INT NOT NULL DEFAULT 1,
                `delay_value` INT NOT NULL DEFAULT 1,
                `delay_unit` VARCHAR(20) DEFAULT 'hours',
                `message_type` VARCHAR(20) DEFAULT 'template',
                `template_id` INT NULL,
                `message_data` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `sequence_id` (`sequence_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        // Drip Enrollments table
        if (!$CI->db->table_exists(db_prefix() . 'wtc_drip_enrollments')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "wtc_drip_enrollments` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `sequence_id` INT NOT NULL,
                `rel_id` INT NOT NULL,
                `rel_type` VARCHAR(50) NOT NULL,
                `phone_number` VARCHAR(25) NULL,
                `current_step` INT DEFAULT 0,
                `status` VARCHAR(20) DEFAULT 'active',
                `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `last_step_sent_at` DATETIME NULL,
                `completed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `sequence_id` (`sequence_id`),
                KEY `status` (`status`),
                UNIQUE KEY `unique_enrollment` (`sequence_id`, `rel_id`, `rel_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        // Drip Execution Log table
        if (!$CI->db->table_exists(db_prefix() . 'wtc_drip_log')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "wtc_drip_log` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `enrollment_id` INT NOT NULL,
                `step_number` INT NOT NULL,
                `status` VARCHAR(20) DEFAULT 'pending',
                `sent_at` DATETIME NULL,
                `whatsapp_message_id` VARCHAR(500) NULL,
                `response_code` VARCHAR(10) NULL,
                `error_message` TEXT NULL,
                PRIMARY KEY (`id`),
                KEY `enrollment_id` (`enrollment_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
            if (!get_instance()->db->field_exists('contact_first_name', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `contact_first_name` varchar(255) DEFAULT NULL;");
            }
            if (!get_instance()->db->field_exists('contact_last_name', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `contact_last_name` varchar(255) DEFAULT NULL;");
            }
            if (!get_instance()->db->field_exists('contact_phone', db_prefix() . 'wtc_bot')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `contact_phone` varchar(50) DEFAULT NULL;");
            }
        }

        if ($CI->db->table_exists('wtc_campaigns')) {
            if (!get_instance()->db->field_exists('crm_events', db_prefix() . 'wtc_campaigns')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `crm_events` TEXT NULL;");
            }
        }

        // Drip sequence inhancement
        if (!option_exists('wb_drip_cron_batch_limit')) {
            add_option('wb_drip_cron_batch_limit', '50');
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_drip_sequences')) {
            if (!$CI->db->field_exists('sender_phone', db_prefix() . 'wtc_drip_sequences')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_sequences` ADD `sender_phone` VARCHAR(25) NULL AFTER `rel_type`;");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_drip_steps')) {
            $columns = [
                'header_params' => 'TEXT NULL',
                'body_params' => 'TEXT NULL',
                'footer_params' => 'TEXT NULL',
                'filename' => 'TEXT NULL',
            ];

            foreach ($columns as $column => $definition) {
                if (!$CI->db->field_exists($column, db_prefix() . 'wtc_drip_steps')) {
                    $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_steps` ADD `{$column}` {$definition};");
                }
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_drip_enrollments')) {
            $columns = [
                'next_send_at' => 'DATETIME NULL',
                'failure_count' => 'INT DEFAULT 0',
                'last_error' => 'TEXT NULL',
                'exit_reason' => 'VARCHAR(50) NULL',
                'processing_started_at' => 'DATETIME NULL',
            ];

            foreach ($columns as $column => $definition) {
                if (!$CI->db->field_exists($column, db_prefix() . 'wtc_drip_enrollments')) {
                    $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_enrollments` ADD `{$column}` {$definition};");
                }
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_campaigns')) {
            if (!$CI->db->field_exists('send_crm_event_pdf', db_prefix() . 'wtc_campaigns')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `send_crm_event_pdf` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bot_type`;");
            }
        }
    }

    public function down() {}
}
