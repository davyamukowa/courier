<div class="panel panel-info">
    <div class="panel-heading">
        <strong><i class="fa fa-globe"></i> International Tariffs (Origin-Based)</strong>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="font-size:13px; margin-bottom: 20px;">
            Upload rate sheets keyed by origin country and service type. The portal quote calculator will match Origin &rarr; Destination to find the correct rate.
        </p>

        <div class="row">
            <div class="col-md-6">
                <!-- Multi-step wizard -->
                <div class="panel panel-default">
                    <div class="panel-heading" style="font-size:14px; font-weight:bold;">
                        <i class="fa fa-magic"></i> Upload Wizard
                    </div>
                    <div class="panel-body">
                        <div id="origin-tariff-alert" class="alert" style="display:none;margin-bottom:12px;"></div>
                        
                        <form id="matrix-upload-form">
                            <!-- STEP 1 -->
                            <div class="wizard-step" id="step-1">
                                <h5><strong>Step 1: Select Details</strong></h5>
                                <div class="form-group">
                                    <label>Origin Country <span class="text-danger">*</span></label>
                                    <select class="form-control" id="matrix_origin_country" required>
                                        <option value="">-- Select Origin Country --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Service Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="matrix_service_type" required>
                                        <option value="">-- Select Service Type --</option>
                                        <option value="courier">Courier (Standard Parcel)</option>
                                        <option value="road">Road Freight</option>
                                        <option value="lcl">Sea LCL</option>
                                        <option value="consolidation">Sea Consolidation</option>
                                        <option value="air_freight">Air Freight</option>
                                        <option value="air_consolidation">Air Consolidation</option>
                                        <!-- Note: Domestic is excluded per requirements -->
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next <i class="fa fa-arrow-right"></i></button>
                            </div>

                            <!-- STEP 2 -->
                            <div class="wizard-step" id="step-2" style="display:none;">
                                <h5><strong>Step 2: Download Template</strong></h5>
                                <p class="text-muted">
                                    Download the 2D Matrix CSV template for the selected origin and service type. 
                                    Fill in your rates across the columns (destinations) and rows (weights/containers).
                                </p>
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" onclick="downloadMatrixTemplate()">
                                        <i class="fa fa-download"></i> Download Template
                                    </button>
                                </div>
                                <hr>
                                <button type="button" class="btn btn-default" onclick="goToStep(1)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next <i class="fa fa-arrow-right"></i></button>
                            </div>

                            <!-- STEP 3 -->
                            <div class="wizard-step" id="step-3" style="display:none;">
                                <h5><strong>Step 3: Upload Tariffs</strong></h5>
                                <div class="form-group">
                                    <label>Completed CSV File <span class="text-danger">*</span></label>
                                    <input type="file" id="matrix_csv_file" accept=".csv" class="form-control">
                                </div>
                                <hr>
                                <button type="button" class="btn btn-default" onclick="goToStep(2)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button type="submit" class="btn btn-success" id="matrix-upload-btn">
                                    <i class="fa fa-upload"></i> Upload &amp; Apply Tariffs
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Uploaded Tariffs List -->
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading" style="font-size:14px; font-weight:bold; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-list"></i> Uploaded Origin Countries</span>
                        <button id="bulk-delete-btn" class="btn btn-danger btn-sm" style="display:none;" onclick="bulkDeleteOrigins(this)">
                            <i class="fa fa-trash"></i> Delete Selected
                        </button>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-bordered table-condensed" style="font-size:13px;margin-bottom:0;" id="origin-countries-table">
                            <thead style="background:#f5f5f5;">
                                <tr>
                                    <th style="width:40px; text-align:center;"><input type="checkbox" id="select-all-origins" onclick="toggleAllOrigins(this)"></th>
                                    <th>Origin Country</th>
                                    <th>Total Rate Rows</th>
                                    <th style="width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="origin-countries-body">
                                <?php if (empty($origin_tariff_origins)): ?>
                                <tr id="origin-empty-row"><td colspan="4" class="text-center text-muted" style="padding:12px;">No origin tariffs uploaded yet.</td></tr>
                                <?php else: ?>
                                <?php foreach ($origin_tariff_origins as $o): ?>
                                <tr id="origin-row-<?php echo htmlspecialchars($o['origin_country']); ?>">
                                    <td style="text-align:center;"><input type="checkbox" class="origin-checkbox" value="<?php echo htmlspecialchars($o['origin_country']); ?>" onchange="toggleBulkDeleteBtn()"></td>
                                    <td><strong><?php echo htmlspecialchars($o['origin_country']); ?></strong></td>
                                    <td><span class="badge"><?php echo $o['rate_count']; ?></span></td>
                                    <td>
                                        <a href="<?php echo admin_url('courier/settings/view_origin_rates?origin=' . urlencode($o['origin_country'])); ?>" class="btn btn-info btn-xs" style="margin-right: 5px; margin-bottom: 5px;" target="_blank">
                                            <i class="fa fa-eye"></i> View Rates
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
        </div>
    </div>
</div>

<script>
const COUNTRIES_URL = '<?php echo base_url("courier/tracker/get_countries"); ?>';

function goToStep(step) {
    if (step === 2) {
        var origin = document.getElementById('matrix_origin_country').value;
        var service = document.getElementById('matrix_service_type').value;
        if (!origin || !service) {
            alert('Please select both Origin Country and Service Type before proceeding.');
            return;
        }
    }
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
    document.getElementById('step-' + step).style.display = 'block';
}

function downloadMatrixTemplate() {
    var origin = document.getElementById('matrix_origin_country').value;
    var service = document.getElementById('matrix_service_type').value;
    window.location.href = '<?php echo admin_url("courier/settings/download_matrix_template"); ?>?origin=' + encodeURIComponent(origin) + '&service=' + encodeURIComponent(service);
}

document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('matrix_origin_country');
    if (sel) {
        fetch(COUNTRIES_URL)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    res.data.forEach(c => {
                        var opt = document.createElement('option');
                        opt.value = c.name;
                        opt.textContent = c.name;
                        sel.appendChild(opt);
                    });
                }
            });
    }

    document.getElementById('matrix-upload-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var origin = document.getElementById('matrix_origin_country').value;
        var service = document.getElementById('matrix_service_type').value;
        var fileInput = document.getElementById('matrix_csv_file');
        
        if (!fileInput.files.length) {
            alert('Please select a CSV file.');
            return;
        }

        var btn = document.getElementById('matrix-upload-btn');
        var oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
        
        var alertDiv = document.getElementById('origin-tariff-alert');
        alertDiv.style.display = 'none';

        var formData = new FormData();
        formData.append('origin_country', origin);
        formData.append('service_type', service);
        formData.append('matrix_csv', fileInput.files[0]);
        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

        fetch('<?php echo admin_url("courier/settings/upload_matrix_csv"); ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            alertDiv.style.display = 'block';
            if (res.success) {
                alertDiv.className = 'alert alert-success';
                alertDiv.innerHTML = res.inserted + ' rates inserted, ' + res.updated + ' updated. ' + (res.errors > 0 ? res.errors + ' errors.' : '');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = res.message || 'Error uploading file.';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            alertDiv.style.display = 'block';
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = 'Network error occurred.';
        });
    });
});

function toggleAllOrigins(source) {
    var checkboxes = document.querySelectorAll('.origin-checkbox');
    checkboxes.forEach(function(cb) {
        cb.checked = source.checked;
    });
    toggleBulkDeleteBtn();
}

function toggleBulkDeleteBtn() {
    var checkboxes = document.querySelectorAll('.origin-checkbox:checked');
    var btn = document.getElementById('bulk-delete-btn');
    if (checkboxes.length > 0) {
        btn.style.display = 'inline-block';
    } else {
        btn.style.display = 'none';
        document.getElementById('select-all-origins').checked = false;
    }
}

function bulkDeleteOrigins(btn) {
    var checkboxes = document.querySelectorAll('.origin-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    if (!confirm('Are you sure you want to delete ALL rates for the selected ' + checkboxes.length + ' origins? This will affect all service types for these origins.')) return;
    
    var origins = [];
    checkboxes.forEach(function(cb) {
        origins.push(cb.value);
    });
    
    btn.disabled = true;
    var oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
    
    var fd = new FormData();
    fd.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
    origins.forEach(function(orig) {
        fd.append('origins[]', orig);
    });
    
    fetch('<?php echo admin_url("courier/settings/bulk_delete_origin_tariffs"); ?>', {
        method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            origins.forEach(function(orig) {
                var row = document.getElementById('origin-row-' + orig);
                if (row) row.remove();
            });
            toggleBulkDeleteBtn();
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            
            // Check if table empty
            if (document.querySelectorAll('.origin-checkbox').length === 0) {
                document.getElementById('origin-countries-body').innerHTML = '<tr id="origin-empty-row"><td colspan="4" class="text-center text-muted" style="padding:12px;">No origin tariffs uploaded yet.</td></tr>';
            }
        } else {
            alert('Failed to delete selected origins.');
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    })
    .catch(err => {
        alert('Network error occurred.');
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    });
}
</script>
