<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Go Shipping Rider</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>
    <link rel="manifest" href="<?php echo site_url('courier_goshipping/rider/manifest'); ?>">
    <meta name="theme-color" content="#0d47a1">
    <link rel="apple-touch-icon" href="<?php echo site_url('courier_goshipping/rider/icon/192'); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GS Rider">
    <meta name="mobile-web-app-capable" content="yes">

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1b2a; color: #fff;
            padding-top: env(safe-area-inset-top);
            padding-bottom: calc(64px + env(safe-area-inset-bottom));
            min-height: 100%;
        }
        h1, h2, h3 { margin: 0; }
        a { color: inherit; }
        .screen { display: none; padding: 20px; max-width: 480px; margin: 0 auto; }
        .screen.active { display: block; }
        .brand { text-align: center; margin: 30px 0 24px; }
        .brand-badge {
            width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 10px;
            background: linear-gradient(135deg,#1565c0,#0d47a1); display: flex; align-items: center; justify-content: center;
            font-size: 26px; font-weight: 800; border-bottom: 5px solid #c62828;
        }
        .brand h1 { font-size: 19px; color: #8ecae6; }
        .brand p { color: #94a3b8; font-size: 12.5px; margin-top: 4px; }

        label { display: block; font-size: 13px; color: #cbd5e1; margin: 14px 0 5px; }
        input[type="text"], input[type="tel"], input[type="password"], textarea {
            width: 100%; padding: 13px 14px; border-radius: 9px; border: none; font-size: 15px;
            font-family: inherit; background: #16283f; color: #fff;
        }
        input::placeholder, textarea::placeholder { color: #64748b; }

        button {
            font-size: 15px; font-weight: 700; padding: 14px 20px; border-radius: 10px;
            border: none; cursor: pointer; width: 100%; margin-top: 8px;
        }
        .btn-primary { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; }
        .btn-success { background: #22c55e; color: #06281a; }
        .btn-danger  { background: #ef4444; color: #2b0a0a; }
        .btn-ghost   { background: #16283f; color: #cbd5e1; font-weight: 600; }
        .link-btn { background: none; color: #8ecae6; font-weight: 600; text-decoration: underline; padding: 10px; }

        .error-box, .info-box {
            display: none; margin-top: 12px; padding: 11px 14px; border-radius: 8px; font-size: 13px;
        }
        .error-box { background: #7f1d1d; color: #fecaca; }
        .info-box { background: #052e1a; color: #86efac; }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; max-width: 480px; margin: 0 auto;
        }
        .topbar h2 { font-size: 17px; color: #8ecae6; }
        .topbar .hi { font-size: 12.5px; color: #94a3b8; }

        .card {
            background: #132339; border-left: 4px solid #1565c0; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 12px; font-size: 13.5px; line-height: 1.7; text-align: left;
        }
        .card.is-salibay { border-left-color: #c62828; }
        .card .row1 { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .card .waybill { font-weight: 800; color: #fff; }
        .card .badge {
            font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px;
            background: #1e293b; color: #8ecae6; text-transform: uppercase; letter-spacing: .04em;
        }
        .card .muted { color: #94a3b8; }
        .card .actions { display: flex; gap: 8px; margin-top: 10px; }
        .card .actions button { margin-top: 0; padding: 10px; font-size: 13px; }

        .empty-state { text-align: center; color: #64748b; padding: 40px 20px; font-size: 14px; }

        .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 18px 0 22px; }
        .kpi { background: #132339; border-radius: 10px; padding: 16px; text-align: center; }
        .kpi .n { font-size: 26px; font-weight: 800; color: #fff; }
        .kpi .l { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; }
        .kpi.kpi-sm .n { font-size: 20px; }

        .section-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; margin: 4px 0 8px; }

        .segmented { display: flex; background: #16283f; border-radius: 10px; padding: 3px; margin: 0 0 14px; }
        .segmented button { flex: 1; margin: 0; padding: 9px 6px; border-radius: 8px; font-size: 12.5px; font-weight: 700; background: none; color: #94a3b8; }
        .segmented button.active { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; }

        .search-box { position: relative; margin-bottom: 12px; }
        .search-box input { padding-left: 34px; }
        .search-box .ic { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #64748b; }

        .icon-btn {
            width: auto; display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            padding: 9px 10px; font-size: 12px; background: #16283f; color: #cbd5e1; margin: 0;
            text-decoration: none; border: none; border-radius: 10px; font-weight: 700; cursor: pointer;
        }
        .icon-btn.call { background: #14532d; color: #86efac; }
        .icon-btn.whatsapp { background: #0f3d24; color: #4ade80; }
        .quick-actions { display: flex; gap: 6px; margin-top: 8px; }

        .topbar-actions { display: flex; align-items: center; gap: 8px; }
        .refresh-btn {
            width: auto; margin: 0; padding: 8px 10px; font-size: 15px; background: #16283f; color: #8ecae6; border-radius: 8px;
        }

        .pod-thumb { width: 100%; max-width: 220px; border-radius: 8px; margin-top: 8px; display: block; }

        .toast-stack { position: fixed; left: 0; right: 0; bottom: calc(78px + env(safe-area-inset-bottom)); z-index: 60; display: flex; flex-direction: column; align-items: center; gap: 8px; pointer-events: none; }
        .toast { background: #16283f; color: #fff; padding: 11px 18px; border-radius: 30px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 14px rgba(0,0,0,.3); max-width: 90%; text-align: center; }
        .toast.success { background: #16532d; color: #bbf7d0; }
        .toast.error { background: #7f1d1d; color: #fecaca; }

        .offline-banner {
            display: none; position: fixed; left: 0; right: 0; top: 0; z-index: 45;
            background: #92400e; color: #fff; padding: 8px 16px; font-size: 12px; text-align: center; font-weight: 600;
        }
        .offline-banner.active { display: block; }

        .photo-preview { width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; margin-top: 8px; display: none; }

        #signature_pad_canvas { width: 100%; height: 150px; background: #fff; border-radius: 8px; touch-action: none; display: block; margin-top: 4px; }

        .modal-backdrop {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 50;
            align-items: flex-end; justify-content: center;
        }
        .modal-backdrop.active { display: flex; }
        .modal-sheet {
            background: #0d1b2a; border-radius: 16px 16px 0 0; padding: 20px 20px calc(20px + env(safe-area-inset-bottom));
            width: 100%; max-width: 480px; max-height: 85vh; overflow-y: auto;
        }
        .modal-sheet h3 { font-size: 16px; color: #8ecae6; margin-bottom: 4px; }

        #install_banner {
            display: none; position: fixed; left: 0; right: 0; top: 0; z-index: 40;
            background: #1565c0; color: #fff; padding: 10px 16px; font-size: 12.5px;
            display: none; align-items: center; justify-content: space-between; gap: 10px;
        }
        #install_banner button { width: auto; margin: 0; padding: 8px 14px; font-size: 12px; background: #fff; color: #0d47a1; }

        .bottom-nav {
            display: none; position: fixed; left: 0; right: 0; bottom: 0; z-index: 30;
            background: #ffffff; border-top: 1px solid #e2e8f0;
            box-shadow: 0 -4px 14px rgba(0,0,0,.15);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .bottom-nav.active { display: flex; }
        .bottom-nav button {
            flex: 1; background: none; margin: 0; padding: 12px 4px 10px; border-radius: 0;
            color: #64748b; font-size: 11.5px; font-weight: 700; display: flex; flex-direction: column; align-items: center; gap: 4px;
        }
        .bottom-nav button.active {
            color: #0d47a1; position: relative;
        }
        .bottom-nav button.active::before {
            content: ''; position: absolute; top: 0; left: 20%; right: 20%; height: 3px;
            background: linear-gradient(90deg,#1565c0,#c62828); border-radius: 0 0 4px 4px;
        }
        .bottom-nav .ic { font-size: 21px; }
    </style>
</head>
<body>

    <div id="install_banner">
        <span>📲 Install this app for faster access to your deliveries.</span>
        <button onclick="installApp()">Install</button>
    </div>
    <div id="offline_banner" class="offline-banner">📡 You're offline — actions will sync automatically once you're back online.</div>
    <div id="toast_stack" class="toast-stack"></div>

    <!-- ── Login ────────────────────────────────────────────────────────────── -->
    <div class="screen active" id="screen_login">
        <div class="brand">
            <div class="brand-badge">GS</div>
            <h1>Go Shipping Rider</h1>
            <p>Manage your deliveries, pickups, and status updates on the go.</p>
        </div>
        <label for="login_phone">Phone number</label>
        <input type="tel" id="login_phone" placeholder="e.g. 0723434533">
        <label for="login_password">Password</label>
        <input type="password" id="login_password" placeholder="••••••••">
        <button class="btn-primary" onclick="doLogin()" id="login_btn">Log In</button>
        <button class="link-btn" onclick="showScreen('register')">Don't have an account? Create one</button>
        <div class="error-box" id="login_error"></div>
    </div>

    <!-- ── Register ─────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_register">
        <div class="brand">
            <div class="brand-badge">GS</div>
            <h1>Create Rider Account</h1>
            <p>Use the same phone number your dispatcher has on file so your deliveries show up automatically.</p>
        </div>
        <label for="reg_name">Full name</label>
        <input type="text" id="reg_name" placeholder="e.g. John Otieno">
        <label for="reg_phone">Phone number</label>
        <input type="tel" id="reg_phone" placeholder="e.g. 0723434533">
        <label for="reg_password">Create a password</label>
        <input type="password" id="reg_password" placeholder="At least 4 characters">
        <button class="btn-primary" onclick="doRegister()" id="register_btn">Create Account</button>
        <button class="link-btn" onclick="showScreen('login')">Already have an account? Log in</button>
        <div class="error-box" id="register_error"></div>
    </div>

    <!-- ── Dashboard ────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_dashboard">
        <div class="topbar">
            <div>
                <h2>Dashboard</h2>
                <div class="hi" id="dash_hi"></div>
            </div>
            <div class="topbar-actions"><button class="refresh-btn" onclick="loadDashboard()">⟳</button></div>
        </div>
        <div style="padding:0 20px;">
            <div id="unlinked_notice" class="info-box" style="background:#3b2f0a; color:#fde68a; display:none;">
                Your account isn't linked to a driver profile yet — ask your dispatcher to confirm your phone number matches your driver record, then log out and back in.
            </div>
            <div class="kpi-grid">
                <div class="kpi"><div class="n" id="kpi_deliveries">0</div><div class="l">Active Deliveries</div></div>
                <div class="kpi"><div class="n" id="kpi_pickups">0</div><div class="l">Active Pickups</div></div>
            </div>
            <div class="section-label">Performance</div>
            <div class="kpi-grid">
                <div class="kpi kpi-sm"><div class="n" id="kpi_today">0</div><div class="l">Delivered Today</div></div>
                <div class="kpi kpi-sm"><div class="n" id="kpi_week">0</div><div class="l">Delivered This Week</div></div>
                <div class="kpi kpi-sm"><div class="n" id="kpi_total">0</div><div class="l">Total Delivered</div></div>
                <div class="kpi kpi-sm"><div class="n" id="kpi_cancelled">0</div><div class="l">Cancelled</div></div>
            </div>
            <div id="dash_trips"></div>
        </div>
    </div>

    <!-- ── Deliveries ───────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_deliveries">
        <div class="topbar">
            <h2>My Deliveries</h2>
            <div class="topbar-actions"><button class="refresh-btn" onclick="refreshDeliveriesTab()">⟳</button></div>
        </div>
        <div style="padding:0 20px;">
            <div class="segmented">
                <button id="deliveries_tab_active" class="active" onclick="switchDeliveriesTab('active')">Active</button>
                <button id="deliveries_tab_history" onclick="switchDeliveriesTab('history')">History</button>
            </div>
            <div class="search-box">
                <span class="ic">🔎</span>
                <input type="text" id="deliveries_search" placeholder="Search by waybill or recipient..." oninput="renderDeliveriesList()">
            </div>
            <div id="deliveries_list"></div>
        </div>
    </div>

    <!-- ── Pickups ──────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_pickups">
        <div class="topbar">
            <h2>My Pickups</h2>
            <div class="topbar-actions"><button class="refresh-btn" onclick="loadPickups()">⟳</button></div>
        </div>
        <div style="padding:0 20px;" id="pickups_list"></div>
    </div>

    <!-- ── Profile ──────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_profile">
        <div class="topbar"><h2>Profile</h2></div>
        <div style="padding:0 20px;">
            <div class="card">
                <div><strong id="profile_name"></strong></div>
                <div class="muted" id="profile_phone"></div>
                <div class="muted" id="profile_link_status" style="margin-top:6px;"></div>
            </div>
            <button class="btn-ghost" onclick="installApp()" id="profile_install_btn" style="display:none;">📲 Install App to Home Screen</button>
            <button class="btn-danger" onclick="doLogout()">Log Out</button>
        </div>
    </div>

    <!-- ── Bottom nav ───────────────────────────────────────────────────────── -->
    <div class="bottom-nav" id="bottom_nav">
        <button onclick="navTo('dashboard')" data-nav="dashboard"><span class="ic">🏠</span>Dashboard</button>
        <button onclick="navTo('deliveries')" data-nav="deliveries"><span class="ic">📦</span>Deliveries</button>
        <button onclick="navTo('pickups')" data-nav="pickups"><span class="ic">🧾</span>Pickups</button>
        <button onclick="navTo('profile')" data-nav="profile"><span class="ic">👤</span>Profile</button>
    </div>

    <!-- ── Deliver modal ────────────────────────────────────────────────────── -->
    <div class="modal-backdrop" id="deliver_modal">
        <div class="modal-sheet">
            <h3>Confirm Delivery</h3>
            <label for="deliver_first_name">Customer first name</label>
            <input type="text" id="deliver_first_name">
            <label for="deliver_last_name">Customer last name</label>
            <input type="text" id="deliver_last_name">
            <label>Customer signature</label>
            <canvas id="signature_pad_canvas"></canvas>
            <button class="btn-ghost" onclick="clearSignature()">Clear Signature</button>
            <label>Proof-of-delivery photo (optional)</label>
            <input type="file" id="deliver_photo_input" accept="image/*" capture="environment" onchange="onDeliverPhotoSelected(event)">
            <img id="deliver_photo_preview" class="photo-preview">
            <button class="btn-success" onclick="submitDeliver()" id="confirm_deliver_btn">Confirm Delivery</button>
            <button class="btn-ghost" onclick="closeModal('deliver_modal')">Cancel</button>
            <div class="error-box" id="deliver_error"></div>
        </div>
    </div>

    <!-- ── Cancel delivery modal ────────────────────────────────────────────── -->
    <div class="modal-backdrop" id="cancel_modal">
        <div class="modal-sheet">
            <h3>Cancel Delivery</h3>
            <label for="cancel_reason">Reason for cancelling</label>
            <textarea id="cancel_reason" rows="4"></textarea>
            <button class="btn-danger" onclick="submitCancel()" id="confirm_cancel_btn">Confirm Cancellation</button>
            <button class="btn-ghost" onclick="closeModal('cancel_modal')">Back</button>
            <div class="error-box" id="cancel_error"></div>
        </div>
    </div>

    <!-- ── Live map modal (rider's own view of their shared location + drop-off) -->
    <div class="modal-backdrop" id="map_modal">
        <div class="modal-sheet">
            <h3>Delivery Map</h3>
            <div id="rider_map" style="width:100%;height:280px;border-radius:8px;background:#eee;margin-top:10px;"></div>
            <button class="btn-ghost" onclick="closeModal('map_modal')" style="margin-top:14px;">Close</button>
        </div>
    </div>

    <!-- ── Pickup signature modal ───────────────────────────────────────────── -->
    <div class="modal-backdrop" id="pickup_modal">
        <div class="modal-sheet">
            <h3 id="pickup_modal_title">Confirm Pickup</h3>
            <label>Signature</label>
            <canvas id="pickup_signature_canvas"></canvas>
            <button class="btn-ghost" onclick="clearPickupSignature()">Clear Signature</button>
            <button class="btn-success" onclick="submitPickup()" id="confirm_pickup_btn">Confirm</button>
            <button class="btn-ghost" onclick="closeModal('pickup_modal')">Cancel</button>
            <div class="error-box" id="pickup_error"></div>
        </div>
    </div>

<script>
    var API = {
        register: <?php echo json_encode(site_url('courier_goshipping/rider-api/register')); ?>,
        login: <?php echo json_encode(site_url('courier_goshipping/rider-api/login')); ?>,
        logout: <?php echo json_encode(site_url('courier_goshipping/rider-api/logout')); ?>,
        me: <?php echo json_encode(site_url('courier_goshipping/rider-api/me')); ?>,
        deliveries: <?php echo json_encode(site_url('courier_goshipping/rider-api/deliveries')); ?>,
        deliveriesHistory: <?php echo json_encode(site_url('courier_goshipping/rider-api/deliveries/history')); ?>,
        deliveryStart: <?php echo json_encode(site_url('courier_goshipping/rider-api/deliveries/')); ?>,
        pickups: <?php echo json_encode(site_url('courier_goshipping/rider-api/pickups')); ?>,
        pickupUpdate: <?php echo json_encode(site_url('courier_goshipping/rider-api/pickups/')); ?>,
        stats: <?php echo json_encode(site_url('courier_goshipping/rider-api/stats')); ?>
    };
    var SW_URL = <?php echo json_encode(site_url('courier_goshipping/rider/sw')); ?>;

    var TOKEN_KEY = 'gs_rider_token';
    var token = localStorage.getItem(TOKEN_KEY);
    var currentRider = null;
    var sigPad = null;
    var pickupSigPad = null;
    var activeDeliveryId = null;
    var activePickupId = null;
    var activePickupStatus = null;

    function post(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(payload).toString()
        }).then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); });
    }
    function get(url, payload) {
        var qs = new URLSearchParams(payload).toString();
        return fetch(url + '?' + qs).then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); });
    }

    // ── Toasts ───────────────────────────────────────────────────────────────
    function toast(message, type) {
        var stack = document.getElementById('toast_stack');
        var el = document.createElement('div');
        el.className = 'toast' + (type ? ' ' + type : '');
        el.textContent = message;
        stack.appendChild(el);
        setTimeout(function () { el.remove(); }, 3200);
    }

    // ── Offline action queue — a rider's connection routinely drops mid-route,
    // so an action that fails purely due to network is queued and auto-retried
    // instead of silently lost or blocking the rider from moving on ──────────
    var OFFLINE_QUEUE_KEY = 'gs_rider_offline_queue';
    function getOfflineQueue() {
        try { return JSON.parse(localStorage.getItem(OFFLINE_QUEUE_KEY)) || []; } catch (e) { return []; }
    }
    function saveOfflineQueue(queue) {
        localStorage.setItem(OFFLINE_QUEUE_KEY, JSON.stringify(queue));
    }
    function queueOfflineAction(url, payload, label) {
        var queue = getOfflineQueue();
        queue.push({ url: url, payload: payload, label: label, queued_at: Date.now() });
        saveOfflineQueue(queue);
        toast('You are offline — "' + label + '" will be sent once your connection returns.', 'error');
    }
    function flushOfflineQueue() {
        var queue = getOfflineQueue();
        if (!queue.length) { return; }
        saveOfflineQueue([]);
        var remaining = [];
        var sent = 0;
        queue.reduce(function (chain, item) {
            return chain.then(function () {
                return post(item.url, item.payload).then(function (res) {
                    if (res.data && res.data.success) { sent++; } else { remaining.push(item); }
                }).catch(function () { remaining.push(item); });
            });
        }, Promise.resolve()).then(function () {
            if (remaining.length) { saveOfflineQueue(remaining); }
            if (sent) {
                toast(sent + ' queued action' + (sent > 1 ? 's' : '') + ' synced.', 'success');
                loadDeliveries(); loadPickups();
            }
        });
    }
    function updateOfflineBanner() {
        document.getElementById('offline_banner').classList.toggle('active', !navigator.onLine);
    }
    window.addEventListener('online', function () { updateOfflineBanner(); flushOfflineQueue(); });
    window.addEventListener('offline', updateOfflineBanner);
    updateOfflineBanner();

    function showScreen(name) {
        document.querySelectorAll('.screen').forEach(function (el) { el.classList.remove('active'); });
        document.getElementById('screen_' + name).classList.add('active');
    }

    function navTo(name) {
        showScreen(name);
        document.querySelectorAll('.bottom-nav button').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-nav') === name);
        });
        if (name === 'dashboard') { loadDashboard(); }
        if (name === 'deliveries') { refreshDeliveriesTab(); }
        if (name === 'pickups') { loadPickups(); }
    }

    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    // ── Auth ─────────────────────────────────────────────────────────────────
    function doLogin() {
        var errBox = document.getElementById('login_error');
        errBox.style.display = 'none';
        var phone = document.getElementById('login_phone').value.trim();
        var password = document.getElementById('login_password').value;
        if (!phone || !password) {
            errBox.textContent = 'Please enter your phone number and password.';
            errBox.style.display = 'block';
            return;
        }
        var btn = document.getElementById('login_btn');
        btn.disabled = true; btn.textContent = 'Logging in...';
        post(API.login, { phone: phone, password: password }).then(function (res) {
            btn.disabled = false; btn.textContent = 'Log In';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not log in.';
                errBox.style.display = 'block';
                return;
            }
            token = res.data.token;
            localStorage.setItem(TOKEN_KEY, token);
            currentRider = res.data.rider;
            enterApp();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Log In';
            errBox.textContent = 'Network error — please check your connection and try again.';
            errBox.style.display = 'block';
        });
    }

    function doRegister() {
        var errBox = document.getElementById('register_error');
        errBox.style.display = 'none';
        var name = document.getElementById('reg_name').value.trim();
        var phone = document.getElementById('reg_phone').value.trim();
        var password = document.getElementById('reg_password').value;
        if (!name || !phone || password.length < 4) {
            errBox.textContent = 'Please fill in your name, phone number, and a password of at least 4 characters.';
            errBox.style.display = 'block';
            return;
        }
        var btn = document.getElementById('register_btn');
        btn.disabled = true; btn.textContent = 'Creating account...';
        post(API.register, { name: name, phone: phone, password: password }).then(function (res) {
            btn.disabled = false; btn.textContent = 'Create Account';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not create your account.';
                errBox.style.display = 'block';
                return;
            }
            token = res.data.token;
            localStorage.setItem(TOKEN_KEY, token);
            currentRider = res.data.rider;
            enterApp();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Create Account';
            errBox.textContent = 'Network error — please check your connection and try again.';
            errBox.style.display = 'block';
        });
    }

    function doLogout() {
        post(API.logout, { token: token }).catch(function () {});
        localStorage.removeItem(TOKEN_KEY);
        token = null;
        currentRider = null;
        document.getElementById('bottom_nav').classList.remove('active');
        showScreen('login');
    }

    function enterApp() {
        document.getElementById('bottom_nav').classList.add('active');
        document.getElementById('profile_name').textContent = currentRider.name;
        document.getElementById('profile_phone').textContent = currentRider.phone;
        document.getElementById('profile_link_status').textContent = currentRider.linked
            ? '✅ Linked to your driver profile'
            : '⚠️ Not linked yet — contact your dispatcher';
        navTo('dashboard');
    }

    // ── Dashboard ────────────────────────────────────────────────────────────
    function loadDashboard() {
        document.getElementById('dash_hi').textContent = 'Welcome back, ' + currentRider.name;
        get(API.deliveries, { token: token }).then(function (res) {
            if (!res.data.success) { return; }
            document.getElementById('unlinked_notice').style.display = res.data.linked ? 'none' : 'block';
            document.getElementById('kpi_deliveries').textContent = res.data.deliveries.length;

            var tripsBox = document.getElementById('dash_trips');
            tripsBox.innerHTML = '';
            (res.data.trips || []).forEach(function (trip) {
                var card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = '<div class="row1"><span class="waybill">Courier Trip #' + trip.id + '</span><span class="badge">' + trip.status + '</span></div>' +
                    '<div class="muted">Full trip tracking (GPS, odometer) happens on the dedicated trip page.</div>' +
                    (trip.url ? '<div class="actions"><button class="btn-primary" onclick="window.location.href=\'' + trip.url + '\'">Open Trip</button></div>' : '');
                tripsBox.appendChild(card);
            });
        });
        get(API.pickups, { token: token }).then(function (res) {
            if (res.data.success) {
                document.getElementById('kpi_pickups').textContent = res.data.pickups.length;
            }
        });
        get(API.stats, { token: token }).then(function (res) {
            if (!res.data.success || !res.data.linked) { return; }
            document.getElementById('kpi_today').textContent = res.data.completed_today;
            document.getElementById('kpi_week').textContent = res.data.completed_week;
            document.getElementById('kpi_total').textContent = res.data.completed_total;
            document.getElementById('kpi_cancelled').textContent = res.data.cancelled_total;
        });
    }

    // ── Quick-action helpers (call / WhatsApp / navigate) ───────────────────
    function callHref(phone) { return 'tel:' + String(phone || '').replace(/[^0-9+]/g, ''); }
    function whatsappHref(phone) { return 'https://wa.me/' + String(phone || '').replace(/[^0-9]/g, ''); }
    function navigateHref(address) { return 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address || ''); }
    function quickActionsHtml(phone, address) {
        var html = '<div class="quick-actions">';
        if (phone) {
            html += '<a class="icon-btn call" href="' + callHref(phone) + '">📞 Call</a>';
            html += '<a class="icon-btn whatsapp" href="' + whatsappHref(phone) + '" target="_blank" rel="noopener">💬 WhatsApp</a>';
        }
        if (address) {
            html += '<a class="icon-btn" href="' + navigateHref(address) + '" target="_blank" rel="noopener">🧭 Navigate</a>';
        }
        html += '</div>';
        return html;
    }

    // ── Deliveries ───────────────────────────────────────────────────────────
    var deliveriesTab = 'active';
    var activeDeliveriesCache = [];
    var historyDeliveriesCache = [];

    function switchDeliveriesTab(tab) {
        deliveriesTab = tab;
        document.getElementById('deliveries_tab_active').classList.toggle('active', tab === 'active');
        document.getElementById('deliveries_tab_history').classList.toggle('active', tab === 'history');
        document.getElementById('deliveries_search').placeholder = tab === 'active'
            ? 'Search by waybill or recipient...' : 'Search delivery history...';
        if (tab === 'active') { loadDeliveries(); } else { loadDeliveriesHistory(); }
    }

    function refreshDeliveriesTab() {
        if (deliveriesTab === 'active') { loadDeliveries(); } else { loadDeliveriesHistory(); }
    }

    function loadDeliveries() {
        var box = document.getElementById('deliveries_list');
        box.innerHTML = '<div class="empty-state">Loading...</div>';
        get(API.deliveries, { token: token }).then(function (res) {
            if (!res.data.success) { box.innerHTML = '<div class="empty-state">Could not load deliveries.</div>'; return; }
            activeDeliveriesCache = res.data.deliveries;
            renderDeliveriesList();
        }).catch(function () {
            box.innerHTML = '<div class="empty-state">Could not load deliveries — check your connection.</div>';
        });
    }

    function loadDeliveriesHistory() {
        var box = document.getElementById('deliveries_list');
        box.innerHTML = '<div class="empty-state">Loading...</div>';
        get(API.deliveriesHistory, { token: token }).then(function (res) {
            if (!res.data.success) { box.innerHTML = '<div class="empty-state">Could not load history.</div>'; return; }
            historyDeliveriesCache = res.data.deliveries;
            renderDeliveriesList();
        }).catch(function () {
            box.innerHTML = '<div class="empty-state">Could not load history — check your connection.</div>';
        });
    }

    function matchesSearch(d, term) {
        if (!term) { return true; }
        term = term.toLowerCase();
        return (d.waybill_number || '').toLowerCase().indexOf(term) !== -1 ||
            (d.recipient_name || '').toLowerCase().indexOf(term) !== -1;
    }

    function renderDeliveriesList() {
        var box = document.getElementById('deliveries_list');
        var term = (document.getElementById('deliveries_search').value || '').trim();

        if (deliveriesTab === 'history') {
            var history = historyDeliveriesCache.filter(function (d) { return matchesSearch(d, term); });
            if (!history.length) { box.innerHTML = '<div class="empty-state">' + (term ? 'No matching deliveries.' : 'No completed deliveries yet.') + '</div>'; return; }
            box.innerHTML = '';
            history.forEach(function (d) {
                var card = document.createElement('div');
                card.className = 'card' + (d.is_salibay ? ' is-salibay' : '');
                var isCancelled = d.status_id === 9;
                card.innerHTML =
                    '<div class="row1"><span class="waybill">' + d.waybill_number + '</span><span class="badge">' + (d.status_text || '') + '</span></div>' +
                    '<div>' + (d.recipient_name || '') + (d.recipient_phone ? ' · ' + d.recipient_phone : '') + '</div>' +
                    '<div class="muted">' + (d.recipient_address || '') + '</div>' +
                    (d.completed_at ? '<div class="muted" style="margin-top:4px;">' + (isCancelled ? 'Cancelled' : 'Delivered') + ' ' + formatDateTime(d.completed_at) + '</div>' : '') +
                    (isCancelled && d.cancel_reason ? '<div class="muted" style="margin-top:4px;color:#fca5a5;">Reason: ' + d.cancel_reason + '</div>' : '') +
                    (d.signature_url ? '<img class="pod-thumb" src="' + d.signature_url + '" alt="Signature" style="background:#fff;">' : '') +
                    (d.photo_url ? '<img class="pod-thumb" src="' + d.photo_url + '" alt="Delivery photo">' : '');
                box.appendChild(card);
            });
            return;
        }

        var active = activeDeliveriesCache.filter(function (d) { return matchesSearch(d, term); });
        if (!active.length) { box.innerHTML = '<div class="empty-state">' + (term ? 'No matching deliveries.' : 'No active deliveries right now.') + '</div>'; return; }

        box.innerHTML = '';
        active.forEach(function (d) {
            var card = document.createElement('div');
            card.className = 'card' + (d.is_salibay ? ' is-salibay' : '');
            var started = d.status_id >= 5;
            if (started) { beginLocationSharing(d.id); }
            var destAddress = d.recipient_address || '';
            card.innerHTML =
                '<div class="row1"><span class="waybill">' + d.waybill_number + '</span><span class="badge">' + (d.status_text || '') + '</span></div>' +
                '<div class="muted">' + (d.items_summary || '-') + '</div>' +
                '<div style="margin-top:6px;">' + (d.recipient_name || '') + (d.recipient_phone ? ' · ' + d.recipient_phone : '') + '</div>' +
                '<div class="muted">' + destAddress + '</div>' +
                (started ? '<div style="margin-top:6px;font-size:11.5px;color:#22c55e;">📍 Sharing your location</div>' : '') +
                quickActionsHtml(d.recipient_phone, destAddress) +
                '<div class="actions">' +
                (started
                    ? '<button class="btn-primary" onclick="openMapModal(' + d.id + ', ' + JSON.stringify(destAddress) + ')">Map</button><button class="btn-success" onclick="openDeliverModal(' + d.id + ')">Delivered</button><button class="btn-danger" onclick="openCancelModal(' + d.id + ')">Cancel</button>'
                    : '<button class="btn-primary" onclick="startDelivery(' + d.id + ', this)">Start Delivery</button>') +
                '</div>';
            box.appendChild(card);
        });
    }

    function formatDateTime(value) {
        try {
            var d = new Date(value.replace(' ', 'T'));
            return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch (e) { return value; }
    }

    function startDelivery(id, btn) {
        btn.disabled = true; btn.textContent = 'Starting...';
        post(API.deliveryStart + id + '/start', { token: token }).then(function (res) {
            if (!res.data.success) {
                toast(res.data.message || 'Could not start the delivery.', 'error');
                btn.disabled = false; btn.textContent = 'Start Delivery';
                return;
            }
            beginLocationSharing(id);
            toast('Delivery started.', 'success');
            loadDeliveries();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Start Delivery';
            toast('Network error — please check your connection and try again.', 'error');
        });
    }

    // ── GPS sharing — starts once a delivery is under way, posts a
    // throttled ping so the admin waybill's live map has something to show.
    // Uses the device's own GPS chip (enableHighAccuracy), not a coarse
    // network/IP-based fix, and holds a Wake Lock so the OS doesn't dim the
    // screen and pause tracking while the installed PWA is open.
    var locationWatches = {};
    var wakeLock = null;

    async function acquireWakeLock() {
        if (!('wakeLock' in navigator)) { return; }
        try {
            wakeLock = await navigator.wakeLock.request('screen');
            wakeLock.addEventListener('release', function () { wakeLock = null; });
        } catch (e) { /* permission or platform doesn't support it — ignore */ }
    }
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && Object.keys(locationWatches).length && !wakeLock) {
            acquireWakeLock();
        }
    });

    function beginLocationSharing(id) {
        if (locationWatches[id] || !navigator.geolocation) { return; }

        var lastSentAt = 0;
        var MIN_INTERVAL_MS = 15000;

        var watchId = navigator.geolocation.watchPosition(function (pos) {
            var now = Date.now();
            if (now - lastSentAt < MIN_INTERVAL_MS) { return; }
            lastSentAt = now;

            post(API.deliveryStart + id + '/location', {
                token: token,
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                accuracy: pos.coords.accuracy || '',
                speed: pos.coords.speed || ''
            }).catch(function () {});
            acquireWakeLock();
        }, function () {
            // Permission denied or unavailable — no visible error, the rider
            // can still complete the delivery without live tracking.
        }, { enableHighAccuracy: true, maximumAge: 10000, timeout: 20000 });

        locationWatches[id] = watchId;
        acquireWakeLock();
    }

    // ── Rider's own map view: last known shared position + geocoded drop-off ──
    var riderMap = null, riderMarker = null, riderDestMarker = null;
    function openMapModal(id, destAddress) {
        openModal('map_modal');

        function ensureLeaflet(cb) {
            if (window.L) { cb(); return; }
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);
            var js = document.createElement('script');
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            js.onload = cb;
            document.head.appendChild(js);
        }

        ensureLeaflet(function () {
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude, lng = pos.coords.longitude;
                if (!riderMap) {
                    riderMap = L.map('rider_map').setView([lat, lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(riderMap);
                    riderMarker = L.marker([lat, lng]).addTo(riderMap).bindPopup('You');
                } else {
                    riderMap.setView([lat, lng], 13);
                    riderMarker.setLatLng([lat, lng]);
                }

                if (destAddress) {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(destAddress))
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            if (res && res[0]) {
                                var dLat = parseFloat(res[0].lat), dLng = parseFloat(res[0].lon);
                                if (riderDestMarker) { riderMap.removeLayer(riderDestMarker); }
                                riderDestMarker = L.marker([dLat, dLng]).addTo(riderMap).bindPopup('Delivery address').openPopup();
                                riderMap.fitBounds([[lat, lng], [dLat, dLng]], { padding: [30, 30] });
                            }
                        }).catch(function () {});
                }
            }, function () {
                document.getElementById('rider_map').innerHTML = '<div style="padding:20px;color:#64748b;font-size:13px;">Could not get your location. Please allow location access.</div>';
            }, { enableHighAccuracy: true });
        });
    }

    var deliverPhotoDataUrl = null;
    function openDeliverModal(id) {
        activeDeliveryId = id;
        document.getElementById('deliver_first_name').value = '';
        document.getElementById('deliver_last_name').value = '';
        document.getElementById('deliver_error').style.display = 'none';
        document.getElementById('deliver_photo_input').value = '';
        document.getElementById('deliver_photo_preview').style.display = 'none';
        deliverPhotoDataUrl = null;
        openModal('deliver_modal');
        if (!sigPad) {
            sigPad = new SignaturePad(document.getElementById('signature_pad_canvas'));
        } else {
            sigPad.clear();
        }
    }
    function clearSignature() { if (sigPad) { sigPad.clear(); } }

    function onDeliverPhotoSelected(event) {
        var file = event.target.files && event.target.files[0];
        if (!file) { return; }
        var reader = new FileReader();
        reader.onload = function (e) {
            deliverPhotoDataUrl = e.target.result;
            var preview = document.getElementById('deliver_photo_preview');
            preview.src = deliverPhotoDataUrl;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function submitDeliver() {
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
        btn.disabled = true; btn.textContent = 'Saving...';
        var payload = {
            token: token, first_name: firstName, last_name: lastName, signature: sigPad.toDataURL('image/png')
        };
        if (deliverPhotoDataUrl) { payload.photo = deliverPhotoDataUrl; }

        if (!navigator.onLine) {
            queueOfflineAction(API.deliveryStart + activeDeliveryId + '/deliver', payload, 'Delivery confirmation for ' + activeDeliveryId);
            btn.disabled = false; btn.textContent = 'Confirm Delivery';
            closeModal('deliver_modal');
            return;
        }

        post(API.deliveryStart + activeDeliveryId + '/deliver', payload).then(function (res) {
            btn.disabled = false; btn.textContent = 'Confirm Delivery';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not save the delivery.';
                errBox.style.display = 'block';
                return;
            }
            toast('Delivery confirmed.', 'success');
            closeModal('deliver_modal');
            loadDeliveries();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Confirm Delivery';
            queueOfflineAction(API.deliveryStart + activeDeliveryId + '/deliver', payload, 'Delivery confirmation for ' + activeDeliveryId);
            closeModal('deliver_modal');
        });
    }

    function openCancelModal(id) {
        activeDeliveryId = id;
        document.getElementById('cancel_reason').value = '';
        document.getElementById('cancel_error').style.display = 'none';
        openModal('cancel_modal');
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
        btn.disabled = true; btn.textContent = 'Cancelling...';
        var payload = { token: token, reason: reason };
        if (!navigator.onLine) {
            queueOfflineAction(API.deliveryStart + activeDeliveryId + '/cancel', payload, 'Cancellation for delivery ' + activeDeliveryId);
            btn.disabled = false; btn.textContent = 'Confirm Cancellation';
            closeModal('cancel_modal');
            return;
        }
        post(API.deliveryStart + activeDeliveryId + '/cancel', payload).then(function (res) {
            btn.disabled = false; btn.textContent = 'Confirm Cancellation';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not cancel the delivery.';
                errBox.style.display = 'block';
                return;
            }
            toast('Delivery cancelled.', 'success');
            closeModal('cancel_modal');
            loadDeliveries();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Confirm Cancellation';
            queueOfflineAction(API.deliveryStart + activeDeliveryId + '/cancel', payload, 'Cancellation for delivery ' + activeDeliveryId);
            closeModal('cancel_modal');
        });
    }

    // ── Pickups ──────────────────────────────────────────────────────────────
    function loadPickups() {
        var box = document.getElementById('pickups_list');
        box.innerHTML = '<div class="empty-state">Loading...</div>';
        get(API.pickups, { token: token }).then(function (res) {
            if (!res.data.success) { box.innerHTML = '<div class="empty-state">Could not load pickups.</div>'; return; }
            if (!res.data.pickups.length) { box.innerHTML = '<div class="empty-state">No active pickups right now.</div>'; return; }

            box.innerHTML = '';
            res.data.pickups.forEach(function (p) {
                var card = document.createElement('div');
                card.className = 'card';
                var nextStatus = p.status === 'pending' ? 'picked_up' : 'delivered';
                var nextLabel = p.status === 'pending' ? 'Mark Picked Up' : 'Mark Delivered to Warehouse';
                card.innerHTML =
                    '<div class="row1"><span class="waybill">Pickup #' + p.id + '</span><span class="badge">' + p.status + '</span></div>' +
                    '<div>' + (p.contact_name || '') + (p.contact_phone ? ' · ' + p.contact_phone : '') + '</div>' +
                    '<div class="muted">' + (p.address || '') + '</div>' +
                    quickActionsHtml(p.contact_phone, p.address) +
                    '<div class="actions"><button class="btn-primary" onclick="openPickupModal(' + p.id + ', \'' + nextStatus + '\', \'' + nextLabel + '\')">' + nextLabel + '</button></div>';
                box.appendChild(card);
            });
        });
    }

    function openPickupModal(id, nextStatus, label) {
        activePickupId = id;
        activePickupStatus = nextStatus;
        document.getElementById('pickup_modal_title').textContent = label;
        document.getElementById('pickup_error').style.display = 'none';
        openModal('pickup_modal');
        if (!pickupSigPad) {
            pickupSigPad = new SignaturePad(document.getElementById('pickup_signature_canvas'));
        } else {
            pickupSigPad.clear();
        }
    }
    function clearPickupSignature() { if (pickupSigPad) { pickupSigPad.clear(); } }

    function submitPickup() {
        var errBox = document.getElementById('pickup_error');
        errBox.style.display = 'none';
        if (!pickupSigPad || pickupSigPad.isEmpty()) {
            errBox.textContent = 'Please capture a signature before confirming.';
            errBox.style.display = 'block';
            return;
        }
        var btn = document.getElementById('confirm_pickup_btn');
        btn.disabled = true; btn.textContent = 'Saving...';
        post(API.pickupUpdate + activePickupId + '/update', {
            token: token, status: activePickupStatus, signature: pickupSigPad.toDataURL('image/png')
        }).then(function (res) {
            btn.disabled = false; btn.textContent = 'Confirm';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not update the pickup.';
                errBox.style.display = 'block';
                return;
            }
            closeModal('pickup_modal');
            loadPickups();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Confirm';
            errBox.textContent = 'Network error — please check your connection and try again.';
            errBox.style.display = 'block';
        });
    }

    // ── PWA install prompt ───────────────────────────────────────────────────
    var deferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredInstallPrompt = e;
        document.getElementById('install_banner').style.display = 'flex';
        document.getElementById('profile_install_btn').style.display = 'block';
    });
    function installApp() {
        if (!deferredInstallPrompt) { return; }
        deferredInstallPrompt.prompt();
        deferredInstallPrompt.userChoice.finally(function () {
            deferredInstallPrompt = null;
            document.getElementById('install_banner').style.display = 'none';
            document.getElementById('profile_install_btn').style.display = 'none';
        });
    }
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(SW_URL, { scope: <?php echo json_encode(site_url('courier_goshipping/rider')); ?> }).catch(function () {});
    }

    // ── Boot ─────────────────────────────────────────────────────────────────
    if (token) {
        get(API.me, { token: token }).then(function (res) {
            if (res.data.success) {
                currentRider = res.data.rider;
                enterApp();
            } else {
                localStorage.removeItem(TOKEN_KEY);
                token = null;
                showScreen('login');
            }
        }).catch(function () {
            showScreen('login');
        });
    }
</script>
</body>
</html>
