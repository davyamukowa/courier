<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'recruitment'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header" style="display: flex; justify-content: space-between; padding: 1.5rem 2rem; background: transparent; border-bottom: none; align-items: center;">
    <div>
        <h2 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">Job Applicants</h2>
        <p style="margin: 0; color: #64748b; font-size: 14px;">Manage candidate pipeline and application records.</p>
    </div>
    <button class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
        <span class="material-symbols-outlined">add</span>
        Add Applicant
    </button>
</div>

<!-- Page Content -->
<div class="xhr-content" style="padding: 0 2rem 2rem 2rem;">
    <div class="xhr-card" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr style="background-color: #f8fafc;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Name</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Contact</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Position</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Stage</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">CV/Resume</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applicants)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #94a3b8; font-size: 14px;">No applicant records found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($applicants as $a): ?>
                    <tr>
                        <td style="padding: 14px 16px; font-size: 14px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($a->first_name . ' ' . $a->last_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <div><i class="fa fa-envelope-o" style="width: 16px;"></i> <?php echo htmlspecialchars($a->email); ?></div>
                            <div style="margin-top: 2px;"><i class="fa fa-phone" style="width: 16px;"></i> <?php echo htmlspecialchars($a->phone); ?></div>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php 
                            $job_title = 'Direct Sourced / Unlinked';
                            foreach ($openings as $o) {
                                if ($o->id == $a->job_opening_id) {
                                    $job_title = $o->title;
                                    break;
                                }
                            }
                            echo htmlspecialchars($job_title);
                            ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php
                            $badge = 'default';
                            if ($a->stage === 'Applied')   $badge = 'info';
                            if ($a->stage === 'Screening') $badge = 'primary';
                            if ($a->stage === 'Interview') $badge = 'warning';
                            if ($a->stage === 'Offer')     $badge = 'success';
                            if ($a->stage === 'Hired')     $badge = 'success';
                            if ($a->stage === 'Rejected')  $badge = 'danger';
                            ?>
                            <span class="label label-<?php echo $badge; ?>" style="padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?php echo $a->stage; ?></span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php if ($a->resume): ?>
                            <a href="<?php echo base_url('uploads/xetuu_hr/resumes/' . $a->resume); ?>" target="_blank" class="btn btn-default btn-xs" style="border-radius: 4px;">
                                <i class="fa fa-download"></i> Download CV
                            </a>
                            <?php else: ?>
                            <span class="text-muted" style="font-size: 11px;">No file</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                            <button class="btn btn-default btn-icon" onclick='xhrOpenModal(<?php echo json_encode($a); ?>)'>
                                <i class="fa fa-pencil"></i>
                            </button>
                            <a href="<?php echo admin_url('xetuu_hr/recruitment/applicants/delete/' . $a->id); ?>" class="_delete btn btn-danger btn-icon">
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
    <div class="modal-dialog" style="width: 500px; margin: 0;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form action="<?php echo admin_url('xetuu_hr/recruitment/applicants'); ?>" method="post">
                <input type="hidden" name="id" id="mf_id">
                
                <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                    <button type="button" class="close" onclick="xhrCloseModal()">&times;</button>
                    <h4 class="modal-title" id="xhr-modal-title" style="font-weight: 700; color: #111827;">Add Applicant</h4>
                </div>
                
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_first_name" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">First Name *</label>
                            <input type="text" name="first_name" id="mf_first_name" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div>
                            <label for="mf_last_name" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Last Name *</label>
                            <input type="text" name="last_name" id="mf_last_name" class="form-control" required style="border-radius: 6px;">
                        </div>
                    </div>

                    <div>
                        <label for="mf_email" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Email Address *</label>
                        <input type="email" name="email" id="mf_email" class="form-control" required style="border-radius: 6px;">
                    </div>

                    <div>
                        <label for="mf_phone" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Phone Number *</label>
                        <input type="text" name="phone" id="mf_phone" class="form-control" required style="border-radius: 6px;">
                    </div>

                    <div>
                        <label for="mf_job_opening_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Job Opening *</label>
                        <select name="job_opening_id" id="mf_job_opening_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Job Opening —</option>
                            <?php foreach ($openings as $o): ?>
                            <option value="<?php echo $o->id; ?>"><?php echo htmlspecialchars($o->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_stage" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Application Stage *</label>
                        <select name="stage" id="mf_stage" class="form-control" required style="border-radius: 6px;">
                            <option value="Applied">Applied</option>
                            <option value="Screening">Screening</option>
                            <option value="Interview">Interview</option>
                            <option value="Offer">Offer</option>
                            <option value="Hired">Hired</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                    <button type="button" class="btn btn-default" onclick="xhrCloseModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">Save Applicant</button>
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
    document.getElementById('mf_first_name').value = '';
    document.getElementById('mf_last_name').value = '';
    document.getElementById('mf_email').value = '';
    document.getElementById('mf_phone').value = '';
    document.getElementById('mf_job_opening_id').value = '';
    document.getElementById('mf_stage').value = 'Applied';

    if (row) {
        title.textContent = 'Edit Applicant';
        document.getElementById('mf_id').value = row.id;
        document.getElementById('mf_first_name').value = row.first_name;
        document.getElementById('mf_last_name').value = row.last_name;
        document.getElementById('mf_email').value = row.email;
        document.getElementById('mf_phone').value = row.phone;
        document.getElementById('mf_job_opening_id').value = row.job_opening_id;
        document.getElementById('mf_stage').value = row.stage;
    } else {
        title.textContent = 'Add Applicant';
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
