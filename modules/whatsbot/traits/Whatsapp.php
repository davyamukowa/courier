<?php

namespace modules\whatsbot\traits;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\Button;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\ButtonAction;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Row;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Section;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Action;
use Netflie\WhatsAppCloudApi\Message\CtaUrl\TitleHeader;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Message\Contact\ContactName;
use Netflie\WhatsAppCloudApi\Message\Contact\Phone;
use Netflie\WhatsAppCloudApi\Message\Contact\PhoneType;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Symfony\Component\HttpClient\HttpClient;
use WpOrg\Requests\Requests as WhatsappMarketingRequests;

use Exception;

trait Whatsapp
{
    // Facebook API endpoint for WhatsApp Cloud API
    public static $facebookAPI = 'https://graph.facebook.com/v21.0/';

    public static $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'audio/mp3' => 'mp3',
        'video/mp4' => 'mp4',
        'audio/aac' => 'aac',
        'audio/amr' => 'amr',
        'audio/ogg' => 'ogg',
        'audio/mp4' => 'mp4',
        'text/plain' => 'txt',
        'application/pdf' => 'pdf',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/msword' => 'doc',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'video/3gp' => '3gp',
        'image/webp' => 'webp',
    ];

    /**
     * Retrieve phone numbers associated with the WhatsApp Business Account
     *
     * @return array Response containing status and data or error message
     */
    public function getPhoneNumbers()
    {
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        $request = HttpClient::create()->request('GET', self::$facebookAPI . $accountId . '/phone_numbers?access_token=' . $accessToken);
        $response = json_decode($request->getContent(false));
        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        update_option('wac_phone_numbers', json_encode($response->data), 0);
        return ['status' => true, 'data' => $response->data];
    }

    /**
     * Load message templates from WhatsApp
     *
     * @return array Response containing status and data or error message
     */
    public function loadTemplatesFromWhatsApp($accessToken = '', $accountId = '')
    {
        if (empty($accessToken)) {
            $accessToken = $this->getToken();
        }
        if (empty($accountId)) {
            $accountId = $this->getAccountID();
        }

        $templates = [];

        $url = self::$facebookAPI . $accountId . '/message_templates?limit=100&access_token=' . $accessToken;

        do {
            $request = HttpClient::create()->request('GET', $url);
            $response = json_decode($request->getContent(false));

            if (property_exists($response, 'error')) {
                return ['status' => false, 'message' => $response->error->message];
            }

            if (!isset($response->data)) {
                return ['status' => false, 'message' => _l('message_templates_not_exists_note')];
            }

            $templates = array_merge($templates, $response->data);

            $url = $response->paging->next ?? null;
        } while ($url);

        return ['status' => true, 'data' => $templates];
    }

    /**
     * Load message flows from WhatsApp
     *
     * @return array Response containing status and data or error message
     */
    public function loadFlowsFromWhatsApp()
    {

        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        $request = HttpClient::create()->request('GET', self::$facebookAPI . $accountId . '/flows?access_token=' . $accessToken)->getContent();

        $response = json_decode($request);
        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        $flows_id = array_column($response->data, 'id');
        foreach ($flows_id as $key => $flow) {
            $json_request = HttpClient::create()->request('GET', self::$facebookAPI . $flow . '/assets?access_token=' . $accessToken)->getContent();
            $asset_url = json_decode($json_request)->data ?? [];
            $download_url = reset($asset_url)->download_url;
            $response->data[$key]->flow_json = file_get_contents($download_url);
        }

        return ['status' => true, 'data' => $response->data];
    }

    /**
     * Get flow preview
     *
     * @return array Response containing status and data or error message
     */
    public function getFlowPreview($flowID)
    {
        $accessToken = $this->getToken();
        $request = HttpClient::create()->request('GET', self::$facebookAPI . $flowID . '?fields=preview.invalidate(false)&access_token=' . $accessToken)->getContent();
        $response = json_decode($request);

        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        if (!property_exists($response, 'preview')) {
            return ['status' => false, 'message' => _l("something_went_wrong")];
        }

        return ['status' => true, 'preview' => $response->preview->preview_url, 'expires_at' => strtotime($response->preview->expires_at)];
    }

    /**
     * Load WhatsApp Cloud API configuration
     *
     * @param string|null $fromNumber Optional phone number to use as the sender
     * @return WhatsAppCloudApi Instance of the WhatsAppCloudApi class
     */
    public function loadConfig($fromNumber = null)
    {
        return new WhatsAppCloudApi([
            'from_phone_number_id' => (!empty($fromNumber)) ? $fromNumber : $this->getPhoneID(),
            'access_token' => $this->getToken(),
            'business_id' => $this->getAccountID(),
        ]);
    }

    /**
     * Send typing indicator before a message
     */
    public function sendTypingIndicator($message_id, $fromNumber = null)
    {
        if (get_option('enable_typing_indicator') != '1') {
            return;
        }
        try {
            $whatsapp_cloud_api = $this->loadConfig($fromNumber);
            $whatsapp_cloud_api->sendTypingIndicator($message_id);
        } catch (\Throwable $th) {
            // Silent fail — typing indicator is non-critical
        }
    }

    /**
     * Send emoji reaction to a message
     */
    public function sendReaction($to, $messageId, $emoji, $fromNumber = null)
    {
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        try {
            $result = $whatsapp_cloud_api->sendReaction($to, $messageId, $emoji);
            return ['status' => true, 'data' => json_decode($result->body())];
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            return ['status' => false, 'message' => $th->getMessage()];
        }
    }

    /**
     * Send a location request to ask the customer for their location
     */
    public function sendLocationRequest($to, $bodyText, $fromNumber = null)
    {
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        $this->sendTypingIndicator($to, $fromNumber);

        try {
            $result = $whatsapp_cloud_api->sendLocationRequest($to, $bodyText);
            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = $data;
            $rawData = json_encode($result->request()->body());
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? $th->getMessage();
            $responseCode = $th->httpStatusCode();
            $responseData = $message;
            $rawData = json_encode([]);
        }

        $log_data['response_code'] = $responseCode;
        $log_data['category'] = 'Location Request';
        $log_data['category_id'] = 0;
        $log_data['rel_type'] = '';
        $log_data['rel_id'] = ' - ';
        $log_data['category_params'] = json_encode(['message' => $message ?? '']);
        $log_data['response_data'] = !empty($responseData) ? json_encode($responseData) : '';
        $log_data['raw_data'] = $rawData;

        $batchLogData[] = $log_data;
        $this->whatsbot_model->addWhatsbotLog($batchLogData);

        return ['status' => $status, 'log_data' => $log_data ?? [], 'data' => $data ?? [], 'message' => $message ?? ''];
    }

    /**
     * Send a contact card message
     */
    public function sendContactCard($to, $contactData, $fromNumber = null)
    {
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        $this->sendTypingIndicator($to, $fromNumber);

        try {
            $name = new ContactName($contactData['full_name'], $contactData['first_name'], $contactData['last_name'] ?? '');
            $phone = new Phone($contactData['phone'], PhoneType::WORK());
            $result = $whatsapp_cloud_api->sendContact($to, $name, $phone);
            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = $data;
            $rawData = json_encode($result->request()->body());
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? $th->getMessage();
            $responseCode = $th->httpStatusCode();
            $responseData = $message;
            $rawData = json_encode([]);
        }

        $log_data['response_code'] = $responseCode;
        $log_data['category'] = 'Contact Card';
        $log_data['category_id'] = 0;
        $log_data['rel_type'] = '';
        $log_data['rel_id'] = ' - ';
        $log_data['category_params'] = json_encode(['message' => $message ?? '']);
        $log_data['response_data'] = !empty($responseData) ? json_encode($responseData) : '';
        $log_data['raw_data'] = $rawData;

        $batchLogData[] = $log_data;
        $this->whatsbot_model->addWhatsbotLog($batchLogData);

        return ['status' => $status, 'log_data' => $log_data ?? [], 'data' => $data ?? [], 'message' => $message ?? ''];
    }

    /**
     * Send a sticker message
     */
    public function sendStickerMessage($to, $stickerUrl, $fromNumber = null)
    {
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        $this->sendTypingIndicator($to, $fromNumber);

        try {
            $link_id = new LinkID($stickerUrl);
            $result = $whatsapp_cloud_api->sendSticker($to, $link_id);
            return ['status' => true, 'data' => json_decode($result->body())];
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            return ['status' => false, 'message' => $th->getMessage()];
        }
    }

    /**
     * Send a carousel template message via direct Graph API call
     *
     * @param string $to Recipient phone number
     * @param string $templateName Template name
     * @param string $language Language code (e.g. 'en_US')
     * @param array $bodyParams Body text parameters for the template
     * @param array $cards Array of card data: [['image_url' => '', 'body_params' => [], 'button_params' => []], ...]
     * @param string|null $fromNumber Optional sender phone number
     * @return array Response with status and data
     */
    public function sendCarouselTemplate($to, $templateName, $language, $bodyParams, $cards, $fromNumber = null)
    {
        $phoneId = (!empty($fromNumber)) ? $fromNumber : $this->getPhoneID();
        $this->sendTypingIndicator($to, $fromNumber);

        // Build body component parameters
        $bodyComponent = [];
        if (!empty($bodyParams)) {
            $params = [];
            foreach ($bodyParams as $param) {
                $params[] = ['type' => 'text', 'text' => $param];
            }
            $bodyComponent = ['type' => 'body', 'parameters' => $params];
        }

        // Build carousel cards
        $carouselCards = [];
        foreach ($cards as $index => $card) {
            $cardComponents = [];

            // Card header (image or video)
            if (!empty($card['image_url'])) {
                $cardComponents[] = [
                    'type' => 'header',
                    'parameters' => [['type' => 'image', 'image' => ['link' => $card['image_url']]]]
                ];
            } elseif (!empty($card['video_url'])) {
                $cardComponents[] = [
                    'type' => 'header',
                    'parameters' => [['type' => 'video', 'video' => ['link' => $card['video_url']]]]
                ];
            }

            // Card body parameters
            if (!empty($card['body_params'])) {
                $params = [];
                foreach ($card['body_params'] as $param) {
                    $params[] = ['type' => 'text', 'text' => $param];
                }
                $cardComponents[] = ['type' => 'body', 'parameters' => $params];
            }

            // Card buttons
            if (!empty($card['button_params'])) {
                foreach ($card['button_params'] as $btnIndex => $btn) {
                    $cardComponents[] = [
                        'type' => 'button',
                        'sub_type' => $btn['sub_type'] ?? 'url',
                        'index' => $btnIndex,
                        'parameters' => [['type' => 'text', 'text' => $btn['text'] ?? '']]
                    ];
                }
            }

            $carouselCards[] = [
                'card_index' => $index,
                'components' => $cardComponents,
            ];
        }

        // Build the full payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
                'components' => array_filter([
                    !empty($bodyComponent) ? $bodyComponent : null,
                    [
                        'type' => 'CAROUSEL',
                        'cards' => $carouselCards,
                    ]
                ]),
            ],
        ];

        try {
            $client = HttpClient::create();
            $url = self::$facebookAPI . $phoneId . '/messages';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getContent(false));

            if ($statusCode >= 200 && $statusCode < 300) {
                return ['status' => true, 'data' => $data, 'response_code' => $statusCode];
            } else {
                return ['status' => false, 'message' => $data->error->message ?? 'Unknown error', 'response_code' => $statusCode];
            }
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => $th->getMessage()];
        }
    }

    /**
     * Create a WhatsApp message template via API
     */
    public function createWhatsAppTemplate($templateData)
    {
        $whatsapp_cloud_api = $this->loadConfig();
        try {
            $result = $whatsapp_cloud_api->createTemplate(
                $templateData['name'],
                $templateData['category'] ?? 'MARKETING',
                $templateData['language'],
                $templateData['components']
            );
            return ['status' => true, 'data' => json_decode($result->body())];
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $responseData = method_exists($th, 'responseData') ? $th->responseData() : [];
            $error = $responseData['error'] ?? [];
            $message = $this->formatMetaTemplateErrorMessage($error, $th->getMessage());
            return [
                'status' => false,
                'message' => $message,
                'details' => $responseData,
                'raw_response' => method_exists($th, 'rawResponse') ? $th->rawResponse() : null,
                'response_code' => method_exists($th, 'httpStatusCode') ? $th->httpStatusCode() : null,
            ];
        }
    }

    /**
     * Update an existing WhatsApp message template via API
     */
    public function updateWhatsAppTemplate($templateId, $templateData)
    {
        $whatsapp_cloud_api = $this->loadConfig();
        try {
            $result = $whatsapp_cloud_api->updateTemplateById(
                $templateId,
                $templateData['components'],
                $templateData['category'] ?? null
            );
            return ['status' => true, 'data' => json_decode($result->body())];
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $responseData = method_exists($th, 'responseData') ? $th->responseData() : [];
            $error = $responseData['error'] ?? [];
            $message = $this->formatMetaTemplateErrorMessage($error, $th->getMessage());
            return [
                'status' => false,
                'message' => $message,
                'details' => $responseData,
                'raw_response' => method_exists($th, 'rawResponse') ? $th->rawResponse() : null,
                'response_code' => method_exists($th, 'httpStatusCode') ? $th->httpStatusCode() : null,
            ];
        }
    }

    /**
     * Send a template message using the WhatsApp Cloud API
     *
     * @param string $to Recipient phone number
     * @param array $template_data Data for the template message
     * @param string $type Type of the message, default is 'campaign'
     * @param string|null $fromNumber Optional sender phone number
     * @return array Response containing status, log data, and any response data or error message
     */
    public function sendTemplate($to, $template_data, $type = 'campaign', $fromNumber = null, $message_id = null)
    {
        $template_buttons_data = json_decode($template_data['buttons_data']);
        $is_flow = false;
        if (!empty($template_buttons_data)) {
            $button_types = array_column($template_buttons_data->buttons, 'type');
            $is_flow = in_array("FLOW", $button_types);
        }
        $this->load->model('whatsbot_model');
        $rel_type = $template_data['rel_type'];
        $header_data = [];
        if ($template_data['header_data_format'] == 'TEXT') {
            $header_data = wbParseText($rel_type, 'header', $template_data, 'array');
        }
        $body_data = wbParseText($rel_type, 'body', $template_data, 'array');
        $buttons_data = wbParseText($rel_type, 'footer', $template_data, 'array');

        $component_header = $component_body = $component_buttons = [];
        $upload_type = $type == "template_bot" ? 'template' : ($type == 'drip' ? 'drip' : 'campaign');
        $header_upload_type = $template_data['header_media_upload_type'] ?? $upload_type;
        $file_link = base_url(get_upload_path_by_type($header_upload_type) . $template_data['filename']);

        switch ($template_data['header_data_format']) {
            case 'IMAGE':
                $component_header[] = ['type' => 'image', 'image' => ["link" => $file_link]];
                break;

            case 'DOCUMENT':
                $component_header[] = ['type' => 'document', 'document' => ["link" => $file_link, "filename" => ($template_data['header_media_filename'] ?? $template_data['filename'])]];
                break;

            case 'VIDEO':
                $component_header[] = ['type' => 'video', 'video' => ["link" => $file_link]];
                break;

            default:
                foreach ($header_data as $header) {
                    $component_header[] = ['type' => 'text', 'text' => $header];
                }
                break;
        }
        foreach ($body_data as $body) {
            $component_body[] = ['type' => 'text', 'text' => $body];
        }
        if ($is_flow) {
            $buttons = json_decode($template_data['buttons_data'])->buttons;
            $flow_id = reset($buttons)->flow_id;
            $component_buttons[] = [
                'type' => 'button',
                "sub_type" => "FLOW",
                "index" => 0,
                "parameters" => [
                    [
                        "type" => "action",
                        "action" => [
                            "flow_token" => json_encode(["flow_id" => $flow_id, "rel_data" => $template_data['flow_action_data'] ?? []]),
                        ]
                    ]
                ]
            ];
        }
        foreach ($buttons_data as $buttons) {
            $component_buttons[] = ['type' => 'text', 'text' => $buttons];
        }

        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        if(!empty($message_id)){
            $this->sendTypingIndicator($message_id, $fromNumber);
        }

        if (!empty($template_data['ref_message_id'])) {
            $whatsapp_cloud_api->replyTo($template_data['ref_message_id']);
        }

        try {
            $components = new Component($component_header, $component_body, $component_buttons);
            $result = $whatsapp_cloud_api->sendTemplate($to, $template_data['template_name'], $template_data['language'], $components);
            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = json_encode($result->decodedBody());
            $rawData = json_encode($result->request()->body());
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? json_decode($th->getMessage());
            $responseCode = $th->httpStatusCode();
            $responseData = json_encode($message);
            $rawData = json_encode([]);
        }

        $log_data['response_code'] = $responseCode;
        $log_data['category'] = $type;
        $log_data['category_id'] = $template_data['campaign_id'] ?? $template_data['campaign_table_id'] ?? "";
        $log_data['rel_type'] = $rel_type;
        $log_data['rel_id'] = $template_data['rel_id'] ?? 0;
        $log_data['category_params'] = json_encode(['templateId' => $template_data['template_id'], 'message' => $message ?? '']);
        $log_data['response_data'] = $responseData;
        $log_data['raw_data'] = $rawData;

        return ['status' => $status, 'log_data' => $log_data, 'data' => $data ?? [], 'message' => $message->error->message ?? ''];
    }

    private function formatMetaTemplateErrorMessage($error, $fallbackMessage = '')
    {
        $userTitle = trim((string) ($error['error_user_title'] ?? ''));
        $userMessage = trim((string) ($error['error_user_msg'] ?? ''));
        $message = trim((string) ($error['message'] ?? ''));

        if ($userTitle !== '' && $userMessage !== '') {
            return $userTitle . ': ' . $userMessage;
        }

        if ($userMessage !== '') {
            return $userMessage;
        }

        if ($message !== '') {
            return $message;
        }

        return trim((string) $fallbackMessage);
    }

    /**
     * Retrieve a URL for a media file using its media ID
     *
     * @param string $media_id Media ID to retrieve the URL for
     * @param string $accessToken Access token for authentication
     * @return string|null Filename of the saved media file or null on failure
     */
    public function retrieveUrl($media_id, $accessToken)
    {
        if (!defined(WHATSBOT_MODULE_UPLOAD_FOLDER)) {
            define('WHATSBOT_MODULE_UPLOAD_FOLDER', 'uploads/whatsbot');
        }
        $uploadFolder = WHATSBOT_MODULE_UPLOAD_FOLDER;

        $client = new \GuzzleHttp\Client();
        $url = self::$facebookAPI . $media_id;
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        if (200 === $response->getStatusCode()) {
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['url'])) {
                $media = $responseData['url'];
                $mediaData = $client->get($media, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                ]);
                if (200 === $mediaData->getStatusCode()) {
                    $imageContent = $mediaData->getBody();
                    $contentType = $mediaData->getHeader('Content-Type')[0];

                    $extensionMap = self::$extensionMap;
                    $extension = $extensionMap[$contentType] ?? 'unknown';
                    $filename = 'media_' . uniqid() . '.' . $extension;
                    $storagePath = $uploadFolder . '/' . $filename;

                    $CI = &get_instance();
                    $CI->load->helper('file');
                    write_file($storagePath, $imageContent);

                    return $filename;
                }
            }
        }

        return null;
    }

    /**
     * Handle attachment upload and save the file
     *
     * @param array $attachment Attachment file information
     * @return string|bool Filename of the saved attachment or false on failure
     */
    public function handle_attachment_upload($attachment)
    {
        if (!defined(WHATSBOT_MODULE_UPLOAD_FOLDER)) {
            define('WHATSBOT_MODULE_UPLOAD_FOLDER', 'uploads/whatsbot');
        }
        $uploadFolder = WHATSBOT_MODULE_UPLOAD_FOLDER;

        $contentType = $attachment['type'];
        $extensionMap = self::$extensionMap;
        $extension = $extensionMap[$contentType] ?? 'unknown';

        $filename = uniqid('attachment_') . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;

        $destination = $uploadFolder . '/' . $filename;
        if (move_uploaded_file($attachment['tmp_name'], $destination)) {
            return $filename;
        }
        return false;
    }

    /**
     * Send a message using the WhatsApp Cloud API
     *
     * @param string $to Recipient phone number
     * @param array $message_data Data for the message
     * @param string|null $fromNumber Optional sender phone number
     * @return array Response containing status, log data, and any response data or error message
     */
    public function sendMessage($to, $message_data, $fromNumber = null, $folder = "bot_files", $message_id = null)
    {
        $message_data = wbParseMessageText($message_data);
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        if(!empty($message_id)){
            $this->sendTypingIndicator($message_id);
        }

        if (!empty($message_data['ref_message_id'])) {
            $whatsapp_cloud_api->replyTo($message_data['ref_message_id']);
        }

        try {

            switch ($message_data['option']) {
                case '2':
                    $rows = [];
                    if (!empty($message_data['button1_id'])) {
                        $rows[] = new Button($message_data['button1_id'], $message_data['button1']);
                    }
                    if (!empty($message_data['button2_id'])) {
                        $rows[] = new Button($message_data['button2_id'], $message_data['button2']);
                    }
                    if (!empty($message_data['button3_id'])) {
                        $rows[] = new Button($message_data['button3_id'], $message_data['button3']);
                    }
                    $action = new ButtonAction($rows);
                    $result = $whatsapp_cloud_api->sendButton(
                        $to,
                        $message_data['reply_text'],
                        $action,
                        $message_data['bot_header'],
                        $message_data['bot_footer']
                    );
                    break;

                case '3':
                    $header = new TitleHeader($message_data['bot_header']);
                    $result = $whatsapp_cloud_api->sendCtaUrl(
                        $to,
                        $message_data['button_name'],
                        $message_data['button_url'],
                        $header,
                        $message_data['reply_text'],
                        $message_data['bot_footer'],
                    );
                    break;

                case '4':
                    $message = $message_data['bot_header'] . "\n" . $message_data['reply_text'] . "\n" . $message_data['bot_footer'];
                    $url = base_url(get_upload_path_by_type($folder) . $message_data['filename']);
                    $link_id = new LinkID($url);
                    $bot_file_path = FCPATH . get_upload_path_by_type($folder) . $message_data['filename'];
                    if (is_image($bot_file_path)) {
                        $result = $whatsapp_cloud_api->sendImage($to, $link_id, $message);
                    } elseif (is_html5_video($bot_file_path)) {
                        $result = $whatsapp_cloud_api->sendVideo($to, $link_id, $message);
                    } elseif (!empty($message_data['filename'])) {
                        $result = $whatsapp_cloud_api->sendDocument($to, $link_id, $message_data['filename'], $message);
                    }
                    break;

                case '5':
                    $json = $message_data['sections'];
                    $sections = [];
                    $option_list = json_decode($json);
                    foreach ($option_list->sections as $section) {
                        $rows = [];
                        foreach ($section->text as $key => $row) {
                            $rows[] = new Row($key, $row, $section->subtext[$key]);
                        }
                        $sections[] = new Section($section->section, $rows);
                    }
                    $action = new Action($option_list->action, $sections);

                    $result = $whatsapp_cloud_api->sendList(
                        $to,
                        $message_data['bot_header'],
                        $message_data['reply_text'],
                        $message_data['bot_footer'],
                        $action
                    );
                    break;
                case '6': // Catalog
                    $message = $message_data['bot_header'] . "\n" . $message_data['reply_text'] . "\n" . $message_data['bot_footer'];
                    $result = $whatsapp_cloud_api->sendCatalog($to, $message);
                    break;

                case '7': // Location Request
                    $result = $whatsapp_cloud_api->sendLocationRequest($to, $message_data['reply_text']);
                    break;

                case '8': // Contact Card
                    $name = new ContactName(
                        $message_data['contact_first_name'] ?? '',
                        $message_data['contact_last_name'] ?? ''
                    );
                    $phone = new Phone($message_data['contact_phone'] ?? '', PhoneType::WORK());
                    $result = $whatsapp_cloud_api->sendContact($to, $name, $phone);
                    break;

                default:
                    $message = $message_data['bot_header'] . "\n" . $message_data['reply_text'] . "\n" . $message_data['bot_footer'];
                    $result = $whatsapp_cloud_api->sendTextMessage($to, $message, true);
                    break;
            }

            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = $data;
            $rawData = json_encode($result->request()->body());
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? $th->getMessage();
            $responseCode = $th->httpStatusCode();
            $responseData = $message;
            $rawData = json_encode([]);
        }

        $log_data['response_code'] = $responseCode;
        $log_data['category'] = $folder == 'bot_files' ? 'Message Bot' : 'Bot Flow Builder';
        $log_data['category_id'] = $message_data['id'];
        $log_data['rel_type'] = $message_data['rel_type'];
        $log_data['rel_id'] = ' - ';
        $log_data['category_params'] = json_encode(['message' => $message ?? '']);
        $log_data['response_data'] = !empty($responseData) ? json_encode($responseData) : '';
        $log_data['raw_data'] = $rawData;

        $batchLogData[] = $log_data;
        $this->whatsbot_model->addWhatsbotLog($batchLogData);

        return ['status' => $status, 'log_data' => $log_data ?? [], 'data' => $data ?? [], 'message' => $message->error->message ?? ''];
    }

    public function sendBulkCampaign($to, $template_data, $campaign, $fromNumber = null)
    {
        $header_data = [];
        if ($template_data['header_data_format'] == 'TEXT') {
            $header_data = wbParseCsvText('header', $template_data, $campaign, 'array');
        }
        $body_data = wbParseCsvText('body', $template_data, $campaign, 'array');
        $buttons_data = wbParseCsvText('footer', $template_data, $campaign, 'array');


        $component_header = $component_body = $component_buttons = [];
        $file_link = base_url(get_upload_path_by_type('csv') . $template_data['filename']);

        switch ($template_data['header_data_format']) {
            case 'IMAGE':
                $component_header[] = ['type' => 'image', 'image' => ["link" => $file_link]];
                break;

            case 'DOCUMENT':
                $component_header[] = ['type' => 'document', 'document' => ["link" => $file_link, "filename" => $template_data['filename']]];
                break;

            case 'VIDEO':
                $component_header[] = ['type' => 'video', 'video' => ["link" => $file_link]];
                break;

            default:
                foreach ($header_data as $header) {
                    $component_header[] = ['type' => 'text', 'text' => $header];
                }
                break;
        }
        foreach ($body_data as $body) {
            $component_body[] = ['type' => 'text', 'text' => $body];
        }
        foreach ($buttons_data as $buttons) {
            $component_buttons[] = ['type' => 'text', 'text' => $buttons];
        }

        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        try {
            $components = new Component($component_header, $component_body, $component_buttons);
            $result = $whatsapp_cloud_api->sendTemplate($to, $template_data['template_name'], $template_data['language'], $components);
            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? json_decode($th->getMessage());
            $responseCode = $th->httpStatusCode();
        }

        return [
            'status' => $status,
            'data' => $data ?? [],
            'responseCode' => $responseCode,
            'message' => $message->error->message ?? ''
        ];
    }

    public function generateUrlQR($url, $logo = null)
    {
        $writer = new PngWriter();

        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        if ($logo) {
            $logo = new Logo(
                path: module_dir_path('whatsbot', 'assets/images/whatsapp.png'),
                resizeToWidth: 50,
                punchoutBackground: true
            );
        }

        // Create generic label
        $label = new Label(
            text: '',
            textColor: new Color(255, 0, 0)
        );
        $result = $writer->write($qrCode, $logo, $label);
        $result->saveToFile(module_dir_path('whatsbot', 'assets/images/qrcode.png'));
        return true;
    }

    public function debugTocken()
    {
        try {
            $fb_app_id = $this->getFBAppID();
            $fb_app_secret = $this->getFBAppSecret();
            $accessToken    = $this->getToken();
            $appAccessToken = $fb_app_id . '|' . $fb_app_secret;
            $url = self::$facebookAPI . 'debug_token?input_token=' . $accessToken . '&access_token=' . $appAccessToken;
            // Use HttpClient (Symfony)
            $response = HttpClient::create()->request('GET', $url)->getContent();
            $data = json_decode($response, true);
            if (isset($data['error'])) {
                return [
                    'status'  => false,
                    'message' => $data['error']['message']
                ];
            }
            return [
                'status' => true,
                'data'   => json_decode(json_encode($data['data'])),
            ];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => _l("something_went_wrong")];
        }
    }

    public function connectWebhook()
    {
        $appId = $this->getFBAppID();
        $appSecret = $this->getFBAppSecret();

        $url = self::$facebookAPI . $appId . "/subscriptions?access_token=" . $appId . "|" . $appSecret;
        try {
            if (empty(get_option('wac_verify_token'))) {
                update_option('wac_verify_token', app_generate_hash(), 0);
            }
            $response = HttpClient::create()->request('POST', $url, [
                'body' => [
                    'object' => 'whatsapp_business_account',
                    'fields' => 'messages,message_template_quality_update,message_template_status_update,account_update',
                    'callback_url' => site_url('whatsbot/whatsapp_webhook'),
                    "verify_token" => get_option('wac_verify_token')
                ]
            ]);

            // Decode the JSON response
            $data = json_decode($response->getContent());

            if (isset($data->error)) {
                return [
                    'status' => false,
                    'message' => $data->error->message
                ];
            }
            update_option('wb_webhook_configure', 1, 0);

            return [
                'status' => true,
                'data' => $data
            ];
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => _l("something_went_wrong")];
        }
    }

    public function subscribeWebhook()
    {
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();
        $url = self::$facebookAPI . "/$accountId/subscribed_apps?access_token=" . $accessToken;
        try {
            $response = HttpClient::create()->request('POST', $url);

            // Decode the JSON response
            $data = json_decode($response->getContent());

            if (isset($data->error)) {
                return [
                    'status' => false,
                    'message' => $data->error->message
                ];
            }
            update_option('wb_webhook_subscribe', 1, 0);

            return [
                'status' => true,   
                'data' => $data
            ];
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => _l("something_went_wrong")];
        }
    }

    public function disconnectWebhook()
    {
        $appId = $this->getFBAppID();
        $appSecret = $this->getFBAppSecret();

        $url = self::$facebookAPI . $appId . "/subscriptions?access_token=" . $appId . "|" . $appSecret;
        try {
            $response = HttpClient::create()->request('DELETE', $url, [
                'body' => [
                    'object' => 'whatsapp_business_account',
                    'fields' => 'messages,message_template_quality_update,message_template_status_update,account_update',
                ]
            ]);
            // Decode the JSON response
            $data = json_decode($response->getContent(false));

            if (isset($data->error)) {
                return [
                    'status' => false,
                    'message' => $data->error->message
                ];
            }
            update_option('wb_webhook_configure', 0, 0);

            return [
                'status' => true,
                'data' => $data
            ];
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => _l("something_went_wrong")];
        }
    }

    public function getHealthStatus()
    {
        $accessToken = $this->getToken();
        $accountId = $this->getAccountID();

        $url = self::$facebookAPI . $accountId . '/?fields=health_status&access_token=' . $accessToken;
        $request = HttpClient::create()->request('GET', $url);
        $response = json_decode($request->getContent(false));
        update_option('wb_health_data', json_encode($response), 0);
        update_option('wb_health_check_time', date('l jS F Y g:i:s a'), 0);

        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        return ['status' => true, 'data' => $response];
    }

    /**
     * Get the access token for the WhatsApp Cloud API
     *
     * @return string Access token
     */
    private function getToken()
    {
        return get_option('wac_access_token');
    }

    /**
     * Get the access token for the WhatsApp Cloud API
     *
     * @return string Access token
     */
    private function getProfile()
    {
        $accessToken = $this->getToken();
        $phoneId = $this->getPhoneID();

        $url = self::$facebookAPI . $phoneId . '/whatsapp_business_profile?fields=profile_picture_url&access_token=' . $accessToken;
        $request = HttpClient::create()->request('GET', $url);
        $response = json_decode($request->getContent(false));

        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        return ['status' => true, 'data' => reset($response->data)];
    }

    public function testMessage($number)
    {
        $whatsapp_cloud_api = $this->loadConfig();
        try {
            $result = $whatsapp_cloud_api->sendTemplate($number, 'hello_world', 'en_US');
            $status = true;
            $message = _l('message_sent_successfully');
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? json_decode($th->getMessage());
            $responseCode = $th->httpStatusCode();
        }
        return ['status' => $status, 'message' => $message ?? ''];
    }

    public function embadedSignin($data)
    {
        $app_id = $this->getFBAppID();
        $app_secret = $this->getFBAppSecret();
        $code = $data['code'];
        $waba_id = $data['waBaId'];
        $phone_number_id = $data['phoneNumberId'];
        if (empty($waba_id) || empty($phone_number_id)) {
            file_put_contents(FCPATH . '/whatsbot_log.txt', '\n Time:' . date('l jS F Y g:i:s a') . '\n Error in embadedSignin : \n\t WABA id & phonenumber id not found \n' . '\n\n', FILE_APPEND);
            return ['status' => false, 'data' => []];
        }

        $url = self::$facebookAPI . 'oauth/access_token';

        $params = [
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'code' => $code,
        ];

        // Build the query string
        $query_string = http_build_query($params);

        // Append the query string to the base URL
        $full_url = $url . '?' . $query_string;

        try {
            // Make the GET request
            $response = HttpClient::create()->request('GET', $full_url);

            if ($response->getStatusCode() !== 200) {
                $status = false;
                file_put_contents(FCPATH . '/whatsbot_log.txt', '\n Time:' . date('l jS F Y g:i:s a') . '\n Error in embadedSignin : \n\t Failed to exchange authorization code for access token \n\n Response : \n' . $response . '\n\n', FILE_APPEND);
                return;
            }

            $responseData = json_decode($response->getContent(false), true);

            if (!isset($responseData['access_token'])) {
                $status = false;
                return;
            }

            $accessToken = $responseData['access_token'];
            update_option('wac_access_token', $accessToken, 0);
            update_option('wac_business_account_id', $waba_id, 0);
            update_option('wac_phone_number_id', $phone_number_id, 0);

            $this->connectWebhook();

            $res = $this->loadTemplatesFromWhatsApp($accessToken, $waba_id);
            if ($res['status']) {
                update_option('wb_account_connected', 1, 0);
            }

            $status = true;
            $data = json_decode($response->body, true);
        } catch (\Throwable $th) {
            file_put_contents(FCPATH . '/whatsbot_log.txt', '\n Time:' . date('l jS F Y g:i:s a') . '\n Error : something went wrong \n\t' . '\n\n', FILE_APPEND);
            $status = false;
        }
        return ['status' => $status ?? false, 'data' => $data ?? []];
    }

    public function sendLocation($to, $locationData, $fromNumber = null)
    {
        $whatsapp_cloud_api = $this->loadConfig($fromNumber);
        $this->sendTypingIndicator($to, $fromNumber);

        try {
            $result = $whatsapp_cloud_api->sendLocation($to, (float) $locationData['longitude'], (float) $locationData['latitude'], $locationData['locationName'], $locationData['address']);

            $status = true;
            $data = json_decode($result->body());
            $responseCode = $result->httpStatusCode();
            $responseData = $data;
            $rawData = json_encode($result->request()->body());
        } catch (\Netflie\WhatsAppCloudApi\Response\ResponseException $th) {
            $status = false;
            $message = $th->responseData()['error']['message'] ?? $th->rawResponse() ?? $th->getMessage();
            $responseCode = $th->httpStatusCode();
            $responseData = $message;
            $rawData = json_encode([]);
        }

        $log_data['response_code'] = $responseCode;
        $log_data['category'] = 'Location Message';
        $log_data['category_id'] = $locationData['id'] ?? 0;
        $log_data['rel_type'] = $locationData['rel_type'] ?? '';
        $log_data['rel_id'] = $locationData['rel_id'] ?? '';
        $log_data['category_params'] = json_encode(['message' => $message ?? '']);
        $log_data['response_data'] = !empty($responseData) ? json_encode($responseData) : '';
        $log_data['raw_data'] = $rawData;

        // Add to batch log data
        $batchLogData[] = $log_data;
        $this->whatsbot_model->addWhatsbotLog($batchLogData);

        return [
            'status' => $status,
            'log_data' => $log_data ?? [],
            'data' => $data ?? [],
            'message' => $message->error->message ?? ''
        ];
    }

    /**
     * Get the phone number ID for the WhatsApp Cloud API
     *
     * @return string Phone number ID
     */
    private function getPhoneID()
    {
        return get_option('wac_phone_number_id');
    }

    /**
     * Get the business account ID for the WhatsApp Cloud API
     *
     * @return string Business account ID
     */
    private function getAccountID()
    {
        return get_option('wac_business_account_id');
    }

    /**
     * Get the default phone number for the WhatsApp Cloud API
     *
     * @return string Default phone number
     */
    private function getDefaultPhoneNumber()
    {
        return get_option('wac_default_phone_number');
    }

    /**
     * Get the facebook app id
     *
     * @return string Default phone number
     */
    private function getFBAppID()
    {
        return get_option('wb_fb_app_id');
    }

    /**
     * Get the facebook app secret
     *
     * @return string Default phone number
     */
    private function getFBAppSecret()
    {
        return get_option('wb_fb_app_secret');
    }    

    /**
     * Get the WhatsApp business catalog ID
     * 
     * @return string|false Catalog ID or false if not found
     */
    public function getBusinessCatalog($return = "id") {
        $business_account_id = get_option('wac_business_account_id');
        $access_token = get_option('wac_access_token');
        
        if (!$business_account_id || !$access_token) {
            return false;
        }
        
        try {
            $request = WhatsappMarketingRequests::get(
                self::$facebookAPI . $business_account_id . '/product_catalogs?access_token=' . $access_token
            );
            
            $response_data = json_decode($request->body, true);
            
            if (isset($response_data['data'][0]['id'])) {
                return ($return == "id") ? $response_data['data'][0]['id'] : $response_data['data'][0];
            }
            
            return false;
        } catch (\Exception $e) {
            log_activity('WhatsApp Catalog Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a product in WhatsApp catalog
     * 
     * @param array $product_data Product data
     * @return array Response with status and product ID
     */
    public function createCatalogProduct($product_data) {
        $catalog_id = $this->getBusinessCatalog();
        $access_token = get_option('wac_access_token');
        
        if (!$catalog_id || !$access_token) {
            return [
                'success' => false,
                'message' => 'Missing catalog ID or access token'
            ];
        }
        
        try {

            $endpoint = self::$facebookAPI . $catalog_id . '/products';
            $request = WhatsappMarketingRequests::post($endpoint, [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
            ], $product_data);

            $response_data = json_decode($request->body, true);
            
            if (isset($response_data['id'])) {
                return [
                    'success' => true,
                    'catalog_product_id' => $response_data['id'],
                    'message' => 'Product added to catalog'
                ];
            }
            
            return [
                'success' => false,
                'message' => $response_data['error']['message']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update a product in WhatsApp catalog
     * 
     * @param string $catalog_product_id Catalog product ID
     * @param array $product_data Product data
     * @return array Response with status
     */
    public function updateCatalogProduct($catalog_product_id, $product_data) {
        $access_token = get_option('wac_access_token');
        if (!$access_token) {
            return [
                'success' => false,
                'message' => 'Missing access token'
            ];
        }
        
        unset($product_data['retailer_id']);

        try {
            $endpoint = self::$facebookAPI . $catalog_product_id;

            $catalog_id = $this->getBusinessCatalog();
            $request = WhatsappMarketingRequests::post($endpoint, [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
            ], $product_data);

            $response_data = json_decode($request->body, true);
                        
            if (isset($response_data['success']) && $response_data['success']) {
                return [
                    'success' => true,
                    'message' => 'Product updated in catalog'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update product in catalog'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all products from WhatsApp catalog
     * 
     * @param int $limit Limit of products to retrieve
     * @param string $after Cursor for pagination
     * @return array Response with status and products
     */
    public function getCatalogProducts($after = null, &$products = []) {
        $catalog_id = $this->getBusinessCatalog();
        $access_token = get_option('wac_access_token');
        
        if (!$catalog_id || !$access_token) {
            return [
                'success' => false,
                'message' => 'Missing catalog ID or access token'
            ];
        }
        
        try {
            $fields = 'id,name,retailer_id,description,price,sale_price,currency,image_url,images,product_catalog';

            $after_query = "";
            if(!empty($after)){
                $after_query = "&after=".$after;
            }
            
            $request = WhatsappMarketingRequests::get(
                self::$facebookAPI . $catalog_id . '/products?fields='.$fields.'&access_token=' . $access_token . '&limit=50' . $after_query
            );
            $response_data = json_decode($request->body, true);
            $products = array_merge($products, $response_data['data']);

            if(isset($response_data['paging'])){
                $this->getCatalogProducts($response_data['paging']['cursors']['after'], $products);
            }

            if (isset($response_data['data'])) {
                return [
                    'success' => true,
                    'products' => $products,
                    'paging' => $response_data['paging'] ?? null
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to get catalog products'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a product from WhatsApp catalog
     * 
     * @param string $catalog_product_id Catalog product ID
     * @return array Response with status
     */
    public function deleteCatalogProduct($catalog_product_id) {
        $access_token = get_option('wac_access_token');
        
        if (!$access_token) {
            return [
                'success' => false,
                'message' => 'Missing access token'
            ];
        }
        
        try {
            $endpoint = self::$facebookAPI . $catalog_product_id;
            
            $client = new \GuzzleHttp\Client();
            $response = $client->request('DELETE', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                ]
            ]);
            
            $response_data = json_decode($response->getBody(), true);
            
            if (isset($response_data['success']) && $response_data['success']) {
                return [
                    'success' => true,
                    'message' => 'Product deleted from catalog'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete product from catalog'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
