<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Get_webhook extends ClientsController {

    use modules\whatsbot\traits\Whatsapp;

    public function __construct() {
        parent::__construct();
        $this->load->model(['whatsbot_model', 'bots_model', 'interaction_model']);
    }

    public function index($id, $hash) {

        $this->load->model("whatsbot/webhook_model");
        $source = $this->webhook_model->get($id);
        if (!$source || ($source['hash'] != $hash)) {
            http_response_code(404);
            echo 'Webhook verification failed.';
            return;
        }
        $webhook_secret = $source['secret'];
        $raw_body = file_get_contents('php://input');
        $decoded = json_decode($raw_body, true);
        if(empty($source['response_json']) && json_last_error() === JSON_ERROR_NONE && (is_object($decoded) || is_array($decoded))){
            $this->webhook_model->insert_update([["id" => $source['id'], "response_json" => $raw_body]]);
        }

        // For WooCommarce Webhook: Start
        $received_signature = $this->input->get_request_header('x-wc-webhook-signature');
        $calculated_signature = base64_encode(hash_hmac('sha256', $raw_body, $webhook_secret, true));
        if(!empty($received_signature) && !hash_equals($calculated_signature, $received_signature)){
            file_put_contents(FCPATH."res.txt", "Unauthorized - Header Missing \n", FILE_APPEND);

            http_response_code(401);
            echo 'Webhook verification failed.';
            return;
        }
        // For WooCommarce Webhook: End
        // For shopify Webhook: Start
        $received_signature = $this->input->get_request_header('x-shopify-hmac-sha256');
        $calculated_signature = base64_encode(hash_hmac('sha256', $raw_body, $webhook_secret, true));
        if(!empty($received_signature) && !hash_equals($calculated_signature, $received_signature)){
            file_put_contents(FCPATH."res.txt", "Unauthorized - Header Missing \n", FILE_APPEND);

            http_response_code(401);
            echo 'Webhook verification failed.';
            return;
        }
        // For shopify Webhook: End
        /*
        // For Other assuming in $_GET: Start
        $received_signature = $this->input->get('secret');
        if(empty($received_signature) || $received_signature != $webhook_secret){
            file_put_contents(FCPATH."res.txt", "Unauthorized - Header Missing \n", FILE_APPEND);

            http_response_code(401);
            echo 'Webhook verification failed.';
            return;
        }
        // For Other assuming in $_GET: End
        */

        $data = json_decode($raw_body, true);

        http_response_code(200);

        $this->db->insert(db_prefix() . 'wtc_webhook_logs', [
            'webhook_id' => $id,
            'payload' => $raw_body,
            'status' => 'pending',
        ]);
    }
}
