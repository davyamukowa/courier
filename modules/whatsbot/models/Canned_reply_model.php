<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Canned_reply_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add($posted_data)
    {
        $insert = $update = false;

        $posted_data['added_from'] = $posted_data['added_from'] ?? get_staff_user_id();
        if (isset($posted_data['description'])) {
            $posted_data['description'] = strip_tags($posted_data['description']);
        }

        if (empty($posted_data['id'])) {
            $insert = $this->db->insert(db_prefix() . 'wtc_canned_reply', $posted_data);
        } else {
            unset($posted_data['added_from']);
            $update = $this->db->update(db_prefix() . 'wtc_canned_reply', $posted_data, ['id' => $posted_data['id']]);
        }

        $type = ($insert || $update) ? 'success' : 'danger';
        $message = _l('something_went_wrong');

        if ($type) {
            $message = ($insert) ? _l('added_successfully', _l('reply')) : _l('updated_successfully', _l('reply'));
        }

        return ['type' => $type, 'message' => $message];
    }

    public function delete_reply($posted_data)
    {
        $this->db->delete(db_prefix() . 'wtc_canned_reply', ['id' => $posted_data['id']]);

        $delete = ($this->db->affected_rows() > 0) ? true : false;

        $message = ($delete) ? _l('deleted', _l('reply')) : _l('something_went_wrong');

        return ['type' => 'danger', 'message' => $message, 'status' => ($delete) ? true : false];
    }

    public function update_status($id, $status)
    {
        $data = $this->db->get_where(
            db_prefix() . 'wtc_canned_reply',
            ['id' => $id]
        )->row_array();

        if ($data['added_from'] == get_staff_user_id()) {
            $updated =  $this->db->update(db_prefix() . 'wtc_canned_reply', ['is_public' => $status], ['id' => $id]);
        } else {
            $updated = false;
        }

        if ($updated == true) {
            return ['type' => 'success', 'message' => _l('updated_successfully')];
        } else {
            return ['type' => 'danger', 'message' => _l('access_denied')];
        }
    }

    public function get($id = '')
    {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_canned_reply', ['id' => $id])->row_array();
        }
        return $this->db->order_by('is_public', 'desc')->get(db_prefix() . 'wtc_canned_reply')->result_array();
    }
}
