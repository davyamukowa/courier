<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('whatsbot/partials/bots_automation_tabs', ['active_group' => $active_group ?? 'bot_flow']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('bot_flow_builder'); ?></h4>
                            <div class="">
                                <?php if (staff_can('create', 'wtc_bot_flow')) { ?>
                                    <a href="#" id="new_bot_flow" data-toggle="modal" data-target="#new_bot_flow_modal" class="btn btn-primary"> <?= _l('create_new_flow'); ?></a>
                                    <div class="modal fade" id="new_bot_flow_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                        <?= form_open('', ['id' => 'new_bot_flow_form'], ['id' => '']); ?>
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h4 id="heading_text" class="modal-title"><?= _l('bot_flow'); ?><h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?= render_input('flow_name', _l('flow_name')); ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                                                    <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                            <div class="">
                                <?php echo render_datatable([
                                    _l('the_number_sign'),
                                    _l('name'),
                                    _l('active'),
                                    _l('action')
                                ], 'bot_flow_table'); ?>
                            </div>
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
    "use strict";
    initDataTable('.table-bot_flow_table', `${admin_url}whatsbot/campaigns/get_table_data/bot_flow_table`);

    $('#new_bot_flow').on('click', function() {
        $('input[name="id"]').val('');
        $('#flow_name').val('');
    });

    appValidateForm($('#new_bot_flow_form'), {
        'flow_name' : 'required',
    });

    $(document).on('submit', '#new_bot_flow_form', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: `${admin_url}whatsbot/bot_flow/save`,
            type: 'POST',
            dataType: 'json',
            data: formData,
        })
        .done(function(res) {
            alert_float(res.type, res.message);
            $('#new_bot_flow_modal').modal("hide");
            $('input[name="id"]').val('');
            $('#flow_name').val('');
            $('.table-bot_flow_table').DataTable().ajax.reload();
        });
    });

    $(document).on('click', '.edit_flow', function() {
        $('input[name="id"]').val($(this).data('id'));
        $('#flow_name').val($(this).data('name'));
        $('#new_bot_flow_modal').modal("show");
    })
</script>
