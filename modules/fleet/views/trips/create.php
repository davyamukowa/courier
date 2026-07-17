<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.trip-step{display:none;}
.trip-step.active{display:block;}

/* Step indicator */
.step-indicator{display:flex;gap:0;margin-bottom:28px;}
.step-dot{flex:1;text-align:center;position:relative;}
.step-dot::before{content:'';position:absolute;top:18px;left:50%;right:-50%;height:2px;background:#ddd;z-index:0;}
.step-dot:last-child::before{display:none;}
.step-dot .circle{width:36px;height:36px;border-radius:50%;border:2px solid #ddd;background:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;position:relative;z-index:1;color:#aaa;}
.step-dot.done .circle{background:#28a745;border-color:#28a745;color:#fff;}
.step-dot.active .circle{background:#1976d2;border-color:#1976d2;color:#fff;}
.step-dot .step-lbl{font-size:11px;color:#888;margin-top:4px;}
.step-dot.active .step-lbl{color:#1976d2;font-weight:600;}

/* Route row */
.route-row{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.route-row .route-field{flex:1;}
.route-arrow{font-size:22px;color:#1976d2;flex-shrink:0;}

/* Track type cards */
.track-grid{display:flex;gap:16px;margin-top:8px;}
.track-card{flex:1;border:2px solid #ddd;border-radius:8px;padding:16px 20px;cursor:pointer;text-align:center;transition:all .2s;}
.track-card:hover{border-color:#1976d2;background:#f0f7ff;}
.track-card.selected{border-color:#1976d2;background:#e3f2fd;}
.track-card i{font-size:28px;color:#1976d2;display:block;margin-bottom:8px;}

/* Vehicle cards */
.vehicle-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:8px;max-height:340px;overflow-y:auto;}
.vehicle-card{border:2px solid #ddd;border-radius:8px;padding:12px 14px;cursor:pointer;transition:all .2s;}
.vehicle-card:hover{border-color:#1976d2;background:#f0f7ff;}
.vehicle-card.selected{border-color:#1976d2;background:#e3f2fd;}
.vehicle-card .plate{font-size:11px;color:#888;}

/* Load option cards */
.load-grid{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;}
.load-card{flex:1;min-width:100px;border:2px solid #ddd;border-radius:8px;padding:10px 14px;cursor:pointer;text-align:center;transition:all .2s;font-size:13px;}
.load-card:hover{border-color:#1976d2;background:#f0f7ff;}
.load-card.selected{border-color:#1976d2;background:#e3f2fd;font-weight:600;}

/* Confirm box */
.confirm-box{background:#f8f9fa;border:1px solid #e0e0e0;border-radius:8px;padding:16px 20px;font-size:13px;line-height:2.2;}
.confirm-box .confirm-row{display:flex;gap:8px;}
.confirm-box .confirm-label{color:#666;min-width:130px;font-weight:600;}

.shipment-info-box{background:#e8f5e9;border:1px solid #c8e6c9;border-radius:6px;padding:12px 16px;font-size:13px;margin-bottom:14px;}
.btn-nav{min-width:110px;}
.section-sub{font-size:11px;color:#888;margin-bottom:4px;}
</style>

<div id="fleet-page-wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-9 col-md-offset-1">
        <div class="panel_s">
          <div class="panel-heading">
            <h4 class="m-0">
              <i class="fa fa-road"></i>
              <?php echo !empty($parent_trip) ? 'Book Return Trip' : 'Book a Trip'; ?>
            </h4>
          </div>
          <div class="panel-body">

            <?php if (!empty($shipment)): ?>
            <div class="shipment-info-box">
              <strong><i class="fa fa-file-text"></i> Linked Shipment:</strong>
              Waybill <strong><?php echo htmlspecialchars($shipment->waybill_number); ?></strong>
              &nbsp;·&nbsp; <?php echo htmlspecialchars($shipment->sender_name ?? ''); ?> → <?php echo htmlspecialchars($shipment->recipient_name ?? ''); ?>
            </div>
            <?php endif; ?>

            <!-- Step indicator -->
            <div class="step-indicator" id="stepIndicator">
              <?php foreach (['Trip Details','Vehicle','Customer & Load','Confirm'] as $i => $lbl): ?>
              <div class="step-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-step="<?php echo $i; ?>">
                <div class="circle"><?php echo $i+1; ?></div>
                <div class="step-lbl"><?php echo $lbl; ?></div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- ═══ STEP 1: Trip Details ═══════════════════════════════════════ -->
            <div class="trip-step active" id="step-0">
              <h5 style="font-weight:700;margin-bottom:16px;">1. Trip Details</h5>

              <div class="form-group">
                <label>Departure Date &amp; Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="trip-date"
                       value="<?php echo date('Y-m-d\TH:i'); ?>">
              </div>

              <div class="route-row">
                <div class="route-field">
                  <label>From (Origin) <span class="text-danger">*</span></label>
                  <select class="form-control" id="sel-from-point" onchange="fromPointChanged()">
                    <option value="">— Select origin service point —</option>
                    <?php foreach ($service_points as $pt): ?>
                    <option value="<?php echo $pt->id; ?>"><?php echo htmlspecialchars($pt->name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="route-arrow"><i class="fa fa-long-arrow-right"></i></div>
                <div class="route-field">
                  <label>To (Destination) <span class="text-danger">*</span></label>
                  <select class="form-control" id="sel-to-point" onchange="toPointChanged()">
                    <option value="">— Select destination service point —</option>
                    <?php foreach ($service_points as $pt): ?>
                    <option value="<?php echo $pt->id; ?>"><?php echo htmlspecialchars($pt->name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Trip Type</label>
                <div class="track-grid">
                  <div class="track-card selected" data-track="single" onclick="selectTrack('single',this)">
                    <i class="fa fa-arrow-right"></i>
                    <strong>Single Track</strong>
                    <p style="font-size:12px;color:#666;margin-top:4px;">Deliver and return empty.</p>
                  </div>
                  <div class="track-card" data-track="double" onclick="selectTrack('double',this)">
                    <i class="fa fa-exchange"></i>
                    <strong>Double Track</strong>
                    <p style="font-size:12px;color:#666;margin-top:4px;">Deliver and pick up return cargo.</p>
                  </div>
                </div>
              </div>

              <div style="margin-top:20px;text-align:right;">
                <button class="btn btn-primary btn-nav" id="btn-step1-next" onclick="validateStep1()">
                  Next <i class="fa fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- ═══ STEP 2: Vehicle ════════════════════════════════════════════ -->
            <div class="trip-step" id="step-1">
              <h5 style="font-weight:700;margin-bottom:12px;">2. Select Vehicle</h5>

              <?php if (empty($vehicles)): ?>
              <div class="alert alert-warning">No vehicles found. Please <a href="<?php echo admin_url('fleet/vehicles'); ?>">add vehicles</a> first.</div>
              <?php else: ?>
              <div class="vehicle-grid">
                <?php foreach ($vehicles as $v): ?>
                <div class="vehicle-card" data-vid="<?php echo $v->id; ?>" data-name="<?php echo htmlspecialchars($v->name . ($v->license_plate ? ' ('.$v->license_plate.')' : ''), ENT_QUOTES); ?>" onclick="selectVehicle(<?php echo $v->id; ?>,this)">
                  <i class="fa fa-truck" style="color:#1976d2;font-size:20px;margin-bottom:6px;display:block;"></i>
                  <strong><?php echo htmlspecialchars($v->name); ?></strong>
                  <?php if ($v->license_plate): ?><div class="plate"><?php echo htmlspecialchars($v->license_plate); ?></div><?php endif; ?>
                  <?php if ($v->make || $v->model): ?><div class="plate"><?php echo htmlspecialchars(trim(($v->make ?? '') . ' ' . ($v->model ?? ''))); ?></div><?php endif; ?>
                  <?php if ($v->odometer): ?><div class="plate">Odo: <?php echo number_format($v->odometer); ?> km</div><?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <div style="margin-top:20px;display:flex;justify-content:space-between;">
                <button class="btn btn-default btn-nav" onclick="goToStep(0)"><i class="fa fa-arrow-left"></i> Back</button>
                <button class="btn btn-primary btn-nav" id="btn-step2-next" onclick="validateStep2()" disabled>
                  Next <i class="fa fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- ═══ STEP 3: Customer & Load ════════════════════════════════════ -->
            <div class="trip-step" id="step-2">
              <h5 style="font-weight:700;margin-bottom:16px;">3. Customer &amp; Load Details</h5>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Customer</label>
                    <select class="form-control" id="sel-customer">
                      <option value="">— Select Customer (optional) —</option>
                      <?php foreach ($customers as $c): ?>
                      <option value="<?php echo $c->userid; ?>"><?php echo htmlspecialchars($c->company); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Driver</label>
                    <select class="form-control" id="sel-driver">
                      <option value="">— Select Driver (optional) —</option>
                      <?php foreach ($drivers as $d): ?>
                      <option value="<?php echo $d->staffid; ?>"><?php echo htmlspecialchars($d->firstname . ' ' . $d->lastname); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Point of Loading</label>
                <div class="section-sub">The service centre where the vehicle is loaded. Defaults to the origin point.</div>
                <select class="form-control" id="sel-loading-point">
                  <option value="">— Same as origin —</option>
                  <?php foreach ($service_points as $pt): ?>
                  <option value="<?php echo $pt->id; ?>"><?php echo htmlspecialchars($pt->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Vehicle Status at Departure</label>
                <div class="load-grid" id="vstatus-cards">
                  <div class="load-card selected" data-vstatus="empty" onclick="selectVStatus('empty',this)"><i class="fa fa-truck" style="color:#aaa;"></i><br>Empty</div>
                  <div class="load-card" data-vstatus="half_load" onclick="selectVStatus('half_load',this)"><i class="fa fa-truck" style="color:#f57c00;"></i><br>Half Load</div>
                  <div class="load-card" data-vstatus="partly_loaded" onclick="selectVStatus('partly_loaded',this)"><i class="fa fa-truck" style="color:#1976d2;"></i><br>Partly Loaded</div>
                </div>
              </div>

              <div class="form-group">
                <label>Load Type</label>
                <div class="load-grid" id="loadtype-cards">
                  <div class="load-card selected" data-ltype="full" onclick="selectLoadType('full',this)"><i class="fa fa-archive" style="color:#2e7d32;"></i><br>Full Load</div>
                  <div class="load-card" data-ltype="half" onclick="selectLoadType('half',this)"><i class="fa fa-archive" style="color:#f57c00;"></i><br>Half Load</div>
                  <div class="load-card" data-ltype="part" onclick="selectLoadType('part',this)"><i class="fa fa-archive" style="color:#aaa;"></i><br>Part Load</div>
                </div>
              </div>

              <div class="form-group">
                <label>Notes (optional)</label>
                <textarea class="form-control" id="trip-notes" rows="2" placeholder="Special instructions, cargo details..."></textarea>
              </div>

              <div style="margin-top:20px;display:flex;justify-content:space-between;">
                <button class="btn btn-default btn-nav" onclick="goToStep(1)"><i class="fa fa-arrow-left"></i> Back</button>
                <button class="btn btn-primary btn-nav" onclick="goToStep(3)">Next <i class="fa fa-arrow-right"></i></button>
              </div>
            </div>

            <!-- ═══ STEP 4: Confirm ════════════════════════════════════════════ -->
            <div class="trip-step" id="step-3">
              <h5 style="font-weight:700;margin-bottom:16px;">4. Confirm Booking</h5>
              <div class="confirm-box">
                <div class="confirm-row"><span class="confirm-label">Departure:</span> <span id="c-date">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Route:</span> <span id="c-route">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Trip Type:</span> <span id="c-track">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Vehicle:</span> <span id="c-vehicle">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Driver:</span> <span id="c-driver">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Customer:</span> <span id="c-customer">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Loading Point:</span> <span id="c-loadpt">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Vehicle Status:</span> <span id="c-vstatus">—</span></div>
                <div class="confirm-row"><span class="confirm-label">Load Type:</span> <span id="c-ltype">—</span></div>
                <?php if (!empty($shipment)): ?>
                <div class="confirm-row"><span class="confirm-label">Shipment:</span> <span><?php echo htmlspecialchars($shipment->waybill_number ?? '—'); ?></span></div>
                <?php endif; ?>
              </div>
              <div style="margin-top:24px;display:flex;justify-content:space-between;">
                <button class="btn btn-default btn-nav" onclick="goToStep(2)"><i class="fa fa-arrow-left"></i> Back</button>
                <button class="btn btn-success btn-nav" id="btn-confirm" onclick="submitBooking()">
                  <i class="fa fa-check"></i> Confirm &amp; Book
                </button>
              </div>
            </div>

          </div><!-- /panel-body -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var currentStep   = 0;
var selectedTrack = 'single';
var selectedVehicleId   = 0;
var selectedVehicleName = '';
var selectedFromId   = 0;
var selectedFromName = '';
var selectedToId     = 0;
var selectedToName   = '';
var selectedLoadingPointId   = 0;
var selectedLoadingPointName = '';
var selectedVStatus  = 'empty';
var selectedLoadType = 'full';

var CSRF_NAME   = '<?php echo $this->security->get_csrf_token_name(); ?>';
var CSRF_HASH   = '<?php echo $this->security->get_csrf_hash(); ?>';
var STORE_URL   = '<?php echo admin_url('fleet/trips/store'); ?>';
var SHIPMENT_ID = <?php echo (int)($shipment_id ?? 0); ?>;
var PARENT_TRIP_ID = <?php echo (int)($parent_trip_id ?? 0); ?>;

<?php if (!empty($shipment)): ?>
// Autofill customer from shipment's invoice client ID
var PRE_CUSTOMER_ID = '<?php echo $shipment->client_id ?? ''; ?>';
document.addEventListener('DOMContentLoaded', function() {
    if (PRE_CUSTOMER_ID) {
        var custSel = document.getElementById('sel-customer');
        if (custSel) {
            custSel.value = PRE_CUSTOMER_ID;
            if (typeof $(custSel).selectpicker === 'function') {
                $(custSel).selectpicker('refresh');
            }
        }
    }
});
<?php endif; ?>

<?php if (!empty($parent_trip)): ?>
selectedVehicleId   = <?php echo (int)$parent_trip->vehicle_id; ?>;
selectedVehicleName = '<?php echo addslashes($parent_trip->vehicle_name ?? ''); ?>';
<?php endif; ?>

// ── Step navigation ─────────────────────────────────────────────────────────
function goToStep(n) {
    if (n === 3) buildConfirm();
    document.querySelectorAll('.trip-step').forEach((s,i) => s.classList.toggle('active', i === n));
    document.querySelectorAll('.step-dot').forEach((d,i) => {
        d.classList.toggle('active', i === n);
        d.classList.toggle('done', i < n);
    });
    currentStep = n;
    window.scrollTo(0,0);
}

// ── Step 1 ───────────────────────────────────────────────────────────────────
function fromPointChanged() {
    var sel = document.getElementById('sel-from-point');
    selectedFromId   = parseInt(sel.value) || 0;
    selectedFromName = sel.options[sel.selectedIndex]?.text || '';
    // Sync loading point default
    var lp = document.getElementById('sel-loading-point');
    if (!lp.value && sel.value) {
        // Auto-select loading point same as origin
    }
}
function toPointChanged() {
    var sel = document.getElementById('sel-to-point');
    selectedToId   = parseInt(sel.value) || 0;
    selectedToName = sel.options[sel.selectedIndex]?.text || '';
}
function selectTrack(type, el) {
    selectedTrack = type;
    document.querySelectorAll('.track-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
function validateStep1() {
    selectedFromId = parseInt(document.getElementById('sel-from-point').value) || 0;
    selectedToId   = parseInt(document.getElementById('sel-to-point').value) || 0;
    if (!selectedFromId) { alert('Please select an origin service point.'); return; }
    if (!selectedToId)   { alert('Please select a destination service point.'); return; }
    if (selectedFromId === selectedToId) { alert('Origin and destination must be different.'); return; }
    goToStep(1);
}

// ── Step 2 ───────────────────────────────────────────────────────────────────
function selectVehicle(id, el) {
    selectedVehicleId   = id;
    selectedVehicleName = el.dataset.name || ('Vehicle #' + id);
    document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('btn-step2-next').disabled = false;
}
function validateStep2() {
    if (!selectedVehicleId) { alert('Please select a vehicle.'); return; }
    goToStep(2);
}

// ── Step 3 ───────────────────────────────────────────────────────────────────
function selectVStatus(v, el) {
    selectedVStatus = v;
    document.querySelectorAll('#vstatus-cards .load-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
function selectLoadType(v, el) {
    selectedLoadType = v;
    document.querySelectorAll('#loadtype-cards .load-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

// ── Step 4 (Confirm) ─────────────────────────────────────────────────────────
function buildConfirm() {
    var fromSel    = document.getElementById('sel-from-point');
    var toSel      = document.getElementById('sel-to-point');
    var lpSel      = document.getElementById('sel-loading-point');
    var driverSel  = document.getElementById('sel-driver');
    var customerSel= document.getElementById('sel-customer');
    var tripDate   = document.getElementById('trip-date').value;

    selectedFromName = fromSel.options[fromSel.selectedIndex]?.text || '—';
    selectedToName   = toSel.options[toSel.selectedIndex]?.text || '—';
    selectedLoadingPointId   = parseInt(lpSel.value) || selectedFromId;
    selectedLoadingPointName = lpSel.value ? lpSel.options[lpSel.selectedIndex]?.text : selectedFromName;

    document.getElementById('c-date').textContent    = tripDate ? tripDate.replace('T',' ') : '—';
    document.getElementById('c-route').textContent   = selectedFromName + ' → ' + selectedToName;
    document.getElementById('c-track').textContent   = selectedTrack === 'double' ? 'Double Track' : 'Single Track';
    document.getElementById('c-vehicle').textContent = selectedVehicleName || '—';
    document.getElementById('c-driver').textContent  = driverSel.value ? driverSel.options[driverSel.selectedIndex]?.text : '—';
    document.getElementById('c-customer').textContent= customerSel.value ? customerSel.options[customerSel.selectedIndex]?.text : '—';
    document.getElementById('c-loadpt').textContent  = selectedLoadingPointName;

    var vsMap = {empty:'Empty',half_load:'Half Load',partly_loaded:'Partly Loaded'};
    var ltMap = {full:'Full Load',half:'Half Load',part:'Part Load'};
    document.getElementById('c-vstatus').textContent = vsMap[selectedVStatus] || selectedVStatus;
    document.getElementById('c-ltype').textContent   = ltMap[selectedLoadType] || selectedLoadType;
}

// ── Submit ────────────────────────────────────────────────────────────────────
function submitBooking() {
    var btn = document.getElementById('btn-confirm');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Booking...';

    var lpSel      = document.getElementById('sel-loading-point');
    var loadingPtId = parseInt(lpSel.value) || selectedFromId;
    var fd = new FormData();
    var tDate = document.getElementById('trip-date').value.replace('T', ' ');
    if (tDate && tDate.length === 16) { tDate += ':00'; }
    fd.append('trip_date',        tDate);
    fd.append('track_type',       selectedTrack);
    fd.append('vehicle_id',       selectedVehicleId);
    fd.append('driver_id',        document.getElementById('sel-driver').value || '');
    fd.append('customer_id',      document.getElementById('sel-customer').value || '');
    fd.append('from_point_id',    selectedFromId);
    fd.append('to_point_id',      selectedToId);
    fd.append('loading_point_id', loadingPtId);
    fd.append('vehicle_status',   selectedVStatus);
    fd.append('load_type',        selectedLoadType);
    fd.append('shipment_id',      SHIPMENT_ID);
    fd.append('parent_trip_id',   PARENT_TRIP_ID);
    fd.append('notes',            document.getElementById('trip-notes').value);
    fd.append(CSRF_NAME, CSRF_HASH);

    fetch(STORE_URL, {method:'POST', body:fd})
        .then(async r => {
            const text = await r.text();
            try {
                return JSON.parse(text);
            } catch(e) {
                throw new Error(text);
            }
        })
        .then(res => {
            if (res.success) {
                window.location.href = res.redirect;
            } else {
                alert(res.message || 'Booking failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check"></i> Confirm &amp; Book';
            }
        })
        .catch((err) => {
            console.error(err);
            alert('Server error: ' + err.message.substring(0, 150));
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Confirm &amp; Book';
        });
}

<?php if (!empty($parent_trip)): ?>
window.addEventListener('DOMContentLoaded', function() {
    var card = document.querySelector('.vehicle-card[data-vid="<?php echo (int)$parent_trip->vehicle_id; ?>"]');
    if (card) { selectVehicle(<?php echo (int)$parent_trip->vehicle_id; ?>, card); }
});
<?php endif; ?>
</script>
<?php init_tail(); ?>
