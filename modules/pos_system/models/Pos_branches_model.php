<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_branches_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_branches';
    }

    public function get_all(array $filters = []): array
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        if (isset($filters['is_active'])) {
            $this->db->where('is_active', $filters['is_active']);
        }
        return $this->db->order_by('name', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }

    public function get(int $id): ?array
    {
        $row = $this->db->where('id', $id)->get($this->table)->row_array();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $data['code'] = strtoupper($data['code']);
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        return (bool) $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete(int $id): bool
    {
        // Protected (auto-created company) branches can never be deleted
        $branch = $this->db->select('is_protected')->where('id', $id)->get($this->table)->row();
        if ($branch && (int)$branch->is_protected === 1) {
            return false;
        }
        // Soft delete — never hard delete branches with transaction history
        return (bool) $this->db->where('id', $id)->set('is_active', 0)->update($this->table);
    }

    public function get_staff(int $branch_id): array
    {
        return $this->db
            ->select('s.staffid as id, s.firstname, s.lastname, s.email, sb.role, sb.is_default')
            ->from(db_prefix() . 'staff s')
            ->join(db_prefix() . 'pos_staff_branches sb', 'sb.staff_id = s.staffid')
            ->where('sb.branch_id', $branch_id)
            ->get()
            ->result_array();
    }

    public function assign_staff(int $branch_id, int $staff_id, string $role = 'cashier', bool $is_default = false): bool
    {
        $exists = $this->db
            ->where('branch_id', $branch_id)
            ->where('staff_id', $staff_id)
            ->get(db_prefix() . 'pos_staff_branches')
            ->row();

        if ($exists) {
            return (bool) $this->db
                ->where('branch_id', $branch_id)
                ->where('staff_id', $staff_id)
                ->update(db_prefix() . 'pos_staff_branches', ['role' => $role, 'is_default' => (int) $is_default]);
        }

        if ($is_default) {
            $this->db->where('staff_id', $staff_id)
                     ->set('is_default', 0)
                     ->update(db_prefix() . 'pos_staff_branches');
        }

        return (bool) $this->db->insert(db_prefix() . 'pos_staff_branches', [
            'branch_id'  => $branch_id,
            'staff_id'   => $staff_id,
            'role'       => $role,
            'is_default' => (int) $is_default,
        ]);
    }

    public function remove_staff(int $branch_id, int $staff_id): bool
    {
        return (bool) $this->db
            ->where('branch_id', $branch_id)
            ->where('staff_id', $staff_id)
            ->delete(db_prefix() . 'pos_staff_branches');
    }
}
