<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Marketing automation Controller
 *
 * Handles operations related to Marketing automation.
 */
class Marketing_automation extends AdminController {
    use modules\whatsbot\traits\Whatsapp; // Uses a trait for WhatsApp related methods

    /**
     * Constructor
     *
     * Initializes the controller and checks module activation status.
     */
    public function __construct() {
        parent::__construct();

        // Check if the whatsbot module is inactive; deny access if so
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';

        $this->load->model('whatsbot_model'); // Load the WhatsApp bot model
    }

    /**
     * Index method
     *
     * Loads the main view for WhatsApp flows management.
     */
    public function index() {
        if ($this->input->post()){
            $automation_data = $this->input->post('automation');
            $flow_id = $automation_data['flow'];
            foreach ($automation_data['hook'] as $hook) {
                if(empty($hook['action'])) {
                    continue;
                }
                $automation[$hook['action']][] = !empty($hook['project_status']) ? $hook['project_status'] : $hook['ticket_status'];
            }
            $update = $this->whatsbot_model->update_flow($flow_id, ["automation" => json_encode($automation ?? [])]);
            set_alert($update ? 'success' : 'danger', $update ? _l('updated_successfully', _l('automation')) : _l('something_went_wrong'));
        }
        // Check if user has permission to view WhatsApp flows
        if (!staff_can('view', 'wtc_template')) {
            access_denied();
        }

        $viewData['title'] = _l('marketing_automation'); // Set view title
        $viewData['active_group'] = 'automation';

        if (staff_can('view',  'expenses') || staff_can('view_own',  'expenses')) {
            $features[] = [
                'feature' => 'expenses',
                'name'    => _l('expenses'),
            ];
        }

        $flows = $this->whatsbot_model->get_flow();
        $viewData['flows'] = array_filter($flows, function($flow){
            return $flow['status'] != "DRAFT";
        });
        $viewData['ticket_status'] = $this->tickets_model->get_ticket_status();
        $viewData['project_status'] = $this->projects_model->get_project_statuses();
        
        $this->load->view('marketing_automation', $viewData); // Load view
    }

    public function get_flow_automation() {
        if(!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $flow_id = $this->input->post('flow_id') ?? 0;
        if(!empty($flow_id)) {
            $automation = $this->whatsbot_model->get_flow($flow_id);
        }
        echo $automation->automation;
    }
}
