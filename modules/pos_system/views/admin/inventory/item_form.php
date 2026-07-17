<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$item       = $item ?? null;
$is_new     = $is_new ?? true;
$csrf_name  = $this->security->get_csrf_token_name();
$csrf_token = $this->security->get_csrf_hash();
$v = function($k, $default = '') use ($item) { return htmlspecialchars($item[$k] ?? $default, ENT_QUOTES); };
$vi = function($k, $default = 0) use ($item) { return (int)($item[$k] ?? $default); };
$vf = function($k, $decimals = 3, $default = '0') use ($item) {
    $val = $item[$k] ?? $default;
    return number_format((float)$val, $decimals, '.', '');
};
$sel = function($k, $val) use ($item) { return ($item[$k] ?? '') == $val ? 'selected' : ''; };
$chk = function($k, $default = 0) use ($item) { return (int)($item[$k] ?? $default) ? 'checked' : ''; };
?>
<div id="wrapper">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'    => 'items',
    'inv_branches'   => $branches,
    'inv_branch_id'  => $branch_id,
]); ?>
<div class="content" style="padding-top:0">

<!-- ════════════════════ STYLES ════════════════════ -->
<style>
:root {
  --pg: #1a5c38;   /* primary green */
  --pgl: #2e7d52;
  --pgd: #124028;
  --gb:  #f0fdf4;
  --gbl: #e8f5e9;
  --border: #dee2e6;
  --txt: #2c3e50;
  --muted: #6c757d;
}

/* ── Form shell ── */
.ifrm-wrap { display:flex; flex-direction:column; min-height:calc(100vh - 56px); background:#f5f6fa; }

/* ── Header ── */
.ifrm-hdr {
  background:#fff;
  border-bottom:1px solid var(--border);
  padding:10px 20px;
  display:flex;
  align-items:center;
  gap:14px;
  position:sticky;
  top:52px;
  z-index:190;
  box-shadow:0 2px 6px rgba(0,0,0,.06);
}
.ifrm-breadcrumb { flex:1; font-size:12px; color:var(--muted); }
.ifrm-breadcrumb a { color:var(--pg); text-decoration:none; }
.ifrm-breadcrumb a:hover { text-decoration:underline; }
.ifrm-breadcrumb .sep { margin:0 6px; color:#ccc; }
.ifrm-breadcrumb .current { color:var(--txt); font-weight:600; }
.ifrm-hdr-title { font-size:16px; font-weight:700; color:var(--txt); margin:0 12px; white-space:nowrap; max-width:280px; overflow:hidden; text-overflow:ellipsis; }
.status-badge { padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; cursor:pointer; border:none; }
.status-badge.enabled  { background:var(--gbl); color:var(--pg); border:1px solid #a5d6a7; }
.status-badge.disabled { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }
.ifrm-save-bar { display:flex; gap:7px; align-items:center; }
.btn-ifrm-save { background:var(--pg); color:#fff; border:none; padding:7px 20px; border-radius:6px; font-weight:600; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px; transition:background .15s; }
.btn-ifrm-save:hover { background:var(--pgl); }
.btn-ifrm-save:disabled { background:#aaa; cursor:not-allowed; }
.btn-ifrm-draft { background:#fff; color:var(--txt); border:1.5px solid var(--border); padding:6px 16px; border-radius:6px; font-weight:500; font-size:12px; cursor:pointer; transition:all .15s; }
.btn-ifrm-draft:hover { border-color:var(--pg); color:var(--pg); }
.btn-ifrm-back { background:#fff; color:var(--muted); border:1.5px solid var(--border); padding:6px 12px; border-radius:6px; font-size:12px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:5px; transition:all .15s; }
.btn-ifrm-back:hover { border-color:var(--border); color:var(--txt); text-decoration:none; }

/* ── Body layout ── */
.ifrm-body { display:flex; flex:1; gap:0; padding:0; }
.ifrm-main { flex:1; min-width:0; padding:0 0 60px; }
.ifrm-sidebar { width:260px; min-width:260px; padding:16px 14px; background:#fff; border-left:1px solid var(--border); overflow-y:auto; }

/* ── Tabs ── */
.ifrm-tabs {
  background:#fff;
  border-bottom:2px solid var(--border);
  display:flex;
  overflow-x:auto;
  padding:0 16px;
  gap:0;
  position:sticky;
  top:104px;
  z-index:140;
}
.ifrm-tabs::-webkit-scrollbar { height:3px; }
.ifrm-tabs::-webkit-scrollbar-thumb { background:var(--pg); }
.tab-btn {
  background:none; border:none; padding:11px 16px; font-size:12.5px; font-weight:500;
  color:var(--muted); cursor:pointer; white-space:nowrap; border-bottom:3px solid transparent;
  margin-bottom:-2px; transition:all .15s; font-family:inherit;
}
.tab-btn:hover { color:var(--txt); }
.tab-btn.active { color:var(--pg); border-bottom-color:var(--pg); font-weight:700; }

/* ── Tab panel ── */
.tab-panel { display:none; padding:20px 18px; }
.tab-panel.active { display:block; }

/* ── Card / Section ── */
.ifrm-card {
  background:#fff;
  border-radius:8px;
  border:1px solid var(--border);
  margin-bottom:14px;
  overflow:hidden;
}
.ifrm-card-hdr {
  padding:10px 16px;
  background:var(--gb);
  border-bottom:1px solid var(--border);
  font-size:12px;
  font-weight:700;
  color:var(--pg);
  display:flex;
  align-items:center;
  gap:8px;
  cursor:pointer;
  user-select:none;
}
.ifrm-card-hdr i.chev {
  margin-left:auto;
  font-size:11px;
  color:var(--muted);
  transition:transform .2s;
}
.ifrm-card-hdr.collapsed i.chev { transform:rotate(-90deg); }
.ifrm-card-body { padding:16px; }

/* ── Fields ── */
.ifrm-row { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:12px; }
.ifrm-row:last-child { margin-bottom:0; }
.ifrm-field { display:flex; flex-direction:column; gap:4px; }
.ifrm-field label { font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
.ifrm-field .required { color:#e53935; margin-left:2px; }
.ifrm-field input[type=text],
.ifrm-field input[type=number],
.ifrm-field input[type=date],
.ifrm-field input[type=email],
.ifrm-field select,
.ifrm-field textarea {
  border:1.5px solid var(--border);
  border-radius:6px;
  padding:7px 10px;
  font-size:13px;
  color:var(--txt);
  background:#fff;
  font-family:inherit;
  transition:border-color .15s;
  outline:none;
}
.ifrm-field input:focus,
.ifrm-field select:focus,
.ifrm-field textarea:focus { border-color:var(--pg); box-shadow:0 0 0 3px rgba(26,92,56,.09); }
.ifrm-field input[readonly] { background:#f8f9fa; color:var(--muted); }
.ifrm-field textarea { resize:vertical; min-height:80px; }
.f-auto { flex:1; min-width:140px; }
.f-sm  { min-width:110px; width:110px; }
.f-md  { min-width:180px; }
.f-lg  { min-width:280px; }
.f-xl  { flex:2; min-width:300px; }

/* ── Checkbox row ── */
.chk-row { display:flex; flex-wrap:wrap; gap:14px; padding:4px 0; }
.chk-item { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:12.5px; color:var(--txt); }
.chk-item input[type=checkbox] { width:15px; height:15px; accent-color:var(--pg); cursor:pointer; }

/* ── Toggle / status ── */
.toggle-wrap { display:flex; align-items:center; gap:10px; font-size:13px; }
.toggle-wrap .lbl { color:var(--muted); font-size:12px; }
input.ifrm-toggle { appearance:none; width:40px; height:20px; background:#ccc; border-radius:20px; cursor:pointer; position:relative; transition:background .2s; }
input.ifrm-toggle:checked { background:var(--pg); }
input.ifrm-toggle::after { content:''; position:absolute; width:16px; height:16px; background:#fff; border-radius:50%; top:2px; left:2px; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,.3); }
input.ifrm-toggle:checked::after { left:22px; }

/* ── Child tables ── */
.child-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.child-tbl th { background:var(--gb); padding:7px 10px; font-weight:600; color:var(--pg); border:1px solid var(--border); font-size:11px; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
.child-tbl td { padding:5px 7px; border:1px solid var(--border); vertical-align:middle; }
.child-tbl input, .child-tbl select {
  border:1px solid var(--border); border-radius:5px; padding:5px 8px;
  font-size:12px; width:100%; background:#fff; outline:none; font-family:inherit;
}
.child-tbl input:focus, .child-tbl select:focus { border-color:var(--pg); }
.child-tbl .del-row { background:none; border:none; color:#ccc; cursor:pointer; padding:3px 6px; border-radius:4px; font-size:15px; line-height:1; }
.child-tbl .del-row:hover { color:#e53935; background:#fce4ec; }
.child-tbl td.no-num { text-align:center; color:var(--muted); font-size:11px; min-width:32px; }
.btn-add-row { background:#fff; border:1.5px dashed #a5d6a7; color:var(--pg); padding:5px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all .15s; margin-top:8px; display:inline-flex; align-items:center; gap:5px; }
.btn-add-row:hover { background:var(--gb); border-style:solid; }
.child-tbl-wrap { overflow-x:auto; }

/* ── Sidebar ── */
.sb-img-zone {
  border:2px dashed #a5d6a7;
  border-radius:10px;
  min-height:160px;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  gap:8px;
  margin-bottom:14px;
  cursor:pointer;
  transition:border-color .15s;
  position:relative;
  overflow:hidden;
  background:var(--gb);
}
.sb-img-zone:hover { border-color:var(--pg); }
.sb-img-zone img { width:100%; height:160px; object-fit:contain; display:none; }
.sb-img-zone .placeholder { display:flex; flex-direction:column; align-items:center; gap:6px; color:var(--muted); font-size:12px; }
.sb-img-zone .placeholder i { font-size:32px; color:#a5d6a7; }
#img-input { display:none; }
.sb-section { margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid var(--border); }
.sb-section:last-child { border-bottom:none; }
.sb-lbl { font-size:10px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:7px; display:flex; align-items:center; justify-content:space-between; }
.sb-lbl a { color:var(--pg); font-weight:700; font-size:11px; text-decoration:none; }
.sb-item-name { font-size:15px; font-weight:700; color:var(--txt); word-break:break-word; margin-bottom:3px; }
.sb-item-code { font-size:11px; color:var(--muted); font-family:monospace; }
.sb-meta { font-size:11px; color:var(--muted); margin-top:3px; }
.sb-tag { display:inline-block; background:var(--gbl); color:var(--pg); border-radius:4px; padding:2px 8px; font-size:10px; font-weight:600; margin:2px 2px 2px 0; }
.sb-attach { font-size:12px; color:var(--txt); display:flex; align-items:center; gap:6px; padding:4px 0; border-bottom:1px solid #f0f0f0; }
.sb-attach:last-child { border-bottom:none; }
.sb-attach i { color:var(--pg); }

/* ── Alerts ── */
.ifrm-alert { padding:9px 16px; border-radius:7px; margin-bottom:12px; font-size:13px; display:flex; align-items:center; gap:8px; }
.ifrm-alert.success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.ifrm-alert.error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

/* ── Serial / Batch conditional sub-panels ── */
.sb-cond-panel {
  border-left:3px solid var(--pg);
  background:#f6faf8;
  border-radius:0 8px 8px 0;
  padding:14px 16px;
  margin-top:10px;
  display:none;
}
.sb-cond-panel.visible {
  display:block;
  animation:sbSlide .2s ease;
}
@keyframes sbSlide {
  from { opacity:0; transform:translateY(-4px); }
  to   { opacity:1; transform:translateY(0); }
}
.sb-cond-title { font-size:12px; font-weight:700; color:var(--pg); margin-bottom:12px; display:flex; align-items:center; gap:6px; }
.chk-disabled-wrap { position:relative; }
.chk-disabled-wrap input[type=checkbox]:disabled { cursor:not-allowed; opacity:.45; }
.chk-locked-tip { font-size:10px; color:#e53935; display:none; background:#fce4ec; border:1px solid #ef9a9a; padding:4px 8px; border-radius:4px; margin-left:6px; }
.chk-locked-tip.visible { display:inline; }
.code-checking { font-size:11px; color:var(--muted); margin-top:3px; display:none; }
.code-ok   { color:#2e7d32; }
.code-fail { color:#c62828; }

/* ── Responsive ── */
@media (max-width:900px) {
  .ifrm-sidebar { display:none; }
  .ifrm-body { padding:0; }
}
@media (max-width:600px) {
  .ifrm-row { flex-direction:column; }
  .f-sm, .f-md, .f-lg, .f-xl, .f-auto { width:100%; min-width:0; }
}
</style>

<!-- ════════════════════ FORM ════════════════════ -->
<div class="ifrm-wrap">

  <!-- Header -->
  <div class="ifrm-hdr">
    <a href="<?php echo admin_url('pos_system/inventory/items'); ?>" class="btn-ifrm-back">
      <i class="fa fa-arrow-left"></i> Back
    </a>
    <div class="ifrm-breadcrumb">
      <a href="<?php echo admin_url('pos_system/inventory'); ?>">Inventory</a>
      <span class="sep">/</span>
      <a href="<?php echo admin_url('pos_system/inventory/items'); ?>">Products</a>
      <span class="sep">/</span>
      <span class="current" id="hdr-item-name"><?php echo $is_new ? 'New Product' : $v('name'); ?></span>
    </div>
    <span class="ifrm-hdr-title" id="hdr-title-display"><?php echo $is_new ? 'New Product' : $v('name'); ?></span>
    <button type="button" id="statusBadge"
      class="status-badge <?php echo ($vi('is_active', 1) && ($v('status') !== 'Disabled')) ? 'enabled' : 'disabled'; ?>">
      <i class="fa fa-circle" style="font-size:8px;margin-right:4px"></i>
      <span id="statusBadgeLbl"><?php echo ($vi('is_active', 1) && ($v('status') !== 'Disabled')) ? 'Enabled' : 'Disabled'; ?></span>
    </button>
    <div class="ifrm-save-bar">
      <?php if (!$is_new): ?>
      <button type="button" id="btnDuplicate" class="btn-ifrm-draft" title="Duplicate this item">
        <i class="fa fa-copy"></i> Duplicate
      </button>
      <?php endif; ?>
      <button type="button" id="btnDraft" class="btn-ifrm-draft">
        <i class="fa fa-floppy-o"></i> Save Draft
      </button>
      <button type="button" id="btnSave" class="btn-ifrm-save">
        <i class="fa fa-check"></i> Save
      </button>
    </div>
  </div>

  <!-- Notification area -->
  <div id="ifrmAlert" style="display:none;margin:10px 18px 0"></div>

  <!-- Body -->
  <div class="ifrm-body">

    <!-- ───────── Main (Tabs) ───────── -->
    <div class="ifrm-main">

      <!-- Tab Navigation -->
      <div class="ifrm-tabs" id="ifrmTabs">
        <button class="tab-btn active" data-tab="details">Details</button>
        <button class="tab-btn" data-tab="inventory">Inventory</button>
        <button class="tab-btn" data-tab="defaults">Defaults</button>
        <button class="tab-btn" data-tab="accounting">Accounting</button>
        <button class="tab-btn" data-tab="purchasing">Purchasing</button>
        <button class="tab-btn" data-tab="sales">Sales</button>
        <button class="tab-btn" data-tab="tax">Tax</button>
        <button class="tab-btn" data-tab="quality">Quality</button>
        <button class="tab-btn" data-tab="manufacturing">Manufacturing</button>
        <button class="tab-btn" data-tab="variations" id="varTabBtn">Variations <?php if (!empty($item_variations)): ?><span style="background:#16a34a;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?php echo count($item_variations); ?></span><?php endif; ?></button>
      </div>

      <!-- ════════════ TAB: DETAILS ════════════ -->
      <div class="tab-panel active" id="tab-details">

        <!-- Basic Info -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr">
            <i class="fa fa-info-circle"></i> Basic Information
          </div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-xl">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" id="fieldName" value="<?php echo $v('name'); ?>"
                  placeholder="e.g. Maize Flour 2kg" required autocomplete="off">
              </div>
              <div class="ifrm-field f-md">
                <label>Item Code <span style="color:var(--muted);font-weight:400">(auto)</span></label>
                <input type="text" name="item_code" id="fieldItemCode" value="<?php echo $v('item_code'); ?>"
                  placeholder="Auto-generated" autocomplete="off">
                <div class="code-checking" id="codeStatus"></div>
              </div>
              <div class="ifrm-field" style="min-width:120px">
                <label>Active Status</label>
                <div class="toggle-wrap" style="padding-top:7px">
                  <span class="lbl">Off</span>
                  <input type="checkbox" class="ifrm-toggle" name="is_active" id="fieldIsActive" value="1"
                    <?php echo $chk('is_active', 1); ?>>
                  <span class="lbl">On</span>
                </div>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Item Group / Category <span class="required">*</span></label>
                <select name="category_id" id="fieldCategory">
                  <option value="">— Select —</option>
                  <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>" <?php echo $sel('category_id', $cat['id']); ?>>
                    <?php echo htmlspecialchars($cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field f-auto">
                <label>Default Unit of Measure <span class="required">*</span></label>
                <select name="unit_id" id="fieldUnitId">
                  <option value="">— Select —</option>
                  <?php foreach ($units as $u): ?>
                  <option value="<?php echo $u['id']; ?>" <?php echo $sel('unit_id', $u['id']); ?>>
                    <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['symbol'] ?? ''); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field f-md">
                <label>Item Type</label>
                <select name="type" id="fieldType">
                  <option value="simple"   <?php echo $sel('type','simple'); ?>>Simple Product</option>
                  <option value="variable" <?php echo $sel('type','variable'); ?>>Variable (Has Variants)</option>
                  <option value="service"  <?php echo $sel('type','service'); ?>>Service (No Stock)</option>
                  <option value="bundle"   <?php echo $sel('type','bundle'); ?>>Bundle / Kit</option>
                </select>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Cost Price</label>
                <input type="number" name="cost_price" value="<?php echo $vf('cost_price', 4, '0'); ?>"
                  step="0.0001" min="0" placeholder="0.0000">
              </div>
              <div class="ifrm-field f-auto">
                <label>Selling Price <span class="required">*</span></label>
                <input type="number" name="selling_price" id="fieldSellingPrice"
                  value="<?php echo $vf('selling_price', 4, '0'); ?>" step="0.0001" min="0" required placeholder="0.0000">
              </div>
              <div class="ifrm-field f-auto">
                <label>SKU / Code</label>
                <input type="text" name="sku" value="<?php echo $v('sku'); ?>" placeholder="Auto-generated if blank">
              </div>
              <div class="ifrm-field f-auto">
                <label>Barcode (Primary)</label>
                <input type="text" name="barcode" value="<?php echo $v('barcode'); ?>" placeholder="EAN / UPC">
              </div>
            </div>

            <!-- Checkboxes -->
            <div class="ifrm-row" style="margin-top:4px">
              <div class="ifrm-field" style="width:100%">
                <label>Item Flags</label>
                <div class="chk-row" style="margin-top:4px">
                  <label class="chk-item">
                    <input type="checkbox" name="allow_alternative" value="1" <?php echo $chk('allow_alternative'); ?>>
                    Allow Alternative Item
                  </label>
                  <label class="chk-item">
                    <input type="checkbox" name="track_inventory" value="1" <?php echo $chk('track_inventory', 1); ?>>
                    Maintain Stock
                  </label>
                  <label class="chk-item" id="hasVariantsChk">
                    <input type="checkbox" name="has_variations" value="1" id="fieldHasVariants" <?php echo $chk('has_variations'); ?>>
                    Has Variants
                    <small style="color:var(--muted)">(cannot be used directly in orders)</small>
                  </label>
                  <label class="chk-item">
                    <input type="checkbox" name="is_fixed_asset" value="1" <?php echo $chk('is_fixed_asset'); ?>>
                    Is Fixed Asset
                  </label>
                  <label class="chk-item">
                    <input type="checkbox" name="allow_negative" value="1" <?php echo $chk('allow_negative'); ?>>
                    Allow Negative Stock
                  </label>
                  <label class="chk-item">
                    <input type="checkbox" name="is_pos_visible" value="1" <?php echo $chk('is_pos_visible', 1); ?>>
                    Visible in POS
                  </label>
                </div>
              </div>
            </div>

            <!-- Allowances -->
            <div class="ifrm-row" style="margin-top:8px">
              <div class="ifrm-field f-md">
                <label>Over Delivery/Receipt Allowance (%)</label>
                <input type="number" name="over_delivery_allowance"
                  value="<?php echo $vf('over_delivery_allowance', 3, '0'); ?>" step="0.001" min="0" max="100">
              </div>
              <div class="ifrm-field f-md">
                <label>Over Billing Allowance (%)</label>
                <input type="number" name="over_billing_allowance"
                  value="<?php echo $vf('over_billing_allowance', 3, '0'); ?>" step="0.001" min="0" max="100">
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="desc-body">
            <i class="fa fa-align-left"></i> Description
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="desc-body">
            <div class="ifrm-row">
              <div class="ifrm-field" style="width:100%">
                <label>Description</label>
                <textarea name="description" id="fieldDescription" rows="4"><?php echo htmlspecialchars($item['description'] ?? '', ENT_QUOTES); ?></textarea>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Brand</label>
                <select name="brand_id" id="fieldBrandId">
                  <option value="">— None —</option>
                  <?php foreach ($brands as $b): ?>
                  <option value="<?php echo $b['id']; ?>" <?php echo $sel('brand_id', $b['id']); ?>>
                    <?php echo htmlspecialchars($b['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Units of Measure -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="uom-body">
            <i class="fa fa-balance-scale"></i> Units of Measure (UOM Conversions)
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="uom-body">
            <div class="child-tbl-wrap">
              <table class="child-tbl" id="uomTable">
                <thead>
                  <tr>
                    <th style="width:32px">#</th>
                    <th>UOM <span style="color:#e53935">*</span></th>
                    <th>Conversion Factor</th>
                    <th style="width:38px"></th>
                  </tr>
                </thead>
                <tbody id="uomTbody">
                  <?php foreach (($item['uoms'] ?? []) as $i_row => $row): ?>
                  <tr>
                    <td class="no-num"><?php echo $i_row + 1; ?></td>
                    <td>
                      <select name="uoms[<?php echo $i_row; ?>][uom_id]">
                        <option value="">— Select —</option>
                        <?php foreach ($units as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($row['uom_id'] ?? '') == $u['id'] ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($u['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input type="hidden" name="uoms[<?php echo $i_row; ?>][uom_name]" value="<?php echo htmlspecialchars($row['uom_name'] ?? ''); ?>">
                    </td>
                    <td><input type="number" name="uoms[<?php echo $i_row; ?>][conversion_factor]"
                      value="<?php echo htmlspecialchars($row['conversion_factor'] ?? '1'); ?>" step="0.000001" min="0"></td>
                    <td><button type="button" class="del-row" title="Remove row">&times;</button></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="button" class="btn-add-row" data-target="uomTbody" data-tpl="uomRowTpl">
              <i class="fa fa-plus"></i> Add Row
            </button>
            <div style="margin-top:10px">
              <label class="chk-item">
                <input type="checkbox" name="apply_uoms_for_variants" value="1" <?php echo $chk('apply_uoms_for_variants'); ?>>
                Will also apply for variants
              </label>
            </div>
          </div>
        </div>

      </div><!-- /tab-details -->

      <!-- ════════════ TAB: INVENTORY ════════════ -->
      <div class="tab-panel" id="tab-inventory">

        <!-- Valuation -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr">
            <i class="fa fa-coins"></i> Inventory Valuation
          </div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Valuation Method</label>
                <select name="valuation_method">
                  <option value="FIFO"           <?php echo $sel('valuation_method','FIFO'); ?>>FIFO (First In First Out)</option>
                  <option value="Moving Average" <?php echo $sel('valuation_method','Moving Average'); ?>>Moving Average</option>
                  <option value="LIFO"           <?php echo $sel('valuation_method','LIFO'); ?>>LIFO (Last In First Out)</option>
                </select>
              </div>
              <div class="ifrm-field f-md">
                <label>Valuation Rate <small style="color:var(--muted)">(auto-calculated)</small></label>
                <input type="number" name="valuation_rate" value="<?php echo $vf('valuation_rate', 4); ?>"
                  step="0.0001" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Settings -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr">
            <i class="fa fa-cogs"></i> Inventory Settings
          </div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-sm">
                <label>Shelf Life (Days)</label>
                <input type="number" name="shelf_life_days" value="<?php echo $vi('shelf_life_days'); ?>" min="0" step="1">
              </div>
              <div class="ifrm-field f-sm">
                <label>Warranty (Days)</label>
                <input type="number" name="warranty_days" value="<?php echo $vi('warranty_days'); ?>" min="0" step="1">
              </div>
              <div class="ifrm-field f-md">
                <label>End of Life</label>
                <input type="date" name="end_of_life" value="<?php echo $v('end_of_life', '2099-12-31'); ?>">
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-sm">
                <label>Weight Per Unit</label>
                <input type="number" name="weight_per_unit" value="<?php echo $vf('weight_per_unit'); ?>" step="0.001" min="0">
              </div>
              <div class="ifrm-field f-md">
                <label>Weight UOM</label>
                <select name="weight_uom_id">
                  <option value="">— Select —</option>
                  <?php foreach ($units as $u): ?>
                  <option value="<?php echo $u['id']; ?>" <?php echo $sel('weight_uom_id', $u['id']); ?>>
                    <?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field f-auto">
                <label>Default Material Request Type</label>
                <select name="default_material_request_type">
                  <option value="Purchase"          <?php echo $sel('default_material_request_type','Purchase'); ?>>Purchase</option>
                  <option value="Manufacture"       <?php echo $sel('default_material_request_type','Manufacture'); ?>>Manufacture</option>
                  <option value="Transfer"          <?php echo $sel('default_material_request_type','Transfer'); ?>>Transfer</option>
                  <option value="Customer Provided" <?php echo $sel('default_material_request_type','Customer Provided'); ?>>Customer Provided</option>
                </select>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-sm">
                <label>Reorder Point</label>
                <input type="number" name="reorder_point" value="<?php echo $vf('reorder_point', 2); ?>" step="0.01" min="0">
              </div>
              <div class="ifrm-field f-sm">
                <label>Max Stock</label>
                <input type="number" name="max_stock" value="<?php echo $vf('max_stock', 2); ?>" step="0.01" min="0">
              </div>
            </div>
          </div>
        </div>

        <!-- Barcodes child table -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="barcodes-body">
            <i class="fa fa-barcode"></i> Barcodes
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="barcodes-body">
            <div class="child-tbl-wrap">
              <table class="child-tbl" id="barcodesTable">
                <thead>
                  <tr>
                    <th style="width:32px">#</th>
                    <th>Barcode <span style="color:#e53935">*</span></th>
                    <th>Barcode Type</th>
                    <th>UOM</th>
                    <th style="width:70px">Primary</th>
                    <th style="width:38px"></th>
                  </tr>
                </thead>
                <tbody id="barcodesTbody">
                  <?php foreach (($item['barcodes'] ?? []) as $i_row => $row): ?>
                  <tr data-id="<?php echo (int)($row['id'] ?? 0); ?>">
                    <td class="no-num"><?php echo $i_row + 1; ?></td>
                    <td><input type="text" name="barcodes[<?php echo $i_row; ?>][barcode]" value="<?php echo htmlspecialchars($row['barcode'] ?? ''); ?>" placeholder="Scan or type"></td>
                    <td>
                      <select name="barcodes[<?php echo $i_row; ?>][barcode_type]">
                        <?php foreach (['EAN','UPC','QR','Code128','Code39','ISBN','ISSN','Custom'] as $bt): ?>
                        <option value="<?php echo $bt; ?>" <?php echo ($row['barcode_type'] ?? 'EAN') === $bt ? 'selected' : ''; ?>><?php echo $bt; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <select name="barcodes[<?php echo $i_row; ?>][uom_id]">
                        <option value="">— Default —</option>
                        <?php foreach ($units as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($row['uom_id'] ?? '') == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td style="text-align:center">
                      <button type="button" class="set-primary-barcode <?php echo ($row['is_primary'] ?? 0) ? 'active' : ''; ?>"
                        data-row-id="<?php echo (int)($row['id'] ?? 0); ?>"
                        title="Set as primary barcode"
                        style="border:none;background:none;cursor:pointer;font-size:16px;color:<?php echo ($row['is_primary'] ?? 0) ? '#f59e0b' : '#ccc'; ?>">
                        <i class="fa fa-star"></i>
                      </button>
                    </td>
                    <td><button type="button" class="del-row">&times;</button></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="button" class="btn-add-row" data-target="barcodesTbody" data-tpl="barcodeRowTpl">
              <i class="fa fa-plus"></i> Add Row
            </button>
          </div>
        </div>

        <!-- Auto Re-order -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="reorder-body">
            <i class="fa fa-redo"></i> Auto Re-order Rules
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="reorder-body">
            <p style="font-size:11px;color:var(--muted);margin-bottom:10px">
              <i class="fa fa-info-circle"></i>
              Will also apply for variants unless overridden.
            </p>
            <div class="child-tbl-wrap">
              <table class="child-tbl" id="reorderTable">
                <thead>
                  <tr>
                    <th style="width:32px">#</th>
                    <th>Request For (Branch) <span style="color:#e53935">*</span></th>
                    <th>Check Availability In</th>
                    <th>Re-order Level</th>
                    <th>Re-order Qty</th>
                    <th>Material Request Type</th>
                    <th style="width:38px"></th>
                  </tr>
                </thead>
                <tbody id="reorderTbody">
                  <?php foreach (($item['reorder_rules'] ?? []) as $i_row => $row): ?>
                  <tr>
                    <td class="no-num"><?php echo $i_row + 1; ?></td>
                    <td>
                      <select name="reorder_rules[<?php echo $i_row; ?>][branch_id]">
                        <option value="">— All Branches —</option>
                        <?php foreach ($all_branches as $br): ?>
                        <option value="<?php echo $br['id']; ?>" <?php echo ($row['branch_id'] ?? '') == $br['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($br['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <select name="reorder_rules[<?php echo $i_row; ?>][check_availability_in]">
                        <option value="">— Same Branch —</option>
                        <?php foreach ($all_branches as $br): ?>
                        <option value="<?php echo $br['id']; ?>" <?php echo ($row['check_availability_in'] ?? '') == $br['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($br['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td><input type="number" name="reorder_rules[<?php echo $i_row; ?>][reorder_level]" value="<?php echo htmlspecialchars($row['reorder_level'] ?? '0'); ?>" step="0.001" min="0"></td>
                    <td><input type="number" name="reorder_rules[<?php echo $i_row; ?>][reorder_qty]" value="<?php echo htmlspecialchars($row['reorder_qty'] ?? '0'); ?>" step="0.001" min="0"></td>
                    <td>
                      <select name="reorder_rules[<?php echo $i_row; ?>][material_request_type]">
                        <?php foreach (['Purchase','Manufacture','Transfer','Customer Provided'] as $mrt): ?>
                        <option value="<?php echo $mrt; ?>" <?php echo ($row['material_request_type'] ?? 'Purchase') === $mrt ? 'selected' : ''; ?>><?php echo $mrt; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td><button type="button" class="del-row">&times;</button></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="button" class="btn-add-row" data-target="reorderTbody" data-tpl="reorderRowTpl">
              <i class="fa fa-plus"></i> Add Row
            </button>
          </div>
        </div>

        <!-- Serial / Batch -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="serial-body">
            <i class="fa fa-list-ol"></i> Serial Nos and Batches
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="serial-body">
            <div id="serialBatchGlobalMsg" style="display:none;margin-bottom:10px">
              <div class="ifrm-alert error" style="margin:0">
                <i class="fa fa-lock"></i>
                Serial / Batch tracking is disabled globally.
                <a href="<?php echo admin_url('pos_system/inventory/config_settings#serial_batch'); ?>" style="color:#c62828;font-weight:700;margin-left:6px">Enable in Settings →</a>
              </div>
            </div>
            <div class="chk-row">
              <div class="chk-disabled-wrap">
                <label class="chk-item">
                  <input type="checkbox" name="has_batch_no" id="chkBatchNo" value="1" <?php echo $chk('has_batch_no'); ?>>
                  Has Batch No
                </label>
                <span class="chk-locked-tip" id="batchLockTip">Enable Serial/Batch in Settings first</span>
              </div>
              <div class="chk-disabled-wrap">
                <label class="chk-item">
                  <input type="checkbox" name="has_serial_no" id="chkSerialNo" value="1" <?php echo $chk('has_serial_no'); ?>>
                  Has Serial No
                </label>
                <span class="chk-locked-tip" id="serialLockTip">Enable Serial/Batch in Settings first</span>
              </div>
            </div>

            <!-- Batch sub-panel -->
            <div id="batchPanel" class="sb-cond-panel <?php echo $chk('has_batch_no') ? 'visible' : ''; ?>">
              <div class="sb-cond-title"><i class="fa fa-box"></i> Batch Settings</div>
              <div class="chk-row">
                <label class="chk-item">
                  <input type="checkbox" name="create_new_batch" value="1" <?php echo $chk('create_new_batch'); ?>>
                  Auto-Create New Batch on Purchase Receipt
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="has_expiry_date" value="1" <?php echo $chk('has_expiry_date'); ?>>
                  Has Expiry Date
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="retain_sample" value="1" <?php echo $chk('retain_sample'); ?>>
                  Retain Sample
                </label>
              </div>
              <div class="ifrm-row" style="margin-top:10px">
                <div class="ifrm-field f-auto">
                  <label>Batch Number Series</label>
                  <input type="text" name="batch_number_series" value="<?php echo $v('batch_number_series'); ?>" placeholder="e.g. BATCH-.YYYY.-.####">
                </div>
                <div class="ifrm-field f-auto">
                  <label>Pick Batch Based On</label>
                  <select name="pick_serial_batch_based_on">
                    <option value="FIFO"   <?php echo $sel('pick_serial_batch_based_on','FIFO'); ?>>FIFO (First In First Out)</option>
                    <option value="LIFO"   <?php echo $sel('pick_serial_batch_based_on','LIFO'); ?>>LIFO (Last In First Out)</option>
                    <option value="Expiry" <?php echo $sel('pick_serial_batch_based_on','Expiry'); ?>>Expiry Date</option>
                  </select>
                </div>
              </div>
              <div class="chk-row" style="margin-top:8px">
                <label class="chk-item">
                  <input type="checkbox" name="do_not_use_batchwise_valuation" value="1" <?php echo $chk('do_not_use_batchwise_valuation'); ?>>
                  Do Not Use Batch-wise Valuation
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="auto_create_serial_batch_bundle_outward" value="1" <?php echo $chk('auto_create_serial_batch_bundle_outward'); ?>>
                  Auto Create Bundle For Outward
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="disable_serial_batch_selector" value="1" <?php echo $chk('disable_serial_batch_selector'); ?>>
                  Disable Batch Selector
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="use_serial_batch_fields" value="1" <?php echo $chk('use_serial_batch_fields'); ?>>
                  Use Serial/Batch Fields
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="do_not_update_serial_batch_on_auto_bundle" value="1" <?php echo $chk('do_not_update_serial_batch_on_auto_bundle'); ?>>
                  Do Not Update on Auto Bundle
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="allow_negative_stock_for_batch" value="1" <?php echo $chk('allow_negative_stock_for_batch'); ?>>
                  Allow Negative Stock for Batch
                  <span style="color:#e53935;font-size:10px;margin-left:4px" title="May lead to incorrect valuation">⚠</span>
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="set_bundle_naming_on_naming_series" value="1" <?php echo $chk('set_bundle_naming_on_naming_series'); ?>>
                  Bundle Naming on Naming Series
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="use_naming_series_for_batch" value="1" <?php echo $chk('use_naming_series_for_batch'); ?>>
                  Use Naming Series for Batch ID
                </label>
              </div>
            </div>

            <!-- Serial sub-panel -->
            <div id="serialPanel" class="sb-cond-panel <?php echo $chk('has_serial_no') ? 'visible' : ''; ?>">
              <div class="sb-cond-title"><i class="fa fa-list-ol"></i> Serial No Settings</div>
              <div class="ifrm-row">
                <div class="ifrm-field f-auto">
                  <label>Serial Number Series</label>
                  <input type="text" name="serial_number_series" value="<?php echo $v('serial_number_series'); ?>" placeholder="e.g. SN-.YYYY.-.####">
                </div>
                <div class="ifrm-field f-auto">
                  <label>Pick Serial Based On</label>
                  <select name="pick_serial_batch_based_on">
                    <option value="FIFO"   <?php echo $sel('pick_serial_batch_based_on','FIFO'); ?>>FIFO</option>
                    <option value="LIFO"   <?php echo $sel('pick_serial_batch_based_on','LIFO'); ?>>LIFO</option>
                    <option value="Expiry" <?php echo $sel('pick_serial_batch_based_on','Expiry'); ?>>Expiry</option>
                  </select>
                </div>
              </div>
              <div class="chk-row" style="margin-top:8px">
                <label class="chk-item">
                  <input type="checkbox" name="auto_create_serial_batch_bundle_outward" value="1" <?php echo $chk('auto_create_serial_batch_bundle_outward'); ?>>
                  Auto Create Bundle For Outward
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="disable_serial_batch_selector" value="1" <?php echo $chk('disable_serial_batch_selector'); ?>>
                  Disable Serial No Selector
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="use_serial_batch_fields" value="1" <?php echo $chk('use_serial_batch_fields'); ?>>
                  Use Serial/Batch Fields
                </label>
                <label class="chk-item">
                  <input type="checkbox" name="do_not_update_serial_batch_on_auto_bundle" value="1" <?php echo $chk('do_not_update_serial_batch_on_auto_bundle'); ?>>
                  Do Not Update on Auto Bundle
                </label>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /tab-inventory -->

      <!-- ════════════ TAB: DEFAULTS ════════════ -->
      <div class="tab-panel" id="tab-defaults">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-home"></i> Default Settings</div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Default Branch / Warehouse</label>
                <select name="default_branch_id">
                  <option value="">— All Branches —</option>
                  <?php foreach ($all_branches as $br): ?>
                  <option value="<?php echo $br['id']; ?>" <?php echo $sel('default_branch_id', $br['id']); ?>><?php echo htmlspecialchars($br['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Standard Selling Rate</label>
                <input type="number" name="selling_price" value="<?php echo $vf('selling_price', 4, '0'); ?>" step="0.0001" min="0">
              </div>
              <div class="ifrm-field f-auto">
                <label>Standard Buying Rate</label>
                <input type="number" name="cost_price" value="<?php echo $vf('cost_price', 4, '0'); ?>" step="0.0001" min="0">
              </div>
            </div>
            <div class="chk-row" style="margin-top:8px">
              <label class="chk-item">
                <input type="checkbox" name="is_sales_item" value="1" checked>
                Is Sales Item
              </label>
              <label class="chk-item">
                <input type="checkbox" name="is_purchase_item" value="1" checked>
                Is Purchase Item
              </label>
              <label class="chk-item">
                <input type="checkbox" name="is_pos_visible" value="1" <?php echo $chk('is_pos_visible', 1); ?>>
                Show in POS Terminal
              </label>
            </div>
          </div>
        </div>
      </div><!-- /tab-defaults -->

      <!-- ════════════ TAB: ACCOUNTING ════════════ -->
      <div class="tab-panel" id="tab-accounting">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-book"></i> Accounting Entries</div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Income / Revenue Account</label>
                <input type="text" name="income_account" value="<?php echo $v('income_account'); ?>"
                  placeholder="e.g. 4000 - Sales Revenue">
              </div>
              <div class="ifrm-field f-auto">
                <label>Expense / COGS Account</label>
                <input type="text" name="expense_account" value="<?php echo $v('expense_account'); ?>"
                  placeholder="e.g. 5000 - Cost of Goods Sold">
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Default Tax Rate</label>
                <select name="tax_rate_id">
                  <option value="">— No Tax —</option>
                  <?php foreach ($taxes as $t): ?>
                  <option value="<?php echo $t['id']; ?>" <?php echo $sel('tax_rate_id', $t['id']); ?>>
                    <?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['taxrate']; ?>%)
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field" style="min-width:180px;padding-top:20px">
                <label class="chk-item">
                  <input type="checkbox" name="is_tax_inclusive" value="1" <?php echo $chk('is_tax_inclusive'); ?>>
                  Price is Tax-Inclusive (VAT Inc.)
                </label>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /tab-accounting -->

      <!-- ════════════ TAB: PURCHASING ════════════ -->
      <div class="tab-panel" id="tab-purchasing">

        <!-- Purchasing Settings -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-shopping-cart"></i> Purchasing Settings</div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Default Purchase Unit of Measure</label>
                <select name="default_purchase_uom_id">
                  <option value="">— Same as Default UOM —</option>
                  <?php foreach ($units as $u): ?>
                  <option value="<?php echo $u['id']; ?>" <?php echo $sel('default_purchase_uom_id', $u['id']); ?>>
                    <?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field f-sm">
                <label>Lead Time (Days)</label>
                <input type="number" name="lead_time_days" value="<?php echo $vi('lead_time_days'); ?>" min="0" step="1">
              </div>
              <div class="ifrm-field f-sm">
                <label>Min. Order Qty</label>
                <input type="number" name="min_order_qty" value="<?php echo $vf('min_order_qty'); ?>" step="0.001" min="0">
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-sm">
                <label>Safety Stock</label>
                <input type="number" name="safety_stock" value="<?php echo $vf('safety_stock'); ?>" step="0.001" min="0">
              </div>
              <div class="ifrm-field f-md">
                <label>Last Purchase Rate <small style="color:var(--muted)">(auto)</small></label>
                <input type="number" name="last_purchase_rate" value="<?php echo $vf('last_purchase_rate', 4); ?>" step="0.0001" readonly>
              </div>
            </div>
            <div class="chk-row" style="margin-top:6px">
              <label class="chk-item">
                <input type="checkbox" name="allow_purchase" value="1" <?php echo $chk('allow_purchase', 1); ?>>
                Allow Purchase
              </label>
              <label class="chk-item">
                <input type="checkbox" name="is_customer_provided" value="1" <?php echo $chk('is_customer_provided'); ?>>
                Is Customer Provided Item
              </label>
            </div>
          </div>
        </div>

        <!-- Supplier Details -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="suppliers-body">
            <i class="fa fa-handshake"></i> Supplier Details
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="suppliers-body">
            <div style="margin-bottom:10px">
              <label class="chk-item">
                <input type="checkbox" name="drop_ship" value="1" <?php echo $chk('drop_ship'); ?>>
                Delivered by Supplier (Drop Ship)
              </label>
            </div>
            <div class="child-tbl-wrap">
              <table class="child-tbl" id="suppliersTable">
                <thead>
                  <tr>
                    <th style="width:32px">#</th>
                    <th>Supplier <span style="color:#e53935">*</span></th>
                    <th>Supplier Part No.</th>
                    <th>Lead Time (Days)</th>
                    <th>Min. Qty</th>
                    <th>Last Rate</th>
                    <th style="width:38px"></th>
                  </tr>
                </thead>
                <tbody id="suppliersTbody">
                  <?php foreach (($item['item_suppliers'] ?? []) as $i_row => $row): ?>
                  <tr>
                    <td class="no-num"><?php echo $i_row + 1; ?></td>
                    <td>
                      <select name="item_suppliers[<?php echo $i_row; ?>][supplier_id]" class="supplier-sel-row" data-row="<?php echo $i_row; ?>">
                        <option value="">— Select Supplier —</option>
                        <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['id']; ?>" <?php echo ($row['supplier_id'] ?? '') == $sup['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sup['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input type="hidden" name="item_suppliers[<?php echo $i_row; ?>][supplier_name]" value="<?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?>">
                    </td>
                    <td><input type="text" name="item_suppliers[<?php echo $i_row; ?>][supplier_part_no]" value="<?php echo htmlspecialchars($row['supplier_part_no'] ?? ''); ?>"></td>
                    <td><input type="number" name="item_suppliers[<?php echo $i_row; ?>][lead_time_days]" value="<?php echo (int)($row['lead_time_days'] ?? 0); ?>" step="1" min="0"></td>
                    <td><input type="number" name="item_suppliers[<?php echo $i_row; ?>][min_qty]" value="<?php echo htmlspecialchars($row['min_qty'] ?? '0'); ?>" step="0.001" min="0"></td>
                    <td><input type="number" name="item_suppliers[<?php echo $i_row; ?>][last_purchase_rate]" value="<?php echo htmlspecialchars($row['last_purchase_rate'] ?? '0'); ?>" step="0.0001" min="0"></td>
                    <td><button type="button" class="del-row">&times;</button></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="button" class="btn-add-row" data-target="suppliersTbody" data-tpl="supplierRowTpl">
              <i class="fa fa-plus"></i> Add Supplier
            </button>
          </div>
        </div>

        <!-- Foreign Trade -->
        <div class="ifrm-card">
          <div class="ifrm-card-hdr" data-toggle-section="trade-body">
            <i class="fa fa-globe"></i> Foreign Trade Details
            <i class="fa fa-chevron-down chev"></i>
          </div>
          <div class="ifrm-card-body" id="trade-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Country of Origin</label>
                <select name="country_of_origin">
                  <option value="">— Select Country —</option>
                  <?php
                  $countries = ['Kenya','Uganda','Tanzania','Rwanda','Ethiopia','Burundi','Somalia','South Sudan',
                    'DR Congo','Mozambique','Zambia','Zimbabwe','Malawi','Namibia','Botswana','South Africa',
                    'Nigeria','Ghana','Egypt','Morocco','China','India','United States','United Kingdom',
                    'Germany','France','Italy','Japan','Brazil','Australia','Canada','Other'];
                  foreach ($countries as $cn): ?>
                  <option value="<?php echo $cn; ?>" <?php echo $sel('country_of_origin', $cn); ?>>
                    <?php echo $cn; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="ifrm-field f-auto">
                <label>Customs Tariff Number (HS Code)</label>
                <input type="text" name="customs_tariff_number" value="<?php echo $v('customs_tariff_number'); ?>"
                  placeholder="e.g. 1101.00.00">
              </div>
            </div>
          </div>
        </div>

      </div><!-- /tab-purchasing -->

      <!-- ════════════ TAB: SALES ════════════ -->
      <div class="tab-panel" id="tab-sales">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-tag"></i> Sales Settings</div>
          <div class="ifrm-card-body">
            <div class="chk-row" style="margin-bottom:14px">
              <label class="chk-item">
                <input type="checkbox" name="is_pos_visible" value="1" <?php echo $chk('is_pos_visible', 1); ?>>
                Show in POS Terminal
              </label>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Selling Price</label>
                <input type="number" name="selling_price" value="<?php echo $vf('selling_price', 4, '0'); ?>" step="0.0001" min="0">
              </div>
            </div>
          </div>
        </div>
      </div><!-- /tab-sales -->

      <!-- ════════════ TAB: TAX ════════════ -->
      <div class="tab-panel" id="tab-tax">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-percent"></i> Tax Configuration</div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Tax Rate</label>
                <select name="tax_rate_id">
                  <option value="">— No Tax —</option>
                  <?php foreach ($taxes as $t): ?>
                  <option value="<?php echo $t['id']; ?>" <?php echo $sel('tax_rate_id', $t['id']); ?>>
                    <?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['taxrate']; ?>%)
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="chk-row" style="margin-top:8px">
              <label class="chk-item">
                <input type="checkbox" name="is_tax_inclusive" value="1" <?php echo $chk('is_tax_inclusive'); ?>>
                Price includes tax (VAT Inclusive)
              </label>
            </div>
          </div>
        </div>
      </div><!-- /tab-tax -->

      <!-- ════════════ TAB: QUALITY ════════════ -->
      <div class="tab-panel" id="tab-quality">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-check-double"></i> Quality Inspection</div>
          <div class="ifrm-card-body">
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Quality Inspection Required</label>
                <div class="chk-row" style="margin-top:4px">
                  <label class="chk-item">
                    <input type="checkbox" name="inspection_required_before_purchase" value="1">
                    Before Purchase
                  </label>
                  <label class="chk-item">
                    <input type="checkbox" name="inspection_required_before_delivery" value="1">
                    Before Delivery
                  </label>
                </div>
              </div>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-auto">
                <label>Quality Template</label>
                <input type="text" name="quality_template" placeholder="Quality inspection template name">
              </div>
            </div>
          </div>
        </div>
      </div><!-- /tab-quality -->

      <!-- ════════════ TAB: MANUFACTURING ════════════ -->
      <div class="tab-panel" id="tab-manufacturing">
        <div class="ifrm-card">
          <div class="ifrm-card-hdr"><i class="fa fa-industry"></i> Manufacturing Settings</div>
          <div class="ifrm-card-body">
            <div class="chk-row" style="margin-bottom:14px">
              <label class="chk-item">
                <input type="checkbox" name="is_manufactured" value="1">
                Is Manufactured Item (BOM required)
              </label>
            </div>
            <div class="ifrm-row">
              <div class="ifrm-field f-sm">
                <label>Default BOM Qty</label>
                <input type="number" name="default_bom_qty" value="1" step="0.001" min="0">
              </div>
              <div class="ifrm-field f-auto">
                <label>Manufacturing UOM</label>
                <select name="manufacturing_uom_id">
                  <option value="">— Same as Default —</option>
                  <?php foreach ($units as $u): ?>
                  <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /tab-manufacturing -->

      <!-- ════════════ TAB: VARIATIONS ════════════ -->
      <div class="tab-panel" id="tab-variations">
<?php
$selColor = $item_attr_values['color'] ?? [];
$selSize  = $item_attr_values['size']  ?? [];
$selStyle = $item_attr_values['style'] ?? [];
$selModel = $item_attr_values['model'] ?? [];
?>
<style>
.var-attr-panel { border:1px solid #d4e8db; border-radius:8px; padding:14px 16px; margin-bottom:16px; background:#fafdfb; }
.var-attr-title { font-size:11px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px; display:flex; align-items:center; gap:7px; }
.var-attr-values { display:flex; flex-wrap:wrap; gap:6px; }
.var-attr-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; border:2px solid #d1fae5; background:#fff; font-size:12px; font-weight:500; color:#166534; cursor:pointer; transition:all .12s; user-select:none; }
.var-attr-chip:hover { border-color:#16a34a; background:#f0fdf4; }
.var-attr-chip.selected { border-color:#16a34a; background:#dcfce7; font-weight:700; }
.var-attr-chip .chip-swatch { width:12px; height:12px; border-radius:50%; border:1px solid rgba(0,0,0,.15); }
.var-tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:12.5px; margin-top:12px; }
.var-tbl th { padding:8px 10px; font-size:10px; font-weight:700; color:#4a5e54; text-transform:uppercase; letter-spacing:.6px; background:#f5faf7; border-bottom:2px solid #e2ece6; white-space:nowrap; }
.var-tbl td { padding:5px 6px; border-bottom:1px solid #edf5f0; vertical-align:middle; }
.var-tbl tr:last-child td { border-bottom:none; }
.var-tbl-input { width:100%; height:30px; border:1px solid #d1e8d8; border-radius:5px; padding:0 7px; font-size:12.5px; color:#1a2520; background:#fff; outline:none; }
.var-tbl-input:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.10); }
</style>

        <div class="ifrm-card">
          <div class="ifrm-card-hdr" style="justify-content:space-between">
            <span><i class="fa fa-layer-group"></i> Item Variations</span>
            <span style="font-size:11px;color:#56665e;font-weight:400">Select attributes below, then click Generate to build variant rows</span>
          </div>
          <div class="ifrm-card-body">

            <!-- Has Variations toggle -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:10px 14px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:#14532d;margin:0">
                <input type="checkbox" name="has_variations" id="fieldHasVariants" value="1" <?php echo $chk('has_variations'); ?> style="width:16px;height:16px;accent-color:#16a34a">
                This item has variations (multiple SKUs based on attributes)
              </label>
            </div>

            <div id="varBuilder" style="display:<?php echo !empty($item_variations)||(!empty($selColor)||!empty($selSize)||!empty($selStyle)||!empty($selModel))?'block':'none'; ?>">

              <!-- Attribute selectors -->
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;margin-bottom:18px">

                <?php if ($attr_colors): ?>
                <div class="var-attr-panel">
                  <div class="var-attr-title"><i class="fa fa-palette"></i> Color</div>
                  <div class="var-attr-values" id="varColorPicker">
                    <?php foreach ($attr_colors as $c): ?>
                    <span class="var-attr-chip <?php echo in_array((int)$c['color_id'], $selColor)?'selected':''; ?>"
                          data-attr="color" data-id="<?php echo $c['color_id']; ?>"
                          data-name="<?php echo htmlspecialchars($c['color_name']); ?>">
                      <?php if ($c['color_hex']): ?><span class="chip-swatch" style="background:<?php echo htmlspecialchars($c['color_hex']); ?>"></span><?php endif; ?>
                      <?php echo htmlspecialchars($c['color_name']); ?>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if ($attr_sizes): ?>
                <div class="var-attr-panel">
                  <div class="var-attr-title"><i class="fa fa-text-height"></i> Size</div>
                  <div class="var-attr-values" id="varSizePicker">
                    <?php foreach ($attr_sizes as $s): ?>
                    <span class="var-attr-chip <?php echo in_array((int)$s['size_type_id'], $selSize)?'selected':''; ?>"
                          data-attr="size" data-id="<?php echo $s['size_type_id']; ?>"
                          data-name="<?php echo htmlspecialchars($s['size_symbol']?:$s['size_name']); ?>">
                      <?php echo htmlspecialchars($s['size_name']); ?>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if ($attr_styles): ?>
                <div class="var-attr-panel">
                  <div class="var-attr-title"><i class="fa fa-tshirt"></i> Style</div>
                  <div class="var-attr-values" id="varStylePicker">
                    <?php foreach ($attr_styles as $s): ?>
                    <span class="var-attr-chip <?php echo in_array((int)$s['style_type_id'], $selStyle)?'selected':''; ?>"
                          data-attr="style" data-id="<?php echo $s['style_type_id']; ?>"
                          data-name="<?php echo htmlspecialchars($s['style_name']); ?>">
                      <?php echo htmlspecialchars($s['style_name']); ?>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if ($attr_models): ?>
                <div class="var-attr-panel">
                  <div class="var-attr-title"><i class="fa fa-microchip"></i> Model</div>
                  <div class="var-attr-values" id="varModelPicker">
                    <?php foreach ($attr_models as $m): ?>
                    <span class="var-attr-chip <?php echo in_array((int)$m['id'], $selModel)?'selected':''; ?>"
                          data-attr="model" data-id="<?php echo $m['id']; ?>"
                          data-name="<?php echo htmlspecialchars($m['name']); ?>">
                      <?php echo htmlspecialchars($m['name']); ?>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

              </div>

              <!-- Generate button -->
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                <button type="button" id="btnGenVariants" class="btn-inv-primary" style="padding:8px 18px">
                  <i class="fa fa-magic"></i> Generate Variants
                </button>
                <span style="font-size:12px;color:#56665e">
                  Generates one row per attribute combination. Existing rows are preserved if SKU matches.
                </span>
                <button type="button" id="btnSaveVariants" class="btn-inv-primary" style="padding:8px 18px;background:linear-gradient(135deg,#16a34a,#15803d);margin-left:auto" <?php echo $is_new?'disabled title="Save the item first"':''; ?>>
                  <i class="fa fa-save"></i> Save Variations
                </button>
              </div>

              <!-- Variants table -->
              <div style="overflow-x:auto">
                <table class="var-tbl">
                  <thead>
                    <tr>
                      <th style="width:22px"></th>
                      <th style="min-width:180px">VARIANT NAME</th>
                      <th style="width:120px">SKU</th>
                      <th style="width:110px">BARCODE</th>
                      <th style="width:100px">PRICE OVERRIDE</th>
                      <th style="width:100px">COST PRICE</th>
                      <th style="width:70px">ACTIVE</th>
                      <th style="width:30px"></th>
                    </tr>
                  </thead>
                  <tbody id="varTbody">
                    <?php foreach ($item_variations as $idx => $vr): ?>
                    <tr class="var-row"
                        data-rowid="<?php echo $vr['id']; ?>"
                        data-color="<?php echo $vr['color_id']??''; ?>"
                        data-size="<?php echo $vr['size_id']??''; ?>"
                        data-style="<?php echo $vr['style_id']??''; ?>"
                        data-model="<?php echo $vr['model_id']??''; ?>">
                      <td style="text-align:center;color:#aaa;font-size:11px"><?php echo $idx+1; ?></td>
                      <td><input type="text" class="var-tbl-input var-name" value="<?php echo htmlspecialchars($vr['name']); ?>" placeholder="e.g. Red / XL"></td>
                      <td><input type="text" class="var-tbl-input var-sku" value="<?php echo htmlspecialchars($vr['sku']??''); ?>" placeholder="SKU"></td>
                      <td><input type="text" class="var-tbl-input var-barcode" value="<?php echo htmlspecialchars($vr['barcode']??''); ?>" placeholder="Barcode"></td>
                      <td><input type="number" class="var-tbl-input var-price" value="<?php echo $vr['price_override']!==null?$vr['price_override']:''; ?>" placeholder="—" step="any" min="0"></td>
                      <td><input type="number" class="var-tbl-input var-cost" value="<?php echo $vr['cost_price']!==null?$vr['cost_price']:''; ?>" placeholder="—" step="any" min="0"></td>
                      <td style="text-align:center">
                        <label style="cursor:pointer">
                          <input type="checkbox" class="var-active" <?php echo $vr['is_active']?'checked':''; ?> style="width:15px;height:15px;accent-color:#16a34a">
                        </label>
                      </td>
                      <td><button type="button" class="btn-inv-icon danger var-del-btn" title="Remove"><i class="fa fa-times"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($item_variations)): ?>
                    <tr id="varNoRows"><td colspan="8" style="text-align:center;padding:24px;color:#94a3b8"><i class="fa fa-layer-group" style="font-size:22px;display:block;margin-bottom:6px;opacity:.4"></i>No variants yet. Select attributes above and click Generate.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div id="varCount" style="font-size:12px;color:#56665e;margin-top:8px;padding:4px 0"></div>

            </div><!-- /varBuilder -->

          </div>
        </div>
      </div><!-- /tab-variations -->

    </div><!-- /ifrm-main -->

    <!-- ───────── Sidebar ───────── -->
    <div class="ifrm-sidebar">

      <!-- Image upload -->
      <div class="sb-section">
        <div class="sb-lbl">Item Image <a href="#" id="imgClearBtn" style="display:none">Remove</a></div>
        <div class="sb-img-zone" id="imgDropZone">
          <img id="imgPreview" src="<?php echo ($item && $item['image']) ? base_url($item['image']) : ''; ?>"
            alt="Item Image" style="<?php echo ($item && $item['image']) ? 'display:block' : ''; ?>">
          <div class="placeholder" id="imgPlaceholder" style="<?php echo ($item && $item['image']) ? 'display:none' : ''; ?>">
            <i class="fa fa-image"></i>
            <span>Click to upload</span>
            <small style="font-size:10px">JPG, PNG, GIF · Max 2MB</small>
          </div>
        </div>
        <input type="file" id="img-input" accept="image/*">
        <input type="hidden" name="image" id="fieldImage" value="<?php echo $v('image'); ?>">
        <div id="imgUploadProgress" style="display:none;font-size:11px;color:var(--muted);margin-top:5px">
          <i class="fa fa-spinner fa-spin"></i> Uploading…
        </div>
      </div>

      <!-- Item summary -->
      <div class="sb-section">
        <div class="sb-lbl">Item Info</div>
        <div class="sb-item-name" id="sbItemName"><?php echo $is_new ? '(unsaved)' : $v('name'); ?></div>
        <div class="sb-item-code" id="sbItemCode"><?php echo $is_new ? '' : $v('item_code'); ?></div>
        <?php if (!$is_new && $item): ?>
        <div class="sb-meta">Created: <?php echo date('d M Y', strtotime($item['date_created'])); ?></div>
        <div class="sb-meta">Updated: <?php echo date('d M Y H:i', strtotime($item['date_updated'])); ?></div>
        <?php endif; ?>
      </div>

      <!-- Quick stats -->
      <?php if (!$is_new && $item): ?>
      <div class="sb-section">
        <div class="sb-lbl">Stock Info</div>
        <div style="display:flex;flex-direction:column;gap:5px">
          <div style="display:flex;justify-content:space-between;font-size:12px">
            <span style="color:var(--muted)">Selling Price</span>
            <strong style="color:var(--pg)"><?php echo number_format((float)$item['selling_price'], 2); ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px">
            <span style="color:var(--muted)">Cost Price</span>
            <strong><?php echo number_format((float)$item['cost_price'], 2); ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px">
            <span style="color:var(--muted)">Type</span>
            <span class="sb-tag"><?php echo ucfirst($item['type'] ?? 'simple'); ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px">
            <span style="color:var(--muted)">Status</span>
            <span class="sb-tag" style="<?php echo $vi('is_active',1) ? '' : 'background:#fce4ec;color:#c62828'; ?>">
              <?php echo $vi('is_active', 1) ? 'Active' : 'Inactive'; ?>
            </span>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Links -->
      <div class="sb-section">
        <div class="sb-lbl">Quick Links</div>
        <div style="display:flex;flex-direction:column;gap:7px">
          <a href="<?php echo admin_url('pos_system/inventory/items'); ?>" style="font-size:12px;color:var(--pg)">
            <i class="fa fa-list" style="width:16px"></i> All Products
          </a>
          <?php if (!$is_new && $item): ?>
          <a href="<?php echo admin_url('pos_system/inventory/stock_levels?product=' . $item['id']); ?>" style="font-size:12px;color:var(--pg)">
            <i class="fa fa-warehouse" style="width:16px"></i> Stock Levels
          </a>
          <?php endif; ?>
          <a href="<?php echo admin_url('pos_system/inventory/config_units'); ?>" style="font-size:12px;color:var(--pg)">
            <i class="fa fa-ruler" style="width:16px"></i> Manage Units
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/config_suppliers'); ?>" style="font-size:12px;color:var(--pg)">
            <i class="fa fa-handshake" style="width:16px"></i> Manage Suppliers
          </a>
        </div>
      </div>

    </div><!-- /ifrm-sidebar -->

  </div><!-- /ifrm-body -->
</div><!-- /ifrm-wrap -->

<!-- ════════════ HIDDEN FORM DATA ════════════ -->
<form id="mainForm" style="display:none">
  <?php echo form_hidden($csrf_name, $csrf_token); ?>
  <input type="hidden" name="id" value="<?php echo $is_new ? '' : $item['id']; ?>">
</form>

<!-- ════════════ ROW TEMPLATES (hidden) ════════════ -->

<!-- UOM row template -->
<script type="text/template" id="uomRowTpl">
<tr>
  <td class="no-num">__N__</td>
  <td>
    <select name="uoms[__I__][uom_id]" onchange="syncUomName(this, __I__)">
      <option value="">— Select —</option>
      <?php foreach ($units as $u): ?>
      <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="uoms[__I__][uom_name]" id="uomName__I__" value="">
  </td>
  <td><input type="number" name="uoms[__I__][conversion_factor]" value="1.000000" step="0.000001" min="0"></td>
  <td><button type="button" class="del-row" title="Remove row">&times;</button></td>
</tr>
</script>

<!-- Barcode row template -->
<script type="text/template" id="barcodeRowTpl">
<tr>
  <td class="no-num">__N__</td>
  <td><input type="text" name="barcodes[__I__][barcode]" placeholder="Scan or type"></td>
  <td>
    <select name="barcodes[__I__][barcode_type]">
      <?php foreach (['EAN','UPC','QR','Code128','Code39','ISBN','ISSN','Custom'] as $bt): ?>
      <option value="<?php echo $bt; ?>"><?php echo $bt; ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td>
    <select name="barcodes[__I__][uom_id]">
      <option value="">— Default —</option>
      <?php foreach ($units as $u): ?>
      <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td><button type="button" class="del-row">&times;</button></td>
</tr>
</script>

<!-- Reorder row template -->
<script type="text/template" id="reorderRowTpl">
<tr>
  <td class="no-num">__N__</td>
  <td>
    <select name="reorder_rules[__I__][branch_id]">
      <option value="">— All Branches —</option>
      <?php foreach ($all_branches as $br): ?>
      <option value="<?php echo $br['id']; ?>"><?php echo htmlspecialchars($br['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td>
    <select name="reorder_rules[__I__][check_availability_in]">
      <option value="">— Same Branch —</option>
      <?php foreach ($all_branches as $br): ?>
      <option value="<?php echo $br['id']; ?>"><?php echo htmlspecialchars($br['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td><input type="number" name="reorder_rules[__I__][reorder_level]" value="0" step="0.001" min="0"></td>
  <td><input type="number" name="reorder_rules[__I__][reorder_qty]"   value="0" step="0.001" min="0"></td>
  <td>
    <select name="reorder_rules[__I__][material_request_type]">
      <option value="Purchase">Purchase</option>
      <option value="Manufacture">Manufacture</option>
      <option value="Transfer">Transfer</option>
      <option value="Customer Provided">Customer Provided</option>
    </select>
  </td>
  <td><button type="button" class="del-row">&times;</button></td>
</tr>
</script>

<!-- Supplier row template -->
<script type="text/template" id="supplierRowTpl">
<tr>
  <td class="no-num">__N__</td>
  <td>
    <select name="item_suppliers[__I__][supplier_id]" onchange="syncSupplierName(this, __I__)">
      <option value="">— Select Supplier —</option>
      <?php foreach ($suppliers as $sup): ?>
      <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="item_suppliers[__I__][supplier_name]" id="supName__I__" value="">
  </td>
  <td><input type="text"   name="item_suppliers[__I__][supplier_part_no]"></td>
  <td><input type="number" name="item_suppliers[__I__][lead_time_days]"     value="0" step="1"      min="0"></td>
  <td><input type="number" name="item_suppliers[__I__][min_qty]"            value="0" step="0.001"  min="0"></td>
  <td><input type="number" name="item_suppliers[__I__][last_purchase_rate]" value="0" step="0.0001" min="0"></td>
  <td><button type="button" class="del-row">&times;</button></td>
</tr>
</script>

<!-- ════════════ JAVASCRIPT ════════════ -->
<script>
(function() {
'use strict';

// ─── Config ───────────────────────────────────────────────────────────────────
var SAVE_URL  = '<?php echo admin_url("pos_system/inv_item_save"); ?>';
var IMG_URL   = '<?php echo admin_url("pos_system/inv_item_image_upload"); ?>';
var ITEM_ID   = '<?php echo $is_new ? '' : $item['id']; ?>';
var IS_NEW    = <?php echo $is_new ? 'true' : 'false'; ?>;
var CSRF_NAME = '<?php echo $csrf_name; ?>';
var DRAFT_KEY = 'pos_item_draft_' + (ITEM_ID || 'new');

// Unit map for syncing uom_name
var UNITS = <?php echo json_encode(array_map(function($u){ return ['id' => $u['id'], 'name' => $u['name']]; }, $units)); ?>;
var SUPPLIERS = <?php echo json_encode(array_map(function($s){ return ['id' => $s['id'], 'name' => $s['name']]; }, $suppliers)); ?>;

// ─── Tab switching (replaced by initTabsWithHash below) ──────────────────────

// ─── Collapsible sections ──────────────────────────────────────────────────────
function initCollapsible() {
  document.querySelectorAll('[data-toggle-section]').forEach(function(hdr) {
    hdr.addEventListener('click', function() {
      var target = document.getElementById(this.dataset.toggleSection);
      if (!target) return;
      var hidden = target.style.display === 'none';
      target.style.display = hidden ? 'block' : 'none';
      this.classList.toggle('collapsed', !hidden);
    });
  });
}

// ─── Dynamic child-table rows ──────────────────────────────────────────────────
var rowCounters = {};

function getRowCount(tbodyId) {
  if (!rowCounters[tbodyId]) {
    rowCounters[tbodyId] = document.getElementById(tbodyId).querySelectorAll('tr').length;
  }
  return rowCounters[tbodyId];
}

function addRow(tbodyId, tplId) {
  var tbody = document.getElementById(tbodyId);
  var tpl   = document.getElementById(tplId);
  if (!tbody || !tpl) return;

  var idx  = (rowCounters[tbodyId] = getRowCount(tbodyId) + 1);
  var n    = tbody.querySelectorAll('tr').length + 1;
  var html = tpl.innerHTML
               .replace(/__I__/g, 'r' + Date.now() + '_' + idx)
               .replace(/__N__/g, n);
  tbody.insertAdjacentHTML('beforeend', html);
  renumberRows(tbody);
}

function renumberRows(tbody) {
  var rows = tbody.querySelectorAll('tr');
  rows.forEach(function(tr, i) {
    var nd = tr.querySelector('.no-num');
    if (nd) nd.textContent = i + 1;
  });
}

function initAddRowButtons() {
  document.querySelectorAll('.btn-add-row').forEach(function(btn) {
    btn.addEventListener('click', function() {
      addRow(this.dataset.target, this.dataset.tpl);
    });
  });
}

function initDelRowButtons() {
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('del-row')) {
      var tr = e.target.closest('tr');
      if (tr) {
        var tbody = tr.closest('tbody');
        tr.remove();
        if (tbody) renumberRows(tbody);
      }
    }
  });
}

// ─── Sync hidden name fields for selects ──────────────────────────────────────
window.syncUomName = function(sel, idx) {
  var unit = UNITS.find(function(u) { return u.id == sel.value; });
  var hid  = document.getElementById('uomName' + idx);
  if (hid) hid.value = unit ? unit.name : '';
};

window.syncSupplierName = function(sel, idx) {
  var sup = SUPPLIERS.find(function(s) { return s.id == sel.value; });
  var hid = document.getElementById('supName' + idx);
  if (hid) hid.value = sup ? sup.name : '';
};

// ─── Status toggle badge ──────────────────────────────────────────────────────
function initStatusBadge() {
  var badge  = document.getElementById('statusBadge');
  var lbl    = document.getElementById('statusBadgeLbl');
  var active = document.getElementById('fieldIsActive');
  if (!badge) return;

  function refresh() {
    var on = active && active.checked;
    badge.className = 'status-badge ' + (on ? 'enabled' : 'disabled');
    lbl.textContent  = on ? 'Enabled' : 'Disabled';
  }

  badge.addEventListener('click', function() {
    if (active) { active.checked = !active.checked; refresh(); markDirty(); }
  });
  if (active) active.addEventListener('change', refresh);
  refresh();
}

// ─── Header title sync ────────────────────────────────────────────────────────
function initNameSync() {
  var nameInput = document.getElementById('fieldName');
  if (!nameInput) return;
  nameInput.addEventListener('input', function() {
    var n = this.value || (IS_NEW ? 'New Product' : '');
    document.getElementById('hdr-item-name').textContent  = n;
    document.getElementById('hdr-title-display').textContent = n;
    document.getElementById('sbItemName').textContent     = n || '(unsaved)';
  });
}

// ─── Image upload ─────────────────────────────────────────────────────────────
function initImageUpload() {
  var zone  = document.getElementById('imgDropZone');
  var input = document.getElementById('img-input');
  var prev  = document.getElementById('imgPreview');
  var ph    = document.getElementById('imgPlaceholder');
  var fld   = document.getElementById('fieldImage');
  var clr   = document.getElementById('imgClearBtn');
  var prog  = document.getElementById('imgUploadProgress');
  if (!zone) return;

  zone.addEventListener('click', function() { input.click(); });
  input.addEventListener('change', function() {
    if (!this.files || !this.files[0]) return;
    var file = this.files[0];

    // Show local preview first
    var reader = new FileReader();
    reader.onload = function(e) {
      prev.src   = e.target.result;
      prev.style.display = 'block';
      if (ph) ph.style.display = 'none';
      if (clr) clr.style.display = 'inline';
    };
    reader.readAsDataURL(file);

    // Upload
    if (prog) prog.style.display = 'block';
    var fd = new FormData();
    fd.append('image', file);
    fd.append(CSRF_NAME, getCsrfToken());
    fetch(IMG_URL, { method:'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (prog) prog.style.display = 'none';
        if (d.success) {
          fld.value = d.path;
          prev.src  = d.url;
          markDirty();
        } else {
          showAlert('Image upload failed: ' + (d.error || 'Unknown error'), 'error');
        }
      })
      .catch(function() {
        if (prog) prog.style.display = 'none';
        showAlert('Image upload failed — server error.', 'error');
      });
  });

  if (clr) {
    clr.addEventListener('click', function(e) {
      e.stopPropagation();
      fld.value = '';
      prev.src  = '';
      prev.style.display = 'none';
      if (ph) ph.style.display = 'flex';
      this.style.display = 'none';
      markDirty();
    });
  }
}

// ─── Collect form data (all fields + child tables) ─────────────────────────────
function collectFormData() {
  var mainForm = document.getElementById('mainForm');
  var fd = new FormData(mainForm);

  // Walk every input/select/textarea in the page (not just the hidden form)
  var allInputs = document.querySelectorAll(
    '.ifrm-wrap input:not([type=file]), .ifrm-wrap select, .ifrm-wrap textarea'
  );
  allInputs.forEach(function(el) {
    if (!el.name) return;
    if (el.type === 'checkbox') {
      fd.set(el.name, el.checked ? '1' : '0');
    } else {
      fd.set(el.name, el.value);
    }
  });

  // Hidden form CSRF
  fd.set(CSRF_NAME, getCsrfToken());

  return fd;
}

function getCsrfToken() {
  var el = document.querySelector('[name="' + CSRF_NAME + '"]');
  return el ? el.value : '';
}

// ─── Validation ───────────────────────────────────────────────────────────────
function validate() {
  var name = document.getElementById('fieldName');
  if (!name || !name.value.trim()) {
    showAlert('Item Name is required.', 'error');
    name && name.focus();
    switchToTab('details');
    return false;
  }
  var price = document.getElementById('fieldSellingPrice');
  if (price && parseFloat(price.value) < 0) {
    showAlert('Selling Price cannot be negative.', 'error');
    switchToTab('details');
    return false;
  }
  return true;
}

function switchToTab(tabName) {
  document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
  document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
  var btn = document.querySelector('[data-tab="' + tabName + '"]');
  var pnl = document.getElementById('tab-' + tabName);
  if (btn) btn.classList.add('active');
  if (pnl) pnl.classList.add('active');
}

// ─── Save (AJAX) ──────────────────────────────────────────────────────────────
var isSaving = false;
var isDirty  = false;

function markDirty() { isDirty = true; }

function initFormChange() {
  document.querySelectorAll('.ifrm-wrap input, .ifrm-wrap select, .ifrm-wrap textarea').forEach(function(el) {
    el.addEventListener('change', markDirty);
    el.addEventListener('input', markDirty);
  });
  // Delegated for dynamic rows
  document.addEventListener('change', function(e) {
    if (e.target.closest('.ifrm-wrap')) markDirty();
  });
}

function saveItem(isDraft) {
  if (isSaving) return;
  if (!validate()) return;

  isSaving = true;
  var btn = document.getElementById(isDraft ? 'btnDraft' : 'btnSave');
  var origText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';

  var fd = collectFormData();
  if (isDraft) fd.set('_draft', '1');

  // Refresh CSRF
  fetch(SAVE_URL, { method:'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      isSaving = false;
      btn.disabled = false;
      btn.innerHTML = origText;

      if (data.success) {
        isDirty = false;
        clearDraft();
        showAlert(data.message || 'Saved successfully.', 'success');
        document.getElementById('sbItemCode').textContent = document.getElementById('fieldItemCode').value;
        if (IS_NEW && data.edit_url) {
          setTimeout(function() { window.location.href = data.edit_url; }, 900);
        }
      } else {
        showAlert(data.error || 'Save failed. Please try again.', 'error');
      }
    })
    .catch(function(err) {
      isSaving = false;
      btn.disabled = false;
      btn.innerHTML = origText;
      showAlert('Network error — save failed.', 'error');
    });
}

// ─── Draft: auto-save to sessionStorage ───────────────────────────────────────
function saveDraft() {
  if (!isDirty) return;
  try {
    var fields = {};
    document.querySelectorAll('.ifrm-wrap input:not([type=file]):not([type=checkbox]), .ifrm-wrap select, .ifrm-wrap textarea').forEach(function(el) {
      if (el.name) fields[el.name] = el.value;
    });
    document.querySelectorAll('.ifrm-wrap input[type=checkbox]').forEach(function(el) {
      if (el.name) fields[el.name] = el.checked ? '1' : '0';
    });
    sessionStorage.setItem(DRAFT_KEY, JSON.stringify(fields));
  } catch(e) {}
}

function clearDraft() {
  try { sessionStorage.removeItem(DRAFT_KEY); } catch(e) {}
}

function restoreDraftIfNew() {
  if (!IS_NEW) return;
  try {
    var raw = sessionStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    var fields = JSON.parse(raw);
    Object.keys(fields).forEach(function(name) {
      var el = document.querySelector('[name="' + name + '"]');
      if (!el) return;
      if (el.type === 'checkbox') el.checked = fields[name] === '1';
      else el.value = fields[name];
    });
    isDirty = false;
    showAlert('Draft restored from previous session. <a href="#" onclick="clearDraft();document.getElementById(\'ifrmAlert\').style.display=\'none\';return false">Dismiss</a>', 'success');
  } catch(e) {}
}

// ─── Alert banner ─────────────────────────────────────────────────────────────
function showAlert(msg, type) {
  var el = document.getElementById('ifrmAlert');
  el.className = 'ifrm-alert ' + (type || 'success');
  el.innerHTML = '<i class="fa fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + '"></i> ' + msg;
  el.style.display = 'flex';
  if (type !== 'error') setTimeout(function() { el.style.display = 'none'; }, 5000);
  el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}

// ─── URL-hash tab routing ──────────────────────────────────────────────────────
function initTabsWithHash() {
  var btns = document.querySelectorAll('.tab-btn');
  btns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var target = this.dataset.tab;
      btns.forEach(function(b) { b.classList.remove('active'); });
      document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
      this.classList.add('active');
      var panel = document.getElementById('tab-' + target);
      if (panel) panel.classList.add('active');
      history.replaceState(null, '', '#tab-' + target);
    });
  });
  // Restore from hash
  var hash = window.location.hash;
  if (hash && hash.startsWith('#tab-')) {
    var btn = document.querySelector('[data-tab="' + hash.slice(5) + '"]');
    if (btn) btn.click();
  }
}

// ─── Serial / Batch conditional reveal ────────────────────────────────────────
var sbEnabled = false;

function initSerialBatch() {
  // Check global setting
  fetch('<?php echo admin_url("pos_system/inv_serial_batch_settings"); ?>', { method: 'GET' })
    .then(function(r) { return r.json(); })
    .then(function(s) {
      sbEnabled = (s['enable_serial_and_batch_no_for_item'] === '1');
      applySerialBatchLock();
    })
    .catch(function() { applySerialBatchLock(); });

  var batchChk  = document.getElementById('chkBatchNo');
  var serialChk = document.getElementById('chkSerialNo');
  if (!batchChk || !serialChk) return;

  batchChk.addEventListener('change', function() {
    if (!sbEnabled) { this.checked = false; showSbLockedMsg(); return; }
    var panel = document.getElementById('batchPanel');
    if (panel) { panel.classList.toggle('visible', this.checked); }
    markDirty();
  });
  serialChk.addEventListener('change', function() {
    if (!sbEnabled) { this.checked = false; showSbLockedMsg(); return; }
    var panel = document.getElementById('serialPanel');
    if (panel) { panel.classList.toggle('visible', this.checked); }
    markDirty();
  });
}

function applySerialBatchLock() {
  var batchChk  = document.getElementById('chkBatchNo');
  var serialChk = document.getElementById('chkSerialNo');
  var msg       = document.getElementById('serialBatchGlobalMsg');
  if (!batchChk) return;
  if (!sbEnabled) {
    batchChk.disabled  = true;
    serialChk.disabled = true;
    if (msg) msg.style.display = 'block';
  } else {
    batchChk.disabled  = false;
    serialChk.disabled = false;
    if (msg) msg.style.display = 'none';
  }
}

function showSbLockedMsg() {
  var msg = document.getElementById('serialBatchGlobalMsg');
  if (msg) { msg.style.display = 'block'; msg.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
}

// ─── Item code uniqueness check ────────────────────────────────────────────────
function initCodeCheck() {
  var fld = document.getElementById('fieldItemCode');
  if (!fld) return;
  var status = document.getElementById('codeStatus');
  var timer;
  fld.addEventListener('input', function() {
    clearTimeout(timer);
    var val = this.value.trim();
    if (!val) { if (status) status.style.display = 'none'; return; }
    timer = setTimeout(function() {
      var url = '<?php echo admin_url("pos_system/inv_item_check_code"); ?>?code=' + encodeURIComponent(val) + '&exclude_id=' + ITEM_ID;
      if (status) { status.className = 'code-checking'; status.textContent = '⏳ Checking…'; status.style.display = 'block'; }
      fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(d) {
          if (!status) return;
          status.style.display = 'block';
          if (d.available) {
            status.className = 'code-checking code-ok';
            status.textContent = '✓ Code is available';
          } else {
            status.className = 'code-checking code-fail';
            status.textContent = '✗ Code already in use';
          }
        })
        .catch(function() { if (status) status.style.display = 'none'; });
    }, 500);
  });
}

// ─── Status badge — AJAX toggle ───────────────────────────────────────────────
function initStatusBadge() {
  var badge  = document.getElementById('statusBadge');
  var lbl    = document.getElementById('statusBadgeLbl');
  var active = document.getElementById('fieldIsActive');
  if (!badge) return;

  function refresh(isActive) {
    badge.className = 'status-badge ' + (isActive ? 'enabled' : 'disabled');
    lbl.textContent  = isActive ? 'Enabled' : 'Disabled';
    if (active) active.checked = !!isActive;
  }

  badge.addEventListener('click', function() {
    if (IS_NEW || !ITEM_ID) {
      // For new items just toggle locally
      var on = active && active.checked;
      refresh(!on); markDirty(); return;
    }
    // AJAX toggle for existing items
    var fd = new FormData();
    fd.append('id', ITEM_ID);
    fd.append(CSRF_NAME, getCsrfToken());
    fetch('<?php echo admin_url("pos_system/inv_item_toggle_status"); ?>', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (d.success) {
          refresh(d.is_active);
          showAlert(d.is_active ? 'Item enabled.' : 'Item disabled.', 'success');
        }
      });
  });

  if (active) active.addEventListener('change', function() { refresh(this.checked); });
  refresh(active ? active.checked : true);
}

// ─── Set primary barcode ───────────────────────────────────────────────────────
function initPrimaryBarcode() {
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.set-primary-barcode');
    if (!btn) return;
    // Clear all stars in the table
    document.querySelectorAll('.set-primary-barcode').forEach(function(b) {
      b.classList.remove('active');
      b.style.color = '#ccc';
    });
    btn.classList.add('active');
    btn.style.color = '#f59e0b';
    // If saved row has id, call AJAX
    var rowId = btn.dataset.rowId;
    if (rowId && parseInt(rowId) > 0 && ITEM_ID) {
      var fd = new FormData();
      fd.append('product_id', ITEM_ID);
      fd.append('barcode_id', rowId);
      fd.append(CSRF_NAME, getCsrfToken());
      fetch('<?php echo admin_url("pos_system/inv_item_set_primary_barcode"); ?>', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.success) showAlert('Primary barcode set.', 'success'); });
    }
    markDirty();
  });
}

// ─── Duplicate ────────────────────────────────────────────────────────────────
function initDuplicate() {
  var btn = document.getElementById('btnDuplicate');
  if (!btn) return;
  btn.addEventListener('click', function() {
    if (!confirm('Duplicate this item? A new copy will be created and you will be redirected to it.')) return;
    btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    var fd = new FormData();
    fd.append('id', ITEM_ID);
    fd.append(CSRF_NAME, getCsrfToken());
    fetch('<?php echo admin_url("pos_system/inv_item_duplicate"); ?>', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-copy"></i> Duplicate';
        if (d.success) {
          showAlert('Item duplicated! Redirecting…', 'success');
          setTimeout(function() { window.location.href = d.edit_url; }, 800);
        } else {
          showAlert(d.error || 'Duplicate failed.', 'error');
        }
      })
      .catch(function() {
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-copy"></i> Duplicate';
        showAlert('Network error.', 'error');
      });
  });
}

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  initTabsWithHash();
  initCollapsible();
  initAddRowButtons();
  initDelRowButtons();
  initStatusBadge();
  initNameSync();
  initImageUpload();
  initFormChange();
  initSerialBatch();
  initCodeCheck();
  initPrimaryBarcode();
  initDuplicate();
  initVariations();
  restoreDraftIfNew();

  document.getElementById('btnSave').addEventListener('click',  function() { saveItem(false); });
  document.getElementById('btnDraft').addEventListener('click', function() { saveItem(true); });

  setInterval(saveDraft, 30000);

  window.addEventListener('beforeunload', function(e) {
    if (isDirty && !isSaving) { e.preventDefault(); e.returnValue = ''; }
  });

  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveItem(false); }
  });
});

})();

// ─── Variations tab ───────────────────────────────────────────────────────────
function initVariations() {
  var PARENT_PRICE = parseFloat(document.querySelector('[name="selling_price"]')?.value) || 0;
  var PARENT_COST  = parseFloat(document.querySelector('[name="cost_price"]')?.value)    || 0;
  var SAVE_URL     = '<?php echo admin_url("pos_system/inv_item_variations_save"); ?>';

  // Show/hide builder when checkbox toggled
  var chk = document.getElementById('fieldHasVariants');
  var builder = document.getElementById('varBuilder');
  if (chk) {
    chk.addEventListener('change', function() {
      if (builder) builder.style.display = this.checked ? 'block' : 'none';
      markDirty();
    });
  }

  // Chip toggle
  document.querySelectorAll('.var-attr-chip').forEach(function(chip) {
    chip.addEventListener('click', function() {
      this.classList.toggle('selected');
      varUpdateCount();
    });
  });

  // Delete row
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.var-del-btn');
    if (!btn) return;
    btn.closest('tr').remove();
    varUpdateCount();
    markDirty();
  });

  // Generate variants from selected chips
  var genBtn = document.getElementById('btnGenVariants');
  if (genBtn) genBtn.addEventListener('click', function() {
    var attrs = varGetSelectedAttrs();
    if (!attrs.length) { showAlert('Select at least one attribute value first.', 'error'); return; }
    var combos = varCartesian(attrs);
    var tbody = document.getElementById('varTbody');
    var noRow = document.getElementById('varNoRows');
    if (noRow) noRow.remove();

    // Build map of existing rows by data-color+size+style+model to preserve edits
    var existingMap = {};
    tbody.querySelectorAll('tr.var-row').forEach(function(r) {
      var key = [r.dataset.color||'', r.dataset.size||'', r.dataset.style||'', r.dataset.model||''].join('|');
      existingMap[key] = r;
    });

    // Clear existing then re-add (preserve matches)
    tbody.innerHTML = '';
    combos.forEach(function(combo, i) {
      var key = [combo.color_id||'', combo.size_id||'', combo.style_id||'', combo.model_id||''].join('|');
      if (existingMap[key]) {
        tbody.appendChild(existingMap[key]);
        return;
      }
      var name = combo.labels.join(' / ');
      var sku  = '';
      if (combo.labels.length) {
        var base = (document.querySelector('[name="item_code"]')?.value || document.querySelector('[name="sku"]')?.value || 'VAR').toUpperCase().replace(/\s+/g,'-');
        sku = base + '-' + combo.labels.map(function(l){ return l.substring(0,3).toUpperCase(); }).join('-');
      }
      tbody.appendChild(varBuildRow(i, { name:name, sku:sku, price:'', cost:'', active:true,
                                         color_id: combo.color_id||'', size_id: combo.size_id||'',
                                         style_id: combo.style_id||'', model_id: combo.model_id||'' }));
    });
    varUpdateCount();
    markDirty();
  });

  // Save variations
  var saveBtn = document.getElementById('btnSaveVariants');
  if (saveBtn) saveBtn.addEventListener('click', function() {
    if (!ITEM_ID) { showAlert('Save the item first before saving variations.', 'error'); return; }
    var variants = varCollectRows();
    var attrs    = varGetSelectedAttrsForPost();
    var fd = new FormData();
    fd.append('product_id',  ITEM_ID);
    fd.append('variants',    JSON.stringify(variants));
    fd.append(CSRF_NAME, getCsrfToken());
    Object.keys(attrs).forEach(function(k) {
      attrs[k].forEach(function(v) { fd.append('attr_'+k+'[]', v); });
    });
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';
    fetch(SAVE_URL, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-save"></i> Save Variations';
        if (d.success) {
          showAlert(d.message || 'Variations saved.', 'success');
          // Update tab badge
          var badge = document.querySelector('#varTabBtn span');
          var cnt   = variants.length;
          if (cnt > 0) {
            if (!badge) {
              badge = document.createElement('span');
              badge.style.cssText = 'background:#16a34a;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px';
              document.getElementById('varTabBtn').appendChild(badge);
            }
            badge.textContent = cnt;
          } else if (badge) {
            badge.remove();
          }
        } else {
          showAlert(d.error || 'Save failed.', 'error');
        }
      })
      .catch(function() {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-save"></i> Save Variations';
        showAlert('Network error.', 'error');
      });
  });

  varUpdateCount();
}

function varGetSelectedAttrs() {
  var groups = {};
  document.querySelectorAll('.var-attr-chip.selected').forEach(function(chip) {
    var at = chip.dataset.attr;
    if (!groups[at]) groups[at] = [];
    groups[at].push({ id: chip.dataset.id, name: chip.dataset.name, type: at });
  });
  return Object.values(groups);
}

function varGetSelectedAttrsForPost() {
  var out = {};
  document.querySelectorAll('.var-attr-chip.selected').forEach(function(chip) {
    var at = chip.dataset.attr;
    if (!out[at]) out[at] = [];
    out[at].push(chip.dataset.id);
  });
  return out;
}

function varCartesian(groups) {
  if (!groups.length) return [];
  return groups.reduce(function(acc, group) {
    var result = [];
    acc.forEach(function(existing) {
      group.forEach(function(item) {
        var combo = Object.assign({}, existing);
        combo[item.type+'_id'] = item.id;
        combo.labels = (combo.labels || []).concat([item.name]);
        result.push(combo);
      });
    });
    return result;
  }, [{}]);
}

function varBuildRow(i, data) {
  var tr = document.createElement('tr');
  tr.className = 'var-row';
  tr.dataset.color = data.color_id || '';
  tr.dataset.size  = data.size_id  || '';
  tr.dataset.style = data.style_id || '';
  tr.dataset.model = data.model_id || '';
  tr.innerHTML = [
    '<td style="text-align:center;color:#aaa;font-size:11px">'+(i+1)+'</td>',
    '<td><input type="text" class="var-tbl-input var-name" value="'+escAttr(data.name||'')+'" placeholder="e.g. Red / XL"></td>',
    '<td><input type="text" class="var-tbl-input var-sku" value="'+escAttr(data.sku||'')+'" placeholder="SKU"></td>',
    '<td><input type="text" class="var-tbl-input var-barcode" value="" placeholder="Barcode"></td>',
    '<td><input type="number" class="var-tbl-input var-price" value="'+escAttr(data.price||'')+'" placeholder="—" step="any" min="0"></td>',
    '<td><input type="number" class="var-tbl-input var-cost" value="'+escAttr(data.cost||'')+'" placeholder="—" step="any" min="0"></td>',
    '<td style="text-align:center"><label style="cursor:pointer"><input type="checkbox" class="var-active"'+(data.active?' checked':'')+' style="width:15px;height:15px;accent-color:#16a34a"></label></td>',
    '<td><button type="button" class="btn-inv-icon danger var-del-btn" title="Remove"><i class="fa fa-times"></i></button></td>',
  ].join('');
  return tr;
}

function varCollectRows() {
  var rows = [];
  document.querySelectorAll('#varTbody tr.var-row').forEach(function(tr) {
    rows.push({
      id:             tr.dataset.rowid || 0,
      name:           tr.querySelector('.var-name')?.value    || '',
      sku:            tr.querySelector('.var-sku')?.value     || '',
      barcode:        tr.querySelector('.var-barcode')?.value || '',
      price_override: tr.querySelector('.var-price')?.value  || '',
      cost_price:     tr.querySelector('.var-cost')?.value   || '',
      is_active:      tr.querySelector('.var-active')?.checked ? 1 : 0,
      color_id:       tr.dataset.color || '',
      size_id:        tr.dataset.size  || '',
      style_id:       tr.dataset.style || '',
      model_id:       tr.dataset.model || '',
    });
  });
  return rows;
}

function varUpdateCount() {
  var cnt = document.querySelectorAll('#varTbody tr.var-row').length;
  var el  = document.getElementById('varCount');
  if (el) el.textContent = cnt ? cnt+' variant(s) defined' : '';
  var noRow = document.getElementById('varNoRows');
  if (!noRow && cnt === 0) {
    var tbody = document.getElementById('varTbody');
    if (tbody) tbody.innerHTML = '<tr id="varNoRows"><td colspan="8" style="text-align:center;padding:24px;color:#94a3b8"><i class="fa fa-layer-group" style="font-size:22px;display:block;margin-bottom:6px;opacity:.4"></i>No variants yet. Select attributes above and click Generate.</td></tr>';
  }
}

function escAttr(s) { return String(s).replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
</script>

<?php init_tail(); ?>
