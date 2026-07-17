<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'recruitment'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header" style="display: flex; justify-content: space-between; padding: 1.5rem 2rem; background: transparent; border-bottom: none; align-items: center;">
    <div>
        <h2 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">Interviews Schedule</h2>
        <p style="margin: 0; color: #64748b; font-size: 14px;">Plan candidate evaluations and record assessment feedback.</p>
    </div>
    <button class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
        <span class="material-symbols-outlined">add</span>
        Schedule Interview
    </button>
</div>

<!-- Page Content -->
<div class="xhr-content" style="padding: 0 2rem 2rem 2rem;">
    <div class="xhr-card" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr style="background-color: #f8fafc;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Applicant</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Type / Round</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Interviewer</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Date & Time</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Status</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Result</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($interviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #94a3b8; font-size: 14px;">No interviews scheduled.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($interviews as $i): ?>
                    <tr>
                        <td style="padding: 14px 16px; font-size: 14px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($i->applicant_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <span style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($i->interview_type); ?></span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;"><?php echo htmlspecialchars($i->interview_round); ?></div>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($i->interviewer_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo _dt($i->interview_date); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php
                            $badge = 'default';
                            if ($i->status === 'Scheduled') $badge = 'info';
                            if ($i->status === 'Completed') $badge = 'success';
                            if ($i->status === 'Cancelled') $badge = 'danger';
                            ?>
                            <span class="label label-<?php echo $badge; ?>" style="padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?php echo $i->status; ?></span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php
                            $badge = 'default';
                            if ($i->result === 'Pass')    $badge = 'success';
                            if ($i->result === 'Fail')    $badge = 'danger';
                            if ($i->result === 'Pending') $badge = 'warning';
                            ?>
                            <span class="label label-<?php echo $badge; ?>" style="padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?php echo $i->result; ?></span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                            <button class="btn btn-default btn-icon" onclick='xhrOpenModal(<?php echo json_encode($i); ?>)'>
                                <i class="fa fa-pencil"></i>
                            </button>
                            <a href="<?php echo admin_url('xetuu_hr/recruitment/interviews/delete/' . $i->id); ?>" class="_delete btn btn-danger btn-icon">
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
            <form action="<?php echo admin_url('xetuu_hr/recruitment/interviews'); ?>" method="post">
                <input type="hidden" name="id" id="mf_id">
                
                <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                    <button type="button" class="close" onclick="xhrCloseModal()">&times;</button>
                    <h4 class="modal-title" id="xhr-modal-title" style="font-weight: 700; color: #111827;">Schedule Interview</h4>
                </div>
                
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px; max-height: 450px; overflow-y: auto;">
                    <div>
                        <label for="mf_applicant_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Applicant *</label>
                        <select name="applicant_id" id="mf_applicant_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Applicant —</option>
                            <?php foreach ($applicants as $a): ?>
                            <option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->first_name . ' ' . $a->last_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_interview_type_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Interview Type *</label>
                            <select name="interview_type_id" id="mf_interview_type_id" class="form-control" required style="border-radius: 6px;">
                                <option value="">— Type —</option>
                                <?php foreach ($interview_types as $t): ?>
                                <option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="mf_interview_round_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Interview Round *</label>
                            <select name="interview_round_id" id="mf_interview_round_id" class="form-control" required style="border-radius: 6px;">
                                <option value="">— Round —</option>
                                <?php foreach ($interview_rounds as $r): ?>
                                <option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="mf_interviewer_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Interviewer (Staff member) *</label>
                        <select name="interviewer_id" id="mf_interviewer_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Staff —</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['staffid']; ?>"><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_interview_date" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Date & Time *</label>
                            <input type="datetime-local" name="interview_date" id="mf_interview_date" class="form-control" required style="border-radius: 6px;">
                        </div>
                        <div>
                            <label for="mf_status" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Status *</label>
                            <select name="status" id="mf_status" class="form-control" required style="border-radius: 6px;">
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="mf_result" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Assessment Result</label>
                        <select name="result" id="mf_result" class="form-control" style="border-radius: 6px;">
                            <option value="Pending">Pending</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                        </select>
                    </div>

                    <div>
                        <label for="mf_comments" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Feedback Comments</label>
                        <textarea name="comments" id="mf_comments" class="form-control" rows="3" style="border-radius: 6px; resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                    <button type="button" class="btn btn-default" onclick="xhrCloseModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">Save Schedule</button>
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
    document.getElementById('mf_applicant_id').value = '';
    document.getElementById('mf_interview_type_id').value = '';
    document.getElementById('mf_interview_round_id').value = '';
    document.getElementById('mf_interviewer_id').value = '';
    document.getElementById('mf_interview_date').value = '';
    document.getElementById('mf_status').value = 'Scheduled';
    document.getElementById('mf_result').value = 'Pending';
    document.getElementById('mf_comments').value = '';

    if (row) {
        title.textContent = 'Edit Interview Details';
        document.getElementById('mf_id').value = row.id;
        document.getElementById('mf_applicant_id').value = row.applicant_id;
        document.getElementById('mf_interview_type_id').value = row.interview_type_id;
        document.getElementById('mf_interview_round_id').value = row.interview_round_id;
        document.getElementById('mf_interviewer_id').value = row.interviewer_id;
        
        // Format datetime-local format (YYYY-MM-DDTHH:MM)
        if (row.interview_date) {
            var d = new Date(row.interview_date);
            var year = d.getFullYear();
            var month = ('0' + (d.getMonth() + 1)).slice(-2);
            var day = ('0' + d.getDate()).slice(-2);
            var hours = ('0' + d.getHours()).slice(-2);
            var minutes = ('0' + d.getMinutes()).slice(-2);
            document.getElementById('mf_interview_date').value = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
        }
        
        document.getElementById('mf_status').value = row.status;
        document.getElementById('mf_result').value = row.result;
        document.getElementById('mf_comments').value = row.comments;
    } else {
        title.textContent = 'Schedule Interview';
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
