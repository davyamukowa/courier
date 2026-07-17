<div class="modal fade" id="initiate_chat_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <?php echo form_open_multipart(admin_url('whatsbot/campaigns/initiateChat'), ['id' => 'initiate_chat_form'], ['id' => '']); ?>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 id="heading_text" class="modal-title"><?= _l('initiate_chat'); ?><h4>
            </div>
            <div class="modal-body">
                <?php echo render_select('template_id', wb_get_whatsapp_template(), ['id', 'template_name', 'language'], 'template', $campaign['template_id'] ?? ''); ?>

                <div class="row variableDetails hide mtop45">
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <?= render_input('chat_rel_type', '', '', 'hidden'); ?>
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
                    <div class="col-md-6">
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
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
        </div>
    </div>
</div>