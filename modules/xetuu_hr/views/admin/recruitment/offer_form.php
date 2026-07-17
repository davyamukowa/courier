<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'recruitment';
$base        = admin_url('xetuu_hr');
$is_edit     = !empty($offer->id);
$form_action = $is_edit
    ? $base . '/recruitment/offers/edit/' . $offer->id
    : $base . '/recruitment/offers/add';

$status_val = $offer->status ?? 'Awaiting Response';
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Breadcrumb + Title -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/recruitment/offers'; ?>" style="color:#6b7280; text-decoration:none;">Job Offers</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $is_edit ? htmlspecialchars($offer->offer_number ?? 'Edit') : 'New Job Offer'; ?></span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;">
                    <?php echo $is_edit ? htmlspecialchars($offer->applicant_name ?? $offer->offer_number ?? 'Job Offer') : 'New Job Offer'; ?>
                </h1>
                <?php if ($is_edit): ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#dcfce7; color:#16a34a;">Saved</span>
                <?php else: ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fef9c3; color:#854d0e;">Not Saved</span>
                <?php endif; ?>
                <?php
                $sp = [
                    'Awaiting Response' => ['#fef9c3','#854d0e'],
                    'Accepted'          => ['#dcfce7','#16a34a'],
                    'Rejected'          => ['#fee2e2','#dc2626'],
                    'Draft'             => ['#f3f4f6','#6b7280'],
                    'Sent'              => ['#dbeafe','#1d4ed8'],
                ];
                $sc = $sp[$status_val] ?? ['#f3f4f6','#6b7280'];
                ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;">
                    <?php echo htmlspecialchars($status_val); ?>
                </span>
            </div>
        </div>
    </div>

    <form action="<?php echo $form_action; ?>" method="post" id="offer-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($is_edit): ?><input type="hidden" name="id" value="<?php echo $offer->id; ?>"><?php endif; ?>

        <div class="row">
            <!-- Main: col-md-9 -->
            <div class="col-md-9">

                <!-- Tabs -->
                <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                    <button type="button" class="off-tab active-tab" data-tab="tab-details"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px;">
                        Details
                    </button>
                    <button type="button" class="off-tab" data-tab="tab-terms"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Job Offer Terms
                    </button>
                    <button type="button" class="off-tab" data-tab="tab-tnc"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Terms &amp; Conditions
                    </button>
                    <button type="button" class="off-tab" data-tab="tab-print"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Printing Details
                    </button>
                </div>

                <!-- Tab: Details -->
                <div id="tab-details" class="off-tab-content" style="display:block;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Job Applicant <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="applicant_id" id="applicant_id" class="form-control" required onchange="fetchApplicantDetails(this.value)">
                                            <option value="">-- Select Applicant --</option>
                                            <?php foreach ($applicants as $ap): ?>
                                            <option value="<?php echo $ap->id; ?>"
                                                    data-email="<?php echo htmlspecialchars($ap->email ?? ''); ?>"
                                                    data-opening="<?php echo $ap->job_opening_id ?? ''; ?>"
                                                <?php echo (isset($offer->applicant_id) && $offer->applicant_id == $ap->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ap->first_name . ' ' . $ap->last_name); ?>
                                                <?php if (!empty($ap->email)): ?> — <?php echo htmlspecialchars($ap->email); ?><?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Status</label>
                                        <select name="status" class="form-control" onchange="updateStatusBadge(this.value)">
                                            <?php
                                            $statuses = ['Awaiting Response', 'Accepted', 'Rejected', 'Sent', 'Draft'];
                                            foreach ($statuses as $sv):
                                            ?>
                                            <option value="<?php echo $sv; ?>" <?php echo $status_val === $sv ? 'selected' : ''; ?>><?php echo $sv; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Applicant Name</label>
                                        <input type="text" id="applicant_name_display" class="form-control" placeholder="Auto-filled" readonly
                                               value="<?php echo htmlspecialchars($offer->applicant_name ?? ''); ?>"
                                               style="background:#f9fafb; color:#374151;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Offer Date <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="date" name="offer_date" class="form-control" required
                                               value="<?php echo $offer->offer_date ?? date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Applicant Email Address</label>
                                        <input type="email" id="applicant_email_display" class="form-control" placeholder="Auto-filled"
                                               value="<?php echo htmlspecialchars($offer->applicant_email ?? ''); ?>"
                                               style="background:#f9fafb; color:#374151;" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Designation</label>
                                        <select name="designation_id" id="designation_id" class="form-control">
                                            <option value="">-- Select Designation --</option>
                                            <?php foreach ($designations as $des): ?>
                                            <option value="<?php echo $des->id; ?>"
                                                <?php echo (isset($offer->designation_id) && $offer->designation_id == $des->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($des->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Job Opening</label>
                                        <select name="job_opening_id" id="job_opening_id" class="form-control">
                                            <option value="">-- Select Job Opening --</option>
                                            <?php foreach ($openings as $jo): ?>
                                            <option value="<?php echo $jo->id; ?>"
                                                <?php echo (isset($offer->job_opening_id) && $offer->job_opening_id == $jo->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($jo->title); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Company</label>
                                        <select name="company" id="company_select" class="form-control">
                                            <option value="">-- Select Company --</option>
                                            <?php foreach ($companies as $co): ?>
                                            <option value="<?php echo htmlspecialchars($co->name); ?>"
                                                <?php echo (isset($offer->company) && $offer->company === $co->name) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($co->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Salary Offered</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">$</span>
                                            <input type="number" name="salary_offered" class="form-control" step="0.01" min="0" placeholder="0.00"
                                                   value="<?php echo $offer->salary_offered ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Joining Date</label>
                                        <input type="date" name="joining_date" class="form-control"
                                               value="<?php echo $offer->joining_date ?? ''; ?>">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tab: Job Offer Terms -->
                <div id="tab-terms" class="off-tab-content" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                                <div>
                                    <h4 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Job Offer Terms</h4>
                                    <p style="font-size:12px; color:#6b7280; margin:4px 0 0 0;">Add offer terms such as job description, notice period, leaves per year, incentives, etc.</p>
                                </div>
                            </div>

                            <table class="table" id="terms-table" style="margin-bottom:12px;">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563; width:40px;">No.</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563;">Offer Term</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563;">Value / Description</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563; width:80px; text-align:right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="terms-tbody">
                                    <?php if (!empty($offer_terms)): ?>
                                    <?php foreach ($offer_terms as $ti => $ot): ?>
                                    <tr class="term-row">
                                        <td style="padding:10px 12px; color:#6b7280; font-size:13px; vertical-align:middle;"><?php echo $ti + 1; ?></td>
                                        <td style="padding:10px 12px; vertical-align:middle;">
                                            <input type="text" name="terms[]" class="form-control" placeholder="e.g. Job Description"
                                                   value="<?php echo htmlspecialchars($ot->offer_term); ?>">
                                        </td>
                                        <td style="padding:10px 12px; vertical-align:middle;">
                                            <input type="text" name="term_values[]" class="form-control" placeholder="Value or description"
                                                   value="<?php echo htmlspecialchars($ot->value_description ?? ''); ?>">
                                        </td>
                                        <td style="padding:10px 12px; vertical-align:middle; text-align:right;">
                                            <button type="button" onclick="removeTermRow(this)" class="btn btn-danger btn-icon" title="Remove"><i class="fa fa-remove"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr id="no-terms-row">
                                        <td colspan="4" class="text-center" style="padding:30px; color:#9ca3af; font-size:13px;">
                                            No offer terms yet. Click <strong>Add Row</strong> to add one.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <button type="button" onclick="addTermRow()" class="btn btn-default" style="font-size:13px; border-radius:6px; padding:7px 16px;">
                                <i class="fa fa-plus"></i> Add Row
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab: Terms & Conditions -->
                <div id="tab-tnc" class="off-tab-content" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Terms &amp; Conditions</label>
                                <p style="font-size:12px; color:#6b7280; margin-bottom:10px;">Enter all terms and conditions related to this Job Offer. You can reference a pre-defined template.</p>
                                <textarea name="terms_conditions" id="tnc-editor" class="tinymce" rows="16"
                                          style="width:100%;"><?php echo set_value('terms_conditions', $offer->terms_conditions ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Printing Details -->
                <div id="tab-print" class="off-tab-content" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">
                            <p style="font-size:13px; color:#6b7280; margin-bottom:20px;">Configure print format settings for this Job Offer document.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Letter Head</label>
                                        <input type="text" name="letter_head" class="form-control" placeholder="e.g. Company letterhead name"
                                               value="<?php echo htmlspecialchars($offer->letter_head ?? ''); ?>">
                                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:4px;">Used when printing the offer letter.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Print Heading</label>
                                        <input type="text" name="print_heading" class="form-control" placeholder="e.g. Job Offer Letter"
                                               value="<?php echo htmlspecialchars($offer->print_heading ?? ''); ?>">
                                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:4px;">Heading shown on the printed document.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save bar -->
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                    <a href="<?php echo $base . '/recruitment/offers'; ?>" class="btn btn-default" style="border-radius:6px; padding:8px 20px;">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:8px 24px; font-weight:600;">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>

            </div><!-- /col-md-9 -->

            <!-- Sidebar: col-md-3 -->
            <div class="col-md-3">

                <!-- Document Summary -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Document Summary</span>
                    </div>
                    <div style="padding:16px;">
                        <div style="display:flex; flex-direction:column; gap:10px; font-size:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:#6b7280;">Status</span>
                                <?php $scc = $sp[$status_val] ?? ['#f3f4f6','#6b7280']; ?>
                                <span id="sb-status-badge" style="padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $scc[0]; ?>; color:<?php echo $scc[1]; ?>;">
                                    <?php echo htmlspecialchars($status_val); ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Offer #</span>
                                <span style="font-weight:600; font-family:monospace; color:#111827; font-size:11px;">
                                    <?php echo $is_edit ? htmlspecialchars($offer->offer_number ?? '—') : 'Auto-assigned'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Applicant</span>
                                <span id="sb-applicant" style="font-weight:600; color:#374151; text-align:right; max-width:110px; font-size:12px;">
                                    <?php echo $is_edit ? htmlspecialchars($offer->applicant_name ?? '—') : '—'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Offer Date</span>
                                <span style="font-weight:600; color:#374151; font-size:12px;">
                                    <?php echo !empty($offer->offer_date) ? date('d M Y', strtotime($offer->offer_date)) : '—'; ?>
                                </span>
                            </div>
                            <?php if (!empty($offer->salary_offered)): ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Salary</span>
                                <span style="font-weight:600; color:#374151; font-size:12px;">
                                    $<?php echo number_format($offer->salary_offered, 2); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($offer->joining_date)): ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Joining</span>
                                <span style="font-weight:600; color:#374151; font-size:12px;">
                                    <?php echo date('d M Y', strtotime($offer->joining_date)); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (edit mode only) -->
                <?php if ($is_edit): ?>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php if (!empty($offer->applicant_id)): ?>
                        <a href="<?php echo $base . '/recruitment/applicants/edit/' . $offer->applicant_id; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#4b5563; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">person_search</span>
                            View Applicant
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo $base . '/recruitment/appointment_letters/add?applicant_id=' . $offer->applicant_id; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#16a34a; font-weight:600; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">mail</span>
                            Create Appointment Letter
                        </a>
                        <a href="javascript:window.print()"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#4b5563; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">print</span>
                            Print Offer
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recruitment shortcuts -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recruitment</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php
                        $rec_links = [
                            ['label'=>'Job Requisition',    'icon'=>'article',          'url'=>$base.'/setup/job_requisition'],
                            ['label'=>'Job Opening',        'icon'=>'work_outline',     'url'=>$base.'/recruitment/job_openings'],
                            ['label'=>'Job Applicant',      'icon'=>'person_search',    'url'=>$base.'/recruitment/applicants'],
                            ['label'=>'Interview',          'icon'=>'record_voice_over','url'=>$base.'/recruitment/interviews'],
                            ['label'=>'Interview Feedback', 'icon'=>'rate_review',      'url'=>$base.'/recruitment/interview_feedback'],
                            ['label'=>'Job Offer',          'icon'=>'handshake',        'url'=>$base.'/recruitment/offers'],
                            ['label'=>'Appointment Letter', 'icon'=>'mail',             'url'=>$base.'/recruitment/appointment_letters'],
                        ];
                        foreach ($rec_links as $rl):
                            $active = ($rl['label'] === 'Job Offer');
                        ?>
                        <a href="<?php echo $rl['url']; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px;
                                  <?php echo $active ? 'color:#16a34a; font-weight:600; background:#f0fdf4;' : 'color:#4b5563;'; ?>">
                            <span class="material-symbols-outlined" style="font-size:16px; <?php echo $active ? 'color:#16a34a;' : 'color:#9ca3af;'; ?>"><?php echo $rl['icon']; ?></span>
                            <?php echo $rl['label']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /col-md-3 -->
        </div><!-- /row -->
    </form>
</div>

</div><!-- #wrapper -->

<script>
/* ---- Tab switching ---- */
document.querySelectorAll('.off-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.off-tab').forEach(function(b) {
            b.style.color = '#6b7280';
            b.style.borderBottom = '2px solid transparent';
            b.classList.remove('active-tab');
        });
        document.querySelectorAll('.off-tab-content').forEach(function(c) { c.style.display = 'none'; });

        this.style.color = '#16a34a';
        this.style.borderBottom = '2px solid #16a34a';
        this.classList.add('active-tab');
        var tab = this.getAttribute('data-tab');
        document.getElementById(tab).style.display = 'block';

        if (tab === 'tab-tnc') {
            if (typeof init_editor === 'function') {
                setTimeout(function() { init_editor('#tnc-editor'); }, 100);
            }
        }
    });
});

/* ---- Status badge live update ---- */
var STATUS_COLORS = {
    'Awaiting Response': ['#fef9c3','#854d0e'],
    'Accepted':          ['#dcfce7','#16a34a'],
    'Rejected':          ['#fee2e2','#dc2626'],
    'Draft':             ['#f3f4f6','#6b7280'],
    'Sent':              ['#dbeafe','#1d4ed8'],
};
function updateStatusBadge(val) {
    var badge = document.getElementById('sb-status-badge');
    if (!badge) return;
    var c = STATUS_COLORS[val] || ['#f3f4f6','#6b7280'];
    badge.style.background = c[0];
    badge.style.color      = c[1];
    badge.textContent      = val;
}

/* ---- Applicant auto-fill (from data attributes, fallback to AJAX) ---- */
var AJAX_APPLICANT = '<?php echo $base; ?>/get_applicant_json/';

function fetchApplicantDetails(applicant_id) {
    if (!applicant_id) {
        document.getElementById('applicant_name_display').value  = '';
        document.getElementById('applicant_email_display').value = '';
        document.getElementById('sb-applicant').textContent      = '—';
        return;
    }
    // Try data-attributes first (no AJAX needed)
    var sel = document.getElementById('applicant_id');
    var opt = sel.options[sel.selectedIndex];
    var name  = opt.textContent.split(' — ')[0].trim();
    var email = opt.getAttribute('data-email') || '';
    var opening_id = opt.getAttribute('data-opening') || '';

    document.getElementById('applicant_name_display').value  = name;
    document.getElementById('applicant_email_display').value = email;
    document.getElementById('sb-applicant').textContent      = name;

    // Auto-select job opening if available
    if (opening_id) {
        var joSel = document.getElementById('job_opening_id');
        for (var i = 0; i < joSel.options.length; i++) {
            if (joSel.options[i].value == opening_id) { joSel.selectedIndex = i; break; }
        }
    }
}

/* ---- Offer Terms table ---- */
var termIdx = <?php echo count($offer_terms ?? []); ?>;

function addTermRow(termName, termVal) {
    var noRow = document.getElementById('no-terms-row');
    if (noRow) noRow.remove();
    var tbody  = document.getElementById('terms-tbody');
    var rowNum = tbody.querySelectorAll('.term-row').length + 1;
    var tr = document.createElement('tr');
    tr.className = 'term-row';
    tr.innerHTML =
        '<td style="padding:10px 12px; color:#6b7280; font-size:13px; vertical-align:middle;">' + rowNum + '</td>' +
        '<td style="padding:10px 12px; vertical-align:middle;">' +
            '<input type="text" name="terms[]" class="form-control" placeholder="e.g. Job Description" value="' + (termName || '') + '">' +
        '</td>' +
        '<td style="padding:10px 12px; vertical-align:middle;">' +
            '<input type="text" name="term_values[]" class="form-control" placeholder="Value or description" value="' + (termVal || '') + '">' +
        '</td>' +
        '<td style="padding:10px 12px; vertical-align:middle; text-align:right;">' +
            '<button type="button" onclick="removeTermRow(this)" class="btn btn-danger btn-icon" title="Remove"><i class="fa fa-remove"></i></button>' +
        '</td>';
    tbody.appendChild(tr);
    termIdx++;
}

function removeTermRow(btn) {
    btn.closest('tr').remove();
    renumberTerms();
    if (!document.querySelectorAll('.term-row').length) {
        var tbody = document.getElementById('terms-tbody');
        var noRow = document.createElement('tr');
        noRow.id = 'no-terms-row';
        noRow.innerHTML = '<td colspan="4" class="text-center" style="padding:30px; color:#9ca3af; font-size:13px;">No offer terms yet. Click <strong>Add Row</strong> to add one.</td>';
        tbody.appendChild(noRow);
    }
}

function renumberTerms() {
    document.querySelectorAll('.term-row').forEach(function(r, i) { r.cells[0].textContent = i + 1; });
}
</script>

<?php init_tail(); ?>
