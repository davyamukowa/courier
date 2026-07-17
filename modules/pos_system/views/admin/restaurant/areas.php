<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/restaurant/_nav', ['rest_section'=>'areas']); ?>
<div style="padding:24px">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <h4 style="margin:0;font-size:17px;font-weight:700"><i class="fa fa-fire-alt" style="color:#16a34a;margin-right:6px"></i> Production Areas</h4>
  <button class="btn btn-primary btn-sm" onclick="areaModal()"><i class="fa fa-plus"></i> Add Area</button>
</div>

<div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden">
  <table class="table table-hover" style="margin:0">
    <thead style="background:#f8fafc">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Type</th>
        <th>Branch</th>
        <th>Order</th>
        <th>Status</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($areas)): ?>
      <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8">No production areas configured. <a href="#" onclick="areaModal()">Add one</a>.</td></tr>
    <?php else: foreach ($areas as $i => $a): ?>
      <tr>
        <td><?php echo $i + 1; ?></td>
        <td><strong><?php echo htmlspecialchars($a['name']); ?></strong></td>
        <td>
          <?php
          $type_icons = ['kitchen'=>'fa-fire','bar'=>'fa-cocktail','bakery'=>'fa-birthday-cake','grill'=>'fa-grill-hot','other'=>'fa-cog'];
          $icon = $type_icons[$a['type']] ?? 'fa-cog';
          echo '<span class="label label-default"><i class="fa ' . $icon . '"></i> ' . ucfirst($a['type']) . '</span>';
          ?>
        </td>
        <td style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($a['branch_id'] ?? '—'); ?></td>
        <td style="font-size:12px;color:#64748b"><?php echo $a['display_order']; ?></td>
        <td><?php echo $a['is_active'] ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
        <td style="text-align:right">
          <button class="btn btn-xs btn-default" onclick='areaModal(<?php echo htmlspecialchars(json_encode($a)); ?>)'><i class="fa fa-edit"></i> Edit</button>
          <button class="btn btn-xs btn-danger" onclick="areaDelete(<?php echo $a['id']; ?>)"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

</div>
</div>
</div>

<!-- Area Modal -->
<div class="modal fade" id="areaModal" tabindex="-1">
 <div class="modal-dialog">
  <div class="modal-content">
   <div class="modal-header" style="background:#1e293b;color:#fff">
     <button class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
     <h4 class="modal-title"><i class="fa fa-fire-alt" style="margin-right:8px"></i><span id="areaModalTitle">New Production Area</span></h4>
   </div>
   <div class="modal-body">
    <form id="areaForm">
      <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
      <input type="hidden" name="id" id="areaId">
      <div class="form-group">
        <label>Area Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="areaName" class="form-control" required placeholder="e.g. Main Kitchen">
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Type</label>
            <select name="type" id="areaType" class="form-control">
              <option value="kitchen">Kitchen</option>
              <option value="bar">Bar</option>
              <option value="bakery">Bakery</option>
              <option value="grill">Grill</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Display Order</label>
            <input type="number" name="display_order" id="areaOrder" class="form-control" value="0" min="0">
          </div>
        </div>
      </div>
      <div class="checkbox"><label>
        <input type="checkbox" name="is_active" id="areaActive" value="1" checked> Active
      </label></div>
    </form>
   </div>
   <div class="modal-footer">
     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
     <button type="button" class="btn btn-primary" onclick="areaSave()"><i class="fa fa-save"></i> Save Area</button>
   </div>
  </div>
 </div>
</div>

<?php init_tail(); ?>
<script>
var AREA_URL = '<?php echo admin_url('pos_system/restaurant/areas'); ?>';

function areaModal(a) {
    a = a || {};
    $('#areaModalTitle').text(a.id ? 'Edit Production Area' : 'New Production Area');
    $('#areaId').val(a.id || '');
    $('#areaName').val(a.name || '');
    $('#areaType').val(a.type || 'kitchen');
    $('#areaOrder').val(a.display_order || 0);
    $('#areaActive').prop('checked', a.is_active != '0');
    $('#areaModal').modal('show');
}

function areaSave() {
    $.post(AREA_URL, $('#areaForm').serialize(), function(r) {
        if (r.success) { $('#areaModal').modal('hide'); alert_float('success', r.message); setTimeout(function(){location.reload();}, 800); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function areaDelete(id) {
    if (!confirm('Delete this production area?')) return;
    $.post('<?php echo admin_url('pos_system/restaurant_delete_area'); ?>', {id: id, <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'}, function(r) {
        if (r.success) { alert_float('success', 'Deleted.'); setTimeout(function(){location.reload();}, 600); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}
</script>
