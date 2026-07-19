<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="alert alert-info">
            Riders who self-registered on the <?php echo htmlspecialchars(site_url('courier_goshipping/rider')); ?> app. Auto-linked riders were matched to a 'Fleet: Driver' staff profile by phone number, or had one created for them automatically (login-blocked, hidden from Setup &rarr; Staff — used only so deliveries can be assigned to them).
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Rider</th>
                        <th>Phone</th>
                        <th>Account Status</th>
                        <th>Driver Profile</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riders)): ?>
                        <?php foreach ($riders as $rider): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rider->name); ?></td>
                                <td><?php echo htmlspecialchars($rider->phone); ?></td>
                                <td>
                                    <span class="label label-<?php echo $rider->status === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($rider->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($rider->linked): ?>
                                        <span class="label label-info">Linked: <?php echo htmlspecialchars($rider->staff_display); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not linked</span>
                                        <?php if (!empty($unlinked_drivers)): ?>
                                            <?php echo form_open(admin_url('courier_goshipping/fulfilment/link_rider/' . $rider->id), ['style' => 'display:inline-block; margin-left:6px;']); ?>
                                                <select name="staff_id" class="form-control input-xs" style="display:inline-block; width:auto; height:26px; padding:2px 6px;" onchange="this.form.submit()">
                                                    <option value="">Link to driver...</option>
                                                    <?php foreach ($unlinked_drivers as $driver): ?>
                                                        <option value="<?php echo $driver->staffid; ?>"><?php echo htmlspecialchars(trim($driver->firstname . ' ' . $driver->lastname)); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php echo form_close(); ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo _dt($rider->created_at); ?></td>
                                <td>
                                    <?php echo form_open(admin_url('courier_goshipping/fulfilment/toggle_rider_status/' . $rider->id)); ?>
                                        <button type="submit" class="btn btn-default btn-xs" onclick="return confirm('<?php echo $rider->status === 'active' ? 'Suspend' : 'Reactivate'; ?> this rider account?');">
                                            <?php echo $rider->status === 'active' ? 'Suspend' : 'Reactivate'; ?>
                                        </button>
                                    <?php echo form_close(); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No riders have registered yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
