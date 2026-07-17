<?php defined('BASEPATH') or exit('No direct script access allowed');
$current_url = uri_string();
$seg1 = $this->uri->segment(3, '');
$seg2 = $this->uri->segment(4, '');

function xb_nav_active($seg, $values) {
    return in_array($seg, (array)$values) ? ' xb-nav-active' : '';
}
?>
<nav class="xb-nav">
  <div class="xb-nav-brand">
    <a href="<?php echo admin_url('xetuu_books'); ?>">
      <span class="xb-brand-icon">&#9783;</span> Xetuu Books
    </a>
  </div>
  <ul class="xb-nav-items">

    <!-- Dashboard -->
    <li class="xb-nav-item<?php echo ($seg1 === '' || $seg1 === 'index' || $seg1 === 'dashboard') ? ' xb-nav-active' : ''; ?>">
      <a href="<?php echo admin_url('xetuu_books'); ?>">Dashboard</a>
    </li>

    <!-- Customers -->
    <li class="xb-nav-item xb-has-dropdown<?php echo xb_nav_active($seg1, ['invoices', 'payments']); ?>">
      <a href="#" class="xb-nav-dropdown-toggle">Customers <span class="xb-caret">&#9660;</span></a>
      <ul class="xb-dropdown">
        <li><a href="<?php echo admin_url('xetuu_books/invoices'); ?>">Invoices</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/invoices/create'); ?>">New Invoice</a></li>
        <li class="xb-dropdown-divider"></li>
        <li><a href="<?php echo admin_url('xetuu_books/payments/customer'); ?>">Customer Payments</a></li>
      </ul>
    </li>

    <!-- Vendors -->
    <li class="xb-nav-item xb-has-dropdown<?php echo xb_nav_active($seg1, ['bills']); ?>">
      <a href="#" class="xb-nav-dropdown-toggle">Vendors <span class="xb-caret">&#9660;</span></a>
      <ul class="xb-dropdown">
        <li><a href="<?php echo admin_url('xetuu_books/bills'); ?>">Bills</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/bills/create'); ?>">New Bill</a></li>
        <li class="xb-dropdown-divider"></li>
        <li><a href="<?php echo admin_url('xetuu_books/payments/vendor'); ?>">Vendor Payments</a></li>
      </ul>
    </li>

    <!-- Accounting -->
    <li class="xb-nav-item xb-has-dropdown<?php echo xb_nav_active($seg1, ['journal_entries', 'reconcile']); ?>">
      <a href="#" class="xb-nav-dropdown-toggle">Accounting <span class="xb-caret">&#9660;</span></a>
      <ul class="xb-dropdown">
        <li><a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>">Journal Entries</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/journal_entry_form'); ?>">New Journal Entry</a></li>
        <li class="xb-dropdown-divider"></li>
        <li><a href="<?php echo admin_url('xetuu_books/reconcile'); ?>">Bank Reconciliation</a></li>
      </ul>
    </li>

    <!-- Reporting -->
    <li class="xb-nav-item xb-has-dropdown<?php echo ($seg1 === 'reports') ? ' xb-nav-active' : ''; ?>">
      <a href="#" class="xb-nav-dropdown-toggle">Reporting <span class="xb-caret">&#9660;</span></a>
      <ul class="xb-dropdown">
        <li><a href="<?php echo admin_url('xetuu_books/reports/balance_sheet'); ?>">Balance Sheet</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/profit_loss'); ?>">Profit &amp; Loss</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/general_ledger'); ?>">General Ledger</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/trial_balance'); ?>">Trial Balance</a></li>
        <li class="xb-dropdown-divider"></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>">Aged Receivable</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/aged_payable'); ?>">Aged Payable</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/tax_report'); ?>">Tax Report</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/reports/cash_flow'); ?>">Cash Flow</a></li>
      </ul>
    </li>

    <!-- Configuration -->
    <li class="xb-nav-item xb-has-dropdown<?php echo ($seg1 === 'config') ? ' xb-nav-active' : ''; ?>">
      <a href="#" class="xb-nav-dropdown-toggle">Configuration <span class="xb-caret">&#9660;</span></a>
      <ul class="xb-dropdown">
        <li><a href="<?php echo admin_url('xetuu_books/config/chart_of_accounts'); ?>">Chart of Accounts</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/config/journals'); ?>">Journals</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/config/taxes'); ?>">Taxes</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/config/payment_terms'); ?>">Payment Terms</a></li>
        <li><a href="<?php echo admin_url('xetuu_books/config/currencies'); ?>">Currencies</a></li>
        <li class="xb-dropdown-divider"></li>
        <li><a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Settings</a></li>
      </ul>
    </li>

  </ul>
  <div class="xb-nav-right">
    <a href="<?php echo admin_url(); ?>" class="xb-nav-back-link" title="Back to Perfex CRM">&#8592; CRM</a>
  </div>
</nav>
