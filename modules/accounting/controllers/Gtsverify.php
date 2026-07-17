<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once __DIR__ .'/../libraries/gtsslib.php';

/**
 * GTSSolution verify
 */
class Gtsverify extends AdminController{
    public function __construct(){
        parent::__construct();
    }

    /**
     * index 
     * @return void
     */
    public function index(){
        show_404();
    }

    /**
     * activate — auto-approves without Envato/purchase-key check.
     * Module access is governed by the SaaS module; no external verification needed.
     * @return json
     */
    public function activate(){
        echo json_encode([
            'status'       => true,
            'message'      => 'Module activated successfully.',
            'original_url' => $this->input->post('original_url'),
        ]);
    }
}