<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_analytic_trait
{
    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTIC ITEMS (Ledger)
    // ─────────────────────────────────────────────────────────────────────────

    public function analytic_items()
    {
        if (!has_permission('accounting_dashboard', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'analytic_account_id' => $this->input->get('account_id') ?: null,
            'plan_id'             => $this->input->get('plan_id') ?: null,
            'date_from'           => $this->input->get('date_from') ?: date('Y-01-01'),
            'date_to'             => $this->input->get('date_to') ?: date('Y-m-d'),
            'search'              => $this->input->get('search') ?: null,
        ];

        $data['title']    = 'Analytic Items';
        $data['lines']    = $this->xb_analytic->get_lines($filters);
        $data['plans']    = $this->xb_analytic->get_all_plans(true);
        $data['accounts'] = $this->xb_analytic->get_all_accounts_flat();
        $data['filters']  = $filters;
        $data['xb_page']  = 'analytic_items';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/analytic/items', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COST CENTRE REPORT
    // ─────────────────────────────────────────────────────────────────────────

    public function cost_centre_report()
    {
        if (!has_permission('accounting_dashboard', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'plan_id'   => $this->input->get('plan_id') ?: null,
            'date_from' => $this->input->get('date_from') ?: date('Y-01-01'),
            'date_to'   => $this->input->get('date_to') ?: date('Y-m-d'),
        ];

        $data['title']   = 'Cost Centre Report';
        $data['rows']    = $this->xb_analytic->get_cost_centre_pl($filters);
        $data['plans']   = $this->xb_analytic->get_all_plans(true);
        $data['filters'] = $filters;
        $data['xb_page'] = 'cost_centre_report';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/analytic/cost_centre_report', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX — get analytic accounts for a plan (used in distribution widget)
    // ─────────────────────────────────────────────────────────────────────────

    public function get_analytic_accounts_for_plan($plan_id = 0)
    {
        $accounts = $this->xb_analytic->get_accounts_for_plan((int)$plan_id);
        echo json_encode(['success' => true, 'data' => $accounts]);
    }

    public function get_all_analytic_accounts_ajax()
    {
        $accounts = $this->xb_analytic->get_all_accounts_flat();
        echo json_encode(['success' => true, 'data' => $accounts]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX — save/get analytic assignment for external Perfex forms
    // ─────────────────────────────────────────────────────────────────────────

    public function save_analytic_assignment()
    {
        $form_type  = $this->input->post('form_type');
        $record_id  = (int)$this->input->post('record_id');
        $account_id = (int)$this->input->post('account_id');

        if (!$form_type || !$record_id) {
            echo json_encode(['success' => false, 'error' => 'Missing params']);
            return;
        }

        $this->db->delete(db_prefix() . 'acc_analytic_assignments', [
            'form_type' => $form_type,
            'record_id' => $record_id,
        ]);
        if ($account_id) {
            $this->db->insert(db_prefix() . 'acc_analytic_assignments', [
                'form_type'           => $form_type,
                'record_id'           => $record_id,
                'analytic_account_id' => $account_id,
            ]);
        }
        echo json_encode(['success' => true]);
    }

    public function get_analytic_assignment()
    {
        $form_type = $this->input->get('form_type');
        $record_id = (int)$this->input->get('record_id');

        $row = $this->db->get_where(db_prefix() . 'acc_analytic_assignments', [
            'form_type' => $form_type,
            'record_id' => $record_id,
        ])->row();

        echo json_encode(['success' => true, 'data' => $row ?: null]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIG — ANALYTIC PLANS (called from config() dispatcher)
    // ─────────────────────────────────────────────────────────────────────────

    public function _load_analytic_plans_data(&$data)
    {
        if ($this->input->post('action') === 'save_plan') {
            $id  = $this->input->post('id') ?: null;
            $res = $this->xb_analytic->save_plan($this->input->post(), $id ? (int)$id : null);
            set_alert('success', 'Analytic plan saved.');
            redirect(admin_url('xetuu_books/config/analytic_plans'));
        }
        if ($this->input->post('action') === 'delete_plan') {
            $res = $this->xb_analytic->delete_plan((int)$this->input->post('id'));
            if (isset($res['error'])) {
                set_alert('danger', $res['error']);
            } else {
                set_alert('success', 'Plan deleted.');
            }
            redirect(admin_url('xetuu_books/config/analytic_plans'));
        }
        $data['plans'] = $this->xb_analytic->get_all_plans();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIG — ANALYTIC ACCOUNTS (called from config() dispatcher)
    // ─────────────────────────────────────────────────────────────────────────

    public function _load_analytic_accounts_data(&$data)
    {
        if ($this->input->post('action') === 'save_account') {
            $id  = $this->input->post('id') ?: null;
            $res = $this->xb_analytic->save_account($this->input->post(), $id ? (int)$id : null);
            set_alert('success', 'Account saved.');
            redirect(admin_url('xetuu_books/config/analytic_accounts'));
        }
        if ($this->input->post('action') === 'delete_account') {
            $res = $this->xb_analytic->delete_account((int)$this->input->post('id'));
            if (isset($res['error'])) {
                set_alert('danger', $res['error']);
            } else {
                set_alert('success', 'Account deleted.');
            }
            redirect(admin_url('xetuu_books/config/analytic_accounts'));
        }
        $data['plans']    = $this->xb_analytic->get_all_plans();
        $data['accounts'] = $this->xb_analytic->get_all_accounts(null, false, true);
        $data['currencies'] = $this->xb_config->get_currencies();
    }

    public function render_analytic_widget()
    {
        $this->load->model('xetuu_books/xb_analytic_model');
        $accounts = $this->xb_analytic_model->get_all_accounts_flat();
        
        $selected_id = $this->input->get('analytic_account_id');
        
        $html = '<div class="form-group xb-analytic-group" style="margin-bottom:15px;">';
        $html .= '<label for="xb_analytic_account_id" class="control-label"><i class="fa fa-pie-chart text-muted"></i> Analytic Account (Cost Centre)</label>';
        $html .= '<select name="xb_analytic_account_id" id="xb_analytic_account_id" class="selectpicker" data-width="100%" data-none-selected-text="'._l('dropdown_non_selected_tex').'">';
        $html .= '<option value="">None</option>';
        foreach ($accounts as $acc) {
            $label = htmlspecialchars($acc->complete_name ?: $acc->name);
            if ($acc->code) { $label .= ' [' . htmlspecialchars($acc->code) . ']'; }
            $selected = ($acc->id == $selected_id) ? 'selected' : '';
            $html .= "<option value=\"{$acc->id}\" {$selected}>{$label}</option>";
        }
        $html .= '</select></div>';
        $html .= '<script>setTimeout(function(){ if($.fn.selectpicker) { $("#xb_analytic_account_id").selectpicker("refresh"); } }, 100);</script>';
        
        echo $html;
    }
}
