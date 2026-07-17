<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for AI prompts functionality.
 */
class Ai_prompts extends AdminController {
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('ai_prompts_model');
    }

    public function index() {
        if (!staff_can('view', 'wtc_ai_prompts') && !staff_can('view_own', 'wtc_ai_prompts')) {
            access_denied();
        }
        $data['title'] = _l('ai_prompts');
        $data['active_group'] = 'ai_prompts';
        $this->load->view("ai_prompts/manage", $data);
    }

    public function get_table_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, '/tables/ai_prompts_table'));
    }

    public function save_prompt() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $data['action'] = $this->input->post('action', true);
        $res = $this->ai_prompts_model->add($data);
        echo json_encode($res);
    }

    public function delete() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $res = $this->ai_prompts_model->delete_reply($data);
        echo json_encode($res);
    }

    public function is_prompt_name_exists() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $res = $this->ai_prompts_model->prompt_name_exists($data);
        echo json_encode($res);
    }

    public function get() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->ai_prompts_model->get();
        echo json_encode(['custom_prompts' => $data]);
    }
}
