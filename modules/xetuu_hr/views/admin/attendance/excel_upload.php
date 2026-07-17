<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $xhr_active = 'attendance'; $base = admin_url('xetuu_hr'); ?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Excel Timesheet Upload</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Excel Timesheet Upload</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">For HR consultants — bulk-import attendance from an Excel/CSV timesheet.</p>
        </div>
        <a href="<?php echo base_url('uploads/hr_timesheets/template.csv'); ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;" id="dl-template">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">download</span> Download Template
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">

            <!-- Upload form -->
            <div class="panel_s" style="border:2px dashed #16a34a; border-radius:12px; margin-bottom:24px; background:#f0fdf4;">
                <div class="panel-body" style="padding:28px 24px;">
                    <div style="text-align:center; margin-bottom:20px;">
                        <span class="material-symbols-outlined" style="font-size:48px; color:#16a34a; display:block;">upload_file</span>
                        <div style="font-size:15px; font-weight:700; color:#15803d; margin-top:8px;">Upload Timesheet File</div>
                        <div style="font-size:12px; color:#6b7280; margin-top:4px;">Supports .csv, .xlsx, .xls — Max 5MB</div>
                    </div>
                    <form action="<?php echo $base.'/attendance/excel_upload'; ?>" method="post" enctype="multipart/form-data" id="upload-form">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <input type="hidden" name="do_import" value="1">
                        <div style="background:#fff; border:1px solid #bbf7d0; border-radius:8px; padding:16px; margin-bottom:16px;">
                            <input type="file" name="timesheet_file" id="timesheet-file" accept=".csv,.xlsx,.xls" required style="display:block; margin-bottom:12px;" onchange="onFileSelected(this)">
                            <div id="file-info" style="display:none; font-size:12px; color:#16a34a; font-weight:600; padding:8px; background:#f0fdf4; border-radius:6px;"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Branch *</label>
                                    <select name="branch_id" class="form-control" required>
                                        <option value="0">— All Branches —</option>
                                        <?php foreach ($branches as $b): ?>
                                        <option value="<?php echo $b->id; ?>"><?php echo htmlspecialchars($b->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Pay Period Month *</label>
                                    <select name="pay_month" class="form-control" required>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Year *</label>
                                    <select name="pay_year" class="form-control" required>
                                        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Column Mapping -->
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:700; color:#374151; margin-bottom:12px;">Column Mapping (CSV column letter → system field)</div>
                            <div class="row">
                                <?php
                                $cols = ['col_employee'=>'Employee ID / Name','col_date'=>'Date','col_in'=>'Check-In Time','col_out'=>'Check-Out Time'];
                                $defaults = ['A','B','C','D'];
                                $i = 0;
                                foreach ($cols as $n => $lbl):
                                ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size:11px; color:#6b7280; font-weight:600;"><?php echo $lbl; ?></label>
                                        <select name="<?php echo $n; ?>" class="form-control input-sm">
                                            <?php foreach (range('A','Z') as $letter): ?>
                                            <option value="<?php echo $letter; ?>" <?php echo $defaults[$i] === $letter ? 'selected' : ''; ?>><?php echo $letter; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php $i++; endforeach; ?>
                            </div>
                            <p style="font-size:11px; color:#9ca3af; margin:4px 0 0 0;">Optional columns: E=Hours Worked, F=OT Hours, G=Project, H=Notes. Employee matched by Employee Number or Full Name.</p>
                        </div>

                        <div style="text-align:center;">
                            <button type="submit" class="btn btn-success btn-lg" style="background:#16a34a; border-color:#16a34a; border-radius:8px; padding:12px 40px; font-weight:700; font-size:15px;">
                                <span class="material-symbols-outlined" style="font-size:20px; vertical-align:-4px;">upload</span> Import Timesheet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Template preview -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#ca8a04;">table_chart</span>
                    <div style="font-size:14px; font-weight:700; color:#111827;">Expected File Format</div>
                </div>
                <div style="padding:16px; overflow-x:auto;">
                    <table class="table table-bordered" style="margin:0; font-size:12px; min-width:700px;">
                        <thead><tr style="background:#fefce8;">
                            <th style="color:#ca8a04;">A — Employee ID / Name</th>
                            <th style="color:#ca8a04;">B — Date</th>
                            <th style="color:#ca8a04;">C — Check-In</th>
                            <th style="color:#ca8a04;">D — Check-Out</th>
                            <th style="color:#9ca3af;">E — Hours (optional)</th>
                            <th style="color:#9ca3af;">F — OT Hrs (optional)</th>
                        </tr></thead>
                        <tbody>
                            <tr><td>HR-EMP-00001</td><td>2025-06-01</td><td>08:00</td><td>17:00</td><td>9</td><td>1</td></tr>
                            <tr><td>John Doe</td><td>2025-06-01</td><td>08:15</td><td>17:30</td><td></td><td></td></tr>
                            <tr><td>HR-EMP-00003</td><td>2025-06-01</td><td>09:00</td><td>14:00</td><td>5</td><td>0</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Import History -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:14px; font-weight:700; color:#111827;">Import History</span>
                </div>
                <?php if (empty($imports)): ?>
                <div style="padding:32px; text-align:center; color:#9ca3af; font-size:13px;">No imports yet.</div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">#</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">File</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Period</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Rows</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Success</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Errors</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Imported</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($imports as $imp):
                        $st_color = $imp->status === 'Completed' ? '#16a34a' : ($imp->status === 'Failed' ? '#dc2626' : '#ca8a04');
                    ?>
                    <tr>
                        <td style="padding:10px 16px; font-size:13px; font-weight:600; color:#374151;"><?php echo htmlspecialchars($imp->import_number ?? '—'); ?></td>
                        <td style="font-size:12px; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($imp->original_name ?? $imp->filename); ?>"><?php echo htmlspecialchars(substr($imp->original_name ?? $imp->filename, 0, 30)); ?></td>
                        <td style="font-size:12px;"><?php echo date('M Y', mktime(0,0,0,$imp->pay_period_month,1,$imp->pay_period_year)); ?></td>
                        <td style="font-size:13px; font-weight:600;"><?php echo $imp->total_rows; ?></td>
                        <td style="font-size:13px; font-weight:700; color:#16a34a;"><?php echo $imp->success_rows; ?></td>
                        <td style="font-size:13px; font-weight:700; color:<?php echo $imp->error_rows > 0 ? '#dc2626' : '#9ca3af'; ?>;"><?php echo $imp->error_rows; ?></td>
                        <td><span style="font-size:11px; font-weight:700; color:<?php echo $st_color; ?>; background:<?php echo $st_color; ?>20; padding:3px 10px; border-radius:20px;"><?php echo $imp->status; ?></span></td>
                        <td style="font-size:11px; color:#9ca3af;"><?php echo date('M j, H:i', strtotime($imp->imported_at)); ?></td>
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
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">How It Works</span>
                </div>
                <div style="padding:16px;">
                    <?php foreach (['Download the template CSV and fill in your attendance data.','Map each column to the correct system field (A=Employee, B=Date, etc.).','Upload the file and select the branch and pay period.','Review the summary — fix errors in Excel and re-upload if needed.','Imported records appear in Daily Attendance and Attendance Log.'] as $i => $step): ?>
                    <div style="display:flex; gap:10px; margin-bottom:12px;">
                        <div style="width:22px; height:22px; border-radius:50%; background:#16a34a; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><?php echo $i+1; ?></div>
                        <p style="font-size:12px; color:#6b7280; margin:0; line-height:1.5;"><?php echo $step; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Daily Attendance','today','attendance/daily'],['Attendance Log','login','attendance/log'],['Bulk Tool','fact_check','attendance/bulk_tool'],['Monthly Sheet','table_chart','attendance/monthly_sheet']] as $lk): ?>
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
function onFileSelected(input) {
    var info = document.getElementById('file-info');
    if (input.files[0]) {
        info.style.display = 'block';
        info.textContent = '📄 ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
    }
}
// Generate CSV template on-the-fly
document.getElementById('dl-template').addEventListener('click', function(e) {
    e.preventDefault();
    var csv = "Employee ID / Name,Date,Check-In,Check-Out,Hours Worked,OT Hours,Project,Notes\n";
    csv += "HR-EMP-00001,2025-06-01,08:00,17:00,9,1,Main Office,\n";
    csv += "John Doe,2025-06-01,08:15,17:30,,,,\n";
    var blob = new Blob([csv], {type:'text/csv'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'xetuu_hr_timesheet_template.csv';
    a.click();
});
</script>
<?php init_tail(); ?>
