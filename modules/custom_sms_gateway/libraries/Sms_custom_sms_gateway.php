<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

defined('BASEPATH') or exit('No direct script access allowed');

class Sms_custom_sms_gateway extends App_sms
{

    public function __construct()
    {
        parent::__construct();
        $this->add_gateway('custom_sms_gateway', [
            'name' => _l('custom_sms_gateway'),
            'info' => _l('this_is_custom_sms_gateway'),
            'options' => [
                [
                    'name' => 'url',
                    'label' => _l('url'),
                ],
                [
                    'name' => 'send_to_parameter',
                    'label' => _l('send_to_parameter_name'),
                ],
                [
                    'name' => 'message_parameter',
                    'label' => _l('message_parameter_name'),
                ],
                [
                    'name'          => 'request_method',
                    'field_type'    => 'radio',
                    'default_value' => 'post',
                    'label'         => _l('request_method'),
                    'options'       => [
                        ['label' => _l('post'), 'value' => 'POST'],
                        ['label' => _l('get'), 'value' => 'GET'],
                    ],
                ],
                [
                    'name' => 'parameter_1_key',
                    'label' => _l('parameter_1_key'),
                ],
                [
                    'name' => 'parameter_1_value',
                    'label' => _l('parameter_1_value'),
                ],
                [
                    'name' => 'parameter_2_key',
                    'label' => _l('parameter_2_key'),
                ],
                [
                    'name' => 'parameter_2_value',
                    'label' => _l('parameter_2_value'),
                ],
                [
                    'name' => 'parameter_3_key',
                    'label' => _l('parameter_3_key'),
                ],
                [
                    'name' => 'parameter_3_value',
                    'label' => _l('parameter_3_value'),
                ],
                [
                    'name' => 'parameter_4_key',
                    'label' => _l('parameter_4_key'),
                ],
                [
                    'name' => 'parameter_4_value',
                    'label' => _l('parameter_4_value'),
                ],
                [
                    'name' => 'parameter_5_key',
                    'label' => _l('parameter_5_key'),
                ],
                [
                    'name' => 'parameter_5_value',
                    'label' => _l('parameter_5_value'),
                ],
                [
                    'name' => 'parameter_6_key',
                    'label' => _l('parameter_6_key'),
                ],
                [
                    'name' => 'parameter_6_value',
                    'label' => _l('parameter_6_value'),
                ],
                [
                    'name' => 'parameter_7_key',
                    'label' => _l('parameter_7_key'),
                ],
                [
                    'name' => 'parameter_7_value',
                    'label' => _l('parameter_7_value'),
                ],
                [
                    'name' => 'parameter_8_key',
                    'label' => _l('parameter_8_key'),
                ],
                [
                    'name' => 'parameter_8_value',
                    'label' => _l('parameter_8_value'),
                ],
                [
                    'name' => 'parameter_9_key',
                    'label' => _l('parameter_9_key'),
                ],
                [
                    'name' => 'parameter_9_value',
                    'label' => _l('parameter_9_value'),
                ],
                [
                    'name' => 'parameter_10_key',
                    'label' => _l('parameter_10_key'),
                ],
                [
                    'name' => 'parameter_10_value',
                    'label' => _l('parameter_10_value'),
                ],
            ],
        ]);
    }

    public function send($number, $message): bool
    {
        $url = $this->get_option('custom_sms_gateway', 'url');
        $send_to_parameter = $this->get_option('custom_sms_gateway', 'send_to_parameter');
        $message_parameter = $this->get_option('custom_sms_gateway', 'message_parameter');
        $request_method = $this->get_option('custom_sms_gateway', 'request_method');
        $data = [
            $send_to_parameter => $number,
            $message_parameter => $message,
            $this->get_option('custom_sms_gateway', 'parameter_1_key') => $this->get_option('custom_sms_gateway', 'parameter_1_value'),
            $this->get_option('custom_sms_gateway', 'parameter_2_key') => $this->get_option('custom_sms_gateway', 'parameter_2_value'),
            $this->get_option('custom_sms_gateway', 'parameter_3_key') => $this->get_option('custom_sms_gateway', 'parameter_3_value'),
            $this->get_option('custom_sms_gateway', 'parameter_4_key') => $this->get_option('custom_sms_gateway', 'parameter_4_value'),
            $this->get_option('custom_sms_gateway', 'parameter_5_key') => $this->get_option('custom_sms_gateway', 'parameter_5_value'),
            $this->get_option('custom_sms_gateway', 'parameter_6_key') => $this->get_option('custom_sms_gateway', 'parameter_6_value'),
            $this->get_option('custom_sms_gateway', 'parameter_7_key') => $this->get_option('custom_sms_gateway', 'parameter_7_value'),
            $this->get_option('custom_sms_gateway', 'parameter_8_key') => $this->get_option('custom_sms_gateway', 'parameter_8_value'),
            $this->get_option('custom_sms_gateway', 'parameter_9_key') => $this->get_option('custom_sms_gateway', 'parameter_9_value'),
            $this->get_option('custom_sms_gateway', 'parameter_10_key') => $this->get_option('custom_sms_gateway', 'parameter_10_value'),
        ];

        try {
            $client = new Client();
            $response = $client->request($request_method, $url, [
                'form_params' => $data,
            ]);

            if ($response->getStatusCode() === 200) {
                log_activity('<strong>SMS sent via Custom Gateway to </strong><hr> Phone: ' . $number . '<br> Message: ' . $message);
                return true;
            } else {
                $this->set_error('<strong>Failed to send sms (Custom Gateway)</strong><hr> Status Code: ' . $response->getStatusCode());
                return false;
            }
        } catch (RequestException $e) {
            $this->set_error('<strong>Something went wrong</strong><hr>' . $e->getMessage());
            return false;
        }
    }
}
