<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="cgs-card">
                    <div class="cgs-card__header">
                        <h4 class="cgs-card__title"><i class="fa fa-calculator"></i> GO Shipping Cargo Quote Calculator</h4>
                    </div>

                        <p class="text-muted mbot20">
                            Select cargo type, destination zone, and weight to calculate an instant quote. All amounts are in KES and include VAT at <?php echo number_format($parcel_vat_rate ?? 16, 0); ?>%.
                        </p>

                        <div id="quote-error-msg" class="alert alert-danger" style="display:none;"></div>

                        <!-- Step 1: Cargo Type -->
                        <div class="form-group">
                            <label class="control-label">1. Cargo Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-default text-center q-type-card" id="qcard-document" onclick="selectCargoType('document')" style="cursor:pointer; border: 2px solid #1a5276; background: #eaf0fb; padding: 15px; border-radius: 8px;">
                                        <i class="fa fa-file-text-o fa-2x" style="color: #1a5276;"></i>
                                        <h5 style="color: #1a5276; font-weight: bold; margin-top: 10px;">Document</h5>
                                        <small class="text-muted">%  350 g</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel panel-default text-center q-type-card" id="qcard-parcel" onclick="selectCargoType('parcel')" style="cursor:pointer; border: 2px solid #ddd; padding: 15px; border-radius: 8px;">
                                        <i class="fa fa-archive fa-2x text-muted"></i>
                                        <h5 class="text-muted font-weight-bold" style="margin-top: 10px; font-weight: bold;">Package / Parcel</h5>
                                        <small class="text-muted">1 kg and above</small>
                                        <?php if (!empty($parcel_rate_per_kg) && $parcel_rate_per_kg > 0): ?>
                                            <small class="text-success" style="display:block; font-weight:bold;">
                                                KES <?php echo number_format($parcel_rate_per_kg, 2); ?>/kg
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="q_cargo_type" value="document">
                        </div>

                        <!-- Step 2: Destination Zone -->
                        <div class="form-group mtop15">
                            <label class="control-label">2. Destination Zone</label>
                            <select class="form-control selectpicker" data-live-search="true" id="q_zone">
                                <option value="">-- Select destination zone --</option>
                            </select>
                            <small id="q_zone_desc" class="help-block text-muted" style="display:none; font-style:italic;"></small>
                        </div>

                        <!-- Step 3: Weight -->
                        <div id="q-weight-section" style="display:none; margin-top:15px;">
                            <label class="control-label">3. Weight &amp; Dimensions</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Actual Weight (kg) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="q_weight" placeholder="e.g. 5.0" min="0.1" step="0.1">
                                    </div>
                                </div>
                            </div>

                            <div class="mtop10">
                                <a href="#" onclick="toggleDims(event)"><i class="fa fa-expand"></i> Add dimensions for volumetric weight (optional)</a>
                            </div>
                            <div id="q-dims-section" class="row" style="display:none; margin-top:10px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Length (cm)</label>
                                        <input type="number" class="form-control" id="q_length" placeholder="0" min="0" step="0.1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Width (cm)</label>
                                        <input type="number" class="form-control" id="q_width" placeholder="0" min="0" step="0.1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Height (cm)</label>
                                        <input type="number" class="form-control" id="q_height" placeholder="0" min="0" step="0.1">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted">Volumetric weight = L - W - H / 6000. Chargeable weight = max(actual, volumetric).</small>
                                </div>
                            </div>
                        </div>

                        <!-- Calculate Button -->
                        <div class="mtop25 text-right">
                            <button class="cgs-btn cgs-btn--primary" id="q-calc-btn" onclick="runCalculateQuote()">
                                <i class="fa fa-calculator"></i> Calculate Quote
                            </button>
                        </div>

                        <!-- Quote Result -->
                        <div id="quote-result-panel" class="mtop25" style="display:none; border:1px solid #cce5ff; border-radius: 4px; overflow:hidden;">
                            <div style="background:#cce5ff; padding:10px 15px; font-weight:bold; color:#004085;">
                                <i class="fa fa-list-alt"></i> Price Breakdown
                            </div>
                            <table class="table table-bordered no-margin cgs-table">
                                <tbody>
                                    <tr class="active">
                                        <td width="50%">Cargo Type</td>
                                        <td id="qr_cargo" class="bold"></td>
                                    </tr>
                                    <tr>
                                        <td>Destination Zone</td>
                                        <td id="qr_zone" class="bold"></td>
                                    </tr>
                                    <tr class="active">
                                        <td>Actual Weight</td>
                                        <td id="qr_actual_wt"></td>
                                    </tr>
                                    <tr id="qr_vol_row">
                                        <td>Volumetric Weight</td>
                                        <td id="qr_vol_wt"></td>
                                    </tr>
                                    <tr class="active">
                                        <td class="bold">Chargeable Weight</td>
                                        <td id="qr_charge_wt" class="bold"></td>
                                    </tr>
                                    <tr>
                                        <td>Transport Charge</td>
                                        <td id="qr_transport"></td>
                                    </tr>
                                    <tr class="active">
                                        <td>Handling Fee</td>
                                        <td id="qr_handling"></td>
                                    </tr>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td id="qr_subtotal"></td>
                                    </tr>
                                    <tr class="active">
                                        <td>VAT (<span id="qr_vat_label"></span>%)</td>
                                        <td id="qr_vat"></td>
                                    </tr>
                                    <tr style="background:#004085; color:#fff;">
                                        <td class="bold" style="font-size:16px;">Total Estimated Cost</td>
                                        <td id="qr_total" class="bold" style="font-size:16px;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    var QUOTE_CALC_URL = '<?php echo base_url("courier_goshipping/portal/calculate_quote"); ?>';
    var TARIFF_ZONES = <?php echo json_encode($tariff_zones ?? []); ?>;

    $(function() {
        var sel = document.getElementById('q_zone');
        TARIFF_ZONES.forEach(function(z) {
            var opt = document.createElement('option');
            opt.value = z.zone_code.toLowerCase();
            opt.textContent = z.name;
            opt.dataset.dest = z.destinations || '';
            opt.dataset.avail = z.is_available;
            sel.appendChild(opt);
        });
        if (TARIFF_ZONES.length === 0) {
            sel.innerHTML = '<option value="">-- No zones configured yet --</option>';
        }
        $('#q_zone').selectpicker('refresh');

        $('#q_zone').on('change', function() {
            var opt = $(this).find('option:selected')[0];
            var desc = document.getElementById('q_zone_desc');
            if (opt && opt.dataset.dest) {
                desc.textContent = opt.dataset.dest;
                desc.style.display = 'block';
            } else {
                desc.style.display = 'none';
            }
            document.getElementById('quote-result-panel').style.display = 'none';
        });
    });

    function selectCargoType(type) {
        document.getElementById('q_cargo_type').value = type;
        var docCard  = document.getElementById('qcard-document');
        var parCard  = document.getElementById('qcard-parcel');
        var weightSec = document.getElementById('q-weight-section');

        if (type === 'document') {
            docCard.style.borderColor = '#1a5276';
            docCard.style.background  = '#eaf0fb';
            docCard.querySelector('i').className = 'fa fa-file-text-o fa-2x';
            docCard.querySelector('i').style.color = '#1a5276';
            docCard.querySelector('h5').className = 'bold';
            docCard.querySelector('h5').style.color = '#1a5276';

            parCard.style.borderColor = '#ddd';
            parCard.style.background  = '#fff';
            parCard.querySelector('i').className = 'fa fa-archive fa-2x text-muted';
            parCard.querySelector('i').style.color = '';
            parCard.querySelector('h5').className = 'text-muted bold';
            parCard.querySelector('h5').style.color = '';

            weightSec.style.display = 'none';
        } else {
            parCard.style.borderColor = '#1a5276';
            parCard.style.background  = '#eaf0fb';
            parCard.querySelector('i').className = 'fa fa-archive fa-2x';
            parCard.querySelector('i').style.color = '#1a5276';
            parCard.querySelector('h5').className = 'bold';
            parCard.querySelector('h5').style.color = '#1a5276';

            docCard.style.borderColor = '#ddd';
            docCard.style.background  = '#fff';
            docCard.querySelector('i').className = 'fa fa-file-text-o fa-2x text-muted';
            docCard.querySelector('i').style.color = '';
            docCard.querySelector('h5').className = 'text-muted bold';
            docCard.querySelector('h5').style.color = '';

            weightSec.style.display = 'block';
        }
        document.getElementById('quote-result-panel').style.display = 'none';
        document.getElementById('quote-error-msg').style.display = 'none';
    }

    function toggleDims(e) {
        e.preventDefault();
        $('#q-dims-section').slideToggle();
    }

    function fmtKes(n) { return 'KES ' + Number(n).toLocaleString('en-KE', {minimumFractionDigits:2,maximumFractionDigits:2}); }

    function runCalculateQuote() {
        var cargoType = document.getElementById('q_cargo_type').value;
        var zone      = document.getElementById('q_zone').value;
        var weight    = parseFloat(document.getElementById('q_weight') && document.getElementById('q_weight').value) || 0;
        var length    = parseFloat(document.getElementById('q_length') && document.getElementById('q_length').value) || 0;
        var width     = parseFloat(document.getElementById('q_width')  && document.getElementById('q_width').value)  || 0;
        var height    = parseFloat(document.getElementById('q_height') && document.getElementById('q_height').value) || 0;

        var errEl = document.getElementById('quote-error-msg');
        errEl.style.display = 'none';

        if (!zone) {
            errEl.textContent = 'Please select a destination zone.';
            errEl.style.display = 'block';
            return;
        }
        if (cargoType === 'parcel' && weight <= 0) {
            errEl.textContent = 'Please enter a valid weight.';
            errEl.style.display = 'block';
            return;
        }

        var btn = document.getElementById('q-calc-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Calculating...';

        var fd = new FormData();
        fd.append('cargo_type', cargoType);
        fd.append('zone', zone);
        fd.append('weight', weight);
        fd.append('length', length);
        fd.append('width',  width);
        fd.append('height', height);

        fetch(QUOTE_CALC_URL, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-calculator"></i> Calculate Quote';
                if (res.status !== 'success') {
                    errEl.textContent = res.message || 'Could not calculate quote.';
                    errEl.style.display = 'block';
                    document.getElementById('quote-result-panel').style.display = 'none';
                    return;
                }
                var d = res.data;
                document.getElementById('qr_cargo').textContent      = d.cargo_type.charAt(0).toUpperCase() + d.cargo_type.slice(1);
                document.getElementById('qr_zone').textContent       = d.zone_name;
                document.getElementById('qr_actual_wt').textContent  = d.actual_weight + ' kg';
                document.getElementById('qr_charge_wt').textContent  = d.chargeable_weight + ' kg';
                document.getElementById('qr_transport').textContent  = fmtKes(d.transport);
                document.getElementById('qr_handling').textContent   = fmtKes(d.handling);
                document.getElementById('qr_subtotal').textContent   = fmtKes(d.subtotal);
                document.getElementById('qr_vat').textContent        = fmtKes(d.vat_amount);
                document.getElementById('qr_vat_label').textContent  = d.vat_rate;
                document.getElementById('qr_total').textContent      = fmtKes(d.total);
                
                var volRow = document.getElementById('qr_vol_row');
                if (d.volumetric_weight > 0) {
                    document.getElementById('qr_vol_wt').textContent = d.volumetric_weight + ' kg';
                    volRow.style.display = '';
                } else {
                    volRow.style.display = 'none';
                }
                document.getElementById('quote-result-panel').style.display = 'block';
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-calculator"></i> Calculate Quote';
                errEl.textContent = 'Network error. Please try again.';
                errEl.style.display = 'block';
            });
    }
</script>
