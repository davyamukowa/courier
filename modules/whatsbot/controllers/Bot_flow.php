<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Bot_flow extends AdminController {
    public $module_version;
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model(['bot_flow_model', 'personal_assistant_model']);
        $module = $this->db->get_where(db_prefix() . 'modules', ['module_name' => 'whatsbot'])->row_array();
        $this->module_version = $module['installed_version'];
    }

    public function index() {
        if (!staff_can('view', 'wtc_bot_flow') && !staff_can('view', 'wtc_template')) {
            access_denied();
        }

        $data['title'] = _l('bot_flow');
        $data['active_group'] = 'bot_flow';
        $this->load->view('bot_flow/manage', $data);
    }

    public function flow($id = '') {
        $permission = (empty($id)) ? 'create' : 'edit';
        if (!staff_can($permission, 'wtc_bot_flow')) {
            access_denied();
        }

        $data['title'] = _l('bot_flow');
        $data['module_version'] = $this->module_version;
        $data['personal_assistants'] = json_encode(get_valid_assistants());

        if (!empty($id)) {
            $data['flow'] = $this->bot_flow_model->get($id);
        }

        $this->load->view('bot_flow/bot_flow', $data);
    }

    public function save() {
        $post_data = $this->input->post();
        $permission = empty($post_data['id']) ? 'create' : 'edit';
        if (!$this->input->is_ajax_request() && !staff_can($permission, 'wtc_bot_flow')) {
            ajax_access_denied();
        }
        $res = $this->bot_flow_model->save($post_data);
        echo json_encode($res);
    }

    public function delete($id) {
        if (!staff_can('delete', 'wtc_bot_flow')) {
            access_denied();
        }

        $res = $this->bot_flow_model->delete($id);
        set_alert('danger', $res['message']);
        redirect(admin_url('whatsbot/bot_flow'));
    }

    public function store_file() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $id = $this->input->post('id') ?? 0;
        $node_id = $this->input->post('node_id') ?? 0;
        if (!empty($node_id)) {
            // check if old file exist then remove it
            $dir = get_upload_path_by_type('flow') . $id;
            if (is_dir($dir)) {
                $files = scandir($dir);
                $matchingFiles = array_filter($files, function ($file) use ($node_id) {
                    return strpos($file, $node_id . '_') === 0;
                });
                array_walk($matchingFiles, function ($file) use ($dir) {
                    $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                });
            }
        }
        $status = false;
        $file_url = base_url('assets/images/user-placeholder.jpg');
        $filename = '';
        $message = '';
        $extensions = wb_get_allowed_extension();
        if (isset($_FILES) && !empty($id)) {
            $type = array_key_first($_FILES);
            $path = get_upload_path_by_type('flow') . '/';
            _maybe_create_upload_path($path);
            $path = $path . $id . '/';
            $tmpFilePath = $_FILES[$type]['tmp_name'];
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $newFileName = str_replace(" ", "_", $_FILES[$type]['name']);
                $filename = $node_id . '_' . unique_filename($path, $newFileName);
                if (in_array('.' . get_file_extension($filename), array_map('trim', explode(',', $extensions[$type]['extension'])))) {
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $status = true;
                        $file_url = base_url(get_upload_path_by_type('flow') . '/' . $id . '/');
                    }
                } else {
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $message = _l('you_can_not_upload_file_type', _l($extension));
                    $filename = '';
                }
            }
        }
        echo json_encode(['status' => $status, 'file_path' => $file_url, 'filename' => $filename, 'message' => $message]);
    }
}
