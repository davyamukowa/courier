<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <!-- Headings -->
            <div class="col-md-12">
                <div class="tw-mb-6 tw-flex tw-justify-between tw-items-center">
                    <h4 class="tw-my-0 tw-font-bold tw-text-xl">
                        <?= _l('catelog_management'); ?>
                    </h4>
                    <div>
                        <?php if(staff_can('edit', 'wtc_catalog_sync')): ?>
                        <button class="btn btn-primary" id="showExportModalBtn">
                            <i class="fa fa-upload"></i> <?= _l('export_to_whatsapp'); ?>
                        </button>
                        <button class="btn btn-default" id="showImportModalBtn">
                            <i class="fa fa-download"></i> <?= _l('import_from_whatsapp'); ?>
                        </button>
                        <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-refresh"></i> <?= _l('sync'); ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right width200">
                                    <li>
                                        <a
                                            href="<?= admin_url('whatsbot/catalog_products/sync_perfex_to_whatsapp'); ?>">
                                            <i class="fa fa-upload"></i><span><?= _l('sync_perfex_to_whatsapp'); ?></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a
                                            href="<?= admin_url('whatsbot/catalog_products/sync_whatsapp_to_perfex'); ?>">
                                            <i class="fa fa-download"></i><span><?= _l('sync_whatsapp_to_perfex'); ?></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a
                                            href="<?= admin_url('whatsbot/catalog_products/sync_bidirectional'); ?>">
                                            <i class="fa fa-arrows-v"></i><span><?= _l('sync_bidirectional'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="horizontal-scrollable-tabs tw-min-h-0 tw-px-3">
                    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                    <div class="horizontal-tabs -tw-mx-[calc(theme(spacing.3)-1px)]">
                        <ul class="nav nav-tabs nav-tabs-segmented nav-tabs-horizontal project-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#products" aria-controls="products" role="tab" data-toggle="tab">
                                    <i class="fa fa-box menu-icon"></i> <?php echo _l('product_list'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#orders" aria-controls="orders" role="tab" data-toggle="tab">
                                    <i class="fa fa-shopping-cart menu-icon"></i> <?php echo _l('orders'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#sync_logs" aria-controls="sync_logs" role="tab" data-toggle="tab">
                                    <i class="fa fa-list-alt menu-icon"></i> <?php echo _l('synchronization_log'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Datatables -->
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content tw-mt-5">
                            <div role="tabpanel" class="tab-pane active" id="products">
                                <!-- Products summary-->
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-2 tw-mb-4">
                                    <div
                                        class="tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                                        <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                            <?= $states->total_products ?? '' ?>
                                        </span>
                                        <span
                                            class="text-dark tw-truncate sm:tw-text-clip"><?= _l('total_products'); ?></span>
                                    </div>
                                    <div
                                        class="tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                                        <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                            <?= $states->synced_products ?? '' ?></span>
                                        <span
                                            class="text-success tw-truncate sm:tw-text-clip"><?= _l('synced_products'); ?></span>
                                    </div>
                                    <div
                                        class="tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                                        <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                            <?= $states->ready_to_sync ?? '' ?></span>
                                        <span
                                            class="text-info tw-truncate sm:tw-text-clip"><?= _l('ready_to_sync'); ?></span>
                                    </div>
                                </div>
                                <hr>
                                <?php
                                render_datatable([
                                    _l('name'),
                                    _l('description'),
                                    _l('price'),
                                    _l('status'),
                                    _l('Action'),
                                ], 'catalog_products');
                                ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="orders">
                                <?php
                                render_datatable([
                                    _l('the_number_sign'),
                                    _l('name'),
                                    _l('catalog_id'),
                                    _l('user_message'),
                                    _l('receiver_id'),
                                    _l('submit_time'),
                                    _l('wa_no'),
                                    _l('type'),
                                ], 'orders');
                                ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="sync_logs">
                                <?php echo render_datatable([
                                    _l('date'),
                                    _l('direction'),
                                    _l('status'),
                                    _l('items_processed'),
                                    _l('Action')
                                ], 'sync-log'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="no-margin"><?= _l('export_to_whatsapp'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- Export Form -->
                <form id="exportForm">
                    <div class="form-group">
                        <label><?= _l('select_products_to_export'); ?></label>
                        <select id="catalogProductsSelectToExport" class="form-control selectpicker" multiple="multiple" data-live-search="true" data-actions-box="true" title="<?= _l('select_products_to_export'); ?>">
                            <option value="loading" disabled><?= _l('loading_catalog_products'); ?></option>
                        </select>
                    </div>
                </form>

                <!-- Progress Bar (only shown when exporting) -->
                <div class="mtop20" id="exportProgressContainer" style="display: none;">
                    <div
                        class="progress-bar"
                        id="exportProgressBar"
                        role="progressbar"
                        style="width: 0%;">
                        <span id="exportProgressText">0%</span>
                    </div>
                </div>

                <!-- Export Results (only shown after export completes) -->
                <div class="export-results mtop20" id="exportResultsContainer" style="display: none;">
                    <div id="exportResultsAlert" class="alert">
                        <p id="exportResultsMessage"></p>
                    </div>

                    <!-- Error Details -->
                    <div id="exportErrorDetails" style="display: none;">
                        <h5><?= _l('error_details'); ?></h5>
                        <ul class="list-group" id="exportErrorsList">
                            <!-- Error items will be inserted here -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="startExportBtn">
                    <?= _l('export'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _l('import_from_whatsapp_catalog'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- Import Form -->
                <form id="importForm">
                    <?php echo render_select('default_group', $items_groups, ['id', 'name'], 'default_item_group', get_option('whatsbot_default_item_group', '1')); ?>
                    <div class="form-group">
                        <label><?= _l('select_catalog_products'); ?></label>
                        <select id="catalogProductsSelect" class="form-control selectpicker" multiple="multiple" data-live-search="true" data-actions-box="true" title="<?= _l('select_catalog_products'); ?>">
                            <option value="loading" disabled><?= _l('loading_catalog_products'); ?></option>
                        </select>
                    </div>
                </form>

                <!-- Progress Bar (only shown when importing) -->
                <div class="mtop20" id="importProgressContainer" style="display: none;">
                    <div
                        class="progress-bar"
                        id="importProgressBar"
                        role="progressbar"
                        style="width: 0%;">
                        <span id="importProgressText1">0%</span>
                    </div>
                </div>

                <!-- Import Results (only shown after import completes) -->
                <div class="import-results mtop20" id="importResultsContainer" style="display: none;">
                    <div id="importResultsAlert" class="alert">
                        <p id="importResultsMessage"></p>
                    </div>

                    <!-- Error Details -->
                    <div id="importErrorDetails" style="display: none;">
                        <h5><?= _l('error_details'); ?></h5>
                        <ul class="list-group" id="importErrorsList">
                            <!-- Error items will be inserted here -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="startImportBtn">
                    <?= _l('import'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _l('edit_product_metadata'); ?></h4>
            </div>
            <?= form_open('', ['id' => 'product-metadata-form', 'enctype' => 'multipart/form-data']) ?>
            <div class="modal-body">
                <!-- Loading Spinner -->
                <div id="modalLoadingSpinner" class="text-center hide">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p><?= _l('loading'); ?></p>
                </div>

                <!-- Product Details Container -->
                <div id="productDetailsContainer" class="hide">
                    <!-- Product Details -->
                    <div class="row tw-mb-6">
                        <div class="col-md-6">
                            <div class="tw-bg-gray-50 tw-border tw-rounded-lg tw-p-4">
                                <div>
                                    <i class="fa fa-info-circle"></i>
                                    <?= _l('product_details'); ?>
                                </div>
                                <div class="tw-mb-3">
                                    <span class="tw-text-sm tw-text-gray-500"><?= _l('name'); ?></span>
                                    <div class="tw-font-medium" id="productName">
                                        -
                                    </div>
                                </div>
                                <div>
                                    <span class="tw-text-sm tw-text-gray-500"><?= _l('price'); ?></span>
                                    <div class="tw-font-semibold tw-text-green-600" id="productPrice">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 tw-flex tw-items-center tw-justify-center">
                            <div class="tw-border tw-rounded-lg tw-p-3 tw-bg-white">
                                <img id="productImageDisplay" src="" class="img-responsive tw-rounded-md hide" style="max-height:150px;">
                                <div class="tw-text-center tw-text-gray-400" id="noProductImagePlaceholder">
                                    <i class="fa fa-image fa-3x"></i>
                                    <p class="tw-mt-2"><?= _l('no_product_image'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr />
                </div>

                <input type="hidden" name="product_id" id="product_id" value="">

                <!-- Product Image Upload -->
                <div class="form-group">
                    <label for="product_image"><?= _l('product_image'); ?></label>
                    <div class="input-group">
                        <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*">
                        <span class="input-group-addon">
                            <a href="#" class="text-info" data-toggle="tooltip" title="<?= _l('product_image_help'); ?>"><i class="fa fa-question-circle"></i></a>
                        </span>
                    </div>
                    <small class="text-muted"><?= _l('product_image_description'); ?></small>

                    <!-- Image Preview -->
                    <div class="mtop10 hide" id="imagePreview">
                        <img id="previewImg" src="" class="img-thumbnail" style="max-height: 150px; max-width: 150px;">
                    </div>
                </div>

                <div id="formFieldsContainer" class="hide">
                    <div class="form-group">
                        <label for="whatsapp_catalog"><?= _l('whatsapp_catalog'); ?></label>
                        <div class="">
                            <input type="text" name="whatsapp_catalog" id="whatsapp_catalog" class="form-control" value="" disabled>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= _l('save'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(function() {
        // Initialize DataTables
        initDataTable('.table-sync-log', `${admin_url}whatsbot/catalog_products/get_sync_logs_table`, [], [], {}, [0, "desc"]);
        initDataTable('.table-catalog_products', `${admin_url}whatsbot/catalog_products/get_product_table_data`);
        initDataTable('.table-orders', `${admin_url}whatsbot/catalog_products/get_order_table`);

        // Toggle catalog sync via AJAX when checkbox changes
        $('#whatsbot_catalog_sync_enabled').on('change', function() {
            var enabled = $(this).is(':checked') ? 1 : 0;

            var data = {};
            data[csrfData.token_name] = csrfData.hash;
            data.enabled = enabled;

            $.post('<?= admin_url('whatsbot/catalog_products/toggle_catalog_sync'); ?>', data, function(response) {
                alert_float(response.type, response.message);
            }, 'json').fail(function() {
                alert_float('danger', '<?= _l('something_went_wrong'); ?>');
            });
        });

        // Variables
        let itemsList = [];
        let catalogProducts = [];
        let selectedItems = [];
        let selectedProducts = [];

        // Format currency
        function formatCurrency(amount) {
            if (typeof accounting !== 'undefined') {
                return accounting.formatMoney(amount);
            }
            return amount;
        }

        // Show Export Modal
        $('#showExportModalBtn').on('click', function() {
            // Reset export state
            $('#exportProgressContainer').hide();
            $('#exportProgressBar')
                .removeClass('progress-bar-success progress-bar-danger')
                .css('width', '0%');
            $('#exportProgressText').text('0%');

            $('#exportResultsContainer').hide();
            $('#exportResultsAlert').removeClass('alert-success alert-danger');
            $('#exportResultsMessage').text('');
            $('#exportErrorDetails').hide();
            $('#exportErrorsList').empty();

            // Show modal
            $('#exportModal').modal('show');

            // Fetch items
            fetchItems();
        });

        // Show Import Modal
        $('#showImportModalBtn').on('click', function() {
            // Reset import state
            $('#importProgressContainer').hide();
            $('#importProgressBar')
                .removeClass('progress-bar-success progress-bar-danger')
                .css('width', '0%');
            $('#importProgressText1').html('0%');

            $('#importResultsContainer').hide();
            $('#importResultsAlert').removeClass('alert-success alert-danger');
            $('#importResultsMessage').text('');
            $('#importErrorDetails').hide();
            $('#importErrorsList').empty();

            // Show modal
            $('#importModal').modal('show');

            // Fetch catalog products
            fetchCatalogProducts();
        });

        // Fetch items from Perfex
        function fetchItems() {
            $('.items-loading').show();
            $('#itemsListContainer').empty();

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/get_items'); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('.items-loading').hide();

                    if (response.success) {
                        itemsList = response.items;
                        renderItemsList();
                    } else {
                        alert_float('danger', response.message || '<?= _l('error_loading_items'); ?>');
                    }
                },
                error: function(xhr, status, error) {
                    $('.items-loading').hide();
                    console.error('Error fetching items:', error);
                    alert_float('danger', '<?= _l('error_loading_items'); ?>');
                }
            });
        }

        // Render items list
        function renderItemsList() {
            $('#catalogProductsSelectToExport').empty();
            itemsList.forEach(function(item) {
                // skip items that already exist in WhatsApp catalog
                if (item.whatsapp_catalog_id !== null) {
                    return;
                }

                const option = `
                        <option value="${item.id}">
                            ${item.description} (${formatCurrency(item.rate)}) 
                        </option>
                    `;

                $('#catalogProductsSelectToExport').append(option);
            });

            $(document).find('#catalogProductsSelectToExport').selectpicker('refresh');

            $(document).on('change', '#catalogProductsSelectToExport', function() {
                updateSelectedItems();
            });
        }

        // Update selected items array
        function updateSelectedItems() {
            selectedItems = $(document).find('#catalogProductsSelectToExport').val() || [];
        }

        // Toggle all items for export
        $('#select_all_items').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.item-checkbox').prop('checked', isChecked);
            updateSelectedItems();
        });

        // Fetch catalog products from WhatsApp
        function fetchCatalogProducts() {
            $('#catalogProductsSelect').empty();
            $('#catalogProductsSelect').append('<option value="loading" disabled><?= _l('loading_catalog_products'); ?></option>');
            $('#catalogProductsSelect').selectpicker('refresh');

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/get_catalog_products'); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        catalogProducts = response.products;
                        if (catalogProducts.length === 0) {
                            $('#catalogProductsSelect').empty();
                            $('#catalogProductsSelect').selectpicker('refresh');
                            $('#catalogProductsSelect').html('<option value="loading" disabled><?= _l('no_products_available'); ?></option>');
                        } else {
                            renderCatalogProducts(response.products_metadata);
                        }
                    } else {
                        $('#catalogProductsSelect').empty();
                        $('#catalogProductsSelect').selectpicker('refresh');
                        alert_float('danger', response.message || '<?= _l('error_loading_catalog_products'); ?>');
                    }
                }
            });
        }

        // Render catalog products
        function renderCatalogProducts(productMetadata) {
            $('#catalogProductsSelect').empty();
            // create a set of product_ids that already exist in whatsapp catalog
            const existingProductIds = new Set(
                productMetadata.map(meta => meta.whatsapp_catalog_id)
            );

            catalogProducts.forEach(function(product) {
                const isDisabled = existingProductIds.has(product.id.toString());
                const option = `
                        <option value="${product.id}" ${isDisabled ? 'disabled' : ''}>
                            ${product.name} (${product.price} ${product.currency}) 
                            ${isDisabled ? ' - Already Exist' : ''}
                        </option>
                    `;
                $('#catalogProductsSelect').append(option);
            });

            // Refresh selectpicker
            $('#catalogProductsSelect').selectpicker('refresh');

            // Add change event to selectpicker
            $('#catalogProductsSelect').on('change', function() {
                updateSelectedProducts();
            });
        }

        // Update selected products array
        function updateSelectedProducts() {
            selectedProducts = $('#catalogProductsSelect').val() || [];
        }

        // Select all products for import
        $(document).on('click', '#import_select_all_btn', function(e) {
            e.preventDefault();
            $('#catalogProductsSelect').selectpicker('selectAll');
            updateSelectedProducts();
        });

        // Deselect all products for import
        $(document).on('click', '#import_deselect_all_btn', function(e) {
            e.preventDefault();
            $('#catalogProductsSelect').selectpicker('deselectAll');
            updateSelectedProducts();
        });

        // Start export process
        $('#startExportBtn').on('click', function() {
            updateSelectedItems();

            if (selectedItems.length === 0) {
                alert_float('warning', '<?= _l('please_select_items_to_export'); ?>');
                return;
            }

            $('#exportProgressContainer').addClass("progress").show();
            $('#exportProgressBar')
                .removeClass('progress-bar-success progress-bar-danger')
                .addClass('progress-bar-info')
                .css('width', '50%');
            $('#exportProgressText').text('50%');

            const formData = new FormData();
            formData.append('item_group', $('#item_group').val());
            formData.append('skip_existing', $('#skip_existing').prop('checked') ? 1 : 0);

            // Add selected items
            selectedItems.forEach(function(id) {
                formData.append('items[]', id);
            });

            // CSRF token
            formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/export_to_whatsapp'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Show results
                    $('#exportResultsContainer').show();

                    if (response.success) {
                        $('#exportResultsAlert').removeClass('alert-danger').addClass('alert-success');
                        $('#exportProgressBar')
                            .removeClass('progress-bar-info')
                            .addClass('progress-bar-success')
                            .css('width', '100%');
                    } else {
                        $('#exportResultsAlert').removeClass('alert-success').addClass('alert-danger');
                        $('#exportProgressBar')
                            .removeClass('progress-bar-info')
                            .addClass('progress-bar-danger')
                            .css('width', '100%');
                    }

                    $('#exportProgressText').text('100%');
                    $('#exportResultsMessage').text(response.message);

                    // Show error details if any
                    if (response.details && response.details.errors && response.details.errors.length > 0) {
                        $('#exportErrorsList').empty();

                        response.details.errors.forEach(function(error) {
                            const html = `
                            <li class="list-group-item">
                                <strong>${error.item_name}</strong>
                                <p class="text-danger">${error.error}</p>
                            </li>
                        `;
                            $('#exportErrorsList').append(html);
                        });

                        $('#exportErrorDetails').show();
                    } else {
                        $('#exportErrorDetails').hide();
                    }

                    // Disable export button
                    $('#startExportBtn').hide();

                    // Refresh items list after export
                    fetchItems();

                    // Refresh sync log table
                    $('.table-sync-log').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Export error:', error);

                    $('#exportProgressBar')
                        .removeClass('progress-bar-info')
                        .addClass('progress-bar-danger')
                        .css('width', '100%');
                    $('#exportProgressText').text('100%');

                    $('#exportResultsContainer').show();
                    $('#exportResultsAlert').removeClass('alert-success').addClass('alert-danger');
                    $('#exportResultsMessage').text('<?= _l('export_failed'); ?>');

                    // Disable export button
                    $('#startExportBtn').hide();
                }
            });
        });

        // Start import process
        $('#startImportBtn').on('click', function() {
            updateSelectedProducts();

            if (selectedProducts.length === 0) {
                alert_float('warning', '<?= _l('please_select_products_to_import'); ?>');
                return;
            }

            $('#importProgressContainer').addClass('progress').show();
            $('#importProgressBar')
                .removeClass('progress-bar-success progress-bar-danger')
                .addClass('progress-bar-info')
                .css('width', '50%');
            $('#importProgressText1').html('50%');

            const formData = new FormData();
            formData.append('default_group', $('select[name="default_group"]').val());
            formData.append('skip_existing', $('#import_skip_existing').prop('checked') ? 1 : 0);

            // Add selected products
            selectedProducts.forEach(function(id) {
                formData.append('products[]', id);
            });

            // CSRF token
            formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/import_from_whatsapp'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Show results
                    $('#importResultsContainer').show();

                    if (response.success) {
                        $('#importResultsAlert').removeClass('alert-danger').addClass('alert-success');
                        $('#importProgressBar')
                            .removeClass('progress-bar-info')
                            .addClass('progress-bar-success')
                            .css('width', '100%');
                    } else {
                        $('#importResultsAlert').removeClass('alert-success').addClass('alert-danger');
                        $('#importProgressBar')
                            .removeClass('progress-bar-info')
                            .addClass('progress-bar-danger')
                            .css('width', '100%');
                    }

                    $('#importProgressText1').html('100%');
                    $('#importResultsMessage').text(response.message);

                    // Show error details if any
                    if (response.details && response.details.errors && response.details.errors.length > 0) {
                        $('#importErrorsList').empty();

                        response.details.errors.forEach(function(error) {
                            const html = `
                            <li class="list-group-item">
                                <strong>${error.product_name}</strong>
                                <p class="text-danger">${error.error}</p>
                            </li>
                        `;
                            $('#importErrorsList').append(html);
                        });

                        $('#importErrorDetails').show();
                    } else {
                        $('#importErrorDetails').hide();
                    }

                    // Disable import button
                    $('#startImportBtn').hide();

                    // Refresh sync log table
                    $('.table-sync-log').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Import error:', error);

                    $('#importProgressBar')
                        .removeClass('progress-bar-info')
                        .addClass('progress-bar-danger')
                        .css('width', '100%');
                    $('#importProgressText1').html('100%');

                    $('#importResultsContainer').show();
                    $('#importResultsAlert').removeClass('alert-success').addClass('alert-danger');
                    $('#importResultsMessage').text('<?= _l('import_failed'); ?>');

                    // Disable import button
                    $('#startImportBtn').hide();
                }
            });
        });

        // Update sync schedule
        $('#updateSyncScheduleBtn').on('click', function() {
            const formData = new FormData();
            formData.append('sync_frequency', $('#sync_frequency').val());
            formData.append('sync_direction', $('#sync_direction').val());

            // CSRF token
            formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/schedule_sync'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating sync schedule:', error);
                    alert_float('danger', '<?= _l('update_schedule_failed'); ?>');
                }
            });
        });


        /*----- Products Management: Start ----- */

        // Edit product button click handler
        $(document).on('click', '.edit-product-btn', function() {
            var productId = $(this).data('product-id');
            loadProductModal(productId);
        });

        // Load product data into modal
        function loadProductModal(productId) {
            $('#modalLoadingSpinner').removeClass('hide');
            $('#productDetailsContainer').addClass('hide');
            $('#formFieldsContainer').addClass('hide');
            $('#productModal').modal('show');

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/get_product_data'); ?>',
                type: 'POST',
                data: {
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var product = response.data;

                        // Set hidden product ID
                        $('#product_id').val(product.id);

                        // Set product details
                        $('#productName').text(product.description);
                        $('#productPrice').text(product.formatted_price);

                        // Set product image
                        if (product.image_url) {
                            var img_url = `<?= site_url('uploads/whatsbot/product_images/') ?>${product.id}/${product.image_url}`;
                            $('#productImageDisplay').attr('src', img_url).removeClass('hide');
                            $('#noProductImagePlaceholder').addClass('hide');
                        } else {
                            $('#productImageDisplay').addClass('hide');
                            $('#noProductImagePlaceholder').removeClass('hide');
                        }

                        // Set catalog name
                        $('#whatsapp_catalog').val(product.catalog_name || '');

                        // Hide spinner and show content
                        $('#modalLoadingSpinner').addClass('hide');
                        $('#productDetailsContainer').removeClass('hide');
                        $('#formFieldsContainer').removeClass('hide');
                    } else {
                        alert_float('danger', response.message || '<?= _l('something_went_wrong'); ?>');
                        $('#productModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading product:', error);
                    alert_float('danger', '<?= _l('something_went_wrong'); ?>');
                    $('#productModal').modal('hide');
                }
            });
        }

        // Image preview functionality
        $('#product_image').on('change', function() {
            const file = this.files[0];

            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                if (!allowedTypes.includes(file.type)) {
                    alert_float('danger', '<?= _l('invalid_image_type'); ?>');
                    $(this).val('');
                    $('#imagePreview').addClass('hide');
                    return;
                }

                // Validate file size (5MB max)
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (file.size > maxSize) {
                    alert_float('danger', '<?= _l('image_too_large'); ?>');
                    $(this).val('');
                    $('#imagePreview').addClass('hide');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').removeClass('hide');
                };
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').addClass('hide');
            }
        });

        // Form submission
        $('#product-metadata-form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append(csrfData.token_name, csrfData.hash);
            // Show loading
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fa fa-spinner fa-spin"></i> ' + '<?= _l('saving'); ?>').prop('disabled', true);

            $.ajax({
                url: '<?= admin_url('whatsbot/catalog_products/save'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    submitBtn.html(originalText).prop('disabled', false);
                    if (response.success) {
                        alert_float('success', response.message);
                        $('.table-catalog_products').DataTable().ajax.reload();
                        setTimeout(function() {
                            $('#productModal').modal('hide');
                        }, 1500);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    submitBtn.html(originalText).prop('disabled', false);
                    alert_float('danger', '<?= _l('something_went_wrong'); ?>');
                }
            });
        });

        // Clear form when modal is closed
        $('#productModal').on('hidden.bs.modal', function() {
            $('#product-metadata-form')[0].reset();
            $('#product_id').val('');
            $('#imagePreview').addClass('hide');
            $('#previewImg').attr('src', '');
        });

        /*----- Products Management: End ----- */
    });

    // View sync log details
    function viewSyncDetails(id) {
        $.ajax({
            url: '<?= admin_url('whatsbot/catalog_products/get_sync_log_details'); ?>',
            type: 'GET',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show details in a modal
                    const details = response.details;

                    let html = '<div class="modal fade" id="syncDetailsModal" tabindex="-1" role="dialog">';
                    html += '<div class="modal-dialog" role="document">';
                    html += '<div class="modal-content">';
                    html += '<div class="modal-header">';
                    html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                    html += '<h4 class="modal-title">Sync Log Details</h4>';
                    html += '</div>';
                    html += '<div class="modal-body">';

                    // Sync info
                    html += '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<dl>';
                    html += '<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">Date</dt>';
                    html += '<dd class="tw-text-neutral-900 tw-mt-1 lead-name">' + details.sync_time + '</dd>';
                    html += '</dl>';
                    html += '<dl>';
                    html += '<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">Direction</dt>';
                    html += '<dd class="tw-text-neutral-900 tw-mt-1 lead-name">' + details.direction + '</dd>';
                    html += '</dl>';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<dl>';
                    html += '<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">Status</dt>';
                    html += '<dd class="tw-text-neutral-900 tw-mt-1 lead-name">' + details.status + '</dd>';
                    html += '</dl>';
                    html += '<dl>';
                    html += '<dt class="lead-field-heading tw-font-normal tw-text-neutral-500">Items processed</dt>';
                    html += '<dd class="tw-text-neutral-900 tw-mt-1 lead-name">' + details.items_processed + '</dd>';
                    html += '</dl>';
                    html += '</div>';
                    html += '</div>';

                    // Error details if any
                    if (details.errors && details.errors.length > 0) {
                        html += '<hr>';
                        html += '<h4>Error details</h4>';

                        details.errors.forEach(function(error) {
                            html += '<div class="alert alert-danger">';
                            html += '<strong>' + (error.item_name || error.product_name || '') + '</strong><br>';
                            html += '<span>' + error.error + '</span>';
                            html += '</div>';
                        });
                    }

                    html += '</div>';
                    html += '<div class="modal-footer">';
                    html += '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';

                    // Remove any existing modal
                    $('#syncDetailsModal').remove();

                    // Append to body and show
                    $('body').append(html);
                    $('#syncDetailsModal').modal('show');
                } else {
                    alert_float('danger', response.message || "Error loading sync details");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sync details:', error);
                alert_float('danger', "Error loading sync details");
            }
        });
    }

    // Reset export modal when it's closed
    $('#exportModal').on('hidden.bs.modal', function() {
        // Reset form
        $('#exportForm')[0].reset();

        // Reset UI elements
        $('#itemsListContainer').empty();
        $('#exportResultsContainer').hide();
        $('#exportProgressContainer').hide();

        // Reset selected items
        selectedItems = [];

        // Show export button if it was hidden
        $('#startExportBtn').show();
    });

    // Reset import modal when it's closed
    $('#importModal').on('hidden.bs.modal', function() {
        // Reset form
        $('#importForm')[0].reset();

        // Reset UI elements
        $('#catalogProductsSelect').empty();
        $('#catalogProductsSelect').append('<option value="loading" disabled><?= _l('loading_catalog_products'); ?></option>');
        $('#catalogProductsSelect').selectpicker('refresh');
        $('#noCatalogProducts').hide();
        $('#importResultsContainer').hide();
        $('#importProgressContainer').hide();

        // Reset selected products
        selectedProducts = [];

        // Show import button if it was hidden
        $('#startImportBtn').show();
    });
</script>