<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>

<?php
$is_suspended = (isset($agent->status) && ($agent->status === '0' || $agent->status == 0) && !empty($agent->suspended_at));
$full_name    = htmlspecialchars($agent->firstname . ' ' . $agent->lastname);
?>


    <!-- Page header -->
    <div class="cgs-card" style="margin-bottom:20px;">
        <div class="cgs-card__header" style="border-bottom:none;margin-bottom:0;padding-bottom:0;">
            <h4 class="cgs-card__title">
                <i class="fa fa-user"></i> <?php echo $full_name; ?>
                <?php if ($is_suspended): ?>
                    <span class="label label-danger tw-ml-2">Suspended</span>
                <?php else: ?>
                    <span class="label label-success tw-ml-2">Active</span>
                <?php endif; ?>
            </h4>
            <div class="cgs-card__actions">
                <a href="<?php echo admin_url('courier_goshipping/agents/edit/' . $agent->id); ?>" class="cgs-btn cgs-btn--outline cgs-btn--sm">
                    <i class="fa fa-pencil"></i> Edit
                </a>
                <button class="cgs-btn cgs-btn--outline cgs-btn--sm" data-toggle="modal" data-target="#resetPasswordModal">
                    <i class="fa fa-key"></i> Reset Password
                </button>
                <?php if ($is_suspended): ?>
                    <a href="<?php echo admin_url('courier_goshipping/agents/activate/' . $agent->id); ?>" class="cgs-btn cgs-btn--primary cgs-btn--sm" onclick="return confirm('Activate this agent?');">
                        <i class="fa fa-check"></i> Activate
                    </a>
                <?php else: ?>
                    <button class="cgs-btn cgs-btn--accent cgs-btn--sm" data-toggle="modal" data-target="#suspendModal">
                        <i class="fa fa-ban"></i> Suspend
                    </button>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="<?php echo admin_url('courier_goshipping/agents/delete/' . $agent->id); ?>" class="cgs-btn cgs-btn--accent cgs-btn--sm" onclick="return confirm('Permanently delete this agent?');">
                        <i class="fa fa-trash"></i> Delete
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <p class="text-muted" style="margin:0;">
            <?php echo htmlspecialchars($agent->unique_number ?? ''); ?>
            &bull; <?php echo ucfirst($agent->agent_type ?? 'individual'); ?> agent
            &bull; <?php echo htmlspecialchars($agent->country_name ?? ''); ?>
        </p>
    </div>

    <!-- Stats row -->
    <div class="cgs-stat-row tw-mb-4">
        <div class="cgs-stat-card">
            <div class="cgs-stat-icon" style="background:#1565c0;"><i class="fa fa-truck"></i></div>
            <div><div class="cgs-stat-val"><?php echo number_format($stats['total_shipments']); ?></div><div class="cgs-stat-lbl">Total Shipments</div></div>
        </div>
        <div class="cgs-stat-card">
            <div class="cgs-stat-icon" style="background:#2e7d32;"><i class="fa fa-money"></i></div>
            <div><div class="cgs-stat-val"><?php echo number_format($stats['total_revenue'], 2); ?></div><div class="cgs-stat-lbl">Total Revenue</div></div>
        </div>
        <div class="cgs-stat-card">
            <div class="cgs-stat-icon" style="background:#00897b;"><i class="fa fa-check-circle"></i></div>
            <div><div class="cgs-stat-val"><?php echo number_format($stats['total_paid'], 2); ?></div><div class="cgs-stat-lbl">Total Paid</div></div>
        </div>
        <div class="cgs-stat-card">
            <div class="cgs-stat-icon" style="background:#c1272d;"><i class="fa fa-exclamation-circle"></i></div>
            <div><div class="cgs-stat-val"><?php echo number_format($stats['outstanding'], 2); ?></div><div class="cgs-stat-lbl">Outstanding</div></div>
        </div>
    </div>

    <!-- Tabs -->
    <style>
    .agent-tabs { display:flex; gap:4px; flex-wrap:wrap; background:var(--cgs-primary-light,#eaf1f8); padding:8px 12px; border-radius:8px; margin-bottom:0; list-style:none; border:none; }
    .agent-tabs > li > a {
        display:inline-flex; align-items:center; gap:6px;
        padding:9px 18px; border-radius:6px;
        font-weight:700; font-size:13.5px; color:var(--cgs-primary-dark,#2c5580);
        background:transparent; border:none;
        text-decoration:none; transition:background .15s;
    }
    .agent-tabs > li > a:hover { background:rgba(58,110,165,.12); color:var(--cgs-primary-dark,#2c5580); text-decoration:none; }
    .agent-tabs > li.active > a { background:#fff; color:var(--cgs-primary-dark,#2c5580); box-shadow:0 2px 6px rgba(16,24,40,.15); border-bottom:3px solid var(--cgs-secondary,#c1272d); }
    .tab-content.agent-tab-content { background:#fff; border:1px solid var(--cgs-border,#e0e0e0); border-radius:0 0 8px 8px; padding:24px; }
    </style>

    <ul class="agent-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#tab-overview"     data-toggle="tab" class="cgs-nav-item"><i class="fa fa-user"></i> Overview</a></li>
        <li role="presentation"><a href="#tab-permissions" data-toggle="tab" class="cgs-nav-item"><i class="fa fa-shield"></i> Permissions</a></li>
        <li role="presentation"><a href="#tab-shipments"   data-toggle="tab" class="cgs-nav-item"><i class="fa fa-truck"></i> Shipments</a></li>
        <li role="presentation"><a href="#tab-invoices"    data-toggle="tab" class="cgs-nav-item"><i class="fa fa-file-text"></i> Invoices</a></li>
        <li role="presentation"><a href="#tab-documents"   data-toggle="tab" class="cgs-nav-item"><i class="fa fa-folder"></i> Documents</a></li>
        <li role="presentation"><a href="#tab-notes"       data-toggle="tab" class="cgs-nav-item"><i class="fa fa-pencil-square-o"></i> Admin Notes</a></li>
    </ul>

    <div class="tab-content agent-tab-content">

        <!-- OVERVIEW TAB -->
        <div class="tab-pane active" id="tab-overview">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered cgs-table">
                        <tr><th>Agent Number</th><td><?php echo htmlspecialchars($agent->unique_number ?? ''); ?></td></tr>
                        <tr><th>Name</th><td><?php echo $full_name; ?></td></tr>
                        <tr><th>Email</th><td><?php echo htmlspecialchars($agent->email ?? ''); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo htmlspecialchars($agent->phonenumber ?? $agent->phone_number ?? ''); ?></td></tr>
                        <tr><th>Country</th><td><?php echo htmlspecialchars($agent->country_name ?? ''); ?></td></tr>
                        <tr><th>Station</th><td><?php echo htmlspecialchars($agent->station ?? '—'); ?></td></tr>
                        <tr><th>Type</th><td><?php echo ucfirst($agent->agent_type ?? ''); ?></td></tr>
                        <?php if (!empty($agent->company_name)): ?>
                        <tr><th>Company</th><td><?php echo htmlspecialchars($agent->company_name); ?></td></tr>
                        <?php endif; ?>
                        <tr><th>KRA PIN</th><td><?php echo htmlspecialchars($agent->kra_pin ?? '—'); ?></td></tr>
                        <tr><th>Commission Rate</th><td>
                            <?php echo ($agent->commission_rate !== null) ? htmlspecialchars($agent->commission_rate) . '%' : '—'; ?>
                        </td></tr>
                        <tr><th>Status</th><td>
                            <?php echo $is_suspended
                                ? '<span class="label label-danger">Suspended</span>'
                                : '<span class="label label-success">Active</span>'; ?>
                        </td></tr>
                        <?php if ($is_suspended && !empty($agent->suspended_reason)): ?>
                        <tr><th>Suspension Reason</th><td><?php echo htmlspecialchars($agent->suspended_reason); ?></td></tr>
                        <tr><th>Suspended At</th><td><?php echo $agent->suspended_at; ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- PERMISSIONS TAB -->
        <div class="tab-pane" id="tab-permissions">
            <?php
            $all_courier_perms = [
                'courier-shipments' => [
                    'label'        => '<i class="fa fa-truck"></i> Shipments',
                    'capabilities' => [
                        'view_own_shipments'            => 'View Own Shipments',
                        'view_all_shipments'            => 'View All Shipments (Global)',
                        'create_shipments'              => 'Create (All Types)',
                        'create_shipment_road'          => 'Create — Road',
                        'create_shipment_fcl'           => 'Create — FCL',
                        'create_shipment_lcl'           => 'Create — LCL',
                        'create_shipment_consolidation' => 'Create — Consolidation',
                        'create_shipment_air_freight'   => 'Create — Air Freight',
                        'create_shipment_air_consolidation' => 'Create — Air Consolidation',
                        'create_shipment_courier'       => 'Create — Courier',
                        'create_shipment_domestic'      => 'Create — Domestic',
                        'edit_shipments'                => 'Edit Shipments',
                        'delete_shipments'              => 'Delete Shipments',
                    ],
                ],
                'courier-pickups' => [
                    'label'        => '<i class="fa fa-map-marker"></i> Pickups',
                    'capabilities' => [
                        'view_own_pickups' => 'View Own Pickups',
                        'view_all_pickups' => 'View All Pickups (Global)',
                        'create_pickups'   => 'Create Pickups',
                        'edit_pickups'     => 'Edit Pickups',
                        'delete_pickups'   => 'Delete Pickups',
                    ],
                ],
                'courier-waybills' => [
                    'label'        => '<i class="fa fa-file-text-o"></i> Waybills',
                    'capabilities' => [
                        'view_own_waybills' => 'View Own Waybills',
                        'view_waybills'     => 'View All Waybills (Global)',
                        'delete_waybills'   => 'Delete Waybills',
                    ],
                ],
                'courier-manifests' => [
                    'label'        => '<i class="fa fa-list-alt"></i> Manifests',
                    'capabilities' => [
                        'view_own_manifests' => 'View Own Manifests',
                        'view_manifests'     => 'View All Manifests (Global)',
                        'create_manifests'   => 'Create Manifests',
                        'edit_manifests'     => 'Edit Manifests',
                        'delete_manifests'   => 'Delete Manifests',
                    ],
                ],
                'courier-invoices' => [
                    'label'        => '<i class="fa fa-credit-card"></i> Invoices & Receipts',
                    'capabilities' => [
                        'view_own_invoices' => 'View Own Invoices',
                        'view_invoices'     => 'View All Invoices (Global)',
                        'create_invoices'   => 'Create Invoices',
                        'delete_invoices'   => 'Delete Invoices',
                        'view_receipts'     => 'View Receipts',
                        'generate_payment'  => 'Generate Payment',
                    ],
                ],
                'courier-agents' => [
                    'label'        => '<i class="fa fa-users"></i> Agents',
                    'capabilities' => [
                        'view_agents'   => 'View Agents',
                        'create_agents' => 'Create Agents',
                        'edit_agents'   => 'Edit Agents',
                        'delete_agents' => 'Delete Agents',
                    ],
                ],
                'courier-companies' => [
                    'label'        => '<i class="fa fa-building"></i> Courier Companies',
                    'capabilities' => [
                        'view_companies'   => 'View Companies',
                        'create_companies' => 'Create Companies',
                        'edit_companies'   => 'Edit Companies',
                        'delete_companies' => 'Delete Companies',
                    ],
                ],
                'courier-settings' => [
                    'label'        => '<i class="fa fa-cog"></i> Settings',
                    'capabilities' => [
                        'view_settings' => 'View Settings',
                        'edit_settings' => 'Edit Settings',
                    ],
                ],
            ];
            ?>

            <div class="row tw-mb-3">
                <div class="col-md-12">
                    <p class="text-muted">
                        These are this agent's individual permissions. Changes here apply <strong>only to this agent</strong> and do not affect the role.
                        To reset to role defaults, use <strong>Reset Agent Role Defaults</strong> on the agents list.
                    </p>
                </div>
            </div>

            <?php echo form_open('', ['id' => 'permissions-form']); ?>
                <div class="row">
                    <?php foreach ($all_courier_perms as $feature => $group): ?>
                    <div class="col-md-6 tw-mb-4">
                        <div class="panel panel-default" style="margin-bottom:0;">
                            <div class="panel-heading" style="font-weight:600;">
                                <?php echo $group['label']; ?>
                            </div>
                            <div class="panel-body" style="padding:12px 16px;">
                                <?php foreach ($group['capabilities'] as $cap => $label): ?>
                                    <?php $checked = !empty($current_perms[$feature]) && in_array($cap, $current_perms[$feature]); ?>
                                    <div class="tw-flex tw-items-center tw-mb-1" style="margin-bottom:6px;">
                                        <label style="margin:0;font-weight:normal;cursor:pointer;display:flex;align-items:center;gap:8px;">
                                            <input type="checkbox"
                                                   name="permissions[<?php echo $feature; ?>][]"
                                                   value="<?php echo $cap; ?>"
                                                   <?php echo $checked ? 'checked' : ''; ?>
                                                   style="width:16px;height:16px;margin:0;">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="tw-mt-4" style="margin-top:16px;">
                    <button type="button" id="btn-save-permissions" class="cgs-btn cgs-btn--primary">
                        <i class="fa fa-save"></i> Save Permissions
                    </button>
                    <span id="perm-saving" style="display:none;margin-left:10px;" class="text-muted"><i class="fa fa-spinner fa-spin"></i> Saving…</span>
                    <span id="perm-saved"  style="display:none;margin-left:10px;" class="text-success"><i class="fa fa-check"></i> Saved!</span>
                </div>
            <?php echo form_close(); ?>
        </div>

        <!-- SHIPMENTS TAB -->
        <div class="tab-pane" id="tab-shipments">
            <?php if (!empty($shipments)): ?>
            <table class="table table-striped cgs-table">
                <thead>
                    <tr>
                        <th>Waybill</th>
                        <th>Sender</th>
                        <th>Recipient</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($shipments as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s->waybill_number ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($s->sender_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s->recipient_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($s->shipping_mode ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($s->status_name ?? ''); ?></td>
                        <td>—</td>
                        <td><?php echo date('d M Y', strtotime($s->created_at)); ?></td>
                        <td><a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $s->id); ?>" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted tw-py-4">No shipments found for this agent.</p>
            <?php endif; ?>
        </div>

        <!-- INVOICES TAB -->
        <div class="tab-pane" id="tab-invoices">
            <?php if (!empty($invoices)): ?>
            <table class="table table-striped cgs-table">
                <thead>
                    <tr>
                        <th>Waybill</th>
                        <th>Invoice Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <?php $balance = (float)($inv->invoice_amount ?? 0) - (float)($inv->paid_amount ?? 0); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv->waybill_number ?? ''); ?></td>
                        <td><?php echo number_format((float)($inv->invoice_amount ?? 0), 2); ?></td>
                        <td><?php echo number_format((float)($inv->paid_amount ?? 0), 2); ?></td>
                        <td class="<?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>">
                            <?php echo number_format($balance, 2); ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($inv->created_at)); ?></td>
                        <td><a href="<?php echo admin_url('courier_goshipping/shipments/courier_invoice/' . $inv->id); ?>" class="btn btn-xs btn-default"><i class="fa fa-file-text"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted tw-py-4">No invoices found for this agent.</p>
            <?php endif; ?>
        </div>

        <!-- DOCUMENTS TAB -->
        <div class="tab-pane" id="tab-documents">
            <div class="row">
                <?php if (!empty($agent->id_file_url)): ?>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-id-card"></i> ID Document</div>
                        <div class="panel-body tw-text-center">
                            <a href="<?php echo base_url($agent->id_file_url); ?>" target="_blank" class="btn btn-success">
                                <i class="fa fa-download"></i> View / Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($agent->kra_file_url)): ?>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-file"></i> KRA Document</div>
                        <div class="panel-body tw-text-center">
                            <a href="<?php echo base_url($agent->kra_file_url); ?>" target="_blank" class="btn btn-info">
                                <i class="fa fa-download"></i> View / Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($agent->cert_of_corp_url)): ?>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-certificate"></i> Certificate of Incorporation</div>
                        <div class="panel-body tw-text-center">
                            <a href="<?php echo base_url($agent->cert_of_corp_url); ?>" target="_blank" class="btn btn-warning">
                                <i class="fa fa-download"></i> View / Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (empty($agent->id_file_url) && empty($agent->kra_file_url) && empty($agent->cert_of_corp_url)): ?>
                    <div class="col-md-12"><p class="text-muted tw-py-4">No documents uploaded.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ADMIN NOTES TAB -->
        <div class="tab-pane" id="tab-notes">
            <?php echo form_open(admin_url('courier_goshipping/agents/update/' . $agent->id)); ?>
                <input type="hidden" name="firstname" value="<?php echo htmlspecialchars($agent->firstname ?? ''); ?>">
                <input type="hidden" name="lastname"  value="<?php echo htmlspecialchars($agent->lastname ?? ''); ?>">
                <input type="hidden" name="email"     value="<?php echo htmlspecialchars($agent->email ?? ''); ?>">
                <div class="form-group">
                    <label>Admin Notes (internal only)</label>
                    <textarea name="admin_notes" class="form-control" rows="8" placeholder="Add notes about this agent..."><?php echo htmlspecialchars($agent->admin_notes ?? ''); ?></textarea>
                </div>
                <button type="submit" class="cgs-btn cgs-btn--primary">Save Notes</button>
            <?php echo form_close(); ?>
        </div>

    </div><!-- .tab-content -->

</div><!-- .cgs-page -->
</div><!-- #wrapper -->

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('courier_goshipping/agents/reset_password/' . $agent->id)); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Reset Password — <?php echo $full_name; ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>New Password <span class="required">*</span></label>
                        <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Min 6 characters">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cgs-btn cgs-btn--outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="cgs-btn cgs-btn--primary">Reset Password</button>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('courier_goshipping/agents/suspend/' . $agent->id)); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Suspend Agent</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Suspension (optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cgs-btn cgs-btn--outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="cgs-btn cgs-btn--accent">Suspend</button>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(function () {
    $('#btn-save-permissions').on('click', function () {
        var $btn    = $(this).prop('disabled', true);
        var $saving = $('#perm-saving').show();
        $('#perm-saved').hide();

        var postData = $('#permissions-form').serialize();

        $.ajax({
            url:         '<?php echo admin_url('courier_goshipping/agents/save_permissions/' . $agent->id); ?>',
            type:        'POST',
            data:        postData,
            dataType:    'json',
            success: function (res) {
                $saving.hide();
                $btn.prop('disabled', false);
                if (res && res.success) {
                    $('#perm-saved').show();
                    setTimeout(function () { $('#perm-saved').hide(); }, 3000);
                    alert_float('success', res.message);
                } else {
                    alert_float('danger', (res && res.message) ? res.message : 'Save failed.');
                }
            },
            error: function (xhr) {
                $saving.hide();
                $btn.prop('disabled', false);
                alert_float('danger', 'Request failed (HTTP ' + xhr.status + '). Check console for details.');
                console.error('Save permissions error:', xhr.status, xhr.responseText);
            }
        });
    });
});
</script>
