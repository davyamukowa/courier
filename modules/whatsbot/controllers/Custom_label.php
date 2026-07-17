<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_label extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model(['custom_label_model', 'interaction_model']);
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
    }

    public function index()
    {
        $data['title'] = _l('custom_label');
        $data['active_group'] = 'custom_label';
        $this->load->view('custom_label/manage', $data);
    }

    public function save()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $postData = $this->input->post();
        $response = $this->custom_label_model->save($postData);
        echo json_encode($response);
    }

    public function get_table_data()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/custom_label_table'));
    }

    public function delete($id = '')
    {
        $response = $this->custom_label_model->delete($id);
        set_alert($response['type'], $response['message']);
        redirect(admin_url('whatsbot/custom_label'));
    }

    public function label_exist()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $postData = $this->input->post();
        $response = $this->custom_label_model->labelExist($postData);

        echo json_encode($response);
    }

    public function get_data($id)
    {
        if (!$this->input->is_ajax_request() || empty($id)) {
            ajax_access_denied();
        }

        $data = $this->custom_label_model->getData($id);
        echo json_encode($data);
    }

    public function assign_label()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        
        $post_data = $this->input->post();
        $res = $this->interaction_model->add_assign_label($post_data);
        echo json_encode($res);
    }
}

/* End of file Aircall_mgmt.php */
