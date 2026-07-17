<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController {

    protected $bn_lcb;

    public function __construct() {
        parent::__construct();
        $this->load->library('whatsbot/whatsbot_aeiou');
    }

    public function index() {
        show_404();
    }

    public function check_update() {
        $module = $this->app_modules->get(WHATSBOT_MODULE);
        $postData = $this->input->post();
        if (empty($postData)) {
            $this->whatsbot_aeiou->checkUpdate($module);
            return;
        }
        $this->whatsbot_aeiou->downloadUpdate($module, $postData);
    }

    public function check_license() {

        $postData = $this->input->post();
        $res =  modules\whatsbot\core\Apiinit::pre_validate($postData['module_name'], $postData['purchase_key'], $postData['username']);
        if ($res['status']) {
            $res['original_url'] = $this->input->post('original_url');
        }
        echo json_encode($res);
    }

}

