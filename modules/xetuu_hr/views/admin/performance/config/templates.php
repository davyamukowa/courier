<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$is_edit = !empty($edit_tpl);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px;}
.pf-field{margin-bottom:14px;}
.pf-field label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;}
.pf-field .form-control{border-radius:8px;border:1px solid #e5e7eb;font-size:13px;}
.pf-criteria-item{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;align-items:end;padding:10px 0;border-bottom:1px solid #f3f4f6;}
.pf-criteria-item:last-child{border-bottom:none;}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / Config / Templates
    </div>
    <div class="pf-two-col">
        <!-- Left: Template List -->
        <div>
            <div style="font-size:18px;font-weight:800;color:#111827;margin-bottom:14px;">Appraisal Templates</div>
            <?php if (empty($templates)): ?>
            <div style="background:#fff;border-radius:14px;padding:40px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:8px;">description</span>
                <div style="font-size:14px;font-weight:700;color:#374151;margin-bottom:4px;">No templates yet</div>
                <div style="font-size:12px;color:#9ca3af;">Templates define weighted scoring criteria for appraisals.</div>
            </div>
            <?php else: ?>
            <?php foreach ($templates as $t): ?>
            <div class="pf-card">
                <div style="padding:16px 20px 8px;display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($t->name); ?></div>
                        <?php if ($t->description): ?>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;"><?php echo htmlspecialchars($t->description); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;gap:4px;">
                        <a href="<?php echo $base; ?>/performance/config/templates/<?php echo $t->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">Edit</a>
                        <form method="post" action="<?php echo $base; ?>/performance/config/templates/<?php echo $t->id; ?>" onsubmit="return confirm('Delete template?')" style="display:inline;">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <?php echo form_hidden('sub_action','delete_template'); ?>
                            <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;font-size:10px;">Del</button>
                        </form>
                    </div>
                </div>
                <?php
                // Criteria are loaded by handler into $templates via get_template() — use global model
                $CI =& get_instance();
                $crit = $CI->perf_mdl->get_criteria($t->id);
                if (!empty($crit)):
                ?>
                <div style="padding:4px 20px 14px;">
                    <?php foreach ($crit as $c): ?>
                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:4px 0;border-bottom:1px solid #f9fafb;color:#374151;">
                        <span><?php echo htmlspecialchars($c->name); ?> <span style="color:#9ca3af;">(<?php echo $c->category??'General'; ?>)</span></span>
                        <span><strong style="color:#7c3aed;"><?php echo $c->weight; ?>%</strong> · max <?php echo $c->max_score; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right: Form -->
        <div>
            <div class="pf-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:16px 20px;">
                    <div style="font-size:14px;font-weight:700;color:#fff;"><?php echo $is_edit?'Edit Template':'New Appraisal Template'; ?></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;">Define weighted criteria for scoring employees.</div>
                </div>
                <div style="padding:20px;">
                    <form method="post" action="<?php echo $base; ?>/performance/config/templates<?php echo $is_edit?'/'.$edit_tpl->id:''; ?>" id="tplForm">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <?php echo form_hidden('sub_action','save_template'); ?>
                        <div class="pf-field">
                            <label>Template Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_tpl->name??''); ?>" placeholder="e.g. Standard Employee Appraisal">
                        </div>
                        <div class="pf-field">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description..."><?php echo htmlspecialchars($edit_tpl->description??''); ?></textarea>
                        </div>

                        <div style="font-size:12px;font-weight:700;color:#374151;margin:16px 0 8px;">
                            Scoring Criteria
                            <button type="button" onclick="addCriterionRow()" style="float:right;background:#7c3aed;color:#fff;border:none;border-radius:6px;padding:3px 10px;font-size:11px;cursor:pointer;font-weight:700;">+ Add</button>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;padding:4px 0;margin-bottom:4px;">
                            <span>Criterion Name</span><span>Category</span><span>Weight %</span><span>Max Score</span><span></span>
                        </div>
                        <div id="criteriaContainer">
                            <?php
                            $existing_criteria = $is_edit ? $edit_tpl->criteria : [];
                            if (empty($existing_criteria)):
                                // Default starter criteria
                                $defaults = [
                                    ['Job Knowledge','Technical',25,5],
                                    ['Quality of Work','Performance',25,5],
                                    ['Communication','Behaviour',20,5],
                                    ['Teamwork','Behaviour',15,5],
                                    ['Initiative','Behaviour',15,5],
                                ];
                                foreach ($defaults as $i => [$name,$cat,$w,$max]):
                            ?>
                            <div class="pf-criteria-item">
                                <input type="text" name="criteria_name[]" class="form-control" value="<?php echo $name; ?>" placeholder="Criterion name" style="font-size:12px;">
                                <input type="text" name="criteria_cat[]" class="form-control" value="<?php echo $cat; ?>" placeholder="Category" style="font-size:12px;">
                                <input type="number" name="criteria_weight[]" class="form-control" value="<?php echo $w; ?>" min="0" max="100" style="font-size:12px;">
                                <input type="number" name="criteria_max[]" class="form-control" value="<?php echo $max; ?>" min="1" max="10" style="font-size:12px;">
                                <button type="button" onclick="this.closest('.pf-criteria-item').remove()" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:14px;">×</button>
                            </div>
                            <?php endforeach;
                            else:
                                foreach ($existing_criteria as $c):
                            ?>
                            <div class="pf-criteria-item">
                                <input type="text" name="criteria_name[]" class="form-control" value="<?php echo htmlspecialchars($c->name); ?>" style="font-size:12px;">
                                <input type="text" name="criteria_cat[]" class="form-control" value="<?php echo htmlspecialchars($c->category??''); ?>" style="font-size:12px;">
                                <input type="number" name="criteria_weight[]" class="form-control" value="<?php echo $c->weight; ?>" min="0" max="100" style="font-size:12px;">
                                <input type="number" name="criteria_max[]" class="form-control" value="<?php echo $c->max_score; ?>" min="1" max="10" style="font-size:12px;">
                                <button type="button" onclick="this.closest('.pf-criteria-item').remove()" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:14px;">×</button>
                            </div>
                            <?php endforeach;
                            endif; ?>
                        </div>

                        <div style="margin-top:14px;padding-top:10px;border-top:1px solid #f3f4f6;display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;"><?php echo $is_edit?'Save Changes':'Create Template'; ?></button>
                            <?php if ($is_edit): ?>
                            <a href="<?php echo $base; ?>/performance/config/templates" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function addCriterionRow(){
    var container = document.getElementById('criteriaContainer');
    var div = document.createElement('div');
    div.className = 'pf-criteria-item';
    div.innerHTML = '<input type="text" name="criteria_name[]" class="form-control" placeholder="Criterion name" style="font-size:12px;">'
        + '<input type="text" name="criteria_cat[]" class="form-control" placeholder="Category" style="font-size:12px;">'
        + '<input type="number" name="criteria_weight[]" class="form-control" value="20" min="0" max="100" style="font-size:12px;">'
        + '<input type="number" name="criteria_max[]" class="form-control" value="5" min="1" max="10" style="font-size:12px;">'
        + '<button type="button" onclick="this.closest(\'.pf-criteria-item\').remove()" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:14px;">×</button>';
    container.appendChild(div);
}
</script>
<?php init_tail(); ?>
