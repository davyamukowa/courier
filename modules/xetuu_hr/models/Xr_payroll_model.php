<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xr_payroll_model extends App_Model
{
    private function p() { return db_prefix(); }

    // ── Payroll Companies ──────────────────────────────────────────────────────

    public function get_payroll_companies($active_only = true)
    {
        $p = $this->p();
        $this->db->select('pc.*, cl.company AS client_name, cl.vat AS client_reg, cl.country AS client_country')
                 ->from($p . 'hr_payroll_companies pc')
                 ->join('clients cl', 'cl.userid = pc.client_id', 'left');
        if ($active_only) $this->db->where('pc.active', 1);
        return $this->db->order_by('pc.name')->get()->result();
    }

    public function get_payroll_company($id)
    {
        $p = $this->p();
        return $this->db->select('pc.*, cl.company AS client_name, cl.vat AS client_reg, cl.phonenumber AS client_phone, cl.email AS client_email')
                        ->from($p . 'hr_payroll_companies pc')
                        ->join('clients cl', 'cl.userid = pc.client_id', 'left')
                        ->where('pc.id', (int) $id)
                        ->get()->row();
    }

    public function save_payroll_company($data, $id = null)
    {
        $allowed = ['client_id','name','reg_number','country_code','currency',
                    'payroll_addon_id','tax_reg_number','social_sec_number','health_fund_number',
                    'xetuu_books_company_id','settings','active'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id', (int)$id)->update($this->p().'hr_payroll_companies', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_payroll_companies', $row);
        return $this->db->insert_id();
    }

    public function delete_payroll_company($id)
    {
        $this->db->where('id',(int)$id)->update($this->p().'hr_payroll_companies', ['active'=>0]);
    }

    // ── Pay Frequencies ────────────────────────────────────────────────────────

    public function get_pay_frequencies($company_id = 0)
    {
        return $this->db->where_in('company_id', [0, (int)$company_id])
                        ->where('active', 1)
                        ->order_by('name')
                        ->get($this->p().'hr_pay_frequencies')
                        ->result();
    }

    public function get_pay_frequency($id)
    {
        return $this->db->where('id',(int)$id)->get($this->p().'hr_pay_frequencies')->row();
    }

    public function save_pay_frequency($data, $id = null)
    {
        $allowed = ['company_id','name','interval_type','interval_count','pay_day','active'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_pay_frequencies', $row);
            return $id;
        }
        $this->db->insert($this->p().'hr_pay_frequencies', $row);
        return $this->db->insert_id();
    }

    public function delete_pay_frequency($id)
    {
        $this->db->where('id',(int)$id)->delete($this->p().'hr_pay_frequencies');
    }

    // ── Salary Structures ──────────────────────────────────────────────────────

    public function get_salary_structures($company_id = null)
    {
        $p = $this->p();
        $this->db->select('ss.*, pf.name AS frequency_name')
                 ->from($p.'hr_salary_structures ss')
                 ->join($p.'hr_pay_frequencies pf', 'pf.id = ss.pay_frequency_id', 'left')
                 ->where('ss.active', 1);
        if ($company_id !== null) $this->db->where_in('ss.company_id', [0, (int)$company_id]);
        return $this->db->order_by('ss.name')->get()->result();
    }

    public function get_salary_structure($id)
    {
        $p = $this->p();
        return $this->db->select('ss.*, pf.name AS frequency_name, pf.interval_type')
                        ->from($p.'hr_salary_structures ss')
                        ->join($p.'hr_pay_frequencies pf', 'pf.id = ss.pay_frequency_id', 'left')
                        ->where('ss.id', (int)$id)->get()->row();
    }

    public function save_salary_structure($data, $id = null)
    {
        $allowed = ['company_id','name','code','pay_frequency_id','description','active'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_salary_structures', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_salary_structures', $row);
        return $this->db->insert_id();
    }

    // ── Salary Rules ───────────────────────────────────────────────────────────

    public function get_salary_rules($structure_id)
    {
        return $this->db->where('structure_id',(int)$structure_id)
                        ->where('active', 1)
                        ->order_by('sequence')
                        ->get($this->p().'hr_salary_rules')
                        ->result();
    }

    public function get_salary_rule($id)
    {
        return $this->db->where('id',(int)$id)->get($this->p().'hr_salary_rules')->row();
    }

    public function save_salary_rule($data, $id = null)
    {
        $allowed = ['structure_id','code','name','sequence','category',
                    'condition_formula','amount_formula','appears_on_payslip',
                    'is_addon_rule','addon_id','active'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_salary_rules', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_salary_rules', $row);
        return $this->db->insert_id();
    }

    public function delete_salary_rule($id)
    {
        $this->db->where('id',(int)$id)->update($this->p().'hr_salary_rules', ['active'=>0]);
    }

    // ── Payroll Contracts ──────────────────────────────────────────────────────

    public function get_active_payroll_contract($employee_id)
    {
        $p = $this->p();
        $contract = $this->db->select('pc.*, pc.wage AS basic_salary, pc.structure_id AS salary_structure_id, ss.name AS structure_name, pf.name AS frequency_name, pf.interval_type')
                             ->from($p.'hr_payroll_contracts pc')
                             ->join($p.'hr_salary_structures ss', 'ss.id = pc.structure_id', 'left')
                             ->join($p.'hr_pay_frequencies pf', 'pf.id = pc.pay_frequency_id', 'left')
                             ->where('pc.employee_id', (int)$employee_id)
                             ->where('pc.status', 'active')
                             ->order_by('pc.date_start', 'DESC')
                             ->limit(1)
                             ->get()->row();
        if ($contract) {
            $contract->lines = $this->get_contract_lines($contract->id);
        }
        return $contract;
    }

    public function get_payroll_contract($id)
    {
        $p = $this->p();
        $contract = $this->db->select('pc.*, pc.wage AS basic_salary, pc.structure_id AS salary_structure_id, ss.name AS structure_name, pf.name AS frequency_name')
                             ->from($p.'hr_payroll_contracts pc')
                             ->join($p.'hr_salary_structures ss', 'ss.id = pc.structure_id', 'left')
                             ->join($p.'hr_pay_frequencies pf', 'pf.id = pc.pay_frequency_id', 'left')
                             ->where('pc.id', (int)$id)->get()->row();
        if ($contract) {
            $contract->lines = $this->get_contract_lines($id);
        }
        return $contract;
    }

    public function get_employee_contracts($employee_id)
    {
        $p = $this->p();
        return $this->db->select('pc.*, ss.name AS structure_name, pf.name AS frequency_name')
                        ->from($p.'hr_payroll_contracts pc')
                        ->join($p.'hr_salary_structures ss', 'ss.id = pc.structure_id', 'left')
                        ->join($p.'hr_pay_frequencies pf', 'pf.id = pc.pay_frequency_id', 'left')
                        ->where('pc.employee_id', (int)$employee_id)
                        ->order_by('pc.date_start', 'DESC')
                        ->get()->result();
    }

    public function save_payroll_contract($data, $id = null)
    {
        $allowed = ['employee_id','company_id','wage','pay_frequency_id','structure_id',
                    'employment_type','date_start','date_end','status',
                    'payment_method','bank_name','bank_account','bank_branch','mpesa_number',
                    'tax_id','working_days','notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            // deactivate old active contracts when saving a new active one
            if (($row['status'] ?? '') === 'active') {
                $this->db->where('employee_id', $row['employee_id'] ?? 0)
                         ->where('status', 'active')
                         ->where('id !=', (int)$id)
                         ->update($this->p().'hr_payroll_contracts', ['status'=>'expired']);
            }
            $this->db->where('id',(int)$id)->update($this->p().'hr_payroll_contracts', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        if (($row['status'] ?? '') === 'active') {
            $this->db->where('employee_id', $row['employee_id'] ?? 0)
                     ->where('status', 'active')
                     ->update($this->p().'hr_payroll_contracts', ['status'=>'expired']);
        }
        $this->db->insert($this->p().'hr_payroll_contracts', $row);
        return $this->db->insert_id();
    }

    // ── Contract Lines ─────────────────────────────────────────────────────────

    public function get_contract_lines($contract_id, $type = null)
    {
        $q = $this->db->where('contract_id',(int)$contract_id)->where('active',1);
        if ($type) $q->where('line_type', $type);
        return $q->order_by('line_type,name')->get($this->p().'hr_contract_lines')->result();
    }

    public function save_contract_line($data, $id = null)
    {
        $allowed = ['contract_id','line_type','name','code','amount','is_recurring','statutory_ref','notes','active'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_contract_lines', $row);
            return $id;
        }
        $this->db->insert($this->p().'hr_contract_lines', $row);
        return $this->db->insert_id();
    }

    public function delete_contract_line($id)
    {
        $this->db->where('id',(int)$id)->update($this->p().'hr_contract_lines', ['active'=>0]);
    }

    public function sync_contract_lines($contract_id, array $benefits, array $deductions)
    {
        $p = $this->p();
        $this->db->where('contract_id',(int)$contract_id)->delete($p.'hr_contract_lines');
        foreach ($benefits as $b) {
            if (empty($b['name']) || !isset($b['amount'])) continue;
            $this->db->insert($p.'hr_contract_lines', [
                'contract_id'   => $contract_id,
                'line_type'     => 'benefit',
                'name'          => $b['name'],
                'code'          => strtoupper($b['code'] ?? substr($b['name'],0,6)),
                'amount'        => (float)$b['amount'],
                'is_recurring'  => 1,
                'statutory_ref' => $b['statutory_ref'] ?? null,
                'active'        => 1,
            ]);
        }
        foreach ($deductions as $d) {
            if (empty($d['name']) || !isset($d['amount'])) continue;
            $this->db->insert($p.'hr_contract_lines', [
                'contract_id'   => $contract_id,
                'line_type'     => 'deduction',
                'name'          => $d['name'],
                'code'          => strtoupper($d['code'] ?? substr($d['name'],0,6)),
                'amount'        => (float)$d['amount'],
                'is_recurring'  => 1,
                'statutory_ref' => $d['statutory_ref'] ?? null,
                'active'        => 1,
            ]);
        }
    }

    // ── Payroll Periods ────────────────────────────────────────────────────────

    public function get_payroll_periods($company_id = 0)
    {
        return $this->db->where_in('company_id', [0, (int)$company_id])
                        ->order_by('date_from', 'DESC')
                        ->get($this->p().'hr_payroll_periods')
                        ->result();
    }

    public function get_open_periods($company_id = 0)
    {
        return $this->db->where_in('company_id', [0, (int)$company_id])
                        ->where('status','open')
                        ->order_by('date_from','DESC')
                        ->get($this->p().'hr_payroll_periods')
                        ->result();
    }

    public function save_payroll_period($data, $id = null)
    {
        $allowed = ['company_id','name','pay_frequency_id','date_from','date_to','status'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_payroll_periods', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_payroll_periods', $row);
        return $this->db->insert_id();
    }

    // ── Payroll Runs (Batches) ─────────────────────────────────────────────────

    public function get_batch_candidates($payroll_company_id, $department_id = null)
    {
        $p = $this->p();
        $this->db->select('pc.employee_id, pc.id AS contract_id, pc.structure_id, pc.company_id, pc.wage,
                           CONCAT(e.first_name," ",e.last_name) AS employee_name,
                           e.employee_number, e.department_id,
                           d.name AS department_name, des.name AS designation_name', false)
                 ->from($p.'hr_payroll_contracts pc')
                 ->join($p.'hr_employees e',     'e.id = pc.employee_id',       'left')
                 ->join($p.'hr_departments d',   'd.id = e.department_id',       'left')
                 ->join($p.'hr_designations des','des.id = e.designation_id',    'left')
                 ->where('pc.status', 'active')
                 ->where('pc.company_id', (int)$payroll_company_id);
        if ($department_id) {
            $this->db->where('e.department_id', (int)$department_id);
        }
        return $this->db->order_by('d.name, e.first_name')->get()->result();
    }

    public function get_payroll_runs($company_id = null, $limit = 50)
    {
        $p = $this->p();
        $this->db->select('pr.*, pc.name AS company_name, pc.currency')
                 ->from($p.'hr_payroll_runs pr')
                 ->join($p.'hr_payroll_companies pc', 'pc.id = pr.company_id', 'left');
        if ($company_id) $this->db->where('pr.company_id', (int)$company_id);
        return $this->db->order_by('pr.date_created','DESC')->limit($limit)->get()->result();
    }

    public function get_payroll_run($id)
    {
        $p = $this->p();
        return $this->db->select('pr.*, pc.name AS company_name, pc.currency, pc.country_code, pc.client_id')
                        ->from($p.'hr_payroll_runs pr')
                        ->join($p.'hr_payroll_companies pc', 'pc.id = pr.company_id', 'left')
                        ->where('pr.id',(int)$id)->get()->row();
    }

    public function save_payroll_run($data, $id = null)
    {
        $allowed = ['company_id','period_id','name','date_from','date_to','state',
                    'employee_count','computed_count','computing_chunk','computing_started_at',
                    'locked_by','total_gross','total_net','total_deductions','total_employer',
                    'journal_entry_id','payment_journal_id','notes',
                    'created_by','confirmed_by','paid_by','confirmed_at','paid_at'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_payroll_runs', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_payroll_runs', $row);
        return $this->db->insert_id();
    }

    // ── Payslips ───────────────────────────────────────────────────────────────

    public function get_payslips($filters = [], $limit = 50)
    {
        $p = $this->p();
        $this->db->select('ps.*, ps.gross_wage AS gross_salary, ps.net_wage AS net_salary,
                           CONCAT(e.first_name," ",e.last_name) AS employee_name,
                           e.employee_number, pc.name AS company_name, pc.currency')
                 ->from($p.'hr_payslips ps')
                 ->join($p.'hr_employees e', 'e.id = ps.employee_id', 'left')
                 ->join($p.'hr_payroll_companies pc', 'pc.id = ps.company_id', 'left');
        if (!empty($filters['company_id'])) $this->db->where('ps.company_id', (int)$filters['company_id']);
        if (!empty($filters['run_id']))     $this->db->where('ps.run_id', (int)$filters['run_id']);
        if (!empty($filters['employee_id'])) $this->db->where('ps.employee_id', (int)$filters['employee_id']);
        if (!empty($filters['state']))      $this->db->where('ps.state', $filters['state']);
        return $this->db->order_by('ps.date_created','DESC')->limit($limit)->get()->result();
    }

    public function get_payslip($id)
    {
        $p = $this->p();
        $slip = $this->db->select('ps.*, ps.gross_wage AS gross_salary, ps.net_wage AS net_salary,
                                   ps.cash_payable,
                                   CONCAT(e.first_name," ",e.last_name) AS employee_name,
                                   e.employee_number, e.company_email, e.personal_email, e.preferred_email,
                                   e.social_sec_number, e.health_fund_number, e.tax_id AS employee_tax_id,
                                   e.passport_number, e.passport_expiry,
                                   pco.name AS company_name, pco.currency, pco.country_code,
                                   ss.name AS structure_name,
                                   con.payment_method, con.bank_account, con.tax_id')
                         ->from($p.'hr_payslips ps')
                         ->join($p.'hr_employees e', 'e.id = ps.employee_id', 'left')
                         ->join($p.'hr_payroll_companies pco', 'pco.id = ps.company_id', 'left')
                         ->join($p.'hr_salary_structures ss', 'ss.id = ps.structure_id', 'left')
                         ->join($p.'hr_payroll_contracts con', 'con.id = ps.contract_id', 'left')
                         ->where('ps.id', (int)$id)->get()->row();
        if ($slip) {
            $slip->lines = $this->get_payslip_lines($id);
        }
        return $slip;
    }

    public function save_payslip($data, $id = null)
    {
        $allowed = ['run_id','company_id','employee_id','contract_id','structure_id','period_id',
                    'date_from','date_to','state','gross_wage','net_wage','cash_payable',
                    'total_deductions','total_employer','total_tax','working_days','worked_days',
                    'email_sent','email_sent_at','pdf_path','notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_payslips', $row);
            return $id;
        }
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p().'hr_payslips', $row);
        return $this->db->insert_id();
    }

    // ── Payslip Lines ──────────────────────────────────────────────────────────

    public function get_payslip_lines($payslip_id, $visible_only = false)
    {
        $q = $this->db->where('payslip_id',(int)$payslip_id);
        if ($visible_only) $q->where('appears_on_payslip', 1);
        return $q->order_by('sequence')->get($this->p().'hr_payslip_lines')->result();
    }

    public function clear_payslip_lines($payslip_id)
    {
        $this->db->where('payslip_id',(int)$payslip_id)->delete($this->p().'hr_payslip_lines');
    }

    public function insert_payslip_line($data)
    {
        $allowed = ['payslip_id','rule_id','rule_code','rule_name','category',
                    'sequence','quantity','rate','amount','appears_on_payslip'];
        $row = array_intersect_key($data, array_flip($allowed));
        $this->db->insert($this->p().'hr_payslip_lines', $row);
        return $this->db->insert_id();
    }

    // ── Payroll Addons ─────────────────────────────────────────────────────────

    public function get_payroll_addons()
    {
        return $this->db->order_by('name')->get($this->p().'hr_payroll_addons')->result();
    }

    public function get_active_addon($addon_id)
    {
        return $this->db->where('addon_id', $addon_id)
                        ->where('status','active')
                        ->get($this->p().'hr_payroll_addons')->row();
    }

    public function get_addon_for_company($company_id)
    {
        $p = $this->p();
        return $this->db->select('pa.*')
                        ->from($p.'hr_payroll_addons pa')
                        ->join($p.'hr_payroll_companies pc', 'pc.payroll_addon_id = pa.id')
                        ->where('pc.id', (int)$company_id)
                        ->where('pa.status','active')
                        ->get()->row();
    }

    public function save_payroll_addon($data, $id = null)
    {
        $allowed = ['addon_id','name','version','country_code','country_name','addon_type','file_path','manifest','status','installed_at','installed_by'];
        $row = array_intersect_key($data, array_flip($allowed));
        if ($id) {
            $this->db->where('id',(int)$id)->update($this->p().'hr_payroll_addons', $row);
            return $id;
        }
        $this->db->insert($this->p().'hr_payroll_addons', $row);
        return $this->db->insert_id();
    }

    public function get_addon_setting($addon_id, $key, $company_id = 0, $default = null)
    {
        $row = $this->db->where('addon_id', $addon_id)
                        ->where('company_id', (int)$company_id)
                        ->where('key', $key)
                        ->get($this->p().'hr_payroll_addon_settings')->row();
        return $row ? $row->value : $default;
    }

    public function set_addon_setting($addon_id, $key, $value, $company_id = 0)
    {
        $p = $this->p();
        $exists = $this->db->where('addon_id',$addon_id)->where('company_id',(int)$company_id)
                           ->where('key',$key)->count_all_results($p.'hr_payroll_addon_settings');
        if ($exists) {
            $this->db->where('addon_id',$addon_id)->where('company_id',(int)$company_id)
                     ->where('key',$key)->update($p.'hr_payroll_addon_settings',['value'=>$value]);
        } else {
            $this->db->insert($p.'hr_payroll_addon_settings',
                ['addon_id'=>$addon_id,'company_id'=>(int)$company_id,'key'=>$key,'value'=>$value]);
        }
    }

    // ── Dashboard stats ────────────────────────────────────────────────────────

    public function get_payroll_dashboard_stats($company_id = null)
    {
        $p = $this->p();
        $cy = date('Y');
        $cm = date('m');

        $run_q = $this->db->from($p.'hr_payroll_runs');
        if ($company_id) $run_q->where('company_id',(int)$company_id);
        $total_runs = $run_q->count_all_results();

        $this->db->from($p.'hr_payroll_runs')->where('state','draft');
        if ($company_id) $this->db->where('company_id',(int)$company_id);
        $pending_runs = $this->db->count_all_results();

        $this->db->select('SUM(net_wage) AS total')->from($p.'hr_payslips')
                 ->where('YEAR(date_from)', $cy)->where('MONTH(date_from)', $cm)
                 ->where('state !=','cancelled');
        if ($company_id) $this->db->where('company_id',(int)$company_id);
        $month_net = $this->db->get()->row()->total ?? 0;

        $this->db->from($p.'hr_payroll_companies')->where('active',1);
        $companies = $this->db->count_all_results();

        $this->db->from($p.'hr_payroll_contracts')->where('status','active');
        if ($company_id) $this->db->where('company_id',(int)$company_id);
        $active_contracts = $this->db->count_all_results();

        return compact('total_runs','pending_runs','month_net','companies','active_contracts');
    }
}
