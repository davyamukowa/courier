<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$states = ['draft', 'computed', 'confirmed', 'done', 'paid'];
$cur_idx = array_search($payslip->state, $states);
$cat_colors = ['EARN'=>'#16a34a','DED'=>'#dc2626','TAX'=>'#9333ea','NET'=>'#2563eb','EMPLOYER'=>'#d97706','LOAN'=>'#d97706'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> /
                <a href="<?php echo $base.'/payroll/payslips'; ?>" style="color:#6b7280; text-decoration:none;">Payslips</a> /
                <span style="color:#111827;"><?php echo htmlspecialchars($payslip->reference ?? 'PS-'.$payslip->id); ?></span>
            </div>
            <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;">
                Payslip — <?php echo htmlspecialchars($payslip->employee_name ?? '—'); ?>
            </h1>
            <div style="font-size:13px; color:#6b7280; margin-top:4px;">
                <?php echo date('d M', strtotime($payslip->date_from)).' – '.date('d M Y', strtotime($payslip->date_to)); ?>
                · <?php echo htmlspecialchars($payslip->company_name ?? ''); ?>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">

            <?php /* ── Primary workflow buttons ── */ ?>
            <?php if ($payslip->state === 'draft'): ?>
            <button type="button" class="btn btn-default" style="border-radius:6px;" onclick="document.getElementById('edit-panel').style.display=document.getElementById('edit-panel').style.display==='none'?'block':'none'">
                <i class="fa fa-pencil"></i> Edit
            </button>
            <form method="post" action="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/compute'; ?>" style="display:inline">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-primary" style="border-radius:6px;"><i class="fa fa-cogs"></i> Compute</button>
            </form>
            <?php elseif ($payslip->state === 'computed'): ?>
            <form method="post" action="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/confirm'; ?>" style="display:inline">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-check"></i> Confirm & Post to Books</button>
            </form>
            <?php elseif (in_array($payslip->state, ['confirmed','done','paid'])): ?>
            <a href="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/pdf'; ?>" class="btn btn-default" style="border-radius:6px;" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <a href="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/email'; ?>" class="btn btn-default" style="border-radius:6px;"><i class="fa fa-envelope-o"></i> Email</a>
            <?php if (!empty($payslip->journal_entry_id)): ?>
            <a href="<?php echo admin_url('xetuu_books/journal_entry/'.$payslip->journal_entry_id); ?>" class="btn btn-default" style="border-radius:6px; border-color:#3b82f6; color:#3b82f6;" target="_blank">
                <i class="fa fa-book"></i> Journal Entry #<?php echo $payslip->journal_entry_id; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php /* ── Secondary / destructive actions ── */ ?>
            <?php if (in_array($payslip->state, ['computed','confirmed','done'])): ?>
            <form method="post" action="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/reset'; ?>" style="display:inline">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-default" style="border-radius:6px; border-color:#f59e0b; color:#92400e;"
                    onclick="return confirm('Reset to Draft?\n<?php echo $payslip->state === 'confirmed' ? 'This will also void the journal entry in Xetuu Books.' : ''; ?>')">
                    <i class="fa fa-undo"></i> Reset to Draft
                </button>
            </form>
            <?php endif; ?>

            <?php if (in_array($payslip->state, ['draft','computed'])): ?>
            <form method="post" action="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/delete'; ?>" style="display:inline">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-danger btn-xs" style="border-radius:6px; margin-left:4px;"
                    onclick="return confirm('Delete this payslip permanently? This cannot be undone.')">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </form>
            <?php endif; ?>

        </div>
    </div>

    <!-- Edit panel — only shown in draft state, toggled by Edit button -->
    <?php if ($payslip->state === 'draft'): ?>
    <div id="edit-panel" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:20px;">
        <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px;"><i class="fa fa-pencil"></i> Edit Payslip</div>
        <form method="post" action="<?php echo $base.'/payroll/payslips/'.$payslip->id.'/edit'; ?>">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#374151;">Period From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $payslip->date_from; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#374151;">Period To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $payslip->date_to; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#374151;">Salary Structure</label>
                        <select name="structure_id" class="form-control">
                            <option value="">— Keep current —</option>
                            <?php foreach ($structures as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo $payslip->structure_id == $s->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#374151;">Worked Days <span style="color:#9ca3af;">(leave blank = full period)</span></label>
                        <input type="number" name="worked_days" class="form-control" value="<?php echo $payslip->worked_days ?? ''; ?>" min="0" step="0.5">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#374151;">Notes</label>
                <input type="text" name="notes" class="form-control" value="<?php echo htmlspecialchars($payslip->notes ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="border-radius:6px;"><i class="fa fa-save"></i> Save Changes</button>
            <button type="button" class="btn btn-default btn-sm" style="border-radius:6px;" onclick="document.getElementById('edit-panel').style.display='none'">Cancel</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- State stepper -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:20px; display:flex; gap:0; overflow:hidden; justify-content:space-between;">
        <?php foreach ($states as $i => $st): ?>
        <?php $active = $i <= $cur_idx; $current = $i === $cur_idx; ?>
        <div style="flex:1; text-align:center; padding:10px 8px; background:<?php echo $active ? '#f0fdf4' : '#f9fafb'; ?>; border-right:<?php echo $i < count($states)-1 ? '1px solid #e5e7eb' : 'none'; ?>;">
            <div style="width:24px; height:24px; border-radius:50%; background:<?php echo $active ? '#16a34a' : '#e5e7eb'; ?>; color:<?php echo $active ? '#fff' : '#9ca3af'; ?>; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; margin-bottom:4px;">
                <?php echo $active ? ($current ? '●' : '✓') : ($i+1); ?>
            </div>
            <div style="font-size:10px; font-weight:700; text-transform:uppercase; color:<?php echo $current ? '#16a34a' : ($active ? '#374151' : '#9ca3af'); ?>;"><?php echo $st; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Payslip Lines -->
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6; font-size:13px; font-weight:700; color:#111827;">Payslip Lines</div>
                <?php if (empty($lines)): ?>
                <div style="padding:40px; text-align:center; color:#9ca3af; font-size:13px;">
                    <?php if ($payslip->state === 'draft'): ?>No lines yet — click <strong>Compute</strong> to generate.
                    <?php else: ?>No lines found.<?php endif; ?>
                </div>
                <?php else: ?>
                <?php
                $grouped = [];
                foreach ($lines as $l) {
                    if (abs((float)$l->amount) < 0.001 && $l->category !== 'NET') continue;
                    $grouped[$l->category][] = $l;
                }
                $order = ['EARN','DED','TAX','EMPLOYER','NET','LOAN'];
                ?>
                <?php foreach ($order as $cat): ?>
                <?php if (empty($grouped[$cat])) continue; ?>
                <?php $cc = $cat_colors[$cat] ?? '#6b7280'; ?>
                <div style="padding:8px 16px; background:<?php echo $cc; ?>10; border-bottom:1px solid <?php echo $cc; ?>20; font-size:11px; font-weight:700; color:<?php echo $cc; ?>; text-transform:uppercase; letter-spacing:.5px;"><?php echo $cat; ?></div>
                <?php foreach ($grouped[$cat] as $l): ?>
                <div style="display:flex; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #f9fafb;">
                    <div>
                        <span style="font-size:13px; color:#374151;"><?php echo htmlspecialchars($l->rule_name ?? ''); ?></span>
                        <?php if (!empty($l->rule_code)): ?>
                        <code style="font-size:10px; background:#f3f4f6; padding:1px 5px; border-radius:3px; margin-left:6px; color:#6b7280;"><?php echo htmlspecialchars($l->rule_code); ?></code>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:13px; font-weight:600; color:<?php echo in_array($cat,['DED','TAX','LOAN']) ? '#dc2626' : '#111827'; ?>;">
                        <?php echo in_array($cat,['DED','TAX','LOAN']) ? '−' : ''; ?><?php echo number_format($l->amount, 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary Card -->
            <div style="background:#1e3a5f; border-radius:10px; padding:22px; color:#fff; margin-bottom:16px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#93c5fd; margin-bottom:16px;">Pay Summary</div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:13px; color:#cbd5e1;">Gross Salary</span>
                    <span style="font-size:13px; font-weight:700;"><?php echo number_format($payslip->gross_salary, 2); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:13px; color:#cbd5e1;">Total Deductions</span>
                    <span style="font-size:13px; font-weight:700; color:#fca5a5;">−<?php echo number_format($payslip->total_deductions, 2); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:13px; color:#cbd5e1;">Tax (PAYE)</span>
                    <span style="font-size:13px; font-weight:700; color:#fca5a5;">−<?php echo number_format($payslip->total_tax, 2); ?></span>
                </div>
                <div style="border-top:1px solid #2d5a8e; padding-top:12px; margin-top:12px; display:flex; justify-content:space-between; align-items:baseline;">
                    <span style="font-size:14px; font-weight:700; color:#93c5fd;">Net Pay</span>
                    <span style="font-size:20px; font-weight:800; color:#fff;"><?php echo number_format($payslip->net_salary, 2); ?></span>
                </div>
                <?php
                // Sum LOAN lines to show loan deductions and cash payable
                $loan_total = 0;
                if (!empty($lines)) {
                    foreach ($lines as $_l) {
                        if ($_l->category === 'LOAN') { $loan_total += (float)$_l->amount; }
                    }
                }
                ?>
                <?php if ($loan_total > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-top:8px;">
                    <span style="font-size:12px; color:#fcd34d;">Loan Deductions</span>
                    <span style="font-size:12px; font-weight:700; color:#fcd34d;">−<?php echo number_format($loan_total, 2); ?></span>
                </div>
                <div style="border-top:1px solid #2d5a8e; padding-top:10px; margin-top:10px; display:flex; justify-content:space-between; align-items:baseline;">
                    <span style="font-size:14px; font-weight:700; color:#6ee7b7;">Cash Payable</span>
                    <span style="font-size:18px; font-weight:800; color:#6ee7b7;">
                        <?php echo number_format(max(0, (float)$payslip->net_salary - $loan_total), 2); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Employee Info -->
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px;">
                <div style="font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase; margin-bottom:12px;">Employee Details</div>
                <div style="font-size:13px; color:#374151; margin-bottom:6px;"><strong><?php echo htmlspecialchars($payslip->employee_name ?? '—'); ?></strong></div>
                <?php if (!empty($payslip->tax_id)): ?>
                <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Tax ID: <strong><?php echo htmlspecialchars($payslip->tax_id); ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($payslip->payment_method)): ?>
                <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Payment: <strong style="text-transform:capitalize;"><?php echo $payslip->payment_method; ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($payslip->bank_account)): ?>
                <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Account: <strong><?php echo htmlspecialchars($payslip->bank_account); ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($payslip->notes)): ?>
                <div style="font-size:12px; color:#9ca3af; margin-top:8px; font-style:italic;"><?php echo htmlspecialchars($payslip->notes); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
