<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active      = 'recruitment';
$base            = admin_url('xetuu_hr');
$is_edit         = isset($req) && $req;
$r               = $req;
$base_currency   = get_base_currency();
$currency_symbol = $base_currency ? $base_currency->symbol : 'KES';

$statuses = ['Pending', 'Open', 'Open & Approved', 'Filled', 'Cancelled'];

$req_number = $is_edit ? htmlspecialchars($r->requisition_number) : 'New Requisition';
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

<form id="jreq-form" action="<?php echo $base . '/setup/job_requisition'; ?>" method="post">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" value="<?php echo $is_edit ? $r->id : ''; ?>">

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/setup/job_requisition'; ?>" style="color:#6b7280; text-decoration:none;">Job Requisition</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $req_number; ?></span>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; margin-left:6px;
                      background:<?php echo $is_edit ? '#dcfce7' : '#fee2e2'; ?>;
                      color:<?php echo $is_edit ? '#16a34a' : '#ef4444'; ?>;">
                    <?php echo $is_edit ? 'Saved' : 'Not Saved'; ?>
                </span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">
                <?php echo $is_edit ? $req_number : 'New Job Requisition'; ?>
            </h1>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="<?php echo $base . '/setup/job_requisition'; ?>" class="btn btn-default" style="font-weight:600; padding:8px 16px; border-radius:6px;">Cancel</a>
            <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px;">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>

    <!-- Two-column layout -->
    <div class="row">

        <!-- ── Main Content ─── -->
        <div class="col-md-9">

            <!-- Tabs -->
            <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                <button type="button" class="jreq-tab-btn active" data-tab="tab-details"
                    style="background:none; border:none; padding:10px 20px; font-size:14px; font-weight:600; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px; cursor:pointer;">
                    Details
                </button>
                <button type="button" class="jreq-tab-btn" data-tab="tab-job-desc"
                    style="background:none; border:none; padding:10px 20px; font-size:14px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">
                    Job Description
                </button>
            </div>

            <!-- ── Tab: Details ── -->
            <div id="tab-details" class="jreq-tab-pane">

                <!-- Row 1: Naming Series | No. of Positions | Company -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Naming Series</label>
                                <div class="input-group" style="margin-bottom:0;">
                                    <input type="text" class="form-control" value="HR-HIREQ-" readonly
                                           style="border-radius:6px 0 0 6px; background:#f9fafb; font-size:13px; color:#6b7280;">
                                    <span class="input-group-addon" style="border-radius:0 6px 6px 0; font-size:12px; background:#f9fafb;">
                                        <?php echo $is_edit ? str_replace('HR-HIREQ-', '', $r->requisition_number) : 'Auto'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    No. of Positions <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="number" name="no_of_positions" class="form-control" min="1"
                                       value="<?php echo $is_edit ? (int)$r->no_of_positions : 1; ?>"
                                       required style="border-radius:6px; font-size:13px;">
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Company <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="company_id" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($companies as $c): ?>
                                    <option value="<?php echo $c->id; ?>" <?php echo ($is_edit && $r->company_id == $c->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row" style="margin-top:16px;">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Designation <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="designation_id" id="designation_id" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($designations as $d): ?>
                                    <option value="<?php echo $d->id; ?>" <?php echo ($is_edit && $r->designation_id == $d->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Expected Compensation <span style="color:#ef4444;">*</span>
                                </label>
                                <div class="input-group" style="margin-bottom:0;">
                                    <span class="input-group-addon" style="font-size:12px;"><?php echo $currency_symbol; ?></span>
                                    <input type="number" step="0.01" name="expected_salary" class="form-control"
                                           value="<?php echo $is_edit && $r->expected_salary ? number_format($r->expected_salary, 2, '.', '') : ''; ?>"
                                           style="font-size:13px;" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Status <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="status" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <?php foreach ($statuses as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo ($is_edit ? $r->status : 'Pending') === $st ? 'selected' : ''; ?>>
                                        <?php echo $st; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row" style="margin-top:16px;">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Department</label>
                                <select name="department_id" class="form-control" style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d->id; ?>" <?php echo ($is_edit && $r->department_id == $d->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Staffing Plan</label>
                                <select name="staffing_plan_id" class="form-control" style="border-radius:6px; font-size:13px;">
                                    <option value="">— None —</option>
                                    <?php foreach ($staffing_plans as $sp): ?>
                                    <option value="<?php echo $sp->id; ?>" <?php echo ($is_edit && $r->staffing_plan_id == $sp->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sp->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requested By Section -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">
                        <h4 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 16px 0; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Requested By</h4>
                        <div class="col-md-6" style="padding-left:0;">
                            <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                Requested By <span style="color:#ef4444;">*</span>
                            </label>
                            <select name="requested_by" class="form-control" required style="border-radius:6px; font-size:13px;">
                                <option value="">— Select Staff —</option>
                                <?php foreach ($staff_list as $s): ?>
                                <option value="<?php echo $s['staffid']; ?>" <?php echo ($is_edit && $r->requested_by == $s['staffid']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Timelines Section -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">
                        <h4 style="font-size:14px; font-weight:700; color:#111827; margin:0 0 16px 0; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Timelines</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Posting Date <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="date" name="posting_date" class="form-control" required
                                       value="<?php echo $is_edit && $r->posting_date ? $r->posting_date : date('Y-m-d'); ?>"
                                       style="border-radius:6px; font-size:13px;">
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Expected By</label>
                                <input type="date" name="expected_by_date" class="form-control"
                                       value="<?php echo $is_edit && $r->expected_by_date ? $r->expected_by_date : ''; ?>"
                                       style="border-radius:6px; font-size:13px;">
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-details -->

            <!-- ── Tab: Job Description ── -->
            <div id="tab-job-desc" class="jreq-tab-pane" style="display:none;">

                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">
                        <div style="margin-bottom:20px;">
                            <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:8px;">
                                Job Description <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="job_description" id="job_description" class="tinymce"><?php echo $is_edit ? htmlspecialchars($r->job_description ?? '') : ''; ?></textarea>
                        </div>

                        <div>
                            <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:8px;">Reason for Requesting</label>
                            <textarea name="reason" class="form-control"
                                      rows="5" style="border-radius:6px; font-size:13px; resize:vertical;"><?php echo $is_edit ? htmlspecialchars($r->reason ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-job-desc -->

        </div><!-- /col-md-9 -->

        <!-- ── Right Sidebar ── -->
        <div class="col-md-3">

            <!-- Document Summary -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 12px 0;">Document Summary</p>

                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Requisition #</span>
                        <span style="font-size:12px; font-weight:600; color:#111827;"><?php echo $is_edit ? htmlspecialchars($r->requisition_number) : 'Auto'; ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Status</span>
                        <span id="sidebar-status" style="font-size:12px; font-weight:600; color:<?php echo $is_edit ? '#16a34a' : '#ef4444'; ?>;">
                            <?php echo $is_edit ? htmlspecialchars($r->status) : 'Not Saved'; ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Positions</span>
                        <span style="font-size:12px; font-weight:600; color:#111827;"><?php echo $is_edit ? (int)$r->no_of_positions : '—'; ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Posting Date</span>
                        <span style="font-size:12px; color:#374151; font-weight:500;">
                            <?php echo ($is_edit && $r->posting_date) ? date('d M Y', strtotime($r->posting_date)) : date('d M Y'); ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="font-size:12px; color:#6b7280;">Expected By</span>
                        <span style="font-size:12px; color:#374151; font-weight:500;">
                            <?php echo ($is_edit && $r->expected_by_date) ? date('d M Y', strtotime($r->expected_by_date)) : '—'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (edit mode only) -->
            <?php if ($is_edit): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 10px 0;">Quick Actions</p>
                    <?php if (in_array($r->status, ['Open', 'Open & Approved'])): ?>
                    <a href="<?php echo admin_url('xetuu_hr/recruitment/job_openings'); ?>"
                       class="btn btn-success btn-block" style="border-radius:6px; font-size:13px; font-weight:600; margin-bottom:8px;">
                        <i class="fa fa-plus"></i> Create Job Opening
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo $base . '/setup/staffing_plan'; ?>"
                       class="btn btn-default btn-block" style="border-radius:6px; font-size:13px;">
                        <i class="fa fa-list"></i> View Staffing Plans
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recruitment Shortcuts -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 10px 0;">Recruitment</p>

                    <?php
                    $shortcuts = [
                        ['url' => $base . '/setup/staffing_plan',              'icon' => 'fact_check',    'label' => 'Staffing Plan',     'active' => false],
                        ['url' => $base . '/setup/job_requisition',            'icon' => 'assignment',    'label' => 'Job Requisition',   'active' => true],
                        ['url' => $base . '/recruitment/job_openings',         'icon' => 'work',          'label' => 'Job Opening',       'active' => false],
                        ['url' => $base . '/recruitment/applicants',           'icon' => 'person_search', 'label' => 'Job Applicant',     'active' => false],
                        ['url' => $base . '/recruitment/interviews',           'icon' => 'event_note',    'label' => 'Interview',         'active' => false],
                        ['url' => $base . '/recruitment/offers',               'icon' => 'description',   'label' => 'Job Offer',         'active' => false],
                        ['url' => $base . '/recruitment/appointment_letters',  'icon' => 'mark_email_read','label'=> 'Appointment Letter', 'active' => false],
                    ];
                    foreach ($shortcuts as $s):
                    ?>
                    <a href="<?php echo $s['url']; ?>"
                       style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:6px; text-decoration:none; margin-bottom:2px;
                              <?php echo $s['active'] ? 'background:#f0faf4; color:#16a34a;' : 'color:#4b5563;'; ?>">
                        <span class="material-symbols-outlined" style="font-size:18px; color:<?php echo $s['active'] ? '#16a34a' : '#6b7280'; ?>;"><?php echo $s['icon']; ?></span>
                        <span style="font-size:13px; font-weight:<?php echo $s['active'] ? '600' : '500'; ?>;"><?php echo $s['label']; ?></span>
                    </a>
                    <?php endforeach; ?>

                    <div style="margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6;">
                        <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 8px 0;">Setup</p>
                        <?php
                        $setup_links = [
                            ['url' => $base . '/setup/interview_type',              'icon' => 'category',     'label' => 'Interview Type'],
                            ['url' => $base . '/setup/interview_round',             'icon' => 'sync',         'label' => 'Interview Round'],
                            ['url' => $base . '/setup/appointment_letter_template', 'icon' => 'receipt_long', 'label' => 'Letter Template'],
                            ['url' => $base . '/setup/recruitment_settings',        'icon' => 'settings',     'label' => 'Settings'],
                        ];
                        foreach ($setup_links as $sl):
                        ?>
                        <a href="<?php echo $sl['url']; ?>"
                           style="display:flex; align-items:center; gap:10px; padding:7px 10px; border-radius:6px; text-decoration:none; color:#4b5563; margin-bottom:2px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $sl['icon']; ?></span>
                            <span style="font-size:12px; font-weight:500;"><?php echo $sl['label']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div><!-- /col-md-3 -->

    </div><!-- /row -->

</form>
</div><!-- /.xhr-setup-page -->

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script>
$(function () {

    // ── Tab switching ──────────────────────────────────────────────────────────
    $('.jreq-tab-btn').on('click', function () {
        var target = $(this).data('tab');

        $('.jreq-tab-btn').each(function () {
            $(this).css({ color: '#6b7280', borderBottomColor: 'transparent' });
        });
        $(this).css({ color: '#16a34a', borderBottomColor: '#16a34a' });

        $('.jreq-tab-pane').hide();
        $('#' + target).show();
    });

    // Init TinyMCE on job description
    init_editor('#job_description');

});
</script>
