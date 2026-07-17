<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'attendance';
$base        = admin_url('xetuu_hr');
$prev_week   = date('Y-m-d', strtotime($week_start . ' -7 days'));
$next_week   = date('Y-m-d', strtotime($week_start . ' +7 days'));
$day_names   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$status_colors = ['Present'=>'#16a34a','Late'=>'#ca8a04','Absent'=>'#ef4444','Scheduled'=>'#2563eb','Swapped'=>'#9333ea','Cancelled'=>'#9ca3af'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Xetuu HR</a> /
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Shift Roster</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Shift Roster</h1>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <a href="<?php echo $base.'/attendance/roster/'.$prev_week; ?>" class="btn btn-default" style="border-radius:6px; padding:7px 12px;">
                <span class="material-symbols-outlined" style="font-size:18px; vertical-align:-4px;">chevron_left</span>
            </a>
            <span style="font-size:14px; font-weight:600; color:#374151; min-width:200px; text-align:center;">
                <?php echo date('M j', strtotime($week_dates[0])); ?> – <?php echo date('M j, Y', strtotime($week_dates[6])); ?>
            </span>
            <a href="<?php echo $base.'/attendance/roster/'.$next_week; ?>" class="btn btn-default" style="border-radius:6px; padding:7px 12px;">
                <span class="material-symbols-outlined" style="font-size:18px; vertical-align:-4px;">chevron_right</span>
            </a>
            <a href="<?php echo $base.'/attendance/roster/'.date('Y-m-d', strtotime('monday this week')); ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;">Today</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- Roster Grid -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px; overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:700px;">
                    <thead>
                        <tr style="background:#f8faf8; border-bottom:2px solid #e5e7eb;">
                            <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em; min-width:160px;">Employee</th>
                            <?php foreach ($week_dates as $wd):
                                $is_today   = $wd === date('Y-m-d');
                                $is_weekend = in_array(date('w', strtotime($wd)), [0, 6]);
                            ?>
                            <th style="padding:12px 8px; text-align:center; font-size:12px; font-weight:700; color:<?php echo $is_today ? '#16a34a' : ($is_weekend ? '#9ca3af' : '#374151'); ?>; text-transform:uppercase; letter-spacing:0.04em; background:<?php echo $is_today ? '#f0fdf4' : 'transparent'; ?>; border-left:1px solid #f3f4f6; min-width:90px;">
                                <div><?php echo $day_names[date('w', strtotime($wd))]; ?></div>
                                <div style="font-size:14px; font-weight:800; margin-top:2px;"><?php echo date('j', strtotime($wd)); ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employees)): ?>
                        <tr><td colspan="8" style="padding:32px; text-align:center; color:#9ca3af; font-size:13px;">No employees found. Add employees first.</td></tr>
                        <?php else: foreach ($employees as $emp):
                            $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                        ?>
                        <tr style="border-bottom:1px solid #f3f4f6;" class="roster-row">
                            <td style="padding:10px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:#e8f5e9; color:#16a34a; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><?php echo $initials; ?></div>
                                    <div>
                                        <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($emp->first_name . ' ' . $emp->last_name); ?></div>
                                        <div style="font-size:11px; color:#9ca3af;"><?php echo htmlspecialchars($emp->employee_number ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            <?php foreach ($week_dates as $wd):
                                $slot     = $roster_map[$emp->id][$wd] ?? null;
                                $is_today = $wd === date('Y-m-d');
                                $is_wknd  = in_array(date('w', strtotime($wd)), [0, 6]);
                            ?>
                            <td style="padding:6px 4px; text-align:center; background:<?php echo $is_today ? '#f0fdf4' : ($is_wknd ? '#fafafa' : 'transparent'); ?>; border-left:1px solid #f3f4f6; vertical-align:middle;"
                                class="roster-cell" data-employee="<?php echo $emp->id; ?>" data-date="<?php echo $wd; ?>">
                                <?php if ($slot): ?>
                                <div class="roster-badge" style="background:<?php echo $slot->color ?: '#2563eb'; ?>20; border:1px solid <?php echo $slot->color ?: '#2563eb'; ?>40; border-radius:6px; padding:4px 6px; cursor:pointer; position:relative;"
                                     onclick="openSlot(<?php echo $emp->id; ?>, '<?php echo $wd; ?>', <?php echo $slot->shift_type_id; ?>)">
                                    <div style="font-size:11px; font-weight:700; color:<?php echo $slot->color ?: '#2563eb'; ?>;"><?php echo htmlspecialchars($slot->shift_name); ?></div>
                                    <div style="font-size:10px; color:#6b7280;"><?php echo date('H:i', strtotime($slot->start_time)); ?>–<?php echo date('H:i', strtotime($slot->end_time)); ?></div>
                                    <button onclick="event.stopPropagation(); removeSlot(<?php echo $emp->id; ?>, '<?php echo $wd; ?>', this)" style="position:absolute; top:2px; right:2px; background:none; border:none; cursor:pointer; color:#9ca3af; font-size:14px; line-height:1; padding:0;">×</button>
                                </div>
                                <?php else: ?>
                                <button onclick="openSlot(<?php echo $emp->id; ?>, '<?php echo $wd; ?>', 0)"
                                        style="width:100%; min-height:44px; background:<?php echo $is_wknd ? '#f5f5f5' : '#f9fafb'; ?>; border:1px dashed #e5e7eb; border-radius:6px; cursor:pointer; color:#d1d5db; font-size:18px; transition:.15s;"
                                        title="Assign shift">+</button>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <!-- Shift Legend -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shift Types</span>
                </div>
                <div style="padding:12px 16px;">
                    <?php if (empty($shift_types)): ?>
                    <p style="font-size:12px; color:#9ca3af;">No shift types defined. <a href="<?php echo $base.'/attendance/shift_types'; ?>">Add one →</a></p>
                    <?php else: foreach ($shift_types as $st): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f9fafb;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="width:10px; height:10px; border-radius:3px; background:<?php echo $st->color ?: '#2563eb'; ?>; display:inline-block;"></span>
                            <span style="font-size:12px; font-weight:600; color:#374151;"><?php echo htmlspecialchars($st->name); ?></span>
                        </div>
                        <span style="font-size:11px; color:#9ca3af;"><?php echo date('H:i', strtotime($st->start_time)); ?>–<?php echo date('H:i', strtotime($st->end_time)); ?></span>
                    </div>
                    <?php endforeach; endif; ?>
                    <div style="margin-top:10px;">
                        <a href="<?php echo $base.'/attendance/shift_types'; ?>" style="font-size:12px; color:#16a34a; text-decoration:none;">+ Manage Shift Types</a>
                    </div>
                </div>
            </div>

            <!-- Week Summary -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">This Week</span>
                </div>
                <div style="padding:12px 16px;">
                    <?php
                    $total_slots = 0;
                    foreach ($roster_map as $emp_slots) $total_slots += count($emp_slots);
                    $total_possible = count($employees) * 5;
                    $coverage = $total_possible > 0 ? round($total_slots / $total_possible * 100) : 0;
                    ?>
                    <div style="text-align:center; margin-bottom:12px;">
                        <div style="font-size:28px; font-weight:800; color:#16a34a;"><?php echo $total_slots; ?></div>
                        <div style="font-size:12px; color:#6b7280;">Shifts Assigned</div>
                    </div>
                    <div style="font-size:12px; color:#374151; margin-bottom:5px; display:flex; justify-content:space-between;">
                        <span>Coverage</span><strong><?php echo $coverage; ?>%</strong>
                    </div>
                    <div style="background:#f3f4f6; border-radius:4px; height:8px;">
                        <div style="background:#16a34a; width:<?php echo min(100,$coverage); ?>%; height:8px; border-radius:4px;"></div>
                    </div>
                </div>
            </div>

            <!-- Shortcuts -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Attendance</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $links = [
                        ['label'=>'Dashboard',        'icon'=>'dashboard',      'url'=>$base.'/attendance'],
                        ['label'=>'Daily Attendance', 'icon'=>'today',          'url'=>$base.'/attendance/daily'],
                        ['label'=>'Bulk Tool',        'icon'=>'fact_check',     'url'=>$base.'/attendance/bulk_tool'],
                        ['label'=>'Shift Requests',   'icon'=>'swap_horiz',     'url'=>$base.'/attendance/shift_request'],
                    ];
                    foreach ($links as $lk):
                    ?>
                    <a href="<?php echo $lk['url']; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $lk['icon']; ?></span>
                        <?php echo $lk['label']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Shift Modal -->
<div id="assign-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:360px; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;">
        <div style="padding:20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; font-weight:700; color:#111827;">Assign Shift</h3>
            <button onclick="closeModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px;">×</button>
        </div>
        <div style="padding:20px;">
            <div style="font-size:13px; color:#6b7280; margin-bottom:16px;" id="modal-subtitle"></div>
            <select id="modal-shift-select" class="form-control" style="margin-bottom:16px;">
                <option value="">— Day Off —</option>
                <?php foreach ($shift_types as $st): ?>
                <option value="<?php echo $st->id; ?>" data-color="<?php echo $st->color; ?>">
                    <?php echo htmlspecialchars($st->name); ?> (<?php echo date('H:i', strtotime($st->start_time)); ?>–<?php echo date('H:i', strtotime($st->end_time)); ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <div style="display:flex; gap:8px;">
                <button onclick="saveSlot()" class="btn btn-success" style="flex:1; background:#16a34a; border-color:#16a34a; border-radius:6px;">Assign</button>
                <button onclick="closeModal()" class="btn btn-default" style="border-radius:6px;">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
var modalEmpId = null, modalDate = null;
var baseUrl = '<?php echo $base; ?>/attendance/roster';

function openSlot(empId, date, currentShiftId) {
    modalEmpId = empId;
    modalDate  = date;
    document.getElementById('modal-subtitle').textContent = 'Date: ' + date;
    var sel = document.getElementById('modal-shift-select');
    sel.value = currentShiftId || '';
    document.getElementById('assign-modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('assign-modal').style.display = 'none';
    modalEmpId = null; modalDate = null;
}
function saveSlot() {
    var shiftId = document.getElementById('modal-shift-select').value;
    if (!shiftId) { removeSlot(modalEmpId, modalDate, null); closeModal(); return; }
    fetch(baseUrl + '/assign', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({employee_id: modalEmpId, shift_type_id: shiftId, roster_date: modalDate, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'})
    }).then(function(){ location.reload(); });
}
function removeSlot(empId, date, btn) {
    if (!confirm('Remove this shift assignment?')) return;
    fetch(baseUrl + '/remove', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({employee_id: empId, roster_date: date, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'})
    }).then(function(){ location.reload(); });
}
document.getElementById('assign-modal').addEventListener('click', function(e){ if (e.target === this) closeModal(); });
</script>

</div></div>
<?php init_tail(); ?>
