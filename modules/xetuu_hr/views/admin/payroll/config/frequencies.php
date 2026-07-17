<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;"><a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Config / <span style="color:#111827;">Pay Frequencies</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Pay Frequencies</h1>
        </div>
        <a href="<?php echo $base.'/payroll/config/frequencies/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New Frequency</a>
    </div>
    <div class="row">
        <div class="col-md-<?php echo $show_form ? '7' : '12'; ?>">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Every</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Pay Day</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($frequencies as $f): ?>
                    <tr>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($f->name); ?></td>
                        <td style="font-size:12px; color:#6b7280; text-transform:capitalize;"><?php echo $f->interval_type; ?></td>
                        <td style="font-size:12px; color:#374151;"><?php echo $f->interval_count; ?> <?php echo $f->interval_type; ?>(s)</td>
                        <td style="font-size:12px; color:#374151;"><?php echo $f->pay_day; ?></td>
                        <td style="text-align:right; padding-right:16px;">
                            <a href="<?php echo $base.'/payroll/config/frequencies/'.$f->id.'/edit'; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">Edit</a>
                            <a href="<?php echo $base.'/payroll/config/frequencies/'.$f->id.'/delete'; ?>" class="btn btn-xs btn-danger" style="border-radius:4px;" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($show_form): ?>
        <div class="col-md-5">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px;"><?php echo $edit_freq ? 'Edit Frequency' : 'New Pay Frequency'; ?></div>
                <form action="<?php echo $base.'/payroll/config/frequencies'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <?php if ($edit_freq): ?><input type="hidden" name="freq_id" value="<?php echo $edit_freq->id; ?>"><?php endif; ?>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_freq->name ?? ''); ?>" placeholder="e.g. Monthly">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Interval Type *</label>
                        <select name="interval_type" class="form-control">
                            <option value="month"<?php echo ($edit_freq->interval_type ?? '') === 'month' ? ' selected' : ''; ?>>Monthly</option>
                            <option value="week"<?php echo ($edit_freq->interval_type ?? '') === 'week' ? ' selected' : ''; ?>>Weekly</option>
                            <option value="biweek"<?php echo ($edit_freq->interval_type ?? '') === 'biweek' ? ' selected' : ''; ?>>Bi-Weekly</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Every N periods</label>
                                <input type="number" name="interval_count" class="form-control" min="1" value="<?php echo $edit_freq->interval_count ?? 1; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Pay Day</label>
                                <input type="number" name="pay_day" class="form-control" min="1" max="31" value="<?php echo $edit_freq->pay_day ?? 28; ?>">
                                <span style="font-size:11px; color:#9ca3af;">Day of month (1-31) or week (1=Mon)</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-save"></i> Save</button>
                        <a href="<?php echo $base.'/payroll/config/frequencies'; ?>" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php init_tail(); ?>
