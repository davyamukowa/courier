<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$loan_types = [
    'salary_advance' => 'Salary Advance',
    'helb'           => 'HELB',
    'equipment'      => 'Equipment',
    'emergency'      => 'Emergency',
    'other'          => 'Other',
];
$status_colors = [
    'active'     => '#16a34a',
    'suspended'  => '#d97706',
    'paid'       => '#6b7280',
    'written_off'=> '#dc2626',
    'cancelled'  => '#9ca3af',
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:20px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
        <div>
            <div style="font-size:11px; color:#6b7280; margin-bottom:2px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Loans
            </div>
            <h1 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Employee Loans</h1>
        </div>
        <a href="<?php echo $base; ?>/payroll/loans/new" class="btn btn-primary btn-sm" style="border-radius:6px;">
            <i class="fa fa-plus"></i> New Loan
        </a>
    </div>

    <!-- Filters -->
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
        <select name="status" class="form-control" style="height:30px; font-size:12px; width:140px; padding:2px 8px;"
                onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (['active','suspended','paid','written_off','cancelled'] as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo isset($filters['status']) && $filters['status'] === $s ? 'selected' : ''; ?>>
                <?php echo ucfirst(str_replace('_',' ',$s)); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="type" class="form-control" style="height:30px; font-size:12px; width:150px; padding:2px 8px;"
                onchange="this.form.submit()">
            <option value="">All Types</option>
            <?php foreach ($loan_types as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo isset($filters['loan_type']) && $filters['loan_type'] === $k ? 'selected' : ''; ?>>
                <?php echo $v; ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="company" value="<?php echo $active_company_id; ?>">
    </form>

    <!-- Table -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
        <table class="table table-condensed" style="margin:0; font-size:12px;">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="padding:10px 14px; font-weight:600; color:#374151;">Employee</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151;">Loan Type</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151;">Reference</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151; text-align:right;">Principal</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151; text-align:right;">Balance</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151; text-align:right;">Installment</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151;">Status</th>
                    <th style="padding:10px 14px; font-weight:600; color:#374151;">Start Date</th>
                    <th style="padding:10px 14px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($loans)): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:32px; color:#9ca3af;">No loans found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($loans as $loan): ?>
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px 14px;">
                        <div style="font-weight:600; color:#111827;"><?php echo htmlspecialchars($loan->employee_name); ?></div>
                        <div style="font-size:10px; color:#9ca3af;"><?php echo htmlspecialchars($loan->employee_number); ?></div>
                    </td>
                    <td style="padding:8px 14px; color:#374151;">
                        <?php echo $loan_types[$loan->loan_type] ?? ucfirst(str_replace('_',' ',$loan->loan_type)); ?>
                    </td>
                    <td style="padding:8px 14px; color:#374151;">
                        <?php echo $loan->loan_reference ? htmlspecialchars($loan->loan_reference) : '<span style="color:#d1d5db;">—</span>'; ?>
                    </td>
                    <td style="padding:8px 14px; text-align:right; color:#111827; font-weight:600;">
                        <?php echo number_format($loan->principal_amount, 2); ?>
                    </td>
                    <td style="padding:8px 14px; text-align:right; color:<?php echo $loan->balance_remaining > 0 ? '#dc2626' : '#16a34a'; ?>; font-weight:600;">
                        <?php echo number_format($loan->balance_remaining, 2); ?>
                    </td>
                    <td style="padding:8px 14px; text-align:right; color:#374151;">
                        <?php echo number_format($loan->monthly_installment, 2); ?>
                    </td>
                    <td style="padding:8px 14px;">
                        <?php $sc = $status_colors[$loan->status] ?? '#6b7280'; ?>
                        <span style="display:inline-block; padding:2px 8px; border-radius:999px; font-size:10px;
                                     font-weight:700; background:<?php echo $sc; ?>18; color:<?php echo $sc; ?>;">
                            <?php echo ucfirst(str_replace('_',' ',$loan->status)); ?>
                        </span>
                    </td>
                    <td style="padding:8px 14px; color:#6b7280; font-size:11px;">
                        <?php echo $loan->start_deduction_date ? date('M Y', strtotime($loan->start_deduction_date)) : '—'; ?>
                    </td>
                    <td style="padding:8px 14px; text-align:right;">
                        <a href="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/statement"
                           class="btn btn-xs btn-default" style="border-radius:4px; font-size:10px;">
                            View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php init_tail(); ?>
