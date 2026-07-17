<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'ware_commodity_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_commodity_type` (
      `commodity_type_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `commondity_code` varchar(100) NULL,
      `commondity_name` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`commodity_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'ware_unit_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_unit_type` (
      `unit_type_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `unit_code` varchar(100) NULL,
      `unit_name` text NULL,
      `unit_symbol` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`unit_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'ware_size_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_size_type` (
      `size_type_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `size_code` varchar(100) NULL,
      `size_name` text NULL,
      `size_symbol` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`size_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'ware_style_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_style_type` (
      `style_type_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `style_code` varchar(100) NULL,
      `style_barcode` text NULL,
      `style_name` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`style_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'ware_body_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_body_type` (
      `body_type_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `body_code` varchar(100) NULL,
      `body_name` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`body_type_id`)
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
if (!$CI->db->table_exists(db_prefix() . 'warehouse')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "warehouse` (
      `warehouse_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_code` varchar(100) NULL,
      `warehouse_name` text NULL,
      `warehouse_address` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('commodity_code' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `commodity_code` varchar(100) NOT NULL;
    ");
}
if (!$CI->db->field_exists('commodity_barcode' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `commodity_barcode` text NULL;
    ");
}
if (!$CI->db->field_exists('commodity_type' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `commodity_type` int(11) NULL;
    ");
}

if (!$CI->db->field_exists('warehouse_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `warehouse_id` int(11) NULL;
    ");
}
if (!$CI->db->field_exists('origin' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `origin` varchar(100) NULL;
    ");
}
if (!$CI->db->field_exists('color_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `color_id` int(11) NULL;
    ");
}
if (!$CI->db->field_exists('style_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `style_id` int(11) NULL;
    ");
}
if (!$CI->db->field_exists('model_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `model_id` int(11) NULL;
    ");
}
if (!$CI->db->field_exists('size_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
    ADD COLUMN `size_id` int(11) NULL;
    ");
}

if (!$CI->db->field_exists('unit_id' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `unit_id` int(11) NULL
  ;");
}

if (!$CI->db->table_exists(db_prefix() . 'goods_receipt')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_receipt` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `supplier_code` varchar(100) NULL,
      `supplier_name` text NULL,
      `deliver_name` text NULL,
      `buyer_id` int(11) NULL,
      `description` text NULL,
      `pr_order_id` int(11) NULL COMMENT 'code puchase request agree',
      `date_c` date NULL ,
      `date_add` date NULL,
      `goods_receipt_code` varchar(100) NULL,
      `total_tax_money` varchar(100) NULL,
      `total_goods_money` varchar(100) NULL,
      `value_of_inventory` varchar(100) NULL,
      `total_money` varchar(100) NULL COMMENT 'total_money = total_tax_money +total_goods_money ',

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('approval', 'goods_receipt')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt` 
ADD COLUMN `approval` INT(11) NULL DEFAULT 0 AFTER `total_money`;');            
}

if (!$CI->db->field_exists('addedfrom', 'goods_receipt')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt` 
ADD COLUMN `addedfrom` INT(11) NULL AFTER `total_money`;');            
}

if (!$CI->db->table_exists(db_prefix() . 'goods_receipt_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_receipt_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `goods_receipt_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` text NULL,
      `warehouse_id` text NULL,
      `unit_id` text NULL,
      `quantities` text NULL,
      `unit_price` varchar(100) NULL,
      `tax` varchar(100) NULL,
      `tax_money` varchar(100) NULL,
      `goods_money` varchar(100) NULL ,
      `note` text NULL ,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'goods_transaction_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_transaction_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `goods_receipt_id` int(11)  NULL COMMENT 'id_goods_receipt_id or goods_delivery_id',
      `goods_id` int(11) NOT NULL COMMENT ' is id commodity',
      `quantity` varchar(100) NULL,
      `date_add` DATETIME NULL,
      `commodity_id` int(11) NOT NULL,
      `warehouse_id` int(11) NOT NULL,
      `note`  text null,
      `status` int(2) NULL COMMENT '1:Goods receipt note 2:Goods delivery note',

      PRIMARY KEY (`id`,`goods_id`, `commodity_id`, `warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'inventory_manage')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "inventory_manage` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` int(11) NOT NULL ,
      `commodity_id` int(11) NOT NULL,
      `inventory_number` varchar(100) NULL,

      PRIMARY KEY (`id`, `commodity_id`, `warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'inventory_commodity_min')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "inventory_commodity_min` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `commodity_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` varchar(100) NULL,
      `inventory_number_min` varchar(100) NULL,

      PRIMARY KEY (`id`, `commodity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_approval_setting')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_approval_setting` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `related` VARCHAR(255) NOT NULL,
    `setting` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_approval_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_approval_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NOT NULL,
  `rel_type` VARCHAR(45) NOT NULL,
  `staffid` VARCHAR(45) NULL,
  `approve` VARCHAR(45) NULL,
  `note` TEXT NULL,
  `date` DATETIME NULL,
  `approve_action` VARCHAR(255) NULL,
  `reject_action` VARCHAR(255) NULL,
  `approve_value` VARCHAR(255) NULL,
  `reject_value` VARCHAR(255) NULL,
  `staff_approve` INT(11) NULL,
  `action` VARCHAR(45) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('sender', 'wh_approval_details')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'wh_approval_details` 
ADD COLUMN `sender` INT(11) NULL AFTER `action`,
ADD COLUMN `date_send` DATETIME NULL AFTER `sender`;');            
}

if (!$CI->db->table_exists(db_prefix() . 'wh_activity_log')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_activity_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NOT NULL,
  `rel_type` VARCHAR(45) NOT NULL,
  `staffid` INT(11) NULL,
  `date` DATETIME NULL,
  `note` TEXT NULL,
  PRIMARY KEY (`id`));');
}

//
if (!$CI->db->table_exists(db_prefix() . 'goods_delivery')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_delivery` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `rel_type` int(11) NULL COMMENT 'type goods delivery',
      `rel_document` int(11) NULL COMMENT 'document id of goods delivery',
      `customer_code` text NULL,
      `customer_name` varchar(100) NULL,
      `to_` varchar(100) NULL,
      `address` varchar(100) NULL,
      `description` text NULL COMMENT 'the reason delivery',
      `staff_id` int(11) NULL COMMENT 'salesman',
      `date_c` date NULL ,
      `date_add` date NULL,
      `goods_delivery_code` varchar(100) NULL COMMENT 'số chứng từ xuất kho',
      `approval` INT(11) NULL DEFAULT 0 COMMENT 'status approval ',
      `addedfrom` INT(11) ,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'goods_delivery_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_delivery_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `goods_delivery_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` text NULL,
      `warehouse_id` text NULL,
      `unit_id` text NULL,
      `quantities` text NULL,
      `unit_price` varchar(100) NULL,
      `note` text NULL ,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'stock_take')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "stock_take` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `description` text NULL COMMENT 'the reason stock take',
      `warehouse_id` int(11) NULL ,
      `date_stock_take` date NULL ,
      `stock_take_code` varchar(100) NULL COMMENT 'số kiểm kê kho',
      `date_add` date NULL,
      `hour_add` date NULL,
      `staff_id` varchar(100) NULL,
      `approval` INT(11) NULL DEFAULT 0 COMMENT 'status approval ',
      `addedfrom` INT(11) ,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'stock_take_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "stock_take_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `stock_take_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` text NULL,
      `unit_id` text NULL,
      `unit_price` varchar(100) NULL,
      `quantity_stock_take` varchar(100) NULL,
      `quantity_accounting_book` varchar(100) NULL,
      `quantity_change` varchar(100) NULL,
      `handling` text NULL ,
      `reason` text NULL ,
      `approval` INT(11) NULL DEFAULT 0 COMMENT 'status approval ',

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

/*add column to table tblitem*/
if (!$CI->db->field_exists('sku_code' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `sku_code` varchar(200)  NULL
  ;");
}
if (!$CI->db->field_exists('sku_name' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `sku_name` varchar(200)  NULL
  ;");
}
if (!$CI->db->field_exists('purchase_price' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `purchase_price` decimal(15,2)  NULL
  ;");
}
if (!$CI->db->field_exists('sub_group' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `sub_group` varchar(200)  NULL
  ;");
}
if (!$CI->db->table_exists(db_prefix() . 'wh_sub_group')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_sub_group` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `sub_group_code` varchar(100) NULL,
      `sub_group_name` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'ware_color')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ware_color` (
      `color_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `color_code` varchar(100) NULL,
      `color_name` varchar(100) NULL,
      `color_hex` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`color_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('commodity_name' ,db_prefix() . 'items')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `commodity_name` varchar(200) NOT NULL
  ;");
}
if (!$CI->db->field_exists('color' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
      ADD COLUMN `color` text NULL
  ;");
}
if (!$CI->db->field_exists('date_manufacture', 'inventory_manage')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'inventory_manage` 
    ADD COLUMN `date_manufacture` date NULL AFTER `inventory_number`,
    ADD COLUMN `expiry_date` date NULL AFTER `date_manufacture`;');            
}

if (!$CI->db->field_exists('warehouse_id', 'goods_receipt')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt` 
    ADD COLUMN `warehouse_id` int(11) NULL AFTER `goods_receipt_code`
    ;');            
}

if (!$CI->db->field_exists('date_manufacture', 'goods_receipt_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt_detail` 
    ADD COLUMN `date_manufacture` date NULL AFTER `goods_money`,
    ADD COLUMN `expiry_date` date NULL AFTER `date_manufacture`;');            
}


if (!$CI->db->table_exists(db_prefix() . 'wh_loss_adjustment')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_loss_adjustment` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,     
      `type` varchar(15) NULL,     
      `addfrom` int(11) NULL,    
      `reason` LONGTEXT NULL,   
      `time` datetime NULL,
      `date_create` date NOT NULL,
      `status` int NOT NULL,  
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('warehouses' ,db_prefix() . 'wh_loss_adjustment')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_loss_adjustment`
  ADD COLUMN `warehouses` int(11) NOT NULL AFTER `status`
  ;");
}

if (!$CI->db->table_exists(db_prefix() . 'wh_loss_adjustment_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_loss_adjustment_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `items` int(11) NULL, 
      `unit` int(11) NULL,
      `current_number` int(15) NULL,     
      `updates_number` int(15) NULL, 
      `loss_adjustment` INT(11) NULL,       
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('total_money', 'goods_delivery')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery` 
    ADD COLUMN `total_money` varchar(200) NULL AFTER `goods_delivery_code`
    ;');            
}

if (!$CI->db->field_exists('total_money', 'goods_delivery_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
    ADD COLUMN `total_money` varchar(200) NULL AFTER `unit_price`
    ;');            
}

if (!$CI->db->field_exists('warehouse_id', 'goods_delivery')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery` 
    ADD COLUMN `warehouse_id` int(11) NULL AFTER `goods_delivery_code`
    ;');            
}

if ($CI->db->field_exists('goods_id', 'goods_transaction_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_transaction_detail` 
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`, `commodity_id`);');            
}

if (!$CI->db->field_exists('old_quantity', 'goods_transaction_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_transaction_detail` 
    ADD COLUMN `old_quantity` varchar(100) NULL AFTER `goods_id`
    ;');            
}

//version v1.0.1
if (!$CI->db->field_exists('discount', 'goods_receipt_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt_detail` 
  ADD COLUMN `discount` varchar(100)
  ;');            
}

if (!$CI->db->field_exists('discount_money', 'goods_receipt_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt_detail` 
  ADD COLUMN `discount_money` varchar(100)
  ;');            
}

if (!$CI->db->field_exists('discount', 'goods_delivery_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
  ADD COLUMN `discount` varchar(100)
  ;');            
}

if (!$CI->db->field_exists('discount_money', 'goods_delivery_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
  ADD COLUMN `discount_money` varchar(100)
  ;');            
}
if (!$CI->db->field_exists('available_quantity', 'goods_delivery_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
  ADD COLUMN `available_quantity` varchar(100)
  ;');            
}

if (!$CI->db->field_exists('purchase_price', 'goods_transaction_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_transaction_detail` 
  ADD COLUMN `purchase_price` varchar(100)
  ;');            
}
if (!$CI->db->field_exists('price', 'goods_transaction_detail')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_transaction_detail` 
  ADD COLUMN `price` varchar(100)
  ;');            
}

if (!$CI->db->field_exists('total_discount', 'goods_delivery')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery` 
  ADD COLUMN `total_discount` varchar(100)
  ;');            
}
if (!$CI->db->field_exists('after_discount', 'goods_delivery')) {
  $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery` 
  ADD COLUMN `after_discount` varchar(100)
  ;');            
}

// Version 1.0.2
if (!$CI->db->field_exists('tax_id', 'goods_delivery_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
    ADD COLUMN `tax_id` varchar(100)
    ;');            
}
if (!$CI->db->field_exists('total_after_discount', 'goods_delivery_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
    ADD COLUMN `total_after_discount` varchar(100)
    ;');            
}

// Version 1.0.3
if (!$CI->db->field_exists('invoice_id', 'goods_delivery')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery` 
    ADD COLUMN `invoice_id` varchar(100)
    ;');            
}

if (!$CI->db->field_exists('lot_number', 'goods_receipt_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_receipt_detail` 
    ADD COLUMN `lot_number` varchar(100)
    ;');            
}

if (!$CI->db->field_exists('lot_number', 'inventory_manage')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'inventory_manage` 
    ADD COLUMN `lot_number` varchar(100)
    ;');            
}

// Version 1.0.4
if (!$CI->db->field_exists('expiry_date', 'goods_delivery_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
    ADD COLUMN `expiry_date` text  NULL ,
    ADD COLUMN `lot_number` text NULL
    ;');            
}

if (!$CI->db->field_exists('expiry_date', 'goods_transaction_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_transaction_detail` 
    ADD COLUMN `expiry_date` text NULL ,
    ADD COLUMN `lot_number` text NULL
    ;');            
}

// Version 1.0.5
if (!$CI->db->field_exists('guarantee', 'items')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'items` 
    ADD COLUMN `guarantee` text  NULL 
    
    ;');            
}

if (!$CI->db->field_exists('guarantee_period', 'goods_delivery_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'goods_delivery_detail` 
    ADD COLUMN `guarantee_period` text  NULL 
    
    ;');            
}

if (!$CI->db->field_exists('expiry_date', 'wh_loss_adjustment_detail')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'wh_loss_adjustment_detail` 
    ADD COLUMN `expiry_date` text NULL ,
    ADD COLUMN `lot_number` text NULL
    ;');            
}
//version 1.0.7
if (!$CI->db->field_exists('group_id' ,db_prefix() . 'wh_sub_group')) { 
    $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_sub_group`
        ADD COLUMN `group_id` int(11)  NULL
    ;");
  } 

//versio 1.0.8 
//update Set the selling price rule according to profit ratio
  
  add_option('warehouse_selling_price_rule_profif_ratio', 0, 1);


  if (!$CI->db->field_exists('profif_ratio' ,db_prefix() . 'items')) { 
      $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
          ADD COLUMN `profif_ratio` text  NULL
      ;");
  } 

  /*value 0 purchase price, 1 selling price*/
  
  add_option('profit_rate_by_purchase_price_sale', 0, 1);
  add_option('warehouse_the_fractional_part', 0, 1);
  add_option('warehouse_integer_part', 0, 1);

  //version 1.0.9
  //update auto create goods received note when create purchase order ( approval)
  //update auto create goods delivery note when create invoices ( paid)

  add_option('auto_create_goods_received', 0, 1);
  add_option('auto_create_goods_delivery', 0, 1);
  add_option('goods_receipt_warehouse', 0, 1);


  if ($CI->db->field_exists('warehouse_id' ,db_prefix() . 'goods_transaction_detail')) { 
      $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_transaction_detail`
          CHANGE COLUMN `warehouse_id` `warehouse_id` TEXT NOT NULL ,
          DROP PRIMARY KEY,
          ADD PRIMARY KEY (`id`, `commodity_id`)
      ;");
  }

  if (!$CI->db->field_exists('active' ,db_prefix() . 'items')) { 
    $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
        ADD COLUMN `active` INT(11) NULL DEFAULT 1
    ;");
  }

  add_option('barcode_with_sku_code', 0, 1);
    
  if (!$CI->db->field_exists('long_descriptions' ,db_prefix() . 'items')) { 
    $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
        ADD COLUMN `long_descriptions` LONGTEXT NULL
    ;");
  }

  add_option('revert_goods_receipt_goods_delivery', 0, 1);

    if (!$CI->db->field_exists('without_checking_warehouse' ,db_prefix() . 'items')) { 
      $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
          ADD COLUMN `without_checking_warehouse` int(11) NULL default 0
      ;");
    }  

    //version 1.1.2
  add_option('cancelled_invoice_reverse_inventory_delivery_voucher', 0, 1);
  add_option('uncancelled_invoice_create_inventory_delivery_voucher', 0, 1);
  add_option('inventory_auto_operations_hour', 0, 1);
  add_option('automatically_send_items_expired_before', 0, 1);
  add_option('inventorys_cronjob_active', 0, 1);
  add_option('inventory_cronjob_notification_recipients', '', 1);

  create_email_template('Inventory warning', 'Hi {staff_name}! <br /><br />This is a inventory warning<br />{<span 12pt="">notification_content</span>}. <br /><br />Regards.', 'inventory_warning', 'Inventory warning (Sent to staff)', 'inventory-warning-to-staff');

  if (get_status_modules_wh('purchase')) {
    if (!$CI->db->field_exists('wh_quantity_received' ,db_prefix() . 'pur_order_detail')) { 
      $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_order_detail`
          ADD COLUMN `wh_quantity_received` varchar(200)  NULL
      ;");
    }
  }

  //update inventory setting prefix

  add_option('inventory_received_number_prefix', 'NK', 1);
  add_option('next_inventory_received_mumber', 1, 1);
  add_option('inventory_delivery_number_prefix', 'XK', 1);
  add_option('next_inventory_delivery_mumber', 1, 1);

    //add internal delivery note, function
    if (!$CI->db->table_exists(db_prefix() . 'internal_delivery_note')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "internal_delivery_note` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

          `internal_delivery_name` text NULL ,
          `description` text NULL ,
          `staff_id` int(11) NULL ,
          `date_c` date NULL ,
          `date_add` date NULL,
          `internal_delivery_code` varchar(100) NULL ,
          `approval` INT(11) NULL DEFAULT 0 COMMENT 'status approval ',
          `addedfrom` INT(11) null,
          `total_amount` decimal(15,2) null ,
          `datecreated` datetime null ,

          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(db_prefix() . 'internal_delivery_note_detail')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "internal_delivery_note_detail` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `internal_delivery_id` int(11) NOT NULL,
          `commodity_code` varchar(100) NULL,
          `from_stock_name` text NULL,
          `to_stock_name` text NULL,
          `unit_id` text NULL,
          `available_quantity` text NULL,
          `quantities` text NULL,
          `unit_price` varchar(100) NULL,
          `into_money` varchar(100) NULL,
          `note` text NULL ,

          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    add_option('internal_delivery_number_prefix', 'ID', 1);
    add_option('next_internal_delivery_mumber', 1, 1);


    if (!$CI->db->field_exists('from_stock_name' ,db_prefix() . 'goods_transaction_detail')) { 
      $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_transaction_detail`
          ADD COLUMN `from_stock_name` int(11),
          ADD COLUMN `to_stock_name` int(11)
      ;");
    }

    add_option('item_sku_prefix', '', 1);

          //current version on eoffice 1.1.3
      //maximum stock
      if (!$CI->db->field_exists('inventory_number_max' ,db_prefix() . 'inventory_commodity_min')) { 
          $CI->db->query('ALTER TABLE `' . db_prefix() . "inventory_commodity_min`
              ADD COLUMN `inventory_number_max` varchar(100) NULL default 0
          ;");
        }

      //Goods receipt

      if (!$CI->db->field_exists('project' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `project` TEXT  NULL
        ;");
      }
      if (!$CI->db->field_exists('type' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `type` TEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('department' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `department` int(11)  NULL
        ;");
      }

      if (!$CI->db->field_exists('requester' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `requester` int(11)  NULL
        ;");
      }

      if (!$CI->db->field_exists('expiry_date' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `expiry_date` DATE NULL
        ;");
      }
      if (!$CI->db->field_exists('invoice_no' ,db_prefix() . 'goods_receipt')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
            ADD COLUMN `invoice_no` text NULL
        ;");
      }

      /*Required PO selected  when create goods received voucher*/

    add_option('goods_receipt_required_po', 0, 1);
    add_option('goods_delivery_required_po', 0, 1);

      //Goods delivery
      if (!$CI->db->field_exists('project' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `project` TEXT  NULL
        ;");
      }
      if (!$CI->db->field_exists('type' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `type` TEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('department' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `department` int(11)  NULL
        ;");
      }

      if (!$CI->db->field_exists('requester' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `requester` int(11)  NULL
        ;");
      }

      if (!$CI->db->field_exists('invoice_no' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `invoice_no` text NULL
        ;");
      }

      //goods delivery invoice
      if (!$CI->db->table_exists(db_prefix() . 'goods_delivery_invoices_pr_orders')) {
          $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_delivery_invoices_pr_orders` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `rel_id` int(11) NULL COMMENT  'goods_delivery_id',
            `rel_type` int(11) NULL COMMENT 'invoice_id or purchase order id',

            `type` varchar(100) NULL COMMENT'invoice,  purchase_orders',

            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }

      if (!$CI->db->field_exists('pr_order_id' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
            ADD COLUMN `pr_order_id` int(11) NULL
        ;");
      }

      //add vat column in lead
      if (!$CI->db->field_exists('vat' ,db_prefix() . 'leads')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "leads`
            ADD COLUMN `vat` varchar(50) NULL
        ;");
      }

      //table brand
      if (!$CI->db->table_exists(db_prefix() . 'wh_brand')) {
          $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_brand` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` text NULL ,

            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }

      //table model
      if (!$CI->db->table_exists(db_prefix() . 'wh_model')) {
          $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_model` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` text NULL ,
            `brand_id` int(11) NOT NULL,

            PRIMARY KEY (`id`,`brand_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }


      //table series
      if (!$CI->db->table_exists(db_prefix() . 'wh_series')) {
          $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_series` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` text NULL ,
            `model_id` int(11) NOT NULL,

            PRIMARY KEY (`id`,`model_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }

      if (!$CI->db->field_exists('series_id' ,db_prefix() . 'items')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `series_id` TEXT  NULL
        ;");
      }
      
      if (!$CI->db->field_exists('processing' ,db_prefix() . 'proposals')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "proposals`
            ADD COLUMN `processing` TEXT  NULL
        ;");
      }

      //warehouse custom fields
      if (!$CI->db->table_exists(db_prefix() . 'wh_custom_fields')) {
          $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_custom_fields` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `custom_fields_id` int NULL ,
            `warehouse_id` text NULL,

            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }
      
      //version_116
    add_option('goods_delivery_pdf_display', 0, 1);


      if (!$CI->db->field_exists('city' ,db_prefix() . 'warehouse')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "warehouse`
            ADD COLUMN `city` TEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('state' ,db_prefix() . 'warehouse')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "warehouse`
            ADD COLUMN `state` TEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('zip_code' ,db_prefix() . 'warehouse')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "warehouse`
            ADD COLUMN `zip_code` TEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('country' ,db_prefix() . 'warehouse')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "warehouse`
            ADD COLUMN `country` TEXT  NULL
        ;");
      }
      

      //new variation
      if (!$CI->db->field_exists('parent_id' ,db_prefix() . 'items')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `parent_id` int(11)  NULL  DEFAULT NULL
        ;");
      }

      if (!$CI->db->field_exists('attributes' ,db_prefix() . 'items')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `attributes` LONGTEXT  NULL
        ;");
      }

      if (!$CI->db->field_exists('parent_attributes' ,db_prefix() . 'items')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `parent_attributes` LONGTEXT  NULL
        ;");
      }
      
          //version_118x

    add_option('display_product_name_when_print_barcode', 0, 1);
    add_option('show_item_cf_on_pdf', 0, 1);


      //version_118x add activity log for Delivery note
      if (!$CI->db->table_exists(db_prefix() . 'wh_goods_delivery_activity_log')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_goods_delivery_activity_log` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `rel_id` int NULL ,
          `rel_type` varchar(100) NULL ,
          `description` mediumtext NULL,
          `additional_data` text NULL,
          `date` datetime NULL,
          `staffid` int(11) NULL,
          `full_name` varchar(100) NULL,

          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
      }
      //version_118x add create inventory delivery was partial from invoice, add column
      if (!$CI->db->field_exists('wh_delivered_quantity' ,db_prefix() . 'itemable')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "itemable`
          ADD COLUMN `wh_delivered_quantity` DECIMAL(15,2)  DEFAULT '0'
          ;");
      }

      if (!$CI->db->field_exists('type_of_delivery' ,db_prefix() . 'goods_delivery')) { 
        $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
          ADD COLUMN `type_of_delivery` VARCHAR(100)  NULL DEFAULT 'total'
          ;");
      }


// Version 122 update tax
if (!$CI->db->field_exists('tax_rate' ,db_prefix() . 'goods_receipt_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt_detail`
      ADD COLUMN `tax_rate` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('sub_total' ,db_prefix() . 'goods_receipt_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt_detail`
      ADD COLUMN `sub_total` DECIMAL(15,2) NULL DEFAULT '0'
  ;");
}

if (!$CI->db->field_exists('tax_name' ,db_prefix() . 'goods_receipt_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt_detail`
      ADD COLUMN `tax_name` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('commodity_name' ,db_prefix() . 'internal_delivery_note_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "internal_delivery_note_detail`
      ADD COLUMN `commodity_name` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('commodity_name' ,db_prefix() . 'wh_loss_adjustment_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_loss_adjustment_detail`
      ADD COLUMN `commodity_name` TEXT NULL
  ;");
}
if (!$CI->db->field_exists('tax_rate' ,db_prefix() . 'goods_delivery_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery_detail`
      ADD COLUMN `tax_rate` TEXT NULL,
      ADD COLUMN `tax_name` TEXT NULL,
      ADD COLUMN `sub_total` DECIMAL(15,2) NULL DEFAULT '0'
  ;");
}
if (!$CI->db->field_exists('additional_discount' ,db_prefix() . 'goods_delivery')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
      ADD COLUMN `additional_discount` DECIMAL(15,2) NULL DEFAULT '0'
  ;");
}

if (!$CI->db->field_exists('sub_total' ,db_prefix() . 'goods_delivery')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
      ADD COLUMN `sub_total` DECIMAL(15,2) NULL DEFAULT '0'
  ;");
}

// Version 123
add_option('goods_delivery_pdf_display_outstanding', 0, 1);
add_option('goods_delivery_pdf_display_warehouse_lotnumber_bottom_infor', 0, 1);

//Version 124
  add_option('packing_list_number_prefix', 'PL', 1);
  add_option('next_packing_list_number', 1, 1);

if (!$CI->db->table_exists(db_prefix() . 'wh_packing_lists')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_packing_lists` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `delivery_note_id` INT(11) NULL,
    `packing_list_number` VARCHAR(100) NULL,
    `packing_list_name` VARCHAR(200) NULL,
    `width` DECIMAL(15,2) NULL DEFAULT '0.00',
    `height` DECIMAL(15,2) NULL DEFAULT '0.00',
    `lenght` DECIMAL(15,2) NULL DEFAULT '0.00',
    `weight` DECIMAL(15,2) NULL DEFAULT '0.00',
    `volume` DECIMAL(15,2) NULL DEFAULT '0.00',
    `clientid` INT(11) NULL,
    `subtotal` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `billing_street` varchar(200) DEFAULT NULL,
    `billing_city` varchar(100) DEFAULT NULL,
    `billing_state` varchar(100) DEFAULT NULL,
    `billing_zip` varchar(100) DEFAULT NULL,
    `billing_country` int(11) DEFAULT NULL,
    `shipping_street` varchar(200) DEFAULT NULL,
    `shipping_city` varchar(100) DEFAULT NULL,
    `shipping_state` varchar(100) DEFAULT NULL,
    `shipping_zip` varchar(100) DEFAULT NULL,
    `shipping_country` int(11) DEFAULT NULL,
    `client_note` TEXT NULL,
    `admin_note` TEXT NULL,
    `approval` INT(11) NULL DEFAULT 0,
    `datecreated` DATETIME NULL,
    `staff_id` INT(11) NULL,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_packing_list_details')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_packing_list_details` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `packing_list_id` INT(11) NOT NULL,
    `delivery_detail_id` INT(11) NULL,
    `commodity_code` INT(11) NULL,
    `commodity_name` TEXT NULL,
    `quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
    `unit_id` INT(11) NULL,
    `unit_price` DECIMAL(15,2) NULL DEFAULT '0.00',
    `sub_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `tax_id`  TEXT NULL,
    `tax_rate`  TEXT NULL,
    `tax_name`  TEXT NULL,
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('packing_qty' ,db_prefix() . 'goods_delivery_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery_detail`
      ADD COLUMN `packing_qty` DECIMAL(15,2) NULL DEFAULT '0.00'
  ;");
}

if (!$CI->db->field_exists('type_of_packing_list' ,db_prefix() . 'wh_packing_lists')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_lists`
      ADD COLUMN `type_of_packing_list` VARCHAR(100)  NULL DEFAULT 'total'
  ;");
}

if (!$CI->db->field_exists('delivery_status' ,db_prefix() . 'wh_packing_lists')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_lists`
      ADD COLUMN `delivery_status` VARCHAR(100)  NULL DEFAULT 'wh_ready_to_deliver'
  ;");
}

if (!$CI->db->field_exists('delivery_status' ,db_prefix() . 'goods_delivery')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
      ADD COLUMN `delivery_status` VARCHAR(100)  NULL DEFAULT 'ready_for_packing'
  ;");
}

// purchase, => can_be_purchased
// inventory => can_be_inventory
// loyalty => can_be_sold
// omni_sale => can_be_sold
// sale_invoice => can_be_sold
// manufacturing order => can_be_manufacturing
// affiliate => can_be_sold

if (!$CI->db->field_exists('can_be_sold' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
  ADD COLUMN `can_be_sold` VARCHAR(100) NULL DEFAULT 'can_be_sold'
  ;");
}
if (!$CI->db->field_exists('can_be_purchased' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
  ADD COLUMN `can_be_purchased` VARCHAR(100) NULL DEFAULT 'can_be_purchased' 
  ;");
}
if (!$CI->db->field_exists('can_be_manufacturing' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
  ADD COLUMN `can_be_manufacturing` VARCHAR(100) NULL DEFAULT 'can_be_manufacturing' 
  ;");
}

if (!$CI->db->field_exists('can_be_inventory' ,db_prefix() . 'items')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "items`
  ADD COLUMN `can_be_inventory` VARCHAR(100) NULL DEFAULT 'can_be_inventory' 
  ;");
}

//add shipment on Omnisales module
if (!$CI->db->table_exists(db_prefix() . 'wh_omni_shipments')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_omni_shipments` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `cart_id` INT(11) NULL,
    `shipment_number` VARCHAR(100) NULL,
    `planned_shipping_date` DATETIME NULL,
    `shipment_status` VARCHAR(50) NULL,
    `datecreated` DATETIME NULL,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Order returns
// return request must be placed within X days after the delivery date
  add_option('wh_return_request_within_x_day', 30, 1);
  add_option('wh_fee_for_return_order', 0, 1);
  add_option('wh_return_policies_information', '', 1);
  add_option('wh_refund_loyaty_point', '1', 1);
  add_option('order_return_number_prefix', 'ReReturn', 1);
  add_option('next_order_return_number', 1, 1);
  add_option('e_order_return_number_prefix', 'DEReturn', 1);
  add_option('e_next_order_return_number', 1, 1);

if (!$CI->db->table_exists(db_prefix() . 'wh_order_returns')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_order_returns` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `rel_id` INT(11) NULL,
    `rel_type` VARCHAR(50) NOT NULL COMMENT'manual, sales_return_order, purchasing_return_order',
    `return_type` VARCHAR(50) NULL COMMENT'manual, partially, fully',
    `company_id` INT(11) NULL,
    `company_name` VARCHAR(500) NULL,
    `email` VARCHAR(100) NULL,
    `phonenumber` VARCHAR(20) NULL,
    `order_number` VARCHAR(500) NULL,
    `order_date` DATETIME NULL,
    `number_of_item` DECIMAL(15,2) NULL DEFAULT '0.00',
    `order_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `order_return_number` VARCHAR(200) NULL,
    `order_return_name` VARCHAR(500) NULL,
    `fee_return_order` DECIMAL(15,2) NULL DEFAULT '0.00',
    `refund_loyaty_point` INT(11) NULL DEFAULT '0',
    `subtotal` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `adjustment_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `return_policies_information` TEXT NULL,
    `admin_note` TEXT NULL,
    `approval` INT(11) NULL DEFAULT 0,
    `datecreated` DATETIME NULL,
    `staff_id` INT(11) NULL,
    `receipt_delivery_id` INT(1) NULL DEFAULT 0,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('order_return_name' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  ADD COLUMN `order_return_name` VARCHAR(500) NULL
  ;");
}

if (!$CI->db->field_exists('company_id' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  ADD COLUMN `company_id` INT(11) NULL 
  ;");
}

if (!$CI->db->table_exists(db_prefix() . 'wh_order_return_details')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_order_return_details` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_return_id` INT(11) NOT NULL,
    `rel_type_detail_id` INT(11) NULL,
    `commodity_code` INT(11) NULL,
    `commodity_name` TEXT NULL,
    `quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
    `unit_id` INT(11) NULL,
    `unit_price` DECIMAL(15,2) NULL DEFAULT '0.00',
    `sub_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `tax_id`  TEXT NULL,
    `tax_rate`  TEXT NULL,
    `tax_name`  TEXT NULL,
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `reason_return` VARCHAR(200) NULL,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('currency' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  ADD COLUMN `currency` INT(11) NULL 
  ;");
}

if (!$CI->db->field_exists('receipt_delivery_id' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  ADD COLUMN `receipt_delivery_id` INT(1) NULL  DEFAULT '0'
  ;");
}

if ($CI->db->field_exists('discount_total' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  CHANGE COLUMN `discount_total` `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00' 
   ;");
}
if ($CI->db->field_exists('additional_discount' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  CHANGE COLUMN `additional_discount` `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00' ;");
}
if ($CI->db->field_exists('adjustment_amount' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  CHANGE COLUMN `adjustment_amount` `adjustment_amount` DECIMAL(15,2) NULL DEFAULT '0.00' ;");
}
if ($CI->db->field_exists('total_after_discount' ,db_prefix() . 'wh_order_returns')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
  CHANGE COLUMN `total_after_discount` `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00' ;");
}


if ($CI->db->field_exists('discount' ,db_prefix() . 'wh_order_return_details')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_return_details`
  CHANGE COLUMN `discount` `discount` DECIMAL(15,2) NULL DEFAULT '0.00' 
   ;");
}
if ($CI->db->field_exists('discount_total' ,db_prefix() . 'wh_order_return_details')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_return_details`
  CHANGE COLUMN `discount_total` `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00' ;");
}
if ($CI->db->field_exists('total_after_discount' ,db_prefix() . 'wh_order_return_details')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_return_details`
  CHANGE COLUMN `total_after_discount` `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00' ;");
}

add_option('warehouse_receive_return_order ', 0, 1);
if (!$CI->db->field_exists('return_reason' ,db_prefix() . 'wh_order_returns')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_order_returns`
  ADD COLUMN `return_reason` longtext NULL
  ');
}

// inventory_receipt_voucher_returned_goods
// inventory_delivery_voucher_returned_purchasing_goods
if (!$CI->db->field_exists('receipt_delivery_type' ,db_prefix() . 'wh_order_returns')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_order_returns`
  ADD COLUMN `receipt_delivery_type` VARCHAR(100) NULL
  ');
}

if ($CI->db->field_exists('discount_total' ,db_prefix() . 'wh_packing_lists')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_lists`
  CHANGE COLUMN `discount_total` `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
  CHANGE COLUMN `additional_discount` `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
  CHANGE COLUMN `total_after_discount` `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00'
   ;");
}
if ($CI->db->field_exists('discount' ,db_prefix() . 'wh_packing_list_details')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_list_details`
  CHANGE COLUMN `discount` `discount` DECIMAL(15,2) NULL DEFAULT '0.00',
  CHANGE COLUMN `discount_total` `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
  CHANGE COLUMN `total_after_discount` `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00'
   ;");
}

//serial numbers
if (!$CI->db->table_exists(db_prefix() . 'wh_inventory_serial_numbers')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_inventory_serial_numbers` (

    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `commodity_id` INT(11) NOT NULL,
    `warehouse_id` INT(11) NULL,
    `inventory_manage_id` INT(11) NULL,
    `serial_number` VARCHAR(255) NULL,
    `is_used` VARCHAR(20) NULL DEFAULT 'no',

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'goods_receipt_detail')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'goods_receipt_detail`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}
if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'goods_delivery_detail')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'goods_delivery_detail`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}

if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'internal_delivery_note_detail')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'internal_delivery_note_detail`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}
if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'wh_loss_adjustment_detail')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_loss_adjustment_detail`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}
if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'wh_packing_list_details')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_packing_list_details`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}
if (!$CI->db->field_exists('serial_number' ,db_prefix() . 'goods_transaction_detail')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'goods_transaction_detail`
  ADD COLUMN `serial_number` LONGTEXT NULL
  ');
}

if (!$CI->db->field_exists('purchase_price' ,db_prefix() . 'inventory_manage')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'inventory_manage`
  ADD COLUMN `purchase_price` DECIMAL(15,2) NULL DEFAULT "0.00"
  ');
}

//Omni_sale add shipping fee on sales order => delivery note -  add shipping_fee
if (!$CI->db->field_exists('shipping_fee' ,db_prefix() . 'goods_delivery')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'goods_delivery`
  ADD COLUMN `shipping_fee` DECIMAL(15,2) NULL DEFAULT "0.00"
  ');
}
if (!$CI->db->field_exists('shipping_fee' ,db_prefix() . 'wh_packing_lists')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_packing_lists`
  ADD COLUMN `shipping_fee` DECIMAL(15,2) NULL DEFAULT "0.00"
  ');
}
if (!$CI->db->field_exists('goods_delivery_id' ,db_prefix() . 'wh_omni_shipments')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_omni_shipments`
  ADD COLUMN `goods_delivery_id` INT(11) NULL
  ');
}

if (!$CI->db->field_exists('shipment_hash' ,db_prefix() . 'wh_omni_shipments')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_omni_shipments`
  ADD COLUMN `shipment_hash` VARCHAR(32) NULL
  ');
}
add_option('wh_display_shipment_on_client_portal', 1, 1);
add_option('wh_on_total_items', 200, 1);
add_option('wh_products_by_serial', 1, 1);

/*for sales agent module*/
if (!$CI->db->field_exists('order_id' ,db_prefix() . 'wh_omni_shipments')){
    $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_omni_shipments`
  ADD COLUMN `order_id` INT(11) NULL DEFAULT '0'
  ;");
}


if (!$CI->db->field_exists('hide_warehouse_when_out_of_stock' ,db_prefix() . 'warehouse')){
    $CI->db->query('ALTER TABLE `' . db_prefix() . "warehouse`
  ADD COLUMN `hide_warehouse_when_out_of_stock` INT(11) NULL DEFAULT '0' COMMENT  ' 1: yes  0: no'
  ;");
}

  add_option('wh_shortened_form_pdf', 0, 1);
  add_option('wh_show_price_when_print_barcode', 1, 1);

if (!$CI->db->table_exists(db_prefix() . 'wh_staff_warehouses')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_staff_warehouses` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `staff_id` INT(11) NULL,
      `warehouse_id` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

add_option('notify_customer_when_change_delivery_status', 1, 1);
add_option('wh_hide_shipping_fee', 0, 1);

add_option('lot_number_prefix', 'LOT', 1);
add_option('next_lot_number', 1, 1);
add_option('auto_generate_lotnumber', 0, 1);
add_option('custom_name_for_meter', 'm', 1);
add_option('custom_name_for_kg', 'kg', 1);
add_option('custom_name_for_m3', 'm3', 1);

add_option('packing_list_pdf_display_rate', 1, 1);
add_option('packing_list_pdf_display_tax', 1, 1);
add_option('packing_list_pdf_display_subtotal', 1, 1);
add_option('packing_list_pdf_display_discount_percent', 1, 1);
add_option('packing_list_pdf_display_discount_amount', 1, 1);
add_option('packing_list_pdf_display_totalpayment', 1, 1);
add_option('packing_list_pdf_display_summary', 1, 1);

if (!$CI->db->field_exists('purchase_price' ,db_prefix() . 'wh_order_return_details')){
    $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_return_details`
      ADD COLUMN `purchase_price` decimal(15,2)  DEFAULT '0.00'
  ;");
}

// V1.4.0
add_option('wh_serial_number_as_mandatory', 0, 1);
add_option('next_serial_number', 1, 1);
add_option('serial_number_format', 1, 1);

// V1.4.1
if ($CI->db->field_exists('serial_number' ,db_prefix() . 'goods_receipt_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt_detail`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}

if ($CI->db->field_exists('serial_number' ,db_prefix() . 'goods_delivery_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery_detail`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}
if ($CI->db->field_exists('serial_number' ,db_prefix() . 'internal_delivery_note_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "internal_delivery_note_detail`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}

if ($CI->db->field_exists('serial_number' ,db_prefix() . 'wh_loss_adjustment_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_loss_adjustment_detail`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}
if ($CI->db->field_exists('serial_number' ,db_prefix() . 'wh_packing_list_details')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_list_details`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}

if ($CI->db->field_exists('serial_number' ,db_prefix() . 'goods_transaction_detail')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_transaction_detail`
  CHANGE COLUMN `serial_number` `serial_number` LONGTEXT NULL
   ;");
}
add_option('update_inventory_number', 0, 1);

// 1.4.3
if (!$CI->db->field_exists('expiry_date', 'wh_packing_list_details')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'wh_packing_list_details` 
    ADD COLUMN `expiry_date` text  NULL
    ;');            
}
add_option('packing_list_expiry_date', 0, 1);
add_option('packing_list_pdf_display_expiry_date', 0, 1);

// 1.4.4
if (!$CI->db->field_exists('currency' ,db_prefix() . 'goods_receipt')){
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_receipt`
    ADD COLUMN `currency` INT(11) NULL DEFAULT '0',
    ADD COLUMN `currency_exchange_rate` DECIMAL(15,6) NULL DEFAULT '0'
    ;");
}

add_option('goods_receipt_do_not_convert_to_base_currency', 0, 1);
if (!$CI->db->field_exists('currency_exchange_rate' ,db_prefix() . 'wh_order_returns')){
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_order_returns`
    ADD COLUMN `currency_exchange_rate` DECIMAL(15,6) NULL DEFAULT '1'
    ;");
}

// 1.4.5
// 1: only need 1 person approve
// 0: Need all approval

if (!$CI->db->field_exists('approval_type' ,db_prefix() . 'wh_approval_setting')){
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_approval_setting`
    ADD COLUMN `approval_type` INT(11) NULL DEFAULT '0' COMMENT '0: All  1: only one'
    ;");
}

// 1.4.6
// 27_06_2024
add_option('display_product_image_receipt_delivery_pdf', 0, 1);


// 1.4.7
// 29_07_2024
if (!$CI->db->table_exists(db_prefix() . 'currency_rates')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "currency_rates` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_currency_id` int(11) NULL,
    `from_currency_name` VARCHAR(100) NULL,
    `from_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `to_currency_id` int(11) NULL,
    `to_currency_name` VARCHAR(100) NULL,
    `to_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'currency_rate_logs')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "currency_rate_logs` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_currency_id` int(11) NULL,
    `from_currency_name` VARCHAR(100) NULL,
    `from_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `to_currency_id` int(11) NULL,
    `to_currency_name` VARCHAR(100) NULL,
    `to_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `date` DATE NULL,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

add_option('cr_date_cronjob_currency_rates', '');
add_option('cr_automatically_get_currency_rate', 1);
add_option('cr_global_amount_expiration', 0);

if (!$CI->db->field_exists('currency' ,db_prefix() . 'goods_delivery')){
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
    ADD COLUMN `currency` INT(11) NULL DEFAULT '0',
    ADD COLUMN `currency_exchange_rate` DECIMAL(15,6) NULL DEFAULT '0'
    ;");
}

if (!$CI->db->field_exists('currency' ,db_prefix() . 'wh_packing_lists')){
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wh_packing_lists`
    ADD COLUMN `currency` INT(11) NULL DEFAULT '0',
    ADD COLUMN `currency_exchange_rate` DECIMAL(15,6) NULL DEFAULT '0'
    ;");
}

// accouting mapping

if ($CI->db->table_exists(db_prefix() . 'wh_order_returns')) {
  if (!$CI->db->field_exists('acc_mapping' ,db_prefix() . 'wh_order_returns')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "wh_order_returns`
      ADD COLUMN `acc_mapping` tinyint(1) NOT NULL DEFAULT '0'
      ");
  }
}

if ($CI->db->table_exists(db_prefix() . 'wh_order_returns_refunds')) {
  if (!$CI->db->field_exists('acc_mapping' ,db_prefix() . 'wh_order_returns_refunds')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "wh_order_returns_refunds`
      ADD COLUMN `acc_mapping` tinyint(1) NOT NULL DEFAULT '0'
      ");
  }
}

if ($CI->db->table_exists(db_prefix() . 'goods_receipt')) {
  if (!$CI->db->field_exists('acc_mapping' ,db_prefix() . 'goods_receipt')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "goods_receipt`
      ADD COLUMN `acc_mapping` tinyint(1) NOT NULL DEFAULT '0'
      ");
  }
}

if ($CI->db->table_exists(db_prefix() . 'goods_delivery')) {
  if (!$CI->db->field_exists('acc_mapping' ,db_prefix() . 'goods_delivery')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "goods_delivery`
      ADD COLUMN `acc_mapping` tinyint(1) NOT NULL DEFAULT '0'
      ");
  }
}

if ($CI->db->table_exists(db_prefix() . 'wh_loss_adjustment')) {
  if (!$CI->db->field_exists('acc_mapping' ,db_prefix() . 'wh_loss_adjustment')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "wh_loss_adjustment`
      ADD COLUMN `acc_mapping` tinyint(1) NOT NULL DEFAULT '0'
      ");
  }
}
add_option('packing_list_pdf_display_only_item_name', 0, 1);

if ($CI->db->field_exists('sub_total' ,db_prefix() . 'goods_delivery')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "goods_delivery`
  CHANGE COLUMN `sub_total` `sub_total` DECIMAL(20,2) NULL DEFAULT '0.00' ;");
}

// ═══════════════════════════════════════════════════════════════════════════════
// KENYAN MARKET SEED DATA
// Inserted once on first install; idempotent — guarded by row-count / sentinel
// checks so re-running install never duplicates data.
// ═══════════════════════════════════════════════════════════════════════════════

// ─── 1. Commodity Types ───────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'ware_commodity_type') == 0) {
    $commodity_types = [
        ['CT001', 'Electronics & ICT',                  1],
        ['CT002', 'Food & Beverages',                   2],
        ['CT003', 'Clothing & Apparel',                 3],
        ['CT004', 'Building & Construction Materials',  4],
        ['CT005', 'Pharmaceuticals & Healthcare',       5],
        ['CT006', 'Household & Domestic Goods',         6],
        ['CT007', 'Agriculture & Farming',              7],
        ['CT008', 'Stationery & Office Supplies',       8],
        ['CT009', 'Automotive & Spare Parts',           9],
        ['CT010', 'Cosmetics & Personal Care',         10],
        ['CT011', 'Furniture & Fittings',              11],
        ['CT012', 'Machinery & Equipment',             12],
        ['CT013', 'Sports & Recreation',               13],
        ['CT014', 'Books, Media & Education',          14],
        ['CT015', 'Services',                          15],
        ['CT016', 'Telecommunications',                16],
        ['CT017', 'Cleaning & Sanitation Supplies',    17],
        ['CT018', 'Electrical & Lighting',             18],
        ['CT019', 'Toys & Baby Products',              19],
        ['CT020', 'Liquor & Tobacco',                  20],
    ];
    foreach ($commodity_types as [$code, $name, $order]) {
        $CI->db->insert(db_prefix() . 'ware_commodity_type', [
            'commondity_code' => $code,
            'commondity_name' => $name,
            'order'           => $order,
            'display'         => 1,
            'note'            => '',
        ]);
    }
}

// ─── 2. Unit Types ────────────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'ware_unit_type') == 0) {
    $unit_types = [
        // Weight
        ['UN001', 'Kilogram',       'kg',     1],
        ['UN002', 'Gram',           'g',      2],
        ['UN003', 'Milligram',      'mg',     3],
        ['UN004', 'Metric Tonne',   'MT',     4],
        ['UN005', 'Sack (90kg)',    'sack',   5],
        ['UN006', 'Bag (50kg)',     'bag50',  6],
        ['UN007', 'Bag (25kg)',     'bag25',  7],
        // Volume
        ['UN008', 'Litre',          'L',      8],
        ['UN009', 'Millilitre',     'mL',     9],
        ['UN010', 'Gallon',         'gal',   10],
        ['UN011', 'Jerrycan (20L)', 'jcan',  11],
        // Count / Each
        ['UN012', 'Piece',          'pcs',   12],
        ['UN013', 'Dozen (12)',     'doz',   13],
        ['UN014', 'Gross (144)',    'gros',  14],
        ['UN015', 'Pair',          'pr',    15],
        ['UN016', 'Set',           'set',   16],
        ['UN017', 'Bundle',        'bdl',   17],
        ['UN018', 'Box',           'box',   18],
        ['UN019', 'Carton',        'ctn',   19],
        ['UN020', 'Packet',        'pkt',   20],
        ['UN021', 'Sachet',        'scht',  21],
        ['UN022', 'Can / Tin',     'can',   22],
        ['UN023', 'Bottle',        'btl',   23],
        ['UN024', 'Tube',          'tube',  24],
        ['UN025', 'Roll',          'roll',  25],
        ['UN026', 'Ream (500 sheets)', 'ream', 26],
        ['UN027', 'Sheet',         'sht',   27],
        // Length / Area
        ['UN028', 'Metre',         'm',     28],
        ['UN029', 'Centimetre',    'cm',    29],
        ['UN030', 'Foot',          'ft',    30],
        ['UN031', 'Yard',          'yd',    31],
        ['UN032', 'Square Metre',  'm²',    32],
        ['UN033', 'Square Foot',   'ft²',   33],
        // Time / Service
        ['UN034', 'Hour',          'hr',    34],
        ['UN035', 'Day',           'day',   35],
        ['UN036', 'Week',          'wk',    36],
        ['UN037', 'Month',         'mo',    37],
        // Misc
        ['UN038', 'Plate / Tray',  'tray',  38],
        ['UN039', 'Bale',          'bale',  39],
        ['UN040', 'Drum (200L)',   'drum',  40],
    ];
    foreach ($unit_types as [$code, $name, $symbol, $order]) {
        $CI->db->insert(db_prefix() . 'ware_unit_type', [
            'unit_code'   => $code,
            'unit_name'   => $name,
            'unit_symbol' => $symbol,
            'order'       => $order,
            'display'     => 1,
            'note'        => '',
        ]);
    }
}

// ─── 3. Size Types ────────────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'ware_size_type') == 0) {
    $size_types = [
        // Clothing (International)
        ['SZ001', 'XS – Extra Small',   'XS',    1],
        ['SZ002', 'S – Small',          'S',     2],
        ['SZ003', 'M – Medium',         'M',     3],
        ['SZ004', 'L – Large',          'L',     4],
        ['SZ005', 'XL – Extra Large',   'XL',    5],
        ['SZ006', 'XXL – Double Extra', 'XXL',   6],
        ['SZ007', '3XL – Triple Extra', '3XL',   7],
        ['SZ008', '4XL – 4 Extra',      '4XL',   8],
        // Shoes (EU sizes)
        ['SZ009', 'EU 35',  'EU 35',  9],
        ['SZ010', 'EU 36',  'EU 36', 10],
        ['SZ011', 'EU 37',  'EU 37', 11],
        ['SZ012', 'EU 38',  'EU 38', 12],
        ['SZ013', 'EU 39',  'EU 39', 13],
        ['SZ014', 'EU 40',  'EU 40', 14],
        ['SZ015', 'EU 41',  'EU 41', 15],
        ['SZ016', 'EU 42',  'EU 42', 16],
        ['SZ017', 'EU 43',  'EU 43', 17],
        ['SZ018', 'EU 44',  'EU 44', 18],
        ['SZ019', 'EU 45',  'EU 45', 19],
        ['SZ020', 'EU 46',  'EU 46', 20],
        // Weight packs
        ['SZ021', '250g',   '250g',  21],
        ['SZ022', '500g',   '500g',  22],
        ['SZ023', '1 KG',   '1kg',   23],
        ['SZ024', '2 KG',   '2kg',   24],
        ['SZ025', '5 KG',   '5kg',   25],
        ['SZ026', '10 KG',  '10kg',  26],
        ['SZ027', '25 KG',  '25kg',  27],
        ['SZ028', '50 KG',  '50kg',  28],
        ['SZ029', '90 KG',  '90kg',  29],
        // Volume packs
        ['SZ030', '50 mL',  '50ml',  30],
        ['SZ031', '100 mL', '100ml', 31],
        ['SZ032', '250 mL', '250ml', 32],
        ['SZ033', '500 mL', '500ml', 33],
        ['SZ034', '1 Litre','1L',    34],
        ['SZ035', '2 Litre','2L',    35],
        ['SZ036', '5 Litre','5L',    36],
        ['SZ037', '10 Litre','10L',  37],
        ['SZ038', '20 Litre','20L',  38],
        // Mattress sizes (Kenyan standard)
        ['SZ039', 'Mattress 3×6 ft',  '3×6',   39],
        ['SZ040', 'Mattress 3.5×6 ft','3.5×6', 40],
        ['SZ041', 'Mattress 4×6 ft',  '4×6',   41],
        ['SZ042', 'Mattress 5×6 ft',  '5×6',   42],
        ['SZ043', 'Mattress 6×6 ft',  '6×6',   43],
        // TV / Screen sizes
        ['SZ044', '32 Inch', '32"',  44],
        ['SZ045', '43 Inch', '43"',  45],
        ['SZ046', '50 Inch', '50"',  46],
        ['SZ047', '55 Inch', '55"',  47],
        ['SZ048', '65 Inch', '65"',  48],
        ['SZ049', '75 Inch', '75"',  49],
    ];
    foreach ($size_types as [$code, $name, $symbol, $order]) {
        $CI->db->insert(db_prefix() . 'ware_size_type', [
            'size_code'   => $code,
            'size_name'   => $name,
            'size_symbol' => $symbol,
            'order'       => $order,
            'display'     => 1,
            'note'        => '',
        ]);
    }
}

// ─── 4. Style Types ───────────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'ware_style_type') == 0) {
    $style_types = [
        ['STY001', 'Casual Wear',            1],
        ['STY002', 'Formal / Business',      2],
        ['STY003', 'Business Casual',        3],
        ['STY004', 'Kitenge / African Print',4],
        ['STY005', 'Kanzu',                  5],
        ['STY006', 'Buibui / Hijab',         6],
        ['STY007', 'School Uniform',         7],
        ['STY008', 'Sportswear / Activewear',8],
        ['STY009', 'Ankara Print',           9],
        ['STY010', 'Slim Fit',              10],
        ['STY011', 'Regular / Classic Fit', 11],
        ['STY012', 'Baggy / Loose Fit',     12],
        ['STY013', 'Vintage / Retro',       13],
        ['STY014', 'Summer / Beach Wear',   14],
        ['STY015', 'Denim / Jeans Style',   15],
        ['STY016', 'Maasai / Shuka Pattern',16],
        ['STY017', 'Work / Safety Wear',    17],
        ['STY018', 'Maternity Wear',        18],
        ['STY019', 'Traditional Wear',      19],
        ['STY020', 'Streetwear / Urban',    20],
    ];
    foreach ($style_types as [$code, $name, $order]) {
        $CI->db->insert(db_prefix() . 'ware_style_type', [
            'style_code'    => $code,
            'style_barcode' => '',
            'style_name'    => $name,
            'order'         => $order,
            'display'       => 1,
            'note'          => '',
        ]);
    }
}

// ─── 5. Colors ────────────────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'ware_color') == 0) {
    $colors = [
        ['COL001', 'Black',           '#000000',  1],
        ['COL002', 'White',           '#FFFFFF',  2],
        ['COL003', 'Red',             '#EF4444',  3],
        ['COL004', 'Blue',            '#3B82F6',  4],
        ['COL005', 'Navy Blue',       '#1E3A5F',  5],
        ['COL006', 'Sky Blue',        '#7DD3FC',  6],
        ['COL007', 'Green',           '#22C55E',  7],
        ['COL008', 'Dark Green',      '#15803D',  8],
        ['COL009', 'Olive Green',     '#84894A',  9],
        ['COL010', 'Yellow',          '#EAB308', 10],
        ['COL011', 'Orange',          '#F97316', 11],
        ['COL012', 'Purple',          '#A855F7', 12],
        ['COL013', 'Maroon',          '#800000', 13],
        ['COL014', 'Pink',            '#EC4899', 14],
        ['COL015', 'Hot Pink',        '#FF1493', 15],
        ['COL016', 'Brown',           '#92400E', 16],
        ['COL017', 'Chocolate Brown', '#5C3317', 17],
        ['COL018', 'Grey',            '#9CA3AF', 18],
        ['COL019', 'Charcoal Grey',   '#374151', 19],
        ['COL020', 'Silver',          '#C0C0C0', 20],
        ['COL021', 'Gold',            '#D4AF37', 21],
        ['COL022', 'Rose Gold',       '#B76E79', 22],
        ['COL023', 'Cream / Off-White','#FFFDD0', 23],
        ['COL024', 'Beige',           '#F5F5DC', 24],
        ['COL025', 'Khaki / Tan',     '#C3A882', 25],
        ['COL026', 'Turquoise',       '#06B6D4', 26],
        ['COL027', 'Teal',            '#0D9488', 27],
        ['COL028', 'Coral',           '#FF6B6B', 28],
        ['COL029', 'Peach',           '#FFCBA4', 29],
        ['COL030', 'Lavender',        '#E6D5FA', 30],
        ['COL031', 'Mint Green',      '#98FF98', 31],
        ['COL032', 'Multi-color',     '#MULTICOLOR', 32],
    ];
    foreach ($colors as [$code, $name, $hex, $order]) {
        $CI->db->insert(db_prefix() . 'ware_color', [
            'color_code' => $code,
            'color_name' => $name,
            'color_hex'  => $hex,
            'order'      => $order,
            'display'    => 1,
            'note'       => '',
        ]);
    }
}

// ─── 6. Commodity Groups (tblitems_groups) ────────────────────────────────────
// Uses sentinel check on commodity_group_code so Perfex's own groups are untouched.
$_wh_has_groups = (int) $CI->db
    ->where('commodity_group_code', 'GRP001')
    ->count_all_results(db_prefix() . 'items_groups');

if (!$_wh_has_groups) {
    $commodity_groups = [
        ['GRP001', 'Phones & Tablets',            1],
        ['GRP002', 'Laptops & Computers',         2],
        ['GRP003', 'TVs & Home Entertainment',    3],
        ['GRP004', 'Electronic Accessories',      4],
        ['GRP005', 'Grains & Cereals',            5],
        ['GRP006', 'Dairy & Eggs',                6],
        ['GRP007', 'Meat & Poultry',              7],
        ['GRP008', 'Fruits & Vegetables',         8],
        ['GRP009', 'Snacks & Confectionery',      9],
        ['GRP010', 'Cooking Oil & Fats',         10],
        ['GRP011', 'Drinks & Beverages',         11],
        ['GRP012', 'Spices & Condiments',        12],
        ['GRP013', "Men's Clothing",             13],
        ['GRP014', "Women's Clothing",           14],
        ['GRP015', "Children's Clothing",        15],
        ['GRP016', 'Footwear',                   16],
        ['GRP017', 'Cement & Building Materials',17],
        ['GRP018', 'Hardware & Tools',           18],
        ['GRP019', 'Paints & Coatings',          19],
        ['GRP020', 'Medicines & Supplements',    20],
        ['GRP021', 'Medical Devices & Supplies', 21],
        ['GRP022', 'Cosmetics & Skincare',       22],
        ['GRP023', 'Haircare Products',          23],
        ['GRP024', 'Automotive Parts',           24],
        ['GRP025', 'Lubricants & Fluids',        25],
        ['GRP026', 'Tyres & Wheels',             26],
        ['GRP027', 'Kitchen Appliances',         27],
        ['GRP028', 'Household Appliances',       28],
        ['GRP029', 'Cleaning Supplies',          29],
        ['GRP030', 'Stationery & Office',        30],
    ];
    foreach ($commodity_groups as [$code, $name, $order]) {
        $CI->db->insert(db_prefix() . 'items_groups', [
            'name'                 => $name,
            'commodity_group_code' => $code,
            'order'                => $order,
            'display'              => 1,
            'note'                 => '',
        ]);
    }
}

// ─── 7. Sub-Groups (tblwh_sub_group) ─────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'wh_sub_group') == 0) {
    // Build group_code → id map from what we just inserted
    $_wh_group_map = [];
    $_wh_groups = $CI->db->select('id, commodity_group_code')
        ->where('commodity_group_code !=', '')
        ->where('commodity_group_code IS NOT NULL', null, false)
        ->get(db_prefix() . 'items_groups')->result();
    foreach ($_wh_groups as $_wg) {
        $_wh_group_map[$_wg->commodity_group_code] = (int) $_wg->id;
    }

    // [sub_group_code, sub_group_name, order, parent_group_code]
    $sub_groups = [
        // Phones & Tablets
        ['SGP001', 'Android Smartphones',       1, 'GRP001'],
        ['SGP002', 'iPhones & Apple Devices',   2, 'GRP001'],
        ['SGP003', 'Feature / Basic Phones',    3, 'GRP001'],
        ['SGP004', 'Tablets & iPads',           4, 'GRP001'],
        // Laptops & Computers
        ['SGP005', 'Windows Laptops',           1, 'GRP002'],
        ['SGP006', 'MacBooks',                  2, 'GRP002'],
        ['SGP007', 'Desktop Computers',         3, 'GRP002'],
        ['SGP008', 'Computer Components',       4, 'GRP002'],
        // TVs & Entertainment
        ['SGP009', 'Smart TVs',                 1, 'GRP003'],
        ['SGP010', 'LED / QLED / OLED TVs',     2, 'GRP003'],
        ['SGP011', 'Soundbars & Audio Systems', 3, 'GRP003'],
        ['SGP012', 'Streaming & Gaming',        4, 'GRP003'],
        // Electronic Accessories
        ['SGP013', 'Phone Cases & Protectors',  1, 'GRP004'],
        ['SGP014', 'Chargers & Cables',         2, 'GRP004'],
        ['SGP015', 'Earphones & Headphones',    3, 'GRP004'],
        ['SGP016', 'Power Banks & Batteries',   4, 'GRP004'],
        ['SGP017', 'Smart Watches & Bands',     5, 'GRP004'],
        // Grains & Cereals
        ['SGP018', 'Maize & Maize Flour',       1, 'GRP005'],
        ['SGP019', 'Rice',                      2, 'GRP005'],
        ['SGP020', 'Wheat Flour & Bread Flour', 3, 'GRP005'],
        ['SGP021', 'Ugali & Porridge Flour',    4, 'GRP005'],
        ['SGP022', 'Pulses & Legumes',          5, 'GRP005'],
        ['SGP023', 'Oats & Breakfast Cereal',   6, 'GRP005'],
        // Dairy & Eggs
        ['SGP024', 'Fresh Milk',                1, 'GRP006'],
        ['SGP025', 'Yoghurt & Fermented Milk',  2, 'GRP006'],
        ['SGP026', 'Cheese & Butter',           3, 'GRP006'],
        ['SGP027', 'Eggs',                      4, 'GRP006'],
        ['SGP028', 'Cream & Condensed Milk',    5, 'GRP006'],
        // Meat & Poultry
        ['SGP029', 'Beef & Goat Meat',          1, 'GRP007'],
        ['SGP030', 'Chicken & Poultry',         2, 'GRP007'],
        ['SGP031', 'Fish & Seafood',            3, 'GRP007'],
        ['SGP032', 'Sausages & Processed Meat', 4, 'GRP007'],
        ['SGP033', 'Mutton & Pork',             5, 'GRP007'],
        // Fruits & Vegetables
        ['SGP034', 'Fresh Fruits',              1, 'GRP008'],
        ['SGP035', 'Fresh Vegetables',          2, 'GRP008'],
        ['SGP036', 'Herbs & Spices (Fresh)',    3, 'GRP008'],
        ['SGP037', 'Dried Fruits & Nuts',       4, 'GRP008'],
        // Snacks & Confectionery
        ['SGP038', 'Biscuits & Cookies',        1, 'GRP009'],
        ['SGP039', 'Crisps & Chips',            2, 'GRP009'],
        ['SGP040', 'Sweets & Chocolate',        3, 'GRP009'],
        ['SGP041', 'Bread & Bakery Products',   4, 'GRP009'],
        ['SGP042', 'Cereals & Granola Bars',    5, 'GRP009'],
        // Cooking Oil & Fats
        ['SGP043', 'Sunflower / Vegetable Oil', 1, 'GRP010'],
        ['SGP044', 'Margarine & Shortening',    2, 'GRP010'],
        ['SGP045', 'Ghee & Butter',             3, 'GRP010'],
        ['SGP046', 'Coconut Oil',               4, 'GRP010'],
        // Drinks & Beverages
        ['SGP047', 'Soft Drinks & Sodas',       1, 'GRP011'],
        ['SGP048', 'Water & Mineral Water',     2, 'GRP011'],
        ['SGP049', 'Juices & Nectars',          3, 'GRP011'],
        ['SGP050', 'Energy & Sports Drinks',    4, 'GRP011'],
        ['SGP051', 'Tea & Coffee',              5, 'GRP011'],
        // Spices & Condiments
        ['SGP052', 'Dry Spices & Masalas',      1, 'GRP012'],
        ['SGP053', 'Sauces & Ketchup',          2, 'GRP012'],
        ['SGP054', 'Salt, Sugar & Sweeteners',  3, 'GRP012'],
        ['SGP055', 'Vinegar & Cooking Wine',    4, 'GRP012'],
        // Men's Clothing
        ['SGP056', 'Shirts & T-Shirts',         1, 'GRP013'],
        ['SGP057', 'Trousers & Jeans',          2, 'GRP013'],
        ['SGP058', 'Suits & Blazers',           3, 'GRP013'],
        ['SGP059', 'Kanzu & Traditional',       4, 'GRP013'],
        ['SGP060', 'Shorts & Casual Bottoms',   5, 'GRP013'],
        // Women's Clothing
        ['SGP061', 'Dresses & Skirts',          1, 'GRP014'],
        ['SGP062', 'Blouses & Tops',            2, 'GRP014'],
        ['SGP063', 'Buibui & Hijab',            3, 'GRP014'],
        ['SGP064', 'Kitenge & Ankara Print',    4, 'GRP014'],
        ['SGP065', 'Leggings & Trousers',       5, 'GRP014'],
        // Children's Clothing
        ['SGP066', 'School Uniforms',           1, 'GRP015'],
        ['SGP067', 'Kids Casual Wear',          2, 'GRP015'],
        ['SGP068', 'Baby & Toddler Clothing',   3, 'GRP015'],
        ['SGP069', 'Kids Sportswear',           4, 'GRP015'],
        // Footwear
        ['SGP070', "Men's Shoes & Boots",       1, 'GRP016'],
        ['SGP071', "Women's Shoes & Heels",     2, 'GRP016'],
        ['SGP072', "Children's Shoes",          3, 'GRP016'],
        ['SGP073', 'Sports & Athletic Shoes',   4, 'GRP016'],
        ['SGP074', 'Sandals & Slippers',        5, 'GRP016'],
        // Cement & Building
        ['SGP075', 'Cement & Concrete Products',1, 'GRP017'],
        ['SGP076', 'Steel Bars & Wire Mesh',    2, 'GRP017'],
        ['SGP077', 'Sand, Ballast & Hardcore',  3, 'GRP017'],
        ['SGP078', 'Tiles & Flooring',          4, 'GRP017'],
        ['SGP079', 'Roofing Sheets & Tiles',    5, 'GRP017'],
        ['SGP080', 'Timber & Wood',             6, 'GRP017'],
        // Hardware & Tools
        ['SGP081', 'Hand Tools',                1, 'GRP018'],
        ['SGP082', 'Power Tools & Drills',      2, 'GRP018'],
        ['SGP083', 'Plumbing Supplies & Pipes', 3, 'GRP018'],
        ['SGP084', 'Electrical Materials',      4, 'GRP018'],
        ['SGP085', 'Fasteners, Bolts & Nails',  5, 'GRP018'],
        // Paints & Coatings
        ['SGP086', 'Interior Paints',           1, 'GRP019'],
        ['SGP087', 'Exterior & Weathershield',  2, 'GRP019'],
        ['SGP088', 'Wood Stains & Varnish',     3, 'GRP019'],
        ['SGP089', 'Primers & Undercoats',      4, 'GRP019'],
        // Medicines
        ['SGP090', 'Prescription Medicines',    1, 'GRP020'],
        ['SGP091', 'OTC / Counter Medicines',   2, 'GRP020'],
        ['SGP092', 'Vitamins & Supplements',    3, 'GRP020'],
        ['SGP093', 'Herbal & Natural Remedies', 4, 'GRP020'],
        // Medical Devices
        ['SGP094', 'Diagnostic Equipment',      1, 'GRP021'],
        ['SGP095', 'First Aid & Wound Care',    2, 'GRP021'],
        ['SGP096', 'PPE & Protective Wear',     3, 'GRP021'],
        // Cosmetics & Skincare
        ['SGP097', 'Face Moisturisers & Creams',1, 'GRP022'],
        ['SGP098', 'Soaps & Body Wash',         2, 'GRP022'],
        ['SGP099', 'Sunscreen & Lotions',       3, 'GRP022'],
        ['SGP100', 'Fragrances & Deodorants',   4, 'GRP022'],
        // Haircare
        ['SGP101', 'Shampoo & Conditioner',     1, 'GRP023'],
        ['SGP102', 'Hair Oil & Serum',          2, 'GRP023'],
        ['SGP103', 'Hair Relaxers & Treatments',3, 'GRP023'],
        ['SGP104', 'Wigs & Hair Extensions',    4, 'GRP023'],
        ['SGP105', 'Braiding Hair',             5, 'GRP023'],
        // Automotive Parts
        ['SGP106', 'Engine & Mechanical Parts', 1, 'GRP024'],
        ['SGP107', 'Electrical & Ignition Parts',2,'GRP024'],
        ['SGP108', 'Brake & Suspension Parts',  3, 'GRP024'],
        ['SGP109', 'Body Parts & Accessories',  4, 'GRP024'],
        // Lubricants
        ['SGP110', 'Engine Oils',               1, 'GRP025'],
        ['SGP111', 'Gear & Transmission Oils',  2, 'GRP025'],
        ['SGP112', 'Coolants & Antifreeze',     3, 'GRP025'],
        ['SGP113', 'Greases & Hydraulic Fluids',4, 'GRP025'],
        // Tyres & Wheels
        ['SGP114', 'Car Tyres',                 1, 'GRP026'],
        ['SGP115', 'Truck & Bus Tyres',         2, 'GRP026'],
        ['SGP116', 'Motorcycle Tyres',          3, 'GRP026'],
        ['SGP117', 'Rims & Alloy Wheels',       4, 'GRP026'],
        // Kitchen Appliances
        ['SGP118', 'Blenders & Juicers',        1, 'GRP027'],
        ['SGP119', 'Cookers & Gas Stoves',      2, 'GRP027'],
        ['SGP120', 'Microwave & Ovens',         3, 'GRP027'],
        ['SGP121', 'Refrigerators & Freezers',  4, 'GRP027'],
        ['SGP122', 'Cookware & Kitchenware',    5, 'GRP027'],
        // Household Appliances
        ['SGP123', 'Washing Machines',          1, 'GRP028'],
        ['SGP124', 'Fans & Air Conditioners',   2, 'GRP028'],
        ['SGP125', 'Water Heaters & Dispensers',3, 'GRP028'],
        ['SGP126', 'Vacuum Cleaners & Irons',   4, 'GRP028'],
        // Cleaning Supplies
        ['SGP127', 'Detergents & Laundry',      1, 'GRP029'],
        ['SGP128', 'Disinfectants & Bleach',    2, 'GRP029'],
        ['SGP129', 'Mops, Brooms & Brushes',    3, 'GRP029'],
        ['SGP130', 'Bins & Waste Management',   4, 'GRP029'],
        // Stationery
        ['SGP131', 'Pens, Pencils & Markers',   1, 'GRP030'],
        ['SGP132', 'Exercise Books & Files',    2, 'GRP030'],
        ['SGP133', 'Printing Paper & Reams',    3, 'GRP030'],
        ['SGP134', 'Office Equipment & Ink',    4, 'GRP030'],
        ['SGP135', 'Art & Craft Supplies',      5, 'GRP030'],
    ];
    foreach ($sub_groups as [$code, $name, $order, $group_code]) {
        $CI->db->insert(db_prefix() . 'wh_sub_group', [
            'sub_group_code' => $code,
            'sub_group_name' => $name,
            'order'          => $order,
            'display'        => 1,
            'note'           => '',
            'group_id'       => $_wh_group_map[$group_code] ?? null,
        ]);
    }
    unset($_wh_group_map, $_wh_groups, $_wg);
}

// ─── 8. Brands & Models ───────────────────────────────────────────────────────
if ($CI->db->count_all(db_prefix() . 'wh_brand') == 0) {
    // Brand name → models array
    $brands_and_models = [
        // ── Phones & Tablets ──────────────────────────────────────────────────
        'Samsung'    => [
            'Galaxy S24 Ultra', 'Galaxy S24+', 'Galaxy S24',
            'Galaxy A55 5G', 'Galaxy A35', 'Galaxy A15', 'Galaxy A05s',
            'Galaxy Tab A9+', 'Galaxy Tab A9', 'Galaxy Tab S9 FE',
        ],
        'Apple'      => [
            'iPhone 15 Pro Max', 'iPhone 15 Pro', 'iPhone 15', 'iPhone 14', 'iPhone 13',
            'MacBook Air M2', 'MacBook Air M3', 'MacBook Pro 14"',
            'iPad Air 5th Gen', 'iPad 10th Gen', 'iPad mini 6',
        ],
        'Infinix'    => [
            'Hot 40 Pro', 'Hot 40i', 'Hot 30i',
            'Note 40 Pro+', 'Note 40', 'Note 30',
            'Zero 30 5G', 'Zero 5G', 'Smart 8 HD',
        ],
        'Tecno'      => [
            'Camon 30 Premier', 'Camon 30', 'Camon 20',
            'Spark 20 Pro+', 'Spark 20', 'Spark Go 2024',
            'Phantom X2 Pro', 'Phantom V Flip',
        ],
        'Itel'       => [
            'A70', 'A60s', 'A60', 'P40', 'S24', 'Vision 3 Plus',
        ],
        'Nokia'      => [
            'G42 5G', 'G21', 'C32', 'C22', '105 (4G)', '110 4G',
        ],
        'Xiaomi'     => [
            'Redmi Note 13 Pro+', 'Redmi Note 13 Pro', 'Redmi Note 13',
            'Redmi 13C', 'POCO X6 Pro', 'POCO M6 Pro',
            '14 Ultra', '13T Pro',
        ],
        'Oppo'       => [
            'Reno 11 F', 'Reno 10', 'A98', 'A78', 'A58', 'Find X7',
        ],
        'Vivo'       => [
            'V30 Pro', 'V29', 'Y200', 'Y100', 'T2x',
        ],
        'Huawei'     => [
            'P60 Pro', 'P50', 'Nova 12', 'Nova 11', 'Y9a', 'MatePad 11',
        ],
        // ── Laptops & Computers ───────────────────────────────────────────────
        'HP'         => [
            'Pavilion 15', 'Pavilion x360', 'EliteBook 840 G10', 'EliteBook 1040 G10',
            'ProBook 450 G10', 'ProBook 440 G10', 'Envy 13', 'Spectre x360 14',
            'HP 15s (Core i5)', 'HP 250 G10',
        ],
        'Dell'       => [
            'Inspiron 15 3000', 'Inspiron 15 5000', 'Inspiron 16 Plus',
            'Vostro 15 3510', 'Vostro 15 5620', 'Latitude 5540', 'Latitude 5440',
            'XPS 15 9530', 'XPS 13 Plus', 'Precision 5570',
        ],
        'Lenovo'     => [
            'IdeaPad 3 Gen 7', 'IdeaPad Flex 5', 'IdeaPad Slim 5',
            'ThinkPad E15 Gen 4', 'ThinkPad X1 Carbon', 'ThinkPad T14s',
            'Legion 5 Gen 7', 'Legion 5i Pro', 'Yoga 7i',
        ],
        'Acer'       => [
            'Aspire 5', 'Aspire 3', 'Swift 3', 'Nitro 5 (Gaming)',
            'Predator Helios 300', 'TravelMate P2',
        ],
        'Asus'       => [
            'VivoBook 15', 'VivoBook Pro 14', 'ZenBook 14',
            'ROG Strix G15', 'TUF Gaming F15', 'ExpertBook B1',
        ],
        // ── TVs & Home Entertainment ──────────────────────────────────────────
        'LG'         => [
            'OLED 65 C3', 'OLED 55 C3', 'QNED 65 80', 'UHD 55 UP7750',
            'UHD 43 UR7800', '32LQ630 HD', 'OLED Evo 77 G3',
        ],
        'Sony'       => [
            'Bravia 65 X95L', 'Bravia 55 X80L', 'Bravia 43 X75WL',
            'Bravia 32 W830K', 'Bravia OLED 65 A80L',
        ],
        'TCL'        => [
            '65C845 QLED 4K', '55C645 QLED', '50P635 4K',
            '43P615 4K', '32S5400 FHD', '65P745 4K',
        ],
        'Hisense'    => [
            '65U8K ULED 4K', '55U8K ULED', '55A7HAU 4K',
            '43A6K 4K', '32A4K HD', '75U7K QLED',
        ],
        'Vitron'     => [
            'V43FHD308S FHD', 'V32HD2 HD', 'V55UHD Smart',
        ],
        // ── Automotive ────────────────────────────────────────────────────────
        'Toyota'     => [
            'Land Cruiser 300', 'Land Cruiser Prado', 'Fortuner',
            'Hilux Double Cab', 'Hilux Single Cab', 'RAV4',
            'Corolla Cross', 'Corolla Sedan', 'Camry',
            'Probox', 'Succeed', 'Noah', 'Voxy', 'Hiace Matatu',
        ],
        'Nissan'     => [
            'Navara NP300', 'X-Trail T32', 'X-Trail T31',
            'Patrol Y62', 'Note', 'Tiida', 'Wingroad',
            'Caravan Matatu', 'Urvan NV350',
        ],
        'Honda'      => [
            'CR-V 5th Gen', 'HR-V 3rd Gen', 'Fit / Jazz',
            'Civic 11th Gen', 'Accord', 'Pilot', 'Freed',
        ],
        'Isuzu'      => [
            'D-Max Single Cab', 'D-Max Double Cab', 'D-Max Space Cab',
            'MU-X 4×4', 'NMR 85H Truck', 'NPS 75H Truck',
            'NPR Tipper', 'FRR Truck',
        ],
        'Mitsubishi' => [
            'Pajero Sport', 'Outlander PHEV', 'Eclipse Cross',
            'L200 Triton', 'ASX', 'Galant Fortis',
        ],
        'Subaru'     => [
            'Forester', 'Outback', 'XV Crosstrek', 'Impreza',
            'Legacy', 'BRZ',
        ],
        'Volkswagen' => [
            'Tiguan', 'Touareg', 'Polo', 'Golf', 'Amarok',
        ],
        'Ford'       => [
            'Ranger Wildtrak', 'Ranger XLT', 'Everest', 'Explorer',
        ],
        // ── Food / FMCG Brands ────────────────────────────────────────────────
        'Bidco Africa'       => [
            'Rina Cooking Oil 2L', 'Rina Cooking Oil 5L',
            'Elianto Cooking Oil 1L', 'Elianto Cooking Oil 2L',
            'Bidco Blueband Margarine 500g', 'Bidco Soya Chunks',
        ],
        'Unga Group'         => [
            'Jogoo Maize Flour 2kg', 'Jogoo Maize Flour 5kg',
            'Sasko Wheat Flour 2kg', 'Pembe Maize Flour 2kg',
            'Pembe Maize Flour 10kg',
        ],
        'Brookside Dairy'    => [
            'Brookside Fresh Milk 500ml', 'Brookside Fresh Milk 1L',
            'Brookside Lala Yoghurt 500g', 'Brookside Butter 250g',
            'Brookside Cream 250ml',
        ],
        'Daima'              => [
            'Daima Fresh Milk 500ml', 'Daima Fresh Milk 1L',
            'Daima Yoghurt 500g',
        ],
        'Dola Edible Oil'    => [
            'Dola Cooking Oil 1L', 'Dola Cooking Oil 2L', 'Dola Cooking Oil 5L',
        ],
        // ── Cosmetics / FMCG ──────────────────────────────────────────────────
        'Unilever Kenya'     => [
            'Omo Washing Powder 1kg', 'Omo Auto 1kg',
            'Sunlight Dish Soap 500ml', 'Sunlight Bar Soap',
            'Vaseline Body Lotion 200ml', 'Vaseline Body Lotion 400ml',
            'Dove Body Wash 250ml', 'Lux Beauty Soap',
            'Lifebuoy Hand Wash 250ml', 'Close-Up Toothpaste 100ml',
        ],
        'Nivea'              => [
            'Nivea Body Lotion 400ml', 'Nivea Soft 200ml',
            'Nivea Men After Shave 100ml', 'Nivea Sun SPF 50',
            'Nivea Deodorant Roll-On', 'Nivea Moisturising Cream 75ml',
        ],
        'Garnier'            => [
            'Garnier Micellar Water 400ml', 'Garnier Vitamin C Serum',
            'Garnier Fructis Shampoo 400ml', 'Garnier BB Cream',
            'Garnier Even & Matte',
        ],
        'OAN Africa'         => [
            'Dark & Lovely Relaxer', 'Dark & Lovely Colour',
            'TCB Naturals Shampoo', 'African Pride Braid Sheen',
        ],
        // ── Stationery ────────────────────────────────────────────────────────
        'Bic'                => [
            'Bic Cristal Ball Pen Blue', 'Bic Cristal Ball Pen Black',
            'Bic Cristal Ball Pen Red', 'Bic Mechanical Pencil',
        ],
        'Pilot'              => [
            'Pilot G2 Gel Pen', 'Pilot V7 Hi-Tecpoint', 'Pilot BPS-GP Pen',
        ],
        'Maped'              => [
            'Maped Ruler 30cm', 'Maped Geometry Set', 'Maped Sharpener',
        ],
    ];

    foreach ($brands_and_models as $brand_name => $models) {
        $CI->db->insert(db_prefix() . 'wh_brand', ['name' => $brand_name]);
        $brand_id = $CI->db->insert_id();
        foreach ($models as $model_name) {
            $CI->db->insert(db_prefix() . 'wh_model', [
                'name'     => $model_name,
                'brand_id' => $brand_id,
            ]);
        }
    }
    unset($brands_and_models, $brand_name, $models, $brand_id, $model_name);
}
// ─── End Kenyan Market Seed Data ─────────────────────────────────────────────
