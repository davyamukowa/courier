<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * POS System - Database Schema Installation
 * Supports: Multi-branch, Mobile Money, Inventory, Split Payments
 * Markets: Kenya, Uganda, Tanzania, Rwanda, Ethiopia
 */

$CI = &get_instance();

// ─── BRANCHES ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_branches')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_branches` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(150)     NOT NULL,
        `code`         VARCHAR(20)      NOT NULL UNIQUE,
        `address`      TEXT             DEFAULT NULL,
        `city`         VARCHAR(100)     DEFAULT NULL,
        `country`      VARCHAR(100)     DEFAULT NULL,
        `phone`        VARCHAR(30)      DEFAULT NULL,
        `email`        VARCHAR(150)     DEFAULT NULL,
        `currency`     VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `timezone`     VARCHAR(60)      NOT NULL DEFAULT \'Africa/Nairobi\',
        `tax_pin`      VARCHAR(50)      DEFAULT NULL,
        `receipt_header` TEXT           DEFAULT NULL,
        `receipt_footer` TEXT           DEFAULT NULL,
        `logo`         VARCHAR(255)     DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `is_protected` TINYINT(1)       NOT NULL DEFAULT 0,
        `created_by`   INT(11)          DEFAULT NULL,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_branch_active` (`is_active`),
        INDEX `idx_branch_code`   (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── STAFF ↔ BRANCH MAPPING ──────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_staff_branches')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_staff_branches` (
        `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_id`   INT(11)          NOT NULL,
        `branch_id`  INT(11) UNSIGNED NOT NULL,
        `role`       ENUM(\'cashier\',\'supervisor\',\'manager\',\'admin\') NOT NULL DEFAULT \'cashier\',
        `is_default` TINYINT(1)       NOT NULL DEFAULT 0,
        `date_added` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_staff_branch` (`staff_id`, `branch_id`),
        INDEX `idx_staff_id`  (`staff_id`),
        INDEX `idx_branch_id` (`branch_id`),
        FOREIGN KEY (`branch_id`) REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── POS SESSIONS ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_sessions')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_sessions` (
        `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `session_uid`          VARCHAR(36)      NOT NULL UNIQUE COMMENT \'UUID for offline sync\',
        `branch_id`            INT(11) UNSIGNED NOT NULL,
        `staff_id`             INT(11)          NOT NULL,
        `terminal_id`          VARCHAR(50)      DEFAULT NULL,
        `status`               ENUM(\'open\',\'closed\',\'suspended\') NOT NULL DEFAULT \'open\',
        `opening_float`        DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
        `closing_float`        DECIMAL(15,2)    DEFAULT NULL,
        `expected_cash`        DECIMAL(15,2)    DEFAULT NULL,
        `actual_cash`          DECIMAL(15,2)    DEFAULT NULL,
        `cash_difference`      DECIMAL(15,2)    DEFAULT NULL,
        `total_sales_amount`   DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
        `total_sales_count`    INT(11)          NOT NULL DEFAULT 0,
        `total_returns_amount` DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
        `total_returns_count`  INT(11)          NOT NULL DEFAULT 0,
        `notes`                TEXT             DEFAULT NULL,
        `opened_at`            DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `closed_at`            DATETIME         DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_session_branch`  (`branch_id`),
        INDEX `idx_session_staff`   (`staff_id`),
        INDEX `idx_session_status`  (`status`),
        INDEX `idx_session_opened`  (`opened_at`),
        FOREIGN KEY (`branch_id`) REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRODUCT CATEGORIES ──────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_product_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_product_categories` (
        `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parent_id`   INT(11) UNSIGNED DEFAULT NULL,
        `name`        VARCHAR(150)     NOT NULL,
        `slug`        VARCHAR(160)     NOT NULL UNIQUE,
        `description` TEXT             DEFAULT NULL,
        `image`       VARCHAR(255)     DEFAULT NULL,
        `sort_order`  INT(11)          NOT NULL DEFAULT 0,
        `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_cat_parent` (`parent_id`),
        INDEX `idx_cat_active` (`is_active`),
        FOREIGN KEY (`parent_id`) REFERENCES `' . db_prefix() . 'pos_product_categories`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRODUCTS ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_products')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_products` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `category_id`       INT(11) UNSIGNED DEFAULT NULL,
        `perfex_item_id`    INT(11)          DEFAULT NULL COMMENT \'Link to Perfex items table\',
        `name`              VARCHAR(255)     NOT NULL,
        `sku`               VARCHAR(100)     DEFAULT NULL,
        `barcode`           VARCHAR(100)     DEFAULT NULL,
        `description`       TEXT             DEFAULT NULL,
        `unit`              VARCHAR(30)      DEFAULT \'pcs\',
        `type`              ENUM(\'simple\',\'variable\',\'service\',\'bundle\') NOT NULL DEFAULT \'simple\',
        `cost_price`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `selling_price`     DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_rate_id`       INT(11) UNSIGNED DEFAULT NULL,
        `is_tax_inclusive`  TINYINT(1)       NOT NULL DEFAULT 0,
        `track_inventory`   TINYINT(1)       NOT NULL DEFAULT 1,
        `allow_negative`    TINYINT(1)       NOT NULL DEFAULT 0,
        `reorder_point`     DECIMAL(10,2)    DEFAULT NULL,
        `max_stock`         DECIMAL(10,2)    DEFAULT NULL,
        `image`             VARCHAR(255)     DEFAULT NULL,
        `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
        `is_pos_visible`    TINYINT(1)       NOT NULL DEFAULT 1,
        `has_variations`    TINYINT(1)       NOT NULL DEFAULT 0,
        `date_created`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_sku`     (`sku`),
        UNIQUE KEY `uq_barcode` (`barcode`),
        INDEX `idx_prod_cat`     (`category_id`),
        INDEX `idx_prod_active`  (`is_active`, `is_pos_visible`),
        INDEX `idx_prod_perfex`  (`perfex_item_id`),
        FOREIGN KEY (`category_id`) REFERENCES `' . db_prefix() . 'pos_product_categories`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRODUCT VARIATION ATTRIBUTES ────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_variation_attributes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_variation_attributes` (
        `id`   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100)     NOT NULL COMMENT \'e.g. Size, Color, Weight\',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_variation_values')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_variation_values` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `attribute_id` INT(11) UNSIGNED NOT NULL,
        `value`        VARCHAR(100)     NOT NULL COMMENT \'e.g. S, M, L, Red, 500ml\',
        PRIMARY KEY (`id`),
        INDEX `idx_attr_id` (`attribute_id`),
        FOREIGN KEY (`attribute_id`) REFERENCES `' . db_prefix() . 'pos_variation_attributes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRODUCT VARIATIONS (SKUs) ────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_product_variations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_product_variations` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`   INT(11) UNSIGNED NOT NULL,
        `sku`          VARCHAR(100)     DEFAULT NULL,
        `barcode`      VARCHAR(100)     DEFAULT NULL,
        `cost_price`   DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `selling_price` DECIMAL(15,4)   NOT NULL DEFAULT 0.0000,
        `image`        VARCHAR(255)     DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_var_sku`     (`sku`),
        UNIQUE KEY `uq_var_barcode` (`barcode`),
        INDEX `idx_var_product` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// Pivot: variation ↔ attribute values
if (!$CI->db->table_exists(db_prefix() . 'pos_product_variation_options')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_product_variation_options` (
        `variation_id` INT(11) UNSIGNED NOT NULL,
        `value_id`     INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`variation_id`, `value_id`),
        FOREIGN KEY (`variation_id`) REFERENCES `' . db_prefix() . 'pos_product_variations`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`value_id`)     REFERENCES `' . db_prefix() . 'pos_variation_values`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── INVENTORY ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_inventory')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inventory` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`    INT(11) UNSIGNED NOT NULL,
        `product_id`   INT(11) UNSIGNED NOT NULL,
        `variation_id` INT(11) UNSIGNED DEFAULT NULL,
        `quantity`     DECIMAL(12,4)    NOT NULL DEFAULT 0.0000,
        `reserved_qty` DECIMAL(12,4)    NOT NULL DEFAULT 0.0000,
        `date_updated` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_inv_branch_product_var` (`branch_id`, `product_id`, `variation_id`),
        INDEX `idx_inv_branch`  (`branch_id`),
        INDEX `idx_inv_product` (`product_id`),
        FOREIGN KEY (`branch_id`)    REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`)   REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`variation_id`) REFERENCES `' . db_prefix() . 'pos_product_variations`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── INVENTORY BATCHES (with expiry) ─────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_inventory_batches')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inventory_batches` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`       INT(11) UNSIGNED NOT NULL,
        `product_id`      INT(11) UNSIGNED NOT NULL,
        `variation_id`    INT(11) UNSIGNED DEFAULT NULL,
        `batch_number`    VARCHAR(100)     NOT NULL,
        `quantity`        DECIMAL(12,4)    NOT NULL DEFAULT 0.0000,
        `cost_price`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `manufacture_date` DATE            DEFAULT NULL,
        `expiry_date`     DATE             DEFAULT NULL,
        `supplier`        VARCHAR(150)     DEFAULT NULL,
        `notes`           TEXT             DEFAULT NULL,
        `date_created`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_batch_branch`   (`branch_id`),
        INDEX `idx_batch_product`  (`product_id`),
        INDEX `idx_batch_expiry`   (`expiry_date`),
        FOREIGN KEY (`branch_id`)  REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── TAX RATES ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_tax_rates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_tax_rates` (
        `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`       VARCHAR(100)     NOT NULL COMMENT \'e.g. VAT 16%, VAT 18%\',
        `rate`       DECIMAL(6,4)     NOT NULL COMMENT \'Stored as decimal: 0.1600 = 16%\',
        `country`    VARCHAR(100)     DEFAULT NULL,
        `is_default` TINYINT(1)       NOT NULL DEFAULT 0,
        `is_active`  TINYINT(1)       NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    // Seed East African tax rates
    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_tax_rates` (`name`,`rate`,`country`,`is_default`,`is_active`) VALUES
        ('VAT 16%',   0.1600, 'Kenya',    1, 1),
        ('VAT 18%',   0.1800, 'Uganda',   0, 1),
        ('VAT 18%',   0.1800, 'Tanzania', 0, 1),
        ('VAT 18%',   0.1800, 'Rwanda',   0, 1),
        ('VAT 15%',   0.1500, 'Ethiopia', 0, 1),
        ('Exempt',    0.0000, NULL,        0, 1)
    ");
}

// ─── DISCOUNT / PROMOTIONS ───────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_discounts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_discounts` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`      INT(11) UNSIGNED DEFAULT NULL COMMENT \'NULL = all branches\',
        `name`           VARCHAR(150)     NOT NULL,
        `code`           VARCHAR(50)      DEFAULT NULL UNIQUE,
        `type`           ENUM(\'percent\',\'fixed\') NOT NULL DEFAULT \'percent\',
        `value`          DECIMAL(12,4)    NOT NULL,
        `min_order`      DECIMAL(12,2)    DEFAULT NULL,
        `max_uses`       INT(11)          DEFAULT NULL,
        `used_count`     INT(11)          NOT NULL DEFAULT 0,
        `applies_to`     ENUM(\'all\',\'category\',\'product\') NOT NULL DEFAULT \'all\',
        `applies_to_id`  INT(11)          DEFAULT NULL,
        `starts_at`      DATETIME         DEFAULT NULL,
        `expires_at`     DATETIME         DEFAULT NULL,
        `is_active`      TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_disc_branch`  (`branch_id`),
        INDEX `idx_disc_code`    (`code`),
        INDEX `idx_disc_active`  (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── POS CUSTOMERS (links to Perfex clients) ─────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_customers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_customers` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `perfex_client_id` INT(11)         DEFAULT NULL COMMENT \'FK to tblclients\',
        `branch_id`       INT(11) UNSIGNED DEFAULT NULL,
        `name`            VARCHAR(200)     NOT NULL,
        `phone`           VARCHAR(30)      DEFAULT NULL,
        `email`           VARCHAR(150)     DEFAULT NULL,
        `id_number`       VARCHAR(50)      DEFAULT NULL,
        `loyalty_points`  INT(11)          NOT NULL DEFAULT 0,
        `total_spent`     DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
        `notes`           TEXT             DEFAULT NULL,
        `date_created`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_cust_perfex` (`perfex_client_id`),
        INDEX `idx_cust_phone`  (`phone`),
        INDEX `idx_cust_branch` (`branch_id`),
        FOREIGN KEY (`branch_id`) REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PAYMENT METHODS ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_payment_methods')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_payment_methods` (
        `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`   INT(11) UNSIGNED DEFAULT NULL COMMENT \'NULL = all branches\',
        `name`        VARCHAR(100)     NOT NULL,
        `code`        VARCHAR(50)      NOT NULL UNIQUE,
        `type`        ENUM(\'cash\',\'card\',\'mobile_money\',\'bank_transfer\',\'credit\',\'other\') NOT NULL DEFAULT \'cash\',
        `provider`    VARCHAR(50)      DEFAULT NULL COMMENT \'mpesa|airtel|mtn|telebirr\',
        `config`      JSON             DEFAULT NULL COMMENT \'API credentials (encrypted)\',
        `icon`        VARCHAR(255)     DEFAULT NULL,
        `sort_order`  INT(11)          NOT NULL DEFAULT 0,
        `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        INDEX `idx_pm_type`   (`type`),
        INDEX `idx_pm_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    // Seed default payment methods
    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_payment_methods` (`name`,`code`,`type`,`provider`,`sort_order`,`is_active`) VALUES
        ('Cash',          'cash',        'cash',         NULL,       1, 1),
        ('Card / POS',    'card',        'card',         NULL,       2, 1),
        ('M-Pesa',        'mpesa',       'mobile_money', 'mpesa',    3, 1),
        ('Airtel Money',  'airtel',      'mobile_money', 'airtel',   4, 1),
        ('MTN Mobile Money', 'mtn',      'mobile_money', 'mtn',      5, 1),
        ('Telebirr',      'telebirr',    'mobile_money', 'telebirr', 6, 1),
        ('Bank Transfer', 'bank',        'bank_transfer', NULL,      7, 1),
        ('Store Credit',  'credit',      'credit',       NULL,       8, 1)
    ");
}

// ─── SALES ───────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_sales')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_sales` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sale_uid`          VARCHAR(36)      NOT NULL UNIQUE COMMENT \'UUID - offline safe\',
        `receipt_number`    VARCHAR(50)      NOT NULL UNIQUE,
        `branch_id`         INT(11) UNSIGNED NOT NULL,
        `session_id`        INT(11) UNSIGNED DEFAULT NULL,
        `staff_id`          INT(11)          NOT NULL,
        `customer_id`       INT(11) UNSIGNED DEFAULT NULL,
        `perfex_invoice_id` INT(11)          DEFAULT NULL COMMENT \'Created invoice in Perfex\',
        `status`            ENUM(\'completed\',\'refunded\',\'partial_refund\',\'void\',\'on_hold\') NOT NULL DEFAULT \'completed\',
        `subtotal`          DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_id`       INT(11) UNSIGNED DEFAULT NULL,
        `discount_type`     ENUM(\'percent\',\'fixed\') DEFAULT NULL,
        `discount_value`    DECIMAL(12,4)    NOT NULL DEFAULT 0.0000,
        `discount_amount`   DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_amount`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `total`             DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `amount_paid`       DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `change_given`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `currency`          VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `notes`             TEXT             DEFAULT NULL,
        `sync_status`       ENUM(\'synced\',\'pending\',\'failed\') NOT NULL DEFAULT \'synced\' COMMENT \'Offline sync state\',
        `synced_at`         DATETIME         DEFAULT NULL,
        `date_created`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_sale_branch`   (`branch_id`),
        INDEX `idx_sale_session`  (`session_id`),
        INDEX `idx_sale_staff`    (`staff_id`),
        INDEX `idx_sale_customer` (`customer_id`),
        INDEX `idx_sale_status`   (`status`),
        INDEX `idx_sale_date`     (`date_created`),
        INDEX `idx_sale_sync`     (`sync_status`),
        FOREIGN KEY (`branch_id`)   REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`session_id`)  REFERENCES `' . db_prefix() . 'pos_sessions`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`customer_id`) REFERENCES `' . db_prefix() . 'pos_customers`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── SALE LINE ITEMS ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_sale_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_sale_items` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sale_id`        INT(11) UNSIGNED NOT NULL,
        `product_id`     INT(11) UNSIGNED NOT NULL,
        `variation_id`   INT(11) UNSIGNED DEFAULT NULL,
        `batch_id`       INT(11) UNSIGNED DEFAULT NULL,
        `product_name`   VARCHAR(255)     NOT NULL COMMENT \'Snapshot at sale time\',
        `sku`            VARCHAR(100)     DEFAULT NULL,
        `quantity`       DECIMAL(12,4)    NOT NULL,
        `unit_price`     DECIMAL(15,4)    NOT NULL,
        `cost_price`     DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_pct`   DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `discount_amt`   DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_rate`       DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `tax_amount`     DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `line_total`     DECIMAL(15,4)    NOT NULL,
        `refunded_qty`   DECIMAL(12,4)    NOT NULL DEFAULT 0.0000,
        `notes`          VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_si_sale`      (`sale_id`),
        INDEX `idx_si_product`   (`product_id`),
        FOREIGN KEY (`sale_id`)      REFERENCES `' . db_prefix() . 'pos_sales`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`)   REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`variation_id`) REFERENCES `' . db_prefix() . 'pos_product_variations`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PAYMENTS (split-payment support) ────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_payments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_payments` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_uid`       VARCHAR(36)      NOT NULL UNIQUE,
        `sale_id`           INT(11) UNSIGNED NOT NULL,
        `branch_id`         INT(11) UNSIGNED NOT NULL,
        `session_id`        INT(11) UNSIGNED DEFAULT NULL,
        `payment_method_id` INT(11) UNSIGNED NOT NULL,
        `amount`            DECIMAL(15,4)    NOT NULL,
        `currency`          VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `status`            ENUM(\'pending\',\'completed\',\'failed\',\'refunded\') NOT NULL DEFAULT \'completed\',
        `reference`         VARCHAR(100)     DEFAULT NULL COMMENT \'External transaction ref\',
        `notes`             TEXT             DEFAULT NULL,
        `date_created`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_pay_sale`    (`sale_id`),
        INDEX `idx_pay_branch`  (`branch_id`),
        INDEX `idx_pay_status`  (`status`),
        INDEX `idx_pay_method`  (`payment_method_id`),
        FOREIGN KEY (`sale_id`)           REFERENCES `' . db_prefix() . 'pos_sales`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`branch_id`)         REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`payment_method_id`) REFERENCES `' . db_prefix() . 'pos_payment_methods`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── MOBILE MONEY TRANSACTIONS ───────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_mobile_money_txns')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_mobile_money_txns` (
        `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id`         INT(11) UNSIGNED NOT NULL,
        `provider`           ENUM(\'mpesa\',\'airtel\',\'mtn\',\'telebirr\') NOT NULL,
        `phone_number`       VARCHAR(20)      NOT NULL,
        `checkout_request_id` VARCHAR(100)    DEFAULT NULL,
        `merchant_request_id` VARCHAR(100)    DEFAULT NULL,
        `transaction_id`     VARCHAR(100)     DEFAULT NULL COMMENT \'Provider transaction ID\',
        `amount`             DECIMAL(15,4)    NOT NULL,
        `currency`           VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `status`             ENUM(\'initiated\',\'pending\',\'completed\',\'failed\',\'cancelled\') NOT NULL DEFAULT \'initiated\',
        `raw_request`        JSON             DEFAULT NULL,
        `raw_callback`       JSON             DEFAULT NULL,
        `failure_reason`     VARCHAR(255)     DEFAULT NULL,
        `initiated_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `completed_at`       DATETIME         DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_mm_payment`  (`payment_id`),
        INDEX `idx_mm_txn`      (`transaction_id`),
        INDEX `idx_mm_provider` (`provider`),
        INDEX `idx_mm_status`   (`status`),
        FOREIGN KEY (`payment_id`) REFERENCES `' . db_prefix() . 'pos_payments`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── CASH MOVEMENTS (float in/out) ───────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_cash_movements')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_cash_movements` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `session_id`   INT(11) UNSIGNED NOT NULL,
        `branch_id`    INT(11) UNSIGNED NOT NULL,
        `staff_id`     INT(11)          NOT NULL,
        `type`         ENUM(\'in\',\'out\') NOT NULL,
        `amount`       DECIMAL(15,4)    NOT NULL,
        `reason`       VARCHAR(255)     NOT NULL,
        `notes`        TEXT             DEFAULT NULL,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_cm_session` (`session_id`),
        INDEX `idx_cm_branch`  (`branch_id`),
        FOREIGN KEY (`session_id`) REFERENCES `' . db_prefix() . 'pos_sessions`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`branch_id`) REFERENCES  `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── INVENTORY MOVEMENTS (audit trail) ───────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_inventory_movements')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inventory_movements` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`     INT(11) UNSIGNED NOT NULL,
        `product_id`    INT(11) UNSIGNED NOT NULL,
        `variation_id`  INT(11) UNSIGNED DEFAULT NULL,
        `batch_id`      INT(11) UNSIGNED DEFAULT NULL,
        `type`          ENUM(\'sale\',\'refund\',\'adjustment\',\'transfer_in\',\'transfer_out\',\'purchase\',\'opening\') NOT NULL,
        `reference_id`  INT(11)          DEFAULT NULL COMMENT \'sale_id or adjustment_id\',
        `reference_type` VARCHAR(50)     DEFAULT NULL,
        `qty_before`    DECIMAL(12,4)    NOT NULL,
        `qty_change`    DECIMAL(12,4)    NOT NULL COMMENT \'Negative = decrease\',
        `qty_after`     DECIMAL(12,4)    NOT NULL,
        `unit_cost`     DECIMAL(15,4)    DEFAULT NULL,
        `staff_id`      INT(11)          DEFAULT NULL,
        `notes`         TEXT             DEFAULT NULL,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_im_branch`   (`branch_id`),
        INDEX `idx_im_product`  (`product_id`),
        INDEX `idx_im_type`     (`type`),
        INDEX `idx_im_date`     (`date_created`),
        FOREIGN KEY (`branch_id`)   REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`)  REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── MODULE SETTINGS ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_settings` (
        `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`  INT(11) UNSIGNED DEFAULT NULL COMMENT \'NULL = global setting\',
        `setting_key`   VARCHAR(100)  NOT NULL,
        `setting_value` LONGTEXT      DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_setting` (`branch_id`, `setting_key`),
        INDEX `idx_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    // Seed global defaults
    $defaults = [
        ['receipt_template',  'default'],
        ['low_stock_alert',   '10'],
        ['enable_loyalty',    '0'],
        ['loyalty_rate',      '1'],        // 1 point per currency unit
        ['enable_offline',    '1'],
        ['sync_interval',     '300'],      // seconds
        ['default_currency',  'KES'],
        ['require_customer',  '0'],
        ['print_receipt',     '1'],
        ['session_required',  '1'],
    ];
    foreach ($defaults as $d) {
        $CI->db->query("INSERT IGNORE INTO `" . db_prefix() . "pos_settings` (`branch_id`,`setting_key`,`setting_value`) VALUES (NULL, '{$d[0]}', '{$d[1]}')");
    }
}

// ─── API TOKENS (for SPA authentication) ─────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_api_tokens')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_api_tokens` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_id`     INT(11)          NOT NULL,
        `branch_id`    INT(11) UNSIGNED NOT NULL,
        `token`        VARCHAR(255)     NOT NULL UNIQUE,
        `token_hash`   VARCHAR(64)      NOT NULL UNIQUE COMMENT \'SHA256 of token\',
        `device_name`  VARCHAR(100)     DEFAULT NULL,
        `last_used_at` DATETIME         DEFAULT NULL,
        `expires_at`   DATETIME         DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_token_staff`  (`staff_id`),
        INDEX `idx_token_branch` (`branch_id`),
        FOREIGN KEY (`branch_id`) REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── POS PROFILES (Config Engine) ─────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_profiles')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_profiles` (
        `id`                            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`                          VARCHAR(150)     NOT NULL,
        `branch_id`                     INT(11) UNSIGNED DEFAULT NULL COMMENT \'NULL = global profile\',
        `default_customer_id`           INT(11) UNSIGNED DEFAULT NULL,
        `default_warehouse_id`          INT(11)          DEFAULT NULL,
        `default_currency`              VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `default_country`               VARCHAR(100)     DEFAULT NULL,
        -- Behaviour toggles
        `hide_images`                   TINYINT(1) NOT NULL DEFAULT 0,
        `hide_unavailable_items`        TINYINT(1) NOT NULL DEFAULT 0,
        `auto_add_item_to_cart`         TINYINT(1) NOT NULL DEFAULT 0,
        `validate_stock_on_save`        TINYINT(1) NOT NULL DEFAULT 1,
        `print_receipt_on_order_complete` TINYINT(1) NOT NULL DEFAULT 1,
        `action_on_new_invoice`         ENUM(\'ask\',\'new\',\'continue\') NOT NULL DEFAULT \'ask\',
        `ignore_pricing_rule`           TINYINT(1) NOT NULL DEFAULT 0,
        `allow_rate_change`             TINYINT(1) NOT NULL DEFAULT 1,
        `allow_discount_change`         TINYINT(1) NOT NULL DEFAULT 1,
        `set_grand_total_to_default_mop` TINYINT(1) NOT NULL DEFAULT 0,
        `allow_partial_payment`         TINYINT(1) NOT NULL DEFAULT 1,
        -- Tax & pricing
        `apply_discount_on`             ENUM(\'net_total\',\'grand_total\') NOT NULL DEFAULT \'net_total\',
        `price_list_id`                 INT(11) UNSIGNED DEFAULT NULL,
        `print_template_id`             INT(11) UNSIGNED DEFAULT NULL,
        -- Cash handling
        `enable_cash_rounding`          TINYINT(1) NOT NULL DEFAULT 0,
        `cash_rounding_increment`       DECIMAL(10,4)    NOT NULL DEFAULT 0.0500,
        `cash_rounding_type`            ENUM(\'nearest\',\'up\',\'down\') NOT NULL DEFAULT \'nearest\',
        `disable_rounded_total`         TINYINT(1) NOT NULL DEFAULT 0,
        -- Invoice settings
        `auto_create_invoice`           TINYINT(1) NOT NULL DEFAULT 0,
        `invoice_prefix`                VARCHAR(20)      DEFAULT \'POS-\',
        -- Access
        `is_active`                     TINYINT(1) NOT NULL DEFAULT 1,
        `created_by`                    INT(11)          DEFAULT NULL,
        `date_created`                  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`                  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_profile_branch` (`branch_id`),
        INDEX `idx_profile_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    // Seed the global default profile
    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_profiles`
        (`name`, `branch_id`, `default_currency`, `validate_stock_on_save`, `print_receipt_on_order_complete`,
         `allow_rate_change`, `allow_discount_change`, `allow_partial_payment`, `auto_create_invoice`)
        VALUES ('Default POS Profile', NULL, 'KES', 1, 1, 1, 1, 1, 0)");
}

// ─── PROFILE ↔ STAFF ASSIGNMENTS ─────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_profile_users')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_profile_users` (
        `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `profile_id`  INT(11) UNSIGNED NOT NULL,
        `staff_id`    INT(11)          NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_profile_user` (`profile_id`, `staff_id`),
        INDEX `idx_pu_staff` (`staff_id`),
        FOREIGN KEY (`profile_id`) REFERENCES `' . db_prefix() . 'pos_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRICE LISTS (multi-tier pricing) ─────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_price_lists')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_price_lists` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(150)     NOT NULL,
        `currency`     VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `is_default`   TINYINT(1)       NOT NULL DEFAULT 0,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_price_lists` (`name`,`currency`,`is_default`) VALUES ('Standard Price List','KES',1)");
}

if (!$CI->db->table_exists(db_prefix() . 'pos_price_list_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_price_list_items` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `price_list_id`  INT(11) UNSIGNED NOT NULL,
        `product_id`     INT(11) UNSIGNED NOT NULL,
        `variation_id`   INT(11) UNSIGNED DEFAULT NULL,
        `selling_price`  DECIMAL(15,4)    NOT NULL,
        `min_quantity`   DECIMAL(12,4)    NOT NULL DEFAULT 1.0000,
        `date_updated`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_pli` (`price_list_id`, `product_id`, `variation_id`),
        INDEX `idx_pli_product` (`product_id`),
        FOREIGN KEY (`price_list_id`) REFERENCES `' . db_prefix() . 'pos_price_lists`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`)    REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ITEM GROUPS (filtering engine) ───────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_groups` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(150)     NOT NULL,
        `description`  TEXT             DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_item_group_products')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_group_products` (
        `group_id`   INT(11) UNSIGNED NOT NULL,
        `product_id` INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`group_id`, `product_id`),
        FOREIGN KEY (`group_id`)   REFERENCES `' . db_prefix() . 'pos_item_groups`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── CUSTOMER GROUPS ──────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_customer_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_customer_groups` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`          VARCHAR(150)     NOT NULL,
        `discount_pct`  DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `price_list_id` INT(11) UNSIGNED DEFAULT NULL,
        `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_customer_groups` (`name`) VALUES ('Walk-In Customer')");
}

// ─── PROFILE FILTERS (pivot tables) ───────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_profile_item_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_profile_item_groups` (
        `profile_id` INT(11) UNSIGNED NOT NULL,
        `group_id`   INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`profile_id`, `group_id`),
        FOREIGN KEY (`profile_id`) REFERENCES `' . db_prefix() . 'pos_profiles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`group_id`)   REFERENCES `' . db_prefix() . 'pos_item_groups`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_profile_customer_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_profile_customer_groups` (
        `profile_id` INT(11) UNSIGNED NOT NULL,
        `group_id`   INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`profile_id`, `group_id`),
        FOREIGN KEY (`profile_id`) REFERENCES `' . db_prefix() . 'pos_profiles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`group_id`)   REFERENCES `' . db_prefix() . 'pos_customer_groups`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── PRINT TEMPLATES ──────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_print_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_print_templates` (
        `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`            INT(11) UNSIGNED DEFAULT NULL,
        `name`                 VARCHAR(150)     NOT NULL,
        `type`                 ENUM(\'thermal\',\'a4\',\'a5\') NOT NULL DEFAULT \'thermal\',
        `format`               ENUM(\'html\',\'pdf\') NOT NULL DEFAULT \'html\',
        `letterhead`           TINYINT(1)       NOT NULL DEFAULT 0,
        `show_logo`            TINYINT(1)       NOT NULL DEFAULT 1,
        `show_tax_breakdown`   TINYINT(1)       NOT NULL DEFAULT 1,
        `show_barcode`         TINYINT(1)       NOT NULL DEFAULT 1,
        `print_heading`        VARCHAR(200)     DEFAULT NULL,
        `terms_conditions`     TEXT             DEFAULT NULL,
        `footer_text`          TEXT             DEFAULT NULL,
        `template_html`        LONGTEXT         DEFAULT NULL,
        `is_default`           TINYINT(1)       NOT NULL DEFAULT 0,
        `is_active`            TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_pt_branch`  (`branch_id`),
        INDEX `idx_pt_default` (`is_default`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    // Seed default thermal template
    $thermal_html = '<div class="receipt thermal">
<div class="header text-center">
  <h2>{{company_name}}</h2>
  <p>{{branch_address}}</p>
  <p>Tel: {{branch_phone}}</p>
  {{#if tax_pin}}<p>{{tax_authority}}: {{tax_pin}}</p>{{/if}}
</div>
<hr>
<table class="items">
  <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
  {{#each items}}
  <tr>
    <td>{{name}}</td>
    <td>{{quantity}}</td>
    <td>{{unit_price}}</td>
    <td>{{line_total}}</td>
  </tr>
  {{/each}}
</table>
<hr>
<table class="totals">
  <tr><td>Subtotal</td><td>{{subtotal}}</td></tr>
  {{#if discount}}<tr><td>Discount</td><td>-{{discount}}</td></tr>{{/if}}
  {{#if tax}}<tr><td>Tax</td><td>{{tax}}</td></tr>{{/if}}
  <tr class="bold"><td>TOTAL</td><td>{{total}}</td></tr>
  <tr><td>Paid</td><td>{{amount_paid}}</td></tr>
  {{#if change}}<tr><td>Change</td><td>{{change}}</td></tr>{{/if}}
</table>
<hr>
<div class="footer text-center">
  <p>Receipt #: {{receipt_number}}</p>
  <p>{{date_created}}</p>
  <p>Served by: {{staff_name}}</p>
  {{#if terms}}<p>{{terms}}</p>{{/if}}
  <p>Thank you for your business!</p>
</div>
</div>';

    $CI->db->query("INSERT INTO `" . db_prefix() . "pos_print_templates`
        (`name`, `type`, `format`, `show_logo`, `show_tax_breakdown`, `show_barcode`, `template_html`, `is_default`)
        VALUES ('Default Thermal Receipt', 'thermal', 'html', 1, 1, 1, '" . $CI->db->escape_str($thermal_html) . "', 1)");
}

// ─── POS INVOICES (link to Perfex invoices) ────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_invoices')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_invoices` (
        `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sale_id`              INT(11) UNSIGNED NOT NULL,
        `branch_id`            INT(11) UNSIGNED NOT NULL,
        `perfex_invoice_id`    INT(11)          DEFAULT NULL COMMENT \'FK to tblinvoices\',
        `invoice_number`       VARCHAR(50)      NOT NULL UNIQUE,
        `status`               ENUM(\'draft\',\'submitted\',\'paid\',\'partial\',\'cancelled\',\'credit_note\') NOT NULL DEFAULT \'draft\',
        `subtotal`             DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_amount`           DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_amount`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `total`                DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `amount_paid`          DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `currency`             VARCHAR(10)      NOT NULL DEFAULT \'KES\',
        `customer_id`          INT(11) UNSIGNED DEFAULT NULL,
        `customer_name`        VARCHAR(200)     DEFAULT NULL,
        `notes`                TEXT             DEFAULT NULL,
        `credit_note_for`      INT(11) UNSIGNED DEFAULT NULL COMMENT \'Original invoice for credit notes\',
        `date_created`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_sale_invoice` (`sale_id`),
        INDEX `idx_inv_branch`  (`branch_id`),
        INDEX `idx_inv_status`  (`status`),
        INDEX `idx_inv_perfex`  (`perfex_invoice_id`),
        FOREIGN KEY (`sale_id`)   REFERENCES `' . db_prefix() . 'pos_sales`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`branch_id`) REFERENCES `' . db_prefix() . 'pos_branches`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── INVENTORY DOCUMENT TABLES ────────────────────────────────────────────────

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_suppliers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_suppliers` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`           VARCHAR(200)     NOT NULL,
        `contact_person` VARCHAR(150)     DEFAULT NULL,
        `email`          VARCHAR(150)     DEFAULT NULL,
        `phone`          VARCHAR(50)      DEFAULT NULL,
        `address`        TEXT             DEFAULT NULL,
        `tax_pin`        VARCHAR(50)      DEFAULT NULL,
        `payment_terms`  VARCHAR(100)     DEFAULT NULL,
        `is_active`      TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_sup_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_brands')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_brands` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(150)     NOT NULL,
        `description`  TEXT             DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_units')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_units` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(100)     NOT NULL,
        `symbol`       VARCHAR(20)      DEFAULT NULL,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_unit_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── Add UNIQUE constraint on unit name if not already present ────────────────
$uq_exists = $CI->db->query(
    "SHOW INDEX FROM `" . db_prefix() . "pos_inv_units` WHERE Key_name = 'uq_unit_name'"
)->num_rows();
if (!$uq_exists) {
    // Deduplicate first (keep lowest id per name), then add constraint
    $CI->db->query(
        "DELETE t1 FROM `" . db_prefix() . "pos_inv_units` t1
         INNER JOIN `" . db_prefix() . "pos_inv_units` t2
         WHERE t1.id > t2.id AND t1.name = t2.name"
    );
    $CI->db->query(
        "ALTER TABLE `" . db_prefix() . "pos_inv_units` ADD UNIQUE KEY `uq_unit_name` (`name`)"
    );
}

// ─── Seed standard Units of Measure ──────────────────────────────────────────
$_std_uoms = [
    // ── Count / Quantity ──────────────────────────────────────────────────────
    ['Piece',                'pcs'],
    ['Unit',                 'unit'],
    ['Dozen',                'doz'],
    ['Gross',                'grs'],
    ['Pair',                 'pr'],
    ['Set',                  'set'],
    ['Kit',                  'kit'],
    ['Bundle',               'bdl'],
    ['Box',                  'box'],
    ['Carton',               'ctn'],
    ['Case',                 'case'],
    ['Pack',                 'pk'],
    ['Bag',                  'bag'],
    ['Sachet',               'sct'],
    ['Packet',               'pkt'],
    ['Roll',                 'roll'],
    ['Ream',                 'ream'],
    ['Sheet',                'sht'],
    ['Strip',                'strip'],
    ['Pallet',               'plt'],
    ['Crate',                'crt'],
    // ── Weight ────────────────────────────────────────────────────────────────
    ['Kilogram',             'kg'],
    ['Gram',                 'g'],
    ['Milligram',            'mg'],
    ['Metric Ton',           'MT'],
    ['Pound',                'lb'],
    ['Ounce',                'oz'],
    // ── Volume / Liquid ───────────────────────────────────────────────────────
    ['Litre',                'L'],
    ['Millilitre',           'ml'],
    ['Cubic Metre',          'm³'],
    ['Gallon',               'gal'],
    ['Fluid Ounce',          'fl oz'],
    ['Barrel',               'bbl'],
    ['Drum',                 'drm'],
    ['Can',                  'can'],
    ['Bottle',               'btl'],
    // ── Length ───────────────────────────────────────────────────────────────
    ['Metre',                'm'],
    ['Centimetre',           'cm'],
    ['Millimetre',           'mm'],
    ['Kilometre',            'km'],
    ['Inch',                 'in'],
    ['Foot',                 'ft'],
    ['Yard',                 'yd'],
    // ── Area ─────────────────────────────────────────────────────────────────
    ['Square Metre',         'm²'],
    ['Square Foot',          'ft²'],
    ['Square Inch',          'in²'],
    ['Acre',                 'ac'],
    ['Hectare',              'ha'],
    // ── Time / Service ────────────────────────────────────────────────────────
    ['Hour',                 'hr'],
    ['Day',                  'day'],
    ['Week',                 'wk'],
    ['Month',                'mo'],
    ['Year',                 'yr'],
    // ── East Africa specific ──────────────────────────────────────────────────
    ['Debe',                 'debe'],     // ~18 L tin used in EA
    ['Gorogoro',             'grg'],      // ~2 kg measure
    ['Quintal',              'qtl'],      // 100 kg, common for grain
];

$_tbl_units = db_prefix() . 'pos_inv_units';
foreach ($_std_uoms as [$_uname, $_usym]) {
    $CI->db->query(
        "INSERT IGNORE INTO `{$_tbl_units}` (`name`, `symbol`)
         VALUES ('" . $CI->db->escape_str($_uname) . "', '" . $CI->db->escape_str($_usym) . "')"
    );
}
unset($_std_uoms, $_tbl_units, $_uname, $_usym, $uq_exists);

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_purchase_orders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_purchase_orders` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `po_number`     VARCHAR(100)     NOT NULL,
        `supplier_id`   INT(11) UNSIGNED DEFAULT NULL,
        `supplier_name` VARCHAR(200)     DEFAULT NULL,
        `branch_id`     INT(11) UNSIGNED DEFAULT NULL,
        `order_date`    DATE             DEFAULT NULL,
        `expected_date` DATE             DEFAULT NULL,
        `status`        ENUM(\'draft\',\'sent\',\'partial\',\'received\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `total_amount`  DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `note`          TEXT             DEFAULT NULL,
        `created_by`    INT(11) UNSIGNED DEFAULT NULL,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_po_supplier` (`supplier_id`),
        INDEX `idx_po_status`   (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_receipts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_receipts` (
        `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `receipt_number`   VARCHAR(100)     NOT NULL,
        `docket_number`    VARCHAR(100)     DEFAULT NULL COMMENT \'Delivery docket from supplier\',
        `accounting_date`  DATE             DEFAULT NULL,
        `receipt_date`     DATE             NOT NULL,
        `po_id`            INT(11) UNSIGNED DEFAULT NULL,
        `po_number`        VARCHAR(100)     DEFAULT NULL,
        `supplier_id`      INT(11) UNSIGNED DEFAULT NULL,
        `supplier_name`    VARCHAR(200)     DEFAULT NULL,
        `buyer_name`       VARCHAR(200)     DEFAULT NULL,
        `project`          VARCHAR(200)     DEFAULT NULL,
        `type`             ENUM(\'standard\',\'emergency\',\'return_to_supplier\') NOT NULL DEFAULT \'standard\',
        `department`       VARCHAR(200)     DEFAULT NULL,
        `requester`        VARCHAR(200)     DEFAULT NULL,
        `deliverer`        VARCHAR(200)     DEFAULT NULL,
        `branch_id`        INT(11) UNSIGNED NOT NULL,
        `invoice_number`   VARCHAR(100)     DEFAULT NULL,
        `status`           ENUM(\'draft\',\'confirmed\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `goods_value`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `inventory_value`  DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_amount`       DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `total_amount`     DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `note`             TEXT             DEFAULT NULL,
        `created_by`       INT(11) UNSIGNED DEFAULT NULL,
        `date_created`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_rcv_branch`    (`branch_id`),
        INDEX `idx_rcv_supplier`  (`supplier_id`),
        INDEX `idx_rcv_status`    (`status`),
        INDEX `idx_rcv_date`      (`receipt_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_receipt_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_receipt_items` (
        `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `receipt_id`       INT(11) UNSIGNED NOT NULL,
        `product_id`       INT(11) UNSIGNED NOT NULL,
        `branch_id`        INT(11) UNSIGNED DEFAULT NULL,
        `quantity`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `unit_cost`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_rate_id`      INT(11) UNSIGNED DEFAULT NULL,
        `tax_rate_pct`     DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `tax_amount`       DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `lot_number`       VARCHAR(100)     DEFAULT NULL,
        `manufacture_date` DATE             DEFAULT NULL,
        `expiry_date`      DATE             DEFAULT NULL,
        `batch_number`     VARCHAR(100)     DEFAULT NULL,
        `line_total`       DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        PRIMARY KEY (`id`),
        INDEX `idx_ri_receipt`  (`receipt_id`),
        INDEX `idx_ri_product`  (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_deliveries')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_deliveries` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `delivery_number` VARCHAR(100)     NOT NULL,
        `delivery_date`   DATE             NOT NULL,
        `accounting_date` DATE             DEFAULT NULL,
        `branch_id`       INT(11) UNSIGNED NOT NULL,
        `customer_name`   VARCHAR(200)     DEFAULT NULL,
        `receiver`        VARCHAR(200)     DEFAULT NULL,
        `address`         TEXT             DEFAULT NULL,
        `project`         VARCHAR(200)     DEFAULT NULL,
        `type`            VARCHAR(50)      NOT NULL DEFAULT \'standard\',
        `department`      VARCHAR(200)     DEFAULT NULL,
        `requester`       VARCHAR(200)     DEFAULT NULL,
        `sales_person`    VARCHAR(200)     DEFAULT NULL,
        `invoice_number`  VARCHAR(100)     DEFAULT NULL,
        `invoice_id`      INT(11) UNSIGNED DEFAULT NULL,
        `reference`       VARCHAR(100)     DEFAULT NULL,
        `status`          ENUM(\'draft\',\'confirmed\',\'delivered\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `subtotal`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_amount` DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `shipping_fee`    DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `total_amount`    DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `note`            TEXT             DEFAULT NULL,
        `created_by`      INT(11) UNSIGNED DEFAULT NULL,
        `date_created`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_dlv_branch`  (`branch_id`),
        INDEX `idx_dlv_status`  (`status`),
        INDEX `idx_dlv_date`    (`delivery_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_delivery_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_delivery_items` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `delivery_id`     INT(11) UNSIGNED NOT NULL,
        `product_id`      INT(11) UNSIGNED NOT NULL,
        `branch_id`       INT(11) UNSIGNED DEFAULT NULL,
        `batch_number`    VARCHAR(100)     DEFAULT NULL,
        `quantity`        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `unit_price`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_rate_id`     INT(11) UNSIGNED DEFAULT NULL,
        `tax_rate_pct`    DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `tax_amount`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `subtotal`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_pct`    DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `discount_amount` DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `line_total`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        PRIMARY KEY (`id`),
        INDEX `idx_di_delivery` (`delivery_id`),
        INDEX `idx_di_product`  (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_transfers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_transfers` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `transfer_number` VARCHAR(100)     NOT NULL,
        `transfer_date`   DATE             NOT NULL,
        `from_branch_id`  INT(11) UNSIGNED NOT NULL,
        `to_branch_id`    INT(11) UNSIGNED NOT NULL,
        `status`          ENUM(\'draft\',\'in_transit\',\'completed\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `notes`           TEXT             DEFAULT NULL,
        `created_by`      INT(11) UNSIGNED DEFAULT NULL,
        `date_created`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_trf_from` (`from_branch_id`),
        INDEX `idx_trf_to`   (`to_branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_transfer_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_transfer_items` (
        `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `transfer_id` INT(11) UNSIGNED NOT NULL,
        `product_id`  INT(11) UNSIGNED NOT NULL,
        `quantity`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `notes`       TEXT             DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_ti_transfer` (`transfer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_adjustments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_adjustments` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `adjustment_number` VARCHAR(100)     NOT NULL,
        `adjustment_date`   DATE             NOT NULL,
        `branch_id`         INT(11) UNSIGNED NOT NULL,
        `type`              ENUM(\'loss\',\'gain\',\'correction\') NOT NULL DEFAULT \'correction\',
        `reason`            VARCHAR(255)     DEFAULT NULL,
        `status`            ENUM(\'draft\',\'confirmed\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `notes`             TEXT             DEFAULT NULL,
        `created_by`        INT(11) UNSIGNED DEFAULT NULL,
        `date_created`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_adj_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_adjustment_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_adjustment_items` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `adjustment_id` INT(11) UNSIGNED NOT NULL,
        `product_id`    INT(11) UNSIGNED NOT NULL,
        `qty_before`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `qty_change`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `qty_after`     DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `reason`        VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_ai_adjustment` (`adjustment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_returns')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_returns` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `return_number` VARCHAR(100)     NOT NULL,
        `return_date`   DATE             NOT NULL,
        `branch_id`     INT(11) UNSIGNED NOT NULL,
        `type`          ENUM(\'customer\',\'supplier\') NOT NULL DEFAULT \'customer\',
        `reference`     VARCHAR(100)     DEFAULT NULL,
        `status`        ENUM(\'draft\',\'confirmed\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `total_amount`  DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `notes`         TEXT             DEFAULT NULL,
        `created_by`    INT(11) UNSIGNED DEFAULT NULL,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_ret_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_return_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_return_items` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `return_id`       INT(11) UNSIGNED NOT NULL,
        `product_id`      INT(11) UNSIGNED NOT NULL,
        `quantity`        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `unit_price`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `condition_notes` VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_reti_return` (`return_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_packing_lists')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_packing_lists` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `packing_number` VARCHAR(100)     NOT NULL,
        `packing_date`   DATE             NOT NULL,
        `branch_id`      INT(11) UNSIGNED NOT NULL,
        `delivery_id`    INT(11) UNSIGNED DEFAULT NULL,
        `status`         ENUM(\'draft\',\'packed\',\'dispatched\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `notes`          TEXT             DEFAULT NULL,
        `created_by`     INT(11) UNSIGNED DEFAULT NULL,
        `date_created`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_pk_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_packing_list_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_packing_list_items` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `packing_list_id`INT(11) UNSIGNED NOT NULL,
        `product_id`     INT(11) UNSIGNED NOT NULL,
        `quantity`       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `package_number` VARCHAR(100)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_pli_list` (`packing_list_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ITEM FORM v2: brand_id + unit_id on pos_products ────────────────────────
if (!$CI->db->field_exists('brand_id', db_prefix() . 'pos_products')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'pos_products`
        ADD COLUMN `brand_id` INT(11) UNSIGNED DEFAULT NULL,
        ADD COLUMN `unit_id`  INT(11) UNSIGNED DEFAULT NULL
    ');
}

// ─── ITEM FORM v2: Extended fields on pos_products ───────────────────────────
if (!$CI->db->field_exists('item_code', db_prefix() . 'pos_products')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'pos_products`
        ADD COLUMN `item_code`                     VARCHAR(50)   DEFAULT NULL,
        ADD COLUMN `status`                        ENUM(\'Enabled\',\'Disabled\') NOT NULL DEFAULT \'Enabled\',
        ADD COLUMN `allow_alternative`             TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `is_fixed_asset`                TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `over_delivery_allowance`       DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        ADD COLUMN `over_billing_allowance`        DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        ADD COLUMN `valuation_method`              ENUM(\'FIFO\',\'Moving Average\',\'LIFO\') NOT NULL DEFAULT \'FIFO\',
        ADD COLUMN `valuation_rate`                DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
        ADD COLUMN `shelf_life_days`               INT(11)       NOT NULL DEFAULT 0,
        ADD COLUMN `warranty_days`                 INT(11)       NOT NULL DEFAULT 0,
        ADD COLUMN `end_of_life`                   DATE          DEFAULT NULL,
        ADD COLUMN `weight_per_unit`               DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        ADD COLUMN `default_material_request_type` ENUM(\'Purchase\',\'Manufacture\',\'Transfer\',\'Customer Provided\') NOT NULL DEFAULT \'Purchase\',
        ADD COLUMN `weight_uom_id`                 INT(11)       DEFAULT NULL,
        ADD COLUMN `has_batch_no`                  TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `has_serial_no`                 TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `default_purchase_uom_id`       INT(11)       DEFAULT NULL,
        ADD COLUMN `lead_time_days`                INT(11)       NOT NULL DEFAULT 0,
        ADD COLUMN `min_order_qty`                 DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        ADD COLUMN `last_purchase_rate`            DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
        ADD COLUMN `safety_stock`                  DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        ADD COLUMN `is_customer_provided`          TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `allow_purchase`                TINYINT(1)    NOT NULL DEFAULT 1,
        ADD COLUMN `drop_ship`                     TINYINT(1)    NOT NULL DEFAULT 0,
        ADD COLUMN `country_of_origin`             VARCHAR(100)  DEFAULT NULL,
        ADD COLUMN `customs_tariff_number`         VARCHAR(100)  DEFAULT NULL,
        ADD COLUMN `income_account`                VARCHAR(150)  DEFAULT NULL,
        ADD COLUMN `expense_account`               VARCHAR(150)  DEFAULT NULL,
        ADD COLUMN `apply_uoms_for_variants`       TINYINT(1)    NOT NULL DEFAULT 0
    ');
}

// ─── ITEM UOMs child table ────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_uoms')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_uoms` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`        INT(11) UNSIGNED NOT NULL,
        `uom_id`            INT(11) UNSIGNED DEFAULT NULL,
        `uom_name`          VARCHAR(80)      NOT NULL,
        `conversion_factor` DECIMAL(15,6)    NOT NULL DEFAULT 1.000000,
        `sort_order`        INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_iuom_product` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ITEM BARCODES child table ────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_barcodes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_barcodes` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`   INT(11) UNSIGNED NOT NULL,
        `barcode`      VARCHAR(100)     NOT NULL,
        `barcode_type` ENUM(\'EAN\',\'UPC\',\'QR\',\'Code128\',\'Code39\',\'ISBN\',\'ISSN\',\'Custom\') DEFAULT \'EAN\',
        `uom_id`       INT(11) UNSIGNED DEFAULT NULL,
        `sort_order`   INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_ibc_product` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ITEM RE-ORDER RULES child table ─────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_reorder_rules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_reorder_rules` (
        `id`                    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`            INT(11) UNSIGNED NOT NULL,
        `branch_id`             INT(11) UNSIGNED DEFAULT NULL,
        `check_availability_in` INT(11) UNSIGNED DEFAULT NULL,
        `reorder_level`         DECIMAL(10,3)    NOT NULL DEFAULT 0.000,
        `reorder_qty`           DECIMAL(10,3)    NOT NULL DEFAULT 0.000,
        `material_request_type` ENUM(\'Purchase\',\'Manufacture\',\'Transfer\',\'Customer Provided\') DEFAULT \'Purchase\',
        `sort_order`            INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_ror_product` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ITEM SUPPLIER DETAILS child table ───────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_suppliers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_suppliers` (
        `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`         INT(11) UNSIGNED NOT NULL,
        `supplier_id`        INT(11) UNSIGNED DEFAULT NULL,
        `supplier_name`      VARCHAR(200)     DEFAULT NULL,
        `supplier_part_no`   VARCHAR(100)     DEFAULT NULL,
        `lead_time_days`     INT(11)          NOT NULL DEFAULT 0,
        `min_qty`            DECIMAL(10,3)    NOT NULL DEFAULT 0.000,
        `last_purchase_rate` DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `sort_order`         INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_isup_product` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_stock_takes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_stock_takes` (
        `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `stocktake_number` VARCHAR(100)     NOT NULL,
        `start_date`       DATE             NOT NULL,
        `end_date`         DATE             DEFAULT NULL,
        `branch_id`        INT(11) UNSIGNED NOT NULL,
        `status`           ENUM(\'draft\',\'in_progress\',\'completed\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `notes`            TEXT             DEFAULT NULL,
        `created_by`       INT(11) UNSIGNED DEFAULT NULL,
        `date_created`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_st_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_stock_take_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_stock_take_items` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `stock_take_id` INT(11) UNSIGNED NOT NULL,
        `product_id`    INT(11) UNSIGNED NOT NULL,
        `qty_system`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `qty_counted`   DECIMAL(10,2)    DEFAULT NULL,
        `variance`      DECIMAL(10,2)    DEFAULT NULL,
        `notes`         VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_sti_take` (`stock_take_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_approval_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_approval_settings` (
        `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sort_order`       INT(11) UNSIGNED NOT NULL DEFAULT 0,
        `subject`          VARCHAR(255)     NOT NULL,
        `related`          VARCHAR(50)      NOT NULL,
        `single_approver`  TINYINT(1)       NOT NULL DEFAULT 0,
        `approvers`        TEXT             DEFAULT NULL COMMENT \'JSON: [{staff_id, action}]\',
        `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
        `created_by`       INT(11) UNSIGNED DEFAULT NULL,
        `date_created`     DATETIME         DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_apv_related` (`related`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── ALTER EXISTING TABLES: add new columns ────────────────────────────────────
// pos_sales: UTM tracking + profile_id
if (!$CI->db->field_exists('profile_id', db_prefix() . 'pos_sales')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_sales` ADD COLUMN `profile_id` INT(11) UNSIGNED DEFAULT NULL AFTER `session_id`");
}
if (!$CI->db->field_exists('utm_source', db_prefix() . 'pos_sales')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_sales` ADD COLUMN `utm_source`   VARCHAR(100) DEFAULT NULL AFTER `notes`");
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_sales` ADD COLUMN `utm_medium`   VARCHAR(100) DEFAULT NULL AFTER `utm_source`");
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_sales` ADD COLUMN `utm_campaign` VARCHAR(100) DEFAULT NULL AFTER `utm_medium`");
}

// pos_payment_methods: Perfex gateway mapping + accounting key
if (!$CI->db->field_exists('perfex_gateway', db_prefix() . 'pos_payment_methods')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_payment_methods`
        ADD COLUMN `perfex_gateway`   VARCHAR(100) DEFAULT NULL COMMENT 'Maps to Perfex payment gateway name' AFTER `provider`,
        ADD COLUMN `allow_in_returns` TINYINT(1)   NOT NULL DEFAULT 1 AFTER `perfex_gateway`,
        ADD COLUMN `is_default`       TINYINT(1)   NOT NULL DEFAULT 0 AFTER `allow_in_returns`,
        ADD COLUMN `account_key`      VARCHAR(100) DEFAULT NULL COMMENT 'Accounting ledger key' AFTER `is_default`
    ");
    // Set cash as default
    $CI->db->query("UPDATE `" . db_prefix() . "pos_payment_methods` SET `is_default`=1 WHERE `code`='cash' LIMIT 1");
}

// pos_customers: customer group assignment
if (!$CI->db->field_exists('customer_group_id', db_prefix() . 'pos_customers')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_customers` ADD COLUMN `customer_group_id` INT(11) UNSIGNED DEFAULT NULL AFTER `branch_id`");
}

// ─── ACCOUNTING JOURNALS + ACTIVITY LOGS ─────────────────────────────────────
// Include the model file directly so the static method is available during install
require_once module_dir_path(POS_SYSTEM_MODULE_NAME) . 'models/Pos_accounting_model.php';
Pos_accounting_model::create_tables();

// ─── ADDITIONAL COLUMNS (idempotent) ──────────────────────────────────────────

// perfex_mode_id: tracks which Perfex native payment mode a pos_payment_methods
// row was synced from (prevents duplicate syncing in Payments_api).
if ($CI->db->table_exists(db_prefix() . 'pos_payment_methods')
    && !$CI->db->field_exists('perfex_mode_id', db_prefix() . 'pos_payment_methods')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_payment_methods`
        ADD COLUMN `perfex_mode_id` INT(11) DEFAULT NULL
        COMMENT 'FK to tblpaymentmodes — set when synced from Perfex payment modes'
    ");
}

// ─── max_stock column on pos_products (added for min/max stock levels feature) ─
if ($CI->db->table_exists(db_prefix() . 'pos_products')
    && !$CI->db->field_exists('max_stock', db_prefix() . 'pos_products')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_products`
        ADD COLUMN `max_stock` DECIMAL(10,2) DEFAULT NULL AFTER `reorder_point`
    ");
}

// ─── pos_inv_deliveries: add new columns to existing basic table ──────────────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_deliveries')) {
    $dlv_cols = [
        'accounting_date' => "ADD COLUMN `accounting_date` DATE DEFAULT NULL AFTER `delivery_date`",
        'receiver'        => "ADD COLUMN `receiver`        VARCHAR(200) DEFAULT NULL AFTER `customer_name`",
        'address'         => "ADD COLUMN `address`         TEXT         DEFAULT NULL AFTER `receiver`",
        'project'         => "ADD COLUMN `project`         VARCHAR(200) DEFAULT NULL AFTER `address`",
        'type'            => "ADD COLUMN `type`            VARCHAR(50)  NOT NULL DEFAULT 'standard' AFTER `project`",
        'department'      => "ADD COLUMN `department`      VARCHAR(200) DEFAULT NULL AFTER `type`",
        'requester'       => "ADD COLUMN `requester`       VARCHAR(200) DEFAULT NULL AFTER `department`",
        'sales_person'    => "ADD COLUMN `sales_person`    VARCHAR(200) DEFAULT NULL AFTER `requester`",
        'invoice_number'  => "ADD COLUMN `invoice_number`  VARCHAR(100) DEFAULT NULL AFTER `sales_person`",
        'invoice_id'      => "ADD COLUMN `invoice_id`      INT(11) UNSIGNED DEFAULT NULL AFTER `invoice_number`",
        'subtotal'        => "ADD COLUMN `subtotal`        DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `reference`",
        'discount_amount' => "ADD COLUMN `discount_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `subtotal`",
        'shipping_fee'    => "ADD COLUMN `shipping_fee`    DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `discount_amount`",
        'note'            => "ADD COLUMN `note`            TEXT          DEFAULT NULL AFTER `notes`",
    ];
    foreach ($dlv_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_deliveries')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries` " . $def);
        }
    }
}

// ─── pos_inv_delivery_items: add new columns to existing basic table ───────────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_delivery_items')) {
    $dli_cols = [
        'branch_id'       => "ADD COLUMN `branch_id`       INT(11) UNSIGNED DEFAULT NULL AFTER `delivery_id`",
        'tax_rate_id'     => "ADD COLUMN `tax_rate_id`     INT(11) UNSIGNED DEFAULT NULL AFTER `unit_price`",
        'tax_rate_pct'    => "ADD COLUMN `tax_rate_pct`    DECIMAL(6,4)  NOT NULL DEFAULT 0.0000 AFTER `tax_rate_id`",
        'tax_amount'      => "ADD COLUMN `tax_amount`      DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `tax_rate_pct`",
        'subtotal'        => "ADD COLUMN `subtotal`        DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `tax_amount`",
        'discount_pct'    => "ADD COLUMN `discount_pct`    DECIMAL(6,4)  NOT NULL DEFAULT 0.0000 AFTER `subtotal`",
        'discount_amount' => "ADD COLUMN `discount_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `discount_pct`",
    ];
    foreach ($dli_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_delivery_items')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_delivery_items` " . $def);
        }
    }
}

// ─── pos_inv_receipts: add columns missing from older table versions ───────────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_receipts')) {
    $rcv_cols = [
        'docket_number'   => "ADD COLUMN `docket_number`   VARCHAR(100)  DEFAULT NULL",
        'accounting_date' => "ADD COLUMN `accounting_date` DATE          DEFAULT NULL",
        'po_id'           => "ADD COLUMN `po_id`           INT(11) UNSIGNED DEFAULT NULL",
        'po_number'       => "ADD COLUMN `po_number`       VARCHAR(100)  DEFAULT NULL",
        'supplier_id'     => "ADD COLUMN `supplier_id`     INT(11) UNSIGNED DEFAULT NULL",
        'supplier_name'   => "ADD COLUMN `supplier_name`   VARCHAR(200)  DEFAULT NULL",
        'buyer_name'      => "ADD COLUMN `buyer_name`      VARCHAR(200)  DEFAULT NULL",
        'project'         => "ADD COLUMN `project`         VARCHAR(200)  DEFAULT NULL",
        'type'            => "ADD COLUMN `type`            ENUM('standard','emergency','return_to_supplier') NOT NULL DEFAULT 'standard'",
        'department'      => "ADD COLUMN `department`      VARCHAR(200)  DEFAULT NULL",
        'requester'       => "ADD COLUMN `requester`       VARCHAR(200)  DEFAULT NULL",
        'deliverer'       => "ADD COLUMN `deliverer`       VARCHAR(200)  DEFAULT NULL",
        'invoice_number'  => "ADD COLUMN `invoice_number`  VARCHAR(100)  DEFAULT NULL",
        'goods_value'     => "ADD COLUMN `goods_value`     DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
        'inventory_value' => "ADD COLUMN `inventory_value` DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
        'tax_amount'      => "ADD COLUMN `tax_amount`      DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
        'note'            => "ADD COLUMN `note`            TEXT          DEFAULT NULL",
    ];
    foreach ($rcv_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_receipts')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_receipts` " . $def);
        }
    }
}

// ─── pos_inv_receipt_items: add columns missing from older table versions ──────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_receipt_items')) {
    $rci_cols = [
        'branch_id'       => "ADD COLUMN `branch_id`       INT(11) UNSIGNED DEFAULT NULL",
        'tax_rate_id'     => "ADD COLUMN `tax_rate_id`     INT(11) UNSIGNED DEFAULT NULL",
        'tax_rate_pct'    => "ADD COLUMN `tax_rate_pct`    DECIMAL(6,4)  NOT NULL DEFAULT 0.0000",
        'tax_amount'      => "ADD COLUMN `tax_amount`      DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
        'lot_number'      => "ADD COLUMN `lot_number`      VARCHAR(100)  DEFAULT NULL",
        'manufacture_date'=> "ADD COLUMN `manufacture_date` DATE         DEFAULT NULL",
        'expiry_date'     => "ADD COLUMN `expiry_date`     DATE          DEFAULT NULL",
        'batch_number'    => "ADD COLUMN `batch_number`    VARCHAR(100)  DEFAULT NULL",
        'line_total'      => "ADD COLUMN `line_total`      DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
    ];
    foreach ($rci_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_receipt_items')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_receipt_items` " . $def);
        }
    }
}

// ─── SALES ORDERS ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_sales_orders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_sales_orders` (
        `id`                   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `so_number`            VARCHAR(50)      NOT NULL UNIQUE,
        `branch_id`            INT(11) UNSIGNED NOT NULL,
        `client_id`            INT(11)          DEFAULT NULL,
        `customer_name`        VARCHAR(255)     DEFAULT NULL,
        `address`              TEXT             DEFAULT NULL,
        `project_id`           INT(11)          DEFAULT NULL,
        `project_name`         VARCHAR(255)     DEFAULT NULL,
        `sales_person_id`      INT(11)          DEFAULT NULL,
        `sales_person`         VARCHAR(200)     DEFAULT NULL,
        `date`                 DATE             NOT NULL,
        `expected_delivery`    DATE             DEFAULT NULL,
        `status`               ENUM(\'draft\',\'confirmed\',\'processing\',\'partially_delivered\',\'delivered\',\'cancelled\') NOT NULL DEFAULT \'draft\',
        `notes`                TEXT             DEFAULT NULL,
        `subtotal`             DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_amount`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_amount`           DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `shipping_fee`         DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `total_amount`         DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `crm_invoice_id`       INT(11)          DEFAULT NULL,
        `created_by`           INT(11)          DEFAULT NULL,
        `date_created`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_so_branch`  (`branch_id`),
        INDEX `idx_so_status`  (`status`),
        INDEX `idx_so_client`  (`client_id`),
        INDEX `idx_so_date`    (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── SALES ORDER ITEMS ────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_sales_order_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_sales_order_items` (
        `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sales_order_id`  INT(11) UNSIGNED NOT NULL,
        `product_id`      INT(11) UNSIGNED DEFAULT NULL,
        `branch_id`       INT(11) UNSIGNED DEFAULT NULL,
        `product_name`    VARCHAR(255)     NOT NULL,
        `qty_ordered`     DECIMAL(15,4)    NOT NULL DEFAULT 1.0000,
        `qty_delivered`   DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `unit_price`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `discount_pct`    DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `discount_amount` DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `tax_rate_id`     INT(11) UNSIGNED DEFAULT NULL,
        `tax_rate_pct`    DECIMAL(6,4)     NOT NULL DEFAULT 0.0000,
        `tax_amount`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `subtotal`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `line_total`      DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `sort_order`      INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_soi_order` (`sales_order_id`),
        INDEX `idx_soi_prod`  (`product_id`),
        FOREIGN KEY (`sales_order_id`) REFERENCES `' . db_prefix() . 'pos_sales_orders`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── pos_inv_deliveries: add sales_order_id if missing ───────────────────────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_deliveries')) {
    if (!$CI->db->field_exists('sales_order_id', db_prefix() . 'pos_inv_deliveries')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries`
            ADD COLUMN `sales_order_id` INT(11) UNSIGNED DEFAULT NULL AFTER `invoice_id`");
    }
    if (!$CI->db->field_exists('crm_invoice_id', db_prefix() . 'pos_inv_deliveries')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries`
            ADD COLUMN `crm_invoice_id` INT(11) DEFAULT NULL AFTER `sales_order_id`");
    }
    if (!$CI->db->field_exists('client_id', db_prefix() . 'pos_inv_deliveries')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries`
            ADD COLUMN `client_id` INT(11) DEFAULT NULL AFTER `crm_invoice_id`");
    }
    if (!$CI->db->field_exists('project_id', db_prefix() . 'pos_inv_deliveries')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries`
            ADD COLUMN `project_id` INT(11) DEFAULT NULL AFTER `project`");
    }
    if (!$CI->db->field_exists('sales_person_id', db_prefix() . 'pos_inv_deliveries')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_deliveries`
            ADD COLUMN `sales_person_id` INT(11) DEFAULT NULL AFTER `sales_person`");
    }
}

// ─── ADDITIONAL GLOBAL SETTINGS (INSERT IGNORE — safe to re-run) ──────────────
if ($CI->db->table_exists(db_prefix() . 'pos_settings')) {
    $extra_defaults = [
        // Auto-generate a Perfex invoice whenever a POS sale is completed.
        // Set to '0' in pos_settings to disable per branch.
        ['pos_auto_invoice',          '1'],
        // ID of the default "Walk-In" customer used when no customer is selected.
        // Empty string = no default (cashier must select or leave blank).
        ['pos_default_customer_id',   ''],
        // Receipt print format: 'thermal' (80mm roll), 'a4', or 'pos' (small slip).
        ['pos_receipt_format',        'thermal'],
        // Whether the receipt prints automatically after sale completion.
        ['pos_auto_print_receipt',    '1'],
        // Use Perfex-generated payment receipt PDF instead of POS built-in.
        ['pos_use_perfex_receipt',    '0'],
        // Whether to show active Perfex payment gateways in the checkout modal.
        ['pos_show_perfex_gateways',  '1'],
        // Per-item discount: allow cashier to set a discount on individual items.
        ['pos_allow_item_discount',   '1'],
        // Maximum % discount a cashier can apply per item (0 = unlimited).
        ['pos_max_item_discount_pct', '100'],
    ];
    foreach ($extra_defaults as [$key, $val]) {
        $CI->db->query(
            "INSERT IGNORE INTO `" . db_prefix() . "pos_settings`
             (`branch_id`, `setting_key`, `setting_value`)
             VALUES (NULL, '" . $CI->db->escape_str($key) . "', '" . $CI->db->escape_str($val) . "')"
        );
    }
}

// ─── Additional product columns (item registration form) ─────────────────────
$_prod_cols = [
    'item_code'                         => 'VARCHAR(50) DEFAULT NULL',
    'allow_alternative'                 => 'TINYINT(1) NOT NULL DEFAULT 0',
    'is_fixed_asset'                    => 'TINYINT(1) NOT NULL DEFAULT 0',
    'over_delivery_allowance'           => 'DECIMAL(5,2) NOT NULL DEFAULT 0',
    'over_billing_allowance'            => 'DECIMAL(5,2) NOT NULL DEFAULT 0',
    'valuation_method'                  => "ENUM('FIFO','Moving Average','LIFO') NOT NULL DEFAULT 'Moving Average'",
    'valuation_rate'                    => 'DECIMAL(15,4) NOT NULL DEFAULT 0',
    'shelf_life_days'                   => 'INT(11) NOT NULL DEFAULT 0',
    'warranty_days'                     => 'INT(11) NOT NULL DEFAULT 0',
    'end_of_life'                       => "DATE DEFAULT '2099-12-31'",
    'weight_per_unit'                   => 'DECIMAL(10,3) NOT NULL DEFAULT 0',
    'weight_uom_id'                     => 'INT(11) UNSIGNED DEFAULT NULL',
    'default_material_request_type'     => "ENUM('Purchase','Manufacture','Transfer','Customer Provided') NOT NULL DEFAULT 'Purchase'",
    'has_batch_no'                      => 'TINYINT(1) NOT NULL DEFAULT 0',
    'has_serial_no'                     => 'TINYINT(1) NOT NULL DEFAULT 0',
    'create_new_batch'                  => 'TINYINT(1) NOT NULL DEFAULT 0',
    'has_expiry_date'                   => 'TINYINT(1) NOT NULL DEFAULT 0',
    'retain_sample'                     => 'TINYINT(1) NOT NULL DEFAULT 0',
    'serial_number_series'              => 'VARCHAR(100) DEFAULT NULL',
    'batch_number_series'               => 'VARCHAR(100) DEFAULT NULL',
    'do_not_use_batchwise_valuation'    => 'TINYINT(1) NOT NULL DEFAULT 0',
    'auto_create_serial_batch_bundle_outward' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'pick_serial_batch_based_on'        => "ENUM('FIFO','LIFO','Expiry') NOT NULL DEFAULT 'FIFO'",
    'disable_serial_batch_selector'     => 'TINYINT(1) NOT NULL DEFAULT 0',
    'use_serial_batch_fields'           => 'TINYINT(1) NOT NULL DEFAULT 0',
    'do_not_update_serial_batch_on_auto_bundle' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'allow_negative_stock_for_batch'    => 'TINYINT(1) NOT NULL DEFAULT 0',
    'set_bundle_naming_on_naming_series'=> 'TINYINT(1) NOT NULL DEFAULT 0',
    'use_naming_series_for_batch'       => 'TINYINT(1) NOT NULL DEFAULT 0',
    'default_purchase_uom_id'           => 'INT(11) UNSIGNED DEFAULT NULL',
    'lead_time_days'                    => 'INT(11) NOT NULL DEFAULT 0',
    'min_order_qty'                     => 'DECIMAL(10,3) NOT NULL DEFAULT 0',
    'last_purchase_rate'                => 'DECIMAL(15,4) NOT NULL DEFAULT 0',
    'safety_stock'                      => 'DECIMAL(10,3) NOT NULL DEFAULT 0',
    'is_customer_provided'              => 'TINYINT(1) NOT NULL DEFAULT 0',
    'allow_purchase'                    => 'TINYINT(1) NOT NULL DEFAULT 1',
    'allow_sales'                       => 'TINYINT(1) NOT NULL DEFAULT 1',
    'is_sales_item'                     => 'TINYINT(1) NOT NULL DEFAULT 1',
    'drop_ship'                         => 'TINYINT(1) NOT NULL DEFAULT 0',
    'country_of_origin'                 => "VARCHAR(100) DEFAULT 'Kenya'",
    'customs_tariff_number'             => 'VARCHAR(100) DEFAULT NULL',
    'income_account'                    => 'VARCHAR(100) DEFAULT NULL',
    'expense_account'                   => 'VARCHAR(100) DEFAULT NULL',
    'apply_uoms_for_variants'           => 'TINYINT(1) NOT NULL DEFAULT 0',
    'default_sales_uom_id'              => 'INT(11) UNSIGNED DEFAULT NULL',
];
foreach ($_prod_cols as $_pc => $_pd) {
    if (!$CI->db->field_exists($_pc, db_prefix() . 'pos_products')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_products` ADD COLUMN `{$_pc}` {$_pd}");
    }
}
unset($_prod_cols, $_pc, $_pd);

// Unique key on item_code
$_uq = $CI->db->query("SHOW INDEX FROM `" . db_prefix() . "pos_products` WHERE Key_name = 'uq_item_code'")->num_rows();
if (!$_uq) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_products` ADD UNIQUE KEY `uq_item_code` (`item_code`)");
}
unset($_uq);

// ─── Item child tables ────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_item_uoms')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_uoms` (
        `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`        INT(11) UNSIGNED NOT NULL,
        `uom_id`            INT(11) UNSIGNED DEFAULT NULL,
        `uom_name`          VARCHAR(100)     NOT NULL DEFAULT "",
        `conversion_factor` DECIMAL(15,6)    NOT NULL DEFAULT 1,
        `sort_order`        INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_iuom_prod` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_item_barcodes')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "pos_item_barcodes` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`   INT(11) UNSIGNED NOT NULL,
        `barcode`      VARCHAR(200)     NOT NULL,
        `barcode_type` ENUM('EAN','UPC','QR','Code128','Code39','ISBN','ISSN','Custom') NOT NULL DEFAULT 'EAN',
        `uom_id`       INT(11) UNSIGNED DEFAULT NULL,
        `is_primary`   TINYINT(1)       NOT NULL DEFAULT 0,
        `sort_order`   INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_ibar_prod`    (`product_id`),
        INDEX `idx_ibar_barcode` (`barcode`),
        FOREIGN KEY (`product_id`) REFERENCES `" . db_prefix() . "pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} else {
    // Add is_primary if missing
    if (!$CI->db->field_exists('is_primary', db_prefix() . 'pos_item_barcodes')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_item_barcodes` ADD COLUMN `is_primary` TINYINT(1) NOT NULL DEFAULT 0");
    }
}

if (!$CI->db->table_exists(db_prefix() . 'pos_item_reorder_rules')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "pos_item_reorder_rules` (
        `id`                     INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`             INT(11) UNSIGNED NOT NULL,
        `branch_id`              INT(11) UNSIGNED DEFAULT NULL,
        `check_availability_in`  INT(11) UNSIGNED DEFAULT NULL,
        `reorder_level`          DECIMAL(15,3)    NOT NULL DEFAULT 0,
        `reorder_qty`            DECIMAL(15,3)    NOT NULL DEFAULT 0,
        `material_request_type`  ENUM('Purchase','Manufacture','Transfer','Customer Provided') NOT NULL DEFAULT 'Purchase',
        `last_triggered_at`      DATETIME         DEFAULT NULL,
        `sort_order`             INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_rorule_prod` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `" . db_prefix() . "pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

if (!$CI->db->table_exists(db_prefix() . 'pos_item_suppliers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_suppliers` (
        `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`         INT(11) UNSIGNED NOT NULL,
        `supplier_id`        INT(11) UNSIGNED DEFAULT NULL,
        `supplier_name`      VARCHAR(200)     DEFAULT NULL,
        `supplier_part_no`   VARCHAR(100)     DEFAULT NULL,
        `lead_time_days`     INT(11)          NOT NULL DEFAULT 0,
        `min_qty`            DECIMAL(15,3)    NOT NULL DEFAULT 0,
        `last_purchase_rate` DECIMAL(15,4)    NOT NULL DEFAULT 0,
        `sort_order`         INT(11)          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `idx_isup_prod` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_item_stock_layers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_item_stock_layers` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `item_id`       INT(11) UNSIGNED NOT NULL,
        `branch_id`     INT(11) UNSIGNED DEFAULT NULL,
        `qty`           DECIMAL(15,6)   NOT NULL DEFAULT 0,
        `rate`          DECIMAL(15,4)   NOT NULL DEFAULT 0,
        `incoming_date` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_layer_item`   (`item_id`),
        INDEX `idx_layer_branch` (`branch_id`),
        FOREIGN KEY (`item_id`) REFERENCES `' . db_prefix() . 'pos_products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── Serial / Batch global settings table ────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'pos_serial_batch_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_serial_batch_settings` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `setting_key`   VARCHAR(100)     NOT NULL,
        `setting_value` TEXT             DEFAULT NULL,
        `updated_at`    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_sbs_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}
$_sbs = [
    ['enable_serial_and_batch_no_for_item',                     '0'],
    ['allow_existing_serial_no',                                '0'],
    ['do_not_use_batchwise_valuation',                          '0'],
    ['auto_create_serial_and_batch_bundle_for_outward',         '0'],
    ['pick_serial_and_batch_based_on',                          'FIFO'],
    ['disable_serial_no_and_batch_selector',                    '0'],
    ['use_serial_batch_fields',                                 '0'],
    ['do_not_update_serial_batch_on_creation_of_auto_bundle',   '0'],
    ['allow_negative_stock_for_batch',                          '0'],
    ['set_serial_and_batch_bundle_naming_based_on_naming_series','0'],
    ['use_naming_series',                                       '0'],
];
foreach ($_sbs as [$_sk, $_sv]) {
    $CI->db->query(
        "INSERT IGNORE INTO `" . db_prefix() . "pos_serial_batch_settings`
         (`setting_key`, `setting_value`) VALUES ('" . $CI->db->escape_str($_sk) . "', '" . $CI->db->escape_str($_sv) . "')"
    );
}
unset($_sbs, $_sk, $_sv);

// ─── RESTAURANT MANAGEMENT ───────────────────────────────────────────────────

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_areas')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_areas` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`    INT(11) UNSIGNED NOT NULL,
        `name`         VARCHAR(100)     NOT NULL,
        `type`         ENUM(\'kitchen\',\'bar\',\'bakery\',\'grill\',\'other\') NOT NULL DEFAULT \'kitchen\',
        `display_order` INT(11)         NOT NULL DEFAULT 0,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_ra_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_tables')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_tables` (
        `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `branch_id`    INT(11) UNSIGNED NOT NULL,
        `table_number` VARCHAR(20)      NOT NULL,
        `name`         VARCHAR(100)     DEFAULT NULL,
        `seats`        INT(11)          NOT NULL DEFAULT 4,
        `status`       ENUM(\'free\',\'occupied\',\'reserved\') NOT NULL DEFAULT \'free\',
        `floor`        VARCHAR(50)      DEFAULT NULL,
        `notes`        TEXT             DEFAULT NULL,
        `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_rtable_branch` (`branch_id`, `table_number`),
        INDEX `idx_rt_branch` (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_recipes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_recipes` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_id`    INT(11) UNSIGNED NOT NULL,
        `name`          VARCHAR(150)     NOT NULL,
        `yield_qty`     DECIMAL(10,4)    NOT NULL DEFAULT 1.0000 COMMENT \'Portions this recipe makes\',
        `area_id`       INT(11) UNSIGNED DEFAULT NULL COMMENT \'Default production area\',
        `prep_minutes`  INT(11)          NOT NULL DEFAULT 15,
        `notes`         TEXT             DEFAULT NULL,
        `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_updated`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_rr_product` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_recipe_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_recipe_items` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `recipe_id`     INT(11) UNSIGNED NOT NULL,
        `ingredient_id` INT(11) UNSIGNED NOT NULL COMMENT \'product_id from pos_products\',
        `quantity`      DECIMAL(10,4)    NOT NULL DEFAULT 1.0000,
        `unit_id`       INT(11) UNSIGNED DEFAULT NULL,
        `notes`         VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_rri_recipe`     (`recipe_id`),
        INDEX `idx_rri_ingredient` (`ingredient_id`),
        FOREIGN KEY (`recipe_id`) REFERENCES `' . db_prefix() . 'pos_restaurant_recipes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_kots')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_kots` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `kot_number`    VARCHAR(30)      NOT NULL UNIQUE,
        `sale_id`       INT(11) UNSIGNED DEFAULT NULL,
        `branch_id`     INT(11) UNSIGNED NOT NULL,
        `area_id`       INT(11) UNSIGNED NOT NULL,
        `table_id`      INT(11) UNSIGNED DEFAULT NULL,
        `table_number`  VARCHAR(20)      DEFAULT NULL,
        `waiter_id`     INT(11)          NOT NULL,
        `waiter_name`   VARCHAR(150)     DEFAULT NULL,
        `status`        ENUM(\'pending\',\'preparing\',\'ready\',\'served\',\'cancelled\') NOT NULL DEFAULT \'pending\',
        `notes`         TEXT             DEFAULT NULL,
        `started_at`    DATETIME         DEFAULT NULL,
        `ready_at`      DATETIME         DEFAULT NULL,
        `served_at`     DATETIME         DEFAULT NULL,
        `date_created`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_kot_branch`   (`branch_id`),
        INDEX `idx_kot_area`     (`area_id`),
        INDEX `idx_kot_status`   (`status`),
        INDEX `idx_kot_waiter`   (`waiter_id`),
        INDEX `idx_kot_table`    (`table_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_restaurant_kot_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_restaurant_kot_items` (
        `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `kot_id`        INT(11) UNSIGNED NOT NULL,
        `product_id`    INT(11) UNSIGNED NOT NULL,
        `product_name`  VARCHAR(255)     NOT NULL,
        `quantity`      DECIMAL(10,2)    NOT NULL DEFAULT 1.00,
        `notes`         VARCHAR(255)     DEFAULT NULL,
        `status`        ENUM(\'pending\',\'preparing\',\'ready\') NOT NULL DEFAULT \'pending\',
        PRIMARY KEY (`id`),
        INDEX `idx_koti_kot`     (`kot_id`),
        FOREIGN KEY (`kot_id`) REFERENCES `' . db_prefix() . 'pos_restaurant_kots`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

// ─── STOCKTAKE: extend existing tables + create new ones ─────────────────────
if ($CI->db->table_exists(db_prefix() . 'pos_inv_stock_takes')) {
    $stk_cols = [
        'scope'               => "ADD COLUMN `scope`               ENUM('full','category','product') NOT NULL DEFAULT 'full' AFTER `notes`",
        'scope_filter'        => "ADD COLUMN `scope_filter`        TEXT          DEFAULT NULL AFTER `scope`",
        'blind_counting'      => "ADD COLUMN `blind_counting`      TINYINT(1)    NOT NULL DEFAULT 0 AFTER `scope_filter`",
        'approved_by'         => "ADD COLUMN `approved_by`         INT(11) UNSIGNED DEFAULT NULL AFTER `blind_counting`",
        'snapshot_timestamp'  => "ADD COLUMN `snapshot_timestamp`  DATETIME      DEFAULT NULL AFTER `approved_by`",
        'freeze_active'       => "ADD COLUMN `freeze_active`       TINYINT(1)    NOT NULL DEFAULT 0 AFTER `snapshot_timestamp`",
        'variance_threshold'  => "ADD COLUMN `variance_threshold`  DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `freeze_active`",
        'items_counted'       => "ADD COLUMN `items_counted`       INT(11)       NOT NULL DEFAULT 0 AFTER `variance_threshold`",
        'total_items'         => "ADD COLUMN `total_items`         INT(11)       NOT NULL DEFAULT 0 AFTER `items_counted`",
    ];
    foreach ($stk_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_stock_takes')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_stock_takes` " . $def);
        }
    }
}

if ($CI->db->table_exists(db_prefix() . 'pos_inv_stock_take_items')) {
    $sti_cols = [
        'bin_location'     => "ADD COLUMN `bin_location`     VARCHAR(100)  DEFAULT NULL AFTER `notes`",
        'counted_by'       => "ADD COLUMN `counted_by`       INT(11) UNSIGNED DEFAULT NULL AFTER `bin_location`",
        'counted_at'       => "ADD COLUMN `counted_at`       DATETIME      DEFAULT NULL AFTER `counted_by`",
        'unit_cost'        => "ADD COLUMN `unit_cost`        DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `counted_at`",
        'reason_code'      => "ADD COLUMN `reason_code`      VARCHAR(50)   DEFAULT NULL AFTER `unit_cost`",
        'recount_requested'=> "ADD COLUMN `recount_requested` TINYINT(1)   NOT NULL DEFAULT 0 AFTER `reason_code`",
        'recount_count'    => "ADD COLUMN `recount_count`    INT(11)       NOT NULL DEFAULT 0 AFTER `recount_requested`",
        'is_found_stock'   => "ADD COLUMN `is_found_stock`   TINYINT(1)    NOT NULL DEFAULT 0 AFTER `recount_count`",
        'counted_qty'      => "ADD COLUMN `counted_qty`      DECIMAL(10,2) DEFAULT NULL AFTER `qty_system`",
    ];
    foreach ($sti_cols as $col => $def) {
        if (!$CI->db->field_exists($col, db_prefix() . 'pos_inv_stock_take_items')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "pos_inv_stock_take_items` " . $def);
        }
    }
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_stocktake_counts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_stocktake_counts` (
        `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `stock_take_id`  INT(11) UNSIGNED NOT NULL,
        `product_id`     INT(11) UNSIGNED NOT NULL,
        `bin_location`   VARCHAR(100)     DEFAULT NULL,
        `counted_qty`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `counter_user_id` INT(11) UNSIGNED NOT NULL,
        `device_id`      VARCHAR(100)     DEFAULT NULL,
        `recorded_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `synced_at`      DATETIME         DEFAULT NULL,
        `is_offline`     TINYINT(1)       NOT NULL DEFAULT 0,
        `notes`          VARCHAR(255)     DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_stc_session` (`stock_take_id`),
        INDEX `idx_stc_product` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}

if (!$CI->db->table_exists(db_prefix() . 'pos_inv_audit_ledger')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_audit_ledger` (
        `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ref_type`         VARCHAR(50)      NOT NULL,
        `ref_id`           INT(11) UNSIGNED NOT NULL,
        `branch_id`        INT(11) UNSIGNED NOT NULL,
        `product_id`       INT(11) UNSIGNED NOT NULL,
        `qty_before`       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `qty_after`        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `qty_variance`     DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `unit_cost`        DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `financial_impact` DECIMAL(15,4)    NOT NULL DEFAULT 0.0000,
        `reason_code`      VARCHAR(50)      DEFAULT NULL,
        `notes`            TEXT             DEFAULT NULL,
        `posted_by`        INT(11) UNSIGNED NOT NULL,
        `posted_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_al_ref`     (`ref_type`, `ref_id`),
        INDEX `idx_al_branch`  (`branch_id`),
        INDEX `idx_al_product` (`product_id`),
        INDEX `idx_al_posted`  (`posted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
}
