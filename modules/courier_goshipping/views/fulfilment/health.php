<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.sf-health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}
.sf-health-card {
    padding: 18px;
    border: 1px solid #e4ebf3;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}
.sf-health-card h4 {
    margin: 0 0 12px;
    font-size: 16px;
    font-weight: 800;
    color: #17324d;
}
.sf-health-card p {
    margin: 0 0 8px;
    color: #60758c;
}
.sf-health-anchor {
    scroll-margin-top: 120px;
}
</style>

<div id="tab_health" class="sf-health-anchor">
    <div class="sf-health-grid">
        <div class="sf-health-card">
            <h4><i class="fa fa-plug text-danger"></i> Store Connection</h4>
            <p><strong>Status:</strong> <span id="health_api_status">Loading...</span></p>
            <p><strong>Store:</strong> <span id="health_api_store">...</span></p>
            <p><strong>Last Sync:</strong> <span id="health_api_last">...</span></p>
            <?php if ($can_manage_fulfilment): ?>
            <button type="button" class="btn btn-danger" onclick="testFulfilmentApiConnection();">Test Connection</button>
            <?php endif; ?>
        </div>
        <div class="sf-health-card">
            <h4><i class="fa fa-exchange text-danger"></i> Webhook Queue</h4>
            <p><strong>Pending:</strong> <span id="health_wh_pending">...</span></p>
            <p><strong>Failed:</strong> <span id="health_wh_failed">...</span></p>
            <p><strong>Oldest Pending:</strong> <span id="health_wh_oldest">...</span></p>
            <?php if ($can_manage_fulfilment): ?>
            <button type="button" class="btn btn-danger" onclick="retryAllFulfilmentWebhooks();">Retry Failed Events</button>
            <?php endif; ?>
        </div>
        <div class="sf-health-card">
            <h4><i class="fa fa-cubes text-danger"></i> Inventory Sync</h4>
            <p><strong>Last Sync:</strong> <span id="health_inv_last">...</span></p>
            <p><strong>Synced Records:</strong> <span id="health_inv_insync">...</span></p>
            <p><strong>Out of Sync:</strong> <span id="health_inv_outsync">...</span></p>
            <?php if ($can_manage_fulfilment): ?>
            <button type="button" class="btn btn-danger" onclick="runFulfilmentInventorySync();">Run Inventory Sync</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="tab_webhooks" class="panel_s sf-health-anchor">
    <div class="panel-body">
        <div class="row mbot15">
            <div class="col-md-8">
                <h4 class="no-margin">Webhook Queue</h4>
                <p class="text-muted mtop5">Track Salibay order events, retry failures, and inspect raw payloads without leaving the courier module.</p>
            </div>
            <div class="col-md-4 text-right">
                <?php if ($can_manage_fulfilment): ?>
                <button type="button" class="btn btn-danger" onclick="retryAllFulfilmentWebhooks();"><i class="fa fa-refresh"></i> Retry All Failed</button>
                <button type="button" class="btn btn-danger" onclick="clearDoneFulfilmentEvents();"><i class="fa fa-trash"></i> Clear Done</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-fulfilment-webhooks" data-order-col="0" data-order-type="desc">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Topic</th>
                        <th>Order</th>
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

<div id="tab_logs" class="panel_s sf-health-anchor">
    <div class="panel-body">
        <div class="row mbot15">
            <div class="col-md-7">
                <h4 class="no-margin">Integration Logs</h4>
                <p class="text-muted mtop5">Clear analytics for connection health, stock sync, shipment creation, and Salibay event activity.</p>
            </div>
            <div class="col-md-5 text-right">
                <?php if ($can_manage_fulfilment): ?>
                <button type="button" class="btn btn-danger" onclick="generateFulfilmentTestLog();"><i class="fa fa-flask"></i> Test Log</button>
                <button type="button" class="btn btn-danger" onclick="exportFulfilmentLogs();"><i class="fa fa-download"></i> Export CSV</button>
                <button type="button" class="btn btn-danger" onclick="clearFulfilmentLogs();"><i class="fa fa-trash"></i> Clear Logs</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mbot15">
            <div class="col-md-3">
                <select id="log_filter_level" class="selectpicker" data-width="100%">
                    <option value="All">All Levels</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                    <option value="debug">Debug</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="log_filter_category" class="selectpicker" data-width="100%">
                    <option value="All">All Categories</option>
                    <option value="webhook">Webhook</option>
                    <option value="order">Order</option>
                    <option value="shipment">Shipment</option>
                    <option value="inventory">Inventory</option>
                    <option value="diagnostic">Diagnostic</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="log_filter_from" class="form-control" placeholder="From">
            </div>
            <div class="col-md-3">
                <input type="date" id="log_filter_to" class="form-control" placeholder="To">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-fulfilment-logs" data-order-col="0" data-order-type="desc">
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

<div class="modal fade" id="fulfilmentPayloadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Raw Data</h4>
            </div>
            <div class="modal-body">
                <pre style="max-height:420px; overflow:auto;"><code id="fulfilment_payload_content"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function waitForJQueryFulfilmentHealth() {
    if (!window.jQuery) {
        setTimeout(waitForJQueryFulfilmentHealth, 50);
        return;
    }

    $(function () {
        $('.table-fulfilment-webhooks').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: admin_url + 'courier_goshipping/fulfilment/get_webhook_events_datatable',
                type: 'POST',
                data: function(d) {
                    if (typeof csrfData !== 'undefined') {
                        d[csrfData['token_name']] = csrfData['hash'];
                    }
                }
            },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [8] }],
            initComplete: function (settings, json) {
                $(this).parents('.table-loading').removeClass('table-loading');
            }
        });

        $('.table-fulfilment-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: admin_url + 'courier_goshipping/fulfilment/get_logs_datatable',
                type: 'POST',
                data: function (d) {
                    d.level = $('#log_filter_level').val();
                    d.category = $('#log_filter_category').val();
                    d.date_from = $('#log_filter_from').val();
                    d.date_to = $('#log_filter_to').val();
                    if (typeof csrfData !== 'undefined') {
                        d[csrfData['token_name']] = csrfData['hash'];
                    }
                }
            },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [6] }],
            initComplete: function (settings, json) {
                $(this).parents('.table-loading').removeClass('table-loading');
            }
        });

        $('#log_filter_level, #log_filter_category').on('changed.bs.select', reloadFulfilmentLogs);
        $('#log_filter_from, #log_filter_to').on('change', reloadFulfilmentLogs);

        loadFulfilmentHealthStatus();
    });
})();

function reloadFulfilmentLogs() {
    $('.table-fulfilment-logs').DataTable().ajax.reload();
}

function loadFulfilmentHealthStatus() {
    $.get(admin_url + 'courier_goshipping/fulfilment/get_health_status', function (res) {
        if (!res.success) {
            return;
        }

        $('#health_api_status').html(res.api.status === 'Connected'
            ? '<span class="label label-success">Connected</span>'
            : '<span class="label label-danger">Not Connected</span>');
        $('#health_api_store').text(res.api.store || 'Not configured');
        $('#health_api_last').text(res.api.last_success || 'Never');
        $('#health_wh_pending').text(res.webhooks.pending);
        $('#health_wh_failed').text(res.webhooks.failed);
        $('#health_wh_oldest').html(res.webhooks.oldest_warning
            ? '<span class="text-danger">' + res.webhooks.oldest + '</span>'
            : res.webhooks.oldest);
        $('#health_inv_last').text(res.inventory.last_sync || 'Never');
        $('#health_inv_insync').text(res.inventory.in_sync);
        $('#health_inv_outsync').text(res.inventory.out_sync);
    }, 'json');
}

function testFulfilmentApiConnection() {
    $.get(admin_url + 'courier_goshipping/fulfilment/test_connection', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Connection test finished.');
        loadFulfilmentHealthStatus();
    }, 'json');
}

function requeueFulfilmentWebhook(id) {
    $.post(admin_url + 'courier_goshipping/fulfilment/requeue_webhook_event/' + id, function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        $('.table-fulfilment-webhooks').DataTable().ajax.reload(null, false);
        loadFulfilmentHealthStatus();
    }, 'json');
}

function retryAllFulfilmentWebhooks() {
    $.post(admin_url + 'courier_goshipping/fulfilment/retry_all_failed_webhooks', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        $('.table-fulfilment-webhooks').DataTable().ajax.reload(null, false);
        loadFulfilmentHealthStatus();
    }, 'json');
}

function clearDoneFulfilmentEvents() {
    if (!confirm('Clear all processed webhook events?')) {
        return;
    }

    $.post(admin_url + 'courier_goshipping/fulfilment/clear_done_events', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        $('.table-fulfilment-webhooks').DataTable().ajax.reload(null, false);
        loadFulfilmentHealthStatus();
    }, 'json');
}

function viewFulfilmentPayload(id, type) {
    $.get(admin_url + 'courier_goshipping/fulfilment/get_raw_data/' + type + '/' + id, function (res) {
        if (!res.success) {
            alert_float('danger', res.message || 'Payload could not be loaded.');
            return;
        }

        $('#fulfilment_payload_content').text(JSON.stringify(res.data || {}, null, 2));
        $('#fulfilmentPayloadModal').modal('show');
    }, 'json');
}

function clearFulfilmentLogs() {
    if (!confirm('Clear all integration logs?')) {
        return;
    }

    $.post(admin_url + 'courier_goshipping/fulfilment/clear_logs', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        $('.table-fulfilment-logs').DataTable().ajax.reload(null, false);
    }, 'json');
}

function generateFulfilmentTestLog() {
    $.post(admin_url + 'courier_goshipping/fulfilment/generate_test_log', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        $('.table-fulfilment-logs').DataTable().ajax.reload(null, false);
    }, 'json');
}

function exportFulfilmentLogs() {
    var url = admin_url + 'courier_goshipping/fulfilment/export_logs_csv'
        + '?level=' + encodeURIComponent($('#log_filter_level').val() || 'All')
        + '&category=' + encodeURIComponent($('#log_filter_category').val() || 'All')
        + '&date_from=' + encodeURIComponent($('#log_filter_from').val() || '')
        + '&date_to=' + encodeURIComponent($('#log_filter_to').val() || '');

    window.location.href = url;
}

function runFulfilmentInventorySync() {
    $.post(admin_url + 'courier_goshipping/fulfilment/run_inventory_sync', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Inventory sync finished.');
        loadFulfilmentHealthStatus();
    }, 'json');
}
</script>
