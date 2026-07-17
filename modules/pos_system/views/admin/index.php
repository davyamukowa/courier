<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
.pos-dash { padding: 0 0 40px; }

/* ── Quick stat cards ─────────────────────────────────────────────────── */
.dash-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
.dash-stat  {
  background: #fff; border: 1px solid #e8ecf0; border-radius: 10px;
  padding: 18px 20px; display: flex; align-items: center; gap: 14px;
}
.dash-stat-icon {
  width: 42px; height: 42px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.dash-stat-val  { font-size: 22px; font-weight: 800; color: #1a2332; line-height: 1; }
.dash-stat-lbl  { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-top: 3px; }

/* ── Quick-action bar ─────────────────────────────────────────────────── */
.dash-actions { display: flex; gap: 10px; margin-bottom: 28px; flex-wrap: wrap; }
.dash-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
  text-decoration: none; border: 1.5px solid; transition: all .15s; cursor: pointer;
}
.dash-btn-primary { background: #15803d; border-color: #15803d; color: #fff; }
.dash-btn-primary:hover { background: #166534; color: #fff; text-decoration: none; }
.dash-btn-outline { background: #fff; border-color: #e2e8f0; color: #475569; }
.dash-btn-outline:hover { background: #f8fafc; color: #1e293b; text-decoration: none; }

/* ── 2-col grid ───────────────────────────────────────────────────────── */
.dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }

/* ── Panel ────────────────────────────────────────────────────────────── */
.dash-panel {
  background: #fff; border: 1px solid #e8ecf0;
  border-radius: 12px; overflow: hidden;
}
.dash-panel-hdr {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 18px 22px 14px; border-bottom: 1px solid #f1f5f9;
}
.dash-panel-title { font-size: 15px; font-weight: 700; color: #1a2332; margin: 0 0 2px; }
.dash-panel-sub   { font-size: 11px; color: #94a3b8; }

/* Year selector */
.year-sel {
  border: 1px solid #e2e8f0; border-radius: 7px; padding: 5px 10px;
  font-size: 12px; font-weight: 600; color: #475569; background: #fff; cursor: pointer;
  appearance: none; -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 8px center; padding-right: 26px;
}

/* ── Table ────────────────────────────────────────────────────────────── */
.dash-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.dash-tbl thead tr { background: #f8fafc; }
.dash-tbl th {
  padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600;
  color: #94a3b8; text-transform: uppercase; letter-spacing: .05em;
  border-bottom: 1px solid #f1f5f9;
}
.dash-tbl th.r { text-align: right; }
.dash-tbl td { padding: 11px 16px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
.dash-tbl td.r { text-align: right; }
.dash-tbl tbody tr:last-child td { border-bottom: none; }
.dash-tbl tbody tr:hover td { background: #fafcff; }
.dash-tbl tfoot tr td {
  padding: 10px 16px; border-top: 1.5px solid #e8ecf0;
  font-weight: 700; font-size: 12px; background: #f8fafc;
}
.dash-tbl tfoot tr td.r { text-align: right; }

.td-branch   { font-weight: 600; color: #334155; }
.td-mono     { font-family: 'Courier New', monospace; font-weight: 600; color: #1a2332; }
.td-paid     { font-family: 'Courier New', monospace; font-weight: 600; color: #16a34a; }
.td-due      { font-family: 'Courier New', monospace; font-weight: 700; color: #ef4444; }
.td-nil      { color: #cbd5e1; }
.td-sl       { color: #94a3b8; font-weight: 500; width: 36px; }

.badge-expired  { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 2px 9px; font-size: 11px; font-weight: 700; }
.badge-ok       { color: #16a34a; font-weight: 600; font-size: 12px; }
.badge-staff    { background: #f1f5f9; color: #475569; border-radius: 10px; padding: 3px 12px; font-weight: 700; font-size: 13px; }

.empty-row td { padding: 28px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
</style>

<div id="wrapper">
<div class="content pos-dash">

  <!-- ── Quick stats ─────────────────────────────────────────────────── -->
  <div class="dash-stats">
    <div class="dash-stat">
      <div class="dash-stat-icon" style="background:#dcfce7;color:#16a34a"><i class="fa fa-shopping-cart"></i></div>
      <div>
        <div class="dash-stat-val"><?php echo $today_totals['count'] ?? 0; ?></div>
        <div class="dash-stat-lbl">Today's Sales</div>
      </div>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-icon" style="background:#dcfce7;color:#15803d"><i class="fa fa-money-bill-wave"></i></div>
      <div>
        <div class="dash-stat-val"><?php echo pos_format_currency($today_totals['total'] ?? 0); ?></div>
        <div class="dash-stat-lbl">Today's Revenue</div>
      </div>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-icon" style="background:<?php echo ($open_session ? '#dcfce7' : '#fee2e2'); ?>;color:<?php echo ($open_session ? '#16a34a' : '#dc2626'); ?>">
        <i class="fa fa-cash-register"></i>
      </div>
      <div>
        <div class="dash-stat-val" style="font-size:16px"><?php echo $open_session ? 'Open' : 'Closed'; ?></div>
        <div class="dash-stat-lbl">Session Status</div>
      </div>
    </div>
    <div class="dash-stat" style="justify-content:center">
      <a href="<?php echo admin_url('pos_system/terminal'); ?>" class="dash-btn dash-btn-primary" style="width:100%;justify-content:center;padding:11px">
        <i class="fa fa-cash-register"></i> Open POS Terminal
      </a>
    </div>
  </div>

  <!-- ── Quick actions ──────────────────────────────────────────────── -->
  <div class="dash-actions">
    <a href="<?php echo admin_url('pos_system/reports'); ?>" class="dash-btn dash-btn-outline"><i class="fa fa-bar-chart"></i> Reports</a>
    <a href="<?php echo admin_url('pos_system/sales_orders'); ?>" class="dash-btn dash-btn-outline"><i class="fa fa-file-invoice"></i> Invoices</a>
    <a href="<?php echo admin_url('pos_system/inventory'); ?>" class="dash-btn dash-btn-outline"><i class="fa fa-boxes"></i> Inventory</a>
    <a href="<?php echo admin_url('pos_system/branches'); ?>" class="dash-btn dash-btn-outline"><i class="fa fa-building"></i> Branches</a>
    <a href="<?php echo admin_url('pos_system/products'); ?>" class="dash-btn dash-btn-outline"><i class="fa fa-tag"></i> Products</a>
  </div>

  <?php
  $cur_year = date('Y');
  $year_options = '';
  foreach (range($cur_year, max(2023, $cur_year - 4)) as $y) {
    $sel = ($y == $report_year) ? ' selected' : '';
    $year_options .= "<option value=\"$y\"$sel>$y</option>";
  }
  $ys = '<form method="get" style="margin:0"><select name="year" class="year-sel" onchange="this.form.submit()">' . $year_options . '</select></form>';
  ?>

  <!-- ── Row 1 ──────────────────────────────────────────────────────── -->
  <div class="dash-grid">

    <!-- Branch Wise Sales -->
    <div class="dash-panel">
      <div class="dash-panel-hdr">
        <div>
          <div class="dash-panel-title">Branch Wise Sales</div>
          <div class="dash-panel-sub">Revenue breakdown by location</div>
        </div>
        <?php echo $ys; ?>
      </div>
      <table class="dash-tbl">
        <thead>
          <tr>
            <th style="width:36px">SL.</th>
            <th>Branch</th>
            <th class="r">Total Sales</th>
            <th class="r">Paid</th>
            <th class="r">Due</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($sales_rows)): ?>
          <tr class="empty-row"><td colspan="5">No sales data for <?php echo $report_year; ?></td></tr>
        <?php else: foreach ($sales_rows as $i => $r): ?>
          <tr>
            <td class="td-sl"><?php echo $i + 1; ?></td>
            <td>
              <div class="td-branch"><?php echo htmlspecialchars($r['branch_name']); ?></div>
              <div style="font-size:11px;color:#94a3b8"><?php echo number_format((int)$r['total_sales']); ?> transactions</div>
            </td>
            <td class="r td-mono"><?php echo pos_format_currency($r['total_amount']); ?></td>
            <td class="r td-paid"><?php echo pos_format_currency($r['paid']); ?></td>
            <td class="r">
              <?php if ((float)$r['due'] > 0): ?>
                <span class="td-due"><?php echo pos_format_currency($r['due']); ?></span>
              <?php else: ?><span class="td-nil">—</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <?php if (!empty($sales_rows)):
          $s_tot = array_sum(array_column($sales_rows,'total_amount'));
          $s_pai = array_sum(array_column($sales_rows,'paid'));
          $s_due = array_sum(array_column($sales_rows,'due'));
        ?>
        <tfoot>
          <tr>
            <td colspan="2">Total</td>
            <td class="r" style="color:#1a2332"><?php echo pos_format_currency($s_tot); ?></td>
            <td class="r" style="color:#16a34a"><?php echo pos_format_currency($s_pai); ?></td>
            <td class="r" style="color:#ef4444"><?php echo $s_due > 0 ? pos_format_currency($s_due) : '—'; ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

    <!-- Expired Product -->
    <div class="dash-panel">
      <div class="dash-panel-hdr">
        <div>
          <div class="dash-panel-title">Expired Product</div>
          <div class="dash-panel-sub">Stock past expiry date — requires action</div>
        </div>
      </div>
      <table class="dash-tbl">
        <thead>
          <tr>
            <th style="width:36px">SL.</th>
            <th>Branch</th>
            <th class="r">Qty</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($expired_rows)): ?>
          <tr class="empty-row"><td colspan="3">No branch data available</td></tr>
        <?php else:
          $any_exp = false;
          foreach ($expired_rows as $i => $r):
            $has = (float)$r['total_qty'] > 0;
            if ($has) $any_exp = true;
        ?>
          <tr>
            <td class="td-sl"><?php echo $i + 1; ?></td>
            <td class="td-branch"><?php echo htmlspecialchars($r['branch_name']); ?></td>
            <td class="r">
              <?php if ($has): ?>
                <span class="td-due"><?php echo number_format((float)$r['total_qty'], 0); ?></span>
              <?php else: ?><span class="td-nil">0</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach;
          if (!$any_exp): ?>
          <tr><td colspan="3" style="padding:14px 16px;text-align:center">
            <span class="badge-ok">&#10003; No expired stock</span>
          </td></tr>
        <?php endif; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Row 2 ──────────────────────────────────────────────────────── -->
  <div class="dash-grid">

    <!-- Branch Wise Purchases -->
    <div class="dash-panel">
      <div class="dash-panel-hdr">
        <div>
          <div class="dash-panel-title">Branch Wise Purchases</div>
          <div class="dash-panel-sub">Stock received by location</div>
        </div>
        <?php echo $ys; ?>
      </div>
      <table class="dash-tbl">
        <thead>
          <tr>
            <th style="width:36px">SL.</th>
            <th>Branch</th>
            <th class="r">Total Purchase</th>
            <th class="r">Paid</th>
            <th class="r">Due</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($purchase_rows)): ?>
          <tr class="empty-row"><td colspan="5">No purchase data for <?php echo $report_year; ?></td></tr>
        <?php else: foreach ($purchase_rows as $i => $r): ?>
          <tr>
            <td class="td-sl"><?php echo $i + 1; ?></td>
            <td>
              <div class="td-branch"><?php echo htmlspecialchars($r['branch_name']); ?></div>
              <div style="font-size:11px;color:#94a3b8"><?php echo number_format((int)$r['total_purchases']); ?> batch<?php echo (int)$r['total_purchases'] !== 1 ? 'es' : ''; ?></div>
            </td>
            <td class="r td-mono"><?php echo pos_format_currency($r['total_amount']); ?></td>
            <td class="r td-paid"><?php echo pos_format_currency($r['paid']); ?></td>
            <td class="r">
              <?php if ((float)$r['due'] > 0): ?>
                <span class="td-due"><?php echo pos_format_currency($r['due']); ?></span>
              <?php else: ?><span class="td-nil">—</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <?php if (!empty($purchase_rows)):
          $p_tot = array_sum(array_column($purchase_rows,'total_amount'));
          $p_pai = array_sum(array_column($purchase_rows,'paid'));
          $p_due = array_sum(array_column($purchase_rows,'due'));
        ?>
        <tfoot>
          <tr>
            <td colspan="2">Total</td>
            <td class="r" style="color:#1a2332"><?php echo pos_format_currency($p_tot); ?></td>
            <td class="r" style="color:#16a34a"><?php echo pos_format_currency($p_pai); ?></td>
            <td class="r" style="color:#ef4444"><?php echo $p_due > 0 ? pos_format_currency($p_due) : '—'; ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

    <!-- Employee Overview -->
    <div class="dash-panel">
      <div class="dash-panel-hdr">
        <div>
          <div class="dash-panel-title">Employee Overview</div>
          <div class="dash-panel-sub">Staff headcount by branch</div>
        </div>
      </div>
      <table class="dash-tbl">
        <thead>
          <tr>
            <th style="width:36px">SL.</th>
            <th>Branch</th>
            <th class="r">Staffs</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($staff_rows)): ?>
          <tr class="empty-row"><td colspan="3">No branch data available</td></tr>
        <?php else: foreach ($staff_rows as $i => $r): ?>
          <tr>
            <td class="td-sl"><?php echo $i + 1; ?></td>
            <td class="td-branch"><?php echo htmlspecialchars($r['branch_name']); ?></td>
            <td class="r"><span class="badge-staff"><?php echo (int)$r['total_staff']; ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <?php if (!empty($staff_rows)): ?>
        <tfoot>
          <tr>
            <td colspan="2">Total</td>
            <td class="r" style="color:#1a2332"><?php echo array_sum(array_column($staff_rows,'total_staff')); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

  </div><!-- row 2 -->

</div><!-- .pos-dash -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
