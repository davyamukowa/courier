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
                <span style="color:#111827; font-weight:500;">Job Applicant</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Job Applicants</h1>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0 0;">Manage candidate pipeline and application records.</p>
        </div>
        <a href="<?php echo $base . '/recruitment/applicants/add'; ?>" class="btn btn-success"
           style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-plus"></i> New Applicant
        </a>
    </div>

    <form id="bulk-actions-form" method="POST" action="<?php echo $base . '/recruitment/applicants'; ?>">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
            <div class="panel-body" style="padding:0;">
                <!-- Bulk Actions Bar -->
                <div id="bulk_actions" style="display:none; padding:16px; background-color:#f8fafc; border-bottom:1px solid #e5e7eb; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:13px; font-weight:600; color:#4b5563;" id="selected_count">0 selected</span>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <select name="bulk_stage" class="form-control" style="width:240px; display:inline-block; height:36px; padding:6px 12px; border-radius:6px;" required>
                                <option value="">-- Select Stage to Update --</option>
                                <optgroup label="Default Stages">
                                    <option value="Applied">Applied</option>
                                    <option value="Screening">Screening</option>
                                    <option value="Interview">Interview</option>
                                    <option value="Offer">Offer</option>
                                    <option value="Hired">Hired</option>
                                    <option value="Rejected">Rejected</option>
                                </optgroup>
                                <?php if (!empty($interview_types)): ?>
                                <optgroup label="Interview Types">
                                    <?php foreach ($interview_types as $it): ?>
                                        <option value="<?php echo htmlspecialchars($it->name); ?>"><?php echo htmlspecialchars($it->name); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                            <button type="submit" name="bulk_action" value="update_stage" class="btn btn-success" style="background-color:#16a34a; border-color:#16a34a; font-weight:600; padding:8px 16px; border-radius:6px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                                Update Selected
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom:0;">
                        <thead>
                            <tr style="background-color:#f9fafb;">
                                <th style="padding:12px 16px; border-bottom:1px solid #e5e7eb; width:40px; text-align:center;">
                                    <input type="checkbox" id="mass_select_all" onchange="toggleSelectAll(this)" style="margin:0; cursor:pointer;">
                                </th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applicant #</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Name</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Contact</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Job Opening</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Source</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Stage</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Applied</th>
                                <th style="padding:12px 16px; font-size:12px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php if (empty($applicants)): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="padding:40px; color:#9ca3af; font-size:14px;">
                                No applicants yet. <a href="<?php echo $base . '/recruitment/applicants/add'; ?>" style="color:#16a34a;">Add one</a>.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($applicants as $a): ?>
                        <?php
                        $stage_colors = [
                            'Applied'   => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
                            'Screening' => ['bg' => '#fef9c3', 'color' => '#854d0e'],
                            'Interview' => ['bg' => '#fff7ed', 'color' => '#c2410c'],
                            'Offer'     => ['bg' => '#f0fdf4', 'color' => '#15803d'],
                            'Hired'     => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                            'Rejected'  => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                        ];
                        $sc = $stage_colors[$a->stage] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
                        ?>
                        <tr>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:center;">
                                <input type="checkbox" name="ids[]" value="<?php echo $a->id; ?>" class="applicant_checkbox" onchange="updateBulkBar()" style="margin:0; cursor:pointer;">
                            </td>
                            <td style="padding:12px 16px; font-size:12px; color:#6b7280; font-family:monospace; border-bottom:1px solid #f3f4f6;">
                                <?php echo $a->applicant_number ? htmlspecialchars($a->applicant_number) : '—'; ?>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:1px solid #f3f4f6;">
                                <a href="<?php echo $base . '/recruitment/applicants/edit/' . $a->id; ?>" style="color:#16a34a; text-decoration:none;">
                                    <?php echo htmlspecialchars($a->first_name . ' ' . $a->last_name); ?>
                                </a>
                            </td>
                            <td style="padding:12px 16px; font-size:12px; color:#4b5563; border-bottom:1px solid #f3f4f6;">
                                <div><?php echo htmlspecialchars($a->email ?? '—'); ?></div>
                                <?php if ($a->phone): ?><div style="color:#6b7280;"><?php echo htmlspecialchars($a->phone); ?></div><?php endif; ?>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6;">
                                <?php echo $a->opening_title ? htmlspecialchars($a->opening_title) : '<span style="color:#9ca3af;">—</span>'; ?>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#4b5563; border-bottom:1px solid #f3f4f6;">
                                <?php echo $a->source ? htmlspecialchars($a->source) : '<span style="color:#9ca3af;">—</span>'; ?>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                                    <?php echo htmlspecialchars($a->stage); ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px; font-size:12px; color:#6b7280; border-bottom:1px solid #f3f4f6;">
                                <?php echo _d($a->date_created); ?>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #f3f4f6; text-align:right;">
                                <a href="<?php echo $base . '/recruitment/applicants/edit/' . $a->id; ?>" class="btn btn-default btn-icon" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?php echo $base . '/recruitment/applicants/delete/' . $a->id; ?>" class="_delete btn btn-danger btn-icon" title="Delete">
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
    </form>

</div>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<script>
function toggleSelectAll(master) {
    document.querySelectorAll('.applicant_checkbox').forEach(function(cb) {
        cb.checked = master.checked;
    });
    updateBulkBar();
}

function updateBulkBar() {
    var checked = document.querySelectorAll('.applicant_checkbox:checked');
    var bar = document.getElementById('bulk_actions');
    var countText = document.getElementById('selected_count');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        countText.innerText = checked.length + ' applicant(s) selected';
    } else {
        bar.style.display = 'none';
        var master = document.getElementById('mass_select_all');
        if (master) {
            master.checked = false;
        }
    }
}
</script>

<?php init_tail(); ?>
