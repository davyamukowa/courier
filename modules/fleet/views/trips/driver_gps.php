<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Live Trip Tracking</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0; min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1b2a; color: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 24px;
        }
        h1 { font-size: 18px; margin: 0 0 4px; color: #8ecae6; }
        .trip-info { color: #cbd5e1; font-size: 14px; margin-bottom: 28px; line-height: 1.6; }
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
        #status_text { font-size: 15px; margin-bottom: 24px; }
        button {
            font-size: 18px; font-weight: 700; padding: 18px 36px; border-radius: 12px;
            border: none; cursor: pointer; min-width: 240px;
        }
        #start_btn { background: #22c55e; color: #06281a; }
        #stop_btn { background: #ef4444; color: #2b0a0a; display: none; }
        #last_sent { color: #94a3b8; font-size: 12px; margin-top: 18px; }
        #error_box {
            display: none; margin-top: 20px; background: #7f1d1d; color: #fecaca;
            padding: 12px 16px; border-radius: 8px; font-size: 13px; max-width: 320px;
        }
    </style>
</head>
<body>
    <h1>Trip #<?php echo (int) $trip->id; ?></h1>
    <div class="trip-info">
        Vehicle status: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $trip->status ?? 'booked'))); ?><br>
        Keep this page open while you drive — your location is shared with dispatch automatically.
    </div>

    <div>
        <span id="status_dot" class="status-dot"></span>
        <span id="status_text">Not sharing location</span>
    </div>

    <button id="start_btn" onclick="startSharing()">Start Sharing Location</button>
    <button id="stop_btn" onclick="stopSharing()">Stop Sharing</button>

    <div id="last_sent"></div>
    <div id="error_box"></div>

    <script>
        var TOKEN = <?php echo json_encode($token); ?>;
        var RECORD_URL = <?php echo json_encode(site_url('admin/fleet/trips/record_location')); ?>;
        var watchId = null;
        var lastSentAt = 0;
        var MIN_INTERVAL_MS = 10000; // don't post more often than every 10s, even if GPS updates faster

        function setStatus(live, text) {
            document.getElementById('status_dot').className = 'status-dot' + (live ? ' live' : '');
            document.getElementById('status_text').textContent = text;
        }

        function showError(msg) {
            var box = document.getElementById('error_box');
            box.textContent = msg;
            box.style.display = 'block';
        }

        function sendPosition(pos) {
            var now = Date.now();
            if (now - lastSentAt < MIN_INTERVAL_MS) {
                return;
            }
            lastSentAt = now;

            var body = new URLSearchParams({
                token: TOKEN,
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                accuracy: pos.coords.accuracy || '',
                speed: pos.coords.speed || ''
            });

            fetch(RECORD_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res.success) {
                    document.getElementById('last_sent').textContent =
                        'Last sent: ' + new Date().toLocaleTimeString();
                }
            }).catch(function () {
                document.getElementById('last_sent').textContent = 'Last send failed — will retry on next update.';
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

            document.getElementById('start_btn').style.display = 'none';
            document.getElementById('stop_btn').style.display = 'inline-block';
            setStatus(true, 'Starting...');
        }

        function stopSharing() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            document.getElementById('start_btn').style.display = 'inline-block';
            document.getElementById('stop_btn').style.display = 'none';
            setStatus(false, 'Not sharing location');
        }

        // Keep sharing even if the phone screen dims, as long as the tab stays open.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible' && watchId !== null) {
                setStatus(true, 'Sharing location');
            }
        });
    </script>
</body>
</html>
