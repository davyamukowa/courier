<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_inventory extends AdminController
{
    public function index()
    {
        $this->load->model('shopify_connector/shopify_connector_model');
        $this->load->controller('shopify_connector/shopify_connector');

        $payload = [
            'id' => 998877, // shopify order ID
            'order_number' => '1099',
            'financial_status' => 'pending',
            'shipping_address' => ['name' => 'John Doe', 'phone' => '123456789'],
            'email' => 'john@example.com',
            'total_price' => '150.00',
            'line_items' => [
                [
                    'id' => 11223344,
                    'variant_id' => 889900,
                    'product_id' => 556677,
                    'name' => 'Test Item',
                    'title' => 'Test Item',
                    'variant_title' => 'Red',
                    'quantity' => 2,
                    'price' => '75.00'
                ]
            ]
        ];

        // Clean up
        $this->db->where('shopify_order_id', 998877)->delete(db_prefix() . 'shopify_orders');
        $this->db->where('shopify_order_id', 998877)->delete(db_prefix() . 'shopify_order_items');
        $this->db->where('shopify_order_id', 998877)->delete(db_prefix() . 'shopify_stock_reservations');
        $this->db->where('gs_sku', 'TEST-SKU-123')->delete(db_prefix() . 'shopify_product_mappings');
        $this->db->where('sku', 'TEST-SKU-123')->delete(db_prefix() . 'pos_products');
        
        $this->db->insert(db_prefix() . 'pos_products', [
            'name' => 'Test Item',
            'sku' => 'TEST-SKU-123',
            'item_code' => 'TEST-SKU-123',
            'type' => 'simple',
            'cost_price' => 50.00,
            'selling_price' => 75.00
        ]);
        $product_id = $this->db->insert_id();

        $this->db->where('product_id', $product_id)->delete(db_prefix() . 'pos_inventory');
        $this->db->insert(db_prefix() . 'pos_inventory', [
            'branch_id' => 1,
            'product_id' => $product_id,
            'quantity' => 10,
            'reserved_qty' => 0
        ]);

        $this->db->insert(db_prefix() . 'shopify_product_mappings', [
            'store_id' => 1,
            'shopify_product_id' => '556677',
            'shopify_variant_id' => '889900',
            'gs_sku' => 'TEST-SKU-123',
            'gs_inventory_item_id' => $product_id,
            'fulfillment_model' => 'A'
        ]);

        echo "Initial Setup Done.<br>";

        // The Shopify_connector expects these methods to be private/protected, but handle_order_create is private.
        // Wait, handle_order_create is private! 
        // I should just fire a fake webhook instead to test it properly!
        echo "Please test via CURL to the webhook endpoint instead.";
    }
}
