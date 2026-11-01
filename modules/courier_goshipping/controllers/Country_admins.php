<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SUPER USER ONLY — appoint / remove the per-country admins.
 *
 * A country admin is an ordinary (non-administrator) staff member linked to a
 * country in tbl_courier_country_admins; they can add and manage staff and
 * agents for that country only (see Country_team + the guards in Agents) and
 * are scoped to that country's branches by the module's normal branch
 * isolation. Only a Perfex administrator can reach this controller.
 */
class Country_admins extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('Courier - Country Admins');
        }
        $this->load->helper('courier_goshipping/courier');
        $this->load->library('form_validation');
        courier_ensure_country_admins_table();
    }

    public function index()
    {
        $p = db_prefix();
        $data['title'] = 'Country Admins';

        $data['admins'] = $this->db
            ->select('ca.id, ca.created_at, c.short_name AS country_name, s.staffid, s.firstname, s.lastname, s.email, s.active')
            ->from($p . '_courier_country_admins ca')
            ->join($p . 'staff s', 's.staffid = ca.staff_id', 'left')
            ->join($p . 'countries c', 'c.country_id = ca.country_id', 'left')
            ->order_by('c.short_name', 'asc')
            ->get()->result();

        // Countries that actually have at least one branch (a country admin needs one).
        $data['countries'] = $this->db
            ->select('c.country_id, c.short_name, COUNT(b.id) AS branch_count', false)
            ->from($p . '_courier_branches b')
            ->join($p . 'countries c', 'c.country_id = b.country_id')
            ->group_by('c.country_id')
            ->order_by('c.short_name', 'asc')
            ->get()->result();

        $data['branches'] = $this->db->select('id, name, country_id')->order_by('name', 'asc')
            ->get($p . '_courier_branches')->result();

        // Staff who could be appointed: active, not super users, not already a country admin.
        $data['staff'] = $this->db
            ->select('staffid, firstname, lastname, email')
            ->where('admin', 0)->where('active', 1)
            ->where('staffid NOT IN (SELECT staff_id FROM ' . $p . '_courier_country_admins)', null, false)
            ->where("email NOT LIKE '%@riders.invalid'", null, false)
            ->order_by('firstname', 'asc')
            ->get($p . 'staff')->result();

        $this->load->view('country_admins/index', $data);
    }

    public function appoint()
    {
        if (!$this->input->post()) {
            redirect(admin_url('courier_goshipping/country_admins'));
        }
        $p          = db_prefix();
        $country_id = (int) $this->input->post('country_id');
        $branch_id  = (int) $this->input->post('home_branch_id');
        $mode       = $this->input->post('mode') === 'new' ? 'new' : 'existing';

        $branch = $this->db->where('id', $branch_id)->where('country_id', $country_id)->get($p . '_courier_branches')->row();
        if (!$country_id || !$branch) {
            set_alert('danger', 'Pick a country and one of its branches (the admin\'s home branch). Create a branch for the country first if it has none.');
            redirect(admin_url('courier_goshipping/country_admins'));
        }

        $role_id = courier_ensure_country_admin_role();

        if ($mode === 'new') {
            $first = trim((string) $this->input->post('firstname'));
            $last  = trim((string) $this->input->post('lastname'));
            $email = trim((string) $this->input->post('email'));
            $pass  = (string) $this->input->post('password');
            if ($first === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
                set_alert('danger', 'New admin needs a first name, a valid email and a password of at least 6 characters.');
                redirect(admin_url('courier_goshipping/country_admins'));
            }
            if ($this->db->where('email', $email)->count_all_results($p . 'staff') > 0) {
                set_alert('danger', 'That email already belongs to a staff member — use "Existing staff" instead.');
                redirect(admin_url('courier_goshipping/country_admins'));
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
                redirect(admin_url('courier_goshipping/country_admins'));
            }
        } else {
            $staff_id = (int) $this->input->post('staff_id');
            $staff    = $this->db->where('staffid', $staff_id)->get($p . 'staff')->row();
            if (!$staff || (int) $staff->admin === 1) {
                set_alert('danger', 'Choose an existing non-administrator staff member.');
                redirect(admin_url('courier_goshipping/country_admins'));
            }
            $this->db->where('staffid', $staff_id)->update($p . 'staff', ['role' => $role_id]);
        }

        courier_grant_role_permissions($staff_id, $role_id);
        courier_record_staff_creator($staff_id);

        $this->db->insert($p . '_courier_country_admins', [
            'staff_id'   => $staff_id,
            'country_id' => $country_id,
            'created_by' => get_staff_user_id(),
        ]);

        // Home branch: stamps the records they create. Their reach is the whole country regardless.
        $has_home = $this->db->where(['staff_id' => $staff_id, 'branch_id' => $branch_id])->count_all_results($p . '_courier_staff_branches');
        if (!$has_home) {
            $has_default = $this->db->where(['staff_id' => $staff_id, 'is_default' => 1])->count_all_results($p . '_courier_staff_branches');
            $this->db->insert($p . '_courier_staff_branches', [
                'staff_id'   => $staff_id,
                'branch_id'  => $branch_id,
                'is_default' => $has_default ? 0 : 1,
            ]);
        }

        set_alert('success', 'Country admin appointed.');
        redirect(admin_url('courier_goshipping/country_admins'));
    }

    public function remove($id)
    {
        $p   = db_prefix();
        $row = $this->db->where('id', (int) $id)->get($p . '_courier_country_admins')->row();
        if (!$row) {
            set_alert('danger', 'Not found.');
            redirect(admin_url('courier_goshipping/country_admins'));
        }
        $this->db->where('id', (int) $id)->delete($p . '_courier_country_admins');

        // If this was their last country, demote them fully: only when their role is still the
        // Country Admin role, strip the permissions it granted and clear the role.
        $remaining = $this->db->where('staff_id', $row->staff_id)->count_all_results($p . '_courier_country_admins');
        if ($remaining === 0) {
            $role = $this->db->where('name', 'Courier: Country Admin')->get($p . 'roles')->row();
            $staff = $this->db->where('staffid', $row->staff_id)->get($p . 'staff')->row();
            if ($role && $staff && (int) $staff->role === (int) $role->roleid) {
                $granted = $role->permissions ? unserialize($role->permissions) : [];
                foreach ($granted as $feature => $caps) {
                    $this->db->where('staff_id', $row->staff_id)->where('feature', $feature)
                        ->where_in('capability', $caps)->delete($p . 'staff_permissions');
                }
                $this->db->where('staffid', $row->staff_id)->update($p . 'staff', ['role' => 0]);
            }
        }

        set_alert('success', 'Country admin removed.');
        redirect(admin_url('courier_goshipping/country_admins'));
    }
}
