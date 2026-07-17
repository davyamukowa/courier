<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active           = 'recruitment';
$base                 = admin_url('xetuu_hr');
$is_edit              = !empty($interview);
$applicant_map_json   = isset($applicant_map_json)   ? $applicant_map_json   : '{}';
$prefill_applicant_id = isset($prefill_applicant_id) ? (int)$prefill_applicant_id : 0;
$cur_status = $is_edit ? $interview->status : 'Scheduled';
$cur_result = $is_edit ? $interview->result : 'Pending';

$status_colors = [
    'Scheduled' => ['bg'=>'#fef9c3','color'=>'#854d0e'],
    'Completed' => ['bg'=>'#dcfce7','color'=>'#16a34a'],
    'Cancelled' => ['bg'=>'#fee2e2','color'=>'#dc2626'],
];
$sc = $status_colors[$cur_status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];

$result_colors = [
    'Pass'    => ['bg'=>'#dcfce7','color'=>'#16a34a'],
    'Fail'    => ['bg'=>'#fee2e2','color'=>'#dc2626'],
    'Pending' => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
];
$rc = $result_colors[$cur_result] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];

// Star rating helper
$rating_val = $is_edit && $interview->rating ? (int)$interview->rating : 0;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Breadcrumb + header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:6px; flex-wrap:wrap;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/recruitment/interviews'; ?>" style="color:#6b7280; text-decoration:none;">Interview</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $is_edit ? htmlspecialchars($interview->interview_number ?? 'Edit Interview') : 'New Interview'; ?></span>
                <?php if ($is_edit): ?>
                <span style="display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; background:#dcfce7; color:#16a34a; margin-left:4px;">Saved</span>
                <?php else: ?>
                <span style="display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; background:#fef9c3; color:#854d0e; margin-left:4px;">Not Saved</span>
                <?php endif; ?>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">
                    <?php echo $is_edit ? htmlspecialchars($interview->interview_number ?? 'Interview') : 'New Interview'; ?>
                </h1>
                <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                    <?php echo htmlspecialchars($cur_status); ?>
                </span>
                <?php if ($cur_result !== 'Pending'): ?>
                <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:<?php echo $rc['bg']; ?>; color:<?php echo $rc['color']; ?>;">
                    <?php echo htmlspecialchars($cur_result); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Recruitment Pipeline Stepper ──────────────────────────────── -->
    <?php
    $pipeline_stages = ['Applied', 'Screening', 'Interview', 'Offer', 'Hired'];
    $applicant_stage = 'Interview'; // default for interview context
    if ($is_edit && $interview->applicant_id) {
        // we'll update this via JS once APPLICANT_MAP is loaded
    }
    $step_colors = [
        'done'    => ['bg' => '#16a34a', 'border' => '#15803d', 'text' => '#fff', 'line' => '#16a34a'],
        'current' => ['bg' => '#16a34a', 'border' => '#15803d', 'text' => '#fff', 'line' => '#e5e7eb'],
        'pending' => ['bg' => '#fff',    'border' => '#e5e7eb', 'text' => '#9ca3af','line' => '#e5e7eb'],
    ];
    ?>
    <div id="int-stepper" style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 22px; margin-bottom:22px; overflow-x:auto;">
        <div style="display:flex; align-items:center; min-width:420px;">
            <?php
            $cur_idx = array_search('Interview', $pipeline_stages);
            foreach ($pipeline_stages as $si => $stage):
                $is_done    = $si < $cur_idx;
                $is_current = $si === $cur_idx;
                $state      = $is_done ? 'done' : ($is_current ? 'current' : 'pending');
                $c          = $step_colors[$state];
                $is_last    = $si === count($pipeline_stages) - 1;
            ?>
            <div style="display:flex; align-items:center; <?php echo !$is_last ? 'flex:1;' : ''; ?>">
                <div style="display:flex; flex-direction:column; align-items:center; gap:5px;">
                    <div style="width:32px; height:32px; border-radius:50%; background:<?php echo $c['bg']; ?>; border:2px solid <?php echo $c['border']; ?>; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:<?php echo $c['text']; ?>; flex-shrink:0;">
                        <?php if ($is_done): ?>
                        <span class="material-symbols-outlined" style="font-size:16px;">check</span>
                        <?php else: ?>
                        <?php echo $si + 1; ?>
                        <?php endif; ?>
                    </div>
                    <span id="step-lbl-<?php echo $si; ?>" style="font-size:10.5px; font-weight:<?php echo $is_current ? '700' : '500'; ?>; color:<?php echo $is_current ? '#111827' : '#9ca3af'; ?>; white-space:nowrap;"><?php echo $stage; ?></span>
                </div>
                <?php if (!$is_last): ?>
                <div style="flex:1; height:2px; background:<?php echo $c['line']; ?>; margin:0 6px; margin-bottom:18px;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <form action="<?php echo $base . '/recruitment/interviews'; ?>" method="post">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo $interview->id; ?>">
        <?php endif; ?>

        <div class="row">

            <!-- ── col-md-9 ──────────────────────────────────────────────── -->
            <div class="col-md-9">

                <!-- Tab nav -->
                <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                    <button type="button" class="int-tab active" data-tab="tab-details"
                        style="background:none; border:none; padding:10px 20px; font-size:13px; font-weight:600; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px; cursor:pointer;">
                        Details
                    </button>
                    <button type="button" class="int-tab" data-tab="tab-feedback"
                        style="background:none; border:none; padding:10px 20px; font-size:13px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer;">
                        Feedback
                    </button>
                </div>

                <!-- Tab: Details -->
                <div id="tab-details" class="int-pane">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div class="panel-body" style="padding:20px;">

                            <!-- Row 1: Interview Round | Status -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Interview Round <span style="color:#ef4444;">*</span></label>
                                    <select name="interview_round_id" class="form-control" required style="border-radius:6px;">
                                        <option value="">— Select Round —</option>
                                        <?php foreach ($interview_rounds as $r): 
                                            $selected_round = ($is_edit && $interview->interview_round_id == $r->id) || (!$is_edit && !empty($prefill_next_round_id) && $prefill_next_round_id == $r->id);
                                        ?>
                                        <option value="<?php echo $r->id; ?>"<?php echo $selected_round ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($r->name); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Status <span style="color:#ef4444;">*</span></label>
                                    <select name="status" id="int-status" class="form-control" required style="border-radius:6px;">
                                        <?php foreach (['Scheduled','Completed','Cancelled'] as $s): ?>
                                        <option value="<?php echo $s; ?>"<?php echo ($cur_status === $s) ? ' selected' : ''; ?>><?php echo $s; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Job Applicant | Scheduled On -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Job Applicant <span style="color:#ef4444;">*</span></label>
                                    <select name="applicant_id" class="form-control" required style="border-radius:6px;">
                                        <option value="">— Select Applicant —</option>
                                        <?php foreach ($applicants as $a): ?>
                                        <option value="<?php echo $a->id; ?>"<?php echo ($is_edit && $interview->applicant_id == $a->id) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($a->first_name . ' ' . $a->last_name); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Scheduled On <span style="color:#ef4444;">*</span></label>
                                    <input type="date" name="interview_date" class="form-control" required style="border-radius:6px;"
                                        value="<?php echo $is_edit && $interview->interview_date ? date('Y-m-d', strtotime($interview->interview_date)) : date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <!-- Row 3: Job Opening | From Time -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Job Opening</label>
                                    <select name="job_opening_id" class="form-control" style="border-radius:6px;">
                                        <option value="">— Select Opening —</option>
                                        <?php foreach ($openings as $o): ?>
                                        <option value="<?php echo $o->id; ?>"<?php echo ($is_edit && $interview->job_opening_id == $o->id) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($o->title); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">From Time <span style="color:#ef4444;">*</span></label>
                                    <input type="time" name="from_time" class="form-control" required style="border-radius:6px;"
                                        value="<?php echo ($is_edit && $interview->from_time) ? $interview->from_time : '09:00'; ?>">
                                </div>
                            </div>

                            <!-- Row 4: Interview Type | To Time -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Interview Type</label>
                                    <select name="interview_type_id" class="form-control" style="border-radius:6px;">
                                        <option value="">— Select Type —</option>
                                        <?php foreach ($interview_types as $t): ?>
                                        <option value="<?php echo $t->id; ?>"<?php echo ($is_edit && $interview->interview_type_id == $t->id) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($t->name); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">To Time <span style="color:#ef4444;">*</span></label>
                                    <input type="time" name="to_time" class="form-control" required style="border-radius:6px;"
                                        value="<?php echo ($is_edit && $interview->to_time) ? $interview->to_time : '10:00'; ?>">
                                </div>
                            </div>

                            <!-- Row 5: Interviewer | Designation -->
                            <div class="row" style="margin-bottom:16px;">
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Interviewer (Staff)</label>
                                    <select name="interviewer_id" class="form-control" style="border-radius:6px;">
                                        <option value="">— Select Interviewer —</option>
                                        <?php foreach ($staff_list as $s): ?>
                                        <option value="<?php echo $s['staffid']; ?>"<?php echo ($is_edit && $interview->interviewer_id == $s['staffid']) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Designation</label>
                                    <select name="designation_id" class="form-control" style="border-radius:6px;">
                                        <option value="">— Select Designation —</option>
                                        <?php foreach ($designations as $d): ?>
                                        <option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Resume Link (full width) -->
                            <div style="margin-bottom:16px;">
                                <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block;">Resume Link</label>
                                <input type="url" name="resume_link" class="form-control" placeholder="https://..." style="border-radius:6px;"
                                    value="<?php echo $is_edit ? htmlspecialchars($interview->resume_link ?? '') : ''; ?>">
                            </div>

                            <!-- Send Email checkbox -->
                            <div style="display:flex; align-items:center; gap:8px; margin-top:16px;">
                                <input type="checkbox" name="send_email_notification" id="send_email_notification" value="1" <?php echo !$is_edit ? 'checked' : ''; ?> style="width:16px; height:16px; cursor:pointer;">
                                <label for="send_email_notification" style="font-size:13px; font-weight:600; color:#374151; cursor:pointer; margin:0;">Send interview invitation email to candidate</label>
                            </div>

                        </div>
                    </div>
                </div><!-- /tab-details -->

                <!-- Tab: Feedback -->
                <div id="tab-feedback" class="int-pane" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                        <div class="panel-body" style="padding:20px;">

                            <!-- Star rating -->
                            <div style="margin-bottom:24px;">
                                <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:12px; display:block;">Overall Rating</label>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div id="star-display" style="display:flex; gap:4px; font-size:28px; cursor:pointer;">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <span class="star-btn" data-val="<?php echo $s; ?>"
                                              style="color:<?php echo $s <= $rating_val ? '#f59e0b' : '#d1d5db'; ?>; cursor:pointer; transition:color 0.1s;">&#9733;</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span id="star-label" style="font-size:14px; color:#6b7280;"><?php echo $rating_val > 0 ? $rating_val . ' / 5' : 'Not rated'; ?></span>
                                </div>
                                <input type="hidden" name="rating" id="rating-input" value="<?php echo $rating_val; ?>">
                            </div>

                            <!-- Result -->
                            <div style="margin-bottom:20px;">
                                <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:8px; display:block;">Assessment Result</label>
                                <div style="display:flex; gap:10px;">
                                    <?php foreach (['Pass','Fail','Pending'] as $res):
                                        $rc2 = $result_colors[$res] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                                        $selected = ($cur_result === $res);
                                    ?>
                                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; padding:8px 16px; border-radius:6px; border:2px solid <?php echo $selected ? $rc2['color'] : '#e5e7eb'; ?>; background:<?php echo $selected ? $rc2['bg'] : '#fff'; ?>; font-size:13px; font-weight:600; color:<?php echo $selected ? $rc2['color'] : '#6b7280'; ?>;" class="result-btn">
                                        <input type="radio" name="result" value="<?php echo $res; ?>" <?php echo $selected ? 'checked' : ''; ?> style="display:none;">
                                        <?php echo $res; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Workflow Action Container (Dynamic based on selected result) -->
                            <div id="workflow-action-container" style="margin-top:20px; display:none; background:#f9fafb; border:1px dashed #e5e7eb; border-radius:8px; padding:16px;">
                                <div style="font-size:11px; font-weight:700; color:#4b5563; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.04em;">Progression Workflow</div>
                                <div id="workflow-pass-actions" style="display:none; gap:10px; flex-wrap:wrap;">
                                    <button type="submit" name="action_workflow" value="pass_next_round" class="btn btn-success" style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span> Pass & Schedule Next Round
                                    </button>
                                    <button type="submit" name="action_workflow" value="pass_offer" class="btn btn-info" style="background-color:#0284c7; border-color:#0284c7; font-weight:600; padding:8px 16px; border-radius:6px; color:#fff; display:inline-flex; align-items:center; gap:6px;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">handshake</span> Pass & Move to Offer
                                    </button>
                                </div>
                                <div id="workflow-fail-actions" style="display:none;">
                                    <button type="submit" name="action_workflow" value="fail_reject" class="btn btn-danger" style="background-color:#dc2626; border-color:#dc2626; font-weight:600; padding:8px 16px; border-radius:6px; color:#fff; display:inline-flex; align-items:center; gap:6px;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">block</span> Fail & Reject Candidate
                                    </button>
                                </div>
                            </div>

                            <!-- Comments -->
                            <div>
                                <label style="font-size:12px; font-weight:600; color:#374151; margin-bottom:8px; display:block;">Interview Comments / Feedback</label>
                                <textarea name="comments" id="int-comments" class="tinymce"><?php echo $is_edit ? htmlspecialchars($interview->comments ?? '') : ''; ?></textarea>
                            </div>

                        </div>
                    </div>
                </div><!-- /tab-feedback -->

                <!-- Save buttons -->
                <div style="display:flex; gap:10px; margin-bottom:24px;">
                    <button type="submit" class="btn btn-success"
                        style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 24px; border-radius:6px;">
                        <i class="fa fa-save"></i> <?php echo $is_edit ? 'Update Interview' : 'Save Interview'; ?>
                    </button>
                    <?php if ($is_edit): ?>
                    <a href="<?php echo $base . '/recruitment/interviews/send_email/' . $interview->id; ?>" class="btn btn-info"
                        style="background-color:#0284c7; border-color:#0284c7; font-weight:600; padding:8px 24px; border-radius:6px; color:#fff;">
                        <i class="fa fa-envelope"></i> Send Email Invitation
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo $base . '/recruitment/interviews'; ?>" class="btn btn-default" style="border-radius:6px; padding:8px 20px;">Cancel</a>
                </div>

            </div><!-- /col-md-9 -->

            <!-- ── Sidebar col-md-3 ───────────────────────────────────────── -->
            <div class="col-md-3">

                <!-- Document Summary -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Document Summary</span>
                    </div>
                    <div style="padding:14px 16px; display:flex; flex-direction:column; gap:12px;">

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Status</div>
                            <span id="sb-status" style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                                <?php echo htmlspecialchars($cur_status); ?>
                            </span>
                        </div>

                        <?php if ($is_edit && $interview->interview_number): ?>
                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Interview #</div>
                            <div style="font-size:13px; font-weight:600; color:#111827; font-family:monospace;"><?php echo htmlspecialchars($interview->interview_number); ?></div>
                        </div>
                        <?php endif; ?>

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Scheduled On</div>
                            <div id="sb-date" style="font-size:13px; color:#374151;">
                                <?php echo ($is_edit && $interview->interview_date) ? _d($interview->interview_date) : '<span style="color:#9ca3af;">Not set</span>'; ?>
                            </div>
                        </div>

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Time</div>
                            <div style="font-size:13px; color:#374151;">
                                <?php
                                if ($is_edit && $interview->from_time) {
                                    echo date('H:i', strtotime($interview->from_time));
                                    if ($interview->to_time) echo ' – ' . date('H:i', strtotime($interview->to_time));
                                } else { echo '<span style="color:#9ca3af;">—</span>'; }
                                ?>
                            </div>
                        </div>

                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Result</div>
                            <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $rc['bg']; ?>; color:<?php echo $rc['color']; ?>;">
                                <?php echo htmlspecialchars($cur_result); ?>
                            </span>
                        </div>

                        <?php if ($is_edit && $interview->resume_link): ?>
                        <div>
                            <div style="font-size:11px; color:#9ca3af; margin-bottom:3px;">Resume</div>
                            <a href="<?php echo htmlspecialchars($interview->resume_link); ?>" target="_blank" style="font-size:12px; color:#16a34a; text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:13px; vertical-align:middle;">open_in_new</span> View Resume
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Quick Actions (edit mode) -->
                <?php if ($is_edit): ?>
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                    </div>
                    <div style="padding:12px 16px; display:flex; flex-direction:column; gap:8px;">
                        <a href="<?php echo $base . '/recruitment/offers/add'; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f9fafb; border-radius:6px; text-decoration:none; color:#374151; font-size:13px; font-weight:500; border:1px solid #e5e7eb;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">handshake</span>
                            Create Job Offer
                        </a>
                        <a href="<?php echo $base . '/recruitment/applicants'; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f9fafb; border-radius:6px; text-decoration:none; color:#374151; font-size:13px; font-weight:500; border:1px solid #e5e7eb;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">person_search</span>
                            View All Applicants
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
                            ['label'=>'Job Requisition',    'icon'=>'article',          'url'=>$base.'/setup/job_requisition'],
                            ['label'=>'Job Opening',        'icon'=>'work_outline',     'url'=>$base.'/recruitment/job_openings'],
                            ['label'=>'Job Applicant',      'icon'=>'person_search',    'url'=>$base.'/recruitment/applicants'],
                            ['label'=>'Interview',          'icon'=>'record_voice_over','url'=>$base.'/recruitment/interviews'],
                            ['label'=>'Job Offer',          'icon'=>'handshake',        'url'=>$base.'/recruitment/offers'],
                            ['label'=>'Appointment Letter', 'icon'=>'mail',             'url'=>$base.'/recruitment/appointment_letters'],
                        ];
                        foreach ($rec_links as $rl):
                            $active = ($rl['label'] === 'Interview');
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

</div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script>
// Tab switching
document.querySelectorAll('.int-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.int-tab').forEach(function(b) {
            b.style.color = '#6b7280';
            b.style.borderBottomColor = 'transparent';
        });
        document.querySelectorAll('.int-pane').forEach(function(p) {
            p.style.display = 'none';
        });
        this.style.color = '#16a34a';
        this.style.borderBottomColor = '#16a34a';
        document.getElementById(this.getAttribute('data-tab')).style.display = 'block';

        // Init TinyMCE when feedback tab is first opened
        if (this.getAttribute('data-tab') === 'tab-feedback' && typeof init_editor === 'function') {
            if (!document.querySelector('#int-comments.mce-content-body') && !tinymce.get('int-comments')) {
                init_editor('#int-comments');
            }
        }
    });
});

// Star rating
var stars = document.querySelectorAll('.star-btn');
var ratingInput = document.getElementById('rating-input');
var starLabel = document.getElementById('star-label');

stars.forEach(function(star) {
    star.addEventListener('mouseover', function() {
        var v = parseInt(this.getAttribute('data-val'));
        stars.forEach(function(s) {
            s.style.color = parseInt(s.getAttribute('data-val')) <= v ? '#f59e0b' : '#d1d5db';
        });
    });
    star.addEventListener('mouseout', function() {
        var current = parseInt(ratingInput.value) || 0;
        stars.forEach(function(s) {
            s.style.color = parseInt(s.getAttribute('data-val')) <= current ? '#f59e0b' : '#d1d5db';
        });
    });
    star.addEventListener('click', function() {
        var v = parseInt(this.getAttribute('data-val'));
        ratingInput.value = v;
        starLabel.textContent = v + ' / 5';
        stars.forEach(function(s) {
            s.style.color = parseInt(s.getAttribute('data-val')) <= v ? '#f59e0b' : '#d1d5db';
        });
    });
});

// Result radio buttons styled
document.querySelectorAll('.result-btn').forEach(function(lbl) {
    lbl.addEventListener('click', function() {
        var resultColors = {
            'Pass':    {bg:'#f0fdf4', border:'#16a34a', color:'#16a34a'},
            'Fail':    {bg:'#fef2f2', border:'#dc2626', color:'#dc2626'},
            'Pending': {bg:'#f3f4f6', border:'#6b7280', color:'#6b7280'}
        };
        document.querySelectorAll('.result-btn').forEach(function(b) {
            b.style.background = '#fff';
            b.style.borderColor = '#e5e7eb';
            b.style.color = '#6b7280';
        });
        var val = this.querySelector('input[type="radio"]').value;
        var c = resultColors[val] || {bg:'#f3f4f6', border:'#6b7280', color:'#6b7280'};
        this.style.background = c.bg;
        this.style.borderColor = c.border;
        this.style.color = c.color;

        updateWorkflowButtons(val);
    });
});

function updateWorkflowButtons(resultVal) {
    var container = document.getElementById('workflow-action-container');
    var passActions = document.getElementById('workflow-pass-actions');
    var failActions = document.getElementById('workflow-fail-actions');
    var statusSelect = document.getElementById('int-status');

    if (!container) return;

    if (resultVal === 'Pass') {
        container.style.display = 'block';
        passActions.style.display = 'flex';
        failActions.style.display = 'none';
        if (statusSelect) {
            statusSelect.value = 'Completed';
            statusSelect.dispatchEvent(new Event('change'));
        }
    } else if (resultVal === 'Fail') {
        container.style.display = 'block';
        passActions.style.display = 'none';
        failActions.style.display = 'block';
        if (statusSelect) {
            statusSelect.value = 'Completed';
            statusSelect.dispatchEvent(new Event('change'));
        }
    } else {
        container.style.display = 'none';
        passActions.style.display = 'none';
        failActions.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var activeResult = document.querySelector('input[name="result"]:checked');
    if (activeResult) {
        updateWorkflowButtons(activeResult.value);
    }
});

// Live sidebar status update
document.querySelector('select[name="status"]').addEventListener('change', function() {
    var el = document.getElementById('sb-status');
    if (el) el.textContent = this.value;
});

// ── Applicant auto-fill ─────────────────────────────────────────────
var APPLICANT_MAP = <?php echo $applicant_map_json ?? '{}'; ?>;
var PREFILL_ID    = <?php echo (int)($prefill_applicant_id ?? 0); ?>;

function applyApplicantFill(applicantId) {
    var data = APPLICANT_MAP[applicantId];
    if (!data) return;
    // Auto-select job opening
    if (data.job_opening_id) {
        var joSel = document.querySelector('select[name="job_opening_id"]');
        if (joSel) joSel.value = data.job_opening_id;
    }
}

var applicantSel = document.querySelector('select[name="applicant_id"]');
if (applicantSel) {
    applicantSel.addEventListener('change', function() {
        applyApplicantFill(parseInt(this.value) || 0);
    });
    // Pre-fill from URL param (new interview from applicant page)
    <?php if (!$is_edit && !empty($prefill_applicant_id)): ?>
    applicantSel.value = <?php echo (int)$prefill_applicant_id; ?>;
    applyApplicantFill(<?php echo (int)$prefill_applicant_id; ?>);
    <?php endif; ?>
}
</script>
