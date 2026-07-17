<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_edit   = isset($employee) && $employee && empty($is_prefill);
$emp       = $employee;
$form_action = admin_url('xetuu_hr/employees/save');

// Helper: get field value (edit mode pulls from $emp, add mode is empty)
if (!function_exists('fv')) {
    function fv($emp, $key, $default = '') {
        if (!$emp) return $default;
        return htmlspecialchars($emp->$key ?? $default);
    }
}
if (!function_exists('fv_select')) {
    function fv_select($emp, $key, $val) {
        if (!$emp) return '';
        return (($emp->$key ?? '') == $val) ? ' selected' : '';
    }
}
?>
<?php init_head(); ?>
<?php $xhr_active = 'employees'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-emp-form-page">

    <!-- ── Top info bar ────────────────────────────────────────────────── -->
    <div class="xhr-emp-form-topbar">
        <div class="xhr-emp-form-breadcrumb">
            <span>People</span>
            <span class="xhr-emp-form-breadcrumb__sep">/</span>
            <a href="<?php echo admin_url('xetuu_hr/employees'); ?>">Employees</a>
            <span class="xhr-emp-form-breadcrumb__sep">/</span>
            <span class="xhr-emp-form-breadcrumb__active">Employee</span>
        </div>
        <div class="xhr-emp-form-title-row">
            <div class="xhr-emp-form-identity">
                <div class="xhr-emp-form-number">
                    <span class="xhr-emp-form-number__id">
                        <?php echo $is_edit ? fv($emp, 'employee_number') : htmlspecialchars($next_number); ?>
                    </span>
                    <?php $st = $is_edit ? ($emp->status ?? 'Active') : 'Active'; ?>
                    <span class="xhr-badge <?php echo $st === 'Active' ? 'xhr-badge--active' : 'xhr-badge--inactive'; ?>">
                        <?php echo htmlspecialchars($st); ?>
                    </span>
                </div>
                <h1 class="xhr-emp-form-name <?php echo !$is_edit ? 'xhr-emp-form-name--placeholder' : ''; ?>"
                    id="xhr-emp-display-name">
                    <?php echo $is_edit ? htmlspecialchars($emp->first_name . ' ' . ($emp->middle_name ? $emp->middle_name . ' ' : '') . $emp->last_name) : 'New Employee'; ?>
                </h1>
            </div>
            <div class="xhr-emp-form-actions">
                <button type="submit" form="xhr-emp-form" class="xhr-btn xhr-btn--primary">
                    <span class="material-symbols-outlined">save</span> Save
                </button>
                <?php if ($is_edit): ?>
                <button type="button" class="xhr-btn xhr-btn--outline">
                    <span class="material-symbols-outlined">edit</span> Edit
                </button>
                <button type="button" class="xhr-btn xhr-btn--outline" onclick="window.print()">
                    <span class="material-symbols-outlined">print</span>
                </button>
                <button type="button" class="xhr-btn xhr-btn--outline xhr-btn--icon">
                    <span class="material-symbols-outlined">more_horiz</span>
                </button>
                <?php endif; ?>
                <!-- Photo upload -->
                <div class="xhr-emp-photo-wrap" title="Click to change photo">
                    <?php if ($is_edit && !empty($emp->photo)): ?>
                        <img id="xhr-photo-preview" src="<?php echo base_url($emp->photo); ?>" alt="">
                    <?php else: ?>
                        <span class="material-symbols-outlined xhr-emp-photo-wrap__placeholder">person</span>
                        <img id="xhr-photo-preview" src="" alt="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                    <?php endif; ?>
                    <input type="file" id="xhr-photo-input" name="photo" accept="image/*">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Form with tab/sidebar layout ──────────────────────────────── -->
    <form id="xhr-emp-form" action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php echo form_hidden('employee_id', $is_edit ? $emp->id : ''); ?>
        <?php if (!$is_edit && !empty($emp->applicant_id)): ?>
            <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($emp->applicant_id); ?>">
        <?php endif; ?>

        <div class="xhr-emp-form-body">

            <!-- ── Main tabs area ─────────────────────────────────────── -->
            <div class="xhr-emp-form-main">

                <!-- Tab bar with scroll arrows -->
                <div class="xhr-tabbar-wrap">
                    <button type="button" class="xhr-tabbar-arrow xhr-tabbar-arrow--left" id="tab-scroll-left" onclick="xhrScrollTabs(-1)">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <div class="xhr-emp-form-tabs" id="emp-form-tabs">
                        <button type="button" class="xhr-emp-form-tab xhr-emp-form-tab--active" data-panel="efp-overview">Overview</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-joining">Joining</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-contacts">Address &amp; Contacts</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-attendance">Attendance &amp; Leaves</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-salary">Salary</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-contract">Contract</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-personal">Personal Details</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-profile">Profile</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-exit">Employee Exit</button>
                        <button type="button" class="xhr-emp-form-tab" data-panel="efp-connections">Connections</button>
                    </div>
                    <button type="button" class="xhr-tabbar-arrow xhr-tabbar-arrow--right" id="tab-scroll-right" onclick="xhrScrollTabs(1)">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════════
                     TAB 1: Overview
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-overview" class="xhr-emp-form-panel xhr-emp-form-panel--active">

                    <!-- Top row: Personal Information (left) + User Access (right) -->
                    <div class="xhr-overview-cols">

                        <!-- LEFT: Personal Information -->
                        <div class="xhr-form-section" style="margin-bottom:0;">
                            <div class="xhr-form-section__title">Personal Information</div>

                            <!-- Row 1: First Name + Middle Name -->
                            <div class="xhr-form-grid-2" style="margin-bottom:16px;">
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">First Name <span class="xhr-required">*</span></label>
                                    <input type="text" name="first_name" class="xhr-form-input" required
                                           placeholder="First name"
                                           value="<?php echo fv($emp,'first_name'); ?>"
                                           oninput="xhrUpdateName()">
                                </div>
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="xhr-form-input"
                                           placeholder="Middle name (optional)"
                                           value="<?php echo fv($emp,'middle_name'); ?>"
                                           oninput="xhrUpdateName()">
                                </div>
                            </div>

                            <!-- Row 2: Last Name (full) -->
                            <div class="xhr-form-grid-1" style="margin-bottom:16px;">
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Last Name <span class="xhr-required">*</span></label>
                                    <input type="text" name="last_name" class="xhr-form-input" required
                                           placeholder="Last name"
                                           value="<?php echo fv($emp,'last_name'); ?>"
                                           oninput="xhrUpdateName()">
                                </div>
                            </div>

                            <!-- Row 3: Gender + Date of Birth -->
                            <div class="xhr-form-grid-2" style="margin-bottom:0;">
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Gender</label>
                                    <select name="gender" class="xhr-form-select">
                                        <option value="">Select gender</option>
                                        <?php foreach (['Male','Female','Other','Prefer not to say'] as $g): ?>
                                        <option value="<?php echo $g; ?>"<?php echo fv_select($emp,'gender',$g); ?>><?php echo $g; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="xhr-form-input"
                                           value="<?php echo fv($emp,'dob'); ?>">
                                </div>
                            </div>
                        </div><!-- /LEFT -->

                        <!-- RIGHT: User Access -->
                        <div class="xhr-form-section" style="margin-bottom:0;">
                            <div class="xhr-form-section__title">User Access</div>

                            <div class="xhr-form-field" style="margin-bottom:14px;">
                                <label class="xhr-form-label">Login Email</label>
                                <input type="email" name="company_email" class="xhr-form-input"
                                       placeholder="e.g. name@company.com"
                                       value="<?php echo fv($emp,'company_email'); ?>">
                            </div>

                            <!-- Portal Access — styled toggle row -->
                            <label class="xhr-portal-toggle-row" style="margin-bottom:10px;">
                                <div class="xhr-portal-toggle-row__left">
                                    <span class="material-symbols-outlined">key</span>
                                    Portal Access
                                </div>
                                <label class="xhr-toggle-wrap" style="margin-bottom:0;">
                                    <input type="checkbox" name="portal_access" value="1"
                                           <?php echo ($is_edit && !empty($emp->portal_access)) ? 'checked' : ''; ?>>
                                    <span class="xhr-toggle-track"></span>
                                </label>
                            </label>

                            <!-- Self-Service Access -->
                            <label class="xhr-portal-toggle-row" style="margin-bottom:14px;">
                                <div class="xhr-portal-toggle-row__left">
                                    <span class="material-symbols-outlined">self_improvement</span>
                                    Self Service Access
                                </div>
                                <label class="xhr-toggle-wrap" style="margin-bottom:0;">
                                    <input type="checkbox" name="self_service_access" value="1"
                                           <?php echo ($is_edit && !empty($emp->self_service_access)) ? 'checked' : ''; ?>>
                                    <span class="xhr-toggle-track"></span>
                                </label>
                            </label>

                            <div class="xhr-form-field" style="margin-bottom:14px;">
                                <label class="xhr-form-label">Joining Date <span class="xhr-required">*</span></label>
                                <input type="date" name="date_of_joining" class="xhr-form-input"
                                       value="<?php echo fv($emp,'date_of_joining'); ?>">
                            </div>

                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Status</label>
                                <select name="status" class="xhr-form-select">
                                    <?php foreach (['Active','Inactive','On Leave','Terminated','Resigned','Retired'] as $s): ?>
                                    <option value="<?php echo $s; ?>"<?php echo fv_select($emp,'status',$s) ?: ($s==='Active'?' selected':''); ?>><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div><!-- /RIGHT -->

                    </div><!-- /.xhr-overview-cols -->

                    <!-- Company Details (full width) -->
                    <div class="xhr-form-section xhr-overview-full" style="margin-top:28px;">
                        <div class="xhr-form-section__title">Company Details</div>
                        <div class="xhr-form-grid-3">

                            <!-- Company — anchor for cascade; data-payco auto-fills payroll company -->
                            <div class="xhr-form-field">
                                <label class="xhr-form-label xhr-form-label--req">Company</label>
                                <select name="company_id" id="emp-company-id" class="xhr-form-select">
                                    <option value="">— Select Company —</option>
                                    <?php foreach ($companies as $c): ?>
                                    <option value="<?php echo $c->id; ?>"
                                            data-payco="<?php echo (int)($c->payroll_company_id ?? 0); ?>"
                                            <?php echo fv_select($emp,'company_id',$c->id); ?>>
                                        <?php echo htmlspecialchars($c->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Branch — filtered by company -->
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Branch</label>
                                <select name="branch_id" id="emp-branch-id" class="xhr-form-select">
                                    <option value="">— Select Branch —</option>
                                    <?php foreach ($branches as $b): ?>
                                    <option value="<?php echo $b->id; ?>"
                                            data-company="<?php echo $b->company_id; ?>"
                                            <?php echo fv_select($emp,'branch_id',$b->id); ?>>
                                        <?php echo htmlspecialchars($b->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Department — filtered by company -->
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Department</label>
                                <select name="department_id" id="emp-dept-id" class="xhr-form-select">
                                    <option value="">— Select Department —</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d->id; ?>"
                                            data-company="<?php echo $d->company_id; ?>"
                                            <?php echo fv_select($emp,'department_id',$d->id); ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Designation</label>
                                <select name="designation_id" class="xhr-form-select">
                                    <option value="">Select Designation</option>
                                    <?php foreach ($designations as $des): ?>
                                    <option value="<?php echo $des->id; ?>"<?php echo fv_select($emp,'designation_id',$des->id); ?>><?php echo htmlspecialchars($des->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Grade</label>
                                <select name="grade_id" class="xhr-form-select">
                                    <option value="">Select Grade</option>
                                    <?php foreach ($grades as $g): ?>
                                    <option value="<?php echo $g->id; ?>"<?php echo fv_select($emp,'grade_id',$g->id); ?>><?php echo htmlspecialchars($g->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Employment Type</label>
                                <select name="employment_type" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['Full-Time','Part-Time','Contract','Intern','Casual','Consultant'] as $et): ?>
                                    <option value="<?php echo $et; ?>"<?php echo fv_select($emp,'employment_type',$et); ?>><?php echo $et; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Reports To</label>
                                <select name="reports_to" class="xhr-form-select">
                                    <option value="">Select Manager</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <?php if ($is_edit && $s->id == ($emp->id ?? 0)) continue; ?>
                                    <option value="<?php echo $s->id; ?>"<?php echo fv_select($emp,'reports_to',$s->id); ?>>
                                        <?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>

                </div><!-- /#efp-overview -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 2: Joining
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-joining" class="xhr-emp-form-panel">
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Joining Details</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Joining Date</label>
                                <input type="date" id="joining-date-mirror" class="xhr-form-input" readonly
                                       value="<?php echo fv($emp,'date_of_joining'); ?>"
                                       style="background:#f9fafb; color:#6b7280; cursor:default;">
                                <p style="font-size:11px; color:#9ca3af; margin:2px 0 0;">Set in Overview → User Access</p>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Offer Date</label>
                                <input type="date" name="offer_date" class="xhr-form-input"
                                       value="<?php echo fv($emp,'offer_date'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Confirmation Date</label>
                                <input type="date" name="confirmation_date" class="xhr-form-input"
                                       value="<?php echo fv($emp,'confirmation_date'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Notice Days</label>
                                <input type="number" name="notice_days" class="xhr-form-input" min="0"
                                       value="<?php echo fv($emp,'notice_days','30'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Probation Start</label>
                                <input type="date" name="probation_start" class="xhr-form-input"
                                       value="<?php echo fv($emp,'probation_start'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Probation End</label>
                                <input type="date" name="probation_end" class="xhr-form-input"
                                       value="<?php echo fv($emp,'probation_end'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Retirement Date</label>
                                <input type="date" name="retirement_date" class="xhr-form-input"
                                       value="<?php echo fv($emp,'retirement_date'); ?>">
                            </div>
                        </div>
                    </div>
                </div><!-- /#efp-joining -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 3: Address & Contacts
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-contacts" class="xhr-emp-form-panel">

                    <!-- Contact Information -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Contact Information</div>
                        <div class="xhr-form-grid-2">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Mobile</label>
                                <input type="tel" name="mobile" class="xhr-form-input"
                                       value="<?php echo fv($emp,'mobile'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Personal Email</label>
                                <input type="email" name="personal_email" class="xhr-form-input"
                                       value="<?php echo fv($emp,'personal_email'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Company Email</label>
                                <input type="email" name="company_email" class="xhr-form-input"
                                       value="<?php echo fv($emp,'company_email'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Preferred Email</label>
                                <select name="preferred_email" class="xhr-form-select">
                                    <option value="company"<?php echo fv_select($emp,'preferred_email','company') ?: ' selected'; ?>>Company Email</option>
                                    <option value="personal"<?php echo fv_select($emp,'preferred_email','personal'); ?>>Personal Email</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contacts -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Emergency Contacts</div>
                        <table class="xhr-repeat-table" id="emergency-table">
                            <thead>
                                <tr>
                                    <th>Contact Name</th>
                                    <th>Relationship</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="emergency-body">
                                <tr>
                                    <td><input type="text" name="emergency_name[]" placeholder="Full Name"></td>
                                    <td><input type="text" name="emergency_relationship[]" placeholder="e.g. Spouse"></td>
                                    <td><input type="tel" name="emergency_phone[]" placeholder="+254 7xx"></td>
                                    <td><input type="email" name="emergency_email[]" placeholder="Email"></td>
                                    <td><button type="button" class="xhr-del-row-btn" onclick="xhrDelRow(this)"><span class="material-symbols-outlined" style="font-size:16px;">delete</span></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="xhr-add-row-btn" onclick="xhrAddEmergencyRow()">
                            <span class="material-symbols-outlined" style="font-size:14px;">add</span> Add Contact
                        </button>
                    </div>

                    <!-- Addresses -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Addresses</div>
                        <?php foreach (['permanent' => 'Permanent Address', 'postal' => 'Postal Address', 'emergency' => 'Emergency Address', 'work' => 'Work Address'] as $akey => $alabel): ?>
                        <div class="xhr-address-block">
                            <div class="xhr-address-block__label"><?php echo $alabel; ?></div>
                            <div class="xhr-form-grid-3">
                                <div class="xhr-form-field xhr-form-field--full">
                                    <label class="xhr-form-label">Street / Address Line 1</label>
                                    <input type="text" name="addr_<?php echo $akey; ?>_line1" class="xhr-form-input">
                                </div>
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">City</label>
                                    <input type="text" name="addr_<?php echo $akey; ?>_city" class="xhr-form-input">
                                </div>
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Country</label>
                                    <input type="text" name="addr_<?php echo $akey; ?>_country" class="xhr-form-input" value="Kenya">
                                </div>
                                <div class="xhr-form-field">
                                    <label class="xhr-form-label">Postal Code</label>
                                    <input type="text" name="addr_<?php echo $akey; ?>_postal" class="xhr-form-input">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div><!-- /#efp-contacts -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 4: Attendance & Leaves
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-attendance" class="xhr-emp-form-panel">

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Attendance Device</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Attendance Device ID</label>
                                <input type="text" name="attendance_device_id" class="xhr-form-input"
                                       value="<?php echo fv($emp,'attendance_device_id'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">RFID Number</label>
                                <input type="text" name="rfid_number" class="xhr-form-input"
                                       value="<?php echo fv($emp,'rfid_number'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Biometric ID</label>
                                <input type="text" name="biometric_id" class="xhr-form-input"
                                       value="<?php echo fv($emp,'biometric_id'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Default Shift</label>
                                <select name="default_shift" class="xhr-form-select">
                                    <option value="">Select Shift</option>
                                    <?php foreach ($shifts as $sh): ?>
                                    <option value="<?php echo $sh->id; ?>"<?php echo fv_select($emp,'default_shift',$sh->id); ?>>
                                        <?php echo htmlspecialchars($sh->name . ' (' . $sh->start_time . ' – ' . $sh->end_time . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <?php if (empty($shifts)): ?>
                                    <option value="" disabled style="color:#9ca3af;">No shifts configured — add via Shift &amp; Attendance</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Approvers</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Leave Approver</label>
                                <select name="leave_approver" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <option value="<?php echo $s->id; ?>"<?php echo fv_select($emp,'leave_approver',$s->id); ?>>
                                        <?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Expense Approver</label>
                                <select name="expense_approver" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <option value="<?php echo $s->id; ?>"<?php echo fv_select($emp,'expense_approver',$s->id); ?>>
                                        <?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Shift Approver</label>
                                <select name="shift_approver" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <option value="<?php echo $s->id; ?>"<?php echo fv_select($emp,'shift_approver',$s->id); ?>>
                                        <?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Policy Assignment -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Leave Policy</div>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#166534;">
                            <span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;margin-right:5px;">lightbulb</span>
                            Assigning a Leave Policy automatically creates all leave type allocations for this employee. Existing allocations for the same year are not overwritten.
                        </div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field" style="grid-column:span 2;">
                                <label class="xhr-form-label">Apply Leave Policy</label>
                                <select name="leave_policy_id" id="leave-policy-sel" class="xhr-form-select" onchange="showEmpPolicyPreview(this.value)">
                                    <option value="">— None (skip) —</option>
                                    <?php foreach ($leave_policies as $lp): ?>
                                    <option value="<?php echo $lp->id; ?>"
                                        data-lines="<?php echo htmlspecialchars(json_encode(array_map(function($l){ return ['name'=>$l->leave_type_name??'','color'=>$l->leave_type_color??'#6b7280','days'=>(float)($l->annual_days??0)]; }, $lp->lines??[]))); ?>"
                                        <?php echo ($current_leave_policy_id == $lp->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lp->name); ?>
                                        (<?php echo count($lp->lines ?? []); ?> leave type<?php echo count($lp->lines ?? []) != 1 ? 's' : ''; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">For Year</label>
                                <select name="leave_policy_year" class="xhr-form-select">
                                    <?php for ($y = date('Y') + 1; $y >= date('Y') - 1; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <!-- Preview of what will be allocated -->
                        <div id="emp-policy-preview" style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:9px;padding:10px 14px;margin-top:8px;">
                            <div style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Will allocate:</div>
                            <div id="emp-policy-lines" style="display:flex;flex-wrap:wrap;gap:7px;"></div>
                        </div>
                    </div>

                </div><!-- /#efp-attendance -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 5: Salary
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-salary" class="xhr-emp-form-panel">

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Salary Information</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Salary Currency</label>
                                <select name="salary_currency" class="xhr-form-select">
                                    <?php foreach (['KES','UGX','TZS','RWF','ETB','USD','GBP','EUR','ZAR'] as $cur): ?>
                                    <option value="<?php echo $cur; ?>"<?php echo fv_select($emp,'salary_currency',$cur) ?: ($cur==='KES'?' selected':''); ?>><?php echo $cur; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Salary Mode</label>
                                <select name="salary_mode" class="xhr-form-select">
                                    <?php foreach (['Bank Transfer','Cash','Cheque','Mobile Money'] as $sm): ?>
                                    <option value="<?php echo $sm; ?>"<?php echo fv_select($emp,'salary_mode',$sm) ?: ($sm==='Bank Transfer'?' selected':''); ?>><?php echo $sm; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Payroll Cost Center
                                    <?php if (empty($analytic_accounts)): ?>
                                    <span style="font-size:10px; font-weight:400; color:#9ca3af;">(set up via Xetuu Books → Config → Analytic Accounts)</span>
                                    <?php endif; ?>
                                </label>
                                <?php if (!empty($analytic_accounts)): ?>
                                <select name="payroll_cost_center" class="xhr-form-select">
                                    <option value="">— Select Cost Center —</option>
                                    <?php
                                    $cur_plan = null;
                                    foreach ($analytic_accounts as $aa):
                                        if ($aa->plan_name !== $cur_plan):
                                            if ($cur_plan !== null) echo '</optgroup>';
                                            echo '<optgroup label="' . htmlspecialchars($aa->plan_name ?? 'General') . '">';
                                            $cur_plan = $aa->plan_name;
                                        endif;
                                    ?>
                                    <option value="<?php echo htmlspecialchars($aa->code); ?>"
                                        <?php echo (fv($emp,'payroll_cost_center') === $aa->code) ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($aa->code . ' — ' . $aa->name); ?>
                                    </option>
                                    <?php endforeach; if ($cur_plan !== null) echo '</optgroup>'; ?>
                                </select>
                                <?php else: ?>
                                <input type="text" name="payroll_cost_center" class="xhr-form-input"
                                       value="<?php echo fv($emp,'payroll_cost_center'); ?>"
                                       placeholder="e.g. Sales, Operations">
                                <?php endif; ?>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Employee Advance Account
                                    <?php if (empty($advance_accounts)): ?>
                                    <span style="font-size:10px; font-weight:400; color:#9ca3af;">(set up via Xetuu Books → Chart of Accounts)</span>
                                    <?php endif; ?>
                                </label>
                                <?php if (!empty($advance_accounts)): ?>
                                <select name="advance_account" class="xhr-form-select">
                                    <option value="">— Select Account —</option>
                                    <?php foreach ($advance_accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>"
                                        <?php echo fv_select($emp,'advance_account',$acc->id); ?>>
                                        <?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                <input type="text" name="advance_account" class="xhr-form-input"
                                       value="<?php echo fv($emp,'advance_account'); ?>"
                                       placeholder="e.g. 11400 — Employee Advances">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Bank Details</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Bank Branch</label>
                                <input type="text" name="bank_branch" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Account Number</label>
                                <input type="text" name="account_number" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Account Name</label>
                                <input type="text" name="account_name" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">SWIFT Code</label>
                                <input type="text" name="swift_code" class="xhr-form-input">
                            </div>
                        </div>
                    </div>

                </div><!-- /#efp-salary -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 6: Contract
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-contract" class="xhr-emp-form-panel">

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Contract Information</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Contract Number</label>
                                <input type="text" name="contract_number" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Contract Type</label>
                                <select name="contract_type" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['Permanent','Fixed-Term','Casual','Internship','Consultancy'] as $ct): ?>
                                    <option value="<?php echo $ct; ?>"><?php echo $ct; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Working Schedule</label>
                                <input type="text" name="working_schedule" class="xhr-form-input" placeholder="e.g. Mon–Fri 8am–5pm">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Wage Type</label>
                                <select name="wage_type" class="xhr-form-select">
                                    <option value="Monthly">Monthly</option>
                                    <option value="Daily">Daily</option>
                                    <option value="Hourly">Hourly</option>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Monthly Salary
                                    <span style="font-size:10px; font-weight:400; color:#9ca3af;">— total gross package/month</span>
                                </label>
                                <input type="number" name="monthly_salary" id="monthly-salary-input" class="xhr-form-input" step="0.01" min="0"
                                       value="<?php echo isset($active_contract) ? ($active_contract->monthly_salary ?? '') : ''; ?>"
                                       oninput="xhrCalcAnnualCost()">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Annual Cost
                                    <span style="font-size:10px; font-weight:400; color:#9ca3af;">— auto-calculated (×12)</span>
                                </label>
                                <input type="number" name="annual_cost" id="annual-cost-input" class="xhr-form-input" step="0.01" min="0"
                                       value="<?php echo isset($active_contract) ? ($active_contract->annual_cost ?? '') : ''; ?>"
                                       style="background:#f9fafb;" readonly>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Contract Start</label>
                                <input type="date" name="contract_start" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Contract End</label>
                                <input type="date" name="contract_end" class="xhr-form-input">
                            </div>
                        </div>
                    </div>

                    <!-- ─── Payroll Integration ──────────────────────────── -->
                    <div class="xhr-form-section" style="border-top:2px solid #16a34a; padding-top:18px; margin-top:4px;">
                        <div class="xhr-form-section__title" style="color:#16a34a; display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-money" style="font-size:14px;"></i> Payroll Integration
                            <span style="font-size:11px; font-weight:400; color:#6b7280; text-transform:none;">Uses the salary &amp; company above — creates an active payroll contract on save</span>
                        </div>
                        <?php
                        // Build payroll company map for JS: hr_company_id → payroll_company_name
                        $payco_names = [];
                        foreach ($payroll_companies ?? [] as $pco) { $payco_names[$pco->id] = $pco->name; }
                        ?>
                        <!-- Hidden: auto-filled by JS when company changes -->
                        <input type="hidden" name="payroll_company_id" id="payroll-company-id-hidden"
                               value="<?php echo isset($active_contract) ? (int)$active_contract->company_id : ''; ?>">
                        <div style="margin-bottom:12px; font-size:12px; color:#374151; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:8px 12px; display:flex; align-items:center; gap:8px;">
                            <span class="material-symbols-outlined" style="font-size:15px; color:#16a34a;">info</span>
                            <span>Payroll Company: <strong id="payco-display-name"><?php
                                $pcid = isset($active_contract) ? (int)$active_contract->company_id : 0;
                                echo $pcid && isset($payco_names[$pcid]) ? htmlspecialchars($payco_names[$pcid]) : 'Auto-detected from Company above';
                            ?></strong></span>
                        </div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Salary Structure</label>
                                <select name="payroll_salary_structure_id" class="xhr-form-select">
                                    <option value="">— Select Structure —</option>
                                    <?php if (!empty($salary_structures)): foreach ($salary_structures as $ss): ?>
                                    <option value="<?php echo $ss->id; ?>"
                                        <?php echo isset($active_contract) && $active_contract->salary_structure_id == $ss->id ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ss->name); ?>
                                    </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Working Days/Week</label>
                                <input type="number" name="payroll_working_days" class="xhr-form-input" min="1" max="7"
                                       value="<?php echo isset($active_contract) ? $active_contract->working_days : 5; ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Payment Method</label>
                                <select name="payroll_payment_method" class="xhr-form-select">
                                    <?php foreach (['bank'=>'Bank Transfer','mobile_money'=>'Mobile Money','cash'=>'Cash'] as $pv => $pl): ?>
                                    <option value="<?php echo $pv; ?>"<?php echo isset($active_contract) && $active_contract->payment_method === $pv ? ' selected' : ''; ?>><?php echo $pl; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Bank / Account No.</label>
                                <input type="text" name="payroll_bank_account" class="xhr-form-input"
                                       value="<?php echo htmlspecialchars(isset($active_contract) ? $active_contract->bank_account : ''); ?>"
                                       placeholder="e.g. Equity – 1234567890">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Employee Tax ID <span style="font-weight:400;">(KRA PIN / TIN / SSN…)</span></label>
                                <input type="text" name="payroll_tax_id" class="xhr-form-input"
                                       value="<?php echo htmlspecialchars(isset($active_contract) ? ($active_contract->tax_id ?? '') : ''); ?>"
                                       placeholder="Country-specific tax ID">
                            </div>
                        </div>
                    </div>

                    <!-- ─── Payroll Benefits (dynamic rows) ──────────────── -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Benefits / Allowances <span style="font-size:11px; font-weight:400; color:#9ca3af;">— used as <code>benefit['CODE']</code> in payroll formulas</span></span>
                            <button type="button" onclick="payAddBenefit()" class="btn btn-xs btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:4px;">+ Add</button>
                        </div>
                        <div id="pay-benefits-wrap" style="margin-top:8px;">
                        <?php if (!empty($contract_lines)): foreach ($contract_lines as $cl): if ($cl->line_type !== 'benefit') continue; ?>
                        <div class="pay-contract-row" style="display:flex; gap:8px; margin-bottom:6px; align-items:center;">
                            <input type="text" name="payroll_benefit_code[]" class="xhr-form-input" placeholder="CODE" style="width:100px; flex-shrink:0;" value="<?php echo htmlspecialchars($cl->code); ?>">
                            <input type="text" name="payroll_benefit_name[]" class="xhr-form-input" placeholder="Name" style="flex:1;" value="<?php echo htmlspecialchars($cl->name); ?>">
                            <input type="number" name="payroll_benefit_amount[]" class="xhr-form-input" placeholder="Amount" style="width:120px; flex-shrink:0;" step="0.01" value="<?php echo $cl->amount; ?>">
                            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#dc2626; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
                        </div>
                        <?php endforeach; endif; ?>
                        </div>
                        <div id="pay-benefits-empty" style="font-size:12px; color:#9ca3af; <?php echo !empty($contract_lines) ? 'display:none' : ''; ?>">No benefits — click + Add</div>
                    </div>

                    <!-- ─── Payroll Deductions (dynamic rows) ────────────── -->
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Deductions <span style="font-size:11px; font-weight:400; color:#9ca3af;">— used as <code>deduction['CODE']</code> in payroll formulas</span></span>
                            <button type="button" onclick="payAddDeduction()" class="btn btn-xs btn-danger" style="border-radius:4px;">+ Add</button>
                        </div>
                        <div id="pay-deductions-wrap" style="margin-top:8px;">
                        <?php if (!empty($contract_lines)): foreach ($contract_lines as $cl): if ($cl->line_type !== 'deduction') continue; ?>
                        <div class="pay-contract-row" style="display:flex; gap:8px; margin-bottom:6px; align-items:center;">
                            <input type="text" name="payroll_deduction_code[]" class="xhr-form-input" placeholder="CODE" style="width:100px; flex-shrink:0;" value="<?php echo htmlspecialchars($cl->code); ?>">
                            <input type="text" name="payroll_deduction_name[]" class="xhr-form-input" placeholder="Name" style="flex:1;" value="<?php echo htmlspecialchars($cl->name); ?>">
                            <input type="number" name="payroll_deduction_amount[]" class="xhr-form-input" placeholder="Amount" style="width:120px; flex-shrink:0;" step="0.01" value="<?php echo $cl->amount; ?>">
                            <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#dc2626; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
                        </div>
                        <?php endforeach; endif; ?>
                        </div>
                        <div id="pay-deductions-empty" style="font-size:12px; color:#9ca3af; <?php echo !empty($contract_lines) ? 'display:none' : ''; ?>">No deductions — click + Add</div>
                    </div>

                </div><!-- /#efp-contract -->
<script>
function payAddBenefit() {
    document.getElementById('pay-benefits-empty').style.display = 'none';
    const row = document.createElement('div');
    row.className = 'pay-contract-row';
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center;';
    row.innerHTML = '<input type="text" name="payroll_benefit_code[]" class="xhr-form-input" placeholder="CODE" style="width:100px;flex-shrink:0;">' +
        '<input type="text" name="payroll_benefit_name[]" class="xhr-form-input" placeholder="Name" style="flex:1;">' +
        '<input type="number" name="payroll_benefit_amount[]" class="xhr-form-input" placeholder="Amount" style="width:120px;flex-shrink:0;" step="0.01">' +
        '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;font-size:20px;cursor:pointer;line-height:1;">&times;</button>';
    document.getElementById('pay-benefits-wrap').appendChild(row);
}
function payAddDeduction() {
    document.getElementById('pay-deductions-empty').style.display = 'none';
    const row = document.createElement('div');
    row.className = 'pay-contract-row';
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center;';
    row.innerHTML = '<input type="text" name="payroll_deduction_code[]" class="xhr-form-input" placeholder="CODE" style="width:100px;flex-shrink:0;">' +
        '<input type="text" name="payroll_deduction_name[]" class="xhr-form-input" placeholder="Name" style="flex:1;">' +
        '<input type="number" name="payroll_deduction_amount[]" class="xhr-form-input" placeholder="Amount" style="width:120px;flex-shrink:0;" step="0.01">' +
        '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;font-size:20px;cursor:pointer;line-height:1;">&times;</button>';
    document.getElementById('pay-deductions-wrap').appendChild(row);
}
</script>

                <!-- ══════════════════════════════════════════════════════
                     TAB 7: Personal Details
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-personal" class="xhr-emp-form-panel">

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Personal Information</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Marital Status</label>
                                <select name="marital_status" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['Single','Married','Divorced','Widowed','Other'] as $ms): ?>
                                    <option value="<?php echo $ms; ?>"<?php echo fv_select($emp,'marital_status',$ms); ?>><?php echo $ms; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Blood Group</label>
                                <select name="blood_group" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                                    <option value="<?php echo $bg; ?>"<?php echo fv_select($emp,'blood_group',$bg); ?>><?php echo $bg; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Nationality</label>
                                <input type="text" name="nationality" class="xhr-form-input"
                                       value="<?php echo fv($emp,'nationality'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Religion <span style="font-weight:400;opacity:.6;">(optional)</span></label>
                                <input type="text" name="religion" class="xhr-form-input"
                                       value="<?php echo fv($emp,'religion'); ?>">
                            </div>
                            <div class="xhr-form-field" style="align-self:end;">
                                <label class="xhr-form-label">&nbsp;</label>
                                <div class="xhr-toggle-row" style="border:none;padding:0;">
                                    <span class="xhr-toggle-label">Person with Disability</span>
                                    <label class="xhr-toggle-wrap">
                                        <input type="checkbox" name="disability_status" value="1"
                                               <?php echo ($is_edit && !empty($emp->disability_status)) ? 'checked' : ''; ?>>
                                        <span class="xhr-toggle-track"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Health Information</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Height (cm)</label>
                                <input type="number" name="height" class="xhr-form-input" min="0">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Weight (kg)</label>
                                <input type="number" name="weight" class="xhr-form-input" min="0">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Insurance Provider</label>
                                <input type="text" name="insurance_provider" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field xhr-form-field--full">
                                <label class="xhr-form-label">Known Allergies</label>
                                <textarea name="allergies" class="xhr-form-textarea" rows="2"></textarea>
                            </div>
                            <div class="xhr-form-field xhr-form-field--full">
                                <label class="xhr-form-label">Medical Conditions</label>
                                <textarea name="medical_conditions" class="xhr-form-textarea" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Statutory / Compliance Details</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Social Security No. <span style="font-weight:400;">(NSSF / SSN…)</span></label>
                                <input type="text" name="social_sec_number" class="xhr-form-input"
                                       value="<?php echo fv($emp,'social_sec_number'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Health Fund No. <span style="font-weight:400;">(SHA / NHIF…)</span></label>
                                <input type="text" name="health_fund_number" class="xhr-form-input"
                                       value="<?php echo fv($emp,'health_fund_number'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Employee Tax ID <span style="font-weight:400;">(KRA PIN / TIN / SSN…)</span></label>
                                <input type="text" name="tax_id" class="xhr-form-input"
                                       value="<?php echo fv($emp,'tax_id'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Passport Number</label>
                                <input type="text" name="passport_number" class="xhr-form-input"
                                       value="<?php echo fv($emp,'passport_number'); ?>">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Passport Expiry</label>
                                <input type="date" name="passport_expiry" class="xhr-form-input"
                                       value="<?php echo fv($emp,'passport_expiry'); ?>">
                            </div>
                        </div>
                    </div>

                </div><!-- /#efp-personal -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 8: Profile
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-profile" class="xhr-emp-form-panel">

                    <!-- Employee Photo Upload -->
                    <div class="xhr-profile-photo-section">
                        <div class="xhr-profile-photo-box" onclick="document.getElementById('xhr-profile-photo-file').click()">
                            <?php if ($is_edit && !empty($emp->photo)): ?>
                                <img id="xhr-profile-img" src="<?php echo base_url($emp->photo); ?>" alt="">
                            <?php else: ?>
                                <div class="xhr-profile-photo-initials" id="xhr-profile-initials">
                                    <?php echo $is_edit ? strtoupper(substr($emp->first_name??'E',0,1).substr($emp->last_name??'M',0,1)) : 'EM'; ?>
                                </div>
                                <img id="xhr-profile-img" src="" alt="" style="display:none;position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:12px;">
                            <?php endif; ?>
                        </div>
                        <div class="xhr-profile-photo-info">
                            <div class="xhr-profile-photo-name"><?php echo $is_edit ? htmlspecialchars($emp->first_name.' '.$emp->last_name) : 'New Employee'; ?></div>
                            <div class="xhr-profile-photo-empno"><?php echo $is_edit ? fv($emp,'employee_number') : htmlspecialchars($next_number); ?></div>
                            <button type="button" class="xhr-profile-photo-btn" onclick="document.getElementById('xhr-profile-photo-file').click()">
                                <span class="material-symbols-outlined">upload</span> Upload Photo
                            </button>
                            <p class="xhr-profile-photo-hint">JPG, PNG or GIF &middot; Max 2 MB &middot; 400&times;400 px recommended</p>
                        </div>
                        <input type="file" id="xhr-profile-photo-file" name="profile_photo" accept="image/*" style="display:none;" onchange="xhrProfilePhotoPreview(this)">
                    </div>

                    <!-- Bio / Cover Letter -->
                    <div class="xhr-profile-section">
                        <div class="xhr-profile-section__label">Bio / Cover Letter</div>
                        <div id="xhr-quill-toolbar" class="xhr-quill-toolbar">
                            <span class="ql-formats">
                                <select class="ql-header">
                                    <option selected>Normal</option>
                                    <option value="1">H1</option><option value="2">H2</option><option value="3">H3</option>
                                </select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-bold" title="Bold"></button>
                                <button class="ql-italic" title="Italic"></button>
                                <button class="ql-underline" title="Underline"></button>
                                <button class="ql-strike" title="Strike"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-blockquote" title="Quote"></button>
                                <button class="ql-code-block" title="Code"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link" title="Link"></button>
                                <button class="ql-image" title="Image"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered" title="Numbered list"></button>
                                <button class="ql-list" value="bullet" title="Bullet list"></button>
                                <button class="ql-indent" value="-1" title="Decrease indent"></button>
                                <button class="ql-indent" value="+1" title="Increase indent"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-align" value="" title="Left"></button>
                                <button class="ql-align" value="center" title="Center"></button>
                                <button class="ql-align" value="right" title="Right"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean" title="Clear formatting"></button>
                            </span>
                        </div>
                        <div id="xhr-quill-editor" class="xhr-quill-editor"><?php if ($is_edit && !empty($emp->biography)) echo htmlspecialchars_decode(htmlspecialchars($emp->biography ?? '')); ?></div>
                        <input type="hidden" name="biography" id="xhr-biography-input">
                        <p class="xhr-profile-section__hint">Short biography for website and other publications.</p>
                    </div>

                    <!-- Educational Qualification -->
                    <div class="xhr-collapsible xhr-collapsible--open" id="coll-education">
                        <button type="button" class="xhr-collapsible__header" onclick="xhrToggleCollapsible('coll-education')">
                            <span class="xhr-collapsible__title">Educational Qualification</span>
                            <span class="material-symbols-outlined xhr-collapsible__icon">expand_less</span>
                        </button>
                        <div class="xhr-collapsible__body">
                            <div class="xhr-collapsible__sublabel">Education</div>
                            <table class="xhr-erp-table" id="education-table">
                                <thead><tr>
                                    <th class="xhr-erp-table__check"><input type="checkbox" onchange="xhrCheckAll(this,'edu-row-check')"></th>
                                    <th class="xhr-erp-table__num">No.</th>
                                    <th>School / University</th>
                                    <th>Qualification</th>
                                    <th>Level</th>
                                    <th>Year of Passing</th>
                                    <th>Class / Percentage</th>
                                    <th>Major / Subject</th>
                                    <th class="xhr-erp-table__act"><span class="material-symbols-outlined" style="font-size:16px;color:#9ca3af;">settings</span></th>
                                </tr></thead>
                                <tbody id="education-body">
                                    <tr>
                                        <td class="xhr-erp-table__check"><input type="checkbox" class="edu-row-check"></td>
                                        <td class="xhr-erp-table__num">1</td>
                                        <td><input type="text" name="edu_institution[]" placeholder="e.g. Kenyatta University"></td>
                                        <td><input type="text" name="edu_qualification[]" placeholder="e.g. Degree"></td>
                                        <td><input type="text" name="edu_level[]" placeholder="e.g. Graduate"></td>
                                        <td><input type="number" name="edu_year[]" placeholder="2020" min="1950" max="2099"></td>
                                        <td><input type="text" name="edu_class[]" placeholder="e.g. Second Class"></td>
                                        <td><input type="text" name="edu_major[]" placeholder="e.g. Supply Chain"></td>
                                        <td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn"><span class="material-symbols-outlined">edit</span></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="xhr-addrow-btn" onclick="xhrAddEduRow()">Add row</button>
                        </div>
                    </div>

                    <!-- Previous Work Experience -->
                    <div class="xhr-collapsible" id="coll-work">
                        <button type="button" class="xhr-collapsible__header" onclick="xhrToggleCollapsible('coll-work')">
                            <span class="xhr-collapsible__title">Previous Work Experience</span>
                            <span class="material-symbols-outlined xhr-collapsible__icon">chevron_right</span>
                        </button>
                        <div class="xhr-collapsible__body" style="display:none;">
                            <table class="xhr-erp-table" id="work-table">
                                <thead><tr>
                                    <th class="xhr-erp-table__check"><input type="checkbox" onchange="xhrCheckAll(this,'work-row-check')"></th>
                                    <th class="xhr-erp-table__num">No.</th>
                                    <th>Company Name</th>
                                    <th>Designation / Title</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Reason for Leaving</th>
                                    <th class="xhr-erp-table__act"></th>
                                </tr></thead>
                                <tbody id="work-body">
                                    <tr>
                                        <td class="xhr-erp-table__check"><input type="checkbox" class="work-row-check"></td>
                                        <td class="xhr-erp-table__num">1</td>
                                        <td><input type="text" name="work_company[]"></td>
                                        <td><input type="text" name="work_position[]"></td>
                                        <td><input type="date" name="work_from[]"></td>
                                        <td><input type="date" name="work_to[]"></td>
                                        <td><input type="text" name="work_reason[]"></td>
                                        <td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn"><span class="material-symbols-outlined">edit</span></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="xhr-addrow-btn" onclick="xhrAddWorkRow()">Add row</button>
                        </div>
                    </div>

                    <!-- History In Company -->
                    <div class="xhr-collapsible" id="coll-history">
                        <button type="button" class="xhr-collapsible__header" onclick="xhrToggleCollapsible('coll-history')">
                            <span class="xhr-collapsible__title">History In Company</span>
                            <span class="material-symbols-outlined xhr-collapsible__icon">chevron_right</span>
                        </button>
                        <div class="xhr-collapsible__body" style="display:none;">
                            <div class="xhr-erp-table-wrap">
                                <table class="xhr-erp-table" id="history-table">
                                    <thead>
                                        <tr>
                                            <th class="xhr-erp-table__check"><input type="checkbox" onchange="xhrCheckAll(this,'hist-row-check')"></th>
                                            <th class="xhr-erp-table__num">No.</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th class="xhr-erp-table__act"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="history-body">
                                        <!-- rows added by JS -->
                                    </tbody>
                                </table>
                                <button type="button" class="xhr-addrow-btn" onclick="xhrAddHistoryRow()">
                                    <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">add</span> Add row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="xhr-profile-section" style="margin-top:8px;">
                        <div class="xhr-profile-section__label">Comments</div>
                        <div class="xhr-comment-input-row">
                            <div class="xhr-comment-avatar">A</div>
                            <input type="text" class="xhr-comment-input" placeholder="Type a reply / comment">
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="xhr-profile-activity">
                        <div class="xhr-profile-activity__header">
                            <span class="xhr-profile-activity__title">Activity</span>
                            <div style="display:flex;gap:8px;">
                                <button type="button" class="xhr-activity-btn"><span class="material-symbols-outlined">add</span> New Email</button>
                                <button type="button" class="xhr-activity-btn"><span class="material-symbols-outlined">event</span> New Event</button>
                            </div>
                        </div>
                        <div class="xhr-activity-empty">
                            <span class="material-symbols-outlined" style="font-size:28px;color:#d1d5db;display:block;margin-bottom:6px;">history</span>
                            <span>No activity yet.</span>
                        </div>
                    </div>

                </div><!-- /#efp-profile -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 10: Connections
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-connections" class="xhr-emp-form-panel">
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Connections</div>
                        <p style="color:#9ca3af;font-size:14px;">Link this employee to related records, projects, or contacts.</p>
                    </div>
                </div><!-- /#efp-connections -->

                <!-- ══════════════════════════════════════════════════════
                     TAB 9: Employee Exit
                ═══════════════════════════════════════════════════════ -->
                <div id="efp-exit" class="xhr-emp-form-panel">
                    <div class="xhr-form-section">
                        <div class="xhr-form-section__title">Employee Exit</div>
                        <div class="xhr-form-grid-3">
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Exit Date</label>
                                <input type="date" name="exit_date" class="xhr-form-input">
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Exit Reason</label>
                                <select name="exit_reason" class="xhr-form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['Resignation','Termination','Retirement','Contract End','Redundancy','Death','Other'] as $er): ?>
                                    <option value="<?php echo $er; ?>"><?php echo $er; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="xhr-form-field">
                                <label class="xhr-form-label">Final Settlement (<?php echo fv($emp,'salary_currency','KES'); ?>)</label>
                                <input type="number" name="final_settlement" class="xhr-form-input" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="xhr-form-grid-2" style="margin-top:16px;">
                            <div class="xhr-toggle-row" style="border:none;padding:8px 0;">
                                <span class="xhr-toggle-label">Clearance Form Completed</span>
                                <label class="xhr-toggle-wrap">
                                    <input type="checkbox" name="clearance_done" value="1">
                                    <span class="xhr-toggle-track"></span>
                                </label>
                            </div>
                            <div class="xhr-toggle-row" style="border:none;padding:8px 0;">
                                <span class="xhr-toggle-label">Assets Returned</span>
                                <label class="xhr-toggle-wrap">
                                    <input type="checkbox" name="assets_returned" value="1">
                                    <span class="xhr-toggle-track"></span>
                                </label>
                            </div>
                        </div>
                        <div class="xhr-form-field xhr-form-field--full" style="margin-top:16px;">
                            <label class="xhr-form-label">Exit Interview Notes</label>
                            <textarea name="exit_interview" class="xhr-form-textarea" rows="4"></textarea>
                        </div>
                    </div>
                </div><!-- /#efp-exit -->

            </div><!-- /.xhr-emp-form-main -->

            <!-- ── Right Sidebar ──────────────────────────────────────── -->
            <div class="xhr-emp-form-sidebar">

                <!-- ── HR Consultancy Mode card ──────────────────────── -->
                <div class="xhr-cmode-card">
                    <div class="xhr-cmode-card__header">
                        <span class="xhr-cmode-card__title">HR Consultancy Mode</span>
                        <span class="material-symbols-outlined" style="color:rgba(255,255,255,.6);font-size:16px;">settings</span>
                    </div>
                    <div class="xhr-cmode-card__body">

                        <!-- Company is set in Overview tab — shown here as read-only context -->
                        <div class="xhr-cmode-row">
                            <div class="xhr-cmode-row__label">Managing Company</div>
                            <div class="xhr-cmode-company-display" id="sidebar-company-display">
                                <span class="material-symbols-outlined" style="font-size:14px;color:#9ca3af;">business</span>
                                <span id="sidebar-company-name" style="font-size:12px;color:#6b7280;">
                                    <?php
                                    if ($is_edit && $emp->company_id) {
                                        foreach ($companies as $c) {
                                            if ($c->id == $emp->company_id) { echo htmlspecialchars($c->name); break; }
                                        }
                                    } else { echo 'Set in Overview tab'; }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="xhr-cmode-row">
                            <div class="xhr-cmode-row__label">Client / EOR Company</div>
                            <select name="client_id" class="xhr-form-select">
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $cl): ?>
                                <option value="<?php echo $cl->id; ?>"<?php echo fv_select($emp,'client_id',$cl->id); ?>>
                                    <?php echo htmlspecialchars($cl->name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="xhr-cmode-row">
                            <div class="xhr-cmode-row__label">Assigned Client</div>
                            <input type="text" name="assigned_client" class="xhr-form-input"
                                   placeholder="e.g. Global Enterprise Solutions"
                                   value="<?php echo fv($emp,'assigned_client'); ?>">
                        </div>

                        <div class="xhr-cmode-row">
                            <div class="xhr-cmode-row__label">HR Manager</div>
                            <select name="hr_manager" class="xhr-form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($staff_list as $s): ?>
                                <option value="<?php echo $s->id; ?>">
                                    <?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="xhr-cmode-row">
                            <div class="xhr-cmode-row__label">Work Location</div>
                            <div class="xhr-cmode-row__val" style="margin-bottom:6px;">
                                <span class="material-symbols-outlined" style="font-size:15px;">location_on</span>
                            </div>
                            <input type="text" name="work_location" class="xhr-form-input"
                                   placeholder="e.g. Westlands, Nairobi"
                                   value="<?php echo fv($emp,'work_location'); ?>">
                        </div>

                    </div><!-- /.xhr-cmode-card__body -->
                </div>

                <!-- ── Employee Summary card ─────────────────────────── -->
                <div class="xhr-summary-card">
                    <div class="xhr-summary-card__header">Employee Summary</div>
                    <div class="xhr-summary-row">
                        <span class="xhr-summary-row__label">Attendance (MTD)</span>
                        <span class="xhr-summary-row__val xhr-summary-row__val--green">
                            <?php echo $is_edit ? '—' : '—'; ?>
                        </span>
                    </div>
                    <div class="xhr-summary-row">
                        <span class="xhr-summary-row__label">Leave Balance</span>
                        <span class="xhr-summary-row__val xhr-summary-row__val--blue">—</span>
                    </div>
                    <div class="xhr-summary-row">
                        <span class="xhr-summary-row__label">Next Review</span>
                        <span class="xhr-summary-row__val xhr-summary-row__val--warn">—</span>
                    </div>
                </div>

                <!-- ── Pending Tasks card ────────────────────────────── -->
                <div class="xhr-tasks-card">
                    <div class="xhr-tasks-card__header">Pending Tasks</div>
                    <?php if ($is_edit): ?>
                    <div class="xhr-task-item">
                        <input type="checkbox">
                        <div>
                            <div class="xhr-task-item__title">Verify NHIF Card</div>
                            <div class="xhr-task-item__sub">Due by tomorrow</div>
                        </div>
                    </div>
                    <div class="xhr-task-item">
                        <input type="checkbox">
                        <div>
                            <div class="xhr-task-item__title">Sign Contract Addendum</div>
                            <div class="xhr-task-item__sub">HR Consultancy Mode update</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="xhr-task-item">
                        <input type="checkbox" disabled>
                        <div>
                            <div class="xhr-task-item__title">Complete profile setup</div>
                            <div class="xhr-task-item__sub">Save first to unlock tasks</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.xhr-emp-form-sidebar -->

        </div><!-- /.xhr-emp-form-body -->

    </form>


</div><!-- /.xhr-emp-form-page -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<!-- Quill.js CDN -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
/* ── Quill snow theme overrides ──────────────────────────────────────────── */
#xhr-quill-toolbar.ql-toolbar.ql-snow {
    border: 1.5px solid #d1d5db;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    background: #f9fafb;
    font-family: 'Inter', sans-serif;
    padding: 8px 10px;
}
#xhr-quill-editor.ql-container.ql-snow {
    border: 1.5px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 8px 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
}
#xhr-quill-editor .ql-editor {
    min-height: 200px;
    padding: 14px 16px;
    font-size: 14px;
    line-height: 1.7;
    color: #111827;
}
#xhr-quill-editor .ql-editor.ql-blank::before {
    color: #9ca3af;
    font-style: normal;
}
.ql-toolbar.ql-snow .ql-formats { margin-right: 12px; }
</style>

<script>
// ── Tab switching ──────────────────────────────────────────────────────────
document.querySelectorAll('#emp-form-tabs .xhr-emp-form-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#emp-form-tabs .xhr-emp-form-tab').forEach(function(b) {
            b.classList.remove('xhr-emp-form-tab--active');
        });
        document.querySelectorAll('.xhr-emp-form-panel').forEach(function(p) {
            p.classList.remove('xhr-emp-form-panel--active');
        });
        this.classList.add('xhr-emp-form-tab--active');
        var panel = document.getElementById(this.getAttribute('data-panel'));
        if (panel) panel.classList.add('xhr-emp-form-panel--active');
        xhrUpdateTabArrows();
    });
});

// ── Company → Branch / Department cascade ─────────────────────────────────
(function() {
    var compSel  = document.getElementById('emp-company-id');
    var branchSel = document.getElementById('emp-branch-id');
    var deptSel   = document.getElementById('emp-dept-id');
    if (!compSel || !branchSel || !deptSel) return;

    // Cache all original options at load time
    var allBranchOpts = Array.from(branchSel.querySelectorAll('option[data-company]'));
    var allDeptOpts   = Array.from(deptSel.querySelectorAll('option[data-company]'));

    function filterSelect(sel, allOpts, companyId) {
        var current = sel.value;
        allOpts.forEach(function(o) { if (o.parentNode) o.parentNode.removeChild(o); });
        allOpts.forEach(function(o) {
            var dc = o.getAttribute('data-company');
            // Show if: no company selected, OR option has no company (global), OR company matches
            if (!companyId || !dc || dc === '0' || dc === '' || dc == companyId) {
                sel.appendChild(o);
            }
        });
        if (sel.querySelector('option[value="' + current + '"]')) {
            sel.value = current;
        } else {
            sel.value = '';
        }
    }

    function updateSidebarCompanyName() {
        var nameEl = document.getElementById('sidebar-company-name');
        if (!nameEl) return;
        var opt = compSel.options[compSel.selectedIndex];
        nameEl.textContent = (opt && opt.value) ? opt.text : 'Set in Overview tab';
    }

    function syncPayrollCompany() {
        var opt = compSel.options[compSel.selectedIndex];
        var paycoId   = (opt && opt.value) ? (opt.getAttribute('data-payco') || '') : '';
        var hiddenEl  = document.getElementById('payroll-company-id-hidden');
        var displayEl = document.getElementById('payco-display-name');
        if (hiddenEl)  hiddenEl.value = paycoId;
        if (displayEl) {
            if (paycoId && paycoId !== '0') {
                var PAYCO_MAP = <?php
                    $tmp = [];
                    foreach ($payroll_companies ?? [] as $pco) { $tmp[(int)$pco->id] = $pco->name; }
                    echo json_encode($tmp);
                ?>;
                displayEl.textContent = PAYCO_MAP[parseInt(paycoId)] || ('Payroll Company #' + paycoId);
            } else {
                displayEl.textContent = 'Not linked — map this company at Setup → Company';
            }
        }
    }

    compSel.addEventListener('change', function() {
        var cid = this.value;
        filterSelect(branchSel, allBranchOpts, cid);
        filterSelect(deptSel,   allDeptOpts,   cid);
        updateSidebarCompanyName();
        syncPayrollCompany();
    });

    if (compSel.value) {
        filterSelect(branchSel, allBranchOpts, compSel.value);
        filterSelect(deptSel,   allDeptOpts,   compSel.value);
        updateSidebarCompanyName();
        syncPayrollCompany();
    }
})();

// ── Joining date mirror (Overview → Joining tab) ───────────────────────────
(function() {
    var src    = document.querySelector('[name=date_of_joining]');
    var mirror = document.getElementById('joining-date-mirror');
    if (!src || !mirror) return;
    src.addEventListener('change', function() { mirror.value = this.value; });
})();

// ── Annual Cost auto-calculate (Monthly × 12) ─────────────────────────────
function xhrCalcAnnualCost() {
    var monthly = parseFloat(document.getElementById('monthly-salary-input').value) || 0;
    var annualEl = document.getElementById('annual-cost-input');
    if (annualEl) annualEl.value = (monthly * 12).toFixed(2);
}
// Allow manual override — remove readonly on click
(function() {
    var el = document.getElementById('annual-cost-input');
    if (el) el.addEventListener('dblclick', function() {
        this.removeAttribute('readonly');
        this.style.background = '';
    });
})();

// ── Tab scroll arrows ──────────────────────────────────────────────────────
function xhrScrollTabs(dir) {
    var el = document.getElementById('emp-form-tabs');
    el.scrollBy({ left: dir * 180, behavior: 'smooth' });
    setTimeout(xhrUpdateTabArrows, 300);
}
function xhrUpdateTabArrows() {
    var el = document.getElementById('emp-form-tabs');
    var btnL = document.getElementById('tab-scroll-left');
    var btnR = document.getElementById('tab-scroll-right');
    if (!el || !btnL || !btnR) return;
    btnL.style.opacity = el.scrollLeft > 4 ? '1' : '0.3';
    btnL.style.pointerEvents = el.scrollLeft > 4 ? '' : 'none';
    btnR.style.opacity = (el.scrollLeft + el.clientWidth < el.scrollWidth - 4) ? '1' : '0.3';
    btnR.style.pointerEvents = (el.scrollLeft + el.clientWidth < el.scrollWidth - 4) ? '' : 'none';
}
document.getElementById('emp-form-tabs').addEventListener('scroll', xhrUpdateTabArrows);
window.addEventListener('resize', xhrUpdateTabArrows);
xhrUpdateTabArrows();

// ── Live name update ───────────────────────────────────────────────────────
function xhrUpdateName() {
    var f = (document.querySelector('[name=first_name]') || {}).value || '';
    var m = (document.querySelector('[name=middle_name]') || {}).value || '';
    var l = (document.querySelector('[name=last_name]') || {}).value || '';
    var full = [f, m, l].filter(Boolean).join(' ');
    var el = document.getElementById('xhr-emp-display-name');
    if (el) {
        el.textContent = full || 'New Employee';
        el.classList.toggle('xhr-emp-form-name--placeholder', !full);
    }
    // Also update the profile tab name + initials
    var pname = document.querySelector('.xhr-profile-photo-name');
    if (pname) pname.textContent = full || 'New Employee';
    var init = document.getElementById('xhr-profile-initials');
    if (init) init.textContent = [(f||'E')[0], (l||'M')[0]].join('').toUpperCase();
}

// ── Header photo preview ───────────────────────────────────────────────────
var photoInput = document.getElementById('xhr-photo-input');
if (photoInput) photoInput.addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var prev = document.getElementById('xhr-photo-preview');
        prev.src = e.target.result;
        prev.style.display = '';
        var ph = prev.previousElementSibling;
        if (ph && ph.classList.contains('material-symbols-outlined')) ph.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

// ── Profile photo preview ──────────────────────────────────────────────────
function xhrProfilePhotoPreview(input) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = document.getElementById('xhr-profile-img');
        var init = document.getElementById('xhr-profile-initials');
        img.src = e.target.result;
        img.style.display = '';
        if (init) init.style.display = 'none';
        // also update header photo
        var hp = document.getElementById('xhr-photo-preview');
        if (hp) { hp.src = e.target.result; hp.style.display = ''; }
    };
    reader.readAsDataURL(file);
}

// ── Quill rich-text editor ─────────────────────────────────────────────────
var quill = null;
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('xhr-quill-editor')) return;
    quill = new Quill('#xhr-quill-editor', {
        modules: { toolbar: '#xhr-quill-toolbar' },
        theme: 'snow',
        placeholder: 'Write a short bio or cover letter…'
    });
    // pre-populate on edit
    var bioInput = document.getElementById('xhr-biography-input');
    if (bioInput && bioInput.value) {
        quill.clipboard.dangerouslyPasteHTML(bioInput.value);
    }
    // sync to hidden input on form submit
    var form = document.getElementById('xhr-emp-form');
    if (form) form.addEventListener('submit', function() {
        if (bioInput && quill) bioInput.value = quill.root.innerHTML;
    });
});

// ── Collapsible sections ───────────────────────────────────────────────────
function xhrToggleCollapsible(id) {
    var wrap = document.getElementById(id);
    if (!wrap) return;
    var body  = wrap.querySelector('.xhr-collapsible__body');
    var icon  = wrap.querySelector('.xhr-collapsible__icon');
    var isOpen = wrap.classList.contains('xhr-collapsible--open');
    if (isOpen) {
        body.style.display = 'none';
        wrap.classList.remove('xhr-collapsible--open');
        if (icon) icon.textContent = 'chevron_right';
    } else {
        body.style.display = '';
        wrap.classList.add('xhr-collapsible--open');
        if (icon) icon.textContent = 'expand_less';
    }
}

// ── ERPNext-style table helpers ────────────────────────────────────────────
function xhrCheckAll(master, cls) {
    document.querySelectorAll('.' + cls).forEach(function(cb) { cb.checked = master.checked; });
}
function xhrAddEduRow() {
    var tbody = document.getElementById('education-body');
    var n = tbody.rows.length + 1;
    var t = `<tr>
        <td class="xhr-erp-table__check"><input type="checkbox" class="edu-row-check"></td>
        <td class="xhr-erp-table__num">${n}</td>
        <td><input type="text" name="edu_institution[]" placeholder="School / University"></td>
        <td><input type="text" name="edu_qualification[]" placeholder="Qualification"></td>
        <td><input type="text" name="edu_level[]" placeholder="Level"></td>
        <td><input type="number" name="edu_year[]" placeholder="Year" min="1950" max="2099"></td>
        <td><input type="text" name="edu_class[]" placeholder="Class %"></td>
        <td><input type="text" name="edu_major[]" placeholder="Major"></td>
        <td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn" onclick="this.closest('tr').remove();xhrRenumberRows('education-body')"><span class="material-symbols-outlined">delete</span></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', t);
}
function xhrAddWorkRow() {
    var tbody = document.getElementById('work-body');
    var n = tbody.rows.length + 1;
    var t = `<tr>
        <td class="xhr-erp-table__check"><input type="checkbox" class="work-row-check"></td>
        <td class="xhr-erp-table__num">${n}</td>
        <td><input type="text" name="work_company[]"></td>
        <td><input type="text" name="work_position[]"></td>
        <td><input type="date" name="work_from[]"></td>
        <td><input type="date" name="work_to[]"></td>
        <td><input type="text" name="work_reason[]"></td>
        <td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn" onclick="this.closest('tr').remove();xhrRenumberRows('work-body')"><span class="material-symbols-outlined">delete</span></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', t);
}
function xhrAddHistoryRow() {
    var tbody = document.getElementById('history-body');
    var n = tbody.rows.length + 1;
    var t = `<tr>
        <td class="xhr-erp-table__check"><input type="checkbox" class="hist-row-check"></td>
        <td class="xhr-erp-table__num">${n}</td>
        <td><input type="text" name="hist_branch[]"></td>
        <td><input type="text" name="hist_department[]"></td>
        <td><input type="text" name="hist_designation[]"></td>
        <td><input type="date" name="hist_from[]"></td>
        <td><input type="date" name="hist_to[]"></td>
        <td class="xhr-erp-table__act"><button type="button" class="xhr-erp-edit-btn" onclick="this.closest('tr').remove();xhrRenumberRows('history-body')"><span class="material-symbols-outlined">delete</span></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', t);
}
function xhrAddEmergencyRow() {
    var tbody = document.getElementById('emergency-body');
    var t = `<tr>
        <td><input type="text" name="emergency_name[]" placeholder="Full Name"></td>
        <td><input type="text" name="emergency_relationship[]" placeholder="e.g. Parent"></td>
        <td><input type="tel" name="emergency_phone[]" placeholder="+254 7xx"></td>
        <td><input type="email" name="emergency_email[]" placeholder="Email"></td>
        <td><button type="button" class="xhr-del-row-btn" onclick="xhrDelRow(this)"><span class="material-symbols-outlined" style="font-size:16px;">delete</span></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', t);
}
function xhrDelRow(btn) {
    var tr = btn.closest('tr');
    var tbody = tr.parentNode;
    if (tbody.rows.length > 1) tr.remove();
}
function xhrRenumberRows(tbodyId) {
    var rows = document.getElementById(tbodyId).rows;
    for (var i = 0; i < rows.length; i++) {
        var numCell = rows[i].querySelector('.xhr-erp-table__num');
        if (numCell) numCell.textContent = i + 1;
    }
}

function showEmpPolicyPreview(policyId) {
    var preview  = document.getElementById('emp-policy-preview');
    var linesDiv = document.getElementById('emp-policy-lines');
    if (!policyId) { preview.style.display = 'none'; return; }
    var sel = document.getElementById('leave-policy-sel');
    var opt = sel.options[sel.selectedIndex];
    var lines = [];
    try { lines = JSON.parse(opt.getAttribute('data-lines') || '[]'); } catch(e) {}
    if (!lines.length) { preview.style.display = 'none'; return; }
    linesDiv.innerHTML = lines.map(function(l) {
        return '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:7px;'
            + 'background:#f3f4f6;border:1px solid #e5e7eb;font-size:12px;font-weight:600;color:#374151;">'
            + '<span style="width:8px;height:8px;border-radius:50%;background:'+l.color+';display:inline-block;"></span>'
            + l.name + ' <span style="color:#16a34a;font-weight:700;">' + l.days.toFixed(1) + 'd</span>'
            + '</span>';
    }).join('');
    preview.style.display = 'block';
}
// Show on page load if pre-selected (edit mode)
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('leave-policy-sel');
    if (sel && sel.value) showEmpPolicyPreview(sel.value);
});
</script>

<?php init_tail(); ?>
