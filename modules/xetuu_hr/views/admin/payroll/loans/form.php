<?php defined('BASEPATH') or exit('No direct script access allowed');
$base    = admin_url('xetuu_hr');
$is_edit = !empty($loan);
$loan_months = function($l) {
    if ($l && $l->disbursement_date && $l->expected_end_date) {
        $d1 = new DateTime($l->disbursement_date);
        $d2 = new DateTime($l->expected_end_date);
        $df = $d1->diff($d2);
        return max(1, $df->y * 12 + $df->m);
    }
    if ($l && $l->monthly_installment > 0) {
        return (int)ceil($l->principal_amount / $l->monthly_installment);
    }
    return 12;
};
$v       = function($field, $default = '') use ($loan) {
    return $loan ? htmlspecialchars($loan->$field ?? $default) : $default;
};
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:20px; max-width:740px;">

    <div style="margin-bottom:16px;">
        <div style="font-size:11px; color:#6b7280; margin-bottom:2px;">
            <a href="<?php echo $base; ?>/payroll/loans" style="color:#6b7280; text-decoration:none;">Loans</a>
            <?php if ($is_edit): ?> /
                <a href="<?php echo $base; ?>/payroll/loans/<?php echo $loan->id; ?>/statement"
                   style="color:#6b7280; text-decoration:none;"><?php echo htmlspecialchars($loan->employee_name ?? ''); ?></a>
            <?php endif; ?> / <?php echo $is_edit ? 'Edit' : 'New Loan'; ?>
        </div>
        <h1 style="font-size:18px; font-weight:700; color:#111827; margin:0;">
            <?php echo $is_edit ? 'Edit Loan' : 'New Employee Loan'; ?>
        </h1>
    </div>

    <form method="post" action="<?php echo $base; ?>/payroll/loans/save" id="loan-form">
        <?php if ($is_edit): ?>
        <input type="hidden" name="loan_id" value="<?php echo $loan->id; ?>">
        <?php endif; ?>

        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; display:grid;
                    grid-template-columns:1fr 1fr; gap:14px;">

            <!-- Employee -->
            <div style="grid-column:1/-1;">
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Employee *</label>
                <select name="employee_id" class="form-control" required id="lf-employee">
                    <option value="">— Select Employee —</option>
                    <?php foreach ($employees as $e): ?>
                    <option value="<?php echo $e->id; ?>" <?php echo (int)$preselect_emp === (int)$e->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($e->first_name . ' ' . $e->last_name . ' (' . $e->employee_number . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Loan Type -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Loan Type *</label>
                <select name="loan_type" class="form-control" required>
                    <?php
                    $types = ['salary_advance'=>'Salary Advance','helb'=>'HELB (Higher Education Loans Board)',
                              'equipment'=>'Equipment','emergency'=>'Emergency','other'=>'Other'];
                    foreach ($types as $k => $label):
                    ?>
                    <option value="<?php echo $k; ?>" <?php echo $v('loan_type') === $k ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Reference -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Reference / Loan No.</label>
                <input type="text" name="loan_reference" class="form-control" value="<?php echo $v('loan_reference'); ?>"
                       placeholder="e.g. HELB-2024-001">
            </div>

            <!-- Principal -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Principal Amount *</label>
                <input type="number" name="principal_amount" id="lf-principal" class="form-control" step="0.01" min="1" required
                       value="<?php echo $v('principal_amount','0'); ?>" oninput="recalcEmi()">
            </div>

            <!-- Interest Rate -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">
                    Annual Interest Rate (%)
                    <span style="font-size:10px; color:#9ca3af; font-weight:400; text-transform:none;">— enter 0 for interest-free</span>
                </label>
                <input type="number" name="interest_rate" id="lf-rate" class="form-control" step="0.01" min="0" max="100"
                       value="<?php echo $v('interest_rate','0'); ?>" oninput="recalcEmi()">
            </div>

            <!-- Months -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Repayment Period (months) *</label>
                <input type="number" name="months" id="lf-months" class="form-control" min="1" max="360" required
                       value="<?php echo $is_edit ? $loan_months($loan) : '12'; ?>" oninput="recalcEmi()">
            </div>

            <!-- EMI preview -->
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; display:flex; gap:24px; align-items:center;">
                <div>
                    <div style="font-size:10px; color:#166534; text-transform:uppercase; font-weight:600;">Monthly Installment</div>
                    <div style="font-size:20px; font-weight:800; color:#15803d;" id="emi-preview">—</div>
                </div>
                <div>
                    <div style="font-size:10px; color:#166534; text-transform:uppercase; font-weight:600;">Total Repayable</div>
                    <div style="font-size:16px; font-weight:700; color:#166534;" id="total-preview">—</div>
                </div>
            </div>

            <!-- Dates -->
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Disbursement Date</label>
                <input type="date" name="disbursement_date" class="form-control" value="<?php echo $v('disbursement_date'); ?>">
            </div>
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">First Deduction Month *</label>
                <input type="date" name="start_deduction_date" class="form-control" required
                       value="<?php echo $v('start_deduction_date', date('Y-m-01', strtotime('+1 month'))); ?>">
            </div>
            <div>
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Expected End Date</label>
                <input type="date" name="expected_end_date" id="lf-end-date" class="form-control" value="<?php echo $v('expected_end_date'); ?>">
            </div>

            <!-- Description / Notes -->
            <div style="grid-column:1/-1;">
                <label class="label-control" style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Notes</label>
                <textarea name="notes" class="form-control" rows="2" style="font-size:12px;"><?php echo $v('notes'); ?></textarea>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn btn-primary" style="border-radius:6px; font-size:13px;">
                <i class="fa fa-save"></i> <?php echo $is_edit ? 'Update Loan' : 'Create Loan'; ?>
            </button>
            <a href="<?php echo $base; ?>/payroll/loans" class="btn btn-default" style="border-radius:6px; font-size:13px;">Cancel</a>
        </div>
    </form>
</div>

<script>
var EMI_BASE = '<?php echo admin_url('xetuu_hr'); ?>/payroll/loans/compute_emi';

function recalcEmi() {
    var p = parseFloat(document.getElementById('lf-principal').value) || 0;
    var r = parseFloat(document.getElementById('lf-rate').value) || 0;
    var m = parseInt(document.getElementById('lf-months').value) || 0;
    if (p <= 0 || m <= 0) { return; }

    if (r === 0) {
        var emi = Math.round(p / m * 100) / 100;
        document.getElementById('emi-preview').textContent   = emi.toLocaleString('en', {minimumFractionDigits:2});
        document.getElementById('total-preview').textContent = p.toLocaleString('en', {minimumFractionDigits:2});
        updateEndDate(m);
        return;
    }

    fetch(EMI_BASE, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body: 'principal=' + p + '&interest_rate=' + r + '&months=' + m
    })
    .then(function(res){ return res.json(); })
    .then(function(d){
        if (d.success) {
            document.getElementById('emi-preview').textContent   = parseFloat(d.emi).toLocaleString('en',{minimumFractionDigits:2});
            document.getElementById('total-preview').textContent = parseFloat(d.total_repayable).toLocaleString('en',{minimumFractionDigits:2});
        }
    });
    updateEndDate(m);
}

function updateEndDate(months) {
    var startEl = document.querySelector('[name="start_deduction_date"]');
    var endEl   = document.getElementById('lf-end-date');
    if (!startEl || !endEl || !startEl.value) return;
    var d = new Date(startEl.value);
    d.setMonth(d.getMonth() + months - 1);
    endEl.value = d.toISOString().slice(0, 10);
}

document.addEventListener('DOMContentLoaded', function() { recalcEmi(); });
</script>
<?php init_tail(); ?>
