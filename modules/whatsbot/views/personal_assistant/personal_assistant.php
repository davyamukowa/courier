<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->config('whatsbot/openai'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo (isset($pa)) ? _l('edit_personal_assistant') : _l('new_personal_assistant'); ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <?php echo form_open_multipart('', ['id' => 'pa_form'], ['id' => $pa['id'] ?? '', "openai_assistant_id" => $pa['openai_assistant_id'] ?? ""]); ?>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?php if (get_option('wb_open_ai_key_verify') != 1) { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger">
                                                <?= _l('openai_key_not_verified_note'); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?= render_input('name', _l('assistant_name'), $pa['name'] ?? ''); ?>
                                <?= render_textarea('pa_description', 'pa_description', (isset($pa)) ? $pa['pa_description'] : '', ['rows'=> 3]) ?>
                                <?= render_textarea('pa_instruction', 'pa_instruction', (isset($pa)) ? $pa['pa_instruction'] : '') ?>
                                <?= render_select('assistant_model', config_item('assistant_models'), ['key', 'value'], 'ai_model', (isset($pa)) ? $pa['assistant_model'] : '', [], [], '', '', false); ?>
                                <div class="range-container tw-flex tw-gap-3 tw-items-center tw-justify-between mbot10">
                                    <div class="tw-flex tw-flex-col tw-justify-center" style="width:695px">
                                        <label for="temperature" class="form-label"><i class="fa-regular fa-circle-question tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('temperature_note'); ?>" data-placement="top"></i><?php echo _l('temperature'); ?></label>
                                        <input type="range" name="pa_temperature" id="temperature" min="0.1" max="2.0" step="0.1" value="<?= (isset($pa)) ? $pa['pa_temperature'] : 1 ?>"  oninput="updateValue('temperature', this.value)">
                                    </div>
                                    <div class="tw-border tw-border-neutral-300/80 tw-border-solid tw-px-4 tw-py-1 tw-rounded">
                                        <span class="range-value" id="temperature-value"><?= (isset($pa)) ? $pa['pa_temperature'] : 1 ?></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="attachments"><?= _l('pa_files'); ?></label>
                                        <div class="well well-lg">
                                            <div class="dropzone dropzone-manual">
                                                <div class="dz-default dz-message dropzoneDragArea">
                                                    <span><?php echo _l('upload'); ?></span><br>
                                                    <i class="mtop20 fa fa-plus fa-2xl"></i>
                                                </div>
                                                <div class="dropzone-previews"></div>
                                            </div>
                                            <div id="attachedImages">
                                                <?php if (isset($pa['files']) && count($pa['files']) > 0) { ?>
                                                    <?php
                                                    $upload_path = get_upload_path_by_type('personal_assistant');
                                                    foreach ($pa['files'] as $attachment) {
                                                        $path = $upload_path . $pa['id'] . '/' . $attachment['file_name'];
                                                        if (is_image($path)) {
                                                            $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $attachment['filetype']);
                                                            $lightBoxUrl = site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type=' . $attachment['filetype']);
                                                        } else {
                                                            $pdf_url = base_url($path);
                                                        }
                                                    ?>
                                                        <div class="attached-image-item" data-fileid="<?= $attachment['id'] ?>">
                                                            <?php
                                                            if (is_image($path)) {
                                                            ?>
                                                                <a href="<?= $lightBoxUrl; ?>" data-lightbox="customer-profile" class="display-block mbot5">
                                                                    <div class="table-image">
                                                                        <a href="<?= $lightBoxUrl; ?>" data-lightbox="customer-profile" class="display-block mbot5">
                                                                            <img src="<?= $img_url; ?>">
                                                                    </div>
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <a href="<?= $pdf_url; ?>" class="display-block mbot5" target="_blank">
                                                                    <div class="table-image">
                                                                        <span class="display-block mbot5"><i class="mime mime-pdf"></i></span>
                                                                    </div>
                                                                </a>
                                                            <?php } ?>
                                                            <a class="remove_file text-danger mleft15" style="display: inline-block;position: absolute;top:0;right:0; cursor: pointer;"><i class="fa fa-times"></i></a>
                                                        </div>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary saveBtn"><?= _l('save'); ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?= _l('file_upload_guidelines'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <div class="tw-p-3 tw-flex tw-justify-between tw-items-center" role="tab" id="headingOne">
                                        <span class="tw-font-bold"><?= _l('supported_file_formats'); ?> </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor"
                                            class="tw-w-5 tw-h-5  tw-text-neutral-500 group-hover:tw-text-neutral-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </a>
                                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                                    <div class="panel-body">
                                        <div class="tw-flex tw-flex-col tw-justify-start">
                                            <p><span class="tw-font-semibold"><?= _l('pdf'); ?></span><?= _l('pdf_text'); ?></p>
                                            <p><span class="tw-font-semibold"><?= _l('word'); ?></span><?= _l('word_text'); ?></p>
                                            <p><span class="tw-font-semibold"><?= _l('text'); ?></span><?= _l('text_text'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <div class="tw-p-3 tw-flex tw-justify-between tw-items-center" role="tab" id="headingTwo">
                                        <span class="tw-font-bold"> <?= _l('what_to_avoid'); ?> </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor"
                                            class="tw-w-5 tw-h-5 tw-text-neutral-500 group-hover:tw-text-neutral-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </a>
                                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                                    <div class="panel-body">
                                        <div class="tw-flex tw-flex-col tw-justify-start">
                                            <p><span class="tw-font-semibold"><?= _l('scanned_images'); ?></span><?= _l('scanned_images_text'); ?></p>
                                            <p><span class="tw-font-semibold"><?= _l('junk_characters'); ?></span><?= _l('junk_characters_text'); ?></p>
                                            <p><span class="tw-font-semibold"><?= _l('large_files'); ?></span><?= _l('large_files_text'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <div class="tw-p-3 tw-flex tw-justify-between tw-items-center" role="tab" id="headingThree">
                                        <span class="tw-font-bold"><?= _l('file_naming'); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor"
                                            class="tw-w-5 tw-h-5  tw-text-neutral-500 group-hover:tw-text-neutral-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </a>
                                <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                    <div class="panel-body">
                                        <div class="tw-flex tw-flex-col tw-justify-start">
                                            <p><span class="tw-font-semibold"><?= _l('avoid_special_characters'); ?></span> <?= _l('avoid_special_characters_text'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <div class="tw-p-3 tw-flex tw-justify-between tw-items-center" role="tab" id="headingFour">
                                        <span class="tw-font-bold"><?= _l('best_practices'); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor"
                                            class="tw-w-5 tw-h-5  tw-text-neutral-500 group-hover:tw-text-neutral-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </a>
                                <div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                    <div class="panel-body">
                                        <div class="tw-flex tw-flex-col tw-justify-start">
                                            <p><?= _l('well_structured_text'); ?></p>
                                            <p><?= _l('proper_encoding'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                <p class="mt-3"><?= _l('modal_processing_note'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    "use strict";
    Dropzone.autoDiscover = false;
    var PADropZone;

    function toggleActive(element) {
        // Toggle active class
        element.classList.toggle('tw-bg-info-300/70');
        // Remove 'tw-bg-info-100' from any other tab if you have more tabs
        const otherTabs = document.querySelectorAll('[role="tab"]:not(#' + element.id + ')');
        otherTabs.forEach(tab => tab.classList.remove('tw-bg-info-300/70'));
    }
    $(function() {
        if ($('.dropzoneDragArea').length > 0) {
            PADropZone = new Dropzone("#pa_form", appCreateDropzoneOptions({
                acceptedFiles: '.doc, .docx, .txt, .pdf',
                autoProcessQueue: false,
                paramName: "file",
                clickable: '.dropzoneDragArea',
                previewsContainer: '.dropzone-previews',
                addRemoveLinks: true,
                maxFiles: 3,
                parallelUploads: 3,
                uploadMultiple: true,
                addedfiles: function(file) {
                    $('.saveBtn').attr('disabled', false);
                },
                success: function(file, response) {
                    response = JSON.parse(response);
                    $('#loadingModal').modal('hide');
                    if (response.status) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                            setTimeout(() => {
                                window.location.assign(response.url);
                            }, 500);
                        }
                    } else {
                        alert_float('danger', "Error while processing File. Please try another file or reupload");
                        return false;
                    }
                },
            }));
        }

        appValidateForm($('#pa_form'), {
            name: "required",
            pa_instruction: "required",
        }, formSubmission);

        function formSubmission(form) {
            $('.saveBtn').attr('disabled', true);
            $.post('<?= admin_url('whatsbot/personal_assistants/save'); ?>', $(form).serialize()).done(function(response) {
                response = JSON.parse(response);
                <?php if (get_option('wb_open_ai_key_verify') != 1) { ?>
                    alert_float('danger', "<?= _l('cant_upload_file_verification_pending'); ?>");
                    $('.saveBtn').attr('disabled', false);
                    return;
                <?php } ?>
                if (response.id) {
                    $("#pa_form [name='id']").val(response.id);
                    if (typeof(PADropZone) !== 'undefined') {
                        if (PADropZone.getQueuedFiles().length > 0) {
                            $('#loadingModal').modal({
                                backdrop: 'static', // Prevent closing by clicking outside
                                keyboard: false // Prevent closing with ESC key
                            }).modal('show');
                            PADropZone.options.url = admin_url + 'whatsbot/personal_assistants/add_attachment/' + response.id;
                            PADropZone.on("success", function(file, data) {
                                data = JSON.parse(data);
                                console.log(data);
                                if (data.status) {
                                    // Show alert after DropZone completes
                                    alert_float(response.type, response.message);

                                    // Redirect after showing the alertDD
                                    setTimeout(() => {
                                        // window.location.assign(response.url);
                                    }, 500);
                                }
                            });
                            PADropZone.processQueue();
                        } else {
                            $('#loadingModal').modal('hide');
                            alert_float(response.type, response.message);
                            setTimeout(() => {
                                window.location.assign(response.url);
                            }, 500);
                        }
                    } else {
                        $('#loadingModal').modal('hide');
                        alert_float(response.type, response.message);
                        setTimeout(() => {
                            window.location.assign(response.url);
                        }, 500);
                    }
                } else {
                    $('#loadingModal').modal('hide');
                    alert_float(response.type, response.message);
                    setTimeout(() => {
                        window.location.assign(response.url);
                    }, 500);
                }
            });
        }

        $(".remove_file").click(function() {
            if (confirm_delete()) {
                // alert("yes");
                $(this).parents(".attached-image-item").remove();
                var fileid = $(this).parents(".attached-image-item").data("fileid");
                $.get('<?= admin_url('whatsbot/personal_assistants/delete_document/'); ?>'+fileid, function(){
                    if($(".attached-image-item").length == 0){
                        $('.saveBtn').attr('disabled', true);
                    }
                });
                return;
            }
            // alert("no");
        });
    });
    // $('#temperature').trigger('input');

    function updateValue(id, value) {
        document.getElementById(id + '-value').innerText = value;
    }
</script>
