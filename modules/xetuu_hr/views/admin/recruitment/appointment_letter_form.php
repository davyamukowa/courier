<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'recruitment';
$base        = admin_url('xetuu_hr');
$is_edit     = !empty($letter->id);
$form_action = $is_edit
    ? $base . '/recruitment/appointment_letters/edit/' . $letter->id
    : $base . '/recruitment/appointment_letters/add';

$status_val = $letter->status ?? 'Draft';
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
                <a href="<?php echo $base . '/recruitment/appointment_letters'; ?>" style="color:#6b7280; text-decoration:none;">Appointment Letters</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $is_edit ? htmlspecialchars($letter->letter_number ?? 'Edit') : 'New Appointment Letter'; ?></span>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;">
                    <?php echo $is_edit ? htmlspecialchars($letter->letter_number ?? 'Appointment Letter') : 'New Appointment Letter'; ?>
                </h1>
                <?php if ($is_edit): ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#dcfce7; color:#16a34a;">Saved</span>
                <?php else: ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fef9c3; color:#854d0e;">Not Saved</span>
                <?php endif; ?>
                <?php
                $sp_colors = ['Draft'=>['#f3f4f6','#6b7280'], 'Sent'=>['#fef9c3','#854d0e'], 'Signed'=>['#dcfce7','#16a34a']];
                $sp_c = $sp_colors[$status_val] ?? ['#f3f4f6','#6b7280'];
                ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sp_c[0]; ?>; color:<?php echo $sp_c[1]; ?>;">
                    <?php echo htmlspecialchars($status_val); ?>
                </span>
            </div>
        </div>
    </div>

    <form action="<?php echo $form_action; ?>" method="post" id="apl-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($is_edit): ?><input type="hidden" name="id" value="<?php echo $letter->id; ?>"><?php endif; ?>

        <div class="row">
            <!-- Main col-md-9 -->
            <div class="col-md-9">

                <!-- Tabs -->
                <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                    <button type="button" class="apl-tab active-tab" data-tab="tab-details"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px;">
                        Details
                    </button>
                    <button type="button" class="apl-tab" data-tab="tab-body"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Body
                    </button>
                </div>

                <!-- Tab: Details -->
                <div id="tab-details" class="apl-tab-content" style="display:block;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Job Applicant <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="applicant_id" id="applicant_id" class="form-control" required onchange="onApplicantChange(this)">
                                            <option value="">-- Select Applicant --</option>
                                            <?php foreach ($applicants as $ap): ?>
                                            <option value="<?php echo $ap->id; ?>"
                                                    data-name="<?php echo htmlspecialchars($ap->first_name . ' ' . $ap->last_name); ?>"
                                                    data-email="<?php echo htmlspecialchars($ap->email ?? ''); ?>"
                                                <?php echo (isset($letter->applicant_id) && $letter->applicant_id == $ap->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ap->first_name . ' ' . $ap->last_name); ?>
                                                <?php if (!empty($ap->email)): ?> — <?php echo htmlspecialchars($ap->email); ?><?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Company</label>
                                        <select name="company" class="form-control">
                                            <option value="">-- Select Company --</option>
                                            <?php foreach ($companies as $co): ?>
                                            <option value="<?php echo htmlspecialchars($co->name); ?>"
                                                <?php echo (isset($letter->company) && $letter->company === $co->name) ? 'selected' : ''; ?>>
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
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Applicant Name</label>
                                        <input type="text" id="applicant_name_display" class="form-control" placeholder="Auto-filled" readonly
                                               value="<?php echo htmlspecialchars($letter->applicant_name ?? ''); ?>"
                                               style="background:#f9fafb; color:#374151;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Appointment Date <span style="color:#dc2626;">*</span>
                                        </label>
                                        <input type="date" name="appointment_date" class="form-control" required
                                               value="<?php echo $letter->appointment_date ?? date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Appointment Letter Template</label>
                                        <select name="template_id" id="template_id" class="form-control" onchange="onTemplateChange(this.value)">
                                            <option value="">-- Select Template (optional) --</option>
                                            <?php foreach ($templates as $tpl): ?>
                                            <option value="<?php echo $tpl->id; ?>"
                                                <?php echo (isset($letter->template_id) && $letter->template_id == $tpl->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tpl->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:4px;">Selecting a template auto-fills Introduction, Terms, and Closing Statement.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Status</label>
                                        <select name="status" class="form-control" onchange="updateStatusBadge(this.value)">
                                            <?php foreach (['Draft','Sent','Signed'] as $sv): ?>
                                            <option value="<?php echo $sv; ?>" <?php echo $status_val === $sv ? 'selected' : ''; ?>><?php echo $sv; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($status_val !== 'Signed'): ?>
                                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:4px;">Setting to <strong>Signed</strong> will mark the applicant as Hired and create an employee record.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="hr_signature" id="hr_signature_val" value="<?php echo htmlspecialchars($letter->hr_signature ?? ''); ?>">
                            
                            <div class="row" style="margin-top: 24px;">
                                <!-- HR Signature Column -->
                                <div class="col-md-6">
                                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: #f9fafb;">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:12px;">HR Representative Signature</label>
                                        
                                        <div id="hr-sig-display-container" style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; height: 120px; display: <?php echo !empty($letter->hr_signature) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; position: relative;">
                                            <?php if (!empty($letter->hr_signature)): ?>
                                                <img id="hr-sig-img" src="<?php echo $letter->hr_signature; ?>" style="max-height: 100%; max-width: 100%;">
                                            <?php else: ?>
                                                <img id="hr-sig-img" src="" style="max-height: 100%; max-width: 100%; display: none;">
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div id="hr-sig-canvas-container" style="position: relative; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; height: 120px; overflow: hidden; display: <?php echo empty($letter->hr_signature) ? 'block' : 'none'; ?>;">
                                            <canvas id="hr-sig-canvas" style="width: 100%; height: 100%; touch-action: none; cursor: crosshair;"></canvas>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; margin-top: 12px;">
                                            <button type="button" id="clear-hr-sig-btn" onclick="clearHrSig()" style="background: none; border: none; color: #6b7280; font-size: 12px; cursor: pointer; text-decoration: underline; display: <?php echo empty($letter->hr_signature) ? 'inline-block' : 'none'; ?>;">Clear Signature</button>
                                            <button type="button" id="change-hr-sig-btn" onclick="showHrSigCanvas()" style="background: none; border: none; color: #16a34a; font-size: 12px; cursor: pointer; text-decoration: underline; display: <?php echo !empty($letter->hr_signature) ? 'inline-block' : 'none'; ?>;">Change Signature</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Applicant Signature Column -->
                                <div class="col-md-6">
                                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: #f9fafb;">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:12px;">Applicant Signature</label>
                                        
                                        <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; height: 120px; display: flex; align-items: center; justify-content: center;">
                                            <?php if (!empty($letter->applicant_signature)): ?>
                                                <img src="<?php echo $letter->applicant_signature; ?>" style="max-height: 100%; max-width: 100%;">
                                            <?php else: ?>
                                                <div style="text-align: center; padding: 10px;">
                                                    <span style="color: #9ca3af; font-size: 13px; display: block; margin-bottom: 8px;">Awaiting candidate signature</span>
                                                    <?php if ($is_edit && !empty($letter->hash)): ?>
                                                        <div style="display: flex; align-items: center; gap: 8px; justify-content: center; background: #f3f4f6; padding: 6px 12px; border-radius: 4px; border: 1px solid #e5e7eb;">
                                                            <span style="font-size: 11px; font-family: monospace; color: #4b5563; word-break: break-all; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" id="public-link"><?php echo site_url('xetuu_hr/jobs/sign_letter/' . $letter->hash); ?></span>
                                                            <button type="button" onclick="copySigningLink()" class="btn btn-default btn-xs" style="padding: 2px 6px; font-size: 10px; display: flex; align-items: center; gap: 2px;">
                                                                <i class="fa fa-copy"></i> Copy
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tab: Body -->
                <div id="tab-body" class="apl-tab-content" style="display:none;">

                    <!-- Introduction -->
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px; font-weight:700; color:#111827;">Introduction</span>
                            <span style="font-size:11px; color:#9ca3af;">Required</span>
                        </div>
                        <div class="panel-body" style="padding:20px;">
                            <textarea name="introduction" id="intro-editor" class="tinymce" rows="10" style="width:100%;"><?php echo set_value('introduction', $letter->introduction ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Terms Table -->
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px; font-weight:700; color:#111827;">Terms</span>
                        </div>
                        <div class="panel-body" style="padding:0;">
                            <table class="table" style="margin-bottom:0;" id="terms-table">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th style="padding:10px 16px; font-size:11px; font-weight:600; color:#4b5563; width:44px; border-bottom:1px solid #e5e7eb;">No.</th>
                                        <th style="padding:10px 16px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Title</th>
                                        <th style="padding:10px 16px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Description</th>
                                        <th style="padding:10px 16px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="terms-tbody">
                                    <?php if (!empty($letter_terms)): ?>
                                    <?php foreach ($letter_terms as $ti => $lt): ?>
                                    <tr class="term-row">
                                        <td style="padding:10px 16px; color:#6b7280; font-size:13px; vertical-align:middle;"><?php echo $ti + 1; ?></td>
                                        <td style="padding:8px 16px; vertical-align:middle;">
                                            <input type="text" name="term_titles[]" class="form-control" placeholder="e.g. Location"
                                                   value="<?php echo htmlspecialchars($lt->title); ?>" style="font-weight:600;">
                                        </td>
                                        <td style="padding:8px 16px; vertical-align:middle;">
                                            <input type="text" name="term_descs[]" class="form-control" placeholder="Description"
                                                   value="<?php echo htmlspecialchars($lt->description ?? ''); ?>">
                                        </td>
                                        <td style="padding:8px 16px; vertical-align:middle; text-align:right;">
                                            <button type="button" onclick="removeTermRow(this)" class="btn btn-danger btn-icon" title="Remove"><i class="fa fa-remove"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr id="no-terms-row">
                                        <td colspan="4" class="text-center" style="padding:24px; color:#9ca3af; font-size:13px;">
                                            No terms yet. Click <strong>Add Row</strong> below or select a template.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <div style="padding:12px 16px; border-top:1px solid #f3f4f6;">
                                <button type="button" onclick="addTermRow()" class="btn btn-default" style="font-size:13px; border-radius:6px; padding:7px 16px;">
                                    <i class="fa fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Closing Statement -->
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
                            <span style="font-size:13px; font-weight:700; color:#111827;">Closing Statement</span>
                        </div>
                        <div class="panel-body" style="padding:20px;">
                            <textarea name="closing_statement" id="closing-editor" class="tinymce" rows="8" style="width:100%;"><?php echo set_value('closing_statement', $letter->closing_statement ?? ''); ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- Save bar -->
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                    <a href="<?php echo $base . '/recruitment/appointment_letters'; ?>" class="btn btn-default" style="border-radius:6px; padding:8px 20px;">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:8px 24px; font-weight:600;">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>

            </div><!-- /col-md-9 -->

            <!-- Sidebar col-md-3 -->
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
                                <span id="sb-status-badge" style="padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sp_c[0]; ?>; color:<?php echo $sp_c[1]; ?>;">
                                    <?php echo htmlspecialchars($status_val); ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Letter #</span>
                                <span style="font-weight:600; font-family:monospace; color:#111827; font-size:11px;">
                                    <?php echo $is_edit ? htmlspecialchars($letter->letter_number ?? '—') : 'Auto-assigned'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <span style="color:#6b7280; flex-shrink:0;">Applicant</span>
                                <span id="sb-applicant" style="font-weight:600; color:#374151; text-align:right; max-width:120px; font-size:12px;">
                                    <?php echo $is_edit ? htmlspecialchars($letter->applicant_name ?? '—') : '—'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Appt. Date</span>
                                <span style="font-weight:600; color:#374151; font-size:12px;">
                                    <?php echo !empty($letter->appointment_date) ? date('d M Y', strtotime($letter->appointment_date)) : '—'; ?>
                                </span>
                            </div>
                            <?php if (!empty($letter->company)): ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Company</span>
                                <span style="font-weight:600; color:#374151; text-align:right; max-width:120px; font-size:12px;"><?php echo htmlspecialchars($letter->company); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($letter->template_id)): ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Template</span>
                                <span style="font-weight:600; color:#374151; text-align:right; max-width:120px; font-size:12px;">
                                    <?php
                                    $tname = '';
                                    foreach ($templates as $tpl) {
                                        if ($tpl->id == $letter->template_id) { $tname = $tpl->name; break; }
                                    }
                                    echo htmlspecialchars($tname);
                                    ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (edit mode) -->
                <?php if ($is_edit): ?>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php if (!empty($letter->applicant_id)): ?>
                        <a href="<?php echo $base . '/recruitment/applicants/edit/' . $letter->applicant_id; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#4b5563; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">person_search</span>
                            View Applicant
                        </a>
                        <?php endif; ?>
                        <a href="javascript:window.print()"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#4b5563; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">print</span>
                            Print Letter
                        </a>
                        <a href="<?php echo $base . '/recruitment/appointment_letters/add'; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#16a34a; font-weight:600; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">add_circle</span>
                            New Letter
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
                            $active = ($rl['label'] === 'Appointment Letter');
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
document.querySelectorAll('.apl-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.apl-tab').forEach(function(b) {
            b.style.color = '#6b7280';
            b.style.borderBottom = '2px solid transparent';
            b.classList.remove('active-tab');
        });
        document.querySelectorAll('.apl-tab-content').forEach(function(c) { c.style.display = 'none'; });

        this.style.color = '#16a34a';
        this.style.borderBottom = '2px solid #16a34a';
        this.classList.add('active-tab');
        var tab = this.getAttribute('data-tab');
        document.getElementById(tab).style.display = 'block';

        if (tab === 'tab-body') {
            if (typeof init_editor === 'function') {
                setTimeout(function() {
                    init_editor('#intro-editor');
                    init_editor('#closing-editor');
                }, 120);
            }
        } else if (tab === 'tab-details') {
            if (typeof resizeHrCanvas === 'function') {
                setTimeout(resizeHrCanvas, 50);
            }
        }
    });
});

/* ---- Status badge live update ---- */
var STATUS_COLORS = {
    'Draft':  ['#f3f4f6','#6b7280'],
    'Sent':   ['#fef9c3','#854d0e'],
    'Signed': ['#dcfce7','#16a34a'],
};
function updateStatusBadge(val) {
    var badge = document.getElementById('sb-status-badge');
    if (!badge) return;
    var c = STATUS_COLORS[val] || ['#f3f4f6','#6b7280'];
    badge.style.background = c[0];
    badge.style.color      = c[1];
    badge.textContent      = val;
}

/* ---- Applicant auto-fill ---- */
function onApplicantChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    var name = opt.getAttribute('data-name') || '';
    document.getElementById('applicant_name_display').value = name;
    document.getElementById('sb-applicant').textContent     = name || '—';
}

/* ---- Template auto-fill via AJAX ---- */
var TPL_AJAX = '<?php echo $base; ?>/get_appointment_template_json/';

function onTemplateChange(tpl_id) {
    if (!tpl_id) return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', TPL_AJAX + tpl_id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);

                // Fill Introduction via TinyMCE or plain textarea
                setEditorContent('intro-editor', d.introduction || '');
                setEditorContent('closing-editor', d.closing_statement || '');

                // Populate terms table
                if (d.terms && d.terms.length > 0) {
                    var tbody = document.getElementById('terms-tbody');
                    tbody.innerHTML = '';
                    d.terms.forEach(function(t, i) { addTermRow(t.title, t.description); });
                }
            } catch(e) { console.error('Template parse error', e); }
        }
    };
    xhr.send();
}

function setEditorContent(editorId, html) {
    if (window.tinymce && tinymce.get(editorId)) {
        tinymce.get(editorId).setContent(html);
    } else {
        var el = document.getElementById(editorId);
        if (el) el.value = html;
    }
}

/* ---- Terms table ---- */
var termCount = <?php echo count($letter_terms ?? []); ?>;

function addTermRow(title, desc) {
    var noRow = document.getElementById('no-terms-row');
    if (noRow) noRow.remove();
    var tbody  = document.getElementById('terms-tbody');
    var rowNum = tbody.querySelectorAll('.term-row').length + 1;
    var tr = document.createElement('tr');
    tr.className = 'term-row';
    tr.innerHTML =
        '<td style="padding:10px 16px; color:#6b7280; font-size:13px; vertical-align:middle;">' + rowNum + '</td>' +
        '<td style="padding:8px 16px; vertical-align:middle;">' +
            '<input type="text" name="term_titles[]" class="form-control" placeholder="e.g. Location" value="' + escHtml(title || '') + '" style="font-weight:600;">' +
        '</td>' +
        '<td style="padding:8px 16px; vertical-align:middle;">' +
            '<input type="text" name="term_descs[]" class="form-control" placeholder="Description" value="' + escHtml(desc || '') + '">' +
        '</td>' +
        '<td style="padding:8px 16px; vertical-align:middle; text-align:right;">' +
            '<button type="button" onclick="removeTermRow(this)" class="btn btn-danger btn-icon" title="Remove"><i class="fa fa-remove"></i></button>' +
        '</td>';
    tbody.appendChild(tr);
    termCount++;
}

function removeTermRow(btn) {
    btn.closest('tr').remove();
    renumberTerms();
    if (!document.querySelectorAll('.term-row').length) {
        var tbody = document.getElementById('terms-tbody');
        var noRow = document.createElement('tr');
        noRow.id  = 'no-terms-row';
        noRow.innerHTML = '<td colspan="4" class="text-center" style="padding:24px; color:#9ca3af; font-size:13px;">No terms yet. Click <strong>Add Row</strong> below or select a template.</td>';
        tbody.appendChild(noRow);
    }
}

function renumberTerms() {
    document.querySelectorAll('.term-row').forEach(function(r, i) { r.cells[0].textContent = i + 1; });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ---- HR Signature canvas pad ---- */
var hrCanvas = document.getElementById('hr-sig-canvas');
var hrCtx = hrCanvas ? hrCanvas.getContext('2d') : null;
var isHrDrawing = false;

function resizeHrCanvas() {
    if (!hrCanvas) return;
    var tempCanvas = document.createElement('canvas');
    var tempCtx = tempCanvas.getContext('2d');
    tempCanvas.width = hrCanvas.width;
    tempCanvas.height = hrCanvas.height;
    if (tempCtx) {
        tempCtx.drawImage(hrCanvas, 0, 0);
    }
    
    var ratio = Math.max(window.devicePixelRatio || 1, 1);
    var oldWidth = hrCanvas.width;
    var oldHeight = hrCanvas.height;
    
    var newWidth = hrCanvas.offsetWidth * ratio;
    var newHeight = hrCanvas.offsetHeight * ratio;
    
    if (oldWidth !== newWidth || oldHeight !== newHeight) {
        hrCanvas.width = newWidth;
        hrCanvas.height = newHeight;
        var ctx = hrCanvas.getContext("2d");
        ctx.scale(ratio, ratio);
        if (tempCtx && oldWidth > 0 && oldHeight > 0) {
            ctx.drawImage(tempCanvas, 0, 0, oldWidth / ratio, oldHeight / ratio);
        }
    }
}

if (hrCanvas) {
    // Only resize if offsetWidth is valid
    if (hrCanvas.offsetWidth > 0) {
        resizeHrCanvas();
    }
    
    // Resize on window resize
    window.addEventListener('resize', function() {
        if (hrCanvas.offsetWidth > 0) {
            resizeHrCanvas();
        }
    });

    function getHrPos(touchOrEvent) {
        var rect = hrCanvas.getBoundingClientRect();
        var cx = touchOrEvent.touches ? touchOrEvent.touches[0].clientX : touchOrEvent.clientX;
        var cy = touchOrEvent.touches ? touchOrEvent.touches[0].clientY : touchOrEvent.clientY;
        return { x: cx - rect.left, y: cy - rect.top };
    }

    hrCanvas.addEventListener('mousedown', function(e) {
        isHrDrawing = true;
        var p = getHrPos(e);
        hrCtx.beginPath();
        hrCtx.moveTo(p.x, p.y);
    });

    hrCanvas.addEventListener('mousemove', function(e) {
        if (!isHrDrawing) return;
        var p = getHrPos(e);
        hrCtx.lineTo(p.x, p.y);
        hrCtx.strokeStyle = '#1e293b';
        hrCtx.lineWidth = 2.5;
        hrCtx.stroke();
    });

    hrCanvas.addEventListener('mouseup', function() {
        isHrDrawing = false;
        saveHrSig();
    });

    hrCanvas.addEventListener('touchstart', function(e) {
        isHrDrawing = true;
        var p = getHrPos(e);
        hrCtx.beginPath();
        hrCtx.moveTo(p.x, p.y);
        e.preventDefault();
    }, { passive: false });

    hrCanvas.addEventListener('touchmove', function(e) {
        if (!isHrDrawing) return;
        var p = getHrPos(e);
        hrCtx.lineTo(p.x, p.y);
        hrCtx.strokeStyle = '#1e293b';
        hrCtx.lineWidth = 2.5;
        hrCtx.stroke();
        e.preventDefault();
    }, { passive: false });

    hrCanvas.addEventListener('touchend', function(e) {
        isHrDrawing = false;
        saveHrSig();
        e.preventDefault();
    }, { passive: false });
}

function saveHrSig() {
    if (hrCanvas) {
        document.getElementById('hr_signature_val').value = hrCanvas.toDataURL();
    }
}

function clearHrSig() {
    if (hrCtx && hrCanvas) {
        hrCtx.clearRect(0, 0, hrCanvas.width, hrCanvas.height);
        document.getElementById('hr_signature_val').value = '';
    }
}

function showHrSigCanvas() {
    document.getElementById('hr-sig-display-container').style.display = 'none';
    document.getElementById('change-hr-sig-btn').style.display = 'none';
    
    document.getElementById('hr-sig-canvas-container').style.display = 'block';
    document.getElementById('clear-hr-sig-btn').style.display = 'inline-block';
    
    // Resize now that it's visible
    setTimeout(resizeHrCanvas, 50);
}

function copySigningLink() {
    var linkText = document.getElementById('public-link').textContent;
    navigator.clipboard.writeText(linkText).then(function() {
        alert('Public signing link copied to clipboard!');
    }, function() {
        // Fallback
        var tempInput = document.createElement('input');
        tempInput.value = linkText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert('Public signing link copied to clipboard!');
    });
}

// Initial trigger if canvas starts visible
setTimeout(function() {
    if (hrCanvas && hrCanvas.offsetWidth > 0) {
        resizeHrCanvas();
    }
}, 200);
</script>

<?php init_tail(); ?>
