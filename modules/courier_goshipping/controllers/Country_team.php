<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * COUNTRY ADMIN — staff of their own country.
 *
 * Lets a country admin add and manage the (non-agent) staff of the country
 * they administer. Agents are managed in Agents (which applies the same
 * country scope). Everything here is limited to branches in the admin's
 * country(ies); super users (Perfex administrators) and other country admins
 * can never be touched from here, and only module roles can be assigned.
 */
class Country_team extends AdminController
{
    private $country_ids = [];
    private $branch_ids  = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('courier_goshipping/courier');
        if (!courier_is_country_admin()) {
            access_denied('Courier - Country Team');
        }
        $this->country_ids = courier_get_admin_country_ids();
        $this->branch_ids  = courier_get_country_branch_ids($this->country_ids);
    }

    /** Staff member in scope and editable by this admin, or redirects away. */
    private function scoped_staff($id)
    {
        $p     = db_prefix();
        $staff = $this->db->where('staffid', (int) $id)->get($p . 'staff')->row();
        $is_agent = $staff && $this->db->where('staff_id', $staff->staffid)->count_all_results($p . '_agents') > 0;
        $is_ca    = $staff && !empty(courier_get_admin_country_ids($staff->staffid));
        if (!$staff || (int) $staff->admin === 1 || $is_ca || $is_agent
            || (int) $staff->staffid === (int) get_staff_user_id()
            || !courier_staff_in_admin_scope($staff->staffid)) {
            set_alert('danger', 'That staff member is not in your country team.');
            redirect(admin_url('courier_goshipping/country_team'));
        }
        return $staff;
    }

    /**
     * Document permissions a country admin can set on their staff: View Own, or
     * View All — where "all" is scoped to THEIR country (the branch-level
     * capability), never every country.
     */
    private function doc_perm_map()
    {
        return [
            'courier-manifests'           => ['label' => 'Manifests',          'own' => 'view_own_manifests',           'all' => 'view_branch_manifests',           'global' => 'view_manifests'],
            'courier-commercial-invoices' => ['label' => 'Commercial Invoices', 'own' => 'view_own_commercial_invoices', 'all' => 'view_branch_commercial_invoices', 'global' => 'view_all_commercial_invoices'],
            'courier-invoices'            => ['label' => 'Courier Invoices',    'own' => 'view_own_invoices',            'all' => 'view_branch_invoices',            'global' => 'view_invoices'],
        ];
    }

    private function country_branches()
    {
        return $this->db->select('id, name')->where_in('id', $this->branch_ids ?: [0])
            ->order_by('name', 'asc')->get(db_prefix() . '_courier_branches')->result();
    }

    public function index()
    {
        $p = db_prefix();
        $data['title'] = 'Country Team';
        $data['country_name'] = implode(', ', array_column(
            $this->db->select('short_name')->where_in('country_id', $this->country_ids)->get($p . 'countries')->result_array(),
            'short_name'
        ));

        $ca_staff = $this->db->select('staff_id')->get($p . '_courier_country_admins')->result_array();
        $exclude  = array_map('intval', array_column($ca_staff, 'staff_id'));
        $exclude[] = (int) get_staff_user_id();

        $this->db->select('s.staffid, s.firstname, s.lastname, s.email, s.phonenumber, s.active, r.name AS role_name, GROUP_CONCAT(DISTINCT b.name SEPARATOR ", ") AS branches', false)
            ->from($p . 'staff s')
            ->join($p . '_courier_staff_branches sb', 'sb.staff_id = s.staffid')
            ->join($p . '_courier_branches b', 'b.id = sb.branch_id')
            ->join($p . 'roles r', 'r.roleid = s.role', 'left')
            ->where_in('sb.branch_id', $this->branch_ids ?: [0])
            ->where('s.admin', 0)
            ->where_not_in('s.staffid', $exclude)
            ->where('s.staffid NOT IN (SELECT staff_id FROM ' . $p . '_agents)', null, false)
            ->where("s.email NOT LIKE '%@riders.invalid'", null, false)
            ->group_by('s.staffid')
            ->order_by('s.firstname', 'asc');
        $data['team'] = $this->db->get()->result();

        $agent_count = $this->db->from($p . '_agents')
            ->where_in('branch_id', $this->branch_ids ?: [0])->count_all_results();
        $data['agent_count'] = $agent_count;

        $this->load->view('country_team/index', $data);
    }

    public function create()
    {
        $data['title']    = 'Add staff';
        $data['member']   = null;
        $data['roles']    = courier_country_admin_allowed_roles();
        $data['branches'] = $this->country_branches();
        $this->load->view('country_team/form', $data);
    }

    public function store()
    {
        if (!$this->input->post()) {
            redirect(admin_url('courier_goshipping/country_team'));
        }
        $p        = db_prefix();
        $first    = trim((string) $this->input->post('firstname'));
        $last     = trim((string) $this->input->post('lastname'));
        $email    = trim((string) $this->input->post('email'));
        $pass     = (string) $this->input->post('password');
        $role_id  = (int) $this->input->post('role_id');
        $branch_id = (int) $this->input->post('branch_id');

        $role_ok   = in_array($role_id, array_map('intval', array_column(courier_country_admin_allowed_roles(), 'roleid')), true);
        $branch_ok = in_array($branch_id, $this->branch_ids, true);

        $error = null;
        if ($first === '') {
            $error = 'First name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'A valid email is required.';
        } elseif (strlen($pass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!$role_ok) {
            $error = 'Choose a role.';
        } elseif (!$branch_ok) {
            $error = 'Choose a branch in your country.';
        } elseif ($this->db->where('email', $email)->count_all_results($p . 'staff') > 0) {
            $error = 'That email already exists in the system.';
        }
        if ($error) {
            // Keep everything typed (password excluded) and show the error.
            $this->session->set_flashdata('country_team_old', $this->input->post());
            set_alert('danger', $error);
            redirect(admin_url('courier_goshipping/country_team/create'));
        }

        $this->load->model('staff_model');
        $staff_id = $this->staff_model->add([
            'firstname'   => $first,
            'lastname'    => $last,
            'email'       => $email,
            'password'    => $pass,
            'phonenumber' => trim((string) $this->input->post('phonenumber')),
            'role'        => $role_id,
            'active'      => 1,
        ]);
        if (!$staff_id) {
            set_alert('danger', 'Could not create the staff account.');
            redirect(admin_url('courier_goshipping/country_team/create'));
        }

        courier_grant_role_permissions($staff_id, $role_id);
        courier_record_staff_creator($staff_id);
        $this->db->insert($p . '_courier_staff_branches', ['staff_id' => $staff_id, 'branch_id' => $branch_id, 'is_default' => 1]);

        set_alert('success', 'Staff member added.');
        redirect(admin_url('courier_goshipping/country_team'));
    }

    public function edit($id)
    {
        $member = $this->scoped_staff($id);
        $branch = $this->db->where('staff_id', $member->staffid)->where_in('branch_id', $this->branch_ids ?: [0])
            ->order_by('is_default', 'desc')->get(db_prefix() . '_courier_staff_branches')->row();
        $member->branch_id = $branch ? (int) $branch->branch_id : 0;

        $granted = [];
        foreach ($this->db->where('staff_id', $member->staffid)->get(db_prefix() . 'staff_permissions')->result() as $r) {
            $granted[$r->feature][] = $r->capability;
        }
        $perm_state = [];
        foreach ($this->doc_perm_map() as $feature => $m) {
            $has = $granted[$feature] ?? [];
            $perm_state[$feature] = [
                'label' => $m['label'],
                'own'   => in_array($m['own'], $has, true),
                'all'   => in_array($m['all'], $has, true) || in_array($m['global'], $has, true),
            ];
        }
        $data['doc_perms'] = $perm_state;
        $data['country_name'] = implode(', ', array_column(
            $this->db->select('short_name')->where_in('country_id', $this->country_ids)->get(db_prefix() . 'countries')->result_array(),
            'short_name'
        ));
        $data['title']    = 'Edit staff';
        $data['member']   = $member;
        $data['roles']    = courier_country_admin_allowed_roles();
        $data['branches'] = $this->country_branches();
        $this->load->view('country_team/form', $data);
    }

    public function update($id)
    {
        $member = $this->scoped_staff($id);
        if (!$this->input->post()) {
            redirect(admin_url('courier_goshipping/country_team/edit/' . $member->staffid));
        }
        $p         = db_prefix();
        $first     = trim((string) $this->input->post('firstname'));
        $role_id   = (int) $this->input->post('role_id');
        $branch_id = (int) $this->input->post('branch_id');
        $role_ok   = in_array($role_id, array_map('intval', array_column(courier_country_admin_allowed_roles(), 'roleid')), true);

        if ($first === '' || !$role_ok || !in_array($branch_id, $this->branch_ids, true)) {
            set_alert('danger', 'First name, a valid role and a branch in your country are required.');
            redirect(admin_url('courier_goshipping/country_team/edit/' . $member->staffid));
        }

        $this->db->where('staffid', $member->staffid)->update($p . 'staff', [
            'firstname'   => $first,
            'lastname'    => trim((string) $this->input->post('lastname')),
            'phonenumber' => trim((string) $this->input->post('phonenumber')),
            'role'        => $role_id,
        ]);

        // A role change re-applies that role's permission set.
        if ((int) $member->role !== $role_id) {
            $this->db->where('staff_id', $member->staffid)->delete($p . 'staff_permissions');
            courier_grant_role_permissions($member->staffid, $role_id);
        }

        // Move the staff member to the chosen branch (only touches branches inside this country).
        $this->db->where('staff_id', $member->staffid)->where_in('branch_id', $this->branch_ids ?: [0])
            ->delete($p . '_courier_staff_branches');
        $this->db->insert($p . '_courier_staff_branches', ['staff_id' => $member->staffid, 'branch_id' => $branch_id, 'is_default' => 1]);

        set_alert('success', 'Staff member updated.');
        redirect(admin_url('courier_goshipping/country_team'));
    }

    public function save_permissions($id)
    {
        $member = $this->scoped_staff($id);
        $posted = $this->input->post('perm');   // perm[feature][] = own|all
        $posted = is_array($posted) ? $posted : [];
        $p      = db_prefix();

        foreach ($this->doc_perm_map() as $feature => $m) {
            // Only this feature's own/all(branch) rows are touched; nothing else of theirs changes.
            $this->db->where('staff_id', $member->staffid)->where('feature', $feature)
                ->where_in('capability', [$m['own'], $m['all']])->delete($p . 'staff_permissions');
            $choice = isset($posted[$feature]) && is_array($posted[$feature]) ? $posted[$feature] : [];
            if (in_array('own', $choice, true)) {
                $this->db->insert($p . 'staff_permissions', ['staff_id' => $member->staffid, 'feature' => $feature, 'capability' => $m['own']]);
            }
            if (in_array('all', $choice, true)) {
                $this->db->insert($p . 'staff_permissions', ['staff_id' => $member->staffid, 'feature' => $feature, 'capability' => $m['all']]);
            }
        }
        set_alert('success', 'Permissions saved.');
        redirect(admin_url('courier_goshipping/country_team/edit/' . $member->staffid));
    }

    public function toggle_active($id)
    {
        $member = $this->scoped_staff($id);
        $this->db->where('staffid', $member->staffid)->update(db_prefix() . 'staff', ['active' => $member->active ? 0 : 1]);
        set_alert('success', $member->active ? 'Staff member deactivated.' : 'Staff member activated.');
        redirect(admin_url('courier_goshipping/country_team'));
    }

    public function reset_password($id)
    {
        $member = $this->scoped_staff($id);
        $pass   = (string) $this->input->post('new_password');
        if (strlen($pass) < 6) {
            set_alert('danger', 'Password must be at least 6 characters.');
        } else {
            $this->db->where('staffid', $member->staffid)->update(db_prefix() . 'staff', ['password' => app_hash_password($pass)]);
            set_alert('success', 'Password reset.');
        }
        redirect(admin_url('courier_goshipping/country_team'));
    }
}
