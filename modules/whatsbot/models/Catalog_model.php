<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Catalog_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get products with WhatsApp metadata
     *
     * @param string $id Optional product ID to retrieve specific product
     * @return array Products data
     */
    public function get_products($id = '', $where = []) {
        if (!empty($id)) {
            // Join with the items table to get product details
            $this->db->select(db_prefix() . 'items.*, ' . db_prefix() . 'wtc_product_metadata.*,'.db_prefix() . 'items.id as product_id');
            $this->db->join(db_prefix() . 'wtc_product_metadata', db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id', 'left');
            return $this->db->get_where(db_prefix() . 'items', [db_prefix() . 'items.id' => $id])->row_array();
        }

        // Get all products with WhatsApp metadata
        $this->db->select(db_prefix() . 'items.*, ' . db_prefix() . 'wtc_product_metadata.*');
        $this->db->join(db_prefix() . 'wtc_product_metadata', db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id', 'left');
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->get(db_prefix() . 'items')->result_array();
    }

    /**
     * Save product metadata for WhatsApp
     *
     * @param array $data Product metadata
     * @return array Response with status and message
     */
    public function save_product_metadata($data) {
        $product_id = $data['product_id'];
        
        // Check if metadata exists for this product
        $existing = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $product_id])->row();

        if (!$existing) {
            // Insert new metadata
            $this->db->insert(db_prefix() . 'wtc_product_metadata', $data);
        }

        // Handle image upload
        $uploaded_filename = wb_handle_product_image_upload($product_id);
        
        if ($uploaded_filename !== false) {
            $data['image_url'] = $uploaded_filename;
        } else if (isset($_FILES['product_image']) && !empty($_FILES['product_image']['name'])) {
            // Upload was attempted but failed
            return [
                'success' => false,
                'message' => _l('product_image_upload_failed')
            ];
        }

        if(isset($data['product_id']))
        {
           unset($data['product_id']); 
        }
        // Update existing metadata
        $this->db->where('product_id', $product_id);
        $update = $this->db->update(db_prefix() . 'wtc_product_metadata', $data);

        hooks()->do_action('after_metadata_updated', [
            'id'            => $product_id,
            'data'          => $data
        ]);

        return [
            'success' => $update,
            'message' => $update ? _l('product_metadata_updated') : _l('product_metadata_update_failed')
        ];
    }

    /**
     * Get product image URL
     *
     * @param int $product_id Product ID
     * @return string|null Image URL or null
     */
    public function get_product_image_url($product_id) {
        $metadata = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $product_id])->row();
        
        if ($metadata && $metadata->image_url) {
            return wb_get_product_image_url($product_id, $metadata->image_url);
        }
        
        return null;
    }

    /**
     * Log product sharing analytics
     *
     * @param array $data Sharing analytics data
     * @return bool Success status
     */
    public function log_product_sharing($data) {
        return $this->db->insert(db_prefix() . 'wtc_product_sharing_analytics', $data);
    }

    /**
     * Get product sharing analytics
     *
     * @param string $product_id Optional product ID to filter by
     * @return array Analytics data
     */
    public function get_product_sharing_analytics($product_id = '') {
        if (!empty($product_id)) {
            $this->db->where('product_id', $product_id);
        }
        
        $this->db->select(db_prefix() . 'wtc_product_sharing_analytics.*, ' . db_prefix() . 'items.description as product_name, ' . db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.id = ' . db_prefix() . 'wtc_product_sharing_analytics.product_id', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'wtc_product_sharing_analytics.staff_id', 'left');
        $this->db->order_by('shared_at', 'desc');
        
        return $this->db->get(db_prefix() . 'wtc_product_sharing_analytics')->result_array();
    }

    /**
     * Get products with WhatsApp catalog mapping
     * 
     * @param array $where Optional where clause
     * @return array Products with catalog mapping
     */
    public function get_products_with_catalog_mapping($where = []) {
        $this->db->select(db_prefix() . 'items.*, ' . db_prefix() . 'wtc_product_metadata.whatsapp_catalog_id');
        $this->db->join(db_prefix() . 'wtc_product_metadata', db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id', 'left');
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->get(db_prefix() . 'items')->result_array();
    }

    /**
     * Update or create product metadata with WhatsApp catalog ID
     * 
     * @param int $product_id Perfex product ID
     * @param string $catalog_id WhatsApp catalog ID
     * @return bool Success status
     */
    public function update_catalog_mapping($product_id, $catalog_id) {
        // Check if mapping exists
        $existing = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $product_id])->row();
        
        if ($existing) {
            // Update existing mapping
            return $this->db->update(db_prefix() . 'wtc_product_metadata', 
                ['whatsapp_catalog_id' => $catalog_id], 
                ['product_id' => $product_id]
            );
        } else {
            // Create new mapping
            return $this->db->insert(db_prefix() . 'wtc_product_metadata', [
                'product_id' => $product_id,
                'whatsapp_catalog_id' => $catalog_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get product by WhatsApp catalog ID
     * 
     * @param string $catalog_id WhatsApp catalog ID
     * @return array|null Product data
     */
    public function get_product_by_catalog_id($catalog_id) {
        $this->db->select(db_prefix() . 'items.*, ' . db_prefix() . 'wtc_product_metadata.*');
        $this->db->join(db_prefix() . 'wtc_product_metadata', 
            db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id');
        $this->db->where(db_prefix() . 'wtc_product_metadata.whatsapp_catalog_id', $catalog_id);
        
        $result = $this->db->get(db_prefix() . 'items')->row_array();
        
        return $result ?: null;
    }

    function get_metadata_items() {
        $result = $this->db
            ->where("whatsapp_catalog_id IS NOT NULL", null)
            ->get(db_prefix() . 'wtc_product_metadata')->result_array();
        return $result ?: [];
    }

    function get_products_states(){
        $this->db->select("
            COUNT(i.id) AS total_products,

            SUM(
                CASE
                    WHEN m.image_url IS NOT NULL
                        AND m.image_url != ''
                        AND m.whatsapp_catalog_id IS NULL
                    THEN 1 ELSE 0
                END
            ) AS ready_to_sync,

            SUM(
                CASE
                    WHEN m.whatsapp_catalog_id IS NOT NULL
                    THEN 1 ELSE 0
                END
            ) AS synced_products,

            SUM(
                CASE
                    WHEN m.id IS NULL
                        OR m.image_url IS NULL
                        OR m.image_url = ''
                    THEN 1 ELSE 0
                END
            ) AS not_ready
        ", false);

        $this->db->from('items i');
        $this->db->join('wtc_product_metadata m', 'm.product_id = i.id', 'left');

        $query = $this->db->get();
        return $query->row();
    }
}