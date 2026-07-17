<?php
/**
 * c:\wamp64\www\perfex_crm\modules\shopify_connector\models\Shopify_connector_model.php
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Shopify_connector_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function encrypt($data)
    {
        if (empty($data)) return $data;
        $key = defined('APP_ENC_KEY') ? APP_ENC_KEY : 'fallback_key';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function decrypt($data)
    {
        if (empty($data)) return $data;
        $key = defined('APP_ENC_KEY') ? APP_ENC_KEY : 'fallback_key';
        $decoded = base64_decode($data);
        if (strpos($decoded, '::') !== false) {
            list($encrypted, $iv) = explode('::', $decoded, 2);
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
            return $decrypted !== false ? $decrypted : $data;
        }
        return $data;
    }

    public function get_store()
    {
        $this->db->where('is_active', 1);
        $store = $this->db->get(db_prefix() . 'shopify_stores')->row();
        if ($store) {
            $store->access_token = $this->decrypt($store->access_token);
            $store->api_key = $this->decrypt($store->api_key);
            $store->api_secret = $this->decrypt($store->api_secret);
            $store->webhook_secret = $this->decrypt($store->webhook_secret);
        }
        return $store;
    }

    public function save_store($data)
    {
        $store = $this->get_store();
        
        $db_data = [
            'shop_domain' => $data['shop_domain'],
            'api_version' => $data['api_version'],
            'default_fulfillment_model' => $data['default_fulfillment_model'],
            'is_active' => 1
        ];

        // Only update secrets if they are provided (not empty)
        if (!empty($data['access_token'])) {
            $db_data['access_token'] = $this->encrypt($data['access_token']);
        }
        if (!empty($data['api_key'])) {
            $db_data['api_key'] = $this->encrypt($data['api_key']);
        }
        if (!empty($data['api_secret'])) {
            $db_data['api_secret'] = $this->encrypt($data['api_secret']);
        }
        if (!empty($data['webhook_secret'])) {
            $db_data['webhook_secret'] = $this->encrypt($data['webhook_secret']);
        }

        if ($store) {
            $this->db->where('id', $store->id);
            $this->db->update(db_prefix() . 'shopify_stores', $db_data);
            return $store->id;
        } else {
            $this->db->insert(db_prefix() . 'shopify_stores', $db_data);
            return $this->db->insert_id();
        }
    }

    public function get_product_mappings($store_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('is_active', 1);
        return $this->db->get(db_prefix() . 'shopify_product_mappings')->result_array();
    }

    public function save_product_mapping($data)
    {
        $existing = $this->get_mapping_by_variant($data['store_id'], $data['shopify_variant_id']);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'shopify_product_mappings', $data);
            return $existing->id;
        } else {
            $this->db->insert(db_prefix() . 'shopify_product_mappings', $data);
            return $this->db->insert_id();
        }
    }

    public function get_mapping_by_variant($store_id, $variant_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('shopify_variant_id', $variant_id);
        return $this->db->get(db_prefix() . 'shopify_product_mappings')->row();
    }

    public function delete_product_mapping($id)
    {
        $this->db->where('id', $id);
        // Soft delete
        $this->db->update(db_prefix() . 'shopify_product_mappings', ['is_active' => 0]);
        return $this->db->affected_rows() > 0;
    }

    public function get_product_by_sku($gs_sku)
    {
        // Go Shipping items table
        $this->db->where('item_code', $gs_sku);
        return $this->db->get(db_prefix() . 'pos_products')->row();
    }

    public function get_inventory_levels($product_id)
    {
        // Fetch current stock from Go Shipping inventory table
        $this->db->where('product_id', $product_id);
        return $this->db->get(db_prefix() . 'pos_inventory')->row();
    }

    public function update_inventory_reservation($product_id, $qty_to_add)
    {
        $this->db->where('product_id', $product_id);
        $this->db->set('reserved_qty', "reserved_qty + ({$qty_to_add})", FALSE);
        $this->db->update(db_prefix() . 'pos_inventory');
        $err = $this->db->error();
        if ($err['code']) {
            log_activity("DB Error in update_inventory_reservation: " . $err['message']);
        }
        return $this->db->affected_rows() > 0;
    }

    public function commit_inventory_deduction($product_id, $qty_to_deduct, $unit_cost, $reference)
    {
        // Get current inventory to calculate before/after
        $this->db->where('product_id', $product_id);
        $inventory = $this->db->get(db_prefix() . 'pos_inventory')->row();
        if (!$inventory) return false;
        
        $qty_before = $inventory->quantity;
        $qty_after = $qty_before - abs($qty_to_deduct);

        // Deduct from on-hand and release the reserved qty
        $this->db->where('product_id', $product_id);
        $this->db->set('quantity', "quantity - (" . abs($qty_to_deduct) . ")", FALSE);
        $this->db->set('reserved_qty', "reserved_qty - (" . abs($qty_to_deduct) . ")", FALSE);
        $this->db->update(db_prefix() . 'pos_inventory');

        // Insert movement log
        $this->db->insert(db_prefix() . 'pos_inventory_movements', [
            'branch_id' => $inventory->branch_id,
            'product_id' => $product_id,
            'qty_before' => $qty_before,
            'qty_change' => -abs($qty_to_deduct),
            'qty_after' => $qty_after,
            'type' => 'sale',
            'reference_type' => 'shopify_order',
            'notes' => $reference,
            'unit_cost' => $unit_cost
        ]);
        return true;
    }

    public function create_courier_shipment_db($shipment_data, $sender_data, $recipient_data, $packages_data)
    {
        $this->db->trans_start();

        // 1. Insert Sender
        $this->db->insert(db_prefix() . '_shipment_senders', $sender_data);
        $sender_id = $this->db->insert_id();

        // 2. Insert Recipient
        $this->db->insert(db_prefix() . '_shipment_recipients', $recipient_data);
        $recipient_id = $this->db->insert_id();

        // 3. Insert Shipment
        $shipment_data['sender_id'] = $sender_id;
        $shipment_data['recipient_id'] = $recipient_id;
        $this->db->insert(db_prefix() . '_shipments', $shipment_data);
        $shipment_id = $this->db->insert_id();

        // 4. Insert Packages
        foreach ($packages_data as $package) {
            $package['shipment_id'] = $shipment_id;
            $this->db->insert(db_prefix() . '_shipment_packages', $package);
        }

        // 5. Insert Status History
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id' => $shipment_data['status_id'],
            'changed_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        return $shipment_id;
    }

    public function create_reservation_record($data)
    {
        $this->db->insert(db_prefix() . 'shopify_stock_reservations', $data);
        return $this->db->insert_id();
    }

    public function get_inventory_sync_report($store_id)
    {
        // Get the latest sync status per sku
        $sql = "
            SELECT s1.* 
            FROM " . db_prefix() . "shopify_inventory_sync s1
            JOIN (
                SELECT gs_sku, MAX(synced_at) as max_sync 
                FROM " . db_prefix() . "shopify_inventory_sync 
                WHERE store_id = ? 
                GROUP BY gs_sku
            ) s2 ON s1.gs_sku = s2.gs_sku AND s1.synced_at = s2.max_sync
            WHERE s1.store_id = ?
            ORDER BY s1.synced_at DESC
        ";
        return $this->db->query($sql, [$store_id, $store_id])->result_array();
    }

    public function get_active_reservations_for_order($shopify_db_order_id)
    {
        $this->db->where('shopify_order_id', $shopify_db_order_id);
        $this->db->where('status', 'active');
        return $this->db->get(db_prefix() . 'shopify_stock_reservations')->result();
    }

    public function update_reservation_status($reservation_id, $status)
    {
        $this->db->where('id', $reservation_id);
        $data = ['status' => $status];
        if ($status == 'released') {
            $data['released_at'] = date('Y-m-d H:i:s');
        }
        $this->db->update(db_prefix() . 'shopify_stock_reservations', $data);
        return $this->db->affected_rows() > 0;
    }
}
