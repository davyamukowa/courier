<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'custom_label', 'label' => _l('custom_label'), 'icon' => 'fa-solid fa-tags', 'url' => admin_url('whatsbot/custom_label'), 'visible' => staff_can('view', 'wtc_custom_label')],
                    ['key' => 'canned_reply', 'label' => _l('canned_reply'), 'icon' => 'fa-regular fa-comment-dots', 'url' => admin_url('whatsbot/canned_reply'), 'visible' => staff_can('view', 'wtc_canned_reply')],
                    ['key' => 'ai_prompts', 'label' => _l('ai_prompts'), 'icon' => 'fa-solid fa-wand-magic-sparkles', 'url' => admin_url('whatsbot/ai_prompts'), 'visible' => staff_can('view', 'wtc_ai_prompts')],
                    ['key' => 'opt_out', 'label' => _l('opt_out'), 'icon' => 'fa-solid fa-toggle-off', 'url' => admin_url('whatsbot/optout'), 'visible' => staff_can('view', 'opt_out')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'custom_label']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('custom_label'); ?></h4>
                            <div class="">
                                <?php if(staff_can('create', 'wtc_custom_label')): ?>
                                    <a href="#" id="new_status" data-toggle="modal" data-target="#new_custom_label_modal" class="btn btn-primary btnaddnew"><i class="fa-regular fa-plus tw-mr-1"></i> <?= _l('add_new_custom_label') ?></a>
                                <?php endif; ?>
                                <?= form_open(admin_url('whatsbot/custom_label/save'), ['id' => 'new_custom_label_form', 'class' => ''], ['id' => '']); ?>
                                <div class="modal fade" id="new_custom_label_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 id="heading_text" class="modal-title"><?= _l('custom_label') ?><h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php echo render_input('label', _l('label')); ?>
                                                <?php echo render_color_picker('color', _l('color')); ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
                                                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                            <div class="">
                                <?= render_datatable([
                                    _l('#'),
                                    _l('label'),
                                    _l('color'),
                                    _l('action')
                                ], 'custom_label_table') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function() {
        $("body").find("div.colorpicker-input").colorpicker({
            format: "hex",
        });
        $('.btnaddnew').click(function() {
            $('input[name="id"]').val('');
            $('#label').val("");
            $('#color').val("");
            init_color_pickers();
        });

        appValidateForm($('#new_custom_label_form'), {
            label: {
                required: true,
                remote: {
                    url: admin_url + 'whatsbot/custom_label/label_exist',
                    type: 'post',
                    data: {
                        label: function() {
                            return $("#label").val();
                        },
                        id: function() {
                            return $('input[name="id"]').val();
                        },
                    },
                },
            },
            color: 'required',
        }, custom_labelSubmitHandler, {
            label: {
                remote: 'This Label is Already Exist'
            }
        });

        initDataTable('.table-custom_label_table', admin_url + 'whatsbot/custom_label/get_table_data', undefined, 2, undefined);

        function custom_labelSubmitHandler(form) {
            var formData = $(form).serialize();
            var action = $(form).attr('action');
            $.ajax({
                    type: 'POST',
                    url: admin_url + 'whatsbot/custom_label/save',
                    data: formData,
                    dataType: "json",
                })
                .done(function(response) {
                    $("#new_custom_label_modal").modal("hide");
                    alert_float(response.type, response.message);
                    $('input[name="id"]').val('');
                    $('#label').val("");
                    $('#input[name="color"]').val("");
                    $('.table-custom_label_table').DataTable().ajax.reload();
                });
        }

    });

    function editCustomLabel(id) {
        $.ajax({
                url: admin_url + 'whatsbot/custom_label/get_data/' + id,
                type: 'POST',
                dataType: 'json',
            })
            .done(function(response) {
                init_color_pickers();
                $("#new_custom_label_modal").modal("show");
                $('#label').val(response.label);
                $('.colorpicker-input').colorpicker('setValue', response.color);
                $('input[name="id"]').val(response.id);
            });
    }
</script>
