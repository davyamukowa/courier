<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Personal_assistant_model extends App_Model {
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public function __construct() {
        parent::__construct();
    }

    public function save($post_data) {
        $insert = $update = $id = false;
        if (!empty($post_data['id'])) {
            $update = $this->db->update(db_prefix() . 'wtc_personal_assistants', $post_data, ['id' => $post_data['id']]);
            $id = $post_data['id'];

            // Update OpenAI assistant if it exists
            if (!empty($post_data['openai_assistant_id'])) {
                $this->updateOpenAIAssistant($post_data['openai_assistant_id'], $post_data);
            }
        } else {
            $insert = $this->db->insert(db_prefix() . 'wtc_personal_assistants', $post_data);
            $id = $this->db->insert_id();
            
            // Create OpenAI assistant
            $openai_assistant = $this->createOpenAIAssistant($post_data);
            if ($openai_assistant) {
                $this->db->update(db_prefix() . 'wtc_personal_assistants', 
                    ['openai_assistant_id' => $openai_assistant['id']], 
                    ['id' => $id]
                );
            }
        }
        return [
            'id' => $id,
            'type' => $update || $insert ? 'success' : 'danger',
            'message' => $update ? _l('updated_successfully', _l('personal_assistant')) : ($insert ? _l('added_successfully', _l('personal_assistant')) : _l('something_went_wrong')),
            'url' => admin_url('whatsbot/personal_assistants')
        ];
    }

    public function get($id = '') {
        if (!empty($id)) {
            $data = $this->db->get_where(db_prefix() . 'wtc_personal_assistants', ['id' => $id])->row_array();
            $data['files'] = $this->get_pa_files($id);
            return $data;
        }
        return $this->db->get(db_prefix() . 'wtc_personal_assistants')->result_array();
    }

    public function get_pa_files($pa_id = '', $attachment_id = '') {
        if (!empty($pa_id)) {
            return $this->db->get_where(db_prefix() . 'wtc_pa_files', ['pa_id' => $pa_id])->result_array();
        }
        if (!empty($attachment_id)) {
            return $this->db->get_where(db_prefix() . 'wtc_pa_files', ['id' => $attachment_id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_pa_files')->result_array();
    }

    public function add_pa_files($pa_id, $attachments) {
        if (!empty($pa_id)) {
            return $this->db->insert_batch(db_prefix() . 'wtc_pa_files', $attachments);
        }
        return false;
    }

    /**
     * Delete assistant and cleanup OpenAI resources
     */
    public function delete($id) {
        $assistant = $this->get($id);
        if (!$assistant) {
            return [
                'type' => 'danger',
                'message' => _l('assistant_not_found'),
            ];
        }

        // Delete from OpenAI first
        if (!empty($assistant['openai_assistant_id'])) {
            $this->deleteOpenAIAssistant($assistant['openai_assistant_id']);
        }

        // Delete local documents
        $documents = $this->get_pa_files($id);
        foreach ($documents as $document) {
            $this->delete_document($document['id']);
        }

        // Delete assistant record
        $delete = $this->db->delete(db_prefix() . 'wtc_personal_assistants', ['id' => $id]);
        
        // Delete upload directory
        $filepath = get_upload_path_by_type('personal_assistant') . $id;
        if (is_dir($filepath)) {
            delete_dir($filepath);
        }

        return [
            'type' => 'danger',
            'message' => $delete ? _l('deleted', _l('personal_assistant')) : _l('something_went_wrong'),
        ];
    }

    /**
     * Add document to assistant
     */
    public function add_document($assistant_id, $file_data) {
        $file_path = get_upload_path_by_type('personal_assistant') . $assistant_id . '/' . $file_data['file_name'];
        $openai_file = $this->uploadFileToOpenAI($file_path, $file_data['original_name']);

        if (!$openai_file) {
            throw new Exception('Failed to upload file to OpenAI');
        }
        $assistant = $this->get($assistant_id);

        // Attach file to assistant
        if (!empty($assistant['openai_assistant_id'])) {
            $this->attachFileToAssistant($assistant['openai_assistant_id'], $openai_file['id'], $assistant['openai_vector_id']);
        }

        $this->db->insert(db_prefix() . 'wtc_pa_files', 
            [
                'file_name' => $file_data['original_name'],
                'filetype' => $file_data['file_type'],
                'pa_id' => $assistant_id,
                'openai_file_id' => $openai_file['id']
            ]
        );
        
        return true;
    }

    /**
     * Delete document and cleanup OpenAI file
     */
    public function delete_document($document_id) {
        $document = $this->db->get_where(db_prefix() . 'wtc_pa_files', ['id' => $document_id])->row_array();
        if (!$document) {
            return false;
        }

        // Delete from OpenAI if synced
        if (!empty($document['openai_file_id'])) {
            $this->deleteOpenAIFile($document['openai_file_id'], $document['pa_id']);
        }

        // Delete local file
        $file_path = get_upload_path_by_type('personal_assistant') . $document['pa_id'] . '/' . $document['file_name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete database record
        return $this->db->delete(db_prefix() . 'wtc_pa_files', ['id' => $document_id]);
    }

}
