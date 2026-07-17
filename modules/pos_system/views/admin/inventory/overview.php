<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'overview',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">

<!-- ══ Stat Cards ══════════════════════════════════════════════════════ -->
<div class="row" style="margin-bottom:20px">
<?php
$cards = [
    ['icon'=>'fa-cube',           'color'=>'#16a34a', 'bg'=>'rgba(22,163,74,.12)',   'label'=>'Total Products',     'value'=> number_format($stats['total_products'] ?? 0),    'link'=>admin_url('pos_system/inventory/items')],
    ['icon'=>'fa-check-circle',   'color'=>'#22c55e', 'bg'=>'rgba(34,197,94,.12)',   'label'=>'In-Stock SKUs',      'value'=> number_format($stats['in_stock'] ?? 0),          'link'=>admin_url('pos_system/inventory/stock_levels')],
    ['icon'=>'fa-exclamation-triangle','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.12)', 'label'=>'Low Stock',       'value'=> number_format($stats['low_stock'] ?? 0),         'link'=>admin_url('pos_system/inventory/stock_levels?status=low_stock')],
    ['icon'=>'fa-times-circle',   'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,.12)',   'label'=>'Out of Stock',       'value'=> number_format($stats['out_of_stock'] ?? 0),      'link'=>admin_url('pos_system/inventory/stock_levels?status=out_of_stock')],
    ['icon'=>'fa-truck-loading',  'color'=>'#8b5cf6', 'bg'=>'rgba(139,92,246,.12)',  'label'=>'Pending Receipts',   'value'=> number_format($stats['pending_receipts'] ?? 0),  'link'=>admin_url('pos_system/inventory/receiving')],
    ['icon'=>'fa-truck',          'color'=>'#14b8a6', 'bg'=>'rgba(20,184,166,.12)',  'label'=>'Pending Deliveries', 'value'=> number_format($stats['pending_deliveries'] ?? 0),'link'=>admin_url('pos_system/inventory/deliveries')],
];
foreach ($cards as $c): ?>
<div class="col-md-2 col-sm-4 col-xs-6">
  <a href="<?php echo $c['link'] ?? '#'; ?>" style="text-decoration:none">
    <div class="inv-stat">
      <div class="inv-stat-icon" style="background:<?php echo $c['bg']; ?>">
        <i class="fa <?php echo $c['icon']; ?>" style="color:<?php echo $c['color']; ?>;font-size:18px"></i>
      </div>
      <div class="inv-stat-val" style="color:<?php echo $c['color']; ?>"><?php echo $c['value']; ?></div>
      <div class="inv-stat-lbl"><?php echo $c['label']; ?></div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<!-- ══ Quick Actions + Warehouse Grid ═════════════════════════════════ -->
<div class="row" style="margin-bottom:20px">
  <div class="col-md-12">
    <div class="inv-card">
      <div class="inv-card-header">
        <h4 class="inv-card-title"><i class="fa fa-building"></i> Warehouse Overview</h4>
        <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>" class="btn-inv-primary" style="margin-right:6px">
          <i class="fa fa-plus"></i> New Receipt
        </a>
        <a href="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="btn-inv-secondary">
          <i class="fa fa-plus"></i> New Delivery
        </a>
      </div>
      <div style="padding:20px">
        <?php if (empty($branch_stats)): ?>
        <div class="inv-empty"><i class="fa fa-building"></i><p>No branches configured yet.</p></div>
        <?php else: ?>
        <div class="row">
          <?php foreach ($branch_stats as $bs): ?>
          <div class="col-md-4 col-sm-6" style="margin-bottom:16px">
            <div style="background:#f8fdf9;border:1px solid #e2ece6;border-radius:10px;overflow:hidden;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 6px 20px rgba(13,40,24,.10)'" onmouseout="this.style.boxShadow='none'">
              <!-- Branch header -->
              <div style="padding:14px 16px;background:linear-gradient(135deg,#b8ddc8,#9ecfb2);border-bottom:1px solid #7dba96;display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:9px;background:rgba(0,0,0,.12);display:flex;align-items:center;justify-content:center">
                  <i class="fa fa-warehouse" style="color:#0d2818;font-size:16px"></i>
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-weight:700;font-size:14px;color:#0d2818;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($bs['name']); ?></div>
                  <div style="font-size:11px;color:#2d4a38"><?php echo htmlspecialchars($bs['code'] ?? ''); ?></div>
                </div>
                <span class="ibadge <?php echo $bs['is_active'] ? 'ibadge-active' : 'ibadge-inactive'; ?>">
                  <?php echo $bs['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
              </div>
              <!-- Stats grid -->
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-bottom:1px solid #e8edf5">
                <div style="padding:12px 10px;text-align:center;border-right:1px solid #e8edf5">
                  <div style="font-size:20px;font-weight:800;color:#16a34a"><?php echo number_format($bs['total_skus']); ?></div>
                  <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px">SKUs</div>
                </div>
                <div style="padding:12px 10px;text-align:center;border-right:1px solid #e8edf5;background:<?php echo $bs['low_stock'] > 0 ? '#fffbf0' : 'transparent'; ?>">
                  <div style="font-size:20px;font-weight:800;color:<?php echo $bs['low_stock'] > 0 ? '#f59e0b' : '#22c55e'; ?>"><?php echo number_format($bs['low_stock']); ?></div>
                  <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px">Low Stock</div>
                </div>
                <div style="padding:12px 10px;text-align:center;background:<?php echo $bs['out_of_stock'] > 0 ? '#fff5f5' : 'transparent'; ?>">
                  <div style="font-size:20px;font-weight:800;color:<?php echo $bs['out_of_stock'] > 0 ? '#ef4444' : '#22c55e'; ?>"><?php echo number_format($bs['out_of_stock']); ?></div>
                  <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px">Out of Stock</div>
                </div>
              </div>
              <!-- Actions -->
              <div style="padding:10px 14px;display:flex;gap:6px;flex-wrap:wrap">
                <a href="<?php echo admin_url('pos_system/inventory/stock_levels?branch_id='.$bs['id']); ?>" class="btn-inv-secondary" style="padding:5px 12px;font-size:12px">
                  <i class="fa fa-list"></i> Stock
                </a>
                <a href="<?php echo admin_url('pos_system/inventory/receiving?branch_id='.$bs['id']); ?>" class="btn-inv-secondary" style="padding:5px 12px;font-size:12px">
                  <i class="fa fa-truck-loading"></i> Receive
                </a>
                <a href="<?php echo admin_url('pos_system/inventory/report_summary?branch_id='.$bs['id']); ?>" class="btn-inv-secondary" style="padding:5px 12px;font-size:12px">
                  <i class="fa fa-chart-bar"></i> Report
                </a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ══ Recent Activity + Low Stock ════════════════════════════════════ -->
<div class="row">
  <!-- Recent Receipts -->
  <div class="col-md-6">
    <div class="inv-card">
      <div class="inv-card-header">
        <h4 class="inv-card-title"><i class="fa fa-truck-loading"></i> Recent Receipts</h4>
        <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:600">View all &rarr;</a>
      </div>
      <?php if (empty($recent_receipts)): ?>
      <div class="inv-empty">
        <i class="fa fa-inbox"></i>
        <p>No receipts yet. <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>">Create one</a></p>
      </div>
      <?php else: ?>
      <table class="inv-table">
        <thead>
          <tr><th>REF #</th><th>SUPPLIER</th><th>DATE</th><th>STATUS</th></tr>
        </thead>
        <tbody>
        <?php foreach ($recent_receipts as $r): ?>
        <tr>
          <td>
            <a href="<?php echo admin_url('pos_system/inv_view/receipt/'.$r['id']); ?>" style="color:#16a34a;font-weight:600;text-decoration:none">
              <?php echo htmlspecialchars($r['ref_number'] ?? $r['receipt_number'] ?? '#'.$r['id']); ?>
            </a>
          </td>
          <td style="color:#64748b"><?php echo htmlspecialchars($r['supplier_name'] ?? '—'); ?></td>
          <td style="color:#64748b;font-size:12px"><?php echo date('d M Y', strtotime($r['date_created'] ?? $r['receipt_date'] ?? 'now')); ?></td>
          <td><span class="ibadge ibadge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Low / Out of Stock Alerts -->
  <div class="col-md-6">
    <div class="inv-card">
      <div class="inv-card-header">
        <h4 class="inv-card-title"><i class="fa fa-exclamation-triangle" style="color:#f59e0b"></i> Stock Alerts</h4>
        <a href="<?php echo admin_url('pos_system/inventory/stock_levels?status=low_stock'); ?>" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:600">View all &rarr;</a>
      </div>
      <?php if (empty($low_stock_items)): ?>
      <div class="inv-empty">
        <i class="fa fa-check-circle" style="color:#22c55e"></i>
        <p style="color:#22c55e;font-weight:600">All stock levels are healthy!</p>
      </div>
      <?php else: ?>
      <table class="inv-table">
        <thead>
          <tr><th>PRODUCT</th><th>BRANCH</th><th style="text-align:right">QTY</th><th style="text-align:right">MIN</th></tr>
        </thead>
        <tbody>
        <?php foreach ($low_stock_items as $item): ?>
        <?php $qty = (float)$item['quantity']; $is_out = $qty <= 0; ?>
        <tr style="background:<?php echo $is_out ? '#fff8f8' : '#fffdf5'; ?>">
          <td style="font-weight:600"><?php echo htmlspecialchars($item['name']); ?></td>
          <td style="color:#64748b;font-size:12px"><?php echo htmlspecialchars($item['branch_name']); ?></td>
          <td style="text-align:right">
            <span style="font-weight:700;color:<?php echo $is_out ? '#ef4444' : '#f59e0b'; ?>">
              <?php echo number_format($qty, 2); ?>
            </span>
          </td>
          <td style="text-align:right;color:#94a3b8;font-size:12px">
            <?php echo $item['reorder_point'] !== null ? number_format((float)$item['reorder_point'], 2) : '—'; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

</div><!-- /inv-content -->
</div><!-- /content -->
</div><!-- /wrapper -->
<?php init_tail(); ?>
