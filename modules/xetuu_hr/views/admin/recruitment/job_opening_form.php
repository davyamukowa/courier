<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active      = 'recruitment';
$base            = admin_url('xetuu_hr');
$is_edit         = isset($opening) && $opening;
$o               = $opening;
$base_currency   = get_base_currency();
$currency_symbol = $base_currency ? $base_currency->symbol : 'KES';
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

<form id="jo-form" action="<?php echo $base . '/recruitment/job_openings'; ?>" method="post">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" value="<?php echo $is_edit ? $o->id : ''; ?>">

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/recruitment/job_openings'; ?>" style="color:#6b7280; text-decoration:none;">Job Opening</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $is_edit ? htmlspecialchars($o->title) : 'New Job Opening'; ?></span>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; margin-left:6px;
                      background:<?php echo $is_edit ? '#dcfce7' : '#fee2e2'; ?>;
                      color:<?php echo $is_edit ? '#16a34a' : '#ef4444'; ?>;">
                    <?php echo $is_edit ? 'Saved' : 'Not Saved'; ?>
                </span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">
                <?php echo $is_edit ? htmlspecialchars($o->title) : 'New Job Opening'; ?>
            </h1>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <?php if ($is_edit && $o->status === 'Open'): ?>
            <a href="<?php echo $base . '/recruitment/applicants'; ?>" class="btn btn-default"
               style="font-weight:600; padding:8px 14px; border-radius:6px; border-color:#16a34a; color:#16a34a;">
                <i class="fa fa-user-plus"></i> Add Applicant
            </a>
            <?php endif; ?>
            <a href="<?php echo $base . '/recruitment/job_openings'; ?>" class="btn btn-default" style="font-weight:600; padding:8px 16px; border-radius:6px;">Cancel</a>
            <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px;">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>

    <div class="row">

        <!-- ── Main Content ── -->
        <div class="col-md-9">

            <!-- Tabs -->
            <div style="display:flex; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                <button type="button" class="jo-tab-btn active" data-tab="tab-details"
                    style="background:none; border:none; padding:10px 20px; font-size:14px; font-weight:600; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px; cursor:pointer;">
                    Details
                </button>
                <button type="button" class="jo-tab-btn" data-tab="tab-job-desc"
                    style="background:none; border:none; padding:10px 20px; font-size:14px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">
                    Job Description
                </button>
            </div>

            <!-- ── Tab: Details ── -->
            <div id="tab-details" class="jo-tab-pane">

                <!-- Core fields -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">

                        <!-- Row 1: Job Title (full width) -->
                        <div style="margin-bottom:16px;">
                            <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                Job Title <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" required
                                   value="<?php echo $is_edit ? htmlspecialchars($o->title) : ''; ?>"
                                   placeholder="e.g. Senior Software Engineer"
                                   style="border-radius:6px; font-size:13px;">
                        </div>

                        <!-- Row 2: Designation | Department | Company -->
                        <div class="row" style="margin-bottom:16px;">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Designation <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="designation_id" id="jo_designation" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($designations as $d): ?>
                                    <option value="<?php echo $d->id; ?>" <?php echo ($is_edit && $o->designation_id == $d->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Department</label>
                                <select name="department_id" class="form-control" style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d->id; ?>" <?php echo ($is_edit && $o->department_id == $d->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Company <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="company_id" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($companies as $c): ?>
                                    <option value="<?php echo $c->id; ?>" <?php echo ($is_edit && $o->company_id == $c->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: No. of Positions | Status | Close Date -->
                        <div class="row" style="margin-bottom:16px;">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    No. of Positions <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="number" name="no_of_positions" class="form-control" min="1" required
                                       value="<?php echo $is_edit ? (int)$o->no_of_positions : 1; ?>"
                                       style="border-radius:6px; font-size:13px;">
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">
                                    Status <span style="color:#ef4444;">*</span>
                                </label>
                                <select name="status" class="form-control" required style="border-radius:6px; font-size:13px;">
                                    <?php foreach (['Open', 'On Hold', 'Closed'] as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo ($is_edit ? $o->status : 'Open') === $st ? 'selected' : ''; ?>>
                                        <?php echo $st; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Close Date</label>
                                <input type="date" name="close_date" class="form-control"
                                       value="<?php echo $is_edit && $o->close_date ? $o->close_date : ''; ?>"
                                       style="border-radius:6px; font-size:13px;">
                            </div>
                        </div>

                        <!-- Row 4: Expected Salary | Job Requisition | Publish -->
                        <div class="row">
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Expected Salary</label>
                                <div class="input-group" style="margin-bottom:0;">
                                    <span class="input-group-addon" style="font-size:12px;"><?php echo $currency_symbol; ?></span>
                                    <input type="number" step="0.01" name="expected_salary" class="form-control"
                                           value="<?php echo $is_edit && $o->expected_salary ? number_format($o->expected_salary, 2, '.', '') : ''; ?>"
                                           placeholder="0.00" style="font-size:13px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Linked Job Requisition</label>
                                <select name="job_requisition_id" class="form-control" style="border-radius:6px; font-size:13px;">
                                    <option value="">— None —</option>
                                    <?php foreach ($requisitions as $rq): ?>
                                    <option value="<?php echo $rq->id; ?>" <?php echo ($is_edit && !empty($o->job_requisition_id) && $o->job_requisition_id == $rq->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($rq->requisition_number); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4" style="display:flex; align-items:flex-end; padding-bottom:2px;">
                                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:13px; color:#374151; font-weight:500; margin:0;">
                                    <input type="checkbox" name="publish_on_website" value="1"
                                           <?php echo ($is_edit && !empty($o->publish_on_website)) ? 'checked' : ''; ?>
                                           style="width:16px; height:16px; accent-color:#16a34a; cursor:pointer;">
                                    Publish on Website
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Staffing Plan Info (auto-populated, readonly) -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:16px 20px;">
                        <h4 style="font-size:13px; font-weight:700; color:#374151; margin:0 0 12px 0;">Staffing Plan Info</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Linked Staffing Plan</label>
                                <select name="staffing_plan_id" id="jo_staffing_plan" class="form-control" style="border-radius:6px; font-size:13px;">
                                    <option value="">— None —</option>
                                    <?php foreach ($staffing_plans as $sp): ?>
                                    <option value="<?php echo $sp->id; ?>" <?php echo ($is_edit && !empty($o->staffing_plan_id) && $o->staffing_plan_id == $sp->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sp->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Planned Positions</label>
                                <input type="text" id="jo_planned_positions" class="form-control" value="—" readonly
                                       style="border-radius:6px; font-size:13px; background:#f9fafb; color:#6b7280;">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Vacancies (Planned)</label>
                                <input type="text" id="jo_planned_vacancies" class="form-control" value="—" readonly
                                       style="border-radius:6px; font-size:13px; background:#f9fafb; color:#6b7280;">
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-details -->

            <!-- ── Tab: Job Description ── -->
            <div id="tab-job-desc" class="jo-tab-pane" style="display:none;">
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div class="panel-body" style="padding:20px;">
                        <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:8px;">Job Description</label>
                        <textarea name="description" id="jo_description" class="tinymce"><?php echo $is_edit ? htmlspecialchars($o->description ?? '') : ''; ?></textarea>
                    </div>
                </div>
            </div>

        </div><!-- /col-md-9 -->

        <!-- ── Right Sidebar ── -->
        <div class="col-md-3">

            <!-- Document Summary -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 12px 0;">Document Summary</p>

                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Status</span>
                        <span style="font-size:12px; font-weight:600; color:<?php echo ($is_edit && $o->status === 'Open') ? '#16a34a' : (($is_edit && $o->status === 'Closed') ? '#dc2626' : '#854d0e'); ?>;">
                            <?php echo $is_edit ? htmlspecialchars($o->status) : 'Not Saved'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Positions</span>
                        <span style="font-size:12px; font-weight:600; color:#111827;"><?php echo $is_edit ? (int)$o->no_of_positions : '—'; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Close Date</span>
                        <span style="font-size:12px; color:#374151; font-weight:500;">
                            <?php echo ($is_edit && $o->close_date) ? date('d M Y', strtotime($o->close_date)) : 'Ongoing'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="font-size:12px; color:#6b7280;">Published</span>
                        <span style="font-size:12px; font-weight:600; color:<?php echo ($is_edit && !empty($o->publish_on_website)) ? '#16a34a' : '#9ca3af'; ?>;">
                            <?php echo ($is_edit && !empty($o->publish_on_website)) ? 'Yes' : 'No'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (edit + open) -->
            <?php if ($is_edit && $o->status === 'Open'): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 10px 0;">Quick Actions</p>
                    <a href="<?php echo $base . '/recruitment/applicants'; ?>"
                       class="btn btn-success btn-block" style="border-radius:6px; font-size:13px; font-weight:600; margin-bottom:8px;">
                        <i class="fa fa-user-plus"></i> Add Applicant
                    </a>
                    <a href="<?php echo $base . '/recruitment/interviews'; ?>"
                       class="btn btn-default btn-block" style="border-radius:6px; font-size:13px;">
                        <i class="fa fa-calendar"></i> Schedule Interview
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
                        ['url' => $base . '/setup/staffing_plan',             'icon' => 'fact_check',    'label' => 'Staffing Plan',     'active' => false],
                        ['url' => $base . '/setup/job_requisition',           'icon' => 'assignment',    'label' => 'Job Requisition',   'active' => false],
                        ['url' => $base . '/recruitment/job_openings',        'icon' => 'work',          'label' => 'Job Opening',       'active' => true],
                        ['url' => $base . '/recruitment/applicants',          'icon' => 'person_search', 'label' => 'Job Applicant',     'active' => false],
                        ['url' => $base . '/recruitment/interviews',          'icon' => 'event_note',    'label' => 'Interview',         'active' => false],
                        ['url' => $base . '/recruitment/offers',              'icon' => 'description',   'label' => 'Job Offer',         'active' => false],
                        ['url' => $base . '/recruitment/appointment_letters', 'icon' => 'mark_email_read','label'=> 'Appointment Letter', 'active' => false],
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
                        <?php foreach ([
                            ['url' => $base . '/setup/interview_type',              'icon' => 'category',     'label' => 'Interview Type'],
                            ['url' => $base . '/setup/interview_round',             'icon' => 'sync',         'label' => 'Interview Round'],
                            ['url' => $base . '/setup/appointment_letter_template', 'icon' => 'receipt_long', 'label' => 'Letter Template'],
                            ['url' => $base . '/setup/recruitment_settings',        'icon' => 'settings',     'label' => 'Settings'],
                        ] as $sl): ?>
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
// Staffing plan details map: { plan_id: { designation_id: { number_of_positions, vacancies } } }
var SP_DETAILS = <?php echo json_encode($staffing_plan_details, JSON_NUMERIC_CHECK); ?>;

$(function () {

    // ── Tab switching ──────────────────────────────────────────────────────────
    $('.jo-tab-btn').on('click', function () {
        var target = $(this).data('tab');
        $('.jo-tab-btn').css({ color: '#6b7280', borderBottomColor: 'transparent' });
        $(this).css({ color: '#16a34a', borderBottomColor: '#16a34a' });
        $('.jo-tab-pane').hide();
        $('#' + target).show();
    });

    // Init TinyMCE on job description
    init_editor('#jo_description');

    // ── Staffing plan info auto-populate ───────────────────────────────────────
    function updateSpInfo() {
        var planId  = parseInt($('#jo_staffing_plan').val()) || 0;
        var desigId = parseInt($('#jo_designation').val())   || 0;
        var detail  = (planId && desigId && SP_DETAILS[planId]) ? SP_DETAILS[planId][desigId] : null;

        if (detail) {
            $('#jo_planned_positions').val(detail.number_of_positions).css('color', '#111827');
            $('#jo_planned_vacancies').val(detail.vacancies).css('color', detail.vacancies > 0 ? '#16a34a' : '#dc2626');
        } else {
            $('#jo_planned_positions').val('—').css('color', '#9ca3af');
            $('#jo_planned_vacancies').val('—').css('color', '#9ca3af');
        }
    }

    $('#jo_staffing_plan, #jo_designation').on('change', updateSpInfo);
    updateSpInfo(); // populate on page load (edit mode)

});
</script>
