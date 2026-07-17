<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
    public function index()
    {
        update_option('whatsbot_cron_has_run_from_cli', 1);
        $this->run();
    }

    public function manually()
    {
        $this->run();
        redirect(admin_url("settings?group=whatsbot_cron"));
    }

    public function run()
    {
        $last_cron_run = get_option('last_whatsbot_cron_run');
        $seconds = hooks()->apply_filters('cron_functions_execute_seconds', 60);

        if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
            $this->load->model('whatsbot_cron_model');
            if (get_option('enable_session_management') == '1') {
                $this->whatsbot_cron_model->run();
            }
        }
    }

    public function cron_status()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        update_option('wb_cron_status', $data['status'] ?? '');
        update_option('wb_cron_last_status_update', date('Y-m-d H:i:s'));
        $response = [
            'success'  => true,
            'message' => 'WhatsBot cron status working successfully!',
            'status' => get_option('wb_cron_status')
        ];

        echo json_encode($response);
    }
}
