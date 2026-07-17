<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Test_migration extends CI_Controller {
    public function index() {
        $this->load->library('migration');
        // Let's just run the migration file directly to test it without CI's migration system which relies on version numbers
        require_once 'modules/shopify_connector/migrations/001_shopify_connector_tables.php';
        $m = new Migration_Shopify_connector_tables();
        $m->up();
        echo "Migration applied.\n";
    }
}