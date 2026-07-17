<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/Xr_handler.php';

/**
 * Leave_handler
 *
 * Routes all /xetuu_hr/leave/* URLs.
 * Loaded by Xetuu_hr::leave() — keeps the main controller thin.
 */
class Leave_handler extends Xr_handler
{
    /** @var Xr_leave_model */
    private $leave;

    /** @var string — current sub-section, stored for _base_data() */
    private $current_sub = '';

    public function __construct($ci)
    {
        parent::__construct($ci);
        $ci->load->model('xetuu_hr/Xr_leave_model', 'leave_mdl');
        $this->leave = $ci->leave_mdl;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Main dispatcher
    // ══════════════════════════════════════════════════════════════════════════

    public function handle($sub, $action, $id)
    {
        $this->current_sub = (string) $sub;

        switch ($sub) {
            case '':
            case null:
                $this->_dashboard();
                break;
            case 'my_requests':
                $this->_my_requests($action, $id);
                break;
            case 'apply':
                $this->_apply($action, $id);
                break;
            case 'approvals':
                $this->_approvals($action, $id);
                break;
            case 'hr_approvals':
                $this->_hr_approvals($action, $id);
                break;
            case 'allocations':
                $this->_allocations($action, $id);
                break;
            case 'encashment':
                $this->_encashment($action, $id);
                break;
            case 'toil':
                $this->_toil($action, $id);
                break;
            case 'reports':
                $this->_reports($action, $id);
                break;
            case 'config':
                $this->_config($action, $id);
                break;
            case 'calc_days':
                $this->_calc_days();
                break;
            case 'import_holidays':
                $this->_import_holidays();
                break;
            default:
                $this->redirect($this->base() . '/leave');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Base view data — merged into every view call
    // ══════════════════════════════════════════════════════════════════════════

    private function _base_data()
    {
        return [
            'xhr_active'       => 'leave',
            'leave_active_sub' => $this->current_sub,
            'leave_types'      => $this->leave->get_leave_types(true),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Resolve the currently logged-in staff member's employee record
    // ══════════════════════════════════════════════════════════════════════════

    private function _current_employee()
    {
        $staff = get_staff(get_staff_user_id());
        if (!$staff) {
            return null;
        }
        $p   = $this->p();
        $row = $this->CI->db
            ->select('*')
            ->from($p . 'hr_employees')
            ->where('email', $staff->email)
            ->limit(1)
            ->get()
            ->row();
        return $row;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    private function _dashboard()
    {
        $employee  = $this->_current_employee();
        $emp_id    = $employee ? (int) $employee->id : 0;
        $is_admin  = is_admin();

        // Pending approvals — always compute for admins / managers
        $pending_approvals = 0;
        if ($is_admin) {
            $pending_approvals = count($this->leave->get_pending_manager_approvals(0, true))
                               + count($this->leave->get_pending_hr_approvals());
        } elseif ($emp_id) {
            $pending_approvals = count($this->leave->get_pending_manager_approvals($emp_id));
            $pending_approvals += count($this->leave->get_pending_hr_approvals());
        }

        // Team on leave today (org-wide)
        $today     = date('Y-m-d');
        $team_reqs = $this->leave->get_leave_requests([
            'status'    => 'approved',
            'date_from' => $today,
            'date_to'   => $today,
        ], 100);

        // Recent requests: admins see last 10 org-wide; employees see their own last 5
        $recent_requests = $is_admin
            ? $this->leave->get_leave_requests([], 10)
            : ($emp_id ? $this->leave->get_leave_requests(['employee_id' => $emp_id], 5) : []);

        // Balance summary for personal leave cards
        $balance_summary = $emp_id
            ? $this->leave->get_employee_balance_summary($emp_id)
            : [];

        // Org-wide quick stats for admins
        $counts = $this->leave->get_leave_request_counts();

        $this->view('xetuu_hr/admin/leave/dashboard', array_merge($this->_base_data(), [
            'title'             => _l('leave_dashboard'),
            'employee'          => $employee,
            'balance_summary'   => $balance_summary,
            'pending_approvals' => $pending_approvals,
            'team_on_leave'     => $team_reqs,
            'recent_requests'   => $recent_requests,
            'counts'            => $counts,
            'is_admin'          => $is_admin,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MY REQUESTS
    // ══════════════════════════════════════════════════════════════════════════

    private function _my_requests($action, $id)
    {
        $employee = $this->_current_employee();
        $emp_id   = $employee ? (int) $employee->id : 0;

        if ($this->CI->input->post()) {
            $this->_save_request($emp_id);
            return;
        }

        // Cancel own request
        if ($action === 'cancel' && $id) {
            $req = $this->leave->get_leave_request((int) $id);
            if ($req && (int) $req->employee_id === $emp_id) {
                $this->leave->save_leave_request(['status' => 'cancel_requested'], (int) $id);
                $this->set_alert('success', _l('leave_cancel_requested'));
            }
            $this->redirect($this->base() . '/leave/my_requests');
            return;
        }

        $is_super = is_admin();
        $filters  = [];
        // Admins see all org requests; regular employees see only their own
        if (!$is_super && $emp_id) {
            $filters['employee_id'] = $emp_id;
        }
        if ($this->get('status'))      { $filters['status']      = $this->get('status'); }
        if ($this->get('year'))        { $filters['year']        = (int) $this->get('year'); }
        if ($this->get('employee_id')) { $filters['employee_id'] = (int) $this->get('employee_id'); }

        $requests = $this->leave->get_leave_requests($filters, 100);

        // Employee list for admin filter dropdown
        $emp_list = [];
        if ($is_super) {
            $emp_list = $this->CI->db
                ->select("id, CONCAT(first_name,' ',last_name) AS full_name")
                ->from(db_prefix() . 'hr_employees')
                ->where('active', 1)
                ->order_by('first_name', 'ASC')
                ->get()->result();
        }

        $this->view('xetuu_hr/admin/leave/my_requests', array_merge($this->_base_data(), [
            'title'     => $is_super ? 'All Leave Requests' : _l('my_leave_requests'),
            'employee'  => $employee,
            'requests'  => $requests,
            'filters'   => $filters,
            'is_super'  => $is_super,
            'emp_list'  => $emp_list,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // APPLY FOR LEAVE
    // ══════════════════════════════════════════════════════════════════════════

    private function _apply($action, $id)
    {
        $employee = $this->_current_employee();
        $emp_id   = $employee ? (int) $employee->id : 0;

        if ($this->CI->input->post()) {
            $this->_save_request($emp_id);
            return;
        }

        // AJAX: balance for a specific employee (on-behalf dropdown)
        if ($action === 'behalf_balance' && $id && (is_admin() || has_permission('xetuu_hr', '', 'edit'))) {
            header('Content-Type: application/json');
            $target_id = (int) $id;
            $p = db_prefix();
            $emp_row = $this->CI->db->where('id', $target_id)->get($p . 'hr_employees')->row();
            $balance  = $this->leave->get_employee_balance_summary($target_id);
            // Enrich with leave type meta
            $types = $this->leave->get_leave_types(true);
            $type_map = [];
            foreach ($types as $t) { $type_map[$t->id] = $t; }
            $summary = [];
            foreach ($balance as $b) {
                $t = $type_map[$b->leave_type_id] ?? null;
                $summary[] = [
                    'leave_type_id'   => $b->leave_type_id,
                    'leave_type_name' => $b->leave_type_name ?? ($t->name ?? ''),
                    'remaining'       => max(0, ((float)($b->total_days ?? 0) + (float)($b->carried_forward_days ?? 0)) - (float)($b->used_days ?? 0)),
                    'unit'            => $t->unit ?? 'days',
                    'color'           => $t->color ?? '#2563eb',
                    'allow_half_day'  => (int)($t->allow_half_day ?? 1),
                ];
            }
            echo json_encode([
                'success'         => true,
                'employee_name'   => $emp_row ? trim($emp_row->first_name . ' ' . $emp_row->last_name) : 'Unknown',
                'balance_summary' => $summary,
            ]);
            exit;
        }

        // Edit existing draft
        $editing = ($id && is_numeric($id)) ? $this->leave->get_leave_request((int) $id) : null;

        // "On behalf" — admins/HR see all employees to apply for
        $on_behalf_employees = [];
        if (is_admin() || has_permission('xetuu_hr', '', 'edit')) {
            $p = db_prefix();
            $on_behalf_employees = $this->CI->db
                ->select("id, CONCAT(first_name,' ',last_name) AS full_name, department_id")
                ->from($p . 'hr_employees')
                ->where('active', 1)
                ->order_by('first_name', 'ASC')
                ->get()->result();
        }

        // All employees for handover dropdown (everyone can pick a colleague)
        $p = db_prefix();
        $all_emp = $this->CI->db
            ->select("id, first_name, last_name")
            ->from($p . 'hr_employees')
            ->where('active', 1)
            ->order_by('first_name', 'ASC')
            ->get()->result();

        $this->view('xetuu_hr/admin/leave/apply', array_merge($this->_base_data(), [
            'title'               => _l('apply_for_leave'),
            'employee'            => $employee,
            'editing'             => $editing,
            'balance_summary'     => $emp_id ? $this->leave->get_employee_balance_summary($emp_id) : [],
            'employees'           => $all_emp,
            'on_behalf_employees' => $on_behalf_employees,
            'can_apply_behalf'    => !empty($on_behalf_employees),
        ]));
    }

    /**
     * Common POST handler for new / edited leave requests.
     */
    private function _save_request($emp_id)
    {
        $post = $this->post(null);

        // Admin/HR applying on behalf of another employee
        $on_behalf_id = !empty($post['on_behalf_of_id']) ? (int) $post['on_behalf_of_id'] : 0;
        $target_emp_id = ($on_behalf_id && (is_admin() || has_permission('xetuu_hr', '', 'edit')))
            ? $on_behalf_id
            : $emp_id;

        $leave_type_id = (int) ($post['leave_type_id'] ?? 0);
        $date_from     = $post['date_from'] ?? '';
        $date_to       = $post['date_to']   ?? '';
        $req_id        = !empty($post['request_id']) ? (int) $post['request_id'] : null;

        // Calculate working days
        $lt         = $this->leave->get_leave_type($leave_type_id);
        $total_days = $this->leave->calc_working_days(
            $date_from,
            $date_to,
            (int) ($post['company_id'] ?? 0),
            (bool) ($lt->include_weekends ?? false),
            (bool) ($lt->include_public_holidays ?? false)
        );

        if (!empty($post['half_day'])) {
            $total_days = 0.5;
        }

        // Admins applying on behalf skip the approval queue entirely
        $status = 'pending_manager';
        if ($on_behalf_id && is_admin() && !empty($post['admin_auto_approve'])) {
            $status = 'approved';
        }

        $data = [
            'employee_id'          => $target_emp_id,
            'leave_type_id'        => $leave_type_id,
            'company_id'           => (int) ($post['company_id'] ?? 0),
            'date_from'            => $date_from,
            'date_to'              => $date_to,
            'half_day'             => !empty($post['half_day']) ? 1 : 0,
            'half_day_period'      => $post['half_day_period'] ?? 'morning',
            'total_days'           => $total_days,
            'reason'               => $post['reason'] ?? null,
            'handover_employee_id' => !empty($post['handover_employee_id']) ? (int) $post['handover_employee_id'] : null,
            'handover_notes'       => $post['handover_notes'] ?? null,
            'status'               => $status,
        ];

        $saved_id = $this->leave->save_leave_request($data, $req_id);

        if ($saved_id && !$req_id) {
            $this->leave->deduct_allocation($target_emp_id, $leave_type_id, $total_days);
        }

        $this->set_alert('success', _l('leave_request_submitted'));
        $this->redirect($this->base() . '/leave/my_requests');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MANAGER APPROVALS (Level 1)
    // ══════════════════════════════════════════════════════════════════════════

    private function _approvals($action, $id)
    {
        $employee = $this->_current_employee();
        $emp_id   = $employee ? (int) $employee->id : 0;

        if ($action === 'approve' && $id) {
            $req = $this->leave->get_leave_request((int) $id);
            if ($req) {
                $full_approve = is_admin() && $this->post('full_approve');
                $new_status   = $full_approve ? 'approved' : 'pending_hr';
                $this->leave->save_leave_request(['status' => $new_status], (int) $id);
                $this->leave->save_leave_approval([
                    'request_id'  => (int) $id,
                    'approver_id' => $emp_id,
                    'level'       => 'manager',
                    'action'      => 'approve',
                    'comment'     => $this->post('comment') ?? null,
                ]);
                if ($full_approve) {
                    // Also record the HR level approval
                    $this->leave->save_leave_approval([
                        'request_id'  => (int) $id,
                        'approver_id' => $emp_id,
                        'level'       => 'hr',
                        'action'      => 'approve',
                        'comment'     => 'Admin full approval',
                    ]);
                    $this->set_alert('success', 'Leave fully approved.');
                } else {
                    $this->set_alert('success', _l('leave_approved_sent_to_hr'));
                }
            }
            $this->redirect($this->base() . '/leave/approvals');
            return;
        }

        if ($action === 'reject' && $id) {
            $req = $this->leave->get_leave_request((int) $id);
            if ($req) {
                $reason = $this->post('rejection_reason') ?? '';
                $this->leave->save_leave_request([
                    'status'           => 'rejected',
                    'rejection_reason' => $reason,
                    'manager_comment'  => $reason,
                ], (int) $id);
                $this->leave->restore_allocation(
                    (int) $req->employee_id,
                    (int) $req->leave_type_id,
                    (float) $req->total_days
                );
                $this->leave->save_leave_approval([
                    'request_id'  => (int) $id,
                    'approver_id' => $emp_id,
                    'level'       => 'manager',
                    'action'      => 'reject',
                    'comment'     => $reason,
                ]);
                $this->set_alert('warning', _l('leave_rejected'));
            }
            $this->redirect($this->base() . '/leave/approvals');
            return;
        }

        if ($action === 'set_approver') {
            header('Content-Type: application/json');
            $target_emp_id = (int) ($this->post('employee_id') ?? 0);
            $approver_id   = (int) ($this->post('approver_id') ?? 0);
            if ($target_emp_id) {
                $this->CI->db->where('id', $target_emp_id)
                    ->update(db_prefix() . 'hr_employees', [
                        'leave_approver' => $approver_id ?: null,
                    ]);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid employee']);
            }
            exit;
        }

        // Super-admins see ALL pending_manager requests; regular managers see their direct reports
        $is_super = is_admin();
        $pending  = $this->leave->get_pending_manager_approvals($emp_id, $is_super);
        $counts   = $this->leave->get_leave_request_counts();

        $p = db_prefix();
        $all_employees = $this->CI->db
            ->select("e.id, CONCAT(e.first_name,' ',e.last_name) AS full_name,
                      e.leave_approver,
                      CONCAT(a.first_name,' ',a.last_name) AS approver_name,
                      dep.name AS department_name")
            ->from($p . 'hr_employees e')
            ->join($p . 'hr_employees a', 'a.id = e.leave_approver', 'left')
            ->join($p . 'hr_departments dep', 'dep.id = e.department_id', 'left')
            ->where('e.active', 1)
            ->order_by('e.first_name', 'ASC')
            ->get()->result();

        $approver_options = $this->CI->db
            ->select("id, CONCAT(first_name,' ',last_name) AS full_name")
            ->from($p . 'hr_employees')
            ->where('active', 1)
            ->order_by('first_name', 'ASC')
            ->get()->result();

        $this->view('xetuu_hr/admin/leave/approvals', array_merge($this->_base_data(), [
            'title'            => _l('leave_manager_approvals'),
            'pending'          => $pending,
            'counts'           => $counts,
            'is_super'         => $is_super,
            'emp_id'           => $emp_id,
            'all_employees'    => $all_employees,
            'approver_options' => $approver_options,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HR APPROVALS (Level 2)
    // ══════════════════════════════════════════════════════════════════════════

    private function _hr_approvals($action, $id)
    {
        $employee = $this->_current_employee();
        $emp_id   = $employee ? (int) $employee->id : 0;

        if ($action === 'approve' && $id) {
            $req = $this->leave->get_leave_request((int) $id);
            if ($req) {
                $this->leave->save_leave_request(['status' => 'approved'], (int) $id);
                $this->leave->save_leave_approval([
                    'request_id'  => (int) $id,
                    'approver_id' => $emp_id,
                    'level'       => 'hr',
                    'action'      => 'approve',
                    'comment'     => $this->post('comment') ?? null,
                ]);
                $this->set_alert('success', _l('leave_fully_approved'));
            }
            $this->redirect($this->base() . '/leave/hr_approvals');
            return;
        }

        if ($action === 'reject' && $id) {
            $req = $this->leave->get_leave_request((int) $id);
            if ($req) {
                $reason = $this->post('rejection_reason') ?? '';
                $this->leave->save_leave_request([
                    'status'           => 'rejected',
                    'rejection_reason' => $reason,
                    'hr_comment'       => $reason,
                ], (int) $id);
                $this->leave->restore_allocation(
                    (int) $req->employee_id,
                    (int) $req->leave_type_id,
                    (float) $req->total_days
                );
                $this->leave->save_leave_approval([
                    'request_id'  => (int) $id,
                    'approver_id' => $emp_id,
                    'level'       => 'hr',
                    'action'      => 'reject',
                    'comment'     => $reason,
                ]);
                $this->set_alert('warning', _l('leave_rejected'));
            }
            $this->redirect($this->base() . '/leave/hr_approvals');
            return;
        }

        $pending = $this->leave->get_pending_hr_approvals();
        $counts  = $this->leave->get_leave_request_counts();

        $this->view('xetuu_hr/admin/leave/hr_approvals', array_merge($this->_base_data(), [
            'title'   => _l('leave_hr_approvals'),
            'pending' => $pending,
            'counts'  => $counts,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ALLOCATIONS
    // ══════════════════════════════════════════════════════════════════════════

    private function _allocations($action, $id)
    {
        $post = $this->CI->input->post();

        // ── Bulk Apply Policy: apply to multiple employees at once ──
        if ($action === 'bulk_apply' && $post) {
            $post        = $this->post(null);
            $policy_id   = (int) ($post['policy_id'] ?? 0);
            $leave_year  = (int) ($post['leave_year'] ?? date('Y'));
            $emp_ids     = array_filter(array_map('intval', (array) ($post['employee_ids'] ?? [])));

            if (!$policy_id || empty($emp_ids)) {
                $this->set_alert('danger', 'Select a policy and at least one employee.');
                $this->redirect($this->base() . '/leave/allocations');
                return;
            }

            $policies = $this->leave->get_leave_policies();
            $policy   = null;
            foreach ($policies as $p2) {
                if ((int) $p2->id === $policy_id) { $policy = $p2; break; }
            }

            $total_created = 0;
            if ($policy && !empty($policy->lines)) {
                foreach ($emp_ids as $employee_id) {
                    foreach ($policy->lines as $line) {
                        $exists = $this->CI->db
                            ->where('employee_id', $employee_id)
                            ->where('leave_type_id', (int) $line->leave_type_id)
                            ->where('leave_year', $leave_year)
                            ->count_all_results(db_prefix() . 'hr_leave_allocations');
                        if ($exists) { continue; }
                        $this->leave->save_allocation([
                            'employee_id'          => $employee_id,
                            'leave_type_id'        => (int) $line->leave_type_id,
                            'policy_id'            => $policy_id,
                            'leave_year'           => $leave_year,
                            'total_days'           => (float) ($line->annual_days ?? 0),
                            'carried_forward_days' => 0,
                            'status'               => 'confirmed',
                            'notes'                => 'Bulk applied: ' . $policy->name,
                            'created_by'           => get_staff_user_id(),
                        ]);
                        $total_created++;
                    }
                }
            }

            $this->set_alert('success', $total_created . ' allocation(s) created for ' . count($emp_ids) . ' employee(s) from policy "' . ($policy ? $policy->name : 'Unknown') . '".');
            $this->redirect($this->base() . '/leave/allocations');
            return;
        }

        // ── Apply Policy: create all allocations from a policy at once ──
        if ($action === 'apply_policy' && $post) {
            $post        = $this->post(null);
            $policy_id   = (int) ($post['policy_id'] ?? 0);
            $employee_id = (int) ($post['employee_id'] ?? 0);
            $leave_year  = (int) ($post['leave_year'] ?? date('Y'));

            if ($policy_id && $employee_id) {
                $policies = $this->leave->get_leave_policies();
                $policy   = null;
                foreach ($policies as $p2) {
                    if ((int) $p2->id === $policy_id) { $policy = $p2; break; }
                }
                if ($policy && !empty($policy->lines)) {
                    $created = 0;
                    foreach ($policy->lines as $line) {
                        // Skip if allocation already exists for this employee+type+year
                        $exists = $this->CI->db
                            ->where('employee_id', $employee_id)
                            ->where('leave_type_id', (int) $line->leave_type_id)
                            ->where('leave_year', $leave_year)
                            ->count_all_results(db_prefix() . 'hr_leave_allocations');
                        if ($exists) { continue; }
                        $this->leave->save_allocation([
                            'employee_id'          => $employee_id,
                            'leave_type_id'        => (int) $line->leave_type_id,
                            'policy_id'            => $policy_id,
                            'leave_year'           => $leave_year,
                            'total_days'           => (float) ($line->annual_days ?? 0),
                            'carried_forward_days' => 0,
                            'status'               => 'confirmed',
                            'notes'                => 'Applied from policy: ' . $policy->name,
                            'created_by'           => get_staff_user_id(),
                        ]);
                        $created++;
                    }
                    $this->set_alert('success', $created . ' allocation(s) created from policy "' . $policy->name . '".');
                } else {
                    $this->set_alert('warning', 'Policy has no leave type lines configured.');
                }
            } else {
                $this->set_alert('danger', 'Please select both an employee and a policy.');
            }
            $this->redirect($this->base() . '/leave/allocations');
            return;
        }

        // ── Manual single allocation save/edit ──
        if ($post) {
            $post     = $this->post(null);
            $alloc_id = !empty($post['allocation_id']) ? (int) $post['allocation_id'] : null;
            $post['created_by'] = get_staff_user_id();
            $this->leave->save_allocation($post, $alloc_id);
            $this->set_alert('success', $alloc_id ? _l('allocation_updated') : _l('allocation_created'));
            $this->redirect($this->base() . '/leave/allocations');
            return;
        }

        // ── Filters ──
        $filters = [];
        if ($this->get('employee_id'))   { $filters['employee_id']   = (int) $this->get('employee_id'); }
        if ($this->get('leave_year'))    { $filters['leave_year']    = (int) $this->get('leave_year'); }
        if ($this->get('leave_type_id')) { $filters['leave_type_id'] = (int) $this->get('leave_type_id'); }

        $p           = $this->p();
        $employees   = $this->CI->db->select('id, CONCAT(first_name," ",last_name) AS full_name, department_id')->from($p.'hr_employees')->where('status','active')->order_by('first_name')->get()->result();
        $departments = $this->CI->db->select('id, name')->from($p.'hr_departments')->order_by('name')->get()->result();
        $leave_types = $this->leave->get_leave_types();
        $policies    = $this->leave->get_leave_policies();

        $this->view('xetuu_hr/admin/leave/allocations', array_merge($this->_base_data(), [
            'title'       => _l('leave_allocations'),
            'allocations' => $this->leave->get_allocations($filters),
            'employees'   => $employees,
            'departments' => $departments,
            'leave_types' => $leave_types,
            'policies'    => $policies,
            'filters'     => $filters,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENCASHMENT
    // ══════════════════════════════════════════════════════════════════════════

    private function _encashment($action, $id)
    {
        if ($this->CI->input->post()) {
            $post        = $this->post(null);
            $enc_id      = !empty($post['encashment_id']) ? (int) $post['encashment_id'] : null;
            $post['created_by'] = get_staff_user_id();
            $this->leave->save_encashment($post, $enc_id);
            $this->set_alert('success', _l('encashment_saved'));
            $this->redirect($this->base() . '/leave/encashment');
            return;
        }

        $employee = $this->_current_employee();
        $emp_id   = $this->get('employee_id') ? (int) $this->get('employee_id') : ($employee ? (int) $employee->id : null);

        $this->view('xetuu_hr/admin/leave/encashment', array_merge($this->_base_data(), [
            'title'       => _l('leave_encashment'),
            'encashments' => $this->leave->get_encashments($emp_id),
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TOIL
    // ══════════════════════════════════════════════════════════════════════════

    private function _toil($action, $id)
    {
        $employee = $this->_current_employee();
        $emp_id   = $employee ? (int) $employee->id : 0;

        if ($this->CI->input->post()) {
            $post       = $this->post(null);
            $toil_id    = !empty($post['toil_id']) ? (int) $post['toil_id'] : null;
            $post['employee_id'] = $post['employee_id'] ?? $emp_id;
            $this->leave->save_toil_entry($post, $toil_id);
            $this->set_alert('success', _l('toil_entry_saved'));
            $this->redirect($this->base() . '/leave/toil');
            return;
        }

        // Manager approve / reject
        if (($action === 'approve' || $action === 'reject') && $id) {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $this->leave->save_toil_entry([
                'status'      => $status,
                'approved_by' => $emp_id,
            ], (int) $id);
            $this->set_alert('success', _l('toil_status_updated'));
            $this->redirect($this->base() . '/leave/toil');
            return;
        }

        $filter_emp = $this->get('employee_id') ? (int) $this->get('employee_id') : $emp_id;
        $status     = $this->get('status') ?: null;

        $this->view('xetuu_hr/admin/leave/toil', array_merge($this->_base_data(), [
            'title'        => _l('toil_entries'),
            'toil_entries' => $this->leave->get_toil_entries($filter_emp, $status),
            'employee'     => $employee,
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // REPORTS
    // ══════════════════════════════════════════════════════════════════════════

    private function _reports($action, $id)
    {
        // ── Excel: Employee Leave Balance Summary ──────────────────────────────
        if ($action === 'balance_excel') {
            $this->_export_balance_excel();
            return;
        }

        $filters = [];
        if ($this->get('employee_id')) { $filters['employee_id'] = (int) $this->get('employee_id'); }
        if ($this->get('status'))      { $filters['status']      = $this->get('status'); }
        if ($this->get('date_from'))   { $filters['date_from']   = $this->get('date_from'); }
        if ($this->get('date_to'))     { $filters['date_to']     = $this->get('date_to'); }

        // Pass data for the balance summary preview table on the page too
        $leave_types = $this->leave->get_leave_types(true);
        $year        = (int) ($this->get('year') ?: date('Y'));
        $dept_id     = (int) $this->get('department_id');
        $emp_status  = $this->get('emp_status') ?: 'active';

        $balance_matrix = $this->_build_balance_matrix($leave_types, $year, $dept_id, $emp_status);

        // Departments for filter
        $departments = $this->CI->db
            ->select('id, name')->from(db_prefix() . 'hr_departments')
            ->order_by('name')->get()->result();

        $this->view('xetuu_hr/admin/leave/reports', array_merge($this->_base_data(), [
            'title'          => _l('leave_reports'),
            'requests'       => $this->leave->get_leave_requests($filters, 200),
            'leave_types'    => $leave_types,
            'balance_matrix' => $balance_matrix,
            'departments'    => $departments,
            'year'           => $year,
            'dept_id'        => $dept_id,
            'emp_status'     => $emp_status,
        ]));
    }

    /**
     * Build pivot: [employee_row => [...], types => [...], rows => [[emp, dept, [type_id => balance]]]]
     */
    private function _build_balance_matrix($leave_types, $year, $dept_id = 0, $emp_status = 'active')
    {
        $p = db_prefix();

        $q = $this->CI->db
            ->select("e.id, e.employee_number,
                      CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                      d.name AS department_name,
                      a.leave_type_id,
                      a.total_days, a.carried_forward_days, a.used_days, a.encashed_days")
            ->from($p . 'hr_employees e')
            ->join($p . 'hr_leave_allocations a', 'a.employee_id = e.id AND a.leave_year = ' . (int)$year . " AND a.status = 'confirmed'", 'left')
            ->join($p . 'hr_departments d', 'd.id = e.department_id', 'left')
            ->where('e.active', 1);

        if ($dept_id) {
            $q->where('e.department_id', $dept_id);
        }
        if ($emp_status) {
            $q->where('e.status', ucfirst($emp_status));
        }

        $rows = $q->order_by('e.first_name, e.last_name')->get()->result();

        // Build employee map
        $employees = [];
        foreach ($rows as $r) {
            $eid = $r->id;
            if (!isset($employees[$eid])) {
                $employees[$eid] = [
                    'id'          => $eid,
                    'number'      => $r->employee_number ?? '',
                    'name'        => $r->employee_name,
                    'department'  => $r->department_name ?? '',
                    'balances'    => [],
                ];
            }
            if ($r->leave_type_id) {
                $total = (float)($r->total_days ?? 0) + (float)($r->carried_forward_days ?? 0);
                $used  = (float)($r->used_days ?? 0) + (float)($r->encashed_days ?? 0);
                $employees[$eid]['balances'][$r->leave_type_id] = [
                    'total'     => $total,
                    'used'      => $used,
                    'remaining' => max(0, $total - $used),
                ];
            }
        }

        return array_values($employees);
    }

    private function _export_balance_excel()
    {
        $year       = (int) ($this->get('year') ?: date('Y'));
        $dept_id    = (int) $this->get('department_id');
        $emp_status = $this->get('emp_status') ?: 'active';
        $company    = get_option('companyname') ?: 'Company';

        $leave_types = $this->leave->get_leave_types(true);
        $matrix      = $this->_build_balance_matrix($leave_types, $year, $dept_id, $emp_status);

        // ── Build Excel XML (SpreadsheetML) ───────────────────────────────────
        $xl = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xl .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xl .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                 xmlns:o="urn:schemas-microsoft-com:office:office"
                 xmlns:x="urn:schemas-microsoft-com:office:excel"
                 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                 xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        // Styles
        $xl .= '<Styles>
  <Style ss:ID="title">
    <Font ss:Bold="1" ss:Size="14" ss:Color="#1e1040"/>
    <Alignment ss:Horizontal="Left"/>
  </Style>
  <Style ss:ID="meta">
    <Font ss:Size="10" ss:Color="#6b7280"/>
  </Style>
  <Style ss:ID="th">
    <Font ss:Bold="1" ss:Size="10" ss:Color="#FFFFFF"/>
    <Interior ss:Color="#2d1b69" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Center" ss:WrapText="1"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e5e7eb"/>
      <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#9ca3af"/>
    </Borders>
  </Style>
  <Style ss:ID="th_left">
    <Font ss:Bold="1" ss:Size="10" ss:Color="#FFFFFF"/>
    <Interior ss:Color="#2d1b69" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Left"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e5e7eb"/>
    </Borders>
  </Style>
  <Style ss:ID="cell">
    <Font ss:Size="10" ss:Color="#374151"/>
    <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
    </Borders>
  </Style>
  <Style ss:ID="num">
    <Font ss:Size="10" ss:Color="#374151"/>
    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
    <NumberFormat ss:Format="0.000"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
      <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
    </Borders>
  </Style>
  <Style ss:ID="num_zero">
    <Font ss:Size="10" ss:Color="#d1d5db"/>
    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
    <NumberFormat ss:Format="0.000"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
      <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
    </Borders>
  </Style>
  <Style ss:ID="alt">
    <Font ss:Size="10" ss:Color="#374151"/>
    <Interior ss:Color="#f9fafb" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
    </Borders>
  </Style>
  <Style ss:ID="alt_num">
    <Font ss:Size="10" ss:Color="#374151"/>
    <Interior ss:Color="#f9fafb" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
    <NumberFormat ss:Format="0.000"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
      <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#f3f4f6"/>
    </Borders>
  </Style>
  <Style ss:ID="total_lbl">
    <Font ss:Bold="1" ss:Size="10" ss:Color="#111827"/>
    <Interior ss:Color="#ede9fe" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="total_num">
    <Font ss:Bold="1" ss:Size="10" ss:Color="#2d1b69"/>
    <Interior ss:Color="#ede9fe" ss:Pattern="Solid"/>
    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
    <NumberFormat ss:Format="0.000"/>
  </Style>
</Styles>' . "\n";

        $xl .= '<Worksheet ss:Name="Leave Balance Summary">' . "\n";
        $xl .= '<Table ss:DefaultRowHeight="18">' . "\n";

        // Column widths
        $xl .= '<Column ss:Width="90"/>'; // Employee ID
        $xl .= '<Column ss:Width="160"/>'; // Name
        $xl .= '<Column ss:Width="130"/>'; // Department
        foreach ($leave_types as $lt) {
            $xl .= '<Column ss:Width="95"/>';
        }
        $xl .= "\n";

        // ── Row 1: Report title ───────────────────────────────────────────────
        $xl .= '<Row ss:Height="28"><Cell ss:StyleID="title" ss:MergeAcross="' . (2 + count($leave_types)) . '"><Data ss:Type="String">Employee Leave Balance Summary</Data></Cell></Row>' . "\n";

        // ── Row 2: Meta info ─────────────────────────────────────────────────
        $meta_cols = [
            date('d-m-Y'),
            $company,
            'Year: ' . $year,
            'Status: ' . ucfirst($emp_status),
        ];
        $xl .= '<Row ss:Height="16">';
        foreach ($meta_cols as $i => $mc) {
            $xl .= '<Cell ss:StyleID="meta"><Data ss:Type="String">' . htmlspecialchars($mc) . '</Data></Cell>';
        }
        $xl .= '</Row>' . "\n";

        // ── Row 3: blank ─────────────────────────────────────────────────────
        $xl .= '<Row ss:Height="8"></Row>' . "\n";

        // ── Row 4: Column headers ─────────────────────────────────────────────
        $xl .= '<Row ss:Height="32">';
        $xl .= '<Cell ss:StyleID="th_left"><Data ss:Type="String">Employee ID</Data></Cell>';
        $xl .= '<Cell ss:StyleID="th_left"><Data ss:Type="String">Employee Name</Data></Cell>';
        $xl .= '<Cell ss:StyleID="th_left"><Data ss:Type="String">Department</Data></Cell>';
        foreach ($leave_types as $lt) {
            $xl .= '<Cell ss:StyleID="th"><Data ss:Type="String">' . htmlspecialchars($lt->name) . '</Data></Cell>';
        }
        $xl .= '</Row>' . "\n";

        // ── Data rows ─────────────────────────────────────────────────────────
        $totals = [];
        foreach ($leave_types as $lt) { $totals[$lt->id] = 0; }

        foreach ($matrix as $i => $emp) {
            $alt = ($i % 2 === 1);
            $xl .= '<Row ss:Height="18">';
            $xl .= '<Cell ss:StyleID="' . ($alt ? 'alt' : 'cell') . '"><Data ss:Type="String">' . htmlspecialchars($emp['number']) . '</Data></Cell>';
            $xl .= '<Cell ss:StyleID="' . ($alt ? 'alt' : 'cell') . '"><Data ss:Type="String">' . htmlspecialchars($emp['name']) . '</Data></Cell>';
            $xl .= '<Cell ss:StyleID="' . ($alt ? 'alt' : 'cell') . '"><Data ss:Type="String">' . htmlspecialchars($emp['department']) . '</Data></Cell>';
            foreach ($leave_types as $lt) {
                $bal = $emp['balances'][$lt->id] ?? null;
                $rem = $bal ? (float)$bal['remaining'] : 0.0;
                $totals[$lt->id] += $rem;
                $ns  = $rem == 0 ? 'num_zero' : ($alt ? 'alt_num' : 'num');
                $xl .= '<Cell ss:StyleID="' . $ns . '"><Data ss:Type="Number">' . number_format($rem, 3, '.', '') . '</Data></Cell>';
            }
            $xl .= '</Row>' . "\n";
        }

        // ── Totals row ────────────────────────────────────────────────────────
        $xl .= '<Row ss:Height="20">';
        $xl .= '<Cell ss:StyleID="total_lbl" ss:MergeAcross="2"><Data ss:Type="String">TOTAL (' . count($matrix) . ' employees)</Data></Cell>';
        $xl .= '<Cell/><Cell/>';
        foreach ($leave_types as $lt) {
            $xl .= '<Cell ss:StyleID="total_num"><Data ss:Type="Number">' . number_format($totals[$lt->id], 3, '.', '') . '</Data></Cell>';
        }
        $xl .= '</Row>' . "\n";

        $xl .= '</Table>' . "\n";

        // Freeze panes at row 5 col D
        $xl .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
  <FreezePanes/>
  <FrozenNoSplit/>
  <SplitHorizontal>4</SplitHorizontal>
  <TopRowBottomPane>4</TopRowBottomPane>
  <SplitVertical>3</SplitVertical>
  <LeftColumnRightPane>3</LeftColumnRightPane>
  <ActivePane>0</ActivePane>
</WorksheetOptions>' . "\n";

        $xl .= '</Worksheet>' . "\n";
        $xl .= '</Workbook>';

        $filename = 'Leave_Balance_Summary_' . $year . '_' . date('Ymd') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        echo $xl;
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONFIG (types / policies / holidays)
    // ══════════════════════════════════════════════════════════════════════════

    private function _config($action, $id)
    {
        switch ($action) {
            case 'types':
                $this->_config_types($id);
                break;
            case 'policies':
                $this->_config_policies($id);
                break;
            case 'holidays':
                $this->_config_holidays($id);
                break;
            default:
                // Redirect to types as the default config landing
                $this->redirect($this->base() . '/leave/config/types');
        }
    }

    private function _config_types($id)
    {
        if ($this->CI->input->post()) {
            $post    = $this->post(null);
            $type_id = !empty($post['id']) ? (int) $post['id'] : null;
            $post['created_by'] = get_staff_user_id();
            $this->leave->save_leave_type($post, $type_id);
            $this->set_alert('success', $type_id ? _l('leave_type_updated') : _l('leave_type_created'));
            $this->redirect($this->base() . '/leave/config/types');
            return;
        }

        if ($id === 'delete' && ($del_id = (int) $this->uri(7))) {
            $this->leave->save_leave_type(['status' => 'inactive'], $del_id);
            $this->set_alert('success', _l('leave_type_deactivated'));
            $this->redirect($this->base() . '/leave/config/types');
            return;
        }

        if ($id === 'toggle' && ($tog_id = (int) $this->uri(7))) {
            header('Content-Type: application/json');
            $lt = $this->leave->get_leave_type($tog_id);
            if ($lt) {
                $new_status = ($lt->status === 'active') ? 'inactive' : 'active';
                $this->leave->save_leave_type(['status' => $new_status], $tog_id);
                echo json_encode(['success' => true, 'status' => $new_status]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false]);
            }
            exit;
        }

        $editing = ($id && is_numeric($id)) ? $this->leave->get_leave_type((int) $id) : null;

        $this->view('xetuu_hr/admin/leave/config/types', array_merge($this->_base_data(), [
            'title'       => _l('leave_types'),
            'leave_types' => $this->leave->get_leave_types(false),
            'editing'     => $editing,
        ]));
    }

    private function _config_policies($id)
    {
        if ($this->CI->input->post()) {
            $post      = $this->post(null);
            $policy_id = !empty($post['id']) ? (int) $post['id'] : null;
            $post['created_by'] = get_staff_user_id();
            $saved_id  = $this->leave->save_leave_policy($post, $policy_id);

            // Replace all policy lines: delete old ones, insert fresh
            if ($policy_id) {
                $this->leave->delete_policy_lines($saved_id);
            }
            if (!empty($post['lines']) && is_array($post['lines'])) {
                foreach ($post['lines'] as $line) {
                    if (empty($line['leave_type_id'])) { continue; }
                    $this->leave->save_policy_line([
                        'policy_id'        => $saved_id,
                        'leave_type_id'    => (int) $line['leave_type_id'],
                        'annual_days'      => (float) ($line['days_per_year'] ?? 0),
                        'allow_carryforward' => !empty($line['allow_carryforward']) ? 1 : 0,
                    ]);
                }
            }

            $this->set_alert('success', $policy_id ? _l('policy_updated') : _l('policy_created'));
            $this->redirect($this->base() . '/leave/config/policies');
            return;
        }

        if ($id === 'delete' && ($del_id = (int) $this->uri(7))) {
            $this->leave->delete_leave_policy($del_id);
            $this->set_alert('success', _l('policy_deleted'));
            $this->redirect($this->base() . '/leave/config/policies');
            return;
        }

        $editing      = ($id && is_numeric($id)) ? $this->leave->get_leave_policy((int) $id) : null;
        $policy_lines = $editing ? $this->leave->get_policy_lines((int) $id) : [];

        $this->view('xetuu_hr/admin/leave/config/policies', array_merge($this->_base_data(), [
            'title'        => _l('leave_policies'),
            'policies'     => $this->leave->get_leave_policies(),
            'editing'      => $editing,
            'policy_lines' => $policy_lines,
        ]));
    }

    private function _config_holidays($id)
    {
        if ($this->CI->input->post()) {
            $post    = $this->post(null);
            $list_id = !empty($post['list_id']) ? (int) $post['list_id'] : null;
            $post['created_by'] = get_staff_user_id();
            $this->leave->save_holiday_list($post, $list_id);
            $this->set_alert('success', $list_id ? _l('holiday_list_updated') : _l('holiday_list_created'));
            $this->redirect($this->base() . '/leave/config/holidays');
            return;
        }

        if ($id === 'delete' && ($del_id = (int) $this->uri(7))) {
            $this->leave->delete_holiday_list($del_id);
            $this->set_alert('success', _l('holiday_list_deleted'));
            $this->redirect($this->base() . '/leave/config/holidays');
            return;
        }

        // AJAX: load holidays for a specific list
        if ($id === 'list_detail') {
            $list_id = (int) $this->uri(7);
            header('Content-Type: application/json');
            echo json_encode(['holidays' => $this->leave->get_public_holidays($list_id)]);
            exit;
        }

        $this->view('xetuu_hr/admin/leave/config/holidays', array_merge($this->_base_data(), [
            'title'         => _l('holiday_lists'),
            'holiday_lists' => $this->leave->get_holiday_lists(),
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Calc Working Days
    // ══════════════════════════════════════════════════════════════════════════

    private function _calc_days()
    {
        $date_from     = $this->get('date_from');
        $date_to       = $this->get('date_to');
        $leave_type_id = (int) $this->get('leave_type_id');
        $employee_id   = (int) $this->get('employee_id');

        if (!$date_from || !$date_to) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'date_from and date_to are required']);
            return;
        }

        $lt  = $leave_type_id ? $this->leave->get_leave_type($leave_type_id) : null;
        $inc_weekends  = $lt ? (bool) $lt->include_weekends        : false;
        $inc_holidays  = $lt ? (bool) $lt->include_public_holidays : false;

        // Resolve company_id from employee
        $company_id = 0;
        if ($employee_id) {
            $emp = $this->CI->db
                ->select('company_id')
                ->from($this->p() . 'hr_employees')
                ->where('id', $employee_id)
                ->get()
                ->row();
            if ($emp) {
                $company_id = (int) $emp->company_id;
            }
        }

        $working_days = $this->leave->calc_working_days(
            $date_from, $date_to, $company_id, $inc_weekends, $inc_holidays
        );

        // Count calendar days
        $start = new DateTime($date_from);
        $end   = new DateTime($date_to);
        $diff  = $end->diff($start);
        $calendar_days = $diff->days + 1;

        // Count excluded holidays
        $holidays_excluded = $calendar_days - $working_days;

        header('Content-Type: application/json');
        echo json_encode([
            'days'              => $working_days,
            'working_days'      => $working_days,
            'holidays_excluded' => $holidays_excluded,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Import Holidays from Nager.Date API
    // ══════════════════════════════════════════════════════════════════════════

    private function _import_holidays()
    {
        header('Content-Type: application/json');

        $country_code    = strtoupper(trim($this->get('country_code') ?? ''));
        $year            = (int) ($this->get('year') ?? date('Y'));
        $holiday_list_id = (int) ($this->get('holiday_list_id') ?? 0);

        if (!$country_code || !$year || !$holiday_list_id) {
            echo json_encode(['success' => false, 'error' => 'country_code, year and holiday_list_id are required']);
            return;
        }

        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$country_code}";

        $response = false;

        // Try curl first; fall back to file_get_contents
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'XetuuHR/1.0',
            ]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code !== 200) {
                $response = false;
            }
        }

        if ($response === false) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout'     => 15,
                    'user_agent'  => 'XetuuHR/1.0',
                ],
                'ssl' => ['verify_peer' => false],
            ]);
            $response = @file_get_contents($url, false, $ctx);
        }

        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'Could not reach Nager.Date API']);
            return;
        }

        $holidays = json_decode($response, true);
        if (!is_array($holidays)) {
            echo json_encode(['success' => false, 'error' => 'Invalid response from API']);
            return;
        }

        // Normalise the Nager.Date format → internal format
        $normalised = array_map(function ($h) {
            return [
                'date'        => $h['date'],
                'name'        => $h['name'],
                'localName'   => $h['localName'] ?? $h['name'],
                'type'        => 'national',
            ];
        }, $holidays);

        $inserted = $this->leave->import_holidays_from_array($holiday_list_id, $normalised);

        echo json_encode([
            'success'  => true,
            'count'    => $inserted,
            'message'  => "{$inserted} holiday(s) imported for {$country_code} {$year}",
        ]);
    }
}
