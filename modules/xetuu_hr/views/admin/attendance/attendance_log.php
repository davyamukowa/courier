<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$is_edit    = !empty($edit_log);
$lg         = $edit_log ?: null;
$method_colors = ['Manual'=>['#374151','#f3f4f6'],'Mobile'=>['#2563eb','#eff6ff'],'Biometric'=>['#16a34a','#f0fdf4'],'RFID'=>['#9333ea','#fdf4ff'],'Excel Import'=>['#ca8a04','#fefce8']];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Attendance Log</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Attendance Log</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Raw check-in / check-out punch records.</p>
        </div>
        <a href="<?php echo $base.'/attendance/log/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> Manual Log
        </a>
    </div>

    <!-- Stat cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php foreach ([['Today\'s Logs',$stat_today,'login','#374151','#f9fafb'],['Check-Ins',$stat_in,'login','#16a34a','#f0fdf4'],['Check-Outs',$stat_out,'logout','#dc2626','#fef2f2']] as $c): ?>
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; display:flex; align-items:center; gap:14px;">
                <div style="width:40px; height:40px; background:<?php echo $c[4]; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $c[3]; ?>;"><?php echo $c[2]; ?></span>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:<?php echo $c[3]; ?>; line-height:1;"><?php echo $c[1]; ?></div>
                    <div style="font-size:12px; color:#6b7280; margin-top:3px;"><?php echo $c[0]; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- Inline Add/Edit Form -->
            <div class="panel_s" style="border:1px solid <?php echo $is_edit ? '#16a34a' : '#e5e7eb'; ?>; border-radius:8px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px; background:<?php echo $is_edit ? '#f0fdf4' : '#f9fafb'; ?>; border-radius:8px 8px 0 0;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">add_circle</span>
                    <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo $is_edit ? 'Edit Log Entry' : 'Add Manual Log Entry'; ?></div>
                </div>
                <div class="panel-body" style="padding:16px 20px;">
                    <form action="<?php echo $base.'/attendance/log'; ?>" method="post">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <?php if ($lg): ?><input type="hidden" name="log_id" value="<?php echo $lg->id; ?>"><?php endif; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Employee *</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">— Select —</option>
                                        <?php foreach ($employees as $e): ?>
                                        <option value="<?php echo $e->id; ?>" <?php echo ($lg && $lg->employee_id == $e->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Type *</label>
                                    <select name="log_type" class="form-control" required>
                                        <option value="IN"  <?php echo ($lg && $lg->log_type === 'IN')  ? 'selected' : ''; ?>>IN</option>
                                        <option value="OUT" <?php echo ($lg && $lg->log_type === 'OUT') ? 'selected' : ''; ?>>OUT</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Date *</label>
                                    <input type="date" name="log_date" class="form-control" required value="<?php echo $lg ? date('Y-m-d', strtotime($lg->log_datetime)) : date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Time *</label>
                                    <input type="time" name="log_time" class="form-control" required value="<?php echo $lg ? date('H:i', strtotime($lg->log_datetime)) : date('H:i'); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Branch</label>
                                    <select name="branch_id" class="form-control">
                                        <option value="0">All</option>
                                        <?php foreach ($branches as $b): ?>
                                        <option value="<?php echo $b->id; ?>" <?php echo ($lg && $lg->branch_id == $b->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Notes</label>
                                    <input type="text" name="notes" class="form-control" value="<?php echo $lg ? htmlspecialchars($lg->notes ?? '') : ''; ?>" placeholder="Optional reason">
                                </div>
                            </div>
                            <div class="col-md-6" style="display:flex; align-items:flex-end; gap:8px; padding-bottom:0;">
                                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; flex:1;">
                                    <i class="fa fa-save"></i> <?php echo $is_edit ? 'Update Log' : 'Save Log'; ?>
                                </button>
                                <?php if ($is_edit): ?>
                                <a href="<?php echo $base.'/attendance/log'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Log table -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:14px; font-weight:700; color:#111827;">Recent Logs (Last 200)</span>
                </div>
                <?php if (empty($logs)): ?>
                <div style="padding:40px; text-align:center; color:#9ca3af;">No logs yet. Add a manual entry above or upload an Excel timesheet.</div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Employee</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date &amp; Time</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Method</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Branch</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($logs as $log):
                        $mc = $method_colors[$log->method] ?? ['#374151','#f3f4f6'];
                        $is_in = $log->log_type === 'IN';
                    ?>
                    <tr>
                        <td style="padding:10px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($log->first_name . ' ' . $log->last_name); ?></td>
                        <td style="font-size:13px;"><?php echo date('M j, Y H:i', strtotime($log->log_datetime)); ?></td>
                        <td>
                            <span style="font-size:12px; font-weight:700; color:<?php echo $is_in ? '#16a34a' : '#dc2626'; ?>; background:<?php echo $is_in ? '#f0fdf4' : '#fef2f2'; ?>; padding:3px 10px; border-radius:20px;">
                                <?php echo $log->log_type; ?>
                            </span>
                        </td>
                        <td><span style="font-size:11px; color:<?php echo $mc[0]; ?>; background:<?php echo $mc[1]; ?>; padding:2px 8px; border-radius:4px;"><?php echo $log->method; ?></span></td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($log->branch_name ?? '—'); ?></td>
                        <td style="text-align:right; padding-right:16px;">
                            <a href="<?php echo $base.'/attendance/log/edit/'.$log->id; ?>" class="btn btn-xs btn-default">Edit</a>
                            <a href="<?php echo $base.'/attendance/log/delete/'.$log->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this log?')">×</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Methods</span>
                </div>
                <div style="padding:12px 16px;">
                    <?php foreach ($method_colors as $method => $mc): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f9fafb;">
                        <span style="font-size:11px; color:<?php echo $mc[0]; ?>; background:<?php echo $mc[1]; ?>; padding:2px 8px; border-radius:4px;"><?php echo $method; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Dashboard','dashboard','attendance'],['Daily Attendance','today','attendance/daily'],['Bulk Tool','fact_check','attendance/bulk_tool'],['Excel Upload','upload_file','attendance/excel_upload']] as $lk): ?>
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
