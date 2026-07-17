<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'recruitment';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div style="font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px; margin-bottom: 5px;">
                <a href="<?php echo $base; ?>" style="color: #6b7280; text-decoration: none;">Recruitment</a>
                <span>/</span>
                <span style="color: #111827; font-weight: 500;">Job Requisition</span>
            </div>
            <h1 style="font-size: 22px; font-weight: 700; color: #111827; margin: 0;">Job Requisition</h1>
            <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Internal requests for opening a new vacancy.</p>
        </div>
        <a href="<?php echo $base . '/setup/job_requisition/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> New Job Requisition
        </a>
    </div>

    <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
        <div class="panel-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom:0;">
                    <thead>
                        <tr style="background-color:#f9fafb;">
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Requisition #</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Designation</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Department</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Company</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Requested By</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Positions</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Posting Date</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Status</th>
                            <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                No job requisitions yet. <a href="<?php echo $base . '/setup/job_requisition/add'; ?>" style="color:#16a34a;">Create one</a>.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <?php
                        $status_styles = [
                            'Pending'          => ['bg' => '#fef9c3', 'color' => '#854d0e'],
                            'Open'             => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'Open & Approved'  => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                            'Filled'           => ['bg' => '#f3f4f6', 'color' => '#374151'],
                            'Cancelled'        => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                        ];
                        $ss = $status_styles[$r->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
                        ?>
                        <tr>
                            <td style="padding:12px 16px; font-size:13px; border-bottom:1px solid #f3f4f6; font-weight:600;">
                                <a href="<?php echo $base . '/setup/job_requisition/edit/' . $r->id; ?>" style="color:#16a34a; text-decoration:none;">
                                    <?php echo htmlspecialchars($r->requisition_number ?: '—'); ?>
                                </a>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#111827; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($r->designation_name ?: '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($r->department_name ?: '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($r->company_name ?: '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($r->requester_name ?: '—'); ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#111827; border-bottom:1px solid #f3f4f6; text-align:center;"><?php echo (int)$r->no_of_positions; ?></td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;"><?php echo $r->posting_date ? _d($r->posting_date) : '—'; ?></td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $ss['bg']; ?>; color:<?php echo $ss['color']; ?>;">
                                    <?php echo htmlspecialchars($r->status); ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                <a href="<?php echo $base . '/setup/job_requisition/edit/' . $r->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?php echo $base . '/setup/job_requisition/delete/' . $r->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
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
