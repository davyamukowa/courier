<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$pr   = $promotion;
$is_edit = !empty($pr);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-form-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;max-width:780px;}
.pf-form-head{background:linear-gradient(135deg,#052e16,#166534);padding:20px 24px;}
.pf-form-body{padding:24px;}
.pf-field{margin-bottom:18px;}
.pf-field label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;}
.pf-field .form-control{border-radius:8px;border:1px solid #e5e7eb;font-size:13px;}
.pf-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.pf-section-title{font-size:11px;font-weight:800;color:#16a34a;text-transform:uppercase;letter-spacing:.05em;margin:20px 0 10px;padding-bottom:6px;border-bottom:1px solid #f0fdf4;}
@media(max-width:600px){.pf-grid-2{grid-template-columns:1fr;}}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> /
        <a href="<?php echo $base; ?>/performance/promotions" style="color:#6b7280;text-decoration:none;">Promotions</a> / <?php echo $is_edit?'Edit':'New'; ?>
    </div>
    <div class="pf-form-card">
        <div class="pf-form-head">
            <h2 style="font-size:18px;font-weight:800;color:#fff;margin:0;"><?php echo $is_edit?'Edit Promotion':'Record Promotion'; ?></h2>
            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:3px;">Document role, grade, department, and salary changes.</div>
        </div>
        <div class="pf-form-body">
            <form method="post" action="<?php echo $base; ?>/performance/promotions/<?php echo $is_edit?'edit/'.$pr->id:'add'; ?>">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="pf-field">
                    <label>Employee *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">— Select Employee —</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo $emp->id; ?>" <?php echo ($pr->employee_id??0)==$emp->id?'selected':''; ?>>
                            <?php echo htmlspecialchars($emp->full_name); ?> (<?php echo $emp->employee_number; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pf-field">
                    <label>Effective Date *</label>
                    <input type="date" name="effective_date" class="form-control" required value="<?php echo $pr->effective_date??date('Y-m-d'); ?>">
                </div>

                <div class="pf-section-title">Designation Change</div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>From Designation</label>
                        <select name="from_designation_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($designations as $d): ?>
                            <option value="<?php echo $d->id; ?>" <?php echo ($pr->from_designation_id??0)==$d->id?'selected':''; ?>><?php echo htmlspecialchars($d->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>To Designation</label>
                        <select name="to_designation_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($designations as $d): ?>
                            <option value="<?php echo $d->id; ?>" <?php echo ($pr->to_designation_id??0)==$d->id?'selected':''; ?>><?php echo htmlspecialchars($d->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pf-section-title">Grade &amp; Department Change</div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>From Grade</label>
                        <select name="from_grade_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($grades as $g): ?>
                            <option value="<?php echo $g->id; ?>" <?php echo ($pr->from_grade_id??0)==$g->id?'selected':''; ?>><?php echo htmlspecialchars($g->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>To Grade</label>
                        <select name="to_grade_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($grades as $g): ?>
                            <option value="<?php echo $g->id; ?>" <?php echo ($pr->to_grade_id??0)==$g->id?'selected':''; ?>><?php echo htmlspecialchars($g->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>From Department</label>
                        <select name="from_department_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept->id; ?>" <?php echo ($pr->from_department_id??0)==$dept->id?'selected':''; ?>><?php echo htmlspecialchars($dept->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>To Department</label>
                        <select name="to_department_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept->id; ?>" <?php echo ($pr->to_department_id??0)==$dept->id?'selected':''; ?>><?php echo htmlspecialchars($dept->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pf-section-title">Salary Adjustment</div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>Current Salary</label>
                        <input type="number" name="salary_before" class="form-control" step="0.01" value="<?php echo $pr->salary_before??''; ?>" placeholder="0.00">
                    </div>
                    <div class="pf-field">
                        <label>New Salary</label>
                        <input type="number" name="salary_after" class="form-control" step="0.01" value="<?php echo $pr->salary_after??''; ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="pf-field">
                    <label>Reason / Justification</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Describe the reason for this promotion..."><?php echo htmlspecialchars($pr->reason??''); ?></textarea>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#16a34a;border-color:#16a34a;font-weight:700;">
                        <?php echo $is_edit?'Save Changes':'Record Promotion'; ?>
                    </button>
                    <a href="<?php echo $base; ?>/performance/promotions" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
