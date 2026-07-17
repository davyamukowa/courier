<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $is_edit = $doc_id > 0; ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'deliveries',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px;background:#f0f7f2;min-height:100vh">

<style>
.df-page { background:#f0f7f2; min-height:100vh; }
.df-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(13,40,24,.07); border:1px solid #d4e8db; margin-bottom:18px; }
.df-card-hdr { display:flex; align-items:center; gap:10px; padding:12px 20px; border-bottom:1px solid #dff0e6; background:#edf7f1; border-radius:10px 10px 0 0; }
.df-card-hdr-title { font-size:13px; font-weight:700; color:#1a2520; margin:0; flex:1; }
.df-card-hdr-title i { color:#16a34a; margin-right:6px; }
.df-body { padding:20px; }
.df-label { font-size:11px; font-weight:700; color:#3d4f45; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:5px; }
.df-input { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; transition:border .15s,box-shadow .15s; }
.df-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); background:#fff; }
.df-select { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; }
.df-select:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.df-textarea { width:100%; border:1px solid #c8dfd0; border-radius:7px; padding:9px 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; resize:vertical; min-height:70px; }
.df-textarea:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.df-fg { margin-bottom:14px; }
.df-row { display:flex; gap:14px; flex-wrap:wrap; }
.df-col-2 { flex:0 0 calc(50% - 7px); min-width:190px; }
.df-col-3 { flex:0 0 calc(33.333% - 10px); min-width:160px; }

/* Custom searchable select */
.df-srch-wrap { position:relative; }
.df-srch-input { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px 7px 0 0; padding:0 32px 0 11px; font-size:13px; color:#1a2520; background:#fff; outline:none; box-sizing:border-box; }
.df-srch-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.df-srch-input-icon { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#9cbaaa; font-size:11px; pointer-events:none; }
.df-srch-list { display:none; position:absolute; top:33px; left:0; right:0; background:#fff; border:1px solid #c8dfd0; border-top:none; border-radius:0 0 7px 7px; max-height:200px; overflow-y:auto; z-index:500; box-shadow:0 4px 12px rgba(13,40,24,.10); }
.df-srch-list.open { display:block; }
.df-srch-opt { padding:7px 11px; font-size:13px; color:#1a2520; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.df-srch-opt:hover, .df-srch-opt.active { background:#edf7f1; color:#14532d; }
.df-srch-opt.placeholder { color:#9cbaaa; font-style:italic; }
.df-srch-hidden { display:none; }

/* Barcode */
.df-barcode-bar { display:flex; align-items:center; gap:10px; padding:10px 16px; background:linear-gradient(135deg,#b8ddc8,#9ecfb2); border-radius:9px; margin-bottom:18px; border:1px solid #7dba96; }
.df-barcode-bar label { color:#0d2818; font-size:12px; font-weight:700; white-space:nowrap; margin:0; }
.df-barcode-input { flex:1; height:38px; border:2px solid #7dba96; border-radius:7px; padding:0 14px; font-size:14px; color:#1a2520; background:rgba(255,255,255,.65); outline:none; }
.df-barcode-input:focus { border-color:#16a34a; background:#fff; box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.df-barcode-input::placeholder { color:#3d6b4f; }

/* Items table */
.df-tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:12.5px; }
.df-tbl thead th { padding:9px 8px; font-size:10px; font-weight:700; color:#4a5e54; text-transform:uppercase; letter-spacing:.6px; background:#f5faf7; border-bottom:2px solid #e2ece6; white-space:nowrap; }
.df-tbl tbody td { padding:5px 5px; border-bottom:1px solid #edf5f0; vertical-align:middle; }
.df-tbl tbody tr:hover { background:#f8fdf9; }
.df-tbl tbody tr:last-child td { border-bottom:none; }
.df-tbl-input { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 7px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.df-tbl-input:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.10); }
.df-tbl-select { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 5px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.df-avail { display:inline-block; padding:1px 8px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; }
.df-avail-ok { background:#dcfce7; color:#14532d; }
.df-avail-low { background:#fef3c7; color:#92400e; }
.df-avail-out { background:#fee2e2; color:#991b1b; }
.df-add-row { display:flex; align-items:center; gap:7px; padding:10px 14px; cursor:pointer; color:#16a34a; font-size:13px; font-weight:600; border-top:1px solid #edf5f0; }
.df-add-row:hover { background:#f0faf4; }

/* Totals */
.df-total-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #edf5f0; font-size:13px; }
.df-total-row:last-child { border-bottom:none; padding-top:10px; }
.df-total-row .lbl { color:#3d4f45; font-weight:600; }
.df-total-row .val { font-weight:700; color:#1a2520; font-size:14px; }
.df-total-row.discount .val { color:#dc2626; }
.df-total-row.final .lbl { color:#1a2520; font-size:14px; font-weight:700; }
.df-total-row.final .val { color:#16a34a; font-size:16px; }
.df-ship-input { height:30px; border:1px solid #c8dfd0; border-radius:6px; padding:0 8px; font-size:13px; color:#1a2520; background:#fff; outline:none; width:130px; text-align:right; }
.df-ship-input:focus { border-color:#16a34a; }

/* Totals panel */
.df-totals { background:#edf7f1; border-radius:9px; border:1px solid #c8dfd0; padding:14px 20px; }

/* Top bar */
.df-topbar { display:flex; align-items:center; gap:10px; padding:12px 20px; background:#edf7f1; border-radius:10px; border:1px solid #c8dfd0; box-shadow:0 1px 6px rgba(13,40,24,.06); margin-bottom:18px; }
.df-doc-num { font-size:15px; font-weight:700; color:#1a2520; }
.df-doc-num span { color:#16a34a; }

.btn-df-save { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(22,163,74,.35); text-decoration:none!important; }
.btn-df-save:hover { opacity:.9; color:#fff; text-decoration:none; }
.btn-df-confirm { background:linear-gradient(135deg,#0284c7,#0369a1); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(2,132,199,.35); text-decoration:none!important; }
.btn-df-confirm:hover { opacity:.9; color:#fff; text-decoration:none; }
.btn-df-back { background:#f0faf4; color:#166534!important; border:1px solid #bbf7d0; border-radius:7px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none!important; }
.btn-df-back:hover { background:#dcfce7; color:#14532d; text-decoration:none; }
</style>

<?php
$_ex_status  = $existing['status'] ?? 'draft';
$fs          = $existing['fulfillment_status'] ?? ($_ex_status === 'confirmed' ? 'validated' : $_ex_status);
$can_dispatch= $is_edit && in_array($fs, ['validated','confirmed']) && $fs !== 'shipped' && $fs !== 'delivered';
$is_shipped  = $fs === 'shipped';
$fs_colors   = ['draft'=>'#64748b','validated'=>'#0284c7','shipped'=>'#16a34a','delivered'=>'#059669'];
$fs_color    = $fs_colors[$fs] ?? '#64748b';
?>
<!-- Top bar -->
<div class="df-topbar">
  <a href="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="btn-df-back">
    <i class="fa fa-arrow-left"></i> Back
  </a>
  <div class="df-doc-num">
    <i class="fa fa-truck" style="color:#16a34a;margin-right:6px"></i>
    <?php echo $is_edit ? 'Edit' : 'New'; ?> Inventory Delivery
    <?php if ($is_edit): ?><span>#<?php echo htmlspecialchars($existing['delivery_number'] ?? ''); ?></span><?php endif; ?>
  </div>
  <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
    <?php if ($is_edit): ?>
    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:99px;font-size:11px;font-weight:700;background:<?php echo $fs_color; ?>22;color:<?php echo $fs_color; ?>;border:1px solid <?php echo $fs_color; ?>44">
      <i class="fa fa-circle" style="font-size:7px"></i> <?php echo ucwords(str_replace('_',' ',$fs)); ?>
    </span>
    <?php endif; ?>
    <?php if (!$is_shipped): ?>
    <button type="button" class="btn-df-save" onclick="dfSave('draft')">
      <i class="fa fa-save"></i> Save Draft
    </button>
    <button type="button" class="btn-df-confirm" onclick="dfSave('confirmed')">
      <i class="fa fa-check-circle"></i> Confirm
    </button>
    <?php endif; ?>
    <?php if ($can_dispatch): ?>
    <button type="button" onclick="openDispatchModal()" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;border-radius:7px;padding:7px 18px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(124,58,237,.35)">
      <i class="fa fa-paper-plane"></i> Dispatch
    </button>
    <?php endif; ?>
    <?php if ($is_shipped): ?>
    <a href="<?php echo admin_url('pos_system/delivery_pdf/'.$doc_id); ?>" target="_blank" class="btn-df-confirm" style="display:inline-flex;align-items:center;gap:6px">
      <i class="fa fa-file-pdf"></i> View / Print PDF
    </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($is_edit && (($existing['tracking_number'] ?? null) || ($existing['carrier_info'] ?? null) || ($existing['shipped_at'] ?? null))): ?>
<!-- Shipped info bar -->
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:9px;padding:10px 18px;margin-bottom:14px;display:flex;gap:24px;flex-wrap:wrap;align-items:center;font-size:12px">
  <span style="font-weight:700;color:#1e40af"><i class="fa fa-shipping-fast"></i> Dispatched</span>
  <?php if ($existing['shipped_at'] ?? null): ?>
  <span><strong>Shipped At:</strong> <?php echo date('d M Y H:i', strtotime($existing['shipped_at'])); ?></span>
  <?php endif; ?>
  <?php if ($existing['tracking_number'] ?? null): ?>
  <span><strong>Tracking #:</strong> <?php echo htmlspecialchars($existing['tracking_number']); ?></span>
  <?php endif; ?>
  <?php if ($existing['carrier_info'] ?? null): ?>
  <span><strong>Carrier:</strong> <?php echo htmlspecialchars($existing['carrier_info']); ?></span>
  <?php endif; ?>
  <a href="<?php echo admin_url('pos_system/delivery_pdf/'.$doc_id); ?>" target="_blank" style="margin-left:auto;color:#1e40af;font-weight:700"><i class="fa fa-file-pdf"></i> Download PDF</a>
</div>
<?php endif; ?>

<input type="hidden" id="df-doc-id" value="<?php echo $doc_id; ?>">
<input type="hidden" id="df-doc-type" value="delivery">

<!-- Header: 2 columns -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">

  <!-- Left: Document Info -->
  <div class="df-card">
    <div class="df-card-hdr">
      <h5 class="df-card-hdr-title"><i class="fa fa-file-alt"></i> Document Information</h5>
    </div>
    <div class="df-body">
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Delivery Date <span style="color:#ef4444">*</span></label>
          <input type="date" id="df-delivery-date" class="df-input" value="<?php echo $existing['delivery_date'] ?? date('Y-m-d'); ?>">
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Accounting Date</label>
          <input type="date" id="df-accounting-date" class="df-input" value="<?php echo $existing['accounting_date'] ?? date('Y-m-d'); ?>">
        </div>
      </div>
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Document Type</label>
          <select id="df-type" class="df-select">
            <?php foreach (['standard'=>'Standard','urgent'=>'Urgent','return'=>'Customer Return'] as $k=>$v): ?>
            <option value="<?php echo $k; ?>" <?php echo ($existing['type'] ?? 'standard')===$k?'selected':''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Warehouse <span style="color:#ef4444">*</span></label>
          <select id="df-branch" class="df-select">
            <?php foreach ($branches as $b): ?>
            <option value="<?php echo $b['id']; ?>" <?php echo (int)($existing['branch_id'] ?? $branch_id)===(int)$b['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($b['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <!-- Link Sales Order row -->
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Link Sales Order</label>
          <div class="df-srch-wrap" id="df-so-wrap">
            <input type="text" class="df-srch-input" id="df-so-search" autocomplete="off"
              placeholder="Search SO #…"
              value="<?php
                $presel_so = $preselect_so ?? null;
                $existing_soid = (int)($existing['sales_order_id'] ?? $so_link_id ?? 0);
                if ($presel_so) {
                    echo htmlspecialchars($presel_so['so_number'] . ($presel_so['customer_name'] ? ' — '.$presel_so['customer_name'] : ''));
                } elseif ($existing_soid) {
                    foreach ($sales_orders as $so_row) {
                        if ((int)$so_row['id'] === $existing_soid) {
                            echo htmlspecialchars($so_row['so_number'] . ($so_row['customer_name'] ? ' — '.$so_row['customer_name'] : ''));
                            break;
                        }
                    }
                }
              ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-so-list">
              <div class="df-srch-opt placeholder" data-value="">— No Sales Order —</div>
              <?php foreach ($sales_orders as $so_row): ?>
              <div class="df-srch-opt"
                data-value="<?php echo (int)$so_row['id']; ?>"
                data-label="<?php echo htmlspecialchars($so_row['so_number'] . ($so_row['customer_name'] ? ' — '.$so_row['customer_name'] : '')); ?>"
                data-customer-name="<?php echo htmlspecialchars($so_row['customer_name'] ?? ''); ?>"
                data-client-id="<?php echo (int)($so_row['client_id'] ?? 0); ?>"
                data-address="<?php echo htmlspecialchars($so_row['address'] ?? ''); ?>"
                data-project-id="<?php echo (int)($so_row['project_id'] ?? 0); ?>"
                data-project-name="<?php echo htmlspecialchars($so_row['project_name'] ?? ''); ?>"
                <?php echo ($existing_soid && (int)$so_row['id'] === $existing_soid) ? ' class="df-srch-opt active"' : ''; ?>>
                <strong><?php echo htmlspecialchars($so_row['so_number']); ?></strong>
                <?php if ($so_row['customer_name']): ?>
                  <span style="color:#6b7c72"> — <?php echo htmlspecialchars($so_row['customer_name']); ?></span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-so-id" value="<?php echo $existing_soid; ?>">
          </div>
          <span style="font-size:11px;color:#56665e;margin-top:4px;display:block">
            <i class="fa fa-info-circle"></i> Selecting a SO auto-fills items &amp; customer below.
          </span>
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Invoice Number</label>
          <input type="text" id="df-invoice-number" class="df-input" placeholder="Ref invoice #"
            value="<?php echo htmlspecialchars($existing['invoice_number'] ?? ''); ?>">
        </div>
      </div>
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Link Invoice</label>
          <div class="df-srch-wrap" id="df-inv-wrap">
            <input type="text" class="df-srch-input" id="df-invoice-search" autocomplete="off"
              placeholder="Search invoice…"
              value="<?php
                $sel_inv = $existing['invoice_id'] ?? 0;
                if ($sel_inv) {
                    foreach ($invoices as $inv_item) {
                        if ((int)$inv_item['id'] === (int)$sel_inv) {
                            echo htmlspecialchars($inv_item['display_number'] . ($inv_item['customer_name'] ? ' — ' . $inv_item['customer_name'] : ''));
                            break;
                        }
                    }
                }
              ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-invoice-list">
              <div class="df-srch-opt placeholder" data-value="">— No invoice linked —</div>
              <?php foreach ($invoices as $inv_item): ?>
              <div class="df-srch-opt"
                data-value="<?php echo (int)$inv_item['id']; ?>"
                data-number="<?php echo htmlspecialchars($inv_item['display_number']); ?>"
                data-label="<?php echo htmlspecialchars($inv_item['display_number'] . ($inv_item['customer_name'] ? ' — ' . $inv_item['customer_name'] : '')); ?>"
                data-client-id="<?php echo (int)$inv_item['clientid']; ?>"
                data-customer-name="<?php echo htmlspecialchars($inv_item['customer_name']); ?>"
                data-address="<?php echo htmlspecialchars($inv_item['full_address']); ?>"
                data-project-id="<?php echo (int)$inv_item['project_id']; ?>"
                <?php echo (int)($existing['invoice_id'] ?? 0) === (int)$inv_item['id'] ? 'class="df-srch-opt active"' : ''; ?>>
                <strong><?php echo htmlspecialchars($inv_item['display_number']); ?></strong>
                <?php if ($inv_item['customer_name']): ?>
                  <span style="color:#6b7c72"> — <?php echo htmlspecialchars($inv_item['customer_name']); ?></span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-invoice-id" value="<?php echo (int)($existing['invoice_id'] ?? 0); ?>">
          </div>
          <span style="font-size:11px;color:#56665e;margin-top:4px;display:block">
            <i class="fa fa-info-circle"></i> Selecting an invoice auto-fills customer &amp; items below.
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Customer & Delivery Details -->
  <div class="df-card">
    <div class="df-card-hdr">
      <h5 class="df-card-hdr-title"><i class="fa fa-user"></i> Customer & Delivery Details</h5>
    </div>
    <div class="df-body">
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Customer Name</label>
          <div class="df-srch-wrap" id="df-customer-wrap">
            <input type="text" class="df-srch-input" id="df-customer-search" autocomplete="off"
              placeholder="Search customer…"
              value="<?php
                $sel_client = (int)($existing['customer_id'] ?? 0);
                $existing_cn = htmlspecialchars($existing['customer_name'] ?? '');
                // Try to match by name if no ID stored
                echo $existing_cn;
              ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-customer-list">
              <div class="df-srch-opt placeholder" data-value="" data-address="">— Select Customer —</div>
              <?php foreach ($clients as $cl): ?>
              <div class="df-srch-opt"
                data-value="<?php echo (int)$cl['userid']; ?>"
                data-label="<?php echo htmlspecialchars($cl['company']); ?>"
                data-address="<?php echo htmlspecialchars($cl['full_address']); ?>">
                <?php echo htmlspecialchars($cl['company']); ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-customer-id" value="<?php echo $sel_client; ?>">
          </div>
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Receiver</label>
          <div class="df-srch-wrap" id="df-receiver-wrap">
            <input type="text" class="df-srch-input" id="df-receiver-search" autocomplete="off"
              placeholder="Search receiver…"
              value="<?php echo htmlspecialchars($existing['receiver'] ?? ''); ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-receiver-list">
              <div class="df-srch-opt placeholder" data-value="" data-label="">— Select Receiver —</div>
              <?php foreach ($clients as $cl): ?>
              <div class="df-srch-opt"
                data-value="<?php echo (int)$cl['userid']; ?>"
                data-label="<?php echo htmlspecialchars($cl['company']); ?>">
                <?php echo htmlspecialchars($cl['company']); ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-receiver-id" value="">
          </div>
        </div>
      </div>
      <div class="df-fg">
        <label class="df-label">Delivery Address</label>
        <textarea id="df-address" class="df-textarea" rows="2" placeholder="Auto-filled from customer, or enter manually…"><?php echo htmlspecialchars($existing['address'] ?? ''); ?></textarea>
      </div>
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Sales Person</label>
          <div class="df-srch-wrap" id="df-salesperson-wrap">
            <input type="text" class="df-srch-input" id="df-sales-person-search" autocomplete="off"
              placeholder="Search staff…"
              value="<?php
                $sp_val = $existing['sales_person'] ?? $current_staff_name;
                echo htmlspecialchars($sp_val);
              ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-sales-person-list">
              <?php foreach ($staff_list as $st):
                $sname = trim($st['firstname'] . ' ' . $st['lastname']);
                $sp_existing = $existing['sales_person'] ?? $current_staff_name;
                $is_default = ($st['staffid'] == $current_staff_id && !isset($existing['sales_person']));
                $is_selected = ($sp_existing === $sname);
              ?>
              <div class="df-srch-opt <?php echo ($is_default || $is_selected) ? 'active' : ''; ?>"
                data-value="<?php echo (int)$st['staffid']; ?>"
                data-label="<?php echo htmlspecialchars($sname); ?>">
                <?php echo htmlspecialchars($sname); ?>
                <?php if ($st['staffid'] == $current_staff_id): ?>
                  <span style="font-size:10px;color:#16a34a;margin-left:4px">(you)</span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-sales-person-id" value="<?php echo (int)$current_staff_id; ?>">
          </div>
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Department</label>
          <input type="text" id="df-department" class="df-input" placeholder="e.g. Sales"
            list="df-dept-list" value="<?php echo htmlspecialchars($existing['department'] ?? ''); ?>">
          <datalist id="df-dept-list">
            <?php foreach ($departments as $d): ?>
            <option value="<?php echo htmlspecialchars($d['name']); ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
      </div>
      <div class="df-row">
        <div class="df-col-2 df-fg">
          <label class="df-label">Requester</label>
          <input type="text" id="df-requester" class="df-input" placeholder="Who requested dispatch"
            value="<?php echo htmlspecialchars($existing['requester'] ?? ''); ?>">
        </div>
        <div class="df-col-2 df-fg">
          <label class="df-label">Project</label>
          <div class="df-srch-wrap" id="df-project-wrap">
            <input type="text" class="df-srch-input" id="df-project-search" autocomplete="off"
              placeholder="Search project…"
              value="<?php echo htmlspecialchars($existing['project'] ?? ''); ?>">
            <i class="fa fa-chevron-down df-srch-input-icon"></i>
            <div class="df-srch-list" id="df-project-list">
              <div class="df-srch-opt placeholder" data-value="" data-label="">— No project —</div>
              <?php foreach ($crm_projects as $proj): ?>
              <div class="df-srch-opt"
                data-value="<?php echo (int)$proj['id']; ?>"
                data-label="<?php echo htmlspecialchars($proj['name']); ?>"
                <?php echo ($existing['project'] ?? '') === $proj['name'] ? 'class="df-srch-opt active"' : ''; ?>>
                <?php echo htmlspecialchars($proj['name']); ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="df-project-id" value="">
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Barcode Scanner -->
<div class="df-barcode-bar">
  <label><i class="fa fa-barcode" style="font-size:16px;margin-right:4px"></i> Barcode Scanner</label>
  <input type="text" id="df-barcode-input" class="df-barcode-input" placeholder="Scan or type barcode, then press Enter…" autocomplete="off">
  <span style="color:#3d6b4f;font-size:12px;white-space:nowrap"><i class="fa fa-info-circle"></i> Scanned items added automatically</span>
</div>

<!-- Line Items -->
<div class="df-card">
  <div class="df-card-hdr">
    <h5 class="df-card-hdr-title"><i class="fa fa-list"></i> Line Items</h5>
    <button type="button" class="btn-inv-primary" style="padding:5px 14px;font-size:12px" onclick="dfAddLine()">
      <i class="fa fa-plus"></i> Add Row
    </button>
  </div>
  <div style="overflow-x:auto">
    <table class="df-tbl">
      <thead>
        <tr>
          <th style="width:24px">#</th>
          <th style="min-width:200px">ITEM</th>
          <th style="min-width:120px">WAREHOUSE</th>
          <th style="width:100px;text-align:center">AVAILABLE</th>
          <th style="width:75px">QTY</th>
          <th style="width:105px">SALE PRICE</th>
          <th style="width:120px">TAX</th>
          <th style="width:100px;text-align:right">SUBTOTAL</th>
          <th style="width:75px">DISC %</th>
          <th style="width:100px">DISC (Ksh)</th>
          <th style="width:105px;text-align:right">TOTAL</th>
          <th style="width:90px">BATCH #</th>
          <th style="width:90px">SERIAL #</th>
          <th style="width:60px;text-align:center" title="Drop Ship — bypasses local warehouse stock">DROP&#8209;SHIP</th>
          <th style="width:34px"></th>
        </tr>
      </thead>
      <tbody id="df-lines">
        <tr id="df-no-lines">
          <td colspan="15" style="text-align:center;padding:28px;color:#56665e;font-size:13px">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>
            No items added. Click <strong>Add Row</strong> or scan a barcode.
          </td>
        </tr>
      </tbody>
    </table>
    <div class="df-add-row" onclick="dfAddLine()">
      <i class="fa fa-plus-circle"></i> Add another item
    </div>
  </div>
</div>

<!-- Totals + Notes -->
<div style="display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start">

  <div class="df-card">
    <div class="df-card-hdr">
      <h5 class="df-card-hdr-title"><i class="fa fa-sticky-note"></i> Note</h5>
    </div>
    <div class="df-body">
      <textarea id="df-note" class="df-textarea" placeholder="Delivery instructions, remarks…"><?php echo htmlspecialchars($existing['note'] ?? ''); ?></textarea>
    </div>
  </div>

  <div class="df-totals">
    <div class="df-total-row">
      <span class="lbl">Subtotal :</span>
      <span class="val" id="df-subtotal">Ksh 0.00</span>
    </div>
    <div class="df-total-row discount">
      <span class="lbl">Total Discount :</span>
      <span class="val" id="df-total-discount">- Ksh 0.00</span>
    </div>
    <div class="df-total-row">
      <span class="lbl">Total Tax :</span>
      <span class="val" id="df-total-tax">Ksh 0.00</span>
    </div>
    <div class="df-total-row" style="border-bottom:1px solid #e2ece6;padding-bottom:10px">
      <span class="lbl">Shipping Fee :</span>
      <input type="number" id="df-shipping-fee" class="df-ship-input" value="<?php echo $existing['shipping_fee'] ?? 0; ?>" min="0" step="any" placeholder="0.00" oninput="dfUpdateTotals()">
    </div>
    <div class="df-total-row final" style="margin-top:4px">
      <span class="lbl">Total Payment :</span>
      <span class="val" id="df-grand-total">Ksh 0.00</span>
    </div>
  </div>

</div>

<!-- Bottom actions -->
<div style="display:flex;gap:10px;padding:16px 0;justify-content:flex-end">
  <a href="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="btn-df-back">
    <i class="fa fa-times"></i> Cancel
  </a>
  <button type="button" class="btn-df-save" onclick="dfSave('draft')">
    <i class="fa fa-save"></i> Save Draft
  </button>
  <button type="button" class="btn-df-confirm" onclick="dfSave('confirmed')">
    <i class="fa fa-check-circle"></i> Confirm Delivery
  </button>
</div>

</div></div></div>

<!-- ── Dispatch Confirmation Modal ─────────────────────────────────────────── -->
<div id="dispatch-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;width:520px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6);padding:16px 22px;border-radius:14px 14px 0 0;color:#fff">
      <h3 style="margin:0;font-size:15px;font-weight:700"><i class="fa fa-paper-plane"></i> Confirm Dispatch</h3>
      <p style="margin:4px 0 0;font-size:12px;opacity:.85">This will deduct stock from the warehouse and mark this delivery as Shipped.</p>
    </div>
    <div style="padding:22px">
      <!-- Availability check result -->
      <div id="dispatch-avail-box" style="margin-bottom:16px;display:none">
        <div style="font-size:12px;font-weight:700;color:#3d4f45;margin-bottom:8px"><i class="fa fa-boxes"></i> Stock Availability Check</div>
        <div id="dispatch-avail-rows"></div>
      </div>
      <div class="df-fg">
        <label class="df-label">Tracking Number</label>
        <input type="text" id="dispatch-tracking" class="df-input" placeholder="e.g. TRK-20240601-001" autocomplete="off">
      </div>
      <div class="df-fg" style="margin-top:10px">
        <label class="df-label">Carrier / Transport Info</label>
        <input type="text" id="dispatch-carrier" class="df-input" placeholder="e.g. DHL Express, Plate KCA 123X, Driver John" autocomplete="off">
      </div>
      <div style="background:#f3e8ff;border:1px solid #d8b4fe;border-radius:7px;padding:10px 14px;margin-top:12px;font-size:12px;color:#6d28d9">
        <i class="fa fa-info-circle"></i> Once dispatched, inventory will be permanently deducted and the delivery note status will change to <strong>Shipped</strong>. This action cannot be undone.
      </div>
      <div id="dispatch-error" style="display:none;background:#fff1f2;border:1px solid #fecdd3;border-radius:7px;padding:10px 14px;margin-top:10px;font-size:12px;color:#991b1b"></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px">
        <button onclick="closeDispatchModal()" class="btn-df-back">Cancel</button>
        <button id="dispatch-submit-btn" onclick="doDispatch()" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;border-radius:7px;padding:7px 22px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
          <i class="fa fa-paper-plane"></i> Dispatch Now
        </button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
var DF_PRODUCTS  = <?php echo json_encode(array_values(array_map(function($p){
    return ['id'=>(int)$p['id'],'name'=>$p['name'],'sku'=>$p['sku']??'','barcode'=>$p['barcode']??'','selling_price'=>(float)($p['selling_price']??0),'tax_rate_id'=>(int)($p['tax_rate_id']??0)];
}, $products))); ?>;
var DF_BRANCHES  = <?php echo json_encode(array_values(array_map(function($b){ return ['id'=>(int)$b['id'],'name'=>$b['name']]; }, $branches))); ?>;
var DF_TAX_RATES = <?php echo json_encode(array_values(array_map(function($t){ return ['id'=>(int)$t['id'],'name'=>$t['name'],'rate'=>(float)$t['rate']]; }, $tax_rates))); ?>;
var DF_STOCK     = <?php echo json_encode($stock_map); ?>;
var DF_BRANCH_ID = <?php echo (int)$branch_id; ?>;
var DF_SAVE_URL      = '<?php echo admin_url('pos_system/inv_save/doc'); ?>';
var DF_INV_ITEMS_URL = '<?php echo admin_url('pos_system/inv_ajax/invoice_items'); ?>';
var DF_SO_ITEMS_URL  = '<?php echo admin_url('pos_system/inv_ajax/so_items'); ?>';
var _csrf_n      = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v      = '<?php echo $this->security->get_csrf_hash(); ?>';
var df_idx       = 0;

var prodByBarcode = {}, prodById = {}, taxById = {};
DF_PRODUCTS.forEach(function(p){
    prodById[p.id] = p;
    if (p.barcode) prodByBarcode[p.barcode.toLowerCase()] = p;
    if (p.sku)     prodByBarcode[p.sku.toLowerCase()]     = p;
});
DF_TAX_RATES.forEach(function(t){ taxById[t.id] = t; });

var prodOpts = '<option value="">— Select Product —</option>';
DF_PRODUCTS.forEach(function(p){
    prodOpts += '<option value="'+p.id+'">'+escHtml(p.name)+(p.sku?' ['+p.sku+']':'')+'</option>';
});
var taxOpts = '<option value="0" data-rate="0">No Tax</option>';
DF_TAX_RATES.forEach(function(t){
    taxOpts += '<option value="'+t.id+'" data-rate="'+t.rate+'">'+escHtml(t.name)+'</option>';
});
var branchOpts = '<option value="">— Header warehouse —</option>';
DF_BRANCHES.forEach(function(b){
    branchOpts += '<option value="'+b.id+'"'+(b.id===DF_BRANCH_ID?' selected':'')+'>'+escHtml(b.name)+'</option>';
});

function escHtml(s){ return String(s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]}); }
function fmtMoney(n){ return 'Ksh '+parseFloat(n||0).toLocaleString('en-KE',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function getAvailQty(pid, branchId) {
    branchId = branchId || DF_BRANCH_ID;
    var key  = branchId + '_' + pid;
    return parseFloat(DF_STOCK[key] || 0);
}

function availBadge(qty) {
    if (qty <= 0)   return '<span class="df-avail df-avail-out">Out ('  +qty.toFixed(2)+')</span>';
    if (qty < 5)    return '<span class="df-avail df-avail-low">Low ('  +qty.toFixed(2)+')</span>';
    return                  '<span class="df-avail df-avail-ok">'+qty.toFixed(2)+'</span>';
}

function dfAddLine(pid, pcost, qty, dPct, description) {
    $('#df-no-lines').remove();
    var idx  = df_idx++;
    var avail = pid ? getAvailQty(pid, DF_BRANCH_ID) : 0;
    var tr = '<tr id="df-line-'+idx+'">';
    tr += '<td style="padding:6px 8px;color:#56665e;font-weight:700;text-align:center" id="df-ln-'+idx+'">'+idx+'</td>';
    tr += '<td style="padding:4px 5px"><select class="df-tbl-select df-pid" data-idx="'+idx+'" onchange="dfProductChange('+idx+')" style="min-width:180px">'+prodOpts+'</select></td>';
    tr += '<td style="padding:4px 5px"><select class="df-tbl-select df-brid" data-idx="'+idx+'" onchange="dfBranchChange('+idx+')" style="min-width:110px">'+branchOpts+'</select></td>';
    tr += '<td style="padding:4px 8px;text-align:center" id="df-avail-'+idx+'">'+availBadge(avail)+'</td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="df-tbl-input df-qty" data-idx="'+idx+'" value="'+(qty||1)+'" min="0.001" step="any" onchange="dfRecalc('+idx+')" style="width:65px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="df-tbl-input df-price" data-idx="'+idx+'" value="'+(pcost||0).toFixed(2)+'" min="0" step="any" onchange="dfRecalc('+idx+')" style="width:95px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><select class="df-tbl-select df-tax" data-idx="'+idx+'" onchange="dfRecalc('+idx+')" style="min-width:110px">'+taxOpts+'</select></td>';
    tr += '<td style="padding:4px 8px;text-align:right;font-weight:600;color:#1a2520" id="df-subtotal-'+idx+'">0.00</td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="df-tbl-input df-disc-pct" data-idx="'+idx+'" value="'+(dPct||0)+'" min="0" max="100" step="any" onchange="dfDiscPctChange('+idx+')" style="width:60px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="df-tbl-input df-disc-amt" data-idx="'+idx+'" value="0" min="0" step="any" onchange="dfDiscAmtChange('+idx+')" style="width:90px;text-align:right"></td>';
    tr += '<td style="padding:4px 8px;text-align:right;font-weight:700;color:#1a2520" id="df-line-total-'+idx+'">0.00</td>';
    tr += '<td style="padding:4px 5px"><input type="text" class="df-tbl-input df-batch" data-idx="'+idx+'" placeholder="Batch #" style="width:80px"></td>';
    tr += '<td style="padding:4px 5px"><input type="text" class="df-tbl-input df-serial" data-idx="'+idx+'" placeholder="Serial #" style="width:80px"></td>';
    tr += '<td style="padding:4px 5px;text-align:center"><input type="checkbox" class="df-dropship" data-idx="'+idx+'" title="Drop Ship — supplier ships directly, no warehouse deduction" onchange="dfDropShipChange('+idx+')"></td>';
    tr += '<td style="padding:4px 5px;text-align:center"><button type="button" class="btn-inv-icon danger" onclick="dfRemoveLine('+idx+')"><i class="fa fa-times"></i></button></td>';
    tr += '</tr>';
    $('#df-lines').append(tr);
    if (pid) {
        $('.df-pid[data-idx="'+idx+'"]').val(pid);
        dfProductChange(idx);
    } else if (description) {
        // Invoice item not matched to a POS product — highlight and show description as tooltip
        $('.df-pid[data-idx="'+idx+'"]')
            .css('border', '2px solid #f59e0b')
            .attr('title', 'Invoice item: "' + description + '" — please select matching product');
    }
    if (dPct) {
        $('.df-disc-pct[data-idx="'+idx+'"]').val(dPct);
        dfDiscPctChange(idx);
    }
    dfRecalc(idx);
    renumberLines();
}

function dfProductChange(idx) {
    var pid = parseInt($('.df-pid[data-idx="'+idx+'"]').val());
    if (pid && prodById[pid]) {
        var p = prodById[pid];
        $('.df-price[data-idx="'+idx+'"]').val((p.selling_price||0).toFixed(2));
        if (p.tax_rate_id) $('.df-tax[data-idx="'+idx+'"]').val(p.tax_rate_id);
        var brId = parseInt($('.df-brid[data-idx="'+idx+'"]').val()) || DF_BRANCH_ID;
        $('#df-avail-'+idx).html(availBadge(getAvailQty(pid, brId)));
    }
    dfRecalc(idx);
}

function dfBranchChange(idx) {
    var pid  = parseInt($('.df-pid[data-idx="'+idx+'"]').val());
    var brId = parseInt($('.df-brid[data-idx="'+idx+'"]').val()) || DF_BRANCH_ID;
    if (pid) $('#df-avail-'+idx).html(availBadge(getAvailQty(pid, brId)));
}

function dfRecalc(idx) {
    var qty     = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())   || 0;
    var price   = parseFloat($('.df-price[data-idx="'+idx+'"]').val()) || 0;
    var rate    = parseFloat($('.df-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
    var discPct = parseFloat($('.df-disc-pct[data-idx="'+idx+'"]').val()) || 0;
    var sub     = qty * price;
    var taxAmt  = sub * rate;
    var discAmt = sub * (discPct / 100);
    var total   = sub + taxAmt - discAmt;
    $('.df-disc-amt[data-idx="'+idx+'"]').val(discAmt.toFixed(2));
    $('#df-subtotal-'+idx).text(sub.toFixed(2));
    $('#df-line-total-'+idx).text(total.toFixed(2));
    dfUpdateTotals();
    if (Object.keys(DF_SO_QTY_MAP).length) dfCheckSoGuardrail(idx);
}
var DF_SO_QTY_MAP = {};

function dfDiscPctChange(idx) {
    var qty  = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())   || 0;
    var price= parseFloat($('.df-price[data-idx="'+idx+'"]').val()) || 0;
    var pct  = parseFloat($('.df-disc-pct[data-idx="'+idx+'"]').val()) || 0;
    var dAmt = qty * price * (pct / 100);
    $('.df-disc-amt[data-idx="'+idx+'"]').val(dAmt.toFixed(2));
    dfRecalc(idx);
}

function dfDiscAmtChange(idx) {
    var qty  = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())   || 0;
    var price= parseFloat($('.df-price[data-idx="'+idx+'"]').val()) || 0;
    var dAmt = parseFloat($('.df-disc-amt[data-idx="'+idx+'"]').val()) || 0;
    var base = qty * price;
    var pct  = base > 0 ? (dAmt / base * 100) : 0;
    $('.df-disc-pct[data-idx="'+idx+'"]').val(pct.toFixed(2));
    dfRecalc(idx);
}

function dfUpdateTotals() {
    var subtotal = 0, discount = 0, tax = 0;
    $('[id^="df-line-"]:not(#df-no-lines)').each(function(){
        var idx = this.id.replace('df-line-','');
        if (isNaN(parseInt(idx))) return;
        var qty   = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())   || 0;
        var price = parseFloat($('.df-price[data-idx="'+idx+'"]').val()) || 0;
        var rate  = parseFloat($('.df-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
        var dAmt  = parseFloat($('.df-disc-amt[data-idx="'+idx+'"]').val()) || 0;
        var sub   = qty * price;
        subtotal += sub;
        discount += dAmt;
        tax      += sub * rate;
    });
    var shipping = parseFloat($('#df-shipping-fee').val()) || 0;
    var grand = subtotal - discount + tax + shipping;
    $('#df-subtotal').text(fmtMoney(subtotal));
    $('#df-total-discount').text('- '+fmtMoney(discount));
    $('#df-total-tax').text(fmtMoney(tax));
    $('#df-grand-total').text(fmtMoney(grand));
}

function dfRemoveLine(idx) {
    $('#df-line-'+idx).remove();
    dfUpdateTotals();
    if (!$('#df-lines tr').length) {
        $('#df-lines').append('<tr id="df-no-lines"><td colspan="15" style="text-align:center;padding:28px;color:#56665e;font-size:13px"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>No items added. Click <strong>Add Row</strong> or scan a barcode.</td></tr>');
    }
    renumberLines();
}

function renumberLines() {
    var n = 1; $('[id^="df-ln-"]').each(function(){ $(this).text(n++); });
}

$('#df-barcode-input').on('keypress', function(e){
    if (e.which !== 13) return;
    var code = $(this).val().trim().toLowerCase();
    if (!code) return;
    var prod = prodByBarcode[code];
    if (prod) { dfAddLine(prod.id, prod.selling_price); $(this).val('').focus(); }
    else { $(this).css('border-color','#ef4444'); setTimeout(function(){ $('#df-barcode-input').css('border-color',''); }, 1000); }
});

/* ── Load items from Sales Order — replaced by guardrail version below ── */

/* ── Load invoice items from Perfex invoice ─────────────── */
function dfLoadInvoiceItems(invoiceId) {
    if (!invoiceId) return;
    $.getJSON(DF_INV_ITEMS_URL, {id: invoiceId}, function(r) {
        if (r.error) { alert_float('danger', 'Invoice lookup failed: ' + r.error); return; }
        // Auto-fill customer if not already set
        if (r.client_id && !$('#df-customer-id').val()) {
            dfSrchSetValue('df-customer', r.client_id, r.customer_name);
            if (r.address) $('#df-address').val(r.address);
        }
        // Auto-fill project
        if (r.project_id) {
            dfSrchSetValue('df-project', r.project_id, r.project_name);
        }
        // Clear and repopulate line items
        if (r.items && r.items.length) {
            $('#df-lines').empty();
            df_idx = 0;
            var unmatched = 0;
            $.each(r.items, function(i, item) {
                dfAddLine(item.product_id || null, item.unit_price, item.qty, item.disc_pct, item.product_name);
                if (!item.product_id) unmatched++;
            });
            if (unmatched) {
                setTimeout(function() {
                    alert_float('warning', unmatched + ' invoice line(s) could not be auto-matched to a POS product (highlighted in orange). Please select the correct product for each.');
                }, 300);
            } else {
                alert_float('success', r.items.length + ' item(s) loaded from invoice.');
            }
        } else {
            alert_float('warning', 'This invoice has no line items.');
        }
        // Update invoice number display field
        if (r.invoice_number) $('#df-invoice-number').val(r.invoice_number);
    }).fail(function(xhr) {
        var msg = 'Failed to load invoice items.';
        try { var e = JSON.parse(xhr.responseText); if (e.error) msg += ' Server: ' + e.error; } catch(x) {}
        alert_float('danger', msg);
    });
}

function dfSave(status) {
    var lines = []; var ok = true;
    $('[id^="df-line-"]:not(#df-no-lines)').each(function(){
        var idx = this.id.replace('df-line-','');
        if (isNaN(parseInt(idx))) return;
        var pid = parseInt($('.df-pid[data-idx="'+idx+'"]').val());
        if (!pid) { ok = false; alert('Select a product for every row.'); return false; }
        var qty    = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())   || 0;
        var price  = parseFloat($('.df-price[data-idx="'+idx+'"]').val()) || 0;
        var taxSel = $('.df-tax[data-idx="'+idx+'"] option:selected');
        var rate   = parseFloat(taxSel.data('rate')) || 0;
        var taxId  = parseInt(taxSel.val()) || 0;
        var dPct   = parseFloat($('.df-disc-pct[data-idx="'+idx+'"]').val()) || 0;
        var dAmt   = parseFloat($('.df-disc-amt[data-idx="'+idx+'"]').val()) || 0;
        var sub    = qty * price;
        lines.push({
            product_id:      pid,
            branch_id:       parseInt($('.df-brid[data-idx="'+idx+'"]').val()) || 0,
            quantity:        qty,
            unit_price:      price,
            tax_rate_id:     taxId,
            tax_rate_pct:    rate,
            tax_amount:      (sub * rate).toFixed(4),
            subtotal:        sub.toFixed(4),
            discount_pct:    dPct,
            discount_amount: dAmt.toFixed(4),
            batch_no:        $('.df-batch[data-idx="'+idx+'"]').val() || '',
            serial_no:       $('.df-serial[data-idx="'+idx+'"]').val() || '',
            is_drop_ship:    $('.df-dropship[data-idx="'+idx+'"]').is(':checked') ? 1 : 0,
        });
    });
    if (!ok) return;
    if (!lines.length) { alert('Add at least one item.'); return; }

    // Read named values from searchable dropdowns
    var customerName  = $('#df-customer-search').val() || '';
    var receiverName  = $('#df-receiver-search').val()  || '';
    var salesPerson   = $('#df-sales-person-search').val() || '';
    var projectName   = $('#df-project-search').val()   || '';

    var d = {};
    d[_csrf_n]          = _csrf_v;
    d.doc_type          = 'delivery';
    d.doc_id            = $('#df-doc-id').val();
    d.branch_id         = $('#df-branch').val();
    d.date              = $('#df-delivery-date').val();
    d.accounting_date   = $('#df-accounting-date').val();
    d.type              = $('#df-type').val();
    d.invoice_number    = $('#df-invoice-number').val();
    d.invoice_id        = $('#df-invoice-id').val();
    d.sales_order_id    = $('#df-so-id').val();
    d.customer_name     = customerName;
    d.receiver          = receiverName;
    d.address           = $('#df-address').val();
    d.sales_person      = salesPerson;
    d.department        = $('#df-department').val();
    d.requester         = $('#df-requester').val();
    d.project           = projectName;
    d.shipping_fee      = $('#df-shipping-fee').val();
    d.note              = $('#df-note').val();
    d.status            = status;
    d.lines             = JSON.stringify(lines);

    var $btns = $('button[onclick^="dfSave"]');
    $btns.prop('disabled', true);
    $.post(DF_SAVE_URL, d, function(r){
        $btns.prop('disabled', false);
        _csrf_v = r.csrf_hash || _csrf_v;
        if (r.success) {
            alert_float('success', r.message || 'Delivery saved.');
            if (r.redirect) setTimeout(function(){ window.location = r.redirect; }, 700);
        } else {
            alert_float('danger', r.error || 'Save failed.');
        }
    }, 'json').fail(function(){ $btns.prop('disabled', false); alert_float('danger', 'Network error.'); });
}

/* ── Searchable dropdown widget ─────────────────────────── */
function dfSrchInit(baseId, onSelect) {
    var $search  = $('#' + baseId + '-search');
    var $list    = $('#' + baseId + '-list');
    var $hidden  = $('#' + baseId + '-id');

    // Show all options when clicking/focusing — stop propagation so the click-outside
    // document handler doesn't close the list on the same click that opened it
    $search.on('focus click', function(e) {
        e.stopPropagation();
        dfSrchFilter(baseId, '');  // always show full list on open
        $list.addClass('open');
    });

    // Filter on type
    $search.on('input', function() {
        dfSrchFilter(baseId, $(this).val());
        $list.addClass('open');
        // If text cleared, clear hidden value
        if (!$(this).val()) { $hidden.val(''); }
    });

    // Select option
    $list.on('mousedown', '.df-srch-opt', function(e) {
        e.preventDefault();
        var val   = $(this).data('value');
        var label = $(this).data('label') || $(this).text().trim();
        $search.val(label);
        $hidden.val(val || '');
        $list.find('.df-srch-opt').removeClass('active');
        $(this).addClass('active');
        $list.removeClass('open');
        if (typeof onSelect === 'function') onSelect(val, label, $(this));
    });

    // Close on blur
    $search.on('blur', function() {
        setTimeout(function(){ $list.removeClass('open'); }, 150);
    });

    // Click outside
    $(document).on('click.srch_'+baseId, function(e) {
        if (!$('#' + baseId + '-wrap, #' + baseId + '-wrap *').is(e.target)) {
            $list.removeClass('open');
        }
    });
}

function dfSrchFilter(baseId, q) {
    q = (q || '').toLowerCase();
    $('#' + baseId + '-list .df-srch-opt').each(function() {
        var text = ($(this).data('label') || $(this).text()).toLowerCase();
        $(this).toggle(!q || text.indexOf(q) !== -1);
    });
}

function dfSrchSetValue(baseId, val, label) {
    $('#' + baseId + '-search').val(label || '');
    $('#' + baseId + '-id').val(val || '');
    $('#' + baseId + '-list .df-srch-opt').removeClass('active');
    $('#' + baseId + '-list .df-srch-opt[data-value="'+val+'"]').addClass('active');
}

$(document).ready(function() {
    // Init Sales Order searchable dropdown
    dfSrchInit('df-so', function(val, label, $opt) {
        $('#df-so-id').val(val || '');
        if (val) {
            var custName  = $opt.data('customer-name');
            var address   = $opt.data('address');
            var projId    = $opt.data('project-id');
            var projName  = $opt.data('project-name');
            var clientId  = $opt.data('client-id');

            if (custName)  dfSrchSetValue('df-customer', clientId, custName);
            if (address)   $('#df-address').val(address);
            if (projName)  dfSrchSetValue('df-project', projId, projName);
            dfLoadSoItems(val);
            // Clear invoice link when SO selected
            dfSrchSetValue('df-invoice', '', '');
            $('#df-invoice-id').val('');
        }
    });

    // Init invoice searchable dropdown
    dfSrchInit('df-invoice', function(val, label, $opt) {
        $('#df-invoice-id').val(val || '');
        if (val) {
            // Auto-fill customer from data attribute
            var clientId   = $opt.data('client-id');
            var custName   = $opt.data('customer-name');
            var address    = $opt.data('address');
            var projectId  = $opt.data('project-id');
            var invNumber  = $opt.data('number');

            if (invNumber) $('#df-invoice-number').val(invNumber);
            if (custName)  dfSrchSetValue('df-customer', clientId, custName);
            if (address)   $('#df-address').val(address);
            // Load full invoice items via AJAX
            dfLoadInvoiceItems(val);
        }
    });

    // Init customer searchable dropdown
    dfSrchInit('df-customer', function(val, label, $opt) {
        if (val) {
            var address = $opt.data('address') || '';
            if (address) $('#df-address').val(address);
        } else {
            $('#df-address').val('');
        }
    });

    // Init receiver searchable dropdown
    dfSrchInit('df-receiver', null);

    // Init sales person searchable dropdown
    dfSrchInit('df-sales-person', null);

    // Init project searchable dropdown
    dfSrchInit('df-project', null);

    dfUpdateTotals();

    // Auto-load SO items when pre-selected from Sales Order page
    var _preselSoId = <?php echo (int)($so_link_id ?? 0); ?>;
    if (_preselSoId) {
        dfLoadSoItems(_preselSoId);
    }
});

// ── Drop-ship per row ────────────────────────────────────────────────────────
function dfDropShipChange(idx) {
    var isDS = $('.df-dropship[data-idx="'+idx+'"]').is(':checked');
    // Gray out availability badge for drop-ship items
    $('#df-avail-'+idx).css('opacity', isDS ? 0.35 : 1).attr('title', isDS ? 'Drop Ship — no local stock deduction' : '');
}

// ── SO Qty Guardrail ─────────────────────────────────────────────────────────
function dfLoadSoItems(soId) {
    if (!soId) return;
    $.getJSON(DF_SO_ITEMS_URL, {id: soId}, function(r) {
        if (r.customer_name && !$('#df-customer-search').val()) {
            dfSrchSetValue('df-customer', r.client_id, r.customer_name);
            if (r.address) $('#df-address').val(r.address);
        }
        if (r.project_id) dfSrchSetValue('df-project', r.project_id, r.project_name);
        if (r.items && r.items.length) {
            DF_SO_QTY_MAP = {};
            r.items.forEach(function(item) {
                if (item.product_id) DF_SO_QTY_MAP[item.product_id] = parseFloat(item.qty) || 0;
            });
            $('#df-lines').empty(); df_idx = 0;
            $.each(r.items, function(i, item) {
                dfAddLine(item.product_id || null, item.unit_price, item.qty, item.disc_pct);
            });
        }
    });
}

function dfCheckSoGuardrail(idx) {
    var pid = parseInt($('.df-pid[data-idx="'+idx+'"]').val());
    var qty = parseFloat($('.df-qty[data-idx="'+idx+'"]').val()) || 0;
    if (pid && DF_SO_QTY_MAP[pid] !== undefined) {
        var max = DF_SO_QTY_MAP[pid];
        if (qty > max) {
            alert('Warning: Quantity ' + qty + ' exceeds the ordered quantity ' + max + ' from the linked Sales Order.');
            $('.df-qty[data-idx="'+idx+'"]').addClass('dirty');
        } else {
            $('.df-qty[data-idx="'+idx+'"]').removeClass('dirty');
        }
    }
}

// ── Dispatch modal ───────────────────────────────────────────────────────────
var DF_DISPATCH_URL  = '<?php echo admin_url('pos_system/inv_ajax/delivery_dispatch'); ?>';
var DF_AVAIL_URL     = '<?php echo admin_url('pos_system/inv_ajax/delivery_avail'); ?>';
var DF_DOC_ID        = <?php echo (int)$doc_id; ?>;

function openDispatchModal() {
    document.getElementById('dispatch-error').style.display = 'none';
    document.getElementById('dispatch-avail-box').style.display = 'none';
    document.getElementById('dispatch-submit-btn').disabled = false;
    document.getElementById('dispatch-submit-btn').innerHTML = '<i class="fa fa-paper-plane"></i> Dispatch Now';
    document.getElementById('dispatch-modal').style.display = 'flex';

    // Run availability check
    if (DF_DOC_ID) {
        $.getJSON(DF_AVAIL_URL, {doc_id: DF_DOC_ID}, function(r) {
            if (!r.items || !r.items.length) return;
            var html = '';
            var hasIssue = false;
            r.items.forEach(function(it) {
                var ok   = it.is_drop_ship || it.ok;
                var icon = ok ? '<i class="fa fa-check-circle" style="color:#16a34a"></i>' : '<i class="fa fa-times-circle" style="color:#dc2626"></i>';
                var tag  = it.is_drop_ship ? '<span style="background:#ede9fe;color:#5b21b6;border-radius:99px;padding:1px 6px;font-size:10px;font-weight:700;margin-left:4px">DROP SHIP</span>' : '';
                html += '<div style="display:flex;justify-content:space-between;padding:4px 8px;font-size:12px;background:'+(ok?'#f0fdf4':'#fff1f2')+';border-radius:5px;margin-bottom:4px">';
                html += '<span>'+icon+' '+escHtml(it.product_name)+tag+'</span>';
                if (!it.is_drop_ship) {
                    html += '<span style="color:'+(it.ok?'#14532d':'#991b1b')+';font-weight:700">Need '+it.qty_needed+' / Have '+it.qty_avail+'</span>';
                } else {
                    html += '<span style="color:#6d28d9;font-weight:700">Supplier ships directly</span>';
                }
                html += '</div>';
                if (!ok) hasIssue = true;
            });
            document.getElementById('dispatch-avail-rows').innerHTML = html;
            document.getElementById('dispatch-avail-box').style.display = 'block';
            if (hasIssue) {
                document.getElementById('dispatch-submit-btn').disabled = true;
                var errEl = document.getElementById('dispatch-error');
                errEl.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Insufficient stock for one or more items. Resolve before dispatching.';
                errEl.style.display = 'block';
            }
        });
    }
}

function closeDispatchModal() {
    document.getElementById('dispatch-modal').style.display = 'none';
}

function doDispatch() {
    var btn = document.getElementById('dispatch-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Dispatching…';

    var fd = new FormData();
    fd.append(_csrf_n, _csrf_v);
    fd.append('doc_id',          DF_DOC_ID);
    fd.append('tracking_number', document.getElementById('dispatch-tracking').value);
    fd.append('carrier_info',    document.getElementById('dispatch-carrier').value);

    fetch(DF_DISPATCH_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            _csrf_v = data.csrf_hash || _csrf_v;
            if (data.success) {
                closeDispatchModal();
                alert_float('success', data.message || 'Delivery dispatched!');
                setTimeout(function() {
                    if (data.pdf_url) { window.open(data.pdf_url, '_blank'); }
                    location.reload();
                }, 800);
            } else {
                var errEl = document.getElementById('dispatch-error');
                errEl.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + (data.error || 'Dispatch failed');
                errEl.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-paper-plane"></i> Dispatch Now';
            }
        })
        .catch(function() {
            document.getElementById('dispatch-error').innerHTML = 'Network error. Please try again.';
            document.getElementById('dispatch-error').style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Dispatch Now';
        });
}

// Close dispatch modal on outside click
document.getElementById('dispatch-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDispatchModal();
});
</script>
