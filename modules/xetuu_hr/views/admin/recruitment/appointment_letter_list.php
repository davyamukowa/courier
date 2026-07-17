<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'recruitment';
$base        = admin_url('xetuu_hr');
$stat_total  = $stat_total  ?? 0;
$stat_draft  = $stat_draft  ?? 0;
$stat_sent   = $stat_sent   ?? 0;
$stat_signed = $stat_signed ?? 0;
$sign_rate   = $stat_total > 0 ? round(($stat_signed / $stat_total) * 100) : 0;
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">Appointment Letters</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Appointment Letters</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Official letters issued to selected candidates confirming their appointment.</p>
        </div>
        <a href="<?php echo $base . '/recruitment/appointment_letters/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> New Appointment Letter
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php
        $cards = [
            ['label'=>'Total Letters', 'value'=>$stat_total,  'icon'=>'mail',          'bg'=>'#eff6ff','ic'=>'#2563eb','vc'=>'#1e40af'],
            ['label'=>'Draft',         'value'=>$stat_draft,  'icon'=>'edit_note',     'bg'=>'#f9fafb','ic'=>'#6b7280','vc'=>'#374151'],
            ['label'=>'Sent',          'value'=>$stat_sent,   'icon'=>'send',          'bg'=>'#fefce8','ic'=>'#ca8a04','vc'=>'#854d0e'],
            ['label'=>'Signed',        'value'=>$stat_signed, 'icon'=>'verified',      'bg'=>'#f0fdf4','ic'=>'#16a34a','vc'=>'#14532d'],
        ];
        foreach ($cards as $card):
        ?>
        <div class="col-md-3">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px; display:flex; align-items:center; gap:14px;">
                <div style="width:44px; height:44px; border-radius:10px; background:<?php echo $card['bg']; ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $card['ic']; ?>;"><?php echo $card['icon']; ?></span>
                </div>
                <div>
                    <div style="font-size:24px; font-weight:800; color:<?php echo $card['vc']; ?>; line-height:1;"><?php echo $card['value']; ?></div>
                    <div style="font-size:11px; color:#6b7280; margin-top:3px;"><?php echo $card['label']; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- Table col-md-9 -->
        <div class="col-md-9">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div class="panel-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table" style="margin-bottom:0;">
                            <thead>
                                <tr style="background-color:#f9fafb;">
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Letter #</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applicant</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Template</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Appointment Date</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($letters)): ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                        No appointment letters yet. <a href="<?php echo $base . '/recruitment/appointment_letters/add'; ?>" style="color:#16a34a;">Create one</a>.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($letters as $l):
                                    $sc_map = [
                                        'Draft'  => ['#f3f4f6','#6b7280'],
                                        'Sent'   => ['#fef9c3','#854d0e'],
                                        'Signed' => ['#dcfce7','#16a34a'],
                                    ];
                                    $sc = $sc_map[$l->status] ?? ['#f3f4f6','#6b7280'];
                                ?>
                                <tr>
                                    <td style="padding:12px 16px; font-size:11px; font-family:monospace; border-bottom:1px solid #f3f4f6;">
                                        <a href="<?php echo $base . '/recruitment/appointment_letters/edit/' . $l->id; ?>" style="color:#16a34a; text-decoration:none; font-weight:600;">
                                            <?php echo $l->letter_number ? htmlspecialchars($l->letter_number) : 'APL-'.$l->id; ?>
                                        </a>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                        <?php echo htmlspecialchars($l->applicant_name ?? '—'); ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $l->template_name ? htmlspecialchars($l->template_name) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo !empty($l->appointment_date) ? date('d M Y', strtotime($l->appointment_date)) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;">
                                            <?php echo htmlspecialchars($l->status); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                        <a href="<?php echo $base . '/recruitment/appointment_letters/edit/' . $l->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo $base . '/recruitment/appointment_letters/delete/' . $l->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
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

        <!-- Sidebar col-md-3 -->
        <div class="col-md-3">

            <!-- Status Breakdown -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Letter Status</span>
                </div>
                <div style="padding:16px;">
                    <?php
                    $sb = [
                        ['label'=>'Draft',  'val'=>$stat_draft,  'color'=>'#6b7280'],
                        ['label'=>'Sent',   'val'=>$stat_sent,   'color'=>'#ca8a04'],
                        ['label'=>'Signed', 'val'=>$stat_signed, 'color'=>'#16a34a'],
                    ];
                    foreach ($sb as $si):
                        $pct = $stat_total > 0 ? round(($si['val'] / $stat_total) * 100) : 0;
                    ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <span style="font-size:12px; color:#374151;"><?php echo $si['label']; ?></span>
                            <span style="font-size:12px; font-weight:600; color:<?php echo $si['color']; ?>;"><?php echo $si['val']; ?></span>
                        </div>
                        <div style="height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                            <div style="height:100%; width:<?php echo $pct; ?>%; background:<?php echo $si['color']; ?>; border-radius:3px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Signing Rate -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Signing Rate</span>
                </div>
                <div style="padding:20px 16px; text-align:center;">
                    <div style="position:relative; display:inline-block; width:100px; height:100px; margin:0 auto 12px;">
                        <svg viewBox="0 0 36 36" style="width:100px; height:100px; transform:rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#16a34a" stroke-width="3.5"
                                    stroke-dasharray="<?php echo $sign_rate; ?> <?php echo 100 - $sign_rate; ?>"
                                    stroke-linecap="round"/>
                        </svg>
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:20px; font-weight:800; color:#16a34a;"><?php echo $sign_rate; ?>%</div>
                    </div>
                    <div style="font-size:12px; color:#6b7280;"><?php echo $stat_signed; ?> of <?php echo $stat_total; ?> letters signed</div>
                </div>
            </div>

            <!-- Recent Letters -->
            <?php if (!empty($stat_recent)): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recent Letters</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $rc_map = ['Draft'=>['#f3f4f6','#6b7280'], 'Sent'=>['#fef9c3','#854d0e'], 'Signed'=>['#dcfce7','#16a34a']];
                    foreach ($stat_recent as $rl):
                        $rc = $rc_map[$rl->status] ?? ['#f3f4f6','#6b7280'];
                    ?>
                    <a href="<?php echo $base . '/recruitment/appointment_letters/edit/' . $rl->id; ?>"
                       style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; border-bottom:1px solid #f9fafb;">
                        <div style="width:32px; height:32px; border-radius:50%; background:#f0fdf4; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span class="material-symbols-outlined" style="font-size:15px; color:#16a34a;">mail</span>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; font-weight:600; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($rl->applicant_name ?? '—'); ?>
                            </div>
                            <div style="font-size:10px; color:#9ca3af; font-family:monospace;">
                                <?php echo $rl->letter_number ? htmlspecialchars($rl->letter_number) : 'APL-'.$rl->id; ?>
                            </div>
                        </div>
                        <span style="padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600; background:<?php echo $rc[0]; ?>; color:<?php echo $rc[1]; ?>; flex-shrink:0;">
                            <?php echo htmlspecialchars($rl->status); ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
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
    </div>
</div>

</div><!-- #wrapper -->

<?php init_tail(); ?>
