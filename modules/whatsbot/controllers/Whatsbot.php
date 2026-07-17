<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for WhatsApp integration functionalities.
 */
class Whatsbot extends AdminController
{
    use modules\whatsbot\traits\Whatsapp;
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public $module_version;

    /**
     * Constructor for Whatsbot controller.
     * Loads necessary models.
     */
    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        try {
            $this->load->model(['whatsbot_model', 'interaction_model', 'campaigns_model']);
        } catch (\Throwable $e) {
            show_error('WhatsBot model load failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);
            return;
        }
        $this->load->config('chat_config');
        $module = $this->db->get_where(db_prefix() . 'modules', ['module_name' => 'whatsbot'])->row_array();
        $this->module_version = $module['installed_version'];
    }

    /**
     * Default entry point. Redirects to connect account page.
     */
    public function index()
    {
        redirect('whatsbot/connect');
    }

    /**
     * Manages the connection settings for a WhatsApp business account.
     * Checks permissions, handles form submissions, updates options, and manages redirection.
     */
    public function connect()
    {
        if (!staff_can('connect', 'wtc_connect_account')) {
            access_denied();
        }

        $data['title'] = _l('connect_whatsapp_business');

        // phone number info
        $phone_numbers = $this->getPhoneNumbers();
        if (false == $phone_numbers['status']) {
            update_option('wb_account_connected', 0, 0);
            update_option('wb_webhook_configure', 0, 0);
        } else {
            $webhook_configuration_url = array_column(array_column($phone_numbers['data'], 'webhook_configuration'), 'application');
            if (in_array(site_url('whatsbot/whatsapp_webhook'), $webhook_configuration_url)) {
                update_option('wb_webhook_configure', 1, 0);
                update_option('wb_account_connected', 1, 0);
            } else {
                update_option('wb_webhook_configure', 0, 0);
                update_option('wb_account_connected', 0, 0);
            }

            if (empty(get_option('wb_health_data')) || empty(get_option('wb_health_check_time'))) {
                $this->getHealthStatus();
            }

            // get profile url
            if (empty(get_option('wac_profile_picture_url'))) {
                $profile_data = $this->getProfile();
                if (isset($profile_data['data']) && isset($profile_data['data']->profile_picture_url)) {
                    update_option('wac_profile_picture_url', $profile_data['data']->profile_picture_url ?? '', 0);
                } else {
                    update_option('wac_profile_picture_url', '');
                }
            }

            // generate qr
            if (!empty(get_option('wac_default_phone_number')) && !is_image(module_dir_path('whatsbot', 'assets/images/qrcode.png'))) {
                @unlink(module_dir_path('whatsbot', 'assets/images/qrcode.png'));
                $qr = $this->generateUrlQR("https://wa.me/" . get_option('wac_default_phone_number'), true);
            }

            // subscribe webhook
            if (get_option('wb_webhook_subscribe') != 1) {
                $this->subscribeWebhook();
            }
        }

        if (get_option('wb_account_connected') == 1 && get_option('wb_webhook_configure') == 1) {

            // get tocken info
            $tocken_info = $this->debugTocken();
            if (false == $tocken_info['status']) {
                update_option('wb_account_connected', 0, 0);
                return;
            }
            $data['tocken_info'] = $tocken_info['data'];
            if (isset($data['tocken_info']->issued_at) && !empty($data['tocken_info']->issued_at)) {
                $epoch_time = $data['tocken_info']->issued_at;
                $dt = new DateTime("@$epoch_time");
                $dt->setTimezone(new DateTimeZone(get_option('default_timezone')));
                $data['tocken_info']->issued_at = $dt->format('l jS F Y g:i:s a');
            }
            $data['tocken_info']->issued_at = $data['tocken_info']->issued_at ?? '-';

            if (isset($data['tocken_info']->expires_at) && !empty($data['tocken_info']->expires_at)) {
                $epoch_time = $data['tocken_info']->expires_at;
                $dt = new DateTime("@$epoch_time");
                $dt->setTimezone(new DateTimeZone(get_option('default_timezone')));
                $data['tocken_info']->expires_at = $dt->format('l jS F Y g:i:s a');
            }
            $data['tocken_info']->expires_at = $data['tocken_info']->expires_at ?? '-';

            $data['phone_numbers'] = $phone_numbers['data'];
            $default_number = $phone_numbers['data'][0];

            $wac_phone_number_id = get_option('wac_phone_number_id');
            if (!empty($wac_phone_number_id)) {
                $default_number = array_filter($phone_numbers['data'], function ($phone) use ($wac_phone_number_id) {
                    return $phone->id == $wac_phone_number_id;
                });
                $default_number = reset($default_number);
            }
            $default_phone = preg_replace('/\D/', '', $default_number->display_phone_number);
            update_option('wac_default_phone_number', $default_phone);
            update_option('wac_phone_number_id', $default_number->id, 0);
            $data['default_number'] = $default_number;

            $this->load->view('account_connected', $data);
        } else {
            $this->load->view('account_disconnected', $data);
        }
    }

    /**
     * Sets the default phone number ID via an AJAX request.
     * Updates the 'wac_phone_number_id' option based on the submitted form data.
     */
    public function set_default_number_phone_number_id()
    {
        if (!$this->input->is_ajax_request()) {
            return;
        }

        update_option('wac_phone_number_id', $this->input->post('wac_phone_number_id'), 0);
        $phone_number = preg_replace('/\D/', '', $this->input->post('wac_default_phone_number'));
        update_option('wac_default_phone_number', $phone_number, 0);

        @unlink(module_dir_path('whatsbot', 'assets/images/qrcode.png'));
        $qr = $this->generateUrlQR("https://wa.me/$phone_number", true);

        set_alert('success', _l('default_number_updated'));
        echo json_encode(true);
    }

    /**
     * Displays the chat interface if the user has the necessary view permissions.
     */
    public function chat()
    {
        if (!staff_can('view', 'wtc_chat') && !staff_can('view_own', 'wtc_chat')) {
            access_denied();
        }

        $data['title'] = _l('chat');
        $data['module_version'] = $this->module_version;
        $data['members'] = $this->staff_model->get();
        $data['templates'] = wb_get_whatsapp_template();
        $this->load->view('interaction', $data);
    }

    /**
     * Disconnects the WhatsApp business account and clears related data.
     * Resets all related options and truncates the template table.
     */
    public function disconnect()
    {
        $optionsToClear = [
            'wac_business_account_id',
            'wac_access_token',
            'wac_phone_number_id',
            'wac_default_phone_number',
            'wac_profile_picture_url',
            'wb_fb_app_id',
            'wb_fb_app_secret',
            'wb_fb_config_id',
            'wb_account_connected',
            'wb_webhook_configure',
        ];

        // Clear each option by setting it to an empty string
        array_walk($optionsToClear, fn($key) => update_option($key, '', 0));

        $this->db->truncate(db_prefix() . 'wtc_templates');
        $this->disconnectWebhook();

        set_alert('danger', _l('account_disconnected'));
        redirect(admin_url('whatsbot/connect'));
    }

    /**
     * Fetches and sends interaction data as a JSON response.
     * Retrieves interaction data from the model and outputs it as JSON.
     */
    public function interactions()
    {
        $data['interactions'] = $this->interaction_model->get_interactions();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Loads the activity log view if the user has view permissions.
     * Displays the activity log related to WhatsApp interactions.
     */
    public function activity_log()
    {
        if (!staff_can('view', 'wtc_log_activity')) {
            access_denied('activity_log');
        }
        $data['title'] = _l('activity_log');
        $data['active_group'] = 'activity_log';
        $this->load->view('activity_log/whatsbot_activity_log', $data);
    }

    /**
     * Handles AJAX request for activity log table data.
     * Fetches and displays the activity log table via AJAX.
     */
    public function activity_log_table()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/activity_log_table'));
    }

    /**
     * Loads the view for log details based on a specific log ID.
     * Retrieves detailed information about a specific log entry.
     */
    public function view_log_details($id = '')
    {
        $data['title'] = _l('activity_log');
        $data['log_data'] = $this->whatsbot_model->getWhatsappLogDetails($id);

        $this->load->view('activity_log/view_log_details', $data);
    }

    /**
     * Marks a chat interaction as read and returns the response as JSON.
     * Updates the status of a chat interaction to 'read'.
     */
    public function chat_mark_as_read()
    {
        $id = $this->input->post('interaction_id');
        $response = $this->interaction_model->chat_mark_as_read($id);
        echo json_encode($response);
    }

    /**
     * Clears the activity log if the user has the necessary permissions.
     * Truncates the activity log table and sets an alert message.
     */
    public function clear_log()
    {
        if (staff_can('clear_log', 'wtc_log_activity')) {
            $this->db->truncate(db_prefix() . 'wtc_activity_log');
            set_alert('danger', _l('log_cleared_successfully'));
        }
        redirect(admin_url('whatsbot/activity_log'));
    }

    public function delete_log($id)
    {
        if (staff_can('clear_log', 'wtc_log_activity')) {
            $delete = $this->whatsbot_model->delete_log($id);
            set_alert('danger', $delete ? _l('deleted', _l('log')) : _l('something_went_wrong'));
        }
        redirect(admin_url('whatsbot/activity_log'));
    }

    public function delete_chat()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $id = $this->input->post('interaction_id');
        $res = $this->whatsbot_model->delete_chat($id);
        echo json_encode($res);
    }

    public function assign_staff()
    {
        $post_data = $this->input->post();
        $res = $this->interaction_model->add_assign_staff($post_data);
        echo json_encode($res);
    }

    public function remove_staff()
    {
        $post_data = $this->input->post();
        $res = $this->interaction_model->remove_staff($post_data);
        echo json_encode($res);
    }

    /**
     * Processes an AI response based on the input data.
     * If the API key is verified, it sends a request to OpenAI and returns the response.
     *
     * @return void Outputs the JSON-encoded response.
     */
    public function ai_response()
    {
        if (get_option('wb_open_ai_key_verify') && get_option('enable_wb_openai')) {
            $data = $this->input->post();
            $response = $this->aiResponse($data);
            echo json_encode($response);
        } else {
            echo json_encode([
                'status' => false,
                'message' => _l('open_ai_key_verification_fail')
            ]);
        }
    }

    public function get_chat_required_data()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->interaction_model->get_chat_required_data();
        echo json_encode($data);
    }

    public function disconnect_webhook()
    {
        $post_data = $this->input->post();
        if (!empty($post_data['app_id']) && !empty($post_data['app_secret'])) {
            update_option('wb_fb_app_id', $post_data['app_id'], 0);
            update_option('wb_fb_app_secret', $post_data['app_secret'], 0);
        }
        $disconnect = $this->disconnectWebhook();
        update_option('wb_webhook_configure', 0, 0);
        update_option('wb_fb_app_secret', '', 0);
        update_option('wb_account_connected', '', 0);
        echo json_encode(['status' => $disconnect['status'] ?? false, 'message' => $disconnect['message'] ?? _l('please_enter_all_details')]);
    }

    public function connect_webhook()
    {
        $post_data = $this->input->post();
        if (!empty($post_data['app_id']) && !empty($post_data['app_secret'])) {
            update_option('wb_fb_app_id', $post_data['app_id'], 0);
            update_option('wb_fb_app_secret', $post_data['app_secret'], 0);
            $response = $this->connectWebhook();
            if (!$response['status']) {
                update_option('wb_webhook_configure', 0, 0);
            }
        }
        $status = $response['status'] ?? false;
        $message = $response['status'] ?? false ? _l('webhook_connected') : $response['message'] ?? _l('something_went_wrong');
        echo json_encode(['status' => $status, 'message' => $message ?? _l('something_went_wrong')]);
    }

    public function configure_account()
    {
        $post_data = $this->input->post();
        if (!empty($post_data['wba_id']) && !empty($post_data['access_token'])) {
            update_option('wac_business_account_id', $post_data['wba_id'], 0);
            update_option('wac_access_token', $post_data['access_token'], 0);
            $response = $this->whatsbot_model->load_templates();
            if (false == $response['success']) {
                update_option('wb_account_connected', 0, 0);
            } else {
                update_option('wb_account_connected', 1, 0);
            }
        }
        $status = $response['success'] ?? false;
        $message = $response['success'] ?? false ? _l('account_connected') : $response['message'] ?? _l('something_went_wrong');
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    public function get_health_status($return = true)
    {
        // get profile url
        $profile_data = $this->getProfile();
        if (isset($profile_data['data']) && isset($profile_data['data']->profile_picture_url)) {
            update_option('wac_profile_picture_url', $profile_data['data']->profile_picture_url ?? '', 0);
        } else {
            update_option('wac_profile_picture_url', '');
        }

        if (!empty(get_option('wac_default_phone_number')) && !is_image(module_dir_path('whatsbot', 'assets/images/qrcode.png'))) {
            @unlink(module_dir_path('whatsbot', 'assets/images/qrcode.png'));
            $qr = $this->generateUrlQR("https://wa.me/" . get_option('wac_default_phone_number'), true);
        }

        // health data
        $health = $this->getHealthStatus();
        if (!$health['status']) {
            set_alert('danger', $health['message']);
        }
        if ($return) {
            redirect(admin_url('whatsbot/connect'));
        }
    }

    public function send_test_message()
    {
        if (empty(get_option('wac_default_phone_number')) || empty(get_option('wac_phone_number_id'))) {
            echo json_encode(['status' => false, 'message' => _l('please_select_default_number_first')]);
            exit;
        }
        $post_data = $this->input->post();
        if (!empty($post_data['test_number'])) {
            $res = $this->testMessage($post_data['test_number']);
        } else {
            echo json_encode(['status' => false, 'message' => _l('please_add_number_for_send_message')]);
            exit;
        }
        echo json_encode(['status' => $res['status'] ?? false, 'message' => $res['message'] ?? _l('something_went_wrong')]);
    }

    public function save_settings()
    {
        $post_data = $this->input->post();
        update_option('wb_fb_app_id', $post_data['emb_fb_app_id'], 0);
        update_option('wb_fb_app_secret', $post_data['emb_fb_app_secret'], 0);
        update_option('wb_fb_config_id', $post_data['emb_fb_config_id'], 0);
        set_alert('success', _l('updated_successfully', _l('settings')));
        redirect(admin_url('whatsbot/connect'));
    }

    public function emb_signin()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->embadedSignin($data);
        echo json_encode($res);
    }

    public function init_relation_flow_response(){

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('whatsbot', 'tables/relation_flow_response_table'));
        }
    }

    public function send_chat_campaign()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        $res = $this->campaigns_model->save($data);
        echo json_encode($res);
    }
    // Get paginated interactions data
    public function chat_data($lastChatId = 0)
    {
        $search = $this->input->get('search') ?? '';
        $res = $this->interaction_model->get_paginated_interactions($lastChatId, $search);
        echo json_encode($res); 
    }

    // Get paginated messages for a specific chat
    public function chat_messages_get($chatId, $lastMessageId = 0)
    {
        $res = $this->interaction_model->get_paginated_messages($chatId, $lastMessageId);
        echo json_encode($res);
    }
}
