<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$is_edit    = !empty($edit_ot) || !empty($show_form);
$ot         = $edit_ot ?: null;
function otv($ot,$k,$d=''){return $ot&&isset($ot->$k)?htmlspecialchars($ot->$k):$d;}
$applies = ['Weekday','Saturday','Sunday','Holiday','Night','On-Call'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Overtime Types</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Overtime Types</h1>
        </div>
        <?php if ($is_edit): ?>
        <a href="<?php echo $base.'/attendance/overtime_type'; ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;">← Back to List</a>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-9">
        <?php if (!$is_edit): ?>
        <!-- List -->
        <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
            <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:14px; font-weight:700; color:#111827;">All Overtime Types</div>
                <a href="<?php echo $base.'/attendance/overtime_type/add'; ?>" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New OT Type</a>
            </div>
            <?php if (empty($ot_types)): ?>
            <div style="padding:40px; text-align:center; color:#9ca3af;">No overtime types defined yet.</div>
            <?php else: ?>
            <table class="table table-hover" style="margin:0;">
                <thead><tr style="background:#f9fafb;">
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Name</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Applies To</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Multiplier</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Min Threshold</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Max / Month</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">TOIL</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($ot_types as $o): ?>
                <tr>
                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($o->name); ?></td>
                    <td>
                        <?php foreach (explode(',', $o->applicable_on) as $ap): ?>
                        <span style="font-size:10px; background:#eff6ff; color:#2563eb; border-radius:4px; padding:2px 6px; margin:1px; display:inline-block;"><?php echo trim($ap); ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td style="font-size:14px; font-weight:800; color:#9333ea;">×<?php echo number_format($o->multiplier,2); ?></td>
                    <td style="font-size:13px;"><?php echo $o->min_threshold_mins; ?> min</td>
                    <td style="font-size:13px;"><?php echo $o->max_hours_per_month ? $o->max_hours_per_month.'h' : '—'; ?></td>
                    <td>
                        <?php if ($o->toil_enabled): ?>
                        <span style="font-size:11px; background:#f0fdf4; color:#16a34a; border-radius:4px; padding:2px 8px; font-weight:700;">×<?php echo $o->toil_multiplier; ?></span>
                        <?php else: ?><span style="color:#d1d5db;">—</span><?php endif; ?>
                    </td>
                    <td style="text-align:right; padding-right:16px;">
                        <a href="<?php echo $base.'/attendance/overtime_type/edit/'.$o->id; ?>" class="btn btn-xs btn-default">Edit</a>
                        <a href="<?php echo $base.'/attendance/overtime_type/delete/'.$o->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this OT type?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Form -->
        <form action="<?php echo $base.'/attendance/overtime_type'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <?php if ($ot): ?><input type="hidden" name="ot_id" value="<?php echo $ot->id; ?>"><?php endif; ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; background:#fdf4ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#9333ea;">timer</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo $ot ? 'Edit OT Type' : 'New Overtime Type'; ?></div>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?php echo otv($ot,'name'); ?>" placeholder="e.g. Sunday Overtime">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Pay Multiplier *</label>
                                <div class="input-group">
                                    <span class="input-group-addon">×</span>
                                    <input type="number" name="multiplier" class="form-control" step="0.05" min="1" required value="<?php echo otv($ot,'multiplier','1.50'); ?>">
                                </div>
                                <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">e.g. 1.5 = time and a half</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Min Threshold (min)</label>
                                <input type="number" name="min_threshold_mins" class="form-control" min="0" value="<?php echo otv($ot,'min_threshold_mins','30'); ?>">
                                <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">OT only counts if exceeded</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Applicable On *</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
                            <?php
                            $cur_applies = $ot ? explode(',', $ot->applicable_on) : [];
                            foreach ($applies as $ap):
                                $checked = in_array($ap, $cur_applies) ? 'checked' : '';
                            ?>
                            <label style="display:flex; align-items:center; gap:6px; padding:7px 14px; border:1px solid <?php echo $checked ? '#16a34a' : '#e5e7eb'; ?>; border-radius:20px; cursor:pointer; background:<?php echo $checked ? '#f0fdf4' : '#fff'; ?>; font-size:13px; font-weight:500; color:<?php echo $checked ? '#16a34a' : '#374151'; ?>;">
                                <input type="checkbox" name="applicable_on[]" value="<?php echo $ap; ?>" <?php echo $checked; ?> style="display:none;" onchange="updateChip(this)">
                                <?php echo $ap; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Max OT Hours / Day</label>
                                <input type="number" name="max_hours_per_day" class="form-control" step="0.5" min="0" value="<?php echo otv($ot,'max_hours_per_day'); ?>" placeholder="No cap">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Max OT Hours / Week</label>
                                <input type="number" name="max_hours_per_week" class="form-control" step="0.5" min="0" value="<?php echo otv($ot,'max_hours_per_week'); ?>" placeholder="No cap">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Max OT Hours / Month</label>
                                <input type="number" name="max_hours_per_month" class="form-control" step="0.5" min="0" value="<?php echo otv($ot,'max_hours_per_month'); ?>" placeholder="No cap">
                            </div>
                        </div>
                    </div>

                    <!-- TOIL section -->
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px; margin-top:8px;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                            <label class="xhr-toggle" style="flex-shrink:0;">
                                <input type="hidden" name="toil_enabled" value="0">
                                <input type="checkbox" name="toil_enabled" value="1" id="toil-toggle" <?php echo !empty($ot->toil_enabled) ? 'checked' : ''; ?> onchange="toggleToil(this)">
                                <span class="xhr-toggle__slider"></span>
                            </label>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:#15803d;">Enable Time Off in Lieu (TOIL)</div>
                                <div style="font-size:12px; color:#16a34a;">Allow employees to choose leave credit instead of cash payment.</div>
                            </div>
                        </div>
                        <div id="toil-fields" style="<?php echo empty($ot->toil_enabled) ? 'display:none;' : ''; ?>">
                            <div class="col-md-4" style="padding-left:0;">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">TOIL Multiplier</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">×</span>
                                        <input type="number" name="toil_multiplier" class="form-control" step="0.05" min="1" value="<?php echo otv($ot,'toil_multiplier','1.50'); ?>">
                                    </div>
                                    <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">1 hr OT → X hrs leave</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo otv($ot,'description'); ?></textarea>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <a href="<?php echo $base.'/attendance/overtime_type'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600;">
                    <i class="fa fa-save"></i> Save OT Type
                </button>
            </div>
        </form>
        <?php endif; ?>
        </div>

        <div class="col-md-3">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">OT Rate Guide</span>
                </div>
                <div style="padding:12px 16px;">
                    <?php foreach ([['Weekday OT','1.5×','#374151'],['Saturday OT','1.5×','#ca8a04'],['Sunday OT','2.0×','#dc2626'],['Holiday OT','2.0–3.0×','#9333ea'],['Night Diff','+allowance','#2563eb']] as $g): ?>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f9fafb; font-size:12px;">
                        <span style="color:#6b7280;"><?php echo $g[0]; ?></span>
                        <span style="font-weight:700; color:<?php echo $g[2]; ?>;"><?php echo $g[1]; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Overtime Slips','more_time','attendance/overtime'],['Shift Types','schedule','attendance/shift_types'],['Dashboard','dashboard','attendance']] as $lk): ?>
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
<style>
.xhr-toggle{position:relative;display:inline-block;width:40px;height:22px;}.xhr-toggle input{display:none;}.xhr-toggle__slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d1d5db;border-radius:22px;transition:.25s;}.xhr-toggle__slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2);}.xhr-toggle input:checked+.xhr-toggle__slider{background:#16a34a;}.xhr-toggle input:checked+.xhr-toggle__slider:before{transform:translateX(18px);}
</style>
<script>
function updateChip(cb) {
    var lbl = cb.closest('label');
    if (cb.checked) { lbl.style.borderColor='#16a34a'; lbl.style.background='#f0fdf4'; lbl.style.color='#16a34a'; }
    else            { lbl.style.borderColor='#e5e7eb'; lbl.style.background='#fff';    lbl.style.color='#374151'; }
}
function toggleToil(cb) { document.getElementById('toil-fields').style.display = cb.checked ? 'block' : 'none'; }
</script>
<?php init_tail(); ?>
