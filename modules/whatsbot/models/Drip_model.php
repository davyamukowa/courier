<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Drip_model extends App_Model
{
    use modules\whatsbot\traits\Whatsapp;

    private const MAX_FAILURES = 3;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsbot/whatsbot_model');
        $this->load->model('whatsbot/interaction_model');
    }

    public function save_sequence($data)
    {
        $steps = $data['steps'] ?? [];
        unset($data['steps']);

        $existing_steps = [];
        if (!empty($data['id'])) {
            foreach ($this->get_steps($data['id']) as $step) {
                $existing_steps[$step['id']] = $step;
            }
        }

        $this->db->trans_start();

        if (!empty($data['id'])) {
            $id = (int) $data['id'];
            unset($data['id']);
            $this->db->update(db_prefix() . 'wtc_drip_sequences', $data, ['id' => $id]);
            $this->db->delete(db_prefix() . 'wtc_drip_steps', ['sequence_id' => $id]);
        } else {
            $data['created_by'] = get_staff_user_id();
            $this->db->insert(db_prefix() . 'wtc_drip_sequences', $data);
            $id = $this->db->insert_id();
        }

        $kept_files = [];
        $ordinal = 1;
        foreach ($steps as $step) {
            $filename = $step['filename'] ?? '';
            if (!empty($filename)) {
                $kept_files[] = $filename;
            }

            $this->db->insert(db_prefix() . 'wtc_drip_steps', [
                'sequence_id' => $id,
                'step_number' => $ordinal++,
                'delay_value' => max(1, (int) ($step['delay_value'] ?? 1)),
                'delay_unit' => $step['delay_unit'] ?? 'hours',
                'message_type' => $step['message_type'] ?? 'template',
                'template_id' => !empty($step['template_id']) ? (int) $step['template_id'] : null,
                'header_params' => json_encode($step['header_params'] ?? []),
                'body_params' => json_encode($step['body_params'] ?? []),
                'footer_params' => json_encode($step['footer_params'] ?? []),
                'filename' => $filename ?: null,
                'message_data' => !empty($step['message_data']) ? json_encode($step['message_data']) : null,
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        foreach ($existing_steps as $old_step) {
            if (!empty($old_step['filename']) && !in_array($old_step['filename'], $kept_files, true)) {
                $this->delete_step_file($old_step['filename']);
            }
        }

        $this->refresh_active_enrollment_schedule($id);

        return $id;
    }

    public function get_sequence($id)
    {
        $sequence = $this->db->get_where(db_prefix() . 'wtc_drip_sequences', ['id' => $id])->row_array();
        if ($sequence) {
            $sequence['steps'] = $this->get_steps($id);
        }
        return $sequence;
    }

    public function get_steps($sequence_id)
    {
        return $this->db->order_by('step_number', 'asc')
            ->get_where(db_prefix() . 'wtc_drip_steps', ['sequence_id' => $sequence_id])
            ->result_array();
    }

    public function get_sequences()
    {
        return $this->db->get(db_prefix() . 'wtc_drip_sequences')->result_array();
    }

    public function delete_sequence($id)
    {
        $steps = $this->get_steps($id);

        $this->db->delete(db_prefix() . 'wtc_drip_log', [
            'enrollment_id IN (SELECT id FROM ' . db_prefix() . 'wtc_drip_enrollments WHERE sequence_id = ' . (int) $id . ')' => null
        ]);
        $this->db->delete(db_prefix() . 'wtc_drip_enrollments', ['sequence_id' => $id]);
        $this->db->delete(db_prefix() . 'wtc_drip_steps', ['sequence_id' => $id]);
        $this->db->delete(db_prefix() . 'wtc_drip_sequences', ['id' => $id]);

        foreach ($steps as $step) {
            if (!empty($step['filename'])) {
                $this->delete_step_file($step['filename']);
            }
        }

        return true;
    }

    public function enroll_contacts($sequence_id, $contacts, $rel_type)
    {
        $summary = [
            'selected' => count($contacts),
            'enrolled' => 0,
            'already_enrolled' => 0,
            'invalid_phone' => 0,
            'opted_out' => 0,
            'missing' => 0,
        ];

        $sequence = $this->get_sequence($sequence_id);
        if (!$sequence || $sequence['rel_type'] !== $rel_type) {
            $summary['message'] = _l('missing_required_fields');
            return $summary;
        }

        foreach ($contacts as $contact) {
            if (empty($contact['id'])) {
                $summary['missing']++;
                continue;
            }

            if (!empty($contact['is_opted_out'])) {
                $summary['opted_out']++;
                continue;
            }

            $phone = normalizePhoneNumber($contact['phone'] ?? '');
            if (empty($phone)) {
                $summary['invalid_phone']++;
                continue;
            }

            $exists = $this->db->get_where(db_prefix() . 'wtc_drip_enrollments', [
                'sequence_id' => $sequence_id,
                'rel_id' => $contact['id'],
                'rel_type' => $rel_type,
            ])->row();

            if ($exists) {
                $summary['already_enrolled']++;
                continue;
            }

            $next_send_at = $this->calculate_next_send_at((int) $sequence_id, 1, date('Y-m-d H:i:s'));
            $status = $next_send_at ? 'active' : 'completed';

            $this->db->insert(db_prefix() . 'wtc_drip_enrollments', [
                'sequence_id' => $sequence_id,
                'rel_id' => $contact['id'],
                'rel_type' => $rel_type,
                'phone_number' => $phone,
                'current_step' => 0,
                'status' => $status,
                'next_send_at' => $next_send_at,
                'completed_at' => $next_send_at ? null : date('Y-m-d H:i:s'),
            ]);
            $summary['enrolled']++;
        }

        return $summary;
    }

    /**
     * Process due drip steps. Called by cron.
     */
    public function process_pending_steps()
    {
        $this->release_stale_processing_enrollments();

        $limit = (int) get_option('wb_drip_cron_batch_limit');
        $limit = $limit > 0 ? $limit : 50;

        $due_ids = $this->db->select('e.id')
            ->from(db_prefix() . 'wtc_drip_enrollments e')
            ->join(db_prefix() . 'wtc_drip_sequences s', 's.id = e.sequence_id')
            ->where('e.status', 'active')
            ->where('s.is_active', 1)
            ->group_start()
                ->where('e.next_send_at <=', date('Y-m-d H:i:s'))
                ->or_where('e.next_send_at IS NULL', null, false)
            ->group_end()
            ->order_by('e.next_send_at', 'asc')
            ->order_by('e.id', 'asc')
            ->limit($limit)
            ->get()->result_array();

        if (empty($due_ids)) {
            return true;
        }

        $ids = array_column($due_ids, 'id');
        $this->db->where_in('id', $ids);
        $this->db->where('status', 'active');
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'processing',
            'processing_started_at' => date('Y-m-d H:i:s'),
        ]);

        $enrollments = $this->db->select('e.*, s.sender_phone, s.is_active as seq_active')
            ->from(db_prefix() . 'wtc_drip_enrollments e')
            ->join(db_prefix() . 'wtc_drip_sequences s', 's.id = e.sequence_id')
            ->where_in('e.id', $ids)
            ->where('e.status', 'processing')
            ->order_by('e.next_send_at', 'asc')
            ->order_by('e.id', 'asc')
            ->get()->result_array();

        foreach ($enrollments as $enrollment) {
            $this->process_enrollment($enrollment);
        }

        return true;
    }

    private function process_enrollment($enrollment)
    {
        $next_step_number = (int) $enrollment['current_step'] + 1;
        $step = $this->get_step((int) $enrollment['sequence_id'], $next_step_number);

        if (!$step) {
            $this->complete_enrollment($enrollment['id']);
            return;
        }

        if (empty($enrollment['next_send_at'])) {
            $next_send_at = $this->calculate_next_send_at((int) $enrollment['sequence_id'], $next_step_number, $enrollment['last_step_sent_at'] ?: $enrollment['enrolled_at']);
            if ($next_send_at && strtotime($next_send_at) > time()) {
                $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
                    'status' => 'active',
                    'next_send_at' => $next_send_at,
                    'processing_started_at' => null,
                ], ['id' => $enrollment['id']]);
                return;
            }
        }

        if ($this->is_rel_opted_out($enrollment)) {
            $this->exit_enrollment($enrollment['id'], 'opted_out');
            return;
        }

        $result = $this->send_drip_step($enrollment, $step);
        $status = !empty($result['status']) ? 'sent' : 'failed';
        $message_id = $result['data']->messages[0]->id ?? '';
        $response_code = $result['response_code'] ?? ($result['log_data']['response_code'] ?? '');
        $error_message = $result['message'] ?? '';

        $this->db->insert(db_prefix() . 'wtc_drip_log', [
            'enrollment_id' => $enrollment['id'],
            'step_number' => $next_step_number,
            'status' => $status,
            'sent_at' => date('Y-m-d H:i:s'),
            'whatsapp_message_id' => $message_id,
            'response_code' => $response_code,
            'error_message' => $error_message,
        ]);

        if ($status === 'sent') {
            $this->mark_step_sent($enrollment, $next_step_number);
            return;
        }

        $this->handle_failed_step($enrollment, $error_message);
    }

    private function send_drip_step($enrollment, $step)
    {
        $phone = $enrollment['phone_number'];

        if ($step['message_type'] === 'template' && !empty($step['template_id'])) {
            $template = $this->db->get_where(db_prefix() . 'wtc_templates', ['id' => $step['template_id']])->row_array();
            if (!$template) {
                return ['status' => false, 'message' => 'Template not found'];
            }

            $template_data = array_merge($template, [
                'rel_type' => $enrollment['rel_type'],
                'rel_id' => $enrollment['rel_id'],
                'header_message' => $template['header_data_text'] ?? '',
                'body_message' => $template['body_data'] ?? '',
                'footer_message' => $template['footer_data'] ?? '',
                'header_params' => $step['header_params'] ?: '[]',
                'body_params' => $step['body_params'] ?: '[]',
                'footer_params' => $step['footer_params'] ?: '[]',
                'filename' => $step['filename'] ?? '',
                'campaign_table_id' => $enrollment['sequence_id'],
            ]);

            if ($enrollment['rel_type'] === 'contacts') {
                $contact = $this->db->get_where(db_prefix() . 'contacts', ['id' => $enrollment['rel_id']])->row_array();
                if ($contact) {
                    $template_data['id'] = $contact['id'];
                    $template_data['userid'] = $contact['userid'];
                }
            }

            return $this->sendTemplate($phone, $template_data, 'drip', $enrollment['sender_phone'] ?? null);
        }

        if ($step['message_type'] === 'text' && !empty($step['message_data'])) {
            $msg_data = json_decode($step['message_data'], true);
            $msg_data['option'] = $msg_data['option'] ?? '';
            $msg_data['bot_header'] = $msg_data['bot_header'] ?? '';
            $msg_data['bot_footer'] = $msg_data['bot_footer'] ?? '';
            $msg_data['rel_type'] = $enrollment['rel_type'];
            $msg_data['rel_id'] = $enrollment['rel_id'];
            $msg_data['id'] = $enrollment['sequence_id'];

            return $this->sendMessage($phone, $msg_data, $enrollment['sender_phone'] ?? null);
        }

        return ['status' => false, 'message' => 'Invalid step configuration'];
    }

    private function mark_step_sent($enrollment, $sent_step_number)
    {
        $now = date('Y-m-d H:i:s');
        $next_step_number = $sent_step_number + 1;
        $next_send_at = $this->calculate_next_send_at((int) $enrollment['sequence_id'], $next_step_number, $now);

        $update = [
            'current_step' => $sent_step_number,
            'last_step_sent_at' => $now,
            'failure_count' => 0,
            'last_error' => null,
            'processing_started_at' => null,
        ];

        if ($next_send_at) {
            $update['status'] = 'active';
            $update['next_send_at'] = $next_send_at;
        } else {
            $update['status'] = 'completed';
            $update['next_send_at'] = null;
            $update['completed_at'] = $now;
        }

        $this->db->update(db_prefix() . 'wtc_drip_enrollments', $update, ['id' => $enrollment['id']]);
    }

    private function handle_failed_step($enrollment, $error_message)
    {
        $failure_count = (int) $enrollment['failure_count'] + 1;

        if ($failure_count >= self::MAX_FAILURES) {
            $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
                'status' => 'exited',
                'completed_at' => date('Y-m-d H:i:s'),
                'exit_reason' => 'failed',
                'failure_count' => $failure_count,
                'last_error' => $error_message,
                'next_send_at' => null,
                'processing_started_at' => null,
            ], ['id' => $enrollment['id']]);
            return;
        }

        $backoff_minutes = 15 * $failure_count;
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'active',
            'failure_count' => $failure_count,
            'last_error' => $error_message,
            'next_send_at' => date('Y-m-d H:i:s', strtotime('+' . $backoff_minutes . ' minutes')),
            'processing_started_at' => null,
        ], ['id' => $enrollment['id']]);
    }

    public function refresh_active_enrollment_schedule($sequence_id)
    {
        $enrollments = $this->db->get_where(db_prefix() . 'wtc_drip_enrollments', [
            'sequence_id' => $sequence_id,
            'status' => 'active',
        ])->result_array();

        foreach ($enrollments as $enrollment) {
            $next_step_number = (int) $enrollment['current_step'] + 1;
            $reference_time = $enrollment['last_step_sent_at'] ?: $enrollment['enrolled_at'];
            $next_send_at = $this->calculate_next_send_at((int) $sequence_id, $next_step_number, $reference_time);
            $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
                'next_send_at' => $next_send_at,
            ], ['id' => $enrollment['id']]);
        }
    }

    private function calculate_next_send_at($sequence_id, $step_number, $reference_time)
    {
        $step = $this->get_step($sequence_id, $step_number);
        if (!$step) {
            return null;
        }

        $reference_timestamp = strtotime($reference_time ?: date('Y-m-d H:i:s'));
        return date('Y-m-d H:i:s', $reference_timestamp + $this->get_delay_seconds($step['delay_value'], $step['delay_unit']));
    }

    private function get_step($sequence_id, $step_number)
    {
        return $this->db->get_where(db_prefix() . 'wtc_drip_steps', [
            'sequence_id' => $sequence_id,
            'step_number' => $step_number,
        ])->row_array();
    }

    private function get_delay_seconds($value, $unit)
    {
        $value = max(1, (int) $value);
        switch ($unit) {
            case 'minutes':
                return $value * 60;
            case 'hours':
                return $value * 3600;
            case 'days':
                return $value * 86400;
            default:
                return $value * 3600;
        }
    }

    private function release_stale_processing_enrollments()
    {
        $this->db->where('status', 'processing');
        $this->db->group_start();
            $this->db->where('processing_started_at IS NULL', null, false);
            $this->db->or_where('processing_started_at <', date('Y-m-d H:i:s', strtotime('-15 minutes')));
        $this->db->group_end();
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'active',
            'processing_started_at' => null,
        ]);
    }

    private function complete_enrollment($enrollment_id)
    {
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'next_send_at' => null,
            'processing_started_at' => null,
        ], ['id' => $enrollment_id]);
    }

    private function is_rel_opted_out($enrollment)
    {
        $rel_table = ($enrollment['rel_type'] === 'leads') ? 'leads' : 'contacts';
        $contact = $this->db->get_where(db_prefix() . $rel_table, ['id' => $enrollment['rel_id']])->row();
        return $contact && !empty($contact->is_opted_out) && (int) $contact->is_opted_out === 1;
    }

    public function exit_enrollment($enrollment_id, $reason = 'manual')
    {
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'exited',
            'completed_at' => date('Y-m-d H:i:s'),
            'exit_reason' => $reason,
            'next_send_at' => null,
            'processing_started_at' => null,
        ], ['id' => $enrollment_id]);
    }

    public function pause_enrollment($enrollment_id)
    {
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'paused',
            'processing_started_at' => null,
        ], ['id' => $enrollment_id, 'status !=' => 'completed']);
    }

    public function resume_enrollment($enrollment_id)
    {
        $enrollment = $this->db->get_where(db_prefix() . 'wtc_drip_enrollments', ['id' => $enrollment_id])->row_array();
        if (!$enrollment || $enrollment['status'] !== 'paused') {
            return false;
        }

        if (empty($enrollment['next_send_at'])) {
            $next_step_number = (int) $enrollment['current_step'] + 1;
            $enrollment['next_send_at'] = $this->calculate_next_send_at((int) $enrollment['sequence_id'], $next_step_number, $enrollment['last_step_sent_at'] ?: date('Y-m-d H:i:s'));
        }

        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'active',
            'next_send_at' => $enrollment['next_send_at'],
        ], ['id' => $enrollment_id]);
        return true;
    }

    public function exit_enrollment_by_phone($phone, $reason = 'user_replied')
    {
        $this->db->where('phone_number', $phone);
        $this->db->where('status', 'active');
        $this->db->update(db_prefix() . 'wtc_drip_enrollments', [
            'status' => 'exited',
            'completed_at' => date('Y-m-d H:i:s'),
            'exit_reason' => $reason,
            'next_send_at' => null,
            'processing_started_at' => null,
        ]);
    }

    public function get_enrollments($sequence_id)
    {
        return $this->db->order_by('id', 'desc')
            ->get_where(db_prefix() . 'wtc_drip_enrollments', ['sequence_id' => $sequence_id])
            ->result_array();
    }

    public function get_enrollments_details($id = null)
    {
        // Base query
        $this->db->select('e.*, steps_tbl.total_steps');
        $this->db->from(db_prefix() . 'wtc_drip_enrollments as e');

        $this->db->join(
            '(SELECT sequence_id, COUNT(*) as total_steps 
            FROM ' . db_prefix() . 'wtc_drip_steps 
            GROUP BY sequence_id) as steps_tbl',
            'steps_tbl.sequence_id = e.sequence_id',
            'left'
        );

        if (!empty($id)) {
            $this->db->where('e.id', $id);
            $enrollment = $this->db->get()->row_array();

            if (!$enrollment) {
                return null;
            }

            // Get relation data safely
            $rel_data = $this->get_rel_data(
                $enrollment['rel_type'] ?? null,
                $enrollment['rel_id'] ?? null
            );

            // Logs
            $logs = $this->db
                ->order_by('sent_at', 'asc')
                ->get_where(db_prefix() . 'wtc_drip_log', ['enrollment_id' => $id])
                ->result_array();

            return [
                'enrollment' => $enrollment,
                'rel_data'   => $rel_data,
                'logs'       => $logs,
            ];
        }

        $enrollments = $this->db->get()->result_array();

        foreach ($enrollments as &$enrollment) {
            $enrollment['rel_data'] = $this->get_rel_data(
                $enrollment['rel_type'] ?? null,
                $enrollment['rel_id'] ?? null
            );
        }

        return [
            'enrollments' => $enrollments
        ];
    }

    private function get_rel_data($type, $id)
    {
        if (empty($type) || empty($id)) {
            return null;
        }

        if ($type == 'leads') {
            return $this->db
                ->get_where(db_prefix() . 'leads', ['id' => $id])
                ->row_array();
        }

        if ($type == 'contacts') {
            return $this->db
                ->get_where(db_prefix() . 'contacts', ['id' => $id])
                ->row_array();
        }

        return null;
    }

    public function get_sequence_stats($sequence_id)
    {
        $total = $this->db->where('sequence_id', $sequence_id)->count_all_results(db_prefix() . 'wtc_drip_enrollments');
        $active = $this->db->where(['sequence_id' => $sequence_id, 'status' => 'active'])->count_all_results(db_prefix() . 'wtc_drip_enrollments');
        $paused = $this->db->where(['sequence_id' => $sequence_id, 'status' => 'paused'])->count_all_results(db_prefix() . 'wtc_drip_enrollments');
        $completed = $this->db->where(['sequence_id' => $sequence_id, 'status' => 'completed'])->count_all_results(db_prefix() . 'wtc_drip_enrollments');
        $exited = $this->db->where(['sequence_id' => $sequence_id, 'status' => 'exited'])->count_all_results(db_prefix() . 'wtc_drip_enrollments');

        return compact('total', 'active', 'paused', 'completed', 'exited');
    }

    private function delete_step_file($filename)
    {
        $path = FCPATH . get_upload_path_by_type('drip') . $filename;
        if (!empty($filename) && file_exists($path) && !is_dir($path)) {
            @unlink($path);
        }
    }
}
