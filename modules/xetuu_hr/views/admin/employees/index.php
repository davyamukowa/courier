<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $xhr_active = 'employees'; ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<!-- Sub-action header -->
<div class="xhr-action-header">
    <div class="xhr-breadcrumb">
        <a href="<?php echo admin_url('xetuu_hr'); ?>" style="color:var(--xhr-secondary);text-decoration:none;"><?php echo _l('xetuu_hr'); ?></a>
        <span class="material-symbols-outlined xhr-breadcrumb__sep">chevron_right</span>
        <span class="xhr-breadcrumb__current"><?php echo _l('xetuu_hr_employees'); ?></span>
    </div>
    <div class="xhr-action-buttons">
        <a href="<?php echo admin_url('xetuu_hr/employees/add'); ?>" class="xhr-btn xhr-btn--primary">
            <span class="material-symbols-outlined">person_add</span>
            <?php echo _l('xetuu_hr_add_employee'); ?>
        </a>
        <button class="xhr-btn xhr-btn--outline" onclick="document.getElementById('xhr-employee-search').focus();">
            <span class="material-symbols-outlined">search</span>
        </button>
    </div>
</div>

<div class="xhr-content">

    <!-- Search / Filter bar -->
    <div class="xhr-card" style="padding:14px var(--xhr-card-padding);margin-bottom:1.5rem;display:flex;gap:12px;align-items:center;">
        <span class="material-symbols-outlined" style="color:var(--xhr-secondary);">search</span>
        <input id="xhr-employee-search"
               type="text"
               placeholder="<?php echo _l('xetuu_hr_search_employees'); ?>"
               style="border:none;outline:none;flex:1;font-size:14px;font-family:'Inter',sans-serif;background:transparent;color:var(--xhr-on-surface);">
        <span style="font-size:12px;color:var(--xhr-secondary);"><?php echo count($employees); ?> <?php echo _l('xetuu_hr_records'); ?></span>
    </div>

    <!-- Employee Table -->
    <div class="xhr-table-card">
        <div class="xhr-table-card__header">
            <h4 class="xhr-table-card__title"><?php echo _l('xetuu_hr_all_employees'); ?></h4>
        </div>

        <?php if (!empty($employees)) : ?>
        <div style="overflow-x:auto;">
            <table class="xhr-table">
                <thead>
                    <tr>
                        <th><?php echo _l('xetuu_hr_employee'); ?></th>
                        <th><?php echo _l('xetuu_hr_employee_id'); ?></th>
                        <th><?php echo _l('xetuu_hr_department'); ?></th>
                        <th><?php echo _l('xetuu_hr_designation'); ?></th>
                        <th><?php echo _l('xetuu_hr_date_joined'); ?></th>
                        <th><?php echo _l('xetuu_hr_employment_type'); ?></th>
                        <th><?php echo _l('xetuu_hr_status'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp) : ?>
                    <tr>
                        <td>
                            <div class="xhr-table__emp-cell">
                                <?php if (!empty($emp->photo)) : ?>
                                    <img class="xhr-table__avatar" src="<?php echo base_url($emp->photo); ?>" alt="">
                                <?php else : ?>
                                    <div class="xhr-table__avatar xhr-avatar-initials" style="width:36px;height:36px;font-size:13px;">
                                        <?php echo strtoupper(substr($emp->first_name,0,1) . substr($emp->last_name,0,1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="xhr-table__name">
                                        <a href="<?php echo admin_url('xetuu_hr/employees/' . $emp->id); ?>">
                                            <?php echo htmlspecialchars($emp->first_name . ' ' . $emp->last_name); ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($emp->company_email)) : ?>
                                    <div class="xhr-table__sub"><?php echo htmlspecialchars($emp->company_email); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12px;font-weight:600;color:var(--xhr-secondary);"><?php echo htmlspecialchars($emp->employee_number); ?></td>
                        <td><?php echo htmlspecialchars($emp->department_name ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($emp->designation_name ?: '—'); ?></td>
                        <td style="font-size:13px;color:var(--xhr-secondary);">
                            <?php echo $emp->date_of_joining ? date('d M Y', strtotime($emp->date_of_joining)) : '—'; ?>
                        </td>
                        <td><span style="font-size:12px;"><?php echo htmlspecialchars($emp->employment_type ?: '—'); ?></span></td>
                        <td>
                            <?php
                            $status_class = 'xhr-badge--inactive';
                            if ($emp->status === 'Active') $status_class = 'xhr-badge--active';
                            if (in_array($emp->status, ['Terminated','Resigned'])) $status_class = 'xhr-badge--error';
                            ?>
                            <span class="xhr-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($emp->status); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('xetuu_hr/employees/' . $emp->id); ?>"
                               class="xhr-btn xhr-btn--outline" style="padding:5px 12px;font-size:12px;">
                                <?php echo _l('xetuu_hr_view'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else : ?>
        <div class="xhr-empty">
            <span class="material-symbols-outlined">badge</span>
            <div class="xhr-empty__title"><?php echo _l('xetuu_hr_no_employees_yet'); ?></div>
            <div class="xhr-empty__sub"><?php echo _l('xetuu_hr_add_first_employee'); ?></div>
            <div style="margin-top:1.5rem;">
                <a href="<?php echo admin_url('xetuu_hr/employees/add'); ?>" class="xhr-btn xhr-btn--primary">
                    <span class="material-symbols-outlined">person_add</span>
                    <?php echo _l('xetuu_hr_add_employee'); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- /.xhr-table-card -->

</div><!-- /.xhr-content -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<!-- HR Assistance FAB -->
<button class="xhr-fab">
    <span class="material-symbols-outlined">support_agent</span>
    <span class="xhr-fab__tooltip">HR Assistance</span>
</button>

<?php init_tail(); ?>
