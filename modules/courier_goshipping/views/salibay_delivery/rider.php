<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>Salibay Delivery</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>
    <meta name="theme-color" content="#0d47a1">

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { height: 100%; }
        body {
            margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1b2a; color: #fff;
            display: flex; flex-direction: column; align-items: center;
            text-align: center; padding: 24px;
            padding-top: calc(24px + env(safe-area-inset-top));
            padding-bottom: calc(24px + env(safe-area-inset-bottom));
        }
        h1 { font-size: 18px; margin: 0 0 4px; color: #8ecae6; }
        .waybill-num { color: #94a3b8; font-size: 13px; margin-bottom: 18px; }
        .order-card {
            width: 100%; max-width: 320px; text-align: left; background: #132339;
            border-left: 4px solid #c62828; border-radius: 10px; padding: 14px 16px;
            margin-bottom: 20px; font-size: 13.5px; line-height: 1.7; color: #cbd5e1;
        }
        .order-card strong { color: #fff; }
        button {
            font-size: 17px; font-weight: 700; padding: 16px 32px; border-radius: 12px;
            border: none; cursor: pointer; min-width: 260px; margin: 6px 0;
        }
        #start_btn { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; }
        #deliver_btn { background: #22c55e; color: #06281a; }
        #cancel_btn { background: #ef4444; color: #2b0a0a; }
        #confirm_deliver_btn { background: #22c55e; color: #06281a; }
        #confirm_cancel_btn { background: #ef4444; color: #2b0a0a; }
        .gs-panel { text-align: left; max-width: 300px; width: 100%; }
        .gs-panel label { display: block; font-size: 13px; color: #cbd5e1; margin: 12px 0 4px; }
        .gs-panel input[type="text"], .gs-panel textarea {
            width: 100%; padding: 12px; border-radius: 8px; border: none; font-size: 15px; font-family: inherit;
        }
        #signature_pad_canvas { width: 100%; height: 160px; background: #fff; border-radius: 8px; touch-action: none; display: block; }
        .gs-btn-wide { width: 100%; margin-top: 14px; }
        .gs-btn-secondary { background: #334155; color: #fff; font-size: 14px; padding: 12px; }
        .gs-error {
            display: none; margin-top: 10px; background: #7f1d1d; color: #fecaca;
            padding: 10px 14px; border-radius: 8px; font-size: 13px; max-width: 300px;
        }
        #trip_complete_banner {
            display: none; margin-top: 20px; background: #052e1a; color: #86efac;
            padding: 16px 20px; border-radius: 10px; font-size: 15px; font-weight: 700; max-width: 300px;
        }
    </style>
</head>
<body>
    <h1>Salibay Delivery</h1>
    <div class="waybill-num">Waybill: <?php echo htmlspecialchars($shipment->waybill_number ?: $shipment->tracking_id); ?></div>

    <div class="order-card">
        <div><strong>Items:</strong> <?php echo htmlspecialchars($items_summary); ?></div>
        <?php if ($recipient): ?>
        <div style="margin-top:8px;"><strong>Deliver to:</strong> <?php echo htmlspecialchars(trim($recipient->first_name . ' ' . $recipient->last_name)); ?></div>
        <div><strong>Phone:</strong> <?php echo htmlspecialchars($recipient->phone_number ?? ''); ?></div>
        <div><strong>Address:</strong> <?php echo htmlspecialchars($recipient->address ?? ''); ?></div>
        <?php endif; ?>
    </div>

    <?php
    $status_id      = (int) $shipment->status_id;
    $trip_finished  = in_array($status_id, [8, 9], true); // delivered or cancelled
    $already_started = $status_id >= 5 && !$trip_finished;
    $not_started     = $status_id < 5 && !$trip_finished;
    ?>

    <!-- ── Step 1: Start Delivery — parcel in hand, heading out ──────────────── -->
    <div id="start_section" style="<?php echo $not_started ? '' : 'display:none;'; ?>">
        <button id="start_btn" onclick="startDelivery()">🚴 Start Delivery</button>
        <div id="start_error" class="gs-error"></div>
    </div>

    <!-- ── Step 2: Deliver or cancel ───────────────────────────────────────── -->
    <div id="delivery_actions_section" style="<?php echo $already_started ? '' : 'display:none;'; ?>">
        <button id="deliver_btn" onclick="showDeliverForm()">✅ Mark Delivered</button>
        <button id="cancel_btn" onclick="showCancelForm()">✖ Cancel Delivery</button>
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
        <div id="deliver_error" class="gs-error"></div>
    </div>

    <div id="cancel_form" class="gs-panel" style="display:none;">
        <h3 style="color:#fca5a5; font-size:15px; margin:0;">Cancel Delivery</h3>
        <label for="cancel_reason">Reason for cancelling</label>
        <textarea id="cancel_reason" rows="4"></textarea>
        <button id="confirm_cancel_btn" onclick="submitCancel()" class="gs-btn-wide">Confirm Cancellation</button>
        <button type="button" onclick="hideForms()" class="gs-btn-secondary gs-btn-wide">Back</button>
        <div id="cancel_error" class="gs-error"></div>
    </div>

    <div id="trip_complete_banner" style="<?php echo $trip_finished ? 'display:block;' : ''; ?>">
        <?php echo $status_id === 9 ? '✖ Delivery cancelled.' : '✅ Delivered — thank you!'; ?>
    </div>

    <script>
        var TOKEN = <?php echo json_encode($token); ?>;
        var START_URL = <?php echo json_encode(site_url('admin/courier_goshipping/salibay_delivery/start')); ?>;
        var DELIVER_URL = <?php echo json_encode(site_url('admin/courier_goshipping/salibay_delivery/deliver')); ?>;
        var CANCEL_URL = <?php echo json_encode(site_url('admin/courier_goshipping/salibay_delivery/cancel')); ?>;
        var sigPad = null;

        function postForm(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(payload).toString()
            }).then(function (r) { return r.json(); });
        }

        function startDelivery() {
            var errBox = document.getElementById('start_error');
            errBox.style.display = 'none';

            var btn = document.getElementById('start_btn');
            btn.disabled = true;
            btn.textContent = 'Starting...';

            postForm(START_URL, { token: TOKEN }).then(function (res) {
                if (!res.success) {
                    errBox.textContent = res.message || 'Could not start the delivery. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = '🚴 Start Delivery';
                    return;
                }
                document.getElementById('start_section').style.display = 'none';
                document.getElementById('delivery_actions_section').style.display = 'block';
            }).catch(function () {
                errBox.textContent = 'Network error — please check your connection and try again.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.textContent = '🚴 Start Delivery';
            });
        }

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

        function tripFinished(message) {
            document.getElementById('delivery_actions_section').style.display = 'none';
            hideForms();
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

            postForm(DELIVER_URL, {
                token: TOKEN,
                first_name: firstName,
                last_name: lastName,
                signature: sigPad.toDataURL('image/png')
            }).then(function (res) {
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

            postForm(CANCEL_URL, { token: TOKEN, reason: reason }).then(function (res) {
                if (!res.success) {
                    errBox.textContent = res.message || 'Could not cancel the delivery. Please try again.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Confirm Cancellation';
                    return;
                }
                tripFinished('✖ Delivery cancelled.');
            }).catch(function () {
                errBox.textContent = 'Network error — please check your connection and try again.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Confirm Cancellation';
            });
        }
    </script>
</body>
</html>
