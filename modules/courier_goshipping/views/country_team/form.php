<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>
<?php
$editing = !empty($member);
// After a failed create, the typed values come back from the session (password is never kept).
$old = $this->session->flashdata('country_team_old') ?: [];
$val = function ($key, $fallback = '') use ($old, $member, $editing) {
    if (isset($old[$key])) { return $old[$key]; }
    if ($editing && isset($member->$key)) { return $member->$key; }
    return $fallback;
};
$sel_role   = (int) $val('role_id', $editing ? $member->role : 0);
$sel_branch = (int) $val('branch_id', $editing ? $member->branch_id : 0);
?>
<div class="cgs-card">
    <div class="cgs-card__header">
        <h4 class="cgs-card__title"><i class="fa fa-user-plus"></i> <?php echo $editing ? 'Edit staff' : 'Add staff'; ?></h4>
        <div class="cgs-card__actions">
            <a href="<?php echo admin_url('courier_goshipping/country_team'); ?>" class="cgs-btn cgs-btn--outline"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div style="padding:0 20px 20px;">
        <?php echo form_open(admin_url('courier_goshipping/country_team/' . ($editing ? 'update/' . (int) $member->staffid : 'store')), ['id' => 'team-form']); ?>
        <div class="row">
            <div class="col-md-4 form-group"><label>First name <span class="text-danger">*</span></label>
                <input type="text" name="firstname" class="form-control" required value="<?php echo htmlspecialchars((string) $val('firstname')); ?>"></div>
            <div class="col-md-4 form-group"><label>Last name</label>
                <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars((string) $val('lastname')); ?>"></div>
            <div class="col-md-4 form-group"><label>Phone</label>
                <input type="text" name="phonenumber" class="form-control" value="<?php echo htmlspecialchars((string) $val('phonenumber')); ?>"></div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group"><label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" <?php echo $editing ? 'readonly' : 'required'; ?> value="<?php echo htmlspecialchars((string) $val('email')); ?>"></div>
            <?php if (!$editing): ?>
            <div class="col-md-4 form-group"><label>Password (min 6) <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password"></div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-4 form-group"><label>Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-control" required>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?php echo (int) $r->roleid; ?>" <?php echo $sel_role === (int) $r->roleid ? 'selected' : ''; ?>><?php echo htmlspecialchars($r->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($editing): ?><small class="text-muted">Changing the role re-applies that role's permissions.</small><?php endif; ?>
            </div>
            <div class="col-md-4 form-group"><label>Branch <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-control" required>
                    <option value="">Select branch</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo (int) $b->id; ?>" <?php echo $sel_branch === (int) $b->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="cgs-btn cgs-btn--primary"><i class="fa fa-save"></i> <?php echo $editing ? 'Save changes' : 'Create staff'; ?></button>
        <?php echo form_close(); ?>

        <?php if ($editing): ?>
        <hr>
        <?php echo form_open(admin_url('courier_goshipping/country_team/reset_password/' . (int) $member->staffid), ['class' => 'form-inline']); ?>
            <label>Reset password&nbsp;</label>
            <input type="password" name="new_password" class="form-control" placeholder="New password (min 6)" autocomplete="new-password">
            <button type="submit" class="btn btn-default">Reset</button>
        <?php echo form_close(); ?>
        <?php endif; ?>
    </div>
</div>

    </div><!-- .cgs-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
