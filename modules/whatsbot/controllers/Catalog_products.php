<?php
defined('BASEPATH') || exit('No direct script access allowed');

use WpOrg\Requests\Requests as WhatsappMarketingRequests;

class Catalog_products extends AdminController {
    use modules\whatsbot\traits\Whatsapp;

    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['catalog_model', 'invoice_items_model', 'catalog_sync_model']);
    }

    /**
     * Main view for catalog synchronization
     */
    public function index() {
        if (!staff_can('view', 'wtc_catalog_sync')) {
            access_denied();
        }
        
        $data['title'] = _l('catalog_synchronization');
        $data['sync_status'] = get_option('whatsbot_last_catalog_sync', 'Never');
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['states'] = $this->catalog_model->get_products_states();
        $this->load->view('catalog_products/manage', $data);
    }

    /**
     * Export Perfex CRM items to WhatsApp Catalog
     */
    public function export_to_whatsapp() {
        if (!$this->input->is_ajax_request() || !staff_can('edit', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        // Get items to export (can be filtered by POST data)
        $items = $this->get_items_for_export();
        
        // Track success and failures
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        foreach ($items as $item) {
            // Check if item already exists in WhatsApp catalog
            $catalog_id = $this->get_whatsapp_catalog_id($item['id']);
            
            // Prepare item data for WhatsApp API
            $product_data = $this->prepare_item_for_whatsapp($item);
            
            // Send to WhatsApp API
            $response = $this->create_or_update_catalog_product($product_data, $item['whatsapp_catalog_id']);

            if ($response['success']) {

                if(!empty($response['catalog_product_id'])){
                    // Store the WhatsApp catalog ID in our database
                    $this->catalog_model->save_product_metadata([
                        'whatsapp_catalog_id' => $response['catalog_product_id'],
                        'pending_sync' => 0,
                        'retailer_id' => $product_data['retailer_id'],
                        'product_id' => $item['product_id']
                    ]);
                }
                
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'item_name' => $item['description'],
                    'error' => $response['message']
                ];
            }
        }
        
        // Update last sync timestamp
        update_option('whatsbot_last_catalog_sync', date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => true,
            'message' => sprintf(
                _l('catalog_export_results'), 
                $results['success'], 
                $results['failed'],
                $results['skipped']
            ),
            'details' => $results
        ]);
    }

    public function delete_items($prod_id) {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->deleteCatalogProduct($prod_id);
        $this->db->where('whatsapp_catalog_id', $prod_id);
        $update = $this->db->delete(db_prefix() . 'wtc_product_metadata');
    }

    /**
     * Import WhatsApp Catalog products into Perfex CRM
     */
    public function import_from_whatsapp() {
        if (!$this->input->is_ajax_request() || !staff_can('edit', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        // Get POST data
        $default_group = $this->input->post('default_group');
        $skip_existing = $this->input->post('skip_existing');
        $products = $this->input->post('products');
        
        if (empty($products) || !is_array($products)) {
            echo json_encode([
                'success' => false,
                'message' => _l('no_products_selected')
            ]);
            return;
        }
        
        // Get catalog products from WhatsApp
        $catalog_products_response = $this->getCatalogProducts();
        
        if (!$catalog_products_response['success']) {
            echo json_encode([
                'success' => false,
                'message' => $catalog_products_response['message']
            ]);
            return;
        }

        // Filter to only selected products
        $catalog_products = array_filter($catalog_products_response['products'], function($product) use ($products) {
            return in_array($product['id'], $products);
        });
        
        // Track success and failures
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        foreach ($catalog_products as $product) {
            // Check if product already exists in Perfex
            $perfex_item = $this->find_perfex_item_by_catalog_id($product['id']);

            // Prepare WhatsApp product for Perfex
            $item_data = [
                'description' => $product['name'],
                'long_description' => $product['description'] ?? '',
                'rate' => preg_replace('/[^\d\.]+/', '', $product['price']),
                'group_id' => $default_group,
                'tax' => get_option('whatsbot_default_item_tax', 0),
                'tax2' => get_option('whatsbot_default_item_tax2', 0),
            ];
            
            if ($perfex_item) {
                // Update existing item
                $item_data['itemid'] = $perfex_item->id;
                $success = $this->invoice_items_model->edit($item_data);
            } else {
                // Create new item
                $item_id = $this->invoice_items_model->add($item_data);
                $success = $item_id ? true : false;
                
                if ($success) {
                    $filename = time() . '_' . uniqid() . '_' . basename(parse_url($product['image_url'], PHP_URL_PATH));
                    _maybe_create_upload_path(FCPATH . get_upload_path_by_type('product_images'));
                    _maybe_create_upload_path(FCPATH . get_upload_path_by_type('product_images') . $item_id);
                    $path = FCPATH . get_upload_path_by_type('product_images') . $item_id;
                    $img = $path . '/' . $filename;
                    
                    $context = stream_context_create([
                        'http' => [
                            'header' => "User-Agent: Mozilla/5.0\r\n"
                        ]
                    ]);
                    $image_data = @file_get_contents($product['image_url'], false, $context);
                    if ($image_data !== false) {
                        file_put_contents($img, $image_data);
                    }

                    // Store the WhatsApp catalog ID in our database
                    $this->catalog_model->save_product_metadata([
                        'product_id' => $item_id,
                        'pending_sync' => 0,
                        'retailer_id' => $product['retailer_id'],
                        'whatsapp_catalog_id' => $product['id'],
                        'product_url' => $product['url'] ?? '',
                        'image_url' => $filename ?? '',
                    ]);
                }
            }
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'catalog_id' => $product['id'],
                    'product_name' => $product['description'],
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
        
        // Log the sync operation
        $this->catalog_sync_model->log_sync_operation('whatsapp_to_perfex', $results, $status);
        
        // Update last sync timestamp
        update_option('whatsbot_last_catalog_sync', date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => ($results['success'] > 0),
            'message' => sprintf(
                _l('catalog_import_results'), 
                $results['success'], 
                $results['failed'],
                $results['skipped']
            ),
            'details' => $results
        ]);
    }

    /**
     * Schedule automatic synchronization
     */
    public function schedule_sync() {
        if (!$this->input->is_ajax_request() || !staff_can('edit', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        $sync_frequency = $this->input->post('sync_frequency');
        $sync_direction = $this->input->post('sync_direction');
        
        update_option('whatsbot_catalog_sync_frequency', $sync_frequency);
        update_option('whatsbot_catalog_sync_direction', $sync_direction);
        
        echo json_encode([
            'success' => true,
            'message' => _l('sync_schedule_updated')
        ]);
    }

    /**
     * Synchronization items from Perfex to WhatsApp
     */
    public function sync_perfex_to_whatsapp() {
        // Get products from WhatsApp catalog
        $catalog_products = $this->catalog_sync_model->getCatalogProducts();

        // Delete Meta details of product if it is deleted from META
        $meta_products = !empty($catalog_products['products']) ? array_column($catalog_products['products'], "id") : [];
        $sync_products = array_column($this->catalog_model->get_metadata_items(),  "whatsapp_catalog_id");
        $deleted_prod = array_diff($sync_products, $meta_products);

        if(!empty($deleted_prod)){
            $this->db->where_in('whatsapp_catalog_id', $deleted_prod);
            $this->db->delete(db_prefix() . 'wtc_product_metadata');
        }

        $res = $this->catalog_sync_model->run_automated_export();
        
        set_alert('success', sprintf(_l('perfex_to_whatsapp_sync_completed'), $res['success'], $res['failed'],$res['skipped']));
        redirect(admin_url('whatsbot/catalog_products'));
    }

    /**
     * Synchronization items from WhatsApp to Perfex
     */
    public function sync_whatsapp_to_perfex() {
        // Get products from WhatsApp catalog
        $catalog_products = $this->catalog_sync_model->getCatalogProducts();

        // Delete Meta details of product if it is deleted from META
        $meta_products = !empty($catalog_products['products']) ? array_column($catalog_products['products'], "id") : [];
        $sync_products = array_column($this->catalog_model->get_metadata_items(),  "whatsapp_catalog_id");
        $deleted_prod = array_diff($sync_products, $meta_products);

        if(!empty($deleted_prod)){
            $this->db->where_in('whatsapp_catalog_id', $deleted_prod);
            $this->db->delete(db_prefix() . 'wtc_product_metadata');
        }

        $res =$this->catalog_sync_model->run_automated_import();
        set_alert('success', sprintf(_l('whatsapp_to_perfex_sync_completed'), $res['success'], $res['failed'], $res['skipped']));
        redirect(admin_url('whatsbot/catalog_products'));
    }

    /**
     * Synchronization items bidirectionally
     */
    public function sync_bidirectional() {
        // Get products from WhatsApp catalog
        $catalog_products = $this->catalog_sync_model->getCatalogProducts();

        // Delete Meta details of product if it is deleted from META
        $meta_products = array_column($catalog_products['products'], "id");
        $sync_products = array_column($this->catalog_model->get_metadata_items(),  "whatsapp_catalog_id");
        $deleted_prod = array_diff($sync_products, $meta_products);

        if(!empty($deleted_prod)){
            $this->db->where_in('whatsapp_catalog_id', $deleted_prod);
            $this->db->delete(db_prefix() . 'wtc_product_metadata');
        }

        $res_export = $this->catalog_sync_model->run_automated_export();
        $res_import = $this->catalog_sync_model->run_automated_import();
        set_alert('success', sprintf(_l('sync_completed'), $res_export['success'] + $res_import['success'], $res_export['failed'] + $res_import['failed'], $res_export['skipped'] + $res_import['skipped']));
        redirect(admin_url('whatsbot/catalog_products'));
    }


    /**
     * Get items from Perfex CRM for export
     * 
     * @return array Items to export
     */
    private function get_items_for_export() {
        $where = [];
        
        if ($this->input->post('items')) {
            $where[db_prefix() . 'items.id IN (' . implode(',', $this->input->post('items')) . ')'] = null;
        }
        return $this->catalog_model->get_products('', $where);
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
        $metadata = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $item['product_id']])->row();
        
        // Get image URL
        $image_url = '';
        if ($metadata && $metadata->image_url) {
            $image_url = wb_get_product_image_url($item['product_id'], $metadata->image_url);
        }
        
        // Prepare data for WhatsApp API
        $product_data = [
            'name' => $item['description'],
            'description' => $item['long_description'] ?? $item['description'],
            'price' => (float)$item['rate'] * 100,
            'currency' => get_base_currency()->name ?? "USD",
            'retailer_id' => (string)$item['id']."-".date("YmdHis"), // WhatsApp requires string
            'image_url' => $image_url,
        ];
        
        return $product_data;
    }

    /**
     * Create or update a product in WhatsApp catalog
     * 
     * @param array $product_data Product data for WhatsApp
     * @param string|null $catalog_id Existing catalog ID (null for new products)
     * @return array Response with status and message
     */
    private function create_or_update_catalog_product($product_data, $catalog_id = null) {
        // Get WhatsApp business account ID
        $business_account_id = get_option('wac_business_account_id');
        $access_token = get_option('wac_access_token');
        
        if (!$business_account_id || !$access_token) {
            return [
                'success' => false,
                'message' => _l('whatsapp_credentials_missing')
            ];
        }

        return $catalog_id ? 
            $this->updateCatalogProduct($catalog_id, $product_data) : $this->createCatalogProduct($product_data);
    }

    /**
     * Get products from WhatsApp catalog
     * 
     * @return array WhatsApp catalog products
     */
    private function get_whatsapp_catalog_products() {
        // Get WhatsApp business account ID
        $business_account_id = get_option('wac_business_account_id');
        $access_token = get_option('wac_access_token');
        
        if (!$business_account_id || !$access_token) {
            return [
                'success' => false,
                'message' => _l('whatsapp_credentials_missing')
            ];
        }

        return $this->getCatalogProducts();
    }

    /**
     * Find Perfex item by WhatsApp catalog ID
     * 
     * @param string $catalog_id WhatsApp catalog ID
     * @return object|null Perfex item
     */
    private function find_perfex_item_by_catalog_id($catalog_id) {
        $this->db->select('product_id');
        $metadata = $this->db->get_where(db_prefix() . 'wtc_product_metadata', ['whatsapp_catalog_id' => $catalog_id])->row();
        
        if (!$metadata) {
            return null;
        }
        
        return $this->db->get_where(db_prefix() . 'items', ['id' => $metadata->product_id])->row();
    }

    /**
     * Prepare WhatsApp product data for Perfex item
     * 
     * @param array $product WhatsApp product data
     * @return array Prepared data for Perfex item
     */
    private function prepare_whatsapp_product_for_perfex($product) {
        
        // Prepare item data
        $item_data = [
            'description' => $product['name'],
            'long_description' => $product['description'] ?? '',
            'rate' => $product['price'],
            'group_id' => get_option('whatsbot_default_item_group', 0),
            'tax' => get_option('whatsbot_default_item_tax', 0),
            'tax2' => get_option('whatsbot_default_item_tax2', 0),
        ];
        
        return $item_data;
    }

    /**
     * Get items data for export
     */
    public function get_items() {
        if (!$this->input->is_ajax_request() || !staff_can('view', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        // Get all items with WhatsApp catalog mapping
        $items = $this->catalog_model->get_products_with_catalog_mapping();

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Get catalog products for import
     */
    public function get_catalog_products() {
        if (!$this->input->is_ajax_request() || !staff_can('view', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        // Get products from WhatsApp catalog
        $catalog_products = $this->get_whatsapp_catalog_products();
        $product_metadata = $this->db->get(db_prefix().'wtc_product_metadata')->result_array();
        if (!$catalog_products['success']) {
            echo json_encode([
                'success' => false,
                'message' => $catalog_products['message']
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'products' => $catalog_products['products'],
            'products_metadata' => $product_metadata
        ]);
    }

    /**
     * Get sync logs table data
     */
    public function get_sync_logs_table() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        
        $this->app->get_table_data(module_views_path('whatsbot', 'tables/sync_logs_table'));
    }

    /**
     * Get sync log details
     */
    public function get_sync_log_details() {
        if (!$this->input->is_ajax_request() || !staff_can('view', 'wtc_catalog_sync')) {
            ajax_access_denied();
        }
        
        $id = $this->input->get('id');
        
        if (empty($id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('invalid_sync_log_id')
            ]);
            return;
        }
        
        $log = $this->catalog_sync_model->get_sync_log_details($id);
        
        if (!$log) {
            echo json_encode([
                'success' => false,
                'message' => _l('sync_log_not_found')
            ]);
            return;
        }
        
        // Parse items processed and details
        $items_processed = json_decode($log['items_processed'], true);
        $details = json_decode($log['details'], true);
        
        // Format the response
        $response = [
            'success' => true,
            'details' => [
                'sync_time' => _dt($log['sync_time']),
                'direction' => _l($log['direction']),
                'status' => _l($log['status']),
                'items_processed' => sprintf(
                    _l('items_processed_count'),
                    $items_processed['success'],
                    $items_processed['failed'],
                    $items_processed['skipped']
                ),
                'errors' => $details['errors'] ?? []
            ]
        ];
        
        echo json_encode($response);
    }

    /**
     * Get orders details
     */
    public function manage_orders(){
        $data['title'] = _l('orders');
        $this->load->view('orders/manage', $data);
    }

    /**
     * Loading order table
     */
    public function get_order_table(){
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path('whatsbot', 'tables/orders_table'));
    }

    function products() {
        if (!staff_can('view', 'wtc_products')) {
            access_denied();
        }

        $data['title'] = _l('product_sharing');
        $this->load->view('catalog_products/manage_products', $data);
    }

    /**
     * Get table data for products
     */
    public function get_product_table_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/products_table'));
    }

    /**
     * Display product metadata form
     *
     * @param string $id Product ID
     */
    public function product($id = '') {
        if (!staff_can('edit', 'wtc_products')) {
            access_denied();
        }

        $data['title'] = _l('product_sharing');
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['catalog'] = $this->getBusinessCatalog(null);
        
        if (!empty($id)) {
            $data['product'] = $this->catalog_model->get_products($id);
        }

        $this->load->view('catalog_products/product', $data);
    }

    /**
     * Save product metadata
     */
    public function save() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $data = $this->input->post();
        
        // Validate required fields
        if (empty($data['product_id'])) {
            echo json_encode([
                'success' => false,
                'message' => _l('product_id_required')
            ]);
            return;
        }
        $data['pending_sync'] = 1;
        $result = $this->catalog_model->save_product_metadata($data);
        
        echo json_encode($result);
    }

    /**
     * Get product image for display
     */
    public function get_image($product_id) {
        if (!staff_can('view', 'wtc_products')) {
            show_404();
        }

        $image_url = $this->catalog_model->get_product_image_url($product_id);
        
        if ($image_url) {
            redirect($image_url);
        } else {
            // Return default placeholder image
            redirect(module_dir_url(WHATSBOT_MODULE, 'assets/img/product-placeholder.png'));
        }
    }

    /**
     * Get product data for modal editing
     */
    public function get_product_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $product_id = $this->input->post('product_id');

        if (empty($product_id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('product_id_required')
            ]);
            return;
        }

        // Get product details
        $product = $this->db->select([
            db_prefix() . 'items.id',
            db_prefix() . 'items.description',
            db_prefix() . 'items.rate',
            db_prefix() . 'wtc_product_metadata.image_url',
        ])
        ->from(db_prefix() . 'items')
        ->join(db_prefix() . 'wtc_product_metadata', db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id', 'left')
        ->where(db_prefix() . 'items.id', $product_id)
        ->get()
        ->row_array();

        if (!$product) {
            echo json_encode([
                'success' => false,
                'message' => _l('product_not_found')
            ]);
            return;
        }

        // Get catalog name
        $catalog = $this->getBusinessCatalog(null);
        $catalog_name = isset($catalog['name']) ? $catalog['name'] : '';

        // Format price
        $formatted_price = app_format_money($product['rate'], get_base_currency());

        // Build image URL
        $image_url = '';
        if (!empty($product['image_url'])) {
            $image_url = $product['image_url'];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $product['id'],
                'description' => $product['description'],
                'rate' => $product['rate'],
                'formatted_price' => $formatted_price,
                'image_url' => $image_url,
                'catalog_name' => $catalog_name
            ]
        ]);
    }

    /**
     * Toggle catalog sync (AJAX)
     */
    public function toggle_catalog_sync() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('edit', 'wtc_catalog_sync')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied')
            ]);
            return;
        }

        $enabled = $this->input->post('enabled');
        $enabled = $enabled ? '1' : '0';

        $updated = update_option('whatsbot_catalog_sync_enabled', $enabled);
        
        echo json_encode([
            'type' => $enabled ? 'success' : 'danger',
            'message' => $enabled ? _l('sync_enabled') : _l('sync_disabled')
        ]);
    }

    /**
     * Get products for product selector
     */
    public function get_products() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $products = $this->catalog_model->get_products();
        echo json_encode($products);
    }
}