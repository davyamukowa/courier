<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $xhr_active = 'attendance'; $base = admin_url('xetuu_hr'); ?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;"><a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> / <span style="color:#111827; font-weight:500;">Shift Schedules</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Shift Schedules</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <!-- Quick add form -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px; background:#f9fafb; border-radius:8px 8px 0 0;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">add_circle</span>
                    <div style="font-size:14px; font-weight:700; color:#111827;">Add New Schedule</div>
                </div>
                <div class="panel-body" style="padding:16px 20px;">
                    <form action="<?php echo $base.'/attendance/shift_schedules'; ?>" method="post">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Standard 5-Day">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Type</label>
                                    <select name="type" class="form-control">
                                        <option value="Fixed">Fixed</option>
                                        <option value="Rotating">Rotating</option>
                                        <option value="Flexible">Flexible</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Rotation Weeks</label>
                                    <input type="number" name="rotation_weeks" class="form-control" min="1" max="8" value="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-save"></i> Save Schedule</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;"><span style="font-size:14px; font-weight:700; color:#111827;">All Schedules</span></div>
                <?php if (empty($schedules)): ?>
                <div style="padding:40px; text-align:center; color:#9ca3af;">No schedules yet. Add one above.</div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Rotation</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Description</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($schedules as $sch): ?>
                    <tr>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($sch->name); ?></td>
                        <td><span style="font-size:11px; background:#eff6ff; color:#2563eb; border-radius:4px; padding:2px 8px; font-weight:600;"><?php echo $sch->type; ?></span></td>
                        <td style="font-size:13px; color:#6b7280;"><?php echo $sch->rotation_weeks; ?> week(s)</td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($sch->description ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;"><span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Schedule Types</span></div>
                <div style="padding:16px; font-size:12px; color:#6b7280; line-height:1.7;">
                    <strong style="color:#111827;">Fixed</strong> — Same shifts every week.<br>
                    <strong style="color:#111827;">Rotating</strong> — Shifts rotate on a weekly cycle (Week A / Week B).<br>
                    <strong style="color:#111827;">Flexible</strong> — No fixed pattern; rely on daily roster assignments.
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;"><span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Setup</span></div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Shift Types','schedule','attendance/shift_types'],['Roster','calendar_month','attendance/roster'],['Settings','settings','attendance/settings']] as $lk): ?>
                    <a href="<?php echo $base.'/'.$lk[2]; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $lk[1]; ?></span><?php echo $lk[0]; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div></div>
<?php init_tail(); ?>
