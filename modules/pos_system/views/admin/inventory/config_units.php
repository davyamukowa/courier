<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'config_units',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <div class="panel_s">
      <div class="panel-body" style="padding:0">
        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #f0f0f0">
          <h4 style="margin:0;font-size:15px;font-weight:600;color:#2c3e50;flex:1">
            <i class="fa fa-ruler" style="color:#3498db;margin-right:6px"></i> Units of Measure
          </h4>
          <button class="btn btn-sm btn-primary" onclick="unitModal(0)"><i class="fa fa-plus"></i> New Unit</button>
        </div>
        <table class="table table-hover" style="margin:0">
          <thead><tr style="background:#f8f9fa">
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">NAME</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">SYMBOL</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">PRODUCTS</th>
            <th style="width:80px"></th>
          </tr></thead>
          <tbody id="unit-body"><tr><td colspan="4" class="text-center" style="padding:24px;color:#95a5a6"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="unitModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background:#2c3e6a;color:#fff;padding:12px 16px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
        <h4 class="modal-title" style="font-size:14px;font-weight:600" id="unitModalTitle">New Unit of Measure</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="unit-id">
        <div class="form-group"><label>Name <span class="text-danger">*</span></label><input type="text" id="unit-name" class="form-control" placeholder="e.g. Kilogram"></div>
        <div class="form-group"><label>Symbol</label><input type="text" id="unit-sym" class="form-control" placeholder="e.g. kg"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="unitSave()"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
var UNIT_AJAX = '<?php echo admin_url('pos_system/inv_ajax/units'); ?>';
var UNIT_SAVE = '<?php echo admin_url('pos_system/inv_save/unit'); ?>';
var UNIT_DEL  = '<?php echo admin_url('pos_system/inv_delete/unit'); ?>';
var _csrf_n   = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v   = '<?php echo $this->security->get_csrf_hash(); ?>';

function unitLoad() {
    $.getJSON(UNIT_AJAX, function(r) {
        if (!r.rows || !r.rows.length) {
            $('#unit-body').html('<tr><td colspan="4" class="text-center" style="padding:24px;color:#95a5a6">No units yet.</td></tr>'); return;
        }
        var html = '';
        $.each(r.rows, function(i, u) {
            html += '<tr><td style="padding:9px 12px;font-weight:500">' + u.name + '</td>';
            html += '<td style="padding:9px 12px;color:#7f8c8d">' + (u.symbol || '—') + '</td>';
            html += '<td style="padding:9px 12px">' + (u.item_count || 0) + '</td>';
            html += '<td style="padding:9px 12px"><button class="btn btn-xs btn-default" onclick="unitModal(' + u.id + ',\'' + u.name + '\',\'' + (u.symbol||'') + '\')"><i class="fa fa-edit"></i></button> ';
            html += '<button class="btn btn-xs btn-danger" onclick="unitDel(' + u.id + ')"><i class="fa fa-trash"></i></button></td></tr>';
        });
        $('#unit-body').html(html);
    });
}

function unitModal(id, name, sym) {
    $('#unit-id').val(id || 0);
    $('#unit-name').val(name || '');
    $('#unit-sym').val(sym || '');
    $('#unitModalTitle').text(id ? 'Edit Unit' : 'New Unit of Measure');
    $('#unitModal').modal('show');
}

function unitSave() {
    var d = {}; d[_csrf_n] = _csrf_v;
    d.id = $('#unit-id').val(); d.name = $('#unit-name').val(); d.symbol = $('#unit-sym').val();
    if (!d.name) { alert('Name is required.'); return; }
    $.post(UNIT_SAVE, d, function(r) {
        if (r.success) { $('#unitModal').modal('hide'); unitLoad(); alert_float('success', r.message); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function unitDel(id) {
    if (!confirm('Delete this unit?')) return;
    var d = {}; d[_csrf_n] = _csrf_v;
    $.post(UNIT_DEL + '/' + id, d, function(r) {
        if (r.success) { unitLoad(); alert_float('success', 'Deleted.'); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}
$(function(){ unitLoad(); });
</script>
