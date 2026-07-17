<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_reports_model
 *
 * All report queries. Each method returns a raw array ready for JSON or PDF rendering.
 * Performance: uses aggregate SQL; avoids N+1; larger reports support pagination.
 */
class Pos_reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─── 1. Daily Sales ───────────────────────────────────────────────────────

    public function daily_sales(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                DATE(s.date_created)                              AS sale_date,
                COUNT(*)                                          AS transaction_count,
                COALESCE(SUM(s.subtotal), 0)                      AS gross_subtotal,
                COALESCE(SUM(s.discount_amount), 0)               AS total_discounts,
                COALESCE(SUM(s.tax_amount), 0)                    AS total_tax,
                COALESCE(SUM(s.total), 0)                         AS gross_sales,
                COALESCE(SUM(CASE WHEN s.status IN ('refunded','partial_refund') THEN s.total ELSE 0 END), 0) AS refunds,
                COALESCE(SUM(s.total) - SUM(CASE WHEN s.status IN ('refunded','partial_refund') THEN s.total ELSE 0 END), 0) AS net_sales,
                COUNT(DISTINCT s.customer_id)                     AS unique_customers
            FROM `" . db_prefix() . "pos_sales` s
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status != 'void'
            GROUP BY DATE(s.date_created)
            ORDER BY sale_date ASC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 2. Sales by Product ──────────────────────────────────────────────────

    public function sales_by_product(int $branch_id, string $from, string $to, int $limit = 50): array
    {
        return $this->db->query("
            SELECT
                p.id                       AS product_id,
                p.name                     AS product_name,
                p.sku,
                c.name                     AS category_name,
                SUM(si.quantity)           AS qty_sold,
                SUM(si.line_total)         AS revenue,
                SUM(si.cost_price * si.quantity) AS cogs,
                SUM(si.line_total) - SUM(si.cost_price * si.quantity) AS gross_profit,
                CASE WHEN SUM(si.line_total) > 0
                     THEN ROUND((SUM(si.line_total) - SUM(si.cost_price * si.quantity)) / SUM(si.line_total) * 100, 2)
                     ELSE 0 END            AS margin_pct
            FROM `" . db_prefix() . "pos_sale_items` si
            JOIN `" . db_prefix() . "pos_sales` s  ON s.id = si.sale_id
            JOIN `" . db_prefix() . "pos_products` p ON p.id = si.product_id
            LEFT JOIN `" . db_prefix() . "pos_product_categories` c ON c.id = p.category_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void', 'refunded')
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT ?
        ", [$branch_id, $from, $to, $limit])->result_array();
    }

    // ─── 3. Sales by Cashier ──────────────────────────────────────────────────

    public function sales_by_cashier(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                s.staff_id,
                CONCAT(st.firstname, ' ', st.lastname)  AS cashier_name,
                COUNT(s.id)                             AS transaction_count,
                COALESCE(SUM(s.total), 0)               AS total_sales,
                COALESCE(AVG(s.total), 0)               AS avg_sale_value,
                COALESCE(SUM(s.discount_amount), 0)     AS total_discounts,
                COUNT(CASE WHEN s.status = 'refunded' THEN 1 END) AS refund_count
            FROM `" . db_prefix() . "pos_sales` s
            JOIN `" . db_prefix() . "staff` st ON st.staffid = s.staff_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status != 'void'
            GROUP BY s.staff_id
            ORDER BY total_sales DESC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 4. Profit & Loss per Branch ──────────────────────────────────────────

    public function profit_and_loss(int $branch_id, string $from, string $to): array
    {
        $revenue = $this->db->query("
            SELECT
                COALESCE(SUM(si.line_total), 0)                           AS net_revenue,
                COALESCE(SUM(si.tax_amount), 0)                           AS tax_collected,
                COALESCE(SUM(si.cost_price * si.quantity), 0)             AS cogs,
                COALESCE(SUM(si.line_total) - SUM(si.cost_price * si.quantity), 0) AS gross_profit
            FROM `" . db_prefix() . "pos_sale_items` si
            JOIN `" . db_prefix() . "pos_sales` s ON s.id = si.sale_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void','refunded')
        ", [$branch_id, $from, $to])->row_array();

        $discounts = $this->db->query("
            SELECT COALESCE(SUM(discount_amount), 0) AS total_discounts
            FROM `" . db_prefix() . "pos_sales`
            WHERE branch_id = ? AND DATE(date_created) BETWEEN ? AND ? AND status != 'void'
        ", [$branch_id, $from, $to])->row()->total_discounts ?? 0;

        return array_merge($revenue ?? [], [
            'total_discounts' => $discounts,
            'net_profit'      => (float) ($revenue['gross_profit'] ?? 0) - (float) $discounts,
            'from'            => $from,
            'to'              => $to,
            'branch_id'       => $branch_id,
        ]);
    }

    // ─── 5. Payment Method Breakdown ─────────────────────────────────────────

    public function payment_method_breakdown(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                pm.name                 AS method_name,
                pm.type                 AS method_type,
                COUNT(p.id)             AS transaction_count,
                COALESCE(SUM(p.amount), 0) AS total_amount
            FROM `" . db_prefix() . "pos_payments` p
            JOIN `" . db_prefix() . "pos_payment_methods` pm ON pm.id = p.payment_method_id
            JOIN `" . db_prefix() . "pos_sales` s ON s.id = p.sale_id
            WHERE p.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND p.status = 'completed'
            GROUP BY pm.id
            ORDER BY total_amount DESC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 6. Hourly Sales Distribution ────────────────────────────────────────

    public function hourly_distribution(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                HOUR(s.date_created)       AS hour_of_day,
                COUNT(*)                   AS transaction_count,
                COALESCE(SUM(s.total), 0)  AS total_sales
            FROM `" . db_prefix() . "pos_sales` s
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void')
            GROUP BY HOUR(s.date_created)
            ORDER BY hour_of_day
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 7. Category Performance ─────────────────────────────────────────────

    public function category_performance(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                c.id                       AS category_id,
                c.name                     AS category_name,
                COUNT(DISTINCT s.id)       AS sale_count,
                SUM(si.quantity)           AS qty_sold,
                SUM(si.line_total)         AS revenue,
                SUM(si.cost_price * si.quantity) AS cogs
            FROM `" . db_prefix() . "pos_sale_items` si
            JOIN `" . db_prefix() . "pos_sales` s ON s.id = si.sale_id
            JOIN `" . db_prefix() . "pos_products` p ON p.id = si.product_id
            LEFT JOIN `" . db_prefix() . "pos_product_categories` c ON c.id = p.category_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void','refunded')
            GROUP BY c.id
            ORDER BY revenue DESC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 8. Session Summary ───────────────────────────────────────────────────

    public function session_summary(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                sess.id,
                sess.opened_at,
                sess.closed_at,
                CONCAT(st.firstname,' ',st.lastname) AS cashier_name,
                sess.opening_float,
                sess.closing_float,
                sess.expected_cash,
                sess.actual_cash,
                sess.cash_difference,
                sess.total_sales_amount,
                sess.total_sales_count,
                TIMESTAMPDIFF(MINUTE, sess.opened_at, COALESCE(sess.closed_at, NOW())) AS duration_minutes
            FROM `" . db_prefix() . "pos_sessions` sess
            JOIN `" . db_prefix() . "staff` st ON st.staffid = sess.staff_id
            WHERE sess.branch_id = ?
              AND DATE(sess.opened_at) BETWEEN ? AND ?
            ORDER BY sess.opened_at DESC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 9. Top Customers ────────────────────────────────────────────────────

    public function top_customers(int $branch_id, string $from, string $to, int $limit = 10): array
    {
        return $this->db->query("
            SELECT
                c.id, c.name, c.phone,
                COUNT(s.id)               AS purchase_count,
                SUM(s.total)              AS total_spent,
                AVG(s.total)              AS avg_order_value,
                c.loyalty_points
            FROM `" . db_prefix() . "pos_sales` s
            JOIN `" . db_prefix() . "pos_customers` c ON c.id = s.customer_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void')
            GROUP BY c.id
            ORDER BY total_spent DESC
            LIMIT ?
        ", [$branch_id, $from, $to, $limit])->result_array();
    }

    // ─── 10. Tax Report ───────────────────────────────────────────────────────

    public function tax_report(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                t.name                      AS tax_name,
                t.rate                      AS tax_rate,
                COUNT(si.id)                AS line_count,
                SUM(si.line_total)          AS taxable_amount,
                SUM(si.tax_amount)          AS tax_collected
            FROM `" . db_prefix() . "pos_sale_items` si
            JOIN `" . db_prefix() . "pos_sales` s ON s.id = si.sale_id
            JOIN `" . db_prefix() . "pos_products` p ON p.id = si.product_id
            LEFT JOIN `" . db_prefix() . "pos_tax_rates` t ON t.id = p.tax_rate_id
            WHERE s.branch_id = ?
              AND DATE(s.date_created) BETWEEN ? AND ?
              AND s.status NOT IN ('void','refunded')
            GROUP BY t.id
            ORDER BY tax_collected DESC
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── 11. Multi-branch comparison (admin) ──────────────────────────────────

    public function branch_comparison(string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                b.id AS branch_id, b.name AS branch_name, b.code, b.currency,
                COUNT(s.id)                AS transaction_count,
                COALESCE(SUM(s.total), 0)  AS total_sales,
                COALESCE(AVG(s.total), 0)  AS avg_sale,
                COALESCE(SUM(s.tax_amount), 0) AS tax_collected
            FROM `" . db_prefix() . "pos_branches` b
            LEFT JOIN `" . db_prefix() . "pos_sales` s
                   ON s.branch_id = b.id
                  AND DATE(s.date_created) BETWEEN ? AND ?
                  AND s.status NOT IN ('void')
            WHERE b.is_active = 1
            GROUP BY b.id
            ORDER BY total_sales DESC
        ", [$from, $to])->result_array();
    }
}
