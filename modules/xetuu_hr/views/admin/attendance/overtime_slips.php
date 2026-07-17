<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'attendance';
$base       = admin_url('xetuu_hr');
$is_edit    = !empty($edit_slip);
$sl         = $edit_slip ?: null;
function slv($sl, $k, $d = '') { return $sl && isset($sl->$k) ? htmlspecialchars($sl->$k) : $d; }
$status_colors = ['Draft'=>['#6b7280','#f3f4f6'],'Pending'=>['#ca8a04','#fefce8'],'Approved'=>['#16a34a','#f0fdf4'],'Rejected'=>['#dc2626','#fef2f2'],'Paid'=>['#2563eb','#eff6ff']];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Xetuu HR</a> /
                <a href="<?php echo $base.'/attendance'; ?>" style="color:#6b7280; text-decoration:none;">Shift &amp; Attendance</a> /
                <span style="color:#111827; font-weight:500;">Overtime</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Overtime Slips</h1>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if ($is_edit): ?>
            <a href="<?php echo $base.'/attendance/overtime'; ?>" class="btn btn-default" style="border-radius:6px; font-size:13px;">← Back to List</a>
            <?php else: ?>
            <a href="<?php echo $base.'/attendance/overtime/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> New OT Slip
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (list view only) -->
    <?php if (!$is_edit): ?>
    <div class="row" style="margin-bottom:20px;">
        <?php
        $ot_cards = [
            ['label'=>'Total Slips',      'val'=>$stat_total,    'icon'=>'receipt_long', 'color'=>'#374151','bg'=>'#f9fafb'],
            ['label'=>'Pending Approval', 'val'=>$stat_pending,  'icon'=>'pending',      'color'=>'#ca8a04','bg'=>'#fefce8'],
            ['label'=>'Approved',         'val'=>$stat_approved, 'icon'=>'check_circle', 'color'=>'#16a34a','bg'=>'#f0fdf4'],
            ['label'=>'Paid',             'val'=>$stat_paid,     'icon'=>'payments',     'color'=>'#2563eb','bg'=>'#eff6ff'],
            ['label'=>'Total OT Hours',   'val'=>number_format($total_ot_hrs,1).'h', 'icon'=>'more_time','color'=>'#9333ea','bg'=>'#fdf4ff'],
        ];
        foreach ($ot_cards as $c):
        ?>
        <div class="col-md-2" style="width:20%;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center;">
                <div style="width:38px; height:38px; background:<?php echo $c['bg']; ?>; border-radius:8px; display:flex; align-items:center; justify-content:center; margin:0 auto 8px;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:<?php echo $c['color']; ?>;"><?php echo $c['icon']; ?></span>
                </div>
                <div style="font-size:22px; font-weight:800; color:<?php echo $c['color']; ?>;"><?php echo $c['val']; ?></div>
                <div style="font-size:11px; color:#6b7280; margin-top:3px;"><?php echo $c['label']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-9">

        <?php if ($is_edit): ?>
        <!-- FORM -->
        <form action="<?php echo $base.'/attendance/overtime'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <?php if ($sl): ?><input type="hidden" name="slip_id" value="<?php echo $sl->id; ?>"><?php endif; ?>

            <!-- Header card with slip number -->
            <?php if ($sl): ?>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px 20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-size:11px; color:#16a34a; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Overtime Slip</div>
                    <div style="font-size:22px; font-weight:800; color:#15803d;"><?php echo slv($sl,'slip_number','—'); ?></div>
                </div>
                <?php $sc = $status_colors[slv($sl,'status','Draft')] ?? ['#6b7280','#f3f4f6']; ?>
                <span style="font-size:13px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:6px 16px; border-radius:20px;"><?php echo slv($sl,'status'); ?></span>
            </div>
            <?php endif; ?>

            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; background:#fdf4ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#9333ea;">more_time</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:#111827;">Overtime Details</div>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Employee *</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">— Select Employee —</option>
                                    <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo $e->id; ?>" <?php echo slv($sl,'employee_id') == $e->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($e->employee_number . ' — ' . $e->first_name . ' ' . $e->last_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">OT Date *</label>
                                <input type="date" name="overtime_date" class="form-control" required value="<?php echo slv($sl,'overtime_date', date('Y-m-d')); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Branch</label>
                                <select name="branch_id" class="form-control">
                                    <option value="0">— All —</option>
                                    <?php foreach ($branches as $b): ?>
                                    <option value="<?php echo $b->id; ?>" <?php echo slv($sl,'branch_id') == $b->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Overtime Type *</label>
                                <select name="overtime_type_id" class="form-control" required onchange="onOtTypeChange(this)">
                                    <option value="">— Select Type —</option>
                                    <?php foreach ($ot_types as $ot): ?>
                                    <option value="<?php echo $ot->id; ?>"
                                            data-mult="<?php echo $ot->multiplier; ?>"
                                            data-toil="<?php echo $ot->toil_enabled; ?>"
                                            <?php echo slv($sl,'overtime_type_id') == $ot->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ot->name); ?> (×<?php echo $ot->multiplier; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shift</label>
                                <select name="shift_type_id" class="form-control">
                                    <option value="">— None —</option>
                                    <?php foreach ($shift_types as $st): ?>
                                    <option value="<?php echo $st->id; ?>" <?php echo slv($sl,'shift_type_id') == $st->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($st->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Compensation Mode</label>
                                <select name="compensation_mode" class="form-control" onchange="onCompModeChange(this)">
                                    <option value="Pay"  <?php echo slv($sl,'compensation_mode','Pay') === 'Pay'  ? 'selected' : ''; ?>>Pay (Cash)</option>
                                    <option value="TOIL" <?php echo slv($sl,'compensation_mode') === 'TOIL' ? 'selected' : ''; ?>>TOIL (Time Off in Lieu)</option>
                                    <option value="Both" <?php echo slv($sl,'compensation_mode') === 'Both' ? 'selected' : ''; ?>>Both</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Regular Hours</label>
                                <input type="number" name="regular_hours" class="form-control" step="0.25" min="0" value="<?php echo slv($sl,'regular_hours','8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Overtime Hours *</label>
                                <input type="number" name="overtime_hours" id="ot-hours" class="form-control" step="0.25" min="0.25" required value="<?php echo slv($sl,'overtime_hours','0'); ?>" onchange="calcPay()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Rate Multiplier</label>
                                <input type="number" name="rate_multiplier" id="rate-mult" class="form-control" step="0.05" min="1" value="<?php echo slv($sl,'rate_multiplier','1.50'); ?>" onchange="calcPay()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">TOIL Hours Credited</label>
                                <input type="text" id="toil-display" class="form-control" readonly style="background:#f9fafb;" value="<?php echo slv($sl,'toil_hours_credited','0'); ?>">
                            </div>
                        </div>
                    </div>
                    <!-- Pay summary -->
                    <div id="pay-summary" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:14px 16px; margin-top:4px; font-size:13px; color:#15803d; font-weight:600;">
                        Effective OT Rate: <span id="eff-rate">—</span>× &nbsp;|&nbsp; Compensation: <span id="comp-mode-label">Pay</span>
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for overtime..."><?php echo slv($sl,'notes'); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Approve / Reject if pending -->
            <?php if ($sl && $sl->status === 'Pending'): ?>
            <div style="display:flex; gap:8px; margin-bottom:16px;">
                <a href="<?php echo $base.'/attendance/overtime/approve/'.$sl->id; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;" onclick="return confirm('Approve this overtime slip?')">
                    <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">check</span> Approve
                </a>
                <a href="<?php echo $base.'/attendance/overtime/reject/'.$sl->id; ?>" class="btn btn-danger" style="border-radius:6px;" onclick="return confirm('Reject this overtime slip?')">
                    <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">close</span> Reject
                </a>
            </div>
            <?php endif; ?>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <a href="<?php echo $base.'/attendance/overtime'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600;">
                    <i class="fa fa-save"></i> Save OT Slip
                </button>
            </div>
        </form>

        <?php else: ?>
        <!-- LIST -->
        <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
            <div style="padding:0;">
                <?php if (empty($slips)): ?>
                <div style="padding:48px; text-align:center; color:#9ca3af;">
                    <span class="material-symbols-outlined" style="font-size:48px; display:block; margin-bottom:10px;">more_time</span>
                    No overtime slips yet. <a href="<?php echo $base.'/attendance/overtime/add'; ?>" style="color:#16a34a;">Create the first slip →</a>
                </div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Slip #</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Employee</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">OT Date</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Type</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">OT Hrs</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Rate</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Mode</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;"></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($slips as $s):
                        $sc = $status_colors[$s->status] ?? ['#6b7280','#f3f4f6'];
                    ?>
                    <tr>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#374151;"><?php echo htmlspecialchars($s->slip_number ?? '—'); ?></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></td>
                        <td style="font-size:13px;"><?php echo date('M j, Y', strtotime($s->overtime_date)); ?></td>
                        <td style="font-size:12px;"><?php echo htmlspecialchars($s->ot_type_name ?? '—'); ?></td>
                        <td style="font-size:13px; font-weight:700; color:#9333ea;"><?php echo number_format($s->overtime_hours, 2); ?>h</td>
                        <td style="font-size:13px;">×<?php echo $s->rate_multiplier; ?></td>
                        <td style="font-size:12px;"><?php echo htmlspecialchars($s->compensation_mode); ?></td>
                        <td>
                            <span style="font-size:11px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:3px 10px; border-radius:20px;"><?php echo $s->status; ?></span>
                        </td>
                        <td style="text-align:right; padding-right:16px;">
                            <a href="<?php echo $base.'/attendance/overtime/edit/'.$s->id; ?>" class="btn btn-xs btn-default">Edit</a>
                            <?php if ($s->status === 'Pending'): ?>
                            <a href="<?php echo $base.'/attendance/overtime/approve/'.$s->id; ?>" class="btn btn-xs btn-success" onclick="return confirm('Approve?')">✓</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        </div><!-- /col-md-9 -->

        <!-- Sidebar -->
        <div class="col-md-3">
            <?php if ($is_edit && $sl): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Slip Summary</span>
                </div>
                <div style="padding:16px;">
                    <?php $sc = $status_colors[$sl->status] ?? ['#6b7280','#f3f4f6']; ?>
                    <div style="text-align:center; margin-bottom:12px;">
                        <span style="font-size:13px; font-weight:700; color:<?php echo $sc[0]; ?>; background:<?php echo $sc[1]; ?>; padding:6px 16px; border-radius:20px;"><?php echo $sl->status; ?></span>
                    </div>
                    <?php $rows = [['OT Hours', number_format($sl->overtime_hours,2).'h'],['Multiplier','×'.number_format($sl->rate_multiplier,2)],['Mode',$sl->compensation_mode],['Created',date('M j, Y',strtotime($sl->date_created))]]; foreach ($rows as $r): ?>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f9fafb; font-size:12px;">
                        <span style="color:#6b7280;"><?php echo $r[0]; ?></span>
                        <span style="font-weight:600; color:#111827;"><?php echo $r[1]; ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($sl->status === 'Draft'): ?>
                    <div style="margin-top:12px;">
                        <a href="<?php echo $base.'/attendance/overtime/delete/'.$sl->id; ?>" class="btn btn-danger btn-block" style="border-radius:6px;" onclick="return confirm('Delete this slip?')">Delete</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">OT Status</span>
                </div>
                <div style="padding:16px;">
                    <?php foreach ([['Draft','#6b7280','#f3f4f6',0],['Pending','#ca8a04','#fefce8',$stat_pending],['Approved','#16a34a','#f0fdf4',$stat_approved],['Paid','#2563eb','#eff6ff',$stat_paid]] as $r): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f9fafb;">
                        <span style="font-size:12px; font-weight:700; color:<?php echo $r[1]; ?>; background:<?php echo $r[2]; ?>; padding:2px 10px; border-radius:20px;"><?php echo $r[0]; ?></span>
                        <span style="font-size:14px; font-weight:800; color:#111827;"><?php echo $r[3]; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Shortcuts</span>
                </div>
                <div style="padding:8px 0;">
                    <?php foreach ([['Dashboard','dashboard','attendance'],['Shift Roster','calendar_month','attendance/roster'],['Overtime Types','timer','attendance/overtime_type'],['Monthly Sheet','table_chart','attendance/monthly_sheet']] as $lk): ?>
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
var otTypes = <?php echo json_encode(array_column($ot_types, null, 'id')); ?>;
function onOtTypeChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.querySelector('[name=rate_multiplier]').value = opt.dataset.mult;
        document.getElementById('eff-rate').textContent = opt.dataset.mult;
    }
    calcPay();
}
function onCompModeChange(sel) {
    document.getElementById('comp-mode-label').textContent = sel.value;
}
function calcPay() {
    var hrs  = parseFloat(document.getElementById('ot-hours').value) || 0;
    var mult = parseFloat(document.querySelector('[name=rate_multiplier]').value) || 1;
    document.getElementById('eff-rate').textContent = mult.toFixed(2);
    document.getElementById('toil-display').value = (hrs * mult).toFixed(2);
}
</script>
<?php init_tail(); ?>
