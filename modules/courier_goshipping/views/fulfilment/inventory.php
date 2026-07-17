<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="alert alert-info">
            <strong><?php echo htmlspecialchars($virtual_warehouse->warehouse_name ?? 'Virtual Warehouse'); ?>:</strong>
            This mirrors Salibay vendor stock for fulfilment control. The product may still physically sit with the vendor until the order is handed over to Go Shipping, but the sellable quantity is monitored here so dispatch and customer promises stay accurate.
        </div>

        <div class="row mbot20">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-uppercase text-muted" style="font-size:11px;font-weight:700;">Tracked SKUs</div>
                        <div style="font-size:26px;font-weight:800;"><?php echo (int) ($metrics['tracked_skus'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-uppercase text-muted" style="font-size:11px;font-weight:700;">Available Units</div>
                        <div style="font-size:26px;font-weight:800;"><?php echo number_format((float) ($metrics['virtual_available_qty'] ?? 0), 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-uppercase text-muted" style="font-size:11px;font-weight:700;">Reserved Units</div>
                        <div style="font-size:26px;font-weight:800;"><?php echo number_format((float) ($metrics['virtual_reserved_qty'] ?? 0), 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-uppercase text-muted" style="font-size:11px;font-weight:700;">Low Stock SKUs</div>
                        <div style="font-size:26px;font-weight:800;"><?php echo (int) ($metrics['low_stock_skus'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mbot15">
            <?php if ($can_manage_fulfilment): ?>
            <button type="button" class="btn btn-success" onclick="importFulfilmentProducts(this);"><i class="fa fa-download"></i> Import from Salibay</button>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" onclick="runFulfilmentInventorySync();"><i class="fa fa-refresh"></i> Sync Inventory Now</button>
            <?php if ($can_manage_fulfilment): ?>
            <button type="button" class="btn btn-default" onclick="ensureFulfilmentWarehouse();"><i class="fa fa-building"></i> Ensure Virtual Warehouse</button>
            <?php endif; ?>
        </div>

        <div class="row mbot15">
            <div class="col-md-3">
                <select id="inventory_stock_filter" class="form-control">
                    <option value="">All Stock Statuses</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-fulfilment-inventory" data-order-col="11" data-order-type="desc">
                <thead>
                    <tr>
                        <th>GS SKU</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Variant ID</th>
                        <th>Model</th>
                        <th>Cost</th>
                        <th>Sell Price</th>
                        <th>On Hand</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Stock Status</th>
                        <th>Last Sync</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function waitForJQueryFulfilmentInventory() {
    if (!window.jQuery) {
        setTimeout(waitForJQueryFulfilmentInventory, 50);
        return;
    }

    $(function () {
        $('.table-fulfilment-inventory').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: admin_url + 'courier_goshipping/fulfilment/get_inventory_datatable',
                type: 'POST',
                data: function(d) {
                    if (typeof csrfData !== 'undefined') {
                        d[csrfData['token_name']] = csrfData['hash'];
                    }
                }
            },
            order: [[11, 'desc']],
            initComplete: function (settings, json) {
                $(this).parents('.table-loading').removeClass('table-loading');
            }
        });
    });
})();

function importFulfilmentProducts(btn) {
    var $btn = $(btn);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
    $.post(admin_url + 'courier_goshipping/fulfilment/import_shopify_products', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Import finished.');
        if (res.success) {
            $('.table-fulfilment-inventory').DataTable().ajax.reload();
        }
    }, 'json').always(function () {
        $btn.prop('disabled', false).html('<i class="fa fa-download"></i> Import from Salibay');
    });
}

function runFulfilmentInventorySync() {
    $.post(admin_url + 'courier_goshipping/fulfilment/run_inventory_sync', function (res) {
        if (!res.success) {
            alert_float('danger', res.message || 'Inventory sync failed.');
            return;
        }
        alert_float('success', res.message || 'Inventory sync completed.');
        $('.table-fulfilment-inventory').DataTable().ajax.reload();
    }, 'json');
}

function ensureFulfilmentWarehouse() {
    $.post(admin_url + 'courier_goshipping/fulfilment/ensure_virtual_warehouse_ajax', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        if (res.success) {
            window.location.reload();
        }
    }, 'json');
}
</script>
