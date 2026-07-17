<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $is_edit = $doc_id > 0; ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'receiving',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px;background:#f0f7f2;min-height:100vh">

<style>
.rf-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(13,40,24,.07); border:1px solid #d4e8db; margin-bottom:18px; }
.rf-card-hdr { display:flex; align-items:center; gap:10px; padding:12px 20px; border-bottom:1px solid #dff0e6; background:#edf7f1; border-radius:10px 10px 0 0; }
.rf-card-hdr-title { font-size:13px; font-weight:700; color:#1a2520; margin:0; flex:1; }
.rf-card-hdr-title i { color:#16a34a; margin-right:6px; }
.rf-body { padding:20px; }
.rf-label { font-size:11px; font-weight:700; color:#3d4f45; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:5px; }
.rf-input { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; transition:border .15s,box-shadow .15s; }
.rf-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); background:#fff; }
.rf-select { width:100%; height:34px; border:1px solid #c8dfd0; border-radius:7px; padding:0 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; }
.rf-select:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.rf-textarea { width:100%; border:1px solid #c8dfd0; border-radius:7px; padding:9px 11px; font-size:13px; color:#1a2520; background:#fafdfb; outline:none; resize:vertical; min-height:70px; }
.rf-textarea:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.13); }
.rf-fg { margin-bottom:14px; }
.rf-row { display:flex; gap:14px; flex-wrap:wrap; }
.rf-col-2 { flex:0 0 calc(50% - 7px); min-width:200px; }
.rf-col-3 { flex:0 0 calc(33.333% - 10px); min-width:180px; }
.rf-col-4 { flex:0 0 calc(25% - 11px); min-width:150px; }

/* Barcode bar */
.rf-barcode-bar { display:flex; align-items:center; gap:10px; padding:10px 16px; background:linear-gradient(135deg,#b8ddc8,#9ecfb2); border-radius:9px; margin-bottom:18px; border:1px solid #7dba96; }
.rf-barcode-bar label { color:#0d2818; font-size:12px; font-weight:700; white-space:nowrap; margin:0; }
.rf-barcode-input { flex:1; height:38px; border:2px solid #7dba96; border-radius:7px; padding:0 14px; font-size:14px; color:#1a2520; background:rgba(255,255,255,.65); outline:none; }
.rf-barcode-input:focus { border-color:#16a34a; background:#fff; box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.rf-barcode-input::placeholder { color:#3d6b4f; }

/* Items table */
.rf-tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:12.5px; }
.rf-tbl thead th { padding:9px 10px; font-size:10px; font-weight:700; color:#4a5e54; text-transform:uppercase; letter-spacing:.6px; background:#f5faf7; border-bottom:2px solid #e2ece6; white-space:nowrap; }
.rf-tbl tbody td { padding:6px 6px; border-bottom:1px solid #edf5f0; vertical-align:middle; }
.rf-tbl tbody tr:hover { background:#f8fdf9; }
.rf-tbl tbody tr:last-child td { border-bottom:none; }
.rf-tbl-input { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 7px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.rf-tbl-input:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.10); }
.rf-tbl-select { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 5px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.rf-add-row { display:flex; align-items:center; gap:7px; padding:10px 14px; cursor:pointer; color:#16a34a; font-size:13px; font-weight:600; border-top:1px solid #edf5f0; }
.rf-add-row:hover { background:#f0faf4; }

/* Totals */
.rf-totals { background:#edf7f1; border-radius:9px; border:1px solid #c8dfd0; padding:14px 20px; }
.rf-total-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #edf5f0; font-size:13px; }
.rf-total-row:last-child { border-bottom:none; padding-top:10px; }
.rf-total-row .label { color:#3d4f45; font-weight:600; }
.rf-total-row .value { font-weight:700; color:#1a2520; font-size:14px; }
.rf-total-row.final .label { color:#1a2520; font-size:14px; font-weight:700; }
.rf-total-row.final .value { color:#16a34a; font-size:16px; }

/* Batch/serial columns — hidden by default, revealed per-row by JS */
.rf-th-batch { display:none; }
.rf-batch-cell { display:none; }

/* Top action bar */
.rf-topbar { display:flex; align-items:center; gap:10px; padding:12px 20px; background:#edf7f1; border-radius:10px; border:1px solid #c8dfd0; box-shadow:0 1px 6px rgba(13,40,24,.06); margin-bottom:18px; }
.rf-doc-num { font-size:15px; font-weight:700; color:#1a2520; }
.rf-doc-num span { color:#16a34a; }

.btn-rf-save { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(22,163,74,.35); text-decoration:none!important; }
.btn-rf-save:hover { opacity:.9; color:#fff; text-decoration:none; }
.btn-rf-confirm { background:linear-gradient(135deg,#0d9488,#0f766e); color:#fff!important; border:none; border-radius:7px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(20,184,166,.35); text-decoration:none!important; }
.btn-rf-confirm:hover { opacity:.9; color:#fff; text-decoration:none; }
.btn-rf-back { background:#f0faf4; color:#166534!important; border:1px solid #bbf7d0; border-radius:7px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none!important; }
.btn-rf-back:hover { background:#dcfce7; color:#14532d; text-decoration:none; }
</style>

<!-- Top bar -->
<div class="rf-topbar">
  <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>" class="btn-rf-back">
    <i class="fa fa-arrow-left"></i> Back
  </a>
  <div class="rf-doc-num">
    <i class="fa fa-truck-loading" style="color:#16a34a;margin-right:6px"></i>
    <?php echo $is_edit ? 'Edit' : 'New'; ?> Inventory Receipt
    <?php if ($is_edit): ?><span>#<?php echo htmlspecialchars($existing['receipt_number'] ?? ''); ?></span><?php endif; ?>
  </div>
  <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
    <?php if ($is_edit): ?>
    <span class="ibadge ibadge-<?php echo $existing['status'] ?? 'draft'; ?>">
      <?php echo ucfirst($existing['status'] ?? 'draft'); ?>
    </span>
    <?php endif; ?>
    <button type="button" class="btn-rf-save" onclick="rfSave('draft')">
      <i class="fa fa-save"></i> Save Draft
    </button>
    <button type="button" class="btn-rf-confirm" onclick="rfSave('confirmed')">
      <i class="fa fa-check-circle"></i> Confirm Receipt
    </button>
  </div>
</div>

<input type="hidden" id="rf-doc-id" value="<?php echo $doc_id; ?>">
<input type="hidden" id="rf-doc-type" value="receipt">

<!-- Header fields: 2 columns -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">

  <!-- Left column -->
  <div class="rf-card">
    <div class="rf-card-hdr">
      <h5 class="rf-card-hdr-title"><i class="fa fa-file-alt"></i> Document Information</h5>
    </div>
    <div class="rf-body">
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Docket Number</label>
          <input type="text" id="rf-docket-number" class="rf-input" placeholder="e.g. DN-2024-001"
            value="<?php echo htmlspecialchars($existing['docket_number'] ?? ''); ?>">
        </div>
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Invoice Number</label>
          <input type="text" id="rf-invoice-number" class="rf-input" placeholder="Supplier invoice #"
            value="<?php echo htmlspecialchars($existing['invoice_number'] ?? ''); ?>">
        </div>
      </div>
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Receiving Date <span style="color:#ef4444">*</span></label>
          <input type="date" id="rf-receipt-date" class="rf-input"
            value="<?php echo $existing['receipt_date'] ?? date('Y-m-d'); ?>">
        </div>
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Accounting Date</label>
          <input type="date" id="rf-accounting-date" class="rf-input"
            value="<?php echo $existing['accounting_date'] ?? date('Y-m-d'); ?>">
        </div>
      </div>
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Type</label>
          <select id="rf-type" class="rf-select">
            <?php foreach (['standard'=>'Standard','emergency'=>'Emergency','return_to_supplier'=>'Return to Supplier'] as $k=>$v): ?>
            <option value="<?php echo $k; ?>" <?php echo ($existing['type'] ?? 'standard')===$k?'selected':''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Warehouse <span style="color:#ef4444">*</span></label>
          <select id="rf-branch" class="rf-select">
            <?php foreach ($branches as $b): ?>
            <option value="<?php echo $b['id']; ?>"
              <?php echo (int)($existing['branch_id'] ?? $branch_id)===(int)$b['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($b['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="rf-fg">
        <label class="rf-label">Link Purchase Order</label>
        <div style="display:flex;gap:8px;align-items:center">
          <select id="rf-po-id" class="rf-select" style="flex:1">
            <option value="">— No PO linked —</option>
            <?php foreach ($purchase_orders as $po): ?>
            <option value="<?php echo $po['id']; ?>" data-supplier="<?php echo (int)$po['supplier_id']; ?>"
              <?php echo (int)($existing['po_id'] ?? 0)===(int)$po['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($po['po_number']); ?>
              <?php if ($po['supplier_name']): ?>(<?php echo htmlspecialchars($po['supplier_name']); ?>)<?php endif; ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="button" id="rf-po-load-btn" class="btn-rf-save" style="padding:6px 10px;font-size:13px"
            onclick="rfLoadPOItems()" title="Reload items from selected PO">
            <i class="fa fa-sync"></i>
          </button>
        </div>
        <span style="font-size:11px;color:#56665e;margin-top:4px;display:block">
          <i class="fa fa-info-circle"></i> Selecting a PO auto-fills the rows below. Click <i class="fa fa-sync"></i> to reload.
        </span>
      </div>
    </div>
  </div>

  <!-- Right column -->
  <div class="rf-card">
    <div class="rf-card-hdr">
      <h5 class="rf-card-hdr-title"><i class="fa fa-users"></i> Parties & Reference</h5>
    </div>
    <div class="rf-body">
      <div class="rf-fg">
        <label class="rf-label">Supplier</label>
        <select id="rf-supplier-id" class="rf-select" onchange="rfSupplierChange(this.value)">
          <option value="">— Select Supplier —</option>
          <?php foreach ($suppliers as $s): ?>
          <option value="<?php echo $s['id']; ?>" data-name="<?php echo htmlspecialchars($s['name']); ?>"
            <?php echo (int)($existing['supplier_id'] ?? 0)===(int)$s['id']?'selected':''; ?>>
            <?php echo htmlspecialchars($s['name']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Buyer</label>
          <input type="text" id="rf-buyer-name" class="rf-input" placeholder="Who purchased this?"
            list="rf-staff-list" value="<?php echo htmlspecialchars($existing['buyer_name'] ?? ''); ?>">
          <datalist id="rf-staff-list">
            <?php foreach ($staff_list as $st): ?>
            <option value="<?php echo htmlspecialchars($st['firstname'].' '.$st['lastname']); ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Department</label>
          <input type="text" id="rf-department" class="rf-input" placeholder="e.g. Procurement"
            list="rf-dept-list" value="<?php echo htmlspecialchars($existing['department'] ?? ''); ?>">
          <datalist id="rf-dept-list">
            <?php foreach ($departments as $d): ?>
            <option value="<?php echo htmlspecialchars($d['name']); ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
      </div>
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Requester</label>
          <input type="text" id="rf-requester" class="rf-input" placeholder="Who requested the items?"
            value="<?php echo htmlspecialchars($existing['requester'] ?? ''); ?>">
        </div>
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Deliverer</label>
          <input type="text" id="rf-deliverer" class="rf-input" placeholder="Delivery person / courier"
            value="<?php echo htmlspecialchars($existing['deliverer'] ?? ''); ?>">
        </div>
      </div>
      <div class="rf-row">
        <div class="rf-col-2 rf-fg">
          <label class="rf-label">Project</label>
          <input type="text" id="rf-project" class="rf-input" placeholder="Project reference"
            value="<?php echo htmlspecialchars($existing['project'] ?? ''); ?>">
        </div>
      </div>
    </div>
  </div>

</div><!-- /header grid -->

<!-- Barcode Scanner -->
<div class="rf-barcode-bar">
  <label><i class="fa fa-barcode" style="font-size:16px;margin-right:4px"></i> Barcode Scanner</label>
  <input type="text" id="rf-barcode-input" class="rf-barcode-input" placeholder="Scan or type barcode, then press Enter…" autocomplete="off">
  <span style="color:#3d6b4f;font-size:12px;white-space:nowrap"><i class="fa fa-info-circle"></i> Scanned items are added to the table below</span>
</div>

<!-- Line Items -->
<div class="rf-card">
  <div class="rf-card-hdr">
    <h5 class="rf-card-hdr-title"><i class="fa fa-list"></i> Line Items</h5>
    <button type="button" class="btn-inv-primary" style="padding:5px 14px;font-size:12px" onclick="rfAddLine()">
      <i class="fa fa-plus"></i> Add Row
    </button>
  </div>
  <div style="overflow-x:auto">
    <table class="rf-tbl">
      <thead>
        <tr>
          <th style="width:24px">#</th>
          <th style="min-width:220px">ITEM</th>
          <th style="min-width:130px">WAREHOUSE</th>
          <th style="width:80px">QTY</th>
          <th style="width:110px">UNIT PRICE</th>
          <th style="width:130px">TAX</th>
          <th class="rf-th-batch" style="width:130px">BATCH / LOT #</th>
          <th class="rf-th-batch" style="width:120px">MFG DATE</th>
          <th class="rf-th-batch" style="width:120px">EXPIRY DATE</th>
          <th style="width:110px;text-align:right">AMOUNT</th>
          <th style="width:34px"></th>
        </tr>
      </thead>
      <tbody id="rf-lines">
        <tr id="rf-no-lines">
          <td colspan="11" style="text-align:center;padding:28px;color:#56665e;font-size:13px">
            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>
            No items added. Click <strong>Add Row</strong> or scan a barcode.
          </td>
        </tr>
      </tbody>
    </table>
    <div class="rf-add-row" onclick="rfAddLine()">
      <i class="fa fa-plus-circle"></i> Add another item
    </div>
  </div>
</div>

<!-- Totals + Notes row -->
<div style="display:grid;grid-template-columns:1fr 360px;gap:18px;align-items:start">

  <!-- Note -->
  <div class="rf-card">
    <div class="rf-card-hdr">
      <h5 class="rf-card-hdr-title"><i class="fa fa-sticky-note"></i> Note</h5>
    </div>
    <div class="rf-body">
      <textarea id="rf-note" class="rf-textarea" placeholder="Internal notes, receiving remarks…"><?php echo htmlspecialchars($existing['note'] ?? ''); ?></textarea>
    </div>
  </div>

  <!-- Totals -->
  <div class="rf-totals">
    <div class="rf-total-row">
      <span class="label">Total Goods Value :</span>
      <span class="value" id="rf-goods-value">Ksh 0.00</span>
    </div>
    <div class="rf-total-row">
      <span class="label">Value of Inventory :</span>
      <span class="value" id="rf-inventory-value">Ksh 0.00</span>
    </div>
    <div class="rf-total-row">
      <span class="label">Total Tax Amount :</span>
      <span class="value" id="rf-tax-total">Ksh 0.00</span>
    </div>
    <div class="rf-total-row final">
      <span class="label">Total Payment :</span>
      <span class="value" id="rf-grand-total">Ksh 0.00</span>
    </div>
  </div>

</div>

<!-- Bottom action bar -->
<div style="display:flex;gap:10px;padding:16px 0;justify-content:flex-end">
  <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>" class="btn-rf-back">
    <i class="fa fa-times"></i> Cancel
  </a>
  <button type="button" class="btn-rf-save" onclick="rfSave('draft')">
    <i class="fa fa-save"></i> Save Draft
  </button>
  <button type="button" class="btn-rf-confirm" onclick="rfSave('confirmed')">
    <i class="fa fa-check-circle"></i> Confirm Receipt
  </button>
</div>

</div><!-- /inv-content -->
</div><!-- /content -->
</div><!-- /wrapper -->
<?php init_tail(); ?>
<script>
var RF_PRODUCTS   = <?php echo json_encode(array_values(array_map(function($p){
    return [
        'id'           => (int)$p['id'],
        'name'         => $p['name'],
        'sku'          => $p['sku']??'',
        'barcode'      => $p['barcode']??'',
        'cost_price'   => (float)($p['cost_price']??0),
        'has_batch_no' => (int)($p['has_batch_no']??0),
        'has_serial_no'=> (int)($p['has_serial_no']??0),
    ];
}, $products))); ?>;
var RF_BRANCHES   = <?php echo json_encode(array_values(array_map(function($b){ return ['id'=>(int)$b['id'],'name'=>$b['name']]; }, $branches))); ?>;
var RF_TAX_RATES  = <?php echo json_encode(array_values(array_map(function($t){ return ['id'=>(int)$t['id'],'name'=>$t['name'],'rate'=>(float)$t['rate']]; }, $tax_rates))); ?>;
var RF_SAVE_URL   = '<?php echo admin_url('pos_system/inv_save/doc'); ?>';
var RF_PO_URL     = '<?php echo admin_url('pos_system/inv_po_items/'); ?>';
var RF_BRANCH_ID  = <?php echo (int)$branch_id; ?>;
var _csrf_n       = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v       = '<?php echo $this->security->get_csrf_hash(); ?>';
var rf_idx        = 0;

// Build lookup maps
var prodByBarcode = {}, prodById = {};
RF_PRODUCTS.forEach(function(p){
    prodById[p.id] = p;
    if (p.barcode) prodByBarcode[p.barcode.toLowerCase()] = p;
    if (p.sku)     prodByBarcode[p.sku.toLowerCase()]     = p;
});

// Build product <option> HTML
var prodOpts = '<option value="">— Select Product —</option>';
RF_PRODUCTS.forEach(function(p){
    prodOpts += '<option value="'+p.id+'">'
             + escHtml(p.name)
             + (p.sku?' ['+p.sku+']':'')
             + '</option>';
});

// Tax rate <option> HTML
var taxOpts = '<option value="0" data-rate="0">No Tax</option>';
RF_TAX_RATES.forEach(function(t){
    taxOpts += '<option value="'+t.id+'" data-rate="'+t.rate+'">'+escHtml(t.name)+'</option>';
});

// Branch <option> HTML
var branchOpts = '<option value="">— Same as header —</option>';
RF_BRANCHES.forEach(function(b){
    branchOpts += '<option value="'+b.id+'"'+(b.id===RF_BRANCH_ID?' selected':'')+'>'+escHtml(b.name)+'</option>';
});

function escHtml(s){ return String(s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]}); }
function fmtMoney(n){ return 'Ksh '+n.toLocaleString('en-KE',{minimumFractionDigits:2,maximumFractionDigits:2}); }

// Batch/serial cell visibility per row + sync header columns
function rfSetBatchCols(idx, hasBatch, hasSerial) {
    var batchCells = $('#rf-line-'+idx+' .rf-batch-cell');
    var serialRow  = $('#rf-serial-row-'+idx);
    if (hasBatch) {
        batchCells.show();
        serialRow.hide();
        $('#rf-line-'+idx+' .rf-lot').attr('placeholder', 'Batch / LOT #');
    } else if (hasSerial) {
        // For serial items: hide the batch# column, show dates, show serial sub-row
        batchCells.hide();
        $('#rf-line-'+idx+' .rf-batch-cell.rf-batch-dates').show();
        serialRow.show();
    } else {
        batchCells.hide();
        serialRow.hide();
    }
    // Sync thead — show batch header cols if any row has batch/serial
    rfSyncBatchHeaders();
}

function rfSyncBatchHeaders() {
    var anyBatch = $('#rf-lines .rf-batch-cell:visible').length > 0;
    if (anyBatch) {
        $('.rf-th-batch').show();
    } else {
        $('.rf-th-batch').hide();
    }
}

function rfAddLine(pid, pname, psku, pcost, pqty) {
    $('#rf-no-lines').remove();
    var idx  = rf_idx++;
    var cost = pcost || 0;
    var qty  = pqty  || 1;
    var prod = (pid && prodById[pid]) ? prodById[pid] : null;
    var hasBatch  = prod ? prod.has_batch_no  : 0;
    var hasSerial = prod ? prod.has_serial_no : 0;

    var tr = '<tr id="rf-line-'+idx+'">';
    tr += '<td style="padding:6px 8px;color:#56665e;font-weight:700;text-align:center" id="rf-ln-'+idx+'">'+idx+'</td>';
    tr += '<td style="padding:4px 5px">'
        + '<select class="rf-tbl-select rf-pid" data-idx="'+idx+'" onchange="rfProductChange('+idx+')" style="min-width:180px">'
        + prodOpts + '</select></td>';
    tr += '<td style="padding:4px 5px"><select class="rf-tbl-select rf-brid" data-idx="'+idx+'" style="min-width:120px">'+branchOpts+'</select></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="rf-tbl-input rf-qty" data-idx="'+idx+'" value="'+qty+'" min="0.001" step="any" onchange="rfRecalc('+idx+')" style="width:72px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><input type="number" class="rf-tbl-input rf-cost" data-idx="'+idx+'" value="'+cost.toFixed(2)+'" min="0" step="any" onchange="rfRecalc('+idx+')" style="width:100px;text-align:right"></td>';
    tr += '<td style="padding:4px 5px"><select class="rf-tbl-select rf-tax" data-idx="'+idx+'" onchange="rfRecalc('+idx+')" style="min-width:110px">'+taxOpts+'</select></td>';
    // Batch / lot cell
    tr += '<td class="rf-batch-cell" style="padding:4px 5px"><input type="text" class="rf-tbl-input rf-lot" data-idx="'+idx+'" placeholder="Batch / LOT #" style="width:118px"></td>';
    // Mfg date
    tr += '<td class="rf-batch-cell rf-batch-dates" style="padding:4px 5px"><input type="date" class="rf-tbl-input rf-mfg" data-idx="'+idx+'" style="width:115px"></td>';
    // Expiry date
    tr += '<td class="rf-batch-cell rf-batch-dates" style="padding:4px 5px"><input type="date" class="rf-tbl-input rf-exp" data-idx="'+idx+'" style="width:115px"></td>';
    tr += '<td style="padding:4px 8px;text-align:right;font-weight:700;color:#1a2520;white-space:nowrap" id="rf-line-total-'+idx+'">0.00</td>';
    tr += '<td style="padding:4px 5px;text-align:center"><button type="button" class="btn-inv-icon danger" onclick="rfRemoveLine('+idx+')"><i class="fa fa-times"></i></button></td>';
    tr += '</tr>';

    // Serial sub-row (hidden by default, shown for serial-tracked items)
    tr += '<tr id="rf-serial-row-'+idx+'" style="display:none;background:#f0fdf4">'
        + '<td></td>'
        + '<td colspan="8" style="padding:6px 10px">'
        + '<div style="display:flex;align-items:flex-start;gap:10px">'
        + '<div style="flex:1">'
        + '<label style="font-size:10px;font-weight:700;color:#166534;text-transform:uppercase;display:block;margin-bottom:4px">'
        + '<i class="fa fa-list-ol"></i> Serial Numbers <span style="font-weight:400;color:#56665e">(one per line — must match qty)</span>'
        + '</label>'
        + '<textarea class="rf-tbl-input rf-serials" data-idx="'+idx+'" rows="3" placeholder="Enter one serial number per line&#10;e.g.&#10;SN-00001&#10;SN-00002" style="width:100%;resize:vertical;height:70px;font-family:monospace;font-size:12px"></textarea>'
        + '</div>'
        + '<div style="flex:0 0 240px">'
        + '<label style="font-size:10px;font-weight:700;color:#166534;text-transform:uppercase;display:block;margin-bottom:4px"><i class="fa fa-calendar-times"></i> Warranty / Expiry</label>'
        + '<div style="display:flex;gap:8px">'
        + '<input type="date" class="rf-tbl-input rf-exp" data-idx="'+idx+'" style="flex:1" placeholder="Expiry">'
        + '</div>'
        + '</div>'
        + '</div>'
        + '<div class="rf-serial-count-msg" id="rf-serial-msg-'+idx+'" style="font-size:11px;margin-top:4px;color:#94a3b8"></div>'
        + '</td>'
        + '<td></td>'
        + '</tr>';

    $('#rf-lines').append(tr);

    if (pid) { $('.rf-pid[data-idx="'+idx+'"]').val(pid); }
    $('.rf-cost[data-idx="'+idx+'"]').val(cost.toFixed(2));
    $('.rf-qty[data-idx="'+idx+'"]').val(qty);

    rfSetBatchCols(idx, hasBatch, hasSerial);
    rfRecalc(idx);
    renumberLines();

    // Live serial count validation
    $(document).off('input.serial-'+idx).on('input.serial-'+idx, '.rf-serials[data-idx="'+idx+'"],.rf-qty[data-idx="'+idx+'"]', function(){
        rfValidateSerials(idx);
    });
}

function rfValidateSerials(idx) {
    var prod = prodById[parseInt($('.rf-pid[data-idx="'+idx+'"]').val())];
    if (!prod || !prod.has_serial_no) return;
    var qty     = parseInt($('.rf-qty[data-idx="'+idx+'"]').val()) || 0;
    var rawText = $('.rf-serials[data-idx="'+idx+'"]').val();
    var serials = rawText.split('\n').map(function(s){ return s.trim(); }).filter(function(s){ return s.length > 0; });
    var msg = $('#rf-serial-msg-'+idx);
    if (serials.length === 0) {
        msg.text('').css('color','#94a3b8');
    } else if (serials.length < qty) {
        msg.html('<i class="fa fa-exclamation-triangle" style="color:#f59e0b"></i> '+serials.length+' serial(s) entered, '+qty+' required.').css('color','#f59e0b');
    } else if (serials.length > qty) {
        msg.html('<i class="fa fa-exclamation-triangle" style="color:#ef4444"></i> '+serials.length+' serial(s) entered but qty is '+qty+'.').css('color','#ef4444');
    } else {
        msg.html('<i class="fa fa-check-circle" style="color:#16a34a"></i> '+serials.length+' serial number(s) — matches qty.').css('color','#16a34a');
    }
}

function rfProductChange(idx) {
    var pid = parseInt($('.rf-pid[data-idx="'+idx+'"]').val());
    var hasBatch = 0, hasSerial = 0;
    if (pid && prodById[pid]) {
        var p = prodById[pid];
        $('.rf-cost[data-idx="'+idx+'"]').val((p.cost_price||0).toFixed(2));
        hasBatch  = p.has_batch_no  || 0;
        hasSerial = p.has_serial_no || 0;
        rfRecalc(idx);
    }
    rfSetBatchCols(idx, hasBatch, hasSerial);
}

function rfRecalc(idx) {
    var qty  = parseFloat($('.rf-qty[data-idx="'+idx+'"]').val())  || 0;
    var cost = parseFloat($('.rf-cost[data-idx="'+idx+'"]').val()) || 0;
    var rate = parseFloat($('.rf-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
    var goods  = qty * cost;
    var taxAmt = goods * rate;
    var total  = goods + taxAmt;
    $('#rf-line-total-'+idx).text(total.toFixed(2));
    rfUpdateTotals();
}

function rfUpdateTotals() {
    var goods = 0, tax = 0, total = 0;
    $('[id^="rf-line-total-"]').each(function(){
        var t = parseFloat($(this).text()) || 0;
        total += t;
    });
    // Recalculate goods and tax by iterating all rows
    $('[id^="rf-line-"]:not(#rf-no-lines)').each(function(){
        var idx = this.id.replace('rf-line-','');
        if (isNaN(parseInt(idx))) return;
        var qty  = parseFloat($('.rf-qty[data-idx="'+idx+'"]').val())  || 0;
        var cost = parseFloat($('.rf-cost[data-idx="'+idx+'"]').val()) || 0;
        var rate = parseFloat($('.rf-tax[data-idx="'+idx+'"] option:selected').data('rate')) || 0;
        var g = qty * cost;
        goods += g;
        tax   += g * rate;
    });
    total = goods + tax;
    $('#rf-goods-value').text(fmtMoney(goods));
    $('#rf-inventory-value').text(fmtMoney(goods));
    $('#rf-tax-total').text(fmtMoney(tax));
    $('#rf-grand-total').text(fmtMoney(total));
}

function rfRemoveLine(idx) {
    $('#rf-line-'+idx).remove();
    $('#rf-serial-row-'+idx).remove();
    rfUpdateTotals();
    rfSyncBatchHeaders();
    if (!$('#rf-lines tr:not(#rf-no-lines)').length) {
        $('#rf-lines').append('<tr id="rf-no-lines"><td colspan="11" style="text-align:center;padding:28px;color:#56665e;font-size:13px"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;color:#a7d6b4"></i>No items added. Click <strong>Add Row</strong> or scan a barcode.</td></tr>');
    }
    renumberLines();
}

function renumberLines() {
    var n = 1;
    $('[id^="rf-ln-"]').each(function(){ $(this).text(n++); });
}

// Barcode scanner
$('#rf-barcode-input').on('keypress', function(e){
    if (e.which !== 13) return;
    var code = $(this).val().trim().toLowerCase();
    if (!code) return;
    var prod = prodByBarcode[code];
    if (prod) {
        rfAddLine(prod.id, prod.name, prod.sku, prod.cost_price);
        $(this).val('').focus();
    } else {
        $(this).css('border-color','#ef4444');
        setTimeout(function(){ $('#rf-barcode-input').css('border-color',''); }, 1000);
    }
});

// When a PO is selected: auto-fill supplier then immediately load items
$('#rf-po-id').on('change', function() {
    var sid = $(this).find(':selected').data('supplier');
    if (sid) $('#rf-supplier-id').val(sid);
    rfLoadPOItems();
});

// Load PO line items into the receipt rows
function rfLoadPOItems() {
    var poId = $('#rf-po-id').val();
    if (!poId) return;

    var $btn = $('#rf-po-load-btn');
    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    $('#rf-po-id').prop('disabled', true);

    $.getJSON(RF_PO_URL + poId, function(resp) {
        if (!resp || !resp.items || !resp.items.length) {
            alert_float('warning', 'No line items found on this Purchase Order.');
            return;
        }

        // Clear existing rows
        $('[id^="rf-line-"]:not(#rf-no-lines)').remove();
        $('#rf-no-lines').remove();
        rf_idx = 0;

        $.each(resp.items, function(i, item) {
            rfAddLine(
                item.pos_product_id || 0,
                item.product_name,
                item.sku,
                item.unit_price,
                item.quantity
            );
        });

        alert_float('success', resp.items.length + ' item(s) loaded from PO.');
    }).fail(function() {
        alert_float('danger', 'Failed to load PO items.');
    }).always(function() {
        $('#rf-po-id').prop('disabled', false);
        $btn.html('<i class="fa fa-sync"></i>').prop('disabled', false);
    });
}

function rfSupplierChange(sid){}

function rfSave(status) {
    var lines = [];
    var ok = true;
    $('[id^="rf-line-"]:not(#rf-no-lines)').each(function(){
        var idx = this.id.replace('rf-line-','');
        if (isNaN(parseInt(idx))) return;
        var pid = parseInt($('.rf-pid[data-idx="'+idx+'"]').val());
        if (!pid) { ok = false; alert('Select a product for every row.'); return false; }
        var qty  = parseFloat($('.rf-qty[data-idx="'+idx+'"]').val())  || 0;
        var cost = parseFloat($('.rf-cost[data-idx="'+idx+'"]').val()) || 0;
        var taxSel = $('.rf-tax[data-idx="'+idx+'"] option:selected');
        var taxRate = parseFloat(taxSel.data('rate')) || 0;
        var taxId   = parseInt(taxSel.val()) || 0;
        var prod = prodById[pid] || {};
        var serialsRaw = $('.rf-serials[data-idx="'+idx+'"]').val() || '';
        var serials = serialsRaw.split('\n').map(function(s){ return s.trim(); }).filter(function(s){ return s.length>0; });
        // Serial count validation
        if (prod.has_serial_no && serials.length > 0 && serials.length !== qty) {
            ok = false;
            alert('Row ' + (idx+1) + ': Serial number count (' + serials.length + ') does not match quantity (' + qty + ').');
            return false;
        }
        lines.push({
            product_id:       pid,
            branch_id:        parseInt($('.rf-brid[data-idx="'+idx+'"]').val()) || 0,
            quantity:         qty,
            unit_cost:        cost,
            tax_rate_id:      taxId,
            tax_rate_pct:     taxRate,
            tax_amount:       (qty * cost * taxRate).toFixed(4),
            lot_number:       $('.rf-lot[data-idx="'+idx+'"]').val(),
            manufacture_date: $('.rf-mfg[data-idx="'+idx+'"]').val(),
            expiry_date:      $('.rf-exp[data-idx="'+idx+'"]').val(),
            serial_numbers:   serials,
            has_batch_no:     prod.has_batch_no  || 0,
            has_serial_no:    prod.has_serial_no || 0,
        });
    });
    if (!ok) return;
    if (!lines.length) { alert('Add at least one item.'); return; }

    var d = {};
    d[_csrf_n]         = _csrf_v;
    d.doc_type         = 'receipt';
    d.doc_id           = $('#rf-doc-id').val();
    d.branch_id        = $('#rf-branch').val();
    d.date             = $('#rf-receipt-date').val();
    d.accounting_date  = $('#rf-accounting-date').val();
    d.docket_number    = $('#rf-docket-number').val();
    d.invoice_number   = $('#rf-invoice-number').val();
    d.type             = $('#rf-type').val();
    d.po_id            = $('#rf-po-id').val();
    d.supplier_id      = $('#rf-supplier-id').val();
    d.buyer_name       = $('#rf-buyer-name').val();
    d.department       = $('#rf-department').val();
    d.requester        = $('#rf-requester').val();
    d.deliverer        = $('#rf-deliverer').val();
    d.project          = $('#rf-project').val();
    d.note             = $('#rf-note').val();
    d.status           = status;
    d.lines            = JSON.stringify(lines);

    var $btns = $('button[onclick^="rfSave"]');
    $btns.prop('disabled', true);
    $.post(RF_SAVE_URL, d, function(r){
        $btns.prop('disabled', false);
        _csrf_v = r.csrf_hash || _csrf_v;
        if (r.success) {
            alert_float('success', r.message || 'Receipt saved.');
            if (r.redirect) setTimeout(function(){ window.location = r.redirect; }, 700);
        } else {
            alert_float('danger', r.error || 'Save failed.');
        }
    }, 'json').fail(function(){ $btns.prop('disabled',false); alert_float('danger','Network error.'); });
}
</script>
