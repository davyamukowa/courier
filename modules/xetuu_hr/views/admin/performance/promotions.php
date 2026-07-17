<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = [
    'Draft'     => ['#6b7280','#f3f4f6'],
    'Approved'  => ['#2563eb','#eff6ff'],
    'Applied'   => ['#16a34a','#f0fdf4'],
    'Cancelled' => ['#dc2626','#fef2f2'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-promo-card{background:#fff;border-radius:12px;border:1px solid #f3f4f6;border-left:4px solid var(--pc,#e5e7eb);padding:16px 18px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:box-shadow .15s;}
.pf-promo-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
.pf-avatar{width:40px;height:40px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-weight:700;color:#6b7280;flex-shrink:0;font-size:14px;overflow:hidden;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-arrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#374151;}
.pf-two-col{display:grid;grid-template-columns:1fr 280px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
</style>
<div class="pf-page">
    <div class="pf-two-col">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:11px;color:#6b7280;"><a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / Promotions</div>
                    <h1 style="font-size:20px;font-weight:800;color:#111827;margin:4px 0 0;">Promotions</h1>
                </div>
                <a href="<?php echo $base; ?>/performance/promotions/add" class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;background:#16a34a;border-color:#16a34a;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span> Record Promotion
                </a>
            </div>

            <!-- Filters -->
            <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border-radius:10px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);align-items:center;">
                <select name="status" class="form-control" style="height:32px;font-size:12px;width:140px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (array_keys($status_colors) as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($filters['status']??'')===$st?'selected':''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="form-control" style="height:32px;font-size:12px;width:90px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    <?php for ($y=date('Y'); $y>=date('Y')-4; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo ($filters['year']??'')==$y?'selected':''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <a href="?" style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:6px;padding:4px 8px;text-decoration:none;">Clear</a>
                <span style="margin-left:auto;font-size:11px;color:#9ca3af;"><?php echo count($promotions); ?> record<?php echo count($promotions)!=1?'s':''; ?></span>
            </form>

            <?php if (empty($promotions)): ?>
            <div style="background:#fff;border-radius:14px;padding:48px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:10px;">trending_up</span>
                <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No promotions recorded</div>
                <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Record employee promotions, role changes, and salary adjustments.</div>
                <a href="<?php echo $base; ?>/performance/promotions/add" class="btn btn-primary" style="border-radius:8px;background:#16a34a;border-color:#16a34a;">Record First Promotion</a>
            </div>
            <?php else: ?>
            <?php foreach ($promotions as $pr):
                $sc = $status_colors[$pr->status] ?? ['#6b7280','#f3f4f6'];
                $border_col = $pr->status === 'Applied' ? '#16a34a' : ($pr->status === 'Approved' ? '#2563eb' : '#e5e7eb');
            ?>
            <div class="pf-promo-card" style="--pc:<?php echo $border_col; ?>;">
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <?php if ($pr->photo): ?>
                    <img src="<?php echo base_url('uploads/staff_profile_images/'.$pr->photo); ?>" class="pf-avatar" style="width:40px;height:40px;object-fit:cover;">
                    <?php else: ?>
                    <div class="pf-avatar"><?php echo strtoupper(substr($pr->employee_name??'?',0,1)); ?></div>
                    <?php endif; ?>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($pr->employee_name??'—'); ?>
                            <span style="font-size:11px;color:#9ca3af;font-weight:400;">(<?php echo $pr->employee_number??''; ?>)</span>
                        </div>
                        <!-- Role change -->
                        <div class="pf-arrow" style="margin-top:4px;">
                            <span style="background:#f3f4f6;padding:2px 8px;border-radius:6px;"><?php echo htmlspecialchars($pr->from_designation??'Current Role'); ?></span>
                            <span class="material-symbols-outlined" style="font-size:16px;color:#16a34a;">arrow_forward</span>
                            <span style="background:#f0fdf4;color:#16a34a;font-weight:700;padding:2px 8px;border-radius:6px;"><?php echo htmlspecialchars($pr->to_designation??'New Role'); ?></span>
                        </div>
                        <?php if ($pr->from_grade || $pr->to_grade): ?>
                        <div class="pf-arrow" style="margin-top:3px;font-size:11px;color:#6b7280;">
                            Grade: <?php echo htmlspecialchars($pr->from_grade??'—'); ?>
                            <span class="material-symbols-outlined" style="font-size:14px;">arrow_forward</span>
                            <strong style="color:#2563eb;"><?php echo htmlspecialchars($pr->to_grade??'—'); ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if ($pr->salary_before && $pr->salary_after): ?>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">
                            Salary: <span><?php echo number_format($pr->salary_before,2); ?></span>
                            <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;">arrow_forward</span>
                            <strong style="color:#16a34a;"><?php echo number_format($pr->salary_after,2); ?></strong>
                            <?php $diff = $pr->salary_after - $pr->salary_before; ?>
                            <span style="color:<?php echo $diff>=0?'#16a34a':'#dc2626'; ?>;font-weight:700;margin-left:4px;">(<?php echo $diff>=0?'+':''; ?><?php echo number_format($diff,2); ?>)</span>
                        </div>
                        <?php endif; ?>
                        <div style="font-size:11px;color:#9ca3af;margin-top:3px;">Effective: <?php echo date('d M Y',strtotime($pr->effective_date)); ?></div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $pr->status; ?></span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <?php if ($pr->status === 'Draft'): ?>
                            <form method="post" action="<?php echo $base; ?>/performance/promotions/approve/<?php echo $pr->id; ?>" style="display:inline;">
                                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                                <button type="submit" class="btn btn-xs btn-primary" style="border-radius:4px;font-size:10px;background:#2563eb;border-color:#2563eb;">Approve</button>
                            </form>
                            <?php elseif ($pr->status === 'Approved'): ?>
                            <form method="post" action="<?php echo $base; ?>/performance/promotions/apply/<?php echo $pr->id; ?>" style="display:inline;" onsubmit="return confirm('Apply this promotion to the employee record now?')">
                                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                                <button type="submit" class="btn btn-xs btn-success" style="border-radius:4px;font-size:10px;background:#16a34a;border-color:#16a34a;color:#fff;">Apply</button>
                            </form>
                            <?php endif; ?>
                            <a href="<?php echo $base; ?>/performance/promotions/edit/<?php echo $pr->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">Edit</a>
                            <form method="post" action="<?php echo $base; ?>/performance/promotions/delete/<?php echo $pr->id; ?>" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                                <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;font-size:10px;">Del</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php if ($pr->reason): ?>
                <div style="margin-top:10px;font-size:11px;color:#374151;background:#f9fafb;border-radius:6px;padding:8px 12px;">
                    <span style="font-weight:700;">Reason:</span> <?php echo htmlspecialchars($pr->reason); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <?php $by_status = []; foreach ($promotions as $p2) $by_status[$p2->status] = ($by_status[$p2->status]??0)+1; ?>
            <div style="background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,#052e16,#166534);padding:16px 20px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.5);">Total Promotions</div>
                    <div style="font-size:32px;font-weight:800;color:#fff;"><?php echo count($promotions); ?></div>
                </div>
                <div style="padding:14px 16px;">
                    <?php foreach ($by_status as $st => $cnt): $sc=$status_colors[$st]??['#6b7280','#f3f4f6']; ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:12px;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $st; ?></span>
                        <strong><?php echo $cnt; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:8px;">Promotion Workflow</div>
                <?php foreach (['Draft → Approved (HR approves)','Approved → Applied (Updates employee record)','Applied (Permanent — cannot undo)'] as $i => $step): ?>
                <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:8px;font-size:11px;color:#374151;">
                    <span style="width:20px;height:20px;background:#f5f3ff;color:#7c3aed;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;flex-shrink:0;"><?php echo $i+1; ?></span>
                    <?php echo $step; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
