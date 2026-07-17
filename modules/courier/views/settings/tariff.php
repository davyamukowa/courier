<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-tags"></i> Tariff &amp; Quotation Rates
        </h4>
    </div>
    <div class="panel-body">

        <!-- Rate viewer modal (inline) -->
                <div id="origin-rates-viewer" style="display:none;margin-top:16px;">
                    <div style="background:#f4f9ff;border:1px solid #d4e6f1;border-radius:6px;padding:14px 18px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <strong style="font-size:13px;"><i class="fa fa-table"></i> Rates for: <span id="origin-rates-title"></span></strong>
                            <button class="btn btn-default btn-xs" onclick="document.getElementById('origin-rates-viewer').style.display='none'">
                                <i class="fa fa-times"></i> Close
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed" style="font-size:12px;margin-bottom:0;">
                                <thead style="background:#e8f4f8;">
                                    <tr>
                                        <th>Destination</th>
                                        <th>Service</th>
                                        <th>Min (kg)</th>
                                        <th>Max (kg)</th>
                                        <th>Rate Type</th>
                                        <th>Rate</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="origin-rates-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ Domestic City-to-City Tariffs ═══ -->
        <div class="panel panel-warning" style="margin-top:28px;">
            <div class="panel-heading">
                <strong><i class="fa fa-city"></i> Domestic Tariffs (City-to-City)</strong>
                <small style="font-size:12px;font-weight:normal;margin-left:10px;">
                    Used for Domestic shipments while in International / Freight mode. The portal quote calculator will match Origin City → Destination City.
                </small>
            </div>
            <div class="panel-body">
                <p class="text-muted" style="font-size:12px;margin-bottom:16px;">
                    Each origin city has its own rate sheet (e.g. Nairobi → Nakuru, Nairobi → Mombasa). Select an origin city, download the template,
                    fill in all destination cities and the rate you charge for each route, then upload. These rates take priority over the zone-based tariff table for Domestic service when set.
                </p>

                <div id="domestic-tariff-alert" class="alert" style="display:none;margin-bottom:12px;"></div>

                <!-- Upload panel -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="font-size:13px;"><strong><i class="fa fa-upload"></i> Upload Tariff for an Origin City</strong></div>
                            <div class="panel-body">
                                <form id="domestic-tariff-form">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Origin City <span class="text-danger">*</span></label>
                                        <select class="form-control" id="domestic_origin_city_select" style="font-size:12px;" required>
                                            <option value="">-- Select origin city --</option>
                                            <?php foreach (($service_point_cities ?? []) as $sp): ?>
                                            <option value="<?php echo htmlspecialchars($sp['name']); ?>"><?php echo htmlspecialchars($sp['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label style="font-size:12px;">CSV File <span class="text-danger">*</span></label>
                                        <input type="file" name="domestic_tariff_csv" id="domestic_tariff_csv_file" accept=".csv,.txt" class="form-control" required>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fa fa-upload"></i> Upload &amp; Apply
                                        </button>
                                        <button type="button" class="btn btn-default btn-sm" id="domestic-template-btn" onclick="downloadDomesticTemplate()" disabled>
                                            <i class="fa fa-download"></i> Download Template
                                        </button>
                                    </div>
                                </form>
                                <div style="margin-top:10px;font-size:11px;color:#888;">
                                    <strong>CSV Columns:</strong> destination_city, weight_min_kg, weight_max_kg, rate_type (flat/per_kg), rate
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="font-size:13px;"><strong><i class="fa fa-list"></i> Uploaded Origin Cities</strong></div>
                            <div class="panel-body" style="padding:0;">
                                <table class="table table-bordered table-condensed" style="font-size:12px;margin-bottom:0;" id="domestic-cities-table">
                                    <thead style="background:#f5f5f5;">
                                        <tr>
                                            <th>Origin City</th>
                                            <th>Rate Rows</th>
                                            <th style="width:120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="domestic-cities-body">
                                        <?php if (empty($domestic_tariff_origins)): ?>
                                        <tr id="domestic-empty-row"><td colspan="3" class="text-center text-muted" style="padding:12px;">No domestic city tariffs uploaded yet.</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($domestic_tariff_origins as $o): ?>
                                        <tr id="domestic-row-<?php echo htmlspecialchars($o['origin_city']); ?>">
                                            <td><strong><?php echo htmlspecialchars($o['origin_city']); ?></strong></td>
                                            <td><span class="badge"><?php echo $o['rate_count']; ?></span></td>
                                            <td>
                                                <button class="btn btn-info btn-xs" onclick="viewDomesticRates('<?php echo addslashes($o['origin_city']); ?>')" title="View rates">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <button class="btn btn-danger btn-xs" onclick="deleteDomesticTariff('<?php echo addslashes($o['origin_city']); ?>', this)" title="Delete all rates for this origin">
                                                    <i class="fa fa-trash"></i>
                                                </button>
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

                <!-- Rate viewer -->
                <div id="domestic-rates-viewer" style="display:none;margin-top:16px;">
                    <div style="background:#fff9ec;border:1px solid #f0e0bf;border-radius:6px;padding:14px 18px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <strong style="font-size:13px;"><i class="fa fa-table"></i> Rates for: <span id="domestic-rates-title"></span></strong>
                            <button class="btn btn-default btn-xs" onclick="document.getElementById('domestic-rates-viewer').style.display='none'">
                                <i class="fa fa-times"></i> Close
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed" style="font-size:12px;margin-bottom:0;">
                                <thead style="background:#fdf2da;">
                                    <tr>
                                        <th>Destination</th>
                                        <th>Min (kg)</th>
                                        <th>Max (kg)</th>
                                        <th>Rate Type</th>
                                        <th>Rate</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="domestic-rates-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Zone Destinations -->
        <h5 style="margin-top:28px;"><i class="fa fa-map"></i> Zone Definitions</h5>
        <p class="text-muted" style="font-size:12px;">These zones define the destinations used in the self-service quote calculator on the client portal.</p>

        <div id="zone-save-alert" class="alert" style="display:none;margin-bottom:10px;"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-condensed" style="font-size:13px;">
                <thead style="background:#f5f5f5;">
                    <tr>
                        <th style="width:80px;">Zone</th>
                        <th style="width:180px;">Name</th>
                        <th>Destinations (shown on portal)</th>
                        <th style="width:100px;">Available</th>
                        <th style="width:80px;">Save</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tariff_zones as $z): ?>
                    <tr>
                        <td><strong>Zone <?php echo htmlspecialchars($z['zone_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($z['name']); ?></td>
                        <td>
                            <textarea class="form-control zone-destinations" rows="2"
                                data-zone-id="<?php echo $z['id']; ?>"
                                style="font-size:12px;resize:vertical;"><?php echo htmlspecialchars($z['destinations']); ?></textarea>
                        </td>
                        <td class="text-center">
                            <select class="form-control zone-available" data-zone-id="<?php echo $z['id']; ?>" style="font-size:12px;">
                                <option value="1" <?php echo $z['is_available'] ? 'selected' : ''; ?>>Yes</option>
                                <option value="0" <?php echo !$z['is_available'] ? 'selected' : ''; ?>>No (Contact Us)</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-success btn-xs" onclick="saveZone(<?php echo $z['id']; ?>, this)">
                                <i class="fa fa-save"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
var PARCEL_RATE_SAVE_URL        = '<?php echo admin_url("courier/settings/save_parcel_rate"); ?>';
var TARIFF_UPLOAD_URL           = '<?php echo admin_url("courier/settings/upload_tariff_csv"); ?>';
var FCL_UPLOAD_URL              = '<?php echo admin_url("courier/settings/upload_fcl_csv"); ?>';
var TARIFF_DELETE_URL           = '<?php echo admin_url("courier/settings/delete_tariff_rate"); ?>';
var TARIFF_ZONE_URL             = '<?php echo admin_url("courier/settings/update_tariff_zone"); ?>';
var ORIGIN_TARIFF_UPLOAD_URL    = '<?php echo admin_url("courier/settings/upload_origin_tariff_csv"); ?>';
var ORIGIN_TARIFF_DELETE_URL    = '<?php echo admin_url("courier/settings/delete_origin_tariff"); ?>';
var ORIGIN_TARIFF_RATES_URL     = '<?php echo admin_url("courier/settings/origin_tariff_rates_json"); ?>';
var ORIGIN_TARIFF_TEMPLATE_URL  = '<?php echo admin_url("courier/settings/download_origin_tariff_template"); ?>';
var COUNTRIES_URL               = '<?php echo base_url("courier/portal/get_countries"); ?>';
var DOMESTIC_TARIFF_UPLOAD_URL   = '<?php echo admin_url("courier/settings/upload_domestic_tariff_csv"); ?>';
var DOMESTIC_TARIFF_DELETE_URL   = '<?php echo admin_url("courier/settings/delete_domestic_tariff"); ?>';
var DOMESTIC_TARIFF_RATES_URL    = '<?php echo admin_url("courier/settings/domestic_tariff_rates_json"); ?>';
var DOMESTIC_TARIFF_TEMPLATE_URL = '<?php echo admin_url("courier/settings/download_domestic_tariff_template"); ?>';
var CSRF_TOKEN_NAME             = '<?php echo $this->security->get_csrf_token_name(); ?>';
var CSRF_TOKEN_HASH             = '<?php echo $this->security->get_csrf_hash(); ?>';

function saveParcelRate() {
    var rate     = parseFloat(document.getElementById('pr_rate_per_kg').value)  || 0;
    var handling = parseFloat(document.getElementById('pr_handling_fee').value) || 0;
    var vat      = parseFloat(document.getElementById('pr_vat_rate').value)     || 0;
    var btn      = document.getElementById('pr-save-btn');
    var alertEl  = document.getElementById('parcel-rate-alert');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
    alertEl.style.display = 'none';

    var fd = new FormData();
    fd.append('courier_parcel_rate_per_kg',  rate);
    fd.append('courier_parcel_handling_fee', handling);
    fd.append('courier_parcel_vat_rate',     vat);
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);

    fetch(PARCEL_RATE_SAVE_URL, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Save Rate Settings';
            alertEl.className = 'alert ' + (res.success ? 'alert-success' : 'alert-danger');
            alertEl.textContent = res.success ? 'Rate settings saved successfully.' : (res.message || 'Save failed.');
            alertEl.style.display = 'block';
            updateParcelPreview();
            setTimeout(function() { alertEl.style.display = 'none'; }, 3500);
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Save Rate Settings';
            alertEl.className = 'alert alert-danger';
            alertEl.textContent = 'Network error. Please try again.';
            alertEl.style.display = 'block';
        });
}

function updateParcelPreview() {
    var rate     = parseFloat(document.getElementById('pr_rate_per_kg').value)  || 0;
    var handling = parseFloat(document.getElementById('pr_handling_fee').value) || 0;
    var vat      = parseFloat(document.getElementById('pr_vat_rate').value)     || 0;
    var sampleKg = 5;
    var transport = rate * sampleKg;
    var subtotal  = transport + handling;
    var vatAmt    = subtotal * (vat / 100);
    var total     = subtotal + vatAmt;
    var fmt = function(n) { return 'KES ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); };
    document.getElementById('prv_transport').textContent = fmt(transport);
    document.getElementById('prv_handling').textContent  = fmt(handling);
    document.getElementById('prv_vat').textContent       = fmt(vatAmt);
    document.getElementById('prv_total').textContent     = fmt(total);
}

['pr_rate_per_kg','pr_handling_fee','pr_vat_rate'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', updateParcelPreview);
});
updateParcelPreview();

document.getElementById('tariff-csv-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var file = document.getElementById('tariff_csv_file').files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('tariff_csv', file);
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    var btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
    var alertEl = document.getElementById('tariff-upload-alert');
    alertEl.style.display = 'none';
    fetch(TARIFF_UPLOAD_URL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-upload"></i> Upload & Apply';
            alertEl.className = 'alert ' + (res.success ? 'alert-success' : 'alert-danger');
            alertEl.style.display = 'block';
            if (res.success) {
                alertEl.textContent = 'Done! ' + res.inserted + ' added, ' + res.updated + ' updated, ' + res.errors + ' skipped. Reload to see changes.';
            } else {
                alertEl.textContent = res.message || 'Upload failed.';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-upload"></i> Upload & Apply';
            alertEl.className = 'alert alert-danger';
            alertEl.style.display = 'block';
            alertEl.textContent = 'Network error. Please try again.';
        });
});

document.getElementById('fcl-csv-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var file = document.getElementById('fcl_csv_file').files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('fcl_csv', file);
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    var btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
    var alertEl = document.getElementById('fcl-upload-alert');
    alertEl.style.display = 'none';
    fetch(FCL_UPLOAD_URL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-upload"></i> Upload FCL Rates';
            alertEl.className = 'alert ' + (res.success ? 'alert-success' : 'alert-danger');
            alertEl.style.display = 'block';
            if (res.success) {
                alertEl.textContent = 'FCL rates updated: ' + res.updated + ' containers updated, ' + res.errors + ' skipped. Reload to see changes.';
            } else {
                alertEl.textContent = res.message || 'Upload failed.';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-upload"></i> Upload FCL Rates';
            alertEl.className = 'alert alert-danger';
            alertEl.style.display = 'block';
            alertEl.textContent = 'Network error. Please try again.';
        });
});

function deleteTariffRate(id, btn) {
    if (!confirm('Delete this rate row?')) return;
    var fd = new FormData();
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    fetch(TARIFF_DELETE_URL + '/' + id, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(function(res) {
            if (res.success) {
                var row = document.getElementById('rate-row-' + id);
                if (row) row.remove();
            } else {
                alert('Could not delete row.');
            }
        });
}

// -- Domestic City-to-City Tariffs --
(function initDomesticTariffPanel() {
    var sel = document.getElementById('domestic_origin_city_select');
    if (!sel) return;
    sel.addEventListener('change', function() {
        var tplBtn = document.getElementById('domestic-template-btn');
        if (tplBtn) tplBtn.disabled = !this.value;
    });
})();

function downloadDomesticTemplate() {
    var origin = document.getElementById('domestic_origin_city_select').value;
    if (!origin) { alert('Please select an origin city first.'); return; }
    window.location.href = DOMESTIC_TARIFF_TEMPLATE_URL + '?origin=' + encodeURIComponent(origin);
}

var domesticTariffForm = document.getElementById('domestic-tariff-form');
if (domesticTariffForm) {
    domesticTariffForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var origin = document.getElementById('domestic_origin_city_select').value;
        var file   = document.getElementById('domestic_tariff_csv_file').files[0];
        var alertEl = document.getElementById('domestic-tariff-alert');

        if (!origin) { alertEl.className = 'alert alert-danger'; alertEl.textContent = 'Please select an origin city.'; alertEl.style.display = 'block'; return; }
        if (!file)   { alertEl.className = 'alert alert-danger'; alertEl.textContent = 'Please choose a CSV file.'; alertEl.style.display = 'block'; return; }

        var fd = new FormData();
        fd.append('origin_city', origin);
        fd.append('domestic_tariff_csv', file);
        fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);

        var btn = this.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
        alertEl.style.display = 'none';

        fetch(DOMESTIC_TARIFF_UPLOAD_URL, { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-upload"></i> Upload & Apply';
                alertEl.className = 'alert ' + (res.success ? 'alert-success' : 'alert-danger');
                alertEl.style.display = 'block';
                if (res.success) {
                    alertEl.textContent = 'Done for ' + res.origin + ': ' + res.inserted + ' added, ' + res.updated + ' updated, ' + res.errors + ' skipped.';
                    refreshDomesticRow(res.origin);
                } else {
                    alertEl.textContent = res.message || 'Upload failed.';
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-upload"></i> Upload & Apply';
                alertEl.className = 'alert alert-danger';
                alertEl.textContent = 'Network error. Please try again.';
                alertEl.style.display = 'block';
            });
    });
}

function refreshDomesticRow(origin) {
    fetch(DOMESTIC_TARIFF_RATES_URL + '?origin=' + encodeURIComponent(origin))
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success) return;
            var count = res.data.length;
            var tbody = document.getElementById('domestic-cities-body');
            var emptyRow = document.getElementById('domestic-empty-row');
            if (emptyRow) emptyRow.remove();
            var existing = document.getElementById('domestic-row-' + origin);
            if (existing) {
                existing.querySelector('.badge').textContent = count;
            } else {
                var tr = document.createElement('tr');
                tr.id = 'domestic-row-' + origin;
                tr.innerHTML = '<td><strong>' + escHtml(origin) + '</strong></td>'
                    + '<td><span class="badge">' + count + '</span></td>'
                    + '<td>'
                    + '<button class="btn btn-info btn-xs" onclick="viewDomesticRates(\'' + escJs(origin) + '\')" title="View rates"><i class="fa fa-eye"></i></button> '
                    + '<button class="btn btn-danger btn-xs" onclick="deleteDomesticTariff(\'' + escJs(origin) + '\', this)" title="Delete all rates for this origin"><i class="fa fa-trash"></i></button>'
                    + '</td>';
                tbody.appendChild(tr);
            }
        });
}

function viewDomesticRates(origin) {
    var viewer = document.getElementById('domestic-rates-viewer');
    var tbody  = document.getElementById('domestic-rates-body');
    var title  = document.getElementById('domestic-rates-title');
    title.textContent = origin;
    tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';
    viewer.style.display = 'block';
    viewer.scrollIntoView({behavior:'smooth', block:'nearest'});

    fetch(DOMESTIC_TARIFF_RATES_URL + '?origin=' + encodeURIComponent(origin))
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success || !res.data.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No rates found.</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            res.data.forEach(function(r) {
                var tr = document.createElement('tr');
                tr.id = 'dtr-' + r.id;
                tr.innerHTML = '<td>' + escHtml(r.destination_city) + '</td>'
                    + '<td>' + r.weight_min + '</td>'
                    + '<td>' + r.weight_max + '</td>'
                    + '<td><small class="text-muted">' + r.rate_type + '</small></td>'
                    + '<td>' + parseFloat(r.rate).toLocaleString(undefined, {minimumFractionDigits:2}) + '</td>'
                    + '<td><button class="btn btn-danger btn-xs" onclick="deleteDomesticRate(' + r.id + ', \'' + escJs(origin) + '\', this)"><i class="fa fa-trash"></i></button></td>';
                tbody.appendChild(tr);
            });
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading rates.</td></tr>';
        });
}

function deleteDomesticTariff(origin, btn) {
    if (!confirm('Delete ALL tariff rates for origin city "' + origin + '"?\nThis cannot be undone.')) return;
    var fd = new FormData();
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    fetch(DOMESTIC_TARIFF_DELETE_URL + '/' + encodeURIComponent(origin), { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.success) {
                var row = document.getElementById('domestic-row-' + origin);
                if (row) row.remove();
                var tbody = document.getElementById('domestic-cities-body');
                if (!tbody.querySelector('tr')) {
                    tbody.innerHTML = '<tr id="domestic-empty-row"><td colspan="3" class="text-center text-muted" style="padding:12px;">No domestic city tariffs uploaded yet.</td></tr>';
                }
                document.getElementById('domestic-rates-viewer').style.display = 'none';
            } else { alert('Could not delete.'); }
        });
}

function deleteDomesticRate(id, origin, btn) {
    if (!confirm('Delete this rate row?')) return;
    var fd = new FormData();
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    fetch('<?php echo admin_url("courier/settings/delete_domestic_tariff_rate"); ?>/' + id, { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.success) {
                var row = document.getElementById('dtr-' + id);
                if (row) row.remove();
                var originRow = document.getElementById('domestic-row-' + origin);
                if (originRow) {
                    var badge = originRow.querySelector('.badge');
                    if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent) - 1);
                }
            } else { alert('Could not delete row.'); }
        });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) {
    return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

function saveZone(id, btn) {
    var row = btn.closest('tr');
    var destinations = row.querySelector('.zone-destinations').value;
    var is_available = row.querySelector('.zone-available').value;
    var fd = new FormData();
    fd.append('id', id);
    fd.append('destinations', destinations);
    fd.append('is_available', is_available);
    fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    var alertEl = document.getElementById('zone-save-alert');
    fetch(TARIFF_ZONE_URL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i>';
            alertEl.className = 'alert ' + (res.success ? 'alert-success' : 'alert-danger');
            alertEl.textContent = res.success ? 'Zone saved.' : 'Save failed.';
            alertEl.style.display = 'block';
            setTimeout(function() { alertEl.style.display = 'none'; }, 3000);
        });
}
</script>



