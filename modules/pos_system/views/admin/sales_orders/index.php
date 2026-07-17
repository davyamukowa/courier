<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$status_map = [
    'draft'               => ['label'=>'Draft',               'color'=>'#6b7280','bg'=>'#f3f4f6'],
    'confirmed'           => ['label'=>'Confirmed',           'color'=>'#d97706','bg'=>'#fef9c3'],
    'processing'          => ['label'=>'Processing',          'color'=>'#2563eb','bg'=>'#dbeafe'],
    'partially_delivered' => ['label'=>'Part. Delivered',     'color'=>'#7c3aed','bg'=>'#ede9fe'],
    'delivered'           => ['label'=>'Delivered',           'color'=>'#16a34a','bg'=>'#dcfce7'],
    'cancelled'           => ['label'=>'Cancelled',           'color'=>'#dc2626','bg'=>'#fee2e2'],
];
function so_badge($s) {
    global $status_map;
    $m = $status_map[$s] ?? ['label'=>ucfirst($s),'color'=>'#6b7280','bg'=>'#f3f4f6'];
    return '<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;background:'.$m['bg'].';color:'.$m['color'].'">'.$m['label'].'</span>';
}
?>
<style>
/* ── POS Invoices / Sales Orders ── Green · White · Black palette */
.poi-wrap     { background:#f0f4f0; min-height:calc(100vh - 60px); }
.poi-topbar   { background:#111827; border-bottom:3px solid #16a34a; padding:14px 20px; display:flex; align-items:center; gap:12px; }
.poi-topbar-title { font-size:18px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px; }
.poi-topbar-title i { color:#16a34a; }
.poi-topbar-sub   { font-size:12px; color:#6b7280; }
.poi-btn-so   { display:inline-flex; align-items:center; gap:7px; padding:7px 16px; background:#1f2937; border:1.5px solid #374151; color:#d1fae5; border-radius:7px; font-size:13px; font-weight:600; text-decoration:none; transition:.15s; }
.poi-btn-so:hover { background:#374151; color:#fff; text-decoration:none; }
.poi-btn-new  { display:inline-flex; align-items:center; gap:7px; padding:7px 18px; background:#16a34a; border:none; color:#fff; border-radius:7px; font-size:13px; font-weight:700; text-decoration:none; transition:.15s; cursor:pointer; }
.poi-btn-new:hover { background:#15803d; color:#fff; text-decoration:none; }
.poi-btn-sec  { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; background:#fff; border:1.5px solid #d1d5db; color:#374151; border-radius:7px; font-size:13px; font-weight:600; text-decoration:none; transition:.15s; cursor:pointer; }
.poi-btn-sec:hover { background:#f9fafb; text-decoration:none; }

.poi-stats    { display:flex; gap:10px; padding:16px 20px; overflow-x:auto; background:#fff; border-bottom:1px solid #e5e7eb; }
.poi-stat-pill { flex-shrink:0; display:flex; align-items:center; gap:8px; padding:8px 16px; border-radius:30px; border:2px solid transparent; text-decoration:none; transition:.15s; cursor:pointer; }
.poi-stat-pill:hover { opacity:.85; text-decoration:none; }
.poi-stat-pill.active { border-color:#16a34a !important; }
.poi-stat-label { font-size:12px; font-weight:600; }
.poi-stat-count { font-size:12px; font-weight:800; }

.poi-toolbar  { padding:12px 20px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.poi-input    { border:1.5px solid #d1d5db; border-radius:7px; padding:7px 12px; font-size:13px; color:#111827; background:#fff; outline:none; }
.poi-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.1); }
.poi-select   { border:1.5px solid #d1d5db; border-radius:7px; padding:7px 30px 7px 10px; font-size:13px; color:#111827; background:#fff; cursor:pointer; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b7280'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; }
.poi-select:focus { border-color:#16a34a; }

.poi-table-wrap { overflow:auto; }
.poi-table    { width:100%; border-collapse:collapse; font-size:13px; }
.poi-table thead tr { background:#111827; }
.poi-table th { padding:10px 14px; text-align:left; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; border-bottom:none; white-space:nowrap; }
.poi-table th.r { text-align:right; }
.poi-table td { padding:11px 14px; border-bottom:1px solid #f3f4f6; vertical-align:middle; color:#111827; }
.poi-table td.r { text-align:right; }
.poi-table tbody tr { cursor:pointer; transition:.1s; }
.poi-table tbody tr:hover td { background:#f0fdf4; }
.poi-table tbody tr.so-active td { background:#dcfce7; }
.poi-table tbody tr.so-active td:first-child { border-left:3px solid #16a34a; }
.poi-empty    { text-align:center; padding:60px 20px; color:#9ca3af; }
.poi-empty i  { font-size:36px; color:#d1d5db; display:block; margin-bottom:12px; }
.poi-footer   { padding:10px 20px; background:#fff; border-top:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; font-size:12px; color:#6b7280; }
.poi-amnt     { font-family:'Courier New',monospace; font-weight:700; color:#111827; }
.poi-amnt-g   { font-family:'Courier New',monospace; font-weight:700; color:#16a34a; }
.split-detail { display:flex; flex-direction:column; background:#fff; border-left:1px solid #e5e7eb; min-height:400px; }
</style>

<div id="wrapper">
<div class="poi-wrap">

  <!-- ── Top bar ── -->
  <div class="poi-topbar">
    <div style="flex:1">
      <div class="poi-topbar-title"><i class="fa fa-file-invoice"></i> Invoices</div>
      <div class="poi-topbar-sub">Sales records from the POS terminal</div>
    </div>
    <!-- Sales Orders button — links to the full order management page -->
    <a href="<?php echo admin_url('pos_system/invoices'); ?>" class="poi-btn-so">
      <i class="fa fa-clipboard-list"></i> Sales Orders
    </a>
    <a href="<?php echo admin_url('pos_system/so_form'); ?>" class="poi-btn-new">
      <i class="fa fa-plus"></i> New Invoice
    </a>
  </div>

  <!-- ── Status stat pills ── -->
  <div class="poi-stats">
    <a href="<?php echo admin_url('pos_system/sales_orders'); ?>"
       class="poi-stat-pill<?php echo !$current_status ? ' active' : ''; ?>"
       style="background:#111827;border-color:<?php echo !$current_status ? '#16a34a' : '#374151'; ?>">
      <span class="poi-stat-label" style="color:#9ca3af">All</span>
      <span class="poi-stat-count" style="color:#fff"><?php echo $total_count; ?></span>
    </a>
    <?php foreach ($status_map as $st => $info):
      $cnt = $stats[$st] ?? 0;
      $active = $current_status === $st;
    ?>
    <a href="<?php echo admin_url('pos_system/sales_orders?status='.$st); ?>"
       class="poi-stat-pill<?php echo $active ? ' active' : ''; ?>"
       style="background:<?php echo $info['bg']; ?>;border-color:<?php echo $active ? $info['color'] : 'transparent'; ?>">
      <span class="poi-stat-label" style="color:<?php echo $info['color']; ?>"><?php echo $info['label']; ?></span>
      <span class="poi-stat-count" style="color:<?php echo $info['color']; ?>"><?php echo $cnt; ?></span>
    </a>
    <?php endforeach; ?>
    <!-- Total amount pill -->
    <div class="poi-stat-pill" style="background:#f0fdf4;border-color:transparent;margin-left:auto">
      <span class="poi-stat-label" style="color:#16a34a">Total Delivered</span>
      <span class="poi-stat-count poi-amnt-g"><?php echo number_format($totals['delivered'] ?? 0, 2); ?></span>
    </div>
  </div>

  <!-- ── Toolbar ── -->
  <div class="poi-toolbar">
    <form method="GET" action="<?php echo admin_url('pos_system/sales_orders'); ?>" style="display:flex;align-items:center;gap:8px;flex:1;flex-wrap:wrap">
      <div style="position:relative;flex:1;min-width:200px;max-width:340px">
        <i class="fa fa-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:12px;pointer-events:none"></i>
        <input type="text" name="search" class="poi-input" placeholder="Search invoice # or customer…"
               value="<?php echo htmlspecialchars($current_search); ?>" style="width:100%;padding-left:32px">
      </div>
      <select name="status" class="poi-select" style="min-width:150px">
        <option value="">All Statuses</option>
        <?php foreach ($status_map as $v => $info): ?>
        <option value="<?php echo $v; ?>" <?php echo $current_status===$v ? 'selected' : ''; ?>><?php echo $info['label']; ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="poi-btn-sec"><i class="fa fa-search"></i> Filter</button>
      <?php if ($current_search || $current_status): ?>
      <a href="<?php echo admin_url('pos_system/sales_orders'); ?>" class="poi-btn-sec"><i class="fa fa-times"></i> Clear</a>
      <?php endif; ?>
    </form>
    <button class="poi-btn-sec" onclick="soToggleSplit()" title="Split view">
      <i class="fa fa-columns" id="split-toggle-icon"></i> Split View
    </button>
  </div>

  <!-- ── Main layout (list + optional split panel) ── -->
  <div style="display:flex;gap:0" id="poi-main-layout">

    <!-- List -->
    <div id="small-table" style="flex:1;overflow:hidden">
      <div class="poi-table-wrap">
        <table class="poi-table">
          <thead>
            <tr>
              <th style="width:130px">INVOICE #</th>
              <th>DATE</th>
              <th>CUSTOMER</th>
              <th class="so-hide-split">SALES PERSON</th>
              <th class="so-hide-split r">EXP. DELIVERY</th>
              <th class="r">AMOUNT</th>
              <th style="text-align:center">STATUS</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7">
                <div class="poi-empty">
                  <i class="fa fa-file-invoice"></i>
                  <p style="font-size:15px;font-weight:600;color:#374151;margin-bottom:6px">No invoices found</p>
                  <p style="font-size:13px;margin-bottom:16px">Create your first invoice from the POS terminal or manually.</p>
                  <a href="<?php echo admin_url('pos_system/so_form'); ?>" class="poi-btn-new">
                    <i class="fa fa-plus"></i> Create Invoice
                  </a>
                </div>
              </td>
            </tr>
            <?php else: foreach ($orders as $o): ?>
            <tr id="so-row-<?php echo (int)$o['id']; ?>" onclick="soLoadPanel(<?php echo (int)$o['id']; ?>)"
                class="<?php echo $o['status']==='cancelled' ? '' : ''; ?>">
              <td>
                <a href="<?php echo admin_url('pos_system/so_form/'.$o['id']); ?>"
                   onclick="soLoadPanel(<?php echo (int)$o['id']; ?>); return false;"
                   style="font-weight:700;color:#16a34a;text-decoration:none">
                  <?php echo htmlspecialchars($o['so_number']); ?>
                </a>
              </td>
              <td style="color:#374151;white-space:nowrap"><?php echo date('d M Y', strtotime($o['date'])); ?></td>
              <td style="font-weight:600;color:#111827"><?php echo htmlspecialchars($o['customer_name'] ?: '—'); ?></td>
              <td class="so-hide-split" style="color:#6b7280"><?php echo htmlspecialchars($o['sales_person'] ?: '—'); ?></td>
              <td class="so-hide-split r" style="color:#6b7280;white-space:nowrap">
                <?php echo $o['expected_delivery'] ? date('d M Y', strtotime($o['expected_delivery'])) : '—'; ?>
              </td>
              <td class="r poi-amnt"><?php echo number_format((float)$o['total_amount'], 2); ?></td>
              <td style="text-align:center"><?php echo so_badge($o['status']); ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
      <div class="poi-footer">
        <span><?php echo count($orders); ?> record(s)</span>
        <span>Total: <strong class="poi-amnt-g"><?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></strong></span>
      </div>
    </div>

    <!-- Split detail panel (hidden by default) -->
    <div id="poi-split-panel" style="display:none;width:45%;min-width:380px;border-left:1px solid #e5e7eb;background:#fff">
      <div id="so-detail-panel" style="height:100%;overflow-y:auto">
        <div class="poi-empty">
          <i class="fa fa-hand-point-left" style="font-size:28px;margin-bottom:10px"></i>
          <p>Select an invoice to view details</p>
        </div>
      </div>
    </div>

  </div>

</div>
</div>

<?php init_tail(); ?>
<script>
var SO_PANEL_BASE = '<?php echo admin_url('pos_system/so_panel'); ?>';
var SO_ACTIVE_ID  = 0;

function soLoadPanel(id) {
    SO_ACTIVE_ID = id;
    $('.poi-table tbody tr').removeClass('so-active');
    $('#so-row-' + id).addClass('so-active');
    if ($('#poi-split-panel').css('display') === 'none') { soToggleSplit(); }
    $('#so-detail-panel').html('<div class="poi-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a;font-size:28px"></i></div>');
    $('#so-detail-panel').load(SO_PANEL_BASE + '/' + id, function(resp, status) {
        if (status === 'error') {
            $('#so-detail-panel').html('<div style="padding:20px"><div class="alert alert-danger">Failed to load details.</div></div>');
        }
    });
}

function soToggleSplit() {
    var $panel = $('#poi-split-panel');
    var $icon  = $('#split-toggle-icon');
    if ($panel.is(':visible')) {
        $panel.hide();
        $icon.removeClass('fa-columns').addClass('fa-columns');
    } else {
        $panel.show();
    }
}

function small_table_full_view() {
    $('#poi-split-panel').hide();
    $('#small-table').css('flex','1');
}

$(function() {
    if (window.location.hash) {
        var id = parseInt(window.location.hash.replace('#',''));
        if (id) soLoadPanel(id);
    }
});
</script>
