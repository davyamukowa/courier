<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Configuration</a> &rsaquo; Asset Models
    </div>

    <div class="xb-header-toolbar">
        <div><h3>Asset Models</h3></div>
        <div>
            <button type="button" class="btn btn-primary xb-btn-primary btn-sm" data-toggle="modal" data-target="#modal-asset-model">
                <i class="fa fa-plus"></i> New Model
            </button>
        </div>
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>Name</th>
                        <th>Depreciation Method</th>
                        <th>Useful Life</th>
                        <th>Period</th>
                        <th class="text-center">Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($asset_models)): ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">
                        No asset models defined. Create one to use as a template for fixed assets.
                    </td></tr>
                    <?php else: foreach($asset_models as $m): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($m->name); ?></strong></td>
                        <td><?php echo ucfirst($m->method); ?></td>
                        <td><?php echo $m->method_number; ?> years</td>
                        <td><?php echo $m->method_period==1?'Monthly':($m->method_period==3?'Quarterly':'Annually'); ?></td>
                        <td class="text-center">
                            <span class="label <?php echo $m->active?'label-success':'label-default'; ?>">
                                <?php echo $m->active?'Active':'Inactive'; ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-default btn-xs"
                                    onclick="editAssetModel(<?php echo htmlspecialchars(json_encode($m)); ?>)">
                                <i class="fa fa-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New/Edit Asset Model -->
<div class="modal fade" id="modal-asset-model" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a6b3a;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title" id="modal-am-title">New Asset Model</h4>
            </div>
            <?php echo form_open(admin_url('xetuu_books/ajax/save_asset_model'), ['id'=>'asset-model-form']); ?>
            <div class="modal-body">
                <input type="hidden" name="id" id="am-id" value="">
                <div class="form-group">
                    <label>Model Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="am-name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Depreciation Method</label>
                            <select name="method" id="am-method" class="form-control">
                                <option value="linear">Straight Line (Linear)</option>
                                <option value="degressive">Declining Balance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Useful Life (Years)</label>
                            <input type="number" name="method_number" id="am-method-number" class="form-control" value="5" min="1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Computation Period</label>
                    <select name="method_period" id="am-period" class="form-control">
                        <option value="1">Monthly</option>
                        <option value="3">Quarterly</option>
                        <option value="12" selected>Annually</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary xb-btn-primary">Save</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
function editAssetModel(m) {
    $('#am-id').val(m.id);
    $('#am-name').val(m.name);
    $('#am-method').val(m.method);
    $('#am-method-number').val(m.method_number);
    $('#am-period').val(m.method_period);
    $('#modal-am-title').text('Edit Asset Model: ' + m.name);
    $('#modal-asset-model').modal('show');
}

$('#asset-model-form').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize();
    $.post('<?php echo admin_url('xetuu_books/ajax/save_asset_model'); ?>', data, function(res) {
        var r = JSON.parse(res);
        if (r.success) { location.reload(); }
        else { alert(r.message || 'Error saving model.'); }
    });
});
</script>
