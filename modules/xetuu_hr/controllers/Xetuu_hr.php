<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xetuu_hr extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('xetuu_hr/Xetuu_hr_model', 'hr');
        $this->load->model('xetuu_hr/Xr_payroll_model', 'payroll');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        $data = [
            'title'  => _l('xetuu_hr'),
            'stats'  => $this->hr->get_dashboard_stats(),
        ];
        $this->load->view('xetuu_hr/admin/dashboard/index', $data);
    }

    // ── Employees ─────────────────────────────────────────────────────────────

    public function employees($id = null)
    {
        $p = $this->hr->p();
        if (!$this->db->field_exists('biography', $p . 'hr_employees')) {
            $this->db->query("ALTER TABLE `{$p}hr_employees` ADD COLUMN `biography` TEXT DEFAULT NULL");
        }

        // Route: /xetuu_hr/employees/add → new employee form
        if ($id === 'add') {
            $this->_employee_form();
            return;
        }

        // Route: /xetuu_hr/employees/edit/N → edit form
        if ($id === 'edit') {
            $emp_id = (int) $this->uri->segment(5);
            $this->_employee_form($emp_id);
            return;
        }

        // Route: /xetuu_hr/employees/save → POST handler
        if ($id === 'save') {
            $this->_save_employee();
            return;
        }

        // Route: /xetuu_hr/employees/N → profile view
        if ($id !== null && is_numeric($id)) {
            $employee = $this->hr->get_employee((int) $id);
            if (!$employee) { show_404(); }
            $emp_id = (int) $id;
            $data = [
                'title'             => $employee->first_name . ' ' . $employee->last_name,
                'employee'          => $employee,
                'emp_payslips'      => $this->payroll->get_payslips(['employee_id' => $emp_id], 20),
                'active_contract'   => $this->payroll->get_active_payroll_contract($emp_id),
                'contract_lines'    => [],
                'attachments'       => $this->db->where('rel_id', $emp_id)->where('rel_type', 'hr_employee')->get(db_prefix() . 'files')->result(),
            ];
            $active = $data['active_contract'];
            if ($active) {
                $data['contract_lines'] = $this->payroll->get_contract_lines($active->id);
            }
            $this->load->view('xetuu_hr/admin/employees/profile', $data);
            return;
        }

        // Route: /xetuu_hr/employees → list
        $data = [
            'title'     => _l('xetuu_hr_employees'),
            'employees' => $this->hr->get_employees(),
        ];
        $this->load->view('xetuu_hr/admin/employees/index', $data);
    }

    private function _employee_form($id = null)
    {
        $employee = null;
        $is_prefill = false;
        if ($id) {
            $employee = $this->hr->get_employee($id);
            if (!$employee) { show_404(); }
        } else {
            $applicant_id = $this->input->get('applicant_id');
            if ($applicant_id) {
                $p = $this->hr->p();
                $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
                if ($applicant) {
                    $is_prefill = true;
                    $offer   = $this->db->where('applicant_id', $applicant->id)->order_by('id','DESC')->get($p . 'hr_job_offers')->row();
                    $opening = ($offer && $offer->job_opening_id)
                        ? $this->db->where('id', $offer->job_opening_id)->get($p . 'hr_job_openings')->row()
                        : ($applicant->job_opening_id ? $this->db->where('id', $applicant->job_opening_id)->get($p . 'hr_job_openings')->row() : null);

                    $employee = (object)[
                        'first_name'     => $applicant->first_name,
                        'last_name'      => $applicant->last_name,
                        'personal_email' => $applicant->email,
                        'email'          => $applicant->email,
                        'mobile'         => $applicant->phone,
                        'phone'          => $applicant->phone,
                        'biography'      => $applicant->cover_letter,
                        'applicant_id'   => $applicant->id,
                        'status'         => 'Active',
                    ];
                    if ($offer) {
                        $employee->designation_id = $offer->designation_id;
                        $employee->date_of_joining = $offer->joining_date;
                    }
                    if ($opening) {
                        $employee->company_id = $opening->company_id;
                        $employee->department_id = $opening->department_id;
                        if (empty($employee->designation_id)) {
                            $employee->designation_id = $opening->designation_id;
                        }
                    }
                }
            }
        }

        $active_contract = $id ? $this->payroll->get_active_payroll_contract($id) : null;
        $contract_lines  = $active_contract ? $this->payroll->get_contract_lines($active_contract->id) : [];

        // Pre-fill salary from job offer for new manual employee additions
        if (!$active_contract && $is_prefill && !empty($offer) && !empty($offer->salary_offered)) {
            $active_contract = (object)[
                'monthly_salary' => $offer->salary_offered,
                'basic_salary'   => $offer->salary_offered,
                'salary_structure_id' => null,
            ];
            $default_struct = $this->db->order_by('id', 'ASC')->limit(1)->get($p . 'hr_payroll_salary_structures')->row();
            if ($default_struct) {
                $active_contract->salary_structure_id = $default_struct->id;
            }
        }

        $this->load->model('xetuu_hr/Xr_leave_model', 'leave_mdl');
        // Current leave policy on this employee (if any)
        $current_leave_policy_id = null;
        if ($id) {
            $alloc_row = $this->db->where('employee_id', (int)$id)->where('leave_year', date('Y'))->where('policy_id IS NOT NULL', null, false)->order_by('id','DESC')->limit(1)->get(db_prefix().'hr_leave_allocations')->row();
            $current_leave_policy_id = $alloc_row ? ($alloc_row->policy_id ?? null) : null;
        }

        $data = [
            'title'                   => $id ? ($employee->first_name . ' ' . $employee->last_name) : _l('xetuu_hr_add_employee'),
            'employee'                => $employee,
            'is_prefill'              => $is_prefill,
            'next_number'             => $this->hr->preview_next_employee_number(),
            'departments'             => $this->hr->get_departments(),
            'designations'            => $this->hr->get_designations(),
            'companies'               => $this->hr->get_companies(),
            'clients'                 => $this->hr->get_clients(),
            'grades'                  => $this->hr->get_grades(),
            'groups'                  => $this->hr->get_employee_groups(),
            'branches'                => $this->hr->get_branches(),
            'staff_list'              => $this->hr->get_all_employees_simple(),
            'payroll_companies'       => $this->payroll->get_payroll_companies(),
            'salary_structures'       => $this->payroll->get_salary_structures(),
            'active_contract'         => $active_contract,
            'contract_lines'          => $contract_lines,
            'analytic_accounts'       => $this->hr->get_analytic_accounts(),
            'advance_accounts'        => $this->hr->get_advance_accounts(),
            'shifts'                  => $this->hr->get_shifts(),
            'leave_policies'          => $this->leave_mdl->get_leave_policies(),
            'current_leave_policy_id' => $current_leave_policy_id,
        ];
        $this->load->view('xetuu_hr/admin/employees/add', $data);
    }

    private function _save_employee()
    {
        $id   = $this->input->post('employee_id');
        $post = $this->input->post(null, true);
        $applicant_id = $this->input->post('applicant_id');

        // Handling photo upload
        $upload_path = FCPATH . 'uploads/hr_employees/';
        if (!is_dir($upload_path)) {
            @mkdir($upload_path, 0755, true);
        }
        $this->load->library('upload');
        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'gif|jpg|jpeg|png',
            'max_size'      => 2048,
            'encrypt_name'  => true,
        ];
        $this->upload->initialize($config);
        if ($this->upload->do_upload('profile_photo')) {
            $post['photo'] = 'uploads/hr_employees/' . $this->upload->data('file_name');
        } else if ($this->upload->do_upload('photo')) {
            $post['photo'] = 'uploads/hr_employees/' . $this->upload->data('file_name');
        }

        if ($id) {
            $ok = $this->hr->update_employee((int) $id, $post);
            if ($ok) { set_alert('success', _l('xetuu_hr_employee_updated')); }
            else      { set_alert('danger',  _l('xetuu_hr_employee_update_failed')); }
            if (!empty($post['monthly_salary']) || !empty($post['contract_start'])) {
                $this->hr->save_hr_contract((int) $id, $post);
            }
            $this->_maybe_save_payroll_contract((int) $id, $post);
            if (!empty($post['leave_policy_id'])) {
                $this->_apply_leave_policy_to_employee((int) $id, (int) $post['leave_policy_id'], (int) ($post['leave_policy_year'] ?? date('Y')));
            }
            redirect(admin_url('xetuu_hr/employees/' . (int) $id));
        } else {
            $new_id = $this->hr->add_employee($post);
            if ($new_id) {
                set_alert('success', _l('xetuu_hr_employee_added'));
                if (!empty($post['monthly_salary']) || !empty($post['contract_start'])) {
                    $this->hr->save_hr_contract((int) $new_id, $post);
                }
                $this->_maybe_save_payroll_contract((int) $new_id, $post);
                if (!empty($post['leave_policy_id'])) {
                    $this->_apply_leave_policy_to_employee((int) $new_id, (int) $post['leave_policy_id'], (int) ($post['leave_policy_year'] ?? date('Y')));
                }

                if (!empty($applicant_id)) {
                    $p = $this->hr->p();
                    $this->db->where('id', (int)$applicant_id)->update($p . 'hr_applicants', ['stage' => 'Hired']);
                    $this->_send_applicant_stage_email($applicant_id, 'Hired');

                    $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
                    if ($applicant && !empty($applicant->resume)) {
                        $src = FCPATH . 'uploads/xetuu_hr/resumes/' . $applicant->resume;
                        if (file_exists($src)) {
                            $dest_filename = uniqid('resume_', true) . '_' . $applicant->resume;
                            $dst = $upload_path . $dest_filename;
                            if (copy($src, $dst)) {
                                $this->db->insert(db_prefix() . 'files', [
                                    'rel_id'    => $new_id,
                                    'rel_type'  => 'hr_employee',
                                    'file_name' => $dest_filename,
                                    'filetype'  => mime_content_type($dst),
                                    'dateadded' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    }
                }
            } else {
                set_alert('danger', _l('xetuu_hr_employee_add_failed'));
            }
            redirect($new_id ? admin_url('xetuu_hr/employees/' . $new_id) : admin_url('xetuu_hr/employees/add'));
        }
    }

    private function _apply_leave_policy_to_employee($employee_id, $policy_id, $leave_year)
    {
        $this->load->model('xetuu_hr/Xr_leave_model', 'leave_mdl');
        $policies = $this->leave_mdl->get_leave_policies();
        $policy   = null;
        foreach ($policies as $p2) {
            if ((int) $p2->id === $policy_id) { $policy = $p2; break; }
        }
        if (!$policy || empty($policy->lines)) { return; }
        foreach ($policy->lines as $line) {
            $exists = $this->db
                ->where('employee_id', $employee_id)
                ->where('leave_type_id', (int) $line->leave_type_id)
                ->where('leave_year', $leave_year)
                ->count_all_results(db_prefix() . 'hr_leave_allocations');
            if ($exists) { continue; }
            $this->leave_mdl->save_allocation([
                'employee_id'          => $employee_id,
                'leave_type_id'        => (int) $line->leave_type_id,
                'policy_id'            => $policy_id,
                'leave_year'           => $leave_year,
                'total_days'           => (float) ($line->annual_days ?? 0),
                'carried_forward_days' => 0,
                'status'               => 'active',
                'notes'                => 'Auto-applied on onboarding: ' . $policy->name,
                'created_by'           => get_staff_user_id(),
            ]);
        }
    }

    private function _maybe_save_payroll_contract($employee_id, $post)
    {
        // Auto-detect payroll company from the employee's HR company if not explicitly posted
        $payroll_company_id = !empty($post['payroll_company_id']) ? (int)$post['payroll_company_id'] : 0;
        if (!$payroll_company_id && !empty($post['company_id'])) {
            $p = $this->hr->p();
            $hrc = $this->db->where('id', (int)$post['company_id'])->get($p . 'hr_companies')->row();
            if ($hrc && !empty($hrc->payroll_company_id)) {
                $payroll_company_id = (int)$hrc->payroll_company_id;
            }
        }

        if (!$payroll_company_id || empty($post['payroll_salary_structure_id'])) {
            return;
        }

        // Use monthly_salary as the wage if payroll_basic_salary not explicitly provided
        $wage = !empty($post['payroll_basic_salary'])
            ? (float)$post['payroll_basic_salary']
            : (float)($post['monthly_salary'] ?? 0);

        $contract_data = [
            'employee_id'    => $employee_id,
            'company_id'     => $payroll_company_id,
            'structure_id'   => (int) $post['payroll_salary_structure_id'],
            'wage'           => $wage,
            'payment_method' => $post['payroll_payment_method'] ?? 'bank',
            'bank_account'   => $post['payroll_bank_account'] ?? '',
            'tax_id'         => $post['payroll_tax_id'] ?? '',
            'working_days'   => (int) ($post['payroll_working_days'] ?? 5),
            'date_start'     => $post['contract_start'] ?? date('Y-m-01'),
            'date_end'       => $post['contract_end'] ?: null,
            'status'         => 'active',
        ];
        $existing = $this->payroll->get_active_payroll_contract($employee_id);
        $contract_id = $this->payroll->save_payroll_contract($contract_data, $existing ? $existing->id : null);

        $benefits   = [];
        $deductions = [];
        if (!empty($post['payroll_benefit_name'])) {
            foreach ($post['payroll_benefit_name'] as $i => $name) {
                if (!$name) continue;
                $benefits[] = [
                    'name'   => $name,
                    'code'   => strtoupper($post['payroll_benefit_code'][$i] ?? ''),
                    'amount' => (float) ($post['payroll_benefit_amount'][$i] ?? 0),
                    'statutory_ref' => null,
                ];
            }
        }
        if (!empty($post['payroll_deduction_name'])) {
            foreach ($post['payroll_deduction_name'] as $i => $name) {
                if (!$name) continue;
                $deductions[] = [
                    'name'   => $name,
                    'code'   => strtoupper($post['payroll_deduction_code'][$i] ?? ''),
                    'amount' => (float) ($post['payroll_deduction_amount'][$i] ?? 0),
                    'statutory_ref' => null,
                ];
            }
        }
        if ($contract_id) {
            $this->payroll->sync_contract_lines($contract_id, $benefits, $deductions);
        }
    }

    // ── Setup ─────────────────────────────────────────────────────────────────

    public function setup($sub = 'company', $action = null, $id = null)
    {
        $valid_subs = [
            'company', 'branch', 'department', 'designation', 'employee_group', 'employee_grade', 'settings',
            'staffing_plan', 'job_requisition', 'interview_type', 'interview_round', 'appointment_letter_template', 'recruitment_settings'
        ];
        if (!in_array($sub, $valid_subs)) { show_404(); }

        // ── Department has its own full-page form ─────────────────────────────
        if ($sub === 'department') {
            if ($this->input->post()) {
                $this->_save_department();
                return;
            }
            if ($action === 'delete' && $id) {
                $this->hr->setup_delete('department', (int)$id);
                set_alert('success', _l('deleted_successfully'));
                redirect(admin_url('xetuu_hr/setup/department'));
                return;
            }
            if ($action === 'add' || $action === 'edit') {
                $dept = ($action === 'edit' && $id) ? $this->hr->get_department((int)$id) : null;
                $data = [
                    'title'        => $dept ? $dept->name : 'New Department',
                    'dept'         => $dept,
                    'companies'    => $this->hr->get_companies(),
                    'departments'  => $this->hr->setup_departments_list(),
                    'staff_list'   => $this->hr->get_all_staff(),
                    'approvers'    => $dept ? $this->hr->get_dept_approvers($dept->id) : [],
                    'cost_centers' => $this->hr->get_analytic_accounts(),
                ];
                $this->load->view('xetuu_hr/admin/setup/department_form', $data);
                return;
            }
            // List view
            $data = [
                'title'       => 'Departments',
                'sub'         => 'department',
                'departments' => $this->hr->setup_departments_list(),
                'companies'   => $this->hr->get_companies(),
                'emp_counts'  => $this->hr->get_dept_employee_counts(),
            ];
            $this->load->view('xetuu_hr/admin/setup/department_list', $data);
            return;
        }

        // ── Duplicate appointment letter template ─────────────────────────────
        if ($sub === 'appointment_letter_template' && $action === 'duplicate' && $id) {
            $p    = $this->hr->p();
            $orig = $this->db->where('id', (int)$id)->get($p . 'hr_appointment_letter_templates')->row();
            if ($orig) {
                $row = (array)$orig;
                unset($row['id']);
                $row['name']         = 'Copy of ' . $orig->name;
                $row['date_created'] = date('Y-m-d H:i:s');
                $this->db->insert($p . 'hr_appointment_letter_templates', $row);
                $new_id = $this->db->insert_id();
                if ($this->db->table_exists($p . 'hr_appointment_letter_template_terms')) {
                    $terms = $this->db->where('template_id', (int)$id)->order_by('sort_order')->get($p . 'hr_appointment_letter_template_terms')->result();
                    foreach ($terms as $t) {
                        $this->db->insert($p . 'hr_appointment_letter_template_terms', [
                            'template_id' => $new_id,
                            'title'       => $t->title,
                            'description' => $t->description,
                            'sort_order'  => $t->sort_order,
                        ]);
                    }
                }
                set_alert('success', 'Template duplicated as "Copy of ' . $orig->name . '". Edit it to customise for your role.');
            }
            redirect(admin_url('xetuu_hr/setup/appointment_letter_template'));
            return;
        }

        // ── Generic setup save & delete ───────────────────────────────────────
        if ($this->input->post() && !in_array($sub, ['recruitment_settings', 'staffing_plan', 'job_requisition', 'interview_round'])) {
            $this->_save_setup($sub);
            return;
        }
        if ($action === 'delete' && $id && !in_array($sub, ['staffing_plan', 'job_requisition'])) {
            $this->_delete_setup($sub, (int)$id);
            return;
        }

        // ── Data preparations for specific setup screens ───────────────────────
        $p = $this->hr->p();
        $data = [
            'title'     => _l('xetuu_hr_' . $sub),
            'sub'       => $sub,
            'companies' => in_array($sub, ['branch', 'staffing_plan', 'job_requisition']) ? $this->hr->get_companies() : [],
        ];

        if ($sub === 'staffing_plan') {
            if ($this->input->post()) {
                $this->_save_staffing_plan();
                return;
            }
            if ($action === 'delete' && $id) {
                // Delete child details first
                $this->db->where('staffing_plan_id', (int)$id)->delete($p . 'hr_staffing_plan_details');
                // Delete parent staffing plan
                $this->db->where('id', (int)$id)->delete($p . 'hr_staffing_plans');
                set_alert('success', 'Staffing Plan deleted successfully.');
                redirect(admin_url('xetuu_hr/setup/staffing_plan'));
                return;
            }
            if ($action === 'add' || $action === 'edit') {
                $plan = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_staffing_plans')->row() : null;
                $details = [];
                if ($plan) {
                    $details = $this->db->where('staffing_plan_id', $plan->id)->get($p . 'hr_staffing_plan_details')->result();
                }
                $data = [
                    'title'        => $plan ? 'Edit Staffing Plan: ' . $plan->name : 'New Staffing Plan',
                    'plan'         => $plan,
                    'details'      => $details,
                    'companies'    => $this->hr->get_companies(),
                    'departments'  => $this->hr->get_departments(),
                    'designations' => $this->hr->get_designations(),
                    'sub'          => 'staffing_plan',
                ];
                $this->load->view('xetuu_hr/admin/setup/staffing_plan_form', $data);
                return;
            }
            // List View
            $this->db->select('s.*, c.name AS company_name, d.name AS department_name');
            $this->db->from($p . 'hr_staffing_plans s');
            $this->db->join($p . 'hr_companies c', 's.company_id = c.id', 'left');
            $this->db->join($p . 'hr_departments d', 's.department_id = d.id', 'left');
            $this->db->order_by('s.id', 'DESC');
            $data['rows'] = $this->db->get()->result();

            $data['sub'] = 'staffing_plan';
            $this->load->view('xetuu_hr/admin/setup/staffing_plan_list', $data);
            return;
        }

        if ($sub === 'job_requisition') {
            // Run column migration inline for existing installs
            $_jreq_cols = [
                'requisition_number' => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `requisition_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
                'company_id'         => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `company_id` INT(11) DEFAULT NULL AFTER `requisition_number`",
                'job_description'    => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `job_description` LONGTEXT DEFAULT NULL AFTER `expected_salary`",
                'posting_date'       => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `posting_date` DATE DEFAULT NULL AFTER `status`",
                'expected_by_date'   => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `expected_by_date` DATE DEFAULT NULL AFTER `posting_date`",
            ];
            foreach ($_jreq_cols as $_col => $_sql) {
                if (!$this->db->field_exists($_col, $p . 'hr_job_requisitions')) {
                    $this->db->query($_sql);
                }
            }

            if ($this->input->post()) {
                $this->_save_job_requisition_form();
                return;
            }

            if ($action === 'delete' && $id) {
                $this->db->where('id', (int)$id)->delete($p . 'hr_job_requisitions');
                set_alert('success', 'Job Requisition deleted.');
                redirect(admin_url('xetuu_hr/setup/job_requisition'));
                return;
            }

            if ($action === 'add' || $action === 'edit') {
                $req = ($action === 'edit' && $id)
                    ? $this->db->where('id', (int)$id)->get($p . 'hr_job_requisitions')->row()
                    : null;
                $form_data = [
                    'title'          => $req ? 'Edit Job Requisition: ' . $req->requisition_number : 'New Job Requisition',
                    'req'            => $req,
                    'companies'      => $this->hr->get_companies(),
                    'departments'    => $this->hr->get_departments(),
                    'designations'   => $this->hr->get_designations(),
                    'staff_list'     => $this->hr->get_all_staff(),
                    'staffing_plans' => $this->db->order_by('id','DESC')->get($p . 'hr_staffing_plans')->result(),
                    'sub'            => 'job_requisition',
                ];
                $this->load->view('xetuu_hr/admin/setup/job_requisition_form', $form_data);
                return;
            }

            // List view
            $this->db->select('r.*, c.name AS company_name, d.name AS department_name, des.name AS designation_name, CONCAT(s.firstname," ",s.lastname) AS requester_name');
            $this->db->from($p . 'hr_job_requisitions r');
            $this->db->join($p . 'hr_companies c', 'r.company_id = c.id', 'left');
            $this->db->join($p . 'hr_departments d', 'r.department_id = d.id', 'left');
            $this->db->join($p . 'hr_designations des', 'r.designation_id = des.id', 'left');
            $this->db->join('tblstaff s', 'r.requested_by = s.staffid', 'left');
            $this->db->order_by('r.id', 'DESC');
            $data['rows'] = $this->db->get()->result();
            $data['sub']  = 'job_requisition';
            $this->load->view('xetuu_hr/admin/setup/job_requisition_list', $data);
            return;
        }

        if ($sub === 'interview_type') {
            $data['rows'] = $this->hr->setup_list('hr_interview_types');
            $this->load->view('xetuu_hr/admin/setup/interview_type', $data);
            return;
        }

        if ($sub === 'interview_round') {
            if ($this->input->post()) {
                $this->_save_interview_round();
                return;
            }

            // Analytics for sidebar
            $data['stats_interviews_total']     = (int)$this->db->count_all($p . 'hr_interviews');
            $data['stats_interviews_scheduled'] = (int)$this->db->where('status', 'Scheduled')->count_all_results($p . 'hr_interviews');
            $data['stats_interviews_completed'] = (int)$this->db->where('status', 'Completed')->count_all_results($p . 'hr_interviews');
            $data['stats_interviews_cancelled'] = (int)$this->db->where('status', 'Cancelled')->count_all_results($p . 'hr_interviews');
            $data['stats_interviews_passed']    = (int)$this->db->where('result', 'Pass')->count_all_results($p . 'hr_interviews');
            $data['stats_interviews_failed']    = (int)$this->db->where('result', 'Fail')->count_all_results($p . 'hr_interviews');

            // Interviews per round
            $this->db->select('ir.name AS round_name, COUNT(i.id) AS total');
            $this->db->from($p . 'hr_interviews i');
            $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
            $this->db->group_by('i.interview_round_id');
            $this->db->order_by('total', 'DESC');
            $this->db->limit(5);
            $data['stats_by_round'] = $this->db->get()->result();

            // Recent 5 interviews
            $this->db->select('i.interview_date, i.status, i.result, CONCAT(a.first_name," ",a.last_name) AS applicant_name, ir.name AS round_name');
            $this->db->from($p . 'hr_interviews i');
            $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
            $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
            $this->db->order_by('i.interview_date', 'DESC');
            $this->db->limit(5);
            $data['stats_recent'] = $this->db->get()->result();

            // Rows + skills per round
            $data['rows'] = $this->hr->setup_list('hr_interview_rounds');
            $round_skills = $this->db->order_by('round_id,sort_order')->get($p . 'hr_interview_round_skills')->result();
            $skills_by_round = [];
            foreach ($round_skills as $rs) {
                $skills_by_round[$rs->round_id][] = $rs->skill_name;
            }
            $data['skills_by_round'] = $skills_by_round;

            $this->load->view('xetuu_hr/admin/setup/interview_round', $data);
            return;
        }

        if ($sub === 'appointment_letter_template') {
            $data['rows'] = $this->hr->setup_list('hr_appointment_letter_templates');
            // Pre-count clauses per template to avoid N+1 in the view
            $p_t = $this->hr->p();
            $term_counts = [];
            if ($this->db->table_exists($p_t . 'hr_appointment_letter_template_terms')) {
                $tc = $this->db->select('template_id, COUNT(*) as cnt')
                    ->group_by('template_id')
                    ->get($p_t . 'hr_appointment_letter_template_terms')->result();
                foreach ($tc as $t) { $term_counts[(int)$t->template_id] = (int)$t->cnt; }
            }
            $data['term_counts'] = $term_counts;
            $this->load->view('xetuu_hr/admin/setup/appointment_letter_template', $data);
            return;
        }

        if ($sub === 'recruitment_settings') {
            if ($this->input->post()) {
                $keys = [
                    // General
                    'rec_applicant_number_prefix',
                    'rec_offer_number_prefix',
                    'rec_letter_number_prefix',
                    // Hiring process
                    'rec_default_stage',
                    'rec_auto_stage_interview',
                    'rec_auto_stage_offer',
                    'rec_auto_stage_hired',
                    'rec_offer_expiry_days',
                    // Interview & scoring
                    'rec_skill_rating_max',
                    'rec_pass_threshold',
                    'rec_send_interview_reminder',
                    'rec_reminder_hours',
                    // Notifications
                    'allow_portal_applications',
                    'new_applicant_alert_email',
                    'rec_notify_applicant_received',
                    'rec_notify_applicant_interview',
                    'rec_notify_applicant_offer',
                    'rec_cc_hr_manager',
                    'rec_hr_manager_email',
                    // Career portal
                    'rec_portal_title',
                    'rec_portal_description',
                    'rec_show_salary',
                ];
                foreach ($keys as $k) {
                    $this->hr->save_setting($k, $this->input->post($k, true) ?? '');
                }
                set_alert('success', 'Recruitment settings saved.');
                redirect(admin_url('xetuu_hr/setup/recruitment_settings'));
                return;
            }
            $data['settings'] = $this->hr->get_all_settings();
            $this->load->view('xetuu_hr/admin/setup/recruitment_settings', $data);
            return;
        }

        // ── Default settings & other generic setups ───────────────────────────
        if ($sub === 'settings') {
            if ($action === 'seed') {
                $this->hr->run_seeds();
                set_alert('success', 'Default departments, designations and job groups loaded successfully.');
                redirect(admin_url('xetuu_hr/setup/settings'));
                return;
            }
            if ($this->input->post()) {
                $keys = [
                    // Organisation
                    'employee_number_prefix', 'employee_number_digits', 'default_currency',
                    'consultancy_mode', 'financial_year_start', 'work_week',
                    'working_hours_per_day', 'probation_days', 'notice_period_days',
                    // Leave
                    'leave_year_type', 'leave_carry_over', 'leave_carry_over_max',
                    'leave_negative_balance', 'leave_approval_levels', 'leave_auto_approve_days',
                    // Payroll
                    'payroll_period', 'payroll_pay_day', 'payslip_template',
                    'email_payslips', 'overtime_multiplier',
                    // Notifications
                    'notify_hr_leave_request', 'notify_manager_leave_request',
                    'notify_employee_payslip', 'contract_expiry_warn_days', 'probation_end_warn_days',
                    // Self-Service
                    'selfservice_enabled', 'selfservice_leave_apply',
                    'selfservice_payslip_download', 'selfservice_profile_update',
                ];
                // Checkboxes default to 0 when unchecked
                $checkboxes = [
                    'consultancy_mode', 'leave_carry_over', 'leave_negative_balance',
                    'email_payslips', 'notify_hr_leave_request', 'notify_manager_leave_request',
                    'notify_employee_payslip', 'selfservice_enabled', 'selfservice_leave_apply',
                    'selfservice_payslip_download', 'selfservice_profile_update',
                ];
                foreach ($keys as $k) {
                    $val = in_array($k, $checkboxes)
                        ? ($this->input->post($k) ? '1' : '0')
                        : ($this->input->post($k, true) ?? '');
                    $this->hr->save_setting($k, $val);
                }
                set_alert('success', 'Settings saved.');
                redirect(admin_url('xetuu_hr/setup/settings'));
                return;
            }
            $data['settings'] = $this->hr->get_all_settings();
            $this->load->view('xetuu_hr/admin/setup/settings', $data);
            return;
        }

        switch ($sub) {
            case 'company':
                if (!$this->db->field_exists('payroll_company_id', $p . 'hr_companies')) {
                    $this->db->query("ALTER TABLE `{$p}hr_companies` ADD COLUMN `payroll_company_id` INT DEFAULT NULL");
                }
                $data['rows']              = $this->hr->setup_list('hr_companies');
                $data['payroll_companies'] = $this->payroll->get_payroll_companies();
                break;
            case 'branch':
                $data['rows'] = $this->hr->setup_branches_list();
                break;
            case 'designation':
                $data['rows'] = $this->hr->setup_list('hr_designations');
                break;
            case 'employee_group':
                $data['rows'] = $this->hr->setup_list('hr_employee_groups');
                break;
            case 'employee_grade':
                $data['rows'] = $this->hr->setup_list('hr_employee_grades');
                break;
        }

        $this->load->view('xetuu_hr/admin/setup/list', $data);
    }

    private function _save_department()
    {
        $id   = (int)$this->input->post('dept_id');
        $post = $this->input->post(null, true);

        $approvers = [
            'shift_request' => (array)$this->input->post('shift_approver_id'),
            'leave'         => (array)$this->input->post('leave_approver_id'),
            'expense'       => (array)$this->input->post('expense_approver_id'),
        ];

        $dept_data = [
            'name'                => $post['name'] ?? '',
            'company_id'          => (int)($post['company_id'] ?? 0),
            'parent_id'           => !empty($post['parent_id']) ? (int)$post['parent_id'] : null,
            'manager_id'          => !empty($post['manager_id']) ? (int)$post['manager_id'] : null,
            'is_group'            => isset($post['is_group']) ? 1 : 0,
            'disabled'            => isset($post['disabled']) ? 1 : 0,
            'payroll_cost_center' => $post['payroll_cost_center'] ?? '',
            'leave_block_list'    => $post['leave_block_list'] ?? '',
            'active'              => 1,
        ];

        if ($id) {
            $this->hr->dept_update($id, $dept_data, $approvers);
            set_alert('success', 'Department updated successfully.');
            redirect(admin_url('xetuu_hr/setup/department/edit/' . $id));
        } else {
            $new_id = $this->hr->dept_insert($dept_data, $approvers);
            set_alert('success', 'Department created successfully.');
            redirect(admin_url('xetuu_hr/setup/department/edit/' . $new_id));
        }
    }

    private function _save_staffing_plan()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();
        
        $parent_data = [
            'name'                   => $this->input->post('name', true),
            'company_id'             => (int)$this->input->post('company_id'),
            'department_id'          => $this->input->post('department_id') ? (int)$this->input->post('department_id') : null,
            'from_date'              => $this->input->post('from_date', true),
            'to_date'                => $this->input->post('to_date', true),
            'total_estimated_budget' => (float)$this->input->post('total_estimated_budget'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_staffing_plans', $parent_data);
            $plan_id = $id;
            // Delete old details to recreate them
            $this->db->where('staffing_plan_id', $plan_id)->delete($p . 'hr_staffing_plan_details');
            set_alert('success', 'Staffing Plan updated successfully.');
        } else {
            $parent_data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_staffing_plans', $parent_data);
            $plan_id = $this->db->insert_id();
            set_alert('success', 'Staffing Plan created successfully.');
        }

        // Save grid details
        $designations = $this->input->post('designation_id', true);
        $vacancies    = $this->input->post('vacancies', true);
        $est_costs    = $this->input->post('estimated_cost_per_position', true);
        $total_costs  = $this->input->post('total_estimated_cost', true);
        $no_positions = $this->input->post('number_of_positions', true);

        if (is_array($designations)) {
            foreach ($designations as $index => $desg_id) {
                if (empty($desg_id)) continue;
                $detail_data = [
                    'staffing_plan_id'            => $plan_id,
                    'designation_id'              => (int)$desg_id,
                    'vacancies'                   => (int)($vacancies[$index] ?? 0),
                    'estimated_cost_per_position' => (float)($est_costs[$index] ?? 0),
                    'total_estimated_cost'        => (float)($total_costs[$index] ?? 0),
                    'number_of_positions'         => (int)($no_positions[$index] ?? 0),
                ];
                $this->db->insert($p . 'hr_staffing_plan_details', $detail_data);
            }
        }

        redirect(admin_url('xetuu_hr/setup/staffing_plan'));
    }

    private function _save_job_requisition_form()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();

        $data = [
            'company_id'       => $this->input->post('company_id') ? (int)$this->input->post('company_id') : null,
            'department_id'    => $this->input->post('department_id') ? (int)$this->input->post('department_id') : null,
            'designation_id'   => (int)$this->input->post('designation_id'),
            'staffing_plan_id' => $this->input->post('staffing_plan_id') ? (int)$this->input->post('staffing_plan_id') : null,
            'requested_by'     => (int)$this->input->post('requested_by'),
            'no_of_positions'  => (int)$this->input->post('no_of_positions') ?: 1,
            'expected_salary'  => $this->input->post('expected_salary') !== '' ? (float)$this->input->post('expected_salary') : null,
            'job_description'  => $this->input->post('job_description', true),
            'reason'           => $this->input->post('reason', true),
            'status'           => $this->input->post('status', true) ?: 'Pending',
            'posting_date'     => $this->input->post('posting_date', true) ?: date('Y-m-d'),
            'expected_by_date' => $this->input->post('expected_by_date', true) ?: null,
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_job_requisitions', $data);
            set_alert('success', 'Job Requisition updated successfully.');
            redirect(admin_url('xetuu_hr/setup/job_requisition/edit/' . $id));
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_job_requisitions', $data);
            $new_id = $this->db->insert_id();
            // Generate requisition number HR-HIREQ-XXXXX
            $req_num = 'HR-HIREQ-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $new_id)->update($p . 'hr_job_requisitions', ['requisition_number' => $req_num]);
            set_alert('success', 'Job Requisition created successfully.');
            redirect(admin_url('xetuu_hr/setup/job_requisition/edit/' . $new_id));
        }
    }

    public function get_job_requisitions_json($dept_id)
    {
        $p = $this->hr->p();
        $this->db->select('r.designation_id, d.name AS designation_name, SUM(r.no_of_positions) AS number_of_positions, AVG(r.expected_salary) AS estimated_cost_per_position');
        $this->db->from($p . 'hr_job_requisitions r');
        $this->db->join($p . 'hr_designations d', 'r.designation_id = d.id', 'left');
        $this->db->where('r.department_id', (int)$dept_id);
        $this->db->where('r.status', 'Approved');
        $this->db->where('(r.staffing_plan_id IS NULL OR r.staffing_plan_id = 0)');
        $this->db->group_by('r.designation_id, d.name');
        $requisitions = $this->db->get()->result_array();

        foreach ($requisitions as &$req) {
            $desg_id = $req['designation_id'];
            $req['current_count'] = (int)$this->db->where('designation_id', $desg_id)->where('active', 1)->count_all_results($p . 'hr_employees');
            $req['vacancies'] = max(0, (int)$req['number_of_positions'] - $req['current_count']);
            $req['total_estimated_cost'] = (float)$req['estimated_cost_per_position'] * (int)$req['number_of_positions'];
        }

        echo json_encode($requisitions);
        exit;
    }

    private function _save_setup($sub)
    {
        $id   = (int)$this->input->post('id');
        $post = $this->input->post(null, true);
        unset($post['id'], $post[$this->security->get_csrf_token_name()]);

        if ($id) {
            $this->hr->setup_update($sub, $id, $post);
            $saved_id = $id;
            set_alert('success', _l('updated_successfully'));
        } else {
            $saved_id = $this->hr->setup_insert($sub, $post);
            set_alert('success', _l('added_successfully'));
        }

        if ($sub === 'company' && $saved_id) {
            $this->_sync_hr_company_to_payroll((int)$saved_id);
        }

        redirect(admin_url('xetuu_hr/setup/' . $sub));
    }

    private function _sync_hr_company_to_payroll($hr_company_id)
    {
        $p   = $this->hr->p();
        $hrc = $this->db->where('id', $hr_company_id)->get($p . 'hr_companies')->row();
        if (!$hrc) return;

        if (!empty($hrc->payroll_company_id)) {
            // Sync name only
            $this->db->where('id', (int)$hrc->payroll_company_id)
                     ->update($p . 'hr_payroll_companies', ['name' => $hrc->name]);
        } else {
            // Auto-create payroll company
            $this->db->insert($p . 'hr_payroll_companies', [
                'name'         => $hrc->name,
                'country_code' => 'KE',
                'currency'     => 'KES',
                'active'       => 1,
                'date_created' => date('Y-m-d H:i:s'),
            ]);
            $payco_id = $this->db->insert_id();
            if ($payco_id) {
                $this->db->where('id', $hr_company_id)
                         ->update($p . 'hr_companies', ['payroll_company_id' => $payco_id]);
            }
        }
    }

    private function _delete_setup($sub, $id)
    {
        $this->hr->setup_delete($sub, $id);
        set_alert('success', _l('deleted_successfully'));
        redirect(admin_url('xetuu_hr/setup/' . $sub));
    }

    public function org_chart()
    {
        $data = [
            'title' => _l('xetuu_hr_org_chart'),
            'tree'  => $this->hr->get_org_tree(),
        ];
        $this->load->view('xetuu_hr/admin/employees/org_chart', $data);
    }

    // ── Recruitment ───────────────────────────────────────────────────────────

    public function recruitment($sub = null, $action = null, $id = null)
    {
        $sub = $sub ?: 'dashboard';
        $p   = $this->hr->p();
        $data = ['title' => 'Recruitment', 'xhr_active' => 'recruitment'];

        switch ($sub) {
            case 'applicants':
                // Inline migration for existing installs
                $_app_cols = [
                    'applicant_number' => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `applicant_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
                    'source'           => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `source` VARCHAR(50) DEFAULT NULL AFTER `phone`",
                    'source_name'      => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `source_name` VARCHAR(200) DEFAULT NULL AFTER `source`",
                    'cover_letter'     => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `cover_letter` LONGTEXT DEFAULT NULL AFTER `source_name`",
                ];
                foreach ($_app_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_applicants')) {
                        $this->db->query($_sql);
                    }
                }
                // Modify stage column to VARCHAR to support custom stages / interview types
                $_app_stage = $this->db->query("SHOW COLUMNS FROM `{$p}hr_applicants` LIKE 'stage'")->row();
                if ($_app_stage && strpos($_app_stage->Type, 'enum') !== false) {
                    $this->db->query("ALTER TABLE `{$p}hr_applicants` MODIFY COLUMN `stage` VARCHAR(100) NOT NULL DEFAULT 'Applied'");
                }

                if ($this->input->post('bulk_action')) {
                    $this->_bulk_update_applicants();
                    return;
                }
                if ($this->input->post()) {
                    $this->_save_applicant();
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('id', $id)->delete($p . 'hr_applicants');
                    set_alert('success', 'Applicant deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/applicants'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $applicant = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_applicants')->row()
                        : null;
                    // Fetch related interviews for this applicant
                    $applicant_interviews = [];
                    if ($applicant) {
                        $this->db->select('i.*, ir.name AS round_name, it.name AS type_name, CONCAT(s.firstname," ",s.lastname) AS interviewer_name');
                        $this->db->from($p . 'hr_interviews i');
                        $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                        $this->db->join($p . 'hr_interview_types it', 'i.interview_type_id = it.id', 'left');
                        $this->db->join('tblstaff s', 'i.interviewer_id = s.staffid', 'left');
                        $this->db->where('i.applicant_id', (int)$applicant->id);
                        $this->db->order_by('i.interview_date', 'ASC');
                        $applicant_interviews = $this->db->get()->result();
                    }
                    $form_data = [
                        'title'                => $applicant ? 'Edit: ' . $applicant->first_name . ' ' . $applicant->last_name : 'New Job Applicant',
                        'applicant'            => $applicant,
                        'openings'             => $this->hr->get_job_openings(),
                        'staff_list'           => $this->hr->get_all_staff(),
                        'applicant_interviews' => $applicant_interviews,
                        'interview_types'      => $this->db->get($p . 'hr_interview_types')->result(),
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/applicant_form', $form_data);
                    return;
                }
                // List view
                $this->db->select('a.*, jo.title AS opening_title, jo.designation_id');
                $this->db->from($p . 'hr_applicants a');
                $this->db->join($p . 'hr_job_openings jo', 'a.job_opening_id = jo.id', 'left');
                $this->db->order_by('a.id', 'DESC');
                $data['applicants'] = $this->db->get()->result();
                $data['interview_types'] = $this->db->get($p . 'hr_interview_types')->result();
                $this->load->view('xetuu_hr/admin/recruitment/applicant_list', $data);
                break;

            case 'job_openings':
                // Inline migration for existing installs
                $_jo_cols = [
                    'job_requisition_id' => "ALTER TABLE `{$p}hr_job_openings` ADD COLUMN `job_requisition_id` INT(11) DEFAULT NULL AFTER `designation_id`",
                    'publish_on_website' => "ALTER TABLE `{$p}hr_job_openings` ADD COLUMN `publish_on_website` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`",
                ];
                foreach ($_jo_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_job_openings')) {
                        $this->db->query($_sql);
                    }
                }

                if ($this->input->post()) {
                    $this->_save_job_opening();
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('id', $id)->delete($p . 'hr_job_openings');
                    set_alert('success', 'Job opening deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/job_openings'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $opening = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_job_openings')->row()
                        : null;
                    // Build plan details map: [plan_id][designation_id] => detail row
                    $sp_details_raw = $this->db->get($p . 'hr_staffing_plan_details')->result();
                    $sp_details_map = [];
                    foreach ($sp_details_raw as $_d) {
                        $sp_details_map[(int)$_d->staffing_plan_id][(int)$_d->designation_id] = [
                            'number_of_positions' => (int)$_d->number_of_positions,
                            'vacancies'           => (int)$_d->vacancies,
                        ];
                    }
                    $form_data = [
                        'title'               => $opening ? 'Edit: ' . $opening->title : 'New Job Opening',
                        'opening'             => $opening,
                        'companies'           => $this->hr->get_companies(),
                        'departments'         => $this->hr->get_departments(),
                        'designations'        => $this->hr->get_designations(),
                        'requisitions'        => $this->db->select('id, requisition_number, designation_id')->where('status', 'Open & Approved')->get($p . 'hr_job_requisitions')->result(),
                        'staffing_plans'      => $this->db->order_by('id','DESC')->get($p . 'hr_staffing_plans')->result(),
                        'staffing_plan_details' => $sp_details_map,
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/job_opening_form', $form_data);
                    return;
                }
                // List view
                $this->db->select('jo.*, c.name AS company_name, d.name AS department_name, des.name AS designation_name');
                $this->db->from($p . 'hr_job_openings jo');
                $this->db->join($p . 'hr_companies c', 'jo.company_id = c.id', 'left');
                $this->db->join($p . 'hr_departments d', 'jo.department_id = d.id', 'left');
                $this->db->join($p . 'hr_designations des', 'jo.designation_id = des.id', 'left');
                $this->db->order_by('jo.id', 'DESC');
                $data['openings'] = $this->db->get()->result();
                $this->load->view('xetuu_hr/admin/recruitment/job_opening_list', $data);
                break;

            case 'interviews':
                // Inline migration for existing installs
                $_int_cols = [
                    'interview_number' => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `interview_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
                    'job_opening_id'   => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `job_opening_id` INT(11) DEFAULT NULL AFTER `applicant_id`",
                    'from_time'        => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `from_time` TIME DEFAULT NULL AFTER `interview_date`",
                    'to_time'          => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `to_time` TIME DEFAULT NULL AFTER `from_time`",
                    'resume_link'      => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `resume_link` VARCHAR(500) DEFAULT NULL AFTER `to_time`",
                    'rating'           => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `rating` TINYINT(1) DEFAULT NULL AFTER `resume_link`",
                ];
                foreach ($_int_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_interviews')) {
                        $this->db->query($_sql);
                    }
                }
                // Fix: ensure interview_date is nullable (MySQL 9+ rejects NOT NULL without DEFAULT)
                $this->db->query("ALTER TABLE `{$p}hr_interviews` MODIFY COLUMN `interview_date` DATE DEFAULT NULL");

                if ($this->input->post()) {
                    $this->_save_interview();
                    return;
                }
                if ($action === 'send_email' && $id) {
                    if ($this->_send_interview_email($id)) {
                        set_alert('success', 'Interview invitation email sent to applicant.');
                    } else {
                        set_alert('danger', 'Failed to send email. Please check your email configuration.');
                    }
                    redirect(admin_url('xetuu_hr/recruitment/interviews/edit/' . $id));
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('id', $id)->delete($p . 'hr_interviews');
                    set_alert('success', 'Interview deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/interviews'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $interview = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_interviews')->row()
                        : null;
                    $applicants_raw = $this->hr->get_applicants();
                    // Build applicant→job_opening map for JS auto-fill
                    $applicant_map = [];
                    foreach ($applicants_raw as $ap) {
                        $applicant_map[(int)$ap->id] = [
                            'name'           => $ap->first_name . ' ' . $ap->last_name,
                            'job_opening_id' => $ap->job_opening_id ? (int)$ap->job_opening_id : null,
                            'stage'          => $ap->stage ?? 'Applied',
                        ];
                    }
                    $prefill_applicant_id = (int)$this->input->get('applicant_id');
                    $prefill_next_round_id = (int)$this->input->get('prefill_next_round_id');
                    $form_data = [
                        'title'                 => $interview ? 'Edit: ' . ($interview->interview_number ?? 'Interview') : 'New Interview',
                        'interview'             => $interview,
                        'applicants'            => $applicants_raw,
                        'openings'              => $this->hr->get_job_openings(),
                        'interview_types'       => $this->db->get($p . 'hr_interview_types')->result(),
                        'interview_rounds'      => $this->db->get($p . 'hr_interview_rounds')->result(),
                        'staff_list'            => $this->hr->get_all_staff(),
                        'designations'          => $this->hr->get_designations(),
                        'applicant_map_json'    => json_encode($applicant_map, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT),
                        'prefill_applicant_id'  => $prefill_applicant_id,
                        'prefill_next_round_id' => $prefill_next_round_id,
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/interview_form', $form_data);
                    return;
                }
                // List view — with analytics
                $this->db->select('i.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name, it.name AS interview_type_name, ir.name AS interview_round_name, CONCAT(s.firstname," ",s.lastname) AS interviewer_name');
                $this->db->from($p . 'hr_interviews i');
                $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
                $this->db->join($p . 'hr_interview_types it', 'i.interview_type_id = it.id', 'left');
                $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                $this->db->join('tblstaff s', 'i.interviewer_id = s.staffid', 'left');
                $this->db->order_by('i.interview_date', 'DESC');
                $data['interviews'] = $this->db->get()->result();

                // Analytics
                $data['stat_total']     = (int)$this->db->count_all($p . 'hr_interviews');
                $data['stat_scheduled'] = (int)$this->db->where('status','Scheduled')->count_all_results($p . 'hr_interviews');
                $data['stat_completed'] = (int)$this->db->where('status','Completed')->count_all_results($p . 'hr_interviews');
                $data['stat_cancelled'] = (int)$this->db->where('status','Cancelled')->count_all_results($p . 'hr_interviews');
                $data['stat_passed']    = (int)$this->db->where('result','Pass')->count_all_results($p . 'hr_interviews');
                $data['stat_failed']    = (int)$this->db->where('result','Fail')->count_all_results($p . 'hr_interviews');

                // Upcoming scheduled (next 5)
                $this->db->select('i.interview_date, i.from_time, i.status, CONCAT(a.first_name," ",a.last_name) AS applicant_name, ir.name AS round_name');
                $this->db->from($p . 'hr_interviews i');
                $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
                $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                $this->db->where('i.status', 'Scheduled');
                $this->db->where('(i.interview_date >= CURDATE() OR i.interview_date IS NULL)');
                $this->db->order_by('i.interview_date', 'ASC');
                $this->db->limit(5);
                $data['stat_upcoming'] = $this->db->get()->result();

                // Interviews per round
                $this->db->select('ir.name AS round_name, COUNT(i.id) AS total');
                $this->db->from($p . 'hr_interviews i');
                $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                $this->db->group_by('i.interview_round_id');
                $this->db->order_by('total', 'DESC');
                $this->db->limit(5);
                $data['stat_by_round'] = $this->db->get()->result();

                $this->load->view('xetuu_hr/admin/recruitment/interview_list', $data);
                break;

            case 'offers':
                // Inline migration: new columns on hr_job_offers
                $_offer_cols = [
                    'offer_number'   => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `offer_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
                    'offer_date'     => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `offer_date` DATE DEFAULT NULL AFTER `offer_number`",
                    'company'        => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `company` VARCHAR(200) DEFAULT NULL AFTER `salary_offered`",
                    'letter_head'    => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `letter_head` VARCHAR(200) DEFAULT NULL AFTER `company`",
                    'print_heading'  => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `print_heading` VARCHAR(200) DEFAULT NULL AFTER `letter_head`",
                    'terms_conditions' => "ALTER TABLE `{$p}hr_job_offers` ADD COLUMN `terms_conditions` TEXT DEFAULT NULL AFTER `print_heading`",
                ];
                foreach ($_offer_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_job_offers')) {
                        $this->db->query($_sql);
                    }
                }
                // New table for offer terms rows
                if (!$this->db->table_exists($p . 'hr_job_offer_terms')) {
                    $this->db->query("CREATE TABLE `{$p}hr_job_offer_terms` (`id` INT(11) NOT NULL AUTO_INCREMENT, `offer_id` INT(11) NOT NULL, `offer_term` VARCHAR(300) NOT NULL, `value_description` TEXT DEFAULT NULL, `sort_order` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `offer_id` (`offer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }
                // Change status column to VARCHAR if still ENUM
                $offer_status_col = $this->db->query("SHOW COLUMNS FROM `{$p}hr_job_offers` LIKE 'status'")->row();
                if ($offer_status_col && strpos($offer_status_col->Type, 'enum') !== false) {
                    $this->db->query("ALTER TABLE `{$p}hr_job_offers` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'Awaiting Response'");
                }

                if ($this->input->post()) {
                    $this->_save_job_offer();
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('offer_id', $id)->delete($p . 'hr_job_offer_terms');
                    $this->db->where('id', $id)->delete($p . 'hr_job_offers');
                    set_alert('success', 'Job offer deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/offers'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $offer = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_job_offers')->row()
                        : null;

                    $prefill_applicant_id = (int)$this->input->get('applicant_id');
                    if (!$offer && $prefill_applicant_id) {
                        $offer = (object)[
                            'applicant_id' => $prefill_applicant_id,
                            'job_opening_id' => null,
                            'designation_id' => null,
                            'salary_offered' => null,
                            'offer_date' => null,
                            'joining_date' => null,
                            'company' => null,
                            'letter_head' => null,
                            'print_heading' => null,
                            'terms_conditions' => null,
                            'status' => 'Draft',
                            'offer_number' => null,
                        ];
                        $ap = $this->db->where('id', $prefill_applicant_id)->get($p . 'hr_applicants')->row();
                        if ($ap) {
                            $offer->job_opening_id = $ap->job_opening_id;
                            $offer->designation_id = null;
                            if ($ap->job_opening_id) {
                                $opening = $this->db->where('id', $ap->job_opening_id)->get($p . 'hr_job_openings')->row();
                                if ($opening) {
                                    $offer->designation_id = $opening->designation_id;
                                }
                            }
                        }
                    }

                    $offer_terms = $offer && isset($offer->id)
                        ? $this->db->where('offer_id', $offer->id)->order_by('sort_order')->get($p . 'hr_job_offer_terms')->result()
                        : [];
                    // Enrich offer with applicant + opening + designation names
                    if ($offer && $offer->applicant_id) {
                        $ap = $this->db->where('id', $offer->applicant_id)->get($p . 'hr_applicants')->row();
                        if ($ap) {
                            $offer->applicant_name  = $ap->first_name . ' ' . $ap->last_name;
                            $offer->applicant_email = $ap->email;
                        }
                    }
                    if ($offer && $offer->designation_id) {
                        $des = $this->db->where('id', $offer->designation_id)->get($p . 'hr_designations')->row();
                        if ($des) { $offer->designation_name = $des->name; }
                    }
                    $form_data = [
                        'title'        => $offer ? 'Edit Job Offer: ' . ($offer->offer_number ?? '') : 'New Job Offer',
                        'offer'        => $offer,
                        'offer_terms'  => $offer_terms,
                        'applicants'   => $this->hr->get_applicants(),
                        'openings'     => $this->hr->get_job_openings(),
                        'designations' => $this->hr->get_designations(),
                        'companies'    => $this->hr->get_companies(),
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/offer_form', $form_data);
                    return;
                }
                // List view — with analytics
                $this->db->select('o.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name, jo.title AS job_title, des.name AS designation_name');
                $this->db->from($p . 'hr_job_offers o');
                $this->db->join($p . 'hr_applicants a', 'o.applicant_id = a.id', 'left');
                $this->db->join($p . 'hr_job_openings jo', 'o.job_opening_id = jo.id', 'left');
                $this->db->join($p . 'hr_designations des', 'o.designation_id = des.id', 'left');
                $this->db->order_by('o.id', 'DESC');
                $data['offers'] = $this->db->get()->result();

                // Analytics
                $data['stat_total']     = $this->db->count_all($p . 'hr_job_offers');
                $data['stat_awaiting']  = $this->db->where('status', 'Awaiting Response')->count_all_results($p . 'hr_job_offers');
                $data['stat_accepted']  = $this->db->where('status', 'Accepted')->count_all_results($p . 'hr_job_offers');
                $data['stat_rejected']  = $this->db->where('status', 'Rejected')->count_all_results($p . 'hr_job_offers');
                $data['stat_sent']      = $this->db->where('status', 'Sent')->count_all_results($p . 'hr_job_offers');
                $data['stat_draft']     = $this->db->where('status', 'Draft')->count_all_results($p . 'hr_job_offers');

                // By designation breakdown
                $this->db->select('des.name AS designation_name, COUNT(o.id) AS cnt');
                $this->db->from($p . 'hr_job_offers o');
                $this->db->join($p . 'hr_designations des', 'o.designation_id = des.id', 'left');
                $this->db->group_by('o.designation_id');
                $this->db->order_by('cnt', 'DESC');
                $this->db->limit(5);
                $data['stat_by_designation'] = $this->db->get()->result();

                // Recent 5 offers
                $this->db->select('o.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name');
                $this->db->from($p . 'hr_job_offers o');
                $this->db->join($p . 'hr_applicants a', 'o.applicant_id = a.id', 'left');
                $this->db->order_by('o.id', 'DESC');
                $this->db->limit(5);
                $data['stat_recent'] = $this->db->get()->result();

                $this->load->view('xetuu_hr/admin/recruitment/offer_list', $data);
                break;

            case 'appointment_letters':
                // Inline migration: new columns on hr_appointment_letters
                $_apl_cols = [
                    'letter_number'     => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `letter_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
                    'appointment_date'  => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `appointment_date` DATE DEFAULT NULL AFTER `letter_number`",
                    'company'           => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `company` VARCHAR(200) DEFAULT NULL AFTER `appointment_date`",
                    'introduction'      => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `introduction` TEXT DEFAULT NULL AFTER `letter_content`",
                    'closing_statement' => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `closing_statement` TEXT DEFAULT NULL AFTER `introduction`",
                ];
                foreach ($_apl_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_appointment_letters')) {
                        $this->db->query($_sql);
                    }
                }
                // Convert status ENUM → VARCHAR
                $apl_st = $this->db->query("SHOW COLUMNS FROM `{$p}hr_appointment_letters` LIKE 'status'")->row();
                if ($apl_st && strpos($apl_st->Type, 'enum') !== false) {
                    $this->db->query("ALTER TABLE `{$p}hr_appointment_letters` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'Draft'");
                }
                // New columns on hr_appointment_letter_templates
                $_tpl_cols = [
                    'introduction'      => "ALTER TABLE `{$p}hr_appointment_letter_templates` ADD COLUMN `introduction` TEXT DEFAULT NULL AFTER `content`",
                    'closing_statement' => "ALTER TABLE `{$p}hr_appointment_letter_templates` ADD COLUMN `closing_statement` TEXT DEFAULT NULL AFTER `introduction`",
                ];
                foreach ($_tpl_cols as $_col => $_sql) {
                    if (!$this->db->field_exists($_col, $p . 'hr_appointment_letter_templates')) {
                        $this->db->query($_sql);
                    }
                }
                // New tables for terms rows
                if (!$this->db->table_exists($p . 'hr_appointment_letter_terms')) {
                    $this->db->query("CREATE TABLE `{$p}hr_appointment_letter_terms` (`id` INT(11) NOT NULL AUTO_INCREMENT, `letter_id` INT(11) NOT NULL, `title` VARCHAR(300) NOT NULL, `description` TEXT DEFAULT NULL, `sort_order` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `letter_id` (`letter_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }
                if (!$this->db->table_exists($p . 'hr_appointment_letter_template_terms')) {
                    $this->db->query("CREATE TABLE `{$p}hr_appointment_letter_template_terms` (`id` INT(11) NOT NULL AUTO_INCREMENT, `template_id` INT(11) NOT NULL, `title` VARCHAR(300) NOT NULL, `description` TEXT DEFAULT NULL, `sort_order` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `template_id` (`template_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }

                if ($this->input->post()) {
                    $this->_save_appointment_letter();
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('letter_id', $id)->delete($p . 'hr_appointment_letter_terms');
                    $this->db->where('id', $id)->delete($p . 'hr_appointment_letters');
                    set_alert('success', 'Appointment letter deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/appointment_letters'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $letter = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_appointment_letters')->row()
                        : null;
                    if (!$letter && $this->input->get('applicant_id')) {
                        $letter = (object)[
                            'applicant_id' => (int)$this->input->get('applicant_id'),
                            'letter_number' => null,
                            'appointment_date' => null,
                            'company' => null,
                            'introduction' => null,
                            'closing_statement' => null,
                            'letter_content' => null,
                            'status' => 'Draft',
                            'template_id' => null,
                        ];
                    }
                    $letter_terms = $letter && isset($letter->id)
                        ? $this->db->where('letter_id', $letter->id)->order_by('sort_order')->get($p . 'hr_appointment_letter_terms')->result()
                        : [];
                    // Enrich with applicant data
                    if ($letter && $letter->applicant_id) {
                        $ap = $this->db->where('id', $letter->applicant_id)->get($p . 'hr_applicants')->row();
                        if ($ap) {
                            $letter->applicant_name  = $ap->first_name . ' ' . $ap->last_name;
                            $letter->applicant_email = $ap->email;
                        }
                    }
                    $form_data = [
                        'title'        => $letter ? 'Edit Letter: ' . ($letter->letter_number ?? '') : 'New Appointment Letter',
                        'letter'       => $letter,
                        'letter_terms' => $letter_terms,
                        'applicants'   => $this->hr->get_applicants(),
                        'templates'    => $this->db->order_by('name')->get($p . 'hr_appointment_letter_templates')->result(),
                        'companies'    => $this->hr->get_companies(),
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/appointment_letter_form', $form_data);
                    return;
                }
                // List view — with analytics
                $this->db->select('al.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name, t.name AS template_name');
                $this->db->from($p . 'hr_appointment_letters al');
                $this->db->join($p . 'hr_applicants a', 'al.applicant_id = a.id', 'left');
                $this->db->join($p . 'hr_appointment_letter_templates t', 'al.template_id = t.id', 'left');
                $this->db->order_by('al.id', 'DESC');
                $data['letters'] = $this->db->get()->result();

                $data['stat_total']  = $this->db->count_all($p . 'hr_appointment_letters');
                $data['stat_draft']  = $this->db->where('status', 'Draft')->count_all_results($p . 'hr_appointment_letters');
                $data['stat_sent']   = $this->db->where('status', 'Sent')->count_all_results($p . 'hr_appointment_letters');
                $data['stat_signed'] = $this->db->where('status', 'Signed')->count_all_results($p . 'hr_appointment_letters');

                $this->db->select('al.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name');
                $this->db->from($p . 'hr_appointment_letters al');
                $this->db->join($p . 'hr_applicants a', 'al.applicant_id = a.id', 'left');
                $this->db->order_by('al.id', 'DESC');
                $this->db->limit(5);
                $data['stat_recent'] = $this->db->get()->result();

                $this->load->view('xetuu_hr/admin/recruitment/appointment_letter_list', $data);
                break;

            case 'interview_feedback':
                // Inline migration
                if (!$this->db->table_exists($p . 'hr_interview_round_skills')) {
                    $this->db->query("CREATE TABLE `{$p}hr_interview_round_skills` (`id` INT(11) NOT NULL AUTO_INCREMENT, `round_id` INT(11) NOT NULL, `skill_name` VARCHAR(200) NOT NULL, `sort_order` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `round_id` (`round_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }
                if (!$this->db->table_exists($p . 'hr_interview_feedback')) {
                    $this->db->query("CREATE TABLE `{$p}hr_interview_feedback` (`id` INT(11) NOT NULL AUTO_INCREMENT, `feedback_number` VARCHAR(40) DEFAULT NULL, `interview_id` INT(11) NOT NULL, `interviewer_id` INT(11) DEFAULT NULL, `result` VARCHAR(50) NOT NULL DEFAULT 'To Be Discussed', `feedback` TEXT DEFAULT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'Draft', `date_created` DATETIME NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }
                if (!$this->db->table_exists($p . 'hr_interview_feedback_skills')) {
                    $this->db->query("CREATE TABLE `{$p}hr_interview_feedback_skills` (`id` INT(11) NOT NULL AUTO_INCREMENT, `feedback_id` INT(11) NOT NULL, `skill_name` VARCHAR(200) NOT NULL, `rating` TINYINT(1) NOT NULL DEFAULT 0, `sort_order` INT(5) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `feedback_id` (`feedback_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
                }

                if ($this->input->post()) {
                    $this->_save_interview_feedback();
                    return;
                }
                if ($action === 'delete' && $id) {
                    $this->db->where('feedback_id', (int)$id)->delete($p . 'hr_interview_feedback_skills');
                    $this->db->where('id', (int)$id)->delete($p . 'hr_interview_feedback');
                    set_alert('success', 'Interview feedback deleted.');
                    redirect(admin_url('xetuu_hr/recruitment/interview_feedback'));
                    return;
                }
                if ($action === 'add' || $action === 'edit') {
                    $fb = ($action === 'edit' && $id)
                        ? $this->db->where('id', (int)$id)->get($p . 'hr_interview_feedback')->row()
                        : null;
                    $fb_skills = $fb ? $this->db->where('feedback_id', $fb->id)->order_by('sort_order')->get($p . 'hr_interview_feedback_skills')->result() : [];

                    // Enrich fb with interview/applicant/round data for display in form
                    if ($fb && $fb->interview_id) {
                        $this->db->select('i.interview_number, CONCAT(a.first_name," ",a.last_name) AS applicant_name, ir.name AS round_name, ir.id AS round_id');
                        $this->db->from($p . 'hr_interviews i');
                        $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
                        $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                        $this->db->where('i.id', $fb->interview_id);
                        $fb_iv = $this->db->get()->row();
                        if ($fb_iv) {
                            $fb->interview_number = $fb_iv->interview_number;
                            $fb->applicant_name   = $fb_iv->applicant_name;
                            $fb->round_name       = $fb_iv->round_name;
                            $fb->round_id         = $fb_iv->round_id;
                        }
                    }

                    $form_data = [
                        'title'           => $fb ? 'Edit Feedback: ' . ($fb->feedback_number ?? '') : 'New Interview Feedback',
                        'feedback'        => $fb,
                        'feedback_skills' => $fb_skills,
                        'interviews'      => $this->db->select('i.id, i.interview_number, i.interview_date, CONCAT(a.first_name," ",a.last_name) AS applicant_name')->from($p . 'hr_interviews i')->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left')->order_by('i.id','DESC')->get()->result(),
                        'staff_list'      => $this->hr->get_all_staff(),
                    ];
                    $this->load->view('xetuu_hr/admin/recruitment/interview_feedback_form', $form_data);
                    return;
                }
                // List view
                $this->db->select('fb.*, i.interview_number, i.interview_date, CONCAT(a.first_name," ",a.last_name) AS applicant_name, ir.name AS round_name, CONCAT(s.firstname," ",s.lastname) AS interviewer_name');
                $this->db->from($p . 'hr_interview_feedback fb');
                $this->db->join($p . 'hr_interviews i', 'fb.interview_id = i.id', 'left');
                $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
                $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
                $this->db->join('tblstaff s', 'fb.interviewer_id = s.staffid', 'left');
                $this->db->order_by('fb.id', 'DESC');
                $data['feedbacks'] = $this->db->get()->result();
                $this->load->view('xetuu_hr/admin/recruitment/interview_feedback_list', $data);
                break;

            default:
                $data['stats'] = $this->hr->get_recruitment_stats();
                $this->load->view('xetuu_hr/admin/recruitment/dashboard', $data);
        }
    }

    private function _save_applicant()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();

        $data = [
            'job_opening_id' => $this->input->post('job_opening_id') ? (int)$this->input->post('job_opening_id') : null,
            'first_name'     => $this->input->post('first_name', true),
            'last_name'      => $this->input->post('last_name', true),
            'email'          => $this->input->post('email', true),
            'phone'          => $this->input->post('phone', true),
            'source'         => $this->input->post('source', true) ?: null,
            'source_name'    => $this->input->post('source_name', true) ?: null,
            'cover_letter'   => $this->input->post('cover_letter', true) ?: null,
            'stage'          => $this->input->post('stage', true) ?: 'Applied',
        ];

        if ($id) {
            $old_applicant = $this->db->where('id', $id)->get($p . 'hr_applicants')->row();
            $old_stage = $old_applicant ? $old_applicant->stage : '';

            $this->db->where('id', $id)->update($p . 'hr_applicants', $data);
            set_alert('success', 'Applicant updated.');

            if ($old_stage !== $data['stage']) {
                if ($data['stage'] === 'Hired') {
                    $this->_hire_applicant($id);
                }
                $this->_send_applicant_stage_email($id, $data['stage']);
            }
            redirect(admin_url('xetuu_hr/recruitment/applicants/edit/' . $id));
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_applicants', $data);
            $new_id = $this->db->insert_id();
            $app_num = 'HR-APP-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $new_id)->update($p . 'hr_applicants', ['applicant_number' => $app_num]);
            set_alert('success', 'Applicant created.');

            $this->_send_applicant_stage_email($new_id, $data['stage']);
            if ($data['stage'] === 'Hired') {
                $this->_hire_applicant($new_id);
            }

            redirect(admin_url('xetuu_hr/recruitment/applicants/edit/' . $new_id));
        }
    }

    private function _save_job_opening()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();

        $data = [
            'company_id'          => (int)$this->input->post('company_id'),
            'department_id'       => $this->input->post('department_id') ? (int)$this->input->post('department_id') : null,
            'designation_id'      => $this->input->post('designation_id') ? (int)$this->input->post('designation_id') : null,
            'job_requisition_id'  => $this->input->post('job_requisition_id') ? (int)$this->input->post('job_requisition_id') : null,
            'title'               => $this->input->post('title', true),
            'description'         => $this->input->post('description', true),
            'no_of_positions'     => (int)$this->input->post('no_of_positions') ?: 1,
            'expected_salary'     => $this->input->post('expected_salary') !== '' ? (float)$this->input->post('expected_salary') : null,
            'close_date'          => $this->input->post('close_date', true) ?: null,
            'status'              => $this->input->post('status', true) ?: 'Open',
            'publish_on_website'  => $this->input->post('publish_on_website') ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_job_openings', $data);
            set_alert('success', 'Job opening updated.');
            redirect(admin_url('xetuu_hr/recruitment/job_openings/edit/' . $id));
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_job_openings', $data);
            $new_id = $this->db->insert_id();
            set_alert('success', 'Job opening created.');
            redirect(admin_url('xetuu_hr/recruitment/job_openings/edit/' . $new_id));
        }
    }

    private function _save_interview()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();
 
        $data = [
            'applicant_id'       => (int)$this->input->post('applicant_id'),
            'job_opening_id'     => $this->input->post('job_opening_id') ? (int)$this->input->post('job_opening_id') : null,
            'interview_type_id'  => $this->input->post('interview_type_id') ? (int)$this->input->post('interview_type_id') : null,
            'interview_round_id' => $this->input->post('interview_round_id') ? (int)$this->input->post('interview_round_id') : null,
            'interviewer_id'     => $this->input->post('interviewer_id') ? (int)$this->input->post('interviewer_id') : null,
            'interview_date'     => $this->input->post('interview_date', true) ?: null,
            'from_time'          => $this->input->post('from_time', true) ?: null,
            'to_time'            => $this->input->post('to_time', true) ?: null,
            'resume_link'        => $this->input->post('resume_link', true) ?: null,
            'rating'             => $this->input->post('rating') !== '' ? (int)$this->input->post('rating') : null,
            'status'             => $this->input->post('status', true) ?: 'Scheduled',
            'result'             => $this->input->post('result', true) ?: 'Pending',
            'comments'           => $this->input->post('comments', true) ?: null,
        ];
 
        $action_workflow = $this->input->post('action_workflow', true);
        if ($action_workflow) {
            $data['status'] = 'Completed';
            $data['result'] = ($action_workflow === 'fail_reject') ? 'Fail' : 'Pass';
        }

        $send_email = (int)$this->input->post('send_email_notification') === 1;

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_interviews', $data);
            $target_id = $id;
            set_alert('success', 'Interview updated.');
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_interviews', $data);
            $target_id = $this->db->insert_id();
            if (!$target_id) {
                $err = $this->db->error();
                set_alert('danger', 'Could not save interview' . (!empty($err['message']) ? ': ' . $err['message'] : '.'));
                redirect(admin_url('xetuu_hr/recruitment/interviews/add'));
                return;
            }
            $int_num = 'HR-INT-' . str_pad($target_id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $target_id)->update($p . 'hr_interviews', ['interview_number' => $int_num]);
            set_alert('success', 'Interview scheduled successfully.');
        }

        // Auto-sync feedback to hr_interview_feedback table
        if (isset($target_id) && ((int)($data['rating'] ?? 0) > 0 || !empty($data['comments']))) {
            $existing_fb = $this->db->where('interview_id', $target_id)->get($p . 'hr_interview_feedback')->row();
            $fb_result = 'To Be Discussed';
            if (($data['result'] ?? '') === 'Pass') $fb_result = 'Cleared';
            elseif (($data['result'] ?? '') === 'Fail') $fb_result = 'Not Cleared';
            
            $fb_data = [
                'interview_id' => $target_id,
                'interviewer_id' => $data['interviewer_id'] ?? get_staff_user_id(),
                'result' => $fb_result,
                'feedback' => $data['comments'] ?? '',
                'status' => 'Submitted'
            ];
            if ($existing_fb) {
                $this->db->where('id', $existing_fb->id)->update($p . 'hr_interview_feedback', $fb_data);
                $fb_id = $existing_fb->id;
            } else {
                $fb_data['date_created'] = date('Y-m-d H:i:s');
                $this->db->insert($p . 'hr_interview_feedback', $fb_data);
                $fb_id = $this->db->insert_id();
                $this->db->where('id', $fb_id)->update($p . 'hr_interview_feedback', ['feedback_number' => 'FEED-'.$fb_id]);
            }
            if ((int)($data['rating'] ?? 0) > 0) {
                $existing_skill = $this->db->where('feedback_id', $fb_id)->where('skill_name', 'Overall Rating')->get($p . 'hr_interview_feedback_skills')->row();
                if ($existing_skill) {
                    $this->db->where('id', $existing_skill->id)->update($p . 'hr_interview_feedback_skills', ['rating' => $data['rating']]);
                } else {
                    $this->db->insert($p . 'hr_interview_feedback_skills', [
                        'feedback_id' => $fb_id,
                        'skill_name' => 'Overall Rating',
                        'rating' => $data['rating'],
                        'sort_order' => 1
                    ]);
                }
            }
        }


        // Send email if selected
        if ($send_email) {
            $this->_send_interview_email($target_id);
        }

        // Stage / Redirection workflow
        if ($data['applicant_id']) {
            if ($action_workflow === 'pass_next_round') {
                $this->db->where('id', $data['applicant_id'])->update($p . 'hr_applicants', ['stage' => 'Interview']);
                $this->_send_applicant_stage_email($data['applicant_id'], 'Interview');
                $next_round_id = $this->_get_next_interview_round_id($data['applicant_id'], $data['interview_round_id']);
                redirect(admin_url('xetuu_hr/recruitment/interviews/add?applicant_id=' . $data['applicant_id'] . '&prefill_next_round_id=' . $next_round_id));
                return;
            } elseif ($action_workflow === 'pass_offer') {
                $this->db->where('id', $data['applicant_id'])->update($p . 'hr_applicants', ['stage' => 'Offer']);
                $this->_send_applicant_stage_email($data['applicant_id'], 'Offer');
                redirect(admin_url('xetuu_hr/recruitment/offers/add?applicant_id=' . $data['applicant_id']));
                return;
            } elseif ($action_workflow === 'fail_reject') {
                $this->db->where('id', $data['applicant_id'])->update($p . 'hr_applicants', ['stage' => 'Rejected']);
                $this->_send_applicant_stage_email($data['applicant_id'], 'Rejected');
                redirect(admin_url('xetuu_hr/recruitment/interviews'));
                return;
            } else {
                // If standard save/update has status completed and result fail, update applicant stage to Rejected
                if ($data['status'] === 'Completed' && $data['result'] === 'Fail') {
                    $this->db->where('id', $data['applicant_id'])->update($p . 'hr_applicants', ['stage' => 'Rejected']);
                    $this->_send_applicant_stage_email($data['applicant_id'], 'Rejected');
                }
            }
        }

        redirect(admin_url('xetuu_hr/recruitment/interviews/edit/' . $target_id));
    }

    private function _get_next_interview_round_id($applicant_id, $current_round_id = null)
    {
        $p = $this->hr->p();
        $rounds = $this->db->order_by('id', 'ASC')->get($p . 'hr_interview_rounds')->result();
        if (empty($rounds)) {
            return null;
        }

        if (!$current_round_id && $applicant_id) {
            $latest = $this->db->select('interview_round_id')
                               ->where('applicant_id', $applicant_id)
                               ->order_by('interview_date', 'DESC')
                               ->order_by('id', 'DESC')
                               ->limit(1)
                               ->get($p . 'hr_interviews')
                               ->row();
            if ($latest) {
                $current_round_id = $latest->interview_round_id;
            }
        }

        if (!$current_round_id) {
            return $rounds[0]->id;
        }

        $curr_idx = -1;
        foreach ($rounds as $idx => $r) {
            if ($r->id == $current_round_id) {
                $curr_idx = $idx;
                break;
            }
        }

        if ($curr_idx !== -1 && isset($rounds[$curr_idx + 1])) {
            return $rounds[$curr_idx + 1]->id;
        }

        return null;
    }

    private function _send_interview_email($interview_id)
    {
        $p = $this->hr->p();
        
        $this->db->select('i.*, CONCAT(a.first_name," ",a.last_name) AS applicant_name, a.email AS applicant_email, it.name AS interview_type_name, ir.name AS interview_round_name, jo.title AS opening_title');
        $this->db->from($p . 'hr_interviews i');
        $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
        $this->db->join($p . 'hr_interview_types it', 'i.interview_type_id = it.id', 'left');
        $this->db->join($p . 'hr_interview_rounds ir', 'i.interview_round_id = ir.id', 'left');
        $this->db->join($p . 'hr_job_openings jo', 'i.job_opening_id = jo.id', 'left');
        $this->db->where('i.id', (int)$interview_id);
        $interview = $this->db->get()->row();
        
        if (!$interview || empty($interview->applicant_email)) {
            return false;
        }

        $subject = "Interview Invitation: " . ($interview->opening_title ?? 'Job Opening') . " - " . ($interview->interview_round_name ?? 'Evaluation');
        
        $date = _d($interview->interview_date);
        $time = $interview->from_time ? date('H:i', strtotime($interview->from_time)) : '';
        if ($interview->to_time) {
            $time .= ' - ' . date('H:i', strtotime($interview->to_time));
        }

        $body = '
        <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; padding: 40px 20px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="background-color: #16a34a; padding: 30px 40px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em;">Interview Invitation</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 40px; color: #374151; font-size: 15px; line-height: 1.6;">
                        <p style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600; color: #111827;">Dear ' . htmlspecialchars($interview->applicant_name) . ',</p>
                        <p style="margin: 0 0 24px 0;">Thank you for your application. We are pleased to invite you for an interview session as part of our evaluation process for the <strong>\' . htmlspecialchars($interview->opening_title ?? \'Job Opening\') . \'</strong> position.</p>
                        
                        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="30%" style="font-weight: 600; color: #4b5563; padding-bottom: 10px; font-size: 14px;">Stage:</td>
                                    <td style="color: #111827; padding-bottom: 10px; font-size: 14px;">' . htmlspecialchars($interview->interview_round_name ?? 'General Round') . ' (' . htmlspecialchars($interview->interview_type_name ?? 'Standard') . ')</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #4b5563; padding-bottom: 10px; font-size: 14px;">Date:</td>
                                    <td style="color: #111827; padding-bottom: 10px; font-size: 14px;">' . $date . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; color: #4b5563; padding-bottom: 10px; font-size: 14px;">Time:</td>
                                    <td style="color: #111827; padding-bottom: 10px; font-size: 14px;">' . $time . '</td>
                                </tr>
                                ' . (!empty($interview->comments) ? '
                                <tr>
                                    <td style="font-weight: 600; color: #4b5563; vertical-align: top; font-size: 14px;">Notes / Location:</td>
                                    <td style="color: #111827; font-size: 14px;">' . nl2br(htmlspecialchars($interview->comments)) . '</td>
                                </tr>' : '') . '
                            </table>
                        </div>

                        <p style="margin: 0 0 30px 0;">Please confirm your availability for this session by replying directly to this email. We look forward to speaking with you.</p>
                        
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,<br><strong>Tagrit Kenya HR Team</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af;">
                        This email was sent automatically by Tagrit Kenya Careers.
                    </td>
                </tr>
            </table>
        </div>
        ';

        $this->load->model('emails_model');
        return $this->emails_model->send_simple_email($interview->applicant_email, $subject, $body);
    }

    private function _send_applicant_stage_email($applicant_id, $stage)
    {
        $p = $this->hr->p();
        $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
        if (!$applicant || empty($applicant->email)) {
            return false;
        }

        $opening = null;
        if ($applicant->job_opening_id) {
            $opening = $this->db->where('id', (int)$applicant->job_opening_id)->get($p . 'hr_job_openings')->row();
        }
        $job_title = $opening ? $opening->title : 'Job Opening';

        $subject = "Application Status Update: " . $job_title . " - " . $stage;
        
        if ($stage === 'Hired') {
            $subject = "Congratulations! You have been hired for the " . $job_title . " position";
            $message = "We are thrilled to inform you that you have been hired for the position of <strong>" . htmlspecialchars($job_title) . "</strong>! Our HR team will contact you shortly to discuss the next steps and onboarding.";
        } else if ($stage === 'Rejected') {
            $subject = "Application Update: " . $job_title;
            $message = "Thank you for taking the time to apply and interview for the <strong>" . htmlspecialchars($job_title) . "</strong> position. Unfortunately, we have decided to move forward with other candidates at this time. We wish you all the best in your job search.";
        } else if ($stage === 'Offer') {
            $message = "We are pleased to inform you that we are extending a job offer for the <strong>" . htmlspecialchars($job_title) . "</strong> position! Please review the details of the offer.";
        } else {
            $message = "We wanted to let you know that your application for the <strong>" . htmlspecialchars($job_title) . "</strong> position has progressed to the next stage: <strong>" . htmlspecialchars($stage) . "</strong>. We will be in touch with you regarding the next steps.";
        }

        $body = '
        <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; padding: 40px 20px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-collapse: collapse; overflow: hidden;">
                <tr>
                    <td style="background-color: #16a34a; padding: 30px 40px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em;">Application Status Update</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 40px; color: #374151; font-size: 15px; line-height: 1.6;">
                        <p style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600; color: #111827;">Dear ' . htmlspecialchars($applicant->first_name . ' ' . $applicant->last_name) . ',</p>
                        <p style="margin: 0 0 24px 0;">' . $message . '</p>
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,<br><strong>Tagrit Kenya HR Team</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af;">
                        This email was sent automatically by Tagrit Kenya Careers.
                    </td>
                </tr>
            </table>
        </div>
        ';

        $this->load->model('emails_model');
        return $this->emails_model->send_simple_email($applicant->email, $subject, $body);
    }

    private function _hire_applicant($applicant_id)
    {
        $p = $this->hr->p();
        $applicant = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
        if (!$applicant) {
            return false;
        }

        // Check if employee already exists
        $exists = $this->db->where('personal_email', $applicant->email)->count_all_results($p . 'hr_employees');
        if ($exists) {
            return false;
        }

        // Fetch designation / department from latest offer or job opening
        $offer   = $this->db->where('applicant_id', $applicant_id)->order_by('id','DESC')->get($p . 'hr_job_offers')->row();
        $opening = ($offer && $offer->job_opening_id)
            ? $this->db->where('id', $offer->job_opening_id)->get($p . 'hr_job_openings')->row()
            : ($applicant->job_opening_id ? $this->db->where('id', $applicant->job_opening_id)->get($p . 'hr_job_openings')->row() : null);

        $emp_data = [
            'first_name'     => $applicant->first_name,
            'last_name'      => $applicant->last_name,
            'personal_email' => $applicant->email,
            'email'          => $applicant->email,
            'mobile'         => $applicant->phone,
            'phone'          => $applicant->phone,
            'biography'      => $applicant->cover_letter,
            'status'         => 'Active',
        ];

        if ($offer) {
            $emp_data['designation_id'] = $offer->designation_id;
            $emp_data['date_of_joining'] = $offer->joining_date;
        }
        if ($opening) {
            $emp_data['company_id'] = $opening->company_id;
            $emp_data['department_id'] = $opening->department_id;
            if (empty($emp_data['designation_id'])) {
                $emp_data['designation_id'] = $opening->designation_id;
            }
        }

        $new_emp_id = $this->hr->add_employee($emp_data);
        if (!$new_emp_id) {
            return false;
        }

        // Copy CV/Resume if exists
        if (!empty($applicant->resume)) {
            $src = FCPATH . 'uploads/xetuu_hr/resumes/' . $applicant->resume;
            if (file_exists($src)) {
                $dest_dir = FCPATH . 'uploads/hr_employees/';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0755, true);
                }
                $dest_filename = uniqid('resume_', true) . '_' . $applicant->resume;
                $dst = $dest_dir . $dest_filename;
                if (copy($src, $dst)) {
                    $this->db->insert(db_prefix() . 'files', [
                        'rel_id'    => $new_emp_id,
                        'rel_type'  => 'hr_employee',
                        'file_name' => $dest_filename,
                        'filetype'  => mime_content_type($dst),
                        'dateadded' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // Create initial payroll contract if offer has salary
        if ($offer && $offer->salary_offered) {
            $this->load->model('xetuu_hr/Xr_payroll_model', 'payroll');
            $contract_data = [
                'employee_id'  => $new_emp_id,
                'wage'         => (float) $offer->salary_offered,
                'date_start'   => $offer->joining_date ?: date('Y-m-d'),
                'status'       => 'active',
                'working_days' => 5,
                'payment_method' => 'bank',
            ];
            if ($opening && !empty($opening->company_id)) {
                $contract_data['company_id'] = $opening->company_id;
            }
            // Auto-detect a default structure if none is provided
            $default_struct = $this->db->order_by('id', 'ASC')->limit(1)->get($p . 'hr_payroll_salary_structures')->row();
            if ($default_struct) {
                $contract_data['structure_id'] = $default_struct->id;
            }
            $this->payroll->save_payroll_contract($contract_data);
        }

        return $new_emp_id;
    }

    private function _bulk_update_applicants()
    {
        $p = $this->hr->p();
        $ids = $this->input->post('ids');
        $stage = $this->input->post('bulk_stage', true);

        if (empty($ids) || empty($stage)) {
            set_alert('warning', 'No applicants or stage selected.');
            redirect(admin_url('xetuu_hr/recruitment/applicants'));
            return;
        }

        $updated_count = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            $applicant = $this->db->where('id', $id)->get($p . 'hr_applicants')->row();
            if ($applicant) {
                if ($applicant->stage !== $stage) {
                    $this->db->where('id', $id)->update($p . 'hr_applicants', ['stage' => $stage]);
                    if ($stage === 'Hired') {
                        $this->_hire_applicant($id);
                    }
                    $this->_send_applicant_stage_email($id, $stage);
                    $updated_count++;
                }
            }
        }

        set_alert('success', "Successfully updated {$updated_count} applicant(s) and sent transition emails.");
        redirect(admin_url('xetuu_hr/recruitment/applicants'));
    }

    private function _save_job_offer()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();

        $data = [
            'applicant_id'     => (int)$this->input->post('applicant_id'),
            'job_opening_id'   => (int)$this->input->post('job_opening_id') ?: null,
            'designation_id'   => (int)$this->input->post('designation_id') ?: null,
            'salary_offered'   => (float)$this->input->post('salary_offered') ?: null,
            'offer_date'       => $this->input->post('offer_date', true) ?: null,
            'joining_date'     => $this->input->post('joining_date', true) ?: null,
            'status'           => $this->input->post('status', true) ?: 'Awaiting Response',
            'company'          => $this->input->post('company', true) ?: null,
            'letter_head'      => $this->input->post('letter_head', true) ?: null,
            'print_heading'    => $this->input->post('print_heading', true) ?: null,
            'terms_conditions' => $this->input->post('terms_conditions') ?: null,
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_job_offers', $data);
            set_alert('success', 'Job offer updated.');
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_job_offers', $data);
            $id = $this->db->insert_id();
            $num = 'HR-OFF-' . str_pad($id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $id)->update($p . 'hr_job_offers', ['offer_number' => $num]);
            // Move applicant stage to Offer
            $this->db->where('id', $data['applicant_id'])->update($p . 'hr_applicants', ['stage' => 'Offer']);
            $this->_send_applicant_stage_email($data['applicant_id'], 'Offer');
            set_alert('success', 'Job offer created.');
        }

        // Rebuild offer terms
        $this->db->where('offer_id', $id)->delete($p . 'hr_job_offer_terms');
        $terms      = $this->input->post('terms') ?: [];
        $term_vals  = $this->input->post('term_values') ?: [];
        foreach ($terms as $i => $term) {
            $term = trim($term);
            if ($term === '') { continue; }
            $this->db->insert($p . 'hr_job_offer_terms', [
                'offer_id'          => $id,
                'offer_term'        => $term,
                'value_description' => isset($term_vals[$i]) ? trim($term_vals[$i]) : '',
                'sort_order'        => $i,
            ]);
        }

        redirect(admin_url('xetuu_hr/recruitment/offers/edit/' . $id));
    }

    private function _save_appointment_letter()
    {
        $id           = (int)$this->input->post('id');
        $applicant_id = (int)$this->input->post('applicant_id');
        $template_id  = (int)$this->input->post('template_id') ?: null;
        $status       = $this->input->post('status', true) ?: 'Draft';
        $p            = $this->hr->p();

        // Migration check for signature columns
        $_apl_sig_cols = [
            'hr_signature'        => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `hr_signature` LONGTEXT DEFAULT NULL",
            'applicant_signature' => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `applicant_signature` LONGTEXT DEFAULT NULL",
            'hash'                => "ALTER TABLE `{$p}hr_appointment_letters` ADD COLUMN `hash` VARCHAR(40) DEFAULT NULL",
        ];
        foreach ($_apl_sig_cols as $_col => $_sql) {
            if (!$this->db->field_exists($_col, $p . 'hr_appointment_letters')) {
                $this->db->query($_sql);
            }
        }

        $hash = null;
        if ($id) {
            $existing_letter = $this->db->where('id', $id)->get($p . 'hr_appointment_letters')->row();
            if ($existing_letter && !empty($existing_letter->hash)) {
                $hash = $existing_letter->hash;
            }
        }
        if (empty($hash)) {
            $hash = md5(uniqid(rand(), true));
        }

        $data = [
            'applicant_id'      => $applicant_id,
            'template_id'       => $template_id,
            'appointment_date'  => $this->input->post('appointment_date', true) ?: null,
            'company'           => $this->input->post('company', true) ?: null,
            'introduction'      => $this->input->post('introduction') ?: null,
            'closing_statement' => $this->input->post('closing_statement') ?: null,
            'letter_content'    => '',
            'status'            => $status,
            'hr_signature'      => $this->input->post('hr_signature') ?: null,
            'hash'              => $hash,
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_appointment_letters', $data);
            set_alert('success', 'Appointment letter updated.');
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_appointment_letters', $data);
            $id  = $this->db->insert_id();
            $num = 'HR-APL-' . str_pad($id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $id)->update($p . 'hr_appointment_letters', ['letter_number' => $num]);
            set_alert('success', 'Appointment letter created.');
        }

        // Rebuild terms rows
        $this->db->where('letter_id', $id)->delete($p . 'hr_appointment_letter_terms');
        $titles = $this->input->post('term_titles') ?: [];
        $descs  = $this->input->post('term_descs')  ?: [];
        foreach ($titles as $i => $title) {
            $title = trim($title);
            if ($title === '') { continue; }
            $this->db->insert($p . 'hr_appointment_letter_terms', [
                'letter_id'   => $id,
                'title'       => $title,
                'description' => isset($descs[$i]) ? trim($descs[$i]) : '',
                'sort_order'  => $i,
            ]);
        }

        // If signed → mark applicant Hired + create employee
        if ($status === 'Signed') {
            $applicant = $this->db->where('id', $applicant_id)->get($p . 'hr_applicants')->row();
            if ($applicant) {
                $this->db->where('id', $applicant_id)->update($p . 'hr_applicants', ['stage' => 'Hired']);
                $this->_hire_applicant($applicant_id);
                $this->_send_applicant_stage_email($applicant_id, 'Hired');
            }
        }

        redirect(admin_url('xetuu_hr/recruitment/appointment_letters/edit/' . $id));
    }

    private function _save_interview_round()
    {
        $id   = (int)$this->input->post('id');
        $p    = $this->hr->p();
        $name = $this->input->post('name', true);
        $desc = $this->input->post('description', true);
        $skills_raw = $this->input->post('skills_text', true) ?? '';

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_interview_rounds', ['name' => $name, 'description' => $desc]);
        } else {
            $this->db->insert($p . 'hr_interview_rounds', ['name' => $name, 'description' => $desc]);
            $id = $this->db->insert_id();
            set_alert('success', _l('added_successfully'));
        }
        // Save skills (replace all)
        $this->db->where('round_id', $id)->delete($p . 'hr_interview_round_skills');
        $lines = array_filter(array_map('trim', explode("\n", $skills_raw)));
        foreach ($lines as $idx => $skill) {
            if ($skill === '') continue;
            $this->db->insert($p . 'hr_interview_round_skills', ['round_id' => $id, 'skill_name' => $skill, 'sort_order' => $idx]);
        }
        set_alert('success', _l('updated_successfully'));
        redirect(admin_url('xetuu_hr/setup/interview_round'));
    }

    private function _save_interview_feedback()
    {
        $id = (int)$this->input->post('id');
        $p  = $this->hr->p();

        $data = [
            'interview_id'   => (int)$this->input->post('interview_id'),
            'interviewer_id' => $this->input->post('interviewer_id') ? (int)$this->input->post('interviewer_id') : null,
            'result'         => $this->input->post('result', true) ?: 'To Be Discussed',
            'feedback'       => $this->input->post('feedback', true) ?: null,
            'status'         => $this->input->post('status', true) ?: 'Draft',
        ];

        if ($id) {
            $this->db->where('id', $id)->update($p . 'hr_interview_feedback', $data);
            set_alert('success', 'Interview feedback updated.');
        } else {
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_interview_feedback', $data);
            $id = $this->db->insert_id();
            $num = 'HR-INT-FEED-' . str_pad($id, 5, '0', STR_PAD_LEFT);
            $this->db->where('id', $id)->update($p . 'hr_interview_feedback', ['feedback_number' => $num]);
            set_alert('success', 'Interview feedback saved.');
        }

        // Save skill ratings
        $this->db->where('feedback_id', $id)->delete($p . 'hr_interview_feedback_skills');
        $skill_names  = (array)$this->input->post('skill_name');
        $skill_ratings = (array)$this->input->post('skill_rating');
        foreach ($skill_names as $idx => $sn) {
            if (trim($sn) === '') continue;
            $this->db->insert($p . 'hr_interview_feedback_skills', [
                'feedback_id' => $id,
                'skill_name'  => trim($sn),
                'rating'      => (int)($skill_ratings[$idx] ?? 0),
                'sort_order'  => $idx,
            ]);
        }

        redirect(admin_url('xetuu_hr/recruitment/interview_feedback/edit/' . $id));
    }

    public function get_interview_details_json($interview_id)
    {
        $p = $this->hr->p();
        $i = $this->db->where('id', (int)$interview_id)->get($p . 'hr_interviews')->row();
        if (!$i) { echo json_encode([]); exit; }

        $applicant = $this->db->where('id', $i->applicant_id)->get($p . 'hr_applicants')->row();
        $round = null; $skills = [];
        if ($i->interview_round_id) {
            $round = $this->db->where('id', $i->interview_round_id)->get($p . 'hr_interview_rounds')->row();
            $skills = $this->db->where('round_id', $i->interview_round_id)->order_by('sort_order')->get($p . 'hr_interview_round_skills')->result();
        }
        echo json_encode([
            'applicant_name' => $applicant ? $applicant->first_name . ' ' . $applicant->last_name : '',
            'round_id'       => $i->interview_round_id,
            'round_name'     => $round ? $round->name : '',
            'interviewer_id' => $i->interviewer_id,
            'skills'         => array_map(function($s) { return $s->skill_name; }, $skills),
        ]);
        exit;
    }

    public function get_appointment_template_json($template_id)
    {
        $p  = $this->hr->p();
        $t  = $this->db->where('id', (int)$template_id)->get($p . 'hr_appointment_letter_templates')->row();
        if (!$t) { echo json_encode([]); exit; }
        $terms = $this->db->where('template_id', (int)$template_id)->order_by('sort_order')->get($p . 'hr_appointment_letter_template_terms')->result();
        echo json_encode([
            'introduction'      => $t->introduction      ?? $t->content ?? '',
            'closing_statement' => $t->closing_statement ?? '',
            'terms'             => array_map(function($r) { return ['title' => $r->title, 'description' => $r->description]; }, $terms),
        ]);
        exit;
    }

    public function get_applicant_json($applicant_id)
    {
        $p  = $this->hr->p();
        $ap = $this->db->where('id', (int)$applicant_id)->get($p . 'hr_applicants')->row();
        if (!$ap) { echo json_encode([]); exit; }
        $opening = $ap->job_opening_id
            ? $this->db->where('id', $ap->job_opening_id)->get($p . 'hr_job_openings')->row()
            : null;
        echo json_encode([
            'name'         => $ap->first_name . ' ' . $ap->last_name,
            'email'        => $ap->email ?? '',
            'job_opening_id' => $ap->job_opening_id ?? '',
            'job_title'    => $opening ? $opening->title : '',
        ]);
        exit;
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    // ── Shift & Attendance ────────────────────────────────────────────────────

    public function attendance($sub = null, $action = null, $id = null)
    {
        $p   = db_prefix();
        $sub = $sub ?: 'dashboard';

        // ── Dashboard ─────────────────────────────────────────────────────────
        if ($sub === 'dashboard') {
            $today = date('Y-m-d');
            $data = [
                'title'         => 'Shift & Attendance',
                'xhr_active'    => 'attendance',
                'stat_present'  => $this->db->where('attendance_date', $today)->where('status', 'Present')->count_all_results($p . 'hr_daily_attendance'),
                'stat_late'     => $this->db->where('attendance_date', $today)->where('status', 'Late')->count_all_results($p . 'hr_daily_attendance'),
                'stat_absent'   => $this->db->where('attendance_date', $today)->where('status', 'Absent')->count_all_results($p . 'hr_daily_attendance'),
                'stat_on_leave' => $this->db->where('attendance_date', $today)->where('status', 'On Leave')->count_all_results($p . 'hr_daily_attendance'),
                'stat_ot_pending' => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_overtime_slips'),
                'stat_requests_pending' => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_attendance_requests')
                    + $this->db->where('status', 'Pending')->count_all_results($p . 'hr_shift_requests'),
                'recent_logs'   => $this->db->select('al.*, e.first_name, e.last_name')
                                       ->from($p . 'hr_attendance_logs al')
                                       ->join($p . 'hr_employees e', 'e.id = al.employee_id', 'left')
                                       ->order_by('al.log_datetime', 'DESC')->limit(8)
                                       ->get()->result(),
                'trend'         => $this->_att_trend_30days(),
                'branches'      => $this->hr->get_branches(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/dashboard', $data);
            return;
        }

        // ── Roster ────────────────────────────────────────────────────────────
        if ($sub === 'roster') {
            // AJAX: assign a shift slot
            if ($action === 'assign' && $this->input->post()) {
                $employee_id  = (int) $this->input->post('employee_id');
                $shift_id     = (int) $this->input->post('shift_type_id');
                $roster_date  = $this->input->post('roster_date', true);
                $existing = $this->db->where('employee_id', $employee_id)->where('roster_date', $roster_date)->get($p . 'hr_shift_roster')->row();
                if ($existing) {
                    $this->db->where('id', $existing->id)->update($p . 'hr_shift_roster', ['shift_type_id' => $shift_id, 'status' => 'Scheduled']);
                } else {
                    $this->db->insert($p . 'hr_shift_roster', ['employee_id' => $employee_id, 'branch_id' => 0, 'shift_type_id' => $shift_id, 'roster_date' => $roster_date, 'status' => 'Scheduled', 'date_created' => date('Y-m-d H:i:s')]);
                }
                echo json_encode(['success' => true]);
                exit;
            }
            // AJAX: remove a roster slot
            if ($action === 'remove' && $this->input->post()) {
                $this->db->where('employee_id', (int)$this->input->post('employee_id'))
                         ->where('roster_date', $this->input->post('roster_date', true))
                         ->delete($p . 'hr_shift_roster');
                echo json_encode(['success' => true]);
                exit;
            }
            $week_start = $action ?: date('Y-m-d', strtotime('monday this week'));
            $week_dates = [];
            for ($i = 0; $i < 7; $i++) {
                $week_dates[] = date('Y-m-d', strtotime($week_start . ' +' . $i . ' days'));
            }
            $roster = $this->db->select('r.*, st.name shift_name, st.color, st.start_time, st.end_time')
                               ->from($p . 'hr_shift_roster r')
                               ->join($p . 'hr_shift_types st', 'st.id = r.shift_type_id', 'left')
                               ->where('r.roster_date >=', $week_dates[0])
                               ->where('r.roster_date <=', $week_dates[6])
                               ->get()->result();
            $roster_map = [];
            foreach ($roster as $r) {
                $roster_map[$r->employee_id][$r->roster_date] = $r;
            }
            $data = [
                'title'       => 'Shift Roster',
                'xhr_active'  => 'attendance',
                'week_start'  => $week_start,
                'week_dates'  => $week_dates,
                'employees'   => $this->hr->get_employees(),
                'shift_types' => $this->db->where('active', 1)->get($p . 'hr_shift_types')->result(),
                'roster_map'  => $roster_map,
                'branches'    => $this->hr->get_branches(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/roster', $data);
            return;
        }

        // ── Attendance Log (raw check-in/out) ─────────────────────────────────
        if ($sub === 'log') {
            if ($action === 'delete' && $id) {
                $this->db->where('id', (int)$id)->delete($p . 'hr_attendance_logs');
                set_alert('success', 'Log deleted.');
                redirect(admin_url('xetuu_hr/attendance/log'));
                return;
            }
            if ($this->input->post()) {
                $log_id = (int)$this->input->post('log_id');
                $row = [
                    'employee_id'  => (int)$this->input->post('employee_id'),
                    'branch_id'    => (int)$this->input->post('branch_id'),
                    'log_datetime' => $this->input->post('log_date', true) . ' ' . $this->input->post('log_time', true),
                    'log_type'     => $this->input->post('log_type', true),
                    'method'       => 'Manual',
                    'notes'        => $this->input->post('notes', true),
                ];
                if ($log_id) {
                    $this->db->where('id', $log_id)->update($p . 'hr_attendance_logs', $row);
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_attendance_logs', $row);
                }
                set_alert('success', 'Attendance log saved.');
                redirect(admin_url('xetuu_hr/attendance/log'));
                return;
            }
            $edit_log = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_attendance_logs')->row() : null;
            $data = [
                'title'      => 'Attendance Log',
                'xhr_active' => 'attendance',
                'logs'       => $this->db->select('al.*, e.first_name, e.last_name, b.name branch_name')
                                         ->from($p . 'hr_attendance_logs al')
                                         ->join($p . 'hr_employees e', 'e.id = al.employee_id', 'left')
                                         ->join($p . 'hr_branches b', 'b.id = al.branch_id', 'left')
                                         ->order_by('al.log_datetime', 'DESC')->limit(200)
                                         ->get()->result(),
                'employees'  => $this->hr->get_employees(),
                'branches'   => $this->hr->get_branches(),
                'edit_log'   => $edit_log,
                'stat_today' => $this->db->where('DATE(log_datetime)', date('Y-m-d'))->count_all_results($p . 'hr_attendance_logs'),
                'stat_in'    => $this->db->where('DATE(log_datetime)', date('Y-m-d'))->where('log_type', 'IN')->count_all_results($p . 'hr_attendance_logs'),
                'stat_out'   => $this->db->where('DATE(log_datetime)', date('Y-m-d'))->where('log_type', 'OUT')->count_all_results($p . 'hr_attendance_logs'),
            ];
            $this->load->view('xetuu_hr/admin/attendance/attendance_log', $data);
            return;
        }

        // ── Daily Attendance ──────────────────────────────────────────────────
        if ($sub === 'daily') {
            $filter_date   = $this->input->get('date', true) ?: date('Y-m-d');
            $filter_branch = (int)($this->input->get('branch_id') ?: 0);
            $filter_status = $this->input->get('status', true) ?: '';
            $q = $this->db->select('da.*, e.first_name, e.last_name, e.employee_number, st.name shift_name, st.color')
                          ->from($p . 'hr_daily_attendance da')
                          ->join($p . 'hr_employees e', 'e.id = da.employee_id', 'left')
                          ->join($p . 'hr_shift_types st', 'st.id = da.shift_type_id', 'left')
                          ->where('da.attendance_date', $filter_date);
            if ($filter_branch) $q->where('da.branch_id', $filter_branch);
            if ($filter_status) $q->where('da.status', $filter_status);
            $data = [
                'title'         => 'Daily Attendance',
                'xhr_active'    => 'attendance',
                'records'       => $q->order_by('e.first_name')->get()->result(),
                'filter_date'   => $filter_date,
                'filter_branch' => $filter_branch,
                'filter_status' => $filter_status,
                'branches'      => $this->hr->get_branches(),
                'stat_present'  => $this->db->where('attendance_date', $filter_date)->where_in('status', ['Present','Late'])->count_all_results($p . 'hr_daily_attendance'),
                'stat_absent'   => $this->db->where('attendance_date', $filter_date)->where('status', 'Absent')->count_all_results($p . 'hr_daily_attendance'),
                'stat_leave'    => $this->db->where('attendance_date', $filter_date)->where('status', 'On Leave')->count_all_results($p . 'hr_daily_attendance'),
                'stat_total'    => $this->db->where('attendance_date', $filter_date)->count_all_results($p . 'hr_daily_attendance'),
            ];
            $this->load->view('xetuu_hr/admin/attendance/daily_attendance', $data);
            return;
        }

        // ── Bulk Attendance Tool ──────────────────────────────────────────────
        if ($sub === 'bulk_tool') {
            if ($this->input->post('bulk_save')) {
                $att_date  = $this->input->post('att_date', true);
                $shift_id  = (int)$this->input->post('shift_type_id');
                $branch_id = (int)$this->input->post('branch_id');
                $statuses  = $this->input->post('att_status');
                $check_ins = $this->input->post('check_in');
                $check_outs= $this->input->post('check_out');
                if (is_array($statuses)) {
                    foreach ($statuses as $emp_id => $status) {
                        $row = [
                            'employee_id'      => (int)$emp_id,
                            'branch_id'        => $branch_id,
                            'attendance_date'  => $att_date,
                            'shift_type_id'    => $shift_id ?: null,
                            'status'           => $status,
                            'check_in'         => !empty($check_ins[$emp_id]) ? $att_date . ' ' . $check_ins[$emp_id] : null,
                            'check_out'        => !empty($check_outs[$emp_id]) ? $att_date . ' ' . $check_outs[$emp_id] : null,
                            'source'           => 'Bulk Tool',
                            'date_created'     => date('Y-m-d H:i:s'),
                            'date_modified'    => date('Y-m-d H:i:s'),
                        ];
                        $existing = $this->db->where('employee_id', (int)$emp_id)->where('attendance_date', $att_date)->get($p . 'hr_daily_attendance')->row();
                        if ($existing) {
                            unset($row['date_created']);
                            $this->db->where('id', $existing->id)->update($p . 'hr_daily_attendance', $row);
                        } else {
                            $this->db->insert($p . 'hr_daily_attendance', $row);
                        }
                    }
                }
                set_alert('success', 'Attendance saved for ' . count($statuses) . ' employees.');
                redirect(admin_url('xetuu_hr/attendance/bulk_tool'));
                return;
            }
            $data = [
                'title'       => 'Bulk Attendance Tool',
                'xhr_active'  => 'attendance',
                'employees'   => $this->hr->get_employees(),
                'shift_types' => $this->db->where('active', 1)->get($p . 'hr_shift_types')->result(),
                'branches'    => $this->hr->get_branches(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/bulk_tool', $data);
            return;
        }

        // ── Attendance Requests ───────────────────────────────────────────────
        if ($sub === 'request') {
            if ($action === 'approve' && $id) {
                $req = $this->db->where('id', (int)$id)->get($p . 'hr_attendance_requests')->row();
                if ($req) {
                    $this->db->where('id', (int)$id)->update($p . 'hr_attendance_requests', ['status' => 'Approved', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                    if ($req->daily_attendance_id) {
                        $upd = [];
                        if ($req->requested_check_in)  $upd['check_in']  = $req->requested_check_in;
                        if ($req->requested_check_out) $upd['check_out'] = $req->requested_check_out;
                        if ($req->requested_status)    $upd['status']    = $req->requested_status;
                        if ($upd) $this->db->where('id', $req->daily_attendance_id)->update($p . 'hr_daily_attendance', $upd);
                    }
                    set_alert('success', 'Request approved and attendance updated.');
                }
                redirect(admin_url('xetuu_hr/attendance/request'));
                return;
            }
            if ($action === 'reject' && $id) {
                $this->db->where('id', (int)$id)->update($p . 'hr_attendance_requests', ['status' => 'Rejected', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                set_alert('warning', 'Request rejected.');
                redirect(admin_url('xetuu_hr/attendance/request'));
                return;
            }
            if ($this->input->post()) {
                $req_id = (int)$this->input->post('request_id');
                $row = [
                    'employee_id'         => (int)$this->input->post('employee_id'),
                    'attendance_date'     => $this->input->post('attendance_date', true),
                    'daily_attendance_id' => (int)$this->input->post('daily_attendance_id') ?: null,
                    'requested_check_in'  => $this->input->post('requested_check_in', true) ?: null,
                    'requested_check_out' => $this->input->post('requested_check_out', true) ?: null,
                    'requested_status'    => $this->input->post('requested_status', true),
                    'reason'              => $this->input->post('reason', true),
                    'status'              => 'Pending',
                ];
                if ($req_id) {
                    $this->db->where('id', $req_id)->update($p . 'hr_attendance_requests', $row);
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_attendance_requests', $row);
                }
                set_alert('success', 'Attendance request saved.');
                redirect(admin_url('xetuu_hr/attendance/request'));
                return;
            }
            $edit_req = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_attendance_requests')->row() : null;
            $data = [
                'title'      => 'Attendance Requests',
                'xhr_active' => 'attendance',
                'requests'   => $this->db->select('ar.*, e.first_name, e.last_name')
                                         ->from($p . 'hr_attendance_requests ar')
                                         ->join($p . 'hr_employees e', 'e.id = ar.employee_id', 'left')
                                         ->order_by('ar.date_created', 'DESC')->limit(100)->get()->result(),
                'employees'  => $this->hr->get_employees(),
                'edit_req'   => $edit_req,
                'stat_pending'  => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_attendance_requests'),
                'stat_approved' => $this->db->where('status', 'Approved')->count_all_results($p . 'hr_attendance_requests'),
                'stat_rejected' => $this->db->where('status', 'Rejected')->count_all_results($p . 'hr_attendance_requests'),
            ];
            $this->load->view('xetuu_hr/admin/attendance/attendance_request', $data);
            return;
        }

        // ── Excel Timesheet Upload ────────────────────────────────────────────
        if ($sub === 'excel_upload') {
            if ($this->input->post('do_import') && isset($_FILES['timesheet_file'])) {
                $this->_process_excel_upload();
                return;
            }
            $data = [
                'title'      => 'Excel Timesheet Upload',
                'xhr_active' => 'attendance',
                'imports'    => $this->db->order_by('imported_at', 'DESC')->limit(20)->get($p . 'hr_timesheet_imports')->result(),
                'branches'   => $this->hr->get_branches(),
                'employees'  => $this->hr->get_employees(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/excel_upload', $data);
            return;
        }
        // AJAX: get import row detail
        if ($sub === 'import_detail' && $id) {
            $rows = $this->db->where('import_id', (int)$id)->order_by('row_number')->get($p . 'hr_timesheet_import_rows')->result();
            echo json_encode($rows);
            exit;
        }

        // ── Overtime Slips ────────────────────────────────────────────────────
        if ($sub === 'overtime') {
            if ($action === 'delete' && $id) {
                $this->db->where('id', (int)$id)->delete($p . 'hr_overtime_slips');
                set_alert('success', 'Overtime slip deleted.');
                redirect(admin_url('xetuu_hr/attendance/overtime'));
                return;
            }
            if ($action === 'approve' && $id) {
                $this->db->where('id', (int)$id)->update($p . 'hr_overtime_slips', ['status' => 'Approved', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                set_alert('success', 'Overtime slip approved.');
                redirect(admin_url('xetuu_hr/attendance/overtime'));
                return;
            }
            if ($action === 'reject' && $id) {
                $this->db->where('id', (int)$id)->update($p . 'hr_overtime_slips', ['status' => 'Rejected', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                set_alert('warning', 'Overtime slip rejected.');
                redirect(admin_url('xetuu_hr/attendance/overtime'));
                return;
            }
            if ($this->input->post()) {
                $slip_id = (int)$this->input->post('slip_id');
                $ot_type = $this->db->where('id', (int)$this->input->post('overtime_type_id'))->get($p . 'hr_overtime_types')->row();
                $row = [
                    'employee_id'       => (int)$this->input->post('employee_id'),
                    'branch_id'         => (int)$this->input->post('branch_id'),
                    'overtime_date'     => $this->input->post('overtime_date', true),
                    'shift_type_id'     => (int)$this->input->post('shift_type_id') ?: null,
                    'overtime_type_id'  => (int)$this->input->post('overtime_type_id'),
                    'regular_hours'     => (float)$this->input->post('regular_hours'),
                    'overtime_hours'    => (float)$this->input->post('overtime_hours'),
                    'rate_multiplier'   => $ot_type ? $ot_type->multiplier : 1.50,
                    'compensation_mode' => $this->input->post('compensation_mode', true),
                    'toil_hours_credited' => $ot_type && $ot_type->toil_enabled ? round((float)$this->input->post('overtime_hours') * $ot_type->toil_multiplier, 2) : 0,
                    'status'            => 'Pending',
                    'notes'             => $this->input->post('notes', true),
                ];
                if ($slip_id) {
                    $this->db->where('id', $slip_id)->update($p . 'hr_overtime_slips', $row);
                    set_alert('success', 'Overtime slip updated.');
                    redirect(admin_url('xetuu_hr/attendance/overtime/edit/' . $slip_id));
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_overtime_slips', $row);
                    $new_id = $this->db->insert_id();
                    $slip_num = 'OT-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
                    $this->db->where('id', $new_id)->update($p . 'hr_overtime_slips', ['slip_number' => $slip_num]);
                    set_alert('success', 'Overtime slip created.');
                    redirect(admin_url('xetuu_hr/attendance/overtime/edit/' . $new_id));
                }
                return;
            }
            $edit_slip = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_overtime_slips')->row() : null;
            $data = [
                'title'        => 'Overtime Slips',
                'xhr_active'   => 'attendance',
                'slips'        => $this->db->select('os.*, e.first_name, e.last_name, ot.name ot_type_name, ot.multiplier, st.name shift_name')
                                           ->from($p . 'hr_overtime_slips os')
                                           ->join($p . 'hr_employees e', 'e.id = os.employee_id', 'left')
                                           ->join($p . 'hr_overtime_types ot', 'ot.id = os.overtime_type_id', 'left')
                                           ->join($p . 'hr_shift_types st', 'st.id = os.shift_type_id', 'left')
                                           ->order_by('os.overtime_date', 'DESC')->limit(100)->get()->result(),
                'employees'    => $this->hr->get_employees(),
                'ot_types'     => $this->db->where('active', 1)->get($p . 'hr_overtime_types')->result(),
                'shift_types'  => $this->db->where('active', 1)->get($p . 'hr_shift_types')->result(),
                'branches'     => $this->hr->get_branches(),
                'edit_slip'    => $edit_slip,
                'stat_total'   => $this->db->count_all($p . 'hr_overtime_slips'),
                'stat_pending' => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_overtime_slips'),
                'stat_approved'=> $this->db->where('status', 'Approved')->count_all_results($p . 'hr_overtime_slips'),
                'stat_paid'    => $this->db->where('status', 'Paid')->count_all_results($p . 'hr_overtime_slips'),
                'total_ot_hrs' => (float)($this->db->select_sum('overtime_hours')->get($p . 'hr_overtime_slips')->row()->overtime_hours ?? 0),
            ];
            $this->load->view('xetuu_hr/admin/attendance/overtime_slips', $data);
            return;
        }

        // ── Overtime Types ────────────────────────────────────────────────────
        if ($sub === 'overtime_type') {
            if ($action === 'delete' && $id) {
                $this->db->where('id', (int)$id)->delete($p . 'hr_overtime_types');
                set_alert('success', 'Overtime type deleted.');
                redirect(admin_url('xetuu_hr/attendance/overtime_type'));
                return;
            }
            if ($this->input->post()) {
                $ot_id = (int)$this->input->post('ot_id');
                $row = [
                    'name'                => $this->input->post('name', true),
                    'applicable_on'       => implode(',', (array)$this->input->post('applicable_on')),
                    'multiplier'          => (float)$this->input->post('multiplier'),
                    'min_threshold_mins'  => (int)$this->input->post('min_threshold_mins'),
                    'max_hours_per_day'   => $this->input->post('max_hours_per_day') !== '' ? (float)$this->input->post('max_hours_per_day') : null,
                    'max_hours_per_week'  => $this->input->post('max_hours_per_week') !== '' ? (float)$this->input->post('max_hours_per_week') : null,
                    'max_hours_per_month' => $this->input->post('max_hours_per_month') !== '' ? (float)$this->input->post('max_hours_per_month') : null,
                    'toil_enabled'        => $this->input->post('toil_enabled') ? 1 : 0,
                    'toil_multiplier'     => (float)$this->input->post('toil_multiplier') ?: 1.00,
                    'description'         => $this->input->post('description', true),
                    'active'              => 1,
                ];
                if ($ot_id) {
                    $this->db->where('id', $ot_id)->update($p . 'hr_overtime_types', $row);
                    set_alert('success', 'Overtime type updated.');
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_overtime_types', $row);
                    set_alert('success', 'Overtime type added.');
                }
                redirect(admin_url('xetuu_hr/attendance/overtime_type'));
                return;
            }
            $edit_ot = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_overtime_types')->row() : null;
            $data = [
                'title'      => 'Overtime Types',
                'xhr_active' => 'attendance',
                'ot_types'   => $this->db->order_by('name')->get($p . 'hr_overtime_types')->result(),
                'edit_ot'    => $edit_ot,
                'show_form'  => ($action === 'add' || ($action === 'edit' && $id)),
            ];
            $this->load->view('xetuu_hr/admin/attendance/overtime_type', $data);
            return;
        }

        // ── Shift Requests ────────────────────────────────────────────────────
        if ($sub === 'shift_request') {
            if ($action === 'approve' && $id) {
                $req = $this->db->where('id', (int)$id)->get($p . 'hr_shift_requests')->row();
                if ($req) {
                    $this->db->where('id', (int)$id)->update($p . 'hr_shift_requests', ['status' => 'Approved', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                    if ($req->to_shift_id) {
                        $dates = [$req->request_date];
                        if ($req->to_date && $req->to_date > $req->request_date) {
                            $d = new DateTime($req->request_date);
                            $end = new DateTime($req->to_date);
                            while ($d <= $end) { $dates[] = $d->format('Y-m-d'); $d->modify('+1 day'); }
                        }
                        foreach (array_unique($dates) as $dt) {
                            $ex = $this->db->where('employee_id', $req->employee_id)->where('roster_date', $dt)->get($p . 'hr_shift_roster')->row();
                            if ($ex) {
                                $this->db->where('id', $ex->id)->update($p . 'hr_shift_roster', ['shift_type_id' => $req->to_shift_id, 'status' => 'Swapped']);
                            } else {
                                $this->db->insert($p . 'hr_shift_roster', ['employee_id' => $req->employee_id, 'branch_id' => 0, 'shift_type_id' => $req->to_shift_id, 'roster_date' => $dt, 'status' => 'Swapped', 'date_created' => date('Y-m-d H:i:s')]);
                            }
                        }
                    }
                    set_alert('success', 'Shift request approved and roster updated.');
                }
                redirect(admin_url('xetuu_hr/attendance/shift_request'));
                return;
            }
            if ($action === 'reject' && $id) {
                $this->db->where('id', (int)$id)->update($p . 'hr_shift_requests', ['status' => 'Rejected', 'approved_by' => get_staff_user_id(), 'approved_on' => date('Y-m-d H:i:s')]);
                set_alert('warning', 'Shift request rejected.');
                redirect(admin_url('xetuu_hr/attendance/shift_request'));
                return;
            }
            if ($this->input->post()) {
                $req_id = (int)$this->input->post('req_id');
                $row = [
                    'employee_id'      => (int)$this->input->post('employee_id'),
                    'request_type'     => $this->input->post('request_type', true),
                    'from_shift_id'    => (int)$this->input->post('from_shift_id') ?: null,
                    'to_shift_id'      => (int)$this->input->post('to_shift_id') ?: null,
                    'request_date'     => $this->input->post('request_date', true),
                    'to_date'          => $this->input->post('to_date', true) ?: null,
                    'swap_with_emp_id' => (int)$this->input->post('swap_with_emp_id') ?: null,
                    'reason'           => $this->input->post('reason', true),
                    'status'           => 'Pending',
                ];
                if ($req_id) {
                    $this->db->where('id', $req_id)->update($p . 'hr_shift_requests', $row);
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_shift_requests', $row);
                }
                set_alert('success', 'Shift request saved.');
                redirect(admin_url('xetuu_hr/attendance/shift_request'));
                return;
            }
            $edit_req = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_shift_requests')->row() : null;
            $data = [
                'title'        => 'Shift Requests',
                'xhr_active'   => 'attendance',
                'requests'     => $this->db->select('sr.*, e.first_name, e.last_name, fs.name from_shift, ts.name to_shift')
                                           ->from($p . 'hr_shift_requests sr')
                                           ->join($p . 'hr_employees e', 'e.id = sr.employee_id', 'left')
                                           ->join($p . 'hr_shift_types fs', 'fs.id = sr.from_shift_id', 'left')
                                           ->join($p . 'hr_shift_types ts', 'ts.id = sr.to_shift_id', 'left')
                                           ->order_by('sr.date_created', 'DESC')->limit(100)->get()->result(),
                'employees'    => $this->hr->get_employees(),
                'shift_types'  => $this->db->where('active', 1)->get($p . 'hr_shift_types')->result(),
                'edit_req'     => $edit_req,
                'stat_pending' => $this->db->where('status', 'Pending')->count_all_results($p . 'hr_shift_requests'),
                'stat_approved'=> $this->db->where('status', 'Approved')->count_all_results($p . 'hr_shift_requests'),
            ];
            $this->load->view('xetuu_hr/admin/attendance/shift_request', $data);
            return;
        }

        // ── Setup: Shift Types ────────────────────────────────────────────────
        if ($sub === 'shift_types') {
            if ($action === 'delete' && $id) {
                $this->db->where('id', (int)$id)->update($p . 'hr_shift_types', ['active' => 0]);
                set_alert('success', 'Shift type deactivated.');
                redirect(admin_url('xetuu_hr/attendance/shift_types'));
                return;
            }
            if ($this->input->post()) {
                $st_id = (int)$this->input->post('st_id');
                $row = [
                    'name'               => $this->input->post('name', true),
                    'code'               => strtoupper($this->input->post('code', true)),
                    'start_time'         => $this->input->post('start_time', true),
                    'end_time'           => $this->input->post('end_time', true),
                    'working_hours'      => (float)$this->input->post('working_hours'),
                    'grace_in_mins'      => (int)$this->input->post('grace_in_mins'),
                    'grace_out_mins'     => (int)$this->input->post('grace_out_mins'),
                    'min_hours_half_day' => (float)$this->input->post('min_hours_half_day'),
                    'min_hours_full_day' => (float)$this->input->post('min_hours_full_day'),
                    'is_night_shift'     => $this->input->post('is_night_shift') ? 1 : 0,
                    'night_start'        => $this->input->post('night_start', true) ?: null,
                    'night_end'          => $this->input->post('night_end', true) ?: null,
                    'night_allowance'    => (float)$this->input->post('night_allowance'),
                    'color'              => $this->input->post('color', true) ?: '#2563eb',
                    'branch_id'          => (int)$this->input->post('branch_id') ?: null,
                    'description'        => $this->input->post('description', true),
                    'active'             => 1,
                ];
                if ($st_id) {
                    $this->db->where('id', $st_id)->update($p . 'hr_shift_types', $row);
                    set_alert('success', 'Shift type updated.');
                    redirect(admin_url('xetuu_hr/attendance/shift_types/edit/' . $st_id));
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_shift_types', $row);
                    set_alert('success', 'Shift type created.');
                    redirect(admin_url('xetuu_hr/attendance/shift_types/edit/' . $this->db->insert_id()));
                }
                return;
            }
            $edit_st = ($action === 'edit' && $id) ? $this->db->where('id', (int)$id)->get($p . 'hr_shift_types')->row() : null;
            $data = [
                'title'       => 'Shift Types',
                'xhr_active'  => 'attendance',
                'shift_types' => $this->db->order_by('name')->get($p . 'hr_shift_types')->result(),
                'branches'    => $this->hr->get_branches(),
                'edit_st'     => $edit_st,
                'show_form'   => ($action === 'add' || ($action === 'edit' && $id)),
            ];
            $this->load->view('xetuu_hr/admin/attendance/shift_types', $data);
            return;
        }

        // ── Setup: Shift Schedules ────────────────────────────────────────────
        if ($sub === 'shift_schedules') {
            if ($this->input->post()) {
                $sch_id = (int)$this->input->post('sch_id');
                $row = [
                    'name'           => $this->input->post('name', true),
                    'type'           => $this->input->post('type', true),
                    'rotation_weeks' => (int)$this->input->post('rotation_weeks') ?: 1,
                    'description'    => $this->input->post('description', true),
                    'branch_id'      => (int)$this->input->post('branch_id') ?: null,
                    'active'         => 1,
                ];
                if ($sch_id) {
                    $this->db->where('id', $sch_id)->update($p . 'hr_shift_schedules', $row);
                } else {
                    $row['date_created'] = date('Y-m-d H:i:s');
                    $this->db->insert($p . 'hr_shift_schedules', $row);
                }
                set_alert('success', 'Schedule saved.');
                redirect(admin_url('xetuu_hr/attendance/shift_schedules'));
                return;
            }
            $data = [
                'title'     => 'Shift Schedules',
                'xhr_active'=> 'attendance',
                'schedules' => $this->db->order_by('name')->get($p . 'hr_shift_schedules')->result(),
                'branches'  => $this->hr->get_branches(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/shift_schedules', $data);
            return;
        }

        // ── Setup: Attendance Settings ────────────────────────────────────────
        if ($sub === 'settings') {
            if ($this->input->post()) {
                $keys = [
                    'att_working_days', 'att_working_hours', 'att_grace_in_mins', 'att_grace_out_mins',
                    'att_auto_compute', 'att_absent_alert', 'att_absent_alert_hour',
                    'att_ot_weekday_mult', 'att_ot_weekend_mult', 'att_ot_holiday_mult', 'att_ot_min_threshold_mins', 'att_toil_enabled',
                ];
                foreach ($keys as $k) {
                    $this->hr->save_setting($k, $this->input->post($k, true) ?? '');
                }
                set_alert('success', 'Attendance settings saved.');
                redirect(admin_url('xetuu_hr/attendance/settings'));
                return;
            }
            $data = [
                'title'    => 'Attendance Settings',
                'xhr_active'=> 'attendance',
                'settings' => $this->hr->get_all_settings(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/settings', $data);
            return;
        }

        // ── Reports ───────────────────────────────────────────────────────────
        if ($sub === 'monthly_sheet') {
            $month  = (int)($this->input->get('month') ?: date('m'));
            $year   = (int)($this->input->get('year')  ?: date('Y'));
            $branch = (int)($this->input->get('branch_id') ?: 0);
            $first  = sprintf('%04d-%02d-01', $year, $month);
            $last   = date('Y-m-t', strtotime($first));
            $days   = (int)date('t', strtotime($first));
            $q = $this->db->select('da.*, e.first_name, e.last_name, e.employee_number')
                          ->from($p . 'hr_daily_attendance da')
                          ->join($p . 'hr_employees e', 'e.id = da.employee_id', 'left')
                          ->where('da.attendance_date >=', $first)->where('da.attendance_date <=', $last);
            if ($branch) $q->where('da.branch_id', $branch);
            $records = $q->order_by('e.first_name')->get()->result();
            $sheet = [];
            foreach ($records as $r) {
                $d = (int)date('d', strtotime($r->attendance_date));
                $sheet[$r->employee_id]['name'] = $r->first_name . ' ' . $r->last_name;
                $sheet[$r->employee_id]['number'] = $r->employee_number;
                $sheet[$r->employee_id]['days'][$d] = $r->status;
            }
            $data = [
                'title'    => 'Monthly Attendance Sheet',
                'xhr_active'=> 'attendance',
                'month'    => $month, 'year' => $year, 'days' => $days,
                'sheet'    => $sheet,
                'branches' => $this->hr->get_branches(),
                'filter_branch' => $branch,
            ];
            $this->load->view('xetuu_hr/admin/attendance/monthly_sheet', $data);
            return;
        }

        if ($sub === 'hours_report') {
            $month  = (int)($this->input->get('month') ?: date('m'));
            $year   = (int)($this->input->get('year')  ?: date('Y'));
            $first  = sprintf('%04d-%02d-01', $year, $month);
            $last   = date('Y-m-t', strtotime($first));
            $records = $this->db->select('e.first_name, e.last_name, e.employee_number, SUM(da.working_hours) total_hours, SUM(da.overtime_hours) total_ot, COUNT(da.id) days_present, SUM(IF(da.status="Late",1,0)) late_count')
                                ->from($p . 'hr_daily_attendance da')
                                ->join($p . 'hr_employees e', 'e.id = da.employee_id', 'left')
                                ->where('da.attendance_date >=', $first)->where('da.attendance_date <=', $last)
                                ->group_by('da.employee_id')->order_by('e.first_name')->get()->result();
            $data = [
                'title'    => 'Hours Utilization Report',
                'xhr_active'=> 'attendance',
                'records'  => $records,
                'month'    => $month, 'year' => $year,
                'branches' => $this->hr->get_branches(),
            ];
            $this->load->view('xetuu_hr/admin/attendance/hours_report', $data);
            return;
        }

        // Default fallback
        redirect(admin_url('xetuu_hr/attendance'));
    }

    private function _att_trend_30days()
    {
        $p = db_prefix();
        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $present = $this->db->where('attendance_date', $date)->where_in('status', ['Present', 'Late'])->count_all_results($p . 'hr_daily_attendance');
            $absent  = $this->db->where('attendance_date', $date)->where('status', 'Absent')->count_all_results($p . 'hr_daily_attendance');
            $result[] = ['date' => $date, 'present' => $present, 'absent' => $absent];
        }
        return $result;
    }

    private function _process_excel_upload()
    {
        $p = db_prefix();
        $branch_id   = (int)$this->input->post('branch_id');
        $pay_month   = (int)$this->input->post('pay_month');
        $pay_year    = (int)$this->input->post('pay_year');
        $col_emp     = $this->input->post('col_employee', true) ?: 'A';
        $col_date    = $this->input->post('col_date', true) ?: 'B';
        $col_in      = $this->input->post('col_in', true) ?: 'C';
        $col_out     = $this->input->post('col_out', true) ?: 'D';

        $upload_dir  = FCPATH . 'uploads/hr_timesheets/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);

        $config = ['upload_path' => $upload_dir, 'allowed_types' => 'xlsx|xls|csv', 'max_size' => 5120];
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('timesheet_file')) {
            set_alert('danger', 'Upload failed: ' . $this->upload->display_errors('', ''));
            redirect(admin_url('xetuu_hr/attendance/excel_upload'));
            return;
        }
        $file_info = $this->upload->data();
        $filepath  = $upload_dir . $file_info['file_name'];

        // Log the import
        $import_data = [
            'filename'         => $file_info['file_name'],
            'original_name'    => $file_info['orig_name'],
            'branch_id'        => $branch_id,
            'pay_period_month' => $pay_month,
            'pay_period_year'  => $pay_year,
            'uploaded_by'      => get_staff_user_id(),
            'imported_at'      => date('Y-m-d H:i:s'),
            'status'           => 'Processing',
            'column_mapping'   => json_encode(['employee' => $col_emp, 'date' => $col_date, 'in' => $col_in, 'out' => $col_out]),
        ];
        $this->db->insert($p . 'hr_timesheet_imports', $import_data);
        $import_id = $this->db->insert_id();
        $import_num = 'TS-IMP-' . str_pad($import_id, 5, '0', STR_PAD_LEFT);
        $this->db->where('id', $import_id)->update($p . 'hr_timesheet_imports', ['import_number' => $import_num]);

        // Parse CSV (handles .csv; xlsx requires PhpSpreadsheet which may not be available)
        $success = $error = $warning = 0;
        $row_num = 0;
        if (($h = fopen($filepath, 'r')) !== false) {
            $headers = fgetcsv($h); // skip header row
            while (($row = fgetcsv($h)) !== false) {
                $row_num++;
                if (empty(array_filter($row))) continue;
                $raw_emp   = $row[0] ?? '';
                $raw_date  = $row[1] ?? '';
                $raw_in    = $row[2] ?? '';
                $raw_out   = $row[3] ?? '';
                $raw_hours = $row[4] ?? null;
                $raw_ot    = $row[5] ?? null;

                $employee_id = null;
                // Match by employee number or name
                $emp = $this->db->where('employee_number', trim($raw_emp))->or_where("CONCAT(first_name,' ',last_name)", trim($raw_emp))->get($p . 'hr_employees')->row();
                if ($emp) $employee_id = $emp->id;

                $att_date = $raw_date ? date('Y-m-d', strtotime($raw_date)) : null;
                $check_in  = $raw_in  ? date('H:i:s', strtotime($raw_in))  : null;
                $check_out = $raw_out ? date('H:i:s', strtotime($raw_out)) : null;

                $status = 'Success';
                $err_msg = null;
                if (!$employee_id) { $status = 'Warning'; $err_msg = 'Employee not found: ' . $raw_emp; $warning++; }
                elseif (!$att_date) { $status = 'Error'; $err_msg = 'Invalid date: ' . $raw_date; $error++; }
                else {
                    // Insert attendance log IN
                    if ($check_in) {
                        $this->db->insert($p . 'hr_attendance_logs', ['employee_id' => $employee_id, 'branch_id' => $branch_id, 'log_datetime' => $att_date . ' ' . $check_in, 'log_type' => 'IN', 'method' => 'Excel Import', 'import_id' => $import_id, 'date_created' => date('Y-m-d H:i:s')]);
                    }
                    if ($check_out) {
                        $this->db->insert($p . 'hr_attendance_logs', ['employee_id' => $employee_id, 'branch_id' => $branch_id, 'log_datetime' => $att_date . ' ' . $check_out, 'log_type' => 'OUT', 'method' => 'Excel Import', 'import_id' => $import_id, 'date_created' => date('Y-m-d H:i:s')]);
                    }
                    // Upsert daily attendance
                    $hours = $raw_hours ?: ($check_in && $check_out ? round((strtotime($att_date . ' ' . $check_out) - strtotime($att_date . ' ' . $check_in)) / 3600, 2) : 0);
                    $da_row = ['employee_id' => $employee_id, 'branch_id' => $branch_id, 'attendance_date' => $att_date, 'check_in' => $check_in ? $att_date . ' ' . $check_in : null, 'check_out' => $check_out ? $att_date . ' ' . $check_out : null, 'working_hours' => $hours, 'overtime_hours' => $raw_ot ?: 0, 'status' => $hours > 0 ? 'Present' : 'Absent', 'source' => 'Excel Import', 'date_modified' => date('Y-m-d H:i:s')];
                    $ex = $this->db->where('employee_id', $employee_id)->where('attendance_date', $att_date)->get($p . 'hr_daily_attendance')->row();
                    if ($ex) { $this->db->where('id', $ex->id)->update($p . 'hr_daily_attendance', $da_row); }
                    else { $da_row['date_created'] = date('Y-m-d H:i:s'); $this->db->insert($p . 'hr_daily_attendance', $da_row); }
                    $success++;
                }
                $this->db->insert($p . 'hr_timesheet_import_rows', ['import_id' => $import_id, 'row_number' => $row_num, 'employee_id' => $employee_id, 'raw_employee' => $raw_emp, 'attendance_date' => $att_date, 'check_in' => $check_in, 'check_out' => $check_out, 'hours_worked' => $raw_hours ?: null, 'ot_hours' => $raw_ot ?: null, 'status' => $status, 'error_message' => $err_msg]);
            }
            fclose($h);
        }
        $this->db->where('id', $import_id)->update($p . 'hr_timesheet_imports', ['total_rows' => $row_num, 'success_rows' => $success, 'warning_rows' => $warning, 'error_rows' => $error, 'status' => 'Completed']);
        set_alert('success', "Import complete: {$success} success, {$warning} warnings, {$error} errors.");
        redirect(admin_url('xetuu_hr/attendance/excel_upload'));
    }

    // ── Performance ───────────────────────────────────────────────────────────

    public function performance($sub = null, $action = null, $id = null)
    {
        require_once APPPATH . '../modules/xetuu_hr/controllers/handlers/Performance_handler.php';
        (new Performance_handler($this))->handle($sub, $action, $id);
    }

    // ── Expenses ──────────────────────────────────────────────────────────────

    public function expenses($sub = null)
    {
        $sub = $sub ?: 'dashboard';
        $data = ['title' => _l('xetuu_hr_expenses')];

        switch ($sub) {
            case 'claims':
                $data['claims'] = $this->hr->get_expense_claims();
                $this->load->view('xetuu_hr/admin/expenses/claims', $data);
                break;
            default:
                $this->load->view('xetuu_hr/admin/expenses/dashboard', $data);
        }
    }

    // ── Tenure ────────────────────────────────────────────────────────────────

    public function tenure($sub = null)
    {
        $sub = $sub ?: 'dashboard';
        $data = ['title' => _l('xetuu_hr_tenure')];

        switch ($sub) {
            case 'onboarding':
                $data['onboardings'] = $this->hr->get_onboarding_list();
                $this->load->view('xetuu_hr/admin/tenure/onboarding', $data);
                break;
            case 'separation':
                $data['exits'] = $this->hr->get_exits();
                $this->load->view('xetuu_hr/admin/tenure/separation', $data);
                break;
            case 'grievances':
                $data['grievances'] = $this->hr->get_grievances();
                $this->load->view('xetuu_hr/admin/tenure/grievances', $data);
                break;
            default:
                $this->load->view('xetuu_hr/admin/tenure/dashboard', $data);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYROLL SUB-MODULE  — delegated to Payroll_handler
    // ══════════════════════════════════════════════════════════════════════════

    public function payroll($sub = null, $action = null, $id = null)
    {
        require_once APPPATH . '../modules/xetuu_hr/controllers/handlers/Payroll_handler.php';
        (new Payroll_handler($this))->handle($sub, $action, $id);
    }

    // ── Leave Management ──────────────────────────────────────────────────────

    public function leave($sub = null, $action = null, $id = null)
    {
        require_once APPPATH . '../modules/xetuu_hr/controllers/handlers/Leave_handler.php';
        (new Leave_handler($this))->handle($sub, $action, $id);
    }

}
