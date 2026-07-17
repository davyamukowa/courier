<?php
/*
 * Inject permissions Feature and Capabilities for whatsbot module
 */
hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName = _l('permission_view');

    $allPermissionsArray = [
        'view' => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $permissions['wtc_connect_account'] = [
        'name' => _l('whatsbot_connect_account'),
        'capabilities' => [
            'connect' => _l('connect'),
        ],
    ];

    $permissions['wtc_message_bot'] = [
        'name' => _l('whatsbot_message_bot'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['wtc_message_bot']['capabilities']['clone_bot'] = _l('permission_bot_clone');

    $permissions['wtc_template_bot'] = [
        'name' => _l('whatsbot_template_bot'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['wtc_template_bot']['capabilities']['clone_bot'] = _l('permission_bot_clone');

    $permissions['wtc_template'] = [
        'name' => _l('whatsbot_template'),
        'capabilities' => [
            'view' => $viewGlobalName,
            'load_template' => _l('load_template'),
            'create' => _l('create_template'),
        ],
    ];

    $permissions['wtc_campaign'] = [
        'name' => _l('whatsbot_campaigns'),
        'capabilities' => array_merge($allPermissionsArray, [
            'show' => _l('show_campaign'),
        ]),
    ];

    $permissions['wtc_chat'] = [
        'name' => _l('whatsbot_chat'),
        'capabilities' => [
            'view' => $viewGlobalName,
            'view_own' => _l('read_only')
        ],
    ];

    $permissions['wtc_log_activity'] = [
        'name' => _l('whatsbot_log_activity'),
        'capabilities' => [
            'view' => $viewGlobalName,
            'clear_log' => _l('clear_log'),
        ],
    ];

    $permissions['wtc_settings'] = [
        'name' => _l('whatsbot_settings'),
        'capabilities' => [
            'view' => _l('view'),
        ],
    ];

    $permissions['wtc_bulk_campaign'] = [
        'name' => _l('whatsbot_bulk_campaign'),
        'capabilities' => [
            'send' => _l('send'),
        ],
    ];

    $permissions['wtc_canned_reply'] = [
        'name' => _l('whatsbot_canned_reply'),
        'capabilities' => array_merge($allPermissionsArray, ['view_own' => _l('permission_view_own')]),
    ];

    $permissions['wtc_ai_prompts'] = [
        'name' => _l('whatsbot_ai_prompts'),
        'capabilities' => array_merge($allPermissionsArray, ['view_own' => _l('permission_view_own')]),
    ];

    $permissions['wtc_bot_flow'] = [
        'name' => _l('whatsbot_bot_flow'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['wtc_pa'] = [
        'name' => _l('whatsbot_pa'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['wtc_webhook'] = [
        'name' => _l('whatsbot_webhook'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['opt_out'] = [
        'name' => _l('whatsbot_opt_out'),
        'capabilities' => [
            'view' => $viewGlobalName,
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['wtc_drip'] = [
        'name' => _l('whatsbot_drip_sequences'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['wtc_analytics'] = [
        'name' => _l('whatsbot_analytics'),
        'capabilities' => [
            'view' => $viewGlobalName,
        ],
    ];

    $permissions['wtc_catalog_sync'] = [
        'name' => _l('whatsbot_catalog'),
        'capabilities' => [
            'view' => $viewGlobalName,
            'edit' => _l('permission_edit'),
        ],
    ];

    $permissions['wtc_custom_label'] = [
        'name' => _l('whatsbot_custom_label'),
        'capabilities' => $allPermissionsArray,
    ];

    return $permissions;
});
