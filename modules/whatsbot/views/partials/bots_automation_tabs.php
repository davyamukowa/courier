<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
$active_tab = $active_group ?? 'message_bot';

if ('ai_assistant' === $active_tab) {
    return;
}

$tabs = [];

if (in_array($active_tab, ['message_bot', 'template_bot'], true)) {
    $tabs = [
        [
            'key' => 'message_bot',
            'label' => _l('message_bot'),
            'icon' => 'fa-regular fa-message',
            'url' => admin_url(WHATSBOT_MODULE . '/bots?group=message_bot'),
            'visible' => staff_can('view', 'wtc_message_bot') || staff_can('view_own', 'wtc_message_bot'),
        ],
        [
            'key' => 'template_bot',
            'label' => _l('template_bot'),
            'icon' => 'fa-regular fa-file-lines',
            'url' => admin_url(WHATSBOT_MODULE . '/bots?group=template_bot'),
            'visible' => staff_can('view', 'wtc_template_bot'),
        ],
    ];

    $tabs = array_values(array_filter($tabs, function ($tab) {
        return !empty($tab['visible']);
    }));
} elseif (in_array($active_tab, ['bot_flow', 'automation'], true)) {
    $tabs = [
        [
            'key' => 'bot_flow',
            'label' => _l('bot_flow_builder'),
            'icon' => 'fa-solid fa-code-fork',
            'url' => admin_url(WHATSBOT_MODULE . '/bot_flow'),
            'visible' => staff_can('view', 'wtc_bot_flow'),
        ],
        [
            'key' => 'automation',
            'label' => _l('marketing_automation'),
            'icon' => 'fa-solid fa-sliders',
            'url' => admin_url(WHATSBOT_MODULE . '/marketing_automation'),
            'visible' => staff_can('view', 'wtc_template'),
        ],
    ];

    $tabs = array_values(array_filter($tabs, function ($tab) {
        return !empty($tab['visible']);
    }));
} else {
    $tabs = [
        [
            'key' => 'message_bot',
            'label' => _l('message_bot'),
            'icon' => 'fa-regular fa-message',
            'url' => admin_url(WHATSBOT_MODULE . '/bots?group=message_bot'),
            'visible' => staff_can('view', 'wtc_message_bot') || staff_can('view_own', 'wtc_message_bot'),
        ],
        [
            'key' => 'template_bot',
            'label' => _l('template_bot'),
            'icon' => 'fa-regular fa-file-lines',
            'url' => admin_url(WHATSBOT_MODULE . '/bots?group=template_bot'),
            'visible' => staff_can('view', 'wtc_template_bot'),
        ],
    ];

    $tabs = array_values(array_filter($tabs, function ($tab) {
        return !empty($tab['visible']);
    }));
}

$tabs = array_values(array_filter($tabs, function ($tab) {
    return !empty($tab['visible']);
}));

$this->load->view('whatsbot/partials/whatsbot_tabs', compact('tabs', 'active_tab'));
