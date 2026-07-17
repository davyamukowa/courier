<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;"><a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / <span style="color:#111827;">Payslips</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payslips</h1>
        </div>
        <a href="<?php echo $base.'/payroll/payslips/new'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New Single Payslip</a>
    </div>

    <?php if ($show_form): ?>
    <!-- New Single Payslip Form -->
    <div class="row">
        <div class="col-md-5">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">New Payslip</div>
                <form action="<?php echo $base.'/payroll/payslips'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">— Select Employee —</option>
                            <?php foreach ($employees as $e): ?>
                            <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars($e->first_name.' '.$e->last_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:3px;">Employee must have an active payroll contract</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Period From *</label>
                                <input type="date" name="date_from" class="form-control" required value="<?php echo date('Y-m-01'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Period To *</label>
                                <input type="date" name="date_to" class="form-control" required value="<?php echo date('Y-m-t'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional note on this payslip">
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="create_payslip" value="1" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-plus"></i> Create &amp; Compute</button>
                        <a href="<?php echo $base.'/payroll/payslips'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:20px;">
                <div style="font-size:13px; font-weight:700; color:#166534; margin-bottom:10px;"><i class="fa fa-info-circle"></i> Single Payslip Flow</div>
                <ol style="font-size:12px; color:#166534; padding-left:18px; margin:0;">
                    <li style="margin-bottom:6px;">Select employee and pay period</li>
                    <li style="margin-bottom:6px;">System auto-computes using the employee's active contract &amp; salary structure</li>
                    <li style="margin-bottom:6px;">Review computed lines, adjust if needed</li>
                    <li style="margin-bottom:6px;">Confirm payslip → locks the figures</li>
                    <li>Send by email or download PDF</li>
                </ol>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Payslips List -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
        <div style="padding:12px 16px; border-bottom:1px solid #f3f4f6; display:flex; gap:12px; flex-wrap:wrap;">
            <input type="text" id="ps-search" class="form-control" placeholder="Search employee..." style="max-width:220px; font-size:13px;">
            <select id="ps-status" class="form-control" style="max-width:130px; font-size:13px;">
                <option value="">All</option>
                <option value="draft">Draft</option>
                <option value="computed">Computed</option>
                <option value="confirmed">Confirmed</option>
                <option value="done">Done</option>
                <option value="paid">Paid</option>
            </select>
            <select id="ps-company" class="form-control" style="max-width:180px; font-size:13px;">
                <option value="">All Companies</option>
                <?php foreach ($companies as $co): ?>
                <option value="<?php echo $co->id; ?>"><?php echo htmlspecialchars($co->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table class="table table-hover" style="margin:0;">
            <thead><tr style="background:#f9fafb;">
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Ref</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Employee</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Company</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Period</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Basic</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Net Pay</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                <th></th>
            </tr></thead>
            <tbody id="ps-tbody">
            <?php
            $state_colors = [
                'draft'     => ['#f3f4f6','#6b7280'],
                'computed'  => ['#eff6ff','#2563eb'],
                'confirmed' => ['#f0fdf4','#16a34a'],
                'done'      => ['#fdf4ff','#9333ea'],
                'paid'      => ['#f0fdf4','#16a34a'],
            ];
            ?>
            <?php if (empty($payslips)): ?>
            <tr><td colspan="8" style="text-align:center; padding:40px; color:#9ca3af;">No payslips found.</td></tr>
            <?php else: ?>
            <?php foreach ($payslips as $p): ?>
            <?php [$sbg,$sfg] = $state_colors[$p->state] ?? ['#f3f4f6','#6b7280']; ?>
            <tr data-name="<?php echo strtolower(htmlspecialchars($p->employee_name ?? '')); ?>" data-status="<?php echo $p->state; ?>" data-company="<?php echo $p->company_id; ?>" style="cursor:pointer;" onclick="location.href='<?php echo $base.'/payroll/payslips/'.$p->id; ?>'">
                <td style="padding:10px 16px; font-size:12px; font-family:monospace; color:#6b7280;"><?php echo htmlspecialchars($p->reference ?? 'PS-'.$p->id); ?></td>
                <td style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($p->employee_name ?? '—'); ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($p->company_name ?? '—'); ?></td>
                <td style="font-size:12px; color:#374151;"><?php echo date('d M', strtotime($p->date_from)).' – '.date('d M Y', strtotime($p->date_to)); ?></td>
                <td style="font-size:12px; color:#374151;"><?php echo number_format($p->gross_salary, 2); ?></td>
                <td style="font-size:13px; font-weight:700; color:#111827;"><?php echo number_format($p->net_salary, 2); ?></td>
                <td><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $sbg; ?>; color:<?php echo $sfg; ?>; text-transform:uppercase;"><?php echo $p->state; ?></span></td>
                <td style="text-align:right; padding-right:12px;" onclick="event.stopPropagation()">
                    <a href="<?php echo $base.'/payroll/payslips/'.$p->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">View</a>
                    <?php if ($p->state === 'confirmed' || $p->state === 'done'): ?>
                    <a href="<?php echo $base.'/payroll/payslips/'.$p->id.'/pdf'; ?>" class="btn btn-xs btn-primary" style="border-radius:4px;" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<script>
const psSearch = document.getElementById('ps-search');
const psStatus = document.getElementById('ps-status');
const psCompany = document.getElementById('ps-company');
function filterPs() {
    if (!psSearch) return;
    const q = psSearch.value.toLowerCase();
    const s = psStatus.value;
    const co = psCompany.value;
    document.querySelectorAll('#ps-tbody tr[data-name]').forEach(tr => {
        tr.style.display = ((!q || tr.dataset.name.includes(q)) && (!s || tr.dataset.status === s) && (!co || tr.dataset.company === co)) ? '' : 'none';
    });
}
if (psSearch) { psSearch.addEventListener('input', filterPs); psStatus.addEventListener('change', filterPs); psCompany.addEventListener('change', filterPs); }
</script>
<?php init_tail(); ?>
