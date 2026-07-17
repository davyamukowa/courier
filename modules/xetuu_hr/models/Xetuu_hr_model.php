<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xetuu_hr_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function p()
    {
        return db_prefix();
    }

    private function next_employee_number()
    {
        $prefix = $this->get_setting('employee_number_prefix') ?: 'HR-EMP-';
        $digits = (int)($this->get_setting('employee_number_digits') ?: 5);

        $last = $this->db
            ->select_max('id')
            ->get($this->p() . 'hr_employees')
            ->row();

        $next = ($last && $last->id) ? (int)$last->id + 1 : 1;
        return $prefix . str_pad($next, $digits, '0', STR_PAD_LEFT);
    }

    public function get_setting($name)
    {
        $row = $this->db
            ->where('name', $name)
            ->get($this->p() . 'hr_settings')
            ->row();
        return $row ? $row->value : null;
    }

    public function get_all_settings()
    {
        $rows = $this->db->get($this->p() . 'hr_settings')->result();
        $out  = [];
        foreach ($rows as $r) { $out[$r->name] = $r->value; }
        return $out;
    }

    public function save_setting($name, $value)
    {
        $exists = $this->db->where('name', $name)->count_all_results($this->p() . 'hr_settings');
        if ($exists) {
            $this->db->where('name', $name)->update($this->p() . 'hr_settings', ['value' => $value]);
        } else {
            $this->db->insert($this->p() . 'hr_settings', ['name' => $name, 'value' => $value]);
        }
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function get_dashboard_stats()
    {
        $p = $this->p();

        $total = $this->db->count_all($p . 'hr_employees');

        $active = $this->db
            ->where('status', 'Active')
            ->where('active', 1)
            ->count_all_results($p . 'hr_employees');

        $current_month_start = date('Y-m-01');
        $new_hires = $this->db
            ->where('date_of_joining >=', $current_month_start)
            ->count_all_results($p . 'hr_employees');

        $open_jobs = $this->db
            ->where('status', 'Open')
            ->count_all_results($p . 'hr_job_openings');

        $pending_appraisals = $this->db
            ->where('status', 'Pending')
            ->count_all_results($p . 'hr_appraisals');

        $today = date('Y-m-d');
        $attendance_today = $this->db
            ->where('attendance_date', $today)
            ->count_all_results($p . 'hr_attendance');

        $pending_claims = $this->db
            ->where('status', 'Submitted')
            ->count_all_results($p . 'hr_expense_claims');

        $resignations = $this->db
            ->where('status', 'Resigned')
            ->where('date_of_joining >=', date('Y-m-01'))
            ->count_all_results($p . 'hr_employees');

        return [
            'total_employees'    => $total,
            'active_employees'   => $active,
            'new_hires'          => $new_hires,
            'resignations'       => $resignations,
            'open_jobs'          => $open_jobs,
            'pending_appraisals' => $pending_appraisals,
            'attendance_today'   => $attendance_today,
            'pending_claims'     => $pending_claims,
        ];
    }

    // ── Employees ─────────────────────────────────────────────────────────────

    public function get_employees($filters = [])
    {
        $p = $this->p();

        $this->db->select("
            e.*,
            CONCAT(e.first_name, ' ', e.last_name) AS full_name,
            d.name AS department_name,
            des.name AS designation_name,
            c.name AS company_name,
            cl.name AS client_name,
            r.first_name AS reports_to_first,
            r.last_name AS reports_to_last
        ");
        $this->db->from($p . 'hr_employees e');
        $this->db->join($p . 'hr_departments d',   'e.department_id = d.id', 'left');
        $this->db->join($p . 'hr_designations des', 'e.designation_id = des.id', 'left');
        $this->db->join($p . 'hr_companies c',      'e.company_id = c.id', 'left');
        $this->db->join($p . 'hr_clients cl',       'e.client_id = cl.id', 'left');
        $this->db->join($p . 'hr_employees r',      'e.reports_to = r.id', 'left');

        if (!empty($filters['status'])) {
            $this->db->where('e.status', $filters['status']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('e.department_id', $filters['department_id']);
        }
        if (!empty($filters['search'])) {
            $s = $this->db->escape_like_str($filters['search']);
            $this->db->group_start();
            $this->db->like('e.first_name', $s);
            $this->db->or_like('e.last_name', $s);
            $this->db->or_like('e.employee_number', $s);
            $this->db->or_like('e.company_email', $s);
            $this->db->group_end();
        }

        $this->db->where('e.active', 1);
        $this->db->order_by('e.id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_employee($id)
    {
        $p = $this->p();

        $this->db->select("
            e.*,
            CONCAT(e.first_name, ' ', e.last_name) AS full_name,
            d.name AS department_name,
            des.name AS designation_name,
            c.name AS company_name,
            cl.name AS client_name,
            CONCAT(r.first_name, ' ', r.last_name) AS reports_to_name,
            r.photo AS reports_to_photo
        ");
        $this->db->from($p . 'hr_employees e');
        $this->db->join($p . 'hr_departments d',   'e.department_id = d.id', 'left');
        $this->db->join($p . 'hr_designations des', 'e.designation_id = des.id', 'left');
        $this->db->join($p . 'hr_companies c',      'e.company_id = c.id', 'left');
        $this->db->join($p . 'hr_clients cl',       'e.client_id = cl.id', 'left');
        $this->db->join($p . 'hr_employees r',      'e.reports_to = r.id', 'left');
        $this->db->where('e.id', $id);

        $employee = $this->db->get()->row();

        if ($employee) {
            $employee->active_contract = $this->get_active_contract($id);
            $employee->addresses       = $this->get_employee_addresses($id);
            $employee->emergency_contacts = $this->get_emergency_contacts($id);
            $employee->bank_details    = $this->get_bank_details($id);
        }

        return $employee;
    }

    public function add_employee($data)
    {
        $data['employee_number'] = $this->next_employee_number();
        $data['date_created']    = date('Y-m-d H:i:s');
        $data['active']          = 1;

        $allowed = [
            'employee_number','staff_id','salutation','first_name','middle_name','last_name',
            'gender','dob','company_id','client_id','branch_id','department_id','designation_id',
            'employee_group_id','grade_id','employment_type','reports_to','date_of_joining',
            'mobile','personal_email','company_email','preferred_email','nationality',
            'status','date_created','active','photo','biography',
        ];
        $insert = array_intersect_key($data, array_flip($allowed));
        $insert['date_created'] = date('Y-m-d H:i:s');

        $this->db->insert($this->p() . 'hr_employees', $insert);
        return $this->db->insert_id();
    }

    public function update_employee($id, $data)
    {
        $allowed = [
            'salutation','first_name','middle_name','last_name','gender','dob',
            'company_id','client_id','branch_id','department_id','designation_id',
            'employee_group_id','grade_id','employment_type','reports_to','date_of_joining',
            'mobile','personal_email','company_email','preferred_email','nationality',
            'marital_status','blood_group','religion','disability_status',
            'social_sec_number','health_fund_number','tax_id','passport_number','passport_expiry',
            'attendance_device_id','rfid_number','biometric_id','default_shift',
            'leave_approver','expense_approver','shift_approver',
            'salary_currency','salary_mode','status','photo','biography',
        ];
        $update = array_intersect_key($data, array_flip($allowed));
        $update['date_modified'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        return $this->db->update($this->p() . 'hr_employees', $update);
    }

    // ── Employee sub-records ──────────────────────────────────────────────────

    public function get_active_contract($employee_id)
    {
        return $this->db
            ->select('*, salary AS monthly_salary, salary * 12 AS annual_cost, \'KES\' AS currency', false)
            ->where('employee_id', $employee_id)
            ->where('status', 'Active')
            ->order_by('start_date', 'DESC')
            ->limit(1)
            ->get($this->p() . 'hr_contracts')
            ->row();
    }

    public function save_hr_contract($employee_id, $data)
    {
        $p = $this->p();
        $existing = $this->db->where('employee_id', $employee_id)->where('status', 'Active')->order_by('id', 'DESC')->limit(1)->get($p . 'hr_contracts')->row();
        $row = [
            'employee_id'   => $employee_id,
            'company_id'    => (int)($data['company_id'] ?? 0),
            'contract_type' => $data['contract_type'] ?? 'Permanent',
            'salary'        => (float)($data['monthly_salary'] ?? 0),
            'start_date'    => $data['contract_start'] ?: date('Y-m-01'),
            'end_date'      => !empty($data['contract_end']) ? $data['contract_end'] : null,
            'status'        => 'Active',
        ];
        if ($existing) {
            $this->db->where('id', $existing->id)->update($p . 'hr_contracts', $row);
        } else {
            $row['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert($p . 'hr_contracts', $row);
        }
    }

    public function get_employee_addresses($employee_id)
    {
        return $this->db
            ->where('employee_id', $employee_id)
            ->get($this->p() . 'hr_employee_addresses')
            ->result();
    }

    public function get_emergency_contacts($employee_id)
    {
        return $this->db
            ->where('employee_id', $employee_id)
            ->get($this->p() . 'hr_emergency_contacts')
            ->result();
    }

    public function get_bank_details($employee_id)
    {
        return $this->db
            ->where('employee_id', $employee_id)
            ->get($this->p() . 'hr_bank_details')
            ->result();
    }

    // ── Org chart ─────────────────────────────────────────────────────────────

    public function get_org_tree()
    {
        $p = $this->p();
        $employees = $this->db
            ->select('e.id, e.first_name, e.last_name, e.reports_to, e.photo, e.employee_number,
                      d.name AS designation_name, dep.name AS department_name')
            ->from($p . 'hr_employees e')
            ->join($p . 'hr_designations d',  'e.designation_id = d.id', 'left')
            ->join($p . 'hr_departments dep',  'e.department_id = dep.id', 'left')
            ->where('e.active', 1)
            ->order_by('e.first_name', 'ASC')
            ->get()
            ->result();

        $map = [];
        foreach ($employees as $e) {
            $map[$e->id] = $e;
            $e->children = [];
        }

        $roots = [];
        foreach ($map as $e) {
            if ($e->reports_to && isset($map[$e->reports_to])) {
                $map[$e->reports_to]->children[] = $e;
            } else {
                $roots[] = $e;
            }
        }

        return $roots;
    }

    // ── Recruitment ───────────────────────────────────────────────────────────

    public function get_job_openings()
    {
        return $this->db
            ->where('status', 'Open')
            ->get($this->p() . 'hr_job_openings')
            ->result();
    }

    public function get_applicants($opening_id = null)
    {
        if ($opening_id) {
            $this->db->where('job_opening_id', $opening_id);
        }
        return $this->db
            ->order_by('date_created', 'DESC')
            ->get($this->p() . 'hr_applicants')
            ->result();
    }

    public function get_recruitment_stats()
    {
        $p = $this->p();
        
        // Basic counts
        $open_positions = $this->db->where('status', 'Open')->count_all_results($p . 'hr_job_openings');
        $applicants = $this->db->count_all($p . 'hr_applicants');
        
        // Funnel stages (cumulative)
        $funnel_sourced = $applicants;
        $funnel_screened = $this->db->where_in('stage', ['Screening', 'Interview', 'Offer', 'Hired'])->count_all_results($p . 'hr_applicants');
        $funnel_interviewed = $this->db->where_in('stage', ['Interview', 'Offer', 'Hired'])->count_all_results($p . 'hr_applicants');
        $funnel_offered = $this->db->where_in('stage', ['Offer', 'Hired'])->count_all_results($p . 'hr_applicants');
        $funnel_hired = $this->db->where('stage', 'Hired')->count_all_results($p . 'hr_applicants');
        
        // Rates
        $screening_to_interview_rate = $funnel_screened > 0 ? round(($funnel_interviewed / $funnel_screened) * 100) : 0;
        $interview_to_offer_rate = $funnel_interviewed > 0 ? round(($funnel_offered / $funnel_interviewed) * 100) : 0;

        // Avg. Time to Hire (days between application creation and employee hired)
        $avg_time_to_hire = 15;
        $time_to_hire_query = $this->db->query("
            SELECT AVG(DATEDIFF(al.date_created, a.date_created)) AS avg_days 
            FROM `{$p}hr_appointment_letters` al 
            JOIN `{$p}hr_applicants` a ON al.applicant_id = a.id 
            WHERE al.status = 'Signed'
        ")->row();
        if ($time_to_hire_query && $time_to_hire_query->avg_days !== null) {
            $avg_time_to_hire = round($time_to_hire_query->avg_days);
        }

        // Offer decline rate (Rejected offers / Total offers)
        $offer_decline_rate = 0.0;
        $total_offers = $this->db->count_all($p . 'hr_job_offers');
        if ($total_offers > 0) {
            $rejected_offers = $this->db->where('status', 'Rejected')->count_all_results($p . 'hr_job_offers');
            $offer_decline_rate = round(($rejected_offers / $total_offers) * 100, 1);
        }

        // Active openings list
        $this->db->select('jo.*, d.name AS department_name, des.name AS designation_name');
        $this->db->from($p . 'hr_job_openings jo');
        $this->db->join($p . 'hr_departments d', 'jo.department_id = d.id', 'left');
        $this->db->join($p . 'hr_designations des', 'jo.designation_id = des.id', 'left');
        $this->db->where('jo.status', 'Open');
        $this->db->order_by('jo.id', 'DESC');
        $this->db->limit(5);
        $active_openings_list = $this->db->get()->result();

        foreach ($active_openings_list as &$jo) {
            $jo->applicant_count = $this->db->where('job_opening_id', $jo->id)->count_all_results($p . 'hr_applicants');
        }

        // Dept count
        $dept_count = $this->db->select('department_id')->where('status', 'Open')->group_by('department_id')->get($p . 'hr_job_openings')->num_rows();

        // Upcoming interviews (next 5)
        $this->db->select('i.interview_date, i.from_time, CONCAT(a.first_name," ",a.last_name) AS applicant_name, jo.title AS opening_title');
        $this->db->from($p . 'hr_interviews i');
        $this->db->join($p . 'hr_applicants a', 'i.applicant_id = a.id', 'left');
        $this->db->join($p . 'hr_job_openings jo', 'i.job_opening_id = jo.id', 'left');
        $this->db->where('i.status', 'Scheduled');
        $this->db->where('(i.interview_date >= CURDATE() OR i.interview_date IS NULL)');
        $this->db->order_by('i.interview_date', 'ASC');
        $this->db->limit(5);
        $upcoming_interviews = $this->db->get()->result();

        return [
            'open_positions'              => $open_positions,
            'applicants'                  => $applicants,
            'funnel_sourced'              => $funnel_sourced,
            'funnel_screened'             => $funnel_screened,
            'funnel_interviewed'          => $funnel_interviewed,
            'funnel_offered'              => $funnel_offered,
            'funnel_hired'                => $funnel_hired,
            'screening_to_interview_rate' => $screening_to_interview_rate,
            'interview_to_offer_rate'     => $interview_to_offer_rate,
            'avg_time_to_hire'            => $avg_time_to_hire,
            'offer_decline_rate'          => $offer_decline_rate,
            'active_openings_list'        => $active_openings_list,
            'dept_count'                  => $dept_count,
            'upcoming_interviews'         => $upcoming_interviews,
        ];
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function get_attendance_today_stats()
    {
        $today = date('Y-m-d');
        $p = $this->p();

        return [
            'present' => $this->db->where('attendance_date', $today)->where('status', 'Present')->count_all_results($p . 'hr_attendance'),
            'absent'  => $this->db->where('attendance_date', $today)->where('status', 'Absent')->count_all_results($p . 'hr_attendance'),
            'on_leave'=> $this->db->where('attendance_date', $today)->where('status', 'On Leave')->count_all_results($p . 'hr_attendance'),
        ];
    }

    // ── Performance ───────────────────────────────────────────────────────────

    public function get_appraisals($filters = [])
    {
        $p = $this->p();
        $this->db->select("a.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name, c.name AS cycle_name");
        $this->db->from($p . 'hr_appraisals a');
        $this->db->join($p . 'hr_employees e', 'a.employee_id = e.id', 'left');
        $this->db->join($p . 'hr_appraisal_cycles c', 'a.cycle_id = c.id', 'left');
        if (!empty($filters['status'])) {
            $this->db->where('a.status', $filters['status']);
        }
        return $this->db->get()->result();
    }

    // ── Expenses ──────────────────────────────────────────────────────────────

    public function get_expense_claims($filters = [])
    {
        $p = $this->p();
        $this->db->select("ec.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name");
        $this->db->from($p . 'hr_expense_claims ec');
        $this->db->join($p . 'hr_employees e', 'ec.employee_id = e.id', 'left');
        if (!empty($filters['status'])) {
            $this->db->where('ec.status', $filters['status']);
        }
        return $this->db->get()->result();
    }

    // ── Tenure ────────────────────────────────────────────────────────────────

    public function get_onboarding_list()
    {
        $p = $this->p();
        $this->db->select("o.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name");
        $this->db->from($p . 'hr_onboarding o');
        $this->db->join($p . 'hr_employees e', 'o.employee_id = e.id', 'left');
        return $this->db->get()->result();
    }

    public function get_exits()
    {
        $p = $this->p();
        $this->db->select("x.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name");
        $this->db->from($p . 'hr_employee_exits x');
        $this->db->join($p . 'hr_employees e', 'x.employee_id = e.id', 'left');
        return $this->db->get()->result();
    }

    public function get_grievances()
    {
        $p = $this->p();
        $this->db->select("g.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name");
        $this->db->from($p . 'hr_grievances g');
        $this->db->join($p . 'hr_employees e', 'g.employee_id = e.id', 'left');
        return $this->db->get()->result();
    }

    // ── Lookup lists ─────────────────────────────────────────────────────────

    public function get_departments($company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        return $this->db->where('active', 1)->get($this->p() . 'hr_departments')->result();
    }

    public function get_designations()
    {
        return $this->db->get($this->p() . 'hr_designations')->result();
    }

    public function get_companies()
    {
        return $this->db->where('active', 1)->get($this->p() . 'hr_companies')->result();
    }

    public function get_clients()
    {
        return $this->db->where('active', 1)->get($this->p() . 'hr_clients')->result();
    }

    public function get_grades()
    {
        return $this->db->get($this->p() . 'hr_employee_grades')->result();
    }

    public function get_employee_groups()
    {
        return $this->db->get($this->p() . 'hr_employee_groups')->result();
    }

    public function get_branches($company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        return $this->db->where('active', 1)->get($this->p() . 'hr_branches')->result();
    }

    public function get_all_employees_simple()
    {
        return $this->db
            ->select('id, employee_number, first_name, last_name, designation_id')
            ->where('active', 1)
            ->order_by('first_name', 'ASC')
            ->get($this->p() . 'hr_employees')
            ->result();
    }

    // ── Department (full form) ────────────────────────────────────────────────

    public function get_department($id)
    {
        $p = $this->p();
        $this->db->select('d.*, c.name AS company_name, p.name AS parent_name,
            CONCAT(s.firstname," ",s.lastname) AS manager_name');
        $this->db->from($p . 'hr_departments d');
        $this->db->join($p . 'hr_companies c',    'd.company_id = c.id', 'left');
        $this->db->join($p . 'hr_departments p',  'd.parent_id = p.id', 'left');
        $this->db->join('tblstaff s',              'd.manager_id = s.staffid', 'left');
        $this->db->where('d.id', $id);
        return $this->db->get()->row();
    }

    public function dept_insert($data, $approvers = [])
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_departments', $data);
        $id = $this->db->insert_id();
        if ($id) { $this->_save_dept_approvers($id, $approvers); }
        return $id;
    }

    public function dept_update($id, $data, $approvers = [])
    {
        $this->db->where('id', $id)->update($this->p() . 'hr_departments', $data);
        $this->_save_dept_approvers($id, $approvers);
    }

    private function _save_dept_approvers($dept_id, $approvers)
    {
        $p = $this->p();
        $this->db->where('dept_id', $dept_id)->delete($p . 'hr_dept_approvers');
        foreach ($approvers as $type => $ids) {
            foreach ($ids as $staff_id) {
                $staff_id = (int)$staff_id;
                if ($staff_id <= 0) continue;
                $this->db->insert($p . 'hr_dept_approvers', [
                    'dept_id'    => $dept_id,
                    'type'       => $type,
                    'approver_id'=> $staff_id,
                ]);
            }
        }
    }

    public function get_dept_approvers($dept_id)
    {
        $p = $this->p();
        $this->db->select('a.*, CONCAT(s.firstname," ",s.lastname) AS approver_name');
        $this->db->from($p . 'hr_dept_approvers a');
        $this->db->join('tblstaff s', 'a.approver_id = s.staffid', 'left');
        $this->db->where('a.dept_id', $dept_id);
        $rows = $this->db->get()->result();
        $out  = ['shift_request' => [], 'leave' => [], 'expense' => []];
        foreach ($rows as $r) {
            if (isset($out[$r->type])) { $out[$r->type][] = $r; }
        }
        return $out;
    }

    public function get_dept_employee_counts()
    {
        $rows = $this->db
            ->select('department_id, COUNT(*) AS cnt')
            ->where('active', 1)
            ->group_by('department_id')
            ->get($this->p() . 'hr_employees')
            ->result();
        $out = [];
        foreach ($rows as $r) { $out[$r->department_id] = (int)$r->cnt; }
        return $out;
    }

    public function get_all_staff()
    {
        if (!class_exists('staff_model', false)) {
            get_instance()->load->model('staff_model');
        }
        return get_instance()->staff_model->get();
    }

    public function get_analytic_accounts()
    {
        if (!$this->db->table_exists('acc_analytic_accounts')) {
            return [];
        }
        return $this->db
            ->select('a.id, a.name, a.code, p.name AS plan_name')
            ->from('acc_analytic_accounts a')
            ->join('acc_analytic_plans p', 'a.plan_id = p.id', 'left')
            ->where('a.active', 1)
            ->order_by('p.name', 'ASC')
            ->order_by('a.name', 'ASC')
            ->get()
            ->result();
    }

    public function get_advance_accounts()
    {
        if (!$this->db->table_exists('acc_accounts')) {
            return [];
        }
        return $this->db
            ->select('id, code, name, type')
            ->where('is_group', 0)
            ->where_in('type', ['Current Asset', 'Receivable'])
            ->order_by('code', 'ASC')
            ->get('acc_accounts')
            ->result();
    }

    public function get_shifts()
    {
        if (!$this->db->table_exists('tblxhr_shifts')) {
            return [];
        }
        return $this->db
            ->select('id, name, start_time, end_time')
            ->where('active', 1)
            ->order_by('name', 'ASC')
            ->get('tblxhr_shifts')
            ->result();
    }

    public function run_seeds()
    {
        require_once FCPATH . 'modules/xetuu_hr/data/seeds.php';
    }

    // ── Setup CRUD ────────────────────────────────────────────────────────────

    private function _setup_table($sub)
    {
        $map = [
            'company'                      => 'hr_companies',
            'branch'                       => 'hr_branches',
            'department'                   => 'hr_departments',
            'designation'                  => 'hr_designations',
            'employee_group'               => 'hr_employee_groups',
            'employee_grade'               => 'hr_employee_grades',
            'staffing_plan'                => 'hr_staffing_plans',
            'job_requisition'              => 'hr_job_requisitions',
            'interview_type'               => 'hr_interview_types',
            'interview_round'              => 'hr_interview_rounds',
            'appointment_letter_template'  => 'hr_appointment_letter_templates',
        ];
        return $this->p() . ($map[$sub] ?? $sub);
    }

    public function setup_get($sub, $id)
    {
        return $this->db->where('id', $id)->get($this->_setup_table($sub))->row();
    }

    public function setup_list($table_suffix)
    {
        return $this->db
            ->order_by('id', 'ASC')
            ->get($this->p() . $table_suffix)
            ->result();
    }

    public function setup_branches_list()
    {
        $p = $this->p();
        $this->db->select('b.*, c.name AS company_name');
        $this->db->from($p . 'hr_branches b');
        $this->db->join($p . 'hr_companies c', 'b.company_id = c.id', 'left');
        $this->db->order_by('b.id', 'ASC');
        return $this->db->get()->result();
    }

    public function setup_departments_list()
    {
        $p = $this->p();
        $this->db->select('d.*, c.name AS company_name, p.name AS parent_name,
            CONCAT(s.firstname," ",s.lastname) AS manager_name');
        $this->db->from($p . 'hr_departments d');
        $this->db->join($p . 'hr_companies c',    'd.company_id = c.id', 'left');
        $this->db->join($p . 'hr_departments p',  'd.parent_id = p.id', 'left');
        $this->db->join('tblstaff s',              'd.manager_id = s.staffid', 'left');
        $this->db->order_by('d.company_id', 'ASC');
        $this->db->order_by('d.name', 'ASC');
        return $this->db->get()->result();
    }

    public function setup_insert($sub, $data)
    {
        $table = $this->_setup_table($sub);
        if ($this->db->field_exists('date_created', $table)) {
            $data['date_created'] = date('Y-m-d H:i:s');
        }
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function setup_update($sub, $id, $data)
    {
        $table = $this->_setup_table($sub);
        unset($data['date_created']);
        $this->db->where('id', $id);
        return $this->db->update($table, $data);
    }

    public function setup_delete($sub, $id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->_setup_table($sub));
    }


    public function save_settings($data)
    {
        foreach ($data as $name => $value) {
            $exists = $this->db->where('name', $name)->count_all_results($this->p() . 'hr_settings');
            if ($exists) {
                $this->db->where('name', $name)->update($this->p() . 'hr_settings', ['value' => $value]);
            } else {
                $this->db->insert($this->p() . 'hr_settings', ['name' => $name, 'value' => $value]);
            }
        }
    }

    public function preview_next_employee_number()
    {
        $prefix = $this->get_setting('employee_number_prefix') ?: 'HR-EMP-';
        $digits = (int)($this->get_setting('employee_number_digits') ?: 5);
        $last   = $this->db->select_max('id')->get($this->p() . 'hr_employees')->row();
        $next   = ($last && $last->id) ? (int)$last->id + 1 : 1;
        return $prefix . str_pad($next, $digits, '0', STR_PAD_LEFT);
    }
}
