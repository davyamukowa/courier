<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Catalog_sync_model extends App_Model {
    use modules\whatsbot\traits\Whatsapp;
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['whatsbot/catalog_model', 'invoice_items_model']);
    }
    
    /**
     * Run automated export to WhatsApp catalog
     */
    public function run_automated_export() {
        // Get all items without filtering
        $items = $this->catalog_model->get_products('', [
            'wtc_product_metadata.pending_sync' => 1
        ]);
        
        // Track success and failures
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        foreach ($items as $item) {
            $item['itemid'] = $item['product_id']; // Ensure itemid is set for image handling
            // Updating catelog items
            if($item['whatsapp_catalog_id']) {
                // Item already has a WhatsApp catalog ID, skip it
                $response = $this->updateCatalogProduct($item['whatsapp_catalog_id'], $this->prepare_item_for_whatsapp($item));
                if ($response['success']) {
                    // Store the WhatsApp catalog ID in our database
                    $this->catalog_model->save_product_metadata([
                        'product_id' => $item['product_id'],
                        'pending_sync' => 0,
                    ]);
                
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'item_id' => $item['product_id'],
                        'item_name' => $item['description'],
                        'error' => $response['message']
                    ];
                }
                continue;
            }

            // Prepare item data for WhatsApp API
            $product_data = $this->prepare_item_for_whatsapp($item);
            
            // Send to WhatsApp API
            $response = $this->createCatalogProduct($product_data);
            
            if ($response['success']) {
                // Store the WhatsApp catalog ID in our database
                $this->catalog_model->save_product_metadata([
                    'product_id' => $item['product_id'],
                    'pending_sync' => 0,
                    'retailer_id' => $product_data['retailer_id'],
                    'whatsapp_catalog_id' => $response['catalog_product_id']
                ]);
                
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'item_id' => $item['product_id'],
                    'item_name' => $item['description'],
                    'error' => $response['message']
                ];
            }
        }
        
        // Log the sync results
        $this->log_sync_operation('perfex_to_whatsapp', $results);
        
        return $results;
    }
    
    /**
     * Run automated import from WhatsApp catalog
     */
    
    public function run_automated_import() {
        // Get catalog products from WhatsApp
        $catalog_products_response = $this->getCatalogProducts();
        
        if (!$catalog_products_response['success']) {
            // Log failed sync attempt
            $this->log_sync_operation('whatsapp_to_perfex', [
                'success' => 0,
                'failed' => 1,
                'skipped' => 0,
                'errors' => [
                    [
                        'error' => $catalog_products_response['message']
                    ]
                ]
            ], 'failed');
            
            return false;
        }
        
        $catalog_products = $catalog_products_response['products'];
        
        // Track success and failures
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        // Get default item group
        $default_group = get_option('whatsbot_default_item_group', 1);
        
        foreach ($catalog_products as $product) {
            // Check if product already exists in Perfex
            $perfex_item = $this->catalog_model->get_product_by_catalog_id($product['id']);
            
            if ($perfex_item) {
                // Skip existing items in automatic mode
                $results['skipped']++;
                continue;
            }
            
            // Prepare WhatsApp product for Perfex
            $item_data = [
                'description' => $product['name'],
                'long_description' => $product['description'] ?? '',
                'rate' => $product['price'],
                'group_id' => $default_group,
                'tax' => get_option('whatsbot_default_item_tax', 0),
                'tax2' => get_option('whatsbot_default_item_tax2', 0),
            ];
            
            // Create new item
            $item_id = $this->invoice_items_model->add($item_data);
            
            if ($item_id) {
                $filename = basename(parse_url($product['image_url'])['path']);
                $path = FCPATH . get_upload_path_by_type('product_images') . $item_id;
                _maybe_create_upload_path($path);
                $img = $path . '/' . $filename;
                
                file_put_contents($img, file_get_contents($product['image_url']));

                // Store the WhatsApp catalog ID in our database
                $this->catalog_model->save_product_metadata([
                    'product_id' => $item_id,
                    'whatsapp_catalog_id' => $product['id'],
                    'product_url' => $product['url'] ?? '',
                    'retailer_id' => $product['retailer_id'],
                    'pending_sync' => 0,
                    'image_url' => $filename ?? '',
                ]);
                
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'catalog_id' => $product['id'],
                    'product_name' => $product['name'],
                    'error' => _l('failed_to_save_item')
                ];
            }
        }
        
        // Determine overall status
        $status = 'success';
        if ($results['failed'] > 0 && $results['success'] == 0) {
            $status = 'failed';
        } else if ($results['failed'] > 0 && $results['success'] > 0) {
            $status = 'partial';
        }
        
        // Log the sync results
        $this->log_sync_operation('whatsapp_to_perfex', $results, $status);
        
        return $results;
    }
    
    /**
     * Get WhatsApp catalog ID for a Perfex item
     * 
     * @param int $item_id Perfex item ID
     * @return string|null WhatsApp catalog ID
     */
    private function get_whatsapp_catalog_id($item_id) {
        $metadata = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $item_id])->row();
        
        return $metadata ? $metadata->whatsapp_catalog_id : null;
    }
    
    /**
     * Prepare Perfex item data for WhatsApp catalog
     * 
     * @param array $item Perfex item data
     * @return array Prepared data for WhatsApp API
     */
    private function prepare_item_for_whatsapp($item) {
        // Get item metadata
        $metadata = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $item['itemid']])->row();
        
        // Get image URL
        $image_url = '';
        if ($metadata && $metadata->image_url) {
            $image_url = wb_get_product_image_url($item['itemid'], $metadata->image_url);
        }
        
        
        // Prepare data for WhatsApp API
        $product_data = [
            'name' => $item['description'],
            'description' => $item['long_description'] ?? $item['description'],
            'price' => (float)$item['rate'],
            'currency' => get_base_currency()->name ?? "USD",
            'retailer_id' => (string)$item['itemid'], // WhatsApp requires string
            'image_url' => $image_url
        ];
        
        return $product_data;
    }
    
    /**
     * Log a synchronization operation
     * 
     * @param string $direction Sync direction
     * @param array $results Operation results
     * @param string $status Operation status (success, partial, failed)
     * @return int Log ID
     */
    public function log_sync_operation($direction, $results, $status = null) {
        // Determine status if not provided
        if (!$status) {
            if ($results['failed'] > 0 && $results['success'] == 0) {
                $status = 'failed';
            } else if ($results['failed'] > 0 && $results['success'] > 0) {
                $status = 'partial';
            } else {
                $status = 'success';
            }
        }
        
        $log_data = [
            'sync_time' => date('Y-m-d H:i:s'),
            'direction' => $direction,
            'status' => $status,
            'items_processed' => json_encode([
                'success' => $results['success'],
                'failed' => $results['failed'],
                'skipped' => $results['skipped']
            ]),
            'details' => json_encode($results)
        ];
        
        $this->db->insert(db_prefix() . 'wtc_catalog_sync_logs', $log_data);
        
        return $this->db->insert_id();
    }
    
    /**
     * Get sync log details
     * 
     * @param int $log_id Log ID
     * @return array|null Log details
     */
    public function get_sync_log_details($log_id) {
        return $this->db->get_where(db_prefix() . 'wtc_catalog_sync_logs', ['id' => $log_id])->row_array();
    }
}