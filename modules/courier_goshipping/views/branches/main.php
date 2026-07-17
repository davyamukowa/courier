<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'branches']); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="row mbot15">
            <div class="col-md-8">
                <h4 class="no-margin">Branches / Offices</h4>
                <p class="text-muted mtop5">Local and international Go Shipping offices. Staff are assigned to branches from their profile page, and only see data belonging to their assigned branch(es) unless they hold "View All Branches".</p>
            </div>
            <div class="col-md-4 text-right">
                <?php if ($can_manage): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#branchModal" onclick="resetBranchModal();">
                    <i class="fa fa-plus"></i> New Branch
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Staff Assigned</th>
                        <th>Status</th>
                        <th>Default</th>
                        <?php if ($can_manage): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($branches)): ?>
                        <?php foreach ($branches as $branch): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($branch->name); ?></strong></td>
                                <td><?php echo htmlspecialchars($branch->code); ?></td>
                                <td>
                                    <span class="label label-<?php echo $branch->branch_type === 'international' ? 'info' : 'default'; ?>">
                                        <?php echo ucfirst($branch->branch_type); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($branch->country_id ? get_country_name($branch->country_id) : '-'); ?></td>
                                <td><?php echo htmlspecialchars($branch->city ?: '-'); ?></td>
                                <td><?php echo (int) $this->CourierBranch_model->get_staff_count($branch->id); ?></td>
                                <td>
                                    <span class="label label-<?php echo $branch->is_active ? 'success' : 'danger'; ?>">
                                        <?php echo $branch->is_active ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo $branch->is_default ? '<i class="fa fa-check-circle text-success"></i>' : ''; ?></td>
                                <?php if ($can_manage): ?>
                                <td>
                                    <button type="button" class="btn btn-default btn-xs" onclick='openEditBranchModal(<?php echo json_encode($branch); ?>);'>
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                    <?php if (is_admin()): ?>
                                    <a href="<?php echo admin_url('courier_goshipping/branches/delete/' . $branch->id); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this branch? This only works if no staff are assigned to it.');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No branches created yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="branchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="branchForm" method="post">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title" id="branchModalTitle">New Branch</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="name" id="branch_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code</label>
                                <input type="text" name="code" id="branch_code" class="form-control" readonly>
                                <p class="text-muted mtop5 mbot0">Auto-generated from the branch name in the format <code>NAME/B/<?php echo date('Y'); ?>/<?php echo str_pad((string) $next_branch_sequence, 2, '0', STR_PAD_LEFT); ?></code>.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="branch_type" id="branch_type" class="form-control">
                                    <option value="local">Local</option>
                                    <option value="international">International</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Country</label>
                                <select name="country_id" id="branch_country_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?php echo (int) $c->country_id; ?>"><?php echo htmlspecialchars($c->short_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>City</label>
                                <select name="city" id="branch_city" class="form-control" disabled>
                                    <option value="">Select country first</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" id="branch_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" id="branch_phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" id="branch_email" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="is_default" id="branch_is_default" value="1"> Default branch (fallback for unresolved orders)</label>
                    </div>
                    <div class="checkbox" id="branch_active_wrap" style="display:none;">
                        <label><input type="checkbox" name="is_active" id="branch_is_active" value="1" checked> Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="branchSaveBtn">Save Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var _branchCitiesUrl = '<?php echo admin_url('courier_goshipping/branches/cities_by_country'); ?>';
var _branchCodeYear = '<?php echo date('Y'); ?>';
var _branchNextSequence = '<?php echo str_pad((string) $next_branch_sequence, 2, '0', STR_PAD_LEFT); ?>';

function buildBranchCodePreview(branchName) {
    var cleaned = (branchName || '')
        .toUpperCase()
        .replace(/[^A-Z0-9]+/g, ' ')
        .trim()
        .replace(/\s+/g, '-');

    if (!cleaned) {
        cleaned = 'BRANCH';
    }

    return cleaned + '/B/' + _branchCodeYear + '/' + _branchNextSequence;
}

function setBranchCityOptions(cities, selectedCity) {
    var citySelect = document.getElementById('branch_city');
    var html = '';

    if (!cities.length) {
        citySelect.disabled = true;
        html = '<option value="">' + (document.getElementById('branch_country_id').value ? 'No cities found' : 'Select country first') + '</option>';
        citySelect.innerHTML = html;
        return;
    }

    citySelect.disabled = false;
    html = '<option value="">-- Select City --</option>';
    cities.forEach(function(city) {
        var selected = selectedCity && selectedCity === city ? ' selected' : '';
        html += '<option value="' + $('<div>').text(city).html() + '"' + selected + '>' + $('<div>').text(city).html() + '</option>';
    });
    citySelect.innerHTML = html;
}

function loadBranchCities(countryId, selectedCity) {
    var citySelect = document.getElementById('branch_city');

    if (!countryId) {
        setBranchCityOptions([], '');
        return;
    }

    citySelect.disabled = true;
    citySelect.innerHTML = '<option value="">Loading cities...</option>';

    $.getJSON(_branchCitiesUrl, { country_id: countryId })
        .done(function(response) {
            setBranchCityOptions(response.cities || [], selectedCity || '');
        })
        .fail(function() {
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Unable to load cities</option>';
        });
}

function resetBranchModal() {
    document.getElementById('branchForm').reset();
    document.getElementById('branchForm').action = '<?php echo admin_url('courier_goshipping/branches/store'); ?>';
    document.getElementById('branchModalTitle').textContent = 'New Branch';
    document.getElementById('branch_active_wrap').style.display = 'none';
    document.getElementById('branch_code').value = buildBranchCodePreview('');
    setBranchCityOptions([], '');
}

function openEditBranchModal(branch) {
    var form = document.getElementById('branchForm');
    form.action = '<?php echo admin_url('courier_goshipping/branches/update/'); ?>' + branch.id;
    document.getElementById('branchModalTitle').textContent = 'Edit Branch';
    document.getElementById('branch_name').value = branch.name || '';
    document.getElementById('branch_code').value = branch.code || '';
    document.getElementById('branch_type').value = branch.branch_type || 'local';
    document.getElementById('branch_country_id').value = branch.country_id || '';
    document.getElementById('branch_address').value = branch.address || '';
    document.getElementById('branch_phone').value = branch.phone || '';
    document.getElementById('branch_email').value = branch.email || '';
    document.getElementById('branch_is_default').checked = !!parseInt(branch.is_default);
    document.getElementById('branch_is_active').checked = !!parseInt(branch.is_active);
    document.getElementById('branch_active_wrap').style.display = 'block';
    loadBranchCities(branch.country_id || '', branch.city || '');
    $('#branchModal').modal('show');
}

document.getElementById('branch_country_id').addEventListener('change', function() {
    loadBranchCities(this.value, '');
});

document.getElementById('branch_name').addEventListener('input', function() {
    if (document.getElementById('branchForm').action.indexOf('/store') !== -1) {
        document.getElementById('branch_code').value = buildBranchCodePreview(this.value);
    }
});

document.getElementById('branchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!document.getElementById('branch_name').value.trim()) {
        alert_float('danger', 'Branch Name is required.');
        return;
    }

    var $btn = $('#branchSaveBtn');
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.post(this.action, $(this).serialize(), function(res) {
        if (res.success) {
            alert_float('success', res.message || 'Saved successfully.');
            window.location.reload();
        } else {
            alert_float('danger', res.message || 'Save failed.');
            $btn.prop('disabled', false).html(originalText);
        }
    }, 'json').fail(function() {
        alert_float('danger', 'Unable to contact the server. Please refresh and try again.');
        $btn.prop('disabled', false).html(originalText);
    });
});
</script>
<?php init_tail(); ?>
