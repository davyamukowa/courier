<script type="text/javascript">
var fnServerParams;
(function($) {
    "use strict";

    appValidateForm($('#fuel-form'), {
        vehicle_id: 'required',
        fuel_time:  'required',
        trip_type:  'required',
    }, fuel_form_handler);

    fnServerParams = {
        "from_date":  '[name="from_date"]',
        "to_date":    '[name="to_date"]',
        "fuel_type":  '[name="_fuel_type"]',
    };

    /* ── Open modal for a new entry ────────────────────────────── */
    $('.add-new-fuel').on('click', function() {
        resetFuelModal();
        $('#fuel-modal-title').text('Add Fuel / Trip Odometer Entry');
        $('#fuel-modal').find('button[type="submit"]').prop('disabled', false);
        $('#fuel-modal').modal('show');
    });

    /* ── Trip-type change handler ───────────────────────────────── */
    $('#fuel_trip_type').on('change', function() {
        applyTripTypeUI($(this).val());
    });

    /* ── Auto-fill odometer from selected assignment ─────────── */
    $('#fuel_assignment_id').on('change', function() {
        var opt  = $(this).find('option:selected');
        var type = $('#fuel_trip_type').val();
        var vid  = opt.data('vehicle');
        if (vid) {
            $('select[name="vehicle_id"]').val(vid).change();
        }
        if (type === 'pre_trip') {
            var startOdo = opt.data('start');
            if (startOdo) { $('#fuel_odometer').val(startOdo); }
        }
    });

    /* ── Filters trigger table reload ───────────────────────────── */
    $('select[name="_fuel_type"]').on('change', function() { init_fuel_table(); });
    $('input[name="from_date"]').on('change',   function() { init_fuel_table(); });
    $('input[name="to_date"]').on('change',     function() { init_fuel_table(); });

    init_fuel_table();

    $("input[data-type='currency']").on({
        keyup: function() { formatCurrency($(this)); },
        blur:  function() { formatCurrency($(this), "blur"); }
    });

})(jQuery);

/* ── Helpers ─────────────────────────────────────────────────────── */

function applyTripTypeUI(type) {
    "use strict";
    var $afterRow   = $('#odometer-after-row');
    var $beforeRow  = $('#odometer-before-row');
    var $fuelSect   = $('#fuel-details-section');
    var $required   = $('.trip-required');
    var $hint       = $('.odo-hint');

    if (type === 'pre_trip') {
        $beforeRow.show();
        $afterRow.hide();
        $fuelSect.show();
        $required.show();
        $hint.text('Odometer at start of trip (before departure).');
        $('#fuel_odometer').attr('placeholder', 'km / miles before trip');
        $('#fuel_odometer_after').removeAttr('required');
    } else if (type === 'post_trip') {
        $beforeRow.show();
        $afterRow.show();
        $fuelSect.show();
        $required.show();
        $hint.text('Odometer when fuelling started (may equal pre-trip end reading).');
        $('#fuel_odometer').attr('placeholder', 'km / miles at fuelling point');
        $('#fuel_odometer_after').attr('required', 'required');
    } else {
        // regular
        $beforeRow.show();
        $afterRow.hide();
        $fuelSect.show();
        $required.hide();
        $hint.text('Current odometer reading at time of fuelling.');
        $('#fuel_odometer').attr('placeholder', 'km / miles');
        $('#fuel_odometer_after').removeAttr('required');
    }
}

function resetFuelModal() {
    "use strict";
    $('input[name="id"]').val('');
    $('select[name="vehicle_id"]').val('').change();
    $('select[name="vendor_id"]').val('').change();
    $('select[name="fuel_type"]').val('').change();
    $('select[name="assignment_id"]').val('').change();
    $('#fuel_trip_type').val('regular').change();
    $('input[name="fuel_time"]').val('');
    $('input[name="odometer"]').val('');
    $('input[name="odometer_after"]').val('');
    $('input[name="gallons"]').val('');
    $('input[name="price"]').val('');
    $('input[name="reference"]').val('');
    $('textarea[name="notes"]').val('');
    applyTripTypeUI('regular');
}

function init_fuel_table() {
    "use strict";
    if ($.fn.DataTable.isDataTable('.table-fuel')) {
        $('.table-fuel').DataTable().destroy();
    }
    initDataTable('.table-fuel', admin_url + 'fleet/fuel_history_table', [0], [0], fnServerParams, [1, 'desc']);
    $('.dataTables_filter').addClass('hide');
}

function edit_fuel(id) {
    "use strict";
    $('#fuel-modal').find('button[type="submit"]').prop('disabled', false);

    requestGetJSON(admin_url + 'fleet/get_data_fuel/' + id).done(function(response) {
        resetFuelModal();
        $('#fuel-modal-title').text('Edit Fuel Entry');
        $('#fuel-modal').modal('show');

        var tripType = response.trip_type || 'regular';
        $('select[name="vehicle_id"]').val(response.vehicle_id).change();
        $('select[name="vendor_id"]').val(response.vendor_id).change();
        $('select[name="fuel_type"]').val(response.fuel_type).change();
        $('select[name="assignment_id"]').val(response.assignment_id || '').change();
        $('#fuel_trip_type').val(tripType).change();
        $('input[name="fuel_time"]').val(response.fuel_time);
        $('input[name="id"]').val(id);
        $('input[name="odometer"]').val(response.odometer);
        $('input[name="odometer_after"]').val(response.odometer_after || '');
        $('input[name="gallons"]').val(response.gallons);
        $('input[name="price"]').val(response.price);
        $('input[name="reference"]').val(response.reference);
        $('textarea[name="notes"]').val(response.notes);

        applyTripTypeUI(tripType);
    });
}

function fuel_form_handler(form) {
    "use strict";
    $('#fuel-modal').find('button[type="submit"]').prop('disabled', true);

    var formURL  = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type:        $(form).attr('method'),
        data:        formData,
        mimeType:    $(form).attr('enctype'),
        contentType: false,
        cache:       false,
        processData: false,
        url:         formURL,
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
            alert_float('success', response.message);
            init_fuel_table();
        } else {
            alert_float('danger', response.message);
        }
        $('#fuel-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', 'Request failed');
    });

    return false;
}

/* ── Bulk-action handler ─────────────────────────────────────────── */
function bulk_action(event) {
    "use strict";
    if (confirm_delete()) {
        var ids  = [],
            data = {};
        data.mass_delete = $('#mass_delete').prop('checked');

        var rows = $($('#fuel_bulk_actions').attr('data-table')).find('tbody tr');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
            $.post(admin_url + 'fleet/fuel_bulk_action', data).done(function() {
                window.location.reload();
            });
        }, 200);
    }
}

/* ── Currency formatter ──────────────────────────────────────────── */
function formatNumber(n) {
    "use strict";
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function formatCurrency(input, blur) {
    "use strict";
    var input_val = input.val();
    if (input_val === "") { return; }
    var original_len = input_val.length;
    var caret_pos    = input.prop("selectionStart");

    if (input_val.indexOf(".") >= 0) {
        var decimal_pos = input_val.indexOf(".");
        var left_side   = formatNumber(input_val.substring(0, decimal_pos));
        var right_side  = formatNumber(input_val.substring(decimal_pos));
        right_side      = right_side.substring(0, 2);
        input_val       = left_side + "." + right_side;
    } else {
        input_val = formatNumber(input_val);
    }
    input.val(input_val);
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}
</script>
