<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_analytic_model extends App_Model
{
    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTIC PLANS (Dimensions)
    // ─────────────────────────────────────────────────────────────────────────

    public function get_all_plans($active_only = false)
    {
        if ($active_only) {
            $this->db->where('active', 1);
        }
        $this->db->order_by('sequence', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get('acc_analytic_plans')->result();
    }

    public function get_plan($id)
    {
        return $this->db->where('id', $id)->get('acc_analytic_plans')->row();
    }

    public function save_plan($data, $id = null)
    {
        $row = [
            'name'                    => $data['name'] ?? '',
            'description'             => $data['description'] ?? '',
            'default_applicability'   => $data['default_applicability'] ?? 'optional',
            'color'                   => $data['color'] ?? '#1a6b3a',
            'sequence'                => !empty($data['sequence']) ? (int)$data['sequence'] : 10,
            'active'                  => isset($data['active']) ? (int)(bool)$data['active'] : 1,
        ];
        if ($id) {
            $this->db->where('id', $id)->update('acc_analytic_plans', $row);
            return $id;
        }
        $this->db->insert('acc_analytic_plans', $row);
        return $this->db->insert_id();
    }

    public function delete_plan($id)
    {
        $in_use = $this->db->where('plan_id', $id)->count_all_results('acc_analytic_accounts');
        if ($in_use > 0) {
            return ['error' => 'Cannot delete: this plan has ' . $in_use . ' analytic accounts assigned to it.'];
        }
        $this->db->where('id', $id)->delete('acc_analytic_plans');
        return ['success' => true];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTIC ACCOUNTS (Cost Centres)
    // ─────────────────────────────────────────────────────────────────────────

    public function get_all_accounts($plan_id = null, $active_only = false, $flat = false)
    {
        $this->db->select('aa.*, ap.name as plan_name, ap.color as plan_color,
            parent.name as parent_name');
        $this->db->from('acc_analytic_accounts aa');
        $this->db->join('acc_analytic_plans ap', 'ap.id = aa.plan_id', 'left');
        $this->db->join('acc_analytic_accounts parent', 'parent.id = aa.parent_id', 'left');

        if ($plan_id) {
            $this->db->where('aa.plan_id', $plan_id);
        }
        if ($active_only) {
            $this->db->where('aa.active', 1);
        }

        $this->db->order_by('aa.plan_id', 'ASC');
        $this->db->order_by('aa.complete_name', 'ASC');
        $accounts = $this->db->get()->result();

        if ($flat) {
            return $accounts;
        }
        return $this->_build_tree($accounts);
    }

    public function get_account($id)
    {
        $this->db->select('aa.*, ap.name as plan_name, ap.color as plan_color');
        $this->db->from('acc_analytic_accounts aa');
        $this->db->join('acc_analytic_plans ap', 'ap.id = aa.plan_id', 'left');
        $this->db->where('aa.id', $id);
        return $this->db->get()->row();
    }

    public function get_accounts_for_plan($plan_id, $flat_with_indent = true)
    {
        $this->db->where('plan_id', $plan_id)->where('active', 1);
        $this->db->order_by('complete_name', 'ASC');
        $rows = $this->db->get('acc_analytic_accounts')->result();

        if (!$flat_with_indent) {
            return $rows;
        }

        // Build indented flat list for dropdowns
        $result = [];
        foreach ($rows as $r) {
            $indent = str_repeat('— ', $r->level);
            $result[] = (object)[
                'id'            => $r->id,
                'code'          => $r->code,
                'name'          => $r->name,
                'complete_name' => $r->complete_name ?: $r->name,
                'indent_name'   => $indent . ($r->code ? '[' . $r->code . '] ' : '') . $r->name,
                'level'         => $r->level,
                'parent_id'     => $r->parent_id,
                'plan_id'       => $r->plan_id,
            ];
        }
        return $result;
    }

    public function get_all_accounts_flat()
    {
        $this->db->select('aa.*, ap.name as plan_name, ap.color as plan_color');
        $this->db->from('acc_analytic_accounts aa');
        $this->db->join('acc_analytic_plans ap', 'ap.id = aa.plan_id', 'left');
        $this->db->where('aa.active', 1);
        $this->db->order_by('ap.sequence', 'ASC');
        $this->db->order_by('aa.complete_name', 'ASC');
        return $this->db->get()->result();
    }

    public function save_account($data, $id = null)
    {
        $row = [
            'name'       => trim($data['name'] ?? ''),
            'code'       => trim($data['code'] ?? ''),
            'plan_id'    => !empty($data['plan_id']) ? (int)$data['plan_id'] : null,
            'parent_id'  => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'partner_id' => !empty($data['partner_id']) ? (int)$data['partner_id'] : null,
            'currency_id'=> !empty($data['currency_id']) ? (int)$data['currency_id'] : 1,
            'note'       => $data['note'] ?? null,
            'active'     => isset($data['active']) ? (int)(bool)$data['active'] : 1,
        ];

        if ($id) {
            $this->db->where('id', $id)->update('acc_analytic_accounts', $row);
        } else {
            $this->db->insert('acc_analytic_accounts', $row);
            $id = $this->db->insert_id();
        }

        $this->_recompute_complete_name($id);
        $this->_recompute_children_names($id);

        return $id;
    }

    public function delete_account($id)
    {
        $has_children = $this->db->where('parent_id', $id)->count_all_results('acc_analytic_accounts');
        if ($has_children) {
            return ['error' => 'Cannot delete: this account has child accounts. Delete children first.'];
        }
        $in_use = $this->db->where('analytic_account_id', $id)->count_all_results('acc_analytic_lines');
        if ($in_use > 0) {
            return ['error' => 'Cannot delete: this account has ' . $in_use . ' analytic entries. Archive it instead.'];
        }
        $this->db->where('id', $id)->delete('acc_analytic_accounts');
        return ['success' => true];
    }

    private function _recompute_complete_name($id)
    {
        $account = $this->db->where('id', $id)->get('acc_analytic_accounts')->row();
        if (!$account) return;

        $parts = [$account->name];
        $level = 0;
        $parent_id = $account->parent_id;

        while ($parent_id) {
            $parent = $this->db->where('id', $parent_id)->get('acc_analytic_accounts')->row();
            if (!$parent) break;
            array_unshift($parts, $parent->name);
            $parent_id = $parent->parent_id;
            $level++;
        }

        $complete_name = implode(' / ', $parts);
        $this->db->where('id', $id)->update('acc_analytic_accounts', [
            'complete_name' => $complete_name,
            'level'         => $level,
        ]);
    }

    private function _recompute_children_names($parent_id)
    {
        $children = $this->db->where('parent_id', $parent_id)->get('acc_analytic_accounts')->result();
        foreach ($children as $child) {
            $this->_recompute_complete_name($child->id);
            $this->_recompute_children_names($child->id);
        }
    }

    private function _build_tree($flat)
    {
        $map = [];
        foreach ($flat as $item) {
            $map[$item->id] = $item;
            $item->children = [];
        }
        $roots = [];
        foreach ($flat as $item) {
            if ($item->parent_id && isset($map[$item->parent_id])) {
                $map[$item->parent_id]->children[] = $item;
            } else {
                $roots[] = $item;
            }
        }
        return $roots;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTIC LINES (Parallel Ledger)
    // ─────────────────────────────────────────────────────────────────────────

    public function get_lines($filters = [])
    {
        $this->db->select('al.*, aa.name as account_name, aa.code as account_code,
            ap.name as plan_name, ap.color as plan_color,
            am.name as move_name, am.move_type,
            IF(am.partner_type="vendor", COALESCE(pv.company,""), COALESCE(c.company,"")) as partner_name');
        $this->db->from('acc_analytic_lines al');
        $this->db->join('acc_analytic_accounts aa', 'aa.id = al.analytic_account_id', 'left');
        $this->db->join('acc_analytic_plans ap',    'ap.id = al.plan_id', 'left');
        $this->db->join('acc_moves am',             'am.id = al.move_id', 'left');
        $this->db->join('tblclients c',             'c.userid  = al.partner_id', 'left');
        $this->db->join('tblpur_vendor pv',         'pv.userid = al.partner_id', 'left');

        if (!empty($filters['analytic_account_id'])) {
            $this->db->where('al.analytic_account_id', $filters['analytic_account_id']);
        }
        if (!empty($filters['plan_id'])) {
            $this->db->where('al.plan_id', $filters['plan_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('al.date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('al.date <=', $filters['date_to']);
        }
        if (!empty($filters['move_type'])) {
            $this->db->where('am.move_type', $filters['move_type']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('al.name', $filters['search']);
            $this->db->or_like('al.ref', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('al.date', 'DESC');
        $this->db->order_by('al.id', 'DESC');
        $limit = !empty($filters['limit']) ? (int)$filters['limit'] : 1000;
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function post_analytic_lines($move_id)
    {
        $move = $this->db->where('id', $move_id)->get('acc_moves')->row();
        if (!$move) return;

        // Delete existing analytic lines for this move (re-posting)
        $this->db->where('move_id', $move_id)->delete('acc_analytic_lines');

        // Get product lines that have analytic_distribution
        $this->db->where('move_id', $move_id)
                 ->where('display_type', 'product')
                 ->where('analytic_distribution !=', '')
                 ->where('analytic_distribution IS NOT NULL', null, false);
        $lines = $this->db->get('acc_move_lines')->result();

        foreach ($lines as $line) {
            $distribution = json_decode($line->analytic_distribution, true);
            if (!is_array($distribution) || empty($distribution)) continue;

            $line_amount = (float)$line->price_subtotal;

            foreach ($distribution as $account_id => $percentage) {
                $account_id = (int)$account_id;
                $pct        = (float)$percentage;
                if ($pct <= 0 || $account_id <= 0) continue;

                $account = $this->db->where('id', $account_id)->get('acc_analytic_accounts')->row();
                $plan_id = $account ? $account->plan_id : null;

                $analytic_amount = round($line_amount * $pct / 100, 4);

                $this->db->insert('acc_analytic_lines', [
                    'name'                => $line->name,
                    'date'                => $move->date,
                    'analytic_account_id' => $account_id,
                    'plan_id'             => $plan_id,
                    'move_line_id'        => $line->id,
                    'move_id'             => $move_id,
                    'partner_id'          => $move->partner_id,
                    'product_id'          => $line->product_id,
                    'amount'              => $analytic_amount,
                    'unit_amount'         => (float)$line->quantity,
                    'currency_id'         => $move->currency_id ?? 1,
                    'ref'                 => $move->ref,
                    'percentage'          => $pct,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTIC BUDGETS
    // ─────────────────────────────────────────────────────────────────────────

    public function get_budgets($analytic_account_id = null)
    {
        $this->db->select('ab.*, aa.name as account_name, aa.code as account_code, ap.name as plan_name');
        $this->db->from('acc_analytic_budgets ab');
        $this->db->join('acc_analytic_accounts aa', 'aa.id = ab.analytic_account_id', 'left');
        $this->db->join('acc_analytic_plans ap', 'ap.id = aa.plan_id', 'left');
        if ($analytic_account_id) {
            $this->db->where('ab.analytic_account_id', $analytic_account_id);
        }
        $this->db->order_by('ab.date_from', 'DESC');
        return $this->db->get()->result();
    }

    public function save_budget($data, $id = null)
    {
        $row = [
            'name'                 => $data['name'] ?? '',
            'analytic_account_id'  => (int)$data['analytic_account_id'],
            'date_from'            => $data['date_from'],
            'date_to'              => $data['date_to'],
            'planned_amount'       => (float)$data['planned_amount'],
            'currency_id'          => !empty($data['currency_id']) ? (int)$data['currency_id'] : 1,
        ];
        if ($id) {
            $this->db->where('id', $id)->update('acc_analytic_budgets', $row);
            return $id;
        }
        $this->db->insert('acc_analytic_budgets', $row);
        return $this->db->insert_id();
    }

    public function delete_budget($id)
    {
        $this->db->where('id', $id)->delete('acc_analytic_budgets');
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REPORTING — Cost Centre P&L
    // ─────────────────────────────────────────────────────────────────────────

    public function get_cost_centre_pl($filters = [])
    {
        $date_from = $filters['date_from'] ?? date('Y-01-01');
        $date_to   = $filters['date_to']   ?? date('Y-m-d');
        $plan_id   = !empty($filters['plan_id']) ? (int)$filters['plan_id'] : null;

        // Get all active accounts for the plan (flat, with hierarchy info)
        $acc_query = $this->db->select('aa.*, ap.name as plan_name');
        $this->db->from('acc_analytic_accounts aa');
        $this->db->join('acc_analytic_plans ap', 'ap.id = aa.plan_id', 'left');
        $this->db->where('aa.active', 1);
        if ($plan_id) {
            $this->db->where('aa.plan_id', $plan_id);
        }
        $this->db->order_by('aa.complete_name', 'ASC');
        $accounts = $this->db->get()->result();

        if (empty($accounts)) return [];

        $account_ids = array_column($accounts, 'id');

        // Get totals from analytic lines in date range, grouped by account
        $this->db->select('al.analytic_account_id, SUM(al.amount) as total_amount, COUNT(al.id) as line_count');
        $this->db->from('acc_analytic_lines al');
        $this->db->join('acc_moves am', 'am.id = al.move_id', 'left');
        $this->db->where('al.date >=', $date_from);
        $this->db->where('al.date <=', $date_to);
        $this->db->where_in('al.analytic_account_id', $account_ids);
        $this->db->group_by('al.analytic_account_id');
        $totals_raw = $this->db->get()->result();

        $totals = [];
        foreach ($totals_raw as $t) {
            $totals[$t->analytic_account_id] = $t;
        }

        // Get budget amounts
        $this->db->select('analytic_account_id, SUM(planned_amount) as budget_amount');
        $this->db->from('acc_analytic_budgets');
        $this->db->where('date_from >=', $date_from);
        $this->db->where('date_to <=', $date_to);
        $this->db->where_in('analytic_account_id', $account_ids);
        $this->db->group_by('analytic_account_id');
        $budgets_raw = $this->db->get()->result();

        $budgets = [];
        foreach ($budgets_raw as $b) {
            $budgets[$b->analytic_account_id] = (float)$b->budget_amount;
        }

        // Build result rows with hierarchy info
        $result = [];
        foreach ($accounts as $acc) {
            $actual = isset($totals[$acc->id]) ? (float)$totals[$acc->id]->total_amount : 0;
            $budget = $budgets[$acc->id] ?? 0;
            $result[] = (object)[
                'id'            => $acc->id,
                'name'          => $acc->name,
                'code'          => $acc->code,
                'complete_name' => $acc->complete_name ?: $acc->name,
                'level'         => (int)$acc->level,
                'parent_id'     => $acc->parent_id,
                'plan_name'     => $acc->plan_name,
                'actual'        => $actual,
                'budget'        => $budget,
                'variance'      => $budget - $actual,
                'line_count'    => isset($totals[$acc->id]) ? (int)$totals[$acc->id]->line_count : 0,
            ];
        }

        return $result;
    }
}
