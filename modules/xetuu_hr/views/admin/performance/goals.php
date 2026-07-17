<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$priority_colors = [
    'Low'      => ['#6b7280','#f3f4f6'],
    'Medium'   => ['#2563eb','#eff6ff'],
    'High'     => ['#ea580c','#fff7ed'],
    'Critical' => ['#dc2626','#fef2f2'],
];
$status_colors = [
    'Draft'     => ['#6b7280','#f3f4f6'],
    'Active'    => ['#2563eb','#eff6ff'],
    'Completed' => ['#16a34a','#f0fdf4'],
    'Cancelled' => ['#6b7280','#f3f4f6'],
    'Overdue'   => ['#dc2626','#fef2f2'],
];
$category_colors = ['Individual'=>'#7c3aed','Team'=>'#2563eb','Company'=>'#16a34a'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-goal-card{background:#fff;border-radius:12px;border:1px solid #f3f4f6;border-left:4px solid var(--gc,#e5e7eb);padding:16px 18px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:box-shadow .15s,transform .15s;}
.pf-goal-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);transform:translateY(-1px);}
.pf-chip{display:inline-block;padding:2px 9px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-progress{height:8px;border-radius:4px;background:#e5e7eb;overflow:hidden;margin-top:6px;}
.pf-progress__fill{height:100%;border-radius:4px;transition:width .5s;}
.pf-two-col{display:grid;grid-template-columns:1fr 300px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-sidebar-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;}
.pf-modal{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;}
.pf-modal.open{display:flex;}
.pf-modal__box{background:#fff;border-radius:16px;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);padding:24px;}
</style>

<div class="pf-page">
    <div class="pf-two-col">
        <!-- Main -->
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:11px;color:#6b7280;"><a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / Goals</div>
                    <h1 style="font-size:20px;font-weight:800;color:#111827;margin:4px 0 0;">Goals &amp; OKRs</h1>
                </div>
                <a href="<?php echo $base; ?>/performance/goals/add" class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;background:#7c3aed;border-color:#7c3aed;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span> New Goal
                </a>
            </div>

            <!-- Filters -->
            <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border-radius:10px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);align-items:center;">
                <select name="employee_id" class="form-control" style="height:32px;font-size:12px;width:180px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp->id; ?>" <?php echo ($filters['employee_id']??'')==$emp->id?'selected':''; ?>>
                        <?php echo htmlspecialchars($emp->full_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-control" style="height:32px;font-size:12px;width:130px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (array_keys($status_colors) as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($filters['status']??'')===$st?'selected':''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="category" class="form-control" style="height:32px;font-size:12px;width:130px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach (['Individual','Team','Company'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo ($filters['category']??'')===$cat?'selected':''; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="?" style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:6px;padding:4px 8px;text-decoration:none;">Clear</a>
                <span style="margin-left:auto;font-size:11px;color:#9ca3af;"><?php echo count($goals); ?> goal<?php echo count($goals)!=1?'s':''; ?></span>
            </form>

            <!-- Goals list -->
            <?php if (empty($goals)): ?>
            <div style="background:#fff;border-radius:14px;padding:48px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:10px;">flag</span>
                <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No goals found</div>
                <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Set OKRs and KPIs to track employee performance.</div>
                <a href="<?php echo $base; ?>/performance/goals/add" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;">Create First Goal</a>
            </div>
            <?php else: ?>
            <?php foreach ($goals as $g):
                $pc = $priority_colors[$g->priority] ?? ['#6b7280','#f3f4f6'];
                $sc = $status_colors[$g->status]    ?? ['#6b7280','#f3f4f6'];
                $cc = $category_colors[$g->category] ?? '#7c3aed';
                $pct = (float)$g->completion_pct;
                $pct_color = $pct >= 75 ? '#16a34a' : ($pct >= 40 ? '#ca8a04' : '#dc2626');
                $is_overdue = $g->due_date && $g->due_date < date('Y-m-d') && $g->status === 'Active';
            ?>
            <div class="pf-goal-card" style="--gc:<?php echo $cc; ?>;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                            <span style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($g->title); ?></span>
                            <span class="pf-chip" style="color:<?php echo $cc; ?>;background:rgba(<?php echo implode(',',sscanf(ltrim($cc,'#'),'%02x%02x%02x')); ?>,.1);"><?php echo $g->category; ?></span>
                            <span class="pf-chip" style="font-size:9px;color:<?php echo $pc[0]; ?>;background:<?php echo $pc[1]; ?>;"><?php echo $g->priority; ?></span>
                            <?php if ($is_overdue): ?>
                            <span class="pf-chip" style="color:#dc2626;background:#fef2f2;">Overdue</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11px;color:#6b7280;display:flex;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
                            <span><span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">person</span> <?php echo htmlspecialchars($g->employee_name ?? '—'); ?></span>
                            <span><?php echo $g->type; ?></span>
                            <?php if ($g->due_date): ?>
                            <span><span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">calendar_today</span> Due <?php echo date('d M Y', strtotime($g->due_date)); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($g->target_value): ?>
                        <div style="font-size:11px;color:#374151;">
                            Progress: <strong><?php echo number_format($g->current_value,2); ?></strong> / <?php echo number_format($g->target_value,2); ?> <?php echo htmlspecialchars($g->unit??''); ?>
                            <strong style="color:<?php echo $pct_color; ?>;margin-left:4px;">(<?php echo number_format($pct,1); ?>%)</strong>
                        </div>
                        <div class="pf-progress" style="width:200px;">
                            <div class="pf-progress__fill" style="width:<?php echo min(100,$pct); ?>%;background:<?php echo $pct_color; ?>;"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $g->status; ?></span>
                        <div style="display:flex;gap:4px;">
                            <?php if ($g->status === 'Active' && $g->target_value): ?>
                            <button onclick="openProgressModal(<?php echo $g->id; ?>, '<?php echo addslashes($g->title); ?>', <?php echo $g->current_value; ?>, <?php echo $g->target_value; ?>, '<?php echo htmlspecialchars($g->unit??''); ?>')"
                                style="background:#7c3aed;color:#fff;border:none;border-radius:6px;padding:4px 8px;font-size:10px;cursor:pointer;font-weight:700;">
                                Update
                            </button>
                            <?php endif; ?>
                            <a href="<?php echo $base; ?>/performance/goals/edit/<?php echo $g->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">Edit</a>
                            <form method="post" action="<?php echo $base; ?>/performance/goals/delete/<?php echo $g->id; ?>" style="display:inline;" onsubmit="return confirm('Delete this goal?')">
                                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                                <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;">Del</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar: Stats -->
        <div>
            <?php
            $by_status = [];
            foreach ($goals as $g) $by_status[$g->status] = ($by_status[$g->status]??0)+1;
            $by_cat = [];
            foreach ($goals as $g) $by_cat[$g->category] = ($by_cat[$g->category]??0)+1;
            $avg_pct = count($goals) ? round(array_sum(array_column((array)$goals,'completion_pct'))/count($goals),1) : 0;
            ?>
            <div class="pf-sidebar-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:16px 20px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-bottom:2px;">Overall Progress</div>
                    <div style="font-size:32px;font-weight:800;color:#fff;"><?php echo $avg_pct; ?>%</div>
                    <div style="height:6px;border-radius:3px;background:rgba(255,255,255,.15);margin-top:8px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo $avg_pct; ?>%;background:#8b5cf6;border-radius:3px;"></div>
                    </div>
                </div>
                <div style="padding:14px 16px;">
                    <?php foreach ($by_status as $st => $cnt):
                        $sc = $status_colors[$st] ?? ['#6b7280','#f3f4f6'];
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:12px;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $st; ?></span>
                        <strong style="color:#111827;"><?php echo $cnt; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pf-sidebar-card">
                <div style="padding:14px 16px;">
                    <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">By Category</div>
                    <?php foreach ($by_cat as $cat => $cnt):
                        $cc2 = $category_colors[$cat] ?? '#7c3aed';
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $cc2; ?>;flex-shrink:0;"></span>
                        <span style="font-size:12px;color:#374151;flex:1;"><?php echo $cat; ?></span>
                        <strong style="font-size:12px;color:<?php echo $cc2; ?>"><?php echo $cnt; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pf-sidebar-card">
                <div style="padding:14px 16px;">
                    <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Quick Links</div>
                    <a href="<?php echo $base; ?>/performance/goals/add" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;color:#111827;font-size:12px;font-weight:600;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size:16px;color:#7c3aed;">add_circle</span> New Goal
                    </a>
                    <a href="<?php echo $base; ?>/performance" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;color:#111827;font-size:12px;font-weight:600;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size:16px;color:#7c3aed;">dashboard</span> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="pf-modal" id="progressModal">
    <div class="pf-modal__box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div style="font-size:16px;font-weight:800;color:#111827;">Update Goal Progress</div>
            <button onclick="document.getElementById('progressModal').classList.remove('open')" style="background:none;border:none;cursor:pointer;color:#6b7280;">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="progressGoalTitle" style="font-size:13px;font-weight:600;color:#374151;margin-bottom:14px;"></div>
        <div id="progressTarget" style="font-size:11px;color:#9ca3af;margin-bottom:12px;"></div>
        <div style="margin-bottom:12px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">New Value <span id="progressUnit"></span></label>
            <input type="number" id="progressNewVal" step="any" class="form-control" placeholder="0">
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:4px;">Note (optional)</label>
            <textarea id="progressNote" class="form-control" rows="2" placeholder="What did you accomplish?"></textarea>
        </div>
        <button onclick="submitProgress()" class="btn btn-primary" style="width:100%;border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;">Save Progress</button>
    </div>
</div>

<script>
var _currentGoalId = null;
function openProgressModal(id, title, current, target, unit) {
    _currentGoalId = id;
    document.getElementById('progressGoalTitle').textContent = title;
    document.getElementById('progressTarget').textContent = 'Target: ' + target + ' ' + unit;
    document.getElementById('progressUnit').textContent = unit ? '('+unit+')' : '';
    document.getElementById('progressNewVal').value = current;
    document.getElementById('progressNote').value = '';
    document.getElementById('progressModal').classList.add('open');
}
function submitProgress() {
    var val  = document.getElementById('progressNewVal').value;
    var note = document.getElementById('progressNote').value;
    if (!val) return;
    fetch('<?php echo $base; ?>/performance/goals/update_progress/' + _currentGoalId, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: '<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>&new_value=' + encodeURIComponent(val) + '&note=' + encodeURIComponent(note)
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); }
    });
}
document.getElementById('progressModal').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
</script>
<?php init_tail(); ?>
