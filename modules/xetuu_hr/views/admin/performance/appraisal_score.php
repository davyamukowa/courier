<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$ap   = $appraisal;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-score-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:20px;}
.pf-score-head{background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:20px 24px;}
.pf-criteria-row{padding:20px 24px;border-bottom:1px solid #f3f4f6;}
.pf-criteria-row:last-child{border-bottom:none;}
.pf-star-row{display:flex;gap:6px;align-items:center;}
.pf-star{cursor:pointer;font-size:24px;color:#d1d5db;transition:color .15s;}
.pf-star.on{color:#f59e0b;}
.pf-two-col{display:grid;grid-template-columns:1fr 260px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-sidebar-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;padding:16px;}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> /
        <a href="<?php echo $base; ?>/performance/appraisals" style="color:#6b7280;text-decoration:none;">Appraisals</a> / Score
    </div>

    <div class="pf-two-col">
        <div>
            <form method="post" action="<?php echo $base; ?>/performance/appraisals/score/<?php echo $ap->id; ?>">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="pf-score-card">
                    <div class="pf-score-head">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0;">
                                <?php echo strtoupper(substr($ap->employee_name??'?',0,1)); ?>
                            </div>
                            <div>
                                <div style="font-size:18px;font-weight:800;color:#fff;"><?php echo htmlspecialchars($ap->employee_name??''); ?></div>
                                <div style="font-size:12px;color:rgba(255,255,255,.6);"><?php echo htmlspecialchars($ap->cycle_name??''); ?> · <?php echo htmlspecialchars($ap->template_name ?? 'Free-form'); ?></div>
                                <?php if ($ap->reviewer_name): ?>
                                <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;">Reviewer: <?php echo htmlspecialchars($ap->reviewer_name); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($criteria)): ?>
                    <div style="padding:32px;text-align:center;">
                        <span class="material-symbols-outlined" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:8px;">quiz</span>
                        <div style="font-size:13px;color:#6b7280;">No scoring template attached. <a href="<?php echo $base; ?>/performance/config/templates" style="color:#7c3aed;font-weight:700;">Create a template</a> to enable weighted scoring.</div>
                    </div>
                    <?php else: ?>
                    <?php
                    $grouped = [];
                    foreach ($criteria as $c) $grouped[$c->category ?: 'General'][] = $c;
                    $existing_scores = [];
                    foreach ($ap->scores as $s) $existing_scores[$s->criteria_id] = $s;
                    ?>
                    <?php foreach ($grouped as $cat_name => $cat_criteria): ?>
                    <div style="padding:16px 24px 4px;background:#f9fafb;border-bottom:1px solid #f3f4f6;">
                        <div style="font-size:10px;font-weight:800;color:#7c3aed;text-transform:uppercase;letter-spacing:.05em;"><?php echo htmlspecialchars($cat_name); ?></div>
                    </div>
                    <?php foreach ($cat_criteria as $c):
                        $ex = $existing_scores[$c->id] ?? null;
                        $max = (float)($c->max_score ?: 5);
                    ?>
                    <div class="pf-criteria-row">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                            <div>
                                <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($c->name); ?></div>
                                <?php if ($c->description): ?>
                                <div style="font-size:11px;color:#6b7280;margin-top:2px;"><?php echo htmlspecialchars($c->description); ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:10px;color:#9ca3af;text-align:right;">
                                Weight: <strong style="color:#7c3aed;"><?php echo $c->weight; ?>%</strong><br>
                                Max: <strong><?php echo $max; ?></strong>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Self Score</div>
                                <div class="pf-star-row" data-field="scores[<?php echo $c->id; ?>][self_score]" data-max="<?php echo $max; ?>">
                                    <?php for ($i=1; $i<=$max; $i++): ?>
                                    <span class="pf-star <?php echo (($ex->self_score??0)>=$i)?'on':''; ?>" data-val="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="scores[<?php echo $c->id; ?>][self_score]" value="<?php echo $ex->self_score??''; ?>">
                                <textarea name="scores[<?php echo $c->id; ?>][self_comment]" class="form-control" rows="2" placeholder="Self assessment comment..." style="margin-top:8px;font-size:12px;border-radius:6px;"><?php echo htmlspecialchars($ex->self_comment??''); ?></textarea>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Manager Score</div>
                                <div class="pf-star-row" data-field="scores[<?php echo $c->id; ?>][manager_score]" data-max="<?php echo $max; ?>">
                                    <?php for ($i=1; $i<=$max; $i++): ?>
                                    <span class="pf-star <?php echo (($ex->manager_score??0)>=$i)?'on':''; ?>" data-val="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="scores[<?php echo $c->id; ?>][manager_score]" value="<?php echo $ex->manager_score??''; ?>">
                                <textarea name="scores[<?php echo $c->id; ?>][manager_comment]" class="form-control" rows="2" placeholder="Manager feedback..." style="margin-top:8px;font-size:12px;border-radius:6px;"><?php echo htmlspecialchars($ex->manager_comment??''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Overall comments + submit -->
                <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                    <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:10px;">Overall Comments</div>
                    <textarea name="comments" class="form-control" rows="3" placeholder="General observations and recommendations..."><?php echo htmlspecialchars($ap->comments??''); ?></textarea>
                    <div style="display:flex;gap:10px;margin-top:14px;align-items:center;">
                        <button type="submit" name="save_draft" class="btn btn-default" style="border-radius:8px;font-weight:600;">Save Draft</button>
                        <button type="submit" name="submit_final" value="1" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;">
                            Complete Appraisal &amp; Compute Score
                        </button>
                        <a href="<?php echo $base; ?>/performance/appraisals" class="btn btn-link" style="color:#6b7280;">Cancel</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="pf-sidebar-card">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Appraisal Info</div>
                <div style="font-size:12px;color:#6b7280;line-height:1.9;">
                    <div><strong style="color:#374151;">Employee:</strong> <?php echo htmlspecialchars($ap->employee_name??'—'); ?></div>
                    <div><strong style="color:#374151;">Cycle:</strong> <?php echo htmlspecialchars($ap->cycle_name??'—'); ?></div>
                    <?php if ($ap->cycle_start): ?>
                    <div><strong style="color:#374151;">Period:</strong> <?php echo date('d M Y',strtotime($ap->cycle_start)); ?> – <?php echo date('d M Y',strtotime($ap->cycle_end)); ?></div>
                    <?php endif; ?>
                    <div><strong style="color:#374151;">Template:</strong> <?php echo htmlspecialchars($ap->template_name ?? 'None'); ?></div>
                    <div><strong style="color:#374151;">Status:</strong> <?php echo $ap->status; ?></div>
                    <?php if ($ap->score): ?>
                    <div><strong style="color:#7c3aed;">Current Score:</strong> <?php echo number_format($ap->score,1); ?>%</div>
                    <div><strong style="color:#7c3aed;">Rating:</strong> <?php echo $ap->rating; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pf-sidebar-card">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">Rating Scale</div>
                <?php
                $scale = ['Outstanding'=>[90,'#16a34a'],'Exceeds Expectations'=>[75,'#2563eb'],'Meets Expectations'=>[60,'#ca8a04'],'Below Expectations'=>[40,'#ea580c'],'Unsatisfactory'=>[0,'#dc2626']];
                foreach ($scale as $r=>[$min,$col]): ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="color:<?php echo $col; ?>;font-weight:600;"><?php echo $r; ?></span>
                    <span style="color:#9ca3af;">&ge;<?php echo $min; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.pf-star-row').forEach(function(row){
    var stars = row.querySelectorAll('.pf-star');
    var fieldName = row.dataset.field;
    var hiddenInput = document.querySelector('input[name="'+fieldName+'"]');
    stars.forEach(function(star, i){
        star.addEventListener('click', function(){
            var val = parseInt(star.dataset.val);
            stars.forEach(function(s,j){ s.classList.toggle('on', j < val); });
            if (hiddenInput) hiddenInput.value = val;
        });
        star.addEventListener('mouseenter', function(){
            var val = parseInt(star.dataset.val);
            stars.forEach(function(s,j){ s.style.color = j < val ? '#f59e0b' : '#d1d5db'; });
        });
        star.addEventListener('mouseleave', function(){
            stars.forEach(function(s){ s.style.color = ''; });
        });
    });
});
</script>
<?php init_tail(); ?>
