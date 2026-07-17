<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">

    </div>
</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="whatsapp_cron_job_settings">
        <div class="alert alert-info tw-mb-0">
            <span class="bold text-info">WHATSAPP CRON COMMAND: wget -q -O-
                <?php echo site_url('whatsbot/cron/index') ?>
            </span><br />
            <?php if (is_admin()) { ?>
                <a href="<?= site_url('whatsbot/cron/manually') ?>">Run Cron Manually</a>
            <?php } ?>
        </div>
    </div>
</div>