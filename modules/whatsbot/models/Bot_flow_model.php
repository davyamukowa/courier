<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Bot_flow_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    public function save($post_data) {
        $insert = $update = false;
        if (!empty($post_data['id'])) {
            $update = $this->db->update(db_prefix() . 'wtc_bot_flow', $post_data, ['id' => $post_data['id']]);
        } else {
            $insert = $this->db->insert(db_prefix() . 'wtc_bot_flow', $post_data);
        }
        return [
            'type' => $update || $insert ? 'success' : 'danger',
            'message' => $update ? _l('updated_successfully', _l('flow')) : ($insert ? _l('added_successfully', _l('flow')) : _l('something_went_wrong'))
        ];
    }

    public function get($id = '') {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_bot_flow', ['id' => $id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_bot_flow')->result_array();
    }

    public function delete($id) {
        $res = false;
        if (!empty($id)) {
            $res = $this->db->delete(db_prefix() . 'wtc_bot_flow', ['id' => $id]);
        }
        return [
            'message' => $res ? _l('deleted', _l('bot_flow')) : _l('something_went_wrong'),
        ];
    }
}
