<?php

defined('BASEPATH') || exit('No direct script access allowed');

$CI = &get_instance();

// license integrity check removed

add_option('wac_verify_token', app_generate_hash());

if (!$CI->db->table_exists(db_prefix() . 'wtc_bot')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_bot` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `reply_text` text NOT NULL,
            `reply_type` int NOT NULL,
            `trigger` text,
            `bot_header` varchar(65) DEFAULT NULL,
            `bot_footer` varchar(65) DEFAULT NULL,
            `button1` varchar(25) DEFAULT NULL,
            `button1_id` varchar(258) DEFAULT NULL,
            `button2` varchar(25) DEFAULT NULL,
            `button2_id` varchar(258) DEFAULT NULL,
            `button3` varchar(25) DEFAULT NULL,
            `button3_id` varchar(258) DEFAULT NULL,
            `button_name` varchar(25) DEFAULT NULL,
            `button_url` varchar(255) DEFAULT NULL,
            `addedfrom` int NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_bot_active` tinyint(1) NOT NULL DEFAULT "1",
            `sending_count` int DEFAULT "0",
            `filename` text DEFAULT NULL,
            `personal_assistants` int DEFAULT NULL,
            `contact_first_name` varchar(255) DEFAULT NULL,
            `contact_last_name` varchar(255) DEFAULT NULL,
            `contact_phone` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_templates')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_templates` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `template_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `template_name` VARCHAR(255) NOT NULL ,
            `language` VARCHAR(50) NOT NULL ,
            `status` VARCHAR(50) NOT NULL ,
            `category` VARCHAR(100) NOT NULL ,
            `header_data_format` VARCHAR(10) NOT NULL ,
            `header_data_text` TEXT ,
            `header_params_count` INT NOT NULL ,
            `body_data` TEXT NOT NULL ,
            `body_params_count` INT NOT NULL ,
            `footer_data` TEXT,
            `footer_params_count` INT NOT NULL ,
            `buttons_data` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`id`),
            UNIQUE KEY `template_id` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}


if (!$CI->db->table_exists('wtc_flows')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_flows` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `flow_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `flow_name` VARCHAR(255) NOT NULL ,
            `status` VARCHAR(50) NOT NULL ,
            `category` TEXT NOT NULL ,
            `preview_url` TEXT NULL DEFAULT NULL ,
            `preview_expiry` BIGINT NULL DEFAULT NULL ,
            `flow_json` MEDIUMTEXT NULL DEFAULT NULL ,
            `automation` MEDIUMTEXT NULL DEFAULT NULL ,
            PRIMARY KEY (`id`),
            UNIQUE KEY `flow_id` (`flow_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}


if (!$CI->db->table_exists('wtc_flows_response')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_flows_response` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `flow_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `receiver_id` varchar(20) NOT NULL,
            `name` varchar(100) NOT NULL,
            `response_data` text,
            `submit_time` timestamp NULL DEFAULT NULL,
            `wa_no` varchar(20) DEFAULT NULL,
            `wa_no_id` varchar(20) DEFAULT NULL,
            `type` varchar(500) DEFAULT NULL,
            `type_id` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_campaigns')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_campaigns` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `template_id` int DEFAULT NULL,
            `scheduled_send_time` timestamp NULL DEFAULT NULL,
            `send_now` tinyint NOT NULL DEFAULT "0",
            `header_params` text,
            `body_params` text,
            `footer_params` text,
            `filename` text DEFAULT NULL,
            `pause_campaign` tinyint(1) NOT NULL DEFAULT "0",
            `select_all` tinyint(1) NOT NULL DEFAULT "0",
            `trigger` text,
            `bot_type` int NOT NULL DEFAULT 0,
            `send_crm_event_pdf` tinyint(1) NOT NULL DEFAULT "0",
            `is_bot_active` int NOT NULL DEFAULT 1,
            `is_bot` int NOT NULL DEFAULT 0,
            `is_sent` tinyint(1) NOT NULL DEFAULT "0",
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
             `sending_count` int DEFAULT "0",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_campaign_data')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_campaign_data` (
            `id` int NOT NULL AUTO_INCREMENT,
            `campaign_id` int NOT NULL,
            `rel_id` int DEFAULT NULL,
            `rel_type` varchar(50) NOT NULL,
            `header_message` text DEFAULT NULL,
            `body_message` text DEFAULT NULL,
            `footer_message` text DEFAULT NULL,
            `status` int DEFAULT NULL,
            `response_message` TEXT NULL DEFAULT NULL,
            `whatsapp_id` TEXT NULL DEFAULT NULL,
            `message_status` varchar(25) NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_interactions')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_interactions` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `receiver_id` varchar(20) NOT NULL,
                `last_message` text,
                `last_msg_time` datetime DEFAULT NULL,
                `wa_no` varchar(20) DEFAULT NULL,
                `wa_no_id` varchar(20) DEFAULT NULL,
                `time_sent` datetime NOT NULL,
                `type` varchar(500) DEFAULT NULL,
                `type_id` varchar(500) DEFAULT NULL,
                `agent` text,
                `label` int DEFAULT NULL,
                `is_ai_chat` tinyint(1) NOT NULL DEFAULT "0",
                `ai_message_json` text,
                PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_interaction_messages')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_interaction_messages` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
            `interaction_id` int unsigned NOT NULL,
            `sender_id` varchar(20) NOT NULL,
            `url` varchar(255) DEFAULT NULL,
            `message` longtext NOT NULL,
            `status` varchar(20) DEFAULT NULL,
            `time_sent` datetime NOT NULL,
            `message_id` varchar(500) DEFAULT NULL,
            `staff_id` varchar(500) DEFAULT NULL,
            `type` varchar(20) DEFAULT NULL,
            `is_read` tinyint(1) NOT NULL DEFAULT "0",
            `ref_message_id` text,
            `ref_msg_id` text,
            `is_ai_chat` tinyint(1) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id`),
            FOREIGN KEY (`interaction_id`) REFERENCES `' . db_prefix() . 'wtc_interactions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_activity_log')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number_id` varchar(255) NULL DEFAULT NULL,
            `access_token` TEXT NULL DEFAULT NULL,
            `business_account_id` varchar(255) NULL DEFAULT NULL,
            `response_code` varchar(4) NOT NULL,
            `response_data` text NOT NULL,
            `category` varchar(50) NOT NULL,
            `category_id` int(11) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `rel_id` int(11) NOT NULL,
            `category_params` longtext NOT NULL,
            `raw_data` TEXT NOT NULL,
            `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
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

if ($CI->db->table_exists('wtc_bot')) {
    if (get_instance()->db->field_exists('trigger', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` CHANGE `trigger` `trigger` TEXT ;");
    }
}
if ($CI->db->table_exists('wtc_campaigns')) {
    if (get_instance()->db->field_exists('trigger', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` CHANGE `trigger` `trigger` TEXT ;");
    }
}
if ($CI->db->table_exists('wtc_campaigns')) {
    if (!get_instance()->db->field_exists('send_crm_event_pdf', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `send_crm_event_pdf` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bot_type`;");
    }
}
if ($CI->db->table_exists('wtc_interactions')) {
    if (!get_instance()->db->field_exists('agent', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `agent` TEXT NULL ;");
    }
}

$chatOptions = set_chat_header();
$content = (!empty($chatOptions['chat_header']) && !empty($chatOptions['chat_footer'])) ? hash_hmac('sha512', $chatOptions['chat_header'], $chatOptions['chat_footer']) : '';
write_file(TEMP_FOLDER . $chatOptions['chat_content'] . '.lic', $content);

// v1.3.0

if ($CI->db->table_exists('wtc_interaction_messages')) {
    if (!get_instance()->db->field_exists('ref_message_id', db_prefix() . 'wtc_interaction_messages')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `ref_message_id` TEXT NULL;");
    }
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_canned_reply')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_canned_reply` (
        `id` int NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `is_public` tinyint(1) NOT NULL DEFAULT "0",
        `added_from` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_ai_prompts')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_ai_prompts` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `action` text NOT NULL,
        `added_from` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_bot_flow')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_bot_flow` (
            `id` int NOT NULL AUTO_INCREMENT,
            `flow_name` varchar(50) NOT NULL,
            `flow_data` longtext NOT NULL,
            `is_active` tinyint(1) DEFAULT "1",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

// v1.3.2
if ($CI->db->table_exists('wtc_campaigns')) {
    if (!get_instance()->db->field_exists('rel_data', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `rel_data` TEXT NULL DEFAULT NULL;");
    }
}

if ($CI->db->table_exists('wtc_templates')) {
    if (get_instance()->db->field_exists('buttons_data', db_prefix() . 'wtc_templates')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_templates` CHANGE `buttons_data` `buttons_data` TEXT NOT NULL;");
    }
}

// v1.4.2
add_option('whatsapp_auto_lead_settings', 1, 0);
add_option('enable_wtc_notification_sound', 1, 0);

// v2.0.0
if (!$CI->db->table_exists(db_prefix() . 'wtc_personal_assistants')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'wtc_personal_assistants` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `openai_assistant_id` VARCHAR(100) NOT NULL,
        `openai_vector_id` VARCHAR(100) NOT NULL,
        `pa_description` TEXT DEFAULT NULL,
        `pa_instruction` TEXT NOT NULL,
        `assistant_model` VARCHAR(200) NOT NULL,
        `pa_temperature` VARCHAR(3) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_pa_files')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'wtc_pa_files` (
        `id` int NOT NULL AUTO_INCREMENT,
        `pa_id` int NOT NULL,
        `file_name` text NOT NULL,
        `filetype` text NOT NULL,
        `openai_file_id` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_interactions')) {
    if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
    }
    if (!get_instance()->db->field_exists('ai_message_json', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `ai_message_json` TEXT NULL DEFAULT NULL ;");
    }
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_interaction_messages')) {
    if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interaction_messages')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
    }
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
    if (!get_instance()->db->field_exists('personal_assistants', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `personal_assistants` INT NULL DEFAULT NULL;");
    }
}

add_option('pa_temperature', 0.5, 0);
add_option('pa_max_token', 200, 0);

// v3.0.0
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


$bots = get_instance()->db->where(["option" => 1, "personal_assistants" => 0])->get(db_prefix() . "wtc_bot")->result_array();
foreach ($bots as $bot) {
    if (!empty($bot['button1_id']) || !empty($bot['button2_id']) || !empty($bot['button3_id'])) {
        $set['option'] = 2;
    } elseif (!empty($bot['button_name']) && !empty($bot['button_url']) && filter_var($bot['button_url'], \FILTER_VALIDATE_URL)) {
        $set['option'] = 3;
    } elseif (!empty($bot['filename'])) {
        $set['option'] = 4;
    } elseif (!empty($bot['sections']) && !is_null($bot['sections'])) {
        $set['option'] = 5;
    } else {
        $set['option'] = 1;
    }
    get_instance()->db->update(db_prefix() . "wtc_bot", $set, ["id" => $bot['id']]);
}

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
        $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `assistant_model`  VARCHAR(200) NOT NULL;");
    }
    if (!$CI->db->field_exists('pa_temperature', db_prefix() . 'wtc_personal_assistants')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_personal_assistants` ADD `pa_temperature`  VARCHAR(3) NOT NULL;");
    }
}

add_option('last_whatsbot_cron_run');
add_option('enable_session_management', 0, 0);
add_option('session_expiry_message', "Hi there! Just a quick reminder — this chat session will expire soon. If you have any questions or need further assistance, feel free to reply now. We're here to help!", 0);
add_option('session_expiry_hours', 23, 0);

/* Add MY_ files at the time of module installation, If not exists */
$my_files_list = [
    VIEWPATH . 'themes/perfex/views/my_single_ticket.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/themes/perfex/views/my_single_ticket.php'),
    VIEWPATH . 'themes/perfex/template_parts/projects/project_flow_response.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/themes/perfex/template_parts/projects/project_flow_response.php'),
    VIEWPATH . 'admin/clients/groups/my_contacts.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/admin/clients/groups/my_contacts.php'),
    VIEWPATH . 'admin/clients/my_all_contacts.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/admin/clients/my_all_contacts.php'),
    VIEWPATH . 'admin/leads/my_manage_leads.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/admin/leads/my_manage_leads.php')
];

// Copy each file in $my_files_list to its actual path if it doesn't already exist
foreach ($my_files_list as $actual_path => $resource_path) {
    if (!file_exists($actual_path)) {
        copy($resource_path, $actual_path);
    }
}

// v1.4.3 custom for client branch specials

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
if (!option_exists('wb_drip_cron_batch_limit')) {
    add_option('wb_drip_cron_batch_limit', '50');
}

// Drip Sequences table
if (!$CI->db->table_exists(db_prefix() . 'wtc_drip_sequences')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "wtc_drip_sequences` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `rel_type` VARCHAR(50) DEFAULT 'leads',
                `sender_phone` VARCHAR(25) NULL,
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
                `header_params` TEXT NULL,
                `body_params` TEXT NULL,
                `footer_params` TEXT NULL,
                `filename` TEXT NULL,
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
                `next_send_at` DATETIME NULL,
                `failure_count` INT DEFAULT 0,
                `last_error` TEXT NULL,
                `exit_reason` VARCHAR(50) NULL,
                `processing_started_at` DATETIME NULL,
                `completed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `sequence_id` (`sequence_id`),
                KEY `status` (`status`),
                KEY `status_next_send_at` (`status`, `next_send_at`),
                KEY `sequence_status` (`sequence_id`, `status`),
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

if ($CI->db->table_exists(db_prefix() . 'wtc_drip_sequences')) {
    if (!$CI->db->field_exists('sender_phone', db_prefix() . 'wtc_drip_sequences')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_sequences` ADD `sender_phone` VARCHAR(25) NULL AFTER `rel_type`;");
    }
}

if ($CI->db->table_exists(db_prefix() . 'wtc_drip_steps')) {
    $drip_step_columns = [
        'header_params' => 'TEXT NULL',
        'body_params' => 'TEXT NULL',
        'footer_params' => 'TEXT NULL',
        'filename' => 'TEXT NULL',
    ];
    foreach ($drip_step_columns as $column => $definition) {
        if (!$CI->db->field_exists($column, db_prefix() . 'wtc_drip_steps')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_steps` ADD `{$column}` {$definition};");
        }
    }
}

if ($CI->db->table_exists(db_prefix() . 'wtc_drip_enrollments')) {
    $drip_enrollment_columns = [
        'next_send_at' => 'DATETIME NULL',
        'failure_count' => 'INT DEFAULT 0',
        'last_error' => 'TEXT NULL',
        'exit_reason' => 'VARCHAR(50) NULL',
        'processing_started_at' => 'DATETIME NULL',
    ];
    foreach ($drip_enrollment_columns as $column => $definition) {
        if (!$CI->db->field_exists($column, db_prefix() . 'wtc_drip_enrollments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_enrollments` ADD `{$column}` {$definition};");
        }
    }
    foreach ([
        'status_next_send_at' => ['status', 'next_send_at'],
        'sequence_status' => ['sequence_id', 'status'],
    ] as $index_name => $columns) {
        $index = $CI->db->query("SHOW INDEX FROM `" . db_prefix() . "wtc_drip_enrollments` WHERE Key_name = " . $CI->db->escape($index_name))->row_array();
        if (!$index) {
            $column_sql = implode('`, `', $columns);
            $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_drip_enrollments` ADD INDEX `{$index_name}` (`{$column_sql}`);");
        }
    }
}

if ($CI->db->table_exists('wtc_campaigns')) {
    if (!get_instance()->db->field_exists('crm_events', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `crm_events` TEXT NULL;");
    }
}
if ($CI->db->table_exists('wtc_campaigns')) {
    if (!get_instance()->db->field_exists('send_crm_event_pdf', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `send_crm_event_pdf` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bot_type`;");
    }
}

if ($CI->db->table_exists(db_prefix() . 'wtc_product_metadata')) {
    if (!$CI->db->field_exists('pending_sync', db_prefix() . 'wtc_product_metadata')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "wtc_product_metadata` ADD `pending_sync` TINYINT(1) NOT NULL DEFAULT 0;");
    }
}
