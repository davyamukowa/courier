<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_customers_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_customers';
    }

    public function search(string $term, ?int $branch_id = null, int $limit = 20): array
    {
        $term = $this->db->escape_like_str($term);
        $this->db->group_start()
                 ->like('name', $term)
                 ->or_like('phone', $term)
                 ->or_like('email', $term)
                 ->or_like('id_number', $term)
                 ->group_end()
                 ->limit($limit);

        if ($branch_id) {
            $this->db->group_start()
                     ->where('branch_id IS NULL')
                     ->or_where('branch_id', $branch_id)
                     ->group_end();
        }

        return $this->db->order_by('name', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }

    public function get_all(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!empty($filters['branch_id'])) {
            $this->db->group_start()
                     ->where('branch_id IS NULL')
                     ->or_where('branch_id', $filters['branch_id'])
                     ->group_end();
        }
        if (!empty($filters['search'])) {
            $term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                     ->like('name', $term)
                     ->or_like('phone', $term)
                     ->group_end();
        }
        return $this->db->order_by('name', 'ASC')
                        ->limit($limit, $offset)
                        ->get($this->table)
                        ->result_array();
    }

    public function count(array $filters = []): int
    {
        if (!empty($filters['branch_id'])) {
            $this->db->group_start()
                     ->where('branch_id IS NULL')
                     ->or_where('branch_id', $filters['branch_id'])
                     ->group_end();
        }
        return (int) $this->db->count_all_results($this->table);
    }

    public function get(int $id): ?array
    {
        return $this->db->where('id', $id)->get($this->table)->row_array() ?: null;
    }

    public function get_by_phone(string $phone): ?array
    {
        return $this->db->where('phone', $phone)->get($this->table)->row_array() ?: null;
    }

    public function create(array $data): int
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Get purchase history for a customer.
     */
    public function get_purchase_history(int $customer_id, int $limit = 20): array
    {
        return $this->db
            ->select('s.id, s.receipt_number, s.total, s.status, s.date_created, b.name AS branch_name')
            ->from(db_prefix() . 'pos_sales s')
            ->join(db_prefix() . 'pos_branches b', 'b.id = s.branch_id', 'left')
            ->where('s.customer_id', $customer_id)
            ->where_in('s.status', ['completed', 'partial_refund'])
            ->order_by('s.date_created', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    /**
     * Add loyalty points after a sale.
     */
    public function add_loyalty_points(int $customer_id, float $sale_total, float $rate = 1.0): int
    {
        $points = (int) floor($sale_total * $rate);
        if ($points <= 0) {
            return 0;
        }
        $this->db->set('loyalty_points', 'loyalty_points + ' . $points, false)
                 ->where('id', $customer_id)
                 ->update($this->table);
        return $points;
    }

    /**
     * Redeem loyalty points (returns redeemed amount in currency value).
     */
    public function redeem_points(int $customer_id, int $points, float $conversion_rate = 1.0): array
    {
        $customer = $this->get($customer_id);
        if (!$customer) {
            return ['success' => false, 'message' => 'Customer not found'];
        }

        $available = (int) $customer['loyalty_points'];
        $points    = min($points, $available);

        if ($points <= 0) {
            return ['success' => false, 'message' => 'Insufficient loyalty points'];
        }

        $this->db->set('loyalty_points', 'loyalty_points - ' . $points, false)
                 ->where('id', $customer_id)
                 ->update($this->table);

        return [
            'success'        => true,
            'points_redeemed' => $points,
            'discount_value' => round($points * $conversion_rate, 2),
        ];
    }
}
