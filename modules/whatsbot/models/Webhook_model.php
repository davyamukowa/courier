<?php

use modules\whatsbot\traits\Whatsapp;

defined('BASEPATH') || exit('No direct script access allowed');

class Webhook_model extends App_Model
{
    use Whatsapp;
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['whatsbot/bots_model', 'whatsbot/whatsbot_model']);
    }

    public function get($id = '')
    {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_receive_webhook_source', ['id' => $id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_receive_webhook_source')->result_array();
    }

    public function get_webhook_log($id = ''){
        $this->db->select(db_prefix().'wtc_webhook_logs.*, '.db_prefix().'wtc_receive_webhook_source.*, '.db_prefix().'wtc_receive_webhook_source.id as webhook_id');
        $this->db->from(db_prefix() . 'wtc_webhook_logs');
        $this->db->join(
            db_prefix() . 'wtc_receive_webhook_source',
            db_prefix() . 'wtc_webhook_logs.webhook_id = ' . db_prefix() . 'wtc_receive_webhook_source.id',
            'left'
        );

        if (!empty($id)) {
            $this->db->where(db_prefix() . 'wtc_webhook_logs.id', $id);
            return $this->db->get()->row_array();
        }
        return $this->db->get()->result_array();
    }

    function insert_update($post_data) {
        $update_data = collect($post_data)->filter(function($source){
            return !empty($source['id']) && collect($source)->filter()->isNotEmpty();
        })->toArray();
        $insert_data = collect($post_data)->filter(function($source){
            return empty($source['id']) && collect($source)->filter()->isNotEmpty();
        })->map(function($new_data){
             $new_data['hash'] = app_generate_hash();
            return $new_data;
        })->toArray();
        (!empty($update_data)) ? $this->db->update_batch(db_prefix() . 'wtc_receive_webhook_source', $update_data, 'id') : [];
        (!empty($insert_data)) ? $this->db->insert_batch(db_prefix() . 'wtc_receive_webhook_source', $insert_data) : [];
    }

    function delete($id) {

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wtc_receive_webhook_source');

        return $this->db->affected_rows() > 0;
    }

    public function delete_log($id){
        return $this->db->delete(db_prefix() . 'wtc_webhook_logs', ['id' => $id]);
    }

    public function getWebhookLogDetails($id){
        return $this->db->get_where(db_prefix() . 'wtc_webhook_logs', ['id' => $id])->row();
    }

    public function send_webhook_message($log)
    {
        $template_bots = $this->bots_model->getTemplateBotsbyRelType('webhooks', "", 0, $log['webhook_id']);
        $logBatch = [];

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
            $template['raw_data'] = $log['payload'];
            $template['phone_number'] = parse_json_value($template['phone_number'], $log['payload']);
            $response = $this->sendTemplate($template['phone_number'], $template, 'template_bot');
            $logBatch[] = $response['log_data'];
        }
        $res = $this->whatsbot_model->addWhatsbotLog($logBatch ?? []);

        return true;
    }
}
