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
$sc = $status_colors[$loan->status] ?? '#6b7280';
$total_paid = array_sum(array_column((array)$repayments, 'amount'));
$pct = $loan->principal_amount > 0 ? min(100, round(($total_paid / $loan->principal_amount) * 100)) : 100;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:20px; max-width:900px;">

    <!-- Breadcrumb + title -->
    <div style="margin-bottom:16px;">
        <div style="font-size:11px; color:#6b7280; margin-bottom:2px;">
            <a href="<?php echo $base; ?>/payroll/loans" style="color:#6b7280; text-decoration:none;">Loans</a> /
            <?php echo htmlspecialchars($loan->employee_name); ?>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <h1 style="font-size:18px; font-weight:700; color:#111827; margin:0;">
                Loan Statement
            </h1>
            <div style="display:flex; gap:8px;">
                <?php if ($loan->status === 'active'): ?>
                <button onclick="showSuspendModal()" class="btn btn-warning btn-sm" style="border-radius:6px; font-size:12px;">
                    <i class="fa fa-pause"></i> Suspend
                </button>
                <button onclick="showPayModal()" class="btn btn-success btn-sm" style="border-radius:6px; font-size:12px;">
                    <i class="fa fa-money"></i> Record Payment
                </button>
                <?php elseif ($loan->status === 'suspended'): ?>
                <form method="post" action="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/reactivate" style="display:inline;">
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius:6px; font-size:12px;">
                        <i class="fa fa-play"></i> Reactivate
                    </button>
                </form>
                <?php endif; ?>
                <a href="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/edit"
                   class="btn btn-default btn-sm" style="border-radius:6px; font-size:12px;">
                    <i class="fa fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px; margin-bottom:16px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr)); gap:14px; margin-bottom:14px;">
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Employee</div>
                <div style="font-size:13px; font-weight:700; color:#111827;"><?php echo htmlspecialchars($loan->employee_name); ?></div>
                <div style="font-size:10px; color:#9ca3af;"><?php echo htmlspecialchars($loan->employee_number); ?></div>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Loan Type</div>
                <div style="font-size:13px; font-weight:600; color:#374151;"><?php echo $loan_types[$loan->loan_type] ?? $loan->loan_type; ?></div>
                <?php if ($loan->loan_reference): ?>
                <div style="font-size:10px; color:#9ca3af;"><?php echo htmlspecialchars($loan->loan_reference); ?></div>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Principal</div>
                <div style="font-size:16px; font-weight:800; color:#111827;"><?php echo number_format($loan->principal_amount, 2); ?></div>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Balance Remaining</div>
                <div style="font-size:16px; font-weight:800; color:<?php echo $loan->balance_remaining > 0 ? '#dc2626' : '#16a34a'; ?>;">
                    <?php echo number_format($loan->balance_remaining, 2); ?>
                </div>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Monthly EMI</div>
                <div style="font-size:14px; font-weight:700; color:#2563eb;"><?php echo number_format($loan->monthly_installment, 2); ?></div>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Interest Rate</div>
                <div style="font-size:14px; font-weight:700; color:#374151;">
                    <?php echo $loan->interest_rate > 0 ? $loan->interest_rate . '% p.a.' : 'Interest-Free'; ?>
                </div>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">Status</div>
                <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700;
                             background:<?php echo $sc; ?>18; color:<?php echo $sc; ?>;">
                    <?php echo ucfirst(str_replace('_',' ',$loan->status)); ?>
                </span>
                <?php if ($loan->status === 'suspended' && $loan->suspension_reason): ?>
                <div style="font-size:10px; color:#d97706; margin-top:3px;"><?php echo htmlspecialchars($loan->suspension_reason); ?></div>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; font-weight:600; margin-bottom:3px;">First Deduction</div>
                <div style="font-size:12px; color:#374151;">
                    <?php echo $loan->start_deduction_date ? date('M Y', strtotime($loan->start_deduction_date)) : '—'; ?>
                </div>
            </div>
        </div>
        <!-- Progress bar -->
        <div style="margin-top:4px;">
            <div style="display:flex; justify-content:space-between; font-size:10px; color:#6b7280; margin-bottom:4px;">
                <span>Repaid: <?php echo number_format($total_paid, 2); ?></span>
                <span><?php echo $pct; ?>% complete</span>
            </div>
            <div style="height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden;">
                <div style="height:100%; width:<?php echo $pct; ?>%; background:#16a34a; border-radius:3px;"></div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

        <!-- Repayment History -->
        <div>
            <h3 style="font-size:13px; font-weight:700; color:#111827; margin:0 0 8px;">Repayment History</h3>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                <table class="table table-condensed" style="margin:0; font-size:11px;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="padding:8px 12px; font-weight:600; color:#374151;">Date</th>
                            <th style="padding:8px 12px; font-weight:600; color:#374151; text-align:right;">Amount</th>
                            <th style="padding:8px 12px; font-weight:600; color:#374151; text-align:right;">Principal</th>
                            <th style="padding:8px 12px; font-weight:600; color:#374151; text-align:right;">Balance</th>
                            <th style="padding:8px 12px; font-weight:600; color:#374151;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($repayments)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:20px; color:#9ca3af;">No payments yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($repayments as $rep): ?>
                        <tr style="border-top:1px solid #f3f4f6;">
                            <td style="padding:6px 12px; color:#374151;"><?php echo date('d M Y', strtotime($rep->repayment_date)); ?></td>
                            <td style="padding:6px 12px; text-align:right; font-weight:600; color:#111827;"><?php echo number_format($rep->amount, 2); ?></td>
                            <td style="padding:6px 12px; text-align:right; color:#6b7280;"><?php echo number_format($rep->principal_portion, 2); ?></td>
                            <td style="padding:6px 12px; text-align:right; color:#dc2626;"><?php echo number_format($rep->balance_after, 2); ?></td>
                            <td style="padding:6px 12px;">
                                <span style="font-size:10px; color:#6b7280;"><?php echo ucfirst($rep->repayment_type); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Amortization Schedule -->
        <div>
            <h3 style="font-size:13px; font-weight:700; color:#111827; margin:0 0 8px;">Amortization Schedule</h3>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; max-height:380px; overflow-y:auto;">
                <table class="table table-condensed" style="margin:0; font-size:11px;">
                    <thead style="background:#f9fafb; position:sticky; top:0;">
                        <tr>
                            <th style="padding:8px 10px; font-weight:600; color:#374151;">#</th>
                            <th style="padding:8px 10px; font-weight:600; color:#374151;">Due</th>
                            <th style="padding:8px 10px; font-weight:600; color:#374151; text-align:right;">EMI</th>
                            <th style="padding:8px 10px; font-weight:600; color:#374151; text-align:right;">Principal</th>
                            <th style="padding:8px 10px; font-weight:600; color:#374151; text-align:right;">Interest</th>
                            <th style="padding:8px 10px; font-weight:600; color:#374151; text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($schedule)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:20px; color:#9ca3af;">No schedule available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($schedule as $row): ?>
                        <tr style="border-top:1px solid #f3f4f6;">
                            <td style="padding:5px 10px; color:#9ca3af;"><?php echo $row['month']; ?></td>
                            <td style="padding:5px 10px; color:#374151;"><?php echo date('M Y', strtotime($row['due_date'])); ?></td>
                            <td style="padding:5px 10px; text-align:right; font-weight:600; color:#111827;"><?php echo number_format($row['emi'], 2); ?></td>
                            <td style="padding:5px 10px; text-align:right; color:#2563eb;"><?php echo number_format($row['principal'], 2); ?></td>
                            <td style="padding:5px 10px; text-align:right; color:#d97706;"><?php echo number_format($row['interest'], 2); ?></td>
                            <td style="padding:5px 10px; text-align:right; color:#dc2626; font-weight:600;"><?php echo number_format($row['closing'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspend-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; width:420px; max-width:95vw;">
        <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0 0 12px;">Suspend Loan</h3>
        <form method="post" action="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/suspend">
            <div style="margin-bottom:12px;">
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Reason</label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="e.g. Employee request — maternity leave"></textarea>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" onclick="hideSuspendModal()" class="btn btn-default btn-sm">Cancel</button>
                <button type="submit" class="btn btn-warning btn-sm">Suspend Loan</button>
            </div>
        </form>
    </div>
</div>

<!-- Manual Payment Modal -->
<div id="pay-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; width:380px; max-width:95vw;">
        <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0 0 12px;">Record Manual Payment</h3>
        <form method="post" action="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/pay">
            <div style="margin-bottom:10px;">
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Amount</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                       max="<?php echo $loan->balance_remaining; ?>"
                       value="<?php echo $loan->monthly_installment; ?>" required>
            </div>
            <div style="margin-bottom:10px;">
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Type</label>
                <select name="repayment_type" class="form-control">
                    <option value="manual">Manual Payment</option>
                    <option value="lump_sum">Lump Sum</option>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:4px;">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Bank transfer ref">
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" onclick="hidePayModal()" class="btn btn-default btn-sm">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function showSuspendModal() { document.getElementById('suspend-modal').style.display = 'flex'; }
function hideSuspendModal() { document.getElementById('suspend-modal').style.display = 'none'; }
function showPayModal()     { document.getElementById('pay-modal').style.display     = 'flex'; }
function hidePayModal()     { document.getElementById('pay-modal').style.display     = 'none'; }
</script>
<?php init_tail(); ?>
