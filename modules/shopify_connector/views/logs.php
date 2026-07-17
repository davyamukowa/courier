<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- SECTION 3: HEALTH DASHBOARD -->
<div class="row mtop20">
    <div class="col-md-12">
        <h4 class="no-margin">System Health</h4>
        <p class="text-muted mtop5">Module version: <?php echo defined('SHOPIFY_CONNECTOR_VERSION') ? SHOPIFY_CONNECTOR_VERSION : 'unknown'; ?></p>
        <hr class="hr-panel-heading" />
    </div>
    
    <!-- Panel 1: Shopify API -->
    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><i class="fa fa-plug text-primary"></i> Shopify API</h4>
                <hr class="hr-panel-heading" />
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status
                        <span class="label" id="health_api_status">Loading...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Last Success
                        <span class="text-muted" id="health_api_last">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Rate Limit
                        <span class="text-muted" id="health_api_rate">...</span>
                    </li>
                </ul>
                <button class="btn btn-default btn-block mtop10" onclick="test_api_connection(); return false;">Test Now</button>
            </div>
        </div>
    </div>

    <!-- Panel 2: Webhook Queue -->
    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><i class="fa fa-exchange text-warning"></i> Webhook Queue</h4>
                <hr class="hr-panel-heading" />
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pending Depth
                        <span class="badge rounded-pill" id="health_wh_pending">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Failed Needs Attention
                        <span class="badge rounded-pill" id="health_wh_failed">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Oldest Pending
                        <span class="text-muted" id="health_wh_oldest">...</span>
                    </li>
                </ul>
                <button class="btn btn-default btn-block mtop10" onclick="process_webhook_queue(); return false;">Process Now</button>
            </div>
        </div>
    </div>

    <!-- Panel 3: Inventory Sync -->
    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><i class="fa fa-cubes text-success"></i> Inventory Sync</h4>
                <hr class="hr-panel-heading" />
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Last Sync
                        <span class="text-muted" id="health_inv_last">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Items In Sync
                        <span class="badge rounded-pill bg-success" id="health_inv_insync">...</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Items Out of Sync
                        <span class="badge rounded-pill" id="health_inv_outsync">...</span>
                    </li>
                </ul>
                <button class="btn btn-default btn-block mtop10" onclick="run_inventory_sync(); return false;">Sync Now</button>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: WEBHOOK QUEUE STATUS -->
<div class="row mtop20">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin">Webhook Events Queue</h4>
                <hr class="hr-panel-heading" />

                <!-- Summary Bar & Bulk Actions -->
                <div class="row mbot20">
                    <div class="col-md-6">
                        <strong>Summary:</strong>
                        Pending: <span id="summ_pending" class="text-muted">0</span> |
                        Processing: <span id="summ_processing" class="text-warning">0</span> |
                        Done: <span id="summ_done" class="text-success">0</span> |
                        Failed: <span id="summ_failed" class="text-danger">0</span>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-info btn-sm" onclick="process_webhook_queue();"><i class="fa fa-play"></i> Process Queue Now</button>
                        <button class="btn btn-warning btn-sm" onclick="retry_all_failed_webhooks();"><i class="fa fa-refresh"></i> Retry All Failed</button>
                        <button class="btn btn-default btn-sm" onclick="clear_done_events();"><i class="fa fa-trash"></i> Clear Done Events</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-shopify-webhooks dt-table" data-order-col="0" data-order-type="desc">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Topic</th>
                                <th>Shopify Order ID</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Created</th>
                                <th>Processed</th>
                                <th>Error</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: INTEGRATION LOGS -->
<div class="row mtop20">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin">Integration Logs</h4>
                <hr class="hr-panel-heading" />

                <!-- Filters -->
                <div class="row mbot20">
                    <div class="col-md-3">
                        <select id="log_filter_level" class="selectpicker" data-width="100%">
                            <option value="All">All Levels</option>
                            <option value="info" data-content="<span class='label label-default'>Info</span>">Info</option>
                            <option value="warning" data-content="<span class='label label-warning'>Warning</span>">Warning</option>
                            <option value="error" data-content="<span class='label label-danger'>Error</span>">Error</option>
                            <option value="debug" data-content="<span class='label label-info'>Debug</span>">Debug</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="log_filter_category" class="selectpicker" data-width="100%">
                            <option value="All">All Categories</option>
                            <option value="webhook">Webhook</option>
                            <option value="order">Order</option>
                            <option value="shipment">Shipment</option>
                            <option value="inventory">Inventory</option>
                            <option value="accounting">Accounting</option>
                            <option value="notification">Notification</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="date" id="log_filter_from" class="form-control" placeholder="From">
                            <div class="input-group-addon">to</div>
                            <input type="date" id="log_filter_to" class="form-control" placeholder="To">
                        </div>
                    </div>
                    <div class="col-md-3 text-right">
                        <div class="checkbox checkbox-primary checkbox-inline" style="margin-top: 6px; margin-right: 15px;">
                            <input type="checkbox" id="log_auto_refresh" checked>
                            <label for="log_auto_refresh">Auto-refresh (30s)</label>
                        </div>
                        <button class="btn btn-default" onclick="$('.table-shopify-logs').DataTable().ajax.reload();"><i class="fa fa-filter"></i> Filter</button>
                    </div>
                </div>

                <div class="row mbot20">
                    <div class="col-md-12 text-right">
                        <button class="btn btn-info btn-sm" onclick="generate_test_log();"><i class="fa fa-flask"></i> Write Test Log</button>
                        <button class="btn btn-default btn-sm" onclick="export_logs_csv();"><i class="fa fa-download"></i> Export CSV</button>
                        <button class="btn btn-danger btn-sm" onclick="clear_all_logs();"><i class="fa fa-trash"></i> Clear All Logs</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-shopify-logs dt-table" data-order-col="0" data-order-type="desc">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Level</th>
                                <th>Category</th>
                                <th>Message</th>
                                <th>Context</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Payload / Context Modal -->
<div class="modal fade" id="payloadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Raw Data</h4>
            </div>
            <div class="modal-body">
                <pre><code id="payload_content"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.log-row-info td { background-color: #fff; }
.log-row-warning td { background-color: #fcf8e3; }
.log-row-error td { background-color: #f2dede; }
.log-row-debug td { background-color: #f5f5f5; }
</style>

<script>
(function waitForJQueryLogs(){
    if (window.jQuery) { initShopifyLogsPage(); } else { setTimeout(waitForJQueryLogs, 50); }
})();

function initShopifyLogsPage() {
$(function(){
    // Initialize Webhooks Table
    if ($('.table-shopify-webhooks').length > 0) {
        $('.table-shopify-webhooks').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": admin_url + "shopify_connector/get_webhook_events_datatable",
                "type": "POST"
            },
            "order": [[0, 'desc']],
            "columnDefs": [ { "orderable": false, "targets": [8] } ]
        });
    }

    // Initialize Logs Table
    if ($('.table-shopify-logs').length > 0) {
        $('.table-shopify-logs').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": admin_url + "shopify_connector/get_logs_datatable",
                "type": "POST",
                "data": function(d) {
                    d.level = $('#log_filter_level').val();
                    d.category = $('#log_filter_category').val();
                    d.date_from = $('#log_filter_from').val();
                    d.date_to = $('#log_filter_to').val();
                }
            },
            "order": [[0, 'desc']],
            "columnDefs": [ { "orderable": false, "targets": [6] } ],
            "createdRow": function( row, data, dataIndex ) {
                var lvl = $(data[1]).text().toLowerCase();
                $(row).addClass('log-row-' + lvl);
            }
        });
    }

    // Auto Refresh Logic
    setInterval(function(){
        if($('#log_auto_refresh').is(':checked') && $('.table-shopify-logs').length > 0) {
            $('.table-shopify-logs').DataTable().ajax.reload(null, false);
            $('.table-shopify-webhooks').DataTable().ajax.reload(null, false);
            load_health_dashboard();
        }
    }, 30000);

    // Initial Health Load
    load_health_dashboard();
});
}

function load_health_dashboard() {
    $.get(admin_url + 'shopify_connector/get_health_status', function(res){
        if(res.success) {
            // API
            var a_badge = res.api.status === 'Connected' ? 'label-success' : 'label-danger';
            $('#health_api_status').html('<span class="label ' + a_badge + '">' + res.api.status + '</span>');
            $('#health_api_last').text(res.api.last_success);
            $('#health_api_rate').text(res.api.rate_limit);
            
            // Webhooks
            $('#health_wh_pending').text(res.webhooks.pending);
            $('#health_wh_failed').text(res.webhooks.failed);
            $('#health_wh_failed').removeClass('bg-danger bg-success').addClass(res.webhooks.failed > 0 ? 'bg-danger' : 'bg-success');
            var o_badge = res.webhooks.oldest_warning ? '<span class="text-danger">' + res.webhooks.oldest + '</span>' : res.webhooks.oldest;
            $('#health_wh_oldest').html(o_badge);

            $('#summ_pending').text(res.webhooks.pending);
            $('#summ_processing').text(res.webhooks.processing);
            $('#summ_done').text(res.webhooks.done);
            $('#summ_failed').text(res.webhooks.failed);
            
            // Inventory
            $('#health_inv_last').text(res.inventory.last_sync);
            $('#health_inv_insync').text(res.inventory.in_sync);
            $('#health_inv_outsync').text(res.inventory.out_sync);
            $('#health_inv_outsync').removeClass('bg-danger bg-default').addClass(res.inventory.out_sync > 0 ? 'bg-danger' : 'bg-default');
        }
    }, 'json');
}

function test_api_connection() {
    $.post(admin_url + 'shopify_connector/test_api_connection', function(res){
        alert(res.message);
        load_health_dashboard();
    }, 'json');
}

function requeue_webhook_event(id) {
    $.post(admin_url + 'shopify_connector/requeue_webhook_event/' + id, function(res){
        $('.table-shopify-webhooks').DataTable().ajax.reload(null, false);
        load_health_dashboard();
    }, 'json');
}

function process_webhook_queue() {
    $.post(admin_url + 'shopify_connector/process_queue_ajax', function(res){
        alert(res.message);
        $('.table-shopify-webhooks').DataTable().ajax.reload(null, false);
        load_health_dashboard();
    }, 'json');
}

function retry_all_failed_webhooks() {
    if(confirm('Retry all failed webhooks?')) {
        $.post(admin_url + 'shopify_connector/retry_all_failed_webhooks', function(res){
            alert(res.message);
            $('.table-shopify-webhooks').DataTable().ajax.reload(null, false);
            load_health_dashboard();
        }, 'json');
    }
}

function clear_done_events() {
    if(confirm('Clear all processed (done) webhook events?')) {
        $.post(admin_url + 'shopify_connector/clear_done_events', function(res){
            $('.table-shopify-webhooks').DataTable().ajax.reload(null, false);
            load_health_dashboard();
        }, 'json');
    }
}

function view_payload(id, type) {
    $.get(admin_url + 'shopify_connector/get_raw_data/' + type + '/' + id, function(res){
        $('#payload_content').text(JSON.stringify(res.data, null, 2));
        $('#payloadModal').modal('show');
    }, 'json');
}

function clear_all_logs() {
    if(confirm('Are you sure you want to completely clear all integration logs? This cannot be undone.')) {
        $.post(admin_url + 'shopify_connector/clear_logs', function(res){
            $('.table-shopify-logs').DataTable().ajax.reload(null, false);
        }, 'json');
    }
}

function generate_test_log() {
    $.post(admin_url + 'shopify_connector/generate_test_log', function(res){
        alert(res.message);
        $('.table-shopify-logs').DataTable().ajax.reload(null, false);
        load_health_dashboard();
    }, 'json');
}

function export_logs_csv() {
    var lvl = $('#log_filter_level').val() || 'All';
    var cat = $('#log_filter_category').val() || 'All';
    var d_from = $('#log_filter_from').val() || '';
    var d_to = $('#log_filter_to').val() || '';
    
    var url = admin_url + 'shopify_connector/export_logs_csv?level=' + lvl + '&category=' + cat + '&date_from=' + d_from + '&date_to=' + d_to;
    window.location.href = url;
}
</script>
