<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Live Trip Tracking</title>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>
    <link rel="manifest" href="<?php echo site_url('admin/fleet/trips/manifest/' . $token); ?>">
    <meta name="theme-color" content="#0d1b2a">
    <link rel="apple-touch-icon" href="<?php echo site_url('admin/fleet/trips/icon/192'); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Trip Tracker">
    <meta name="mobile-web-app-capable" content="yes">

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { height: 100%; }
        body {
            margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1b2a; color: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 24px;
            padding-top: calc(24px + env(safe-area-inset-top));
            padding-bottom: calc(24px + env(safe-area-inset-bottom));
        }
        h1 { font-size: 18px; margin: 0 0 4px; color: #8ecae6; }
        .trip-info { color: #cbd5e1; font-size: 14px; margin-bottom: 22px; line-height: 1.6; }
        .status-dot {
            width: 14px; height: 14px; border-radius: 50%; display: inline-block;
            background: #ef4444; margin-right: 6px; vertical-align: middle;
        }
        .status-dot.live { background: #22c55e; box-shadow: 0 0 0 0 rgba(34,197,94,.7); animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34,197,94,.6); }
            70% { box-shadow: 0 0 0 12px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        #status_text { font-size: 15px; margin-bottom: 20px; }
        button {
            font-size: 18px; font-weight: 700; padding: 18px 36px; border-radius: 12px;
            border: none; cursor: pointer; min-width: 260px; margin: 6px 0;
        }
        #start_btn { background: #22c55e; color: #06281a; }
        #stop_btn { background: #ef4444; color: #2b0a0a; display: none; }
        #install_btn { background: #1565c0; color: #fff; font-size: 14px; padding: 12px 24px; min-width: 260px; display: none; }
        #last_sent { color: #94a3b8; font-size: 12px; margin-top: 16px; }
        #queued_note { color: #fbbf24; font-size: 12px; margin-top: 4px; display: none; }
        #error_box {
            display: none; margin-top: 20px; background: #7f1d1d; color: #fecaca;
            padding: 12px 16px; border-radius: 8px; font-size: 13px; max-width: 320px;
        }
        #ios_hint {
            display: none; margin-top: 16px; background: #1e293b; color: #cbd5e1;
            padding: 12px 16px; border-radius: 8px; font-size: 12px; max-width: 300px; line-height: 1.6;
        }
        .gs-panel { text-align: left; max-width: 300px; width: 100%; }
        .gs-panel label { display: block; font-size: 13px; color: #cbd5e1; margin: 12px 0 4px; }
        .gs-panel input[type="text"], .gs-panel textarea {
            width: 100%; padding: 12px; border-radius: 8px; border: none; font-size: 15px; font-family: inherit;
        }
        #signature_pad_canvas { width: 100%; height: 160px; background: #fff; border-radius: 8px; touch-action: none; display: block; }
        .gs-btn-wide { width: 100%; margin-top: 14px; }
        .gs-btn-secondary { background: #334155; color: #fff; font-size: 14px; padding: 12px; }
        #deliver_btn { background: #22c55e; color: #06281a; }
        #cancel_btn { background: #ef4444; color: #2b0a0a; }
        #confirm_deliver_btn { background: #22c55e; color: #06281a; }
        #confirm_cancel_btn { background: #ef4444; color: #2b0a0a; }
        #trip_complete_banner {
            display: none; margin-top: 20px; background: #052e1a; color: #86efac;
            padding: 16px 20px; border-radius: 10px; font-size: 15px; font-weight: 700; max-width: 300px;
        }
    </style>
</head>
<body>
    <h1>Trip #<?php echo (int) $trip->id; ?></h1>
    <div class="trip-info" id="trip_status_line">
        Vehicle status: <span id="trip_status_text"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $trip->status ?? 'booked'))); ?></span><br>
        Keep this open and your screen on while you drive — location is sent automatically.
    </div>

    <?php
    $already_started = in_array($trip->status, ['started', 'offloading', 'completed'], true);
    $has_shipment     = !empty($trip->shipment_id);
    $trip_finished    = in_array($trip->status, ['completed', 'cancelled'], true);
    ?>

    <!-- ── Step 1: Start Trip (odometer reading) — skipped if already started ── -->
    <div id="start_trip_section" style="<?php echo ($already_started || $trip_finished) ? 'display:none;' : ''; ?>">
        <label style="display:block; font-size:13px; color:#cbd5e1; margin-bottom:8px;">
            Starting odometer reading (km)
        </label>
        <input type="number" id="start_odometer_input" placeholder="e.g. 45800"
               style="font-size:18px; padding:14px; border-radius:10px; border:none; width:260px; text-align:center; margin-bottom:14px;">
        <br>
        <button id="start_trip_btn" onclick="startTrip()" style="background:#3b82f6; color:#fff;">Start Trip</button>
        <div id="start_trip_error" style="display:none; margin-top:12px; background:#7f1d1d; color:#fecaca; padding:10px 14px; border-radius:8px; font-size:13px; max-width:300px;"></div>
    </div>

    <!-- ── Step 2: Share location — shown once the trip has started ──────────── -->
    <div id="sharing_section" style="<?php echo ($already_started && !$trip_finished) ? '' : 'display:none;'; ?>">
        <div>
            <span id="status_dot" class="status-dot"></span>
            <span id="status_text">Not sharing location</span>
        </div>

        <button id="start_btn" onclick="startSharing()">Start Sharing Location</button>
        <button id="stop_btn" onclick="stopSharing()">Stop Sharing</button>
    </div>

    <?php if ($has_shipment): ?>
    <!-- ── Step 3: Deliver or cancel — shown once the trip has started ───────── -->
    <div id="delivery_actions_section" style="<?php echo ($already_started && !$trip_finished) ? '' : 'display:none;'; ?> margin-top:24px;">
        <button id="deliver_btn" onclick="showDeliverForm()">✅ Mark Delivered</button>
        <button id="cancel_btn" onclick="showCancelForm()">✖ Cancel Shipment</button>
    </div>

    <div id="deliver_form" class="gs-panel" style="display:none;">
        <h3 style="color:#8ecae6; font-size:15px; margin:0;">Confirm Delivery</h3>
        <label for="deliver_first_name">Customer first name</label>
        <input type="text" id="deliver_first_name">
        <label for="deliver_last_name">Customer last name</label>
        <input type="text" id="deliver_last_name">
        <label>Customer signature</label>
        <canvas id="signature_pad_canvas"></canvas>
        <button type="button" onclick="clearSignature()" class="gs-btn-secondary" style="width:100%; margin-top:8px;">Clear Signature</button>
        <button id="confirm_deliver_btn" onclick="submitDelivery()" class="gs-btn-wide">Confirm Delivery</button>
        <button type="button" onclick="hideForms()" class="gs-btn-secondary gs-btn-wide">Back</button>
        <div id="deliver_error" style="display:none; margin-top:10px; background:#7f1d1d; color:#fecaca; padding:10px 14px; border-radius:8px; font-size:13px;"></div>
    </div>

    <div id="cancel_form" class="gs-panel" style="display:none;">
        <h3 style="color:#fca5a5; font-size:15px; margin:0;">Cancel Shipment</h3>
        <label for="cancel_reason">Reason for cancelling</label>
        <textarea id="cancel_reason" rows="4"></textarea>
        <button id="confirm_cancel_btn" onclick="submitCancel()" class="gs-btn-wide">Confirm Cancellation</button>
        <button type="button" onclick="hideForms()" class="gs-btn-secondary gs-btn-wide">Back</button>
        <div id="cancel_error" style="display:none; margin-top:10px; background:#7f1d1d; color:#fecaca; padding:10px 14px; border-radius:8px; font-size:13px;"></div>
    </div>

    <div id="trip_complete_banner" style="<?php echo $trip_finished ? 'display:block;' : ''; ?>">
        <?php echo $trip->status === 'cancelled' ? '✖ Shipment cancelled.' : '✅ Delivered — thank you!'; ?>
    </div>
    <?php endif; ?>

    <br>
    <button id="install_btn" onclick="installApp()">📲 Install App to Home Screen</button>

    <div id="last_sent"></div>
    <div id="queued_note">Offline — queued locally, will send once back online.</div>
    <div id="error_box"></div>
    <div id="ios_hint">
        To install: tap the <strong>Share</strong> icon in Safari's toolbar, then
        <strong>"Add to Home Screen"</strong>. Opening it from the home screen keeps it full-screen
        and easier to keep active while driving.
    </div>

    <script>
        var TOKEN = <?php echo json_encode($token); ?>;
        var RECORD_URL = <?php echo json_encode(site_url('admin/fleet/trips/record_location')); ?>;
        var START_TRIP_URL = <?php echo json_encode(site_url('admin/fleet/trips/driver_start_trip')); ?>;
        var DELIVER_URL = <?php echo json_encode(site_url('admin/fleet/trips/driver_deliver_shipment')); ?>;
        var CANCEL_URL = <?php echo json_encode(site_url('admin/fleet/trips/driver_cancel_shipment')); ?>;
        var SW_URL = <?php echo json_encode(site_url('admin/fleet/trips/sw')); ?>;
        var sigPad = null;
        var QUEUE_KEY = 'trip_tracker_queue_' + TOKEN;
        var watchId = null;
        var wakeLock = null;
        var lastSentAt = 0;
        var MIN_INTERVAL_MS = 10000; // don't post more often than every 10s, even if GPS updates faster

        function startTrip() {
            var odo = document.getElementById('start_odometer_input').value;
            var errBox = document.getElementById('start_trip_error');
            errBox.style.display = 'none';

            if (!odo || Number(odo) <= 0) {
                errBox.textContent = 'Please enter a valid odometer reading.';
                errBox.style.display = 'block';
                return;
            }

            var btn = document.getElementById('start_trip_btn');
            btn.disabled = true;
            btn.textContent = 'Starting...';

            fetch(START_TRIP_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ token: TOKEN, odometer: odo }).toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res.success) {
                    errBox.textContent = res.message || 'Could not start the trip. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Start Trip';
                    return;
                }

                document.getElementById('trip_status_text').textContent = 'In transit';
                document.getElementById('start_trip_section').style.display = 'none';
                document.getElementById('sharing_section').style.display = 'block';
                var deliverySection = document.getElementById('delivery_actions_section');
                if (deliverySection) { deliverySection.style.display = 'block'; }

                // Starting the trip means the driver is already moving — begin
                // sharing location immediately rather than making them tap twice.
                startSharing();
            }).catch(function () {
                errBox.textContent = 'Network error — please check your connection and try again.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Start Trip';
            });
        }

        // ── Step 3: Deliver / Cancel ─────────────────────────────────────────────
        function hideForms() {
            document.getElementById('deliver_form').style.display = 'none';
            document.getElementById('cancel_form').style.display = 'none';
            document.getElementById('deliver_error').style.display = 'none';
            document.getElementById('cancel_error').style.display = 'none';
        }

        function showDeliverForm() {
            hideForms();
            document.getElementById('deliver_form').style.display = 'block';
            if (!sigPad) {
                sigPad = new SignaturePad(document.getElementById('signature_pad_canvas'));
            } else {
                sigPad.clear();
            }
        }

        function showCancelForm() {
            hideForms();
            document.getElementById('cancel_form').style.display = 'block';
        }

        function clearSignature() {
            if (sigPad) { sigPad.clear(); }
        }

        // Delivery/cancellation both end the trip — stop GPS sharing, hide the
        // action buttons, and show a final confirmation banner.
        function tripFinished(message) {
            stopSharing();
            document.getElementById('delivery_actions_section').style.display = 'none';
            hideForms();
            document.getElementById('sharing_section').style.display = 'none';
            var banner = document.getElementById('trip_complete_banner');
            banner.textContent = message;
            banner.style.display = 'block';
        }

        function submitDelivery() {
            var errBox = document.getElementById('deliver_error');
            errBox.style.display = 'none';

            var firstName = document.getElementById('deliver_first_name').value.trim();
            var lastName = document.getElementById('deliver_last_name').value.trim();
            if (!firstName || !lastName) {
                errBox.textContent = "Please enter the customer's first and last name.";
                errBox.style.display = 'block';
                return;
            }
            if (!sigPad || sigPad.isEmpty()) {
                errBox.textContent = 'Please have the customer sign before confirming.';
                errBox.style.display = 'block';
                return;
            }

            var btn = document.getElementById('confirm_deliver_btn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            fetch(DELIVER_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    token: TOKEN,
                    first_name: firstName,
                    last_name: lastName,
                    signature: sigPad.toDataURL('image/png')
                }).toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res.success) {
                    errBox.textContent = res.message || 'Could not save the delivery. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Confirm Delivery';
                    return;
                }
                tripFinished('✅ Delivered — thank you!');
            }).catch(function () {
                errBox.textContent = 'Network error — please check your connection and try again.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Confirm Delivery';
            });
        }

        function submitCancel() {
            var errBox = document.getElementById('cancel_error');
            errBox.style.display = 'none';

            var reason = document.getElementById('cancel_reason').value.trim();
            if (!reason) {
                errBox.textContent = 'Please enter a reason for cancelling.';
                errBox.style.display = 'block';
                return;
            }

            var btn = document.getElementById('confirm_cancel_btn');
            btn.disabled = true;
            btn.textContent = 'Cancelling...';

            fetch(CANCEL_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ token: TOKEN, reason: reason }).toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res.success) {
                    errBox.textContent = res.message || 'Could not cancel the shipment. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Confirm Cancellation';
                    return;
                }
                tripFinished('✖ Shipment cancelled.');
            }).catch(function () {
                errBox.textContent = 'Network error — please check your connection and try again.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Confirm Cancellation';
            });
        }

        // ── Service worker (installability + offline app-shell caching) ────────
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(SW_URL, { scope: '<?php echo site_url("admin/fleet/trips/driver_gps/"); ?>' }).catch(function () {});
        }

        // ── Install prompt (Android/Chrome) ─────────────────────────────────────
        var deferredInstallPrompt = null;
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredInstallPrompt = e;
            document.getElementById('install_btn').style.display = 'inline-block';
        });
        function installApp() {
            if (!deferredInstallPrompt) { return; }
            deferredInstallPrompt.prompt();
            deferredInstallPrompt.userChoice.finally(function () {
                deferredInstallPrompt = null;
                document.getElementById('install_btn').style.display = 'none';
            });
        }
        // iOS Safari has no install prompt API — show manual instructions instead,
        // unless already running installed (standalone) mode.
        var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isIOS && !isStandalone) {
            document.getElementById('ios_hint').style.display = 'block';
        }

        // ── Wake Lock: keeps the screen on while actively tracking, since a
        // locked/sleeping screen pauses all JS (this is the practical ceiling
        // for "background" tracking in a standards-compliant web app). ────────
        async function acquireWakeLock() {
            if (!('wakeLock' in navigator)) { return; }
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                wakeLock.addEventListener('release', function () { wakeLock = null; });
            } catch (e) { /* permission or platform doesn't support it — ignore */ }
        }
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible' && watchId !== null && !wakeLock) {
                acquireWakeLock();
            }
        });

        function setStatus(live, text) {
            document.getElementById('status_dot').className = 'status-dot' + (live ? ' live' : '');
            document.getElementById('status_text').textContent = text;
        }

        function showError(msg) {
            var box = document.getElementById('error_box');
            box.textContent = msg;
            box.style.display = 'block';
        }

        function getQueue() {
            try { return JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]'); } catch (e) { return []; }
        }
        function saveQueue(q) {
            try { localStorage.setItem(QUEUE_KEY, JSON.stringify(q)); } catch (e) {}
        }
        function queuePing(payload) {
            var q = getQueue();
            q.push(payload);
            if (q.length > 200) { q = q.slice(-200); } // cap so storage can't grow unbounded if offline for a long time
            saveQueue(q);
            document.getElementById('queued_note').style.display = 'block';
        }

        function postPing(payload) {
            return fetch(RECORD_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(payload).toString()
            }).then(function (r) { return r.json(); });
        }

        function flushQueue() {
            var q = getQueue();
            if (!q.length) { return; }
            saveQueue([]);
            document.getElementById('queued_note').style.display = 'none';

            var chain = Promise.resolve();
            q.forEach(function (payload) {
                chain = chain.then(function () { return postPing(payload).catch(function () { queuePing(payload); }); });
            });
        }

        window.addEventListener('online', flushQueue);

        function sendPosition(pos) {
            var now = Date.now();
            if (now - lastSentAt < MIN_INTERVAL_MS) {
                return;
            }
            lastSentAt = now;

            var payload = {
                token: TOKEN,
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                accuracy: pos.coords.accuracy || '',
                speed: pos.coords.speed || ''
            };

            postPing(payload).then(function (res) {
                if (res.success) {
                    document.getElementById('last_sent').textContent =
                        'Last sent: ' + new Date().toLocaleTimeString();
                    flushQueue();
                } else {
                    queuePing(payload);
                }
            }).catch(function () {
                queuePing(payload);
            });
        }

        function startSharing() {
            if (!navigator.geolocation) {
                showError('This browser does not support location sharing. Try Chrome or Safari.');
                return;
            }

            watchId = navigator.geolocation.watchPosition(
                function (pos) {
                    setStatus(true, 'Sharing location');
                    sendPosition(pos);
                },
                function (err) {
                    setStatus(false, 'Location error');
                    showError('Could not get your location: ' + err.message + '. Please allow location access and try again.');
                },
                { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
            );

            acquireWakeLock();
            flushQueue();

            document.getElementById('start_btn').style.display = 'none';
            document.getElementById('stop_btn').style.display = 'inline-block';
            setStatus(true, 'Starting...');
        }

        function stopSharing() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (wakeLock) {
                wakeLock.release().catch(function () {});
                wakeLock = null;
            }
            document.getElementById('start_btn').style.display = 'inline-block';
            document.getElementById('stop_btn').style.display = 'none';
            setStatus(false, 'Not sharing location');
        }
    </script>
</body>
</html>
