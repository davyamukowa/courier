<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="alert alert-info">
            This page now uses the same server-rendered order loading approach as the dashboard, so every captured Salibay order is shown immediately with its shipment state.
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Salibay Order</th>
                        <th>Customer</th>
                        <th>Order Status</th>
                        <th>Payment</th>
                        <th>Shipment Status</th>
                        <th>Waybill / Shipment</th>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Mode</th>
                        <th>Tracking</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salibay_orders)): ?>
                        <?php foreach ($salibay_orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars((string) $order->shopify_order_number); ?></td>
                                <td><?php echo htmlspecialchars((string) $order->customer_name); ?></td>
                                <td><span class="label label-<?php echo $order->order_badge_class; ?>"><?php echo ucfirst((string) $order->order_status); ?></span></td>
                                <td><span class="label label-<?php echo $order->financial_badge_class; ?>"><?php echo ucfirst((string) ($order->financial_status ?: 'unknown')); ?></span></td>
                                <td><span class="label label-<?php echo $order->shipment_badge_class; ?>"><?php echo htmlspecialchars($order->shipment_status_text); ?></span></td>
                                <td>
                                    <?php if (!empty($order->shipment_id)): ?>
                                        <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $order->shipment_id); ?>" style="font-weight:700;">
                                            <?php echo htmlspecialchars($order->waybill_display !== '' ? $order->waybill_display : ('#' . $order->shipment_id)); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not created</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($order->sender_display); ?></td>
                                <td><?php echo htmlspecialchars($order->recipient_display); ?></td>
                                <td><?php echo htmlspecialchars((string) ($order->shipping_mode ?: '-')); ?></td>
                                <td><?php echo htmlspecialchars($order->tracking_display); ?></td>
                                <td><?php echo _dt($order->order_created_at); ?></td>
                                <td>
                                    <?php if (!empty($order->shipment_id)): ?>
                                        <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $order->shipment_id); ?>" class="btn btn-default btn-xs">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-default btn-xs" onclick="createSalibayShipment(<?php echo (int) $order->order_id; ?>); return false;">
                                            <i class="fa fa-truck"></i> Create Shipment
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">No Salibay orders have been captured yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
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
</script>
