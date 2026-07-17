<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Templates Controller
 *
 * Handles operations related to WhatsApp templates.
 */
class Templates extends AdminController {
    use modules\whatsbot\traits\Whatsapp; // Uses a trait for WhatsApp related methods

    /**
     * Constructor
     *
     * Initializes the controller and checks module activation status.
     */
    public function __construct() {
        parent::__construct();

        // Check if the whatsbot module is inactive; deny access if so
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';

        $this->load->model('whatsbot_model'); // Load the WhatsApp bot model
    }

    /**
     * Index method
     *
     * Loads the main view for WhatsApp templates management.
     */
    public function index() {
        // Check if user has permission to view WhatsApp templates
        if (!staff_can('view', 'wtc_template')) {
            access_denied();
        }

        $viewData['title'] = _l('templates'); // Set view title
        $viewData['active_group'] = 'message_templates';

        $this->load->view('templates', $viewData); // Load templates view
    }

    /**
     * Get Table Data method
     *
     * Retrieves data for the templates table via AJAX.
     *
     * @return bool Returns false if the request is not an AJAX request.
     */
    public function get_table_data() {
        if (!$this->input->is_ajax_request()) {
            return false;
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/templates')); // Get table data
    }

    /**
     * Template Builder - Create or edit a template
     */
    public function create($id = '') {
        if (!staff_can('create', 'wtc_template')) {
            access_denied();
        }

        $viewData['title'] = empty($id) ? _l('create_new_template') : _l('edit_template');
        $viewData['tpl'] = !empty($id) ? $this->db->get_where(db_prefix() . 'wtc_templates', ['id' => $id])->row() : null;
        $viewData['languages'] = [
            ['key' => 'en', 'value' => 'English'],
            ['key' => 'en_GB', 'value' => 'English (UK)'],
            ['key' => 'en_US', 'value' => 'English (US)'],
            ['key' => 'en_AE', 'value' => 'English (UAE)'],
            ['key' => 'en_AU', 'value' => 'English (Australia)'],
            ['key' => 'en_CA', 'value' => 'English (Canada)'],
            ['key' => 'en_GH', 'value' => 'English (Ghana)'],
            ['key' => 'en_IE', 'value' => 'English (Ireland)'],
            ['key' => 'en_IN', 'value' => 'English (India)'],
            ['key' => 'en_JM', 'value' => 'English (Jamaica)'],
            ['key' => 'en_MY', 'value' => 'English (Malaysia)'],
            ['key' => 'en_NZ', 'value' => 'English (New Zealand)'],
            ['key' => 'en_QA', 'value' => 'English (Qatar)'],
            ['key' => 'en_SG', 'value' => 'English (Singapore)'],
            ['key' => 'en_UG', 'value' => 'English (Uganda)'],
            ['key' => 'en_ZA', 'value' => 'English (South Africa)'],
            
            ['key' => 'af', 'value' => 'Afrikaans'],
            ['key' => 'sq', 'value' => 'Albanian'],

            ['key' => 'ar', 'value' => 'Arabic'],
            ['key' => 'ar_EG', 'value' => 'Arabic (Egypt)'],
            ['key' => 'ar_AE', 'value' => 'Arabic (UAE)'],
            ['key' => 'ar_LB', 'value' => 'Arabic (Lebanon)'],
            ['key' => 'ar_MA', 'value' => 'Arabic (Morocco)'],
            ['key' => 'ar_QA', 'value' => 'Arabic (Qatar)'],

            ['key' => 'az', 'value' => 'Azerbaijani'],

            ['key' => 'be_BY', 'value' => 'Belarusian'],

            ['key' => 'bn', 'value' => 'Bengali'],
            ['key' => 'bn_IN', 'value' => 'Bengali (India)'],

            ['key' => 'bg', 'value' => 'Bulgarian'],
            ['key' => 'ca', 'value' => 'Catalan'],

            ['key' => 'zh_CN', 'value' => 'Chinese (CHN)'],
            ['key' => 'zh_HK', 'value' => 'Chinese (Hong Kong)'],
            ['key' => 'zh_TW', 'value' => 'Chinese (Taiwan)'],

            ['key' => 'hr', 'value' => 'Croatian'],
            ['key' => 'cs', 'value' => 'Czech'],
            ['key' => 'da', 'value' => 'Danish'],

            ['key' => 'prs_AF', 'value' => 'Dari (Afghanistan)'],

            ['key' => 'nl', 'value' => 'Dutch'],
            ['key' => 'nl_BE', 'value' => 'Dutch (Belgium)'],

            ['key' => 'et', 'value' => 'Estonian'],
            ['key' => 'fil', 'value' => 'Filipino'],
            ['key' => 'fi', 'value' => 'Finnish'],

            ['key' => 'fr', 'value' => 'French'],
            ['key' => 'fr_BE', 'value' => 'French (Belgium)'],
            ['key' => 'fr_CA', 'value' => 'French (Canada)'],
            ['key' => 'fr_CH', 'value' => 'French (Switzerland)'],
            ['key' => 'fr_CI', 'value' => 'French (Ivory Coast)'],
            ['key' => 'fr_MA', 'value' => 'French (Morocco)'],

            ['key' => 'ka', 'value' => 'Georgian'],

            ['key' => 'de', 'value' => 'German'],
            ['key' => 'de_AT', 'value' => 'German (Austria)'],
            ['key' => 'de_CH', 'value' => 'German (Switzerland)'],

            ['key' => 'el', 'value' => 'Greek'],

            ['key' => 'gu', 'value' => 'Gujarati'],
            ['key' => 'ha', 'value' => 'Hausa'],

            ['key' => 'he', 'value' => 'Hebrew'],
            ['key' => 'hi', 'value' => 'Hindi'],
            ['key' => 'hu', 'value' => 'Hungarian'],

            ['key' => 'id', 'value' => 'Indonesian'],
            ['key' => 'ga', 'value' => 'Irish'],

            ['key' => 'it', 'value' => 'Italian'],
            ['key' => 'ja', 'value' => 'Japanese'],
            ['key' => 'kn', 'value' => 'Kannada'],
            ['key' => 'kk', 'value' => 'Kazakh'],

            ['key' => 'rw_RW', 'value' => 'Kinyarwanda'],

            ['key' => 'ko', 'value' => 'Korean'],

            ['key' => 'ky_KG', 'value' => 'Kyrgyz (Kyrgyzstan)'],

            ['key' => 'lo', 'value' => 'Lao'],
            ['key' => 'lv', 'value' => 'Latvian'],
            ['key' => 'lt', 'value' => 'Lithuanian'],
            ['key' => 'mk', 'value' => 'Macedonian'],

            ['key' => 'ms', 'value' => 'Malay'],
            ['key' => 'ml', 'value' => 'Malayalam'],
            ['key' => 'mr', 'value' => 'Marathi'],

            ['key' => 'nb', 'value' => 'Norwegian'],

            ['key' => 'ps_AF', 'value' => 'Pashto (Afghanistan)'],
            ['key' => 'fa', 'value' => 'Persian'],

            ['key' => 'pl', 'value' => 'Polish'],

            ['key' => 'pt_BR', 'value' => 'Portuguese (Brazil)'],
            ['key' => 'pt_PT', 'value' => 'Portuguese (Portugal)'],

            ['key' => 'pa', 'value' => 'Punjabi'],

            ['key' => 'ro', 'value' => 'Romanian'],
            ['key' => 'ru', 'value' => 'Russian'],
            ['key' => 'sr', 'value' => 'Serbian'],

            ['key' => 'si_LK', 'value' => 'Sinhala (Sri Lanka)'],

            ['key' => 'sk', 'value' => 'Slovak'],
            ['key' => 'sl', 'value' => 'Slovenian'],

            ['key' => 'es', 'value' => 'Spanish'],
            ['key' => 'es_AR', 'value' => 'Spanish (Argentina)'],
            ['key' => 'es_CL', 'value' => 'Spanish (Chile)'],
            ['key' => 'es_CO', 'value' => 'Spanish (Colombia)'],
            ['key' => 'es_CR', 'value' => 'Spanish (Costa Rica)'],
            ['key' => 'es_DO', 'value' => 'Spanish (Dominican Republic)'],
            ['key' => 'es_EC', 'value' => 'Spanish (Ecuador)'],
            ['key' => 'es_HN', 'value' => 'Spanish (Honduras)'],
            ['key' => 'es_MX', 'value' => 'Spanish (Mexico)'],
            ['key' => 'es_PA', 'value' => 'Spanish (Panama)'],
            ['key' => 'es_PE', 'value' => 'Spanish (Peru)'],
            ['key' => 'es_ES', 'value' => 'Spanish (Spain)'],
            ['key' => 'es_UY', 'value' => 'Spanish (Uruguay)'],

            ['key' => 'sw', 'value' => 'Swahili'],
            ['key' => 'sv', 'value' => 'Swedish'],

            ['key' => 'ta', 'value' => 'Tamil'],
            ['key' => 'te', 'value' => 'Telugu'],

            ['key' => 'th', 'value' => 'Thai'],
            ['key' => 'tr', 'value' => 'Turkish'],
            ['key' => 'uk', 'value' => 'Ukrainian'],
            ['key' => 'ur', 'value' => 'Urdu'],
            ['key' => 'uz', 'value' => 'Uzbek'],

            ['key' => 'vi', 'value' => 'Vietnamese'],
            ['key' => 'zu', 'value' => 'Zulu'],
        ];
        $viewData['categories'] = [
            ['key' => 'MARKETING', 'value' => 'Marketing'],
            ['key' => 'UTILITY', 'value' => 'Utility'],
            ['key' => 'AUTHENTICATION', 'value' => 'Authentication'],
        ];
        $viewData['header_types'] = [
            ['key' => 'NONE', 'value' => 'None'],
            ['key' => 'TEXT', 'value' => 'Text'],
            ['key' => 'IMAGE', 'value' => 'Image'],
            ['key' => 'VIDEO', 'value' => 'Video'],
            ['key' => 'DOCUMENT', 'value' => 'Document'],
        ];
        $module = $this->db->get_where(db_prefix() . 'modules', ['module_name' => 'whatsbot'])->row_array();
        $viewData['module_version'] = $module['installed_version'];

        $this->load->view('templates/template_builder', $viewData);
    }

    /**
     * Save template - create or update via WhatsApp API
     */
    public function save() {
        if (!$this->input->is_ajax_request()) {
            return false;
        }

        $post = $this->input->post();
        
        $validation = $this->validateTemplateBuilderPayload($post);
        if (!$validation['status']) {
            echo json_encode($validation);
            return;
        }

        if (in_array(strtoupper(trim($post['header_data_format'] ?? 'NONE')), ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
            if (!empty($_FILES['header_media_file']['name'])) {
                if (strtoupper(trim($post['header_data_format'] ?? 'NONE')) === 'IMAGE') {
                    $imageValidation = $this->validateTemplateHeaderImage($_FILES['header_media_file']['tmp_name'] ?? '');
                    if (!$imageValidation['status']) {
                        echo json_encode($imageValidation);
                        return;
                    }
                }
                $upload = $this->uploadTemplateMedia($_FILES['header_media_file'], strtoupper(trim($post['header_data_format'])));
                if (!$upload['status']) {
                    echo json_encode($upload);
                    return;
                }
                $post['header_media_url'] = $upload['url'];
                $post['header_media_path'] = $upload['path'];
            }
        }

        // Build components array for WhatsApp API
        $components = [];

        // Header component
        if (!empty($post['header_data_format']) && $post['header_data_format'] !== 'NONE') {
            $header = ['type' => 'HEADER', 'format' => $post['header_data_format']];
            if ($post['header_data_format'] === 'TEXT') {
                $header['text'] = trim($post['header_data_text'] ?? '');
                $headerVars = $this->extractTemplateVariables($header['text']);
                if (!empty($headerVars)) {
                    $examples = [];
                    foreach ($headerVars as $index => $varNum) {
                        $examples[] = trim($post['header_variables'][$varNum] ?? '');
                    }
                    $header['example'] = ['header_text' => [$examples]];
                }
            } else {
                $headerMediaHandle = '';
                if (!empty($post['header_media_path']) && is_file($post['header_media_path'])) {
                    if ($post['header_data_format'] === 'IMAGE') {
                        $imageValidation = $this->validateTemplateHeaderImage($post['header_media_path']);
                        if (!$imageValidation['status']) {
                            echo json_encode($imageValidation);
                            return;
                        }
                    }
                    $uploadResult = $this->uploadTemplateMediaHandle($post['header_media_path']);
                    if (!$uploadResult['status']) {
                        echo json_encode($uploadResult);
                        return;
                    }
                    $headerMediaHandle = $uploadResult['handle'];
                } elseif (!empty($post['header_media_url'])) {
                    $downloaded = $this->downloadRemoteMediaToTempFile(trim($post['header_media_url']));
                    if ($downloaded['status']) {
                        if ($post['header_data_format'] === 'IMAGE') {
                            $imageValidation = $this->validateTemplateHeaderImage($downloaded['path']);
                            if (!$imageValidation['status']) {
                                @unlink($downloaded['path']);
                                echo json_encode($imageValidation);
                                return;
                            }
                        }
                        $uploadResult = $this->uploadTemplateMediaHandle($downloaded['path']);
                        @unlink($downloaded['path']);
                        if (!$uploadResult['status']) {
                            echo json_encode($uploadResult);
                            return;
                        }
                        $headerMediaHandle = $uploadResult['handle'];
                    }
                }

                if (!empty($headerMediaHandle)) {
                    $header['example'] = [
                        'header_handle' => [$headerMediaHandle],
                    ];
                } elseif (in_array($post['header_data_format'], ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Unable to prepare header media for template creation.',
                        'details' => [
                            'header_data_format' => $post['header_data_format'],
                        ],
                    ]);
                    return;
                }
            }
            $components[] = $header;
        }

        // Body component
        if (!empty($post['body_data'])) {
            $bodyText = trim($post['body_data']);
            $body = ['type' => 'BODY', 'text' => $bodyText];
            $bodyVars = $this->extractTemplateVariables($bodyText);
            if (!empty($bodyVars)) {
                $examples = [];
                foreach ($bodyVars as $varNum) {
                    $examples[] = trim($post['body_variables'][$varNum] ?? '');
                }
                $body['example'] = ['body_text' => [$examples]];
            }
            $components[] = $body;
        }

        // Footer component
        if (!empty($post['footer_data'])) {
            $components[] = ['type' => 'FOOTER', 'text' => trim($post['footer_data'])];
        }

        // Buttons
        if (!empty($post['buttons'])) {
            $buttons = [];
            foreach ($post['buttons'] as $btn) {
                if (!empty(trim($btn['text'] ?? ''))) {
                    $type = strtoupper(trim($btn['type'] ?? 'QUICK_REPLY'));
                    $button = ['type' => $type, 'text' => trim($btn['text'])];
                    if ($type === 'URL') {
                        $button['url'] = trim($btn['url'] ?? '');
                    } elseif ($type === 'PHONE_NUMBER') {
                        $button['phone_number'] = trim($btn['phone_number'] ?? '');
                    }
                    $buttons[] = $button;
                }
            }
            if (!empty($buttons)) {
                $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
            }
        }

        $templateData = [
            'name' => $post['template_name'],
            'language' => $post['language'],
            'category' => $post['category'] ?? 'MARKETING',
            'components' => $components,
        ];

        if (!empty($post['id']) && !empty($post['template_id'])) {
            $result = $this->updateWhatsAppTemplate($post['template_id'], $templateData);
        } else {
            $result = $this->createWhatsAppTemplate($templateData);
        }

        if (empty($result['status'])) {
            echo json_encode([
                'status' => false,
                'message' => $result['message'] ?? _l('something_went_wrong'),
                'details' => $result['details'] ?? null,
                'raw_response' => $result['raw_response'] ?? null,
                'response_code' => $result['response_code'] ?? null,
            ]);
            return;
        }

        $this->whatsbot_model->load_templates();

        echo json_encode([
            'status' => true,
            'message' => !empty($post['id']) ? 'Template updated successfully.' : 'Template created successfully.',
            'data' => $result['data'] ?? [],
        ]);
        return;
    }

    private function validateTemplateBuilderPayload($post)
    {
        $templateName = trim($post['template_name'] ?? '');
        $language = trim($post['language'] ?? '');
        $category = trim($post['category'] ?? 'MARKETING');
        $headerFormat = strtoupper(trim($post['header_data_format'] ?? 'NONE'));
        if ($headerFormat === '') {
            $headerFormat = 'NONE';
        }
        $headerText = trim($post['header_data_text'] ?? '');
        $bodyText = trim($post['body_data'] ?? '');
        $footerText = trim($post['footer_data'] ?? '');
        $errors = [];

        if ($templateName === '') {
            $errors[] = _l('template_name') . ': This field is required.';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $templateName)) {
            $errors[] = _l('template_name') . ' must use lowercase letters, numbers, and underscores only.';
        } elseif (strlen($templateName) > 512) {
            $errors[] = _l('template_name') . ' must not exceed 512 characters.';
        }

        if ($language === '') {
            $errors[] = _l('language') . ': This field is required.';
        }

        if (!in_array($category, ['MARKETING', 'UTILITY', 'AUTHENTICATION'], true)) {
            $errors[] = _l('category') . ': This field is required.';
        }

        if (!in_array($headerFormat, ['NONE', 'TEXT', 'IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
            $errors[] = _l('header_type') . ' is invalid.';
        }

        if ($headerFormat === 'TEXT') {
            if ($headerText === '') {
                $errors[] = _l('header_text') . ': This field is required.';
            } elseif (strlen($headerText) > 60) {
                $errors[] = _l('header_text') . ' must not exceed 60 characters.';
            }

            $headerVars = $this->extractTemplateVariables($headerText);
            if (count($headerVars) > 1) {
                $errors[] = 'Text header supports only one variable.';
            }
        } elseif (in_array($headerFormat, ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
            $hasFile = !empty($_FILES['header_media_file']['name']);
            $headerMediaUrl = trim($post['header_media_url'] ?? '');

            if (!$hasFile && $headerMediaUrl === '') {
                $errors[] = ucfirst(strtolower($headerFormat)) . ' file or URL: This field is required.';
            } elseif (!$hasFile && $headerMediaUrl !== '' && !filter_var($headerMediaUrl, FILTER_VALIDATE_URL)) {
                $errors[] = ucfirst(strtolower($headerFormat)) . ' URL must be a valid URL.';
            }
        }

        if ($bodyText === '') {
            $errors[] = _l('message_body') . ': This field is required.';
        } elseif (strlen($bodyText) > 1024) {
            $errors[] = _l('message_body') . ' must not exceed 1024 characters.';
        }

        $bodyVars = $this->extractTemplateVariables($bodyText);
        if (!empty($bodyVars)) {
            $expected = range(1, count($bodyVars));
            if ($bodyVars !== $expected) {
                $errors[] = 'Body variables must be sequential and start from {{1}}.';
            }

            foreach ($bodyVars as $varNum) {
                $example = trim($post['body_variables'][$varNum] ?? '');
                if ($example === '') {
                    $errors[] = 'Body variable {{' . $varNum . '}}: This field is required.';
                }
            }
        }

        if ($footerText !== '' && strlen($footerText) > 60) {
            $errors[] = _l('footer_text') . ' must not exceed 60 characters.';
        }

        $buttons = $post['buttons'] ?? [];
        if (!empty($buttons) && count($buttons) > 3) {
            $errors[] = 'WhatsApp template supports a maximum of 3 buttons.';
        }

        foreach ($buttons as $index => $button) {
            $type = strtoupper(trim($button['type'] ?? ''));
            $text = trim($button['text'] ?? '');
            $url = trim($button['url'] ?? '');
            $phone = trim($button['phone_number'] ?? '');

            if (!in_array($type, ['QUICK_REPLY', 'URL', 'PHONE_NUMBER'], true)) {
                $errors[] = 'Button ' . ($index + 1) . ' type is invalid.';
                continue;
            }

            if ($text === '') {
                $errors[] = 'Button ' . ($index + 1) . ' text: This field is required.';
            } elseif (mb_strlen($text) > 25) {
                $errors[] = 'Button ' . ($index + 1) . ' text must not exceed 25 characters.';
            }

            if ($type === 'URL') {
                if ($url === '') {
                    $errors[] = 'Button ' . ($index + 1) . ' website URL: This field is required.';
                } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $errors[] = 'Button ' . ($index + 1) . ' website URL must be valid.';
                }
            }

            if ($type === 'PHONE_NUMBER') {
                if ($phone === '') {
                    $errors[] = 'Button ' . ($index + 1) . ' phone number: This field is required.';
                } elseif (!preg_match('/^\+?[0-9]{6,15}$/', $phone)) {
                    $errors[] = 'Button ' . ($index + 1) . ' phone number must contain 6 to 15 digits and may start with +.';
                }
            }
        }

        if (!empty($errors)) {
            return [
                'status' => false,
                'message' => implode(' ', $errors),
            ];
        }

        return ['status' => true];
    }

    private function validateTemplateHeaderImage($filePath)
    {
        if (empty($filePath) || !is_file($filePath)) {
            return [
                'status' => false,
                'message' => 'Header image file is missing.',
            ];
        }

        $mimeType = '';
        if (function_exists('mime_content_type')) {
            $mimeType = (string) mime_content_type($filePath);
        }

        if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            return [
                'status' => false,
                'message' => 'Header image must be a JPG or PNG file.',
            ];
        }

        return ['status' => true];
    }

    private function extractTemplateVariables($text)
    {
        if (empty($text)) {
            return [];
        }

        preg_match_all('/{{\s*(\d+)\s*}}/', $text, $matches);
        $vars = array_values(array_unique(array_map('intval', $matches[1] ?? [])));
        sort($vars);

        return $vars;
    }

    private function uploadTemplateMedia($file, $format)
    {
        if (empty($file['name']) || empty($file['tmp_name'])) {
            return ['status' => false, 'message' => 'Media file is required.'];
        }

        $allowedExtensions = wb_get_allowed_extension();
        $formatKey = strtolower($format);
        if (!isset($allowedExtensions[$formatKey])) {
            return ['status' => false, 'message' => 'Unsupported media type.'];
        }

        $maxSize = (float) $allowedExtensions[$formatKey]['size'] * 1024 * 1024;
        if (!empty($file['size']) && $file['size'] > $maxSize) {
            return ['status' => false, 'message' => 'Max file size for ' . strtolower($format) . ' is ' . $allowedExtensions[$formatKey]['size'] . ' MB.'];
        }

        $allowedExt = array_map('trim', explode(',', $allowedExtensions[$formatKey]['extension']));
        $fileExt = '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExt, true)) {
            return ['status' => false, 'message' => 'Invalid file type for ' . strtolower($format) . '.'];
        }

        $path = get_upload_path_by_type('template');
        _maybe_create_upload_path($path);

        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $originalName);
        $sanitizedName = trim((string) $sanitizedName, '_');
        if ($sanitizedName === '') {
            $sanitizedName = 'template_media';
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $sanitizedName . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        if ($extension !== '') {
            $filename .= '.' . $extension;
        }
        $newFilePath = $path . $filename;

        if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
            return ['status' => false, 'message' => 'Unable to upload media file.'];
        }

        return [
            'status' => true,
            'url' => base_url(get_upload_path_by_type('template') . $filename),
            'path' => $newFilePath,
        ];
    }

    private function uploadTemplateMediaHandle($filePath)
    {
        if (empty($filePath) || !is_file($filePath)) {
            return [
                'status' => false,
                'message' => 'Template header media file is missing.',
            ];
        }

        try {
            $mimeType = '';
            if (function_exists('mime_content_type')) {
                $mimeType = (string) mime_content_type($filePath);
            }
            if ($mimeType === '') {
                $mimeType = $this->guessMimeTypeFromFileName($filePath);
            }

            $fileLength = @filesize($filePath);
            if (empty($mimeType) || empty($fileLength)) {
                return [
                    'status' => false,
                    'message' => 'Unable to determine template header media type.',
                ];
            }

            $session = $this->createTemplateUploadSession($fileLength, $mimeType, $filePath);
            if (empty($session['status']) || empty($session['upload_id'])) {
                return $session;
            }

            $upload = $this->completeTemplateUploadSession($session['upload_id'], $filePath, $mimeType);
            if (empty($upload['status']) || empty($upload['handle'])) {
                return $upload;
            }

            return [
                'status' => true,
                'handle' => $upload['handle'],
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => $th->getMessage(),
            ];
        }
    }

    private function createTemplateUploadSession($fileLength, $mimeType, $filePath)
    {
        $accessToken = $this->getToken();
        $url = self::$facebookAPI . 'app/uploads?' . http_build_query([
            'file_length' => $fileLength,
            'file_type' => $mimeType,
            'file_name' => basename($filePath),
        ]);

        $ch = curl_init($url);
        if ($ch === false) {
            return ['status' => false, 'message' => 'Unable to initialize upload session.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: OAuth ' . $accessToken,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $response === '') {
            return ['status' => false, 'message' => $curlError ?: 'Unable to create upload session.'];
        }

        $decoded = json_decode($response, true);
        $uploadId = $decoded['id'] ?? $decoded['upload_id'] ?? $decoded['session_id'] ?? '';

        if (empty($uploadId)) {
            return [
                'status' => false,
                'message' => 'Unable to create upload session.',
                'response_code' => $httpCode,
                'raw_response' => $response,
            ];
        }

        return [
            'status' => true,
            'upload_id' => $uploadId,
        ];
    }

    private function completeTemplateUploadSession($uploadId, $filePath, $mimeType)
    {
        $accessToken = $this->getToken();
        $url = self::$facebookAPI . ltrim($uploadId, '/');
        $binary = @file_get_contents($filePath);
        if ($binary === false || $binary === '') {
            return [
                'status' => false,
                'message' => 'Unable to read template header media file.',
            ];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'status' => false,
                'message' => 'Unable to initialize template media upload.',
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $binary,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: OAuth ' . $accessToken,
                'file_offset: 0',
                'Content-Type: ' . $mimeType,
                'Accept: */*',
            ],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $response === '') {
            return [
                'status' => false,
                'message' => $curlError ?: 'Unable to upload template header media.',
            ];
        }

        $decoded = json_decode($response, true);
        if (!empty($decoded['h'])) {
            return [
                'status' => true,
                'handle' => (string) $decoded['h'],
            ];
        }

        if (!empty($decoded['handle'])) {
            return [
                'status' => true,
                'handle' => (string) $decoded['handle'],
            ];
        }

        return [
            'status' => false,
            'message' => 'Unable to retrieve template header media handle.',
            'response_code' => $httpCode,
            'raw_response' => $response,
        ];
    }

    private function guessMimeTypeFromFileName($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $map[$extension] ?? '';
    }

    private function downloadRemoteMediaToTempFile($url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['status' => false, 'message' => 'Invalid media URL.'];
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => 1,
            ],
            'https' => [
                'timeout' => 15,
                'follow_location' => 1,
            ],
        ]);

        $contents = @file_get_contents($url, false, $context);
        if ($contents === false || $contents === '') {
            return ['status' => false, 'message' => 'Unable to fetch media URL.'];
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'wb_template_');
        if ($tmpFile === false) {
            return ['status' => false, 'message' => 'Unable to create temporary file.'];
        }

        if (file_put_contents($tmpFile, $contents) === false) {
            @unlink($tmpFile);
            return ['status' => false, 'message' => 'Unable to write temporary file.'];
        }

        return ['status' => true, 'path' => $tmpFile];
    }

    /**
     * Load Templates method
     *
     * Loads WhatsApp templates asynchronously.
     *
     * @return bool Returns false if the request is not an AJAX request or if the user lacks permission.
     */
    public function load_templates() {
        if (!$this->input->is_ajax_request() && !staff_can('load_template', 'wtc_template')) {
            return false;
        }

        $response = $this->whatsbot_model->load_templates(); // Call model method to load templates

        if (false == $response['success']) {
            // If loading templates fails, return error response
            echo json_encode([
                'success' => $response['success'],
                'type' => $response['type'],
                'message' => $response['message'],
            ]);
            exit();
        }

        // If templates are loaded successfully, return success response
        echo json_encode([
            'success' => true,
            'type' => 'success',
            'message' => _l('template_data_loaded'),
        ]);
    }
}
