<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Bots Controller
 *
 * Handles the functionality related to opt out.
 */
class Optout extends AdminController
{
    /**
     * Constructor
     *
     * Loads necessary models.
     */
    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['whatsbot_model']);
    }

    /**
     * Index method
     *
     * Loads the main view for opt out.
     */
    public function index()
    {
        if (!staff_can('view', 'opt_out')) {
            access_denied();
        }
        $data['title'] = 'Opt out';
        $data['active_group'] = 'opt_out';
        $this->load->view('opt_out/manage', $data);
    }

    /**
     * Table method
     *
     * Loads the data for table.
     */
    public function table()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/opted_out'));
    }

    /**
     * Toggle optout method
     *
     * Loads the data for table.
     */
    public function toggle_optout()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $post_data = $this->input->post();
        $res = $this->whatsbot_model->update_optout_status($post_data);
        echo json_encode($res);
    }
}
