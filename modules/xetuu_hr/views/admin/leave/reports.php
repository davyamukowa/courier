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
$filters = [
    'employee_id' => $this->input->get('employee_id'),
    'status'      => $this->input->get('status'),
    'date_from'   => $this->input->get('date_from'),
    'date_to'     => $this->input->get('date_to'),
    'leave_type_id'=> $this->input->get('leave_type_id'),
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.lv-rep-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.lv-rep-table thead th {
    background: #f9fafb; font-size: 10px; font-weight: 700; text-transform: uppercase;
    color: #6b7280; padding: 10px 14px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;
}
.lv-rep-table thead th:first-child { border-radius: 8px 0 0 0; }
.lv-rep-table thead th:last-child  { border-radius: 0 8px 0 0; }
.lv-rep-table tbody td { padding: 10px 14px; font-size: 12px; color: #374151; border-bottom: 1px solid #f3f4f6; }
.lv-rep-table tr:hover td { background: #f9fafb; }
.lv-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap: 12px; margin-bottom: 20px; }
.lv-stat-box {
    background: #fff; border-radius: 12px; padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 2px 10px rgba(0,0,0,.04);
    border-left: 4px solid var(--sc);
}
.lv-stat-box__lbl { font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; }
.lv-stat-box__val { font-size: 24px; font-weight: 800; color: #111827; line-height: 1; }
.lv-stat-box__sub { font-size: 11px; color: #6b7280; margin-top: 3px; }
</style>

<div class="lv-page">

    <!-- ════════════════════════════════════════════════════════════
         SECTION 1 — EMPLOYEE LEAVE BALANCE SUMMARY (Excel Export)
         ════════════════════════════════════════════════════════════ -->
    <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);
                margin-bottom:28px;overflow:hidden;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#0f0f1a,#2d1b69);padding:20px 24px;
                    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.5);margin-bottom:3px;">
                    <a href="<?php echo $base; ?>/leave" style="color:rgba(255,255,255,.5);text-decoration:none;">Leave</a> / Reports
                </div>
                <h2 style="font-size:18px;font-weight:800;color:#fff;margin:0;">Employee Leave Balance Summary</h2>
                <div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:2px;">
                    Pivot view — one row per employee, one column per leave type
                </div>
            </div>
            <form method="get" action="<?php echo $base; ?>/leave/reports/balance_excel" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <select name="year" class="form-control" style="height:32px;font-size:12px;width:80px;padding:2px 8px;">
                    <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo ($year ?? date('Y')) == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <select name="department_id" class="form-control" style="height:32px;font-size:12px;width:140px;padding:2px 8px;">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?php echo $d->id; ?>" <?php echo ($dept_id ?? 0) == $d->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="emp_status" class="form-control" style="height:32px;font-size:12px;width:100px;padding:2px 8px;">
                    <option value="active" <?php echo ($emp_status ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="" <?php echo ($emp_status ?? '') === '' ? 'selected' : ''; ?>>All</option>
                </select>
                <button type="submit" style="padding:8px 18px;border-radius:8px;background:#8b5cf6;color:#fff;
                        border:none;cursor:pointer;font-size:12px;font-weight:700;
                        display:flex;align-items:center;gap:6px;white-space:nowrap;
                        box-shadow:0 2px 8px rgba(139,92,246,.4);">
                    <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                    Download Excel
                </button>
            </form>
        </div>

        <!-- Preview table -->
        <div style="overflow-x:auto;max-height:480px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
                <thead style="position:sticky;top:0;z-index:2;">
                    <tr>
                        <th style="background:#2d1b69;color:#fff;padding:10px 14px;font-size:10px;font-weight:700;
                                   text-transform:uppercase;white-space:nowrap;text-align:left;border-right:1px solid rgba(255,255,255,.1);">
                            Employee
                        </th>
                        <th style="background:#2d1b69;color:#fff;padding:10px 14px;font-size:10px;font-weight:700;
                                   text-transform:uppercase;white-space:nowrap;text-align:left;border-right:1px solid rgba(255,255,255,.1);">
                            Employee Name
                        </th>
                        <th style="background:#2d1b69;color:#fff;padding:10px 14px;font-size:10px;font-weight:700;
                                   text-transform:uppercase;white-space:nowrap;text-align:left;border-right:1px solid rgba(255,255,255,.1);">
                            Department
                        </th>
                        <?php foreach ($leave_types as $lt):
                            $lhex = ltrim($lt->color ?? '#8b5cf6','#');
                            $lr=hexdec(substr($lhex,0,2));$lg=hexdec(substr($lhex,2,2));$lb=hexdec(substr($lhex,4,2));
                        ?>
                        <th style="background:#2d1b69;color:#fff;padding:10px 8px;font-size:10px;font-weight:700;
                                   white-space:nowrap;text-align:center;border-right:1px solid rgba(255,255,255,.1);min-width:90px;">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                                         background:<?php echo $lt->color??'#8b5cf6'; ?>;margin-right:4px;vertical-align:middle;"></span>
                            <?php echo htmlspecialchars($lt->name); ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($balance_matrix)): ?>
                    <tr><td colspan="<?php echo 3 + count($leave_types); ?>" style="text-align:center;padding:40px;color:#9ca3af;">
                        No employees found for the selected filters.
                    </td></tr>
                <?php else: ?>
                <?php $col_totals = []; foreach($leave_types as $lt) $col_totals[$lt->id]=0; ?>
                <?php foreach ($balance_matrix as $i => $emp): ?>
                    <tr style="background:<?php echo $i%2===1?'#f9fafb':'#fff'; ?>;">
                        <td style="padding:9px 14px;color:#6b7280;border-bottom:1px solid #f3f4f6;white-space:nowrap;font-size:11px;">
                            <?php echo htmlspecialchars($emp['number']); ?>
                        </td>
                        <td style="padding:9px 14px;font-weight:600;color:#111827;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
                            <?php echo htmlspecialchars($emp['name']); ?>
                        </td>
                        <td style="padding:9px 14px;color:#374151;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
                            <?php echo htmlspecialchars($emp['department']); ?>
                        </td>
                        <?php foreach ($leave_types as $lt):
                            $bal = $emp['balances'][$lt->id] ?? null;
                            $rem = $bal ? (float)$bal['remaining'] : 0.0;
                            $col_totals[$lt->id] += $rem;
                            $lhex2 = ltrim($lt->color ?? '#2d1b69','#');
                            $lr2=hexdec(substr($lhex2,0,2));$lg2=hexdec(substr($lhex2,2,2));$lb2=hexdec(substr($lhex2,4,2));
                        ?>
                        <td style="padding:9px 8px;text-align:center;border-bottom:1px solid #f3f4f6;border-right:1px solid #f3f4f6;
                                   color:<?php echo $rem>0?"rgba($lr2,$lg2,$lb2,1)":'#d1d5db'; ?>;
                                   font-weight:<?php echo $rem>0?700:400; ?>;">
                            <?php echo number_format($rem, 3); ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                    <!-- Totals row -->
                    <tr style="background:#ede9fe;position:sticky;bottom:0;">
                        <td colspan="3" style="padding:10px 14px;font-weight:800;font-size:11px;color:#2d1b69;
                                               text-transform:uppercase;letter-spacing:.04em;">
                            Total · <?php echo count($balance_matrix); ?> employees
                        </td>
                        <?php foreach ($leave_types as $lt): ?>
                        <td style="padding:10px 8px;text-align:center;font-weight:800;color:#2d1b69;border-right:1px solid #c4b5fd;">
                            <?php echo number_format($col_totals[$lt->id], 3); ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="padding:10px 16px;font-size:11px;color:#9ca3af;border-top:1px solid #f3f4f6;display:flex;align-items:center;gap:8px;">
            <span class="material-symbols-outlined" style="font-size:14px;">info</span>
            Values show <strong>remaining</strong> leave days (allocated + carried forward − used). Click <strong>Download Excel</strong> to export with full formatting.
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
         SECTION 2 — LEAVE REGISTER (existing)
         ════════════════════════════════════════════════════════════ -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <h1 style="font-size:18px;font-weight:800;color:#111827;margin:0;">Leave Register</h1>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">All leave requests with full details</div>
        </div>
        <a href="<?php echo $base; ?>/leave/reports?<?php echo http_build_query(array_merge($filters,['export'=>'csv'])); ?>"
           class="btn btn-default" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;font-size:12px;">
            <span class="material-symbols-outlined" style="font-size:16px;">download</span> Export CSV
        </a>
    </div>

    <!-- Summary Stats -->
    <?php
    $stats = ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'total_days' => 0];
    foreach ($requests as $r) {
        $stats['total']++;
        if ($r->status === 'approved') { $stats['approved']++; $stats['total_days'] += (float)$r->total_days; }
        if (in_array($r->status, ['pending_manager','pending_hr'])) $stats['pending']++;
        if ($r->status === 'rejected') $stats['rejected']++;
    }
    ?>
    <div class="lv-stat-grid">
        <div class="lv-stat-box" style="--sc:#2563eb;">
            <div class="lv-stat-box__lbl">Total Requests</div>
            <div class="lv-stat-box__val"><?php echo $stats['total']; ?></div>
        </div>
        <div class="lv-stat-box" style="--sc:#16a34a;">
            <div class="lv-stat-box__lbl">Approved</div>
            <div class="lv-stat-box__val"><?php echo $stats['approved']; ?></div>
            <div class="lv-stat-box__sub"><?php echo number_format($stats['total_days'],1); ?> days taken</div>
        </div>
        <div class="lv-stat-box" style="--sc:#d97706;">
            <div class="lv-stat-box__lbl">Pending</div>
            <div class="lv-stat-box__val"><?php echo $stats['pending']; ?></div>
        </div>
        <div class="lv-stat-box" style="--sc:#dc2626;">
            <div class="lv-stat-box__lbl">Rejected</div>
            <div class="lv-stat-box__val"><?php echo $stats['rejected']; ?></div>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border-radius:10px;
                               padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <select name="leave_type_id" class="form-control" style="height:30px;font-size:12px;width:160px;padding:2px 8px;" onchange="this.form.submit()">
            <option value="">All Leave Types</option>
            <?php foreach ($leave_types as $lt): ?>
            <option value="<?php echo $lt->id; ?>" <?php echo ($filters['leave_type_id'] == $lt->id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($lt->name); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-control" style="height:30px;font-size:12px;width:150px;padding:2px 8px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($status_map as $sv => $sl): ?>
            <option value="<?php echo $sv; ?>" <?php echo ($filters['status'] === $sv) ? 'selected' : ''; ?>><?php echo $sl[0]; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>"
               class="form-control" style="height:30px;font-size:12px;width:130px;padding:2px 8px;" placeholder="From date">
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>"
               class="form-control" style="height:30px;font-size:12px;width:130px;padding:2px 8px;" placeholder="To date">
        <button type="submit" class="btn btn-default btn-sm" style="border-radius:6px;height:30px;">Filter</button>
        <a href="<?php echo $base; ?>/leave/reports" class="btn btn-link btn-sm" style="height:30px;line-height:20px;font-size:12px;padding:4px 8px;">Clear</a>
    </form>

    <!-- Table -->
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.05);overflow:hidden;">
        <?php if (empty($requests)): ?>
        <div style="padding:48px;text-align:center;">
            <span class="material-symbols-outlined" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:12px;">search_off</span>
            <div style="font-size:15px;font-weight:700;color:#374151;margin-bottom:6px;">No records found</div>
            <div style="font-size:13px;color:#9ca3af;">Try adjusting the filters above.</div>
        </div>
        <?php else: ?>
        <table class="lv-rep-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Applied</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php $n = 1; foreach ($requests as $req):
                $sm    = $status_map[$req->status] ?? [$req->status, '#6b7280', '#f3f4f6'];
                $color = $req->type_color ?? '#2563eb';
                $hex   = ltrim($color,'#');
                $r2=hexdec(substr($hex,0,2)); $g2=hexdec(substr($hex,2,2)); $b2=hexdec(substr($hex,4,2));
                $bg2   = "rgba($r2,$g2,$b2,.1)";
            ?>
            <tr>
                <td style="color:#9ca3af;font-size:11px;"><?php echo $n++; ?></td>
                <td>
                    <div style="font-weight:700;color:#111827;"><?php echo htmlspecialchars($req->employee_name ?? '—'); ?></div>
                    <div style="font-size:10px;color:#6b7280;"><?php echo htmlspecialchars($req->department_name ?? ''); ?></div>
                </td>
                <td>
                    <span style="display:inline-block;padding:2px 9px;border-radius:6px;font-size:10px;font-weight:700;
                                 background:<?php echo $bg2; ?>;color:<?php echo $color; ?>;">
                        <?php echo htmlspecialchars($req->leave_type_name ?? ''); ?>
                    </span>
                </td>
                <td><?php echo date('d M Y', strtotime($req->date_from)); ?></td>
                <td><?php echo date('d M Y', strtotime($req->date_to)); ?></td>
                <td style="font-weight:700;"><?php echo number_format($req->total_days,1); ?></td>
                <td style="color:#6b7280;"><?php echo date('d M Y', strtotime($req->date_created)); ?></td>
                <td>
                    <span style="display:inline-block;padding:2px 9px;border-radius:999px;font-size:10px;font-weight:700;
                                 background:<?php echo $sm[2]; ?>;color:<?php echo $sm[1]; ?>;">
                        <?php echo $sm[0]; ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php init_tail(); ?>
