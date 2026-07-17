<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicle_groups')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_vehicle_groups (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicle_types')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_vehicle_types (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicles')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_vehicles (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `vehicle_type_id` int(11) NULL,
      `vehicle_group_id` int(11) NULL,
      `status` TEXT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('fleet_is_driver' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `fleet_is_driver` INT(11) NULL DEFAULT 0;");
}


if (!$CI->db->field_exists('model' ,db_prefix() . 'fleet_vehicles')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_vehicles`
    ADD COLUMN `model` TEXT NULL,
    ADD COLUMN `year` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('width' ,db_prefix() . 'fleet_vehicles')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_vehicles`
    ADD COLUMN `width` TEXT NULL,
    ADD COLUMN `height` TEXT NULL,
    ADD COLUMN `length` TEXT NULL,
    ADD COLUMN `interior_volume` TEXT NULL,
    ADD COLUMN `passenger_volume` TEXT NULL,
    ADD COLUMN `cargo_volume` TEXT NULL,
    ADD COLUMN `ground_clearance` TEXT NULL,
    ADD COLUMN `bed_length` TEXT NULL,
    ADD COLUMN `curb_weight` TEXT NULL,
    ADD COLUMN `gross_vehicle_weight_rating` TEXT NULL,
    ADD COLUMN `towing_capacity` TEXT NULL,
    ADD COLUMN `max_payload` TEXT NULL,
    ADD COLUMN `epa_city` TEXT NULL,
    ADD COLUMN `epa_highway` TEXT NULL,
    ADD COLUMN `epa_combined` TEXT NULL,
    ADD COLUMN `oil_capacity` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('in_service_date' ,db_prefix() . 'fleet_vehicles')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_vehicles`
    ADD COLUMN `in_service_date` DATE NULL,
    ADD COLUMN `in_service_odometer` INT(11) NULL,
    ADD COLUMN `estimated_service_life_in_months` TEXT NULL,
    ADD COLUMN `estimated_service_life_in_meter` TEXT NULL,
    ADD COLUMN `estimated_resale_value` TEXT NULL,
    ADD COLUMN `out_of_service_date` DATE NULL,
    ADD COLUMN `out_of_service_odometer` INT(11) NULL;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_driver_documents')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_driver_documents (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `driver_id` int(11) NOT NULL DEFAULT 0,
      `subject` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicle_assignments')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_vehicle_assignments (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `driver_id` int(11) NOT NULL,
      `vehicle_id` int(11) NOT NULL,
      `start_time` DATETIME NULL,
      `starting_odometer` FLOAT NULL,
      `end_time` DATETIME NULL,
      `ending_odometer` FLOAT NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_maintenances')) {
  $CI->db->query('CREATE TABLE ' . db_prefix() .'fleet_maintenances (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `vehicle_id` INT(11) NULL,
    `supplier_id` INT(11) NULL,
    `maintenance_type` varchar(30) NULL,
    `title` varchar(250) NULL,
    `warranty_improvement` INT(11) NOT NULL DEFAULT 0,
    `start_date` DATE NULL,
    `completion_date` DATE NULL,
    `cost` DECIMAL(15,2) NULL,
    `notes` text NULL,
    `date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_garages')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_garages (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `country` int(11) NOT NULL DEFAULT '0',
      `city` varchar(100) NULL,
      `zip` varchar(15) NULL,
      `state` varchar(50) NULL,
      `address` TEXT NULL,
      `notes` TEXT NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_fuel_history')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_fuel_history (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `vehicle_id` INT(11) NULL,
      `vendor_id` INT(11) NULL,
      `fuel_time` DATETIME NOT NULL,
      `odometer` INT(11) NULL,
      `gallons` TEXT NULL,
      `price` DECIMAL(15,2) NULL,
      `fuel_type` TEXT NULL,
      `reference` TEXT NULL,
      `notes` TEXT NULL,
      `addedfrom` INT(11) NULL,
      `datecreated` DATETIME NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_inspection_forms')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspection_forms (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `color` TEXT NULL,
      `description` TEXT NULL,
      `addedfrom` INT(11) NULL,
      `datecreated` DATETIME NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('slug' ,db_prefix() . 'fleet_inspection_forms')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_inspection_forms`
    ADD COLUMN `slug` TEXT NULL,
    ADD COLUMN `hash` TEXT NULL;
    ");
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_inspection_question_forms')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspection_question_forms (
      `questionid` int(11) NOT NULL AUTO_INCREMENT,
      `rel_id` int(11) NOT NULL,
      `rel_type` varchar(20) DEFAULT NULL,
      `question` mediumtext NOT NULL,
      `required` tinyint(1) NOT NULL DEFAULT '0',
      `question_order` int(11) NOT NULL,
      PRIMARY KEY (`questionid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_inspection_question_box')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspection_question_box (
      `boxid` int(11) NOT NULL AUTO_INCREMENT,
      `boxtype` varchar(10) NOT NULL,
      `questionid` int(11) NOT NULL,
      PRIMARY KEY (`boxid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_inspection_question_box_description')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspection_question_box_description (
      `questionboxdescriptionid` int(11) NOT NULL AUTO_INCREMENT,
      `description` mediumtext NOT NULL,
      `boxid` mediumtext NOT NULL,
      `questionid` int(11) NOT NULL,
      PRIMARY KEY (`questionboxdescriptionid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_inspections')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspections (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `vehicle_id` INT(11) NULL,
      `inspection_form_id` INT(11) NULL,
      `status` INT(11) NULL,
      `addedfrom` INT(11) NULL,
      `datecreated` DATETIME NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_inspection_results')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_inspection_results (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `inspection_id` INT(11) NULL,
      `boxid` INT(11) NULL,
      `boxdescriptionid` INT(11) NULL,
      `questionid` INT(11) NULL,
      `answer` TEXT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('commodity_group_code' ,db_prefix() . 'items_groups')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items_groups`
  ADD COLUMN `commodity_group_code` varchar(100) NULL AFTER `name`,
  ADD COLUMN `order` int(10) NULL AFTER `commodity_group_code`,
  ADD COLUMN `display` int(1)  NULL AFTER `order` ,
  ADD COLUMN `note` text NULL AFTER `display`
  ;");
}

$i = count($CI->db->query('Select * from ' . db_prefix() . 'items_groups where name = "Fleet: Parts" and commodity_group_code = "FLEET_PARTS"')->result_array());
if ($i == 0) {
  $CI->db->query("INSERT INTO `" . db_prefix() . "items_groups` (`name`, `commodity_group_code`, `display`, `order`, `note`) VALUES ('Fleet: Parts', 'FLEET_PARTS', '1', '10', '');");
}

$i = count($CI->db->query('Select * from ' . db_prefix() . 'roles where name = "Fleet: Driver"')->result_array());
if ($i == 0) {
  $CI->db->query("INSERT INTO `" . db_prefix() . "roles` (`name`) VALUES ('Fleet: Driver');");
}


if (!$CI->db->field_exists('vin' ,db_prefix() . 'fleet_vehicles')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_vehicles`
    ADD COLUMN `vin` TEXT NULL,
    ADD COLUMN `license_plate` TEXT NULL,
    ADD COLUMN `make` TEXT NULL,
    ADD COLUMN `trim` TEXT NULL,
    ADD COLUMN `registration_state` TEXT NULL,
    ADD COLUMN `ownership` TEXT NULL,
    ADD COLUMN `color` TEXT NULL,
    ADD COLUMN `body_type` TEXT NULL,
    ADD COLUMN `body_subtype` TEXT NULL,
    ADD COLUMN `msrp` TEXT NULL,
    ADD COLUMN `purchase_vendor` INT(11) NULL,
    ADD COLUMN `purchase_date` DATE NULL,
    ADD COLUMN `purchase_price` DECIMAL(15,2) NULL,
    ADD COLUMN `odometer` INT(11) NULL,
    ADD COLUMN `notes` TEXT NULL,
    ADD COLUMN `expiration_date` DATE NULL,
    ADD COLUMN `max_meter_value` TEXT NULL,
    ADD COLUMN `engine_summary` TEXT NULL,
    ADD COLUMN `engine_brand` TEXT NULL,
    ADD COLUMN `aspiration` TEXT NULL,
    ADD COLUMN `block_type` TEXT NULL,
    ADD COLUMN `bore` TEXT NULL,
    ADD COLUMN `cam_type` TEXT NULL,
    ADD COLUMN `compression` TEXT NULL,
    ADD COLUMN `cylinders` TEXT NULL,
    ADD COLUMN `displacement` TEXT NULL,
    ADD COLUMN `fuel_induction` TEXT NULL,
    ADD COLUMN `max_hp` TEXT NULL,
    ADD COLUMN `max_torque` TEXT NULL,
    ADD COLUMN `redline_rpm` TEXT NULL,
    ADD COLUMN `stroke` TEXT NULL,
    ADD COLUMN `valves` TEXT NULL,
    ADD COLUMN `transmission_summary` TEXT NULL,
    ADD COLUMN `transmission_brand` TEXT NULL,
    ADD COLUMN `transmission_type` TEXT NULL,
    ADD COLUMN `transmission_gears` TEXT NULL,
    ADD COLUMN `drive_type` TEXT NULL,
    ADD COLUMN `brake_system` TEXT NULL,
    ADD COLUMN `front_track_width` TEXT NULL,
    ADD COLUMN `rear_track_width` TEXT NULL,
    ADD COLUMN `wheelbase` TEXT NULL,
    ADD COLUMN `front_wheel_diameter` TEXT NULL,
    ADD COLUMN `rear_wheel_diameter` TEXT NULL,
    ADD COLUMN `rear_axle` TEXT NULL,
    ADD COLUMN `front_tire_type` TEXT NULL,
    ADD COLUMN `front_tire_psi` TEXT NULL,
    ADD COLUMN `rear_tire_type` TEXT NULL,
    ADD COLUMN `rear_tire_psi` TEXT NULL,
    ADD COLUMN `fuel_type` TEXT NULL,
    ADD COLUMN `fuel_quality` TEXT NULL,
    ADD COLUMN `fuel_tank_1_capacity` TEXT NULL,
    ADD COLUMN `fuel_tank_2_capacity` TEXT NULL;
    ");

}

if (!$CI->db->table_exists(db_prefix() . 'fleet_benefit_and_penalty')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_benefit_and_penalty (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `subject` TEXT NULL,
      `criteria_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `type` TEXT NULL,
      `date` DATE NULL,
      `benefit_formality` TEXT NULL,
      `reward` DECIMAL(15,2) NULL,
      `penalty_formality` TEXT NULL,
      `amount_of_damage` DECIMAL(15,2) NULL,
      `amount_of_compensation` DECIMAL(15,2) NULL,
      `notes` TEXT NULL,
      `addedfrom` INT(11) NULL,
      `datecreated` DATETIME NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_criterias')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_criterias (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_bookings')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_bookings (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `subject` TEXT NOT NULL,
      `contactid` INT(11) NULL,
      `userid` INT(11) NULL,
      `approved` INT(11) NOT NULL DEFAULT '0',
      `status` VARCHAR(45) NOT NULL DEFAULT 'new',
      `delivery_date` DATE NULL,
      `phone` TEXT NOT NULL,
      `receipt_address` TEXT NOT NULL,
      `delivery_address` TEXT NOT NULL,
      `note` TEXT NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('invoice_id' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `invoice_id` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('admin_note' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `admin_note` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('number' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `number` TEXT NULL,
    ADD COLUMN `amount` DECIMAL(15,2) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('invoice_hash' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `invoice_hash` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('invoice_id' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `invoice_id` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_maintenance_teams')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_maintenance_teams (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `staffid` INT(11) NULL,
      `garage_id` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('garage_id' ,db_prefix() . 'fleet_maintenances')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_maintenances`
    ADD COLUMN `garage_id` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_logbooks')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_logbooks (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `vehicle_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `booking_id` INT(11) NULL,
      `odometer` INT(11) NULL,
      `status` VARCHAR(45) NOT NULL DEFAULT 'new',
      `date` DATE NULL,
      `description` TEXT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('name' ,db_prefix() . 'fleet_logbooks')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_logbooks`
    ADD COLUMN `name` TEXT NOT NULL;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_time_cards')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_time_cards (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `logbook_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `start_time` TEXT NOT NULL,
      `end_time` TEXT NOT NULL,
      `notes` TEXT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_insurances')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_insurances (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `vehicle_id` INT(11) NULL,
      `insurance_category_id` INT(11) NULL,
      `insurance_type_id` INT(11) NULL,
      `name` TEXT NULL,
      `status` TEXT NULL,
      `start_date` DATE NOT NULL,
      `end_date` DATE NOT NULL,
      `description` TEXT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('amount' ,db_prefix() . 'fleet_insurances')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_insurances`
    ADD COLUMN `amount` DECIMAL(15,2) NOT NULL;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_insurance_categories')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_insurance_categories (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_insurance_types')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_insurance_types (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_events')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_events (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `subject` TEXT NOT NULL,
      `vehicle_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `event_type` TEXT NOT NULL,
      `event_time` DATETIME NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_work_orders')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_work_orders (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `subject` TEXT NOT NULL,
      `number` TEXT NULL,
      `vehicle_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `vendor_id` INT(11) NULL,
      `invoice_id` INT(11) NULL,
      `purchase_order_id` INT(11) NULL,
      `status` VARCHAR(45) NOT NULL DEFAULT 'open',
      `issue_date` DATE NULL,
      `start_date` DATE NULL,
      `complete_date` DATE NULL,
      `odometer_in` INT(11) NULL,
      `odometer_out` INT(11) NULL,
      `total` DECIMAL(15,2) NULL,
      `work_requested` TEXT NULL,
      `notes` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_work_order_details')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_work_order_details (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `work_order_id` INT(11) NULL,
      `part_id` INT(11) NULL,
      `item_name` TEXT NULL,
      `qty` INT(11) NULL,
      `price` DECIMAL(15,2) NULL,
      `total` DECIMAL(15,2) NULL,
      `description` TEXT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('parts' ,db_prefix() . 'fleet_maintenances')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_maintenances`
    ADD COLUMN `parts` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('from_fleet' ,db_prefix() . 'invoices')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "invoices`
    ADD COLUMN `from_fleet` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('from_fleet' ,db_prefix() . 'expenses')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "expenses`
    ADD COLUMN `from_fleet` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('rating' ,db_prefix() . 'fleet_bookings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
    ADD COLUMN `rating` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `comments` TEXT NULL;
    ");
}

if (!$CI->db->field_exists('expense_id' ,db_prefix() . 'fleet_work_orders')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_work_orders`
    ADD COLUMN `expense_id` INT(11) NOT NULL DEFAULT 0;
    ");
}

$i = count($CI->db->query('Select * from ' . db_prefix() . 'expenses_categories where name = "Fleet: Work Order"')->result_array());
if ($i == 0) {
  $CI->db->query("INSERT INTO `" . db_prefix() . "expenses_categories` (`name`) VALUES ('Fleet: Work Order');");
}


if (!$CI->db->field_exists('type' ,db_prefix() . 'fleet_driver_documents')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_driver_documents`
    ADD COLUMN `type` VARCHAR(45) NOT NULL DEFAULT 'driver',
    ADD COLUMN `vehicle_id` INT(11) NOT NULL DEFAULT 0;
    ");
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_part_groups')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_part_groups (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_part_types')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_part_types (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_parts')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_parts (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `part_type_id` INT(11) NULL,
      `brand` TEXT NULL,
      `model` TEXT NULL,
      `serial_number` TEXT NULL,
      `vehicle_id` INT(11) NOT NULL DEFAULT 0,
      `driver_id` INT(11) NOT NULL DEFAULT 0,
      `part_group_id` INT(11) NULL,
      `status` TEXT NULL,
      `purchase_vendor` INT(11) NULL,
      `purchase_date` DATE NULL,
      `purchase_price` DECIMAL(15,2) NULL,
      `warranty_expiration_date` DATE NULL,
      `purchase_comments` TEXT NULL,
      `in_service_date` DATE NULL,
      `estimated_service_life_in_months` INT(11) NULL,
      `estimated_resale_value` DECIMAL(15,2) NULL,
      `out_of_service_date` DATE NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_part_histories')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_part_histories (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `part_id` INT(11) NOT NULL,
      `type` TEXT NOT NULL,
      `vehicle_id` INT(11) NULL,
      `driver_id` INT(11) NULL,
      `start_time` TEXT NULL,
      `end_time` TEXT NULL,
      `start_by` INT(11) NULL,
      `end_by` INT(11) NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('parts' ,db_prefix() . 'fleet_work_orders')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_work_orders`
    ADD COLUMN `parts` TEXT NULL;
    ");
}


if (!$CI->db->table_exists(db_prefix() . 'fleet_insurance_status')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_insurance_status (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_insurance_company')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_insurance_company (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// ── Fleet schema upgrades ─────────────────────────────────────────────────────

// Trip association + type on fuel_history
if (!$CI->db->field_exists('trip_type', db_prefix() . 'fleet_fuel_history')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_fuel_history`
        ADD COLUMN `trip_type`     ENUM('pre_trip','post_trip','regular') NOT NULL DEFAULT 'regular',
        ADD COLUMN `assignment_id` INT(11) NULL DEFAULT NULL
    ");
}

// Odometer after trip on fuel_history (for post-trip record)
if (!$CI->db->field_exists('odometer_after', db_prefix() . 'fleet_fuel_history')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_fuel_history`
        ADD COLUMN `odometer_after` INT(11) NULL DEFAULT NULL
    ");
}

// Approval workflow fields on fuel_history
if (!$CI->db->field_exists('approved_by', db_prefix() . 'fleet_fuel_history')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_fuel_history`
        ADD COLUMN `approved_by`      INT(11) NULL DEFAULT NULL,
        ADD COLUMN `declined_by`      INT(11) NULL DEFAULT NULL,
        ADD COLUMN `checked_by`       INT(11) NULL DEFAULT NULL,
        ADD COLUMN `decline_reason`   TEXT    NULL DEFAULT NULL
    ");
}

// Courier shipment link on fleet_bookings
if (!$CI->db->field_exists('courier_shipment_id', db_prefix() . 'fleet_bookings')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_bookings`
        ADD COLUMN `courier_shipment_id` INT(11) NULL DEFAULT NULL
    ");
}

// Courier shipment link on fleet_vehicle_assignments
if (!$CI->db->field_exists('courier_shipment_id', db_prefix() . 'fleet_vehicle_assignments')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_vehicle_assignments`
        ADD COLUMN `courier_shipment_id` INT(11) NULL DEFAULT NULL,
        ADD COLUMN `trip_notes`          TEXT NULL DEFAULT NULL
    ");
}


if (!$CI->db->field_exists('insurance_company_id' ,db_prefix() . 'fleet_insurances')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_insurances`
    ADD COLUMN `insurance_company_id` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `insurance_status_id` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicle_histories')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "fleet_vehicle_histories (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `vehicle_id` INT(11) NOT NULL,
      `type` TEXT NOT NULL,
      `from_value` TEXT NULL,
      `to_value` TEXT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('is_fail' ,db_prefix() . 'fleet_inspection_question_box_description')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_inspection_question_box_description`
    ADD COLUMN `is_fail` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('recurring' ,db_prefix() . 'fleet_inspection_forms')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_inspection_forms`
    ADD COLUMN `recurring` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `recurring_type` VARCHAR(10) NULL,
    ADD COLUMN `custom_recurring` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `cycles` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `total_cycles` INT(11) NOT NULL DEFAULT 0;
    ");
}

if (!$CI->db->field_exists('recurring' ,db_prefix() . 'fleet_inspections')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "fleet_inspections`
    ADD COLUMN `recurring` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `recurring_type` VARCHAR(10) NULL,
    ADD COLUMN `custom_recurring` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `cycles` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `total_cycles` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `is_recurring_from` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `last_recurring_date` DATE NULL;
    ");
}

// ── Default seed data ────────────────────────────────────────────────────────
$now = date('Y-m-d H:i:s');

// Vehicle Groups
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_vehicle_groups')->row()->c === 0) {
    $vgroups = [
        ['Light Commercial Vehicles', 'Vans, pickups and small trucks under 3.5 tonnes GVW'],
        ['Medium Commercial Vehicles', 'Box trucks and rigid lorries 3.5–12 tonnes GVW'],
        ['Heavy Commercial Vehicles', 'Semi-trucks, tractor-trailers and articulated lorries over 12 tonnes GVW'],
        ['Passenger Vehicles', 'Cars, minibuses and people carriers for staff transport'],
        ['Motorcycles & Scooters', 'Two-wheeled vehicles for last-mile delivery or courier runs'],
        ['Special Purpose Vehicles', 'Cranes, tankers, refrigerated trucks and other specialised vehicles'],
        ['Off-Road & Construction', 'Excavators, bulldozers, graders and heavy plant machinery'],
        ['Trailers & Semi-Trailers', 'Drawn equipment without their own motive power'],
        ['Electric & Hybrid Vehicles', 'Battery-electric and hybrid-electric fleet units'],
        ['Agricultural Vehicles', 'Tractors, harvesters and farm transport'],
    ];
    foreach ($vgroups as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_vehicle_groups` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Vehicle Types
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_vehicle_types')->row()->c === 0) {
    $vtypes = [
        ['Sedan / Saloon',       'Standard four-door passenger car'],
        ['SUV / 4x4',            'Sport utility vehicle with optional all-wheel drive'],
        ['Pickup Truck',         'Light truck with open cargo bed'],
        ['Minivan / People Carrier', 'Multi-seat passenger van'],
        ['Panel Van',            'Enclosed cargo van with no rear side windows'],
        ['Box Truck / Rigid Lorry', 'Fixed enclosed cargo body on a rigid chassis'],
        ['Curtainsider Truck',   'Rigid or articulated truck with sliding curtain sides'],
        ['Flatbed Truck',        'Open platform bed for oversized or heavy loads'],
        ['Refrigerated Truck',   'Temperature-controlled cargo vehicle (reefer)'],
        ['Tipper / Dump Truck',  'Hydraulic tipping bed for bulk loose materials'],
        ['Tanker Truck',         'Cylindrical tank for liquids, gases or dry bulk'],
        ['Tractor Unit / Semi',  'Prime mover designed to pull semi-trailers'],
        ['Semi-Trailer',         'Unpowered trailer with rear axle(s) only'],
        ['Curtain Semi-Trailer', 'Semi-trailer with sliding curtain sides'],
        ['Refrigerated Trailer', 'Temperature-controlled semi-trailer'],
        ['Flatbed Trailer',      'Open platform semi-trailer'],
        ['Tanker Trailer',       'Liquid or gas tank semi-trailer'],
        ['Lowboy / Lowloader',   'Low deck trailer for tall or heavy plant machinery'],
        ['Car Transporter',      'Multi-level trailer for vehicle haulage'],
        ['Motorcycle',           'Two-wheeled powered vehicle'],
        ['Scooter / Moped',      'Low-powered two-wheeler for urban delivery'],
        ['Electric Van',         'Battery-electric enclosed cargo van'],
        ['Electric Car',         'Battery-electric passenger vehicle'],
        ['Minibus (9–16 seats)', 'Small bus for group transport'],
        ['Coach / Bus',          'Large passenger road vehicle over 16 seats'],
        ['Ambulance',            'Emergency medical transport vehicle'],
        ['Fire Truck',           'Emergency fire-fighting vehicle'],
        ['Crane Truck',          'Vehicle-mounted hydraulic crane'],
        ['Forklift',             'Warehouse or yard forklift truck'],
        ['Excavator / Digger',   'Tracked or wheeled earth-moving machine'],
    ];
    foreach ($vtypes as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_vehicle_types` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Inspection Forms
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_inspection_forms')->row()->c === 0) {
    $iforms = [
        ['Pre-Trip Inspection',         '#28a745', 'Daily driver walkaround check completed before the vehicle departs',  'pre_trip'],
        ['Post-Trip Inspection',        '#1565c0', 'End-of-day driver check after the vehicle returns to base',           'post_trip'],
        ['Weekly Safety Check',         '#f57c00', 'Comprehensive weekly safety and fluid-level inspection by workshop',   'weekly'],
        ['Monthly Maintenance Check',   '#6a1b9a', 'Scheduled monthly inspection covering all major systems',             'monthly'],
        ['Annual Roadworthiness Check', '#b71c1c', 'Full annual compliance and roadworthiness inspection',                'annual'],
        ['Accident / Damage Report',    '#c62828', 'Post-incident damage assessment and photographic record',             'accident'],
        ['Tyre Inspection',             '#37474f', 'Dedicated tyre depth, pressure and condition check',                  'tyre'],
        ['Engine & Fluids Check',       '#00695c', 'Inspection of engine oil, coolant, brake fluid and power steering',   'engine'],
        ['Brake System Inspection',     '#e53935', 'Detailed brake pad, disc, drum and hydraulic line inspection',        'brake'],
        ['Electrical Systems Check',    '#fdd835', 'Lights, indicators, battery, alternator and wiring inspection',       'electrical'],
        ['Refrigeration Unit Check',    '#0288d1', 'Temperature log verification and reefer unit inspection',             'reefer'],
        ['Load / Cargo Securement Check','#4e342e', 'Verification that load is within limits and properly secured',       'cargo'],
    ];
    foreach ($iforms as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_inspection_forms` (`name`,`color`,`description`,`slug`,`addedfrom`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $CI->db->escape_str($r[2]) . "','" . $CI->db->escape_str($r[3]) . "',1,'" . $now . "')");
    }
}

// Criterias (driver performance evaluation criteria)
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_criterias')->row()->c === 0) {
    $criterias = [
        ['Punctuality',              'Driver arrives at pick-up and delivery points on time'],
        ['Fuel Efficiency',          'Fuel consumption relative to distance and load benchmarks'],
        ['Safe Driving',             'Adherence to speed limits, following distance and traffic laws'],
        ['Vehicle Care',             'Condition of assigned vehicle; cleanliness and minor damage prevention'],
        ['Route Compliance',         'Following the assigned or optimal route without unauthorised detours'],
        ['Documentation Accuracy',   'Correct completion of waybills, delivery notes and inspection forms'],
        ['Customer Interaction',     'Professional and courteous conduct with clients and recipients'],
        ['Cargo Handling',           'Safe loading, securing and unloading of goods without damage'],
        ['Incident Rate',            'Frequency of accidents, near-misses and traffic violations'],
        ['Attendance & Reliability', 'Absence rate, shift adherence and availability for scheduled runs'],
        ['Communication',            'Timely status updates and responsive communication with dispatch'],
        ['Hours of Service Compliance', 'Compliance with legal driving-hours and rest-period regulations'],
    ];
    foreach ($criterias as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_criterias` (`name`,`description`,`datecreated`,`addedfrom`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "',1)");
    }
}

// Insurance Categories
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_insurance_categories')->row()->c === 0) {
    $icats = [
        ['Motor Vehicle Insurance',  'Cover for the vehicle itself — collision, fire, theft, damage'],
        ['Cargo / Goods in Transit', 'Cover for goods being transported against loss or damage'],
        ['Third-Party Liability',    'Cover for injury or property damage caused to third parties'],
        ['Employer\'s Liability',    'Cover for employee injuries sustained while operating vehicles'],
        ['Public Liability',         'Cover for injury or damage to members of the public'],
        ['Breakdown & Recovery',     'Roadside assistance and recovery from breakdown events'],
        ['Personal Accident',        'Cover for driver and occupant personal injury or death'],
        ['Fleet Umbrella Policy',    'Blanket policy covering the entire fleet under one agreement'],
    ];
    foreach ($icats as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_insurance_categories` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Insurance Types
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_insurance_types')->row()->c === 0) {
    $itypes = [
        ['Comprehensive',                  'Full cover including own damage, third-party, fire and theft'],
        ['Third Party Only',               'Statutory minimum — covers third-party injury and property damage only'],
        ['Third Party, Fire & Theft',      'Third-party plus fire and theft cover for the insured vehicle'],
        ['All-Risk Cargo',                 'Broad cargo cover for all perils unless specifically excluded'],
        ['Named-Perils Cargo',             'Cargo cover limited to perils explicitly listed in the policy'],
        ['Liability Only',                 'Covers legal liability to third parties; no vehicle own-damage'],
        ['Agreed Value',                   'Payout is a pre-agreed sum regardless of depreciation'],
        ['Market Value',                   'Payout reflects current market value at time of loss'],
        ['Fleet Block Policy',             'Single policy covering all vehicles, often with fleet discounts'],
        ['Temporary / Short-Term Cover',   'Cover issued for a defined short period (days to weeks)'],
        ['Extended Warranty',              'Mechanical and electrical breakdown cover beyond standard warranty'],
    ];
    foreach ($itypes as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_insurance_types` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Insurance Status
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_insurance_status')->row()->c === 0) {
    $istatus = [
        ['Active',            'Policy is current and providing cover'],
        ['Expired',           'Policy period has ended and cover has lapsed'],
        ['Pending Renewal',   'Policy is approaching expiry and renewal is in progress'],
        ['Cancelled',         'Policy was cancelled before the end of the policy period'],
        ['Suspended',         'Cover temporarily suspended, e.g. due to non-payment of premium'],
        ['Claimed',           'An active claim has been lodged against this policy'],
        ['Under Review',      'Policy or claim is currently under insurer review'],
        ['Lapsed',            'Cover lapsed due to missed premium payment'],
    ];
    foreach ($istatus as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_insurance_status` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Part Groups
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_part_groups')->row()->c === 0) {
    $pgroups = [
        ['Engine',              'All components directly related to the engine block and power output'],
        ['Fuel System',         'Fuel tank, pump, injectors, carburettor and related lines'],
        ['Exhaust System',      'Exhaust manifold, catalytic converter, DPF, silencer and tailpipe'],
        ['Cooling System',      'Radiator, coolant hoses, thermostat, water pump and cooling fans'],
        ['Electrical System',   'Battery, alternator, starter motor, wiring looms and fuse boxes'],
        ['Lighting',            'Headlights, tail lights, indicators, work lights and interior lighting'],
        ['Transmission',        'Gearbox, clutch, torque converter, driveshafts and differentials'],
        ['Brakes',              'Brake pads, discs, drums, callipers, master cylinder and brake lines'],
        ['Steering',            'Steering rack, column, tie rods, ball joints and power-steering components'],
        ['Suspension',          'Springs, shock absorbers, struts, control arms and anti-roll bars'],
        ['Tyres & Wheels',      'Tyres, rims, wheel nuts, valve stems and wheel spacers'],
        ['Body & Cabin',        'Doors, panels, bumpers, glass, mirrors and cab fittings'],
        ['HVAC',                'Heating, ventilation and air-conditioning components'],
        ['Hydraulics',          'Hydraulic pumps, cylinders, hoses and control valves (tippers, cranes)'],
        ['Safety Equipment',    'Fire extinguishers, first aid kits, warning triangles and seat belts'],
        ['Cargo / Load System', 'Curtains, straps, load locks, tail-lifts and ramp equipment'],
        ['Refrigeration Unit',  'Reefer compressor, condenser, evaporator and temperature controls'],
        ['Fifth Wheel & Coupling', 'Fifth wheel plates, kingpins, trailer couplings and landing gear'],
        ['Filters & Fluids',    'Oil, air, fuel and cabin filters; lubricants and service fluids'],
        ['Miscellaneous',       'Sundry parts not falling into a specific category above'],
    ];
    foreach ($pgroups as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_part_groups` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}

// Part Types
if ((int)$CI->db->query('SELECT COUNT(*) c FROM ' . db_prefix() . 'fleet_part_types')->row()->c === 0) {
    $ptypes = [
        // Engine
        ['Engine Oil Filter',         'Cartridge or spin-on filter for engine lubrication oil'],
        ['Air Filter',                'Engine intake air filter element'],
        ['Fuel Filter',               'Inline filter protecting injectors from contaminated fuel'],
        ['Spark Plug',                'Ignition spark plug for petrol/gasoline engines'],
        ['Glow Plug',                 'Pre-heat plug for diesel engine cold starting'],
        ['Timing Belt / Chain',       'Engine timing drive belt or chain and tensioner kit'],
        ['Cambelt Kit',               'Cambelt, tensioner and idler pulley set'],
        ['Engine Gasket Set',         'Full set of engine sealing gaskets (head, sump, rocker cover)'],
        ['Piston & Rings',            'Engine pistons, piston rings and gudgeon pins'],
        ['Crankshaft',                'Engine crankshaft and main bearing shells'],
        ['Camshaft',                  'Engine camshaft and related valve-train components'],
        // Fuel System
        ['Fuel Injector',             'Common-rail or multipoint fuel injector'],
        ['Fuel Pump (Mechanical)',    'Mechanically driven engine fuel pump'],
        ['Fuel Pump (Electric)',      'Electrically driven in-tank fuel pump module'],
        ['Fuel Tank',                 'Main vehicle fuel tank and mounting straps'],
        ['Turbocharger',              'Exhaust-driven turbocharger unit or rebuild kit'],
        // Exhaust & Emissions
        ['Catalytic Converter',       'Three-way catalytic converter for exhaust emission control'],
        ['Diesel Particulate Filter', 'DPF for capturing soot from diesel exhaust'],
        ['EGR Valve',                 'Exhaust gas recirculation valve'],
        ['Exhaust Manifold',          'Cast or fabricated exhaust manifold'],
        ['Silencer / Muffler',        'Exhaust noise attenuation silencer'],
        // Cooling
        ['Radiator',                  'Engine coolant-to-air heat exchanger'],
        ['Thermostat',                'Engine coolant flow thermostat and housing'],
        ['Water Pump',                'Engine-driven coolant circulation pump'],
        ['Coolant Hose',              'Radiator top or bottom coolant hose'],
        ['Cooling Fan',               'Viscous-coupled or electric engine cooling fan'],
        // Electrical
        ['Battery',                   '12V or 24V starting or auxiliary battery'],
        ['Alternator',                'Engine-driven charging alternator'],
        ['Starter Motor',             'Electric engine cranking motor'],
        ['Relay',                     'Electrical load relay (horn, fuel pump, glow plug etc.)'],
        ['Fuse',                      'Blade or glass-tube electrical protection fuse'],
        ['ECU / Engine Control Unit', 'Engine management or body control module'],
        ['Sensor (O2 / Lambda)',      'Exhaust oxygen / lambda sensor'],
        ['Sensor (Temperature)',      'Coolant, intake air or exhaust temperature sensor'],
        ['Sensor (ABS / Wheel Speed)','ABS wheel speed sensor ring and pickup'],
        // Lighting
        ['Headlight Assembly',        'Complete headlight housing unit'],
        ['Tail Light Assembly',       'Complete rear light cluster'],
        ['Indicator / Turn Signal',   'Front or rear direction indicator lamp'],
        ['Work / Spot Light',         'Cab or load-area auxiliary work light'],
        ['LED Light Bar',             'Roof-mounted LED light bar for off-road or emergency use'],
        // Transmission
        ['Clutch Kit',                'Clutch plate, pressure plate and release bearing set'],
        ['Gearbox',                   'Manual or automatic transmission assembly'],
        ['Driveshaft / Propshaft',    'Rear or front driveshaft with universal joints'],
        ['CV Joint & Boot',           'Constant velocity joint and rubber gaiter'],
        ['Differential',              'Front, rear or inter-axle differential unit'],
        // Brakes
        ['Brake Pad Set',             'Front or rear friction brake pads'],
        ['Brake Disc / Rotor',        'Solid or vented brake disc (single axle)'],
        ['Brake Drum',                'Cast iron brake drum for drum-brake axles'],
        ['Brake Calliper',            'Sliding or fixed brake calliper assembly'],
        ['Brake Master Cylinder',     'Hydraulic master cylinder for brake actuation'],
        ['Brake Hose',                'Flexible hydraulic brake hose'],
        ['Brake Shoe Set',            'Friction brake shoes for drum brake assemblies'],
        ['ABS Pump / Module',         'Anti-lock braking system hydraulic pump and ECU module'],
        // Steering & Suspension
        ['Power Steering Pump',       'Hydraulic or electric power steering assist pump'],
        ['Steering Rack & Pinion',    'Complete rack-and-pinion steering assembly'],
        ['Tie Rod End',               'Outer or inner steering tie rod end'],
        ['Ball Joint',                'Upper or lower suspension ball joint'],
        ['Wheel Bearing',             'Hub or press-fit wheel bearing kit'],
        ['Shock Absorber / Damper',   'Front or rear hydraulic shock absorber'],
        ['Coil Spring',               'Front or rear suspension coil spring'],
        ['Leaf Spring',               'Commercial vehicle leaf spring pack'],
        ['Anti-Roll Bar Bush',        'Stabiliser bar / anti-roll bar rubber bush kit'],
        // Tyres & Wheels
        ['Tyre',                      'Replacement tyre (road, all-terrain or specialised)'],
        ['Rim / Wheel',               'Steel or alloy wheel rim'],
        ['Valve Stem',                'Tyre valve stem and cap'],
        ['Wheel Nut / Bolt',          'Wheel fastening nuts or bolts'],
        ['Tyre Repair Kit',           'Plugs, patches and vulcanising solution for puncture repair'],
        // Body & Cabin
        ['Windscreen / Windshield',   'Front laminated safety glass screen'],
        ['Side Window Glass',         'Tempered door or quarter-panel glass'],
        ['Door Mirror',               'Complete external rear-view mirror assembly'],
        ['Wiper Blade',               'Front or rear screen wiper blade'],
        ['Wiper Motor',               'Front or rear wiper drive motor and linkage'],
        ['Seat Assembly',             'Driver or passenger seat base, back and adjustment mechanism'],
        ['Seat Belt',                 'Retractable lap-and-diagonal safety belt assembly'],
        ['Door Lock Actuator',        'Electric central-locking door actuator'],
        ['Bonnet / Hood Strut',       'Gas or mechanical bonnet stay strut'],
        // HVAC
        ['Compressor (A/C)',          'Air-conditioning refrigerant compressor'],
        ['Condenser (A/C)',           'A/C refrigerant-to-air heat exchanger condenser'],
        ['Evaporator (A/C)',          'Cabin evaporator coil for A/C cooling'],
        ['Cabin Air Filter',          'Pollen or particulate filter for cabin ventilation'],
        ['Heater Matrix',             'Cabin heater core heat exchanger'],
        // Hydraulics
        ['Hydraulic Pump',            'PTO-driven or electric hydraulic pump'],
        ['Hydraulic Cylinder',        'Ram or double-acting hydraulic cylinder'],
        ['Hydraulic Hose',            'High-pressure braided hydraulic hose and fittings'],
        ['Control Valve (Hydraulic)', 'Directional or flow-control hydraulic valve'],
        // Filters & Fluids
        ['Engine Oil',                'Mineral, semi-synthetic or full-synthetic engine oil'],
        ['Gearbox Oil',               'Manual or automatic transmission fluid'],
        ['Differential Oil',          'Axle or differential lubricant'],
        ['Brake Fluid',               'DOT 3, DOT 4 or DOT 5.1 hydraulic brake fluid'],
        ['Coolant / Antifreeze',      'Engine coolant concentrate or ready-mixed'],
        ['AdBlue / DEF',              'Diesel exhaust fluid for SCR emission systems'],
        ['Grease',                    'Multi-purpose or specialist chassis grease'],
        // Safety
        ['Fire Extinguisher',         'Vehicle-mounted dry powder or CO2 fire extinguisher'],
        ['First Aid Kit',             'Roadside first aid kit meeting local regulations'],
        ['Warning Triangle',          'Reflective breakdown warning triangle set'],
        ['Tow Rope / Strap',          'Recovery tow strap or rigid tow bar'],
        ['High-Visibility Vest',      'EN 471 or equivalent high-vis reflective vest'],
        // Refrigeration
        ['Reefer Compressor',         'Refrigeration unit compressor motor'],
        ['Reefer Condenser Coil',     'Heat-rejection condenser coil for trailer reefer unit'],
        ['Reefer Evaporator Coil',    'Cold-side evaporator coil for trailer reefer unit'],
        ['Reefer Fuel Filter',        'Dedicated fuel filter for diesel reefer unit engine'],
        // Cargo / Coupling
        ['Curtain Track & Buckle',    'Side curtain track rail, buckle strap and tensioner'],
        ['Cargo Strap / Load Binder', 'Ratchet strap or chain binder for load securing'],
        ['Tail-Lift Motor',           'Hydraulic pump motor for tail-lift platform'],
        ['Fifth Wheel Plate',         'Kingpin coupling fifth wheel assembly'],
        ['Kingpin',                   'Semi-trailer kingpin and wear ring'],
        ['Landing Gear Leg',          'Trailer front support landing leg and gearbox'],
    ];
    foreach ($ptypes as $r) {
        $CI->db->query("INSERT INTO `" . db_prefix() . "fleet_part_types` (`name`,`description`,`datecreated`) VALUES ('" . $CI->db->escape_str($r[0]) . "','" . $CI->db->escape_str($r[1]) . "','" . $now . "')");
    }
}
// ─────────────────────────────────────────────────────────────────────────────

// ── Trip Booking tables ──────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'fleet_trips')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_trips` (
        `id`               INT(11)        NOT NULL AUTO_INCREMENT,
        `shipment_id`      INT(11)        NULL DEFAULT NULL,
        `trip_date`        DATETIME       NULL DEFAULT NULL,
        `vehicle_id`       INT(11)        NOT NULL,
        `driver_id`        INT(11)        NULL DEFAULT NULL,
        `track_type`       ENUM('single','double') NOT NULL DEFAULT 'single',
        `picking_point_id` INT(11)        NULL DEFAULT NULL,
        `to_point_id`      INT(11)        NULL DEFAULT NULL,
        `customer_id`      INT(11)        NULL DEFAULT NULL,
        `vehicle_status`   ENUM('empty','half_load','partly_loaded') NULL DEFAULT NULL,
        `load_type`        ENUM('full','half','part') NULL DEFAULT NULL,
        `status`           ENUM('booked','fuel_requested','started','offloading','completed','cancelled') NOT NULL DEFAULT 'booked',
        `fuel_request_id`  INT(11)        NULL DEFAULT NULL,
        `start_odometer`   DECIMAL(10,2)  NULL DEFAULT NULL,
        `end_odometer`     DECIMAL(10,2)  NULL DEFAULT NULL,
        `start_time`       DATETIME       NULL DEFAULT NULL,
        `end_time`         DATETIME       NULL DEFAULT NULL,
        `parent_trip_id`   INT(11)        NULL DEFAULT NULL,
        `notes`            TEXT           NULL DEFAULT NULL,
        `created_by`       INT(11)        NOT NULL,
        `created_at`       DATETIME       NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Migrate existing installations — add columns if missing
if (!$CI->db->field_exists('trip_date', db_prefix() . 'fleet_trips')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_trips`
        ADD COLUMN `trip_date`      DATETIME NULL DEFAULT NULL AFTER `shipment_id`,
        ADD COLUMN `to_point_id`    INT(11)  NULL DEFAULT NULL AFTER `picking_point_id`,
        ADD COLUMN `customer_id`    INT(11)  NULL DEFAULT NULL AFTER `to_point_id`,
        ADD COLUMN `vehicle_status` ENUM(\'empty\',\'half_load\',\'partly_loaded\') NULL DEFAULT NULL AFTER `customer_id`,
        ADD COLUMN `load_type`      ENUM(\'full\',\'half\',\'part\') NULL DEFAULT NULL AFTER `vehicle_status`');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_trip_offloading')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_trip_offloading` (
        `id`                  INT(11)      NOT NULL AUTO_INCREMENT,
        `trip_id`             INT(11)      NOT NULL,
        `service_point_id`    INT(11)      NULL DEFAULT NULL,
        `offload_type`        ENUM('full','partial','parts') NOT NULL DEFAULT 'full',
        `packages_offloaded`  TEXT         NULL DEFAULT NULL,
        `location`            VARCHAR(255) NULL DEFAULT NULL,
        `notes`               TEXT         NULL DEFAULT NULL,
        `odometer`            DECIMAL(10,2) NULL DEFAULT NULL,
        `recorded_at`         DATETIME     NOT NULL,
        `recorded_by`         INT(11)      NOT NULL,
        PRIMARY KEY (`id`),
        KEY `trip_id` (`trip_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('service_point_id', db_prefix() . 'fleet_trip_offloading')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_trip_offloading` ADD COLUMN `service_point_id` INT(11) NULL DEFAULT NULL AFTER `trip_id`');
}

// Unguessable token so a driver can open a no-login mobile page and share
// GPS location for their active trip, without needing a full staff account.
if (!$CI->db->field_exists('tracking_token', db_prefix() . 'fleet_trips')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_trips` ADD COLUMN `tracking_token` VARCHAR(64) NULL DEFAULT NULL AFTER `id`, ADD UNIQUE KEY `tracking_token` (`tracking_token`)');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_trip_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_trip_locations` (
        `id`          INT(11)        NOT NULL AUTO_INCREMENT,
        `trip_id`     INT(11)        NOT NULL,
        `latitude`    DECIMAL(10,7)  NOT NULL,
        `longitude`   DECIMAL(10,7)  NOT NULL,
        `accuracy`    DECIMAL(10,2)  NULL DEFAULT NULL,
        `speed`       DECIMAL(10,2)  NULL DEFAULT NULL,
        `recorded_at` DATETIME       NOT NULL,
        PRIMARY KEY (`id`),
        KEY `trip_id` (`trip_id`),
        KEY `trip_recorded` (`trip_id`, `recorded_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

