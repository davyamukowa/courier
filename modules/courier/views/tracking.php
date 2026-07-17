<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">

    <?php
    $company_name      = get_option('companyname') ?: 'Courier Services';
    $_lc_raw           = get_option('courier_logistic_company');
    // Treat the old seeded default as empty so the Perfex company name is used
    $logistic_company  = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : $company_name;
    $company_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
    $company_logo_url  = !empty($company_logo_file) ? base_url('uploads/company/' . $company_logo_file) : '';
    $company_phone     = get_option('invoice_company_phonenumber') ?: get_option('company_phonenumber');
    $company_email     = get_option('smtp_email') ?: get_option('company_email');
    ?>
    <title><?php echo htmlspecialchars($logistic_company); ?> - Customer Portal</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6fb;
            color: #333;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #333;
            font-size: 20px;
            font-weight: 700;
        }
        .navbar .brand img { height: 44px; width: auto; }
        .navbar .nav-phone {
            font-size: 14px;
            color: #555;
        }
        .navbar .nav-phone a {
            color: #28a745;
            font-weight: 600;
            text-decoration: none;
        }

        /* ── Tabs ── */
        .tabs-bar {
            background: #fff;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
            padding: 0 20px;
            gap: 4px;
        }
        .tab-btn {
            padding: 14px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
        }
        .tab-btn:hover { text-decoration: none; }
        .tab-btn:hover { color: #28a745; }
        .tab-btn.active { color: #28a745; border-bottom-color: #28a745; }

        /* ── Main content ── */
        .portal-content {
            max-width: 900px;
            margin: 36px auto;
            padding: 0 16px;
        }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,.07);
            padding: 32px;
            margin-bottom: 24px;
        }
        .card h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #222;
        }
        .card h2 i { margin-right: 8px; color: #28a745; }

        /* ── Forms ── */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #dde3ed;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color .2s;
        }
        .form-control:focus { border-color: #28a745; }
        select.form-control { background: #fff; }
        .form-row { display: flex; gap: 16px; flex-wrap: wrap; }
        .form-row .form-group { flex: 1; min-width: 200px; }

        .btn {
            padding: 11px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            transition: background .2s, transform .1s;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: #28a745; color: #fff; }
        .btn-primary:hover { background: #1e7e34; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-outline { background: #fff; border: 1.5px solid #28a745; color: #28a745; }
        .btn-outline:hover { background: #28a745; color: #fff; }

        /* ── Tracking ── */
        .tracking-input { display: flex; gap: 10px; margin-bottom: 24px; }
        .tracking-input input { flex: 1; }
        .tracking-input button { flex-shrink: 0; }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
            margin-bottom: 16px;
        }

        .timeline { position: relative; padding-left: 32px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dde3ed;
        }
        .timeline-item {
            position: relative;
            padding: 10px 0 10px 20px;
            display: none;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 14px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #28a745;
        }
        .timeline-item h4 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .timeline-item p { font-size: 13px; color: #666; margin: 0; }
        .timeline-item .tl-time { font-size: 11px; color: #999; }

        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
        @media(max-width: 600px) { .data-grid { grid-template-columns: 1fr; } }
        .data-block h4 { font-size: 13px; color: #28a745; font-weight: 600; margin-bottom: 8px; }
        .data-block p { font-size: 13px; color: #444; margin-bottom: 4px; }

        /* ── Service points ── */
        .service-point-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 14px;
        }
        .service-point-card h4 { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .service-point-card p { font-size: 13px; color: #555; margin-bottom: 4px; }
        .service-point-card p i { width: 18px; color: #28a745; }

        /* ── Quote result ── */
        .quote-result {
            background: #f0fff4;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .quote-result h3 { font-size: 18px; color: #28a745; margin-bottom: 10px; }
        .quote-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .quote-table td { padding: 6px 10px; border-bottom: 1px solid #dee2e6; }
        .quote-table td:first-child { font-weight: 600; color: #555; }

        /* ── Alert ── */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
            display: none;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        /* ── Call for booking ── */
        .call-card {
            text-align: center;
            padding: 48px 32px;
        }
        .call-card .call-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .call-card h2 { margin-bottom: 10px; }
        .call-card p { color: #666; margin-bottom: 24px; }
        .call-card .phone-number {
            font-size: 28px;
            font-weight: 700;
            color: #28a745;
            text-decoration: none;
        }

        /* ── Footer ── */
        .portal-footer {
            text-align: center;
            padding: 24px;
            color: #999;
            font-size: 12px;
        }

        /* ── Mobile responsive ── */
        @media (max-width: 767px) {
            .navbar { padding: 10px 16px; flex-wrap: wrap; gap: 8px; }
            .navbar .brand { font-size: 16px; }
            .navbar .brand img { height: 34px; }
            .navbar .nav-phone { font-size: 13px; }

            .tabs-bar { padding: 0 6px; gap: 0; }
            .tab-btn { padding: 10px 10px; font-size: 11px; gap: 4px; flex-direction: column; align-items: center; }
            .tab-btn i { font-size: 16px; }

            .portal-content { margin: 12px auto; padding: 0 10px; }
            .card { padding: 18px 14px; border-radius: 10px; }
            .card h2 { font-size: 17px; }

            .form-row { flex-direction: column; gap: 0; }
            .form-row .form-group { min-width: unset; }

            .tracking-input { flex-direction: column; }
            .tracking-input button { width: 100%; }

            .data-grid { grid-template-columns: 1fr; }

            .call-card { padding: 32px 16px; }
            .call-card .call-icon { font-size: 48px; }
            .call-card .phone-number { font-size: 22px; }

            .btn { width: 100%; text-align: center; }
        }

        @media (max-width: 400px) {
            .tab-btn { padding: 8px 6px; font-size: 10px; }
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
    <a class="brand" href="#">
        <?php if ($company_logo_url): ?>
            <img src="<?php echo $company_logo_url; ?>" alt="<?php echo htmlspecialchars($logistic_company); ?>">
        <?php endif; ?>
        <span><?php echo htmlspecialchars($logistic_company); ?></span>
    </a>
    <?php if ($company_phone): ?>
    <div class="nav-phone">
        <i class="fas fa-phone"></i> Call: <a href="tel:<?php echo htmlspecialchars($company_phone); ?>"><?php echo htmlspecialchars($company_phone); ?></a>
    </div>
    <?php endif; ?>
</nav>

<!-- Tabs -->
<?php
$active_tab = $active_tab ?? 'track';
$tab_urls = [
    'track'    => base_url('courier/track'),
    'quote'    => base_url('courier/get-a-quote'),
    'pickup'   => base_url('courier/schedule-delivery'),
    'service'  => base_url('courier/find-service-point'),
    'shipment' => base_url('courier/create-shipment'),
    'call'     => base_url('courier/call-for-booking'),
];
?>
<div class="tabs-bar">
    <a href="<?php echo $tab_urls['track']; ?>" class="tab-btn<?php echo $active_tab === 'track' ? ' active' : ''; ?>" onclick="return switchTab(event,'track')" id="tab-track">
        <i class="fas fa-search"></i> Track
    </a>
    <a href="<?php echo $tab_urls['quote']; ?>" class="tab-btn<?php echo $active_tab === 'quote' ? ' active' : ''; ?>" onclick="return switchTab(event,'quote')" id="tab-quote">
        <i class="fas fa-calculator"></i> Get a Quote
    </a>
    <a href="<?php echo $tab_urls['pickup']; ?>" class="tab-btn<?php echo $active_tab === 'pickup' ? ' active' : ''; ?>" onclick="return switchTab(event,'pickup')" id="tab-pickup">
        <i class="fas fa-truck-pickup"></i> Schedule Delivery / Request Bike Delivery
    </a>
    <a href="<?php echo $tab_urls['service']; ?>" class="tab-btn<?php echo $active_tab === 'service' ? ' active' : ''; ?>" onclick="return switchTab(event,'service')" id="tab-service">
        <i class="fas fa-map-marker-alt"></i> Find Service Point
    </a>
    <a href="<?php echo $tab_urls['shipment']; ?>" class="tab-btn<?php echo $active_tab === 'shipment' ? ' active' : ''; ?>" onclick="return switchTab(event,'shipment')" id="tab-shipment">
        <i class="fas fa-box"></i> Create Shipment
    </a>
    <a href="<?php echo $tab_urls['call']; ?>" class="tab-btn<?php echo $active_tab === 'call' ? ' active' : ''; ?>" onclick="return switchTab(event,'call')" id="tab-call">
        <i class="fas fa-phone-alt"></i> Call for Booking
    </a>
</div>

<div class="portal-content">

    <!-- ═══════════════ TRACK ═══════════════ -->
    <div class="tab-panel<?php echo $active_tab === 'track' ? ' active' : ''; ?>" id="panel-track">
        <div class="card">
            <h2><i class="fas fa-search"></i> Track Your Shipment</h2>
            <div class="tracking-input">
                <input type="text" id="tracking_number" class="form-control" placeholder="Enter tracking / waybill number…">
                <button class="btn btn-primary" id="get_shipment_btn"><i class="fas fa-search"></i> Track</button>
            </div>

            <div id="track-result" style="display:none;">
                <div id="status-badge-wrap"></div>

                <div class="data-grid" id="shipment_data">
                    <div class="data-block">
                        <h4><i class="fas fa-user"></i> Sender</h4>
                        <p><strong>Name:</strong> <span id="sender_name"></span></p>
                        <p><strong>Phone:</strong> <span id="sender_phone"></span></p>
                        <p><strong>Address:</strong> <span id="sender_address"></span></p>
                    </div>
                    <div class="data-block">
                        <h4><i class="fas fa-user-check"></i> Recipient</h4>
                        <p><strong>Name:</strong> <span id="recipient_name"></span></p>
                        <p><strong>Phone:</strong> <span id="recipient_phone"></span></p>
                        <p><strong>Address:</strong> <span id="recipient_address"></span></p>
                    </div>
                </div>

                <div id="delivery_info" style="display:none; margin-top:16px;" class="data-block">
                    <h4><i class="fas fa-check-circle"></i> Delivered To</h4>
                    <p id="delivered_to"></p>
                </div>

                <div class="timeline" id="status-timeline" style="margin-top:28px;">
                    <div class="timeline-item" id="status-1">
                        <h4>Shipment Created</h4>
                        <p>Your shipment has been created.</p>
                        <p class="tl-time" id="time-1"></p>
                    </div>
                    <div class="timeline-item" id="status-2">
                        <h4>Picked Up</h4>
                        <p>Package was picked up from sender.</p>
                        <p class="tl-time" id="time-2"></p>
                    </div>
                    <div class="timeline-item" id="status-3">
                        <h4>Received at Warehouse</h4>
                        <p>Package received at our facility.</p>
                        <p class="tl-time" id="time-3"></p>
                    </div>
                    <div class="timeline-item" id="status-4">
                        <h4>Dispatched</h4>
                        <p>Package has been dispatched.</p>
                        <p class="tl-time" id="time-4"></p>
                    </div>
                    <div class="timeline-item" id="status-5">
                        <h4>In Transit</h4>
                        <p>Package is on its way.</p>
                        <p class="tl-time" id="time-5"></p>
                        <div id="shipment-stops" style="margin-top:10px; display:none;">
                            <p style="font-weight:600; color:#555;">Transit stops:</p>
                            <div id="stops-container"></div>
                        </div>
                    </div>
                    <div class="timeline-item" id="status-6">
                        <h4>Arrived at Destination</h4>
                        <p>Package has arrived at destination hub.</p>
                        <p class="tl-time" id="time-6"></p>
                    </div>
                    <div class="timeline-item" id="status-7">
                        <h4>Out for Delivery</h4>
                        <p>Package is out for final delivery.</p>
                        <p class="tl-time" id="time-7"></p>
                    </div>
                    <div class="timeline-item" id="status-8">
                        <h4>Delivered</h4>
                        <p>Package has been delivered.</p>
                        <p class="tl-time" id="time-8"></p>
                    </div>
                </div>
            </div>

            <div id="track-empty" style="text-align:center; padding:30px 0; color:#999; display:none;">
                <i class="fas fa-box-open" style="font-size:40px; margin-bottom:12px;"></i>
                <p>No shipment found for that tracking number.</p>
            </div>
        </div>
    </div>

    <!-- ═══════════════ GET A QUOTE ═══════════════ -->
    <?php
    $is_international = (($courier_type ?? 'international') === 'international');
    $fcl_rates_json   = json_encode($fcl_rates ?? []);
    ?>
    <div class="tab-panel<?php echo $active_tab === 'quote' ? ' active' : ''; ?>" id="panel-quote">
        <div class="card">
            <h2><i class="fas fa-calculator"></i> Get a Quote</h2>
            <p style="color:#666;font-size:13px;margin-bottom:20px;">
                <?php if ($is_international): ?>
                Select your service type, destination and weight to get an instant freight quote.
                <?php else: ?>
                Select your cargo type, destination zone and weight to get an instant price estimate.
                <?php endif; ?>
                All amounts are in <strong>Kenya Shillings (KES)</strong> and include VAT at <?php echo number_format($parcel_vat_rate ?? 16, 0); ?>%.
            </p>

            <div id="quote-error-msg" style="display:none;background:#fdecea;border:1px solid #f5c6cb;border-radius:6px;padding:12px 16px;color:#c62828;margin-bottom:16px;font-size:13px;"></div>

            <?php if ($is_international): ?>
            <!-- STEP 0 (International only): Service Type -->
            <div class="form-group" id="q-service-section">
                <label style="font-weight:600;">1. Select Service</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-top:8px;" id="q-service-grid">
                    <?php
                    $services = [
                        'domestic'          => ['icon'=>'fas fa-home',        'label'=>'Domestic'],
                        'road'              => ['icon'=>'fas fa-truck',        'label'=>'Road'],
                        'fcl'               => ['icon'=>'fas fa-ship',         'label'=>'FCL'],
                        'lcl'               => ['icon'=>'fas fa-boxes',        'label'=>'LCL'],
                        'consolidation'     => ['icon'=>'fas fa-layer-group',  'label'=>'Consolidation'],
                        'air_freight'       => ['icon'=>'fas fa-plane',        'label'=>'Air Freight'],
                        'air_consolidation' => ['icon'=>'fas fa-plane-arrival','label'=>'Air Consolidation'],
                        'courier'           => ['icon'=>'fas fa-motorcycle',   'label'=>'Courier'],
                    ];
                    foreach ($services as $svc => $info):
                    ?>
                    <div id="qsvc-<?php echo $svc; ?>" onclick="selectServiceType('<?php echo $svc; ?>')"
                         style="border:2px solid #ddd;border-radius:8px;padding:12px 8px;cursor:pointer;text-align:center;background:#fff;transition:all .2s;">
                        <i class="<?php echo $info['icon']; ?>" style="font-size:20px;color:#888;display:block;margin-bottom:5px;"></i>
                        <span style="font-size:12px;font-weight:600;color:#555;"><?php echo $info['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="q_service_type" value="">
            </div>

            <!-- FCL Container Selector (shown only for FCL) -->
            <div id="q-fcl-section" style="display:none;margin-top:18px;">
                <label style="font-weight:600;">2. Container Type</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-top:8px;" id="q-container-grid">
                    <?php
                    $containers = [
                        '20dv'=>"20' DV",'40dv'=>"40' DV",'20hc'=>"20' HC",'40hc'=>"40' HC",
                        '20rf'=>"20' RF",'40rf'=>"40' RF",'20fr'=>"20' FR",'40fr'=>"40' FR",'roro'=>'RoRo',
                    ];
                    foreach ($containers as $key => $lbl):
                        $r = (float)(($fcl_rates ?? [])[$key] ?? 0);
                    ?>
                    <div id="qcnt-<?php echo $key; ?>" onclick="selectContainer('<?php echo $key; ?>')"
                         style="border:2px solid #ddd;border-radius:8px;padding:10px 6px;cursor:pointer;text-align:center;background:#fff;transition:all .2s;">
                        <span style="font-size:12px;font-weight:600;display:block;"><?php echo $lbl; ?></span>
                        <?php if ($r > 0): ?>
                        <small style="color:#28a745;font-size:11px;">KES <?php echo number_format($r, 0); ?></small>
                        <?php else: ?>
                        <small style="color:#aaa;font-size:11px;">Contact Us</small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="q_container" value="">
            </div>
            <?php endif; ?>

            <!-- ── Cargo Type (hidden for FCL & hidden initially for international) ── -->
            <div class="form-group" id="q-cargo-section" style="margin-top:18px;display:none;">
                <label style="font-weight:600;"><?php echo $is_international ? '3.' : '1.'; ?> Cargo Type</label>
                <div style="display:flex;gap:12px;margin-top:6px;">
                    <label class="q-type-card" id="qcard-document" onclick="selectCargoType('document')" style="flex:1;border:2px solid #1a5276;border-radius:8px;padding:14px 18px;cursor:pointer;text-align:center;background:#eaf0fb;transition:all .2s;">
                        <i class="fas fa-file-alt" style="font-size:22px;color:#1a5276;display:block;margin-bottom:6px;"></i>
                        <span style="font-weight:600;color:#1a5276;">Document</span>
                        <small style="display:block;color:#888;font-size:11px;margin-top:2px;">≤ 350 g</small>
                    </label>
                    <label class="q-type-card" id="qcard-parcel" onclick="selectCargoType('parcel')" style="flex:1;border:2px solid #ddd;border-radius:8px;padding:14px 18px;cursor:pointer;text-align:center;background:#fff;transition:all .2s;">
                        <i class="fas fa-box" style="font-size:22px;color:#555;display:block;margin-bottom:6px;"></i>
                        <span style="font-weight:600;color:#555;">Package / Parcel</span>
                        <small style="display:block;color:#888;font-size:11px;margin-top:2px;">1 kg and above</small>
                        <?php if (!empty($parcel_rate_per_kg) && $parcel_rate_per_kg > 0): ?>
                        <small style="display:block;color:#28a745;font-size:11px;margin-top:3px;font-weight:600;">
                            KES <?php echo number_format($parcel_rate_per_kg, 2); ?>/kg
                        </small>
                        <?php endif; ?>
                    </label>
                </div>
                <input type="hidden" id="q_cargo_type" value="document">
            </div>

            <?php if ($is_international): ?>
            <!-- ── INTERNATIONAL: Origin + Destination country pickers ── -->
            <div id="q-destination-section" style="display:none;margin-top:18px;">
                <label style="font-weight:600;">2. Origin &amp; Destination</label>

                <!-- Origin row -->
                <div style="margin-top:10px;">
                    <label style="font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        <i class="fas fa-map-pin" style="color:#e74c3c;margin-right:4px;"></i> Origin Country <span style="color:red">*</span>
                    </label>
                    <select id="q_origin_select"
                            onchange="selectOrigin(this.value)"
                            style="width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box;">
                        <option value="">-- Select origin country --</option>
                    </select>
                    <input type="hidden" id="q_origin_selected" value="">
                </div>

                <!-- Destination row -->
                <div style="margin-top:14px;">
                    <label style="font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        <i class="fas fa-map-marker-alt" style="color:#28a745;margin-right:4px;"></i> Destination Country <span style="color:red">*</span>
                    </label>
                    <select id="q_country_select"
                            onchange="selectCountry(this.value)"
                            style="width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box;">
                        <option value="">-- Select destination country --</option>
                    </select>
                    <input type="hidden" id="q_country_selected" value="">
                </div>

                <!-- City / Town (optional) -->
                <div style="margin-top:14px;">
                    <label style="font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        <i class="fas fa-city" style="color:#888;margin-right:4px;"></i> City / Town <span style="font-size:11px;color:#aaa;">(optional)</span>
                    </label>
                    <div id="q_city_loading" style="display:none;padding:10px 0;color:#888;font-size:12px;">
                        <i class="fas fa-spinner fa-spin" style="margin-right:6px;color:#1a5276;"></i> Loading cities…
                    </div>
                    <select id="q_city_select"
                            onchange="selectCity(this.value)"
                            style="width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box;">
                        <option value="">-- Select destination country first --</option>
                    </select>
                    <input type="hidden" id="q_city_selected" value="">
                </div>

            </div><!-- /#q-destination-section -->
            <small id="q_zone_desc" style="display:none;"></small><!-- kept for JS compat -->

            <!-- ── Domestic (within-country) city pickers — shown only for Domestic service while in International/Freight mode ── -->
            <div id="q-domestic-city-section" style="display:none;margin-top:18px;">
                <label style="font-weight:600;">2. Origin &amp; Destination City</label>

                <div style="margin-top:10px;">
                    <label style="font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        <i class="fas fa-map-pin" style="color:#e74c3c;margin-right:4px;"></i> Origin City <span style="color:red">*</span>
                    </label>
                    <select id="q_origin_city_select"
                            onchange="selectOriginCity(this.value)"
                            style="width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box;">
                        <option value="">-- Select origin city --</option>
                    </select>
                </div>

                <div style="margin-top:14px;">
                    <label style="font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        <i class="fas fa-map-marker-alt" style="color:#28a745;margin-right:4px;"></i> Destination City <span style="color:red">*</span>
                    </label>
                    <select id="q_dest_city_select"
                            onchange="selectDestCity(this.value)"
                            style="width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box;">
                        <option value="">-- Select destination city --</option>
                    </select>
                </div>
            </div><!-- /#q-domestic-city-section -->

            <!-- Rate Zone (auto-detected or manual; shown for Domestic and non-FCL international) -->
            <div id="q-zone-auto-wrap" style="display:none;margin-top:14px;padding:10px 14px;background:#fffde7;border:1px solid #fff176;border-radius:6px;font-size:12px;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="color:#555;font-weight:600;white-space:nowrap;"><i class="fas fa-tag" style="color:#f9a825;margin-right:4px;"></i>Rate Zone:</span>
                    <select id="q_zone" style="flex:1;min-width:180px;padding:6px 10px;border:1px solid #ddd;border-radius:4px;font-size:12px;">
                        <option value="">-- Select zone for rate --</option>
                    </select>
                    <small id="q_zone_auto_note" style="color:#888;font-style:italic;"></small>
                </div>
                <small id="q_zone_auto_note2" style="color:#999;display:block;margin-top:4px;">Rate zone determines pricing. Auto-detected from destination when possible.</small>
            </div>
            <?php else: ?>
            <!-- ── LOCAL: simple zone dropdown ── -->
            <div class="form-group" id="q-zone-section" style="margin-top:18px;">
                <label style="font-weight:600;">2. Destination Zone</label>
                <select class="form-control" id="q_zone" style="margin-top:6px;font-size:13px;">
                    <option value="">-- Loading zones... --</option>
                </select>
                <small id="q_zone_desc" style="display:block;margin-top:5px;color:#666;font-size:11px;font-style:italic;"></small>
            </div>
            <?php endif; ?>

            <!-- ── Weight & Dimensions ── -->
            <div id="q-weight-section" style="display:none;margin-top:18px;">
                <label style="font-weight:600;"><?php echo $is_international ? '4.' : '3.'; ?> Weight &amp; Dimensions</label>
                <small id="q-dim-factor-note" style="display:block;color:#888;font-size:11px;margin-bottom:8px;"></small>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                    <div>
                        <label style="font-size:12px;color:#555;">Actual Weight (kg) <span style="color:red">*</span></label>
                        <input type="number" class="form-control" id="q_weight" placeholder="e.g. 5.0" min="0.1" step="0.1" style="font-size:13px;">
                    </div>
                    <div></div>
                </div>

                <div style="margin-top:10px;">
                    <a href="#" onclick="toggleDims(event)" style="font-size:12px;color:#1a5276;">
                        <i class="fas fa-ruler-combined"></i> Add dimensions for volumetric weight (optional)
                    </a>
                </div>
                <div id="q-dims-section" style="display:none;margin-top:8px;">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                        <div>
                            <label style="font-size:12px;color:#555;">Length (cm)</label>
                            <input type="number" class="form-control" id="q_length" placeholder="0" min="0" step="0.1" style="font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#555;">Width (cm)</label>
                            <input type="number" class="form-control" id="q_width" placeholder="0" min="0" step="0.1" style="font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#555;">Height (cm)</label>
                            <input type="number" class="form-control" id="q_height" placeholder="0" min="0" step="0.1" style="font-size:13px;">
                        </div>
                    </div>
                    <small id="q-vol-formula" style="color:#888;font-size:11px;">Volumetric weight = L × W × H ÷ 6000. Chargeable weight = max(actual, volumetric).</small>
                </div>
            </div>

            <!-- Contact Details -->
            <div id="q-contact-section" style="margin-top:18px;">
                <label style="font-weight:600;"><?php echo $is_international ? '5.' : '4.'; ?> Contact Information</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                    <div>
                        <label style="font-size:12px;color:#555;">Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" id="q_contact_name" style="font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px;color:#555;">Company <span style="color:red">*</span></label>
                        <input type="text" class="form-control" id="q_contact_company" style="font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px;color:#555;">Email <span style="color:red">*</span></label>
                        <input type="email" class="form-control" id="q_contact_email" style="font-size:13px;" required>
                    </div>
                    <div>
                        <label style="font-size:12px;color:#555;">Phone <span style="color:red">*</span></label>
                        <input type="tel" class="form-control" id="q_contact_phone" style="font-size:13px;" required>
                    </div>
                </div>
            </div>

            <!-- Calculate Button -->
            <div style="margin-top:22px;">
                <button class="btn btn-primary" id="q-calc-btn" onclick="runCalculateQuote()" style="padding:10px 28px;font-size:14px;">
                    <i class="fas fa-calculator"></i> Calculate Quote
                </button>
            </div>

            <!-- Quote Result -->
            <div id="quote-result-panel" style="display:none;margin-top:24px;border:1px solid #d4e6f1;border-radius:8px;overflow:hidden;">
                <div style="background:#1a5276;color:#fff;padding:12px 18px;font-weight:600;font-size:15px;">
                    <i class="fas fa-receipt"></i> Price Breakdown
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <tr id="qr_service_row" style="background:#f4f9ff;display:none;">
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;width:55%;">Service</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;font-weight:600;" id="qr_service"></td>
                    </tr>
                    <tr style="background:#f4f9ff;" id="qr_cargo_row">
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;width:55%;">Cargo Type</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;font-weight:600;" id="qr_cargo"></td>
                    </tr>
                    <tr id="qr_container_row" style="display:none;">
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">Container</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;font-weight:600;" id="qr_container"></td>
                    </tr>
                    <tr>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;" id="qr_zone_label">Route / Zone</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;font-weight:600;" id="qr_zone"></td>
                    </tr>
                    <tr id="qr_weight_rows">
                        <td colspan="2" style="padding:0;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tr style="background:#f4f9ff;">
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;width:55%;">Actual Weight</td>
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_actual_wt"></td>
                                </tr>
                                <tr id="qr_vol_row">
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">Volumetric Weight</td>
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_vol_wt"></td>
                                </tr>
                                <tr style="background:#f4f9ff;">
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;font-weight:600;">Chargeable Weight</td>
                                    <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;font-weight:700;" id="qr_charge_wt"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">Transport Charge</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_transport"></td>
                    </tr>
                    <tr style="background:#f4f9ff;">
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">Handling Fee</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_handling"></td>
                    </tr>
                    <tr>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">Subtotal</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_subtotal"></td>
                    </tr>
                    <tr style="background:#f4f9ff;">
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;color:#555;">VAT (<span id="qr_vat_label"><?php echo number_format($parcel_vat_rate ?? 16, 0); ?></span>%)</td>
                        <td style="padding:9px 16px;border-bottom:1px solid #e0e9f5;" id="qr_vat"></td>
                    </tr>
                    <tr style="background:#e8f5e9;">
                        <td style="padding:13px 16px;font-weight:700;font-size:15px;color:#1b5e20;">TOTAL (VAT Inclusive)</td>
                        <td style="padding:13px 16px;font-weight:700;font-size:17px;color:#1b5e20;" id="qr_total"></td>
                    </tr>
                </table>
                <div style="padding:14px 18px;background:#f9f9f9;border-top:1px solid #e0e9f5;">
                    <p style="margin:0 0 10px;font-size:12px;color:#777;">
                        <i class="fas fa-info-circle"></i>
                        Estimate only — final price confirmed at office after weight verification.
                        Valid for 7 days.
                    </p>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <input type="email" id="q_email" class="form-control" placeholder="Your email address" style="flex:1;min-width:200px;font-size:13px;max-width:300px;">
                        <button class="btn btn-success" id="q-email-btn" onclick="sendQuoteEmail()" style="white-space:nowrap;">
                            <i class="fas fa-envelope"></i> Email Me This Quote
                        </button>
                    </div>
                    <div id="q-email-alert" style="display:none;margin-top:8px;font-size:13px;padding:8px 12px;border-radius:4px;"></div>
                </div>
            </div>

            <p style="margin-top:18px;font-size:12px;color:#aaa;">
                Need help? <a href="tel:<?php echo htmlspecialchars($company_phone); ?>" style="color:#1a5276;"><?php echo htmlspecialchars($company_phone); ?></a>
            </p>
        </div>
    </div>

    <!-- ═══════════════ SCHEDULE PICKUP ═══════════════ -->
    <div class="tab-panel<?php echo $active_tab === 'pickup' ? ' active' : ''; ?>" id="panel-pickup">
        <div class="card">
            <h2><i class="fas fa-truck-pickup"></i> Schedule Delivery / Request Bike Delivery</h2>
            <div class="alert alert-success" id="pickup-success">Your pickup request was successfully submitted, we will get back to you. In case of a question, reach out to <?php echo htmlspecialchars($company_phone); ?> or email <?php echo htmlspecialchars($company_email); ?>.</div>
            <div class="alert alert-danger"  id="pickup-error">Please fill in all required fields.</div>

            <form id="public-pickup-form">
                <input type="hidden" name="csrf_token_name" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <h4 style="margin-bottom:14px; font-size:15px; color:#444;">Contact Person</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" name="contact_first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" name="contact_last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number <span style="color:red">*</span></label>
                        <input type="tel" class="form-control" name="contact_phone" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address <span style="color:red">*</span></label>
                        <input type="email" class="form-control" name="contact_email" required>
                    </div>
                </div>

                <h4 style="margin:20px 0 14px; font-size:15px; color:#444;">Pickup Details</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Pickup Date <span style="color:red">*</span></label>
                        <input type="date" class="form-control" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Preferred Time Window</label>
                        <select class="form-control" name="time_window">
                            <option value="08:00-12:00">08:00 – 12:00</option>
                            <option value="12:00-16:00">12:00 – 16:00</option>
                            <option value="16:00-20:00">16:00 – 20:00</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Pickup Address <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="pickup_address" placeholder="Street address, city, country" required>
                </div>
                <div class="form-group">
                    <label>Package Description</label>
                    <textarea class="form-control" name="package_description" rows="3" placeholder="Briefly describe the items to be picked up…"></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Submit Pickup Request</button>
            </form>
        </div>
    </div>

    <!-- ═══════════════ FIND SERVICE POINT ═══════════════ -->
    <div class="tab-panel<?php echo $active_tab === 'service' ? ' active' : ''; ?>" id="panel-service">
        <div class="card">
            <h2><i class="fas fa-map-marker-alt"></i> Find a Service Point</h2>
            <?php if (!empty($service_points)): ?>
                <p style="color:#666;font-size:13px;margin-bottom:20px;">
                    We serve the following locations. Contact us for the specific address and opening hours at each stop.
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <?php foreach ($service_points as $sp): ?>
                    <div style="background:#f0fff4;border:1px solid #c3e6cb;border-radius:8px;padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#1b5e20;">
                        <i class="fas fa-map-marker-alt" style="color:#28a745;"></i>
                        <?php echo htmlspecialchars($sp['name']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($company_phone): ?>
                <p style="margin-top:20px;font-size:13px;color:#666;">
                    <i class="fas fa-phone" style="color:#28a745;"></i>
                    Call us at <a href="tel:<?php echo htmlspecialchars($company_phone); ?>" style="color:#28a745;font-weight:600;"><?php echo htmlspecialchars($company_phone); ?></a> for directions or delivery queries.
                </p>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align:center; padding:40px; color:#999;">
                    <i class="fas fa-map" style="font-size:48px; margin-bottom:16px;"></i>
                    <p>Service point information will be listed here. Please contact us for the nearest location.</p>
                    <?php if ($company_phone): ?>
                    <p style="margin-top:12px;"><a href="tel:<?php echo htmlspecialchars($company_phone); ?>" class="btn btn-primary"><i class="fas fa-phone"></i> Call Us</a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════ CREATE SHIPMENT ═══════════════ -->
    <div class="tab-panel<?php echo $active_tab === 'shipment' ? ' active' : ''; ?>" id="panel-shipment">
        <div class="card">
            <h2><i class="fas fa-box"></i> Create a Shipment Request</h2>
            <div class="alert alert-success" id="shipment-success">Your shipment request was successfully submitted, we will get back to you. In case of a question, reach out to <?php echo htmlspecialchars($company_phone); ?> or email <?php echo htmlspecialchars($company_email); ?>.</div>
            <div class="alert alert-danger"  id="shipment-error">Please fill in all required fields.</div>

            <form id="public-shipment-form">
                <input type="hidden" name="csrf_token_name" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <h4 style="margin-bottom:14px; font-size:15px; color:#444;">Sender Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sender Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" name="sender_name" required>
                    </div>
                    <div class="form-group">
                        <label>Sender Phone <span style="color:red">*</span></label>
                        <input type="tel" class="form-control" name="sender_phone" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sender Email</label>
                    <input type="email" class="form-control" name="sender_email">
                </div>
                <div class="form-group">
                    <label>Sender Address <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="sender_address" placeholder="Street, city, country" required>
                </div>

                <h4 style="margin:20px 0 14px; font-size:15px; color:#444;">Recipient Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Recipient Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" name="recipient_name" required>
                    </div>
                    <div class="form-group">
                        <label>Recipient Phone <span style="color:red">*</span></label>
                        <input type="tel" class="form-control" name="recipient_phone" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Recipient Email</label>
                    <input type="email" class="form-control" name="recipient_email">
                </div>
                <div class="form-group">
                    <label>Recipient Address <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="recipient_address" placeholder="Street, city, country" required>
                </div>

                <h4 style="margin:20px 0 14px; font-size:15px; color:#444;">Shipment Details</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Shipping Mode</label>
                        <select class="form-control" name="shipping_mode">
                            <option value="Air Freight">Air Freight</option>
                            <option value="Sea LCL">Sea LCL</option>
                            <option value="Sea FCL">Sea FCL</option>
                            <option value="Ground" selected>Ground / Courier</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-control">
                            <option value="">-- Select Vehicle Type --</option>
                            <option value="bike">Motorcycle / Bike</option>
                            <option value="tuk_tuk">Tuk Tuk / Three-Wheeler</option>
                            <option value="van">Van / Minivan</option>
                            <option value="pickup">Pickup Truck</option>
                            <option value="truck">Truck</option>
                            <option value="lorry">Lorry / Large Truck</option>
                            <option value="flatbed">Flatbed Truck</option>
                            <option value="refrigerated">Refrigerated Truck</option>
                            <option value="boat">Cargo Boat</option>
                            <option value="air">Air Freight</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Package Description <span style="color:red">*</span></label>
                    <textarea class="form-control" name="package_description" rows="3" placeholder="Describe the items being shipped…" required></textarea>
                </div>
                <div class="form-group">
                    <label>Special Instructions</label>
                    <textarea class="form-control" name="special_instructions" rows="2" placeholder="Fragile, handle with care, etc."></textarea>
                </div>
                <div class="form-group">
                    <label>Commercial Invoice</label>
                    <input type="file" class="form-control" name="commercial_invoice" accept=".pdf,.doc,.docx,.jpg,.png">
                    <small style="color:#666;">Attach a commercial invoice if available.</small>
                </div>

                <!-- ── Quote Calculator ─────────────────────────────────── -->
                <div style="background:#f0f7ff;border:1px solid #b3d1f7;border-radius:8px;padding:18px 20px;margin:20px 0 16px;">
                    <h4 style="margin:0 0 14px;font-size:15px;color:#1565c0;"><i class="fas fa-calculator"></i> Get a Price Estimate</h4>
                    <p style="font-size:12px;color:#666;margin-bottom:14px;">Fill in package details below and click <strong>Get Quote</strong> to see an estimated cost before submitting.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:13px;">Cargo Type <span style="color:red">*</span></label>
                            <select id="ship_cargo_type" name="cargo_type" class="form-control">
                                <option value="parcel">Parcel / Package</option>
                                <option value="document">Document / Envelope</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-size:13px;">Delivery Zone <span style="color:red">*</span></label>
                            <select id="ship_zone" name="zone_code" class="form-control">
                                <option value="">-- Select Delivery Zone --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label style="font-size:13px;">Weight (kg)</label>
                            <input type="number" id="ship_weight" name="weight" class="form-control" placeholder="0.0" min="0" step="0.1">
                        </div>
                        <div class="form-group" id="ship_dims_wrap">
                            <label style="font-size:13px;">Dimensions (cm) — L × W × H</label>
                            <div style="display:flex;gap:6px;">
                                <input type="number" id="ship_length" name="length" class="form-control" placeholder="L" min="0" step="0.1" style="flex:1;">
                                <input type="number" id="ship_width"  name="width"  class="form-control" placeholder="W" min="0" step="0.1" style="flex:1;">
                                <input type="number" id="ship_height" name="height" class="form-control" placeholder="H" min="0" step="0.1" style="flex:1;">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="ship_btn_get_quote"
                            style="background:#1565c0;color:#fff;border:none;border-radius:6px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-calculator"></i> Get Quote
                    </button>

                    <!-- Quote result panel -->
                    <div id="ship_quote_result" style="display:none;margin-top:16px;border:1px solid #c3e6cb;border-radius:6px;background:#f1fdf4;padding:14px 18px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                            <div>
                                <div style="font-size:12px;color:#555;" id="ship_qr_zone"></div>
                                <div style="font-size:12px;color:#555;" id="ship_qr_weight"></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:12px;color:#555;">Transport: <strong id="ship_qr_transport"></strong></div>
                                <div style="font-size:12px;color:#555;">Handling: <strong id="ship_qr_handling"></strong></div>
                                <div style="font-size:12px;color:#555;">VAT (<span id="ship_qr_vat_rate"></span>%): <strong id="ship_qr_vat"></strong></div>
                                <div style="font-size:16px;font-weight:700;color:#2e7d32;margin-top:4px;">Total Estimate: <span id="ship_qr_total"></span></div>
                            </div>
                        </div>
                        <p style="font-size:11px;color:#888;margin:10px 0 0;">This is an estimate. The final invoice may vary based on actual weight and admin review.</p>
                    </div>
                    <div id="ship_quote_error" style="display:none;margin-top:12px;padding:10px 14px;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;font-size:13px;color:#856404;"></div>
                </div>

                <!-- Hidden fields carrying quote data to the server -->
                <input type="hidden" id="h_quoted_amount" name="quoted_amount" value="0">

                <button type="submit" class="btn btn-primary" style="margin-top:4px;"><i class="fas fa-paper-plane"></i> Submit Shipment Request</button>
                <p style="font-size:11px;color:#888;margin-top:8px;"><i class="fas fa-info-circle"></i> Your request will be reviewed by our team. You'll receive a confirmation with your official tracking number.</p>
            </form>
        </div>
    </div>

    <!-- ═══════════════ CALL FOR BOOKING ═══════════════ -->
    <div class="tab-panel<?php echo $active_tab === 'call' ? ' active' : ''; ?>" id="panel-call">
        <div class="card call-card">
            <div class="call-icon"><i class="fas fa-phone-alt"></i></div>
            <h2>Call for Booking</h2>
            <p>Speak directly with our team to book a shipment, get a quote, or arrange a pickup.</p>
            <?php if ($company_phone): ?>
            <a href="tel:<?php echo htmlspecialchars($company_phone); ?>" class="phone-number">
                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($company_phone); ?>
            </a>
            <?php else: ?>
            <p style="color:#999;">Phone number not configured. Please contact the administrator.</p>
            <?php endif; ?>
            <br><br>
            <p style="color:#666; font-size:14px;">Our team is available during business hours to assist you.</p>
        </div>
    </div>

</div><!-- /.portal-content -->

<div class="portal-footer">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($logistic_company); ?>. All rights reserved.
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    // ── Tab switching ──
    const tabUrls = <?php echo json_encode($tab_urls); ?>;

    function showTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + name).classList.add('active');
        document.getElementById('tab-' + name).classList.add('active');
    }

    // Switch tabs in-place (no full page reload) while keeping the URL bookmarkable
    function switchTab(e, name) {
        e.preventDefault();
        showTab(name);
        if (tabUrls[name] && location.href !== tabUrls[name]) {
            history.pushState({ tab: name }, '', tabUrls[name]);
        }
        return false;
    }

    window.addEventListener('popstate', function(e) {
        const name = (e.state && e.state.tab) || '<?php echo $active_tab; ?>';
        showTab(name);
    });

    // Legacy hash deep-links (e.g. #quote) still work
    (function() {
        const map = { '#track':'track','#quote':'quote','#pickup':'pickup','#service':'service','#shipment':'shipment','#call':'call' };
        if (map[location.hash]) showTab(map[location.hash]);
    })();

    // ── Dimensional factor data ──
    const dimFactors = {
        air:    <?php echo $dim_factors['air'] ?? 6000; ?>,
        sea:    <?php echo $dim_factors['sea'] ?? 1000; ?>,
        ground: <?php echo $dim_factors['ground'] ?? 5000; ?>
    };
    const svcDimFactor = {
        domestic:          dimFactors.ground,
        road:              dimFactors.ground,
        courier:           dimFactors.ground,
        lcl:               dimFactors.sea,
        consolidation:     dimFactors.sea,
        air_freight:       dimFactors.air,
        air_consolidation: dimFactors.air,
        fcl:               0
    };

    var IS_INTERNATIONAL = <?php echo $is_international ? 'true' : 'false'; ?>;
    var FCL_RATES        = <?php echo $fcl_rates_json; ?>;

    // ── Quote Calculator state ──
    var _quoteData      = null;
    var _allCountries   = [];   // [{id, name, iso2}, ...]
    var _allCities      = [];   // string[]
    var _currentService = '';
    var _originSelected = '';
    var _domesticCities       = [];   // string[]
    var _originCitySelected   = '';
    var _destCitySelected     = '';

    var QUOTE_CALC_URL    = '<?php echo base_url("courier/portal/calculate_quote"); ?>';
    var QUOTE_EMAIL_URL   = '<?php echo base_url("courier/portal/send_quote_email"); ?>';
    var COUNTRIES_URL     = '<?php echo base_url("courier/portal/get_countries"); ?>';
    var CITIES_URL        = '<?php echo base_url("courier/portal/get_cities"); ?>';
    var DOMESTIC_CITIES_URL = '<?php echo base_url("courier/portal/get_domestic_cities"); ?>';
    var CSRF_TOKEN_NAME   = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var CSRF_TOKEN_HASH   = '<?php echo $this->security->get_csrf_hash(); ?>';

    // Zones bootstrapped from PHP
    var TARIFF_ZONES = <?php echo json_encode($tariff_zones ?? []); ?>;

    // Populate zone dropdowns (both local #q_zone and international #q_zone in auto-wrap)
    (function initZones() {
        var sel = document.getElementById('q_zone');
        if (!sel) return;
        sel.innerHTML = '<option value="">-- Select rate zone --</option>';
        TARIFF_ZONES.forEach(function(z) {
            var opt = document.createElement('option');
            opt.value       = z.zone_code.toLowerCase();
            opt.textContent = z.name;
            opt.dataset.dest  = z.destinations || '';
            opt.dataset.avail = z.is_available;
            sel.appendChild(opt);
        });
        if (!TARIFF_ZONES.length) sel.innerHTML = '<option value="">-- No zones configured --</option>';
        sel.addEventListener('change', function() {
            _quoteData = null;
            document.getElementById('quote-result-panel').style.display = 'none';
        });
    })();

    // For local courier (no service-type step), show cargo section immediately
    (function initLocalCourier() {
        if (IS_INTERNATIONAL) return;
        var cargoSec = document.getElementById('q-cargo-section');
        if (cargoSec) cargoSec.style.display = 'block';
    })();

    // ── Load countries from server and populate both origin and destination selects ──
    (function loadCountries() {
        if (!IS_INTERNATIONAL) return;
        var destSel   = document.getElementById('q_country_select');
        var originSel = document.getElementById('q_origin_select');
        if (!destSel && !originSel) return;
        fetch(COUNTRIES_URL)
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'success' && res.data) {
                    _allCountries = res.data;
                    [destSel, originSel].forEach(function(sel) {
                        if (!sel) return;
                        var placeholder = sel.id === 'q_origin_select'
                            ? '-- Select origin country --'
                            : '-- Select destination country --';
                        sel.innerHTML = '<option value="">' + placeholder + '</option>';
                        _allCountries.forEach(function(c) {
                            var opt = document.createElement('option');
                            opt.value       = c.name;
                            opt.textContent = c.name;
                            sel.appendChild(opt);
                        });
                    });
                    // Default origin to Kenya
                    if (originSel) {
                        originSel.value = 'Kenya';
                        selectOrigin('Kenya');
                    }
                }
            })
            .catch(function(){
                if (destSel)   destSel.innerHTML   = '<option value="">-- Could not load countries --</option>';
                if (originSel) originSel.innerHTML = '<option value="">-- Could not load countries --</option>';
            });
    })();

    // ── Load domestic (within-country) cities for the Domestic + International-mode city pickers ──
    (function loadDomesticCities() {
        if (!IS_INTERNATIONAL) return;
        var originSel = document.getElementById('q_origin_city_select');
        var destSel   = document.getElementById('q_dest_city_select');
        if (!originSel && !destSel) return;
        fetch(DOMESTIC_CITIES_URL)
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'success' && res.data) {
                    _domesticCities = res.data.map(function(c){ return c.name; });
                    [originSel, destSel].forEach(function(sel) {
                        if (!sel) return;
                        var placeholder = sel.id === 'q_origin_city_select'
                            ? '-- Select origin city --'
                            : '-- Select destination city --';
                        sel.innerHTML = '<option value="">' + placeholder + '</option>';
                        _domesticCities.forEach(function(name) {
                            var opt = document.createElement('option');
                            opt.value       = name;
                            opt.textContent = name;
                            sel.appendChild(opt);
                        });
                    });
                }
            })
            .catch(function(){
                if (originSel) originSel.innerHTML = '<option value="">-- Could not load cities --</option>';
                if (destSel)   destSel.innerHTML   = '<option value="">-- Could not load cities --</option>';
            });
    })();

    function selectOriginCity(name) {
        _originCitySelected = name;
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
    }

    function selectDestCity(name) {
        _destCitySelected = name;
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
    }

    function selectOrigin(name) {
        _originSelected = name;
        document.getElementById('q_origin_selected').value = name;
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
    }

    function selectCountry(name) {
        document.getElementById('q_country_selected').value = name;

        // Reset city select
        var citySel = document.getElementById('q_city_select');
        if (citySel) citySel.innerHTML = '<option value="">-- Select city / town --</option>';
        document.getElementById('q_city_selected').value = '';

        if (!name) return;

        // Auto-detect zone from country name
        autoDetectZone(name, '');

        // Load cities for this country
        loadCities(name);

        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
    }

    function loadCities(country) {
        var loading = document.getElementById('q_city_loading');
        var citySel = document.getElementById('q_city_select');
        if (loading) loading.style.display = 'block';
        if (citySel) citySel.disabled = true;
        document.getElementById('q_city_selected').value = '';

        var fd = new FormData();
        fd.append('country', country);
        fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);

        fetch(CITIES_URL, { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (loading) loading.style.display = 'none';
                _allCities = (res.status === 'success' && res.data) ? res.data : [];
                renderCityList();
                if (citySel) citySel.disabled = false;
            })
            .catch(function(){
                if (loading) loading.style.display = 'none';
                if (citySel) {
                    citySel.innerHTML = '<option value="">-- Could not load cities --</option>';
                    citySel.disabled  = false;
                }
            });
    }

    function renderCityList() {
        var sel = document.getElementById('q_city_select');
        if (!sel) return;
        sel.innerHTML = '<option value="">-- Select city / town --</option>';
        _allCities.forEach(function(city) {
            var opt = document.createElement('option');
            opt.value       = city;
            opt.textContent = city;
            sel.appendChild(opt);
        });
        if (!_allCities.length) {
            var opt = document.createElement('option');
            opt.value    = '';
            opt.textContent = 'No city data available for this country';
            opt.disabled = true;
            sel.appendChild(opt);
        }
    }

    function selectCity(city) {
        document.getElementById('q_city_selected').value = city;

        // Try zone auto-detect with city + country
        var country = document.getElementById('q_country_selected').value;
        if (city) autoDetectZone(country, city);

        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
    }

    /**
     * Try to match country/city name against zone destinations text.
     * If found, auto-select the zone and show a note.
     */
    function autoDetectZone(country, city) {
        var sel  = document.getElementById('q_zone');
        var note = document.getElementById('q_zone_auto_note');
        if (!sel) return;

        var search = (city + ' ' + country).toLowerCase();
        var matched = null;
        TARIFF_ZONES.forEach(function(z) {
            if (matched) return;
            var dest = (z.destinations || '').toLowerCase();
            if (dest && (dest.indexOf(country.toLowerCase()) !== -1 || (city && dest.indexOf(city.toLowerCase()) !== -1))) {
                matched = z;
            }
        });

        if (matched) {
            sel.value = matched.zone_code.toLowerCase();
            if (note) {
                note.textContent = '✓ Auto-detected: ' + matched.name;
                note.style.color = '#28a745';
            }
        } else {
            // Don't clear — let user keep a previous selection
            if (note) {
                note.textContent = country ? 'Not auto-detected — please select manually' : '';
                note.style.color = '#aaa';
            }
        }
    }

    // ── Service type selection (international mode only) ──
    var SERVICE_LABELS = {
        domestic:          'Domestic',
        road:              'Road Freight',
        fcl:               'FCL (Full Container)',
        lcl:               'Sea LCL',
        consolidation:     'Sea Consolidation',
        air_freight:       'Air Freight',
        air_consolidation: 'Air Consolidation',
        courier:           'Courier'
    };

    function selectServiceType(svc) {
        _currentService = svc;
        document.getElementById('q_service_type').value = svc;

        // Highlight selected service card
        Object.keys(SERVICE_LABELS).forEach(function(s) {
            var card = document.getElementById('qsvc-' + s);
            if (!card) return;
            var active = (s === svc);
            card.style.borderColor = active ? '#1a5276' : '#ddd';
            card.style.background  = active ? '#eaf0fb' : '#fff';
            card.querySelector('i').style.color   = active ? '#1a5276' : '#888';
            card.querySelector('span').style.color = active ? '#1a5276' : '#555';
        });

        var fclSection   = document.getElementById('q-fcl-section');
        var destSection  = document.getElementById('q-destination-section'); // country+city (non-domestic international)
        var zoneWrap     = document.getElementById('q-zone-auto-wrap') || document.getElementById('q-zone-section'); // zone select (domestic + non-FCL international)
        var domesticCitySec = document.getElementById('q-domestic-city-section'); // origin/dest city picker (domestic, international mode only)
        var cargoSec     = document.getElementById('q-cargo-section');
        var weightSec    = document.getElementById('q-weight-section');
        var dimsNote     = document.getElementById('q-dim-factor-note');
        var volFormula   = document.getElementById('q-vol-formula');

        // Services that require country+city picker (NOT domestic, NOT FCL)
        var intlDestServices = ['road','lcl','consolidation','air_freight','air_consolidation','courier'];

        if (svc === 'fcl') {
            fclSection      && (fclSection.style.display      = 'block');
            destSection     && (destSection.style.display     = 'none');
            zoneWrap        && (zoneWrap.style.display        = 'none');
            domesticCitySec && (domesticCitySec.style.display = 'none');
            cargoSec        && (cargoSec.style.display        = 'none');
            weightSec       && (weightSec.style.display       = 'none');
        } else if (svc === 'domestic') {
            fclSection  && (fclSection.style.display  = 'none');
            destSection && (destSection.style.display = 'none');  // no country+city for domestic
            zoneWrap        && (zoneWrap.style.display        = 'block');
            domesticCitySec && (domesticCitySec.style.display = 'none');
            cargoSec        && (cargoSec.style.display        = 'block');
            var df = svcDimFactor[svc] || 5000;
            if (dimsNote)   dimsNote.textContent  = 'Dimensional factor for ' + (SERVICE_LABELS[svc] || svc) + ': ' + df.toLocaleString() + ' (Volumetric = L×W×H ÷ ' + df.toLocaleString() + ')';
            if (volFormula) volFormula.textContent = 'Volumetric weight = L × W × H ÷ ' + df.toLocaleString() + '. Chargeable weight = max(actual, volumetric).';
            var cargoType = document.getElementById('q_cargo_type').value;
            weightSec && (weightSec.style.display = cargoType === 'parcel' ? 'block' : 'none');
            // Clear auto-detect note since no country is selected
            var note = document.getElementById('q_zone_auto_note');
            if (note) { note.textContent = ''; }
        } else {
            // Road, LCL, Consolidation, Air Freight, Air Consolidation, Courier
            fclSection      && (fclSection.style.display      = 'none');
            destSection     && (destSection.style.display     = 'block');  // show country+city
            zoneWrap        && (zoneWrap.style.display        = 'none');   // hide zone manual select for international
            domesticCitySec && (domesticCitySec.style.display = 'none');
            cargoSec        && (cargoSec.style.display        = 'block');
            var df = svcDimFactor[svc] || 6000;
            if (dimsNote)   dimsNote.textContent  = 'Dimensional factor for ' + (SERVICE_LABELS[svc] || svc) + ': ' + df.toLocaleString() + ' (Volumetric = L×W×H ÷ ' + df.toLocaleString() + ')';
            if (volFormula) volFormula.textContent = 'Volumetric weight = L × W × H ÷ ' + df.toLocaleString() + '. Chargeable weight = max(actual, volumetric).';
            var cargoType = document.getElementById('q_cargo_type').value;
            weightSec && (weightSec.style.display = cargoType === 'parcel' ? 'block' : 'none');
        }

        // Reset dependent fields
        var cntEl = document.getElementById('q_container');
        if (cntEl) cntEl.value = '';
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
        document.getElementById('quote-error-msg').style.display    = 'none';
    }

    // ── Container type selection (FCL) ──
    function selectContainer(key) {
        document.getElementById('q_container').value = key;
        ['20dv','40dv','20hc','40hc','20rf','40rf','20fr','40fr','roro'].forEach(function(k) {
            var card = document.getElementById('qcnt-' + k);
            if (!card) return;
            card.style.borderColor = (k === key) ? '#1a5276' : '#ddd';
            card.style.background  = (k === key) ? '#eaf0fb' : '#fff';
        });
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
        document.getElementById('quote-error-msg').style.display    = 'none';
    }

    // ── Cargo type cards ──
    function selectCargoType(type) {
        document.getElementById('q_cargo_type').value = type;
        var docCard  = document.getElementById('qcard-document');
        var parCard  = document.getElementById('qcard-parcel');
        var wSec     = document.getElementById('q-weight-section');
        var isDoc    = (type === 'document');
        docCard.style.borderColor = isDoc  ? '#1a5276' : '#ddd';
        docCard.style.background  = isDoc  ? '#eaf0fb' : '#fff';
        docCard.querySelector('i').style.color    = isDoc  ? '#1a5276' : '#555';
        docCard.querySelector('span').style.color = isDoc  ? '#1a5276' : '#555';
        parCard.style.borderColor = !isDoc ? '#1a5276' : '#ddd';
        parCard.style.background  = !isDoc ? '#eaf0fb' : '#fff';
        parCard.querySelector('i').style.color    = !isDoc ? '#1a5276' : '#555';
        parCard.querySelector('span').style.color = !isDoc ? '#1a5276' : '#555';
        wSec.style.display = isDoc ? 'none' : 'block';
        _quoteData = null;
        document.getElementById('quote-result-panel').style.display = 'none';
        document.getElementById('quote-error-msg').style.display    = 'none';
    }

    function toggleDims(e) {
        e.preventDefault();
        var sec = document.getElementById('q-dims-section');
        sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
    }

    function fmtKes(n) { return 'KES ' + Number(n).toLocaleString('en-KE', {minimumFractionDigits:2,maximumFractionDigits:2}); }

    function runCalculateQuote() {
        var errEl = document.getElementById('quote-error-msg');
        errEl.style.display = 'none';

        var serviceType   = (document.getElementById('q_service_type') || {}).value || '';
        var cargoType     = document.getElementById('q_cargo_type').value;
        var zone          = (document.getElementById('q_zone') || {}).value || '';
        var weight        = parseFloat((document.getElementById('q_weight') || {}).value)  || 0;
        var length        = parseFloat((document.getElementById('q_length') || {}).value)  || 0;
        var width         = parseFloat((document.getElementById('q_width')  || {}).value)  || 0;
        var height        = parseFloat((document.getElementById('q_height') || {}).value)  || 0;
        var container     = (document.getElementById('q_container') || {}).value || '';
        var country       = (document.getElementById('q_country_selected') || {}).value || '';
        var city          = (document.getElementById('q_city_selected') || {}).value || '';
        var originCountry = (document.getElementById('q_origin_selected') || {}).value || '';
        var isDomesticIntl = IS_INTERNATIONAL && serviceType === 'domestic';
        var originCity    = isDomesticIntl ? (document.getElementById('q_origin_city_select') || {}).value || '' : '';
        var destCity      = isDomesticIntl ? (document.getElementById('q_dest_city_select')   || {}).value || '' : '';

        var contactName    = (document.getElementById('q_contact_name') || {}).value || '';
        var contactCompany = (document.getElementById('q_contact_company') || {}).value || '';
        var contactEmail   = (document.getElementById('q_contact_email') || {}).value || '';
        var contactPhone   = (document.getElementById('q_contact_phone') || {}).value || '';

        // Validate
        if (!contactName.trim() || !contactCompany.trim() || !contactEmail.trim() || !contactPhone.trim()) {
            errEl.textContent = 'Please fill in all contact details (Name, Company, Email, Phone).'; errEl.style.display = 'block'; return;
        }

        if (IS_INTERNATIONAL && !serviceType) {
            errEl.textContent = 'Please select a service type.'; errEl.style.display = 'block'; return;
        }
        if (!cargoType) {
            errEl.textContent = 'Please select a cargo type (Document or Package / Parcel).'; errEl.style.display = 'block'; return;
        }
        if (serviceType === 'fcl') {
            if (!container) { errEl.textContent = 'Please select a container type.'; errEl.style.display = 'block'; return; }
        } else {
            if (IS_INTERNATIONAL && serviceType !== 'domestic') {
                if (!originCountry) { errEl.textContent = 'Please select an origin country.'; errEl.style.display = 'block'; return; }
                if (!country)       { errEl.textContent = 'Please select a destination country.'; errEl.style.display = 'block'; return; }
            }
            if ((serviceType === 'domestic' || !serviceType) && !zone) {
                errEl.textContent = 'Please select a destination zone.'; errEl.style.display = 'block'; return;
            }
            if (cargoType === 'parcel' && weight <= 0) {
                errEl.textContent = 'Please enter a valid weight.'; errEl.style.display = 'block'; return;
            }
        }

        var btn = document.getElementById('q-calc-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';

        var fd = new FormData();
        fd.append('cargo_type',      cargoType);
        fd.append('zone',            zone);
        fd.append('weight',          weight);
        fd.append('length',          length);
        fd.append('width',           width);
        fd.append('height',          height);

        fd.append('contact_name',    contactName);
        fd.append('contact_company', contactCompany);
        fd.append('contact_email',   contactEmail);
        fd.append('contact_phone',   contactPhone);

        fd.append('service_type',    serviceType);
        fd.append('container',       container);
        fd.append('country',         country);
        fd.append('city',            city);
        fd.append('origin_country',  originCountry);
        fd.append('origin_city',     originCity);
        fd.append('destination_city', destCity);
        fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);

        fetch(QUOTE_CALC_URL, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-calculator"></i> Calculate Quote';
                if (res.status !== 'success') {
                    errEl.textContent = res.message || 'Could not calculate quote.';
                    errEl.style.display = 'block';
                    document.getElementById('quote-result-panel').style.display = 'none';
                    return;
                }
                // Augment data with origin/country/city for display & email
                res.data.origin_country = originCountry;
                res.data.country        = country;
                res.data.city           = city;
                res.data.origin_city      = originCity;
                res.data.destination_city = destCity;
                _quoteData = res.data;
                var d = res.data;
                var isFCL = d.service_type === 'fcl';

                // Service row
                var svcRow = document.getElementById('qr_service_row');
                if (d.service_type && SERVICE_LABELS[d.service_type]) {
                    document.getElementById('qr_service').textContent = SERVICE_LABELS[d.service_type];
                    svcRow.style.display = '';
                } else { svcRow.style.display = 'none'; }

                // Cargo row
                var cargoRow = document.getElementById('qr_cargo_row');
                if (!isFCL) {
                    document.getElementById('qr_cargo').textContent = d.cargo_type.charAt(0).toUpperCase() + d.cargo_type.slice(1);
                    cargoRow.style.display = '';
                } else { cargoRow.style.display = 'none'; }

                // Container row (FCL only)
                var cntRow = document.getElementById('qr_container_row');
                if (isFCL && d.container_label) {
                    document.getElementById('qr_container').textContent = d.container_label;
                    cntRow.style.display = '';
                } else { cntRow.style.display = 'none'; }

                // Destination row — show origin → destination for international freight / domestic city pairs
                var destLabel = isFCL ? 'FCL Shipment' : d.zone_name;
                if (!isFCL && originCity && destCity) {
                    destLabel = originCity + ' → ' + destCity;
                } else if (!isFCL && (originCountry || country || city)) {
                    var destPart = [city, country].filter(Boolean).join(', ');
                    if (originCountry && destPart) {
                        destLabel = originCountry + ' → ' + destPart;
                        if (d.zone_name && d.zone_name !== 'Standard International') destLabel += ' (' + d.zone_name + ')';
                    } else if (destPart) {
                        destLabel = destPart + ' (' + d.zone_name + ')';
                    }
                }
                document.getElementById('qr_zone').textContent = destLabel;

                // Weight rows (hidden for FCL)
                var wtRows = document.getElementById('qr_weight_rows');
                if (!isFCL) {
                    document.getElementById('qr_actual_wt').textContent = d.actual_weight + ' kg';
                    document.getElementById('qr_charge_wt').textContent = d.chargeable_weight + ' kg';
                    var volRow = document.getElementById('qr_vol_row');
                    if (d.volumetric_weight > 0) {
                        document.getElementById('qr_vol_wt').textContent = d.volumetric_weight + ' kg';
                        volRow.style.display = '';
                    } else { volRow.style.display = 'none'; }
                    wtRows.style.display = '';
                } else { wtRows.style.display = 'none'; }

                document.getElementById('qr_transport').textContent = fmtKes(d.transport);
                document.getElementById('qr_handling').textContent  = fmtKes(d.handling);
                document.getElementById('qr_subtotal').textContent  = fmtKes(d.subtotal);
                document.getElementById('qr_vat').textContent       = fmtKes(d.vat_amount);
                document.getElementById('qr_vat_label').textContent = d.vat_rate;
                document.getElementById('qr_total').textContent     = fmtKes(d.total);

                document.getElementById('quote-result-panel').style.display = 'block';
                document.getElementById('q-email-alert').style.display = 'none';
                document.getElementById('q_email').value = '';
                document.getElementById('quote-result-panel').scrollIntoView({behavior:'smooth',block:'nearest'});
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-calculator"></i> Calculate Quote';
                errEl.textContent = 'Network error. Please try again.';
                errEl.style.display = 'block';
            });
    }

    function sendQuoteEmail() {
        if (!_quoteData) { alert('Please calculate a quote first.'); return; }
        var email = document.getElementById('q_email').value.trim();
        if (!email || !email.includes('@')) {
            document.getElementById('q-email-alert').style.cssText = 'display:block;background:#fdecea;color:#c62828;padding:8px 12px;border-radius:4px;';
            document.getElementById('q-email-alert').textContent = 'Please enter a valid email address.';
            return;
        }
        var btn = document.getElementById('q-email-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        var fd = new FormData();
        fd.append('email', email);
        fd.append('quote_data', JSON.stringify(_quoteData));
        fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
        fetch(QUOTE_EMAIL_URL, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-envelope"></i> Email Me This Quote';
                var alertEl = document.getElementById('q-email-alert');
                if (res.status === 'success') {
                    alertEl.style.cssText = 'display:block;background:#e8f5e9;color:#1b5e20;padding:8px 12px;border-radius:4px;';
                    alertEl.textContent = res.message;
                } else {
                    alertEl.style.cssText = 'display:block;background:#fdecea;color:#c62828;padding:8px 12px;border-radius:4px;';
                    alertEl.textContent = res.message || 'Failed to send email.';
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-envelope"></i> Email Me This Quote';
                var alertEl = document.getElementById('q-email-alert');
                alertEl.style.cssText = 'display:block;background:#fdecea;color:#c62828;padding:8px 12px;border-radius:4px;';
                alertEl.textContent = 'Network error. Please try again.';
            });
    }

    // ── Tracking ──
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#get_shipment_btn').on('click', function () {
        trackShipment($('#tracking_number').val().trim());
    });

    $('#tracking_number').on('keypress', function (e) {
        if (e.which === 13) trackShipment($(this).val().trim());
    });

    // Auto-track if ?track= is present in the URL (e.g. from email link)
    (function () {
        var params = new URLSearchParams(window.location.search);
        var autoTrack = params.get('track');
        if (autoTrack) {
            $('#tracking_number').val(autoTrack);
            trackShipment(autoTrack);
        }
    })();

    function fmtDate(dt) {
        if (!dt) return '';
        // Replace '-' with '/' for Safari and Firefox date parsing compatibility
        const safeDt = dt.replace(/-/g, '/');
        const dateObj = new Date(safeDt);
        if (isNaN(dateObj.getTime())) {
            return dt; // Fallback to raw string if parsing fails
        }
        return dateObj.toLocaleString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'numeric', minute:'numeric', hour12:true });
    }

    function trackShipment(trackingNumber) {
        if (!trackingNumber) return;
        $('#get_shipment_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Tracking…');
        $('#track-result').hide();
        $('#track-empty').hide();

        $.ajax({
            url: '<?php echo base_url("courier/tracking/shipment_info"); ?>',
            type: 'POST',
            data: { tracking_number: trackingNumber },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function (data) {
                $('#get_shipment_btn').prop('disabled', false).html('<i class="fas fa-search"></i> Track');

                if (!data || data.status !== 'success') {
                    $('#track-empty').show();
                    return;
                }

                const d = data.data ? data.data.shipment_details : null;
                const statuses = data.data ? data.data.statuses : null;
                const shipment = d ? d.shipment : null;

                if (!shipment) {
                    $('#track-empty').show();
                    return;
                }

                // Status badge
                let statusLabel = '';
                if (Array.isArray(statuses)) {
                    statuses.forEach(s => { if (s.id == shipment.status_id) statusLabel = s.description; });
                }
                $('#status-badge-wrap').html('<span class="status-badge"><i class="fas fa-circle" style="font-size:9px;margin-right:6px;"></i>' + statusLabel + '</span>');

                // Sender/recipient
                if (d.sender) {
                    if (d.sender_type === 'individual') {
                        $('#sender_name').text((d.sender.first_name || '') + ' ' + (d.sender.last_name || ''));
                        $('#sender_phone').text(d.sender.phone_number || '');
                        $('#sender_address').text((d.sender.address || '') + (d.sender.zipcode ? ', ' + d.sender.zipcode : ''));
                    } else {
                        $('#sender_name').text(d.sender.contact_person_name || '');
                        $('#sender_phone').text(d.sender.contact_person_phone_number || '');
                        $('#sender_address').text((d.sender.contact_address || '') + (d.sender.contact_zipcode ? ', ' + d.sender.contact_zipcode : ''));
                    }
                } else {
                    $('#sender_name').text('N/A');
                    $('#sender_phone').text('N/A');
                    $('#sender_address').text('N/A');
                }

                if (d.recipient) {
                    if (d.recipient_type === 'individual') {
                        $('#recipient_name').text((d.recipient.first_name || '') + ' ' + (d.recipient.last_name || ''));
                        $('#recipient_phone').text(d.recipient.phone_number || '');
                        $('#recipient_address').text((d.recipient.address || '') + (d.recipient.zipcode ? ', ' + d.recipient.zipcode : ''));
                    } else {
                        $('#recipient_name').text(d.recipient.recipient_contact_person_name || '');
                        $('#recipient_phone').text(d.recipient.recipient_contact_person_phone_number || '');
                        $('#recipient_address').text((d.recipient.recipient_contact_address || '') + (d.recipient.recipient_contact_zipcode ? ', ' + d.recipient.recipient_contact_zipcode : ''));
                    }
                } else {
                    $('#recipient_name').text('N/A');
                    $('#recipient_phone').text('N/A');
                    $('#recipient_address').text('N/A');
                }

                // Timeline
                $('.timeline-item').hide();
                let latestDate = null;
                for (let i = 1; i <= parseInt(shipment.status_id); i++) {
                    const hist = (d.shipment_history || []).find(h => parseInt(h.status_id) === i);
                    const dt   = hist ? fmtDate(hist.changed_at) : (latestDate || '');
                    if (hist) latestDate = dt;
                    $('#time-' + i).text(dt);
                    $('#status-' + i).show();
                }

                // Stops
                if (shipment.status_id >= 5 && d.shipment_stops && d.shipment_stops.length) {
                    const sc = document.getElementById('stops-container');
                    sc.innerHTML = '';
                    d.shipment_stops.forEach(s => {
                        sc.innerHTML += '<p style="font-size:13px; color:#555; margin:4px 0;">→ <strong>' + s.departure_point + '</strong> to <strong>' + s.destination_point + '</strong>' + (s.description ? ': ' + s.description : '') + '</p>';
                    });
                    $('#shipment-stops').show();
                }

                // Delivery info
                if (shipment.status_id == 8 && d.delivery_details) {
                    $('#delivered_to').text((d.delivery_details.first_name || '') + ' ' + (d.delivery_details.last_name || ''));
                    $('#delivery_info').show();
                }

                $('#track-result').show();
            },
            error: function () {
                $('#get_shipment_btn').prop('disabled', false).html('<i class="fas fa-search"></i> Track');
                $('#track-empty').show();
            }
        });
    }

    // ── Public Pickup ──
    $('#public-pickup-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        $('#pickup-success,#pickup-error').hide();

        $.ajax({
            url: '<?php echo base_url("courier/portal/store_pickup"); ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (r) {
                if (r.status === 'success') {
                    $('#pickup-success').show();
                    form.hide();
                    form[0].reset();
                } else {
                    $('#pickup-error').text(r.message || 'An error occurred.').show();
                }
            },
            error: function () {
                $('#pickup-error').text('Network error. Please try again.').show();
            }
        });
    });

    // ── Shipment form quote calculator ──
    (function () {
        // Reuse TARIFF_ZONES already bootstrapped by the page (no extra AJAX call needed)
        var $shipZone = $('#ship_zone');
        if (typeof TARIFF_ZONES !== 'undefined' && TARIFF_ZONES.length) {
            $.each(TARIFF_ZONES, function (i, z) {
                $shipZone.append('<option value="' + z.zone_code.toLowerCase() + '">' + z.zone_code + ' — ' + z.name + '</option>');
            });
        } else {
            $shipZone.html('<option value="">No zones configured yet</option>');
        }

        // Hide dimensions for documents
        $('#ship_cargo_type').on('change', function () {
            if ($(this).val() === 'document') {
                $('#ship_dims_wrap').hide();
                $('#ship_length,#ship_width,#ship_height').val('');
            } else {
                $('#ship_dims_wrap').show();
            }
        });

        // Get Quote button
        $('#ship_btn_get_quote').on('click', function () {
            $('#ship_quote_result,#ship_quote_error').hide();
            var cargo  = $('#ship_cargo_type').val();
            var zone   = $('#ship_zone').val();
            var weight = $('#ship_weight').val();
            var length = $('#ship_length').val() || 0;
            var width  = $('#ship_width').val()  || 0;
            var height = $('#ship_height').val() || 0;

            if (!zone) { showShipQErr('Please select a delivery zone.'); return; }
            if (!weight && cargo !== 'document') { showShipQErr('Please enter the package weight.'); return; }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Calculating…');

            $.ajax({
                url:      QUOTE_CALC_URL,
                type:     'POST',
                dataType: 'json',
                data: { cargo_type: cargo, zone: zone, weight: weight, length: length, width: width, height: height },
                success: function (r) {
                    btn.prop('disabled', false).html('<i class="fas fa-calculator"></i> Get Quote');
                    if (r.status === 'success') {
                        var d = r.data;
                        var fmt = function(n){ return parseFloat(n).toLocaleString('en-KE', {minimumFractionDigits:2,maximumFractionDigits:2}); };
                        $('#ship_qr_zone').text('Zone: ' + d.zone_name);
                        $('#ship_qr_weight').text('Chargeable weight: ' + d.chargeable_weight + ' kg');
                        $('#ship_qr_transport').text(fmt(d.transport));
                        $('#ship_qr_handling').text(fmt(d.handling));
                        $('#ship_qr_vat_rate').text(d.vat_rate);
                        $('#ship_qr_vat').text(fmt(d.vat_amount));
                        $('#ship_qr_total').text(fmt(d.total));
                        $('#ship_quote_result').show();
                        $('#h_quoted_amount').val(d.total);
                    } else {
                        showShipQErr(r.message || 'Could not calculate quote.');
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html('<i class="fas fa-calculator"></i> Get Quote');
                    showShipQErr('Network error. Please try again.');
                }
            });
        });

        function showShipQErr(msg) {
            $('#ship_quote_error').text(msg).show();
        }
    })();

    // ── Public Shipment Request ──
    $('#public-shipment-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        $('#shipment-success,#shipment-error').hide();

        $.ajax({
            url: '<?php echo base_url("courier/portal/store_shipment"); ?>',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (r) {
                if (r.status === 'success') {
                    $('#shipment-success').show();
                    form.hide();
                    form[0].reset();
                } else {
                    $('#shipment-error').text(r.message || 'An error occurred.').show();
                }
            },
            error: function () {
                $('#shipment-error').text('Network error. Please try again.').show();
            }
        });
    });
</script>
</body>
</html>
