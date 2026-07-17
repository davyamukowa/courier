<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'template_campaigns', 'label' => _l('campaigns'), 'icon' => 'fa-solid fa-bullhorn', 'url' => admin_url('whatsbot/campaigns')],
                    ['key' => 'csv_campaigns', 'label' => _l('csv_campaign'), 'icon' => 'fa-solid fa-file-csv', 'url' => admin_url('whatsbot/bulk_campaigns')],
                    ['key' => 'drip_sequences', 'label' => _l('drip_sequences'), 'icon' => 'fa-solid fa-water', 'url' => admin_url('whatsbot/drip_campaigns')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'csv_campaigns']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo form_open_multipart('', ['id' => 'bulk_campaign_form']); ?>
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('campaigns_from_csv_file'); ?></h4>
                <div class="row mbot20">
                    <div class="col-md-4">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('campaign'); ?></h4>
                                <div class="clearfix"></div>
                                <hr class="hr-panel-separator">
                                <?php echo render_input('name', 'campaign_name', '', '', ['autocomplete' => 'off']); ?>
                                <div class="dropzone dropzone-manual">
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <label for="" class="form-label"><?php echo _l('choose_csv_file'); ?></label>
                                        <u class="text-info"><a href="javascript:void(0)" id="download_campaign_sample_file"><?= _l('download_sample_and_read_rules'); ?></a></u>
                                    </div>
                                    <div id="dropzoneDragArea" class="dz-default dz-message">
                                        <span><?php echo _l('upload_csv'); ?></span>
                                    </div>
                                    <div class="dropzone-previews"></div>
                                    <button type="button" class="btn btn-primary pull-right" id="csv_upload_btn"><?= _l('upload'); ?></button>
                                </div>
                                <input type="hidden" id="json_file_path" name="json_file_path" value="">
                                <div class="error_note mtop10"></div>
                                <div class="hide bulk_template">
                                    <hr>
                                    <?php echo render_select('sender_phone', $phone_numbers, ['id', 'display_phone_number', 'verified_name'], 'sender_phone', $campaign['sender_phone'] ?? ''); ?>
                                    <?php echo render_select('bulk_template_id', $templates, ['id', 'template_name', 'language'], 'template', ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="variableDetails hide">
                        <div class="col-md-4">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('variables'); ?>
                                        </h4>
                                        <span class="text-muted"><?php echo _l('merge_field_note'); ?></span>
                                    </div>
                                    <div class="clearfix"></div>
                                    <hr class="hr-panel-separator">
                                    <div class="variables"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row" id="preview_message">
                                <div class="col-md-12">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin">
                                                <?php echo _l('preview'); ?>
                                            </h4>
                                            <div class="clearfix"></div>
                                            <hr class="hr-panel-separator">
                                            <div class="padding" style='background: url(" <?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/bg.png'); ?>");'>
                                                <div class="wtc_panel previewImage">
                                                </div>
                                                <div class="panel_s no-margin">
                                                    <div class="panel-body previewmsg"></div>
                                                </div>
                                                <div class="previewBtn">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin">
                                                <?php echo _l('send_campaign'); ?>
                                            </h4>
                                            <div class="clearfix"></div>
                                            <hr class="hr-panel-separator">
                                            <button type="submit" class="btn btn-danger mtop15" id="send_bulk_campaign"><?php echo _l('send_campaign'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="download_cav_sample_modal" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div>
                        <h4 class="modal-title"><?= _l('download_sample'); ?></h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <?= _l('csv_rule_1'); ?>
                        <?= _l('csv_rule_2'); ?>
                    </div>
                    <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-justify-between tw-items-center">
                        <?= _l('campaign');  ?>
                        <a href="<?= admin_url('whatsbot/bulk_campaigns/download_sample'); ?>" class="btn btn-success"><?= _l('download_sample'); ?></a>
                    </h4>
                    <div>
                        <div class="table-responsive no-dt">
                            <table class="table table-hover table-bordered no-mtop">
                                <thead>
                                    <tr>
                                        <th class="bold database_field_firstname" style="white-space: nowrap;"><?= _l('firstname'); ?></th>
                                        <th class="bold database_field_lastname" style="white-space: nowrap;"><?= _l('lastname'); ?></th>
                                        <th class="bold database_field_phoneno" style="white-space: nowrap;">
                                            <span class="text-danger">*</span> <?= _l('phoneno'); ?><br />
                                        </th>
                                        <th class="bold database_field_email" style="white-space: nowrap;"><?= _l('email'); ?></th>
                                        <th class="bold database_field_country" style="white-space: nowrap;"><?= _l('country'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= _l('sample_data'); ?></td>
                                        <td><?= _l('sample_data'); ?></td>
                                        <td><?= _l('sample_data'); ?></td>
                                        <td>66d824de53e6b@example.com</td>
                                        <td><?= _l('sample_data'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close"><?php echo _l('close'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loading_Modal">
    <div class="modal-dialog bulk_modal_1" role="document">
        <div class="modal-content bulk_modal_2">
            <div class="modal-body">
                <div class="spinner-border text-primary" role="status">
                    <img src="<?php echo base_url('assets/plugins/lightbox/images/loading.gif'); ?>" alt="" class="bulk_modal_3"> <span class="tw-font-semibold tw-text-md"><?= _l('please_wait_your_request_in_process'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";

    $('#preview_message').hide();
    $(document).on('click', '#load_model', function(event) {
        $('#loading_Modal').modal('show');
    });
    appValidateForm($('#bulk_campaign_form'), {
        'name': 'required',
        'bulk_template_id': {
            required: {
                depends: function() {
                    return (!$('.bulk_template').hasClass('hide')) ? true : false;
                }
            }
        },
        'image': {
            required: {
                depends: function() {
                    return empty($('#image_url').val()) ? true : false;
                },
            },
        },
        'document': {
            required: {
                depends: function() {
                    return (!$('.campaign_document').hasClass('hide')) ? true : false;
                }
            }
        }
    });

    $(document).on('submit', '#bulk_campaign_form', function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        $('#send_bulk_campaign').attr('disabled', true);
        $('#loading_Modal').modal('show');
        $.ajax({
            url: `${admin_url}whatsbot/bulk_campaigns/send`,
            type: 'post',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
        }).done(function(res) {
            $('#loading_Modal').modal('hide');
            $('#send_bulk_campaign').attr('disabled', false);
            alert_float(res.type, res.message);
            setTimeout(function() {
                window.location.reload();
            }, 3000);
        });
    });
</script>