<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.trip-badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.tb-booked{background:#e3f2fd;color:#1565c0;}
.tb-fuel_requested{background:#fff3e0;color:#e65100;}
.tb-started{background:#e8f5e9;color:#1b5e20;}
.tb-offloading{background:#fce4ec;color:#880e4f;}
.tb-completed{background:#e8f5e9;color:#2e7d32;}
.tb-cancelled{background:#ffebee;color:#b71c1c;}
.stats-row{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;}
.stat-pill{flex:1;min-width:120px;background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:12px 14px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.06);}
.stat-pill .num{font-size:26px;font-weight:700;line-height:1;}
.stat-pill .lbl{font-size:11px;color:#888;margin-top:3px;}
</style>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
              <h4 class="font-bold m-0"><i class="fa fa-road"></i> Trip Bookings</h4>
              <a href="<?php echo admin_url('fleet/trips/create'); ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Book a Trip
              </a>
            </div>

            <!-- Status summary -->
            <div class="stats-row">
              <?php
              $pill_defs = [
                'booked'         => ['All Booked',       '#1565c0'],
                'fuel_requested' => ['Fuel Requested',   '#e65100'],
                'started'        => ['In Progress',      '#1b5e20'],
                'offloading'     => ['Offloading',       '#880e4f'],
                'completed'      => ['Completed',        '#2e7d32'],
                'cancelled'      => ['Cancelled',        '#b71c1c'],
              ];
              foreach ($pill_defs as $slug => $cfg): $n = $counts[$slug] ?? 0; ?>
              <div class="stat-pill">
                <div class="num" style="color:<?php echo $cfg[1]; ?>"><?php echo $n; ?></div>
                <div class="lbl"><?php echo $cfg[0]; ?></div>
              </div>
              <?php endforeach; ?>
            </div>

            <?php if (!empty($trips)): ?>
            <div class="table-responsive">
              <table class="table dt-table" id="tripsTable">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Waybill</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Track</th>
                    <th>Picking Point</th>
                    <th>Status</th>
                    <th>Start Time</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($trips as $t): ?>
                  <tr>
                    <td><?php echo $t->id; ?></td>
                    <td>
                      <?php if ($t->waybill_number): ?>
                        <a href="<?php echo admin_url('courier/shipments/waybill/' . $t->shipment_id); ?>">
                          <?php echo htmlspecialchars($t->waybill_number); ?>
                        </a>
                      <?php else: echo '—'; endif; ?>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($t->vehicle_name ?? '—'); ?></strong>
                      <?php if ($t->license_plate): ?>
                        <small style="color:#888;display:block;"><?php echo htmlspecialchars($t->license_plate); ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($t->driver_name ?? '—'); ?></td>
                    <td>
                      <span style="font-size:12px;">
                        <i class="fa fa-<?php echo $t->track_type === 'double' ? 'exchange' : 'arrow-right'; ?>"></i>
                        <?php echo ucfirst($t->track_type); ?>
                      </span>
                    </td>
                    <td><?php echo htmlspecialchars($t->from_point_name ?? '—'); ?></td>
                    <td><span class="trip-badge tb-<?php echo $t->status; ?>"><?php echo ucwords(str_replace('_',' ',$t->status)); ?></span></td>
                    <td><?php echo $t->start_time ? date('d M Y H:i', strtotime($t->start_time)) : '—'; ?></td>
                    <td>
                      <a href="<?php echo admin_url('fleet/trips/detail/' . $t->id); ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                      <?php if (is_admin()): ?>
                      <a href="<?php echo admin_url('fleet/trips/delete_trip/' . $t->id); ?>"
                         class="btn btn-xs btn-danger"
                         onclick="return confirm('Delete trip #<?php echo $t->id; ?>?')"><i class="fa fa-trash"></i></a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#aaa;">
              <i class="fa fa-road fa-3x" style="margin-bottom:12px;"></i>
              <p>No trips booked yet. <a href="<?php echo admin_url('fleet/trips/create'); ?>">Book the first trip.</a></p>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
