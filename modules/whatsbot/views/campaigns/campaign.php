<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsbot/campaigns/save'), ['id' => 'campaign_form']); ?>
        <input type="hidden" name="id" id="id" value="<?php echo $campaign['id'] ?? ''; ?>" class="temp_id">
        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('send_new_campaign'); ?></h4>
        <div class="row mbot20">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('campaign'); ?></h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <?php echo render_input('name', 'campaign_name', $campaign['name'] ?? '', '', ['autocomplete' => 'off']); ?>
                        <?php echo render_select('sender_phone', $phone_numbers, ['id', 'display_phone_number', 'verified_name'], 'sender_phone', $campaign['sender_phone'] ?? ''); ?>
                        <?php echo render_select('rel_type', wb_get_rel_type(), ['key', 'name'], 'relation_type', $campaign['rel_type'] ?? ''); ?>
                        <?php echo render_select('template_id', $templates, ['id', 'template_name', 'language'], 'template', $campaign['template_id'] ?? ''); ?>
                        <div class="relation_wrapper hide">
                            <hr class="hr-panel-separator">
                            <div class="checkbox checkbox-primary checkbox-inline task-add-edit-public tw-pt-2 tw-pb-3 select_all">
                                <input type="checkbox" id="select_all" name="select_all" <?php echo isset($campaign['select_all']) && 1 == $campaign['select_all'] ? 'checked' : ''; ?>>
                                <label for="select_all" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?php echo _l('select_all_note_leads'); ?>"><?php echo _l('select_all'); ?></label>
                            </div>
                            <div style="width: 100%; height: 10px; border-bottom: 1px solid #ddd; text-align: center; margin: 20px 0px" class="orhr no-mtop">
                                <span style="font-size: 15px; background-color: #fff; padding: 0 4px;">
                                    <?php echo _l('or'); ?>
                                </span>
                            </div>
                            <?php echo render_select('status', [], [], 'status', $campaign['status'] ?? '', [], [], 'lead_status col-md-6 no-padding-left hide'); ?>
                            <?php echo render_select('source', [], [], 'source', $campaign['source'] ?? '', [], [], 'lead_source col-md-6 no-padding-right hide'); ?>
                            <?php echo render_select('groups[]', [], [], 'groups', $campaign['groups'] ?? '', ['multiple' => 1, 'data-actions-box' => 1, 'data-width' => '100%', 'data-live-search' => 1], [], 'groups hide', '', false); ?>
                            <?php echo render_select('lead_ids[]', [], [], 'leads', $campaign['lead_ids'] ?? '' ?? '', ['multiple' => 1, 'data-actions-box' => 1, 'data-width' => '100%', 'data-live-search' => 1], [], 'hide lead_ids', '', false); ?>
                            <?php echo render_select('contact_ids[]', [], [], 'contacts', $campaign['contact_ids'] ?? '' ?? '', ['multiple' => 1, 'data-actions-box' => 1, 'data-width' => '100%', 'data-live-search' => 1], [], 'hide contact_ids', '', false); ?>
                            <hr class="hr-panel-separator">
                        </div>
                        <?php echo render_datetime_input('scheduled_send_time', 'scheduled_send_time', $campaign['scheduled_send_time'] ?? '', ['data-date-min-date' => date('Y-m-d')]); ?>
                        <div class="form-group">
                            <label for="send_now" class="form-control-label"><?php echo _l('ignore_scheduled_time_and_send_now'); ?></label>
                            <div class="onoffswitch">
                                <input type="checkbox" id="send_now" class="onoffswitch-checkbox" value="1" name="send_now" <?php echo (isset($campaign) && 1 == $campaign['send_now']) ? 'checked' : ''; ?>>
                                <label class="onoffswitch-label" for="send_now"></label>
                            </div>
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
                                    <p><?php echo _l('send_to'); ?> : <span class="totalCount"></span></p>
                                    <button type="submit" class="btn btn-danger mtop15"><?php echo _l('send_campaign'); ?></button>
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
<?php init_tail(); ?>
<script>
    "use strict";

    appValidateForm($('#campaign_form'), {
        'name': 'required',
        'template_id': 'required',
        'rel_type': 'required',
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
        },
        'scheduled_send_time ': {
            required: {
                depends: function() {
                    return !$('#send_now').is(':checked');
                }
            }
        },
        'lead_ids[]': {
            required: {
                depends: function() {
                    return $('#rel_type').val() == 'leads';
                },
            },
        },
        'contact_ids[]': {
            required: {
                depends: function() {
                    return $('#rel_type').val() == 'contacts';
                },
            },
        }
    });

    $('#rel_type').on('change', function() {
        var value = '';
        value = $(this).val();
        $('.relation_wrapper, .select_all, .orhr').removeClass('hide');
        $('.lead_status, .lead_source, .groups').addClass('hide');
        if (value == 'leads') {
            $('.lead_ids').removeClass('hide');
            $('.contact_ids').addClass('hide');
            $('[for="select_all"]').attr('data-original-title', "<?php echo _l('select_all_note_leads'); ?>");
            get_leads();
            $.ajax({
                url: `${admin_url}whatsbot/campaigns/get_lead_data`,
                type: 'POST',
                dataType: 'json',
            }).done(function(res) {
                var status = '<?= isset($campaign) && !empty($campaign['status']) ? $campaign['status'] : ''; ?>';
                var source = '<?= isset($campaign) && !empty($campaign['source']) ? $campaign['source'] : ''; ?>';
                $.each(res.status, function(index, val) {
                    $('select[name="status"]').append(`<option value='${val.id}'>${val.name}</option>`);
                });
                $.each(res.source, function(index, val) {
                    $('select[name="source"]').append(`<option value='${val.id}'>${val.name}</option>`);
                });
                $('select[name="status"], select[name="source"]').selectpicker('refresh');
                get_leads(status, source);
                $('select[name="status"]').val(status).selectpicker('refresh');
                $('select[name="source"]').val(source).selectpicker('refresh');
                $('.lead_status, .lead_source').removeClass('hide');
            });
        } else if (value == 'contacts') {
            $('.contact_ids').removeClass('hide');
            $('.lead_ids').addClass('hide');
            $('[for="select_all"]').attr('data-original-title', "<?php echo _l('select_all_note_contacts'); ?>");
            get_contacts();
            $.ajax({
                url: `${admin_url}whatsbot/campaigns/get_contacts_gropus`,
                type: 'POST',
                dataType: 'json',
            }).done(function(res) {
                var groups = '<?= isset($campaign) && !empty($campaign['group']) ? $campaign['group'] : '[]'; ?>';
                $.each(res, function(index, val) {
                    $('select[name="groups[]"]').append(`<option value='${val.id}'>${val.name}</option>`);
                });
                $('select[name="groups[]"]').selectpicker('refresh');
                get_contacts(groups);
                $('select[name="groups[]"]').val(JSON.parse(groups)).selectpicker('refresh');
                $('.groups').removeClass('hide');
            });
        } else {
            $('.contact_ids, .lead_ids, .select_all, .orhr, .relation_wrapper').addClass('hide');
            $('#contact_ids, lead_ids').val('').trigger('change').selectpicker('refresh');
        }
        $('[for="select_all"]').text("<?php echo _l('select_all'); ?>" + ' ' + $('#rel_type :selected').text());
        $('#select_all').trigger('change');
    });

    $('select[name="status"], select[name="source"]').on('change ', function() {
        var status = $('select[name="status"]').val();
        var source = $('select[name="source"]').val();
        get_leads(status, source);
    });

    $('select[name="groups[]"]').on('change', function() {
        var groups = $('select[name="groups[]"]').val();
        get_contacts(groups);
    });

    function get_leads(status = '', source = '') {
        $.ajax({
            url: `${admin_url}whatsbot/campaigns/get_leads`,
            type: 'POST',
            dataType: 'json',
            data: {
                status: status,
                source: source,
            }
        }).done(function(res) {
            $('select[name="lead_ids[]"]').empty();
            $.each(res, function(index, val) {
                $('select[name="lead_ids[]"]').append(`<option value='${val.id}'>${val.name}</option>`);
            });
            var lead_ids = '<?= isset($campaign) && !empty($campaign['lead_ids']) ? json_encode($campaign['lead_ids']) : '[]'; ?>';
            $('select[name="lead_ids[]"]').val(JSON.parse(lead_ids)).selectpicker('refresh');
            $('#select_all').trigger('change');
        });
    }

    function get_contacts(group = '') {
        $.ajax({
            url: `${admin_url}whatsbot/campaigns/get_contacts`,
            type: 'POST',
            dataType: 'json',
            data: {
                group: group,
            }
        }).done(function(res) {
            $('select[name="contact_ids[]"]').empty();
            $.each(res, function(index, val) {
                $('select[name="contact_ids[]"]').append(`<option value='${val.id}'>${val.firstname} ${val.lastname}</option>`);
            });
            var contact_ids = '<?= isset($campaign) && !empty($campaign['contact_ids']) ? json_encode($campaign['contact_ids']) : '[]'; ?>';
            $('select[name="contact_ids[]"]').val(JSON.parse(contact_ids)).selectpicker('refresh');
            $('#select_all').trigger('change')
        });
    }

    $('#select_all').on('change', function() {
        let relType = $('#rel_type').val();
        let isChecked = $('#select_all').prop('checked');
        let leadIds = $('#lead_ids\\[\\]');
        let contactIds = $('#contact_ids\\[\\]');

        leadIds.prop('disabled', false);
        contactIds.prop('disabled', false);
        $('select[name="status"]').prop('disabled', false);
        $('select[name="source"]').prop('disabled', false);
        $('select[name="groups[]"]').prop('disabled', false);

        if (relType === 'leads') {
            leadIds.trigger('change');
        } else {
            contactIds.trigger('change');
        }

        if (isChecked) {
            leadIds.prop('disabled', true);
            contactIds.prop('disabled', true);
            $('select[name="status"]').prop('disabled', true);
            $('select[name="source"]').prop('disabled', true);
            $('select[name="groups[]"]').prop('disabled', true);
            let totalCount = (relType == 'leads') ? "<?php echo count($leads ?? []) . ' ' . _l('leads'); ?>" : "<?php echo count($contacts ?? []) . ' ' . _l('contacts'); ?>";
            $('.totalCount').text(totalCount);
        }

        $('#lead_ids\\[\\], #contact_ids\\[\\], select[name="status"], select[name="source"], select[name="groups[]"]').selectpicker('refresh');
    });

    $('#lead_ids\\[\\], #contact_ids\\[\\]').on('change', function() {
        const selectedCount = $(this).find('option:selected').length;
        $('.totalCount').text(selectedCount + ' ' + $('#rel_type :selected').text());
    });

    $('#preview_message').hide();

    $('#send_now').on('change', function() {
        $('#scheduled_send_time').prop("disabled", false);
        if ($('#send_now').prop('checked')) {
            $('#scheduled_send_time').prop("disabled", true);
        }
    });

    // trigger apply button on click for edit time
    <?php if (isset($campaign)) { ?> $('#template_id, #send_now, #rel_type').trigger('change');
        setTimeout(function() {
            $('.header_image').trigger('change');
            $('.header_document').trigger('change');
        }, 200);
    <?php } ?>
</script>
