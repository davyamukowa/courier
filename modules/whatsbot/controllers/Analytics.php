<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Analytics extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('whatsbot/analytics_model');
    }

    public function index()
    {
        if (!staff_can('view', 'wtc_analytics')) {
            access_denied();
        }

        $date_from = $this->input->get('date_from') ?: date('Y-m-d', strtotime('-30 days'));
        $date_to = $this->input->get('date_to') ?: date('Y-m-d');

        $data['title'] = _l('campaign_analytics');
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['campaigns'] = $this->analytics_model->get_all_campaigns_stats($date_from, $date_to);
        $data['overall'] = $this->analytics_model->get_overall_messaging_stats($date_from, $date_to);
        $data['template_perf'] = $this->analytics_model->get_template_performance($date_from, $date_to);
        $data['daily_volume'] = $this->analytics_model->get_daily_message_volume($date_from, $date_to);
        $data['active_group'] = 'campaign_stats';

        $this->load->view('analytics/campaign_analytics', $data);
    }

    public function get_campaign_analytics_data()
    {
        if (!$this->input->is_ajax_request()) return false;

        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        echo json_encode([
            'campaigns' => $this->analytics_model->get_all_campaigns_stats($date_from, $date_to),
            'overall' => $this->analytics_model->get_overall_messaging_stats($date_from, $date_to),
            'template_perf' => $this->analytics_model->get_template_performance($date_from, $date_to),
            'daily_volume' => $this->analytics_model->get_daily_message_volume($date_from, $date_to),
        ]);
    }
}
