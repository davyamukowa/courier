<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'config_brands',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="panel_s">
      <div class="panel-body" style="padding:0">
        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #f0f0f0">
          <h4 style="margin:0;font-size:15px;font-weight:600;color:#2c3e50;flex:1">
            <i class="fa fa-trademark" style="color:#3498db;margin-right:6px"></i> Brands
          </h4>
          <input type="text" id="brand-search" class="form-control input-sm" placeholder="Search…" style="width:180px">
          <button class="btn btn-sm btn-primary" onclick="brandModal(0)"><i class="fa fa-plus"></i> New Brand</button>
        </div>
        <table class="table table-hover" style="margin:0">
          <thead><tr style="background:#f8f9fa">
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">BRAND NAME</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">DESCRIPTION</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">PRODUCTS</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">STATUS</th>
            <th style="width:80px"></th>
          </tr></thead>
          <tbody id="brand-body"><tr><td colspan="5" class="text-center" style="padding:24px;color:#95a5a6"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#2c3e6a;color:#fff;padding:12px 16px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
        <h4 class="modal-title" style="font-size:14px;font-weight:600" id="brandModalTitle">New Brand</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="brand-id">
        <div class="row">
          <div class="col-md-8">
            <div class="form-group"><label>Brand Name <span class="text-danger">*</span></label><input type="text" id="brand-name" class="form-control"></div>
          </div>
          <div class="col-md-4">
            <div class="form-group"><label>Active</label>
              <select id="brand-active" class="form-control"><option value="1">Yes</option><option value="0">No</option></select>
            </div>
          </div>
        </div>
        <div class="form-group"><label>Description</label><textarea id="brand-desc" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="brandSave()"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
var BRAND_AJAX = '<?php echo admin_url('pos_system/inv_ajax/brands'); ?>';
var BRAND_SAVE = '<?php echo admin_url('pos_system/inv_save/brand'); ?>';
var BRAND_DEL  = '<?php echo admin_url('pos_system/inv_delete/brand'); ?>';
var _csrf_n    = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v    = '<?php echo $this->security->get_csrf_hash(); ?>';
var brand_timer;

function brandLoad() {
    $.getJSON(BRAND_AJAX, {search: $('#brand-search').val()}, function(r) {
        if (!r.rows || !r.rows.length) {
            $('#brand-body').html('<tr><td colspan="5" class="text-center" style="padding:24px;color:#95a5a6">No brands yet.</td></tr>'); return;
        }
        var html = '';
        $.each(r.rows, function(i, b) {
            var status = b.is_active == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
            html += '<tr>';
            html += '<td style="padding:9px 12px;font-weight:500">' + b.name + '</td>';
            html += '<td style="padding:9px 12px;font-size:12px;color:#7f8c8d">' + (b.description || '—') + '</td>';
            html += '<td style="padding:9px 12px">' + (b.product_count || 0) + '</td>';
            html += '<td style="padding:9px 12px">' + status + '</td>';
            html += '<td style="padding:9px 12px"><button class="btn btn-xs btn-default" onclick="brandModal(' + b.id + ',\'' + b.name.replace(/'/g,"\\'")+'\',\'' + (b.description||'').replace(/'/g,"\\'")+'\','+ b.is_active +')"><i class="fa fa-edit"></i></button> ';
            html += '<button class="btn btn-xs btn-danger" onclick="brandDel(' + b.id + ')"><i class="fa fa-trash"></i></button></td>';
            html += '</tr>';
        });
        $('#brand-body').html(html);
    });
}

function brandModal(id, name, desc, active) {
    $('#brand-id').val(id || 0);
    $('#brand-name').val(name || '');
    $('#brand-desc').val(desc || '');
    $('#brand-active').val(active !== undefined ? active : 1);
    $('#brandModalTitle').text(id ? 'Edit Brand' : 'New Brand');
    $('#brandModal').modal('show');
}

function brandSave() {
    var d = {}; d[_csrf_n] = _csrf_v;
    d.id = $('#brand-id').val(); d.name = $('#brand-name').val();
    d.description = $('#brand-desc').val(); d.is_active = $('#brand-active').val();
    if (!d.name) { alert('Brand name is required.'); return; }
    $.post(BRAND_SAVE, d, function(r) {
        if (r.success) { $('#brandModal').modal('hide'); brandLoad(); alert_float('success', r.message); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function brandDel(id) {
    if (!confirm('Delete this brand?')) return;
    var d = {}; d[_csrf_n] = _csrf_v;
    $.post(BRAND_DEL + '/' + id, d, function(r) {
        if (r.success) { brandLoad(); alert_float('success', 'Deleted.'); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}

$('#brand-search').on('input', function() { clearTimeout(brand_timer); brand_timer = setTimeout(brandLoad, 300); });
$(function(){ brandLoad(); });
</script>
