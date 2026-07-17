<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 001 — create all POS tables.
 * Delegates to install.php which contains the full schema.
 */
class Migration_Create_pos_tables extends CI_Migration
{
    public function up()
    {
        // install.php is loaded by Perfex during module activation
        // This migration class exists for tooling / CI compatibility
        include_once module_dir_path(POS_SYSTEM_MODULE_NAME) . 'install.php';
    }

    public function down()
    {
        $tables = [
            'pos_api_tokens',
            'pos_mobile_money_txns',
            'pos_payments',
            'pos_sale_items',
            'pos_sales',
            'pos_cash_movements',
            'pos_inventory_movements',
            'pos_inventory_batches',
            'pos_inventory',
            'pos_product_variation_options',
            'pos_product_variations',
            'pos_variation_values',
            'pos_variation_attributes',
            'pos_products',
            'pos_product_categories',
            'pos_discounts',
            'pos_customers',
            'pos_sessions',
            'pos_staff_branches',
            'pos_settings',
            'pos_payment_methods',
            'pos_tax_rates',
            'pos_branches',
        ];

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $this->dbforge->drop_table(db_prefix() . $table, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
