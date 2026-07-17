<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'network']); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
.select2-container .select2-selection--single {
    background-color: #f9fafb;
    border: 1px solid #d1d5db;
    color: #111827;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    padding: 5px 10px 10px 10px;
    width: 100%;
    height: 40px;
}
.select2-selection__arrow { transform: translateY(30%); }
</style>


        <?php $show_company_section = $this->session->userdata('show_company_section') ?? false; ?>

        <!-- Top navigation bar -->
        <div class="cgs-card" style="margin-bottom:20px;">
            <div class="cgs-card__header" style="border-bottom:none;margin-bottom:0;padding-bottom:0;">
                <h4 class="cgs-card__title"><i class="fa fa-users"></i> Courier Logistic — Agents</h4>
                <div class="cgs-card__actions">
                    <a href="<?php echo admin_url('courier_logistic/agents/main?group=create_agent'); ?>" class="cgs-btn cgs-btn--primary">
                        <i class="fa fa-plus"></i> Create Agent
                    </a>
                    <a href="<?php echo admin_url('courier_logistic/agents/main'); ?>" class="cgs-btn cgs-btn--outline">
                        <i class="fa fa-list"></i> List of Agents
                    </a>
                    <button id="sync-permissions-btn" class="cgs-btn cgs-btn--outline" type="button">
                        <i class="fa fa-refresh" id="sync-icon"></i>
                        <span id="sync-text"> Sync Permissions</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main content — full width -->
        <div class="row">
            <div class="col-md-12">
                <?php echo $group_content ?? ''; ?>
            </div>
        </div>

    </div><!-- .cgs-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const _csrfName  = '<?php echo $this->security->get_csrf_token_name(); ?>';
const _csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';

$(function () {

    /* ── Sync Permissions ── */
    $('#sync-permissions-btn').on('click', function () {
        var $btn = $(this).prop('disabled', true);
        $('#sync-icon').removeClass('fa-refresh').addClass('fa-spinner fa-spin');
        $.ajax({
            url: '<?php echo admin_url("courier_logistic/agents/sync_role_permissions"); ?>',
            method: 'GET',
            dataType: 'json',
            success: function (r) {
                alert_float(r.message ? 'success' : 'danger', r.message || 'Done');
            },
            error: function () { alert_float('danger', 'Sync failed.'); },
            complete: function () {
                $btn.prop('disabled', false);
                $('#sync-icon').removeClass('fa-spinner fa-spin').addClass('fa-refresh');
            }
        });
    });

    /* ── Country / service-point machinery (only active when create form is rendered) ── */
    if (!document.getElementById('country_id')) return;

    $('#country_id').select2({});
    $('#company_country_id').select2({});

    var KENYA_COUNTIES = [
        { name: 'Mombasa', num: 1 }, { name: 'Kwale', num: 2 }, { name: 'Kilifi', num: 3 },
        { name: 'Tana River', num: 4 }, { name: 'Lamu', num: 5 }, { name: 'Taita/Taveta', num: 6 },
        { name: 'Garissa', num: 7 }, { name: 'Wajir', num: 8 }, { name: 'Mandera', num: 9 },
        { name: 'Marsabit', num: 10 }, { name: 'Isiolo', num: 11 }, { name: 'Meru', num: 12 },
        { name: 'Tharaka-Nithi', num: 13 }, { name: 'Embu', num: 14 }, { name: 'Kitui', num: 15 },
        { name: 'Machakos', num: 16 }, { name: 'Makueni', num: 17 }, { name: 'Nyandarua', num: 18 },
        { name: 'Nyeri', num: 19 }, { name: 'Kirinyaga', num: 20 }, { name: "Murang'a", num: 21 },
        { name: 'Kiambu', num: 22 }, { name: 'Turkana', num: 23 }, { name: 'West Pokot', num: 24 },
        { name: 'Samburu', num: 25 }, { name: 'Trans-Nzoia', num: 26 }, { name: 'Uasin Gishu', num: 27 },
        { name: 'Elgeyo-Marakwet', num: 28 }, { name: 'Nandi', num: 29 }, { name: 'Baringo', num: 30 },
        { name: 'Laikipia', num: 31 }, { name: 'Nakuru', num: 32 }, { name: 'Narok', num: 33 },
        { name: 'Kajiado', num: 34 }, { name: 'Kericho', num: 35 }, { name: 'Bomet', num: 36 },
        { name: 'Kakamega', num: 37 }, { name: 'Vihiga', num: 38 }, { name: 'Bungoma', num: 39 },
        { name: 'Busia', num: 40 }, { name: 'Siaya', num: 41 }, { name: 'Kisumu', num: 42 },
        { name: 'Homa Bay', num: 43 }, { name: 'Migori', num: 44 }, { name: 'Kisii', num: 45 },
        { name: 'Nyamira', num: 46 }, { name: 'Nairobi City', num: 47 }
    ];
    var KENYA_COUNTY_NAMES = new Set(KENYA_COUNTIES.map(function(c){ return c.name.toLowerCase(); }));
    var SP_JSON_URL = '<?php echo admin_url("courier_logistic/settings/service_points_json"); ?>';

    function loadCities(countryId, countryName, selectId, stateHiddenId, preselectValue) {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        try { $(sel).select2('destroy'); } catch (e) {}
        sel.innerHTML = '<option value="">Loading…</option>';
        var isKenya = (countryName && countryName.trim().toLowerCase() === 'kenya');
        var customPromise = countryId
            ? fetch(SP_JSON_URL + '?country_id=' + countryId).then(function(r){ return r.json(); }).catch(function(){ return { data: [] }; })
            : Promise.resolve({ data: [] });

        if (isKenya) {
            customPromise.then(function(customData) {
                var customCities = (customData && Array.isArray(customData.data)) ? customData.data : [];
                sel.innerHTML = '<option value="">-- Select County / Service Point --</option>';
                var countyGroup = document.createElement('optgroup');
                countyGroup.label = 'Kenya Counties';
                KENYA_COUNTIES.forEach(function(county) {
                    var opt = document.createElement('option');
                    opt.value = county.name; opt.dataset.idx = county.num;
                    opt.textContent = String(county.num).padStart(3, '0') + ' — ' + county.name;
                    if (preselectValue && county.name === preselectValue) opt.selected = true;
                    countyGroup.appendChild(opt);
                });
                sel.appendChild(countyGroup);
                var extras = customCities.filter(function(c){ return !KENYA_COUNTY_NAMES.has(c.toLowerCase()); });
                if (extras.length) {
                    var customGroup = document.createElement('optgroup');
                    customGroup.label = 'Additional Service Points';
                    extras.forEach(function(city, i) {
                        var opt = document.createElement('option');
                        opt.value = city; opt.dataset.idx = 48 + i;
                        opt.textContent = '★ ' + city;
                        if (preselectValue && city === preselectValue) opt.selected = true;
                        customGroup.appendChild(opt);
                    });
                    sel.appendChild(customGroup);
                }
                $(sel).select2({ placeholder: '-- Select County / Service Point --', allowClear: true });
                if (preselectValue) {
                    var chosen = Array.from(sel.options).find(function(o){ return o.value === preselectValue; });
                    if (chosen) { document.getElementById(stateHiddenId).value = chosen.dataset.idx || 0; generateAgentNumber(); }
                }
            });
        } else {
            var apiPromise = (countryName && countryName.trim())
                ? fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                      method: 'POST', headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ country: countryName.trim() })
                  }).then(function(r){ return r.json(); }).catch(function(){ return { error: true }; })
                : Promise.resolve({ error: true });
            Promise.all([apiPromise, customPromise]).then(function(results) {
                var apiData = results[0], customData = results[1];
                var apiCities = (!apiData.error && Array.isArray(apiData.data)) ? apiData.data : [];
                var customCities = (customData && Array.isArray(customData.data)) ? customData.data : [];
                var customSet = new Set(customCities.map(function(c){ return c.toLowerCase(); }));
                var merged = customCities.slice();
                apiCities.forEach(function(city){ if (!customSet.has(city.toLowerCase())) merged.push(city); });
                merged.sort();
                sel.innerHTML = '<option value="">-- Select Service Point --</option>';
                if (merged.length) {
                    var customLower = new Set(customCities.map(function(c){ return c.toLowerCase(); }));
                    merged.forEach(function(city, i){
                        var opt = document.createElement('option');
                        opt.value = city; opt.dataset.idx = i + 1;
                        opt.textContent = customLower.has(city.toLowerCase()) ? '★ ' + city : city;
                        if (preselectValue && city === preselectValue) opt.selected = true;
                        sel.appendChild(opt);
                    });
                } else { sel.innerHTML = '<option value="">No cities found</option>'; }
                $(sel).select2({ placeholder: '-- Select Service Point --', allowClear: true });
                if (preselectValue) {
                    var chosen = Array.from(sel.options).find(function(o){ return o.value === preselectValue; });
                    if (chosen) { document.getElementById(stateHiddenId).value = chosen.dataset.idx || 0; generateAgentNumber(); }
                }
            });
        }
    }

    function updateServicePointLabel(countryName, labelSelector) {
        var lbl = document.querySelector(labelSelector);
        if (!lbl) return;
        lbl.innerHTML = (countryName && countryName.trim().toLowerCase() === 'kenya')
            ? 'County / Service Point <span class="req">*</span>'
            : 'Service Point <span class="req">*</span>';
    }

    $('#country_id').on('change', function () {
        var name = $(this).find('option:selected').text();
        updateServicePointLabel(name, 'label[for="station"]');
        loadCities($(this).val(), name, 'station', 'state_id');
    });
    $('#company_country_id').on('change', function () {
        var name = $(this).find('option:selected').text();
        updateServicePointLabel(name, 'label[for="company_station"]');
        loadCities($(this).val(), name, 'company_station', 'company_state_id');
    });

    var _preInd  = <?php echo json_encode($this->session->flashdata('station') ?: ''); ?>;
    var _preCo   = <?php echo json_encode($this->session->flashdata('company_station') ?: ''); ?>;
    var _indName = $('#country_id').find('option:selected').text();
    var _coName  = $('#company_country_id').find('option:selected').text();
    updateServicePointLabel(_indName, 'label[for="station"]');
    updateServicePointLabel(_coName, 'label[for="company_station"]');
    loadCities($('#country_id').val(), _indName, 'station', 'state_id', _preInd);
    loadCities($('#company_country_id').val(), _coName, 'company_station', 'company_state_id', _preCo);

    var showCompanySection = <?php echo $show_company_section ? 'true' : 'false'; ?>;

    window.toggleAgentType = function (type) {
        type = type || document.getElementById('type').value;
        document.getElementById('individualContent').style.display = type === 'individual' ? 'block' : 'none';
        document.getElementById('companyContent').style.display   = type === 'company'    ? 'block' : 'none';
        $('#individualContent input, #individualContent select, #individualContent textarea').prop('disabled', type !== 'individual');
        $('#companyContent input, #companyContent select, #companyContent textarea').prop('disabled', type !== 'company');
        generateAgentNumber();
    };

    toggleAgentType(showCompanySection ? 'company' : 'individual');
    generateAgentNumber();

    function generateAgentNumber() {
        var agentType = document.getElementById('type') ? document.getElementById('type').value : null;
        if (!agentType) return;
        if (agentType === 'company') {
            var country_id = document.getElementById('company_country_id').value;
            var sp = document.getElementById('company_station');
            var sp_idx = sp ? parseInt(sp.selectedOptions[0] && sp.selectedOptions[0].dataset.idx || 0) : 0;
            if (country_id && sp_idx) { document.getElementById('company_state_id').value = sp_idx; populateAgentNumber(country_id, sp_idx); }
        } else {
            var country_id = document.getElementById('country_id').value;
            var sp = document.getElementById('station');
            var sp_idx = sp ? parseInt(sp.selectedOptions[0] && sp.selectedOptions[0].dataset.idx || 0) : 0;
            if (country_id && sp_idx) { document.getElementById('state_id').value = sp_idx; populateAgentNumber(country_id, sp_idx); }
        }
    }

    window.populateAgentNumber = function (country_id, state_id) {
        $.ajax({
            url: '<?php echo admin_url("courier_logistic/agents/agent_number"); ?>',
            method: 'GET',
            data: { country_id: country_id, state_id: state_id },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    var type = document.getElementById('type').value;
                    var el = document.getElementById(type === 'company' ? 'company_unique_number' : 'unique_number');
                    if (el) el.value = r.new_agent_number;
                }
            }
        });
    };

    $('#station').change(function () {
        var idx = parseInt($(this).find('option:selected').data('idx') || 0);
        $('#state_id').val(idx);
        var cid = $('#country_id').val();
        if (cid && idx) populateAgentNumber(cid, idx);
    });
    $('#company_station').change(function () {
        var idx = parseInt($(this).find('option:selected').data('idx') || 0);
        $('#company_state_id').val(idx);
        var cid = $('#company_country_id').val();
        if (cid && idx) populateAgentNumber(cid, idx);
    });
    $('#country_id, #company_country_id').change(function () { generateAgentNumber(); });
});
</script>

