<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');
$sub        = isset($sub) ? $sub : 'company';
$rows       = isset($rows) ? $rows : [];

$configs = [
    'company' => [
        'label'       => 'Company',
        'description' => 'Legal entities and EOR client companies managed by this HR system.',
        'icon'        => 'business',
        'cols'        => ['Company', 'Contact', 'Country', 'Type', 'Status'],
        'fields'      => [
            ['name' => 'name',    'label' => 'Company Name',   'type' => 'text',     'required' => true,  'span' => 2],
            ['name' => 'email',   'label' => 'Email Address',  'type' => 'email',    'required' => false, 'span' => 1],
            ['name' => 'phone',   'label' => 'Phone',          'type' => 'text',     'required' => false, 'span' => 1],
            ['name' => 'country', 'label' => 'Country',        'type' => 'text',     'required' => false, 'span' => 1],
            ['name' => 'address', 'label' => 'Address',        'type' => 'textarea', 'required' => false, 'span' => 2],
            ['name' => 'is_eor',             'label' => 'This is an Employer of Record (EOR) client', 'type' => 'toggle',          'required' => false, 'span' => 2],
            ['name' => 'payroll_company_id', 'label' => 'Linked Payroll Company',                      'type' => 'payroll_company', 'required' => false, 'span' => 2],
            ['name' => 'active',             'label' => 'Active',                                       'type' => 'toggle',          'required' => false, 'span' => 1],
        ],
    ],
    'branch' => [
        'label'       => 'Branch',
        'description' => 'Physical offices or locations belonging to a company.',
        'icon'        => 'location_on',
        'cols'        => ['Branch', 'Company', 'City', 'Country', 'Status'],
        'fields'      => [
            ['name' => 'name',       'label' => 'Branch Name', 'type' => 'text',     'required' => true,  'span' => 2],
            ['name' => 'company_id', 'label' => 'Company',     'type' => 'company',  'required' => true,  'span' => 2],
            ['name' => 'address',    'label' => 'Address',     'type' => 'textarea', 'required' => false, 'span' => 2],
            ['name' => 'city',       'label' => 'City',        'type' => 'text',     'required' => false, 'span' => 1],
            ['name' => 'country',    'label' => 'Country',     'type' => 'text',     'required' => false, 'span' => 1],
            ['name' => 'active',     'label' => 'Active',      'type' => 'toggle',   'required' => false, 'span' => 1],
        ],
    ],
    'department' => [
        'label'       => 'Department',
        'description' => 'Functional units within a company.',
        'icon'        => 'account_tree',
        'cols'        => ['Department', 'Company', 'Status'],
        'fields'      => [
            ['name' => 'name',       'label' => 'Department Name', 'type' => 'text',    'required' => true, 'span' => 2],
            ['name' => 'company_id', 'label' => 'Company',         'type' => 'company', 'required' => true, 'span' => 2],
            ['name' => 'active',     'label' => 'Active',          'type' => 'toggle',  'required' => false,'span' => 1],
        ],
    ],
    'designation' => [
        'label'       => 'Designation',
        'description' => 'Job titles or roles assigned to employees.',
        'icon'        => 'badge',
        'cols'        => ['Designation', 'Description'],
        'fields'      => [
            ['name' => 'name',        'label' => 'Designation Name', 'type' => 'text',     'required' => true,  'span' => 2],
            ['name' => 'description', 'label' => 'Description',      'type' => 'textarea', 'required' => false, 'span' => 2],
        ],
    ],
    'employee_group' => [
        'label'       => 'Employee Group',
        'description' => 'Categories for grouping employees (e.g. Permanent, Casual).',
        'icon'        => 'groups',
        'cols'        => ['Group Name', 'Description'],
        'fields'      => [
            ['name' => 'name',        'label' => 'Group Name',  'type' => 'text',     'required' => true,  'span' => 2],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false, 'span' => 2],
        ],
    ],
    'employee_grade' => [
        'label'       => 'Employee Grade',
        'description' => 'Pay bands or seniority levels with optional salary ranges.',
        'icon'        => 'military_tech',
        'cols'        => ['Grade', 'Min Salary', 'Max Salary'],
        'fields'      => [
            ['name' => 'name',       'label' => 'Grade Name',   'type' => 'text',   'required' => true,  'span' => 2],
            ['name' => 'min_salary', 'label' => 'Min Salary',   'type' => 'number', 'required' => false, 'span' => 1],
            ['name' => 'max_salary', 'label' => 'Max Salary',   'type' => 'number', 'required' => false, 'span' => 1],
        ],
    ],
];
$cfg = $configs[$sub] ?? $configs['company'];
$companies_list = isset($companies) ? $companies : [];
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
                <span><?php echo $cfg['label']; ?></span>
            </div>
            <h1 class="xhr-setup-title"><?php echo $cfg['label']; ?></h1>
            <p class="xhr-setup-subtitle"><?php echo $cfg['description']; ?></p>
        </div>
        <button type="button" class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
            <span class="material-symbols-outlined">add</span>
            Add <?php echo $cfg['label']; ?>
        </button>
    </div>

    <!-- Sub-nav -->
    <div class="xhr-setup-subnav">
        <?php
        $subnav_items = [
            'company'        => ['icon' => 'business',       'label' => 'Company'],
            'branch'         => ['icon' => 'location_on',    'label' => 'Branch'],
            'department'     => ['icon' => 'account_tree',   'label' => 'Department'],
            'designation'    => ['icon' => 'badge',          'label' => 'Designation'],
            'employee_group' => ['icon' => 'groups',         'label' => 'Employee Group'],
            'employee_grade' => ['icon' => 'military_tech',  'label' => 'Employee Grade'],
            'settings'       => ['icon' => 'settings',       'label' => 'Settings'],
        ];
        foreach ($subnav_items as $key => $item):
        ?>
        <a href="<?php echo $base . '/setup/' . $key; ?>"
           class="xhr-setup-subnav__item <?php echo $sub === $key ? 'xhr-setup-subnav__item--active' : ''; ?>">
            <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Stats bar (company only) -->
    <?php if ($sub === 'company'): ?>
    <div class="xhr-setup-stats-bar">
        <?php
        $total    = count($rows);
        $active   = count(array_filter((array)$rows, function($r){ return $r->active; }));
        $eor      = count(array_filter((array)$rows, function($r){ return $r->is_eor; }));
        $inactive = $total - $active;
        ?>
        <div class="xhr-setup-stat">
            <div class="xhr-setup-stat__val"><?php echo $total; ?></div>
            <div class="xhr-setup-stat__lbl">Total Companies</div>
        </div>
        <div class="xhr-setup-stat">
            <div class="xhr-setup-stat__val xhr-setup-stat__val--green"><?php echo $active; ?></div>
            <div class="xhr-setup-stat__lbl">Active</div>
        </div>
        <div class="xhr-setup-stat">
            <div class="xhr-setup-stat__val xhr-setup-stat__val--blue"><?php echo $eor; ?></div>
            <div class="xhr-setup-stat__lbl">EOR Clients</div>
        </div>
        <div class="xhr-setup-stat">
            <div class="xhr-setup-stat__val xhr-setup-stat__val--grey"><?php echo $inactive; ?></div>
            <div class="xhr-setup-stat__lbl">Inactive</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main card -->
    <div class="xhr-setup-card">

        <!-- Toolbar -->
        <div class="xhr-setup-toolbar">
            <div class="xhr-setup-search-wrap">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="xhr-setup-search" class="xhr-setup-search" placeholder="Search <?php echo strtolower($cfg['label']); ?>s…">
            </div>
            <div style="font-size:12px;color:#9ca3af;padding:0 4px;">
                <?php echo count($rows); ?> record<?php echo count($rows) !== 1 ? 's' : ''; ?>
            </div>
        </div>

        <?php if (empty($rows)): ?>
        <div class="xhr-setup-empty">
            <span class="material-symbols-outlined"><?php echo $cfg['icon']; ?></span>
            <p>No <?php echo strtolower($cfg['label']); ?> records yet.</p>
            <button type="button" class="xhr-btn xhr-btn--primary" onclick="xhrOpenModal()">
                <span class="material-symbols-outlined">add</span>
                Add <?php echo $cfg['label']; ?>
            </button>
        </div>
        <?php else: ?>
        <div class="xhr-setup-table-wrap">
            <table class="xhr-setup-table" id="xhr-setup-table">
                <thead>
                    <tr>
                        <?php foreach ($cfg['cols'] as $col): ?>
                        <th><?php echo $col; ?></th>
                        <?php endforeach; ?>
                        <th style="text-align:right;width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php if ($sub === 'company'): ?>
                            <td>
                                <div class="xhr-setup-company-cell">
                                    <div class="xhr-setup-company-avatar">
                                        <?php echo strtoupper(substr($row->name, 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div class="xhr-setup-company-name"><?php echo htmlspecialchars($row->name); ?></div>
                                        <?php if (!empty($row->email)): ?>
                                        <div class="xhr-setup-company-meta"><?php echo htmlspecialchars($row->email); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row->phone ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row->country ?? '—'); ?></td>
                            <td>
                                <?php if ($row->is_eor): ?>
                                <span class="xhr-badge xhr-badge--blue">EOR Client</span>
                                <?php else: ?>
                                <span class="xhr-badge xhr-badge--grey">Own Company</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row->active ? '<span class="xhr-badge xhr-badge--green">Active</span>' : '<span class="xhr-badge xhr-badge--grey">Inactive</span>'; ?></td>

                        <?php elseif ($sub === 'branch'): ?>
                            <td><strong><?php echo htmlspecialchars($row->name); ?></strong></td>
                            <td><?php echo htmlspecialchars($row->company_name ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row->city ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row->country ?? '—'); ?></td>
                            <td><?php echo $row->active ? '<span class="xhr-badge xhr-badge--green">Active</span>' : '<span class="xhr-badge xhr-badge--grey">Inactive</span>'; ?></td>

                        <?php elseif ($sub === 'department'): ?>
                            <td><strong><?php echo htmlspecialchars($row->name); ?></strong></td>
                            <td><?php echo htmlspecialchars($row->company_name ?? '—'); ?></td>
                            <td><?php echo $row->active ? '<span class="xhr-badge xhr-badge--green">Active</span>' : '<span class="xhr-badge xhr-badge--grey">Inactive</span>'; ?></td>

                        <?php elseif ($sub === 'designation'): ?>
                            <td><strong><?php echo htmlspecialchars($row->name); ?></strong></td>
                            <td style="color:#6b7280;"><?php echo htmlspecialchars(substr($row->description ?? '', 0, 80)) ?: '—'; ?></td>

                        <?php elseif ($sub === 'employee_group'): ?>
                            <td><strong><?php echo htmlspecialchars($row->name); ?></strong></td>
                            <td style="color:#6b7280;"><?php echo htmlspecialchars(substr($row->description ?? '', 0, 80)) ?: '—'; ?></td>

                        <?php elseif ($sub === 'employee_grade'): ?>
                            <td><strong><?php echo htmlspecialchars($row->name); ?></strong></td>
                            <td><?php echo number_format($row->min_salary ?? 0, 2); ?></td>
                            <td><?php echo number_format($row->max_salary ?? 0, 2); ?></td>
                        <?php endif; ?>

                        <td style="text-align:right;">
                            <button type="button" class="xhr-tbl-btn" title="Edit"
                                onclick="xhrOpenModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES); ?>)">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <a href="<?php echo $base . '/setup/' . $sub . '/delete/' . $row->id; ?>"
                               class="xhr-tbl-btn xhr-tbl-btn--danger"
                               onclick="return confirm('Delete this <?php echo strtolower($cfg['label']); ?>? This cannot be undone.')"
                               title="Delete">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.xhr-setup-page -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<!-- ── Add / Edit Modal ──────────────────────────────────────────────────── -->
<div id="xhr-setup-modal" class="xhr-modal-overlay" style="display:none;">
    <div class="xhr-modal">
        <div class="xhr-modal__header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="xhr-modal__icon">
                    <span class="material-symbols-outlined"><?php echo $cfg['icon']; ?></span>
                </div>
                <span id="xhr-modal-title">Add <?php echo $cfg['label']; ?></span>
            </div>
            <button type="button" class="xhr-modal__close" onclick="xhrCloseModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="post" action="<?php echo $base . '/setup/' . $sub; ?>" id="xhr-setup-form">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="xhr-modal-id" value="">
            <div class="xhr-modal__body">
                <div class="xhr-modal__grid">

                    <?php foreach ($cfg['fields'] as $f):
                        $span_class = isset($f['span']) && $f['span'] == 2 ? ' xhr-modal__field--full' : '';
                    ?>
                    <div class="xhr-modal__field<?php echo $span_class; ?>">
                        <label class="xhr-form-label<?php echo $f['required'] ? ' xhr-form-label--req' : ''; ?>">
                            <?php echo $f['label']; ?>
                        </label>

                        <?php if ($f['type'] === 'text' || $f['type'] === 'email'): ?>
                            <input type="<?php echo $f['type']; ?>" name="<?php echo $f['name']; ?>"
                                   id="mf_<?php echo $f['name']; ?>" class="xhr-form-input"
                                   <?php echo $f['required'] ? 'required' : ''; ?>>

                        <?php elseif ($f['type'] === 'number'): ?>
                            <input type="number" step="0.01" min="0" name="<?php echo $f['name']; ?>"
                                   id="mf_<?php echo $f['name']; ?>" class="xhr-form-input">

                        <?php elseif ($f['type'] === 'textarea'): ?>
                            <textarea name="<?php echo $f['name']; ?>"
                                      id="mf_<?php echo $f['name']; ?>"
                                      class="xhr-form-input" rows="2"></textarea>

                        <?php elseif ($f['type'] === 'company'): ?>
                            <select name="<?php echo $f['name']; ?>" id="mf_<?php echo $f['name']; ?>"
                                    class="xhr-form-select" <?php echo $f['required'] ? 'required' : ''; ?>>
                                <option value="">— Select Company —</option>
                                <?php foreach ($companies_list as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($f['type'] === 'payroll_company'): ?>
                            <select name="<?php echo $f['name']; ?>" id="mf_<?php echo $f['name']; ?>"
                                    class="xhr-form-select">
                                <option value="">— None / Skip —</option>
                                <?php foreach ($payroll_companies ?? [] as $pc): ?>
                                <option value="<?php echo $pc->id; ?>"><?php echo htmlspecialchars($pc->name); ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($f['type'] === 'toggle'): ?>
                            <label class="xhr-toggle-wrap" style="margin-top:6px;">
                                <input type="hidden" name="<?php echo $f['name']; ?>" value="0">
                                <input type="checkbox" name="<?php echo $f['name']; ?>"
                                       id="mf_<?php echo $f['name']; ?>" value="1">
                                <span class="xhr-toggle-track"></span>
                            </label>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
            <div class="xhr-modal__footer">
                <button type="button" class="xhr-btn xhr-btn--outline" onclick="xhrCloseModal()">Cancel</button>
                <button type="submit" class="xhr-btn xhr-btn--primary">
                    <span class="material-symbols-outlined">save</span>
                    Save <?php echo $cfg['label']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var xhrModalFields = <?php echo json_encode($cfg['fields']); ?>;

function xhrOpenModal(row) {
    var modal = document.getElementById('xhr-setup-modal');
    var title = document.getElementById('xhr-modal-title');
    var idInput = document.getElementById('xhr-modal-id');

    xhrModalFields.forEach(function(f) {
        var el = document.getElementById('mf_' + f.name);
        if (!el) return;
        if (f.type === 'toggle') { el.checked = false; }
        else { el.value = ''; }
    });
    idInput.value = '';

    if (row) {
        title.textContent = 'Edit <?php echo $cfg['label']; ?>';
        idInput.value = row.id || '';
        xhrModalFields.forEach(function(f) {
            var el = document.getElementById('mf_' + f.name);
            if (!el) return;
            var val = row[f.name] !== undefined ? row[f.name] : '';
            if (f.type === 'toggle') { el.checked = (val == 1 || val === true); }
            else { el.value = val; }
        });
    } else {
        title.textContent = 'Add <?php echo $cfg['label']; ?>';
        var activeEl = document.getElementById('mf_active');
        if (activeEl) activeEl.checked = true;
    }

    modal.style.display = 'flex';
    var first = modal.querySelector('input[type=text],input[type=email]');
    if (first) setTimeout(function(){ first.focus(); }, 60);
}

function xhrCloseModal() {
    document.getElementById('xhr-setup-modal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('xhr-setup-modal').addEventListener('click', function(e) {
    if (e.target === this) xhrCloseModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') xhrCloseModal();
});

// Live search
var searchInput = document.getElementById('xhr-setup-search');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        var term = this.value.toLowerCase();
        var rows = document.querySelectorAll('#xhr-setup-table tbody tr');
        rows.forEach(function(tr) {
            tr.style.display = tr.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
        });
    });
}
</script>

<?php init_tail(); ?>
