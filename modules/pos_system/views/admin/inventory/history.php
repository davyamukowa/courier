<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'history',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
      <i class="fa fa-history"></i> Inventory History &amp; Activity Log
    </h4>
    <div style="position:relative">
      <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px;pointer-events:none"></i>
      <input type="text" id="f-search" class="inv-input" placeholder="Product or SKU…" style="width:180px;padding-left:30px">
    </div>
    <select id="f-type" class="inv-input" style="width:160px;cursor:pointer">
      <option value="">All Movement Types</option>
      <option value="purchase">Purchase / Receipt</option>
      <option value="sale">Sale / Delivery</option>
      <option value="adjustment">Adjustment</option>
      <option value="transfer_in">Transfer In</option>
      <option value="transfer_out">Transfer Out</option>
      <option value="refund">Refund / Return</option>
      <option value="opening">Opening Stock</option>
    </select>
    <input type="date" id="f-from" class="inv-input" style="width:135px" title="Date from">
    <input type="date" id="f-to"   class="inv-input" style="width:135px" title="Date to">
    <select id="hist-per-page" class="inv-input" style="width:90px;cursor:pointer">
      <option value="25">25 rows</option>
      <option value="50">50 rows</option>
      <option value="100">100 rows</option>
      <option value="200">200 rows</option>
    </select>
    <button class="btn-inv-secondary" onclick="historyExportCsv()" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-file-csv"></i> Export
    </button>
  </div>

  <div class="xls-wrap" id="hist-wrap">
    <table class="xls-table" id="hist-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum">#<span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="date_fmt"       style="min-width:130px"><span class="xls-th-label">DATE / TIME</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="product_name"   style="min-width:160px"><span class="xls-th-label">PRODUCT</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="sku"            style="min-width:100px"><span class="xls-th-label">SKU</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="branch_name"    style="min-width:120px"><span class="xls-th-label">BRANCH</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="type"           style="min-width:110px"><span class="xls-th-label">TYPE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="qty_before"  style="min-width:90px"><span class="xls-th-label">QTY BEFORE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="qty_change"  style="min-width:90px"><span class="xls-th-label">CHANGE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-right" data-col="qty_after"   style="min-width:90px"><span class="xls-th-label">QTY AFTER</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="staff_name"     style="min-width:130px"><span class="xls-th-label">STAFF</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" data-col="reference_type" style="min-width:130px"><span class="xls-th-label">REFERENCE</span><span class="xls-sort-icon"><i class="fa fa-sort"></i></span><span class="xls-resize-handle"></span></th>
        </tr>
      </thead>
      <tbody id="hist-body">
        <tr><td colspan="12"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="hist-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="hist-pages" class="inv-pagination"></div>
  </div>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var HIST_URL     = '<?php echo admin_url('pos_system/inv_ajax/history'); ?>';
var _csrf_n      = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v      = '<?php echo $this->security->get_csrf_hash(); ?>';
var hist_page    = 1, hist_allRows = [], hist_timer;
var hist_sortCol = '', hist_sortDir = 'asc';

var TYPE_LABELS = {purchase:'Receipt',sale:'Sale',adjustment:'Adjustment',transfer_in:'Transfer In',transfer_out:'Transfer Out',refund:'Return',opening:'Opening'};
var TYPE_BADGES = {purchase:'ibadge-confirmed',sale:'ibadge-cancelled',adjustment:'ibadge-correction',transfer_in:'ibadge-delivered',transfer_out:'ibadge-in_transit',refund:'ibadge-loss',opening:'ibadge-inactive'};

function histLoad(page) {
    hist_page = page || 1;
    var per = parseInt($('#hist-per-page').val()) || 25;
    var params = {
        page: hist_page, per_page: per,
        search:    $('#f-search').val(),
        type:      $('#f-type').val(),
        date_from: $('#f-from').val(),
        date_to:   $('#f-to').val()
    };
    $('#hist-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');
    $.getJSON(HIST_URL, params, function(r) {
        hist_allRows = r.rows || [];
        var total = r.total || 0;
        if (!hist_allRows.length) {
            $('#hist-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No movements found.</p></div></td></tr>');
            $('#hist-info').text(''); $('#hist-pages').html(''); return;
        }
        histRender(hist_allRows, (hist_page - 1) * per, total, per);
    }).fail(function() {
        $('#hist-body').html('<tr><td colspan="12"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load.</p></div></td></tr>');
    });
}

function histRender(rows, offset, total, per) {
    var html = '';
    $.each(rows, function(i, row) {
        var qc = parseFloat(row.qty_change || 0);
        var qcColor = qc >= 0 ? 'color:#16a34a;font-weight:700' : 'color:#dc2626;font-weight:700';
        var lbl = TYPE_LABELS[row.type] || (row.type || '—');
        var badgeCls = TYPE_BADGES[row.type] || 'ibadge-inactive';
        var ref = row.reference_type ? row.reference_type + ' #' + (row.reference_id || '') : '<span style="color:#c0ccc6">—</span>';
        html += '<tr class="xls-row">';
        html += '<td class="xls-cell xls-col-rownum">' + (offset + i + 1) + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b;white-space:nowrap">' + (row.date_fmt || '—') + '</td>';
        html += '<td class="xls-cell" style="font-weight:600">' + (row.product_name || '—') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (row.sku || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px">' + (row.branch_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell"><span class="ibadge ' + badgeCls + '">' + lbl + '</span></td>';
        html += '<td class="xls-cell xls-right" style="color:#64748b">' + parseFloat(row.qty_before || 0).toFixed(2) + '</td>';
        html += '<td class="xls-cell xls-right" style="' + qcColor + '">' + (qc >= 0 ? '+' : '') + qc.toFixed(2) + '</td>';
        html += '<td class="xls-cell xls-right" style="font-weight:600">' + parseFloat(row.qty_after || 0).toFixed(2) + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#64748b">' + (row.staff_name || '<span style="color:#c0ccc6">—</span>') + '</td>';
        html += '<td class="xls-cell" style="font-size:11px;color:#94a3b8">' + ref + '</td>';
        html += '</tr>';
    });
    $('#hist-body').html(html);

    var tp = Math.ceil(total / per), pages = '';
    if (tp > 1) {
        if (hist_page > 1) pages += '<a href="#" onclick="histLoad(' + (hist_page - 1) + ');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
        var st = Math.max(1, hist_page - 2), en = Math.min(tp, hist_page + 2);
        for (var pg = st; pg <= en; pg++) pages += '<a href="#" class="' + (pg == hist_page ? 'active' : '') + '" onclick="histLoad(' + pg + ');return false;">' + pg + '</a>';
        if (hist_page < tp) pages += '<a href="#" onclick="histLoad(' + (hist_page + 1) + ');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
    }
    $('#hist-pages').html(pages);
    $('#hist-info').html('Showing <strong>' + ((hist_page - 1) * per + 1) + '</strong>–<strong>' + ((hist_page - 1) * per + rows.length) + '</strong> of <strong>' + total + '</strong> movements');
}

$(document).on('click', '#hist-table .xls-th', function(e) {
    if ($(e.target).hasClass('xls-resize-handle')) return;
    if ($(this).hasClass('xls-col-rownum')) return;
    var col = $(this).data('col');
    if (!col) return;
    if (hist_sortCol === col) { hist_sortDir = hist_sortDir === 'asc' ? 'desc' : 'asc'; }
    else { hist_sortCol = col; hist_sortDir = 'asc'; }
    $('#hist-table .xls-th .xls-sort-icon').html('<i class="fa fa-sort"></i>');
    $(this).find('.xls-sort-icon').html('<i class="fa fa-sort-' + (hist_sortDir === 'asc' ? 'up' : 'down') + '"></i>');
    var sorted = hist_allRows.slice().sort(function(a, b) {
        var av = String(a[col] || '').replace(/<[^>]+>/g, '').trim();
        var bv = String(b[col] || '').replace(/<[^>]+>/g, '').trim();
        var an = parseFloat(av.replace(/[^0-9.-]/g, '')), bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return hist_sortDir === 'asc' ? an - bn : bn - an;
        return hist_sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    var per = parseInt($('#hist-per-page').val()) || 25;
    histRender(sorted, (hist_page - 1) * per, hist_allRows.length, per);
});

(function() {
    var th, startX, startW;
    $(document).on('mousedown', '#hist-table .xls-resize-handle', function(e) {
        th = $(this).closest('.xls-th'); startX = e.pageX; startW = th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.xlshist', function(ev) {
            var w = Math.max(48, startW + (ev.pageX - startX));
            th.css({'min-width': w + 'px', 'max-width': w + 'px'});
        });
        $(document).on('mouseup.xlshist', function() { $('body').removeClass('xls-resizing'); $(document).off('.xlshist'); });
        e.preventDefault(); e.stopPropagation();
    });
})();

function historyExportCsv() {
    if (!hist_allRows.length) { alert('No data to export.'); return; }
    var per = parseInt($('#hist-per-page').val()) || 25;
    var hdrs = ['#', 'Date/Time', 'Product', 'SKU', 'Branch', 'Type', 'Qty Before', 'Change', 'Qty After', 'Staff', 'Reference'];
    var lines = [hdrs.map(function(h) { return '"' + h.replace(/"/g, '""') + '"'; }).join(',')];
    hist_allRows.forEach(function(row, i) {
        var qc = parseFloat(row.qty_change || 0);
        var ref = row.reference_type ? row.reference_type + ' #' + (row.reference_id || '') : '';
        lines.push([
            (hist_page - 1) * per + i + 1,
            '"' + (row.date_fmt || '').replace(/"/g, '""') + '"',
            '"' + (row.product_name || '').replace(/"/g, '""') + '"',
            '"' + (row.sku || '').replace(/"/g, '""') + '"',
            '"' + (row.branch_name || '').replace(/"/g, '""') + '"',
            '"' + (TYPE_LABELS[row.type] || row.type || '').replace(/"/g, '""') + '"',
            parseFloat(row.qty_before || 0).toFixed(2),
            qc.toFixed(2),
            parseFloat(row.qty_after || 0).toFixed(2),
            '"' + (row.staff_name || '').replace(/"/g, '""') + '"',
            '"' + ref.replace(/"/g, '""') + '"'
        ].join(','));
    });
    var blob = new Blob([lines.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'inventory_history_' + Date.now() + '.csv'; a.click();
}

$('#f-search, #f-type, #f-from, #f-to').on('input change', function() {
    clearTimeout(hist_timer);
    hist_timer = setTimeout(function() { histLoad(1); }, 320);
});
$('#hist-per-page').on('change', function() { histLoad(1); });
$(function() { histLoad(1); });
</script>
