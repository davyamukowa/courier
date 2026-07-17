<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $xhr_active = 'attendance'; $base = admin_url('xetuu_hr'); ?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Bulk Attendance Tool</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Bulk Attendance Tool</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Mark attendance for multiple employees at once.</p>
        </div>
    </div>

    <form action="<?php echo $base.'/attendance/bulk_tool'; ?>" method="post" id="bulk-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div class="row">
            <div class="col-md-9">
                <!-- Controls -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:16px 20px;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Date *</label>
                                    <input type="date" name="att_date" id="att-date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Shift</label>
                                    <select name="shift_type_id" class="form-control">
                                        <option value="">— No Shift —</option>
                                        <?php foreach ($shift_types as $st): ?>
                                        <option value="<?php echo $st->id; ?>"><?php echo htmlspecialchars($st->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Branch</label>
                                    <select name="branch_id" class="form-control">
                                        <option value="0">All Branches</option>
                                        <?php foreach ($branches as $b): ?>
                                        <option value="<?php echo $b->id; ?>"><?php echo htmlspecialchars($b->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Mark All As</label>
                                    <select id="mark-all-select" class="form-control" onchange="markAll(this.value)">
                                        <option value="">— Choose —</option>
                                        <option value="Present">Present</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Half Day">Half Day</option>
                                        <option value="On Leave">On Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee table -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-size:14px; font-weight:700; color:#111827;">Employees (<?php echo count($employees); ?>)</span>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="btn btn-xs btn-default" onclick="markAll('Present')" style="border-radius:4px;">All Present</button>
                            <button type="button" class="btn btn-xs btn-default" onclick="markAll('Absent')" style="border-radius:4px;">All Absent</button>
                        </div>
                    </div>
                    <?php if (empty($employees)): ?>
                    <div style="padding:40px; text-align:center; color:#9ca3af;">No employees found.</div>
                    <?php else: ?>
                    <table class="table" style="margin:0;">
                        <thead><tr style="background:#f9fafb;">
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px; width:30%;">Employee</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280; width:25%;">Status</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280; width:17%;">Check-In</th>
                            <th style="font-size:11px; text-transform:uppercase; color:#6b7280; width:17%;">Check-Out</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($employees as $e): ?>
                        <tr class="bulk-row" style="border-bottom:1px solid #f9fafb;">
                            <td style="padding:10px 16px;">
                                <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name); ?></div>
                                <div style="font-size:11px; color:#9ca3af;"><?php echo htmlspecialchars($e->employee_number ?? ''); ?></div>
                            </td>
                            <td>
                                <select name="att_status[<?php echo $e->id; ?>]" class="form-control input-sm status-select" style="font-size:12px;" onchange="onStatusChange(this)">
                                    <option value="Present" selected>Present</option>
                                    <option value="Late">Late</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Half Day">Half Day</option>
                                    <option value="On Leave">On Leave</option>
                                    <option value="Holiday">Holiday</option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="check_in[<?php echo $e->id; ?>]" class="form-control input-sm check-in-field" style="font-size:12px;" value="08:00">
                            </td>
                            <td>
                                <input type="time" name="check_out[<?php echo $e->id; ?>]" class="form-control input-sm check-out-field" style="font-size:12px;" value="17:00">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <a href="<?php echo $base.'/attendance/daily'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                    <button type="submit" name="bulk_save" value="1" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 32px; font-weight:600; font-size:14px;">
                        <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">save</span>
                        Save Attendance for <?php echo count($employees); ?> Employees
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Live Summary</span>
                    </div>
                    <div style="padding:16px;" id="summary-panel">
                        <?php foreach (['Present'=>'#16a34a','Late'=>'#ca8a04','Absent'=>'#dc2626','Half Day'=>'#ea580c','On Leave'=>'#2563eb','Holiday'=>'#9333ea'] as $s => $col): ?>
                        <div style="display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f9fafb; font-size:12px;">
                            <span style="color:#6b7280;"><?php echo $s; ?></span>
                            <span id="count-<?php echo str_replace(' ','-',strtolower($s)); ?>" style="font-weight:800; color:<?php echo $col; ?>;">0</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php foreach ([['Daily Attendance','today','attendance/daily'],['Attendance Log','login','attendance/log'],['Excel Upload','upload_file','attendance/excel_upload'],['Roster','calendar_month','attendance/roster']] as $lk): ?>
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
<script>
function markAll(status) {
    document.querySelectorAll('.status-select').forEach(function(sel) {
        sel.value = status;
        updateRowStyle(sel);
    });
    updateSummary();
    var absLike = ['Absent','Holiday','On Leave'];
    document.querySelectorAll('.check-in-field, .check-out-field').forEach(function(inp) {
        inp.style.opacity = absLike.includes(status) ? '0.4' : '1';
    });
}
function onStatusChange(sel) { updateRowStyle(sel); updateSummary(); }
function updateRowStyle(sel) {
    var absLike = ['Absent','Holiday','On Leave'];
    var row = sel.closest('tr');
    row.querySelectorAll('.check-in-field, .check-out-field').forEach(function(inp) {
        inp.style.opacity = absLike.includes(sel.value) ? '0.4' : '1';
    });
}
function updateSummary() {
    var counts = {};
    document.querySelectorAll('.status-select').forEach(function(sel) {
        counts[sel.value] = (counts[sel.value] || 0) + 1;
    });
    var keys = {'present':0,'late':0,'absent':0,'half-day':0,'on-leave':0,'holiday':0};
    Object.keys(keys).forEach(function(k) {
        var el = document.getElementById('count-' + k);
        if (el) el.textContent = 0;
    });
    Object.keys(counts).forEach(function(s) {
        var k = s.toLowerCase().replace(/ /g,'-');
        var el = document.getElementById('count-' + k);
        if (el) el.textContent = counts[s];
    });
}
// Init
document.addEventListener('DOMContentLoaded', function() { markAll('Present'); });
</script>
<?php init_tail(); ?>
