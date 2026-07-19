<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Declare $CI as global
global $CI;
$CI =& get_instance();

// create table `tbl_courier_companies`
if (!$CI->db->table_exists(db_prefix() . '_courier_companies')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_companies` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `company_name` VARCHAR(255) NOT NULL,
        `prefix` VARCHAR(255) NOT NULL,
        `type` ENUM("internal", "third_party") NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Insert data into `tbl_courier_companies`
    $CI->db->query('INSERT INTO `' . db_prefix() . '_courier_companies` (`id`, `company_name`, `prefix`, `type`) VALUES
    (1, "GO Shipping", "GOSHP", "internal"),
    (2, "DELL", "DELL", "third_party"),
    (3, "FedEx", "FEDE", "third_party"),
    (4, "Dafric", "DAFR", "third_party"),
    (5, "MultiMedia", "MILTU", "third_party");');

}


//create table  `tbl_shipment_statuses`
if (!$CI->db->table_exists(db_prefix() . '_shipment_statuses')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_statuses` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `status_name` VARCHAR(100) NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT \'1\',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');


    // Insert data into `tbl_shipment_statuses`
    $CI->db->query('INSERT INTO `' . db_prefix() . '_shipment_statuses` (`id`, `status_name`, `description`, `active`) VALUES
    (1, "created", "Created", 1),
    (2, "picked_up", "Picked up", 1),
    (3, "received", "Received", 1),
    (4, "dispatched", "Dispatched", 1),
    (5, "in_transit", "In Transit", 1),
    (6, "arrived_destination", "Arrived at Destination", 1),
    (7, "out_for_delivery", "Out For Delivery", 1),
    (8, "delivered", "Delivered", 1);');

}


//create table  `tbl_shipment_recipients`
if (!$CI->db->table_exists(db_prefix() . '_shipment_recipients')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_recipients` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `address` TEXT,
        `zipcode` VARCHAR(20) NOT NULL,
        `address_type` ENUM(\'postal_code\', \'zip_code\') NOT NULL,
        `state_id` INT NULL,
        `country_id` INT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`country_id`) REFERENCES `' . db_prefix() . 'countries`(`country_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_shipment_companies`
if (!$CI->db->table_exists(db_prefix() . '_shipment_companies')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_companies` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `company_name` VARCHAR(255) NOT NULL,
        `contact_person_name` VARCHAR(255) NOT NULL,
        `contact_person_phone_number` VARCHAR(20) NOT NULL,
        `contact_person_email` VARCHAR(255) NOT NULL,
        `contact_state_id` INT NULL,
        `contact_country_id` INT NULL,
        `contact_address_type` ENUM(\'postal_code\', \'zip_code\') NOT NULL,
        `contact_address` TEXT,
        `contact_zipcode` VARCHAR(20) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_recipient_companies`
if (!$CI->db->table_exists(db_prefix() . '_recipient_companies')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_recipient_companies` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `recipient_company_name` VARCHAR(255) NOT NULL,
        `recipient_contact_person_name` VARCHAR(255) NOT NULL,
        `recipient_contact_person_phone_number` VARCHAR(20) NOT NULL,
        `recipient_contact_person_email` VARCHAR(255) NOT NULL,
        `recipient_contact_state_id` INT NULL,
        `recipient_contact_country_id` INT NULL,
        `recipient_contact_address_type` ENUM(\'postal_code\', \'zip_code\') NOT NULL,
        `recipient_contact_address` TEXT,
        `recipient_contact_zipcode` VARCHAR(20) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_shipment_senders`
if (!$CI->db->table_exists(db_prefix() . '_shipment_senders')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_senders` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `address` TEXT,
        `zipcode` VARCHAR(20) NOT NULL,
        `address_type` ENUM(\'postal_code\', \'zip_code\') NOT NULL,
        `state_id` INT NULL,
        `country_id` INT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`country_id`) REFERENCES `' . db_prefix() . 'countries`(`country_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_shipments`
if (!$CI->db->table_exists(db_prefix() . '_shipments')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipments` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `shipping_mode` VARCHAR(255) NOT NULL,
        `shipping_category` ENUM(\'domestic\', \'international\') NOT NULL,
        `export` INT NOT NULL DEFAULT 0,
        `import` INT NOT NULL DEFAULT 0,
        `tracking_id` VARCHAR(255) NOT NULL,
        `company_type` VARCHAR(255) NOT NULL,
        `waybill_number` VARCHAR(255) NOT NULL,
        `courier_company_id` INT NULL DEFAULT NULL,
        `invoice_id` INT NOT NULL DEFAULT 0,
        `status_id` INT NOT NULL,
        `sender_id` INT NULL,
        `recipient_id` INT NULL,
        `company_id` INT NULL,
        `recipient_company_id` INT NULL,
        `fcl_shipment` INT NULL,
        `staff_id` INT NOT NULL,
        `packaging_charges` DECIMAL(10, 2) NOT NULL,
        `commercial_invoice_url` VARCHAR(255),
        `created_at` DATETIME,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`status_id`) REFERENCES `' . db_prefix() . '_shipment_statuses`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`sender_id`) REFERENCES `' . db_prefix() . '_shipment_senders`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`recipient_id`) REFERENCES `' . db_prefix() . '_shipment_recipients`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`company_id`) REFERENCES `' . db_prefix() . '_shipment_companies`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_third_party_shipments`
if (!$CI->db->table_exists(db_prefix() . '_third_party_shipments')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_third_party_shipments` (
        `shipment_id` INT NOT NULL,
        `tracking_id` VARCHAR(255) NOT NULL,
        `courier_company_id` INT NOT NULL,
        PRIMARY KEY (`shipment_id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`courier_company_id`) REFERENCES `' . db_prefix() . '_courier_companies`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_pickup_contacts`
if (!$CI->db->table_exists(db_prefix() . '_pickup_contacts')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_pickup_contacts` (
        `id` INT NOT NULL auto_increment,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_contact_persons`
if (!$CI->db->table_exists(db_prefix() . '_contact_persons')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_contact_persons` (
        `id` INT NOT NULL auto_increment,
        `company_id` INT NOT NULL,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(20) UNIQUE NOT NULL,
        `email` VARCHAR(255) UNIQUE NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`company_id`) REFERENCES `' . db_prefix() . '_courier_companies`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_pickups`
if (!$CI->db->table_exists(db_prefix() . '_pickups')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_pickups` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `pickup_date` DATE,
        `pickup_start_time` VARCHAR(20) NOT NULL,
        `pickup_end_time` VARCHAR(20) NOT NULL,
        `country_id` INT NULL,
        `state_id` INT NULL,
        `address` TEXT NOT NULL,
        `pickup_zip` VARCHAR(20) NOT NULL,
        `address_type` VARCHAR(20) NOT NULL,
        `vehicle_type` VARCHAR(20) NOT NULL,
        `shipment_id` INT NULL,
        `contact_person_id` INT NULL,
        `staff_id` INT NOT NULL,
        `driver_id` INT NOT NULL,
        `status` ENUM(\'pending\', \'picked_up\', \'delivered\') NOT NULL DEFAULT \'pending\',
        `signature_url` TEXT NULL DEFAULT NULL,
        `created_at` DATETIME,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`country_id`) REFERENCES `' . db_prefix() . 'countries`(`country_id`) ON DELETE CASCADE,
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`contact_person_id`) REFERENCES `' . db_prefix() . '_pickup_contacts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_shipment_fcl_packages`
if (!$CI->db->table_exists(db_prefix() . '_shipment_fcl_packages')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_fcl_packages` (
        `id` INT NOT NULL auto_increment,
        `quantity` INT NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `fcl_option` VARCHAR(255) NOT NULL,
        `shipment_id` INT NOT NULL,
        `created_at` DATETIME,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_shipment_packages`
if (!$CI->db->table_exists(db_prefix() . '_shipment_packages')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_packages` (
        `id`               INT NOT NULL auto_increment,
        `quantity`         INT NOT NULL,
        `description`      VARCHAR(255) NOT NULL,
        `length`           DECIMAL(10, 2) NOT NULL,
        `width`            DECIMAL(10, 2) NOT NULL,
        `height`           DECIMAL(10, 2) NOT NULL,
        `weight`           DECIMAL(10, 2) NOT NULL,
        `weight_volume`    DECIMAL(10, 2) NOT NULL,
        `chargeable_weight` DECIMAL(10, 2) NOT NULL,
        `unit_price`       DECIMAL(10, 2) NULL DEFAULT NULL,
        `pod`              VARCHAR(255) NULL DEFAULT NULL,
        `shipment_id`      INT NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_shipment_status_history`
if (!$CI->db->table_exists(db_prefix() . '_shipment_status_history')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_status_history` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `shipment_id` INT,
        `status_id` INT,
        `changed_at` DATETIME,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_shipment_stops`
if (!$CI->db->table_exists(db_prefix() . '_shipment_stops')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_shipment_stops` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `shipment_id` INT,
        `departure_point` VARCHAR(255) NOT NULL,
        `destination_point` VARCHAR(255) NOT NULL,
        `description` LONGTEXT NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_manifest_period`
if (!$CI->db->table_exists(db_prefix() . '_manifest_period')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_manifest_period` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `manifest_number` INT,
        `start_date` VARCHAR(255) NOT NULL,
        `end_date` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_dimensional_factor`
if (!$CI->db->table_exists(db_prefix() . '_dimensional_factor')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_dimensional_factor` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `value` INT NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Insert data into `tbl_dimensional_factor`
$CI->db->query("INSERT INTO `" . db_prefix() . "_dimensional_factor` (`id`, `name`, `value`) VALUES
    (1, 'default', 5000),
    (2, 'air_consolidation', 6000),
    (3, 'air_freight', 6000),
    (4, 'sea_lcl', 1000)
    ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`);");


// Create table `tbl_deliveries`
if (!$CI->db->table_exists(db_prefix() . '_deliveries')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_deliveries` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `shipment_id` INT,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `signature_url` TEXT NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `tbl_commercial_values_items`
if (!$CI->db->table_exists(db_prefix() . '_commercial_values_items')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_commercial_values_items` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `quantity` INT NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `declared_value` INT NOT NULL,
        `shipment_id` INT NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_agents`
if (!$CI->db->table_exists(db_prefix() . '_agents')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_agents` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `staff_id` INT NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `address` TEXT NOT NULL,
        `company_name` TEXT  NULL,
        `id_file_url` TEXT NOT NULL,
        `location_file_url` TEXT  NULL,
        `cert_of_corp_url` TEXT  NULL,
        `kra_file_url` TEXT NOT NULL,
        `country_id` INT(20) NOT NULL,
        `state_id` INT(20) NOT NULL,
        `agent_number` INT NOT NULL,
        `unique_number` VARCHAR(255) NOT NULL,
        `agent_type` ENUM("individual", "company") NOT NULL,
        `status` ENUM("1", "0") NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}


// Create table `manifests`
if (!$CI->db->table_exists(db_prefix() . '_manifests')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_manifests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `date` DATE NOT NULL,
        `sender` VARCHAR(255) NOT NULL,
        `rcvr` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NOT NULL,
        `awb_number` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `pcs` INT NOT NULL,
        `kgs` DECIMAL(10, 2) NOT NULL,
        `rate` DECIMAL(10, 2) NOT NULL,
        `aed` DECIMAL(10, 2) NOT NULL,
        `usd` DECIMAL(10, 2) NOT NULL,
        `pack` VARCHAR(50) NOT NULL,
        `dest` VARCHAR(255) NOT NULL,
        `rmks` TEXT,
        `manifest_number` VARCHAR(255) NOT NULL,
        `flight_number` VARCHAR(255) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `destination_id` INT NOT NULL,
        `created_at` DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Create table `tbl_courier_audit_logs`
if (!$CI->db->table_exists(db_prefix() . '_courier_audit_logs')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_audit_logs` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `staff_id` INT NOT NULL,
        `country_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
}

// Check if the table exists
if (!$CI->db->table_exists(db_prefix() . '_destination_offices')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_destination_offices` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `company_name` VARCHAR(255) NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `street_address` VARCHAR(255) NOT NULL,
        `landmark` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(15) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}


// Create table `tbl_country_states`
if (!$CI->db->table_exists(db_prefix() . '_country_states')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_country_states` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `country_id` INT NOT NULL,
        `country_code` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`country_id`) REFERENCES `' . db_prefix() . 'countries`(`country_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');

    //add states
    addStates($CI);
}


//function to add states
if (!function_exists('addStates')) :
function addStates($CI)
{
    // Load states from JSON file
    $jsonFilePath = __DIR__ . '/assets/states.json'; // Update the path to your JSON file
    $jsonContent = file_get_contents($jsonFilePath);
    $states = json_decode($jsonContent, true);

    // Check for decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'Failed to decode JSON: ' . json_last_error_msg());
        return;
    }

    // Define chunk size (500 states per chunk)
    $chunkSize = 500;
    $totalStates = count($states);
    $chunks = array_chunk($states, $chunkSize);

    // Use transaction to ensure data consistency
    $CI->db->trans_start();

    foreach ($chunks as $chunk) {
        $CI->db->insert_batch(db_prefix() . '_country_states', $chunk);
    }

    // Complete the transaction
    $CI->db->trans_complete();

    // Check if the transaction was successful
    if ($CI->db->trans_status() === FALSE) {
        log_message('error', 'Failed to insert states data');
    } else {
        log_message('info', 'States data inserted successfully');
    }


}
endif; // end if (!function_exists('addStates'))

// ── Courier schema upgrades ──────────────────────────────────────────────────

// VAT fields on shipments
if (!$CI->db->field_exists('vat_applicable', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `vat_applicable` TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN `vat_rate`       DECIMAL(5,2) NOT NULL DEFAULT 16.00,
        ADD COLUMN `vat_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00
    ');
}

// Round-trip flag on shipments
if (!$CI->db->field_exists('is_round_trip', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `is_round_trip` TINYINT(1) NOT NULL DEFAULT 0
    ');
}

// Fleet assignment link on shipments
if (!$CI->db->field_exists('fleet_assignment_id', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `fleet_assignment_id` INT(11) NULL DEFAULT NULL
    ');
}

// Offloading-specific columns on shipment_stops
if (!$CI->db->field_exists('stop_type', db_prefix() . '_shipment_stops')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_stops`
        ADD COLUMN `stop_type`         ENUM(\'transit\',\'offloading\',\'pickup\') NOT NULL DEFAULT \'transit\',
        ADD COLUMN `odometer_reading`  INT(11) NULL DEFAULT NULL,
        ADD COLUMN `stop_time`         DATETIME NULL DEFAULT NULL,
        ADD COLUMN `handler_name`      VARCHAR(255) NULL DEFAULT NULL
    ');
}

// Seed default courier options if not already set (empty = fall back to Perfex company name)
add_option('courier_logistic_company', '');
add_option('courier_waybill_prefix', '');
add_option('courier_vat_rate', '16');           // default VAT rate %

// ── Courier Quotations table ─────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . '_courier_quotations')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_quotations` (
        `id`          INT NOT NULL AUTO_INCREMENT,
        `shipment_id` INT NOT NULL,
        `quote_number` VARCHAR(50) NOT NULL,
        `valid_until`  DATE NULL,
        `notes`        TEXT NULL,
        `status`       ENUM(\'draft\',\'sent\',\'accepted\',\'rejected\') NOT NULL DEFAULT \'draft\',
        `created_at`   DATETIME,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`shipment_id`) REFERENCES `' . db_prefix() . '_shipments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// Migrate old hardcoded defaults so they fall back to the Perfex company name
if (get_option('courier_logistic_company') === 'GO Shipping') {
    update_option('courier_logistic_company', '');
}
if (get_option('courier_waybill_prefix') === 'GOSH') {
    update_option('courier_waybill_prefix', '');
}

// signature_url: allow NULL so pickups can be created before signature is captured
$CI->db->query("ALTER TABLE `" . db_prefix() . "_pickups`
    MODIFY COLUMN `signature_url` TEXT NULL DEFAULT NULL");

// ── Drop blocking UNIQUE constraints from existing installs ──────────────────
// The email/phone UNIQUE keys prevent the same staff or company from creating
// more than one pickup/shipment. Drop them if still present.
$upgrade_indexes = [
    ['table' => db_prefix() . '_pickup_contacts',    'index' => 'email'],
    ['table' => db_prefix() . '_shipment_companies', 'index' => 'contact_person_phone_number'],
    ['table' => db_prefix() . '_recipient_companies','index' => 'recipient_contact_person_phone_number'],
];
foreach ($upgrade_indexes as $_idx) {
    $idx_check = $CI->db->query(
        "SELECT COUNT(*) AS cnt FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '" . $_idx['table'] . "'
           AND INDEX_NAME = '" . $_idx['index'] . "'"
    )->row();
    if ($idx_check && $idx_check->cnt > 0) {
        $CI->db->query('ALTER TABLE `' . $_idx['table'] . '` DROP INDEX `' . $_idx['index'] . '`');
    }
}
unset($upgrade_indexes, $_idx, $idx_check);

// Unit price per package for local courier billing
if (!$CI->db->field_exists('unit_price', db_prefix() . '_shipment_packages')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_packages`
        ADD COLUMN `unit_price` DECIMAL(10,2) NULL DEFAULT NULL
    ');
}

// POD (Point of Delivery) per package row — enables multi-POD shipments
if (!$CI->db->field_exists('pod', db_prefix() . '_shipment_packages')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_packages`
        ADD COLUMN `pod` VARCHAR(255) NULL DEFAULT NULL
    ');
}

// Routes for manifest filtering
if (!$CI->db->table_exists(db_prefix() . '_courier_routes')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_routes` (
        `id`          INT NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

if (!$CI->db->table_exists(db_prefix() . '_courier_route_stops')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_route_stops` (
        `id`         INT NOT NULL AUTO_INCREMENT,
        `route_id`   INT NOT NULL,
        `stop_name`  VARCHAR(255) NOT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`route_id`) REFERENCES `' . db_prefix() . '_courier_routes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// Payment terms per shipment
if (!$CI->db->field_exists('payment_terms', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `payment_terms` TEXT NULL DEFAULT NULL
    ');
}

// Special instructions per shipment (entered by client or admin at creation)
if (!$CI->db->field_exists('special_instructions', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `special_instructions` TEXT NULL DEFAULT NULL
    ');
}

// Portal request flag: 1 = submitted via public portal, pending admin confirmation
if (!$CI->db->field_exists('is_portal_request', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `is_portal_request` TINYINT(1) NOT NULL DEFAULT 0
    ');
}

// Quoted amount stored at submission time, used to pre-fill admin confirm form
if (!$CI->db->field_exists('quoted_amount', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `quoted_amount` DECIMAL(10,2) NULL DEFAULT NULL
    ');
}

// Declared value of goods for domestic/local shipments
if (!$CI->db->field_exists('goods_declared_value', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `goods_declared_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00
    ');
}

// Reason a driver gave when cancelling a shipment from the mobile trip page
if (!$CI->db->field_exists('cancel_reason', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `cancel_reason` TEXT NULL DEFAULT NULL
    ');
}

// Free-text note attached to a status change (e.g. a driver's cancellation
// reason) — the history table only ever recorded the bare status_id before.
if (!$CI->db->field_exists('notes', db_prefix() . '_shipment_status_history')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipment_status_history`
        ADD COLUMN `notes` TEXT NULL DEFAULT NULL
    ');
}

// ── Rider PWA — self-service accounts for delivery riders, deliberately a
// separate lightweight identity from tbl_staff (no admin-panel login
// capability). `staff_id` links a rider to a real 'Fleet: Driver' staff
// record — auto-matched by phone number at registration/login — so
// existing driver_id/staff_id assignment fields across shipments, pickups
// and fleet trips keep working unchanged.
if (!$CI->db->table_exists(db_prefix() . '_courier_riders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_riders` (
        `id`            INT NOT NULL AUTO_INCREMENT,
        `name`          VARCHAR(255) NOT NULL,
        `phone`         VARCHAR(30) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `staff_id`      INT NULL DEFAULT NULL,
        `status`        ENUM(\'active\',\'suspended\') NOT NULL DEFAULT \'active\',
        `created_at`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . '_courier_rider_tokens')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . '_courier_rider_tokens` (
        `id`           INT NOT NULL AUTO_INCREMENT,
        `rider_id`     INT NOT NULL,
        `token_hash`   VARCHAR(64) NOT NULL,
        `created_at`   DATETIME NOT NULL,
        `last_used_at` DATETIME NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token_hash` (`token_hash`),
        FOREIGN KEY (`rider_id`) REFERENCES `' . db_prefix() . '_courier_riders`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Live GPS pings from the rider app while a Salibay delivery is "in
// progress" (Start Delivery tapped) — same shape as fleet's
// tbl_fleet_trip_locations, but keyed by shipment_id since the short
// delivery flow has no separate "trip" record.
if (!$CI->db->table_exists(db_prefix() . '_shipment_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . '_shipment_locations` (
        `id`          INT NOT NULL AUTO_INCREMENT,
        `shipment_id` INT NOT NULL,
        `latitude`    DECIMAL(10,7) NOT NULL,
        `longitude`   DECIMAL(10,7) NOT NULL,
        `accuracy`    DECIMAL(10,2) NULL DEFAULT NULL,
        `speed`       DECIMAL(10,2) NULL DEFAULT NULL,
        `recorded_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `shipment_id` (`shipment_id`),
        KEY `shipment_recorded` (`shipment_id`, `recorded_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Opaque per-shipment token for the public, no-login Salibay rider delivery
// link (Shipments::_get_or_create_driver_token()).
if (!$CI->db->field_exists('driver_token', db_prefix() . '_shipments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . '_shipments`
        ADD COLUMN `driver_token` VARCHAR(64) NULL DEFAULT NULL,
        ADD UNIQUE KEY `driver_token` (`driver_token`)
    ');
}

// Default courier type setting
add_option('courier_type', 'international');

// ── Courier Email Templates ───────────────────────────────────────────────────
// create_email_template() is a no-op if the slug already exists — safe to re-run.

create_email_template(
    'Your Invoice {invoice_number} from {company_name}',
    '<p>Dear {recipient_name},</p>
<p>Please find below your invoice details for shipment <strong>{waybill_number}</strong>.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;border:1px solid #e0e0e0;">
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;width:40%;">Invoice #</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{invoice_number}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Waybill #</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{waybill_number}</td></tr>
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Total Amount Due</td><td style="padding:8px 12px;border:1px solid #e0e0e0;font-size:16px;font-weight:bold;color:#2e7d32;">{total_amount}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Due Date</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{due_date}</td></tr>
</table>
<p>Please arrange payment before the due date. For any queries, do not hesitate to contact us.</p>
<p>Thank you for choosing <strong>{company_name}</strong>.</p>',
    'courier',
    'Courier Invoice to Customer',
    'courier_invoice_to_customer'
);

create_email_template(
    'Payment Receipt {receipt_number} - {company_name}',
    '<p>Dear {recipient_name},</p>
<p>We acknowledge receipt of your payment for shipment <strong>{waybill_number}</strong>.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;border:1px solid #e0e0e0;">
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;width:40%;">Receipt #</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{receipt_number}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Waybill #</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{waybill_number}</td></tr>
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Amount Paid</td><td style="padding:8px 12px;border:1px solid #e0e0e0;font-size:16px;font-weight:bold;color:#2e7d32;">{amount_paid}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Payment Mode</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{payment_mode}</td></tr>
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Payment Date</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{payment_date}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Balance Due</td><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">{balance_due}</td></tr>
</table>
<p>Thank you for your payment. <strong>{company_name}</strong></p>',
    'courier',
    'Courier Payment Receipt to Customer',
    'courier_payment_receipt_to_customer'
);

create_email_template(
    'Your Shipment Waybill {waybill_number} - {company_name}',
    '<p>Dear {recipient_name},</p>
<p>Your shipment waybill is ready. Please find the details below.</p>
<table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;border:1px solid #e0e0e0;">
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;width:40%;">Waybill Number</td><td style="padding:8px 12px;border:1px solid #e0e0e0;font-size:18px;font-weight:bold;color:#2e7d32;">{waybill_number}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Sender</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{sender_name}</td></tr>
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Recipient</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{recipient_name}</td></tr>
  <tr><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Shipping Mode</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{shipping_mode}</td></tr>
  <tr style="background:#f5f5f5;"><td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Status</td><td style="padding:8px 12px;border:1px solid #e0e0e0;">{status}</td></tr>
</table>
<p>Use your waybill number <strong>{waybill_number}</strong> to track your shipment.</p>
<p>Thank you for choosing <strong>{company_name}</strong>.</p>',
    'courier',
    'Courier Waybill Notification to Customer',
    'courier_waybill_to_customer'
);

// ── Service Points table ──────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . '_courier_service_points')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_service_points` (
        `id`         INT NOT NULL AUTO_INCREMENT,
        `name`       VARCHAR(255) NOT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// Seed Kenya service points on fresh install (skip if any exist)
$sp_count = $CI->db->count_all(db_prefix() . '_courier_service_points');
if ($sp_count === 0) {
    $kenya_sps = [
        // Original 73 service points
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
        // Additional stops (Routes A–L)
        'Kikuyu','Uthiru','Gilgil','Kodiaga','Nyamasaria','Mtito Andei',
        'Kapsabet','Kaimosi','Chavakali','Soy','Cabanas','Kitengela',
        'Isinya','Athi River','Kajiado','Ngong','Karen','Rongai','Kiserani','Sagana',
    ];
    $sp_rows = [];
    foreach ($kenya_sps as $i => $name) {
        $sp_rows[] = ['name' => $name, 'sort_order' => $i + 1];
    }
    $CI->db->insert_batch(db_prefix() . '_courier_service_points', $sp_rows);
} else {
    // For existing installs: add any missing extra stops
    $_extra_sps = [
        'Kikuyu','Uthiru','Gilgil','Kodiaga','Nyamasaria','Mtito Andei',
        'Kapsabet','Kaimosi','Chavakali','Soy','Cabanas','Kitengela',
        'Isinya','Athi River','Kajiado','Ngong','Karen','Rongai','Kiserani','Sagana',
    ];
    $_max_sp_order = (int)$CI->db->select_max('sort_order')->get(db_prefix() . '_courier_service_points')->row()->sort_order;
    foreach ($_extra_sps as $_esp) {
        $_esp_exists = $CI->db->where('LOWER(name)', strtolower($_esp))->count_all_results(db_prefix() . '_courier_service_points');
        if (!$_esp_exists) {
            $_max_sp_order++;
            $CI->db->insert(db_prefix() . '_courier_service_points', ['name' => $_esp, 'sort_order' => $_max_sp_order]);
        }
    }
    unset($_extra_sps, $_max_sp_order, $_esp, $_esp_exists);
}

// ── Pre-seed Routes A–L (only if no routes exist yet) ────────────────────────
if ($CI->db->count_all(db_prefix() . '_courier_routes') === 0) {
    $_routes_seed = [
        ['Route A — Nairobi to Busia (Via Nakuru & Kisumu)',
         'Via Gitaru, Kangemi, Naivasha, Nakuru, Gilgil, Kericho, Kisumu, Ugunja, Busia and surrounding stops',
         ['Kangemi','Gitaru','Uthiru','Kikuyu','Naivasha','Gilgil','Nakuru','Kericho','Kisumu','Ahero','Awasi','Luanda','Maseno','Sidindi','Nyamasaria','Yala','Ugunja','Sega','Bumala','Busia','Kodiaga']],

        ['Route B — Nairobi to Mombasa',
         'Via Kibwezi, Emali, Mtito Andei',
         ['Kibwezi','Emali','Mtito Andei','Mombasa']],

        ['Route C — Nairobi to Homabay (Via Kisii)',
         'Via Bomet, Narok, Keumbu, Kisii, Rongo, Migori',
         ['Narok','Bomet','Keumbu','Kisii','Kibirigo','Rongo','Migori','Homabay']],

        ['Route D — Nairobi to Malaba (Via Eldoret)',
         'Via Eldoret, Bungoma',
         ['Eldoret','Bungoma','Malaba']],

        ['Route E — Nairobi to Mumias (Via Kakamega)',
         'Via Kapsabet, Kaimosi, Chavakali, Kakamega',
         ['Kapsabet','Kaimosi','Chavakali','Kakamega','Mumias','Musanda']],

        ['Route F — Nairobi to Bondo (Via Siaya)',
         'Via Naivasha, Nakuru, Kisumu, Luanda, Siaya',
         ['Naivasha','Nakuru','Kisumu','Luanda','Siaya','Bondo','Usenge']],

        ['Route G — Nairobi to Kitale (Via Eldoret)',
         'Via Eldoret, Soy, Kimilili, Mosibridge',
         ['Eldoret','Soy','Kimilili','Mosibridge','Kitale']],

        ['Route H — Nairobi to Machakos (Via Athi River)',
         'Via Mlolongo, Kitengela, Kajiado, Machakos',
         ['Cabanas','Mlolongo','Kitengela','Isinya','Athi River','Kajiado','Machakos']],

        ['Route I — Nairobi to Karen',
         'Via Ngong, Karen',
         ['Ngong','Karen']],

        ['Route J — Nairobi to Rongai',
         '',
         ['Rongai','Kiserani']],

        ['Route K — Nairobi to Nyeri (Via Thika)',
         'Via Thika, Makongeni, Ruiru, Mwea',
         ['Thika','Makongeni Thika','Ruiru','Mwea','Nyeri']],

        ['Route L — Nairobi to Meru (Via Thika & Sagana)',
         'Via Thika, Sagana',
         ['Thika','Sagana','Meru']],
    ];

    foreach ($_routes_seed as $_route) {
        $CI->db->insert(db_prefix() . '_courier_routes', [
            'name'        => $_route[0],
            'description' => $_route[1],
        ]);
        $_route_id = $CI->db->insert_id();
        foreach ($_route[2] as $_sort => $_stop) {
            $CI->db->insert(db_prefix() . '_courier_route_stops', [
                'route_id'   => $_route_id,
                'stop_name'  => $_stop,
                'sort_order' => $_sort + 1,
            ]);
        }
    }
    unset($_routes_seed, $_route, $_route_id, $_sort, $_stop);
}

// ── Tariff Zone & Rate tables ─────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . '_courier_tariff_zones')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_tariff_zones` (
        `id`           INT NOT NULL AUTO_INCREMENT,
        `zone_code`    CHAR(1) NOT NULL,
        `name`         VARCHAR(100) NOT NULL,
        `destinations` TEXT NULL,
        `is_available` TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        UNIQUE KEY `zone_code` (`zone_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

if (!$CI->db->table_exists(db_prefix() . '_courier_tariff_rates')) {
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
}

// Seed tariff zones (idempotent — ON DUPLICATE KEY UPDATE)
$CI->db->query("INSERT INTO `" . db_prefix() . "_courier_tariff_zones`
    (`zone_code`,`name`,`destinations`,`is_available`) VALUES
    ('A','Zone A — Nairobi Metro','Nairobi CBD, Westlands, Kasarani, Eastleigh, Embakasi, Githurai, Kangemi, Gitaru, Uthiru, Kikuyu, Karen, Ngong, Pipeline, Kayole, Umoja, Country Bus (Nairobi), Rongai, Kiserani',1),
    ('B','Zone B — Greater Nairobi','Thika, Ruiru, Makongeni Thika, Athi River, Mlolongo, Cabanas, Kitengela, Isinya, Kajiado, Machakos, Tala, Wote',1),
    ('C','Zone C — Central & Rift Valley','Naivasha, Gilgil, Nakuru, Kericho, Nyeri, Mwea, Embu, Meru, Kirinyaga, Sagana, Narok, Bomet, Londiani',1),
    ('D','Zone D — Coast & Mid-Western','Mombasa, Voi, Kibwezi, Emali, Sultan Hamud, Mtito Andei, Kisumu, Ahero, Awasi, Katito, Muhoroni, Kisii, Keumbu, Kibirigo, Homabay, Migori, Rongo',1),
    ('E','Zone E — Far Western Kenya','Busia, Bumala, Sega, Ugunja, Sidindi, Yala, Luanda, Maseno, Nyamasaria, Kodiaga, Siaya, Bondo, Usenge, Ngiya, Eldoret, Kitale, Kimilili, Mosibridge, Soy, Bungoma, Malaba, Kakamega, Mumias, Musanda, Kapsabet, Kaimosi, Chavakali, Sabatia',1),
    ('F','Zone F — Special / International','International destinations and remote areas not covered by Zones A–E. Contact us directly for pricing.',0)
    ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),`destinations`=VALUES(`destinations`),`is_available`=VALUES(`is_available`);");

// Seed tariff rates (only if table is completely empty)
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

// Create "Courier: Agent" role with safe default permissions if it does not exist
$existing_role = $CI->db->query('SELECT roleid FROM ' . db_prefix() . 'roles WHERE name = "Courier: Agent"')->row();
if (!$existing_role) {
    $default_agent_permissions = serialize([
        'courier-shipments' => ['view_own_shipments', 'create_shipments', 'create_shipment_road', 'create_shipment_courier', 'create_shipment_domestic'],
        'courier-pickups'   => ['view_own_pickups', 'create_pickups'],
        'courier-waybills'  => ['view_own_waybills'],
        'courier-invoices'  => ['view_own_invoices', 'generate_payment', 'view_receipts'],
    ]);
    $CI->db->query('INSERT INTO ' . db_prefix() . 'roles (name, permissions) VALUES ("Courier: Agent", ' . $CI->db->escape($default_agent_permissions) . ')');
}
