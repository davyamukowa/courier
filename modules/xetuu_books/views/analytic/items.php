<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-analytic-badge { display:inline-block;padding:2px 7px;border-radius:10px;font-size:11px;font-weight:600;color:#fff;margin-right:3px; }
.xb-amount-pos { color:#16a34a;font-weight:600; }
.xb-amount-neg { color:#dc2626;font-weight:600; }
</style>

<div class="row">
  <div class="col-md-12">

    <!-- Filter Bar -->
    <div class="xb-card" style="margin-bottom:16px;">
      <div class="xb-card-body" style="padding:14px 18px;">
        <form method="get" class="form-inline" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">DATE FROM</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $filters['date_from']; ?>">
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">DATE TO</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $filters['date_to']; ?>">
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">PLAN</label>
            <select name="plan_id" class="form-control form-control-sm" style="min-width:160px;">
              <option value="">All Plans</option>
              <?php foreach ($plans as $p): ?>
              <option value="<?php echo $p->id; ?>" <?php echo $filters['plan_id'] == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">ACCOUNT</label>
            <select name="account_id" class="form-control form-control-sm selectpicker" data-live-search="true" style="min-width:200px;">
              <option value="">All Accounts</option>
              <?php foreach ($accounts as $a): ?>
              <option value="<?php echo $a->id; ?>" <?php echo $filters['analytic_account_id'] == $a->id ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($a->complete_name ?: $a->name); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">SEARCH</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Description, reference…" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
          </div>
          <button type="submit" class="btn btn-success btn-sm">Filter</button>
          <a href="<?php echo admin_url('xetuu_books/analytic_items'); ?>" class="btn btn-default btn-sm">Reset</a>
        </form>
      </div>
    </div>

    <!-- Summary Cards -->
    <?php
    $total_amount = array_sum(array_column($lines, 'amount'));
    $unique_accounts = count(array_unique(array_column($lines, 'analytic_account_id')));
    ?>
    <div class="row" style="margin-bottom:16px;">
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #1a6b3a;">
          <div class="xb-card-body" style="padding:14px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#1a6b3a;"><?php echo count($lines); ?></div>
            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;">Total Entries</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #0ea5e9;">
          <div class="xb-card-body" style="padding:14px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#0ea5e9;"><?php echo $unique_accounts; ?></div>
            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;">Accounts Used</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid <?php echo $total_amount >= 0 ? '#16a34a' : '#dc2626'; ?>;">
          <div class="xb-card-body" style="padding:14px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:<?php echo $total_amount >= 0 ? '#16a34a' : '#dc2626'; ?>;"><?php echo xb_format_money(abs($total_amount)); ?></div>
            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;">Total Amount</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #f59e0b;">
          <div class="xb-card-body" style="padding:14px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#f59e0b;"><?php echo $filters['date_from']; ?></div>
            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;">to <?php echo $filters['date_to']; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table -->
    <div class="xb-card">
      <div class="xb-card-header d-flex justify-content-between align-items-center">
        <span>Analytic Items — <?php echo count($lines); ?> entries</span>
        <a href="<?php echo admin_url('xetuu_books/cost_centre_report?' . http_build_query(['date_from' => $filters['date_from'], 'date_to' => $filters['date_to']])); ?>" class="btn btn-default btn-sm">
          <i class="fa fa-bar-chart"></i> Cost Centre Report
        </a>
      </div>
      <div class="xb-card-body" style="padding:0;">
        <table class="table table-hover" style="margin:0;font-size:13px;">
          <thead style="background:#f9fafb;">
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Analytic Account</th>
              <th>Plan</th>
              <th>Document</th>
              <th>Partner</th>
              <th class="text-right">%</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($lines)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">
              No analytic entries found for this period.<br>
              <small>Entries are created automatically when you post bills, invoices, or receipts with analytic distribution assigned.</small>
            </td></tr>
            <?php else: ?>
            <?php foreach ($lines as $line): ?>
            <tr>
              <td style="white-space:nowrap;"><?php echo _d($line->date); ?></td>
              <td><?php echo htmlspecialchars($line->name); ?><?php if ($line->ref): ?><br><small class="text-muted"><?php echo htmlspecialchars($line->ref); ?></small><?php endif; ?></td>
              <td>
                <strong><?php echo htmlspecialchars($line->account_code ? '[' . $line->account_code . '] ' : ''); ?><?php echo htmlspecialchars($line->account_name); ?></strong>
              </td>
              <td>
                <?php if ($line->plan_name): ?>
                <span class="xb-analytic-badge" style="background:<?php echo htmlspecialchars($line->plan_color ?? '#6b7280'); ?>;"><?php echo htmlspecialchars($line->plan_name); ?></span>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td>
                <?php if ($line->move_id): ?>
                <a href="<?php echo admin_url('xetuu_books/' . ($line->move_type === 'in_invoice' ? 'bill_form' : ($line->move_type === 'out_invoice' ? 'invoice_form' : 'vendor_receipt')) . '/' . $line->move_id); ?>">
                  <?php echo htmlspecialchars($line->move_name ?: '#' . $line->move_id); ?>
                </a>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($line->partner_name ?? '—'); ?></td>
              <td class="text-right"><small class="text-muted"><?php echo number_format($line->percentage, 0); ?>%</small></td>
              <td class="text-right <?php echo $line->amount >= 0 ? 'xb-amount-pos' : 'xb-amount-neg'; ?>">
                <?php echo xb_format_money($line->amount); ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <?php if (!empty($lines)): ?>
          <tfoot style="background:#f9fafb;">
            <tr>
              <td colspan="7" class="text-right"><strong>Total</strong></td>
              <td class="text-right <?php echo $total_amount >= 0 ? 'xb-amount-pos' : 'xb-amount-neg'; ?>">
                <strong><?php echo xb_format_money($total_amount); ?></strong>
              </td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>
