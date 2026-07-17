<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_config_trait
{
    public function settings()
    {
        $this->config('settings');
    }

    public function config($section = 'settings')
    {
        if (!has_permission('accounting_setting', '', 'view')) { access_denied('xetuu_books'); }

        if ($section === 'lock_dates') {
            $section = 'settings';
        }

        $valid = ['settings','chart_of_accounts','taxes','journals',
                  'currencies','payment_terms','fiscal_years','asset_models','follow_up_levels',
                  'analytic_plans','analytic_accounts','incoterms','reconcil_models','stamp'];
        if (!in_array($section, $valid)) { show_404(); }

        // analytic_plans, analytic_accounts, reconcil_models handle their own POST
        if ($this->input->post() && !in_array($section, ['analytic_plans', 'analytic_accounts', 'reconcil_models'])) {
            $this->_handle_config_post($section);
        }

        $data['section'] = $section;
        $data['xb_page'] = 'config';
        $data['title']   = 'Configuration — ' . ucwords(str_replace('_', ' ', $section));

        switch ($section) {
            case 'chart_of_accounts':
                $this->xb_config->fix_account_parents();
                $data['accounts']        = $this->xb_config->get_accounts_with_balances();
                $data['account_types']   = $this->xb_config->get_account_types();
                $data['currencies']      = $this->xb_config->get_currencies();
                $data['company_name']    = $this->xb_config->get_setting('company_name') ?: '';
                $data['currency_symbol'] = $this->_xb_currency_symbol();
                break;
            case 'taxes':
                $data['taxes']        = $this->xb_config->get_taxes();
                $data['tax_accounts'] = $this->xb_config->get_accounts(['type' => 'Tax']);
                break;
            case 'journals':
                $data['journals']    = $this->xb_journal->get_all_with_accounts();
                $data['gl_accounts'] = $this->xb_config->get_accounts();
                break;
            case 'currencies':
                $data['currencies'] = $this->xb_config->get_currencies();
                break;
            case 'payment_terms':
                $data['payment_terms'] = $this->xb_config->get_payment_terms(true);
                break;
            case 'fiscal_years':
                $data['fiscal_years'] = $this->xb_config->get_fiscal_years();
                break;
            case 'asset_models':
                $data['asset_models'] = $this->db->get('acc_asset_models')->result();
                $data['gl_accounts']  = $this->xb_config->get_accounts();
                $data['journals']     = $this->xb_journal->get_all();
                break;
            case 'follow_up_levels':
                $data['followup_levels'] = $this->db->order_by('sequence', 'ASC')->get('acc_followup_levels')->result();
                break;
            case 'incoterms':
                $data['incoterms'] = $this->db->order_by('sort_order', 'ASC')->get('acc_incoterms')->result();
                break;
            case 'reconcil_models':
                $data['recon_models'] = $this->db->order_by('sequence', 'ASC')->get('acc_reconcil_models')->result();
                $data['gl_accounts']  = $this->xb_config->get_accounts();
                $data['journals']     = $this->xb_journal->get_all();
                break;
            case 'analytic_plans':
                $this->_load_analytic_plans_data($data);
                break;
            case 'analytic_accounts':
                $this->_load_analytic_accounts_data($data);
                break;
            case 'settings':
            default:
                $data['settings']      = $this->xb_config->get_all_settings();
                $data['gl_accounts']   = $this->xb_config->get_accounts();
                $data['currencies']    = $this->xb_config->get_currencies();
                $data['payment_terms'] = $this->xb_config->get_payment_terms();
                $data['lock_dates']    = $this->xb_config->get_lock_dates();
                $data['journals']      = $this->xb_journal->get_all();
                break;
        }

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/config/' . $section, $data, true),
        ]));
    }

    private function _handle_config_post($section)
    {
        $post = $this->input->post(null, true);
        switch ($section) {
            case 'settings':
            case 'stamp':
                foreach ($post as $k => $v) { $this->xb_config->set_setting($k, $v); }
                set_alert('success', 'Settings saved.');
                break;
            case 'chart_of_accounts':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->xb_config->save_account($post, $id);
                set_alert('success', 'Account saved.');
                break;
            case 'taxes':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->xb_config->save_tax($post, $id);
                set_alert('success', 'Tax saved.');
                break;
            case 'journals':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->xb_journal->save($post, $id);
                set_alert('success', 'Journal saved.');
                break;
            case 'payment_terms':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->xb_config->save_payment_term($post, $id);
                set_alert('success', 'Payment term saved.');
                break;
        }
        redirect(admin_url('xetuu_books/config/' . $section));
    }

    private function _xb_currency_symbol()
    {
        $cid = (int)($this->xb_config->get_setting('default_currency') ?: 1);
        $cur = $this->db->where('id', $cid)->get('acc_currencies')->row();
        return ($cur && $cur->symbol) ? $cur->symbol : 'KSh';
    }
}
