<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_journal_model extends App_Model
{
    private $t = 'acc_journals';

    public function get($id)
    {
        return $this->db->get_where(db_prefix() . $this->t, ['id' => $id])->row();
    }

    public function get_by_type($type)
    {
        return $this->db->where('type', $type)->order_by('name', 'ASC')->get(db_prefix() . $this->t)->result();
    }

    public function get_all()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . $this->t)->result();
    }

    public function get_by_code($code)
    {
        return $this->db->get_where(db_prefix() . $this->t, ['code' => $code])->row();
    }

    public function get_dashboard_journals()
    {
        $journals = $this->get_by_type('bank');
        $result   = [];
        $p        = db_prefix();
        foreach ($journals as $j) {
            $sql     = "SELECT SUM(ml.debit) AS total_debit, SUM(ml.credit) AS total_credit
                        FROM {$p}acc_move_lines ml
                        INNER JOIN {$p}acc_moves m ON m.id = ml.move_id
                        WHERE m.journal_id = ? AND m.state = 'posted'";
            $balance = $this->db->query($sql, [(int)$j->id])->row();
            $j->balance = $balance ? (float)$balance->total_debit - (float)$balance->total_credit : 0;
            $result[] = $j;
        }
        return $result;
    }

    public function save($post, $id = null)
    {
        $data = [
            'name'                => $post['name'],
            'code'                => strtoupper($post['code']),
            'type'                => $post['type'],
            'account_id'          => (int)($post['account_id'] ?? 0) ?: null,
            'currency_id'         => (int)($post['currency_id'] ?? 0) ?: null,
            'sequence_prefix'     => $post['sequence_prefix'] ?? null,
            'restrict_mode_hash'  => (int)($post['restrict_mode_hash'] ?? 0),
        ];

        if ($id) {
            $this->db->update(db_prefix() . $this->t, $data, ['id' => $id]);
            return $id;
        }
        $this->db->insert(db_prefix() . $this->t, $data);
        return $this->db->insert_id();
    }

    public function delete($id)
    {
        $in_use = $this->db->where('journal_id', $id)->count_all_results(db_prefix() . 'acc_moves');
        if ($in_use > 0) {
            throw new Exception('Journal has existing entries and cannot be deleted.');
        }
        $this->db->delete(db_prefix() . $this->t, ['id' => $id]);
        return true;
    }

    public function get_for_invoice_type($move_type)
    {
        $type = in_array($move_type, ['out_invoice', 'out_refund']) ? 'sale' : 'purchase';
        return $this->get_by_type($type);
    }

    public function get_all_with_accounts()
    {
        return $this->db->select('j.*, a.code as account_code, a.name as account_name')
            ->from(db_prefix() . $this->t . ' j')
            ->join(db_prefix() . 'acc_gl_accounts a', 'a.id = j.account_id', 'left')
            ->order_by('j.name', 'ASC')
            ->get()->result();
    }
}
