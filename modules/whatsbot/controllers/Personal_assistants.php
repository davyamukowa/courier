<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for personal assistant functionality.
 */
class Personal_assistants extends AdminController {
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('personal_assistant_model');
    }

    public function index() {
        if (!staff_can('view', 'wtc_pa')) {
            access_denied();
        }
        $data['title'] = _l('personal_assistant');
        $data['active_group'] = 'ai_assistant';
        $this->load->view("personal_assistant/manage", $data);
    }

    public function personal_assistant($id = '') {
        if (!staff_can('edit', 'wtc_pa') && !staff_can('create', 'wtc_pa')) {
            access_denied();
        }
        $data['title'] = _l('personal_assistant');

        if (!empty($id)) {
            $data['pa'] = $this->personal_assistant_model->get($id);
        }

        $this->load->view("personal_assistant/personal_assistant", $data);
    }

    public function get_table_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, '/tables/personal_assistant'));
    }

    public function save() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $post_data = $this->input->post();
        $res = $this->personal_assistant_model->save($post_data);
        echo json_encode($res);
    }

    public function add_attachment($id) {
        if ((!staff_can('edit', 'wtc_pa') && !staff_can('create', 'wtc_pa')) || !$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $assistant = $this->personal_assistant_model->get($id);
        if (!$assistant) {
            echo json_encode(['success' => false, 'message' => _l('assistant_not_found')]);
            return;
        }

        /* Creating folder for personal assistant */
        $create_personal_assistant_paths = [
            WHATSBOT_MODULE_UPLOAD_FOLDER,
            WHATSBOT_MODULE_UPLOAD_FOLDER . '/personal_assistant',
        ];

        array_map('_maybe_create_upload_path', $create_personal_assistant_paths);

        $upload_path = get_upload_path_by_type('personal_assistant') . $id;
        _maybe_create_upload_path($upload_path);
        
        $uploaded_files = [];
        $errors = [];
        
        if (isset($_FILES['file'])) {
            $files = $_FILES['file'];
            
            // Handle multiple files
            $attachment = [];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_info = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    $result = $this->process_file_upload($file_info, $id, $upload_path);
                    
                    if ($result['success']) {
                        $uploaded_files[] = $result['file_data'];
                    } else {
                        $errors[] = $result['message'];
                    }
                }
            }
        }

        echo json_encode([
            'status' => empty($errors),
            'url' => admin_url('whatsbot/personal_assistants'),
        ]);
    }

    /**
     * Process individual file upload
     */
    private function process_file_upload($file_info, $assistant_id, $upload_path) {
        // Validate file type
        $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
        $file_ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            return [
                'success' => false, 
                'message' => _l('file_type_not_allowed', $file_info['name'])
            ];
        }
        
        // Validate file size (max 50MB)
        if ($file_info['size'] > 50 * 1024 * 1024) {
            return [
                'success' => false, 
                'message' => _l('file_too_large', $file_info['name'])
            ];
        }
        
        // Generate unique filename
        $filename = time() . '_' . str_replace(' ', '_', $file_info['name']);
        $file_path = $upload_path . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file_info['tmp_name'], $file_path)) {
            // Add to database
            $file_data = [
                'file_name' => $filename,
                'original_name' => $file_info['name'],
                'file_type' => $file_info['type'],
                'file_size' => $file_info['size']
            ];
            
            $document_id = $this->personal_assistant_model->add_document($assistant_id, $file_data);
            
            if ($document_id) {
                return [
                    'success' => true,
                    'file_data' => array_merge($file_data, ['id' => $document_id])
                ];
            }
        }
        
        return [
            'success' => false, 
            'message' => _l('file_upload_failed', $file_info['name'])
        ];
    }

    /**
     * Delete a document
     */
    public function delete_document($document_id) {
        if (!staff_can('delete', 'wtc_pa') || !$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        
        $result = $this->personal_assistant_model->delete_document($document_id);
        echo json_encode(['success' => $result]);
    }

    /**
     * Delete assistant
     */
    public function delete($id) {
        if (!staff_can('delete', 'wtc_pa')) {
            access_denied();
        }
        
        $result = $this->personal_assistant_model->delete($id);
        set_alert($result['type'], $result['message']);
        redirect(admin_url('whatsbot/personal_assistants'));
    }

    /**
     * Test assistant with a question
     */
    public function test_assistant() {
        $assistant_id = 39;
        $question = "Does he have experince? If so where";
        
        if (empty($assistant_id) || empty($question)) {
            echo json_encode(['success' => false, 'message' => _l('missing_required_fields')]);
            return;
        }
        // exit;
        $result = $this->getAIAnswer($assistant_id, $question);
        echo json_encode($result);
    }


}
