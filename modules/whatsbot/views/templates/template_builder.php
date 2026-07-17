<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
$existingButtons = [];
if (!empty($tpl->buttons_data)) {
    $decodedButtons = json_decode($tpl->buttons_data, true);
    if (!empty($decodedButtons['buttons']) && is_array($decodedButtons['buttons'])) {
        $existingButtons = $decodedButtons['buttons'];
    }
}

$bodyVariableCount = 0;
if (!empty($tpl->body_data) && preg_match_all('/{{\s*(\d+)\s*}}/', $tpl->body_data, $bodyMatches)) {
    $bodyVariableCount = count(array_unique($bodyMatches[1]));
}

$headerHasVariable = !empty($tpl->header_data_text) && preg_match('/{{\s*1\s*}}/', $tpl->header_data_text);
?>
<?php init_head(); ?>
<style>
    [v-cloak] {
        display: none;
    }
    .button-row {
        align-items: center;
    }

    .button-row .btn-type-select {
        min-height: 42px;
    }

    .button-row .remove-btn {
        min-width: 42px;
        padding-left: 0;
        padding-right: 0;
        font-size: 18px;
        line-height: 1;
    }

    #add_button_btn:hover {
        text-decoration: none;
    }

    .body-variable-row {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px;
    }

    .body-variable-token {
        min-width: 72px;
        background: #f8fafc;
        border-color: #dbe3ea;
    }

    .wb-empty-buttons-state {
        border: 1px dashed #e5e7eb;
        border-radius: 6px;
        background: #fff;
    }

    .wb-button-card {
        background: #fff;
        border: 1px solid #dbe3ea;
        border-radius: 10px;
        padding: 10px;
    }

    .wb-button-main {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }

    .wb-button-type {
        flex: 0 0 122px;
        max-width: 122px;
    }

    .wb-button-field {
        flex: 1 1 0;
        min-width: 0;
    }

    .wb-button-field-wide {
        flex: 1 1 100%;
    }

    .wb-button-actions {
        flex: 0 0 42px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding-top: 0;
    }

    .wb-button-type .bootstrap-select,
    .wb-button-type .dropdown.bootstrap-select {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .wb-button-type .bootstrap-select > .dropdown-toggle {
        min-height: 42px;
        padding-top: 9px;
        padding-bottom: 9px;
    }

    .wb-button-type .bootstrap-select .filter-option-inner-inner {
        line-height: 22px;
    }

    .wb-preview-button-row {
        width: 100%;
        box-sizing: border-box;
        border-top: 1px solid #e5e7eb;
    }

    .wb-preview-button-row:last-child {
        border-bottom: 1px solid #e5e7eb;
    }

    .wb-doc-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 74px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #111827;
        background: #fff;
        text-decoration: none;
    }

    .wb-doc-preview:hover {
        text-decoration: none;
        color: #111827;
    }

    .wb-body-format-toolbar {
        padding: 10px 12px;
        background: #f4f8ff;
        border: 1px solid #cdddf5;
        border-radius: 10px;
    }

    .wb-format-btn {
        min-width: 38px;
        min-height: 34px;
        padding-left: 0;
        padding-right: 0;
        font-size: 14px;
        line-height: 1;
    }

    .wb-strike-icon {
        text-decoration: line-through;
    }

    .wb-code-icon {
        font-family: Consolas, "Liberation Mono", monospace;
        font-size: 12px;
    }

    .wb-preview-body {
        white-space: pre-wrap;
        word-break: break-word;
    }

    .wb-preview-code {
        margin: 6px 0 0;
        padding: 8px 10px;
        background: #f3f4f6;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: Consolas, "Liberation Mono", monospace;
        font-size: 12px;
        color: #111827;
    }
</style>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsbot/templates/save'), ['id' => 'template_builder_form', 'novalidate' => 'novalidate']); ?>
        <input type="hidden" name="id" value="<?= isset($tpl) ? $tpl->id : ''; ?>">
        <input type="hidden" name="template_id" value="<?= isset($tpl) ? $tpl->template_id : ''; ?>">

        <div class="row" id="template_builder_app" v-cloak>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-heading"><?= _l('template_basics'); ?></div>
                    <div class="panel-body">
                        <?= render_input('template_name', _l('template_name') . ' <span class="text-danger">*</span>', isset($tpl) ? $tpl->template_name : '', 'text', ['placeholder' => _l('enter_template_name'), 'pattern' => '[a-z0-9_]+', 'maxlength' => '512', 'v-model' => 'templateName', '@input' => "clearValidationError('template_name')", 'required' => 'required'], [], 'mbot5'); ?>
                        <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.template_name">{{ validationErrors.template_name }}</span>
                        <small class="text-muted mbot15 tw-block">
                            <i class="fa-regular fa-circle-question tw-mt-0.5 tw-mr-1"></i><?= _l('template_name_note'); ?>
                        </small>
                        <?= render_select('category', $categories, ['key', 'value'], _l('category') . ' <span class="text-danger">*</span>', isset($tpl) ? $tpl->category : 'MARKETING', ['required' => 'required']); ?>
                        <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.category">{{ validationErrors.category }}</span>
                        <?= render_select('language', $languages, ['key', 'value'], _l('language') . ' <span class="text-danger">*</span>', isset($tpl) ? $tpl->language : 'en', ['required' => 'required']); ?>
                        <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.language">{{ validationErrors.language }}</span>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-heading"><?= _l('header_type'); ?></div>
                    <div class="panel-body">
                        <?= render_select('header_data_format', $header_types, ['key', 'value'], _l('header_type'), isset($tpl) ? $tpl->header_data_format : 'NONE', ['id' => 'header_data_format'], [], '', '', false); ?>

                        <div id="header_text_section" v-show="headerFormat === 'TEXT'">
                            <div class="form-group" :class="{ 'has-error': validationErrors.header_data_text }">
                                <label class="control-label"><?= _l('header_text'); ?></label>
                                <div class="input-group">
                                <input type="text" name="header_data_text" id="header_data_text" ref="headerText" class="form-control" :maxlength="headerMaxLength" v-model="headerDataText" placeholder="<?= _l('enter_header'); ?>" @input="onHeaderTextInput" @keyup="rememberCaret('header', $event)" @mouseup="rememberCaret('header', $event)" @click="rememberCaret('header', $event)" @select="rememberCaret('header', $event)">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" id="add_header_variable" @click="addHeaderVariable"><?php echo _l('add_variable'); ?></button>
                                    </span>
                                </div>
                            </div>
                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-mt-1">
                                <span class="help-block text-danger tw-mb-0" v-if="validationErrors.header_data_text">{{ validationErrors.header_data_text }}</span>
                                <small :class="headerCharacterCount > headerMaxLength ? 'text-danger' : 'text-muted'">{{ headerCharacterCount }}/{{ headerMaxLength }} Characters</small>
                            </div>
                            <div id="header_variable_wrap" v-show="headerHasVariable" class="tw-space-y-2 mtop15">
                                <div class="body-variable-row tw-flex tw-items-center tw-gap-3" :class="{ 'has-error': validationErrors.header_variable_1 }">
                                    <button type="button" class="btn btn-default btn-sm tw-shrink-0 body-variable-token" :title="'<?= _l('variable'); ?> {{1}}'" v-text="'{{1}}'"></button>
                                    <div class="tw-flex-1">
                                        <input type="text" name="header_variables[1]" id="header_variable_1" class="form-control body-variable-input" v-model="headerVariable1" placeholder="<?= _l('variable'); ?> 1" @input="clearValidationError('header_variable_1')">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-body-variable tw-shrink-0" @click="removeHeaderVariable" aria-label="<?= _l('remove'); ?>">&times;</button>
                                </div>
                                <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.header_variable_1">{{ validationErrors.header_variable_1 }}</span>
                            </div>
                        </div>

                        <div id="header_media_section" v-show="isMediaHeader">
                            <div class="form-group" id="header_media_file_wrap" :class="{ 'has-error': validationErrors.header_media_file }">
                                <label class="control-label" id="header_media_file_label">Media file</label>
                                <input type="file" name="header_media_file" id="header_media_file" class="form-control" :accept="mediaAccept" @change="onMediaFileChange">
                                <small class="text-muted mtop5 tw-block" id="header_media_file_note">Choose file</small>
                                <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.header_media_file">{{ validationErrors.header_media_file }}</span>
                            </div>

                            <div class="tw-border-t tw-border-gray-100 tw-my-3"></div>

                            <div class="form-group" id="header_media_url_wrap" :class="{ 'has-error': validationErrors.header_media_url }">
                                <label class="control-label" id="header_media_label">Or provide URL directly</label>
                                <input type="text" name="header_media_url" id="header_media_url" class="form-control" v-model="headerMediaUrl" placeholder="https://example.com/media-file" @input="onHeaderMediaUrlInput">
                                <small class="text-muted mtop5 tw-block" id="header_media_note"><?= _l('image'); ?>, <?= _l('video'); ?>, <?= _l('document'); ?> URL used for template preview and validation.</small>
                                <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.header_media_url">{{ validationErrors.header_media_url }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-heading"><?= _l('template_content'); ?></div>
                    <div class="panel-body">
                        <div class="form-group" :class="{ 'has-error': validationErrors.body_data }">
                                <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-mb-2">
                                    <label class="control-label tw-mb-0"><?= _l('message_body'); ?> <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-sm btn-success" id="add_body_variable" @click="addBodyVariable"><?= _l('add_variable'); ?></button>
                                </div>
                                <div class="wb-body-format-toolbar tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mb-2">
                                    <button type="button" class="btn btn-default btn-xs wb-format-btn" @click.prevent="formatBodyText('bold')" title="Bold">
                                        <strong>B</strong>
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs wb-format-btn" @click.prevent="formatBodyText('italic')" title="Italic">
                                        <em>I</em>
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs wb-format-btn" @click.prevent="formatBodyText('strike')" title="Strikethrough">
                                        <span class="wb-strike-icon">S</span>
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs wb-format-btn" @click.prevent="formatBodyText('code')" title="Code">
                                        <span class="wb-code-icon">&lt;/&gt;</span>
                                    </button>
                                    <span class="text-muted tw-text-xs">Select text and click a style button to format it.</span>
                                </div>
                                <textarea name="body_data" id="body_data" ref="bodyText" class="form-control" rows="7" :maxlength="bodyMaxLength" placeholder="<?= _l('body_text_placeholder'); ?>" v-model="bodyText" @input="onBodyTextInput" @keyup="rememberCaret('body', $event)" @mouseup="rememberCaret('body', $event)" @click="rememberCaret('body', $event)" @select="rememberCaret('body', $event)"></textarea>
                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-mt-1">
                                <span class="help-block text-danger tw-mb-0" v-if="validationErrors.body_data">{{ validationErrors.body_data }}</span>
                                <small :class="bodyCharacterCount > bodyMaxLength ? 'text-danger' : 'text-muted'">{{ bodyCharacterCount }}/{{ bodyMaxLength }} Characters</small>
                            </div>
                            <div class="tw-flex tw-items-center tw-justify-between tw-mt-2">
                                <small class="text-muted"><?= _l('body_variable_note'); ?></small>
                            </div>
                        </div>

                        <div id="body_variables_container" class="tw-space-y-2 mtop15">
                            <div class="body-variable-row tw-flex tw-items-center tw-gap-3 mtop10" v-for="variable in bodyVariables" :key="variable.index" :data-var-index="variable.index">
                                <button type="button" class="btn btn-default btn-sm tw-shrink-0 body-variable-token" :title="'<?= _l('variable'); ?> {{' + variable.index + '}}'" v-text="'{{' + variable.index + '}}'"></button>
                                <div class="tw-flex-1">
                                    <input type="text" :name="'body_variables[' + variable.index + ']'" class="form-control body-variable-input" :data-var-index="variable.index" v-model="variable.value" :placeholder="'<?= _l('variable'); ?> ' + variable.index" @input="clearValidationError('body_variables')">
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-body-variable tw-shrink-0" @click="removeBodyVariable(variable.index)" aria-label="<?= _l('remove'); ?>">&times;</button>
                            </div>
                        </div>
                            <span class="help-block text-danger tw-block mtop5" v-if="validationErrors.body_variables">{{ validationErrors.body_variables }}</span>

                        <div class="form-group mtop15" :class="{ 'has-error': validationErrors.footer_data }">
                            <label class="control-label"><?= _l('footer_text'); ?></label>
                            <input type="text" name="footer_data" id="footer_data" class="form-control" :maxlength="footerMaxLength" v-model="footerData" placeholder="<?= _l('footer_text_placeholder'); ?>" @input="onFooterInput">
                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-mt-1">
                                <span class="help-block text-danger tw-mb-0" v-if="validationErrors.footer_data">{{ validationErrors.footer_data }}</span>
                                <small :class="footerCharacterCount > footerMaxLength ? 'text-danger' : 'text-muted'">{{ footerCharacterCount }}/{{ footerMaxLength }} Characters</small>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                                <label class="control-label tw-mb-0"><?= _l('buttons'); ?></label>
                                <a href="#" id="add_button_btn" class="tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-medium tw-text-success" @click.prevent="addButton">
                                    <span>+</span>
                                    <span>Add Button</span>
                                </a>
                            </div>
                            <div id="buttons_container" class="tw-space-y-3">
                                <div v-if="!hasButtons" class="wb-empty-buttons-state tw-text-center tw-text-xs tw-text-gray-400 tw-py-3">
                                    No buttons added yet. Click "Add Button" to create interactive buttons.
                                </div>
                                <div class="button-row wb-button-card" v-for="(button, index) in buttons" :key="button.uid" :data-button-index="index">
                                    <div class="wb-button-main">
                                        <div class="wb-button-type">
                                            <select :name="'buttons[' + index + '][type]'" class="selectpicker btn-type-select" data-width="100%" v-model="button.type" @change="onButtonTypeChange(button)" @changed.bs.select="onButtonTypeChange(button)">
                                            <option value="QUICK_REPLY"><?= _l('quick_reply'); ?></option>
                                            <option value="URL"><?= _l('url'); ?></option>
                                            <option value="PHONE_NUMBER"><?= _l('phone_number'); ?></option>
                                            </select>
                                        </div>
                                        <template v-if="button.type === 'QUICK_REPLY'">
                                            <div class="wb-button-field wb-button-field-wide">
                                                <input type="text" :name="'buttons[' + index + '][text]'" class="form-control btn-text" maxlength="25" v-model="button.text" placeholder="<?= _l('button_text'); ?>" @input="clearButtonError(index, 'text')">
                                                <span class="help-block text-danger tw-block mtop5" v-if="getButtonError(index, 'text')">{{ getButtonError(index, 'text') }}</span>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="wb-button-field">
                                                <input type="text" :name="'buttons[' + index + '][text]'" class="form-control btn-text" maxlength="25" v-model="button.text" placeholder="<?= _l('button_text'); ?>" @input="clearButtonError(index, 'text')">
                                                <span class="help-block text-danger tw-block mtop5" v-if="getButtonError(index, 'text')">{{ getButtonError(index, 'text') }}</span>
                                            </div>
                                            <div class="wb-button-field" v-if="button.type === 'URL'">
                                                <input type="text" :name="'buttons[' + index + '][url]'" class="form-control btn-url" v-model="button.url" placeholder="https://example.com" @input="clearButtonError(index, 'url')">
                                                <span class="help-block text-danger tw-block mtop5" v-if="getButtonError(index, 'url')">{{ getButtonError(index, 'url') }}</span>
                                            </div>
                                            <div class="wb-button-field" v-else-if="button.type === 'PHONE_NUMBER'">
                                                <input type="text" :name="'buttons[' + index + '][phone_number]'" class="form-control btn-phone" v-model="button.phone_number" placeholder="+919999999999" @input="clearButtonError(index, 'phone_number')">
                                                <span class="help-block text-danger tw-block mtop5" v-if="getButtonError(index, 'phone_number')">{{ getButtonError(index, 'phone_number') }}</span>
                                            </div>
                                        </template>
                                        <div class="wb-button-actions">
                                            <button type="button" class="btn btn-sm btn-danger remove-btn" @click="removeButton(index)" aria-label="<?= _l('remove'); ?>">&times;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <a href="<?= admin_url('whatsbot/templates'); ?>" class="btn btn-default"><?= _l('cancel'); ?></a>
                        <button type="button" class="btn btn-primary" @click="submitTemplate"><?= _l('create_template'); ?></button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-heading"><?= _l('preview'); ?></div>
                    <div class="panel-body" style="background: #e5ddd5; border-radius: 8px; padding: 15px;">
                        <div style="background: white; border-radius: 8px; padding: 10px; margin: 0 auto; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <div id="preview_header" class="tw-font-bold tw-mb-1" v-html="previewHeaderHtml"></div>
                            <div id="preview_media" class="tw-mb-1" v-html="previewMediaHtml"></div>
                            <div id="preview_body" class="tw-text-sm tw-text-gray-700 tw-mb-1 wb-preview-body" v-html="previewBodyHtml"></div>
                            <div id="preview_footer" class="tw-text-xs tw-text-gray-400 tw-mb-2" v-text="footerData"></div>
                            <div id="preview_buttons" class="tw-w-full">
                                <div v-for="button in buttons" :key="button.uid" class="wb-preview-button-row tw-flex tw-items-center tw-justify-center tw-gap-2 tw-w-full tw-py-3 tw-px-3 tw-text-sky-600 tw-cursor-default tw-text-sm tw-font-medium">
                                    <i v-if="button.type === 'QUICK_REPLY'" class="fa fa-reply"></i>
                                    <i v-else-if="button.type === 'URL'" class="fa fa-globe"></i>
                                    <i v-else-if="button.type === 'PHONE_NUMBER'" class="fa fa-phone"></i>
                                    <span>{{ button.text }}</span>
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
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/vue.min.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/template_builder_vue.js') . '?v=' . $module_version; ?>"></script>
<script>
    window.wbTemplateBuilderConfig = <?= json_encode([
        'headerFormat' => isset($tpl) ? strtoupper((string) $tpl->header_data_format) : 'NONE',
        'headerDataText' => isset($tpl) ? (string) ($tpl->header_data_text ?? '') : '',
        'headerVariable1' => '',
        'headerMediaUrl' => isset($tpl) ? (string) ($tpl->header_media_url ?? '') : '',
        'bodyText' => isset($tpl) ? (string) ($tpl->body_data ?? '') : '',
        'bodyVariableIndexes' => $bodyVariableCount > 0 ? range(1, (int) $bodyVariableCount) : [],
        'footerData' => isset($tpl) ? (string) ($tpl->footer_data ?? '') : '',
        'buttons' => $existingButtons,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
