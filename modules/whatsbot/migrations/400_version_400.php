<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_400 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        // Adding custom labels for chat
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
        if ($CI->db->table_exists(db_prefix() . 'contacts')) {
            if (!$CI->db->field_exists('is_opted_out', db_prefix() . 'contacts')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "contacts` ADD `is_opted_out` TINYINT(1) DEFAULT '0';");
            }
            if (!$CI->db->field_exists('opted_out_date', db_prefix() . 'contacts')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "contacts` ADD `opted_out_date` datetime DEFAULT NULL;");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'leads')) {
            if (!$CI->db->field_exists('is_opted_out', db_prefix() . 'leads')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "leads` ADD `is_opted_out` TINYINT(1) DEFAULT '0';");
            }
            if (!$CI->db->field_exists('opted_out_date', db_prefix() . 'leads')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "leads` ADD `opted_out_date` datetime DEFAULT NULL;");
            }
        }
        if (!$CI->db->table_exists(db_prefix() . 'wtc_receive_webhook_source')) {
            $CI->db->query(
                "CREATE TABLE `" . db_prefix() . "wtc_receive_webhook_source` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `secret` text NOT NULL,
                `hash` varchar(32) NOT NULL,
                `response_json` longtext NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

        if (!$CI->db->table_exists(db_prefix() . 'wtc_webhook_logs')) {
            $CI->db->query(
                "CREATE TABLE `" . db_prefix() . "wtc_webhook_logs` (
                        `id` int NOT NULL AUTO_INCREMENT,
                        `webhook_id` int NOT NULL,
                        `payload` longtext NOT NULL,
                        `status` varchar(50) DEFAULT NULL,
                        `sendtime` datetime DEFAULT NULL,
                        PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        if (get_instance()->db->table_exists(db_prefix() . 'wtc_campaigns')) {
            if (!get_instance()->db->field_exists('phone_number', db_prefix() . 'wtc_campaigns')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `phone_number` VARCHAR(50) NULL DEFAULT NULL;");
            }
        }
        if (get_instance()->db->table_exists(db_prefix() . 'wtc_campaigns')) {
            if (!get_instance()->db->field_exists('webhook_id', db_prefix() . 'wtc_campaigns')) {
                get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `webhook_id` INT NULL DEFAULT NULL;");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_pa_files')) {
            if (!$CI->db->field_exists('openai_file_id', db_prefix() . 'wtc_pa_files')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_pa_files` ADD `openai_file_id` VARCHAR(100) NOT NULL;");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'wtc_personal_assistants')) {
            if (!$CI->db->field_exists('openai_assistant_id', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `openai_assistant_id` VARCHAR(100) NOT NULL;");
            }
            if (!$CI->db->field_exists('openai_vector_id', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `openai_vector_id` VARCHAR(100) NOT NULL;");
            }
            if (!$CI->db->field_exists('pa_description', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `pa_description` TEXT DEFAULT NULL;");
            }
            if (!$CI->db->field_exists('pa_instruction', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `pa_instruction` TEXT NOT NULL;");
            }
            if (!$CI->db->field_exists('assistant_model', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `assistant_model` VARCHAR(200) NOT NULL;");
            }
            if (!$CI->db->field_exists('pa_temperature', db_prefix() . 'wtc_personal_assistants')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `pa_temperature` VARCHAR(3) NOT NULL;");
            }
        }

        $CI->load->model('whatsbot/personal_assistant_model');
        $all_pa = $CI->personal_assistant_model->get();
        foreach ($all_pa as $pa) {
            if (empty($pa['openai_assistant_id'])) {
                $openai_assistant = $CI->personal_assistant_model->createOpenAIAssistant($pa);
                if ($openai_assistant) {
                    $CI->db->update(
                        db_prefix() . 'wtc_personal_assistants',
                        [
                            'openai_assistant_id' => $openai_assistant['id'],
                            'pa_instruction' => "You are a helpful assistant. Please answer questions based on the provided documents.",
                            'assistant_model' => 'gpt-4o-mini',
                            'pa_temperature' => 0.9
                        ],
                        ['id' => $pa['id']]
                    );
                }
            }
            $files = $CI->personal_assistant_model->get_pa_files($pa['id']);
            foreach ($files as $pa_file) {
                if (empty($pa_file['openai_file_id'])) {
                    $file_path = get_upload_path_by_type('personal_assistant') . $pa['id'] . '/' . $pa_file['file_name'];
                    $openai_file = $CI->personal_assistant_model->uploadFileToOpenAI($file_path, $pa_file['file_name']);

                    if ($openai_file) {
                        $personal_assistant = $CI->personal_assistant_model->get($pa['id']);
                        if (!empty($personal_assistant['openai_assistant_id'])) {
                            $CI->personal_assistant_model->attachFileToAssistant($personal_assistant['openai_assistant_id'], $openai_file['id']);
                        }
                        $CI->db->update(
                            db_prefix() . 'wtc_pa_files',
                            ['openai_file_id' => $openai_file['id']],
                            ['id' => $pa_file['id']]
                        );
                    }
                }
            }
        }
    }

    public function down() {}
}
