<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Whatsbot_model extends App_Model {
    use modules\whatsbot\traits\Whatsapp;
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    protected $pusher;

    public function __construct() {
        parent::__construct();
        $this->load->model(WHATSBOT_MODULE . '/interaction_model');

        if (!empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
            $this->pusher = new Pusher\Pusher(
                get_option('pusher_app_key'),
                get_option('pusher_app_secret'),
                get_option('pusher_app_id'),
                ['cluster' => get_option('pusher_cluster') ?? '']
            );
        }
    }

    public function load_templates($accessToken = '', $accountId = '')
    {
        $templates = $this->loadTemplatesFromWhatsApp($accessToken, $accountId);

        // If there is any error from API, return an error message
        if (!$templates['status']) {
            return [
                'success' => false,
                'type' => 'danger',
                'message' => $templates['message'],
            ];
        }

        $data = $templates['data'];
        $insertData = [];

        foreach ($data as $key => $templateData) {
            $components = array_column($templateData->components, null, 'type');

            $insertData[$key] = [
                'template_id'           => $templateData->id,
                'template_name'         => $templateData->name,
                'language'              => $templateData->language,
                'status'                => $templateData->status,
                'category'              => $templateData->category,
                'header_data_format'    => $components['HEADER']->format ?? '',
                'header_data_text'      => $components['HEADER']->text ?? null,
                'header_params_count'   => preg_match_all('/{{(.*?)}}/i', $components['HEADER']->text ?? '', $matches),
                'body_data'             => $components['BODY']->text ?? null,
                'body_params_count'     => preg_match_all('/{{(.*?)}}/i', $components['BODY']->text ?? '', $matches),
                'footer_data'           => $components['FOOTER']->text ?? null,
                'footer_params_count'   => preg_match_all('/{{(.*?)}}/i', $components['FOOTER']->text ?? '', $matches),
                'buttons_data'          => json_encode($components['BUTTONS'] ?? []),
            ];
        }

        // Get template IDs from the new API response
        $apiTemplateIds = array_column($insertData, 'template_id');

        // Fetch all existing templates from the DB
        $existingTemplates = $this->db->get(db_prefix() . 'wtc_templates')->result_array();

        // Map existing templates by template_id
        $existingTemplatesMap = [];
        foreach ($existingTemplates as $template) {
            $existingTemplatesMap[$template['template_id']] = $template;
        }

        // Determine which templates to insert/update and which to delete
        $newTemplates = [];
        $changedStatusTemplates = [];
        $allDbTemplateIds = array_column($existingTemplates, 'template_id');

        foreach ($insertData as $template) {
            $templateId = $template['template_id'];

            if (!isset($existingTemplatesMap[$templateId])) {
                $newTemplates[] = $template;
            } elseif ($existingTemplatesMap[$templateId]['status'] !== $template['status']) {
                $changedStatusTemplates[] = $templateId;
                $newTemplates[] = $template;
            }
        }

        // Identify templates to delete that are no longer present in API
        $removedTemplateIds = array_diff($allDbTemplateIds, $apiTemplateIds);

        // Delete templates with changed status
        if (!empty($changedStatusTemplates)) {
            $this->db->where_in('template_id', $changedStatusTemplates)->delete(db_prefix() . 'wtc_templates');
        }

        // Delete templates no longer in the API
        if (!empty($removedTemplateIds)) {
            $this->db->where_in('template_id', $removedTemplateIds)->delete(db_prefix() . 'wtc_templates');
        }

        // Insert new templates
        if (!empty($newTemplates)) {
            $this->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
            $this->db->insert_batch(db_prefix() . 'wtc_templates', $newTemplates);
        }


        return ['success' => true];
    }


    public function getContactData($contactNumber, $name) {
        $contact = $this->db->get_where(db_prefix() . 'contacts', ['phonenumber' => $contactNumber])->row();
        if (!empty($contact)) {
            $contact->rel_type = 'contacts';
            $contact->name = $contact->firstname . ' ' . $contact->lastname;
            return $contact;
        }

        $lead = $this->db->get_where(db_prefix() . 'leads', ['phonenumber' => $contactNumber])->row();
        if (!empty($lead)) {
            $lead->rel_type = 'leads';
            return $lead;
        }

        $leadId = hooks()->apply_filters('ctl_auto_lead_creation', $contactNumber, $name);

        if (!empty($leadId)) {
            $lead = $this->db->get_where(db_prefix() . 'leads', ['id' => $leadId])->row();
            $lead->rel_type = 'leads';
            return $lead;
        }

        return false;
    }

    public function updateStatus($status_data) {
        foreach ($status_data as $status) {
            $stat = is_array($status) ? $status['status'] : $status->status;
            $id = is_array($status) ? $status['id'] : $status->id;
            $this->db->update(db_prefix() . 'wtc_campaign_data', ['message_status' => $stat], ['whatsapp_id' => $id]);
        }
    }

    public function send_campaign($scheduled_data) {
        $logBatch = $chatMessage = [];

        foreach ($scheduled_data as $data) {
            switch ($data['rel_type']) {
                case 'leads':
                    $this->db->where('id', $data['rel_id']);
                    $rel_data = $this->db->get(db_prefix() . 'leads')->row();
                    $interactionId = wbGetInteractionId($data, 'leads', $rel_data->id, $rel_data->name, $rel_data->phonenumber, $this->getDefaultPhoneNumber());
                    break;

                case 'contacts':
                    $this->db->where('id', $data['rel_id']);
                    $rel_data = $this->db->get(db_prefix() . 'contacts')->row();
                    $data['id'] = $rel_data->id;
                    $data['userid'] = $rel_data->userid;
                    $interactionId = wbGetInteractionId($data, 'contacts', $data['id'], $rel_data->firstname . ' ' . $rel_data->lastname, $rel_data->phonenumber, $this->getDefaultPhoneNumber());
                    break;
            }
            if($rel_data->is_opted_out){
                $log_data['response_code'] = 401;
                $log_data['category'] = 'campaign';
                $log_data['category_id'] = $data['campaign_id'] ?? $data['campaign_table_id'] ?? "";
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['category_params'] = json_encode(['templateId' => $data['template_id'], 'message' => "User has opted-out for campaign" ?? '']);
                $log_data['response_data'] = "";
                $log_data['raw_data'] = "";
                $logBatch[] = $log_data;
            }

            if(!$rel_data->is_opted_out){
                $response = $this->sendTemplate($rel_data->phonenumber, $data, 'campaign', $data['sender_phone']);

                $logBatch[] = $response['log_data'];

                if (!empty($response['status'])) {
                    $header = wbParseText($data['rel_type'], 'header', $data);
                    $body = wbParseText($data['rel_type'], 'body', $data);
                    $footer = wbParseText($data['rel_type'], 'footer', $data);

                    $header_data = '';
                    if ($data['header_data_format'] == 'IMAGE') {
                        $header_data = '<a href="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" class="img-responsive img-rounded" style="width: 300px"></img></a>';
                    } elseif ($data['header_data_format'] == 'TEXT' || $data['header_data_format'] == '') {
                        $header_data = "<span class='tw-mb-3 bold'>" . nl2br(wbDecodeWhatsAppSigns($header ?? '')) . "</span>";
                    } elseif ($data['header_data_format'] == 'DOCUMENT') {
                        $header_data = '<a href="' . base_url(get_upload_path_by_type('campaign') . $data['filename']) . '" target="_blank" class="btn btn-default tw-w-full">' . _l('document') . '</a>';
                    } elseif ($data['header_data_format'] == 'VIDEO') {
                        $header_data = '<video src="' . base_url(get_upload_path_by_type('campaign') . $data['filename']) . '" controls class="rounded-lg"></video>';
                    }

                    $buttonHtml = '';
                    if (!empty(json_decode($data['buttons_data']))) {
                        $buttons = json_decode($data['buttons_data']);
                        $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
                        foreach ($buttons->buttons as $key => $value) {
                            $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $value->text . '</button>';
                        }
                        $buttonHtml .= '</div>';
                    }

                    // Prepare the data for chat message
                    $chatMessage[] = [
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


                $update_data['status'] = (1 == $response['status']) ? 2 : $response['status'];
                $update_data['whatsapp_id'] = ($response['status']) ? reset($response['data']->messages)->id : null;
                $update_data['response_message'] = $response['message'] ?? '';
                $this->db->update(db_prefix() . 'wtc_campaign_data', $update_data, ['id' => $data['campaign_data_id']]);

                $this->db->update(db_prefix() . 'wtc_campaigns', ['is_sent' => 1, 'sending_count' => $data['sending_count'] + 1, 'scheduled_send_time' => date('Y-m-d H:i:s')], ['id' => $data['campaign_id']]);
            }
        }

        // Add activity log
        $this->addWhatsbotLog($logBatch);

        // Add chat message
        $this->addChatMessage($chatMessage);

        return true;
    }

    public function addWhatsbotLog($logData) {
        if (!empty($logData)) {
            // Prepare the data for activity log
            $logsData = [
                'phone_number_id' => get_option('wac_phone_number_id'),
                'access_token' => get_option('wac_access_token'),
                'business_account_id' => get_option('wac_business_account_id'),
            ];
            $logData = array_map(function ($item) use ($logsData) {
                return array_merge($item, $logsData);
            }, $logData);
            return $this->db->insert_batch(db_prefix() . 'wtc_activity_log', $logData);
        }
        return false;
    }

    public function addChatMessage($chatMessage) {
        if (!empty($chatMessage)) {
            $affected_rows = 0;
            for ($i = 0; $i < count($chatMessage); $i++) {
                $message_id = $this->interaction_model->insert_interaction_message($chatMessage[$i]);
                if (!empty($message_id) && !empty(get_option('pusher_app_key')) || !empty(get_option('pusher_app_secret')) || !empty(get_option('pusher_app_id'))) {
                    $this->pusher->trigger('interactions-channel', 'new-message-event', [
                        'interaction' => $this->interaction_model->get_new_interaction_message($chatMessage[$i]['interaction_id'], $message_id)
                    ]);
                    $affected_rows++;
                }
            }
            return $affected_rows;
        }
    }

    public function getWhatsappLogDetails($id) {
        return $this->db->get_where(db_prefix() . 'wtc_activity_log', ['id' => $id])->row();
    }

    public function delete_log($id) {
        return $this->db->delete(db_prefix() . 'wtc_activity_log', ['id' => $id]);
    }

    public function delete_chat($id) {
        return $this->db->delete(db_prefix() . 'wtc_interactions', ['id' => $id]);
    }

    /**
     * Connects to the OpenAI API and updates the stored API key.
     * Also lists available models and returns the response.
     *
     * @return void Outputs the JSON-encoded response.
     */
    public function connectAi($key) {
        update_option('wb_open_ai_key', $key, 0);
        return $this->listModel();
    }

    public function load_flows($accessToken = '', $accountId = '')
    {
        $flows = $this->loadFlowsFromWhatsApp($accessToken, $accountId);

        // if there is any error from api then display appropriate message
        if (!$flows['status']) {
            return [
                'success' => false,
                'type' => 'danger',
                'message' => $flows['message'],
            ];
        }
        $data = $flows['data'];
        $insertData = [];

        foreach ($data as $key => $flowData) {
            $insertData[$key]['flow_id'] = $flowData->id;
            $insertData[$key]['flow_name'] = $flowData->name;

            $insertData[$key]['status'] = $flowData->status;
            $insertData[$key]['flow_json'] = $flowData->flow_json;
            $insertData[$key]['category'] = json_encode($flowData->categories);
        }
        $insertDataId = array_column($insertData, 'flow_id');
        $existingFlow = $this->db->where_in(array_column($insertData, 'flow_id'))->get(db_prefix() . 'wtc_flows')->result();

        $existingDataId = array_column($existingFlow, 'flow_id');
        if (!empty($existingDataId)) {
            $this->db->update_batch(db_prefix() . 'wtc_flows', $insertData, 'flow_id');
        }

        $newFlowId = array_diff($insertDataId, $existingDataId);
        $newFlow = array_filter($insertData, function ($val) use ($newFlowId) {
            return in_array($val['flow_id'], $newFlowId);
        });

        // No need to update flow data in db because you can't edit flow in meta dashboard
        if (!empty($newFlow)) {
            $this->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
            $this->db->insert_batch(db_prefix() . 'wtc_flows', $newFlow);
        }

        return ['success' => true];
    }

    function get_flow($flow_id = "")
    {
        if (!empty($flow_id)) {
            $this->db->where('flow_id', $flow_id);
        }
        $res = $this->db->get(db_prefix() . 'wtc_flows');
        if (!empty($flow_id)) {
            return $res->row();
        }
        return $res->result_array();
    }

    function update_flow($flow_id, $data)
    {
        $this->db->where(['flow_id' => $flow_id]);
        $this->db->set($data);
        return $this->db->update(db_prefix() . 'wtc_flows');
    }

    function insert_flow_res($data)
    {
        $this->db->insert(db_prefix() . 'wtc_flows_response', $data);
        return $this->db->insert_id();
    }

    function insert_order_res($data)
    {
        $this->db->insert(db_prefix() . 'wtc_orders_response', $data);
        return $this->db->insert_id();
    }

    function get_flow_res($flow_id = "", $res_id = "")
    {
        if (!empty($flow_id)) {
            $where = ['flow_id' => $flow_id];
        }
        if (!empty($res_id)) {
            $where = ['id' => $res_id];
        }
        $res = $this->db->get_where(db_prefix() . 'wtc_flows_response', $where);
        return empty($res_id) ? $res->result() : $res->row();
    }

    function send_flow($hook, $data, $client, $contact, $rel_data)
    {
        $contact_number = !empty($contact->phonenumber) ? $contact->phonenumber : $client->phonenumber;
        $whats_flow = getFlowForAutomation($hook, $data['status']);
        $flow_names = array_map("trim", array_column($whats_flow, 'flow_name'));
        $template_bots = $this->bots_model->getTemplateBotsbyRelType('contacts', "");

        foreach ($template_bots as $template) {
            if (in_array(str_replace('_', ' ', $template['template_name']), $flow_names)) {
                $template['rel_id'] = $client->userid;
                $template['userid'] = $contact->id;
                $template['flow_action_data'] = $rel_data;
                $response = $this->sendTemplate($contact_number, $template, 'template_bot');
            }
        }
    }

    function get_flow_responses_with_name()
    {
        return $this->db->select(db_prefix() . 'wtc_flows_response.*, ' . db_prefix() . 'wtc_flows.flow_name')
            ->join(db_prefix() . 'wtc_flows', db_prefix() . 'wtc_flows.flow_id = ' . db_prefix() . 'wtc_flows_response.flow_id', 'inner')->get(db_prefix() . 'wtc_flows_response')->result_array();
    }

    // Function to disable optout 
    function update_optout_status($data)
    {
        $table = ($data['type'] == 'contacts') ? db_prefix() . $data['type'] : db_prefix() . $data['type'];
        $this->db->update($table, ['is_opted_out' => isset($data['opted_out']) ? 1 : 0, 'opted_out_date' => (isset($data['opted_out'])) ? date("Y-m-d H:i:s") : null], ['id' => $data['id']]);

        return [
            'type' => $this->db->affected_rows() > 0 ? 'success' : 'danger',
            'message' => $this->db->affected_rows() > 0 ? _l('removed_from_opted_out') : _l('something_went_wrong')
        ];
    }

    function send_template($contact_number, $template, $type = 'campaign', $sender_phone = null)
    {
        $response = $this->sendTemplate($contact_number, $template, $type, $sender_phone);
        return $response;
    }
}
