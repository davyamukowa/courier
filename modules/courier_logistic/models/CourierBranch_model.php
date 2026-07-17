<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CourierBranch_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . '_courier_branches';
    }

    public function get($id = null)
    {
        if ($id !== null) {
            return $this->db->where('id', (int) $id)->get($this->table)->row();
        }

        return $this->db->order_by('branch_type', 'asc')
            ->order_by('name', 'asc')
            ->get($this->table)
            ->result();
    }

    public function add($data)
    {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }

        log_message('error', 'Insert failed for courier branch: ' . $this->db->last_query());
        return false;
    }

    public function update($id, $data)
    {
        if (!empty($data['is_default'])) {
            // Only one default branch org-wide.
            $this->db->update($this->table, ['is_default' => 0]);
        }

        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function get_staff_count($branch_id)
    {
        return (int) $this->db->where('branch_id', (int) $branch_id)
            ->count_all_results(db_prefix() . '_courier_staff_branches');
    }
}
