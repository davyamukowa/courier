<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_journal_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all()
    {
        $this->db->order_by('sequence', 'ASC');
        return $this->db->get('acc_journals')->result();
    }

    public function get_all_with_accounts()
    {
        $this->db->select('acc_journals.*, acc_accounts.name as default_account_name');
        $this->db->from('acc_journals');
        $this->db->join('acc_accounts', 'acc_accounts.id = acc_journals.account_id', 'left');
        $this->db->order_by('acc_journals.sequence', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_type($type)
    {
        if (is_array($type)) {
            $this->db->where_in('type', $type);
        } else {
            $this->db->where('type', $type);
        }
        $this->db->order_by('sequence', 'ASC');
        return $this->db->get('acc_journals')->result();
    }

    public function get_dashboard_journals()
    {
        $this->db->where_in('type', ['Bank', 'Cash']);
        $this->db->where('show_on_dashboard', 1);
        $this->db->order_by('sequence', 'ASC');
        return $this->db->get('acc_journals')->result();
    }

    public function save($data, $id = null)
    {
        if (empty($data['name']) || empty($data['type']) || empty($data['code'])) {
            return false;
        }

        $journal_data = [
            'name'                => $data['name'],
            'type'                => $data['type'],
            'code'                => $data['code'],
            'account_id'          => !empty($data['account_id']) ? $data['account_id'] : null,
            'show_on_dashboard'   => isset($data['show_on_dashboard']) ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('acc_journals', $journal_data);
            return $id;
        } else {
            $this->db->insert('acc_journals', $journal_data);
            return $this->db->insert_id();
        }
    }
}
