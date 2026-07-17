<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'config_suppliers',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">
<div class="row">
  <div class="col-md-12">
    <div class="panel_s">
      <div class="panel-body" style="padding:0">
        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #f0f0f0;flex-wrap:wrap">
          <h4 style="margin:0;font-size:15px;font-weight:600;color:#2c3e50;flex:1">
            <i class="fa fa-handshake" style="color:#3498db;margin-right:6px"></i> Suppliers
          </h4>
          <input type="text" id="sup-search" class="form-control input-sm" placeholder="Search…" style="width:200px">
          <select id="sup-status" class="form-control input-sm" style="width:130px">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <button class="btn btn-sm btn-primary" onclick="supModal(0)"><i class="fa fa-plus"></i> New Supplier</button>
        </div>
        <table class="table table-hover" style="margin:0">
          <thead><tr style="background:#f8f9fa">
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">SUPPLIER</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">CONTACT</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">EMAIL</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">PHONE</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">TAX PIN</th>
            <th style="padding:10px 12px;font-size:11px;color:#7f8c8d;font-weight:600">STATUS</th>
            <th style="width:80px"></th>
          </tr></thead>
          <tbody id="sup-body"><tr><td colspan="7" class="text-center" style="padding:24px;color:#95a5a6"><i class="fa fa-spinner fa-spin"></i></td></tr></tbody>
        </table>
        <div style="padding:12px 16px;border-top:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between">
          <span id="sup-info" style="font-size:12px;color:#7f8c8d"></span>
          <div id="sup-pages"></div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="supModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#2c3e6a;color:#fff;padding:12px 16px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
        <h4 class="modal-title" style="font-size:14px;font-weight:600" id="supModalTitle">New Supplier</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sup-id">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group"><label>Supplier Name <span class="text-danger">*</span></label><input type="text" id="sup-name" class="form-control"></div>
          </div>
          <div class="col-md-4">
            <div class="form-group"><label>Contact Person</label><input type="text" id="sup-contact" class="form-control"></div>
          </div>
          <div class="col-md-2">
            <div class="form-group"><label>Active</label>
              <select id="sup-active" class="form-control"><option value="1">Yes</option><option value="0">No</option></select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group"><label>Email</label><input type="email" id="sup-email" class="form-control"></div>
          </div>
          <div class="col-md-4">
            <div class="form-group"><label>Phone</label><input type="text" id="sup-phone" class="form-control"></div>
          </div>
          <div class="col-md-4">
            <div class="form-group"><label>Tax PIN / KRA PIN</label><input type="text" id="sup-pin" class="form-control"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-8">
            <div class="form-group"><label>Address</label><textarea id="sup-addr" class="form-control" rows="2"></textarea></div>
          </div>
          <div class="col-md-4">
            <div class="form-group"><label>Payment Terms</label>
              <select id="sup-terms" class="form-control">
                <option value="">— Select —</option>
                <option value="cash">Cash on Delivery</option>
                <option value="net7">Net 7 Days</option>
                <option value="net14">Net 14 Days</option>
                <option value="net30">Net 30 Days</option>
                <option value="net60">Net 60 Days</option>
                <option value="prepaid">Prepaid</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="supSave()"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
var SUP_AJAX = '<?php echo admin_url('pos_system/inv_ajax/suppliers'); ?>';
var SUP_SAVE = '<?php echo admin_url('pos_system/inv_save/supplier'); ?>';
var SUP_DEL  = '<?php echo admin_url('pos_system/inv_delete/supplier'); ?>';
var _csrf_n  = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v  = '<?php echo $this->security->get_csrf_hash(); ?>';
var sup_page = 1, sup_timer;

function supLoad(page) {
    sup_page = page || 1;
    $.getJSON(SUP_AJAX, {page: sup_page, search: $('#sup-search').val(), is_active: $('#sup-status').val()}, function(r) {
        if (!r.rows || !r.rows.length) {
            $('#sup-body').html('<tr><td colspan="7" class="text-center" style="padding:30px;color:#95a5a6"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>No suppliers yet.</td></tr>');
            $('#sup-info').text(''); $('#sup-pages').html(''); return;
        }
        var html = '';
        $.each(r.rows, function(i, s) {
            var status = s.is_active == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
            html += '<tr>';
            html += '<td style="padding:9px 12px;font-weight:500">' + s.name + '</td>';
            html += '<td style="padding:9px 12px;font-size:12px">' + (s.contact_person || '—') + '</td>';
            html += '<td style="padding:9px 12px;font-size:12px">' + (s.email ? '<a href="mailto:'+s.email+'">'+s.email+'</a>' : '—') + '</td>';
            html += '<td style="padding:9px 12px;font-size:12px">' + (s.phone || '—') + '</td>';
            html += '<td style="padding:9px 12px;font-size:12px;font-family:monospace">' + (s.tax_pin || '—') + '</td>';
            html += '<td style="padding:9px 12px">' + status + '</td>';
            html += '<td style="padding:9px 12px"><button class="btn btn-xs btn-default" onclick="supEdit(' + s.id + ')"><i class="fa fa-edit"></i></button> ';
            html += '<button class="btn btn-xs btn-danger" onclick="supDel(' + s.id + ')"><i class="fa fa-trash"></i></button></td>';
            html += '</tr>';
        });
        $('#sup-body').html(html);
        $('#sup-info').text('Showing ' + r.rows.length + ' of ' + r.total);
        var tp = Math.ceil(r.total / 25), pages = '';
        if (tp > 1) { pages = '<ul class="pagination pagination-sm" style="margin:0">'; for (var p=1;p<=tp;p++) pages+='<li'+(p==sup_page?' class="active"':'')+'>​<a href="#" onclick="supLoad('+p+');return false;">'+p+'</a></li>'; pages+='</ul>'; }
        $('#sup-pages').html(pages);
        window._sup_rows = r.rows;
    });
}

function supModal(id) {
    $('#sup-id').val(id || 0);
    $('#sup-name,#sup-contact,#sup-email,#sup-phone,#sup-pin,#sup-addr').val('');
    $('#sup-active').val('1'); $('#sup-terms').val('');
    $('#supModalTitle').text(id ? 'Edit Supplier' : 'New Supplier');
    $('#supModal').modal('show');
}

function supEdit(id) {
    var row = (window._sup_rows || []).find(function(r){ return r.id == id; });
    if (!row) { supModal(id); return; }
    $('#sup-id').val(id);
    $('#sup-name').val(row.name); $('#sup-contact').val(row.contact_person || '');
    $('#sup-email').val(row.email || ''); $('#sup-phone').val(row.phone || '');
    $('#sup-pin').val(row.tax_pin || ''); $('#sup-addr').val(row.address || '');
    $('#sup-active').val(row.is_active); $('#sup-terms').val(row.payment_terms || '');
    $('#supModalTitle').text('Edit Supplier'); $('#supModal').modal('show');
}

function supSave() {
    var d = {}; d[_csrf_n] = _csrf_v;
    d.id = $('#sup-id').val(); d.name = $('#sup-name').val(); d.contact_person = $('#sup-contact').val();
    d.email = $('#sup-email').val(); d.phone = $('#sup-phone').val(); d.tax_pin = $('#sup-pin').val();
    d.address = $('#sup-addr').val(); d.payment_terms = $('#sup-terms').val(); d.is_active = $('#sup-active').val();
    if (!d.name) { alert('Supplier name is required.'); return; }
    $.post(SUP_SAVE, d, function(r) {
        if (r.success) { $('#supModal').modal('hide'); supLoad(sup_page); alert_float('success', r.message); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function supDel(id) {
    if (!confirm('Delete this supplier?')) return;
    var d = {}; d[_csrf_n] = _csrf_v;
    $.post(SUP_DEL + '/' + id, d, function(r) {
        if (r.success) { supLoad(sup_page); alert_float('success', 'Deleted.'); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}

$('#sup-search, #sup-status').on('input change', function(){ clearTimeout(sup_timer); sup_timer = setTimeout(function(){ supLoad(1); }, 300); });
$(function(){ supLoad(1); });
</script>
