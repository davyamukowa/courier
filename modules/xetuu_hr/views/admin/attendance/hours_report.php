<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $xhr_active = 'attendance'; $base = admin_url('xetuu_hr');
$total_hrs = array_sum(array_column(array_map(function($r){return (array)$r;},$records??[]),'total_hours'));
$total_ot  = array_sum(array_column(array_map(function($r){return (array)$r;},$records??[]),'total_ot'));
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;"><a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> / <span style="color:#111827; font-weight:500;">Hours Utilization</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Employee Hours Utilization — <?php echo date('F Y', mktime(0,0,0,$month,1,$year)); ?></h1>
        </div>
        <button onclick="window.print()" class="btn btn-default" style="border-radius:6px; font-size:13px;"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">print</span> Print</button>
    </div>

    <form method="get" action="<?php echo $base.'/attendance/hours_report'; ?>">
        <div style="display:flex; gap:8px; margin-bottom:20px; align-items:flex-end;">
            <div><label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Month</label>
                <select name="month" class="form-control"><?php for ($m=1;$m<=12;$m++): ?><option value="<?php echo $m; ?>" <?php echo $m==$month?'selected':''; ?>><?php echo date('F',mktime(0,0,0,$m,1)); ?></option><?php endfor; ?></select>
            </div>
            <div><label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Year</label>
                <select name="year" class="form-control"><?php for ($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?php echo $y; ?>" <?php echo $y==$year?'selected':''; ?>><?php echo $y; ?></option><?php endfor; ?></select>
            </div>
            <button type="submit" class="btn btn-default" style="border-radius:6px;">Generate</button>
        </div>
    </form>

    <!-- Totals -->
    <div class="row" style="margin-bottom:20px;">
        <?php foreach ([['Total Hours',number_format($total_hrs,1).'h','schedule','#2563eb','#eff6ff'],['Total OT Hours',number_format($total_ot,1).'h','more_time','#9333ea','#fdf4ff'],['Employees',count($records),'group','#16a34a','#f0fdf4']] as $c): ?>
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; display:flex; align-items:center; gap:14px;">
                <div style="width:40px; height:40px; background:<?php echo $c[4]; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $c[3]; ?>;"><?php echo $c[2]; ?></span>
                </div>
                <div>
                    <div style="font-size:22px; font-weight:800; color:<?php echo $c[3]; ?>; line-height:1;"><?php echo $c[1]; ?></div>
                    <div style="font-size:12px; color:#6b7280; margin-top:3px;"><?php echo $c[0]; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
        <?php if (empty($records)): ?>
        <div style="padding:48px; text-align:center; color:#9ca3af;">No data for this period. Mark attendance first.</div>
        <?php else: ?>
        <table class="table table-hover" style="margin:0;">
            <thead><tr style="background:#f9fafb;">
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Employee</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Days Present</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Total Hours</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">OT Hours</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Late Count</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Utilization</th>
            </tr></thead>
            <tbody>
            <?php
            $work_days = 22; // approx working days per month
            $std_hours = $work_days * 8;
            foreach ($records as $r):
                $util = $std_hours > 0 ? min(100, round(($r->total_hours / $std_hours) * 100)) : 0;
                $util_color = $util >= 90 ? '#16a34a' : ($util >= 70 ? '#ca8a04' : '#dc2626');
            ?>
            <tr>
                <td style="padding:12px 16px;">
                    <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($r->first_name . ' ' . $r->last_name); ?></div>
                    <div style="font-size:11px; color:#9ca3af;"><?php echo htmlspecialchars($r->employee_number ?? ''); ?></div>
                </td>
                <td style="font-size:13px; font-weight:600; color:#374151;"><?php echo $r->days_present; ?></td>
                <td style="font-size:14px; font-weight:800; color:#2563eb;"><?php echo number_format($r->total_hours, 1); ?>h</td>
                <td style="font-size:13px; font-weight:700; color:<?php echo $r->total_ot > 0 ? '#9333ea' : '#9ca3af'; ?>;"><?php echo number_format($r->total_ot, 1); ?>h</td>
                <td style="font-size:13px; color:<?php echo $r->late_count > 0 ? '#ca8a04' : '#9ca3af'; ?>;"><?php echo $r->late_count; ?></td>
                <td style="min-width:140px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="background:#f3f4f6; border-radius:4px; height:8px; flex:1;">
                            <div style="background:<?php echo $util_color; ?>; width:<?php echo $util; ?>%; height:8px; border-radius:4px;"></div>
                        </div>
                        <span style="font-size:12px; font-weight:700; color:<?php echo $util_color; ?>; min-width:36px;"><?php echo $util; ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</div></div>
<?php init_tail(); ?>
