<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$today_label = date('l, F j, Y');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Xetuu HR</a>
                <span>/</span><span style="color:#111827; font-weight:500;">Shift &amp; Attendance</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Shift &amp; Attendance</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;"><?php echo $today_label; ?></p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="<?php echo $base . '/attendance/bulk_tool'; ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">fact_check</span> Bulk Tool
            </a>
            <a href="<?php echo $base . '/attendance/log/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> Mark Attendance
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php
        $cards = [
            ['label'=>'Present Today',      'val'=>$stat_present,          'icon'=>'check_circle', 'color'=>'#16a34a', 'bg'=>'#f0fdf4', 'link'=>$base.'/attendance/daily?status=Present'],
            ['label'=>'Late Today',         'val'=>$stat_late,             'icon'=>'schedule',     'color'=>'#ca8a04', 'bg'=>'#fefce8', 'link'=>$base.'/attendance/daily?status=Late'],
            ['label'=>'Absent Today',       'val'=>$stat_absent,           'icon'=>'cancel',       'color'=>'#dc2626', 'bg'=>'#fef2f2', 'link'=>$base.'/attendance/daily?status=Absent'],
            ['label'=>'On Leave Today',     'val'=>$stat_on_leave,         'icon'=>'beach_access', 'color'=>'#2563eb', 'bg'=>'#eff6ff', 'link'=>$base.'/attendance/daily?status=On+Leave'],
            ['label'=>'Pending OT Slips',   'val'=>$stat_ot_pending,       'icon'=>'more_time',    'color'=>'#9333ea', 'bg'=>'#fdf4ff', 'link'=>$base.'/attendance/overtime'],
            ['label'=>'Pending Requests',   'val'=>$stat_requests_pending, 'icon'=>'pending_actions','color'=>'#ea580c','bg'=>'#fff7ed', 'link'=>$base.'/attendance/request'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="col-md-2">
            <a href="<?php echo $c['link']; ?>" style="text-decoration:none; display:block;">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center; transition:.15s;">
                    <div style="width:40px; height:40px; background:<?php echo $c['bg']; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                        <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $c['color']; ?>;"><?php echo $c['icon']; ?></span>
                    </div>
                    <div style="font-size:26px; font-weight:800; color:<?php echo $c['color']; ?>; line-height:1;"><?php echo $c['val']; ?></div>
                    <div style="font-size:11px; color:#6b7280; margin-top:4px; font-weight:500;"><?php echo $c['label']; ?></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- Main col-md-9 -->
        <div class="col-md-9">

            <!-- 30-day trend chart -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#f0fdf4; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">show_chart</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;">30-Day Attendance Trend</div>
                    </div>
                    <div style="display:flex; gap:16px; font-size:12px;">
                        <span style="display:flex; align-items:center; gap:4px;"><span style="width:10px; height:10px; background:#16a34a; border-radius:2px; display:inline-block;"></span>Present</span>
                        <span style="display:flex; align-items:center; gap:4px;"><span style="width:10px; height:10px; background:#ef4444; border-radius:2px; display:inline-block;"></span>Absent</span>
                    </div>
                </div>
                <div style="padding:16px 20px;">
                    <canvas id="trend-chart" height="80"></canvas>
                </div>
            </div>

            <!-- Recent check-ins -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">login</span>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#111827;">Recent Check-Ins / Outs</div>
                    </div>
                    <a href="<?php echo $base . '/attendance/log'; ?>" style="font-size:12px; color:#16a34a; text-decoration:none;">View all →</a>
                </div>
                <div style="padding:0;">
                    <?php if (empty($recent_logs)): ?>
                    <div style="padding:32px; text-align:center; color:#9ca3af; font-size:13px;">No check-ins logged today.</div>
                    <?php else: foreach ($recent_logs as $log):
                    $is_in = $log->log_type === 'IN';
                    ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid #f9fafb;">
                        <div style="width:36px; height:36px; border-radius:50%; background:<?php echo $is_in ? '#f0fdf4' : '#fef2f2'; ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:<?php echo $is_in ? '#16a34a' : '#ef4444'; ?>;">
                                <?php echo $is_in ? 'login' : 'logout'; ?>
                            </span>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:600; color:#111827;">
                                <?php echo htmlspecialchars($log->first_name . ' ' . $log->last_name); ?>
                            </div>
                            <div style="font-size:12px; color:#6b7280;"><?php echo $log->method; ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:12px; font-weight:600; color:<?php echo $is_in ? '#16a34a' : '#ef4444'; ?>; background:<?php echo $is_in ? '#f0fdf4' : '#fef2f2'; ?>; padding:3px 8px; border-radius:20px;">
                                <?php echo $log->log_type; ?>
                            </span>
                            <div style="font-size:11px; color:#9ca3af; margin-top:3px;"><?php echo date('H:i', strtotime($log->log_datetime)); ?></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </div><!-- /col-md-9 -->

        <!-- Sidebar col-md-3 -->
        <div class="col-md-3">

            <!-- Quick Actions -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $quick = [
                        ['label'=>'Shift Roster',        'icon'=>'calendar_month', 'url'=>$base.'/attendance/roster'],
                        ['label'=>'Mark Attendance',     'icon'=>'fact_check',     'url'=>$base.'/attendance/bulk_tool'],
                        ['label'=>'Upload Timesheet',    'icon'=>'upload_file',    'url'=>$base.'/attendance/excel_upload'],
                        ['label'=>'New OT Slip',         'icon'=>'more_time',      'url'=>$base.'/attendance/overtime/add'],
                        ['label'=>'Monthly Sheet',       'icon'=>'table_chart',    'url'=>$base.'/attendance/monthly_sheet'],
                    ];
                    foreach ($quick as $q):
                    ?>
                    <a href="<?php echo $q['url']; ?>" style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; font-size:13px; color:#374151;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;"><?php echo $q['icon']; ?></span>
                        <?php echo $q['label']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Today at a glance -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Today at a Glance</span>
                </div>
                <div style="padding:16px;">
                    <?php
                    $total_today = $stat_present + $stat_late + $stat_absent + $stat_on_leave;
                    $pct_present = $total_today > 0 ? round(($stat_present + $stat_late) / $total_today * 100) : 0;
                    ?>
                    <div style="margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; font-size:12px; color:#374151; margin-bottom:5px;">
                            <span>Attendance Rate</span><strong><?php echo $pct_present; ?>%</strong>
                        </div>
                        <div style="background:#f3f4f6; border-radius:4px; height:8px;">
                            <div style="background:#16a34a; width:<?php echo $pct_present; ?>%; height:8px; border-radius:4px;"></div>
                        </div>
                    </div>
                    <?php
                    $bars = [
                        ['label'=>'Present', 'val'=>$stat_present,  'color'=>'#16a34a'],
                        ['label'=>'Late',    'val'=>$stat_late,     'color'=>'#ca8a04'],
                        ['label'=>'Absent',  'val'=>$stat_absent,   'color'=>'#ef4444'],
                        ['label'=>'On Leave','val'=>$stat_on_leave, 'color'=>'#2563eb'],
                    ];
                    foreach ($bars as $b):
                    ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:5px 0; font-size:12px; border-bottom:1px solid #f9fafb;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:<?php echo $b['color']; ?>; display:inline-block;"></span>
                            <span style="color:#6b7280;"><?php echo $b['label']; ?></span>
                        </div>
                        <span style="font-weight:700; color:#111827;"><?php echo $b['val']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Setup shortcuts -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Setup</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $setup = [
                        ['label'=>'Shift Types',      'icon'=>'schedule',      'url'=>$base.'/attendance/shift_types'],
                        ['label'=>'Shift Schedules',  'icon'=>'event_repeat',  'url'=>$base.'/attendance/shift_schedules'],
                        ['label'=>'Overtime Types',   'icon'=>'timer',         'url'=>$base.'/attendance/overtime_type'],
                        ['label'=>'Settings',         'icon'=>'settings',      'url'=>$base.'/attendance/settings'],
                    ];
                    foreach ($setup as $s):
                    ?>
                    <a href="<?php echo $s['url']; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $s['icon']; ?></span>
                        <?php echo $s['label']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /col-md-3 -->
    </div>
</div>

</div></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var trend = <?php echo json_encode($trend); ?>;
    var labels  = trend.map(function(d){ return d.date.slice(5); });
    var present = trend.map(function(d){ return d.present; });
    var absent  = trend.map(function(d){ return d.absent; });
    new Chart(document.getElementById('trend-chart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Present', data: present, backgroundColor: '#16a34a', borderRadius: 3, borderSkipped: false },
                { label: 'Absent',  data: absent,  backgroundColor: '#ef4444', borderRadius: 3, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                y: { grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
})();
</script>

<?php init_tail(); ?>
