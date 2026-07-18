<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>


    <div class="row">
        <div class="col-md-8">
            <div class="cgs-card">
                <div class="cgs-card__header">
                    <h4 class="cgs-card__title"><i class="fa fa-user"></i> Edit Agent: <?php echo htmlspecialchars($agent->firstname . ' ' . $agent->lastname); ?></h4>
                    <div class="cgs-card__actions">
                        <span class="text-muted"><?php echo htmlspecialchars($agent->unique_number ?? ''); ?></span>
                    </div>
                </div>
                <div>
                    <?php echo form_open(admin_url('courier_goshipping/agents/update/' . $agent->id)); ?>
            
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="firstname" class="form-control" required value="<?php echo htmlspecialchars($agent->firstname ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="lastname" class="form-control" required value="<?php echo htmlspecialchars($agent->lastname ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($agent->email ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phonenumber" class="form-control" value="<?php echo htmlspecialchars($agent->phonenumber ?? $agent->phone_number ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Country</label>
                                    <select name="country_id" class="form-control">
                                        <option value="">— Select Country —</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?php echo $c->country_id; ?>" <?php echo ($c->country_id == $agent->country_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c->short_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station / Service Point</label>
                                    <input type="text" name="station" class="form-control" value="<?php echo htmlspecialchars($agent->station ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Branch / Office <span class="required">*</span></label>
                                    <select name="agent_branch_id" class="form-control" required>
                                        <option value="">— Select Branch —</option>
                                        <?php foreach ($branches as $b): ?>
                                            <option value="<?php echo $b->id; ?>" <?php echo ($b->id == $agent->branch_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($b->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="help-block">Which office this agent reports to — controls which shipments/pickups they see.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Commission Rate (%)</label>
                                    <div class="input-group">
                                        <input type="number" name="commission_rate" class="form-control" step="0.01" min="0" max="100"
                                               value="<?php echo htmlspecialchars($agent->commission_rate ?? ''); ?>"
                                               placeholder="e.g. 5.00">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <p class="help-block">Percentage of invoice amount earned by agent</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Admin Notes (internal only)</label>
                            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Internal notes about this agent…"><?php echo htmlspecialchars($agent->admin_notes ?? ''); ?></textarea>
                        </div>

                        <hr>
                        <div class="tw-flex tw-gap-2">
                            <button type="submit" class="cgs-btn cgs-btn--primary">Save Changes</button>
                            <a href="<?php echo admin_url('courier_goshipping/agents/view/' . $agent->id); ?>" class="cgs-btn cgs-btn--outline">Cancel</a>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Quick Actions</div>
                <div class="panel-body">
                    <p><a href="<?php echo admin_url('courier_goshipping/agents/view/' . $agent->id); ?>" class="cgs-btn cgs-btn--outline" style="width:100%;justify-content:center;"><i class="fa fa-eye"></i> View Agent</a></p>
                    <p><button class="cgs-btn cgs-btn--outline" style="width:100%;justify-content:center;" data-toggle="modal" data-target="#resetPasswordModal"><i class="fa fa-key"></i> Reset Password</button></p>
                    <?php if (isset($agent->status) && ($agent->status === '0' || $agent->status == 0) && !empty($agent->suspended_at)): ?>
                        <p><a href="<?php echo admin_url('courier_goshipping/agents/activate/' . $agent->id); ?>" class="cgs-btn cgs-btn--primary" style="width:100%;justify-content:center;" onclick="return confirm('Activate this agent?');"><i class="fa fa-check"></i> Activate Agent</a></p>
                    <?php else: ?>
                        <p><button class="cgs-btn cgs-btn--accent" style="width:100%;justify-content:center;" data-toggle="modal" data-target="#suspendModal"><i class="fa fa-ban"></i> Suspend Agent</button></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div><!-- .cgs-page -->
</div><!-- #wrapper -->

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('courier_goshipping/agents/reset_password/' . $agent->id)); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Reset Password</h4>
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
