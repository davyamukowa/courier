<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>

<?php
// Include Flatpickr CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';

// Include Select2 CSS
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';

?>


<style>
    /* ── Required field indicator ─────────────────────────────────── */
    .req-star {
        color: #dc3545;
        font-weight: 700;
        font-size: 12px;
        margin-left: 2px;
    }
    /* Pickup optional note */
    .pickup-optional-badge {
        display: inline-block;
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 8px;
        margin-left: 8px;
        vertical-align: middle;
    }

    /* Custom Styles for Phone Input with Country Code */
    .phone-input-group {
        display: flex;
        align-items: center; /* Vertically center the inputs */
    }

    .country-code {
        width: 60px; /* Set a fixed width for the country code input */
        text-align: center; /* Center the text inside */
        border: 1px solid #ccc;
        border-radius: 4px;
        border-top-right-radius: 0 !important; /* Rounded corners on the left */
        border-bottom-right-radius: 0 !important; /* Rounded corners on the left */
        padding: 10px; /* Padding for better appearance */
        font-size: 14px; /* Adjust font size */
        background-color: #f9f9f9; /* Background color */
        cursor: none; /* Cursor style for read-only input */
    }


    .phone {
        background-color: #f9fafb; /* Equivalent to bg-gray-50 */
        border: 1px solid #d1d5db;
        border-left: 0px !important;
        color: #111827; /* Equivalent to text-gray-900 */
        font-size: 0.875rem;
        border-radius: 0.375rem; /* Equivalent to rounded-lg */
        border-top-left-radius: 0 !important; /* Remove top-left border radius */
        border-bottom-left-radius: 0 !important; /* Remove top-left border radius */
        padding: 0.625rem; /* Equivalent to p-2.5 */
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
    }


    .error-message {
        color: red; /* Red color for error messages */
        font-size: 12px; /* Smaller font size for error messages */
        margin-top: 5px; /* Spacing above the error message */
    }


    /* Switch styling */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }

    /* Hide default checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* Slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 15px;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: .4s;
    }

    /* Checked state */
    input:checked + .slider {
        background-color: #28a745;
    }

    input:checked + .slider:before {
        transform: translateX(30px);
    }

    /* Checkbox Styles */
    .custom-checkbox {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .custom-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        margin-left: 10px;
        margin-bottom: 5px;
    }

    .checkbox-text {
        font-weight: bold;
        color: #333;
    }

    .select2-container .select2-selection--single {
        background-color: #f9fafb;
        border: 1px solid #d1d5db;
        color: #111827;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        padding: 5px 10px 10px 10px;
        width: 100%;
        height: 40px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select2-selection__arrow {
        transform: translateY(30%);
    }

</style>
<!-- Assume the $mode_type is set in a script tag in your HTML -->
<script>
    // Set this based on your PHP code or context
    const modeType = '<?php echo $mode_type; ?>'; // Adjust according to how you get the value
    const isLocalCourier = <?php echo ((($courier_type ?? 'international') === 'local') || ($type ?? '') === 'domestic') ? 'true' : 'false'; ?>;

    document.addEventListener('DOMContentLoaded', function () {

        const pickupCheckbox = document.getElementById('addPickupCheckbox');
        const pickupSection = document.getElementById('optionalPickupSection');

        //toggle commercial invoice view
        const toggleSwitch = document.getElementById('toggleSwitch');
        const attachInvoice = document.getElementById('invoice_attachment');
        const inputInvoice = document.getElementById('invoice_input');

        const showPickupSection = <?php echo isset($show_pickup_section) && $show_pickup_section ? 'true' : 'false'; ?>;
        const showCommercialAttachmentSection = <?php echo isset($show_commercial_value_attachment_section) && $show_commercial_value_attachment_section ? 'true' : 'false'; ?>;


        if (showPickupSection) {
            pickupSection.style.display = 'block';
            pickupCheckbox.checked = true; // Ensure the checkbox is checked
        }

        if (showCommercialAttachmentSection) {
            attachInvoice.style.display = 'block';
            inputInvoice.style.display = 'none';
            toggleSwitch.checked = true;
        } else {
            attachInvoice.style.display = 'none';
            inputInvoice.style.display = 'block';
            toggleSwitch.checked = false;
        }

        toggleSwitch.addEventListener('change', () => {
            if (toggleSwitch.checked) {
                attachInvoice.style.display = 'block';
                inputInvoice.style.display = 'none';
            } else {
                attachInvoice.style.display = 'none';
                inputInvoice.style.display = 'block';
            }
        });


        if (showPickupSection) {
            pickupSection.style.display = 'block';
            pickupCheckbox.checked = true; // Ensure the checkbox is checked
        }

        function setPickupRequired(required) {
            pickupSection.querySelectorAll('.pickup-required-field').forEach(function(el) {
                if (required) {
                    el.setAttribute('required', 'required');
                } else {
                    el.removeAttribute('required');
                }
            });
        }

        pickupCheckbox.addEventListener('change', function () {
            if (this.checked) {
                pickupSection.style.display = 'block';
                setPickupRequired(true);
            } else {
                pickupSection.style.display = 'none';
                setPickupRequired(false);
            }
        });

        // Sync on initial load
        if (showPickupSection) {
            setPickupRequired(true);
        }

        function attachRemoveEvent(button) {
            button.addEventListener('click', function () {
                this.closest('tr').remove();
            });
        }

        // Attach event to initial remove package buttons
        const packageRemoveButtons = document.getElementsByClassName('remove-package');
        for (let i = 0; i < packageRemoveButtons.length; i++) {
            attachRemoveEvent(packageRemoveButtons[i]);
        }
        // Attach event to initial remove fcl-package buttons
        const fclPackageRemoveButtons = document.getElementsByClassName('remove-fcl-package');
        for (let i = 0; i < fclPackageRemoveButtons.length; i++) {
            attachRemoveEvent(fclPackageRemoveButtons[i]);
        }

        // Attach event to initial remove fcl-package buttons
        const CommercialItemsRemoveButtons = document.getElementsByClassName('remove-commercial-item');
        for (let i = 0; i < CommercialItemsRemoveButtons.length; i++) {
            attachRemoveEvent(CommercialItemsRemoveButtons[i]);
        }

        // Add new row functionality for FCL package
        window.addFCLPackage = function () {
            const packageTable = document.getElementById('fclPackageTable').getElementsByTagName('tbody')[0];
            const newRow = packageTable.insertRow();

            newRow.innerHTML = `
            <td><input name="amount[]" class="form-control" type="number" step="any"></td>
            <td><textarea name="package_description[]" class="custom-textarea" rows="3"></textarea></td>
            <td><select class="custom-select" name="fcl_options[]" id="">
                <option value="20'DV">20'DV</option>
                <option value="40'DV">40'DV</option>
                <option value="20'HC">20'HC</option>
                <option value="40'HC">40'HC</option>
                <option value="20'RF">20'RF</option>
                <option value="40'RF">40'RF</option>
                <option value="20'FR">20'FR</option>
                <option value="40'FR">40'FR</option>
                <option value="RoRo">RoRo</option>
            </select></td>
            <td><button type="button" class="btn btn-danger remove-fcl-package"><i class="fa fa-trash"></i></button></td>
        `;

            attachRemoveEvent(newRow.getElementsByClassName('remove-fcl-package')[0]);
        }

        // Add new row functionality for Commercial Items
        window.addCommercialItem = function () {
            const commercialItemsTable = document.getElementById('commercialItemsTable').getElementsByTagName('tbody')[0];
            const newRow = commercialItemsTable.insertRow();

            newRow.innerHTML = `
            <td><input name="commodity_quantity[]" class="form-control amount" type="number" step="any"></td>
            <td><textarea name="commodity_description[]" class="custom-textarea" rows="3"></textarea></td>
            <td><input name="declared_value[]" class="form-control chargeable-weight" type="number" step="any"></td>
            <td><button type="button" class="btn btn-danger remove-commercial-item"><i class="fa fa-trash"></i></button></td>
        `;

            attachRemoveEvent(newRow.getElementsByClassName('remove-commercial-item')[0]);
        }


        // ── POD dropdown: merge API towns + custom service points ────────────
        var POD_SP_JSON_URL = '<?php echo admin_url("courier_logistic/settings/service_points_json"); ?>';

        // Start with PHP-rendered custom service points as the base
        window.SP_POD_OPTIONS = <?php
            $sp_html = '<option value="">POD</option>';
            if (!empty($service_points)) {
                foreach ($service_points as $_sp) {
                    $sp_html .= '<option value="' . htmlspecialchars($_sp['name'], ENT_QUOTES) . '">'
                              . htmlspecialchars($_sp['name']) . '</option>';
                }
            }
            echo json_encode($sp_html);
        ?>;

        function loadPODOptions(countryId, countryName) {
            if (!countryId && !countryName) return;

            var apiPromise = (countryName && countryName.trim())
                ? fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ country: countryName.trim() })
                  }).then(function (r) { return r.json(); }).catch(function () { return { error: true }; })
                : Promise.resolve({ error: true });

            var customPromise = countryId
                ? fetch(POD_SP_JSON_URL + '?country_id=' + countryId)
                      .then(function (r) { return r.json(); }).catch(function () { return { data: [] }; })
                : Promise.resolve({ data: [] });

            Promise.all([apiPromise, customPromise]).then(function (results) {
                var apiCities    = (!results[0].error && Array.isArray(results[0].data)) ? results[0].data : [];
                var customCities = (results[1] && Array.isArray(results[1].data))        ? results[1].data : [];

                var customSet = new Set(customCities.map(function (c) { return c.toLowerCase(); }));
                var merged    = customCities.slice();
                apiCities.forEach(function (city) {
                    if (!customSet.has(city.toLowerCase())) merged.push(city);
                });
                merged.sort();

                // Build new options HTML
                var html = '<option value="">POD</option>';
                var customLower = new Set(customCities.map(function (c) { return c.toLowerCase(); }));
                merged.forEach(function (city) {
                    var label = customLower.has(city.toLowerCase()) ? '★ ' + city : city;
                    html += '<option value="' + city.replace(/"/g, '&quot;') + '">' + label + '</option>';
                });

                // Update global variable (used by addNormalPackage for new rows)
                window.SP_POD_OPTIONS = html;

                // Update all existing POD selects
                document.querySelectorAll('.pod-input').forEach(function (sel) {
                    var current = sel.value;
                    sel.innerHTML = html;
                    if (current) sel.value = current; // restore any existing selection
                });
            });
        }

        // Auto-load on page ready using sender's country
        (function () {
            var senderSel = document.getElementById('sender_country_id');
            if (senderSel && senderSel.value) {
                var countryName = senderSel.options[senderSel.selectedIndex]
                    ? senderSel.options[senderSel.selectedIndex].text : '';
                loadPODOptions(senderSel.value, countryName);
            }
        })();

        // Reload POD options when sender country changes
        document.addEventListener('DOMContentLoaded', function () {
            var senderSel = document.getElementById('sender_country_id');
            if (senderSel) {
                senderSel.addEventListener('change', function () {
                    var name = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
                    loadPODOptions(this.value, name);
                });
            }
        });

        // Add new row functionality for normal package
        window.addNormalPackage = function () {
            const packageTable = document.getElementById('packageTable').getElementsByTagName('tbody')[0];
            const newRow = packageTable.insertRow();

            newRow.innerHTML = `
            <td><input name="amount[]" class="form-control amount" type="number" step="any"></td>
            <td><textarea name="package_description[]" class="custom-textarea" rows="3"></textarea></td>
            <td><input name="weight[]" class="form-control weight" type="number" step="any"></td>
            <td><input name="length[]" class="form-control length" type="number" step="any"></td>
            <td><input name="width[]" class="form-control width" type="number" step="any"></td>
            <td><input name="height[]" class="form-control height" type="number" step="any"></td>
            <td><input name="weight_vol[]" class="form-control weight-vol" type="number" step="any"></td>
            <?php if (($courier_type ?? 'international') !== 'local' && ($type ?? '') === 'international'): ?>
            <td><input type="text" readonly name="chargeable_weight[]" class="form-control chargeable-weight" value="0" style="background:#eef7ee; font-weight:600; cursor:default; min-width:80px;"></td>
            <?php else: ?>
            <input type="hidden" name="chargeable_weight[]" class="chargeable-weight" value="0">
            <td style="width:110px;"><select name="pod[]" class="form-control pod-input">${window.SP_POD_OPTIONS}</select></td>
            <td style="min-width:120px;"><input name="unit_price[]" class="form-control unit-price" type="number" step="any" placeholder="0.00"></td>
            <?php endif; ?>
            <td><button type="button" class="btn btn-danger remove-package"><i class="fa fa-trash"></i></button></td>
        `;

            attachRemoveEvent(newRow.getElementsByClassName('remove-package')[0]);
            attachVolumetricWeightCalculation(newRow);
            updateTotalChargeableWeight();
        }

        function attachVolumetricWeightCalculation(row) {
            const lengthInput = row.querySelector('.length');
            const quantityInput = row.querySelector('.amount');
            const widthInput = row.querySelector('.width');
            const heightInput = row.querySelector('.height');
            const weightVolInput = row.querySelector('.weight-vol');
            const chargeableWeightInput = row.querySelector('.chargeable-weight');
            const weightInput = row.querySelector('.weight');

            function calculateVolumetricWeight() {
                const length = parseFloat(lengthInput.value) || 0;
                const amount = parseFloat(quantityInput.value) || 0;
                const width = parseFloat(widthInput.value) || 0;
                const height = parseFloat(heightInput.value) || 0;
                const actualWeight = parseFloat(weightInput.value) || 0;

                // For local courier: chargeable weight = gross weight; volumetric shown for reference only
                if (isLocalCourier) {
                    const volWt = (length * width * height) / parseInt(<?php echo $dimensional_factor[0]->value; ?>);
                    weightVolInput.value = volWt.toFixed(2);
                    chargeableWeightInput.value = actualWeight.toFixed(2);
                    return;
                }

                // International/freight: determine chargeable weight based on modeType
                let chargeableWeight = 0;
                switch (modeType) {
                    case 'lcl':
                        const seaVolumetricWeight = (length * width * height) / parseInt(<?php echo $dimensional_factor[3]->value; ?>);
                        chargeableWeight = Math.max(actualWeight, seaVolumetricWeight);
                        weightVolInput.value = seaVolumetricWeight.toFixed(2);
                        chargeableWeightInput.value = chargeableWeight.toFixed(2) * amount;
                        break;
                    case 'air_consolidation':
                        const airConsolidationVolumetricWeight = (length * width * height) / parseInt(<?php echo $dimensional_factor[1]->value; ?>);
                        weightVolInput.value = airConsolidationVolumetricWeight.toFixed(2);
                        chargeableWeightInput.value = actualWeight.toFixed(2) * amount;
                        break;
                    case 'air_freight':
                        const airFreightVolumetricWeight = (length * width * height) / parseInt(<?php echo $dimensional_factor[2]->value; ?>);
                        chargeableWeight = Math.max(actualWeight, airFreightVolumetricWeight);
                        weightVolInput.value = airFreightVolumetricWeight.toFixed(2);
                        chargeableWeightInput.value = chargeableWeight.toFixed(2) * amount;
                        break;
                    case 'sea_consolidation':
                        chargeableWeight = (length * width * height) / 1000000;
                        weightVolInput.value = chargeableWeight.toFixed(2);
                        chargeableWeightInput.value = chargeableWeight.toFixed(2) * amount;
                        break;
                    default:
                        const volumetricWeight = (length * width * height) / parseInt(<?php echo $dimensional_factor[0]->value; ?>);
                        chargeableWeight = Math.max(actualWeight, volumetricWeight);
                        weightVolInput.value = volumetricWeight.toFixed(2);
                        chargeableWeightInput.value = chargeableWeight.toFixed(2) * amount;
                }

                // Keep total chargeable weight display in sync after every recalculation
                updateTotalChargeableWeight();
            }

            // Attach the event listeners
            lengthInput.addEventListener('input', calculateVolumetricWeight);
            widthInput.addEventListener('input', calculateVolumetricWeight);
            heightInput.addEventListener('input', calculateVolumetricWeight);
            weightInput.addEventListener('input', calculateVolumetricWeight);
            quantityInput.addEventListener('input', calculateVolumetricWeight);
        }

        // ── Total Chargeable Weight ─────────────────────────────────────────
        function updateTotalChargeableWeight() {
            var total = 0;
            if (isLocalCourier) {
                // Local: charge is based on gross weight per unit
                document.querySelectorAll('#packageTable tbody .weight').forEach(function(inp) {
                    total += parseFloat(inp.value) || 0;
                });
            } else {
                // International: chargeable_weight[] already contains MAX(actual,vol) × qty per row
                document.querySelectorAll('#packageTable tbody .chargeable-weight').forEach(function(inp) {
                    total += parseFloat(inp.value) || 0;
                });
            }
            var el = document.getElementById('totalChargeableWeight');
            if (el) el.value = total.toFixed(2);
        }

        // Attach calculation to the initial row
        const initialRow = document.querySelector('#packageTable tbody tr');
        if (initialRow) {
            attachVolumetricWeightCalculation(initialRow);
            // Wire weight change to total chargeable weight
            var wEl = initialRow.querySelector('.weight');
            if (wEl) wEl.addEventListener('input', updateTotalChargeableWeight);
        }

        // ── Auto-inject red asterisks on required field labels ────────────
        // Fields validated as required server-side (or HTML required)
        var requiredFieldIds = [
            'sender_first_name','sender_last_name','sender_phone_number',
            'sender_email','sender_address',
            'recipient_first_name','recipient_last_name','recipient_phone_number',
            'recipient_email','recipient_address',
            'company_name','contact_name','contact_phone','contact_email','contact_address',
            'recipient_company_name','recipient_contact_name','recipient_contact_phone',
            'recipient_contact_email','recipient_contact_address',
            'shipping_mode'
        ];
        requiredFieldIds.forEach(function(fid) {
            var lbl = document.querySelector('label[for="' + fid + '"]');
            if (lbl && !lbl.querySelector('.req-star')) {
                lbl.insertAdjacentHTML('beforeend', '<span class="req-star">*</span>');
            }
        });
        // Also mark all inputs/selects/textareas that already have HTML required
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(el) {
            if (!el.id) return;
            var lbl = document.querySelector('label[for="' + el.id + '"]');
            if (lbl && !lbl.querySelector('.req-star')) {
                lbl.insertAdjacentHTML('beforeend', '<span class="req-star">*</span>');
            }
        });

        // ── State loader ──────────────────────────────────────────────────────
        // Populates a state <select> from the local tbl_courier_country_states table
        // via admin/courier/states — no external API, works on any server.
        var STATES_URL = '<?php echo admin_url("courier_logistic/states"); ?>';

        function loadStates(countryId, stateSelectId, preselectValue) {
            var sel = document.getElementById(stateSelectId);
            if (!sel) return;

            if (!countryId) {
                sel.innerHTML = '<option value="">Select State</option>';
                if (typeof $ !== 'undefined' && $(sel).data('select2')) $(sel).trigger('change');
                return;
            }

            sel.innerHTML = '<option value="">Loading...</option>';

            $.ajax({
                url:      STATES_URL,
                type:     'GET',
                data:     { country_id: countryId },
                dataType: 'json',
                success: function (res) {
                    var html = '<option value="">Select State</option>';
                    if (res.states && res.states.length) {
                        res.states.forEach(function (s) {
                            var selected = (preselectValue && (s.id == preselectValue || s.name === preselectValue)) ? ' selected' : '';
                            html += '<option value="' + s.id + '"' + selected + '>' + s.name + '</option>';
                        });
                    } else {
                        html = '<option value="">No states available</option>';
                    }
                    sel.innerHTML = html;
                    if (typeof $ !== 'undefined' && $(sel).data('select2')) $(sel).trigger('change');
                },
                error: function () {
                    sel.innerHTML = '<option value="">Could not load states</option>';
                }
            });
        }

        // Country → State mapping for every pair in this form
        var stateMap = {
            'sender_country_id':           'sender_state_id',
            'contact_country_id':          'contact_state_id',
            'recipient_country_id':        'recipient_state_id',
            'recipient_contact_country_id':'recipient_contact_state_id',
            'pickup_country_id':           'pickup_state_id'
        };

        // Wire change events
        Object.keys(stateMap).forEach(function (cid) {
            var sid = stateMap[cid];
            var cEl = document.getElementById(cid);
            if (!cEl) return;
            cEl.addEventListener('change', function () {
                loadStates(this.value, sid);
            });
        });

        // Auto-load states for any country already selected on page load (e.g. after validation error)
        Object.keys(stateMap).forEach(function (cid) {
            var sid = stateMap[cid];
            var cEl = document.getElementById(cid);
            var sEl = document.getElementById(sid);
            if (cEl && cEl.value) {
                var preselect = sEl ? (sEl.dataset.preselect || '') : '';
                loadStates(cEl.value, sid, preselect);
            }
        });

    });
</script>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>

        <div class="row">
            <?php echo form_open(admin_url('courier_logistic/shipments/store'), [
                'id' => 'create-shipment-form',
                'enctype' => 'multipart/form-data'
            ]); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div style="width:100%;" class="panel-body">
                        <div style="margin-bottom:25px">
                            <a class="custom-button custom-button-green"
                               href="<?php echo admin_url('courier_logistic/shipments/main'); ?>">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                <span style="margin-left:8px;">Shipments Dashboard</span>
                            </a>
                        </div>

                        <div class="flex-container" style="margin-bottom:12px;">
                            <div>
                                <h3 style="margin:0; font-weight:800; color:#1a1a1a; letter-spacing:-.3px;">
                                    Create Shipment
                                </h3>
                                <span style="display:inline-block; margin-top:4px; background:#28a745; color:#fff; font-size:12px; font-weight:700; padding:2px 10px; border-radius:20px; letter-spacing:.5px;">
                                    <?php
                                    $label = ucfirst($type);
                                    if (!empty($mode) && $mode !== 'none') {
                                        $label .= ' — ' . ucfirst($mode);
                                        if (!empty($mode_type) && $mode_type !== 'none') {
                                            $label .= ' / ' . ucfirst(str_replace('_', ' ', $mode_type));
                                        }
                                    }
                                    echo htmlspecialchars($label);
                                    ?>
                                </span>
                            </div>
                            <a class="custom-button custom-button-green"
                               href="<?php echo !empty($mode) ? admin_url('courier_logistic/shipments?type=' . $type . '&mode=' . $mode . '&mode_type=' . $mode_type) : admin_url('courier_logistic/shipments?type=' . $type); ?>">
                                <i class="fa fa-list" style="margin-right:6px;"></i> View Shipments
                            </a>
                            <?php if ($type === 'international'): ?>
                                <input type="hidden" name="type" value="international">
                            <?php else: ?>
                                <input type="hidden" name="type" value="domestic">
                            <?php endif; ?>

                            <input type="hidden" name="mode" value="<?php echo $mode; ?>">

                            <input type="hidden" name="mode_type" value="<?php echo $mode_type; ?>">

                        </div>

                        <hr class="hr-panel-heading"/>

                        <!-- Record Shipment -->
                        <section class="custom-section">


                            <div class="custom-container">
                                <!-- Radio buttons to toggle between Individual and Company -->

                                <div class="custom-form-group"
                                     style="margin-bottom: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                                    <label class="custom-label"
                                           style="display: block; font-weight: bold; font-size: 18px;">Sender
                                        Type</label>
                                    <div id="type_selection"
                                         style="display: flex; margin-top:-18px; gap: 20px; align-items: center;">
                                        <label style="display: flex; align-items: center; font-size: 16px; cursor: pointer;">
                                            <input type="radio" name="sender_type" value="individual" checked
                                                   style="margin-right: 8px; accent-color: #007bff;">
                                            Individual
                                        </label>
                                        <label style="display: flex; align-items: center; font-size: 16px; cursor: pointer;">
                                            <input type="radio" name="sender_type" value="company"
                                                   style="margin-right: 8px; accent-color: #007bff;">
                                            Company
                                        </label>

                                        <label style="padding-left:40px; text-align:right; margin-top:-5px; align-items: center; font-size: 15px; cursor: pointer;">
                                            <label for="currency_id" class="custom-label"
                                                   style="display: block; font-weight: bold; margin-top:-12px; margin-bottom: 5px; font-size: 18px;">Shipping
                                                Currency
                                            </label>
                                            <?php echo form_dropdown('currency_id', array_column($currencies, 'name', 'id'), set_value('currency_id', get_base_currency()->id), ['id' => 'currency_id', 'class' => 'custom-select']); ?>
                                            <?php echo form_error('currency_id', '<div class="error-message">', '</div>'); ?>
                                        </label>
                                    </div>
                                </div>


                                <!-- Company Section (Hidden by default) -->
                                <div id="company_section" class="custom-form-grid"
                                     style="display: none; margin-top:70px;">
                                    <div class="row section-container">
                                        <div class="section-label">Company</div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="company_name" class="custom-label">Company Name</label>
                                                    <?php echo form_input(['id' => 'company_name', 'name' => 'company_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Company Name', 'value' => set_value('company_name')]); ?>
                                                    <?php echo form_error('company_name', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="company_kra_pin" class="custom-label">KRA PIN <span style="color:#999;font-weight:normal;">(optional)</span></label>
                                                    <?php echo form_input(['id' => 'company_kra_pin', 'name' => 'company_kra_pin', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'e.g. P052209979X', 'value' => set_value('company_kra_pin')]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="contact_name" class="custom-label">Contact Name</label>
                                                    <?php echo form_input(['id' => 'contact_name', 'name' => 'contact_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Contact Name', 'value' => set_value('contact_name')]); ?>
                                                    <?php echo form_error('contact_name', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($type === 'international'): ?>
                                            <div style="padding-left:15px; padding-right:15px;" class="row">
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="contact_country_id"
                                                               class="custom-label">Country</label>
                                                        <?php
                                                        // Check if $contact_country_id is set, otherwise default to form validation data
                                                        $selected_contact_country_id = isset($user_country->country_id) ? $user_country->country_id : set_value('contact_country_id');

                                                        echo form_dropdown(
                                                            'contact_country_id',
                                                            array_column($countries, 'short_name', 'country_id'),
                                                            $selected_contact_country_id,
                                                            [
                                                                'id' => 'contact_country_id',
                                                                'class' => 'custom-select',
                                                                'style' => 'width:100%;'
                                                            ]
                                                        );
                                                        ?>
                                                        <?php echo form_error('contact_country_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="sender_state_id"
                                                               class="custom-label">State</label>
                                                        <select style="width:100%;" name="contact_state_id"
                                                                id="contact_state_id"
                                                                class="custom-select">
                                                            <option value="" selected>Select State</option>
                                                        </select>
                                                        <?php echo form_error('contact_state_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <?php
                                                    $contact_country_code = set_value('contact_country_code')
                                                        ?: (isset($user_country->calling_code) ? '+' . $user_country->calling_code : '+254');
                                                    ?>
                                                    <label for="contact_phone" class="custom-label">Contact
                                                        Phone</label>
                                                    <div class="phone-input-group">
                                                        <!-- Country Code Input -->
                                                        <input style="border-right:transparent;" type="text"
                                                               id="contact_country_code" name="contact_country_code"
                                                               class="country-code"
                                                               value="<?php echo $contact_country_code; ?>" readonly>
                                                        <?php echo form_input(['id' => 'contact_phone', 'name' => 'contact_phone', 'type' => 'text', 'class' => 'phone', 'placeholder' => 'Phone Number', 'value' => set_value('contact_phone')]); ?>
                                                    </div>
                                                    <!-- Hidden Field for Combined Number -->
                                                    <input type="hidden" id="contact_full_phone_number"
                                                           name="contact_full_phone_number" value="">
                                                    <?php echo form_error('contact_phone', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="contact_email" class="custom-label">Contact
                                                        Email</label>
                                                    <?php echo form_input(['id' => 'contact_email', 'name' => 'contact_email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Email', 'value' => set_value('contact_email')]); ?>
                                                    <?php echo form_error('contact_email', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="contact_address" class="custom-label">Address</label>
                                                    <textarea id="contact_address" name="contact_address"
                                                              class="custom-textarea"
                                                              rows="3"
                                                              placeholder="Enter your address here..."><?php echo set_value('contact_address'); ?></textarea>
                                                    <?php echo form_error('contact_address', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <div id="address_type" style="display: flex; gap: 20px;">
                                                        <label>
                                                            <input type="radio" name="contact_address_type"
                                                                   value="zip_code" checked>
                                                            Zip code
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="contact_address_type"
                                                                   value="postal_code"> Postal code
                                                        </label>
                                                    </div>
                                                    <?php echo form_input(['id' => 'contact_zipcode', 'name' => 'contact_zipcode', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Zipcode', 'value' => set_value('contact_zipcode')]); ?>
                                                    <?php echo form_error('contact_zipcode', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>


                        <!-- Sender Shipment -->
                        <section id="sender_section" style="display: block;" class="custom-section">
                            <div class="custom-container">
                                <div class="custom-form-grid">
                                    <div style="margin-top:-10px;" class="row section-container">
                                        <div class="section-label">Sender</div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="sender_first_name" class="custom-label">First Name</label>
                                                <?php echo form_input(['id' => 'sender_first_name', 'name' => 'sender_first_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'First Name', 'value' => set_value('sender_first_name')]); ?>
                                                <?php echo form_error('sender_first_name', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="sender_last_name" class="custom-label">Last Name</label>
                                                <?php echo form_input(['id' => 'sender_last_name', 'name' => 'sender_last_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Last Name', 'value' => set_value('sender_last_name')]); ?>
                                                <?php echo form_error('sender_last_name', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <?php if ($type === 'international'): ?>
                                            <div style="padding-left:15px; padding-right:15px;" class="row">
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="sender_country_id"
                                                               class="custom-label">Country</label>
                                                        <?php

                                                        $selected_sender_country_id = isset($user_country->country_id) ? $user_country->country_id : set_value('sender_country_id');

                                                        echo form_dropdown(
                                                            'sender_country_id',
                                                            array_column($countries, 'short_name', 'country_id'),
                                                            $selected_sender_country_id,
                                                            [
                                                                'id' => 'sender_country_id',
                                                                'class' => 'custom-select'
                                                            ]
                                                        );
                                                        ?>
                                                        <?php echo form_error('sender_country_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="sender_state_id"
                                                               class="custom-label">State</label>
                                                        <select name="sender_state_id" id="sender_state_id"
                                                                class="custom-select">
                                                            <option value="" selected>Select State</option>
                                                        </select>
                                                        <?php echo form_error('sender_state_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <?php
                                                $sender_country_code = set_value('sender_country_code')
                                                    ?: (isset($user_country->calling_code) ? '+' . $user_country->calling_code : '+254');
                                                ?>
                                                <label for="sender_phone_number" class="custom-label">Phone</label>
                                                <div class="phone-input-group">
                                                    <!-- Country Code Input -->
                                                    <input style="border-right:transparent;" type="text"
                                                           id="sender_country_code" name="sender_country_code"
                                                           class="country-code"
                                                           value="<?php echo $sender_country_code; ?>" readonly>
                                                    <?php echo form_input(['id' => 'sender_phone_number', 'name' => 'sender_phone_number', 'type' => 'text', 'class' => 'phone', 'placeholder' => 'Phone Number', 'value' => set_value('sender_phone_number')]); ?>
                                                </div>
                                                <!-- Hidden Field for Combined Number -->
                                                <input type="hidden" id="sender_full_phone_number"
                                                       name="sender_full_phone_number" value="">
                                                <?php echo form_error('sender_phone_number', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="sender_email" class="custom-label">Email</label>
                                                <?php echo form_input(['id' => 'sender_email', 'name' => 'sender_email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Email', 'value' => set_value('sender_email')]); ?>
                                                <?php echo form_error('sender_email', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="sender_id_number" class="custom-label">ID No.</label>
                                                <?php echo form_input(['id' => 'sender_id_number', 'name' => 'sender_id_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'National ID / Passport No.', 'value' => set_value('sender_id_number')]); ?>
                                                <?php echo form_error('sender_id_number', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="sender_kra_pin" class="custom-label">KRA PIN <span style="color:#999;font-weight:normal;">(optional)</span></label>
                                                <?php echo form_input(['id' => 'sender_kra_pin', 'name' => 'sender_kra_pin', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'e.g. P052209979X', 'value' => set_value('sender_kra_pin')]); ?>
                                            </div>
                                        </div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="sender_address" class="custom-label">Address</label>
                                                    <textarea id="sender_address" name="sender_address"
                                                              class="custom-textarea"
                                                              rows="3"
                                                              autocomplete="off"
                                                              placeholder="Enter your address here..."><?php echo set_value('sender_address'); ?></textarea>
                                                    <?php echo form_error('sender_address', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <div id="address_type" style="display: flex; gap: 20px;">
                                                        <label>
                                                            <input type="radio" name="sender_address_type"
                                                                   value="zip_code" checked>
                                                            Zip code
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="sender_address_type"
                                                                   value="postal_code"> Postal code
                                                        </label>
                                                    </div>
                                                    <?php echo form_input(['id' => 'sender_zipcode', 'name' => 'sender_zipcode', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Zipcode', 'value' => set_value('recipient_zipcode'), 'autocomplete' => 'off']); ?>
                                                    <?php echo form_error('sender_zipcode', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id class="custom-section">
                            <div class="custom-container">
                                <div class="custom-form-group"
                                     style="margin-bottom: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                                    <label class="custom-label"
                                           style="display: block; font-weight: bold; font-size: 18px;">Recipient
                                        Type</label>
                                    <div id="type_selection"
                                         style="display: flex; gap: 20px; align-items: center;">
                                        <label style="display: flex; align-items: center; font-size: 16px; cursor: pointer;">
                                            <input type="radio" name="recipient_type" value="individual" checked
                                                   style="margin-right: 8px; accent-color: #007bff;">
                                            Individual
                                        </label>
                                        <label style="display: flex; align-items: center; font-size: 16px; cursor: pointer;">
                                            <input type="radio" name="recipient_type" value="company"
                                                   style="margin-right: 8px; accent-color: #007bff;">
                                            Company
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Recipient Information -->
                        <section id="recipient_section" class="custom-section">
                            <div class="custom-container">
                                <div class="custom-form-grid">
                                    <div style="margin-top:-10px;" class="row section-container">
                                        <div class="section-label">Recipient</div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="recipient_first_name" class="custom-label">First
                                                    Name</label>
                                                <?php echo form_input(['id' => 'recipient_first_name', 'name' => 'recipient_first_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'First Name', 'value' => set_value('recipient_first_name')]); ?>
                                                <?php echo form_error('recipient_first_name', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="recipient_last_name" class="custom-label">Last Name</label>
                                                <?php echo form_input(['id' => 'recipient_last_name', 'name' => 'recipient_last_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Last Name', 'value' => set_value('recipient_last_name')]); ?>
                                                <?php echo form_error('recipient_last_name', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <?php if ($type === 'international'): ?>
                                            <div style="padding-left:15px; padding-right:15px;" class="row">
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="recipient_country_id"
                                                               class="custom-label">Country</label>
                                                        <?php
                                                        // Check if $recipient_country_id is set, otherwise default to form validation data
                                                        $selected_recipient_country_id = isset($user_country->country_id) ? $user_country->country_id : set_value('recipient_country_id');

                                                        echo form_dropdown(
                                                            'recipient_country_id',
                                                            array_column($recipient_countries, 'short_name', 'country_id'),
                                                            $selected_recipient_country_id,
                                                            [
                                                                'id' => 'recipient_country_id',
                                                                'class' => 'custom-select'
                                                            ]
                                                        );
                                                        ?>
                                                        <?php echo form_error('recipient_country_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="recipient_state_id"
                                                               class="custom-label">State</label>
                                                        <select name="recipient_state_id" id="recipient_state_id"
                                                                class="custom-select">
                                                            <option value="" selected>Select State</option>
                                                        </select>
                                                        <?php echo form_error('recipient_state_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <?php
                                                $recipient_country_code = set_value('recipient_country_code')
                                                    ?: (isset($user_country->calling_code) ? '+' . $user_country->calling_code : '+254');
                                                ?>
                                                <label for="recipient_phone_number" class="custom-label">Phone</label>
                                                <div class="phone-input-group">
                                                    <!-- Country Code Input -->
                                                    <input style="border-right:transparent;" type="text"
                                                           id="recipient_country_code" name="recipient_country_code"
                                                           class="country-code"
                                                           value="<?php echo $recipient_country_code ?>" readonly>

                                                    <!-- Phone Number Input -->
                                                    <?php echo form_input([
                                                        'id' => 'recipient_phone_number',
                                                        'name' => 'recipient_phone_number',
                                                        'type' => 'text',
                                                        'class' => 'phone',
                                                        'placeholder' => 'Phone Number',
                                                        'value' => set_value('recipient_phone_number')
                                                    ]); ?>
                                                </div>
                                                <!-- Hidden Field for Combined Number -->
                                                <input type="hidden" id="recipient_full_phone_number"
                                                       name="recipient_full_phone_number" value="">
                                                <?php echo form_error('recipient_phone_number', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="recipient_email" class="custom-label">Email</label>
                                                <?php echo form_input(['id' => 'recipient_email', 'name' => 'recipient_email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Email', 'value' => set_value('recipient_email')]); ?>
                                                <?php echo form_error('recipient_email', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="recipient_id_number" class="custom-label">ID No.</label>
                                                <?php echo form_input(['id' => 'recipient_id_number', 'name' => 'recipient_id_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'National ID / Passport No.', 'value' => set_value('recipient_id_number')]); ?>
                                                <?php echo form_error('recipient_id_number', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="recipient_address" class="custom-label">Address</label>
                                                    <textarea id="recipient_address" name="recipient_address"
                                                              class="custom-textarea"
                                                              rows="3"
                                                              autocomplete="off"
                                                              placeholder="Enter your address here..."><?php echo set_value('recipient_address'); ?></textarea>
                                                    <?php echo form_error('recipient_address', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <div id="address_type" style="display: flex; gap: 20px;">
                                                        <label>
                                                            <input type="radio" name="recipient_address_type"
                                                                   value="zip_code" checked>
                                                            Zip code
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="recipient_address_type"
                                                                   value="postal_code"> Postal code
                                                        </label>
                                                    </div>
                                                    <?php echo form_input(['id' => 'recipient_zipcode', 'name' => 'recipient_zipcode', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Zipcode', 'value' => set_value('recipient_zipcode'), 'autocomplete' => 'off']); ?>
                                                    <?php echo form_error('recipient_zipcode', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Recipient Company Section (Hidden by default) -->
                        <section style="display: none; margin-top:-20px;" id="recipient_company_section" class="custom-section">
                            <div class="custom-container">
                                <div id="company_section" class="custom-form-grid">
                                    <div class="row section-container">
                                        <div class="section-label">Company</div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="recipient_company_name" class="custom-label">Company Name</label>
                                                    <?php echo form_input(['id' => 'recipient_company_name', 'name' => 'recipient_company_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Company Name', 'value' => set_value('recipient_company_name')]); ?>
                                                    <?php echo form_error('recipient_company_name', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="recipient_contact_name" class="custom-label">Contact Name</label>
                                                    <?php echo form_input(['id' => 'recipient_contact_name', 'name' => 'recipient_contact_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Contact Name', 'value' => set_value('recipient_contact_name')]); ?>
                                                    <?php echo form_error('recipient_contact_name', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($type === 'international'): ?>
                                            <div style="padding-left:15px; padding-right:15px;" class="row">
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="recipient_contact_country_id"
                                                               class="custom-label">Country</label>
                                                        <?php
                                                        // Check if $contact_country_id is set, otherwise default to form validation data
                                                        $selected_contact_country_id = isset($user_country->country_id) ? $user_country->country_id : set_value('recipient_contact_country_id');

                                                        echo form_dropdown(
                                                            'recipient_contact_country_id',
                                                            array_column($countries, 'short_name', 'country_id'),
                                                            $selected_contact_country_id,
                                                            [
                                                                'id' => 'recipient_contact_country_id',
                                                                'class' => 'custom-select',
                                                                'style' => 'width:100%;'
                                                            ]
                                                        );
                                                        ?>
                                                        <?php echo form_error('recipient_contact_country_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="recipient_contact_state_id"
                                                               class="custom-label">State</label>
                                                        <select style="width:100%;" name="recipient_contact_state_id"
                                                                id="recipient_contact_state_id"
                                                                class="custom-select">
                                                            <option value="" selected>Select State</option>
                                                        </select>
                                                        <?php echo form_error('recipient_contact_state_id', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <?php
                                                    $recipient_contact_country_code = set_value('recipient_contact_country_code')
                                                        ?: (isset($user_country->calling_code) ? '+' . $user_country->calling_code : '+254');
                                                    ?>
                                                    <label for="contact_phone" class="custom-label">Contact
                                                        Phone</label>
                                                    <div class="phone-input-group">
                                                        <!-- Country Code Input -->
                                                        <input style="border-right:transparent;" type="text"
                                                               id="recipient_contact_country_code" name="recipient_contact_country_code"
                                                               class="country-code"
                                                               value="<?php echo $recipient_contact_country_code; ?>" readonly>
                                                        <?php echo form_input(['id' => 'recipient_contact_phone', 'name' => 'recipient_contact_phone', 'type' => 'text', 'class' => 'phone', 'placeholder' => 'Phone Number', 'value' => set_value('recipient_contact_phone')]); ?>
                                                    </div>
                                                    <!-- Hidden Field for Combined Number -->
                                                    <input type="hidden" id="recipient_contact_full_phone_number"
                                                           name="recipient_contact_full_phone_number" value="">
                                                    <?php echo form_error('recipient_contact_phone', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="recipient_contact_email" class="custom-label">Contact
                                                        Email</label>
                                                    <?php echo form_input(['id' => 'recipient_contact_email', 'name' => 'recipient_contact_email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Email', 'value' => set_value('recipient_contact_email')]); ?>
                                                    <?php echo form_error('recipient_contact_email', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="recipient_contact_address" class="custom-label">Address</label>
                                                    <textarea id="recipient_contact_address" name="recipient_contact_address"
                                                              class="custom-textarea"
                                                              rows="3"
                                                              placeholder="Enter your address here..."><?php echo set_value('recipient_contact_address'); ?></textarea>
                                                    <?php echo form_error('recipient_contact_address', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <div id="recipient_contact_address_type" style="display: flex; gap: 20px;">
                                                        <label>
                                                            <input type="radio" name="recipient_contact_address_type"
                                                                   value="zip_code" checked>
                                                            Zip code
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="recipient_contact_address_type"
                                                                   value="postal_code"> Postal code
                                                        </label>
                                                    </div>
                                                    <?php echo form_input(['id' => 'recipient_contact_zipcode', 'name' => 'recipient_contact_zipcode', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Zipcode', 'value' => set_value('recipient_contact_zipcode')]); ?>
                                                    <?php echo form_error('recipient_contact_zipcode', '<div class="error-message">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Shipping  Information -->
                        <section style="margin-top:-40px;" class="custom-section">
                            <div class="custom-container">
                                <div class="custom-form-grid">
                                    <div class="row section-container">
                                        <div class="section-label">Shipment Information</div>

                                        <div style="padding-left:15px; padding-right:15px;" class="row">
                                            <?php if ($type !== 'international'): ?>
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="shipping_mode" class="custom-label">Shipping
                                                            Mode</label>
                                                        <select id="shipping_mode" name="shipping_mode"
                                                                class="custom-select">
                                                            <option value="<?php echo strtoupper('air'); ?>">
                                                                Air
                                                            </option>
                                                            <option value="<?php echo strtoupper('road'); ?>">
                                                                Road Transport
                                                            </option>
                                                        </select>
                                                        <?php echo form_error('shipping_mode', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-md-6 col-sm-12">
                                                    <div class="custom-form-group">
                                                        <label for="shipping_mode" class="custom-label">Shipping
                                                            Mode</label>

                                                        <input type="text" readonly class="custom-input"
                                                               id="shipping_mode" name="shipping_mode"
                                                               value="<?php echo strtoupper($mode . ' (' . str_replace('_', ' ', $mode_type) . ')') ?>">
                                                        <?php echo form_error('shipping_mode', '<div class="error-message">', '</div>'); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <?php
                                                    switch ($mode ?? 'default') {
                                                        case 'road':    $company_type = 'Logistic Company';  break;
                                                        case 'sea':     $company_type = 'Shipping Company'; break;
                                                        case 'air':     $company_type = 'Freight Company';  break;
                                                        case 'courier': $company_type = 'Courier Company';  break;
                                                        default:        $company_type = 'Logistic Company';  break;
                                                    }
                                                    $company_type_value = (($type ?? '') === 'domestic') ? 'INTERNAL' : $company_type;
                                                    ?>
                                                    <label class="custom-label"><?php echo $company_type; ?></label>
                                                    <input type="hidden" name="company_type" value="<?php echo htmlspecialchars($company_type_value, ENT_QUOTES); ?>"/>
                                                    <?php
                                                    /* Resolve display value — multiple fallbacks */
                                                    $_selected_id = (int) set_value('courier_company_id');
                                                    if ($_selected_id <= 0 && !empty($courier_companies) && is_array($courier_companies)) {
                                                        foreach ($courier_companies as $_default_company) {
                                                            if (($type ?? '') === 'domestic' && strtolower((string) ($_default_company->type ?? '')) === 'internal') {
                                                                $_selected_id = (int) ($_default_company->id ?? 0);
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <select name="courier_company_id"
                                                            id="courier_company_id"
                                                            class="custom-select"
                                                            style="background:#f0fff4; border:1.5px solid #28a745; color:#155724; font-weight:600;">
                                                        <option value="0">Select Logistic Company</option>
                                                        <?php if (!empty($courier_companies) && is_array($courier_companies)): ?>
                                                            <?php foreach ($courier_companies as $_company_option): ?>
                                                                <?php
                                                                $_company_id = (int) ($_company_option->id ?? 0);
                                                                $_company_name = (string) ($_company_option->company_name ?? '');
                                                                $_company_kind = strtolower((string) ($_company_option->type ?? ''));
                                                                $_suffix = $_company_kind !== '' ? ' (' . strtoupper($_company_kind) . ')' : '';
                                                                ?>
                                                                <option value="<?php echo $_company_id; ?>" <?php echo $_selected_id === $_company_id ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($_company_name . $_suffix); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        <?php if ($type === 'international'): ?>
                                            <div class="col-md-6 col-sm-12">
                                                <div class="custom-form-group">
                                                    <label for="export_import"
                                                           class="custom-label">Export/Import</label>
                                                    <select id="export_import" name="export_import"
                                                            class="custom-select">
                                                        <option value="export" <?php echo set_select('export_import', 'export'); ?>>
                                                            Export
                                                        </option>
                                                        <option value="import" <?php echo set_select('export_import', 'import'); ?>>
                                                            Import
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <?php if ($mode_type === 'fcl'): ?>
                            <!-- Package Information -->
                            <section class="custom-section">
                                <div class="custom-container">
                                    <div class="custom-form-grid">
                                        <div style="margin-top:-10px;" class="row section-container">
                                            <div class="section-label">Package</div>
                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped cgs-table"
                                                           id="fclPackageTable">
                                                        <thead>
                                                        <tr>
                                                            <th>Quantity</th>
                                                            <th>Package Description</th>
                                                            <th>FCL Option</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        // Retrieve previously set values for amounts, descriptions, and FCL options
                                                        $amounts = set_value('amount', []);
                                                        $descriptions = set_value('package_description', []);
                                                        $fclOptions = set_value('fcl_options', []);

                                                        // Ensure there is at least one row; otherwise, initialize with one empty row
                                                        $itemCount = max(1, count($amounts)); // At least one row

                                                        // Iterate through each FCL package item
                                                        for ($i = 0; $i < $itemCount; $i++): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => "amount[$i]", // Change to indexed name
                                                                        'class' => 'amount form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => isset($amounts[$i]) ? $amounts[$i] : ''
                                                                    ]); ?>
                                                                    <?php echo form_error("amount[$i]", '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <textarea
                                                                            name="package_description[<?php echo $i; ?>]"
                                                                            class="custom-textarea"
                                                                            rows="3"><?php echo isset($descriptions[$i]) ? $descriptions[$i] : ''; ?></textarea>
                                                                    <?php echo form_error("package_description[$i]", '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_dropdown("fcl_options[$i]", [
                                                                        "20'DV" => "20'DV",
                                                                        "40'DV" => "40'DV",
                                                                        "20'HC" => "20'HC",
                                                                        "40'HC" => "40'HC",
                                                                        "20'RF" => "20'RF",
                                                                        "40'RF" => "40'RF",
                                                                        "20'FR" => "20'FR",
                                                                        "40'FR" => "40'FR",
                                                                        "RoRo" => "RoRo"
                                                                    ], isset($fclOptions[$i]) ? $fclOptions[$i] : '', ['class' => 'custom-select']); ?>
                                                                    <?php echo form_error("fcl_options[$i]", '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($i === 0): ?>
                                                                        <button type="button" class="btn btn-primary"
                                                                                onclick="addFCLPackage()">
                                                                            <i class="fa fa-plus"></i>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button type="button"
                                                                                class="btn btn-danger remove-fcl-package">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endfor; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div style="margin-left:0px; margin-top:20px; margin-bottom:15px;"
                                                 class="col-lg-6 col-sm-12 col-md-6">
                                                <label style="font-weight:bold;" for="packaging_charges">
                                                    Packaging Charges
                                                    <span style="color:#888; font-weight:400; font-size:11px;">(Optional)</span>
                                                </label>
                                                <?php echo form_input([
                                                    'name'        => 'packaging_charges',
                                                    'id'          => 'packaging_charges',
                                                    'class'       => 'form-control',
                                                    'type'        => 'number',
                                                    'step'        => 'any',
                                                    'placeholder' => '0.00',
                                                    'value'       => set_value('packaging_charges')
                                                ]); ?>
                                                <?php echo form_error('packaging_charges', '<div class="text-danger">', '</div>'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        <?php endif; ?>


                        <?php if ($mode_type !== 'fcl'): ?>
                            <!-- Package Information -->
                            <section style="margin-left:0px;" class="custom-section">
                                <div class="custom-container">
                                    <div class="custom-form-grid">
                                        <div style="padding-bottom:10px; padding-right:2px; padding-left:2px; margin-top:-40px;"
                                             class="row section-container">
                                            <div class="section-label">Package</div>
                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped cgs-table" id="packageTable">
                                                        <thead>
                                                        <tr>
                                                            <th>Quantity</th>
                                                            <th>Package Description</th>
                                                            <th style="min-width:90px;">Gross Weight<br>(kgs)</th>
                                                            <th style="min-width:80px;">Length<br>(cm)</th>
                                                            <th style="min-width:80px;">Width<br>(cm)</th>
                                                            <th style="min-width:80px;">Height<br>(cm)</th>
                                                            <th style="min-width:90px;">Volumetric Weight<br>(kgs)</th>
                                                            <?php if (($courier_type ?? 'international') !== 'local' && ($type ?? '') === 'international'): ?>
                                                            <th>Chargeable Weight<br>(kgs)</th>
                                                            <?php else: ?>
                                                            <th>POD</th>
                                                            <th>Unit Price</th>
                                                            <?php endif; ?>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php for ($i = 0; $i < (count(set_value('amount', [])) ?: 1); $i++): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'amount[' . $i . ']',
                                                                        'class' => 'amount form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('amount[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('amount[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <textarea
                                                                            name="package_description[<?php echo $i; ?>]"
                                                                            class="custom-textarea"
                                                                            rows="3"><?php echo set_value('package_description[' . $i . ']', ''); ?></textarea>
                                                                    <?php echo form_error('package_description[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'weight[' . $i . ']',
                                                                        'class' => 'weight form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('weight[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('weight[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'length[' . $i . ']',
                                                                        'class' => 'length form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('length[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('length[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'width[' . $i . ']',
                                                                        'class' => 'width form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('width[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('width[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'height[' . $i . ']',
                                                                        'class' => 'height form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('height[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('height[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo form_input([
                                                                        'name' => 'weight_vol[' . $i . ']',
                                                                        'class' => 'weight-vol form-control',
                                                                        'type' => 'number',
                                                                        'step' => 'any',
                                                                        'value' => set_value('weight_vol[' . $i . ']', '')
                                                                    ]); ?>
                                                                    <?php echo form_error('weight_vol[' . $i . ']', '<div class="text-danger">', '</div>'); ?>
                                                                </td>
                                                                <!-- chargeable_weight — auto-computed by JS, submitted to backend -->
                                                                <?php if (($courier_type ?? 'international') !== 'local' && ($type ?? '') === 'international'): ?>
                                                                <td>
                                                                    <input type="text" readonly
                                                                           name="chargeable_weight[<?php echo $i; ?>]"
                                                                           class="form-control chargeable-weight"
                                                                           value="<?php echo set_value('chargeable_weight['.$i.']', '0'); ?>"
                                                                           style="background:#eef7ee; font-weight:600; cursor:default; min-width:80px;">
                                                                </td>
                                                                <?php else: ?>
                                                                <input type="hidden"
                                                                       name="chargeable_weight[<?php echo $i; ?>]"
                                                                       class="chargeable-weight"
                                                                       value="<?php echo set_value('chargeable_weight['.$i.']', '0'); ?>">
                                                                <?php endif; ?>
                                                                <?php if (($courier_type ?? 'international') === 'local' || ($type ?? '') === 'domestic'): ?>
                                                                <!-- POD — Point of Delivery -->
                                                                <td style="width:110px;">
                                                                    <select name="pod[<?php echo $i; ?>]" class="form-control pod-input">
                                                                        <option value="">POD</option>
                                                                        <?php
                                                                        $pod_selected = set_value('pod['.$i.']', '');
                                                                        foreach (($service_points ?? []) as $_sp):
                                                                            $selected = ($pod_selected === $_sp['name']) ? ' selected' : '';
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($_sp['name'], ENT_QUOTES); ?>"<?php echo $selected; ?>><?php echo htmlspecialchars($_sp['name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                                <!-- Unit Price — local courier only -->
                                                                <td style="min-width:120px;">
                                                                    <?php echo form_input([
                                                                        'name'        => 'unit_price[' . $i . ']',
                                                                        'class'       => 'form-control unit-price',
                                                                        'type'        => 'number',
                                                                        'step'        => 'any',
                                                                        'placeholder' => '0.00',
                                                                        'value'       => set_value('unit_price[' . $i . ']', ''),
                                                                    ]); ?>
                                                                </td>
                                                                <?php endif; ?>
                                                                <td>
                                                                    <?php if ($i === 0): ?>
                                                                        <button type="button" class="btn btn-primary"
                                                                                onclick="addNormalPackage()">
                                                                            <i class="fa fa-plus"></i>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button type="button"
                                                                                class="btn btn-danger remove-package">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endfor; ?>
                                                        </tbody>
                                                    </table>

                                                </div>
                                                <div class="row" style="margin-top:16px; margin-bottom:10px; padding:0 10px;">
                                                    <div class="col-md-6">
                                                        <label style="font-weight:bold;" for="packaging_charges">
                                                            Packaging Charges
                                                            <span style="color:#888; font-weight:400; font-size:11px;">(Optional)</span>
                                                        </label>
                                                        <?php echo form_input([
                                                            'name'        => 'packaging_charges',
                                                            'id'          => 'packaging_charges',
                                                            'class'       => 'amount form-control',
                                                            'type'        => 'number',
                                                            'step'        => 'any',
                                                            'placeholder' => '0.00',
                                                            'value'       => set_value('packaging_charges')
                                                        ]); ?>
                                                        <?php echo form_error('packaging_charges', '<div class="text-danger">', '</div>'); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label style="font-weight:bold;" for="goods_declared_value">
                                                            Value of Goods
                                                            <span style="color:#888; font-weight:400; font-size:11px;">(Optional)</span>
                                                        </label>
                                                        <?php echo form_input([
                                                            'name'        => 'goods_declared_value',
                                                            'id'          => 'goods_declared_value',
                                                            'class'       => 'form-control',
                                                            'type'        => 'number',
                                                            'step'        => '0.01',
                                                            'min'         => '0',
                                                            'placeholder' => '0.00',
                                                            'value'       => set_value('goods_declared_value')
                                                        ]); ?>
                                                        <?php echo form_error('goods_declared_value', '<div class="text-danger">', '</div>'); ?>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-bottom:10px; padding:0 10px;">
                                                    <div class="col-md-6 col-md-offset-6">
                                                        <label style="font-weight:bold;">
                                                            <?php if (($courier_type ?? 'international') === 'local'): ?>
                                                                Total Gross Weight (kgs)
                                                            <?php else: ?>
                                                                Total Chargeable Weight (kgs)
                                                                <span style="color:#888; font-weight:400; font-size:11px;">= MAX(actual, volumetric) &times; qty</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="text" id="totalChargeableWeight" class="form-control"
                                                               style="background:#eef7ee; font-weight:700; color:#155724;" readonly placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        <?php endif; ?>

                        <!-- VAT Section -->
                        <section class="custom-section" style="margin-left:0; margin-top:10px;">
                            <div class="custom-container">
                                <div style="padding: 0 15px;">
                                    <div style="
                                        background: #fffde7;
                                        border: 1.5px solid #f9a825;
                                        border-radius: 8px;
                                        padding: 14px 18px;
                                    ">
                                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#f57f17; margin-bottom:10px;">
                                            <i class="fa fa-percent"></i> VAT / Tax
                                        </div>
                                        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:16px;">
                                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; font-size:14px; margin:0; user-select:none;">
                                                <input type="checkbox" name="vat_applicable" id="vat_applicable_check" value="1"
                                                       style="width:18px; height:18px; accent-color:#e65100; cursor:pointer; flex-shrink:0;"
                                                       onchange="toggleVatRate(this)">
                                                <span style="color:#e65100;">Charge VAT</span>
                                            </label>
                                            <span style="font-size:12px; color:#999;">(tick to apply VAT on this shipment)</span>
                                            <div id="vat_rate_wrapper" style="display:none; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <span style="font-weight:600; font-size:13px; white-space:nowrap;">VAT Rate (%):</span>
                                                <input type="number" name="vat_rate" id="vat_rate"
                                                       class="form-control"
                                                       step="0.01" min="0" max="100"
                                                       value="<?php echo get_option('courier_vat_rate') ?: 16; ?>"
                                                       style="width:80px; display:inline-block; padding:5px 8px; height:34px;">
                                                <span id="vat_preview_text" style="color:#e65100; font-size:13px; font-weight:600;"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <script>
                        function toggleVatRate(cb) {
                            var wrap = document.getElementById('vat_rate_wrapper');
                            wrap.style.display = cb.checked ? 'flex' : 'none';
                        }
                        (function() {
                            var pkgEl  = document.getElementById('packaging_charges');
                            var rateEl = document.getElementById('vat_rate');
                            function updateVatPreview() {
                                var pkg  = parseFloat(pkgEl  ? pkgEl.value  : 0) || 0;
                                var rate = parseFloat(rateEl ? rateEl.value : 0) || 0;
                                var el   = document.getElementById('vat_preview_text');
                                if (el) el.textContent = (pkg > 0 && rate > 0) ? '→ VAT = ' + (pkg * rate / 100).toFixed(2) : '';
                            }
                            if (pkgEl)  pkgEl.addEventListener('input',  updateVatPreview);
                            if (rateEl) rateEl.addEventListener('input', updateVatPreview);
                        })();
                        </script>

                        <!-- Commercial Value Information -->
                        <section style="margin-left:0px;" class="custom-section">
                            <div class="custom-container">
                                <div class="custom-form-grid">
                                    <div style="padding-bottom:10px; padding-right:2px; padding-left:2px; margin-top:-40px;"
                                         class="row section-container">
                                        <div class="section-label">Commercial Invoice Information (<span
                                                    class="text-danger">This information will be used to generate commercial Invoice*</span>)
                                        </div>
                                        <div class="custom-form-group"
                                             style="margin: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); background-color: #f9f9f9;">
                                            <div id="type_selection" style="display: flex; align-items: center;">
                                                <label class="switch"
                                                       style="display: flex; align-items: center; cursor: pointer;">
                                                    <input type="checkbox" name="hasCommercialInvoiceAttachment"
                                                           id="toggleSwitch">
                                                    <span class="slider"></span>
                                                </label>
                                                <span id="toggleLabel"
                                                      style="margin-left: 15px; font-weight:bold; font-size: 13px;">Attach Commercial Invoice</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div style="display: none; margin-top:10px; padding-bottom:10px;"
                                                 id="invoice_attachment">
                                                <label style="font-weight:bold; font-size:14px;"
                                                       for="commercial_invoice_file">Attach
                                                    Commercial Invoice</label>
                                                <input class="custom-input" type="file" name="commercial_invoice_file"
                                                       id="commercial_invoice_file">
                                                <?php echo form_error('commercial_invoice_file', '<div class="error-message">', '</div>'); ?>

                                            </div>
                                            <div style="display: block;" id="invoice_input" class="table-responsive">
                                                <table class="table table-bordered table-striped cgs-table"
                                                       id="commercialItemsTable">
                                                    <thead>
                                                    <tr>
                                                        <th>Quantity</th>
                                                        <th>Item Description</th>
                                                        <th>Declared Value</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    // Retrieve previously set values for quantity, description, and declared value
                                                    $quantities = set_value('commodity_quantity', []);
                                                    $descriptions = set_value('commodity_description', []);
                                                    $declaredValues = set_value('declared_value', []);

                                                    // Check if there are existing items; if not, initialize with one empty row
                                                    $itemCount = max(1, count($quantities)); // Ensure at least one row

                                                    for ($i = 0; $i < $itemCount; $i++): ?>
                                                        <tr>
                                                            <td>
                                                                <?php echo form_input([
                                                                    'name' => "commodity_quantity[$i]", // Change to indexed name
                                                                    'class' => 'amount form-control',
                                                                    'type' => 'number',
                                                                    'step' => 'any',
                                                                    'value' => isset($quantities[$i]) ? $quantities[$i] : ''
                                                                ]); ?>
                                                                <?php echo form_error("commodity_quantity[$i]", '<div class="text-danger">', '</div>'); ?>
                                                            </td>
                                                            <td>
                                                                <textarea
                                                                        name="commodity_description[<?php echo $i; ?>]"
                                                                        class="custom-textarea"
                                                                        rows="3"><?php echo isset($descriptions[$i]) ? $descriptions[$i] : ''; ?></textarea>
                                                                <?php echo form_error("commodity_description[$i]", '<div class="text-danger">', '</div>'); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo form_input([
                                                                    'name' => "declared_value[$i]", // Change to indexed name
                                                                    'class' => 'declared-value form-control',
                                                                    'type' => 'number',
                                                                    'step' => 'any',
                                                                    'value' => isset($declaredValues[$i]) ? $declaredValues[$i] : ''
                                                                ]); ?>
                                                                <?php echo form_error("declared_value[$i]", '<div class="text-danger">', '</div>'); ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($i === 0): ?>
                                                                    <button type="button" class="btn btn-primary"
                                                                            onclick="addCommercialItem()">
                                                                        <i class="fa fa-plus"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button type="button"
                                                                            class="btn btn-danger remove-commercial-item">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endfor; ?>
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>


                        <div class="question-container">
                            <label class="custom-checkbox">
                                <span class="checkbox-text">Do you wish to add a pickup for this shipment ?</span>
                                <span class="pickup-optional-badge"><i class="fa fa-info-circle"></i> Optional</span>
                                <input type="checkbox" name="hasPickup" id="addPickupCheckbox">
                            </label>
                        </div>

                        <!-- Optional Pickup Section -->
                        <section style="display: none; margin-left: 18px; margin-right:18px;" id="optionalPickupSection"
                                 class="custom-section">
                            <div class="custom-form-grid">
                                <!--Pickup Information-->
                                <div class=" section-container">
                                    <div class="section-label">Pickup Information</div>
                                    <div style="padding-left:15px; padding-right:15px; margin-top:10px;" class="row">
                                        <div class="custom-form-group col-md-6 col-sm-12">
                                            <label for="pickup_date" class="custom-label">Pickup Date:</label>
                                            <input type="text" id="pickup_date" name="pickup_date"
                                                   class="custom-input pickup-required-field" placeholder="Choose Date">
                                            <?php echo form_error('pickup_date', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                        <div class="custom-form-group col-md-3 col-sm-12">
                                            <label for="pickup_start_time" class="custom-label">Pickup Start
                                                Time:</label>
                                            <input type="text" id="pickup_start_time" name="pickup_start_time"
                                                   class="custom-input pickup_time pickup-required-field" placeholder="Choose Start Time">
                                            <?php echo form_error('pickup_start_time', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                        <div class="custom-form-group col-md-3 col-sm-12">
                                            <label for="pickup_end_time" class="custom-label">Pickup End Time:</label>
                                            <input type="text" id="pickup_end_time" name="pickup_end_time"
                                                   class="custom-input pickup_time pickup-required-field" placeholder="Choose End Time">
                                            <?php echo form_error('pickup_end_time', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div style="padding-left:15px; padding-right:15px;" class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="pickup_country_id" class="custom-label">Country</label>
                                                <?php

                                                // Default to Kenya (ID 115); fall back to staff country if set; honour re-submit value
                                                $selected_country_id = set_value('pickup_country_id')
                                                    ?: (isset($user_country->country_id) ? $user_country->country_id : 115);

                                                echo form_dropdown(
                                                    'pickup_country_id',
                                                    array_column($countries, 'short_name', 'country_id'),
                                                    $selected_country_id,
                                                    [
                                                        'id' => 'pickup_country_id',
                                                        'class' => 'custom-select',
                                                        'style' => 'width: 100%;'
                                                    ]
                                                );
                                                ?>
                                                <?php echo form_error('pickup_country_id', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="pickup_state_id" class="custom-label" id="pickup_state_label">State / Town</label>
                                                <select style="width:100%;" name="pickup_state_id" id="pickup_state_id"
                                                        class="custom-select">
                                                    <option value="" selected>Select Town / State</option>
                                                </select>
                                                <?php echo form_error('pickup_state_id', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="padding-left:15px; padding-right:15px;" class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="pickup_address" class="custom-label">Address</label>
                                                <textarea id="pickup_address" name="pickup_address"
                                                          class="custom-textarea"
                                                          autocomplete="off"
                                                          rows="3"
                                                          placeholder="Enter your address here..."><?php echo set_value('pickup_address'); ?></textarea>
                                                <?php echo form_error('pickup_address', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <div id="address_type" style="display: flex; gap: 20px;">
                                                    <label>
                                                        <input type="radio" name="pickup_address_type"
                                                               value="zip_code" checked>
                                                        Zip code
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="pickup_address_type"
                                                               value="postal_code"> Postal code
                                                    </label>
                                                </div>
                                                <?php echo form_input(['id' => 'pickup_zipcode', 'name' => 'pickup_zipcode', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Zipcode', 'value' => set_value('zipcode'), 'autocomplete' => 'off']); ?>
                                                <?php echo form_error('pickup_zipcode', '<div class="error-message">', '</div>'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="pickup_vehicle_type" class="custom-label">Vehicle Type</label>
                                            <select name="pickup_vehicle_type" id="vehicle_type"
                                                    class="custom-select">
                                                <option value="truck">Truck</option>
                                                <option value="van">Van</option>
                                                <option value="motorcycle">Motorcycle</option>
                                                <option value="motorcycle">Other</option>
                                            </select>
                                            <?php echo form_error('pickup_vehicle_type', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="driver_id" class="custom-label">Driver</label>
                                            <?php
                                            // Prepare the dropdown options by concatenating first name and last name
                                            $drivers_dropdown = array_column($drivers, 'firstname', 'staffid');
                                            foreach ($drivers as $driver) {
                                                $drivers_dropdown[$driver['staffid']] = $driver['firstname'] . ' ' . $driver['lastname'];
                                            }
                                            echo form_dropdown('pickup_driver_id', $drivers_dropdown, set_value('pickup_driver_id'), ['id' => 'driver_id', 'class' => 'custom-select', 'style' => 'width: 100%;']);
                                            ?>
                                            <?php echo form_error('driver_id', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                </div>
                                <!--End Of Pickup Information-->

                                <!--Contact Person (pre-filled from logged-in staff)-->
                                <?php
                                // Defaults: posted value first, then current staff, then empty
                                $pf_first = set_value('pickup_contact_first_name') ?: ($current_staff->firstname ?? '');
                                $pf_last  = set_value('pickup_contact_last_name')  ?: ($current_staff->lastname  ?? '');
                                $pf_email = set_value('pickup_contact_email')       ?: ($current_staff->email    ?? '');
                                $pf_phone = set_value('pickup_contact_phone_number') ?: ($current_staff->phonenumber ?? '');
                                // Country code: POST value on re-display; otherwise staff country or Kenya (+254)
                                $pickup_country_code = set_value('pickup_country_code')
                                    ?: (isset($user_country->calling_code) ? '+' . $user_country->calling_code : '+254');
                                ?>
                                <div class="row section-container">
                                    <div class="section-label">Contact Person
                                        <span style="font-weight:normal; font-size:11px; color:#28a745; margin-left:6px;"><i class="fa fa-user-check"></i> Auto-filled from your profile</span>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="pickup_contact_first_name" class="custom-label">First Name</label>
                                            <input name="pickup_contact_first_name" id="pickup_contact_first_name"
                                                   type="text"
                                                   class="custom-input"
                                                   value="<?php echo htmlspecialchars($pf_first); ?>"
                                                   placeholder="First Name">
                                            <?php echo form_error('pickup_contact_first_name', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="pickup_contact_last_name" class="custom-label">Last Name</label>
                                            <input name="pickup_contact_last_name" id="pickup_contact_last_name"
                                                   type="text"
                                                   class="custom-input"
                                                   value="<?php echo htmlspecialchars($pf_last); ?>"
                                                   placeholder="Last Name">
                                            <?php echo form_error('pickup_contact_last_name', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="pickup_contact_phone_number" class="custom-label">Phone</label>
                                            <div class="phone-input-group">
                                                <input style="border-right:transparent;" type="text"
                                                       id="pickup_country_code" name="pickup_country_code"
                                                       class="country-code"
                                                       value="<?php echo htmlspecialchars($pickup_country_code); ?>" readonly>
                                                <input name="pickup_contact_phone_number"
                                                       id="pickup_contact_phone_number"
                                                       type="text"
                                                       class="phone"
                                                       value="<?php echo htmlspecialchars($pf_phone); ?>"
                                                       placeholder="Phone Number">
                                            </div>
                                            <input type="hidden" id="pickup_full_phone_number"
                                                   name="pickup_full_phone_number" value="">
                                            <?php echo form_error('pickup_contact_phone_number', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="pickup_contact_email" class="custom-label">Email</label>
                                            <input name="pickup_contact_email" id="pickup_contact_email" type="email"
                                                   class="custom-input"
                                                   value="<?php echo htmlspecialchars($pf_email); ?>"
                                                   placeholder="Email">
                                            <?php echo form_error('pickup_contact_email', '<div class="error-message">', '</div>'); ?>
                                        </div>
                                    </div>
                                </div>
                                <!--End of Contact Person-->
                            </div>
                        </section>

                        <!-- ── Charges & Options ─────────────────────────── -->
                        <section class="custom-section" style="margin-top:0;">
                            <div class="custom-container">
                                <div class="row section-container" style="margin-top:0;">
                                    <div class="section-label">Charges &amp; Options</div>

                                    <div class="row" style="padding:0 15px;">

                                        <!-- VAT Toggle -->
                                        <div class="col-md-4 col-sm-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label">
                                                    Apply VAT?
                                                    <small class="text-muted" style="font-weight:400;">(toggle on to add VAT to shipment)</small>
                                                </label>
                                                <div style="display:flex; align-items:center; gap:12px; margin-top:4px;">
                                                    <label class="switch" style="margin:0;">
                                                        <input type="checkbox" name="vat_applicable" id="vat_applicable"
                                                               value="1" <?php echo set_value('vat_applicable') ? 'checked' : ''; ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <span id="vat-toggle-label" style="font-size:13px; color:#555;">
                                                        <?php echo set_value('vat_applicable') ? 'VAT Enabled' : 'VAT Disabled'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- VAT Rate (shown when VAT is on) -->
                                        <div class="col-md-4 col-sm-12" id="vat-rate-col"
                                             style="<?php echo set_value('vat_applicable') ? '' : 'display:none;'; ?>">
                                            <div class="custom-form-group">
                                                <label class="custom-label" for="vat_rate">VAT Rate (%)</label>
                                                <input type="number" name="vat_rate" id="vat_rate" step="0.01" min="0"
                                                       class="custom-input"
                                                       value="<?php echo set_value('vat_rate', get_option('courier_vat_rate') ?: '16'); ?>"
                                                       placeholder="e.g. 16">
                                            </div>
                                        </div>

                                        <!-- Round-trip checkbox -->
                                        <div class="col-md-4 col-sm-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label">Round Trip?</label>
                                                <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                                                    <input type="checkbox" name="is_round_trip" id="is_round_trip"
                                                           value="1" style="width:18px; height:18px;"
                                                           <?php echo set_value('is_round_trip') ? 'checked' : ''; ?>>
                                                    <label for="is_round_trip" style="margin:0; font-size:13px; color:#555; cursor:pointer;">
                                                        Shipment includes return journey
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                    </div><!-- /.row -->

                                    <!-- Shipment Date (backdating) -->
                                    <div class="row" style="margin-top:16px;">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label" for="shipment_date">
                                                    <i class="fa fa-calendar" style="color:#1a5276;margin-right:4px;"></i>
                                                    Shipment Date
                                                </label>
                                                <input type="text" id="shipment_date" name="shipment_date"
                                                       class="custom-input"
                                                       placeholder="<?php echo date('Y-m-d'); ?>"
                                                       value="<?php echo set_value('shipment_date', date('Y-m-d')); ?>"
                                                       autocomplete="off">
                                                <small class="text-muted">Defaults to today. Set a past date to backdate the shipment and invoice.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label" for="invoice_due_days">
                                                    Invoice Due (days)
                                                </label>
                                                <input type="number" id="invoice_due_days" name="invoice_due_days"
                                                       class="custom-input"
                                                       min="0" value="<?php echo set_value('invoice_due_days', 30); ?>">
                                                <small class="text-muted">Days after shipment date the invoice is due.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Terms (shown only in manual mode) -->
                                    <?php if (($courier_payment_terms_mode ?? 'manual') === 'manual'): ?>
                                    <div class="row" style="margin-top:16px;">
                                        <div class="col-md-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label" for="payment_terms">Payment Terms</label>
                                                <textarea name="payment_terms" id="payment_terms" rows="3"
                                                          class="custom-input"
                                                          style="resize:vertical;"
                                                          placeholder="e.g. Payment due on delivery, Net 30 days, 50% upfront..."><?php echo set_value('payment_terms'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Special Instructions -->
                                    <div class="row" style="margin-top:16px;">
                                        <div class="col-md-12">
                                            <div class="custom-form-group">
                                                <label class="custom-label" for="special_instructions">
                                                    <i class="fa fa-exclamation-circle" style="color:#e65100;"></i>
                                                    Special Instructions
                                                </label>
                                                <textarea name="special_instructions" id="special_instructions" rows="3"
                                                          class="custom-input"
                                                          style="resize:vertical;"
                                                          placeholder="e.g. Fragile – handle with care, Keep upright, Do not stack, Refrigerated..."><?php echo set_value('special_instructions'); ?></textarea>
                                                <small class="text-muted">Printed on the Waybill and Consignment Note.</small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </section>
                        <!-- ── End Charges & Options ─────────────────────── -->

                        <!-- ── Invoice Preview (compact, right-aligned) ── -->
                        <div class="col-md-12" style="margin-top:18px; padding:0 20px 4px;">
                            <div style="max-width:300px; margin-left:auto;
                                        border:1px solid #d0d0d0; border-left:3px solid #4caf50;
                                        border-radius:5px; background:#f7f7f7;
                                        padding:10px 14px; font-size:12px; color:#444;">
                                <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                                            letter-spacing:.6px; color:#666; margin-bottom:7px;
                                            display:flex; align-items:center; gap:5px;">
                                    <i class="fa fa-file-text-o" style="color:#4caf50;"></i>
                                    Est. Invoice &nbsp;<span style="font-weight:400; color:#aaa; font-style:italic; font-size:10px;">— updates as you type</span>
                                </div>
                                <p id="invoice-preview-placeholder" style="color:#bbb; font-style:italic; margin:0; font-size:11px; text-align:center; padding:4px 0;">
                                    Enter package details above…
                                </p>
                                <div id="invoice-preview-lines" style="display:none;">
                                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                        <tbody id="invoice-preview-items"></tbody>
                                        <tfoot id="invoice-preview-footer"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- ── End Invoice Preview ────────────────────────── -->

                        <div class="col-md-12">
                            <button type="submit" class="custom-button custom-button-green"
                                    style="margin-left:20px; margin-top:16px; font-size:15px; font-weight:700; padding:10px 28px;">
                                <i class="fa fa-check-circle" style="margin-right:8px;"></i> Create Shipment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- ═══ ADD / CHANGE LOGISTIC COMPANY MODAL ════════════════════════════════ -->
<div id="lc-modal-overlay" style="
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:99999; align-items:center; justify-content:center;">
    <div style="
        background:#fff; border-radius:10px; width:100%; max-width:420px;
        box-shadow:0 16px 48px rgba(0,0,0,.25); overflow:hidden; font-family:Arial,sans-serif;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#2e7d32,#1b5e20); padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
            <span style="color:#fff; font-size:15px; font-weight:700;">
                <i class="fa fa-building"></i>&nbsp; Set Logistic Company
            </span>
            <button onclick="closeLogisticModal()" style="background:rgba(255,255,255,.2);border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;color:#fff;font-size:14px;display:flex;align-items:center;justify-content:center;">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <!-- Body -->
        <div style="padding:20px 18px;">
            <p style="font-size:12px; color:#555; margin:0 0 14px;">
                Enter your logistic / shipping company name. This will be saved and used across all shipments.
            </p>
            <label style="font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:5px;">
                Company Name <span style="color:#dc3545;">*</span>
            </label>
            <input type="text" id="lc-input"
                   placeholder="e.g. Xetuu Logistics Ltd"
                   style="width:100%; border:1.5px solid #c8e6c9; border-radius:6px; padding:9px 11px; font-size:13px; color:#333; box-sizing:border-box;"
                   onkeydown="if(event.key==='Enter'){saveLogisticCompany();}">
            <div id="lc-alert" style="display:none; margin-top:10px; padding:8px 12px; border-radius:6px; font-size:12px;"></div>
        </div>
        <!-- Footer -->
        <div style="padding:12px 18px; background:#f9fbe7; border-top:1px solid #c8e6c9; display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closeLogisticModal()" style="background:#546e7a;color:#fff;border:none;border-radius:6px;padding:7px 16px;cursor:pointer;font-size:12px;font-weight:700;">
                <i class="fa fa-times"></i> Cancel
            </button>
            <button id="lc-save-btn" onclick="saveLogisticCompany()" style="background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff;border:none;border-radius:6px;padding:7px 16px;cursor:pointer;font-size:12px;font-weight:700;">
                <i class="fa fa-check"></i> Save Company
            </button>
        </div>
    </div>
</div>

<script>
/* ── Logistic Company Modal ──────────────────────────────────────────────── */
function openLogisticModal() {
    var overlay = document.getElementById('lc-modal-overlay');
    overlay.style.display = 'flex';
    var input = document.getElementById('lc-input');
    // Pre-fill with current value
    var current = document.getElementById('logistic_company_display').value;
    input.value = current || '';
    document.getElementById('lc-alert').style.display = 'none';
    setTimeout(function(){ input.focus(); input.select(); }, 100);
}
function closeLogisticModal() {
    document.getElementById('lc-modal-overlay').style.display = 'none';
}
// Close on backdrop click
document.getElementById('lc-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeLogisticModal();
});

function saveLogisticCompany() {
    var name = document.getElementById('lc-input').value.trim();
    var alert = document.getElementById('lc-alert');

    if (!name) {
        alert.style.display = 'block';
        alert.style.background = '#fce4ec';
        alert.style.color = '#c62828';
        alert.style.border = '1px solid #ef9a9a';
        alert.textContent = 'Please enter a company name.';
        return;
    }

    var btn = document.getElementById('lc-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';

    $.ajax({
        url: '<?php echo admin_url("courier_logistic/shipments/save_logistic_company"); ?>',
        type: 'POST',
        data: {
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>',
            company_name: name
        },
        dataType: 'json',
        success: function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Save Company';
            if (res.success) {
                // Update the display field immediately
                document.getElementById('logistic_company_display').value = res.name;
                alert.style.display = 'block';
                alert.style.background = '#e8f5e9';
                alert.style.color = '#2e7d32';
                alert.style.border = '1px solid #a5d6a7';
                alert.textContent = '✓ Saved! The company name has been updated.';
                setTimeout(closeLogisticModal, 1500);
            } else {
                alert.style.display = 'block';
                alert.style.background = '#fce4ec';
                alert.style.color = '#c62828';
                alert.style.border = '1px solid #ef9a9a';
                alert.textContent = res.message || 'Failed to save. Please try again.';
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Save Company';
            alert.style.display = 'block';
            alert.style.background = '#fce4ec';
            alert.style.color = '#c62828';
            alert.style.border = '1px solid #ef9a9a';
            alert.textContent = 'Network error. Please try again.';
        }
    });
}
</script>

<?php init_tail(); ?>

<!-- flatpickr + Select2 JS after init_tail so they use Perfex's jQuery.
     NOTE: the duplicate jquery-3.6.0.min.js CDN tag was removed — it caused "$ is read-only". -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Use jQuery .ready() — runs after DOM is parsed AND all scripts above have executed,
    // so flatpickr and select2 (loaded from CDN just above) are always available.
    $(document).ready(function () {

        //intialize selct2 on countries and states...
        $('#contact_country_id').select2({});
        $('#contact_state_id').select2({});

        $('#recipient_country_id').select2({});
        $('#recipient_state_id').select2({});

        $('#recipient_contact_country_id').select2({});
        $('#recipient_contact_state_id').select2({});

        $('#sender_country_id').select2({});
        $('#sender_state_id').select2({});

        $('#pickup_country_id').select2({});
        $('#pickup_state_id').select2({});

        $('#driver_id').select2({});

        const companySection = document.getElementById('company_section');
        const senderSection = document.getElementById('sender_section');
        const recipientSection = document.getElementById('recipient_section');
        const recipientCompanySection = document.getElementById('recipient_company_section');

        const senderTypeRadios = document.querySelectorAll('input[name="sender_type"]');
        const recipientTypeRadios = document.querySelectorAll('input[name="recipient_type"]');


        const showCompanySection = <?php echo isset($show_company_section) && $show_company_section ? 'true' : 'false'; ?>;
        const showRecipientCompanySection = <?php echo isset($show_recipient_company_section) && $show_recipient_company_section ? 'true' : 'false'; ?>;

        if (showCompanySection) {
            senderSection.style.display = 'none';
            companySection.style.display = 'block';
            document.querySelector('input[name="sender_type"][value="company"]').checked = true;
        }

        if (showRecipientCompanySection) {
            recipientSection.style.display = 'none';
            recipientCompanySection.style.display = 'block';
            document.querySelector('input[name="recipient_type"][value="company"]').checked = true;
        }

        // Function to show/hide sections based on selected radio button
        function toggleSections() {
            if (document.querySelector('input[name="sender_type"]:checked').value === 'company') {
                companySection.style.display = 'block';
                senderSection.style.display = 'none';
            } else {
                senderSection.style.display = 'block';
                companySection.style.display = 'none';
            }
        }

        // Function to show/hide sections based on selected radio button
        function toggleRecipientSections() {
            if (document.querySelector('input[name="recipient_type"]:checked').value === 'company') {
                recipientCompanySection.style.display = 'block';
                recipientSection.style.display = 'none';
            } else {
                recipientSection.style.display = 'block';
                recipientCompanySection.style.display = 'none';
            }
        }

        // Initial check to set the correct section visibility on page load
        toggleSections();

        // Add event listeners to the radio buttons
        senderTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleSections);
        });

        // Add event listeners to the radio buttons
        recipientTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleRecipientSections);
        });

        // Get the state dropdown elements
        const recipientState = $('#recipient_state_id');
        const senderState = $('#sender_state_id');
        const pickupState = $('#pickup_state_id');
        const contactState = $('#contact_state_id');
        const recipientContactState = $('#recipient_contact_state_id');
        const recipientCountry = $('#recipient_country_id');
        const senderCountry = $('#sender_country_id');
        const pickupCountry = $('#pickup_country_id');
        const contactCountry = $('#contact_country_id');
        const recipientContactCountry = $('#recipient_contact_country_id');

        // Preselected values from the backend (PHP)
        const recipientCountryId = <?php echo $recipient_country_id ?? 'recipientCountry.val() '; ?>;
        const senderCountryId = <?php echo $sender_country_id ?? 'senderCountry.val()'; ?>;
        const pickupCountryId = <?php echo $pickup_country_id ?? 'pickupCountry.val()'; ?>;
        const contactCountryId = <?php echo $contact_country_id ?? 'contactCountry.val()'; ?>;
        const recipientContactCountryId = <?php echo $recipient_contact_country_id ?? 'recipientContactCountry.val()'; ?>;

        // ── Kenya service-point constants — MUST be declared before the initial
        //    handlePickupCountryChange() call below, otherwise KENYA_COUNTRY_ID is
        //    still undefined when that function first runs (var hoisting ≠ assignment hoisting).
        var KENYA_COUNTRY_ID    = '115';
        var KENYA_SERVICE_POINTS = [
            'Busia','Bumala','Sega','Ugunja','Sidindi','Madeya','Yala','Luanda',
            'Maseno','Kisumu','Ahero','Awasi','Muhoroni','Kericho','Nakuru',
            'Londiani','Naivasha','Kangemi','Gitaru','Westlands','Nairobi CBD',
            'Mlolongo','Kasarani','Githurai','Thika','Makongeni Thika','Ruiru',
            'Meru','Embu','Mwea','Nyeri','Kirinyaga','Mombasa','Voi','Kibwezi',
            'Emali','Sultan Hamud','Narok','Bomet','Kisii','Kibirigo','Keumbu',
            'Migori','Rongo','Homa Bay','Katito','Eastleigh',
            'Country Bus (Nairobi)','Wote','Machakos','Tala','Kayole Junction',
            'Umoja','Kayole','Embakasi','Pipeline','Kitale','Eldoret','Kimilili',
            'Kiminini','Mosibridge','Bungoma','Malaba','Kakamega','Mumias',
            'Musanda','Sigomere','Sabatia','Ekero/Shinda','Bondo','Siaya',
            'Usenge','Ngiya'
        ];

        // Load states for pre-selected countries (if any)
        if (recipientCountryId) {
            const recipientStateId = "<?php echo set_value('recipient_state_id') ?: 'null'; ?>";
            getStates(recipientCountryId, 'recipient', recipientState, recipientStateId === 'null' ? null : recipientStateId);
        }

        if (recipientContactCountryId) {
            const recipientContactStateId = "<?php echo set_value('recipient_contact_state_id') ?: 'null'; ?>";
            getStates(recipientContactCountryId, 'recipient_contact', recipientContactState, recipientContactStateId === 'null' ? null : recipientContactStateId);
        }

        if (senderCountryId) {
            const senderStateId = "<?php echo set_value('sender_state_id') ?: 'null'; ?>";
            getStates(senderCountryId, 'sender', senderState, senderStateId === 'null' ? null : senderStateId);
        }
        if (pickupCountryId) {
            const pickupStateId = "<?php echo set_value('pickup_state_id') ?: ''; ?>";
            handlePickupCountryChange(pickupCountryId, pickupStateId || null);
        }
        if (contactCountryId) {
            const contactStateId = "<?php echo set_value('contact_state_id') ?: 'null'; ?>";
            getStates(contactCountryId, 'contact', contactState, contactStateId === 'null' ? null : contactStateId);
        }

        function getStates(countryId, section, stateDropdown, stateId) {
            if (countryId) {
                $.ajax({
                    url: '<?php echo admin_url("courier_logistic/states"); ?>',
                    type: "GET",
                    data: {country_id: countryId},
                    dataType: "json",
                    success: function (data) {
                        // Log the received data to see if it’s as expected
                        console.log('Received states data:', data);

                        if (section === 'recipient') {
                            $('#recipient_country_code').val(`+${data.country_code}`);
                        }

                        if (section === 'recipient_contact') {
                            $('#recipient_contact_country_code').val(`+${data.country_code}`);
                        }

                        if (section === 'sender') {
                            $('#sender_country_code').val(`+${data.country_code}`);
                        }
                        if (section === 'contact') {
                            $('#contact_country_code').val(`+${data.country_code}`);
                        }
                        if (section === 'pickup') {
                            $('#pickup_country_code').val(`+${data.country_code}`);
                        }

                        // Clear current options
                        stateDropdown.empty();

                        // Populate dropdown with new data
                        $.each(data.states, function (key, value) {
                            var selected = (stateId && value.id == stateId) ? 'selected' : '';
                            stateDropdown.append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                        });
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Log detailed error information
                        console.error('Error retrieving states:', jqXHR.responseText);
                        console.error('Text Status:', textStatus);
                        console.error('Error Thrown:', errorThrown);
                        alert('Error retrieving states. Check the console for more details.');
                    }
                });
            } else {
                // Handle empty country case
                stateDropdown.empty();
                stateDropdown.append('<option value="">Select State</option>');
            }
        }


        // Attach change event listeners to the country dropdowns
        recipientCountry.change(function () {
            const countryId = $(this).val();
            getStates(countryId, 'recipient', recipientState, null);
        });

        recipientContactCountry.change(function () {
            const countryId = $(this).val();
            getStates(countryId, 'recipient_contact', recipientContactState, null);
        });

        senderCountry.change(function () {
            const countryId = $(this).val();
            getStates(countryId, 'sender', senderState, null);
        });

        // (KENYA_COUNTRY_ID and KENYA_SERVICE_POINTS are declared earlier, before first use)

        function populateKenyaServicePoints(stateDropdown, selectedVal) {
            stateDropdown.empty();
            stateDropdown.append('<option value="">— Select Town / Service Point —</option>');
            $.each(KENYA_SERVICE_POINTS, function(i, town) {
                var sel = (selectedVal && selectedVal === town) ? ' selected' : '';
                stateDropdown.append('<option value="' + town + '"' + sel + '>' + town + '</option>');
            });
            // Notify select2 that the underlying options changed
            try { stateDropdown.trigger('change'); } catch(e) {}
        }

        function handlePickupCountryChange(countryId, preselectedState) {
            var label = document.getElementById('pickup_state_label');
            if (String(countryId) === KENYA_COUNTRY_ID) {
                if (label) label.textContent = 'Town / Service Point';
                populateKenyaServicePoints(pickupState, preselectedState || null);
            } else {
                if (label) label.textContent = 'State / Town';
                getStates(countryId, 'pickup', pickupState, preselectedState || null);
            }
        }

        pickupCountry.change(function () {
            handlePickupCountryChange($(this).val(), null);
        });

        contactCountry.change(function () {
            const countryId = $(this).val();
            getStates(countryId, 'contact', contactState, null);
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#shipment_date", {
                dateFormat: "Y-m-d",
                maxDate: "today",   // can't future-date a shipment
                defaultDate: "today",
                disableMobile: true
            });

            flatpickr("#pickup_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: [function (date) {
                    return (date.getDay() === 6 || date.getDay() === 0);
                }],
                disableMobile: true
            });

            flatpickr(".pickup_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
                defaultHour: 9,
                defaultMinute: 0,
                minuteIncrement: 30,
                disableMobile: true
            });
        } else {
            // fallback: show native browser date/time pickers
            document.getElementById('pickup_date').type = 'date';
            document.querySelectorAll('.pickup_time').forEach(function(el){ el.type = 'time'; });
        }

    });
</script>

<script>
/* ── VAT Toggle ─────────────────────────────────────────────────────────── */
(function() {
    var vatChk   = document.getElementById('vat_applicable');
    var rateCol  = document.getElementById('vat-rate-col');
    var rateInp  = document.getElementById('vat_rate');
    var lbl      = document.getElementById('vat-toggle-label');

    if (!vatChk) return;

    vatChk.addEventListener('change', function() {
        if (this.checked) {
            rateCol.style.display = '';
            lbl.textContent = 'VAT Enabled';
            lbl.style.color = '#28a745';
        } else {
            rateCol.style.display = 'none';
            lbl.textContent = 'VAT Disabled';
            lbl.style.color = '#555';
        }
    });
})();
</script>

<script>
/* ── Invoice Preview ─────────────────────────────────────────────────────── */
(function () {
    var INTL_RATE = 7; // rate per chargeable kg for international shipments

    function getVatEnabled() {
        var chk = isLocalCourier
            ? document.getElementById('vat_applicable_check')
            : document.getElementById('vat_applicable');
        return chk ? chk.checked : false;
    }

    function getVatRate() {
        var inp = isLocalCourier
            ? document.querySelector('#vat_rate_wrapper input[name="vat_rate"]')
            : document.querySelector('#vat-rate-col input[name="vat_rate"]');
        return inp ? (parseFloat(inp.value) || 0) : 0;
    }

    function getPackagingCharges() {
        var el = document.getElementById('packaging_charges');
        return el ? (parseFloat(el.value) || 0) : 0;
    }

    function getPackageLines() {
        var lines = [];
        var rows = document.querySelectorAll('#packageTable tbody tr');
        if (isLocalCourier) {
            rows.forEach(function (row) {
                var qtyInp   = row.querySelector('.amount');
                var descInp  = row.querySelector('.custom-textarea, textarea[name^="package_description"]');
                var priceInp = row.querySelector('.unit-price');
                var qty   = qtyInp   ? (parseFloat(qtyInp.value)   || 0) : 0;
                var price = priceInp ? (parseFloat(priceInp.value)  || 0) : 0;
                var desc  = descInp  ? (descInp.value.trim() || 'Package') : 'Package';
                if (qty > 0 && price > 0) {
                    lines.push({ desc: desc, amount: qty * price });
                }
            });
        } else {
            var totalKg = 0;
            rows.forEach(function (row) {
                var qtyInp = row.querySelector('.amount');
                var cwInp  = row.querySelector('.chargeable-weight');
                var qty = qtyInp ? (parseFloat(qtyInp.value) || 0) : 0;
                var cw  = cwInp  ? (parseFloat(cwInp.value)  || 0) : 0;
                totalKg += qty > 0 ? cw : 0; // chargeable_weight already includes qty factor in some modes
            });
            if (totalKg > 0) {
                lines.push({
                    desc:   'Shipping (Chargeable Weight)',
                    detail: totalKg.toFixed(2) + ' kg × ' + INTL_RATE,
                    amount: totalKg * INTL_RATE
                });
            }
        }
        return lines;
    }

    function fmt(n) {
        return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function updatePreview() {
        var placeholder  = document.getElementById('invoice-preview-placeholder');
        var linesDiv     = document.getElementById('invoice-preview-lines');
        var itemsTbody   = document.getElementById('invoice-preview-items');
        var footerTfoot  = document.getElementById('invoice-preview-footer');
        if (!placeholder || !linesDiv || !itemsTbody || !footerTfoot) return;

        var pkgLines  = getPackageLines();
        var packaging = getPackagingCharges();
        var vatOn     = getVatEnabled();
        var vatRate   = getVatRate();

        var subtotal = 0;
        pkgLines.forEach(function (l) { subtotal += l.amount; });
        var preVat = subtotal + packaging;

        if (pkgLines.length === 0 && packaging === 0) {
            placeholder.style.display = '';
            linesDiv.style.display = 'none';
            return;
        }

        placeholder.style.display = 'none';
        linesDiv.style.display = '';

        /* — item rows — */
        var itemsHtml = '';
        pkgLines.forEach(function (l) {
            var detail = l.detail
                ? '<br><span style="color:#aaa;font-size:10px;">' + esc(l.detail) + '</span>'
                : '';
            itemsHtml +=
                '<tr>' +
                '<td style="padding:5px 0;border-bottom:1px dashed #e0e0e0;color:#555;max-width:160px;overflow:hidden;text-overflow:ellipsis;">' + esc(l.desc) + detail + '</td>' +
                '<td style="padding:5px 0;border-bottom:1px dashed #e0e0e0;text-align:right;font-weight:600;color:#333;white-space:nowrap;padding-left:8px;">' + fmt(l.amount) + '</td>' +
                '</tr>';
        });
        if (packaging > 0) {
            itemsHtml +=
                '<tr>' +
                '<td style="padding:5px 0;border-bottom:1px dashed #e0e0e0;color:#555;">Packaging</td>' +
                '<td style="padding:5px 0;border-bottom:1px dashed #e0e0e0;text-align:right;font-weight:600;color:#333;white-space:nowrap;padding-left:8px;">' + fmt(packaging) + '</td>' +
                '</tr>';
        }
        itemsTbody.innerHTML = itemsHtml;

        /* — footer — */
        var vatAmt = vatOn ? (preVat * vatRate / 100) : 0;
        var total  = preVat + vatAmt;
        var footerHtml = '';

        if (packaging > 0 || pkgLines.length > 1) {
            footerHtml +=
                '<tr>' +
                '<td style="padding:4px 0;color:#888;text-align:left;font-size:11px;border-top:1px solid #ddd;">Subtotal</td>' +
                '<td style="padding:4px 0;text-align:right;font-size:11px;color:#888;border-top:1px solid #ddd;white-space:nowrap;padding-left:8px;">' + fmt(preVat) + '</td>' +
                '</tr>';
        }
        if (vatOn && vatAmt > 0) {
            footerHtml +=
                '<tr>' +
                '<td style="padding:4px 0;color:#888;font-size:11px;">VAT (' + vatRate + '%)</td>' +
                '<td style="padding:4px 0;text-align:right;font-size:11px;color:#888;white-space:nowrap;padding-left:8px;">' + fmt(vatAmt) + '</td>' +
                '</tr>';
        }
        footerHtml +=
            '<tr style="border-top:2px solid #4caf50;">' +
            '<td style="padding:6px 0;color:#2e7d32;font-weight:700;font-size:13px;">TOTAL</td>' +
            '<td style="padding:6px 0;color:#2e7d32;font-weight:800;font-size:14px;text-align:right;white-space:nowrap;padding-left:8px;">' + fmt(total) + '</td>' +
            '</tr>';
        footerTfoot.innerHTML = footerHtml;
    }

    // Re-run preview on any input or change anywhere in the document
    document.addEventListener('input',  updatePreview);
    document.addEventListener('change', updatePreview);

    // Initial run after DOM is ready
    updatePreview();
})();
</script>

<script>
/**
 * IP Geolocation — auto-detect calling code for the pickup Contact Person phone field.
 *
 * Only runs when the field is blank (fresh form load).
 * On form re-submission the PHP set_value() already preserves the user's choice.
 *
 * Primary:  ipapi.co  (free, 30k req/month, returns country_calling_code directly)
 * Fallback: ip-api.com (free, unlimited, returns countryCode — we map to calling code)
 */
(function autoDetectPickupCallingCode() {
    var field = document.getElementById('pickup_country_code');
    if (!field || field.value !== '') return; // already set (e.g. validation re-display)

    field.value = '…';
    field.style.color = '#aaa';

    function applyCode(code) {
        if (!code) code = '+254'; // Kenya fallback
        if (code.charAt(0) !== '+') code = '+' + code;
        field.value = code;
        field.style.color = '';
    }

    // Primary: ipapi.co
    fetch('https://ipapi.co/json/', { cache: 'no-store' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.country_calling_code) {
                applyCode(data.country_calling_code);
            } else {
                throw new Error('no calling code in response');
            }
        })
        .catch(function() {
            // Fallback: ip-api.com → returns ISO country code, map to calling code
            fetch('https://ip-api.com/json/?fields=countryCode', { cache: 'no-store' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var isoToCode = {
                        'KE':'+254','UG':'+256','TZ':'+255','RW':'+250','ET':'+251',
                        'NG':'+234','GH':'+233','ZA':'+27','US':'+1','GB':'+44',
                        'IN':'+91','CN':'+86','DE':'+49','FR':'+33','IT':'+39',
                        'AU':'+61','CA':'+1','BR':'+55','MX':'+52','AE':'+971',
                        'SD':'+249','SS':'+211','SO':'+252','DJ':'+253','ER':'+291',
                        'MW':'+265','MZ':'+258','ZM':'+260','ZW':'+263','BW':'+267',
                        'NA':'+264','CM':'+237','SN':'+221','CI':'+225','CD':'+243'
                    };
                    var code = data && data.countryCode ? (isoToCode[data.countryCode] || '+254') : '+254';
                    applyCode(code);
                })
                .catch(function() {
                    applyCode('+254'); // last resort
                });
        });
})();

// ── Form persistence — survives page refresh ──────────────────────────────
(function () {
    'use strict';

    // Unique key per shipment type/mode so domestic, sea-LCL, air-freight etc. each keep their own draft
    var DRAFT_KEY = 'courier_shipment_draft_<?php echo $type . '_' . ($mode ?? 'none') . '_' . ($mode_type ?? 'none'); ?>';
    var saveTimer;

    // ---------- helpers ----------

    function eachField(form, cb) {
        var seen = {};
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name) return;
            if (el.type === 'file' || el.type === 'submit' || el.type === 'button') return;
            if (el.type === 'hidden' || el.readOnly) return;   // skip CSRF tokens & auto-calc fields
            seen[el.name] = (seen[el.name] || 0);
            cb(el, el.name + '@@' + seen[el.name]++);
        });
    }

    function triggerEl(el) {
        el.dispatchEvent(new Event('change', { bubbles: true }));
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
        // Keep Select2 in sync
        if (typeof $ !== 'undefined' && $(el).data('select2')) {
            $(el).trigger('change');
        }
    }

    // ---------- save ----------

    function saveDraft() {
        var form = document.getElementById('create-shipment-form');
        if (!form) return;

        var pkgTbody = document.querySelector('#packageTable tbody');
        var fclTbody = document.querySelector('#fclPackageTable tbody');

        var draft = {
            meta: {
                pkgRows: pkgTbody ? pkgTbody.querySelectorAll('tr').length : 1,
                fclRows: fclTbody ? fclTbody.querySelectorAll('tr').length : 1
            },
            fields: {}
        };

        eachField(form, function (el, key) {
            if (el.type === 'checkbox') {
                draft.fields[key] = el.checked;
            } else if (el.type === 'radio') {
                draft.fields[key + '_v'] = el.value;
                draft.fields[key + '_c'] = el.checked;
            } else {
                draft.fields[key] = el.value;
            }
        });

        try { sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft)); } catch (e) {}
    }

    // ---------- restore ----------

    function restoreDraft() {
        var form = document.getElementById('create-shipment-form');
        if (!form) return;

        var raw;
        try { raw = sessionStorage.getItem(DRAFT_KEY); } catch (e) {}
        if (!raw) return;

        var draft;
        try { draft = JSON.parse(raw); } catch (e) { return; }
        if (!draft || !draft.fields) return;

        // 1 — Restore extra package rows before filling values
        var pkgTbody = document.querySelector('#packageTable tbody');
        if (pkgTbody) {
            var need = (parseInt(draft.meta.pkgRows) || 1) - pkgTbody.querySelectorAll('tr').length;
            for (var p = 0; p < need; p++) {
                if (typeof window.addNormalPackage === 'function') window.addNormalPackage();
            }
        }
        var fclTbody = document.querySelector('#fclPackageTable tbody');
        if (fclTbody) {
            var needF = (parseInt(draft.meta.fclRows) || 1) - fclTbody.querySelectorAll('tr').length;
            for (var f = 0; f < needF; f++) {
                if (typeof window.addFCLPackage === 'function') window.addFCLPackage();
            }
        }

        // 2 — Restore field values
        eachField(form, function (el, key) {
            if (el.type === 'checkbox') {
                var v = draft.fields[key];
                if (v !== undefined) { el.checked = !!v; triggerEl(el); }
            } else if (el.type === 'radio') {
                var rv = draft.fields[key + '_v'], rc = draft.fields[key + '_c'];
                if (rc !== undefined && el.value === rv) {
                    el.checked = !!rc;
                    if (el.checked) triggerEl(el);
                }
            } else {
                var val = draft.fields[key];
                if (val !== undefined && val !== null) {
                    el.value = val;
                    triggerEl(el);
                }
            }
        });

        // 3 — Re-run volumetric weight calculations for restored package rows
        document.querySelectorAll('#packageTable tbody tr').forEach(function (row) {
            var w = row.querySelector('.weight');
            if (w) w.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // 4 — Show a dismissible notice so the user knows their draft was restored
        var notice = document.createElement('div');
        notice.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;'
            + 'background:#1b5e20;color:#fff;padding:10px 18px;border-radius:6px;'
            + 'font-size:13px;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,.25);'
            + 'display:flex;align-items:center;gap:12px;';
        notice.innerHTML = '<i class="fa fa-check-circle"></i> Draft restored — your previous entries are back.'
            + '<button onclick="this.parentNode.remove()" style="background:none;border:none;color:#fff;'
            + 'font-size:16px;cursor:pointer;line-height:1;padding:0;">&times;</button>';
        document.body.appendChild(notice);
        setTimeout(function () { if (notice.parentNode) notice.remove(); }, 6000);
    }

    // ---------- wire up ----------

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('create-shipment-form');
        if (!form) return;

        // Clear draft on successful submit
        form.addEventListener('submit', function () {
            try { sessionStorage.removeItem(DRAFT_KEY); } catch (e) {}
        });

        // Debounced save on any interaction
        function scheduleSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveDraft, 500);
        }
        form.addEventListener('input',  scheduleSave);
        form.addEventListener('change', scheduleSave);

        // Restore draft on load
        restoreDraft();
    });
})();
</script>
</body>
</html>

