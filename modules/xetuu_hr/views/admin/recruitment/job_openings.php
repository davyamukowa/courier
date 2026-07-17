<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'recruitment'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header" style="display: flex; justify-content: space-between; padding: 1.5rem 2rem; background: transparent; border-bottom: none; align-items: center;">
    <div>
        <h2 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">Job Openings</h2>
        <p style="margin: 0; color: #64748b; font-size: 14px;">Post and manage employment vacancies.</p>
    </div>
    <button class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
        <span class="material-symbols-outlined">add</span>
        Add Job Opening
    </button>
</div>

<!-- Page Content -->
<div class="xhr-content" style="padding: 0 2rem 2rem 2rem;">
    <div class="xhr-card" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr style="background-color: #f8fafc;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Job Title</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Department</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Designation</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Openings</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Close Date</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Status</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($openings)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8; font-size: 14px;">No job openings found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($openings as $o): ?>
                    <tr>
                        <td style="padding: 14px 16px; font-size: 14px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($o->title); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($o->department_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($o->designation_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #1e293b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">
                            <?php echo $o->no_of_positions; ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo $o->close_date ? _d($o->close_date) : 'Ongoing'; ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php
                            $badge = 'default';
                            if ($o->status === 'Open')    $badge = 'success';
                            if ($o->status === 'Closed')  $badge = 'danger';
                            if ($o->status === 'On Hold') $badge = 'warning';
                            ?>
                            <span class="label label-<?php echo $badge; ?>" style="padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?php echo $o->status; ?></span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                            <button class="btn btn-default btn-icon" onclick='xhrOpenModal(<?php echo json_encode($o); ?>)'>
                                <i class="fa fa-pencil"></i>
                            </button>
                            <a href="<?php echo admin_url('xetuu_hr/recruitment/job_openings/delete/' . $o->id); ?>" class="_delete btn btn-danger btn-icon">
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

<!-- Modal Form -->
<div id="xhr-setup-modal" class="modal fade" role="dialog" style="display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog" style="width: 550px; margin: 0;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form action="<?php echo admin_url('xetuu_hr/recruitment/job_openings'); ?>" method="post">
                <input type="hidden" name="id" id="mf_id">
                
                <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                    <button type="button" class="close" onclick="xhrCloseModal()">&times;</button>
                    <h4 class="modal-title" id="xhr-modal-title" style="font-weight: 700; color: #111827;">Add Job Opening</h4>
                </div>
                
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px; max-height: 450px; overflow-y: auto;">
                    <div>
                        <label for="mf_title" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Job Title *</label>
                        <input type="text" name="title" id="mf_title" class="form-control" required style="border-radius: 6px;">
                    </div>

                    <div>
                        <label for="mf_company_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Company *</label>
                        <select name="company_id" id="mf_company_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Company —</option>
                            <?php foreach ($companies as $c): ?>
                            <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
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
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_no_of_positions" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">No of Positions *</label>
                            <input type="number" name="no_of_positions" id="mf_no_of_positions" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div>
                            <label for="mf_expected_salary" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Expected Salary Range</label>
                            <input type="number" step="0.01" name="expected_salary" id="mf_expected_salary" class="form-control" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_close_date" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Close Date</label>
                            <input type="date" name="close_date" id="mf_close_date" class="form-control" style="border-radius: 6px;">
                        </div>
                        <div>
                            <label for="mf_status" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Opening Status *</label>
                            <select name="status" id="mf_status" class="form-control" required style="border-radius: 6px;">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="mf_description" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Job Description</label>
                        <textarea name="description" id="mf_description" class="form-control" rows="4" style="border-radius: 6px; resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                    <button type="button" class="btn btn-default" onclick="xhrCloseModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">Save Opening</button>
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
    document.getElementById('mf_title').value = '';
    document.getElementById('mf_company_id').value = '';
    document.getElementById('mf_department_id').value = '';
    document.getElementById('mf_designation_id').value = '';
    document.getElementById('mf_no_of_positions').value = '1';
    document.getElementById('mf_expected_salary').value = '';
    document.getElementById('mf_close_date').value = '';
    document.getElementById('mf_status').value = 'Open';
    document.getElementById('mf_description').value = '';

    if (row) {
        title.textContent = 'Edit Job Opening';
        document.getElementById('mf_id').value = row.id;
        document.getElementById('mf_title').value = row.title;
        document.getElementById('mf_company_id').value = row.company_id;
        document.getElementById('mf_department_id').value = row.department_id;
        document.getElementById('mf_designation_id').value = row.designation_id;
        document.getElementById('mf_no_of_positions').value = row.no_of_positions;
        document.getElementById('mf_expected_salary').value = row.expected_salary;
        document.getElementById('mf_close_date').value = row.close_date;
        document.getElementById('mf_status').value = row.status;
        document.getElementById('mf_description').value = row.description;
    } else {
        title.textContent = 'Add Job Opening';
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
