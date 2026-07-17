<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.sp-card { background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:20px 24px; margin-bottom:20px; }
.sp-card h4 { margin-top:0; margin-bottom:16px; font-size:15px; font-weight:700; color:#333; border-bottom:1px solid #f0f0f0; padding-bottom:10px; }
.sp-badge { display:inline-block; background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:12px; font-size:11px; font-weight:600; padding:2px 10px; margin:3px 3px; }
.sp-delete-btn { background:none; border:none; color:#c0392b; cursor:pointer; font-size:13px; padding:2px 6px; }
.sp-delete-btn:hover { color:#922b21; }
.sp-row { display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border-radius:4px; }
.sp-row:nth-child(even) { background:#f9f9f9; }
.sp-name { font-size:13px; color:#333; flex:1; }
.sp-country-tag { font-size:11px; color:#1976d2; background:#e3f2fd; border:1px solid #90caf9; border-radius:10px; padding:1px 8px; margin-right:10px; white-space:nowrap; }
.upload-note { font-size:12px; color:#888; margin-top:6px; }
</style>

<div class="sp-card">
    <h4><i class="fa fa-map-marker" style="color:#2e7d32;margin-right:6px;"></i> Add Service Point</h4>
    <div class="row">
        <div class="col-md-4" style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;display:block;">Country</label>
            <select id="spCountryInput" class="form-control" style="width:100%;">
                <option value="">-- Select Country --</option>
                <?php foreach ($countries as $c): ?>
                <option value="<?php echo (int)$c->country_id; ?>"><?php echo htmlspecialchars($c->short_name); ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">The country these service points belong to.</small>
        </div>
        <div class="col-md-6" style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;display:block;">Service Point Name</label>
            <div class="input-group">
                <input type="text" id="spNameInput" class="form-control" placeholder="e.g. Kisumu CBD" maxlength="255">
                <span class="input-group-btn">
                    <button class="btn btn-success" id="btnAddSp">
                        <i class="fa fa-plus"></i> Add
                    </button>
                </span>
            </div>
            <div id="spAddMsg" style="margin-top:6px;font-size:12px;"></div>
        </div>
    </div>
</div>

<div class="sp-card">
    <h4><i class="fa fa-upload" style="color:#1976d2;margin-right:6px;"></i> Bulk Upload via CSV</h4>
    <p style="font-size:13px;color:#555;margin-bottom:10px;">
        Upload a <strong>.csv</strong> file with one service point name per row (no header required, or use <code>name</code> as header — it will be skipped). Duplicates are ignored automatically.
    </p>
    <div class="row">
        <div class="col-md-4" style="margin-bottom:10px;">
            <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;display:block;">Country (for all rows in this file)</label>
            <select id="csvCountryInput" class="form-control">
                <option value="">-- Select Country --</option>
                <?php foreach ($countries as $c): ?>
                <option value="<?php echo (int)$c->country_id; ?>"><?php echo htmlspecialchars($c->short_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:4px;display:block;">CSV File</label>
            <input type="file" id="csvFileInput" accept=".csv,.txt" class="form-control">
            <div class="upload-note">Supported: CSV, TXT</div>
        </div>
        <div class="col-md-2" style="padding-top:22px;">
            <button class="btn btn-primary" id="btnUploadCsv">
                <i class="fa fa-upload"></i> Upload
            </button>
        </div>
    </div>
    <div id="csvUploadMsg" style="margin-top:8px;font-size:12px;"></div>
</div>

<div class="sp-card">
    <h4>
        <i class="fa fa-list" style="color:#555;margin-right:6px;"></i>
        Current Service Points
        <span id="spCount" class="badge" style="margin-left:8px;background:#2e7d32;"><?php echo count($service_points); ?></span>
    </h4>
    <div id="spListContainer">
        <?php if (empty($service_points)): ?>
            <p style="color:#aaa;font-size:13px;">No service points added yet.</p>
        <?php else: ?>
            <?php foreach ($service_points as $sp): ?>
            <div class="sp-row" id="sp-row-<?php echo (int)$sp['id']; ?>">
                <span class="sp-name"><?php echo htmlspecialchars($sp['name']); ?></span>
                <?php if (!empty($sp['country_name'])): ?>
                <span class="sp-country-tag"><?php echo htmlspecialchars($sp['country_name']); ?></span>
                <?php endif; ?>
                <button class="sp-delete-btn" onclick="deleteServicePoint(<?php echo (int)$sp['id']; ?>, this)" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
var CI_BASE_URL = '<?php echo admin_url("courier/settings/"); ?>';

function getCsrfToken() {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var c = cookies[i].trim();
        if (c.indexOf('csrf_cookie_name=') === 0) return c.substring('csrf_cookie_name='.length);
    }
    return '';
}

function showMsg(el, msg, ok) {
    el.innerHTML = msg;
    el.style.color = ok ? '#2e7d32' : '#c0392b';
}

document.getElementById('btnAddSp').addEventListener('click', function () {
    var name       = document.getElementById('spNameInput').value.trim();
    var countryId  = document.getElementById('spCountryInput').value;
    var msgEl      = document.getElementById('spAddMsg');
    if (!name) { showMsg(msgEl, 'Please enter a service point name.', false); return; }
    if (!countryId) { showMsg(msgEl, 'Please select a country first.', false); return; }

    var fd = new FormData();
    fd.append('name', name);
    fd.append('country_id', countryId);
    fd.append('csrf_test_name', getCsrfToken());

    fetch(CI_BASE_URL + 'add_service_point', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showMsg(msgEl, 'Service point added successfully.', true);
                document.getElementById('spNameInput').value = '';
                var container = document.getElementById('spListContainer');
                var noMsg = container.querySelector('p');
                if (noMsg) noMsg.remove();
                var div = document.createElement('div');
                div.className = 'sp-row';
                div.id = 'sp-row-' + res.id;
                div.innerHTML = '<span class="sp-name">' + escHtml(res.name) + '</span>'
                    + (res.country_name ? '<span class="sp-country-tag">' + escHtml(res.country_name) + '</span>' : '')
                    + '<button class="sp-delete-btn" onclick="deleteServicePoint(' + res.id + ', this)" title="Delete"><i class="fa fa-trash"></i></button>';
                container.appendChild(div);
                updateCount(1);
            } else {
                showMsg(msgEl, res.message || 'Error adding service point.', false);
            }
        })
        .catch(function () { showMsg(msgEl, 'Network error. Please try again.', false); });
});

document.getElementById('btnUploadCsv').addEventListener('click', function () {
    var fileInput  = document.getElementById('csvFileInput');
    var countryId  = document.getElementById('csvCountryInput').value;
    var msgEl      = document.getElementById('csvUploadMsg');
    if (!countryId) { showMsg(msgEl, 'Please select a country for this upload.', false); return; }
    if (!fileInput.files.length) { showMsg(msgEl, 'Please select a CSV file first.', false); return; }

    var fd = new FormData();
    fd.append('csv_file', fileInput.files[0]);
    fd.append('country_id', countryId);
    fd.append('csrf_test_name', getCsrfToken());

    showMsg(msgEl, 'Uploading…', true);
    fetch(CI_BASE_URL + 'upload_service_points_csv', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showMsg(msgEl, 'Done! Added: ' + res.inserted + ', Skipped (duplicates): ' + res.skipped + '.', true);
                fileInput.value = '';
                if (res.inserted > 0) {
                    setTimeout(function () { window.location.reload(); }, 1200);
                }
            } else {
                showMsg(msgEl, res.message || 'Upload failed.', false);
            }
        })
        .catch(function () { showMsg(msgEl, 'Network error. Please try again.', false); });
});

function deleteServicePoint(id, btn) {
    if (!confirm('Delete this service point?')) return;
    var fd = new FormData();
    fd.append('csrf_test_name', getCsrfToken());
    fetch(CI_BASE_URL + 'delete_service_point/' + id, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var row = document.getElementById('sp-row-' + id);
                if (row) row.remove();
                updateCount(-1);
            } else {
                alert('Could not delete service point.');
            }
        });
}

function updateCount(delta) {
    var el = document.getElementById('spCount');
    var current = parseInt(el.textContent) || 0;
    el.textContent = Math.max(0, current + delta);
}

function escHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
