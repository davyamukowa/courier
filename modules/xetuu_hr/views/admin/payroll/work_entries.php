<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$source_labels = ['manual'=>'Manual','attendance_module'=>'Attendance Module','leave_module'=>'Leave Module','upload'=>'Upload'];
$type_colors = ['work'=>'#16a34a','leave'=>'#2563eb','overtime'=>'#d97706','absence'=>'#dc2626'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;"><a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / <span style="color:#111827;">Work Entries</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Work Entries</h1>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="<?php echo $base.'/payroll/work_entries/upload'; ?>" class="btn btn-default" style="border-radius:6px;"><i class="fa fa-upload"></i> Upload Timesheet</a>
            <a href="<?php echo $base.'/payroll/work_entries/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ Manual Entry</a>
        </div>
    </div>

    <?php if ($show_upload): ?>
    <!-- Timesheet Upload Panel -->
    <div class="row">
        <div class="col-md-6">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;"><i class="fa fa-upload"></i> Upload Timesheet</div>
                <form action="<?php echo $base.'/payroll/work_entries/upload'; ?>" method="post" enctype="multipart/form-data">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Company *</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">— Select Company —</option>
                            <?php foreach ($companies as $co): ?>
                            <option value="<?php echo $co->id; ?>"><?php echo htmlspecialchars($co->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Period From *</label>
                                <input type="date" name="date_from" class="form-control" required value="<?php echo date('Y-m-01'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Period To *</label>
                                <input type="date" name="date_to" class="form-control" required value="<?php echo date('Y-m-t'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">CSV / Excel File *</label>
                        <input type="file" name="timesheet_file" accept=".csv,.xlsx,.xls" class="form-control" required style="padding:8px; font-size:13px;">
                    </div>
                    <button type="submit" name="upload_timesheet" value="1" class="btn btn-primary" style="border-radius:6px;"><i class="fa fa-upload"></i> Upload &amp; Import</button>
                    <a href="<?php echo $base.'/payroll/work_entries'; ?>" class="btn btn-default" style="border-radius:6px; margin-left:8px;">Cancel</a>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
                <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:10px;">CSV Format</div>
                <table class="table" style="font-size:12px; margin:0;">
                    <thead><tr style="background:#f9fafb;"><th>Column</th><th>Format</th><th>Required</th></tr></thead>
                    <tbody>
                    <tr><td><code>employee_id</code></td><td>Employee system ID</td><td style="color:#16a34a;">Yes</td></tr>
                    <tr><td><code>entry_date</code></td><td>YYYY-MM-DD</td><td style="color:#16a34a;">Yes</td></tr>
                    <tr><td><code>entry_type</code></td><td>work / leave / overtime / absence</td><td style="color:#16a34a;">Yes</td></tr>
                    <tr><td><code>hours</code></td><td>Decimal (e.g. 8.5)</td><td style="color:#16a34a;">Yes</td></tr>
                    <tr><td><code>notes</code></td><td>Any text</td><td style="color:#9ca3af;">Optional</td></tr>
                    </tbody>
                </table>
                <a href="<?php echo $base.'/payroll/work_entries/download_template'; ?>" class="btn btn-xs btn-default" style="margin-top:12px; border-radius:4px;"><i class="fa fa-download"></i> Download Template</a>
            </div>
        </div>
    </div>

    <?php elseif ($show_add_form): ?>
    <!-- Manual Entry Form -->
    <div class="row">
        <div class="col-md-5">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Manual Work Entry</div>
                <form action="<?php echo $base.'/payroll/work_entries'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">— Select Employee —</option>
                            <?php foreach ($employees as $e): ?>
                            <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars($e->first_name.' '.$e->last_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Date *</label>
                        <input type="date" name="entry_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Type *</label>
                                <select name="entry_type" class="form-control" required>
                                    <option value="work">Work</option>
                                    <option value="leave">Leave</option>
                                    <option value="overtime">Overtime</option>
                                    <option value="absence">Absence</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Hours *</label>
                                <input type="number" name="hours" class="form-control" step="0.5" min="0" max="24" required value="8">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional">
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="save_work_entry" value="1" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-save"></i> Save</button>
                        <a href="<?php echo $base.'/payroll/work_entries'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Work Entries List -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
        <div style="padding:12px 16px; border-bottom:1px solid #f3f4f6; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="date" id="we-from" class="form-control" style="max-width:150px; font-size:13px;" value="<?php echo date('Y-m-01'); ?>">
            <span style="color:#9ca3af; font-size:13px;">to</span>
            <input type="date" id="we-to" class="form-control" style="max-width:150px; font-size:13px;" value="<?php echo date('Y-m-t'); ?>">
            <select id="we-type" class="form-control" style="max-width:140px; font-size:13px;">
                <option value="">All Types</option>
                <option value="work">Work</option>
                <option value="leave">Leave</option>
                <option value="overtime">Overtime</option>
                <option value="absence">Absence</option>
            </select>
            <select id="we-source" class="form-control" style="max-width:180px; font-size:13px;">
                <option value="">All Sources</option>
                <option value="manual">Manual</option>
                <option value="attendance_module">Attendance Module</option>
                <option value="leave_module">Leave Module</option>
                <option value="upload">Upload</option>
            </select>
        </div>
        <table class="table table-hover" style="margin:0;">
            <thead><tr style="background:#f9fafb;">
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Employee</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Hours</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Source</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Notes</th>
            </tr></thead>
            <tbody id="we-tbody">
            <?php if (empty($entries)): ?>
            <tr><td colspan="6" style="text-align:center; padding:40px; color:#9ca3af;">No work entries found for this period.</td></tr>
            <?php else: ?>
            <?php foreach ($entries as $e): $tc = $type_colors[$e->entry_type] ?? '#6b7280'; ?>
            <tr>
                <td style="padding:10px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($e->employee_name ?? '—'); ?></td>
                <td style="font-size:12px; color:#374151;"><?php echo $e->entry_date; ?></td>
                <td><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $tc; ?>20; color:<?php echo $tc; ?>; text-transform:capitalize;"><?php echo $e->entry_type; ?></span></td>
                <td style="font-size:13px; font-weight:600; color:#111827;"><?php echo $e->hours; ?>h</td>
                <td style="font-size:11px; color:#9ca3af;"><?php echo $source_labels[$e->source] ?? $e->source; ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($e->notes ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php init_tail(); ?>
