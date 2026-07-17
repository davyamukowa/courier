<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'recruitment'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header" style="display: flex; justify-content: space-between; padding: 1.5rem 2rem; background: transparent; border-bottom: none; align-items: center;">
    <div>
        <h2 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">Appointment Letters</h2>
        <p style="margin: 0; color: #64748b; font-size: 14px;">Generate contracts and official hiring documentation from templates.</p>
    </div>
    <button class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
        <span class="material-symbols-outlined">add</span>
        Generate Appointment Letter
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
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Template Used</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Generated On</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Signed Date</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Status</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($letters)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #94a3b8; font-size: 14px;">No appointment letters generated.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($letters as $l): ?>
                    <tr>
                        <td style="padding: 14px 16px; font-size: 14px; font-weight: 600; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($l->applicant_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php echo htmlspecialchars($l->template_name); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #64748b; border-bottom: 1px solid #f1f5f9;">
                            <?php echo _dt($l->date_created); ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9;">
                            <?php echo $l->signed_date ? _d($l->signed_date) : 'Pending Signature'; ?>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                            <?php
                            $badge = 'default';
                            if ($l->status === 'Draft')  $badge = 'info';
                            if ($l->status === 'Sent')   $badge = 'primary';
                            if ($l->status === 'Signed') $badge = 'success';
                            ?>
                            <span class="label label-<?php echo $badge; ?>" style="padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?php echo $l->status; ?></span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                            <button class="btn btn-default btn-xs" style="margin-right: 5px; border-radius: 4px;" onclick='xhrPreviewLetter(<?php echo json_encode($l->letter_content); ?>)'>
                                <i class="fa fa-eye"></i> View Letter
                            </button>
                            <button class="btn btn-default btn-icon" onclick='xhrOpenModal(<?php echo json_encode($l); ?>)'>
                                <i class="fa fa-pencil"></i>
                            </button>
                            <a href="<?php echo admin_url('xetuu_hr/recruitment/appointment_letters/delete/' . $l->id); ?>" class="_delete btn btn-danger btn-icon">
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
            <form action="<?php echo admin_url('xetuu_hr/recruitment/appointment_letters'); ?>" method="post">
                <input type="hidden" name="id" id="mf_id">
                
                <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                    <button type="button" class="close" onclick="xhrCloseModal()">&times;</button>
                    <h4 class="modal-title" id="xhr-modal-title" style="font-weight: 700; color: #111827;">Generate Appointment Letter</h4>
                </div>
                
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label for="mf_applicant_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Applicant *</label>
                        <select name="applicant_id" id="mf_applicant_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Applicant —</option>
                            <?php foreach ($applicants as $a): ?>
                            <option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->first_name . ' ' . $a->last_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mf_template_id" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Template *</label>
                        <select name="template_id" id="mf_template_id" class="form-control" required style="border-radius: 6px;">
                            <option value="">— Select Template —</option>
                            <?php foreach ($templates as $t): ?>
                            <option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="mf_signed_date" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Signed Date</label>
                            <input type="date" name="signed_date" id="mf_signed_date" class="form-control" style="border-radius: 6px;">
                        </div>
                        <div>
                            <label for="mf_status" style="font-weight: 500; font-size: 13px; color: #374151; margin-bottom: 6px;">Letter Status *</label>
                            <select name="status" id="mf_status" class="form-control" required style="border-radius: 6px;">
                                <option value="Draft">Draft</option>
                                <option value="Sent">Sent</option>
                                <option value="Signed">Signed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                    <button type="button" class="btn btn-default" onclick="xhrCloseModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600;">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div id="xhr-preview-modal" class="modal fade" role="dialog" style="display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog" style="width: 650px; margin: 0;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #f3f4f6; padding: 16px 20px;">
                <button type="button" class="close" onclick="xhrClosePreviewModal()">&times;</button>
                <h4 class="modal-title" style="font-weight: 700; color: #111827;">Appointment Letter Document</h4>
            </div>
            
            <div class="modal-body" id="xhr-letter-body" style="padding: 30px; font-size: 14px; color: #334155; line-height: 1.6; max-height: 500px; overflow-y: auto; background-color: #fff;">
                <!-- HTML Content will load here -->
            </div>

            <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 12px 20px;">
                <button type="button" class="btn btn-default" onclick="xhrClosePreviewModal()">Close</button>
                <button type="button" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a;" onclick="window.print()">Print Document</button>
            </div>
        </div>
    </div>
</div>

<script>
function xhrOpenModal(row) {
    var modal = document.getElementById('xhr-setup-modal');
    var title = document.getElementById('xhr-modal-title');
    
    document.getElementById('mf_id').value = '';
    document.getElementById('mf_applicant_id').value = '';
    document.getElementById('mf_template_id').value = '';
    document.getElementById('mf_signed_date').value = '';
    document.getElementById('mf_status').value = 'Draft';

    if (row) {
        title.textContent = 'Edit Letter Details';
        document.getElementById('mf_id').value = row.id;
        document.getElementById('mf_applicant_id').value = row.applicant_id;
        document.getElementById('mf_template_id').value = row.template_id;
        document.getElementById('mf_signed_date').value = row.signed_date;
        document.getElementById('mf_status').value = row.status;
    } else {
        title.textContent = 'Generate Appointment Letter';
    }

    modal.style.display = 'flex';
    modal.classList.add('in');
}

function xhrCloseModal() {
    var modal = document.getElementById('xhr-setup-modal');
    modal.style.display = 'none';
    modal.classList.remove('in');
}

function xhrPreviewLetter(htmlContent) {
    var modal = document.getElementById('xhr-preview-modal');
    document.getElementById('xhr-letter-body').innerHTML = htmlContent;
    modal.style.display = 'flex';
    modal.classList.add('in');
}

function xhrClosePreviewModal() {
    var modal = document.getElementById('xhr-preview-modal');
    modal.style.display = 'none';
    modal.classList.remove('in');
}
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
