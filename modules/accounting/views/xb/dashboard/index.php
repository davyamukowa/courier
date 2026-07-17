<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-dashboard">

  <!-- KPI strip -->
  <div class="xb-kpi-row">
    <div class="xb-kpi xb-kpi-green">
      <div class="xb-kpi-label">Unpaid Invoices</div>
      <div class="xb-kpi-amount"><?php echo app_format_money($inv_stats['unpaid_amount'] ?? 0, get_base_currency()); ?></div>
      <div class="xb-kpi-meta"><?php echo (int)($inv_stats['unpaid_count'] ?? 0); ?> invoice(s)</div>
    </div>
    <div class="xb-kpi xb-kpi-red">
      <div class="xb-kpi-label">Overdue Invoices</div>
      <div class="xb-kpi-amount"><?php echo app_format_money($inv_stats['overdue_amount'] ?? 0, get_base_currency()); ?></div>
      <div class="xb-kpi-meta"><?php echo (int)($inv_stats['overdue_count'] ?? 0); ?> invoice(s)</div>
    </div>
    <div class="xb-kpi xb-kpi-blue">
      <div class="xb-kpi-label">Unpaid Bills</div>
      <div class="xb-kpi-amount"><?php echo app_format_money($bill_stats['unpaid_amount'] ?? 0, get_base_currency()); ?></div>
      <div class="xb-kpi-meta"><?php echo (int)($bill_stats['unpaid_count'] ?? 0); ?> bill(s)</div>
    </div>
    <div class="xb-kpi xb-kpi-orange">
      <div class="xb-kpi-label">Overdue Bills</div>
      <div class="xb-kpi-amount"><?php echo app_format_money($bill_stats['overdue_amount'] ?? 0, get_base_currency()); ?></div>
      <div class="xb-kpi-meta"><?php echo (int)($bill_stats['overdue_count'] ?? 0); ?> bill(s)</div>
    </div>
  </div>

  <!-- Bank accounts -->
  <?php if (!empty($bank_journals)): ?>
  <div class="xb-section">
    <h3 class="xb-section-title">Bank &amp; Cash Accounts</h3>
    <div class="xb-bank-cards">
      <?php foreach ($bank_journals as $bj): ?>
      <div class="xb-bank-card">
        <div class="xb-bank-name"><?php echo htmlspecialchars($bj->name); ?></div>
        <div class="xb-bank-balance <?php echo $bj->balance >= 0 ? 'positive' : 'negative'; ?>">
          <?php echo app_format_money($bj->balance, get_base_currency()); ?>
        </div>
        <a href="<?php echo admin_url('xetuu_books/reconcile?journal_id=' . $bj->id); ?>" class="xb-bank-link">Reconcile</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recent invoices -->
  <div class="xb-section-grid">
    <div class="xb-section">
      <div class="xb-section-header">
        <h3 class="xb-section-title">Recent Invoices</h3>
        <a href="<?php echo admin_url('xetuu_books/invoices'); ?>" class="xb-link-more">View all</a>
      </div>
      <table class="xb-table">
        <thead>
          <tr><th>Number</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($recent_invoices)): ?>
          <tr><td colspan="5" class="xb-empty">No invoices yet.</td></tr>
          <?php else: foreach ($recent_invoices as $inv): ?>
          <tr>
            <td><a href="<?php echo admin_url('xetuu_books/invoice_form/' . $inv->id); ?>"><?php echo htmlspecialchars($inv->name ?: '(Draft)'); ?></a></td>
            <td><?php echo htmlspecialchars($inv->partner_name ?? ''); ?></td>
            <td><?php echo $inv->date ? _d($inv->date) : '—'; ?></td>
            <td><?php echo app_format_money($inv->amount_total, get_base_currency()); ?></td>
            <td><span class="xb-badge xb-badge-<?php echo $inv->payment_state; ?>"><?php echo ucfirst(str_replace('_', ' ', $inv->payment_state ?? $inv->state)); ?></span></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="xb-section">
      <div class="xb-section-header">
        <h3 class="xb-section-title">Recent Bills</h3>
        <a href="<?php echo admin_url('xetuu_books/bills'); ?>" class="xb-link-more">View all</a>
      </div>
      <table class="xb-table">
        <thead>
          <tr><th>Number</th><th>Vendor</th><th>Due</th><th>Amount</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($recent_bills)): ?>
          <tr><td colspan="5" class="xb-empty">No bills yet.</td></tr>
          <?php else: foreach ($recent_bills as $bill): ?>
          <tr>
            <td><a href="<?php echo admin_url('xetuu_books/bill_form/' . $bill->id); ?>"><?php echo htmlspecialchars($bill->name ?: '(Draft)'); ?></a></td>
            <td><?php echo htmlspecialchars($bill->partner_name ?? ''); ?></td>
            <td><?php echo $bill->invoice_date_due ? _d($bill->invoice_date_due) : '—'; ?></td>
            <td><?php echo app_format_money($bill->amount_total, get_base_currency()); ?></td>
            <td><span class="xb-badge xb-badge-<?php echo $bill->payment_state; ?>"><?php echo ucfirst(str_replace('_', ' ', $bill->payment_state ?? $bill->state)); ?></span></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
