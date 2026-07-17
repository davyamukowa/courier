<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.contract-line-row { display:flex; gap:8px; align-items:center; margin-bottom:6px; }
.contract-line-row input, .contract-line-row select { font-size:12px; }
</style>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;"><a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / <span style="color:#111827;">Contracts</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payroll Contracts</h1>
        </div>
        <a href="<?php echo $base.'/payroll/contracts/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New Contract</a>
    </div>

    <?php if (!$show_form): ?>
    <!-- Contracts List -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
        <div style="padding:12px 16px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:12px;">
            <input type="text" id="contract-search" class="form-control" placeholder="Search by employee name..." style="max-width:280px; font-size:13px;">
            <select id="contract-status-filter" class="form-control" style="max-width:140px; font-size:13px;">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <table class="table table-hover" style="margin:0;">
            <thead><tr style="background:#f9fafb;">
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Employee</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Structure</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Basic Salary</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Start Date</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">End Date</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                <th></th>
            </tr></thead>
            <tbody id="contracts-tbody">
            <?php if (empty($contracts)): ?>
            <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No contracts yet. <a href="<?php echo $base.'/payroll/contracts/add'; ?>" style="color:#2563eb;">Add first contract</a>.</td></tr>
            <?php else: ?>
            <?php foreach ($contracts as $c): ?>
            <?php
            $status_colors = ['active'=>['#f0fdf4','#16a34a'],'expired'=>['#fef2f2','#dc2626'],'draft'=>['#fef9c3','#ca8a04']];
            [$sbg,$sfg] = $status_colors[$c->status] ?? ['#f3f4f6','#6b7280'];
            ?>
            <tr data-name="<?php echo strtolower(htmlspecialchars($c->employee_name ?? '')); ?>" data-status="<?php echo $c->status; ?>">
                <td style="padding:10px 16px;">
                    <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($c->employee_name ?? 'Unknown'); ?></div>
                    <?php if (!empty($c->company_name)): ?>
                    <div style="font-size:11px; color:#9ca3af;"><?php echo htmlspecialchars($c->company_name); ?></div>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px; color:#374151;"><?php echo htmlspecialchars($c->structure_name ?? '—'); ?></td>
                <td style="font-size:13px; font-weight:600; color:#111827;"><?php echo number_format($c->basic_salary, 2); ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo $c->date_start; ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo $c->date_end ?: '—'; ?></td>
                <td><span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $sbg; ?>; color:<?php echo $sfg; ?>; text-transform:uppercase;"><?php echo $c->status; ?></span></td>
                <td style="text-align:right; padding-right:12px;">
                    <a href="<?php echo $base.'/payroll/contracts/'.$c->id.'/edit'; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <!-- Add / Edit Form -->
    <form action="<?php echo $base.'/payroll/contracts'; ?>" method="post" id="contract-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($edit_contract): ?><input type="hidden" name="contract_id" value="<?php echo $edit_contract->id; ?>"><?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; margin-bottom:20px;">
                    <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Contract Details</div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">— Select Employee —</option>
                            <?php foreach ($employees as $e): ?>
                            <option value="<?php echo $e->id; ?>"<?php echo ($edit_contract->employee_id ?? '') == $e->id ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($e->first_name.' '.$e->last_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Payroll Company *</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">— Select Company —</option>
                            <?php foreach ($companies as $co): ?>
                            <option value="<?php echo $co->id; ?>"<?php echo ($edit_contract->company_id ?? '') == $co->id ? ' selected' : ''; ?>><?php echo htmlspecialchars($co->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Salary Structure *</label>
                        <select name="salary_structure_id" class="form-control" required>
                            <option value="">— Select Structure —</option>
                            <?php foreach ($structures as $s): ?>
                            <option value="<?php echo $s->id; ?>"<?php echo ($edit_contract->salary_structure_id ?? '') == $s->id ? ' selected' : ''; ?>><?php echo htmlspecialchars($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Basic Salary *</label>
                        <div class="input-group">
                            <span class="input-group-addon" style="font-size:12px; font-weight:600;">KES</span>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" min="0" required value="<?php echo $edit_contract->basic_salary ?? ''; ?>" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Start Date *</label>
                                <input type="date" name="date_start" class="form-control" required value="<?php echo $edit_contract->date_start ?? date('Y-m-01'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">End Date</label>
                                <input type="date" name="date_end" class="form-control" value="<?php echo $edit_contract->date_end ?? ''; ?>">
                                <span style="font-size:11px; color:#9ca3af;">Leave blank for open-ended</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Working Days/Week</label>
                                <input type="number" name="working_days" class="form-control" min="1" max="7" value="<?php echo $edit_contract->working_days ?? 5; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="bank"<?php echo ($edit_contract->payment_method ?? 'bank') === 'bank' ? ' selected' : ''; ?>>Bank Transfer</option>
                                    <option value="mobile_money"<?php echo ($edit_contract->payment_method ?? '') === 'mobile_money' ? ' selected' : ''; ?>>Mobile Money</option>
                                    <option value="cash"<?php echo ($edit_contract->payment_method ?? '') === 'cash' ? ' selected' : ''; ?>>Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Bank / Account Details</label>
                        <input type="text" name="bank_account" class="form-control" value="<?php echo htmlspecialchars($edit_contract->bank_account ?? ''); ?>" placeholder="e.g. Equity Bank – 1234567890">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Employee Tax ID <span style="font-weight:400; text-transform:none;">(KRA PIN / TIN / SSN / RFC…)</span></label>
                        <input type="text" name="tax_id" class="form-control" value="<?php echo htmlspecialchars($edit_contract->tax_id ?? ''); ?>" placeholder="Country-specific tax identifier">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Benefits -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="font-size:14px; font-weight:700; color:#16a34a;">Benefits / Allowances</div>
                        <button type="button" onclick="addBenefitRow()" class="btn btn-xs btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:4px;">+ Add</button>
                    </div>
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:10px;">These values are available in salary formulas as <code>benefit['CODE']</code></div>
                    <div id="benefits-container">
                    <?php if (!empty($contract_lines)): ?>
                    <?php foreach ($contract_lines as $line): ?>
                    <?php if ($line->line_type !== 'benefit') continue; ?>
                    <div class="contract-line-row">
                        <input type="text" name="benefit_code[]" class="form-control" placeholder="CODE" style="width:90px; flex-shrink:0;" value="<?php echo htmlspecialchars($line->code); ?>">
                        <input type="text" name="benefit_name[]" class="form-control" placeholder="Name" style="flex:1;" value="<?php echo htmlspecialchars($line->name); ?>">
                        <input type="number" name="benefit_amount[]" class="form-control" placeholder="Amount" style="width:110px; flex-shrink:0;" step="0.01" value="<?php echo $line->amount; ?>">
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#dc2626; font-size:18px; cursor:pointer; padding:0 4px;">&times;</button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                    <div id="benefits-empty" style="font-size:12px; color:#9ca3af; text-align:center; padding:8px 0; <?php echo !empty($contract_lines) ? 'display:none' : ''; ?>">No benefits — click + Add</div>
                </div>

                <!-- Deductions -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="font-size:14px; font-weight:700; color:#dc2626;">Deductions</div>
                        <button type="button" onclick="addDeductionRow()" class="btn btn-xs btn-danger" style="border-radius:4px;">+ Add</button>
                    </div>
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:10px;">Available as <code>deduction['CODE']</code> in salary formulas</div>
                    <div id="deductions-container">
                    <?php if (!empty($contract_lines)): ?>
                    <?php foreach ($contract_lines as $line): ?>
                    <?php if ($line->line_type !== 'deduction') continue; ?>
                    <div class="contract-line-row">
                        <input type="text" name="deduction_code[]" class="form-control" placeholder="CODE" style="width:90px; flex-shrink:0;" value="<?php echo htmlspecialchars($line->code); ?>">
                        <input type="text" name="deduction_name[]" class="form-control" placeholder="Name" style="flex:1;" value="<?php echo htmlspecialchars($line->name); ?>">
                        <input type="number" name="deduction_amount[]" class="form-control" placeholder="Amount" style="width:110px; flex-shrink:0;" step="0.01" value="<?php echo $line->amount; ?>">
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#dc2626; font-size:18px; cursor:pointer; padding:0 4px;">&times;</button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                    <div id="deductions-empty" style="font-size:12px; color:#9ca3af; text-align:center; padding:8px 0; <?php echo !empty($contract_lines) ? 'display:none' : ''; ?>">No deductions — click + Add</div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-save"></i> <?php echo $edit_contract ? 'Update Contract' : 'Create Contract'; ?></button>
                    <a href="<?php echo $base.'/payroll/contracts'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <?php if ($edit_contract): ?>
    <!-- Employee Loans section -->
    <div style="margin-top:24px; padding-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <h3 style="font-size:14px; font-weight:700; color:#111827; margin:0;">
                Employee Loans
                <span id="loans-badge" style="font-size:11px; color:#6b7280; font-weight:400; margin-left:6px;"></span>
            </h3>
            <a href="<?php echo $base; ?>/payroll/loans/new?employee_id=<?php echo $edit_contract->employee_id; ?>"
               class="btn btn-sm btn-default" style="border-radius:6px; font-size:12px;">
                <i class="fa fa-plus"></i> Add Loan
            </a>
        </div>
        <div id="loans-table-wrap" style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
            <div style="padding:24px; text-align:center; color:#9ca3af; font-size:12px;">
                <i class="fa fa-spinner fa-spin"></i> Loading loans…
            </div>
        </div>
    </div>

    <script>
    (function(){
        var empId = <?php echo (int)$edit_contract->employee_id; ?>;
        var base  = '<?php echo $base; ?>';
        fetch(base + '/payroll/loans/employee_loans?employee_id=' + empId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var wrap  = document.getElementById('loans-table-wrap');
            var badge = document.getElementById('loans-badge');
            if (!d.success || !d.loans || d.loans.length === 0) {
                wrap.innerHTML = '<div style="padding:24px;text-align:center;color:#9ca3af;font-size:12px;">No loans for this employee. <a href="' + base + '/payroll/loans/new?employee_id=' + empId + '">Add one</a></div>';
                return;
            }
            badge.textContent = '(' + d.loans.length + ')';
            var sc = {active:'#16a34a',suspended:'#d97706',paid:'#6b7280',written_off:'#dc2626',cancelled:'#9ca3af'};
            var tl = {salary_advance:'Salary Advance',helb:'HELB',equipment:'Equipment',emergency:'Emergency',other:'Other'};
            var html = '<table class="table table-condensed" style="margin:0;font-size:12px;"><thead style="background:#f9fafb;"><tr>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;">Type</th>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;">Reference</th>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;text-align:right;">Principal</th>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;text-align:right;">Balance</th>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;text-align:right;">EMI/Mo</th>' +
                '<th style="padding:8px 14px;font-weight:600;color:#374151;">Status</th>' +
                '<th style="padding:8px 14px;"></th></tr></thead><tbody>';
            d.loans.forEach(function(l){
                var c = sc[l.status] || '#6b7280';
                html += '<tr style="border-top:1px solid #f3f4f6;">' +
                    '<td style="padding:7px 14px;color:#374151;">' + (tl[l.loan_type]||l.loan_type) + '</td>' +
                    '<td style="padding:7px 14px;color:#6b7280;">' + (l.loan_reference||'—') + '</td>' +
                    '<td style="padding:7px 14px;text-align:right;font-weight:600;">' + parseFloat(l.principal_amount).toLocaleString('en',{minimumFractionDigits:2}) + '</td>' +
                    '<td style="padding:7px 14px;text-align:right;font-weight:600;color:' + (parseFloat(l.balance_remaining)>0?'#dc2626':'#16a34a') + ';">' + parseFloat(l.balance_remaining).toLocaleString('en',{minimumFractionDigits:2}) + '</td>' +
                    '<td style="padding:7px 14px;text-align:right;">' + parseFloat(l.monthly_installment).toLocaleString('en',{minimumFractionDigits:2}) + '</td>' +
                    '<td style="padding:7px 14px;"><span style="padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;background:' + c + '18;color:' + c + ';">' + l.status.replace(/_/g,' ') + '</span></td>' +
                    '<td style="padding:7px 14px;text-align:right;"><a href="' + base + '/payroll/loans/' + l.id + '/statement" class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;">View</a></td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            wrap.innerHTML = html;
        })
        .catch(function(){ document.getElementById('loans-table-wrap').innerHTML = '<div style="padding:16px;text-align:center;color:#dc2626;font-size:12px;">Could not load loans.</div>'; });
    })();
    </script>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script>
function addBenefitRow() {
    document.getElementById('benefits-empty').style.display = 'none';
    const row = document.createElement('div');
    row.className = 'contract-line-row';
    row.innerHTML = '<input type="text" name="benefit_code[]" class="form-control" placeholder="CODE" style="width:90px;flex-shrink:0;">' +
        '<input type="text" name="benefit_name[]" class="form-control" placeholder="Name" style="flex:1;">' +
        '<input type="number" name="benefit_amount[]" class="form-control" placeholder="Amount" style="width:110px;flex-shrink:0;" step="0.01">' +
        '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;font-size:18px;cursor:pointer;padding:0 4px;">&times;</button>';
    document.getElementById('benefits-container').appendChild(row);
}
function addDeductionRow() {
    document.getElementById('deductions-empty').style.display = 'none';
    const row = document.createElement('div');
    row.className = 'contract-line-row';
    row.innerHTML = '<input type="text" name="deduction_code[]" class="form-control" placeholder="CODE" style="width:90px;flex-shrink:0;">' +
        '<input type="text" name="deduction_name[]" class="form-control" placeholder="Name" style="flex:1;">' +
        '<input type="number" name="deduction_amount[]" class="form-control" placeholder="Amount" style="width:110px;flex-shrink:0;" step="0.01">' +
        '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;font-size:18px;cursor:pointer;padding:0 4px;">&times;</button>';
    document.getElementById('deductions-container').appendChild(row);
}
// Search / filter
const searchInput = document.getElementById('contract-search');
const statusFilter = document.getElementById('contract-status-filter');
function filterContracts() {
    if (!searchInput) return;
    const q = searchInput.value.toLowerCase();
    const s = statusFilter.value;
    document.querySelectorAll('#contracts-tbody tr[data-name]').forEach(tr => {
        const nameMatch = tr.dataset.name.includes(q);
        const statusMatch = !s || tr.dataset.status === s;
        tr.style.display = nameMatch && statusMatch ? '' : 'none';
    });
}
if (searchInput) { searchInput.addEventListener('input', filterContracts); statusFilter.addEventListener('change', filterContracts); }
</script>
<?php init_tail(); ?>
