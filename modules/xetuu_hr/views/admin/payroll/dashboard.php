<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Page header + company switcher -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Xetuu HR / <span style="color:#111827;">Payroll</span></div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payroll Dashboard</h1>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <!-- Company switcher -->
            <?php if (!empty($payroll_companies)): ?>
            <form method="GET" style="margin:0;">
                <select name="company" onchange="this.form.submit()"
                    style="padding:7px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; background:#fff; cursor:pointer;">
                    <option value="0"<?php echo !$active_company_id ? ' selected' : ''; ?>>All Companies</option>
                    <?php foreach ($payroll_companies as $co): ?>
                    <option value="<?php echo $co->id; ?>"<?php echo $active_company_id == $co->id ? ' selected' : ''; ?>>
                        <?php echo htmlspecialchars($co->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
            <a href="<?php echo $base.'/payroll/batches/new'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px; font-size:13px;">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-3px;">add</span> New Payroll Run
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row" style="margin-bottom:24px;">
        <?php
        $kpis = [
            ['label'=>'Active Contracts',  'value'=>number_format($stats['active_contracts']),   'icon'=>'description',   'color'=>'#2563eb', 'bg'=>'#eff6ff'],
            ['label'=>'Payroll Companies', 'value'=>number_format($stats['companies']),           'icon'=>'business',      'color'=>'#16a34a', 'bg'=>'#f0fdf4'],
            ['label'=>'This Month Net Pay','value'=>number_format($stats['month_net'],2),         'icon'=>'payments',      'color'=>'#9333ea', 'bg'=>'#fdf4ff'],
            ['label'=>'Pending Runs',      'value'=>number_format($stats['pending_runs']),        'icon'=>'pending_actions','color'=>'#ca8a04','bg'=>'#fefce8'],
        ];
        foreach ($kpis as $k):
        ?>
        <div class="col-md-3 col-sm-6" style="margin-bottom:16px;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; display:flex; align-items:center; gap:14px;">
                <div style="width:44px; height:44px; background:<?php echo $k['bg']; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:<?php echo $k['color']; ?>;"><?php echo $k['icon']; ?></span>
                </div>
                <div>
                    <div style="font-size:22px; font-weight:800; color:#111827; line-height:1;"><?php echo $k['value']; ?></div>
                    <div style="font-size:12px; color:#6b7280; margin-top:3px;"><?php echo $k['label']; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- Recent Runs -->
        <div class="col-md-8">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:20px;">
                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:14px; font-weight:700; color:#111827;">Recent Payroll Runs</span>
                    <a href="<?php echo $base.'/payroll/batches'; ?>" style="font-size:12px; color:#2563eb; text-decoration:none;">View All</a>
                </div>
                <?php if (empty($recent_runs)): ?>
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <span class="material-symbols-outlined" style="font-size:36px; display:block; margin-bottom:8px;">layers</span>
                    No payroll runs yet. <a href="<?php echo $base.'/payroll/batches/new'; ?>" style="color:#2563eb;">Create your first run</a>.
                </div>
                <?php else: ?>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Company</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Period</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Payslips</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Net Pay</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($recent_runs as $run): ?>
                    <?php
                    $state_colors = [
                        'draft'     => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
                        'computing' => ['bg'=>'#fef3c7','color'=>'#d97706'],
                        'computed'  => ['bg'=>'#eff6ff','color'=>'#2563eb'],
                        'confirmed' => ['bg'=>'#f0fdf4','color'=>'#16a34a'],
                        'done'      => ['bg'=>'#f0fdf4','color'=>'#16a34a'],
                        'paid'      => ['bg'=>'#dcfce7','color'=>'#15803d'],
                        'cancelled' => ['bg'=>'#fef2f2','color'=>'#dc2626'],
                    ];
                    $sc = $state_colors[$run->state] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];
                    ?>
                    <tr style="cursor:pointer;" onclick="location.href='<?php echo $base.'/payroll/batches/view/'.$run->id; ?>'">
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($run->name); ?></td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($run->company_name ?? '—'); ?></td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo date('d M',strtotime($run->date_from)); ?> – <?php echo date('d M Y',strtotime($run->date_to)); ?></td>
                        <td style="font-size:13px; font-weight:600; color:#374151;"><?php echo number_format($run->employee_count); ?></td>
                        <td>
                            <span style="font-size:11px; font-weight:700; padding:3px 9px; border-radius:4px; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>; text-transform:uppercase;">
                                <?php echo $run->state; ?>
                            </span>
                        </td>
                        <td style="font-size:13px; font-weight:700; color:#111827;"><?php echo ($run->currency ?? 'KES').' '.number_format($run->total_net,2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:16px;">
                <div style="font-size:13px; font-weight:700; color:#111827; margin-bottom:16px;">Quick Actions</div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="<?php echo $base.'/payroll/batches/new'; ?>" style="display:flex; align-items:center; gap:10px; padding:12px; background:#f9fafb; border-radius:8px; text-decoration:none; color:#374151; font-size:13px; font-weight:500;">
                        <span class="material-symbols-outlined" style="color:#2563eb; font-size:18px;">add_circle</span>New Payroll Run
                    </a>
                    <a href="<?php echo $base.'/payroll/payslips/new'; ?>" style="display:flex; align-items:center; gap:10px; padding:12px; background:#f9fafb; border-radius:8px; text-decoration:none; color:#374151; font-size:13px; font-weight:500;">
                        <span class="material-symbols-outlined" style="color:#16a34a; font-size:18px;">receipt</span>Single Payslip
                    </a>
                    <a href="<?php echo $base.'/payroll/contracts'; ?>" style="display:flex; align-items:center; gap:10px; padding:12px; background:#f9fafb; border-radius:8px; text-decoration:none; color:#374151; font-size:13px; font-weight:500;">
                        <span class="material-symbols-outlined" style="color:#9333ea; font-size:18px;">description</span>Manage Contracts
                    </a>
                    <a href="<?php echo $base.'/payroll/work_entries'; ?>" style="display:flex; align-items:center; gap:10px; padding:12px; background:#f9fafb; border-radius:8px; text-decoration:none; color:#374151; font-size:13px; font-weight:500;">
                        <span class="material-symbols-outlined" style="color:#ca8a04; font-size:18px;">upload_file</span>Upload Timesheet
                    </a>
                    <a href="<?php echo $base.'/payroll/config/addons'; ?>" style="display:flex; align-items:center; gap:10px; padding:12px; background:#f9fafb; border-radius:8px; text-decoration:none; color:#374151; font-size:13px; font-weight:500;">
                        <span class="material-symbols-outlined" style="color:#ea580c; font-size:18px;">extension</span>Manage Addons
                    </a>
                </div>
            </div>

            <?php if (!empty($payroll_companies)): ?>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
                <div style="font-size:13px; font-weight:700; color:#111827; margin-bottom:12px;">Managed Companies</div>
                <?php foreach ($payroll_companies as $co): ?>
                <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f3f4f6;">
                    <div style="width:32px; height:32px; background:#eff6ff; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#2563eb; flex-shrink:0;">
                        <?php echo strtoupper(substr($co->name,0,2)); ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <a href="?company=<?php echo $co->id; ?>" style="font-size:13px; font-weight:600; color:#111827; text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($co->name); ?>
                        </a>
                        <div style="font-size:11px; color:#9ca3af;"><?php echo $co->currency; ?> · <?php echo $co->country_code; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="<?php echo $base.'/payroll/config/companies/add'; ?>" style="display:block; margin-top:10px; font-size:12px; color:#2563eb; text-decoration:none; text-align:center;">+ Add Company</a>
            </div>
            <?php else: ?>
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:20px; text-align:center;">
                <span class="material-symbols-outlined" style="color:#d97706; font-size:28px; display:block; margin-bottom:8px;">warning</span>
                <div style="font-size:13px; font-weight:600; color:#92400e; margin-bottom:6px;">No Payroll Companies</div>
                <div style="font-size:12px; color:#b45309; margin-bottom:12px;">Set up at least one payroll company and assign a country addon.</div>
                <a href="<?php echo $base.'/payroll/config/companies/add'; ?>" class="btn btn-warning btn-sm" style="border-radius:6px;">+ Setup Company</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php init_tail(); ?>
