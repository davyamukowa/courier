<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Campaigns Controller
 *
 * Handles the functionality related to campaign management.
 */
class Campaigns extends AdminController {
    
    use modules\whatsbot\traits\Whatsapp;
    
    /**
     * Constructor
     *
     * Loads necessary models.
     */
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['campaigns_model', 'leads_model', 'clients_model']);
    }

    /**
     * Index method
     *
     * Loads the main view for campaigns management.
     */
    public function index() {
        if (!staff_can('view', 'wtc_campaign')) {
            access_denied();
        }

        $data['title'] = _l('campaigns');
        $data['active_group'] = 'template_campaigns';
        $this->load->view('campaigns/manage', $data);
    }

    /**
     * Campaign method
     *
     * Loads the view for creating or editing a campaign.
     *
     * @param string $id The ID of the campaign (optional).
     */
    public function campaign($id = '') {
        $permission = empty($id) ? 'create' : 'edit';
        if (!staff_can($permission, 'wtc_campaign')) {
            access_denied();
        }

        $data['title'] = _l('campaigns');

        $data['leads'] = $this->leads_model->get();
        $data['contacts'] = $this->clients_model->get_contacts();

        if(!option_exists('wac_phone_numbers')){
            $this->getPhoneNumbers();
        }
        $data['phone_numbers'] = json_decode(get_option('wac_phone_numbers'), true) ?? [];
        $data['templates'] = wb_get_whatsapp_template();

        if (!empty($id)) {
            $data['campaign'] = $this->campaigns_model->get($id);

            $relationMapping = [
                'leads' => 'lead_ids',
                'contacts' => 'contact_ids',
            ];


            if (isset($relationMapping[$data['campaign']['rel_type']])) {
                $data['campaign'][$relationMapping[$data['campaign']['rel_type']]] = !empty($data['campaign']['rel_ids']) ? json_decode($data['campaign']['rel_ids']) : [];
            }

            $rel_data = json_decode($data['campaign']['rel_data'], true);
            $data['campaign']['status'] = $rel_data['status'];
            $data['campaign']['source'] = $rel_data['source'];
            $data['campaign']['group'] = $rel_data['group'];
        }
        $this->load->view('campaigns/campaign', $data);
    }

    /**
     * Save method
     *
     * Saves campaign data from POST request.
     */
    public function save() {
        $permission = empty($this->input->post('id')) ? 'create' : 'edit';
        if (!staff_can($permission, 'wtc_campaign')) {
            access_denied();
        }

        $res = $this->campaigns_model->save($this->input->post());
        set_alert($res['type'], $res['message']);
        redirect(admin_url('whatsbot/campaigns'));
    }

    /**
     * Get Table Data method
     *
     * Loads the data for the specified table.
     *
     * @param string $table The table name.
     * @param string $id The ID associated with the table (optional).
     * @param string $rel_type The relationship type (optional).
     *
     * @return bool Returns false if the request is not an AJAX request.
     */
    public function get_table_data($table, $id = '', $rel_type = '') {
        if (!$this->input->is_ajax_request()) {
            return false;
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/'.$table), compact('id', 'rel_type'));
    }

    /**
     * Delete method
     *
     * Deletes a campaign based on its ID.
     *
     * @param string $id The ID of the campaign.
     */
    public function delete($id) {
        if (!staff_can('delete', 'wtc_campaign')) {
            access_denied();
        }

        $res = $this->campaigns_model->delete($id);
        set_alert('danger', $res['message']);
        redirect(admin_url('whatsbot/campaigns'));
    }

    /**
     * Get Template Map method
     *
     * Loads the template map for a campaign.
     */
    public function get_template_map() {
        if ($this->input->is_ajax_request()) {
            $data['template'] = wb_get_whatsapp_template($this->input->post('template_id'));

            if (!empty($data['template'])) {
                $header_data = $data['template']['header_data_text'] ?? '';
                $body_data = $data['template']['body_data'] ?? '';
                $footer_data = $data['template']['footer_data'] ?? '';
                $button_data = !empty($data['template']['buttons_data']) ? json_decode($data['template']['buttons_data']) : [];
            }

            if (!empty($this->input->post('temp_id'))) {
                $campaign = $this->campaigns_model->get($this->input->post('temp_id'));

                $data['header_params'] = json_decode($campaign['header_params'] ?? '');
                $data['body_params'] = json_decode($campaign['body_params'] ?? '');
                $data['footer_params'] = json_decode($campaign['footer_params'] ?? '');
                $data['campaign'] = $campaign;
            }

            $view = $this->load->view('variables', $data, true);
            echo json_encode(['view' => $view, 'header_data' => $header_data ?? '', 'body_data' => $body_data ?? '', 'footer_data' => $footer_data ?? '', 'button_data' => $button_data]);
        }
    }

    /**
     * View method
     *
     * Loads the view for a specific campaign.
     *
     * @param string $id The ID of the campaign.
     */
    public function view($id) {
        if (!staff_can('show', 'wtc_campaign')) {
            access_denied();
        }

        $data['title'] = _l('view_campaign');
        $data['campaign'] = $this->campaigns_model->get($id);
        $total_leads = total_rows(db_prefix().'leads');
        $total_contacts = total_rows(db_prefix().'contacts');
        $campaign_data = count(json_decode($data['campaign']['rel_ids']));
        $relation_type_map = [
            'leads' => $total_leads,
            'contacts' => $total_contacts,
        ];
        $data['total_percent'] = number_format(($campaign_data / $relation_type_map[$data['campaign']['rel_type']]) * 100, 2);

        $data['delivered_to_count'] = total_rows(db_prefix().'wtc_campaign_data', ['status' => 2, 'campaign_id' => $id]);
        $data['read_by_count'] = total_rows(db_prefix().'wtc_campaign_data', ['message_status' => 'read', 'campaign_id' => $id]);
        $data['delivered_to_percent'] = $data['read_by_percent'] = 0;
        if (!empty($data['delivered_to_count'])) {
            $data['delivered_to_percent'] = number_format(($data['delivered_to_count'] / $campaign_data) * 100, 2);
            $data['read_by_percent'] = number_format(($data['read_by_count'] / $data['delivered_to_count']) * 100, 2);
        }
        $this->load->view('campaigns/view', $data);
    }

    /**
     * Pause or Resume Campaign method
     *
     * Pauses or resumes a campaign based on its ID.
     *
     * @param string $id The ID of the campaign.
     */
    public function pause_resume_campaign($id) {
        $res = $this->campaigns_model->pause_resume_campaign($id);
        set_alert('success', $res['message']);
        redirect(admin_url('whatsbot/campaigns/view/'.$id));
    }

    /**
     * Delete campaign files
     * @param  string $id The ID of the campaign
     */
    public function delete_campaign_files($id) {
        $res = $this->campaigns_model->delete_campaign_files($id);
        set_alert('danger', $res['message']);
        redirect($res['url']);
    }

    public function get_leads() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $post_data = $this->input->post();
        $where = [];
        if (!empty($post_data)) {
            if (isset($post_data['status']) && !empty($post_data['status'])) {
                $where['status'] = $post_data['status'];
            }
            if (isset($post_data['source']) && !empty($post_data['source'])) {
                array_push($where, ' AND source = ' . $post_data['source']);
                $where['source'] = $post_data['source'];
            }
        }
        $data = $this->leads_model->get('', $where);
        echo json_encode($data);
    }

    public function get_lead_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data['status'] = $this->leads_model->get_status();
        $data['source'] = $this->leads_model->get_source();
        echo json_encode($data);
    }

    public function get_contacts() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $post_data = $this->input->post();
        $where = ['active' => 1];
        if (!empty($post_data) && $post_data['group'] != '[]' && isset($post_data['group']) && !empty($post_data['group'])) {
            if (!is_array($post_data['group'])) {
                $post_data['group'] = json_decode($post_data['group'], true);
            }
            if (!empty($post_data['group'])) {
                $data = $this->campaigns_model->get_contacts_where_group($post_data['group']);
            }
            echo json_encode($data);
            exit;
        }
        $data = $this->clients_model->get_contacts('', $where);
        echo json_encode($data);
    }

    public function get_contacts_gropus()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->client_groups_model->get_groups();
        echo json_encode($data);
    }

    public function initiateChat()
    {
        $res = $this->campaigns_model->initiateChat($this->input->post());
        if ($res['rel_type'] == 'leads') {
            set_alert($res['type'], $res['message']);
            redirect(admin_url('leads'));
        } elseif ($res['rel_type'] == 'contacts') {
            set_alert($res['type'], $res['message']);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
