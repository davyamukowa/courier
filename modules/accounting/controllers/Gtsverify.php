<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Development activation stub.
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
     * activate
     * @return json
     */
    public function activate(){
        $res = array();
        $res['status'] = true;
        $res['message'] = 'Accounting module activated successfully.';
        $res['original_url']= $this->input->post('original_url') ?: admin_url('modules');
        echo json_encode($res);
    }    
}
