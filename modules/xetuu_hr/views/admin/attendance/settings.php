<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance'; $base = admin_url('xetuu_hr');
$s = $settings ?? [];
function as_val($s,$k,$d=''){return isset($s[$k])?htmlspecialchars($s[$k]):$d;}
function as_chk($s,$k){return (isset($s[$k])&&$s[$k]=='1')?'checked':'';}
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;"><a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> / <span style="color:#111827; font-weight:500;">Settings</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Attendance Settings</h1>
        </div>
    </div>
    <form action="<?php echo $base.'/attendance/settings'; ?>" method="post">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div class="row">
            <div class="col-md-9">

                <!-- Working Hours -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">schedule</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;">Working Hours</div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Working Days</label>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:4px;">
                                        <?php
                                        $wdays = explode(',', as_val($s, 'att_working_days', 'Mon,Tue,Wed,Thu,Fri'));
                                        foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d):
                                            $chk = in_array($d, $wdays) ? 'checked' : '';
                                        ?>
                                        <label style="display:flex; align-items:center; gap:5px; padding:6px 12px; border:1px solid <?php echo $chk ? '#16a34a' : '#e5e7eb'; ?>; border-radius:20px; cursor:pointer; background:<?php echo $chk ? '#f0fdf4' : '#fff'; ?>; font-size:12px; font-weight:600; color:<?php echo $chk ? '#16a34a' : '#374151'; ?>;">
                                            <input type="checkbox" name="att_working_days_arr[]" value="<?php echo $d; ?>" <?php echo $chk; ?> style="display:none;" onchange="var l=this.closest('label');l.style.borderColor=this.checked?'#16a34a':'#e5e7eb';l.style.background=this.checked?'#f0fdf4':'#fff';l.style.color=this.checked?'#16a34a':'#374151';">
                                            <?php echo $d; ?>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="att_working_days" id="working-days-hidden" value="<?php echo as_val($s,'att_working_days','Mon,Tue,Wed,Thu,Fri'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Standard Working Hours / Day</label>
                                    <div class="input-group">
                                        <input type="number" name="att_working_hours" class="form-control" min="1" max="24" value="<?php echo as_val($s,'att_working_hours','8'); ?>">
                                        <span class="input-group-addon">hrs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Grace In (min)</label>
                                    <input type="number" name="att_grace_in_mins" class="form-control" min="0" max="120" value="<?php echo as_val($s,'att_grace_in_mins','15'); ?>">
                                    <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">Global default (overridden per shift)</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Grace Out (min)</label>
                                    <input type="number" name="att_grace_out_mins" class="form-control" min="0" max="120" value="<?php echo as_val($s,'att_grace_out_mins','15'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auto Compute -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#f0fdf4; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">auto_mode</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;">Auto-Compute &amp; Alerts</div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <?php foreach ([
                            ['att_auto_compute','Auto-compute Daily Attendance from Logs','When enabled, check-in/out logs are automatically processed into Daily Attendance records at midnight.'],
                        ] as $t): ?>
                        <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px; margin-bottom:12px;">
                            <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                <input type="hidden" name="<?php echo $t[0]; ?>" value="0">
                                <input type="checkbox" name="<?php echo $t[0]; ?>" value="1" <?php echo as_chk($s,$t[0]); ?>>
                                <span class="xhr-toggle__slider"></span>
                            </label>
                            <div><div style="font-size:13px; font-weight:600; color:#111827;"><?php echo $t[1]; ?></div>
                            <div style="font-size:12px; color:#6b7280; margin-top:2px;"><?php echo $t[2]; ?></div></div>
                        </div>
                        <?php endforeach; ?>
                        <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px;">
                            <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                <input type="hidden" name="att_absent_alert" value="0">
                                <input type="checkbox" name="att_absent_alert" value="1" <?php echo as_chk($s,'att_absent_alert'); ?>>
                                <span class="xhr-toggle__slider"></span>
                            </label>
                            <div style="flex:1;">
                                <div style="font-size:13px; font-weight:600; color:#111827;">Send absent alerts to branch managers</div>
                                <div style="font-size:12px; color:#6b7280; margin-top:2px;">Notify managers when no check-in is detected by the alert hour.</div>
                                <div style="margin-top:10px; display:flex; align-items:center; gap:8px;">
                                    <label style="font-size:12px; color:#374151; white-space:nowrap;">Alert at</label>
                                    <input type="number" name="att_absent_alert_hour" class="form-control" min="6" max="23" value="<?php echo as_val($s,'att_absent_alert_hour','10'); ?>" style="width:70px; display:inline-block;">
                                    <label style="font-size:12px; color:#374151; white-space:nowrap;">:00 (24-hour)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OT Defaults -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#fdf4ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#9333ea;">more_time</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;">Overtime Defaults</div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Weekday OT Rate</label>
                                    <div class="input-group"><span class="input-group-addon">×</span>
                                        <input type="number" name="att_ot_weekday_mult" class="form-control" step="0.05" min="1" value="<?php echo as_val($s,'att_ot_weekday_mult','1.5'); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Weekend OT Rate</label>
                                    <div class="input-group"><span class="input-group-addon">×</span>
                                        <input type="number" name="att_ot_weekend_mult" class="form-control" step="0.05" min="1" value="<?php echo as_val($s,'att_ot_weekend_mult','2.0'); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Holiday OT Rate</label>
                                    <div class="input-group"><span class="input-group-addon">×</span>
                                        <input type="number" name="att_ot_holiday_mult" class="form-control" step="0.05" min="1" value="<?php echo as_val($s,'att_ot_holiday_mult','2.0'); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Min OT Threshold (min)</label>
                                    <input type="number" name="att_ot_min_threshold_mins" class="form-control" min="0" max="120" value="<?php echo as_val($s,'att_ot_min_threshold_mins','30'); ?>">
                                    <span style="font-size:11px; color:#9ca3af; display:block; margin-top:2px;">Must exceed shift by X min</span>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px;">
                            <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                <input type="hidden" name="att_toil_enabled" value="0">
                                <input type="checkbox" name="att_toil_enabled" value="1" <?php echo as_chk($s,'att_toil_enabled'); ?>>
                                <span class="xhr-toggle__slider"></span>
                            </label>
                            <div>
                                <div style="font-size:13px; font-weight:600; color:#111827;">Enable TOIL (Time Off in Lieu) globally</div>
                                <div style="font-size:12px; color:#6b7280; margin-top:2px;">Allows employees to choose leave credit instead of cash for overtime. Individual OT Types can override this.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600; font-size:14px;">
                        <i class="fa fa-save"></i> Save Settings
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;"><span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Setup</span></div>
                    <div style="padding:8px 0;">
                        <?php foreach ([['Shift Types','schedule','attendance/shift_types'],['Shift Schedules','event_repeat','attendance/shift_schedules'],['Overtime Types','timer','attendance/overtime_type'],['Dashboard','dashboard','attendance']] as $lk): ?>
                        <a href="<?php echo $base.'/'.$lk[2]; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $lk[1]; ?></span><?php echo $lk[0]; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div></div>
<style>
.xhr-toggle{position:relative;display:inline-block;width:40px;height:22px;}.xhr-toggle input{display:none;}.xhr-toggle__slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d1d5db;border-radius:22px;transition:.25s;}.xhr-toggle__slider:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2);}.xhr-toggle input:checked+.xhr-toggle__slider{background:#16a34a;}.xhr-toggle input:checked+.xhr-toggle__slider:before{transform:translateX(18px);}
</style>
<script>
// Sync working days checkboxes → hidden input
document.querySelectorAll('[name="att_working_days_arr[]"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var sel = Array.from(document.querySelectorAll('[name="att_working_days_arr[]"]:checked')).map(function(c){return c.value;});
        document.getElementById('working-days-hidden').value = sel.join(',');
    });
});
</script>
<?php init_tail(); ?>
