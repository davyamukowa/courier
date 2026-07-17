<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.agent-actions { white-space: nowrap; }
.agent-actions a, .agent-actions button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    text-decoration: none;
    margin-right: 4px;
    transition: opacity .15s;
}
.agent-actions a:hover, .agent-actions button:hover { opacity: .85; text-decoration: none; }
.btn-view    { background: #f0f0f0; color: #333; }
.btn-edit    { background: #3498db; color: #fff; }
.btn-suspend { background: #f39c12; color: #fff; }
.btn-activate{ background: #27ae60; color: #fff; }
.btn-delete  { background: #e74c3c; color: #fff; }
.badge-active    { background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.badge-suspended { background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.agent-meta { font-size: 12px; color: #888; margin-top: 2px; }
.agent-name { font-weight: 600; font-size: 14px; color: #222; }
.doc-link { display: inline-flex; align-items: center; gap: 3px; font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 10px; text-decoration: none; margin-right: 4px; }
.doc-link:hover { text-decoration: none; opacity: .8; }
.doc-id   { background: #e8f5e9; color: #2e7d32; }
.doc-kra  { background: #e3f2fd; color: #1565c0; }
.doc-cert { background: #fff8e1; color: #e65100; }
</style>

<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
    <div></div>
    <?php if (is_admin()): ?>
    <button id="btn-reset-role" class="cgs-btn cgs-btn--accent">
        <i class="fa fa-shield"></i> Reset Agent Role Defaults
    </button>
    <?php endif; ?>
</div>

<?php if (!empty($agents)): ?>
<div class="table-responsive">
<table class="table dt-table cgs-table" id="agents-table">
    <thead>
    <tr>
        <th>Agent Number</th>
        <th>Agent</th>
        <th>Country</th>
        <th>Station</th>
        <th>Commission</th>
        <th>Status</th>
        <th>Documents</th>
        <th style="min-width:220px;">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($agents as $agent): ?>
        <?php $is_suspended = (isset($agent->status) && ($agent->status === '0' || $agent->status == 0) && !empty($agent->suspended_at)); ?>
        <tr>
            <td style="font-weight:600;font-size:13px;"><?php echo htmlspecialchars($agent->unique_number); ?></td>
            <td>
                <?php if ($agent->agent_type === 'company'): ?>
                    <div class="agent-name">COMPANY: <?php echo htmlspecialchars($agent->company_name); ?></div>
                    <div class="agent-meta"><?php echo htmlspecialchars($agent->firstname); ?></div>
                <?php else: ?>
                    <div class="agent-name"><?php echo htmlspecialchars($agent->firstname . ' ' . $agent->lastname); ?></div>
                <?php endif; ?>
                <div class="agent-meta"><?php echo htmlspecialchars($agent->email); ?></div>
                <div class="agent-meta"><?php echo htmlspecialchars($agent->phone_number ?? ''); ?></div>
            </td>
            <td><?php echo htmlspecialchars($agent->country_name ?? ''); ?></td>
            <td><?php echo htmlspecialchars($agent->station ?? '—'); ?></td>
            <td>
                <?php echo ($agent->commission_rate !== null)
                    ? '<strong>' . htmlspecialchars($agent->commission_rate) . '%</strong>'
                    : '<span class="text-muted">—</span>'; ?>
            </td>
            <td>
                <?php if ($is_suspended): ?>
                    <span class="badge-suspended"><i class="fa fa-ban"></i> Suspended</span>
                <?php else: ?>
                    <span class="badge-active"><i class="fa fa-check-circle"></i> Active</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($agent->id_file_url)): ?>
                    <a href="<?php echo base_url($agent->id_file_url); ?>" target="_blank" class="doc-link doc-id"><i class="fa fa-id-card"></i> ID</a>
                <?php endif; ?>
                <?php if (!empty($agent->kra_file_url)): ?>
                    <a href="<?php echo base_url($agent->kra_file_url); ?>" target="_blank" class="doc-link doc-kra"><i class="fa fa-file"></i> KRA</a>
                <?php endif; ?>
                <?php if (!empty($agent->cert_of_corp_url)): ?>
                    <a href="<?php echo base_url($agent->cert_of_corp_url); ?>" target="_blank" class="doc-link doc-cert"><i class="fa fa-certificate"></i> Cert</a>
                <?php endif; ?>
            </td>
            <td class="agent-actions">
                <a href="<?php echo admin_url('courier_logistic/agents/view/' . $agent->id); ?>" class="btn-view">
                    <i class="fa fa-eye"></i> View
                </a>
                <a href="<?php echo admin_url('courier_logistic/agents/edit/' . $agent->id); ?>" class="btn-edit">
                    <i class="fa fa-pencil"></i> Edit
                </a>
                <?php if ($is_suspended): ?>
                    <a href="<?php echo admin_url('courier_logistic/agents/activate/' . $agent->id); ?>" class="btn-activate" onclick="return confirm('Activate this agent?');">
                        <i class="fa fa-check"></i> Activate
                    </a>
                <?php else: ?>
                    <button class="btn-suspend btn-do-suspend" data-id="<?php echo $agent->id; ?>" data-name="<?php echo htmlspecialchars($agent->firstname . ' ' . $agent->lastname); ?>">
                        <i class="fa fa-ban"></i> Suspend
                    </button>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="<?php echo admin_url('courier_logistic/agents/delete/' . $agent->id); ?>" class="btn-delete" onclick="return confirm('Permanently delete this agent?');">
                        <i class="fa fa-trash"></i>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th>Agent Number</th><th>Agent</th><th>Country</th><th>Station</th>
        <th>Commission</th><th>Status</th><th>Documents</th><th>Actions</th>
    </tr>
    </tfoot>
</table>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px 0;color:#aaa;">
    <i class="fa fa-users" style="font-size:40px;display:block;margin-bottom:12px;"></i>
    No agents yet. <a href="<?php echo admin_url('courier_logistic/agents/main?group=create_agent'); ?>">Create the first agent</a>.
</div>
<?php endif; ?>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="suspend-form" action="">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="modal-content">
                <div class="modal-header" style="background:#f39c12;color:#fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-ban"></i> Suspend Agent: <span id="suspend-name"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Suspension <span class="text-muted">(optional)</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cgs-btn cgs-btn--outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="cgs-btn cgs-btn--accent"><i class="fa fa-ban"></i> Suspend Agent</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    $('.btn-do-suspend').on('click', function () {
        $('#suspend-name').text($(this).data('name'));
        $('#suspend-form').attr('action', '<?php echo admin_url('courier_logistic/agents/suspend/'); ?>' + $(this).data('id'));
        $('#suspendModal').modal('show');
    });

    $('#btn-reset-role').on('click', function () {
        if (!confirm('Reset ALL agent permissions to the correct restricted defaults and sync to all agents?')) return;
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Resetting…');
        $.post('<?php echo admin_url('courier_logistic/agents/reset_permissions'); ?>', {
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        }, function (r) {
            $btn.prop('disabled', false).html('<i class="fa fa-shield"></i> Reset Agent Role Defaults');
            alert_float(r.success ? 'success' : 'danger', r.message || 'Done.');
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-shield"></i> Reset Agent Role Defaults');
            alert_float('danger', 'Request failed.');
        });
    });
});
</script>

