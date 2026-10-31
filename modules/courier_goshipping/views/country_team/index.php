<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>

<div class="cgs-card">
    <div class="cgs-card__header">
        <h4 class="cgs-card__title"><i class="fa fa-users"></i> Country Team &mdash; <?php echo htmlspecialchars($country_name); ?></h4>
        <div class="cgs-card__actions">
            <a href="<?php echo admin_url('courier_goshipping/country_team/create'); ?>" class="cgs-btn cgs-btn--primary"><i class="fa fa-plus"></i> Add staff</a>
            <a href="<?php echo admin_url('courier_goshipping/agents/main'); ?>" class="cgs-btn cgs-btn--outline"><i class="fa fa-handshake-o"></i> Agents (<?php echo (int) $agent_count; ?>)</a>
        </div>
    </div>
    <div style="padding:0 20px 20px;">
        <p class="text-muted">You administer <strong><?php echo htmlspecialchars($country_name); ?></strong>. You can add and manage staff and agents for this country only.</p>
        <?php if (empty($team)): ?>
            <p class="text-muted">No staff yet. Use <strong>Add staff</strong> to create the first one.</p>
        <?php else: ?>
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Branch</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($team as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars(trim($m->firstname . ' ' . $m->lastname)); ?></td>
                    <td><?php echo htmlspecialchars($m->email); ?></td>
                    <td><?php echo htmlspecialchars((string) $m->phonenumber); ?></td>
                    <td><?php echo htmlspecialchars((string) $m->role_name); ?></td>
                    <td><?php echo htmlspecialchars((string) $m->branches); ?></td>
                    <td><?php echo $m->active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
                    <td class="text-right" style="white-space:nowrap;">
                        <a href="<?php echo admin_url('courier_goshipping/country_team/edit/' . (int) $m->staffid); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a>
                        <?php echo form_open(admin_url('courier_goshipping/country_team/toggle_active/' . (int) $m->staffid), ['style' => 'display:inline;']); ?>
                        <button type="submit" class="btn btn-<?php echo $m->active ? 'warning' : 'success'; ?> btn-xs"><?php echo $m->active ? 'Deactivate' : 'Activate'; ?></button>
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
