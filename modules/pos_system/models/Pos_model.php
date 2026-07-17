<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─── Dashboard aggregates ────────────────────────────────────────────────

    public function get_today_totals(?int $branch_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_sales')) {
            return ['count' => 0, 'total' => 0];
        }

        $this->db->select('COUNT(*) AS count, COALESCE(SUM(total),0) AS total')
                 ->where('status', 'completed')
                 ->where('DATE(date_created)', date('Y-m-d'));

        if ($branch_id) {
            $this->db->where('branch_id', $branch_id);
        }

        return (array) $this->db->get(db_prefix() . 'pos_sales')->row();
    }

    public function get_period_report(int $branch_id, string $from, string $to): array
    {
        $row = $this->db
            ->select('
                COUNT(*) AS sale_count,
                COALESCE(SUM(s.subtotal),0)                                                    AS net_revenue,
                COALESCE(SUM(s.tax_amount),0)                                                  AS tax_collected,
                COALESCE(SUM(s.discount_amount),0)                                             AS total_discounts,
                COALESCE(SUM(CASE WHEN s.status=\'refunded\' THEN s.total ELSE 0 END),0)       AS refunds
            ')
            ->from(db_prefix() . 'pos_sales s')
            ->where('s.branch_id', $branch_id)
            ->where('DATE(s.date_created) >=', $from)
            ->where('DATE(s.date_created) <=', $to)
            ->where_in('s.status', ['completed', 'refunded', 'partial_refund'])
            ->get()
            ->row_array();

        if (!$row) {
            return ['sale_count' => 0, 'net_revenue' => 0, 'cogs' => 0,
                    'gross_profit' => 0, 'tax_collected' => 0, 'total_discounts' => 0];
        }

        // COGS from sale items
        $cogs_row = $this->db
            ->select('COALESCE(SUM(si.cost_price * si.quantity), 0) AS cogs')
            ->from(db_prefix() . 'pos_sale_items si')
            ->join(db_prefix() . 'pos_sales s', 's.id = si.sale_id')
            ->where('s.branch_id', $branch_id)
            ->where('DATE(s.date_created) >=', $from)
            ->where('DATE(s.date_created) <=', $to)
            ->where_in('s.status', ['completed', 'partial_refund'])
            ->get()
            ->row_array();

        $row['cogs']         = (float) ($cogs_row['cogs'] ?? 0);
        $row['gross_profit'] = (float) $row['net_revenue'] - $row['cogs'];

        return $row;
    }

    // ─── Settings ────────────────────────────────────────────────────────────

    public function get_all_settings(?int $branch_id = null): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_settings')) {
            return [];
        }

        $this->db->where('branch_id IS NULL');
        if ($branch_id) {
            $this->db->or_where('branch_id', $branch_id);
        }
        $rows    = $this->db->get(db_prefix() . 'pos_settings')->result();
        $settings = [];
        foreach ($rows as $row) {
            // Branch-level overrides global
            $settings[$row->setting_key] = $row->setting_value;
        }
        return $settings;
    }

    public function save_settings(array $data, ?int $branch_id = null): void
    {
        foreach ($data as $key => $value) {
            pos_set_setting($key, $value, $branch_id);
        }
    }
}
