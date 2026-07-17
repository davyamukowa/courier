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
                <span style="color: #111827; font-weight: 500;">Job Requisition</span>
            </div>
            <h1 class="xhr-setup-title" style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Job Requisition</h1>
            <p class="xhr-setup-subtitle" style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Internal manager requests for opening a new vacancy.</p>
        </div>
        <button type="button" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;" onclick="xhrOpenModal()">
            <i class="fa fa-plus"></i> Add Requisition
        </button>
    </div>

    <!-- Sub-nav -->
    <div class="xhr-setup-subnav" style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; overflow-x: auto;">
        <?php
        $subnav_items = [
            'company'                      => ['icon' => 'business',       'label' => 'Company'],
            'branch'                       => ['icon' => 'location_on',    'label' => 'Branch'],
            'department'                   => ['icon' => 'account_tree',   'label' => 'Department'],
            'designation'                  => ['icon' => 'badge',          'label' => 'Designation'],
            'employee_group'               => ['icon' => 'groups',         'label' => 'Employee Group'],
            'employee_grade'               => ['icon' => 'military_tech',  'label' => 'Employee Grade'],
            'staffing_plan'                => ['icon' => 'fact_check',     'label' => 'Staffing Plan'],
            'job_requisition'              => ['icon' => 'assignment',     'label' => 'Job Requisition'],
            'interview_type'               => ['icon' => 'category',       'label' => 'Interview Type'],
            'interview_round'              => ['icon' => 'sync',           'label' => 'Interview Round'],
            'appointment_letter_template'  => ['icon' => 'receipt_long',   'label' => 'Letter Template'],
            'recruitment_settings'         => ['icon' => 'settings',       'label' => 'Settings'],
        ];
        foreach ($subnav_items as $key => $item):
        ?>
        <a href="<?php echo $base . '/setup/' . $key; ?>"
           style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500; <?php echo $sub === $key ? 'background-color: #f0faf4; color: #16a34a;' : 'color: #4b5563;'; ?>">
            <span class="material-symbols-outlined" style="font-size: 18px;"><?php echo $item['icon']; ?></span>
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Main List -->
    <div class="panel_s" style="border: 1px solid #e5e7eb; border-radius: 8px;">
        <div class="panel-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #f9fafb;">
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Department</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Designation</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Staffing Plan</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Requested By</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Positions</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Est. Salary</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb;">Status</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px; color: #9ca3af; font-size: 14px;">No requisitions created yet.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td style="padding: 12px 16px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6;"><?php echo htmlspecialchars($r->department_name); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6;"><?php echo htmlspecialchars($r->designation_name); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;">
                                <?php
                                $plan_label = 'None';
                                if ($r->staffing_plan_id) {
                                    foreach ($staffing_plans as $sp) {
                                        if ($sp->id == $r->staffing_plan_id) {
                                            $plan_label = $sp->financial_year . ' (' . $sp->department_name . ' - ' . $sp->designation_name . ')';
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars($plan_label);
                                ?>
                            </td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;"><?php echo htmlspecialchars($r->requester_name); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6;"><?php echo $r->no_of_positions; ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; color: #4b5563; border-bottom: 1px solid #f3f4f6;"><?php echo app_format_money($r->expected_salary, ''); ?></td>
                            <td style="padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f3f4f6;">
                                <?php
                                $class = 'default';
                                if ($r->status === 'Approved')   $class = 'success';
                                if ($r->status === 'Rejected')   $class = 'danger';
                                if ($r->status === 'Pending Approval') $class = 'warning';
                                ?>
                                <span class="label label-<?php echo $class; ?>"><?php echo $r->status; ?></span>
                            </td>
                            <td style="padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f3f4f6; text-align: right;">
                                <button type="button" class="btn btn-default btn-icon" onclick='xhrOpenModal(<?php echo json_encode($r); ?>)'>
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <a href="<?php echo $base . '/setup/job_requisition/delete/' . $r->id; ?>" class="_delete btn btn-danger btn-icon">
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

<!-- Modal Form -->
<div id="xhr-setup-modal" class="modal fade" role="dialog" style="display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog" style="width: 500px; margin: 0;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form action="<?php echo $base . '/setup/job_requisition'; ?>" method="post">
                <input type="hidden" name="id" id="mf_id">
                
                <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                    <button type="button" class="close" onclick="xhrCloseModal()">&times;</button>
                    <h4 class="modal-title" id="xhr-modal-title" style="font-weight: 700; color: #111827;">Add Requisition</h4>
                </div>
                
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label for="mf_staffing_plan_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Staffing Plan (Optional)</label>
                        <select name="staffing_plan_id" id="mf_staffing_plan_id" class="form-control" style="border-radius: 6px;">
                            <option value="">— Select Staffing Plan —</option>
                            <?php foreach ($staffing_plans as $sp): ?>
                            <option value="<?php echo $sp->id; ?>"><?php echo htmlspecialchars($sp->financial_year . ' (' . $sp->department_name . ' - ' . $sp->designation_name . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_department_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Department *</label>
                        <select name="department_id" id="mf_department_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Department —</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_designation_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Designation *</label>
                        <select name="designation_id" id="mf_designation_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Designation —</option>
                            <?php foreach ($designations as $des): ?>
                            <option value="<?php echo $des->id; ?>"><?php echo htmlspecialchars($des->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_requested_by" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Requested By *</label>
                        <select name="requested_by" id="mf_requested_by" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Requester —</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['staffid']; ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_no_of_positions" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">No of Positions *</label>
                        <input type="number" name="no_of_positions" id="mf_no_of_positions" class="form-control" required style="border-radius: 6px;">
                    </div>

                    <div>
                        <label for="mf_expected_salary" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Expected Salary Range</label>
                        <input type="number" step="0.01" name="expected_salary" id="mf_expected_salary" class="form-control" style="border-radius: 6px;">
                    </div>

                    <div>
                        <label for="mf_reason" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Reason for Requisition</label>
                        <textarea name="reason" id="mf_reason" class="form-control" rows="3" style="border-radius: 6px; resize: vertical;"></textarea>
                    </div>

                    <div>
                        <label for="mf_status" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Requisition Status</label>
                        <select name="status" id="mf_status" class="form-control" style="border-radius: 6px;">
                            <option value="Draft">Draft</option>
                            <option value="Pending Approval">Pending Approval</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                    <button type="button" class="btn btn-default" onclick="xhrCloseModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">Save Requisition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function xhrOpenModal(row) {
    var modal = document.getElementById('xhr-setup-modal');
    var title = document.getElementById('xhr-modal-title');
    
    document.getElementById('mf_id').value = '';
    document.getElementById('mf_staffing_plan_id').value = '';
    document.getElementById('mf_department_id').value = '';
    document.getElementById('mf_designation_id').value = '';
    document.getElementById('mf_requested_by').value = '';
    document.getElementById('mf_no_of_positions').value = '1';
    document.getElementById('mf_expected_salary').value = '';
    document.getElementById('mf_reason').value = '';
    document.getElementById('mf_status').value = 'Draft';

    if (row) {
        title.textContent = 'Edit Requisition';
        document.getElementById('mf_id').value = row.id;
        document.getElementById('mf_staffing_plan_id').value = row.staffing_plan_id || '';
        document.getElementById('mf_department_id').value = row.department_id;
        document.getElementById('mf_designation_id').value = row.designation_id;
        document.getElementById('mf_requested_by').value = row.requested_by;
        document.getElementById('mf_no_of_positions').value = row.no_of_positions;
        document.getElementById('mf_expected_salary').value = row.expected_salary;
        document.getElementById('mf_reason').value = row.reason;
        document.getElementById('mf_status').value = row.status;
    } else {
        title.textContent = 'Add Requisition';
    }

    modal.style.display = 'flex';
    modal.classList.add('in');
}

function xhrCloseModal() {
    var modal = document.getElementById('xhr-setup-modal');
    modal.style.display = 'none';
    modal.classList.remove('in');
}
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
