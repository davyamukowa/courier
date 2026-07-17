<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_management_trait
{
    // ─────────────────────────────────────────────────────────────────────────
    // BUDGETS
    // ─────────────────────────────────────────────────────────────────────────
    public function budgets()
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->order_by('date_from', 'DESC');
        $data['title']   = 'Budgets';
        $data['budgets'] = $this->db->get('acc_budgets')->result();
        $data['xb_page'] = 'budgets';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/budgets/list', $data, true),
        ]));
    }

    public function budget_form($id = null)
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $budget_data = [
                'name'       => $post['name'],
                'date_from'  => $post['date_from'],
                'date_to'    => $post['date_to'],
                'state'      => 'draft',
                'created_by' => get_staff_user_id(),
            ];

            if ($id) {
                $this->db->where('id', $id)->update('acc_budgets', $budget_data);
            } else {
                $this->db->insert('acc_budgets', $budget_data);
                $id = $this->db->insert_id();
            }

            if (!empty($post['lines']) && is_array($post['lines'])) {
                $this->db->where('budget_id', $id)->delete('acc_budget_lines');
                foreach ($post['lines'] as $line) {
                    if (empty($line['account_id'])) continue;
                    $this->db->insert('acc_budget_lines', [
                        'budget_id'      => $id,
                        'account_id'     => $line['account_id'],
                        'date_from'      => $post['date_from'],
                        'date_to'        => $post['date_to'],
                        'planned_amount' => (float)($line['planned_amount'] ?? 0),
                    ]);
                }
            }

            set_alert('success', $id ? 'Budget updated.' : 'Budget created.');
            redirect(admin_url('xetuu_books/budget_form/' . $id));
        }

        $budget = $id ? $this->db->where('id', $id)->get('acc_budgets')->row() : null;
        if ($id && !$budget) { show_404(); }

        $budget_lines = $id ? $this->db->where('budget_id', $id)->get('acc_budget_lines')->result() : [];

        $data['title']        = $id ? 'Edit Budget: ' . $budget->name : 'New Budget';
        $data['budget']       = $budget;
        $data['budget_lines'] = $budget_lines;
        $data['accounts']     = $this->xb_config->get_accounts();
        $data['xb_page']      = 'budgets';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/budgets/form', $data, true),
        ]));
    }

    public function confirm_budget($id)
    {
        $this->db->where('id', $id)->update('acc_budgets', ['state' => 'confirm']);
        set_alert('success', 'Budget confirmed.');
        redirect(admin_url('xetuu_books/budget_form/' . $id));
    }

    public function delete_budget($id)
    {
        $this->db->where('id', $id)->delete('acc_budgets');
        set_alert('success', 'Budget deleted.');
        redirect(admin_url('xetuu_books/budgets'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FIXED ASSETS
    // ─────────────────────────────────────────────────────────────────────────
    public function assets()
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select('acc_assets.*, acc_asset_models.name as model_name');
        $this->db->from('acc_assets');
        $this->db->join('acc_asset_models', 'acc_asset_models.id = acc_assets.model_id', 'left');
        $this->db->order_by('acc_assets.acquisition_date', 'DESC');

        $data['title']   = 'Fixed Assets';
        $data['assets']  = $this->db->get()->result();
        $data['xb_page'] = 'assets';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/assets/list', $data, true),
        ]));
    }

    public function asset_form($id = null)
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $asset_data = [
                'name'             => $post['name'],
                'acquisition_date' => $post['acquisition_date'] ?? date('Y-m-d'),
                'original_value'   => (float)($post['original_value'] ?? 0),
                'salvage_value'    => (float)($post['salvage_value'] ?? 0),
                'book_value'       => (float)($post['original_value'] ?? 0) - (float)($post['salvage_value'] ?? 0),
                'method'           => $post['method'] ?? 'linear',
                'method_number'    => (int)($post['method_number'] ?? 5),
                'method_period'    => (int)($post['method_period'] ?? 12),
                'model_id'         => !empty($post['model_id']) ? (int)$post['model_id'] : null,
                'state'            => 'draft',
            ];

            if ($id) {
                $this->db->where('id', $id)->update('acc_assets', $asset_data);
            } else {
                $this->db->insert('acc_assets', $asset_data);
                $id = $this->db->insert_id();
            }

            set_alert('success', $id ? 'Asset updated.' : 'Asset created.');
            redirect(admin_url('xetuu_books/asset_form/' . $id));
        }

        $asset = $id ? $this->db->where('id', $id)->get('acc_assets')->row() : null;
        if ($id && !$asset) { show_404(); }

        $asset_lines = $id ? $this->db->where('asset_id', $id)->get('acc_asset_lines')->result() : [];

        $data['title']        = $id ? 'Asset: ' . $asset->name : 'New Fixed Asset';
        $data['asset']        = $asset;
        $data['asset_lines']  = $asset_lines;
        $data['asset_models'] = $this->db->where('active', 1)->get('acc_asset_models')->result();
        $data['accounts']     = $this->xb_config->get_accounts();
        $data['xb_page']      = 'assets';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/assets/form', $data, true),
        ]));
    }

    public function confirm_asset($id)
    {
        $this->db->where('id', $id)->update('acc_assets', ['state' => 'open']);
        set_alert('success', 'Asset confirmed and running.');
        redirect(admin_url('xetuu_books/asset_form/' . $id));
    }

    public function delete_asset($id)
    {
        $this->db->where('id', $id)->delete('acc_assets');
        set_alert('success', 'Asset deleted.');
        redirect(admin_url('xetuu_books/assets'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEFERRED REVENUE / EXPENSE
    // ─────────────────────────────────────────────────────────────────────────
    public function deferred()
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        $type = $this->input->get('type') ?: 'revenue';
        $this->db->where('type', $type)->order_by('date_from', 'DESC');

        $data['title']    = $type === 'revenue' ? 'Deferred Revenues' : 'Deferred Expenses';
        $data['deferred'] = $this->db->get('acc_deferred')->result();
        $data['type']     = $type;
        $data['xb_page']  = 'deferred';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/deferred/list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRODUCTS
    // ─────────────────────────────────────────────────────────────────────────
    public function products()
    {
        if (!has_permission('accounting_transaction', '', 'view')) { access_denied('xetuu_books'); }

        $search = $this->input->get('search') ?: '';

        // Pull products from Perfex CRM items catalog
        if ($search) {
            $esc = $this->db->escape_like_str($search);
            $this->db->where("(description LIKE '%{$esc}%' OR long_description LIKE '%{$esc}%')", null, false);
        }
        $this->db->order_by('description', 'ASC');
        $items = $this->db->get(db_prefix() . 'items')->result();

        // Sales stats per product via acc_move_lines
        $pfx = $this->db->dbprefix;
        $stats_rows = $this->db->query("
            SELECT ml.product_id,
                   COUNT(DISTINCT ml.move_id) as sale_count,
                   SUM(ml.quantity) as total_qty,
                   SUM(ml.price_subtotal) as total_revenue
            FROM {$pfx}acc_move_lines ml
            JOIN {$pfx}acc_moves m ON m.id = ml.move_id
            WHERE m.move_type = 'out_invoice' AND m.state = 'posted' AND ml.product_id IS NOT NULL
            GROUP BY ml.product_id
        ")->result();
        $stats = [];
        foreach ($stats_rows as $s) { $stats[$s->product_id] = $s; }

        $data['title']   = 'Products';
        $data['items']   = $items;
        $data['stats']   = $stats;
        $data['search']  = $search;
        $data['xb_page'] = 'products';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/products/list', $data, true),
        ]));
    }

    // CUSTOMERS / SUPPLIERS
    // ─────────────────────────────────────────────────────────────────────────
    public function customers()
    {
        if (!has_permission('accounting_transaction', '', 'view')) { access_denied('xetuu_books'); }

        $search = $this->input->get('search') ?: '';
        $this->db->select('c.userid, c.company, ct.email, c.phonenumber,
            COALESCE(inv.invoice_count,0) as invoice_count,
            COALESCE(inv.total_invoiced,0) as total_invoiced,
            COALESCE(inv.total_outstanding,0) as total_outstanding');
        $pfx = $this->db->dbprefix;
        $this->db->from('tblclients c');
        $this->db->join('tblcontacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left');
        $this->db->join('(SELECT partner_id,
                COUNT(*) as invoice_count,
                SUM(amount_total) as total_invoiced,
                SUM(amount_residual) as total_outstanding
            FROM ' . $pfx . 'acc_moves
            WHERE move_type = \'out_invoice\' AND state = \'posted\'
            GROUP BY partner_id) inv', 'inv.partner_id = c.userid', 'left');
        $this->db->where('c.active', 1);
        if ($search) {
            $this->db->group_start();
            $this->db->like('c.company', $search);
            $this->db->or_like('ct.email', $search);
            $this->db->group_end();
        }
        $this->db->order_by('c.company', 'ASC');

        $data['title']     = 'Customers';
        $data['customers'] = $this->db->get()->result();
        $data['search']    = $search;
        $data['xb_page']   = 'customers';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/customers/list', $data, true),
        ]));
    }

    public function suppliers()
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        $search = $this->input->get('search') ?: '';
        $this->db->select('c.userid, c.company, ct.email, c.phonenumber,
            COALESCE(bills.bill_count,0) as bill_count,
            COALESCE(bills.total_billed,0) as total_billed,
            COALESCE(bills.total_outstanding,0) as total_outstanding');
        $pfx = $this->db->dbprefix;
        $this->db->from('tblclients c');
        $this->db->join('tblcontacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left');
        $this->db->join('(SELECT partner_id,
                COUNT(*) as bill_count,
                SUM(amount_total) as total_billed,
                SUM(amount_residual) as total_outstanding
            FROM ' . $pfx . 'acc_moves
            WHERE move_type = \'in_invoice\' AND state = \'posted\'
            GROUP BY partner_id) bills', 'bills.partner_id = c.userid', 'left');
        $this->db->where('c.active', 1);
        if ($search) {
            $this->db->group_start();
            $this->db->like('c.company', $search);
            $this->db->or_like('ct.email', $search);
            $this->db->group_end();
        }
        $this->db->order_by('c.company', 'ASC');

        $data['title']     = 'Suppliers';
        $data['suppliers'] = $this->db->get()->result();
        $data['search']    = $search;
        $data['xb_page']   = 'suppliers';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/suppliers/list', $data, true),
        ]));
    }
}
