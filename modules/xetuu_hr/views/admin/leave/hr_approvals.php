<?php defined('BASEPATH') or exit('No direct script access allowed');
$base  = admin_url('xetuu_hr');
$route = 'hr_approvals';

$cnt_pending_mgr = $counts['pending_manager'] ?? 0;
$cnt_pending_hr  = $counts['pending_hr']      ?? 0;
$cnt_approved    = $counts['approved']         ?? 0;
$cnt_rejected    = $counts['rejected']         ?? 0;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.ap-page { padding:20px 24px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; font-size:14px; }
.ap-body { display:flex; gap:20px; align-items:flex-start; }
.ap-main { flex:1; min-width:0; }
.ap-side { width:260px; flex-shrink:0; display:flex; flex-direction:column; gap:14px; }
@media(max-width:1100px){ .ap-side { display:none; } }

.ap-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:18px; }
@media(max-width:860px){ .ap-kpi-row { grid-template-columns:repeat(2,1fr); } }
.ap-kpi { background:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.06);position:relative;overflow:hidden; }
.ap-kpi__bar { position:absolute;top:0;left:0;right:0;height:3px; }
.ap-kpi__val { font-size:28px;font-weight:900;color:#111827;line-height:1.1; }
.ap-kpi__lbl { font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-top:2px; }
.ap-kpi__sub { font-size:11px;color:#6b7280;margin-top:4px; }

.ap-card { background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);
           overflow:hidden;margin-bottom:12px;transition:box-shadow .15s; }
.ap-card:hover { box-shadow:0 4px 28px rgba(0,0,0,.1); }
.ap-card:last-child { margin-bottom:0; }
.ap-card__head { padding:14px 18px 12px;display:flex;align-items:flex-start;gap:14px;border-bottom:1px solid #f3f4f6; }
.ap-card__body { padding:12px 18px; }
.ap-card__foot { padding:10px 18px;background:#f9fafb;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }

.ap-avatar { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;
             justify-content:center;font-size:15px;font-weight:700;color:#fff;flex-shrink:0; }
.ap-info-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:10px; }
.ap-info-box { background:#f9fafb;border-radius:8px;padding:10px 12px; }
.ap-info-box__lbl { font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;margin-bottom:3px; }
.ap-info-box__val { font-size:13px;font-weight:800;color:#111827; }

.ap-btn-approve { padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;
                  background:#9333ea;color:#fff;border:none;cursor:pointer;
                  display:flex;align-items:center;gap:6px;
                  box-shadow:0 2px 8px rgba(147,51,234,.3);transition:transform .15s,box-shadow .15s; }
.ap-btn-approve:hover { transform:translateY(-1px);box-shadow:0 4px 14px rgba(147,51,234,.4); }
.ap-btn-reject  { padding:8px 18px;border-radius:8px;font-size:12px;font-weight:700;
                  background:#fef2f2;color:#dc2626;border:1px solid #fecaca;cursor:pointer;
                  display:flex;align-items:center;gap:6px;transition:background .15s; }
.ap-btn-reject:hover { background:#dc2626;color:#fff; }
.ap-btn-link { font-size:11px;color:#6b7280;text-decoration:none;margin-left:auto; }
.ap-btn-link:hover { color:#111827;text-decoration:underline; }

.sb { background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden; }
.sb__head { padding:11px 14px;border-bottom:1px solid #f3f4f6;font-size:11px;font-weight:800;
            color:#374151;text-transform:uppercase;letter-spacing:.05em; }
.sb__body { padding:12px 14px; }
.sb-row { display:flex;justify-content:space-between;align-items:center;
          padding:7px 0;border-bottom:1px solid #f9fafb;font-size:13px; }
.sb-row:last-child { border-bottom:none; }
.sb-action { display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;
             font-size:12px;font-weight:700;color:#374151;text-decoration:none;
             background:#f9fafb;border:1px solid #e5e7eb;margin-bottom:6px;transition:background .12s; }
.sb-action:last-child { margin-bottom:0; }
.sb-action:hover { background:#f3f4f6;color:#111827;text-decoration:none; }
.sb-action span { font-size:15px;color:#6b7280; }
</style>

<div class="ap-page">

    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:3px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> / HR Approvals
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin:0;">
                HR Leave Approvals
                <?php if ($cnt_pending_hr > 0): ?>
                <span style="background:#9333ea;color:#fff;border-radius:999px;font-size:13px;
                             padding:2px 11px;font-weight:700;vertical-align:middle;margin-left:8px;"><?php echo $cnt_pending_hr; ?></span>
                <?php endif; ?>
            </h1>
            <div style="font-size:12px;color:#9ca3af;margin-top:3px;">Final approval level — these requests have already been approved by a Manager</div>
        </div>
        <a href="<?php echo $base; ?>/leave/approvals" style="text-decoration:none;">
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 16px;display:flex;align-items:center;gap:10px;cursor:pointer;">
                <span class="material-symbols-outlined" style="color:#d97706;font-size:20px;">supervisor_account</span>
                <div>
                    <div style="font-size:11px;font-weight:700;color:#92400e;">Manager Approvals</div>
                    <div style="font-size:20px;font-weight:900;color:#111827;line-height:1;"><?php echo $cnt_pending_mgr; ?></div>
                </div>
            </div>
        </a>
    </div>

    <!-- KPI strip -->
    <div class="ap-kpi-row">
        <div class="ap-kpi">
            <div class="ap-kpi__bar" style="background:#9333ea;"></div>
            <div class="ap-kpi__val"><?php echo $cnt_pending_hr; ?></div>
            <div class="ap-kpi__lbl">Awaiting HR</div>
            <div class="ap-kpi__sub">Need your final decision</div>
        </div>
        <div class="ap-kpi">
            <div class="ap-kpi__bar" style="background:#d97706;"></div>
            <div class="ap-kpi__val"><?php echo $cnt_pending_mgr; ?></div>
            <div class="ap-kpi__lbl">At Manager Level</div>
            <div class="ap-kpi__sub">Not yet reached HR</div>
        </div>
        <div class="ap-kpi">
            <div class="ap-kpi__bar" style="background:#16a34a;"></div>
            <div class="ap-kpi__val"><?php echo $cnt_approved; ?></div>
            <div class="ap-kpi__lbl">Fully Approved</div>
            <div class="ap-kpi__sub">All levels signed off</div>
        </div>
        <div class="ap-kpi">
            <div class="ap-kpi__bar" style="background:#dc2626;"></div>
            <div class="ap-kpi__val"><?php echo $cnt_rejected; ?></div>
            <div class="ap-kpi__lbl">Rejected</div>
            <div class="ap-kpi__sub">At any approval level</div>
        </div>
    </div>

    <div class="ap-body">

        <!-- Main -->
        <div class="ap-main">

            <?php if (!empty($pending)): ?>
            <div style="background:linear-gradient(135deg,#faf5ff,#ede9fe);border:1px solid #e9d5ff;border-radius:10px;
                        padding:12px 16px;margin-bottom:16px;font-size:13px;color:#581c87;display:flex;align-items:center;gap:10px;">
                <span class="material-symbols-outlined" style="font-size:22px;color:#9333ea;">admin_panel_settings</span>
                <span>You are the <strong>final approver</strong>. Fully approving a request will mark it confirmed and deduct from the employee's leave balance.</span>
            </div>
            <?php endif; ?>

            <?php if (empty($pending)): ?>
            <div style="background:#fff;border-radius:14px;padding:54px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:52px;color:#d1d5db;display:block;margin-bottom:12px;">verified</span>
                <div style="font-size:16px;font-weight:700;color:#374151;margin-bottom:6px;">All caught up!</div>
                <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">No leave requests pending HR final approval.</div>
                <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;padding:14px 18px;text-align:left;max-width:480px;margin:0 auto;font-size:13px;color:#581c87;">
                    <strong>Requests appear here when:</strong><br>
                    1. An employee submits a leave request<br>
                    2. Their manager approves it at <a href="<?php echo $base; ?>/leave/approvals" style="color:#7c3aed;font-weight:700;">Manager Approvals</a><br>
                    3. The request then appears here for HR final sign-off
                    <?php if ($cnt_pending_mgr > 0): ?>
                    <div style="margin-top:10px;padding:8px 12px;background:#fffbeb;border-radius:7px;color:#92400e;">
                        <strong><?php echo $cnt_pending_mgr; ?> request<?php echo $cnt_pending_mgr!=1?'s':''; ?></strong> waiting at the Manager level right now.
                        <a href="<?php echo $base; ?>/leave/approvals" style="color:#d97706;font-weight:700;">View →</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <?php
            $av = ['#2563eb','#16a34a','#d97706','#9333ea','#e11d48','#0891b2'];
            $ci = 0;
            foreach ($pending as $req):
                $ac  = $av[$ci++ % count($av)];
                $nm  = $req->employee_name ?? 'Unknown';
                $ini = strtoupper(substr($nm,0,1)).strtoupper(substr(strstr($nm,' '),1,1)?:'?');
                $col = $req->type_color ?? '#9333ea';
                $hex = ltrim($col,'#');
                $r2=hexdec(substr($hex,0,2));$g2=hexdec(substr($hex,2,2));$b2=hexdec(substr($hex,4,2));
                $bg2 = "rgba($r2,$g2,$b2,.1)";
                $dtxt = number_format($req->total_days,1).($req->total_days==1?' day':' days');
                $alloc_rem = max(0, (float)($req->alloc_total??0) - (float)($req->alloc_used??0));
            ?>
            <div class="ap-card">
                <div class="ap-card__head">
                    <div class="ap-avatar" style="background:<?php echo $ac; ?>"><?php echo $ini; ?></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($nm); ?></div>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                            <?php echo htmlspecialchars($req->department_name??''); ?>
                            <?php if (!empty($req->designation_name)): ?> · <?php echo htmlspecialchars($req->designation_name); ?><?php endif; ?>
                        </div>
                        <div style="margin-top:7px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:6px;
                                         font-size:11px;font-weight:700;background:<?php echo $bg2; ?>;color:<?php echo $col; ?>;">
                                <span class="material-symbols-outlined" style="font-size:12px;">beach_access</span>
                                <?php echo htmlspecialchars($req->leave_type_name??''); ?>
                            </span>
                            <span style="font-size:10px;background:#faf5ff;color:#9333ea;padding:2px 8px;border-radius:999px;font-weight:700;">
                                Manager Approved
                            </span>
                            <?php if ($req->half_day): ?>
                            <span style="font-size:10px;background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:999px;font-weight:700;">Half Day</span>
                            <?php endif; ?>
                            <?php if (empty($req->leave_is_paid)): ?>
                            <span style="font-size:10px;background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:999px;font-weight:700;">Unpaid</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="font-size:11px;color:#9ca3af;white-space:nowrap;flex-shrink:0;">
                        Applied <?php echo date('d M Y', strtotime($req->date_created)); ?>
                    </div>
                </div>
                <div class="ap-card__body">
                    <div class="ap-info-grid">
                        <div class="ap-info-box">
                            <div class="ap-info-box__lbl">From</div>
                            <div class="ap-info-box__val"><?php echo date('d M Y', strtotime($req->date_from)); ?></div>
                        </div>
                        <div class="ap-info-box">
                            <div class="ap-info-box__lbl">To</div>
                            <div class="ap-info-box__val"><?php echo date('d M Y', strtotime($req->date_to)); ?></div>
                        </div>
                        <div class="ap-info-box">
                            <div class="ap-info-box__lbl">Duration</div>
                            <div class="ap-info-box__val" style="color:#9333ea;"><?php echo $dtxt; ?></div>
                        </div>
                    </div>

                    <?php if ($req->reason): ?>
                    <div style="margin-top:10px;padding:10px 12px;background:#f9fafb;border-radius:8px;font-size:12px;color:#374151;border-left:3px solid #e9d5ff;">
                        <span style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Reason: </span>
                        <?php echo htmlspecialchars($req->reason); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (($req->alloc_total??0) > 0): ?>
                    <div style="margin-top:10px;display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                        <span class="material-symbols-outlined" style="font-size:14px;">account_balance_wallet</span>
                        Balance: <strong style="color:<?php echo $alloc_rem>=$req->total_days?'#16a34a':'#dc2626'; ?>">
                            <?php echo number_format($alloc_rem,1); ?> days remaining
                        </strong>
                        <?php if ($alloc_rem < $req->total_days): ?>
                        <span style="color:#dc2626;font-weight:700;">⚠ Insufficient balance</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($req->manager_comment)): ?>
                    <div style="margin-top:8px;padding:8px 12px;background:#f0fdf4;border-radius:8px;font-size:12px;color:#166534;border-left:3px solid #bbf7d0;">
                        <span style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Manager note: </span>
                        <?php echo htmlspecialchars($req->manager_comment); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($req->handover_employee_name)): ?>
                    <div style="margin-top:7px;font-size:11px;color:#374151;display:flex;align-items:center;gap:5px;">
                        <span class="material-symbols-outlined" style="font-size:13px;color:#6b7280;">handshake</span>
                        Handover to: <strong><?php echo htmlspecialchars($req->handover_employee_name); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ap-card__foot">
                    <form method="post" action="<?php echo $base; ?>/leave/hr_approvals/approve/<?php echo $req->id; ?>" style="display:contents;">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <button type="submit" class="ap-btn-approve">
                            <span class="material-symbols-outlined" style="font-size:15px;">verified</span>
                            Fully Approve
                        </button>
                    </form>
                    <button type="button" class="ap-btn-reject" onclick="showReject(<?php echo $req->id; ?>)">
                        <span class="material-symbols-outlined" style="font-size:15px;">cancel</span>
                        Reject
                    </button>
                    <a href="<?php echo $base; ?>/leave/approvals" class="ap-btn-link">View Manager Queue →</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="ap-side">

            <!-- Workflow (HR step highlighted) -->
            <div class="sb">
                <div class="sb__head">Approval Workflow</div>
                <div class="sb__body" style="padding:14px;">
                    <?php
                    $steps = [
                        ['Employee submits request',    '#6b7280', 'edit_note'],
                        ['Manager approves',            '#d97706', 'supervisor_account'],
                        ['HR gives final approval',    '#9333ea', 'admin_panel_settings', true],
                        ['Leave confirmed, employee notified', '#16a34a', 'notifications_active'],
                    ];
                    foreach ($steps as $i => $step):
                        [$label, $color, $icon] = $step;
                        $active = $step[3] ?? false; ?>
                    <div style="display:flex;gap:10px;margin-bottom:<?php echo $i<count($steps)-1?'16':'0'; ?>px;align-items:flex-start;">
                        <div style="width:30px;height:30px;border-radius:8px;background:<?php echo $active?$color:'#f3f4f6'; ?>;
                                    color:<?php echo $active?'#fff':'#9ca3af'; ?>;display:flex;align-items:center;
                                    justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="font-size:16px;"><?php echo $icon; ?></span>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:<?php echo $active?700:600; ?>;color:<?php echo $active?$color:'#374151'; ?>;">
                                <?php echo $label; ?>
                            </div>
                            <?php if ($active): ?>
                            <div style="font-size:10px;color:#9ca3af;">← You are here</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="sb">
                <div class="sb__head">All Leave Requests</div>
                <div class="sb__body" style="padding:0 14px;">
                    <?php
                    $statuses = [
                        'pending_manager' => ['Awaiting Manager', '#d97706'],
                        'pending_hr'      => ['Awaiting HR',      '#9333ea'],
                        'approved'        => ['Approved',         '#16a34a'],
                        'rejected'        => ['Rejected',         '#dc2626'],
                        'cancelled'       => ['Cancelled',        '#6b7280'],
                    ];
                    foreach ($statuses as $key => [$lbl, $col]):
                        $n = $counts[$key] ?? 0;
                    ?>
                    <div class="sb-row">
                        <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $col; ?>;display:inline-block;"></span>
                            <?php echo $lbl; ?>
                        </span>
                        <span style="font-weight:800;font-size:13px;color:<?php echo $n>0?$col:'#9ca3af'; ?>;"><?php echo $n; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="sb">
                <div class="sb__head">Quick Actions</div>
                <div class="sb__body">
                    <a href="<?php echo $base; ?>/leave/approvals" class="sb-action">
                        <span class="material-symbols-outlined">supervisor_account</span>Manager Queue
                        <?php if ($cnt_pending_mgr>0): ?><span style="margin-left:auto;background:#d97706;color:#fff;border-radius:999px;font-size:10px;padding:1px 7px;font-weight:700;"><?php echo $cnt_pending_mgr; ?></span><?php endif; ?>
                    </a>
                    <a href="<?php echo $base; ?>/leave/allocations" class="sb-action">
                        <span class="material-symbols-outlined">playlist_add_check</span>Leave Allocations
                    </a>
                    <a href="<?php echo $base; ?>/leave/config/policies" class="sb-action">
                        <span class="material-symbols-outlined">policy</span>Leave Policies
                    </a>
                    <a href="<?php echo $base; ?>/leave/toil" class="sb-action">
                        <span class="material-symbols-outlined">swap_horiz</span>TOIL Entries
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Reject modal -->
<div id="reject-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:440px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:16px;font-weight:700;margin:0 0 14px;color:#111827;">Reject Leave Request (HR Final Decision)</h3>
        <form id="reject-form" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div style="margin-bottom:14px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">
                    HR Rejection Reason <span style="color:#dc2626;">*</span>
                </label>
                <textarea name="rejection_reason" class="form-control" rows="3" required
                          placeholder="Explain why this request is being rejected at HR level…"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeReject()" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm" style="border-radius:6px;">Reject Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReject(id) {
    document.getElementById('reject-form').action = '<?php echo $base; ?>/leave/hr_approvals/reject/' + id;
    document.getElementById('reject-modal').style.display = 'flex';
}
function closeReject() { document.getElementById('reject-modal').style.display = 'none'; }
</script>
<?php init_tail(); ?>
