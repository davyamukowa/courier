<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>

<div class="cgs-card" style="margin-bottom:20px;">
    <div class="cgs-card__header">
        <h4 class="cgs-card__title"><i class="fa fa-globe"></i> Country Admins</h4>
    </div>
    <div style="padding:0 20px 16px;color:#555;">
        <p style="margin:0 0 6px;">
            You are the <strong>super user</strong>: you see every country, agent and staff member.
            A <strong>country admin</strong> sees and manages <em>only their own country</em> &mdash; they can add staff and
            agents for that country and manage them, and nothing else. They never get Perfex administrator rights.
        </p>
        <p style="margin:0;" class="text-muted">A country needs at least one branch (Network &rarr; Branches / Offices) before it can have an admin.</p>
    </div>
</div>

<div class="cgs-card" style="margin-bottom:20px;">
    <div class="cgs-card__header"><h4 class="cgs-card__title"><i class="fa fa-user-plus"></i> Appoint a country admin</h4></div>
    <div style="padding:0 20px 20px;">
        <?php echo form_open(admin_url('courier_goshipping/country_admins/appoint'), ['id' => 'appoint-form']); ?>
        <div class="row">
            <div class="col-md-4">
                <label>Country <span class="text-danger">*</span></label>
                <select name="country_id" id="ca_country" class="form-control" required>
                    <option value="">Select country</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?php echo (int) $c->country_id; ?>"><?php echo htmlspecialchars($c->short_name); ?> (<?php echo (int) $c->branch_count; ?> branch<?php echo $c->branch_count == 1 ? '' : 'es'; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Home branch <span class="text-danger">*</span></label>
                <select name="home_branch_id" id="ca_branch" class="form-control" required>
                    <option value="">Select a country first</option>
                </select>
                <small class="text-muted">Records they create are stamped with this branch. They still reach every branch in the country.</small>
            </div>
            <div class="col-md-4">
                <label>Who</label>
                <select name="mode" id="ca_mode" class="form-control">
                    <option value="existing">Existing staff member</option>
                    <option value="new">Create a new login</option>
                </select>
            </div>
        </div>

        <div class="row" id="ca_existing" style="margin-top:12px;">
            <div class="col-md-6">
                <label>Staff member</label>
                <select name="staff_id" class="form-control">
                    <option value="">Select staff</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?php echo (int) $s->staffid; ?>"><?php echo htmlspecialchars(trim($s->firstname . ' ' . $s->lastname) . ' — ' . $s->email); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row" id="ca_new" style="margin-top:12px;display:none;">
            <div class="col-md-3"><label>First name</label><input type="text" name="firstname" class="form-control"></div>
            <div class="col-md-3"><label>Last name</label><input type="text" name="lastname" class="form-control"></div>
            <div class="col-md-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-3"><label>Phone</label><input type="text" name="phonenumber" class="form-control"></div>
            <div class="col-md-3" style="margin-top:10px;"><label>Password (min 6)</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
        </div>

        <div style="margin-top:16px;">
            <button type="submit" class="cgs-btn cgs-btn--primary"><i class="fa fa-check"></i> Appoint country admin</button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="cgs-card">
    <div class="cgs-card__header"><h4 class="cgs-card__title"><i class="fa fa-list"></i> Current country admins</h4></div>
    <div style="padding:0 20px 20px;">
        <?php if (empty($admins)): ?>
            <p class="text-muted">No country admins yet.</p>
        <?php else: ?>
        <table class="table table-striped">
            <thead><tr><th>Country</th><th>Admin</th><th>Email</th><th>Status</th><th>Since</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($a->country_name ?? '—'); ?></strong></td>
                    <td><?php echo htmlspecialchars(trim(($a->firstname ?? '') . ' ' . ($a->lastname ?? ''))); ?></td>
                    <td><?php echo htmlspecialchars($a->email ?? ''); ?></td>
                    <td><?php echo !empty($a->active) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($a->created_at))); ?></td>
                    <td class="text-right">
                        <?php echo form_open(admin_url('courier_goshipping/country_admins/remove/' . (int) $a->id), ['style' => 'display:inline;', 'onsubmit' => "return confirm('Remove this country admin? They lose the admin permissions the role granted.');"]); ?>
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Remove</button>
                        <?php echo form_close(); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

    </div><!-- .cgs-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
<script>
(function () {
    var branches = <?php echo json_encode(array_map(function ($b) {
        return ['id' => (int) $b->id, 'name' => $b->name, 'country_id' => (int) $b->country_id];
    }, $branches)); ?>;
    var country = document.getElementById('ca_country');
    var branch  = document.getElementById('ca_branch');
    country.addEventListener('change', function () {
        var html = '<option value="">Select branch</option>';
        branches.filter(function (b) { return String(b.country_id) === country.value; }).forEach(function (b) {
            html += '<option value="' + b.id + '">' + String(b.name).replace(/</g, '&lt;') + '</option>';
        });
        branch.innerHTML = html;
    });
    document.getElementById('ca_mode').addEventListener('change', function () {
        var isNew = this.value === 'new';
        document.getElementById('ca_new').style.display = isNew ? '' : 'none';
        document.getElementById('ca_existing').style.display = isNew ? 'none' : '';
    });
})();
</script>
