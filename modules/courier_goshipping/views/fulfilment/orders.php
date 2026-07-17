<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="row mbot15">
            <div class="col-md-3">
                <select id="sf_filter_status" class="selectpicker" data-width="100%" multiple data-none-selected-text="Filter by Status">
                    <option value="pending" <?php echo $prefilter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $prefilter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="processing" <?php echo $prefilter_status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="dispatched" <?php echo $prefilter_status === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                    <option value="delivered" <?php echo $prefilter_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $prefilter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="returned" <?php echo $prefilter_status === 'returned' ? 'selected' : ''; ?>>Returned</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="sf_filter_model" class="selectpicker" data-width="100%">
                    <option value="All">All Models</option>
                    <option value="A">Model A</option>
                    <option value="B">Model B</option>
                    <option value="C">Model C</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="sf_filter_date_from" class="form-control" placeholder="From date">
            </div>
            <div class="col-md-3">
                <input type="date" id="sf_filter_date_to" class="form-control" placeholder="To date">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-default btn-block" onclick="reloadFulfilmentOrders();"><i class="fa fa-filter"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-fulfilment-orders" data-order-col="0" data-order-type="desc">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Salibay #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Courier Shipment</th>
                        <th>Driver / Trip</th>
                        <th>Tracking</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="fulfilmentOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Salibay Order Detail</h4>
            </div>
            <div class="modal-body">
                <div id="fulfilment_order_summary" class="row"></div>
                <hr>
                <h5>Line Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Model</th>
                            </tr>
                        </thead>
                        <tbody id="fulfilment_order_items"></tbody>
                    </table>
                </div>
                <h5>Tracking History</h5>
                <div id="fulfilment_order_tracking" class="well well-sm text-muted">No tracking history available.</div>
                <h5>Raw Payload</h5>
                <pre style="max-height:240px; overflow:auto;"><code id="fulfilment_order_payload"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function waitForJQueryFulfilmentOrders() {
    if (!window.jQuery) {
        setTimeout(waitForJQueryFulfilmentOrders, 50);
        return;
    }

    $(function () {
        if ($('#sf_filter_status option:selected').length) {
            $('#sf_filter_status').selectpicker('refresh');
        }

        $('.table-fulfilment-orders').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: admin_url + 'courier_goshipping/fulfilment/get_orders_datatable',
                type: 'POST',
                data: function (d) {
                    d.status = $('#sf_filter_status').val();
                    d.model = $('#sf_filter_model').val();
                    d.date_from = $('#sf_filter_date_from').val();
                    d.date_to = $('#sf_filter_date_to').val();
                    if (typeof csrfData !== 'undefined') {
                        d[csrfData['token_name']] = csrfData['hash'];
                    }
                }
            },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [8, 11] }],
            initComplete: function (settings, json) {
                $(this).parents('.table-loading').removeClass('table-loading');
            }
        });
    });
})();

function reloadFulfilmentOrders() {
    $('.table-fulfilment-orders').DataTable().ajax.reload();
}

function viewFulfilmentOrder(id) {
    $.get(admin_url + 'courier_goshipping/fulfilment/get_order_detail/' + id, function (res) {
        if (!res.success) {
            alert_float('danger', res.message || 'Order not found.');
            return;
        }

        var order = res.order;
        var summary = '';
        summary += '<div class="col-md-6">';
        summary += '<p><strong>Order:</strong> #' + (order.shopify_order_number || '') + '</p>';
        summary += '<p><strong>Customer:</strong> ' + (order.customer_name || '') + '</p>';
        summary += '<p><strong>Email:</strong> ' + (order.customer_email || '') + '</p>';
        summary += '</div>';
        summary += '<div class="col-md-6">';
        summary += '<p><strong>Status:</strong> ' + (order.order_status || '') + '</p>';
        summary += '<p><strong>Shipment:</strong> ' + (order.gs_shipment_id ? '<a href="' + admin_url + 'courier_goshipping/shipments/waybill/' + order.gs_shipment_id + '">#' + order.gs_shipment_id + '</a>' : 'None') + '</p>';
        summary += '<p><strong>Tracking:</strong> ' + (order.tracking_number || '-') + '</p>';
        summary += '</div>';
        $('#fulfilment_order_summary').html(summary);

        var itemsHtml = '';
        (res.items || []).forEach(function (item) {
            itemsHtml += '<tr>';
            itemsHtml += '<td>' + (item.gs_sku || '-') + '</td>';
            itemsHtml += '<td>' + (item.product_name || '-') + '</td>';
            itemsHtml += '<td>' + (item.quantity || 0) + '</td>';
            itemsHtml += '<td>' + (item.unit_price || 0) + '</td>';
            itemsHtml += '<td>' + (item.fulfillment_model || '-') + '</td>';
            itemsHtml += '</tr>';
        });
        if (!itemsHtml) {
            itemsHtml = '<tr><td colspan="5" class="text-center text-muted">No line items found.</td></tr>';
        }
        $('#fulfilment_order_items').html(itemsHtml);

        var trackingHtml = '';
        (res.tracking || []).forEach(function (track) {
            trackingHtml += '<div><strong>' + (track.status_description || track.status_name || 'Status') + '</strong> <span class="text-muted">(' + (track.changed_at || '') + ')</span></div>';
        });
        $('#fulfilment_order_tracking').html(trackingHtml || 'No tracking history available.');

        $('#fulfilment_order_payload').text(JSON.stringify(res.raw_payload || {}, null, 2));
        $('#fulfilmentOrderModal').modal('show');
    }, 'json');
}

function createFulfilmentShipment(id) {
    if (!confirm('Create a courier shipment for this Salibay order?')) {
        return;
    }

    $.post(admin_url + 'courier_goshipping/fulfilment/create_shipment/' + id, function (res) {
        if (!res.success) {
            alert_float('danger', res.error || res.message || 'Shipment creation failed.');
            return;
        }

        alert_float('success', res.message || 'Shipment created successfully.');
        reloadFulfilmentOrders();
        if (res.open_url) {
            window.location.href = res.open_url;
        }
    }, 'json');
}
</script>
