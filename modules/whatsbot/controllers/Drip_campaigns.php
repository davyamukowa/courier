<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Drip_campaigns extends AdminController
{
    use modules\whatsbot\traits\Whatsapp;

    public function __construct()
    {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model([
            'whatsbot/drip_model',
            'whatsbot/whatsbot_model',
            'campaigns_model',
            'leads_model',
            'clients_model',
            'client_groups_model',
        ]);
    }

    public function index()
    {
        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }
        $data['title'] = _l('drip_sequences');
        $data['active_group'] = 'drip_sequences';
        $data['sequences'] = $this->drip_model->get_sequences();
        $this->load->view('drip/manage', $data);
    }

    public function sequence($id = '')
    {
        $permission = empty($id) ? 'create' : 'edit';
        if (!staff_can($permission, 'wtc_drip')) {
            access_denied();
        }

        if (!option_exists('wac_phone_numbers')) {
            $this->getPhoneNumbers();
        }

        $data['title'] = empty($id) ? _l('create_drip_sequence') : _l('edit_drip_sequence');
        $data['sequence'] = !empty($id) ? $this->drip_model->get_sequence($id) : null;
        $data['templates'] = wb_get_whatsapp_template();
        $data['rel_types'] = wb_get_campaign_rel_type();
        $data['phone_numbers'] = json_decode(get_option('wac_phone_numbers'), true) ?? [];
        $this->load->view('drip/sequence', $data);
    }

    public function get_sequences_table()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/drip_sequences_table'));
    }

    public function save()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $permission = empty($this->input->post('id')) ? 'create' : 'edit';
        if (!staff_can($permission, 'wtc_drip')) {
            access_denied();
        }

        $post = $this->input->post();
        $prepared = $this->prepare_sequence_data($post);

        if (!$prepared['status']) {
            echo json_encode($prepared);
            return;
        }

        $id = $this->drip_model->save_sequence($prepared['data']);
        if (!$id) {
            echo json_encode(['status' => false, 'message' => _l('something_went_wrong')]);
            return;
        }

        echo json_encode(['status' => true, 'id' => $id, 'message' => _l('drip_sequence_saved')]);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'wtc_drip')) {
            access_denied();
        }
        $this->drip_model->delete_sequence($id);
        set_alert('success', _l('drip_sequence_deleted'));
        redirect(admin_url('whatsbot/drip_campaigns'));
    }

    public function get_template_map()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('create', 'wtc_drip') && !staff_can('edit', 'wtc_drip')) {
            access_denied();
        }

        $template = wb_get_whatsapp_template($this->input->post('template_id'));
        $index = (int) $this->input->post('index');
        $step = [];

        if (!empty($this->input->post('step_id'))) {
            $step = $this->db->get_where(db_prefix() . 'wtc_drip_steps', ['id' => $this->input->post('step_id')])->row_array() ?? [];
        }

        $data = [
            'template' => $template,
            'step' => $step,
            'index' => $index,
            'header_params' => json_decode($step['header_params'] ?? '[]'),
            'body_params' => json_decode($step['body_params'] ?? '[]'),
            'footer_params' => json_decode($step['footer_params'] ?? '[]'),
        ];

        $view = $this->load->view('drip/step_variables', $data, true);

        echo json_encode([
            'view' => $view,
            'header_data' => $template['header_data_text'] ?? '',
            'body_data' => $template['body_data'] ?? '',
            'footer_data' => $template['footer_data'] ?? '',
            'button_data' => !empty($template['buttons_data']) ? json_decode($template['buttons_data']) : [],
            'header_data_format' => $template['header_data_format'] ?? '',
        ]);
    }

    public function enroll()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('edit', 'wtc_drip') && !staff_can('create', 'wtc_drip')) {
            access_denied();
        }

        $sequence_id = (int) $this->input->post('sequence_id');
        $sequence = $this->drip_model->get_sequence($sequence_id);
        if (!$sequence) {
            echo json_encode(['status' => false, 'message' => _l('not_found')]);
            return;
        }

        $contacts = $this->collect_enrollment_contacts($sequence['rel_type'], $this->input->post());
        if (empty($contacts)) {
            echo json_encode(['status' => false, 'message' => _l('please_select_at_least_one_lead')]);
            return;
        }

        $summary = $this->drip_model->enroll_contacts($sequence_id, $contacts, $sequence['rel_type']);
        $message = $summary['enrolled'] . ' ' . _l('contacts_enrolled');

        echo json_encode(['status' => true, 'summary' => $summary, 'message' => $message]);
    }

    public function enrollments($id)
    {
        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }

        $sequence = $this->drip_model->get_sequence($id);
        if (!$sequence) {
            show_404();
        }

        $data['title'] = _l('drip_enrollments');
        $data['sequence'] = $sequence;
        $data['enrollments'] = $this->drip_model->get_enrollments($id);
        $data['stats'] = $this->drip_model->get_sequence_stats($id);
        $data['lead_statuses'] = $this->leads_model->get_status();
        $data['lead_sources'] = $this->leads_model->get_source();
        $data['customer_groups'] = $this->client_groups_model->get_groups();
        $data['rel_records'] = $sequence['rel_type'] === 'leads'
            ? $this->leads_model->get()
            : $this->clients_model->get_contacts('', ['active' => 1]);

        $this->load->view('drip/enrollments', $data);
    }

    public function get_enrollments_table($sequence_id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/drip_enrollments_table'), ['sequence_id' => $sequence_id]);
    }

    public function get_enrollment_records($sequence_id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }

        $sequence = $this->drip_model->get_sequence($sequence_id);
        if (!$sequence) {
            echo json_encode([]);
            return;
        }

        echo json_encode($this->collect_enrollment_contacts($sequence['rel_type'], $this->input->post(), true));
    }

    public function get_enrollment_details($id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        if (!staff_can('view', 'wtc_drip')) {
            access_denied();
        }

        $enrollment = $this->drip_model->get_enrollments_details($id);
        if (!$enrollment) {
            echo json_encode([
                'status' => false,
                'message' => _l('no_enrollment_found'),
            ]);
            return;
        }

        echo json_encode([
                'status' => true,
                'message' => _l('enrollment_found'),
                'enrollment' => $enrollment,
            ]);
    }

    public function pause_enrollment($id)
    {
        if (!staff_can('edit', 'wtc_drip')) {
            access_denied();
        }
        $this->drip_model->pause_enrollment($id);
        echo json_encode(['status' => true]);
    }

    public function resume_enrollment($id)
    {
        if (!staff_can('edit', 'wtc_drip')) {
            access_denied();
        }
        $this->drip_model->resume_enrollment($id);
        echo json_encode(['status' => true]);
    }

    public function exit_enrollment($id)
    {
        if (!staff_can('edit', 'wtc_drip')) {
            access_denied();
        }
        $this->drip_model->exit_enrollment($id, 'manual');
        echo json_encode(['status' => true]);
    }

    public function toggle_active($id)
    {
        if (!staff_can('edit', 'wtc_drip')) {
            access_denied();
        }
        $seq = $this->db->get_where(db_prefix() . 'wtc_drip_sequences', ['id' => $id])->row();
        if (!$seq) {
            echo json_encode(['status' => false]);
            return;
        }
        $new_status = $seq->is_active ? 0 : 1;
        $this->db->update(db_prefix() . 'wtc_drip_sequences', ['is_active' => $new_status], ['id' => $id]);
        echo json_encode(['status' => true, 'is_active' => $new_status]);
    }

    public function get_table_data($table)
    {
        if (!$this->input->is_ajax_request()) {
            return false;
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/' . $table));
    }

    private function prepare_sequence_data($post)
    {
        $steps = $post['steps'] ?? [];
        if (empty($post['name']) || empty($steps)) {
            return ['status' => false, 'message' => _l('missing_required_fields')];
        }

        $sequence_data = [
            'name' => $post['name'],
            'description' => $post['description'] ?? '',
            'rel_type' => $post['rel_type'] ?? 'leads',
            'sender_phone' => $post['sender_phone'] ?? null,
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];

        if (!empty($post['id'])) {
            $sequence_data['id'] = (int) $post['id'];
        }

        $prepared_steps = [];
        foreach ($steps as $index => $step) {
            if (($step['message_type'] ?? 'template') === 'text') {
                $prepared_steps[] = [
                    'id' => $step['id'] ?? null,
                    'delay_value' => $step['delay_value'] ?? 1,
                    'delay_unit' => $step['delay_unit'] ?? 'hours',
                    'message_type' => 'text',
                    'template_id' => null,
                    'message_data' => !empty($step['message_data']) ? json_decode($step['message_data'], true) : [],
                    'header_params' => [],
                    'body_params' => [],
                    'footer_params' => [],
                    'filename' => $step['existing_filename'] ?? '',
                ];
                continue;
            }

            $template_id = (int) ($step['template_id'] ?? 0);
            $template = $template_id ? wb_get_whatsapp_template($template_id) : [];
            if (!$template) {
                return ['status' => false, 'message' => _l('select_template')];
            }

            $header_format = $template['header_data_format'] ?? '';
            $existing_filename = $step['existing_filename'] ?? '';
            $upload = $this->handle_step_media_upload($index, $header_format);
            if (!$upload['status']) {
                return $upload;
            }

            $filename = $upload['filename'] ?: $existing_filename;
            if (in_array($header_format, ['IMAGE', 'VIDEO', 'DOCUMENT'], true) && empty($filename)) {
                return ['status' => false, 'message' => _l('missing_required_fields') . ': ' . _l(strtolower($header_format))];
            }

            $param_validation = $this->validate_params($template, $step);
            if (!$param_validation['status']) {
                return $param_validation;
            }

            $prepared_steps[] = [
                'id' => $step['id'] ?? null,
                'delay_value' => $step['delay_value'] ?? 1,
                'delay_unit' => $step['delay_unit'] ?? 'hours',
                'message_type' => 'template',
                'template_id' => $template_id,
                'header_params' => $step['header_params'] ?? [],
                'body_params' => $step['body_params'] ?? [],
                'footer_params' => $step['footer_params'] ?? [],
                'filename' => $filename,
            ];
        }

        $sequence_data['steps'] = $prepared_steps;

        return ['status' => true, 'data' => $sequence_data];
    }

    private function validate_params($template, $step)
    {
        foreach (['header', 'body', 'footer'] as $type) {
            $count = (int) ($template[$type . '_params_count'] ?? 0);
            if ($count <= 0) {
                continue;
            }

            for ($i = 1; $i <= $count; $i++) {
                if (empty($step[$type . '_params'][$i]['value'])) {
                    return ['status' => false, 'message' => _l('missing_required_fields') . ': ' . $type . ' ' . $i];
                }
            }
        }

        return ['status' => true];
    }

    private function handle_step_media_upload($index, $header_format)
    {
        $field = strtolower($header_format);
        if (!in_array($field, ['image', 'video', 'document'], true)) {
            return ['status' => true, 'filename' => ''];
        }

        if (empty($_FILES['steps']['name'][$index][$field])) {
            return ['status' => true, 'filename' => ''];
        }

        $allowed = wb_get_allowed_extension();
        $file = [
            'name' => $_FILES['steps']['name'][$index][$field],
            'type' => $_FILES['steps']['type'][$index][$field],
            'tmp_name' => $_FILES['steps']['tmp_name'][$index][$field],
            'error' => $_FILES['steps']['error'][$index][$field],
            'size' => $_FILES['steps']['size'][$index][$field],
        ];

        if (!empty($file['error'])) {
            return ['status' => false, 'message' => _l('something_went_wrong')];
        }

        $max_size = ((float) $allowed[$field]['size']) * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return ['status' => false, 'message' => _l('maximum_file_size_should_be') . ' ' . $allowed[$field]['size'] . ' MB'];
        }

        $extension = '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array_map('trim', explode(',', strtolower($allowed[$field]['extension'])));
        if (!in_array($extension, $allowed_extensions, true)) {
            return ['status' => false, 'message' => _l('file_php_extension_blocked')];
        }

        $path = get_upload_path_by_type('drip');
        _maybe_create_upload_path($path);
        $new_file_name = str_replace(' ', '_', $file['name']);
        $filename = unique_filename($path, $new_file_name);

        if (!_upload_extension_allowed($filename)) {
            return ['status' => false, 'message' => _l('file_php_extension_blocked')];
        }

        if (!move_uploaded_file($file['tmp_name'], $path . $filename)) {
            return ['status' => false, 'message' => _l('something_went_wrong')];
        }

        return ['status' => true, 'filename' => $filename];
    }

    private function collect_enrollment_contacts($rel_type, $post, $for_select = false)
    {
        $records = [];

        if ($rel_type === 'leads') {
            $where = [];
            if (!empty($post['status'])) {
                $where['status'] = $post['status'];
            }
            if (!empty($post['source'])) {
                $where['source'] = $post['source'];
            }

            if (!empty($post['select_all']) || $for_select) {
                $leads = $this->leads_model->get('', $where);
            } else {
                $ids = $post['rel_ids'] ?? [];
                $leads = [];
                foreach ((array) $ids as $id) {
                    $lead = $this->leads_model->get($id);
                    if ($lead) {
                        $leads[] = $lead;
                    }
                }
            }

            foreach ($leads as $lead) {
                $records[] = [
                    'id' => is_array($lead) ? $lead['id'] : $lead->id,
                    'name' => is_array($lead) ? $lead['name'] : $lead->name,
                    'phone' => is_array($lead) ? ($lead['phonenumber'] ?? '') : ($lead->phonenumber ?? ''),
                    'is_opted_out' => is_array($lead) ? ($lead['is_opted_out'] ?? 0) : ($lead->is_opted_out ?? 0),
                ];
            }
        } else {
            if (!empty($post['select_all']) || $for_select) {
                if (!empty($post['groups'])) {
                    $groups = is_array($post['groups']) ? $post['groups'] : json_decode($post['groups'], true);
                    $contacts = $this->campaigns_model->get_contacts_where_group($groups ?: []);
                } else {
                    $contacts = $this->clients_model->get_contacts('', ['active' => 1]);
                }
            } else {
                $ids = $post['rel_ids'] ?? [];
                $contacts = [];
                foreach ((array) $ids as $id) {
                    $contact = $this->clients_model->get_contact($id);
                    if ($contact) {
                        $contacts[] = $contact;
                    }
                }
            }

            foreach ($contacts as $contact) {
                $records[] = [
                    'id' => is_array($contact) ? $contact['id'] : $contact->id,
                    'name' => trim((is_array($contact) ? ($contact['firstname'] ?? '') : ($contact->firstname ?? '')) . ' ' . (is_array($contact) ? ($contact['lastname'] ?? '') : ($contact->lastname ?? ''))),
                    'phone' => is_array($contact) ? ($contact['phonenumber'] ?? '') : ($contact->phonenumber ?? ''),
                    'is_opted_out' => is_array($contact) ? ($contact['is_opted_out'] ?? 0) : ($contact->is_opted_out ?? 0),
                ];
            }
        }

        return $for_select ? array_values($records) : $records;
    }
}
