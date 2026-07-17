<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('whatsbot/partials/bots_automation_tabs', ['active_group' => $active_group ?? 'ai_assistant']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('personal_assistant'); ?></h4>
                            <div class="">
                                <?php if (staff_can('create', 'wtc_pa')) { ?>
                                    <a href="<?= admin_url('whatsbot/personal_assistants/personal_assistant');?>" id="create-btn" class="btn btn-primary"><?php echo _l('create_personal_assistant'); ?></a>
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
                                    _l('actions'),
                                ], 'pa_table'); ?>
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

    initDataTable('.table-pa_table', `${admin_url}whatsbot/personal_assistants/get_table_data`);
</script>
