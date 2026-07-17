// c:\wamp64\www\perfex_crm\modules\shopify_connector\assets\js\shopify_connector.js

$(function(){
    // Toggle Password Visibility
    $('.toggle-password').on('click', function(e){
        e.preventDefault();
        var target = $($(this).data('target'));
        if (target.attr('type') == 'password') {
            target.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            target.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Test Connection
    $('#btn-test-connection').on('click', function(){
        var btn = $(this);
        var resultSpan = $('#test-connection-result');
        btn.button('loading');
        resultSpan.html('');

        $.get(admin_url + 'shopify_connector/test_connection', function(response){
            btn.button('reset');
            if(response.success) {
                resultSpan.html('<span class="text-success"><i class="fa fa-check"></i> ' + response.message + '</span>');
            } else {
                resultSpan.html('<span class="text-danger"><i class="fa fa-times"></i> ' + response.message + '</span>');
            }
        }, 'json').fail(function(){
            btn.button('reset');
            resultSpan.html('<span class="text-danger"><i class="fa fa-times"></i> Server error.</span>');
        });
    });

    // Supplier dropdown logic in modal
    $('#mapping_fulfillment_model').on('change', function(){
        var val = $(this).val();
        if(val == 'B' || val == 'C') {
            $('#supplier_wrapper').show();
        } else {
            $('#supplier_wrapper').hide();
        }
    });

    // Initialize Product Mappings DataTable
    var mappingsTable = $('#product-mappings-table').DataTable({
        "ajax": admin_url + 'shopify_connector/get_product_mappings',
        "columns": [
            { "data": "shopify_product_id" },
            { "data": "shopify_variant_id" },
            { "data": "gs_sku" },
            { "data": "fulfillment_model" },
            { "data": "supplier_id" },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    return '<a href="#" class="btn btn-danger btn-icon delete-mapping" data-id="'+row.id+'"><i class="fa fa-remove"></i></a>';
                }
            }
        ]
    });

    // Save Mapping
    $('#btn-save-mapping').on('click', function(){
        var form = $('#productMappingForm');
        var data = form.serialize();
        $.post(admin_url + 'shopify_connector/save_product_mapping', data, function(response){
            if(response.success) {
                $('#productMappingModal').modal('hide');
                mappingsTable.ajax.reload();
                alert_float('success', 'Mapping saved');
            }
        }, 'json');
    });

    // Delete Mapping
    $('#product-mappings-table').on('click', '.delete-mapping', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        if(confirm_delete()) {
            $.post(admin_url + 'shopify_connector/delete_product_mapping/' + id, function(response){
                if(response.success) {
                    mappingsTable.ajax.reload();
                    alert_float('success', 'Mapping deleted');
                }
            }, 'json');
        }
    });

    // Import Products AJAX
    $('#btn-import-products').on('click', function(){
        var btn = $(this);
        btn.button('loading');
        $.post(admin_url + 'shopify_connector/import_shopify_products', function(response){
            btn.button('reset');
            if(response.success) {
                alert_float('success', response.message);
                mappingsTable.ajax.reload();
            }
        }, 'json');
    });
});
