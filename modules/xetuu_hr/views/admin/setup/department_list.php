<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active  = 'setup';
$base        = admin_url('xetuu_hr');
$departments = isset($departments) ? $departments : [];
$companies   = isset($companies)   ? $companies   : [];
$emp_counts  = isset($emp_counts)  ? $emp_counts  : [];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page">

    <!-- Page header -->
    <div class="xhr-setup-header">
        <div class="xhr-setup-header__left">
            <div class="xhr-setup-breadcrumb">
                <a href="<?php echo $base; ?>">Xetuu HR</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <span>Setup</span>
                <span class="material-symbols-outlined">chevron_right</span>
                <span>Departments</span>
            </div>
            <h1 class="xhr-setup-title">Departments</h1>
            <p class="xhr-setup-subtitle">Functional units within each company, with approver assignments.</p>
        </div>
        <a href="<?php echo $base . '/setup/department/add'; ?>" class="xhr-btn xhr-btn--primary">
            <span class="material-symbols-outlined">add</span>
            New Department
        </a>
    </div>

    <!-- Sub-nav -->
    <div class="xhr-setup-subnav">
        <?php foreach ([
            'company'        => ['icon' => 'business',      'label' => 'Company'],
            'branch'         => ['icon' => 'location_on',   'label' => 'Branch'],
            'department'     => ['icon' => 'account_tree',  'label' => 'Department'],
            'designation'    => ['icon' => 'badge',         'label' => 'Designation'],
            'employee_group' => ['icon' => 'groups',        'label' => 'Employee Group'],
            'employee_grade' => ['icon' => 'military_tech', 'label' => 'Employee Grade'],
            'settings'       => ['icon' => 'settings',      'label' => 'Settings'],
        ] as $key => $item): ?>
        <a href="<?php echo $base . '/setup/' . $key; ?>"
           class="xhr-setup-subnav__item <?php echo $key === 'department' ? 'xhr-setup-subnav__item--active' : ''; ?>">
            <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Odoo-style two-panel layout -->
    <div class="xhr-dept-layout">

        <!-- LEFT: company filter sidebar + dept tree -->
        <div class="xhr-dept-sidebar">
            <div class="xhr-dept-sidebar__header">
                <span class="material-symbols-outlined">account_tree</span>
                Department
            </div>
            <a href="<?php echo $base . '/setup/department'; ?>"
               class="xhr-dept-sidebar__item xhr-dept-sidebar__item--all active" data-company="">
                All
                <span class="xhr-dept-sidebar__count"><?php echo count($departments); ?></span>
            </a>
            <?php foreach ($companies as $c):
                $c_depts = array_filter((array)$departments, function($d) use ($c) { return $d->company_id == $c->id; });
            ?>
            <div class="xhr-dept-sidebar__company"><?php echo htmlspecialchars($c->name); ?></div>
            <?php foreach ($c_depts as $d):
                $count = $emp_counts[$d->id] ?? 0;
            ?>
            <a href="#" class="xhr-dept-sidebar__item" data-dept="<?php echo $d->id; ?>">
                <?php if ($d->parent_id): ?>
                <span style="padding-left:12px;opacity:.5">↳</span>
                <?php endif; ?>
                <?php echo htmlspecialchars($d->name); ?>
                <?php if ($count): ?>
                <span class="xhr-dept-sidebar__count"><?php echo $count; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- RIGHT: department table -->
        <div class="xhr-dept-main">
            <!-- Toolbar -->
            <div class="xhr-setup-toolbar">
                <div class="xhr-setup-search-wrap">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="xhr-dept-search" class="xhr-setup-search" placeholder="Search departments…">
                </div>
                <span style="font-size:12px;color:#9ca3af;"><?php echo count($departments); ?> departments</span>
            </div>

            <?php if (empty($departments)): ?>
            <div class="xhr-setup-empty">
                <span class="material-symbols-outlined">account_tree</span>
                <p>No departments yet.</p>
                <a href="<?php echo $base . '/setup/department/add'; ?>" class="xhr-btn xhr-btn--primary">
                    <span class="material-symbols-outlined">add</span> New Department
                </a>
            </div>
            <?php else: ?>
            <table class="xhr-setup-table" id="xhr-dept-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Company</th>
                        <th>Parent</th>
                        <th>Manager</th>
                        <th>Employees</th>
                        <th>Flags</th>
                        <th style="text-align:right;width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d):
                        $count = $emp_counts[$d->id] ?? 0;
                    ?>
                    <tr data-company="<?php echo $d->company_id; ?>" data-dept="<?php echo $d->id; ?>">
                        <td>
                            <a href="<?php echo $base . '/setup/department/edit/' . $d->id; ?>"
                               class="xhr-dept-name-link">
                                <?php if ($d->is_group): ?>
                                <span class="material-symbols-outlined" style="font-size:15px;color:#6b7280;vertical-align:middle;">folder</span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($d->name); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($d->company_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($d->parent_name ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($d->manager_name ?? '—'); ?></td>
                        <td>
                            <?php if ($count): ?>
                            <a href="<?php echo $base . '/employees?dept=' . $d->id; ?>"
                               class="xhr-dept-emp-count"><?php echo $count; ?></a>
                            <?php else: ?>
                            <span style="color:#d1d5db;">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d->is_group): ?><span class="xhr-badge xhr-badge--blue">Group</span><?php endif; ?>
                            <?php if ($d->disabled): ?><span class="xhr-badge xhr-badge--grey">Disabled</span><?php endif; ?>
                            <?php if (!$d->is_group && !$d->disabled): ?><span class="xhr-badge xhr-badge--green">Active</span><?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?php echo $base . '/setup/department/edit/' . $d->id; ?>" class="xhr-tbl-btn" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <a href="<?php echo $base . '/setup/department/delete/' . $d->id; ?>"
                               class="xhr-tbl-btn xhr-tbl-btn--danger" title="Delete"
                               onclick="return confirm('Delete department <?php echo htmlspecialchars(addslashes($d->name)); ?>?')">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<script>
// Sidebar dept filter
document.querySelectorAll('.xhr-dept-sidebar__item[data-dept]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.xhr-dept-sidebar__item').forEach(function(i){ i.classList.remove('active'); });
        this.classList.add('active');
        var deptId = this.getAttribute('data-dept');
        document.querySelectorAll('#xhr-dept-table tbody tr').forEach(function(tr) {
            if (!deptId || tr.getAttribute('data-dept') === deptId) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
});
document.querySelector('.xhr-dept-sidebar__item--all').addEventListener('click', function(e){
    e.preventDefault();
    document.querySelectorAll('.xhr-dept-sidebar__item').forEach(function(i){ i.classList.remove('active'); });
    this.classList.add('active');
    document.querySelectorAll('#xhr-dept-table tbody tr').forEach(function(tr){ tr.style.display = ''; });
});

// Search
document.getElementById('xhr-dept-search').addEventListener('input', function() {
    var term = this.value.toLowerCase();
    document.querySelectorAll('#xhr-dept-table tbody tr').forEach(function(tr) {
        tr.style.display = tr.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
    });
});
</script>

<?php init_tail(); ?>
