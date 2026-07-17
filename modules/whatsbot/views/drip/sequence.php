<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsbot/drip_campaigns/save'), ['id' => 'drip_sequence_form']); ?>
        <input type="hidden" name="id" value="<?= $sequence['id'] ?? ''; ?>">

        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('sequence_details'); ?></h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <?= render_input('name', _l('sequence_name'), $sequence['name'] ?? ''); ?>
                        <?= render_textarea('description', _l('description'), $sequence['description'] ?? ''); ?>
                        <?= render_select('rel_type', $rel_types, ['key', 'name'], _l('rel_type'), $sequence['rel_type'] ?? 'leads'); ?>
                        <?= render_select('sender_phone', $phone_numbers, ['id', 'display_phone_number', 'verified_name'], _l('sender_phone'), $sequence['sender_phone'] ?? ''); ?>
                        <div class="form-group">
                            <label><?= _l('active'); ?></label>
                            <div class="onoffswitch">
                                <input type="checkbox" value="1" class="onoffswitch-checkbox" id="is_active" name="is_active" <?= (!isset($sequence) || ($sequence['is_active'] ?? 1)) ? 'checked' : ''; ?>>
                                <label class="onoffswitch-label" for="is_active"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('sequence_steps'); ?></h4>
                            <button type="button" class="btn btn-success btn-sm" id="add_step_btn"><i class="fa fa-plus"></i> <?= _l('add_step'); ?></button>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">

                        <div id="steps_container">
                            <?php
                            $steps = $sequence['steps'] ?? [['id' => '', 'delay_value' => 1, 'delay_unit' => 'hours', 'message_type' => 'template', 'template_id' => '', 'filename' => '']];
                            foreach ($steps as $i => $step) {
                                $tpl_name = '';
                                $selected_template = null;
                                if (!empty($step['template_id'])) {
                                    foreach ($templates as $tpl) {
                                        if ((int) $tpl['id'] === (int) $step['template_id']) {
                                            $tpl_name = $tpl['template_name'] . ' (' . $tpl['language'] . ')';
                                            $selected_template = $tpl;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <div class="drip-step panel_s" data-index="<?= $i; ?>" data-step-id="<?= $step['id'] ?? ''; ?>">
                                <div class="panel-body">
                                    <div class="tw-flex tw-justify-between tw-items-center">
                                        <div>
                                            <strong class="drip-step-title"><?= _l('step'); ?> <span class="drip-step-number"><?= $i + 1; ?></span></strong>
                                            <div class="text-muted small drip-step-summary"><?= $tpl_name ?: _l('select_template'); ?></div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-default btn-sm drip-toggle-step"><i class="fa fa-chevron-down"></i></button>
                                            <?php if ($i > 0) { ?>
                                                <button type="button" class="btn btn-danger btn-sm remove-step"><i class="fa fa-trash"></i></button>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="drip-step-body mtop15 <?= $i > 0 ? 'hide' : ''; ?>">
                                        <input type="hidden" name="steps[<?= $i; ?>][id]" value="<?= $step['id'] ?? ''; ?>">
                                        <input type="hidden" name="steps[<?= $i; ?>][existing_filename]" value="<?= $step['filename'] ?? ''; ?>">
                                        <input type="hidden" name="steps[<?= $i; ?>][message_type]" value="<?= $step['message_type'] ?? 'template'; ?>">
                                        <?php if (($step['message_type'] ?? 'template') === 'text') { ?>
                                            <input type="hidden" name="steps[<?= $i; ?>][message_data]" value="<?= html_escape($step['message_data'] ?? ''); ?>">
                                            <div class="alert alert-info"><?= _l('text_message'); ?> (legacy)</div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <label><?= _l('delay'); ?></label>
                                                <input type="number" name="steps[<?= $i; ?>][delay_value]" class="form-control" min="1" value="<?= $step['delay_value'] ?? 1; ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label><?= _l('time_unit'); ?></label>
                                                <select name="steps[<?= $i; ?>][delay_unit]" class="form-control selectpicker">
                                                    <option value="minutes" <?= ($step['delay_unit'] ?? '') == 'minutes' ? 'selected' : ''; ?>><?= _l('minutes'); ?></option>
                                                    <option value="hours" <?= ($step['delay_unit'] ?? 'hours') == 'hours' ? 'selected' : ''; ?>><?= _l('hours'); ?></option>
                                                    <option value="days" <?= ($step['delay_unit'] ?? '') == 'days' ? 'selected' : ''; ?>><?= _l('days'); ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-7">
                                                <label><?= _l('template'); ?></label>
                                                <select name="steps[<?= $i; ?>][template_id]" class="form-control selectpicker drip-template-select" data-live-search="true" data-none-selected-text="<?= _l('select_template'); ?>">
                                                    <option value=""><?= _l('select_template'); ?></option>
                                                    <?php foreach ($templates as $tpl) { ?>
                                                        <option value="<?= $tpl['id']; ?>" <?= ($step['template_id'] ?? '') == $tpl['id'] ? 'selected' : ''; ?>><?= $tpl['template_name'] . ' (' . $tpl['language'] . ')'; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mtop15 drip-template-details">
                                            <div class="col-md-6">
                                                <div class="panel_s">
                                                    <div class="panel-body">
                                                        <h5 class="tw-mt-0 tw-font-semibold"><?= _l('variables'); ?></h5>
                                                        <div class="drip-variables">
                                                            <?php
                                                            if (!empty($selected_template)) {
                                                                $this->load->view('drip/step_variables', [
                                                                    'template' => $selected_template,
                                                                    'step' => $step,
                                                                    'index' => $i,
                                                                    'header_params' => json_decode($step['header_params'] ?? '[]'),
                                                                    'body_params' => json_decode($step['body_params'] ?? '[]'),
                                                                    'footer_params' => json_decode($step['footer_params'] ?? '[]'),
                                                                ]);
                                                            } else {
                                                                echo '<div class="text-muted drip-template-empty-state">' . _l('select_template') . '</div>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="panel_s">
                                                    <div class="panel-body">
                                                        <h5 class="tw-mt-0 tw-font-semibold"><?= _l('preview'); ?></h5>
                                                        <div class="padding" style='background: url("<?= module_dir_url(WHATSBOT_MODULE, 'assets/images/bg.png'); ?>");'>
                                                            <div class="wtc_panel drip-preview-media">
                                                                <?php if (!empty($selected_template) && !empty($step['filename'])) { ?>
                                                                    <?php if (($selected_template['header_data_format'] ?? '') === 'VIDEO') { ?>
                                                                        <video controls src="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" class="wtc_image"></video>
                                                                    <?php } elseif (($selected_template['header_data_format'] ?? '') === 'DOCUMENT') { ?>
                                                                        <a href="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" target="_blank" class="btn btn-default"><?= $step['filename']; ?></a>
                                                                    <?php } else { ?>
                                                                        <img src="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" class="wtc_image">
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="panel_s no-margin">
                                                                <div class="panel-body drip-preview-message">
                                                                    <?php if (!empty($selected_template)) { ?>
                                                                        <strong class="drip-header-data"><?= $selected_template['header_data_text'] ?? ''; ?></strong><br><br>
                                                                        <p class="drip-body-data"><?= $selected_template['body_data'] ?? ''; ?></p><br>
                                                                        <span class="text-muted tw-text-xs drip-footer-data"><?= $selected_template['footer_data'] ?? ''; ?></span>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="drip-preview-buttons">
                                                                <?php
                                                                $buttons = !empty($selected_template['buttons_data']) ? json_decode($selected_template['buttons_data']) : null;
                                                                if (!empty($buttons->buttons)) {
                                                                    foreach ($buttons->buttons as $button) {
                                                                        echo '<button type="button" class="btn btn-default btn-lg btn-block wtc_button">' . html_escape($button->text ?? '') . '</button>';
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <a href="<?= admin_url('whatsbot/drip_campaigns'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
                        <button type="submit" class="btn btn-primary"><?= _l('save'); ?></button>
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
$(function() {
    var stepIndex = <?= count($steps); ?>;
    var templatesHtml = <?= json_encode(array_map(function($t) { return '<option value="'.$t['id'].'">'.$t['template_name'].' ('.$t['language'].')</option>'; }, $templates)); ?>.join('');
    var templatesData = <?= json_encode($templates); ?>;
    var savedStepData = <?= json_encode(array_map(function($s) {
        return [
            'header_params' => json_decode($s['header_params'] ?? '[]', true) ?: [],
            'body_params' => json_decode($s['body_params'] ?? '[]', true) ?: [],
            'footer_params' => json_decode($s['footer_params'] ?? '[]', true) ?: [],
            'filename' => $s['filename'] ?? '',
            'media_url' => !empty($s['filename']) ? base_url(get_upload_path_by_type('drip') . $s['filename']) : '',
        ];
    }, $steps)); ?>;
    var allowedExtensions = <?= json_encode(wb_get_allowed_extension()); ?>;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(char) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
        });
    }

    function decodeEntities(value) {
        return $('<textarea/>').html(value || '').text();
    }

    function getTemplate(templateId) {
        templateId = String(templateId || '');
        for (var i = 0; i < templatesData.length; i++) {
            if (String(templatesData[i].id) === templateId) {
                templatesData[i].header_data_text = decodeEntities(templatesData[i].header_data_text || '');
                templatesData[i].body_data = decodeEntities(templatesData[i].body_data || '');
                templatesData[i].footer_data = decodeEntities(templatesData[i].footer_data || '');
                return templatesData[i];
            }
        }
        return null;
    }

    function getStepSavedData(step) {
        return savedStepData[parseInt(step.data('index'), 10)] || {};
    }

    function getSavedParam(saved, type, number) {
        var params = saved[type + '_params'] || {};
        return params[number] && params[number].value ? params[number].value : '';
    }

    function renderParamInputs(step, template, saved) {
        var index = step.data('index');
        var html = '';
        var headerFormat = template.header_data_format || '';

        if (parseInt(template.header_params_count || 0, 10) > 0) {
            html += '<h5 class="tw-mt-0 tw-font-semibold"><?= _l('header'); ?></h5>';
            if (headerFormat === 'TEXT') {
                for (var h = 1; h <= parseInt(template.header_params_count || 0, 10); h++) {
                    html += '<div class="form-group"><label><?= _l('variable'); ?> ' + h + '</label>' +
                        '<input type="text" name="steps[' + index + '][header_params][' + h + '][value]" value="' + escapeHtml(getSavedParam(saved, 'header', h)) + '" class="form-control drip-param-input drip-header-input mentionable" data-part="header" data-number="' + h + '" autocomplete="off"></div>';
                }
            } else {
                html += '<div class="alert alert-danger"><?= _l('currently_type_not_supported', '{format}'); ?></div>'.replace('{format}', escapeHtml(headerFormat));
            }
            html += '<hr>';
        }

        if (['IMAGE', 'VIDEO', 'DOCUMENT'].indexOf(headerFormat) !== -1) {
            var mediaType = headerFormat.toLowerCase();
            var mediaCfg = allowedExtensions[mediaType] || {};
            var label = headerFormat === 'IMAGE' ? '<?= _l('select_image'); ?>' : (headerFormat === 'VIDEO' ? '<?= _l('select_video'); ?>' : '<?= _l('select_document'); ?>');
            html += '<h5 class="tw-mt-0 tw-font-semibold">' + escapeHtml(headerFormat.charAt(0) + headerFormat.slice(1).toLowerCase()) + '</h5>';
            html += '<input type="hidden" class="drip-max-file-size" value="' + escapeHtml(mediaCfg.size || '') + '">';
            if (saved.filename) {
                html += '<div class="drip-current-media mtop10 mbot10">';
                html += '<input type="hidden" class="drip-existing-media-url" value="' + escapeHtml(saved.media_url || '') + '">';
                if (headerFormat === 'VIDEO') {
                    html += '<video controls src="' + escapeHtml(saved.media_url || '') + '" class="img img-responsive" style="max-width: 220px;"></video>';
                } else if (headerFormat === 'DOCUMENT') {
                    html += '<a href="' + escapeHtml(saved.media_url || '') + '" target="_blank" class="btn btn-default">' + escapeHtml(saved.filename) + '</a>';
                } else {
                    html += '<img src="' + escapeHtml(saved.media_url || '') + '" class="img img-responsive" style="max-width: 220px;">';
                }
                html += '</div>';
            }
            html += '<label class="control-label">' + label + ' <small class="text-muted">(<?= _l('allowed_file_types'); ?>' + escapeHtml(mediaCfg.extension || '') + ')</small></label>';
            html += '<input type="file" name="steps[' + index + '][' + mediaType + ']" accept="' + escapeHtml(mediaCfg.extension || '') + '" class="form-control drip-media-input" data-media-type="' + mediaType + '">';
            html += '<hr>';
        }

        if (parseInt(template.body_params_count || 0, 10) > 0) {
            html += '<h5 class="tw-mt-0 tw-font-semibold"><?= _l('body'); ?></h5>';
            for (var b = 1; b <= parseInt(template.body_params_count || 0, 10); b++) {
                html += '<div class="form-group"><label><?= _l('variable'); ?> ' + b + '</label>' +
                    '<input type="text" name="steps[' + index + '][body_params][' + b + '][value]" value="' + escapeHtml(getSavedParam(saved, 'body', b)) + '" class="form-control drip-param-input drip-body-input mentionable" data-part="body" data-number="' + b + '" autocomplete="off"></div>';
            }
            html += '<hr>';
        }

        if (parseInt(template.footer_params_count || 0, 10) > 0) {
            html += '<h5 class="tw-mt-0 tw-font-semibold"><?= _l('footer'); ?></h5>';
            for (var f = 1; f <= parseInt(template.footer_params_count || 0, 10); f++) {
                html += '<div class="form-group"><label><?= _l('variable'); ?> ' + f + '</label>' +
                    '<input type="text" name="steps[' + index + '][footer_params][' + f + '][value]" value="' + escapeHtml(getSavedParam(saved, 'footer', f)) + '" class="form-control drip-param-input drip-footer-input mentionable" data-part="footer" data-number="' + f + '" autocomplete="off"></div>';
            }
            html += '<hr>';
        }

        if ($.trim(html) === '') {
            html = '<div class="alert alert-info">This template has no variables or media header.</div>';
        }

        step.find('.drip-variables').html(html);
    }

    function renderLocalTemplateConfig(step) {
        var select = step.find('.drip-template-select');
        var template = getTemplate(select.val());
        if (!template) {
            step.find('.drip-step-summary').text('<?= _l('select_template'); ?>');
            step.find('.drip-variables').html('<div class="text-muted drip-template-empty-state"><?= _l('select_template'); ?></div>');
            step.find('.drip-preview-message, .drip-preview-media, .drip-preview-buttons').empty();
            return false;
        }
        var saved = getStepSavedData(step);
        step.find('.drip-template-details').removeClass('hide');
        step.find('.drip-step-summary').text(select.find('option:selected').text());
        renderParamInputs(step, template, saved);
        step.find('.drip-preview-message').html(stepPreviewHtml({
            header_data: template.header_data_text || '',
            body_data: template.body_data || '',
            footer_data: template.footer_data || ''
        })).data('header-original', template.header_data_text || '')
            .data('body-original', template.body_data || '')
            .data('footer-original', template.footer_data || '');
        step.find('.drip-preview-buttons').html('');
        if (template.buttons_data) {
            try {
                var buttons = JSON.parse(template.buttons_data);
                if (buttons && buttons.buttons) {
                    $.each(buttons.buttons, function(i, btn) {
                        step.find('.drip-preview-buttons').append('<button type="button" class="btn btn-default btn-lg btn-block wtc_button">' + escapeHtml(btn.text || '') + '</button>');
                    });
                }
            } catch (e) {}
        }
        step.find('.drip-preview-media').html('');
        if (saved.media_url) {
            if (template.header_data_format === 'VIDEO') {
                step.find('.drip-preview-media').html('<video controls src="' + escapeHtml(saved.media_url) + '" class="wtc_image"></video>');
            } else if (template.header_data_format === 'DOCUMENT') {
                step.find('.drip-preview-media').html('<a href="' + escapeHtml(saved.media_url) + '" target="_blank" class="btn btn-default">' + escapeHtml(saved.filename || '<?= _l('document'); ?>') + '</a>');
            } else {
                step.find('.drip-preview-media').html('<img src="' + escapeHtml(saved.media_url) + '" class="wtc_image">');
            }
        }
        initDripMentions(step);
        step.find('.drip-param-input').trigger('input');
        return true;
    }

    function csrfPayload(extra) {
        var data = extra || {};
        if (typeof csrfData !== 'undefined') {
            data[csrfData.token_name] = csrfData.hash;
        }
        return data;
    }

    function initDripMentions(step) {
        if (typeof Tribute === 'undefined' || typeof merge_fields === 'undefined') {
            return;
        }
        var relType = $('#rel_type').val() === 'contacts' ? 'client' : 'leads';
        var values = [];
        $.each(merge_fields, function(i, group) {
            if (group[relType]) {
                $.each(group[relType], function(j, field) {
                    if (field.name) {
                        values.push({key: field.name, value: field.key});
                    }
                });
            }
            if (group.other) {
                $.each(group.other, function(j, field) {
                    if (field.name) {
                        values.push({key: field.name, value: field.key});
                    }
                });
            }
        });
        var tribute = new Tribute({values: values, selectClass: 'highlights'});
        tribute.attach(step.find('.mentionable'));
    }

    function renumberSteps() {
        $('.drip-step').each(function(i) {
            $(this).find('.drip-step-number').text(i + 1);
            $(this).find('.remove-step').toggle(i > 0);
        });
    }

    function stepPreviewHtml(data) {
        return `
            <strong class="drip-header-data">${data.header_data || ''}</strong><br><br>
            <p class="drip-body-data">${data.body_data || ''}</p><br>
            <span class="text-muted tw-text-xs drip-footer-data">${data.footer_data || ''}</span>
        `;
    }

    function replaceVariable(preview, part, number, value) {
        var selector = part === 'header' ? '.drip-header-data' : (part === 'body' ? '.drip-body-data' : '.drip-footer-data');
        var original = preview.data(part + '-original') || preview.find(selector).text();
        preview.data(part + '-original', original);
        var rendered = original;
        preview.closest('.drip-step').find('.drip-param-input[data-part="' + part + '"]').each(function() {
            var n = $(this).data('number');
            rendered = rendered.replace('{{' + n + '}}', $(this).val() || '{{' + n + '}}');
        });
        preview.find(selector).text(rendered);
    }

    function loadTemplateMap(step) {
        var select = step.find('select.drip-template-select');
        var templateId = select.val();
        if (!templateId) {
            renderLocalTemplateConfig(step);
            return;
        }

        renderLocalTemplateConfig(step);

        $.post(admin_url + 'whatsbot/drip_campaigns/get_template_map', csrfPayload({
            template_id: templateId,
            index: step.data('index'),
            step_id: step.data('step-id')
        }), function(raw) {
            var res;
            try {
                res = typeof raw === 'string' ? JSON.parse(raw) : raw;
            } catch (e) {
                renderLocalTemplateConfig(step);
                if (window.console) {
                    console.error('Invalid drip template map response', raw);
                }
                return;
            }
            step.find('.drip-template-details').removeClass('hide');
            if ($.trim(res.view || '') !== '') {
                step.find('.drip-variables').html(res.view);
            }
            step.find('.drip-preview-message').html(stepPreviewHtml(res));
            step.find('.drip-preview-message')
                .data('header-original', res.header_data || '')
                .data('body-original', res.body_data || '')
                .data('footer-original', res.footer_data || '');
            step.find('.drip-step-summary').text(select.find('option:selected').text());
            step.find('.drip-preview-buttons').html('');
            if (res.button_data && res.button_data.buttons) {
                $.each(res.button_data.buttons, function(i, btn) {
                    step.find('.drip-preview-buttons').append('<button type="button" class="btn btn-default btn-lg btn-block wtc_button">' + btn.text + '</button>');
                });
            }
            step.find('.drip-preview-media').html('');
            var existingMedia = step.find('.drip-existing-media-url').val();
            if (existingMedia) {
                if (res.header_data_format === 'VIDEO') {
                    step.find('.drip-preview-media').html('<video controls src="' + existingMedia + '" class="wtc_image"></video>');
                } else if (res.header_data_format === 'DOCUMENT') {
                    step.find('.drip-preview-media').html('<a href="' + existingMedia + '" target="_blank" class="btn btn-default"><?= _l('document'); ?></a>');
                } else {
                    step.find('.drip-preview-media').html('<img src="' + existingMedia + '" class="wtc_image">');
                }
            }
            step.find('.selectpicker').selectpicker('refresh');
            initDripMentions(step);
            step.find('.drip-param-input').trigger('input');
        }).fail(function(xhr) {
            renderLocalTemplateConfig(step);
            if (window.console) {
                console.error('Drip template map failed', xhr.responseText);
            }
        });
    }

    $('#add_step_btn').on('click', function() {
        var html = `
            <div class="drip-step panel_s" data-index="${stepIndex}" data-step-id="">
                <div class="panel-body">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div>
                            <strong class="drip-step-title"><?= _l('step'); ?> <span class="drip-step-number">${$('.drip-step').length + 1}</span></strong>
                            <div class="text-muted small drip-step-summary"><?= _l('select_template'); ?></div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-default btn-sm drip-toggle-step"><i class="fa fa-chevron-down"></i></button>
                            <button type="button" class="btn btn-danger btn-sm remove-step"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="drip-step-body mtop15">
                        <input type="hidden" name="steps[${stepIndex}][id]" value="">
                        <input type="hidden" name="steps[${stepIndex}][existing_filename]" value="">
                        <input type="hidden" name="steps[${stepIndex}][message_type]" value="template">
                        <div class="row">
                            <div class="col-md-2">
                                <label><?= _l('delay'); ?></label>
                                <input type="number" name="steps[${stepIndex}][delay_value]" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label><?= _l('time_unit'); ?></label>
                                <select name="steps[${stepIndex}][delay_unit]" class="form-control selectpicker">
                                    <option value="minutes"><?= _l('minutes'); ?></option>
                                    <option value="hours" selected><?= _l('hours'); ?></option>
                                    <option value="days"><?= _l('days'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label><?= _l('template'); ?></label>
                                <select name="steps[${stepIndex}][template_id]" class="form-control selectpicker drip-template-select" data-live-search="true">
                                    <option value=""><?= _l('select_template'); ?></option>
                                    ${templatesHtml}
                                </select>
                            </div>
                        </div>
                        <div class="row mtop15 drip-template-details hide">
                            <div class="col-md-6"><div class="panel_s"><div class="panel-body"><h5 class="tw-mt-0 tw-font-semibold"><?= _l('variables'); ?></h5><div class="drip-variables"></div></div></div></div>
                            <div class="col-md-6"><div class="panel_s"><div class="panel-body"><h5 class="tw-mt-0 tw-font-semibold"><?= _l('preview'); ?></h5><div class="padding" style='background: url("<?= module_dir_url(WHATSBOT_MODULE, 'assets/images/bg.png'); ?>");'><div class="wtc_panel drip-preview-media"></div><div class="panel_s no-margin"><div class="panel-body drip-preview-message"></div></div><div class="drip-preview-buttons"></div></div></div></div></div>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#steps_container').append(html);
        $('.selectpicker').selectpicker('refresh');
        stepIndex++;
        renumberSteps();
    });

    $(document).on('click', '.drip-toggle-step', function() {
        $(this).closest('.drip-step').find('.drip-step-body').toggleClass('hide');
    });

    $(document).on('click', '.remove-step', function() {
        $(this).closest('.drip-step').remove();
        renumberSteps();
    });

    $(document).on('change', '.drip-template-select', function() {
        loadTemplateMap($(this).closest('.drip-step'));
    });

    $(document).on('input change', '.drip-param-input', function() {
        var preview = $(this).closest('.drip-step').find('.drip-preview-message');
        replaceVariable(preview, $(this).data('part'), $(this).data('number'), $(this).val());
    });

    $(document).on('change', '.drip-media-input', function(e) {
        var file = e.target.files[0];
        var step = $(this).closest('.drip-step');
        if (!file) {
            return;
        }
        var maxSize = parseFloat(step.find('.drip-max-file-size').val() || 0) * 1024 * 1024;
        if (maxSize && file.size > maxSize) {
            alert_float('danger', '<?= _l('maximum_file_size_should_be'); ?> ' + step.find('.drip-max-file-size').val() + ' MB');
            $(this).val('');
            return;
        }
        var url = URL.createObjectURL(file);
        var type = $(this).data('media-type');
        if (type === 'video') {
            step.find('.drip-preview-media').html('<video controls src="' + url + '" class="wtc_image"></video>');
        } else if (type === 'document') {
            step.find('.drip-preview-media').html('<a class="btn btn-default" target="_blank" href="' + url + '">' + file.name + '</a>');
        } else {
            step.find('.drip-preview-media').html('<img src="' + url + '" class="wtc_image">');
        }
    });

    appValidateForm($('#drip_sequence_form'), { name: 'required', rel_type: 'required' }, function(form) {
        var formData = new FormData(form);
        $.ajax({
            url: '<?= admin_url("whatsbot/drip_campaigns/save"); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status) {
                    alert_float('success', res.message);
                    setTimeout(function() { window.location.href = admin_url + 'whatsbot/drip_campaigns'; }, 1000);
                } else {
                    alert_float('danger', res.message || 'Error');
                }
            }
        });
        return false;
    });

    $('.drip-step').each(function() {
        var step = $(this);
        initDripMentions($(this));
        $(this).find('.drip-param-input').trigger('input');
        setTimeout(function() {
            var select = step.find('.drip-template-select');
            var selectedText = select.find('option:selected').text();
            if (select.val()) {
                step.find('.drip-step-summary').text(selectedText);
                step.find('.drip-template-details').removeClass('hide');
                if (!$.trim(step.find('.drip-variables').html())) {
                    step.find('.drip-variables').html('<div class="text-muted">Loading template information...</div>');
                }
                loadTemplateMap(step);
            }
        }, 250);
    });
});
</script>
