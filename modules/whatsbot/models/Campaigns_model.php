<?php

defined('BASEPATH') || exit('No direct script access allowed');


class Campaigns_model extends App_Model

{
    use modules\whatsbot\traits\Whatsapp;
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['leads_model', 'clients_model']);
    }

    public function save($post_data)
    {
        $source = isset($post_data['source']) ? $post_data['source'] : '';
        $status = isset($post_data['status']) ? $post_data['status'] : '';
        $group = isset($post_data['groups']) ? json_encode($post_data['groups']) : '';
        $post_data['sender_phone'] = $post_data['sender_phone'];
        unset($post_data['document'], $post_data['image'], $post_data['source'], $post_data['status'], $post_data['groups'], $post_data['video']);
        $post_data['scheduled_send_time'] = isset($post_data['scheduled_send_time']) ? to_sql_date($post_data['scheduled_send_time'], true) : null;
        $post_data['send_now'] = (isset($post_data['send_now']) ? 1 : 0);
        $post_data['select_all'] = (isset($post_data['select_all']) ? 1 : 0);
        $post_data['header_params'] = json_encode($post_data['header_params'] ?? []);
        $post_data['body_params'] = json_encode($post_data['body_params'] ?? []);
        $post_data['footer_params'] = json_encode($post_data['footer_params'] ?? []);
        $post_data['rel_data'] = json_encode([
            'source' => $source,
            'status' => $status,
            'group' => $group,
        ]);
        $rel_ids = (isset($post_data['lead_ids']) && !empty($post_data['lead_ids'])) ? $post_data['lead_ids'] : ((isset($post_data['contact_ids']) && !empty($post_data['contact_ids'])) ? $post_data['contact_ids'] : '');
        $rel_type = (isset($post_data['lead_ids']) && !empty($post_data['lead_ids'])) ? 'leads' : ((isset($post_data['contact_ids']) && !empty($post_data['contact_ids'])) ? 'contacts' : '');

        unset($post_data['lead_ids'], $post_data['contact_ids']);

        if (1 == $post_data['select_all']) {
            if ('leads' == $post_data['rel_type']) {
                $leads = $this->leads_model->get();
                $rel_ids = array_column($leads, 'id');
                $rel_type = 'leads';
            } elseif ('contacts' == $post_data['rel_type']) {
                $contacts = $this->clients_model->get_contacts();
                $rel_ids = array_column($contacts, 'id');
                $rel_type = 'contacts';
            }
        }

        $insert = $update = false;
        $template = wb_get_whatsapp_template($post_data['template_id']);
        if (!empty($post_data['id'])) {
            $update = $this->db->update(db_prefix().'wtc_campaigns', $post_data, ['id' => $post_data['id']]);
            if ($update) {
                $this->db->delete(db_prefix().'wtc_campaign_data', ['campaign_id' => $post_data['id']]);
                foreach ($rel_ids as $rel_id) {
                    $this->db->insert(db_prefix().'wtc_campaign_data', [
                        'campaign_id' => $post_data['id'],
                        'rel_id' => $rel_id,
                        'rel_type' => $rel_type,
                        'header_message' => $template['header_data_text'],
                        'body_message' => $template['body_data'],
                        'footer_message' => $template['footer_data'],
                        'status' => 1,
                    ]);
                }
            }
        } else {
            $insert = $this->db->insert(db_prefix().'wtc_campaigns', $post_data);
            if ($insert) {
                $insert_id = $this->db->insert_id();
                foreach ($rel_ids as $rel_id) {
                    $this->db->insert(db_prefix().'wtc_campaign_data', [
                        'campaign_id' => $insert_id,
                        'rel_id' => $rel_id,
                        'rel_type' => $rel_type,
                        'header_message' => $template['header_data_text'],
                        'body_message' => $template['body_data'],
                        'footer_message' => $template['footer_data'],
                        'status' => 1,
                    ]);
                }
            }
        }

        $campaign_id = !empty($post_data['id']) ? $post_data['id'] : $insert_id;
        wb_handle_campaign_upload($campaign_id, 'campaign');
        if ($post_data['send_now']) {
            $scheduledData = $this->db
                ->select(db_prefix() . 'wtc_campaigns.*, ' . db_prefix() . 'wtc_templates.*, ' . db_prefix() . 'wtc_campaign_data.*, ' . db_prefix() . 'wtc_campaign_data.id as campaign_data_id')
                ->join(db_prefix() . 'wtc_campaigns', db_prefix() . 'wtc_campaigns.id = ' . db_prefix() . 'wtc_campaign_data.campaign_id', 'left')
                ->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_campaigns.template_id = ' . db_prefix() . 'wtc_templates.id', 'left')
                ->where(db_prefix() . 'wtc_campaign_data.status', 1)
                ->where(db_prefix() . 'wtc_campaigns.is_bot', 0)
                ->where(db_prefix() . 'wtc_campaign_data.campaign_id', $campaign_id)
                ->get(db_prefix() . 'wtc_campaign_data')->result_array();

            if (!empty($scheduledData)) {
                $this->load->model('whatsbot_model');
                $this->whatsbot_model->send_campaign($scheduledData);
            }
        }


        return [
            'type' => $insert || $update ? 'success' : 'danger',
            'message' => $insert ? _l('added_successfully', _l('campaign')) : ($update ? _l('updated_successfully', _l('campaign')) : _l('something_went_wrong')),
            'campaign_id' => $campaign_id,
        ];
    }


    public function initiateChat($post_data)
    {
        $ids = is_array($post_data['id']) ? $post_data['id'] : json_decode($post_data['id'], true);

        if (empty($ids)) {
            return [
                'type' => 'danger',
                'rel_type' => $post_data['chat_rel_type'],
                'message' => _l('please_select_at_least_one_lead')
            ];
        }


        // Fallback for a single ID that's not JSON
        if (!is_array($ids)) {
            $ids = [$post_data['id']];
        }


        $logBatch = [];

        foreach ($ids as $id) {
            $post_data['id'] = $id;

            switch ($post_data['chat_rel_type']) {
                case 'leads':
                    $rel_data = $this->db->get_where(db_prefix() . 'leads', ['id' => $id])->row_array();
                    break;

                case 'contacts':
                    $rel_data = $this->db->get_where(db_prefix() . 'contacts', ['id' => $id])->row_array();
                    break;
            }

            $template = $this->db->get_where(db_prefix() . 'wtc_templates', ['id' => $post_data['template_id']])->row_array();
            $filename = wb_handle_campaign_upload('', 'campaign');

            $template['rel_id'] = $id;
            $template['rel_type'] = $post_data['chat_rel_type'];
            $template['filename'] = $filename;
            $template['header_message'] = $template['header_data_text'];
            $template['body_message'] = $template['body_data'];
            $template['footer_message'] = $template['footer_data'];
            $template['body_params'] = json_encode($post_data['body_params'] ?? []);
            $template['header_params'] = json_encode($post_data['header_params'] ?? []);
            if ($template['rel_type'] == 'contacts') {
                $template['id'] = $rel_data['id'];
                $template['userid'] = $rel_data['userid'];
            }
            $response = $this->sendTemplate($rel_data['phonenumber'], $template);
            $logBatch[] = $response['log_data'];

            switch ($template['rel_type']) {
                case 'leads':
                    $rel_data = $this->db->get_where(db_prefix() . 'leads', ['id' => $template['rel_id']])->row();
                    $interactionId = wbGetInteractionId($template, 'leads', $rel_data->id, $rel_data->name, $rel_data->phonenumber, $this->getDefaultPhoneNumber());
                    break;

                case 'contacts':
                    $rel_data = $this->db->get_where(db_prefix() . 'contacts', ['id' => $template['rel_id']])->row();
                    $template['id'] = $rel_data->id;
                    $template['userid'] = $rel_data->userid;
                    $interactionId = wbGetInteractionId($template, 'contacts', $template['id'], $rel_data->firstname . ' ' . $rel_data->lastname, $rel_data->phonenumber, $this->getDefaultPhoneNumber());
                    break;
            }


            if (!empty($response['status'])) {
                $header = wbParseText($template['rel_type'], 'header', $template);
                $body = wbParseText($template['rel_type'], 'body', $template);
                $footer = wbParseText($template['rel_type'], 'footer', $template);


                $header_data = '';
                if ($template['header_data_format'] == 'IMAGE') {
                    $header_data = '<a href="' . base_url(get_upload_path_by_type('campaign') . '/' . $template['filename']) . '" data-lightbox="image-group">
                        <img src="' . base_url(get_upload_path_by_type('campaign') . '/' . $template['filename']) . '" class="img-responsive img-rounded" style="width: 300px">
                    </a>';
                } elseif ($template['header_data_format'] == 'TEXT' || $template['header_data_format'] == '') {
                    $header_data = "<span class='tw-mb-3 bold'>" . nl2br(wbDecodeWhatsAppSigns($header)) . "</span>";
                } elseif ($template['header_data_format'] == 'DOCUMENT') {
                    $header_data = '<a href="' . base_url(get_upload_path_by_type('campaign') . '/' . $template['filename']) . '" target="_blank" class="btn btn-default tw-w-full">' . _l('document') . '</a>';
                } elseif ($template['header_data_format'] == 'VIDEO') {
                    $header_data = '<video width="300" controls>
                        <source src="' . base_url(get_upload_path_by_type('campaign') . '/' . $template['filename']) . '" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>';
                }


                $buttonHtml = '';
                if (!empty(json_decode($template['buttons_data']))) {
                    $buttons = json_decode($template['buttons_data']);
                    $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
                    foreach ($buttons->buttons as $value) {
                        $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $value->text . '</button>';
                    }
                    $buttonHtml .= '</div>';
                }

                $chatMessage = [
                    'interaction_id' => $interactionId,
                    'sender_id' => $this->getDefaultPhoneNumber(),
                    'url' => null,
                    'message' => "
                            $header_data
                            <p>" . nl2br(wbDecodeWhatsAppSigns($body ?? '')) . "</p>
                            <span class='text-muted tw-text-xs'>" . nl2br(wbDecodeWhatsAppSigns($footer ?? '')) . "</span>
                            $buttonHtml
                        ",
                    'status' => 'sent',
                    'time_sent' => date('Y-m-d H:i:s'),
                    'message_id' => $response['data']->messages[0]->id,
                    'staff_id' => 0,
                    'type' => 'text',
                ];

                $this->interaction_model->insert_interaction_message($chatMessage);
            }
        }

        $this->addWhatsbotLog($logBatch);

        return [
            'type' => $response['status'] ? 'success':'danger',
            'rel_type' => $post_data['chat_rel_type'],
            'message' => $response['status']? _l('chat_initiated_successfully').': '.count($ids) : _l('something_went_wrong')
        ];
    }


    public function addWhatsbotLog($logData)
    {
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

            $logData[0]['category'] = _l('initiate_chat');
            return $this->db->insert_batch(db_prefix() . 'wtc_activity_log', $logData);
        }
        return false;
    }


    public function get($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->select(
                db_prefix() . 'wtc_campaigns.*,' .
                    db_prefix() . 'wtc_templates.template_name as template_name,' .
                    db_prefix() . 'wtc_templates.template_id as tmp_id,' .
                    db_prefix() . 'wtc_templates.header_params_count,' .
                    db_prefix() . 'wtc_templates.body_params_count,' .
                    db_prefix() . 'wtc_templates.footer_params_count,' .
                    'CONCAT("[", GROUP_CONCAT(' . db_prefix() . 'wtc_campaign_data.rel_id SEPARATOR ","), "]") as rel_ids,'
            )
                ->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_templates.id = ' . db_prefix() . 'wtc_campaigns.template_id')
                ->join(db_prefix() . 'wtc_campaign_data', db_prefix() . 'wtc_campaign_data.campaign_id = ' . db_prefix() . 'wtc_campaigns.id', 'LEFT')
                ->get_where(db_prefix() . 'wtc_campaigns', [db_prefix() . 'wtc_campaigns.id' => $id])->row_array();
        }

        return $this->db->get(db_prefix() . 'wtc_campaigns')->result_array();
    }

    public function delete($id)
    {
        $campaign = $this->get($id);
        $delete = $this->db->delete(db_prefix() . 'wtc_campaigns', ['id' => $id]);
        $delete = ($this->db->affected_rows() > 0) ? true : false;

        if ($delete) {
            $this->db->delete(db_prefix() . 'wtc_campaign_data', ['campaign_id' => $id]);

            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/campaign/' . $campaign['filename'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return [
            'message' => $delete ? _l('deleted', _l('campaign')) : _l('something_went_wrong'),
            'status' => ($delete) ? true : false
        ];
    }

    public function pause_resume_campaign($id)
    {
        $campaign = $this->get($id);
        $update = $this->db->update(db_prefix() . 'wtc_campaigns', ['pause_campaign' => (1 == $campaign['pause_campaign'] ? 0 : 1)], ['id' => $id]);

        return ['message' => $update && 1 == $campaign['pause_campaign'] ? _l('campaign_resumed') : _l('campaign_paused')];
    }

    public function delete_campaign_files($id)
    {
        $campaign = $this->get($id);
        $type = ($campaign['is_bot'] == 1) ? 'template' : 'campaign';

        $update = $this->db->update(db_prefix() . 'wtc_campaigns', ['filename' => null], ['id' => $id]);
        $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $campaign['filename'];
        if ($update && file_exists($path)) {
            unlink($path);
        }

        return [
            'message' => ($update) ? _l('attchment_deleted_successfully') : _l('something_went_wrong'),
            'url' => ($campaign['is_bot'] == 1) ? admin_url('whatsbot/bots/bot/template/' . $id) : admin_url('whatsbot/campaigns/campaign/' . $id),
        ];
    }

    public function get_contacts_where_group($group)
    {
        $customers = $this->db->select('customer_id')->distinct()->where_in('groupid', $group)->get(db_prefix() . 'customer_groups')->result_array();
        $customers = array_column($customers, 'customer_id');
        $contacts = [];
        foreach ($customers as $customer_id) {
            $data = $this->clients_model->get_contacts($customer_id);
            if (!empty($data)) {
                $contacts = array_merge($contacts, $data);
            }
        }
        return $contacts;
    }
}
