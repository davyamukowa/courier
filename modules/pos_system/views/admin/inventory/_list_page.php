<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => $inv_section,
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <!-- Toolbar -->
  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
      <i class="fa <?php echo $page_icon; ?>"></i>
      <?php echo $page_title; ?>
    </h4>

    <?php foreach ($filters as $f): ?>
      <?php if ($f['type'] === 'text'): ?>
        <div style="position:relative">
          <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aab4c4;font-size:12px;pointer-events:none"></i>
          <input type="text" class="inv-input inv-filter" data-filter="<?php echo $f['name']; ?>"
                 placeholder="<?php echo htmlspecialchars($f['label']); ?>" style="width:180px;padding-left:30px">
        </div>
      <?php elseif ($f['type'] === 'select'): ?>
        <select class="inv-input inv-filter" data-filter="<?php echo $f['name']; ?>" style="width:140px;cursor:pointer">
          <option value=""><?php echo htmlspecialchars($f['label']); ?></option>
          <?php foreach ($f['options'] as $v => $l): ?>
            <option value="<?php echo htmlspecialchars($v); ?>"><?php echo htmlspecialchars($l); ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($f['type'] === 'date'): ?>
        <input type="date" class="inv-input inv-filter" data-filter="<?php echo $f['name']; ?>" style="width:140px">
      <?php endif; ?>
    <?php endforeach; ?>

    <select id="inv-per-page" class="inv-input" style="width:90px;cursor:pointer" title="Rows per page">
      <option value="25">25 rows</option>
      <option value="50">50 rows</option>
      <option value="100">100 rows</option>
      <option value="200">200 rows</option>
    </select>

    <button onclick="invExportCsv()" class="btn-inv-secondary" title="Export visible rows to CSV" style="padding:6px 12px;font-size:12px">
      <i class="fa fa-file-csv"></i> Export
    </button>

    <a href="<?php echo $new_url; ?>" class="btn-inv-primary">
      <i class="fa fa-plus"></i> <?php echo $new_btn_label; ?>
    </a>
  </div>

  <!-- Excel-style spreadsheet table -->
  <div class="xls-wrap" id="xls-wrap">
    <table class="xls-table" id="xls-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum" data-col="#">
            #
            <span class="xls-resize-handle"></span>
          </th>
          <?php foreach ($columns as $col): ?>
          <th class="xls-th<?php echo isset($col['right']) ? ' xls-right' : ''; ?>"
              data-col="<?php echo htmlspecialchars($col['key']); ?>"
              style="min-width:<?php echo isset($col['width']) ? (int)$col['width'] : 100; ?>px">
            <span class="xls-th-label"><?php echo $col['label']; ?></span>
            <span class="xls-sort-icon"><i class="fa fa-sort"></i></span>
            <span class="xls-resize-handle"></span>
          </th>
          <?php endforeach; ?>
          <th class="xls-th xls-col-actions">
            ACTIONS
            <span class="xls-resize-handle"></span>
          </th>
        </tr>
      </thead>
      <tbody id="inv-list-body">
        <tr>
          <td colspan="<?php echo count($columns) + 2; ?>">
            <div class="inv-empty">
              <i class="fa fa-spinner fa-spin" style="color:#16a34a"></i>
              <p>Loading records…</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination footer -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="inv-list-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="inv-list-pages" class="inv-pagination"></div>
  </div>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var INV_AJAX_URL = '<?php echo $ajax_url; ?>';
var INV_COLS     = <?php echo json_encode(array_values($columns)); ?>;
var INV_BASE     = '<?php echo admin_url('pos_system'); ?>';
var _csrf_n      = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v      = '<?php echo $this->security->get_csrf_hash(); ?>';
var inv_page = 1, inv_sort_col = '', inv_sort_dir = 'asc', inv_timer;
var inv_all_rows = [], inv_total = 0;

/* ── Load from server ── */
function invListLoad(page) {
    inv_page = page || 1;
    var per  = parseInt($('#inv-per-page').val()) || 25;
    var params = { page: inv_page, per_page: per };
    if (inv_sort_col) { params.sort = inv_sort_col; params.dir = inv_sort_dir; }
    $('.inv-filter').each(function() {
        var v = $(this).val();
        if (v !== '') params[$(this).data('filter')] = v;
    });
    var colspan = INV_COLS.length + 2;
    $('#inv-list-body').html('<tr><td colspan="'+colspan+'"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');

    $.getJSON(INV_AJAX_URL, params, function(r) {
        inv_all_rows = r.rows || [];
        inv_total    = r.total || 0;
        if (!inv_all_rows.length) {
            $('#inv-list-body').html('<tr><td colspan="'+colspan+'"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No records found.</p></div></td></tr>');
            $('#inv-list-info').text('');
            $('#inv-list-pages').html('');
            return;
        }
        renderRows(inv_all_rows);

        var offset = (inv_page - 1) * per;
        var endRow = offset + inv_all_rows.length;
        $('#inv-list-info').html('Showing <strong>'+(offset+1)+'</strong>–<strong>'+endRow+'</strong> of <strong>'+inv_total+'</strong> records');

        var tp = Math.ceil(inv_total / per);
        var pages = '';
        if (tp > 1) {
            if (inv_page > 1) pages += '<a href="#" onclick="invListLoad('+(inv_page-1)+');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
            var start = Math.max(1, inv_page-2), end2 = Math.min(tp, inv_page+2);
            for (var p = start; p <= end2; p++)
                pages += '<a href="#" class="'+(p===inv_page?'active':'')+'" onclick="invListLoad('+p+');return false;">'+p+'</a>';
            if (inv_page < tp) pages += '<a href="#" onclick="invListLoad('+(inv_page+1)+');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
        }
        $('#inv-list-pages').html(pages);
    }).fail(function() {
        $('#inv-list-body').html('<tr><td colspan="'+colspan+'"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load records.</p></div></td></tr>');
    });
}

/* ── Render rows ── */
function renderRows(rows) {
    var per    = parseInt($('#inv-per-page').val()) || 25;
    var offset = (inv_page - 1) * per;
    var html   = '';
    $.each(rows, function(i, row) {
        html += '<tr class="xls-row">';
        html += '<td class="xls-cell xls-col-rownum">'+(offset+i+1)+'</td>';
        $.each(INV_COLS, function(j, col) {
            var val = (row[col.key] !== undefined && row[col.key] !== null)
                ? row[col.key]
                : '<span style="color:#c0ccc6">—</span>';
            var cls = 'xls-cell' + (col.right ? ' xls-right' : '');
            html += '<td class="'+cls+'">'+val+'</td>';
        });
        html += '<td class="xls-cell xls-col-actions">';
        if (row._view_url)   html += '<a href="'+row._view_url+'" class="btn-inv-icon" title="View"><i class="fa fa-eye"></i></a> ';
        if (row._edit_url)   html += '<a href="'+row._edit_url+'" class="btn-inv-icon" title="Edit"><i class="fa fa-pen"></i></a> ';
        if (row._delete_id)  html += '<button class="btn-inv-icon danger" onclick="invListDelete('+row._delete_id+')" title="Delete"><i class="fa fa-trash"></i></button>';
        html += '</td></tr>';
    });
    $('#inv-list-body').html(html);
}

/* ── Delete ── */
function invListDelete(id) {
    if (!confirm('Delete this record? This action cannot be undone.')) return;
    var type = INV_AJAX_URL.split('/').pop();
    var data = {}; data[_csrf_n] = _csrf_v;
    $.post(INV_BASE + '/inv_delete/' + type + '/' + id, data, function(r) {
        if (r.success) { alert_float('success', 'Deleted.'); invListLoad(inv_page); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}

/* ── Client-side column sort ── */
$(document).on('click', '.xls-th', function(e) {
    if ($(e.target).hasClass('xls-resize-handle')) return;
    if ($(this).hasClass('xls-col-rownum') || $(this).hasClass('xls-col-actions')) return;
    var col = $(this).data('col');
    if (inv_sort_col === col) {
        inv_sort_dir = inv_sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        inv_sort_col = col;
        inv_sort_dir = 'asc';
    }
    $('.xls-th .xls-sort-icon').html('<i class="fa fa-sort"></i>');
    $(this).find('.xls-sort-icon').html(
        '<i class="fa fa-sort-'+(inv_sort_dir === 'asc' ? 'up' : 'down')+'"></i>'
    );
    var sorted = inv_all_rows.slice().sort(function(a, b) {
        var av = (a[col] !== null && a[col] !== undefined) ? String(a[col]) : '';
        var bv = (b[col] !== null && b[col] !== undefined) ? String(b[col]) : '';
        av = av.replace(/<[^>]+>/g, '').trim();
        bv = bv.replace(/<[^>]+>/g, '').trim();
        var an = parseFloat(av.replace(/[^0-9.-]/g, '')), bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return inv_sort_dir === 'asc' ? an - bn : bn - an;
        return inv_sort_dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    renderRows(sorted);
});

/* ── Column resize via drag ── */
(function() {
    var th, startX, startW;
    $(document).on('mousedown', '.xls-resize-handle', function(e) {
        th = $(this).closest('.xls-th');
        startX = e.pageX;
        startW = th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.xlsrz', function(ev) {
            var w = Math.max(48, startW + (ev.pageX - startX));
            th.css({ 'min-width': w+'px', 'max-width': w+'px', 'width': w+'px' });
        });
        $(document).on('mouseup.xlsrz', function() {
            $('body').removeClass('xls-resizing');
            $(document).off('.xlsrz');
        });
        e.preventDefault();
        e.stopPropagation();
    });
})();

/* ── Export current page to CSV ── */
function invExportCsv() {
    if (!inv_all_rows.length) { alert('No data to export.'); return; }
    var per = parseInt($('#inv-per-page').val()) || 25;
    var headers = ['#'].concat(INV_COLS.map(function(c) { return c.label; }));
    var lines = [headers.map(function(h) { return '"'+String(h).replace(/"/g,'""')+'"'; }).join(',')];
    inv_all_rows.forEach(function(row, i) {
        var vals = [(inv_page-1)*per + i + 1];
        INV_COLS.forEach(function(col) {
            var v = row[col.key] !== undefined ? String(row[col.key]) : '';
            v = v.replace(/<[^>]+>/g, '').replace(/"/g, '""').trim();
            vals.push('"'+v+'"');
        });
        lines.push(vals.join(','));
    });
    var blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url; a.download = 'inventory_export_'+Date.now()+'.csv'; a.click();
    URL.revokeObjectURL(url);
}

/* ── Init ── */
$('.inv-filter').on('input change', function() {
    clearTimeout(inv_timer);
    inv_timer = setTimeout(function() { invListLoad(1); }, 320);
});
$('#inv-per-page').on('change', function() { invListLoad(1); });
$(function() { invListLoad(1); });
</script>
