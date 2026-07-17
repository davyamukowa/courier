<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $is_edit = !empty($order); ?>
<?php $status  = $order['status'] ?? 'draft'; ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'deliveries',
    'inv_branches'  => [],
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px;background:#f0f7f2;min-height:100vh">

<style>
.so-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(13,40,24,.07); border:1px solid #d4e8db; margin-bottom:18px; }
.so-card-hdr { display:flex; align-items:center; gap:10px; padding:12px 20px; border-bottom:1px solid #dff0e6; background:#edf7f1; border-radius:10px 10px 0 0; }
.so-card-hdr-title { font-size:13px; font-weight:700; color:#1a2520; margin:0; flex:1; }
.so-card-hdr-title i { color:#16a34a; margin-right:6px; }
.so-body { padding:20px; }
.so-label { font-size:11px; font-weight:700; color:#3d4f45; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:5px; }
.so-input { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; transition:border .15s,box-shadow .15s; }
.so-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); background:#fff; }
.so-select { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; }
.so-select:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.so-textarea { width:100%; border:1px solid #c8dfd0; border-radius:7px; padding:9px 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; resize:vertical; min-height:70px; }
.so-textarea:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.so-fg { margin-bottom:14px; }
.so-row { display:flex; gap:14px; flex-wrap:wrap; }
.so-col-2 { flex:0 0 calc(50% - 7px); min-width:200px; }
.so-col-3 { flex:0 0 calc(33.333% - 10px); min-width:180px; }

/* Searchable dropdown */
.so-srch-wrap { position:relative; }
.so-srch-input { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px 7px 0 0; padding:0 30px 0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; box-sizing:border-box; transition:border .15s; }
.so-srch-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.so-srch-icon { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#9cbaaa; font-size:11px; pointer-events:none; }
.so-srch-list { display:none; position:absolute; top:33px; left:0; right:0; background:#fff; border:1px solid #c8dfd0; border-top:none; border-radius:0 0 7px 7px; max-height:200px; overflow-y:auto; z-index:500; box-shadow:0 4px 12px rgba(13,40,24,.10); }
.so-srch-list.open { display:block; }
.so-srch-opt { padding:7px 11px; font-size:13px; color:#1a2520; cursor:pointer; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.so-srch-opt:hover, .so-srch-opt.active { background:#edf7f1; color:#14532d; }
.so-srch-opt.placeholder { color:#9cbaaa; font-style:italic; }

/* Barcode bar */
.so-barcode-bar { display:flex; align-items:center; gap:10px; padding:10px 16px; background:linear-gradient(135deg,#b8ddc8,#9ecfb2); border-radius:9px; margin-bottom:18px; border:1px solid #7dba96; }
.so-barcode-bar label { color:#0d2818; font-size:12px; font-weight:700; white-space:nowrap; margin:0; }
.so-barcode-input { flex:1; height:38px; border:2px solid #7dba96; border-radius:7px; padding:0 14px; font-size:14px; color:#1a2520; background:rgba(255,255,255,.65); outline:none; }
.so-barcode-input:focus { border-color:#16a34a; background:#fff; box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.so-barcode-input::placeholder { color:#3d6b4f; }

/* Items table */
.so-tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:12.5px; }
.so-tbl thead th { padding:9px 8px; font-size:10px; font-weight:700; color:#4a5e54; text-transform:uppercase; letter-spacing:.6px; background:#f5faf7; border-bottom:2px solid #e2ece6; white-space:nowrap; }
.so-tbl tbody td { padding:5px 5px; border-bottom:1px solid #edf5f0; vertical-align:middle; }
.so-tbl tbody tr:hover { background:#f8fdf9; }
.so-tbl tbody tr:last-child td { border-bottom:none; }
.so-tbl-input { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 7px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.so-tbl-input:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.10); }
.so-tbl-select { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 5px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.so-add-row { display:flex; align-items:center; gap:7px; padding:10px 14px; cursor:pointer; color:#16a34a; font-size:13px; font-weight:600; border-top:1px solid #edf5f0; }
.so-add-row:hover { background:#f0faf4; }

/* Totals */
.so-totals { background:#edf7f1; border-radius:9px; border:1px solid #c8dfd0; padding:14px 20px; }
.so-total-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #edf5f0; font-size:13px; }
.so-total-row:last-child { border-bottom:none; padding-top:10px; }
.so-total-row .lbl { color:#3d4f45; font-weight:600; }
.so-total-row .val { font-weight:700; color:#1a2520; font-size:14px; }
.so-total-row.final .lbl { color:#1a2520; font-size:14px; font-weight:700; }
.so-total-row.final .val { color:#16a34a; font-size:16px; }
.so-ship-input { height:28px; border:1px solid #c8dfd0; border-radius:5px; padding:0 7px; font-size:13px; color:#1a2520; background:#fff; outline:none; width:110px; text-align:right; }

/* Top action bar */
.so-topbar { display:flex; align-items:center; gap:10px; padding:12px 20px; background:#edf7f1; border-radius:10px; border:1px solid #c8dfd0; box-shadow:0 1px 6px rgba(13,40,24,.06); margin-bottom:18px; }
.so-doc-num { font-size:15px; font-weight:700; color:#1a2520; }
.so-doc-num span { color:#16a34a; }

.btn-so-save    { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(22,163,74,.35); text-decoration:none!important; }
.btn-so-save:hover { opacity:.9; color:#fff; }
.btn-so-confirm { background:linear-gradient(135deg,#0d9488,#0f766e); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(20,184,166,.35); text-decoration:none!important; }
.btn-so-confirm:hover { opacity:.9; color:#fff; }
.btn-so-dn      { background:linear-gradient(135deg,#0284c7,#0369a1); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(2,132,199,.30); text-decoration:none!important; }
.btn-so-dn:hover { opacity:.9; color:#fff; }
.btn-so-back    { background:#f0faf4; color:#166534!important; border:1px solid #bbf7d0; border-radius:7px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none!important; }
.btn-so-back:hover { background:#dcfce7; color:#14532d; text-decoration:none; }
</style>

<!-- Top bar -->
<div class="so-topbar">
  <a href="<?php echo admin_url('pos_system/sales_orders'); ?>" class="btn-so-back">
    <i class="fa fa-arrow-left"></i> Back
  </a>
  <div class="so-doc-num">
    <i class="fa fa-file-text" style="color:#16a34a;margin-right:6px"></i>
    <?php echo $is_edit ? 'Edit Sales Order' : 'New Sales Order'; ?>
    <?php if ($is_edit): ?><span>#<?php echo htmlspecialchars($order['so_number']); ?></span><?php endif; ?>
  </div>
  <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
    <?php if ($is_edit): ?>
    <span class="ibadge ibadge-<?php echo $status; ?>"><?php echo ucwords(str_replace('_',' ',$status)); ?></span>
    <?php endif; ?>
    <button type="button" class="btn-so-save" onclick="soSave('draft')">
      <i class="fa fa-save"></i> Save Draft
    </button>
    <?php if (!$is_edit || $status === 'draft'): ?>
    <button type="button" class="btn-so-confirm" onclick="soSave('confirm')">
      <i class="fa fa-check-circle"></i> Confirm Order
    </button>
    <?php endif; ?>
    <?php if ($is_edit && in_array($status, ['confirmed','processing','partially_delivered'])): ?>
    <a href="<?php echo admin_url('pos_system/inv_form/delivery?so_id='.$order['id']); ?>" class="btn-so-dn">
      <i class="fa fa-truck"></i> Create Delivery
    </a>
    <?php endif; ?>
  </div>
</div>

<input type="hidden" id="so-order-id" value="<?php echo $is_edit ? (int)$order['id'] : 0; ?>">
<input type="hidden" id="so-branch-id" value="<?php echo (int)$branch_id; ?>">

<!-- Header: 2 columns -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">

  <!-- Left: Order Info -->
  <div class="so-card">
    <div class="so-card-hdr">
      <h5 class="so-card-hdr-title"><i class="fa fa-file-alt"></i> Order Information</h5>
    </div>
    <div class="so-body">
      <div class="so-row">
        <div class="so-col-2 so-fg">
          <label class="so-label">Order Date <span style="color:#ef4444">*</span></label>
          <input type="date" id="so-date" class="so-input"
                 value="<?php echo $is_edit ? $order['date'] : date('Y-m-d'); ?>">
        </div>
        <div class="so-col-2 so-fg">
          <label class="so-label">Expected Delivery</label>
          <input type="date" id="so-exp-delivery" class="so-input"
                 value="<?php echo $is_edit ? ($order['expected_delivery']??'') : ''; ?>">
        </div>
      </div>

      <div class="so-fg">
        <label class="so-label">Link Perfex Invoice (optional)</label>
        <div class="so-srch-wrap" id="so-invoice-wrap">
          <input type="text" class="so-srch-input" id="so-invoice-search" autocomplete="off"
                 placeholder="Search invoice…"
                 value="<?php
                   if ($is_edit && $order['crm_invoice_id']) {
                     foreach ($invoices as $inv) {
                       if ((int)$inv['id'] === (int)$order['crm_invoice_id']) {
                         echo htmlspecialchars($inv['display_number'].' — '.($inv['customer_name']??''));
                         break;
                       }
                     }
                   }
                 ?>">
          <i class="fa fa-chevron-down so-srch-icon"></i>
          <div class="so-srch-list" id="so-invoice-list">
            <div class="so-srch-opt placeholder" data-value="" data-clientid="">— None —</div>
            <?php foreach ($invoices as $inv): ?>
            <div class="so-srch-opt"
                 data-value="<?php echo (int)$inv['id']; ?>"
                 data-label="<?php echo htmlspecialchars($inv['display_number'].' — '.($inv['customer_name']??'')); ?>"
                 data-clientid="<?php echo (int)$inv['clientid']; ?>"
                 data-customername="<?php echo htmlspecialchars($inv['customer_name']??''); ?>">
              <strong><?php echo htmlspecialchars($inv['display_number']); ?></strong>
              <?php if ($inv['customer_name']): ?>
              <span style="color:#6b7c72"> — <?php echo htmlspecialchars($inv['customer_name']); ?></span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="so-invoice-id" value="<?php echo $is_edit ? (int)$order['crm_invoice_id'] : ''; ?>">
        </div>
        <span style="font-size:11px;color:#56665e;margin-top:4px;display:block">
          <i class="fa fa-info-circle"></i> Selecting an invoice auto-fills customer &amp; items below.
        </span>
      </div>

      <div class="so-fg so-last">
        <label class="so-label">Notes</label>
        <textarea id="so-notes" class="so-textarea" placeholder="Internal notes, terms…"><?php echo $is_edit ? htmlspecialchars($order['notes']??'') : ''; ?></textarea>
      </div>
    </div>
  </div>

  <!-- Right: Customer & Parties -->
  <div class="so-card">
    <div class="so-card-hdr">
      <h5 class="so-card-hdr-title"><i class="fa fa-user"></i> Customer & Parties</h5>
    </div>
    <div class="so-body">
      <div class="so-fg">
        <label class="so-label">Customer</label>
        <div class="so-srch-wrap" id="so-customer-wrap">
          <input type="text" class="so-srch-input" id="so-customer-search" autocomplete="off"
                 placeholder="Search customer…"
                 value="<?php echo $is_edit ? htmlspecialchars($order['customer_name']??'') : ''; ?>">
          <i class="fa fa-chevron-down so-srch-icon"></i>
          <div class="so-srch-list" id="so-customer-list">
            <div class="so-srch-opt placeholder" data-value="" data-address="">— Select Customer —</div>
            <?php foreach ($clients as $cl): ?>
            <div class="so-srch-opt"
                 data-value="<?php echo (int)$cl['id']; ?>"
                 data-label="<?php echo htmlspecialchars($cl['name']); ?>"
                 data-address="<?php echo htmlspecialchars($cl['full_address']); ?>">
              <?php echo htmlspecialchars($cl['name']); ?>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="so-customer-id" value="<?php echo $is_edit ? (int)($order['client_id']??0) : ''; ?>">
        </div>
        <input type="hidden" id="so-customer-name" value="<?php echo $is_edit ? htmlspecialchars($order['customer_name']??'') : ''; ?>">
      </div>

      <div class="so-fg">
        <label class="so-label">Delivery Address</label>
        <textarea id="so-address" class="so-textarea" rows="2"
                  placeholder="Auto-filled from customer, or enter manually…"><?php echo $is_edit ? htmlspecialchars($order['address']??'') : ''; ?></textarea>
      </div>

      <div class="so-row">
        <div class="so-col-2 so-fg">
          <label class="so-label">Sales Person</label>
          <div class="so-srch-wrap" id="so-person-wrap">
            <input type="text" class="so-srch-input" id="so-person-search" autocomplete="off"
                   placeholder="Search staff…"
                   value="<?php
                     if ($is_edit && !empty($order['sales_person'])) echo htmlspecialchars($order['sales_person']);
                     elseif (!$is_edit && $current_staff_name) echo htmlspecialchars($current_staff_name);
                   ?>">
            <i class="fa fa-chevron-down so-srch-icon"></i>
            <div class="so-srch-list" id="so-person-list">
              <?php foreach ($staff_list as $s): ?>
              <div class="so-srch-opt"
                   data-value="<?php echo (int)$s['id']; ?>"
                   data-label="<?php echo htmlspecialchars($s['name']); ?>">
                <?php echo htmlspecialchars($s['name']); ?>
                <?php if ((int)$s['id'] === (int)$current_staff_id): ?>
                <span style="font-size:10px;color:#16a34a;margin-left:4px">(you)</span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="so-person-id" value="<?php echo $is_edit ? (int)($order['sales_person_id']??0) : $current_staff_id; ?>">
          </div>
          <input type="hidden" id="so-person-name" value="<?php echo $is_edit ? htmlspecialchars($order['sales_person']??'') : htmlspecialchars($current_staff_name); ?>">
        </div>
        <div class="so-col-2 so-fg">
          <label class="so-label">Project</label>
          <div class="so-srch-wrap" id="so-project-wrap">
            <input type="text" class="so-srch-input" id="so-project-search" autocomplete="off"
                   placeholder="Search project…"
                   value="<?php echo $is_edit ? htmlspecialchars($order['project_name']??'') : ''; ?>">
            <i class="fa fa-chevron-down so-srch-icon"></i>
            <div class="so-srch-list" id="so-project-list">
              <div class="so-srch-opt placeholder" data-value="">— None —</div>
              <?php foreach ($projects as $proj): ?>
              <div class="so-srch-opt"
                   data-value="<?php echo (int)$proj['id']; ?>"
                   data-label="<?php echo htmlspecialchars($proj['name']); ?>">
                <?php echo htmlspecialchars($proj['name']); ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="so-project-id" value="<?php echo $is_edit ? (int)($order['project_id']??0) : ''; ?>">
          </div>
          <input type="hidden" id="so-project-name" value="<?php echo $is_edit ? htmlspecialchars($order['project_name']??'') : ''; ?>">
        </div>
      </div>
    </div>
  </div>

</div><!-- /header grid -->

<!-- Barcode Scanner -->
<div class="so-barcode-bar">
  <label><i class="fa fa-barcode" style="font-size:16px;margin-right:4px"></i> Barcode Scanner</label>
  <input type="text" id="so-barcode-input" class="so-barcode-input" placeholder="Scan or type barcode / SKU, then press Enter…" autocomplete="off">
  <span style="color:#3d6b4f;font-size:12px;white-space:nowrap"><i class="fa fa-info-circle"></i> Scanned items are added to the table below</span>
</div>

<!-- Line Items -->
<div class="so-card">
  <div class="so-card-hdr">
    <h5 class="so-card-hdr-title"><i class="fa fa-list"></i> Line Items</h5>
    <button type="button" class="btn-inv-primary" style="padding:5px 14px;font-size:12px" onclick="soAddLine()">
      <i class="fa fa-plus"></i> Add Row
    </button>
  </div>
  <div style="overflow-x:auto">
    <table class="so-tbl">
      <thead>
        <tr>
          <th style="width:24px">#</th>
          <th style="min-width:220px">PRODUCT</th>
          <th style="width:80px;text-align:right">QTY</th>
          <th style="width:110px;text-align:right">UNIT PRICE</th>
          <th style="width:90px;text-align:right">DISC %</th>
          <th style="width:130px">TAX</th>
          <th style="width:110px;text-align:right">SUBTOTAL</th>
          <th style="width:110px;text-align:right">TOTAL</th>
          <th style="width:34px"></th>
        </tr>
      </thead>
      <tbody id="so-lines">
        <tr id="so-no-lines">
          <td colspan="9" style="text-align:center;padding:28px;color:#56665e;font-size:13px">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>
            No items added. Click <strong>Add Row</strong> or scan a barcode.
          </td>
        </tr>
        <?php if (!empty($items)): ?>
        <?php /* pre-populate existing items — JS will render them on DOMContentLoaded */ ?>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="so-add-row" onclick="soAddLine()">
      <i class="fa fa-plus-circle"></i> Add another item
    </div>
  </div>
</div>

<!-- Totals + Notes -->
<div style="display:grid;grid-template-columns:1fr 360px;gap:18px;align-items:start">

  <div class="so-card">
    <div class="so-card-hdr">
      <h5 class="so-card-hdr-title"><i class="fa fa-sticky-note"></i> Internal Note</h5>
    </div>
    <div class="so-body">
      <textarea id="so-note-extra" class="so-textarea" placeholder="Any additional instructions or remarks…"></textarea>
    </div>
  </div>

  <div class="so-totals">
    <div class="so-total-row">
      <span class="lbl">Subtotal :</span>
      <span class="val" id="so-subtotal-display">0.00</span>
    </div>
    <div class="so-total-row">
      <span class="lbl">Total Discount :</span>
      <span class="val" id="so-discount-display" style="color:#dc2626">- 0.00</span>
    </div>
    <div class="so-total-row">
      <span class="lbl">Total Tax :</span>
      <span class="val" id="so-tax-display">0.00</span>
    </div>
    <div class="so-total-row">
      <span class="lbl">Shipping Fee :</span>
      <span class="val">
        <input type="number" class="so-ship-input" id="so-shipping"
               value="<?php echo $is_edit ? number_format((float)($order['shipping_fee']??0),2,'.','') : '0.00'; ?>"
               step="0.01" min="0" oninput="soUpdateTotals()">
      </span>
    </div>
    <div class="so-total-row final">
      <span class="lbl">Grand Total :</span>
      <span class="val" id="so-grand-total">0.00</span>
    </div>
  </div>

</div>

<input type="hidden" id="so-subtotal-val"  value="<?php echo $is_edit ? (float)($order['subtotal']??0) : 0; ?>">
<input type="hidden" id="so-discount-val"  value="<?php echo $is_edit ? (float)($order['discount_amount']??0) : 0; ?>">
<input type="hidden" id="so-tax-val"       value="<?php echo $is_edit ? (float)($order['tax_amount']??0) : 0; ?>">
<input type="hidden" id="so-total-val"     value="<?php echo $is_edit ? (float)($order['total_amount']??0) : 0; ?>">

</div><!-- /inv-content -->
</div><!-- /content -->
</div><!-- /wrapper -->

<?php init_tail(); ?>
<script>
/* ═══════════════════════════════════════
   Sales Order Form JS
   ═══════════════════════════════════════ */
var SO_SAVE_URL = '<?php echo admin_url('pos_system/so_save'); ?>';
var SO_INV_URL  = '<?php echo admin_url('pos_system/inv_ajax/invoice_items'); ?>';
var _csrf_n = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v = '<?php echo $this->security->get_csrf_hash(); ?>';

var SO_PRODUCTS = <?php
    $pp = [];
    foreach ($products as $p) {
        $pp[] = ['id'=>(int)$p['id'],'name'=>$p['name'],'sku'=>$p['sku']??'',
                 'price'=>(float)$p['selling_price'],'tax_id'=>(int)($p['tax_rate_id']??0),
                 'barcode'=>$p['barcode']??''];
    }
    echo json_encode($pp);
?>;

var SO_TAX_RATES = <?php
    $tr = [];
    if (!empty($tax_rates)) {
        foreach ($tax_rates as $t) {
            $tr[] = ['id'=>(int)$t['id'],'name'=>$t['name'],'rate'=>(float)$t['rate']];
        }
    }
    echo json_encode($tr);
?>;

var SO_EXISTING_ITEMS = <?php echo json_encode(array_values($items)); ?>;

var soIdx = 0;
var prodByBarcode = {}, prodById = {}, taxById = {};
SO_PRODUCTS.forEach(function(p) {
    prodById[p.id] = p;
    if (p.barcode) prodByBarcode[p.barcode.toLowerCase()] = p;
    if (p.sku)     prodByBarcode[p.sku.toLowerCase()]     = p;
});
SO_TAX_RATES.forEach(function(t) { taxById[t.id] = t; });

var prodOpts = '<option value="">— Select Product —</option>';
SO_PRODUCTS.forEach(function(p) {
    prodOpts += '<option value="'+p.id+'">'+escHtml(p.name)+(p.sku?' ['+p.sku+']':'')+'</option>';
});
var taxOpts = '<option value="0" data-rate="0">No Tax</option>';
SO_TAX_RATES.forEach(function(t) {
    taxOpts += '<option value="'+t.id+'" data-rate="'+t.rate+'">'+escHtml(t.name)+'</option>';
});

function escHtml(s) {
    return String(s).replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}
function fmtNum(n) { return parseFloat(n||0).toFixed(2); }

/* ── Line Items ─────────────────────────── */
function soAddLine(pid, price, qty, dPct, taxId) {
    $('#so-no-lines').remove();
    var idx   = soIdx++;
    var avail = 0;
    var tr = '<tr id="so-line-'+idx+'">';
    tr += '<td style="padding:6px 8px;color:#56665e;font-weight:700;text-align:center" id="so-ln-num-'+idx+'">'+idx+'</td>';
    tr += '<td style="padding:4px 5px"><select class="so-tbl-select so-pid" data-idx="'+idx+'" onchange="soProductChange('+idx+')" style="min-width:200px">'+prodOpts+'</select></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="so-tbl-input so-qty" data-idx="'+idx+'" value="'+(qty||1)+'" min="0.001" step="any" onchange="soRecalc('+idx+')" style="width:70px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="so-tbl-input so-price" data-idx="'+idx+'" value="'+(price||0).toFixed(2)+'" min="0" step="any" onchange="soRecalc('+idx+')" style="width:100px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="so-tbl-input so-disc-pct" data-idx="'+idx+'" value="'+(dPct||0)+'" min="0" max="100" step="any" onchange="soRecalc('+idx+')" style="width:70px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><select class="so-tbl-select so-tax" data-idx="'+idx+'" onchange="soRecalc('+idx+')" style="min-width:110px">'+taxOpts+'</select></td>';
    tr += '<td style="padding:4px 8px;text-align:right;font-weight:600;color:#1a2520" id="so-sub-'+idx+'">0.00</td>';
    tr += '<td style="padding:4px 8px;text-align:right;font-weight:700;color:#1a2520" id="so-total-'+idx+'">0.00</td>';
    tr += '<td style="padding:4px 5px;text-align:center"><button type="button" class="btn-inv-icon danger" onclick="soRemoveLine('+idx+')"><i class="fa fa-times"></i></button></td>';
    tr += '</tr>';
    $('#so-lines').append(tr);

    if (pid) {
        $('.so-pid[data-idx="'+idx+'"]').val(pid);
        soProductChange(idx);
    }
    if (taxId) {
        $('.so-tax[data-idx="'+idx+'"]').val(taxId);
    }
    soRecalc(idx);
    soRenumber();
}

function soProductChange(idx) {
    var pid = parseInt($('.so-pid[data-idx="'+idx+'"]').val());
    if (pid && prodById[pid]) {
        var p = prodById[pid];
        $('.so-price[data-idx="'+idx+'"]').val((p.price||0).toFixed(2));
        if (p.tax_id) $('.so-tax[data-idx="'+idx+'"]').val(p.tax_id);
    }
    soRecalc(idx);
}

function soRecalc(idx) {
    var qty     = parseFloat($('.so-qty[data-idx="'+idx+'"]').val())      || 0;
    var price   = parseFloat($('.so-price[data-idx="'+idx+'"]').val())    || 0;
    var discPct = parseFloat($('.so-disc-pct[data-idx="'+idx+'"]').val()) || 0;
    var rate    = parseFloat($('.so-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
    var sub     = qty * price;
    var dAmt    = sub * (discPct / 100);
    var taxAmt  = (sub - dAmt) * rate;
    var total   = sub - dAmt + taxAmt;
    $('#so-sub-'+idx).text(fmtNum(sub));
    $('#so-total-'+idx).text(fmtNum(total));
    soUpdateTotals();
}

function soRemoveLine(idx) {
    $('#so-line-'+idx).remove();
    soUpdateTotals();
    if (!$('#so-lines tr').length) {
        $('#so-lines').append('<tr id="so-no-lines"><td colspan="9" style="text-align:center;padding:28px;color:#56665e;font-size:13px"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>No items added. Click <strong>Add Row</strong> or scan a barcode.</td></tr>');
    }
    soRenumber();
}

function soRenumber() {
    var n = 1; $('[id^="so-ln-num-"]').each(function(){ $(this).text(n++); });
}

function soUpdateTotals() {
    var sub = 0, disc = 0, tax = 0;
    $('[id^="so-line-"]:not(#so-no-lines)').each(function() {
        var idx   = this.id.replace('so-line-','');
        if (isNaN(parseInt(idx))) return;
        var qty   = parseFloat($('.so-qty[data-idx="'+idx+'"]').val())      || 0;
        var price = parseFloat($('.so-price[data-idx="'+idx+'"]').val())    || 0;
        var dPct  = parseFloat($('.so-disc-pct[data-idx="'+idx+'"]').val()) || 0;
        var rate  = parseFloat($('.so-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
        var s     = qty * price;
        var d     = s * (dPct / 100);
        sub  += s;
        disc += d;
        tax  += (s - d) * rate;
    });
    var ship  = parseFloat($('#so-shipping').val()) || 0;
    var grand = sub - disc + tax + ship;

    $('#so-subtotal-display').text(fmtNum(sub));
    $('#so-discount-display').text('- '+fmtNum(disc));
    $('#so-tax-display').text(fmtNum(tax));
    $('#so-grand-total').text(fmtNum(grand));

    $('#so-subtotal-val').val(sub.toFixed(4));
    $('#so-discount-val').val(disc.toFixed(4));
    $('#so-tax-val').val(tax.toFixed(4));
    $('#so-total-val').val(grand.toFixed(4));
}

/* ── Barcode scanner ────────────────────── */
$('#so-barcode-input').on('keypress', function(e) {
    if (e.which !== 13) return;
    var code = $(this).val().trim().toLowerCase();
    if (!code) return;
    var prod = prodByBarcode[code];
    if (prod) {
        soAddLine(prod.id, prod.price, 1, 0, prod.tax_id);
        $(this).val('').focus();
    } else {
        $(this).css('border-color','#ef4444');
        setTimeout(function(){ $('#so-barcode-input').css('border-color',''); }, 1000);
    }
});

/* ── Searchable dropdown widget ─────────── */
function soSrchInit(baseId, onSelect) {
    var $search = $('#so-' + baseId + '-search');
    var $list   = $('#so-' + baseId + '-list');
    var $hidden = $('#so-' + baseId + '-id');

    $search.on('focus click', function() {
        soSrchFilter(baseId, $search.val());
        $list.addClass('open');
    });
    $search.on('input', function() {
        soSrchFilter(baseId, $(this).val());
        $list.addClass('open');
        if (!$(this).val()) $hidden.val('');
    });
    $list.on('mousedown', '.so-srch-opt', function(e) {
        e.preventDefault();
        var val   = $(this).data('value');
        var label = $(this).data('label') || $(this).text().trim();
        $search.val(label);
        $hidden.val(val || '');
        $list.find('.so-srch-opt').removeClass('active');
        $(this).addClass('active');
        $list.removeClass('open');
        if (typeof onSelect === 'function') onSelect(val, label, $(this));
    });
    $search.on('blur', function() {
        setTimeout(function(){ $list.removeClass('open'); }, 150);
    });
}

function soSrchFilter(baseId, q) {
    q = (q||'').toLowerCase();
    $('#so-' + baseId + '-list .so-srch-opt').each(function() {
        var t = ($(this).data('label') || $(this).text()).toLowerCase();
        $(this).toggle(!q || t.indexOf(q) !== -1);
    });
}

function soSrchSetValue(baseId, val, label) {
    $('#so-' + baseId + '-search').val(label || '');
    $('#so-' + baseId + '-id').val(val || '');
}

/* ── Load invoice items ─────────────────── */
function soLoadInvoiceItems(invId) {
    if (!invId) return;
    $.getJSON(SO_INV_URL, {id: invId}, function(r) {
        if (r.client_id && !$('#so-customer-id').val()) {
            soSrchSetValue('customer', r.client_id, r.customer_name);
            $('#so-customer-name').val(r.customer_name || '');
            if (r.address) $('#so-address').val(r.address);
        }
        if (r.project_id) {
            soSrchSetValue('project', r.project_id, r.project_name);
            $('#so-project-name').val(r.project_name || '');
        }
        if (r.items && r.items.length) {
            $('#so-lines').empty(); soIdx = 0;
            $.each(r.items, function(i, it) {
                soAddLine(it.product_id||null, it.unit_price, it.qty, it.disc_pct, it.tax_rate_id||0);
            });
        }
    });
}

/* ── Save ───────────────────────────────── */
function soSave(mode) {
    var lines = [];
    var ok = true;
    $('[id^="so-line-"]:not(#so-no-lines)').each(function() {
        var idx = this.id.replace('so-line-','');
        if (isNaN(parseInt(idx))) return;
        var pid = parseInt($('.so-pid[data-idx="'+idx+'"]').val());
        if (!pid) { ok = false; alert('Select a product for every row.'); return false; }
        var qty    = parseFloat($('.so-qty[data-idx="'+idx+'"]').val())      || 0;
        var price  = parseFloat($('.so-price[data-idx="'+idx+'"]').val())    || 0;
        var dPct   = parseFloat($('.so-disc-pct[data-idx="'+idx+'"]').val()) || 0;
        var taxSel = $('.so-tax[data-idx="'+idx+'"] option:selected');
        var rate   = parseFloat(taxSel.data('rate')) || 0;
        var taxId  = parseInt(taxSel.val()) || 0;
        var pname  = $('.so-pid[data-idx="'+idx+'"] option:selected').text().replace(/\s*\[.*\]$/,'').trim();
        lines.push({
            product_id:   pid,
            product_name: pname,
            qty:          qty,
            unit_price:   price,
            discount_pct: dPct,
            tax_rate_id:  taxId,
            tax_rate_pct: rate,
        });
    });
    if (!ok) return;
    if (!lines.length) { alert('Add at least one item.'); return; }

    var d = new FormData();
    d.append('id',                parseInt($('#so-order-id').val()) || 0);
    d.append('branch_id',         parseInt($('#so-branch-id').val()) || 0);
    d.append('client_id',         $('#so-customer-id').val() || '');
    d.append('customer_name',     $('#so-customer-name').val() || $('#so-customer-search').val() || '');
    d.append('address',           $('#so-address').val() || '');
    d.append('project_id',        $('#so-project-id').val() || '');
    d.append('project_name',      $('#so-project-name').val() || $('#so-project-search').val() || '');
    d.append('sales_person_id',   $('#so-person-id').val() || '');
    d.append('sales_person',      $('#so-person-name').val() || $('#so-person-search').val() || '');
    d.append('date',              $('#so-date').val() || '');
    d.append('expected_delivery', $('#so-exp-delivery').val() || '');
    d.append('notes',             $('#so-notes').val() || '');
    d.append('subtotal',          $('#so-subtotal-val').val());
    d.append('discount_amount',   $('#so-discount-val').val());
    d.append('tax_amount',        $('#so-tax-val').val());
    d.append('shipping_fee',      $('#so-shipping').val() || '0');
    d.append('total_amount',      $('#so-total-val').val());
    d.append('crm_invoice_id',    $('#so-invoice-id').val() || '');
    d.append(_csrf_n, _csrf_v);

    lines.forEach(function(l, i) {
        Object.keys(l).forEach(function(k) { d.append('lines['+i+']['+k+']', l[k]); });
    });

    var $btns = $('button[onclick^="soSave"]');
    $btns.prop('disabled', true);

    $.ajax({
        url:         SO_SAVE_URL,
        type:        'POST',
        data:        d,
        contentType: false,
        processData: false,
        dataType:    'json',
        success: function(r) {
            $btns.prop('disabled', false);
            if (r.csrf_hash) _csrf_v = r.csrf_hash;
            if (r.success) {
                if (mode === 'confirm' && r.id) {
                    window.location.href = '<?php echo admin_url('pos_system/so_action/'); ?>' + r.id + '?action=confirm';
                } else {
                    window.location.href = '<?php echo admin_url('pos_system/so_form/'); ?>' + r.id;
                }
            } else {
                alert_float('danger', r.error || 'Save failed.');
            }
        },
        error: function(xhr) {
            $btns.prop('disabled', false);
            var msg = 'Save failed.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            alert_float('danger', msg + ' (HTTP ' + xhr.status + ')');
        }
    });
}

/* ── Init on ready ──────────────────────── */
$(document).ready(function() {
    // Invoice dropdown
    soSrchInit('invoice', function(val, label, $opt) {
        $('#so-invoice-id').val(val || '');
        if (val) soLoadInvoiceItems(val);
    });

    // Customer dropdown
    soSrchInit('customer', function(val, label, $opt) {
        $('#so-customer-name').val($opt.data('label') || '');
        if ($opt.data('address')) $('#so-address').val($opt.data('address'));
    });

    // Sales person dropdown
    soSrchInit('person', function(val, label, $opt) {
        $('#so-person-name').val($opt.data('label') || '');
    });

    // Project dropdown
    soSrchInit('project', function(val, label, $opt) {
        $('#so-project-name').val($opt.data('label') || '');
    });

    // Pre-populate existing line items (edit mode)
    if (SO_EXISTING_ITEMS.length) {
        $.each(SO_EXISTING_ITEMS, function(i, it) {
            soAddLine(it.product_id||null, parseFloat(it.unit_price)||0,
                      parseFloat(it.qty_ordered)||1, parseFloat(it.discount_pct)||0,
                      parseInt(it.tax_rate_id)||0);
        });
    }

    soUpdateTotals();
});
</script>
