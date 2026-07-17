<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Test_sync extends CI_Controller {
    public function index() {
        try {
            $this->load->model('shopify_connector/shopify_connector_model');
            $this->load->controller('shopify_connector/shopify_connector');
            
            $reflection = new ReflectionClass(get_class($this->shopify_connector));
            $method = $reflection->getMethod('sync_all_inventory_to_shopify');
            $method->setAccessible(true);
            
            $res = $method->invokeArgs($this->shopify_connector, [99]);
            
            echo json_encode($res);
        } catch (\Throwable $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
}