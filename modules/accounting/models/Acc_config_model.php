<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Acc_config_model extends App_Model
{
    // ── Chart of Accounts ────────────────────────────────────────────────

    public function get_accounts($filters = [])
    {
        $this->db->select('a.*, p.name as parent_name, p.code as parent_code');
        $this->db->from(db_prefix() . 'acc_gl_accounts a');
        $this->db->join(db_prefix() . 'acc_gl_accounts p', 'p.id = a.parent_id', 'left');
        if (!empty($filters['type']))       { $this->db->where('a.type', $filters['type']); }
        if (!empty($filters['active']))     { $this->db->where('a.active', $filters['active']); }
        if (!empty($filters['search'])) {
            $s = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()->like('a.code', $s)->or_like('a.name', $s)->group_end();
        }
        $this->db->order_by('a.code', 'ASC');
        return $this->db->get()->result();
    }

    public function get_account($id)
    {
        return $this->db->get_where(db_prefix() . 'acc_gl_accounts', ['id' => $id])->row();
    }

    public function search_accounts($term)
    {
        $s = $this->db->escape_like_str($term);
        return $this->db->select('id, code, name, type')
            ->where('active', 1)
            ->group_start()->like('code', $s)->or_like('name', $s)->group_end()
            ->order_by('code', 'ASC')->limit(20)
            ->get(db_prefix() . 'acc_gl_accounts')->result();
    }

    public function save_account($post, $id = null)
    {
        $data = [
            'code'        => $post['code'],
            'name'        => $post['name'],
            'type'        => $post['type'],
            'parent_id'   => (int)($post['parent_id'] ?? 0) ?: null,
            'currency_id' => (int)($post['currency_id'] ?? 0) ?: null,
            'is_group'    => (int)($post['is_group'] ?? 0),
            'deprecated'  => (int)($post['deprecated'] ?? 0),
            'active'      => 1,
        ];
        if ($id) {
            $this->db->update(db_prefix() . 'acc_gl_accounts', $data, ['id' => $id]);
            return $id;
        }
        $this->db->insert(db_prefix() . 'acc_gl_accounts', $data);
        return $this->db->insert_id();
    }

    public function delete_account($id)
    {
        $in_use = $this->db->where('account_id', $id)->count_all_results(db_prefix() . 'acc_move_lines');
        if ($in_use > 0) {
            throw new Exception('Account has existing journal lines and cannot be deleted.');
        }
        $this->db->delete(db_prefix() . 'acc_gl_accounts', ['id' => $id]);
        return true;
    }

    // ── Taxes ────────────────────────────────────────────────────────────

    public function get_taxes($filters = [])
    {
        // Accept string shorthand: 'sale' / 'purchase'
        if (is_string($filters)) { $filters = ['type_tax_use' => $filters]; }
        $this->db->select('t.*, a.code as account_code, a.name as account_name');
        $this->db->from(db_prefix() . 'acc_taxes t');
        $this->db->join(db_prefix() . 'acc_gl_accounts a', 'a.id = t.account_id', 'left');
        if (!empty($filters['type_tax_use'])) { $this->db->where('t.type_tax_use', $filters['type_tax_use']); }
        if (!empty($filters['active']))       { $this->db->where('t.active', $filters['active']); }
        $this->db->order_by('t.name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_tax($id)
    {
        return $this->db->get_where(db_prefix() . 'acc_taxes', ['id' => $id])->row();
    }

    public function save_tax($post, $id = null)
    {
        $data = [
            'name'          => $post['name'],
            'type_tax_use'  => $post['type_tax_use'],
            'amount_type'   => $post['amount_type'] ?? 'percent',
            'amount'        => (float)$post['amount'],
            'account_id'    => (int)($post['account_id'] ?? 0) ?: null,
            'refund_account_id' => (int)($post['refund_account_id'] ?? 0) ?: null,
            'description'   => $post['description'] ?? null,
            'price_include' => (int)($post['price_include'] ?? 0),
            'active'        => (int)($post['active'] ?? 1),
        ];
        if ($id) {
            $this->db->update(db_prefix() . 'acc_taxes', $data, ['id' => $id]);
            return $id;
        }
        $this->db->insert(db_prefix() . 'acc_taxes', $data);
        return $this->db->insert_id();
    }

    public function delete_tax($id)
    {
        $this->db->delete(db_prefix() . 'acc_taxes', ['id' => $id]);
        return true;
    }

    // ── Payment Terms ────────────────────────────────────────────────────

    public function get_payment_terms($active_only = true)
    {
        if ($active_only) { $this->db->where('active', 1); }
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'acc_payment_terms')->result();
    }

    public function get_payment_term($id)
    {
        $term  = $this->db->get_where(db_prefix() . 'acc_payment_terms', ['id' => $id])->row();
        if ($term) {
            $term->lines = $this->db->order_by('sequence', 'ASC')->get_where(db_prefix() . 'acc_payment_term_lines', ['payment_term_id' => $id])->result();
        }
        return $term;
    }

    // ── Currencies ───────────────────────────────────────────────────────

    public function get_currencies()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'acc_currencies')->result();
    }

    public function get_currency($id)
    {
        return $this->db->get_where(db_prefix() . 'acc_currencies', ['id' => $id])->row();
    }

    public function get_base_currency()
    {
        return $this->db->where('is_base', 1)->get(db_prefix() . 'acc_currencies')->row();
    }

    public function update_currency_rate($id, $rate)
    {
        $this->db->update(db_prefix() . 'acc_currencies', ['rate' => $rate, 'date' => date('Y-m-d')], ['id' => $id]);
    }

    // ── Settings ─────────────────────────────────────────────────────────

    public function get_setting($key, $default = null)
    {
        $row = $this->db->get_where(db_prefix() . 'acc_xb_settings', ['setting_key' => $key])->row();
        return $row ? $row->setting_value : $default;
    }

    public function get_settings_group($group = null)
    {
        if ($group) { $this->db->where('setting_group', $group); }
        $rows = $this->db->get(db_prefix() . 'acc_xb_settings')->result();
        $out  = [];
        foreach ($rows as $r) { $out[$r->setting_key] = $r->setting_value; }
        return $out;
    }

    public function save_setting($key, $value, $group = 'general')
    {
        $exists = $this->db->get_where(db_prefix() . 'acc_xb_settings', ['setting_key' => $key])->row();
        if ($exists) {
            $this->db->update(db_prefix() . 'acc_xb_settings', ['setting_value' => $value], ['setting_key' => $key]);
        } else {
            $this->db->insert(db_prefix() . 'acc_xb_settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => $group]);
        }
    }

    public function save_settings_bulk($post, $group = 'general')
    {
        foreach ($post as $key => $value) {
            $this->save_setting($key, $value, $group);
        }
    }

    // ── Fiscal Years ─────────────────────────────────────────────────────

    public function get_fiscal_years()
    {
        return $this->db->order_by('date_from', 'DESC')->get(db_prefix() . 'acc_fiscal_years')->result();
    }

    public function get_current_fiscal_year()
    {
        $today = date('Y-m-d');
        return $this->db->where('date_from <=', $today)->where('date_to >=', $today)->get(db_prefix() . 'acc_fiscal_years')->row();
    }

    public function get_lock_dates()
    {
        return $this->db->limit(1)->order_by('id', 'DESC')->get(db_prefix() . 'acc_lock_dates')->row();
    }

    public function save_lock_dates($post)
    {
        $data = [
            'period_lock_date' => $post['period_lock_date'] ?? null,
            'hard_lock_date'   => $post['hard_lock_date'] ?? null,
        ];
        $existing = $this->get_lock_dates();
        if ($existing) {
            $this->db->update(db_prefix() . 'acc_lock_dates', $data, ['id' => $existing->id]);
        } else {
            $this->db->insert(db_prefix() . 'acc_lock_dates', $data);
        }
    }

    // ── Analytic Plans ───────────────────────────────────────────────────

    public function get_analytic_plans()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'acc_analytic_plans')->result();
    }

    public function get_analytic_accounts($plan_id = null)
    {
        if ($plan_id) { $this->db->where('plan_id', $plan_id); }
        return $this->db->where('active', 1)->order_by('name', 'ASC')->get(db_prefix() . 'acc_analytic_accounts')->result();
    }

    // ── Partner search (clients + vendors) ───────────────────────────────

    public function search_partners($term, $type = 'all')
    {
        $s      = $this->db->escape_like_str($term);
        $result = [];

        if ($type === 'all' || $type === 'customer') {
            $clients = $this->db->select('id, CONCAT(firstname," ",lastname) as name, email, "customer" as partner_type')
                ->like('firstname', $s)->or_like('lastname', $s)->or_like('email', $s)
                ->limit(10)->get(db_prefix() . 'clients')->result();
            foreach ($clients as $c) { $result[] = $c; }
        }

        if ($type === 'all' || $type === 'vendor') {
            if ($this->db->table_exists(db_prefix() . 'pur_vendor')) {
                $vendors = $this->db->select('id, vname as name, email, "vendor" as partner_type')
                    ->like('vname', $s)->or_like('email', $s)
                    ->limit(10)->get(db_prefix() . 'pur_vendor')->result();
                foreach ($vendors as $v) { $result[] = $v; }
            }
        }

        return $result;
    }

    // ── Aliases and missing helpers ──────────────────────────────────────────

    public function get_all_settings() { return $this->get_settings_group(); }

    public function set_setting($key, $value) { $this->save_setting($key, $value); return true; }

    public function get_account_types()
    {
        return ['Asset','Current Asset','Fixed Asset','Bank','Cash','Liability',
                'Current Liability','Long-term Liability','Equity','Revenue',
                'Cost of Revenue','Expense','Other Income','Other Expense','Tax','Receivable','Payable'];
    }

    public function get_taxes_by_ids($ids)
    {
        if (empty($ids)) { return []; }
        return $this->db->where_in('id', $ids)->get(db_prefix() . 'acc_taxes')->result();
    }
}
