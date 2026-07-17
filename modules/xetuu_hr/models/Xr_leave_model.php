<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Xr_leave_model
 *
 * Leave management model for the Xetuu HR module.
 * Covers leave types, policies, allocations, requests, approvals,
 * holiday lists, public holidays, TOIL entries and encashments.
 */
class Xr_leave_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Shorthand for db_prefix(). */
    public function p()
    {
        return db_prefix();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LEAVE TYPES
    // ══════════════════════════════════════════════════════════════════════════

    public function get_leave_types($active_only = true)
    {
        if ($active_only) {
            $this->db->where($this->p() . 'hr_leave_types.status', 'active');
        }
        return $this->db
            ->select('*')
            ->from($this->p() . 'hr_leave_types')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function get_leave_type($id)
    {
        return $this->db
            ->select('*')
            ->from($this->p() . 'hr_leave_types')
            ->where('id', (int) $id)
            ->get()
            ->row();
    }

    public function save_leave_type($data, $id = null)
    {
        // Convert is_active checkbox → status enum
        if (array_key_exists('is_active', $data)) {
            $data['status'] = !empty($data['is_active']) ? 'active' : 'inactive';
            unset($data['is_active']);
        }

        $allowed = [
            'name', 'code', 'color', 'unit', 'default_days', 'is_paid', 'gender_restriction',
            'requires_proof', 'allow_half_day', 'carry_forward', 'max_carry_forward',
            'carry_forward_expiry_days', 'allow_negative', 'approval_levels',
            'cascade_to_type_id', 'encashable', 'include_public_holidays', 'include_weekends',
            'notice_days_required', 'max_consecutive_days', 'description', 'status', 'created_by',
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_types', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_types', $data);
        return $this->db->insert_id();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LEAVE POLICIES
    // ══════════════════════════════════════════════════════════════════════════

    public function get_leave_policies($company_id = null)
    {
        if ($company_id !== null) {
            $this->db->where('company_id', (int) $company_id);
        }
        $policies = $this->db
            ->select('*')
            ->from($this->p() . 'hr_leave_policies')
            ->order_by('name', 'ASC')
            ->get()
            ->result();

        // Attach lines (with leave type name + color) to each policy
        foreach ($policies as $pol) {
            $pol->lines = $this->db
                ->select('pl.*, lt.name AS leave_type_name, lt.color AS leave_type_color')
                ->from($this->p() . 'hr_leave_policy_lines pl')
                ->join($this->p() . 'hr_leave_types lt', 'lt.id = pl.leave_type_id', 'left')
                ->where('pl.policy_id', (int) $pol->id)
                ->order_by('lt.name', 'ASC')
                ->get()
                ->result();
        }

        return $policies;
    }

    public function get_leave_policy($id)
    {
        return $this->db
            ->select('*')
            ->from($this->p() . 'hr_leave_policies')
            ->where('id', (int) $id)
            ->get()
            ->row();
    }

    public function save_leave_policy($data, $id = null)
    {
        if (array_key_exists('is_active', $data)) {
            $data['status'] = !empty($data['is_active']) ? 'active' : 'inactive';
            unset($data['is_active']);
        }
        $allowed = ['name', 'description', 'company_id', 'status', 'created_by'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_policies', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_policies', $data);
        return $this->db->insert_id();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // POLICY LINES
    // ══════════════════════════════════════════════════════════════════════════

    public function get_policy_lines($policy_id)
    {
        return $this->db
            ->select('pl.*, lt.name AS leave_type_name, lt.color AS leave_type_color')
            ->from($this->p() . 'hr_leave_policy_lines pl')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = pl.leave_type_id', 'left')
            ->where('pl.policy_id', (int) $policy_id)
            ->get()
            ->result();
    }

    public function delete_policy_lines($policy_id)
    {
        $this->db->where('policy_id', (int) $policy_id)->delete($this->p() . 'hr_leave_policy_lines');
    }

    public function delete_leave_policy($id)
    {
        $this->delete_policy_lines((int) $id);
        $this->db->where('id', (int) $id)->delete($this->p() . 'hr_leave_policies');
        return $this->db->affected_rows() > 0;
    }

    public function save_policy_line($data, $id = null)
    {
        $allowed = ['policy_id', 'leave_type_id', 'annual_days', 'allow_carryforward', 'accrual_plan_id'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_policy_lines', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_policy_lines', $data);
        return $this->db->insert_id();
    }

    public function delete_policy_line($id)
    {
        $this->db->where('id', (int) $id)->delete($this->p() . 'hr_leave_policy_lines');
        return $this->db->affected_rows() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ALLOCATIONS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_allocations($filters = [])
    {
        $this->db
            ->select('a.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                      lt.name AS leave_type_name, lt.color AS leave_type_color,
                      d.name AS department_name,
                      lp.name AS policy_name,
                      COALESCE(a.used_days, 0) AS used_days')
            ->from($this->p() . 'hr_leave_allocations a')
            ->join($this->p() . 'hr_employees e', 'e.id = a.employee_id', 'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = a.leave_type_id', 'left')
            ->join($this->p() . 'hr_departments d', 'd.id = e.department_id', 'left')
            ->join($this->p() . 'hr_leave_policies lp', 'lp.id = a.policy_id', 'left');

        if (!empty($filters['employee_id'])) {
            $this->db->where('a.employee_id', (int) $filters['employee_id']);
        }
        if (!empty($filters['leave_type_id'])) {
            $this->db->where('a.leave_type_id', (int) $filters['leave_type_id']);
        }
        if (!empty($filters['leave_year'])) {
            $this->db->where('a.leave_year', $filters['leave_year']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('a.company_id', (int) $filters['company_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('a.status', $filters['status']);
        }

        return $this->db->order_by('a.leave_year DESC, lt.name ASC')->get()->result();
    }

    public function get_allocation($id)
    {
        return $this->db
            ->select('a.*, CONCAT(e.first_name," ",e.last_name) AS employee_name, lt.name AS leave_type_name, lt.color AS leave_type_color')
            ->from($this->p() . 'hr_leave_allocations a')
            ->join($this->p() . 'hr_employees e', 'e.id = a.employee_id', 'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = a.leave_type_id', 'left')
            ->where('a.id', (int) $id)
            ->get()
            ->row();
    }

    /**
     * Return a single allocation row with balance for the given employee / leave type / year.
     */
    public function get_employee_allocation($employee_id, $leave_type_id, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        return $this->db
            ->select('a.*, (a.total_days + a.carried_forward_days - a.used_days - a.encashed_days) AS balance')
            ->from($this->p() . 'hr_leave_allocations a')
            ->where('a.employee_id', (int) $employee_id)
            ->where('a.leave_type_id', (int) $leave_type_id)
            ->where('a.leave_year', $year)
            ->where('a.status', 'confirmed')
            ->order_by('a.id', 'DESC')
            ->get()
            ->row();
    }

    public function save_allocation($data, $id = null)
    {
        $allowed = [
            'employee_id', 'leave_type_id', 'policy_id', 'company_id', 'leave_year', 'date_from', 'date_to',
            'total_days', 'carried_forward_days', 'carry_forward_expiry', 'status', 'notes', 'created_by',
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $data['date_modified'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_allocations', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_allocations', $data);
        return $this->db->insert_id();
    }

    /**
     * Increment used_days by $days for an employee + leave_type + year.
     */
    public function deduct_allocation($employee_id, $leave_type_id, $days, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        $this->db->query(
            "UPDATE `{$this->p()}hr_leave_allocations`
             SET `used_days` = `used_days` + ?, `date_modified` = NOW()
             WHERE `employee_id` = ? AND `leave_type_id` = ? AND `leave_year` = ? AND `status` = 'confirmed'",
            [(float) $days, (int) $employee_id, (int) $leave_type_id, $year]
        );
        return $this->db->affected_rows() > 0;
    }

    /**
     * Reverse a prior deduction (e.g. on rejection / cancellation).
     */
    public function restore_allocation($employee_id, $leave_type_id, $days, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        $this->db->query(
            "UPDATE `{$this->p()}hr_leave_allocations`
             SET `used_days` = GREATEST(0, `used_days` - ?), `date_modified` = NOW()
             WHERE `employee_id` = ? AND `leave_type_id` = ? AND `leave_year` = ? AND `status` = 'confirmed'",
            [(float) $days, (int) $employee_id, (int) $leave_type_id, $year]
        );
        return $this->db->affected_rows() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LEAVE REQUESTS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_leave_requests($filters = [], $limit = 50)
    {
        $this->db
            ->select('r.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                      lt.name AS leave_type_name, lt.color AS type_color, lt.is_paid AS leave_is_paid,
                      CONCAT(h.first_name," ",h.last_name) AS handover_employee_name')
            ->from($this->p() . 'hr_leave_requests r')
            ->join($this->p() . 'hr_employees e',  'e.id = r.employee_id', 'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = r.leave_type_id', 'left')
            ->join($this->p() . 'hr_employees h',  'h.id = r.handover_employee_id', 'left');

        if (!empty($filters['employee_id'])) {
            $this->db->where('r.employee_id', (int) $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->db->where_in('r.status', $filters['status']);
            } else {
                $this->db->where('r.status', $filters['status']);
            }
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('r.date_to >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('r.date_from <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('r.company_id', (int) $filters['company_id']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('YEAR(r.date_from)', (int) $filters['year']);
        }

        return $this->db
            ->order_by('r.date_created', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    /**
     * Load a single leave request plus its approval log.
     */
    public function get_leave_request($id)
    {
        $row = $this->db
            ->select('r.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                      lt.name AS leave_type_name, lt.color AS type_color, lt.is_paid AS leave_is_paid,
                      CONCAT(h.first_name," ",h.last_name) AS handover_employee_name')
            ->from($this->p() . 'hr_leave_requests r')
            ->join($this->p() . 'hr_employees e',  'e.id = r.employee_id', 'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = r.leave_type_id', 'left')
            ->join($this->p() . 'hr_employees h',  'h.id = r.handover_employee_id', 'left')
            ->where('r.id', (int) $id)
            ->get()
            ->row();

        if ($row) {
            $row->approvals = $this->get_leave_approvals($id);
        }

        return $row;
    }

    public function save_leave_request($data, $id = null)
    {
        $allowed = [
            'employee_id', 'leave_type_id', 'company_id', 'date_from', 'date_to',
            'half_day', 'half_day_period', 'total_days', 'total_hours', 'reason',
            'handover_employee_id', 'handover_notes', 'proof_document', 'status',
            'manager_comment', 'hr_comment', 'rejection_reason',
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $data['date_modified'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_requests', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_requests', $data);
        return $this->db->insert_id();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LEAVE APPROVALS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_leave_approvals($request_id)
    {
        return $this->db
            ->select('a.*, CONCAT(e.first_name," ",e.last_name) AS approver_name')
            ->from($this->p() . 'hr_leave_approvals a')
            ->join($this->p() . 'hr_employees e', 'e.id = a.approver_id', 'left')
            ->where('a.request_id', (int) $request_id)
            ->order_by('a.date_created', 'ASC')
            ->get()
            ->result();
    }

    public function save_leave_approval($data)
    {
        $allowed = ['request_id', 'approver_id', 'level', 'action', 'comment'];
        $data    = array_intersect_key($data, array_flip($allowed));
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_approvals', $data);
        return $this->db->insert_id();
    }

    /**
     * Requests waiting for Level-1 (manager) approval where the requesting
     * employee's reports_to matches $manager_employee_id.
     */
    public function get_pending_manager_approvals($manager_employee_id, $all = false)
    {
        $q = $this->db
            ->select('r.*,
                CONCAT(e.first_name," ",e.last_name)   AS employee_name,
                lt.name                                 AS leave_type_name,
                lt.color                                AS type_color,
                lt.is_paid                              AS leave_is_paid,
                d.name                                  AS department_name,
                des.name                                AS designation_name,
                CONCAT(he2.first_name," ",he2.last_name) AS handover_employee_name,
                a.total_days AS alloc_total,
                a.used_days  AS alloc_used')
            ->from($this->p() . 'hr_leave_requests r')
            ->join($this->p() . 'hr_employees e',   'e.id = r.employee_id',       'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = r.leave_type_id',   'left')
            ->join($this->p() . 'hr_departments d',  'd.id = e.department_id',    'left')
            ->join($this->p() . 'hr_designations des', 'des.id = e.designation_id', 'left')
            ->join($this->p() . 'hr_employees he2',  'he2.id = r.handover_employee_id', 'left')
            ->join($this->p() . 'hr_leave_allocations a',
                   'a.employee_id = r.employee_id AND a.leave_type_id = r.leave_type_id AND a.leave_year = YEAR(r.date_from)',
                   'left')
            ->where('r.status', 'pending_manager');

        // Super admins see all; regular managers see only their direct reports
        if (!$all && $manager_employee_id) {
            $q->where('e.leave_approver', (int) $manager_employee_id);
        }

        return $q->order_by('r.date_from', 'ASC')->get()->result();
    }

    /**
     * Requests waiting for Level-2 (HR) approval.
     */
    public function get_pending_hr_approvals($company_id = null)
    {
        $this->db
            ->select('r.*,
                CONCAT(e.first_name," ",e.last_name)   AS employee_name,
                lt.name                                 AS leave_type_name,
                lt.color                                AS type_color,
                lt.is_paid                              AS leave_is_paid,
                d.name                                  AS department_name,
                des.name                                AS designation_name,
                CONCAT(he2.first_name," ",he2.last_name) AS handover_employee_name,
                a.total_days AS alloc_total,
                a.used_days  AS alloc_used')
            ->from($this->p() . 'hr_leave_requests r')
            ->join($this->p() . 'hr_employees e',    'e.id = r.employee_id',      'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = r.leave_type_id',   'left')
            ->join($this->p() . 'hr_departments d',  'd.id = e.department_id',    'left')
            ->join($this->p() . 'hr_designations des', 'des.id = e.designation_id', 'left')
            ->join($this->p() . 'hr_employees he2',  'he2.id = r.handover_employee_id', 'left')
            ->join($this->p() . 'hr_leave_allocations a',
                   'a.employee_id = r.employee_id AND a.leave_type_id = r.leave_type_id AND a.leave_year = YEAR(r.date_from)',
                   'left')
            ->where('r.status', 'pending_hr');

        if ($company_id) {
            $this->db->where('r.company_id', (int) $company_id);
        }

        return $this->db->order_by('r.date_from', 'ASC')->get()->result();
    }

    /**
     * Count all requests by status across the system (for dashboard / stats).
     */
    public function get_leave_request_counts()
    {
        $rows = $this->db
            ->select('status, COUNT(*) AS cnt')
            ->from($this->p() . 'hr_leave_requests')
            ->group_by('status')
            ->get()->result();
        $out = [];
        foreach ($rows as $r) { $out[$r->status] = (int)$r->cnt; }
        return $out;
    }

    /**
     * Return all active allocations for an employee with remaining balance.
     */
    public function get_employee_balance_summary($employee_id, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        return $this->db
            ->select('a.*, lt.name AS leave_type_name, lt.color AS leave_type_color,
                      (a.total_days + a.carried_forward_days - a.used_days - a.encashed_days) AS balance_remaining')
            ->from($this->p() . 'hr_leave_allocations a')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = a.leave_type_id', 'left')
            ->where('a.employee_id', (int) $employee_id)
            ->where('a.leave_year', $year)
            ->where('a.status', 'confirmed')
            ->order_by('lt.name', 'ASC')
            ->get()
            ->result();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HOLIDAY LISTS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_holiday_lists($filters = [])
    {
        $this->db->select('*')->from($this->p() . 'hr_holiday_lists');

        if (!empty($filters['company_id'])) {
            $this->db->where('company_id', (int) $filters['company_id']);
        }
        if (!empty($filters['year'])) {
            $this->db->where('year', $filters['year']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('country_code', $filters['country_code']);
        }

        return $this->db->order_by('year DESC, name ASC')->get()->result();
    }

    public function get_holiday_list($id)
    {
        return $this->db
            ->select('*')
            ->from($this->p() . 'hr_holiday_lists')
            ->where('id', (int) $id)
            ->get()
            ->row();
    }

    public function save_holiday_list($data, $id = null)
    {
        $allowed = ['name', 'country_code', 'year', 'company_id', 'branch_id', 'is_default', 'created_by'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_holiday_lists', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_holiday_lists', $data);
        return $this->db->insert_id();
    }

    public function delete_holiday_list($id)
    {
        // Remove child holidays first
        $this->db->where('holiday_list_id', (int) $id)->delete($this->p() . 'hr_public_holidays');
        $this->db->where('id', (int) $id)->delete($this->p() . 'hr_holiday_lists');
        return $this->db->affected_rows() > 0;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PUBLIC HOLIDAYS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_public_holidays($holiday_list_id)
    {
        return $this->db
            ->select('*')
            ->from($this->p() . 'hr_public_holidays')
            ->where('holiday_list_id', (int) $holiday_list_id)
            ->order_by('holiday_date', 'ASC')
            ->get()
            ->result();
    }

    public function save_public_holiday($data, $id = null)
    {
        $allowed = ['holiday_list_id', 'name', 'holiday_date', 'description', 'type'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_public_holidays', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_public_holidays', $data);
        return $this->db->insert_id();
    }

    public function delete_public_holiday($id)
    {
        $this->db->where('id', (int) $id)->delete($this->p() . 'hr_public_holidays');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Bulk-insert holidays from a normalised array (e.g. from Nager.Date API).
     * Each element must have at minimum: name, holiday_date.
     * Duplicate dates within the same list are skipped.
     */
    public function import_holidays_from_array($holiday_list_id, $holidays)
    {
        $existing = [];
        $rows     = $this->get_public_holidays($holiday_list_id);
        foreach ($rows as $r) {
            $existing[$r->holiday_date] = true;
        }

        $inserted = 0;
        foreach ($holidays as $h) {
            $date = isset($h['date']) ? $h['date'] : (isset($h['holiday_date']) ? $h['holiday_date'] : null);
            if (!$date || isset($existing[$date])) {
                continue;
            }
            $this->db->insert($this->p() . 'hr_public_holidays', [
                'holiday_list_id' => (int) $holiday_list_id,
                'name'            => $h['name'] ?? 'Holiday',
                'holiday_date'    => $date,
                'description'     => $h['localName'] ?? ($h['description'] ?? null),
                'type'            => $h['type'] ?? 'national',
                'date_created'    => date('Y-m-d H:i:s'),
            ]);
            $existing[$date] = true;
            $inserted++;
        }
        return $inserted;
    }

    /**
     * Return flat array of 'Y-m-d' date strings for all holidays attached to
     * the active (is_default=1 or company-specific) list for the given year.
     */
    public function get_active_holiday_dates($company_id, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // Prefer company-specific list, fall back to default
        $list = $this->db
            ->select('id')
            ->from($this->p() . 'hr_holiday_lists')
            ->where('year', $year)
            ->group_start()
                ->where('company_id', (int) $company_id)
                ->or_where('is_default', 1)
            ->group_end()
            ->order_by("FIELD(company_id, {$company_id}) DESC", null, false)
            ->limit(1)
            ->get()
            ->row();

        if (!$list) {
            return [];
        }

        $rows = $this->db
            ->select('holiday_date')
            ->from($this->p() . 'hr_public_holidays')
            ->where('holiday_list_id', (int) $list->id)
            ->get()
            ->result();

        return array_column(array_map(fn($r) => (array) $r, $rows), 'holiday_date');
    }

    /**
     * Count working days between two dates (inclusive).
     *
     * @param  string $date_from           'Y-m-d'
     * @param  string $date_to             'Y-m-d'
     * @param  int    $company_id
     * @param  bool   $include_weekends    If true, Sat/Sun count as working days
     * @param  bool   $include_public_holidays  If true, public holidays count too
     * @return float
     */
    public function calc_working_days($date_from, $date_to, $company_id, $include_weekends = false, $include_public_holidays = false)
    {
        $start = new DateTime($date_from);
        $end   = new DateTime($date_to);

        if ($end < $start) {
            return 0.0;
        }

        $holidays = [];
        if (!$include_public_holidays) {
            $year = $start->format('Y');
            $holidays = $this->get_active_holiday_dates($company_id, $year);
            // If date range spans year-end, also fetch next year
            if ($end->format('Y') !== $year) {
                $holidays = array_merge(
                    $holidays,
                    $this->get_active_holiday_dates($company_id, $end->format('Y'))
                );
            }
            $holidays = array_flip($holidays); // key lookup is O(1)
        }

        $days    = 0.0;
        $current = clone $start;

        while ($current <= $end) {
            $dow  = (int) $current->format('N'); // 1=Mon … 7=Sun
            $date = $current->format('Y-m-d');

            $is_weekend = ($dow >= 6); // 6=Sat, 7=Sun
            $is_holiday = isset($holidays[$date]);

            if (($include_weekends || !$is_weekend) && ($include_public_holidays || !$is_holiday)) {
                $days += 1.0;
            }

            $current->modify('+1 day');
        }

        return $days;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TOIL ENTRIES
    // ══════════════════════════════════════════════════════════════════════════

    public function get_toil_entries($employee_id, $status = null)
    {
        $this->db
            ->select('*')
            ->from($this->p() . 'hr_toil_entries')
            ->where('employee_id', (int) $employee_id);

        if ($status !== null) {
            $this->db->where('status', $status);
        }

        return $this->db->order_by('work_date', 'DESC')->get()->result();
    }

    public function save_toil_entry($data, $id = null)
    {
        $allowed = [
            'employee_id', 'company_id', 'work_date', 'hours_worked',
            'toil_hours_earned', 'reason', 'approved_by', 'status',
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_toil_entries', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_toil_entries', $data);
        return $this->db->insert_id();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENCASHMENTS
    // ══════════════════════════════════════════════════════════════════════════

    public function get_encashments($employee_id = null)
    {
        $this->db
            ->select('enc.*, CONCAT(e.first_name," ",e.last_name) AS employee_name, lt.name AS leave_type_name')
            ->from($this->p() . 'hr_leave_encashments enc')
            ->join($this->p() . 'hr_employees e', 'e.id = enc.employee_id', 'left')
            ->join($this->p() . 'hr_leave_types lt', 'lt.id = enc.leave_type_id', 'left');

        if ($employee_id !== null) {
            $this->db->where('enc.employee_id', (int) $employee_id);
        }

        return $this->db->order_by('enc.date_created', 'DESC')->get()->result();
    }

    public function save_encashment($data, $id = null)
    {
        $allowed = [
            'employee_id', 'leave_type_id', 'allocation_id', 'days_encashed',
            'amount', 'payslip_id', 'status', 'notes', 'created_by',
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int) $id)->update($this->p() . 'hr_leave_encashments', $data);
            return (int) $id;
        }
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_leave_encashments', $data);
        return $this->db->insert_id();
    }
}
