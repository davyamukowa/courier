<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$s    = $stats;
$cycle = $s['active_cycle'];

$rating_colors = [
    'Outstanding'          => ['#16a34a','#f0fdf4'],
    'Exceeds Expectations' => ['#2563eb','#eff6ff'],
    'Meets Expectations'   => ['#ca8a04','#fef9c3'],
    'Below Expectations'   => ['#ea580c','#fff7ed'],
    'Unsatisfactory'       => ['#dc2626','#fef2f2'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
.pf-kpi{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.05);border-top:3px solid var(--kc,#e5e7eb);}
.pf-kpi__val{font-size:28px;font-weight:800;color:var(--kc,#111827);line-height:1;}
.pf-kpi__label{font-size:11px;color:#6b7280;margin-top:4px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
.pf-two-col{display:grid;grid-template-columns:1fr 340px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;}
.pf-card__head{padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;}
.pf-card__title{font-size:14px;font-weight:700;color:#111827;}
.pf-appr-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f9fafb;}
.pf-appr-row:last-child{border-bottom:none;}
.pf-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#6b7280;flex-shrink:0;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-progress-bar{height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden;}
.pf-progress-bar__fill{height:100%;background:linear-gradient(90deg,#7c3aed,#2563eb);border-radius:3px;}
</style>

<div class="pf-page">

    <!-- Breadcrumb + Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:11px;color:#6b7280;margin-bottom:4px;">HR / Performance</div>
            <h1 style="font-size:20px;font-weight:800;color:#111827;margin:0;">Performance Dashboard</h1>
            <?php if ($cycle): ?>
            <div style="font-size:12px;color:#7c3aed;margin-top:3px;font-weight:600;">
                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;">radio_button_checked</span>
                Active cycle: <?php echo htmlspecialchars($cycle->name); ?>
                (<?php echo date('d M Y', strtotime($cycle->start_date)); ?> – <?php echo date('d M Y', strtotime($cycle->end_date)); ?>)
            </div>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="<?php echo $base; ?>/performance/goals/add" class="btn btn-default" style="border-radius:8px;font-size:12px;display:flex;align-items:center;gap:5px;font-weight:600;">
                <span class="material-symbols-outlined" style="font-size:15px;">flag</span> New Goal
            </a>
            <a href="<?php echo $base; ?>/performance/appraisals/add" class="btn btn-primary" style="border-radius:8px;font-size:12px;display:flex;align-items:center;gap:5px;font-weight:700;background:#7c3aed;border-color:#7c3aed;">
                <span class="material-symbols-outlined" style="font-size:15px;">add</span> New Appraisal
            </a>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="pf-kpi-grid">
        <div class="pf-kpi" style="--kc:#7c3aed;">
            <div class="pf-kpi__val"><?php echo $s['total_appraisals']; ?></div>
            <div class="pf-kpi__label">Total Appraisals</div>
        </div>
        <div class="pf-kpi" style="--kc:#ca8a04;">
            <div class="pf-kpi__val"><?php echo $s['pending_appraisals']; ?></div>
            <div class="pf-kpi__label">Pending</div>
        </div>
        <div class="pf-kpi" style="--kc:#16a34a;">
            <div class="pf-kpi__val"><?php echo $s['completed_appraisals']; ?></div>
            <div class="pf-kpi__label">Completed</div>
        </div>
        <div class="pf-kpi" style="--kc:#2563eb;">
            <div class="pf-kpi__val"><?php echo $s['active_goals']; ?></div>
            <div class="pf-kpi__label">Active Goals</div>
        </div>
        <div class="pf-kpi" style="--kc:#059669;">
            <div class="pf-kpi__val"><?php echo $s['completed_goals']; ?></div>
            <div class="pf-kpi__label">Goals Done</div>
        </div>
        <div class="pf-kpi" style="--kc:#dc2626;">
            <div class="pf-kpi__val"><?php echo $s['overdue_goals']; ?></div>
            <div class="pf-kpi__label">Overdue Goals</div>
        </div>
        <div class="pf-kpi" style="--kc:#9333ea;">
            <div class="pf-kpi__val"><?php echo $s['feedback_pending']; ?></div>
            <div class="pf-kpi__label">Feedback Sent</div>
        </div>
        <div class="pf-kpi" style="--kc:#0891b2;">
            <div class="pf-kpi__val"><?php echo $s['promotions_pending']; ?></div>
            <div class="pf-kpi__label">Promotions Queued</div>
        </div>
    </div>

    <div class="pf-two-col">

        <!-- Left: Recent Appraisals -->
        <div>
            <div class="pf-card" style="margin-bottom:20px;">
                <div class="pf-card__head">
                    <span class="pf-card__title">Recent Appraisals</span>
                    <a href="<?php echo $base; ?>/performance/appraisals" style="font-size:11px;color:#7c3aed;font-weight:600;text-decoration:none;">View all</a>
                </div>
                <?php if (empty($s['recent_appraisals'])): ?>
                <div style="padding:32px;text-align:center;">
                    <span class="material-symbols-outlined" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:8px;">star_rate</span>
                    <div style="font-size:13px;color:#9ca3af;">No appraisals yet.</div>
                    <a href="<?php echo $base; ?>/performance/appraisals/add" class="btn btn-primary btn-sm" style="margin-top:10px;border-radius:8px;background:#7c3aed;border-color:#7c3aed;">Start First Appraisal</a>
                </div>
                <?php else: ?>
                <?php foreach ($s['recent_appraisals'] as $ap):
                    $status_map = [
                        'Pending'     => ['#ca8a04','#fef9c3'],
                        'In Progress' => ['#2563eb','#eff6ff'],
                        'Completed'   => ['#16a34a','#f0fdf4'],
                    ];
                    $sc = $status_map[$ap->status] ?? ['#6b7280','#f3f4f6'];
                    $rc = $rating_colors[$ap->rating ?? ''] ?? ['#6b7280','#f3f4f6'];
                ?>
                <div class="pf-appr-row">
                    <?php if ($ap->photo): ?>
                    <img src="<?php echo base_url('uploads/staff_profile_images/'.$ap->photo); ?>" class="pf-avatar">
                    <?php else: ?>
                    <div class="pf-avatar"><?php echo strtoupper(substr($ap->employee_name??'?',0,1)); ?></div>
                    <?php endif; ?>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($ap->employee_name ?? '—'); ?>
                        </div>
                        <div style="font-size:11px;color:#6b7280;">
                            <?php echo htmlspecialchars($ap->cycle_name ?? '—'); ?>
                            <?php if ($ap->reviewer_name): ?> · Reviewer: <?php echo htmlspecialchars($ap->reviewer_name); ?><?php endif; ?>
                        </div>
                        <?php if ($ap->score !== null): ?>
                        <div style="margin-top:4px;">
                            <div class="pf-progress-bar" style="width:120px;">
                                <div class="pf-progress-bar__fill" style="width:<?php echo min(100,(float)$ap->score); ?>%;"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $ap->status; ?></span>
                        <?php if ($ap->score !== null): ?>
                        <div style="font-size:12px;font-weight:800;color:<?php echo $rc[0]; ?>;margin-top:3px;"><?php echo number_format($ap->score,1); ?>%</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- No cycle warning -->
            <?php if (!$cycle): ?>
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:12px;">
                <span class="material-symbols-outlined" style="color:#ca8a04;font-size:20px;">warning</span>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#713f12;">No Active Appraisal Cycle</div>
                    <div style="font-size:12px;color:#854d0e;">Create a cycle to start running appraisals.</div>
                </div>
                <a href="<?php echo $base; ?>/performance/config/cycles" class="btn btn-xs" style="margin-left:auto;background:#ca8a04;color:#fff;border-radius:6px;border:none;font-size:11px;font-weight:700;">Create Cycle</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar: Analytics -->
        <div>
            <!-- Rating Distribution -->
            <div class="pf-card" style="margin-bottom:16px;">
                <div class="pf-card__head">
                    <span class="pf-card__title">Rating Distribution</span>
                </div>
                <div style="padding:16px;">
                    <?php
                    $ratings = ['Outstanding','Exceeds Expectations','Meets Expectations','Below Expectations','Unsatisfactory'];
                    $total_rated = array_sum($s['rating_dist']);
                    if ($total_rated === 0): ?>
                    <div style="text-align:center;padding:16px;color:#9ca3af;font-size:12px;">No completed appraisals yet.</div>
                    <?php else:
                    foreach ($ratings as $r):
                        $cnt = $s['rating_dist'][$r] ?? 0;
                        $pct = $total_rated > 0 ? round(($cnt/$total_rated)*100) : 0;
                        $rc  = $rating_colors[$r];
                    ?>
                    <div style="margin-bottom:10px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px;">
                            <span style="font-weight:600;color:#374151;"><?php echo $r; ?></span>
                            <span style="color:#6b7280;"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                        </div>
                        <div class="pf-progress-bar">
                            <div style="height:100%;border-radius:3px;background:<?php echo $rc[0]; ?>;width:<?php echo $pct; ?>%;transition:width .4s;"></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="pf-card" style="margin-bottom:16px;">
                <div class="pf-card__head"><span class="pf-card__title">Quick Actions</span></div>
                <div style="padding:8px;">
                    <?php
                    $links = [
                        [$base.'/performance/appraisals/add','add_circle','New Appraisal','#7c3aed','#f5f3ff'],
                        [$base.'/performance/goals/add','flag','New Goal','#ca8a04','#fef9c3'],
                        [$base.'/performance/feedback/add','360','New 360° Feedback','#2563eb','#eff6ff'],
                        [$base.'/performance/promotions/add','trending_up','Record Promotion','#16a34a','#f0fdf4'],
                        [$base.'/performance/config/templates','description','Manage Templates','#6b7280','#f3f4f6'],
                        [$base.'/performance/config/cycles','date_range','Manage Cycles','#0891b2','#ecfeff'],
                    ];
                    foreach ($links as [$url,$icon,$label,$col,$bg]): ?>
                    <a href="<?php echo $url; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;text-decoration:none;color:#111827;transition:background .15s;"
                       onmouseover="this.style.background='<?php echo $bg; ?>'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size:18px;color:<?php echo $col; ?>;"><?php echo $icon; ?></span>
                        <span style="font-size:13px;font-weight:600;"><?php echo $label; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Goals Summary -->
            <div class="pf-card">
                <div class="pf-card__head">
                    <span class="pf-card__title">Goals Summary</span>
                    <a href="<?php echo $base; ?>/performance/goals" style="font-size:11px;color:#7c3aed;font-weight:600;text-decoration:none;">View all</a>
                </div>
                <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <?php
                    $goal_stats = [
                        ['Active',   $s['active_goals'],    '#2563eb','#eff6ff'],
                        ['Done',     $s['completed_goals'], '#16a34a','#f0fdf4'],
                        ['Overdue',  $s['overdue_goals'],   '#dc2626','#fef2f2'],
                    ];
                    foreach ($goal_stats as [$lbl,$cnt,$col,$bg]): ?>
                    <div style="background:<?php echo $bg; ?>;border-radius:10px;padding:12px;text-align:center;">
                        <div style="font-size:22px;font-weight:800;color:<?php echo $col; ?>;"><?php echo $cnt; ?></div>
                        <div style="font-size:10px;color:<?php echo $col; ?>;font-weight:600;text-transform:uppercase;"><?php echo $lbl; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
