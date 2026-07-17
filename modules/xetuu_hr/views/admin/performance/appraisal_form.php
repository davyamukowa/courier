<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-form-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;max-width:700px;}
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
        <a href="<?php echo $base; ?>/performance/appraisals" style="color:#6b7280;text-decoration:none;">Appraisals</a> / New
    </div>
    <div class="pf-form-card">
        <div class="pf-form-head">
            <h2 style="font-size:18px;font-weight:800;color:#fff;margin:0;">New Appraisal</h2>
            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:3px;">Select employee, cycle, and scoring template.</div>
        </div>
        <div class="pf-form-body">
            <?php if (empty($cycles)): ?>
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                <span class="material-symbols-outlined" style="color:#ca8a04;">warning</span>
                <div style="font-size:13px;color:#713f12;">No active appraisal cycles found. <a href="<?php echo $base; ?>/performance/config/cycles" style="color:#7c3aed;font-weight:700;">Create a cycle first.</a></div>
            </div>
            <?php endif; ?>
            <?php if (empty($templates)): ?>
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                <span class="material-symbols-outlined" style="color:#2563eb;">info</span>
                <div style="font-size:13px;color:#1e40af;">No scoring templates found. <a href="<?php echo $base; ?>/performance/config/templates" style="color:#7c3aed;font-weight:700;">Create a template</a> for weighted criteria scoring (optional).</div>
            </div>
            <?php endif; ?>
            <form method="post" action="<?php echo $base; ?>/performance/appraisals/add">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">— Select Employee —</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?> (<?php echo $emp->employee_number; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Reviewer</label>
                        <select name="reviewer_id" class="form-control">
                            <option value="">— Self / No Reviewer —</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Appraisal Cycle *</label>
                        <select name="cycle_id" class="form-control" required>
                            <option value="">— Select Cycle —</option>
                            <?php foreach ($cycles as $cy): ?>
                            <option value="<?php echo $cy->id; ?>"><?php echo htmlspecialchars($cy->name); ?> (<?php echo $cy->status; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Scoring Template</label>
                        <select name="template_id" class="form-control">
                            <option value="">— No Template (Free-form) —</option>
                            <?php foreach ($templates as $t): ?>
                            <option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:6px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;">Create &amp; Start Scoring</button>
                    <a href="<?php echo $base; ?>/performance/appraisals" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
