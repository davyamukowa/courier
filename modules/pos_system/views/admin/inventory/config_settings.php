<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'config_settings',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content">

<style>
.cfg-wrap    { display:flex; gap:0; min-height:600px; }
.cfg-sidebar { width:220px; flex-shrink:0; background:#fafbfd; border-right:1px solid #e8edf5; border-radius:10px 0 0 10px; padding:8px 0; }
.cfg-sidebar a { display:flex; align-items:center; gap:9px; padding:9px 16px; font-size:13px; color:#4b5e78; font-weight:500; text-decoration:none; border-left:3px solid transparent; transition:all .15s; }
.cfg-sidebar a:hover { background:#f0faf4; color:#0d2818; }
.cfg-sidebar a.active { background:#dcfce7; color:#14532d; font-weight:700; border-left-color:#16a34a; }
.cfg-sidebar a i { width:16px; text-align:center; font-size:12px; color:#6b9e7a; }
.cfg-sidebar a.active i { color:#16a34a; }
.cfg-sep { border-top:1px solid #e8edf5; margin:6px 0; }
.cfg-section-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; padding:8px 16px 4px; }
.cfg-body { flex:1; padding:24px; min-width:0; }
.cfg-tab { display:none; }
.cfg-tab.active { display:block; }
.cfg-section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; margin:20px 0 10px; padding-bottom:6px; border-bottom:1px solid #f0f4f9; }
.cfg-section-title:first-child { margin-top:0; }
.cfg-row { display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start; padding:10px 0; border-bottom:1px solid #f8fafc; }
.cfg-row:last-child { border-bottom:none; }
.cfg-lbl { font-size:13px; font-weight:600; color:#334155; }
.cfg-hint { font-size:11px; color:#94a3b8; margin-top:3px; line-height:1.4; }
.cfg-ctrl { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.cfg-input { height:34px; border:1px solid #dde4ef; border-radius:7px; padding:0 12px; font-size:13px; color:#334155; background:#fafbfd; outline:none; transition:border .15s,box-shadow .15s; }
.cfg-input:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.14); background:#fff; }
.cfg-textarea { border:1px solid #dde4ef; border-radius:7px; padding:8px 12px; font-size:13px; color:#334155; background:#fafbfd; outline:none; width:100%; max-width:500px; transition:border .15s; resize:vertical; }
.cfg-textarea:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.14); background:#fff; }
.cfg-toggle { position:relative; width:42px; height:22px; flex-shrink:0; }
.cfg-toggle input { opacity:0; width:0; height:0; }
.cfg-toggle-sl { position:absolute; inset:0; background:#dde4ef; border-radius:22px; cursor:pointer; transition:.2s; }
.cfg-toggle-sl:before { content:''; position:absolute; width:16px; height:16px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.cfg-toggle input:checked+.cfg-toggle-sl { background:#16a34a; }
.cfg-toggle input:checked+.cfg-toggle-sl:before { transform:translateX(20px); }
.cfg-radio-group { display:flex; flex-direction:column; gap:8px; }
.cfg-radio-option { display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; cursor:pointer; }
.cfg-radio-option input { accent-color:#16a34a; }
.cfg-save-bar { position:sticky; bottom:0; background:#fff; border-top:1px solid #e2ece6; padding:14px 24px; margin:-24px; margin-top:20px; display:flex; align-items:center; gap:12px; z-index:10; box-shadow:0 -4px 16px rgba(13,40,24,.07); }
.mm-tbl { width:100%; border-collapse:separate; border-spacing:0; }
.mm-tbl thead th { padding:9px 12px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#8a9bb0; background:#f5f7fb; border-bottom:1px solid #e8edf5; }
.mm-tbl tbody td { padding:9px 12px; font-size:13px; border-bottom:1px solid #f0f4f9; vertical-align:middle; }
.mm-tbl tbody tr:hover { background:#f8faff; }
.reset-card { background:#fff8f8; border:1px solid #fca5a533; border-radius:10px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:16px; }
</style>

<div class="inv-card" style="border-radius:10px">
<div class="inv-card-header">
  <h4 class="inv-card-title"><i class="fa fa-sliders-h"></i> Inventory Settings</h4>
</div>

<div class="cfg-wrap">
  <!-- Sidebar -->
  <nav class="cfg-sidebar">
    <div class="cfg-section-label">Product Settings</div>
    <a href="#" data-tab="general" class="active"><i class="fa fa-box-open"></i> General / Items</a>
    <a href="#" data-tab="numbering"><i class="fa fa-hashtag"></i> Document Numbers</a>
    <div class="cfg-sep"></div>
    <div class="cfg-section-label">Operations</div>
    <a href="#" data-tab="receiving"><i class="fa fa-truck-loading"></i> Receiving &amp; Delivery</a>
    <a href="#" data-tab="returns"><i class="fa fa-undo-alt"></i> Return Orders</a>
    <a href="#" data-tab="packing"><i class="fa fa-box"></i> Packing Lists</a>
    <a href="#" data-tab="shipments"><i class="fa fa-shipping-fast"></i> Shipments</a>
    <div class="cfg-sep"></div>
    <div class="cfg-section-label">Output</div>
    <a href="#" data-tab="pdf"><i class="fa fa-file-pdf"></i> PDF / Print</a>
    <a href="#" data-tab="serial"><i class="fa fa-barcode"></i> Serial Numbers</a>
    <div class="cfg-sep"></div>
    <div class="cfg-section-label">Levels &amp; Alerts</div>
    <a href="#" data-tab="minmax"><i class="fa fa-chart-line"></i> Min / Max Inventory</a>
    <a href="#" data-tab="notifications"><i class="fa fa-bell"></i> Notifications</a>
    <div class="cfg-sep"></div>
    <div class="cfg-section-label">Access</div>
    <a href="#" data-tab="approval"><i class="fa fa-check-double"></i> Approval Settings</a>
    <a href="#" data-tab="permissions"><i class="fa fa-shield-alt"></i> Permissions</a>
    <div class="cfg-sep"></div>
    <a href="#" data-tab="reset" style="color:#ef4444"><i class="fa fa-trash-alt" style="color:#ef4444"></i> Reset Data</a>
  </nav>

  <!-- Body -->
  <div class="cfg-body">
    <form id="cfg-form">

    <!-- ══ GENERAL / ITEMS ════════════════════════════════ -->
    <div class="cfg-tab active" id="tab-general">
      <div class="cfg-section-title">Profit &amp; Pricing</div>

      <div class="cfg-row">
        <div><div class="cfg-lbl">Default Profit Rate (%)</div><div class="cfg-hint">Applied when creating new products if no specific rate is set.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_default_profit_rate" class="cfg-input" value="<?php echo pos_get_setting('inv_default_profit_rate') ?: 20; ?>" style="width:100px" min="0" max="1000" step="0.01"><span style="font-size:12px;color:#94a3b8">%</span></div>
      </div>

      <div class="cfg-row">
        <div><div class="cfg-lbl">Sale Price Calculation</div><div class="cfg-hint">How to auto-calculate the selling price when editing a product.</div></div>
        <div class="cfg-ctrl">
          <div class="cfg-radio-group">
            <label class="cfg-radio-option"><input type="radio" name="inv_price_calc_method" value="cost_profit" <?php echo (pos_get_setting('inv_price_calc_method') ?: 'cost_profit') === 'cost_profit' ? 'checked' : ''; ?>> Calculate selling price using cost price &times; (1 + profit rate)</label>
            <label class="cfg-radio-option"><input type="radio" name="inv_price_calc_method" value="selling_profit" <?php echo pos_get_setting('inv_price_calc_method') === 'selling_profit' ? 'checked' : ''; ?>> Calculate profit rate from selling price &divide; cost price</label>
          </div>
        </div>
      </div>

      <div class="cfg-row">
        <div><div class="cfg-lbl">Decimal Places (Fractional portion)</div><div class="cfg-hint">Number of digits after the decimal point in price/quantity display.</div></div>
        <div class="cfg-ctrl">
          <select name="inv_decimal_places" class="cfg-input" style="width:90px">
            <?php for ($i=0;$i<=6;$i++): ?><option value="<?php echo $i; ?>" <?php echo (int)(pos_get_setting('inv_decimal_places') ?: 2) === $i ? 'selected' : ''; ?>><?php echo $i; ?></option><?php endfor; ?>
          </select><span style="font-size:12px;color:#94a3b8">digits after decimal</span>
        </div>
      </div>

      <div class="cfg-row">
        <div><div class="cfg-lbl">Integer Rounding (Integer portion)</div><div class="cfg-hint">Optionally round the integer part of prices (0 = no rounding).</div></div>
        <div class="cfg-ctrl">
          <select name="inv_integer_rounding" class="cfg-input" style="width:130px">
            <option value="0" <?php echo (pos_get_setting('inv_integer_rounding') ?: '0') === '0' ? 'selected' : ''; ?>>No rounding</option>
            <option value="1" <?php echo pos_get_setting('inv_integer_rounding') === '1' ? 'selected' : ''; ?>>Round to 1</option>
            <option value="5" <?php echo pos_get_setting('inv_integer_rounding') === '5' ? 'selected' : ''; ?>>Round to 5</option>
            <option value="10" <?php echo pos_get_setting('inv_integer_rounding') === '10' ? 'selected' : ''; ?>>Round to 10</option>
          </select>
        </div>
      </div>

      <div class="cfg-section-title">Barcodes</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Use SKU as Barcode Value</div><div class="cfg-hint">The product's SKU code is used as the barcode value for printing.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_barcode_use_sku" value="1" <?php echo pos_get_setting('inv_barcode_use_sku') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Short Description Below Barcode</div><div class="cfg-hint">Print a short product description below the barcode image when printing labels.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_barcode_show_desc" value="1" <?php echo pos_get_setting('inv_barcode_show_desc') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Price when Print Barcode</div><div class="cfg-hint">Print the selling price below the barcode on barcode labels.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_barcode_show_price" value="1" <?php echo pos_get_setting('inv_barcode_show_price') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Product Search</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Show Search if More Than X Products</div><div class="cfg-hint">If the product list exceeds this count, a search box will be displayed on the product selection box.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_search_threshold" class="cfg-input" value="<?php echo pos_get_setting('inv_search_threshold') ?: 20; ?>" style="width:100px" min="5"><span style="font-size:12px;color:#94a3b8">products</span></div>
      </div>

      <div class="cfg-section-title">Inventory Number Updates</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Do Not Update Inventory Numbers</div><div class="cfg-hint">When enabled, stock quantities will NOT be updated when receipts/deliveries are confirmed. Use for dry-run or reporting-only mode.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_no_qty_update" value="1" <?php echo pos_get_setting('inv_no_qty_update') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Update "Do Not Update" to Unchecked</div><div class="cfg-hint">Bulk-set all products' "Do not update inventory numbers" flag to unchecked (re-enable updates for all products).</div></div>
        <div class="cfg-ctrl"><button type="button" class="btn-inv-secondary" onclick="cfgBulkAction('reset_no_update')"><i class="fa fa-sync-alt"></i> Apply to All Products</button></div>
      </div>

      <div class="cfg-section-title">Stock Control</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Track Inventory by Default</div><div class="cfg-hint">New products will have inventory tracking enabled automatically.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_track_by_default" value="1" <?php echo pos_get_setting('inv_track_by_default') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Allow Negative Stock</div><div class="cfg-hint">Allow sales when on-hand quantity is zero. Not recommended for physical goods.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_allow_negative" value="1" <?php echo pos_get_setting('inv_allow_negative') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Hide Items When Out of Stock</div><div class="cfg-hint">Out-of-stock items will not appear in the POS terminal product list.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_hide_out_of_stock" value="1" <?php echo pos_get_setting('inv_hide_out_of_stock') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Global Low Stock Threshold</div><div class="cfg-hint">Alert when quantity falls to or below this value (used if no per-product reorder point is set).</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_low_stock_threshold" class="cfg-input" value="<?php echo pos_get_setting('inv_low_stock_threshold') ?: 5; ?>" style="width:100px" min="0"><span style="font-size:12px;color:#94a3b8">units</span></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Default Reorder Quantity</div><div class="cfg-hint">Suggested quantity when creating restock orders from low-stock alerts.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_default_reorder_qty" class="cfg-input" value="<?php echo pos_get_setting('inv_default_reorder_qty') ?: 10; ?>" style="width:100px" min="1"></div>
      </div>
    </div><!-- /tab-general -->

    <!-- ══ DOCUMENT NUMBERS ═══════════════════════════════ -->
    <div class="cfg-tab" id="tab-numbering">
      <div class="cfg-section-title">Document Prefixes &amp; Sequences</div>
      <p style="font-size:12px;color:#94a3b8;margin-bottom:16px">Numbers format: <code>PREFIX-00001</code>. Change "Next #" to skip or reset a sequence.</p>
      <?php
      $num_cfg = [
          ['Receipt (Stock In)',    'inv_receipt_prefix',    'inv_next_num_receipt',    'RCV'],
          ['Delivery (Stock Out)',  'inv_delivery_prefix',   'inv_next_num_delivery',   'DLV'],
          ['Internal Transfer',     'inv_transfer_prefix',   'inv_next_num_transfer',   'TRF'],
          ['Loss &amp; Adjustment', 'inv_adjustment_prefix', 'inv_next_num_adjustment', 'ADJ'],
          ['Return Order',          'inv_return_prefix',     'inv_next_num_return',     'RTN'],
          ['Packing List',          'inv_packing_prefix',    'inv_next_num_packing',    'PKL'],
          ['Stock Take / Count',    'inv_stocktake_prefix',  'inv_next_num_stocktake',  'STK'],
      ];
      foreach ($num_cfg as [$lbl,$pk,$nk,$def]):
          $pfx = pos_get_setting($pk) ?: $def;
          $num = (int)(pos_get_setting($nk) ?: 1);
      ?>
      <div class="cfg-row">
        <div><div class="cfg-lbl"><?php echo $lbl; ?></div><div class="cfg-hint">Prefix — Next number</div></div>
        <div class="cfg-ctrl">
          <input type="text" name="<?php echo $pk; ?>" class="cfg-input" value="<?php echo htmlspecialchars($pfx); ?>" style="width:90px">
          <span style="color:#94a3b8">—</span>
          <input type="number" name="<?php echo $nk; ?>" class="cfg-input" value="<?php echo $num; ?>" style="width:100px" min="1">
          <span style="font-size:12px;color:#94a3b8">Preview: <strong id="prev-<?php echo $pk; ?>"><?php echo $pfx.'-'.str_pad($num,5,'0',STR_PAD_LEFT); ?></strong></span>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="cfg-section-title">SKU &amp; Lot Settings</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Item SKU Prefix</div><div class="cfg-hint">Auto-generated SKU codes will use this prefix.</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_sku_prefix" class="cfg-input" value="<?php echo pos_get_setting('inv_sku_prefix') ?: 'SKU'; ?>" style="width:110px"></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto-generate Batch/Lot Numbers</div><div class="cfg-hint">Auto-create a lot number when saving a receipt with no lot specified.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_lot_number" value="1" <?php echo pos_get_setting('inv_auto_lot_number') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Lot Number Prefix</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_lot_prefix" class="cfg-input" value="<?php echo pos_get_setting('inv_lot_prefix') ?: 'LOT'; ?>" style="width:110px"></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Expiry Warning (days before)</div><div class="cfg-hint">Highlight batches expiring within this many days.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_expiry_warn_days" class="cfg-input" value="<?php echo pos_get_setting('inv_expiry_warn_days') ?: 30; ?>" style="width:100px" min="1"><span style="font-size:12px;color:#94a3b8">days</span></div>
      </div>
    </div><!-- /tab-numbering -->

    <!-- ══ RECEIVING & DELIVERY ═══════════════════════════ -->
    <div class="cfg-tab" id="tab-receiving">
      <div class="cfg-section-title">General</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Allow Delete of Receipt/Delivery After Approval</div><div class="cfg-hint">Allow deleting an Inventory Receipt or Delivery voucher after it has been approved/confirmed.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_allow_delete_confirmed" value="1" <?php echo pos_get_setting('inv_allow_delete_confirmed') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Product Image on Voucher</div><div class="cfg-hint">Show product thumbnail images on Inventory Receipt and Delivery vouchers.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_show_product_image" value="1" <?php echo pos_get_setting('inv_show_product_image') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Do Not Convert to Base Currency</div><div class="cfg-hint">Keep amounts in original currency without converting to base system currency.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_no_currency_convert" value="1" <?php echo pos_get_setting('inv_no_currency_convert') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Export Method</div><div class="cfg-hint">Default file format when exporting inventory data.</div></div>
        <div class="cfg-ctrl">
          <select name="inv_export_method" class="cfg-input" style="width:160px">
            <option value="csv" <?php echo (pos_get_setting('inv_export_method') ?: 'csv') === 'csv' ? 'selected' : ''; ?>>CSV</option>
            <option value="xlsx" <?php echo pos_get_setting('inv_export_method') === 'xlsx' ? 'selected' : ''; ?>>Excel (.xlsx)</option>
            <option value="pdf" <?php echo pos_get_setting('inv_export_method') === 'pdf' ? 'selected' : ''; ?>>PDF</option>
          </select>
        </div>
      </div>

      <div class="cfg-section-title">Inventory Receiving Voucher</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Create Receipt when Purchase Order Approved</div><div class="cfg-hint">Automatically create an Inventory Receipt when a linked Purchase Order is approved.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_receipt_on_po" value="1" <?php echo pos_get_setting('inv_auto_receipt_on_po') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Select Warehouse to Auto-Receive from PO</div><div class="cfg-hint">Warehouse/branch to use when auto-generating receipts from Purchase Orders.</div></div>
        <div class="cfg-ctrl">
          <select name="inv_auto_receipt_branch_id" class="cfg-input" style="width:200px">
            <option value="">— None —</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?php echo $b['id']; ?>" <?php echo pos_get_setting('inv_auto_receipt_branch_id') == $b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Mandatory PO Selection when Entering Receipt</div><div class="cfg-hint">Require a linked Purchase Order when entering an Inventory Receipt.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_po_mandatory_receipt" value="1" <?php echo pos_get_setting('inv_po_mandatory_receipt') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Inventory Delivery Voucher</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Create Delivery When Invoice is Created</div><div class="cfg-hint">Automatically create an Inventory Delivery note when a Perfex Invoice is created.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_delivery_on_invoice" value="1" <?php echo pos_get_setting('inv_auto_delivery_on_invoice') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Delete Delivery When Invoice is Cancelled</div><div class="cfg-hint">When an Invoice is cancelled, automatically delete the corresponding Inventory Delivery generated from it.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_delete_delivery_on_cancel" value="1" <?php echo pos_get_setting('inv_delete_delivery_on_cancel') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto-generate Delivery on Invoice Cancel</div><div class="cfg-hint">When unchecked, the invoice cancellation and the Inventory Delivery will be automatically generated for the reversal.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_gen_on_cancel" value="1" <?php echo pos_get_setting('inv_auto_gen_on_cancel') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Mandatory Purchase Order when Entering Delivery</div><div class="cfg-hint">Require a linked Purchase Order when entering an Inventory Delivery.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_po_mandatory_delivery" value="1" <?php echo pos_get_setting('inv_po_mandatory_delivery') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Notify Customers when Delivery Status Changes</div><div class="cfg-hint">Send an email to the customer when a delivery status changes.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_notify_customer_delivery" value="1" <?php echo pos_get_setting('inv_notify_customer_delivery') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Hide Shipping Fee</div><div class="cfg-hint">Do not display the shipping fee line on Inventory Delivery documents.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_hide_shipping_fee" value="1" <?php echo pos_get_setting('inv_hide_shipping_fee') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto Deduct Stock on POS Sale</div><div class="cfg-hint">Automatically deduct inventory when a POS sale is completed.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_deduct_on_sale" value="1" <?php echo pos_get_setting('inv_auto_deduct_on_sale') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto Restore Stock on POS Refund</div><div class="cfg-hint">Automatically add stock back when a POS sale is refunded.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_auto_restore_on_refund" value="1" <?php echo pos_get_setting('inv_auto_restore_on_refund') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
    </div><!-- /tab-receiving -->

    <!-- ══ RETURN ORDERS ══════════════════════════════════ -->
    <div class="cfg-tab" id="tab-returns">
      <div class="cfg-section-title">The Warehouse Receives Return Order</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto-Receive Returned Goods to Warehouse</div><div class="cfg-hint">When a return is confirmed, automatically add goods back to warehouse stock.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_return_restores_stock" value="1" <?php echo pos_get_setting('inv_return_restores_stock') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Return Window (Days)</div><div class="cfg-hint">Maximum days after delivery within which a return can be placed. 0 = no limit.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_return_window_days" class="cfg-input" value="<?php echo pos_get_setting('inv_return_window_days') ?: 30; ?>" style="width:100px" min="0"><span style="font-size:12px;color:#94a3b8">days after delivery</span></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Return Handling Fee (%)</div><div class="cfg-hint">Percentage fee deducted from refund amount (0 = no fee).</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_return_fee_pct" class="cfg-input" value="<?php echo pos_get_setting('inv_return_fee_pct') ?: 0; ?>" style="width:100px" min="0" max="100" step="0.5"><span style="font-size:12px;color:#94a3b8">%</span></div>
      </div>
      <div class="cfg-section-title">Return Policies Information</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Return Policy Text</div><div class="cfg-hint">Displayed to customers on the client portal and printed on return documents.</div></div>
        <div class="cfg-ctrl"><textarea name="inv_return_policy" class="cfg-textarea" rows="6"><?php echo htmlspecialchars(pos_get_setting('inv_return_policy') ?: ''); ?></textarea></div>
      </div>
    </div><!-- /tab-returns -->

    <!-- ══ PACKING LISTS ══════════════════════════════════ -->
    <div class="cfg-tab" id="tab-packing">
      <div class="cfg-section-title">Custom Measurement Name</div>
      <p style="font-size:12px;color:#94a3b8;margin-bottom:12px">Override default unit labels when printing packing lists.</p>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Custom name for metre (m)</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_packing_unit_m" class="cfg-input" value="<?php echo htmlspecialchars(pos_get_setting('inv_packing_unit_m') ?: 'm'); ?>" style="width:150px" placeholder="m"></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Custom name for kilogram (kg)</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_packing_unit_kg" class="cfg-input" value="<?php echo htmlspecialchars(pos_get_setting('inv_packing_unit_kg') ?: 'kg'); ?>" style="width:150px" placeholder="kg"></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Custom name for cubic metre (m&sup3;)</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_packing_unit_m3" class="cfg-input" value="<?php echo htmlspecialchars(pos_get_setting('inv_packing_unit_m3') ?: 'm³'); ?>" style="width:150px" placeholder="m³"></div>
      </div>
      <div class="cfg-section-title">Expiry Date</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Expiry Date on Packing List</div><div class="cfg-hint">Show expiry date column on printed packing list documents.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_packing_show_expiry" value="1" <?php echo pos_get_setting('inv_packing_show_expiry') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
    </div><!-- /tab-packing -->

    <!-- ══ PDF / PRINT ════════════════════════════════════ -->
    <div class="cfg-tab" id="tab-pdf">
      <div class="cfg-section-title">General</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display "Warehouse name", "Lot number", "Sign information" in PDF of Inventory Delivery; Display "Sign information" in PDF of Inventory Receipt</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_show_wh_lot_sign" value="1" <?php echo pos_get_setting('inv_pdf_show_wh_lot_sign') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Show item custom fields on PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_show_custom_fields" value="1" <?php echo pos_get_setting('inv_pdf_show_custom_fields') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Inventory Delivery Voucher</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Unit price, Subtotal, Total payment in the PDF of Inventory Delivery</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_delivery_show_price" value="1" <?php echo pos_get_setting('inv_pdf_delivery_show_price') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display "Outstanding" in the PDF of Inventory Delivery</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_delivery_show_outstanding" value="1" <?php echo pos_get_setting('inv_pdf_delivery_show_outstanding') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Shortened form PDF</div><div class="cfg-hint">Use a condensed/abbreviated layout for the Inventory Delivery PDF.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_delivery_short_form" value="1" <?php echo pos_get_setting('inv_pdf_delivery_short_form') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Packing Lists</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display rate in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_price" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_price') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display tax in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_tax" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_tax') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display subtotal in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_subtotal" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_subtotal') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display discount percent in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_disc_pct" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_disc_pct') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display discount amount in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_disc_amt" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_disc_amt') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display total payment in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_total" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_total') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display summary in the PDF</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_summary" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_summary') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Expiry Date</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_show_expiry" value="1" <?php echo pos_get_setting('inv_pdf_packing_show_expiry') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display only Item Name</div><div class="cfg-hint">Simplified packing list showing only item names and quantities, no price columns.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_pdf_packing_item_name_only" value="1" <?php echo pos_get_setting('inv_pdf_packing_item_name_only') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
    </div><!-- /tab-pdf -->

    <!-- ══ SHIPMENTS ══════════════════════════════════════ -->
    <div class="cfg-tab" id="tab-shipments">
      <div class="cfg-section-title">General</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Display Shipment information on the Client Portal</div><div class="cfg-hint">Show shipment tracking information to customers on the Perfex client portal.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_shipment_show_portal" value="1" <?php echo pos_get_setting('inv_shipment_show_portal') != '0' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Default Carrier</div><div class="cfg-hint">Pre-fill this carrier on new shipment records.</div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_default_carrier" class="cfg-input" value="<?php echo htmlspecialchars(pos_get_setting('inv_default_carrier') ?: ''); ?>" style="width:220px" placeholder="e.g. DHL, G4S, Speedaf"></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Tracking URL Template</div><div class="cfg-hint">Use <code>{tracking_number}</code> as placeholder. e.g. <code>https://track.dhl.com/{tracking_number}</code></div></div>
        <div class="cfg-ctrl"><input type="text" name="inv_tracking_url_tpl" class="cfg-input" value="<?php echo htmlspecialchars(pos_get_setting('inv_tracking_url_tpl') ?: ''); ?>" style="width:380px" placeholder="https://tracking.example.com/{tracking_number}"></div>
      </div>
    </div><!-- /tab-shipments -->

    <!-- ══ SERIAL NUMBERS ════════════════════════════════ -->
    <div class="cfg-tab" id="tab-serial">
      <?php
        $CI2 =& get_instance();
        $CI2->db->from(db_prefix().'pos_serial_batch_settings');
        $sbRows = $CI2->db->get()->result_array();
        $sb = [];
        foreach ($sbRows as $r) $sb[$r['setting_key']] = $r['setting_value'];
        $sbv = function($k, $default='0') use ($sb) { return isset($sb[$k]) ? $sb[$k] : $default; };
      ?>
      <div class="cfg-section-title">Master Switch</div>
      <div class="cfg-row" style="background:#f0faf4;border-radius:8px;padding:12px 14px;border:1px solid #bbf7d0;margin-bottom:6px">
        <div>
          <div class="cfg-lbl" style="color:#14532d;font-size:14px"><i class="fa fa-toggle-on" style="color:#16a34a;margin-right:6px"></i> Enable Serial &amp; Batch Tracking per Item</div>
          <div class="cfg-hint">When ON, the "Has Batch No" and "Has Serial No" checkboxes on the item form become active. Items marked with batch/serial will prompt for batch numbers or serial numbers during receiving.</div>
        </div>
        <div class="cfg-ctrl">
          <label class="cfg-toggle">
            <input type="checkbox" name="sb_enable_serial_and_batch_no_for_item" value="1" <?php echo $sbv('enable_serial_and_batch_no_for_item')==='1'?'checked':''; ?>>
            <span class="cfg-toggle-sl"></span>
          </label>
          <span id="sb-master-status" style="font-size:12px;font-weight:700;color:<?php echo $sbv('enable_serial_and_batch_no_for_item')==='1'?'#16a34a':'#94a3b8'; ?>">
            <?php echo $sbv('enable_serial_and_batch_no_for_item')==='1'?'Enabled':'Disabled'; ?>
          </span>
        </div>
      </div>

      <div class="cfg-section-title">Batch Settings</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Do Not Use Batchwise Valuation</div><div class="cfg-hint">When checked, batch items use the item-level valuation method instead of per-batch cost.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="sb_do_not_use_batchwise_valuation" value="1" <?php echo $sbv('do_not_use_batchwise_valuation')==='1'?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Pick Serial / Batch Based On</div><div class="cfg-hint">Which batches/serials to pick first when issuing stock.</div></div>
        <div class="cfg-ctrl">
          <select name="sb_pick_serial_and_batch_based_on" class="cfg-input" style="width:160px">
            <?php foreach (['FIFO'=>'FIFO (First In First Out)','LIFO'=>'LIFO (Last In First Out)','Expiry'=>'Nearest Expiry First'] as $k=>$l): ?>
            <option value="<?php echo $k; ?>" <?php echo $sbv('pick_serial_and_batch_based_on','FIFO')===$k?'selected':''; ?>><?php echo $l; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Allow Negative Stock for Batch</div><div class="cfg-hint">Allow issuing more stock than available in a batch.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="sb_allow_negative_stock_for_batch" value="1" <?php echo $sbv('allow_negative_stock_for_batch')==='1'?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Serial Number Settings</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Allow Existing Serial No</div><div class="cfg-hint">Allow reuse of a serial number that has already been used.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="sb_allow_existing_serial_no" value="1" <?php echo $sbv('allow_existing_serial_no')==='1'?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Auto-Create Bundle for Outward</div><div class="cfg-hint">Automatically create a serial/batch bundle when issuing stock.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="sb_auto_create_serial_and_batch_bundle_for_outward" value="1" <?php echo $sbv('auto_create_serial_and_batch_bundle_for_outward')==='1'?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Disable Serial/Batch Selector</div><div class="cfg-hint">Hide the serial/batch picker on the POS and delivery forms.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="sb_disable_serial_no_and_batch_selector" value="1" <?php echo $sbv('disable_serial_no_and_batch_selector')==='1'?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>

      <div class="cfg-section-title">Legacy / General Serial Tracking</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Enable Serial Number Tracking</div><div class="cfg-hint">Track individual unit serial numbers in deliveries and receipts.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_serial_enabled" value="1" <?php echo pos_get_setting('inv_serial_enabled') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Serial Number as Mandatory</div><div class="cfg-hint">Require a serial number for every unit when processing a delivery or receipt.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_serial_mandatory" value="1" <?php echo pos_get_setting('inv_serial_mandatory') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
    </div><!-- /tab-serial -->

    <!-- ══ MIN / MAX INVENTORY ════════════════════════════ -->
    <div class="cfg-tab" id="tab-minmax">
      <div class="cfg-section-title">Per-Product Minimum / Maximum Stock Levels</div>
      <p style="font-size:12px;color:#94a3b8;margin-bottom:12px">Set reorder thresholds per product. These override the global threshold for individual products.</p>
      <div style="display:flex;gap:8px;margin-bottom:12px;align-items:center">
        <div style="position:relative">
          <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px"></i>
          <input type="text" id="mm-search" class="cfg-input" placeholder="Search product…" style="width:220px;padding-left:30px">
        </div>
        <button type="button" class="btn-inv-primary" onclick="mmSave()"><i class="fa fa-save"></i> Save Levels</button>
      </div>
      <div style="overflow-x:auto">
        <table class="mm-tbl">
          <thead><tr><th>PRODUCT</th><th>SKU</th><th>CURRENT QTY</th><th>MIN (Reorder Point)</th><th>MAX Stock</th></tr></thead>
          <tbody id="mm-body"><tr><td colspan="5"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr></tbody>
        </table>
      </div>
    </div><!-- /tab-minmax -->

    <!-- ══ NOTIFICATIONS ══════════════════════════════════ -->
    <div class="cfg-tab" id="tab-notifications">
      <div class="cfg-section-title">Stock Warning Notifications</div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Enable Inventory Warning Emails</div><div class="cfg-hint">Send email notifications when stock falls below minimum or items are about to expire.</div></div>
        <div class="cfg-ctrl"><label class="cfg-toggle"><input type="checkbox" name="inv_warnings_enabled" value="1" <?php echo pos_get_setting('inv_warnings_enabled') == '1' ? 'checked' : ''; ?>><span class="cfg-toggle-sl"></span></label></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Warning Check Time</div><div class="cfg-hint">Hour of the day (0–23) to run the stock check via cron.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_warning_hour" class="cfg-input" value="<?php echo pos_get_setting('inv_warning_hour') ?: 8; ?>" style="width:90px" min="0" max="23"><span style="font-size:12px;color:#94a3b8">:00</span></div>
      </div>
      <div class="cfg-row">
        <div><div class="cfg-lbl">Expiry Alert (days before)</div><div class="cfg-hint">Send an alert this many days before a batch expiry date.</div></div>
        <div class="cfg-ctrl"><input type="number" name="inv_expiry_notify_days" class="cfg-input" value="<?php echo pos_get_setting('inv_expiry_notify_days') ?: 14; ?>" style="width:90px" min="1"><span style="font-size:12px;color:#94a3b8">days</span></div>
      </div>
    </div><!-- /tab-notifications -->

    <!-- ══ APPROVAL SETTINGS ══════════════════════════════ -->
    <div class="cfg-tab" id="tab-approval">

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
        <div>
          <div style="font-size:15px;font-weight:700;color:#1a2520">Approval Settings</div>
          <div style="font-size:12px;color:#6b7c72;margin-top:2px">Define who must approve each document type before it can be confirmed.</div>
        </div>
        <button type="button" class="btn-inv-primary" onclick="apvOpenModal(0)">
          <i class="fa fa-plus"></i> New approval setting
        </button>
      </div>

      <!-- Controls bar -->
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:6px">
          <select id="apv-per-page" class="cfg-input" style="width:70px;height:32px;font-size:12px" onchange="apvLoad()">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
          </select>
          <button type="button" class="btn-inv-secondary" style="height:32px;padding:0 12px;font-size:12px" onclick="apvExport()">Export</button>
        </div>
        <div style="margin-left:auto;display:flex;align-items:center;gap:6px">
          <i class="fa fa-search" style="color:#9cbaaa;font-size:12px"></i>
          <input type="text" id="apv-search" class="cfg-input" placeholder="Search…" style="width:180px;height:32px" oninput="clearTimeout(apv_timer);apv_timer=setTimeout(apvLoad,300)">
        </div>
      </div>

      <div style="overflow-x:auto;border:1px solid #e2ece6;border-radius:9px">
        <table class="mm-tbl" style="min-width:500px">
          <thead>
            <tr>
              <th style="width:60px">ORDER</th>
              <th>NAME</th>
              <th>RELATED</th>
              <th style="width:100px;text-align:center">OPTIONS</th>
            </tr>
          </thead>
          <tbody id="apv-body">
            <tr><td colspan="4" style="text-align:center;padding:28px;color:#9cbaaa"><i class="fa fa-spinner fa-spin" style="font-size:18px"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div><!-- /tab-approval -->

    <!-- ══ APPROVAL MODAL ══════════════════════════════════ -->
    <div id="apv-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1050" onclick="apvCloseModal()"></div>
    <div id="apv-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:640px;max-width:96vw;max-height:90vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);z-index:1060;padding:0">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e8f0eb">
        <h5 id="apv-modal-title" style="margin:0;font-size:15px;font-weight:700;color:#1a2520">New approval setting</h5>
        <button type="button" onclick="apvCloseModal()" style="background:none;border:none;cursor:pointer;font-size:18px;color:#6b7c72;padding:0;line-height:1">&times;</button>
      </div>
      <div style="padding:24px">
        <input type="hidden" id="apv-id" value="0">

        <div style="margin-bottom:16px">
          <label style="font-size:12px;font-weight:700;color:#3d4f45;display:block;margin-bottom:5px"><span style="color:#ef4444">*</span> Subject</label>
          <input type="text" id="apv-subject" class="cfg-input" style="width:100%" placeholder="e.g. Receipt approval required">
        </div>

        <div style="margin-bottom:16px">
          <label style="font-size:12px;font-weight:700;color:#3d4f45;display:block;margin-bottom:5px"><span style="color:#ef4444">*</span> Related</label>
          <select id="apv-related" class="cfg-input" style="width:100%">
            <option value="">Nothing selected</option>
            <option value="receipt">Inventory Receipt</option>
            <option value="delivery">Inventory Delivery</option>
            <option value="transfer">Internal Transfer</option>
            <option value="adjustment">Loss &amp; Adjustment</option>
            <option value="return">Return Order</option>
            <option value="packing">Packing List</option>
            <option value="stocktake">Stock Take</option>
          </select>
        </div>

        <div style="margin-bottom:20px">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#1a2520;cursor:pointer">
            <input type="checkbox" id="apv-single" style="accent-color:#16a34a;width:15px;height:15px">
            Only 1 person needs to approve the transaction
            <span title="When checked, approval by any one of the listed staff is sufficient." style="width:18px;height:18px;border-radius:50%;background:#0284c7;color:#fff;font-size:10px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:help;flex-shrink:0">?</span>
          </label>
        </div>

        <div style="display:grid;grid-template-columns:1fr 140px 32px;gap:8px;align-items:center;margin-bottom:8px">
          <div style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px">Staff</div>
          <div style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px">Action</div>
          <div></div>
        </div>
        <div id="apv-approver-rows"></div>
        <button type="button" onclick="apvAddRow()" style="background:none;border:1px dashed #b2d4be;border-radius:7px;color:#16a34a;font-size:13px;font-weight:600;padding:7px 14px;cursor:pointer;width:100%;margin-top:4px">
          <i class="fa fa-plus"></i> Add approver
        </button>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #e8f0eb;background:#f8fdf9;border-radius:0 0 12px 12px">
        <button type="button" onclick="apvCloseModal()" class="btn-inv-secondary">Close</button>
        <button type="button" onclick="apvSave()" class="btn-inv-primary"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>

    <!-- ══ PERMISSIONS ════════════════════════════════════ -->
    <div class="cfg-tab" id="tab-permissions">
      <div class="cfg-section-title">Role-Based Inventory Permissions</div>
      <p style="font-size:12px;color:#94a3b8;margin-bottom:16px">Configure what each role can do within the inventory module.</p>
      <?php
      $perm_roles = ['cashier'=>'Cashier','supervisor'=>'Supervisor','manager'=>'Manager','admin'=>'Admin'];
      $perm_actions = [
          'view'      => 'View stock &amp; reports',
          'create'    => 'Create receipts / deliveries',
          'edit'      => 'Edit draft documents',
          'confirm'   => 'Confirm / approve documents',
          'delete'    => 'Delete documents',
          'adjust'    => 'Perform adjustments',
          'transfer'  => 'Create internal transfers',
          'stocktake' => 'Perform stock takes',
          'settings'  => 'Manage settings',
      ];
      ?>
      <div style="overflow-x:auto">
        <table class="mm-tbl">
          <thead><tr><th>PERMISSION</th><?php foreach ($perm_roles as $rk=>$rl): ?><th style="text-align:center"><?php echo $rl; ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php foreach ($perm_actions as $ak=>$al): ?>
            <tr>
              <td><span style="font-size:12px;font-weight:600;color:#334155"><?php echo $al; ?></span></td>
              <?php foreach ($perm_roles as $rk=>$rl):
                $pk = 'inv_perm_'.$rk.'_'.$ak;
                $def = in_array($rk,['manager','admin']) || ($ak==='view' && in_array($rk,['cashier','supervisor']));
                $cv = pos_get_setting($pk);
                $chk = ($cv !== '' && $cv !== null && $cv !== false) ? ($cv=='1') : $def;
              ?>
              <td style="text-align:center"><label class="cfg-toggle" style="margin:0 auto"><input type="checkbox" name="<?php echo $pk; ?>" value="1" <?php echo $chk?'checked':''; ?>><span class="cfg-toggle-sl"></span></label></td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div><!-- /tab-permissions -->

    <!-- ══ RESET DATA ════════════════════════════════════ -->
    <div class="cfg-tab" id="tab-reset">
      <div class="cfg-section-title" style="color:#ef4444">&#9888; Danger Zone — Reset Inventory Data</div>
      <p style="font-size:13px;color:#64748b;margin-bottom:20px">These actions permanently delete data and <strong>cannot be undone</strong>. Please take a database backup before proceeding.</p>
      <?php
      $reset_actions = [
          ['reset_movements',   'Clear Movement History',           '#f59e0b', 'Deletes all records from the inventory movements log. Stock quantities are NOT affected.'],
          ['reset_documents',   'Delete All Draft Documents',       '#ef4444', 'Permanently deletes all draft receipts, deliveries, transfers, adjustments, returns, and packing lists.'],
          ['reset_batches',     'Clear Batch / Lot Records',        '#ef4444', 'Deletes all batch and lot tracking records. Stock quantities are NOT affected.'],
          ['reset_stock_takes', 'Delete Stock Take Records',        '#ef4444', 'Permanently removes all stock take (physical count) records.'],
          ['reset_stock_zero',  'Zero All Stock Quantities',        '#b91c1c', 'Sets all inventory quantities to 0 across all branches. Will log adjustment movements.'],
          ['reset_all',         'Reset Entire Inventory Module',    '#7f1d1d', 'Deletes ALL inventory data: movements, documents, batches, stock levels. Complete fresh start.'],
      ];
      foreach ($reset_actions as [$act,$lbl,$clr,$desc]):
      ?>
      <div class="reset-card" style="border-color:<?php echo $clr; ?>44">
        <div style="flex:1">
          <div style="font-weight:700;font-size:13px;color:#0d2818"><?php echo $lbl; ?></div>
          <div style="font-size:12px;color:#64748b;margin-top:3px"><?php echo $desc; ?></div>
        </div>
        <button type="button" class="btn-inv-secondary" style="color:<?php echo $clr; ?>;border-color:<?php echo $clr; ?>66;flex-shrink:0" onclick="cfgReset('<?php echo $act; ?>','<?php echo addslashes($lbl); ?>')">
          <i class="fa fa-exclamation-triangle"></i> <?php echo $lbl; ?>
        </button>
      </div>
      <?php endforeach; ?>
    </div><!-- /tab-reset -->

    <!-- Save bar -->
    <div class="cfg-save-bar" id="cfg-save-bar">
      <button type="button" class="btn-inv-primary" onclick="cfgSave()"><i class="fa fa-save"></i> Save Settings</button>
      <span id="cfg-save-msg" style="font-size:12px;color:#22c55e;display:none"><i class="fa fa-check-circle"></i> Saved!</span>
    </div>

    </form>
  </div><!-- /cfg-body -->
</div><!-- /cfg-wrap -->

</div><!-- /inv-card -->
</div><!-- /inv-content -->
</div>
</div>
<?php init_tail(); ?>
<script>
var SAVE_URL  = '<?php echo admin_url('pos_system/inv_save/settings'); ?>';
var RESET_URL = '<?php echo admin_url('pos_system/inv_action'); ?>';
var MM_URL    = '<?php echo admin_url('pos_system/inv_ajax/minmax'); ?>';
var MM_SAVE   = '<?php echo admin_url('pos_system/inv_save/minmax'); ?>';
var APV_URL   = '<?php echo admin_url('pos_system/inv_ajax/approval_list'); ?>';
var APV_SAVE  = '<?php echo admin_url('pos_system/inv_save/approval'); ?>';
var APV_ACT   = '<?php echo admin_url('pos_system/inv_action'); ?>';
var _csrf_n   = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v   = '<?php echo $this->security->get_csrf_hash(); ?>';
var APV_STAFF = <?php
    $sl = isset($staff_list) ? $staff_list : [];
    $out = [];
    foreach ($sl as $s) {
        $out[] = ['id' => (int)$s['staffid'], 'name' => $s['firstname'] . ' ' . $s['lastname']];
    }
    echo json_encode($out);
?>;

$('.cfg-sidebar a[data-tab]').on('click', function(e) {
    e.preventDefault();
    var t = $(this).data('tab');
    $('.cfg-sidebar a').removeClass('active');
    $(this).addClass('active');
    $('.cfg-tab').removeClass('active');
    $('#tab-' + t).addClass('active');
    var noSave = ['minmax','reset','approval'];
    $('#cfg-save-bar').toggle(!noSave.includes(t));
    if (t === 'minmax') mmLoad();
    if (t === 'approval') apvLoad();
});

function cfgSave() {
    var d = {}; d[_csrf_n] = _csrf_v;
    $('#cfg-form input[type="checkbox"]').each(function() { d[this.name] = this.checked ? '1' : '0'; });
    $('#cfg-form input:not([type="checkbox"]):not([type="radio"]), #cfg-form textarea, #cfg-form select').each(function() { if (this.name) d[this.name] = this.value; });
    $('#cfg-form input[type="radio"]:checked').each(function() { d[this.name] = this.value; });
    $.post(SAVE_URL, d, function(r) {
        if (r.success) { alert_float('success','Settings saved.'); $('#cfg-save-msg').show(); setTimeout(function(){ $('#cfg-save-msg').hide(); }, 2000); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

var mm_timer;
function mmLoad() {
    $.getJSON(MM_URL, {search: $('#mm-search').val()}, function(r) {
        if (!r.rows || !r.rows.length) { $('#mm-body').html('<tr><td colspan="5"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No products found.</p></div></td></tr>'); return; }
        var html = '';
        $.each(r.rows, function(i, p) {
            html += '<tr><td><strong>' + p.name + '</strong></td><td style="color:#94a3b8;font-size:11px">' + (p.sku||'—') + '</td>';
            html += '<td style="text-align:right">' + parseFloat(p.current_qty||0).toFixed(2) + '</td>';
            html += '<td><input type="number" class="cfg-input mm-min" data-pid="'+p.id+'" value="'+(p.reorder_point||'')+'" placeholder="0" min="0" style="width:110px"></td>';
            html += '<td><input type="number" class="cfg-input mm-max" data-pid="'+p.id+'" value="'+(p.max_stock||'')+'" placeholder="0" min="0" style="width:110px"></td></tr>';
        });
        $('#mm-body').html(html);
    });
}
function mmSave() {
    var updates = [];
    $('.mm-min').each(function() { var pid = $(this).data('pid'); updates.push({id: pid, reorder_point: $(this).val(), max_stock: $('.mm-max[data-pid="'+pid+'"]').val()}); });
    if (!updates.length) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.updates = JSON.stringify(updates);
    $.post(MM_SAVE, d, function(r) {
        if (r.success) alert_float('success','Stock levels saved.'); else alert_float('danger', r.error||'Save failed.');
    }, 'json');
}
$('#mm-search').on('input', function() { clearTimeout(mm_timer); mm_timer = setTimeout(mmLoad, 300); });

function cfgReset(action, label) {
    if (!confirm('WARNING: ' + label + '\n\nThis cannot be undone. Continue?')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.action = action; d.type = 'reset';
    $.post(RESET_URL, d, function(r) {
        if (r.success) alert_float('success', label + ' completed.');
        else alert_float('danger', r.error || 'Operation failed.');
    }, 'json');
}

function cfgBulkAction(action) {
    if (!confirm('Apply "' + action + '" to all products?')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.action = action; d.type = 'bulk';
    $.post(RESET_URL, d, function(r) {
        if (r.success) alert_float('success', 'Done: ' + (r.count||'') + ' products updated.');
        else alert_float('danger', r.error||'Failed.');
    }, 'json');
}

/* ── Approval Settings ─────────────────────────────────────── */
var apv_timer;

var APV_RELATED_LABELS = {
    'receipt':    'Inventory Receipt',
    'delivery':   'Inventory Delivery',
    'transfer':   'Internal Transfer',
    'adjustment': 'Loss & Adjustment',
    'return':     'Return Order',
    'packing':    'Packing List',
    'stocktake':  'Stock Take'
};

function apvLoad() {
    var q = $('#apv-search').val();
    var pp = $('#apv-per-page').val();
    $.getJSON(APV_URL, {search: q, per_page: pp}, function(r) {
        var rows = r.rows || [];
        if (!rows.length) {
            $('#apv-body').html('<tr><td colspan="4" style="text-align:center;padding:28px;color:#9cbaaa"><i class="fa fa-inbox" style="font-size:20px"></i><div style="margin-top:6px;font-size:12px">No approval settings yet. Click <strong>New approval setting</strong> to add one.</div></td></tr>');
            return;
        }
        var html = '';
        $.each(rows, function(i, row) {
            var rel = APV_RELATED_LABELS[row.related] || row.related;
            html += '<tr data-id="'+row.id+'" data-order="'+(i+1)+'">';
            html += '<td style="color:#6b7c72;font-size:12px;text-align:center">'+row.sort_order+'</td>';
            html += '<td><strong style="color:#1a2520;font-size:13px">'+esc(row.subject)+'</strong>';
            if (row.staff_labels) html += '<div style="font-size:11px;color:#6b7c72;margin-top:2px">'+esc(row.staff_labels)+'</div>';
            html += '</td>';
            html += '<td><span style="background:#edf7f1;color:#166534;border:1px solid #b8ddc8;border-radius:5px;padding:2px 8px;font-size:11px;font-weight:600">'+esc(rel)+'</span></td>';
            html += '<td style="text-align:center">';
            html += '<button type="button" onclick="apvOpenModal('+row.id+')" style="background:none;border:none;color:#16a34a;cursor:pointer;padding:4px 7px;font-size:13px" title="Edit"><i class="fa fa-edit"></i></button>';
            html += '<button type="button" onclick="apvDelete('+row.id+')" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:4px 7px;font-size:13px" title="Delete"><i class="fa fa-trash"></i></button>';
            html += '</td></tr>';
        });
        $('#apv-body').html(html);
    });
}

function apvOpenModal(id) {
    $('#apv-id').val(id);
    $('#apv-subject').val('');
    $('#apv-related').val('');
    $('#apv-single').prop('checked', false);
    $('#apv-approver-rows').empty();
    $('#apv-modal-title').text(id ? 'Edit approval setting' : 'New approval setting');
    if (id) {
        $.getJSON(APV_URL, {id: id}, function(r) {
            var row = r.row || (r.rows && r.rows[0]);
            if (!row) return;
            $('#apv-subject').val(row.subject || '');
            $('#apv-related').val(row.related || '');
            $('#apv-single').prop('checked', row.single_approver == 1);
            var approvers = [];
            try { approvers = JSON.parse(row.approvers || '[]'); } catch(e) {}
            $.each(approvers, function(i, a) { apvAddRow(a.staff_id, a.action); });
            if (!approvers.length) apvAddRow();
        });
    } else {
        apvAddRow();
    }
    $('#apv-backdrop, #apv-modal').show();
}

function apvCloseModal() {
    $('#apv-backdrop, #apv-modal').hide();
}

function apvBuildStaffOptions(selected) {
    var html = '<option value="">Select staff…</option>';
    $.each(APV_STAFF, function(i, s) {
        html += '<option value="'+s.id+'"'+(s.id == selected ? ' selected' : '')+'>'+esc(s.name)+'</option>';
    });
    return html;
}

function apvAddRow(staff_id, action) {
    var rid = 'apvr' + Date.now() + Math.random().toString(36).substr(2,4);
    var html = '<div id="'+rid+'" style="display:grid;grid-template-columns:1fr 140px 32px;gap:8px;align-items:center;margin-bottom:6px">';
    html += '<select class="cfg-input apv-staff-sel" style="height:34px">'+apvBuildStaffOptions(staff_id||'')+'</select>';
    html += '<select class="cfg-input apv-action-sel" style="height:34px;width:100%">';
    html += '<option value="approve"'+((!action||action==='approve')?' selected':'')+'">Approve</option>';
    html += '<option value="sign"'+(action==='sign'?' selected':'')+'>Sign</option>';
    html += '<option value="review"'+(action==='review'?' selected':'')+'>Review</option>';
    html += '</select>';
    html += '<button type="button" onclick="$(\'#'+rid+'\').remove()" style="width:32px;height:32px;border:1px solid #f9a8a8;border-radius:7px;background:#fff5f5;color:#ef4444;cursor:pointer;font-size:13px;flex-shrink:0"><i class="fa fa-times"></i></button>';
    html += '</div>';
    $('#apv-approver-rows').append(html);
}

function apvSave() {
    var subject = $.trim($('#apv-subject').val());
    var related = $('#apv-related').val();
    if (!subject) { alert_float('warning','Subject is required.'); $('#apv-subject').focus(); return; }
    if (!related) { alert_float('warning','Related document type is required.'); $('#apv-related').focus(); return; }
    var approvers = [];
    $('#apv-approver-rows > div').each(function() {
        var sid = $(this).find('.apv-staff-sel').val();
        var act = $(this).find('.apv-action-sel').val();
        if (sid) approvers.push({staff_id: parseInt(sid), action: act});
    });
    var d = {};
    d[_csrf_n]        = _csrf_v;
    d.id              = $('#apv-id').val();
    d.subject         = subject;
    d.related         = related;
    d.single_approver = $('#apv-single').is(':checked') ? 1 : 0;
    d.approvers       = JSON.stringify(approvers);
    $.post(APV_SAVE, d, function(r) {
        if (r.success) {
            _csrf_v = r.csrf_hash || _csrf_v;
            alert_float('success', d.id > 0 ? 'Approval setting updated.' : 'Approval setting created.');
            apvCloseModal();
            apvLoad();
        } else {
            alert_float('danger', r.error || 'Save failed.');
        }
    }, 'json');
}

function apvDelete(id) {
    if (!confirm('Delete this approval setting?')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.type = 'approval'; d.action = 'delete'; d.id = id;
    $.post(APV_ACT, d, function(r) {
        _csrf_v = r.csrf_hash || _csrf_v;
        if (r.success) { alert_float('success', 'Approval setting deleted.'); apvLoad(); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}

function apvExport() {
    alert_float('info', 'Export coming soon.');
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
