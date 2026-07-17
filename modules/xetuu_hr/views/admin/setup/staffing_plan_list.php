<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

    <!-- Page header -->
    <div class="xhr-setup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <div class="xhr-setup-breadcrumb" style="font-size: 12px; margin-bottom: 5px; color: #6b7280;">
                <a href="<?php echo $base; ?>" style="color: #6b7280; text-decoration: none;">Xetuu HR</a>
                <span style="margin: 0 4px;">/</span>
                <span>Setup</span>
                <span style="margin: 0 4px;">/</span>
                <span style="color: #111827; font-weight: 500;">Staffing Plan</span>
            </div>
            <h1 class="xhr-setup-title" style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Staffing Plan</h1>
            <p class="xhr-setup-subtitle" style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">HR Headcount & budget planning per department.</p>
        </div>
        <a href="<?php echo $base . '/setup/staffing_plan/add'; ?>" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600; padding: 8px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-plus"></i> New Staffing Plan
        </a>
    </div>

    <!-- Main List -->
    <div class="panel_s" style="border: 1px solid #e5e7eb; border-radius: 8px;">
        <div class="panel-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #f9fafb;">
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Name</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Company</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Department</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">From Date</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">To Date</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Total Estimated Budget</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 30px; color: #9ca3af; font-size: 14px;">No staffing plans created yet.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td style="padding: 12px 16px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6; font-weight: 600;">
                                <a href="<?php echo $base . '/setup/staffing_plan/edit/' . $r->id; ?>" style="color: #16a34a; text-decoration: none;">
                                    <?php echo htmlspecialchars($r->name); ?>
                                </a>
                            </td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6;"><?php echo htmlspecialchars($r->company_name); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;"><?php echo htmlspecialchars($r->department_name ?: 'Global Default'); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;"><?php echo _d($r->from_date); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;"><?php echo _d($r->to_date); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #16a34a; border-bottom: 1px solid #f3f4f6; font-weight: 600;"><?php echo app_format_money($r->total_estimated_budget, ''); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f3f4f6; text-align: right;">
                                <a href="<?php echo $base . '/setup/staffing_plan/edit/' . $r->id; ?>" class="btn btn-default btn-icon">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?php echo $base . '/setup/staffing_plan/delete/' . $r->id; ?>" class="_delete btn btn-danger btn-icon">
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
