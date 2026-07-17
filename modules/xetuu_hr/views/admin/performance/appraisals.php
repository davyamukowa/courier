<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = [
    'Pending'     => ['#ca8a04','#fef9c3'],
    'In Progress' => ['#2563eb','#eff6ff'],
    'Completed'   => ['#16a34a','#f0fdf4'],
];
$rating_colors = [
    'Outstanding'          => '#16a34a',
    'Exceeds Expectations' => '#2563eb',
    'Meets Expectations'   => '#ca8a04',
    'Below Expectations'   => '#ea580c',
    'Unsatisfactory'       => '#dc2626',
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-appr-card{background:#fff;border-radius:12px;border:1px solid #f3f4f6;border-left:4px solid var(--ac,#e5e7eb);padding:16px 18px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;align-items:center;gap:14px;transition:box-shadow .15s;}
.pf-appr-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
.pf-avatar{width:40px;height:40px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-weight:700;color:#6b7280;flex-shrink:0;font-size:14px;overflow:hidden;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-two-col{display:grid;grid-template-columns:1fr 280px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-sidebar-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;}
</style>
<div class="pf-page">
    <div class="pf-two-col">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:11px;color:#6b7280;"><a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / Appraisals</div>
                    <h1 style="font-size:20px;font-weight:800;color:#111827;margin:4px 0 0;">Appraisals</h1>
                </div>
                <a href="<?php echo $base; ?>/performance/appraisals/add" class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;background:#7c3aed;border-color:#7c3aed;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span> New Appraisal
                </a>
            </div>

            <!-- Filters -->
            <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border-radius:10px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);align-items:center;">
                <select name="cycle_id" class="form-control" style="height:32px;font-size:12px;width:180px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Cycles</option>
                    <?php foreach ($cycles as $cy): ?>
                    <option value="<?php echo $cy->id; ?>" <?php echo ($filters['cycle_id']??'')==$cy->id?'selected':''; ?>>
                        <?php echo htmlspecialchars($cy->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-control" style="height:32px;font-size:12px;width:140px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (array_keys($status_colors) as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($filters['status']??'')===$st?'selected':''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="employee_id" class="form-control" style="height:32px;font-size:12px;width:180px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp->id; ?>" <?php echo ($filters['employee_id']??'')==$emp->id?'selected':''; ?>>
                        <?php echo htmlspecialchars($emp->full_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <a href="?" style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:6px;padding:4px 8px;text-decoration:none;">Clear</a>
                <span style="margin-left:auto;font-size:11px;color:#9ca3af;"><?php echo count($appraisals); ?> appraisal<?php echo count($appraisals)!=1?'s':''; ?></span>
            </form>

            <?php if (empty($appraisals)): ?>
            <div style="background:#fff;border-radius:14px;padding:48px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:10px;">star_rate</span>
                <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No appraisals found</div>
                <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Create appraisal cycles and templates first, then assign appraisals to employees.</div>
                <div style="display:flex;gap:8px;justify-content:center;">
                    <a href="<?php echo $base; ?>/performance/config/cycles" class="btn btn-default" style="border-radius:8px;">Create Cycle</a>
                    <a href="<?php echo $base; ?>/performance/appraisals/add" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;">New Appraisal</a>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($appraisals as $ap):
                $sc = $status_colors[$ap->status] ?? ['#6b7280','#f3f4f6'];
                $rc_col = $rating_colors[$ap->rating??''] ?? '#6b7280';
                $pct = (float)($ap->score ?? 0);
            ?>
            <div class="pf-appr-card" style="--ac:<?php echo $sc[0]; ?>;">
                <?php if ($ap->photo): ?>
                <img src="<?php echo base_url('uploads/staff_profile_images/'.$ap->photo); ?>" class="pf-avatar" style="width:40px;height:40px;object-fit:cover;">
                <?php else: ?>
                <div class="pf-avatar"><?php echo strtoupper(substr($ap->employee_name??'?',0,1)); ?></div>
                <?php endif; ?>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($ap->employee_name??'—'); ?></div>
                    <div style="font-size:11px;color:#6b7280;display:flex;gap:10px;flex-wrap:wrap;margin-top:2px;">
                        <span><?php echo htmlspecialchars($ap->cycle_name??'—'); ?></span>
                        <?php if ($ap->template_name): ?><span>· <?php echo htmlspecialchars($ap->template_name); ?></span><?php endif; ?>
                        <?php if ($ap->reviewer_name): ?><span>· Reviewer: <?php echo htmlspecialchars($ap->reviewer_name); ?></span><?php endif; ?>
                    </div>
                    <?php if ($ap->score !== null): ?>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                        <div style="height:6px;width:140px;border-radius:3px;background:#e5e7eb;overflow:hidden;">
                            <div style="height:100%;width:<?php echo min(100,$pct); ?>%;background:<?php echo $rc_col; ?>;border-radius:3px;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:<?php echo $rc_col; ?>;"><?php echo number_format($pct,1); ?>%</span>
                        <?php if ($ap->rating): ?><span style="font-size:10px;color:<?php echo $rc_col; ?>;font-weight:600;"><?php echo $ap->rating; ?></span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                    <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $ap->status; ?></span>
                    <div style="display:flex;gap:4px;">
                        <?php if ($ap->status !== 'Completed'): ?>
                        <a href="<?php echo $base; ?>/performance/appraisals/score/<?php echo $ap->id; ?>" class="btn btn-xs btn-primary" style="border-radius:4px;font-size:10px;background:#7c3aed;border-color:#7c3aed;">Score</a>
                        <?php endif; ?>
                        <a href="<?php echo $base; ?>/performance/appraisals/view/<?php echo $ap->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">View</a>
                        <form method="post" action="<?php echo $base; ?>/performance/appraisals/delete/<?php echo $ap->id; ?>" style="display:inline;" onsubmit="return confirm('Delete this appraisal?')">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;font-size:10px;">Del</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div>
            <?php
            $by_status = [];
            foreach ($appraisals as $a) $by_status[$a->status] = ($by_status[$a->status]??0)+1;
            $by_rating = [];
            foreach ($appraisals as $a) if ($a->rating) $by_rating[$a->rating] = ($by_rating[$a->rating]??0)+1;
            $completed = array_filter($appraisals, fn($a) => $a->score !== null);
            $avg_score = count($completed) ? round(array_sum(array_column(array_map(fn($a)=>['s'=>(float)$a->score],$completed),'s'))/count($completed),1) : 0;
            ?>
            <div class="pf-sidebar-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:16px 20px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.5);">Average Score</div>
                    <div style="font-size:32px;font-weight:800;color:#fff;"><?php echo $avg_score; ?>%</div>
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

            <?php if (!empty($by_rating)): ?>
            <div class="pf-sidebar-card">
                <div style="padding:14px 16px;">
                    <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Ratings Breakdown</div>
                    <?php foreach ($by_rating as $r => $cnt):
                        $rc = $rating_colors[$r] ?? '#6b7280';
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:7px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $rc; ?>;flex-shrink:0;"></span>
                        <span style="font-size:11px;color:#374151;flex:1;"><?php echo $r; ?></span>
                        <strong style="font-size:11px;color:<?php echo $rc; ?>"><?php echo $cnt; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="pf-sidebar-card">
                <div style="padding:14px 16px;">
                    <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Configuration</div>
                    <a href="<?php echo $base; ?>/performance/config/cycles" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;color:#111827;font-size:12px;font-weight:600;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size:16px;color:#7c3aed;">date_range</span> Appraisal Cycles
                    </a>
                    <a href="<?php echo $base; ?>/performance/config/templates" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;text-decoration:none;color:#111827;font-size:12px;font-weight:600;" onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size:16px;color:#7c3aed;">description</span> Templates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
