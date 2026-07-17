<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');

// ── Compute analytics from $allocations ───────────────────────────
$stat_total_allocs  = count($allocations);
$stat_total_days    = 0;
$stat_used_days     = 0;
$stat_exhausted     = 0;
$stat_low           = 0;        // < 20% remaining
$covered_emps       = [];
$policy_allocs      = 0;
$manual_allocs      = 0;
$type_totals        = [];       // [type_name => [color, total, used]]
$dept_totals        = [];       // [dept_name => count]
$expiring_soon      = [];       // carry_forward_expiry within 60 days

foreach ($allocations as $a) {
    $tot  = (float)($a->total_days ?? 0) + (float)($a->carried_forward_days ?? 0);
    $used = (float)($a->used_days ?? 0);
    $rem  = max(0, $tot - $used);
    $stat_total_days += $tot;
    $stat_used_days  += $used;
    if ($rem <= 0 && $tot > 0)              $stat_exhausted++;
    elseif ($tot > 0 && ($rem / $tot) < .2) $stat_low++;
    $covered_emps[$a->employee_id ?? 0] = true;
    if (!empty($a->policy_name)) $policy_allocs++; else $manual_allocs++;

    $tn = $a->leave_type_name ?? 'Unknown';
    if (!isset($type_totals[$tn])) $type_totals[$tn] = ['color'=>$a->leave_type_color??'#6b7280','total'=>0,'used'=>0];
    $type_totals[$tn]['total'] += $tot;
    $type_totals[$tn]['used']  += $used;

    $dn = $a->department_name ?? 'Unassigned';
    $dept_totals[$dn] = ($dept_totals[$dn] ?? 0) + 1;

    if (!empty($a->carry_forward_expiry)) {
        $days_left = (strtotime($a->carry_forward_expiry) - time()) / 86400;
        if ($days_left >= 0 && $days_left <= 60) {
            $expiring_soon[] = $a;
        }
    }
}

$stat_covered_emps = count($covered_emps);
$stat_utilization  = $stat_total_days > 0 ? round($stat_used_days / $stat_total_days * 100) : 0;
arsort($dept_totals);
arsort($type_totals);
$top_types = array_slice($type_totals, 0, 6, true);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Layout ─────────────────────────────────────────────────────── */
.al-page   { padding: 20px 24px; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; font-size:14px; }
.al-body   { display: flex; gap: 20px; align-items: flex-start; }
.al-main   { flex: 1; min-width: 0; }
.al-side   { width: 268px; flex-shrink: 0; display: flex; flex-direction: column; gap: 14px; }

/* ── KPI cards ──────────────────────────────────────────────────── */
.al-kpi-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 18px; }
.al-kpi { background:#fff; border-radius:12px; padding:16px 18px;
          box-shadow:0 1px 3px rgba(0,0,0,.06); position:relative; overflow:hidden; }
.al-kpi__accent { position:absolute; top:0;left:0;right:0; height:3px; border-radius:12px 12px 0 0; }
.al-kpi__icon   { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;
                  justify-content:center;font-size:20px;margin-bottom:10px; }
.al-kpi__val    { font-size:26px;font-weight:900;color:#111827;line-height:1; }
.al-kpi__lbl    { font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;
                  letter-spacing:.05em;margin-top:3px; }
.al-kpi__sub    { font-size:11px;color:#6b7280;margin-top:5px; }

/* ── How-it-works banner ────────────────────────────────────────── */
.al-how { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #bbf7d0;
          border-radius:10px; padding:12px 16px; margin-bottom:16px;
          display:flex; gap:12px; align-items:flex-start; }
.al-how-steps { display:flex; gap:20px; flex-wrap:wrap; margin-top:5px; }
.al-how-step  { display:flex; align-items:center; gap:7px; font-size:12px; color:#166534; }
.al-how-step__num { width:20px;height:20px;border-radius:50%;background:#16a34a;color:#fff;
                    font-size:10px;font-weight:900;display:flex;align-items:center;
                    justify-content:center;flex-shrink:0; }

/* ── Table ─────────────────────────────────────────────────────── */
.al-card  { background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden; }
.al-table { width:100%;border-collapse:collapse; }
.al-table thead tr  { background:#f9fafb; }
.al-table th  { padding:10px 13px;text-align:left;font-size:10.5px;font-weight:800;
                text-transform:uppercase;letter-spacing:.05em;color:#6b7280;
                border-bottom:1px solid #e5e7eb;white-space:nowrap; }
.al-table td  { padding:11px 13px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;
                vertical-align:middle; }
.al-table tbody tr:hover  { background:#fafafa; }
.al-table tbody tr:last-child td { border-bottom:none; }

.al-emp-cell { display:flex;align-items:center;gap:9px; }
.al-avatar   { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;
               justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0; }
.al-type-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 9px;
                 border-radius:6px;font-size:11.5px;font-weight:700; }
.al-bal-pill  { display:inline-block;padding:3px 9px;border-radius:999px;
                font-size:11.5px;font-weight:700; }
.al-progress  { background:#e5e7eb;border-radius:999px;height:5px;margin-top:5px;
                overflow:hidden;width:80px; }
.al-progress-bar { height:100%;border-radius:999px; }
.al-source-badge { display:inline-block;padding:2px 7px;border-radius:4px;font-size:10.5px;font-weight:700; }

/* ── Filters ────────────────────────────────────────────────────── */
.al-filters { display:flex;gap:9px;flex-wrap:wrap;margin-bottom:15px;
              background:#fff;border-radius:10px;padding:11px 14px;
              box-shadow:0 1px 3px rgba(0,0,0,.05); }

/* ── Sidebar cards ──────────────────────────────────────────────── */
.sb-card { background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden; }
.sb-card__head { padding:12px 14px 10px;border-bottom:1px solid #f3f4f6;
                 font-size:12px;font-weight:800;color:#374151;
                 text-transform:uppercase;letter-spacing:.05em; }
.sb-card__body { padding:12px 14px; }
.sb-stat-row { display:flex;justify-content:space-between;align-items:center;
               padding:7px 0;border-bottom:1px solid #f9fafb;font-size:13px; }
.sb-stat-row:last-child { border-bottom:none; }
.sb-stat-row__lbl { color:#374151; display:flex;align-items:center;gap:6px; }
.sb-stat-row__val { font-weight:800;color:#111827; }

.sb-donut-wrap { display:flex;align-items:center;gap:14px; }
.sb-donut-legend { flex:1;font-size:12px; }
.sb-donut-leg-row { display:flex;align-items:center;gap:6px;margin-bottom:5px; }

.sb-type-bar { margin-bottom:9px; }
.sb-type-bar:last-child { margin-bottom:0; }
.sb-type-bar__head { display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px; }
.sb-type-bar__track { background:#e5e7eb;border-radius:999px;height:6px;overflow:hidden; }
.sb-type-bar__fill  { height:100%;border-radius:999px; }

.sb-exp-row { padding:7px 0;border-bottom:1px solid #f9fafb;font-size:12px; }
.sb-exp-row:last-child { border-bottom:none; }

.sb-action-btn { display:flex;align-items:center;gap:8px;padding:8px 12px;
                 border-radius:8px;font-size:12px;font-weight:700;color:#374151;
                 text-decoration:none;background:#f9fafb;border:1px solid #e5e7eb;
                 margin-bottom:6px;transition:background .12s; }
.sb-action-btn:last-child { margin-bottom:0; }
.sb-action-btn:hover { background:#f3f4f6;color:#111827;text-decoration:none; }
.sb-action-btn span.material-symbols-outlined { font-size:16px;color:#6b7280; }

/* ── Modals ─────────────────────────────────────────────────────── */
.al-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;
              align-items:center;justify-content:center;overflow:auto; }
.al-overlay.open { display:flex; }
.al-modal { background:#fff;border-radius:16px;width:100%;max-width:540px;
            box-shadow:0 20px 60px rgba(0,0,0,.2);margin:20px auto;overflow:hidden; }
.al-modal__head { padding:18px 20px 14px;border-bottom:1px solid #f3f4f6;
                  display:flex;align-items:center;justify-content:space-between; }
.al-modal__title { font-size:16px;font-weight:800;color:#111827;margin:0; }
.al-modal__close { background:none;border:none;cursor:pointer;color:#6b7280;padding:4px;border-radius:6px; }
.al-modal__close:hover { background:#f3f4f6; }
.al-modal__body   { padding:20px; }
.al-modal__footer { padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:8px; }

.al-label { display:block;font-size:11px;font-weight:700;color:#374151;
            text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px; }
.al-label span { color:#dc2626; }
.al-ctrl  { width:100%;padding:9px 11px;border:1.5px solid #e5e7eb;border-radius:8px;
            font-size:13px;color:#374151;outline:none;box-sizing:border-box;transition:border-color .15s; }
.al-ctrl:focus { border-color:#006b2c;box-shadow:0 0 0 3px rgba(0,107,44,.08); }
.al-row2  { display:grid;grid-template-columns:1fr 1fr;gap:13px; }
.al-field { margin-bottom:13px; }

.al-btn   { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;
            border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:all .15s; }
.al-btn--primary  { background:#006b2c;color:#fff; }
.al-btn--primary:hover  { background:#005523; }
.al-btn--outline  { background:#fff;border:1.5px solid #d1d5db;color:#374151; }
.al-btn--outline:hover  { background:#f9fafb; }
.al-btn--secondary { background:#eff6ff;border:1.5px solid #bfdbfe;color:#1d4ed8; }
.al-btn--secondary:hover { background:#dbeafe; }

.al-policy-preview { background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;
                     padding:11px 13px;margin-top:9px;display:none; }
.al-policy-preview__title { font-size:11px;font-weight:700;color:#374151;margin-bottom:7px; }
.al-policy-line-row { display:flex;justify-content:space-between;align-items:center;
                      padding:5px 0;border-bottom:1px solid #f3f4f6;font-size:12px; }
.al-policy-line-row:last-child { border-bottom:none; }
.al-policy-dot  { width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px; }

@media (max-width:1100px) { .al-side { display:none; } }
@media (max-width:860px)  { .al-kpi-row { grid-template-columns:repeat(2,1fr); } }
</style>

<div class="al-page">

    <!-- ── Header ─────────────────────────────────────────────────── -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:3px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> / Allocations
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin:0;">Leave Allocations</h1>
            <div style="font-size:12px;color:#9ca3af;margin-top:3px;"><?php echo $stat_total_allocs; ?> allocation<?php echo $stat_total_allocs!=1?'s':''; ?> · <?php echo date('Y'); ?> default year</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button onclick="openBulkModal()" class="al-btn" style="background:#fff8e1;border:1.5px solid #ffc107;color:#856404;">
                <span class="material-symbols-outlined" style="font-size:16px;">group_add</span>Bulk Allocate
            </button>
            <button onclick="openPolicyModal()" class="al-btn al-btn--primary">
                <span class="material-symbols-outlined" style="font-size:16px;">policy</span>Apply Policy
            </button>
            <button onclick="openManualModal()" class="al-btn al-btn--secondary">
                <span class="material-symbols-outlined" style="font-size:16px;">add</span>Add Single
            </button>
        </div>
    </div>

    <!-- ── KPI strip ──────────────────────────────────────────────── -->
    <div class="al-kpi-row">
        <div class="al-kpi">
            <div class="al-kpi__accent" style="background:#006b2c;"></div>
            <div class="al-kpi__icon" style="background:#f0fdf4;color:#16a34a;">
                <span class="material-symbols-outlined">playlist_add_check</span>
            </div>
            <div class="al-kpi__val"><?php echo $stat_total_allocs; ?></div>
            <div class="al-kpi__lbl">Total Allocations</div>
            <div class="al-kpi__sub"><?php echo $stat_covered_emps; ?> employee<?php echo $stat_covered_emps!=1?'s':''; ?> covered</div>
        </div>
        <div class="al-kpi">
            <div class="al-kpi__accent" style="background:#2563eb;"></div>
            <div class="al-kpi__icon" style="background:#eff6ff;color:#2563eb;">
                <span class="material-symbols-outlined">calendar_month</span>
            </div>
            <div class="al-kpi__val"><?php echo number_format($stat_total_days,0); ?></div>
            <div class="al-kpi__lbl">Days Allocated</div>
            <div class="al-kpi__sub"><?php echo number_format($stat_used_days,1); ?> days used</div>
        </div>
        <div class="al-kpi">
            <div class="al-kpi__accent" style="background:<?php echo $stat_utilization > 80 ? '#dc2626' : ($stat_utilization > 50 ? '#d97706' : '#16a34a'); ?>;"></div>
            <div class="al-kpi__icon" style="background:<?php echo $stat_utilization>80?'#fef2f2':($stat_utilization>50?'#fffbeb':'#f0fdf4'); ?>;
                                             color:<?php echo $stat_utilization>80?'#dc2626':($stat_utilization>50?'#d97706':'#16a34a'); ?>;">
                <span class="material-symbols-outlined">percent</span>
            </div>
            <div class="al-kpi__val"><?php echo $stat_utilization; ?>%</div>
            <div class="al-kpi__lbl">Utilization Rate</div>
            <div class="al-kpi__sub"><?php echo number_format($stat_total_days - $stat_used_days,1); ?> days remaining</div>
        </div>
        <div class="al-kpi">
            <div class="al-kpi__accent" style="background:<?php echo ($stat_exhausted+$stat_low)>0?'#dc2626':'#16a34a'; ?>;"></div>
            <div class="al-kpi__icon" style="background:<?php echo ($stat_exhausted+$stat_low)>0?'#fef2f2':'#f0fdf4'; ?>;
                                             color:<?php echo ($stat_exhausted+$stat_low)>0?'#dc2626':'#16a34a'; ?>;">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div class="al-kpi__val"><?php echo $stat_exhausted + $stat_low; ?></div>
            <div class="al-kpi__lbl">At Risk</div>
            <div class="al-kpi__sub"><?php echo $stat_exhausted; ?> exhausted · <?php echo $stat_low; ?> low</div>
        </div>
    </div>

    <!-- ── Two-column body ────────────────────────────────────────── -->
    <div class="al-body">

        <!-- ── Main content ───────────────────────────────────────── -->
        <div class="al-main">

            <!-- How it works -->
            <div class="al-how">
                <span class="material-symbols-outlined" style="color:#16a34a;font-size:22px;flex-shrink:0;margin-top:1px;">lightbulb</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:5px;">How Leave Allocation Works</div>
                    <div class="al-how-steps">
                        <div class="al-how-step"><div class="al-how-step__num">1</div>Configure Leave Types &amp; Policies</div>
                        <div class="al-how-step"><div class="al-how-step__num">2</div><strong>Apply Policy to employee</strong> → all leave types allocated at once</div>
                        <div class="al-how-step"><div class="al-how-step__num">3</div>Employees apply for leave against their balance</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="get" class="al-filters">
                <select name="employee_id" class="form-control" style="font-size:13px;width:190px;" onchange="this.form.submit()">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp->id; ?>" <?php echo (($filters['employee_id']??'')==$emp->id)?'selected':''; ?>>
                        <?php echo htmlspecialchars($emp->full_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="leave_type_id" class="form-control" style="font-size:13px;width:190px;" onchange="this.form.submit()">
                    <option value="">All Leave Types</option>
                    <?php foreach ($leave_types as $lt): ?>
                    <option value="<?php echo $lt->id; ?>" <?php echo (($filters['leave_type_id']??'')==$lt->id)?'selected':''; ?>>
                        <?php echo htmlspecialchars($lt->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="leave_year" class="form-control" style="font-size:13px;width:95px;" onchange="this.form.submit()">
                    <?php for ($y = date('Y')+1; $y >= date('Y')-2; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (($filters['leave_year']??date('Y'))==$y)?'selected':''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <?php if (!empty($filters)): ?>
                <a href="<?php echo $base; ?>/leave/allocations" class="btn btn-default btn-sm" style="border-radius:7px;font-size:13px;align-self:center;">Clear</a>
                <?php endif; ?>
            </form>

            <!-- Table -->
            <div class="al-card">
                <?php if (empty($allocations)): ?>
                <div style="padding:60px;text-align:center;">
                    <span class="material-symbols-outlined" style="font-size:52px;color:#d1d5db;display:block;margin-bottom:12px;">playlist_add</span>
                    <div style="font-size:16px;font-weight:700;color:#374151;margin-bottom:6px;">No allocations yet</div>
                    <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Apply a Leave Policy to an employee to create their entitlements for the year.</div>
                    <button onclick="openPolicyModal()" class="al-btn al-btn--primary">Apply Policy to Employee</button>
                </div>
                <?php else: ?>
                <table class="al-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Yr</th>
                            <th>Source</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Progress</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $av_colors = ['#2563eb','#16a34a','#d97706','#9333ea','#e11d48','#0891b2'];
                    $aci = 0;
                    foreach ($allocations as $alloc):
                        $color   = $alloc->leave_type_color ?? '#2563eb';
                        $total   = (float)($alloc->total_days??0)+(float)($alloc->carried_forward_days??0);
                        $used    = (float)($alloc->used_days??0);
                        $remain  = max(0,$total-$used);
                        $pct     = $total>0 ? min(100,round($used/$total*100)) : 0;
                        $name    = $alloc->employee_name ?? 'Unknown';
                        $initials = strtoupper(substr($name,0,1)).strtoupper(substr(strstr($name,' '),1,1)?:'X');
                        $ac      = $av_colors[$aci++ % count($av_colors)];
                        $hex     = ltrim($color,'#');
                        $r2=hexdec(substr($hex,0,2));$g2=hexdec(substr($hex,2,2));$b2=hexdec(substr($hex,4,2));
                        $bg2     = "rgba($r2,$g2,$b2,.12)";
                        $warn    = $total>0 && $remain/$total < .2;
                    ?>
                    <tr>
                        <td>
                            <div class="al-emp-cell">
                                <div class="al-avatar" style="background:<?php echo $ac; ?>"><?php echo $initials; ?></div>
                                <div>
                                    <div style="font-weight:700;color:#111827;font-size:13px;"><?php echo htmlspecialchars($name); ?></div>
                                    <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($alloc->department_name??''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="al-type-badge" style="background:<?php echo $bg2; ?>;color:<?php echo $color; ?>;">
                                <span style="width:6px;height:6px;border-radius:50%;background:<?php echo $color; ?>;display:inline-block;"></span>
                                <?php echo htmlspecialchars($alloc->leave_type_name??''); ?>
                            </span>
                        </td>
                        <td style="font-weight:700;font-size:12px;color:#6b7280;"><?php echo $alloc->leave_year; ?></td>
                        <td>
                            <?php if (!empty($alloc->policy_name)): ?>
                            <span class="al-source-badge" style="background:#eff6ff;color:#1d4ed8;"><?php echo htmlspecialchars($alloc->policy_name); ?></span>
                            <?php else: ?>
                            <span class="al-source-badge" style="background:#f3f4f6;color:#6b7280;">Manual</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-weight:800;font-size:13px;"><?php echo number_format($total,1); ?></span>
                            <?php if ((float)($alloc->carried_forward_days??0)>0): ?>
                            <div style="font-size:10px;color:#9ca3af;">+<?php echo number_format($alloc->carried_forward_days,1); ?> CF</div>
                            <?php endif; ?>
                        </td>
                        <td style="color:<?php echo $used>0?'#d97706':'#9ca3af'; ?>;font-weight:<?php echo $used>0?700:400; ?>;">
                            <?php echo number_format($used,1); ?>
                        </td>
                        <td>
                            <span class="al-bal-pill" style="background:<?php echo $warn?'#fef2f2':($remain>0?'#f0fdf4':'#f3f4f6'); ?>;
                                                             color:<?php echo $warn?'#dc2626':($remain>0?'#16a34a':'#9ca3af'); ?>;">
                                <?php echo number_format($remain,1); ?> left
                            </span>
                        </td>
                        <td>
                            <div style="font-size:10px;color:#9ca3af;"><?php echo $pct; ?>%</div>
                            <div class="al-progress">
                                <div class="al-progress-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $warn?'#dc2626':$color; ?>;"></div>
                            </div>
                        </td>
                        <td>
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($alloc)); ?>)"
                                    class="btn btn-xs btn-default" style="border-radius:5px;font-size:11px;">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div><!-- /.al-main -->

        <!-- ── Right Sidebar ──────────────────────────────────────── -->
        <div class="al-side">

            <!-- Coverage donut -->
            <div class="sb-card">
                <div class="sb-card__head">Allocation Source</div>
                <div class="sb-card__body">
                    <?php
                    $total_a = $policy_allocs + $manual_allocs;
                    $pol_pct = $total_a>0 ? round($policy_allocs/$total_a*100) : 0;
                    $man_pct = 100-$pol_pct;
                    $circ = 2*3.14159*36;
                    $pol_dash = round($circ * $pol_pct/100, 1);
                    ?>
                    <div class="sb-donut-wrap">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="36" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                            <circle cx="40" cy="40" r="36" fill="none" stroke="#006b2c" stroke-width="10"
                                    stroke-dasharray="<?php echo $pol_dash; ?> <?php echo $circ; ?>"
                                    stroke-dashoffset="<?php echo $circ*.25; ?>"
                                    transform="rotate(-90 40 40)" stroke-linecap="round"/>
                            <text x="40" y="44" text-anchor="middle" font-size="14" font-weight="800" fill="#111827"><?php echo $pol_pct; ?>%</text>
                        </svg>
                        <div class="sb-donut-legend">
                            <div class="sb-donut-leg-row">
                                <span style="width:10px;height:10px;border-radius:3px;background:#006b2c;display:inline-block;flex-shrink:0;"></span>
                                <span style="font-size:12px;color:#374151;">Policy</span>
                                <span style="margin-left:auto;font-weight:800;font-size:12px;"><?php echo $policy_allocs; ?></span>
                            </div>
                            <div class="sb-donut-leg-row">
                                <span style="width:10px;height:10px;border-radius:3px;background:#e5e7eb;display:inline-block;flex-shrink:0;"></span>
                                <span style="font-size:12px;color:#374151;">Manual</span>
                                <span style="margin-left:auto;font-weight:800;font-size:12px;"><?php echo $manual_allocs; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilization by leave type -->
            <?php if (!empty($top_types)): ?>
            <div class="sb-card">
                <div class="sb-card__head">Days by Leave Type</div>
                <div class="sb-card__body">
                    <?php foreach ($top_types as $tn => $td):
                        $upct = $td['total']>0 ? min(100,round($td['used']/$td['total']*100)) : 0;
                    ?>
                    <div class="sb-type-bar">
                        <div class="sb-type-bar__head">
                            <span style="display:flex;align-items:center;gap:5px;">
                                <span style="width:7px;height:7px;border-radius:50%;background:<?php echo $td['color']; ?>;display:inline-block;"></span>
                                <span style="font-weight:600;color:#374151;"><?php echo htmlspecialchars($tn); ?></span>
                            </span>
                            <span style="color:#6b7280;"><?php echo number_format($td['used'],1); ?>/<?php echo number_format($td['total'],1); ?>d</span>
                        </div>
                        <div class="sb-type-bar__track">
                            <div class="sb-type-bar__fill" style="width:<?php echo $upct; ?>%;background:<?php echo $td['color']; ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Department breakdown -->
            <?php if (!empty($dept_totals)): ?>
            <div class="sb-card">
                <div class="sb-card__head">By Department</div>
                <div class="sb-card__body" style="padding:0 14px;">
                    <?php $max_d = max($dept_totals); foreach (array_slice($dept_totals,0,6,true) as $dn=>$cnt): ?>
                    <div class="sb-stat-row">
                        <span class="sb-stat-row__lbl"><?php echo htmlspecialchars($dn); ?></span>
                        <span class="sb-stat-row__val"><?php echo $cnt; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Expiring carry-forwards -->
            <?php if (!empty($expiring_soon)): ?>
            <div class="sb-card">
                <div class="sb-card__head" style="color:#d97706;">
                    <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;margin-right:4px;">schedule</span>
                    Expiring Soon
                </div>
                <div class="sb-card__body" style="padding:6px 14px;">
                    <?php foreach (array_slice($expiring_soon,0,5) as $ea):
                        $days_left = ceil((strtotime($ea->carry_forward_expiry)-time())/86400);
                    ?>
                    <div class="sb-exp-row">
                        <div style="font-weight:700;color:#111827;font-size:12px;"><?php echo htmlspecialchars($ea->employee_name??''); ?></div>
                        <div style="color:#6b7280;font-size:11px;"><?php echo htmlspecialchars($ea->leave_type_name??''); ?> ·
                            <span style="color:<?php echo $days_left<=14?'#dc2626':'#d97706'; ?>;font-weight:700;">
                                <?php echo $days_left; ?>d left
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Policy coverage stats -->
            <div class="sb-card">
                <div class="sb-card__head">Summary</div>
                <div class="sb-card__body" style="padding:0 14px;">
                    <div class="sb-stat-row">
                        <span class="sb-stat-row__lbl">
                            <span class="material-symbols-outlined" style="font-size:14px;color:#6b7280;">people</span>Covered Employees
                        </span>
                        <span class="sb-stat-row__val"><?php echo $stat_covered_emps; ?></span>
                    </div>
                    <div class="sb-stat-row">
                        <span class="sb-stat-row__lbl">
                            <span class="material-symbols-outlined" style="font-size:14px;color:#6b7280;">check_circle</span>Active Policies
                        </span>
                        <span class="sb-stat-row__val"><?php echo count($policies??[]); ?></span>
                    </div>
                    <div class="sb-stat-row">
                        <span class="sb-stat-row__lbl">
                            <span class="material-symbols-outlined" style="font-size:14px;color:#dc2626;">cancel</span>Exhausted
                        </span>
                        <span class="sb-stat-row__val" style="color:#dc2626;"><?php echo $stat_exhausted; ?></span>
                    </div>
                    <div class="sb-stat-row">
                        <span class="sb-stat-row__lbl">
                            <span class="material-symbols-outlined" style="font-size:14px;color:#d97706;">warning</span>Low Balance
                        </span>
                        <span class="sb-stat-row__val" style="color:#d97706;"><?php echo $stat_low; ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="sb-card">
                <div class="sb-card__head">Quick Actions</div>
                <div class="sb-card__body">
                    <a href="<?php echo $base; ?>/leave/config/policies" class="sb-action-btn">
                        <span class="material-symbols-outlined">policy</span>Manage Policies
                    </a>
                    <a href="<?php echo $base; ?>/leave/config/types" class="sb-action-btn">
                        <span class="material-symbols-outlined">category</span>Manage Leave Types
                    </a>
                    <a href="<?php echo $base; ?>/leave/approvals" class="sb-action-btn">
                        <span class="material-symbols-outlined">task_alt</span>Pending Approvals
                    </a>
                    <a href="<?php echo $base; ?>/leave/toil" class="sb-action-btn">
                        <span class="material-symbols-outlined">swap_horiz</span>TOIL Entries
                    </a>
                    <button onclick="openBulkModal()" class="sb-action-btn" style="width:100%;text-align:left;cursor:pointer;">
                        <span class="material-symbols-outlined">group_add</span>Bulk Allocate
                    </button>
                </div>
            </div>

        </div><!-- /.al-side -->

    </div><!-- /.al-body -->

</div><!-- /.al-page -->

<?php
$policies_js = array_map(function($pol) {
    return [
        'id'    => $pol->id,
        'name'  => $pol->name,
        'lines' => array_map(function($l) {
            return [
                'leave_type_id'      => $l->leave_type_id,
                'leave_type_name'    => $l->leave_type_name ?? '',
                'color'              => $l->leave_type_color ?? '#6b7280',
                'annual_days'        => (float)($l->annual_days ?? 0),
                'allow_carryforward' => !empty($l->allow_carryforward),
            ];
        }, $pol->lines ?? []),
    ];
}, $policies ?? []);
?>

<!-- ══ APPLY POLICY MODAL ════════════════════════════════════════ -->
<div class="al-overlay" id="policy-modal" onclick="if(event.target===this)closePolicyModal()">
    <div class="al-modal" style="max-width:520px;">
        <div class="al-modal__head">
            <h3 class="al-modal__title">Apply Policy to Employee</h3>
            <button class="al-modal__close" onclick="closePolicyModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="<?php echo $base; ?>/leave/allocations/apply_policy">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div class="al-modal__body">
                <div class="al-field">
                    <label class="al-label">Employee <span>*</span></label>
                    <select name="employee_id" class="al-ctrl" required>
                        <option value="">— Select employee —</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="al-row2">
                    <div class="al-field">
                        <label class="al-label">Leave Policy <span>*</span></label>
                        <select name="policy_id" id="pol-sel" class="al-ctrl" required onchange="showPolicyPreview(this.value)">
                            <option value="">— Select policy —</option>
                            <?php foreach ($policies??[] as $pol): ?>
                            <option value="<?php echo $pol->id; ?>"><?php echo htmlspecialchars($pol->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="al-field">
                        <label class="al-label">Leave Year <span>*</span></label>
                        <select name="leave_year" class="al-ctrl" required>
                            <?php for ($y=date('Y')+1;$y>=date('Y')-1;$y--): ?>
                            <option value="<?php echo $y; ?>"<?php echo $y==date('Y')?' selected':''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="al-policy-preview" id="policy-preview">
                    <div class="al-policy-preview__title">Will allocate:</div>
                    <div id="policy-lines-preview"></div>
                </div>
                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:9px 13px;margin-top:10px;font-size:12px;color:#92400e;">
                    Existing allocations for the same employee + type + year are skipped.
                </div>
            </div>
            <div class="al-modal__footer">
                <button type="button" class="al-btn al-btn--outline" onclick="closePolicyModal()">Cancel</button>
                <button type="submit" class="al-btn al-btn--primary">
                    <span class="material-symbols-outlined" style="font-size:15px;">task_alt</span>Apply &amp; Allocate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ ADD / EDIT SINGLE ALLOCATION MODAL ════════════════════════ -->
<div class="al-overlay" id="manual-modal" onclick="if(event.target===this)closeManualModal()">
    <div class="al-modal">
        <div class="al-modal__head">
            <h3 class="al-modal__title" id="manual-modal-title">Add Leave Allocation</h3>
            <button class="al-modal__close" onclick="closeManualModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="<?php echo $base; ?>/leave/allocations">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="allocation_id" id="alloc-id">
            <div class="al-modal__body">
                <div class="al-field">
                    <label class="al-label">Employee <span>*</span></label>
                    <select name="employee_id" id="alloc-emp" class="al-ctrl" required>
                        <option value="">— Select employee —</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="al-row2">
                    <div class="al-field">
                        <label class="al-label">Leave Type <span>*</span></label>
                        <select name="leave_type_id" id="alloc-type" class="al-ctrl" required>
                            <option value="">— Select type —</option>
                            <?php foreach ($leave_types as $lt): ?>
                            <option value="<?php echo $lt->id; ?>" data-days="<?php echo (float)($lt->default_days??0); ?>">
                                <?php echo htmlspecialchars($lt->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="al-field">
                        <label class="al-label">Year <span>*</span></label>
                        <select name="leave_year" id="alloc-year" class="al-ctrl" required>
                            <?php for ($y=date('Y')+1;$y>=date('Y')-2;$y--): ?>
                            <option value="<?php echo $y; ?>"<?php echo $y==date('Y')?' selected':''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="al-row2">
                    <div class="al-field">
                        <label class="al-label">Days Allocated <span>*</span></label>
                        <input type="number" name="total_days" id="alloc-days" class="al-ctrl" min="0.5" step="0.5" required placeholder="e.g. 21">
                    </div>
                    <div class="al-field">
                        <label class="al-label">Carried Forward</label>
                        <input type="number" name="carried_forward_days" id="alloc-cf" class="al-ctrl" min="0" step="0.5" value="0">
                    </div>
                </div>
                <div class="al-field">
                    <label class="al-label">Notes</label>
                    <input type="text" name="notes" id="alloc-notes" class="al-ctrl" placeholder="Optional override note">
                </div>
            </div>
            <div class="al-modal__footer">
                <button type="button" class="al-btn al-btn--outline" onclick="closeManualModal()">Cancel</button>
                <button type="submit" class="al-btn al-btn--primary">Save Allocation</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ BULK ALLOCATE MODAL ════════════════════════════════════════ -->
<div class="al-overlay" id="bulk-modal" onclick="if(event.target===this)closeBulkModal()">
    <div class="al-modal" style="max-width:640px;">
        <div class="al-modal__head">
            <h3 class="al-modal__title">Bulk Allocate Leave Policy</h3>
            <button class="al-modal__close" onclick="closeBulkModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="<?php echo $base; ?>/leave/allocations/bulk_apply">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div class="al-modal__body">
                <div class="al-row2" style="margin-bottom:14px;">
                    <div class="al-field" style="margin-bottom:0;">
                        <label class="al-label">Leave Policy <span>*</span></label>
                        <select name="policy_id" id="bulk-pol-sel" class="al-ctrl" required onchange="showBulkPolicyPreview(this.value)">
                            <option value="">— Select policy —</option>
                            <?php foreach ($policies??[] as $pol): ?>
                            <option value="<?php echo $pol->id; ?>"><?php echo htmlspecialchars($pol->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="al-field" style="margin-bottom:0;">
                        <label class="al-label">Leave Year <span>*</span></label>
                        <select name="leave_year" class="al-ctrl" required>
                            <?php for ($y=date('Y')+1;$y>=date('Y')-1;$y--): ?>
                            <option value="<?php echo $y; ?>"<?php echo $y==date('Y')?' selected':''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="al-policy-preview" id="bulk-policy-preview" style="margin-bottom:13px;">
                    <div class="al-policy-preview__title">Will allocate these leave types:</div>
                    <div id="bulk-policy-lines"></div>
                </div>
                <div class="al-field" style="margin-bottom:10px;">
                    <label class="al-label">Filter by Department</label>
                    <select id="bulk-dept-filter" class="al-ctrl" onchange="filterBulkEmployees(this.value)">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="border:1.5px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                    <div style="background:#f9fafb;padding:8px 13px;border-bottom:1px solid #e5e7eb;
                                display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:11px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:.04em;">Select Employees</span>
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#374151;cursor:pointer;">
                            <input type="checkbox" id="bulk-select-all" onchange="toggleAllEmployees(this.checked)" style="cursor:pointer;">
                            Select All Visible
                        </label>
                    </div>
                    <div id="bulk-emp-list" style="max-height:210px;overflow-y:auto;padding:6px 0;">
                        <?php
                        $deptMap = [];
                        foreach ($departments as $d) { $deptMap[$d->id] = $d->name; }
                        $bcolors = ['#2563eb','#16a34a','#d97706','#9333ea','#e11d48','#0891b2'];
                        $bi = 0;
                        foreach ($employees as $emp):
                            $bi_col  = $bcolors[$bi++ % count($bcolors)];
                            $dept_id = property_exists($emp,'department_id') ? ($emp->department_id??0) : 0;
                        ?>
                        <label class="bulk-emp-row" data-dept="<?php echo $dept_id; ?>"
                               style="display:flex;align-items:center;gap:9px;padding:6px 13px;cursor:pointer;">
                            <input type="checkbox" name="employee_ids[]" value="<?php echo $emp->id; ?>" style="cursor:pointer;">
                            <div style="width:28px;height:28px;border-radius:7px;background:<?php echo $bi_col; ?>;color:#fff;
                                        font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <?php echo strtoupper(substr($emp->full_name,0,1)); ?>
                            </div>
                            <div>
                                <div style="font-size:12px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($emp->full_name); ?></div>
                                <?php if ($dept_id && isset($deptMap[$dept_id])): ?>
                                <div style="font-size:10px;color:#9ca3af;"><?php echo htmlspecialchars($deptMap[$dept_id]); ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="background:#f9fafb;padding:5px 13px;border-top:1px solid #e5e7eb;font-size:11px;color:#9ca3af;">
                        <span id="bulk-selected-count">0</span> employee(s) selected
                    </div>
                </div>
                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:9px 13px;margin-top:11px;font-size:12px;color:#92400e;">
                    Existing allocations for the same employee + leave type + year are skipped automatically.
                </div>
            </div>
            <div class="al-modal__footer">
                <button type="button" class="al-btn al-btn--outline" onclick="closeBulkModal()">Cancel</button>
                <button type="submit" class="al-btn al-btn--primary">
                    <span class="material-symbols-outlined" style="font-size:15px;">task_alt</span>Allocate to Selected
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const policiesData = <?php echo json_encode($policies_js); ?>;

// ── Policy modal ────────────────────────────────────────────────
function openPolicyModal()  { document.getElementById('policy-modal').classList.add('open'); }
function closePolicyModal() {
    document.getElementById('policy-modal').classList.remove('open');
    document.getElementById('policy-preview').style.display = 'none';
}

// ── Manual modal ────────────────────────────────────────────────
function openManualModal() {
    document.getElementById('manual-modal-title').textContent = 'Add Leave Allocation';
    ['alloc-id','alloc-emp','alloc-type','alloc-notes'].forEach(id => { var el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('alloc-days').value = '';
    document.getElementById('alloc-cf').value   = '0';
    document.getElementById('manual-modal').classList.add('open');
}
function closeManualModal() { document.getElementById('manual-modal').classList.remove('open'); }

function openEditModal(alloc) {
    document.getElementById('manual-modal-title').textContent = 'Edit Leave Allocation';
    document.getElementById('alloc-id').value    = alloc.id;
    document.getElementById('alloc-emp').value   = alloc.employee_id;
    document.getElementById('alloc-type').value  = alloc.leave_type_id;
    document.getElementById('alloc-year').value  = alloc.leave_year;
    document.getElementById('alloc-days').value  = alloc.total_days;
    document.getElementById('alloc-cf').value    = alloc.carried_forward_days || 0;
    document.getElementById('alloc-notes').value = alloc.notes || '';
    document.getElementById('manual-modal').classList.add('open');
}

document.getElementById('alloc-type').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var days = opt.getAttribute('data-days');
    if (days && parseFloat(days) > 0 && !document.getElementById('alloc-id').value) {
        document.getElementById('alloc-days').value = days;
    }
});

// ── Shared policy preview renderer ──────────────────────────────
function renderPolicyLines(pol) {
    if (!pol || !pol.lines.length) return '';
    return pol.lines.map(function(l) {
        return '<div class="al-policy-line-row">'
            + '<span><span class="al-policy-dot" style="background:'+l.color+'"></span><strong>'+l.leave_type_name+'</strong></span>'
            + '<span style="font-weight:700;color:#006b2c;">'+l.annual_days.toFixed(1)+' days/yr'
            + (l.allow_carryforward ? ' <span style="color:#2563eb;font-size:10px;">+carry</span>' : '')
            + '</span></div>';
    }).join('');
}

function showPolicyPreview(policyId) {
    var preview = document.getElementById('policy-preview');
    var pol = policiesData.find(p => p.id == policyId);
    document.getElementById('policy-lines-preview').innerHTML = renderPolicyLines(pol);
    preview.style.display = pol && pol.lines.length ? 'block' : 'none';
}

// ── Bulk modal ──────────────────────────────────────────────────
function openBulkModal()  { updateBulkCount(); document.getElementById('bulk-modal').classList.add('open'); }
function closeBulkModal() { document.getElementById('bulk-modal').classList.remove('open'); }

function showBulkPolicyPreview(policyId) {
    var preview  = document.getElementById('bulk-policy-preview');
    var pol = policiesData.find(p => p.id == policyId);
    document.getElementById('bulk-policy-lines').innerHTML = renderPolicyLines(pol);
    preview.style.display = pol && pol.lines.length ? 'block' : 'none';
}

function filterBulkEmployees(deptId) {
    document.querySelectorAll('.bulk-emp-row').forEach(function(row) {
        var show = !deptId || row.getAttribute('data-dept') == deptId;
        row.style.display = show ? 'flex' : 'none';
        if (!show) row.querySelector('input').checked = false;
    });
    document.getElementById('bulk-select-all').checked = false;
    updateBulkCount();
}

function toggleAllEmployees(checked) {
    document.querySelectorAll('.bulk-emp-row').forEach(function(row) {
        if (row.style.display !== 'none') row.querySelector('input').checked = checked;
    });
    updateBulkCount();
}

function updateBulkCount() {
    var n = document.querySelectorAll('.bulk-emp-row input:checked').length;
    document.getElementById('bulk-selected-count').textContent = n;
}

document.getElementById('bulk-emp-list').addEventListener('change', function(e) {
    if (e.target && e.target.type === 'checkbox') {
        updateBulkCount();
        var vis = document.querySelectorAll('.bulk-emp-row:not([style*="display: none"]):not([style*="display:none"])');
        var all = Array.from(vis).every(r => r.querySelector('input').checked);
        document.getElementById('bulk-select-all').checked = (vis.length > 0 && all);
    }
});
</script>

<?php init_tail(); ?>
