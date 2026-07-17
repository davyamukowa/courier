<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/Xr_handler.php';

/**
 * Handles all /xetuu_hr/payroll/* routes.
 * Loaded by Xetuu_hr::payroll() — keeps the main controller thin.
 */
class Payroll_handler extends Xr_handler
{
    private $payroll;  // alias for $this->CI->payroll model
    private $hr;       // alias for $this->CI->hr model

    public function __construct($ci)
    {
        parent::__construct($ci);
        $this->payroll = $ci->payroll;
        $this->hr      = $ci->hr;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Main dispatcher
    // ══════════════════════════════════════════════════════════════════════════

    public function handle($sub, $action, $id)
    {
        $p          = $this->p();
        $company_id = $this->_active_company();
        $base_data  = $this->_base_data($company_id);

        // Dashboard
        if (!$sub) {
            $this->view('xetuu_hr/admin/payroll/dashboard', array_merge($base_data, [
                'title'       => 'Payroll Dashboard',
                'stats'       => $this->payroll->get_payroll_dashboard_stats($company_id ?: null),
                'recent_runs' => $this->payroll->get_payroll_runs($company_id ?: null, 10),
            ]));
            return;
        }

        switch ($sub) {
            case 'config':       $this->_config($action, $id, $base_data, $company_id, $p); break;
            case 'contracts':    $this->_contracts($action, $id, $base_data, $company_id, $p); break;
            case 'payslips':     $this->_payslips($action, $id, $base_data, $company_id, $p); break;
            case 'batches':      $this->_batches($action, $id, $base_data, $company_id, $p); break;
            case 'loans':        $this->_loans_dispatch($action, $id, $base_data, $company_id, $p); break;
            case 'work_entries': $this->_work_entries($action, $id, $base_data, $company_id, $p); break;
            case 'reporting':    $this->_reporting($action, $id, $base_data, $company_id, $p); break;
            default:             redirect($this->base() . '/payroll');
        }
    }

    // ── Active company from session / GET param ────────────────────────────────

    private function _active_company()
    {
        $company_id = (int)($this->CI->session->userdata('payroll_company_id') ?: 0);
        if ($this->get('company')) {
            $company_id = (int)$this->get('company');
            $this->CI->session->set_userdata('payroll_company_id', $company_id);
        }
        return $company_id;
    }

    private function _base_data($company_id)
    {
        return [
            'xhr_active'        => 'payroll',
            'payroll_companies' => $this->payroll->get_payroll_companies(),
            'active_company_id' => $company_id,
            'active_company'    => $company_id ? $this->payroll->get_payroll_company($company_id) : null,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONFIG
    // ══════════════════════════════════════════════════════════════════════════

    private function _config($section, $id, $base_data, $company_id, $p)
    {
        if (!$section || $section === 'companies') {
            $this->_config_companies($id, $base_data, $p);
        } elseif ($section === 'frequencies') {
            $this->_config_frequencies($id, $base_data, $company_id, $p);
        } elseif ($section === 'structures') {
            $this->_config_structures($section, $id, $base_data, $company_id, $p);
        } elseif ($section === 'addons') {
            $this->_config_addons($section, $id, $base_data, $p);
        } elseif ($section === 'settings') {
            $this->_config_settings($base_data, $company_id);
        } else {
            redirect($this->base() . '/payroll/config/companies');
        }
    }

    private function _config_companies($id, $base_data, $p)
    {
        if ($this->CI->input->post()) {
            $post = $this->post(null);
            $cid  = $post['company_id'] ?? null;
            $this->payroll->save_payroll_company($post, $cid ?: null);
            $this->set_alert('success', $cid ? 'Company updated.' : 'Payroll company created.');
            $this->redirect($this->base() . '/payroll/config/companies');
            return;
        }
        // URL convention: config/companies/add  |  config/companies/{id}/edit
        $verb   = $this->uri(7);
        $is_add = ($id === 'add');
        $rec_id = $is_add ? 0 : (int)$id;
        $edit   = ($verb === 'edit' && $rec_id) ? $this->payroll->get_payroll_company($rec_id) : null;

        // Addon-injected field labels — active PHP addon may override via filter
        $addon_labels = hooks()->apply_filters('payroll_company_field_labels', [
            'tax_reg_number'    => 'Tax Registration No.',
            'social_sec_number' => 'Social Security No.',
            'health_fund_number'=> 'Health Fund No.',
        ]);

        $this->view('xetuu_hr/admin/payroll/config/companies', array_merge($base_data, [
            'title'          => 'Payroll Companies',
            'xhr_payroll_sub'=> 'config_companies',
            'companies'      => $this->payroll->get_payroll_companies(false),
            'clients'        => $this->db()->order_by('company')->get('clients')->result(),
            'addons'         => $this->payroll->get_payroll_addons(),
            'edit_company'   => $edit,
            'show_form'      => $is_add || ($verb === 'edit' && $rec_id),
            'addon_labels'   => $addon_labels,
        ]));
    }

    private function _config_frequencies($id, $base_data, $company_id, $p)
    {
        if ($this->CI->input->post()) {
            $post = $this->post(null);
            $fid  = $post['freq_id'] ?? null;
            $this->payroll->save_pay_frequency($post, $fid ?: null);
            $this->set_alert('success', $fid ? 'Frequency updated.' : 'Pay frequency created.');
            $this->redirect($this->base() . '/payroll/config/frequencies');
            return;
        }
        // URL convention: config/frequencies/add  |  config/frequencies/{id}/edit|delete
        $verb   = $this->uri(7);
        $is_add = ($id === 'add');
        $rec_id = $is_add ? 0 : (int)$id;
        if ($verb === 'delete' && $rec_id) {
            $this->payroll->delete_pay_frequency($rec_id);
            $this->set_alert('success','Pay frequency deleted.');
            $this->redirect($this->base() . '/payroll/config/frequencies');
            return;
        }
        $this->view('xetuu_hr/admin/payroll/config/frequencies', array_merge($base_data, [
            'title'          => 'Pay Frequencies',
            'xhr_payroll_sub'=> 'config_freq',
            'frequencies'    => $this->payroll->get_pay_frequencies(0),
            'edit_freq'      => ($verb === 'edit' && $rec_id) ? $this->payroll->get_pay_frequency($rec_id) : null,
            'show_form'      => $is_add || ($verb === 'edit' && $rec_id),
        ]));
    }

    private function _config_structures($section, $id, $base_data, $company_id, $p)
    {
        if ($this->CI->input->post('save_rule')) {
            $post = $this->post(null);
            $rid  = $post['rule_id'] ?? null;
            $this->payroll->save_salary_rule($post, $rid ?: null);
            $this->set_alert('success','Rule saved.');
            $this->redirect($this->base() . '/payroll/config/structures/'.$post['structure_id'].'/rules');
            return;
        }
        if ($this->CI->input->post('save_structure')) {
            $post    = $this->post(null);
            $sid     = $post['structure_id'] ?? null;
            $new_sid = $this->payroll->save_salary_structure($post, $sid ?: null);
            $this->set_alert('success', $sid ? 'Structure updated.' : 'Structure created.');
            $this->redirect($this->base() . '/payroll/config/structures/'.$new_sid.'/rules');
            return;
        }

        $verb    = $this->uri(7);            // edit | rules | add_rule | toggle_rule
        
        if ($verb === 'toggle_rule' && $this->CI->input->post()) {
            $rule_id = $this->post('id');
            $active = $this->post('active');
            $this->db()->where('id', $rule_id)->update($p.'hr_salary_rules', ['active' => $active]);
            echo json_encode(['success' => true]);
            exit;
        }

        // URL convention:
        //   config/structures/add                       → list + new structure form
        //   config/structures/{id}/edit                 → list + edit structure form
        //   config/structures/{id}/rules                → rules page
        //   config/structures/{id}/add_rule             → rules page + new rule form
        //   config/structures/{id}/edit/{ruleid}        → rules page + edit rule form
        $verb    = $this->uri(7);            // edit | rules | add_rule
        $rule_id = $this->uri(8);            // present only when editing a rule
        $is_add  = ($id === 'add');
        $rec_id  = $is_add ? 0 : (int)$id;

        $on_rules_page = $rec_id && (in_array($verb, ['rules','add_rule']) || ($verb === 'edit' && $rule_id));
        if ($on_rules_page) {
            $struct = $this->payroll->get_salary_structure($rec_id);
            if (!$struct) $this->show404();
            $this->view('xetuu_hr/admin/payroll/config/salary_rules', array_merge($base_data, [
                'title'          => $struct->name . ' — Rules',
                'xhr_payroll_sub'=> 'config_structures',
                'structure'      => $struct,
                'rules'          => $this->payroll->get_salary_rules($rec_id),
                'edit_rule'      => ($verb === 'edit' && $rule_id) ? $this->payroll->get_salary_rule((int)$rule_id) : null,
                'show_rule_form' => ($verb === 'add_rule') || ($verb === 'edit' && $rule_id),
            ]));
            return;
        }
        $this->view('xetuu_hr/admin/payroll/config/structures', array_merge($base_data, [
            'title'          => 'Salary Structures',
            'xhr_payroll_sub'=> 'config_structures',
            'structures'     => $this->payroll->get_salary_structures($company_id ?: null),
            'frequencies'    => $this->payroll->get_pay_frequencies($company_id),
            'edit_structure' => ($verb === 'edit' && $rec_id) ? $this->payroll->get_salary_structure($rec_id) : null,
            'show_form'      => $is_add || ($verb === 'edit' && $rec_id),
        ]));
    }

    private function _config_addons($section, $id, $base_data, $p)
    {
        if ($this->CI->input->post('upload_addon')) {
            $this->_upload_addon();
            return;
        }
        // URL convention: config/addons/{verb}/{id}  (verb = $id param, id = segment 7)
        $verb     = $id;
        $addon_id = (int)$this->uri(7);
        if ($verb === 'activate' && $addon_id) {
            $addon = $this->db()->where('id',$addon_id)->get($p.'hr_payroll_addons')->row();
            if ($addon && !empty($addon->file_path)) {
                $install_path = rtrim($addon->file_path, '/') . '/install.php';
                if (file_exists($install_path)) {
                    $CI = &get_instance();
                    include $install_path;
                }
            }
            $this->db()->where('id',$addon_id)->update($p.'hr_payroll_addons',['status'=>'active']);
            $this->set_alert('success','Addon activated.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }
        if ($verb === 'deactivate' && $addon_id) {
            $this->db()->where('id',$addon_id)->update($p.'hr_payroll_addons',['status'=>'inactive']);
            $this->set_alert('success','Addon deactivated.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }
        if ($verb === 'delete' && $addon_id) {
            $addon = $this->db()->where('id',$addon_id)->get($p.'hr_payroll_addons')->row();
            if ($addon && !empty($addon->file_path) && is_dir($addon->file_path)) {
                $this->_rrmdir($addon->file_path);
            }
            $this->db()->where('id',$addon_id)->delete($p.'hr_payroll_addons');
            $this->set_alert('success','Addon removed.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }
        $this->view('xetuu_hr/admin/payroll/config/addons', array_merge($base_data, [
            'title'          => 'Payroll Addons',
            'xhr_payroll_sub'=> 'config_addons',
            'addons'         => $this->payroll->get_payroll_addons(),
        ]));
    }

    private function _config_settings($base_data, $company_id)
    {
        $setting_keys = ['default_company_id','payslip_template','chunk_size','round_decimals',
                         'email_payslips','email_subject','email_body','auto_journal','auto_payment_entry'];
        if ($this->CI->input->post()) {
            $post = $this->post(null);
            foreach ($setting_keys as $k) {
                $this->payroll->set_addon_setting('core', $k, $post[$k] ?? '', $company_id);
            }
            $this->set_alert('success','Payroll settings saved.');
            $this->redirect($this->base() . '/payroll/config/settings');
            return;
        }
        $settings = [];
        foreach ($setting_keys as $k) {
            $settings[$k] = $this->payroll->get_addon_setting('core', $k, $company_id);
        }
        $this->view('xetuu_hr/admin/payroll/config/settings', array_merge($base_data, [
            'title'          => 'Payroll Settings',
            'xhr_payroll_sub'=> 'config_settings',
            'settings'       => $settings,
            'companies'      => $this->payroll->get_payroll_companies(),
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONTRACTS
    // ══════════════════════════════════════════════════════════════════════════

    private function _contracts($action, $id, $base_data, $company_id, $p)
    {
        // Support RESTful URL pattern: /contracts/{id}/edit  or  /contracts/{id}/delete
        if (is_numeric($action) && in_array($id, ['edit', 'delete'])) {
            [$action, $id] = [$id, (int)$action];
        }

        // Form posts to /payroll/contracts (no /save segment) — handle POST with contract fields
        if ($this->CI->input->post() && ($action === 'save' || $this->CI->input->post('employee_id'))) {
            $this->_save_contract();
            return;
        }
        if ($action === 'delete' && $id) {
            $this->db()->where('id',(int)$id)->update($p.'hr_payroll_contracts',['status'=>'cancelled']);
            $this->set_alert('success','Contract cancelled.');
            $this->redirect($this->base() . '/payroll/contracts');
            return;
        }
        $emp_id = (int)($this->get('employee_id') ?: 0);
        $this->view('xetuu_hr/admin/payroll/contracts', array_merge($base_data, [
            'title'          => 'Payroll Contracts',
            'xhr_payroll_sub'=> 'contracts',
            'contracts'      => $emp_id
                ? $this->payroll->get_employee_contracts($emp_id)
                : $this->db()
                    ->select('pc.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                              ss.name AS structure_name, pco.name AS company_name, pc.wage AS basic_salary')
                    ->from($p.'hr_payroll_contracts pc')
                    ->join($p.'hr_employees e','e.id=pc.employee_id','left')
                    ->join($p.'hr_salary_structures ss','ss.id=pc.structure_id','left')
                    ->join($p.'hr_payroll_companies pco','pco.id=pc.company_id','left')
                    ->order_by('pc.date_created','DESC')->get()->result(),
            'employees'      => $this->hr->get_employees(),
            'companies'      => $this->payroll->get_payroll_companies(),
            'structures'     => $this->payroll->get_salary_structures($company_id ?: null),
            'edit_contract'  => ($action === 'edit' && $id) ? $this->payroll->get_payroll_contract((int)$id) : null,
            'contract_lines' => ($action === 'edit' && $id) ? $this->payroll->get_contract_lines((int)$id) : [],
            'show_form'      => in_array($action, ['add','edit']),
        ]));
    }

    private function _save_contract()
    {
        $post = $this->post(null);
        $id   = $post['contract_id'] ?? null;

        $ben_names = $post['benefit_name']    ?? [];
        $ben_codes = $post['benefit_code']    ?? [];
        $ben_amts  = $post['benefit_amount']  ?? [];
        $ded_names = $post['deduction_name']  ?? ($post['ded_name']  ?? []);
        $ded_codes = $post['deduction_code']  ?? ($post['ded_code']  ?? []);
        $ded_amts  = $post['deduction_amount']?? ($post['ded_amount']?? []);

        $benefits = $deductions = [];
        foreach ($ben_names as $i => $name) {
            if (!$name) continue;
            $benefits[] = ['name'=>$name,'code'=>strtoupper($ben_codes[$i] ?? ''),'amount'=>(float)($ben_amts[$i]??0),'statutory_ref'=>null];
        }
        foreach ($ded_names as $i => $name) {
            if (!$name) continue;
            $deductions[] = ['name'=>$name,'code'=>strtoupper($ded_codes[$i] ?? ''),'amount'=>(float)($ded_amts[$i]??0),'statutory_ref'=>null];
        }

        if (isset($post['salary_structure_id'])) $post['structure_id'] = $post['salary_structure_id'];
        if (isset($post['basic_salary']))         $post['wage']         = $post['basic_salary'];

        $contract_id = $this->payroll->save_payroll_contract($post, $id ?: null);
        $this->payroll->sync_contract_lines($contract_id, $benefits, $deductions);
        $this->set_alert('success', $id ? 'Contract updated.' : 'Contract created.');
        $this->redirect($this->base() . '/payroll/contracts');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYSLIPS (single)
    // ══════════════════════════════════════════════════════════════════════════

    private function _payslips($action, $id, $base_data, $company_id, $p)
    {
        // Accept POST either from /payslips/save or from /payslips (form posts with create_payslip button)
        if ($this->CI->input->post() && ($action === 'save' || $this->CI->input->post('create_payslip'))) {
            $this->_save_payslip();
            return;
        }
        // URL pattern: /payslips/{numeric_id}/{action_word}
        // CI parses this as payroll(sub='payslips', action={numeric_id}, id={action_word})
        if (is_numeric($action) && $id === 'compute') {
            $this->_compute_single((int)$action);
            return;
        }
        if (is_numeric($action) && $id === 'confirm') {
            $rows = $this->db()->where('id',(int)$action)->where('state','computed')->get($p.'hr_payslips');
            if ($rows->num_rows()) {
                $this->db()->where('id',(int)$action)->update($p.'hr_payslips',['state'=>'confirmed']);
                $this->_record_loan_repayments((int)$action);
                $this->_post_payslip_to_books((int)$action);
            }
            $this->set_alert('success','Payslip confirmed.');
            $this->redirect($this->base() . '/payroll/payslips/view/'.(int)$action);
            return;
        }
        if (is_numeric($action) && $id === 'reset') {
            $slip = $this->payroll->get_payslip((int)$action);
            if ($slip && !in_array($slip->state, ['paid'])) {
                // Void journal entry if one was created
                if (!empty($slip->journal_entry_id) && file_exists(APPPATH . '../modules/xetuu_books/models/Xb_engine_model.php')) {
                    $this->CI->load->model('xetuu_books/Xb_engine_model', 'xb_engine');
                    try { $this->CI->xb_engine->reset_move((int)$slip->journal_entry_id); } catch (\Exception $e) {}
                }
                // Reverse any loan repayments linked to this payslip
                $this->CI->load->model('xetuu_hr/Xr_loans_model', 'loans_mdl');
                $this->CI->loans_mdl->reverse_payslip_repayments((int)$action);
                $this->db()->where('id',(int)$action)->update($p.'hr_payslips', [
                    'state'            => 'draft',
                    'gross_wage'       => 0, 'net_wage' => 0, 'cash_payable' => null,
                    'total_deductions' => 0, 'total_tax' => 0, 'total_employer' => 0,
                    'journal_entry_id' => null,
                ]);
                $this->db()->where('payslip_id',(int)$action)->delete($p.'hr_payslip_lines');
                $this->set_alert('success', 'Payslip reset to draft.');
            } else {
                $this->set_alert('warning', 'Paid payslips cannot be reset.');
            }
            $this->redirect($this->base() . '/payroll/payslips/view/'.(int)$action);
            return;
        }
        if (is_numeric($action) && $id === 'delete') {
            $slip = $this->payroll->get_payslip((int)$action);
            if ($slip && !in_array($slip->state, ['confirmed','done','paid'])) {
                $this->db()->where('payslip_id',(int)$action)->delete($p.'hr_payslip_lines');
                $this->db()->where('id',(int)$action)->delete($p.'hr_payslips');
                $this->set_alert('success', 'Payslip deleted.');
                $this->redirect($this->base() . '/payroll/payslips');
            } else {
                $this->set_alert('danger', 'Confirmed or paid payslips cannot be deleted. Reset to draft first.');
                $this->redirect($this->base() . '/payroll/payslips/view/'.(int)$action);
            }
            return;
        }
        if (is_numeric($action) && $id === 'edit' && $this->CI->input->post()) {
            $post = $this->post(null);
            $slip = $this->payroll->get_payslip((int)$action);
            if ($slip && $slip->state === 'draft') {
                $this->payroll->save_payslip([
                    'date_from'    => $post['date_from'],
                    'date_to'      => $post['date_to'],
                    'structure_id' => $post['structure_id'] ?: $slip->structure_id,
                    'worked_days'  => $post['worked_days'] ?: null,
                    'notes'        => $post['notes'] ?? '',
                ], (int)$action);
                $this->set_alert('success', 'Payslip updated.');
            }
            $this->redirect($this->base() . '/payroll/payslips/view/'.(int)$action);
            return;
        }
        if (is_numeric($action) && $id === 'pdf') {
            $this->_payslip_pdf((int)$action);
            return;
        }
        // Legacy action patterns (kept for backward compatibility)
        if ($action === 'compute' && $id) {
            $this->_compute_single((int)$id);
            return;
        }
        if ($action === 'confirm' && $id) {
            $rows = $this->db()->where('id',(int)$id)->where('state','computed')->get($p.'hr_payslips');
            if ($rows->num_rows()) {
                $this->db()->where('id',(int)$id)->update($p.'hr_payslips',['state'=>'confirmed']);
                $this->_record_loan_repayments((int)$id);
                $this->_post_payslip_to_books((int)$id);
            }
            $this->set_alert('success','Payslip confirmed.');
            $this->redirect($this->base() . '/payroll/payslips/view/'.$id);
            return;
        }
        if ($id === 'pdf' && $action) {
            $this->_payslip_pdf((int)$action);
            return;
        }
        // URL: /payslips/{id} → action={id}, id=null — load payslip detail directly
        if (is_numeric($action) && !$id) {
            $payslip = $this->payroll->get_payslip((int)$action);
            if (!$payslip) $this->show404();
            $this->view('xetuu_hr/admin/payroll/payslip_view', array_merge($base_data, [
                'title'          => 'Payslip — ' . ($payslip->employee_name ?? 'PS-'.$action),
                'xhr_payroll_sub'=> 'payslips',
                'payslip'        => $payslip,
                'lines'          => $this->payroll->get_payslip_lines((int)$action),
                'structures'     => $this->payroll->get_salary_structures(null),
            ]));
            return;
        }
        if ($action === 'view' && $id) {
            $payslip = $this->payroll->get_payslip((int)$id);
            if (!$payslip) $this->show404();
            $this->view('xetuu_hr/admin/payroll/payslip_view', array_merge($base_data, [
                'title'          => 'Payslip — ' . ($payslip->employee_name ?? 'PS-'.$id),
                'xhr_payroll_sub'=> 'payslips',
                'payslip'        => $payslip,
                'lines'          => $this->payroll->get_payslip_lines((int)$id),
                'structures'     => $this->payroll->get_salary_structures(null),
            ]));
            return;
        }
        $this->view('xetuu_hr/admin/payroll/payslips', array_merge($base_data, [
            'title'          => 'Payslips',
            'xhr_payroll_sub'=> 'payslips',
            'payslips'       => $this->payroll->get_payslips(['company_id'=>$company_id ?: null]),
            'employees'      => $this->hr->get_employees(),
            'companies'      => $this->payroll->get_payroll_companies(),
            'show_form'      => $action === 'new',
            'edit_payslip'   => null,
        ]));
    }

    private function _save_payslip()
    {
        $post     = $this->post(null);
        $contract = $this->payroll->get_active_payroll_contract((int)$post['employee_id']);
        $slip_id  = $this->payroll->save_payslip([
            'company_id'   => $post['company_id'] ?? ($contract->company_id ?? 0),
            'employee_id'  => (int)$post['employee_id'],
            'contract_id'  => $contract ? $contract->id : null,
            'structure_id' => $contract ? $contract->structure_id : null,
            'date_from'    => $post['date_from'],
            'date_to'      => $post['date_to'],
            'state'        => 'draft',
            'notes'        => $post['notes'] ?? '',
        ]);
        $this->set_alert('success','Payslip created. Click Compute to calculate.');
        $this->redirect($this->base() . '/payroll/payslips/view/'.$slip_id);
    }

    private function _compute_single($payslip_id)
    {
        $this->CI->load->library('xetuu_hr/Payroll_engine');
        $result = $this->CI->payroll_engine->compute_payslip($payslip_id);
        if ($this->CI->input->is_ajax_request()) { echo json_encode($result); exit; }
        $this->set_alert($result['success'] ? 'success' : 'danger', $result['message'] ?? 'Computed.');
        $this->redirect($this->base() . '/payroll/payslips/view/'.$payslip_id);
    }

    private function _payslip_pdf($payslip_id)
    {
        $payslip = $this->payroll->get_payslip($payslip_id);
        if (!$payslip) { $this->show404(); }

        // Model returns stdClass objects — cast to arrays for PDF templates
        $lines = array_map(fn($l) => (array)$l, $this->payroll->get_payslip_lines($payslip_id));

        $contract = $payslip->contract_id
            ? $this->payroll->get_payroll_contract($payslip->contract_id)
            : $this->payroll->get_active_payroll_contract($payslip->employee_id);

        $template = $this->payroll->get_addon_setting('core', 'payslip_template', $this->_active_company()) ?: 'a4_standard';

        // Discard any stray output (PHP warnings etc.) so PDF headers can be sent cleanly
        if (ob_get_level()) { ob_end_clean(); }
        ob_start();

        require_once APPPATH . '../modules/xetuu_hr/libraries/pdf/Payslip_pdf.php';
        $pdf = new Payslip_pdf($payslip, $lines, $contract, $template);
        $pdf->prepare();

        ob_end_clean(); // clear anything the template may have accidentally echoed

        $filename = 'Payslip_' . preg_replace('/[^A-Za-z0-9_]/', '_', $payslip->employee_name ?? $payslip_id)
                  . '_' . date('M_Y', strtotime($payslip->date_from)) . '.pdf';
        $pdf->Output($filename, 'I');
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BATCHES
    // ══════════════════════════════════════════════════════════════════════════

    private function _batches($action, $id, $base_data, $company_id, $p)
    {
        if ($action === 'save' && $this->CI->input->post()) {
            $this->_save_batch($p);
            return;
        }
        // AJAX: return eligible employees for a given HR company + optional department
        if ($action === 'preview_employees') {
            header('Content-Type: application/json');
            $hr_company_id = (int)$this->get('hr_company_id');
            $dept_id       = (int)$this->get('department_id');
            $hrc           = $hr_company_id ? $this->db()->where('id', $hr_company_id)->get($p.'hr_companies')->row() : null;
            $payroll_co_id = ($hrc && !empty($hrc->payroll_company_id)) ? (int)$hrc->payroll_company_id : 0;
            if (!$payroll_co_id) {
                echo json_encode(['success'=>false,'message'=>'This company has no linked payroll company. Go to Setup → Company to link one.','employees'=>[]]);
                exit;
            }
            $candidates = $this->payroll->get_batch_candidates($payroll_co_id, $dept_id ?: null);
            echo json_encode(['success'=>true,'employees'=>$candidates,'payroll_company_id'=>$payroll_co_id]);
            exit;
        }
        if ($action === 'view' && $id) {
            $run = $this->payroll->get_payroll_run((int)$id);
            if (!$run) $this->show404();
            $this->view('xetuu_hr/admin/payroll/batch_view', array_merge($base_data, [
                'title'          => $run->name,
                'xhr_payroll_sub'=> 'batches',
                'run'            => $run,
                'payslips'       => $this->payroll->get_payslips(['run_id'=>$id], 5000),
            ]));
            return;
        }
        if ($action === 'confirm' && $id) {
            $this->db()->where('id',(int)$id)->where('state','computed')
                       ->update($p.'hr_payroll_runs',['state'=>'confirmed','confirmed_by'=>get_staff_user_id(),'confirmed_at'=>date('Y-m-d H:i:s')]);
            $this->db()->where('run_id',(int)$id)->where('state','computed')
                       ->update($p.'hr_payslips',['state'=>'confirmed']);
            $this->set_alert('success','Batch confirmed.');
            $this->redirect($this->base() . '/payroll/batches/view/'.$id);
            return;
        }
        if ($action === 'mark_paid' && $id) {
            $this->db()->where('id',(int)$id)
                       ->update($p.'hr_payroll_runs',['state'=>'paid','paid_by'=>get_staff_user_id(),'paid_at'=>date('Y-m-d H:i:s')]);
            $this->db()->where('run_id',(int)$id)->update($p.'hr_payslips',['state'=>'paid']);
            hooks()->do_action('payroll_run_paid', ['run_id'=>(int)$id]);
            $this->set_alert('success','Batch marked as Paid.');
            $this->redirect($this->base() . '/payroll/batches/view/'.$id);
            return;
        }
        if ($action === 'compute_chunk' && $id) {
            ignore_user_abort(true);
            set_time_limit(120);
            $this->CI->load->library('xetuu_hr/Payroll_engine');
            header('Content-Type: application/json');
            echo json_encode($this->CI->payroll_engine->compute_chunk((int)$id, 200));
            exit;
        }
        $this->view('xetuu_hr/admin/payroll/batches', array_merge($base_data, [
            'title'          => 'Payslip Batches',
            'xhr_payroll_sub'=> 'batches',
            'runs'           => $this->payroll->get_payroll_runs($company_id ?: null),
            'hr_companies'   => $this->db()->order_by('name')->get($p.'hr_companies')->result(),
            'departments'    => $this->db()->order_by('name')->get($p.'hr_departments')->result(),
            'periods'        => $this->payroll->get_open_periods($company_id),
            'show_form'      => $action === 'new',
        ]));
    }

    private function _save_batch($p)
    {
        $post          = $this->post(null);
        $hr_company_id = (int)($post['hr_company_id'] ?? 0);
        $dept_id       = (int)($post['department_id'] ?? 0);
        $employee_ids  = !empty($post['employee_ids']) ? array_map('intval', (array)$post['employee_ids']) : [];

        // Resolve HR company → payroll company via the payroll_company_id link
        $payroll_company_id = (int)($post['payroll_company_id'] ?? 0);
        if (!$payroll_company_id && $hr_company_id) {
            $hrc = $this->db()->where('id', $hr_company_id)->get($p.'hr_companies')->row();
            if ($hrc && !empty($hrc->payroll_company_id)) {
                $payroll_company_id = (int)$hrc->payroll_company_id;
            }
        }

        if (!$payroll_company_id) {
            $this->set_alert('danger', 'No payroll company linked to this company. Go to Setup → Company to link one.');
            $this->redirect($this->base() . '/payroll/batches/new');
            return;
        }

        $run_data = [
            'company_id'  => $payroll_company_id,
            'period_id'   => $post['period_id'] ?: null,
            'name'        => $post['name'],
            'date_from'   => $post['date_from'],
            'date_to'     => $post['date_to'],
            'state'       => 'draft',
            'created_by'  => get_staff_user_id(),
        ];

        $candidates = $this->payroll->get_batch_candidates($payroll_company_id, $dept_id ?: null);

        // Filter to only the employees the user confirmed in the preview modal
        if (!empty($employee_ids)) {
            $candidates = array_values(array_filter($candidates, function($e) use ($employee_ids) {
                return in_array((int)$e->employee_id, $employee_ids);
            }));
        }

        $run_data['employee_count'] = count($candidates);
        $run_id = $this->payroll->save_payroll_run($run_data);

        foreach ($candidates as $emp) {
            $this->payroll->save_payslip([
                'run_id'       => $run_id,
                'company_id'   => $payroll_company_id,
                'employee_id'  => $emp->employee_id,
                'contract_id'  => $emp->contract_id,
                'structure_id' => $emp->structure_id,
                'date_from'    => $run_data['date_from'],
                'date_to'      => $run_data['date_to'],
                'state'        => 'draft',
            ]);
        }
        $this->set_alert('success', 'Batch created with '.$run_data['employee_count'].' payslip(s). Click Compute All to calculate.');
        $this->redirect($this->base() . '/payroll/batches/view/'.$run_id);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // WORK ENTRIES
    // ══════════════════════════════════════════════════════════════════════════

    private function _work_entries($action, $id, $base_data, $company_id, $p)
    {
        if ($this->CI->input->post('save_work_entry')) {
            $post = $this->post(null);
            $this->db()->insert($p.'hr_work_entries', [
                'employee_id' => (int)$post['employee_id'],
                'entry_date'  => $post['entry_date'],
                'entry_type'  => $post['entry_type'],
                'hours'       => (float)$post['hours'],
                'notes'       => $post['notes'] ?? '',
                'source'      => 'manual',
            ]);
            $this->set_alert('success','Work entry added.');
            $this->redirect($this->base() . '/payroll/work_entries');
            return;
        }
        if ($this->CI->input->post('upload_timesheet')) {
            // Timesheet CSV import delegated to Payroll_excel_import
            $this->CI->load->library('xetuu_hr/Payroll_excel_import');
            $result = $this->CI->payroll_excel_import->import_timesheet($company_id);
            $this->set_alert($result['success'] ? 'success' : 'danger', $result['message']);
            $this->redirect($this->base() . '/payroll/work_entries');
            return;
        }
        $from = $this->get('date_from') ?: date('Y-m-01');
        $to   = $this->get('date_to')   ?: date('Y-m-t');
        $this->view('xetuu_hr/admin/payroll/work_entries', array_merge($base_data, [
            'title'          => 'Work Entries',
            'xhr_payroll_sub'=> 'work_entries',
            'entries'        => $this->db()
                ->select('we.*, CONCAT(e.first_name," ",e.last_name) AS employee_name')
                ->from($p.'hr_work_entries we')
                ->join($p.'hr_employees e','e.id=we.employee_id','left')
                ->where('we.entry_date >=',$from)->where('we.entry_date <=',$to)
                ->order_by('we.entry_date','DESC')->limit(500)->get()->result(),
            'employees'      => $this->hr->get_employees(),
            'companies'      => $this->payroll->get_payroll_companies(),
            'show_upload'    => $action === 'upload',
            'show_add_form'  => $action === 'add',
        ]));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // REPORTING
    // ══════════════════════════════════════════════════════════════════════════

    private function _reporting($action, $id, $base_data, $company_id, $p)
    {
        // AJAX fragment
        if ($action && $this->get('format') === 'html') {
            $this->_render_report_html($action, $company_id, $p);
            return;
        }
        // File download
        if ($action && in_array($this->get('format'), ['pdf','csv','excel'])) {
            // Let active PHP addon handle statutory reports first
            $handled = hooks()->apply_filters('payroll_download_report_'.$action, false, [
                'company_id' => $company_id,
                'date_from'  => $this->get('date_from') ?: date('Y-m-01'),
                'date_to'    => $this->get('date_to')   ?: date('Y-m-t'),
                'format'     => $this->get('format'),
            ]);
            if (!$handled) {
                $this->_render_report_csv($action, $company_id, $p);
            }
            return;
        }
        // Collect report cards — base ones + addon-injected ones
        $report_cards = $this->_base_report_cards();
        $report_cards = hooks()->apply_filters('payroll_report_cards', $report_cards, $company_id);

        $this->view('xetuu_hr/admin/payroll/reporting', array_merge($base_data, [
            'title'          => 'Payroll Reports',
            'xhr_payroll_sub'=> 'reporting',
            'report'         => $action,
            'companies'      => $this->payroll->get_payroll_companies(),
            'report_cards'   => $report_cards,
        ]));
    }

    private function _base_report_cards()
    {
        return [
            'summary'       => ['Summary Report',        'Overview of gross, deductions, net pay per employee', 'fa-table',     '#2563eb'],
            'bank_transfer' => ['Bank Transfer List',    'Export account numbers with net pay amounts',          'fa-bank',      '#16a34a'],
            'cost_centre'   => ['Cost Centre Report',    'Payroll costs grouped by department',                  'fa-sitemap',   '#d97706'],
            'variance'      => ['Variance Report',       'Month-over-month changes in salary components',        'fa-line-chart','#9333ea'],
            'ytd'           => ['Year-to-Date (YTD)',    'Cumulative earnings, deductions, and tax for the year','fa-calendar',  '#0891b2'],
        ];
    }

    private function _render_report_html($type, $company_id, $p)
    {
        $from = $this->get('date_from') ?: date('Y-m-01');
        $to   = $this->get('date_to')   ?: date('Y-m-t');
        $cid  = (int)($this->get('company_id') ?: $company_id);

        // Let addons (e.g. Kenya) handle their own report types
        $handled = hooks()->apply_filters('payroll_render_report_html_' . $type, false, [
            'company_id' => $cid, 'date_from' => $from, 'date_to' => $to,
        ]);
        if ($handled) exit;

        switch ($type) {
            case 'summary':       $this->_rpt_summary($cid, $from, $to, $p);       break;
            case 'bank_transfer': $this->_rpt_bank_transfer($cid, $from, $to, $p); break;
            case 'cost_centre':   $this->_rpt_cost_centre($cid, $from, $to, $p);   break;
            case 'variance':      $this->_rpt_variance($cid, $from, $to, $p);      break;
            case 'ytd':           $this->_rpt_ytd($cid, $from, $to, $p);           break;
            default:
                echo '<div style="padding:40px;text-align:center;color:#6b7280;font-size:13px;">'
                   . '<i class="fa fa-info-circle" style="font-size:28px;display:block;margin-bottom:10px;color:#d97706;"></i>'
                   . 'HTML preview is not available for this report type.<br>'
                   . 'Use the <strong>CSV</strong> button above to download the data.'
                   . '</div>';
        }
        exit;
    }

    // ── Shared table helper ────────────────────────────────────────────────────

    private function _rpt_table($headers, $rows, $foot = null, $right_cols = [])
    {
        $th = '<table class="table table-condensed table-bordered" style="font-size:12px;margin:0;"><thead><tr style="background:#f9fafb;">';
        foreach ($headers as $h) {
            $th .= '<th style="font-size:11px;text-transform:uppercase;color:#6b7280;white-space:nowrap;padding:8px 10px;">'.htmlspecialchars($h).'</th>';
        }
        $th .= '</tr></thead><tbody>';
        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach (array_values($row) as $i => $cell) {
                $a = in_array($i, $right_cols) ? 'text-align:right;' : '';
                $tbody .= '<td style="padding:7px 10px;'.$a.'">'.$cell.'</td>';
            }
            $tbody .= '</tr>';
        }
        $tbody .= '</tbody>';
        $tfoot = '';
        if ($foot) {
            $tfoot = '<tfoot><tr style="background:#f0fdf4;font-weight:700;">';
            foreach (array_values($foot) as $i => $cell) {
                $a = in_array($i, $right_cols) ? 'text-align:right;' : '';
                $tfoot .= '<td style="padding:7px 10px;'.$a.'">'.$cell.'</td>';
            }
            $tfoot .= '</tr></tfoot>';
        }
        echo '<div style="overflow-x:auto;">'.$th.$tbody.$tfoot.'</table></div>';
    }

    // ── Core report renderers ──────────────────────────────────────────────────

    private function _rpt_summary($cid, $from, $to, $p)
    {
        $q = $this->db()
            ->select('ps.gross_wage, ps.net_wage, ps.total_deductions, ps.total_tax, ps.state,
                      ps.reference, ps.id, ps.date_from, ps.date_to,
                      CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number', false)
            ->from($p.'hr_payslips ps')
            ->join($p.'hr_employees e', 'e.id=ps.employee_id', 'left')
            ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
            ->where('ps.state !=', 'draft');
        if ($cid) $q->where('ps.company_id', $cid);
        $rows = $q->order_by('e.last_name')->get()->result();

        $g = $d = $t = $n = 0;
        $out = [];
        foreach ($rows as $r) {
            $g += $r->gross_wage; $d += $r->total_deductions;
            $t += $r->total_tax;  $n += $r->net_wage;
            $out[] = [
                htmlspecialchars($r->employee_name ?? '—'),
                htmlspecialchars($r->employee_number ?? ''),
                '<code style="font-size:10px;">'.htmlspecialchars($r->reference ?? 'PS-'.$r->id).'</code>',
                date('d M y', strtotime($r->date_from)).'–'.date('d M y', strtotime($r->date_to)),
                number_format($r->gross_wage, 2),
                '<span style="color:#dc2626;">'.number_format($r->total_deductions, 2).'</span>',
                '<span style="color:#9333ea;">'.number_format($r->total_tax, 2).'</span>',
                '<strong>'.number_format($r->net_wage, 2).'</strong>',
                '<span style="font-size:10px;background:#f3f4f6;padding:2px 6px;border-radius:3px;">'.ucfirst($r->state).'</span>',
            ];
        }
        $foot = ['TOTAL ('.count($rows).')', '', '', '',
            number_format($g,2),
            '<span style="color:#dc2626;">'.number_format($d,2).'</span>',
            '<span style="color:#9333ea;">'.number_format($t,2).'</span>',
            '<strong>'.number_format($n,2).'</strong>', ''];
        $this->_rpt_table(['Employee','No','Ref','Period','Gross','Deductions','Tax','Net Pay','Status'], $out, $foot, [4,5,6,7]);
    }

    private function _rpt_bank_transfer($cid, $from, $to, $p)
    {
        $q = $this->db()
            ->select('CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number,
                      con.bank_account, con.payment_method, ps.net_wage', false)
            ->from($p.'hr_payslips ps')
            ->join($p.'hr_employees e',             'e.id=ps.employee_id',   'left')
            ->join($p.'hr_payroll_contracts con',   'con.id=ps.contract_id', 'left')
            ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
            ->where('ps.state !=', 'draft');
        if ($cid) $q->where('ps.company_id', $cid);
        $rows = $q->order_by('e.last_name')->get()->result();

        $total = 0;
        $out   = [];
        foreach ($rows as $r) {
            $total += $r->net_wage;
            $out[] = [
                htmlspecialchars($r->employee_name ?? '—'),
                htmlspecialchars($r->employee_number ?? ''),
                htmlspecialchars($r->bank_account ?? '—'),
                ucfirst($r->payment_method ?? 'bank'),
                '<strong>'.number_format($r->net_wage, 2).'</strong>',
            ];
        }
        $foot = ['TOTAL ('.count($rows).')', '', '', '', '<strong>'.number_format($total, 2).'</strong>'];
        $this->_rpt_table(['Employee','No','Bank Account / Details','Method','Net Pay'], $out, $foot, [4]);
    }

    private function _rpt_cost_centre($cid, $from, $to, $p)
    {
        $q = $this->db()
            ->select('d.name AS dept_name,
                      COUNT(ps.id) AS headcount,
                      SUM(ps.gross_wage) AS gross, SUM(ps.total_deductions) AS ded,
                      SUM(ps.total_tax) AS tax,   SUM(ps.net_wage) AS net', false)
            ->from($p.'hr_payslips ps')
            ->join($p.'hr_employees e',   'e.id=ps.employee_id',    'left')
            ->join($p.'hr_departments d', 'd.id=e.department_id',   'left')
            ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
            ->where('ps.state !=', 'draft')
            ->group_by('e.department_id')
            ->order_by('d.name');
        if ($cid) $q->where('ps.company_id', $cid);
        $rows = $q->get()->result();

        $g = $d = $t = $n = $h = 0;
        $out = [];
        foreach ($rows as $r) {
            $g += $r->gross; $d += $r->ded; $t += $r->tax; $n += $r->net; $h += $r->headcount;
            $out[] = [
                htmlspecialchars($r->dept_name ?? 'Unassigned'),
                $r->headcount,
                number_format($r->gross, 2),
                number_format($r->ded,   2),
                number_format($r->tax,   2),
                '<strong>'.number_format($r->net, 2).'</strong>',
            ];
        }
        $foot = ['TOTAL', $h, number_format($g,2), number_format($d,2), number_format($t,2), '<strong>'.number_format($n,2).'</strong>'];
        $this->_rpt_table(['Department','Headcount','Gross','Deductions','Tax','Net Pay'], $out, $foot, [2,3,4,5]);
    }

    private function _rpt_variance($cid, $from, $to, $p)
    {
        $cur_rows = $this->db()
            ->select('ps.employee_id, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                      SUM(ps.gross_wage) AS gross, SUM(ps.net_wage) AS net', false)
            ->from($p.'hr_payslips ps')
            ->join($p.'hr_employees e', 'e.id=ps.employee_id', 'left')
            ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
            ->where('ps.state !=', 'draft')->group_by('ps.employee_id');
        if ($cid) $cur_rows->where('ps.company_id', $cid);
        $cur = [];
        foreach ($cur_rows->get()->result() as $r) $cur[$r->employee_id] = $r;

        $prev_from = date('Y-m-d', strtotime($from . ' -1 month'));
        $prev_to   = date('Y-m-t', strtotime($prev_from));
        $prev_rows = $this->db()
            ->select('ps.employee_id, SUM(ps.gross_wage) AS gross, SUM(ps.net_wage) AS net', false)
            ->from($p.'hr_payslips ps')
            ->where('ps.date_from >=', $prev_from)->where('ps.date_to <=', $prev_to)
            ->where('ps.state !=', 'draft')->group_by('ps.employee_id');
        if ($cid) $prev_rows->where('ps.company_id', $cid);
        $prev = [];
        foreach ($prev_rows->get()->result() as $r) $prev[$r->employee_id] = $r;

        $out = [];
        foreach ($cur as $emp_id => $c) {
            $pr = $prev[$emp_id] ?? null;
            $pg = $pr ? (float)$pr->gross : 0.0;
            $pn = $pr ? (float)$pr->net   : 0.0;
            $dg = (float)$c->gross - $pg;
            $dn = (float)$c->net   - $pn;
            $delta = function($v) {
                if ($v > 0)  return '<span style="color:#16a34a;">▲ '.number_format($v,2).'</span>';
                if ($v < 0)  return '<span style="color:#dc2626;">▼ '.number_format(abs($v),2).'</span>';
                return '<span style="color:#9ca3af;">— 0.00</span>';
            };
            $out[] = [
                htmlspecialchars($c->employee_name ?? '—'),
                number_format($pg, 2), number_format((float)$c->gross, 2), $delta($dg),
                number_format($pn, 2), number_format((float)$c->net,   2), $delta($dn),
            ];
        }
        if (empty($out)) {
            echo '<div style="padding:30px;text-align:center;color:#9ca3af;">No data for selected period.</div>';
            return;
        }
        echo '<div style="font-size:11px;color:#6b7280;margin-bottom:8px;">Comparing '.date('M Y',strtotime($from)).' vs '.date('M Y',strtotime($prev_from)).'</div>';
        $this->_rpt_table(['Employee','Prev Gross','Curr Gross','Gross Δ','Prev Net','Curr Net','Net Δ'], $out, null, [1,2,4,5]);
    }

    private function _rpt_ytd($cid, $from, $to, $p)
    {
        $year_start = date('Y-01-01', strtotime($to));
        $q = $this->db()
            ->select('CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number,
                      COUNT(ps.id) AS slips,
                      SUM(ps.gross_wage) AS gross, SUM(ps.total_deductions) AS ded,
                      SUM(ps.total_tax) AS tax,    SUM(ps.net_wage) AS net', false)
            ->from($p.'hr_payslips ps')
            ->join($p.'hr_employees e', 'e.id=ps.employee_id', 'left')
            ->where('ps.date_from >=', $year_start)->where('ps.date_to <=', $to)
            ->where('ps.state !=', 'draft')
            ->group_by('ps.employee_id')
            ->order_by('e.last_name');
        if ($cid) $q->where('ps.company_id', $cid);
        $rows = $q->get()->result();

        $g = $d = $t = $n = 0;
        $out = [];
        foreach ($rows as $r) {
            $g += $r->gross; $d += $r->ded; $t += $r->tax; $n += $r->net;
            $out[] = [
                htmlspecialchars($r->employee_name ?? '—'),
                htmlspecialchars($r->employee_number ?? ''),
                $r->slips,
                number_format($r->gross, 2), number_format($r->ded, 2),
                number_format($r->tax,   2),
                '<strong>'.number_format($r->net, 2).'</strong>',
            ];
        }
        echo '<div style="font-size:11px;color:#6b7280;margin-bottom:8px;">Year to Date: Jan '.date('Y',strtotime($to)).' – '.date('d M Y',strtotime($to)).'</div>';
        $foot = ['TOTAL ('.count($rows).')', '', '', number_format($g,2), number_format($d,2), number_format($t,2), '<strong>'.number_format($n,2).'</strong>'];
        $this->_rpt_table(['Employee','No','Slips','YTD Gross','YTD Deductions','YTD Tax','YTD Net Pay'], $out, $foot, [3,4,5,6]);
    }

    private function _render_report_csv($type, $company_id, $p)
    {
        $from = $this->get('date_from') ?: date('Y-m-01');
        $to   = $this->get('date_to')   ?: date('Y-m-t');
        $cid  = (int)($this->get('company_id') ?: $company_id);

        header('Content-Type: text/csv');

        switch ($type) {
            case 'bank_transfer':
                $rows = $this->db()
                    ->select('CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number,
                              con.bank_account, con.payment_method, ps.net_wage', false)
                    ->from($p.'hr_payslips ps')
                    ->join($p.'hr_employees e',           'e.id=ps.employee_id',   'left')
                    ->join($p.'hr_payroll_contracts con', 'con.id=ps.contract_id', 'left')
                    ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
                    ->where('ps.state !=', 'draft');
                if ($cid) $rows->where('ps.company_id', $cid);
                $rows = $rows->order_by('e.last_name')->get()->result();
                header('Content-Disposition: attachment; filename="bank_transfer_'.date('Ymd').'.csv"');
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Employee','No','Bank Account / Details','Method','Net Pay']);
                $total = 0;
                foreach ($rows as $r) {
                    fputcsv($out, [$r->employee_name, $r->employee_number ?? '', $r->bank_account ?? '', $r->payment_method ?? 'bank', $r->net_wage]);
                    $total += $r->net_wage;
                }
                fputcsv($out, ['TOTAL ('.count($rows).')', '', '', '', number_format($total, 2)]);
                fclose($out);
                break;

            case 'cost_centre':
                $rows = $this->db()
                    ->select('d.name AS dept_name, COUNT(ps.id) AS headcount,
                              SUM(ps.gross_wage) AS gross, SUM(ps.total_deductions) AS ded,
                              SUM(ps.total_tax) AS tax, SUM(ps.net_wage) AS net', false)
                    ->from($p.'hr_payslips ps')
                    ->join($p.'hr_employees e',   'e.id=ps.employee_id',  'left')
                    ->join($p.'hr_departments d', 'd.id=e.department_id', 'left')
                    ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
                    ->where('ps.state !=', 'draft')->group_by('e.department_id')->order_by('d.name');
                if ($cid) $rows->where('ps.company_id', $cid);
                $rows = $rows->get()->result();
                header('Content-Disposition: attachment; filename="cost_centre_'.date('Ymd').'.csv"');
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Department','Headcount','Gross','Deductions','Tax','Net Pay']);
                foreach ($rows as $r) fputcsv($out, [$r->dept_name ?? 'Unassigned', $r->headcount, $r->gross, $r->ded, $r->tax, $r->net]);
                fclose($out);
                break;

            case 'ytd':
                $year_start = date('Y-01-01', strtotime($to));
                $rows = $this->db()
                    ->select('CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number,
                              COUNT(ps.id) AS slips, SUM(ps.gross_wage) AS gross,
                              SUM(ps.total_deductions) AS ded, SUM(ps.total_tax) AS tax, SUM(ps.net_wage) AS net', false)
                    ->from($p.'hr_payslips ps')
                    ->join($p.'hr_employees e', 'e.id=ps.employee_id', 'left')
                    ->where('ps.date_from >=', $year_start)->where('ps.date_to <=', $to)
                    ->where('ps.state !=', 'draft')->group_by('ps.employee_id')->order_by('e.last_name');
                if ($cid) $rows->where('ps.company_id', $cid);
                $rows = $rows->get()->result();
                header('Content-Disposition: attachment; filename="ytd_payroll_'.date('Ymd').'.csv"');
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Employee','No','Slips','YTD Gross','YTD Deductions','YTD Tax','YTD Net Pay']);
                foreach ($rows as $r) fputcsv($out, [$r->employee_name, $r->employee_number ?? '', $r->slips, $r->gross, $r->ded, $r->tax, $r->net]);
                fclose($out);
                break;

            default: // summary + unknown
                $rows = $this->db()
                    ->select('ps.gross_wage AS gross, ps.net_wage AS net, ps.total_deductions, ps.total_tax,
                              ps.state, ps.reference, ps.id, ps.date_from, ps.date_to,
                              CONCAT(e.first_name," ",e.last_name) AS employee_name, e.employee_number', false)
                    ->from($p.'hr_payslips ps')
                    ->join($p.'hr_employees e', 'e.id=ps.employee_id', 'left')
                    ->where('ps.date_from >=', $from)->where('ps.date_to <=', $to)
                    ->where('ps.state !=', 'draft');
                if ($cid) $rows->where('ps.company_id', $cid);
                $rows = $rows->order_by('e.last_name')->get()->result();
                header('Content-Disposition: attachment; filename="payroll_'.$type.'_'.date('Ymd').'.csv"');
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Employee','No','Ref','From','To','Gross','Deductions','Tax','Net Pay','Status']);
                foreach ($rows as $r) {
                    fputcsv($out, [$r->employee_name, $r->employee_number ?? '', $r->reference ?? 'PS-'.$r->id,
                        $r->date_from, $r->date_to, $r->gross, $r->total_deductions, $r->total_tax, $r->net, $r->state]);
                }
                fclose($out);
        }
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADDON UPLOAD  (supports both PHP and Excel/CSV addon types)
    // ══════════════════════════════════════════════════════════════════════════

    private function _upload_addon()
    {
        $upload_dir = FCPATH . 'modules/xetuu_hr/payroll_addons/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $this->CI->load->library('upload', [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'zip',
            'max_size'      => 20480,
        ]);
        if (!$this->CI->upload->do_upload('addon_zip')) {
            $this->set_alert('danger', 'Upload failed: '.$this->CI->upload->display_errors('',''));
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }

        $file     = $this->CI->upload->data();
        $zip_path = $upload_dir . $file['file_name'];
        $extract  = $upload_dir . pathinfo($file['file_name'], PATHINFO_FILENAME) . '/';

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            $this->set_alert('danger','Could not open ZIP file.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }
        $zip->extractTo($extract);
        $zip->close();
        unlink($zip_path);

        // Validate manifest
        $manifest_path = $extract . 'manifest.json';
        if (!file_exists($manifest_path)) {
            $this->_rrmdir($extract);
            $this->set_alert('danger','Invalid addon: manifest.json missing.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }
        $manifest = json_decode(file_get_contents($manifest_path), true);
        if (!$manifest || empty($manifest['id']) || empty($manifest['country'])) {
            $this->_rrmdir($extract);
            $this->set_alert('danger','Invalid manifest.json — required: id, country.');
            $this->redirect($this->base() . '/payroll/config/addons');
            return;
        }

        $addon_type = $manifest['type'] ?? 'php';

        if ($addon_type === 'excel' || $addon_type === 'csv') {
            // Excel/CSV tax table addon — parse and generate salary rules
            $this->CI->load->library('xetuu_hr/Payroll_excel_import');
            $result = $this->CI->payroll_excel_import->install_addon($extract, $manifest);
            if (!$result['success']) {
                $this->_rrmdir($extract);
                $this->set_alert('danger', $result['message']);
                $this->redirect($this->base() . '/payroll/config/addons');
                return;
            }
        } else {
            // PHP addon — run install.php if present
            if (file_exists($extract . 'install.php')) {
                $CI = &get_instance();
                include $extract . 'install.php';
            }
        }

        // Register in DB
        $this->payroll->save_payroll_addon([
            'addon_id'     => $manifest['id'],
            'name'         => $manifest['name'],
            'version'      => $manifest['version'] ?? '1.0.0',
            'country_code' => strtoupper($manifest['country']),
            'country_name' => $manifest['country_name'] ?? '',
            'addon_type'   => $addon_type,
            'file_path'    => $extract,
            'manifest'     => json_encode($manifest),
            'status'       => 'inactive',
            'installed_at' => date('Y-m-d H:i:s'),
            'installed_by' => get_staff_user_id(),
        ]);

        $this->set_alert('success', 'Addon "'.htmlspecialchars($manifest['name']).'" installed successfully. Activate it to use.');
        $this->redirect($this->base() . '/payroll/config/addons');
    }

    private function _rrmdir($dir)
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $f;
            is_dir($path) ? $this->_rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // XETUU BOOKS JOURNAL INTEGRATION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Creates a balanced journal entry in Xetuu Books when a payslip is confirmed.
     *
     * Journal structure (standard payroll accounting):
     *   Dr  Salary Expense       gross earnings
     *   Cr  NSSF Payable         employee NSSF
     *   Cr  SHIF Payable         SHIF contribution
     *   Cr  Housing Levy Payable AHL (1.5%)
     *   Cr  PAYE Payable         income tax withheld
     *   Cr  Net Wages Payable    take-home pay (balancing figure)
     *
     * If an account is not configured the line is omitted; if the entry would be
     * unbalanced it is saved as draft so the accountant can correct it manually.
     */
    private function _post_payslip_to_books($payslip_id)
    {
        // Check if Xetuu Books module is active
        if (!file_exists(APPPATH . '../modules/xetuu_books/models/Xb_engine_model.php')) return;

        $this->CI->load->model('xetuu_books/Xb_config_model',  'xb_config');
        $this->CI->load->model('xetuu_books/Xb_engine_model',  'xb_engine');
        $this->CI->load->model('xetuu_books/Xb_journal_model', 'xb_journal');

        $cfg = $this->CI->xb_config->get_all_settings();

        $journal_id      = (int)($cfg['payroll_journal_id'] ?? 0);
        $salary_acc      = trim($cfg['payroll_salary_account']      ?? '');
        $net_acc         = trim($cfg['payroll_net_payable_account']  ?? '');
        $nssf_acc        = trim($cfg['payroll_nssf_payable_account'] ?? '');
        $paye_acc        = trim($cfg['payroll_paye_payable_account'] ?? '');
        $shif_acc        = trim($cfg['payroll_shif_payable_account'] ?? '');
        $ahl_acc         = trim($cfg['payroll_ahl_payable_account']  ?? '');
        $auto_post       = ($cfg['payroll_auto_post'] ?? '1') === '1';

        // Require at minimum: journal, salary expense, net payable
        if (!$journal_id || !$salary_acc || !$net_acc) return;

        // Load payslip + lines
        $slip  = $this->payroll->get_payslip($payslip_id);
        if (!$slip) return;

        $p = $this->p();

        // Aggregate payslip lines by category and rule_code
        $gross  = 0.0;
        $nssf   = 0.0;
        $shif   = 0.0;
        $ahl    = 0.0;
        $paye   = 0.0;
        $net    = 0.0;

        foreach ($slip->lines as $ln) {
            switch ($ln->category) {
                case 'EARN': $gross += (float)$ln->amount; break;
                case 'NET':  $net    = (float)$ln->amount; break;
            }
            switch ($ln->rule_code) {
                case 'NSSF':        case 'NSSF_AMOUNT': $nssf += (float)$ln->amount; break;
                case 'SHIF':        case 'SHIF_AMOUNT':  $shif += (float)$ln->amount; break;
                case 'AHL':         case 'Housing_Levy': $ahl  += (float)$ln->amount; break;
                case 'PAYE':        case 'INCOME_TAX':   $paye += (float)$ln->amount; break;
            }
        }

        if ($gross <= 0) return;

        // Resolve account codes → IDs
        $acc_map = [];
        $accounts = $this->CI->xb_config->get_accounts();
        foreach ($accounts as $a) {
            $acc_map[$a->code] = (int)$a->id;
        }

        $salary_id = $acc_map[$salary_acc] ?? null;
        $net_id    = $acc_map[$net_acc]    ?? null;
        if (!$salary_id || !$net_id) return;

        $nssf_id = $nssf_acc ? ($acc_map[$nssf_acc] ?? null) : null;
        $paye_id = $paye_acc ? ($acc_map[$paye_acc] ?? null) : null;
        $shif_id = $shif_acc ? ($acc_map[$shif_acc] ?? null) : null;
        $ahl_id  = $ahl_acc  ? ($acc_map[$ahl_acc]  ?? null) : null;

        $period = date('M Y', strtotime($slip->date_from));
        $ref    = 'PAY-' . str_pad($payslip_id, 5, '0', STR_PAD_LEFT);

        $lines = [];

        // Dr: Salary Expense (gross)
        $lines[] = [
            'account_id' => $salary_id,
            'name'       => 'Salary — ' . $slip->employee_name . ' (' . $period . ')',
            'debit'      => round($gross, 2),
            'credit'     => 0,
        ];

        // Cr: statutory liabilities (only if account is configured and amount > 0)
        if ($nssf_id && $nssf > 0) {
            $lines[] = ['account_id' => $nssf_id, 'name' => 'NSSF Payable — ' . $slip->employee_name, 'debit' => 0, 'credit' => round($nssf, 2)];
        }
        if ($shif_id && $shif > 0) {
            $lines[] = ['account_id' => $shif_id, 'name' => 'SHIF Payable — ' . $slip->employee_name, 'debit' => 0, 'credit' => round($shif, 2)];
        }
        if ($ahl_id && $ahl > 0) {
            $lines[] = ['account_id' => $ahl_id, 'name' => 'Housing Levy Payable — ' . $slip->employee_name, 'debit' => 0, 'credit' => round($ahl, 2)];
        }
        if ($paye_id && $paye > 0) {
            $lines[] = ['account_id' => $paye_id, 'name' => 'PAYE Payable — ' . $slip->employee_name, 'debit' => 0, 'credit' => round($paye, 2)];
        }

        // Cr: Net Wages Payable — balancing figure (net pay to employee)
        $allocated_cr = $nssf + $shif + $ahl + $paye;
        $net_cr       = round($gross - $allocated_cr, 2);
        if ($net_cr > 0) {
            $lines[] = ['account_id' => $net_id, 'name' => 'Net Wages Payable — ' . $slip->employee_name, 'debit' => 0, 'credit' => $net_cr];
        }

        $move_data = [
            'journal_id' => $journal_id,
            'date'       => $slip->date_to,
            'ref'        => $ref,
            'narration'  => 'Payroll — ' . $slip->employee_name . ' (' . $period . ')',
            'move_type'  => 'entry',
            'lines'      => $lines,
        ];

        $result = $this->CI->xb_engine->save_entry($move_data);

        if (is_array($result) && !empty($result['id'])) {
            $move_id = $result['id'];

            // Store the link on the payslip
            $this->db()->where('id', $payslip_id)
                       ->update($p . 'hr_payslips', ['journal_entry_id' => $move_id]);

            // Auto-post if configured
            if ($auto_post) {
                try {
                    $this->CI->xb_engine->post_move($move_id);
                } catch (\Exception $e) {
                    // Entry created but posting failed (e.g. unbalanced) — leave as draft
                }
            }
        }
    }

    // ── Record loan repayments when a payslip is confirmed ────────────────────
    private function _record_loan_repayments($payslip_id)
    {
        $this->CI->load->model('xetuu_hr/Xr_loans_model', 'loans_mdl');
        $p    = $this->p();
        $slip = $this->payroll->get_payslip($payslip_id);
        if (!$slip) { return; }

        // Find LOAN lines on this payslip
        $loan_lines = $this->db()->where('payslip_id', $payslip_id)
                                  ->where('category', 'LOAN')
                                  ->get($p . 'hr_payslip_lines')->result();

        foreach ($loan_lines as $line) {
            // Extract loan_id from rule_code = 'LOAN_{id}'
            if (!preg_match('/^LOAN_(\d+)$/', $line->rule_code, $m)) { continue; }
            $loan_id = (int)$m[1];
            $this->CI->loans_mdl->record_repayment(
                $loan_id,
                (float)$line->amount,
                $payslip_id,
                'payroll',
                '',
                get_staff_user_id()
            );
        }
    }

    // ── Loans dispatch (delegates to Loans_handler) ───────────────────────────
    private function _loans_dispatch($action, $id, $base_data, $company_id, $p)
    {
        require_once __DIR__ . '/Loans_handler.php';
        (new Loans_handler($this->CI))->handle($action, $id, $base_data, $company_id, $p);
    }
}
