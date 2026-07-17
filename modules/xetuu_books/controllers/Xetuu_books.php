<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/traits/Xb_transactions_trait.php';
require_once __DIR__ . '/traits/Xb_payments_trait.php';
require_once __DIR__ . '/traits/Xb_journals_trait.php';
require_once __DIR__ . '/traits/Xb_reports_trait.php';
require_once __DIR__ . '/traits/Xb_management_trait.php';
require_once __DIR__ . '/traits/Xb_config_trait.php';
require_once __DIR__ . '/traits/Xb_ajax_trait.php';
require_once __DIR__ . '/traits/Xb_vendors_trait.php';
require_once __DIR__ . '/traits/Xb_analytic_trait.php';

class Xetuu_books extends AdminController
{
    use Xb_transactions_trait;
    use Xb_payments_trait;
    use Xb_journals_trait;
    use Xb_reports_trait;
    use Xb_management_trait;
    use Xb_config_trait;
    use Xb_ajax_trait;
    use Xb_vendors_trait;
    use Xb_analytic_trait;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('xetuu_books/Xb_engine_model',   'xb_engine');
        $this->load->model('xetuu_books/Xb_invoice_model',  'xb_invoice');
        $this->load->model('xetuu_books/Xb_payment_model',  'xb_payment');
        $this->load->model('xetuu_books/Xb_batch_payment_model', 'xb_batch');
        $this->load->model('xetuu_books/Xb_journal_model',  'xb_journal');
        $this->load->model('xetuu_books/Xb_report_model',   'xb_report');
        $this->load->model('xetuu_books/Xb_config_model',   'xb_config');
        $this->load->model('xetuu_books/Xb_analytic_model', 'xb_analytic');
        $this->load->helper('xetuu_books/xb');
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        if (!has_permission('accounting_dashboard', '', 'view')) { access_denied('xetuu_books'); }

        $data['title']           = 'Xetuu Books — Dashboard';
        $data['inv_stats']       = $this->xb_invoice->get_dashboard_stats();
        $data['bill_stats']      = $this->xb_invoice->get_bill_dashboard_stats();
        $data['bank_journals']   = $this->xb_journal->get_dashboard_journals();
        $data['recent_invoices'] = $this->xb_invoice->get_list('out_invoice', ['limit' => 5]);
        $data['recent_bills']    = $this->xb_invoice->get_list('in_invoice',  ['limit' => 5]);
        $data['financials']      = $this->xb_report->get_dashboard_financials();
        $data['xb_page']         = 'dashboard';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/dashboard/index', $data, true),
        ]));
    }

    public function get_all_customers()
    {
        $this->load->model('clients_model');
        $this->db->select('userid, company');
        $clients = $this->db->get(db_prefix() . 'clients')->result_array();
        echo json_encode($clients);
    }

    public function quick_add_customer()
    {
        $company = $this->input->post('company');
        if ($company) {
            $this->load->model('clients_model');
            $id = $this->clients_model->add(['company' => $company]);
            if ($id) {
                echo json_encode(['success' => true, 'id' => $id]);
                return;
            }
        }
        echo json_encode(['success' => false]);
    }

    public function products_vendors()
    {
        $this->products();
    }

    public function journal_items()
    {
        $this->journal_entries();
    }

    public function auto_transfers()
    {
        $this->journal_entries();
    }

    public function reconciliation()
    {
        $this->reconcile();
    }

    public function budgets_assets()
    {
        $this->budgets();
    }

    public function deferred_rev_exp()
    {
        $this->deferred();
    }

    public function lock_dates()
    {
        $this->config('settings');
    }
}
