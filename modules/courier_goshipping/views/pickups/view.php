<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'pickups']); ?>
<?php echo '<script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>'; ?>
<style>
    body {
        background-color: #f4f7f6;
    }
    .exec-dashboard-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .exec-dashboard-header h2 {
        font-weight: 800;
        font-size: 26px;
        color: #1a1a1a;
        margin: 0;
    }
    .exec-dashboard-header p {
        color: #777;
        margin-top: 5px;
        font-size: 14px;
    }
    .exec-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        padding: 20px;
        margin-bottom: 25px;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .exec-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Top border accents */
    .border-top-blue { border-top: 4px solid #4a90e2; }
    .border-top-green { border-top: 4px solid #2ecc71; }
    .border-top-orange { border-top: 4px solid #f39c12; }
    .border-top-purple { border-top: 4px solid #9b59b6; }
    
    .stat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .stat-row:last-child {
        border-bottom: none;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    .stat-label i {
        width: 20px;
        color: #999;
    }
    .stat-value {
        color: #222;
        font-weight: 600;
        font-size: 14px;
        text-align: right;
    }
    .stat-value a {
        color: #4a90e2;
        text-decoration: none;
    }
    
    .status-pill {
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 12px;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    
    /* Summary boxes at top */
    .summary-box {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 12px;
        padding: 15px 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        margin-bottom: 25px;
    }
    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 15px;
    }
    .summary-icon.blue { background: rgba(74, 144, 226, 0.1); color: #4a90e2; }
    .summary-icon.green { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
    .summary-icon.orange { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
    
    .summary-content h4 {
        margin: 0;
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        font-weight: 700;
    }
    .summary-content h2 {
        margin: 5px 0 0 0;
        font-size: 24px;
        font-weight: 800;
        color: #222;
    }

    .signature-box {
        background: #fafafa;
        border: 1px dashed #ddd;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        margin-top: 15px;
    }
    .signature-box img {
        max-width: 100%;
        border-radius: 4px;
    }
</style>

    <div style="padding: 20px 30px;">

        <!-- Header -->
        <div class="exec-dashboard-header">
            <div>
                <h2>GO Shipping Cargo — Pickup Request #<?php echo $pickup['id'] ?? ''; ?></h2>
                <p>Real-time overview of pickup logistics and analytics.</p>
            </div>
            <div>
                <a href="<?php echo admin_url('courier_goshipping/pickups'); ?>" class="cgs-btn cgs-btn--outline">
                    <i class="fa fa-arrow-left"></i> Back to Pickups
                </a>
            </div>
        </div>

        <?php
            $st = $pickup['status'] ?? 'pending';
            $st_badge = ['pending'=>'default','picked_up'=>'info','delivered'=>'success','cancelled'=>'danger'][$st] ?? 'default';
            $src = $pickup['source'] ?? 'system';
            $src_badge = ['portal'=>'info', 'shipment'=>'primary', 'system'=>'default'][$src] ?? 'default';
        ?>

        <!-- Top Summary Boxes -->
        <div class="row">
            <div class="col-md-4">
                <div class="summary-box border-top-blue">
                    <div class="summary-icon blue"><i class="fa fa-info-circle"></i></div>
                    <div class="summary-content">
                        <h4>Current Status</h4>
                        <h2><span class="label label-<?php echo $st_badge; ?> status-pill"><?php echo htmlspecialchars(str_replace('_',' ',strtoupper($st))); ?></span></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box border-top-green">
                    <div class="summary-icon green"><i class="fa fa-truck"></i></div>
                    <div class="summary-content">
                        <h4>Driver Assigned</h4>
                        <?php $drv = (int)($pickup['driver_id'] ?? 0); ?>
                        <h2><?php echo $drv === 0 ? '<span class="text-danger" style="font-size:16px;"><i class="fa fa-exclamation-triangle"></i> Unassigned</span>' : '#'.$drv; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box border-top-orange">
                    <div class="summary-icon orange"><i class="fa fa-calendar"></i></div>
                    <div class="summary-content">
                        <h4>Scheduled Date</h4>
                        <h2><?php echo htmlspecialchars($pickup['pickup_date'] ?? 'N/A'); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Analytics & Logistics -->
            <div class="col-md-4">
                <div class="exec-card border-top-blue" style="min-height: 420px;">
                    <h3 class="exec-card-title"><i class="fa fa-line-chart" style="color:#4a90e2; margin-right:8px;"></i> Analytics & Logistics</h3>
                    
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-cloud-upload"></i> Source</span>
                        <span class="stat-value"><span class="label label-<?php echo $src_badge; ?>"><?php echo ucfirst($src); ?></span></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-clock-o"></i> Created At</span>
                        <span class="stat-value"><?php echo htmlspecialchars($pickup['created_at'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-hourglass-half"></i> Time Window</span>
                        <span class="stat-value"><?php echo htmlspecialchars($pickup['pickup_start_time'] ?? '') . ' - ' . htmlspecialchars($pickup['pickup_end_time'] ?? ''); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-car"></i> Vehicle Type</span>
                        <span class="stat-value"><?php echo htmlspecialchars(strtoupper($pickup['vehicle_type'] ?? 'N/A')); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-link"></i> Shipment ID</span>
                        <span class="stat-value">
                            <?php if(!empty($pickup['shipment_id'])): ?>
                                <a href="<?php echo admin_url('courier_goshipping/shipments/view/'.$pickup['shipment_id']); ?>" style="font-weight:700;">#<?php echo $pickup['shipment_id']; ?></a>
                            <?php else: ?>
                                <span class="text-muted">Not linked</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact & Location -->
            <div class="col-md-4">
                <div class="exec-card border-top-green" style="min-height: 420px;">
                    <h3 class="exec-card-title"><i class="fa fa-address-book" style="color:#2ecc71; margin-right:8px;"></i> Contact & Location</h3>
                    
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-user"></i> Name</span>
                        <span class="stat-value"><?php echo htmlspecialchars($pickup['contact_first_name'] ?? '') . ' ' . htmlspecialchars($pickup['contact_last_name'] ?? ''); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-envelope"></i> Email</span>
                        <span class="stat-value"><a href="mailto:<?php echo htmlspecialchars($pickup['contact_email'] ?? ''); ?>"><?php echo htmlspecialchars($pickup['contact_email'] ?? ''); ?></a></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-phone"></i> Phone</span>
                        <span class="stat-value"><a href="tel:<?php echo htmlspecialchars($pickup['contact_phone_number'] ?? ''); ?>"><?php echo htmlspecialchars($pickup['contact_phone_number'] ?? ''); ?></a></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-globe"></i> Country</span>
                        <span class="stat-value"><?php echo htmlspecialchars($pickup['country_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-map-marker"></i> Address</span>
                        <span class="stat-value text-right" style="max-width: 60%; line-height: 1.4;">
                            <?php echo htmlspecialchars($pickup['address'] ?? 'N/A'); ?><br>
                            <?php echo htmlspecialchars($pickup['pickup_zip'] ?? ''); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Assignments & Signatures -->
            <div class="col-md-4">
                <div class="exec-card border-top-orange" style="min-height: 420px;">
                    <h3 class="exec-card-title"><i class="fa fa-id-badge" style="color:#f39c12; margin-right:8px;"></i> Assignments & Signatures</h3>
                    
                    <div class="stat-row">
                        <span class="stat-label"><i class="fa fa-user-secret"></i> Agent Assigned</span>
                        <span class="stat-value">
                            <?php echo (int)($pickup['staff_id'] ?? 0) === 0 ? '<span class="text-danger"><i class="fa fa-times-circle"></i> Unassigned</span>' : '#'.(int)$pickup['staff_id']; ?>
                        </span>
                    </div>
                    
                    <div class="row mtop15">
                        <div class="col-md-6">
                            <div class="signature-box">
                                <p style="font-size:12px; font-weight:700; color:#777; margin-bottom:5px; text-transform:uppercase;">Pickup Sign</p>
                                <?php if (!empty($pickup['signature_url'])): ?>
                                    <img src="<?php echo base_url('modules/courier_goshipping/' . $pickup['signature_url']); ?>" />
                                <?php else: ?>
                                    <p class="text-muted" style="margin:20px 0;"><small>Pending</small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="signature-box">
                                <p style="font-size:12px; font-weight:700; color:#777; margin-bottom:5px; text-transform:uppercase;">Delivery Sign</p>
                                <?php if (!empty($pickup['delivery_signature_url'])): ?>
                                    <img src="<?php echo base_url('modules/courier_goshipping/' . $pickup['delivery_signature_url']); ?>" />
                                <?php else: ?>
                                    <p class="text-muted" style="margin:20px 0;"><small>Pending</small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="row">
            <div class="col-md-12">
                <div class="exec-card border-top-purple">
                    <h3 class="exec-card-title"><i class="fa fa-refresh" style="color:#9b59b6; margin-right:8px;"></i> Action: Update Status</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo form_open('admin/courier_goshipping/pickups/update_status/', ['id' => 'update-status-pickup-form']); ?>
                            <input type="hidden" id="current_status" value="<?php echo htmlspecialchars($pickup['status'] ?? 'pending'); ?>">
                            <input type="hidden" value="<?php echo $pickup['id'] ?? ''; ?>" name="pickup_id">
                            <input type="hidden" value="" name="signature">
                            
                            <div class="form-group">
                                <label for="status" style="font-weight:600; color:#555;">Select New Status:</label>
                                <select id="status" name="status" class="selectpicker" data-width="100%" data-style="btn-default" style="border: 1px solid #ccc; border-radius: 6px;">
                                    <option value="picked_up">Picked Up</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>
                            
                            <div id="signatureCanvasP" style="display:none; margin-bottom:20px; background:#f9fafb; padding:20px; border-radius:8px; border:1px solid #eef0f2;">
                                <label style="font-weight:600; color:#333; margin-bottom:10px; display:block;">Required: Draw Signature to verify</label>
                                <canvas height="150" id="signature" style="width:100%; max-width:400px; background:#fff; border: 2px dashed #d1d5db; border-radius:8px; cursor:crosshair; box-shadow:inset 0 2px 4px rgba(0,0,0,0.02);"></canvas>
                                <div class="mtop10">
                                    <button id="clear-signature" class="cgs-btn cgs-btn--outline cgs-btn--sm"><i class="fa fa-eraser"></i> Clear Canvas</button>
                                </div>
                            </div>

                            <button type="submit" class="cgs-btn cgs-btn--primary"><i class="fa fa-check"></i> Commit Status Update</button>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="col-md-6">
                            <div style="background: rgba(155,89,182,0.05); border: 1px solid rgba(155,89,182,0.2); padding: 20px; border-radius: 8px;">
                                <h4 style="margin-top:0; color:#9b59b6; font-weight:700;"><i class="fa fa-info-circle"></i> Instructions</h4>
                                <ul style="padding-left:20px; color:#555; line-height:1.6; margin-bottom:0;">
                                    <li>When updating to <b>Picked Up</b>, a signature from the sender is required.</li>
                                    <li>When updating to <b>Delivered</b>, a signature from the receiver is required.</li>
                                    <li>Portal pickups initially arrive completely unassigned. You must assign them manually to a driver.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        if($.fn.selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }

        window.toggleStatusView = function () {
            let currentStatus = $('#current_status').val();
            let statusSelect = $('#status');
            
            let html = '';
            if (currentStatus === 'picked_up' || currentStatus === 'delivered') {
                html += '<option value="delivered">Delivered</option>';
            } else {
                html += '<option value="picked_up">Picked Up</option>';
                html += '<option value="delivered">Delivered</option>';
            }
            statusSelect.html(html);
            if($.fn.selectpicker) statusSelect.selectpicker('refresh');
        }

        toggleStatusView();

        let statusSelect = $('#status');
        let signatureCanvas = $('#signatureCanvasP');

        window.toggleSignatureCanvas = function () {
            let val = statusSelect.val();
            if (val === 'picked_up' || val === 'delivered') {
                signatureCanvas.slideDown();
            } else {
                signatureCanvas.slideUp();
            }
        }
        
        toggleSignatureCanvas();
        statusSelect.on('change', toggleSignatureCanvas);

        let canvas = document.getElementById("signature");
        let signaturePad = new SignaturePad(canvas);

        $('#clear-signature').on('click', function (event) {
            event.preventDefault();
            signaturePad.clear();
        });

        $('#update-status-pickup-form').on('submit', function (e) {
            if (signatureCanvas.is(':visible') && signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Please provide a signature before updating the status.');
                return false;
            }
            if (!signaturePad.isEmpty()) {
                document.querySelector('input[name="signature"]').value = canvas.toDataURL('image/png');
            }
        });

    });
</script>
