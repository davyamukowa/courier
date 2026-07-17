<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="horizontal-scrollable-tabs">
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active"><a href="#store_connection" aria-controls="store_connection" role="tab" data-toggle="tab">Store Connection</a></li>
                    <li role="presentation"><a href="#webhooks" aria-controls="webhooks" role="tab" data-toggle="tab">Webhooks</a></li>
                    <li role="presentation"><a href="#product_mappings" aria-controls="product_mappings" role="tab" data-toggle="tab">Product Mappings</a></li>
                    <li role="presentation"><a href="#locations_branches" aria-controls="locations_branches" role="tab" data-toggle="tab">Locations &amp; Branches</a></li>
                    <li role="presentation"><a href="#advanced_settings" aria-controls="advanced_settings" role="tab" data-toggle="tab">Advanced Settings</a></li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="store_connection">
                <?php echo form_open(admin_url('courier_logistic/fulfilment/save_settings')); ?>
                <input type="hidden" name="store_settings" value="1">
                <div class="form-group">
                    <label for="shop_domain">Shop Domain</label>
                    <input type="text" class="form-control" name="shop_domain" id="shop_domain" placeholder="salibay.myshopify.com" value="<?php echo isset($store->shop_domain) ? $store->shop_domain : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="api_key">Client ID / API Key</label>
                    <input type="text" class="form-control" name="api_key" id="api_key" value="<?php echo isset($store->api_key) ? $store->api_key : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="api_secret">Client Secret / API Secret</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="api_secret" id="api_secret" value="<?php echo isset($store->api_secret) ? $store->api_secret : ''; ?>" required>
                        <span class="input-group-addon"><a href="#" class="toggle-password" data-target="#api_secret"><i class="fa fa-eye"></i></a></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="access_token">Admin API Access Token</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="access_token" id="access_token" value="<?php echo isset($store->access_token) ? $store->access_token : ''; ?>" required>
                        <span class="input-group-addon"><a href="#" class="toggle-password" data-target="#access_token"><i class="fa fa-eye"></i></a></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="webhook_secret">Webhook Secret</label>
                    <input type="text" class="form-control" name="webhook_secret" id="webhook_secret" value="<?php echo isset($store->webhook_secret) ? $store->webhook_secret : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="api_version">API Version</label>
                    <select class="form-control" name="api_version" id="api_version">
                        <option value="2024-01" <?php echo isset($store) && $store->api_version == '2024-01' ? 'selected' : ''; ?>>2024-01</option>
                        <option value="2023-10" <?php echo isset($store) && $store->api_version == '2023-10' ? 'selected' : ''; ?>>2023-10</option>
                        <option value="2023-07" <?php echo isset($store) && $store->api_version == '2023-07' ? 'selected' : ''; ?>>2023-07</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="virtual_warehouse_name">Virtual Warehouse Name</label>
                    <input type="text" class="form-control" name="virtual_warehouse_name" id="virtual_warehouse_name" value="<?php echo htmlspecialchars($advanced['virtual_warehouse_name']); ?>">
                    <p class="text-muted mtop5">This warehouse is the virtual stock mirror for Salibay vendor inventory.</p>
                </div>
                <div class="form-group">
                    <label>Default Fulfillment Model</label><br>
                    <div class="radio radio-primary">
                        <input type="radio" name="default_fulfillment_model" id="model_a" value="A" <?php if (!isset($store) || $store->default_fulfillment_model == 'A') echo 'checked'; ?>>
                        <label for="model_a">Model A - Virtual warehouse stock mirrored for fulfilment</label>
                    </div>
                    <div class="radio radio-primary">
                        <input type="radio" name="default_fulfillment_model" id="model_b" value="B" <?php if (isset($store) && $store->default_fulfillment_model == 'B') echo 'checked'; ?>>
                        <label for="model_b">Model B - Vendor / supplier stock routed through Go Shipping</label>
                    </div>
                    <div class="radio radio-primary">
                        <input type="radio" name="default_fulfillment_model" id="model_c" value="C" <?php if (isset($store) && $store->default_fulfillment_model == 'C') echo 'checked'; ?>>
                        <label for="model_c">Model C - On-demand procurement</label>
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-info">Save Settings</button>
                <button type="button" class="btn btn-default" id="btn-test-connection">Test Connection</button>
                <button type="button" class="btn btn-default" onclick="ensureFulfilmentWarehouse();">Ensure Virtual Warehouse</button>
                <span id="test-connection-result" class="mleft10"></span>
                <?php echo form_close(); ?>
            </div>

            <div role="tabpanel" class="tab-pane" id="webhooks">
                <div class="alert alert-info">Webhook Endpoint URL: <strong><?php echo e($webhook_endpoint); ?></strong></div>
                <p>Register the webhook topics required for order capture, product changes, and stock synchronization.</p>
                <div class="mbot20">
                    <?php echo form_open(admin_url('courier_logistic/fulfilment/register_webhooks'), ['class' => 'inline-block']); ?>
                    <button type="submit" class="btn btn-success">Register All Webhooks</button>
                    <?php echo form_close(); ?>

                    <?php echo form_open(admin_url('courier_logistic/fulfilment/delete_webhooks'), ['class' => 'inline-block mleft10']); ?>
                    <button type="submit" class="btn btn-danger">Delete All Webhooks</button>
                    <?php echo form_close(); ?>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Endpoint URL</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($webhooks)): ?>
                            <?php foreach ($webhooks as $webhook): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($webhook->topic); ?></td>
                                <td><?php echo htmlspecialchars($webhook->address); ?></td>
                                <td><span class="label label-<?php echo $webhook->is_active ? 'success' : 'danger'; ?>"><?php echo $webhook->is_active ? 'Active' : 'Inactive'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No webhooks recorded locally yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="product_mappings">
                <div class="mbot20">
                    <button class="btn btn-info" data-toggle="modal" data-target="#productMappingModal">Add Mapping</button>
                    <button class="btn btn-default" id="btn-import-products">Import from Salibay</button>
                </div>
                <table class="table table-bordered" id="product-mappings-table">
                    <thead>
                        <tr>
                            <th>Shopify Product ID</th>
                            <th>Variant ID</th>
                            <th>GS SKU</th>
                            <th>Model</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="locations_branches">
                <div class="alert alert-info">Pair each Salibay/Shopify warehouse location to a Go Shipping branch/office. Once mapped, products stocked at that location are automatically routed to that branch when an order comes in.</div>
                <div class="mbot20">
                    <button class="btn btn-info" id="btn-sync-locations">Sync Locations from Salibay</button>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>City</th>
                            <th>Country Code</th>
                            <th>Branch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shopify_locations)): ?>
                            <?php foreach ($shopify_locations as $loc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loc->name ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($loc->city ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($loc->country_code ?: '-'); ?></td>
                                    <td>
                                        <select class="form-control location-branch-select" data-id="<?php echo (int) $loc->id; ?>">
                                            <option value="">-- Unmapped --</option>
                                            <?php foreach ($branches as $b): ?>
                                                <option value="<?php echo (int) $b->id; ?>" <?php echo ((int) $loc->branch_id === (int) $b->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b->name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No locations synced yet. Click "Sync Locations from Salibay" above.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div role="tabpanel" class="tab-pane" id="advanced_settings">
                <?php echo form_open(admin_url('courier_logistic/fulfilment/save_settings')); ?>
                <input type="hidden" name="advanced_settings" value="1">
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="auto_process_orders" id="auto_process_orders" value="1" <?php if ($advanced['auto_process_orders'] == 1) echo 'checked'; ?>>
                            <label for="auto_process_orders">Auto-process orders</label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="auto_create_shipments" id="auto_create_shipments" value="1" <?php if ($advanced['auto_create_shipments'] == 1) echo 'checked'; ?>>
                            <label for="auto_create_shipments">Auto-create shipments</label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="auto_post_accounting" id="auto_post_accounting" value="1" <?php if ($advanced['auto_post_accounting'] == 1) echo 'checked'; ?>>
                            <label for="auto_post_accounting">Auto-post accounting entries</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notification_sms" id="notification_sms" value="1" <?php if ($advanced['notification_sms'] == 1) echo 'checked'; ?>>
                                <label for="notification_sms">Notification SMS</label>
                            </div>
                            <input type="text" class="form-control" name="notification_sms_sender" placeholder="Sender ID" value="<?php echo htmlspecialchars($advanced['notification_sms_sender']); ?>">
                        </div>
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notification_email" id="notification_email" value="1" <?php if ($advanced['notification_email'] == 1) echo 'checked'; ?>>
                                <label for="notification_email">Notification Email</label>
                            </div>
                            <input type="text" class="form-control mbot5" name="notification_email_name" placeholder="From Name" value="<?php echo htmlspecialchars($advanced['notification_email_name']); ?>">
                            <input type="email" class="form-control" name="notification_email_address" placeholder="From Email" value="<?php echo htmlspecialchars($advanced['notification_email_address']); ?>">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inventory_sync_direction">Inventory Sync Direction</label>
                            <select class="form-control" name="inventory_sync_direction" id="inventory_sync_direction">
                                <option value="Disabled" <?php if ($advanced['inventory_sync_direction'] == 'Disabled') echo 'selected'; ?>>Disabled</option>
                                <option value="GS_to_Shopify" <?php if ($advanced['inventory_sync_direction'] == 'GS_to_Shopify') echo 'selected'; ?>>GS → Salibay</option>
                                <option value="Bidirectional" <?php if ($advanced['inventory_sync_direction'] == 'Bidirectional') echo 'selected'; ?>>Bidirectional</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="low_stock_threshold">Low stock alert threshold</label>
                            <input type="number" class="form-control" name="low_stock_threshold" id="low_stock_threshold" value="<?php echo htmlspecialchars((string) $advanced['low_stock_threshold']); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="default_staff_id">Default staff for Salibay shipments</label>
                            <select class="form-control" name="default_staff_id" id="default_staff_id">
                                <option value="">Auto (first active staff)</option>
                                <?php foreach ($staff_members as $staff): ?>
                                <option value="<?php echo $staff->staffid; ?>" <?php echo $advanced['default_staff_id'] == $staff->staffid ? 'selected' : ''; ?>>
                                    <?php echo e($staff->firstname . ' ' . $staff->lastname); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="public_webhook_url">Public webhook URL</label>
                            <input type="url" class="form-control" name="public_webhook_url" id="public_webhook_url" value="<?php echo e($advanced['public_webhook_url']); ?>" placeholder="https://yourdomain/admin/shopify_connector/webhook">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="virtual_warehouse_name_advanced">Virtual Warehouse Name</label>
                    <input type="text" class="form-control" name="virtual_warehouse_name" id="virtual_warehouse_name_advanced" value="<?php echo htmlspecialchars($advanced['virtual_warehouse_name']); ?>">
                </div>
                <button type="submit" class="btn btn-info">Save Advanced Settings</button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productMappingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Add / Edit Product Mapping</h4>
            </div>
            <div class="modal-body">
                <form id="productMappingForm">
                    <div class="form-group">
                        <label>Shopify Product ID</label>
                        <input type="text" class="form-control" name="shopify_product_id" required>
                    </div>
                    <div class="form-group">
                        <label>Shopify Variant ID</label>
                        <input type="text" class="form-control" name="shopify_variant_id" required>
                    </div>
                    <div class="form-group">
                        <label>GS SKU</label>
                        <input type="text" class="form-control" name="gs_sku" required placeholder="Search or enter SKU">
                    </div>
                    <div class="form-group">
                        <label>Fulfillment Model</label>
                        <select class="form-control" name="fulfillment_model" id="mapping_fulfillment_model">
                            <option value="A">A - Virtual Warehouse</option>
                            <option value="B">B - Vendor Stock</option>
                            <option value="C">C - On-Demand Procurement</option>
                        </select>
                    </div>
                    <div class="form-group" id="supplier_wrapper" style="display:none;">
                        <label>Supplier</label>
                        <select class="form-control" name="supplier_id">
                            <option value="">Select Supplier</option>
                            <option value="1">Supplier 1</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="btn-save-mapping">Save Mapping</button>
            </div>
        </div>
    </div>
</div>

<script>
(function waitForJQueryFulfilmentSettings() {
    if (!window.jQuery) {
        setTimeout(waitForJQueryFulfilmentSettings, 50);
        return;
    }

    $(function () {
        $('.toggle-password').on('click', function (e) {
            e.preventDefault();
            var target = $($(this).data('target'));
            var icon = $(this).find('i');
            var nextType = target.attr('type') === 'password' ? 'text' : 'password';

            target.attr('type', nextType);
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        function openHashTab() {
            var hash = window.location.hash;
            if (hash && $('.nav-tabs a[href="' + hash + '"]').length) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }
        }

        $('.nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.hash) {
                window.location.hash = e.target.hash;
            }
        });

        openHashTab();
        $(window).on('hashchange', openHashTab);

        $('#btn-test-connection').on('click', function () {
            var result = $('#test-connection-result');
            result.html('<span class="text-muted">Testing...</span>');

            $.get(admin_url + 'courier_logistic/fulfilment/test_connection', function (res) {
                result.html(res.success
                    ? '<span class="text-success"><i class="fa fa-check"></i> ' + (res.message || 'Connected') + '</span>'
                    : '<span class="text-danger"><i class="fa fa-times"></i> ' + (res.message || 'Connection failed') + '</span>');
            }, 'json').fail(function () {
                result.html('<span class="text-danger"><i class="fa fa-times"></i> Server error.</span>');
            });
        });

        $('#mapping_fulfillment_model').on('change', function () {
            var model = $(this).val();
            $('#supplier_wrapper').toggle(model === 'B' || model === 'C');
        }).trigger('change');

        var mappingsTable = $('#product-mappings-table').DataTable({
            ajax: admin_url + 'courier_logistic/fulfilment/get_product_mappings',
            columns: [
                { data: 'shopify_product_id' },
                { data: 'shopify_variant_id' },
                { data: 'gs_sku' },
                { data: 'fulfillment_model' },
                { data: 'supplier_id' },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return '<a href="#" class="btn btn-danger btn-icon delete-mapping" data-id="' + row.id + '"><i class="fa fa-remove"></i></a>';
                    }
                }
            ],
            initComplete: function (settings, json) {
                $(this).parents('.table-loading').removeClass('table-loading');
            }
        });

        $('#btn-save-mapping').on('click', function () {
            $.post(admin_url + 'courier_logistic/fulfilment/save_product_mapping', $('#productMappingForm').serialize(), function (res) {
                if (!res.success) {
                    alert_float('danger', res.message || 'Mapping could not be saved.');
                    return;
                }

                $('#productMappingModal').modal('hide');
                $('#productMappingForm')[0].reset();
                $('#mapping_fulfillment_model').trigger('change');
                mappingsTable.ajax.reload(null, false);
                alert_float('success', 'Mapping saved successfully.');
            }, 'json');
        });

        $('#product-mappings-table').on('click', '.delete-mapping', function (e) {
            e.preventDefault();
            if (!confirm_delete()) {
                return;
            }

            $.post(admin_url + 'courier_logistic/fulfilment/delete_product_mapping/' + $(this).data('id'), function (res) {
                if (!res.success) {
                    alert_float('danger', 'Mapping could not be deleted.');
                    return;
                }

                mappingsTable.ajax.reload(null, false);
                alert_float('success', 'Mapping deleted.');
            }, 'json');
        });

        $('#btn-import-products').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true);
            $.post(admin_url + 'courier_logistic/fulfilment/import_shopify_products', function (res) {
                alert_float(res.success ? 'success' : 'danger', res.message || 'Import finished.');
                if (res.success) {
                    mappingsTable.ajax.reload(null, false);
                }
            }, 'json').always(function () {
                $btn.prop('disabled', false);
            });
        });

        $('#btn-sync-locations').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true);
            $.post(admin_url + 'courier_logistic/fulfilment/sync_shopify_locations', function (res) {
                alert_float(res.success ? 'success' : 'danger', res.message || 'Sync finished.');
                if (res.success) {
                    window.location.reload();
                }
            }, 'json').always(function () {
                $btn.prop('disabled', false);
            });
        });

        $(document).on('change', '.location-branch-select', function () {
            var $select = $(this);
            $.post(admin_url + 'courier_logistic/fulfilment/save_location_branch_map', {
                id: $select.data('id'),
                branch_id: $select.val()
            }, function (res) {
                alert_float(res.success ? 'success' : 'danger', res.success ? 'Location mapped.' : 'Failed to save mapping.');
            }, 'json');
        });
    });
})();

function ensureFulfilmentWarehouse() {
    $.post(admin_url + 'courier_logistic/fulfilment/ensure_virtual_warehouse_ajax', function (res) {
        alert_float(res.success ? 'success' : 'danger', res.message || 'Done.');
        if (res.success) {
            window.location.reload();
        }
    }, 'json');
}
</script>

