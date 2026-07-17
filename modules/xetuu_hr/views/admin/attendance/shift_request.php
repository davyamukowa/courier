<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance'; $base = admin_url('xetuu_hr');
$is_edit = !empty($edit_req); $rq = $edit_req ?: null;
$status_colors = ['Pending'=>['#ca8a04','#fefce8'],'Approved'=>['#16a34a','#f0fdf4'],'Rejected'=>['#dc2626','#fef2f2'],'Cancelled'=>['#6b7280','#f3f4f6']];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Shift Requests</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Shift Requests</h1>
        </div>
        <a href="<?php echo $base.'/attendance/shift_request/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> New Request
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">
        <?php if ($is_edit): ?>
        <!-- Form -->
        <form action="<?php echo $base.'/attendance/shift_request'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <?php if ($rq): ?><input type="hidden" name="req_id" value="<?php echo $rq->id; ?>"><?php endif; ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">swap_horiz</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo $rq ? 'Edit Shift Request' : 'New Shift Request'; ?></div>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Employee *</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">— Select Employee —</option>
                                    <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo $e->id; ?>" <?php echo ($rq && $rq->employee_id == $e->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Request Type *</label>
                                <select name="request_type" class="form-control" required onchange="onTypeChange(this)">
                                    <option value="Change" <?php echo (!$rq || $rq->request_type === 'Change') ? 'selected' : ''; ?>>Shift Change</option>
                                    <option value="Swap"   <?php echo ($rq && $rq->request_type === 'Swap') ? 'selected' : ''; ?>>Shift Swap</option>
                                    <option value="Day Off"<?php echo ($rq && $rq->request_type === 'Day Off') ? 'selected' : ''; ?>>Day Off</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Request Date *</label>
                                <input type="date" name="request_date" class="form-control" required value="<?php echo $rq ? htmlspecialchars($rq->request_date) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">To Date (multi-day)</label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo $rq ? htmlspecialchars($rq->to_date ?? '') : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Current Shift</label>
                                <select name="from_shift_id" class="form-control">
                                    <option value="">— None —</option>
                                    <?php foreach ($shift_types as $st): ?>
                                    <option value="<?php echo $st->id; ?>" <?php echo ($rq && $rq->from_shift_id == $st->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" id="to-shift-wrap">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Requested Shift</label>
                                <select name="to_shift_id" class="form-control">
                                    <option value="">— None —</option>
                                    <?php foreach ($shift_types as $st): ?>
                                    <option value="<?php echo $st->id; ?>" <?php echo ($rq && $rq->to_shift_id == $st->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" id="swap-emp-wrap" style="display:none;">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Swap With Employee</label>
                                <select name="swap_with_emp_id" class="form-control">
                                    <option value="">— Select —</option>
                                    <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo $e->id; ?>" <?php echo ($rq && $rq->swap_with_emp_id == $e->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain the reason for this request..."><?php echo $rq ? htmlspecialchars($rq->reason ?? '') : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <a href="<?php echo $base.'/attendance/shift_request'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600;"><i class="fa fa-save"></i> Save Request</button>
            </div>
        </form>
        <?php else: ?>
        <!-- List -->
        <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
            <?php if (empty($requests)): ?>
            <div style="padding:48px; text-align:center; color:#9ca3af;"><span class="material-symbols-outlined" style="font-size:48px; display:block; margin-bottom:10px;">swap_horiz</span>No shift requests yet.</div>
            <?php else: ?>
            <table class="table table-hover" style="margin:0;">
                <thead><tr style="background:#f9fafb;">
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Employee</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">From Shift</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">To Shift</th>
                    <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($requests as $r): $sc = $status_colors[$r->status] ?? ['#6b7280','#f3f4f6']; ?>
                <tr>
                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($r->first_name . ' ' . $r->last_name); ?></td>
                    <td><span style="font-size:11px; background:#eff6ff; color:#2563eb; border-radius:4px; padding:2px 8px; font-weight:600;"><?php echo $r->request_type; ?></span></td>
                    <td style="font-size:13px;"><?php echo date('M j, Y', strtotime($r->request_date)); ?></td>
                    <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($r->from_shift ?? '—'); ?></td>
                    <td style="font-size:12px; color:#374151; font-weight:600;"><?php echo htmlspecialchars($r->to_shift ?? '—'); ?></td>
                    <td><span style="font-size:11px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:3px 10px; border-radius:20px;"><?php echo $r->status; ?></span></td>
                    <td style="text-align:right; padding-right:16px;">
                        <?php if ($r->status === 'Pending'): ?>
                        <a href="<?php echo $base.'/attendance/shift_request/approve/'.$r->id; ?>" class="btn btn-xs btn-success" onclick="return confirm('Approve?')">✓</a>
                        <a href="<?php echo $base.'/attendance/shift_request/reject/'.$r->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Reject?')">✗</a>
                        <?php endif; ?>
                        <a href="<?php echo $base.'/attendance/shift_request/edit/'.$r->id; ?>" class="btn btn-xs btn-default">Edit</a>
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
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Request Status</span>
                </div>
                <div style="padding:16px;">
                    <?php foreach ([['Pending',$stat_pending],['Approved',$stat_approved]] as $r): $sc = $status_colors[$r[0]]; ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f9fafb;">
                        <span style="font-size:12px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:2px 10px; border-radius:20px;"><?php echo $r[0]; ?></span>
                        <span style="font-size:16px; font-weight:800; color:#111827;"><?php echo $r[1]; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Dashboard','dashboard','attendance'],['Shift Roster','calendar_month','attendance/roster'],['Att. Requests','edit_calendar','attendance/request'],['Overtime','more_time','attendance/overtime']] as $lk): ?>
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
<script>
function onTypeChange(sel) {
    var isSwap = sel.value === 'Swap';
    document.getElementById('swap-emp-wrap').style.display = isSwap ? 'block' : 'none';
    document.getElementById('to-shift-wrap').style.display = sel.value === 'Day Off' ? 'none' : 'block';
}
</script>
<?php init_tail(); ?>
