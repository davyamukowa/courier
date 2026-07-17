<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $xhr_active = 'attendance'; $base = admin_url('xetuu_hr');
$is_edit = !empty($edit_req); $rq = $edit_req ?: null;
$status_colors = ['Pending'=>['#ca8a04','#fefce8'],'Approved'=>['#16a34a','#f0fdf4'],'Rejected'=>['#dc2626','#fef2f2']]; ?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Attendance Requests</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Attendance Requests</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Employees request corrections to missed or wrong attendance records.</p>
        </div>
        <a href="<?php echo $base.'/attendance/request/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> New Request
        </a>
    </div>

    <!-- Stat cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php foreach ([['Pending',$stat_pending,'pending_actions','#ca8a04','#fefce8'],['Approved',$stat_approved,'check_circle','#16a34a','#f0fdf4'],['Rejected',$stat_rejected,'cancel','#dc2626','#fef2f2']] as $c): ?>
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; display:flex; align-items:center; gap:14px;">
                <div style="width:40px; height:40px; background:<?php echo $c[4]; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $c[3]; ?>;"><?php echo $c[2]; ?></span>
                </div>
                <div>
                    <div style="font-size:26px; font-weight:800; color:<?php echo $c[3]; ?>; line-height:1;"><?php echo $c[1]; ?></div>
                    <div style="font-size:12px; color:#6b7280; margin-top:3px;"><?php echo $c[0]; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-9">
        <?php if ($is_edit): ?>
        <form action="<?php echo $base.'/attendance/request'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <?php if ($rq): ?><input type="hidden" name="request_id" value="<?php echo $rq->id; ?>"><?php endif; ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">edit_calendar</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo $rq ? 'Edit Attendance Request' : 'New Attendance Request'; ?></div>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Employee *</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">— Select —</option>
                                    <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo $e->id; ?>" <?php echo ($rq && $rq->employee_id == $e->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Attendance Date *</label>
                                <input type="date" name="attendance_date" class="form-control" required value="<?php echo $rq ? htmlspecialchars($rq->attendance_date) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Requested Status</label>
                                <select name="requested_status" class="form-control">
                                    <option value="">No Change</option>
                                    <?php foreach (['Present','Late','Half Day','On Leave','Absent'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($rq && $rq->requested_status === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Correct Check-In</label>
                                <input type="datetime-local" name="requested_check_in" class="form-control" value="<?php echo $rq ? str_replace(' ','T', $rq->requested_check_in ?? '') : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Correct Check-Out</label>
                                <input type="datetime-local" name="requested_check_out" class="form-control" value="<?php echo $rq ? str_replace(' ','T', $rq->requested_check_out ?? '') : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain what happened and why the correction is needed..."><?php echo $rq ? htmlspecialchars($rq->reason) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <a href="<?php echo $base.'/attendance/request'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600;"><i class="fa fa-save"></i> Save Request</button>
            </div>
        </form>
        <?php else: ?>
        <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
            <?php if (empty($requests)): ?>
            <div style="padding:48px; text-align:center; color:#9ca3af;">No attendance requests yet.</div>
            <?php else: ?>
            <table class="table table-hover" style="margin:0;">
                <thead><tr style="background:#f9fafb;">
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Employee</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Req. Check-In</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Req. Check-Out</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Req. Status</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($requests as $r): $sc = $status_colors[$r->status] ?? ['#6b7280','#f3f4f6']; ?>
                <tr>
                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($r->first_name . ' ' . $r->last_name); ?></td>
                    <td style="font-size:13px;"><?php echo date('M j, Y', strtotime($r->attendance_date)); ?></td>
                    <td style="font-size:13px; color:#16a34a;"><?php echo $r->requested_check_in ? date('H:i', strtotime($r->requested_check_in)) : '—'; ?></td>
                    <td style="font-size:13px; color:#dc2626;"><?php echo $r->requested_check_out ? date('H:i', strtotime($r->requested_check_out)) : '—'; ?></td>
                    <td style="font-size:12px;"><?php echo $r->requested_status ?: '—'; ?></td>
                    <td><span style="font-size:11px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:3px 10px; border-radius:20px;"><?php echo $r->status; ?></span></td>
                    <td style="text-align:right; padding-right:16px;">
                        <?php if ($r->status === 'Pending'): ?>
                        <a href="<?php echo $base.'/attendance/request/approve/'.$r->id; ?>" class="btn btn-xs btn-success" onclick="return confirm('Approve and update attendance?')">Approve</a>
                        <a href="<?php echo $base.'/attendance/request/reject/'.$r->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Reject?')">Reject</a>
                        <?php endif; ?>
                        <a href="<?php echo $base.'/attendance/request/edit/'.$r->id; ?>" class="btn btn-xs btn-default">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        </div>

        <div class="col-md-3">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;"><span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Tip</span></div>
                <div style="padding:16px; font-size:12px; color:#6b7280; line-height:1.6;">
                    <span class="material-symbols-outlined" style="font-size:16px; color:#2563eb; vertical-align:-3px;">info</span>
                    Approving a request automatically updates the employee's Daily Attendance record. The original record is not deleted — the change is noted in the source field.
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;"><span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span></div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Dashboard','dashboard','attendance'],['Daily Attendance','today','attendance/daily'],['Shift Requests','swap_horiz','attendance/shift_request']] as $lk): ?>
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
