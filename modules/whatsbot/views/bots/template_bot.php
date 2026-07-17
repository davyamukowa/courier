<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsbot/bots/saveTemplateBot'), ['id' => 'template_bot_form'], ['is_bot' => $bot['is_bot'] ?? '1', 'is_bot_active' => $bot['is_bot_active'] ?? '1']); ?>
        <h3 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('create_new_template_bot'); ?></h3>
        <input type="hidden" name="id" id="id" value="<?php echo $bot['id'] ?? ''; ?>" class="temp_id">
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo (isset($bot)) ? _l('edit') . ' #' . $bot['name'] : _l('template_bot'); ?></h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <?php echo render_input('name', 'bot_name', $bot['name'] ?? '', '', ['autocomplete' => 'off']); ?>
                        <?php echo render_select('rel_type', wb_get_rel_type(), ['key', 'name'], 'relation_type', $bot['rel_type'] ?? ''); ?>
                        <?php echo render_input('phone_number', 'phone_number', $bot['phone_number'] ?? '', '', ['placeholder' => _l('phone_number')], [], 'hide'); ?>
                        <?php
                        $selected_events = [];
                        if (!empty($bot['crm_events'])) {
                            $selected_events = explode(',', $bot['crm_events']);
                        }
                        ?>
                        <div class="form-group hide">
                            <label><?= _l('crm_events'); ?></label>
                            <select id="crm_events" name="crm_events[]" class="form-control selectpicker" multiple data-live-search="true" data-actions-box="true" title="<?= _l('select_crm_events'); ?>">
                                <?php
                                $events = wb_get_crm_events();
                                $grouped = [];
                                foreach ($events as $event) {
                                    $grouped[$event['group']][] = $event;
                                }
                                foreach ($grouped as $groupName => $items) {
                                    echo "<optgroup label='{$groupName}'>";   
                                    foreach ($items as $event) {
                                        $selected = in_array($event['key'], $selected_events) ? 'selected' : '';
                                        echo "<option value='{$event['key']}' {$selected}>{$event['name']}</option>";
                                    }
                                    echo "</optgroup>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group hide" id="send_crm_event_pdf_wrapper">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" id="send_crm_event_pdf" name="send_crm_event_pdf" value="1" <?= !empty($bot['send_crm_event_pdf']) ? 'checked' : ''; ?>>
                                <label for="send_crm_event_pdf"><?= _l('send_crm_event_pdf'); ?></label>
                            </div>
                            <p class="text-muted no-margin"><?= _l('send_crm_event_pdf_help'); ?></p>
                        </div>
                        <?php echo render_select('webhook_id', wb_get_webhooks(), ['id', 'name'], 'webhook_id', $bot['webhook_id'] ?? '', [], [], 'hide'); ?>
                        <?php echo render_select('template_id', $templates, ['id', 'template_name', 'language'], 'template', $bot['template_id'] ?? ''); ?>
                        <?php echo render_select('bot_type', wb_get_reply_type(), ['id', 'label'], 'reply_type', $bot['bot_type'] ?? '', [], []); ?>
                        <div class="alert_default_message hide">
                            <div class="alert alert-warning">
                                <?= _l('default_message_note'); ?>
                            </div>
                        </div>
                        <div class="form-group trigger_input <?= (isset($bot) && ($bot['rel_type'] == 'webhooks' || $bot['rel_type'] == 'crm_events')) ? 'hide' : '' ?>">
                            <label for="trigger" class="control-label"><?php echo _l('trigger_keyword'); ?></label>
                            <input type="text" class="tagsinput" id="trigger" name="trigger" value="<?= $bot['trigger'] ?? ''; ?>" data-role="tagsinput">
                        </div>
                        <button type="submit" class="btn btn-success"><?php echo _l('save_bot'); ?></button>
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
                            <div class="variables">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row" id="preview_message">
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('preview'); ?></h4>
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
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";
    appValidateForm($('#template_bot_form'), {
        'name': 'required',
        'template_id': 'required',
        'rel_type': 'required',
        // 'bot_type': 'required',
        'trigger': {
            required: {
                depends: function() {
                    return $('#trigger_input').hide() ? false : true;
                },
            },
            remote: {
                url: '<?= admin_url(WHATSBOT_MODULE . '/bots/check_trigger_keyword') ?>',
                type: 'post',
                data: {
                    keyword: function() {
                        return $('input[name="trigger"]').val();
                    }
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
    }, '', {
        trigger: {
            remote: '<?= _l('trigger_keyword_exists') ?>'
        }
    });

    $(document).on('change', '#bot_type', function(event) {
        $('.trigger_input').show();
        $('.alert_default_message').addClass('hide');
        if ($(this).val() == "3" || $(this).val() == "4") {
            $('.trigger_input').hide();
        }
        if ($(this).val() == "4") {
            $('.alert_default_message').removeClass('hide');
        }
    }).trigger("change");

    <?php if (isset($bot)) { ?>
        $('#template_id').trigger('change');
        $('#rel_type').trigger('change');
        $('#bot_type').trigger('change');
        setTimeout(function() {
            $('.header_image').trigger('change');
            $('.header_document').trigger('change');
        }, 200);
    <?php } ?>
</script>