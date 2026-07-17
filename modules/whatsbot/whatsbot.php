<?php

defined('BASEPATH') || exit('No direct script access allowed');
ini_set('memory_limit', '-1');

/*
    Module Name: WhatsBot
    Description: Elevate your customer relationship management and streamline your communication strategy with the power of WhatsApp
    Version: 5.0.0
    Requires at least: 3.0.*
    Author: Xetuu Limited
*/

define('WHATSBOT_MODULE', 'whatsbot');

/*
* Register language files, must be registered if the module is using languages
*/
register_language_files(WHATSBOT_MODULE, [WHATSBOT_MODULE]);

define('WHATSBOT_MODULE_UPLOAD_FOLDER', 'uploads/' . WHATSBOT_MODULE);
define('WHATSBOT_MODULE_UPLOAD_URL', base_url() . WHATSBOT_MODULE_UPLOAD_FOLDER . '/');

/*
 * Register activation module hook
 */
register_activation_hook(WHATSBOT_MODULE, 'whatsbot_module_activation_hook');
function whatsbot_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';

    $create_paths = [
        WHATSBOT_MODULE_UPLOAD_FOLDER,
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/campaign',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/template',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/csv',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/drip',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/crm_event_docs'
    ];

    array_map('_maybe_create_upload_path', $create_paths);
}

register_deactivation_hook(WHATSBOT_MODULE, 'whatsbot_module_deactivation_hook');
function whatsbot_module_deactivation_hook()
{
    $my_files_list = [
        VIEWPATH . 'themes/perfex/views/my_single_ticket.php',
        VIEWPATH . 'themes/perfex/template_parts/projects/project_flow_response.php',
        VIEWPATH . 'admin/clients/groups/my_contacts.php',
        VIEWPATH . 'admin/clients/my_all_contacts.php',
        VIEWPATH . 'admin/leads/my_manage_leads.php'
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }
}

require_once __DIR__ . '/vendor/autoload.php';

get_instance()->load->helper(WHATSBOT_MODULE . '/whatsbot');

require_once __DIR__ . '/install.php';
get_instance()->config->load(WHATSBOT_MODULE . '/config');

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

\modules\whatsbot\core\Apiinit::ease_of_mind(WHATSBOT_MODULE);
\modules\whatsbot\core\Apiinit::the_da_vinci_code(WHATSBOT_MODULE);

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
hooks()->add_filter('module_whatsbot_action_links', function ($actions) {
    $actions[] = '<a href="https://docs.corbitaltech.dev/products/whatsbot/index.html" class="text-danger" target="_blank">' . _l('help') . '</a>';

    get_instance()->load->library(WHATSBOT_MODULE . '/whatsbot_aeiou');
    $update = get_instance()->whatsbot_aeiou->checkUpdateStatus(WHATSBOT_MODULE);
    if ($update > 0) {
        $actions[] = '<a href="' . admin_url('whatsbot/env_ver/check_update') . '" class="text-warning">' . _l('check_update') . '</a>';
    }
    return $actions;
});

// add new created lead in campaign that is selected all leads
hooks()->add_action('lead_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'wtc_campaigns', ['select_all' => '1', 'rel_type' => 'leads'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = wb_get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'wtc_campaign_data', [
                'campaign_id' => $campaign['id'],
                'rel_id' => $id,
                'rel_type' => 'leads',
                'header_message' => $template['header_data_text'],
                'body_message' => $template['body_data'],
                'footer_message' => $template['footer_data'],
                'status' => 1,
            ]);
        }
    }
});

// delete campaign lead when lead deleted
hooks()->add_action('after_lead_deleted', function ($id) {
    get_instance()->db->delete(db_prefix() . 'wtc_campaign_data', ['rel_id' => $id, 'rel_type' => 'leads']);
});

// delete campaign contacts when contact deleted
hooks()->add_action('contact_deleted', function ($id, $result) {
    get_instance()->db->delete(db_prefix() . 'wtc_campaign_data', ['rel_id' => $id, 'rel_type' => 'contacts']);
}, 0, 2);

if(!function_exists('processArraySettings')){
    function processArraySettings(&$data, $settingKey){
        if (isset($data['settings'][$settingKey]) && !empty($data['settings'][$settingKey])) {
            $data['settings'][$settingKey] = implode(',', $data['settings'][$settingKey]);
        }
        if (!isset($data['settings'][$settingKey]) && empty($data['settings'][$settingKey])) {
            $data['settings'][$settingKey] = '';
        }
    }
}

hooks()->add_filter('before_settings_updated', function ($data) {
    $data['settings']['whatsapp_auto_lead_settings'] = $data['settings']['whatsapp_auto_lead_settings'] ?? '0';
    $data['settings']['enable_webhooks'] = $data['settings']['enable_webhooks'] ?? '0';
    $data['settings']['wb_enable_crm_triggers'] = $data['settings']['wb_enable_crm_triggers'] ?? '0';
    $data['settings']['enable_supportagent'] = $data['settings']['enable_supportagent'] ?? '0';
    $data['settings']['enable_wtc_notification_sound'] = $data['settings']['enable_wtc_notification_sound'] ?? '0';
    $data['settings']['enable_wb_openai'] = $data['settings']['enable_wb_openai'] ?? '0';
    $data['settings']['enable_clear_chat_history'] = $data['settings']['enable_clear_chat_history'] ?? '0';
    $data['settings']['enable_ai_assistant'] = $data['settings']['enable_ai_assistant'] ?? '0';
    $data['settings']['enable_session_management'] = $data['settings']['enable_session_management'] ?? '0';
    $data['settings']['whatsbot_catalog_sync_enabled'] = $data['settings']['whatsbot_catalog_sync_enabled'] ?? '0';
    $data['settings']['enable_typing_indicator'] = $data['settings']['enable_typing_indicator'] ?? '0';
    $data['settings']['enable_business_hours'] = $data['settings']['enable_business_hours'] ?? '0';
    $data['settings']['wb_drip_cron_batch_limit'] = $data['settings']['wb_drip_cron_batch_limit'] ?? '50';
    // Processing and converting array settings to string before saving in database
    processArraySettings($data, 'business_days');

    if (isset($data['settings']['wb_open_ai_key']) && (get_option('wb_open_ai_key') != $data['settings']['wb_open_ai_key'])) {
        get_instance()->load->model(WHATSBOT_MODULE . '/whatsbot_model');
        $response = get_instance()->whatsbot_model->connectAi($data['settings']['wb_open_ai_key']);
        if (!$response['status']) {
            set_alert('danger', $response['message']);
            return;
        }
        set_alert('success', _l('settings_updated'));
        return;
    }

    if (isset($data['settings']['opt_out_keyword']) && is_opt_keyword_exists_in_bot($data['settings']['opt_out_keyword'])) {
        set_alert('danger', _l('opt_keyword_exists'));
        return;
    }

    if (isset($data['settings']['opt_in_keyword']) && is_opt_keyword_exists_in_bot($data['settings']['opt_in_keyword'])) {
        set_alert('danger', _l('opt_keyword_exists'));
        return;
    }
    return $data;
});

// custom hook for whatsapp auto lead create if not available
hooks()->add_filter('ctl_auto_lead_creation', function ($contact_number, $name) {
    if (1 == get_option('whatsapp_auto_lead_settings')) {
        $lead_data = [
            'phonenumber' => $contact_number,
            'name' => $name,
            'status' => get_option('whatsapp_auto_leads_status') ?? 1,
            'source' => get_option('whatsapp_auto_leads_source') ?? 1,
            'assigned' => get_option('whatsapp_auto_leads_assigned') ?? 1,
            'dateadded' => date('Y-m-d H:i:s'),
            'description' => '',
            'address' => '',
            'email' => '',
        ];
        get_instance()->load->model('leads_model');

        return get_instance()->leads_model->add($lead_data);
    }

    return false;
}, 10, 2);

// add new created contact in campaign that is select all contacts
hooks()->add_action('contact_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'wtc_campaigns', ['select_all' => '1', 'rel_type' => 'contacts'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = wb_get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'wtc_campaign_data', [
                'campaign_id' => $campaign['id'],
                'rel_id' => $id,
                'rel_type' => 'contacts',
                'header_message' => $template['header_data_text'],
                'body_message' => $template['body_data'],
                'footer_message' => $template['footer_data'],
                'status' => 1,
            ]);
        }
    }
});

hooks()->add_action('after_cron_run', 'send_campaign');
hooks()->add_action('after_cron_run', 'send_webhook_message');
hooks()->add_action('after_cron_run', 'process_drip_campaigns');

function process_drip_campaigns()
{
    $CI = &get_instance();
    $CI->load->model(WHATSBOT_MODULE . '/drip_model');
    $CI->drip_model->process_pending_steps();
}
function send_campaign() {
    $scheduledData = get_instance()->db
        ->select(db_prefix() . 'wtc_campaigns.*, ' . db_prefix() . 'wtc_templates.*, ' . db_prefix() . 'wtc_campaign_data.*, ' . db_prefix() . 'wtc_campaign_data.id as campaign_data_id')
        ->join(db_prefix() . 'wtc_campaigns', db_prefix() . 'wtc_campaigns.id = ' . db_prefix() . 'wtc_campaign_data.campaign_id', 'left')
        ->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_campaigns.template_id = ' . db_prefix() . 'wtc_templates.id', 'left')
        ->where(db_prefix() . 'wtc_campaigns.scheduled_send_time <= NOW()')
        ->where(db_prefix() . 'wtc_campaigns.pause_campaign', 0)
        ->where(db_prefix() . 'wtc_campaign_data.status', 1)
        ->where(db_prefix() . 'wtc_campaigns.is_bot', 0)
        ->get(db_prefix() . 'wtc_campaign_data')->result_array();

    if (!empty($scheduledData)) {
        get_instance()->load->model(WHATSBOT_MODULE . '/whatsbot_model');
        get_instance()->whatsbot_model->send_campaign($scheduledData);
    }

    $directory = FCPATH . 'uploads/whatsbot/csv';
    if (is_dir($directory)) {
        $files = get_filenames($directory);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'html') {
                $filePath = $directory . '/' . $file;
                @unlink($filePath);
            }
        }
    }
}

function send_webhook_message()
{
    get_instance()->load->model(WHATSBOT_MODULE . '/webhook_model');
    $webhooks_logs = get_instance()->db->get_where(db_prefix() . 'wtc_webhook_logs', ['status' => 'pending'])->result_array();
    foreach ($webhooks_logs as $log) {
        // Send the webhook message
        get_instance()->webhook_model->send_webhook_message($log);
        // Update the log status
        get_instance()->db->update(db_prefix() . 'wtc_webhook_logs', ['status' => 'success', 'sendtime' => date('Y-m-d H:i:s')], ['id' => $log['id']]);
    }
}

// add widgets
hooks()->add_filter('get_dashboard_widgets', function ($widgets) {
    $new_widgets = [];
    $new_widgets[] = [
        'path' => WHATSBOT_MODULE . '/widgets/whatsapp-widget',
        'container' => 'top-12',
    ];

    return array_merge($new_widgets, $widgets);
});

hooks()->add_filter('get_dashboard_widgets', function ($widgets) {
    $widgets[] = [
        'path' => WHATSBOT_MODULE . '/widgets/session_management_widget',
        'container' => 'left-8',
    ];
    return $widgets;
});

if (!is_dir(WHATSBOT_MODULE_UPLOAD_FOLDER)) {
    if (!mkdir(WHATSBOT_MODULE_UPLOAD_FOLDER, 0755, true)) {
        exit('Failed to create directory: ' . WHATSBOT_MODULE_UPLOAD_FOLDER);
    }
    $fp = fopen(WHATSBOT_MODULE_UPLOAD_FOLDER . '/index.html', 'w');
    fclose($fp);
}

hooks()->add_filter('get_upload_path_by_type', 'add_whatsbot_files_upload_path', 0, 2);
function add_whatsbot_files_upload_path($path, $type) {
    switch ($type) {
        case 'bot_files':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files/';
            break;
        case 'campaign':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/campaign/';
            break;
        case 'template':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/template/';
            break;
        case 'csv':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/csv/';
            break;
        case 'drip':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/drip/';
            break;
        case 'crm_event_docs':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/crm_event_docs/';
            break;
        case 'flow':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/flow/';
            break;
        case 'personal_assistant':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/personal_assistant/';
            break;
        case 'product_images':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/product_images/';
            break;
        default:
            $path = $path;
            break;
    }
    return $path;
}

hooks()->add_action('item_deleted', function($id){
    $existing = get_instance()->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $id])->row();
    if(!empty($existing)){
        $msg_html = _l("deleted_exists_in_meta");
        $msg_html .= "<br><button class='btn btn-success' id='delete_meta_item_accept' data-id='".$existing->whatsapp_catalog_id."'>"._l('clients_knowledge_base_find_useful_yes')."</button>";
        $msg_html .= " <button class='btn btn-danger' id='delete_meta_item_decline' type='button'>"._l('clients_knowledge_base_find_useful_no')."</button>";
        get_instance()->session->set_userdata([
            'system-popup' => $msg_html,
        ]);
    }
});

// Marketing item as pending to sync
hooks()->add_action('after_item_updated', function($data){
    $existing = get_instance()->db->get_where(db_prefix() . 'wtc_product_metadata', ['product_id' => $data['id']])->row();
    if(!empty($existing)){
        get_instance()->db->update(db_prefix() . 'wtc_product_metadata', ['pending_sync' => 1], ['product_id' => $data['id']]);
    }
});

hooks()->add_action('before_cron_run', function ($manually) {
    if (get_option('enable_clear_chat_history') == '1') {
        $days = get_option('wb_auto_clear_time');
        $time_string = "-$days days";
        $date = date('Y-m-d H:i:s', strtotime($time_string));
        $data = get_instance()->db->get_where(db_prefix() . 'wtc_interaction_messages', ['time_sent < ' => $date])->result_array();
        if ($data) {
            get_instance()->db->delete(db_prefix() . 'wtc_interaction_messages', ['time_sent < ' => $date]);
        }
    }
});

hooks()->add_action('after_ticket_status_changed', function ($data) {
    get_instance()->load->model(['whatsbot/bots_model', 'whatsbot/whatsbot_model']);

    $ticket = get_instance()->tickets_model->get($data['id']);
    $client = get_instance()->clients_model->get($ticket->userid);
    $contact = get_instance()->clients_model->get_contact($ticket->contactid);
    $ticket->relation_type = 'ticket';
    $ticket->relation_id = $data['id'];

    get_instance()->whatsbot_model->send_flow("after_ticket_status_changed", $data, $client, $contact, $ticket);
});

hooks()->add_action('project_status_changed', function ($data) {
    get_instance()->load->model(['whatsbot/bots_model', 'whatsbot/whatsbot_model']);

    $project = get_instance()->projects_model->get($data['project_id']);
    $client = get_instance()->clients_model->get($project->clientid);
    $contact = get_instance()->clients_model->get_contact(get_primary_contact_user_id($project->clientid));
    $project->relation_type = 'project';
    $project->relation_id = $data['project_id'];

    get_instance()->whatsbot_model->send_flow("project_status_changed", $data, $client, $contact, $project);
});

// CRM Event Triggers: start
// Invoice Events
hooks()->add_action('after_invoice_added', function ($invoice_id) {
    wb_handle_crm_event('invoice_created', $invoice_id, 'invoice');
});
hooks()->add_action('invoice_updated', function ($data) {
    wb_handle_crm_event('invoice_updated', $data['id'] ?? 0, 'invoice', $data);
});
hooks()->add_action('before_invoice_deleted', function ($invoice_id) {
    wb_handle_crm_event('invoice_deleted', $invoice_id, 'invoice');
});
hooks()->add_action('after_invoice_sent', function ($invoice_id) {
    wb_handle_crm_event('invoice_sent', $invoice_id, 'invoice');
});
hooks()->add_action('invoice_status_changed', function ($data) {
    wb_handle_crm_event('invoice_status_changed', $data['invoice_id'] ?? $data['id'] ?? 0, 'invoice', $data);
});

// Projects Events
hooks()->add_action('after_add_project', function ($project_id) {
    wb_handle_crm_event('project_created', $project_id, 'project');
});
hooks()->add_action('after_update_project', function ($project_id) {
    wb_handle_crm_event('project_updated', $project_id, 'project');
});
hooks()->add_action('before_project_deleted', function ($project_id) {
    wb_handle_crm_event('project_deleted', $project_id, 'project');
});

// Payment Events
hooks()->add_action('after_payment_added', function ($payment_id) {
    wb_handle_crm_event('payment_created', $payment_id, 'payment');
});
hooks()->add_action('after_payment_updated', function ($data) {
    wb_handle_crm_event('payment_updated', $data['id'] ?? 0, 'payment', $data);
});
hooks()->add_action('before_payment_deleted', function ($data) {
    wb_handle_crm_event('payment_deleted', $data['paymentid'] ?? 0, 'payment');
});

// Credit Note Events
hooks()->add_action('after_create_credit_note', function ($credit_note_id) {
    wb_handle_crm_event('credit_note_created', $credit_note_id, 'credit_note');
});
hooks()->add_action('after_update_credit_note', function ($credit_note_id) {
    wb_handle_crm_event('credit_note_updated', $credit_note_id, 'credit_note');
});
hooks()->add_action('before_credit_note_deleted', function ($credit_note_id) {
    wb_handle_crm_event('credit_note_deleted', $credit_note_id, 'credit_note');
});
hooks()->add_action('credit_note_sent', function ($credit_note_id) {
    wb_handle_crm_event('credit_note_sent', $credit_note_id, 'credit_note');
});
hooks()->add_action('credits_applied', function ($data) {
    wb_handle_crm_event('credits_applied', $data['credit_note_id'], 'credit_note');
});

// Estimate Events
hooks()->add_action('after_estimate_added', function ($estimate_id) {
    wb_handle_crm_event('estimate_created', $estimate_id, 'estimate');
});
hooks()->add_action('after_estimate_updated', function ($estimate_id) {
    wb_handle_crm_event('estimate_updated', $estimate_id, 'estimate');
});
hooks()->add_action('before_estimate_deleted', function ($estimate_id) {
    wb_handle_crm_event('estimate_deleted', $estimate_id, 'estimate');
});
hooks()->add_action('estimate_sent', function ($estimate_id) {
    wb_handle_crm_event('estimate_sent', $estimate_id, 'estimate');
});
hooks()->add_action('estimate_accepted', function ($estimate_id) {
    wb_handle_crm_event('estimate_accepted', $estimate_id, 'estimate');
});
hooks()->add_action('estimate_declined', function ($estimate_id) {
    wb_handle_crm_event('estimate_declined', $estimate_id, 'estimate');
});

// Proposal Events
hooks()->add_action('proposal_created', function ($proposal_id) {
    wb_handle_crm_event('proposal_created', $proposal_id, 'proposal');
});
hooks()->add_action('after_proposal_updated', function ($proposal_id) {
    wb_handle_crm_event('proposal_updated', $proposal_id, 'proposal');
});
hooks()->add_action('before_proposal_deleted', function ($proposal_id) {
    wb_handle_crm_event('proposal_deleted', $proposal_id, 'proposal');
});
hooks()->add_action('proposal_sent', function ($proposal_id) {
    wb_handle_crm_event('proposal_sent', $proposal_id, 'proposal');
});
hooks()->add_action('proposal_accepted', function ($proposal_id) {
    wb_handle_crm_event('proposal_accepted', $proposal_id, 'proposal');
});
hooks()->add_action('proposal_declined', function ($proposal_id) {
    wb_handle_crm_event('proposal_declined', $proposal_id, 'proposal');
});

// Lead Events
hooks()->add_action('lead_created', function ($lead_id) {
    wb_handle_crm_event('lead_created', $lead_id, 'lead');
});
hooks()->add_action('before_lead_deleted', function ($lead_id) {
    wb_handle_crm_event('lead_deleted', $lead_id, 'lead');
});
hooks()->add_action('lead_status_changed', function ($data) {
    wb_handle_crm_event('lead_status_changed', $data['lead_id'] ?? $data['id'] ?? 0, 'lead', $data);
});
hooks()->add_action('lead_converted_to_customer', function ($data) {
    wb_handle_crm_event('lead_converted_to_customer', $data['lead_id'] ?? $data['id'] ?? 0, 'lead', $data);
});
hooks()->add_action('lead_marked_as_lost', function ($lead_id) {
    wb_handle_crm_event('lead_marked_as_lost', $lead_id, 'lead');
});
hooks()->add_action('lead_marked_as_junk', function ($lead_id) {
    wb_handle_crm_event('lead_marked_as_junk', $lead_id, 'lead');
});

// Customer Events
hooks()->add_action('after_client_created', function ($data) {
    wb_handle_crm_event('customer_created', $data['id'] ?? 0, 'customer', $data);
});
hooks()->add_action('before_client_deleted', function ($client_id) {
    wb_handle_crm_event('customer_deleted', $client_id, 'customer');
});
hooks()->add_action('contact_created', function ($contact_id) {
    wb_handle_crm_event('contact_created', $contact_id, 'contact');
});
hooks()->add_action('contact_updated', function ($contact_id) {
    wb_handle_crm_event('contact_updated', $contact_id, 'contact');
});
hooks()->add_action('before_delete_contact', function ($contact_id) {
    wb_handle_crm_event('contact_deleted', $contact_id, 'contact');
});

// Contract Events
hooks()->add_action('after_contract_added', function ($contract_id) {
    wb_handle_crm_event('contract_created', $contract_id, 'contract');
});
hooks()->add_action('after_contract_updated', function ($contract_id) {
    wb_handle_crm_event('contract_updated', $contract_id, 'contract');
});
hooks()->add_action('before_contract_deleted', function ($contract_id) {
    wb_handle_crm_event('contract_deleted', $contract_id, 'contract');
});

// Ticket Events
hooks()->add_action('ticket_created', function ($ticket_id) {
    wb_handle_crm_event('ticket_created', $ticket_id, 'ticket');
});
hooks()->add_action('before_ticket_deleted', function ($ticket_id) {
    wb_handle_crm_event('ticket_deleted', $ticket_id, 'ticket');
});
hooks()->add_action('after_ticket_status_changed', function ($data) {
    wb_handle_crm_event('ticket_status_changed', $data['id'] ?? 0, 'ticket', $data);
});
// CRM Events Triggers: End

hooks()->add_filter('leads_table_columns', function ($table_data) {
    $CI = &get_instance();
    echo $CI->load->view(WHATSBOT_MODULE . '/initiate_chat', [], true);
    $new_column = [
        'name'     => _l('initiate_chat'),
        'th_attrs' => ['class' => 'toggleable', 'id' => 'th-initiate-chat'],
    ];
    array_push($table_data, $new_column);

    return $table_data;
});

hooks()->add_filter('leads_table_row_data', function ($row, $aRow) {
    $row[] = !empty($aRow['phonenumber']) ? '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="leads" class="initiate_chat text-success"
  data-toggle="tooltip" data-title=' . _l('initiate_chat') . '><i class="fa-brands fa-whatsapp fa-lg"></i></a>' : '';
    return $row;
}, 10, 2);

hooks()->add_filter('all_contacts_table_row', function ($row, $aRow) {
    $row[] = !empty($aRow['phonenumber']) ? '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="contacts" class="initiate_chat text-success"
  data-toggle="tooltip" data-title=' . _l('initiate_chat') . '><i class="fa-brands fa-whatsapp fa-lg"></i></a>' : '';
    return $row;
}, 10, 2);

hooks()->add_filter('admin_customer_contacts_table_row', function ($row, $aRow) {
    $row[] = !empty($aRow['phonenumber']) ? '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="contacts" class="initiate_chat text-success"
  data-toggle="tooltip" data-title=' . _l('initiate_chat') . '><i class="fa-brands fa-whatsapp fa-lg"></i></a>' : '';
    return $row;
}, 10, 2);

// Add cron job for catalog synchronization
hooks()->add_action('after_cron_run', 'whatsbot_catalog_sync_cron');

/**
 * Catalog synchronization cron function
 */
function whatsbot_catalog_sync_cron() {
    $CI = &get_instance();

    // Load required models
    $CI->load->model(['whatsbot/catalog_sync_model', 'whatsbot/catalog_model', 'invoice_items_model']);

    // Get products from WhatsApp catalog
    $catalog_products = $CI->catalog_sync_model->getCatalogProducts();

    // Delete Meta details of product if it is deleted from META
    $meta_products = array_column($catalog_products['products'], "id");
    $sync_products = array_column($CI->catalog_model->get_metadata_items(),  "whatsapp_catalog_id");
    $deleted_prod = array_diff($sync_products, $meta_products);

    if(!empty($deleted_prod)){
        $CI->db->where_in('whatsapp_catalog_id', $deleted_prod);
        $CI->db->delete(db_prefix() . 'wtc_product_metadata');
    }
    // Check if automatic sync is enabled
    $sync = get_option('whatsbot_catalog_sync_enabled');
    
    if ($sync == '0') {
        return;
    }
    $sync_frequency = get_option('whatsbot_catalog_sync_frequency');
    // Get last sync time
    $last_sync = get_option('whatsbot_last_catalog_sync');
    $now = new DateTime();
    
    if ($last_sync != 'Never') {
        $last_sync_time = new DateTime($last_sync);
        $interval = $last_sync_time->diff($now);
        
        // Check if it's time to sync based on frequency
        $should_sync = false;
        
        switch ($sync_frequency) {
            case 'daily':
                // Sync if more than 24 hours have passed
                $should_sync = $interval->days >= 1;
                break;
            case 'weekly':
                // Sync if more than 7 days have passed
                $should_sync = $interval->days >= 7;
                break;
            case 'monthly':
                // Sync if more than 30 days have passed
                $should_sync = $interval->days >= 30;
                break;
        }
        
        if (!$should_sync) {
            return;
        }
    }
    
    // Get sync direction
    $sync_direction = get_option('whatsbot_catalog_sync_direction', 'bidirectional');
    
    // Perform export to WhatsApp if needed
    if ($sync_direction == 'perfex_to_whatsapp' || $sync_direction == 'bidirectional') {
        $CI->catalog_sync_model->run_automated_export();
    }
    
    // Perform import from WhatsApp if needed
    if ($sync_direction == 'whatsapp_to_perfex' || $sync_direction == 'bidirectional') {
        $CI->catalog_sync_model->run_automated_import();
    }
    
    // Update last sync time
    update_option('whatsbot_last_catalog_sync', $now->format('Y-m-d H:i:s'));
}