<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', ['inv_section'=>'items','inv_branches'=>$branches,'inv_branch_id'=>$branch_id]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
      <i class="fa fa-cube"></i> Products
    </h4>
    <div style="position:relative">
      <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px;pointer-events:none"></i>
      <input type="text" id="inv-item-search" class="inv-input" placeholder="Search name, SKU, barcode…" style="width:200px;padding-left:30px">
    </div>
    <select id="inv-item-cat" class="inv-input" style="width:150px;cursor:pointer">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <select id="inv-item-status" class="inv-input" style="width:120px;cursor:pointer">
      <option value="">All Status</option>
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>
    <select id="inv-per-page" class="inv-input" style="width:90px;cursor:pointer">
      <option value="25">25 rows</option>
      <option value="50">50 rows</option>
      <option value="100">100 rows</option>
      <option value="200">200 rows</option>
    </select>
    <button onclick="invItemsExportCsv()" class="btn-inv-secondary" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-file-csv"></i> Export
    </button>
    <button onclick="invItemModal()" class="btn-inv-primary">
      <i class="fa fa-plus"></i> New Product
    </button>
  </div>

  <div class="xls-wrap" id="items-wrap">
    <table class="xls-table" id="items-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum">#<span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="sku"             style="min-width:100px"><span class="xls-th-label">SKU</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="name"            style="min-width:180px"><span class="xls-th-label">PRODUCT NAME</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="barcode"         style="min-width:110px"><span class="xls-th-label">BARCODE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="category_name"   style="min-width:130px"><span class="xls-th-label">CATEGORY</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="brand_name"      style="min-width:110px"><span class="xls-th-label">BRAND</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="unit_name"       style="min-width:80px" ><span class="xls-th-label">UNIT</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="type"            style="min-width:90px" ><span class="xls-th-label">TYPE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="cost_price"    style="min-width:110px"><span class="xls-th-label">COST</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="selling_price" style="min-width:110px"><span class="xls-th-label">SELL PRICE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="total_qty"     style="min-width:90px" ><span class="xls-th-label">ON HAND</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="reorder_point" style="min-width:90px" ><span class="xls-th-label">REORDER PT</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="track_inventory"  style="min-width:70px"><span class="xls-th-label">TRACK</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="is_pos_visible"   style="min-width:70px"><span class="xls-th-label">POS</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="is_active"        style="min-width:80px"><span class="xls-th-label">STATUS</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-col-actions">ACTIONS<span class="xls-resize-handle"></span></th>
        </tr>
      </thead>
      <tbody id="inv-items-body">
        <tr><td colspan="16"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="inv-items-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="inv-items-pages" class="inv-pagination"></div>
  </div>

</div>
</div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="invItemModal" tabindex="-1">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header" style="background:#2c3e6a;color:#fff">
     <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
     <h4 class="modal-title"><i class="fa fa-cube" style="margin-right:8px"></i><span id="invItemModalTitle">New Product</span></h4>
   </div>
   <div class="modal-body">
    <form id="invItemForm">
      <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
      <input type="hidden" name="id" id="invItemId" value="">
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="invItemName" class="form-control" required placeholder="e.g. Maize Flour 2kg">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>SKU / Code</label>
            <input type="text" name="sku" id="invItemSku" class="form-control" placeholder="Auto-generated if blank">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Barcode</label>
            <input type="text" name="barcode" id="invItemBarcode" class="form-control" placeholder="EAN / UPC">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Category</label>
            <select name="category_id" id="invItemCategoryId" class="form-control">
              <option value="">— None —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Brand</label>
            <select name="brand_id" id="invItemBrandId" class="form-control">
              <option value="">— None —</option>
              <?php foreach ($brands as $b): ?>
              <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label>Product Type</label>
            <select name="type" id="invItemType" class="form-control">
              <option value="simple">Simple Product</option>
              <option value="variable">Variable (Variants)</option>
              <option value="service">Service (No Stock)</option>
              <option value="bundle">Bundle</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Unit</label>
            <select name="unit_id" id="invItemUnitId" class="form-control">
              <option value="">— Default —</option>
              <?php foreach ($units as $u): ?>
              <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['symbol']); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Supplier</label>
            <select name="supplier_id" id="invItemSupplierId" class="form-control">
              <option value="">— None —</option>
              <?php foreach ($suppliers as $sup): ?>
              <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Reorder Point</label>
            <input type="number" name="reorder_point" id="invItemReorder" class="form-control" step="0.01" min="0" placeholder="Min. stock">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Cost Price (Purchase)</label>
            <div class="input-group">
              <span class="input-group-addon">KSh</span>
              <input type="number" name="cost_price" id="invItemCost" class="form-control" step="0.01" min="0" value="0">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Selling Price <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-addon">KSh</span>
              <input type="number" name="selling_price" id="invItemPrice" class="form-control" step="0.01" min="0" value="0" required>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Tax Rate</label>
            <select name="tax_rate_id" class="form-control">
              <option value="">No Tax</option>
              <?php if (!empty($taxes)): foreach ($taxes as $t): ?>
              <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['taxrate']; ?>%)</option>
              <?php endforeach; endif; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="invItemDesc" class="form-control" rows="2" placeholder="Optional product description"></textarea>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <div class="checkbox"><label>
            <input type="checkbox" name="track_inventory" id="invItemTrack" value="1" checked> Track Inventory
          </label></div>
        </div>
        <div class="col-md-3">
          <div class="checkbox"><label>
            <input type="checkbox" name="is_pos_visible" id="invItemPosVis" value="1" checked> Visible in POS
          </label></div>
        </div>
        <div class="col-md-3">
          <div class="checkbox"><label>
            <input type="checkbox" name="allow_negative" id="invItemNeg" value="1"> Allow Negative Stock
          </label></div>
        </div>
        <div class="col-md-3">
          <div class="checkbox"><label>
            <input type="checkbox" name="is_active" id="invItemActive" value="1" checked> Active
          </label></div>
        </div>
      </div>
    </form>
   </div>
   <div class="modal-footer">
     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
     <button type="button" class="btn btn-primary" id="invItemSaveBtn" onclick="invItemSave()">
       <i class="fa fa-save"></i> Save Product
     </button>
   </div>
  </div>
 </div>
</div>

</div>
<?php init_tail(); ?>
<script>
var INV_BASE      = '<?php echo admin_url('pos_system'); ?>';
var _csrf_n       = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v       = '<?php echo $this->security->get_csrf_hash(); ?>';
var invItemsPage  = 1, invItemsAllRows = [], invItemsMap = {}, invItemsTimer;
var invItemsSortCol = '', invItemsSortDir = 'asc';

function invItemsLoad(page) {
    invItemsPage = page || 1;
    var per = parseInt($('#inv-per-page').val()) || 25;
    $('#inv-items-body').html('<tr><td colspan="16"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');
    $.getJSON(INV_BASE + '/inv_ajax/items', {
        page: invItemsPage, per_page: per,
        search:   $('#inv-item-search').val(),
        category: $('#inv-item-cat').val(),
        active:   $('#inv-item-status').val()
    }, function(r) {
        invItemsAllRows = r.rows || [];
        invItemsMap = {};
        $.each(invItemsAllRows, function(i, p) { invItemsMap[p.id] = p; });
        var total = r.total || 0;
        if (!invItemsAllRows.length) {
            $('#inv-items-body').html('<tr><td colspan="16"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No products found. <a href="#" onclick="invItemModal()">Add one</a>.</p></div></td></tr>');
            $('#inv-items-info').text(''); $('#inv-items-pages').html(''); return;
        }
        invItemsRender(invItemsAllRows, (invItemsPage - 1) * per, total, per);
    }).fail(function() {
        $('#inv-items-body').html('<tr><td colspan="16"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load.</p></div></td></tr>');
    });
}

function invItemsRender(rows, offset, total, per) {
    var typeB = {
        simple:   '<span class="ibadge" style="background:#dbeafe;color:#1d4ed8">Simple</span>',
        variable: '<span class="ibadge" style="background:#ede9fe;color:#7c3aed">Variable</span>',
        service:  '<span class="ibadge ibadge-inactive">Service</span>',
        bundle:   '<span class="ibadge" style="background:#fef3c7;color:#92400e">Bundle</span>'
    };
    var html = '';
    $.each(rows, function(i, p) {
        var qty = parseFloat(p.total_qty || 0), reord = parseFloat(p.reorder_point || 0);
        var stockB;
        if (p.track_inventory == '0') {
            stockB = '<span class="ibadge ibadge-inactive">—</span>';
        } else if (qty <= 0) {
            stockB = '<span class="ibadge ibadge-cancelled">' + qty.toFixed(2) + '</span>';
        } else if (reord > 0 && qty <= reord) {
            stockB = '<span class="ibadge ibadge-in_transit">' + qty.toFixed(2) + '</span>';
        } else {
            stockB = '<span class="ibadge ibadge-active">' + qty.toFixed(2) + '</span>';
        }
        var unit = p.unit_name ? p.unit_name + (p.unit_symbol ? ' (' + p.unit_symbol + ')' : '') : '<span style="color:#c0ccc6">—</span>';
        html += '<tr class="xls-row">';
        html += '<td class="xls-cell xls-col-rownum">' + (offset + i + 1) + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (p.sku || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-weight:600">' + $('<div>').text(p.name || '').html() + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (p.barcode || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + (p.category_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + (p.brand_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + unit + '</td>';
        html += '<td class="xls-cell">' + (typeB[p.type] || '<span class="ibadge ibadge-inactive">' + (p.type || '—') + '</span>') + '</td>';
        html += '<td class="xls-cell xls-right">KSh ' + parseFloat(p.cost_price || 0).toLocaleString('en-KE', {minimumFractionDigits: 2}) + '</td>';
        html += '<td class="xls-cell xls-right" style="font-weight:600">KSh ' + parseFloat(p.selling_price || 0).toLocaleString('en-KE', {minimumFractionDigits: 2}) + '</td>';
        html += '<td class="xls-cell xls-right">' + stockB + '</td>';
        html += '<td class="xls-cell xls-right" style="font-size:11px;color:#64748b">' + (reord > 0 ? reord.toFixed(2) : '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell">' + (p.track_inventory != '0' ? '<span class="ibadge ibadge-active">Yes</span>' : '<span class="ibadge ibadge-inactive">No</span>') + '</td>';
        html += '<td class="xls-cell">' + (p.is_pos_visible != '0' ? '<span class="ibadge ibadge-active">Yes</span>' : '<span class="ibadge ibadge-cancelled">No</span>') + '</td>';
        html += '<td class="xls-cell">' + (p.is_active != '0' ? '<span class="ibadge ibadge-active">Active</span>' : '<span class="ibadge ibadge-inactive">Inactive</span>') + '</td>';
        html += '<td class="xls-cell xls-col-actions">';
        html += '<button class="btn-inv-icon" onclick="invItemModal(invItemsMap[' + p.id + '])" title="Edit"><i class="fa fa-pen"></i></button> ';
        html += '<button class="btn-inv-icon danger" onclick="invItemDelete(' + p.id + ')" title="Delete"><i class="fa fa-trash"></i></button>';
        html += '</td></tr>';
    });
    $('#inv-items-body').html(html);

    var tp = Math.ceil(total / per), pages = '';
    if (tp > 1) {
        if (invItemsPage > 1) pages += '<a href="#" onclick="invItemsLoad(' + (invItemsPage - 1) + ');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
        var st = Math.max(1, invItemsPage - 2), en = Math.min(tp, invItemsPage + 2);
        for (var pg = st; pg <= en; pg++) pages += '<a href="#" class="' + (pg == invItemsPage ? 'active' : '') + '" onclick="invItemsLoad(' + pg + ');return false;">' + pg + '</a>';
        if (invItemsPage < tp) pages += '<a href="#" onclick="invItemsLoad(' + (invItemsPage + 1) + ');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
    }
    $('#inv-items-pages').html(pages);
    $('#inv-items-info').html('Showing <strong>' + ((invItemsPage - 1) * per + 1) + '</strong>–<strong>' + ((invItemsPage - 1) * per + rows.length) + '</strong> of <strong>' + total + '</strong> products');
}

$(document).on('click', '#items-table .xls-th', function(e) {
    if ($(e.target).hasClass('xls-resize-handle')) return;
    if ($(this).hasClass('xls-col-rownum') || $(this).hasClass('xls-col-actions')) return;
    var col = $(this).data('col');
    if (!col) return;
    if (invItemsSortCol === col) { invItemsSortDir = invItemsSortDir === 'asc' ? 'desc' : 'asc'; }
    else { invItemsSortCol = col; invItemsSortDir = 'asc'; }
    $('#items-table .xls-th .xls-sort-icon').html('<i class="fa fa-sort"></i>');
    $(this).find('.xls-sort-icon').html('<i class="fa fa-sort-' + (invItemsSortDir === 'asc' ? 'up' : 'down') + '"></i>');
    var sorted = invItemsAllRows.slice().sort(function(a, b) {
        var av = String(a[col] || '').replace(/<[^>]+>/g, '').trim();
        var bv = String(b[col] || '').replace(/<[^>]+>/g, '').trim();
        var an = parseFloat(av.replace(/[^0-9.-]/g, '')), bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return invItemsSortDir === 'asc' ? an - bn : bn - an;
        return invItemsSortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    var per = parseInt($('#inv-per-page').val()) || 25;
    invItemsRender(sorted, (invItemsPage - 1) * per, invItemsAllRows.length, per);
});

(function() {
    var th, startX, startW;
    $(document).on('mousedown', '#items-table .xls-resize-handle', function(e) {
        th = $(this).closest('.xls-th'); startX = e.pageX; startW = th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.xlsitems', function(ev) {
            var w = Math.max(48, startW + (ev.pageX - startX));
            th.css({'min-width': w + 'px', 'max-width': w + 'px'});
        });
        $(document).on('mouseup.xlsitems', function() { $('body').removeClass('xls-resizing'); $(document).off('.xlsitems'); });
        e.preventDefault(); e.stopPropagation();
    });
})();

function invItemsExportCsv() {
    if (!invItemsAllRows.length) { alert('No data to export.'); return; }
    var per = parseInt($('#inv-per-page').val()) || 25;
    var hdrs = ['#', 'SKU', 'Product Name', 'Barcode', 'Category', 'Brand', 'Unit', 'Type', 'Cost Price', 'Sell Price', 'On Hand', 'Reorder Pt', 'Track Inv', 'POS Visible', 'Status'];
    var lines = [hdrs.map(function(h) { return '"' + h.replace(/"/g, '""') + '"'; }).join(',')];
    invItemsAllRows.forEach(function(p, i) {
        var unit = p.unit_name ? p.unit_name + (p.unit_symbol ? ' (' + p.unit_symbol + ')' : '') : '';
        lines.push([
            (invItemsPage - 1) * per + i + 1,
            '"' + (p.sku || '').replace(/"/g, '""') + '"',
            '"' + (p.name || '').replace(/"/g, '""') + '"',
            '"' + (p.barcode || '').replace(/"/g, '""') + '"',
            '"' + (p.category_name || '').replace(/"/g, '""') + '"',
            '"' + (p.brand_name || '').replace(/"/g, '""') + '"',
            '"' + unit.replace(/"/g, '""') + '"',
            '"' + (p.type || '').replace(/"/g, '""') + '"',
            parseFloat(p.cost_price || 0).toFixed(2),
            parseFloat(p.selling_price || 0).toFixed(2),
            parseFloat(p.total_qty || 0).toFixed(2),
            parseFloat(p.reorder_point || 0).toFixed(2),
            p.track_inventory != '0' ? 'Yes' : 'No',
            p.is_pos_visible != '0' ? 'Yes' : 'No',
            p.is_active != '0' ? 'Active' : 'Inactive'
        ].join(','));
    });
    var blob = new Blob([lines.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'products_' + Date.now() + '.csv'; a.click();
}

function invItemModal(p) {
    p = p || {};
    $('#invItemModalTitle').text(p.id ? 'Edit Product' : 'New Product');
    $('#invItemId').val(p.id || '');
    $('#invItemName').val(p.name || '');
    $('#invItemSku').val(p.sku || '');
    $('#invItemBarcode').val(p.barcode || '');
    $('#invItemCategoryId').val(p.category_id || '');
    $('#invItemBrandId').val(p.brand_id || '');
    $('#invItemType').val(p.type || 'simple');
    $('#invItemUnitId').val(p.unit_id || '');
    $('#invItemSupplierId').val(p.supplier_id || '');
    $('#invItemReorder').val(p.reorder_point || '');
    $('#invItemCost').val(p.cost_price || 0);
    $('#invItemPrice').val(p.selling_price || 0);
    $('#invItemDesc').val(p.description || '');
    $('#invItemTrack').prop('checked', p.track_inventory != '0');
    $('#invItemPosVis').prop('checked', p.is_pos_visible != '0');
    $('#invItemNeg').prop('checked', p.allow_negative == '1');
    $('#invItemActive').prop('checked', p.is_active != '0');
    $('#invItemModal').modal('show');
}

function invItemSave() {
    var data = $('#invItemForm').serialize();
    $('#invItemSaveBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');
    $.post(INV_BASE + '/inv_save/product', data, function(r) {
        $('#invItemSaveBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Product');
        if (r.success) {
            $('#invItemModal').modal('hide');
            alert_float('success', r.message || 'Product saved.');
            invItemsLoad(invItemsPage);
        } else {
            alert_float('danger', r.error || 'Save failed.');
        }
    }, 'json').fail(function() {
        $('#invItemSaveBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Product');
        alert_float('danger', 'Server error. Please try again.');
    });
}

function invItemDelete(id) {
    var name = invItemsMap[id] ? invItemsMap[id].name : 'this product';
    if (!confirm('Delete "' + name + '"?\nThis cannot be undone.')) return;
    $.post(INV_BASE + '/inv_delete/product/' + id, {<?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'}, function(r) {
        if (r.success) { alert_float('success', 'Product deleted.'); invItemsLoad(invItemsPage); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}

$('#inv-item-search').on('input', function() { clearTimeout(invItemsTimer); invItemsTimer = setTimeout(function() { invItemsLoad(1); }, 350); });
$('#inv-item-cat, #inv-item-status, #inv-per-page').on('change', function() { invItemsLoad(1); });
$(function() { invItemsLoad(1); });
</script>
