<div class="panel panel-info">
    <div class="panel-heading">
        <strong><i class="fa fa-globe"></i> International Tariffs (Origin-Based)</strong>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="font-size:13px; margin-bottom: 20px;">
            Upload rate sheets keyed by origin country and service type. Shipment creation and the client portal's quote calculator both match Origin &rarr; Destination &rarr; Weight to find the correct rate automatically.
        </p>

        <?php
            $kenya_origin = null;
            foreach (($origin_tariff_origins ?? []) as $o) {
                if (strcasecmp(trim($o['origin_country']), 'Kenya') === 0) { $kenya_origin = $o; break; }
            }
        ?>
        <?php if ($kenya_origin): ?>
        <a href="<?php echo admin_url('courier_goshipping/settings/view_origin_rates?origin=' . urlencode($kenya_origin['origin_country']) . '&service=courier'); ?>"
           target="_blank"
           style="display:flex; align-items:center; justify-content:space-between; gap:12px;
                  background:linear-gradient(135deg,#2e7d32,#1b5e20); color:#fff; text-decoration:none;
                  border-radius:8px; padding:14px 18px; margin-bottom:18px;">
            <span style="display:flex; align-items:center; gap:12px;">
                <i class="fa fa-plane" style="font-size:22px;"></i>
                <span>
                    <strong style="font-size:15px;">International Courier — Kenya &rarr; Rest of World</strong><br>
                    <span style="font-size:12px; opacity:.85;">View or edit the full Kenya-origin courier rate sheet in an Excel-style grid</span>
                </span>
            </span>
            <span><i class="fa fa-arrow-right"></i></span>
        </a>
        <?php else: ?>
        <div class="alert alert-warning" style="margin-bottom:18px;">
            <i class="fa fa-exclamation-triangle"></i> No Kenya-origin rates uploaded yet. Use the wizard below —
            Origin Country = <strong>Kenya</strong>, Service Type = <strong>International Courier</strong> — to upload the rate sheet.
        </div>
        <?php endif; ?>

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
                                        <option value="courier">International Courier</option>
                                        <option value="road">International Road</option>
                                        <option value="air_freight">International by Air — Air Freight</option>
                                        <option value="air_consolidation">International by Air — Air Consolidation</option>
                                        <option value="fcl">International by Sea — FCL</option>
                                        <option value="lcl">International by Sea — LCL</option>
                                        <option value="consolidation">International by Sea — Consolidation</option>
                                        <!-- Note: Domestic is excluded per requirements -->
                                    </select>
                                </div>
                                <button type="button" class="cgs-btn cgs-btn--primary" onclick="goToStep(2)">Next <i class="fa fa-arrow-right"></i></button>
                            </div>

                            <!-- STEP 2 -->
                            <div class="wizard-step" id="step-2" style="display:none;">
                                <h5><strong>Step 2: Download Template (optional)</strong></h5>
                                <p class="text-muted">
                                    You can upload your own rate sheet directly as an <strong>.xlsx</strong> file in Step 3 —
                                    a row of destination country codes (US, NL, DE, AE...) followed by weight-band rows
                                    (0.5&nbsp;kg, 1.0&nbsp;kg... up to a "Price per kg" row for the over-max-weight rate)
                                    is read automatically. Only download this CSV template if you'd rather start from a
                                    blank sheet.
                                </p>
                                <div class="form-group">
                                    <button type="button" class="cgs-btn cgs-btn--outline" onclick="downloadMatrixTemplate()">
                                        <i class="fa fa-download"></i> Download CSV Template
                                    </button>
                                </div>
                                <hr>
                                <button type="button" class="cgs-btn cgs-btn--outline" onclick="goToStep(1)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button type="button" class="cgs-btn cgs-btn--primary" onclick="goToStep(3)">Next <i class="fa fa-arrow-right"></i></button>
                            </div>

                            <!-- STEP 3 -->
                            <div class="wizard-step" id="step-3" style="display:none;">
                                <h5><strong>Step 3: Upload Tariffs</strong></h5>
                                <div class="form-group">
                                    <label>Rate Sheet File <span class="text-danger">*</span></label>
                                    <input type="file" id="matrix_csv_file" accept=".csv,.xlsx,.xls" class="form-control">
                                    <small class="text-muted">Accepts .xlsx (your own rate sheet, uploaded as-is) or .csv (the downloaded template).</small>
                                </div>
                                <hr>
                                <button type="button" class="cgs-btn cgs-btn--outline" onclick="goToStep(2)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button type="submit" class="cgs-btn cgs-btn--primary" id="matrix-upload-btn">
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
                        <button id="bulk-delete-btn" class="cgs-btn cgs-btn--accent cgs-btn--sm" style="display:none;" onclick="bulkDeleteOrigins(this)">
                            <i class="fa fa-trash"></i> Delete Selected
                        </button>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <table class="table table-bordered table-condensed cgs-table" style="font-size:13px;margin-bottom:0;" id="origin-countries-table">
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
                                        <a href="<?php echo admin_url('courier_goshipping/settings/view_origin_rates?origin=' . urlencode($o['origin_country'])); ?>" class="cgs-btn cgs-btn--outline cgs-btn--sm" style="margin-right: 5px; margin-bottom: 5px;" target="_blank">
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
const COUNTRIES_URL = '<?php echo base_url("courier_goshipping/tracker/get_countries"); ?>';

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
    window.location.href = '<?php echo admin_url("courier_goshipping/settings/download_matrix_template"); ?>?origin=' + encodeURIComponent(origin) + '&service=' + encodeURIComponent(service);
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

        var file = fileInput.files[0];
        var isExcel = /\.(xlsx|xls)$/i.test(file.name);

        var formData = new FormData();
        formData.append('origin_country', origin);
        formData.append('service_type', service);
        formData.append(isExcel ? 'matrix_excel' : 'matrix_csv', file);
        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

        var uploadUrl = isExcel
            ? '<?php echo admin_url("courier_goshipping/settings/upload_matrix_excel"); ?>'
            : '<?php echo admin_url("courier_goshipping/settings/upload_matrix_csv"); ?>';

        fetch(uploadUrl, {
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
    
    fetch('<?php echo admin_url("courier_goshipping/settings/bulk_delete_origin_tariffs"); ?>', {
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
