<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$status_badges = [
    'Present'  => ['#16a34a','#f0fdf4'],
    'Late'     => ['#ca8a04','#fefce8'],
    'Absent'   => ['#dc2626','#fef2f2'],
    'Half Day' => ['#ea580c','#fff7ed'],
    'On Leave' => ['#2563eb','#eff6ff'],
    'Holiday'  => ['#9333ea','#fdf4ff'],
    'Weekend'  => ['#6b7280','#f3f4f6'],
    'No Show'  => ['#374151','#f9fafb'],
];
$att_rate = $stat_total > 0 ? round(($stat_present / $stat_total) * 100) : 0;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Daily Attendance</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Daily Attendance</h1>
        </div>
        <a href="<?php echo $base.'/attendance/bulk_tool'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">fact_check</span> Bulk Tool
        </a>
    </div>

    <!-- Stat cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php foreach ([['Total',$stat_total,'groups','#374151','#f9fafb'],['Present',$stat_present,'check_circle','#16a34a','#f0fdf4'],['Absent',$stat_absent,'cancel','#dc2626','#fef2f2'],['On Leave',$stat_leave,'beach_access','#2563eb','#eff6ff']] as $c): ?>
        <div class="col-md-3">
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
            <!-- Filter bar -->
            <form method="get" action="<?php echo $base.'/attendance/daily'; ?>">
                <div style="display:flex; gap:8px; margin-bottom:16px; align-items:flex-end;">
                    <div style="flex:0 0 160px;">
                        <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    <div style="flex:0 0 160px;">
                        <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Branch</label>
                        <select name="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b->id; ?>" <?php echo $filter_branch == $b->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:0 0 140px;">
                        <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <?php foreach (array_keys($status_badges) as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $filter_status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default" style="border-radius:6px;">Filter</button>
                    <a href="<?php echo $base.'/attendance/daily'; ?>" class="btn btn-default" style="border-radius:6px;">Reset</a>
                </div>
            </form>

            <!-- Table -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <?php if (empty($records)): ?>
                <div style="padding:48px; text-align:center; color:#9ca3af;">
                    <span class="material-symbols-outlined" style="font-size:48px; display:block; margin-bottom:10px;">today</span>
                    No attendance records for <?php echo date('M j, Y', strtotime($filter_date)); ?>.
                    <br><a href="<?php echo $base.'/attendance/bulk_tool'; ?>" style="color:#16a34a; margin-top:8px; display:inline-block;">Use Bulk Tool to mark attendance →</a>
                </div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Employee</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Shift</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Check-In</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Check-Out</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Hours</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Late</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">OT</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($records as $r):
                        $sb = $status_badges[$r->status] ?? ['#6b7280','#f3f4f6'];
                    ?>
                    <tr>
                        <td style="padding:12px 16px;">
                            <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($r->first_name . ' ' . $r->last_name); ?></div>
                            <div style="font-size:11px; color:#9ca3af;"><?php echo htmlspecialchars($r->employee_number ?? ''); ?></div>
                        </td>
                        <td>
                            <?php if ($r->shift_name): ?>
                            <span style="font-size:11px; background:<?php echo $r->color ?? '#2563eb'; ?>20; color:<?php echo $r->color ?? '#2563eb'; ?>; padding:2px 8px; border-radius:4px; font-weight:600;"><?php echo htmlspecialchars($r->shift_name); ?></span>
                            <?php else: ?><span style="color:#d1d5db; font-size:12px;">—</span><?php endif; ?>
                        </td>
                        <td style="font-size:13px; font-weight:600; color:#16a34a;"><?php echo $r->check_in ? date('H:i', strtotime($r->check_in)) : '—'; ?></td>
                        <td style="font-size:13px; font-weight:600; color:#dc2626;"><?php echo $r->check_out ? date('H:i', strtotime($r->check_out)) : '—'; ?></td>
                        <td style="font-size:13px; font-weight:700; color:#374151;"><?php echo $r->working_hours ? number_format($r->working_hours, 1).'h' : '—'; ?></td>
                        <td style="font-size:12px; color:<?php echo $r->late_minutes > 0 ? '#ca8a04' : '#9ca3af'; ?>;">
                            <?php echo $r->late_minutes > 0 ? $r->late_minutes.' min' : '—'; ?>
                        </td>
                        <td style="font-size:12px; color:<?php echo $r->overtime_hours > 0 ? '#9333ea' : '#9ca3af'; ?>;">
                            <?php echo $r->overtime_hours > 0 ? number_format($r->overtime_hours, 1).'h' : '—'; ?>
                        </td>
                        <td>
                            <span style="font-size:11px; font-weight:700; color:<?php echo $sb[0]; ?>; background:<?php echo $sb[1]; ?>; padding:3px 10px; border-radius:20px;"><?php echo $r->status; ?></span>
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
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Attendance Rate</span>
                </div>
                <div style="padding:16px; text-align:center;">
                    <div style="font-size:40px; font-weight:900; color:<?php echo $att_rate >= 80 ? '#16a34a' : ($att_rate >= 60 ? '#ca8a04' : '#dc2626'); ?>; line-height:1;"><?php echo $att_rate; ?>%</div>
                    <div style="font-size:12px; color:#6b7280; margin-top:4px;"><?php echo date('M j, Y', strtotime($filter_date)); ?></div>
                    <div style="background:#f3f4f6; border-radius:4px; height:8px; margin-top:12px;">
                        <div style="background:<?php echo $att_rate >= 80 ? '#16a34a' : ($att_rate >= 60 ? '#ca8a04' : '#dc2626'); ?>; width:<?php echo $att_rate; ?>%; height:8px; border-radius:4px;"></div>
                    </div>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Navigate</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $prev = date('Y-m-d', strtotime($filter_date . ' -1 day'));
                    $next = date('Y-m-d', strtotime($filter_date . ' +1 day'));
                    ?>
                    <a href="<?php echo $base.'/attendance/daily?date='.$prev; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">chevron_left</span>Previous Day
                    </a>
                    <a href="<?php echo $base.'/attendance/daily?date='.date('Y-m-d'); ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#16a34a; font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">today</span>Today
                    </a>
                    <a href="<?php echo $base.'/attendance/daily?date='.$next; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">chevron_right</span>Next Day
                    </a>
                </div>
            </div>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Roster','calendar_month','attendance/roster'],['Bulk Tool','fact_check','attendance/bulk_tool'],['Monthly Sheet','table_chart','attendance/monthly_sheet'],['Requests','edit_calendar','attendance/request']] as $lk): ?>
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
