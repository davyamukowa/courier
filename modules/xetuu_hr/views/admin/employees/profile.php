<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Helpers
$full_name  = htmlspecialchars($employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name);
$short_name = htmlspecialchars(substr($employee->first_name,0,1) . '. ' . $employee->last_name);
$dept       = htmlspecialchars($employee->department_name ?: '—');
$desig      = htmlspecialchars($employee->designation_name ?: 'Staff');
$company    = htmlspecialchars($employee->company_name ?: '—');
$client     = htmlspecialchars($employee->client_name ?: '');
$reports_to = htmlspecialchars($employee->reports_to_name ?: '');
$contract   = $employee->active_contract;
$initials_rt= $reports_to ? strtoupper(substr($employee->reports_to_name,0,1)) : '';
$status     = $employee->status ?: 'Active';
$status_cls = ($status === 'Active') ? 'xhr-badge--active' : (in_array($status, ['Terminated','Resigned']) ? 'xhr-badge--error' : 'xhr-badge--inactive');
?>
<?php init_head(); ?>
<?php $xhr_active = 'employees'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header">
    <div class="xhr-breadcrumb">
        <a href="<?php echo admin_url('xetuu_hr/employees'); ?>" style="color:var(--xhr-secondary);text-decoration:none;"><?php echo _l('xetuu_hr_employees'); ?></a>
        <span class="material-symbols-outlined xhr-breadcrumb__sep">chevron_right</span>
        <span class="xhr-breadcrumb__current"><?php echo htmlspecialchars($employee->employee_number); ?></span>
    </div>
    <div class="xhr-action-buttons">
        <a href="<?php echo admin_url('xetuu_hr/employees'); ?>" class="xhr-btn xhr-btn--primary">
            <span class="material-symbols-outlined">person_add</span>
            <?php echo _l('xetuu_hr_add_employee'); ?>
        </a>
        <a href="<?php echo admin_url('xetuu_hr/employees/edit/' . $employee->id); ?>" class="xhr-btn xhr-btn--outline">
            <span class="material-symbols-outlined">edit</span>
            <?php echo _l('xetuu_hr_edit_profile'); ?>
        </a>
        <button class="xhr-btn xhr-btn--tertiary" onclick="window.print()">
            <span class="material-symbols-outlined">print</span>
            <?php echo _l('xetuu_hr_print_letter'); ?>
        </button>
        <button class="xhr-btn xhr-btn--outline xhr-btn--icon">
            <span class="material-symbols-outlined">more_vert</span>
        </button>
    </div>
</div>

<div class="xhr-content">

    <!-- Profile Header -->
    <div class="xhr-profile-header" style="margin-bottom:2rem;">
        <?php if (!empty($employee->photo)) : ?>
            <img class="xhr-profile-avatar" src="<?php echo base_url($employee->photo); ?>" alt="<?php echo $full_name; ?>">
        <?php else : ?>
            <div class="xhr-profile-avatar" style="display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:var(--xhr-primary);background:rgba(0,107,44,0.08);">
                <?php echo strtoupper(substr($employee->first_name,0,1) . substr($employee->last_name,0,1)); ?>
            </div>
        <?php endif; ?>
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
                <h2 class="xhr-profile-name"><?php echo $full_name; ?></h2>
                <span class="xhr-badge <?php echo $status_cls; ?>"><?php echo htmlspecialchars($status); ?></span>
            </div>
            <p class="xhr-profile-title"><?php echo $desig; ?></p>
            <div class="xhr-profile-meta">
                <?php if ($company !== '—') : ?>
                <span class="material-symbols-outlined" style="font-size:14px;">corporate_fare</span>
                <?php echo _l('xetuu_hr_employer'); ?>: <a href="#"><?php echo $company; ?></a>
                <?php endif; ?>
                <?php if ($client) : ?>
                <div class="xhr-profile-meta__sep"></div>
                <span class="material-symbols-outlined" style="font-size:14px;">handshake</span>
                <?php echo _l('xetuu_hr_client'); ?>: <a href="#"><?php echo $client; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bento Grid -->
    <div class="xhr-profile-grid">

        <!-- ── Sidebar ──────────────────────────────────────────────────── -->
        <div class="xhr-sidebar-col">

            <!-- Employment Details Card -->
            <div class="xhr-card xhr-emp-details">
                <p class="xhr-emp-details__heading"><?php echo _l('xetuu_hr_employment_details'); ?></p>
                <ul class="xhr-field-list">
                    <li class="xhr-field-group">
                        <span class="xhr-field-label"><?php echo _l('xetuu_hr_employee_id'); ?></span>
                        <span class="xhr-field-value"><?php echo htmlspecialchars($employee->employee_number); ?></span>
                    </li>
                    <li class="xhr-field-group">
                        <span class="xhr-field-label"><?php echo _l('xetuu_hr_date_of_joining'); ?></span>
                        <span class="xhr-field-value">
                            <?php echo $employee->date_of_joining ? date('d M Y', strtotime($employee->date_of_joining)) : '—'; ?>
                        </span>
                    </li>
                    <li class="xhr-field-group">
                        <span class="xhr-field-label"><?php echo _l('xetuu_hr_department'); ?></span>
                        <span class="xhr-field-value"><?php echo $dept; ?></span>
                    </li>
                    <li class="xhr-field-group">
                        <span class="xhr-field-label"><?php echo _l('xetuu_hr_employment_type'); ?></span>
                        <span class="xhr-field-value"><?php echo htmlspecialchars($employee->employment_type ?: '—'); ?></span>
                    </li>
                    <?php if ($reports_to) : ?>
                    <li class="xhr-field-group">
                        <span class="xhr-field-label"><?php echo _l('xetuu_hr_reports_to'); ?></span>
                        <div class="xhr-reports-to">
                            <div class="xhr-avatar-initials"><?php echo $initials_rt; ?></div>
                            <span class="xhr-field-value" style="font-weight:500;">
                                <a href="#"><?php echo $reports_to; ?></a>
                            </span>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Consultancy Mode Widget (only shown when client is set) -->
            <?php if ($client && $contract) : ?>
            <div class="xhr-consultancy-widget">
                <div class="xhr-consultancy-widget__header">
                    <span class="xhr-consultancy-widget__label"><?php echo _l('xetuu_hr_consultancy_mode'); ?></span>
                    <span class="material-symbols-outlined" style="color:var(--xhr-primary);font-size:18px;">verified_user</span>
                </div>
                <div class="xhr-consultancy-sub">
                    <p><?php echo _l('xetuu_hr_assigned_project'); ?></p>
                    <p><?php echo $client . ' — ' . ($contract->contract_type ?: 'Contract'); ?></p>
                </div>
                <div class="xhr-consultancy-sub-grid">
                    <div class="xhr-consultancy-sub">
                        <p><?php echo _l('xetuu_hr_start_date'); ?></p>
                        <p><?php echo $contract->start_date ? date('M Y', strtotime($contract->start_date)) : '—'; ?></p>
                    </div>
                    <div class="xhr-consultancy-sub">
                        <p><?php echo _l('xetuu_hr_end_date'); ?></p>
                        <p><?php echo ($contract->end_date ? date('M Y', strtotime($contract->end_date)) : _l('xetuu_hr_open')); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.xhr-sidebar-col -->

        <!-- ── Main Content ─────────────────────────────────────────────── -->
        <div class="xhr-main-col">

            <!-- Tab Bar -->
            <div class="xhr-tab-bar" data-tabs="emp-profile-tabs" style="overflow-x:auto; white-space:nowrap; display:flex; gap:0;">
                <button class="xhr-tab xhr-tab--active"     data-panel="tab-dashboard"     onclick="xhrTab(this,'emp-profile-tabs')"><?php echo _l('xetuu_hr_tab_dashboard'); ?></button>
                <button class="xhr-tab"                     data-panel="tab-payslips"      onclick="xhrTab(this,'emp-profile-tabs')">Payslips</button>
                <button class="xhr-tab"                     data-panel="tab-personal"      onclick="xhrTab(this,'emp-profile-tabs')"><?php echo _l('xetuu_hr_tab_personal'); ?></button>
                <button class="xhr-tab"                     data-panel="tab-salary"        onclick="xhrTab(this,'emp-profile-tabs')"><?php echo _l('xetuu_hr_tab_salary'); ?></button>
                <button class="xhr-tab"                     data-panel="tab-leaves"        onclick="xhrTab(this,'emp-profile-tabs')">Leaves</button>
                <button class="xhr-tab"                     data-panel="tab-loans"         onclick="xhrTab(this,'emp-profile-tabs')">Loans &amp; Advances</button>
                <button class="xhr-tab"                     data-panel="tab-disciplinary"  onclick="xhrTab(this,'emp-profile-tabs')">Disciplinary</button>
                <button class="xhr-tab"                     data-panel="tab-shifts"        onclick="xhrTab(this,'emp-profile-tabs')">Shifts</button>
                <button class="xhr-tab"                     data-panel="tab-assets"        onclick="xhrTab(this,'emp-profile-tabs')"><?php echo _l('xetuu_hr_tab_assets'); ?></button>
                <button class="xhr-tab"                     data-panel="tab-documents"     onclick="xhrTab(this,'emp-profile-tabs')"><?php echo _l('xetuu_hr_tab_documents'); ?></button>
            </div>

            <!-- ═══ TAB: Dashboard ═════════════════════════════════════════ -->
            <div id="tab-dashboard" data-tab-group="emp-profile-tabs">

                <!-- Stat Cards -->
                <?php
                $ytd_net = array_sum(array_map(
                    fn($p) => (float)$p->net_wage,
                    array_filter($emp_payslips ?? [], fn($p) =>
                        in_array($p->state, ['paid','done','confirmed']) &&
                        date('Y', strtotime($p->date_from)) == date('Y')
                    )
                ));
                ?>
                <div class="xhr-stat-row" style="grid-template-columns:repeat(4,1fr);">
                    <div class="xhr-stat-card">
                        <div class="xhr-stat-card__icon xhr-stat-card__icon--primary">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <div>
                            <div class="xhr-stat-card__label">Basic Salary</div>
                            <div class="xhr-stat-card__value">
                                <?php if ($active_contract && $active_contract->basic_salary > 0):
                                    echo htmlspecialchars($active_contract->currency ?? 'KES') . ' ' . number_format($active_contract->basic_salary ?? 0);
                                else: ?>—<?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="xhr-stat-card">
                        <div class="xhr-stat-card__icon xhr-stat-card__icon--tertiary">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <div>
                            <div class="xhr-stat-card__label">Payslips (Total)</div>
                            <div class="xhr-stat-card__value" style="cursor:pointer; text-decoration:underline dotted;" onclick="document.querySelector('[data-panel=tab-payslips]').click()"><?php echo count($emp_payslips ?? []); ?></div>
                        </div>
                    </div>
                    <div class="xhr-stat-card">
                        <div class="xhr-stat-card__icon xhr-stat-card__icon--secondary">
                            <span class="material-symbols-outlined">trending_up</span>
                        </div>
                        <div>
                            <div class="xhr-stat-card__label">YTD Net Pay (<?php echo date('Y'); ?>)</div>
                            <div class="xhr-stat-card__value" style="font-size:14px;">
                                <?php echo $ytd_net > 0 ? number_format($ytd_net, 2) : '—'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="xhr-stat-card">
                        <div class="xhr-stat-card__icon" style="background:#fef3c7;">
                            <span class="material-symbols-outlined" style="color:#d97706;">event_available</span>
                        </div>
                        <div>
                            <div class="xhr-stat-card__label">Leave Balance</div>
                            <div class="xhr-stat-card__value">—</div>
                        </div>
                    </div>
                </div><!-- /.xhr-stat-row -->

                <!-- Personal Information Section -->
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title"><?php echo _l('xetuu_hr_personal_information'); ?></h4>
                        <button class="xhr-section-card__edit">
                            <span class="material-symbols-outlined">edit_square</span>
                        </button>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-field-grid-3">
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_full_name'); ?></span>
                                <span class="xhr-field-value"><?php echo $full_name; ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_gender'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->gender ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_date_of_birth'); ?></span>
                                <span class="xhr-field-value">
                                    <?php echo $employee->dob ? date('d M Y', strtotime($employee->dob)) : '—'; ?>
                                </span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_email'); ?></span>
                                <span class="xhr-field-value">
                                    <?php if (!empty($employee->company_email)) : ?>
                                        <a href="mailto:<?php echo htmlspecialchars($employee->company_email); ?>"><?php echo htmlspecialchars($employee->company_email); ?></a>
                                    <?php elseif (!empty($employee->personal_email)) : ?>
                                        <a href="mailto:<?php echo htmlspecialchars($employee->personal_email); ?>"><?php echo htmlspecialchars($employee->personal_email); ?></a>
                                    <?php else : ?>—<?php endif; ?>
                                </span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_phone'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->mobile ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_nationality'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->nationality ?: '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div><!-- /.xhr-section-card -->

                <!-- Line Management Section -->
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title"><?php echo _l('xetuu_hr_line_management'); ?></h4>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-org-tree">

                            <!-- Manager node -->
                            <?php if ($reports_to) : ?>
                            <div class="xhr-org-node">
                                <?php if (!empty($employee->reports_to_photo)) : ?>
                                    <img class="xhr-org-node__avatar"
                                         src="<?php echo base_url($employee->reports_to_photo); ?>"
                                         style="width:64px;height:64px;" alt="">
                                <?php else : ?>
                                    <div class="xhr-org-node__avatar"
                                         style="width:64px;height:64px;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:var(--xhr-primary);background:rgba(0,107,44,0.08);">
                                        <?php echo strtoupper($initials_rt); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="xhr-org-node__name"><?php echo $reports_to; ?></span>
                                <span class="xhr-org-node__role"><?php echo _l('xetuu_hr_manager'); ?></span>
                            </div>
                            <div class="xhr-org-connector" style="left:calc(50% - 100px);"></div>
                            <?php endif; ?>

                            <!-- Current employee (YOU) -->
                            <div class="xhr-org-node">
                                <?php if (!empty($employee->photo)) : ?>
                                    <img class="xhr-org-node__avatar xhr-org-node__avatar--current"
                                         src="<?php echo base_url($employee->photo); ?>"
                                         style="width:80px;height:80px;" alt="">
                                <?php else : ?>
                                    <div class="xhr-org-node__avatar xhr-org-node__avatar--current"
                                         style="width:80px;height:80px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:var(--xhr-primary);background:rgba(0,107,44,0.08);">
                                        <?php echo strtoupper(substr($employee->first_name,0,1) . substr($employee->last_name,0,1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="xhr-org-node__name xhr-org-node__name--current"><?php echo $short_name; ?></span>
                                <span class="xhr-org-node__role" style="font-weight:700;"><?php echo _l('xetuu_hr_you'); ?></span>
                            </div>
                            <div class="xhr-org-connector" style="left:calc(50% + 20px);"></div>

                            <!-- Subordinate placeholder -->
                            <div class="xhr-org-node xhr-org-node--ghost">
                                <div class="xhr-org-node__avatar xhr-org-node__avatar--ghost"
                                     style="width:64px;height:64px;">
                                    <span class="material-symbols-outlined" style="font-size:28px;color:var(--xhr-secondary);">person_add</span>
                                </div>
                                <span class="xhr-org-node__name"><?php echo _l('xetuu_hr_no_reports'); ?></span>
                                <span class="xhr-org-node__role"><?php echo _l('xetuu_hr_hiring_active'); ?></span>
                            </div>

                        </div><!-- /.xhr-org-tree -->
                    </div>
                </div><!-- /.xhr-section-card -->

            </div><!-- /#tab-dashboard -->

            <!-- ═══ TAB: Personal Details ══════════════════════════════════ -->
            <div id="tab-personal" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title"><?php echo _l('xetuu_hr_personal_details'); ?></h4>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-field-grid-3">
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_marital_status'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->marital_status ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_blood_group'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->blood_group ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_nationality'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->nationality ?: '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statutory Details -->
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title"><?php echo _l('xetuu_hr_statutory_details'); ?></h4>
                        <button class="xhr-section-card__edit"><span class="material-symbols-outlined">edit_square</span></button>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-field-grid-3">
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">Social Security No.</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->social_sec_number ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">Health Fund No.</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->health_fund_number ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">Tax ID</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->tax_id ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_passport'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->passport_number ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_passport_expiry'); ?></span>
                                <span class="xhr-field-value">
                                    <?php echo $employee->passport_expiry ? date('d M Y', strtotime($employee->passport_expiry)) : '—'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /#tab-personal -->

            <!-- ═══ TAB: Salary & Payroll ══════════════════════════════════ -->
            <div id="tab-salary" data-tab-group="emp-profile-tabs" style="display:none;">
                <?php if ($contract) : ?>
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title"><?php echo _l('xetuu_hr_active_contract'); ?></h4>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-field-grid-3">
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_contract_type'); ?></span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($contract->contract_type ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_monthly_salary'); ?></span>
                                <span class="xhr-field-value">
                                    <?php echo htmlspecialchars($contract->currency ?: 'KES') . ' ' . number_format($contract->monthly_salary, 2); ?>
                                </span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_annual_cost'); ?></span>
                                <span class="xhr-field-value">
                                    <?php echo htmlspecialchars($contract->currency ?: 'KES') . ' ' . number_format($contract->annual_cost, 2); ?>
                                </span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_start_date'); ?></span>
                                <span class="xhr-field-value"><?php echo date('d M Y', strtotime($contract->start_date)); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label"><?php echo _l('xetuu_hr_end_date'); ?></span>
                                <span class="xhr-field-value"><?php echo $contract->end_date ? date('d M Y', strtotime($contract->end_date)) : _l('xetuu_hr_open'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else : ?>
                <div class="xhr-empty">
                    <span class="material-symbols-outlined">description</span>
                    <div class="xhr-empty__title"><?php echo _l('xetuu_hr_no_contract_yet'); ?></div>
                    <div class="xhr-empty__sub"><?php echo _l('xetuu_hr_add_contract'); ?></div>
                </div>
                <?php endif; ?>
            </div><!-- /#tab-salary -->

            <!-- ═══ TAB: Payslips ══════════════════════════════════════════ -->
            <div id="tab-payslips" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header" style="justify-content:space-between;">
                        <h4 class="xhr-section-card__title">Payslip History</h4>
                        <a href="<?php echo admin_url('xetuu_hr/payroll/payslips?employee_id='.$employee->id); ?>"
                           style="font-size:12px; color:#2563eb; text-decoration:none;">View All →</a>
                    </div>
                    <div class="xhr-section-card__body" style="padding:0;">
                        <?php if (empty($emp_payslips)): ?>
                        <div style="padding:32px; text-align:center; color:#9ca3af; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:32px; display:block; margin-bottom:8px; color:#d1d5db;">receipt_long</span>
                            No payslips yet for this employee.
                        </div>
                        <?php else: ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb;">Period</th>
                                    <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb;">Reference</th>
                                    <th style="text-align:right; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb;">Gross</th>
                                    <th style="text-align:right; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb;">Net Pay</th>
                                    <th style="text-align:center; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="padding:10px 16px; border-bottom:1px solid #e5e7eb;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $state_colors = [
                                    'draft'     => '#9ca3af',
                                    'computed'  => '#2563eb',
                                    'confirmed' => '#d97706',
                                    'done'      => '#16a34a',
                                    'paid'      => '#16a34a',
                                ];
                                foreach ($emp_payslips as $ps):
                                $sc = $state_colors[$ps->state] ?? '#6b7280';
                                ?>
                                <tr style="cursor:pointer; border-bottom:1px solid #f3f4f6;"
                                    onclick="location.href='<?php echo admin_url('xetuu_hr/payroll/payslips/'.$ps->id); ?>'"
                                    onmouseenter="this.style.background='#f9fafb'" onmouseleave="this.style.background=''">
                                    <td style="padding:12px 16px; font-size:13px; color:#374151;">
                                        <?php echo date('M Y', strtotime($ps->date_from)); ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151;">
                                        <code style="font-size:11px; background:#f3f4f6; padding:2px 6px; border-radius:3px;">
                                            <?php echo htmlspecialchars($ps->reference ?? 'PS-'.$ps->id); ?>
                                        </code>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; text-align:right; color:#374151;">
                                        <?php echo number_format($ps->gross_salary ?? 0, 2); ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; font-weight:600; text-align:right; color:#111827;">
                                        <?php echo number_format($ps->net_salary ?? 0, 2); ?>
                                    </td>
                                    <td style="padding:12px 16px; text-align:center;">
                                        <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:<?php echo $sc; ?>; background:<?php echo $sc; ?>18; padding:2px 8px; border-radius:4px;">
                                            <?php echo $ps->state; ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <?php if (in_array($ps->state, ['confirmed','done','paid'])): ?>
                                        <a href="<?php echo admin_url('xetuu_hr/payroll/payslips/'.$ps->id.'/pdf'); ?>"
                                           onclick="event.stopPropagation()" target="_blank"
                                           style="font-size:11px; color:#6b7280; text-decoration:none;">
                                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">picture_as_pdf</span> PDF
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!-- /#tab-payslips -->

            <!-- ═══ TAB: Leaves & Attendance ══════════════════════════════ -->
            <div id="tab-leaves" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title">Leave Summary</h4>
                    </div>
                    <div class="xhr-section-card__body">
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;">
                            <?php foreach ([['Annual Leave','event_available','#16a34a'],['Sick Leave','medical_services','#dc2626'],['Maternity/Paternity','child_care','#2563eb']] as [$lname,$licon,$lcol]): ?>
                            <div style="background:<?php echo $lcol; ?>10; border:1px solid <?php echo $lcol; ?>30; border-radius:8px; padding:16px; text-align:center;">
                                <span class="material-symbols-outlined" style="color:<?php echo $lcol; ?>; font-size:24px; display:block; margin-bottom:6px;"><?php echo $licon; ?></span>
                                <div style="font-size:11px; color:#6b7280; text-transform:uppercase; font-weight:600; margin-bottom:4px;"><?php echo $lname; ?></div>
                                <div style="font-size:20px; font-weight:700; color:#111827;">—</div>
                                <div style="font-size:11px; color:#9ca3af;">days remaining</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="color:#9ca3af; font-size:12px; text-align:center; padding:8px;">
                            Leave management module integration pending. Leave balances will appear here when the Leave submodule is activated.
                        </div>
                    </div>
                </div>
            </div><!-- /#tab-leaves -->

            <!-- ═══ TAB: Loans & Advances ══════════════════════════════════ -->
            <div id="tab-loans" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header" style="justify-content:space-between;">
                        <h4 class="xhr-section-card__title">Loans &amp; Salary Advances</h4>
                        <button class="btn btn-sm btn-success" style="border-radius:6px; font-size:12px;">
                            <span class="material-symbols-outlined" style="font-size:13px; vertical-align:middle;">add</span> New Loan
                        </button>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-empty" style="padding:32px 0;">
                            <span class="material-symbols-outlined">account_balance_wallet</span>
                            <div class="xhr-empty__title">No active loans or advances</div>
                            <div class="xhr-empty__sub">Loan management module integration pending.</div>
                        </div>
                    </div>
                </div>
            </div><!-- /#tab-loans -->

            <!-- ═══ TAB: Disciplinary ══════════════════════════════════════ -->
            <div id="tab-disciplinary" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header" style="justify-content:space-between;">
                        <h4 class="xhr-section-card__title">Disciplinary Records</h4>
                        <button class="btn btn-sm btn-danger" style="border-radius:6px; font-size:12px; background:#dc2626; border-color:#dc2626;">
                            <span class="material-symbols-outlined" style="font-size:13px; vertical-align:middle;">add</span> New Case
                        </button>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-empty" style="padding:32px 0;">
                            <span class="material-symbols-outlined">gavel</span>
                            <div class="xhr-empty__title">No disciplinary records</div>
                            <div class="xhr-empty__sub">Disciplinary cases for this employee will appear here.</div>
                        </div>
                    </div>
                </div>
            </div><!-- /#tab-disciplinary -->

            <!-- ═══ TAB: Shifts ════════════════════════════════════════════ -->
            <div id="tab-shifts" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-section-card">
                    <div class="xhr-section-card__header">
                        <h4 class="xhr-section-card__title">Shift &amp; Attendance</h4>
                    </div>
                    <div class="xhr-section-card__body">
                        <div class="xhr-field-grid-3" style="margin-bottom:16px;">
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">Default Shift</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->default_shift ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">Attendance Device ID</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->attendance_device_id ?: '—'); ?></span>
                            </div>
                            <div class="xhr-field-group">
                                <span class="xhr-field-label">RFID Number</span>
                                <span class="xhr-field-value"><?php echo htmlspecialchars($employee->rfid_number ?: '—'); ?></span>
                            </div>
                        </div>
                        <div style="color:#9ca3af; font-size:12px; text-align:center; padding:16px; border-top:1px solid #f3f4f6;">
                            Shift schedule and attendance logs will appear here when the Shift &amp; Attendance submodule is activated.
                        </div>
                    </div>
                </div>
            </div><!-- /#tab-shifts -->

            <!-- ═══ TAB: Assets ════════════════════════════════════════════ -->
            <div id="tab-assets" data-tab-group="emp-profile-tabs" style="display:none;">
                <div class="xhr-empty">
                    <span class="material-symbols-outlined">devices</span>
                    <div class="xhr-empty__title"><?php echo _l('xetuu_hr_no_assets'); ?></div>
                </div>
            </div>

            <!-- ═══ TAB: Documents ═════════════════════════════════════════ -->
            <div id="tab-documents" data-tab-group="emp-profile-tabs" style="display:none;">
                <?php if (empty($attachments)): ?>
                    <div class="xhr-empty">
                        <span class="material-symbols-outlined">folder</span>
                        <div class="xhr-empty__title"><?php echo _l('xetuu_hr_no_documents'); ?></div>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px;">
                        <h4 style="margin-top:0; margin-bottom:20px; font-weight:600; color:#111827;">Employee Documents</h4>
                        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
                            <?php foreach ($attachments as $file): ?>
                                <div style="border: 1px solid #e5e7eb; border-radius:8px; padding:16px; display:flex; align-items:center; justify-content:space-between; background-color:#f9fafb;">
                                    <div style="display:flex; align-items:center; gap:12px; overflow:hidden; margin-right:12px;">
                                        <span class="material-symbols-outlined" style="font-size:32px; color:#9ca3af; flex-shrink:0;">description</span>
                                        <div style="overflow:hidden;">
                                            <div style="font-size:14px; font-weight:600; color:#374151; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;" title="<?php echo htmlspecialchars($file->file_name); ?>">
                                                <?php echo htmlspecialchars($file->file_name); ?>
                                            </div>
                                            <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                                                Uploaded: <?php echo _d($file->dateadded); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="<?php echo base_url('uploads/hr_employees/' . $file->file_name); ?>" target="_blank" class="btn btn-default btn-icon" title="View/Download" style="flex-shrink:0;">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /.xhr-main-col -->

    </div><!-- /.xhr-profile-grid -->

</div><!-- /.xhr-content -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<!-- HR Assistance FAB -->
<button class="xhr-fab">
    <span class="material-symbols-outlined">support_agent</span>
    <span class="xhr-fab__tooltip">HR Assistance</span>
</button>

<script>
// Inline tab switcher (augments xhr.js for server-rendered tabs)
function xhrTab(el, groupId) {
    document.querySelectorAll('[data-tabs="' + groupId + '"] .xhr-tab').forEach(function(t){
        t.classList.remove('xhr-tab--active');
    });
    el.classList.add('xhr-tab--active');
    document.querySelectorAll('[data-tab-group="' + groupId + '"]').forEach(function(p){
        p.style.display = 'none';
    });
    var panelId = el.getAttribute('data-panel');
    var panel = document.getElementById(panelId);
    if (panel) panel.style.display = '';
}
</script>

<?php init_tail(); ?>
