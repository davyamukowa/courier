<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$today = date('l, F j, Y');
$staff = get_staff();
$fname = $staff ? $staff->firstname : 'there';

// Status badge helper
function leave_status_badge($status) {
    $map = [
        'draft'           => ['Draft',           '#6b7280','#f3f4f6'],
        'pending_manager' => ['Pending Manager',  '#d97706','#fef9c3'],
        'pending_hr'      => ['Pending HR',       '#9333ea','#faf5ff'],
        'approved'        => ['Approved',         '#16a34a','#f0fdf4'],
        'rejected'        => ['Rejected',         '#dc2626','#fef2f2'],
        'cancelled'       => ['Cancelled',        '#6b7280','#f3f4f6'],
        'cancel_requested'=> ['Cancel Requested', '#d97706','#fef9c3'],
    ];
    $s = $map[$status] ?? [$status,'#6b7280','#f3f4f6'];
    return "<span style='display:inline-block;padding:2px 9px;border-radius:999px;font-size:10px;font-weight:700;color:{$s[1]};background:{$s[2]};'>".htmlspecialchars($s[0])."</span>";
}
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Leave Dashboard Styles ─────────────────────────────────── */
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Hero */
.lv-hero {
    background: linear-gradient(135deg, #0f0f1a 0%, #1e1040 50%, #2d1b69 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(15,15,26,.4);
    position: relative;
    overflow: hidden;
}
.lv-hero::before {
    content: '';
    position: absolute;
    right: -40px; top: -60px;
    width: 260px; height: 260px;
    background: rgba(139,92,246,.12);
    border-radius: 50%;
}
.lv-hero::after {
    content: '';
    position: absolute;
    right: 80px; bottom: -90px;
    width: 180px; height: 180px;
    background: rgba(99,102,241,.08);
    border-radius: 50%;
}
.lv-hero__greeting { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.lv-hero__date { font-size: 13px; opacity: .6; }
.lv-hero__actions { display: flex; gap: 10px; position: relative; z-index: 1; }
.lv-hero__btn {
    padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 700;
    cursor: pointer; border: none; display: flex; align-items: center; gap: 6px;
    text-decoration: none; transition: transform .15s, box-shadow .15s;
}
.lv-hero__btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.3); }
.lv-hero__btn--primary { background: #8b5cf6; color: #fff; box-shadow: 0 2px 10px rgba(139,92,246,.4); }
.lv-hero__btn--ghost { background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.2); }

/* Balance Cards */
.lv-balance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.lv-balance-card {
    background: #fff;
    border-radius: 14px;
    padding: 18px 18px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 20px rgba(0,0,0,.06);
    border-top: 3px solid var(--lc);
    transition: transform .15s, box-shadow .15s;
    cursor: default;
}
.lv-balance-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 28px rgba(0,0,0,.1);
}
.lv-balance-card__header {
    display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
}
.lv-balance-card__icon {
    width: 36px; height: 36px;
    background: var(--lc-bg);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lv-balance-card__icon .material-symbols-outlined { color: var(--lc); font-size: 20px; }
.lv-balance-card__name { font-size: 12px; font-weight: 700; color: #374151; line-height: 1.3; }
.lv-balance-card__big { font-size: 32px; font-weight: 900; color: var(--lc); line-height: 1; margin-bottom: 4px; }
.lv-balance-card__sub { font-size: 11px; color: #9ca3af; margin-bottom: 10px; }
.lv-balance-bar { height: 5px; background: #f3f4f6; border-radius: 3px; overflow: hidden; }
.lv-balance-bar__fill { height: 100%; background: var(--lc); border-radius: 3px; transition: width .4s; }
.lv-balance-card__apply {
    display: block; margin-top: 10px; text-align: center;
    font-size: 11px; font-weight: 700; color: var(--lc);
    text-decoration: none; padding: 6px;
    border-radius: 6px; background: var(--lc-bg);
    transition: background .15s;
}
.lv-balance-card__apply:hover { background: var(--lc); color: #fff; }

/* Two-column layout */
.lv-grid-2 { display: grid; grid-template-columns: 1fr 380px; gap: 16px; }
@media (max-width: 1100px) { .lv-grid-2 { grid-template-columns: 1fr; } }

/* Panel */
.lv-panel {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 20px rgba(0,0,0,.06);
    overflow: hidden;
}
.lv-panel__head {
    padding: 14px 18px;
    border-bottom: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between;
}
.lv-panel__title { font-size: 13px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 7px; }
.lv-panel__title .material-symbols-outlined { font-size: 18px; color: #6b7280; }
.lv-panel__more { font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600; }
.lv-panel__more:hover { text-decoration: underline; }

/* Team on leave */
.lv-team-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 18px; border-bottom: 1px solid #f9fafb;
}
.lv-team-item:last-child { border-bottom: none; }
.lv-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.lv-team-item__name { font-size: 12px; font-weight: 600; color: #111827; }
.lv-team-item__info { font-size: 11px; color: #6b7280; }

/* Request rows */
.lv-req-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 18px; border-bottom: 1px solid #f9fafb;
}
.lv-req-item:last-child { border-bottom: none; }
.lv-req-item__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.lv-req-item__type { font-size: 12px; font-weight: 600; color: #111827; flex: 1; }
.lv-req-item__dates { font-size: 11px; color: #6b7280; }

/* Approval cards */
.lv-appr-item {
    padding: 12px 18px;
    border-bottom: 1px solid #f9fafb;
    display: flex; gap: 12px; align-items: flex-start;
}
.lv-appr-item:last-child { border-bottom: none; }
.lv-appr-item__name { font-size: 12px; font-weight: 700; color: #111827; }
.lv-appr-item__meta { font-size: 11px; color: #6b7280; margin-top: 2px; }
.lv-appr-btns { display: flex; gap: 6px; margin-top: 8px; }
.lv-btn-approve {
    font-size: 11px; font-weight: 700; padding: 4px 12px;
    border-radius: 5px; border: none; cursor: pointer;
    background: #dcfce7; color: #16a34a; transition: background .15s;
}
.lv-btn-approve:hover { background: #16a34a; color: #fff; }
.lv-btn-reject {
    font-size: 11px; font-weight: 700; padding: 4px 12px;
    border-radius: 5px; border: none; cursor: pointer;
    background: #fee2e2; color: #dc2626; transition: background .15s;
}
.lv-btn-reject:hover { background: #dc2626; color: #fff; }

/* Empty state */
.lv-empty { text-align: center; padding: 32px 16px; color: #9ca3af; font-size: 12px; }
.lv-empty .material-symbols-outlined { font-size: 36px; display: block; margin-bottom: 8px; opacity: .4; }
</style>

<div class="lv-page">

    <!-- Hero Banner -->
    <div class="lv-hero">
        <div>
            <div class="lv-hero__greeting">Good <?php echo date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening'); ?>, <?php echo htmlspecialchars($fname); ?> 👋</div>
            <div class="lv-hero__date"><?php echo $today; ?> · Leave Management</div>
        </div>
        <div class="lv-hero__actions">
            <a href="<?php echo $base; ?>/leave/apply" class="lv-hero__btn lv-hero__btn--primary">
                <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                Apply for Leave
            </a>
            <a href="<?php echo $base; ?>/leave/my_requests" class="lv-hero__btn lv-hero__btn--ghost">
                <span class="material-symbols-outlined" style="font-size:16px;">history</span>
                My History
            </a>
        </div>
    </div>

    <?php if (!empty($is_admin)): ?>
    <!-- Admin org-wide KPI strip -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px;">
        <?php
        $kpis = [
            ['Pending Manager', $counts['pending_manager'] ?? 0, '#d97706', '#fffbeb', 'supervisor_account'],
            ['Pending HR',      $counts['pending_hr']      ?? 0, '#9333ea', '#faf5ff', 'admin_panel_settings'],
            ['Approved',        $counts['approved']        ?? 0, '#16a34a', '#f0fdf4', 'check_circle'],
            ['Rejected',        $counts['rejected']        ?? 0, '#dc2626', '#fef2f2', 'cancel'],
            ['On Leave Today',  count($team_on_leave ?? []), '#2563eb', '#eff6ff', 'beach_access'],
        ];
        foreach ($kpis as [$lbl,$val,$col,$bg,$ico]): ?>
        <div style="background:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.06);
                    border-top:3px solid <?php echo $col; ?>;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <div style="width:30px;height:30px;border-radius:8px;background:<?php echo $bg; ?>;
                            display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:16px;color:<?php echo $col; ?>;"><?php echo $ico; ?></span>
                </div>
            </div>
            <div style="font-size:28px;font-weight:900;color:#111827;line-height:1;"><?php echo $val; ?></div>
            <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-top:3px;"><?php echo $lbl; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Leave Balance Cards -->
    <?php if (!empty($balance_summary)): ?>
    <div class="lv-balance-grid">
        <?php
        $icons = ['annual'=>'beach_access','sick'=>'local_hospital','maternity'=>'child_care',
                  'paternity'=>'escalator_warning','study'=>'school','compassionate'=>'favorite',
                  'unpaid'=>'money_off','emergency'=>'emergency','toil'=>'more_time'];
        foreach ($balance_summary as $bal):
            $total     = (float)($bal->total_days) + (float)($bal->carried_forward_days);
            $used      = (float)$bal->used_days;
            $remaining = max(0, $total - $used);
            $pct       = $total > 0 ? min(100, round(($used / $total) * 100)) : 0;
            $color     = $bal->color ?? '#2563eb';
            $hex       = ltrim($color, '#');
            $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
            $bg        = "rgba($r,$g,$b,.1)";
            $key       = strtolower(preg_replace('/[^a-z]/i','_',$bal->leave_type_name ?? ''));
            $icon      = 'event_available';
            foreach ($icons as $k => $ico) { if (str_contains(strtolower($bal->leave_type_name ?? ''), $k)) { $icon = $ico; break; } }
        ?>
        <div class="lv-balance-card" style="--lc:<?php echo $color; ?>; --lc-bg:<?php echo $bg; ?>;">
            <div class="lv-balance-card__header">
                <div class="lv-balance-card__icon">
                    <span class="material-symbols-outlined"><?php echo $icon; ?></span>
                </div>
                <div class="lv-balance-card__name"><?php echo htmlspecialchars($bal->leave_type_name ?? ''); ?></div>
            </div>
            <div class="lv-balance-card__big"><?php echo number_format($remaining, 1); ?></div>
            <div class="lv-balance-card__sub">
                <?php echo number_format($used, 1); ?> used of <?php echo number_format($total, 1); ?>
                <?php echo $bal->unit === 'hours' ? 'hrs' : 'days'; ?>
                <?php if ((float)$bal->carried_forward_days > 0): ?>
                <span style="color:var(--lc);font-weight:600;"> · <?php echo number_format($bal->carried_forward_days,1); ?> c/f</span>
                <?php endif; ?>
            </div>
            <div class="lv-balance-bar">
                <div class="lv-balance-bar__fill" style="width:<?php echo $pct; ?>%;"></div>
            </div>
            <a href="<?php echo $base; ?>/leave/apply?type=<?php echo $bal->leave_type_id; ?>" class="lv-balance-card__apply">
                + Apply
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background:#fff;border-radius:14px;padding:28px;text-align:center;color:#9ca3af;margin-bottom:24px;
                box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);">
        <span class="material-symbols-outlined" style="font-size:40px;display:block;margin-bottom:8px;opacity:.4;">event_busy</span>
        No leave allocations found for <?php echo date('Y'); ?>.
        <a href="<?php echo $base; ?>/leave/allocations" style="color:#2563eb;font-weight:600;">Set up allocations</a>
    </div>
    <?php endif; ?>

    <!-- Two-column: Team Calendar + Approvals/Recent -->
    <div class="lv-grid-2">

        <!-- Left: Team on leave today + recent requests -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Team on Leave Today -->
            <div class="lv-panel">
                <div class="lv-panel__head">
                    <div class="lv-panel__title">
                        <span class="material-symbols-outlined">groups</span>
                        Team on Leave Today
                    </div>
                    <a href="<?php echo $base; ?>/leave/reports/calendar" class="lv-panel__more">View calendar →</a>
                </div>
                <?php if (empty($team_on_leave)): ?>
                <div class="lv-empty">
                    <span class="material-symbols-outlined">celebration</span>
                    Full team is in today!
                </div>
                <?php else: ?>
                <?php
                $avatar_colors = ['#2563eb','#16a34a','#d97706','#9333ea','#e11d48','#0891b2'];
                $ci = 0;
                foreach ($team_on_leave as $tol):
                    $initials2 = strtoupper(substr($tol->employee_name,0,1)) . strtoupper(substr(strstr($tol->employee_name,' '),1,1));
                    $ac = $avatar_colors[$ci++ % count($avatar_colors)];
                ?>
                <div class="lv-team-item">
                    <div class="lv-avatar" style="background:<?php echo $ac; ?>"><?php echo $initials2; ?></div>
                    <div style="flex:1;">
                        <div class="lv-team-item__name"><?php echo htmlspecialchars($tol->employee_name); ?></div>
                        <div class="lv-team-item__info">
                            <?php echo htmlspecialchars($tol->leave_type_name ?? ''); ?> ·
                            Back <?php echo $tol->date_to ? date('D j M', strtotime($tol->date_to)) : '?'; ?>
                        </div>
                    </div>
                    <span style="font-size:10px;background:#fef9c3;color:#d97706;padding:2px 8px;border-radius:999px;font-weight:700;">On Leave</span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recent Requests -->
            <div class="lv-panel">
                <div class="lv-panel__head">
                    <div class="lv-panel__title">
                        <span class="material-symbols-outlined">history</span>
                        <?php echo !empty($is_admin) ? 'Recent Leave Requests' : 'My Recent Requests'; ?>
                    </div>
                    <a href="<?php echo $base; ?>/leave/my_requests" class="lv-panel__more">View all →</a>
                </div>
                <?php if (empty($recent_requests)): ?>
                <div class="lv-empty">
                    <span class="material-symbols-outlined">event_note</span>
                    No leave requests yet.
                </div>
                <?php else: ?>
                <?php foreach ($recent_requests as $req): ?>
                <div class="lv-req-item">
                    <div class="lv-req-item__dot" style="background:<?php echo $req->type_color ?? '#2563eb'; ?>;"></div>
                    <div style="flex:1;">
                        <?php if (!empty($is_admin)): ?>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;margin-bottom:1px;"><?php echo htmlspecialchars($req->employee_name ?? ''); ?></div>
                        <?php endif; ?>
                        <div class="lv-req-item__type"><?php echo htmlspecialchars($req->leave_type_name ?? ''); ?></div>
                        <div class="lv-req-item__dates">
                            <?php echo date('d M', strtotime($req->date_from)); ?> –
                            <?php echo date('d M Y', strtotime($req->date_to)); ?> ·
                            <?php echo number_format($req->total_days, 1); ?> day<?php echo $req->total_days != 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    <?php echo leave_status_badge($req->status); ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div><!-- /left -->

        <!-- Right: Pending Approvals -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <?php if (!empty($pending_manager) || !empty($pending_hr)): ?>
            <!-- Manager Approvals Queue -->
            <?php if (!empty($pending_manager)): ?>
            <div class="lv-panel">
                <div class="lv-panel__head">
                    <div class="lv-panel__title">
                        <span class="material-symbols-outlined" style="color:#d97706;">approval</span>
                        Awaiting Your Approval
                        <span style="background:#d97706;color:#fff;border-radius:999px;font-size:10px;padding:1px 7px;font-weight:700;">
                            <?php echo count($pending_manager); ?>
                        </span>
                    </div>
                    <a href="<?php echo $base; ?>/leave/approvals" class="lv-panel__more">Manage →</a>
                </div>
                <?php
                $ci2 = 0;
                foreach (array_slice($pending_manager, 0, 4) as $ap):
                    $ini2 = strtoupper(substr($ap->employee_name,0,1)) . strtoupper(substr(strstr($ap->employee_name,' '),1,1));
                    $ac2  = $avatar_colors[$ci2++ % count($avatar_colors)];
                ?>
                <div class="lv-appr-item">
                    <div class="lv-avatar" style="background:<?php echo $ac2; ?>; width:32px; height:32px; font-size:11px;"><?php echo $ini2; ?></div>
                    <div style="flex:1;">
                        <div class="lv-appr-item__name"><?php echo htmlspecialchars($ap->employee_name); ?></div>
                        <div class="lv-appr-item__meta">
                            <?php echo htmlspecialchars($ap->leave_type_name ?? ''); ?> ·
                            <?php echo date('d M', strtotime($ap->date_from)); ?>–<?php echo date('d M', strtotime($ap->date_to)); ?>
                            (<?php echo number_format($ap->total_days,1); ?> days)
                        </div>
                        <div class="lv-appr-btns">
                            <form method="post" action="<?php echo $base; ?>/leave/approvals/approve/<?php echo $ap->id; ?>" style="display:inline;">
                                <button type="submit" class="lv-btn-approve">✓ Approve</button>
                            </form>
                            <button type="button" class="lv-btn-reject"
                                    onclick="showRejectModal(<?php echo $ap->id; ?>, 'manager')">✕ Reject</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- HR Approvals Queue -->
            <?php if (!empty($pending_hr)): ?>
            <div class="lv-panel">
                <div class="lv-panel__head">
                    <div class="lv-panel__title">
                        <span class="material-symbols-outlined" style="color:#9333ea;">verified_user</span>
                        HR Approval Queue
                        <span style="background:#9333ea;color:#fff;border-radius:999px;font-size:10px;padding:1px 7px;font-weight:700;">
                            <?php echo count($pending_hr); ?>
                        </span>
                    </div>
                    <a href="<?php echo $base; ?>/leave/hr_approvals" class="lv-panel__more">Manage →</a>
                </div>
                <?php foreach (array_slice($pending_hr, 0, 3) as $hr_ap): ?>
                <div class="lv-appr-item">
                    <div style="width:8px;height:8px;border-radius:50%;background:#9333ea;flex-shrink:0;margin-top:5px;"></div>
                    <div style="flex:1;">
                        <div class="lv-appr-item__name"><?php echo htmlspecialchars($hr_ap->employee_name); ?></div>
                        <div class="lv-appr-item__meta">
                            <?php echo htmlspecialchars($hr_ap->leave_type_name ?? ''); ?> ·
                            <?php echo date('d M', strtotime($hr_ap->date_from)); ?>–<?php echo date('d M', strtotime($hr_ap->date_to)); ?>
                        </div>
                        <div class="lv-appr-btns">
                            <form method="post" action="<?php echo $base; ?>/leave/hr_approvals/approve/<?php echo $hr_ap->id; ?>" style="display:inline;">
                                <button type="submit" class="lv-btn-approve">✓ Approve</button>
                            </form>
                            <button type="button" class="lv-btn-reject"
                                    onclick="showRejectModal(<?php echo $hr_ap->id; ?>, 'hr')">✕ Reject</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="lv-panel">
                <div class="lv-panel__head">
                    <div class="lv-panel__title">
                        <span class="material-symbols-outlined">check_circle</span>
                        Approvals
                    </div>
                </div>
                <div class="lv-empty">
                    <span class="material-symbols-outlined">task_alt</span>
                    No pending approvals. All clear!
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Stats Card -->
            <div class="lv-panel" style="padding:18px;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:12px;letter-spacing:.05em;">
                    This Year at a Glance
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <?php
                    $total_taken  = array_sum(array_column((array)($balance_summary ?? []), 'used_days'));
                    $total_avail  = array_sum(array_map(function($b){ return (float)$b->total_days + (float)$b->carried_forward_days; }, (array)($balance_summary ?? [])));
                    $total_remain = max(0, $total_avail - $total_taken);
                    $stats = [
                        ['Days Taken',     number_format($total_taken,1),  '#dc2626','error'],
                        ['Days Remaining', number_format($total_remain,1), '#16a34a','event_available'],
                        ['Requests Made',  count((array)($recent_requests ?? [])), '#2563eb','list_alt'],
                        ['Leave Types',    count((array)($balance_summary ?? [])), '#9333ea','category'],
                    ];
                    foreach ($stats as $st):
                    ?>
                    <div style="background:#f9fafb;border-radius:10px;padding:12px;">
                        <span class="material-symbols-outlined" style="font-size:18px;color:<?php echo $st[2]; ?>;display:block;margin-bottom:4px;"><?php echo $st[3]; ?></span>
                        <div style="font-size:20px;font-weight:800;color:<?php echo $st[2]; ?>;"><?php echo $st[1]; ?></div>
                        <div style="font-size:10px;color:#6b7280;font-weight:600;"><?php echo $st[0]; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /right -->
    </div><!-- /.lv-grid-2 -->
</div><!-- /.lv-page -->

<!-- Reject Modal -->
<div id="reject-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;">Reject Leave Request</h3>
        <form id="reject-form" method="post">
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:4px;">Reason for Rejection</label>
                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Provide a reason..."></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="hideRejectModal()" class="btn btn-default btn-sm">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Reject Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id, level) {
    var base = '<?php echo $base; ?>';
    var url = base + '/leave/' + (level === 'hr' ? 'hr_approvals' : 'approvals') + '/reject/' + id;
    document.getElementById('reject-form').action = url;
    document.getElementById('reject-modal').style.display = 'flex';
}
function hideRejectModal() { document.getElementById('reject-modal').style.display = 'none'; }
</script>
<?php init_tail(); ?>
