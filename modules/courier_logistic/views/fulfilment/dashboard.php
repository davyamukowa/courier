<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.sf-grid {
    display: grid;
    grid-template-columns: 2fr 1.1fr;
    gap: 18px;
}
.sf-card {
    background: #fff;
    border: 1px solid #dfe8f2;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}
.sf-card__head {
    padding: 18px 20px 12px;
    border-bottom: 1px solid #edf2f7;
}
.sf-card__head h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #17324d;
}
.sf-card__body {
    padding: 18px 20px 20px;
}
.sf-story {
    padding: 18px 20px;
    border-radius: 16px;
    background: linear-gradient(135deg, #fff6f6 0%, #fff 100%);
    border: 1px solid #f1cccc;
    color: #6a2c2c;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 18px;
}
.sf-story strong { color: #9b1c1c; }
.sf-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
}
.sf-stat {
    padding: 16px;
    border-radius: 16px;
    border: 1px solid #e6edf5;
    background: #f9fbfd;
}
.sf-stat__label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #72859b;
}
.sf-stat__value {
    margin-top: 8px;
    font-size: 28px;
    font-weight: 800;
    color: #102a43;
}
.sf-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.sf-actions a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #e1e8f0;
    background: #fff;
    color: #22405d;
    font-weight: 700;
    text-decoration: none;
}
.sf-actions a.sf-action--primary {
    background: #c62828;
    border-color: #c62828;
    color: #fff;
}
.sf-recent-table td, .sf-recent-table th {
    vertical-align: middle !important;
}
@media (max-width: 991px) {
    .sf-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="sf-grid">
    <div>
        <div class="sf-story">
            <strong>How this works:</strong> Salibay vendors publish their products and quantities on Salibay, Go Shipping mirrors those sellable quantities into a virtual warehouse, a customer places an order, the available quantity drops immediately for fulfilment control, then the vendor brings the item to the Go Shipping office and Go Shipping handles tracking, dispatch, and delivery.
        </div>

        <div class="sf-stats">
            <div class="sf-stat">
                <div class="sf-stat__label">All Salibay Orders</div>
                <div class="sf-stat__value"><?php echo (int) ($metrics['orders_total'] ?? 0); ?></div>
            </div>
            <div class="sf-stat">
                <div class="sf-stat__label">Courier Shipments Linked</div>
                <div class="sf-stat__value"><?php echo (int) ($metrics['linked_shipments'] ?? 0); ?></div>
            </div>
            <div class="sf-stat">
                <div class="sf-stat__label">Mapped Vendors</div>
                <div class="sf-stat__value"><?php echo (int) ($metrics['mapped_vendors'] ?? 0); ?></div>
            </div>
            <div class="sf-stat">
                <div class="sf-stat__label">Low Stock SKUs</div>
                <div class="sf-stat__value"><?php echo (int) ($metrics['low_stock_skus'] ?? 0); ?></div>
            </div>
        </div>

        <div class="sf-card" style="margin-top:18px;">
            <div class="sf-card__head">
                <h4>Recent Salibay Orders</h4>
            </div>
            <div class="sf-card__body">
                <div class="table-responsive">
                    <table class="table sf-recent-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Shipment</th>
                                <th>Total</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_orders)): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars((string) $order->shopify_order_number); ?></td>
                                    <td><?php echo htmlspecialchars((string) $order->customer_name); ?></td>
                                    <td><span class="label label-<?php echo $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo ucfirst((string) $order->order_status); ?></span></td>
                                    <td>
                                        <?php if (!empty($order->gs_shipment_id)): ?>
                                            <a href="<?php echo admin_url('courier_logistic/shipments/waybill/' . $order->gs_shipment_id); ?>">#<?php echo (int) $order->gs_shipment_id; ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) $order->currency); ?> <?php echo number_format((float) $order->total_price, 2); ?></td>
                                    <td><?php echo _dt($order->created_at); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No Salibay orders have been captured yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="sf-card">
            <div class="sf-card__head">
                <h4>Operational Shortcuts</h4>
            </div>
            <div class="sf-card__body">
                <div class="sf-actions">
                    <a class="sf-action--primary" href="<?php echo admin_url('courier_logistic/fulfilment/orders'); ?>"><i class="fa fa-shopping-cart"></i><span>Open Orders Desk</span></a>
                    <a href="<?php echo admin_url('courier_logistic/fulfilment/orders?status=processing'); ?>"><i class="fa fa-truck"></i><span>Pending Dispatch</span></a>
                    <a href="<?php echo admin_url('courier_logistic/fulfilment/inventory'); ?>"><i class="fa fa-cubes"></i><span>Virtual Warehouse</span></a>
                    <a href="<?php echo admin_url('courier_logistic/fulfilment/health'); ?>"><i class="fa fa-heartbeat"></i><span>Health & Logs</span></a>
                    <?php if ($can_manage_fulfilment): ?>
                    <a href="<?php echo admin_url('courier_logistic/fulfilment/settings'); ?>"><i class="fa fa-cogs"></i><span>Connector Settings</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="sf-card" style="margin-top:18px;">
            <div class="sf-card__head">
                <h4>Connector Snapshot</h4>
            </div>
            <div class="sf-card__body">
                <p><strong>Store:</strong> <?php echo htmlspecialchars($store->shop_domain ?? 'Not connected'); ?></p>
                <p><strong>Webhook Pending:</strong> <?php echo (int) ($metrics['webhook_pending'] ?? 0); ?></p>
                <p><strong>Webhook Failed:</strong> <?php echo (int) ($metrics['webhook_failed'] ?? 0); ?></p>
                <p><strong>Last Inventory Sync:</strong> <?php echo !empty($metrics['last_sync']) ? _dt($metrics['last_sync']) : 'Never'; ?></p>
                <p><strong>Virtual Warehouse:</strong> <?php echo htmlspecialchars($virtual_warehouse->warehouse_name ?? 'Not ready'); ?></p>
            </div>
        </div>
    </div>
</div>

