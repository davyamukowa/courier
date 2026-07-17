<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px; margin-bottom:5px;">
                <a href="<?php echo $base; ?>" style="color:#6b7280; text-decoration:none;">Recruitment</a>
                <span>/</span>
                <span style="color:#111827; font-weight:500;">Job Opening</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Job Opening</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Post and manage employment vacancies.</p>
        </div>
        <a href="<?php echo $base . '/recruitment/job_openings/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> New Job Opening
        </a>
    </div>

    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
        <div class="panel-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom:0;">
                    <thead>
                        <tr style="background-color:#f9fafb;">
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Job Title</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Designation</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Department</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Company</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:center;">Positions</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Close Date</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Published</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($openings)): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                No job openings yet. <a href="<?php echo $base . '/recruitment/job_openings/add'; ?>" style="color:#16a34a;">Create one</a>.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($openings as $o): ?>
                        <?php
                        $ss = [
                            'Open'    => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                            'Closed'  => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                            'On Hold' => ['bg' => '#fef9c3', 'color' => '#854d0e'],
                        ][$o->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
                        ?>
                        <tr>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                <a href="<?php echo $base . '/recruitment/job_openings/edit/' . $o->id; ?>" style="color:#16a34a; text-decoration:none;">
                                    <?php echo htmlspecialchars($o->title); ?>
                                </a>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($o->designation_name ?? '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($o->department_name ?? '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($o->company_name ?? '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#111827; font-weight:600; border-bottom:1px solid #f3f4f6; text-align:center;"><?php echo (int)$o->no_of_positions; ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo $o->close_date ? _d($o->close_date) : '<span style="color:#9ca3af;">Ongoing</span>'; ?></td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:center;">
                                <?php if (!empty($o->publish_on_website)): ?>
                                <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;" title="Published">check_circle</span>
                                <?php else: ?>
                                <span class="material-symbols-outlined" style="font-size:18px; color:#d1d5db;" title="Not published">cancel</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $ss['bg']; ?>; color:<?php echo $ss['color']; ?>;">
                                    <?php echo $o->status; ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                <a href="<?php echo $base . '/recruitment/job_openings/edit/' . $o->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?php echo $base . '/recruitment/job_openings/delete/' . $o->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
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

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
