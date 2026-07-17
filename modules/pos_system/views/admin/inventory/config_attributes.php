<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'config_attributes',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content">

<style>
/* Tab bar */
.attr-tabs { display:flex; gap:2px; flex-wrap:wrap; margin-bottom:0; background:#1a2e22; padding:6px 10px; border-radius:8px 8px 0 0; }
.attr-tab-btn { padding:5px 13px; border-radius:5px; font-size:11.5px; font-weight:600; cursor:pointer; border:none; background:transparent; color:#8fb89e; transition:all .12s; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.attr-tab-btn.active { background:#2d5c3e; color:#c8f0d7; }
.attr-tab-btn:hover:not(.active) { background:#243d2e; color:#c8f0d7; text-decoration:none; }
.attr-tab-btn i { font-size:11px; }

/* Section body */
.attr-section { display:none; }
.attr-section.active { display:block; }

/* Inline add-row form above table */
.attr-addbar { display:flex; align-items:center; gap:5px; flex-wrap:wrap; padding:7px 10px; background:#f0fdf4; border-bottom:2px solid #b9e4ca; }
.attr-addbar .aa-fld { display:flex; flex-direction:column; gap:2px; }
.attr-addbar .aa-lbl { font-size:9px; font-weight:700; color:#56665e; text-transform:uppercase; letter-spacing:.4px; }
.attr-addbar .aa-inp { height:26px; border:1px solid #c8dfd0; border-radius:4px; padding:0 7px; font-size:12px; color:#1a2520; background:#fff; outline:none; }
.attr-addbar .aa-inp:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.12); }
.attr-addbar .aa-sel { height:26px; border:1px solid #c8dfd0; border-radius:4px; padding:0 5px; font-size:12px; color:#1a2520; background:#fff; outline:none; }
.attr-addbar .aa-sep { width:1px; height:24px; background:#d4e8db; flex-shrink:0; margin:0 3px; }
.aa-save-btn { height:26px; padding:0 12px; background:#16a34a; color:#fff; border:none; border-radius:4px; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
.aa-save-btn:hover { background:#15803d; }
.aa-clr-btn  { height:26px; padding:0 10px; background:#e2ece6; color:#3d5e47; border:none; border-radius:4px; font-size:11.5px; font-weight:600; cursor:pointer; }
.aa-clr-btn:hover { background:#d0e8d8; }
.aa-edit-mode { background:#fffbeb; border-bottom-color:#fbbf24; }
.aa-edit-lbl { font-size:11px; font-weight:700; color:#b45309; margin-right:4px; }

/* XLS cell edits for this page */
.xls-cell { font-size:11.5px; }
.xls-th   { font-size:10px; }

/* Toggle cell */
.aa-toggle { position:relative; display:inline-block; width:34px; height:18px; }
.aa-toggle input { opacity:0; width:0; height:0; position:absolute; }
.aa-toggle-sl { position:absolute; inset:0; background:#dde4ef; border-radius:18px; cursor:pointer; transition:.15s; }
.aa-toggle-sl:before { content:''; position:absolute; width:12px; height:12px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.15s; box-shadow:0 1px 2px rgba(0,0,0,.2); }
.aa-toggle input:checked + .aa-toggle-sl { background:#16a34a; }
.aa-toggle input:checked + .aa-toggle-sl:before { transform:translateX(16px); }

/* Color swatch */
.aa-swatch { width:14px; height:14px; border-radius:3px; border:1px solid rgba(0,0,0,.15); display:inline-block; vertical-align:middle; margin-right:4px; }
</style>

<!-- Tab bar -->
<div class="attr-tabs">
  <a href="#" class="attr-tab-btn active" data-attr="commodity_types"><i class="fa fa-tags"></i> Commodity Types</a>
  <a href="#" class="attr-tab-btn" data-attr="commodity_groups"><i class="fa fa-layer-group"></i> Commodity Groups</a>
  <a href="#" class="attr-tab-btn" data-attr="sub_groups"><i class="fa fa-sitemap"></i> Sub Groups</a>
  <a href="#" class="attr-tab-btn" data-attr="units"><i class="fa fa-ruler"></i> Units</a>
  <a href="#" class="attr-tab-btn" data-attr="colors"><i class="fa fa-palette"></i> Colors</a>
  <a href="#" class="attr-tab-btn" data-attr="models"><i class="fa fa-microchip"></i> Models</a>
  <a href="#" class="attr-tab-btn" data-attr="sizes"><i class="fa fa-text-height"></i> Sizes</a>
  <a href="#" class="attr-tab-btn" data-attr="styles"><i class="fa fa-tshirt"></i> Styles</a>
</div>

<!-- Wrapper card (no top rounding — tabs sit on top) -->
<div class="inv-card" style="border-radius:0 0 8px 8px;border-top:none">

<!-- ══ COMMODITY TYPES ══════════════════════════════════════════════════════ -->
<div class="attr-section active" id="attr-commodity_types">
  <div class="attr-addbar" id="ct-addbar">
    <span class="aa-edit-lbl" id="ct-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="ct-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="ct-code" class="aa-inp" placeholder="CT001" style="width:80px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="ct-name" class="aa-inp" placeholder="Electronics & ICT" style="width:220px"></div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="ct-note" class="aa-inp" placeholder="Optional note" style="width:180px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('commodity_types')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('ct')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:200px">Name</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:160px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="ct-body"><tr><td colspan="6"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ COMMODITY GROUPS ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-commodity_groups">
  <div class="attr-addbar" id="cg-addbar">
    <span class="aa-edit-lbl" id="cg-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="cg-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="cg-code" class="aa-inp" placeholder="GRP001" style="width:80px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="cg-name" class="aa-inp" placeholder="Phones & Tablets" style="width:220px"></div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="cg-note" class="aa-inp" placeholder="Optional" style="width:160px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('commodity_groups')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('cg')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:200px">Name</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:160px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="cg-body"><tr><td colspan="6"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ SUB GROUPS ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-sub_groups">
  <div class="attr-addbar" id="sg-addbar">
    <span class="aa-edit-lbl" id="sg-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="sg-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="sg-code" class="aa-inp" placeholder="SGP001" style="width:80px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="sg-name" class="aa-inp" placeholder="Android Smartphones" style="width:200px"></div>
    <div class="aa-fld">
      <span class="aa-lbl">Parent Group</span>
      <select id="sg-group_id" class="aa-sel" style="width:170px">
        <option value="">— None —</option>
        <?php foreach ($commodity_groups as $cg): ?>
        <option value="<?php echo $cg['id']; ?>"><?php echo htmlspecialchars($cg['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('sub_groups')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('sg')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:200px">Name</th>
        <th class="xls-th" style="min-width:150px">Parent Group</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="sg-body"><tr><td colspan="6"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ UNITS ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-units">
  <div class="attr-addbar" id="un-addbar">
    <span class="aa-edit-lbl" id="un-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="un-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="un-code" class="aa-inp" placeholder="UN001" style="width:75px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="un-name" class="aa-inp" placeholder="Kilogram" style="width:160px"></div>
    <div class="aa-fld"><span class="aa-lbl">Symbol</span><input type="text" id="un-symbol" class="aa-inp" placeholder="kg" style="width:60px"></div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="un-note" class="aa-inp" placeholder="Optional" style="width:140px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('units')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('un')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:160px">Name</th>
        <th class="xls-th" style="width:70px">Symbol</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:160px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="un-body"><tr><td colspan="7"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ COLORS ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-colors">
  <div class="attr-addbar" id="col-addbar">
    <span class="aa-edit-lbl" id="col-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="col-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="col-code" class="aa-inp" placeholder="COL001" style="width:75px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="col-name" class="aa-inp" placeholder="Black" style="width:140px"></div>
    <div class="aa-fld">
      <span class="aa-lbl">Hex Color</span>
      <div style="display:flex;align-items:center;gap:4px">
        <input type="color" id="col-hex-picker" value="#000000" style="width:26px;height:26px;border:1px solid #c8dfd0;border-radius:4px;padding:1px;cursor:pointer">
        <input type="text" id="col-hex" class="aa-inp" placeholder="#000000" style="width:80px">
      </div>
    </div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="col-note" class="aa-inp" placeholder="Optional" style="width:120px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('colors')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('col')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:160px">Color</th>
        <th class="xls-th" style="width:80px">Hex</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:140px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="col-body"><tr><td colspan="7"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ MODELS ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-models">
  <div class="attr-addbar" id="mod-addbar">
    <span class="aa-edit-lbl" id="mod-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="mod-id">
    <div class="aa-fld"><span class="aa-lbl">Model Name</span><input type="text" id="mod-name" class="aa-inp" placeholder="Galaxy S24 Ultra" style="width:230px"></div>
    <div class="aa-fld">
      <span class="aa-lbl">Brand</span>
      <select id="mod-brand_id" class="aa-sel" style="width:160px">
        <option value="">— No Brand —</option>
        <?php foreach ($brands as $b): ?>
        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('models')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('mod')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:200px">Model Name</th>
        <th class="xls-th" style="min-width:150px">Brand</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="mod-body"><tr><td colspan="4"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ SIZES ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-sizes">
  <div class="attr-addbar" id="sz-addbar">
    <span class="aa-edit-lbl" id="sz-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="sz-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="sz-code" class="aa-inp" placeholder="SZ001" style="width:75px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="sz-name" class="aa-inp" placeholder="XS – Extra Small" style="width:180px"></div>
    <div class="aa-fld"><span class="aa-lbl">Symbol</span><input type="text" id="sz-symbol" class="aa-inp" placeholder="XS" style="width:60px"></div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="sz-note" class="aa-inp" placeholder="Optional" style="width:120px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('sizes')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('sz')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:170px">Name</th>
        <th class="xls-th" style="width:70px">Symbol</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:140px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="sz-body"><tr><td colspan="7"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

<!-- ══ STYLES ══════════════════════════════════════════════════════ -->
<div class="attr-section" id="attr-styles">
  <div class="attr-addbar" id="sty-addbar">
    <span class="aa-edit-lbl" id="sty-edit-lbl" style="display:none"><i class="fa fa-pen"></i> Editing</span>
    <input type="hidden" id="sty-id">
    <div class="aa-fld"><span class="aa-lbl">Code</span><input type="text" id="sty-code" class="aa-inp" placeholder="STY001" style="width:75px"></div>
    <div class="aa-fld"><span class="aa-lbl">Name</span><input type="text" id="sty-name" class="aa-inp" placeholder="Casual Wear" style="width:200px"></div>
    <div class="aa-fld"><span class="aa-lbl">Barcode</span><input type="text" id="sty-barcode" class="aa-inp" placeholder="Optional" style="width:130px"></div>
    <div class="aa-fld"><span class="aa-lbl">Note</span><input type="text" id="sty-note" class="aa-inp" placeholder="Optional" style="width:120px"></div>
    <div class="aa-sep"></div>
    <button class="aa-save-btn" onclick="attrSave('styles')"><i class="fa fa-check"></i> Save</button>
    <button class="aa-clr-btn"  onclick="attrClear('sty')">Clear</button>
  </div>
  <div class="xls-wrap">
    <table class="xls-table">
      <thead><tr>
        <th class="xls-th xls-col-rownum">#</th>
        <th class="xls-th" style="min-width:80px">Code</th>
        <th class="xls-th" style="min-width:180px">Name</th>
        <th class="xls-th" style="min-width:120px">Barcode</th>
        <th class="xls-th" style="width:70px">Display</th>
        <th class="xls-th" style="min-width:140px">Note</th>
        <th class="xls-th xls-col-actions">Actions</th>
      </tr></thead>
      <tbody id="sty-body"><tr><td colspan="7"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr></tbody>
    </table>
  </div>
</div>

</div><!-- /inv-card -->
</div><!-- /inv-content -->
</div>
</div>

<?php init_tail(); ?>
<script>
var AJAX_URL   = '<?php echo admin_url('pos_system/inv_ajax/attr'); ?>';
var SAVE_URL   = '<?php echo admin_url('pos_system/inv_save/attr'); ?>';
var DELETE_URL = '<?php echo admin_url('pos_system/inv_delete/attr'); ?>';
var _csrf_n    = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v    = '<?php echo $this->security->get_csrf_hash(); ?>';
var _cur = 'commodity_types';

/* ── Tab switch ── */
$('.attr-tab-btn').on('click', function(e) {
    e.preventDefault();
    var a = $(this).data('attr');
    _cur = a;
    $('.attr-tab-btn').removeClass('active');
    $(this).addClass('active');
    $('.attr-section').removeClass('active');
    $('#attr-' + a).addClass('active');
    attrLoad(a);
});

/* ── Prefix map ── */
function pfx(type) {
    return {commodity_types:'ct',commodity_groups:'cg',sub_groups:'sg',units:'un',colors:'col',models:'mod',sizes:'sz',styles:'sty'}[type]||type;
}

/* ── Load ── */
function attrLoad(type) {
    type = type || _cur;
    var p = pfx(type);
    var tbody = $('#' + p + '-body');
    tbody.html('<tr><td colspan="10"><div class="inv-empty" style="padding:12px"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i></div></td></tr>');
    $.getJSON(AJAX_URL, {type: type}, function(r) {
        if (!r.rows || !r.rows.length) {
            tbody.html('<tr><td colspan="10"><div class="inv-empty" style="padding:14px 10px"><i class="fa fa-inbox" style="color:#bcd9c8"></i><p style="font-size:11px;color:#94a3b8;margin:4px 0 0">No records. Add one above.</p></div></td></tr>');
            return;
        }
        var html = '';
        r.rows.forEach(function(row, i) {
            html += '<tr class="xls-row">';
            html += '<td class="xls-cell xls-col-rownum">' + (i+1) + '</td>';
            if (type === 'commodity_types') {
                html += td(row.commondity_code||'','#8a9bb0');
                html += tdB(row.commondity_name);
                html += tdToggle('commodity_types', row.commodity_type_id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('commodity_types', row.commodity_type_id, row);
            } else if (type === 'commodity_groups') {
                html += td(row.commodity_group_code||'','#8a9bb0');
                html += tdB(row.name);
                html += tdToggle('commodity_groups', row.id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('commodity_groups', row.id, row);
            } else if (type === 'sub_groups') {
                html += td(row.sub_group_code||'','#8a9bb0');
                html += tdB(row.sub_group_name);
                html += td(row.group_name||'—');
                html += tdToggle('sub_groups', row.id, row.display);
                html += tdAct('sub_groups', row.id, row);
            } else if (type === 'units') {
                html += td(row.unit_code||'','#8a9bb0');
                html += tdB(row.unit_name);
                html += '<td class="xls-cell"><span style="background:#dcfce7;color:#166534;border-radius:3px;padding:1px 6px;font-size:10px">'+(row.unit_symbol||'')+'</span></td>';
                html += tdToggle('units', row.unit_type_id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('units', row.unit_type_id, row);
            } else if (type === 'colors') {
                html += td(row.color_code||'','#8a9bb0');
                html += '<td class="xls-cell"><span class="aa-swatch" style="background:'+(row.color_hex||'#ccc')+'"></span>'+esc(row.color_name)+'</td>';
                html += '<td class="xls-cell" style="font-size:10px;font-family:monospace;color:#56665e">'+(row.color_hex||'')+'</td>';
                html += tdToggle('colors', row.color_id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('colors', row.color_id, row);
            } else if (type === 'models') {
                html += tdB(row.name);
                html += td(row.brand_name||'—');
                html += tdAct('models', row.id, row);
            } else if (type === 'sizes') {
                html += td(row.size_code||'','#8a9bb0');
                html += tdB(row.size_name);
                html += '<td class="xls-cell"><span style="background:#dcfce7;color:#166534;border-radius:3px;padding:1px 6px;font-size:10px">'+(row.size_symbol||'')+'</span></td>';
                html += tdToggle('sizes', row.size_type_id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('sizes', row.size_type_id, row);
            } else if (type === 'styles') {
                html += td(row.style_code||'','#8a9bb0');
                html += tdB(row.style_name);
                html += td(row.style_barcode||'','#8a9bb0');
                html += tdToggle('styles', row.style_type_id, row.display);
                html += td(row.note||'','#94a3b8');
                html += tdAct('styles', row.style_type_id, row);
            }
            html += '</tr>';
        });
        tbody.html(html);
    });
}

/* ── Cell helpers ── */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function td(v, color) { return '<td class="xls-cell"' + (color?' style="color:'+color+'"':'') + '>'+esc(v)+'</td>'; }
function tdB(v) { return '<td class="xls-cell"><strong style="font-weight:600">'+esc(v)+'</strong></td>'; }
function tdToggle(type, id, val) {
    return '<td class="xls-cell" style="text-align:center">'
        + '<label class="aa-toggle">'
        + '<input type="checkbox" onchange="attrToggleDisplay(\''+type+'\','+id+',this.checked)" '+(val==1?'checked':'')+'>'
        + '<span class="aa-toggle-sl"></span>'
        + '</label>'
        + '</td>';
}
function tdAct(type, id, row) {
    return '<td class="xls-cell xls-col-actions">'
        + '<button class="btn-inv-icon" onclick="attrEdit(\''+type+'\','+JSON.stringify(row).replace(/'/g,"&#39;")+')"><i class="fa fa-pen"></i></button> '
        + '<button class="btn-inv-icon danger" onclick="attrDelete(\''+type+'\','+id+')"><i class="fa fa-trash"></i></button>'
        + '</td>';
}

/* ── Save ── */
function attrSave(type) {
    var p = pfx(type);
    var d = {}; d[_csrf_n] = _csrf_v; d.type = type;
    var id = $('#'+p+'-id').val();
    if (id) d.id = id;
    $('#attr-'+type+' input[id^="'+p+'-"]:not([type="hidden"]), #attr-'+type+' select[id^="'+p+'-"]').each(function() {
        var key = this.id.replace(p+'-', '');
        d[key] = $(this).val();
    });
    $.post(SAVE_URL, d, function(r) {
        if (r.success) {
            alert_float('success', r.message || 'Saved.');
            attrClear(p);
            attrLoad(type);
        } else {
            alert_float('danger', r.error || 'Save failed.');
        }
    }, 'json');
}

/* ── Edit ── */
function attrEdit(type, row) {
    var p = pfx(type);
    // Show edit mode indicator
    $('#'+p+'-edit-lbl').show();
    $('#'+p+'-addbar').addClass('aa-edit-mode');
    if (type === 'commodity_types') {
        $('#ct-id').val(row.commodity_type_id); $('#ct-code').val(row.commondity_code); $('#ct-name').val(row.commondity_name); $('#ct-note').val(row.note||'');
    } else if (type === 'commodity_groups') {
        $('#cg-id').val(row.id); $('#cg-code').val(row.commodity_group_code); $('#cg-name').val(row.name); $('#cg-note').val(row.note||'');
    } else if (type === 'sub_groups') {
        $('#sg-id').val(row.id); $('#sg-code').val(row.sub_group_code); $('#sg-name').val(row.sub_group_name); $('#sg-group_id').val(row.group_id||'');
    } else if (type === 'units') {
        $('#un-id').val(row.unit_type_id); $('#un-code').val(row.unit_code); $('#un-name').val(row.unit_name); $('#un-symbol').val(row.unit_symbol||''); $('#un-note').val(row.note||'');
    } else if (type === 'colors') {
        $('#col-id').val(row.color_id); $('#col-code').val(row.color_code); $('#col-name').val(row.color_name); $('#col-hex').val(row.color_hex||''); $('#col-hex-picker').val(row.color_hex||'#000000'); $('#col-note').val(row.note||'');
    } else if (type === 'models') {
        $('#mod-id').val(row.id); $('#mod-name').val(row.name); $('#mod-brand_id').val(row.brand_id||'');
    } else if (type === 'sizes') {
        $('#sz-id').val(row.size_type_id); $('#sz-code').val(row.size_code); $('#sz-name').val(row.size_name); $('#sz-symbol').val(row.size_symbol||''); $('#sz-note').val(row.note||'');
    } else if (type === 'styles') {
        $('#sty-id').val(row.style_type_id); $('#sty-code').val(row.style_code); $('#sty-name').val(row.style_name); $('#sty-barcode').val(row.style_barcode||''); $('#sty-note').val(row.note||'');
    }
    // Scroll add bar into view
    document.getElementById(p+'-addbar').scrollIntoView({block:'nearest', behavior:'smooth'});
    $('#attr-'+type+' .aa-inp:first').focus();
}

/* ── Clear ── */
function attrClear(p) {
    $('#attr-'+_cur+' input[id^="'+p+'-"]:not([type="hidden"])').val('');
    $('#attr-'+_cur+' select[id^="'+p+'-"]').val('');
    $('#'+p+'-id').val('');
    $('#'+p+'-edit-lbl').hide();
    $('#'+p+'-addbar').removeClass('aa-edit-mode');
}

/* ── Delete ── */
function attrDelete(type, id) {
    if (!confirm('Delete this record? This cannot be undone.')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.sub_type = type;
    $.post(DELETE_URL+'/'+id, d, function(r) {
        if (r.success) { alert_float('success','Deleted.'); attrLoad(type); }
        else alert_float('danger', r.error||'Delete failed.');
    }, 'json');
}

/* ── Toggle display ── */
function attrToggleDisplay(type, id, val) {
    var d = {}; d[_csrf_n] = _csrf_v; d.type = type; d.id = id; d.display = val ? 1 : 0;
    $.post('<?php echo admin_url('pos_system/inv_save/attr_display'); ?>', d, function(r) {
        if (!r.success) { alert_float('danger','Toggle failed.'); attrLoad(type); }
    }, 'json');
}

/* ── Color picker sync ── */
$('#col-hex-picker').on('input', function() { $('#col-hex').val(this.value); });
$('#col-hex').on('input', function() { if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) $('#col-hex-picker').val(this.value); });

/* ── Enter key saves ── */
$(document).on('keydown', '.aa-inp, .aa-sel', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); attrSave(_cur); }
});

/* ── Column resize ── */
(function(){
    var th, sx, sw;
    $(document).on('mousedown','.xls-resize-handle',function(e){
        th=$(this).closest('.xls-th'); sx=e.pageX; sw=th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.ar',function(ev){th.css({'min-width':Math.max(40,sw+(ev.pageX-sx))+'px'});});
        $(document).on('mouseup.ar',function(){$('body').removeClass('xls-resizing');$(document).off('.ar');});
        e.preventDefault(); e.stopPropagation();
    });
})();

/* ── Init ── */
$(function() { attrLoad('commodity_types'); });
</script>
