<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Branches extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !has_permission('courier-branches', '', 'view_branches')) {
            access_denied('Courier - Branches');
        }
        $this->load->helper('courier_goshipping/courier');
        // MX's model loader lowercases the whole path then only ucfirst()'s the
        // first letter when checking is_file(), so it looks for
        // "Courierbranch_model.php" / "Countrystate_model.php" — which never
        // matches these mixed-case filenames on a case-sensitive (Linux) fs,
        // even though it silently works on case-insensitive Windows dev boxes.
        courier_load_model('CourierBranch_model');
        courier_load_model('CountryState_model');
        $this->load->library('form_validation');
    }

    public function main()
    {
        $data['branches'] = $this->CourierBranch_model->get();
        $data['countries'] = $this->db->order_by('short_name', 'asc')->get(db_prefix() . 'countries')->result();
        $data['next_branch_sequence'] = $this->get_next_branch_sequence();
        $data['title'] = 'Branches / Offices';
        $data['can_manage'] = is_admin() || has_permission('courier-branches', '', 'create_branches') || has_permission('courier-branches', '', 'edit_branches');
        $this->load->view('courier_goshipping/branches/main', $data);
    }

    public function store()
    {
        if (!is_admin() && !has_permission('courier-branches', '', 'create_branches')) {
            ajax_access_denied();
        }

        $this->form_validation->set_rules('name', 'Branch Name', 'required');
        $this->form_validation->set_rules('branch_type', 'Branch Type', 'required');

        if ($this->form_validation->run() === false) {
            echo json_encode(['success' => false, 'message' => strip_tags(validation_errors())]);
            return;
        }

        $branch_name = trim((string) $this->input->post('name', true));
        $data = [
            'name'        => $branch_name,
            'code'        => $this->generate_branch_code($branch_name),
            'branch_type' => $this->input->post('branch_type', true),
            'country_id'  => $this->input->post('country_id', true) ?: null,
            'city'        => $this->input->post('city', true),
            'address'     => $this->input->post('address', true),
            'phone'       => $this->input->post('phone', true),
            'email'       => $this->input->post('email', true),
            'is_active'   => 1,
            'is_default'  => $this->input->post('is_default') ? 1 : 0,
        ];

        $id = $this->CourierBranch_model->add($data);
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Branch created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create branch. The code may already be in use.']);
        }
    }

    public function update($id)
    {
        if (!is_admin() && !has_permission('courier-branches', '', 'edit_branches')) {
            ajax_access_denied();
        }

        $branch = $this->CourierBranch_model->get((int) $id);
        if (!$branch) {
            echo json_encode(['success' => false, 'message' => 'Branch not found.']);
            return;
        }

        $branch_name = trim((string) $this->input->post('name', true));
        $data = [
            'name'        => $branch_name,
            'code'        => !empty($branch->code) ? $branch->code : $this->generate_branch_code($branch_name, (int) $id),
            'branch_type' => $this->input->post('branch_type', true),
            'country_id'  => $this->input->post('country_id', true) ?: null,
            'city'        => $this->input->post('city', true),
            'address'     => $this->input->post('address', true),
            'phone'       => $this->input->post('phone', true),
            'email'       => $this->input->post('email', true),
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
            'is_default'  => $this->input->post('is_default') ? 1 : 0,
        ];

        if ($this->CourierBranch_model->update((int) $id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Branch updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update branch.']);
        }
    }

    public function delete($id)
    {
        if (!is_admin() && !has_permission('courier-branches', '', 'delete_branches')) {
            access_denied('Courier - Branches');
        }

        if ($this->CourierBranch_model->get_staff_count((int) $id) > 0) {
            set_alert('danger', 'Cannot delete a branch that still has staff assigned to it. Reassign staff first.');
            redirect(admin_url('courier_goshipping/branches/main'));
            return;
        }

        $this->CourierBranch_model->delete((int) $id);
        set_alert('success', 'Branch deleted.');
        redirect(admin_url('courier_goshipping/branches/main'));
    }

    public function cities_by_country()
    {
        if (!is_staff_logged_in()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['cities' => []]));
            return;
        }

        $country_id = (int) $this->input->get('country_id');
        $rows = [];

        if ($country_id > 0) {
            $rows = $this->db
                ->select('name')
                ->from(db_prefix() . '_country_states')
                ->where('country_id', $country_id)
                ->order_by('name', 'asc')
                ->get()
                ->result();
        }

        $cities = array_values(array_unique(array_filter(array_map(static function ($row) {
            return trim((string) ($row->name ?? ''));
        }, $rows))));

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['cities' => $cities]));
    }

    private function generate_branch_code($branch_name, $exclude_id = null)
    {
        $prefix = strtoupper(trim((string) preg_replace('/\s+/', '-', preg_replace('/[^A-Za-z0-9]+/', ' ', $branch_name))));
        if ($prefix === '') {
            $prefix = 'BRANCH';
        }

        $year = date('Y');
        $sequence = $this->get_next_branch_sequence($year, $exclude_id);

        return $prefix . '/B/' . $year . '/' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    private function get_next_branch_sequence($year = null, $exclude_id = null)
    {
        $year = $year ?: date('Y');
        $query = $this->db
            ->select('id, code')
            ->from(db_prefix() . '_courier_branches')
            ->like('code', '/B/' . $year . '/', 'both');

        if ($exclude_id !== null) {
            $query->where('id !=', (int) $exclude_id);
        }

        $rows = $query->get()->result();
        $max_sequence = 0;

        foreach ($rows as $row) {
            if (preg_match('#/B/' . preg_quote((string) $year, '#') . '/(\d+)$#', (string) ($row->code ?? ''), $matches)) {
                $max_sequence = max($max_sequence, (int) $matches[1]);
            }
        }

        return $max_sequence + 1;
    }
}
