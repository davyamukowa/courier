<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Ai_prompts_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    public function add($posted_data) {
        $insert = $update = false;
        $posted_data['added_from'] = get_staff_user_id();
        if (isset($posted_data['action'])) {
            $posted_data['action'] = strip_tags($posted_data['action']);
        }

        if (empty($posted_data['id'])) {
            $insert = $this->db->insert(db_prefix() . 'wtc_ai_prompts', $posted_data);
        } else {
            $update = $this->db->update(db_prefix() . 'wtc_ai_prompts', $posted_data, ['id' => $posted_data['id']]);
        }

        $type = ($insert || $update) ? 'success' : 'danger';
        $message = _l('something_went_wrong');

        if ($type) {
            $message = ($insert) ? _l('added_successfully', _l('prompt')) : _l('updated_successfully', _l('prompt'));
        }

        return ['type' => $type, 'message' => $message];
    }

    public function delete_reply($posted_data) {
        $delete = $this->db->delete(db_prefix() . 'wtc_ai_prompts', ['id' => $posted_data['id']]);

        $message = ($delete) ? _l('delete', _l('prompt')) : _l('something_went_wrong');

        return ['type' => 'danger', 'message' => $message];
    }

    public function prompt_name_exists($posted_data) {
        if (!empty($posted_data['id'])) {
            $data = $this->db->get_where(db_prefix() . 'wtc_ai_prompts', ['id' => $posted_data['id']])->row_array();
            if ($data['name'] == $posted_data['name']) {
                return true;
            }
            $this->db->where('name', $posted_data['name']);
            $total_count = $this->db->count_all_results(db_prefix() . 'wtc_ai_prompts');
            return ($total_count > 0) ? false : true;

        }
        $this->db->where('name', $posted_data['name']);
        $total_count = $this->db->count_all_results(db_prefix() . 'wtc_ai_prompts');
        return ($total_count > 0) ? false : true;

    }

    public function get($id = '') {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_ai_prompts', ['id' => $id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_ai_prompts')->result_array();
    }
}
