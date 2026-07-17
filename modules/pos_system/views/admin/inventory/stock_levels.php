<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'stock_levels',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
      <i class="fa fa-warehouse"></i> Current Stock Levels
    </h4>
    <div style="position:relative">
      <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px;pointer-events:none"></i>
      <input type="text" id="sl-search" class="inv-input" placeholder="Product, SKU, barcode…" style="width:200px;padding-left:30px">
    </div>
    <select id="sl-branch" class="inv-input" style="width:150px;cursor:pointer">
      <option value="">All Branches</option>
      <?php foreach ($branches as $b): ?>
        <option value="<?php echo $b['id']; ?>" <?php echo (int)$branch_id === (int)$b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <select id="sl-status" class="inv-input" style="width:140px;cursor:pointer">
      <option value="">All Status</option>
      <option value="in_stock">In Stock</option>
      <option value="low_stock">Low Stock</option>
      <option value="out_of_stock">Out of Stock</option>
    </select>
    <select id="sl-per-page" class="inv-input" style="width:90px;cursor:pointer">
      <option value="25">25 rows</option>
      <option value="50">50 rows</option>
      <option value="100">100 rows</option>
      <option value="200">200 rows</option>
    </select>
    <button onclick="slExportCsv()" class="btn-inv-secondary" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-file-csv"></i> Export
    </button>
  </div>

  <div id="sl-summary" style="padding:8px 16px;background:#f0f9f4;border-bottom:1px solid #e2f0e8;font-size:12px;color:#4a7a5a;min-height:34px"></div>

  <div class="xls-wrap" id="sl-wrap">
    <table class="xls-table" id="sl-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum">#<span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="product_name"  style="min-width:170px"><span class="xls-th-label">PRODUCT</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="sku"           style="min-width:100px"><span class="xls-th-label">SKU</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="barcode"       style="min-width:110px"><span class="xls-th-label">BARCODE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="category_name" style="min-width:130px"><span class="xls-th-label">CATEGORY</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="branch_name"   style="min-width:120px"><span class="xls-th-label">BRANCH</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="quantity"      style="min-width:100px"><span class="xls-th-label">QTY ON HAND</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="reorder_point" style="min-width:90px"><span class="xls-th-label">REORDER PT</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="max_stock"     style="min-width:90px"><span class="xls-th-label">MAX STOCK</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="cost_price"    style="min-width:110px"><span class="xls-th-label">COST PRICE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="selling_price" style="min-width:110px"><span class="xls-th-label">SELL PRICE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="cost_value"    style="min-width:120px"><span class="xls-th-label">COST VALUE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="sell_value"    style="min-width:120px"><span class="xls-th-label">SELL VALUE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="status_label"  style="min-width:90px"><span class="xls-th-label">STATUS</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-col-actions">ACTIONS<span class="xls-resize-handle"></span></th>
        </tr>
      </thead>
      <tbody id="sl-body">
        <tr><td colspan="15"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="sl-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="sl-pages" class="inv-pagination"></div>
  </div>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var SL_AJAX    = '<?php echo admin_url('pos_system/inv_ajax/stock_levels'); ?>';
var sl_page    = 1, sl_allRows = [], sl_timer;
var sl_sortCol = '', sl_sortDir = 'asc';

function slLoad(page) {
    sl_page = page || 1;
    var per = parseInt($('#sl-per-page').val()) || 25;
    $('#sl-body').html('<tr><td colspan="15"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');
    $.getJSON(SL_AJAX, {
        page: sl_page, per_page: per,
        search:    $('#sl-search').val(),
        branch_id: $('#sl-branch').val(),
        status:    $('#sl-status').val()
    }, function(r) {
        sl_allRows = r.rows || [];
        var total = r.total || 0;
        if (!sl_allRows.length) {
            $('#sl-body').html('<tr><td colspan="15"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No stock records found.</p></div></td></tr>');
            $('#sl-info').text(''); $('#sl-pages').html(''); $('#sl-summary').text(''); return;
        }
        if (r.summary) {
            $('#sl-summary').html(
                '<i class="fa fa-info-circle"></i> &nbsp;' +
                'Total: <strong>' + r.summary.total + '</strong> &nbsp;|&nbsp; ' +
                'In Stock: <strong style="color:#16a34a">' + r.summary.in_stock + '</strong> &nbsp;|&nbsp; ' +
                'Low Stock: <strong style="color:#d97706">' + r.summary.low + '</strong> &nbsp;|&nbsp; ' +
                'Out of Stock: <strong style="color:#dc2626">' + r.summary.out + '</strong>'
            );
        }
        slRender(sl_allRows, (sl_page - 1) * per, total, per);
    }).fail(function() {
        $('#sl-body').html('<tr><td colspan="15"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load.</p></div></td></tr>');
    });
}

function slRender(rows, offset, total, per) {
    var html = '';
    $.each(rows, function(i, row) {
        var qty = parseFloat(row.quantity || 0), min = parseFloat(row.reorder_point || 0);
        var statusB, qc, statusLabel;
        if (qty <= 0) {
            statusB = '<span class="ibadge ibadge-cancelled">Out of Stock</span>'; statusLabel = 'Out of Stock';
            qc = 'color:#dc2626;font-weight:700';
        } else if (min > 0 && qty <= min) {
            statusB = '<span class="ibadge ibadge-in_transit">Low Stock</span>'; statusLabel = 'Low Stock';
            qc = 'color:#d97706;font-weight:700';
        } else {
            statusB = '<span class="ibadge ibadge-active">In Stock</span>'; statusLabel = 'In Stock';
            qc = 'color:#16a34a;font-weight:700';
        }
        var maxS = parseFloat(row.max_stock || 0);
        row.status_label = statusLabel;
        html += '<tr class="xls-row">';
        html += '<td class="xls-cell xls-col-rownum">' + (offset + i + 1) + '</td>';
        html += '<td class="xls-cell" style="font-weight:600">' + (row.product_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (row.sku || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (row.barcode || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + (row.category_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + (row.branch_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell xls-right" style="' + qc + '">' + qty.toFixed(2) + '</td>';
        html += '<td class="xls-cell xls-right" style="font-size:11px;color:#64748b">' + (min > 0 ? min.toFixed(2) : '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell xls-right" style="font-size:11px;color:#64748b">' + (maxS > 0 ? maxS.toFixed(2) : '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell xls-right" style="font-size:12px">KSh ' + parseFloat(row.cost_price || 0).toLocaleString('en-KE', {minimumFractionDigits: 2}) + '</td>';
        html += '<td class="xls-cell xls-right" style="font-size:12px">KSh ' + parseFloat(row.selling_price || 0).toLocaleString('en-KE', {minimumFractionDigits: 2}) + '</td>';
        html += '<td class="xls-cell xls-right" style="font-weight:600">' + (row.cost_value ? 'KSh ' + parseFloat(row.cost_value).toLocaleString('en-KE', {minimumFractionDigits: 2}) : '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell xls-right" style="font-weight:600">' + (row.sell_value ? 'KSh ' + parseFloat(row.sell_value).toLocaleString('en-KE', {minimumFractionDigits: 2}) : '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell">' + statusB + '</td>';
        html += '<td class="xls-cell xls-col-actions">';
        html += '<a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>?product_id=' + row.product_id + '&branch_id=' + row.branch_id + '" class="btn-inv-icon" title="Receive stock"><i class="fa fa-truck-loading"></i></a> ';
        html += '<a href="<?php echo admin_url('pos_system/inventory/adjustments'); ?>?product_id=' + row.product_id + '" class="btn-inv-icon" title="Adjust"><i class="fa fa-sliders-h"></i></a>';
        html += '</td></tr>';
    });
    $('#sl-body').html(html);

    var tp = Math.ceil(total / per), pages = '';
    if (tp > 1) {
        if (sl_page > 1) pages += '<a href="#" onclick="slLoad(' + (sl_page - 1) + ');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
        var st = Math.max(1, sl_page - 2), en = Math.min(tp, sl_page + 2);
        for (var pg = st; pg <= en; pg++) pages += '<a href="#" class="' + (pg == sl_page ? 'active' : '') + '" onclick="slLoad(' + pg + ');return false;">' + pg + '</a>';
        if (sl_page < tp) pages += '<a href="#" onclick="slLoad(' + (sl_page + 1) + ');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
    }
    $('#sl-pages').html(pages);
    $('#sl-info').html('Showing <strong>' + ((sl_page - 1) * per + 1) + '</strong>–<strong>' + ((sl_page - 1) * per + rows.length) + '</strong> of <strong>' + total + '</strong> records');
}

$(document).on('click', '#sl-table .xls-th', function(e) {
    if ($(e.target).hasClass('xls-resize-handle')) return;
    if ($(this).hasClass('xls-col-rownum') || $(this).hasClass('xls-col-actions')) return;
    var col = $(this).data('col');
    if (!col) return;
    if (sl_sortCol === col) { sl_sortDir = sl_sortDir === 'asc' ? 'desc' : 'asc'; }
    else { sl_sortCol = col; sl_sortDir = 'asc'; }
    $('#sl-table .xls-th .xls-sort-icon').html('<i class="fa fa-sort"></i>');
    $(this).find('.xls-sort-icon').html('<i class="fa fa-sort-' + (sl_sortDir === 'asc' ? 'up' : 'down') + '"></i>');
    var sorted = sl_allRows.slice().sort(function(a, b) {
        var av = String(a[col] || '').replace(/<[^>]+>/g, '').trim();
        var bv = String(b[col] || '').replace(/<[^>]+>/g, '').trim();
        var an = parseFloat(av.replace(/[^0-9.-]/g, '')), bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return sl_sortDir === 'asc' ? an - bn : bn - an;
        return sl_sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    var per = parseInt($('#sl-per-page').val()) || 25;
    slRender(sorted, (sl_page - 1) * per, sl_allRows.length, per);
});

(function() {
    var th, startX, startW;
    $(document).on('mousedown', '#sl-table .xls-resize-handle', function(e) {
        th = $(this).closest('.xls-th'); startX = e.pageX; startW = th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.xlssl', function(ev) {
            var w = Math.max(48, startW + (ev.pageX - startX));
            th.css({'min-width': w + 'px', 'max-width': w + 'px'});
        });
        $(document).on('mouseup.xlssl', function() { $('body').removeClass('xls-resizing'); $(document).off('.xlssl'); });
        e.preventDefault(); e.stopPropagation();
    });
})();

function slExportCsv() {
    if (!sl_allRows.length) { alert('No data to export.'); return; }
    var per = parseInt($('#sl-per-page').val()) || 25;
    var hdrs = ['#', 'Product', 'SKU', 'Barcode', 'Category', 'Branch', 'Qty On Hand', 'Reorder Pt', 'Max Stock', 'Cost Price', 'Sell Price', 'Cost Value', 'Sell Value', 'Status'];
    var lines = [hdrs.map(function(h) { return '"' + h.replace(/"/g, '""') + '"'; }).join(',')];
    sl_allRows.forEach(function(row, i) {
        var qty = parseFloat(row.quantity || 0), min = parseFloat(row.reorder_point || 0);
        var status = qty <= 0 ? 'Out of Stock' : (min > 0 && qty <= min ? 'Low Stock' : 'In Stock');
        lines.push([
            (sl_page - 1) * per + i + 1,
            '"' + (row.product_name || '').replace(/"/g, '""') + '"',
            '"' + (row.sku || '').replace(/"/g, '""') + '"',
            '"' + (row.barcode || '').replace(/"/g, '""') + '"',
            '"' + (row.category_name || '').replace(/"/g, '""') + '"',
            '"' + (row.branch_name || '').replace(/"/g, '""') + '"',
            qty.toFixed(2),
            parseFloat(row.reorder_point || 0).toFixed(2),
            parseFloat(row.max_stock || 0).toFixed(2),
            parseFloat(row.cost_price || 0).toFixed(2),
            parseFloat(row.selling_price || 0).toFixed(2),
            parseFloat(row.cost_value || 0).toFixed(2),
            parseFloat(row.sell_value || 0).toFixed(2),
            '"' + status + '"'
        ].join(','));
    });
    var blob = new Blob([lines.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'stock_levels_' + Date.now() + '.csv'; a.click();
}

$('#sl-search').on('input', function() { clearTimeout(sl_timer); sl_timer = setTimeout(function() { slLoad(1); }, 300); });
$('#sl-branch, #sl-status, #sl-per-page').on('change', function() { slLoad(1); });
$(function() { slLoad(1); });
</script>
