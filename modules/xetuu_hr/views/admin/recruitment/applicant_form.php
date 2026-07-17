<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');
$is_edit    = !empty($applicant);

$stage_colors = [
    'Applied'   => ['bg' => '#eff6ff',  'color' => '#1d4ed8'],
    'Screening' => ['bg' => '#fef9c3',  'color' => '#854d0e'],
    'Interview' => ['bg' => '#fff7ed',  'color' => '#c2410c'],
    'Offer'     => ['bg' => '#f0fdf4',  'color' => '#15803d'],
    'Hired'     => ['bg' => '#dcfce7',  'color' => '#16a34a'],
    'Rejected'  => ['bg' => '#fee2e2',  'color' => '#dc2626'],
];
$cur_stage = $is_edit ? $applicant->stage : 'Applied';
$sc = $stage_colors[$cur_stage] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <form action="<?php echo $base . '/recruitment/applicants'; ?>" method="post" enctype="multipart/form-data">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo $applicant->id; ?>">
        <?php endif; ?>

    <!-- Breadcrumb + header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:6px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/recruitment/applicants'; ?>" style="color:#6b7280; text-decoration:none;">Job Applicant</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">
                    <?php echo $is_edit ? htmlspecialchars($applicant->first_name . ' ' . $applicant->last_name) : 'New Applicant'; ?>
                </span>
                <?php if ($is_edit): ?>
                <span style="display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; background:#dcfce7; color:#16a34a; margin-left:4px;">Saved</span>
                <?php else: ?>
                <span style="display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; background:#fef9c3; color:#854d0e; margin-left:4px;">Not Saved</span>
                <?php endif; ?>
            </div>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">
                    <?php echo $is_edit ? htmlspecialchars($applicant->first_name . ' ' . $applicant->last_name) : 'New Job Applicant'; ?>
                </h1>
                <?php if ($is_edit): ?>
                <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                    <?php echo htmlspecialchars($cur_stage); ?>
                </span>
                <?php if ($applicant->applicant_number): ?>
                <span style="font-size:12px; color:#9ca3af; font-family:monospace;"><?php echo htmlspecialchars($applicant->applicant_number); ?></span>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
            <button type="submit" class="btn btn-success"
                style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa fa-save"></i> <?php echo $is_edit ? 'Update Applicant' : 'Save Applicant'; ?>
            </button>
            <?php if ($is_edit && !in_array($cur_stage, ['Offer', 'Hired', 'Rejected'])): ?>
            <a href="<?php echo $base . '/recruitment/interviews/add?applicant_id=' . $applicant->id; ?>"
               class="btn btn-default" style="border-radius:6px; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                <span class="material-symbols-outlined" style="font-size:16px;">event</span> Schedule Interview
            </a>
            <?php endif; ?>
            <?php if ($is_edit && in_array($cur_stage, ['Offer', 'Hired'])): ?>
            <a href="<?php echo $base . '/recruitment/offers/add?applicant_id=' . $applicant->id; ?>"
               class="btn btn-default" style="border-radius:6px; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                <span class="material-symbols-outlined" style="font-size:16px;">description</span> Create Job Offer
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Recruitment Pipeline Stepper ──────────────────────────────── -->
    <?php
    $pipeline = ['Applied', 'Screening', 'Interview', 'Offer', 'Hired'];
    $cur_pipeline_idx = array_search($cur_stage, $pipeline);
    if ($cur_pipeline_idx === false) { $cur_pipeline_idx = -1; } // Rejected
    ?>
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 22px; margin-bottom:22px; overflow-x:auto;">
        <div style="display:flex; align-items:center; min-width:380px;">
            <?php foreach ($pipeline as $pi => $pstage):
                $p_done    = $pi < $cur_pipeline_idx;
                $p_current = $pi === $cur_pipeline_idx;
                $p_state   = $p_done ? 'done' : ($p_current ? 'current' : 'pending');
                $pbg       = $p_done || $p_current ? '#16a34a' : '#fff';
                $pborder   = $p_done || $p_current ? '#15803d' : '#e5e7eb';
                $ptextc    = $p_done || $p_current ? '#fff' : '#9ca3af';
                $plabelc   = $p_current ? '#111827' : ($p_done ? '#16a34a' : '#9ca3af');
                $plblw     = $p_current ? '700' : '500';
                $pline     = ($p_done || $p_current) ? '#16a34a' : '#e5e7eb';
                $is_last   = $pi === count($pipeline) - 1;
            ?>
            <div style="display:flex; align-items:center; <?php echo !$is_last ? 'flex:1;' : ''; ?>">
                <div style="display:flex; flex-direction:column; align-items:center; gap:5px;">
                    <div style="width:32px; height:32px; border-radius:50%; background:<?php echo $pbg; ?>; border:2px solid <?php echo $pborder; ?>; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:<?php echo $ptextc; ?>; flex-shrink:0;">
                        <?php if ($p_done): ?>
                        <span class="material-symbols-outlined" style="font-size:16px;">check</span>
                        <?php else: ?>
                        <?php echo $pi + 1; ?>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:10.5px; font-weight:<?php echo $plblw; ?>; color:<?php echo $plabelc; ?>; white-space:nowrap;"><?php echo $pstage; ?></span>
                </div>
                <?php if (!$is_last): ?>
                <div style="flex:1; height:2px; background:<?php echo $pline; ?>; margin:0 6px; margin-bottom:18px;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if ($cur_stage === 'Rejected'): ?>
            <div style="display:flex; align-items:center;">
                <div style="width:12px; height:2px; background:#ef4444; margin:0 6px; margin-bottom:18px;"></div>
                <div style="display:flex; flex-direction:column; align-items:center; gap:5px;">
                    <div style="width:32px; height:32px; border-radius:50%; background:#fee2e2; border:2px solid #ef4444; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#dc2626;">close</span>
                    </div>
                    <span style="font-size:10.5px; font-weight:700; color:#dc2626; white-space:nowrap;">Rejected</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>


        <div class="row">

            <!-- ── Main content col-md-9 ──────────────────────────────────────── -->
            <div class="col-md-9">

                <!-- Tab nav -->
                <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                    <button type="button" class="apptab-btn active" data-tab="tab-details"
                        style="background:none; border:none; padding:10px 20px; font-size:13px; font-weight:600; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px; cursor:pointer;">
                        Details
                    </button>
                    <button type="button" class="apptab-btn" data-tab="tab-cover"
                        style="background:none; border:none; padding:10px 20px; font-size:13px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">
                        Cover Letter
                    </button>
                    <?php if ($is_edit): ?>
                    <button type="button" class="apptab-btn" data-tab="tab-interviews"
                        style="background:none; border:none; padding:10px 20px; font-size:13px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer; display:flex; align-items:center; gap:5px;">
                        Interviews
                        <?php if (!empty($applicant_interviews)): ?>
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; background:#16a34a; color:#fff; border-radius:50%; font-size:10px; font-weight:700;"><?php echo count($applicant_interviews); ?></span>
                        <?php endif; ?>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Tab: Details -->
                <div id="tab-details" class="apptab-pane">

                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div class="panel-body" style="padding:20px;">

                            <!-- Row 1: First Name | Last Name -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">First Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required
                                        value="<?php echo $is_edit ? htmlspecialchars($applicant->first_name) : ''; ?>"
                                        placeholder="First name" style="border-radius:6px;">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Last Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" name="last_name" class="form-control" required
                                        value="<?php echo $is_edit ? htmlspecialchars($applicant->last_name) : ''; ?>"
                                        placeholder="Last name" style="border-radius:6px;">
                                </div>
                            </div>

                            <!-- Row 2: Email | Phone -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Email Address <span style="color:#ef4444;">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                        value="<?php echo $is_edit ? htmlspecialchars($applicant->email) : ''; ?>"
                                        placeholder="applicant@email.com" style="border-radius:6px;">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Phone Number</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?php echo $is_edit ? htmlspecialchars($applicant->phone) : ''; ?>"
                                        placeholder="+254 7XX XXX XXX" style="border-radius:6px;">
                                </div>
                            </div>

                            <!-- Row 3: Job Opening | Stage -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-8">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Job Opening <span style="color:#ef4444;">*</span></label>
                                    <select name="job_opening_id" class="form-control" required style="border-radius:6px;">
                                        <option value="">— Select Job Opening —</option>
                                        <?php foreach ($openings as $o): ?>
                                        <option value="<?php echo $o->id; ?>"<?php echo ($is_edit && $applicant->job_opening_id == $o->id) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($o->title); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label style="font-size:12px; font-weight:700; color:#16a34a; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">ads_click</span> Update Applicant Stage
                                    </label>
                                    <select name="stage" class="form-control" 
                                            style="border-radius:6px; border:2px solid #16a34a; background:#f0fdf4; color:#15803d; font-weight:700; cursor:pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.1);"
                                            onchange="this.style.borderColor='#16a34a'; this.style.background='#f0fdf4';"
                                            onfocus="this.style.borderColor='#15803d'; this.style.boxShadow='0 0 0 3px rgba(22, 163, 74, 0.2)';"
                                            onblur="this.style.borderColor='#16a34a'; this.style.boxShadow='0 4px 6px -1px rgba(22, 163, 74, 0.1)';">
                                        <optgroup label="Default Stages">
                                            <?php foreach (['Applied','Screening','Interview','Offer','Hired','Rejected'] as $s): ?>
                                            <option value="<?php echo $s; ?>"<?php echo ($cur_stage === $s) ? ' selected' : ''; ?>><?php echo $s; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php if (!empty($interview_types)): ?>
                                        <optgroup label="Interview Types">
                                            <?php foreach ($interview_types as $it): ?>
                                                <option value="<?php echo htmlspecialchars($it->name); ?>"<?php echo ($cur_stage === $it->name) ? ' selected' : ''; ?>><?php echo htmlspecialchars($it->name); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 4: Source | Source Name -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Source</label>
                                    <select name="source" id="source-select" class="form-control" style="border-radius:6px;"
                                        onchange="toggleSourceName(this.value)">
                                        <option value="">— Select Source —</option>
                                        <?php
                                        $sources = ['Campaign','Employee Referral','Walk In','Website Listing'];
                                        foreach ($sources as $src):
                                            $sel = ($is_edit && $applicant->source === $src) ? ' selected' : '';
                                        ?>
                                        <option value="<?php echo $src; ?>"<?php echo $sel; ?>><?php echo $src; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6" id="source-name-wrap" style="display:<?php echo ($is_edit && $applicant->source === 'Employee Referral') ? 'block' : 'none'; ?>;">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Source Name (Referring Employee)</label>
                                    <select name="source_name" class="form-control" style="border-radius:6px;">
                                        <option value="">— Select Employee —</option>
                                        <?php foreach ($staff_list as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s->firstname . ' ' . $s->lastname); ?>"
                                            <?php echo ($is_edit && $applicant->source_name === $s->firstname . ' ' . $s->lastname) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s->firstname . ' ' . $s->lastname); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- CV/Resume upload -->
                            <div style="margin-bottom:0;">
                                <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">CV / Resume</label>
                                <?php if ($is_edit && !empty($applicant->resume)): ?>
                                <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                                    <a href="<?php echo base_url('uploads/xetuu_hr/resumes/' . $applicant->resume); ?>" target="_blank"
                                       class="btn btn-default btn-xs" style="border-radius:4px;">
                                        <i class="fa fa-download"></i> Download Current CV
                                    </a>
                                    <span style="font-size:12px; color:#6b7280;">Upload a new file to replace</span>
                                </div>
                                <?php endif; ?>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" class="form-control" style="border-radius:6px; padding:6px;">
                                <span style="font-size:11px; color:#9ca3af;">PDF, DOC or DOCX. Max 5MB.</span>
                            </div>

                        </div>
                    </div>

                </div><!-- /tab-details -->

                <!-- Tab: Cover Letter -->
                <div id="tab-cover" class="apptab-pane" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div class="panel-body" style="padding:20px;">
                            <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:8px; display:block;">Cover Letter / Notes</label>
                            <textarea name="cover_letter" id="cover-letter-editor" class="tinymce" style="width:100%;"><?php echo $is_edit ? htmlspecialchars($applicant->cover_letter ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                </div><!-- /tab-cover -->

                <!-- Tab: Interviews -->
                <?php if ($is_edit): ?>
                <div id="tab-interviews" class="apptab-pane" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <div style="font-size:14px; font-weight:600; color:#111827;">Interview History</div>
                        <a href="<?php echo $base . '/recruitment/interviews/add?applicant_id=' . $applicant->id; ?>"
                           class="btn btn-success btn-sm"
                           style="background:#16a34a; border-color:#16a34a; border-radius:6px; display:inline-flex; align-items:center; gap:5px; font-size:12px;">
                            <i class="fa fa-plus"></i> Schedule Interview
                        </a>
                    </div>
                    <?php if (empty($applicant_interviews)): ?>
                    <div style="background:#fff; border:1px dashed #e5e7eb; border-radius:10px; padding:40px 24px; text-align:center;">
                        <span class="material-symbols-outlined" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:10px;">event_busy</span>
                        <div style="font-size:14px; font-weight:600; color:#374151; margin-bottom:5px;">No interviews scheduled yet</div>
                        <div style="font-size:12px; color:#9ca3af;">Click "Schedule Interview" to set one up for this applicant.</div>
                    </div>
                    <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php
                        $int_status_colors = [
                            'Scheduled' => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                            'Completed' => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                            'Cancelled' => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                        ];
                        $int_result_colors = [
                            'Pass'    => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                            'Fail'    => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                            'Pending' => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
                        ];
                        foreach ($applicant_interviews as $int):
                            $isc = $int_status_colors[$int->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                            $irc = $int_result_colors[$int->result ?? 'Pending'] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                        ?>
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:9px; padding:14px 16px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                <div>
                                    <div style="font-size:12px; font-weight:700; color:#111827; margin-bottom:4px;">
                                        <?php echo htmlspecialchars($int->interview_number ?? ('INT-' . $int->id)); ?>
                                        <?php if ($int->round_name): ?>
                                        <span style="font-size:11px; color:#6b7280; font-weight:400;"> · <?php echo htmlspecialchars($int->round_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap; margin-bottom:6px;">
                                        <span style="font-size:10.5px; padding:2px 8px; border-radius:20px; font-weight:600; background:<?php echo $isc['bg']; ?>; color:<?php echo $isc['color']; ?>;"><?php echo htmlspecialchars($int->status); ?></span>
                                        <span style="font-size:10.5px; padding:2px 8px; border-radius:20px; font-weight:600; background:<?php echo $irc['bg']; ?>; color:<?php echo $irc['color']; ?>;"><?php echo htmlspecialchars($int->result ?? 'Pending'); ?></span>
                                        <?php if ($int->rating): ?>
                                        <span style="font-size:10.5px; color:#f59e0b;">
                                            <?php for ($s=1; $s<=5; $s++) echo $s <= $int->rating ? '★' : '☆'; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:11.5px; color:#6b7280; display:flex; gap:12px; flex-wrap:wrap;">
                                        <?php if ($int->interview_date): ?>
                                        <span><span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">calendar_today</span> <?php echo date('d M Y', strtotime($int->interview_date)); ?>
                                        <?php if ($int->from_time): ?> <?php echo date('H:i', strtotime($int->from_time)); ?><?php if ($int->to_time): ?>–<?php echo date('H:i', strtotime($int->to_time)); endif; ?><?php endif; ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($int->interviewer_name): ?>
                                        <span><span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">person</span> <?php echo htmlspecialchars($int->interviewer_name); ?></span>
                                        <?php endif; ?>
                                        <?php if ($int->type_name): ?>
                                        <span><span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">category</span> <?php echo htmlspecialchars($int->type_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?php echo $base . '/recruitment/interviews/edit/' . $int->id; ?>"
                                   style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:#16a34a; text-decoration:none; white-space:nowrap; padding:5px 10px; border:1.5px solid #bbf7d0; border-radius:6px; background:#f0fdf4; flex-shrink:0;">
                                    <span class="material-symbols-outlined" style="font-size:13px;">open_in_new</span> View
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div><!-- /tab-interviews -->
                <?php endif; ?>

                <!-- Save button -->
                <div style="display:flex; gap:10px; justify-content:flex-start; margin-top:4px; margin-bottom:24px;">
                    <button type="submit" class="btn btn-success"
                        style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 24px; border-radius:6px;">
                        <i class="fa fa-save"></i> <?php echo $is_edit ? 'Update Applicant' : 'Save Applicant'; ?>
                    </button>
                    <a href="<?php echo $base . '/recruitment/applicants'; ?>" class="btn btn-default"
                        style="border-radius:6px; padding:8px 20px;">
                        Cancel
                    </a>
                </div>

            </div><!-- /col-md-9 -->

            <!-- ── Right sidebar col-md-3 ──────────────────────────────────────── -->
            <div class="col-md-3">

                <!-- Document Summary -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Document Summary</span>
                    </div>
                    <div style="padding:14px 16px; display:flex; flex-direction:column; gap:12px;">

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Stage</div>
                            <div id="sidebar-stage-badge">
                                <span id="sidebar-stage-text" style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                                    <?php echo htmlspecialchars($cur_stage); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($is_edit && $applicant->applicant_number): ?>
                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Applicant #</div>
                            <div style="font-size:13px; font-weight:600; color:#111827; font-family:monospace;"><?php echo htmlspecialchars($applicant->applicant_number); ?></div>
                        </div>
                        <?php endif; ?>

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Job Opening</div>
                            <div id="sidebar-opening" style="font-size:13px; color:#374151;">
                                <?php
                                if ($is_edit && $applicant->job_opening_id) {
                                    foreach ($openings as $o) {
                                        if ($o->id == $applicant->job_opening_id) { echo htmlspecialchars($o->title); break; }
                                    }
                                } else { echo '<span style="color:#9ca3af;">Not set</span>'; }
                                ?>
                            </div>
                        </div>

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Source</div>
                            <div id="sidebar-source" style="font-size:13px; color:#374151;">
                                <?php echo ($is_edit && $applicant->source) ? htmlspecialchars($applicant->source) : '<span style="color:#9ca3af;">—</span>'; ?>
                            </div>
                        </div>

                        <?php if ($is_edit): ?>
                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Applied On</div>
                            <div style="font-size:13px; color:#374151;"><?php echo _d($applicant->date_created); ?></div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Quick Actions (only in edit mode) -->
                <?php if ($is_edit): ?>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                    </div>
                    <div style="padding:12px 16px; display:flex; flex-direction:column; gap:8px;">
                        <a href="<?php echo $base . '/recruitment/interviews/add?applicant_id=' . $applicant->id; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f9fafb; border-radius:6px; text-decoration:none; color:#374151; font-size:13px; font-weight:500; border:1px solid #e5e7eb;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">event</span>
                            Schedule Interview
                        </a>
                        <a href="<?php echo $base . '/recruitment/offers/add'; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f9fafb; border-radius:6px; text-decoration:none; color:#374151; font-size:13px; font-weight:500; border:1px solid #e5e7eb;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">description</span>
                            Create Job Offer
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recruitment shortcuts -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recruitment</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php
                        $rec_links = [
                            ['label' => 'Job Requisition',    'icon' => 'article',         'url' => $base . '/setup/job_requisition'],
                            ['label' => 'Job Opening',        'icon' => 'work_outline',     'url' => $base . '/recruitment/job_openings'],
                            ['label' => 'Job Applicant',      'icon' => 'person_search',    'url' => $base . '/recruitment/applicants'],
                            ['label' => 'Interview',          'icon' => 'record_voice_over','url' => $base . '/recruitment/interviews'],
                            ['label' => 'Job Offer',          'icon' => 'handshake',        'url' => $base . '/recruitment/offers'],
                            ['label' => 'Appointment Letter', 'icon' => 'mail',             'url' => $base . '/recruitment/appointment_letters'],
                        ];
                        foreach ($rec_links as $rl):
                            $active = ($rl['label'] === 'Job Applicant');
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

        </div><!-- /.row -->
    </form>

</div><!-- /.xhr-setup-page -->

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script>
// Tab switching
document.querySelectorAll('.apptab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.apptab-btn').forEach(function(b) {
            b.style.color = '#6b7280';
            b.style.borderBottomColor = 'transparent';
            b.classList.remove('active');
        });
        document.querySelectorAll('.apptab-pane').forEach(function(p) {
            p.style.display = 'none';
        });
        this.style.color = '#16a34a';
        this.style.borderBottomColor = '#16a34a';
        this.classList.add('active');
        var target = document.getElementById(this.getAttribute('data-tab'));
        if (target) target.style.display = 'block';
    });
});

// Show/hide Source Name field
function toggleSourceName(val) {
    var wrap = document.getElementById('source-name-wrap');
    if (wrap) wrap.style.display = (val === 'Employee Referral') ? 'block' : 'none';
}

// Live sidebar updates
document.querySelector('select[name="stage"]').addEventListener('change', function() {
    var stageBadge = document.getElementById('sidebar-stage-text');
    if (stageBadge) stageBadge.textContent = this.value;
});

document.querySelector('select[name="job_opening_id"]').addEventListener('change', function() {
    var el = document.getElementById('sidebar-opening');
    if (el) el.textContent = this.options[this.selectedIndex].text || '—';
});

document.querySelector('select[name="source"]').addEventListener('change', function() {
    var el = document.getElementById('sidebar-source');
    if (el) el.textContent = this.value || '—';
});

// Init TinyMCE on the cover letter tab textarea
$(document).ready(function() {
    init_editor('#cover-letter-editor');
});
</script>
