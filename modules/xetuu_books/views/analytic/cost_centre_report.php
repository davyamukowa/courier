<?php defined('BASEPATH') or exit('No direct script access allowed');
$total_actual = array_sum(array_column($rows, 'actual'));
$total_budget = array_sum(array_column($rows, 'budget'));
$total_variance = $total_budget - $total_actual;
?>
<style>
.xb-cc-row-0 { background:#f0fdf4;font-weight:700;font-size:13px; }
.xb-cc-row-1 { background:#fff;font-weight:600;font-size:13px; }
.xb-cc-row-2 { background:#fafafa;font-size:13px; }
.xb-cc-row-3 { background:#fff;font-size:12px;color:#6b7280; }
.xb-cc-bar { height:8px;border-radius:4px;background:#e5e7eb;overflow:hidden; }
.xb-cc-bar-fill { height:100%;background:#1a6b3a;border-radius:4px;transition:width 0.4s; }
.xb-cc-bar-over { background:#dc2626 !important; }
.xb-var-pos { color:#16a34a;font-weight:600; }
.xb-var-neg { color:#dc2626;font-weight:600; }
</style>

<div class="row">
  <div class="col-md-12">

    <!-- Filter Bar -->
    <div class="xb-card" style="margin-bottom:16px;">
      <div class="xb-card-body" style="padding:14px 18px;">
        <form method="get" class="form-inline" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">PLAN / DIMENSION</label>
            <select name="plan_id" class="form-control form-control-sm" style="min-width:180px;">
              <option value="">All Plans</option>
              <?php foreach ($plans as $p): ?>
              <option value="<?php echo $p->id; ?>" <?php echo $filters['plan_id'] == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">DATE FROM</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $filters['date_from']; ?>">
          </div>
          <div class="form-group">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:3px;">DATE TO</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $filters['date_to']; ?>">
          </div>
          <button type="submit" class="btn btn-success btn-sm">Apply</button>
          <a href="<?php echo admin_url('xetuu_books/cost_centre_report'); ?>" class="btn btn-default btn-sm">Reset</a>
          <a href="<?php echo admin_url('xetuu_books/analytic_items?' . http_build_query($filters)); ?>" class="btn btn-default btn-sm"><i class="fa fa-list"></i> View Analytic Items</a>
        </form>
      </div>
    </div>

    <!-- Summary KPIs -->
    <div class="row" style="margin-bottom:16px;">
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #1a6b3a;">
          <div class="xb-card-body" style="padding:16px;">
            <div style="font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:600;">Total Actual Spend</div>
            <div style="font-size:24px;font-weight:700;color:#1a6b3a;"><?php echo xb_format_money($total_actual); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #0ea5e9;">
          <div class="xb-card-body" style="padding:16px;">
            <div style="font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:600;">Total Budget</div>
            <div style="font-size:24px;font-weight:700;color:#0ea5e9;"><?php echo xb_format_money($total_budget); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid <?php echo $total_variance >= 0 ? '#16a34a' : '#dc2626'; ?>;">
          <div class="xb-card-body" style="padding:16px;">
            <div style="font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:600;">Variance (Budget − Actual)</div>
            <div style="font-size:24px;font-weight:700;color:<?php echo $total_variance >= 0 ? '#16a34a' : '#dc2626'; ?>;">
              <?php echo ($total_variance >= 0 ? '+' : '') . xb_format_money($total_variance); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="xb-card" style="border-left:4px solid #f59e0b;">
          <div class="xb-card-body" style="padding:16px;">
            <div style="font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:600;">Cost Centres Active</div>
            <div style="font-size:24px;font-weight:700;color:#f59e0b;"><?php echo count(array_filter($rows, fn($r) => $r->actual != 0)); ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Tree Report -->
    <div class="xb-card">
      <div class="xb-card-header">
        <span>Cost Centre P&amp;L — <?php echo $filters['date_from']; ?> to <?php echo $filters['date_to']; ?></span>
      </div>
      <div class="xb-card-body" style="padding:0;">
        <table class="table" style="margin:0;font-size:13px;">
          <thead style="background:#1a6b3a;color:#fff;">
            <tr>
              <th style="color:#fff;padding:12px 16px;">Cost Centre / Analytic Account</th>
              <th style="color:#fff;">Code</th>
              <th style="color:#fff;">Plan</th>
              <th style="color:#fff;" class="text-right">Actual</th>
              <th style="color:#fff;" class="text-right">Budget</th>
              <th style="color:#fff;" class="text-right">Variance</th>
              <th style="color:#fff;" width="150">% Used</th>
              <th style="color:#fff;" class="text-right">Lines</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">
              No data found. Post transactions with analytic distribution to see reports here.
            </td></tr>
            <?php else: ?>
            <?php
            $current_plan = null;
            foreach ($rows as $row):
                // Plan group header
                if ($row->plan_name !== $current_plan):
                    $current_plan = $row->plan_name;
            ?>
            <tr style="background:#e5e7eb;">
              <td colspan="8" style="padding:8px 16px;font-size:12px;font-weight:700;text-transform:uppercase;color:#374151;letter-spacing:0.05em;">
                <i class="fa fa-th-large" style="margin-right:6px;"></i><?php echo htmlspecialchars($row->plan_name ?: 'No Plan'); ?>
              </td>
            </tr>
            <?php endif; ?>
            <?php
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $row->level);
                $pct_used = $row->budget > 0 ? min(100, ($row->actual / $row->budget) * 100) : 0;
                $over_budget = $row->budget > 0 && $row->actual > $row->budget;
                $row_class = 'xb-cc-row-' . min($row->level, 3);
            ?>
            <tr class="<?php echo $row_class; ?>" onclick="location.href='<?php echo admin_url('xetuu_books/analytic_items?account_id=' . $row->id . '&date_from=' . $filters['date_from'] . '&date_to=' . $filters['date_to']); ?>'" style="cursor:pointer;">
              <td style="padding-left:<?php echo 16 + $row->level * 20; ?>px;">
                <?php if ($row->level == 0): ?><i class="fa fa-building-o" style="color:#1a6b3a;margin-right:6px;"></i><?php endif; ?>
                <?php if ($row->level == 1): ?><i class="fa fa-map-marker" style="color:#0ea5e9;margin-right:6px;"></i><?php endif; ?>
                <?php if ($row->level >= 2): ?><span style="color:#9ca3af;margin-right:4px;">└</span><?php endif; ?>
                <?php echo htmlspecialchars($row->name); ?>
              </td>
              <td><code style="font-size:11px;"><?php echo htmlspecialchars($row->code ?? ''); ?></code></td>
              <td><small class="text-muted"><?php echo htmlspecialchars($row->plan_name ?? ''); ?></small></td>
              <td class="text-right <?php echo $row->actual != 0 ? 'xb-amount-pos' : 'text-muted'; ?>">
                <?php echo $row->actual != 0 ? xb_format_money($row->actual) : '—'; ?>
              </td>
              <td class="text-right text-muted">
                <?php echo $row->budget != 0 ? xb_format_money($row->budget) : '—'; ?>
              </td>
              <td class="text-right <?php echo $row->variance >= 0 ? 'xb-var-pos' : 'xb-var-neg'; ?>">
                <?php echo $row->budget != 0 ? (($row->variance >= 0 ? '+' : '') . xb_format_money($row->variance)) : '—'; ?>
              </td>
              <td>
                <?php if ($row->budget > 0): ?>
                <div class="xb-cc-bar">
                  <div class="xb-cc-bar-fill <?php echo $over_budget ? 'xb-cc-bar-over' : ''; ?>" style="width:<?php echo number_format($pct_used, 1); ?>%;"></div>
                </div>
                <small style="font-size:10px;color:<?php echo $over_budget ? '#dc2626' : '#6b7280'; ?>;">
                  <?php echo number_format($pct_used, 1); ?>%<?php echo $over_budget ? ' ⚠ Over budget' : ''; ?>
                </small>
                <?php else: ?>
                <small class="text-muted">No budget</small>
                <?php endif; ?>
              </td>
              <td class="text-right"><small class="text-muted"><?php echo $row->line_count ?: '—'; ?></small></td>
            </tr>
            <?php endforeach; ?>

            <!-- Grand Total Row -->
            <tr style="background:#1a6b3a;color:#fff;font-weight:700;">
              <td colspan="3" style="color:#fff;padding:12px 16px;"><i class="fa fa-sigma" style="margin-right:6px;"></i>Grand Total</td>
              <td class="text-right" style="color:#fff;"><?php echo xb_format_money($total_actual); ?></td>
              <td class="text-right" style="color:#fff;"><?php echo xb_format_money($total_budget); ?></td>
              <td class="text-right" style="color:<?php echo $total_variance >= 0 ? '#86efac' : '#fca5a5'; ?>;">
                <?php echo ($total_variance >= 0 ? '+' : '') . xb_format_money($total_variance); ?>
              </td>
              <td colspan="2"></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <p class="text-muted" style="margin-top:10px;font-size:12px;">
      <i class="fa fa-info-circle"></i>
      Click any row to drill down to individual analytic entries. Variance = Budget − Actual (positive = under budget, negative = over budget).
    </p>
  </div>
</div>
