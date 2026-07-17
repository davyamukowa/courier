<?php

defined('BASEPATH') || exit('No direct script access allowed');

use League\Csv\Reader;
use League\Csv\Statement;
use JsonPath\JsonObject;


/**
 * Get the reply type based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_reply_type')) {
    function wb_get_reply_type($id = '') {
        $reply_types = [
            [
                'id' => 1,
                'label' => _l('on_exact_match'),
            ],
            [
                'id' => 2,
                'label' => _l('when_message_contains'),
            ],
            [
                'id' => 3,
                'label' => _l('when_client_send_the_first_message'),
            ],
            [
                'id' => 4,
                'label' => _l('default_message_on_no_match'),
            ],
        ];

        if (!empty($id)) {
            $key = array_search($id, array_column($reply_types, 'id'));

            return $reply_types[$key];
        }

        return $reply_types;
    }
}


/**
 * Get the reply type based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_crm_events')) {
    function wb_get_crm_events() {
        return [
            // Invoices
            ['group' => 'Invoices', 'key' => 'invoice_created', 'name' => _l('invoice_created')],
            ['group' => 'Invoices', 'key' => 'invoice_updated', 'name' => _l('invoice_updated')],
            ['group' => 'Invoices', 'key' => 'invoice_deleted', 'name' => _l('invoice_deleted')],
            ['group' => 'Invoices', 'key' => 'invoice_sent', 'name' => _l('invoice_sent')],
            ['group' => 'Invoices', 'key' => 'invoice_status_changed', 'name' => _l('invoice_status_changed')],
            
            // Projects            
            ['group' => 'Project', 'key' => 'project_created', 'name' => _l('project_created')],
            ['group' => 'Project', 'key' => 'project_updated', 'name' => _l('project_updated')],
            ['group' => 'Project', 'key' => 'project_deleted', 'name' => _l('project_deleted')],
            
            // Payments
            ['group' => 'Payments', 'key' => 'payment_created', 'name' => _l('payment_created')],
            ['group' => 'Payments', 'key' => 'payment_updated', 'name' => _l('payment_updated')],
            ['group' => 'Payments', 'key' => 'payment_deleted', 'name' => _l('payment_deleted')],

            // Credit Notes
            ['group' => 'Credit Notes', 'key' => 'credit_note_created', 'name' => _l('credit_note_created')],
            ['group' => 'Credit Notes', 'key' => 'credit_note_updated', 'name' => _l('credit_note_updated')],
            ['group' => 'Credit Notes', 'key' => 'credit_note_deleted', 'name' => _l('credit_note_deleted')],
            ['group' => 'Credit Notes', 'key' => 'credit_note_sent', 'name' => _l('credit_note_sent')],
            ['group' => 'Credit Notes', 'key' => 'credits_applied', 'name' => _l('credits_applied')],

            // Estimates
            ['group' => 'Estimates', 'key' => 'estimate_created', 'name' => _l('estimate_created')],
            ['group' => 'Estimates', 'key' => 'estimate_updated', 'name' => _l('estimate_updated')],
            ['group' => 'Estimates', 'key' => 'estimate_deleted', 'name' => _l('estimate_deleted')],
            ['group' => 'Estimates', 'key' => 'estimate_sent', 'name' => _l('estimate_sent')],
            ['group' => 'Estimates', 'key' => 'estimate_accepted', 'name' => _l('estimate_accepted')],
            ['group' => 'Estimates', 'key' => 'estimate_declined', 'name' => _l('estimate_declined')],

            // Proposals
            ['group' => 'Proposals', 'key' => 'proposal_created', 'name' => _l('proposal_created')],
            ['group' => 'Proposals', 'key' => 'proposal_updated', 'name' => _l('proposal_updated')],
            ['group' => 'Proposals', 'key' => 'proposal_deleted', 'name' => _l('proposal_deleted')],
            ['group' => 'Proposals', 'key' => 'proposal_sent', 'name' => _l('proposal_sent')],
            ['group' => 'Proposals', 'key' => 'proposal_accepted', 'name' => _l('proposal_accepted')],
            ['group' => 'Proposals', 'key' => 'proposal_declined', 'name' => _l('proposal_declined')],

            // Leads
            ['group' => 'Leads', 'key' => 'lead_created', 'name' => _l('lead_created')],
            ['group' => 'Leads', 'key' => 'lead_deleted', 'name' => _l('lead_deleted')],
            ['group' => 'Leads', 'key' => 'lead_status_changed', 'name' => _l('lead_status_changed')],
            ['group' => 'Leads', 'key' => 'lead_converted_to_customer', 'name' => _l('lead_converted_to_customer')],
            ['group' => 'Leads', 'key' => 'lead_marked_as_lost', 'name' => _l('lead_marked_as_lost')],
            ['group' => 'Leads', 'key' => 'lead_marked_as_junk', 'name' => _l('lead_marked_as_junk')],

            // Customers
            ['group' => 'Customers', 'key' => 'customer_created', 'name' => _l('customer_created')],
            ['group' => 'Customers', 'key' => 'customer_deleted', 'name' => _l('customer_deleted')],
            ['group' => 'Customers', 'key' => 'contact_created', 'name' => _l('contact_created')],
            ['group' => 'Customers', 'key' => 'contact_updated', 'name' => _l('contact_updated')],
            ['group' => 'Customers', 'key' => 'contact_deleted', 'name' => _l('contact_deleted')],

            // Contracts
            ['group' => 'Contracts', 'key' => 'contract_created', 'name' => _l('contract_created')],
            ['group' => 'Contracts', 'key' => 'contract_updated', 'name' => _l('contract_updated')],
            ['group' => 'Contracts', 'key' => 'contract_deleted', 'name' => _l('contract_deleted')],

            // Tickets
            ['group' => 'Tickets', 'key' => 'ticket_created', 'name' => _l('ticket_created')],
            ['group' => 'Tickets', 'key' => 'ticket_deleted', 'name' => _l('ticket_deleted')],
            ['group' => 'Tickets', 'key' => 'ticket_status_changed', 'name' => _l('ticket_status_changed')],
        ];
    }
}

/**
 * Get the reply type based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_webhooks')) {
    function wb_get_webhooks() {
        return get_instance()->db->get(db_prefix() . 'wtc_receive_webhook_source')->result_array();
    }
}

/**
 * Get WhatsApp template based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_whatsapp_template')) {
    function wb_get_whatsapp_template($id = '') {
        get_instance()->db->where_in('header_data_format', ['', 'TEXT', 'IMAGE', 'DOCUMENT', 'VIDEO']);
        if (is_numeric($id)) {
            return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix() . 'wtc_templates', ['id' => $id, 'status' => 'APPROVED'])->row_array();
        }

        return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix() . 'wtc_templates', ['status' => 'APPROVED'])->result_array();
    }
}

/**
 * Get campaign data based on campaign ID
 *
 * @param string $campaign_id
 * @return array
 */
if (!function_exists('wb_get_campaign_data')) {
    function wb_get_campaign_data($campaign_id = '') {
        return get_instance()->db->get_where(db_prefix() . 'wtc_campaign_data', ['campaign_id' => $campaign_id])->result_array();
    }
}

/**
 * Check if a string is a valid JSON
 *
 * @param string $string
 * @return bool
 */
if (!function_exists('wbIsJson')) {
    function wbIsJson($string) {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}

/**
 * Get the relation types
 *
 * @return array
 */
if (!function_exists('wb_get_rel_type')) {
    function wb_get_rel_type() {
        return [
            [
                'key' => 'leads',
                'name' => _l('leads'),
            ],
            [
                'key' => 'contacts',
                'name' => _l('contacts'),
            ],
            [
                'key' => 'webhooks',
                'name' => _l('webhooks'),
            ],
            [
                'key' => 'crm_events',
                'name' => _l('crm_events'),
            ],
        ];
    }
}

if (!function_exists('wb_get_campaign_rel_type')) {
    function wb_get_campaign_rel_type() {
        return [
            [
                'key' => 'leads',
                'name' => _l('leads'),
            ],
            [
                'key' => 'contacts',
                'name' => _l('contacts'),
            ]
        ];
    }
}

/**
 * Get the relation types
 *
 * @return array
 */
if (!function_exists('wb_get_messagebot_rel_type')) {
    function wb_get_messagebot_rel_type() {
        return [
            [
                'key' => 'leads',
                'name' => _l('leads'),
            ],
            [
                'key' => 'contacts',
                'name' => _l('contacts'),
            ],
            [
                'key' => 'webhooks',
                'name' => _l('webhooks'),
            ],
        ];
    }
}

/**
 * Parse text with merge fields
 *
 * @param string $rel_type
 * @param string $type
 * @param array $data
 * @param string $return_type
 * @return string|array
 */
if (!function_exists('wbParseText')) {
    function wbParseText($rel_type, $type, $data, $return_type = 'text') {
        $rel_type = ('contacts' == $rel_type || 'crm_events' == $rel_type) ? 'client' : $rel_type;
        $CI = get_instance();
        $merge_fields = [];
        $parse_data = [];
        if($rel_type != "webhooks") {
            $CI->load->library('merge_fields/app_merge_fields');
            $merge_fields = $CI->app_merge_fields->format_feature(
                $rel_type . '_merge_fields',
                $data['userid'] ?? $data['rel_id'],
                $data['rel_id']
            );
            $other_merge_fields = $CI->app_merge_fields->format_feature('other_merge_fields');
            $merge_fields = array_merge($other_merge_fields, $merge_fields);
        }

        for ($i = 1; $i <= $data["{$type}_params_count"]; ++$i) {
            if (wbIsJson($data["{$type}_params"] ?? '[]')) {
                $parsed_text = json_decode($data["{$type}_params"] ?? '[]', true);
                $parsed_text = array_map(static function ($body) use ($merge_fields, $rel_type, $data) {
                    $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
                    if($rel_type == "webhooks"){
                        $body['value'] = parse_json_value($body['value'], $data['raw_data']);
                    }
                    foreach ($merge_fields as $key => $val) {
                        $body['value'] =
                            false !== stripos($body['value'], $key)
                            ? str_replace($key, !empty($val) ? $val : ' ', $body['value'])
                            : str_replace($key, '', $body['value']);
                    }

                    return preg_replace('/\s+/', ' ', trim($body['value']));
                }, $parsed_text);
            } else {
                $parsed_text[1] = preg_replace('/\s+/', ' ', trim($data["{$type}_params"]));
            }

            if ('text' == $return_type && !empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ', $data["{$type}_message"]);
            }
            $parse_data[] = !empty($parsed_text[$i]) ? $parsed_text[$i] : '.';
        }
        return ('text' == $return_type) ? $data["{$type}_message"] : $parse_data;
    }
}

/**
 * Parse message text with merge fields
 *
 * @param array $data
 * @return array
 */
if (!function_exists('wbParseMessageText')) {
    function wbParseMessageText($data) {
        $rel_type = $data['rel_type'];
        $rel_type = ('contacts' == $rel_type) ? 'client' : $rel_type;
        get_instance()->load->library('merge_fields/app_merge_fields');
        if (!class_exists($rel_type . '_merge_fields') && file_exists(LIBSPATH . 'merge_fields/' . ucfirst($rel_type) . '_merge_fields.php')) {
            get_instance()->load->library('merge_fields/' . ucfirst($rel_type) . '_merge_fields');
        }
        $merge_fields = [];
        if(class_exists($rel_type . '_merge_fields')){
            $merge_fields = get_instance()->app_merge_fields->format_feature(
                $rel_type . '_merge_fields',
                $data['userid'] ?? $data['rel_id'],
                $data['rel_id']
            );
        }
        $other_merge_fields = get_instance()->app_merge_fields->format_feature('other_merge_fields');
        $merge_fields = array_merge($other_merge_fields, $merge_fields);

        $data['reply_text'] = preg_replace('/@{(.*?)}/', '{$1}', $data['reply_text'] ?? '');
        if($rel_type == "webhooks"){
            $data['reply_text'] = parse_json_value($data['reply_text'], $data['raw_data']);
        }
        foreach ($merge_fields as $key => $val) {
            $data['reply_text'] =
                false !== stripos($data['reply_text'], $key)
                ? str_replace($key, !empty($val) ? $val : ' ', $data['reply_text'])
                : str_replace($key, '', $data['reply_text']);
        }

        return $data;
    }
}

/**
 * Get the campaign status based on status ID
 *
 * @param string $status_id
 * @return array
 */
if (!function_exists('wb_campaign_status')) {
    function wb_campaign_status($status_id = '') {
        $statusid = ['0', '1', '2'];
        $status['label'] = ['Failed', 'Pending', 'Success'];
        $status['label_class'] = ['label-danger', 'label-warning', 'label-success'];
        if (in_array($status_id, $statusid)) {
            $index = array_search($status_id, $statusid);
            if (false !== $index && isset($status['label'][$index])) {
                $status['label'] = $status['label'][$index];
            }
            if (false !== $index && isset($status['label_class'][$index])) {
                $status['label_class'] = $status['label_class'][$index];
            }
        } else {
            $status['label'] = _l('draft');
            $status['label_class'] = 'label-default';
        }

        return $status;
    }
}

/**
 * Get all staff members
 *
 * @return array
 */
if (!function_exists('wb_get_all_staff')) {
    function wb_get_all_staff() {
        return get_instance()->db
            ->select('staffid, email, firstname, lastname, phonenumber, datecreated, profile_image, admin, role, active, default_language, media_path_slug, is_not_staff')
            ->get(db_prefix() . 'staff')
            ->result_array();
    }
}

/**
 * Get staff members allowed to view message templates
 *
 * @return array
 */
if (!function_exists('wbGetStaffMembersAllowedToViewMessageTemplates')) {
    function wbGetStaffMembersAllowedToViewMessageTemplates() {
        get_instance()->db->join(db_prefix() . 'staff_permissions', db_prefix() . 'staff_permissions.staff_id = ' . db_prefix() . 'staff.staffid', 'LEFT');
        get_instance()->db->where([db_prefix() . 'staff_permissions.capability' => 'view', db_prefix() . 'staff_permissions.feature' => 'wtc_template']);
        get_instance()->db->or_where([db_prefix() . 'staff.admin' => '1']);

        return get_instance()->db->get(db_prefix() . 'staff')->result_array();
    }
}

/**
 * Get the interaction ID based on data, relation type, ID, name, and phone number
 *
 * @param array $data
 * @param string $relType
 * @param string $id
 * @param string $name
 * @param string $phonenumber
 * @return int
 */
if (!function_exists('wbGetInteractionId')) {
    function wbGetInteractionId($data, $relType, $id, $name, $phonenumber, $fromNumber) {
        $interaction = get_instance()->db->get_where(db_prefix() . 'wtc_interactions', ['type' => $relType, 'type_id' => $id, 'wa_no' => $fromNumber])->row();

        if (!empty($interaction)) {
            return $interaction->id;
        }

        // If data has reply type then it is message bot else it is template bot
        $message = '';
        if (!empty($data['reply_type'])) {
            $message_data = wbParseMessageText($data);
            $message = $message_data['reply_text'];
        }
        if (!empty($data['bot_type'])) {
            $message = wbParseText($data['rel_type'], 'header', $data) . ' ' . wbParseText($data['rel_type'], 'body', $data) . ' ' . wbParseText($data['rel_type'], 'footer', $data);
        }

        $interactionData = [
            'name' => $name,
            'receiver_id' => $phonenumber,
            'last_message' => $message,
            'last_msg_time' => date('Y-m-d H:i:s'),
            'wa_no' => get_option('wac_default_phone_number'),
            'wa_no_id' => get_option('wac_phone_number_id'),
            'time_sent' => date('Y-m-d H:i:s'),
            'type' => $relType,
            'type_id' => $id,
        ];

        get_instance()->db->insert(db_prefix() . 'wtc_interactions', $interactionData);

        return get_instance()->db->insert_id();
    }
}

/**
 * Decode WhatsApp signs to HTML tags
 *
 * @param string $text
 * @return string
 */
if (!function_exists('wbDecodeWhatsAppSigns')) {
    function wbDecodeWhatsAppSigns($text) {
        if (empty($text)) {
            return '';
        }
        $patterns = [
            '/\*(.*?)\*/',       // Bold
            '/_(.*?)_/',         // Italic
            '/~(.*?)~/',         // Strikethrough
            '/```(.*?)```/',      // Monospace
        ];
        $replacements = [
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<del>$1</del>',
            '<code>$1</code>',
        ];

        return preg_replace($patterns, $replacements, $text);
    }
}

if (!function_exists('wb_handle_whatsbot_upload')) {
    function wb_handle_whatsbot_upload($bot_id) {
        if (isset($_FILES['bot_file']['name'])) {
            $path = get_upload_path_by_type('bot_files');
            $tmpFilePath = $_FILES['bot_file']['tmp_name'];
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $newFileName = str_replace(" ", "_", $_FILES['bot_file']['name']);
                $filename = unique_filename($path, $newFileName);
                if (_upload_extension_allowed($filename)) {
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        get_instance()->db->update(db_prefix() . 'wtc_bot', ['filename' => $filename], ['id' => $bot_id]);
                        return $filename;
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('wb_handle_campaign_upload')) {
    function wb_handle_campaign_upload($id = '', $type = '') {
        if (isset($_FILES['image'])) {
            if (isset($_FILES['image']['name'])) {
                $path = get_upload_path_by_type($type);
                $tmpFilePath = $_FILES['image']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['image']['name']);
                    $filename = unique_filename($path, $newFileName);
                    if (_upload_extension_allowed($filename)) {
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            if (!empty($id)) {
                                get_instance()->db->update(db_prefix() . 'wtc_campaigns', ['filename' => $filename], ['id' => $id]);
                            }
                            return $filename;
                        }
                    }
                }
            }
        }

        if (isset($_FILES['document'])) {
            if (isset($_FILES['document']['name'])) {
                $path = get_upload_path_by_type($type);
                $tmpFilePath = $_FILES['document']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['document']['name']);
                    $filename = unique_filename($path, $newFileName);
                    if (_upload_extension_allowed($filename)) {
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            if (!empty($id)) {
                                get_instance()->db->update(db_prefix() . 'wtc_campaigns', ['filename' => $filename], ['id' => $id]);
                            }
                            return $filename;
                        }
                    }
                }
            }
        }

        if (isset($_FILES['video'])) {
            if (isset($_FILES['video']['name'])) {
                $path = get_upload_path_by_type($type);
                $tmpFilePath = $_FILES['video']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['video']['name']);
                    $filename = unique_filename($path, $newFileName);
                    if (_upload_extension_allowed($filename)) {
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            if (!empty($id)) {
                                get_instance()->db->update(db_prefix() . 'wtc_campaigns', ['filename' => $filename], ['id' => $id]);
                            }
                            return $filename;
                        }
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('wb_get_allowed_extension')) {
    function wb_get_allowed_extension() {
        return [
            'image' => [
                'extension' => '.jpeg, .png',
                'size' => 5
            ],
            'video' => [
                'extension' => '.mp4, .3gp',
                'size' => 16,
            ],
            'audio' => [
                'extension' => '.aac, .amr, .mp3, .m4a, .ogg',
                'size' => 16,
            ],
            'document' => [
                'extension' => '.pdf, .doc, .docx, .txt, .xls, .xlsx, .ppt, .pptx',
                'size' => 100,
            ],
            'sticker' => [
                'extension' => '.webp',
                'size' => 0.1,
            ]
        ];
    }
}

if (!function_exists('set_chat_header')) {
    function set_chat_header() {
        $module = get_instance()->app_modules->get('whatsbot');
        return [
            'chat_header'  => get_option('whatsbot_product_token'),
            'chat_footer'  => get_option('whatsbot_verification_id'),
            'chat_content' => basename($module['headers']['uri'] ?? 'whatsbot'),
        ];
    }
}

if (!function_exists('handleCsvUpload')) {
    function handleCsvUpload() {
        if (isset($_FILES['file'])) {
            if (isset($_FILES['file']['name'])) {
                $path = get_upload_path_by_type('csv');
                $tmpFilePath = $_FILES['file']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['file']['name']);
                    $filename = unique_filename($path, $newFileName);
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $json_file_name = str_replace(get_file_extension($filename), 'json', $filename);
                        $res = csvToJson($newFilePath, $path . $json_file_name);
                        @unlink($newFilePath);

                        return $res;
                    }
                }
            }
        }
    }
}

if (!function_exists('normalizePhoneNumber')) {
    function normalizePhoneNumber($phoneNumber) {
        $normalizedNumber = sprintf('%.0f', $phoneNumber);
        $normalizedNumber = preg_replace('/\D/', '', $normalizedNumber);
        ;

        return (is_numeric($normalizedNumber) && strlen($normalizedNumber) >= 10) ? $normalizedNumber : null;
    }
}

if (!function_exists('csvToJson')) {
    function csvToJson($csvFilePath, $jsonFilePath) {
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0);
        $records = (new Statement())->process($csv);

        $filteredRecords = [];
        if (!empty($records) && in_array('Phoneno', $records->getHeader())) {
            foreach ($records as $record) {
                $newPhonenumber = normalizePhoneNumber(preg_replace('/\s+/', '', $record['Phoneno']));
                $record['Phoneno'] = $newPhonenumber;
                if (!empty($record['Phoneno']) && preg_match('/^\+?[0-9]+$/', $record['Phoneno'])) {
                    $record = array_filter($record, function ($value) {
                        return !empty($value);
                    });
                    $filteredRecords[] = $record;
                }
            }

            if (!empty($filteredRecords)) {
                $jsonData = json_encode($filteredRecords, JSON_PRETTY_PRINT);
                $upload = file_put_contents($jsonFilePath, $jsonData);
                return [
                    'type' => ($upload) ? 'success' : 'danger',
                    'message' => ($upload) ? _l('csv_uploaded_successfully') : _l('something_went_wrong'),
                    'fields' => $records->getHeader(),
                    'valid' => count($filteredRecords),
                    'not_valid' => count($records) - count($filteredRecords),
                    'total' => count($records),
                    'json_file_path' => base_url($jsonFilePath),
                ];
            }
        }

        return [
            'type' => 'danger',
            'message' => (empty($records)) ? _l('phonenumber_field_is_required') : _l('please_upload_valid_csv_file'),
            'fields' => $records->getHeader(),
        ];
    }
}

if (!function_exists('wbParseCsvText')) {
    function wbParseCsvText($type, $data, $rel_data, $return_type = 'text') {
        $CI = get_instance();
        $CI->load->library('merge_fields/app_merge_fields');
        $merge_fields = array_reduce(array_keys($rel_data), function ($carry, $key) use ($rel_data) {
            $carry['{' . $key . '}'] = $rel_data[$key];
            return $carry;
        }, []);
        $parse_data = [];

        for ($i = 1; $i <= $data["{$type}_params_count"]; ++$i) {
            if (wbIsJson($data["{$type}_params"] ?? '[]')) {
                $parsed_text = json_decode($data["{$type}_params"] ?? '[]', true);
                $parsed_text = array_map(static function ($body) use ($merge_fields) {
                    $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
                    foreach ($merge_fields as $key => $val) {
                        $body['value'] =
                            false !== stripos($body['value'], $key)
                            ? str_replace($key, !empty($val) ? $val : ' ', $body['value'])
                            : str_replace($key, '', $body['value']);
                    }

                    return preg_replace('/\s+/', ' ', trim($body['value']));
                }, $parsed_text);
            } else {
                $parsed_text[1] = preg_replace('/\s+/', ' ', trim($data["{$type}_params"]));
            }

            if ('text' == $return_type && !empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ', $data["{$type}_message"]);
            }
            $parse_data[] = !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ';
        }
        return ('text' == $return_type) ? $data["{$type}_message"] : $parse_data;
    }
}

if (!function_exists('get_client_id_from_contact')) {
    function get_client_id_from_contact($id) {
        if (!empty($id)) {
            $res = get_instance()->db->get_where(db_prefix() . 'contacts', ['id' => $id])->row_array();
            return $res['userid'] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('get_valid_assistants')) {
    function get_valid_assistants() {
       get_instance()->load->model('personal_assistant_model');
        $pas = array_filter(
            array_map(
                fn($pa) => array_merge($pa, ['files' => get_instance()->personal_assistant_model->get_pa_files($pa['id'])]),
                get_instance()->personal_assistant_model->get()
            ),
            fn($pa) => !empty($pa['files'])
        );
        $pas = array_map(function($item) {
            return array_intersect_key($item, array_flip(['id', 'name']));
        }, $pas);
       return $pas;
    }
}

if (!function_exists('get_flow_name_by_flow_id')) {
    function get_flow_name_by_flow_id($flow_id) {
      if(!empty($flow_id)) {
        $flow = get_instance()->db->get_where(db_prefix().'wtc_flows', ['flow_id' => $flow_id])->row_array();
        return $flow['flow_name'] ?? '';
      }
      return '';
    }
}

function getFlowForAutomation($hook, $status) {
    get_instance()->load->model("whatsbot/whatsbot_model");
    $flows = get_instance()->whatsbot_model->get_flow();
    $flow_hook = [];
    foreach ($flows as $flow) {
        $automation = json_decode($flow['automation'] ?? '', true);
        if(!empty($automation[$hook])){
            foreach ($automation[$hook] as $status_id) {
                if($status == $status_id){
                    $flow_hook[] = $flow;
                }
            }
        }
    }
    return $flow_hook;
}

if(!function_exists('get_flow_responses')) {
    function get_flow_responses(){
        get_instance()->load->model("whatsbot/whatsbot_model");
        $flow_responses = get_instance()->whatsbot_model->get_flow_responses_with_name();

        return $flow_responses;
    }
}


if (!function_exists('wb_get_custom_labels')) {
    function wb_get_custom_labels()
    {
        return get_instance()->db->get(db_prefix() . 'wtc_custom_label')->result_array();
    }
}
if (!function_exists('is_opt_keyword_exists')) {
    function is_opt_keyword_exists($keywords)
    {
        $opt_keywords = array_merge(explode(',', get_option('opt_out_keyword')), explode(',', get_option('opt_in_keyword')));

        // Normalize keywords (trim and lowercase)
        $opt_keywords = array_map('strtolower', array_map('trim', $opt_keywords));
        $trigger_keywords = array_map('strtolower', array_map('trim', explode(',', $keywords)));
        $stop_ai_keyword = array_map('strtolower', array_map('trim', [get_option('stop_ai_assistant')]));
        $keywords = array_merge($opt_keywords, $stop_ai_keyword);

        // Check if any input keyword exists in opt keywords
        $common = array_intersect($trigger_keywords, $keywords);
        return empty($common);
    }
}

if (!function_exists('is_opt_keyword_exists_in_bot')) {
    function is_opt_keyword_exists_in_bot($opt_keywords)
    {
        $CI = &get_instance();
        $sql = "
            SELECT `trigger` FROM `" . db_prefix() . "wtc_bot`
            UNION
            SELECT `trigger` FROM `" . db_prefix() . "wtc_campaigns`";
            $query = $CI->db->query($sql);
            $keywords = array_column($query->result_array(), 'trigger');
        
        $CI->db->select('flow_data');
        $flows = array_column($CI->db->get(db_prefix() . 'wtc_bot_flow')->result_array(), 'flow_data');    

        foreach ($flows as $json) {
            $flow = json_decode($json, true);
            foreach ($flow['nodes'] ?? [] as $node) {
                if ($node['type'] === 'start') {
                    $trigger = $node['data']['output'][0]['trigger'] ?? '';
                    if ($trigger) {
                        $keywords = [...$keywords, ...array_map('trim', explode(',', $trigger))];
                    }
                }
            }
        }
        $keywords = array_filter($keywords, function($keyword) {
            return !is_null($keyword);
        });
        $keywords = implode(',', $keywords);
        // Normalize keywords (trim and lowercase)
        $opt_keywords = array_map('strtolower', array_map('trim', explode(',', $opt_keywords)));
        $trigger_keywords = array_map('strtolower', array_map('trim', explode(',', $keywords)));
        $stop_ai_keyword = array_map('strtolower', array_map('trim', [get_option('stop_ai_assistant')]));
        $keywords = array_merge($trigger_keywords, $stop_ai_keyword);
        // Check if any input keyword exists in opt keywords
        
        // Filtering empty and null values
        $opt_keywords = array_filter($opt_keywords);
        $keywords = array_filter($keywords);

        $common = array_intersect($opt_keywords, $keywords);
        
        return !empty($common);
    }
}

/**
 * Parse JSON Value using JSON PATH URL
 * 
 * @param string $string Value
 * @param string $json JSON
 */
function parse_json_value($string, $json) {
    $CI = &get_instance();
    preg_match_all('/{([^}]+)}/', $string, $matches);

    $jsonObject = new JsonObject($json);
    foreach ($matches[1] as $path) {
        $jsonPath = '$.' . $path;
        $value = $jsonObject->get($jsonPath);
        $replacement = implode(", ", $value) ?? '';
        $string = str_replace("{" . $path . "}", $replacement, $string);
    }
    return $string;
    
}

/**
 * Handle product image upload
 * 
 * @param int $product_id Product ID
 * @return string|false Filename of uploaded image or false on failure
 */
function wb_handle_product_image_upload($product_id) {
    if (!isset($_FILES['product_image']) || empty($_FILES['product_image']['name'])) {
        return false;
    }
    
    $path = get_upload_path_by_type('product_images');
    $tmpFilePath = $_FILES['product_image']['tmp_name'];
    
    if (!empty($tmpFilePath) && $tmpFilePath != '') {
        _maybe_create_upload_path($path);
        
        // Create product-specific folder
        $product_path = $path . $product_id . '/';
        _maybe_create_upload_path($product_path);
        
        $extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . $product_id . '_' . time() . '_' . uniqid() . '.' . $extension;    
        
        // Validate file type
        $allowed_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $file_extension = '.' . get_file_extension($filename);
        
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            return false;
        }
        
        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($_FILES['product_image']['size'] > $max_size) {
            return false;
        }
        
        $newFilePath = $product_path . $filename;
        
        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
            // Remove old image if exists
            $CI = &get_instance();
            $old_metadata = $CI->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $product_id])->row();
            
            if ($old_metadata && $old_metadata->image_url) {
                $old_file_path = $product_path . $old_metadata->image_url;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            
            return $filename;
        }
    }
    
    return false;
}

/**
 * Get product image URL
 * 
 * @param int $product_id Product ID
 * @param string $filename Image filename
 * @return string Image URL
 */
function wb_get_product_image_url($product_id, $filename) {
    if (empty($filename) || !file_exists(FCPATH . get_upload_path_by_type('product_images') . $product_id . '/' . $filename)) {
        return '';
    }
    
    return base_url(get_upload_path_by_type('product_images') . $product_id . '/' . $filename);
}

/**
 * Handle CRM event triggers — send WhatsApp template message based on event.
 *
 * @param string $event_type Event name (invoice_created, estimate_accepted, etc.)
 * @param int $event_id Related record ID
 * @param string $relation_type Relation type (invoice, estimate, task, payment)
 * @param array $extra_data Additional event data
 */
if (!function_exists('wb_get_template_header_upload_type')) {
    function wb_get_template_header_upload_type($template_data, $default = 'template')
    {
        return $template_data['header_media_upload_type'] ?? $default;
    }
}

if (!function_exists('wb_is_document_crm_event_template')) {
    function wb_is_document_crm_event_template($template_data, $relation_type)
    {
        $supported_relations = ['invoice', 'credit_note', 'estimate', 'proposal', 'contract'];

        return strtoupper(trim($template_data['header_data_format'] ?? '')) === 'DOCUMENT'
            && (int) ($template_data['send_crm_event_pdf'] ?? 0) === 1
            && in_array($relation_type, $supported_relations, true);
    }
}

if (!function_exists('wb_get_crm_event_document_source')) {
    function wb_get_crm_event_document_source($event_id, $relation_type, $extra_data = [])
    {
        $CI = &get_instance();
        $CI->load->helper('pdf');

        switch ($relation_type) {
            case 'invoice':
                $CI->load->model('invoices_model');
                $record = $CI->invoices_model->get($event_id);
                if (!$record) {
                    return false;
                }

                return [
                    'record' => $record,
                    'stored_prefix' => 'invoice',
                    'display_filename' => preg_replace('/[^A-Za-z0-9._-]/', '_', format_invoice_number($record->id)) . '.pdf',
                    'pdf_binary' => invoice_pdf($record)->Output(format_invoice_number($record->id) . '.pdf', 'S'),
                ];

            case 'credit_note':
                $CI->load->model('credit_notes_model');
                $record = $CI->credit_notes_model->get($event_id);
                if (!$record) {
                    return false;
                }

                return [
                    'record' => $record,
                    'stored_prefix' => 'credit_note',
                    'display_filename' => preg_replace('/[^A-Za-z0-9._-]/', '_', format_credit_note_number($record->id)) . '.pdf',
                    'pdf_binary' => credit_note_pdf($record)->Output(format_credit_note_number($record->id) . '.pdf', 'S'),
                ];

            case 'estimate':
                $CI->load->model('estimates_model');
                $estimate_id = $extra_data['estimateid'] ?? $extra_data['id'] ?? $event_id;
                $record = $CI->estimates_model->get($estimate_id);
                if (!$record) {
                    return false;
                }

                return [
                    'record' => $record,
                    'stored_prefix' => 'estimate',
                    'display_filename' => preg_replace('/[^A-Za-z0-9._-]/', '_', format_estimate_number($record->id)) . '.pdf',
                    'pdf_binary' => estimate_pdf($record)->Output(format_estimate_number($record->id) . '.pdf', 'S'),
                ];

            case 'proposal':
                $CI->load->model('proposals_model');
                $record = $CI->proposals_model->get($event_id);
                if (!$record) {
                    return false;
                }

                return [
                    'record' => $record,
                    'stored_prefix' => 'proposal',
                    'display_filename' => preg_replace('/[^A-Za-z0-9._-]/', '_', format_proposal_number($record->id)) . '.pdf',
                    'pdf_binary' => proposal_pdf($record)->Output(format_proposal_number($record->id) . '.pdf', 'S'),
                ];

            case 'contract':
                $CI->load->model('contracts_model');
                $record = $CI->contracts_model->get($event_id);
                if (!$record) {
                    return false;
                }

                $contract_label = 'contract_' . $record->id;
                return [
                    'record' => $record,
                    'stored_prefix' => 'contract',
                    'display_filename' => $contract_label . '.pdf',
                    'pdf_binary' => contract_pdf($record)->Output($contract_label . '.pdf', 'S'),
                ];
        }

        return false;
    }
}

if (!function_exists('wb_generate_crm_event_document_pdf')) {
    function wb_generate_crm_event_document_pdf($event_id, $relation_type, $extra_data = [])
    {
        try {
            $document_source = wb_get_crm_event_document_source($event_id, $relation_type, $extra_data);
        } catch (\Throwable $e) {
            log_message('error', 'WhatsBot CRM event PDF generation failed: ' . $e->getMessage());
            return false;
        }

        if (!$document_source || empty($document_source['pdf_binary'])) {
            return false;
        }

        $relative_path = get_upload_path_by_type('crm_event_docs');
        _maybe_create_upload_path($relative_path);
        $absolute_path = FCPATH . $relative_path;

        $stored_name = $document_source['stored_prefix'] . '_' . $document_source['record']->id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';

        if (file_put_contents($absolute_path . $stored_name, $document_source['pdf_binary']) === false) {
            log_message('error', 'WhatsBot CRM event PDF save failed for relation: ' . $relation_type . ' ID: ' . $event_id);
            return false;
        }

        return [
            'filename' => $stored_name,
            'display_filename' => $document_source['display_filename'],
            'upload_type' => 'crm_event_docs',
        ];
    }
}

if (!function_exists('wb_attach_crm_event_document_to_template')) {
    function wb_attach_crm_event_document_to_template($template_data, $event_id, $relation_type, $extra_data = [])
    {
        if (!wb_is_document_crm_event_template($template_data, $relation_type)) {
            return $template_data;
        }

        $document = wb_generate_crm_event_document_pdf($event_id, $relation_type, $extra_data);
        if (!$document) {
            return $template_data;
        }

        $template_data['filename'] = $document['filename'];
        $template_data['header_media_filename'] = $document['display_filename'];
        $template_data['header_media_upload_type'] = $document['upload_type'];

        return $template_data;
    }
}

function wb_handle_crm_event($event_type, $event_id, $relation_type, $extra_data = [])
{
    if (get_option('wb_enable_crm_triggers') != '1') {
        return;
    }
    $CI = &get_instance();
    $CI->load->model([
        'whatsbot/whatsbot_model',
        'whatsbot/bots_model',
        'clients_model',
        'invoices_model',
        'projects_model',
        'payments_model',
        'credit_notes_model',
        'estimates_model',
        'proposals_model',
        'leads_model',
        'contracts_model',
        'tickets_model',
    ]);

    // Fetching all template bots for the event type: crm_events
    $CI->db->select(db_prefix() . 'wtc_campaigns.id AS campaign_table_id, ' . db_prefix() . 'wtc_campaigns.*, ' . db_prefix() . 'wtc_templates.*');
    $CI->db->where("FIND_IN_SET(" . $CI->db->escape($event_type) . ", crm_events) !=", 0);
    $CI->db->where(['is_bot' => 1, 'is_bot_active' => 1]);
    $CI->db->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_campaigns.template_id = ' . db_prefix() . 'wtc_templates.id', 'left');
    $query = $CI->db->get(db_prefix() . 'wtc_campaigns');
    $template_bots = $query->result_array();
    // Determine the contact to message based on relation type
    $contact_phone = '';
    $contactType = 'contacts';
    $contact = null;
    $chatMessage = [];

    // Helper to get contact from client ID
    $get_contact_from_client = function ($clientid) use ($CI) {
        $primary_contact_id = get_primary_contact_user_id($clientid);
        if (!$primary_contact_id) return null;
        return $CI->clients_model->get_contact($primary_contact_id);
    };

    switch ($relation_type) {
        case 'invoice':
            $invoice = $CI->invoices_model->get($event_id);
            if (!$invoice) return;
            $contact = $get_contact_from_client($invoice->clientid);
            break;

        case 'project':
            $project = $CI->projects_model->get($event_id);
            if (!$project) return;
            $contact = $get_contact_from_client($project->clientid);
            break;

         case 'payment':
            $payment = $CI->payments_model->get($event_id);
            if (!$payment) return;
            $invoice = $CI->invoices_model->get($payment->invoiceid);
            if (!$invoice) return;
            $contact = $get_contact_from_client($invoice->clientid);
            break;

        case 'credit_note':
            $credit_note = $CI->credit_notes_model->get($event_id);
            if (!$credit_note) return;
            $contact = $get_contact_from_client($credit_note->clientid);
            break;

        case 'estimate':
            $eid = $extra_data['estimateid'] ?? $extra_data['id'] ?? $event_id;
            $estimate = $CI->estimates_model->get($eid);
            if (!$estimate) return;
            $contact = $get_contact_from_client($estimate->clientid);
            break;

        case 'proposal':
            $proposal = $CI->proposals_model->get($event_id);
            if (!$proposal) return;
            if ($proposal->rel_type == 'lead') {
                $contact = $CI->leads_model->get($proposal->rel_id);
                $contact_phone = $contact->phonenumber ?? '';
                $contactType = 'leads';
            } else {
                $contact = $get_contact_from_client($proposal->rel_id);
            }
            break;

        case 'lead':
            $leadid = $extra_data['lead_id'] ?? $extra_data['id'] ?? $event_id;
            $contact = $CI->leads_model->get($leadid);
            if (!$contact) return;
            $contact_phone = $contact->phonenumber ?? '';
            $contactType = 'leads';
            break;

        case 'customer':
            $customerid = $extra_data['id'] ?? $event_id;
            $contact = $get_contact_from_client($customerid);
            break;

        case 'contact':
            $contact = $CI->clients_model->get_contact($event_id);
            break;

        case 'contract':
            $contract = $CI->contracts_model->get($event_id);
            if (!$contract) return;
            $contact = $get_contact_from_client($contract->client);
            break;

        case 'ticket':
            $ticket = $CI->tickets_model->get($event_id);
            if (!$ticket) return;
            $contact = $get_contact_from_client($ticket->userid);
            break;
    }

    // Extract phone from contact object if not already set
    if (empty($contact_phone) && isset($contact) && $contact) {
        $contact_phone = $contact->phonenumber ?? '';
    }

    if (empty($contact_phone)) {
        return;
    }

    // Normalize phone number
    $contact_phone = normalizePhoneNumber($contact_phone);
    if (empty($contact_phone)) {
        return;
    }
    
    $add_messages = function ($item) {
        $item['header_message'] = $item['header_data_text'];
        $item['body_message'] = $item['body_data'];
        $item['footer_message'] = $item['footer_data'];
        return $item;
    };

    $logBatch = [];
    $template_bots = array_map($add_messages, $template_bots);
    $contactName = ($contactType == 'contacts') ? $contact->firstname . ' ' . $contact->lastname : $contact->name;
    $contact_data = $CI->whatsbot_model->getContactData($contact_phone, $contactName);
    foreach ($template_bots as $template) {
        $template['rel_id'] = $contact->id;
        if (!empty($contact->userid)) {
            $template['userid'] = $contact->userid;
        }
        $template = wb_attach_crm_event_document_to_template($template, $event_id, $relation_type, $extra_data);
        // Sending template message to contact
        $response = $CI->whatsbot_model->send_template($contact_phone, $template, 'template_bot', null);
        if ($response['status']) {
            $display_phone_number = get_option('wac_default_phone_number');
            $interactionId = wbGetInteractionId($template, $contact_data->rel_type, $contact_data->id, $contact_data->name, $contact_phone, $display_phone_number);
            $chatMessage[] = store_bot_messages($template, $interactionId, $contact_data, 'template_bot', $response);
        }
        $logBatch[] = $response['log_data'];
    }
    $CI->whatsbot_model->addWhatsbotLog($logBatch ?? []);
    $CI->whatsbot_model->addChatMessage($chatMessage);
}

/**
 * Auto-route a new conversation to a staff member based on routing settings.
 * Supports: round-robin, lead assignee, or specific department staff.
 *
 * @param int $interaction_id The interaction ID to assign
 * @param string $rel_type Contact type (leads/contacts)
 * @param int|string $rel_id Contact/Lead ID
 * @return void
 */
function wb_auto_route_conversation($interaction_id, $rel_type = '', $rel_id = '')
{
    $CI = &get_instance();
    $routing_mode = get_option('wb_auto_routing_mode'); // off, lead_assignee, round_robin, specific_staff

    if (empty($routing_mode) || $routing_mode === 'off') {
        return;
    }

    $agent_id = 0;
    $existing = $CI->db->where('id', $interaction_id)->get(db_prefix() . 'wtc_interactions')->row();
    switch ($routing_mode) {
        case 'lead_assignee':
            // Assign to the lead/contact's assigned staff
            if ($existing->type === 'leads' && !empty($existing->type_id)) {
                $lead = $CI->db->select('assigned')->get_where(db_prefix() . 'leads', ['id' => $existing->type_id])->row();
                if ($lead && !empty($lead->assigned)) {
                    $agent_id = $lead->assigned;
                }
            }
            break;

        case 'round_robin':
            // Round-robin among active staff with chat permission
            $staff = wb_get_chat_staff();
            if (!empty($staff)) {
                $last_assigned = get_option('wb_last_assigned_staff_index');
                $next_index = (intval($last_assigned) + 1) % count($staff);
                $agent_id = $staff[$next_index]['staffid'];
                update_option('wb_last_assigned_staff_index', $next_index);
            }
            break;

        case 'specific_staff':
            // Assign to a specific configured staff member
            $staff_id = get_option('wb_routing_specific_staff');
            if (!empty($staff_id)) {
                $agent_id = $staff_id;
            }
            break;
    }

    if (!empty($agent_id)) {
        $agent_data = [];
        if (!empty($existing) && !empty($existing->agent)) {
            $agent_data = json_decode($existing->agent, true) ?? [];
        }
        $agent_data['agent_id'] = [$agent_id];

        $CI->db->update(db_prefix() . 'wtc_interactions', ['agent' => json_encode($agent_data)], ['id' => $interaction_id]);
    }
}

/**
 * Get staff members who have chat permission.
 *
 * @return array Staff members with chat view permission
 */
function wb_get_chat_staff()
{
    $all_staff = get_instance()->db->get_where(db_prefix().'staff', ['active'=>1])->result_array();
    $chat_staff = [];
    foreach ($all_staff as $s) {
        if (staff_can('view', 'wtc_chat', $s['staffid']) || $s['admin'] == 1) {
            $chat_staff[] = $s;
        }
    }
    return $chat_staff;
}

if(!function_exists('store_bot_messages')) {
    function store_bot_messages($data, $interactionId, $rel_data, $type, $response)
    {
        $CI = &get_instance();
        $CI->load->model(['bots_model']);
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
            $header_upload_type = wb_get_template_header_upload_type($data);
            $header_media_path = get_upload_path_by_type($header_upload_type) . $data['filename'];
            if ($data['header_data_format'] == 'IMAGE' && is_image($header_media_path)) {
                $header_data = '<a href="' . base_url($header_media_path) . '" data-lightbox="image-group"><img src="' . base_url($header_media_path) . '" class="img-responsive img-rounded" style="object-fit: cover;"></img></a>';
            } elseif ($data['header_data_format'] == 'TEXT' || $data['header_data_format'] == '') {
                $header_data = "<span class='tw-mb-3 bold'>" . nl2br(wbDecodeWhatsAppSigns($header ?? '')) . "</span>";
            } elseif ($data['header_data_format'] == 'DOCUMENT') {
                $header_data = '<a href="' . base_url($header_media_path) . '" target="_blank" class="btn btn-default tw-w-full">' . _l('document') . '</a>';
            } elseif ($data['header_data_format'] == 'VIDEO') {
                $header_data = '<video src="'  . base_url($header_media_path) . '" controls class="rounded-lg"></video>';
            }

            $CI->bots_model->update_sending_count(db_prefix() . 'wtc_campaigns', $data['sending_count'], ['id' => $data['campaign_table_id']]);

            // Prepare the data for chat message
            return [
                'interaction_id' => $interactionId,
                'sender_id' => get_option('wac_default_phone_number'),
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
                'sender_id' => get_option('wac_default_phone_number'),
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
                    $buttonHtml .= '<a href="' . $data['button_url'] . '" class="btn btn-default tw-w-full mtop10"><i class="mright5 fa-solid fa-share-from-square"></i>' . $data['button_name'] . '</a><br>';
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
        $CI->bots_model->update_sending_count(db_prefix() . 'wtc_bot', $data['sending_count'], ['id' => $data['id']]);

        return [
            'interaction_id' => $interactionId,
            'sender_id' => get_option('wac_default_phone_number'),
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
