<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $health_status = json_decode(get_option('wb_health_data')); ?>
<style>
    .wrap-text-cust {
        display: inline-block;
        /* Allows wrapping */
        white-space: normal;
        /* Allows text wrapping */
        word-wrap: break-word;
        /* Breaks long words to wrap within the container */
        word-break: break-word;
        /* Ensures long word*/
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row mbot15">
            <div class="col-md-12 tw-flex tw-items-center tw-justify-between">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo _l('whatsapp_business_account'); ?>
                </h4>
                <button class="btn btn-info tw-w-80" data-toggle="modal" data-target="#qrCodeModal">
                    <span class="tw-white"><?php echo _l('click_to_get_qr_code'); ?></span>
                </button>
                <a href="<?= admin_url('whatsbot/disconnect') ?>" class="btn btn-danger _delete"><i class="fa-solid fa-link-slash tw-mr-1"></i><?php echo _l('disconnect_acount'); ?></a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">

                <div class="panel tw-p-6 tw-bg-black/5">
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('access_token_information'); ?></h4>
                        </div>
                        <div class="panel-body tw-flex tw-gap-3">
                            <div class="tw-flex tw-flex-col tw-gap-3">
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('access_token'); ?></label>
                                    <?php if (is_admin()) { ?>
                                        <span class="tw-text-black/50 wrap-text-cust"></i><?= get_option('wac_access_token') ?></span>
                                    <?php } else { ?>
                                        <span class="text-danger"><i class="fa fa-lock tw-mr-1.5"></i><?= _l('not_allowed_to_view') ?></span>
                                    <?php } ?>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('permission_scopes'); ?></label>
                                    <span class="tw-text-black/50"></i><?= implode(', <br>', $tocken_info->scopes); ?></span>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('issued_at'); ?></label>
                                    <span class="tw-text-black/50"><?= $tocken_info->issued_at; ?></span>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('expiry_at'); ?></label>
                                    <span class="tw-text-black/50"><?= empty($tocken_info->expires_at) ? 'N/A' : $tocken_info->expires_at; ?></span>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('webhook_url'); ?></label>
                                    <?php if (is_admin()) { ?>
                                        <span class="tw-text-black/50 wrap-text-cust"><?= implode(', <br>', array_column(array_column($phone_numbers, 'webhook_configuration'), 'application')); ?></span>
                                    <?php } else { ?>
                                        <span class="text-danger"><i class="fa fa-lock tw-mr-1.5"></i><?= _l('not_allowed_to_view') ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading tw-bg-white">
                            <h4 class="no-margin text-primary"><?= _l('send_test_message'); ?></h4>
                        </div>
                        <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('test_number_note'); ?>" data-placement="left"></i>
                                    <?php echo render_input('wb_test_number', _l('wb_number')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer text-right">
                            <button type="submit" name="submit" value="submit" class="btn btn-success" id="send_message"><i class="fa-regular fa-paper-plane tw-mr-1"></i><?php echo _l('send_message'); ?></button>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading tw-bg-white">
                            <h4 class="no-margin text-primary"><?= _l('verify_webhook'); ?></h4>
                        </div>
                        <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                            <div class="row text-center">
                                <div class="col-md-12">
                                    <a href="" class="btn btn-success btn-block verify_webhook"><?= _l('verify_webhook') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="panel tw-p-6 tw-bg-black/5">
                    <?php foreach ($phone_numbers as $phone) {
                        $isDefault = ($phone->id == get_option('wac_phone_number_id')); ?>
                        <div class="panel">
                            <div class="panel-heading tw-bg-white">
                                <h4 class="no-margin text-primary"><?php echo _l('phone_numbers'); ?></h4>
                            </div>
                            <div class="panel-body tw-flex tw-gap-3">
                                <div class="tw-flex tw-flex-col tw-gap-3 col-md-6">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label text-danger"><?php echo _l('display_phone_number'); ?></label>
                                        <span class="tw-text-black/50"></i><?php echo $phone->display_phone_number; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label text-danger"><?php echo _l('verified_name'); ?></label>
                                        <span class="tw-text-black/50"><?php echo $phone->verified_name; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label text-danger"><?php echo _l('number_id'); ?></label>
                                        <?php if (is_admin()) { ?>
                                            <span class="tw-text-black/50"><?php echo $phone->id; ?></span>
                                        <?php } else { ?>
                                            <span class="text-danger"><i class="fa fa-lock tw-mr-1.5"></i><?= _l('not_allowed_to_view') ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-3 col-md-6">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label text-danger"><?php echo _l('quality'); ?></label>
                                        <span id="qualityRating" style="color:<?= $phone->quality_rating ?>"><?php echo $phone->quality_rating; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label text-danger"><?php echo _l('status'); ?></label>
                                        <span class="tw-text-black/50"><?php echo $phone->code_verification_status; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer tw-bg-white">
                                <?php if ($isDefault) { ?>
                                    <a href="<?= 'https://business.facebook.com/wa/manage/phone-numbers/?waba_id=' . get_option('wac_business_account_id'); ?>" class="btn btn-primary" target="_blank"><?php echo _l('manage_phone_numbers'); ?><i class="fas fa-external-link-alt tw-ml-1"></i></a>
                                <?php } else { ?>
                                    <a href="#" class="btn btn-warning mark_as_default" data-phone_number_id="<?php echo $phone->id; ?>" data-default-phone-number="<?php echo $phone->display_phone_number; ?>">
                                        <i class="fa-solid fa-check tw-mr-1"></i>
                                        <?php echo _l('mark_as_default'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="panel">
                        <div class="panel-heading tw-bg-white">
                            <h4 class="no-margin text-primary"><?= _l('overall_health'); ?></h4>
                        </div>
                        <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                            <div class="tw-flex tw-flex-col">
                                <label class="control-label text-danger"><?= _l('whatsApp_business_id'); ?></label>
                                <?php if (is_admin()) { ?>
                                    <span class="tw-text-black/50"><?= get_option('wac_business_account_id'); ?></span>
                                <?php } else { ?>
                                    <span class="text-danger"><i class="fa fa-lock tw-mr-1.5"></i><?= _l('not_allowed_to_view') ?></span>
                                <?php } ?>
                            </div>
                            <div class="tw-flex tw-flex-col">
                                <label class="control-label text-danger"><?= _l('status_as_at'); ?></label>
                                <span class="tw-text-black/50"><?= get_option('wb_health_check_time'); ?></span>
                            </div>
                            <div class="tw-flex tw-flex-col">
                                <label class="control-label text-danger"><?= _l('overall_health_send_message'); ?></label>
                                <span class="tw-text-black/50"><?= $health_status->health_status->can_send_message; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php
                    foreach ($health_status->health_status->entities as $entity) { ?>
                        <div class="panel">
                            <div class="panel-heading tw-bg-white">
                                <h4 class="no-margin text-primary">
                                    <?= htmlspecialchars($entity->entity_type) ?> -
                                    <?php if (is_admin()) { ?>
                                        <?= htmlspecialchars($entity->id); ?>
                                    <?php } else { ?>
                                        <span class="text-danger"><i class="fa fa-lock tw-mr-1.5"></i><?= _l('not_allowed_to_view') ?></span>
                                    <?php } ?>
                                </h4>
                            </div>

                            <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label text-danger"><?= _l('can_send_message'); ?></label>
                                    <span class="tw-text-black/50"><?= htmlspecialchars($entity->can_send_message); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <a href="<?= admin_url('whatsbot/get_health_status'); ?>" class="btn btn-success"><?= _l('refresh_health_status'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Qr code modal Start -->
<div class="modal fade" id="qrCodeModal" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('scan_qr_code_to_start_chat'); ?></h4>
            </div>
            <div class="modal-body tw-bg-black/5">
                <div class="tw-bg-black/80 tw-flex tw-items-center tw-justify-center tw-p-4 tw-rounded">
                    <span class="tw-text-white"><?php echo _l('use_qr_code_to_invite'); ?></span>
                </div>
                <div class="panel tw-mt-6">
                    <div class="panel-heading tw-bg-white tw-flex tw-justify-center">
                        <h4 class="no-margin text-primary"><?= $default_number->verified_name . ' (' .  $default_number->display_phone_number . ')'; ?></h4>
                    </div>
                    <div class="panel-body tw-flex tw-flex-col tw-gap-2 tw-justify-center tw-items-center">
                        <img src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/qrcode.png?t=' . time()); ?>" alt="qr code" style="height:160px">
                        <span class="tw-text-base tw-text-black/50"><?= _l('phone_number'); ?></span>
                        <span class="text-success tw-text-base"><?= $default_number->display_phone_number; ?></span>
                        <div class="col-md-12">
                            <h5><?php echo _l('url_for_qr_image'); ?></h5>
                            <a class="copyText" href="<?= module_dir_url('whatsbot', 'assets/images/qrcode.png'); ?>"><?php echo module_dir_url('whatsbot', 'assets/images/qrcode.png'); ?></a>
                            <span class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn copyBtn"><?php echo _l('copy'); ?></span>
                        </div>
                        <div class="col-md-12">
                            <h5><?php echo _l('whatsapp_url'); ?></h5>
                            <a class="copyText" href="<?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?>"><?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?></a>
                            <span class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn copyBtn"><?php echo _l('copy'); ?></span>
                            <a href="<?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?>" class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn"><?php echo _l('whatsapp_now'); ?></a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- Qr code modal End -->
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
                <p class="mt-3"><?= _l('sending'); ?></p>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $('.mark_as_default').on('click', function() {
            $.ajax({
                url: `${admin_url}whatsbot/set_default_number_phone_number_id`,
                data: {
                    wac_phone_number_id: $(this).data('phone_number_id'),
                    wac_default_phone_number: $(this).data('default-phone-number')
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                location.reload();
            });
        });

        $('#send_message').on('click', function(event) {
            event.preventDefault();
            $.ajax({
                url: `${admin_url}whatsbot/send_test_message`,
                data: {
                    test_number: $('#wb_test_number').val(),
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                if (res.status) {
                    alert_float('success', res.message);
                } else {
                    alert_float('danger', res.message);
                }
            });
        });

        $('.verify_webhook').on('click', function() {
            event.preventDefault();
            var data = <?php echo json_encode(['message' => 'ctl_whatsbot_ping', 'identifier' => uniqid('ping_', true), 'timestamp' => date('Y-m-d H:i:s')]); ?>;
            $('#loadingModal').modal({
                backdrop: 'static', // Prevent closing by clicking outside
                keyboard: false // Prevent closing with ESC key
            }).modal('show');
            $.ajax({
                url: `${site_url}whatsbot/whatsapp_webhook`,
                contentType: 'application/json',
                type: 'POST',
                data: JSON.stringify(data)
            }).done(function(res) {
                res = JSON.parse(res);
                if (res.status) {
                    alert_float('success', "<?= _l('webhook_received_successfully') ?>");
                } else {
                    alert_float('danger', "<?= _l('something_went_wrong') ?>");
                }
                $('#loadingModal').modal('hide').attr('aria-hidden', 'true');
            });
        });
    });
</script>
