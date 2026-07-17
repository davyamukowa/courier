<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_sessions_model extends App_Model
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'pos_sessions';
    }

    /**
     * Find the open session for a given staff member + branch.
     */
    public function get_open_session(int $staff_id, int $branch_id): ?array
    {
        if (!$this->db->table_exists($this->table)) {
            return null;
        }

        return $this->db
            ->where('staff_id', $staff_id)
            ->where('branch_id', $branch_id)
            ->where('status', 'open')
            ->order_by('opened_at', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row_array() ?: null;
    }

    public function get(int $id): ?array
    {
        $session = $this->db->where('id', $id)->get($this->table)->row_array();
        if ($session) {
            $session['cash_movements'] = $this->get_cash_movements((int) $session['id']);
        }
        return $session ?: null;
    }

    public function open(int $branch_id, int $staff_id, float $opening_float, string $terminal_id = ''): array
    {
        $existing = $this->get_open_session($staff_id, $branch_id);
        if ($existing) {
            return ['success' => false, 'message' => 'A session is already open', 'session' => $existing];
        }

        $data = [
            'session_uid'   => pos_uuid(),
            'branch_id'     => $branch_id,
            'staff_id'      => $staff_id,
            'terminal_id'   => $terminal_id,
            'status'        => 'open',
            'opening_float' => $opening_float,
            'opened_at'     => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        $session_id = $this->db->insert_id();

        return ['success' => true, 'session_id' => $session_id, 'session' => $this->get($session_id)];
    }

    public function close(int $session_id, float $actual_cash, string $notes = ''): array
    {
        $session = $this->get($session_id);
        if (!$session || $session['status'] !== 'open') {
            return ['success' => false, 'message' => 'Session not found or already closed'];
        }

        // Compute expected cash: opening float + cash sales + cash in - cash out
        $cash_sales = $this->db
            ->select('COALESCE(SUM(p.amount),0) AS total')
            ->from(db_prefix() . 'pos_payments p')
            ->join(db_prefix() . 'pos_payment_methods pm', 'pm.id = p.payment_method_id')
            ->where('p.session_id', $session_id)
            ->where('pm.type', 'cash')
            ->where('p.status', 'completed')
            ->get()
            ->row()->total ?? 0;

        $cash_movements = $this->db
            ->select("
                COALESCE(SUM(CASE WHEN type='in'  THEN amount ELSE 0 END),0) AS total_in,
                COALESCE(SUM(CASE WHEN type='out' THEN amount ELSE 0 END),0) AS total_out
            ")
            ->where('session_id', $session_id)
            ->get(db_prefix() . 'pos_cash_movements')
            ->row_array();

        $expected_cash = (float) $session['opening_float']
            + (float) $cash_sales
            + (float) ($cash_movements['total_in'] ?? 0)
            - (float) ($cash_movements['total_out'] ?? 0);

        $this->db->where('id', $session_id)->update($this->table, [
            'status'         => 'closed',
            'closing_float'  => $actual_cash,
            'expected_cash'  => $expected_cash,
            'actual_cash'    => $actual_cash,
            'cash_difference' => $actual_cash - $expected_cash,
            'notes'          => $notes,
            'closed_at'      => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'session' => $this->get($session_id)];
    }

    public function add_cash_movement(int $session_id, int $branch_id, int $staff_id, string $type, float $amount, string $reason, string $notes = ''): int
    {
        $this->db->insert(db_prefix() . 'pos_cash_movements', [
            'session_id'  => $session_id,
            'branch_id'   => $branch_id,
            'staff_id'    => $staff_id,
            'type'        => $type,
            'amount'      => $amount,
            'reason'      => $reason,
            'notes'       => $notes,
            'date_created' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function get_branch_sessions(int $branch_id, int $limit = 20, int $offset = 0): array
    {
        return $this->db
            ->select('s.*, CONCAT(st.firstname,\' \',st.lastname) AS staff_name')
            ->from($this->table . ' s')
            ->join(db_prefix() . 'staff st', 'st.staffid = s.staff_id', 'left')
            ->where('s.branch_id', $branch_id)
            ->order_by('s.opened_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    private function get_cash_movements(int $session_id): array
    {
        return $this->db
            ->select('cm.*, CONCAT(s.firstname,\' \',s.lastname) AS staff_name')
            ->from(db_prefix() . 'pos_cash_movements cm')
            ->join(db_prefix() . 'staff s', 's.staffid = cm.staff_id', 'left')
            ->where('cm.session_id', $session_id)
            ->order_by('cm.date_created', 'ASC')
            ->get()
            ->result_array();
    }
}
