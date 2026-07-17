<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Bulk_campaigns Controller
 *
 * Handles the functionality related to bulk campaign management.
 */
class Bulk_campaigns extends AdminController {
    use modules\whatsbot\traits\Whatsapp;
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['bulk_campaigns_model']);
    }

    public function index() {
        if (!staff_can('send', 'wtc_bulk_campaign')) {
            access_denied();
        }
        $viewData['title'] = _l('bulk_campaigns');
        $viewData['active_group'] = 'csv_campaigns';
        $viewData['templates'] = wb_get_whatsapp_template();

        if (!option_exists('wac_phone_numbers')) {
            $this->getPhoneNumbers();
        }
        $viewData['phone_numbers'] = json_decode(get_option('wac_phone_numbers'), true) ?? [];

        $this->load->view('bulk_campaigns', $viewData);
    }

    public function get_template_map() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $data['template'] = wb_get_whatsapp_template($this->input->post('template_id'));
        if (!empty($data['template'])) {
            $header_data = $data['template']['header_data_text'] ?? '';
            $body_data = $data['template']['body_data'] ?? '';
            $footer_data = $data['template']['footer_data'] ?? '';
            $button_data = !empty($data['template']['buttons_data']) ? json_decode($data['template']['buttons_data']) : [];
        }

        $view = $this->load->view('variables', $data, true);
        echo json_encode(['view' => $view, 'header_data' => $header_data ?? '', 'body_data' => $body_data ?? '', 'footer_data' => $footer_data ?? '', 'button_data' => $button_data]);
    }

    public function uploadCsvFile() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $res = handleCsvUpload();
        $response = $this->bulk_campaigns_model->prepare_merge_field($res);
        echo json_encode($response);
    }

    public function download_sample() {
        $this->load->helper('download');
        $path = APP_MODULES_PATH . 'whatsbot/assets/csv/campaigns_sample.csv';
        force_download($path, null);
    }

    public function send() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $post_data = $this->input->post();
        $res = $this->bulk_campaigns_model->send($post_data);
        echo json_encode($res);
    }
}
