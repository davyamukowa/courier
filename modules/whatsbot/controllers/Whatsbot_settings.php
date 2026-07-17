<?php

defined('BASEPATH') || exit('No direct script access allowed');
class Whatsbot_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('settings_model');
    }

    public function index()
    {
        if (staff_cant('view', 'wtc_settings')) {
            access_denied('settings');
        }

        $group = $this->input->get('group');
        $this->load->model('whatsbot/webhook_model');

        if ($this->input->post()) {

            if (staff_cant('edit', 'wtc_settings')) {
                access_denied('settings');
            }

            $post_data = $this->input->post();
            hooks()->do_action('before_update_system_options', $post_data);

            $webhook_receive = $post_data['settings']['webhook_receive'];
            unset($post_data['settings']['webhook_receive']);

            $this->webhook_model->insert_update($webhook_receive);
            $success = $this->settings_model->update($post_data);

            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            }

            $redUrl = admin_url(WHATSBOT_MODULE . '/whatsbot_settings?group=whatsbot');

            if ($this->input->get('active_tab')) {
                $redUrl .= '&tab=' . $this->input->get('active_tab');
            }

            redirect($redUrl);
        }

        $this->load->model('taxes_model');
        $this->load->model('tickets_model');
        $this->load->model('leads_model');
        $this->load->model('currencies_model');
        $this->load->model('staff_model');
        $data['taxes']                                   = $this->taxes_model->get();
        $data['ticket_priorities']                       = $this->tickets_model->get_priority();
        $data['ticket_priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['roles']                                   = $this->roles_model->get();
        $data['leads_sources']                           = $this->leads_model->get_source();
        $data['leads_statuses']                          = $this->leads_model->get_status();
        $data['title']                                   = _l('options');
        $data['staff']                                   = $this->staff_model->get('', ['active' => 1]);

        $data['admin_tabs'] = ['update', 'info'];

        $data['group']['id']       = $group;
        $data['group']['view']     = 'admin/settings/includes/' . $group;
        $data['group']['name']     = $group === 'info' ? ' System/Server Info' : _l('settings_update');
        $data['group']['children'] = [];
        if ($group === 'info') {
            $data['group']['without_submit_button'] = true;
        }

        $data['webhook_sources']   = $this->webhook_model->get();

        $data['group'] = 'whatsbot';
        if (! $data['group']) {
            show_404();
        }

        $data['contacts_permissions'] = get_contact_permissions();

        $this->load->view('settings/whatsbot_settings', $data);
    }

    public function delete_webhook_source($id)
    {
        if (!$id) {
            redirect(admin_url('whatsbot/whatsbot_settings?group=whatsbot&tab=webhooks_receive'));
        }
        $this->load->model('whatsbot/webhook_model');
        $success = $this->webhook_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('webhook')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('webhook')));
        }
        redirect(admin_url('whatsbot/whatsbot_settings?group=whatsbot&tab=webhooks_receive'));
    }

    public function get_webhook_source_data($id = null){
        $this->load->model('whatsbot/webhook_model');
        $res = $this->webhook_model->get($id);
        echo json_encode($res);
    }
}

