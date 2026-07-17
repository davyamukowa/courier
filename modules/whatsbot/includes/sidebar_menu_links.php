<?php

/*
 * Inject sidebar menu and links for whatsbot module
 */
hooks()->add_action('admin_init', function () {

    if (get_instance()->app_modules->is_active('whatsbot')) {

        // Parent menu
        get_instance()->app_menu->add_sidebar_menu_item('whatsbot', [
            'slug' => 'whatsbot',
            'name' => _l('whatsbot'),
            'icon' => 'fa-brands fa-whatsapp',
            'href' => '#',
            'position' => 20,
        ]);

        // 1. Connect Account
        if (staff_can('connect', 'wtc_connect_account')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_connect_account',
                'name' => _l('connect_account'),
                'icon' => 'fa-solid fa-link',
                'href' => admin_url(WHATSBOT_MODULE . '/connect'),
                'position' => 1,
            ]);
        }

        // 3. Chat (most used top position)
        if (staff_can('view', 'wtc_chat') || staff_can('view_own', 'wtc_chat')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsapp_chat',
                'name' => _l('chat'),
                'icon' => 'fa-regular fa-comment-dots',
                'href' => admin_url(WHATSBOT_MODULE . '/chat'),
                'position' => 3,
            ]);
        }

        // 4. AI Personal Assistant
        if (staff_can('view', 'wtc_pa')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_ai_personal_assistant',
                'name' => _l('personal_assistant'),
                'icon' => 'fa fa-brain',
                'href' => admin_url(WHATSBOT_MODULE . '/personal_assistants'),
                'position' => 4,
            ]);
        }

        // 5. Bots
        if (staff_can('view', 'wtc_message_bot') || staff_can('view_own', 'wtc_message_bot') || staff_can('view', 'wtc_template_bot')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_bots',
                'name' => _l('bots'),
                'icon' => 'fa-solid fa-robot',
                'href' => admin_url(WHATSBOT_MODULE . '/bots?group=message_bot'),
                'position' => 5,
            ]);
        }

        // 6. Automations
        if (staff_can('view', 'wtc_bot_flow') || staff_can('view', 'wtc_template')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_automations',
                'name' => _l('automation'),
                'icon' => 'fa fa-cogs',
                'href' => admin_url(WHATSBOT_MODULE . '/bot_flow'),
                'position' => 6,
            ]);
        }

        // 7. Campaigns
        if (staff_can('view', 'wtc_campaign') || staff_can('send', 'wtc_bulk_campaign') || staff_can('view', 'wtc_drip')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_campaigns',
                'name' => _l('campaigns'),
                'icon' => 'fa-solid fa-bullhorn',
                'href' => admin_url(WHATSBOT_MODULE . '/campaigns'),
                'position' => 7,
            ]);
        }

        // 8. Templates & Flows
        if (staff_can('view', 'wtc_template')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_templates',
                'name' => _l('templates_and_flows'),
                'icon' => 'fa-solid fa-scroll',
                'href' => admin_url(WHATSBOT_MODULE . '/templates'),
                'position' => 8,
            ]);
        }

        // 9. Commerce
        if (staff_can('view', 'wtc_catalog_sync')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_commerce',
                'name' => _l('commerce'),
                'icon' => 'fa-solid fa-store',
                'href' => admin_url(WHATSBOT_MODULE . '/catalog_products'),
                'position' => 9,
            ]);
        }

        // 10. Analytics
        if (staff_can('view', 'wtc_analytics') || staff_can('view', 'wtc_log_activity')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_analytics',
                'name' => _l('analytics'),
                'icon' => 'fa-solid fa-chart-line',
                'href' => admin_url(WHATSBOT_MODULE . '/analytics'),
                'position' => 10,
            ]);
        }

        // 11. Miscellaneous
        if (staff_can('view', 'wtc_custom_label') || staff_can('send', 'wtc_canned_reply') || staff_can('view', 'opt_out') || staff_can('view', 'wtc_ai_prompts')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_miscellaneous',
                'name' => _l('miscellaneous'),
                'icon' => 'fa-solid fa-layer-group',
                'href' => admin_url(WHATSBOT_MODULE . '/custom_label'),
                'position' => 11,
            ]);
        }

        // 12. Settings
        if (staff_can('view', 'wtc_settings') || staff_can('connect', 'wtc_connect_account')) {
            get_instance()->app_menu->add_sidebar_children_item(WHATSBOT_MODULE, [
                'slug' => 'whatsbot_settings',
                'name' => _l('settings'),
                'icon' => 'fa-solid fa-gears',
                'href' => admin_url(WHATSBOT_MODULE . '/Whatsbot_settings'),
                'position' => 12,
            ]);
        }
    }
});

hooks()->add_action('module_deactivated', function ($module_name) {
    if (WHATSBOT_MODULE == $module_name['system_name']) {
        $url = basename(get_instance()->app_modules->get(WHATSBOT_MODULE)['headers']['uri']) . '-' . trim(preg_replace(['#/admin.*#', '#https?://#', '/[^a-zA-Z0-9]+/'], ['', '', '-'], current_full_url()), '-');
        write_file(TEMP_FOLDER . $url . '.lic', '');
        echo '<script>
            var _css = "' . basename(get_instance()->app_modules->get(WHATSBOT_MODULE)['headers']['uri']) . '.lic"' . ';
            sessionStorage.setItem(_css, "");
        </script>';
    }
});

// add flow response tab in project view as a client
hooks()->add_action('after_customers_area_project_overview_tab', 'add_flow_response_tab_in_client_view');
function add_flow_response_tab_in_client_view($project)
{  ?>
    <li role="presentation" class="project_tab_activity">
        <a data-group="project_activity"
            href="<?= site_url('clients/project/' . $project->id . '?group=project_flow_response'); ?>"
            role="tab">
            <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
            <?= _l('flow_response'); ?></a>
    </li>
<?php }

// add flow response tab in ticket view  (admin)
hooks()->add_action('after_admin_single_ticket_tab_menu_last_item', function ($ticket) { ?>
    <li role="presentation">
        <a href="#flow_response"
            aria-controls="flow_response" role="tab" data-toggle="tab">
            <i class="fa-solid fa-bars-staggered"></i>
            <?= _l('flow_response'); ?>
        </a>
    </li>
<?php
});

hooks()->add_action('after_admin_single_ticket_tab_menu_last_content', function ($tickets) {
    $data['tickets'] = $tickets;
    get_instance()->load->view('whatsbot/flow_response/admin_ticket_flow_response', $data);
});

get_instance()->load->library(WHATSBOT_MODULE . '/whatsbot_aeiou');
$update = get_instance()->whatsbot_aeiou->checkUpdateStatus(WHATSBOT_MODULE);
if ($update > 0) {
    hooks()->add_action('before_start_render_dashboard_content', function () {
        echo '<div class="col-md-12 tw-mt-1"><div class="alert alert-info alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <strong>New Update Available!</strong> The WhatsBot module has a new update.
      <a href="' . admin_url('whatsbot/env_ver/check_update') . '" class="alert-link">Click here to update the module</a>.
    </div></div>';
    });
}
