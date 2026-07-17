<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'dashboard']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">

                        <div class="cgs-card__header">
                            <h4 class="cgs-card__title"><i class="fa fa-home"></i> GO Shipping Cargo Dashboard</h4>
                            <div class="cgs-card__actions">
                                <a href="<?php echo admin_url('courier_goshipping/shipments/create'); ?>" class="cgs-btn cgs-btn--primary cgs-btn--sm"><i class="fa fa-plus"></i> New Shipment</a>
                                <a href="<?php echo admin_url('courier_goshipping/pickups/create'); ?>"  class="cgs-btn cgs-btn--outline cgs-btn--sm"><i class="fa fa-truck"></i> New Pickup</a>
                                <a href="<?php echo admin_url('courier_goshipping/shipments/main?group=manifests'); ?>" class="cgs-btn cgs-btn--outline cgs-btn--sm"><i class="fa fa-file-text-o"></i> Manifests</a>
                            </div>
                        </div>

                        <style>
                        .cdash-stat-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;}
                        .cdash-stat-card{flex:1;min-width:130px;background:#fff;border-radius:8px;border:1px solid #e0e0e0;padding:14px 16px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);transition:transform .15s,box-shadow .15s;cursor:pointer;text-decoration:none;color:inherit;}
                        .cdash-stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.12);text-decoration:none;color:inherit;}
                        .cdash-icon{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;}
                        .cdash-val{font-size:24px;font-weight:700;line-height:1;}
                        .cdash-lbl{font-size:11px;color:#777;margin-top:2px;}
                        .cdash-divider{font-weight:700;font-size:12px;color:#555;margin:22px 0 6px;border-bottom:2px solid #e0e0e0;padding-bottom:4px;}
                        .cdash-today-bar{display:flex;gap:16px;background:var(--cgs-primary-light,#eaf1f8);border:1px solid var(--cgs-border,#e2e6ec);border-radius:6px;padding:10px 16px;margin:14px 0;font-size:13px;}
                        .cdash-today-bar span{display:flex;align-items:center;gap:6px;}
                        </style>

                        <!-- Today / Month quick stats -->
                        <div class="cdash-today-bar">
                            <span><i class="fa fa-calendar-o" style="color:var(--cgs-primary,#3a6ea5);"></i> <strong>Today:</strong> <?php echo (int)($today_count ?? 0); ?> shipment<?php echo ($today_count ?? 0) != 1 ? 's' : ''; ?></span>
                            <span><i class="fa fa-calendar" style="color:var(--cgs-secondary,#c1272d);"></i> <strong>This Month:</strong> <?php echo (int)($month_count ?? 0); ?> shipment<?php echo ($month_count ?? 0) != 1 ? 's' : ''; ?></span>
                        </div>

                        <!-- Overview cards -->
                        <div class="cdash-stat-row">
                            <a href="<?php echo admin_url('courier_goshipping/shipments?type=domestic'); ?>" class="cdash-stat-card">
                                <div class="cdash-icon" style="background:#1565c0;"><i class="fa fa-globe"></i></div>
                                <div><div class="cdash-val"><?php echo (int)($total_shipments ?? 0); ?></div><div class="cdash-lbl">Total Shipments</div></div>
                            </a>
                            <a href="<?php echo admin_url('courier_goshipping/pickups/index'); ?>" class="cdash-stat-card">
                                <div class="cdash-icon" style="background:#ef6c00;"><i class="fa fa-truck"></i></div>
                                <div><div class="cdash-val"><?php echo (int)($total_pickups ?? 0); ?></div><div class="cdash-lbl">Total Pickups</div></div>
                            </a>
                            <a href="<?php echo admin_url('courier_goshipping/pickups/index'); ?>" class="cdash-stat-card">
                                <div class="cdash-icon" style="background:#c62828;"><i class="fa fa-clock-o"></i></div>
                                <div><div class="cdash-val"><?php echo (int)($pending_pickups ?? 0); ?></div><div class="cdash-lbl">Pending Pickups</div></div>
                            </a>
                            <a href="<?php echo admin_url('courier_goshipping/shipments?type=domestic&status=0'); ?>" class="cdash-stat-card">
                                <div class="cdash-icon" style="background:#6a1b9a;"><i class="fa fa-user"></i></div>
                                <div><div class="cdash-val"><?php echo (int)($portal_requests ?? 0); ?></div><div class="cdash-lbl">Portal Requests</div></div>
                            </a>
                            <a href="<?php echo admin_url('courier_goshipping/companies/main'); ?>" class="cdash-stat-card">
                                <div class="cdash-icon" style="background:#2e7d32;"><i class="fa fa-building"></i></div>
                                <div><div class="cdash-val"><?php echo (int)($courier_company_counts ?? 0); ?></div><div class="cdash-lbl">Courier Companies</div></div>
                            </a>
                        </div>

                        <!-- Per-status breakdown -->
                        <?php
                        $sc = $status_counts ?? [];
                        $sc_total = array_sum($sc);
                        $sc_defs = [
                            ['id'=>'1','label'=>'Created',            'icon'=>'fa-box',            'bg'=>'#0288d1'],
                            ['id'=>'2','label'=>'Picked Up',          'icon'=>'fa-truck',          'bg'=>'#1565c0'],
                            ['id'=>'3','label'=>'Received',           'icon'=>'fa-inbox',          'bg'=>'#00897b'],
                            ['id'=>'4','label'=>'Dispatched',         'icon'=>'fa-truck-loading',  'bg'=>'#ef6c00'],
                            ['id'=>'5','label'=>'In Transit',         'icon'=>'fa-shipping-fast',  'bg'=>'#6a1b9a'],
                            ['id'=>'6','label'=>'Arrived Dest.',      'icon'=>'fa-map-pin',        'bg'=>'#283593'],
                            ['id'=>'7','label'=>'Out for Delivery',   'icon'=>'fa-map-marker-alt', 'bg'=>'#f9a825'],
                            ['id'=>'8','label'=>'Delivered',          'icon'=>'fa-check-circle',   'bg'=>'#2e7d32'],
                            ['id'=>'9','label'=>'Cancelled',          'icon'=>'fa-times-circle',   'bg'=>'#c62828'],
                        ];
                        $list_base = admin_url('courier_goshipping/shipments?type=domestic&status=');
                        ?>
                        <?php if ($sc_total > 0): ?>
                        <div class="cdash-divider"><i class="fa fa-bar-chart"></i> Shipments by Status</div>
                        <div class="cdash-stat-row">
                        <?php foreach ($sc_defs as $scd):
                            $cnt = (int)($sc[$scd['id']] ?? 0);
                            if ($cnt === 0) continue;
                        ?>
                            <a href="<?php echo $list_base . $scd['id']; ?>" class="cdash-stat-card" style="min-width:110px;">
                                <div class="cdash-icon" style="background:<?php echo $scd['bg']; ?>;width:36px;height:36px;font-size:15px;">
                                    <i class="fa <?php echo $scd['icon']; ?>"></i>
                                </div>
                                <div><div class="cdash-val" style="font-size:20px;"><?php echo $cnt; ?></div><div class="cdash-lbl"><?php echo $scd['label']; ?></div></div>
                            </a>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Links -->
                        <div style="margin-top:28px;">
                            <h5 class="font-bold" style="margin-bottom:12px;"><i class="fa fa-th-large"></i> Quick Access</h5>
                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <a href="<?php echo admin_url('courier_goshipping/shipments/main'); ?>"        class="cgs-btn cgs-btn--outline"><i class="fa fa-globe"></i> Shipments</a>
                                <a href="<?php echo admin_url('courier_goshipping/pickups/main'); ?>"          class="cgs-btn cgs-btn--outline"><i class="fa fa-truck"></i> Pickups</a>
                                <a href="<?php echo admin_url('courier_goshipping/companies/main'); ?>"        class="cgs-btn cgs-btn--outline"><i class="fa fa-building"></i> Companies</a>
                                <a href="<?php echo admin_url('courier_goshipping/agents/main'); ?>"           class="cgs-btn cgs-btn--outline"><i class="fa fa-users"></i> Agents</a>
                                <a href="<?php echo admin_url('courier_goshipping/shipments/main?group=manifests'); ?>"   class="cgs-btn cgs-btn--outline"><i class="fa fa-file-text-o"></i> Manifests</a>
                                <a href="<?php echo admin_url('courier_goshipping/shipments/list_invoices'); ?>" class="cgs-btn cgs-btn--outline"><i class="fa fa-file-text"></i> Invoices</a>
                                <a href="<?php echo admin_url('courier_goshipping/settings/main'); ?>"        class="cgs-btn cgs-btn--outline"><i class="fa fa-cogs"></i> Settings</a>
                                <a href="<?php echo base_url('courier_goshipping/tracking'); ?>" target="_blank" class="cgs-btn cgs-btn--accent"><i class="fa fa-external-link"></i> Client Portal</a>
                            </div>
                        </div>

                        <!-- Recent Shipments -->
                        <?php if (!empty($recent_shipments)): ?>
                        <div style="margin-top:32px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                <h5 class="font-bold m-0"><i class="fa fa-history"></i> Recent Shipments</h5>
                                <a href="<?php echo admin_url('courier_goshipping/shipments'); ?>" class="btn btn-xs btn-default">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered cgs-table" style="font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th>Waybill #</th>
                                            <th>Sender</th>
                                            <th>Receiver</th>
                                            <th>Mode</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent_shipments as $s): ?>
                                        <?php
                                            $slug       = strtolower(str_replace(' ', '_', $s->status_name ?? ''));
                                            $badge_cls  = $status_badge[$slug] ?? 'default';
                                            $status_lbl = ucwords(str_replace('_', ' ', $s->status_name ?? 'Unknown'));
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($s->waybill_number ?: '—'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($s->sender_name ?: '—'); ?></td>
                                            <td><?php echo htmlspecialchars($s->receiver_name ?: '—'); ?></td>
                                            <td><?php echo htmlspecialchars($s->shipping_mode ?? '—'); ?></td>
                                            <td><span class="label label-<?php echo $badge_cls; ?>"><?php echo $status_lbl; ?></span></td>
                                            <td><?php echo $s->created_at ? date('d M Y', strtotime($s->created_at)) : '—'; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $s->id); ?>" class="btn btn-xs btn-info" title="Waybill"><i class="fa fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php else: ?>
                        <div style="margin-top:28px; text-align:center; color:#aaa; padding:24px 0;">
                            <i class="fa fa-inbox fa-3x" style="margin-bottom:10px;"></i>
                            <p>No shipments yet. <a href="<?php echo admin_url('courier_goshipping/shipments/create'); ?>">Create the first one.</a></p>
                        </div>
                        <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
