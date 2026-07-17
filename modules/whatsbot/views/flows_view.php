<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'message_templates', 'label' => _l('templates'), 'icon' => 'fa-regular fa-note-sticky', 'url' => admin_url('whatsbot/templates')],
                    ['key' => 'whatsapp_flows', 'label' => _l('flow_templates'), 'icon' => 'fa-solid fa-code-fork', 'url' => admin_url('whatsbot/flows')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'whatsapp_flows']);
                ?>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="panel panel-default rounded-lg shadow-lg">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-8">
                                <span class="text-muted tw-font-semibold text-uppercase"><?php echo _l('flow_responses'); ?></span>
                                <h5 class="tw-mt-2 tw-mb-0 tw-font-semibold"><?php echo count($flow->responses) ?? '0'; ?></h5>
                                <p class="tw-mt-2 tw-mb-0">
                                    <?php echo _d($flow->submit_time ?? ''); ?>
                                </p>
                            </div>
                            <span class="circle numbertext circle_warning"><i class="fa-solid fa-arrow-trend-up menu-icon fa-xl"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-mt-0 tw-mb-0 tw-font-semibold">
                                <?php echo _l('flow_responses'); ?>
                            </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                            <?php render_datatable([
                                _l('the_number_sign'),
                                _l('name'),
                                _l('receiver'),
                                _l('submit_time'),
                                _l('whatsapp_no'),
                                _l('type'),
                                _l('action'),
                            ], 'flows_responses'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="response_preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('flow_responses'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";
    initDataTable('.table-flows_responses', `${admin_url}whatsbot/flows/get_table_data/flow_responses/<?= $flow->flow_id ?>`, [], [], [], [2, 'ASC']);
    $('#response_preview').on('show.bs.modal', function(e) {
        var invoker = $(e.relatedTarget);
        var res_id = $(invoker).data('id');
        $.get(`${admin_url}whatsbot/flows/flow_review/${res_id}`, function(data){
            $('#response_preview .modal-body').html(data)
        });
    });
</script>
