<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: GO Shipping Cargo
Description: Integrated Transportation System for Freight Shipping, Courier Services, and Logistics
Version: 1.0
Author: Xetuu Limited
Requires at least: 2.3.*
*/

const COURIER_MODULE_NAME = 'courier_goshipping';
const CONFIG_FILE = 'config';

class Courier_Logistic_System {

    /** Temporary storage for courier_branch_ids[]/courier_default_branch_id stripped from staff POST data. */
    private $_pending_staff_branch_ids = null;
    private $_pending_staff_default_branch_id = null;

    public function __construct() {
        $this->register_hooks();
    }

    private function register_hooks() {
        // Register uninstall hook
        register_uninstall_hook(COURIER_MODULE_NAME, [$this, 'uninstall']);

        // Register activation hook
        register_activation_hook(COURIER_MODULE_NAME, [$this, 'courier_module_activation_hook']);

        // Register admin menu items
        hooks()->add_action('admin_init', [$this, 'init_menu_items_and_create_permissions']);

        // Themed assets + navbar (Settings > Appearance)
        hooks()->add_action('app_admin_head', [$this, 'load_theme_assets']);
        hooks()->add_action('app_admin_footer', [$this, 'load_theme_scripts']);

        // Migrate old seeded defaults to empty (so Perfex company name is used as fallback)
        hooks()->add_action('admin_init', [$this, 'migrate_legacy_defaults']);

        // Run pending DB schema upgrades (idempotent — skipped once applied)
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v3']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v4']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v5']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v6']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v7']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v8']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v9']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v10']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v11']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v12']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v13']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v14']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v15']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v16']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v17']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v18']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v19']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v20']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v21']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v22']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v23']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v24']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v25']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v26']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v27']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v28']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v29']);
        hooks()->add_action('admin_init', [$this, 'run_db_upgrades_v30']);
        // Register email templates (idempotent — skips if slug already exists)
        hooks()->add_action('admin_init', [$this, 'register_email_templates']);

        // Branch-based staff isolation (replaces the older country-only mechanism)
        hooks()->add_action('after_admin_login_form_start',   [$this, 'inject_login_branch_selector']);
        hooks()->add_action('after_staff_login',              [$this, 'validate_staff_branch_on_login']);
        hooks()->add_filter('before_create_staff_member',     [$this, 'strip_courier_branch_from_data']);
        hooks()->add_filter('before_update_staff_member',     [$this, 'strip_courier_branch_from_data']);
        hooks()->add_action('staff_member_created',           [$this, 'save_staff_branches']);
        hooks()->add_action('staff_member_updated',           [$this, 'save_staff_branches']);
        hooks()->add_action('app_admin_footer',               [$this, 'inject_staff_branch_field_js']);

        // Auto-allow all active payment modes on every new invoice
        hooks()->add_filter('before_invoice_added', [$this, 'auto_allow_all_payment_modes']);

        // Exclude public courier portal + tracking endpoints from CSRF checks
        hooks()->add_filter('csrf_exclude_uris', [$this, 'exclude_public_uris_from_csrf']);

        // Inject shipment reference into the default Perfex invoice (HTML portal view)
        hooks()->add_action('after_right_panel_invoicehtml', [$this, 'inject_shipment_ref_invoicehtml']);

        // Inject shipment reference into the default Perfex invoice (PDF view)
        hooks()->add_filter('invoice_pdf_header_after_custom_fields', [$this, 'inject_shipment_ref_invoicepdf'], 10, 2);
    }

    /**
     * Run pending DB schema upgrades once.
     * Uses an option flag so the ALTER checks only fire once, not on every request.
     */
    public function run_db_upgrades() {
        if (get_option('courier_schema_v2_done')) {
            return;
        }
        $CI = &get_instance();

        if (!$CI->db->field_exists('vat_applicable', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `vat_applicable` TINYINT(1) NOT NULL DEFAULT 0,
                ADD COLUMN `vat_rate`       DECIMAL(5,2) NOT NULL DEFAULT 16.00,
                ADD COLUMN `vat_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00
            ');
        }
        if (!$CI->db->field_exists('is_round_trip', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `is_round_trip` TINYINT(1) NOT NULL DEFAULT 0
            ');
        }
        if (!$CI->db->field_exists('fleet_assignment_id', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `fleet_assignment_id` INT(11) NULL DEFAULT NULL
            ');
        }
        if (!$CI->db->field_exists('stop_type', db_prefix() . '_shipment_stops')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_stops`
                ADD COLUMN `stop_type`        ENUM(\'transit\',\'offloading\',\'pickup\') NOT NULL DEFAULT \'transit\',
                ADD COLUMN `odometer_reading` INT(11) NULL DEFAULT NULL,
                ADD COLUMN `stop_time`        DATETIME NULL DEFAULT NULL,
                ADD COLUMN `handler_name`     VARCHAR(255) NULL DEFAULT NULL
            ');
        }

        add_option('courier_vat_rate', '16');
        update_option('courier_schema_v2_done', '1');
    }

    /**
     * v3 upgrade: make courier_company_id nullable so shipments can be saved
     * without a linked courier-company record (logistic company is now a text setting).
     */
    public function run_db_upgrades_v3() {
        if (get_option('courier_schema_v3_done')) {
            return;
        }
        $CI = &get_instance();
        $tbl = db_prefix() . '_shipments';

        // Check if column is already nullable (fresh installs from updated install.php).
        $col = $CI->db->query(
            "SELECT IS_NULLABLE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = '{$tbl}'
               AND COLUMN_NAME  = 'courier_company_id'"
        )->row();

        if ($col && $col->IS_NULLABLE === 'NO') {
            // Find every FK on this column using info_schema (MySQL 5.7 compatible).
            $fks = $CI->db->query(
                "SELECT kcu.CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE kcu
                 JOIN information_schema.TABLE_CONSTRAINTS tc
                   ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                  AND kcu.TABLE_SCHEMA    = tc.TABLE_SCHEMA
                  AND kcu.TABLE_NAME      = tc.TABLE_NAME
                 WHERE kcu.TABLE_SCHEMA = DATABASE()
                   AND kcu.TABLE_NAME   = '{$tbl}'
                   AND kcu.COLUMN_NAME  = 'courier_company_id'
                   AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'"
            )->result_array();

            foreach ($fks as $fk) {
                $CI->db->query("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `" . $fk['CONSTRAINT_NAME'] . "`");
            }

            $CI->db->query("ALTER TABLE `{$tbl}` MODIFY COLUMN `courier_company_id` INT NULL DEFAULT NULL");
        }

        update_option('courier_schema_v3_done', '1');
    }

    public function run_db_upgrades_v4() {
        if (get_option('courier_schema_v4_done')) {
            return;
        }
        $CI = &get_instance();

        if (!$CI->db->field_exists('unit_price', db_prefix() . '_shipment_packages')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_packages`
                ADD COLUMN `unit_price` DECIMAL(10,2) NULL DEFAULT NULL
            ');
        }

        add_option('courier_type', 'international');
        update_option('courier_schema_v4_done', '1');
    }

    /**
     * Automatically set allowed_payment_modes to ALL active modes on every new invoice.
     * Fires via the before_invoice_added filter so it works for courier invoices
     * and any normal Perfex invoice created while this module is active.
     */
    public function auto_allow_all_payment_modes($hook_data) {
        // Only override if nothing was explicitly chosen
        if (!empty($hook_data['data']['allowed_payment_modes'])) {
            return $hook_data;
        }

        $CI = &get_instance();
        $CI->load->model('payment_modes_model');

        $modes = [];

        // Offline modes (numeric IDs — Bank, Cash, MPESA, etc.)
        $offline = $CI->db->select('id')
                           ->where('active', 1)
                           ->get(db_prefix() . 'payment_modes')
                           ->result();
        foreach ($offline as $m) {
            $modes[] = (int)$m->id;
        }

        // Online gateways (string IDs — 'pesapal', 'stripe', etc.)
        $gateways = $CI->payment_modes_model->get_payment_gateways(false);
        foreach ($gateways as $gw) {
            if (!empty($gw['id'])) {
                $modes[] = $gw['id'];
            }
        }

        if (!empty($modes)) {
            $hook_data['data']['allowed_payment_modes'] = $modes;
        }

        return $hook_data;
    }

    public function run_db_upgrades_v5() {
        if (get_option('courier_schema_v5_done')) {
            return;
        }
        add_option('courier_invoice_color', '#2e7d32');
        update_option('courier_schema_v5_done', '1');
    }

    /**
     * One-time patch: backfill allowed_payment_modes on all existing courier invoices
     * that currently have an empty serialized array (a:0:{}).
     */
    public function run_db_upgrades_v6() {
        if (get_option('courier_schema_v6_done')) {
            return;
        }

        $CI = &get_instance();
        $CI->load->model('payment_modes_model');

        // Build the full list of active mode IDs
        $modes = [];
        $offline = $CI->db->select('id')
                           ->where('active', 1)
                           ->get(db_prefix() . 'payment_modes')
                           ->result();
        foreach ($offline as $m) {
            $modes[] = (int)$m->id;
        }
        $gateways = $CI->payment_modes_model->get_payment_gateways(false);
        foreach ($gateways as $gw) {
            if (!empty($gw['id'])) {
                $modes[] = $gw['id'];
            }
        }

        if (!empty($modes)) {
            $serialized = serialize($modes);
            // Patch every invoice that has no modes: NULL, empty string, or empty serialized array
            $CI->db->query(
                "UPDATE `" . db_prefix() . "invoices`
                 SET `allowed_payment_modes` = ?
                 WHERE `allowed_payment_modes` IS NULL
                    OR `allowed_payment_modes` = ''
                    OR `allowed_payment_modes` = 'a:0:{}'",
                [$serialized]
            );
        }

        update_option('courier_schema_v6_done', '1');
    }

    public function run_db_upgrades_v7() {
        if (get_option('courier_schema_v7_done')) {
            return;
        }
        $CI = &get_instance();

        if (!$CI->db->field_exists('id_number', db_prefix() . '_shipment_senders')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_senders` ADD COLUMN `id_number` VARCHAR(100) NULL DEFAULT NULL');
        }
        if (!$CI->db->field_exists('id_number', db_prefix() . '_shipment_recipients')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_recipients` ADD COLUMN `id_number` VARCHAR(100) NULL DEFAULT NULL');
        }
        if (!$CI->db->field_exists('station', db_prefix() . '_agents')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_agents` ADD COLUMN `station` VARCHAR(255) NULL DEFAULT NULL');
        }

        update_option('courier_schema_v7_done', '1');
    }

    public function run_db_upgrades_v8() {
        if (get_option('courier_schema_v8_done')) {
            return;
        }
        $CI = &get_instance();

        if (!$CI->db->field_exists('kra_pin', db_prefix() . '_agents')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_agents` ADD COLUMN `kra_pin` VARCHAR(100) NULL DEFAULT NULL');
        }

        update_option('courier_schema_v8_done', '1');
    }

    /**
     * v9: add POD column to packages; create routes + stops tables.
     */
    public function run_db_upgrades_v9() {
        if (get_option('courier_schema_v9_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('pod', db_prefix() . '_shipment_packages')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_packages` ADD COLUMN `pod` VARCHAR(255) NULL DEFAULT NULL');
        }

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_routes` (
            `id`          INT NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_route_stops` (
            `id`         INT NOT NULL AUTO_INCREMENT,
            `route_id`   INT NOT NULL,
            `stop_name`  VARCHAR(255) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`route_id`) REFERENCES `' . db_prefix() . '_courier_routes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        update_option('courier_schema_v9_done', '1');
    }

    /**
     * v10: create service_points table and seed 73 Kenya stops on first run.
     */
    public function run_db_upgrades_v10() {
        if (get_option('courier_schema_v10_done')) return;
        $CI = &get_instance();

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_service_points` (
            `id`         INT NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(255) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $sp_count = $CI->db->count_all(db_prefix() . '_courier_service_points');
        if ($sp_count === 0) {
            $kenya_sps = [
                'Busia','Bumala','Sega','Ugunja','Sidindi','Madeya','Yala','Luanda','Maseno',
                'Kisumu','Ahero','Awasi','Muhoroni','Kericho','Nakuru','Londiani','Naivasha',
                'Kangemi','Gitaru','Westlands','Nairobi CBD','Mlolongo','Kasarani','Githurai',
                'Thika','Makongeni Thika','Ruiru','Meru','Embu','Mwea','Nyeri','Kirinyaga',
                'Mombasa','Voi','Kibwezi','Emali','Sultan Hamud','Narok','Bomet','Kisii',
                'Kibirigo','Keumbu','Migori','Rongo','Homabay','Katito','Eastleigh',
                'Country Bus (Nairobi)','Wote','Machakos','Tala','Kayole Junction','Umoja',
                'Kayole','Embakasi','Pipeline','Kitale','Eldoret','Kimilili','Kiminini',
                'Mosibridge','Bungoma','Malaba','Kakamega','Mumias','Musanda','Sigomere',
                'Sabatia','Ekero/Shinda','Bondo','Siaya','Usenge','Ngiya',
            ];
            $sp_rows = [];
            foreach ($kenya_sps as $i => $name) {
                $sp_rows[] = ['name' => $name, 'sort_order' => $i + 1];
            }
            $CI->db->insert_batch(db_prefix() . '_courier_service_points', $sp_rows);
        }

        update_option('courier_schema_v10_done', '1');
    }

    /**
     * v11: add extra Kenya service points (Route A-L stops) and seed Routes A-L.
     */
    public function run_db_upgrades_v11() {
        if (get_option('courier_schema_v11_done')) return;
        $CI = &get_instance();

        // Extra service points for Routes A-L not in the original 73
        $extra_sps = [
            'Kikuyu','Uthiru','Gilgil','Kodiaga','Nyamasaria','Mtito Andei',
            'Kapsabet','Kaimosi','Chavakali','Soy','Cabanas','Kitengela',
            'Isinya','Athi River','Kajiado','Ngong','Karen','Rongai','Kiserani','Sagana',
        ];
        $max_order = (int)$CI->db->select_max('sort_order')->get(db_prefix() . '_courier_service_points')->row()->sort_order;
        foreach ($extra_sps as $esp) {
            $exists = $CI->db->where('LOWER(name)', strtolower($esp))->count_all_results(db_prefix() . '_courier_service_points');
            if (!$exists) {
                $max_order++;
                $CI->db->insert(db_prefix() . '_courier_service_points', ['name' => $esp, 'sort_order' => $max_order]);
            }
        }

        // Seed Routes A-L only if no routes exist yet
        if ($CI->db->count_all(db_prefix() . '_courier_routes') === 0) {
            $routes_seed = [
                ['Route A — Nairobi to Busia (Via Nakuru & Kisumu)',
                 'Via Gitaru, Kangemi, Naivasha, Nakuru, Gilgil, Kericho, Kisumu, Ugunja, Busia and surrounding stops',
                 ['Kangemi','Gitaru','Uthiru','Kikuyu','Naivasha','Gilgil','Nakuru','Kericho','Kisumu','Ahero','Awasi','Luanda','Maseno','Sidindi','Nyamasaria','Yala','Ugunja','Sega','Bumala','Busia','Kodiaga']],
                ['Route B — Nairobi to Mombasa', 'Via Kibwezi, Emali, Mtito Andei',
                 ['Kibwezi','Emali','Mtito Andei','Mombasa']],
                ['Route C — Nairobi to Homabay (Via Kisii)', 'Via Bomet, Narok, Keumbu, Kisii, Rongo, Migori',
                 ['Narok','Bomet','Keumbu','Kisii','Kibirigo','Rongo','Migori','Homabay']],
                ['Route D — Nairobi to Malaba (Via Eldoret)', 'Via Eldoret, Bungoma',
                 ['Eldoret','Bungoma','Malaba']],
                ['Route E — Nairobi to Mumias (Via Kakamega)', 'Via Kapsabet, Kaimosi, Chavakali, Kakamega',
                 ['Kapsabet','Kaimosi','Chavakali','Kakamega','Mumias','Musanda']],
                ['Route F — Nairobi to Bondo (Via Siaya)', 'Via Naivasha, Nakuru, Kisumu, Luanda, Siaya',
                 ['Naivasha','Nakuru','Kisumu','Luanda','Siaya','Bondo','Usenge']],
                ['Route G — Nairobi to Kitale (Via Eldoret)', 'Via Eldoret, Soy, Kimilili, Mosibridge',
                 ['Eldoret','Soy','Kimilili','Mosibridge','Kitale']],
                ['Route H — Nairobi to Machakos (Via Athi River)', 'Via Mlolongo, Kitengela, Kajiado, Machakos',
                 ['Cabanas','Mlolongo','Kitengela','Isinya','Athi River','Kajiado','Machakos']],
                ['Route I — Nairobi to Karen', 'Via Ngong, Karen', ['Ngong','Karen']],
                ['Route J — Nairobi to Rongai', '', ['Rongai','Kiserani']],
                ['Route K — Nairobi to Nyeri (Via Thika)', 'Via Thika, Makongeni, Ruiru, Mwea',
                 ['Thika','Makongeni Thika','Ruiru','Mwea','Nyeri']],
                ['Route L — Nairobi to Meru (Via Thika & Sagana)', 'Via Thika, Sagana',
                 ['Thika','Sagana','Meru']],
            ];
            foreach ($routes_seed as $route) {
                $CI->db->insert(db_prefix() . '_courier_routes', ['name' => $route[0], 'description' => $route[1]]);
                $route_id = $CI->db->insert_id();
                foreach ($route[2] as $sort => $stop) {
                    $CI->db->insert(db_prefix() . '_courier_route_stops', [
                        'route_id'   => $route_id,
                        'stop_name'  => $stop,
                        'sort_order' => $sort + 1,
                    ]);
                }
            }
        }

        update_option('courier_schema_v11_done', '1');
    }

    /**
     * v12: create tariff_zones + tariff_rates tables and seed default data.
     */
    public function run_db_upgrades_v12() {
        if (get_option('courier_schema_v12_done')) return;
        $CI = &get_instance();

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_tariff_zones` (
            `id`           INT NOT NULL AUTO_INCREMENT,
            `zone_code`    CHAR(1) NOT NULL,
            `name`         VARCHAR(100) NOT NULL,
            `destinations` TEXT NULL,
            `is_available` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `zone_code` (`zone_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_tariff_rates` (
            `id`          INT NOT NULL AUTO_INCREMENT,
            `cargo_type`  ENUM(\'document\',\'parcel\') NOT NULL,
            `weight_min`  DECIMAL(10,3) NOT NULL,
            `weight_max`  DECIMAL(10,3) NOT NULL,
            `rate_type`   ENUM(\'flat\',\'per_kg\') NOT NULL DEFAULT \'per_kg\',
            `zone_a`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            `zone_b`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            `zone_c`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            `zone_d`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            `zone_e`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            `zone_f`      DECIMAL(10,2) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $CI->db->query("INSERT INTO `" . db_prefix() . "_courier_tariff_zones`
            (`zone_code`,`name`,`destinations`,`is_available`) VALUES
            ('A','Zone A — Nairobi Metro','Nairobi CBD, Westlands, Kasarani, Eastleigh, Embakasi, Githurai, Kangemi, Gitaru, Uthiru, Kikuyu, Karen, Ngong, Pipeline, Kayole, Umoja, Country Bus (Nairobi), Rongai, Kiserani',1),
            ('B','Zone B — Greater Nairobi','Thika, Ruiru, Makongeni Thika, Athi River, Mlolongo, Cabanas, Kitengela, Isinya, Kajiado, Machakos, Tala, Wote',1),
            ('C','Zone C — Central & Rift Valley','Naivasha, Gilgil, Nakuru, Kericho, Nyeri, Mwea, Embu, Meru, Kirinyaga, Sagana, Narok, Bomet, Londiani',1),
            ('D','Zone D — Coast & Mid-Western','Mombasa, Voi, Kibwezi, Emali, Sultan Hamud, Mtito Andei, Kisumu, Ahero, Awasi, Katito, Muhoroni, Kisii, Keumbu, Kibirigo, Homabay, Migori, Rongo',1),
            ('E','Zone E — Far Western Kenya','Busia, Bumala, Sega, Ugunja, Sidindi, Yala, Luanda, Maseno, Nyamasaria, Kodiaga, Siaya, Bondo, Usenge, Ngiya, Eldoret, Kitale, Kimilili, Mosibridge, Soy, Bungoma, Malaba, Kakamega, Mumias, Musanda, Kapsabet, Kaimosi, Chavakali, Sabatia',1),
            ('F','Zone F — Special / International','International destinations and remote areas not covered by Zones A-E. Contact us directly for pricing.',0)
            ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),`destinations`=VALUES(`destinations`),`is_available`=VALUES(`is_available`);");

        if ($CI->db->count_all(db_prefix() . '_courier_tariff_rates') === 0) {
            $CI->db->insert_batch(db_prefix() . '_courier_tariff_rates', [
                ['cargo_type'=>'document','weight_min'=>0.001,'weight_max'=>0.350,'rate_type'=>'flat',  'zone_a'=>200, 'zone_b'=>200, 'zone_c'=>200, 'zone_d'=>200, 'zone_e'=>200, 'zone_f'=>0   ],
                ['cargo_type'=>'parcel',  'weight_min'=>1,    'weight_max'=>15,   'rate_type'=>'per_kg','zone_a'=>150, 'zone_b'=>150, 'zone_c'=>150, 'zone_d'=>150, 'zone_e'=>150, 'zone_f'=>250 ],
                ['cargo_type'=>'parcel',  'weight_min'=>16,   'weight_max'=>30,   'rate_type'=>'per_kg','zone_a'=>130, 'zone_b'=>140, 'zone_c'=>150, 'zone_d'=>160, 'zone_e'=>170, 'zone_f'=>220 ],
                ['cargo_type'=>'parcel',  'weight_min'=>31,   'weight_max'=>50,   'rate_type'=>'per_kg','zone_a'=>110, 'zone_b'=>120, 'zone_c'=>130, 'zone_d'=>140, 'zone_e'=>150, 'zone_f'=>200 ],
                ['cargo_type'=>'parcel',  'weight_min'=>51,   'weight_max'=>100,  'rate_type'=>'per_kg','zone_a'=>100, 'zone_b'=>110, 'zone_c'=>120, 'zone_d'=>130, 'zone_e'=>140, 'zone_f'=>180 ],
                ['cargo_type'=>'parcel',  'weight_min'=>101,  'weight_max'=>152,  'rate_type'=>'per_kg','zone_a'=>90,  'zone_b'=>100, 'zone_c'=>110, 'zone_d'=>120, 'zone_e'=>130, 'zone_f'=>160 ],
            ]);
        }

        update_option('courier_schema_v12_done', '1');
    }

    /**
     * v13: add payment_terms column to shipments table.
     */
    public function run_db_upgrades_v13() {
        if (get_option('courier_schema_v13_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('payment_terms', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `payment_terms` TEXT NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v13_done', '1');
    }

    public function run_db_upgrades_v14() {
        if (get_option('courier_schema_v14_done')) return;

        // Default rates for each shipping mode (set to 1.00 — admin must update to real rates)
        add_option('courier_rate_road',             '1.00');
        add_option('courier_rate_sea_lcl',          '1.00');
        add_option('courier_rate_sea_consolidation','1.00');
        add_option('courier_rate_air_freight',      '1.00');
        add_option('courier_rate_air_consolidation','1.00');
        // FCL per-container rates
        add_option('courier_rate_sea_fcl_20dv', '1.00');
        add_option('courier_rate_sea_fcl_40dv', '1.00');
        add_option('courier_rate_sea_fcl_20hc', '1.00');
        add_option('courier_rate_sea_fcl_40hc', '1.00');
        add_option('courier_rate_sea_fcl_20rf', '1.00');
        add_option('courier_rate_sea_fcl_40rf', '1.00');
        add_option('courier_rate_sea_fcl_20fr', '1.00');
        add_option('courier_rate_sea_fcl_40fr', '1.00');
        add_option('courier_rate_sea_fcl_roro', '1.00');

        update_option('courier_schema_v14_done', '1');
    }

    public function run_db_upgrades_v15() {
        if (get_option('courier_schema_v15_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('country_id', db_prefix() . '_courier_service_points')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_courier_service_points`
                ADD COLUMN `country_id` INT NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v15_done', '1');
    }

    public function run_db_upgrades_v16() {
        if (get_option('courier_schema_v16_done')) return;
        $CI = &get_instance();

        // Reseed states if the table exists but is empty (common on live deployments
        // where the module was installed before states.json was added)
        if ($CI->db->table_exists(db_prefix() . '_country_states')) {
            $count = $CI->db->count_all(db_prefix() . '_country_states');
            if ($count === 0) {
                $jsonFile = FCPATH . 'modules/courier_goshipping/assets/states.json';
                if (file_exists($jsonFile)) {
                    $states = json_decode(file_get_contents($jsonFile), true);
                    if (is_array($states) && !empty($states)) {
                        $chunks = array_chunk($states, 500);
                        foreach ($chunks as $chunk) {
                            $CI->db->insert_batch(db_prefix() . '_country_states', $chunk);
                        }
                    }
                }
            }
        }

        update_option('courier_schema_v16_done', '1');
    }

    /**
     * v17: create courier_staff_countries table; add country_id to shipments + pickups.
     */
    public function run_db_upgrades_v17() {
        if (get_option('courier_schema_v17_done')) return;
        $CI = &get_instance();

        $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_staff_countries` (
            `id`           INT NOT NULL AUTO_INCREMENT,
            `staff_id`     INT NOT NULL,
            `country_id`   INT NOT NULL,
            `country_name` VARCHAR(100) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_staff` (`staff_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        if (!$CI->db->field_exists('country_id', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `country_id` INT NULL DEFAULT NULL
            ');
        }
        if (!$CI->db->field_exists('staff_country_id', db_prefix() . '_pickups')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_pickups`
                ADD COLUMN `staff_country_id` INT NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v17_done', '1');
    }

    /**
     * v18: add is_portal_request + quoted_amount to shipments for portal quote flow.
     */
    public function run_db_upgrades_v18() {
        if (get_option('courier_schema_v18_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('is_portal_request', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `is_portal_request` TINYINT(1) NOT NULL DEFAULT 0
            ');
        }
        if (!$CI->db->field_exists('quoted_amount', db_prefix() . '_shipments')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
                ADD COLUMN `quoted_amount` DECIMAL(10,2) NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v18_done', '1');
    }

    /**
     * v20: add 'cancelled' status to shipments and pickups.
     */
    public function run_db_upgrades_v20() {
        if (get_option('courier_schema_v20_done')) return;
        $CI = &get_instance();

        // Insert 'cancelled' into shipment statuses if not already present
        $exists = $CI->db->get_where(db_prefix() . '_shipment_statuses', ['status_name' => 'cancelled'])->row();
        if (!$exists) {
            $CI->db->insert(db_prefix() . '_shipment_statuses', [
                'status_name' => 'cancelled',
                'description' => 'Cancelled',
                'active'      => 1,
            ]);
        }

        // Expand pickups ENUM to include 'cancelled'
        $CI->db->query("ALTER TABLE `" . db_prefix() . "_pickups`
            MODIFY COLUMN `status` ENUM('pending','picked_up','delivered','cancelled') NOT NULL DEFAULT 'pending'
        ");

        update_option('courier_schema_v20_done', '1');
    }

    /**
     * v21: create origin-based international tariff table.
     */
    public function run_db_upgrades_v21() {
        if (get_option('courier_schema_v21_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_origin_tariffs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_origin_tariffs` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `origin_country` VARCHAR(100) NOT NULL,
                `destination_country` VARCHAR(100) NOT NULL,
                `service_type` VARCHAR(30) NOT NULL,
                `weight_min` DECIMAL(10,3) NOT NULL DEFAULT 0,
                `weight_max` DECIMAL(10,3) NOT NULL DEFAULT 0,
                `rate_type` ENUM(\'flat\',\'per_kg\') NOT NULL DEFAULT \'per_kg\',
                `rate` DECIMAL(12,4) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_orig_dest_svc` (`origin_country`(50), `destination_country`(50), `service_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }

        update_option('courier_schema_v21_done', '1');
    }

    /**
     * v22: add commission_rate, admin_notes, suspended_reason, suspended_at to agents.
     */
    public function run_db_upgrades_v22() {
        if (get_option('courier_schema_v22_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('commission_rate', db_prefix() . '_agents')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_agents`
                ADD COLUMN `commission_rate`  DECIMAL(5,2) NULL DEFAULT NULL,
                ADD COLUMN `admin_notes`      TEXT         NULL DEFAULT NULL,
                ADD COLUMN `suspended_reason` TEXT         NULL DEFAULT NULL,
                ADD COLUMN `suspended_at`     DATETIME     NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v22_done', '1');
    }

    /**
     * v23: create "Courier: Agent" role with default safe permissions if not present.
     */
    public function run_db_upgrades_v23() {
        if (get_option('courier_schema_v23_done')) return;
        $CI = &get_instance();

        $existing = $CI->db->query('SELECT roleid FROM ' . db_prefix() . 'roles WHERE name = "Courier: Agent"')->row();
        if (!$existing) {
            $default_perms = serialize([
                'courier-shipments' => ['view_own_shipments', 'create_shipments', 'create_shipment_road', 'create_shipment_courier', 'create_shipment_domestic'],
                'courier-pickups'   => ['view_own_pickups', 'create_pickups'],
                'courier-waybills'  => ['view_own_waybills'],
                'courier-invoices'  => ['view_own_invoices', 'generate_payment', 'view_receipts'],
            ]);
            $CI->db->query('INSERT INTO ' . db_prefix() . 'roles (name, permissions) VALUES ("Courier: Agent", ' . $CI->db->escape($default_perms) . ')');
        }

        update_option('courier_schema_v23_done', '1');
    }

    /**
     * v24: create domestic city-to-city tariffs table (international/freight mode only).
     */
    public function run_db_upgrades_v24() {
        if (get_option('courier_schema_v24_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_domestic_tariffs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_domestic_tariffs` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `origin_city` VARCHAR(100) NOT NULL,
                `destination_city` VARCHAR(100) NOT NULL,
                `weight_min` DECIMAL(10,3) NOT NULL DEFAULT 0,
                `weight_max` DECIMAL(10,3) NOT NULL DEFAULT 999999,
                `rate_type` ENUM(\'flat\',\'per_kg\') NOT NULL DEFAULT \'flat\',
                `rate` DECIMAL(12,4) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_orig_dest` (`origin_city`(50), `destination_city`(50))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }

        update_option('courier_schema_v24_done', '1');
    }

    /**
     * v25: add source and delivery_signature_url to pickups table.
     */
    public function run_db_upgrades_v25() {
        if (get_option('courier_schema_v25_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('source', db_prefix() . '_pickups')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_pickups`
                ADD COLUMN `source` ENUM(\'portal\', \'shipment\', \'system\') NOT NULL DEFAULT \'system\'
            ');
        }
        
        if (!$CI->db->field_exists('delivery_signature_url', db_prefix() . '_pickups')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_pickups`
                ADD COLUMN `delivery_signature_url` TEXT NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v25_done', '1');
    }

    /**
     * v26: introduce branches/offices (local + international) and staff→branch
     * assignment, replacing the older single-country mechanism. Adds branch_id
     * to shipments/pickups/manifests/agents/quotations (and, if the shopify
     * connector module is active, to shopify_orders/shopify_product_mappings
     * for Salibay order routing). Backfills existing staff country assignments
     * into branches so nobody is left unassigned after the migration.
     */
    public function run_db_upgrades_v26() {
        if (get_option('courier_schema_v26_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_branches')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_branches` (
                `id`          INT NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(150) NOT NULL,
                `code`        VARCHAR(80)  NOT NULL,
                `branch_type` ENUM(\'local\',\'international\') NOT NULL DEFAULT \'local\',
                `country_id`  INT NULL DEFAULT NULL,
                `city`        VARCHAR(100) NULL DEFAULT NULL,
                `address`     TEXT NULL DEFAULT NULL,
                `phone`       VARCHAR(30)  NULL DEFAULT NULL,
                `email`       VARCHAR(150) NULL DEFAULT NULL,
                `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
                `is_default`  TINYINT(1)   NOT NULL DEFAULT 0,
                `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_branch_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }

        if (!$CI->db->table_exists(db_prefix() . '_courier_staff_branches')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_staff_branches` (
                `id`         INT NOT NULL AUTO_INCREMENT,
                `staff_id`   INT NOT NULL,
                `branch_id`  INT NOT NULL,
                `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                `date_added` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_staff_branch` (`staff_id`, `branch_id`),
                INDEX `idx_csb_staff` (`staff_id`),
                INDEX `idx_csb_branch` (`branch_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }

        if (!$CI->db->table_exists(db_prefix() . '_courier_shopify_locations')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_shopify_locations` (
                `id`                 INT NOT NULL AUTO_INCREMENT,
                `shopify_location_id` VARCHAR(50) NOT NULL,
                `branch_id`          INT NULL DEFAULT NULL,
                `name`               VARCHAR(150) NULL DEFAULT NULL,
                `city`               VARCHAR(100) NULL DEFAULT NULL,
                `country_code`       VARCHAR(10)  NULL DEFAULT NULL,
                `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
                `last_synced_at`     DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_shopify_location` (`shopify_location_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }

        foreach (['_shipments', '_pickups', '_manifests', '_agents', '_courier_quotations'] as $suffix) {
            $table = db_prefix() . $suffix;
            if ($CI->db->table_exists($table) && !$CI->db->field_exists('branch_id', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `branch_id` INT NULL DEFAULT NULL");
            }
        }

        // Cross-module: only touch shopify_* tables if that module is active/installed.
        if ($CI->db->table_exists(db_prefix() . 'shopify_orders') && !$CI->db->field_exists('branch_id', db_prefix() . 'shopify_orders')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'shopify_orders` ADD COLUMN `branch_id` INT NULL DEFAULT NULL');
        }
        if ($CI->db->table_exists(db_prefix() . 'shopify_product_mappings') && !$CI->db->field_exists('courier_branch_id', db_prefix() . 'shopify_product_mappings')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'shopify_product_mappings` ADD COLUMN `courier_branch_id` INT NULL DEFAULT NULL');
        }

        // One-time backfill: legacy staff_countries → branches + staff_branches,
        // so staff who already had a country assigned keep working data isolation.
        $legacy_rows = $CI->db->get(db_prefix() . '_courier_staff_countries')->result();
        $home_country_id = (int) get_option('customer_default_country');

        foreach ($legacy_rows as $legacy) {
            $country_row = $CI->db->where('country_id', (int) $legacy->country_id)
                ->get(db_prefix() . 'countries')
                ->row();
            $country_name = $country_row->short_name ?? ('Country #' . (int) $legacy->country_id);

            $branch = $CI->db->where('country_id', (int) $legacy->country_id)
                ->where('branch_type', ((int) $legacy->country_id === $home_country_id) ? 'local' : 'international')
                ->get(db_prefix() . '_courier_branches')
                ->row();

            if (!$branch) {
                $branch_type = ((int) $legacy->country_id === $home_country_id) ? 'local' : 'international';
                $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $country_name), 0, 6)) . '-' . (int) $legacy->country_id;
                $CI->db->insert(db_prefix() . '_courier_branches', [
                    'name'        => $country_name . ' Office',
                    'code'        => $code,
                    'branch_type' => $branch_type,
                    'country_id'  => (int) $legacy->country_id,
                    'is_active'   => 1,
                    'is_default'  => ($branch_type === 'local') ? 1 : 0,
                ]);
                $branch_id = $CI->db->insert_id();
            } else {
                $branch_id = (int) $branch->id;
            }

            $exists = $CI->db->where('staff_id', (int) $legacy->staff_id)
                ->where('branch_id', $branch_id)
                ->count_all_results(db_prefix() . '_courier_staff_branches');
            if (!$exists) {
                $CI->db->insert(db_prefix() . '_courier_staff_branches', [
                    'staff_id'   => (int) $legacy->staff_id,
                    'branch_id'  => $branch_id,
                    'is_default' => 1,
                ]);
            }
        }

        // Ensure at least one default local branch exists so unresolved orders
        // and single-branch staff always have a sensible fallback.
        $has_default = $CI->db->where('is_default', 1)->count_all_results(db_prefix() . '_courier_branches');
        if (!$has_default) {
            $country = $home_country_id ? $CI->db->where('country_id', $home_country_id)->get(db_prefix() . 'countries')->row() : null;
            $CI->db->insert(db_prefix() . '_courier_branches', [
                'name'        => ($country->short_name ?? 'Head Office') . ' Office',
                'code'        => 'HQ',
                'branch_type' => 'local',
                'country_id'  => $home_country_id ?: null,
                'is_active'   => 1,
                'is_default'  => 1,
            ]);
        }

        update_option('courier_schema_v26_done', '1');
    }

    /**
     * v27: force-resync "Courier: Agent" role permissions to the intended
     * "own everything, nothing global, no Salibay Fulfilment" definition —
     * a one-time correction in case the live role drifted from this seed
     * (manually edited via Setup > Roles) or any agent staff picked up
     * extra individual permissions along the way. Runs once; re-running
     * Setup > Roles > "Courier: Agent" and saving will NOT be reverted by
     * this again since it's gated by the version flag below.
     */
    public function run_db_upgrades_v27() {
        if (get_option('courier_schema_v27_done')) return;
        $CI = &get_instance();

        $safe_agent_permissions = [
            'courier-shipments' => ['view_own_shipments', 'create_shipments', 'create_shipment_road', 'create_shipment_courier', 'create_shipment_domestic'],
            'courier-pickups'   => ['view_own_pickups', 'create_pickups'],
            'courier-waybills'  => ['view_own_waybills'],
            'courier-invoices'  => ['view_own_invoices', 'generate_payment', 'view_receipts'],
        ];

        $role = $CI->db->where('name', 'Courier: Agent')->get(db_prefix() . 'roles')->row();
        if ($role) {
            $CI->db->where('roleid', $role->roleid)->update(db_prefix() . 'roles', [
                'permissions' => serialize($safe_agent_permissions),
            ]);

            // Push the corrected set onto every staff member currently
            // assigned this role, replacing whatever individual permissions
            // they'd accumulated (mirrors Agents::sync_role_permissions()).
            $agent_staff = $CI->db->where('role', $role->roleid)->get(db_prefix() . 'staff')->result();
            foreach ($agent_staff as $staff) {
                $CI->db->where('staff_id', $staff->staffid)->delete(db_prefix() . 'staff_permissions');
                foreach ($safe_agent_permissions as $feature => $capabilities) {
                    foreach ($capabilities as $capability) {
                        $CI->db->insert(db_prefix() . 'staff_permissions', [
                            'staff_id'   => $staff->staffid,
                            'feature'    => $feature,
                            'capability' => $capability,
                        ]);
                    }
                }
            }
        }

        update_option('courier_schema_v27_done', '1');
    }

    /**
     * v28: seed international freight-forwarding branches/offices requested
     * for the global (Envato) rollout — China, Hong Kong, Thailand, UK, UAE,
     * plus a catch-all "Europe" branch covering all European countries.
     * Dubai Branch already exists (id 2, country UAE) from an earlier seed,
     * so it's left untouched here; skipped by name+country if already present.
     */
    public function run_db_upgrades_v28() {
        if (get_option('courier_schema_v28_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_branches')) {
            return;
        }

        $lookup_country = function ($short_name) use ($CI) {
            $row = $CI->db->where('short_name', $short_name)->get(db_prefix() . 'countries')->row();
            return $row ? (int) $row->country_id : null;
        };

        $branches = [
            ['name' => 'China Branch',       'code' => 'CHINA01',   'country' => 'China'],
            ['name' => 'Hong Kong Branch',   'code' => 'HK01',      'country' => 'Hong Kong'],
            ['name' => 'Thailand Branch',    'code' => 'THAI01',    'country' => 'Thailand'],
            ['name' => 'UK Branch',          'code' => 'UK01',      'country' => 'United Kingdom'],
            ['name' => 'UAE Branch',         'code' => 'UAE01',     'country' => 'United Arab Emirates'],
            ['name' => 'Europe Branch',      'code' => 'EUROPE01',  'country' => null],
        ];

        foreach ($branches as $branch) {
            $exists = $CI->db->where('name', $branch['name'])
                ->get(db_prefix() . '_courier_branches')
                ->row();
            if ($exists) {
                continue;
            }

            $country_id = $branch['country'] ? $lookup_country($branch['country']) : null;

            $CI->db->insert(db_prefix() . '_courier_branches', [
                'name'        => $branch['name'],
                'code'        => $branch['code'],
                'branch_type' => 'international',
                'country_id'  => $country_id,
                'is_active'   => 1,
                'is_default'  => 0,
            ]);
        }

        update_option('courier_schema_v28_done', '1');
    }

    /**
     * v29: switch international branch codes onto the same {PREFIX}/B/{YEAR}/{SEQ}
     * pattern Branches::generate_branch_code() uses for branches created via the
     * admin UI, add capital-city values, and expand the single "Europe" catch-all
     * from v28 into one branch per European country (UK stays separate — it
     * already has its own branch). Dubai keeps "Dubai" as its city since the
     * branch is explicitly named after the city, not the UAE capital.
     */
    public function run_db_upgrades_v29() {
        if (get_option('courier_schema_v29_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_branches')) {
            return;
        }

        $year = '2026';
        $seq = 0;
        $make_code = function ($name) use (&$seq, $year) {
            $seq++;
            $prefix = strtoupper(trim((string) preg_replace('/\s+/', '-', preg_replace('/[^A-Za-z0-9]+/', ' ', $name))));
            return $prefix . '/B/' . $year . '/' . str_pad((string) $seq, 2, '0', STR_PAD_LEFT);
        };

        // name => [country short_name or null, city]
        $entries = [
            'Dubai Branch'      => ['United Arab Emirates', 'Dubai'],
            'China Branch'      => ['China', 'Beijing'],
            'Hong Kong Branch'  => ['Hong Kong', 'Hong Kong'],
            'Thailand Branch'   => ['Thailand', 'Bangkok'],
            'UK Branch'         => ['United Kingdom', 'London'],
            'UAE Branch'        => ['United Arab Emirates', 'Abu Dhabi'],

            'Albania Branch'                => ['Albania', 'Tirana'],
            'Andorra Branch'                => ['Andorra', 'Andorra la Vella'],
            'Austria Branch'                => ['Austria', 'Vienna'],
            'Belarus Branch'                => ['Belarus', 'Minsk'],
            'Belgium Branch'                => ['Belgium', 'Brussels'],
            'Bosnia and Herzegovina Branch' => ['Bosnia and Herzegovina', 'Sarajevo'],
            'Bulgaria Branch'               => ['Bulgaria', 'Sofia'],
            'Croatia Branch'                => ['Croatia', 'Zagreb'],
            'Cyprus Branch'                 => ['Cyprus', 'Nicosia'],
            'Czech Republic Branch'         => ['Czech Republic', 'Prague'],
            'Denmark Branch'                => ['Denmark', 'Copenhagen'],
            'Estonia Branch'                => ['Estonia', 'Tallinn'],
            'Finland Branch'                => ['Finland', 'Helsinki'],
            'France Branch'                 => ['France', 'Paris'],
            'Germany Branch'                => ['Germany', 'Berlin'],
            'Greece Branch'                 => ['Greece', 'Athens'],
            'Hungary Branch'                => ['Hungary', 'Budapest'],
            'Iceland Branch'                => ['Iceland', 'Reykjavik'],
            'Ireland Branch'                => ['Ireland', 'Dublin'],
            'Italy Branch'                  => ['Italy', 'Rome'],
            'Kosovo Branch'                 => ['Kosovo', 'Pristina'],
            'Latvia Branch'                 => ['Latvia', 'Riga'],
            'Liechtenstein Branch'          => ['Liechtenstein', 'Vaduz'],
            'Lithuania Branch'              => ['Lithuania', 'Vilnius'],
            'Luxembourg Branch'             => ['Luxembourg', 'Luxembourg City'],
            'Malta Branch'                  => ['Malta', 'Valletta'],
            'Moldova Branch'                => ['Moldava', 'Chisinau'],
            'Monaco Branch'                 => ['Monaco', 'Monaco'],
            'Montenegro Branch'             => ['Montenegro', 'Podgorica'],
            'Netherlands Branch'            => ['Netherlands', 'Amsterdam'],
            'North Macedonia Branch'        => ['North Macedonia', 'Skopje'],
            'Norway Branch'                 => ['Norway', 'Oslo'],
            'Poland Branch'                 => ['Poland', 'Warsaw'],
            'Portugal Branch'               => ['Portugal', 'Lisbon'],
            'Romania Branch'                => ['Romania', 'Bucharest'],
            'Russia Branch'                 => ['Russia', 'Moscow'],
            'San Marino Branch'             => ['San Marino', 'San Marino'],
            'Serbia Branch'                 => ['Serbia', 'Belgrade'],
            'Slovakia Branch'               => ['Slovakia', 'Bratislava'],
            'Slovenia Branch'               => ['Slovenia', 'Ljubljana'],
            'Spain Branch'                  => ['Spain', 'Madrid'],
            'Sweden Branch'                 => ['Sweden', 'Stockholm'],
            'Switzerland Branch'            => ['Switzerland', 'Bern'],
            'Ukraine Branch'                => ['Ukraine', 'Kyiv'],
            'Vatican City Branch'           => ['Vatican City', 'Vatican City'],
        ];

        // Widen code: the original CREATE TABLE used VARCHAR(30), too short for
        // codes like "BOSNIA-AND-HERZEGOVINA-BRANCH/B/2026/12" (would silently
        // truncate rather than error, since strict mode isn't guaranteed).
        $CI->db->query('ALTER TABLE `' . db_prefix() . '_courier_branches` MODIFY COLUMN `code` VARCHAR(80) NOT NULL');

        // The v28 catch-all is superseded by individual country branches.
        $CI->db->where('name', 'Europe Branch')->delete(db_prefix() . '_courier_branches');

        $country_ids = [];
        foreach ($CI->db->get(db_prefix() . 'countries')->result() as $row) {
            $country_ids[$row->short_name] = (int) $row->country_id;
        }

        // Keyed by trimmed name: the pre-existing "Dubai Branch" row was seeded
        // with a trailing space, which would otherwise dodge an exact-match
        // lookup and create a duplicate.
        $existing_by_name = [];
        foreach ($CI->db->get(db_prefix() . '_courier_branches')->result() as $row) {
            $existing_by_name[trim((string) $row->name)] = $row;
        }

        foreach ($entries as $name => $info) {
            [$country_name, $city] = $info;
            $country_id = $country_ids[$country_name] ?? null;
            $code = $make_code($name);

            $existing = $existing_by_name[$name] ?? null;
            if ($existing) {
                $CI->db->where('id', $existing->id)->update(db_prefix() . '_courier_branches', [
                    'code'       => $code,
                    'city'       => $city,
                    'country_id' => $country_id,
                ]);
                continue;
            }

            $CI->db->insert(db_prefix() . '_courier_branches', [
                'name'        => $name,
                'code'        => $code,
                'branch_type' => 'international',
                'country_id'  => $country_id,
                'city'        => $city,
                'is_active'   => 1,
                'is_default'  => 0,
            ]);
        }

        update_option('courier_schema_v29_done', '1');
    }

    /**
     * v30: seed a USA branch (needed for the "Route GSC-US-ORD" mapping —
     * no US branch existed from the earlier country-branch rollout, which
     * only covered Dubai/China/Hong Kong/Thailand/UK/UAE/Europe) and the
     * route-tag → branch mappings for Dubai/USA/UK/China, so a fresh
     * production deploy doesn't need someone to re-enter these by hand at
     * Salibay Fulfilment > Settings > Route Mapping.
     */
    public function run_db_upgrades_v30() {
        if (get_option('courier_schema_v30_done')) return;
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . '_courier_branches')) {
            return;
        }

        $usa_branch = $CI->db->where('name', 'USA Branch')->get(db_prefix() . '_courier_branches')->row();
        if (!$usa_branch) {
            $country = $CI->db->where('short_name', 'United States')->get(db_prefix() . 'countries')->row();
            $CI->db->insert(db_prefix() . '_courier_branches', [
                'name'        => 'USA Branch',
                'code'        => 'USA-BRANCH/B/2026/52',
                'branch_type' => 'international',
                'country_id'  => $country ? (int) $country->country_id : null,
                'city'        => 'Chicago',
                'is_active'   => 1,
                'is_default'  => 0,
            ]);
            $usa_branch_id = $CI->db->insert_id();
        } else {
            $usa_branch_id = (int) $usa_branch->id;
        }

        if (!$CI->db->table_exists(db_prefix() . 'courier_route_branch_map')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'courier_route_branch_map` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `route_tag` VARCHAR(100) NOT NULL UNIQUE,
                `branch_id` INT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        $branch_ids_by_name = [];
        foreach ($CI->db->get(db_prefix() . '_courier_branches')->result() as $row) {
            $branch_ids_by_name[$row->name] = (int) $row->id;
        }

        $route_map = [
            'GSC-AE-DXB' => $branch_ids_by_name['Dubai Branch'] ?? null,
            'GSC-US-ORD' => $usa_branch_id,
            'GSC-GB-LHR' => $branch_ids_by_name['UK Branch'] ?? null,
            'GSC-CN-SZX' => $branch_ids_by_name['China Branch'] ?? null,
        ];

        foreach ($route_map as $route_tag => $branch_id) {
            if (!$branch_id) {
                continue;
            }
            $existing = $CI->db->where('route_tag', $route_tag)->get(db_prefix() . 'courier_route_branch_map')->row();
            if ($existing) {
                $CI->db->where('id', $existing->id)->update(db_prefix() . 'courier_route_branch_map', ['branch_id' => $branch_id]);
            } else {
                $CI->db->insert(db_prefix() . 'courier_route_branch_map', [
                    'route_tag' => $route_tag,
                    'branch_id' => $branch_id,
                ]);
            }
        }

        update_option('courier_schema_v30_done', '1');
    }

    /**
     * v19: add kra_pin to shipment senders and companies tables.
     */
    public function run_db_upgrades_v19() {
        if (get_option('courier_schema_v19_done')) return;
        $CI = &get_instance();

        if (!$CI->db->field_exists('kra_pin', db_prefix() . '_shipment_senders')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_senders`
                ADD COLUMN `kra_pin` VARCHAR(50) NULL DEFAULT NULL
            ');
        }
        if (!$CI->db->field_exists('kra_pin', db_prefix() . '_shipment_companies')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_companies`
                ADD COLUMN `kra_pin` VARCHAR(50) NULL DEFAULT NULL
            ');
        }

        update_option('courier_schema_v19_done', '1');
    }

    /* ── Branch-based staff isolation ────────────────────────────────────── */

    /**
     * Inject a "Select Branch" dropdown at the top of the admin login form.
     * Only shown when the submitting staff account (looked up by email, since
     * we're pre-auth here) has more than one assigned branch; a single-branch
     * staff member is auto-selected server-side after login, no prompt needed.
     */
    public function inject_login_branch_selector() {
        $CI = &get_instance();
        // Login happens before we know who's logging in, so this can't be
        // pre-filtered to just one agent's assignment — it lists every active
        // office, grouped by country so "Kenya" reads as one group covering
        // Nairobi/Nakuru/Kisumu etc., rather than one flat list of office
        // names. Staff assigned to exactly one office never need to touch
        // this at all — validate_staff_branch_on_login() auto-resolves it
        // silently; this selector only matters for staff/agents covering
        // more than one office.
        $branches = $CI->db->select('b.id, b.name, b.country_id, c.short_name as country_name')
                            ->from(db_prefix() . '_courier_branches b')
                            ->join(db_prefix() . 'countries c', 'c.country_id = b.country_id', 'left')
                            ->where('b.is_active', 1)
                            ->order_by('c.short_name', 'ASC')
                            ->order_by('b.name', 'ASC')
                            ->get()
                            ->result_array();

        if (empty($branches)) {
            return;
        }

        $grouped = [];
        foreach ($branches as $b) {
            $country_label = $b['country_name'] ?: 'Other';
            $grouped[$country_label][] = $b;
        }
        ksort($grouped);
        ?>
        <div class="form-group" id="courier-branch-group" style="margin-bottom:20px;">
            <label for="courier_branch_id" class="control-label !tw-mb-3">
                Select Country / Office
            </label>
            <select name="courier_branch_id" id="courier_branch_id" class="form-control">
                <option value="">-- Select Country / Office (if applicable) --</option>
                <?php foreach ($grouped as $country_label => $offices): ?>
                <?php if (count($offices) === 1): ?>
                <option value="<?= (int) $offices[0]['id']; ?>"><?= htmlspecialchars($country_label, ENT_QUOTES); ?></option>
                <?php else: ?>
                <optgroup label="<?= htmlspecialchars($country_label, ENT_QUOTES); ?>">
                    <?php foreach ($offices as $b): ?>
                    <option value="<?= (int) $b['id']; ?>"><?= htmlspecialchars($b['name'], ENT_QUOTES); ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * After a successful admin login, resolve which branch this session is
     * "operating as". If the staff has 0 or 1 assigned branches, auto-resolve
     * silently. If they have more than one, the submitted courier_branch_id
     * must be one of their assignments, or the session is revoked.
     */
    public function validate_staff_branch_on_login() {
        $CI = &get_instance();
        $staff_id = get_staff_user_id();

        $branch_ids = courier_get_staff_branch_ids($staff_id);
        if (empty($branch_ids)) {
            // No branch assigned — no restriction applied (matches legacy behaviour)
            return;
        }

        if (count($branch_ids) === 1) {
            $CI->session->set_userdata('courier_active_branch_id', $branch_ids[0]);
            return;
        }

        $submitted = (int) $CI->input->post('courier_branch_id');
        if ($submitted <= 0 || !in_array($submitted, $branch_ids, true)) {
            $CI->load->model('Authentication_model');
            $CI->Authentication_model->logout();
            set_alert('danger', 'Please select one of your assigned branches to continue.');
            redirect(admin_url('authentication'));
            return;
        }

        $CI->session->set_userdata('courier_active_branch_id', $submitted);
    }

    /**
     * Filter: remove courier_branch_ids[]/courier_default_branch_id from the
     * staff data array before DB insert/update. Saves the values in instance
     * variables so save_staff_branches() can use them.
     */
    public function strip_courier_branch_from_data($data, $id = null) {
        if (isset($data['courier_branch_ids'])) {
            $this->_pending_staff_branch_ids = array_map('intval', (array) $data['courier_branch_ids']);
            unset($data['courier_branch_ids']);
        }
        if (isset($data['courier_default_branch_id'])) {
            $this->_pending_staff_default_branch_id = (int) $data['courier_default_branch_id'];
            unset($data['courier_default_branch_id']);
        }
        return $data;
    }

    /**
     * After a staff member is created or updated, persist their branch
     * assignments. Fires from the staff_member_created / staff_member_updated
     * hooks. Replaces the whole assignment set (delete + re-insert).
     */
    public function save_staff_branches($staff_id) {
        $CI = &get_instance();
        $branch_ids = $this->_pending_staff_branch_ids !== null
            ? $this->_pending_staff_branch_ids
            : array_map('intval', (array) $CI->input->post('courier_branch_ids'));
        $default_branch_id = $this->_pending_staff_default_branch_id !== null
            ? $this->_pending_staff_default_branch_id
            : (int) $CI->input->post('courier_default_branch_id');

        $this->_pending_staff_branch_ids = null;
        $this->_pending_staff_default_branch_id = null;

        $tbl = db_prefix() . '_courier_staff_branches';
        $CI->db->where('staff_id', (int) $staff_id)->delete($tbl);

        $branch_ids = array_values(array_unique(array_filter($branch_ids)));
        if (empty($branch_ids)) {
            return;
        }

        if (!in_array($default_branch_id, $branch_ids, true)) {
            $default_branch_id = $branch_ids[0];
        }

        foreach ($branch_ids as $branch_id) {
            $CI->db->insert($tbl, [
                'staff_id'   => (int) $staff_id,
                'branch_id'  => $branch_id,
                'is_default' => ($branch_id === $default_branch_id) ? 1 : 0,
            ]);
        }
    }

    /**
     * Inject a JS block in the admin footer that adds a branch checklist
     * (+ default radio) to the staff member profile form (no core file
     * changes needed).
     */
    public function inject_staff_branch_field_js() {
        $CI = &get_instance();
        $uri = $CI->uri->uri_string();
        if (strpos($uri, 'staff/member') === false && strpos($uri, 'staff/myprofile') === false) {
            return;
        }

        $staff_id = (int) $CI->uri->segment(4); // /admin/staff/member/{id}
        if ($staff_id <= 0 && strpos($uri, 'myprofile') !== false) {
            $staff_id = get_staff_user_id();
        }

        $assigned_ids = $staff_id > 0 ? courier_get_staff_branch_ids($staff_id) : [];
        $default_id = $staff_id > 0 ? (courier_get_default_staff_branch_id($staff_id) ?: 0) : 0;

        $branches = $CI->db->where('is_active', 1)
                            ->order_by('name', 'ASC')
                            ->get(db_prefix() . '_courier_branches')
                            ->result_array();

        $rows_html = '';
        foreach ($branches as $b) {
            $id = (int) $b['id'];
            $checked = in_array($id, $assigned_ids, true) ? ' checked' : '';
            $default_checked = ($id === $default_id) ? ' checked' : '';
            $label = htmlspecialchars($b['name'], ENT_QUOTES) . ' (' . htmlspecialchars($b['branch_type'] ?? 'local', ENT_QUOTES) . ')';
            $rows_html .= '<div style="display:flex;align-items:center;gap:10px;padding:4px 0;">'
                . '<label style="font-weight:400;margin:0;flex:1;"><input type="checkbox" class="cgs-branch-check" name="courier_branch_ids[]" value="' . $id . '"' . $checked . '> ' . $label . '</label>'
                . '<label style="font-weight:400;margin:0;font-size:11px;color:#888;"><input type="radio" name="courier_default_branch_id" value="' . $id . '"' . $default_checked . '> Default</label>'
                . '</div>';
        }
        $rows_js = json_encode($rows_html);
        ?>
        <script>
        (function() {
            function injectCourierBranchField() {
                if (document.getElementById('courier-branch-field-wrap')) return;

                var pwLabel = document.querySelector('label[for="password"]');
                if (!pwLabel) return;

                var anchor = pwLabel;
                var prev = pwLabel.previousElementSibling;
                if (prev && (prev.classList.contains('clearfix') || prev.classList.contains('form-group'))) {
                    anchor = prev;
                }

                var wrap = document.createElement('div');
                wrap.id = 'courier-branch-field-wrap';
                wrap.className = 'form-group';
                wrap.innerHTML =
                    '<label class="control-label">' +
                    '<i class="fa fa-building" style="margin-right:4px;color:#555;"></i>' +
                    ' Branches / Offices <span style="color:#888;font-weight:400;font-size:11px;">(Courier Module)</span>' +
                    '</label>' +
                    '<div style="border:1px solid #e5e5e5;border-radius:4px;padding:8px 12px;">' +
                    <?= $rows_js; ?> +
                    '</div>';

                anchor.parentNode.insertBefore(wrap, anchor);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', injectCourierBranchField);
            } else {
                injectCourierBranchField();
            }
        })();
        </script>
        <?php
    }

    /**
     * Register courier email templates so they appear in Setup > Email Templates.
     * create_email_template() is idempotent — it skips insertion if the slug exists.
     */

    /* ──────────────────────────────────────────────────────────────
     * Shipment reference injection into the default Perfex invoice
     * ────────────────────────────────────────────────────────────── */

    /**
     * Fetch shipment data linked to a Perfex invoice ID.
     * Returns an array with reference fields, or null if no shipment found.
     */
    private function get_shipment_invoice_ref($invoice_id) {
        $CI = &get_instance();

        $shipment = $CI->db
            ->where('invoice_id', (int)$invoice_id)
            ->get(db_prefix() . '_shipments')
            ->row();

        if (!$shipment) return null;

        // Origin — prefer individual sender, fall back to company sender
        $origin = '';
        if (!empty($shipment->sender_id)) {
            $sender = $CI->db->get_where(db_prefix() . '_shipment_senders', ['id' => $shipment->sender_id])->row();
            if ($sender) {
                $parts = array_filter([$sender->address ?? '', $sender->zipcode ?? '']);
                $origin = implode(', ', $parts);
            }
        } elseif (!empty($shipment->company_id)) {
            $company = $CI->db->get_where(db_prefix() . '_shipment_companies', ['id' => $shipment->company_id])->row();
            if ($company) {
                $origin = trim($company->contact_address ?? '');
            }
        }

        // Destination — prefer individual recipient, fall back to company recipient
        $destination = '';
        if (!empty($shipment->recipient_id)) {
            $recipient = $CI->db->get_where(db_prefix() . '_shipment_recipients', ['id' => $shipment->recipient_id])->row();
            if ($recipient) {
                $parts = array_filter([$recipient->address ?? '', $recipient->zipcode ?? '']);
                $destination = implode(', ', $parts);
            }
        } elseif (!empty($shipment->recipient_company_id)) {
            $company = $CI->db->get_where(db_prefix() . '_shipment_companies', ['id' => $shipment->recipient_company_id])->row();
            if ($company) {
                $destination = trim($company->contact_address ?? '');
            }
        }

        return [
            'waybill'      => $shipment->waybill_number ?? '',
            'mode'         => strtoupper($shipment->shipping_mode ?? ''),
            'category'     => strtoupper($shipment->shipping_category ?? ''),
            'origin'       => $origin,
            'destination'  => $destination,
        ];
    }

    /**
     * Action hook: inject shipment reference block into the HTML invoice
     * (after_right_panel_invoicehtml — inside the right header column).
     * Only fires when the invoice is linked to a courier shipment.
     */
    public function inject_shipment_ref_invoicehtml($invoice) {
        $ref = $this->get_shipment_invoice_ref($invoice->id);
        if (!$ref) return;
        $esc = function($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); };
        ?>
        <div style="margin-top:16px;text-align:left;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr>
                        <th colspan="4" style="background:#f5f5f5;color:#444;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:7px 10px;border:1px solid #ddd;border-bottom:2px solid #bbb;">
                            Shipment Reference
                            <?php if ($ref['waybill']): ?>
                                <span style="float:right;font-weight:400;color:#888;font-size:10px;">Waybill: <?= $esc($ref['waybill']); ?></span>
                            <?php endif; ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:22%;padding:6px 10px;font-weight:700;color:#555;border:1px solid #e8e8e8;background:#fafafa;white-space:nowrap;">Shipping Mode</td>
                        <td style="width:28%;padding:6px 10px;color:#222;border:1px solid #e8e8e8;"><?= $esc($ref['mode']); ?></td>
                        <td style="width:22%;padding:6px 10px;font-weight:700;color:#555;border:1px solid #e8e8e8;background:#fafafa;white-space:nowrap;">Category</td>
                        <td style="width:28%;padding:6px 10px;color:#222;border:1px solid #e8e8e8;"><?= $esc($ref['category']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 10px;font-weight:700;color:#555;border:1px solid #e8e8e8;background:#fafafa;white-space:nowrap;">Origin</td>
                        <td style="padding:6px 10px;color:#222;border:1px solid #e8e8e8;"><?= $esc($ref['origin']); ?></td>
                        <td style="padding:6px 10px;font-weight:700;color:#555;border:1px solid #e8e8e8;background:#fafafa;white-space:nowrap;">Destination</td>
                        <td style="padding:6px 10px;color:#222;border:1px solid #e8e8e8;"><?= $esc($ref['destination']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Filter hook: append shipment reference to the PDF invoice right-column string.
     * Only appends when the invoice is linked to a courier shipment.
     */
    public function inject_shipment_ref_invoicepdf($invoice_info, $invoice) {
        $ref = $this->get_shipment_invoice_ref($invoice->id);
        if (!$ref) return $invoice_info;

        $esc = function($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); };

        $invoice_info .= '<br />';
        $invoice_info .= '<b>Shipment Reference</b><br />';
        $invoice_info .= '<b>Shipping Mode:</b> ' . $esc($ref['mode']) . ' &nbsp;&nbsp; ';
        $invoice_info .= '<b>Category:</b> '      . $esc($ref['category']) . '<br />';
        $invoice_info .= '<b>Origin:</b> '         . $esc($ref['origin'])   . ' &nbsp;&nbsp; ';
        $invoice_info .= '<b>Destination:</b> '    . $esc($ref['destination']) . '<br />';
        if ($ref['waybill']) {
            $invoice_info .= '<b>Waybill:</b> ' . $esc($ref['waybill']) . '<br />';
        }

        return $invoice_info;
    }

    public function register_email_templates() {
        create_email_template(
            'Your Invoice {invoice_number} from {company_name}',
            '<p>Dear {recipient_name},</p>
<p>Please find your invoice <strong>{invoice_number}</strong> attached.</p>
<p><strong>Total Amount:</strong> {total_amount}<br>
<strong>Due Date:</strong> {due_date}</p>
<p>Thank you for choosing {company_name}.</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Courier Invoice to Customer',
            'courier_invoice_to_customer'
        );

        create_email_template(
            'Payment Receipt {receipt_number} - {company_name}',
            '<p>Dear {recipient_name},</p>
<p>We have received your payment. Here are the details:</p>
<p><strong>Receipt No:</strong> {receipt_number}<br>
<strong>Amount Paid:</strong> {amount_paid}<br>
<strong>Payment Mode:</strong> {payment_mode}<br>
<strong>Payment Date:</strong> {payment_date}<br>
<strong>Balance Due:</strong> {balance_due}</p>
<p>Thank you for your payment.</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Courier Payment Receipt to Customer',
            'courier_payment_receipt_to_customer'
        );

        create_email_template(
            'Your Shipment Waybill {waybill_number} - {company_name}',
            '<p>Dear {recipient_name},</p>
<p>Your shipment waybill is ready.</p>
<p><strong>Waybill Number:</strong> {waybill_number}<br>
<strong>Sender:</strong> {sender_name}<br>
<strong>Shipping Mode:</strong> {shipping_mode}<br>
<strong>Status:</strong> {status}</p>
<p>Please use the waybill number to track your shipment.</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Courier Waybill Notification to Customer',
            'courier_waybill_to_customer'
        );
    }

    public function migrate_legacy_defaults() {
        if (get_option('courier_logistic_company') === 'GO Shipping') {
            update_option('courier_logistic_company', '');
        }
        if (get_option('courier_waybill_prefix') === 'GOSH') {
            update_option('courier_waybill_prefix', '');
        }
    }

    public function exclude_public_uris_from_csrf($uris) {
        $uris[] = 'courier_goshipping/tracking/shipment_info';
        $uris[] = 'courier_goshipping/portal/store_pickup';
        $uris[] = 'courier_goshipping/portal/store_shipment';
        $uris[] = 'courier_goshipping/portal/calculate_quote';
        $uris[] = 'courier_goshipping/portal/send_quote_email';
        $uris[] = 'courier_goshipping/portal/tariff_zones';
        return $uris;
    }

    public function uninstall() {
        require_once __DIR__ . '/uninstall.php';
    }

    public function courier_module_activation_hook() {
        // Use require (not require_once) so install.php runs fresh for each
        // tenant activation in a SaaS context — require_once would skip the
        // file if it was already included for a different tenant in the same request.
        require __DIR__ . '/install.php';
    }

    /**
     * Registers a single Perfex sidebar entry ("GO Shipping Cargo") pointing at the
     * module dashboard. All in-module navigation (Dashboard, Shipments, Pickups,
     * Companies, Manifests, Agents, Client Quotes, Settings, ...) is now rendered by
     * the module's own top navbar/mega-menu (views/layout/_topnav.php), which
     * reproduces the exact same has_permission()/is_admin() checks that used to gate
     * these as separate sidebar children — same destinations, same permission
     * gating, just surfaced in-page instead of in the Perfex sidebar tree.
     */
    public function init_menu_items_and_create_permissions() {

        // Create permissions
        $this->create_permissions();

        $CI =& get_instance();

        $CI->app_menu->add_sidebar_menu_item('courier-management', [
            'name'     => 'GO Shipping Cargo',
            'icon'     => 'fa fa-cubes',
            'href'     => admin_url(COURIER_MODULE_NAME . '/dashboard'),
            'position' => 5,
        ]);
    }

    /**
     * Injects the GO Shipping Cargo design-token stylesheet plus the site-configured
     * theme color overrides (Settings > Appearance) into <head>. Scoped to this
     * module's own pages only.
     */
    public function load_theme_assets() {
        $CI = &get_instance();
        if ($CI->uri->segment(2) !== COURIER_MODULE_NAME) {
            return;
        }

        $css_url = module_dir_url('courier_goshipping', 'assets/css/courier_goshipping.css');
        echo '<link rel="stylesheet" href="' . $css_url . '?v=' . filemtime(FCPATH . 'modules/courier_goshipping/assets/css/courier_goshipping.css') . '">';

        $primary   = get_option('courier_theme_primary_color') ?: '#3a6ea5';
        $secondary = get_option('courier_theme_secondary_color') ?: '#c1272d';
        ?>
        <style>
            :root {
                --cgs-primary: <?php echo $primary; ?>;
                --cgs-secondary: <?php echo $secondary; ?>;
            }
        </style>
        <?php
    }

    /**
     * Injects the mega-menu toggle script. Scoped to this module's own pages only.
     */
    public function load_theme_scripts() {
        $CI = &get_instance();
        if ($CI->uri->segment(2) !== COURIER_MODULE_NAME) {
            return;
        }

        $js_url = module_dir_url('courier_goshipping', 'assets/js/courier_goshipping.js');
        echo '<script src="' . $js_url . '?v=' . filemtime(FCPATH . 'modules/courier_goshipping/assets/js/courier_goshipping.js') . '"></script>';
    }

    private function create_permissions(){
        $config = [];

        // Shipments
        $config['capabilities'] = [
            'view_own_shipments'   => 'View (Own)',
            'view_all_shipments'   => 'View (Global)',
            'create_shipments'     => 'Create (All)',
            'create_shipment_road' => 'Create (Road)',
            'create_shipment_fcl'  => 'Create (FCL)',
            'create_shipment_lcl'  => 'Create (LCL)',
            'create_shipment_consolidation' => 'Create (Consolidation)',
            'create_shipment_air_freight' => 'Create (Air Freight)',
            'create_shipment_air_consolidation' => 'Create (Air Consolidation)',
            'create_shipment_courier' => 'Create (Courier)',
            'create_shipment_domestic' => 'Create (Domestic)',
            'edit_shipments'       => 'Edit',
            'delete_shipments'     => 'Delete',
        ];
        register_staff_capabilities('courier-shipments', $config, 'Courier - Shipments');

        // Pickups
        $config['capabilities'] = [
            'view_own_pickups'   => 'View (Own)',
            'view_all_pickups'   => 'View (Global)',
            'create_pickups'     => 'Create',
            'edit_pickups'       => 'Edit',
            'delete_pickups'     => 'Delete',
        ];
        register_staff_capabilities('courier-pickups', $config, 'Courier - Pickups');

        // Waybills
        $config['capabilities'] = [
            'view_own_waybills' => 'View (Own)',
            'view_waybills'   => 'View (Global)',
            'delete_waybills' => 'Delete',
        ];
        register_staff_capabilities('courier-waybills', $config, 'Courier - Waybills');

        // Manifests
        $config['capabilities'] = [
            'view_own_manifests' => 'View (Own)',
            'view_manifests'   => 'View (Global)',
            'create_manifests' => 'Create',
            'edit_manifests'   => 'Edit',
            'delete_manifests' => 'Delete',
        ];
        register_staff_capabilities('courier-manifests', $config, 'Courier - Manifests');

        // Invoices & Receipts
        $config['capabilities'] = [
            'view_own_invoices' => 'View (Own)',
            'view_invoices'    => 'View (Global)',
            'create_invoices'  => 'Create',
            'delete_invoices'  => 'Delete',
            'view_receipts'    => 'View Receipts',
            'generate_payment' => 'Generate Payment',
        ];
        register_staff_capabilities('courier-invoices', $config, 'Courier - Invoices & Receipts');

        // Agents
        $config['capabilities'] = [
            'view_agents'   => 'View',
            'create_agents' => 'Create',
            'edit_agents'   => 'Edit',
            'delete_agents' => 'Delete',
        ];
        register_staff_capabilities('courier-agents', $config, 'Courier - Agents');

        // Courier Companies
        $config['capabilities'] = [
            'view_companies'   => 'View',
            'create_companies' => 'Create',
            'edit_companies'   => 'Edit',
            'delete_companies' => 'Delete',
        ];
        register_staff_capabilities('courier-companies', $config, 'Courier - Companies');

        // Settings
        $config['capabilities'] = [
            'view_settings' => 'View',
            'edit_settings' => 'Edit',
        ];
        register_staff_capabilities('courier-settings', $config, 'Courier - Settings');

        // Branches / Offices
        $config['capabilities'] = [
            'view_branches'         => 'View',
            'create_branches'       => 'Create',
            'edit_branches'         => 'Edit',
            'delete_branches'       => 'Delete',
            'assign_staff_branches' => 'Assign Staff to Branches',
            'view_all_branches'     => 'View All Branches (bypass isolation)',
        ];
        register_staff_capabilities('courier-branches', $config, 'Courier - Branches');
    }
}

// CSRF exclusions for the Rider PWA API and Salibay driver links live in
// config/csrf_exclude_uris.php, not here — InitModules applies that file at
// the pre_system hook, before App_Security::csrf_verify() ever runs; an
// add_filter('csrf_exclude_uris', ...) registered from this file fires too
// late to have any effect (see that file's header comment).

// Instantiate the module class to initialize it
new Courier_Logistic_System();
