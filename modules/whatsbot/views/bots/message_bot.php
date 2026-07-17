<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsbot/bots/saveBots'), ['id' => 'whatsapp_bot_form'], ['id' => $bot['id'] ?? '']); ?>
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo (isset($bot)) ? _l('edit') . ' #' . $bot['name'] : _l('new_message_bot'); ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <?php echo render_input('name', 'bot_name', $bot['name'] ?? '', '', ['placeholder' => _l('enter_name'), 'autocomplete' => 'off'], [], 'col-md-12'); ?>
                            <?php echo render_select('rel_type', wb_get_messagebot_rel_type(), ['key', 'name'], 'relation_type', $bot['rel_type'] ?? '', [], [], 'col-md-12'); ?>
                            <?php echo render_textarea('reply_text', 'reply_text', $bot['reply_text'] ?? '', ['rows' => '10', 'maxlength' => '1024'], [], 'col-md-12', 'mentionable'); ?>
                            <?php echo render_select('reply_type', wb_get_reply_type(), ['id', 'label'], 'reply_type', $bot['reply_type'] ?? '', [], [], 'col-md-12', '', '', false); ?>
                            <div class="col-md-12 alert_default_message hide">
                                <div class="alert alert-warning">
                                    <?= _l('default_message_note'); ?>
                                </div>
                            </div>
                            <div class="form-group col-md-12 trigger_input">
                                <label for="trigger" class="control-label"><?php echo _l('trigger_keyword'); ?></label>
                                <input type="text" class="tagsinput" id="trigger" name="trigger" value="<?= $bot['trigger'] ?? ''; ?>" data-role="tagsinput">
                            </div>
                            <?php echo render_input('bot_header', 'header', $bot['bot_header'] ?? '', '', ['placeholder' => _l('enter_header')], [], 'col-md-12'); ?>
                            <?php echo render_input('bot_footer', 'footer_bot', $bot['bot_footer'] ?? '', '', ['placeholder' => _l('enter_footer'), 'maxlength' => '60'], [], 'col-md-12'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col col-md-12">
                                <?php
                                $bot_options = [
                                    ['key' => '1', 'value' => _l('option_1_personal_assitant')],
                                    ['key' => '2', 'value' => _l('bot_with_reply_buttons')],
                                    ['key' => '3', 'value' => _l('option_2_bot_with_link')],
                                    ['key' => '4', 'value' => _l('option_3_file')],
                                    ['key' => '5', 'value' => _l('option_5_bot_with_options')],
                                    ['key' => '6', 'value' => _l('option_6_bot_catalog')],
                                    ['key' => '7', 'value' => _l('option_7_location_request')],
                                    ['key' => '8', 'value' => _l('option_8_contact_card')],
                                ];
                                echo render_select('option', $bot_options, ['key', 'value'], 'choose_options', $bot['option'] ?? '');
                                ?>
                            </div>
                        </div>
                        <hr class="hr-panel-separator" />
                        <div class="hidden option" id="option-1">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_1_personal_assitant'); ?></h4>
                            <div class="row">
                                <?php echo render_select('personal_assistants', get_valid_assistants(), ['id', 'name'], 'personal_assistant', $bot['personal_assistants'] ?? '', [], [], 'col-md-12'); ?>
                            </div>
                            <hr class="hr-panel-separator" />
                        </div>
                        <div class="hidden option" id="option-2">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('bot_with_reply_buttons'); ?></h4>
                            <div class="row">
                                <?php echo render_input('button1', 'button1', $bot['button1'] ?? '', '', ['placeholder' => _l('enter_button1')], [], 'col-md-6'); ?>
                                <?php echo render_input('button1_id', 'button1_id', $bot['button1_id'] ?? '', '', ['placeholder' => _l('enter_button1_id'), 'maxlength' => '256'], [], 'col-md-6'); ?>
                            </div>
                            <div class="row">
                                <?php echo render_input('button2', 'button2', $bot['button2'] ?? '', '', ['placeholder' => _l('enter_button2')], [], 'col-md-6'); ?>
                                <?php echo render_input('button2_id', 'button2_id', $bot['button2_id'] ?? '', '', ['placeholder' => _l('enter_button2_id'), 'maxlength' => '256'], [], 'col-md-6'); ?>
                            </div>
                            <div class="row">
                                <?php echo render_input('button3', 'button3', $bot['button3'] ?? '', '', ['placeholder' => _l('enter_button3')], [], 'col-md-6'); ?>
                                <?php echo render_input('button3_id', 'button3_id', $bot['button3_id'] ?? '', '', ['placeholder' => _l('enter_button3_id'), 'maxlength' => '256'], [], 'col-md-6'); ?>
                            </div>
                            <hr class="hr-panel-separator" />
                        </div>
                        <div class="hidden option" id="option-3">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_2_bot_with_link'); ?></h4>
                            <div class="row ">
                                <?php echo render_input('button_name', 'button_name', $bot['button_name'] ?? '', '', ['placeholder' => _l('enter_button_name')], [], 'col-md-12'); ?>
                                <?php echo render_input('button_url', 'button_link', $bot['button_url'] ?? '', '', ['placeholder' => _l('enter_button_url')], [], 'col-md-12'); ?>
                            </div>
                            <hr class="hr-panel-separator" />
                        </div>
                        <div class="hidden option" id="option-4">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_3_file'); ?></h4>
                            <?php $allowd_extension = wb_get_allowed_extension(); ?>
                            <div class="row ">
                                <div class="col-md-12 <?= (isset($bot) && !empty($bot['filename'])) ? 'hide' : ''; ?>">
                                    <?php $file_types = [['key' => 'image', 'value' => _l('image')], ['key' => 'document', 'value' => _l('document')], ['key' => 'video', 'value' => _l('video')]]; ?>
                                    <?= render_select('file_type', $file_types, ['key', 'value'], 'choose_file_type', 'image'); ?>
                                </div>
                                <div class="<?= (isset($bot) && empty($bot['filename'])) ? 'hide' : ''; ?>">
                                    <?php if (isset($bot)) : ?>
                                        <?php $imgExt = array_map('trim', explode(',', $allowd_extension['image']['extension']));
                                        $docExt = array_map('trim', explode(',', $allowd_extension['document']['extension']));
                                        $videoExt = array_map('trim', explode(',', $allowd_extension['video']['extension'])); ?>
                                        <?php if (in_array('.' . get_file_extension($bot['filename']), $imgExt)) { ?>
                                            <div class="col-md-9">
                                                <img src="<?= base_url(get_upload_path_by_type('bot_files') . $bot['filename']); ?>" class="img img-responsive" height="70%" width="70%">
                                            </div>
                                        <?php } elseif (in_array('.' . get_file_extension($bot['filename']), $docExt)) { ?>
                                            <div class="col-md-9">
                                                <div class="pull-left">
                                                    <i class="attachment-icon-preview fa-regular fa-file"></i>
                                                </div>
                                                <a href="<?= base_url(get_upload_path_by_type('bot_files') . $bot['filename']); ?>" target="_blank">
                                                    <p class="text-primary"><?= $bot['filename']; ?></p>
                                                </a>
                                            </div>
                                        <?php } elseif (in_array('.' . get_file_extension($bot['filename']), $videoExt)) { ?>
                                            <div class="col-md-9">
                                                <video src="<?= base_url(get_upload_path_by_type('bot_files') . $bot['filename']); ?>" height=300 width="300" controls></video>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3 text-right">
                                            <a href="<?= admin_url(WHATSBOT_MODULE . '/bots/delete_bot_files/' . $bot['id']); ?>"><i class="fa fa-remove text-danger"></i></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="<?= (isset($bot) && !empty($bot['filename'])) ? 'hide' : ''; ?> col-md-12">
                                    <input type="hidden" id="maxFileSize" value="">
                                    <label id="bot_file_label" for="bot_file" class="control-label"></label>
                                    <input type="file" name="bot_file" id="bot_file" accept="" class="form-control">
                                </div>
                            </div>
                        </div>
                        <?php
                        $formatted = [];
                        if (!empty($bot['options'])) {
                            $botop = json_decode($bot['options'], true);
                            if (isset($botop['sections']) && is_array($botop['sections'])) {
                                foreach ($botop['sections'] as $value) {
                                    $formatted[] = $value;
                                }
                            }
                        }
                        ?>
                        <div class="hidden option" id="option-5">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_5_bot_with_options'); ?></h4>
                            <div id="sectionsContainer">
                                <?php foreach ($sections["sections"] as $index => $section) { ?>
                                    <div class="section-container" data-section-index="<?= $index ?>">
                                        <div class="row">
                                            <?php echo render_input('sections[' . $index . '][section]', 'section', $section['section'] ?? '', '', [], [], 'col-md-10'); ?>
                                            <div class="col-md-2">
                                                <?php if ($index == 0) { ?>
                                                    <button type="button" class="btn btn-sm btn-success btn-add-section mtop25">+</button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-sm btn-danger btn-remove-section mtop25">-</button>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="text-container">
                                            <?php foreach ($section["text"] as $key => $text) { ?>
                                                <div class="row form-group">
                                                    <div class="col-md-5">
                                                        <label><?= _l('text') ?></label>
                                                        <input type="text" name="sections[<?= $index ?>][text][]" class="form-control text-field" value="<?= $text ?>">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label><?= _l('sub_text') ?></label>
                                                        <input type="text" name="sections[<?= $index ?>][subtext][]" class="form-control subtext-field" value="<?= $section['subtext'][$key] ?>">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <?php if ($key == 0) { ?>
                                                            <button type="button" class="btn btn-sm btn-success btn-add-text mtop25">+</button>
                                                        <?php } else { ?>
                                                            <button type="button" class="btn btn-sm btn-danger btn-remove-text mtop25">-</button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <hr class="hr-panel-separator">
                                <?php } ?>
                            </div>

                            <div class="form-group">
                                <label><?= _l('submit_button_label') ?></label>
                                <input type="text" name="action" class="form-control" value="<?= $sections['action'] ?>">
                            </div>
                        </div>
                        <div class="hidden option" id="option-7">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_7_location_request'); ?></h4>
                            <p class="text-muted"><?php echo _l('option_7_location_request_note'); ?></p>
                        </div>
                        <div class="hidden option" id="option-8">
                            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('option_8_contact_card'); ?></h4>
                            <div class="row">
                                <?php echo render_input('contact_first_name', _l('contact_first_name'), $bot['contact_first_name'] ?? '', '', ['placeholder' => _l('enter_contact_first_name')], [], 'col-md-6'); ?>
                                <?php echo render_input('contact_last_name', _l('contact_last_name'), $bot['contact_last_name'] ?? '', '', ['placeholder' => _l('enter_contact_last_name')], [], 'col-md-6'); ?>
                                <?php echo render_input('contact_phone', _l('contact_phone'), $bot['contact_phone'] ?? '', '', ['placeholder' => _l('enter_contact_phone')], [], 'col-md-6'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-right tw-space-x-1">
                        <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
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
        appValidateForm($('#whatsapp_bot_form'), {
            name: "required",
            reply_text: "required",
            reply_type: "required",
            rel_type: "required",
            button1: "alphanumericMaxlength",
            button2: "alphanumericMaxlength",
            button3: "alphanumericMaxlength",
            button_name: "alphanumericMaxlength",
            button_url: "url",
            trigger: {
                required: {
                    depends: function() {
                        return $('#trigger_input').hide() ? false : true;
                    },
                },
                remote: {
                    url: '<?= admin_url(WHATSBOT_MODULE.'/bots/check_trigger_keyword') ?>',
                    type: 'post',
                    data: {
                        keyword: function() {
                            return $('input[name="trigger"]').val();
                        }
                    }
                }
            },
        }, '', {
            trigger:{
                remote:'<?= _l('trigger_keyword_exists') ?>'
            }
        });
        $.validator.addMethod("alphanumericMaxlength", function(value, element) {
            // Check if value is alphanumeric with spaces and does not exceed 20 characters
            return this.optional(element) || /^[A-Za-z0-9\s]{1,20}$/.test(value);
        }, "Please enter only letters, numbers, or spaces and maximum 20 characters allowed.");
        $('#file_type').trigger('change');
    });

    $(document).on('change', '#rel_type', function(event) {
        if ($(this).val() == "leads") {
            $('[for="reply_text"]').html(`<?php echo _l('reply_text', _l('leads')); ?>`);
        } else {
            $('[for="reply_text"]').html(`<?php echo _l('reply_text', _l('contacts')); ?>`);
        }
    });

    $(document).on('change', '#reply_type', function(event) {
        $('.trigger_input').show();
        $('.alert_default_message').addClass('hide');
        if ($(this).val() == "3" || $(this).val() == "4") {
            $('.trigger_input').hide();
        }
        if ($(this).val() == "4") {
            $('.alert_default_message').removeClass('hide');
        }
    }).trigger("change");

    let sectionIndex = <?= count($sections["sections"]) ?>;

    // Add a new section
    $(".btn-add-section").click(function() {
        let sectionHTML = `
                <div class="section-container" data-section-index="${sectionIndex}">
                    <div class="row form-group">
                        <div class="col-md-10">
                            <label><?= _l('section') ?></label>
                            <input type="text" name="sections[${sectionIndex}][section]" class="form-control section-name">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-section mtop25">-</button>
                        </div>
                    </div>

                    <div class="text-container">
                        <div class="row form-group">
                            <div class="col-md-5">
                                <label><?= _l('text') ?></label>
                                <input type="text" name="sections[${sectionIndex}][text][]" class="form-control text-field">
                            </div>
                            <div class="col-md-5">
                                <label><?= _l('sub_text') ?></label>
                                <input type="text" name="sections[${sectionIndex}][subtext][]" class="form-control subtext-field">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-success btn-add-text mtop25">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="hr-panel-separator">
            `;
        $("#sectionsContainer").append(sectionHTML);
        sectionIndex++;
    });

    // Remove section (except first)
    $(document).on("click", ".btn-remove-section", function() {
        $(this).closest(".section-container").next("hr").remove();
        $(this).closest(".section-container").remove();
    });

    // Add text & subtext inside a section
    $(document).on("click", ".btn-add-text", function() {
        let sectionIndex = $(this).closest(".section-container").data("section-index");
        let textHTML = `
                <div class="row form-group">
                    <div class="col-md-5">
                        <label><?= _l('text') ?></label>
                        <input type="text" name="sections[${sectionIndex}][text][]" class="form-control text-field">
                    </div>
                    <div class="col-md-5">
                        <label><?= _l('sub_text') ?></label>
                        <input type="text" name="sections[${sectionIndex}][subtext][]" class="form-control subtext-field">
                    </div>
                    <div class="col-md-2">
                       <button type="button" class="btn btn-sm btn-danger btn-remove-text mtop25">-</button>
                    </div>
                </div>
            `;
        $(this).closest(".text-container").append(textHTML);
    });

    // Remove text & subtext fields
    $(document).on("click", ".btn-remove-text", function() {
        $(this).closest(".form-group").remove();
    });

    // Remove text & subtext fields
    $(document).on("click", ".btn-remove-text", function() {
        $(this).closest(".form-group").remove();
    });

    $(document).on('change', '#file_type', function() {
        var value = $(this).val();
        const imageMaxSize = "<?= $allowd_extension['image']['size']; ?>";
        const documentMaxSize = "<?= $allowd_extension['document']['size']; ?>";
        const videoMaxSize = "<?= $allowd_extension['video']['size']; ?>";
        const imageAllowedExt = "<?= $allowd_extension['image']['extension']; ?>";
        const documentAllowedExt = "<?= $allowd_extension['document']['extension']; ?>";
        const videoAllowedExt = "<?= $allowd_extension['video']['extension']; ?>";
        if (value == 'image') {
            $('#maxFileSize').val(imageMaxSize);
            $('#bot_file_label').html("<?= _l('image'); ?>" + '<small class="text-muted">' + "( <?= _l('max_size') . $allowd_extension['image']['size'] . ' MB), ( ' .  _l('allowed_file_types') . $allowd_extension['image']['extension']; ?> )" + '</small>');
            $('#bot_file').attr('accept', imageAllowedExt);
            $('.file_tootltip').data('title', '<?= _l('maximum_file_size_should_be'); ?>' + imageMaxSize + ' MB');
        } else if (value == 'document') {
            $('#maxFileSize').val(documentMaxSize);
            $('#bot_file_label').html("<?= _l('document'); ?>" + '<small class="text-muted">' + "( <?= _l('max_size') . $allowd_extension['document']['size'] . ' MB), ( ' .  _l('allowed_file_types') . $allowd_extension['document']['extension']; ?> )" + '</small>');
            $('#bot_file').attr('accept', documentAllowedExt);
            $('.file_tootltip').data('title', '<?= _l('maximum_file_size_should_be'); ?>' + documentMaxSize + ' MB');
        } else if (value == 'video') {
            $('#maxFileSize').val(videoMaxSize);
            $('#bot_file_label').html("<?= _l('video'); ?>" + '<small class="text-muted">' + "( <?= _l('max_size') . $allowd_extension['video']['size'] . ' MB), ( ' .  _l('allowed_file_types') . $allowd_extension['video']['extension']; ?> )" + '</small>');
            $('#bot_file').attr('accept', videoAllowedExt);
            $('.file_tootltip').data('title', '<?= _l('maximum_file_size_should_be'); ?>' + videoMaxSize + ' MB');
        }
    });

    function showOption(value) {
        $('.option').addClass('hidden');
        $('#option-' + value).removeClass('hidden');
    }

    // Initialize on page load
    showOption($('#option').val());

    // Handle change event
    $(document).on('change', '#option', function() {
        showOption($(this).val());
    });

    <?php if (isset($bot)) { ?>
        $('#rel_type').trigger('change');
        $('#reply_type').trigger('change');
    <?php } ?>
</script>
