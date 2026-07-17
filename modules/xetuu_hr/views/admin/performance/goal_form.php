<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$g    = $goal;
$is_edit = !empty($g);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-form-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;max-width:760px;}
.pf-form-head{background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:20px 24px;}
.pf-form-body{padding:24px;}
.pf-field{margin-bottom:18px;}
.pf-field label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;}
.pf-field .form-control{border-radius:8px;border:1px solid #e5e7eb;font-size:13px;}
.pf-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:600px){.pf-grid-2{grid-template-columns:1fr;}}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> /
        <a href="<?php echo $base; ?>/performance/goals" style="color:#6b7280;text-decoration:none;">Goals</a> / <?php echo $is_edit ? 'Edit' : 'New'; ?>
    </div>
    <div class="pf-form-card">
        <div class="pf-form-head">
            <h2 style="font-size:18px;font-weight:800;color:#fff;margin:0;"><?php echo $is_edit ? 'Edit Goal' : 'New Goal / OKR'; ?></h2>
            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:3px;">Track employee performance targets and key results.</div>
        </div>
        <div class="pf-form-body">
            <form method="post" action="<?php echo $base; ?>/performance/goals/<?php echo $is_edit ? 'edit/'.$g->id : 'add'; ?>">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="pf-field">
                    <label>Goal Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($g->title??''); ?>" placeholder="e.g. Increase customer satisfaction score to 4.5">
                </div>

                <div class="pf-field">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="What does success look like?"><?php echo htmlspecialchars($g->description??''); ?></textarea>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>Assigned Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp->id; ?>" <?php echo ($g->employee_id??0)==$emp->id?'selected':''; ?>>
                                <?php echo htmlspecialchars($emp->full_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <?php foreach (['Individual','Team','Company'] as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($g->category??'Individual')===$cat?'selected':''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Goal Type</label>
                        <select name="type" class="form-control">
                            <?php foreach (['OKR','KPI','Target'] as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo ($g->type??'KPI')===$t?'selected':''; ?>><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <?php foreach (['Low','Medium','High','Critical'] as $p): ?>
                            <option value="<?php echo $p; ?>" <?php echo ($g->priority??'Medium')===$p?'selected':''; ?>><?php echo $p; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Target Value</label>
                        <input type="number" name="target_value" class="form-control" step="any" value="<?php echo htmlspecialchars($g->target_value??''); ?>" placeholder="e.g. 100">
                    </div>
                    <div class="pf-field">
                        <label>Unit</label>
                        <input type="text" name="unit" class="form-control" value="<?php echo htmlspecialchars($g->unit??''); ?>" placeholder="e.g. %, calls, $, units">
                    </div>
                    <div class="pf-field">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $g->start_date??date('Y-m-d'); ?>">
                    </div>
                    <div class="pf-field">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?php echo $g->due_date??''; ?>">
                    </div>
                    <div class="pf-field">
                        <label>Linked Appraisal Cycle</label>
                        <select name="linked_appraisal_cycle" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($cycles as $cy): ?>
                            <option value="<?php echo $cy->id; ?>" <?php echo ($g->linked_appraisal_cycle??0)==$cy->id?'selected':''; ?>>
                                <?php echo htmlspecialchars($cy->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <?php foreach (['Draft','Active','Completed','Cancelled'] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo ($g->status??'Active')===$st?'selected':''; ?>><?php echo $st; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;">
                        <?php echo $is_edit ? 'Save Changes' : 'Create Goal'; ?>
                    </button>
                    <a href="<?php echo $base; ?>/performance/goals" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
