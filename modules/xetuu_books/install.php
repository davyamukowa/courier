<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$prefix = db_prefix();

// Auto-patch conflicting tables from other accounting modules
$acc_table = $prefix . 'acc_accounts';
if ($CI->db->table_exists($acc_table)) {
    $required_columns = [
        'code' => 'VARCHAR(20) NULL UNIQUE',
        'type' => "ENUM('Asset','Current Asset','Fixed Asset','Bank','Cash','Liability','Current Liability','Long-term Liability','Equity','Revenue','Cost of Revenue','Expense','Other Income','Other Expense','Tax','Receivable','Payable') NOT NULL DEFAULT 'Expense'",
        'currency_id' => 'INT DEFAULT 1',
        'parent_id' => 'INT DEFAULT NULL',
        'is_group' => 'TINYINT(1) DEFAULT 0',
        'allow_reconciliation' => 'TINYINT(1) DEFAULT 0',
        'deprecated' => 'TINYINT(1) DEFAULT 0',
        'company_id' => 'INT DEFAULT 1',
        'account_category' => 'VARCHAR(100) DEFAULT NULL',
        'notes' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    foreach ($required_columns as $col => $def) {
        if (!$CI->db->field_exists($col, $acc_table)) {
            $CI->db->query("ALTER TABLE `$acc_table` ADD COLUMN `$col` $def");
        }
    }
}

// Auto-patch journals if exists
$jour_table = $prefix . 'acc_journals';
if ($CI->db->table_exists($jour_table)) {
    $required_columns_jour = [
        'name' => 'VARCHAR(200) NOT NULL',
        'type' => "ENUM('Sale','Purchase','Cash','Bank','General','Situation') NOT NULL DEFAULT 'General'",
        'code' => 'VARCHAR(10) NULL',
        'account_id' => 'INT',
        'suspense_account_id' => 'INT',
        'profit_account_id' => 'INT',
        'loss_account_id' => 'INT',
        'currency_id' => 'INT DEFAULT 1',
        'bank_account_id' => 'INT',
        'sequence_id' => 'INT',
        'dedicated_refund_sequence' => 'TINYINT(1) DEFAULT 0',
        'lock_posted_entries' => 'TINYINT(1) DEFAULT 0',
        'restrict_mode_hash_table' => 'TINYINT(1) DEFAULT 0',
        'show_on_dashboard' => 'TINYINT(1) DEFAULT 1',
        'color' => 'VARCHAR(20)',
        'sequence' => 'INT DEFAULT 10',
        'active' => 'TINYINT(1) DEFAULT 1',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    foreach ($required_columns_jour as $col => $def) {
        if (!$CI->db->field_exists($col, $jour_table)) {
            $CI->db->query("ALTER TABLE `$jour_table` ADD COLUMN `$col` $def");
        }
    }
}

// Auto-patch taxes if exists
$tax_table = $prefix . 'acc_taxes';
if ($CI->db->table_exists($tax_table)) {
    $required_columns_tax = [
        'name' => 'VARCHAR(200) NOT NULL',
        'type_tax_use' => "ENUM('sale','purchase','none','adjustment') DEFAULT 'sale'",
        'tax_scope' => "ENUM('consu','service','') DEFAULT ''",
        'amount_type' => "ENUM('percent','fixed','division','group','code') DEFAULT 'percent'",
        'amount' => 'DECIMAL(10,4) DEFAULT 0',
        'price_include' => 'TINYINT(1) DEFAULT 0',
        'include_base_amount' => 'TINYINT(1) DEFAULT 0',
        'is_base_affected' => 'TINYINT(1) DEFAULT 1',
        'account_id' => 'INT',
        'refund_account_id' => 'INT',
        'description' => 'VARCHAR(100)',
        'invoice_label' => 'VARCHAR(100)',
        'active' => 'TINYINT(1) DEFAULT 1',
        'sequence' => 'INT DEFAULT 1',
        'tax_group_id' => 'INT',
        'country_id' => 'INT',
        'fiscal_country_codes' => 'VARCHAR(100)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    foreach ($required_columns_tax as $col => $def) {
        if (!$CI->db->field_exists($col, $tax_table)) {
            $CI->db->query("ALTER TABLE `$tax_table` ADD COLUMN `$col` $def");
        }
    }
}

// Execute the migration script
$sql_file = __DIR__ . '/xetuu_books_migration.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Replace all instances of `acc_` with the actual db prefix + `acc_`
    // using a regex to match the exact word boundary for table names
    $sql = preg_replace('/\bacc_/', $prefix . 'acc_', $sql);
    
    // Split the SQL statements based on semicolons while avoiding splitting inside quotes
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if (!empty($query)) {
            $CI->db->query($query);
        }
    }
}

// Add account_category column if it doesn't exist yet (idempotent upgrade)
if ($CI->db->table_exists($prefix . 'acc_accounts')) {
    if (!$CI->db->field_exists('account_category', $prefix . 'acc_accounts')) {
        $CI->db->query(
            "ALTER TABLE `" . $prefix . "acc_accounts`
             ADD COLUMN `account_category` VARCHAR(100) DEFAULT NULL"
        );
    }
}

// Make code column nullable if it is still NOT NULL (allows optional account numbers)
if ($CI->db->table_exists($prefix . 'acc_accounts')) {
    $fields = $CI->db->field_data($prefix . 'acc_accounts');
    foreach ($fields as $field) {
        if ($field->name === 'code' && (isset($field->nullable) && $field->nullable == false)) {
            $CI->db->query(
                "ALTER TABLE `" . $prefix . "acc_accounts`
                 MODIFY COLUMN `code` VARCHAR(20) NULL"
            );
            break;
        }
    }
}

// Add source_move_id to acc_moves for credit note → bill linking
if ($CI->db->table_exists($prefix . 'acc_moves')) {
    if (!$CI->db->field_exists('xb_analytic_account_id', db_prefix() . 'creditnotes')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'creditnotes` ADD `xb_analytic_account_id` INT(11) NULL DEFAULT NULL;');
    }
    if (!$CI->db->field_exists('source_move_id', $prefix . 'acc_moves')) {
        $CI->db->query(
            "ALTER TABLE `" . $prefix . "acc_moves`
             ADD COLUMN `source_move_id` INT NULL DEFAULT NULL"
        );
    }
}

// Analytic plans, accounts, assignments, lines, budgets
$analytic_tables = [
    'acc_analytic_plans' =>
        "CREATE TABLE IF NOT EXISTS `{$prefix}acc_analytic_plans` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(200) NOT NULL,
          `color` VARCHAR(20) DEFAULT NULL,
          `applicability` ENUM('mandatory','optional','unavailable') DEFAULT 'optional',
          `sequence` INT DEFAULT 10,
          `active` TINYINT(1) DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'acc_analytic_accounts' =>
        "CREATE TABLE IF NOT EXISTS `{$prefix}acc_analytic_accounts` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(200) NOT NULL,
          `code` VARCHAR(50) DEFAULT NULL,
          `complete_name` VARCHAR(500) DEFAULT NULL,
          `plan_id` INT DEFAULT NULL,
          `parent_id` INT DEFAULT NULL,
          `level` INT DEFAULT 0,
          `active` TINYINT(1) DEFAULT 1,
          `currency_id` INT DEFAULT 1,
          `company_id` INT DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'acc_analytic_lines' =>
        "CREATE TABLE IF NOT EXISTS `{$prefix}acc_analytic_lines` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(300) DEFAULT NULL,
          `date` DATE NOT NULL,
          `amount` DECIMAL(15,4) DEFAULT 0,
          `percentage` DECIMAL(5,2) DEFAULT 100.00,
          `analytic_account_id` INT NOT NULL,
          `move_line_id` INT DEFAULT NULL,
          `move_id` INT DEFAULT NULL,
          `partner_id` INT DEFAULT NULL,
          `partner_type` ENUM('customer','vendor') DEFAULT NULL,
          `general_account_id` INT DEFAULT NULL,
          `company_id` INT DEFAULT 1,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'acc_analytic_budgets' =>
        "CREATE TABLE IF NOT EXISTS `{$prefix}acc_analytic_budgets` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `analytic_account_id` INT NOT NULL,
          `period_type` ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
          `period_start` DATE NOT NULL,
          `period_end` DATE NOT NULL,
          `budget_amount` DECIMAL(15,4) DEFAULT 0,
          `currency_id` INT DEFAULT 1,
          `notes` TEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'acc_analytic_assignments' =>
        "CREATE TABLE IF NOT EXISTS `{$prefix}acc_analytic_assignments` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `form_type` VARCHAR(80) NOT NULL,
          `record_id` INT NOT NULL,
          `analytic_account_id` INT NOT NULL,
          `percentage` DECIMAL(5,2) DEFAULT 100.00,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY `uq_assignment` (`form_type`, `record_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($analytic_tables as $table => $ddl) {
    if (!$CI->db->table_exists($prefix . $table)) {
        $CI->db->query($ddl);
    }
}

add_option('xetuu_books_installed', 1);
