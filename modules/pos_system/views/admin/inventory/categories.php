<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'categories',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content">
<div class="inv-card" style="overflow:visible">

  <!-- Toolbar -->
  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1;min-width:0">
      <i class="fa fa-folder-open"></i> Product Categories
    </h4>
    <input type="text" class="inv-input" id="cat-search" placeholder="Search categories…" style="width:200px">
    <button onclick="catModalOpen(null)" class="btn-inv-primary">
      <i class="fa fa-plus"></i> New Category
    </button>
  </div>

  <!-- Table -->
  <div class="xls-wrap" id="xls-wrap">
    <table class="xls-table" id="xls-table">
      <thead>
        <tr>
          <th class="xls-th xls-col-rownum">#<span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:200px"><span class="xls-th-label">Category Name</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:160px"><span class="xls-th-label">Parent Category</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:80px"><span class="xls-th-label">Products</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:80px"><span class="xls-th-label">Sort Order</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:80px"><span class="xls-th-label">Status</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th" style="min-width:120px"><span class="xls-th-label">Description</span><span class="xls-resize-handle"></span></th>
          <th class="xls-th xls-col-actions">ACTIONS<span class="xls-resize-handle"></span></th>
        </tr>
      </thead>
      <tbody id="cat-list-body">
        <tr><td colspan="8"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>
      </tbody>
    </table>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-top:1px solid #edf5f0;background:#f8fdf9">
    <span id="cat-list-info" style="font-size:12px;color:#7a8b80"></span>
    <div id="cat-list-pages" class="inv-pagination"></div>
  </div>
</div>
</div>
</div>
</div>

<!-- ── Category Modal ── -->
<div class="modal fade" id="catModal" tabindex="-1" role="dialog" aria-labelledby="catModalTitle">
  <div class="modal-dialog" role="document" style="max-width:540px">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a5c38;border-radius:4px 4px 0 0">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1">&times;</button>
        <h4 class="modal-title" id="catModalTitle" style="color:#fff;font-size:15px">
          <i class="fa fa-folder-open"></i> <span id="catModalTitleText">New Category</span>
        </h4>
      </div>
      <div class="modal-body" style="padding:20px">
        <div id="catModalAlert" style="display:none;margin-bottom:12px"></div>
        <input type="hidden" id="catId">
        <div style="display:flex;gap:14px;margin-bottom:14px">
          <div style="flex:1">
            <label style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase">Category Name <span style="color:#e53935">*</span></label>
            <input type="text" id="catName" class="form-control" placeholder="e.g. Beverages" style="margin-top:4px">
          </div>
          <div style="width:110px">
            <label style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase">Sort Order</label>
            <input type="number" id="catSortOrder" class="form-control" value="0" min="0" style="margin-top:4px">
          </div>
        </div>
        <div style="margin-bottom:14px">
          <label style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase">Parent Category</label>
          <select id="catParentId" class="form-control" style="margin-top:4px">
            <option value="">— None (Top Level) —</option>
            <?php foreach ($all_categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="margin-bottom:14px">
          <label style="font-size:11px;font-weight:700;color:#6c757d;text-transform:uppercase">Description</label>
          <textarea id="catDescription" class="form-control" rows="3" placeholder="Optional description…" style="margin-top:4px;resize:vertical"></textarea>
        </div>
        <div>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:500">
            <input type="checkbox" id="catIsActive" checked style="width:15px;height:15px;accent-color:#1a5c38">
            Active (visible in item form dropdowns and POS)
          </label>
        </div>
      </div>
      <div class="modal-footer" style="padding:12px 20px;background:#f8f9fa;border-top:1px solid #dee2e6;display:flex;align-items:center;gap:8px;justify-content:flex-end">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="catSaveBtn" class="btn" style="background:#1a5c38;color:#fff;font-weight:600;min-width:100px">
          <i class="fa fa-check"></i> Save Category
        </button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
var CAT_SAVE_URL  = '<?php echo $save_url; ?>';
var CAT_AJAX_URL  = '<?php echo $ajax_url; ?>';
var CAT_DEL_URL   = '<?php echo admin_url('pos_system/inv_delete/categories'); ?>';
var _csrf_n = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v = '<?php echo $this->security->get_csrf_hash(); ?>';

// All categories data for parent dropdown sync
var ALL_CATS = <?php echo json_encode(array_map(function($c){ return ['id'=>(int)$c['id'],'name'=>$c['name'],'parent_id'=>(int)($c['parent_id']??0)]; }, $all_categories)); ?>;

var catPage = 1, catTimer, catAllRows = [], catTotal = 0;
var PER = 25;

/* ── Load ── */
function catLoad(page) {
    catPage = page || 1;
    var params = { page: catPage, per_page: PER };
    var q = $('#cat-search').val();
    if (q) params.search = q;
    var offset = (catPage - 1) * PER;
    var colspan = 8;
    $('#cat-list-body').html('<tr><td colspan="'+colspan+'"><div class="inv-empty"><i class="fa fa-spinner fa-spin" style="color:#16a34a"></i><p>Loading…</p></div></td></tr>');

    $.getJSON(CAT_AJAX_URL, params, function(r) {
        catAllRows = r.rows || [];
        catTotal   = r.total || 0;
        if (!catAllRows.length) {
            $('#cat-list-body').html('<tr><td colspan="'+colspan+'"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No categories found. Click <strong>New Category</strong> to add one.</p></div></td></tr>');
            $('#cat-list-info').text('');
            $('#cat-list-pages').html('');
            return;
        }
        catRender(catAllRows, offset);
        $('#cat-list-info').html('Showing <strong>'+(offset+1)+'</strong>–<strong>'+(offset+catAllRows.length)+'</strong> of <strong>'+catTotal+'</strong>');
        catPages();
    }).fail(function() {
        $('#cat-list-body').html('<tr><td colspan="8"><div class="inv-empty"><i class="fa fa-exclamation-triangle" style="color:#e74c3c"></i><p>Failed to load.</p></div></td></tr>');
    });
}

function catRender(rows, offset) {
    var html = '';
    rows.forEach(function(r, i) {
        html += '<tr class="xls-row">';
        html += '<td class="xls-cell xls-col-rownum">'+(offset+i+1)+'</td>';
        html += '<td class="xls-cell"><strong>'+esc(r.name)+'</strong></td>';
        html += '<td class="xls-cell">'+(r.parent || '<span style="color:#aaa">—</span>')+'</td>';
        html += '<td class="xls-cell xls-right">'+(r.product_count||0)+'</td>';
        html += '<td class="xls-cell xls-right">'+(r.sort_order||0)+'</td>';
        html += '<td class="xls-cell">'+(r.is_active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>')+'</td>';
        var desc = r.description ? esc(r.description.substring(0,60))+(r.description.length>60?'…':'') : '<span style="color:#aaa">—</span>';
        html += '<td class="xls-cell">'+desc+'</td>';
        html += '<td class="xls-cell xls-col-actions">';
        html += '<button class="btn-inv-icon" onclick="catEdit('+r.id+')" title="Edit"><i class="fa fa-pen"></i></button> ';
        if (!r.product_count || r.product_count == 0) {
            html += '<button class="btn-inv-icon danger" onclick="catDelete('+r.id+',\''+esc(r.name)+'\')" title="Delete"><i class="fa fa-trash"></i></button>';
        }
        html += '</td></tr>';
    });
    $('#cat-list-body').html(html);
}

function catPages() {
    var tp = Math.ceil(catTotal / PER);
    var pages = '';
    if (tp > 1) {
        if (catPage > 1) pages += '<a href="#" onclick="catLoad('+(catPage-1)+');return false;"><i class="fa fa-chevron-left" style="font-size:10px"></i></a>';
        var start = Math.max(1, catPage-2), end = Math.min(tp, catPage+2);
        for (var p = start; p <= end; p++)
            pages += '<a href="#" class="'+(p===catPage?'active':'')+'" onclick="catLoad('+p+');return false;">'+p+'</a>';
        if (catPage < tp) pages += '<a href="#" onclick="catLoad('+(catPage+1)+');return false;"><i class="fa fa-chevron-right" style="font-size:10px"></i></a>';
    }
    $('#cat-list-pages').html(pages);
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Modal: New ── */
window.catModalOpen = function(id) {
    $('#catModalTitleText').text(id ? 'Edit Category' : 'New Category');
    $('#catId').val('');
    $('#catName').val('');
    $('#catParentId').val('');
    $('#catSortOrder').val(0);
    $('#catDescription').val('');
    $('#catIsActive').prop('checked', true);
    $('#catModalAlert').hide();

    if (id) {
        // load row data
        var row = catAllRows.find(function(r){ return r.id == id; });
        if (row) {
            $('#catId').val(row.id);
            $('#catName').val(row.name);
            $('#catParentId').val(row.parent_id || '');
            $('#catSortOrder').val(row.sort_order || 0);
            $('#catDescription').val(row.description || '');
            $('#catIsActive').prop('checked', !!row.is_active);
        }
    }
    $('#catModal').modal('show');
    setTimeout(function(){ $('#catName').focus(); }, 300);
};

/* ── Modal: Edit (called from row button) ── */
window.catEdit = function(id) { catModalOpen(id); };

/* ── Modal: Save ── */
$('#catSaveBtn').on('click', function() {
    var name = $.trim($('#catName').val());
    if (!name) {
        $('#catModalAlert').attr('class','alert alert-danger').text('Category name is required.').show();
        $('#catName').focus(); return;
    }
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');
    $('#catModalAlert').hide();

    var data = {
        id:          $('#catId').val(),
        name:        name,
        parent_id:   $('#catParentId').val(),
        sort_order:  $('#catSortOrder').val() || 0,
        description: $('#catDescription').val(),
        is_active:   $('#catIsActive').is(':checked') ? 1 : 0
    };
    data[_csrf_n] = _csrf_v;

    $.post(CAT_SAVE_URL, data, function(r) {
        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Category');
        if (r.success) {
            $('#catModal').modal('hide');
            alert_float('success', r.message || 'Category saved.');
            catLoad(catPage);
        } else {
            $('#catModalAlert').attr('class','alert alert-danger').text(r.error || 'Save failed.').show();
        }
    }, 'json').fail(function() {
        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Category');
        $('#catModalAlert').attr('class','alert alert-danger').text('Server error. Try again.').show();
    });
});

/* ── Delete ── */
window.catDelete = function(id, name) {
    if (!confirm('Delete category "' + name + '"? This cannot be undone.')) return;
    var data = {}; data[_csrf_n] = _csrf_v;
    $.post(CAT_DEL_URL + '/' + id, data, function(r) {
        if (r.success) { alert_float('success', 'Category deleted.'); catLoad(catPage); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
};

/* ── Column resize ── */
(function() {
    var th, startX, startW;
    $(document).on('mousedown', '.xls-resize-handle', function(e) {
        th = $(this).closest('.xls-th');
        startX = e.pageX; startW = th.outerWidth();
        $('body').addClass('xls-resizing');
        $(document).on('mousemove.xlsrz', function(ev) {
            th.css({ 'min-width': Math.max(48, startW+(ev.pageX-startX))+'px' });
        });
        $(document).on('mouseup.xlsrz', function() {
            $('body').removeClass('xls-resizing');
            $(document).off('.xlsrz');
        });
        e.preventDefault(); e.stopPropagation();
    });
})();

/* ── Init ── */
$('#cat-search').on('input', function() {
    clearTimeout(catTimer);
    catTimer = setTimeout(function(){ catLoad(1); }, 320);
});

// Submit modal on Enter in name field
$('#catName').on('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#catSaveBtn').trigger('click'); }
});

$(function() { catLoad(1); });
</script>
