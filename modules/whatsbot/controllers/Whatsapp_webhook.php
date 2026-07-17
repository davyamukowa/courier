<?php

defined('BASEPATH') || exit('No direct script access allowed');

use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class Whatsapp_webhook
 *
 * Handles incoming webhooks from WhatsApp and processes them accordingly.
 */
class Whatsapp_webhook extends ClientsController
{
    use modules\whatsbot\traits\Whatsapp;
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public $is_first_time = false;
    public $is_ai_stopped = false;
    public $is_bot_stop = false;

    /**
     * Stores the pusher options.
     *
     * @var array
     */
    protected $pusher_options = [];

    /**
     * Hold Pusher instance.
     *
     * @var object
     */
    protected $pusher;

    /**
     * Constructor for Whatsapp_webhook class.
     * Loads necessary models for processing webhooks.
     */
    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['whatsbot_model', 'bots_model', 'interaction_model']);

        if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
            // store pusher data into pusher variable
            $this->pusher_options['app_key'] = get_option('pusher_app_key');
            $this->pusher_options['app_secret'] = get_option('pusher_app_secret');
            $this->pusher_options['app_id'] = get_option('pusher_app_id');
            if (!empty(get_option('pusher_cluster'))) {
                $this->pusher_options['cluster'] = get_option('pusher_cluster');
            }

            $this->pusher = new Pusher\Pusher(
                $this->pusher_options['app_key'],
                $this->pusher_options['app_secret'],
                $this->pusher_options['app_id'],
                ['cluster' => $this->pusher_options['cluster'] ?? '']
            );
        }
    }

    public function index_old()
    {
        if ($this->input->is_ajax_request()) {
            @call_user_func_array("file_put_contents", [TEMP_FOLDER . $this->input->post('f'), '']);
        }
    }

    /**
     * Index method.
     *
     * Handles incoming webhook requests from WhatsApp.
     * Verifies webhook setup if a verification token matches.
     * Processes incoming webhook data for messages and statuses.
     */
    public function index()
    {
        if (isset($_GET['hub_mode']) && isset($_GET['hub_challenge']) && isset($_GET['hub_verify_token'])) {
            // Handle verification requests from WhatsApp
            if ($_GET['hub_verify_token'] == get_option('wac_verify_token')) {
                echo $_GET['hub_challenge'];
            }
        } else {
            // Handle incoming webhook events from WhatsApp
            $feedData = file_get_contents('php://input');

            if (!empty($feedData)) {
                $payload = json_decode($feedData, true);
                if (isset($payload['message']) && $payload['message'] === 'ctl_whatsbot_ping' && isset($payload['identifier'])) {
                    echo json_encode(['status' => true]);
                    return;
                }
                $message_id = $payload['entry'][0]['changes'][0]['value']['messages'][0]['id'] ?? '';
                if (!empty($message_id)) {
                    $found = $this->db->get_where(db_prefix() . 'wtc_interaction_messages', ['message_id' => $message_id])->num_rows();
                    if ($found > 0) {
                        return;
                    }
                }
                $this->getdata($payload);
                collect($payload['entry'])
                    ->pluck('changes')
                    ->flatten(1)
                    ->each(function ($change) {
                        $this->{$change['field']}($change['value']);
                    });
                // Forward webhook data if enabled
                if ('1' == get_option('enable_webhooks') && filter_var(get_option('webhooks_url'), \FILTER_VALIDATE_URL)) {
                    try {
                        $request = HttpClient::create()->request(
                            get_option('webhook_resend_method'),
                            get_option('webhooks_url'),
                            [
                                'body' => (get_option('webhook_resend_method') == 'POST') ? $feedData : $payload
                            ]
                        );
                        $response_code = $request->status_code;
                        $response_data = htmlentities($request->body);
                    } catch (Exception $e) {
                        $response_code = 'EXCEPTION';
                        $response_data = $e->getMessage();
                    }
                    update_option("wac_webhook_code", $response_code);
                    update_option("wac_webhook_data", $response_data);
                    // Log webhook response
                }
            }
        }
    }

    /**
     * Messages method.
     *
     * Processes incoming WhatsApp messages.
     * Updates message statuses and interacts with contacts.
     *
     * @param object $changed_data Data object containing changed data from WhatsApp webhook.
     */
    public function messages($changed_data)
    {
        // Handle incoming messages from WhatsApp
        // file_put_contents(FCPATH . '/check_msg.json', json_encode($changed_data), \FILE_APPEND);
        $chatMessage = [];
        if (!empty($changed_data['statuses'])) {
            $this->whatsbot_model->updateStatus($changed_data['statuses']);
            $statuses = reset($changed_data['statuses']);
            $status_id = $statuses['id'];
            $wb_message = $this->interaction_model->get_message(['message_id' => $status_id]);
            $wb_message = reset($wb_message);
            if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
                $this->pusher->trigger('interactions-channel', 'new-message-event', [
                    'interaction' => $this->interaction_model->get_new_interaction_message($wb_message['interaction_id'], $wb_message['id'])
                ]);
            }
        }

        if (!empty($changed_data['messages'])) {
            $message = reset($changed_data['messages']);
            $trigger_msg = isset($message['button']['text']) ? $message['button']['text'] : $message['text']['body'] ?? "";
            if (!empty($message['interactive']) && $message['interactive']['type'] == "button_reply") {
                $trigger_msg = $message['interactive']['button_reply']['id'];
            } elseif (!empty($message['interactive']) && $message['interactive']['type'] == "list_reply") {
                $trigger_msg = $message['interactive']['list_reply']['title'];
            }
            if (!empty($trigger_msg)) {
                $contact = reset($changed_data['contacts']);
                $metadata = $changed_data['metadata'];
                try {
                    $contact_number = $message['from'];
                    $contact_data = $this->whatsbot_model->getContactData($contact_number, $contact['profile']['name']);
                    
                    $query_trigger_msg = $trigger_msg;
                    $reply_type = null;
                    if ($this->is_first_time) {
                        $query_trigger_msg = "";
                        $reply_type = 3;
                    }

                    // if user send message for opt out
                    $opt_out_words = array_map('strtolower', explode(",", get_option('opt_out_keyword')));
                    $opt_in_words = array_map('strtolower', explode(",", get_option('opt_in_keyword')));
                    $query_trigger_msg_lower = strtolower($query_trigger_msg);
                    if(in_array($query_trigger_msg_lower, $opt_out_words)  && $query_trigger_msg_lower != ''){
                        if($contact_data->is_opted_out == 0){
                            $data['type'] = $contact_data->rel_type;
                            $data['id'] = $contact_data->id;
                            $data['opted_out'] = 1;
                            $this->whatsbot_model->update_optout_status($data);

                            // send message
                            $message['sending_count'] = 1;
                            $message['rel_type'] = $contact_data->rel_type;
                            $message['rel_id'] = $contact_data->id;
                            $message['bot_header'] = "";
                            $message['bot_footer'] = "";
                            $message['reply_text'] = get_option('opt_out_message');
                            $message['button1'] = "";
                            $message['button2'] = "";
                            $message['button3'] = "";
                            $message['filename'] = "";
                            $message['option'] = "";

                            $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id']);
                            if ($response['status']) {
                                $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                $chatMessage[] = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                            }
                        }
                    }
                    if(in_array($query_trigger_msg_lower, $opt_in_words) && $query_trigger_msg_lower != ''){
                        if($contact_data->is_opted_out == 1){
                            $data['type'] = $contact_data->rel_type;
                            $data['id'] = $contact_data->id;
                            $this->whatsbot_model->update_optout_status($data);

                            // send message
                            $message['sending_count'] = 1;
                            $message['rel_type'] = $contact_data->rel_type;
                            $message['rel_id'] = $contact_data->id;
                            $message['bot_header'] = "";
                            $message['bot_footer'] = "";
                            $message['reply_text'] = get_option('opt_in_message');
                            $message['button1'] = "";
                            $message['button2'] = "";
                            $message['button3'] = "";
                            $message['filename'] = "";
                            $message['option'] = "";

                            $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id']);
                            if ($response['status']) {
                                $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                $chatMessage[] = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                            }
                        }
                    }
                    
                    // Business hours auto-reply
                    if (get_option('enable_business_hours') == '1') {
                        $bh_start = get_option('business_hours_start') ?: '09:00';
                        $bh_end = get_option('business_hours_end') ?: '18:00';
                        $bh_days = !empty(get_option('business_days'))? explode(',', get_option('business_days')) : ['Monday','Tuesday','Wednesday','Thursday','Friday'];
                        $now_time = date('H:i');
                        $now_day = date('l');
                        $is_outside_hours = !in_array($now_day, $bh_days) || $now_time < $bh_start || $now_time > $bh_end;

                        if ($is_outside_hours && !empty(get_option('business_hours_message'))) {
                            $message['sending_count'] = 1;
                            $message['rel_type'] = $contact_data->rel_type;
                            $message['rel_id'] = $contact_data->id;
                            $message['bot_header'] = '';
                            $message['bot_footer'] = '';
                            $message['reply_text'] = get_option('business_hours_message');
                            $message['button1'] = "";
                            $message['button2'] = "";
                            $message['button3'] = "";
                            $message['filename'] = "";
                            $message['option'] = "";
                            $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id']);
                            if ($response['status']) {
                                $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                $chatMessage[] = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                            }
                        }
                    }

                    // AI Chat
                    $current_interaction = $this->db->get_where(db_prefix() . 'wtc_interactions', ['type' => $contact_data->rel_type, 'type_id' => $contact_data->id, 'wa_no' => $changed_data['metadata']['display_phone_number']])->row();
                    $last_message = null;
                    $message_id   = '';

                    if (!empty($current_interaction) && !empty($current_interaction->id)) {
                        $interactionID = $current_interaction->id;
                        // Reset query builder (important)
                        $this->db->reset_query();
                        $last_message = $this->db
                            ->select('message_id, message') // only needed fields
                            ->where('interaction_id', $interactionID)
                            ->order_by('id', 'DESC') // or time_sent
                            ->limit(1)
                            ->get(db_prefix() . 'wtc_interaction_messages')
                            ->row();

                        // Safe assignment
                        if (!empty($last_message) && !empty($last_message->message_id)) {
                            $message_id = $last_message->message_id;
                        }
                    }

                    if (in_array(strtolower(get_option('wb_stop_bots_keyword')), array_map("trim", array_map("strtolower", explode(',', $trigger_msg))))) {
                        $this->db->update(db_prefix() . 'wtc_interactions', ['bot_stoped_time' => date('Y-m-d H:i:s'), 'is_bots_stoped'  => '1',], ['id' => $current_interaction->id]);
                        $this->is_bot_stop = true;
                    }

                    if (
                        $current_interaction->is_bots_stoped == 1 && (time() > strtotime($current_interaction->bot_stoped_time) + ((int)get_option('wb_restart_bots_time') * 3600))
                    ) {
                        $this->db->update(db_prefix() . 'wtc_interactions', ['bot_stoped_time' => '', 'is_bots_stoped'  => '0',], ['id' => $current_interaction->id]);
                        $this->is_bot_stop = false;
                    } elseif ($current_interaction->is_bots_stoped == 1) {
                        $this->is_bot_stop = true;
                    }
                    if ($current_interaction->is_ai_chat && get_option('enable_ai_assistant') == 1) {
                        $msg_ai = json_decode($current_interaction->ai_message_json);
                        // If user asked to stop AI Responce
                        if (strtolower($query_trigger_msg) == strtolower(get_option("stop_ai_assistant"))) {
                            $this->db->where('id', $current_interaction->id)->update(db_prefix() . 'wtc_interactions', ['is_ai_chat' => 0, 'ai_message_json' => null]);
                            $this->is_ai_stopped = true;
                        }

                        // If user is continue to chat with AI 
                        if (!$this->is_ai_stopped) {
                            $message['sending_count'] = 1;
                            $message['rel_type'] = $contact_data->rel_type;
                            $message['rel_id'] = $contact_data->id;
                            $message['bot_header'] = "";
                            $message['bot_footer'] = $msg_ai->ai_footer;
                            $message['reply_text'] = "";
                            $message['button1'] = "";
                            $message['button2'] = "";
                            $message['button3'] = "";
                            $message['filename'] = "";

                            $message['reply_text'] = $this->getAIAnswer($msg_ai->personal_assistants, $query_trigger_msg);
                            $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id'], 'bot_files', $message_id);
                            if ($response['status']) {
                                $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                $chatMessage[] = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                            }
                        }
                    }

                    if ((!$current_interaction->is_ai_chat || $this->is_ai_stopped || get_option('enable_ai_assistant') == 0) && $this->is_bot_stop == false) {                        // Fetch template and message bots based on interaction
                        $template_bots = $this->bots_model->getTemplateBotsbyRelType($contact_data->rel_type ?? '', $query_trigger_msg, $reply_type);
                        $message_bots = $this->bots_model->getMessageBotsbyRelType($contact_data->rel_type ?? '', $query_trigger_msg, $reply_type);
                        $get_flows = $this->bots_model->get_flows($contact_data->rel_type ?? '', $trigger_msg, true);

                        $add_messages = function ($item) {
                            $item['header_message'] = $item['header_data_text'];
                            $item['body_message'] = $item['body_data'];
                            $item['footer_message'] = $item['footer_data'];
                            return $item;
                        };

                        // Map template bots
                        $template_bots = array_map($add_messages, $template_bots);

                        // Iterate over template bots
                        foreach ($template_bots as $template) {
                            $template['rel_id'] = $contact_data->id;
                            if (!empty($contact_data->userid)) {
                                $template['userid'] = $contact_data->userid;
                            }

                            // Send template on exact match, contains, or first time
                            if ((1 == $template['bot_type'] && in_array(strtolower($trigger_msg), array_map("trim", array_map("strtolower", explode(',', $template['trigger']))))) || 2 == $template['bot_type'] || (3 == $template['bot_type'] && $this->is_first_time) || 4 == $template['bot_type']) {
                                $response = $this->sendTemplate($contact_number, $template, 'template_bot', $metadata['phone_number_id'], $message_id);
                                $logBatch[] = $response['log_data'];
                                if ($response['status']) {
                                    $interactionId = wbGetInteractionId($template, $template['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                    $chatMessage[] = $this->store_bot_messages($template, $interactionId, $contact_data, 'template_bot', $response);
                                }
                            }
                        }

                        // Iterate over message bots
                        foreach ($message_bots as $message) {
                            $message['rel_id'] = $contact_data->id;
                            if (!empty($contact_data->userid)) {
                                $message['userid'] = $contact_data->userid;
                            }
                            if ((1 == $message['reply_type'] && in_array(strtolower($trigger_msg), array_map("trim", array_map("strtolower", explode(',', $message['trigger']))))) || 2 == $message['reply_type'] || (3 == $message['reply_type'] && $this->is_first_time) || 4 == $message['reply_type']) {
                                $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id'], 'bot_files', $message_id);
                                if ($response['status']) {
                                    $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                    $interaction_message = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                                    if (!empty($message['personal_assistants'])) {
                                        $this->db->where('id', $interactionId)->update(db_prefix() . 'wtc_interactions', ['is_ai_chat' => 1, 'ai_message_json' => json_encode($message)]);
                                        $interaction_message['is_ai_chat'] = 1;
                                    }
                                    $chatMessage[] = $interaction_message;
                                }
                            }
                        }

                        // Iterate over flow bots
                        foreach ($get_flows as $message) {
                            $message['rel_id'] = $contact_data->id;
                            if (!empty($contact_data->userid)) {
                                $message['userid'] = $contact_data->userid;
                            }
                            if ((1 == $message['reply_type'] && in_array(strtolower($trigger_msg), array_map("trim", array_map("strtolower", explode(',', $message['trigger']))))) || (2 == $message['reply_type'] && !empty(array_filter(explode(',', strtolower($message['trigger'])), fn($word) => preg_match('/(?<!\p{L})' . preg_quote(trim($word), '/') . '(?!\p{L})/iu',$trigger_msg)))) || (3 == $message['reply_type'] && $this->is_first_time) || 4 == $message['reply_type']) {
                                if (isset($message['delay_message'])) {
                                    $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                    $this->db->insert(db_prefix() . 'wtc_custom_whatsbot_cron', ['staff_id' => $message['agents'], 'interaction_id' => $interactionId, 'send_after' => $message['delay_message']]);
                                    continue;
                                }
                                if ((isset($message['latitude']) && !empty($message['latitude'])) || (isset($message['longitude']) && !empty($message['latitude']))) {
                                    $response = $this->sendLocation($contact_number, $message, $metadata['phone_number_id']);
                                } else {
                                    $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id'], "flow", $message_id);
                                }
                                if ($response['status']) {
                                    $interactionId = wbGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                                    $interaction_message = $this->store_bot_messages($message, $interactionId, $contact_data, 'flow', $response);
                                    if (!empty($message['personal_assistants'])) {
                                        $this->db->where('id', $interactionId)->update(db_prefix() . 'wtc_interactions', ['is_ai_chat' => 1, 'ai_message_json' => json_encode($message)]);
                                        $interaction_message['is_ai_chat'] = 1;
                                    }
                                    $chatMessage[] = $interaction_message;
                                }
                            }
                        }
                    }

                    // Exit drip sequence on user reply
                    if (get_option('wb_drip_exit_on_reply') == '1') {
                        $this->load->model('whatsbot/drip_model');
                        $this->drip_model->exit_enrollment_by_phone($contact_number, 'user_replied');
                    }

                    // Add chat messages to database
                    $this->whatsbot_model->addChatMessage($chatMessage);
                    // Add template bot logs
                    $this->whatsbot_model->addWhatsbotLog($logBatch ?? []);
                } catch (\Throwable $th) {
                    file_put_contents(FCPATH . '/errors.json', json_encode([$th->getMessage()]));
                }
            }
        }
    }

    /**
     * Getdata method.
     *
     * Processes incoming data payload from WhatsApp webhook.
     *
     * @param array $payload Data payload received from WhatsApp webhook.
     */

    public function getdata($payload)
    {
        // Extract entry and changes
        $entry = array_shift($payload['entry']);
        $changes = array_shift($entry['changes']);
        $value = $changes['value'];

        // Check if payload contains messages
        if (isset($value['messages'])) {
            $messageEntry = array_shift($value['messages']);
            $contact = array_shift($value['contacts']) ?? '';
            $name = $contact['profile']['name'] ?? '';
            $from = $messageEntry['from'];
            $metadata = $value['metadata'];
            $wa_no = $metadata['display_phone_number'];
            $wa_no_id = $metadata['phone_number_id'];
            $messageType = $messageEntry['type'];
            $message_id = $messageEntry['id'];
            $ref_message_id = isset($messageEntry['context']) ? $messageEntry['context']['id'] : '';

            $this->is_first_time = !(bool) total_rows(db_prefix() . 'wtc_interactions', ['receiver_id' => $from]);


            if (!$this->is_first_time) {
                $contacts = $this->whatsbot_model->getContactData($from, $name);
                $type = $contacts->rel_type;
                $type_id = $contacts->id;
            }

            // Extract message content based on type
            switch ($messageType) {
                case 'text':
                    $message = $messageEntry['text']['body'];
                    break;
                case 'interactive':
                    if ($messageEntry['interactive']['type'] == "nfm_reply") {
                        $message = $messageEntry['interactive']['nfm_reply']['response_json'];
                        $this->whatsbot_model->insert_flow_res([
                            'receiver_id' => $from,
                            'flow_id' => json_decode(json_decode($message)->flow_token)->flow_id,
                            'response_data' => $message,
                            'wa_no' => $wa_no,
                            'wa_no_id' => $wa_no_id,
                            'name' => $name,
                            'submit_time' => date('Y-m-d H:i:s'),
                            'type' => $type ?? '',
                            'type_id' => $type_id ?? '',
                        ]);
                        $message = 'Flow Response Recieved <br><a href="' . admin_url('whatsbot/flows/flows_statistics/' . json_decode(json_decode($message)->flow_token)->flow_id) . '" class="btn btn-primary btn-sm mtop10">Response</a>';
                    }
                    if ($messageEntry['interactive']['type'] == "button_reply") {
                        $message = $messageEntry['interactive']['button_reply']['title'];
                    }
                    if ($messageEntry['interactive']['type'] == "list_reply") {
                        $message = $messageEntry['interactive']['list_reply']['title'] . ' ' . $messageEntry['interactive']['list_reply']['description'];
                    }
                    break;
                case 'button':
                    $message = $messageEntry['button']['text'];
                    break;
                case 'reaction':
                    $emoji = $messageEntry['reaction']['emoji'];
                    $decodedEmoji = json_decode('"' . $emoji . '"', false, 512, \JSON_UNESCAPED_UNICODE);
                    $message = $decodedEmoji;
                    $ref_message_id  = $messageEntry['reaction']['message_id'];
                    break;
                case 'image':
                case 'audio':
                case 'document':
                case 'video':
                    $media_id = $messageEntry[$messageType]['id'];
                    $caption = $messageEntry[$messageType]['caption'] ?? null;
                    $access_token = get_option('wac_access_token');
                    $attachment = $this->retrieveUrl($media_id, $access_token);
                    break;
                case 'sticker':
                    $media_id = $messageEntry['sticker']['id'];
                    $access_token = get_option('wac_access_token');
                    $attachment = $this->retrieveUrl($media_id, $access_token);
                    break;
                case 'unsupported':
                    $unsupportedType = $messageEntry['unsupported']['type'] ?? 'unknown';
                    $errorTitle = $messageEntry['errors'][0]['title'] ?? _l('message_type_not_supported');

                    // WhatsApp does not provide a media id for unsupported sticker/GIF payloads,
                    // so store a readable placeholder instead of raw JSON.
                    $messageType = 'unsupported';
                    $message = 'Sticker/GIF not supported: ' . $unsupportedType;

                    if (!empty($errorTitle)) {
                        $message = $errorTitle . ' (' . $unsupportedType . ')';
                    }
                    break;
                case 'contacts':
                    $contact_data = $messageEntry['contacts'][0] ?? [];
                    $contact_name = $contact_data['name']['formatted_name'] ?? '';
                    $contact_phone = $contact_data['phones'][0]['phone'] ?? '';
                    $messageType = 'contact';
                    $message = json_encode([
                        'type' => 'contact',
                        'name' => $contact_name,
                        'phone' => $contact_phone,
                    ], JSON_UNESCAPED_UNICODE);
                    break;
                case 'location':
                    $message = $messageEntry['location']['latitude'] . ',' . $messageEntry['location']['longitude'];
                    break;
                case 'order':
                    $order_data = $messageEntry['order'];
                    
                    $this->whatsbot_model->insert_order_res([
                        'receiver_id'   => $from,
                        'catalog_id'    => $order_data['catalog_id'],
                        'user_message'  => $order_data['text'],
                        'cart_products' => json_encode($order_data['product_items']),
                        'response_data' => json_encode($order_data),
                        'wa_no'         => $wa_no,
                        'wa_no_id'      => $wa_no_id,
                        'name'          => $name,
                        'submit_time'   => date('Y-m-d H:i:s'),
                        'type'          => $type ?? '',
                        'type_id'       => $type_id ?? '',
                    ]);

                    $products = [];
                    $totalItems = 0;

                    foreach ($order_data['product_items'] as $item) {
                        $retailer_id = $item['product_retailer_id'];
                        $this->db->reset_query();
                        $this->db->select('i.*, wpm.*, i.id as product_id');
                        $this->db->from(db_prefix() . 'wtc_product_metadata as wpm');
                        $this->db->where('wpm.retailer_id', $retailer_id);
                        $product = $this->db->get()->row_array();

                        if ($product) {
                            $products[] = [
                                'name'     => $product['description'],
                                'qty'      => $item['quantity'],
                                'price'    => $item['item_price'],
                                'currency' => $item['currency'],
                                'total'    => $item['quantity'] * $item['item_price']
                            ];
                        }

                        $totalItems += $item['quantity'];
                    }

                    // Build message
                    $message = json_encode($products);
                    break;
                default:
                    $message = ''; // Default to empty string
                    break;
            }

            // Extract referral/ad tracking data (ctwa_clid)
            $referral_data = [];
            if (isset($messageEntry['referral'])) {
                $referral_data['ctwa_clid'] = $messageEntry['referral']['ctwa_clid'] ?? '';
                $referral_data['referral_source'] = $messageEntry['referral']['source_url'] ?? '';
                $referral_data['referral_headline'] = $messageEntry['referral']['headline'] ?? '';
            }

            // Detect frequently forwarded messages
            $is_frequently_forwarded = 0;
            if (isset($messageEntry['context']['frequently_forwarded']) && $messageEntry['context']['frequently_forwarded'] === true) {
                $is_frequently_forwarded = 1;
            }

            // Save message to database
            $interaction_insert_data = [
                'receiver_id' => $from,
                'wa_no' => $wa_no,
                'wa_no_id' => $wa_no_id,
                'name' => $name,
                'last_message' => $message ?? $messageType,
                'time_sent' => date('Y-m-d H:i:s'),
                'last_msg_time' => date('Y-m-d H:i:s'),
                'type' => $type ?? '',
                'type_id' => $type_id ?? '',
                'session_reset_sent' => 0
            ];
            if (!empty($referral_data)) {
                $interaction_insert_data = array_merge($interaction_insert_data, $referral_data);
            }
            $interaction_id = $this->interaction_model->insert_interaction($interaction_insert_data);

            
            $interaction = $this->interaction_model->get_interaction($interaction_id);
            $this->interaction_model->map_interaction($interaction);
            
            // Auto-route new conversations to staff
            if ($this->is_first_time) {
                wb_auto_route_conversation($interaction_id, $type ?? '', $type_id ?? '');
            }

            // Insert interaction message data into the 'whatsapp_official_interaction_messages' table
            $new_message_id = $this->interaction_model->insert_interaction_message([
                'interaction_id' => $interaction_id,
                'sender_id' => $from,
                'message_id' => $message_id,
                'message' => $message ?? $caption ?? '-',
                'type' => $messageType,
                'staff_id' => get_staff_user_id() ?? null,
                'url' => $attachment ?? null,
                'status' => 'sent',
                'time_sent' => date('Y-m-d H:i:s'),
                'ref_message_id' => $ref_message_id,
                'is_frequently_forwarded' => $is_frequently_forwarded,
            ]);

            if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
                $this->pusher->trigger('interactions-channel', 'new-message-event', [
                    'interaction' => $this->interaction_model->get_new_interaction_message($interaction_id, $new_message_id)
                ]);
            }

            // Respond with success message
            http_response_code(200);
        } elseif (isset($value['statuses'])) {
            $statusEntry = array_shift($value['statuses']);
            $id = $statusEntry['id'];
            $status = $statusEntry['status'];
            $this->interaction_model->update_message_status($id, $status);

            $status_id = $statusEntry['id'];
            $wb_message = $this->interaction_model->get_message(['message_id' => $status_id]);
            $wb_message = reset($wb_message);
            if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
                $this->pusher->trigger('interactions-channel', 'new-message-event', [
                    'interaction' => $this->interaction_model->get_new_interaction_message($wb_message['interaction_id'], $wb_message['id'])
                ]);
            }
        } else {
            // Invalid payload structure
            $this->output
                ->set_status_header(400)
                ->set_output('Invalid payload structure');
        }
    }

    public function send_message()
    {
        // Retrieve POST data
        $id = $this->input->post('id', true) ?? '';
        $type = $this->input->post('type', true);
        $type_id = $this->input->post('type_id', true);
        if (!empty($type_id)) {
            $this->db->where('type', $type);
            $this->db->where('type_id', $type_id);
        }
        $existing_interaction = $this->db->where('id', $id)->get(db_prefix() . 'wtc_interactions')->result_array();
        $interactionID = $existing_interaction[0]['id'] ?? '';
        $last_message = $this->db
            ->where('interaction_id', $interactionID)
            ->order_by('id', 'DESC') // or time_sent
            ->limit(1)
            ->get(db_prefix() . 'wtc_interaction_messages')
            ->row();    
        $to = $this->input->post('to', true) ?? '';
        $message = strip_tags($this->input->post('message', true) ?? '');
        if ($type == 'contacts' || $type == 'leads') {
            if ($type == 'contacts') {
                $user_id = $this->clients_model->get_contact($type_id)->userid;
            }
            $message_data = wbParseMessageText([
                'rel_type' => $type,
                'rel_id' => $type_id,
                'reply_text' => $message,
                'userid' => $user_id ?? null,
            ]);
        }
        $message = $message_data['reply_text'] ?? $message;
        $ref_message_id = $this->input->post('ref_message_id', true);
        $imageAttachment = $_FILES['image'] ?? null;
        $videoAttachment = $_FILES['video'] ?? null;
        $documentAttachment = $_FILES['document'] ?? null;
        $audioAttachment = $_FILES['audio'] ?? null;
        $latitude = $this->input->post('latitude', true);
        $longitude = $this->input->post('longitude', true);
        $locationName = $this->input->post('locationName', true) ?? 'Shared location';
        $locationAddress = $this->input->post('locationAddress', true) ?? '';

        // Initialize message data
        $message_data = [];

        // Check if there is only text message or only attachment
        if (!empty($message)) {
            // Send only text message
            $message_data[] = [
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $message,
                ],
            ];
        }

        // Handle audio attachment
        if (!empty($audioAttachment)) {
            $audio_url = $this->handle_attachment_upload($audioAttachment);
            $message_data[] = [
                'type' => 'audio',
                'audio' => [
                    'url' => WHATSBOT_MODULE_UPLOAD_URL . $audio_url,  // Prepend base URL to audio file name
                ],
            ];
        }

        // Handle image attachment
        if (!empty($imageAttachment)) {
            $image_url = $this->handle_attachment_upload($imageAttachment);
            $message_data[] = [
                'type' => 'image',
                'image' => [
                    'url' => WHATSBOT_MODULE_UPLOAD_URL . $image_url,  // Prepend base URL to image file name
                ],
            ];
        }

        // Handle video attachment
        if (!empty($videoAttachment)) {
            $video_url = $this->handle_attachment_upload($videoAttachment);
            $message_data[] = [
                'type' => 'video',
                'video' => [
                    'url' => WHATSBOT_MODULE_UPLOAD_URL . $video_url,  // Prepend base URL to video file name
                ],
            ];
        }

        // Handle document attachment
        if (!empty($documentAttachment)) {
            $document_url = $this->handle_attachment_upload($documentAttachment);

            $message_data[] = [
                'type' => 'document',
                'document' => [
                    'url' => WHATSBOT_MODULE_UPLOAD_URL . $document_url,  // Prepend base URL to document file name
                ],
            ];
        }

        if (!empty($latitude) && !empty($longitude)) {
            $mapUrl = "https://maps.google.com/maps?q={$latitude},{$longitude}";
            $message = $latitude . ',' . $longitude;
            $message_data[] = [
                'type' => 'location',
                'location' => [
                    'name' => $locationName,
                    'address' => $locationAddress,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'url' => $mapUrl
                ]
            ];
        }

        $whatsapp_cloud_api = new WhatsAppCloudApi([
            'from_phone_number_id' => $existing_interaction[0]['wa_no_id'],
            'access_token' => get_option('wac_access_token'),
            'business_id' => get_option('wac_business_account_id'),
        ]);

        if(!empty($last_message) && !empty($last_message->message_id)){
            $this->sendTypingIndicator($last_message->message_id);
        }

        if (!empty($ref_message_id)) {
            $whatsapp_cloud_api->replyTo($ref_message_id);
        }

        $messageId = null;

        foreach ($message_data as $data) {
            switch ($data['type']) {
                case 'text':
                    $response = $whatsapp_cloud_api->sendTextMessage($to, $data['text']['body']);
                    break;
                case 'audio':
                    $response = $whatsapp_cloud_api->sendAudio($to, new LinkID($data['audio']['url']));
                    break;
                case 'image':
                    $response = $whatsapp_cloud_api->sendImage($to, new LinkID($data['image']['url']));
                    break;
                case 'video':
                    $response = $whatsapp_cloud_api->sendVideo($to, new LinkID($data['video']['url']));
                    break;
                case 'document':
                    $fileName = basename($data['document']['url']);
                    $response = $whatsapp_cloud_api->sendDocument($to, new LinkID($data['document']['url']), $fileName, '');
                    break;
                case 'location':
                    $response = $whatsapp_cloud_api->sendLocation($to, (float) $longitude, (float) $latitude, $locationName, $locationAddress);
                    break;
            }

            // Decode the response JSON
            $response_data = $response->decodedBody();

            // Check if the response data contains the message ID
            if (isset($response_data['messages'][0]['id'])) {
                // Message sent successfully, store the message ID
                $messageId = $response_data['messages'][0]['id'];
            }
        }

        // Insert message into the database
        $interaction_id = $this->interaction_model->insert_interaction([
            'receiver_id' => $to,
            'last_message' => $message ?? ($message_data[0]['type'] ?? ''), // Ensure fallback in case message_data is not set
            'wa_no' => $existing_interaction[0]['wa_no'],
            'wa_no_id' => $existing_interaction[0]['wa_no_id'],
            'time_sent' => date('Y-m-d H:i:s'),
            'type' => $type ?? '',
            'type_id' => $type_id ?? 0,
        ]);

        foreach ($message_data as $data) {
            $new_message_id = $this->interaction_model->insert_interaction_message([
                'interaction_id' => $interaction_id,
                'sender_id' => $existing_interaction[0]['wa_no'], // Accessing object property directly
                'message' => $message,
                'message_id' => $messageId,
                'type' => $data['type'] ?? '', // Ensure fallback in case message_data['type'] is not set
                'staff_id' => get_staff_user_id() ?? null,
                'url' => isset($data[$data['type']]['url']) ? basename($data[$data['type']]['url']) : null, // Check if URL exists before accessing
                'status' => 'sent',
                'time_sent' => date('Y-m-d H:i:s'),
                'ref_message_id' => $ref_message_id ?? '',
            ]);
            if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
                $this->pusher->trigger('interactions-channel', 'new-message-event', [
                    'interaction' => $this->interaction_model->get_new_interaction_message($interaction_id, $new_message_id)
                ]);
            }
        }


        // Return success response
        echo json_encode(['success' => true]);
    }

    public function mark_interaction_as_read()
    {
        // Retrieve POST data
        $interaction_id = $_POST['interaction_id'] ?? '';

        // Validate input
        if (empty($interaction_id)) {
            echo json_encode(['error' => 'Invalid interaction ID']);

            return;
        }

        // Call the model function to mark the interaction as read
        $success = $this->interaction_model->update_message_status($interaction_id, 'read');

        // Check if the interaction was successfully marked as read
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to mark interaction as read']);
        }
    }

    /**
     * Save an internal note on a conversation (not sent to customer)
     */
    public function send_internal_note()
    {
        $interaction_id = $this->input->post('interaction_id', true);
        $note = $this->input->post('note', true);

        if (empty($interaction_id) || empty($note)) {
            echo json_encode(['status' => false, 'message' => 'Missing required fields']);
            return;
        }

        $message_id = $this->interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => 'internal',
            'message' => $note,
            'type' => 'internal_note',
            'staff_id' => get_staff_user_id(),
            'status' => 'note',
            'time_sent' => date('Y-m-d H:i:s'),
            'is_system' => 1,
        ]);

        echo json_encode([
            'status' => true,
            'message' => _l('internal_note_saved'),
            'data' => ['id' => $message_id]
        ]);
    }

    /**
     * Send emoji reaction to a message via AJAX
     */
    public function react_to_message()
    {
        $to = $this->input->post('to', true);
        $message_id = $this->input->post('message_id', true);
        $emoji = $this->input->post('emoji');
        $wa_no_id = $this->input->post('wa_no_id', true);

        if (empty($to) || empty($message_id) || empty($emoji)) {
            echo json_encode(['status' => false, 'message' => 'Missing required fields']);
            return;
        }

        $result = $this->sendReaction($to, $message_id, $emoji, $wa_no_id);
        echo json_encode($result);
    }

    public function template_category_update($changed_data)
    {
        $this->db->update(db_prefix() . 'wtc_templates', ['category' => $changed_data['new_category']], ['template_id' => $changed_data['message_template_id']]);
        $message = "Your whatsapp template {$changed_data['message_template_name']} category changed from {$changed_data['previous_category']} to {$changed_data['new_category']} for {$changed_data['message_template_language']} language";
        log_activity($message);

        $notifiedUsers = [];
        foreach (wbGetStaffMembersAllowedToViewMessageTemplates() as $staff) {
            if (
                add_notification([
                    'description' => 'message_template_category_has_been_changed',
                    'additional_data' => serialize(['template_name' => $changed_data['message_template_name'], 'from_to' => _l('from') . ' ' . $changed_data['previous_category'] . ' ' . _l('to') . ' ' . $changed_data['new_category']]),
                    'touserid' => $staff['staffid'],
                    'fromuserid' => 1,
                    'link' => 'whatsapp_api',
                ])
            ) {
                $notifiedUsers[] = $staff['staffid'];
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function store_bot_messages($data, $interactionId, $rel_data, $type, $response)
    {
        $data['sending_count'] = (int) $data['sending_count'] + 1;
        if ('template_bot' == $type && !empty($response['status'])) {
            $header = wbParseText($data['rel_type'], 'header', $data);
            $body = wbParseText($data['rel_type'], 'body', $data);
            $footer = wbParseText($data['rel_type'], 'footer', $data);

            $buttonHtml = '';
            if (!empty(json_decode($data['buttons_data']))) {
                $buttons = json_decode($data['buttons_data']);
                $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
                foreach ($buttons->buttons as $key => $value) {
                    $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $value->text . '</button>';
                }
                $buttonHtml .= '</div>';
            }

            $header_data = '';
            if ($data['header_data_format'] == 'IMAGE' && is_image(get_upload_path_by_type('template') . $data['filename'])) {
                $header_data = '<a href="' . base_url(get_upload_path_by_type('template') . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('template') . $data['filename']) . '" class="img-responsive img-rounded" style="object-fit: cover;"></img></a>';
            } elseif ($data['header_data_format'] == 'TEXT' || $data['header_data_format'] == '') {
                $header_data = "<span class='tw-mb-3 bold'>" . nl2br(wbDecodeWhatsAppSigns($header ?? '')) . "</span>";
            } elseif ($data['header_data_format'] == 'DOCUMENT') {
                $header_data = '<a href="' . base_url(get_upload_path_by_type('template') . $data['filename']) . '" target="_blank" class="btn btn-default tw-w-full">' . _l('document') . '</a>';
            } elseif ($data['header_data_format'] == 'VIDEO') {
                $header_data = '<video src="'  . base_url(get_upload_path_by_type('template') . $data['filename']) . '" controls class="rounded-lg"></video>';
            }

            $this->bots_model->update_sending_count(db_prefix() . 'wtc_campaigns', $data['sending_count'], ['id' => $data['campaign_table_id']]);

            // Prepare the data for chat message
            return [
                'interaction_id' => $interactionId,
                'sender_id' => $this->getDefaultPhoneNumber(),
                'url' => null,
                'message' => "
                            $header_data
                            <p>" . nl2br(wbDecodeWhatsAppSigns($body)) . "</p>
                            <span class='text-muted tw-text-xs'>" . nl2br(wbDecodeWhatsAppSigns($footer ?? '')) . "</span>
                            $buttonHtml
                        ",
                'status' => 'sent',
                'time_sent' => date('Y-m-d H:i:s'),
                'message_id' => $response['data']->messages[0]->id,
                'staff_id' => 0,
                'type' => 'text',
            ];
        }
        if ((isset($data['latitude']) && !empty($data['latitude'])) || (isset($data['longitude']) && !empty($data['latitude']))) {
            // Format the location message for display
            $latitude = $data['latitude'] ?? '';
            $longitude = $data['longitude'] ?? '';
            $mapUrl = "https://maps.google.com/maps?q={$latitude},{$longitude}";

            return [
                'interaction_id' => $interactionId,
                'sender_id' => $this->getDefaultPhoneNumber(),
                'url' => $mapUrl,
                'message' => $latitude . ',' . $longitude,
                'status' => 'sent',
                'time_sent' => date('Y-m-d H:i:s'),
                'message_id' => $response['data']->messages[0]->id,
                'staff_id' => 0,
                'type' => 'location',
            ];
        }
        $type = $type == 'flow' ? 'flow' : 'bot_files';
        $data = wbParseMessageText($data);

        $header = $data['bot_header'];
        $body = $data['reply_text'];
        $footer = $data['bot_footer'];

        $header_image = '';
        $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
        $extensions = wb_get_allowed_extension();
        $option = false;

        // Use option number to decide layout
        switch ((int) $data['option']) {
            case 2:
                if (!empty($data['button1_id'])) {
                    $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button1'] . '</button>';
                    $option = true;
                }
                if (!empty($data['button2_id'])) {
                    $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button2'] . '</button>';
                    $option = true;
                }
                if (!empty($data['button3_id'])) {
                    $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button3'] . '</button>';
                    $option = true;
                }
                break;

            case 3:
                if (!empty($data['button_name']) && !empty($data['button_url']) && filter_var($data['button_url'], FILTER_VALIDATE_URL)) {
                    $buttonHtml .= '<a href="' . $data['button_url'] . '" class="btn btn-default tw-w-full mtop10"><i class="mright5 fa-solid fa-share-from-square"></i>' . $data['button_name'] . '</a>';
                    $option = true;
                }
                break;

            case 4:
                if (!empty($data['filename'])) {
                    $file_extension = '.' . get_file_extension($data['filename']);
                    $file_url = base_url(get_upload_path_by_type($type) . $data['filename']);

                    if (in_array($file_extension, array_map('trim', explode(',', $extensions['image']['extension'])))) {
                        $header_image = '<a href="' . $file_url . '" data-lightbox="image-group"><img src="' . $file_url . '" class="img-responsive img-rounded" style="width: 300px"></a>';
                    } elseif (in_array($file_extension, array_map('trim', explode(',', $extensions['document']['extension'])))) {
                        $header_image = '<a href="' . $file_url . '" target="_blank" class="btn btn-default tw-w-full">' . _l('document') . '</a>';
                    } elseif (in_array($file_extension, array_map('trim', explode(',', $extensions['video']['extension'])))) {
                        $header_image = '<video src="' . $file_url . '" controls class="rounded-lg max-w-xs max-h-28"></video>';
                    } elseif (in_array($file_extension, array_map('trim', explode(',', $extensions['audio']['extension'])))) {
                        $header_image = '<audio controls class="w-[250px]"><source src="' . $file_url . '" type="audio/mpeg"></audio>';
                    }
                }
                break;

            case 5:
                $json = $data['sections'];
                $option_list = json_decode($json);
                if ($option_list && !empty($option_list->sections)) {
                    $sections = [];
                    foreach ($option_list->sections as $section) {
                        $rows = [];
                        if (isset($section->text) && is_array($section->text)) {
                            foreach ($section->text as $text) {
                                $rows[] = '<i class="fa fa-check-circle"></i> ' . htmlspecialchars($text);
                            }
                        }

                        if (!empty($rows)) {
                            $sections[] = [
                                'title' => htmlspecialchars($section->section ?? ''),
                                'items' => $rows
                            ];
                        }
                    }

                    // Optionally render sections (if needed)
                    if (!empty($sections)) {
                        foreach ($sections as $sec) {
                            $buttonHtml .= "<div class='tw-mb-2'>";
                            if (!empty($sec['title'])) {
                                $buttonHtml .= "<strong>{$sec['title']}</strong><br>";
                            }
                            foreach ($sec['items'] as $item) {
                                $buttonHtml .= "{$item}<br>";
                            }
                            $buttonHtml .= "</div>";
                        }
                        // Add action button
                        if (!empty($option_list->action)) {
                            $buttonHtml .= "<button class='btn btn-default tw-w-full'>{$option_list->action}</button>";
                        }
                    }
                }
                break;

            default:
                break;
        }

        $buttonHtml .= '</div>';

        // Update sending count
        $this->bots_model->update_sending_count(db_prefix() . 'wtc_bot', $data['sending_count'], ['id' => $data['id']]);

        return [
            'interaction_id' => $interactionId,
            'sender_id' => $this->getDefaultPhoneNumber(),
            'url' => null,
            'message' => $header_image . "
        <span class='tw-mb-3 bold'>" . nl2br(wbDecodeWhatsAppSigns($header ?? '')) . "</span>
        <p>" . nl2br(wbDecodeWhatsAppSigns($body)) . "</p>
        <span class='text-muted tw-text-xs'>" . nl2br(wbDecodeWhatsAppSigns($footer ?? '')) . "</span> $buttonHtml ",
            'status' => 'sent',
            'time_sent' => date('Y-m-d H:i:s'),
            'message_id' => $response['data']->messages[0]->id,
            'staff_id' => 0,
            'type' => 'text',
        ];
    }
}
