<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-form-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;max-width:680px;}
.pf-form-head{background:linear-gradient(135deg,#0f0f1a,#1e3a8a);padding:20px 24px;}
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
        <a href="<?php echo $base; ?>/performance/feedback" style="color:#6b7280;text-decoration:none;">360° Feedback</a> / New
    </div>
    <div class="pf-form-card">
        <div class="pf-form-head">
            <h2 style="font-size:18px;font-weight:800;color:#fff;margin:0;">New 360° Feedback</h2>
            <div style="font-size:12px;color:rgba(255,255,255,.5);margin-top:3px;">Collect multi-rater feedback for an employee.</div>
        </div>
        <div class="pf-form-body">
            <form method="post" action="<?php echo $base; ?>/performance/feedback/add">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <div class="pf-field">
                    <label>Feedback Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Q2 2025 Performance Feedback — Jane Doe">
                </div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label>Employee (Appraisee) *</label>
                        <select name="appraisee_id" class="form-control" required>
                            <option value="">— Select Employee —</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Linked Cycle</label>
                        <select name="cycle_id" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($cycles as $cy): ?>
                            <option value="<?php echo $cy->id; ?>"><?php echo htmlspecialchars($cy->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label>Response Deadline</label>
                        <input type="date" name="deadline" class="form-control" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                    </div>
                    <div class="pf-field">
                        <label>Anonymous Responses</label>
                        <select name="anonymous" class="form-control">
                            <option value="0">No — Reviewers identified</option>
                            <option value="1">Yes — Keep responses anonymous</option>
                        </select>
                    </div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:12px;color:#166534;">
                    <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">check_circle</span>
                    6 default questions will be added automatically. You can edit them after creating.
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#2563eb;border-color:#2563eb;font-weight:700;">Create Feedback</button>
                    <a href="<?php echo $base; ?>/performance/feedback" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
