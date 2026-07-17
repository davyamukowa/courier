<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = [
    'Draft'     => ['#6b7280','#f3f4f6'],
    'Sent'      => ['#2563eb','#eff6ff'],
    'Completed' => ['#16a34a','#f0fdf4'],
    'Closed'    => ['#6b7280','#f3f4f6'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-fb-card{background:#fff;border-radius:12px;border:1px solid #f3f4f6;border-left:4px solid #2563eb;padding:16px 18px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;align-items:center;gap:14px;transition:box-shadow .15s;}
.pf-fb-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
.pf-avatar{width:40px;height:40px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-weight:700;color:#6b7280;flex-shrink:0;font-size:14px;overflow:hidden;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-two-col{display:grid;grid-template-columns:1fr 280px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
</style>
<div class="pf-page">
    <div class="pf-two-col">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:11px;color:#6b7280;"><a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> / 360° Feedback</div>
                    <h1 style="font-size:20px;font-weight:800;color:#111827;margin:4px 0 0;">360° Feedback</h1>
                </div>
                <a href="<?php echo $base; ?>/performance/feedback/add" class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;background:#2563eb;border-color:#2563eb;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span> New Feedback
                </a>
            </div>

            <!-- Filters -->
            <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border-radius:10px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);align-items:center;">
                <select name="appraisee_id" class="form-control" style="height:32px;font-size:12px;width:180px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp->id; ?>" <?php echo ($filters['appraisee_id']??'')==$emp->id?'selected':''; ?>>
                        <?php echo htmlspecialchars($emp->full_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-control" style="height:32px;font-size:12px;width:130px;padding:2px 8px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (array_keys($status_colors) as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($filters['status']??'')===$st?'selected':''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="?" style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:6px;padding:4px 8px;text-decoration:none;">Clear</a>
                <span style="margin-left:auto;font-size:11px;color:#9ca3af;"><?php echo count($feedbacks); ?> feedback<?php echo count($feedbacks)!=1?'s':''; ?></span>
            </form>

            <?php if (empty($feedbacks)): ?>
            <div style="background:#fff;border-radius:14px;padding:48px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:10px;">360</span>
                <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No 360° Feedback yet</div>
                <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Collect multi-rater feedback from peers, managers, and subordinates.</div>
                <a href="<?php echo $base; ?>/performance/feedback/add" class="btn btn-primary" style="border-radius:8px;background:#2563eb;border-color:#2563eb;">Create First Feedback</a>
            </div>
            <?php else: ?>
            <?php foreach ($feedbacks as $fb):
                $sc = $status_colors[$fb->status] ?? ['#6b7280','#f3f4f6'];
                $submitted = (int)($fb->submitted_count ?? 0);
                $total     = (int)($fb->reviewer_count ?? 0);
                $pct_submitted = $total > 0 ? round($submitted/$total*100) : 0;
            ?>
            <div class="pf-fb-card">
                <?php if ($fb->photo): ?>
                <img src="<?php echo base_url('uploads/staff_profile_images/'.$fb->photo); ?>" class="pf-avatar" style="width:40px;height:40px;object-fit:cover;">
                <?php else: ?>
                <div class="pf-avatar"><?php echo strtoupper(substr($fb->appraisee_name??'?',0,1)); ?></div>
                <?php endif; ?>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:700;color:#111827;"><?php echo htmlspecialchars($fb->title); ?></div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                        <span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">person</span> <?php echo htmlspecialchars($fb->appraisee_name??'—'); ?>
                        <?php if ($fb->anonymous): ?> · <span style="color:#7c3aed;font-weight:600;">Anonymous</span><?php endif; ?>
                        <?php if ($fb->deadline): ?> · Due <?php echo date('d M Y',strtotime($fb->deadline)); ?><?php endif; ?>
                    </div>
                    <?php if ($total > 0): ?>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                        <div style="height:5px;width:120px;border-radius:3px;background:#e5e7eb;overflow:hidden;">
                            <div style="height:100%;width:<?php echo $pct_submitted; ?>%;background:#2563eb;border-radius:3px;"></div>
                        </div>
                        <span style="font-size:10px;color:#6b7280;"><?php echo $submitted; ?>/<?php echo $total; ?> responded</span>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
                    <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $fb->status; ?></span>
                    <div style="display:flex;gap:4px;">
                        <a href="<?php echo $base; ?>/performance/feedback/view/<?php echo $fb->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">View</a>
                        <?php if ($fb->status === 'Draft'): ?>
                        <form method="post" action="<?php echo $base; ?>/performance/feedback/send/<?php echo $fb->id; ?>" style="display:inline;">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <button type="submit" class="btn btn-xs btn-primary" style="border-radius:4px;font-size:10px;background:#2563eb;border-color:#2563eb;">Send</button>
                        </form>
                        <?php endif; ?>
                        <form method="post" action="<?php echo $base; ?>/performance/feedback/delete/<?php echo $fb->id; ?>" style="display:inline;" onsubmit="return confirm('Delete this feedback?')">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <button type="submit" class="btn btn-xs btn-danger" style="border-radius:4px;font-size:10px;">Del</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <?php
            $by_status = [];
            foreach ($feedbacks as $f) $by_status[$f->status] = ($by_status[$f->status]??0)+1;
            ?>
            <div style="background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,#0f0f1a,#1e3a8a);padding:16px 20px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.5);">Total Feedback</div>
                    <div style="font-size:32px;font-weight:800;color:#fff;"><?php echo count($feedbacks); ?></div>
                </div>
                <div style="padding:14px 16px;">
                    <?php foreach ($by_status as $st => $cnt):
                        $sc = $status_colors[$st] ?? ['#6b7280','#f3f4f6'];
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:12px;">
                        <span class="pf-chip" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;"><?php echo $st; ?></span>
                        <strong><?php echo $cnt; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">About 360° Feedback</div>
                <div style="font-size:11px;color:#6b7280;line-height:1.7;">
                    Multi-rater feedback collects input from peers, managers, subordinates, and clients — giving employees a holistic view of their performance beyond just their direct manager's opinion.
                </div>
                <div style="margin-top:12px;">
                    <?php
                    $types = [['Self','#7c3aed'],['Peer','#2563eb'],['Manager','#16a34a'],['Subordinate','#ca8a04'],['Client','#dc2626']];
                    foreach ($types as [$t,$c]): ?>
                    <span style="display:inline-block;background:rgba(<?php echo implode(',',sscanf(ltrim($c,'#'),'%02x%02x%02x')); ?>,.1);color:<?php echo $c; ?>;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;margin:2px;"><?php echo $t; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
