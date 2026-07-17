<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$t = $trip;
$status_order  = ['booked','fuel_requested','started','offloading','completed'];
$status_labels = ['booked'=>'Booked','fuel_requested'=>'Fuel Requested','started'=>'In Transit','offloading'=>'Offloading','completed'=>'Completed','cancelled'=>'Cancelled'];
$current_idx   = array_search($t->status, $status_order);
$vstatus_labels = ['empty'=>'Empty','half_load'=>'Half Load','partly_loaded'=>'Partly Loaded'];
$ltype_labels   = ['full'=>'Full Load','half'=>'Half Load','part'=>'Part Load'];
?>
<style>
/* Timeline */
.trip-timeline{display:flex;align-items:flex-start;gap:0;margin:20px 0 24px;}
.tl-step{flex:1;text-align:center;position:relative;}
.tl-step::after{content:'';position:absolute;top:18px;left:50%;width:100%;height:3px;background:#ddd;z-index:0;}
.tl-step:last-child::after{display:none;}
.tl-step.done::after{background:#28a745;}
.tl-step.active::after{background:linear-gradient(90deg,#28a745 0%,#ddd 100%);}
.tl-circle{width:38px;height:38px;border-radius:50%;border:3px solid #ddd;background:#fff;display:inline-flex;align-items:center;justify-content:center;position:relative;z-index:1;font-size:15px;}
.tl-step.done .tl-circle{border-color:#28a745;background:#28a745;color:#fff;}
.tl-step.active .tl-circle{border-color:#1976d2;background:#1976d2;color:#fff;box-shadow:0 0 0 4px rgba(25,118,210,.2);}
.tl-lbl{font-size:11px;margin-top:5px;color:#888;}
.tl-step.active .tl-lbl{color:#1976d2;font-weight:700;}
.tl-step.done .tl-lbl{color:#28a745;}
/* Route bar */
.route-bar{display:flex;align-items:center;background:linear-gradient(135deg,#e3f2fd,#f3e5f5);border:1px solid #90caf9;border-radius:8px;padding:14px 20px;margin-bottom:16px;gap:12px;font-size:14px;}
.route-bar .pt{font-weight:700;font-size:16px;}
.route-bar .arrow{color:#1976d2;font-size:22px;flex:0;}
.route-bar .date-badge{background:#1976d2;color:#fff;border-radius:20px;padding:3px 12px;font-size:12px;margin-left:auto;}
/* Info grid */
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:20px;}
.info-card{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:12px 14px;}
.info-card .ic-lbl{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.5px;}
.info-card .ic-val{font-size:14px;font-weight:600;margin-top:2px;}
/* Action panel */
.action-panel{background:#f8f9fa;border:1px solid #e0e0e0;border-radius:8px;padding:20px;margin-top:16px;}
/* Offloading */
.offload-row{background:#fff;border:1px solid #e0e0e0;border-radius:6px;padding:10px 14px;margin-bottom:8px;font-size:13px;}
.badge-full{background:#28a745;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;}
.badge-partial{background:#ff9800;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;}
.badge-parts{background:#2196f3;color:#fff;border-radius:12px;padding:2px 8px;font-size:11px;}
/* Offload type selector */
.offload-opts{display:flex;gap:10px;margin-top:6px;}
.offload-opt{flex:1;border:2px solid #ddd;border-radius:6px;padding:10px;text-align:center;cursor:pointer;transition:all .2s;user-select:none;}
.offload-opt:hover{border-color:#1976d2;background:#f0f7ff;}
.offload-opt.selected{border-color:#1976d2;background:#e3f2fd;}
</style>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">

          <!-- Header -->
          <div class="panel-heading" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <h4 class="m-0">
              <i class="fa fa-road"></i> Trip #<?php echo $t->id; ?>
              <span class="label label-<?php echo $t->track_type==='double'?'info':'default'; ?>" style="font-size:12px;margin-left:8px;">
                <i class="fa fa-<?php echo $t->track_type==='double'?'exchange':'arrow-right'; ?>"></i>
                <?php echo $t->track_type==='double'?'Double Track':'Single Track'; ?>
              </span>
              <?php if ($t->status==='cancelled'): ?><span class="label label-danger" style="font-size:12px;margin-left:8px;">Cancelled</span><?php endif; ?>
            </h4>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <a href="<?php echo admin_url('fleet/trips'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> All Trips</a>
              <?php if ($t->status==='completed' && $t->track_type==='double'): ?>
              <a href="<?php echo admin_url('fleet/trips/create?parent='.$t->id); ?>" class="btn btn-primary btn-sm"><i class="fa fa-exchange"></i> Book Return Trip</a>
              <?php elseif ($t->status==='completed'): ?>
              <a href="<?php echo admin_url('fleet/trips/create'); ?>" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> New Trip</a>
              <?php endif; ?>
              <?php if (!in_array($t->status,['completed','cancelled'])): ?>
              <button class="btn btn-danger btn-sm" onclick="cancelTrip(<?php echo $t->id; ?>)"><i class="fa fa-times"></i> Cancel Trip</button>
              <?php endif; ?>
            </div>
          </div>

          <div class="panel-body">

            <!-- Route bar -->
            <div class="route-bar">
              <div>
                <div style="font-size:11px;color:#666;text-transform:uppercase;">From</div>
                <div class="pt"><?php echo htmlspecialchars($t->from_point_name ?? '—'); ?></div>
              </div>
              <div class="arrow"><i class="fa fa-long-arrow-right"></i></div>
              <div>
                <div style="font-size:11px;color:#666;text-transform:uppercase;">To</div>
                <div class="pt"><?php echo htmlspecialchars($t->to_point_name ?? '—'); ?></div>
              </div>
              <?php if ($t->trip_date): ?>
              <div class="date-badge"><i class="fa fa-calendar"></i> <?php echo date('d M Y H:i', strtotime($t->trip_date)); ?></div>
              <?php endif; ?>
            </div>

            <!-- Status timeline -->
            <?php if ($t->status !== 'cancelled'): ?>
            <div class="trip-timeline">
              <?php
              $icons = ['booked'=>'fa-calendar-check-o','fuel_requested'=>'fa-tint','started'=>'fa-play','offloading'=>'fa-download','completed'=>'fa-check-circle'];
              foreach ($status_order as $idx => $s):
                $cls = ($idx < $current_idx) ? 'done' : (($idx === $current_idx) ? 'active' : '');
              ?>
              <div class="tl-step <?php echo $cls; ?>">
                <div class="tl-circle"><i class="fa <?php echo $icons[$s]; ?>"></i></div>
                <div class="tl-lbl"><?php echo $status_labels[$s]; ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Info cards -->
            <div class="info-grid">
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-truck"></i> Vehicle</div>
                <div class="ic-val"><?php echo htmlspecialchars($t->vehicle_name ?? '—'); ?></div>
                <div style="font-size:12px;color:#888;"><?php echo htmlspecialchars($t->license_plate ?? ''); ?></div>
              </div>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-user"></i> Driver</div>
                <div class="ic-val"><?php echo htmlspecialchars($t->driver_name ?? 'Unassigned'); ?></div>
              </div>
              <?php if ($t->customer_name): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-building"></i> Customer</div>
                <div class="ic-val"><?php echo htmlspecialchars($t->customer_name); ?></div>
              </div>
              <?php endif; ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-map-marker"></i> Loading Point</div>
                <div class="ic-val"><?php echo htmlspecialchars($t->from_point_name ?? '—'); ?></div>
              </div>
              <?php if ($t->vehicle_status): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-archive"></i> Vehicle Status</div>
                <div class="ic-val"><?php echo $vstatus_labels[$t->vehicle_status] ?? $t->vehicle_status; ?></div>
              </div>
              <?php endif; ?>
              <?php if ($t->load_type): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-cubes"></i> Load Type</div>
                <div class="ic-val"><?php echo $ltype_labels[$t->load_type] ?? $t->load_type; ?></div>
              </div>
              <?php endif; ?>
              <?php if ($shipment): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-file-text"></i> Shipment</div>
                <div class="ic-val">
                  <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/'.$t->shipment_id); ?>">
                    <?php echo htmlspecialchars($shipment->waybill_number ?? '—'); ?>
                  </a>
                </div>
              </div>
              <?php endif; ?>
              <?php if ($t->start_odometer): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-tachometer"></i> Start Odometer</div>
                <div class="ic-val"><?php echo number_format($t->start_odometer); ?> km</div>
                <?php if ($t->start_time): ?><div style="font-size:11px;color:#888;"><?php echo date('d M Y H:i', strtotime($t->start_time)); ?></div><?php endif; ?>
              </div>
              <?php endif; ?>
              <?php if ($t->end_odometer): ?>
              <div class="info-card">
                <div class="ic-lbl"><i class="fa fa-flag-checkered"></i> End Odometer</div>
                <div class="ic-val"><?php echo number_format($t->end_odometer); ?> km</div>
                <?php if ($t->start_odometer): ?><div style="font-size:11px;color:#888;">Distance: <?php echo number_format($t->end_odometer - $t->start_odometer); ?> km</div><?php endif; ?>
              </div>
              <?php endif; ?>
            </div>

            <!-- Fuel info bar -->
            <?php if ($fuel_request): ?>
            <div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:10px 16px;margin-bottom:14px;font-size:13px;">
              <i class="fa fa-tint" style="color:#e65100;"></i> <strong>Fuel:</strong>
              <?php echo htmlspecialchars($fuel_request->gallons ?? 0); ?> L <?php echo htmlspecialchars($fuel_request->fuel_type ?? 'Diesel'); ?>
              <?php if ($fuel_request->price): ?>&nbsp;·&nbsp; KES <?php echo number_format($fuel_request->price, 2); ?><?php endif; ?>
              <?php if ($fuel_request->odometer): ?>&nbsp;·&nbsp; Odo: <?php echo number_format($fuel_request->odometer); ?> km<?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if ($t->notes): ?>
            <div style="background:#f5f5f5;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;">
              <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($t->notes)); ?>
            </div>
            <?php endif; ?>

            <!-- ── Action panel ──────────────────────────────────────────────── -->
            <?php if (!in_array($t->status, ['completed','cancelled'])): ?>
            <div class="action-panel">
              <h5 style="font-weight:700;margin-top:0;margin-bottom:14px;"><i class="fa fa-bolt" style="color:#1976d2;"></i> Next Action</h5>

              <?php if ($t->status === 'booked'): ?>
              <p style="font-size:13px;color:#555;">Trip is booked. Request fuel before departure or start the trip directly.</p>
              <button class="btn btn-warning" onclick="$('#fuelModal').modal('show')"><i class="fa fa-tint"></i> Request Fuel</button>
              &nbsp;
              <button class="btn btn-success" onclick="$('#startModal').modal('show')"><i class="fa fa-play"></i> Start Trip</button>

              <?php elseif ($t->status === 'fuel_requested'): ?>
              <p style="font-size:13px;color:#555;"><i class="fa fa-check-circle" style="color:#28a745;"></i> Fuel request submitted. Ready to depart.</p>
              <button class="btn btn-success btn-lg" onclick="$('#startModal').modal('show')"><i class="fa fa-play"></i> Start Trip</button>

              <?php elseif ($t->status === 'started'): ?>
              <p style="font-size:13px;color:#555;"><i class="fa fa-truck" style="color:#1976d2;"></i> Trip is in progress. Record offloading when you reach the destination.</p>
              <button class="btn btn-warning btn-lg" onclick="$('#offloadModal').modal('show')"><i class="fa fa-download"></i> Start Offloading / Add Top-Up</button>

              <?php elseif ($t->status === 'offloading'): ?>
              <p style="font-size:13px;color:#880e4f;font-weight:600;"><i class="fa fa-check-circle"></i> Full offload recorded — truck is empty.</p>
              <?php if ($t->track_type === 'double'): ?>
              <div style="background:#e3f2fd;border:1px solid #90caf9;border-radius:6px;padding:10px 14px;margin-bottom:12px;font-size:13px;">
                <i class="fa fa-exchange" style="color:#1565c0;"></i> <strong>Double Track:</strong> You can end this trip or book the return leg.
              </div>
              <a href="<?php echo admin_url('fleet/trips/create?parent='.$t->id); ?>" class="btn btn-primary" style="margin-right:10px;"><i class="fa fa-exchange"></i> Book Return Trip</a>
              <?php endif; ?>
              <button class="btn btn-success" onclick="$('#endModal').modal('show')"><i class="fa fa-flag-checkered"></i> End Trip</button>
              <?php endif; ?>
            </div>

            <?php elseif ($t->status === 'completed'): ?>
            <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:8px;padding:16px 20px;text-align:center;">
              <i class="fa fa-check-circle" style="color:#2e7d32;font-size:32px;display:block;margin-bottom:8px;"></i>
              <strong style="color:#2e7d32;font-size:16px;">Trip Completed</strong>
              <?php if ($t->end_time): ?><div style="font-size:12px;color:#555;margin-top:4px;">Ended: <?php echo date('d M Y H:i', strtotime($t->end_time)); ?></div><?php endif; ?>
              <div style="margin-top:14px;">
                <?php if ($t->track_type === 'double'): ?>
                <a href="<?php echo admin_url('fleet/trips/create?parent='.$t->id); ?>" class="btn btn-primary"><i class="fa fa-exchange"></i> Book Return Trip</a>
                &nbsp;
                <?php endif; ?>
                <a href="<?php echo admin_url('fleet/trips/create'); ?>" class="btn btn-default"><i class="fa fa-plus"></i> New Trip</a>
              </div>
            </div>
            <?php endif; ?>

            <!-- Offloading log -->
            <?php if (!empty($offloading)): ?>
            <div style="margin-top:24px;">
              <h5 style="font-weight:700;"><i class="fa fa-history"></i> Offloading Log</h5>
              <?php foreach ($offloading as $o):
                $bc = ['full'=>'badge-full','partial'=>'badge-partial','parts'=>'badge-parts'][$o->offload_type] ?? 'badge-partial';
              ?>
              <div class="offload-row">
                <span class="<?php echo $bc; ?>"><?php echo ucfirst($o->offload_type); ?> Offload</span>
                <?php if (!empty($o->point_name)): ?> &nbsp;at <strong><?php echo htmlspecialchars($o->point_name); ?></strong><?php endif; ?>
                <small style="color:#888;float:right;"><?php echo date('d M Y H:i', strtotime($o->recorded_at)); ?></small>
                <?php if ($o->packages_offloaded): ?><div style="margin-top:4px;font-size:12px;color:#555;"><?php echo htmlspecialchars($o->packages_offloaded); ?></div><?php endif; ?>
                <?php if ($o->notes): ?><div style="font-size:12px;color:#777;margin-top:2px;"><?php echo htmlspecialchars($o->notes); ?></div><?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

          </div><!-- /panel-body -->
        </div>
      </div>
 <div class="modal fade" id="fuelModal" tabindex="-1">
    <div class="modal-dialog modal-lg" style="width: 800px; max-width: 95%;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #eaeaea; padding: 15px 25px;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-tint" style="color: #1976d2;"></i> Request Fuel</h4>
            </div>
            <div class="modal-body" style="background: #fff; padding: 30px 40px; color: #333;">
                <!-- Form Header -->
                <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 25px;">
                    <strong style="font-size: 20px; font-style: italic; letter-spacing: 1px;">FQ<?php echo str_pad($t->id, 6, '0', STR_PAD_LEFT); ?></strong>
                    <div style="font-size: 16px;">Date: <span style="border-bottom: 1px dotted #999; display: inline-block; width: 150px; text-align: center;"><?php echo date('Y-m-d'); ?></span></div>
                </div>

                <div class="row" style="font-size: 14px;">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Vehicle :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" value="<?php echo htmlspecialchars($t->vehicle_name ?? ''); ?>" readonly style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Litres :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="number" id="fuel_qty" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Ext. Tank(Ltrs) :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="number" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Driver :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" value="<?php echo htmlspecialchars($t->driver_name ?? ''); ?>" readonly style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Vendor :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Fuel Type :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <select id="fuel_type" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="diesel">Diesel</option>
                                    <option value="petrol">Petrol</option>
                                    <option value="cng">CNG</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Trip Fueling :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Requested By :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" value="<?php echo get_staff_full_name(); ?>" readonly style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Checked By :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <select id="fuel_checked_by" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s->staffid; ?>"><?php echo htmlspecialchars($s->firstname . ' ' . $s->lastname); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Declined By :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <select id="fuel_declined_by" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s->staffid; ?>"><?php echo htmlspecialchars($s->firstname . ' ' . $s->lastname); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 40px;">Trip :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc; margin-right: 15px;">
                                <input type="text" value="<?php echo htmlspecialchars($t->from_point_name ?? ''); ?>" readonly title="<?php echo htmlspecialchars($t->from_point_name ?? ''); ?>" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; text-overflow: ellipsis;">
                            </div>
                            <span style="width: 30px;">To :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" value="<?php echo htmlspecialchars($t->to_point_name ?? ''); ?>" readonly title="<?php echo htmlspecialchars($t->to_point_name ?? ''); ?>" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; text-overflow: ellipsis;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 90px;">Trip Route :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc; margin-right: 15px;">
                                <input type="text" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                            <span style="width: 30px;">via</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 230px;">Fuel available in the Tank(Ltrs) :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="number" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 180px;">Odometer reading (Start) :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc; margin-right: 10px;">
                                <input type="number" id="fuel_odometer" value="<?php echo htmlspecialchars($t->vehicle_odometer ?: ''); ?>" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                            <span style="width: 170px;">Odometer reading (End) :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="number" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 110px;">Fuel Amount :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="number" id="fuel_cost" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: center; height: 26px;">
                            <span style="width: 60px;">Empty :</span>
                            <input type="checkbox" style="margin-right: 40px; transform: scale(1.3); cursor: pointer;">
                            <span style="width: 70px;">Loaded :</span>
                            <input type="checkbox" style="transform: scale(1.3); cursor: pointer;">
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 130px;">Highway Top up:</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 110px;">Approved By :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <select id="fuel_approved_by" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">— Select —</option>
                                    <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s->staffid; ?>"><?php echo htmlspecialchars($s->firstname . ' ' . $s->lastname); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; margin-bottom: 20px; align-items: baseline;">
                            <span style="width: 160px;">Reason for declining :</span>
                            <div style="flex: 1; border-bottom: 1px solid #ccc;">
                                <input type="text" id="fuel_decline_reason" style="border:none; width:100%; background:transparent; padding:0 5px; outline:none;">
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="fuel_notes" value="">
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #eaeaea;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitFuel()" style="background: #1976d2; border-color: #1976d2;"><i class="fa fa-check"></i> Submit Request</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Start Trip Modal ──────────────────────────────────────────────────────── -->
<div class="modal fade" id="startModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-play"></i> Start Trip</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Current Odometer (km) <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="start_odometer" value="<?php echo $t->start_odometer ?: ($t->vehicle_odometer ?: ''); ?>">
        </div>
        <p style="color:#555;font-size:13px;"><i class="fa fa-info-circle"></i> Trip will be marked as started now.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitStart()"><i class="fa fa-play"></i> Start Trip</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Offloading Modal ──────────────────────────────────────────────────────── -->
<div class="modal fade" id="offloadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-download"></i> Record Offloading</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Offload Type <span class="text-danger">*</span></label>
          <div class="offload-opts">
            <div class="offload-opt" data-val="full" onclick="pickOffload('full',this)"><i class="fa fa-truck" style="color:#2e7d32;font-size:18px;display:block;margin-bottom:4px;"></i><strong>Full</strong><div style="font-size:11px;color:#666;">Truck empty</div></div>
            <div class="offload-opt" data-val="partial" onclick="pickOffload('partial',this)"><i class="fa fa-truck" style="color:#f57c00;font-size:18px;display:block;margin-bottom:4px;"></i><strong>Partial / Top-Up</strong><div style="font-size:11px;color:#666;">Trip continues</div></div>
            <div class="offload-opt" data-val="parts" onclick="pickOffload('parts',this)"><i class="fa fa-cubes" style="color:#1565c0;font-size:18px;display:block;margin-bottom:4px;"></i><strong>Parts Only</strong><div style="font-size:11px;color:#666;">Specific items</div></div>
          </div>
        </div>
        <div class="form-group">
          <label>Destination Service Point</label>
          <select class="form-control" id="offload_service_point">
            <option value="">— Select service point (optional) —</option>
            <?php foreach ($service_points as $sp): ?>
            <option value="<?php echo $sp->id; ?>"><?php echo htmlspecialchars($sp->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Current Odometer (km)</label>
          <input type="number" class="form-control" id="offload_odometer" placeholder="e.g. 45800">
        </div>
        <div class="form-group">
          <label>Packages / Description</label>
          <textarea class="form-control" id="offload_packages" rows="2" placeholder="Describe what was offloaded..."></textarea>
        </div>
        <div class="form-group"><label>Notes</label><textarea class="form-control" id="offload_notes" rows="2"></textarea></div>
        <div id="full-offload-note" style="display:none;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:4px;padding:8px 12px;font-size:13px;">
          <i class="fa fa-check-circle" style="color:#2e7d32;"></i> <strong>Full Offload</strong> — Truck will be marked empty. You can then end the trip.
        </div>
        <div id="partial-offload-note" style="display:none;background:#fff3e0;border:1px solid #ffe0b2;border-radius:4px;padding:8px 12px;font-size:13px;">
          <i class="fa fa-info-circle" style="color:#e65100;"></i> <strong>Partial / Top-Up</strong> — Trip continues after recording.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="btn-offload" onclick="submitOffload()"><i class="fa fa-download"></i> Record</button>
      </div>
    </div>
  </div>
</div>

<!-- ── End Trip Modal ────────────────────────────────────────────────────────── -->
<div class="modal fade" id="endModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-flag-checkered"></i> End Trip</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Final Odometer Reading (km) <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="end_odometer" placeholder="e.g. 46100">
        </div>
        <p style="color:#555;font-size:13px;"><i class="fa fa-info-circle"></i> Trip will be closed and the vehicle released.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitEnd()"><i class="fa fa-flag-checkered"></i> End Trip</button>
      </div>
    </div>
  </div>
</div>

<script>
var TRIP_ID   = <?php echo (int)$t->id; ?>;
var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
var CSRF_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';
var BASE      = '<?php echo admin_url('fleet/trips/'); ?>';
var selectedOffloadType = '';

function pickOffload(val, el) {
    selectedOffloadType = val;
    document.querySelectorAll('.offload-opt').forEach(function(e){ e.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('full-offload-note').style.display    = val === 'full'    ? 'block' : 'none';
    document.getElementById('partial-offload-note').style.display = val === 'partial' ? 'block' : 'none';
}

function post(url, data, cb) {
    data[CSRF_NAME] = CSRF_HASH;
    var fd = new FormData();
    Object.keys(data).forEach(function(k){ fd.append(k, data[k]); });
    fetch(url, {method:'POST', body:fd}).then(function(r){ return r.json(); }).then(cb)
        .catch(function(){ alert('Network error.'); });
}

function submitFuel() {
    var qty = document.getElementById('fuel_qty').value;
    if (!qty) { alert('Please enter fuel quantity.'); return; }
    post(BASE+'request_fuel/'+TRIP_ID, {
        fuel_type:      document.getElementById('fuel_type').value,
        quantity:       qty,
        cost:           document.getElementById('fuel_cost').value,
        odometer:       document.getElementById('fuel_odometer').value,
        notes:          document.getElementById('fuel_notes').value,
        approved_by:    document.getElementById('fuel_approved_by').value,
        declined_by:    document.getElementById('fuel_declined_by').value,
        checked_by:     document.getElementById('fuel_checked_by').value,
        decline_reason: document.getElementById('fuel_decline_reason').value,
    }, function(res){ if (res.success) location.reload(); else alert(res.message||'Failed.'); });
}

function submitStart() {
    var odo = document.getElementById('start_odometer').value;
    if (!odo) { alert('Please enter odometer reading.'); return; }
    post(BASE+'start_trip/'+TRIP_ID, {odometer:odo}, function(res){ if (res.success) location.reload(); else alert('Failed.'); });
}

function submitOffload() {
    if (!selectedOffloadType) { alert('Please select the offload type.'); return; }
    post(BASE+'offload/'+TRIP_ID, {
        offload_type:       selectedOffloadType,
        service_point_id:   document.getElementById('offload_service_point').value,
        odometer:           document.getElementById('offload_odometer').value,
        packages_offloaded: document.getElementById('offload_packages').value,
        notes:              document.getElementById('offload_notes').value,
    }, function(res) {
        if (res.success) {
            $('#offloadModal').modal('hide');
            location.reload();
        } else { alert('Failed.'); }
    });
}

function submitEnd() {
    var odo = document.getElementById('end_odometer').value;
    if (!odo) { alert('Please enter final odometer reading.'); return; }
    post(BASE+'end_trip/'+TRIP_ID, {end_odometer:odo}, function(res){ if (res.success) location.reload(); else alert('Failed.'); });
}

function cancelTrip(id) {
    if (!confirm('Cancel this trip?')) return;
    post(BASE+'cancel/'+id, {}, function(res){ if (res.success) location.reload(); });
}
</script>
<?php init_tail(); ?>
