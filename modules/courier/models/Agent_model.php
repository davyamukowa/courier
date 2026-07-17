<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Agent_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . '_agents';
    }

    public function get($id = null)
    {
        $this->db->select(db_prefix().'_agents.*, '.db_prefix().'staff.firstname, '.db_prefix().'staff.lastname, '.db_prefix().'staff.email, '.db_prefix().'staff.active as staff_active, '.db_prefix().'countries.short_name as country_name');
        $this->db->join(db_prefix().'staff', db_prefix().'_agents.staff_id = '.db_prefix().'staff.staffid', 'left');
        $this->db->join(db_prefix().'countries', db_prefix().'_agents.country_id = '.db_prefix().'countries.country_id', 'left');

        if ($id) {
            $this->db->where(db_prefix().'_agents.id', $id);
            return $this->db->get($this->table)->row();
        }

        return $this->db->get($this->table)->result();
    }

    public function get_single($id)
    {
        $this->db->select(db_prefix().'_agents.*, '.db_prefix().'staff.firstname, '.db_prefix().'staff.lastname, '.db_prefix().'staff.email, '.db_prefix().'staff.phonenumber, '.db_prefix().'staff.active as staff_active, '.db_prefix().'staff.profile_image, '.db_prefix().'countries.short_name as country_name');
        $this->db->join(db_prefix().'staff', db_prefix().'_agents.staff_id = '.db_prefix().'staff.staffid', 'left');
        $this->db->join(db_prefix().'countries', db_prefix().'_agents.country_id = '.db_prefix().'countries.country_id', 'left');
        $this->db->where(db_prefix().'_agents.id', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_stats($staff_id)
    {
        $s_table = db_prefix() . '_shipments';
        $inv_table = db_prefix() . 'invoices';
        $pay_table = db_prefix() . 'invoicepaymentrecords';

        $total_shipments = $this->db->where('staff_id', $staff_id)->count_all_results($s_table);

        $this->db->select('COALESCE(SUM(i.total), 0) as total_revenue', false);
        $this->db->from($s_table . ' s');
        $this->db->join($inv_table . ' i', 'i.id = s.invoice_id', 'left');
        $this->db->where('s.staff_id', $staff_id);
        $revenue_row   = $this->db->get()->row();
        $total_revenue = $revenue_row ? $revenue_row->total_revenue : 0;

        $this->db->select('COALESCE(SUM(p.amount), 0) as total_paid', false);
        $this->db->from($s_table . ' s');
        $this->db->join($pay_table . ' p', 'p.invoiceid = s.invoice_id', 'left');
        $this->db->where('s.staff_id', $staff_id);
        $paid_row   = $this->db->get()->row();
        $total_paid = $paid_row ? $paid_row->total_paid : 0;

        return [
            'total_shipments' => (int)$total_shipments,
            'total_revenue'   => (float)$total_revenue,
            'total_paid'      => (float)$total_paid,
            'outstanding'     => (float)$total_revenue - (float)$total_paid,
        ];
    }

    public function get_shipments($staff_id, $limit = 50, $offset = 0)
    {
        $s_table  = db_prefix() . '_shipments';
        $sn_table = db_prefix() . '_shipment_senders';
        $rp_table = db_prefix() . '_shipment_recipients';
        $st_table = db_prefix() . '_shipment_statuses';

        $this->db->select('s.id, s.waybill_number, s.shipping_mode, s.created_at, CONCAT(sn.first_name," ",sn.last_name) as sender_name, CONCAT(rp.first_name," ",rp.last_name) as recipient_name, st.status_name', false);
        $this->db->from($s_table . ' s');
        $this->db->join($sn_table . ' sn', 'sn.id = s.sender_id', 'left');
        $this->db->join($rp_table . ' rp', 'rp.id = s.recipient_id', 'left');
        $this->db->join($st_table . ' st', 'st.id = s.status_id', 'left');
        $this->db->where('s.staff_id', $staff_id);
        $this->db->order_by('s.created_at', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function get_invoices($staff_id, $limit = 50, $offset = 0)
    {
        $s_table   = db_prefix() . '_shipments';
        $inv_table = db_prefix() . 'invoices';
        $pay_table = db_prefix() . 'invoicepaymentrecords';

        $this->db->select('s.id, s.waybill_number, s.created_at, COALESCE(i.total, 0) as invoice_amount, COALESCE(SUM(p.amount), 0) as paid_amount', false);
        $this->db->from($s_table . ' s');
        $this->db->join($inv_table . ' i', 'i.id = s.invoice_id', 'left');
        $this->db->join($pay_table . ' p', 'p.invoiceid = s.invoice_id', 'left');
        $this->db->where('s.staff_id', $staff_id);
        $this->db->group_by('s.id');
        $this->db->order_by('s.created_at', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function update_agent($id, $data): bool
    {
        $agent_fields = ['commission_rate', 'admin_notes', 'suspended_reason', 'suspended_at', 'status', 'station', 'country_id'];
        $staff_fields = ['firstname', 'lastname', 'email', 'phonenumber'];

        $agent_data = array_intersect_key($data, array_flip($agent_fields));
        $staff_data = array_intersect_key($data, array_flip($staff_fields));

        $agent = $this->get_single($id);
        if (!$agent) return false;

        if (!empty($agent_data)) {
            $this->db->where('id', $id);
            $this->db->update($this->table, $agent_data);
        }

        if (!empty($staff_data) && $agent->staff_id) {
            $this->db->where('staffid', $agent->staff_id);
            $this->db->update(db_prefix() . 'staff', $staff_data);
        }

        return true;
    }

    public function reset_password($staff_id, $hashed_password): bool
    {
        $this->db->where('staffid', $staff_id);
        return $this->db->update(db_prefix() . 'staff', ['password' => $hashed_password]);
    }

    public function add($data): bool|int
    {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        log_message('error', 'Insert failed for agent: ' . $this->db->last_query());
        return false;
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
