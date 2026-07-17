<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Bulk_campaigns_model extends App_Model {
    use modules\whatsbot\traits\Whatsapp;

    public function __construct() {
        parent::__construct();
    }

    public function prepare_merge_field($data) {
        $merge_field = [];
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $key => $value) {
                $merge_field[] = [
                    'key' => $value,
                    'value' => '{' . $value . '}'
                ];
            }
        }
        $data['error_note'] = '';
        if (isset($data['json_file_path'])) {
            $data['error_note'] = '<h5>' . _l('note') . ' : </h5>' . _l('out_of_the') . ' ' . $data['total'] . ' ' . _l('records_in_your_csv_file') . ' ' . $data['valid'] . ' ' . _l('valid_the_campaign_can_be_sent') . ' ' . $data['valid'] . ' ' . _l('users');
        }
        unset($data['fields']);
        $data['merge_field'] = $merge_field;
        return $data;
    }

    public function send($post_data) {
        $template = wb_get_whatsapp_template($post_data['bulk_template_id']);
        $post_data['header_params'] = json_encode($post_data['header_params'] ?? []);
        $post_data['body_params'] = json_encode($post_data['body_params'] ?? []);
        $post_data['footer_params'] = json_encode($post_data['footer_params'] ?? []);
        $post_data = array_merge($post_data, $template);

        $jsonData = file_get_contents($post_data['json_file_path']);
        $post_data['filename'] = wb_handle_campaign_upload('', 'csv');
        $campaignData = json_decode($jsonData, true);
        $response = [];
        foreach ($campaignData as $campaign) {
            $data = array_merge($campaign, $post_data);
            $result = $this->sendBulkCampaign($data['Phoneno'], $data, $campaign, $post_data['sender_phone']);
            array_push($response, $result);
        }

        $valid = count(array_filter($response, function ($item) {
            return $item['responseCode'] === 200;
        }));

        return [
            'type' => ($valid != 0) ? 'success' : 'danger',
            'message' => ($valid != 0) ? _l('total_send_campaign_list', $valid) : _l('please_add_valid_number_in_csv_file')
        ];
    }
}
