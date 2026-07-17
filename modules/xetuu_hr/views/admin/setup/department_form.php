<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'setup';
$base        = admin_url('xetuu_hr');
$is_edit     = isset($dept) && $dept;
$d           = $dept;

if (!function_exists('dfv')) {
    function dfv($d, $key, $default = '') {
        if (!$d) return $default;
        return htmlspecialchars($d->$key ?? $default);
    }
}
if (!function_exists('dfv_sel')) {
    function dfv_sel($d, $key, $val) {
        if (!$d) return '';
        return (($d->$key ?? '') == $val) ? ' selected' : '';
    }
}

$approvers_raw = isset($approvers) ? $approvers : [];
$approvers = [
    'shift_request' => isset($approvers_raw['shift_request']) ? (array)$approvers_raw['shift_request'] : [],
    'leave'         => isset($approvers_raw['leave'])         ? (array)$approvers_raw['leave']         : [],
    'expense'       => isset($approvers_raw['expense'])       ? (array)$approvers_raw['expense']       : [],
];
$cost_centers = isset($cost_centers) ? $cost_centers : [];
$departments = isset($departments) ? $departments : [];
$companies   = isset($companies)   ? $companies   : [];
$staff_list  = isset($staff_list)  ? $staff_list  : [];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-dept-form-page">

    <!-- ── Sticky Topbar ─────────────────────────────────────────────────── -->
    <div class="xhr-dept-form-topbar" id="xhr-dept-topbar">
        <div class="xhr-dept-form-topbar__left">
            <nav class="xhr-dept-breadcrumb">
                <a href="<?php echo $base; ?>"><span class="material-symbols-outlined">desktop_windows</span></a>
                <span>/</span>
                <span>People</span>
                <span>/</span>
                <a href="<?php echo $base . '/setup/department'; ?>">Department</a>
                <span>/</span>
                <strong id="dept-breadcrumb-name"><?php echo $is_edit ? dfv($d,'name') : 'New Department'; ?></strong>
            </nav>
            <div class="xhr-dept-form-status <?php echo $is_edit ? 'xhr-dept-form-status--saved' : 'xhr-dept-form-status--unsaved'; ?>" id="dept-save-status">
                <?php echo $is_edit ? 'Saved' : 'Not Saved'; ?>
            </div>
        </div>
        <div class="xhr-dept-form-topbar__right">
            <?php if ($is_edit): ?>
            <a href="<?php echo $base . '/setup/department/delete/' . $d->id; ?>"
               class="xhr-btn xhr-btn--outline xhr-btn--danger-outline"
               onclick="return confirm('Delete this department?')">
                <span class="material-symbols-outlined">delete</span>
                Delete
            </a>
            <?php endif; ?>
            <button type="submit" form="xhr-dept-form" class="xhr-dept-save-btn">
                <span class="material-symbols-outlined">save</span>
                Save
            </button>
        </div>
    </div>

    <!-- ── Form ──────────────────────────────────────────────────────────── -->
    <form id="xhr-dept-form" method="post"
          action="<?php echo $base . '/setup/department'; ?>">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($is_edit): ?>
        <input type="hidden" name="dept_id" value="<?php echo $d->id; ?>">
        <?php endif; ?>

        <div class="xhr-dept-form-body">

            <!-- ── Section: Core Info ───────────────────────────────────── -->
            <div class="xhr-dept-form-card">

                <div class="xhr-dept-form-grid">

                    <!-- Department Name -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label xhr-dept-label--req">Department</label>
                        <input type="text" name="name" id="dept-name-input"
                               class="xhr-dept-input"
                               value="<?php echo dfv($d,'name'); ?>"
                               placeholder="e.g. Finance and Accounts"
                               required>
                    </div>

                    <!-- Company -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label xhr-dept-label--req">Company</label>
                        <select name="company_id" class="xhr-dept-select" required>
                            <option value="">— Select Company —</option>
                            <?php foreach ($companies as $c): ?>
                            <option value="<?php echo $c->id; ?>"<?php echo dfv_sel($d,'company_id',$c->id); ?>>
                                <?php echo htmlspecialchars($c->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Parent Department -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label">Parent Department</label>
                        <select name="parent_id" class="xhr-dept-select">
                            <option value="">— None —</option>
                            <?php foreach ($departments as $pd):
                                if ($is_edit && $pd->id == $d->id) continue;
                            ?>
                            <option value="<?php echo $pd->id; ?>"<?php echo dfv_sel($d,'parent_id',$pd->id); ?>>
                                <?php echo htmlspecialchars($pd->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Manager -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label">Manager</label>
                        <select name="manager_id" class="xhr-dept-select">
                            <option value="">— Select Manager —</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['staffid']; ?>"<?php echo dfv_sel($d,'manager_id',$s['staffid']); ?>>
                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div><!-- /.xhr-dept-form-grid -->

                <!-- Divider -->
                <div class="xhr-dept-form-divider"></div>

                <div class="xhr-dept-form-grid">

                    <!-- Payroll Cost Center -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label">Payroll Cost Center</label>
                        <select name="payroll_cost_center" class="xhr-dept-select">
                            <option value="">— Select Cost Centre —</option>
                            <?php foreach ($cost_centers as $cc): ?>
                            <option value="<?php echo $cc->id; ?>"
                                <?php echo dfv($d,'payroll_cost_center') == $cc->id ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars(($cc->code ? '[' . $cc->code . '] ' : '') . $cc->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($cost_centers)): ?>
                        <div class="xhr-dept-field-hint">
                            <a href="<?php echo admin_url('xetuu_books/config/analytic_accounts'); ?>" target="_blank" style="color:var(--xhr-primary);">
                                Set up Cost Centres in Xetuu Books →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Leave Block List -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label">Leave Block List</label>
                        <input type="text" name="leave_block_list" class="xhr-dept-input"
                               value="<?php echo dfv($d,'leave_block_list'); ?>"
                               placeholder="e.g. Public Holidays 2025">
                        <div class="xhr-dept-field-hint">Days for which Holidays are blocked for this department.</div>
                    </div>

                    <!-- Is Group -->
                    <div class="xhr-dept-form-field">
                        <label class="xhr-dept-label">&nbsp;</label>
                        <label class="xhr-dept-checkbox-row">
                            <input type="checkbox" name="is_group" value="1"
                                   <?php echo ($is_edit && $d->is_group) ? 'checked' : ''; ?>>
                            <span class="xhr-dept-checkbox-box"></span>
                            <span>Is Group</span>
                        </label>
                        <label class="xhr-dept-checkbox-row" style="margin-top:8px;">
                            <input type="checkbox" name="disabled" value="1"
                                   <?php echo ($is_edit && $d->disabled) ? 'checked' : ''; ?>>
                            <span class="xhr-dept-checkbox-box"></span>
                            <span>Disabled</span>
                        </label>
                    </div>

                </div>

            </div><!-- /.xhr-dept-form-card -->

            <!-- ── Section: Approvers ────────────────────────────────────── -->
            <div class="xhr-dept-form-card" style="margin-top:16px;">

                <div class="xhr-dept-section-title">Approvers</div>
                <p class="xhr-dept-section-desc">The first Approver in the list will be set as the default Approver.</p>

                <!-- Shift Request Approver -->
                <div class="xhr-dept-approver-block">
                    <div class="xhr-dept-approver-label">Shift Request Approver</div>
                    <div class="xhr-erp-table-wrap">
                        <table class="xhr-erp-table" id="shift-approver-table">
                            <thead>
                                <tr>
                                    <th class="xhr-erp-table__check">
                                        <input type="checkbox" onchange="xhrCheckAll(this,'shift-chk')">
                                    </th>
                                    <th class="xhr-erp-table__num">No.</th>
                                    <th>Approver <span style="color:#ef4444;">*</span></th>
                                    <th class="xhr-erp-table__act">
                                        <span class="material-symbols-outlined" style="font-size:16px;color:#9ca3af;">settings</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="shift-approver-body">
                                <?php foreach ($approvers['shift_request'] as $i => $a): ?>
                                <tr>
                                    <td class="xhr-erp-table__check"><input type="checkbox" class="shift-chk"></td>
                                    <td class="xhr-erp-table__num"><?php echo $i+1; ?></td>
                                    <td>
                                        <select name="shift_approver_id[]" class="xhr-dept-select-inline">
                                            <option value="">— Select —</option>
                                            <?php foreach ($staff_list as $s): ?>
                                            <option value="<?php echo $s['staffid']; ?>"<?php echo $s['staffid'] == $a->approver_id ? ' selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="xhr-erp-table__act">
                                        <button type="button" class="xhr-erp-edit-btn" onclick="xhrDelApproverRow(this,'shift-approver-body')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($approvers['shift_request'])): ?>
                                <tr class="xhr-erp-empty-row">
                                    <td colspan="4" style="text-align:center;color:#9ca3af;padding:14px;">No rows</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="xhr-addrow-btn" onclick="xhrAddApproverRow('shift-approver-body','shift_approver_id','shift-chk')">
                        Add row
                    </button>
                </div>

                <!-- Leave Approver -->
                <div class="xhr-dept-approver-block">
                    <div class="xhr-dept-approver-label">Leave Approver</div>
                    <div class="xhr-erp-table-wrap">
                        <table class="xhr-erp-table" id="leave-approver-table">
                            <thead>
                                <tr>
                                    <th class="xhr-erp-table__check">
                                        <input type="checkbox" onchange="xhrCheckAll(this,'leave-chk')">
                                    </th>
                                    <th class="xhr-erp-table__num">No.</th>
                                    <th>Approver <span style="color:#ef4444;">*</span></th>
                                    <th class="xhr-erp-table__act">
                                        <span class="material-symbols-outlined" style="font-size:16px;color:#9ca3af;">settings</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="leave-approver-body">
                                <?php foreach ($approvers['leave'] as $i => $a): ?>
                                <tr>
                                    <td class="xhr-erp-table__check"><input type="checkbox" class="leave-chk"></td>
                                    <td class="xhr-erp-table__num"><?php echo $i+1; ?></td>
                                    <td>
                                        <select name="leave_approver_id[]" class="xhr-dept-select-inline">
                                            <option value="">— Select —</option>
                                            <?php foreach ($staff_list as $s): ?>
                                            <option value="<?php echo $s['staffid']; ?>"<?php echo $s['staffid'] == $a->approver_id ? ' selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="xhr-erp-table__act">
                                        <button type="button" class="xhr-erp-edit-btn" onclick="xhrDelApproverRow(this,'leave-approver-body')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($approvers['leave'])): ?>
                                <tr class="xhr-erp-empty-row">
                                    <td colspan="4" style="text-align:center;color:#9ca3af;padding:14px;">No rows</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="xhr-addrow-btn" onclick="xhrAddApproverRow('leave-approver-body','leave_approver_id','leave-chk')">
                        Add row
                    </button>
                </div>

                <!-- Expense Approver -->
                <div class="xhr-dept-approver-block">
                    <div class="xhr-dept-approver-label">Expense Approver</div>
                    <div class="xhr-erp-table-wrap">
                        <table class="xhr-erp-table" id="expense-approver-table">
                            <thead>
                                <tr>
                                    <th class="xhr-erp-table__check">
                                        <input type="checkbox" onchange="xhrCheckAll(this,'expense-chk')">
                                    </th>
                                    <th class="xhr-erp-table__num">No.</th>
                                    <th>Approver <span style="color:#ef4444;">*</span></th>
                                    <th class="xhr-erp-table__act">
                                        <span class="material-symbols-outlined" style="font-size:16px;color:#9ca3af;">settings</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="expense-approver-body">
                                <?php foreach ($approvers['expense'] as $i => $a): ?>
                                <tr>
                                    <td class="xhr-erp-table__check"><input type="checkbox" class="expense-chk"></td>
                                    <td class="xhr-erp-table__num"><?php echo $i+1; ?></td>
                                    <td>
                                        <select name="expense_approver_id[]" class="xhr-dept-select-inline">
                                            <option value="">— Select —</option>
                                            <?php foreach ($staff_list as $s): ?>
                                            <option value="<?php echo $s['staffid']; ?>"<?php echo $s['staffid'] == $a->approver_id ? ' selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="xhr-erp-table__act">
                                        <button type="button" class="xhr-erp-edit-btn" onclick="xhrDelApproverRow(this,'expense-approver-body')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($approvers['expense'])): ?>
                                <tr class="xhr-erp-empty-row">
                                    <td colspan="4" style="text-align:center;color:#9ca3af;padding:14px;">No rows</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="xhr-addrow-btn" onclick="xhrAddApproverRow('expense-approver-body','expense_approver_id','expense-chk')">
                        Add row
                    </button>
                </div>

            </div><!-- /approvers card -->

        </div><!-- /.xhr-dept-form-body -->
    </form>

</div><!-- /.xhr-dept-form-page -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<script>
var xhrStaffOptions = <?php
    $opts = [];
    foreach ($staff_list as $s) {
        $opts[] = ['id' => $s['staffid'], 'name' => $s['firstname'] . ' ' . $s['lastname']];
    }
    echo json_encode($opts);
?>;

function xhrStaffSelectHtml(fieldName, checkClass) {
    var opts = '<option value="">— Select —</option>';
    xhrStaffOptions.forEach(function(s) {
        opts += '<option value="' + s.id + '">' + s.name + '</option>';
    });
    return '<select name="' + fieldName + '[]" class="xhr-dept-select-inline">' + opts + '</select>';
}

function xhrCheckAll(master, cls) {
    document.querySelectorAll('.' + cls).forEach(function(cb) {
        cb.checked = master.checked;
    });
}

function xhrAddApproverRow(tbodyId, fieldName, checkClass) {
    var tbody = document.getElementById(tbodyId);
    // Remove "No rows" placeholder if present
    var emptyRow = tbody.querySelector('.xhr-erp-empty-row');
    if (emptyRow) emptyRow.remove();
    var n = tbody.rows.length + 1;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td class="xhr-erp-table__check"><input type="checkbox" class="' + checkClass + '"></td>' +
        '<td class="xhr-erp-table__num">' + n + '</td>' +
        '<td>' + xhrStaffSelectHtml(fieldName, checkClass) + '</td>' +
        '<td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn" onclick="xhrDelApproverRow(this,\'' + tbodyId + '\')">' +
        '<span class="material-symbols-outlined">delete</span></button></td>';
    tbody.appendChild(tr);
    // Update "Not Saved" indicator
    document.getElementById('dept-save-status').textContent = 'Not Saved';
    document.getElementById('dept-save-status').className = 'xhr-dept-form-status xhr-dept-form-status--unsaved';
}

function xhrDelApproverRow(btn, tbodyId) {
    var tr    = btn.closest('tr');
    var tbody = document.getElementById(tbodyId);
    tr.remove();
    // Renumber
    Array.from(tbody.rows).forEach(function(row, i) {
        var num = row.querySelector('.xhr-erp-table__num');
        if (num) num.textContent = i + 1;
    });
    // Show empty row if no rows left
    if (tbody.rows.length === 0) {
        tbody.innerHTML = '<tr class="xhr-erp-empty-row"><td colspan="4" style="text-align:center;color:#9ca3af;padding:14px;">No rows</td></tr>';
    }
    document.getElementById('dept-save-status').textContent = 'Not Saved';
    document.getElementById('dept-save-status').className = 'xhr-dept-form-status xhr-dept-form-status--unsaved';
}

// Live breadcrumb name update
var nameInput = document.getElementById('dept-name-input');
var breadcrumbName = document.getElementById('dept-breadcrumb-name');
if (nameInput && breadcrumbName) {
    nameInput.addEventListener('input', function() {
        breadcrumbName.textContent = this.value || 'New Department';
        document.getElementById('dept-save-status').textContent = 'Not Saved';
        document.getElementById('dept-save-status').className = 'xhr-dept-form-status xhr-dept-form-status--unsaved';
    });
}
</script>

<?php init_tail(); ?>
