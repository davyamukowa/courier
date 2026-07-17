<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Configuration</a> &rsaquo; Follow-up Levels
    </div>

    <div class="xb-header-toolbar">
        <div><h3>Follow-up Levels</h3></div>
        <div>
            <button type="button" class="btn btn-primary xb-btn-primary btn-sm"
                    data-toggle="modal" data-target="#modal-followup">
                <i class="fa fa-plus"></i> New Level
            </button>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        Follow-up levels define automated collection actions based on days overdue.
        They are used in the <a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>">Aged Receivable</a> report.
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>#</th>
                        <th>Level Name</th>
                        <th>Days Overdue</th>
                        <th>Description</th>
                        <th class="text-center">Send Email</th>
                        <th class="text-center">Block Account</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($followup_levels)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">
                        No follow-up levels configured.
                        <br>
                        <strong>Example levels:</strong> Reminder (7 days), First Notice (30 days), Final Notice (60 days), Legal Action (90 days)
                    </td></tr>
                    <?php else: foreach($followup_levels as $i => $lvl): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($lvl->name); ?></strong></td>
                        <td><?php echo $lvl->delay; ?> days past due</td>
                        <td><?php echo htmlspecialchars($lvl->description ?? ''); ?></td>
                        <td class="text-center">
                            <span class="label <?php echo $lvl->send_email?'label-success':'label-default'; ?>">
                                <?php echo $lvl->send_email?'Yes':'No'; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="label <?php echo $lvl->block_account?'label-danger':'label-default'; ?>">
                                <?php echo $lvl->block_account?'Yes':'No'; ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-default btn-xs"
                                    onclick="editFollowup(<?php echo htmlspecialchars(json_encode($lvl)); ?>)">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-xs"
                                    onclick="deleteFollowup(<?php echo $lvl->id; ?>, '<?php echo htmlspecialchars(addslashes($lvl->name)); ?>')">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-followup" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a6b3a;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title" id="modal-fu-title">New Follow-up Level</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fu-id">
                <div class="form-group">
                    <label>Level Name <span class="text-danger">*</span></label>
                    <input type="text" id="fu-name" class="form-control" placeholder="e.g., First Reminder">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Days Past Due <span class="text-danger">*</span></label>
                            <input type="number" id="fu-delay" class="form-control" value="30" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sequence</label>
                            <input type="number" id="fu-seq" class="form-control" value="10">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description / Email Template</label>
                    <textarea id="fu-desc" class="form-control" rows="3" placeholder="Message to send to customer..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><input type="checkbox" id="fu-email" value="1" checked> Send Email</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><input type="checkbox" id="fu-letter" value="1"> Send Letter</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><input type="checkbox" id="fu-block" value="1"> Block Account</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary xb-btn-primary" onclick="saveFollowup()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function editFollowup(lvl) {
    $('#fu-id').val(lvl.id);
    $('#fu-name').val(lvl.name);
    $('#fu-delay').val(lvl.delay);
    $('#fu-seq').val(lvl.sequence);
    $('#fu-desc').val(lvl.description);
    $('#fu-email').prop('checked', lvl.send_email == 1);
    $('#fu-letter').prop('checked', lvl.send_letter == 1);
    $('#fu-block').prop('checked', lvl.block_account == 1);
    $('#modal-fu-title').text('Edit: ' + lvl.name);
    $('#modal-followup').modal('show');
}

function deleteFollowup(id, name) {
    if (!confirm('Delete follow-up level "' + name + '"?')) return;
    $.post('<?php echo admin_url('xetuu_books/ajax/delete_followup_level'); ?>', {id: id}, function(res) {
        try { var r = JSON.parse(res); } catch(e) { var r = {success:false}; }
        if (r.success) { location.reload(); }
        else { alert('Could not delete this level.'); }
    });
}

function saveFollowup() {
    var data = {
        id: $('#fu-id').val(),
        name: $('#fu-name').val(),
        delay: $('#fu-delay').val(),
        sequence: $('#fu-seq').val(),
        description: $('#fu-desc').val(),
        send_email: $('#fu-email').is(':checked') ? 1 : 0,
        send_letter: $('#fu-letter').is(':checked') ? 1 : 0,
        block_account: $('#fu-block').is(':checked') ? 1 : 0,
    };
    $.post('<?php echo admin_url('xetuu_books/ajax/save_followup_level'); ?>', data, function(res) {
        var r = JSON.parse(res);
        if (r.success) { location.reload(); }
        else { alert(r.message || 'Error saving level.'); }
    });
}
</script>
