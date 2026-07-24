<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="alert alert-info">
            This page now uses the same server-rendered order loading approach as the dashboard, so every captured Salibay order is shown immediately with its shipment state. Click anywhere on a row to open its waybill, or to create a shipment if it doesn't have one yet.
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-condensed salibay-order-list">
                <thead>
                    <tr>
                        <th>Salibay Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Sourcing</th>
                        <th>Fulfilment Status</th>
                        <th>Payment</th>
                        <th>Waybill / Shipment</th>
                        <th>Sender</th>
                        <th>Assigned To</th>
                        <th>Total Amount</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salibay_orders)): ?>
                        <?php foreach ($salibay_orders as $order): ?>
                            <?php
                                $row_href = !empty($order->shipment_id)
                                    ? admin_url('courier_goshipping/shipments/waybill/' . $order->shipment_id)
                                    : '';
                            ?>
                            <tr
                                class="salibay-order-row<?php echo $order->needs_manual_review ? ' salibay-needs-review' : ''; ?>"
                                <?php if ($row_href): ?>
                                    data-href="<?php echo $row_href; ?>"
                                <?php else: ?>
                                    data-order-id="<?php echo (int) $order->order_id; ?>"
                                <?php endif; ?>
                            >
                                <td>#<?php echo htmlspecialchars((string) $order->shopify_order_number); ?></td>
                                <td><?php echo htmlspecialchars((string) $order->customer_name); ?></td>
                                <td><?php echo htmlspecialchars($order->items_display); ?></td>
                                <td>
                                    <span class="label label-<?php echo $order->classification_badge_class; ?>"><?php echo htmlspecialchars($order->classification_display); ?></span>
                                    <?php if (!empty($order->salibay_route_tag)): ?>
                                        <div class="text-muted" style="font-size:11px;"><?php echo htmlspecialchars($order->salibay_route_tag); ?></div>
                                    <?php endif; ?>
                                    <?php if ($order->needs_manual_review): ?>
                                        <div style="color:#c62828; font-weight:700; font-size:11px;"><i class="fa fa-exclamation-triangle"></i> Needs review</div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="label label-<?php echo $order->fulfilment_badge_class; ?>"><?php echo htmlspecialchars($order->fulfilment_status_text); ?></span></td>
                                <td><span class="label label-<?php echo $order->financial_badge_class; ?>"><?php echo ucfirst((string) ($order->financial_status ?: 'unknown')); ?></span></td>
                                <td>
                                    <?php if (!empty($order->shipment_id)): ?>
                                        <span style="font-weight:700;"><?php echo htmlspecialchars($order->waybill_display !== '' ? $order->waybill_display : ('#' . $order->shipment_id)); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not created</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($order->sender_column_display); ?></td>
                                <td><?php echo htmlspecialchars($order->assigned_display); ?></td>
                                <td><?php echo htmlspecialchars($order->total_display); ?></td>
                                <td><?php echo _dt($order->order_created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">No Salibay orders have been captured yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.salibay-order-list > tbody > tr > td {
    padding: 4px 8px;
    vertical-align: middle;
    font-size: 12px;
}
.salibay-order-list > thead > tr > th {
    padding: 6px 8px;
    font-size: 12px;
}
.salibay-order-row {
    cursor: pointer;
}
.salibay-order-row:hover {
    background-color: #f5f7fa;
}
.salibay-needs-review {
    background-color: #fdeaea !important;
}
</style>

<script>
(function waitForJQuerySalibayOrderList() {
    // group_content (this view) is echoed into main.php BEFORE init_tail()
    // loads jQuery, so $ isn't defined yet at the point this script tag
    // runs — same reason main.php's own dashboard poller has to wait for it.
    if (!window.jQuery) { setTimeout(waitForJQuerySalibayOrderList, 50); return; }

    function createSalibayShipment(id) {
        if (!confirm('Create a courier shipment for this Salibay order?')) {
            return;
        }

        var postData = {};
        if (typeof csrfData !== 'undefined') {
            postData[csrfData['token_name']] = csrfData['hash'];
        } else {
            postData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        }

        $.post('<?php echo site_url('admin/courier_goshipping/fulfilment/create_shipment/'); ?>' + id, postData, function (res) {
            if (!res.success) {
                alert_float('danger', res.error || res.message || 'Shipment creation failed.');
                return;
            }

            alert_float('success', res.message || 'Shipment created successfully.');
            window.location.reload();
        }, 'json').fail(function () {
            alert_float('danger', 'Unable to contact the server. Please refresh and try again.');
        });
    }

    $(document).on('click', '.salibay-order-row', function () {
        var href = $(this).data('href');
        if (href) {
            window.location.href = href;
            return;
        }

        var orderId = $(this).data('order-id');
        if (orderId) {
            createSalibayShipment(orderId);
        }
    });
})();
</script>
