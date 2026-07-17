<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">Interview Feedback</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Interview Feedback</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Skill assessments and feedback submitted after interviews.</p>
        </div>
        <a href="<?php echo $base . '/recruitment/interview_feedback/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> Add Interview Feedback
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div class="panel-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table" style="margin-bottom:0;">
                            <thead>
                                <tr style="background-color:#f9fafb;">
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">#</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Interview</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applicant</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Round</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Interviewer</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Result</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feedbacks)): ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                        No feedback submitted yet. <a href="<?php echo $base . '/recruitment/interview_feedback/add'; ?>" style="color:#16a34a;">Add one</a>.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($feedbacks as $fb):
                                    $result_colors = [
                                        'Cleared'           => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                                        'Not Cleared'       => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                                        'To Be Discussed'   => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                                    ];
                                    $rc = $result_colors[$fb->result] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];
                                    $status_color = $fb->status === 'Submitted' ? '#16a34a' : '#6b7280';
                                    $status_bg    = $fb->status === 'Submitted' ? '#dcfce7' : '#f3f4f6';
                                ?>
                                <tr>
                                    <td style="padding:12px 16px; font-size:11px; color:#6b7280; font-family:monospace; border-bottom:1px solid #f3f4f6;">
                                        <a href="<?php echo $base . '/recruitment/interview_feedback/edit/' . $fb->id; ?>" style="color:#16a34a; text-decoration:none;">
                                            <?php echo $fb->feedback_number ? htmlspecialchars($fb->feedback_number) : 'FEED-'.$fb->id; ?>
                                        </a>
                                    </td>
                                    <td style="padding:12px 16px; font-size:12px; font-family:monospace; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $fb->interview_number ? htmlspecialchars($fb->interview_number) : '—'; ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                        <?php echo htmlspecialchars($fb->applicant_name ?? '—'); ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $fb->round_name ? htmlspecialchars($fb->round_name) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $fb->interviewer_name ? htmlspecialchars($fb->interviewer_name) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $rc['bg']; ?>; color:<?php echo $rc['color']; ?>;">
                                            <?php echo htmlspecialchars($fb->result); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $status_bg; ?>; color:<?php echo $status_color; ?>;">
                                            <?php echo htmlspecialchars($fb->status); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                        <a href="<?php echo $base . '/recruitment/interview_feedback/edit/' . $fb->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo $base . '/recruitment/interview_feedback/delete/' . $fb->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
                                            <i class="fa fa-remove"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
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
        </div>
    </div>
</div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
