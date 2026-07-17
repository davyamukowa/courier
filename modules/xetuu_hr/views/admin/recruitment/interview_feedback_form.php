<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'recruitment';
$base        = admin_url('xetuu_hr');
$is_edit     = !empty($feedback->id);
$form_action = $is_edit
    ? $base . '/recruitment/interview_feedback/edit/' . $feedback->id
    : $base . '/recruitment/interview_feedback/add';

$status_val = $feedback->status ?? 'Draft';
$result_val = $feedback->result ?? 'To Be Discussed';
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Breadcrumb -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/recruitment/interview_feedback'; ?>" style="color:#6b7280; text-decoration:none;">Interview Feedback</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;"><?php echo $is_edit ? htmlspecialchars($feedback->feedback_number ?? 'Edit') : 'New Feedback'; ?></span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;">
                    <?php echo $is_edit ? htmlspecialchars($feedback->feedback_number ?? 'Interview Feedback') : 'New Interview Feedback'; ?>
                </h1>
                <?php if ($is_edit): ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#dcfce7; color:#16a34a;">Saved</span>
                <?php else: ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fef9c3; color:#854d0e;">Not Saved</span>
                <?php endif; ?>
                <!-- Status pill -->
                <?php
                $sp = ['Draft'=>['#f3f4f6','#6b7280'], 'Submitted'=>['#dcfce7','#16a34a']];
                $sc = $sp[$status_val] ?? ['#f3f4f6','#6b7280'];
                ?>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;">
                    <?php echo htmlspecialchars($status_val); ?>
                </span>
            </div>
        </div>
    </div>

    <form action="<?php echo $form_action; ?>" method="post" id="feedback-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div class="row">
            <!-- Main content: col-md-9 -->
            <div class="col-md-9">

                <!-- Tabs -->
                <div style="display:flex; gap:0; border-bottom:2px solid #e5e7eb; margin-bottom:20px;">
                    <button type="button" class="fb-tab active-tab" data-tab="tab-details"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#16a34a; border-bottom:2px solid #16a34a; margin-bottom:-2px;">
                        Details
                    </button>
                    <button type="button" class="fb-tab" data-tab="tab-skills"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Skill Assessment
                    </button>
                    <button type="button" class="fb-tab" data-tab="tab-feedback"
                            style="padding:10px 20px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px;">
                        Feedback
                    </button>
                </div>

                <!-- Tab: Details -->
                <div id="tab-details" class="fb-tab-content" style="display:block;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Interview <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="interview_id" id="interview_id" class="form-control" required onchange="fetchInterviewDetails(this.value)">
                                            <option value="">-- Select Interview --</option>
                                            <?php foreach ($interviews as $iv): ?>
                                            <option value="<?php echo $iv->id; ?>"
                                                <?php echo (isset($feedback->interview_id) && $feedback->interview_id == $iv->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($iv->interview_number ?? 'INT-'.$iv->id); ?>
                                                <?php if (!empty($iv->applicant_name)): ?>— <?php echo htmlspecialchars($iv->applicant_name); ?><?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Interviewer <span style="color:#dc2626;">*</span>
                                        </label>
                                        <select name="interviewer_id" id="interviewer_id" class="form-control" required>
                                            <option value="">-- Select Interviewer --</option>
                                            <?php foreach ($staff_list as $s): ?>
                                            <option value="<?php echo $s['staffid']; ?>"
                                                <?php echo (isset($feedback->interviewer_id) && $feedback->interviewer_id == $s['staffid']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Interview Round</label>
                                        <input type="text" id="round_name_display" class="form-control" placeholder="Auto-filled from interview" readonly
                                               value="<?php echo htmlspecialchars($feedback->round_name ?? ''); ?>"
                                               style="background:#f9fafb; color:#6b7280;">
                                        <input type="hidden" name="round_id" id="round_id_hidden" value="<?php echo $feedback->round_id ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">
                                            Result <span style="color:#dc2626;">*</span>
                                        </label>
                                        <div style="display:flex; gap:10px; margin-top:6px; flex-wrap:wrap;">
                                            <?php
                                            $results = ['Cleared', 'Not Cleared', 'To Be Discussed'];
                                            $result_styles = [
                                                'Cleared'         => ['#dcfce7','#16a34a','#16a34a'],
                                                'Not Cleared'     => ['#fee2e2','#dc2626','#dc2626'],
                                                'To Be Discussed' => ['#fef9c3','#854d0e','#854d0e'],
                                            ];
                                            foreach ($results as $r):
                                                $rs = $result_styles[$r];
                                                $sel = ($result_val === $r);
                                            ?>
                                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; padding:6px 12px; border-radius:6px; border:2px solid <?php echo $sel ? $rs[2] : '#e5e7eb'; ?>; background:<?php echo $sel ? $rs[0] : '#fff'; ?>; font-size:12px; font-weight:600; color:<?php echo $sel ? $rs[1] : '#6b7280'; ?>; transition:all 0.15s;" class="result-radio-label">
                                                <input type="radio" name="result" value="<?php echo $r; ?>" <?php echo $sel ? 'checked' : ''; ?> style="display:none;" onchange="updateResultUI()">
                                                <?php echo $r; ?>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Job Applicant</label>
                                        <input type="text" id="applicant_name_display" class="form-control" placeholder="Auto-filled from interview" readonly
                                               value="<?php echo htmlspecialchars($feedback->applicant_name ?? ''); ?>"
                                               style="background:#f9fafb; color:#6b7280;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="Draft" <?php echo $status_val === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="Submitted" <?php echo $status_val === 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tab: Skill Assessment -->
                <div id="tab-skills" class="fb-tab-content" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                                <div>
                                    <h4 style="font-size:14px; font-weight:700; color:#111827; margin:0;">Skill Assessment</h4>
                                    <p style="font-size:12px; color:#6b7280; margin:4px 0 0 0;">Rate each skill from 1–5 stars. Skills auto-populate from the selected interview round.</p>
                                </div>
                                <button type="button" onclick="addSkillRow()" class="btn btn-default" style="font-size:12px; border-radius:6px; padding:6px 12px;">
                                    <i class="fa fa-plus"></i> Add Skill
                                </button>
                            </div>

                            <table class="table" id="skills-table" style="margin-bottom:0;">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563; width:40px;">No.</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563;">Skill</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563; text-align:center;">Rating (1–5)</th>
                                        <th style="padding:10px 12px; font-size:11px; font-weight:600; color:#4b5563; width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="skills-tbody">
                                    <?php if (!empty($feedback_skills)): ?>
                                    <?php foreach ($feedback_skills as $si => $sk): ?>
                                    <tr class="skill-row" data-idx="<?php echo $si; ?>">
                                        <td style="padding:10px 12px; color:#6b7280; font-size:13px; vertical-align:middle;"><?php echo $si + 1; ?></td>
                                        <td style="padding:10px 12px; vertical-align:middle;">
                                            <input type="text" name="skills[<?php echo $si; ?>][skill_name]" class="form-control" placeholder="Skill name"
                                                   value="<?php echo htmlspecialchars($sk->skill_name); ?>" style="border-radius:4px;">
                                        </td>
                                        <td style="padding:10px 12px; text-align:center; vertical-align:middle;">
                                            <div class="star-widget" data-idx="<?php echo $si; ?>" data-rating="<?php echo $sk->rating; ?>">
                                                <?php for ($r = 1; $r <= 5; $r++): ?>
                                                <span class="star" data-val="<?php echo $r; ?>"
                                                      style="font-size:22px; cursor:pointer; color:<?php echo $r <= $sk->rating ? '#f59e0b' : '#d1d5db'; ?>; transition:color 0.1s;"
                                                      onclick="setRating(this, <?php echo $si; ?>, <?php echo $r; ?>)"
                                                      onmouseover="hoverRating(this, <?php echo $si; ?>, <?php echo $r; ?>)"
                                                      onmouseout="resetRating(this, <?php echo $si; ?>)">&#9733;</span>
                                                <?php endfor; ?>
                                                <input type="hidden" name="skills[<?php echo $si; ?>][rating]" value="<?php echo $sk->rating; ?>">
                                            </div>
                                        </td>
                                        <td style="padding:10px 12px; vertical-align:middle;">
                                            <button type="button" onclick="removeSkillRow(this)" class="btn btn-danger btn-icon"><i class="fa fa-remove"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr id="no-skills-row">
                                        <td colspan="4" class="text-center" style="padding:30px; color:#9ca3af; font-size:13px;">
                                            Select an interview to auto-populate skills, or <a href="javascript:void(0)" onclick="addSkillRow()" style="color:#16a34a;">add manually</a>.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Feedback -->
                <div id="tab-feedback" class="fb-tab-content" style="display:none;">
                    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                        <div class="panel-body" style="padding:24px;">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Feedback / Notes</label>
                                <textarea name="feedback" id="feedback-editor" class="tinymce" rows="15"
                                          style="width:100%;"><?php echo set_value('feedback', $feedback->feedback ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save bar -->
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                    <a href="<?php echo $base . '/recruitment/interview_feedback'; ?>" class="btn btn-default" style="border-radius:6px; padding:8px 20px;">Cancel</a>
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:8px 24px; font-weight:600;">
                        <i class="fa fa-save"></i> Save Feedback
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
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Status</span>
                                <?php
                                $sp2 = ['Draft'=>['#f3f4f6','#6b7280'], 'Submitted'=>['#dcfce7','#16a34a']];
                                $sc2 = $sp2[$status_val] ?? ['#f3f4f6','#6b7280'];
                                ?>
                                <span style="padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc2[0]; ?>; color:<?php echo $sc2[1]; ?>;">
                                    <?php echo htmlspecialchars($status_val); ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Feedback #</span>
                                <span style="font-weight:600; font-family:monospace; color:#111827; font-size:11px;">
                                    <?php echo $is_edit ? htmlspecialchars($feedback->feedback_number ?? '—') : 'Auto-assigned'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Interview</span>
                                <span style="font-weight:600; color:#374151; text-align:right; max-width:110px;">
                                    <?php echo $is_edit ? htmlspecialchars($feedback->interview_number ?? '—') : '—'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Applicant</span>
                                <span style="font-weight:600; color:#374151; text-align:right; max-width:110px;">
                                    <?php echo $is_edit ? htmlspecialchars($feedback->applicant_name ?? '—') : '—'; ?>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:#6b7280;">Result</span>
                                <?php
                                $rcs = [
                                    'Cleared'         => ['#dcfce7','#16a34a'],
                                    'Not Cleared'     => ['#fee2e2','#dc2626'],
                                    'To Be Discussed' => ['#fef9c3','#854d0e'],
                                ];
                                $rcc = $rcs[$result_val] ?? ['#f3f4f6','#6b7280'];
                                ?>
                                <span style="padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $rcc[0]; ?>; color:<?php echo $rcc[1]; ?>;">
                                    <?php echo htmlspecialchars($result_val); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                <!-- Quick Actions -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php if (!empty($feedback->interview_id)): ?>
                        <a href="<?php echo $base . '/recruitment/interviews/edit/' . $feedback->interview_id; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#4b5563; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;">record_voice_over</span>
                            View Interview
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo $base . '/recruitment/interview_feedback/add'; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:9px 16px; text-decoration:none; color:#16a34a; font-weight:600; font-size:13px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a;">add_circle</span>
                            Add New Feedback
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
                            $active = ($rl['label'] === 'Interview Feedback');
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
document.querySelectorAll('.fb-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.fb-tab').forEach(function(b) {
            b.style.color = '#6b7280';
            b.style.borderBottom = '2px solid transparent';
            b.classList.remove('active-tab');
        });
        document.querySelectorAll('.fb-tab-content').forEach(function(c) { c.style.display = 'none'; });

        this.style.color = '#16a34a';
        this.style.borderBottom = '2px solid #16a34a';
        this.classList.add('active-tab');
        var tab = this.getAttribute('data-tab');
        document.getElementById(tab).style.display = 'block';

        if (tab === 'tab-feedback') {
            if (typeof init_editor === 'function') {
                setTimeout(function() { init_editor('#feedback-editor'); }, 100);
            }
        }
    });
});

/* ---- Result radio UI ---- */
function updateResultUI() {
    document.querySelectorAll('.result-radio-label').forEach(function(lbl) {
        var inp = lbl.querySelector('input[type=radio]');
        var val = inp.value;
        var styles = {
            'Cleared':         ['#dcfce7','#16a34a','#16a34a'],
            'Not Cleared':     ['#fee2e2','#dc2626','#dc2626'],
            'To Be Discussed': ['#fef9c3','#854d0e','#854d0e'],
        };
        var s = styles[val] || ['#f3f4f6','#6b7280','#e5e7eb'];
        if (inp.checked) {
            lbl.style.background = s[0];
            lbl.style.color      = s[1];
            lbl.style.borderColor = s[2];
        } else {
            lbl.style.background  = '#fff';
            lbl.style.color       = '#6b7280';
            lbl.style.borderColor = '#e5e7eb';
        }
    });
}
updateResultUI();

/* ---- Star rating ---- */
var ratings = {};

function setRating(star, idx, val) {
    ratings[idx] = val;
    var widget = star.closest('.star-widget');
    widget.setAttribute('data-rating', val);
    widget.querySelector('input[type=hidden]').value = val;
    refreshStars(widget, val, false);
}
function hoverRating(star, idx, val) {
    var widget = star.closest('.star-widget');
    refreshStars(widget, val, true);
}
function resetRating(star, idx) {
    var widget = star.closest('.star-widget');
    var cur = parseInt(widget.getAttribute('data-rating')) || 0;
    refreshStars(widget, cur, false);
}
function refreshStars(widget, val, hover) {
    widget.querySelectorAll('.star').forEach(function(s) {
        s.style.color = parseInt(s.getAttribute('data-val')) <= val ? '#f59e0b' : '#d1d5db';
        s.style.transform = (hover && parseInt(s.getAttribute('data-val')) <= val) ? 'scale(1.15)' : 'scale(1)';
    });
}

/* ---- Skill rows ---- */
var skillIdx = <?php echo max(count($feedback_skills ?? []), 0); ?>;

function addSkillRow(skillName, rating) {
    var noRow = document.getElementById('no-skills-row');
    if (noRow) noRow.remove();
    var tbody = document.getElementById('skills-tbody');
    var rowCount = tbody.querySelectorAll('.skill-row').length;
    var idx = skillIdx++;
    var r = rating || 0;
    var stars = '';
    for (var i = 1; i <= 5; i++) {
        stars += '<span class="star" data-val="' + i + '" ' +
            'style="font-size:22px; cursor:pointer; color:' + (i <= r ? '#f59e0b' : '#d1d5db') + '; transition:color 0.1s; display:inline-block;" ' +
            'onclick="setRating(this,' + idx + ',' + i + ')" ' +
            'onmouseover="hoverRating(this,' + idx + ',' + i + ')" ' +
            'onmouseout="resetRating(this,' + idx + ')">&#9733;</span>';
    }
    var tr = document.createElement('tr');
    tr.className = 'skill-row';
    tr.setAttribute('data-idx', idx);
    tr.innerHTML =
        '<td style="padding:10px 12px; color:#6b7280; font-size:13px; vertical-align:middle;">' + (rowCount + 1) + '</td>' +
        '<td style="padding:10px 12px; vertical-align:middle;">' +
            '<input type="text" name="skills[' + idx + '][skill_name]" class="form-control" placeholder="Skill name" value="' + (skillName || '') + '" style="border-radius:4px;">' +
        '</td>' +
        '<td style="padding:10px 12px; text-align:center; vertical-align:middle;">' +
            '<div class="star-widget" data-idx="' + idx + '" data-rating="' + r + '">' + stars +
            '<input type="hidden" name="skills[' + idx + '][rating]" value="' + r + '">' +
            '</div>' +
        '</td>' +
        '<td style="padding:10px 12px; vertical-align:middle;">' +
            '<button type="button" onclick="removeSkillRow(this)" class="btn btn-danger btn-icon"><i class="fa fa-remove"></i></button>' +
        '</td>';
    tbody.appendChild(tr);
    renumberRows();
}

function removeSkillRow(btn) {
    btn.closest('tr').remove();
    renumberRows();
    if (!document.querySelectorAll('.skill-row').length) {
        var tbody = document.getElementById('skills-tbody');
        var noRow = document.createElement('tr');
        noRow.id = 'no-skills-row';
        noRow.innerHTML = '<td colspan="4" class="text-center" style="padding:30px; color:#9ca3af; font-size:13px;">No skills added yet. <a href="javascript:void(0)" onclick="addSkillRow()" style="color:#16a34a;">Add one</a>.</td>';
        tbody.appendChild(noRow);
    }
}

function renumberRows() {
    var rows = document.querySelectorAll('.skill-row');
    rows.forEach(function(r, i) { r.cells[0].textContent = i + 1; });
}

/* ---- AJAX: fetch interview details ---- */
var AJAX_URL = '<?php echo $base; ?>/get_interview_details_json/';

function fetchInterviewDetails(interviewId) {
    if (!interviewId) return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', AJAX_URL + interviewId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                document.getElementById('round_name_display').value     = d.round_name || '';
                document.getElementById('round_id_hidden').value        = d.round_id   || '';
                document.getElementById('applicant_name_display').value = d.applicant_name || '';

                if (d.interviewer_id) {
                    var sel = document.getElementById('interviewer_id');
                    for (var i = 0; i < sel.options.length; i++) {
                        if (sel.options[i].value == d.interviewer_id) { sel.selectedIndex = i; break; }
                    }
                }

                /* populate skills */
                var tbody = document.getElementById('skills-tbody');
                tbody.innerHTML = '';
                skillIdx = 0;
                if (d.skills && d.skills.length > 0) {
                    d.skills.forEach(function(sk) { addSkillRow(sk, 0); });
                } else {
                    var noRow = document.createElement('tr');
                    noRow.id = 'no-skills-row';
                    noRow.innerHTML = '<td colspan="4" class="text-center" style="padding:30px; color:#9ca3af; font-size:13px;">No skills defined for this round. <a href="javascript:void(0)" onclick="addSkillRow()" style="color:#16a34a;">Add manually</a>.</td>';
                    tbody.appendChild(noRow);
                }
            } catch(e) { console.error('AJAX parse error', e); }
        }
    };
    xhr.send();
}

/* Auto-fetch on page load if editing and interview already selected */
<?php if ($is_edit && !empty($feedback->interview_id) && empty($feedback_skills)): ?>
// fetchInterviewDetails(<?php echo (int)$feedback->interview_id; ?>);
<?php endif; ?>
</script>

<?php init_tail(); ?>
