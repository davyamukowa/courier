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
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-mt-0 tw-font-semibold">
                                <?php echo _l('flows'); ?>
                            </h4>
                            <div class="<?= is_mobile() ? 'tw-flex tw-flex-col tw-gap-2' : ''; ?>">
                                <?php if (staff_can('load_flow', 'wtc_flow')) { ?>
                                    <button class="btn btn-primary load_flows"><?php echo _l('load_flows'); ?></button>
                                <?php } ?>
                                <a href="https://business.facebook.com/latest/whatsapp_manager/flows/" class="btn btn-primary <?= is_mobile() ? '' : 'tw-ml-1'; ?>" target="_blank"><?php echo _l('flow_management'); ?></a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                            <?php render_datatable([
                                _l('the_number_sign'),
                                _l('flow_name'),
                                _l('category'),
                                _l('status'),
                                _l('action'),
                            ], 'flows'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script type="text/javascript">
    "use strict";
    initDataTable('.table-flows', `${admin_url}whatsbot/flows/get_table_data`, [], [], [], [2, 'ASC']);

    $(document).on("click", ".flow_preview", function() {
        $.get(`${admin_url}whatsbot/flows/get_preview/${$(this).data("id")}`, function(data) {
            window.open(data, '_blank', 'width=800,height=600,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');
        });
    });

    $('.load_flows').on('click', function() {
        $.ajax({
            url: `${admin_url}whatsbot/flows/load_flows`,
            type: 'POST',
            dataType: 'json'
        }).done(function(res) {
            if (res.success == true) {
                alert_float('success', res.message);
                $('.table-flows').DataTable().ajax.reload();
            } else {
                alert_float('danger', res.message);
                $('.table-flows').DataTable().ajax.reload();
            }
        });
    });
</script>
