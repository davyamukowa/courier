<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'fulfilment']); ?>
<style>
.cgs-fulfilment-shell {
    min-width: 0;
}
.cgs-fulfilment-header {
    margin: 0 0 20px;
    padding: 22px 24px;
    border: 1px solid #dbe7f3;
    border-radius: 18px;
    background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 52%, #ffffff 100%);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}
.cgs-fulfilment-header__eyebrow {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c84a0;
}
.cgs-fulfilment-header__title {
    margin: 8px 0 6px;
    font-size: 30px;
    font-weight: 800;
    color: #102a43;
}
.cgs-fulfilment-header__subtitle {
    margin: 0;
    max-width: 940px;
    font-size: 14px;
    line-height: 1.6;
    color: #60758c;
}
.cgs-fulfilment-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}
.cgs-fulfilment-nav a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #d8e3ee;
    color: #294a68;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}
.cgs-fulfilment-nav a.is-active {
    background: #c62828;
    border-color: #c62828;
    color: #fff;
}
.cgs-fulfilment-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-top: 18px;
}
.cgs-fulfilment-kpi {
    padding: 14px 15px;
    border: 1px solid #e3ebf3;
    border-radius: 14px;
    background: #fff;
}
.cgs-fulfilment-kpi__label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #74859a;
}
.cgs-fulfilment-kpi__value {
    margin-top: 8px;
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    color: #102a43;
}
.cgs-fulfilment-shell .btn {
    border-color: #c62828;
    background: #c62828;
    color: #fff;
}
.cgs-fulfilment-shell .btn:hover,
.cgs-fulfilment-shell .btn:focus,
.cgs-fulfilment-shell .btn:active {
    border-color: #9f1f1f;
    background: #9f1f1f;
    color: #fff;
}
</style>

<section class="cgs-fulfilment-shell">
    <div class="cgs-fulfilment-header">
        <div class="cgs-fulfilment-header__eyebrow">Salibay Fulfilment</div>
        <h1 class="cgs-fulfilment-header__title"><?php echo htmlspecialchars($title); ?></h1>
        <p class="cgs-fulfilment-header__subtitle">
            Salibay vendors keep their products in their own stores, while Go Shipping mirrors that stock into a virtual fulfilment warehouse, receives the item after checkout, and then handles delivery, tracking, and last-mile execution end to end.
        </p>

        <div class="cgs-fulfilment-nav">
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/dashboard'); ?>" class="<?php echo $group === 'dashboard' ? 'is-active' : ''; ?>"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/orders'); ?>" class="<?php echo $group === 'orders' ? 'is-active' : ''; ?>"><i class="fa fa-shopping-cart"></i><span>Orders</span></a>
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/inventory'); ?>" class="<?php echo $group === 'inventory' ? 'is-active' : ''; ?>"><i class="fa fa-cubes"></i><span>Virtual Warehouse</span></a>
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/health'); ?>" class="<?php echo $group === 'health' ? 'is-active' : ''; ?>"><i class="fa fa-heartbeat"></i><span>Health & Logs</span></a>
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/riders'); ?>" class="<?php echo $group === 'riders' ? 'is-active' : ''; ?>"><i class="fa fa-motorcycle"></i><span>Riders</span></a>
            <?php if ($can_manage_fulfilment): ?>
            <a href="<?php echo admin_url('courier_goshipping/fulfilment/settings'); ?>" class="<?php echo $group === 'settings' ? 'is-active' : ''; ?>"><i class="fa fa-cogs"></i><span>Settings</span></a>
            <?php endif; ?>
        </div>

        <div class="cgs-fulfilment-kpis">
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Orders Today</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_orders_today"><?php echo (int) ($metrics['orders_today'] ?? 0); ?></div>
            </div>
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Pending Dispatch</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_pending_dispatch"><?php echo (int) ($metrics['pending_dispatch'] ?? 0); ?></div>
            </div>
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Tracked SKUs</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_tracked_skus"><?php echo (int) ($metrics['tracked_skus'] ?? 0); ?></div>
            </div>
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Available Units</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_available_qty"><?php echo number_format((float) ($metrics['virtual_available_qty'] ?? 0), 0); ?></div>
            </div>
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Reserved Units</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_reserved_qty"><?php echo number_format((float) ($metrics['virtual_reserved_qty'] ?? 0), 0); ?></div>
            </div>
            <div class="cgs-fulfilment-kpi">
                <div class="cgs-fulfilment-kpi__label">Virtual Warehouse</div>
                <div class="cgs-fulfilment-kpi__value" id="kpi_warehouse_name" style="font-size:16px;line-height:1.25;"><?php echo htmlspecialchars($virtual_warehouse->warehouse_name ?? 'Not ready'); ?></div>
            </div>
        </div>
    </div>

    <?php echo $group_content ?? ''; ?>
</section>

<script>
(function waitForJQueryFulfilmentLive() {
    if (!window.jQuery) { setTimeout(waitForJQueryFulfilmentLive, 50); return; }

    $(function () {
        var METRICS_URL = <?php echo json_encode(admin_url('courier_goshipping/fulfilment/get_dashboard_metrics_ajax')); ?>;

        function pollMetrics() {
            $.get(METRICS_URL, function (res) {
                if (!res.success) { return; }
                var m = res.metrics;
                $('#kpi_orders_today').text(m.orders_today);
                $('#kpi_pending_dispatch').text(m.pending_dispatch);
                $('#kpi_tracked_skus').text(m.tracked_skus);
                $('#kpi_available_qty').text(Number(m.virtual_available_qty).toLocaleString());
                $('#kpi_reserved_qty').text(Number(m.virtual_reserved_qty).toLocaleString());
                if (res.virtual_warehouse_name) {
                    $('#kpi_warehouse_name').text(res.virtual_warehouse_name);
                }
            }, 'json');
        }

        // New Salibay orders arrive instantly via webhook — this just makes
        // the already-open dashboard/orders page reflect that within a few
        // seconds, without staff needing to hit refresh themselves.
        setInterval(pollMetrics, 3000);
    });
})();
</script>

<?php init_tail(); ?>
