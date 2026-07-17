<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'setup';
$base        = admin_url('xetuu_hr');
$is_edit     = isset($plan) && $plan;
$p           = $plan;

$base_currency   = get_base_currency();
$currency_symbol = $base_currency ? $base_currency->symbol : 'KES';

// Employee counts per designation (active employees)
$CI =& get_instance();
$designation_counts = [];
$counts_query = $CI->db->select('designation_id, COUNT(*) AS cnt')
    ->where('active', 1)
    ->group_by('designation_id')
    ->get(db_prefix() . 'hr_employees')->result();
foreach ($counts_query as $row) {
    $designation_counts[$row->designation_id] = (int)$row->cnt;
}
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding: 24px;">

<form id="xhr-staffing-plan-form" action="<?php echo $base . '/setup/staffing_plan'; ?>" method="post">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" value="<?php echo $is_edit ? $p->id : ''; ?>">

    <!-- Page Header (full width) -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <div style="font-size: 12px; margin-bottom: 5px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                <a href="<?php echo $base; ?>" style="color: #6b7280; text-decoration: none;">Recruitment</a>
                <span>/</span>
                <a href="<?php echo $base . '/setup/staffing_plan'; ?>" style="color: #6b7280; text-decoration: none;">Staffing Plan</a>
                <span>/</span>
                <span style="color: #111827; font-weight: 500;"><?php echo $is_edit ? htmlspecialchars($p->name) : 'New Staffing Plan'; ?></span>
                <span class="label" style="background-color: <?php echo $is_edit ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $is_edit ? '#16a34a' : '#ef4444'; ?>; font-weight: 600; padding: 4px 8px; border-radius: 4px; margin-left: 8px; font-size: 11px;">
                    <?php echo $is_edit ? 'Saved' : 'Not Saved'; ?>
                </span>
            </div>
            <h1 style="font-size: 22px; font-weight: 700; color: #111827; margin: 0;">
                <?php echo $is_edit ? 'Edit Staffing Plan' : 'New Staffing Plan'; ?>
            </h1>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?php echo $base . '/setup/staffing_plan'; ?>" class="btn btn-default" style="font-weight: 600; padding: 8px 16px; border-radius: 6px;">Cancel</a>
            <button type="submit" class="btn btn-success" style="background-color: #16a34a; border-color: #16a34a; font-weight: 600; padding: 8px 16px; border-radius: 6px;">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>

    <!-- Two-column layout: main (9) + sidebar (3) -->
    <div class="row">

        <!-- ── Main Content ─────────────────────────────────────────────── -->
        <div class="col-md-9">

            <!-- Header Fields Card -->
            <div class="panel_s" style="border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                <div class="panel-body" style="padding: 20px;">
                    <div class="row">
                        <div class="col-md-6" style="display:flex; flex-direction:column; gap:14px;">
                            <div>
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Name *</label>
                                <input type="text" name="name" id="plan-name" class="form-control" value="<?php echo $is_edit ? htmlspecialchars($p->name) : ''; ?>" required style="border-radius:6px;" placeholder="e.g. FY 2026-2027 Staffing Plan">
                            </div>
                            <div>
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Company *</label>
                                <select name="company_id" id="company_id" class="form-control" required style="border-radius:6px;">
                                    <option value="">— Select Company —</option>
                                    <?php foreach ($companies as $c): ?>
                                    <option value="<?php echo $c->id; ?>" <?php echo ($is_edit && $p->company_id == $c->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">Department</label>
                                <select name="department_id" id="department_id" class="form-control" style="border-radius:6px;">
                                    <option value="">— Select Department —</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?php echo $d->id; ?>" <?php echo ($is_edit && $p->department_id == $d->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" style="display:flex; flex-direction:column; gap:14px;">
                            <div>
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">From Date *</label>
                                <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $is_edit ? $p->from_date : ''; ?>" required style="border-radius:6px;">
                            </div>
                            <div>
                                <label style="font-weight:500; font-size:13px; color:#374151; display:block; margin-bottom:5px;">To Date *</label>
                                <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $is_edit ? $p->to_date : ''; ?>" required style="border-radius:6px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Section -->
            <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0 0 10px 0;">Details</h3>

            <div style="margin-bottom:16px;">
                <button type="button" id="get-job-requisitions-btn" class="btn btn-default" style="font-weight:600; border-radius:6px; padding:7px 14px;">
                    Get Job Requisitions
                </button>
            </div>

            <!-- Staffing Details Table -->
            <h4 style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px;">Staffing Details</h4>
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:14px;">
                <div class="panel-body" style="padding:0;">
                    <div class="table-responsive">
                        <table class="table" id="staffing-details-table"
                               style="margin-bottom:0; table-layout:fixed; width:100%;">
                            <colgroup>
                                <col style="width:36px;">
                                <col style="width:44px;">
                                <col style="width:180px;"><!-- Designation -->
                                <col style="width:82px;"> <!-- Vacancies -->
                                <col style="width:190px;"><!-- Estimated Cost Per Position -->
                                <col style="width:175px;"><!-- Total Estimated Cost -->
                                <col style="width:115px;"><!-- Number Of Positions -->
                                <col style="width:46px;"> <!-- Action -->
                            </colgroup>
                            <thead>
                                <tr style="background-color:#f9fafb;">
                                    <th style="padding:10px 12px; border-bottom:1px solid #e5e7eb;"><input type="checkbox" id="select-all-rows"></th>
                                    <th style="padding:10px 8px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">No.</th>
                                    <th style="padding:10px 10px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Designation *</th>
                                    <th style="padding:10px 8px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Vacancies</th>
                                    <th style="padding:10px 8px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Estimated Cost Per Position</th>
                                    <th style="padding:10px 8px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">Total Estimated Cost</th>
                                    <th style="padding:10px 8px; font-size:11px; font-weight:600; color:#4b5563; border-bottom:1px solid #e5e7eb;">No. of Positions</th>
                                    <th style="padding:10px 8px; border-bottom:1px solid #e5e7eb; text-align:center;"><i class="fa fa-cog" style="color:#9ca3af;"></i></th>
                                </tr>
                            </thead>
                            <tbody id="staffing-details-body">

                                <!-- Hidden template row -->
                                <tr id="row-template" style="display:none;">
                                    <td style="padding:10px 12px; vertical-align:middle;"><input type="checkbox" class="row-checkbox" disabled></td>
                                    <td class="row-number" style="padding:10px 8px; vertical-align:middle; font-size:13px; color:#4b5563;">0</td>
                                    <td style="padding:10px 10px; vertical-align:middle;">
                                        <select name="designation_id[]" class="form-control designation-select" style="border-radius:6px; font-size:13px;" disabled>
                                            <option value="">— Select Designation —</option>
                                            <?php foreach ($designations as $des): ?>
                                            <option value="<?php echo $des->id; ?>" data-count="<?php echo $designation_counts[$des->id] ?? 0; ?>">
                                                <?php echo htmlspecialchars($des->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="padding:10px 8px; vertical-align:middle;">
                                        <input type="number" name="vacancies[]" class="form-control vacancies-input" value="0" min="0" style="border-radius:6px; background-color:#f9fafb; font-size:13px;" readonly disabled>
                                    </td>
                                    <td style="padding:10px 8px; vertical-align:middle;">
                                        <div class="input-group" style="margin-bottom:0;">
                                            <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                            <input type="number" step="0.01" name="estimated_cost_per_position[]" class="form-control cost-input" value="0.00" min="0" style="font-size:13px;" disabled>
                                        </div>
                                    </td>
                                    <td style="padding:10px 8px; vertical-align:middle;">
                                        <div class="input-group" style="margin-bottom:0;">
                                            <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                            <input type="text" name="total_estimated_cost[]" class="form-control total-cost-input" value="0.00" readonly style="background-color:#f9fafb; font-weight:600; font-size:13px;" disabled>
                                        </div>
                                    </td>
                                    <td style="padding:10px 8px; vertical-align:middle;">
                                        <input type="number" name="number_of_positions[]" class="form-control positions-input" value="1" min="0" style="border-radius:6px; font-size:13px;" disabled>
                                    </td>
                                    <td style="padding:10px 8px; vertical-align:middle; text-align:center;">
                                        <div class="row-action-wrap" style="position:relative; display:inline-block;">
                                            <button type="button" class="btn btn-default btn-icon row-action-btn" style="border-color:#e5e7eb; border-radius:6px; padding:4px 8px;" disabled>
                                                <i class="fa fa-pencil" style="color:#6b7280;"></i>
                                            </button>
                                            <div class="row-action-menu" style="display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.1); min-width:130px; z-index:300;">
                                                <a href="javascript:void(0)" class="remove-row-btn" style="display:block; padding:8px 14px; font-size:13px; color:#ef4444; text-decoration:none; white-space:nowrap;">
                                                    <i class="fa fa-trash"></i> Delete Row
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <?php if ($is_edit && !empty($details)): ?>
                                    <?php foreach ($details as $idx => $d): ?>
                                    <tr>
                                        <td style="padding:10px 12px; vertical-align:middle;"><input type="checkbox" class="row-checkbox"></td>
                                        <td class="row-number" style="padding:10px 8px; vertical-align:middle; font-size:13px; color:#4b5563;"><?php echo $idx + 1; ?></td>
                                        <td style="padding:10px 10px; vertical-align:middle;">
                                            <select name="designation_id[]" class="form-control designation-select" required style="border-radius:6px; font-size:13px;">
                                                <option value="">— Select Designation —</option>
                                                <?php foreach ($designations as $des): ?>
                                                <option value="<?php echo $des->id; ?>" data-count="<?php echo $designation_counts[$des->id] ?? 0; ?>" <?php echo ($d->designation_id == $des->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($des->name); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <input type="number" name="vacancies[]" class="form-control vacancies-input" value="<?php echo $d->vacancies; ?>" min="0" style="border-radius:6px; background-color:#f9fafb; font-size:13px;" readonly>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <div class="input-group" style="margin-bottom:0;">
                                                <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                                <input type="number" step="0.01" name="estimated_cost_per_position[]" class="form-control cost-input" value="<?php echo number_format($d->estimated_cost_per_position, 2, '.', ''); ?>" min="0" style="font-size:13px;">
                                            </div>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <div class="input-group" style="margin-bottom:0;">
                                                <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                                <input type="text" name="total_estimated_cost[]" class="form-control total-cost-input" value="<?php echo number_format($d->total_estimated_cost, 2, '.', ''); ?>" readonly style="background-color:#f9fafb; font-weight:600; font-size:13px;">
                                            </div>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <input type="number" name="number_of_positions[]" class="form-control positions-input" value="<?php echo $d->number_of_positions; ?>" min="0" style="border-radius:6px; font-size:13px;">
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle; text-align:center;">
                                            <div class="row-action-wrap" style="position:relative; display:inline-block;">
                                                <button type="button" class="btn btn-default btn-icon row-action-btn" style="border-color:#e5e7eb; border-radius:6px; padding:4px 8px;">
                                                    <i class="fa fa-pencil" style="color:#6b7280;"></i>
                                                </button>
                                                <div class="row-action-menu" style="display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.1); min-width:130px; z-index:300;">
                                                    <a href="javascript:void(0)" class="remove-row-btn" style="display:block; padding:8px 14px; font-size:13px; color:#ef4444; text-decoration:none; white-space:nowrap;">
                                                        <i class="fa fa-trash"></i> Delete Row
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Default empty row (positions defaults to 1 so budget auto-computes) -->
                                    <tr class="default-empty-row">
                                        <td style="padding:10px 12px; vertical-align:middle;"><input type="checkbox" class="row-checkbox"></td>
                                        <td class="row-number" style="padding:10px 8px; vertical-align:middle; font-size:13px; color:#4b5563;">1</td>
                                        <td style="padding:10px 10px; vertical-align:middle;">
                                            <select name="designation_id[]" class="form-control designation-select" required style="border-radius:6px; font-size:13px;">
                                                <option value="">— Select Designation —</option>
                                                <?php foreach ($designations as $des): ?>
                                                <option value="<?php echo $des->id; ?>" data-count="<?php echo $designation_counts[$des->id] ?? 0; ?>">
                                                    <?php echo htmlspecialchars($des->name); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <input type="number" name="vacancies[]" class="form-control vacancies-input" value="1" min="0" style="border-radius:6px; background-color:#f9fafb; font-size:13px;" readonly>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <div class="input-group" style="margin-bottom:0;">
                                                <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                                <input type="number" step="0.01" name="estimated_cost_per_position[]" class="form-control cost-input" value="0.00" min="0" style="font-size:13px;">
                                            </div>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <div class="input-group" style="margin-bottom:0;">
                                                <span class="input-group-addon" style="padding:6px 8px; font-size:12px;"><?php echo $currency_symbol; ?></span>
                                                <input type="text" name="total_estimated_cost[]" class="form-control total-cost-input" value="0.00" readonly style="background-color:#f9fafb; font-weight:600; font-size:13px;">
                                            </div>
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle;">
                                            <input type="number" name="number_of_positions[]" class="form-control positions-input" value="1" min="0" style="border-radius:6px; font-size:13px;">
                                        </td>
                                        <td style="padding:10px 8px; vertical-align:middle; text-align:center;">
                                            <div class="row-action-wrap" style="position:relative; display:inline-block;">
                                                <button type="button" class="btn btn-default btn-icon row-action-btn" style="border-color:#e5e7eb; border-radius:6px; padding:4px 8px;">
                                                    <i class="fa fa-pencil" style="color:#6b7280;"></i>
                                                </button>
                                                <div class="row-action-menu" style="display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.1); min-width:130px; z-index:300;">
                                                    <a href="javascript:void(0)" class="remove-row-btn" style="display:block; padding:8px 14px; font-size:13px; color:#ef4444; text-decoration:none; white-space:nowrap;">
                                                        <i class="fa fa-trash"></i> Delete Row
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table action buttons -->
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                <button type="button" id="add-row-btn" class="btn btn-default" style="font-weight:600; border-radius:6px; padding:7px 14px;">
                    Add row
                </button>
                <button type="button" id="delete-selected-rows-btn" class="btn btn-danger" style="border-radius:6px; padding:7px 14px; display:none;">
                    <i class="fa fa-trash"></i> Delete Selected
                </button>
            </div>

            <!-- Total Estimated Budget (bottom of main col) -->
            <div style="display:flex; justify-content:flex-end;">
                <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; min-width:260px;">
                    <div class="panel-body" style="padding:16px;">
                        <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:6px;">Total Estimated Budget</label>
                        <div class="input-group" style="margin-bottom:0;">
                            <span class="input-group-addon" style="border-radius:6px 0 0 6px; font-weight:600;"><?php echo $currency_symbol; ?></span>
                            <input type="text" name="total_estimated_budget" id="total_estimated_budget_input" class="form-control"
                                   value="<?php echo $is_edit ? number_format($p->total_estimated_budget, 2, '.', '') : '0.00'; ?>"
                                   readonly style="border-radius:0 6px 6px 0; background-color:#f9fafb; font-weight:700; color:#16a34a; font-size:16px;">
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-md-9 -->

        <!-- ── Right Sidebar ─────────────────────────────────────────────── -->
        <div class="col-md-3">

            <!-- Document Summary -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px; margin-bottom:16px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 12px 0;">Document Summary</p>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Status</span>
                        <span id="sidebar-status" style="font-size:12px; font-weight:600; color:<?php echo $is_edit ? '#16a34a' : '#ef4444'; ?>;">
                            <?php echo $is_edit ? 'Saved' : 'Not Saved'; ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">Total Budget</span>
                        <span id="sidebar-budget" style="font-size:13px; font-weight:700; color:#16a34a;">
                            <?php echo $currency_symbol; ?> <span id="sidebar-budget-value"><?php echo $is_edit ? number_format($p->total_estimated_budget, 2) : '0.00'; ?></span>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:12px; color:#6b7280;">From</span>
                        <span id="sidebar-from" style="font-size:12px; color:#374151; font-weight:500;">
                            <?php echo ($is_edit && $p->from_date) ? date('M d, Y', strtotime($p->from_date)) : '—'; ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">
                        <span style="font-size:12px; color:#6b7280;">To</span>
                        <span id="sidebar-to" style="font-size:12px; color:#374151; font-weight:500;">
                            <?php echo ($is_edit && $p->to_date) ? date('M d, Y', strtotime($p->to_date)) : '—'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Recruitment Shortcuts -->
            <div class="panel_s" style="border:1px solid #e5e7eb; border-radius:8px;">
                <div class="panel-body" style="padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 10px 0;">Recruitment</p>

                    <?php
                    $shortcuts = [
                        ['url' => $base . '/setup/staffing_plan',     'icon' => 'fact_check',    'label' => 'Staffing Plan',          'active' => true],
                        ['url' => $base . '/setup/job_requisition',   'icon' => 'assignment',    'label' => 'Job Requisition',        'active' => false],
                        ['url' => $base . '/recruitment/job_openings','icon' => 'work',          'label' => 'Job Opening',            'active' => false],
                        ['url' => $base . '/recruitment/applicants',  'icon' => 'person_search', 'label' => 'Job Applicant',          'active' => false],
                        ['url' => $base . '/recruitment/interviews',  'icon' => 'event_note',    'label' => 'Interview',              'active' => false],
                        ['url' => $base . '/recruitment/offers',      'icon' => 'description',   'label' => 'Job Offer',              'active' => false],
                        ['url' => $base . '/recruitment/appointment_letters', 'icon' => 'mark_email_read', 'label' => 'Appointment Letter', 'active' => false],
                    ];
                    foreach ($shortcuts as $s):
                    ?>
                    <a href="<?php echo $s['url']; ?>"
                       style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:6px; text-decoration:none; margin-bottom:2px;
                              <?php echo $s['active'] ? 'background:#f0faf4; color:#16a34a;' : 'color:#4b5563;'; ?>">
                        <span class="material-symbols-outlined" style="font-size:18px; color:<?php echo $s['active'] ? '#16a34a' : '#6b7280'; ?>;"><?php echo $s['icon']; ?></span>
                        <span style="font-size:13px; font-weight:<?php echo $s['active'] ? '600' : '500'; ?>;"><?php echo $s['label']; ?></span>
                    </a>
                    <?php endforeach; ?>

                    <div style="margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6;">
                        <p style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; letter-spacing:.06em; margin:0 0 8px 0;">Setup</p>
                        <?php
                        $setup_links = [
                            ['url' => $base . '/setup/interview_type',              'icon' => 'category',      'label' => 'Interview Type'],
                            ['url' => $base . '/setup/interview_round',             'icon' => 'sync',          'label' => 'Interview Round'],
                            ['url' => $base . '/setup/appointment_letter_template', 'icon' => 'receipt_long',  'label' => 'Letter Template'],
                            ['url' => $base . '/setup/recruitment_settings',        'icon' => 'settings',      'label' => 'Settings'],
                        ];
                        foreach ($setup_links as $sl):
                        ?>
                        <a href="<?php echo $sl['url']; ?>"
                           style="display:flex; align-items:center; gap:10px; padding:7px 10px; border-radius:6px; text-decoration:none; color:#4b5563; margin-bottom:2px;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:#9ca3af;"><?php echo $sl['icon']; ?></span>
                            <span style="font-size:12px; font-weight:500;"><?php echo $sl['label']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div><!-- /col-md-3 sidebar -->

    </div><!-- /row -->

</form>
</div><!-- /.xhr-setup-page -->

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script>
$(function () {

    // ── Add Row ───────────────────────────────────────────────────────────────
    $('#add-row-btn').on('click', function () { addRow(); });

    // ── Pencil dropdown ───────────────────────────────────────────────────────
    $(document).on('click', '.row-action-btn', function (e) {
        e.stopPropagation();
        var menu = $(this).siblings('.row-action-menu');
        $('.row-action-menu').not(menu).hide();
        menu.toggle();
    });
    $(document).on('click', function () { $('.row-action-menu').hide(); });

    // ── Remove Row ────────────────────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function () {
        $(this).closest('tr').remove();
        reindexRows();
    });

    // ── Checkbox select-all ───────────────────────────────────────────────────
    $('#select-all-rows').on('change', function () {
        var checked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', checked).trigger('change');
    });
    $(document).on('change', '.row-checkbox', function () {
        $('#delete-selected-rows-btn').toggle($('.row-checkbox:checked').length > 0);
    });
    $('#delete-selected-rows-btn').on('click', function () {
        $('.row-checkbox:checked').closest('tr').not('#row-template').remove();
        $('#select-all-rows').prop('checked', false);
        $(this).hide();
        reindexRows();
    });

    // ── Designation → vacancies ───────────────────────────────────────────────
    $(document).on('change', '.designation-select', function () {
        recalcRow($(this).closest('tr'));
    });

    // ── Positions / Cost → total ──────────────────────────────────────────────
    $(document).on('input change keyup', '.positions-input, .cost-input', function () {
        recalcRow($(this).closest('tr'));
    });

    // ── Sidebar date preview ──────────────────────────────────────────────────
    $('#from_date').on('change', function () {
        var v = $(this).val();
        $('#sidebar-from').text(v ? formatDate(v) : '—');
    });
    $('#to_date').on('change', function () {
        var v = $(this).val();
        $('#sidebar-to').text(v ? formatDate(v) : '—');
    });

    // ── Get Job Requisitions ──────────────────────────────────────────────────
    $('#get-job-requisitions-btn').on('click', function () {
        var deptId = $('#department_id').val();
        if (!deptId) { alert('Please select a Department first.'); return; }
        $.ajax({
            url: '<?php echo admin_url("xetuu_hr/get_job_requisitions_json"); ?>/' + deptId,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data && data.length > 0) {
                    $('#staffing-details-body tr').not('#row-template').remove();
                    data.forEach(function (item) {
                        var r = addRow();
                        r.find('.designation-select').val(item.designation_id).trigger('change');
                        r.find('.positions-input').val(item.number_of_positions);
                        r.find('.cost-input').val(parseFloat(item.estimated_cost_per_position).toFixed(2));
                        recalcRow(r);
                    });
                    reindexRows();
                } else {
                    alert('No approved pending job requisitions found for this department.');
                }
            },
            error: function () { alert('Error fetching job requisitions.'); }
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function recalcRow(row) {
        var option    = row.find('.designation-select option:selected');
        var count     = parseInt(option.data('count')) || 0;
        var positions = parseInt(row.find('.positions-input').val()) || 0;
        var cost      = parseFloat(row.find('.cost-input').val()) || 0;
        var vacancies = Math.max(0, positions - count);
        row.find('.vacancies-input').val(vacancies);
        row.find('.total-cost-input').val((cost * positions).toFixed(2));
        calculateBudget();
    }

    function addRow() {
        var tpl = $('#row-template').clone();
        tpl.removeAttr('id');
        tpl.find('input, select, button').removeAttr('disabled');
        tpl.find('.designation-select').attr('required', true);
        tpl.find('.positions-input').val(1);
        tpl.find('.vacancies-input').val(1);
        tpl.show();
        $('#staffing-details-body').append(tpl);
        reindexRows();
        return tpl;
    }

    function reindexRows() {
        var i = 1;
        $('#staffing-details-body tr:visible').each(function () {
            $(this).find('.row-number').text(i++);
        });
        calculateBudget();
    }

    function calculateBudget() {
        var total = 0;
        $('#staffing-details-body tr:visible').each(function () {
            total += parseFloat($(this).find('.total-cost-input').val()) || 0;
        });
        var fmt = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#total_estimated_budget_input').val(total.toFixed(2));
        $('#sidebar-budget-value').text(fmt);
    }

    function formatDate(dateStr) {
        var d = new Date(dateStr);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[d.getMonth()] + ' ' + String(d.getDate()).padStart(2,'0') + ', ' + d.getFullYear();
    }

    // Initial calculation
    calculateBudget();
});
</script>
