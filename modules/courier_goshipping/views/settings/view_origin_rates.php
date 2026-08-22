<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'settings']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">
                    <div class="cgs-card__header">
                        <h4 class="cgs-card__title">
                            <i class="fa fa-globe"></i> International Tariffs for: <strong><?php echo htmlspecialchars($origin); ?></strong>
                        </h4>
                        <p class="text-muted" style="font-size:12px; margin:4px 0 0;">
                            Click any cell to edit its rate. Blank cells are editable too — type a rate and click away to save.
                        </p>
                    </div>

                        <?php if (empty($matrices)): ?>
                            <div class="alert alert-info">No rates uploaded for this origin country yet.</div>
                        <?php else: ?>
                            <!-- Nav tabs for multiple service types -->
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($matrices as $index => $m): ?>
                                    <?php $is_active = !empty($active_service) ? ($m['service'] === $active_service) : ($index === 0); ?>
                                    <li role="presentation" class="<?php echo $is_active ? 'active' : ''; ?>">
                                        <a href="#service_<?php echo md5($m['service']); ?>" aria-controls="service_<?php echo md5($m['service']); ?>" role="tab" data-toggle="tab">
                                            Service: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $m['service']))); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content" style="padding-top: 15px;">
                                <?php foreach ($matrices as $index => $m): ?>
                                    <?php $is_active = !empty($active_service) ? ($m['service'] === $active_service) : ($index === 0); ?>
                                    <div role="tabpanel" class="tab-pane <?php echo $is_active ? 'active' : ''; ?>" id="service_<?php echo md5($m['service']); ?>">

                                        <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                                            <button type="button" class="cgs-btn cgs-btn--outline cgs-btn--sm add-weight-band-btn"
                                                    data-service="<?php echo htmlspecialchars($m['service']); ?>">
                                                <i class="fa fa-plus"></i> Add Weight Band
                                            </button>
                                            <button type="button" class="cgs-btn cgs-btn--outline cgs-btn--sm add-per-kg-btn"
                                                    data-service="<?php echo htmlspecialchars($m['service']); ?>">
                                                <i class="fa fa-plus"></i> Add "Over Max Weight" Per-kg Rate
                                            </button>
                                            <span style="display:flex; align-items:center; gap:6px;">
                                                <input type="text" list="cgs-country-list-<?php echo md5($m['service']); ?>" class="cgs-add-dest-input" placeholder="Destination country..." style="font-size:12px; padding:5px 8px; border:1px solid #ccc; border-radius:4px; width:200px;">
                                                <datalist id="cgs-country-list-<?php echo md5($m['service']); ?>"></datalist>
                                                <button type="button" class="cgs-btn cgs-btn--outline cgs-btn--sm add-destination-btn" data-service="<?php echo htmlspecialchars($m['service']); ?>">
                                                    <i class="fa fa-plus"></i> Add Destination
                                                </button>
                                            </span>
                                            <span class="cgs-grid-save-status" style="font-size:12px; color:#888; align-self:center;"></span>
                                        </div>

                                        <!-- Excel-like scrolling container -->
                                        <div style="overflow: auto; max-height: 600px; width: 100%; border: 1px solid #ddd;">
                                            <table class="table table-bordered table-condensed table-hover cgs-table cgs-rate-grid"
                                                   data-service="<?php echo htmlspecialchars($m['service']); ?>"
                                                   style="white-space: nowrap; font-size: 13px; margin-bottom: 0;">
                                                <thead style="background: #f5f5f5; position: sticky; top: 0; z-index: 10;">
                                                    <tr>
                                                        <th style="position: sticky; left: 0; background: #f5f5f5; z-index: 11; border-right: 2px solid #ccc;">Weight / Destination</th>
                                                        <?php foreach ($m['destinations'] as $dest): ?>
                                                            <th class="text-center"><?php echo htmlspecialchars($dest); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($m['weights'] as $w): ?>
                                                        <?php
                                                            $row_is_per_kg = false;
                                                            foreach ($m['destinations'] as $dest) {
                                                                if (isset($m['data'][$w][$dest]) && $m['data'][$w][$dest]['rate_type'] === 'per_kg') {
                                                                    $row_is_per_kg = true;
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                        <tr data-weight-max="<?php echo htmlspecialchars($w); ?>">
                                                            <td style="position: sticky; left: 0; background: #fff; font-weight: bold; border-right: 2px solid #ccc; z-index: 5;">
                                                                <?php if ($row_is_per_kg): ?>
                                                                    Over <?php echo htmlspecialchars($w); ?> kg <span class="label label-default" style="font-weight:normal;">per kg</span>
                                                                <?php else: ?>
                                                                    <?php echo htmlspecialchars($w); ?> <?php echo is_numeric($w) ? 'kg' : ''; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <?php foreach ($m['destinations'] as $dest): ?>
                                                                <?php
                                                                    $cell = $m['data'][$w][$dest] ?? null;
                                                                    $weight_min = $cell['weight_min'] ?? 0;
                                                                    $rate_type  = $cell['rate_type']  ?? ($row_is_per_kg ? 'per_kg' : 'flat');
                                                                ?>
                                                                <td class="text-center" style="padding:0;<?php echo $cell ? 'background-color:#f9fff9;' : 'background-color:#fefefe;'; ?>">
                                                                    <input type="number" step="0.01" min="0"
                                                                           class="cgs-rate-cell"
                                                                           data-id="<?php echo $cell['id'] ?? 0; ?>"
                                                                           data-origin="<?php echo htmlspecialchars($origin); ?>"
                                                                           data-service="<?php echo htmlspecialchars($m['service']); ?>"
                                                                           data-dest="<?php echo htmlspecialchars($dest); ?>"
                                                                           data-weight-min="<?php echo htmlspecialchars($weight_min); ?>"
                                                                           data-weight-max="<?php echo htmlspecialchars($w); ?>"
                                                                           data-rate-type="<?php echo htmlspecialchars($rate_type); ?>"
                                                                           value="<?php echo $cell ? htmlspecialchars($cell['rate']) : ''; ?>"
                                                                           style="width:100%; border:none; text-align:center; padding:6px 4px; background:transparent; font-size:12px;">
                                                                </td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';
    var UPDATE_CELL_URL   = '<?php echo admin_url("courier_goshipping/settings/update_origin_tariff_cell"); ?>';
    var ADD_WEIGHT_URL    = '<?php echo admin_url("courier_goshipping/settings/add_origin_tariff_weight_band"); ?>';
    var ADD_DEST_URL      = '<?php echo admin_url("courier_goshipping/settings/add_origin_tariff_destination"); ?>';
    var COUNTRIES_URL     = '<?php echo base_url("courier_goshipping/tracker/get_countries"); ?>';
    var ORIGIN            = <?php echo json_encode($origin); ?>;

    function csrfBody(obj) {
        var fd = new FormData();
        Object.keys(obj).forEach(function (k) { fd.append(k, obj[k]); });
        fd.append(CSRF_NAME, CSRF_HASH);
        return fd;
    }

    function flashStatus(table, msg, isError) {
        var status = table.closest('div[role="tabpanel"]').querySelector('.cgs-grid-save-status');
        if (!status) return;
        status.textContent = msg;
        status.style.color = isError ? '#c62828' : '#2e7d32';
        setTimeout(function () { status.textContent = ''; }, 2500);
    }

    // ---- Inline cell editing ----
    document.addEventListener('blur', function (e) {
        var input = e.target;
        if (!input.classList || !input.classList.contains('cgs-rate-cell')) return;

        var val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            if (input.value.trim() === '') return; // still blank, nothing to save
            return;
        }

        var table = input.closest('table');
        fetch(UPDATE_CELL_URL, {
            method: 'POST',
            body: csrfBody({
                id:                  input.dataset.id || 0,
                origin_country:      input.dataset.origin,
                destination_country: input.dataset.dest,
                service_type:        input.dataset.service,
                weight_min:          input.dataset.weightMin,
                weight_max:          input.dataset.weightMax,
                rate_type:           input.dataset.rateType,
                rate:                val
            }),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                input.dataset.id = res.id;
                input.closest('td').style.backgroundColor = '#f9fff9';
                flashStatus(table, 'Saved', false);
            } else {
                flashStatus(table, res.message || 'Save failed', true);
            }
        })
        .catch(function () { flashStatus(table, 'Network error', true); });
    }, true);

    // ---- Add Weight Band ----
    document.querySelectorAll('.add-weight-band-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var w = prompt('New weight band (kg), e.g. 75:');
            if (!w) return;
            w = parseFloat(w);
            if (isNaN(w) || w <= 0) { alert('Enter a valid weight in kg.'); return; }

            var service = btn.dataset.service;
            fetch(ADD_WEIGHT_URL, {
                method: 'POST',
                body: csrfBody({ origin_country: ORIGIN, service_type: service, weight_max: w }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) { alert(res.message || 'Could not add weight band.'); return; }
                addWeightRow(service, w, res.destinations, 'flat');
            });
        });
    });

    // ---- Add "Over Max Weight" per-kg overage band ----
    document.querySelectorAll('.add-per-kg-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var service = btn.dataset.service;
            var table = document.querySelector('.cgs-rate-grid[data-service="' + service + '"]');
            var maxKnown = 0;
            table.querySelectorAll('tbody tr').forEach(function (tr) {
                var w = parseFloat(tr.dataset.weightMax);
                if (!isNaN(w) && w > maxKnown && w < 999999) maxKnown = w;
            });
            if (maxKnown <= 0) { alert('Add at least one destination weight band first.'); return; }

            fetch(ADD_WEIGHT_URL, {
                method: 'POST',
                body: csrfBody({ origin_country: ORIGIN, service_type: service, weight_max: 999999 }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) { alert(res.message || 'Could not add per-kg band.'); return; }
                addWeightRow(service, 999999, res.destinations, 'per_kg', maxKnown);
            });
        });
    });

    function addWeightRow(service, weightMax, destinations, rateType, weightMin) {
        weightMin = weightMin || 0;
        var table = document.querySelector('.cgs-rate-grid[data-service="' + service + '"]');
        if (!table) return;

        var headerDests = [];
        table.querySelectorAll('thead th').forEach(function (th, i) { if (i > 0) headerDests.push(th.textContent.trim()); });

        var tr = document.createElement('tr');
        tr.dataset.weightMax = weightMax;
        var label = (rateType === 'per_kg') ? ('Over ' + weightMin + ' kg <span class="label label-default" style="font-weight:normal;">per kg</span>') : (weightMax + ' kg');
        tr.innerHTML = '<td style="position:sticky;left:0;background:#fff;font-weight:bold;border-right:2px solid #ccc;z-index:5;">' + label + '</td>';

        headerDests.forEach(function (dest) {
            var td = document.createElement('td');
            td.className = 'text-center';
            td.style.padding = '0';
            td.style.backgroundColor = '#fefefe';
            var input = document.createElement('input');
            input.type = 'number'; input.step = '0.01'; input.min = '0';
            input.className = 'cgs-rate-cell';
            input.dataset.id = 0;
            input.dataset.origin = ORIGIN;
            input.dataset.service = service;
            input.dataset.dest = dest;
            input.dataset.weightMin = weightMin;
            input.dataset.weightMax = weightMax;
            input.dataset.rateType = rateType;
            input.style.cssText = 'width:100%;border:none;text-align:center;padding:6px 4px;background:transparent;font-size:12px;';
            td.appendChild(input);
            tr.appendChild(td);
        });

        table.querySelector('tbody').appendChild(tr);
    }

    // ---- Add Destination ----
    document.querySelectorAll('.add-destination-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var service = btn.dataset.service;
            var wrap = btn.closest('span');
            var input = wrap.querySelector('.cgs-add-dest-input');
            var dest = input.value.trim();
            if (!dest) { alert('Type a destination country first.'); return; }

            fetch(ADD_DEST_URL, {
                method: 'POST',
                body: csrfBody({ origin_country: ORIGIN, service_type: service, destination_country: dest }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) { alert(res.message || 'Could not add destination.'); return; }
                addDestinationColumn(service, res.destination, res.weights);
                input.value = '';
            });
        });
    });

    function addDestinationColumn(service, dest, weights) {
        var table = document.querySelector('.cgs-rate-grid[data-service="' + service + '"]');
        if (!table) return;

        var th = document.createElement('th');
        th.className = 'text-center';
        th.textContent = dest;
        table.querySelector('thead tr').appendChild(th);

        table.querySelectorAll('tbody tr').forEach(function (tr) {
            var wmax = tr.dataset.weightMax;
            var wmin = 0;
            var firstInput = tr.querySelector('.cgs-rate-cell');
            var rateType = firstInput ? firstInput.dataset.rateType : 'flat';
            if (firstInput) wmin = firstInput.dataset.weightMin;

            var td = document.createElement('td');
            td.className = 'text-center';
            td.style.padding = '0';
            td.style.backgroundColor = '#fefefe';
            var input = document.createElement('input');
            input.type = 'number'; input.step = '0.01'; input.min = '0';
            input.className = 'cgs-rate-cell';
            input.dataset.id = 0;
            input.dataset.origin = ORIGIN;
            input.dataset.service = service;
            input.dataset.dest = dest;
            input.dataset.weightMin = wmin;
            input.dataset.weightMax = wmax;
            input.dataset.rateType = rateType;
            input.style.cssText = 'width:100%;border:none;text-align:center;padding:6px 4px;background:transparent;font-size:12px;';
            td.appendChild(input);
            tr.appendChild(td);
        });
    }

    // Populate country datalists (shared across all service tabs on this page)
    fetch(COUNTRIES_URL).then(function (r) { return r.json(); }).then(function (res) {
        if (res.status !== 'success' || !res.data) return;
        document.querySelectorAll('datalist[id^="cgs-country-list-"]').forEach(function (dl) {
            res.data.forEach(function (c) {
                var opt = document.createElement('option');
                opt.value = c.name;
                dl.appendChild(opt);
            });
        });
    });
})();
</script>
<?php init_tail(); ?>
</body>
</html>
