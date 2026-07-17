<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_label_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($postData)
    {
        $update = $save = false;
        if (!empty($postData['id'])) {
            $update = $this->db->update(db_prefix() . 'wtc_custom_label', $postData, ['id' => $postData['id']]);
        } else {
            $save = $this->db->insert(db_prefix() . 'wtc_custom_label', $postData);
        }
        return [
            'type' => ($save || $update) ? 'success' : 'danger',
            'message' => ($save || $update) ? (($save) ? _l('added_successfully', _l('custom_label')) : _l('updated_successfully', _l('custom_label'))) : _l('something_went_wrong')
        ];
    }

    public function delete($id)
    {
        $labels = total_rows(db_prefix() . 'wtc_interactions', ['label' => $id]);
        $delete = false;
        if ($labels == 0) {
            $delete = $this->db->delete(db_prefix() . 'wtc_custom_label', ['id' => $id]);
        }
        return [
            'type' => 'danger',
            'message' => $delete ? _l('deleted', _l('custom_label')) : _l('delete_not_allowed_labels')
        ];
    }

    public function labelExist($postData)
    {
        $where['label'] = $postData['label'];

        if (!empty($postData['id'])) {
            $where['id !='] = $postData['id'];
        }

        $status = $this->db->where($where)->count_all_results(db_prefix() . 'wtc_custom_label');

        return ($status > 0) ? false : true;
    }

    public function getData($id)
    {
        $data = $this->db->get_where(db_prefix() . 'wtc_custom_label', ['id' => $id])->row_array();
        return $data;
    }
}
