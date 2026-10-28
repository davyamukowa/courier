<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>
<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt_min = function ($m) {
    if ($m === null) { return '—'; }
    return $m >= 60 ? floor($m / 60) . 'h ' . ($m % 60) . 'm' : $m . ' min';
};
$max_day    = max(1, max($per_day));
$max_status = max(1, $by_status ? max($by_status) : 1);
$photo      = !empty($rider->photo_url) ? base_url('modules/courier_goshipping/' . $rider->photo_url) : null;
?>
<style>
.rp-wrap { padding: 0 15px 30px; }
.rp-card { background:#fff; border:1px solid #e3ebf3; border-radius:14px; padding:18px 20px; margin-bottom:16px; box-shadow:0 4px 14px rgba(15,23,42,.05); }
.rp-head { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
.rp-avatar { width:72px; height:72px; border-radius:50%; background:#eef2f7; object-fit:cover; display:flex; align-items:center; justify-content:center; font-size:28px; color:#7b8ca3; overflow:hidden; }
.rp-avatar img { width:100%; height:100%; object-fit:cover; }
.rp-head h2 { margin:0 0 4px; font-size:24px; font-weight:800; color:#102a43; }
.rp-meta { color:#60758c; font-size:13px; }
.rp-meta span { margin-right:14px; white-space:nowrap; }
.rp-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:16px; }
.rp-kpi { background:#fff; border:1px solid #e3ebf3; border-radius:12px; padding:14px; }
.rp-kpi .l { font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:#74859a; }
.rp-kpi .v { margin-top:6px; font-size:26px; font-weight:800; color:#102a43; line-height:1.1; }
.rp-kpi .s { font-size:12px; color:#74859a; margin-top:4px; }
.rp-grid { display:grid; grid-template-columns:2fr 1fr; gap:16px; }
@media (max-width: 900px) { .rp-grid { grid-template-columns:1fr; } }
.rp-card h4 { margin:0 0 12px; font-size:15px; font-weight:800; color:#102a43; }
.rp-bars { display:flex; align-items:flex-end; gap:3px; height:130px; }
.rp-bars .b { flex:1; background:#2f6fb5; border-radius:3px 3px 0 0; min-height:2px; position:relative; }
.rp-bars .b.zero { background:#dde6f0; }
.rp-axis { display:flex; justify-content:space-between; font-size:11px; color:#74859a; margin-top:6px; }
.rp-hrow { display:flex; align-items:center; gap:8px; margin-bottom:8px; font-size:13px; }
.rp-hrow .n { width:130px; color:#294a68; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.rp-hrow .t { flex:1; background:#eef2f7; border-radius:6px; height:12px; }
.rp-hrow .t i { display:block; height:100%; border-radius:6px; background:#2f6fb5; }
.rp-hrow .c { width:34px; text-align:right; font-weight:700; color:#102a43; }
.rp-form label { font-size:12px; font-weight:700; color:#60758c; margin:10px 0 4px; display:block; }
.rp-search { max-width:280px; margin-bottom:10px; }
.rp-pill { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700; background:#eef2f7; color:#294a68; }
.rp-pill.ok { background:#e3f6ea; color:#1b7a3d; } .rp-pill.bad { background:#fde8e8; color:#b42318; }
.rp-pill.sal { background:#fff1db; color:#a35c00; }
</style>

<div class="rp-wrap">
    <p style="margin:0 0 12px;"><a href="<?php echo admin_url('courier_goshipping/fulfilment/riders'); ?>"><i class="fa fa-arrow-left"></i> All riders</a></p>

    <div class="rp-card">
        <div class="rp-head">
            <div class="rp-avatar"><?php echo $photo ? '<img src="' . $e($photo) . '" alt="">' : '<i class="fa fa-motorcycle"></i>'; ?></div>
            <div style="flex:1;min-width:220px;">
                <h2><?php echo $e($rider->name); ?>
                    <span class="rp-pill <?php echo $rider->status === 'active' ? 'ok' : 'bad'; ?>"><?php echo $e(ucfirst($rider->status)); ?></span>
                </h2>
                <div class="rp-meta">
                    <span><i class="fa fa-phone"></i> <?php echo $e($rider->phone); ?></span>
                    <?php if (!empty($rider->email)) { ?><span><i class="fa fa-envelope-o"></i> <?php echo $e($rider->email); ?></span><?php } ?>
                    <?php if (!empty($rider->national_id)) { ?><span><i class="fa fa-id-card-o"></i> ID <?php echo $e($rider->national_id); ?></span><?php } ?>
                    <span><i class="fa fa-calendar"></i> Registered <?php echo $e(_dt($rider->created_at)); ?></span>
                    <span><i class="fa fa-link"></i> <?php echo $staff ? 'Driver profile: ' . $e(trim($staff->firstname . ' ' . $staff->lastname)) : 'No driver profile linked'; ?></span>
                </div>
            </div>
            <?php if ($can_manage_salibay_riders) { ?>
            <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#rp_edit"><i class="fa fa-pencil"></i> Edit rider</button>
            <?php } ?>
        </div>

        <?php if ($can_manage_salibay_riders) { ?>
        <div id="rp_edit" class="collapse" style="margin-top:14px;border-top:1px solid #eef2f7;padding-top:6px;">
            <?php echo form_open(admin_url('courier_goshipping/fulfilment/update_rider/' . $rider->id), ['class' => 'rp-form']); ?>
            <div class="row">
                <div class="col-md-3"><label>Name</label><input type="text" name="name" class="form-control" value="<?php echo $e($rider->name); ?>" required></div>
                <div class="col-md-3"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?php echo $e($rider->phone); ?>" required></div>
                <div class="col-md-2"><label>National ID</label><input type="text" name="national_id" class="form-control" value="<?php echo $e($rider->national_id); ?>" required></div>
                <div class="col-md-2"><label>Email</label><input type="text" name="email" class="form-control" value="<?php echo $e($rider->email); ?>"></div>
                <div class="col-md-2"><label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo $rider->status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="suspended" <?php echo $rider->status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:12px;">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#rp_edit">Cancel</button>
            </div>
            <?php echo form_close(); ?>
        </div>
        <?php } ?>
    </div>

    <div class="rp-kpis">
        <div class="rp-kpi"><div class="l">Total Deliveries</div><div class="v"><?php echo (int) $stats['total']; ?></div><div class="s"><?php echo (int) $stats['active']; ?> in progress</div></div>
        <div class="rp-kpi"><div class="l">Delivered</div><div class="v"><?php echo (int) $stats['delivered']; ?></div><div class="s"><?php echo (int) $stats['this_month']; ?> this month</div></div>
        <div class="rp-kpi"><div class="l">Cancelled</div><div class="v"><?php echo (int) $stats['cancelled']; ?></div></div>
        <div class="rp-kpi"><div class="l">Success Rate</div><div class="v"><?php echo $stats['success_rate'] === null ? '—' : $e($stats['success_rate']) . '%'; ?></div><div class="s">delivered vs cancelled</div></div>
        <div class="rp-kpi"><div class="l">Avg Delivery Time</div><div class="v" style="font-size:22px;"><?php echo $e($fmt_min($stats['avg_minutes'])); ?></div><div class="s">dispatch to delivered</div></div>
        <div class="rp-kpi"><div class="l">Clients Served</div><div class="v"><?php echo (int) $stats['clients']; ?></div></div>
        <div class="rp-kpi"><div class="l">Pickups</div><div class="v"><?php echo (int) $stats['pickups']; ?></div><div class="s"><?php echo (int) $stats['pickups_done']; ?> completed</div></div>
        <div class="rp-kpi"><div class="l">Salibay / General</div><div class="v" style="font-size:22px;"><?php echo (int) $stats['salibay']; ?> / <?php echo (int) $stats['general']; ?></div><div class="s"><?php echo (int) $stats['trips']; ?> fleet trips</div></div>
    </div>

    <?php if (!$rider->staff_id) { ?>
        <div class="alert alert-warning">This rider isn't linked to a driver profile yet, so no deliveries can be attributed to them. Link one from the Riders list.</div>
    <?php } ?>

    <div class="rp-grid">
        <div class="rp-card">
            <h4>Deliveries per day — last 30 days</h4>
            <div class="rp-bars">
                <?php foreach ($per_day as $day => $n) { ?>
                    <div class="b <?php echo $n ? '' : 'zero'; ?>" style="height:<?php echo $n ? max(4, round($n / $max_day * 100)) : 2; ?>%;" title="<?php echo $e($day . ': ' . $n); ?>"></div>
                <?php } ?>
            </div>
            <div class="rp-axis"><span><?php echo $e(date('d M', strtotime('-29 day'))); ?></span><span>peak: <?php echo (int) $max_day === 1 && !array_sum($per_day) ? 0 : (int) $max_day; ?>/day</span><span>Today</span></div>
        </div>
        <div class="rp-card">
            <h4>By status</h4>
            <?php if (!$by_status) { ?><p class="text-muted">No deliveries yet.</p><?php } ?>
            <?php foreach ($by_status as $label => $n) { ?>
                <div class="rp-hrow"><div class="n" title="<?php echo $e($label); ?>"><?php echo $e($label); ?></div><div class="t"><i style="width:<?php echo round($n / $max_status * 100); ?>%"></i></div><div class="c"><?php echo (int) $n; ?></div></div>
            <?php } ?>
        </div>
    </div>

    <div class="rp-card">
        <ul class="nav nav-tabs" role="tablist">
            <li class="active"><a href="#rp_tab_deliveries" data-toggle="tab">Deliveries &amp; orders (<?php echo count($deliveries); ?>)</a></li>
            <li><a href="#rp_tab_clients" data-toggle="tab">Clients (<?php echo count($clients); ?>)</a></li>
            <li><a href="#rp_tab_pickups" data-toggle="tab">Pickups (<?php echo count($pickups); ?>)</a></li>
        </ul>
        <div class="tab-content" style="padding-top:14px;">
            <div class="tab-pane active" id="rp_tab_deliveries">
                <input type="text" class="form-control rp-search" data-filter="#rp_deliveries_table" placeholder="Search waybill, order, client...">
                <div class="table-responsive">
                <table class="table table-striped table-condensed" id="rp_deliveries_table">
                    <thead><tr><th>Waybill</th><th>Order</th><th>Client</th><th>Phone</th><th>Address</th><th>Status</th><th>Assigned</th><th>Delivered</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php foreach ($deliveries as $d) { $st = (int) $d->status_id; ?>
                        <tr>
                            <td><a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . (int) $d->id); ?>"><?php echo $e($d->waybill_number ?: $d->tracking_id); ?></a></td>
                            <td><?php echo $d->shopify_order_number ? '<span class="rp-pill sal">#' . $e($d->shopify_order_number) . '</span>' : '<span class="text-muted">—</span>'; ?></td>
                            <td><?php echo $e(trim($d->first_name . ' ' . $d->last_name)); ?></td>
                            <td><?php echo $e($d->phone_number); ?></td>
                            <td><?php echo $e($d->address); ?></td>
                            <td><span class="rp-pill <?php echo $st === 8 ? 'ok' : ($st === 9 ? 'bad' : ''); ?>"><?php echo $e($d->status_text); ?></span></td>
                            <td><?php echo $d->created_at ? $e(_dt($d->created_at)) : '—'; ?></td>
                            <td><?php echo $d->delivered_at ? $e(_dt($d->delivered_at)) : '—'; ?></td>
                            <td><?php echo $d->total_price !== null ? $e($d->currency . ' ' . number_format((float) $d->total_price, 2)) : '—'; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (!$deliveries) { ?><tr><td colspan="9" class="text-center text-muted">No deliveries assigned to this rider yet.</td></tr><?php } ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="tab-pane" id="rp_tab_clients">
                <input type="text" class="form-control rp-search" data-filter="#rp_clients_table" placeholder="Search clients...">
                <div class="table-responsive">
                <table class="table table-striped table-condensed" id="rp_clients_table">
                    <thead><tr><th>Client</th><th>Phone</th><th>Address</th><th>Orders</th><th>Delivered</th><th>Last activity</th></tr></thead>
                    <tbody>
                    <?php foreach ($clients as $c) { ?>
                        <tr>
                            <td><?php echo $e($c->name); ?></td><td><?php echo $e($c->phone); ?></td><td><?php echo $e($c->address); ?></td>
                            <td><?php echo (int) $c->orders; ?></td><td><?php echo (int) $c->delivered; ?></td>
                            <td><?php echo $c->last ? $e(_dt($c->last)) : '—'; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (!$clients) { ?><tr><td colspan="6" class="text-center text-muted">No clients yet.</td></tr><?php } ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="tab-pane" id="rp_tab_pickups">
                <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead><tr><th>#</th><th>Date</th><th>Address</th><th>Waybill</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($pickups as $p) { ?>
                        <tr>
                            <td><?php echo (int) $p->id; ?></td>
                            <td><?php echo $p->pickup_date ? $e(_d($p->pickup_date)) : '—'; ?></td>
                            <td><?php echo $e($p->address); ?></td>
                            <td><?php echo $p->shipment_id ? '<a href="' . admin_url('courier_goshipping/shipments/waybill/' . (int) $p->shipment_id) . '">' . $e($p->waybill_number) . '</a>' : '—'; ?></td>
                            <td><span class="rp-pill <?php echo $p->status === 'pending' ? '' : 'ok'; ?>"><?php echo $e(ucwords(str_replace('_', ' ', $p->status))); ?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if (!$pickups) { ?><tr><td colspan="5" class="text-center text-muted">No pickups for this rider.</td></tr><?php } ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function waitForJQueryRiderProfile() {
    if (!window.jQuery) { setTimeout(waitForJQueryRiderProfile, 50); return; }
    $(function () {
        $('.rp-search').on('input', function () {
            var q = $(this).val().toLowerCase();
            $($(this).data('filter')).find('tbody tr').each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
            });
        });
    });
})();
</script>
<?php init_tail(); ?>
