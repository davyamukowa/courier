<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for WhatsApp integration functionalities.
 */
class Webhook_logs extends AdminController
{
     /**
     * Constructor for Webhook_logs controller.
     * Loads necessary models.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['webhook_model']);
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
    }

    public function index(){
        $data['title'] = _l('webhook_logs');
        $data['active_group'] = 'webhook_logs';
        $this->load->view('whatsbot/webhook_log/webhook_logs_list', $data);
    }

    public function get_webhook($id = ''){
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->webhook_model->get_webhook_log($id);
        echo json_encode($data);
    }

    public function webhook_logs_table(){
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/webhook_logs'));
    }

    public function view_log_details($id = '')
    {
        $data['title'] = _l('activity_log');
        $data['log_data'] = $this->webhook_model->getWebhookLogDetails($id);

        $this->load->view('webhook_log/webhook_log_details', $data);
    }

    public function delete_log($id)
    {
        if (staff_can('clear_log', 'wtc_log_activity')) {
            $delete = $this->webhook_model->delete_log($id);
            set_alert('danger', $delete ? _l('deleted', _l('log')) : _l('something_went_wrong'));
        }
        redirect(admin_url('whatsbot/webhook_logs'));
    }

    public function clear_log(){
        if (staff_can('clear_log', 'wtc_log_activity')) {
            $this->db->truncate(db_prefix() . 'wtc_webhook_logs');
            set_alert('danger', _l('log_cleared_successfully'));
        }
        redirect(admin_url('whatsbot/webhook_logs'));
    }
}
