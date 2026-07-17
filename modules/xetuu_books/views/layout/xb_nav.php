<?php defined('BASEPATH') or exit('No direct script access allowed');
$page = isset($xb_page) ? $xb_page : '';

$in_customers = in_array($page, ['invoices','credit_notes','payments_customer','customers']);
$in_vendors   = in_array($page, ['bills','refunds','payments_vendor','expenses','suppliers']);
$in_acct      = in_array($page, ['journal_entries','reconcile','budgets','assets','deferred']);
$in_reports   = ($page === 'reports');
$in_config    = ($page === 'config');
?>
<style>
/* ── Mega-menu overrides ──────────────────────────────── */
.xb-navbar .dropdown-menu { min-width: 200px; }
.xb-navbar .mega-menu {
    position: static !important;
}
.xb-navbar .mega-menu > .dropdown-menu {
    width: 620px;
    padding: 16px;
    left: auto;
    right: 0;
    display: none;
}
.xb-navbar .mega-menu.open > .dropdown-menu { display: block; }
.xb-mega-cols { display: flex; gap: 12px; }
.xb-mega-col { flex: 1; min-width: 150px; }
.xb-mega-col .dropdown-header {
    padding: 4px 8px 4px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #888;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 4px;
}
.xb-mega-col li > a {
    padding: 5px 8px !important;
    font-size: 13px;
    color: #1f2937 !important;
    white-space: normal;
}
.xb-mega-col li > a:hover { background: #f0fdf4 !important; color: #1a6b3a !important; }
@media (max-width: 900px) {
    .xb-navbar .mega-menu > .dropdown-menu { width: auto; }
    .xb-mega-cols { flex-direction: column; }
}
</style>

<nav class="navbar xb-navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed"
                    data-toggle="collapse" data-target="#xb-navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo admin_url('xetuu_books'); ?>"
               style="color:#fff;font-weight:700;font-size:18px;letter-spacing:-.3px;">
                <i class="fa fa-book"></i> Xetuu Books
            </a>
        </div>

        <div class="collapse navbar-collapse" id="xb-navbar-collapse">
            <ul class="nav navbar-nav">

                <!-- Dashboard -->
                <li class="<?php echo ($page=='dashboard') ? 'active' : ''; ?>">
                    <a href="<?php echo admin_url('xetuu_books'); ?>"><i class="fa fa-tachometer"></i> Dashboard</a>
                </li>

                <!-- ── CUSTOMERS ─────────────────────────────────────────── -->
                <li class="dropdown <?php echo $in_customers ? 'active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Customers <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">Transactions</li>
                        <li><a href="<?php echo admin_url('xetuu_books/invoices'); ?>"><i class="fa fa-file-text-o fa-fw"></i> Invoices</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/credit_notes'); ?>"><i class="fa fa-undo fa-fw"></i> Credit Notes</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/payments/customer'); ?>"><i class="fa fa-money fa-fw"></i> Payments</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Collections</li>
                        <li><a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>"><i class="fa fa-clock-o fa-fw"></i> Follow-up / Aged Report</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Master Data</li>
                        <li><a href="<?php echo admin_url('xetuu_books/customers'); ?>"><i class="fa fa-users fa-fw"></i> Customers</a></li>
                        <li><a href="<?php echo admin_url('items'); ?>"><i class="fa fa-cubes fa-fw"></i> Products</a></li>
                    </ul>
                </li>

                <!-- ── VENDORS ───────────────────────────────────────────── -->
                <li class="dropdown <?php echo $in_vendors ? 'active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Vendors <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">Transactions</li>
                        <li><a href="<?php echo admin_url('xetuu_books/bills'); ?>"><i class="fa fa-file-text fa-fw"></i> Bills</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/refunds'); ?>"><i class="fa fa-rotate-left fa-fw"></i> Refunds / Credit Notes</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/payments/vendor'); ?>"><i class="fa fa-money fa-fw"></i> Payments</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/expenses'); ?>"><i class="fa fa-ticket fa-fw"></i> Employee Expenses</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Master Data</li>
                        <li><a href="<?php echo admin_url('xetuu_books/suppliers'); ?>"><i class="fa fa-truck fa-fw"></i> Suppliers</a></li>
                    </ul>
                </li>

                <!-- ── ACCOUNTING ────────────────────────────────────────── -->
                <li class="dropdown <?php echo $in_acct ? 'active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Accounting <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">Journals</li>
                        <li><a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>"><i class="fa fa-book fa-fw"></i> Journal Entries</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Management</li>
                        <li><a href="<?php echo admin_url('xetuu_books/budgets'); ?>"><i class="fa fa-bar-chart fa-fw"></i> Budgets</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/assets'); ?>"><i class="fa fa-building-o fa-fw"></i> Fixed Assets</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/deferred?type=revenue'); ?>"><i class="fa fa-calendar-o fa-fw"></i> Deferred Revenues</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/deferred?type=expense'); ?>"><i class="fa fa-calendar fa-fw"></i> Deferred Expenses</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Actions</li>
                        <li><a href="<?php echo admin_url('xetuu_books/reconcile'); ?>"><i class="fa fa-exchange fa-fw"></i> Reconciliation</a></li>
                        <li><a href="<?php echo admin_url('xetuu_books/config/fiscal_years'); ?>"><i class="fa fa-lock fa-fw"></i> Lock Dates</a></li>
                    </ul>
                </li>

                <!-- ── REPORTING (mega-menu) ─────────────────────────────── -->
                <li class="dropdown mega-menu <?php echo $in_reports ? 'active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Reporting <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <div class="xb-mega-cols">
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Financial Statements</li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/profit_loss'); ?>">Profit &amp; Loss</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/balance_sheet'); ?>">Balance Sheet</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/cash_flow'); ?>">Cash Flow Statement</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/executive_summary'); ?>">Executive Summary</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/tax_report'); ?>">Tax Report</a></li>
                            </div>
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Audit Reports</li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/general_ledger'); ?>">General Ledger</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/trial_balance'); ?>">Trial Balance</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/journal_report'); ?>">Journal Report</a></li>
                            </div>
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Partner Reports</li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/partner_ledger'); ?>">Partner Ledger</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>">Aged Receivable</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/aged_payable'); ?>">Aged Payable</a></li>
                            </div>
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Management</li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/invoice_analysis'); ?>">Invoice Analysis</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/reports/depreciation_schedule'); ?>">Depreciation Schedule</a></li>
                            </div>
                        </div>
                    </ul>
                </li>

                <!-- ── CONFIGURATION (mega-menu) ─────────────────────────── -->
                <li class="dropdown mega-menu <?php echo $in_config ? 'active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        Configuration <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <div class="xb-mega-cols">
                            <div class="xb-mega-col">
                                <li class="dropdown-header">General</li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Settings</a></li>
                                <li class="dropdown-header" style="margin-top:8px;">Invoicing</li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/payment_terms'); ?>">Payment Terms</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/follow_up_levels'); ?>">Follow-up Levels</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/stamp'); ?>">Stamp Configuration</a></li>
                            </div>
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Accounting</li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/chart_of_accounts'); ?>">Chart of Accounts</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/taxes'); ?>">Taxes</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/journals'); ?>">Journals</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/currencies'); ?>">Currencies</a></li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/fiscal_years'); ?>">Fiscal Years</a></li>
                            </div>
                            <div class="xb-mega-col">
                                <li class="dropdown-header">Management</li>
                                <li><a href="<?php echo admin_url('xetuu_books/config/asset_models'); ?>">Asset Models</a></li>
                            </div>
                        </div>
                    </ul>
                </li>

            </ul>

            <!-- Right side: quick actions -->
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="<?php echo admin_url('xetuu_books/invoice_form'); ?>"
                       style="background: rgba(255,255,255,.15); border-radius: 4px; margin: 8px 0; padding: 6px 12px;">
                        <i class="fa fa-plus"></i> New Invoice
                    </a>
                </li>
            </ul>
        </div><!-- /#xb-navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<script>
// Mega-menu: keep dropdown open on click (Bootstrap 3 fix for nested divs)
$(document).on('click', '.mega-menu .dropdown-menu', function(e){ e.stopPropagation(); });
</script>
