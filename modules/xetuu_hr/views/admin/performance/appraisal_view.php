<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$ap   = $appraisal;
$rating_colors = [
    'Outstanding'          => ['#16a34a','#f0fdf4'],
    'Exceeds Expectations' => ['#2563eb','#eff6ff'],
    'Meets Expectations'   => ['#ca8a04','#fef9c3'],
    'Below Expectations'   => ['#ea580c','#fff7ed'],
    'Unsatisfactory'       => ['#dc2626','#fef2f2'],
];
$rc = $rating_colors[$ap->rating??''] ?? ['#6b7280','#f3f4f6'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-two-col{display:grid;grid-template-columns:1fr 280px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px;}
.pf-criteria-row{padding:16px 24px;border-bottom:1px solid #f3f4f6;}
.pf-criteria-row:last-child{border-bottom:none;}
.pf-star-display{display:flex;gap:4px;}
.pf-star-display span{font-size:18px;color:#d1d5db;}
.pf-star-display span.on{color:#f59e0b;}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> /
        <a href="<?php echo $base; ?>/performance/appraisals" style="color:#6b7280;text-decoration:none;">Appraisals</a> / View
    </div>
    <div class="pf-two-col">
        <div>
            <!-- Hero -->
            <div class="pf-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:24px;">
                    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;color:#fff;flex-shrink:0;">
                            <?php echo strtoupper(substr($ap->employee_name??'?',0,1)); ?>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:20px;font-weight:800;color:#fff;"><?php echo htmlspecialchars($ap->employee_name??''); ?></div>
                            <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:2px;">
                                <?php echo htmlspecialchars($ap->designation_name??''); ?>
                                <?php if ($ap->department_name): ?> · <?php echo htmlspecialchars($ap->department_name); ?><?php endif; ?>
                            </div>
                            <div style="font-size:12px;color:rgba(255,255,255,.5);"><?php echo htmlspecialchars($ap->cycle_name??''); ?></div>
                        </div>
                        <?php if ($ap->score !== null): ?>
                        <div style="text-align:center;background:rgba(255,255,255,.08);border-radius:12px;padding:14px 20px;">
                            <div style="font-size:36px;font-weight:900;color:#fff;"><?php echo number_format($ap->score,1); ?>%</div>
                            <?php if ($ap->rating): ?>
                            <div style="font-size:11px;font-weight:700;color:<?php echo $rc[0]; ?>;margin-top:4px;background:<?php echo $rc[1]; ?>;padding:3px 10px;border-radius:999px;"><?php echo $ap->rating; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="padding:16px 24px;display:flex;gap:8px;flex-wrap:wrap;">
                    <?php if ($ap->status !== 'Completed'): ?>
                    <a href="<?php echo $base; ?>/performance/appraisals/score/<?php echo $ap->id; ?>" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;display:flex;align-items:center;gap:6px;">
                        <span class="material-symbols-outlined" style="font-size:15px;">edit</span> Edit Scores
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo $base; ?>/performance/appraisals" class="btn btn-default" style="border-radius:8px;">Back to List</a>
                    <form method="post" action="<?php echo $base; ?>/performance/appraisals/delete/<?php echo $ap->id; ?>" style="display:inline;" onsubmit="return confirm('Delete this appraisal?')">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <button type="submit" class="btn btn-danger" style="border-radius:8px;">Delete</button>
                    </form>
                </div>
            </div>

            <!-- Scores -->
            <?php if (!empty($ap->scores)): ?>
            <div class="pf-card">
                <div style="padding:16px 24px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:700;color:#111827;">Scoring Detail</div>
                <?php
                $grouped = [];
                foreach ($ap->scores as $s) $grouped[$s->category ?: 'General'][] = $s;
                ?>
                <?php foreach ($grouped as $cat => $cat_scores): ?>
                <div style="padding:10px 24px 4px;background:#f9fafb;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:10px;font-weight:800;color:#7c3aed;text-transform:uppercase;"><?php echo htmlspecialchars($cat); ?></span>
                </div>
                <?php foreach ($cat_scores as $s):
                    $final = $s->final_score ?? $s->manager_score ?? $s->self_score ?? 0;
                    $max   = (float)($s->max_score ?: 5);
                    $pct_s = $max > 0 ? round(($final/$max)*100) : 0;
                ?>
                <div class="pf-criteria-row">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($s->criteria_name??''); ?></div>
                            <?php if ($s->criteria_description): ?>
                            <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($s->criteria_description); ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;">Weight: <strong style="color:#7c3aed;"><?php echo $s->weight; ?>%</strong></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:10px;">
                        <div>
                            <div style="font-size:10px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase;">Self</div>
                            <div class="pf-star-display">
                                <?php for ($i=1;$i<=$max;$i++): ?><span class="<?php echo ($s->self_score??0)>=$i?'on':''; ?>">★</span><?php endfor; ?>
                                <span style="font-size:11px;color:#374151;margin-left:4px;font-weight:600;"><?php echo $s->self_score??'—'; ?>/<?php echo $max; ?></span>
                            </div>
                            <?php if ($s->self_comment): ?>
                            <div style="font-size:11px;color:#374151;margin-top:4px;background:#f9fafb;padding:6px 10px;border-radius:6px;"><?php echo htmlspecialchars($s->self_comment); ?></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-size:10px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase;">Manager</div>
                            <div class="pf-star-display">
                                <?php for ($i=1;$i<=$max;$i++): ?><span class="<?php echo ($s->manager_score??0)>=$i?'on':''; ?>">★</span><?php endfor; ?>
                                <span style="font-size:11px;color:#374151;margin-left:4px;font-weight:600;"><?php echo $s->manager_score??'—'; ?>/<?php echo $max; ?></span>
                            </div>
                            <?php if ($s->manager_comment): ?>
                            <div style="font-size:11px;color:#374151;margin-top:4px;background:#f9fafb;padding:6px 10px;border-radius:6px;"><?php echo htmlspecialchars($s->manager_comment); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="margin-top:8px;">
                        <div style="height:4px;border-radius:2px;background:#e5e7eb;overflow:hidden;width:200px;">
                            <div style="height:100%;width:<?php echo $pct_s; ?>%;background:#7c3aed;border-radius:2px;"></div>
                        </div>
                        <div style="font-size:10px;color:#7c3aed;font-weight:700;margin-top:2px;">Final: <?php echo $final; ?>/<?php echo $max; ?> (<?php echo $pct_s; ?>%)</div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($ap->comments): ?>
            <div class="pf-card" style="padding:20px 24px;">
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:8px;">Overall Comments</div>
                <div style="font-size:13px;color:#374151;line-height:1.6;"><?php echo nl2br(htmlspecialchars($ap->comments)); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:16px;">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Details</div>
                <div style="font-size:12px;color:#6b7280;line-height:2;">
                    <div><strong style="color:#374151;">Reviewer:</strong> <?php echo htmlspecialchars($ap->reviewer_name??'N/A'); ?></div>
                    <div><strong style="color:#374151;">Template:</strong> <?php echo htmlspecialchars($ap->template_name??'None'); ?></div>
                    <?php if ($ap->cycle_start): ?>
                    <div><strong style="color:#374151;">Period:</strong><br><?php echo date('d M Y',strtotime($ap->cycle_start)); ?> – <?php echo date('d M Y',strtotime($ap->cycle_end)); ?></div>
                    <?php endif; ?>
                    <div><strong style="color:#374151;">Created:</strong> <?php echo date('d M Y',strtotime($ap->date_created)); ?></div>
                </div>
            </div>
            <?php if ($ap->score !== null):
                $scale = ['Outstanding'=>[90,'#16a34a'],'Exceeds Expectations'=>[75,'#2563eb'],'Meets Expectations'=>[60,'#ca8a04'],'Below Expectations'=>[40,'#ea580c'],'Unsatisfactory'=>[0,'#dc2626']];
            ?>
            <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Rating Scale</div>
                <?php foreach ($scale as $r=>[$min,$col]): ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:5px 0;border-bottom:1px solid #f3f4f6;<?php echo $ap->rating===$r?'background:#f5f3ff;margin:0 -8px;padding:5px 8px;border-radius:6px;':''; ?>">
                    <span style="color:<?php echo $col; ?>;font-weight:<?php echo $ap->rating===$r?800:600; ?>;"><?php echo $r; ?></span>
                    <span style="color:#9ca3af;">&ge;<?php echo $min; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
