<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance'; $base = admin_url('xetuu_hr');
$status_symbols = ['Present'=>'P','Late'=>'L','Absent'=>'A','Half Day'=>'H','On Leave'=>'OL','Holiday'=>'Ho','Weekend'=>'WO','No Show'=>'NS'];
$status_colors  = ['Present'=>'#16a34a','Late'=>'#ca8a04','Absent'=>'#dc2626','Half Day'=>'#ea580c','On Leave'=>'#2563eb','Holiday'=>'#9333ea','Weekend'=>'#9ca3af','No Show'=>'#374151'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Monthly Attendance Sheet</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;"><?php echo date('F Y', mktime(0,0,0,$month,1,$year)); ?> — Attendance Sheet</h1>
        </div>
        <button onclick="window.print()" class="btn btn-default" style="border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">print</span> Print
        </button>
    </div>

    <!-- Filter bar -->
    <form method="get" action="<?php echo $base.'/attendance/monthly_sheet'; ?>">
        <div style="display:flex; gap:8px; margin-bottom:16px; align-items:flex-end;">
            <div>
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Month</label>
                <select name="month" class="form-control">
                    <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Year</label>
                <select name="year" class="form-control">
                    <?php for ($y=date('Y'); $y >= date('Y')-3; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Branch</label>
                <select name="branch_id" class="form-control">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b->id; ?>" <?php echo $filter_branch == $b->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-default" style="border-radius:6px;">Generate</button>
        </div>
    </form>

    <!-- Legend -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
        <?php foreach ($status_symbols as $status => $sym): ?>
        <span style="font-size:11px; font-weight:700; color:<?php echo $status_colors[$status]; ?>; background:<?php echo $status_colors[$status]; ?>15; border-radius:4px; padding:3px 8px;">
            <?php echo $sym; ?> = <?php echo $status; ?>
        </span>
        <?php endforeach; ?>
    </div>

    <!-- Grid -->
    <div style="overflow-x:auto;">
        <table style="border-collapse:collapse; min-width:<?php echo 200 + $days * 36; ?>px; width:100%;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:10px 14px; text-align:left; font-size:12px; font-weight:700; color:#374151; border:1px solid #e5e7eb; min-width:200px; position:sticky; left:0; background:#f9fafb;">Employee</th>
                    <?php for ($d = 1; $d <= $days; $d++):
                        $day_date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $dow = date('w', strtotime($day_date));
                        $is_wknd = in_array($dow, [0, 6]);
                    ?>
                    <th style="padding:8px 4px; text-align:center; font-size:11px; font-weight:700; color:<?php echo $is_wknd ? '#9ca3af' : '#374151'; ?>; border:1px solid #e5e7eb; min-width:32px; background:<?php echo $is_wknd ? '#f5f5f5' : '#f9fafb'; ?>;">
                        <div><?php echo ['Su','Mo','Tu','We','Th','Fr','Sa'][$dow]; ?></div>
                        <div style="font-size:13px;"><?php echo $d; ?></div>
                    </th>
                    <?php endfor; ?>
                    <th style="padding:10px 8px; text-align:center; font-size:11px; font-weight:700; color:#16a34a; border:1px solid #e5e7eb; min-width:40px; background:#f0fdf4;">P</th>
                    <th style="padding:10px 8px; text-align:center; font-size:11px; font-weight:700; color:#dc2626; border:1px solid #e5e7eb; min-width:40px; background:#fef2f2;">A</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($sheet)): ?>
            <tr><td colspan="<?php echo $days + 3; ?>" style="padding:32px; text-align:center; color:#9ca3af; border:1px solid #e5e7eb;">No attendance data for this period.</td></tr>
            <?php else: foreach ($sheet as $emp_id => $emp): $p_count = 0; $a_count = 0; ?>
            <tr>
                <td style="padding:8px 14px; font-size:12px; font-weight:600; color:#111827; border:1px solid #e5e7eb; position:sticky; left:0; background:#fff; white-space:nowrap;">
                    <div><?php echo htmlspecialchars($emp['name']); ?></div>
                    <div style="font-size:10px; color:#9ca3af;"><?php echo htmlspecialchars($emp['number'] ?? ''); ?></div>
                </td>
                <?php for ($d = 1; $d <= $days; $d++):
                    $status = $emp['days'][$d] ?? null;
                    $sym    = $status ? ($status_symbols[$status] ?? '?') : '';
                    $col    = $status ? ($status_colors[$status] ?? '#9ca3af') : '#e5e7eb';
                    $day_date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                    $dow = date('w', strtotime($day_date));
                    $is_wknd = in_array($dow, [0, 6]);
                    if ($status === 'Present' || $status === 'Late') $p_count++;
                    if ($status === 'Absent') $a_count++;
                ?>
                <td style="padding:6px 2px; text-align:center; border:1px solid #e5e7eb; background:<?php echo $is_wknd ? '#fafafa' : 'transparent'; ?>;">
                    <?php if ($sym): ?>
                    <span style="display:inline-block; width:26px; height:20px; line-height:20px; font-size:10px; font-weight:700; color:<?php echo $col; ?>; background:<?php echo $col; ?>15; border-radius:3px;"><?php echo $sym; ?></span>
                    <?php endif; ?>
                </td>
                <?php endfor; ?>
                <td style="padding:8px 4px; text-align:center; font-size:13px; font-weight:800; color:#16a34a; border:1px solid #e5e7eb; background:#f0fdf4;"><?php echo $p_count; ?></td>
                <td style="padding:8px 4px; text-align:center; font-size:13px; font-weight:800; color:#dc2626; border:1px solid #e5e7eb; background:#fef2f2;"><?php echo $a_count; ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div></div>
<style>
@media print {
    .xhr-topnav, .btn, form { display: none !important; }
    .xhr-setup-page { padding: 8px !important; }
    table { font-size: 9px !important; }
}
</style>
<?php init_tail(); ?>
