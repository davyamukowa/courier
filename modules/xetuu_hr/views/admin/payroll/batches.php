<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / <span style="color:#111827;">Payslip Batches</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payslip Batches</h1>
        </div>
        <a href="<?php echo $base.'/payroll/batches/new'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">add</span> NEW
        </a>
    </div>

    <?php if ($show_form): ?>
    <!-- New Batch Form -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; margin-bottom:24px; max-width:720px;">
        <div style="font-size:15px; font-weight:700; color:#111827; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
            <span class="material-symbols-outlined" style="vertical-align:-4px; color:#16a34a;">layers</span> New Payslip Batch
        </div>
        <form id="batch-form" action="<?php echo $base.'/payroll/batches/save'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <!-- hidden fields populated by the modal -->
            <input type="hidden" name="payroll_company_id" id="fld-payroll-company-id" value="">
            <div id="employee-ids-container"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Batch Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. June 2026 — All Staff"
                               value="<?php echo date('F Y'); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Company *</label>
                        <select name="hr_company_id" id="fld-hr-company" class="form-control" required>
                            <option value="">— Select Company —</option>
                            <?php foreach ($hr_companies as $co): ?>
                            <option value="<?php echo $co->id; ?>">
                                <?php echo htmlspecialchars($co->name); ?>
                                <?php echo empty($co->payroll_company_id) ? ' ⚠ (no payroll link)' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Filter by Department <span style="font-weight:400; text-transform:none;">(optional)</span></label>
                        <select name="department_id" id="fld-department" class="form-control">
                            <option value="">— All Departments —</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Date From *</label>
                        <input type="date" name="date_from" class="form-control" required value="<?php echo date('Y-m-01'); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Date To *</label>
                        <input type="date" name="date_to" class="form-control" required value="<?php echo date('Y-m-t'); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.04em;">Period (optional)</label>
                        <select name="period_id" class="form-control">
                            <option value="">— None / Auto —</option>
                            <?php foreach ($periods as $per): ?>
                            <option value="<?php echo $per->id; ?>"><?php echo htmlspecialchars($per->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <button type="button" id="btn-preview" class="btn btn-primary" style="background:#2563eb; border-color:#2563eb; border-radius:6px;">
                    <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">group</span> Preview Employees
                </button>
                <a href="<?php echo $base.'/payroll/batches'; ?>" class="btn btn-default" style="border-radius:6px;">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Batches List -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:14px; font-weight:700; color:#111827;">All Batches</span>
            <span style="font-size:12px; color:#9ca3af;"><?php echo count($runs); ?> run<?php echo count($runs) !== 1 ? 's' : ''; ?></span>
        </div>
        <?php if (empty($runs)): ?>
        <div style="padding:60px; text-align:center; color:#9ca3af;">
            <span class="material-symbols-outlined" style="font-size:42px; display:block; margin-bottom:10px;">layers</span>
            No payroll batches yet.
        </div>
        <?php else: ?>
        <table class="table table-hover" style="margin:0;">
            <thead><tr style="background:#f9fafb;">
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;"><input type="checkbox"></th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Name</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Company</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date From</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Date To</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Payslips</th>
                <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Net Pay</th>
            </tr></thead>
            <tbody>
            <?php
            $state_colors = [
                'draft'     => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>'New'],
                'computing' => ['bg'=>'#fef3c7','color'=>'#d97706','label'=>'Computing'],
                'computed'  => ['bg'=>'#eff6ff','color'=>'#2563eb','label'=>'Computed'],
                'confirmed' => ['bg'=>'#f0fdf4','color'=>'#16a34a','label'=>'Confirmed'],
                'done'      => ['bg'=>'#f0fdf4','color'=>'#16a34a','label'=>'Done'],
                'paid'      => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Paid'],
                'cancelled' => ['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Cancelled'],
            ];
            ?>
            <?php foreach ($runs as $run): ?>
            <?php $sc = $state_colors[$run->state] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>ucfirst($run->state)]; ?>
            <tr style="cursor:pointer;" onclick="location.href='<?php echo $base.'/payroll/batches/view/'.$run->id; ?>'">
                <td style="padding:10px 16px;" onclick="event.stopPropagation()"><input type="checkbox"></td>
                <td style="font-size:13px; font-weight:600; color:#111827; padding:10px 0;"><?php echo htmlspecialchars($run->name); ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($run->company_name ?? '—'); ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo date('d/m/Y', strtotime($run->date_from)); ?></td>
                <td style="font-size:12px; color:#6b7280;"><?php echo date('d/m/Y', strtotime($run->date_to)); ?></td>
                <td>
                    <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:4px; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                        <?php echo $sc['label']; ?>
                    </span>
                </td>
                <td style="font-size:13px; font-weight:600; color:#374151; text-align:center;"><?php echo number_format($run->employee_count); ?></td>
                <td style="font-size:13px; font-weight:700; color:#111827;"><?php echo ($run->currency ?? 'KES').' '.number_format($run->total_net, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ══ Employee Preview Modal ══════════════════════════════════════════════ -->
<div id="emp-preview-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:680px; max-width:96vw; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.25);">

        <!-- Modal header -->
        <div style="padding:18px 22px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div>
                <div style="font-size:16px; font-weight:700; color:#111827;">Generate Payslips</div>
                <div id="modal-subtitle" style="font-size:12px; color:#6b7280; margin-top:2px;">Select the employees to include in this batch</div>
            </div>
            <button onclick="closePreviewModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px; line-height:1;">&times;</button>
        </div>

        <!-- Loading / error states -->
        <div id="modal-loading" style="padding:40px; text-align:center; color:#6b7280;">
            <div class="spinner" style="width:28px; height:28px; border:3px solid #e5e7eb; border-top-color:#2563eb; border-radius:50%; animation:spin .7s linear infinite; margin:0 auto 12px;"></div>
            Loading eligible employees…
        </div>
        <div id="modal-error" style="display:none; padding:24px; color:#dc2626; font-size:13px;"></div>

        <!-- Employee list -->
        <div id="modal-body" style="display:none; flex:1; overflow-y:auto; padding:0;">

            <!-- Toolbar: select-all + dept filter + search + counter -->
            <div style="padding:12px 20px; border-bottom:1px solid #f3f4f6; display:flex; gap:10px; align-items:center; flex-wrap:wrap; background:#f9fafb; flex-shrink:0;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:flex; align-items:center; gap:6px; cursor:pointer; white-space:nowrap;">
                    <input type="checkbox" id="chk-all" onchange="toggleAll(this.checked)"> Select All
                </label>
                <select id="modal-dept-filter" class="form-control" style="width:180px; height:30px; font-size:12px; padding:2px 8px;" onchange="filterDept(this.value)">
                    <option value="">All Departments</option>
                </select>
                <input type="text" id="modal-search" placeholder="Search name…" class="form-control"
                       style="width:160px; height:30px; font-size:12px; padding:2px 8px;"
                       oninput="filterSearch(this.value)">
                <span id="modal-counter" style="font-size:12px; color:#6b7280; margin-left:auto; white-space:nowrap;"></span>
            </div>

            <table class="table" style="margin:0;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="font-size:11px; color:#6b7280; padding:8px 16px; width:36px;"></th>
                        <th style="font-size:11px; color:#6b7280; text-transform:uppercase;">Employee</th>
                        <th style="font-size:11px; color:#6b7280; text-transform:uppercase;">Department</th>
                        <th style="font-size:11px; color:#6b7280; text-transform:uppercase;">Designation</th>
                        <th style="font-size:11px; color:#6b7280; text-transform:uppercase; text-align:right;">Basic Wage</th>
                    </tr>
                </thead>
                <tbody id="emp-list-body"></tbody>
            </table>
        </div>

        <!-- Modal footer -->
        <div id="modal-footer" style="display:none; padding:14px 20px; border-top:1px solid #f3f4f6; gap:10px; justify-content:flex-end; flex-shrink:0;">
            <button onclick="closePreviewModal()" class="btn btn-default" style="border-radius:6px;">Cancel</button>
            <button onclick="submitBatch()" id="btn-generate" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;" disabled>
                <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">check</span>
                <span id="btn-generate-label">Generate 0 Payslips</span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin { to { transform:rotate(360deg); } }
#emp-preview-modal.open { display:flex !important; }
.emp-row { transition:background .1s; }
.emp-row.hidden-row { display:none; }
.emp-row:hover td { background:#f9fafb; }
</style>

<script>
var PREVIEW_URL = '<?php echo $base.'/payroll/batches/preview_employees'; ?>';
var _allEmployees = [];

document.getElementById('btn-preview') && document.getElementById('btn-preview').addEventListener('click', function() {
    var hrCo = document.getElementById('fld-hr-company').value;
    var dept  = document.getElementById('fld-department').value;
    if (!hrCo) { alert('Please select a company first.'); return; }
    openPreviewModal(hrCo, dept);
});

function openPreviewModal(hrCoId, deptId) {
    var modal = document.getElementById('emp-preview-modal');
    modal.classList.add('open');
    document.getElementById('modal-loading').style.display = 'block';
    document.getElementById('modal-error').style.display   = 'none';
    document.getElementById('modal-body').style.display    = 'none';
    document.getElementById('modal-footer').style.display  = 'none';

    var url = PREVIEW_URL + '?hr_company_id=' + hrCoId + '&department_id=' + (deptId || '');
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(data) {
            document.getElementById('modal-loading').style.display = 'none';
            if (!data.success) {
                document.getElementById('modal-error').textContent = data.message || 'Error loading employees.';
                document.getElementById('modal-error').style.display = 'block';
                return;
            }
            _allEmployees = data.employees || [];
            document.getElementById('fld-payroll-company-id').value = data.payroll_company_id || '';
            renderEmployees(_allEmployees);
            document.getElementById('modal-body').style.display        = 'block';
            document.getElementById('modal-footer').style.display      = 'flex';
        })
        .catch(function(e) {
            document.getElementById('modal-loading').style.display = 'none';
            document.getElementById('modal-error').textContent = 'Network error: ' + e.message;
            document.getElementById('modal-error').style.display = 'block';
        });
}

function renderEmployees(employees) {
    var tbody  = document.getElementById('emp-list-body');
    var deptSel = document.getElementById('modal-dept-filter');
    var depts  = {};
    employees.forEach(function(e){ if (e.department_id) depts[e.department_id] = e.department_name || '—'; });

    // Rebuild dept filter (keep "All Departments")
    while (deptSel.options.length > 1) deptSel.remove(1);
    Object.keys(depts).forEach(function(id) {
        var opt = new Option(depts[id], id);
        deptSel.add(opt);
    });

    tbody.innerHTML = '';
    if (!employees.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">No employees with active payroll contracts found.</td></tr>';
        updateCounter();
        return;
    }
    employees.forEach(function(e) {
        var tr = document.createElement('tr');
        tr.className = 'emp-row';
        tr.setAttribute('data-dept', e.department_id || '');
        tr.setAttribute('data-name', (e.employee_name || '').toLowerCase());
        tr.innerHTML =
            '<td style="padding:8px 16px;"><input type="checkbox" class="emp-chk" value="'+e.employee_id+'" checked onchange="updateCounter()"></td>'+
            '<td style="font-size:13px;font-weight:600;color:#111827;padding:8px 0;">'+
                htmlEsc(e.employee_name || '—')+'<br>'+
                '<span style="font-size:11px;color:#9ca3af;">'+htmlEsc(e.employee_number || '')+'</span></td>'+
            '<td style="font-size:12px;color:#6b7280;">'+htmlEsc(e.department_name || '—')+'</td>'+
            '<td style="font-size:12px;color:#6b7280;">'+htmlEsc(e.designation_name || '—')+'</td>'+
            '<td style="font-size:13px;font-weight:600;color:#374151;text-align:right;padding-right:16px;">'+
                'KES '+numberFmt(e.wage)+'</td>';
        tbody.appendChild(tr);
    });
    document.getElementById('chk-all').checked = true;
    updateCounter();
}

function toggleAll(checked) {
    document.querySelectorAll('.emp-chk').forEach(function(chk) {
        var row = chk.closest('tr');
        if (!row.classList.contains('hidden-row')) chk.checked = checked;
    });
    updateCounter();
}

function filterDept(deptId) {
    document.querySelectorAll('.emp-row').forEach(function(row) {
        var match = !deptId || row.getAttribute('data-dept') === deptId;
        row.classList.toggle('hidden-row', !match);
    });
    updateCounter();
}

function filterSearch(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.emp-row').forEach(function(row) {
        var name = row.getAttribute('data-name') || '';
        row.classList.toggle('hidden-row', q && name.indexOf(q) === -1);
    });
    updateCounter();
}

function updateCounter() {
    var checked = document.querySelectorAll('.emp-chk:checked').length;
    document.getElementById('modal-counter').textContent = checked + ' / ' + _allEmployees.length + ' selected';
    var btn = document.getElementById('btn-generate');
    btn.disabled = checked === 0;
    document.getElementById('btn-generate-label').textContent = 'Generate ' + checked + ' Payslip' + (checked !== 1 ? 's' : '');
}

function submitBatch() {
    var ids = [];
    document.querySelectorAll('.emp-chk:checked').forEach(function(chk){ ids.push(chk.value); });
    if (!ids.length) { alert('No employees selected.'); return; }

    // Inject hidden employee_ids[] inputs into the form
    var container = document.getElementById('employee-ids-container');
    container.innerHTML = '';
    ids.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'employee_ids[]'; inp.value = id;
        container.appendChild(inp);
    });
    document.getElementById('batch-form').submit();
}

function closePreviewModal() {
    document.getElementById('emp-preview-modal').classList.remove('open');
}

document.getElementById('emp-preview-modal').addEventListener('click', function(e) {
    if (e.target === this) closePreviewModal();
});

function htmlEsc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function numberFmt(n) {
    return parseFloat(n || 0).toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>

<?php init_tail(); ?>
