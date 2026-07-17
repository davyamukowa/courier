<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'template_campaigns', 'label' => _l('campaigns'), 'icon' => 'fa-solid fa-bullhorn', 'url' => admin_url('whatsbot/campaigns')],
                    ['key' => 'csv_campaigns', 'label' => _l('csv_campaign'), 'icon' => 'fa-solid fa-file-csv', 'url' => admin_url('whatsbot/bulk_campaigns')],
                    ['key' => 'drip_sequences', 'label' => _l('drip_sequences'), 'icon' => 'fa-solid fa-water', 'url' => admin_url('whatsbot/drip_campaigns')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'drip_sequences']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="tw-my-0 tw-font-semibold"><?= _l('drip_sequences'); ?></h4>
                            <?php if (staff_can('create', 'wtc_drip')) { ?>
                                <a href="<?= admin_url('whatsbot/drip_campaigns/sequence'); ?>" class="btn btn-primary"><?= _l('create_drip_sequence'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                            <?= render_datatable([
                                _l('id'),
                                _l('name'),
                                _l('rel_type'),
                                _l('steps'),
                                _l('enrolled'),
                                _l('status'),
                                _l('created_at'),
                                _l('options'),
                            ], 'whatsbot-drip-sequences');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-whatsbot-drip-sequences', admin_url + 'whatsbot/drip_campaigns/get_sequences_table', 'undefined', 'undefined', 'undefined', [0, 'desc']);
    });
</script>
