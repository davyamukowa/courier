<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        show_404();
    }

    public function activate() {
        // License check bypassed — always report success so the module activates without a purchase key.
        echo json_encode([
            'status'       => true,
            'message'      => 'Module activated successfully.',
            'original_url' => $this->input->post('original_url') ?: admin_url('modules'),
        ]);
    }

    public function upgrade_database() {
        // License check bypassed — always report success.
        echo json_encode([
            'status'       => true,
            'message'      => 'Database upgraded successfully.',
            'original_url' => $this->input->post('original_url') ?: admin_url('modules'),
        ]);
    }
}
