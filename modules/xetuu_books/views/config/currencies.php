<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Currencies &amp; Exchange Rates</span>
                <div style="display:flex;gap:6px;align-items:center;">
                    <button type="button" id="xb-sync-perfex-btn" class="btn btn-default btn-sm" title="Sync between Perfex native currencies (/admin/currencies) and Xetuu Books">
                        <i class="fa fa-exchange"></i> Sync with Perfex
                    </button>
                    <button type="button" id="xb-seed-world-btn" class="btn btn-info btn-sm" title="Add all ISO 4217 world currencies to both Xetuu Books and Perfex CRM">
                        <i class="fa fa-globe"></i> Add All World Currencies
                    </button>
                    <button type="button" id="xb-fetch-rates-btn" class="btn btn-primary xb-btn-primary btn-sm">
                        <i class="fa fa-refresh"></i> Update Rates (Live)
                    </button>
                </div>
            </div>
            <div class="xb-card-body">
                <div id="xb-rates-msg" class="mbot15" style="display:none;"></div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Symbol</th>
                            <th>Full Name</th>
                            <th class="text-right">Rate (1 unit = X KES)</th>
                            <th class="text-right">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($currencies as $cur): ?>
                        <tr id="cur-row-<?php echo $cur->id; ?>">
                            <td>
                                <strong><?php echo $cur->name; ?></strong>
                                <?php if ($cur->isdefault): ?>
                                <span class="label label-success" style="font-size:10px;margin-left:5px;">Default</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $cur->symbol; ?></td>
                            <td><?php echo $cur->full_name ?? ''; ?></td>
                            <td class="text-right">
                                <?php if ($cur->isdefault): ?>
                                <span class="text-muted">1.0000 (base)</span>
                                <?php else: ?>
                                <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;">
                                    <input type="number" step="any" min="0.0001" id="rate-<?php echo $cur->id; ?>"
                                           value="<?php echo number_format((float)$cur->rate, 4, '.', ''); ?>"
                                           class="form-control input-sm" style="width:110px;text-align:right;">
                                    <button type="button" class="btn btn-success btn-xs" onclick="saveRate(<?php echo $cur->id; ?>)">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right text-muted" id="cur-updated-<?php echo $cur->id; ?>"><?php echo isset($cur->updated_at) ? $cur->updated_at : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="text-muted small">Rates are fetched from <strong>api.exchangerate-api.com</strong> (free). Click <em>Update Rates</em> to refresh all rates against KES.</p>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('xb-fetch-rates-btn').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fetching...';
    var msg = document.getElementById('xb-rates-msg');

    var csrf1 = xbGetCsrf(); var csrfBody1 = Object.keys(csrf1).map(k => encodeURIComponent(k)+'='+encodeURIComponent(csrf1[k])).join('&');
    fetch('<?php echo admin_url('xetuu_books/ajax/fetch_exchange_rates'); ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: csrfBody1
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh"></i> Update Rates (Live)';
        if (data.success) {
            msg.style.display = 'block';
            msg.className = 'alert alert-success';
            msg.innerHTML = '<i class="fa fa-check"></i> Updated ' + data.updated + ' currencies. API date: ' + (data.date || '') + '. <a href="">Refresh page to see new rates.</a>';
        } else {
            msg.style.display = 'block';
            msg.className = 'alert alert-danger';
            msg.innerHTML = '<i class="fa fa-times"></i> ' + (data.error || data.message || 'Unknown error');
        }
    })
    .catch(function(e){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh"></i> Update Rates (Live)';
        msg.style.display = 'block';
        msg.className = 'alert alert-danger';
        msg.innerHTML = 'Network error: ' + e.message;
    });
});

function xbGetCsrf() {
    if (typeof csrfData !== 'undefined' && csrfData.token_name && csrfData.hash) {
        var obj = {};
        obj[csrfData.token_name] = csrfData.hash;
        return obj;
    }
    // Fallback: use PHP-rendered hash (valid only on first call)
    var obj = {};
    obj['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
    return obj;
}

function saveRate(curId) {
    var rate = parseFloat(document.getElementById('rate-' + curId).value);
    if (!rate || rate <= 0) { alert('Enter a valid rate greater than 0'); return; }
    var data = Object.assign({id: curId, rate: rate}, xbGetCsrf());
    $.post('<?php echo admin_url('xetuu_books/ajax/save_currency'); ?>', data, function(res) {
        try { var r = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e) { var r = {success: false}; }
        if (r.success) {
            // Update csrfData hash if server returns a new one (CI3 regenerates on each request)
            if (r.csrf_hash && typeof csrfData !== 'undefined') { csrfData.hash = r.csrf_hash; csrfData.formatted[csrfData.token_name] = r.csrf_hash; }
            var now = new Date().toISOString().replace('T', ' ').substring(0, 19);
            document.getElementById('cur-updated-' + curId).textContent = now;
            // Brief green flash
            var row = document.getElementById('cur-row-' + curId);
            row.style.background = '#f0fdf4';
            setTimeout(function() { row.style.background = ''; }, 1200);
        } else {
            alert('Failed to save rate: ' + (r.message || 'Unknown error'));
        }
    });
}

// ── Seed All World Currencies ──────────────────────────────────────────────
document.getElementById('xb-seed-world-btn').addEventListener('click', function () {
    var btn = this;
    var msg = document.getElementById('xb-rates-msg');
    if (!confirm('This will add all ISO 4217 world currencies to both Xetuu Books and Perfex CRM (existing currencies are not overwritten). Continue?')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adding...';

    var csrf2 = xbGetCsrf(); var csrfBody2 = Object.keys(csrf2).map(k => encodeURIComponent(k)+'='+encodeURIComponent(csrf2[k])).join('&');
    fetch('<?php echo admin_url('xetuu_books/ajax/seed_world_currencies'); ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: csrfBody2
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-globe"></i> Add All World Currencies';
        msg.style.display = 'block';
        if (data.success) {
            msg.className = 'alert alert-success';
            msg.innerHTML = '<i class="fa fa-check"></i> ' + data.message + ' <a href="">Refresh page to see all currencies.</a>';
        } else {
            msg.className = 'alert alert-danger';
            msg.innerHTML = '<i class="fa fa-times"></i> ' + (data.message || 'Unknown error');
        }
    })
    .catch(function(e){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-globe"></i> Add All World Currencies';
        msg.style.display = 'block';
        msg.className = 'alert alert-danger';
        msg.innerHTML = 'Network error: ' + e.message;
    });
});

// ── Sync with Perfex Currencies ────────────────────────────────────────────
document.getElementById('xb-sync-perfex-btn').addEventListener('click', function () {
    var btn = this;
    var msg = document.getElementById('xb-rates-msg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';

    var csrf3 = xbGetCsrf(); var csrfBody3 = Object.keys(csrf3).map(k => encodeURIComponent(k)+'='+encodeURIComponent(csrf3[k])).join('&');
    fetch('<?php echo admin_url('xetuu_books/ajax/sync_from_perfex'); ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: csrfBody3
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-exchange"></i> Sync with Perfex';
        msg.style.display = 'block';
        if (data.success) {
            msg.className = 'alert alert-success';
            msg.innerHTML = '<i class="fa fa-check"></i> ' + data.message + ' <a href="">Refresh page.</a>';
        } else {
            msg.className = 'alert alert-danger';
            msg.innerHTML = '<i class="fa fa-times"></i> ' + (data.message || 'Unknown error');
        }
    })
    .catch(function(e){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-exchange"></i> Sync with Perfex';
        msg.style.display = 'block';
        msg.className = 'alert alert-danger';
        msg.innerHTML = 'Network error: ' + e.message;
    });
});
</script>
