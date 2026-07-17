<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Analytics_model extends App_Model
{

    /**
     * Campaign Analytics: Stats per campaign
     */
    public function get_all_campaigns_stats($date_from = null, $date_to = null)
    {
        $where_date = '';
        if ($date_from) {
            $where_date .= " AND c.created_at >= '" . $this->db->escape_str($date_from) . "'";
        }
        if ($date_to) {
            $next_day = date('Y-m-d', strtotime($date_to . ' +1 day'));
            $where_date .= " AND c.created_at < '" . $next_day . "'";
        }
        $sql = "SELECT c.id, c.name, c.created_at,
                    t.template_name,
                    COUNT(cd.id) as total,
                    SUM(CASE WHEN cd.message_status IN ('sent','delivered','read') THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN cd.message_status IN ('delivered','read') THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN cd.message_status = 'read' THEN 1 ELSE 0 END) as msg_read,
                    SUM(CASE WHEN cd.message_status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM " . db_prefix() . "wtc_campaigns c
                LEFT JOIN " . db_prefix() . "wtc_campaign_data cd ON cd.campaign_id = c.id
                LEFT JOIN " . db_prefix() . "wtc_templates t ON t.id = c.template_id
                WHERE c.is_bot = 0
                {$where_date}
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        return $this->db->query($sql)->result_array();
    }

    /**
     * Campaign Analytics: Overall messaging stats
     */
    public function get_overall_messaging_stats($date_from = null, $date_to = null)
    {
        $where_date = '';
        if ($date_from) {
            $where_date .= " AND c.created_at >= '" . $this->db->escape_str($date_from) . "'";
        }
        if ($date_to) {
            $next_day = date('Y-m-d', strtotime($date_to . ' +1 day'));
            $where_date .= " AND c.created_at < '" . $next_day . "'";
        }

        $sql = "SELECT
                    COUNT(cd.id) as total,
                    SUM(CASE WHEN cd.message_status IN ('sent','delivered','read') THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN cd.message_status IN ('delivered','read') THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN cd.message_status = 'read' THEN 1 ELSE 0 END) as msg_read,
                    SUM(CASE WHEN cd.message_status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM " . db_prefix() . "wtc_campaigns c
                LEFT JOIN " . db_prefix() . "wtc_campaign_data cd ON cd.campaign_id = c.id
                WHERE c.is_bot = 0 {$where_date}";

        return $this->db->query($sql)->row_array();
    }

    /**
     * Campaign Analytics: Template performance ranking
     */
    public function get_template_performance($date_from = null, $date_to = null)
    {
        $where_date = '';
        if ($date_from) {
            $where_date .= " AND cd.created_at >= '" . $this->db->escape_str($date_from) . "'";
        }
        if ($date_to) {
            $next_day = date('Y-m-d', strtotime($date_to . ' +1 day'));
            $where_date .= " AND cd.created_at < '" . $next_day . "'";
        }

        $sql = "SELECT t.template_name, t.category,
                       COUNT(cd.id) as total_sent,
                       SUM(CASE WHEN cd.message_status = 'read' THEN 1 ELSE 0 END) as total_read,
                       ROUND(SUM(CASE WHEN cd.message_status = 'read' THEN 1 ELSE 0 END) / COUNT(cd.id) * 100, 1) as read_rate
                FROM " . db_prefix() . "wtc_campaign_data cd
                JOIN " . db_prefix() . "wtc_campaigns c ON c.id = cd.campaign_id
                JOIN " . db_prefix() . "wtc_templates t ON t.id = c.template_id
                WHERE 1=1 {$where_date}
                GROUP BY t.id
                HAVING total_sent > 0
                ORDER BY read_rate DESC
                LIMIT 10";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Campaign Analytics: Daily message volume
     */
    public function get_daily_message_volume($date_from = null, $date_to = null)
    {
        $where_date = '';
        if ($date_from) $where_date .= " AND created_at >= '{$this->db->escape_str($date_from)}'";
        if ($date_to) $where_date .= " AND created_at <= '{$this->db->escape_str($date_to)}'";

        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM " . db_prefix() . "wtc_campaign_data
                WHERE 1=1 {$where_date}
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Overall stats for dashboard
     */
    public function get_overall_stats($date_from = null, $date_to = null)
    {
        $where_date = $this->build_date_where('time_sent', $date_from, $date_to);
        $total_conversations = $this->db->query("SELECT COUNT(*) as cnt FROM " . db_prefix() . "wtc_interactions WHERE 1=1 {$where_date}")->row()->cnt;
        $total_messages = $this->db->query("SELECT COUNT(*) as cnt FROM " . db_prefix() . "wtc_interaction_messages WHERE 1=1 {$where_date}")->row()->cnt;

        $active_agents = $this->db->query("SELECT COUNT(DISTINCT staff_id) as cnt FROM " . db_prefix() . "wtc_interaction_messages WHERE staff_id IS NOT NULL AND staff_id != '' AND staff_id != '0' {$where_date}")->row()->cnt;

        return [
            'total_conversations' => $total_conversations,
            'total_messages' => $total_messages,
            'active_agents' => $active_agents,
        ];
    }

    private function build_date_where($column, $date_from = null, $date_to = null)
    {
        $where = '';
        if ($date_from) $where .= " AND {$column} >= '{$this->db->escape_str($date_from)}'";
        if ($date_to) $where .= " AND {$column} <= '{$this->db->escape_str($date_to)}'";
        return $where;
    }
}
