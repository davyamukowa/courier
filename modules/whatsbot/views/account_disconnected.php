<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row mbot15">
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo _l('whatsapp_business_account'); ?>
                </h4>
            </div>
            <div class="col-md-6">
                <div class="tw-flex tw-gap-3 tw-items-center tw-justify-end">
                    <label for="enable_embaded_signin" class="no-margin"><?php echo _l('enable_embaded_signin'); ?></label>
                    <div class="onoffswitch">
                        <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_embaded_signin" name="enable_embaded_signin" checked>
                        <label class="onoffswitch-label no-margin" for="enable_embaded_signin"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo form_open('', ['id' => 'connect_form'], []); ?>
                <!-- step 1 -->
                <div class="col-md-6 mannual hide">
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('facebook_developer_account_facebook_app'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <span><a href="<?= site_url('download/preview_image?path=' . protected_file_url_by_path(module_dir_path('whatsbot', 'assets/images/app_id_secret.png'))) ?>" data-lightbox="customer-profile" class="display-block mbot5"><i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1"></i></a></span>
                                    <?= render_input('wb_fb_app_id', 'fb_app_id', get_option('wb_fb_app_id')); ?>
                                    <?= render_input('wb_fb_app_secret', 'fb_app_secret', get_option('wb_fb_app_secret')); ?>
                                </div>
                            </div>
                        </div>
                        <?php if (get_option('wb_webhook_configure') == 0) { ?>
                            <div class="panel-footer text-right">
                                <button type="submit" name="connect_webhook" value="submit" class="btn btn-success" id="connect_webhook"><i class="fa-solid fa-link tw-mr-1"></i><?php echo _l('connect_webhook'); ?></button>
                            </div>
                        <?php } else { ?>
                            <div class="panel-footer text-right">
                                <button type="submit" name="disconnect_webhook" value="submit" class="btn btn-danger" id="disconnect_webhook"><i class="fa-solid fa-link-slash tw-mr-1"></i><?php echo _l('disconnect_webhook'); ?></button>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('whatsApp_integration_setup'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('business_account_id_description'); ?>" data-placement="left"></i>
                                    <?php echo render_input('wac_business_account_id', _l('whatsapp_business_account_id'), get_option('wac_business_account_id')); ?>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('access_token_description'); ?>"></i>
                                        <label for="wac_access_token" class="control-label"><?php echo _l('whatsapp_access_token'); ?></label>
                                        <div class="input-group">
                                            <input id="wac_access_token" name="wac_access_token" class="form-control" value="<?= get_option('wac_access_token'); ?>" oninput="updateLink()">
                                            <span class="input-group-addon tw-cursor-pointer btn btn-primary" target="_blank" id="debugTokenButton" onclick="openDebugLink()">
                                                <?= _l('debug_token'); ?> <i class="fas fa-external-link-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer text-right">
                            <button type="submit" name="submit" value="submit" class="btn btn-success" id="configure"><i class="fa-solid fa-link tw-mr-1"></i><?php echo _l('configure'); ?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

                <div class="col-md-6 embaded">
                    <?php echo form_open(admin_url('whatsbot/save_settings'), ['id' => 'connect_form'], []); ?>
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('facebook_developer_account_info'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <span><a href="<?= site_url('download/preview_image?path=' . protected_file_url_by_path(module_dir_path('whatsbot', 'assets/images/app_id_secret.png'))) ?>" data-lightbox="customer-profile" class="display-block mbot5"><i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1"></i></a></span>
                                    <?= render_input('emb_fb_app_id', 'fb_app_id', get_option('wb_fb_app_id')); ?>
                                    <?= render_input('emb_fb_app_secret', 'fb_app_secret', get_option('wb_fb_app_secret')); ?>
                                    <?= render_input('emb_fb_config_id', 'fb_config_id', get_option('wb_fb_config_id')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-success"><?php echo (!empty(get_option('wb_fb_app_id')) && !empty(get_option('wb_fb_config_id')) && !empty(get_option('wb_fb_app_secret'))) ? _l('update_details') : _l('save_details') ?></button>
                        </div>
                    </div>
                    <?php if ((!empty(get_option('wb_fb_app_id')) && !empty(get_option('wb_fb_config_id')) && !empty(get_option('wb_fb_app_secret')))) { ?>
                        <div class="text-center">
                            <button style="background-color: #1877f2; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; height: 40px; padding: 0 34px;" id="wb_facebook_signin" class="btn btn-lg"><i class="fa-brands fa-facebook"></i> <?= _l('connect_with_facebook') ?></button>
                        </div>
                    <?php } ?>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Loading Modal -->
<div id="loadingModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="margin-top: 20%;">
        <div class="modal-content text-center p-3" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
            <!-- Modal Body with SVG Spinner -->
            <div class="modal-body">
                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#007bff" stroke-width="4" stroke-dasharray="31.4" stroke-dashoffset="0">
                        <animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite" />
                    </circle>
                </svg>
                <p class="mt-3"><?= _l('modal_processing_connect_account_note'); ?></p>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
<script>
    $(function() {
        var emb_signin = sessionStorage.getItem('emb_sign') === 'true';
        $('#enable_embaded_signin').prop('checked', emb_signin);

        if (emb_signin) {
            $('.mannual').addClass('hide');
            $('.embaded').removeClass('hide');
        } else {
            $('.embaded').addClass('hide');
            $('.mannual').removeClass('hide');
        }

        function updateLink() {
            var inputValue = document.getElementById('wac_access_token').value;
            var debugLink = "https://developers.facebook.com/tools/debug/accesstoken/?access_token=" + encodeURIComponent(inputValue);
            document.getElementById('debugTokenButton').setAttribute('data-url', debugLink);
        }

        function openDebugLink() {
            var debugLink = document.getElementById('debugTokenButton').getAttribute('data-url');
            window.open(debugLink, '_blank');
        }

        // Initialize the button with the current input value when the page loads
        updateLink();
        $('#connect_webhook').on('click', function(event) {
            event.preventDefault();
            $.ajax({
                url: `${admin_url}whatsbot/connect_webhook`,
                data: {
                    app_id: $('#wb_fb_app_id').val(),
                    app_secret: $('#wb_fb_app_secret').val(),
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                if (res.status) {
                    alert_float('success', res.message);
                } else {
                    alert_float('danger', res.message);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        });
        $('#disconnect_webhook').on('click', function(event) {
            event.preventDefault();
            $.ajax({
                url: `${admin_url}whatsbot/disconnect_webhook`,
                data: {
                    app_id: $('#wb_fb_app_id').val(),
                    app_secret: $('#wb_fb_app_secret').val(),
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                if (res.status) {
                    alert_float('success', res.message);
                } else {
                    alert_float('danger', res.message);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        });
        $('#configure').on('click', function(event) {
            event.preventDefault();
            $.ajax({
                url: `${admin_url}whatsbot/configure_account`,
                data: {
                    wba_id: $('#wac_business_account_id').val(),
                    access_token: $('#wac_access_token').val(),
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                if (res.status) {
                    alert_float('success', res.message);
                } else {
                    alert_float('danger', res.message);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        });

        $('#enable_embaded_signin').on('change', function() {
            var emb_signin = $(this).is(':checked');
            sessionStorage.setItem('emb_sign', emb_signin); // Store value in sessionStorage
            if (emb_signin) {
                $('.mannual').addClass('hide');
                $('.embaded').removeClass('hide');
            } else {
                $('.embaded').addClass('hide');
                $('.mannual').removeClass('hide');
            }
        }).trigger('change');

        var app_id = '<?= get_option('wb_fb_app_id') ?>';
        window.fbAsyncInit = function() {
            FB.init({
                appId: app_id,
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v21.0'
            });
        };

        $('#wb_facebook_signin').on('click', function() {
            $('#loadingModal').modal({
                backdrop: 'static', // Prevent closing by clicking outside
                keyboard: false // Prevent closing with ESC key
            }).modal('show');
            phoneNumberId = waBaId = '';
            event.preventDefault();
            // Continue with Facebook login
            try {
                FB.login(function(response) {
                    if (response.authResponse) {
                        const authResponse_code = response.authResponse.code;

                        // Send the code securely to your backend
                        $.ajax({
                            url: `${admin_url}whatsbot/emb_signin`,
                            type: 'POST',
                            contentType: 'application/json', // This sets the `Content-Type` header to `application/json`
                            data: JSON.stringify({
                                code: authResponse_code,
                                phoneNumberId: phoneNumberId,
                                waBaId: waBaId,
                            }),
                            dataType: 'json', // Expecting JSON response
                            success: function(data) {
                                $('#loadingModal').modal('hide');
                            },
                            error: function(xhr, status, error) {
                                alert_float('danger', '<?= _l('something_went_wrong') ?>');
                            }
                        }).done(function() {
                            setTimeout(() => {
                                window.location.reload();
                            }, 100);
                        });
                    } else {
                        $('#loadingModal').modal('hide');
                        alert_float('danger', '<?= _l('user_cancle_note') ?>');
                    }
                }, {
                    config_id: '<?= get_option('wb_fb_config_id') ?>',
                    redirect_uri: `${admin_url}whatsbot/connect`,
                    response_type: 'code',
                    override_default_response_type: true,
                    extras: {
                        setup: {},
                        featureType: '',
                        sessionInfoVersion: '2',
                    }
                });
                const sessionInfoListener = (event) => {
                    if (event.origin !== "https://www.facebook.com") return;
                    try {
                        const data = JSON.parse(event.data);
                        if (data.type === 'WA_EMBEDDED_SIGNUP') {
                            // if user finishes the Embedded Signup flow
                            if (data.event === 'FINISH') {
                                const {
                                    phone_number_id,
                                    waba_id
                                } = data.data;
                                phoneNumberId = phone_number_id;
                                waBaId = waba_id;
                            }
                            // if user cancels the Embedded Signup flow
                        } else if (data.event === 'CANCEL') {
                            $('#loadingModal').modal('hide');
                            // if user reports an error during the Embedded Signup flow
                        } else if (data.event === 'ERROR') {
                            $('#loadingModal').modal('hide');
                        }

                    } catch {
                        $('#loadingModal').modal('hide');
                        alert_float('danger', '<?= _l('something_went_wrong') ?>');
                    }
                };

                window.addEventListener('message', sessionInfoListener);
            } catch (error) {
                $('#loadingModal').modal('hide');
                alert_float('danger', error);
            }
        });

        // Initialize the button with the current input value when the page loads
        updateLink();
    });

    function updateLink() {
        var inputValue = document.getElementById('wac_access_token').value;
        var debugLink = "https://developers.facebook.com/tools/debug/accesstoken/?access_token=" + encodeURIComponent(inputValue);
        document.getElementById('debugTokenButton').setAttribute('data-url', debugLink);
    }

    function openDebugLink() {
        var debugLink = document.getElementById('debugTokenButton').getAttribute('data-url');
        window.open(debugLink, '_blank');
    }
</script>
