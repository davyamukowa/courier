<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Bots_model extends App_Model {
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public function __construct() {
        parent::__construct();
    }

    public function saveBots($data) {
        if (isset($data['sections'])) {
            $section = [];
            $section['action'] = $data['action'];
            $section['sections'] = $data['sections'];
            unset($data['action'], $data['sections']);
            $data['sections'] = json_encode($section);
        }
        unset($data['bot_file'], $data['file_type']);
        $insert = $update = false;

        if (empty($data['id'])) {
            $data['addedfrom'] = get_staff_user_id();
            $insert = $this->db->insert(db_prefix().'wtc_bot', $data);
            $bot_id = $this->db->insert_id();
        } else {
            $update = $this->db->update(db_prefix().'wtc_bot', $data, ['id' => $data['id']]);
            $bot_id = $data['id'];
        }

        return [
            'type' => ($insert || $update) ? 'success' : 'danger',
            'message' => ($insert) ? _l('bot_create_successfully') : ($update ? _l('bot_update_successfully') : _l('something_went_wrong')),
            'id' => $bot_id,
        ];
    }

    public function getMessageBot($id = '') {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_bot', ['id' => $id])->row_array();
        }

        return $this->db->get(db_prefix() . 'wtc_bot')->result_array();
    }

    public function deleteMessageBot($type, $id) {
        $message = _l('something_went_wrong');
        $status = false;

        $bot = ('template' == $type) ? $this->bots_model->getTemplateBot($id) : $this->bots_model->getMessageBot($id);
        $table = ('template' == $type) ? 'wtc_campaigns' : 'wtc_bot';

        $this->db->delete(db_prefix() . $table, ['id' => $id]);

        if ($this->db->affected_rows() > 0) {
            $status = true;
            $dir_name = ('template' == $type) ? 'template' : 'bot_files';
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/' . $dir_name . '/' . $bot['filename'];
            if (file_exists($path) && !is_dir($path)) {
                unlink($path);
            }
            $message = _l('bot_deleted_successfully');
        }

        return [
            'type' => 'danger',
            'message' => $message,
            'status' => $status
        ];
    }

    public function saveTemplateBot($data) {
        unset($data['document']);
        $data['header_params'] = json_encode($data['header_params'] ?? []);
        $data['body_params'] = json_encode($data['body_params'] ?? []);
        $data['footer_params'] = json_encode($data['footer_params'] ?? []);

        $insert = $update = false;
        if (empty($data['id'])) {
            $insert = $this->db->insert(db_prefix().'wtc_campaigns', $data);
            $bot_id = $this->db->insert_id();
        } else {
            $update = $this->db->update(db_prefix().'wtc_campaigns', $data, ['id' => $data['id']]);
            $bot_id = $data['id'];
        }

        return [
            'type' => $insert || $update ? 'success' : 'danger',
            'message' => ($insert) ? _l('template_bot_create_successfully') : ($update ? _l('template_bot_update_successfully') : _l('something_went_wrong')),
            'temp_id' => $bot_id,
        ];
    }

    public function getTemplateBot($id = '') {
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_campaigns', ['id' => $id])->row_array();
        }

        return $this->db->get_where(db_prefix() . 'wtc_campaigns', ['is_bot' => '1'])->result_array();
    }

    public function getTemplateBotsByRelType($relType, $message, $botType = null, $webhook_id = null) {
        if (!is_null($botType)) {
            $this->db->where("bot_type", $botType);
        }

        if (!is_null($webhook_id)) {
            $this->db->where("webhook_id", $webhook_id);
        }

        if (!empty($message)) {
            $this->db->select(db_prefix() . 'wtc_campaigns.id AS campaign_table_id, ' . db_prefix() . 'wtc_campaigns.*, ' . db_prefix() . 'wtc_templates.*');
            $messageWords = explode(' ', $message);

            $this->db->group_start();
            foreach ($messageWords as $value) {
                $value = str_replace(["'", "\""], "", $value);
                $this->db->or_where("FIND_IN_SET(" . $this->db->escape($value) . ", `trigger`) >", 0);
            }
            $this->db->or_where("FIND_IN_SET(" .  $this->db->escape($message) . ", `trigger`) >", 0);
            $this->db->group_end();
        }

        $this->db->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_campaigns.template_id = ' . db_prefix() . 'wtc_templates.id', 'left');
        $data = $this->db->get_where(db_prefix() . 'wtc_campaigns', ['rel_type' => $relType, 'is_bot' => 1, 'is_bot_active' => 1]);

        if ($data->num_rows() == 0 && $botType != 4) {
            return $this->getTemplateBotsByRelType($relType, '', 4);
        }

        return $data->result_array();
    }

    public function getMessageBotsByRelType($relType, $message, $replyType = null, $webhook_id = null) {
        if (!is_null($replyType)) {
            $this->db->where("reply_type", $replyType);
        }

        if (!is_null($webhook_id)) {
            $this->db->where("webhook_id", $webhook_id);
        }

        if (!empty($message)) {
            $messageWords = explode(' ', $message);

            $this->db->group_start();
            foreach ($messageWords as $value) {
                $value = str_replace(["'", "\""], "", $value);
                $this->db->or_where("FIND_IN_SET('$value', `trigger`) >", 0);
            }
            $this->db->or_where("FIND_IN_SET('$message', `trigger`) >", 0);
            $this->db->group_end();
        }

        $data = $this->db->get_where(db_prefix() . 'wtc_bot', ['rel_type' => $relType, 'is_bot_active' => 1]);

        if ($data->num_rows() == 0 && $replyType != 4) {
            return $this->getMessageBotsByRelType($relType, '', 4);
        }

        return $data->result_array();
    }

    public function change_active_status($type, $id, $status) {
        if ('message' == $type) {
            return $this->db->update(db_prefix() . 'wtc_bot', ['is_bot_active' => $status], ['id' => $id]);
        } elseif ('template' == $type) {
            return $this->db->update(db_prefix() . 'wtc_campaigns', ['is_bot_active' => $status], ['id' => $id, 'is_bot' => 1]);
        } elseif ('bot_flow' == $type) {
            return $this->db->update(db_prefix() . 'wtc_bot_flow', ['is_active' => $status], ['id' => $id]);
        }
    }

    public function update_sending_count($table, $count, $where) {
        return $this->db->update($table, ['sending_count' => $count], $where);
    }

    public function delete_bot_files($id) {
        $bot = $this->getMessageBot($id);

        $update = $this->db->update(db_prefix() . 'wtc_bot', ['filename' => null], ['id' => $id]);
        $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files/' . $bot['filename'];
        if ($update && file_exists($path)) {
            unlink($path);
        }

        return [
            'message' => ($update) ? _l('deleted', _l('file')) : _l('something_went_wrong'),
        ];
    }

    public function clone_bot($type, $id) {
        if ($type == 'text') {
            $bot_data = $this->getMessageBot($id);
            $bot_data['id'] = '';
            if (!empty($bot_data['filename'])) {
                $new_file_name = time() . '.' . pathinfo($bot_data['filename'], PATHINFO_EXTENSION);
                $bot_data['filename'] = copy(WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files/' . $bot_data['filename'], WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files/' . $new_file_name) ? $new_file_name : '';
            }
            $clone_bot = $this->saveBots($bot_data);
        } else {
            $bot_data = $this->getTemplateBot($id);
            $bot_data['id'] = '';
            if (!empty($bot_data['filename'])) {
                $new_file_name = time() . '.' . pathinfo($bot_data['filename'], PATHINFO_EXTENSION);
                $bot_data['filename'] = copy(WHATSBOT_MODULE_UPLOAD_FOLDER . '/template/' . $bot_data['filename'], WHATSBOT_MODULE_UPLOAD_FOLDER . '/template/' . $new_file_name) ? $new_file_name : '';
            }
            $clone_bot = $this->saveTemplateBot($bot_data);
        }
        return [
            'id' => $type == 'template' ? $clone_bot['temp_id'] : $clone_bot['id'],
            'type' => $clone_bot ? 'success' : 'danger',
            'message' => $clone_bot ? _l('bot_clone_successfully') : _l('something_went_wrong')
        ];
    }

    public function save_flow($post_data) {
        if (!empty($post_data['id'])) {
            return $this->db->update(db_prefix() . 'wtc_bot_flow', $post_data, ['id' => $post_data['id']]);
        }
        return $this->db->insert(db_prefix() . 'wtc_bot_flow', $post_data);
    }

    public function get_flow($id = '', $like_flow = "") {
        if (!empty($like_flow)) {
            $this->db->like("flow_data", $like_flow, 'both', false);
        }
        if (!empty($id)) {
            return $this->db->get_where(db_prefix() . 'wtc_bot_flow', ['id' => $id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_bot_flow')->result_array();
    }


    public function get_flows($relType, $message, $is_first_time) {
        $data = $this->db->get_where(db_prefix() . 'wtc_bot_flow', ['is_active' => 1]);
        $flows = $data->result_array();
        $msg_arr = [];

        foreach ($flows as &$flow) {
            $map = json_decode($flow['flow_data'], true);

            $nodes = collect($map['nodes'])->mapWithKeys(function ($node) {
                return [$node["id"] => ["data" => $node["data"]['output'][0], "type" => $node['type']]];
            })->toArray();

            $connections = collect($map['edges'])->mapToGroups(function ($edge) {
                return [$edge["source"] => ["target" => $edge["target"], "handle" => $edge["sourceHandle"]]];
            })->toArray();

            $msg_mapping = collect($map['nodes'])->filter(function ($box) use ($relType, $message) {
                return $box['type'] == "start" && $box['data']['output'][0]['rel_type'] == $relType;
            })->toArray();

            $this->prepare_msg_array($msg_mapping, $nodes, $connections, $flow, $message, $msg_arr);
        }
        return $msg_arr;
    }

    public function prepare_msg_array($msg_mapping, $nodes, $connections, $flow, $message, &$msg_arr) {
        foreach ($msg_mapping as $start) {
            $start_data = $nodes[$start['id']]['data'];
            $start_data['id'] = $flow['id'];
            $start_data['sending_count'] = $flow['sending_count'] ?? 0;
            $start_data['bot_header'] = "";
            $start_data['bot_footer'] = "";
            $start_data['reply_text'] = "";
            $start_data['button1'] = "";
            $start_data['button2'] = "";
            $start_data['button3'] = "";
            $start_data['filename'] = "";
            if (!empty($connections[$start['id']])) {
                foreach ($connections[$start['id']] as $output_details) {
                    $output_id = $output_details['target'];
                    $output_data = $nodes[$output_id]['data'];
                    $output_data['option'] = 1;
                    if (!empty($output_details['handle'])) {
                        $btn_id = str_replace("source-", "", $output_details['handle']);
                        $output_data['trigger'] = "flow_" . $flow['id'] . "_output_" . $start['parent_id'] . "_node_" . $start['id'] . "_btn" . $btn_id;
                        if ($btn_id == "4") {
                            $output_data['trigger'] = $nodes[$start['parent_id']]['data']['trigger'];
                            $output_data['reply_type'] = $nodes[$start['parent_id']]['data']['reply_type'];
                            $output_data['rel_type'] = $nodes[$start['parent_id']]['data']['rel_type'];
                        }
                    }
                    if ($nodes[$output_id]['type'] == "imageMessage") {
                        $output_data['option'] = 4;
                        $output_data['filename'] = $flow['id'] . "/" . rawurlencode($output_data['imageUrl']);
                    }
                    if ($nodes[$output_id]['type'] == "videoMessage") {
                        $output_data['option'] = 4;
                        $output_data['filename'] = $flow['id'] . "/" . rawurlencode($output_data['videoUrl']);
                    }
                    if ($nodes[$output_id]['type'] == "document") {
                        $output_data['option'] = 4;
                        $output_data['filename'] = $flow['id'] . "/" . rawurlencode($output_data['documentName']);
                    }
                    if ($nodes[$output_id]['type'] == "audioMessage") {
                        $output_data['option'] = 4;
                        $output_data['filename'] = $flow['id'] . "/" . rawurlencode($output_data['audioUrl']);
                    }
                    if ($nodes[$output_id]['type'] == "cta") {
                        $output_data['option'] = 3;
                    }
                    if ($nodes[$output_id]['type'] == "locationMessage") {
                        // Extract location data and prepare for sending
                        $output_data['locationName'] = $nodes[$output_id]['data']['locationName'] ?? 'Location';
                        $output_data['address'] = $nodes[$output_id]['data']['address'] ?? '';
                        $output_data['latitude'] = $nodes[$output_id]['data']['latitude'] ?? '';
                        $output_data['longitude'] = $nodes[$output_id]['data']['longitude'] ?? '';
                    }
                    if ($nodes[$output_id]['type'] == "buttonsMessage") {
                        $output_data['option'] = 2;
                        if (!empty($output_data['button1'])) {
                            $output_data['button1_id'] = "flow_" . $flow['id'] . "_output_" . $start['id'] . "_node_" . $output_id . "_btn1";
                        }
                        if (!empty($output_data['button2'])) {
                            $output_data['button2_id'] = "flow_" . $flow['id'] . "_output_" . $start['id'] . "_node_" . $output_id . "_btn2";
                        }
                        if (!empty($output_data['button3'])) {
                            $output_data['button3_id'] = "flow_" . $flow['id'] . "_output_" . $start['id'] . "_node_" . $output_id . "_btn3";
                        }
                    }
                    if ($nodes[$output_id]['type'] == "aiResponse") {
                        $output_data['option'] = 1;
                        $output_data['reply_text'] = $this->getAIAnswer($output_data['personal_assistants'], $message);
                        $output_data['bot_footer'] = $output_data['ai_footer'];
                    }
                    $msg_arr[] = array_merge($start_data, $output_data);
                    $new_msg_mapping = [];
                    if (!empty($connections[$output_id])) {
                        $new_msg_mapping[] = ["id" => $output_id, "parent_id" => $start['id']];
                        $nodes[$output_id]['data'] = $start_data;
                        $nodes[$output_id]['data']['reply_type'] = 1;
                        $this->prepare_msg_array($new_msg_mapping, $nodes, $connections, $flow, $message, $msg_arr);
                    }
                }
            }
        }
    }
}
