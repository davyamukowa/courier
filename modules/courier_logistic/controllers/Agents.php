<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Agents extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !has_permission('courier-agents', '', 'view_agents')) {
            access_denied('Courier - Agents');
        }
        $this->load->helper('courier_logistic/courier'); // Load the helper specific to the courier module
        $this->load->model('Agent_model');
        $this->load->model('Shipment_model');
        $this->load->library('form_validation');

    }


    public function main()
    {

        $group = $this->input->get('group', true) ?? 'dashboard';

        switch ($group) {
            case 'create_agent':
                $data['title']   = _l('Create Agent');
                $data['countries'] = $this->Shipment_model->get_countries();
                $data['roles']   = $this->db->get(db_prefix() . 'roles')->result();
                $agent_role      = $this->db->where('name', 'Courier: Agent')->get(db_prefix() . 'roles')->row();
                $data['courier_agent_role_id'] = $agent_role ? $agent_role->roleid : null;
                $data['group_content'] = $this->load->view('agents/create', $data, true);
                break;

            default:
                $branch_ids = courier_staff_can_view_all_branches() ? null : courier_get_staff_branch_ids();
                $data['agents'] = $this->Agent_model->get(null, $branch_ids);
                $data['title'] = _l('List agents');
                $data['group_content'] = $this->load->view('agents/index', $data, true);
                break;
        }

        if ($this->router->fetch_method() == 'main' && !$this->input->is_ajax_request()) {
            $this->load->view('agents/main', $data);
        }

    }

    public function dashboard()
    {
        $this->load->view('agents/dashboard');
    }


    public function create()
    {
        $this->load->view('agents/create');
    }

    private function set_validation_rules()
    {
        if ($this->input->post('type') === 'individual') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|min_length[2]|max_length[100]');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|min_length[2]|max_length[100]');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('phone_number', 'Phone Number', 'required|numeric|min_length[10]|max_length[15]');
            $this->form_validation->set_rules('address', 'Address', 'required|trim');
            $this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('country_id', 'Country', 'required');
            $this->form_validation->set_rules('state_id', 'State', 'required');
            $this->form_validation->set_rules('unique_number', 'Agent Number', 'required');
        }

        if ($this->input->post('type') === 'company') {
            $this->form_validation->set_rules('company_name', 'Company Name', 'required');
            $this->form_validation->set_rules('contact_name', 'Name', 'required');
            $this->form_validation->set_rules('contact_email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('company_password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('contact_phone_number', 'Phone Number', 'required|numeric|min_length[10]|max_length[15]');
            $this->form_validation->set_rules('company_address', 'Address', 'required|trim');
            $this->form_validation->set_rules('company_username', 'Username', 'required|alpha_numeric|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('company_country_id', 'Country', 'required');
            $this->form_validation->set_rules('company_state_id', 'State', 'required');
            $this->form_validation->set_rules('company_unique_number', 'Agent Number', 'required');
        }

    }


    public function upload_file($folder, $name)
    {
        $upload_path = FCPATH . 'modules/courier_logistic/assets/' . $folder . '/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file_name = time() . '_' . $_FILES[$name]['name'];
        $file_path = $upload_path . $file_name;

        if (move_uploaded_file($_FILES[$name]['tmp_name'], $file_path)) {
            return 'modules/courier_logistic/assets/' . $folder . '/' . $file_name;
        } else {
            set_alert('danger', 'File upload failed.');
        }

    }


    public function store()
    {
        $this->set_validation_rules();

        $file_error = false;

        if ($this->input->post('type') === 'individual') {
            if (empty($_FILES['id_file']['name'])) {
                $this->session->set_flashdata('id_file_error', 'The Attachment is required.');
                $file_error = true;
            }
            if (empty($_FILES['kra_file']['name'])) {
                $this->session->set_flashdata('kra_file_error', 'The Attachment is required.');
                $file_error = true;
            }
        }

        if ($this->input->post('type') === 'company') {
            if (empty($_FILES['company_id_file']['name'])) {
                $this->session->set_flashdata('company_id_file_error', 'The Attachment is required.');
                $file_error = true;
            }
            if (empty($_FILES['company_kra_file']['name'])) {
                $this->session->set_flashdata('company_kra_file_error', 'The Attachment is required.');
                $file_error = true;
            }
            if (empty($_FILES['corporation_certificate_file']['name'])) {
                $this->session->set_flashdata('corporation_certificate_file_error', 'The Attachment is required.');
                $file_error = true;
            }
        }

        if ($this->form_validation->run() == FALSE || $file_error) {

            $show_company_section = $this->input->post('type') === 'company';

            $this->session->set_userdata('show_company_section', $show_company_section);

            // Preserve form input values
            foreach ($this->input->post() as $key => $value) {
                $this->session->set_flashdata($key . '_error', form_error($key));
                $this->session->set_flashdata($key, $value);
            }

            redirect('admin/courier/agents/main?group=create_agent');
        } else {

            $data = [];

            // Insert into staff table
            if ($this->input->post('type') === 'individual') {
                $data = [
                    'firstname' => $this->input->post('first_name'),
                    'lastname' => $this->input->post('last_name'),
                    'email' => $this->input->post('email'),
                    'password' => app_hash_password($this->input->post('password')),
                ];
            }

            if ($this->input->post('type') === 'company') {
                $data = [
                    'firstname' => $this->input->post('contact_name'),
                    'lastname' => $this->input->post('contact_name'),
                    'email' => $this->input->post('contact_email'),
                    'password' => app_hash_password($this->input->post('company_password')),
                ];
            }


            $this->db->insert(db_prefix() . 'staff', $data);
            $staff_id = $this->db->insert_id();

            // Use the role selected in the form (defaults to "Courier: Agent")
            $role_id = (int)$this->input->post('role_id');
            if (!$role_id) {
                $this->db->where('name', 'Courier: Agent');
                $default_role = $this->db->get(db_prefix() . 'roles')->row();
                $role_id = $default_role ? (int)$default_role->roleid : 0;
            }

            if ($role_id) {
                $this->db->where('staffid', $staff_id);
                $this->db->update(db_prefix() . 'staff', ['role' => $role_id]);

                $this->db->where('roleid', $role_id);
                $role = $this->db->get(db_prefix() . 'roles')->row();

                $permissions = ($role && $role->permissions) ? unserialize($role->permissions) : [];
                foreach ($permissions as $feature => $capabilities) {
                    foreach ($capabilities as $capability) {
                        $this->db->insert(db_prefix() . 'staff_permissions', [
                            'staff_id'   => $staff_id,
                            'feature'    => $feature,
                            'capability' => $capability,
                        ]);
                    }
                }
            }

            $unique_number = '';

            if ($this->input->post('type') === 'individual') {
                $unique_number = $this->input->post('unique_number');
            } else {
                $unique_number = $this->input->post('company_unique_number');
            }

            $parts = explode('/', $unique_number);
            $agent_number = $parts[2];
            $agent_data = [];

            if ($this->input->post('type') === 'individual') {

                $id_file_url  = $this->upload_file('agent_ids', 'id_file');
                $kra_file_url = $this->upload_file('agent_kras', 'kra_file');

                $agent_data = [
                    'staff_id'    => $staff_id,
                    'phone_number'=> $this->input->post('phone_number'),
                    'address'     => $this->input->post('address'),
                    'unique_number'=> $unique_number,
                    'agent_number' => $agent_number,
                    'id_file_url'  => $id_file_url,
                    'kra_file_url' => $kra_file_url,
                    'kra_pin'     => $this->input->post('kra_pin') ?: NULL,
                    'country_id'  => $this->input->post('country_id'),
                    'state_id'    => $this->input->post('state_id') ?: NULL,
                    'agent_type'  => $this->input->post('type'),
                    'station'     => $this->input->post('station') ?: NULL,
                    'branch_id'   => courier_get_session_branch_id(),
                ];
            }


            if ($this->input->post('type') === 'company') {

                $id_file_url = $this->upload_file('agent_ids', 'company_id_file');
                $kra_file_url = $this->upload_file('agent_kras', 'company_kra_file');
                $cert_of_corp_file = $this->upload_file('agent_corporation_certificates', 'corporation_certificate_file');

                $agent_data = [
                    'staff_id'         => $staff_id,
                    'phone_number'     => $this->input->post('contact_phone_number'),
                    'address'          => $this->input->post('company_address'),
                    'company_name'     => $this->input->post('company_name'),
                    'unique_number'    => $unique_number,
                    'agent_number'     => $agent_number,
                    'id_file_url'      => $id_file_url,
                    'kra_file_url'     => $kra_file_url,
                    'cert_of_corp_url' => $cert_of_corp_file,
                    'kra_pin'          => $this->input->post('company_kra_pin') ?: NULL,
                    'country_id'       => $this->input->post('company_country_id'),
                    'state_id'         => $this->input->post('company_state_id') ?: NULL,
                    'agent_type'       => $this->input->post('type'),
                    'station'          => $this->input->post('company_station') ?: NULL,
                    'branch_id'        => courier_get_session_branch_id(),
                ];
            }

            $this->db->insert(db_prefix() . '_agents', $agent_data);

            // Assign the new agent's staff account to the creating staff's active
            // branch, so their own shipments/pickups/etc. are scoped from day one.
            $creator_branch_id = courier_get_session_branch_id();
            if ($creator_branch_id) {
                $this->db->insert(db_prefix() . '_courier_staff_branches', [
                    'staff_id'   => $staff_id,
                    'branch_id'  => $creator_branch_id,
                    'is_default' => 1,
                ]);
            }

            set_alert('success', 'Agent added successfully.');
            redirect('admin/courier/agents/main?group=list_agents');
        }
    }


    public function sync_role_permissions()
    {
        // Syncing logic
        $this->db->where('name', 'Courier: Agent');
        $courier_agent_role = $this->db->get(db_prefix() . 'roles')->row();

        if ($courier_agent_role) {
            $role_id = $courier_agent_role->roleid;

            $this->db->where('roleid', $role_id);
            $role = $this->db->get(db_prefix() . 'roles')->row();

            if ($role) {
                $permissions = unserialize($role->permissions);

                $this->db->where('role', $role_id);
                $staff_members = $this->db->get(db_prefix() . 'staff')->result();

                foreach ($staff_members as $staff) {
                    // Clear old permissions
                    $this->db->where('staff_id', $staff->staffid);
                    $this->db->delete(db_prefix() . 'staff_permissions');

                    // Insert new permissions
                    if ($permissions) {
                        foreach ($permissions as $feature => $capabilities) {
                            foreach ($capabilities as $capability) {
                                $this->db->insert(db_prefix() . 'staff_permissions', [
                                    'staff_id' => $staff->staffid,
                                    'feature' => $feature,
                                    'capability' => $capability
                                ]);
                            }
                        }
                    }
                }

                // Return success response
                echo json_encode(['message' => 'Permissions synced successfully.']);
            } else {
                // Role not found, return error
                http_response_code(404);
                echo json_encode(['message' => 'Role not found.']);
            }
        } else {
            // Return error if the role is not found
            http_response_code(404);
            echo json_encode(['message' => 'Courier: Agent role not found.']);
        }
    }


public function agent_number()
    {
        if ($this->input->is_ajax_request()) {

            $country_id = $this->input->get('country_id') ?: $this->input->post('country_id');
            $state_id   = $this->input->get('state_id')   ?: $this->input->post('state_id');

            $countries    = $this->Shipment_model->get_countries($country_id);
            $country_code = strtoupper($countries[0]->iso2);

            // state_id is now the service point index (1-73) — use directly as middle segment
            $middle_segment = str_pad($state_id, 3, '0', STR_PAD_LEFT);

            // Sequential number scoped per country + state
            $this->db->select_max('agent_number');
            $this->db->where('country_id', $country_id);
            $this->db->where('state_id', $state_id);
            $result       = $this->db->get(db_prefix() . '_agents')->row();
            $agent_number = ($result && $result->agent_number) ? $result->agent_number + 1 : 1;

            $new_unique_number = $country_code
                . '/' . $middle_segment
                . '/' . str_pad($agent_number, 3, '0', STR_PAD_LEFT);

            echo json_encode(['success' => true, 'new_agent_number' => $new_unique_number]);

        } else {
            show_404();
        }
    }


    public function delete($id)
    {
        $this->db->where('id', $id);
        $agent = $this->db->get(db_prefix() . '_agents')->row();

        if ($agent) {
            $staff_id = $agent->staff_id;

            // Begin a transaction to ensure atomic operations
            $this->db->trans_start();

            // Delete the staff record
            $this->db->where('staffid', $staff_id);
            $this->db->delete(db_prefix() . 'staff');

            // Delete all permissions for the staff
            $this->db->where('staff_id', $staff_id);
            $this->db->delete(db_prefix() . 'staff_permissions');

            // Delete the agent from the agents table
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . '_agents');

            // Complete the transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                set_alert('danger', 'Failed to delete agent and related records.');
                redirect('admin/courier/agents/main?group=list_agents');

            } else {
                set_alert('success', 'Agent and related records deleted successfully.');
                redirect('admin/courier/agents/main?group=list_agents');
            }
        } else {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main?group=list_agents');
        }
    }

    public function update_status()
    {
        $agent_id = $this->input->post('agent_id');
        $status = $this->input->post('status');

        $this->db->where('id', $agent_id);
        $success = $this->db->update(db_prefix().'_agents', ['status' => $status]);

        echo json_encode(['success' => $success]);
    }

    public function view($id)
    {
        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $data['agent']       = $agent;
        $data['stats']       = $this->Agent_model->get_stats($agent->staff_id);
        $data['shipments']   = $this->Agent_model->get_shipments($agent->staff_id, 20);
        $data['invoices']    = $this->Agent_model->get_invoices($agent->staff_id, 20);
        $data['title']       = 'Agent: ' . $agent->firstname . ' ' . $agent->lastname;

        // Load current permissions for this staff member
        $this->db->where('staff_id', $agent->staff_id);
        $perms_rows = $this->db->get(db_prefix() . 'staff_permissions')->result();
        $current_perms = [];
        foreach ($perms_rows as $p) {
            $current_perms[$p->feature][] = $p->capability;
        }
        $data['current_perms'] = $current_perms;

        $this->load->view('agents/view', $data);
    }

    public function edit($id)
    {
        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $data['agent']     = $agent;
        $data['countries'] = $this->Shipment_model->get_countries();
        $data['title']     = 'Edit Agent: ' . $agent->firstname . ' ' . $agent->lastname;
        $this->load->view('agents/edit', $data);
    }

    public function update($id)
    {
        if (!$this->input->post()) {
            redirect('admin/courier/agents/edit/' . $id);
        }

        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $data = [
            'firstname'       => $this->input->post('firstname'),
            'lastname'        => $this->input->post('lastname'),
            'email'           => $this->input->post('email'),
            'phonenumber'     => $this->input->post('phonenumber'),
            'station'         => $this->input->post('station'),
            'country_id'      => $this->input->post('country_id'),
            'commission_rate' => $this->input->post('commission_rate') !== '' ? $this->input->post('commission_rate') : null,
            'admin_notes'     => $this->input->post('admin_notes'),
        ];

        if ($this->Agent_model->update_agent($id, $data)) {
            set_alert('success', 'Agent updated successfully.');
        } else {
            set_alert('danger', 'Failed to update agent.');
        }

        redirect('admin/courier/agents/view/' . $id);
    }

    public function reset_password($id)
    {
        if (!$this->input->post()) {
            redirect('admin/courier/agents/view/' . $id);
        }

        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $new_password = $this->input->post('new_password');
        if (strlen($new_password) < 6) {
            set_alert('danger', 'Password must be at least 6 characters.');
            redirect('admin/courier/agents/view/' . $id);
        }

        if ($this->Agent_model->reset_password($agent->staff_id, app_hash_password($new_password))) {
            set_alert('success', 'Password reset successfully.');
        } else {
            set_alert('danger', 'Failed to reset password.');
        }

        redirect('admin/courier/agents/view/' . $id);
    }

    public function suspend($id)
    {
        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $reason = $this->input->post('reason') ?: null;
        $this->Agent_model->update_agent($id, [
            'status'           => '0',
            'suspended_reason' => $reason,
            'suspended_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->db->where('staffid', $agent->staff_id);
        $this->db->update(db_prefix() . 'staff', ['active' => 0]);

        set_alert('success', 'Agent suspended.');
        redirect('admin/courier/agents/view/' . $id);
    }

    public function activate($id)
    {
        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            set_alert('danger', 'Agent not found.');
            redirect('admin/courier/agents/main');
        }

        $this->Agent_model->update_agent($id, [
            'status'           => '1',
            'suspended_reason' => null,
            'suspended_at'     => null,
        ]);

        $this->db->where('staffid', $agent->staff_id);
        $this->db->update(db_prefix() . 'staff', ['active' => 1]);

        set_alert('success', 'Agent activated.');
        redirect('admin/courier/agents/view/' . $id);
    }

    public function save_permissions($id)
    {
        $this->output->set_content_type('application/json');

        $agent = $this->Agent_model->get_single($id);
        if (!$agent) {
            echo json_encode(['success' => false, 'message' => 'Agent not found.']);
            return;
        }

        $staff_id    = $agent->staff_id;
        // Use raw $_POST to avoid CI3 XSS sanitizer mangling hyphenated array keys
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];

        // Delete all existing courier permissions for this staff member
        $courier_features = [
            'courier-shipments', 'courier-pickups', 'courier-waybills',
            'courier-manifests', 'courier-invoices', 'courier-agents',
            'courier-companies', 'courier-settings',
        ];
        $this->db->where('staff_id', $staff_id);
        $this->db->where_in('feature', $courier_features);
        $this->db->delete(db_prefix() . 'staff_permissions');

        // Whitelist: only allow known courier features and alphanumeric/underscore capabilities
        $allowed_features = [
            'courier-shipments', 'courier-pickups', 'courier-waybills',
            'courier-manifests', 'courier-invoices', 'courier-agents',
            'courier-companies', 'courier-settings',
        ];

        foreach ($permissions as $feature => $capabilities) {
            if (!in_array($feature, $allowed_features, true)) continue;
            if (!is_array($capabilities)) continue;
            foreach ($capabilities as $capability) {
                $capability = preg_replace('/[^a-z0-9_]/', '', (string)$capability);
                if (!$capability) continue;
                $this->db->insert(db_prefix() . 'staff_permissions', [
                    'staff_id'   => $staff_id,
                    'feature'    => $feature,
                    'capability' => $capability,
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Permissions saved successfully.']);
    }

    public function reset_permissions()
    {
        $default_permissions = [
            'courier-shipments' => ['view_own_shipments', 'create_shipments'],
            'courier-waybills'  => ['view_own_waybills'],
            'courier-invoices'  => ['view_own_invoices', 'generate_payment', 'view_receipts'],
        ];

        $this->db->where('name', 'Courier: Agent');
        $role = $this->db->get(db_prefix() . 'roles')->row();

        if (!$role) {
            echo json_encode(['success' => false, 'message' => 'Courier: Agent role not found.']);
            return;
        }

        $serialized = serialize($default_permissions);
        $this->db->where('roleid', $role->roleid);
        $this->db->update(db_prefix() . 'roles', ['permissions' => $serialized]);

        $this->db->where('role', $role->roleid);
        $agents = $this->db->get(db_prefix() . 'staff')->result();

        foreach ($agents as $staff) {
            $this->db->where('staff_id', $staff->staffid);
            $this->db->delete(db_prefix() . 'staff_permissions');

            foreach ($default_permissions as $feature => $capabilities) {
                foreach ($capabilities as $capability) {
                    $this->db->insert(db_prefix() . 'staff_permissions', [
                        'staff_id'   => $staff->staffid,
                        'feature'    => $feature,
                        'capability' => $capability,
                    ]);
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Agent role permissions reset and synced to ' . count($agents) . ' agent(s).']);
    }

}

