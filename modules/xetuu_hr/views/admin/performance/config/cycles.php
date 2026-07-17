<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = ['Draft'=>['#6b7280','#f3f4f6'],'Active'=>['#16a34a','#f0fdf4'],'Closed'=>['#dc2626','#fef2f2']];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-two-col{display:grid;grid-template-columns:1fr 400px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-field{margin-bottom:14px;}
.pf-field label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;}
.pf-field .form-control{border-radius:8px;border:1px solid #e5e7eb;font-size:13px;}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / Config / Cycles
    </div>
    <div class="pf-two-col">
        <!-- Left: Cycle list -->
        <div>
            <div style="font-size:18px;font-weight:800;color:#111827;margin-bottom:14px;">Appraisal Cycles</div>
            <?php if (empty($cycles)): ?>
            <div style="background:#fff;border-radius:14px;padding:40px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:8px;">date_range</span>
                <div style="font-size:14px;font-weight:700;color:#374151;margin-bottom:6px;">No cycles yet</div>
                <div style="font-size:12px;color:#9ca3af;">Create cycles to define appraisal periods (e.g. Q1 2025, Annual 2025).</div>
            </div>
            <?php else: ?>
            <?php foreach ($cycles as $cy):
                $sc = $status_colors[$cy->status] ?? ['#6b7280','#f3f4f6'];
            ?>
            <div class="pf-card" style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
                <div style="width:10px;height:10px;border-radius:50%;background:<?php echo $sc[0]; ?>;flex-shrink:0;"></div>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($cy->name); ?></div>
                    <div style="font-size:11px;color:#6b7280;"><?php echo date('d M Y',strtotime($cy->start_date)); ?> – <?php echo date('d M Y',strtotime($cy->end_date)); ?></div>
                </div>
                <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $cy->status; ?></span>
                <div style="display:flex;gap:4px;">
                    <a href="<?php echo $base; ?>/performance/config/cycles/<?php echo $cy->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">Edit</a>
                    <form method="post" action="<?php echo $base; ?>/performance/config/cycles/<?php echo $cy->id; ?>" onsubmit="return confirm('Delete this cycle?')" style="display:inline;">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <?php echo form_hidden('sub_action','delete_cycle'); ?>
                        <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;font-size:10px;">Del</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right: Form -->
        <div>
            <div class="pf-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:16px 20px;">
                    <div style="font-size:14px;font-weight:700;color:#fff;"><?php echo $edit_cycle ? 'Edit Cycle' : 'New Appraisal Cycle'; ?></div>
                </div>
                <div style="padding:20px;">
                    <form method="post" action="<?php echo $base; ?>/performance/config/cycles<?php echo $edit_cycle?'/'.$edit_cycle->id:''; ?>">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                        <?php echo form_hidden('sub_action','save_cycle'); ?>
                        <div class="pf-field">
                            <label>Cycle Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_cycle->name??''); ?>" placeholder="e.g. Annual Performance Review 2025">
                        </div>
                        <div class="pf-field">
                            <label>Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required value="<?php echo $edit_cycle->start_date??date('Y-01-01'); ?>">
                        </div>
                        <div class="pf-field">
                            <label>End Date *</label>
                            <input type="date" name="end_date" class="form-control" required value="<?php echo $edit_cycle->end_date??date('Y-12-31'); ?>">
                        </div>
                        <div class="pf-field">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <?php foreach (['Draft','Active','Closed'] as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo ($edit_cycle->status??'Draft')===$st?'selected':''; ?>><?php echo $st; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#7c3aed;border-color:#7c3aed;font-weight:700;">
                                <?php echo $edit_cycle?'Update':'Create Cycle'; ?>
                            </button>
                            <?php if ($edit_cycle): ?>
                            <a href="<?php echo $base; ?>/performance/config/cycles" class="btn btn-default" style="border-radius:8px;">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
