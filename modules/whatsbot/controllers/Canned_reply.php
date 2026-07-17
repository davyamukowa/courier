<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for canned reply functionality.
 */
class Canned_reply extends AdminController {
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('canned_reply_model');
    }

    public function index() {
        if (!staff_can('view', 'wtc_canned_reply') && !staff_can('view_own', 'wtc_canned_reply')) {
            access_denied();
        }
        $data['title'] = _l('canned_reply');
        $data['active_group'] = 'canned_reply';
        $this->load->view("canned_reply/manage", $data);
    }

    public function get_table_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, '/tables/canned_reply_table'));
    }

    public function save_reply() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $data['description'] = $this->input->post('description', true);
        $res = $this->canned_reply_model->add($data);
        echo json_encode($res);
    }

    public function delete()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $res = $this->canned_reply_model->delete_reply($data);
        echo json_encode($res);
    }


    public function change_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        $res =  $this->canned_reply_model->update_status($id, $status);
        echo json_encode($res);
    }

    public function get()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->canned_reply_model->get();
        echo json_encode(['reply_data' => $data]);
    }
}
