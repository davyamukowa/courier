<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');

$total     = (int)($stat_total ?? 0);
$scheduled = (int)($stat_scheduled ?? 0);
$completed = (int)($stat_completed ?? 0);
$cancelled = (int)($stat_cancelled ?? 0);
$passed    = (int)($stat_passed ?? 0);
$failed    = (int)($stat_failed ?? 0);
$pass_rate = $completed > 0 ? round(($passed / $completed) * 100) : 0;

// Build calendar events JSON from interviews
$cal_events = [];
foreach ($interviews as $i) {
    if (empty($i->interview_date)) continue;

    $status_color = [
        'Scheduled' => ['bg' => '#f59e0b', 'border' => '#d97706'],
        'Completed' => ['bg' => '#16a34a', 'border' => '#15803d'],
        'Cancelled' => ['bg' => '#ef4444', 'border' => '#dc2626'],
    ][$i->status] ?? ['bg' => '#6b7280', 'border' => '#4b5563'];

    $start = $i->interview_date;
    if (!empty($i->from_time)) { $start .= 'T' . substr($i->from_time, 0, 5); }
    $end = null;
    if (!empty($i->to_time))   { $end   = $i->interview_date . 'T' . substr($i->to_time, 0, 5); }

    $title = ($i->applicant_name ?? 'Applicant');
    if (!empty($i->interview_round_name)) { $title .= ' · ' . $i->interview_round_name; }

    $cal_events[] = [
        'id'              => $i->id,
        'title'           => $title,
        'start'           => $start,
        'end'             => $end,
        'backgroundColor' => $status_color['bg'],
        'borderColor'     => $status_color['border'],
        'textColor'       => '#ffffff',
        'url'             => admin_url('xetuu_hr/recruitment/interviews/edit/' . $i->id),
        'extendedProps'   => [
            'status'    => $i->status,
            'result'    => $i->result,
            'round'     => $i->interview_round_name ?? '',
            'applicant' => $i->applicant_name ?? '',
            'num'       => $i->interview_number ?? ('INT-' . $i->id),
        ],
    ];
}
$cal_events_json = json_encode($cal_events, JSON_HEX_TAG | JSON_HEX_AMP);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- FullCalendar 6 -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<style>
/* FullCalendar overrides */
#int-calendar .fc-toolbar-title { font-size:16px; font-weight:700; color:#111827; }
#int-calendar .fc-button-primary { background:#16a34a !important; border-color:#15803d !important; font-size:12px !important; }
#int-calendar .fc-button-primary:hover { background:#15803d !important; }
#int-calendar .fc-button-primary:not(:disabled).fc-button-active { background:#14532d !important; }
#int-calendar .fc-daygrid-day-number { color:#374151; font-size:12px; }
#int-calendar .fc-event { cursor:pointer; border-radius:4px !important; font-size:11px !important; padding:1px 4px !important; }
#int-calendar .fc-col-header-cell { background:#f9fafb; }
#int-calendar .fc-col-header-cell-cushion { font-size:11px; font-weight:600; color:#6b7280; text-decoration:none; }
#int-calendar .fc-daygrid-day.fc-day-today { background:#f0fdf4 !important; }

/* Tooltip */
#cal-tooltip {
    position:fixed; z-index:9999; background:#1f2937; color:#fff; border-radius:8px;
    padding:10px 14px; font-size:12px; pointer-events:none; max-width:220px;
    box-shadow:0 10px 25px rgba(0,0,0,0.3); display:none; line-height:1.6;
}

/* Clickable elements and row/card hover states */
.stat-card {
    border: 2px solid transparent !important;
    transition: all 0.2s ease-in-out;
    opacity: 0.8;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.stat-card.active {
    border-color: currentColor !important;
    opacity: 1 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.interview-row {
    transition: background 0.15s ease-in-out;
}
.interview-row:hover {
    background-color: #f8fafc !important;
}
</style>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">Interviews</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Interviews</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Plan and track candidate evaluation sessions.</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <!-- View toggle -->
            <div style="display:inline-flex; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden;">
                <button id="btn-list-view" onclick="switchView('list')"
                    style="padding:7px 14px; font-size:12px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px; background:#16a34a; color:#fff;">
                    <span class="material-symbols-outlined" style="font-size:15px;">list</span> List View
                </button>
                <button id="btn-cal-view" onclick="switchView('calendar')"
                    style="padding:7px 14px; font-size:12px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px; background:#fff; color:#374151;">
                    <span class="material-symbols-outlined" style="font-size:15px;">calendar_month</span> Calendar View
                </button>
            </div>
            <a href="<?php echo $base . '/recruitment/interviews/add'; ?>" class="btn btn-success"
               style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa fa-plus"></i> Schedule Interview
            </a>
        </div>
    </div>

    <div class="row">

        <!-- ── Main col-md-9 ──────────────────────────────────────────────── -->
        <div class="col-md-9">

            <!-- Stat cards (always visible) -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
                <?php
                $cards = [
                    ['label'=>'Total',      'value'=>$total,         'icon'=>'event_note',      'bg'=>'#eff6ff','color'=>'#1d4ed8', 'slug'=>'total'],
                    ['label'=>'Scheduled',  'value'=>$scheduled,     'icon'=>'pending_actions', 'bg'=>'#fef9c3','color'=>'#854d0e', 'slug'=>'scheduled'],
                    ['label'=>'Completed',  'value'=>$completed,     'icon'=>'task_alt',        'bg'=>'#dcfce7','color'=>'#16a34a', 'slug'=>'completed'],
                    ['label'=>'Pass Rate',  'value'=>$pass_rate.'%', 'icon'=>'trending_up',     'bg'=>'#f0fdf4','color'=>'#15803d', 'slug'=>'pass-rate'],
                ];
                foreach ($cards as $c):
                ?>
                <div class="stat-card" data-filter="<?php echo $c['slug']; ?>" onclick="filterInterviews('<?php echo $c['slug']; ?>')"
                     style="background:<?php echo $c['bg']; ?>; color:<?php echo $c['color']; ?>; border-radius:8px; padding:14px 16px; display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <span class="material-symbols-outlined" style="font-size:26px; color:<?php echo $c['color']; ?>;"><?php echo $c['icon']; ?></span>
                    <div>
                        <div style="font-size:20px; font-weight:700; color:<?php echo $c['color']; ?>; line-height:1.1;"><?php echo $c['value']; ?></div>
                        <div style="font-size:11px; color:<?php echo $c['color']; ?>; opacity:0.8; margin-top:2px;"><?php echo $c['label']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ══ LIST VIEW ════════════════════════════════════════════════ -->
            <div id="view-list">
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                    <div class="panel-body" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table" style="margin-bottom:0;">
                                <thead>
                                    <tr style="background-color:#f9fafb;">
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">#</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applicant</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Round / Type</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Scheduled</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Interviewer</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Result</th>
                                        <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($interviews)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                            No interviews yet. <a href="<?php echo $base . '/recruitment/interviews/add'; ?>" style="color:#16a34a;">Schedule one</a>.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($interviews as $i):
                                        $sc = [
                                            'Scheduled' => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                                            'Completed' => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                                            'Cancelled' => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                                        ][$i->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                                        $rc = [
                                            'Pass'    => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                                            'Fail'    => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                                            'Pending' => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
                                        ][$i->result] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];
                                    ?>
                                    <tr class="interview-row" data-status="<?php echo htmlspecialchars($i->status); ?>" data-result="<?php echo htmlspecialchars($i->result); ?>" data-edit-url="<?php echo $base . '/recruitment/interviews/edit/' . $i->id; ?>" style="cursor:pointer;">
                                        <td style="padding:12px 16px; font-size:11px; color:#6b7280; font-family:monospace; border-bottom:1px solid #f3f4f6;">
                                            <a href="<?php echo $base . '/recruitment/interviews/edit/' . $i->id; ?>" style="color:#16a34a; text-decoration:none;">
                                                <?php echo $i->interview_number ? htmlspecialchars($i->interview_number) : 'INT-'.$i->id; ?>
                                            </a>
                                        </td>
                                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                            <?php echo htmlspecialchars($i->applicant_name ?? '—'); ?>
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                            <div style="font-weight:500;"><?php echo $i->interview_round_name ? htmlspecialchars($i->interview_round_name) : '<span style="color:#9ca3af;">—</span>'; ?></div>
                                            <?php if ($i->interview_type_name): ?><div style="color:#9ca3af; font-size:11px;"><?php echo htmlspecialchars($i->interview_type_name); ?></div><?php endif; ?>
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                            <?php if ($i->interview_date): ?>
                                            <div><?php echo _d($i->interview_date); ?></div>
                                            <?php if ($i->from_time): ?><div style="color:#6b7280;"><?php echo date('H:i', strtotime($i->from_time)); ?><?php echo $i->to_time ? ' – '.date('H:i', strtotime($i->to_time)) : ''; ?></div><?php endif; ?>
                                            <?php else: ?><span style="color:#9ca3af;">—</span><?php endif; ?>
                                        </td>
                                        <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                            <?php echo $i->interviewer_name ? htmlspecialchars($i->interviewer_name) : '<span style="color:#9ca3af;">—</span>'; ?>
                                        </td>
                                        <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                            <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                                                <?php echo htmlspecialchars($i->status); ?>
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                            <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $rc['bg']; ?>; color:<?php echo $rc['color']; ?>;">
                                                <?php echo htmlspecialchars($i->result); ?>
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                            <a href="<?php echo $base . '/recruitment/interviews/edit/' . $i->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="<?php echo $base . '/recruitment/interviews/delete/' . $i->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /#view-list -->

            <!-- ══ CALENDAR VIEW ════════════════════════════════════════════ -->
            <div id="view-calendar" style="display:none;">
                <!-- Legend -->
                <div style="display:flex; gap:16px; margin-bottom:12px; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#374151;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#f59e0b; display:inline-block;"></span> Scheduled
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#374151;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#16a34a; display:inline-block;"></span> Completed
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#374151;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#ef4444; display:inline-block;"></span> Cancelled
                    </span>
                </div>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                    <div style="padding:20px;">
                        <div id="int-calendar"></div>
                    </div>
                </div>
            </div><!-- /#view-calendar -->

        </div><!-- /col-md-9 -->

        <!-- ── Sidebar col-md-3 ──────────────────────────────────────────── -->
        <div class="col-md-3">

            <!-- Status breakdown -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Status Breakdown</span>
                </div>
                <div style="padding:14px 16px;">
                    <?php
                    $sbars = [
                        ['label'=>'Scheduled','value'=>$scheduled,'color'=>'#854d0e','bg'=>'#fef9c3'],
                        ['label'=>'Completed','value'=>$completed,'color'=>'#16a34a','bg'=>'#dcfce7'],
                        ['label'=>'Cancelled','value'=>$cancelled,'color'=>'#dc2626','bg'=>'#fee2e2'],
                    ];
                    foreach ($sbars as $sb):
                        $pct = $total > 0 ? round(($sb['value'] / $total) * 100) : 0;
                    ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span style="font-size:12px; color:#374151; font-weight:500;"><?php echo $sb['label']; ?></span>
                            <span style="font-size:12px; font-weight:700; color:<?php echo $sb['color']; ?>;"><?php echo $sb['value']; ?></span>
                        </div>
                        <div style="height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                            <div style="height:100%; width:<?php echo $pct; ?>%; background:<?php echo $sb['color']; ?>; border-radius:3px;"></div>
                        </div>
                        <div style="font-size:10px; color:#9ca3af; margin-top:2px;"><?php echo $pct; ?>% of total</div>
                    </div>
                    <?php endforeach; ?>
                    <div style="border-top:1px solid #f3f4f6; padding-top:12px; margin-top:4px;">
                        <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.04em;">Results</div>
                        <div style="display:flex; gap:6px;">
                            <div style="flex:1; background:#f0fdf4; border-radius:6px; padding:8px; text-align:center;">
                                <div style="font-size:16px; font-weight:700; color:#16a34a;"><?php echo $passed; ?></div>
                                <div style="font-size:10px; color:#15803d;">Pass</div>
                            </div>
                            <div style="flex:1; background:#fef2f2; border-radius:6px; padding:8px; text-align:center;">
                                <div style="font-size:16px; font-weight:700; color:#dc2626;"><?php echo $failed; ?></div>
                                <div style="font-size:10px; color:#b91c1c;">Fail</div>
                            </div>
                            <div style="flex:1; background:#f9fafb; border-radius:6px; padding:8px; text-align:center;">
                                <div style="font-size:16px; font-weight:700; color:#374151;"><?php echo $pass_rate; ?>%</div>
                                <div style="font-size:10px; color:#6b7280;">Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming -->
            <?php if (!empty($stat_upcoming)): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Upcoming</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ($stat_upcoming as $u): ?>
                    <div style="padding:8px 16px; border-bottom:1px solid #f9fafb;">
                        <div style="font-size:12px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($u->applicant_name ?? '—'); ?></div>
                        <div style="font-size:11px; color:#6b7280; margin-top:2px; display:flex; justify-content:space-between;">
                            <span><?php echo $u->round_name ? htmlspecialchars($u->round_name) : 'No round'; ?></span>
                            <span><?php echo $u->interview_date ? date('d M', strtotime($u->interview_date)) : '—'; ?><?php echo $u->from_time ? ' · '.date('H:i', strtotime($u->from_time)) : ''; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- By Round -->
            <?php if (!empty($stat_by_round)): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">By Round</span>
                </div>
                <div style="padding:10px 16px;">
                    <?php foreach ($stat_by_round as $rb):
                        $pct = $total > 0 ? round(($rb->total / $total) * 100) : 0;
                    ?>
                    <div style="display:flex; align-items:center; gap:8px; padding:5px 0; border-bottom:1px solid #f9fafb;">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; font-weight:500; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($rb->round_name ?? 'Unassigned'); ?></div>
                            <div style="height:4px; background:#f3f4f6; border-radius:2px; margin-top:3px;"><div style="height:100%; width:<?php echo $pct; ?>%; background:#16a34a; border-radius:2px;"></div></div>
                        </div>
                        <span style="font-size:12px; font-weight:700; color:#16a34a; flex-shrink:0;"><?php echo $rb->total; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recruitment shortcuts -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recruitment</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $rec_links = [
                        ['label'=>'Job Requisition',    'icon'=>'article',          'url'=>$base.'/setup/job_requisition'],
                        ['label'=>'Job Opening',        'icon'=>'work_outline',     'url'=>$base.'/recruitment/job_openings'],
                        ['label'=>'Job Applicant',      'icon'=>'person_search',    'url'=>$base.'/recruitment/applicants'],
                        ['label'=>'Interview',          'icon'=>'record_voice_over','url'=>$base.'/recruitment/interviews'],
                        ['label'=>'Job Offer',          'icon'=>'handshake',        'url'=>$base.'/recruitment/offers'],
                        ['label'=>'Appointment Letter', 'icon'=>'mail',             'url'=>$base.'/recruitment/appointment_letters'],
                    ];
                    foreach ($rec_links as $rl):
                        $active = ($rl['label'] === 'Interview');
                    ?>
                    <a href="<?php echo $rl['url']; ?>"
                       style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px;
                              <?php echo $active ? 'color:#16a34a; font-weight:600; background:#f0fdf4;' : 'color:#4b5563;'; ?>">
                        <span class="material-symbols-outlined" style="font-size:16px; <?php echo $active ? 'color:#16a34a;' : 'color:#9ca3af;'; ?>"><?php echo $rl['icon']; ?></span>
                        <?php echo $rl['label']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /col-md-3 -->
    </div><!-- /.row -->
</div>

<!-- Hover tooltip -->
<div id="cal-tooltip"></div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script>
var INT_EVENTS = <?php echo $cal_events_json; ?>;
var calInstance = null;

// ── View toggle ──────────────────────────────────────────────────────────────
function switchView(v) {
    var btnList = document.getElementById('btn-list-view');
    var btnCal  = document.getElementById('btn-cal-view');
    var listEl  = document.getElementById('view-list');
    var calEl   = document.getElementById('view-calendar');

    if (v === 'list') {
        listEl.style.display = 'block';
        calEl.style.display  = 'none';
        btnList.style.background = '#16a34a'; btnList.style.color = '#fff';
        btnCal.style.background  = '#fff';    btnCal.style.color  = '#374151';
        localStorage.setItem('hr_interview_view', 'list');
    } else {
        listEl.style.display = 'none';
        calEl.style.display  = 'block';
        btnList.style.background = '#fff';    btnList.style.color = '#374151';
        btnCal.style.background  = '#16a34a'; btnCal.style.color  = '#fff';
        localStorage.setItem('hr_interview_view', 'calendar');
        if (!calInstance) { initCalendar(); }
        else              { calInstance.render(); }
    }
}

// ── FullCalendar init ────────────────────────────────────────────────────────
function initCalendar() {
    var el = document.getElementById('int-calendar');
    var tooltip = document.getElementById('cal-tooltip');

    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library could not be loaded.');
        alert('Calendar view is unavailable because FullCalendar library failed to load (offline or CDN blocked). Falling back to List View.');
        switchView('list');
        return;
    }

    calInstance = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        height: 680,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today:    'Today',
            month:    'Month',
            week:     'Week',
            list:     'Agenda'
        },
        events: INT_EVENTS,
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventDisplay: 'block',

        // Click → go to edit form
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) { window.location.href = info.event.url; }
        },

        // Hover tooltip
        eventMouseEnter: function(info) {
            var p = info.event.extendedProps;
            var statusColor = {
                'Scheduled': '#f59e0b',
                'Completed': '#16a34a',
                'Cancelled': '#ef4444'
            }[p.status] || '#6b7280';

            tooltip.innerHTML =
                '<div style="font-weight:700; margin-bottom:4px; font-size:13px;">' + escHtml(p.num) + '</div>' +
                '<div style="margin-bottom:2px;"><span style="opacity:0.7;">Applicant:</span> ' + escHtml(p.applicant) + '</div>' +
                (p.round ? '<div style="margin-bottom:2px;"><span style="opacity:0.7;">Round:</span> ' + escHtml(p.round) + '</div>' : '') +
                '<div style="margin-top:6px; display:flex; gap:8px; align-items:center;">' +
                '<span style="padding:2px 8px; border-radius:10px; background:' + statusColor + '; color:#fff; font-size:10px; font-weight:700;">' + escHtml(p.status) + '</span>' +
                '<span style="font-size:11px; opacity:0.8;">' + escHtml(p.result) + '</span>' +
                '</div>';
            tooltip.style.display = 'block';
        },
        eventMouseLeave: function() {
            tooltip.style.display = 'none';
        },
        eventDidMount: function(info) {
            // Add subtle time label inside event
        }
    });

    calInstance.render();

    // Track mouse for tooltip position
    document.addEventListener('mousemove', function(e) {
        tooltip.style.left = (e.clientX + 14) + 'px';
        tooltip.style.top  = (e.clientY - 10) + 'px';
    });
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Restore last used view on page load
document.addEventListener('DOMContentLoaded', function() {
    var saved = localStorage.getItem('hr_interview_view');
    if (saved === 'calendar') { switchView('calendar'); }

    // Clickable rows
    document.querySelectorAll('.interview-row').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.btn')) {
                return;
            }
            var url = row.getAttribute('data-edit-url');
            if (url) { window.location.href = url; }
        });
    });

    // Make 'total' active initially
    var totalCard = document.querySelector('[data-filter="total"]');
    if (totalCard) {
        totalCard.classList.add('active');
    }
});

// Stats filtering logic
function filterInterviews(filterType) {
    // Card styling highlight
    document.querySelectorAll('.stat-card').forEach(function(card) {
        card.classList.remove('active');
    });
    var activeCard = document.querySelector('[data-filter="' + filterType + '"]');
    if (activeCard) {
        activeCard.classList.add('active');
    }

    // Table rows
    document.querySelectorAll('.interview-row').forEach(function(row) {
        var status = row.getAttribute('data-status');
        var result = row.getAttribute('data-result');
        var show = false;

        if (filterType === 'total') {
            show = true;
        } else if (filterType === 'scheduled') {
            show = (status === 'Scheduled');
        } else if (filterType === 'completed') {
            show = (status === 'Completed');
        } else if (filterType === 'pass-rate') {
            show = (result === 'Pass');
        }

        row.style.display = show ? '' : 'none';
    });

    // Calendar events
    if (calInstance) {
        calInstance.removeAllEvents();
        var filteredEvents = INT_EVENTS.filter(function(ev) {
            if (filterType === 'total') return true;
            if (filterType === 'scheduled') return ev.extendedProps.status === 'Scheduled';
            if (filterType === 'completed') return ev.extendedProps.status === 'Completed';
            if (filterType === 'pass-rate') return ev.extendedProps.result === 'Pass';
            return true;
        });
        calInstance.addEventSource(filteredEvents);
    }
}
</script>
