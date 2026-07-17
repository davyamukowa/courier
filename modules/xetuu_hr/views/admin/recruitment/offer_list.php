<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active    = 'recruitment';
$base          = admin_url('xetuu_hr');

$stat_total    = $stat_total    ?? 0;
$stat_awaiting = $stat_awaiting ?? 0;
$stat_accepted = $stat_accepted ?? 0;
$stat_rejected = $stat_rejected ?? 0;
$stat_sent     = $stat_sent     ?? 0;
$stat_draft    = $stat_draft    ?? 0;

$accept_rate = $stat_total > 0 ? round(($stat_accepted / $stat_total) * 100) : 0;
$responded   = $stat_accepted + $stat_rejected;
$response_rate = $stat_total > 0 ? round(($responded / $stat_total) * 100) : 0;
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
                <span style="color:#111827; font-weight:500;">Job Offers</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Job Offers</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Salary packages and terms offered to selected candidates after interview.</p>
        </div>
        <a href="<?php echo $base . '/recruitment/offers/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> New Job Offer
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php
        $cards = [
            ['label'=>'Total Offers',       'value'=>$stat_total,    'icon'=>'handshake',       'bg'=>'#eff6ff','ic'=>'#2563eb','vc'=>'#1e40af'],
            ['label'=>'Awaiting Response',  'value'=>$stat_awaiting, 'icon'=>'schedule',        'bg'=>'#fefce8','ic'=>'#ca8a04','vc'=>'#854d0e'],
            ['label'=>'Accepted',           'value'=>$stat_accepted, 'icon'=>'check_circle',    'bg'=>'#f0fdf4','ic'=>'#16a34a','vc'=>'#14532d'],
            ['label'=>'Rejected',           'value'=>$stat_rejected, 'icon'=>'cancel',          'bg'=>'#fef2f2','ic'=>'#dc2626','vc'=>'#7f1d1d'],
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
        <!-- Table: col-md-9 -->
        <div class="col-md-9">
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div class="panel-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table" style="margin-bottom:0;">
                            <thead>
                                <tr style="background-color:#f9fafb;">
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Offer #</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applicant</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Job Opening</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Designation</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Offer Date</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($offers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                        No job offers yet. <a href="<?php echo $base . '/recruitment/offers/add'; ?>" style="color:#16a34a;">Create one</a>.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($offers as $o):
                                    $status_colors = [
                                        'Awaiting Response' => ['#fef9c3','#854d0e'],
                                        'Accepted'          => ['#dcfce7','#16a34a'],
                                        'Rejected'          => ['#fee2e2','#dc2626'],
                                        'Draft'             => ['#f3f4f6','#6b7280'],
                                        'Sent'              => ['#dbeafe','#1d4ed8'],
                                        'Declined'          => ['#fee2e2','#dc2626'],
                                    ];
                                    $sc = $status_colors[$o->status] ?? ['#f3f4f6','#6b7280'];
                                ?>
                                <tr>
                                    <td style="padding:12px 16px; font-size:11px; font-family:monospace; border-bottom:1px solid #f3f4f6;">
                                        <a href="<?php echo $base . '/recruitment/offers/edit/' . $o->id; ?>" style="color:#16a34a; text-decoration:none; font-weight:600;">
                                            <?php echo $o->offer_number ? htmlspecialchars($o->offer_number) : 'OFF-'.$o->id; ?>
                                        </a>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                        <?php echo htmlspecialchars($o->applicant_name ?? '—'); ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $o->job_title ? htmlspecialchars($o->job_title) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $o->designation_name ? htmlspecialchars($o->designation_name) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                        <?php echo $o->offer_date ? date('d M Y', strtotime($o->offer_date)) : '<span style="color:#9ca3af;">—</span>'; ?>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                        <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;">
                                            <?php echo htmlspecialchars($o->status); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                        <a href="<?php echo $base . '/recruitment/offers/edit/' . $o->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo $base . '/recruitment/offers/delete/' . $o->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
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

        <!-- Sidebar: col-md-3 -->
        <div class="col-md-3">

            <!-- Offer Status Breakdown -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Offer Status</span>
                </div>
                <div style="padding:16px;">
                    <?php
                    $sb_items = [
                        ['label'=>'Awaiting Response', 'val'=>$stat_awaiting, 'color'=>'#ca8a04', 'bg'=>'#fef9c3'],
                        ['label'=>'Accepted',          'val'=>$stat_accepted, 'color'=>'#16a34a', 'bg'=>'#dcfce7'],
                        ['label'=>'Rejected',          'val'=>$stat_rejected, 'color'=>'#dc2626', 'bg'=>'#fee2e2'],
                        ['label'=>'Sent',              'val'=>$stat_sent,     'color'=>'#2563eb', 'bg'=>'#dbeafe'],
                        ['label'=>'Draft',             'val'=>$stat_draft,    'color'=>'#6b7280', 'bg'=>'#f3f4f6'],
                    ];
                    foreach ($sb_items as $si):
                        $pct = $stat_total > 0 ? round(($si['val'] / $stat_total) * 100) : 0;
                    ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <span style="font-size:12px; color:#374151;"><?php echo $si['label']; ?></span>
                            <span style="font-size:12px; font-weight:600; color:<?php echo $si['color']; ?>;"><?php echo $si['val']; ?></span>
                        </div>
                        <div style="height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                            <div style="height:100%; width:<?php echo $pct; ?>%; background:<?php echo $si['color']; ?>; border-radius:3px; transition:width 0.6s;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Acceptance & Response Rate -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Conversion Rates</span>
                </div>
                <div style="padding:16px; display:flex; gap:10px;">
                    <!-- Accept Rate -->
                    <div style="flex:1; text-align:center; padding:14px 10px; background:#f0fdf4; border-radius:8px; border:1px solid #bbf7d0;">
                        <div style="font-size:26px; font-weight:800; color:#16a34a; line-height:1;"><?php echo $accept_rate; ?>%</div>
                        <div style="font-size:10px; color:#6b7280; margin-top:4px; font-weight:600;">Acceptance Rate</div>
                    </div>
                    <!-- Response Rate -->
                    <div style="flex:1; text-align:center; padding:14px 10px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe;">
                        <div style="font-size:26px; font-weight:800; color:#2563eb; line-height:1;"><?php echo $response_rate; ?>%</div>
                        <div style="font-size:10px; color:#6b7280; margin-top:4px; font-weight:600;">Response Rate</div>
                    </div>
                </div>
            </div>

            <!-- By Designation -->
            <?php if (!empty($stat_by_designation)): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">By Designation</span>
                </div>
                <div style="padding:16px;">
                    <?php
                    $max_des = max(array_column((array)$stat_by_designation, 'cnt') ?: [1]);
                    foreach ($stat_by_designation as $des):
                        $bar_w = $max_des > 0 ? round(($des->cnt / $max_des) * 100) : 0;
                    ?>
                    <div style="margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                            <span style="font-size:11px; color:#374151; max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($des->designation_name ?? 'Unspecified'); ?>
                            </span>
                            <span style="font-size:11px; font-weight:700; color:#16a34a;"><?php echo $des->cnt; ?></span>
                        </div>
                        <div style="height:5px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                            <div style="height:100%; width:<?php echo $bar_w; ?>%; background:#16a34a; border-radius:3px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Offers -->
            <?php if (!empty($stat_recent)): ?>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.05em;">Recent Offers</span>
                </div>
                <div style="padding:8px 0;">
                    <?php
                    $rc_map = [
                        'Awaiting Response' => ['#fef9c3','#854d0e'],
                        'Accepted'          => ['#dcfce7','#16a34a'],
                        'Rejected'          => ['#fee2e2','#dc2626'],
                        'Sent'              => ['#dbeafe','#1d4ed8'],
                        'Draft'             => ['#f3f4f6','#6b7280'],
                    ];
                    foreach ($stat_recent as $ro):
                        $rc = $rc_map[$ro->status] ?? ['#f3f4f6','#6b7280'];
                    ?>
                    <a href="<?php echo $base . '/recruitment/offers/edit/' . $ro->id; ?>"
                       style="display:flex; align-items:center; gap:8px; padding:8px 16px; text-decoration:none; border-bottom:1px solid #f9fafb;">
                        <div style="width:32px; height:32px; border-radius:50%; background:#f0fdf4; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span class="material-symbols-outlined" style="font-size:15px; color:#16a34a;">handshake</span>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; font-weight:600; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($ro->applicant_name ?? '—'); ?>
                            </div>
                            <div style="font-size:10px; color:#9ca3af; font-family:monospace;">
                                <?php echo $ro->offer_number ? htmlspecialchars($ro->offer_number) : 'OFF-'.$ro->id; ?>
                            </div>
                        </div>
                        <span style="padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600; background:<?php echo $rc[0]; ?>; color:<?php echo $rc[1]; ?>; flex-shrink:0;">
                            <?php echo htmlspecialchars($ro->status); ?>
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
    </div>
</div>

</div><!-- #wrapper -->

<?php init_tail(); ?>
