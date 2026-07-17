<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'custom_label', 'label' => _l('custom_label'), 'icon' => 'fa-solid fa-tags', 'url' => admin_url('whatsbot/custom_label')],
                    ['key' => 'canned_reply', 'label' => _l('canned_reply'), 'icon' => 'fa-regular fa-comment-dots', 'url' => admin_url('whatsbot/canned_reply')],
                    ['key' => 'ai_prompts', 'label' => _l('ai_prompts'), 'icon' => 'fa-solid fa-wand-magic-sparkles', 'url' => admin_url('whatsbot/ai_prompts')],
                    ['key' => 'opt_out', 'label' => _l('opt_out'), 'icon' => 'fa-solid fa-toggle-off', 'url' => admin_url('whatsbot/optout')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'custom_label']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700">
                            <?= _l('miscellaneous'); ?>
                        </h4>
                        <p class="text-muted no-margin">
                            Quick access to the lighter WhatsBot settings pages.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
