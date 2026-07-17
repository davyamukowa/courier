<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'variations',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <!-- Toolbar -->
  <div class="inv-filter-bar" style="flex-wrap:wrap;gap:8px">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:0 0 auto">
      <i class="fa fa-layer-group"></i> Variations Report
    </h4>

    <!-- Search -->
    <div style="position:relative">
      <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px;pointer-events:none"></i>
      <input type="text" class="inv-input var-filter" data-filter="search"
             placeholder="Search item / SKU…" style="width:180px;padding-left:30px">
    </div>

    <!-- Color -->
    <?php if ($attr_colors): ?>
    <select class="inv-input var-filter" data-filter="color_id" style="width:140px;cursor:pointer">
      <option value="">All Colors</option>
      <?php foreach ($attr_colors as $c): ?>
      <option value="<?php echo $c['color_id']; ?>"><?php echo htmlspecialchars($c['color_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <!-- Size -->
    <?php if ($attr_sizes): ?>
    <select class="inv-input var-filter" data-filter="size_id" style="width:130px;cursor:pointer">
      <option value="">All Sizes</option>
      <?php foreach ($attr_sizes as $s): ?>
      <option value="<?php echo $s['size_type_id']; ?>"><?php echo htmlspecialchars($s['size_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <!-- Style -->
    <?php if ($attr_styles): ?>
    <select class="inv-input var-filter" data-filter="style_id" style="width:130px;cursor:pointer">
      <option value="">All Styles</option>
      <?php foreach ($attr_styles as $s): ?>
      <option value="<?php echo $s['style_type_id']; ?>"><?php echo htmlspecialchars($s['style_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <!-- Model -->
    <?php if ($attr_models): ?>
    <select class="inv-input var-filter" data-filter="model_id" style="width:140px;cursor:pointer">
      <option value="">All Models</option>
      <?php foreach ($attr_models as $m): ?>
      <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <!-- Status -->
    <select class="inv-input var-filter" data-filter="is_active" style="width:120px;cursor:pointer">
      <option value="">All Status</option>
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>

    <select id="var-per-page" class="inv-input" style="width:90px;cursor:pointer">
      <option value="25">25 rows</option>
      <option value="50">50 rows</option>
      <option value="100">100 rows</option>
      <option value="200">200 rows</option>
    </select>

    <button onclick="varExportCsv()" class="btn-inv-secondary" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-file-csv"></i> Export
    </button>

    <a href="<?php echo admin_url('pos_system/inventory/config_attributes'); ?>" class="btn-inv-secondary" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-cog"></i> Manage Attributes
    </a>
  </div>

  <!-- Table -->
  <div class="xls-wrap" id="xls-wrap">
    <table class="xls-table" id="xls-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum">#<span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:200px"><span class="xls-th-label">Parent Item</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:200px"><span class="xls-th-label">Variant Name</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:110px"><span class="xls-th-label">SKU</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:90px"><span class="xls-th-label">Color</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:80px"><span class="xls-th-label">Size</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:100px"><span class="xls-th-label">Style</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:120px"><span class="xls-th-label">Model</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" style="min-width:100px"><span class="xls-th-label">Price Override</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" style="min-width:100px"><span class="xls-th-label">Cost</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:80px"><span class="xls-th-label">Status</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-col-actions">ACTIONS<span class="xls-resize-handle"></span></th>
        </tr>
      </thead>
      <tbody id="var-list-body">
        <tr><td colspan="12"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="var-list-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="var-list-pages" class="inv-pagination"></div>
  </div>
</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<script>
var VAR_AJAX  = '<?php echo admin_url("pos_system/inv_ajax/variations"); ?>';
var _csrf_n   = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v   = '<?php echo $this->security->get_csrf_hash(); ?>';
var varPage = 1, varTimer, varAllRows = [], varTotal = 0;

var VAR_COLS = [
  {key:'product_name',  label:'Parent Item'},
  {key:'variant_name',  label:'Variant Name'},
  {key:'sku',           label:'SKU'},
  {key:'color_display', label:'Color'},
  {key:'size_display',  label:'Size'},
  {key:'style_display', label:'Style'},
  {key:'model_display', label:'Model'},
  {key:'price_display', label:'Price Override', right:true},
  {key:'cost_display',  label:'Cost', right:true},
  {key:'status_badge',  label:'Status'},
];

function varLoad(page) {
  varPage = page || 1;
  var per    = parseInt($('#var-per-page').val()) || 25;
  var offset = (varPage - 1) * per;
  var params = { page: varPage, per_page: per };
  $('.var-filter').each(function() {
    var v = $(this).val();
    if (v !== '') params[$(this).data('filter')] = v;
  });
  $('#var-list-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');

  $.getJSON(VAR_AJAX, params, function(r) {
    varAllRows = r.rows || [];
    varTotal   = r.total || 0;
    if (!varAllRows.length) {
      $('#var-list-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No variants found. Register items with "Has Variations" checked and add attribute values.</p></div></td></tr>');
      $('#var-list-info').text('');
      $('#var-list-pages').html('');
      return;
    }
    varRender(varAllRows, offset);
    var end = offset + varAllRows.length;
    $('#var-list-info').html('Showing <strong>'+(offset+1)+'</strong>–<strong>'+end+'</strong> of <strong>'+varTotal+'</strong>');
    varPages();
  }).fail(function() {
    $('#var-list-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load.</p></div></td></tr>');
  });
}

function varRender(rows, offset) {
  var html = '';
  rows.forEach(function(r, i) {
    html += '<tr class="xls-row">';
    html += '<td class="xls-cell xls-col-rownum">'+(offset+i+1)+'</td>';
    VAR_COLS.forEach(function(col) {
      var val = r[col.key] !== undefined ? r[col.key] : '<span style="color:#c0ccc6">—</span>';
      html += '<td class="xls-cell'+(col.right?' xls-right':'')+'">'+val+'</td>';
    });
    html += '<td class="xls-cell xls-col-actions">';
    if (r._edit_url) html += '<a href="'+r._edit_url+'" class="btn-inv-icon" title="Edit item"><i class="fa fa-pen"></i></a>';
    html += '</td>';
    html += '</tr>';
  });
  $('#var-list-body').html(html);
}

function varPages() {
  var per = parseInt($('#var-per-page').val()) || 25;
  var tp  = Math.ceil(varTotal / per);
  var p = '';
  if (tp > 1) {
    if (varPage > 1) p += '<a href="#" onclick="varLoad('+(varPage-1)+');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
    var s = Math.max(1, varPage-2), e2 = Math.min(tp, varPage+2);
    for (var x = s; x <= e2; x++) p += '<a href="#" class="'+(x===varPage?'active':'')+'" onclick="varLoad('+x+');return false;">'+x+'</a>';
    if (varPage < tp) p += '<a href="#" onclick="varLoad('+(varPage+1)+');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
  }
  $('#var-list-pages').html(p);
}

function varExportCsv() {
  if (!varAllRows.length) { alert('No data to export.'); return; }
  var per    = parseInt($('#var-per-page').val()) || 25;
  var headers = ['#'].concat(VAR_COLS.map(function(c){ return c.label; }));
  var lines = [headers.map(function(h){ return '"'+String(h).replace(/"/g,'""')+'"'; }).join(',')];
  varAllRows.forEach(function(row, i) {
    var vals = [(varPage-1)*per + i + 1];
    VAR_COLS.forEach(function(col) {
      var v = row[col.key] !== undefined ? String(row[col.key]) : '';
      v = v.replace(/<[^>]+>/g,'').replace(/"/g,'""').trim();
      vals.push('"'+v+'"');
    });
    lines.push(vals.join(','));
  });
  var blob = new Blob([lines.join('\r\n')], {type:'text/csv;charset=utf-8;'});
  var a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'variations_'+Date.now()+'.csv';
  a.click();
  URL.revokeObjectURL(a.href);
}

/* Column resize */
(function() {
  var th, startX, startW;
  $(document).on('mousedown', '.xls-resize-handle', function(e) {
    th = $(this).closest('.xls-th');
    startX = e.pageX; startW = th.outerWidth();
    $('body').addClass('xls-resizing');
    $(document).on('mousemove.vr', function(ev) { th.css({'min-width':Math.max(48,startW+(ev.pageX-startX))+'px'}); });
    $(document).on('mouseup.vr', function() { $('body').removeClass('xls-resizing'); $(document).off('.vr'); });
    e.preventDefault(); e.stopPropagation();
  });
})();

$('.var-filter').on('input change', function() {
  clearTimeout(varTimer);
  varTimer = setTimeout(function(){ varLoad(1); }, 320);
});
$('#var-per-page').on('change', function() { varLoad(1); });
$(function() { varLoad(1); });
</script>
