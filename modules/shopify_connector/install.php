<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// TABLE 1: tblshopify_stores
if (!$CI->db->table_exists(db_prefix() . 'shopify_stores')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_stores` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shop_domain` VARCHAR(255) NOT NULL UNIQUE,
        `access_token` VARCHAR(255) NOT NULL,
        `api_key` VARCHAR(255) NOT NULL,
        `api_secret` VARCHAR(255) NOT NULL,
        `webhook_secret` VARCHAR(255),
        `api_version` VARCHAR(20) DEFAULT '2024-01',
        `is_active` TINYINT(1) DEFAULT 1,
        `default_fulfillment_model` ENUM('A','B','C') DEFAULT 'A',
        `warehouse_location_id` VARCHAR(50),
        `last_inventory_sync_at` DATETIME NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 2: tblshopify_orders
if (!$CI->db->table_exists(db_prefix() . 'shopify_orders')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT NOT NULL,
        `shopify_order_id` VARCHAR(50) NOT NULL UNIQUE,
        `shopify_order_number` VARCHAR(50),
        `gs_order_id` INT,
        `gs_shipment_id` INT,
        `fulfillment_model` ENUM('A','B','C') NOT NULL DEFAULT 'A',
        `order_status` VARCHAR(50) DEFAULT 'pending',
        `payment_status` VARCHAR(50) DEFAULT 'pending',
        `financial_status` VARCHAR(50),
        `customer_name` VARCHAR(255),
        `customer_email` VARCHAR(255),
        `customer_phone` VARCHAR(50),
        `delivery_address` TEXT,
        `line_items` TEXT,
        `total_price` DECIMAL(15,2),
        `currency` VARCHAR(10) DEFAULT 'KES',
        `tracking_number` VARCHAR(100),
        `shopify_fulfillment_id` VARCHAR(50),
        `notes` TEXT,
        `raw_payload` LONGTEXT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 3: tblshopify_order_items
if (!$CI->db->table_exists(db_prefix() . 'shopify_order_items')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_order_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_order_id` INT NOT NULL,
        `shopify_line_item_id` VARCHAR(50),
        `shopify_variant_id` VARCHAR(50),
        `shopify_product_id` VARCHAR(50),
        `gs_sku` VARCHAR(100),
        `gs_inventory_item_id` INT,
        `product_name` VARCHAR(500),
        `variant_title` VARCHAR(255),
        `quantity` INT NOT NULL,
        `unit_price` DECIMAL(15,2),
        `total_price` DECIMAL(15,2),
        `fulfillment_model` ENUM('A','B','C') DEFAULT 'A',
        `reservation_id` INT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 4: tblshopify_product_mappings
if (!$CI->db->table_exists(db_prefix() . 'shopify_product_mappings')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_product_mappings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT NOT NULL,
        `shopify_product_id` VARCHAR(50) NOT NULL,
        `shopify_variant_id` VARCHAR(50) NOT NULL,
        `gs_sku` VARCHAR(100) NOT NULL,
        `gs_inventory_item_id` INT,
        `shopify_inventory_item_id` VARCHAR(50),
        `fulfillment_model` ENUM('A','B','C') DEFAULT 'A',
        `supplier_id` INT,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (`store_id`, `shopify_variant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 5: tblshopify_webhooks
if (!$CI->db->table_exists(db_prefix() . 'shopify_webhooks')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_webhooks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT NOT NULL,
        `shopify_webhook_id` VARCHAR(50),
        `topic` VARCHAR(100) NOT NULL,
        `address` VARCHAR(500) NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 6: tblshopify_webhook_events
if (!$CI->db->table_exists(db_prefix() . 'shopify_webhook_events')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_webhook_events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT,
        `topic` VARCHAR(100) NOT NULL,
        `shopify_order_id` VARCHAR(50),
        `payload` LONGTEXT,
        `status` ENUM('pending','processing','done','failed','retrying') DEFAULT 'pending',
        `attempts` INT DEFAULT 0,
        `last_error` TEXT,
        `processed_at` DATETIME,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 7: tblshopify_fulfillment_updates
if (!$CI->db->table_exists(db_prefix() . 'shopify_fulfillment_updates')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_fulfillment_updates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_order_id` INT NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `tracking_number` VARCHAR(100),
        `tracking_url` VARCHAR(500),
        `shopify_response` TEXT,
        `pushed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `success` TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 8: tblshopify_inventory_sync
if (!$CI->db->table_exists(db_prefix() . 'shopify_inventory_sync')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_inventory_sync` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT NOT NULL,
        `shopify_inventory_item_id` VARCHAR(50),
        `shopify_location_id` VARCHAR(50),
        `gs_sku` VARCHAR(100) NOT NULL,
        `gs_qty_available` INT DEFAULT 0,
        `shopify_qty_before` INT,
        `shopify_qty_after` INT,
        `sync_type` ENUM('push','pull','reconcile') DEFAULT 'push',
        `synced_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `success` TINYINT(1) DEFAULT 0,
        `error_message` TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 9: tblshopify_accounting_entries
if (!$CI->db->table_exists(db_prefix() . 'shopify_accounting_entries')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_accounting_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_order_id` INT NOT NULL,
        `gs_journal_entry_id` INT,
        `entry_type` VARCHAR(50),
        `amount` DECIMAL(15,2),
        `currency` VARCHAR(10) DEFAULT 'KES',
        `posted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `success` TINYINT(1) DEFAULT 0,
        `error_message` TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 10: tblshopify_integration_logs
if (!$CI->db->table_exists(db_prefix() . 'shopify_integration_logs')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_integration_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT,
        `log_level` ENUM('info','warning','error','debug') DEFAULT 'info',
        `category` VARCHAR(100),
        `message` TEXT NOT NULL,
        `context` TEXT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (`store_id`, `created_at`),
        INDEX (`log_level`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// TABLE 11: tblshopify_stock_reservations
if (!$CI->db->table_exists(db_prefix() . 'shopify_stock_reservations')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "shopify_stock_reservations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shopify_order_id` INT NOT NULL,
        `shopify_order_item_id` INT NOT NULL,
        `gs_inventory_item_id` INT NOT NULL,
        `gs_sku` VARCHAR(100),
        `quantity_reserved` DECIMAL(12,4) NOT NULL,
        `status` ENUM('active','released','converted') DEFAULT 'active',
        `reserved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `released_at` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// ALTERATIONS
if ($CI->db->table_exists(db_prefix() . 'shopify_stores')) {
    if (!$CI->db->field_exists('last_inventory_sync_at', db_prefix() . 'shopify_stores')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "shopify_stores` ADD `last_inventory_sync_at` DATETIME NULL AFTER `warehouse_location_id`;");
    }
    if ($CI->db->field_exists('warehouse_location_id', db_prefix() . 'shopify_stores')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "shopify_stores` MODIFY `warehouse_location_id` VARCHAR(50);");
    }
}

if ($CI->db->table_exists(db_prefix() . 'shopify_product_mappings')) {
    if (!$CI->db->field_exists('shopify_inventory_item_id', db_prefix() . 'shopify_product_mappings')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "shopify_product_mappings` ADD `shopify_inventory_item_id` VARCHAR(50) NULL AFTER `gs_inventory_item_id`;");
    }
}
