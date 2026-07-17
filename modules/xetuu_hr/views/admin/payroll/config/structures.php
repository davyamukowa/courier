<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Config / <span style="color:#111827;">Salary Structures</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Salary Structures</h1>
        </div>
        <a href="<?php echo $base.'/payroll/config/structures/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New Structure</a>
    </div>
    <div class="row">
        <div class="col-md-<?php echo $show_form ? '7' : '12'; ?>">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Code</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Pay Frequency</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php if (empty($structures)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:#9ca3af;">No salary structures yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($structures as $s): ?>
                    <tr>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($s->name); ?></td>
                        <td><code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px;"><?php echo $s->code; ?></code></td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo $s->frequency_name ?? '—'; ?></td>
                        <td style="text-align:right; padding-right:16px;">
                            <?php $rules_count = get_instance()->db->where('structure_id', $s->id)->count_all_results(db_prefix().'hr_salary_rules'); ?>
                            <a href="<?php echo $base.'/payroll/config/structures/'.$s->id.'/rules'; ?>" class="btn btn-xs btn-primary" style="border-radius:4px;">Rules (<?php echo $rules_count; ?>)</a>
                            <a href="<?php echo $base.'/payroll/config/structures/'.$s->id.'/edit'; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($show_form): ?>
        <div class="col-md-5">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px;"><?php echo $edit_structure ? 'Edit Structure' : 'New Salary Structure'; ?></div>
                <form action="<?php echo $base.'/payroll/config/structures'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <?php if ($edit_structure): ?><input type="hidden" name="structure_id" value="<?php echo $edit_structure->id; ?>"><?php endif; ?>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_structure->name ?? ''); ?>" placeholder="e.g. Monthly Staff Kenya">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Code *</label>
                        <input type="text" name="code" class="form-control" required value="<?php echo htmlspecialchars($edit_structure->code ?? ''); ?>" placeholder="e.g. MONTHLY_KE">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Pay Frequency</label>
                        <select name="pay_frequency_id" class="form-control">
                            <?php foreach ($frequencies as $f): ?>
                            <option value="<?php echo $f->id; ?>"<?php echo ($edit_structure->pay_frequency_id ?? '') == $f->id ? ' selected' : ''; ?>><?php echo htmlspecialchars($f->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_structure->description ?? ''); ?></textarea>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="save_structure" value="1" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
                            <i class="fa fa-save"></i> <?php echo $edit_structure ? 'Update' : 'Create'; ?>
                        </button>
                        <a href="<?php echo $base.'/payroll/config/structures'; ?>" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php init_tail(); ?>
