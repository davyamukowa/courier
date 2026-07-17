<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xetuu_books extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('accounting/Acc_engine_model',  'acc_engine');
        $this->load->model('accounting/Acc_invoice_model', 'acc_invoice');
        $this->load->model('accounting/Acc_payment_model', 'acc_payment');
        $this->load->model('accounting/Acc_journal_model', 'acc_journal');
        $this->load->model('accounting/Acc_report_model',  'acc_report');
        $this->load->model('accounting/Acc_config_model',  'acc_config');
        $this->load->helper('accounting/Accounting');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        if (!has_permission('accounting_dashboard', '', 'view')) { access_denied('xetuu_books'); }

        $data['title']           = 'Xetuu Books — Dashboard';
        $data['inv_stats']       = $this->acc_invoice->get_dashboard_stats();
        $data['bill_stats']      = $this->acc_invoice->get_bill_dashboard_stats();
        $data['bank_journals']   = $this->acc_journal->get_dashboard_journals();
        $data['recent_invoices'] = $this->acc_invoice->get_list('out_invoice', ['limit' => 5]);
        $data['recent_bills']    = $this->acc_invoice->get_list('in_invoice',  ['limit' => 5]);
        $data['xb_page']         = 'dashboard';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/dashboard/index', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER INVOICES
    // ─────────────────────────────────────────────────────────────────────────
    public function invoices()
    {
        if (!has_permission('accounting_transaction', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'      => $this->input->get('state'),
            'partner_id' => $this->input->get('partner_id'),
            'journal_id' => $this->input->get('journal_id'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to'),
            'search'     => $this->input->get('search'),
        ];

        $data['title']        = 'Customer Invoices';
        $data['moves']        = $this->acc_invoice->get_list('out_invoice', $filters);
        $data['list_totals']  = $this->acc_invoice->get_list_totals('out_invoice', $filters);
        $data['filters']      = $filters;
        $data['payment_journals'] = $this->acc_journal->get_by_type('bank');
        $data['journals']     = $this->acc_journal->get_by_type('sale');
        $data['xb_page']      = 'invoices';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/invoices/list', $data, true),
        ]));
    }

    public function invoice_form($id = null)
    {
        if (!has_permission('accounting_transaction', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $result = $this->acc_invoice->save_invoice($post, $id);
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', $id ? 'Invoice updated.' : 'Invoice created.');
                redirect(admin_url('xetuu_books/invoice_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save invoice.');
            }
        }

        $move = $id ? $this->acc_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']            = $id ? ('Invoice #' . ($move->name ?? 'Draft')) : 'New Invoice';
        $data['move']             = $move;
        $data['invoice_lines']    = $id ? $this->acc_invoice->get_lines($id) : [];
        $data['journals']         = $this->acc_journal->get_by_type('sale');
        $data['payment_journals'] = $this->acc_journal->get_by_type('bank');
        $data['payment_terms']    = $this->acc_config->get_payment_terms();
        $data['taxes']            = $this->acc_config->get_taxes('sale');
        $data['accounts']         = $this->acc_config->get_accounts();
        $data['currencies']       = $this->acc_config->get_currencies();
        $data['payments']         = $id ? $this->acc_payment->get_for_move($id) : [];
        $data['xb_page']          = 'invoices';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/invoices/form', $data, true),
        ]));
    }

    public function post_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->acc_engine->post_move($id);
            echo json_encode(['success' => true, 'name' => $this->acc_invoice->get($id)->name]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancel_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'edit')) { access_denied('xetuu_books'); }
        $this->acc_engine->cancel_move($id);
        set_alert('success', 'Invoice cancelled.');
        redirect(admin_url('xetuu_books/invoice_form/' . $id));
    }

    public function reset_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'edit')) { access_denied('xetuu_books'); }
        $this->acc_engine->reset_move_to_draft($id);
        set_alert('success', 'Invoice reset to draft.');
        redirect(admin_url('xetuu_books/invoice_form/' . $id));
    }

    public function delete_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'delete')) { access_denied('xetuu_books'); }
        $this->acc_invoice->delete($id);
        set_alert('success', 'Invoice deleted.');
        redirect(admin_url('xetuu_books/invoices'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VENDOR BILLS
    // ─────────────────────────────────────────────────────────────────────────
    public function bills()
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'      => $this->input->get('state'),
            'partner_id' => $this->input->get('partner_id'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to'),
            'search'     => $this->input->get('search'),
        ];

        $data['title']        = 'Vendor Bills';
        $data['moves']        = $this->acc_invoice->get_list('in_invoice', $filters);
        $data['list_totals']  = $this->acc_invoice->get_list_totals('in_invoice', $filters);
        $data['filters']      = $filters;
        $data['payment_journals'] = $this->acc_journal->get_by_type('bank');
        $data['journals']     = $this->acc_journal->get_by_type('purchase');
        $data['xb_page']  = 'bills';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/bills/list', $data, true),
        ]));
    }

    public function bill_form($id = null)
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->acc_invoice->save_invoice($post, $id, 'in_invoice');
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', $id ? 'Bill updated.' : 'Bill created.');
                redirect(admin_url('xetuu_books/bill_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save bill.');
            }
        }

        $move = $id ? $this->acc_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']            = $id ? ('Bill #' . ($move->name ?? 'Draft')) : 'New Bill';
        $data['move']             = $move;
        $data['invoice_lines']    = $id ? $this->acc_invoice->get_lines($id) : [];
        $data['journals']         = $this->acc_journal->get_by_type('purchase');
        $data['payment_journals'] = $this->acc_journal->get_by_type('bank');
        $data['payment_terms']    = $this->acc_config->get_payment_terms();
        $data['taxes']            = $this->acc_config->get_taxes('purchase');
        $data['accounts']         = $this->acc_config->get_accounts();
        $data['currencies']       = $this->acc_config->get_currencies();
        $data['payments']         = $id ? $this->acc_payment->get_for_move($id) : [];
        $data['xb_page']          = 'bills';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/bills/form', $data, true),
        ]));
    }

    public function post_bill($id)
    {
        if (!has_permission('accounting_bills', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->acc_engine->post_move($id);
            echo json_encode(['success' => true, 'name' => $this->acc_invoice->get($id)->name]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_bill($id)
    {
        if (!has_permission('accounting_bills', '', 'delete')) { access_denied('xetuu_books'); }
        $this->acc_invoice->delete($id);
        set_alert('success', 'Bill deleted.');
        redirect(admin_url('xetuu_books/bills'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENTS
    // ─────────────────────────────────────────────────────────────────────────
    public function payments($type = 'customer')
    {
        if (!has_permission('accounting_transaction', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'partner_type' => $type,
            'state'        => $this->input->get('state'),
            'date_from'    => $this->input->get('date_from'),
            'date_to'      => $this->input->get('date_to'),
            'search'       => $this->input->get('search'),
        ];

        $data['title']        = ($type === 'customer') ? 'Customer Payments' : 'Vendor Payments';
        $data['payments']     = $this->acc_payment->get_list($filters);
        $data['filters']      = $filters;
        $data['partner_type'] = $type;
        $data['xb_page']      = 'payments_' . $type;

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/payments/list', $data, true),
        ]));
    }

    public function register_payment()
    {
        if (!$this->input->post() || !$this->input->is_ajax_request()) { show_404(); }
        $post = $this->input->post(null, true);
        try {
            $payment_id = $this->acc_payment->register_payment($post);
            echo json_encode(['success' => true, 'payment_id' => $payment_id]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JOURNAL ENTRIES
    // ─────────────────────────────────────────────────────────────────────────
    public function journal_entries()
    {
        if (!has_permission('accounting_journal_entry', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'journal_id' => $this->input->get('journal_id'),
            'state'      => $this->input->get('state'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to'),
            'search'     => $this->input->get('search'),
        ];

        $data['title']    = 'Journal Entries';
        $data['entries']  = $this->acc_engine->get_moves_list($filters);
        $data['journals'] = $this->acc_journal->get_all();
        $data['filters']  = $filters;
        $data['xb_page']  = 'journal_entries';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/journals/list', $data, true),
        ]));
    }

    public function journal_entry_form($id = null)
    {
        if (!has_permission('accounting_journal_entry', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->acc_engine->save_entry($post, $id);
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', 'Journal entry saved.');
                redirect(admin_url('xetuu_books/journal_entry_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save entry.');
            }
        }

        $move = $id ? $this->acc_engine->get_move($id) : null;

        $data['title']       = $id ? 'Journal Entry' : 'New Journal Entry';
        $data['entry']       = $move;
        $data['entry_lines'] = $id ? $this->acc_engine->get_move_lines($id) : [];
        $data['all_journals']= $this->acc_journal->get_all();
        $data['accounts']    = $this->acc_config->get_accounts();
        $data['xb_page']     = 'journal_entries';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/journals/form', $data, true),
        ]));
    }

    public function post_entry($id)
    {
        if (!has_permission('accounting_journal_entry', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->acc_engine->post_move($id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_entry($id)
    {
        if (!has_permission('accounting_journal_entry', '', 'delete')) { access_denied('xetuu_books'); }
        $this->acc_engine->delete_move($id);
        set_alert('success', 'Journal entry deleted.');
        redirect(admin_url('xetuu_books/journal_entries'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECONCILIATION
    // ─────────────────────────────────────────────────────────────────────────
    public function reconcile()
    {
        if (!has_permission('accounting_reconcile', '', 'view')) { access_denied('xetuu_books'); }

        $journal_id = (int)$this->input->get('journal_id');
        $journals   = $this->acc_journal->get_by_type(['Bank', 'Cash']);
        if (!$journal_id && !empty($journals)) { $journal_id = $journals[0]->id; }

        $data['title']            = 'Bank Reconciliation';
        $data['bank_journals']    = $journals;
        $data['selected_journal'] = $journal_id;
        $data['statement_lines']  = $journal_id ? $this->acc_engine->get_unreconciled_statement_lines($journal_id) : [];
        $data['unreconciled']     = $journal_id ? $this->acc_engine->get_unreconciled_move_lines($journal_id) : [];
        $data['xb_page']          = 'reconcile';

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/reconcile/index', $data, true),
        ]));
    }

    public function do_reconcile()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $post = $this->input->post(null, true);
        try {
            $this->acc_engine->reconcile_lines($post['debit_line_ids'], $post['credit_line_ids']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function import_statement()
    {
        if (!$this->input->post() || !$this->input->is_ajax_request()) { show_404(); }
        $journal_id = (int)$this->input->post('journal_id');
        if (!$_FILES['statement_file']['tmp_name']) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']); return;
        }
        try {
            $count = $this->acc_engine->import_bank_statement($journal_id, $_FILES['statement_file']);
            echo json_encode(['success' => true, 'imported' => $count]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REPORTS
    // ─────────────────────────────────────────────────────────────────────────
    public function reports($report = 'balance_sheet')
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        $date_from  = $this->input->get('date_from')  ?: date('Y-01-01');
        $date_to    = $this->input->get('date_to')    ?: date('Y-12-31');
        $show_zero  = (bool)$this->input->get('show_zero');
        $compare    = (bool)$this->input->get('compare');
        $partner_id = (int)$this->input->get('partner_id');

        $params = compact('date_from', 'date_to', 'show_zero', 'compare', 'partner_id');

        $valid = ['balance_sheet','profit_loss','general_ledger','trial_balance',
                  'aged_receivable','aged_payable','tax_report','cash_flow','partner_ledger'];
        if (!in_array($report, $valid)) { show_404(); }

        $data = $this->acc_report->get_report_data($report, $params);
        $data['report']     = $report;
        $data['params']     = $params;
        $data['xb_page']    = 'reports';
        $data['title']      = $this->_report_title($report);
        $data['fiscal_years'] = $this->acc_config->get_fiscal_years();

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/reports/' . $report, $data, true),
        ]));
    }

    public function report_export()
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }
        $report   = $this->input->get('report');
        $format   = $this->input->get('format'); // excel | csv | pdf
        $date_from= $this->input->get('date_from') ?: date('Y-01-01');
        $date_to  = $this->input->get('date_to')   ?: date('Y-12-31');
        $params   = compact('date_from', 'date_to');
        $this->acc_report->export_report($report, $format, $params);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIGURATION
    // ─────────────────────────────────────────────────────────────────────────
    public function config($section = 'settings')
    {
        if (!has_permission('accounting_setting', '', 'view')) { access_denied('xetuu_books'); }

        $valid = ['settings','chart_of_accounts','taxes','journals',
                  'currencies','payment_terms','fiscal_years'];
        if (!in_array($section, $valid)) { show_404(); }

        if ($this->input->post()) {
            $this->_handle_config_post($section);
        }

        $data['section']  = $section;
        $data['xb_page']  = 'config';
        $data['title']    = 'Configuration — ' . ucwords(str_replace('_', ' ', $section));

        switch ($section) {
            case 'chart_of_accounts':
                $data['accounts']       = $this->acc_config->get_accounts();
                $data['account_types']  = $this->acc_config->get_account_types();
                break;
            case 'taxes':
                $data['taxes']          = $this->acc_config->get_taxes();
                $data['tax_accounts']   = $this->acc_config->get_accounts(['type' => 'Tax']);
                break;
            case 'journals':
                $data['journals']       = $this->acc_journal->get_all_with_accounts();
                $data['gl_accounts']    = $this->acc_config->get_accounts();
                break;
            case 'currencies':
                $data['currencies']     = $this->acc_config->get_currencies();
                break;
            case 'payment_terms':
                $data['payment_terms']  = $this->acc_config->get_payment_terms(true);
                break;
            case 'fiscal_years':
                $data['fiscal_years']   = $this->acc_config->get_fiscal_years();
                break;
            case 'settings':
            default:
                $data['settings']       = $this->acc_config->get_all_settings();
                $data['gl_accounts']    = $this->acc_config->get_accounts();
                $data['currencies']     = $this->acc_config->get_currencies();
                $data['payment_terms']  = $this->acc_config->get_payment_terms();
                $data['lock_dates']     = $this->acc_config->get_lock_dates();
                break;
        }

        $this->load->view('accounting/xb/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('accounting/xb/config/' . $section, $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX ENDPOINTS
    // ─────────────────────────────────────────────────────────────────────────
    public function ajax($action = '')
    {
        if (!$this->input->is_ajax_request() && !in_array($action, ['report_data'])) {
            show_404();
        }
        header('Content-Type: application/json');

        switch ($action) {
            case 'search_accounts':
                $q = $this->input->get('q');
                echo json_encode($this->acc_config->search_accounts($q));
                break;

            case 'search_partners':
                $q    = $this->input->get('q');
                $type = $this->input->get('type'); // customer | vendor | any
                echo json_encode($this->acc_config->search_partners($q, $type));
                break;

            case 'get_account_balance':
                $id        = (int)$this->input->get('id');
                $date_from = $this->input->get('date_from');
                $date_to   = $this->input->get('date_to');
                echo json_encode($this->acc_engine->get_account_balance($id, $date_from, $date_to));
                break;

            case 'compute_totals':
                $post = $this->input->post(null, true);
                echo json_encode($this->acc_invoice->compute_totals($post));
                break;

            case 'get_tax_details':
                $ids = $this->input->get('ids');
                echo json_encode($this->acc_config->get_taxes_by_ids(explode(',', $ids)));
                break;

            case 'get_payment_terms':
                $id   = (int)$this->input->get('id');
                $amt  = (float)$this->input->get('amount');
                $date = $this->input->get('date');
                echo json_encode($this->acc_engine->compute_payment_terms($amt, $id, $date));
                break;

            case 'save_account':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $result = $this->acc_config->save_account($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'delete_account':
                $id = (int)$this->input->post('id');
                echo json_encode(['success' => $this->acc_config->delete_account($id)]);
                break;

            case 'save_tax':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $result = $this->acc_config->save_tax($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'delete_tax':
                $id = (int)$this->input->post('id');
                echo json_encode(['success' => $this->acc_config->delete_tax($id)]);
                break;

            case 'save_journal':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $result = $this->acc_journal->save($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'save_setting':
                $key = $this->input->post('key', true);
                $val = $this->input->post('value', true);
                echo json_encode(['success' => $this->acc_config->set_setting($key, $val)]);
                break;

            case 'get_move_lines':
                $move_id = (int)$this->input->get('move_id');
                echo json_encode($this->acc_engine->get_move_lines($move_id));
                break;

            case 'register_payment':
                $post = $this->input->post(null, true);
                try {
                    $pid = $this->acc_payment->register_payment($post);
                    echo json_encode(['success' => true, 'payment_id' => $pid]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;

            case 'report_data':
                $report    = $this->input->get('report');
                $date_from = $this->input->get('date_from') ?: date('Y-01-01');
                $date_to   = $this->input->get('date_to')   ?: date('Y-12-31');
                $params    = compact('date_from', 'date_to');
                echo json_encode($this->acc_report->get_report_data($report, $params));
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Unknown action']);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function _handle_config_post($section)
    {
        $post = $this->input->post(null, true);
        switch ($section) {
            case 'settings':
                foreach ($post as $k => $v) { $this->acc_config->set_setting($k, $v); }
                set_alert('success', 'Settings saved.');
                break;
            case 'chart_of_accounts':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->acc_config->save_account($post, $id);
                set_alert('success', 'Account saved.');
                break;
            case 'taxes':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->acc_config->save_tax($post, $id);
                set_alert('success', 'Tax saved.');
                break;
            case 'journals':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->acc_journal->save($post, $id);
                set_alert('success', 'Journal saved.');
                break;
            case 'payment_terms':
                $id = isset($post['id']) ? (int)$post['id'] : null;
                $this->acc_config->save_payment_term($post, $id);
                set_alert('success', 'Payment term saved.');
                break;
        }
        redirect(admin_url('xetuu_books/config/' . $section));
    }

    private function _report_title($report)
    {
        $titles = [
            'balance_sheet'   => 'Balance Sheet',
            'profit_loss'     => 'Profit and Loss',
            'general_ledger'  => 'General Ledger',
            'trial_balance'   => 'Trial Balance',
            'aged_receivable' => 'Aged Receivable',
            'aged_payable'    => 'Aged Payable',
            'tax_report'      => 'Tax Report',
            'cash_flow'       => 'Cash Flow Statement',
            'partner_ledger'  => 'Partner Ledger',
        ];
        return $titles[$report] ?? ucwords(str_replace('_', ' ', $report));
    }
}
