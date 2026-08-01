<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Go Shipping Rider</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>
    <link rel="manifest" href="<?php echo site_url('courier_goshipping/rider/manifest'); ?>">
    <meta name="theme-color" content="#f4f6f9">
    <link rel="apple-touch-icon" href="<?php echo site_url('courier_goshipping/rider/icon/192'); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="GS Rider">
    <meta name="mobile-web-app-capable" content="yes">

    <style>
        :root {
            --navy: #0a2a52;
            --navy-deep: #071d3b;
            --blue: #1d5fd6;
            --red: #b3261e;
            --bg: #f4f6f9;
            --surface: #ffffff;
            --surface-sunken: #eef1f6;
            --border: #e2e7ee;
            --ink: #1a2433;
            --ink-soft: #5b6b82;
            --ink-faint: #93a1b5;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg); color: var(--ink);
            padding-top: env(safe-area-inset-top);
            padding-bottom: calc(70px + env(safe-area-inset-bottom));
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3 { margin: 0; }
        a { color: inherit; }
        svg { display: block; }
        .icon { display: inline-flex; }
        .screen { display: none; padding: 18px; max-width: 480px; margin: 0 auto; }
        .screen.active { display: block; }

        /* ── Auth screens ── */
        .auth-screen { padding-top: 8vh; }
        .brand { text-align: center; margin: 0 0 30px; }
        .brand-badge {
            width: 56px; height: 56px; border-radius: 14px; margin: 0 auto 16px;
            background: var(--navy); display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800; color: #fff; letter-spacing: 0.5px;
        }
        .brand h1 { font-size: 20px; color: var(--ink); font-weight: 700; }
        .brand p { color: var(--ink-soft); font-size: 13px; margin-top: 6px; line-height: 1.5; }

        label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin: 16px 0 6px; }
        input[type="text"], input[type="tel"], input[type="password"], textarea {
            width: 100%; padding: 13px 14px; border-radius: 10px; border: 1.5px solid var(--border); font-size: 15px;
            font-family: inherit; background: var(--surface); color: var(--ink);
        }
        input:focus, textarea:focus { outline: none; border-color: var(--blue); }
        input::placeholder, textarea::placeholder { color: var(--ink-faint); }

        button {
            font-size: 14.5px; font-weight: 700; padding: 14px 20px; border-radius: 10px;
            border: none; cursor: pointer; width: 100%; margin-top: 10px;
            font-family: inherit;
        }
        .btn-primary { background: var(--navy); color: #fff; }
        .btn-primary:active { background: var(--navy-deep); }
        .btn-success { background: #1c8a4b; color: #fff; }
        .btn-danger  { background: var(--surface); color: var(--red); border: 1.5px solid #eecbc8; }
        .btn-ghost   { background: var(--surface-sunken); color: var(--ink); font-weight: 600; }
        .link-btn { background: none; color: var(--blue); font-weight: 600; padding: 10px; }

        .error-box, .info-box, .warn-box {
            display: none; margin-top: 14px; padding: 11px 14px; border-radius: 8px; font-size: 13px; line-height: 1.5;
        }
        .error-box { background: #fdecec; color: #9b2d26; }
        .info-box { background: #eef6ee; color: #2c6e3f; }
        .warn-box { background: #fdf3e0; color: #8a5b12; }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 4px 0 18px;
        }
        .topbar h2 { font-size: 19px; color: var(--ink); font-weight: 700; }
        .topbar .hi { font-size: 12.5px; color: var(--ink-soft); margin-top: 2px; }

        /* ── Cards ── */
        .card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
            padding: 14px 15px; margin-bottom: 10px; font-size: 13.5px; line-height: 1.6; text-align: left;
            box-shadow: 0 1px 2px rgba(16, 30, 54, 0.04);
        }
        .card .row1 { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 8px; }
        .card .waybill { font-weight: 700; color: var(--ink); font-size: 14px; }
        .card .badge {
            font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; flex-shrink: 0;
            background: var(--surface-sunken); color: var(--ink-soft);
        }
        .card.is-salibay .badge { background: #fdecec; color: var(--red); }
        .card .muted { color: var(--ink-soft); }
        .card .actions { display: flex; gap: 8px; margin-top: 12px; }
        .card .actions button { margin-top: 0; padding: 10px; font-size: 13px; }

        .empty-state { text-align: center; color: var(--ink-faint); padding: 48px 20px; font-size: 14px; }

        /* ── KPI stat row (not a boxy grid of numbers — a compact strip) ── */
        .kpi-grid { display: flex; gap: 8px; margin: 0 0 20px; }
        .kpi {
            flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
            padding: 12px 8px; text-align: center;
        }
        .kpi .n { font-size: 22px; font-weight: 700; color: var(--navy); line-height: 1.1; }
        .kpi .l { font-size: 10.5px; color: var(--ink-soft); font-weight: 600; margin-top: 4px; }
        .kpi.kpi-sm .n { font-size: 17px; }

        .section-label { font-size: 12px; color: var(--ink-soft); font-weight: 700; margin: 22px 0 10px; }

        .segmented { display: flex; background: var(--surface-sunken); border-radius: 10px; padding: 3px; margin: 0 0 14px; }
        .segmented button { flex: 1; margin: 0; padding: 9px 6px; border-radius: 8px; font-size: 13px; font-weight: 600; background: none; color: var(--ink-soft); }
        .segmented button.active { background: var(--surface); color: var(--navy); box-shadow: 0 1px 3px rgba(16,30,54,.1); }

        .search-box { position: relative; margin-bottom: 14px; }
        .search-box input { padding-left: 38px; }
        .search-box .ic { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ink-faint); }

        .icon-btn {
            width: auto; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 12px; font-size: 12.5px; background: var(--surface-sunken); color: var(--ink); margin: 0;
            text-decoration: none; border: none; border-radius: 9px; font-weight: 600; cursor: pointer;
        }
        .icon-btn.call { background: #eaf6ee; color: #1c8a4b; }
        .icon-btn.whatsapp { background: #eaf6ee; color: #1c8a4b; }
        .quick-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }

        .topbar-actions { display: flex; align-items: center; gap: 8px; }
        .refresh-btn {
            width: auto; margin: 0; padding: 9px; background: var(--surface); border: 1px solid var(--border); color: var(--ink-soft); border-radius: 9px;
        }

        .pod-thumb { width: 100%; max-width: 220px; border-radius: 8px; margin-top: 8px; display: block; }

        .toast-stack { position: fixed; left: 0; right: 0; bottom: calc(84px + env(safe-area-inset-bottom)); z-index: 60; display: flex; flex-direction: column; align-items: center; gap: 8px; pointer-events: none; }
        .toast { background: var(--navy-deep); color: #fff; padding: 11px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; box-shadow: 0 6px 18px rgba(7,29,59,.25); max-width: 90%; text-align: center; }
        .toast.success { background: #1c6b3f; }
        .toast.error { background: #9b2d26; }

        .offline-banner {
            display: none; position: fixed; left: 0; right: 0; top: 0; z-index: 45;
            background: #7a4a08; color: #fff; padding: 9px 16px; font-size: 12px; text-align: center; font-weight: 600;
            align-items: center; justify-content: center; gap: 8px;
        }
        .offline-banner.active { display: flex; }

        .photo-preview { width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; margin-top: 8px; display: none; }

        #signature_pad_canvas { width: 100%; height: 150px; background: #fbfcfe; border: 1.5px dashed var(--border); border-radius: 8px; touch-action: none; display: block; margin-top: 4px; }

        .modal-backdrop {
            display: none; position: fixed; inset: 0; background: rgba(10,20,35,.5); z-index: 50;
            align-items: flex-end; justify-content: center;
        }
        .modal-backdrop.active { display: flex; }
        .modal-sheet {
            background: var(--surface); border-radius: 18px 18px 0 0; padding: 10px 20px calc(20px + env(safe-area-inset-bottom));
            width: 100%; max-width: 480px; max-height: 85vh; overflow-y: auto;
        }
        .modal-sheet::before {
            content: ''; display: block; width: 36px; height: 4px; border-radius: 3px; background: var(--border);
            margin: 8px auto 16px;
        }
        .modal-sheet h3 { font-size: 16px; color: var(--ink); font-weight: 700; margin-bottom: 4px; }

        #install_banner {
            position: fixed; left: 0; right: 0; top: 0; z-index: 40;
            background: var(--navy); color: #fff; padding: 10px 16px; font-size: 12.5px;
            display: none; align-items: center; justify-content: space-between; gap: 10px;
        }
        #install_banner button { width: auto; margin: 0; padding: 8px 14px; font-size: 12px; background: #fff; color: var(--navy); }

        .bottom-nav {
            display: none; position: fixed; left: 0; right: 0; bottom: 0; z-index: 30;
            background: var(--surface); border-top: 1px solid var(--border);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .bottom-nav.active { display: flex; }
        .bottom-nav button {
            flex: 1; background: none; margin: 0; padding: 11px 4px 9px; border-radius: 0;
            color: var(--ink-faint); font-size: 11px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 5px;
        }
        .bottom-nav button.active {
            color: var(--navy); position: relative;
        }
        .bottom-nav button.active::before {
            content: ''; position: absolute; top: 0; left: 30%; right: 30%; height: 2.5px;
            background: var(--navy); border-radius: 0 0 3px 3px;
        }
        .bottom-nav .ic { width: 22px; height: 22px; }
    </style>
</head>
<body>

    <div id="install_banner">
        <span style="display:flex;align-items:center;gap:8px;"><svg class="icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11"/><path d="M8 11l4 4 4-4"/><path d="M5 19h14"/></svg> Install this app for faster access to your deliveries.</span>
        <button onclick="installApp()">Install</button>
    </div>
    <div id="offline_banner" class="offline-banner">
        <svg class="icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M5 12.5a10 10 0 0 1 3-2"/><path d="M12.5 8.03A10 10 0 0 1 19 10.5"/><path d="M2 8.5a15 15 0 0 1 4-2.5"/><path d="M16.5 6a15 15 0 0 1 5.5 2.5"/><circle cx="12" cy="19.5" r="1" fill="currentColor" stroke="none"/></svg>
        You're offline — actions will sync automatically once you're back online.
    </div>
    <div id="toast_stack" class="toast-stack"></div>

    <!-- ── Login ────────────────────────────────────────────────────────────── -->
    <div class="screen active auth-screen" id="screen_login">
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
    <div class="screen auth-screen" id="screen_register">
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
            <div class="topbar-actions"><button class="refresh-btn" onclick="loadDashboard()"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 9a7 7 0 0 1 12-3.5L20 8"/><path d="M18.5 15a7 7 0 0 1-12 3.5L4 16"/></svg></button></div>
        </div>
        <div>
            <div id="unlinked_notice" class="warn-box" style="display:none;">
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
            <div class="topbar-actions"><button class="refresh-btn" onclick="refreshDeliveriesTab()"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 9a7 7 0 0 1 12-3.5L20 8"/><path d="M18.5 15a7 7 0 0 1-12 3.5L4 16"/></svg></button></div>
        </div>
        <div>
            <div class="segmented">
                <button id="deliveries_tab_active" class="active" onclick="switchDeliveriesTab('active')">Active</button>
                <button id="deliveries_tab_history" onclick="switchDeliveriesTab('history')">History</button>
            </div>
            <div class="search-box">
                <span class="ic"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.5-4.5"/></svg></span>
                <input type="text" id="deliveries_search" placeholder="Search by waybill or recipient..." oninput="renderDeliveriesList()">
            </div>
            <div id="deliveries_list"></div>
        </div>
    </div>

    <!-- ── Pickups ──────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_pickups">
        <div class="topbar">
            <h2>My Pickups</h2>
            <div class="topbar-actions"><button class="refresh-btn" onclick="loadPickups()"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 9a7 7 0 0 1 12-3.5L20 8"/><path d="M18.5 15a7 7 0 0 1-12 3.5L4 16"/></svg></button></div>
        </div>
        <div id="pickups_list"></div>
    </div>

    <!-- ── Profile ──────────────────────────────────────────────────────────── -->
    <div class="screen" id="screen_profile">
        <div class="topbar"><h2>Profile</h2></div>
        <div>
            <div class="card">
                <div><strong id="profile_name"></strong></div>
                <div class="muted" id="profile_phone"></div>
                <div class="muted" id="profile_link_status" style="margin-top:6px;"></div>
            </div>
            <button class="btn-ghost" onclick="installApp()" id="profile_install_btn" style="display:none; align-items:center; justify-content:center; gap:8px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11"/><path d="M8 11l4 4 4-4"/><path d="M5 19h14"/></svg>
                Install App to Home Screen
            </button>
            <button class="btn-ghost" onclick="openModal('change_password_modal')">Change Password</button>
            <button class="btn-danger" onclick="doLogout()">Log Out</button>
        </div>
    </div>

    <!-- ── Bottom nav ───────────────────────────────────────────────────────── -->
    <div class="bottom-nav" id="bottom_nav">
        <button onclick="navTo('dashboard')" data-nav="dashboard"><span class="ic"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h4v-6h2v6h4a1 1 0 0 0 1-1v-9"/></svg></span>Dashboard</button>
        <button onclick="navTo('deliveries')" data-nav="deliveries"><span class="ic"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l9-4 9 4-9 4-9-4Z"/><path d="M3 8v9l9 4 9-4V8"/><path d="M12 12v9"/></svg></span>Deliveries</button>
        <button onclick="navTo('pickups')" data-nav="pickups"><span class="ic"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/></svg></span>Pickups</button>
        <button onclick="navTo('profile')" data-nav="profile"><span class="ic"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-4 4-6 7-6s5.8 2 7 6"/></svg></span>Profile</button>
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

    <!-- ── Change password modal ────────────────────────────────────────────── -->
    <div class="modal-backdrop" id="change_password_modal">
        <div class="modal-sheet">
            <h3>Change Password</h3>
            <label for="cp_current_password">Current password</label>
            <input type="password" id="cp_current_password">
            <label for="cp_new_password">New password</label>
            <input type="password" id="cp_new_password" placeholder="At least 4 characters">
            <button class="btn-success" onclick="submitChangePassword()" id="confirm_change_password_btn">Update Password</button>
            <button class="btn-ghost" onclick="closeModal('change_password_modal')">Cancel</button>
            <div class="error-box" id="change_password_error"></div>
            <div class="info-box" id="change_password_success"></div>
        </div>
    </div>

<script>
    // Small line-icon set (stroke-based, currentColor) used in place of emoji
    // throughout the app — kept as plain strings so both server-rendered
    // markup and JS-built card templates can share the exact same icons.
    var Icons = {
        home: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h4v-6h2v6h4a1 1 0 0 0 1-1v-9"/></svg>',
        box: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l9-4 9 4-9 4-9-4Z"/><path d="M3 8v9l9 4 9-4V8"/><path d="M12 12v9"/></svg>',
        receipt: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/></svg>',
        user: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-4 4-6 7-6s5.8 2 7 6"/></svg>',
        refresh: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 9a7 7 0 0 1 12-3.5L20 8"/><path d="M18.5 15a7 7 0 0 1-12 3.5L4 16"/></svg>',
        search: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.5-4.5"/></svg>',
        phone: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C9.5 21 3 14.5 3 6a2 2 0 0 1 2-2Z"/></svg>',
        chat: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 1 1 3.2 6.4L4 20l1.3-3.5A8 8 0 0 1 4 12Z"/></svg>',
        compass: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 6-6 2 2-6 6-2Z"/></svg>',
        download: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11"/><path d="M8 11l4 4 4-4"/><path d="M5 19h14"/></svg>',
        wifiOff: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M5 12.5a10 10 0 0 1 3-2"/><path d="M12.5 8.03A10 10 0 0 1 19 10.5"/><path d="M2 8.5a15 15 0 0 1 4-2.5"/><path d="M16.5 6a15 15 0 0 1 5.5 2.5"/><circle cx="12" cy="19.5" r="1" fill="currentColor" stroke="none"/></svg>',
        checkCircle: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>',
        alert: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l10 18H2L12 3Z"/><path d="M12 10v4"/><circle cx="12" cy="17" r=".6" fill="currentColor" stroke="none"/></svg>'
    };

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
        stats: <?php echo json_encode(site_url('courier_goshipping/rider-api/stats')); ?>,
        changePassword: <?php echo json_encode(site_url('courier_goshipping/rider-api/change_password')); ?>
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

    function submitChangePassword() {
        var errBox = document.getElementById('change_password_error');
        var okBox = document.getElementById('change_password_success');
        errBox.style.display = 'none';
        okBox.style.display = 'none';

        var currentPassword = document.getElementById('cp_current_password').value;
        var newPassword = document.getElementById('cp_new_password').value;
        if (!currentPassword || newPassword.length < 4) {
            errBox.textContent = 'Please enter your current password and a new password of at least 4 characters.';
            errBox.style.display = 'block';
            return;
        }

        var btn = document.getElementById('confirm_change_password_btn');
        btn.disabled = true; btn.textContent = 'Updating...';
        post(API.changePassword, { token: token, current_password: currentPassword, new_password: newPassword }).then(function (res) {
            btn.disabled = false; btn.textContent = 'Update Password';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not update your password.';
                errBox.style.display = 'block';
                return;
            }
            document.getElementById('cp_current_password').value = '';
            document.getElementById('cp_new_password').value = '';
            okBox.textContent = 'Password updated.';
            okBox.style.display = 'block';
            toast('Password updated.', 'success');
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Update Password';
            errBox.textContent = 'Network error — please check your connection and try again.';
            errBox.style.display = 'block';
        });
    }

    function enterApp() {
        document.getElementById('bottom_nav').classList.add('active');
        document.getElementById('profile_name').textContent = currentRider.name;
        document.getElementById('profile_phone').textContent = currentRider.phone;
        var linkStatusEl = document.getElementById('profile_link_status');
        linkStatusEl.style.cssText = 'margin-top:6px; display:flex; align-items:center; gap:6px; color:' + (currentRider.linked ? '#1c8a4b' : '#8a5b12') + ';';
        linkStatusEl.innerHTML = (currentRider.linked ? Icons.checkCircle : Icons.alert) +
            '<span>' + (currentRider.linked ? 'Linked to your driver profile' : 'Not linked yet — contact your dispatcher') + '</span>';
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
            html += '<a class="icon-btn call" href="' + callHref(phone) + '">' + Icons.phone + ' Call</a>';
            html += '<a class="icon-btn whatsapp" href="' + whatsappHref(phone) + '" target="_blank" rel="noopener">' + Icons.chat + ' WhatsApp</a>';
        }
        if (address) {
            html += '<a class="icon-btn" href="' + navigateHref(address) + '" target="_blank" rel="noopener">' + Icons.compass + ' Navigate</a>';
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
                document.getElementById('rider_map').innerHTML = '<div style="padding:20px;color:#5b6b82;font-size:13px;">Could not get your location. Please allow location access.</div>';
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
        var payload = { token: token, status: activePickupStatus, signature: pickupSigPad.toDataURL('image/png') };
        if (!navigator.onLine) {
            queueOfflineAction(API.pickupUpdate + activePickupId + '/update', payload, 'Pickup update for #' + activePickupId);
            btn.disabled = false; btn.textContent = 'Confirm';
            closeModal('pickup_modal');
            return;
        }
        post(API.pickupUpdate + activePickupId + '/update', payload).then(function (res) {
            btn.disabled = false; btn.textContent = 'Confirm';
            if (!res.data.success) {
                errBox.textContent = res.data.message || 'Could not update the pickup.';
                errBox.style.display = 'block';
                return;
            }
            toast('Pickup updated.', 'success');
            closeModal('pickup_modal');
            loadPickups();
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Confirm';
            queueOfflineAction(API.pickupUpdate + activePickupId + '/update', payload, 'Pickup update for #' + activePickupId);
            closeModal('pickup_modal');
        });
    }

    // ── PWA install prompt ───────────────────────────────────────────────────
    var deferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredInstallPrompt = e;
        document.getElementById('install_banner').style.display = 'flex';
        document.getElementById('profile_install_btn').style.display = 'flex';
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
        if (navigator.onLine) { flushOfflineQueue(); }
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
