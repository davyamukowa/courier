<?php
/**
 * Shopify Connector Dashboard
 */
defined('BASEPATH') or exit('No direct script access allowed');
init_head(); ?>

<style>
    /* Premium Modern Dashboard Styles */
    :root {
        --dash-blue-start: #4facfe;
        --dash-blue-end: #00f2fe;
        --dash-orange-start: #ff0844;
        --dash-orange-end: #ffb199;
        --dash-green-start: #43e97b;
        --dash-green-end: #38f9d7;
        --dash-purple-start: #a18cd1;
        --dash-purple-end: #fbc2eb;
        --card-radius: 16px;
    }

    .sh-dashboard-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* KPI Cards */
    .sh-kpi-card {
        border-radius: var(--card-radius);
        padding: 25px 20px;
        color: white;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        border: none;
        margin-bottom: 20px;
    }

    .sh-kpi-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .sh-kpi-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
        transform: scale(2);
        transition: transform 0.5s ease;
    }

    .sh-kpi-card:hover::after {
        transform: scale(2.5) translate(-10%, 10%);
    }

    .sh-kpi-blue { background: linear-gradient(135deg, var(--dash-blue-start) 0%, var(--dash-blue-end) 100%); }
    .sh-kpi-orange { background: linear-gradient(135deg, var(--dash-orange-start) 0%, var(--dash-orange-end) 100%); }
    .sh-kpi-green { background: linear-gradient(135deg, var(--dash-green-start) 0%, var(--dash-green-end) 100%); }
    .sh-kpi-purple { background: linear-gradient(135deg, var(--dash-purple-start) 0%, var(--dash-purple-end) 100%); }

    .sh-kpi-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        opacity: 0.9;
        margin-top: 10px;
        margin-bottom: 0;
    }

    .sh-kpi-value {
        font-size: 38px;
        font-weight: 800;
        margin: 0;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .sh-kpi-icon {
        position: absolute;
        right: 20px;
        bottom: 10px;
        font-size: 60px;
        opacity: 0.15;
        transform: rotate(-10deg);
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .sh-kpi-card:hover .sh-kpi-icon {
        transform: rotate(0deg) scale(1.1);
        opacity: 0.25;
    }

    /* Panel Styling */
    .sh-panel {
        border-radius: var(--card-radius);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        background: #fff;
        margin-bottom: 25px;
    }
    .sh-panel-header {
        padding: 20px 25px 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .sh-panel-header h4 {
        margin: 0;
        font-weight: 600;
        color: #333;
        font-size: 16px;
    }
    .sh-panel-body {
        padding: 25px;
    }

    /* Nav Tabs Styling */
    .sh-tabs.nav-tabs {
        border-bottom: none;
        margin-bottom: 20px;
        display: flex;
        background: #f8f9fa;
        padding: 5px;
        border-radius: 12px;
        width: fit-content;
    }
    .sh-tabs.nav-tabs > li {
        margin-bottom: 0;
    }
    .sh-tabs.nav-tabs > li > a {
        border: none !important;
        background: transparent !important;
        border-radius: 8px !important;
        color: #6c757d;
        font-weight: 600;
        padding: 10px 25px;
        transition: all 0.2s ease;
    }
    .sh-tabs.nav-tabs > li.active > a, 
    .sh-tabs.nav-tabs > li > a:hover {
        background: #fff !important;
        color: #2196f3 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    /* List group premium styling */
    .sh-list-group .list-group-item {
        border: none;
        border-bottom: 1px solid #f4f5f7;
        padding: 15px 10px;
        transition: background-color 0.2s ease;
    }
    .sh-list-group .list-group-item:last-child {
        border-bottom: none;
    }
    .sh-list-group .list-group-item:hover {
        background-color: #fcfcfc;
    }
    .sh-badge {
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* Buttons */
    .sh-btn-primary {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        color: white;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .sh-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        color: white;
    }

    .sh-btn-outline {
        border: 2px solid #e2e8f0;
        background: transparent;
        color: #64748b;
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 18px;
        transition: all 0.2s;
    }
    .sh-btn-outline:hover {
        border-color: #94a3b8;
        color: #475569;
        background: #f8fafc;
    }

</style>

<div id="wrapper" class="sh-dashboard-container">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s" style="background: transparent; border: none; box-shadow: none;">
                    <div class="panel-body" style="padding: 0;">
                        
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs sh-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab_dashboard" aria-controls="tab_dashboard" role="tab" data-toggle="tab"><i class="fa fa-dashboard mright5"></i> Dashboard</a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_orders" aria-controls="tab_orders" role="tab" data-toggle="tab"><i class="fa fa-shopping-cart mright5"></i> Orders</a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_logs" aria-controls="tab_logs" role="tab" data-toggle="tab"><i class="fa fa-terminal mright5"></i> Logs & Health</a>
                            </li>
                            <li role="presentation">
                                <a href="<?php echo admin_url('shopify_connector/settings'); ?>"><i class="fa fa-cog mright5"></i> Settings</a>
                            </li>
                        </ul>

                        <!-- Tab Panes -->
                        <div class="tab-content">
                            
                            <!-- DASHBOARD TAB -->
                            <div role="tabpanel" class="tab-pane active" id="tab_dashboard">
                                
                                <!-- ROW 1: KPI Cards -->
                                <div class="row mtop20">
                                    <div class="col-md-3">
                                        <div class="sh-kpi-card sh-kpi-blue">
                                            <i class="fa fa-shopping-cart sh-kpi-icon"></i>
                                            <p class="sh-kpi-value"><?php echo $orders_today; ?></p>
                                            <p class="sh-kpi-title">Total Orders Today</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sh-kpi-card sh-kpi-orange">
                                            <i class="fa fa-clock-o sh-kpi-icon"></i>
                                            <p class="sh-kpi-value"><?php echo $pending_dispatch; ?></p>
                                            <p class="sh-kpi-title">Pending Dispatch</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sh-kpi-card sh-kpi-green">
                                            <i class="fa fa-check-circle sh-kpi-icon"></i>
                                            <p class="sh-kpi-value"><?php echo $delivered_today; ?></p>
                                            <p class="sh-kpi-title">Delivered Today</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sh-kpi-card sh-kpi-purple">
                                            <i class="fa fa-refresh sh-kpi-icon"></i>
                                            <p class="sh-kpi-value" style="font-size: 24px; margin-top:10px; margin-bottom: 4px;">
                                                <?php echo $last_sync !== 'Never' ? time_ago($last_sync) : 'Never'; ?>
                                            </p>
                                            <p class="sh-kpi-title">Last Inventory Sync</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- ROW 2: Charts and Health -->
                                <div class="row mtop20">
                                    <div class="col-md-8">
                                        <div class="sh-panel">
                                            <div class="sh-panel-header">
                                                <h4><i class="fa fa-bar-chart mright5 text-info"></i> Orders by Status</h4>
                                            </div>
                                            <div class="sh-panel-body">
                                                <div style="position: relative; height:300px; width:100%">
                                                    <canvas id="ordersChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="sh-panel">
                                            <div class="sh-panel-header">
                                                <h4><i class="fa fa-heartbeat mright5 text-danger"></i> Integration Health</h4>
                                            </div>
                                            <div class="sh-panel-body">
                                                <ul class="list-group sh-list-group">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Webhook Events Pending
                                                        <span class="label sh-badge <?php echo $webhook_pending > 0 ? 'label-warning' : 'label-default'; ?> rounded-pill pull-right"><?php echo $webhook_pending; ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Webhook Events Failed
                                                        <span class="label sh-badge <?php echo $webhook_failed > 0 ? 'label-danger' : 'label-success'; ?> rounded-pill pull-right"><?php echo $webhook_failed; ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Last Successful Order
                                                        <span class="text-muted pull-right"><strong><?php echo $last_order_time; ?></strong></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Last Inventory Sync
                                                        <span class="text-muted pull-right"><strong><?php echo $last_sync !== 'Never' ? _dt($last_sync) : 'Never'; ?></strong></span>
                                                    </li>
                                                </ul>
                                                <?php if (has_permission('shopify_connector', '', 'manage')) { ?>
                                                    <button class="btn sh-btn-primary btn-block mtop20" onclick="run_inventory_sync(); return false;"><i class="fa fa-refresh mright5"></i> Sync Inventory Now</button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ORDERS TAB -->
                            <div role="tabpanel" class="tab-pane" id="tab_orders">
                                <div class="sh-panel mtop20">
                                    <div class="sh-panel-body">
                                        <!-- Filters -->
                                        <div class="row mbot20">
                                            <div class="col-md-3">
                                                <select id="filter_status" class="selectpicker" data-width="100%" multiple data-none-selected-text="Filter by Status">
                                                    <option value="pending">Pending</option>
                                                    <option value="confirmed">Confirmed</option>
                                                    <option value="processing">Processing</option>
                                                    <option value="dispatched">Dispatched</option>
                                                    <option value="delivered">Delivered</option>
                                                    <option value="cancelled">Cancelled</option>
                                                    <option value="returned">Returned</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select id="filter_model" class="selectpicker" data-width="100%">
                                                    <option value="All">All Fulfillment Models</option>
                                                    <option value="Model A">Model A</option>
                                                    <option value="Model B">Model B</option>
                                                    <option value="Model C">Model C</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="date" id="filter_date_from" class="form-control" placeholder="From">
                                                    <div class="input-group-addon">to</div>
                                                    <input type="date" id="filter_date_to" class="form-control" placeholder="To">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-default btn-block" onclick="$('.table-shopify-orders').DataTable().ajax.reload();">
                                                    <i class="fa fa-filter"></i> Filter
                                                </button>
                                            </div>
                                        </div>

                                        <div class="clearfix mbot20">
                                            <button type="button" class="btn sh-btn-outline pull-right" onclick="export_orders_csv(); return false;">
                                                <i class="fa fa-download mright5"></i> Export CSV
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-shopify-orders dt-table" data-order-col="0" data-order-type="desc">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Shopify #</th>
                                                        <th>Customer</th>
                                                        <th>Items</th>
                                                        <th>Total</th>
                                                        <th>Model</th>
                                                        <th>Status</th>
                                                        <th>GS Shipment</th>
                                                        <th>Tracking</th>
                                                        <th>Created</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- LOGS TAB (Prompt 17) -->
                            <div role="tabpanel" class="tab-pane" id="tab_logs">
                                <?php $this->load->view('logs'); ?>
                            </div>

                        </div> <!-- End Tab Panes -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: var(--card-radius); overflow:hidden;">
            <div class="modal-header" style="background:#f8f9fa; border-bottom:1px solid #eaeaea;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="font-weight:600;"><i class="fa fa-shopping-bag mright5 text-primary"></i> Order Details <span id="modal_order_number" class="label label-primary mleft5"></span></h4>
            </div>
            <div class="modal-body" style="background: #fff;">
                
                <ul class="nav nav-tabs sh-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#mdl_info" aria-controls="mdl_info" role="tab" data-toggle="tab">Order Info</a></li>
                    <li role="presentation"><a href="#mdl_items" aria-controls="mdl_items" role="tab" data-toggle="tab">Line Items</a></li>
                    <li role="presentation"><a href="#mdl_shipment" aria-controls="mdl_shipment" role="tab" data-toggle="tab">Shipment & Tracking</a></li>
                    <li role="presentation"><a href="#mdl_accounting" aria-controls="mdl_accounting" role="tab" data-toggle="tab">Accounting</a></li>
                    <li role="presentation"><a href="#mdl_raw" aria-controls="mdl_raw" role="tab" data-toggle="tab">Raw Payload</a></li>
                </ul>

                <div class="tab-content mtop20">
                    <div role="tabpanel" class="tab-pane active" id="mdl_info">
                        <div class="row" id="mdl_info_content">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mdl_items">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Fulfillment</th>
                                    </tr>
                                </thead>
                                <tbody id="mdl_items_body">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mdl_shipment">
                        <div id="mdl_shipment_content"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mdl_accounting">
                        <div id="mdl_accounting_content"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="mdl_raw">
                        <pre style="background:#282c34; color:#abb2bf; border-radius:8px; border:none;"><code id="mdl_raw_content"></code></pre>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="background:#f8f9fa; border-top:1px solid #eaeaea;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function waitForJQueryDashboard(){
    if (window.jQuery) { initShopifyDashboardPage(); } else { setTimeout(waitForJQueryDashboard, 50); }
})();

function initShopifyDashboardPage() {
$(function(){
    if (window.location.hash) {
        var $tabLink = $('.sh-tabs a[href="' + window.location.hash + '"]');
        if ($tabLink.length) {
            $tabLink.tab('show');
        }
    }

    $('.sh-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target && target.charAt(0) === '#') {
            window.location.hash = target;
        }
    });

    // Initialize DataTables manually because we want custom filters easily passed
    if ($('.table-shopify-orders').length > 0) {
        var tOpts = {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": admin_url + "shopify_connector/get_orders_datatable",
                "type": "POST",
                "data": function(d) {
                    d.status = $('#filter_status').val();
                    d.model = $('#filter_model').val();
                    d.date_from = $('#filter_date_from').val();
                    d.date_to = $('#filter_date_to').val();
                    // CSRF handled by Perfex ajax setup
                }
            },
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [10] }
            ]
        };
        $('.table-shopify-orders').DataTable(tOpts);
    }

    // Load Chart
    $.get(admin_url + 'shopify_connector/get_order_stats', function(res){
        var ctx = document.getElementById('ordersChart').getContext('2d');
        
        // Handle empty data scenario
        var dataValues = res.data;
        var hasData = dataValues.some(function(val) { return val > 0; });
        if (!hasData) {
            // Provide placeholder data so the chart doesn't look broken
            res.labels = ['No Data Yet'];
            dataValues = [1];
        }

        var gradient = ctx.createLinearGradient(0, 0, 400, 0);
        gradient.addColorStop(0, '#4facfe');
        gradient.addColorStop(1, '#00f2fe');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: res.labels,
                datasets: [{
                    label: hasData ? 'Orders' : 'Awaiting Orders',
                    data: dataValues,
                    backgroundColor: hasData ? gradient : '#e2e8f0',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { 
                        beginAtZero: true,
                        grid: { display: false }
                    },
                    y: {
                        grid: { borderDash: [5, 5] }
                    }
                }
            }
        });
    }, 'json');
});
}

function export_orders_csv() {
    var status = $('#filter_status').val() || '';
    var model = $('#filter_model').val() || 'All';
    var d_from = $('#filter_date_from').val() || '';
    var d_to = $('#filter_date_to').val() || '';
    
    var url = admin_url + 'shopify_connector/export_orders_csv?model=' + model + '&date_from=' + d_from + '&date_to=' + d_to;
    if (status) {
        url += '&status=' + status.join(',');
    }
    window.location.href = url;
}

function run_inventory_sync() {
    if(confirm('Run inventory sync now? This might take a while depending on product count.')) {
        $.post(admin_url + 'shopify_connector/run_inventory_sync', function(res){
            alert(res.message);
            window.location.reload();
        }, 'json');
    }
}

function view_order_details(id) {
    $.get(admin_url + 'shopify_connector/get_order_detail/' + id, function(res){
        if (!res.success) {
            alert(res.message);
            return;
        }

        var order = res.order;
        $('#modal_order_number').text('#' + order.shopify_order_number);
        
        // Info Tab
        var info = '<div class="col-md-6">';
        info += '<p><strong>Customer:</strong> ' + order.customer_name + '</p>';
        info += '<p><strong>Email:</strong> ' + order.customer_email + '</p>';
        info += '<p><strong>Phone:</strong> ' + order.customer_phone + '</p>';
        info += '</div><div class="col-md-6">';
        info += '<p><strong>Total:</strong> ' + order.currency + ' ' + order.total_price + '</p>';
        info += '<p><strong>Status:</strong> <span class="label label-info">' + order.order_status + '</span></p>';
        info += '<p><strong>Model:</strong> ' + order.fulfillment_model + '</p>';
        info += '</div>';
        $('#mdl_info_content').html(info);

        // Items Tab
        var itemsHtml = '';
        if (res.items && res.items.length > 0) {
            res.items.forEach(function(item){
                itemsHtml += '<tr>';
                itemsHtml += '<td>' + item.sku + '</td>';
                itemsHtml += '<td>' + item.name + '</td>';
                itemsHtml += '<td><span class="label label-primary">' + item.quantity + '</span></td>';
                itemsHtml += '<td>' + order.currency + ' ' + item.price + '</td>';
                itemsHtml += '<td><span class="label label-default">' + item.fulfillment_model + '</span></td>';
                itemsHtml += '</tr>';
            });
        } else {
            itemsHtml = '<tr><td colspan="5" class="text-center">No items found in DB (Check raw payload)</td></tr>';
        }
        $('#mdl_items_body').html(itemsHtml);

        // Shipment Tab
        var trackHtml = '<div class="well well-sm"><p class="no-margin"><strong>GS Shipment ID:</strong> ' + (order.gs_shipment_id || 'None') + '</p>';
        trackHtml += '<p class="no-margin mtop5"><strong>Tracking Number:</strong> <span class="text-info">' + (order.tracking_number || 'None') + '</span></p></div>';
        if (res.tracking && res.tracking.length > 0) {
            trackHtml += '<ul class="list-group sh-list-group mtop15">';
            res.tracking.forEach(function(t){
                trackHtml += '<li class="list-group-item"><span class="text-muted">[' + t.created_at + ']</span> <strong class="mleft5">' + t.status + '</strong> <span class="mleft5">- ' + t.description + '</span></li>';
            });
            trackHtml += '</ul>';
        } else {
            trackHtml += '<p class="text-muted mtop15">No tracking history available.</p>';
        }
        $('#mdl_shipment_content').html(trackHtml);

        // Accounting Tab
        var accHtml = '';
        if (res.accounting && res.accounting.length > 0) {
            accHtml += '<table class="table table-bordered table-striped"><thead><tr><th>Date</th><th>Type</th><th>Account</th><th>Debit</th><th>Credit</th></tr></thead><tbody>';
            res.accounting.forEach(function(a){
                accHtml += '<tr><td>' + a.created_at + '</td><td><span class="label label-default">' + a.entry_type + '</span></td><td>' + a.account_name + '</td><td class="text-success">' + a.debit + '</td><td class="text-danger">' + a.credit + '</td></tr>';
            });
            accHtml += '</tbody></table>';
        } else {
            accHtml = '<p class="text-muted">No accounting entries found.</p>';
        }
        $('#mdl_accounting_content').html(accHtml);

        // Raw Tab
        $('#mdl_raw_content').text(JSON.stringify(JSON.parse(order.raw_payload), null, 2));

        $('#orderDetailModal').modal('show');
    }, 'json');
}

function cancel_order(id) {
    if(confirm('Are you sure you want to cancel this order? This will release reservations and update Shopify.')){
        alert('Cancel action will be implemented in future phase.');
    }
}

function create_shipment(id) {
    if(confirm('Create Go Shipping shipment for this order?')){
        alert('Calling internal shipment creation... (To be hooked up to endpoint)');
    }
}
</script>
</body>
</html>
