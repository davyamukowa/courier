<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');
$s          = $settings; // shorthand

/* helper: get setting with default */
function xs($s, $k, $d = '') { return htmlspecialchars($s[$k] ?? $d); }
function xc($s, $k)           { return !empty($s[$k]) && $s[$k] !== '0' ? 'checked' : ''; }
function xsel($s, $k, $v)     { return (($s[$k] ?? '') == $v) ? 'selected' : ''; }
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Page shell ─────────────────────────────────────────────────────── */
.sts-page { display:flex; height:calc(100vh - 116px); overflow:hidden; background:#f8fafc; }
#sts-form { flex:1; display:flex; flex-direction:column; overflow:hidden; }

/* ── Sidebar ────────────────────────────────────────────────────────── */
.sts-sidebar {
    width:220px; flex-shrink:0; padding:28px 0;
    background:#fff; border-right:1px solid #e5e7eb;
    position:sticky; top:0; height:calc(100vh - 116px); overflow-y:auto;
}
.sts-sidebar-head { padding:0 20px 16px; border-bottom:1px solid #f3f4f6; margin-bottom:8px; }
.sts-sidebar-head h2 { font-size:15px; font-weight:700; color:#111827; margin:0 0 2px; }
.sts-sidebar-head p  { font-size:11px; color:#9ca3af; margin:0; }

.sts-nav a {
    display:flex; align-items:center; gap:10px; padding:9px 20px;
    font-size:12.5px; color:#4b5563; text-decoration:none;
    border-left:3px solid transparent; transition:all .12s;
}
.sts-nav a:hover { background:#f0fdf4; color:#16a34a; border-left-color:#d1fae5; }
.sts-nav a.active { background:#f0fdf4; color:#16a34a; font-weight:700; border-left-color:#16a34a; }
.sts-nav .material-symbols-outlined { font-size:18px; }

.sts-nav-divider { margin:6px 16px; border-top:1px solid #f3f4f6; }

/* ── Content ────────────────────────────────────────────────────────── */
.sts-content { flex:1; overflow-y:auto; padding:28px 36px; }

.sts-page-head { margin-bottom:28px; }
.sts-page-head h1 { font-size:20px; font-weight:800; color:#111827; margin:0 0 4px; }
.sts-page-head p  { font-size:13px; color:#6b7280; margin:0; }

/* ── Section cards ──────────────────────────────────────────────────── */
.sts-section { background:#fff; border:1px solid #e5e7eb; border-radius:14px; margin-bottom:20px;
    overflow:hidden; scroll-margin-top:20px; }
.sts-section-head {
    display:flex; align-items:center; gap:12px; padding:18px 22px;
    border-bottom:1px solid #f3f4f6; background:#fafafa;
}
.sts-section-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center;
    justify-content:center; flex-shrink:0; }
.sts-section-icon .material-symbols-outlined { font-size:20px; }
.sts-section-title { font-size:13.5px; font-weight:700; color:#111827; }
.sts-section-desc  { font-size:11.5px; color:#9ca3af; margin-top:1px; }
.sts-section-body  { padding:22px; }

/* ── Field row ──────────────────────────────────────────────────────── */
.sts-grid   { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.sts-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.sts-field  { display:flex; flex-direction:column; gap:4px; }
.sts-field label { font-size:12px; font-weight:600; color:#374151; }
.sts-field .hint { font-size:10.5px; color:#9ca3af; }
.sts-input, .sts-select {
    border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 11px;
    font-size:13px; color:#111827; outline:none; background:#fff;
    transition:border-color .12s; width:100%; box-sizing:border-box;
}
.sts-input:focus, .sts-select:focus { border-color:#16a34a; }
.sts-input-prefix { display:flex; }
.sts-input-prefix .pfx { background:#f3f4f6; border:1.5px solid #e5e7eb; border-right:none;
    border-radius:8px 0 0 8px; padding:8px 10px; font-size:12px; color:#6b7280; white-space:nowrap; }
.sts-input-prefix .sts-input { border-radius:0 8px 8px 0; }

/* ── Divider row ────────────────────────────────────────────────────── */
.sts-divider { border:none; border-top:1px solid #f3f4f6; margin:18px 0; }

/* ── Toggle switch ──────────────────────────────────────────────────── */
.sts-toggle-row { display:flex; align-items:center; justify-content:space-between;
    padding:12px 0; border-bottom:1px solid #f9fafb; }
.sts-toggle-row:last-child { border-bottom:none; padding-bottom:0; }
.sts-toggle-row:first-child { padding-top:0; }
.sts-toggle-info label { font-size:13px; font-weight:600; color:#111827; display:block; margin-bottom:2px; }
.sts-toggle-info p    { font-size:11.5px; color:#6b7280; margin:0; }
.sts-switch { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
.sts-switch input { opacity:0; width:0; height:0; }
.sts-switch .slider {
    position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#d1d5db; border-radius:24px; transition:.2s;
}
.sts-switch .slider:before {
    position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.sts-switch input:checked + .slider { background:#16a34a; }
.sts-switch input:checked + .slider:before { transform:translateX(20px); }

/* ── Radio cards ────────────────────────────────────────────────────── */
.sts-radio-group { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
.sts-radio-group.cols2 { grid-template-columns:repeat(2,1fr); }
.sts-radio-card { position:relative; }
.sts-radio-card input { position:absolute; opacity:0; }
.sts-radio-card label {
    display:flex; flex-direction:column; align-items:center; gap:6px; padding:14px 10px;
    border:1.5px solid #e5e7eb; border-radius:10px; cursor:pointer; transition:all .12s;
    text-align:center;
}
.sts-radio-card label:hover { border-color:#16a34a44; background:#f0fdf4; }
.sts-radio-card input:checked + label { border-color:#16a34a; background:#f0fdf4; color:#15803d; }
.sts-radio-card label .material-symbols-outlined { font-size:24px; color:#9ca3af; }
.sts-radio-card input:checked + label .material-symbols-outlined { color:#16a34a; }
.sts-radio-card label b { font-size:12px; font-weight:700; color:#111827; }
.sts-radio-card label span.sub { font-size:10.5px; color:#9ca3af; }

/* ── Data Management ────────────────────────────────────────────────── */
.sts-action-row { display:flex; align-items:center; justify-content:space-between;
    padding:14px 0; border-bottom:1px solid #f9fafb; }
.sts-action-row:last-child { border-bottom:none; padding-bottom:0; }
.sts-action-row:first-child { padding-top:0; }
.sts-action-info b  { font-size:13px; color:#111827; display:block; margin-bottom:2px; }
.sts-action-info p  { font-size:11.5px; color:#6b7280; margin:0; }
.sts-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 16px;
    border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;
    border:none; text-decoration:none; }
.sts-btn-outline { background:#fff; border:1.5px solid #e5e7eb; color:#374151; }
.sts-btn-outline:hover { border-color:#16a34a; color:#16a34a; }
.sts-btn-primary { background:#16a34a; color:#fff; }
.sts-btn-primary:hover { background:#15803d; }
.sts-btn-danger  { background:#fff; border:1.5px solid #fca5a5; color:#dc2626; }
.sts-btn-danger:hover { background:#fef2f2; }
.sts-btn .material-symbols-outlined { font-size:15px; }

/* ── Save bar ───────────────────────────────────────────────────────── */
.sts-save-bar {
    flex-shrink:0; background:#fff; border-top:1px solid #e5e7eb;
    padding:14px 36px; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 -2px 12px rgba(0,0,0,.06);
}
.sts-save-bar p { font-size:12px; color:#9ca3af; margin:0; }

/* ── Danger zone card ───────────────────────────────────────────────── */
.sts-danger-card { border-color:#fecaca; }
.sts-danger-card .sts-section-head { background:#fff5f5; }
.sts-danger-icon { background:#fee2e2; }
.sts-danger-icon .material-symbols-outlined { color:#dc2626; }

@media (max-width:768px) {
    .sts-sidebar { display:none; }
    .sts-grid, .sts-grid-3 { grid-template-columns:1fr; }
    .sts-radio-group { grid-template-columns:1fr 1fr; }
    .sts-content { padding:20px; }
}
</style>

<div class="sts-page">

<!-- ── Sidebar navigation ─────────────────────────────────────────── -->
<aside class="sts-sidebar">
    <div class="sts-sidebar-head">
        <h2>HR Settings</h2>
        <p>Xetuu HR Module</p>
    </div>
    <nav class="sts-nav">
        <a href="#org" class="active" onclick="sts_scroll(event,'org')">
            <span class="material-symbols-outlined">business_center</span> Organisation
        </a>
        <a href="#leave" onclick="sts_scroll(event,'leave')">
            <span class="material-symbols-outlined">event_available</span> Leave
        </a>
        <a href="#payroll" onclick="sts_scroll(event,'payroll')">
            <span class="material-symbols-outlined">payments</span> Payroll
        </a>
        <a href="#notifications" onclick="sts_scroll(event,'notifications')">
            <span class="material-symbols-outlined">notifications</span> Notifications
        </a>
        <a href="#selfservice" onclick="sts_scroll(event,'selfservice')">
            <span class="material-symbols-outlined">manage_accounts</span> Self-Service
        </a>
        <div class="sts-nav-divider"></div>
        <a href="#data" onclick="sts_scroll(event,'data')">
            <span class="material-symbols-outlined">database</span> Data Management
        </a>
    </nav>
</aside>

<!-- ── Main content ───────────────────────────────────────────────── -->
<form method="post" action="<?php echo $base . '/setup/settings'; ?>" id="sts-form">
<?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

<div class="sts-content" id="sts-scroll">

    <div class="sts-page-head">
        <h1>HR Settings</h1>
        <p>Configure how Xetuu HR behaves across the module — payroll, leave, notifications, and more.</p>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- ORGANISATION                                                 -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="org">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#f0fdf4;">
                <span class="material-symbols-outlined" style="color:#16a34a;">business_center</span>
            </div>
            <div>
                <div class="sts-section-title">Organisation</div>
                <div class="sts-section-desc">Employee numbering, work schedule, and company structure defaults.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <!-- Employee number -->
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin:0 0 12px;">Employee Numbering</p>
            <div class="sts-grid" style="margin-bottom:18px;">
                <div class="sts-field">
                    <label>Number Prefix</label>
                    <div class="sts-input-prefix">
                        <div class="pfx"><span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">tag</span></div>
                        <input type="text" name="employee_number_prefix" class="sts-input"
                               value="<?php echo xs($s,'employee_number_prefix','HR-EMP-'); ?>" placeholder="HR-EMP-">
                    </div>
                    <span class="hint">Added before the number — e.g. HR-EMP-</span>
                </div>
                <div class="sts-field">
                    <label>Digit Width</label>
                    <input type="number" name="employee_number_digits" class="sts-input"
                           value="<?php echo xs($s,'employee_number_digits','5'); ?>" min="3" max="10">
                    <span class="hint">Zero-padding length &mdash; 5 produces <b>00001</b></span>
                </div>
            </div>

            <hr class="sts-divider">

            <!-- Work schedule -->
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin:0 0 12px;">Work Schedule</p>
            <div class="sts-grid" style="margin-bottom:18px;">
                <div class="sts-field">
                    <label>Work Week</label>
                    <select name="work_week" class="sts-select">
                        <option value="mon-fri" <?php echo xsel($s,'work_week','mon-fri'); ?>>Monday – Friday (5 days)</option>
                        <option value="mon-sat" <?php echo xsel($s,'work_week','mon-sat'); ?>>Monday – Saturday (6 days)</option>
                        <option value="sun-thu" <?php echo xsel($s,'work_week','sun-thu'); ?>>Sunday – Thursday (5 days)</option>
                        <option value="sun-fri" <?php echo xsel($s,'work_week','sun-fri'); ?>>Sunday – Friday (6 days)</option>
                    </select>
                </div>
                <div class="sts-field">
                    <label>Working Hours / Day</label>
                    <input type="number" name="working_hours_per_day" class="sts-input"
                           value="<?php echo xs($s,'working_hours_per_day','8'); ?>" min="1" max="24" step="0.5">
                    <span class="hint">Used for overtime and leave calculations</span>
                </div>
                <div class="sts-field">
                    <label>Financial Year Start</label>
                    <select name="financial_year_start" class="sts-select">
                        <?php
                        $months = ['January','February','March','April','May','June',
                                   'July','August','September','October','November','December'];
                        foreach ($months as $mi => $mn):
                            $mv = str_pad($mi+1, 2, '0', STR_PAD_LEFT);
                        ?>
                        <option value="<?php echo $mv; ?>" <?php echo xsel($s,'financial_year_start',$mv); ?>><?php echo $mn; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="hint">First month of your HR / payroll year</span>
                </div>
                <div class="sts-field">
                    <label>Default Currency</label>
                    <select name="default_currency" class="sts-select">
                        <?php foreach ([
                            'KES'=>'KES — Kenyan Shilling','UGX'=>'UGX — Ugandan Shilling',
                            'TZS'=>'TZS — Tanzanian Shilling','ETB'=>'ETB — Ethiopian Birr',
                            'RWF'=>'RWF — Rwandan Franc','USD'=>'USD — US Dollar',
                            'GBP'=>'GBP — British Pound','EUR'=>'EUR — Euro','ZAR'=>'ZAR — South African Rand',
                        ] as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo xsel($s,'default_currency',$code); ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr class="sts-divider">

            <!-- Employment defaults -->
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin:0 0 12px;">Employment Defaults</p>
            <div class="sts-grid">
                <div class="sts-field">
                    <label>Default Probation Period</label>
                    <div class="sts-input-prefix">
                        <input type="number" name="probation_days" class="sts-input" style="border-radius:8px 0 0 8px;"
                               value="<?php echo xs($s,'probation_days','90'); ?>" min="0">
                        <div class="pfx" style="border-left:none;border-radius:0 8px 8px 0;">days</div>
                    </div>
                </div>
                <div class="sts-field">
                    <label>Default Notice Period</label>
                    <div class="sts-input-prefix">
                        <input type="number" name="notice_period_days" class="sts-input" style="border-radius:8px 0 0 8px;"
                               value="<?php echo xs($s,'notice_period_days','30'); ?>" min="0">
                        <div class="pfx" style="border-left:none;border-radius:0 8px 8px 0;">days</div>
                    </div>
                </div>
            </div>

            <hr class="sts-divider">

            <!-- Consultancy mode -->
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Consultancy / Multi-Company Mode</label>
                    <p>Allows employees to be assigned to different client companies. Enable for staffing agencies or consultancies.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="consultancy_mode" value="1" <?php echo xc($s,'consultancy_mode'); ?>>
                    <span class="slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- LEAVE                                                        -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="leave">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#eff6ff;">
                <span class="material-symbols-outlined" style="color:#2563eb;">event_available</span>
            </div>
            <div>
                <div class="sts-section-title">Leave Management</div>
                <div class="sts-section-desc">Leave year, carry-over, approvals, and balance rules.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <!-- Leave year & carry-over -->
            <div class="sts-grid" style="margin-bottom:18px;">
                <div class="sts-field">
                    <label>Leave Year Type</label>
                    <select name="leave_year_type" class="sts-select">
                        <option value="calendar"  <?php echo xsel($s,'leave_year_type','calendar'); ?>>Calendar Year (Jan 1 – Dec 31)</option>
                        <option value="financial" <?php echo xsel($s,'leave_year_type','financial'); ?>>Financial Year (based on FY start)</option>
                    </select>
                    <span class="hint">Determines when leave balances reset</span>
                </div>
                <div class="sts-field">
                    <label>Approval Levels</label>
                    <select name="leave_approval_levels" class="sts-select">
                        <option value="1" <?php echo xsel($s,'leave_approval_levels','1'); ?>>1 — Manager only</option>
                        <option value="2" <?php echo xsel($s,'leave_approval_levels','2'); ?>>2 — Manager + HR</option>
                        <option value="3" <?php echo xsel($s,'leave_approval_levels','3'); ?>>3 — Manager + HR + Director</option>
                    </select>
                </div>
                <div class="sts-field">
                    <label>Max Carry-Over Days</label>
                    <input type="number" name="leave_carry_over_max" class="sts-input"
                           value="<?php echo xs($s,'leave_carry_over_max','5'); ?>" min="0">
                    <span class="hint">0 = unlimited carry-over</span>
                </div>
                <div class="sts-field">
                    <label>Auto-Approve After (days)</label>
                    <input type="number" name="leave_auto_approve_days" class="sts-input"
                           value="<?php echo xs($s,'leave_auto_approve_days','0'); ?>" min="0">
                    <span class="hint">0 = manual approval always required</span>
                </div>
            </div>

            <hr class="sts-divider">

            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Allow Leave Carry-Over</label>
                    <p>Unused leave days roll over to the next leave year up to the max carry-over limit above.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="leave_carry_over" value="1" <?php echo xc($s,'leave_carry_over'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Allow Negative Leave Balance</label>
                    <p>Employees can apply for leave even when their balance is zero (leave in advance).</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="leave_negative_balance" value="1" <?php echo xc($s,'leave_negative_balance'); ?>>
                    <span class="slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- PAYROLL                                                      -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="payroll">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#fdf4ff;">
                <span class="material-symbols-outlined" style="color:#9333ea;">payments</span>
            </div>
            <div>
                <div class="sts-section-title">Payroll</div>
                <div class="sts-section-desc">Pay cycle, payslip template, overtime rates, and payslip delivery.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <!-- Pay cycle cards -->
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin:0 0 12px;">Pay Cycle</p>
            <div class="sts-radio-group" style="margin-bottom:20px;">
                <div class="sts-radio-card">
                    <input type="radio" name="payroll_period" id="pp_monthly" value="monthly"
                           <?php echo (($s['payroll_period'] ?? 'monthly') === 'monthly') ? 'checked' : ''; ?>>
                    <label for="pp_monthly">
                        <span class="material-symbols-outlined">calendar_month</span>
                        <b>Monthly</b>
                        <span class="sub">Once per month</span>
                    </label>
                </div>
                <div class="sts-radio-card">
                    <input type="radio" name="payroll_period" id="pp_biweekly" value="biweekly"
                           <?php echo (($s['payroll_period'] ?? '') === 'biweekly') ? 'checked' : ''; ?>>
                    <label for="pp_biweekly">
                        <span class="material-symbols-outlined">date_range</span>
                        <b>Bi-Weekly</b>
                        <span class="sub">Every 2 weeks</span>
                    </label>
                </div>
                <div class="sts-radio-card">
                    <input type="radio" name="payroll_period" id="pp_weekly" value="weekly"
                           <?php echo (($s['payroll_period'] ?? '') === 'weekly') ? 'checked' : ''; ?>>
                    <label for="pp_weekly">
                        <span class="material-symbols-outlined">view_week</span>
                        <b>Weekly</b>
                        <span class="sub">Every week</span>
                    </label>
                </div>
            </div>

            <!-- Payslip template -->
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin:0 0 12px;">Payslip Template</p>
            <div class="sts-radio-group" style="margin-bottom:20px;">
                <div class="sts-radio-card">
                    <input type="radio" name="payslip_template" id="pt_standard" value="a4_standard"
                           <?php echo (($s['payslip_template'] ?? 'a4_standard') === 'a4_standard') ? 'checked' : ''; ?>>
                    <label for="pt_standard">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <b>Standard</b>
                        <span class="sub">Classic tabular layout</span>
                    </label>
                </div>
                <div class="sts-radio-card">
                    <input type="radio" name="payslip_template" id="pt_modern" value="a4_modern"
                           <?php echo (($s['payslip_template'] ?? '') === 'a4_modern') ? 'checked' : ''; ?>>
                    <label for="pt_modern">
                        <span class="material-symbols-outlined">article</span>
                        <b>Modern</b>
                        <span class="sub">Dark header, two columns</span>
                    </label>
                </div>
                <div class="sts-radio-card">
                    <input type="radio" name="payslip_template" id="pt_minimal" value="a4_minimal"
                           <?php echo (($s['payslip_template'] ?? '') === 'a4_minimal') ? 'checked' : ''; ?>>
                    <label for="pt_minimal">
                        <span class="material-symbols-outlined">minimize</span>
                        <b>Minimal</b>
                        <span class="sub">Clean single-column</span>
                    </label>
                </div>
            </div>

            <hr class="sts-divider">

            <!-- Pay day & overtime -->
            <div class="sts-grid" style="margin-bottom:18px;">
                <div class="sts-field">
                    <label>Pay Day (day of month)</label>
                    <input type="number" name="payroll_pay_day" class="sts-input"
                           value="<?php echo xs($s,'payroll_pay_day','28'); ?>" min="1" max="31">
                    <span class="hint">Used as the default payment date on payslips</span>
                </div>
                <div class="sts-field">
                    <label>Overtime Multiplier</label>
                    <input type="number" name="overtime_multiplier" class="sts-input"
                           value="<?php echo xs($s,'overtime_multiplier','1.5'); ?>" min="1" max="5" step="0.1">
                    <span class="hint">1.5 = time-and-a-half; 2.0 = double time</span>
                </div>
            </div>

            <hr class="sts-divider">

            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Email Payslips to Employees</label>
                    <p>Automatically email a PDF payslip when a payroll run is confirmed and paid.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="email_payslips" value="1" <?php echo xc($s,'email_payslips'); ?>>
                    <span class="slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- NOTIFICATIONS                                                -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="notifications">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#fffbeb;">
                <span class="material-symbols-outlined" style="color:#d97706;">notifications</span>
            </div>
            <div>
                <div class="sts-section-title">Notifications</div>
                <div class="sts-section-desc">Email alerts for leave requests, payslips, contract renewals, and probation.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Notify HR on Leave Request</label>
                    <p>Send an email to HR managers when any employee submits a leave request.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="notify_hr_leave_request" value="1" <?php echo xc($s,'notify_hr_leave_request'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Notify Line Manager on Leave Request</label>
                    <p>Send an email to the employee's direct manager when a leave request is submitted.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="notify_manager_leave_request" value="1" <?php echo xc($s,'notify_manager_leave_request'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Notify Employee When Payslip is Ready</label>
                    <p>Send the employee an email with their payslip attached when the payroll is confirmed.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="notify_employee_payslip" value="1" <?php echo xc($s,'notify_employee_payslip'); ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <hr class="sts-divider">

            <div class="sts-grid">
                <div class="sts-field">
                    <label>Contract Expiry Warning</label>
                    <div class="sts-input-prefix">
                        <input type="number" name="contract_expiry_warn_days" class="sts-input" style="border-radius:8px 0 0 8px;"
                               value="<?php echo xs($s,'contract_expiry_warn_days','30'); ?>" min="0">
                        <div class="pfx" style="border-left:none;border-radius:0 8px 8px 0;">days before</div>
                    </div>
                    <span class="hint">Alert HR this many days before a contract expires. 0 = disabled.</span>
                </div>
                <div class="sts-field">
                    <label>Probation End Warning</label>
                    <div class="sts-input-prefix">
                        <input type="number" name="probation_end_warn_days" class="sts-input" style="border-radius:8px 0 0 8px;"
                               value="<?php echo xs($s,'probation_end_warn_days','14'); ?>" min="0">
                        <div class="pfx" style="border-left:none;border-radius:0 8px 8px 0;">days before</div>
                    </div>
                    <span class="hint">Alert HR this many days before probation ends. 0 = disabled.</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- SELF-SERVICE                                                  -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="selfservice">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#ecfdf5;">
                <span class="material-symbols-outlined" style="color:#059669;">manage_accounts</span>
            </div>
            <div>
                <div class="sts-section-title">Employee Self-Service</div>
                <div class="sts-section-desc">Control what employees can do from their own portal.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Enable Employee Self-Service Portal</label>
                    <p>Master switch — disabling this hides the self-service portal from all employees.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="selfservice_enabled" value="1" <?php echo xc($s,'selfservice_enabled'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Allow Leave Applications</label>
                    <p>Employees can submit and track their leave requests from the portal.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="selfservice_leave_apply" value="1" <?php echo xc($s,'selfservice_leave_apply'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Allow Payslip Downloads</label>
                    <p>Employees can view and download their payslips from the portal.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="selfservice_payslip_download" value="1" <?php echo xc($s,'selfservice_payslip_download'); ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="sts-toggle-row">
                <div class="sts-toggle-info">
                    <label>Allow Personal Info Updates</label>
                    <p>Employees can update their personal contact details, emergency contacts, and bank details.</p>
                </div>
                <label class="sts-switch">
                    <input type="checkbox" name="selfservice_profile_update" value="1" <?php echo xc($s,'selfservice_profile_update'); ?>>
                    <span class="slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════ -->
    <!-- DATA MANAGEMENT                                              -->
    <!-- ════════════════════════════════════════════════════════════ -->
    <div class="sts-section" id="data">
        <div class="sts-section-head">
            <div class="sts-section-icon" style="background:#f0f9ff;">
                <span class="material-symbols-outlined" style="color:#0284c7;">database</span>
            </div>
            <div>
                <div class="sts-section-title">Data Management</div>
                <div class="sts-section-desc">Reference data seeding and module maintenance.</div>
            </div>
        </div>
        <div class="sts-section-body">

            <div class="sts-action-row">
                <div class="sts-action-info">
                    <b>Load Default Reference Data</b>
                    <p>Populate departments, designations, employee groups, and grade bands from the built-in library.
                       Only inserts into empty tables &mdash; never overwrites existing records.</p>
                    <p style="margin-top:4px;font-size:10.5px;color:#9ca3af;">
                        ~150 departments &middot; ~320 designations &middot; 13 job groups &middot; 11 grade bands
                    </p>
                </div>
                <a href="<?php echo $base . '/setup/settings/seed'; ?>" class="sts-btn sts-btn-outline"
                   onclick="return confirm('Load default reference data into empty tables?')">
                    <span class="material-symbols-outlined">download</span> Load Data
                </a>
            </div>

            <div class="sts-action-row">
                <div class="sts-action-info">
                    <b>Export All Employees</b>
                    <p>Download a CSV of all active employee records including personal details and contract info.</p>
                </div>
                <a href="<?php echo $base . '/employees/export'; ?>" class="sts-btn sts-btn-outline">
                    <span class="material-symbols-outlined">download</span> Export CSV
                </a>
            </div>

        </div>
    </div>

</div>

<!-- ── Sticky save bar ──────────────────────────────────────────── -->
<div class="sts-save-bar">
    <p>Changes apply immediately for all staff using Xetuu HR.</p>
    <button type="submit" class="sts-btn sts-btn-primary" style="padding:10px 24px;font-size:13px;">
        <span class="material-symbols-outlined">save</span>
        Save All Settings
    </button>
</div>

</form>
</div><!-- /.sts-page -->

<script>
function sts_scroll(e, id) {
    e.preventDefault();
    var el = document.getElementById(id);
    if (!el) return;
    var content = document.getElementById('sts-scroll');
    content.scrollTo({ top: el.offsetTop - 20, behavior: 'smooth' });
    document.querySelectorAll('.sts-nav a').forEach(function(a){ a.classList.remove('active'); });
    e.currentTarget.classList.add('active');
}

/* Highlight active nav item on scroll */
(function() {
    var ids = ['org','leave','payroll','notifications','selfservice','data'];
    var content = document.getElementById('sts-scroll');
    content.addEventListener('scroll', function() {
        var scrollTop = content.scrollTop + 40;
        var active = ids[0];
        ids.forEach(function(id) {
            var el = document.getElementById(id);
            if (el && el.offsetTop <= scrollTop) active = id;
        });
        document.querySelectorAll('.sts-nav a').forEach(function(a) {
            var href = a.getAttribute('href');
            a.classList.toggle('active', href === '#' + active);
        });
    });
})();
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
