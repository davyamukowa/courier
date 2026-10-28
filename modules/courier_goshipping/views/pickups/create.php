<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'pickups']); ?>

<?php
echo '<!-- Include Flatpickr CSS -->';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';

// Include Select2 CSS
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';

?>
<style>
    .req { color: #e74c3c; margin-left: 2px; }
    /* Checkbox Styles */
    .custom-checkbox {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .custom-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        margin-left: 10px;
        margin-bottom: 5px;
    }

    .checkbox-text {
        font-weight: bold;
        color: #333;
    }

    .select2-container .select2-selection--single {
        background-color: #f9fafb;
        border: 1px solid #d1d5db;
        color: #111827;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        padding: 5px 10px 10px 10px;
        width: 100%;
        height: 40px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select2-selection__arrow {
        transform: translateY(30%);
    }

    .cgs-input-error,
    .custom-input.cgs-input-error,
    .custom-select.cgs-input-error,
    .custom-textarea.cgs-input-error,
    .select2-container.cgs-input-error .select2-selection {
        border: 2px solid #e74c3c !important;
        background-color: #fff9f9 !important;
        box-shadow: 0 0 4px rgba(231, 76, 60, 0.4) !important;
    }
    .cgs-field-error-msg {
        color: #e74c3c !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        margin-top: 4px !important;
        display: block !important;
    }
    .cgs-validation-alert {
        background: #fde8e8 !important;
        border-left: 5px solid #e74c3c !important;
        color: #9b1c1c !important;
        padding: 14px 18px !important;
        border-radius: 6px !important;
        margin-bottom: 20px !important;
        font-size: 14px !important;
        box-shadow: 0 2px 6px rgba(231,76,60,0.15) !important;
    }
    .cgs-validation-alert h5 {
        color: #9b1c1c !important;
        font-weight: 700 !important;
        margin: 0 0 6px 0 !important;
        font-size: 15px !important;
    }
    .cgs-validation-alert ul {
        margin: 6px 0 0 18px !important;
        padding: 0 !important;
    }
    .cgs-validation-alert li {
        margin-bottom: 3px !important;
    }
</style>

        <div class="row">
            <?php echo form_open('admin/courier_goshipping/pickups/store', ['id' => 'create-pickup-form']); ?>
            <div class="col-md-12">
                <div class="cgs-card">
                        <div class="cgs-card__header">
                            <h4 class="cgs-card__title"><i class="fa fa-truck"></i> GO Shipping Cargo — Create Pickup</h4>
                            <div class="cgs-card__actions">
                                <a class="cgs-btn cgs-btn--outline cgs-btn--sm"
                                   href="<?php echo admin_url('courier_goshipping/pickups/main'); ?>">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Pickup Dashboard
                                </a>
                                <a class="cgs-btn cgs-btn--outline cgs-btn--sm"
                                   href="<?php echo admin_url('courier_goshipping/pickups/index'); ?>">
                                    <i class="fa fa-list"></i> View Pickups
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errors) || validation_errors()): ?>
                            <div id="cgs-pickup-server-alert" class="cgs-validation-alert" style="margin: 15px 20px 0 20px;">
                                <h5><i class="fa fa-exclamation-triangle"></i> Please correct the following errors:</h5>
                                <div><?php echo !empty($errors) ? $errors : validation_errors(); ?></div>
                            </div>
                        <?php endif; ?>

                        <div id="cgs-pickup-client-alert" class="cgs-validation-alert" style="display:none; margin: 15px 20px 0 20px;">
                            <h5><i class="fa fa-exclamation-triangle"></i> Please fix the following highlighted errors before submitting:</h5>
                            <ul id="cgs-pickup-client-error-list"></ul>
                        </div>

                        <section class="custom-section">
                            <div class="custom-form-grid">
                                <!--Pickup Information-->
                                <div class=" section-container">
                                    <div class="section-label">Pickup Information</div>
                                    <div style="padding-left:15px; padding-right:15px; margin-top:10px;" class="row">
                                        <div class="custom-form-group col-md-6 col-sm-12">
                                            <label for="pickup_date" class="custom-label">Pickup Date <span class="req">*</span></label>
                                            <input type="text" id="pickup_date" name="pickup_date"
                                                   class="custom-input <?php echo form_error('pickup_date') ? 'cgs-input-error' : ''; ?>" placeholder="Choose Date" value="<?php echo set_value('pickup_date'); ?>" required>
                                            <?php echo form_error('pickup_date', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                        <div class="custom-form-group col-md-3 col-sm-12">
                                            <label for="pickup_start_time" class="custom-label">Pickup Start Time <span class="req">*</span></label>
                                            <input type="text" id="pickup_start_time" name="pickup_start_time"
                                                   class="custom-input pickup_time <?php echo form_error('pickup_start_time') ? 'cgs-input-error' : ''; ?>" placeholder="Choose Start Time" value="<?php echo set_value('pickup_start_time'); ?>" required>
                                            <?php echo form_error('pickup_start_time', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                        <div class="custom-form-group col-md-3 col-sm-12">
                                            <label for="pickup_end_time" class="custom-label">Pickup End Time <span class="req">*</span></label>
                                            <input type="text" id="pickup_end_time" name="pickup_end_time"
                                                   class="custom-input pickup_time <?php echo form_error('pickup_end_time') ? 'cgs-input-error' : ''; ?>" placeholder="Choose End Time" value="<?php echo set_value('pickup_end_time'); ?>" required>
                                            <?php echo form_error('pickup_end_time', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div style="padding-left:15px; padding-right:15px;" class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="country_id" class="custom-label">Country <span class="req">*</span></label>
                                                <?php echo form_dropdown('country_id', array_column($countries, 'short_name', 'country_id'), set_value('country_id'), ['id' => 'country_id', 'class' => 'custom-select' . (form_error('country_id') ? ' cgs-input-error' : '')]); ?>
                                                <?php echo form_error('country_id', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="state_id" class="custom-label">State</label>
                                                <select name="state_id" id="state_id"
                                                        class="custom-select <?php echo form_error('state_id') ? 'cgs-input-error' : ''; ?>">
                                                    <option value="">Select State</option>
                                                </select>
                                                <?php echo form_error('state_id', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="padding-left:15px; padding-right:15px;" class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <label for="address" class="custom-label">Address <span class="req">*</span></label>
                                                <textarea id="address" name="address"
                                                          class="custom-textarea <?php echo form_error('address') ? 'cgs-input-error' : ''; ?>"
                                                          rows="3"
                                                          placeholder="Enter your address here..."><?php echo set_value('address'); ?></textarea>
                                                <?php echo form_error('address', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="custom-form-group">
                                                <div id="address_type" style="display: flex; gap: 20px;">
                                                    <label>
                                                        <input type="radio" name="address_type"
                                                               value="zip_code" <?php echo set_radio('address_type', 'zip_code', true); ?>>
                                                        Zipcode
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="address_type"
                                                               value="postal_code" <?php echo set_radio('address_type', 'postal_code'); ?>> Postal
                                                        Address
                                                    </label>
                                                </div>
                                                <?php echo form_input(['id' => 'zipcode', 'name' => 'zipcode', 'type' => 'text', 'class' => 'custom-input' . (form_error('zipcode') ? ' cgs-input-error' : ''), 'placeholder' => 'Zipcode', 'value' => set_value('zipcode')]); ?>
                                                <?php echo form_error('zipcode', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="vehicle_type" class="custom-label">Vehicle Type <span class="req">*</span></label>
                                            <select name="vehicle_type" id="vehicle_type"
                                                    class="custom-select <?php echo form_error('vehicle_type') ? 'cgs-input-error' : ''; ?>">
                                                <option value="truck" <?php echo (set_select('vehicle_type', 'TRUCK') ?: set_select('vehicle_type', 'truck', true)); ?>>Truck</option>
                                                <option value="van" <?php echo (set_select('vehicle_type', 'VAN') ?: set_select('vehicle_type', 'van')); ?>>Van</option>
                                                <option value="motorcycle" <?php echo (set_select('vehicle_type', 'MOTORCYCLE') ?: set_select('vehicle_type', 'motorcycle')); ?>>Motorcycle</option>
                                                <option value="other" <?php echo (set_select('vehicle_type', 'OTHER') ?: set_select('vehicle_type', 'other')); ?>>Other</option>
                                            </select>
                                            <?php echo form_error('vehicle_type', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="driver_id" class="custom-label">Driver <span class="req">*</span></label>
                                            <?php
                                            // Prepare the dropdown options by concatenating first name and last name
                                            $drivers_dropdown = ['' => '-- Select Driver --'];
                                            foreach ($drivers as $driver) {
                                                $drivers_dropdown[$driver['staffid']] = $driver['firstname'] . ' ' . $driver['lastname'];
                                            }
                                            echo form_dropdown('driver_id', $drivers_dropdown, set_value('driver_id'), ['id' => 'driver_id', 'class' => 'custom-select' . (form_error('driver_id') ? ' cgs-input-error' : ''), 'style' => 'width: 100%;']);
                                            ?>
                                            <?php echo form_error('driver_id', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                </div>
                                <!--End Of Pickup Information-->

                                <!--Recipient Information-->
                                <div class="row section-container">
                                    <div class="section-label">Contact Person</div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="contact_first_name" class="custom-label">First Name <span class="req">*</span></label>
                                            <input name="contact_first_name" id="contact_first_name" type="text"
                                                   class="custom-input <?php echo form_error('contact_first_name') ? 'cgs-input-error' : ''; ?>"
                                                   value="<?php echo set_value('contact_first_name'); ?>"
                                                   placeholder="First Name">
                                            <?php echo form_error('contact_first_name', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="contact_last_name" class="custom-label">Last Name <span class="req">*</span></label>
                                            <input name="contact_last_name" id="contact_last_name" type="text"
                                                   class="custom-input <?php echo form_error('contact_last_name') ? 'cgs-input-error' : ''; ?>"
                                                   value="<?php echo set_value('contact_last_name'); ?>"
                                                   placeholder="Last Name">
                                            <?php echo form_error('contact_last_name', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="contact_phone_number" class="custom-label">Phone <span class="req">*</span></label>
                                            <input name="contact_phone_number" id="contact_phone_number" type="text"
                                                   class="custom-input <?php echo form_error('contact_phone_number') ? 'cgs-input-error' : ''; ?>"
                                                   value="<?php echo set_value('contact_phone_number'); ?>"
                                                   placeholder="Phone Number">
                                            <?php echo form_error('contact_phone_number', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-form-group">
                                            <label for="email" class="custom-label">Email</label>
                                            <input name="contact_email" id="email" type="text"
                                                   class="custom-input <?php echo form_error('contact_email') ? 'cgs-input-error' : ''; ?>"
                                                   value="<?php echo set_value('contact_email'); ?>"
                                                   placeholder="Email">
                                            <?php echo form_error('contact_email', '<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                        </div>
                                    </div>

                                </div>
                                <!--End of Recipient Information-->
                            </div>
                        </section>
                        <button type="submit" class="cgs-btn cgs-btn--primary">Add pickup</button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<!-- Scripts should be at the end of the body -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    $(document).ready(function () {

        $('#country_id').select2({});
        $('#state_id').select2({});
        $('#driver_id').select2({});

        var STATES_URL = '<?php echo admin_url("courier_goshipping/states"); ?>';
        var savedStateId = '<?php echo set_value("state_id"); ?>';

        function getStates(countryId) {
            var sel = $('#state_id');
            if (!countryId) {
                sel.html('<option value="">Select State</option>');
                return;
            }
            sel.html('<option value="">Loading...</option>');
            $.ajax({
                url:      STATES_URL,
                type:     'GET',
                data:     { country_id: countryId },
                dataType: 'json',
                success: function (res) {
                    var html = '<option value="">Select State</option>';
                    if (res.states && res.states.length) {
                        $.each(res.states, function (i, s) {
                            var isSelected = (savedStateId && String(savedStateId) === String(s.id)) ? ' selected' : '';
                            html += '<option value="' + s.id + '"' + isSelected + '>' + s.name + '</option>';
                        });
                    } else {
                        html = '<option value="">No states available</option>';
                    }
                    sel.html(html);
                    sel.trigger('change');
                },
                error: function () {
                    sel.html('<option value="">Could not load states</option>');
                }
            });
        }

        // Auto-load states for the currently selected country on page load
        var initialCountry = $('#country_id').val();
        if (initialCountry) {
            getStates(initialCountry);
        }

        $('#country_id').change(function () {
            getStates($(this).val());
        });

        flatpickr("#pickup_date", {
            dateFormat: "Y-m-d",
            minDate: "today", // Prevent selection of past dates
            disableMobile: true // Forces desktop UI even on mobile devices
        });

        // Initialize Flatpickr for time range selection
        flatpickr(".pickup_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false, // Use 12-hour time format with AM/PM
            defaultHour: 9,  // Set the default hour to something other than 12
            defaultMinute: 0,  // Set the default minute
            minuteIncrement: 30,  // Allow increments every 30 minutes
            disableMobile: true // Forces desktop UI even on mobile devices
        });

        /* ── Client-side Validation & Real-time error handling ── */
        var $pickupForm = $('#create-pickup-form');
        if ($pickupForm.length) {
            function clearFieldError($input) {
                $input.removeClass('cgs-input-error');
                if ($input.hasClass('select2-hidden-accessible')) {
                    $input.next('.select2-container').removeClass('cgs-input-error');
                }
                $input.closest('.custom-form-group').find('.cgs-field-error-msg').remove();
            }

            function setFieldError($input, msg) {
                $input.addClass('cgs-input-error');
                if ($input.hasClass('select2-hidden-accessible')) {
                    $input.next('.select2-container').addClass('cgs-input-error');
                }
                var $group = $input.closest('.custom-form-group');
                $group.find('.cgs-field-error-msg').remove();
                $group.append('<div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>');
            }

            // Real-time error removal
            $pickupForm.on('input change', 'input, select, textarea', function () {
                clearFieldError($(this));
                if ($pickupForm.find('.cgs-input-error').length === 0) {
                    $('#cgs-pickup-client-alert').slideUp(200);
                }
            });
            $pickupForm.on('select2:select select2:clear', 'select', function () {
                clearFieldError($(this));
                if ($pickupForm.find('.cgs-input-error').length === 0) {
                    $('#cgs-pickup-client-alert').slideUp(200);
                }
            });

            $pickupForm.on('submit', function (e) {
                var errors = [];
                var $firstInvalid = null;

                function addError($field, fieldLabel, errorMsg) {
                    setFieldError($field, errorMsg);
                    errors.push(fieldLabel + ': ' + errorMsg);
                    if (!$firstInvalid) {
                        $firstInvalid = $field;
                    }
                }

                $pickupForm.find('.cgs-input-error').removeClass('cgs-input-error');
                $pickupForm.find('.cgs-field-error-msg').remove();

                // 1. Pickup Date
                var $pDate = $('#pickup_date');
                if (!$.trim($pDate.val())) {
                    addError($pDate, 'Pickup Date', 'Pickup Date is required');
                }

                // 2. Pickup Start Time
                var $pStart = $('#pickup_start_time');
                if (!$.trim($pStart.val())) {
                    addError($pStart, 'Pickup Start Time', 'Pickup Start Time is required');
                }

                // 3. Pickup End Time
                var $pEnd = $('#pickup_end_time');
                if (!$.trim($pEnd.val())) {
                    addError($pEnd, 'Pickup End Time', 'Pickup End Time is required');
                }

                // 4. Country
                var $pCountry = $('#country_id');
                if (!$pCountry.val()) {
                    addError($pCountry, 'Country', 'Country is required');
                }

                // 5. Address
                var $pAddr = $('#address');
                if (!$.trim($pAddr.val())) {
                    addError($pAddr, 'Address', 'Address is required');
                }

                // 6. Vehicle Type
                var $pVehicle = $('#vehicle_type');
                if (!$pVehicle.val()) {
                    addError($pVehicle, 'Vehicle Type', 'Vehicle Type is required');
                }

                // 7. Driver
                var $pDriver = $('#driver_id');
                if (!$pDriver.val()) {
                    addError($pDriver, 'Driver', 'Please select a driver');
                }

                // 8. Contact First Name
                var $cfn = $('#contact_first_name');
                if (!$.trim($cfn.val())) {
                    addError($cfn, 'Contact First Name', 'First Name is required');
                }

                // 9. Contact Last Name
                var $cln = $('#contact_last_name');
                if (!$.trim($cln.val())) {
                    addError($cln, 'Contact Last Name', 'Last Name is required');
                }

                // 10. Contact Phone
                var $cphone = $('#contact_phone_number');
                var phoneVal = $.trim($cphone.val()).replace(/[\s\-\(\)\+]/g, '');
                if (!phoneVal) {
                    addError($cphone, 'Contact Phone', 'Phone Number is required');
                } else if (phoneVal.length < 8) {
                    addError($cphone, 'Contact Phone', 'Phone Number must be at least 8 digits');
                }

                // 11. Contact Email (optional, but must be valid if entered)
                var $cemail = $('#email');
                var emailVal = $.trim($cemail.val());
                if (emailVal) {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailVal)) {
                        addError($cemail, 'Contact Email', 'Please enter a valid email address');
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    var $alert = $('#cgs-pickup-client-alert');
                    var $list = $('#cgs-pickup-client-error-list');
                    $list.empty();
                    errors.forEach(function (err) {
                        $list.append('<li>' + err + '</li>');
                    });
                    $alert.slideDown(200);

                    if ($firstInvalid && $firstInvalid.length) {
                        $('html, body').animate({
                            scrollTop: Math.max(0, $alert.offset().top - 80)
                        }, 400, function () {
                            if ($firstInvalid.is(':visible')) {
                                $firstInvalid.focus();
                            }
                        });
                    }
                    return false;
                }

                $('#cgs-pickup-client-alert').hide();
                return true;
            });
        }

    });
</script>

