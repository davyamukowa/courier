<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Bots Controller
 *
 * Handles the functionality related to bots management.
 */
class Bots extends AdminController {
    /**
     * Constructor
     *
     * Loads necessary models.
     */
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['bots_model', 'campaigns_model']);
    }

    /**
     * Index method
     *
     * Loads the main view for bots management.
     */
    public function index() {
        $group = strtolower((string) $this->input->get('group'));
        $redirect_map = [
            'bot_flow' => 'whatsbot/bot_flow',
            'ai_assistant' => 'whatsbot/personal_assistants',
            'automation' => 'whatsbot/marketing_automation',
        ];

        if (isset($redirect_map[$group])) {
            redirect(admin_url($redirect_map[$group]));
        }

        $group_map = [
            'text' => 'text',
            'message_bot' => 'text',
            'template' => 'template',
            'template_bot' => 'template',
        ];
        $requested_type = $group_map[$group] ?? '';
        $can_view_message = staff_can('view', 'wtc_message_bot') || staff_can('view_own', 'wtc_message_bot');
        $can_view_template = staff_can('view', 'wtc_template_bot');

        if (empty($requested_type)) {
            $requested_type = $can_view_message ? 'text' : ($can_view_template ? 'template' : '');
        } elseif ('text' === $requested_type && !$can_view_message && $can_view_template) {
            $requested_type = 'template';
        } elseif ('template' === $requested_type && !$can_view_template && $can_view_message) {
            $requested_type = 'text';
        }

        if (empty($requested_type)) {
            access_denied();
        }

        $data['type'] = $requested_type;
        $data['active_group'] = ('template' === $data['type']) ? 'template_bot' : 'message_bot';
        $data['title'] = ('text' == $data['type']) ? _l('message_bot') : _l('template_bot');
        $this->load->view('bots/manage', $data);
    }

    /**
     * Bot method
     *
     * Loads the view for creating or editing a bot.
     *
     * @param string $type The type of the bot ('template' or 'text').
     * @param string $id The ID of the bot (optional).
     */
    public function bot($type, $id = '')
    {
        $permission = (empty($id)) ? 'create' : 'edit';
        if (!staff_can($permission, ('template' == $type) ? 'wtc_template_bot' : 'wtc_message_bot') && ($this->session->has_userdata('is_bot_clone') == false)) {
            access_denied();
        }

        $data['title'] = ('template' == $type) ? _l('create_template_base_bot') : _l('create_message_bot');
        $data['sections'] = [];
        if (!empty($id)) {
            $data['bot'] = ('template' == $type) ? $this->bots_model->getTemplateBot($id) : $this->bots_model->getMessageBot($id);
            $data['sections'] = json_decode($data['bot']['sections'] ?? '', true) ?? [];
        }
        if (empty($data['sections'])) {
            $data['sections'] = ["sections" => [["section" => "", "text" => [""], "subtext" => [""]]], "action" => ""];
        }
        $data['templates'] = wb_get_whatsapp_template();
        ('template' == $type) ? $this->load->view('bots/template_bot', $data) : $this->load->view('bots/message_bot', $data);
    }

    /**
     * Table method
     *
     * Loads the data for the specified table.
     *
     * @param string $table The table name.
     */
    public function table($table) {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/'.$table));
    }

    /**
     * Save Bots method
     *
     * Saves bot data from POST request.
     */
    public function saveBots() {
        if ($this->input->post()) {
            $permission = empty($this->input->post('id')) ? 'create' : 'edit';
            if (!staff_can($permission, 'wtc_message_bot') && ($this->session->has_userdata('is_bot_clone') == false)) {
                access_denied();
            }

            $res = $this->bots_model->saveBots($this->input->post());
            $this->session->set_userdata('is_bot_clone', false);
            wb_handle_whatsbot_upload($res['id']);
            set_alert($res['type'], $res['message']);
        }
        redirect(admin_url('whatsbot/bots?group=message_bot'));
    }

    /**
     * Delete Message Bot method
     *
     * Deletes a message bot based on type and ID.
     *
     * @param string $type The type of the bot.
     * @param string $id The ID of the bot.
     */
    public function deleteMessageBot($type, $id) {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $res = $this->bots_model->deleteMessageBot($type, $id);
        echo json_encode($res);
    }

    /**
     * Save Template Bot method
     *
     * Saves template bot data from POST request.
     */
    public function saveTemplateBot() {
        if ($this->input->post()) {
            $permission = empty($this->input->post('id')) ? 'create' : 'edit';
            if (!staff_can($permission, 'wtc_template_bot') && ($this->session->has_userdata('is_bot_clone') == false)) {
                access_denied();
            }
            $post_data = $this->input->post();
            $post_data['crm_events'] = !empty($post_data['crm_events']) && is_array($post_data['crm_events']) ? implode(',', $post_data['crm_events']) : '';
            $post_data['send_crm_event_pdf'] = isset($post_data['send_crm_event_pdf']) ? 1 : 0;
            $res = $this->bots_model->saveTemplateBot($post_data);
            $this->session->set_userdata('is_bot_clone', false);
            wb_handle_campaign_upload($res['temp_id'], 'template');
            set_alert($res['type'], $res['message']);
        }
        redirect(admin_url('whatsbot/bots?group=template_bot'));
    }

    /**
     * Change Active Status method
     *
     * Changes the active status of a bot.
     *
     * @param string $type The type of the bot.
     * @param string $id The ID of the bot.
     * @param string $status The new status of the bot.
     */
    public function change_active_status($type, $id, $status) {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        echo json_encode($this->bots_model->change_active_status($type, $id, $status));
    }

    /**
     * Delete bot files
     * @param  string $id The ID of the bot
     */
    public function delete_bot_files($id) {
        $res = $this->bots_model->delete_bot_files($id);
        set_alert('danger', $res['message']);
        redirect(admin_url('whatsbot/bots/bot/text/' . $id));
    }

    /**
     * Bot method
     *
     * create clone of bots.
     *
     * @param string $type The type of the bot ('template' or 'text').
     * @param string $id The ID of the bot.
     */
    public function clone_bot($type, $id) {
        if (!staff_can('clone_bot', ('template' == $type) ? 'wtc_template_bot' : 'wtc_message_bot')) {
            access_denied();
        }

        $data['title'] = ('template' == $type) ? _l('edit_template_base_bot') : _l('edit_message_bot');
        $res = $this->bots_model->clone_bot($type, $id);
        $this->session->set_userdata('is_bot_clone', $res);
        set_alert($res['type'], $res['message']);
        redirect(admin_url('whatsbot/bots/bot/' . $type . '/' . $res['id']));
    }

    /**
     * Bot method
     *
     * create clone of bots.
     *
     * @param string $type The type of the bot ('template' or 'text').
     * @param string $id The ID of the bot.
     */
    public function check_trigger_keyword()
    {
        $keywords = $this->input->post();
        echo json_encode(is_opt_keyword_exists($keywords['trigger']));
    }
}
