<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');
$s          = $settings ?? [];
function rs($s, $key, $default = '') { return isset($s[$key]) ? htmlspecialchars($s[$key]) : $default; }
function rc($s, $key) { return (isset($s[$key]) && $s[$key] == '1') ? 'checked' : ''; }
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Xetuu HR</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">Recruitment Settings</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Recruitment Settings</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Configure all aspects of your recruitment pipeline, notifications, and career portal.</p>
        </div>
    </div>

    <form action="<?php echo $base . '/setup/recruitment_settings'; ?>" method="post" id="rec-settings-form">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

        <div class="row">
            <!-- Main col-md-9 -->
            <div class="col-md-9">

                <!-- 1. Numbering -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#f0fdf4; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">tag</span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Document Numbering</div>
                            <div style="font-size:12px; color:#6b7280;">Auto-number prefixes for recruitment documents.</div>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Applicant Prefix</label>
                                    <input type="text" name="rec_applicant_number_prefix" class="form-control"
                                           value="<?php echo rs($s, 'rec_applicant_number_prefix', 'HR-APP'); ?>"
                                           placeholder="HR-APP">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">e.g. HR-APP-00001</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Job Offer Prefix</label>
                                    <input type="text" name="rec_offer_number_prefix" class="form-control"
                                           value="<?php echo rs($s, 'rec_offer_number_prefix', 'HR-OFF'); ?>"
                                           placeholder="HR-OFF">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">e.g. HR-OFF-00001</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Appointment Letter Prefix</label>
                                    <input type="text" name="rec_letter_number_prefix" class="form-control"
                                           value="<?php echo rs($s, 'rec_letter_number_prefix', 'HR-APL'); ?>"
                                           placeholder="HR-APL">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">e.g. HR-APL-00001</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Hiring Pipeline -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#2563eb;">linear_scale</span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Hiring Pipeline</div>
                            <div style="font-size:12px; color:#6b7280;">Control how applicants move through your pipeline stages.</div>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Default Stage on Application</label>
                                    <select name="rec_default_stage" class="form-control">
                                        <?php
                                        $stages = ['Applied', 'Screening', 'Interview', 'Offer'];
                                        $cur = rs($s, 'rec_default_stage', 'Applied');
                                        foreach ($stages as $st):
                                        ?>
                                        <option value="<?php echo $st; ?>" <?php echo $cur === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Stage assigned when a new applicant is created.</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Offer Acceptance Deadline (days)</label>
                                    <input type="number" name="rec_offer_expiry_days" class="form-control" min="1" max="90"
                                           value="<?php echo rs($s, 'rec_offer_expiry_days', '7'); ?>"
                                           placeholder="7">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Days before a sent offer is considered expired.</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:14px; margin-top:4px;">
                            <?php
                            $toggles = [
                                ['key'=>'rec_auto_stage_interview', 'label'=>'Auto-advance to Interview when interview is scheduled',
                                 'desc'=>'Applicant stage changes to Interview automatically when a new interview is created.'],
                                ['key'=>'rec_auto_stage_offer',     'label'=>'Auto-advance to Offer when job offer is created',
                                 'desc'=>'Applicant stage changes to Offer automatically when a job offer is saved.'],
                                ['key'=>'rec_auto_stage_hired',     'label'=>'Auto-advance to Hired when appointment letter is signed',
                                 'desc'=>'Applicant stage changes to Hired and an employee record is created when a letter is marked Signed.'],
                            ];
                            foreach ($toggles as $t):
                            ?>
                            <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px; border:1px solid #f3f4f6;">
                                <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                    <input type="hidden"   name="<?php echo $t['key']; ?>" value="0">
                                    <input type="checkbox" name="<?php echo $t['key']; ?>" value="1" <?php echo rc($s, $t['key']); ?>>
                                    <span class="xhr-toggle__slider"></span>
                                </label>
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo $t['label']; ?></div>
                                    <div style="font-size:12px; color:#6b7280; margin-top:2px;"><?php echo $t['desc']; ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Interview & Scoring -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#fefce8; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#ca8a04;">star_rate</span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Interview &amp; Skill Scoring</div>
                            <div style="font-size:12px; color:#6b7280;">Settings for interview scheduling, reminders and skill evaluations.</div>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Skill Rating Scale</label>
                                    <select name="rec_skill_rating_max" class="form-control">
                                        <?php foreach ([5, 10] as $mx): ?>
                                        <option value="<?php echo $mx; ?>" <?php echo rs($s,'rec_skill_rating_max','5') == $mx ? 'selected' : ''; ?>>
                                            1 – <?php echo $mx; ?> stars
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Max stars shown on each skill in Interview Feedback.</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Pass Threshold (%)</label>
                                    <div class="input-group">
                                        <input type="number" name="rec_pass_threshold" class="form-control" min="1" max="100"
                                               value="<?php echo rs($s, 'rec_pass_threshold', '60'); ?>"
                                               placeholder="60">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Minimum average score % to be marked as Cleared.</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:14px; margin-top:4px;">
                            <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px; border:1px solid #f3f4f6;">
                                <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                    <input type="hidden"   name="rec_send_interview_reminder" value="0">
                                    <input type="checkbox" name="rec_send_interview_reminder" value="1" <?php echo rc($s, 'rec_send_interview_reminder'); ?>>
                                    <span class="xhr-toggle__slider"></span>
                                </label>
                                <div style="flex:1;">
                                    <div style="font-size:13px; font-weight:600; color:#111827;">Send interview reminder emails</div>
                                    <div style="font-size:12px; color:#6b7280; margin-top:2px;">Notify interviewers and applicants before the scheduled interview time.</div>
                                    <div style="margin-top:10px; display:flex; align-items:center; gap:8px;">
                                        <label style="font-size:12px; color:#374151; white-space:nowrap;">Send reminder</label>
                                        <input type="number" name="rec_reminder_hours" class="form-control" min="1" max="72"
                                               value="<?php echo rs($s, 'rec_reminder_hours', '24'); ?>"
                                               style="width:80px; display:inline-block;">
                                        <label style="font-size:12px; color:#374151; white-space:nowrap;">hours before the interview.</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Email Notifications -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#fdf4ff; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#9333ea;">notifications</span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Email Notifications</div>
                            <div style="font-size:12px; color:#6b7280;">Control which email alerts are sent and to whom.</div>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:20px;">

                        <div class="row" style="margin-bottom:16px;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">New Applicant Alert Email</label>
                                    <input type="email" name="new_applicant_alert_email" class="form-control"
                                           value="<?php echo rs($s, 'new_applicant_alert_email'); ?>"
                                           placeholder="hr@company.com">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Notified when any new application is received.</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">CC HR Manager Email</label>
                                    <input type="email" name="rec_hr_manager_email" class="form-control"
                                           value="<?php echo rs($s, 'rec_hr_manager_email'); ?>"
                                           placeholder="manager@company.com">
                                    <span style="font-size:11px; color:#9ca3af; margin-top:3px; display:block;">Copied on all recruitment notifications.</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:14px;">
                            <?php
                            $notifs = [
                                ['key'=>'rec_cc_hr_manager',              'label'=>'CC HR Manager on all recruitment emails',
                                 'desc'=>'The email above will be copied on every notification sent to applicants or interviewers.'],
                                ['key'=>'rec_notify_applicant_received',  'label'=>'Notify applicant when application is received',
                                 'desc'=>'Send a confirmation email to the candidate as soon as their application is saved.'],
                                ['key'=>'rec_notify_applicant_interview', 'label'=>'Notify applicant when interview is scheduled',
                                 'desc'=>'Email the candidate with date, time and interview round details when an interview is created.'],
                                ['key'=>'rec_notify_applicant_offer',     'label'=>'Notify applicant when a job offer is made',
                                 'desc'=>'Email the candidate when a Job Offer with status "Sent" is saved for them.'],
                            ];
                            foreach ($notifs as $n):
                            ?>
                            <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px; border:1px solid #f3f4f6;">
                                <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                    <input type="hidden"   name="<?php echo $n['key']; ?>" value="0">
                                    <input type="checkbox" name="<?php echo $n['key']; ?>" value="1" <?php echo rc($s, $n['key']); ?>>
                                    <span class="xhr-toggle__slider"></span>
                                </label>
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:#111827;"><?php echo $n['label']; ?></div>
                                    <div style="font-size:12px; color:#6b7280; margin-top:2px;"><?php echo $n['desc']; ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 5. Career Portal -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:20px;">
                    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; background:#f0fdf4; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">public</span>
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:#111827;">Career Portal</div>
                            <div style="font-size:12px; color:#6b7280;">Settings for the public-facing careers page.</div>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:20px;">

                        <div style="display:flex; align-items:flex-start; gap:12px; padding:14px; background:#f9fafb; border-radius:8px; border:1px solid #f3f4f6; margin-bottom:16px;">
                            <label class="xhr-toggle" style="flex-shrink:0; margin-top:1px;">
                                <input type="hidden"   name="allow_portal_applications" value="0">
                                <input type="checkbox" name="allow_portal_applications" value="1" <?php echo rc($s, 'allow_portal_applications'); ?>>
                                <span class="xhr-toggle__slider"></span>
                            </label>
                            <div>
                                <div style="font-size:13px; font-weight:600; color:#111827;">Enable Public Careers Portal</div>
                                <div style="font-size:12px; color:#6b7280; margin-top:2px;">Allow candidates to browse open jobs and submit applications through the public portal.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Portal Page Title</label>
                                    <input type="text" name="rec_portal_title" class="form-control"
                                           value="<?php echo rs($s, 'rec_portal_title', 'Careers'); ?>"
                                           placeholder="e.g. Careers at Acme Inc.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Show Salary in Job Postings</label>
                                    <select name="rec_show_salary" class="form-control">
                                        <?php
                                        $cur = rs($s, 'rec_show_salary', 'never');
                                        foreach (['never'=>'Never', 'always'=>'Always', 'range'=>'Show as Range'] as $v => $l):
                                        ?>
                                        <option value="<?php echo $v; ?>" <?php echo $cur === $v ? 'selected' : ''; ?>><?php echo $l; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Portal Introduction</label>
                            <textarea name="rec_portal_description" class="form-control" rows="3"
                                      placeholder="A short message shown to candidates on the careers page..."
                                      style="border-radius:6px; resize:vertical;"><?php echo rs($s, 'rec_portal_description'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Save button -->
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:4px;">
                    <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; padding:9px 28px; font-weight:600; font-size:14px;">
                        <i class="fa fa-save"></i> Save Settings
                    </button>
                </div>

            </div><!-- /col-md-9 -->

            <!-- Sidebar col-md-3 -->
            <div class="col-md-3">

                <!-- Quick Nav -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">On This Page</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php
                        $anchors = [
                            ['label'=>'Document Numbering',    'icon'=>'tag',           'href'=>'#sec-numbering'],
                            ['label'=>'Hiring Pipeline',       'icon'=>'linear_scale',  'href'=>'#sec-pipeline'],
                            ['label'=>'Interview & Scoring',   'icon'=>'star_rate',     'href'=>'#sec-interview'],
                            ['label'=>'Email Notifications',   'icon'=>'notifications', 'href'=>'#sec-email'],
                            ['label'=>'Career Portal',         'icon'=>'public',        'href'=>'#sec-portal'],
                        ];
                        foreach ($anchors as $a):
                        ?>
                        <a href="<?php echo $a['href']; ?>" style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                            <span class="material-symbols-outlined" style="font-size:15px; color:#9ca3af;"><?php echo $a['icon']; ?></span>
                            <?php echo $a['label']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tips -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Tips</span>
                    </div>
                    <div style="padding:16px;">
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; gap:8px;">
                                <span class="material-symbols-outlined" style="font-size:16px; color:#16a34a; flex-shrink:0; margin-top:1px;">lightbulb</span>
                                <p style="font-size:12px; color:#6b7280; margin:0;">Prefix changes only affect new documents. Existing records keep their current numbers.</p>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <span class="material-symbols-outlined" style="font-size:16px; color:#2563eb; flex-shrink:0; margin-top:1px;">info</span>
                                <p style="font-size:12px; color:#6b7280; margin:0;">Auto-advance stages save HR time but can be overridden manually on any record.</p>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <span class="material-symbols-outlined" style="font-size:16px; color:#ca8a04; flex-shrink:0; margin-top:1px;">warning</span>
                                <p style="font-size:12px; color:#6b7280; margin:0;">Signing an Appointment Letter automatically creates an Employee record. Make sure the applicant's data is complete first.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recruitment shortcuts -->
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                    <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recruitment Setup</span>
                    </div>
                    <div style="padding:8px 0;">
                        <?php
                        $setup_links = [
                            ['label'=>'Staffing Plan',     'icon'=>'account_tree', 'url'=>$base.'/setup/staffing_plan'],
                            ['label'=>'Job Requisition',   'icon'=>'assignment',   'url'=>$base.'/setup/job_requisition'],
                            ['label'=>'Job Opening',       'icon'=>'work_outline', 'url'=>$base.'/recruitment/job_openings'],
                            ['label'=>'Interview Type',    'icon'=>'category',     'url'=>$base.'/setup/interview_type'],
                            ['label'=>'Interview Round',   'icon'=>'refresh',      'url'=>$base.'/setup/interview_round'],
                            ['label'=>'Letter Template',   'icon'=>'receipt_long', 'url'=>$base.'/setup/appointment_letter_template'],
                        ];
                        foreach ($setup_links as $sl):
                        ?>
                        <a href="<?php echo $sl['url']; ?>"
                           style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; font-size:13px; color:#4b5563;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $sl['icon']; ?></span>
                            <?php echo $sl['label']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /col-md-3 -->
        </div><!-- /row -->
    </form>
</div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<style>
/* Toggle switch */
.xhr-toggle { position:relative; display:inline-block; width:40px; height:22px; }
.xhr-toggle input { display:none; }
.xhr-toggle__slider {
    position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#d1d5db; border-radius:22px; transition:.25s;
}
.xhr-toggle__slider:before {
    content:''; position:absolute; width:16px; height:16px;
    left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.25s;
    box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.xhr-toggle input:checked + .xhr-toggle__slider { background:#16a34a; }
.xhr-toggle input:checked + .xhr-toggle__slider:before { transform:translateX(18px); }
</style>

<?php init_tail(); ?>
