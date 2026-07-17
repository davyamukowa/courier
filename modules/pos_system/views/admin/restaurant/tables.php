<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/restaurant/_nav', ['rest_section'=>'tables']); ?>
<div style="padding:24px">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <h4 style="margin:0;font-size:17px;font-weight:700"><i class="fa fa-chair" style="color:#16a34a;margin-right:6px"></i> Table Management</h4>
  <button class="btn btn-primary btn-sm" onclick="tableModal()"><i class="fa fa-plus"></i> Add Table</button>
</div>

<!-- Visual table layout -->
<?php
$floors = [];
foreach ($tables as $t) { $floors[$t['floor'] ?: 'Ground Floor'][] = $t; }
?>
<?php foreach ($floors as $floor => $ftables): ?>
<div style="margin-bottom:20px">
  <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px"><?php echo htmlspecialchars($floor); ?></div>
  <div style="display:flex;flex-wrap:wrap;gap:12px">
    <?php foreach ($ftables as $t):
      $statusColors = ['free'=>'#16a34a','occupied'=>'#dc2626','reserved'=>'#d97706'];
      $bgColors     = ['free'=>'#f0fdf4','occupied'=>'#fef2f2','reserved'=>'#fffbeb'];
      $bc           = $statusColors[$t['status']] ?? '#64748b';
      $bg           = $bgColors[$t['status']]     ?? '#f8fafc';
    ?>
    <div style="width:110px;background:<?php echo $bg; ?>;border:2px solid <?php echo $bc; ?>;border-radius:10px;padding:14px 10px;text-align:center;cursor:pointer;transition:.15s" onclick='tableModal(<?php echo htmlspecialchars(json_encode($t)); ?>)'>
      <div style="font-size:22px;font-weight:800;color:<?php echo $bc; ?>"><?php echo htmlspecialchars($t['table_number']); ?></div>
      <?php if ($t['name']): ?><div style="font-size:10px;color:#64748b;margin-top:1px"><?php echo htmlspecialchars($t['name']); ?></div><?php endif; ?>
      <div style="font-size:10px;font-weight:600;color:<?php echo $bc; ?>;margin-top:4px"><?php echo ucfirst($t['status']); ?></div>
      <div style="font-size:10px;color:#94a3b8;margin-top:2px"><i class="fa fa-user"></i> <?php echo $t['seats']; ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php if (empty($tables)): ?>
  <div style="text-align:center;padding:48px;color:#94a3b8">
    <i class="fa fa-chair" style="font-size:32px;display:block;margin-bottom:12px"></i>
    No tables configured yet. <a href="#" onclick="tableModal()">Add your first table</a>.
  </div>
<?php endif; ?>

<!-- Table list -->
<div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden;margin-top:20px">
  <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;font-weight:600;font-size:13px">All Tables (<?php echo count($tables); ?>)</div>
  <table class="table table-hover" style="margin:0">
    <thead style="background:#f8fafc">
      <tr>
        <th>#</th><th>Table #</th><th>Name</th><th>Floor</th><th>Seats</th><th>Status</th><th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($tables)): ?>
      <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8">None</td></tr>
    <?php else: foreach ($tables as $i => $t): ?>
      <tr>
        <td><?php echo $i + 1; ?></td>
        <td><strong><?php echo htmlspecialchars($t['table_number']); ?></strong></td>
        <td style="color:#64748b"><?php echo htmlspecialchars($t['name'] ?? ''); ?></td>
        <td style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($t['floor'] ?: 'Ground Floor'); ?></td>
        <td><?php echo $t['seats']; ?></td>
        <td>
          <?php $sbadge = ['free'=>'success','occupied'=>'danger','reserved'=>'warning'];
          echo '<span class="label label-' . ($sbadge[$t['status']] ?? 'default') . '">' . ucfirst($t['status']) . '</span>'; ?>
        </td>
        <td style="text-align:right">
          <button class="btn btn-xs btn-default" onclick='tableModal(<?php echo htmlspecialchars(json_encode($t)); ?>)'><i class="fa fa-edit"></i> Edit</button>
          <button class="btn btn-xs btn-danger" onclick="tableDelete(<?php echo $t['id']; ?>)"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

</div>
</div>
</div>

<!-- Table Modal -->
<div class="modal fade" id="tableModal" tabindex="-1">
 <div class="modal-dialog">
  <div class="modal-content">
   <div class="modal-header" style="background:#1e293b;color:#fff">
     <button class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
     <h4 class="modal-title"><i class="fa fa-chair" style="margin-right:8px"></i><span id="tableModalTitle">New Table</span></h4>
   </div>
   <div class="modal-body">
    <form id="tableForm">
      <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
      <input type="hidden" name="id" id="tableId">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Table Number <span class="text-danger">*</span></label>
            <input type="text" name="table_number" id="tableNumber" class="form-control" required placeholder="T1">
          </div>
        </div>
        <div class="col-md-8">
          <div class="form-group">
            <label>Table Name / Description</label>
            <input type="text" name="name" id="tableName" class="form-control" placeholder="e.g. Window Table">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Floor / Section</label>
            <input type="text" name="floor" id="tableFloor" class="form-control" placeholder="Ground Floor">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Seats</label>
            <input type="number" name="seats" id="tableSeats" class="form-control" value="4" min="1" max="50">
          </div>
        </div>
      </div>
      <div class="checkbox"><label>
        <input type="checkbox" name="is_active" id="tableActive" value="1" checked> Active
      </label></div>
    </form>
   </div>
   <div class="modal-footer">
     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
     <button type="button" class="btn btn-primary" onclick="tableSave()"><i class="fa fa-save"></i> Save Table</button>
   </div>
  </div>
 </div>
</div>

<?php init_tail(); ?>
<script>
var TABLE_URL = '<?php echo admin_url('pos_system/restaurant/tables'); ?>';

function tableModal(t) {
    t = t || {};
    $('#tableModalTitle').text(t.id ? 'Edit Table' : 'New Table');
    $('#tableId').val(t.id || '');
    $('#tableNumber').val(t.table_number || '');
    $('#tableName').val(t.name || '');
    $('#tableFloor').val(t.floor || '');
    $('#tableSeats').val(t.seats || 4);
    $('#tableActive').prop('checked', t.is_active != '0');
    $('#tableModal').modal('show');
}

function tableSave() {
    $.post(TABLE_URL, $('#tableForm').serialize(), function(r) {
        if (r.success) { $('#tableModal').modal('hide'); alert_float('success', r.message); setTimeout(function(){location.reload();}, 800); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function tableDelete(id) {
    if (!confirm('Delete this table?')) return;
    $.post('<?php echo admin_url('pos_system/restaurant_delete_table'); ?>', {id: id, <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'}, function(r) {
        if (r.success) { alert_float('success', 'Deleted.'); setTimeout(function(){location.reload();}, 600); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}
</script>
