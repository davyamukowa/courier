<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Courier extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('courier_goshipping/courier'); // Load the helper specific to the courier module
        // MX's model loader lowercases the whole path then only ucfirst()'s
        // the first letter before checking is_file(), so multi-capital
        // filenames like these never resolve on case-sensitive (Linux) fs.
        courier_load_model('CountryState_model');
        $this->load->model('Pickup_model');
        $this->load->model('Shipment_model');
        courier_load_model('CourierCompany_model');
    }

    public function dashboard()
    {
        $staff_id   = get_staff_user_id();
        $branch_ids = $this->get_staff_branch_ids();
        $can_all    = staff_can('view_all_shipments', 'courier-shipments');

        // Total shipments
        $data['total_shipments'] = $can_all
            ? $this->Shipment_model->get_shipment_count_by_status(null, null, $branch_ids)
            : $this->Shipment_model->get_shipment_count_by_status(null, $staff_id, $branch_ids);

        // Pending pickups
        $can_pickups = staff_can('view_all_pickups', 'courier-pickups');
        $data['total_pickups'] = $can_pickups
            ? $this->Pickup_model->get(true, null, null, null, $branch_ids)
            : $this->Pickup_model->get(true, null, $staff_id, null, $branch_ids);

        $data['pending_pickups'] = $can_pickups
            ? $this->Pickup_model->get(true, 'pending', null, null, $branch_ids)
            : $this->Pickup_model->get(true, 'pending', $staff_id, null, $branch_ids);

        // Portal (customer) requests awaiting confirmation (invoice_id = 0 means not yet confirmed)
        $this->db->where('is_portal_request', 1)->where('invoice_id', 0);
        if (!$can_all) { $this->db->where('staff_id', $staff_id); }
        $data['portal_requests'] = (int)$this->db->get(db_prefix() . '_shipments')->num_rows();

        // Courier companies
        $data['courier_company_counts'] = (int)$this->CourierCompany_model->get_company_count_by_type();

        // Per-status shipment counts (single GROUP BY query)
        $this->db->select('s.status_id, COUNT(*) as cnt', false);
        $this->db->from(db_prefix() . '_shipments s');
        if (!$can_all)   { $this->db->where('s.staff_id', $staff_id); }
        if ($branch_ids !== null) { $this->db->where_in('s.branch_id', !empty($branch_ids) ? $branch_ids : [0]); }
        $this->db->group_by('s.status_id');
        $sc_raw = $this->db->get()->result();
        $sc = [];
        foreach ($sc_raw as $r) { $sc[(string)$r->status_id] = (int)$r->cnt; }
        foreach (['1','2','3','4','5','6','7','8','9'] as $sid) { $sc[$sid] = $sc[$sid] ?? 0; }
        $data['status_counts'] = $sc;

        // Today's and this month's totals
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        if (!$can_all) { $this->db->where('staff_id', $staff_id); }
        $data['today_count'] = (int)$this->db->count_all_results(db_prefix() . '_shipments');

        $this->db->where('YEAR(created_at)', date('Y'));
        $this->db->where('MONTH(created_at)', date('n'));
        if (!$can_all) { $this->db->where('staff_id', $staff_id); }
        $data['month_count'] = (int)$this->db->count_all_results(db_prefix() . '_shipments');

        // Recent 8 shipments with sender/recipient names and status
        $recent_q = $this->db
            ->select('sh.id, sh.waybill_number, sh.shipping_mode, sh.status_id, sh.created_at,
                      CONCAT(sn.first_name," ",sn.last_name) AS sender_name,
                      CONCAT(rp.first_name," ",rp.last_name) AS receiver_name,
                      ss.status_name', FALSE)
            ->from(db_prefix() . '_shipments sh')
            ->join(db_prefix() . '_shipment_senders sn',    'sh.sender_id    = sn.id', 'left')
            ->join(db_prefix() . '_shipment_recipients rp', 'sh.recipient_id = rp.id', 'left')
            ->join(db_prefix() . '_shipment_statuses ss',   'sh.status_id    = ss.id', 'left');
        if (!$can_all) { $recent_q->where('sh.staff_id', $staff_id); }
        $data['recent_shipments'] = $recent_q->order_by('sh.id', 'DESC')->limit(8)->get()->result();

        // Status badge classes (keyed by status_name slug)
        $data['status_badge'] = [
            'created'              => 'default',
            'picked_up'            => 'info',
            'received'             => 'primary',
            'dispatched'           => 'primary',
            'in_transit'           => 'warning',
            'arrived_destination'  => 'warning',
            'out_for_delivery'     => 'warning',
            'delivered'            => 'success',
            'cancelled'            => 'danger',
        ];

        $this->load->view('dashboard', $data);
    }

    private function get_staff_branch_ids()
    {
        if (courier_staff_can_view_all_branches()) {
            return null;
        }

        return courier_get_staff_branch_ids();
    }

    public function states()
    {
        // Accept both GET and POST so callers don't need a CSRF token
        $country_id = $this->input->get('country_id') ?: $this->input->post('country_id');

        if ($country_id) {
            $this->db->where('country_id', $country_id);
            $query  = $this->db->get(db_prefix() . '_country_states');
            $states = $query->result_array();
            echo json_encode(['states' => $states, 'country_code' => $this->getCountryCode($country_id)]);
        } else {
            echo json_encode(['states' => [], 'country_code' => null]);
        }
    }


    public function getCountryCode($country_id)
    {
        if (!is_null($country_id)) {
            $country = $this->db->get_where(db_prefix().'countries', ['country_id' => $country_id])->row();
            return $country ? $country->calling_code : null;
        }
        return null;
    }

    /**
     * AJAX: return the branch(es) assigned to a staff member.
     */
    public function get_staff_branches()
    {
        if (!is_staff_logged_in()) {
            echo json_encode(['branch_ids' => []]);
            return;
        }
        $staff_id = (int) $this->input->get('staff_id');
        if ($staff_id <= 0) {
            $staff_id = get_staff_user_id();
        }
        echo json_encode(['branch_ids' => courier_get_staff_branch_ids($staff_id)]);
    }

}