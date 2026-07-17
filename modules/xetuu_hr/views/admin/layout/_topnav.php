<?php defined('BASEPATH') or exit('No direct script access allowed');
$xhr_active = isset($xhr_active) ? $xhr_active : 'dashboard';
$base = admin_url('xetuu_hr');
?>
<div id="wrapper">
<div class="xhr-page">
<nav class="xhr-topnav">

    <a href="<?php echo $base; ?>" class="xhr-topnav__brand">Xetuu HR</a>

    <div class="xhr-topnav__nav">

        <!-- Dashboard (no dropdown) -->
        <div class="xhr-topnav__item">
            <a href="<?php echo $base; ?>"
               class="xhr-topnav__link <?php echo $xhr_active === 'dashboard' ? 'xhr-topnav__link--active' : ''; ?>">
                Dashboard
            </a>
        </div>

        <!-- Employees -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-employees">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active === 'employees' ? 'xhr-topnav__link--active' : ''; ?>">
                Employees <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-employees" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/employees'; ?>" class="xhr-mega-menu__nav-item <?php echo $xhr_active==='employees' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">person</span></div>
                        <span class="xhr-mega-menu__nav-label">Employees</span>
                    </a>
                    <a href="<?php echo $base . '/org_chart'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">account_tree</span></div>
                        <span class="xhr-mega-menu__nav-label">Org Chart</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Setup</p>
                    <a href="<?php echo $base . '/setup/company'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">business</span>Company</a>
                    <a href="<?php echo $base . '/setup/branch'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">location_on</span>Branch</a>
                    <a href="<?php echo $base . '/setup/department'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">corporate_fare</span>Department</a>
                    <a href="<?php echo $base . '/setup/designation'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">badge</span>Designation</a>
                    <a href="<?php echo $base . '/setup/employee_group'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">groups</span>Employee Group</a>
                    <a href="<?php echo $base . '/setup/employee_grade'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">grade</span>Employee Grade</a>
                    <div class="xhr-dropdown-menu__sep"></div>
                    <a href="<?php echo $base . '/setup/settings'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">settings</span>Settings</a>
                </div>
            </div>
        </div>

        <!-- Recruitment -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-recruitment">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='recruitment' ? 'xhr-topnav__link--active' : ''; ?>">
                Recruitment <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-recruitment" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/recruitment'; ?>" class="xhr-mega-menu__nav-item <?php echo $xhr_active==='recruitment' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">dashboard</span></div>
                        <span class="xhr-mega-menu__nav-label">Dashboard</span>
                    </a>
                    <a href="<?php echo $base . '/recruitment/applicants'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">person_search</span></div>
                        <span class="xhr-mega-menu__nav-label">Job Applicant</span>
                    </a>
                    <a href="<?php echo $base . '/recruitment/interviews'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">record_voice_over</span></div>
                        <span class="xhr-mega-menu__nav-label">Interview</span>
                    </a>
                    <a href="<?php echo $base . '/recruitment/offers'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">handshake</span></div>
                        <span class="xhr-mega-menu__nav-label">Job Offer</span>
                    </a>
                    <a href="<?php echo $base . '/recruitment/appointment_letters'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">mail</span></div>
                        <span class="xhr-mega-menu__nav-label">Appointment Letter</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Reports</p>
                    <a href="<?php echo $base . '/recruitment/analytics'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">analytics</span>Recruitment Analytics</a>
                    <div class="xhr-dropdown-menu__sep xhr-mega-menu__section-sep"></div>
                    <p class="xhr-mega-menu__section-label" style="margin-top:0;">Setup</p>
                    <a href="<?php echo $base . '/setup/staffing_plan'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">account_tree</span>Staffing Plan</a>
                    <a href="<?php echo $base . '/setup/job_requisition'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">assignment</span>Job Requisition</a>
                    <a href="<?php echo $base . '/recruitment/job_openings'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">work_outline</span>Job Opening</a>
                    <a href="<?php echo $base . '/setup/interview_type'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">category</span>Interview Type</a>
                    <a href="<?php echo $base . '/setup/interview_round'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">refresh</span>Interview Round</a>
                    <div class="xhr-dropdown-menu__sep"></div>
                    <a href="<?php echo $base . '/setup/recruitment_settings'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">settings</span>Settings</a>
                </div>
            </div>
        </div>

        <!-- Shift & Attendance -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-attendance">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='attendance' ? 'xhr-topnav__link--active' : ''; ?>">
                Shift &amp; Attendance <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-attendance" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/attendance'; ?>" class="xhr-mega-menu__nav-item <?php echo $xhr_active==='attendance' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">home</span></div>
                        <span class="xhr-mega-menu__nav-label">Home</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/roster'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">calendar_month</span></div>
                        <span class="xhr-mega-menu__nav-label">Roster</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/dashboard'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">dashboard</span></div>
                        <span class="xhr-mega-menu__nav-label">Dashboard</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/bulk_tool'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">fact_check</span></div>
                        <span class="xhr-mega-menu__nav-label">Attendance Tool</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/daily'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">login</span></div>
                        <span class="xhr-mega-menu__nav-label">Check In</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/shift_request'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">swap_horiz</span></div>
                        <span class="xhr-mega-menu__nav-label">Shift Request</span>
                    </a>
                    <a href="<?php echo $base . '/attendance/request'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap"><span class="material-symbols-outlined">edit_calendar</span></div>
                        <span class="xhr-mega-menu__nav-label">Attendance Request</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Overtime</p>
                    <a href="<?php echo $base . '/attendance/overtime_type'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">more_time</span>Overtime Type</a>
                    <a href="<?php echo $base . '/attendance/overtime'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">timer</span>Overtime Slip</a>
                    <div class="xhr-dropdown-menu__sep xhr-mega-menu__section-sep"></div>
                    <p class="xhr-mega-menu__section-label" style="margin-top:0;">Reports</p>
                    <a href="<?php echo $base . '/attendance/monthly_sheet'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">table_chart</span>Monthly Attendance Sheet</a>
                    <a href="<?php echo $base . '/attendance/monthly_sheet'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">schedule</span>Shift Attendance</a>
                    <a href="<?php echo $base . '/attendance/hours_report'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">bar_chart</span>Employee Hours Utilization</a>
                    <div class="xhr-dropdown-menu__sep xhr-mega-menu__section-sep"></div>
                    <p class="xhr-mega-menu__section-label" style="margin-top:0;">Setup</p>
                    <a href="<?php echo $base . '/attendance/shift_types'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">schedule</span>Shift Type</a>
                    <a href="<?php echo $base . '/attendance/shift_schedules'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">event_repeat</span>Shift Schedule</a>
                    <a href="<?php echo $base . '/attendance/settings'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">settings</span>Settings <span class="material-symbols-outlined" style="font-size:14px;margin-left:auto;">chevron_right</span></a>
                </div>
            </div>
        </div>

        <!-- Payroll -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-payroll">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='payroll' ? 'xhr-topnav__link--active' : ''; ?>">
                Payroll Mgmt <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-payroll" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/payroll'; ?>" class="xhr-mega-menu__nav-item <?php echo ($xhr_active==='payroll'&&!isset($xhr_payroll_sub)) ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#f0fdf4;"><span class="material-symbols-outlined" style="color:#16a34a;">dashboard</span></div>
                        <span class="xhr-mega-menu__nav-label">Dashboard</span>
                    </a>
                    <a href="<?php echo $base . '/payroll/payslips'; ?>" class="xhr-mega-menu__nav-item <?php echo (isset($xhr_payroll_sub)&&$xhr_payroll_sub==='payslips') ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#eff6ff;"><span class="material-symbols-outlined" style="color:#2563eb;">receipt</span></div>
                        <span class="xhr-mega-menu__nav-label">Payslips</span>
                    </a>
                    <a href="<?php echo $base . '/payroll/batches'; ?>" class="xhr-mega-menu__nav-item <?php echo (isset($xhr_payroll_sub)&&$xhr_payroll_sub==='batches') ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fefce8;"><span class="material-symbols-outlined" style="color:#ca8a04;">layers</span></div>
                        <span class="xhr-mega-menu__nav-label">Payslip Batches</span>
                    </a>
                    <a href="<?php echo $base . '/payroll/work_entries'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fdf4ff;"><span class="material-symbols-outlined" style="color:#9333ea;">access_time</span></div>
                        <span class="xhr-mega-menu__nav-label">Work Entries</span>
                    </a>
                    <a href="<?php echo $base . '/payroll/contracts'; ?>" class="xhr-mega-menu__nav-item <?php echo (isset($xhr_payroll_sub)&&$xhr_payroll_sub==='contracts') ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fff7ed;"><span class="material-symbols-outlined" style="color:#ea580c;">description</span></div>
                        <span class="xhr-mega-menu__nav-label">Contracts</span>
                    </a>
                    <a href="<?php echo $base . '/payroll/loans'; ?>" class="xhr-mega-menu__nav-item <?php echo (isset($xhr_payroll_sub)&&$xhr_payroll_sub==='loans') ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fff1f2;"><span class="material-symbols-outlined" style="color:#e11d48;">payments</span></div>
                        <span class="xhr-mega-menu__nav-label">Loans</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Reports</p>
                    <a href="<?php echo $base . '/payroll/reporting/summary'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">summarize</span>Payroll Summary</a>
                    <a href="<?php echo $base . '/payroll/reporting/bank_list'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">account_balance</span>Bank Transfer List</a>
                    <a href="<?php echo $base . '/payroll/reporting/cost_centre'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">pie_chart</span>Cost Centre Report</a>
                    <a href="<?php echo $base . '/payroll/reporting/ytd'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">calendar_today</span>Year-to-Date</a>
                    <div class="xhr-dropdown-menu__sep xhr-mega-menu__section-sep"></div>
                    <p class="xhr-mega-menu__section-label" style="margin-top:0;">Configuration</p>
                    <a href="<?php echo $base . '/payroll/config/companies'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">business</span>Payroll Companies</a>
                    <a href="<?php echo $base . '/payroll/config/structures'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">schema</span>Salary Structures</a>
                    <a href="<?php echo $base . '/payroll/config/frequencies'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">date_range</span>Pay Frequencies</a>
                    <a href="<?php echo $base . '/payroll/config/addons'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">extension</span>Payroll Addons</a>
                    <a href="<?php echo $base . '/payroll/config/settings'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">settings</span>Settings</a>
                </div>
            </div>
        </div>

        <!-- Leave -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-leave">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='leave' ? 'xhr-topnav__link--active' : ''; ?>">
                Leave Mgmt <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-leave" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/leave'; ?>" class="xhr-mega-menu__nav-item <?php echo ($xhr_active==='leave'&&empty($leave_active_sub)) ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#f0fdf4;"><span class="material-symbols-outlined" style="color:#16a34a;">dashboard</span></div>
                        <span class="xhr-mega-menu__nav-label">Dashboard</span>
                    </a>
                    <a href="<?php echo $base . '/leave/my_requests'; ?>" class="xhr-mega-menu__nav-item <?php echo ($leave_active_sub??'')==='my_requests' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#eff6ff;"><span class="material-symbols-outlined" style="color:#2563eb;">event_note</span></div>
                        <span class="xhr-mega-menu__nav-label">My Requests</span>
                    </a>
                    <a href="<?php echo $base . '/leave/approvals'; ?>" class="xhr-mega-menu__nav-item <?php echo ($leave_active_sub??'')==='approvals' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fefce8;"><span class="material-symbols-outlined" style="color:#ca8a04;">approval</span></div>
                        <span class="xhr-mega-menu__nav-label">Manager Approvals</span>
                    </a>
                    <a href="<?php echo $base . '/leave/hr_approvals'; ?>" class="xhr-mega-menu__nav-item <?php echo ($leave_active_sub??'')==='hr_approvals' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fdf4ff;"><span class="material-symbols-outlined" style="color:#9333ea;">verified_user</span></div>
                        <span class="xhr-mega-menu__nav-label">HR Approvals</span>
                    </a>
                    <a href="<?php echo $base . '/leave/allocations'; ?>" class="xhr-mega-menu__nav-item <?php echo ($leave_active_sub??'')==='allocations' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fff7ed;"><span class="material-symbols-outlined" style="color:#ea580c;">assignment</span></div>
                        <span class="xhr-mega-menu__nav-label">Allocations</span>
                    </a>
                    <a href="<?php echo $base . '/leave/toil'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fff1f2;"><span class="material-symbols-outlined" style="color:#e11d48;">more_time</span></div>
                        <span class="xhr-mega-menu__nav-label">TOIL</span>
                    </a>
                    <a href="<?php echo $base . '/leave/encashment'; ?>" class="xhr-mega-menu__nav-item">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#f0fdf4;"><span class="material-symbols-outlined" style="color:#059669;">payments</span></div>
                        <span class="xhr-mega-menu__nav-label">Encashment</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Reports</p>
                    <a href="<?php echo $base . '/leave/reports'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">bar_chart</span>Balance Summary</a>
                    <a href="<?php echo $base . '/leave/reports'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">table_chart</span>Leave Register</a>
                    <a href="<?php echo $base . '/leave/reports'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">calendar_month</span>Team Calendar</a>
                    <div class="xhr-dropdown-menu__sep xhr-mega-menu__section-sep"></div>
                    <p class="xhr-mega-menu__section-label" style="margin-top:0;">Configuration</p>
                    <a href="<?php echo $base . '/leave/config/types'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">category</span>Leave Types</a>
                    <a href="<?php echo $base . '/leave/config/policies'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">policy</span>Leave Policies</a>
                    <a href="<?php echo $base . '/leave/config/holidays'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">celebration</span>Holiday Calendars</a>
                </div>
            </div>
        </div>

        <!-- Performance -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-performance">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='performance' ? 'xhr-topnav__link--active' : ''; ?>">
                Performance <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-performance" class="xhr-mega-menu">
                <div class="xhr-mega-menu__primary">
                    <a href="<?php echo $base . '/performance'; ?>" class="xhr-mega-menu__nav-item <?php echo ($xhr_active==='performance'&&($perf_active_sub??'')==='') ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#f5f3ff;"><span class="material-symbols-outlined" style="color:#7c3aed;">dashboard</span></div>
                        <span class="xhr-mega-menu__nav-label">Dashboard</span>
                    </a>
                    <a href="<?php echo $base . '/performance/goals'; ?>" class="xhr-mega-menu__nav-item <?php echo ($perf_active_sub??'')==='goals' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fef9c3;"><span class="material-symbols-outlined" style="color:#ca8a04;">flag</span></div>
                        <span class="xhr-mega-menu__nav-label">Goals &amp; OKRs</span>
                    </a>
                    <a href="<?php echo $base . '/performance/appraisals'; ?>" class="xhr-mega-menu__nav-item <?php echo ($perf_active_sub??'')==='appraisals' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#fdf4ff;"><span class="material-symbols-outlined" style="color:#9333ea;">star_rate</span></div>
                        <span class="xhr-mega-menu__nav-label">Appraisals</span>
                    </a>
                    <a href="<?php echo $base . '/performance/feedback'; ?>" class="xhr-mega-menu__nav-item <?php echo ($perf_active_sub??'')==='feedback' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#eff6ff;"><span class="material-symbols-outlined" style="color:#2563eb;">360</span></div>
                        <span class="xhr-mega-menu__nav-label">360° Feedback</span>
                    </a>
                    <a href="<?php echo $base . '/performance/promotions'; ?>" class="xhr-mega-menu__nav-item <?php echo ($perf_active_sub??'')==='promotions' ? 'xhr-mega-menu__nav-item--active' : ''; ?>">
                        <div class="xhr-mega-menu__icon-wrap" style="background:#f0fdf4;"><span class="material-symbols-outlined" style="color:#16a34a;">trending_up</span></div>
                        <span class="xhr-mega-menu__nav-label">Promotions</span>
                    </a>
                </div>
                <div class="xhr-mega-menu__secondary">
                    <p class="xhr-mega-menu__section-label">Configuration</p>
                    <a href="<?php echo $base . '/performance/config/cycles'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">date_range</span>Appraisal Cycles</a>
                    <a href="<?php echo $base . '/performance/config/templates'; ?>" class="xhr-mega-menu__setup-link"><span class="material-symbols-outlined">description</span>Appraisal Templates</a>
                </div>
            </div>
        </div>

        <!-- Expenses -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-expenses">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='expenses' ? 'xhr-topnav__link--active' : ''; ?>">
                Expenses <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-expenses" class="xhr-dropdown-menu">
                <a href="<?php echo $base . '/expenses'; ?>"><span class="material-symbols-outlined">dashboard</span>Dashboard</a>
                <a href="<?php echo $base . '/expenses/claims'; ?>"><span class="material-symbols-outlined">receipt_long</span>Expense Claims</a>
                <a href="<?php echo $base . '/expenses/advances'; ?>"><span class="material-symbols-outlined">payments</span>Advances</a>
                <a href="<?php echo $base . '/expenses/travel'; ?>"><span class="material-symbols-outlined">flight</span>Travel</a>
            </div>
        </div>

        <!-- Tenure -->
        <div class="xhr-topnav__item" data-menu="xhr-menu-tenure">
            <a href="javascript:void(0);" class="xhr-topnav__link xhr-topnav__link--has-menu <?php echo $xhr_active==='tenure' ? 'xhr-topnav__link--active' : ''; ?>">
                Tenure <span class="material-symbols-outlined xhr-chevron">expand_more</span>
            </a>
            <div id="xhr-menu-tenure" class="xhr-dropdown-menu">
                <a href="<?php echo $base . '/tenure'; ?>"><span class="material-symbols-outlined">dashboard</span>Dashboard</a>
                <a href="<?php echo $base . '/tenure/onboarding'; ?>"><span class="material-symbols-outlined">how_to_reg</span>Onboarding</a>
                <a href="<?php echo $base . '/tenure/separation'; ?>"><span class="material-symbols-outlined">exit_to_app</span>Separation</a>
                <a href="<?php echo $base . '/tenure/grievances'; ?>"><span class="material-symbols-outlined">report_problem</span>Grievances</a>
                <a href="<?php echo $base . '/tenure/training'; ?>"><span class="material-symbols-outlined">school</span>Training</a>
            </div>
        </div>

    </div><!-- /.xhr-topnav__nav -->


</nav>
