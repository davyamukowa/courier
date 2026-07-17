<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$is_edit    = !empty($edit_st) || !empty($show_form);
$st         = $edit_st ?: null;
function stv($st, $k, $d = '') { return $st && isset($st->$k) ? htmlspecialchars($st->$k) : $d; }
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Xetuu HR</a> /
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Shift Types</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Shift Types</h1>
        </div>
        <?php if ($is_edit): ?>
        <a href="<?php echo $base.'/attendance/shift_types'; ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">arrow_back</span> Back to List
        </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Form / List -->
        <div class="col-md-9">

            <?php if (!$is_edit): ?>
            <!-- List view -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:14px; font-weight:700; color:#111827;">All Shift Types</div>
                    <a href="<?php echo $base.'/attendance/shift_types/add'; ?>" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
                        <span class="material-symbols-outlined" style="font-size:14px; vertical-align:-2px;">add</span> New Shift Type
                    </a>
                </div>
                <div style="padding:0;">
                    <?php if (empty($shift_types)): ?>
                    <div style="padding:40px; text-align:center; color:#9ca3af;">
                        <span class="material-symbols-outlined" style="font-size:48px; display:block; margin-bottom:8px;">schedule</span>
                        No shift types yet. <a href="<?php echo $base.'/attendance/shift_types/add'; ?>" style="color:#16a34a;">Create the first one →</a>
                    </div>
                    <?php else: ?>
                    <table class="table table-hover" style="margin:0;">
                        <thead><tr style="background:#f9fafb;">
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Name</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Start</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">End</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Hours</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Grace In</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Night</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280;"></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($shift_types as $s): ?>
                        <tr>
                            <td style="padding:12px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:12px; height:12px; border-radius:3px; background:<?php echo $s->color ?: '#2563eb'; ?>; display:inline-block; flex-shrink:0;"></span>
                                    <span style="font-weight:600; color:#111827; font-size:13px;"><?php echo htmlspecialchars($s->name); ?></span>
                                    <?php if (!$s->active): ?>
                                    <span style="font-size:10px; background:#f3f4f6; color:#9ca3af; border-radius:4px; padding:1px 6px;">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="font-size:13px;"><?php echo date('H:i', strtotime($s->start_time)); ?></td>
                            <td style="font-size:13px;"><?php echo date('H:i', strtotime($s->end_time)); ?></td>
                            <td style="font-size:13px;"><?php echo number_format($s->working_hours, 1); ?>h</td>
                            <td style="font-size:13px;"><?php echo ($s->grace_in_mins ?? 15); ?> min</td>
                            <td>
                                <?php if (!empty($s->is_night_shift)): ?>
                                <span style="font-size:11px; background:#fdf4ff; color:#9333ea; border-radius:4px; padding:2px 8px;">Night</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; padding-right:16px;">
                                <a href="<?php echo $base.'/attendance/shift_types/edit/'.$s->id; ?>" class="btn btn-xs btn-default">Edit</a>
                                <a href="<?php echo $base.'/attendance/shift_types/delete/'.$s->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Deactivate this shift type?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Edit / Add form -->
            <form action="<?php echo $base.'/attendance/shift_types'; ?>" method="post">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <?php if ($edit_st): ?><input type="hidden" name="st_id" value="<?php echo $edit_st->id; ?>"><?php endif; ?>

                <!-- Basic Info -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">schedule</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo $edit_st ? 'Edit Shift Type' : 'New Shift Type'; ?></div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shift Name *</label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo stv($st,'name'); ?>" placeholder="e.g. Morning Shift">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Code</label>
                                    <input type="text" name="code" class="form-control" value="<?php echo stv($st,'code'); ?>" placeholder="MRN" maxlength="10" style="text-transform:uppercase;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Color</label>
                                    <input type="color" name="color" class="form-control" value="<?php echo stv($st,'color','#2563eb'); ?>" style="height:38px; padding:2px 4px; cursor:pointer;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Branch</label>
                                    <select name="branch_id" class="form-control">
                                        <option value="">All Branches</option>
                                        <?php foreach ($branches as $b): ?>
                                        <option value="<?php echo $b->id; ?>" <?php echo stv($st,'branch_id') == $b->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Start Time *</label>
                                    <input type="time" name="start_time" class="form-control" required value="<?php echo stv($st,'start_time','08:00'); ?>" onchange="calcHours()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">End Time *</label>
                                    <input type="time" name="end_time" class="form-control" required value="<?php echo stv($st,'end_time','17:00'); ?>" onchange="calcHours()">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Working Hours</label>
                                    <input type="number" name="working_hours" id="working-hours" class="form-control" step="0.5" min="0.5" max="24" value="<?php echo stv($st,'working_hours','8'); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Grace In (min)</label>
                                    <input type="number" name="grace_in_mins" class="form-control" min="0" max="120" value="<?php echo stv($st,'grace_in_mins','15'); ?>">
                                    <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">Late after</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Grace Out (min)</label>
                                    <input type="number" name="grace_out_mins" class="form-control" min="0" max="120" value="<?php echo stv($st,'grace_out_mins','15'); ?>">
                                    <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">Early exit after</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Min Hours (Half Day)</label>
                                    <input type="number" name="min_hours_half_day" class="form-control" step="0.5" value="<?php echo stv($st,'min_hours_half_day','4'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Min Hours (Full Day)</label>
                                    <input type="number" name="min_hours_full_day" class="form-control" step="0.5" value="<?php echo stv($st,'min_hours_full_day','8'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Description</label>
                                    <input type="text" name="description" class="form-control" value="<?php echo stv($st,'description'); ?>" placeholder="Optional description">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Night Shift -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:12px;">
                        <label class="xhr-toggle" style="flex-shrink:0;">
                            <input type="hidden" name="is_night_shift" value="0">
                            <input type="checkbox" name="is_night_shift" value="1" id="night-toggle" <?php echo !empty($st->is_night_shift) ? 'checked' : ''; ?> onchange="toggleNight(this)">
                            <span class="xhr-toggle__slider"></span>
                        </label>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Night Shift</div>
                            <div style="font-size:12px; color:#6b7280;">Enable night shift differential allowance for hours within the defined night window.</div>
                        </div>
                    </div>
                    <div id="night-fields" class="panel-body" style="padding:20px; <?php echo empty($st->is_night_shift) ? 'display:none;' : ''; ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Night Window Start</label>
                                    <input type="time" name="night_start" class="form-control" value="<?php echo stv($st,'night_start','22:00'); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Night Window End</label>
                                    <input type="time" name="night_end" class="form-control" value="<?php echo stv($st,'night_end','06:00'); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Night Allowance (flat per shift)</label>
                                    <input type="number" name="night_allowance" class="form-control" step="0.01" min="0" value="<?php echo stv($st,'night_allowance','0'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <a href="<?php echo $base.'/attendance/shift_types'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600;">
                        <i class="fa fa-save"></i> <?php echo $edit_st ? 'Update Shift Type' : 'Create Shift Type'; ?>
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                        <?php echo $is_edit ? 'Existing Shifts' : 'Quick Stats'; ?>
                    </span>
                </div>
                <div style="padding:12px 16px;">
                    <?php if ($is_edit): ?>
                    <a href="<?php echo $base.'/attendance/shift_types/add'; ?>" class="btn btn-success btn-block" style="background:#16a34a; border-color:#16a34a; border-radius:6px; margin-bottom:12px; font-size:13px;">+ New Shift Type</a>
                    <?php foreach ($shift_types as $s): ?>
                    <a href="<?php echo $base.'/attendance/shift_types/edit/'.$s->id; ?>" style="display:flex; align-items:center; gap:8px; padding:7px 0; text-decoration:none; border-bottom:1px solid #f9fafb;">
                        <span style="width:10px; height:10px; border-radius:3px; background:<?php echo $s->color ?: '#2563eb'; ?>; flex-shrink:0;"></span>
                        <span style="font-size:13px; color:<?php echo ($st && $st->id == $s->id) ? '#16a34a' : '#374151'; ?>; font-weight:<?php echo ($st && $st->id == $s->id) ? '700' : '400'; ?>;">
                            <?php echo htmlspecialchars($s->name); ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div style="text-align:center; padding:8px 0;">
                        <div style="font-size:32px; font-weight:800; color:#16a34a;"><?php echo count($shift_types); ?></div>
                        <div style="font-size:12px; color:#6b7280;">Shift Types Defined</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Setup</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Shift Schedules','event_repeat','shift_schedules'],['Overtime Types','timer','overtime_type'],['Settings','settings','settings']] as $sl): ?>
                    <a href="<?php echo $base.'/attendance/'.$sl[2]; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $sl[1]; ?></span><?php echo $sl[0]; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.xhr-toggle{position:relative;display:inline-block;width:40px;height:22px;}
.xhr-toggle input{display:none;}
.xhr-toggle__slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d1d5db;border-radius:22px;transition:.25s;}
.xhr-toggle__slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.xhr-toggle input:checked+.xhr-toggle__slider{background:#16a34a;}
.xhr-toggle input:checked+.xhr-toggle__slider:before{transform:translateX(18px);}
</style>
<script>
function calcHours() {
    var s = document.querySelector('[name=start_time]').value;
    var e = document.querySelector('[name=end_time]').value;
    if (s && e) {
        var sm = parseInt(s.split(':')[0])*60+parseInt(s.split(':')[1]);
        var em = parseInt(e.split(':')[0])*60+parseInt(e.split(':')[1]);
        var diff = em > sm ? (em - sm) : (1440 - sm + em);
        document.getElementById('working-hours').value = (diff/60).toFixed(1);
    }
}
function toggleNight(cb) {
    document.getElementById('night-fields').style.display = cb.checked ? 'block' : 'none';
}
</script>

</div></div>
<?php init_tail(); ?>
