<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CourierCompany_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . '_courier_companies';

    }

    public function get($return_count = false, $type = null, $branch_ids = null): array|bool|int|string
    {
        // Select fields from the company table
        $this->db->select('c.*, cp.first_name as contact_person_first_name, cp.last_name as contact_person_last_name, cp.phone_number as contact_person_phone_number, cp.email as contact_person_email');
        $this->db->from(db_prefix() .'_courier_companies c');
        $this->db->join(db_prefix() .'_contact_persons cp', 'c.id = cp.company_id', 'left'); // Join with the contact persons table

        if ($type !== null) {
            $this->db->where('c.type', $type);
        }

        // branch_ids === null means "can view all branches" (no filter).
        // Otherwise show that staff's own branch companies PLUS shared/global
        // ones (branch_id NULL — the original carrier partners seeded before
        // per-branch companies existed, e.g. GO Shipping, FedEx, DHL).
        if ($branch_ids !== null) {
            $this->db->group_start();
            $this->db->where_in('c.branch_id', !empty($branch_ids) ? (array) $branch_ids : [0]);
            $this->db->or_where('c.branch_id IS NULL', null, false);
            $this->db->group_end();
        }

        if ($return_count) {
            return $this->db->count_all_results();
        } else {
            $query = $this->db->get();
            return $query->num_rows() > 0 ? $query->result() : false;
        }
    }

    public function get_company_count_by_type($type = null, $branch_ids = null): array|bool|int|string
    {
        return $this->get(true, $type, $branch_ids);
    }

    /**
     * Whether the given branch(es) may modify/delete this company record —
     * true for shared/global companies (branch_id NULL, e.g. legacy carrier
     * partners) so any branch can still use them, or when the company
     * belongs to one of the staff's own branches.
     */
    public function belongs_to_branch($company, $branch_ids)
    {
        if ($branch_ids === null) {
            return true; // staff can view/manage all branches
        }
        if (empty($company->branch_id)) {
            return true; // shared/global company
        }
        return in_array((int) $company->branch_id, array_map('intval', (array) $branch_ids), true);
    }


    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }


    public function add($data): bool|int
    {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        } else {
            // Log the error for debugging purposes
            log_message('error', 'Insert failed for courier company: ' . $this->db->last_query());
            return false;
        }
    }

    public function update($id, $data): bool
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
