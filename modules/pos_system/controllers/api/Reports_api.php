<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * All report endpoints require supervisor+ role.
 * Heavy reports are cached for 5 minutes.
 *
 * GET /api/reports/daily-sales        â†’ daily sales summary
 * GET /api/reports/products           â†’ sales by product
 * GET /api/reports/cashiers           â†’ sales by cashier
 * GET /api/reports/profit-loss        â†’ P&L
 * GET /api/reports/payments           â†’ payment method breakdown
 * GET /api/reports/hourly             â†’ hourly distribution
 * GET /api/reports/categories         â†’ category performance
 * GET /api/reports/sessions           â†’ session summaries
 * GET /api/reports/customers          â†’ top customers
 * GET /api/reports/tax                â†’ tax report
 * GET /api/reports/branches           â†’ multi-branch comparison (admin)
 * GET /api/reports/inventory          â†’ stock valuation
 * GET /api/reports/ledger             â†’ general ledger (admin)
 */
class Reports_api extends Pos_api
{
    protected int $cache_ttl = 300; // 5-minute cache

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_reports_model',    'pos_reports_model');
        $this->load->model('pos_system/Pos_inventory_model',  'pos_inventory_model');
        $this->load->model('pos_system/Pos_accounting_model', 'pos_accounting_model');
    }

    public function daily_sales()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $cache_key = "report_daily_{$branch_id}_{$from}_{$to}";
        $cached    = $this->cache_get($cache_key);

        if ($cached) {
            $this->ok($cached);
        }

        $data = $this->pos_reports_model->daily_sales($branch_id, $from, $to);
        $this->cache_set($cache_key, $data);
        $this->ok($data, 200, ['from' => $from, 'to' => $to, 'branch_id' => $branch_id]);
    }

    public function products()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $limit     = min(200, (int) ($this->input->get('limit') ?? 50));
        $cache_key = "report_products_{$branch_id}_{$from}_{$to}_{$limit}";

        if ($cached = $this->cache_get($cache_key)) {
            $this->ok($cached);
        }

        $data = $this->pos_reports_model->sales_by_product($branch_id, $from, $to, $limit);
        $this->cache_set($cache_key, $data);
        $this->ok($data, 200, ['from' => $from, 'to' => $to]);
    }

    public function cashiers()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->sales_by_cashier($branch_id, $from, $to);
        $this->ok($data, 200, ['from' => $from, 'to' => $to]);
    }

    public function profit_loss()
    {
        $this->require_auth();
        $this->require_role('manager');

        [$from, $to, $branch_id] = $this->_date_params();
        $cache_key = "report_pl_{$branch_id}_{$from}_{$to}";

        if ($cached = $this->cache_get($cache_key)) {
            $this->ok($cached);
        }

        $data = $this->pos_reports_model->profit_and_loss($branch_id, $from, $to);
        $this->cache_set($cache_key, $data);
        $this->ok($data);
    }

    public function payments()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->payment_method_breakdown($branch_id, $from, $to);
        $this->ok($data);
    }

    public function hourly()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->hourly_distribution($branch_id, $from, $to);
        $this->ok($data);
    }

    public function categories()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->category_performance($branch_id, $from, $to);
        $this->ok($data);
    }

    public function sessions()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->session_summary($branch_id, $from, $to);
        $this->ok($data);
    }

    public function customers()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        [$from, $to, $branch_id] = $this->_date_params();
        $limit = min(50, (int) ($this->input->get('limit') ?? 10));
        $data  = $this->pos_reports_model->top_customers($branch_id, $from, $to, $limit);
        $this->ok($data);
    }

    public function tax()
    {
        $this->require_auth();
        $this->require_role('manager');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_reports_model->tax_report($branch_id, $from, $to);
        $this->ok($data);
    }

    public function branches()
    {
        $this->require_auth();
        $this->require_role('admin');

        [$from, $to] = $this->_date_params();
        $data = $this->pos_reports_model->branch_comparison($from, $to);
        $this->ok($data);
    }

    public function inventory()
    {
        $this->require_auth();
        $this->require_role('manager');

        $data = $this->pos_inventory_model->valuation_report($this->auth_branch_id);
        $this->ok($data);
    }

    public function ledger()
    {
        $this->require_auth();
        $this->require_role('admin');

        [$from, $to, $branch_id] = $this->_date_params();
        $data = $this->pos_accounting_model->general_ledger($branch_id, $from, $to);
        $this->ok($data);
    }

    // â”€â”€â”€ Private â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function _date_params(): array
    {
        $from = $this->input->get('from') ?? date('Y-m-01');
        $to   = $this->input->get('to')   ?? date('Y-m-t');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $this->error('Invalid date format. Use YYYY-MM-DD.', 422, self::ERR_VALIDATION);
        }

        // Allow manager to query a specific branch
        $req_branch = (int) ($this->input->get('branch_id') ?? 0);
        $branch_id  = ($req_branch && ($this->auth_role === 'admin' || $req_branch === $this->auth_branch_id))
            ? $req_branch
            : $this->auth_branch_id;

        return [$from, $to, $branch_id];
    }
}
