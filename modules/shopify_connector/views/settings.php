<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="mbot15">
                            <a href="<?php echo admin_url('shopify_connector/dashboard'); ?>" class="btn btn-default">
                                <i class="fa fa-dashboard mright5"></i> Dashboard
                            </a>
                            <a href="<?php echo admin_url('shopify_connector/logs'); ?>" class="btn btn-info">
                                <i class="fa fa-terminal mright5"></i> Logs &amp; Health
                            </a>
                        </div>

                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#store_connection" aria-controls="store_connection" role="tab" data-toggle="tab">Store Connection</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#webhooks" aria-controls="webhooks" role="tab" data-toggle="tab">Webhooks</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#product_mappings" aria-controls="product_mappings" role="tab" data-toggle="tab">Product Mappings</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#advanced_settings" aria-controls="advanced_settings" role="tab" data-toggle="tab">Advanced Settings</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content">
                            <!-- TAB 1: Store Connection -->
                            <div role="tabpanel" class="tab-pane active" id="store_connection">
                                <?php echo form_open(admin_url('shopify_connector/save_settings')); ?>
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
                                    <label for="api_secret">Client Secret / API Secret (Starts with shpss_)</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="api_secret" id="api_secret" value="<?php echo isset($store->api_secret) ? $store->api_secret : ''; ?>" required>
                                        <span class="input-group-addon"><a href="#" class="toggle-password" data-target="#api_secret"><i class="fa fa-eye"></i></a></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="access_token">Admin API Access Token (Starts with shpat_)</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="access_token" id="access_token" value="<?php echo isset($store->access_token) ? $store->access_token : ''; ?>" required>
                                        <span class="input-group-addon"><a href="#" class="toggle-password" data-target="#access_token"><i class="fa fa-eye"></i></a></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="webhook_secret">Webhook Secret (Usually the same as Client Secret for Custom Apps)</label>
                                    <input type="text" class="form-control" name="webhook_secret" id="webhook_secret" value="<?php echo isset($store->webhook_secret) ? $store->webhook_secret : ''; ?>" placeholder="Used for HMAC validation">
                                </div>
                                <div class="form-group">
                                    <label for="api_version">API Version</label>
                                    <select class="form-control" name="api_version" id="api_version">
                                        <option value="2024-01" <?php if(isset($store) && $store->api_version == '2024-01') echo 'selected'; ?>>2024-01</option>
                                        <option value="2023-10" <?php if(isset($store) && $store->api_version == '2023-10') echo 'selected'; ?>>2023-10</option>
                                        <option value="2023-07" <?php if(isset($store) && $store->api_version == '2023-07') echo 'selected'; ?>>2023-07</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Default Fulfillment Model</label><br>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="default_fulfillment_model" id="model_a" value="A" <?php if(!isset($store) || $store->default_fulfillment_model == 'A') echo 'checked'; ?>>
                                        <label for="model_a">Model A - Go Shipping Warehouse Stock (own inventory)</label>
                                    </div>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="default_fulfillment_model" id="model_b" value="B" <?php if(isset($store) && $store->default_fulfillment_model == 'B') echo 'checked'; ?>>
                                        <label for="model_b">Model B - Third-Party Supplier Stock (routed through GS)</label>
                                    </div>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="default_fulfillment_model" id="model_c" value="C" <?php if(isset($store) && $store->default_fulfillment_model == 'C') echo 'checked'; ?>>
                                        <label for="model_c">Model C - On-Demand Procurement (procured per order)</label>
                                    </div>
                                </div>

                                <hr />
                                <button type="submit" class="btn btn-info">Save Settings</button>
                                <button type="button" class="btn btn-default" id="btn-test-connection">Test Connection</button>
                                <span id="test-connection-result" class="mleft10"></span>
                                <?php echo form_close(); ?>
                            </div>

                            <!-- TAB 2: Webhooks -->
                            <div role="tabpanel" class="tab-pane" id="webhooks">
                                <div class="alert alert-info">
                                    Webhook Endpoint URL: <strong><?php echo e($webhook_endpoint); ?></strong>
                                </div>
                                <p>Required webhook topics to register: <code>orders/create, orders/updated, orders/cancelled, orders/paid, refunds/create, products/update, inventory_levels/update</code></p>
                                
                                <div class="mbot20">
                                    <?php echo form_open(admin_url('shopify_connector/register_webhooks'), ['class' => 'inline-block']); ?>
                                        <button type="submit" class="btn btn-success">Register All Webhooks</button>
                                    <?php echo form_close(); ?>
                                    
                                    <?php echo form_open(admin_url('shopify_connector/delete_webhooks'), ['class' => 'inline-block mleft10']); ?>
                                        <button type="submit" class="btn btn-danger">Delete All Webhooks</button>
                                    <?php echo form_close(); ?>
                                </div>

                                <table class="table table-bordered dt-table">
                                    <thead>
                                        <tr>
                                            <th>Topic</th>
                                            <th>Endpoint URL</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($webhooks)) { ?>
                                            <?php foreach ($webhooks as $webhook) { ?>
                                                <tr>
                                                    <td><?php echo $webhook->topic; ?></td>
                                                    <td><?php echo $webhook->address; ?></td>
                                                    <td>
                                                        <span class="label label-<?php echo $webhook->is_active ? 'success' : 'danger'; ?>">
                                                            <?php echo $webhook->is_active ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr><td colspan="3" class="text-center">No webhooks recorded locally yet.</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- TAB 3: Product Mappings -->
                            <div role="tabpanel" class="tab-pane" id="product_mappings">
                                <div class="mbot20">
                                    <button class="btn btn-info" data-toggle="modal" data-target="#productMappingModal">Add Mapping</button>
                                    <button class="btn btn-default" id="btn-import-products">Import from Shopify</button>
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

                            <!-- TAB 4: Advanced Settings -->
                            <div role="tabpanel" class="tab-pane" id="advanced_settings">
                                <?php echo form_open(admin_url('shopify_connector/save_settings')); ?>
                                <input type="hidden" name="advanced_settings" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="auto_process_orders" id="auto_process_orders" value="1" <?php if($advanced['auto_process_orders'] == 1) echo 'checked'; ?>>
                                            <label for="auto_process_orders">Auto-process orders</label>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="auto_create_shipments" id="auto_create_shipments" value="1" <?php if($advanced['auto_create_shipments'] == 1) echo 'checked'; ?>>
                                            <label for="auto_create_shipments">Auto-create shipments</label>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="auto_post_accounting" id="auto_post_accounting" value="1" <?php if($advanced['auto_post_accounting'] == 1) echo 'checked'; ?>>
                                            <label for="auto_post_accounting">Auto-post accounting entries</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="notification_sms" id="notification_sms" value="1" <?php if($advanced['notification_sms'] == 1) echo 'checked'; ?>>
                                                <label for="notification_sms">Notification SMS</label>
                                            </div>
                                            <input type="text" class="form-control" name="notification_sms_sender" placeholder="Sender ID" value="<?php echo $advanced['notification_sms_sender']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="notification_email" id="notification_email" value="1" <?php if($advanced['notification_email'] == 1) echo 'checked'; ?>>
                                                <label for="notification_email">Notification Email</label>
                                            </div>
                                            <input type="text" class="form-control mbot5" name="notification_email_name" placeholder="From Name" value="<?php echo $advanced['notification_email_name']; ?>">
                                            <input type="email" class="form-control" name="notification_email_address" placeholder="From Email" value="<?php echo $advanced['notification_email_address']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="inventory_sync_direction">Inventory Sync Direction</label>
                                            <select class="form-control" name="inventory_sync_direction" id="inventory_sync_direction">
                                                <option value="Disabled" <?php if($advanced['inventory_sync_direction'] == 'Disabled') echo 'selected'; ?>>Disabled</option>
                                                <option value="GS_to_Shopify" <?php if($advanced['inventory_sync_direction'] == 'GS_to_Shopify') echo 'selected'; ?>>GS → Shopify</option>
                                                <option value="Bidirectional" <?php if($advanced['inventory_sync_direction'] == 'Bidirectional') echo 'selected'; ?>>Bidirectional</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="low_stock_threshold">Low stock alert threshold</label>
                                            <input type="number" class="form-control" name="low_stock_threshold" id="low_stock_threshold" value="<?php echo $advanced['low_stock_threshold']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="default_staff_id">Default staff for Shopify shipments</label>
                                            <select class="form-control" name="default_staff_id" id="default_staff_id">
                                                <option value="">Auto (first active staff)</option>
                                                <?php foreach ($staff_members as $staff) { ?>
                                                    <option value="<?php echo $staff->staffid; ?>" <?php if ($advanced['default_staff_id'] == $staff->staffid) echo 'selected'; ?>>
                                                        <?php echo e($staff->firstname . ' ' . $staff->lastname); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="public_webhook_url">Public webhook URL</label>
                                            <input type="url" class="form-control" name="public_webhook_url" id="public_webhook_url" value="<?php echo e($advanced['public_webhook_url']); ?>" placeholder="https://xetuu.com/admin/shopify_connector/webhook">
                                            <p class="text-muted mtop5">Required when APP_BASE_URL is localhost because Shopify only delivers webhooks to public HTTPS URLs.</p>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info">Save Advanced Settings</button>
                                <?php echo form_close(); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Mapping Modal -->
<div class="modal fade" id="productMappingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                <!-- Assume Select2 logic handled by JS -->
                <input type="text" class="form-control" name="gs_sku" required placeholder="Search or enter SKU">
            </div>
            <div class="form-group">
                <label>Fulfillment Model</label>
                <select class="form-control" name="fulfillment_model" id="mapping_fulfillment_model">
                    <option value="A">A - Warehouse Stock</option>
                    <option value="B">B - Supplier Stock</option>
                    <option value="C">C - On-Demand</option>
                </select>
            </div>
            <div class="form-group" id="supplier_wrapper" style="display:none;">
                <label>Supplier</label>
                <select class="form-control" name="supplier_id">
                    <option value="">Select Supplier</option>
                    <!-- Populated dynamically -->
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

<?php init_tail(); ?>
