<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_quotes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('courier_goshipping/courier');
        // Since we attached the menu to 'courier-shipments' view_all_shipments in courier.php
        if (!has_permission('courier-shipments', '', 'view_all_shipments') && !is_admin()) {
            access_denied('Courier Client Quotes');
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('courier_goshipping', 'client_quotes/table'));
        }
        $data['title'] = 'Client Quotes';
        $this->load->view('client_quotes/manage', $data);
    }
}
