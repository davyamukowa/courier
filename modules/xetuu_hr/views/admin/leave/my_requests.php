<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_map = [
    'draft'           => ['Draft',            '#6b7280','#f3f4f6'],
    'pending_manager' => ['Pending Manager',   '#d97706','#fef9c3'],
    'pending_hr'      => ['Pending HR',        '#9333ea','#faf5ff'],
    'approved'        => ['Approved',          '#16a34a','#f0fdf4'],
    'rejected'        => ['Rejected',          '#dc2626','#fef2f2'],
    'cancelled'       => ['Cancelled',         '#6b7280','#f3f4f6'],
    'cancel_requested'=> ['Cancel Requested',  '#d97706','#fef9c3'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.lv-req-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f3f4f6;
    border-left: 4px solid var(--lc, #e5e7eb);
    padding: 16px 18px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 2px 10px rgba(0,0,0,.04);
    display: flex; align-items: flex-start; gap: 14px;
    transition: box-shadow .15s, transform .15s;
}
.lv-req-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-1px); }
.lv-req-card__icon {
    width: 40px; height: 40px;
    background: var(--lc-bg, #f3f4f6);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lv-req-card__icon .material-symbols-outlined { color: var(--lc, #6b7280); font-size: 22px; }
.lv-req-card__type { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 3px; }
.lv-req-card__meta { font-size: 12px; color: #6b7280; display: flex; gap: 12px; flex-wrap: wrap; }
.lv-req-card__actions { margin-left: auto; display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
.lv-chip { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; }
</style>

<div class="lv-page">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:11px;color:#6b7280;margin-bottom:4px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> / My Requests
            </div>
            <h1 style="font-size:20px;font-weight:800;color:#111827;margin:0;">My Leave Requests</h1>
        </div>
        <a href="<?php echo $base; ?>/leave/apply" class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:16px;">add</span> Apply for Leave
        </a>
    </div>

    <!-- Filters -->
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;background:#fff;border-radius:10px;
                               padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);align-items:center;">
        <?php if (!empty($is_super) && !empty($emp_list)): ?>
        <select name="employee_id" class="form-control" style="height:32px;font-size:12px;width:180px;padding:2px 8px;" onchange="this.form.submit()">
            <option value="">All Employees</option>
            <?php foreach ($emp_list as $el): ?>
            <option value="<?php echo $el->id; ?>" <?php echo (($filters['employee_id'] ?? '') == $el->id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($el->full_name); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <select name="status" class="form-control" style="height:32px;font-size:12px;width:160px;padding:2px 8px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($status_map as $sv => $sl): ?>
            <option value="<?php echo $sv; ?>" <?php echo (($filters['status'] ?? '') === $sv) ? 'selected' : ''; ?>>
                <?php echo $sl[0]; ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="year" class="form-control" style="height:32px;font-size:12px;width:100px;padding:2px 8px;" onchange="this.form.submit()">
            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
            <option value="<?php echo $y; ?>" <?php echo (($filters['year'] ?? date('Y')) == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        <?php if (!empty($filters)): ?>
        <a href="?" style="font-size:11px;color:#6b7280;text-decoration:none;padding:4px 8px;background:#f3f4f6;border-radius:6px;">Clear</a>
        <?php endif; ?>
        <span style="margin-left:auto;font-size:11px;color:#9ca3af;"><?php echo count($requests); ?> request<?php echo count($requests) != 1 ? 's' : ''; ?></span>
    </form>

    <!-- Requests List -->
    <?php if (empty($requests)): ?>
    <div style="background:#fff;border-radius:14px;padding:48px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:12px;">event_note</span>
        <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No leave requests found</div>
        <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">You haven't applied for any leave yet.</div>
        <a href="<?php echo $base; ?>/leave/apply" class="btn btn-primary" style="border-radius:8px;">Apply for Leave</a>
    </div>
    <?php else: ?>
    <?php foreach ($requests as $req):
        $sm    = $status_map[$req->status] ?? [$req->status, '#6b7280', '#f3f4f6'];
        $color = $req->type_color ?? $req->leave_type_color ?? '#2563eb';
        $hex   = ltrim($color,'#');
        $r2 = hexdec(substr($hex,0,2)); $g2 = hexdec(substr($hex,2,2)); $b2 = hexdec(substr($hex,4,2));
        $bg2 = "rgba($r2,$g2,$b2,.1)";
    ?>
    <div class="lv-req-card" style="--lc:<?php echo $color; ?>; --lc-bg:<?php echo $bg2; ?>;">
        <div class="lv-req-card__icon">
            <span class="material-symbols-outlined">beach_access</span>
        </div>
        <div style="flex:1;">
            <?php if (!empty($is_super)): ?>
            <div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:2px;">
                <span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">person</span>
                <?php echo htmlspecialchars($req->employee_name ?? ''); ?>
            </div>
            <?php endif; ?>
            <div class="lv-req-card__type"><?php echo htmlspecialchars($req->leave_type_name ?? ''); ?></div>
            <div class="lv-req-card__meta">
                <span><span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;">calendar_today</span>
                    <?php echo date('d M Y', strtotime($req->date_from)); ?> →
                    <?php echo date('d M Y', strtotime($req->date_to)); ?>
                </span>
                <span><b><?php echo number_format($req->total_days,1); ?></b> day<?php echo $req->total_days != 1 ? 's' : ''; ?></span>
                <?php if ($req->half_day): ?><span style="color:#9333ea;font-weight:600;">· Half Day (<?php echo $req->half_day_period; ?>)</span><?php endif; ?>
                <?php if ($req->handover_employee_name ?? ''): ?>
                <span>· Handover: <b><?php echo htmlspecialchars($req->handover_employee_name); ?></b></span>
                <?php endif; ?>
                <span style="color:#9ca3af;">Applied <?php echo date('d M Y', strtotime($req->date_created)); ?></span>
            </div>
            <?php if ($req->reason): ?>
            <div style="font-size:11px;color:#374151;margin-top:6px;padding:6px 10px;background:#f9fafb;border-radius:6px;">
                <?php echo htmlspecialchars($req->reason); ?>
            </div>
            <?php endif; ?>
            <?php if ($req->rejection_reason && in_array($req->status, ['rejected'])): ?>
            <div style="font-size:11px;color:#dc2626;margin-top:6px;padding:6px 10px;background:#fef2f2;border-radius:6px;">
                <strong>Rejection reason:</strong> <?php echo htmlspecialchars($req->rejection_reason); ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="lv-req-card__actions">
            <span class="lv-chip" style="color:<?php echo $sm[1]; ?>;background:<?php echo $sm[2]; ?>;"><?php echo $sm[0]; ?></span>
            <?php if ($req->status === 'approved'): ?>
            <form method="post" action="<?php echo $base; ?>/leave/my_requests/cancel/<?php echo $req->id; ?>" style="display:inline;">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;"
                        onclick="return confirm('Request cancellation of this leave?')">Cancel</button>
            </form>
            <?php endif; ?>
            <?php if ($req->status === 'draft'): ?>
            <a href="<?php echo $base; ?>/leave/my_requests/edit/<?php echo $req->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">Edit</a>
            <form method="post" action="<?php echo $base; ?>/leave/my_requests/submit/<?php echo $req->id; ?>" style="display:inline;">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-xs btn-primary" style="border-radius:4px;font-size:10px;">Submit</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php init_tail(); ?>
