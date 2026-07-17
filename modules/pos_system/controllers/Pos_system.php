<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_system extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'pos_system/pos_model',
            'pos_system/pos_branches_model',
            'pos_system/pos_products_model',
            'pos_system/pos_sessions_model',
            'pos_system/pos_sales_model',
            'pos_system/pos_profiles_model',
            'pos_system/pos_invoice_model',
        ]);
        require_once POS_SYSTEM_PATH . 'helpers/pos_helper.php';
        $this->load->library('pos_system/pos_auth');
        $this->load->library('pos_system/pos_integrations');
    }

    /**
     * Deny access without looping back via HTTP_REFERER.
     * Perfex's access_denied() redirects to the referer, which is the very page
     * that just denied access — causing an infinite redirect loop. This method
     * always sends the user forward to the highest POS page they can reach.
     */
    private function _pos_deny(): void
    {
        set_alert('danger', _l('access_denied'));
        if (is_admin()) {
            redirect(admin_url('pos_system'));
        } elseif (pos_can_access('supervisor') || pos_perm('pos_invoices') || pos_perm('pos_reports')) {
            redirect(admin_url('pos_system'));
        } elseif (pos_can_access('cashier') || pos_perm('pos_terminal')) {
            redirect(admin_url('pos_system/terminal'));
        } else {
            redirect(admin_url());
        }
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index()
    {
        if (!pos_can_access('supervisor') && !pos_perm('pos_invoices') && !pos_perm('pos_reports')) {
            $this->_pos_deny(); return;
        }

        $branch_id     = pos_get_staff_branch();
        $branch_filter = pos_get_auth_branch(); // null = all, int = scoped to this branch
        $year          = (int)($this->input->get('year') ?: date('Y'));

        // Branch-wise sales (scoped by branch_filter for non-admins)
        $qb = $this->db
            ->select('b.name AS branch_name,
                      COUNT(s.id)                               AS total_sales,
                      COALESCE(SUM(s.total),0)                 AS total_amount,
                      COALESCE(SUM(s.amount_paid),0)           AS paid,
                      COALESCE(SUM(s.total-s.amount_paid),0)   AS due')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_sales s',
                   's.branch_id = b.id AND s.status=\'completed\' AND YEAR(s.date_created)=' . $year,
                   'left');
        if ($branch_filter !== null) {
            $qb = $qb->where('b.id', $branch_filter);
        }
        $sales_rows = $qb->group_by('b.id')->order_by('b.name')->get()->result_array();

        $qb = $this->db
            ->select('b.name AS branch_name,
                      COUNT(ib.id)                                    AS total_purchases,
                      COALESCE(SUM(ib.quantity * ib.cost_price),0)   AS total_amount,
                      COALESCE(SUM(ib.quantity * ib.cost_price),0)   AS paid,
                      0                                               AS due')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_inventory_batches ib',
                   'ib.branch_id = b.id AND YEAR(ib.date_created)=' . $year,
                   'left');
        if ($branch_filter !== null) {
            $qb = $qb->where('b.id', $branch_filter);
        }
        $purchase_rows = $qb->group_by('b.id')->order_by('b.name')->get()->result_array();

        $qb = $this->db
            ->select('b.name AS branch_name,
                      COUNT(DISTINCT ib.product_id) AS product_count,
                      COALESCE(SUM(ib.quantity),0)  AS total_qty')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_inventory_batches ib',
                   'ib.branch_id = b.id AND ib.expiry_date IS NOT NULL
                    AND ib.expiry_date < CURDATE() AND ib.quantity > 0',
                   'left');
        if ($branch_filter !== null) {
            $qb = $qb->where('b.id', $branch_filter);
        }
        $expired_rows = $qb->group_by('b.id')->order_by('b.name')->get()->result_array();

        $qb = $this->db
            ->select('b.name AS branch_name, COUNT(sb.staff_id) AS total_staff')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_staff_branches sb', 'sb.branch_id = b.id', 'left');
        if ($branch_filter !== null) {
            $qb = $qb->where('b.id', $branch_filter);
        }
        $staff_rows = $qb->group_by('b.id')->order_by('b.name')->get()->result_array();

        $data = [
            'title'          => _l('pos_dashboard'),
            'today_totals'   => $this->pos_model->get_today_totals($branch_id),
            'current_branch' => $branch_id ? $this->pos_branches_model->get($branch_id) : null,
            'open_session'   => $branch_id
                ? $this->pos_sessions_model->get_open_session(get_staff_user_id(), $branch_id)
                : null,
            'report_year'    => $year,
            'sales_rows'     => $sales_rows,
            'purchase_rows'  => $purchase_rows,
            'expired_rows'   => $expired_rows,
            'staff_rows'     => $staff_rows,
        ];

        $this->load->view('pos_system/admin/index', $data);
    }

    // ─── POS Terminal (SPA shell) ─────────────────────────────────────────────

    public function terminal()
    {
        if (!pos_can_access('cashier') && !pos_perm('pos_terminal')) {
            $this->_pos_deny(); return;
        }

        $branch_id = pos_get_staff_branch();
        if (!$branch_id) {
            set_alert('warning', 'You are not assigned to any branch. Ask your admin to assign you to a branch first.');
            redirect(admin_url('pos_system'));
        }

        // Issue a short-lived token for the SPA to use
        $token = $this->pos_auth->issue_token(
            get_staff_user_id(),
            $branch_id,
            'web-terminal',
            8    // 8-hour session
        );

        $data = [
            'title'     => _l('pos_terminal'),
            'api_url'   => base_url('pos_system/api'),
            'api_token' => $token,
            'branch_id' => $branch_id,
        ];

        $this->load->view('pos_system/admin/pos/terminal', $data);
    }

    // ─── Branches ─────────────────────────────────────────────────────────────

    public function branches()
    {
        if (!is_admin()) {
            $this->_pos_deny(); return;
        }

        $branches = $this->pos_branches_model->get_all();

        // Enrich each branch with live stats
        foreach ($branches as &$b) {
            $bid = (int) $b['id'];

            $staff_row = $this->db
                ->select('COUNT(*) AS cnt')
                ->where('branch_id', $bid)
                ->get(db_prefix() . 'pos_staff_branches')
                ->row();
            $b['staff_count'] = (int)($staff_row->cnt ?? 0);

            $sales_row = $this->db
                ->select('COUNT(*) AS cnt, COALESCE(SUM(total),0) AS revenue')
                ->where('branch_id', $bid)
                ->where('status', 'completed')
                ->get(db_prefix() . 'pos_sales')
                ->row();
            $b['total_sales']   = (int)($sales_row->cnt     ?? 0);
            $b['total_revenue'] = (float)($sales_row->revenue ?? 0);

            $today_row = $this->db
                ->select('COALESCE(SUM(total),0) AS today_rev')
                ->where('branch_id', $bid)
                ->where('status', 'completed')
                ->where('DATE(date_created)', date('Y-m-d'))
                ->get(db_prefix() . 'pos_sales')
                ->row();
            $b['today_revenue'] = (float)($today_row->today_rev ?? 0);
        }
        unset($b);

        $total_revenue  = array_sum(array_column($branches, 'total_revenue'));
        $total_staff    = array_sum(array_column($branches, 'staff_count'));
        $total_sales    = array_sum(array_column($branches, 'total_sales'));

        $year = (int)($this->input->get('year') ?: date('Y'));

        // ── Branch-wise sales table ────────────────────────────────────────
        $sales_rows = $this->db
            ->select('b.id, b.name AS branch_name,
                      COUNT(s.id)                                AS total_sales,
                      COALESCE(SUM(s.total),0)                  AS total_amount,
                      COALESCE(SUM(s.amount_paid),0)            AS paid,
                      COALESCE(SUM(s.total - s.amount_paid),0)  AS due')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_sales s',
                   's.branch_id = b.id AND s.status = \'completed\' AND YEAR(s.date_created) = ' . $year,
                   'left')
            ->group_by('b.id')
            ->order_by('b.name')
            ->get()->result_array();

        // ── Branch-wise purchases table (inventory batches = stock received) ──
        $purchase_rows = $this->db
            ->select('b.id, b.name AS branch_name,
                      COUNT(ib.id)                                          AS total_purchases,
                      COALESCE(SUM(ib.quantity * ib.cost_price),0)         AS total_amount,
                      COALESCE(SUM(ib.quantity * ib.cost_price),0)         AS paid,
                      0                                                     AS due')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_inventory_batches ib',
                   'ib.branch_id = b.id AND YEAR(ib.date_created) = ' . $year,
                   'left')
            ->group_by('b.id')
            ->order_by('b.name')
            ->get()->result_array();

        // ── Expired products per branch ────────────────────────────────────
        $expired_rows = $this->db
            ->select('b.id, b.name AS branch_name,
                      COUNT(DISTINCT ib.product_id)     AS product_count,
                      COALESCE(SUM(ib.quantity),0)       AS total_qty')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_inventory_batches ib',
                   'ib.branch_id = b.id AND ib.expiry_date IS NOT NULL
                    AND ib.expiry_date < CURDATE() AND ib.quantity > 0',
                   'left')
            ->group_by('b.id')
            ->order_by('b.name')
            ->get()->result_array();

        // ── Employee overview ──────────────────────────────────────────────
        $staff_rows = $this->db
            ->select('b.id, b.name AS branch_name,
                      COUNT(sb.staff_id) AS total_staff,
                      SUM(CASE WHEN sb.role=\'cashier\'    THEN 1 ELSE 0 END) AS cashiers,
                      SUM(CASE WHEN sb.role=\'supervisor\' THEN 1 ELSE 0 END) AS supervisors,
                      SUM(CASE WHEN sb.role=\'manager\'    THEN 1 ELSE 0 END) AS managers,
                      SUM(CASE WHEN sb.role=\'admin\'      THEN 1 ELSE 0 END) AS admins')
            ->from(db_prefix() . 'pos_branches b')
            ->join(db_prefix() . 'pos_staff_branches sb', 'sb.branch_id = b.id', 'left')
            ->group_by('b.id')
            ->order_by('b.name')
            ->get()->result_array();

        $data = [
            'title'          => _l('pos_branches'),
            'branches'       => $branches,
            'total_revenue'  => $total_revenue,
            'total_staff'    => $total_staff,
            'total_sales'    => $total_sales,
            'report_year'    => $year,
            'sales_rows'     => $sales_rows,
            'purchase_rows'  => $purchase_rows,
            'expired_rows'   => $expired_rows,
            'staff_rows'     => $staff_rows,
        ];

        $this->load->view('pos_system/admin/branches/manage', $data);
    }

    public function branch_save()
    {
        if (!is_admin()) {
            $this->_pos_deny(); return;
        }

        $id   = $this->input->post('id');
        $data = $this->input->post([
            'name', 'code', 'address', 'city', 'country',
            'phone', 'email', 'currency', 'timezone', 'tax_pin',
            'receipt_header', 'receipt_footer',
        ]);

        if ($id) {
            $this->pos_branches_model->update((int) $id, $data);
            set_alert('success', _l('updated_successfully', _l('pos_branch')));
        } else {
            $data['created_by'] = get_staff_user_id();

            // ── First-branch auto-creation ──────────────────────────────
            // When there are NO branches yet, automatically create a protected
            // company-named branch first and re-assign all orphaned data to it.
            $existing_count = (int) $this->db->count_all(db_prefix() . 'pos_branches');

            if ($existing_count === 0) {
                $company_name = get_option('companyname') ?: 'Main Branch';

                // Build a safe unique code from company name
                $company_code = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $company_name));
                $company_code = substr($company_code ?: 'MAIN', 0, 8) . '-AUTO';

                $auto_id = $this->pos_branches_model->create([
                    'name'         => $company_name,
                    'code'         => $company_code,
                    'currency'     => $data['currency'] ?? 'KES',
                    'timezone'     => $data['timezone'] ?? 'Africa/Nairobi',
                    'is_protected' => 1,
                    'created_by'   => get_staff_user_id(),
                ]);

                // Re-assign all orphaned records (branch_id = 0 or NULL) to the auto branch
                if ($auto_id) {
                    foreach (['pos_sales', 'pos_sessions', 'pos_payments',
                              'pos_inventory', 'pos_inventory_batches',
                              'pos_inventory_movements'] as $tbl) {
                        $t = db_prefix() . $tbl;
                        if ($this->db->table_exists($t)) {
                            $this->db->query(
                                "UPDATE `$t` SET branch_id = $auto_id
                                 WHERE branch_id IS NULL OR branch_id = 0"
                            );
                        }
                    }

                    set_alert('success',
                        'A default branch "' . htmlspecialchars($company_name) . '" was automatically created ' .
                        'and all existing data has been assigned to it. ' .
                        'This branch is protected and cannot be deleted.'
                    );
                }
            }
            // ── End first-branch logic ───────────────────────────────────

            $this->pos_branches_model->create($data);
            set_alert('success', _l('added_successfully', _l('pos_branch')));
        }

        redirect(admin_url('pos_system/branches'));
    }

    public function branch_delete(int $id)
    {
        if (!is_admin()) {
            $this->_pos_deny(); return;
        }

        $branch = $this->pos_branches_model->get($id);
        if ($branch && (int)($branch['is_protected'] ?? 0) === 1) {
            set_alert('danger',
                'The "' . htmlspecialchars($branch['name']) . '" branch is automatically created and protected — it cannot be deleted.'
            );
            redirect(admin_url('pos_system/branches'));
        }

        $deleted = $this->pos_branches_model->delete($id);
        if ($deleted) {
            set_alert('success', _l('deleted_successfully', _l('pos_branch')));
        } else {
            set_alert('danger', 'This branch cannot be deleted.');
        }
        redirect(admin_url('pos_system/branches'));
    }

    public function branch_assign_me(int $id)
    {
        $this->pos_branches_model->assign_staff($id, get_staff_user_id(), 'cashier', true);
        set_alert('success', 'You have been assigned to this branch successfully.');
        redirect(admin_url('pos_system/branches'));
    }

    // ─── Branch Staff AJAX ────────────────────────────────────────────────────

    public function branch_staff_get()
    {
        if (!is_admin()) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $branch_id = (int) $this->input->post('branch_id');
        $staff     = $this->pos_branches_model->get_staff($branch_id);
        $this->output->set_content_type('application/json');
        echo json_encode(['success' => true, 'staff' => $staff]);
    }

    public function branch_staff_save()
    {
        if (!is_admin()) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $branch_id  = (int)   $this->input->post('branch_id');
        $staff_id   = (int)   $this->input->post('staff_id');
        $role       = (string)($this->input->post('role') ?: 'cashier');
        $is_default = (bool)  $this->input->post('is_default');

        if (!in_array($role, ['cashier', 'supervisor', 'manager', 'admin'], true)) {
            $role = 'cashier';
        }

        $this->pos_branches_model->assign_staff($branch_id, $staff_id, $role, $is_default);
        $staff = $this->pos_branches_model->get_staff($branch_id);

        $this->output->set_content_type('application/json');
        echo json_encode(['success' => true, 'staff' => $staff]);
    }

    public function branch_staff_remove()
    {
        if (!is_admin()) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $branch_id = (int) $this->input->post('branch_id');
        $staff_id  = (int) $this->input->post('staff_id');
        $this->pos_branches_model->remove_staff($branch_id, $staff_id);
        $staff = $this->pos_branches_model->get_staff($branch_id);

        $this->output->set_content_type('application/json');
        echo json_encode(['success' => true, 'staff' => $staff]);
    }

    // ─── Staff Assignments (merged into Profiles) ────────────────────────────

    public function staff_assignments()
    {
        set_alert('info', 'Staff assignments have been moved to POS Profiles. Assign users per profile.');
        redirect(admin_url('pos_system/profiles'));
    }

    public function save_staff_assignment()
    {
        redirect(admin_url('pos_system/profiles'));
    }

    // ─── Products ─────────────────────────────────────────────────────────────

    public function products()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products')) {
            $this->_pos_deny(); return;
        }

        // Pull items directly from Perfex `tblitems` (shared with Sales/Warehouse)
        // merged with pos_products overrides + warehouse stock levels.
        // Fall back to branch_id = 0 (sum across warehouses) if no branch is assigned.
        $branch_id = (int) (pos_get_staff_branch() ?: 0);

        $search   = $this->input->get('search')   ?: null;
        $category = $this->input->get('category') ?: null;
        $status   = $this->input->get('status');
        $status   = ($status === '' || $status === null) ? 1 : (int) $status;

        $filters = array_filter([
            'search'      => $search,
            'category_id' => $category,
            'is_active'   => $status,
        ], fn($v) => $v !== null && $v !== '');

        $this->load->library('pos_system/Pos_integrations');

        $items = $this->pos_integrations->get_sellable_items($branch_id, $filters, 500, 0);

        // Item-groups (categories) — pulled from the shared tblitems_groups
        $groups = $this->db->order_by('name', 'ASC')->get(db_prefix() . 'items_groups')->result_array();

        $data = [
            'title'           => _l('pos_products'),
            'items'           => $items,
            'categories'      => $this->pos_products_model->get_categories(),
            'item_groups'     => $groups,
            'current_search'  => $search,
            'current_category'=> $category,
            'current_status'  => $status,
            'branch_id'       => $branch_id,
        ];

        $this->load->view('pos_system/admin/products/manage', $data);
    }

    /**
     * Save a POS-only product (form POST). For items that live in tblitems
     * (Perfex/Warehouse), edit them from the Sales / Items module — those
     * changes flow through to POS via get_sellable_items().
     */
    public function product_save()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products', 'create') && !pos_perm('pos_products', 'edit')) {
            $this->_pos_deny(); return;
        }

        $id   = (int) $this->input->post('id');
        $data = $this->input->post([
            'name', 'sku', 'barcode', 'selling_price', 'cost_price',
            'category_id', 'unit', 'track_inventory', 'allow_negative',
            'reorder_point', 'description', 'is_active', 'is_pos_visible', 'image',
        ]);

        // Normalise numerics and booleans
        $data['selling_price']   = (float) ($data['selling_price'] ?? 0);
        $data['cost_price']      = (float) ($data['cost_price'] ?? 0);
        $data['category_id']     = $data['category_id'] !== '' ? (int) $data['category_id'] : null;
        $data['track_inventory'] = (int) ($data['track_inventory'] ?? 1);
        $data['allow_negative']  = (int) ($data['allow_negative'] ?? 0);
        $data['reorder_point']   = $data['reorder_point'] !== '' ? (float) $data['reorder_point'] : null;
        $data['is_active']       = (int) ($data['is_active'] ?? 1);
        $data['is_pos_visible']  = (int) ($data['is_pos_visible'] ?? 1);

        if ($id) {
            $this->pos_products_model->update($id, $data);
            set_alert('success', _l('updated_successfully', _l('pos_product')));
        } else {
            $this->pos_products_model->create($data);
            set_alert('success', _l('added_successfully', _l('pos_product')));
        }

        redirect(admin_url('pos_system/products'));
    }

    public function product_delete(int $id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products', 'delete')) {
            $this->_pos_deny(); return;
        }

        $this->pos_products_model->delete($id);
        set_alert('success', _l('deleted_successfully', _l('pos_product')));
        redirect(admin_url('pos_system/products'));
    }

    // ─── Item Full Form (GET: new/edit, POST: save, POST: image upload) ─────────

    /**
     * Full-page item creation / edit form.
     * GET admin/pos_system/inventory/item_form        → new
     * GET admin/pos_system/inventory/item_form/{id}   → edit
     */
    private function _inv_item_form(?int $id = null): void
    {
        [$branch_id, $branches] = $this->_inv_common_data();

        $item = null;
        if ($id) {
            $item = $this->pos_products_model->get_full($id);
            if (!$item) {
                set_alert('danger', 'Product not found.');
                redirect(admin_url('pos_system/inventory/items'));
            }
        }

        $categories  = $this->db->order_by('name', 'ASC')->get(db_prefix() . 'pos_product_categories')->result_array();
        $units       = $this->db->order_by('name', 'ASC')->get(db_prefix() . 'pos_inv_units')->result_array();
        $brands      = $this->db->where('is_active', 1)->order_by('name', 'ASC')->get(db_prefix() . 'pos_inv_brands')->result_array();
        $suppliers   = $this->db->where('is_active', 1)->order_by('name', 'ASC')->get(db_prefix() . 'pos_inv_suppliers')->result_array();
        $taxes       = $this->db->get(db_prefix() . 'taxes')->result_array();
        $all_branches= $this->pos_branches_model->get_all();

        // Attribute library for variations tab
        $attr_colors = $this->db->where('display', 1)->order_by('color_name')->get(db_prefix().'ware_color')->result_array();
        $attr_sizes  = $this->db->where('display', 1)->order_by('size_name')->get(db_prefix().'ware_size_type')->result_array();
        $attr_styles = $this->db->where('display', 1)->order_by('style_name')->get(db_prefix().'ware_style_type')->result_array();
        $attr_models = $this->db->order_by('name')->get(db_prefix().'wh_model')->result_array();

        // Existing variations & attr selections for edit
        $item_variations  = [];
        $item_attr_values = [];
        if ($id && $this->db->table_exists(db_prefix().'pos_item_variations')) {
            $item_variations = $this->db
                ->select('v.*, c.color_name, c.color_hex, sz.size_name, sz.size_symbol, st.style_name, m.name AS model_name')
                ->from(db_prefix().'pos_item_variations v')
                ->join(db_prefix().'ware_color c', 'c.color_id = v.color_id', 'left')
                ->join(db_prefix().'ware_size_type sz', 'sz.size_type_id = v.size_id', 'left')
                ->join(db_prefix().'ware_style_type st', 'st.style_type_id = v.style_id', 'left')
                ->join(db_prefix().'wh_model m', 'm.id = v.model_id', 'left')
                ->where('v.product_id', $id)
                ->order_by('v.sort_order, v.id')
                ->get()->result_array();
        }
        if ($id && $this->db->table_exists(db_prefix().'pos_item_attr_values')) {
            $rows = $this->db->where('product_id', $id)->get(db_prefix().'pos_item_attr_values')->result_array();
            foreach ($rows as $r) {
                $item_attr_values[$r['attr_type']][] = (int)$r['attr_value_id'];
            }
        }

        $this->load->view('pos_system/admin/inventory/item_form', [
            'title'             => $item ? 'Edit: ' . htmlspecialchars($item['name']) : 'New Product',
            'item'              => $item,
            'is_new'            => !$id,
            'categories'        => $categories,
            'units'             => $units,
            'brands'            => $brands,
            'suppliers'         => $suppliers,
            'taxes'             => $taxes,
            'all_branches'      => $all_branches,
            'branch_id'         => $branch_id,
            'branches'          => $branches,
            'attr_colors'       => $attr_colors,
            'attr_sizes'        => $attr_sizes,
            'attr_styles'       => $attr_styles,
            'attr_models'       => $attr_models,
            'item_variations'   => $item_variations,
            'item_attr_values'  => $item_attr_values,
        ]);
    }

    /**
     * AJAX save endpoint.
     * POST admin/pos_system/inv_item_save
     */
    public function inv_item_save(): void
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products', 'create') && !pos_perm('pos_products', 'edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $this->output->set_content_type('application/json');

        try {
            $id   = (int) $this->input->post('id');
            $data = $this->_parse_item_post();

            if ($id) {
                $this->pos_products_model->save_full($data, $id);
                $result_id = $id;
                $msg       = 'Product updated successfully.';
            } else {
                $result_id = $this->pos_products_model->save_full($data);
                $msg       = 'Product created successfully.';
            }

            echo json_encode([
                'success'  => true,
                'message'  => $msg,
                'id'       => $result_id,
                'edit_url' => admin_url('pos_system/inventory/item_form/' . $result_id),
            ]);
        } catch (Exception $e) {
            $this->output->set_status_header(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Image upload for item form.
     * POST admin/pos_system/inv_item_image_upload
     */
    public function inv_item_image_upload(): void
    {
        if (!pos_can_access('manager')) {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            return;
        }

        $this->output->set_content_type('application/json');

        if (empty($_FILES['image']['name'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
            return;
        }

        $config = [
            'upload_path'   => './uploads/pos_items/',
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size'      => 2048,
            'encrypt_name'  => true,
        ];

        if (!is_dir('./uploads/pos_items/')) {
            mkdir('./uploads/pos_items/', 0755, true);
        }

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('image')) {
            echo json_encode(['success' => false, 'error' => $this->upload->display_errors('', '')]);
            return;
        }

        $info = $this->upload->data();
        echo json_encode([
            'success' => true,
            'path'    => 'uploads/pos_items/' . $info['file_name'],
            'url'     => base_url('uploads/pos_items/' . $info['file_name']),
        ]);
    }

    /**
     * Parse all POST fields for the item form into a typed array.
     */
    private function _parse_item_post(): array
    {
        $scalars = [
            'name', 'item_code', 'sku', 'barcode', 'description',
            'category_id', 'unit_id', 'type', 'status',
            'brand_id', 'brand',
            'cost_price', 'selling_price', 'tax_rate_id',
            'reorder_point', 'max_stock', 'image',
            'is_active', 'is_pos_visible', 'is_tax_inclusive',
            'track_inventory', 'allow_negative', 'has_variations',
            // Extended fields
            'allow_alternative', 'is_fixed_asset',
            'over_delivery_allowance', 'over_billing_allowance',
            'valuation_method', 'valuation_rate',
            'shelf_life_days', 'warranty_days', 'end_of_life',
            'weight_per_unit', 'default_material_request_type', 'weight_uom_id',
            'has_batch_no', 'has_serial_no',
            'default_purchase_uom_id', 'lead_time_days', 'min_order_qty',
            'last_purchase_rate', 'safety_stock',
            'is_customer_provided', 'allow_purchase', 'drop_ship',
            'country_of_origin', 'customs_tariff_number',
            'income_account', 'expense_account',
            'apply_uoms_for_variants',
        ];

        $data = $this->input->post($scalars) ?: [];

        // Nullable integer FKs
        foreach (['category_id', 'unit_id', 'tax_rate_id', 'brand_id',
                  'weight_uom_id', 'default_purchase_uom_id'] as $f) {
            $data[$f] = ($data[$f] ?? '') !== '' ? (int) $data[$f] : null;
        }

        // Decimals
        foreach (['cost_price', 'selling_price', 'over_delivery_allowance',
                  'over_billing_allowance', 'valuation_rate', 'weight_per_unit',
                  'min_order_qty', 'last_purchase_rate', 'safety_stock',
                  'reorder_point', 'max_stock'] as $f) {
            $data[$f] = (float) ($data[$f] ?? 0);
        }

        // Integers
        foreach (['shelf_life_days', 'warranty_days', 'lead_time_days'] as $f) {
            $data[$f] = (int) ($data[$f] ?? 0);
        }

        // Boolean flags (0/1)
        foreach (['is_active', 'is_pos_visible', 'is_tax_inclusive', 'track_inventory',
                  'allow_negative', 'has_variations', 'allow_alternative', 'is_fixed_asset',
                  'has_batch_no', 'has_serial_no', 'is_customer_provided', 'allow_purchase',
                  'drop_ship', 'apply_uoms_for_variants'] as $f) {
            $data[$f] = (int) ($data[$f] ?? 0);
        }

        // Child tables
        $data['uoms']           = $this->input->post('uoms')           ?: [];
        $data['barcodes']       = $this->input->post('barcodes')       ?: [];
        $data['reorder_rules']  = $this->input->post('reorder_rules')  ?: [];
        $data['item_suppliers'] = $this->input->post('item_suppliers') ?: [];

        // Validate required
        if (empty(trim((string) ($data['name'] ?? '')))) {
            throw new Exception('Item Name is required.');
        }

        // Auto-generate item code if blank
        if (empty(trim((string) ($data['item_code'] ?? '')))) {
            $data['item_code'] = $this->pos_products_model->generate_item_code($data['name']);
        }

        return $data;
    }

    // ─── Inventory ────────────────────────────────────────────────────────────

    // ─── Inventory Sub-Module ─────────────────────────────────────────────────

    public function inventory($section = 'overview', $id = null)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory')) {
            $this->_pos_deny(); return;
        }
        switch ($section) {
            case 'items':           $this->_inv_items();           break;
            case 'categories':      $this->_inv_categories();      break;
            case 'stock_levels':    $this->_inv_stock_levels();    break;
            case 'batches':         $this->_inv_batches();         break;
            case 'variations':      $this->inv_variations();       break;
            case 'receiving':       $this->_inv_receiving($id);    break;
            case 'deliveries':      $this->_inv_deliveries($id);   break;
            case 'transfers':       $this->_inv_transfers($id);    break;
            case 'adjustments':     $this->_inv_adjustments($id);  break;
            case 'returns':         $this->_inv_returns($id);      break;
            case 'packing':         $this->_inv_packing($id);      break;
            case 'stocktake':       $this->_inv_stocktake($id);    break;
            case 'report_summary':  $this->_inv_report('summary'); break;
            case 'report_balance':  $this->_inv_report('balance'); break;
            case 'report_movements':$this->_inv_report('movements');break;
            case 'report_valuation':$this->_inv_report('valuation');break;
            case 'config_units':    $this->_inv_config('units');   break;
            case 'config_brands':   $this->_inv_config('brands');  break;
            case 'config_suppliers':$this->_inv_config('suppliers');break;
            case 'config_settings':   $this->_inv_config('settings');                    break;
            case 'config_attributes': $this->_inv_config_attributes();                break;
            case 'history':           $this->_inv_history();                          break;
            case 'item_form':         $this->_inv_item_form($id ? (int)$id : null);   break;
            default:                  $this->_inv_overview();                         break;
        }
    }

    private function _inv_common_data()
    {
        $branch_id = (int)($this->input->get('branch_id') ?: pos_get_auth_branch() ?: pos_get_staff_branch() ?: 0);
        $branches  = $this->pos_branches_model->get_all(['is_active' => 1]);
        if (!$branch_id && !empty($branches)) {
            $branch_id = (int)$branches[0]['id'];
        }
        return [$branch_id, $branches];
    }

    private function _inv_overview()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $db = $this->db;

        $filter_branch = pos_get_auth_branch(); // null=admin, 0=none, int=specific

        // Total products
        $total_products = $db->count_all_results(db_prefix() . 'pos_products');

        // In stock / low / out (scoped if non-admin)
        $inv_q = $db->select('i.product_id, SUM(i.quantity) AS qty, p.reorder_point')
                    ->from(db_prefix() . 'pos_inventory i')
                    ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id');
        if ($filter_branch !== null) $inv_q->where('i.branch_id', $filter_branch ?: $branch_id);
        $inv_rows = $inv_q->group_by('i.product_id')->get()->result_array();

        $in_stock = $low_stock_count = $out_of_stock = 0;
        foreach ($inv_rows as $r) {
            $qty = (float)$r['qty'];
            $min = (float)($r['reorder_point'] ?? 0);
            if ($qty <= 0) $out_of_stock++;
            elseif ($min > 0 && $qty <= $min) $low_stock_count++;
            else $in_stock++;
        }

        // Pending receipts / deliveries
        $pending_receipts  = $db->where('status', 'draft')->count_all_results(db_prefix() . 'pos_inv_receipts');
        $pending_deliveries= $db->where('status', 'draft')->count_all_results(db_prefix() . 'pos_inv_deliveries');

        // Recent receipts
        $recent_receipts = $db->select('*')->order_by('date_created', 'DESC')->limit(5)
                              ->get(db_prefix() . 'pos_inv_receipts')->result_array();

        // Low stock items
        $low_q = $db->select('p.name, b.name AS branch_name, i.quantity, p.reorder_point')
                    ->from(db_prefix() . 'pos_inventory i')
                    ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
                    ->join(db_prefix() . 'pos_branches b', 'b.id = i.branch_id')
                    ->where('p.track_inventory', 1)
                    ->group_start()
                        ->where('i.quantity <= 0')
                        ->or_where('(p.reorder_point > 0 AND i.quantity <= p.reorder_point)')
                    ->group_end()
                    ->order_by('i.quantity', 'ASC')->limit(10);
        if ($filter_branch !== null) $low_q->where('i.branch_id', $filter_branch ?: $branch_id);
        $low_stock_items = $low_q->get()->result_array();

        // Branch stats
        $branch_stats = [];
        foreach ($branches as $b) {
            if ($filter_branch !== null && (int)$filter_branch !== (int)$b['id']) continue;
            $bid  = (int)$b['id'];
            $rows = $db->select('SUM(i.quantity>0) AS total_skus')
                       ->from(db_prefix() . 'pos_inventory i')
                       ->where('i.branch_id', $bid)->get()->row_array();
            $low  = $db->select('COUNT(*) AS cnt')->from(db_prefix() . 'pos_inventory i')
                       ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
                       ->where('i.branch_id', $bid)->where('p.reorder_point > 0')
                       ->where('i.quantity >', 0)->where('i.quantity <=', 'p.reorder_point', false)
                       ->get()->row_array();
            $out  = $db->select('COUNT(*) AS cnt')->from(db_prefix() . 'pos_inventory i')
                       ->where('i.branch_id', $bid)->where('i.quantity <=', 0)->get()->row_array();
            $branch_stats[] = array_merge($b, [
                'total_skus'   => (int)($rows['total_skus'] ?? 0),
                'low_stock'    => (int)($low['cnt'] ?? 0),
                'out_of_stock' => (int)($out['cnt'] ?? 0),
            ]);
        }

        $this->load->view('pos_system/admin/inventory/overview', [
            'title'            => 'Inventory',
            'branch_id'        => $branch_id,
            'branches'         => $branches,
            'stats'            => [
                'total_products'    => $total_products,
                'in_stock'          => $in_stock,
                'low_stock'         => $low_stock_count,
                'out_of_stock'      => $out_of_stock,
                'pending_receipts'  => $pending_receipts,
                'pending_deliveries'=> $pending_deliveries,
            ],
            'branch_stats'     => $branch_stats,
            'recent_receipts'  => $recent_receipts,
            'low_stock_items'  => $low_stock_items,
        ]);
    }

    private function _inv_items()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $categories = $this->db->get(db_prefix() . 'pos_product_categories')->result_array();
        $brands     = $this->db->where('is_active', 1)->get(db_prefix() . 'pos_inv_brands')->result_array();
        $units      = $this->db->get(db_prefix() . 'pos_inv_units')->result_array();
        $suppliers  = $this->db->where('is_active', 1)->get(db_prefix() . 'pos_inv_suppliers')->result_array();
        $taxes      = $this->db->get(db_prefix() . 'taxes')->result_array();
        $this->load->view('pos_system/admin/inventory/items', compact('branch_id','branches','categories','brands','units','suppliers','taxes') + ['title' => 'Products']);
    }

    private function _inv_categories(): void
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $all_cats = $this->db->order_by('name', 'ASC')
                             ->get(db_prefix() . 'pos_product_categories')
                             ->result_array();
        $this->load->view('pos_system/admin/inventory/categories', [
            'title'          => 'Product Categories',
            'inv_section'    => 'categories',
            'branch_id'      => $branch_id,
            'branches'       => $branches,
            'all_categories' => $all_cats,
            'save_url'       => admin_url('pos_system/inv_cat_save'),
            'ajax_url'       => admin_url('pos_system/inv_ajax/categories'),
        ]);
    }

    /** POST admin/pos_system/inv_cat_save — create or update a product category */
    public function inv_cat_save(): void
    {
        header('Content-Type: application/json');
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'edit')) {
            echo json_encode(['success' => false, 'error' => 'Forbidden']); return;
        }
        $id   = (int)$this->input->post('id');
        $name = trim($this->input->post('name'));
        if ($name === '') { echo json_encode(['success' => false, 'error' => 'Category name is required.']); return; }

        // Generate unique slug
        $slug_base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $slug = $slug_base; $i = 0;
        while (true) {
            $q = $this->db->where('slug', $slug);
            if ($id) { $q->where('id !=', $id); }
            if (!$q->count_all_results(db_prefix() . 'pos_product_categories')) break;
            $slug = $slug_base . '-' . (++$i);
        }

        $row = [
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => ($this->input->post('parent_id') !== '' && $this->input->post('parent_id') !== null)
                               ? (int)$this->input->post('parent_id') : null,
            'description' => $this->input->post('description') ?: null,
            'sort_order'  => (int)$this->input->post('sort_order'),
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update(db_prefix() . 'pos_product_categories', $row);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Category updated.']);
        } else {
            $this->db->insert(db_prefix() . 'pos_product_categories', $row);
            echo json_encode(['success' => true, 'id' => $this->db->insert_id(), 'message' => 'Category created.']);
        }
    }

    private function _inv_stock_levels()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/stock_levels', [
            'title' => 'Current Stock Levels', 'inv_section' => 'stock_levels',
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_batches()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Batch / Serial / LOT Register', 'inv_section' => 'batches',
            'page_title' => 'Batch / Serial / LOT Register', 'page_icon' => 'fa-layer-group',
            'new_btn_label' => 'New Receipt', 'new_url' => admin_url('pos_system/inv_form/receipt'),
            'columns' => [
                ['key' => 'tracking_type',      'label' => 'Type',             'width' => 90],
                ['key' => 'batch_number',       'label' => 'Batch / Serial #', 'width' => 140],
                ['key' => 'product_name',       'label' => 'Product',          'width' => 170],
                ['key' => 'sku',                'label' => 'SKU',              'width' => 110],
                ['key' => 'branch_name',        'label' => 'Branch',           'width' => 120],
                ['key' => 'quantity',           'label' => 'Qty',  'right' => true, 'width' => 80],
                ['key' => 'unit_cost',          'label' => 'Unit Cost', 'right' => true, 'width' => 110],
                ['key' => 'total_value',        'label' => 'Total Value', 'right' => true, 'width' => 120],
                ['key' => 'manufacture_date_fmt','label' => 'Mfg Date',        'width' => 100],
                ['key' => 'expiry_date',        'label' => 'Expiry',           'width' => 130],
                ['key' => 'supplier',           'label' => 'Supplier',         'width' => 140],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search',    'label'=>'Search…'],
                ['type'=>'select','name'=>'exp_status', 'label'=>'Expiry Status',
                 'options'=>['fresh'=>'Fresh','expiring_soon'=>'Expiring Soon','expired'=>'Expired']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/batches'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_receiving($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Receipts', 'inv_section' => 'receiving',
            'page_title' => 'Inventory Receipts (Receiving Vouchers)', 'page_icon' => 'fa-truck-loading',
            'new_btn_label' => 'New Receipt', 'new_url' => admin_url('pos_system/inv_form/receipt'),
            'columns' => [
                ['key' => 'receipt_number',   'label' => 'Receipt #',        'width' => 120],
                ['key' => 'invoice_number',   'label' => 'Invoice #',        'width' => 110],
                ['key' => 'docket_number',    'label' => 'Docket #',         'width' => 110],
                ['key' => 'po_number',        'label' => 'PO #',             'width' => 100],
                ['key' => 'supplier_name',    'label' => 'Supplier',         'width' => 160],
                ['key' => 'branch_name',      'label' => 'Branch',           'width' => 120],
                ['key' => 'type',             'label' => 'Type',             'width' => 100],
                ['key' => 'receipt_date',     'label' => 'Receipt Date',     'width' => 110],
                ['key' => 'items_count',      'label' => 'Items',            'width' => 60],
                ['key' => 'total_amount',     'label' => 'Total', 'right' => true, 'width' => 120],
                ['key' => 'note',             'label' => 'Notes',            'width' => 180],
                ['key' => 'status_badge',     'label' => 'Status',           'width' => 90],
                ['key' => 'date_created_fmt', 'label' => 'Created At',       'width' => 130],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search', 'label'=>'Search…'],
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','confirmed'=>'Confirmed','cancelled'=>'Cancelled']],
                ['type'=>'date',  'name'=>'date_from', 'label'=>'From'],
                ['type'=>'date',  'name'=>'date_to',   'label'=>'To'],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/receipts'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_deliveries($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $status_f = $this->input->get('status') ?: '';
        $search   = $this->input->get('search') ?: '';

        $all_dn = $this->db->select('id, status, total_amount')
            ->get(db_prefix().'pos_inv_deliveries')->result_array();
        $dn_stats = ['draft'=>0,'confirmed'=>0,'delivered'=>0,'cancelled'=>0];
        $dn_total = 0;
        foreach ($all_dn as $r) {
            if (isset($dn_stats[$r['status']])) $dn_stats[$r['status']]++;
            $dn_total += (float)$r['total_amount'];
        }

        $qb = $this->db
            ->select('d.id, d.delivery_number, d.customer_name, d.delivery_date,
                      d.total_amount, d.status, d.date_created, d.sales_order_id,
                      b.name AS branch_name')
            ->from(db_prefix().'pos_inv_deliveries d')
            ->join(db_prefix().'pos_branches b', 'b.id = d.branch_id', 'left');
        if ($branch_id) $qb->where('d.branch_id', $branch_id);
        if ($status_f)  $qb->where('d.status', $status_f);
        if ($search)    $qb->group_start()->like('d.delivery_number', $search)->or_like('d.customer_name', $search)->group_end();
        $deliveries = $qb->order_by('d.date_created', 'DESC')->get()->result_array();

        $this->load->view('pos_system/admin/inventory/deliveries_list', [
            'title'          => 'Delivery Notes',
            'branch_id'      => $branch_id,
            'branches'       => $branches,
            'deliveries'     => $deliveries,
            'dn_stats'       => $dn_stats,
            'dn_total'       => $dn_total,
            'total_count'    => count($all_dn),
            'current_status' => $status_f,
            'current_search' => $search,
        ]);
    }

    private function _inv_transfers($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Internal Transfers', 'inv_section' => 'transfers',
            'page_title' => 'Internal Stock Transfers', 'page_icon' => 'fa-exchange-alt',
            'new_btn_label' => 'New Transfer', 'new_url' => admin_url('pos_system/inv_form/transfer'),
            'columns' => [
                ['key' => 'transfer_number',  'label' => 'Transfer #',       'width' => 130],
                ['key' => 'from_branch',      'label' => 'From Branch',      'width' => 130],
                ['key' => 'to_branch',        'label' => 'To Branch',        'width' => 130],
                ['key' => 'transfer_date',    'label' => 'Transfer Date',    'width' => 110],
                ['key' => 'items_count',      'label' => 'Items',            'width' => 60],
                ['key' => 'notes',            'label' => 'Notes',            'width' => 200],
                ['key' => 'status_badge',     'label' => 'Status',           'width' => 100],
                ['key' => 'date_created_fmt', 'label' => 'Created At',       'width' => 130],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search', 'label'=>'Search…'],
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','in_transit'=>'In Transit','completed'=>'Completed','cancelled'=>'Cancelled']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/transfers'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_adjustments($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Loss & Adjustment', 'inv_section' => 'adjustments',
            'page_title' => 'Loss & Adjustment', 'page_icon' => 'fa-sliders-h',
            'new_btn_label' => 'New Adjustment', 'new_url' => admin_url('pos_system/inv_form/adjustment'),
            'columns' => [
                ['key' => 'adjustment_number', 'label' => 'Ref #',            'width' => 130],
                ['key' => 'branch_name',       'label' => 'Branch',           'width' => 120],
                ['key' => 'type_badge',        'label' => 'Type',             'width' => 90],
                ['key' => 'adjustment_date',   'label' => 'Adj. Date',        'width' => 100],
                ['key' => 'items_count',       'label' => 'Items',            'width' => 60],
                ['key' => 'reason',            'label' => 'Reason',           'width' => 190],
                ['key' => 'notes',             'label' => 'Notes',            'width' => 180],
                ['key' => 'status_badge',      'label' => 'Status',           'width' => 90],
                ['key' => 'date_created_fmt',  'label' => 'Created At',       'width' => 130],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search', 'label'=>'Search…'],
                ['type'=>'select','name'=>'type',   'label'=>'All Types',
                 'options'=>['loss'=>'Loss','gain'=>'Gain','correction'=>'Correction']],
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','confirmed'=>'Confirmed','cancelled'=>'Cancelled']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/adjustments'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_returns($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Return Orders', 'inv_section' => 'returns',
            'page_title' => 'Return Orders', 'page_icon' => 'fa-undo-alt',
            'new_btn_label' => 'New Return', 'new_url' => admin_url('pos_system/inv_form/return'),
            'columns' => [
                ['key' => 'return_number',    'label' => 'Return #',          'width' => 120],
                ['key' => 'branch_name',      'label' => 'Branch',            'width' => 120],
                ['key' => 'type_badge',       'label' => 'Type',              'width' => 100],
                ['key' => 'return_date',      'label' => 'Return Date',       'width' => 100],
                ['key' => 'reference',        'label' => 'Reference',         'width' => 110],
                ['key' => 'items_count',      'label' => 'Items',             'width' => 60],
                ['key' => 'total_amount_fmt', 'label' => 'Total', 'right' => true, 'width' => 120],
                ['key' => 'notes',            'label' => 'Notes',             'width' => 180],
                ['key' => 'status_badge',     'label' => 'Status',            'width' => 90],
                ['key' => 'date_created_fmt', 'label' => 'Created At',        'width' => 130],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search', 'label'=>'Search…'],
                ['type'=>'select','name'=>'type',   'label'=>'All Types',
                 'options'=>['customer'=>'Customer Return','supplier'=>'Supplier Return']],
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','confirmed'=>'Confirmed','cancelled'=>'Cancelled']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/returns'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_packing($id = null)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Packing Lists', 'inv_section' => 'packing',
            'page_title' => 'Packing Lists', 'page_icon' => 'fa-box',
            'new_btn_label' => 'New Packing List', 'new_url' => admin_url('pos_system/inv_form/packing'),
            'columns' => [
                ['key' => 'packing_number',  'label' => 'Packing #',          'width' => 130],
                ['key' => 'branch_name',     'label' => 'Branch',             'width' => 120],
                ['key' => 'delivery_ref',    'label' => 'Delivery Ref',       'width' => 110],
                ['key' => 'packing_date',    'label' => 'Pack Date',          'width' => 100],
                ['key' => 'items_count',     'label' => 'Items',              'width' => 60],
                ['key' => 'notes',           'label' => 'Notes',              'width' => 200],
                ['key' => 'status_badge',    'label' => 'Status',             'width' => 90],
                ['key' => 'date_created_fmt','label' => 'Created At',         'width' => 130],
            ],
            'filters' => [
                ['type'=>'text',  'name'=>'search', 'label'=>'Search…'],
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','packed'=>'Packed','dispatched'=>'Dispatched','delivered'=>'Delivered']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/packing'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _ensure_delivery_schema()
    {
        $db  = $this->db;
        $dlv = db_prefix() . 'pos_inv_deliveries';
        $dli = db_prefix() . 'pos_inv_delivery_items';
        if ($db->table_exists($dlv)) {
            $cols = [
                'fulfillment_status' => "ADD COLUMN `fulfillment_status` ENUM('draft','validated','shipped','delivered') NOT NULL DEFAULT 'draft'",
                'invoiced_status'    => "ADD COLUMN `invoiced_status` ENUM('uninvoiced','partially_invoiced','fully_invoiced') NOT NULL DEFAULT 'uninvoiced'",
                'shipped_at'         => "ADD COLUMN `shipped_at` DATETIME DEFAULT NULL",
                'tracking_number'    => "ADD COLUMN `tracking_number` VARCHAR(100) DEFAULT NULL",
                'carrier_info'       => "ADD COLUMN `carrier_info` VARCHAR(200) DEFAULT NULL",
                'dispatched_by'      => "ADD COLUMN `dispatched_by` INT(11) UNSIGNED DEFAULT NULL",
            ];
            foreach ($cols as $col => $def) {
                if (!$db->field_exists($col, $dlv)) { $db->query("ALTER TABLE `{$dlv}` {$def}"); }
            }
            // Sync fulfillment_status for existing rows from legacy status column
            if ($db->field_exists('fulfillment_status', $dlv) && $db->field_exists('status', $dlv)) {
                $db->query("UPDATE `{$dlv}` SET `fulfillment_status`='validated' WHERE `status`='confirmed' AND `fulfillment_status`='draft'");
                $db->query("UPDATE `{$dlv}` SET `fulfillment_status`='shipped'   WHERE `status`='delivered'  AND `fulfillment_status` IN ('draft','validated')");
            }
        }
        if ($db->table_exists($dli)) {
            $icols = [
                'batch_no'     => "ADD COLUMN `batch_no` VARCHAR(100) DEFAULT NULL",
                'serial_no'    => "ADD COLUMN `serial_no` VARCHAR(200) DEFAULT NULL",
                'is_drop_ship' => "ADD COLUMN `is_drop_ship` TINYINT(1) NOT NULL DEFAULT 0",
            ];
            foreach ($icols as $col => $def) {
                if (!$db->field_exists($col, $dli)) { $db->query("ALTER TABLE `{$dli}` {$def}"); }
            }
        }
    }

    private function _inv_get_qty($branch_id, $product_id, $db)
    {
        $row = $db->where('branch_id', $branch_id)->where('product_id', $product_id)
                  ->get(db_prefix().'pos_inventory')->row_array();
        return (float)($row['quantity'] ?? 0);
    }

    private function _inv_ajax_delivery_dispatch($section)
    {
        $this->_ensure_delivery_schema();
        $db       = $this->db;
        $staff_id = get_staff_user_id();
        $now      = date('Y-m-d H:i:s');

        switch ($section) {
            case 'delivery_dispatch':
                if (!pos_can_access('manager')) {
                    echo json_encode(['success'=>false,'error'=>'Manager role required to dispatch.']); return;
                }
                $doc_id = (int)$this->input->post('doc_id');
                if (!$doc_id) { echo json_encode(['success'=>false,'error'=>'Missing delivery ID']); return; }

                $dlv = $db->where('id', $doc_id)->get(db_prefix().'pos_inv_deliveries')->row_array();
                if (!$dlv) { echo json_encode(['success'=>false,'error'=>'Delivery note not found']); return; }

                if (in_array($dlv['fulfillment_status'] ?? '', ['shipped','delivered'])) {
                    echo json_encode(['success'=>false,'error'=>'This delivery has already been dispatched.']); return;
                }
                $tracking = $this->input->post('tracking_number') ?: null;
                $carrier  = $this->input->post('carrier_info')    ?: null;

                // Load items
                $items = $db->where('delivery_id', $doc_id)->get(db_prefix().'pos_inv_delivery_items')->result_array();
                if (empty($items)) { echo json_encode(['success'=>false,'error'=>'No line items on this delivery.']); return; }

                // Availability check (skip drop-ship items)
                $errors = [];
                foreach ($items as $item) {
                    if (!empty($item['is_drop_ship'])) continue;
                    $br      = (int)($item['branch_id'] ?? $dlv['branch_id'] ?? 0);
                    $pid     = (int)$item['product_id'];
                    $qty_req = (float)$item['quantity'];
                    $avail   = $this->_inv_get_qty($br, $pid, $db);
                    if ($avail < $qty_req) {
                        $prod = $db->where('id', $pid)->get(db_prefix().'pos_products')->row_array();
                        $errors[] = ($prod['name'] ?? "Product #{$pid}") . ": need {$qty_req}, available {$avail}";
                    }
                }
                if (!empty($errors)) {
                    echo json_encode(['success'=>false,'error'=>'Insufficient stock:<br>' . implode('<br>', $errors)]); return;
                }

                // Stock was NOT yet deducted if status is still 'draft'
                $already_applied = !in_array($dlv['status'] ?? 'draft', ['draft']);

                $db->trans_start();
                if (!$already_applied) {
                    foreach ($items as $item) {
                        if (!empty($item['is_drop_ship'])) continue;
                        $br  = (int)($item['branch_id'] ?? $dlv['branch_id'] ?? 0);
                        $pid = (int)$item['product_id'];
                        $qty_change = -(float)$item['quantity'];
                        $this->_inv_update_qty($br, $pid, $qty_change, $db);
                        $this->_inv_log_movement($br, $pid, 'sale', $qty_change, $doc_id, 'pos_inv_deliveries', (float)$item['unit_price'], $staff_id, $now, $db);
                    }
                }
                // Update SO status
                $so_id = (int)($dlv['sales_order_id'] ?? 0);
                if ($so_id && $db->table_exists(db_prefix().'pos_sales_orders') && $db->table_exists(db_prefix().'pos_sales_order_items')) {
                    $so_items = $db->where('sales_order_id', $so_id)->get(db_prefix().'pos_sales_order_items')->result_array();
                    $fully = true;
                    foreach ($so_items as $soi) {
                        if ((float)$soi['qty_delivered'] < (float)$soi['qty_ordered']) { $fully = false; break; }
                    }
                    $db->where('id', $so_id)->update(db_prefix().'pos_sales_orders', [
                        'status' => $fully ? 'delivered' : 'partially_delivered'
                    ]);
                }
                // Flip delivery to shipped
                $db->where('id', $doc_id)->update(db_prefix().'pos_inv_deliveries', [
                    'fulfillment_status' => 'shipped',
                    'status'             => 'confirmed',
                    'shipped_at'         => $now,
                    'tracking_number'    => $tracking,
                    'carrier_info'       => $carrier,
                    'dispatched_by'      => $staff_id,
                ]);
                $db->trans_complete();
                if ($db->trans_status() === false) {
                    echo json_encode(['success'=>false,'error'=>'Database transaction failed. No changes made.']); return;
                }
                echo json_encode([
                    'success'    => true,
                    'message'    => 'Delivery dispatched successfully.',
                    'pdf_url'    => admin_url('pos_system/delivery_pdf/' . $doc_id),
                    'shipped_at' => $now,
                    'csrf_hash'  => $this->security->get_csrf_hash(),
                ]);
                return;

            case 'delivery_avail':
                $doc_id = (int)$this->input->get('doc_id');
                if (!$doc_id) { echo json_encode(['items'=>[]]); return; }
                $dlv   = $db->where('id', $doc_id)->get(db_prefix().'pos_inv_deliveries')->row_array();
                $items = $db->where('delivery_id', $doc_id)->get(db_prefix().'pos_inv_delivery_items')->result_array();
                $result = [];
                foreach ($items as $item) {
                    $br    = (int)($item['branch_id'] ?? ($dlv['branch_id'] ?? 0));
                    $pid   = (int)$item['product_id'];
                    $avail = $this->_inv_get_qty($br, $pid, $db);
                    $prod  = $db->where('id', $pid)->get(db_prefix().'pos_products')->row_array();
                    $result[] = [
                        'product_id'   => $pid,
                        'product_name' => $prod['name'] ?? "Product #{$pid}",
                        'qty_needed'   => (float)$item['quantity'],
                        'qty_avail'    => $avail,
                        'ok'           => $avail >= (float)$item['quantity'],
                        'is_drop_ship' => !empty($item['is_drop_ship']),
                    ];
                }
                echo json_encode(['items'=>$result]);
                return;
        }
    }

    private function _ensure_stocktake_schema()
    {
        $db  = $this->db;
        $stk = db_prefix() . 'pos_inv_stock_takes';
        $sti = db_prefix() . 'pos_inv_stock_take_items';

        if ($db->table_exists($stk)) {
            $stk_cols = [
                'date_updated'        => "ADD COLUMN `date_updated` DATETIME DEFAULT NULL",
                'scope'               => "ADD COLUMN `scope` ENUM('full','category','product') NOT NULL DEFAULT 'full' AFTER `notes`",
                'scope_filter'        => "ADD COLUMN `scope_filter` TEXT DEFAULT NULL AFTER `scope`",
                'blind_counting'      => "ADD COLUMN `blind_counting` TINYINT(1) NOT NULL DEFAULT 0 AFTER `scope_filter`",
                'approved_by'         => "ADD COLUMN `approved_by` INT(11) UNSIGNED DEFAULT NULL AFTER `blind_counting`",
                'snapshot_timestamp'  => "ADD COLUMN `snapshot_timestamp` DATETIME DEFAULT NULL AFTER `approved_by`",
                'freeze_active'       => "ADD COLUMN `freeze_active` TINYINT(1) NOT NULL DEFAULT 0 AFTER `snapshot_timestamp`",
                'variance_threshold'  => "ADD COLUMN `variance_threshold` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `freeze_active`",
                'items_counted'       => "ADD COLUMN `items_counted` INT(11) NOT NULL DEFAULT 0 AFTER `variance_threshold`",
                'total_items'         => "ADD COLUMN `total_items` INT(11) NOT NULL DEFAULT 0 AFTER `items_counted`",
            ];
            foreach ($stk_cols as $col => $def) {
                if (!$db->field_exists($col, $stk)) {
                    $db->query("ALTER TABLE `{$stk}` {$def}");
                }
            }
        }
        if ($db->table_exists($sti)) {
            $sti_cols = [
                'qty_system'        => "ADD COLUMN `qty_system` DECIMAL(10,2) NOT NULL DEFAULT 0.00",
                'qty_counted'       => "ADD COLUMN `qty_counted` DECIMAL(10,2) DEFAULT NULL",
                'variance'          => "ADD COLUMN `variance` DECIMAL(10,2) DEFAULT NULL",
                'notes'             => "ADD COLUMN `notes` TEXT DEFAULT NULL",
                'bin_location'      => "ADD COLUMN `bin_location` VARCHAR(100) DEFAULT NULL",
                'counted_by'        => "ADD COLUMN `counted_by` INT(11) UNSIGNED DEFAULT NULL",
                'counted_at'        => "ADD COLUMN `counted_at` DATETIME DEFAULT NULL",
                'unit_cost'         => "ADD COLUMN `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0.0000",
                'reason_code'       => "ADD COLUMN `reason_code` VARCHAR(50) DEFAULT NULL",
                'recount_requested' => "ADD COLUMN `recount_requested` TINYINT(1) NOT NULL DEFAULT 0",
                'recount_count'     => "ADD COLUMN `recount_count` INT(11) NOT NULL DEFAULT 0",
                'is_found_stock'    => "ADD COLUMN `is_found_stock` TINYINT(1) NOT NULL DEFAULT 0",
                'counted_qty'       => "ADD COLUMN `counted_qty` DECIMAL(10,2) DEFAULT NULL",
            ];
            foreach ($sti_cols as $col => $def) {
                if (!$db->field_exists($col, $sti)) {
                    $db->query("ALTER TABLE `{$sti}` {$def}");
                }
            }
        }
        if (!$db->table_exists(db_prefix() . 'pos_inv_stocktake_counts')) {
            $db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_stocktake_counts` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `stock_take_id` INT(11) UNSIGNED NOT NULL,
                `product_id` INT(11) UNSIGNED NOT NULL,
                `bin_location` VARCHAR(100) DEFAULT NULL,
                `counted_qty` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `counter_user_id` INT(11) UNSIGNED NOT NULL,
                `device_id` VARCHAR(100) DEFAULT NULL,
                `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `synced_at` DATETIME DEFAULT NULL,
                `is_offline` TINYINT(1) NOT NULL DEFAULT 0,
                `notes` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_stc_session` (`stock_take_id`),
                INDEX `idx_stc_product` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }
        if (!$db->table_exists(db_prefix() . 'pos_inv_audit_ledger')) {
            $db->query('CREATE TABLE `' . db_prefix() . 'pos_inv_audit_ledger` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref_type` VARCHAR(50) NOT NULL,
                `ref_id` INT(11) UNSIGNED NOT NULL,
                `branch_id` INT(11) UNSIGNED NOT NULL,
                `product_id` INT(11) UNSIGNED NOT NULL,
                `qty_before` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `qty_after` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `qty_variance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `financial_impact` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `reason_code` VARCHAR(50) DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `posted_by` INT(11) UNSIGNED NOT NULL,
                `posted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_al_ref` (`ref_type`, `ref_id`),
                INDEX `idx_al_branch` (`branch_id`),
                INDEX `idx_al_product` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }
    }

    private function _inv_stocktake($id = null)
    {
        $this->_ensure_stocktake_schema();
        [$branch_id, $branches] = $this->_inv_common_data();
        $db = $this->db;

        // Summary stats for dashboard cards
        $all = $db->select('status')->get(db_prefix().'pos_inv_stock_takes')->result_array();
        $stats = ['draft'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0];
        foreach ($all as $r) { if (isset($stats[$r['status']])) $stats[$r['status']]++; }

        // Recent sessions (last 50) — columns guaranteed to exist after migration above
        $sessions = $db->select('s.id, s.stocktake_number, s.start_date, s.end_date, s.status,
                                  s.scope, s.blind_counting, s.total_items, s.items_counted,
                                  s.date_created, s.freeze_active,
                                  b.name AS branch_name,
                                  CONCAT(st.firstname," ",st.lastname) AS created_by_name')
            ->from(db_prefix().'pos_inv_stock_takes s')
            ->join(db_prefix().'pos_branches b', 'b.id = s.branch_id', 'left')
            ->join(db_prefix().'staff st', 'st.staffid = s.created_by', 'left')
            ->order_by('s.date_created', 'DESC')
            ->limit(50)->get()->result_array();

        $categories = $db->order_by('name')->get(db_prefix().'pos_product_categories')->result_array();

        $this->load->view('pos_system/admin/inventory/stocktake', [
            'title'      => 'Physical Inventory',
            'inv_section'=> 'stocktake',
            'branch_id'  => $branch_id,
            'branches'   => $branches,
            'stats'      => $stats,
            'sessions'   => $sessions,
            'categories' => $categories,
        ]);
        return;

        // Legacy _list_page fallback (unreachable — kept for reference)
        $this->load->view('pos_system/admin/inventory/_list_page', [
            'title' => 'Physical Inventory', 'inv_section' => 'stocktake',
            'page_title' => 'Physical Inventory (Stock Take)', 'page_icon' => 'fa-clipboard-check',
            'new_btn_label' => 'Start Stock Take', 'new_url' => admin_url('pos_system/inv_form/stocktake'),
            'columns' => [
                ['key' => 'stocktake_number', 'label' => 'Ref #',            'width' => 130],
                ['key' => 'branch_name',      'label' => 'Branch',           'width' => 120],
                ['key' => 'start_date',       'label' => 'Start Date',       'width' => 100],
                ['key' => 'end_date',         'label' => 'End Date',         'width' => 100],
                ['key' => 'item_count',       'label' => 'Items Counted',    'width' => 100],
                ['key' => 'variance_items',   'label' => 'Variances',        'width' => 90],
                ['key' => 'notes',            'label' => 'Notes',            'width' => 200],
                ['key' => 'status_badge',     'label' => 'Status',           'width' => 100],
                ['key' => 'date_created_fmt', 'label' => 'Created At',       'width' => 130],
            ],
            'filters' => [
                ['type'=>'select','name'=>'status', 'label'=>'All Status',
                 'options'=>['draft'=>'Draft','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled']],
            ],
            'ajax_url' => admin_url('pos_system/inv_ajax/stocktakes'),
            'branch_id' => $branch_id, 'branches' => $branches,
        ]);
    }

    private function _inv_report($type)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $titles = [
            'summary'   => ['Stock Summary',         'fa-chart-pie'],
            'balance'   => ['Stock Balance',          'fa-balance-scale'],
            'movements' => ['Stock Movements',        'fa-arrows-alt-v'],
            'valuation' => ['Inventory Valuation',    'fa-coins'],
        ];
        [$report_title, $report_icon] = $titles[$type] ?? ['Report', 'fa-chart-bar'];
        $this->load->view('pos_system/admin/inventory/report', [
            'title'        => $report_title,
            'inv_section'  => 'report_' . $type,
            'report_type'  => $type,
            'report_title' => $report_title,
            'report_icon'  => $report_icon,
            'branch_id'    => $branch_id,
            'branches'     => $branches,
        ]);
    }

    private function _inv_history()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $this->load->view('pos_system/admin/inventory/history', [
            'title'      => 'Inventory History',
            'branch_id'  => $branch_id,
            'branches'   => $branches,
        ]);
    }

    private function _inv_config_attributes()
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $commodity_groups = $this->db->select('id, name, commodity_group_code')->from(db_prefix().'items_groups')->where('display', 1)->order_by('name')->get()->result_array();
        $brands           = $this->db->select('id, name')->from(db_prefix().'wh_brand')->order_by('name')->get()->result_array();
        $this->load->view('pos_system/admin/inventory/config_attributes', [
            'title'            => 'Attribute Management',
            'branch_id'        => $branch_id,
            'branches'         => $branches,
            'commodity_groups' => $commodity_groups,
            'brands'           => $brands,
        ]);
    }

    private function _inv_config($type)
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $configs = [
            'units'     => ['Units of Measure', 'fa-ruler',      'pos_inv_units',
                            [['key'=>'name_sym','label'=>'Name / Symbol'],['key'=>'item_count','label'=>'Products']]],
            'brands'    => ['Brands',            'fa-trademark',  'pos_inv_brands',
                            [['key'=>'name','label'=>'Brand Name'],['key'=>'product_count','label'=>'Products'],['key'=>'status','label'=>'Status']]],
            'suppliers' => ['Suppliers',         'fa-handshake',  'pos_inv_suppliers',
                            [['key'=>'name','label'=>'Supplier'],['key'=>'contact','label'=>'Contact'],['key'=>'email','label'=>'Email'],['key'=>'phone','label'=>'Phone'],['key'=>'status','label'=>'Status']]],
            'settings'  => ['Inventory Settings','fa-sliders-h',  null, []],
        ];
        [$cfg_title, $cfg_icon] = $configs[$type] ?? ['Config', 'fa-cog', null, []];
        $extra = [];
        if ($type === 'settings') {
            $extra['staff_list'] = $this->db
                ->select('staffid, firstname, lastname')
                ->where('active', 1)
                ->order_by('firstname', 'ASC')
                ->get(db_prefix() . 'staff')
                ->result_array();
        }
        $this->load->view('pos_system/admin/inventory/config_' . $type, array_merge([
            'title'        => $cfg_title,
            'inv_section'  => 'config_' . $type,
            'config_type'  => $type,
            'config_title' => $cfg_title,
            'config_icon'  => $cfg_icon,
            'branch_id'    => $branch_id,
            'branches'     => $branches,
        ], $extra));
    }

    // ─── Inventory AJAX data endpoint ──────────────────────────────────────────

    public function inv_ajax($section)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'forbidden']); return;
        }
        // Delivery dispatch operations — always return JSON
        if ($section === 'delivery_dispatch' || $section === 'delivery_avail') {
            header('Content-Type: application/json');
            try {
                $this->_inv_ajax_delivery_dispatch($section);
            } catch (Throwable $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            return;
        }
        // Ensure stocktake AJAX operations always return JSON on exception
        if (strpos($section, 'st_') === 0 || $section === 'audit_ledger') {
            header('Content-Type: application/json');
            try {
                $this->_inv_ajax_stocktake_dispatch($section);
            } catch (Throwable $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            return;
        }

        // Invoice items lookup — isolated with try-catch so DB errors return JSON, not HTML
        if ($section === 'invoice_items') {
            header('Content-Type: application/json');
            try {
                $inv_id = (int)$this->input->get('id');
                if (!$inv_id) { echo json_encode(['items' => []]); return; }

                // Fetch invoice header — avoid deleted_customer_name which may not exist in all versions
                $inv_row = $this->db
                    ->select("i.id, i.number, i.prefix, i.clientid, i.project_id,
                              COALESCE(c.company, '') AS customer_name,
                              c.billing_street, c.billing_city, c.billing_state, c.billing_zip")
                    ->from(db_prefix().'invoices i')
                    ->join(db_prefix().'clients c', 'c.userid = i.clientid', 'left')
                    ->where('i.id', $inv_id)
                    ->get()->row_array();

                $addr_parts = [];
                if ($inv_row) {
                    foreach (['billing_street','billing_city','billing_state','billing_zip'] as $f) {
                        $v = trim(strip_tags($inv_row[$f] ?? ''));
                        if ($v) $addr_parts[] = $v;
                    }
                }

                $proj_name = '';
                if ($inv_row && $inv_row['project_id']) {
                    $proj = $this->db->select('name')->where('id', (int)$inv_row['project_id'])
                        ->get(db_prefix().'projects')->row_array();
                    $proj_name = $proj['name'] ?? '';
                }

                // Use SELECT * for itemable compatibility — discount_percent may not exist in older Perfex
                $inv_items_raw = $this->db
                    ->select('*')
                    ->where('rel_type', 'invoice')
                    ->where('rel_id', $inv_id)
                    ->order_by('item_order')
                    ->get(db_prefix().'itemable')->result_array();

                // Match by name, then by SKU as fallback
                $pos_prod_rows = $this->db->select('id, name, sku, selling_price, tax_rate_id')
                    ->where('is_active', 1)->get(db_prefix().'pos_products')->result_array();
                $prod_by_lower = [];
                $prod_by_sku   = [];
                foreach ($pos_prod_rows as $pp) {
                    $prod_by_lower[strtolower(trim($pp['name']))] = $pp;
                    if (!empty($pp['sku'])) {
                        $prod_by_sku[strtolower(trim($pp['sku']))] = $pp;
                    }
                }

                $inv_items = [];
                foreach ($inv_items_raw as $it) {
                    $lname   = strtolower(trim($it['description'] ?? ''));
                    $matched = $prod_by_lower[$lname] ?? ($prod_by_sku[$lname] ?? null);
                    $disc    = (float)($it['discount_percent'] ?? $it['discount'] ?? 0);
                    $inv_items[] = [
                        'product_id'   => $matched ? (int)$matched['id'] : null,
                        'product_name' => $it['description'] ?? '',
                        'qty'          => (float)($it['qty'] ?? 1),
                        'unit_price'   => (float)($it['rate'] ?? 0),
                        'disc_pct'     => $disc,
                        'tax_rate_id'  => $matched ? (int)($matched['tax_rate_id'] ?? 0) : 0,
                    ];
                }

                $display_num = $inv_row
                    ? $inv_row['prefix'] . str_pad($inv_row['number'], 5, '0', STR_PAD_LEFT)
                    : '';

                echo json_encode([
                    'invoice_number' => $display_num,
                    'customer_name'  => $inv_row['customer_name'] ?? '',
                    'client_id'      => (int)($inv_row['clientid'] ?? 0),
                    'project_id'     => (int)($inv_row['project_id'] ?? 0),
                    'project_name'   => $proj_name,
                    'address'        => implode(', ', $addr_parts),
                    'items'          => $inv_items,
                ]);
            } catch (Throwable $e) {
                echo json_encode(['error' => $e->getMessage(), 'items' => []]);
            }
            return;
        }

        $page     = max(1, (int)$this->input->get('page'));
        $per_page = max(10, min(200, (int)($this->input->get('per_page') ?: 25)));
        $offset   = ($page - 1) * $per_page;
        $search   = $this->input->get('search');
        $status   = $this->input->get('status');
        $branch_filter = pos_get_auth_branch();
        $bid = $branch_filter ?? (int)($this->input->get('branch_id') ?: 0);

        $db = $this->db;
        $rows = []; $total = 0;

        switch ($section) {
            case 'items':
                $db->select('p.*, c.name AS category_name, br.name AS brand_name, u.name AS unit_name, u.symbol AS unit_symbol, COALESCE(SUM(i.quantity),0) AS total_qty')
                   ->from(db_prefix() . 'pos_products p')
                   ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
                   ->join(db_prefix() . 'pos_inv_brands br', 'br.id = p.brand_id', 'left')
                   ->join(db_prefix() . 'pos_inv_units u', 'u.id = p.unit_id', 'left')
                   ->join(db_prefix() . 'pos_inventory i', 'i.product_id = p.id', 'left');
                if ($search) $db->group_start()->like('p.name', $search)->or_like('p.sku', $search)->or_like('p.barcode', $search)->group_end();
                if ($this->input->get('category')) $db->where('p.category_id', (int)$this->input->get('category'));
                $active_val = $this->input->get('active');
                if ($active_val !== null && $active_val !== '') $db->where('p.is_active', (int)$active_val);
                if ($bid && $branch_filter !== null) $db->where('i.branch_id', $bid);
                $total = $db->count_all_results(); // uses accumulated FROM/JOIN/WHERE, then resets
                $db->select('p.*, c.name AS category_name, br.name AS brand_name, u.name AS unit_name, u.symbol AS unit_symbol, COALESCE(SUM(i.quantity),0) AS total_qty')
                   ->from(db_prefix() . 'pos_products p')
                   ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
                   ->join(db_prefix() . 'pos_inv_brands br', 'br.id = p.brand_id', 'left')
                   ->join(db_prefix() . 'pos_inv_units u', 'u.id = p.unit_id', 'left')
                   ->join(db_prefix() . 'pos_inventory i', 'i.product_id = p.id', 'left');
                if ($search) $db->group_start()->like('p.name', $search)->or_like('p.sku', $search)->or_like('p.barcode', $search)->group_end();
                if ($this->input->get('category')) $db->where('p.category_id', (int)$this->input->get('category'));
                if ($active_val !== null && $active_val !== '') $db->where('p.is_active', (int)$active_val);
                if ($bid && $branch_filter !== null) $db->where('i.branch_id', $bid);
                $raw_items = $db->group_by('p.id')->order_by('p.name')->limit($per_page, $offset)->get()->result_array();
                // Fetch variant counts in one query
                $var_counts = [];
                if ($this->db->table_exists(db_prefix().'pos_item_variations')) {
                    $vc_rows = $this->db->select('product_id, COUNT(*) as cnt')->from(db_prefix().'pos_item_variations')->group_by('product_id')->get()->result_array();
                    foreach ($vc_rows as $vc) $var_counts[(int)$vc['product_id']] = (int)$vc['cnt'];
                }
                foreach ($raw_items as $r) {
                    $pid = (int)$r['id'];
                    $vc = $var_counts[$pid] ?? 0;
                    if ($vc > 0) {
                        $r['name'] = $r['name'] . ' <span style="background:#0369a1;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;vertical-align:middle" title="'.$vc.' variants">'.$vc.' vars</span>';
                    }
                    $r['_edit_url'] = admin_url('pos_system/inventory/item_form/' . $r['id']);
                    $rows[] = $r;
                }
                break;

            case 'receipts':
                $rcnt = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_receipt_items rc WHERE rc.receipt_id = r.id)';
                $db->select("r.*, b.name AS branch_name, $rcnt AS items_count", false)
                   ->from(db_prefix() . 'pos_inv_receipts r')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = r.branch_id', 'left');
                if ($search) $db->group_start()->like('r.receipt_number', $search)->or_like('r.supplier_name', $search)->group_end();
                if ($status) $db->where('r.status', $status);
                if ($branch_filter !== null) $db->where('r.branch_id', $branch_filter ?: $bid);
                $total = $db->count_all_results();
                $db->select("r.*, b.name AS branch_name, $rcnt AS items_count", false)
                   ->from(db_prefix() . 'pos_inv_receipts r')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = r.branch_id', 'left');
                if ($search) $db->group_start()->like('r.receipt_number', $search)->or_like('r.supplier_name', $search)->group_end();
                if ($status) $db->where('r.status', $status);
                if ($branch_filter !== null) $db->where('r.branch_id', $branch_filter ?: $bid);
                $raw = $db->order_by('r.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst($r['status']) . '</span>';
                    $r['total_amount']     = 'KSh ' . number_format((float)$r['total_amount'], 2);
                    $r['items_count']      = (int)($r['items_count'] ?? 0);
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['invoice_number']   = $r['invoice_number'] ?: '—';
                    $r['docket_number']    = $r['docket_number']  ?: '—';
                    $r['po_number']        = $r['po_number']      ?: '—';
                    $r['_view_url']        = admin_url('pos_system/inv_view/receipt/' . $r['id']);
                    $r['_edit_url']        = admin_url('pos_system/inv_form/receipt/' . $r['id']);
                    $r['_delete_id']       = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'deliveries':
            case 'deliveries_list':
                $search   = $this->input->get('search') ?: '';
                $status_f = $this->input->get('status') ?: '';
                $db->select('d.id, d.delivery_number, d.customer_name, d.delivery_date,
                             d.total_amount, d.status, d.date_created, d.sales_order_id,
                             b.name AS branch_name')
                   ->from(db_prefix() . 'pos_inv_deliveries d')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = d.branch_id', 'left');
                if ($bid)      $db->where('d.branch_id', $bid);
                if ($status_f) $db->where('d.status', $status_f);
                if ($search)   $db->group_start()->like('d.delivery_number', $search)->or_like('d.customer_name', $search)->group_end();
                $total = $db->count_all_results(); // uses accumulated state, then resets
                $db->select('d.id, d.delivery_number, d.customer_name, d.delivery_date,
                             d.total_amount, d.status, d.date_created, d.sales_order_id,
                             b.name AS branch_name')
                   ->from(db_prefix() . 'pos_inv_deliveries d')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = d.branch_id', 'left');
                if ($bid)      $db->where('d.branch_id', $bid);
                if ($status_f) $db->where('d.status', $status_f);
                if ($search)   $db->group_start()->like('d.delivery_number', $search)->or_like('d.customer_name', $search)->group_end();
                $raw = $db->order_by('d.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                if ($section === 'deliveries_list') {
                    echo json_encode(['rows' => $raw, 'total' => $total]);
                    return;
                }
                foreach ($raw as $r) {
                    $r['status_badge'] = '<span class="badge badge-' . $r['status'] . '">' . ucfirst($r['status']) . '</span>';
                    $r['total_amount'] = 'KSh ' . number_format((float)$r['total_amount'], 2);
                    $r['_view_url']  = admin_url('pos_system/inv_view/delivery/' . $r['id']);
                    $r['_delete_id'] = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'transfers':
                $tcnt = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_transfer_items ti WHERE ti.transfer_id = t.id)';
                $raw = $db->select("t.*, bf.name AS from_branch, bt.name AS to_branch, $tcnt AS items_count", false)
                          ->from(db_prefix() . 'pos_inv_transfers t')
                          ->join(db_prefix() . 'pos_branches bf', 'bf.id = t.from_branch_id', 'left')
                          ->join(db_prefix() . 'pos_branches bt', 'bt.id = t.to_branch_id', 'left')
                          ->order_by('t.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $db->count_all_results(db_prefix() . 'pos_inv_transfers');
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst(str_replace('_',' ',$r['status'])) . '</span>';
                    $r['items_count']      = (int)($r['items_count'] ?? 0);
                    $r['notes']            = $r['notes'] ?: '—';
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['_view_url']        = admin_url('pos_system/inv_view/transfer/' . $r['id']);
                    $r['_delete_id']       = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'adjustments':
                $acnt = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_adjustment_items ai WHERE ai.adjustment_id = a.id)';
                $raw = $db->select("a.*, b.name AS branch_name, $acnt AS items_count", false)
                          ->from(db_prefix() . 'pos_inv_adjustments a')
                          ->join(db_prefix() . 'pos_branches b', 'b.id = a.branch_id', 'left')
                          ->order_by('a.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $db->count_all_results(db_prefix() . 'pos_inv_adjustments');
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst($r['status']) . '</span>';
                    $r['type_badge']       = '<span class="badge badge-' . $r['type'] . '">' . ucfirst($r['type']) . '</span>';
                    $r['items_count']      = (int)($r['items_count'] ?? 0);
                    $r['notes']            = $r['notes'] ?: '—';
                    $r['reason']           = $r['reason'] ?: '—';
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['_view_url']        = admin_url('pos_system/inv_view/adjustment/' . $r['id']);
                    $r['_delete_id']       = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'returns':
                $rticnt = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_return_items reti WHERE reti.return_id = r.id)';
                $raw = $db->select("r.*, b.name AS branch_name, $rticnt AS items_count", false)
                          ->from(db_prefix() . 'pos_inv_returns r')
                          ->join(db_prefix() . 'pos_branches b', 'b.id = r.branch_id', 'left')
                          ->order_by('r.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $db->count_all_results(db_prefix() . 'pos_inv_returns');
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst($r['status']) . '</span>';
                    $r['type_badge']       = '<span class="badge badge-' . $r['type'] . '">' . ucfirst($r['type']) . '</span>';
                    $r['total_amount_fmt'] = 'KSh ' . number_format((float)$r['total_amount'], 2);
                    $r['items_count']      = (int)($r['items_count'] ?? 0);
                    $r['reference']        = $r['reference'] ?: '—';
                    $r['notes']            = $r['notes'] ?: '—';
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['_view_url']        = admin_url('pos_system/inv_view/return/' . $r['id']);
                    $r['_delete_id']       = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'packing':
                $pkcnt = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_packing_list_items pli WHERE pli.packing_list_id = p.id)';
                $raw = $db->select("p.*, b.name AS branch_name, $pkcnt AS items_count", false)
                          ->from(db_prefix() . 'pos_inv_packing_lists p')
                          ->join(db_prefix() . 'pos_branches b', 'b.id = p.branch_id', 'left')
                          ->order_by('p.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $db->count_all_results(db_prefix() . 'pos_inv_packing_lists');
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst($r['status']) . '</span>';
                    $r['delivery_ref']     = $r['delivery_id'] ? '#' . $r['delivery_id'] : '—';
                    $r['items_count']      = (int)($r['items_count'] ?? 0);
                    $r['notes']            = $r['notes'] ?: '—';
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['_view_url']        = admin_url('pos_system/inv_view/packing/' . $r['id']);
                    $r['_delete_id']       = $r['status'] === 'draft' ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            case 'stocktakes':
                $varsub = '(SELECT COUNT(*) FROM '.db_prefix().'pos_inv_stock_take_items sti WHERE sti.stock_take_id = s.id AND sti.variance IS NOT NULL AND sti.variance <> 0)';
                $raw = $db->select("s.*, b.name AS branch_name, COUNT(si.id) AS item_count, $varsub AS variance_items", false)
                          ->from(db_prefix() . 'pos_inv_stock_takes s')
                          ->join(db_prefix() . 'pos_branches b', 'b.id = s.branch_id', 'left')
                          ->join(db_prefix() . 'pos_inv_stock_take_items si', 'si.stock_take_id = s.id', 'left')
                          ->group_by('s.id')->order_by('s.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $db->count_all_results(db_prefix() . 'pos_inv_stock_takes');
                foreach ($raw as $r) {
                    $r['status_badge']     = '<span class="badge badge-' . $r['status'] . '">' . ucfirst(str_replace('_',' ',$r['status'])) . '</span>';
                    $r['variance_items']   = (int)($r['variance_items'] ?? 0);
                    $r['notes']            = $r['notes'] ?: '—';
                    $r['date_created_fmt'] = date('d M Y H:i', strtotime($r['date_created']));
                    $r['_view_url']        = admin_url('pos_system/inv_view/stocktake/' . $r['id']);
                    $r['_delete_id']       = in_array($r['status'], ['draft','cancelled']) ? $r['id'] : null;
                    $rows[] = $r;
                }
                break;

            // stocktake + audit_ledger cases handled above by _inv_ajax_stocktake_dispatch()

            case 'categories':
                if ($search) { $db->like('name', $search); }
                $total = $db->count_all_results(db_prefix() . 'pos_product_categories');
                if ($search) { $db->like('c.name', $search); }
                $raw = $db->select('c.*, p.name AS parent_name, COUNT(pr.id) AS product_count')
                          ->from(db_prefix() . 'pos_product_categories c')
                          ->join(db_prefix() . 'pos_product_categories p', 'p.id = c.parent_id', 'left')
                          ->join(db_prefix() . 'pos_products pr', 'pr.category_id = c.id', 'left')
                          ->group_by('c.id')->order_by('c.sort_order', 'ASC')->order_by('c.name', 'ASC')
                          ->limit($per_page, $offset)->get()->result_array();
                foreach ($raw as $r) {
                    $r['product_count'] = (int)($r['product_count'] ?? 0);
                    $r['sort_order']    = (int)($r['sort_order'] ?? 0);
                    $r['is_active']     = (int)($r['is_active'] ?? 1);
                    $r['parent']        = $r['parent_name'] ?: '';
                    $r['description']   = $r['description'] ?? '';
                    $r['_delete_id']    = $r['id'];
                    $rows[] = $r;
                }
                break;

            case 'batches':
                $exp_filter = $this->input->get('exp_status');
                $q = $db->select('ib.*, p.name AS product_name, p.sku, p.has_batch_no, p.has_serial_no, b.name AS branch_name')
                          ->from(db_prefix() . 'pos_inventory_batches ib')
                          ->join(db_prefix() . 'pos_products p', 'p.id = ib.product_id', 'left')
                          ->join(db_prefix() . 'pos_branches b', 'b.id = ib.branch_id', 'left')
                          ->where('(p.has_batch_no = 1 OR p.has_serial_no = 1)');
                if ($search) $q->group_start()->like('ib.batch_number', $search)->or_like('p.name', $search)->or_like('p.sku', $search)->group_end();
                if ($branch_filter !== null) $q->where('ib.branch_id', $branch_filter ?: $bid);
                if ($exp_filter === 'expired')       $q->where('ib.expiry_date <', date('Y-m-d'));
                elseif ($exp_filter === 'expiring_soon') $q->where('ib.expiry_date >=', date('Y-m-d'))->where('ib.expiry_date <=', date('Y-m-d', strtotime('+30 days')));
                elseif ($exp_filter === 'fresh')     $q->where('(ib.expiry_date IS NULL OR ib.expiry_date > \''.date('Y-m-d', strtotime('+30 days')).'\')');
                $count_db = clone $q;
                $raw = $q->order_by('ib.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $total = $count_db->count_all_results();
                foreach ($raw as $r) {
                    $exp = $r['expiry_date'] ? date('d M Y', strtotime($r['expiry_date'])) : '—';
                    if ($r['expiry_date']) {
                        $days = (strtotime($r['expiry_date']) - time()) / 86400;
                        if ($days < 0) $exp .= ' <span class="label label-danger">Expired</span>';
                        elseif ($days < 30) $exp .= ' <span class="label label-warning">Expiring</span>';
                    }
                    $qty  = (float)$r['quantity'];
                    $cost = (float)($r['cost_price'] ?? 0);
                    // Determine tracking type badge
                    if (!empty($r['has_serial_no'])) {
                        $r['tracking_type'] = '<span class="label" style="background:#7c3aed;color:#fff">Serial</span>';
                    } else {
                        $r['tracking_type'] = '<span class="label" style="background:#0369a1;color:#fff">Batch / LOT</span>';
                    }
                    $r['expiry_date']          = $exp;
                    $r['manufacture_date_fmt'] = $r['manufacture_date'] ? date('d M Y', strtotime($r['manufacture_date'])) : '—';
                    $r['quantity']             = number_format($qty, 2);
                    $r['unit_cost']            = 'KSh ' . number_format($cost, 2);
                    $r['total_value']          = 'KSh ' . number_format($qty * $cost, 2);
                    $r['supplier']             = $r['supplier'] ?: '—';
                    $r['sku']                  = $r['sku'] ?: '—';
                    $rows[] = $r;
                }
                break;

            case 'variations':
                $color_filter = $this->input->get('color_id') ? (int)$this->input->get('color_id') : null;
                $size_filter  = $this->input->get('size_id')  ? (int)$this->input->get('size_id')  : null;
                $style_filter = $this->input->get('style_id') ? (int)$this->input->get('style_id') : null;
                $model_filter = $this->input->get('model_id') ? (int)$this->input->get('model_id') : null;
                $active_filter= $this->input->get('is_active');
                $q = $db->select('v.id, v.product_id, v.name AS variant_name, v.sku, v.barcode, v.price_override, v.cost_price, v.is_active, v.sort_order,
                                  p.name AS product_name, p.sku AS parent_sku, p.selling_price AS parent_price,
                                  c.color_name, c.color_hex,
                                  sz.size_name, sz.size_symbol,
                                  st.style_name,
                                  m.name AS model_name')
                          ->from(db_prefix().'pos_item_variations v')
                          ->join(db_prefix().'pos_products p',    'p.id = v.product_id', 'left')
                          ->join(db_prefix().'ware_color c',      'c.color_id = v.color_id', 'left')
                          ->join(db_prefix().'ware_size_type sz',  'sz.size_type_id = v.size_id', 'left')
                          ->join(db_prefix().'ware_style_type st', 'st.style_type_id = v.style_id', 'left')
                          ->join(db_prefix().'wh_model m',        'm.id = v.model_id', 'left');
                if ($search) $q->group_start()->like('v.name', $search)->or_like('v.sku', $search)->or_like('p.name', $search)->group_end();
                if ($color_filter) $q->where('v.color_id', $color_filter);
                if ($size_filter)  $q->where('v.size_id', $size_filter);
                if ($style_filter) $q->where('v.style_id', $style_filter);
                if ($model_filter) $q->where('v.model_id', $model_filter);
                if ($active_filter !== null && $active_filter !== '') $q->where('v.is_active', (int)$active_filter);
                $count_q = clone $q;
                $raw = $q->order_by('p.name, v.sort_order, v.id')->limit($per_page, $offset)->get()->result_array();
                $total = $count_q->count_all_results();
                foreach ($raw as $r) {
                    $attrs = array_filter([
                        $r['color_name'] ?: null,
                        $r['size_name']  ?: null,
                        $r['style_name'] ?: null,
                        $r['model_name'] ?: null,
                    ]);
                    $price = $r['price_override'] !== null ? number_format((float)$r['price_override'], 2) : '<span style="color:#94a3b8">—</span>';
                    $cost  = $r['cost_price']     !== null ? number_format((float)$r['cost_price'], 2)     : '<span style="color:#94a3b8">—</span>';
                    $colorBadge = $r['color_hex']
                        ? '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:'.htmlspecialchars($r['color_hex']).';border:1px solid #ccc;margin-right:4px;vertical-align:middle"></span>'.$r['color_name']
                        : ($r['color_name'] ?: '—');
                    $r['color_display']  = $colorBadge;
                    $r['size_display']   = $r['size_name'] ? $r['size_name'].($r['size_symbol']?' ('.$r['size_symbol'].')':'') : '—';
                    $r['style_display']  = $r['style_name'] ?: '—';
                    $r['model_display']  = $r['model_name'] ?: '—';
                    $r['price_display']  = $price;
                    $r['cost_display']   = $cost;
                    $r['status_badge']   = $r['is_active'] ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
                    $r['_edit_url']      = admin_url('pos_system/inventory/item_form/'.$r['product_id'].'#tab-variations');
                    $rows[] = $r;
                }
                break;

            case 'stock_levels':
                $status_filter = $this->input->get('status');
                $q = $db->select('i.*, p.name AS product_name, p.sku, p.barcode, p.reorder_point, p.max_stock, p.cost_price, p.selling_price, c.name AS category_name, b.name AS branch_name, (i.quantity * p.cost_price) AS cost_value, (i.quantity * p.selling_price) AS sell_value')
                        ->from(db_prefix() . 'pos_inventory i')
                        ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
                        ->join(db_prefix() . 'pos_branches b', 'b.id = i.branch_id', 'left')
                        ->join(db_prefix() . 'pos_product_categories c', 'c.id = p.category_id', 'left')
                        ->where('p.track_inventory', 1);
                if ($search) $q->group_start()->like('p.name', $search)->or_like('p.sku', $search)->group_end();
                if ($branch_filter !== null) $q->where('i.branch_id', $branch_filter ?: $bid);
                elseif ($bid) $q->where('i.branch_id', $bid);
                if ($status_filter === 'out_of_stock')   $q->where('i.quantity <=', 0);
                elseif ($status_filter === 'low_stock')  $q->where('p.reorder_point >', 0)->where('i.quantity >', 0)->where('i.quantity <=', 'p.reorder_point', false);
                elseif ($status_filter === 'in_stock')   $q->group_start()->where('p.reorder_point', 0)->or_where('i.quantity >', 'p.reorder_point', false)->group_end()->where('i.quantity >', 0);
                $all_rows = $q->order_by('p.name')->get()->result_array();
                $total = count($all_rows);
                $in_stock = $low = $out = 0;
                foreach ($all_rows as $r) {
                    $qty = (float)$r['quantity']; $min = (float)($r['reorder_point'] ?? 0);
                    if ($qty <= 0) $out++; elseif ($min > 0 && $qty <= $min) $low++; else $in_stock++;
                }
                $rows = array_slice($all_rows, $offset, $per_page);
                header('Content-Type: application/json');
                echo json_encode(['rows' => $rows, 'total' => $total, 'per_page' => $per_page, 'page' => $page,
                    'summary' => ['total' => $total, 'in_stock' => $in_stock, 'low' => $low, 'out' => $out]]);
                return;

            case 'units':
                $raw = $db->select('u.*, COUNT(p.id) AS item_count')
                          ->from(db_prefix() . 'pos_inv_units u')
                          ->join(db_prefix() . 'pos_products p', 'p.unit_id = u.id', 'left')
                          ->group_by('u.id')->order_by('u.name')->get()->result_array();
                $total = count($raw);
                $rows  = $raw;
                break;

            case 'brands':
                $q = $db->select('b.*, COUNT(p.id) AS product_count')
                        ->from(db_prefix() . 'pos_inv_brands b')
                        ->join(db_prefix() . 'pos_products p', 'p.brand_id = b.id', 'left')
                        ->group_by('b.id');
                if ($search) $q->like('b.name', $search);
                $raw   = $q->order_by('b.name')->get()->result_array();
                $total = count($raw);
                $rows  = $raw;
                break;

            case 'suppliers':
                $is_active = $this->input->get('is_active');
                $db->select('*')->from(db_prefix() . 'pos_inv_suppliers');
                if ($search)      $db->group_start()->like('name', $search)->or_like('contact_person', $search)->or_like('email', $search)->group_end();
                if ($is_active !== '') $db->where('is_active', (int)$is_active);
                $total = $db->count_all_results(); // uses accumulated state, then resets
                $db->select('*')->from(db_prefix() . 'pos_inv_suppliers');
                if ($search)      $db->group_start()->like('name', $search)->or_like('contact_person', $search)->or_like('email', $search)->group_end();
                if ($is_active !== '') $db->where('is_active', (int)$is_active);
                $rows  = $db->order_by('name')->limit($per_page, $offset)->get()->result_array();
                break;

            case 'attr':
                $attr_type = $this->input->get('type');
                $attr_tbl_map = [
                    'commodity_types'  => [db_prefix().'ware_commodity_type',  null],
                    'commodity_groups' => [db_prefix().'items_groups',          null],
                    'sub_groups'       => [db_prefix().'wh_sub_group',          db_prefix().'items_groups'],
                    'units'            => [db_prefix().'ware_unit_type',        null],
                    'colors'           => [db_prefix().'ware_color',            null],
                    'models'           => [db_prefix().'wh_model',             db_prefix().'wh_brand'],
                    'sizes'            => [db_prefix().'ware_size_type',        null],
                    'styles'           => [db_prefix().'ware_style_type',       null],
                ];
                if (!isset($attr_tbl_map[$attr_type])) { echo json_encode(['rows'=>[]]); return; }
                [$attr_main_tbl, $attr_join_tbl] = $attr_tbl_map[$attr_type];
                $attr_rows = [];
                if ($attr_type === 'sub_groups') {
                    $attr_rows = $this->db->select('s.*, g.name AS group_name')
                        ->from($attr_main_tbl . ' s')
                        ->join($attr_join_tbl . ' g', 'g.id = s.group_id', 'left')
                        ->order_by('s.order, s.sub_group_name')->get()->result_array();
                } elseif ($attr_type === 'models') {
                    $attr_rows = $this->db->select('m.*, b.name AS brand_name')
                        ->from($attr_main_tbl . ' m')
                        ->join($attr_join_tbl . ' b', 'b.id = m.brand_id', 'left')
                        ->order_by('b.name, m.name')->get()->result_array();
                } else {
                    $attr_rows = $this->db->from($attr_main_tbl)->order_by('`order`')->get()->result_array();
                }
                header('Content-Type: application/json');
                echo json_encode(['rows' => $attr_rows, 'total' => count($attr_rows)]);
                return;

            case 'history':
                $type_filter    = $this->input->get('type');
                $date_from      = $this->input->get('date_from');
                $date_to        = $this->input->get('date_to');
                $product_search = $this->input->get('search');
                // Build filters closure to avoid repeating conditions
                $apply_hist_filters = function() use ($db, $type_filter, $bid, $branch_filter, $product_search, $date_from, $date_to) {
                    if ($type_filter)   $db->where('m.type', $type_filter);
                    if ($bid && $branch_filter !== null) $db->where('m.branch_id', $bid);
                    if ($product_search) $db->group_start()->like('p.name', $product_search)->or_like('p.sku', $product_search)->group_end();
                    if ($date_from)     $db->where('DATE(m.date_created) >=', $date_from);
                    if ($date_to)       $db->where('DATE(m.date_created) <=', $date_to);
                };
                $db->select('m.id')
                   ->from(db_prefix() . 'pos_inventory_movements m')
                   ->join(db_prefix() . 'pos_products p', 'p.id = m.product_id', 'left')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = m.branch_id', 'left');
                $apply_hist_filters();
                $total = $db->count_all_results(); // uses accumulated state, then resets
                $db->select('m.*, p.name AS product_name, p.sku, b.name AS branch_name, s.firstname, s.lastname')
                   ->from(db_prefix() . 'pos_inventory_movements m')
                   ->join(db_prefix() . 'pos_products p', 'p.id = m.product_id', 'left')
                   ->join(db_prefix() . 'pos_branches b', 'b.id = m.branch_id', 'left')
                   ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left');
                $apply_hist_filters();
                $raw = $db->order_by('m.date_created', 'DESC')->limit($per_page, $offset)->get()->result_array();
                $type_colors = [
                    'purchase'     => '#22c55e', 'sale'         => '#ef4444',
                    'adjustment'   => '#f59e0b', 'transfer_in'  => '#3d8ef0',
                    'transfer_out' => '#8b5cf6', 'refund'       => '#14b8a6',
                    'opening'      => '#64748b',
                ];
                foreach ($raw as $r) {
                    $t  = $r['type'];
                    $qc = (float)$r['qty_change'];
                    $r['type_badge']    = '<span class="ibadge" style="background:'.($type_colors[$t]??'#64748b').'22;color:'.($type_colors[$t]??'#64748b').';border-color:'.($type_colors[$t]??'#64748b').'44">'.ucfirst(str_replace('_',' ',$t)).'</span>';
                    $r['qty_change_fmt']= '<span style="font-weight:700;color:'.($qc >= 0 ? '#22c55e' : '#ef4444').'">'
                                         .($qc >= 0 ? '+' : '').number_format($qc, 2).'</span>';
                    $r['qty_after_fmt'] = number_format((float)$r['qty_after'], 2);
                    $r['staff_name']    = htmlspecialchars(trim(($r['firstname']??'').' '.($r['lastname']??'')) ?: '—');
                    $r['date_fmt']      = date('d M Y H:i', strtotime($r['date_created']));
                    $rows[] = $r;
                }
                header('Content-Type: application/json');
                echo json_encode(['rows' => $rows, 'total' => $total, 'per_page' => $per_page, 'page' => $page]);
                return;

            case 'approval_list':
                $apv_single_id = (int)$this->input->get('id');
                if ($apv_single_id) {
                    $apv_row = $db->where('id', $apv_single_id)->get(db_prefix().'pos_inv_approval_settings')->row_array();
                    header('Content-Type: application/json');
                    echo json_encode(['row' => $apv_row ?: null]);
                    return;
                }
                $apv_search = $this->input->get('search');
                if ($apv_search) $db->like('subject', $apv_search);
                $apv_rows = $db->order_by('sort_order','ASC')->get(db_prefix().'pos_inv_approval_settings')->result_array();
                $staff_map = [];
                foreach ($db->select('staffid,firstname,lastname')->get(db_prefix().'staff')->result_array() as $st) {
                    $staff_map[(int)$st['staffid']] = $st['firstname'].' '.$st['lastname'];
                }
                foreach ($apv_rows as &$ar) {
                    $apvrs = json_decode($ar['approvers'] ?: '[]', true);
                    $ar['staff_labels'] = implode(', ', array_map(function($a) use ($staff_map) {
                        return ($staff_map[(int)($a['staff_id'] ?? 0)] ?? '—') . ' (' . ($a['action'] ?? '') . ')';
                    }, $apvrs ?: []));
                }
                unset($ar);
                header('Content-Type: application/json');
                echo json_encode(['rows' => $apv_rows]);
                return;

            case 'minmax':
                $mm_search = $this->input->get('search');
                $q = $db->select('p.id, p.name, p.sku, p.reorder_point, p.max_stock, COALESCE(SUM(i.quantity),0) AS current_qty')
                        ->from(db_prefix() . 'pos_products p')
                        ->join(db_prefix() . 'pos_inventory i', 'i.product_id = p.id', 'left')
                        ->where('p.track_inventory', 1);
                if ($mm_search) $q->group_start()->like('p.name', $mm_search)->or_like('p.sku', $mm_search)->group_end();
                $rows = $q->group_by('p.id')->order_by('p.name')->get()->result_array();
                $total = count($rows);
                header('Content-Type: application/json');
                echo json_encode(['rows' => $rows, 'total' => $total]);
                return;

            case 'report_summary':
            case 'report_balance':
            case 'report_movements':
            case 'report_valuation':
                $this->_inv_ajax_report(str_replace('report_', '', $section));
                return;

            case 'doc_view':
                $doc_type = $this->input->get('type');
                $doc_id   = (int)$this->input->get('id');
                $dcfg2 = [
                    'receipt'    => [db_prefix().'pos_inv_receipts',    db_prefix().'pos_inv_receipt_items',    'receipt_id',    'Receipt',     'fa-truck-loading', 'receipt_number',    'receipt_date'],
                    'delivery'   => [db_prefix().'pos_inv_deliveries',  db_prefix().'pos_inv_delivery_items',   'delivery_id',   'Delivery',    'fa-truck',         'delivery_number',   'delivery_date'],
                    'transfer'   => [db_prefix().'pos_inv_transfers',   db_prefix().'pos_inv_transfer_items',   'transfer_id',   'Transfer',    'fa-exchange-alt',  'transfer_number',   'transfer_date'],
                    'adjustment' => [db_prefix().'pos_inv_adjustments', db_prefix().'pos_inv_adjustment_items', 'adjustment_id', 'Adjustment',  'fa-sliders-h',     'adjustment_number', 'adjustment_date'],
                    'return'     => [db_prefix().'pos_inv_returns',     db_prefix().'pos_inv_return_items',     'return_id',     'Return',      'fa-undo-alt',      'return_number',     'return_date'],
                    'packing'    => [db_prefix().'pos_inv_packing_lists',db_prefix().'pos_inv_packing_list_items','packing_list_id','Packing List','fa-box',          'packing_number',    'packing_date'],
                    'stocktake'  => [db_prefix().'pos_inv_stock_takes', db_prefix().'pos_inv_stock_take_items', 'stock_take_id', 'Stock Take',  'fa-clipboard-check','stocktake_number',  'start_date'],
                ];
                if (!isset($dcfg2[$doc_type]) || !$doc_id) { echo json_encode(['error'=>'Not found']); return; }
                [$dtbl2,$itbl2,$fk2,$label2,$icon2,$num_col2,$date_col2] = $dcfg2[$doc_type];
                $doc = $this->db->where('id', $doc_id)->get($dtbl2)->row_array();
                if (!$doc) { echo json_encode(['error'=>'Document not found']); return; }
                $items = $this->db->where($fk2, $doc_id)->get($itbl2)->result_array();
                // Enrich items with product names
                foreach ($items as &$it) {
                    $p = $this->db->where('id', $it['product_id'])->get(db_prefix().'pos_products')->row_array();
                    $it['product_name']    = htmlspecialchars($p['name'] ?? '—');
                    $it['qty_display']     = number_format((float)($it['quantity'] ?? $it['qty_change'] ?? $it['counted_qty'] ?? 0), 2);
                    $it['cost_display']    = isset($it['unit_cost']) ? 'KSh '.number_format((float)$it['unit_cost'],2) : (isset($it['unit_price']) ? 'KSh '.number_format((float)$it['unit_price'],2) : '—');
                    $it['total_display']   = isset($it['line_total']) ? 'KSh '.number_format((float)$it['line_total'],2) : '—';
                }
                unset($it);
                $doc['ref_number']   = $doc[$num_col2] ?? '#'.$doc_id;
                $doc['status_label'] = ucfirst($doc['status'] ?? 'draft');
                $b_id    = $doc['branch_id'] ?? $doc['from_branch_id'] ?? 0;
                $branch  = $b_id ? $this->db->where('id',$b_id)->get(db_prefix().'pos_branches')->row_array() : [];
                $doc_date = $doc[$date_col2] ?? ($doc['date_created'] ?? date('Y-m-d'));
                $info_fields = [
                    ['label'=>'Document #', 'value'=>'<strong>'.$doc['ref_number'].'</strong>'],
                    ['label'=>'Branch',     'value'=>htmlspecialchars($branch['name']??'—')],
                    ['label'=>'Date',       'value'=>date('d M Y', strtotime($doc_date))],
                    ['label'=>'Status',     'value'=>'<span class="ibadge ibadge-'.$doc['status'].'">'.$doc['status_label'].'</span>'],
                ];
                if (!empty($doc['total_amount'])) $info_fields[] = ['label'=>'Total','value'=>'<strong>KSh '.number_format((float)$doc['total_amount'],2).'</strong>'];
                if (!empty($doc['supplier_name'])) $info_fields[] = ['label'=>'Supplier','value'=>htmlspecialchars($doc['supplier_name'])];
                if (!empty($doc['customer_name'])) $info_fields[] = ['label'=>'Customer','value'=>htmlspecialchars($doc['customer_name'])];
                echo json_encode(['doc'=>$doc,'label'=>$label2,'icon'=>$icon2,'info_fields'=>$info_fields,
                    'item_cols'=>['Product','Batch / Lot','Qty','Unit Cost / Price','Line Total'],
                    'item_keys'=>['product_name','batch_number','qty_display','cost_display','total_display'],
                    'items'=>$items]);
                return;

            // invoice_items is handled via early-return above (try-catch safe)

            case 'so_items':
                $so_id = (int)$this->input->get('id');
                header('Content-Type: application/json');
                if (!$so_id) { echo json_encode(['items'=>[]]); return; }

                $so = $this->db->where('id', $so_id)
                    ->get(db_prefix().'pos_sales_orders')->row_array();
                if (!$so) { echo json_encode(['items'=>[]]); return; }

                $so_items_raw = $this->db
                    ->where('sales_order_id', $so_id)
                    ->order_by('sort_order')
                    ->get(db_prefix().'pos_sales_order_items')->result_array();

                $so_items = [];
                foreach ($so_items_raw as $it) {
                    $qty_remaining = (float)$it['qty_ordered'] - (float)$it['qty_delivered'];
                    if ($qty_remaining <= 0) continue;
                    $so_items[] = [
                        'product_id'   => $it['product_id'] ? (int)$it['product_id'] : null,
                        'product_name' => $it['product_name'],
                        'qty'          => $qty_remaining,
                        'unit_price'   => (float)$it['unit_price'],
                        'disc_pct'     => (float)$it['discount_pct'],
                        'tax_rate_id'  => (int)($it['tax_rate_id'] ?? 0),
                    ];
                }
                echo json_encode([
                    'so_number'     => $so['so_number'],
                    'customer_name' => $so['customer_name'] ?? '',
                    'client_id'     => (int)($so['client_id'] ?? 0),
                    'project_id'    => (int)($so['project_id'] ?? 0),
                    'project_name'  => $so['project_name'] ?? '',
                    'address'       => $so['address'] ?? '',
                    'items'         => $so_items,
                ]);
                return;
        }

        header('Content-Type: application/json');
        echo json_encode(['rows' => $rows, 'total' => $total, 'per_page' => $per_page, 'page' => $page]);
    }

    // ─── Inventory Save (products, config entries) ─────────────────────────────

    public function inv_save($type)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'create')) {
            echo json_encode(['success' => false, 'error' => 'Forbidden']); return;
        }
        header('Content-Type: application/json');
        $db = $this->db;

        switch ($type) {
            case 'product':
                $id = (int)$this->input->post('id');
                $data = [
                    'name'           => $this->input->post('name'),
                    'sku'            => $this->input->post('sku') ?: null,
                    'barcode'        => $this->input->post('barcode') ?: null,
                    'category_id'    => (int)$this->input->post('category_id') ?: null,
                    'brand_id'       => (int)$this->input->post('brand_id') ?: null,
                    'unit_id'        => (int)$this->input->post('unit_id') ?: null,
                    'supplier_id'    => (int)$this->input->post('supplier_id') ?: null,
                    'type'           => $this->input->post('type'),
                    'cost_price'     => (float)$this->input->post('cost_price'),
                    'selling_price'  => (float)$this->input->post('selling_price'),
                    'tax_rate_id'    => (int)$this->input->post('tax_rate_id') ?: null,
                    'description'    => $this->input->post('description'),
                    'reorder_point'  => $this->input->post('reorder_point') !== '' ? (float)$this->input->post('reorder_point') : null,
                    'track_inventory'=> $this->input->post('track_inventory') ? 1 : 0,
                    'is_pos_visible' => $this->input->post('is_pos_visible') ? 1 : 0,
                    'allow_negative' => $this->input->post('allow_negative') ? 1 : 0,
                    'is_active'      => $this->input->post('is_active') ? 1 : 0,
                ];
                if (!$data['name']) { echo json_encode(['success'=>false,'error'=>'Product name required']); return; }
                if ($id) {
                    $db->where('id', $id)->update(db_prefix() . 'pos_products', $data);
                    echo json_encode(['success' => true, 'message' => 'Product updated.']);
                } else {
                    if (!$data['sku']) $data['sku'] = 'SKU-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    $db->insert(db_prefix() . 'pos_products', $data);
                    echo json_encode(['success' => true, 'message' => 'Product created.', 'id' => $db->insert_id()]);
                }
                break;

            case 'unit':
                $id = (int)$this->input->post('id');
                $data = ['name' => $this->input->post('name'), 'symbol' => $this->input->post('symbol')];
                if ($id) { $db->where('id', $id)->update(db_prefix() . 'pos_inv_units', $data); }
                else { $db->insert(db_prefix() . 'pos_inv_units', $data); }
                echo json_encode(['success' => true, 'message' => 'Unit saved.']);
                break;

            case 'brand':
                $id = (int)$this->input->post('id');
                $data = ['name' => $this->input->post('name'), 'description' => $this->input->post('description'), 'is_active' => $this->input->post('is_active') ? 1 : 0];
                if ($id) { $db->where('id', $id)->update(db_prefix() . 'pos_inv_brands', $data); }
                else { $db->insert(db_prefix() . 'pos_inv_brands', $data); }
                echo json_encode(['success' => true, 'message' => 'Brand saved.']);
                break;

            case 'supplier':
                $id = (int)$this->input->post('id');
                $data = [
                    'name' => $this->input->post('name'),
                    'contact_person' => $this->input->post('contact_person'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'address' => $this->input->post('address'),
                    'tax_pin' => $this->input->post('tax_pin'),
                    'payment_terms' => $this->input->post('payment_terms'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                ];
                if ($id) { $db->where('id', $id)->update(db_prefix() . 'pos_inv_suppliers', $data); }
                else { $db->insert(db_prefix() . 'pos_inv_suppliers', $data); }
                echo json_encode(['success' => true, 'message' => 'Supplier saved.']);
                break;

            case 'doc':
                $doc_type = $this->input->post('doc_type');
                $doc_id   = (int)$this->input->post('doc_id');
                $status   = in_array($this->input->post('status'), ['draft','confirmed']) ? $this->input->post('status') : 'draft';
                $lines    = json_decode($this->input->post('lines'), true) ?: [];
                if (empty($doc_type) || empty($lines)) { echo json_encode(['success'=>false,'error'=>'Add at least one product line.']); return; }

                // [table, items_table, number_col, date_col, fk_col, prefix_setting, default_prefix]
                $dcfg = [
                    'receipt'    => ['pos_inv_receipts',    'pos_inv_receipt_items',    'receipt_number',    'receipt_date',    'receipt_id',    'inv_receipt_prefix',    'RCV'],
                    'delivery'   => ['pos_inv_deliveries',  'pos_inv_delivery_items',   'delivery_number',   'delivery_date',   'delivery_id',   'inv_delivery_prefix',   'DLV'],
                    'transfer'   => ['pos_inv_transfers',   'pos_inv_transfer_items',   'transfer_number',   'transfer_date',   'transfer_id',   'inv_transfer_prefix',   'TRF'],
                    'adjustment' => ['pos_inv_adjustments', 'pos_inv_adjustment_items', 'adjustment_number', 'adjustment_date', 'adjustment_id', 'inv_adjustment_prefix', 'ADJ'],
                    'return'     => ['pos_inv_returns',     'pos_inv_return_items',     'return_number',     'return_date',     'return_id',     'inv_return_prefix',     'RTN'],
                    'packing'    => ['pos_inv_packing_lists','pos_inv_packing_list_items','packing_number',  'packing_date',    'packing_list_id','inv_packing_prefix',   'PKL'],
                    'stocktake'  => ['pos_inv_stock_takes', 'pos_inv_stock_take_items', 'stocktake_number',  'start_date',      'stock_take_id', 'inv_stocktake_prefix',  'STK'],
                ];
                if (!isset($dcfg[$doc_type])) { echo json_encode(['success'=>false,'error'=>'Unknown document type']); return; }
                [$dtable, $itable, $num_col, $date_col, $fk_col, $pfx_key, $pfx_def] = $dcfg[$doc_type];

                // Sequential document numbering
                $prefix   = pos_get_setting($pfx_key) ?: $pfx_def;
                $next_key = 'inv_next_num_' . $doc_type;
                $next_num = (int)(pos_get_setting($next_key) ?: 1);
                $doc_num  = $prefix . '-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);

                $branch_id_post = (int)$this->input->post('branch_id');
                $date_post      = $this->input->post('date') ?: date('Y-m-d');
                $now            = date('Y-m-d H:i:s');

                // Build header — transfers have from/to instead of branch_id
                if ($doc_type === 'transfer') {
                    $header = [
                        $num_col         => $doc_num,
                        'from_branch_id' => $branch_id_post,
                        'to_branch_id'   => (int)$this->input->post('to_branch_id') ?: null,
                        $date_col        => $date_post,
                        'status'         => $status,
                        'notes'          => $this->input->post('notes'),
                        'created_by'     => get_staff_user_id(),
                        'date_created'   => $now,
                    ];
                } else {
                    $header = [
                        $num_col       => $doc_num,
                        'branch_id'    => $branch_id_post,
                        $date_col      => $date_post,
                        'status'       => $status,
                        'notes'        => $this->input->post('notes'),
                        'created_by'   => get_staff_user_id(),
                        'date_created' => $now,
                    ];
                }

                // Type-specific header extras
                if ($doc_type === 'receipt') {
                    $goods_val = 0; $tax_val = 0;
                    foreach ($lines as $l) {
                        $g = (float)$l['quantity'] * (float)$l['unit_cost'];
                        $goods_val += $g;
                        $tax_val   += (float)($l['tax_amount'] ?? $g * (float)($l['tax_rate_pct'] ?? 0));
                    }
                    $sup_id = (int)$this->input->post('supplier_id') ?: null;
                    $sup_name = null;
                    if ($sup_id) {
                        $sup = null;
                        if ($this->db->table_exists(db_prefix() . 'pur_vendor')) {
                            $row = $this->db->query(
                                "SELECT CASE WHEN company IS NOT NULL AND company != '' THEN company ELSE 'Unknown' END AS name
                                 FROM " . db_prefix() . "pur_vendor WHERE userid = " . (int)$sup_id . " LIMIT 1"
                            )->row_array();
                            if ($row) $sup = $row;
                        }
                        if (!$sup) {
                            $sup = $this->db->select('name')->where('id', $sup_id)->get(db_prefix().'pos_inv_suppliers')->row_array();
                        }
                        $sup_name = $sup['name'] ?? null;
                    }
                    $header['docket_number']   = $this->input->post('docket_number') ?: null;
                    $header['accounting_date'] = $this->input->post('accounting_date') ?: null;
                    $header['po_id']           = (int)$this->input->post('po_id') ?: null;
                    $header['supplier_id']     = $sup_id;
                    $header['supplier_name']   = $sup_name ?: ($this->input->post('supplier_name') ?: null);
                    $header['buyer_name']      = $this->input->post('buyer_name') ?: null;
                    $header['project']         = $this->input->post('project') ?: null;
                    $header['type']            = in_array($this->input->post('type'),['standard','emergency','return_to_supplier']) ? $this->input->post('type') : 'standard';
                    $header['department']      = $this->input->post('department') ?: null;
                    $header['requester']       = $this->input->post('requester') ?: null;
                    $header['deliverer']       = $this->input->post('deliverer') ?: null;
                    $header['invoice_number']  = $this->input->post('invoice_number') ?: null;
                    $header['goods_value']     = $goods_val;
                    $header['inventory_value'] = $goods_val;
                    $header['tax_amount']      = $tax_val;
                    $header['total_amount']    = $goods_val + $tax_val;
                    $header['note']            = $this->input->post('note') ?: null;
                    unset($header['notes']);
                }
                if ($doc_type === 'delivery') {
                    $this->_ensure_delivery_schema();
                    $subtotal = 0; $disc_total = 0; $tax_total = 0;
                    foreach ($lines as $l) {
                        $sub       = (float)$l['quantity'] * (float)($l['unit_price'] ?? 0);
                        $subtotal += $sub;
                        $disc_total += (float)($l['discount_amount'] ?? 0);
                        $tax_total  += (float)($l['tax_amount'] ?? $sub * (float)($l['tax_rate_pct'] ?? 0));
                    }
                    $shipping_fee = (float)$this->input->post('shipping_fee') ?: 0;
                    $header['accounting_date'] = $this->input->post('accounting_date') ?: null;
                    $header['customer_name']   = $this->input->post('customer_name') ?: null;
                    $header['receiver']        = $this->input->post('receiver') ?: null;
                    $header['address']         = $this->input->post('address') ?: null;
                    $header['project']         = $this->input->post('project') ?: null;
                    $header['type']            = in_array($this->input->post('type'), ['standard','urgent','return']) ? $this->input->post('type') : 'standard';
                    $header['department']      = $this->input->post('department') ?: null;
                    $header['requester']       = $this->input->post('requester') ?: null;
                    $header['sales_person']    = $this->input->post('sales_person') ?: null;
                    $header['invoice_number']  = $this->input->post('invoice_number') ?: null;
                    $header['invoice_id']      = (int)$this->input->post('invoice_id') ?: null;
                    $header['subtotal']        = $subtotal;
                    $header['discount_amount'] = $disc_total;
                    $header['shipping_fee']    = $shipping_fee;
                    $header['total_amount']    = $subtotal - $disc_total + $tax_total + $shipping_fee;
                    $header['note']            = $this->input->post('note') ?: null;
                    // Map status to fulfillment_status
                    if ($db->field_exists('fulfillment_status', db_prefix().'pos_inv_deliveries')) {
                        $header['fulfillment_status'] = ($status === 'confirmed') ? 'validated' : 'draft';
                    }
                    // Sales Order / CRM Invoice linking
                    if ($this->db->field_exists('sales_order_id', db_prefix().'pos_inv_deliveries')) {
                        $header['sales_order_id'] = (int)$this->input->post('sales_order_id') ?: null;
                    }
                    if ($this->db->field_exists('crm_invoice_id', db_prefix().'pos_inv_deliveries')) {
                        $header['crm_invoice_id'] = (int)$this->input->post('crm_invoice_id') ?: null;
                    }
                    if ($this->db->field_exists('client_id', db_prefix().'pos_inv_deliveries')) {
                        $header['client_id'] = (int)$this->input->post('client_id') ?: null;
                    }
                    if ($this->db->field_exists('project_id', db_prefix().'pos_inv_deliveries')) {
                        $header['project_id'] = (int)$this->input->post('project_id') ?: null;
                    }
                    unset($header['notes'], $header['reference']);
                }
                if ($doc_type === 'adjustment') {
                    $header['type']   = in_array($this->input->post('adj_type'), ['loss','gain','correction']) ? $this->input->post('adj_type') : 'correction';
                    $header['reason'] = $this->input->post('reason');
                }
                if ($doc_type === 'return') {
                    $header['type'] = in_array($this->input->post('return_type'), ['customer','supplier']) ? $this->input->post('return_type') : 'customer';
                }
                if ($doc_type === 'packing') {
                    $header['delivery_id'] = (int)$this->input->post('delivery_id') ?: null;
                }

                if ($doc_id > 0) {
                    $db->where('id', $doc_id)->update(db_prefix() . $dtable, $header);
                    $db->where($fk_col, $doc_id)->delete(db_prefix() . $itable);
                } else {
                    $db->insert(db_prefix() . $dtable, $header);
                    $doc_id = $db->insert_id();
                    // Advance sequential counter
                    $this->_inv_save_setting($next_key, $next_num + 1);
                }

                // Insert line items with correct column names per type
                foreach ($lines as $l) {
                    $pid = (int)$l['product_id'];
                    switch ($doc_type) {
                        case 'receipt':
                            $ri_qty   = (float)$l['quantity'];
                            $ri_cost  = (float)$l['unit_cost'];
                            $ri_rate  = (float)($l['tax_rate_pct'] ?? 0);
                            $ri_tax   = (float)($l['tax_amount'] ?? $ri_qty * $ri_cost * $ri_rate);
                            $ri_br    = (int)($l['branch_id'] ?? 0) ?: $branch_id_post;
                            $db->insert(db_prefix() . $itable, [
                                $fk_col           => $doc_id,
                                'product_id'      => $pid,
                                'branch_id'       => $ri_br,
                                'quantity'        => $ri_qty,
                                'unit_cost'       => $ri_cost,
                                'tax_rate_id'     => (int)($l['tax_rate_id'] ?? 0) ?: null,
                                'tax_rate_pct'    => $ri_rate,
                                'tax_amount'      => $ri_tax,
                                'lot_number'      => $l['lot_number'] ?? null ?: null,
                                'manufacture_date' => $l['manufacture_date'] ?? null ?: null,
                                'expiry_date'     => $l['expiry_date'] ?? null ?: null,
                                'batch_number'    => $l['lot_number'] ?? null ?: null,
                                'line_total'      => $ri_qty * $ri_cost + $ri_tax,
                            ]);
                            break;
                        case 'delivery':
                            $this->_ensure_delivery_schema();
                            $dl_qty  = (float)$l['quantity'];
                            $dl_price= (float)($l['unit_price'] ?? 0);
                            $dl_rate = (float)($l['tax_rate_pct'] ?? 0);
                            $dl_tax  = (float)($l['tax_amount'] ?? $dl_qty * $dl_price * $dl_rate);
                            $dl_sub  = (float)($l['subtotal'] ?? $dl_qty * $dl_price);
                            $dl_disc_pct = (float)($l['discount_pct'] ?? 0);
                            $dl_disc_amt = (float)($l['discount_amount'] ?? $dl_sub * ($dl_disc_pct / 100));
                            $dl_br   = (int)($l['branch_id'] ?? 0) ?: $branch_id_post;
                            $dl_row  = [
                                $fk_col          => $doc_id,
                                'product_id'     => $pid,
                                'branch_id'      => $dl_br,
                                'quantity'       => $dl_qty,
                                'unit_price'     => $dl_price,
                                'tax_rate_id'    => (int)($l['tax_rate_id'] ?? 0) ?: null,
                                'tax_rate_pct'   => $dl_rate,
                                'tax_amount'     => $dl_tax,
                                'subtotal'       => $dl_sub,
                                'discount_pct'   => $dl_disc_pct,
                                'discount_amount'=> $dl_disc_amt,
                                'line_total'     => $dl_sub + $dl_tax - $dl_disc_amt,
                            ];
                            if ($db->field_exists('batch_no', db_prefix().$itable)) {
                                $dl_row['batch_no']     = $l['batch_no']     ?? null ?: null;
                                $dl_row['serial_no']    = $l['serial_no']    ?? null ?: null;
                                $dl_row['is_drop_ship'] = !empty($l['is_drop_ship']) ? 1 : 0;
                            }
                            $db->insert(db_prefix() . $itable, $dl_row);
                            break;
                        case 'transfer':
                            $db->insert(db_prefix() . $itable, [
                                $fk_col      => $doc_id,
                                'product_id' => $pid,
                                'quantity'   => (float)$l['quantity'],
                                'notes'      => $l['notes'] ?? null,
                            ]);
                            break;
                        case 'adjustment':
                            $inv = $db->where('branch_id', $branch_id_post)->where('product_id', $pid)
                                      ->get(db_prefix().'pos_inventory')->row_array();
                            $qty_before = (float)($inv['quantity'] ?? 0);
                            $qty_change = (float)($l['qty_change'] ?? $l['quantity'] ?? 0);
                            $db->insert(db_prefix() . $itable, [
                                $fk_col      => $doc_id,
                                'product_id' => $pid,
                                'qty_before' => $qty_before,
                                'qty_change' => $qty_change,
                                'qty_after'  => $qty_before + $qty_change,
                                'reason'     => $l['reason'] ?? null,
                            ]);
                            break;
                        case 'return':
                            $db->insert(db_prefix() . $itable, [
                                $fk_col          => $doc_id,
                                'product_id'     => $pid,
                                'quantity'       => (float)$l['quantity'],
                                'unit_price'     => (float)($l['unit_price'] ?? 0),
                                'condition_notes'=> $l['condition_notes'] ?? null,
                            ]);
                            break;
                        case 'packing':
                            $db->insert(db_prefix() . $itable, [
                                $fk_col          => $doc_id,
                                'product_id'     => $pid,
                                'quantity'       => (float)$l['quantity'],
                                'package_number' => $l['package_number'] ?? null,
                            ]);
                            break;
                        case 'stocktake':
                            $inv = $db->where('branch_id', $branch_id_post)->where('product_id', $pid)
                                      ->get(db_prefix().'pos_inventory')->row_array();
                            $db->insert(db_prefix() . $itable, [
                                $fk_col      => $doc_id,
                                'product_id' => $pid,
                                'system_qty' => (float)($inv['quantity'] ?? 0),
                                'counted_qty'=> (float)($l['counted_qty'] ?? $l['quantity'] ?? 0),
                                'notes'      => $l['notes'] ?? null,
                            ]);
                            break;
                    }
                }

                // If confirming immediately, apply stock
                if ($status === 'confirmed') {
                    $this->_inv_apply_stock($doc_type, $doc_id, $header, $lines, $db);

                    // Update linked Sales Order status
                    if ($doc_type === 'delivery' && !empty($header['sales_order_id'])
                        && $db->table_exists(db_prefix().'pos_sales_orders')
                        && $db->table_exists(db_prefix().'pos_sales_order_items')) {
                        $so_id    = (int)$header['sales_order_id'];
                        $all_so_items = $db->where('sales_order_id', $so_id)
                            ->get(db_prefix().'pos_sales_order_items')->result_array();
                        $fully_delivered = true;
                        foreach ($all_so_items as $soi) {
                            if ((float)$soi['qty_delivered'] < (float)$soi['qty_ordered']) {
                                $fully_delivered = false; break;
                            }
                        }
                        $new_so_status = $fully_delivered ? 'delivered' : 'partially_delivered';
                        $db->where('id', $so_id)->update(db_prefix().'pos_sales_orders', ['status'=>$new_so_status]);
                    }
                }

                $redirect = admin_url('pos_system/inv_view/' . $doc_type . '/' . $doc_id);
                echo json_encode(['success'=>true,'message'=>ucfirst($doc_type).' saved.','id'=>$doc_id,'redirect'=>$redirect,'csrf_hash'=>$this->security->get_csrf_hash()]);
                break;

            case 'settings':
                $allowed_keys = [
                    // Document prefixes
                    'inv_receipt_prefix','inv_delivery_prefix','inv_transfer_prefix',
                    'inv_adjustment_prefix','inv_return_prefix','inv_packing_prefix','inv_stocktake_prefix',
                    // Document next numbers
                    'inv_next_num_receipt','inv_next_num_delivery','inv_next_num_transfer',
                    'inv_next_num_adjustment','inv_next_num_return','inv_next_num_packing','inv_next_num_stocktake',
                    // Stock control
                    'inv_track_by_default','inv_allow_negative','inv_hide_out_of_stock',
                    'inv_default_reorder_qty','inv_low_stock_threshold',
                    'inv_allow_delete_confirmed','inv_auto_deduct_on_sale','inv_auto_restore_on_refund',
                    // Batch / serial
                    'inv_batch_enabled','inv_expiry_warn_days','inv_lot_prefix',
                    'inv_serial_enabled','inv_serial_mandatory','inv_sku_prefix',
                    // Notifications
                    'inv_warnings_enabled','inv_warning_hour','inv_expiry_notify_days','inv_notify_customer_delivery',
                    // PDF settings
                    'inv_pdf_delivery_show_price','inv_pdf_delivery_show_outstanding','inv_pdf_delivery_show_lot',
                    'inv_pdf_packing_show_price','inv_pdf_packing_show_tax','inv_pdf_packing_show_total',
                    // Barcodes
                    'inv_barcode_use_sku','inv_barcode_show_name','inv_barcode_show_price',
                    // Return policies
                    'inv_return_window_days','inv_return_fee_pct','inv_return_policy',
                ];
                $posted = $this->input->post();
                // Checkboxes not submitted = 0
                $checkbox_keys = [
                    'inv_track_by_default','inv_allow_negative','inv_hide_out_of_stock',
                    'inv_allow_delete_confirmed','inv_auto_deduct_on_sale','inv_auto_restore_on_refund',
                    'inv_batch_enabled','inv_serial_enabled','inv_serial_mandatory',
                    'inv_warnings_enabled','inv_notify_customer_delivery',
                    'inv_pdf_delivery_show_price','inv_pdf_delivery_show_outstanding','inv_pdf_delivery_show_lot',
                    'inv_pdf_packing_show_price','inv_pdf_packing_show_tax','inv_pdf_packing_show_total',
                    'inv_barcode_use_sku','inv_barcode_show_name','inv_barcode_show_price',
                ];
                foreach ($allowed_keys as $key) {
                    $val = isset($posted[$key]) ? $posted[$key] : (in_array($key, $checkbox_keys) ? '0' : null);
                    if ($val === null) continue;
                    $this->_inv_save_setting($key, $val);
                }
                // Save serial & batch settings (sb_ prefixed fields → tblpos_serial_batch_settings)
                $sb_map = [
                    'sb_enable_serial_and_batch_no_for_item'                      => 'enable_serial_and_batch_no_for_item',
                    'sb_allow_existing_serial_no'                                 => 'allow_existing_serial_no',
                    'sb_do_not_use_batchwise_valuation'                           => 'do_not_use_batchwise_valuation',
                    'sb_auto_create_serial_and_batch_bundle_for_outward'          => 'auto_create_serial_and_batch_bundle_for_outward',
                    'sb_pick_serial_and_batch_based_on'                           => 'pick_serial_and_batch_based_on',
                    'sb_disable_serial_no_and_batch_selector'                     => 'disable_serial_no_and_batch_selector',
                    'sb_use_serial_batch_fields'                                  => 'use_serial_batch_fields',
                    'sb_do_not_update_serial_batch_on_creation_of_auto_bundle'    => 'do_not_update_serial_batch_on_creation_of_auto_bundle',
                    'sb_allow_negative_stock_for_batch'                           => 'allow_negative_stock_for_batch',
                    'sb_set_serial_and_batch_bundle_naming_based_on_naming_series'=> 'set_serial_and_batch_bundle_naming_based_on_naming_series',
                    'sb_use_naming_series'                                        => 'use_naming_series',
                ];
                $sb_checkboxes = array_diff(array_keys($sb_map), ['sb_pick_serial_and_batch_based_on']);
                foreach ($sb_map as $post_key => $db_key) {
                    $val = isset($posted[$post_key]) ? $posted[$post_key] : (in_array($post_key, $sb_checkboxes) ? '0' : null);
                    if ($val === null) continue;
                    $this->db->where('setting_key', $db_key)->update(db_prefix().'pos_serial_batch_settings', ['setting_value' => $val]);
                }
                echo json_encode(['success' => true]);
                break;

            case 'attr':
                $atype = $this->input->post('type');
                $aid   = (int)$this->input->post('id');
                $attr_cfg = [
                    'commodity_types'  => [db_prefix().'ware_commodity_type',  'commodity_type_id',  ['commondity_code'=>'code','commondity_name'=>'name','note'=>'note','order'=>'order','display'=>'display']],
                    'commodity_groups' => [db_prefix().'items_groups',          'id',                 ['commodity_group_code'=>'code','name'=>'name','note'=>'note','order'=>'order','display'=>'display']],
                    'sub_groups'       => [db_prefix().'wh_sub_group',          'id',                 ['sub_group_code'=>'code','sub_group_name'=>'name','group_id'=>'group_id','note'=>'note','order'=>'order','display'=>'display']],
                    'units'            => [db_prefix().'ware_unit_type',        'unit_type_id',       ['unit_code'=>'code','unit_name'=>'name','unit_symbol'=>'symbol','note'=>'note','order'=>'order','display'=>'display']],
                    'colors'           => [db_prefix().'ware_color',            'color_id',           ['color_code'=>'code','color_name'=>'name','color_hex'=>'hex','note'=>'note','order'=>'order','display'=>'display']],
                    'models'           => [db_prefix().'wh_model',             'id',                 ['name'=>'name','brand_id'=>'brand_id']],
                    'sizes'            => [db_prefix().'ware_size_type',        'size_type_id',       ['size_code'=>'code','size_name'=>'name','size_symbol'=>'symbol','note'=>'note','order'=>'order','display'=>'display']],
                    'styles'           => [db_prefix().'ware_style_type',       'style_type_id',      ['style_code'=>'code','style_name'=>'name','style_barcode'=>'barcode','note'=>'note','order'=>'order','display'=>'display']],
                ];
                if (!isset($attr_cfg[$atype])) { echo json_encode(['success'=>false,'error'=>'Unknown type']); return; }
                [$atbl, $apk, $afields] = $attr_cfg[$atype];
                $arow = [];
                foreach ($afields as $col => $post_key) {
                    $val = $this->input->post($post_key);
                    if ($val !== null && $val !== '') $arow[$col] = $val;
                }
                if (empty($arow)) { echo json_encode(['success'=>false,'error'=>'No data provided']); return; }
                if ($aid) { $db->where($apk, $aid)->update($atbl, $arow); }
                else { $db->insert($atbl, $arow); }
                echo json_encode(['success'=>true,'message'=>'Saved.']);
                break;

            case 'attr_display':
                $atype2 = $this->input->post('type');
                $aid2   = (int)$this->input->post('id');
                $adsp   = (int)$this->input->post('display');
                $pk_map = ['commodity_types'=>[db_prefix().'ware_commodity_type','commodity_type_id'],
                           'commodity_groups'=>[db_prefix().'items_groups','id'],
                           'sub_groups'=>[db_prefix().'wh_sub_group','id'],
                           'units'=>[db_prefix().'ware_unit_type','unit_type_id'],
                           'colors'=>[db_prefix().'ware_color','color_id'],
                           'sizes'=>[db_prefix().'ware_size_type','size_type_id'],
                           'styles'=>[db_prefix().'ware_style_type','style_type_id']];
                if (!isset($pk_map[$atype2])) { echo json_encode(['success'=>false]); return; }
                [$atbl2,$apk2] = $pk_map[$atype2];
                $db->where($apk2,$aid2)->update($atbl2,['display'=>$adsp]);
                echo json_encode(['success'=>true]);
                break;

            case 'minmax':
                $updates = json_decode($this->input->post('updates'), true) ?: [];
                foreach ($updates as $u) {
                    $pid = (int)($u['id'] ?? 0);
                    if (!$pid) continue;
                    $data = [];
                    if ($u['reorder_point'] !== '' && $u['reorder_point'] !== null) $data['reorder_point'] = (float)$u['reorder_point'];
                    if ($u['max_stock'] !== '' && $u['max_stock'] !== null) $data['max_stock'] = (float)$u['max_stock'];
                    if (!empty($data)) $db->where('id', $pid)->update(db_prefix() . 'pos_products', $data);
                }
                echo json_encode(['success' => true]);
                break;

            case 'approval':
                $apv_id   = (int)$this->input->post('id');
                $subject  = trim($this->input->post('subject'));
                $related  = $this->input->post('related');
                $single   = $this->input->post('single_approver') ? 1 : 0;
                $approvers= json_decode($this->input->post('approvers') ?: '[]', true) ?: [];
                if (!$subject || !$related) { echo json_encode(['success'=>false,'error'=>'Subject and Related are required.']); break; }
                $apv_data = [
                    'subject'         => $subject,
                    'related'         => $related,
                    'single_approver' => $single,
                    'approvers'       => json_encode($approvers),
                ];
                if ($apv_id) {
                    $db->where('id', $apv_id)->update(db_prefix().'pos_inv_approval_settings', $apv_data);
                } else {
                    $apv_data['sort_order']   = (int)$db->count_all(db_prefix().'pos_inv_approval_settings') + 1;
                    $apv_data['created_by']   = get_staff_user_id();
                    $apv_data['date_created'] = date('Y-m-d H:i:s');
                    $db->insert(db_prefix().'pos_inv_approval_settings', $apv_data);
                    $apv_id = $db->insert_id();
                }
                echo json_encode(['success'=>true,'message'=>'Approval setting saved.','id'=>$apv_id,'csrf_hash'=>$this->security->get_csrf_hash()]);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Unknown type']);
        }
    }

    // ─── Inventory Action (confirm, cancel documents) ──────────────────────────

    public function inv_action()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'edit')) {
            echo json_encode(['success' => false, 'error' => 'Forbidden']); return;
        }
        header('Content-Type: application/json');
        $doc_type = $this->input->post('type');
        $doc_id   = (int)$this->input->post('id');
        $action   = $this->input->post('action');

        // Approval setting delete
        if ($doc_type === 'approval' && $action === 'delete' && $doc_id) {
            $this->db->where('id', $doc_id)->delete(db_prefix().'pos_inv_approval_settings');
            echo json_encode(['success'=>true,'message'=>'Approval setting deleted.','csrf_hash'=>$this->security->get_csrf_hash()]); return;
        }
        // Approval setting reorder
        if ($doc_type === 'approval' && $action === 'reorder') {
            $order = json_decode($this->input->post('order') ?: '[]', true);
            foreach ($order as $i => $rid) {
                $this->db->where('id',(int)$rid)->update(db_prefix().'pos_inv_approval_settings',['sort_order'=>$i+1]);
            }
            echo json_encode(['success'=>true,'csrf_hash'=>$this->security->get_csrf_hash()]); return;
        }

        $table_map = [
            'receipt'    => db_prefix() . 'pos_inv_receipts',
            'delivery'   => db_prefix() . 'pos_inv_deliveries',
            'transfer'   => db_prefix() . 'pos_inv_transfers',
            'adjustment' => db_prefix() . 'pos_inv_adjustments',
            'return'     => db_prefix() . 'pos_inv_returns',
            'packing'    => db_prefix() . 'pos_inv_packing_lists',
            'stocktake'  => db_prefix() . 'pos_inv_stock_takes',
        ];
        if (!isset($table_map[$doc_type]) || !$doc_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']); return;
        }
        $doc = $this->db->where('id', $doc_id)->get($table_map[$doc_type])->row_array();
        if (!$doc) { echo json_encode(['success' => false, 'error' => 'Document not found']); return; }
        if ($doc['status'] !== 'draft' && $action === 'confirm') {
            echo json_encode(['success' => false, 'error' => 'Only draft documents can be confirmed']); return;
        }

        if ($action === 'confirm') {
            $this->db->where('id', $doc_id)->update($table_map[$doc_type], ['status' => 'confirmed', 'date_updated' => date('Y-m-d H:i:s')]);
            $this->_inv_apply_stock($doc_type, $doc_id, $doc, [], $this->db);
            echo json_encode(['success' => true, 'message' => ucfirst($doc_type) . ' confirmed. Stock levels updated.']);
        } elseif ($action === 'cancel') {
            $this->db->where('id', $doc_id)->update($table_map[$doc_type], ['status' => 'cancelled', 'date_updated' => date('Y-m-d H:i:s')]);
            echo json_encode(['success' => true, 'message' => 'Document cancelled.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
        }
    }

    // ─── Stock application helper (receipt=in, delivery=out, transfer, adjustment) ─

    private function _inv_apply_stock($doc_type, $doc_id, $doc, $lines, $db)
    {
        $fk_map = [
            'receipt'    => ['pos_inv_receipt_items',   'receipt_id'],
            'delivery'   => ['pos_inv_delivery_items',  'delivery_id'],
            'transfer'   => ['pos_inv_transfer_items',  'transfer_id'],
            'adjustment' => ['pos_inv_adjustment_items','adjustment_id'],
            'return'     => ['pos_inv_return_items',    'return_id'],
            'stocktake'  => ['pos_inv_stock_take_items','stock_take_id'],
        ];
        if (!isset($fk_map[$doc_type])) return;
        [$items_table, $fk_col] = $fk_map[$doc_type];
        $items = $db->where($fk_col, $doc_id)->get(db_prefix() . $items_table)->result_array();
        if (empty($items)) return;

        $staff_id = get_staff_user_id();
        $now      = date('Y-m-d H:i:s');
        $branch_id = $doc['branch_id'] ?? $doc['from_branch_id'] ?? 0;

        foreach ($items as $item) {
            $pid = (int)$item['product_id'];
            switch ($doc_type) {
                case 'receipt':
                    $qty_change = (float)$item['quantity'];
                    $this->_inv_update_qty($branch_id, $pid, $qty_change, $db);
                    $this->_inv_log_movement($branch_id, $pid, 'purchase', $qty_change, $doc_id, 'pos_inv_receipts', (float)$item['unit_cost'], $staff_id, $now, $db);
                    // Log batch if batch_number present
                    if (!empty($item['batch_number'])) {
                        $this->_inv_upsert_batch($branch_id, $pid, $item, $qty_change, $db);
                    }
                    break;
                case 'delivery':
                    $qty_change = -(float)$item['quantity'];
                    $this->_inv_update_qty($branch_id, $pid, $qty_change, $db);
                    $this->_inv_log_movement($branch_id, $pid, 'sale', $qty_change, $doc_id, 'pos_inv_deliveries', (float)$item['unit_price'], $staff_id, $now, $db);
                    // Update Sales Order qty_delivered for this product if DN is linked to a SO
                    if (!empty($doc['sales_order_id']) && $db->table_exists(db_prefix().'pos_sales_order_items')) {
                        $so_item = $db->where('sales_order_id', (int)$doc['sales_order_id'])
                            ->where('product_id', $pid)
                            ->get(db_prefix().'pos_sales_order_items')->row_array();
                        if ($so_item) {
                            $new_delivered = (float)$so_item['qty_delivered'] + (float)$item['quantity'];
                            $db->where('id', $so_item['id'])
                               ->update(db_prefix().'pos_sales_order_items', ['qty_delivered'=>$new_delivered]);
                        }
                    }
                    break;
                case 'transfer':
                    $qty = (float)$item['quantity'];
                    $to  = (int)($doc['to_branch_id'] ?? 0);
                    $this->_inv_update_qty($branch_id, $pid, -$qty, $db);
                    $this->_inv_log_movement($branch_id, $pid, 'transfer_out', -$qty, $doc_id, 'pos_inv_transfers', 0, $staff_id, $now, $db);
                    if ($to) {
                        $this->_inv_update_qty($to, $pid, $qty, $db);
                        $this->_inv_log_movement($to, $pid, 'transfer_in', $qty, $doc_id, 'pos_inv_transfers', 0, $staff_id, $now, $db);
                    }
                    break;
                case 'adjustment':
                    $qty_change = (float)$item['qty_change'];
                    $this->_inv_update_qty($branch_id, $pid, $qty_change, $db);
                    $this->_inv_log_movement($branch_id, $pid, 'adjustment', $qty_change, $doc_id, 'pos_inv_adjustments', 0, $staff_id, $now, $db);
                    break;
                case 'return':
                    $qty_change = (float)$item['quantity'];
                    $this->_inv_update_qty($branch_id, $pid, $qty_change, $db);
                    $this->_inv_log_movement($branch_id, $pid, 'refund', $qty_change, $doc_id, 'pos_inv_returns', (float)($item['unit_price'] ?? 0), $staff_id, $now, $db);
                    break;
                case 'stocktake':
                    if ($item['counted_qty'] !== null && $item['counted_qty'] !== '') {
                        $inv = $db->where('branch_id', $branch_id)->where('product_id', $pid)->get(db_prefix().'pos_inventory')->row_array();
                        $current = (float)($inv['quantity'] ?? 0);
                        $counted = (float)$item['counted_qty'];
                        $diff    = $counted - $current;
                        if ($diff != 0) {
                            $this->_inv_update_qty($branch_id, $pid, $diff, $db);
                            $this->_inv_log_movement($branch_id, $pid, 'adjustment', $diff, $doc_id, 'pos_inv_stock_takes', 0, $staff_id, $now, $db);
                        }
                    }
                    break;
            }
        }
    }

    private function _inv_update_qty($branch_id, $product_id, $qty_change, $db)
    {
        $inv = $db->where('branch_id', $branch_id)->where('product_id', $product_id)->get(db_prefix().'pos_inventory')->row_array();
        $now = date('Y-m-d H:i:s');
        if ($inv) {
            $new_qty = (float)$inv['quantity'] + $qty_change;
            $db->where('id', $inv['id'])->update(db_prefix().'pos_inventory', ['quantity' => $new_qty, 'date_updated' => $now]);
        } else {
            $db->insert(db_prefix().'pos_inventory', [
                'branch_id'   => $branch_id, 'product_id' => $product_id,
                'quantity'    => max(0, $qty_change),
                'reserved_qty'=> 0, 'date_updated' => $now,
            ]);
        }
    }

    private function _inv_log_movement($branch_id, $product_id, $type, $qty_change, $ref_id, $ref_type, $unit_cost, $staff_id, $now, $db)
    {
        $inv = $db->where('branch_id', $branch_id)->where('product_id', $product_id)->get(db_prefix().'pos_inventory')->row_array();
        $qty_after = (float)($inv['quantity'] ?? 0);
        $db->insert(db_prefix().'pos_inventory_movements', [
            'branch_id'     => $branch_id,
            'product_id'    => $product_id,
            'type'          => $type,
            'reference_id'  => $ref_id,
            'reference_type'=> $ref_type,
            'qty_before'    => $qty_after - $qty_change,
            'qty_change'    => $qty_change,
            'qty_after'     => $qty_after,
            'unit_cost'     => $unit_cost,
            'staff_id'      => $staff_id,
            'date_created'  => $now,
        ]);
    }

    private function _inv_upsert_batch($branch_id, $product_id, $item, $qty, $db)
    {
        $existing = $db->where('branch_id', $branch_id)->where('product_id', $product_id)
                       ->where('batch_number', $item['batch_number'])->get(db_prefix().'pos_inventory_batches')->row_array();
        if ($existing) {
            $db->where('id', $existing['id'])->update(db_prefix().'pos_inventory_batches', ['quantity' => (float)$existing['quantity'] + $qty]);
        } else {
            $db->insert(db_prefix().'pos_inventory_batches', [
                'branch_id'      => $branch_id,
                'product_id'     => $product_id,
                'batch_number'   => $item['batch_number'],
                'quantity'       => $qty,
                'cost_price'     => (float)($item['unit_cost'] ?? 0),
                'manufacture_date'=> $item['manufacture_date'] ?? null ?: null,
                'expiry_date'    => $item['expiry_date'] ?? null ?: null,
                'date_created'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _inv_ajax_stocktake_dispatch($section)
    {
        $this->_ensure_stocktake_schema();
        $db = $this->db;

        switch ($section) {
            case 'st_initiate':
                if (!pos_can_access('manager')) { echo json_encode(['success'=>false,'error'=>'Permission denied']); return; }
                $now      = date('Y-m-d H:i:s');
                $bid_post = (int)($this->input->post('branch_id') ?: 0);
                if (!$bid_post) { echo json_encode(['success'=>false,'error'=>'Branch is required']); return; }
                $scope    = $this->input->post('scope') ?: 'full';
                $scope_f  = $this->input->post('scope_filter') ?: null;
                $blind    = (int)(bool)$this->input->post('blind_counting');
                $threshold= (float)($this->input->post('variance_threshold') ?: 0);
                $notes_in = $this->input->post('notes') ?: null;
                $pfx      = pos_get_setting('inv_stocktake_prefix', $bid_post) ?: 'STK';
                $next     = (int)(pos_get_setting('inv_next_num_stocktake', $bid_post) ?: 1);
                $stk_num  = $pfx . '-' . date('Ymd') . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
                $pq = $db->select('p.id, p.name, p.sku, p.barcode, p.cost_price, COALESCE(i.quantity,0) AS system_qty')
                          ->from(db_prefix().'pos_products p')
                          ->join(db_prefix().'pos_inventory i', 'i.product_id = p.id AND i.branch_id = '.(int)$bid_post, 'left')
                          ->where('p.is_active', 1);
                if ($scope === 'category' && $scope_f) {
                    $pq->where_in('p.category_id', array_map('intval', explode(',', $scope_f)));
                } elseif ($scope === 'product' && $scope_f) {
                    $pq->where_in('p.id', array_map('intval', explode(',', $scope_f)));
                }
                $prods = $pq->order_by('p.name')->get()->result_array();
                if (empty($prods)) { echo json_encode(['success'=>false,'error'=>'No active products found for the selected scope']); return; }
                $db->trans_start();
                $db->insert(db_prefix().'pos_inv_stock_takes', [
                    'stocktake_number'   => $stk_num,
                    'start_date'         => date('Y-m-d'),
                    'branch_id'          => $bid_post,
                    'status'             => 'in_progress',
                    'notes'              => $notes_in,
                    'created_by'         => get_staff_user_id(),
                    'scope'              => $scope,
                    'scope_filter'       => $scope_f,
                    'blind_counting'     => $blind,
                    'variance_threshold' => $threshold,
                    'snapshot_timestamp' => $now,
                    'freeze_active'      => 1,
                    'total_items'        => count($prods),
                    'items_counted'      => 0,
                    'date_created'       => $now,
                    'date_updated'       => $now,
                ]);

                $stk_id = $db->insert_id();
                foreach ($prods as $p) {
                    $db->insert(db_prefix().'pos_inv_stock_take_items', [
                        'stock_take_id' => $stk_id,
                        'product_id'    => (int)$p['id'],
                        'qty_system'    => (float)$p['system_qty'],
                        'qty_counted'   => null,
                        'counted_qty'   => null,
                        'variance'      => null,
                        'unit_cost'     => (float)($p['cost_price'] ?? 0),
                        'notes'         => null,
                    ]);
                }
                $this->_inv_save_setting('inv_next_num_stocktake', $next + 1);
                $db->trans_complete();
                if ($db->trans_status() === false) { echo json_encode(['success'=>false,'error'=>'Database transaction failed']); return; }
                echo json_encode(['success'=>true, 'id'=>$stk_id, 'number'=>$stk_num,
                    'redirect'=> admin_url('pos_system/inv_view/stocktake/'.$stk_id)]);
                return;

            case 'st_session_items':
                $stk_id  = (int)$this->input->get('id');
                $session = $db->where('id', $stk_id)->get(db_prefix().'pos_inv_stock_takes')->row_array();
                if (!$session) { echo json_encode(['error'=>'Not found']); return; }
                $blind_m    = (int)($session['blind_counting'] ?? 0);
                $is_manager = pos_can_access('manager');
                $items = $db->select('sti.id, sti.product_id, sti.qty_system, sti.counted_qty,
                                       sti.variance, sti.unit_cost, sti.reason_code, sti.notes,
                                       sti.bin_location, sti.counted_by, sti.counted_at,
                                       sti.recount_requested, sti.recount_count, sti.is_found_stock,
                                       p.name AS product_name, p.sku, p.barcode,
                                       c.name AS category_name,
                                       CONCAT(st.firstname," ",st.lastname) AS counter_name')
                              ->from(db_prefix().'pos_inv_stock_take_items sti')
                              ->join(db_prefix().'pos_products p', 'p.id = sti.product_id', 'left')
                              ->join(db_prefix().'pos_product_categories c', 'c.id = p.category_id', 'left')
                              ->join(db_prefix().'staff st', 'st.staffid = sti.counted_by', 'left')
                              ->where('sti.stock_take_id', $stk_id)
                              ->order_by('p.name')->get()->result_array();
                foreach ($items as &$it) {
                    $it['qty_system']        = (float)$it['qty_system'];
                    $it['counted_qty']       = $it['counted_qty'] !== null ? (float)$it['counted_qty'] : null;
                    $it['variance']          = $it['variance'] !== null ? (float)$it['variance'] : null;
                    $it['unit_cost']         = (float)($it['unit_cost'] ?? 0);
                    $it['recount_requested'] = (int)($it['recount_requested'] ?? 0);
                    $it['is_found_stock']    = (int)($it['is_found_stock'] ?? 0);
                    $it['financial_impact']  = $it['variance'] !== null ? round($it['variance'] * $it['unit_cost'], 4) : null;
                    if ($blind_m && !$is_manager) { $it['qty_system'] = null; }
                }
                unset($it);
                $counted = array_filter($items, function($i){ return $i['counted_qty'] !== null; });
                echo json_encode(['items'=>$items,'total'=>count($items),'counted'=>count($counted),
                    'blind_mode'=>$blind_m,'is_manager'=>(int)$is_manager,'session'=>$session]);
                return;

            case 'st_submit_count':
                $stk_id    = (int)$this->input->post('stock_take_id');
                $prod_id   = (int)$this->input->post('product_id');
                $qty_in    = $this->input->post('counted_qty');
                if ($qty_in === '' || $qty_in === null || (float)$qty_in < 0) {
                    echo json_encode(['success'=>false,'error'=>'Quantity must be 0 or greater']); return;
                }
                $qty_counted = (float)$qty_in;
                $session = $db->where('id', $stk_id)->get(db_prefix().'pos_inv_stock_takes')->row_array();
                if (!$session || !in_array($session['status'], ['in_progress','draft'])) {
                    echo json_encode(['success'=>false,'error'=>'Session not active']); return;
                }
                $item    = $db->where('stock_take_id', $stk_id)->where('product_id', $prod_id)
                               ->get(db_prefix().'pos_inv_stock_take_items')->row_array();
                $now_c   = date('Y-m-d H:i:s');
                $staff_c = get_staff_user_id();
                $bin     = $this->input->post('bin_location') ?: null;
                $reason  = $this->input->post('reason_code') ?: null;
                $notes_c = $this->input->post('notes') ?: null;
                $is_off  = (int)(bool)$this->input->post('is_offline');
                $dev_id  = $this->input->post('device_id') ?: null;
                $db->insert(db_prefix().'pos_inv_stocktake_counts', [
                    'stock_take_id'   => $stk_id, 'product_id'   => $prod_id,
                    'bin_location'    => $bin,     'counted_qty'  => $qty_counted,
                    'counter_user_id' => $staff_c, 'device_id'    => $dev_id,
                    'recorded_at'     => $now_c,   'synced_at'    => $is_off ? null : $now_c,
                    'is_offline'      => $is_off,  'notes'        => $notes_c,
                ]);
                $sys_qty      = (float)($item['qty_system'] ?? 0);
                $variance     = $qty_counted - $sys_qty;
                $was_uncounted = (!$item || $item['counted_qty'] === null);
                if ($item) {
                    $db->where('id', $item['id'])->update(db_prefix().'pos_inv_stock_take_items', [
                        'counted_qty'=>$qty_counted,'qty_counted'=>$qty_counted,'variance'=>$variance,
                        'counted_by'=>$staff_c,'counted_at'=>$now_c,
                        'bin_location'=>$bin ?: ($item['bin_location'] ?? null),
                        'reason_code'=>$reason,'recount_requested'=>0,
                        'notes'=>$notes_c ?: ($item['notes'] ?? null),
                    ]);
                } else {
                    $prod_r = $db->where('id', $prod_id)->get(db_prefix().'pos_products')->row_array();
                    $db->insert(db_prefix().'pos_inv_stock_take_items', [
                        'stock_take_id'=>$stk_id,'product_id'=>$prod_id,'qty_system'=>0,
                        'counted_qty'=>$qty_counted,'qty_counted'=>$qty_counted,'variance'=>$qty_counted,
                        'counted_by'=>$staff_c,'counted_at'=>$now_c,'bin_location'=>$bin,
                        'unit_cost'=>(float)($prod_r['cost_price']??0),'reason_code'=>'found_stock','is_found_stock'=>1,'notes'=>$notes_c,
                    ]);
                }
                if ($was_uncounted) {
                    $db->set('items_counted','items_counted+1',false)->where('id',$stk_id)->update(db_prefix().'pos_inv_stock_takes');
                }
                echo json_encode(['success'=>true,'variance'=>$variance,'qty_counted'=>$qty_counted,'qty_system'=>$sys_qty]);
                return;

            case 'st_variance':
                if (!pos_can_access('manager')) { echo json_encode(['error'=>'forbidden']); return; }
                $stk_id  = (int)$this->input->get('id');
                $session = $db->where('id',$stk_id)->get(db_prefix().'pos_inv_stock_takes')->row_array();
                if (!$session) { echo json_encode(['error'=>'Not found']); return; }
                $thr = (float)($session['variance_threshold'] ?? 0);
                $items = $db->select('sti.id, sti.product_id, sti.qty_system, sti.counted_qty, sti.variance,
                                       sti.unit_cost, sti.reason_code, sti.notes,
                                       p.name AS product_name, p.sku, p.barcode, c.name AS category_name')
                            ->from(db_prefix().'pos_inv_stock_take_items sti')
                            ->join(db_prefix().'pos_products p','p.id = sti.product_id','left')
                            ->join(db_prefix().'pos_product_categories c','c.id = p.category_id','left')
                            ->where('sti.stock_take_id',$stk_id)
                            ->where('sti.variance IS NOT NULL',null,false)
                            ->order_by('ABS(sti.variance)','DESC')->get()->result_array();
                $total_v = $db->where('stock_take_id',$stk_id)->count_all_results(db_prefix().'pos_inv_stock_take_items');
                $summary = ['total_items'=>$total_v,'items_with_variance'=>0,'items_ok'=>0,
                            'total_financial_impact'=>0,'total_gain_impact'=>0,'total_loss_impact'=>0,
                            'items_above_threshold'=>0,'uncounted_items'=>$total_v - count($items)];
                foreach ($items as &$it) {
                    $v = (float)$it['variance']; $uc = (float)($it['unit_cost']??0); $fi = round($v*$uc,4);
                    $it['variance']=$v; $it['unit_cost']=$uc; $it['financial_impact']=$fi;
                    $it['pct_variance'] = (float)$it['qty_system'] > 0 ? round(($v/(float)$it['qty_system'])*100,2) : null;
                    $it['flag'] = ($thr>0 && abs($v)>$thr) ? 'high' : ($v!=0 ? 'low' : 'ok');
                    if ($v!=0) $summary['items_with_variance']++; else $summary['items_ok']++;
                    if ($fi<0) $summary['total_loss_impact']+=$fi; else $summary['total_gain_impact']+=$fi;
                    $summary['total_financial_impact']+=$fi;
                    if ($thr>0 && abs($v)>$thr) $summary['items_above_threshold']++;
                }
                unset($it);
                echo json_encode(['summary'=>$summary,'items'=>$items]);
                return;

            case 'st_post_adjustments':
                if (!pos_can_access('manager')) { echo json_encode(['success'=>false,'error'=>'Manager access required']); return; }
                $stk_id  = (int)$this->input->post('id');
                $session = $db->where('id',$stk_id)->get(db_prefix().'pos_inv_stock_takes')->row_array();
                if (!$session || !in_array($session['status'],['in_progress','draft'])) {
                    echo json_encode(['success'=>false,'error'=>'Session must be active to post']); return;
                }
                $branch_p = (int)$session['branch_id'];
                $staff_p  = get_staff_user_id();
                $now_p    = date('Y-m-d H:i:s');
                $items_p  = $db->where('stock_take_id',$stk_id)->where('counted_qty IS NOT NULL',null,false)
                               ->get(db_prefix().'pos_inv_stock_take_items')->result_array();
                if (empty($items_p)) { echo json_encode(['success'=>false,'error'=>'No counted items to post']); return; }
                $db->trans_start();
                $adjusted = 0;
                foreach ($items_p as $it) {
                    $pid      = (int)$it['product_id'];
                    $counted  = (float)$it['counted_qty'];
                    $inv_row  = $db->where('branch_id',$branch_p)->where('product_id',$pid)->get(db_prefix().'pos_inventory')->row_array();
                    $qty_before = (float)($inv_row['quantity']??0);
                    $var_p = $counted - $qty_before;
                    if ($var_p == 0) continue;
                    $this->_inv_update_qty($branch_p,$pid,$var_p,$db);
                    $uc = (float)($it['unit_cost']??0);
                    $db->insert(db_prefix().'pos_inv_audit_ledger',[
                        'ref_type'=>'stocktake','ref_id'=>$stk_id,'branch_id'=>$branch_p,'product_id'=>$pid,
                        'qty_before'=>$qty_before,'qty_after'=>$counted,'qty_variance'=>$var_p,
                        'unit_cost'=>$uc,'financial_impact'=>round($var_p*$uc,4),
                        'reason_code'=>$it['reason_code']?:'stocktake','notes'=>$it['notes'],
                        'posted_by'=>$staff_p,'posted_at'=>$now_p,
                    ]);
                    $this->_inv_log_movement($branch_p,$pid,'adjustment',$var_p,$stk_id,'pos_inv_stock_takes',$uc,$staff_p,$now_p,$db);
                    $adjusted++;
                }
                $db->where('id',$stk_id)->update(db_prefix().'pos_inv_stock_takes',[
                    'status'=>'completed','end_date'=>date('Y-m-d'),'approved_by'=>$staff_p,
                    'freeze_active'=>0,'date_updated'=>$now_p,
                ]);
                $db->trans_complete();
                if ($db->trans_status()===false) { echo json_encode(['success'=>false,'error'=>'Transaction failed']); return; }
                hooks()->do_action('pos_stocktake_completed',['stocktake_id'=>$stk_id,'branch_id'=>$branch_p,'adjusted_items'=>$adjusted]);
                echo json_encode(['success'=>true,'adjusted'=>$adjusted]);
                return;

            case 'st_recount':
                if (!pos_can_access('manager')) { echo json_encode(['success'=>false,'error'=>'forbidden']); return; }
                $item_id = (int)$this->input->post('item_id');
                $db->where('id',$item_id)->update(db_prefix().'pos_inv_stock_take_items',[
                    'recount_requested'=>1,'recount_count'=>'recount_count+1',
                    'counted_qty'=>null,'qty_counted'=>null,'variance'=>null,'counted_at'=>null,
                ]);
                echo json_encode(['success'=>true]);
                return;

            case 'st_cancel':
                if (!pos_can_access('manager')) { echo json_encode(['success'=>false,'error'=>'forbidden']); return; }
                $stk_id = (int)$this->input->post('id');
                $db->where('id',$stk_id)->where_in('status',['draft','in_progress'])
                   ->update(db_prefix().'pos_inv_stock_takes',['status'=>'cancelled','freeze_active'=>0,'date_updated'=>date('Y-m-d H:i:s')]);
                echo json_encode(['success'=>true]);
                return;

            case 'st_batch_sync':
                $raw_counts = $this->input->post('counts');
                if (!is_array($raw_counts)||empty($raw_counts)){echo json_encode(['success'=>false,'error'=>'No data']);return;}
                $synced=0; $now_s=date('Y-m-d H:i:s'); $staff_s=get_staff_user_id();
                foreach ($raw_counts as $entry){
                    $sid=(int)($entry['stock_take_id']??0); $pid=(int)($entry['product_id']??0);
                    $qty_s=max(0,(float)($entry['counted_qty']??0));
                    if(!$sid||!$pid) continue;
                    $sess_r=$db->where('id',$sid)->get(db_prefix().'pos_inv_stock_takes')->row_array();
                    if(!$sess_r||!in_array($sess_r['status'],['in_progress','draft'])) continue;
                    $db->insert(db_prefix().'pos_inv_stocktake_counts',[
                        'stock_take_id'=>$sid,'product_id'=>$pid,'bin_location'=>$entry['bin_location']??null,
                        'counted_qty'=>$qty_s,'counter_user_id'=>$staff_s,'device_id'=>$entry['device_id']??null,
                        'recorded_at'=>$entry['recorded_at']??$now_s,'synced_at'=>$now_s,'is_offline'=>1,'notes'=>$entry['notes']??null,
                    ]);
                    $it_s=$db->where('stock_take_id',$sid)->where('product_id',$pid)->get(db_prefix().'pos_inv_stock_take_items')->row_array();
                    $sys_s=(float)($it_s['qty_system']??0); $var_s=$qty_s-$sys_s;
                    $unc_s=(!$it_s||$it_s['counted_qty']===null);
                    if($it_s){$db->where('id',$it_s['id'])->update(db_prefix().'pos_inv_stock_take_items',['counted_qty'=>$qty_s,'qty_counted'=>$qty_s,'variance'=>$var_s,'counted_by'=>$staff_s,'counted_at'=>$now_s]);}
                    else{$pr=$db->where('id',$pid)->get(db_prefix().'pos_products')->row_array();$db->insert(db_prefix().'pos_inv_stock_take_items',['stock_take_id'=>$sid,'product_id'=>$pid,'qty_system'=>0,'counted_qty'=>$qty_s,'qty_counted'=>$qty_s,'variance'=>$qty_s,'counted_by'=>$staff_s,'counted_at'=>$now_s,'unit_cost'=>(float)($pr['cost_price']??0),'is_found_stock'=>1]);$unc_s=true;}
                    if($unc_s){$db->set('items_counted','items_counted+1',false)->where('id',$sid)->update(db_prefix().'pos_inv_stock_takes');}
                    $synced++;
                }
                echo json_encode(['success'=>true,'synced'=>$synced]);
                return;

            case 'audit_ledger':
                if (!pos_can_access('manager')) { echo json_encode(['error'=>'forbidden']); return; }
                $ref_type = $this->input->get('ref_type') ?: 'stocktake';
                $ref_id   = (int)$this->input->get('ref_id');
                if (!$ref_id) { echo json_encode(['rows'=>[]]); return; }
                $ledger = $db->select('al.*, p.name AS product_name, p.sku, CONCAT(st.firstname," ",st.lastname) AS posted_by_name')
                    ->from(db_prefix().'pos_inv_audit_ledger al')
                    ->join(db_prefix().'pos_products p','p.id = al.product_id','left')
                    ->join(db_prefix().'staff st','st.staffid = al.posted_by','left')
                    ->where('al.ref_type',$ref_type)->where('al.ref_id',$ref_id)
                    ->order_by('al.posted_at','ASC')->get()->result_array();
                echo json_encode(['rows'=>$ledger]);
                return;

            default:
                echo json_encode(['error'=>'Unknown stocktake action: '.$section]);
        }
    }

    private function _inv_save_setting($key, $value)
    {
        $existing = $this->db->where('setting_key', $key)->where('branch_id IS NULL', null, false)->get(db_prefix().'pos_settings')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update(db_prefix().'pos_settings', ['setting_value' => $value]);
        } else {
            $this->db->insert(db_prefix().'pos_settings', ['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    private function _inv_ajax_report($type)
    {
        header('Content-Type: application/json');
        $branch_id = (int)($this->input->get('branch_id') ?: 0);
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to   = $this->input->get('date_to')   ?: date('Y-m-d');
        $branch_filter = pos_get_auth_branch();
        $bid = $branch_filter ?? $branch_id;
        $db  = $this->db;

        $html = '';
        switch ($type) {
            case 'summary':
                $q = $db->select('p.name, p.sku, b.name AS branch_name, COALESCE(SUM(i.quantity),0) AS qty, p.cost_price, (COALESCE(SUM(i.quantity),0)*p.cost_price) AS value')
                        ->from(db_prefix() . 'pos_inventory i')
                        ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
                        ->join(db_prefix() . 'pos_branches b', 'b.id = i.branch_id', 'left')
                        ->where('p.track_inventory', 1)->group_by('i.product_id, i.branch_id')->order_by('p.name');
                if ($bid) $q->where('i.branch_id', $bid);
                $rows = $q->get()->result_array();
                $total_val = array_sum(array_column($rows, 'value'));
                $html  = '<div style="margin-bottom:12px;padding:12px 16px;background:#e8f4fd;border-radius:4px;display:flex;align-items:center;gap:16px">';
                $html .= '<strong style="font-size:15px;color:#2c3e6a">Total Stock Value: KSh ' . number_format($total_val, 2) . '</strong>';
                $html .= '<span style="color:#64748b">·</span><span style="font-size:13px">' . count($rows) . ' product–branch entries</span></div>';
                if (empty($rows)) { $html .= '<div style="padding:40px;text-align:center;color:#94a3b8"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>No inventory data.</div>'; break; }
                $html .= '<div class="xls-wrap" style="max-height:calc(100vh - 320px)">';
                $html .= '<table class="xls-table"><thead><tr>';
                $html .= '<th class="xls-th xls-col-rownum">#</th>';
                foreach ([['Product',160,false],['SKU',100,false],['Branch',130,false],['Qty On Hand',100,true],['Cost Price',120,true],['Stock Value',130,true]] as $c) {
                    $r = $c[2] ? ' xls-right' : '';
                    $html .= '<th class="xls-th'.$r.'" style="min-width:'.$c[1].'px">'.$c[0].'</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $n => $r) {
                    $qty = (float)$r['qty']; $val = (float)$r['value'];
                    $html .= '<tr class="xls-row">';
                    $html .= '<td class="xls-cell xls-col-rownum">'.($n+1).'</td>';
                    $html .= '<td class="xls-cell">'.htmlspecialchars($r['name']).'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#64748b">'.htmlspecialchars($r['sku']??'').'</td>';
                    $html .= '<td class="xls-cell">'.htmlspecialchars($r['branch_name']??'—').'</td>';
                    $html .= '<td class="xls-cell xls-right" style="font-weight:700;color:'.($qty<=0?'#dc2626':'#16a34a').'">'.number_format($qty,2).'</td>';
                    $html .= '<td class="xls-cell xls-right">KSh '.number_format((float)$r['cost_price'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="font-weight:600">KSh '.number_format($val,2).'</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
                break;

            case 'movements':
                $q = $db->select('m.*, p.name AS product_name, p.sku, b.name AS branch_name')
                        ->from(db_prefix() . 'pos_inventory_movements m')
                        ->join(db_prefix() . 'pos_products p', 'p.id = m.product_id', 'left')
                        ->join(db_prefix() . 'pos_branches b', 'b.id = m.branch_id', 'left')
                        ->where('DATE(m.date_created) >=', $date_from)
                        ->where('DATE(m.date_created) <=', $date_to)
                        ->order_by('m.date_created', 'DESC')->limit(500);
                if ($bid) $q->where('m.branch_id', $bid);
                $rows = $q->get()->result_array();
                $type_labels = ['purchase'=>'Receipt','sale'=>'Sale','transfer_in'=>'Transfer In','transfer_out'=>'Transfer Out','adjustment'=>'Adjustment','refund'=>'Return','opening'=>'Opening'];
                $type_bgs    = ['purchase'=>'#dcfce7;color:#166534','sale'=>'#fee2e2;color:#dc2626','transfer_in'=>'#dbeafe;color:#1d4ed8','transfer_out'=>'#fef3c7;color:#92400e','adjustment'=>'#f3e8ff;color:#7c3aed','refund'=>'#fff7ed;color:#c2410c','opening'=>'#f1f5f9;color:#475569'];
                $html  = '<div class="xls-wrap" style="max-height:calc(100vh - 320px)">';
                $html .= '<table class="xls-table"><thead><tr>';
                $html .= '<th class="xls-th xls-col-rownum">#</th>';
                foreach ([['DATE / TIME',130,false],['PRODUCT',160,false],['SKU',100,false],['BRANCH',120,false],['TYPE',110,false],['CHANGE',90,true],['BEFORE',90,true],['AFTER',90,true],['REFERENCE',130,false]] as $c) {
                    $r = $c[2] ? ' xls-right' : '';
                    $html .= '<th class="xls-th'.$r.'" style="min-width:'.$c[1].'px">'.$c[0].'</th>';
                }
                $html .= '</tr></thead><tbody>';
                if (empty($rows)) { $html .= '<tr><td colspan="10"><div class="inv-empty"><i class="fa fa-inbox"></i><p>No movements in this period.</p></div></td></tr>'; }
                foreach ($rows as $n => $r) {
                    $qc  = (float)$r['qty_change'];
                    $qcs = $qc >= 0 ? 'color:#16a34a;font-weight:700' : 'color:#dc2626;font-weight:700';
                    $lbl = $type_labels[$r['type']] ?? ucfirst(str_replace('_',' ',$r['type']));
                    $bg  = $type_bgs[$r['type']] ?? '#f1f5f9;color:#475569';
                    $html .= '<tr class="xls-row">';
                    $html .= '<td class="xls-cell xls-col-rownum">'.($n+1).'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#64748b;white-space:nowrap">'.date('d M Y H:i', strtotime($r['date_created'])).'</td>';
                    $html .= '<td class="xls-cell" style="font-weight:600">'.htmlspecialchars($r['product_name']??'').'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#64748b">'.htmlspecialchars($r['sku']??'').'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px">'.htmlspecialchars($r['branch_name']??'').'</td>';
                    $html .= '<td class="xls-cell"><span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;background:'.$bg.'">'.$lbl.'</span></td>';
                    $html .= '<td class="xls-cell xls-right" style="'.$qcs.'">'.($qc>=0?'+':'').number_format($qc,2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#64748b">'.number_format((float)$r['qty_before'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="font-weight:600">'.number_format((float)$r['qty_after'],2).'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#94a3b8">'.htmlspecialchars($r['reference_type']??'').' #'.$r['reference_id'].'</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
                if (count($rows) >= 500) $html .= '<div style="padding:8px 16px;font-size:11px;color:#e67e22;background:#fff3e0"><i class="fa fa-exclamation-triangle"></i> Showing first 500 movements. Narrow date range to see more.</div>';
                break;

            case 'valuation':
                $rows = $db->select('p.name, p.sku, p.cost_price, p.selling_price, COALESCE(SUM(i.quantity),0) AS qty, (COALESCE(SUM(i.quantity),0)*p.cost_price) AS cost_val, (COALESCE(SUM(i.quantity),0)*p.selling_price) AS sell_val')
                           ->from(db_prefix() . 'pos_inventory i')
                           ->join(db_prefix() . 'pos_products p', 'p.id = i.product_id')
                           ->where('p.track_inventory', 1)->group_by('i.product_id')->order_by('cost_val', 'DESC')
                           ->get()->result_array();
                $tot_cost = array_sum(array_column($rows, 'cost_val'));
                $tot_sell = array_sum(array_column($rows, 'sell_val'));
                $html  = '<div class="row" style="margin-bottom:16px">';
                $html .= '<div class="col-md-4"><div style="background:#e8f4fd;padding:14px;border-radius:4px;text-align:center"><div style="font-size:20px;font-weight:700;color:#2c3e6a">KSh '.number_format($tot_cost,2).'</div><div style="font-size:11px;color:#7f8c8d">Total Cost Value</div></div></div>';
                $html .= '<div class="col-md-4"><div style="background:#e8f9f0;padding:14px;border-radius:4px;text-align:center"><div style="font-size:20px;font-weight:700;color:#27ae60">KSh '.number_format($tot_sell,2).'</div><div style="font-size:11px;color:#7f8c8d">Total Retail Value</div></div></div>';
                $html .= '<div class="col-md-4"><div style="background:#fdf2e9;padding:14px;border-radius:4px;text-align:center"><div style="font-size:20px;font-weight:700;color:#e67e22">KSh '.number_format($tot_sell-$tot_cost,2).'</div><div style="font-size:11px;color:#7f8c8d">Potential Margin</div></div></div>';
                $html .= '</div>';
                if (empty($rows)) { $html .= '<div style="padding:40px;text-align:center;color:#94a3b8">No inventory data.</div>'; break; }
                $html .= '<div class="xls-wrap" style="max-height:calc(100vh - 380px)">';
                $html .= '<table class="xls-table"><thead><tr>';
                $html .= '<th class="xls-th xls-col-rownum">#</th>';
                foreach ([['Product',160,false],['SKU',100,false],['Qty',80,true],['Cost Price',110,true],['Retail Price',110,true],['Cost Value',120,true],['Retail Value',120,true],['Margin',110,true]] as $c) {
                    $r = $c[2] ? ' xls-right' : '';
                    $html .= '<th class="xls-th'.$r.'" style="min-width:'.$c[1].'px">'.$c[0].'</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $n => $r) {
                    $margin = (float)$r['sell_val'] - (float)$r['cost_val'];
                    $mc = $margin >= 0 ? 'color:#16a34a;font-weight:600' : 'color:#dc2626;font-weight:600';
                    $html .= '<tr class="xls-row">';
                    $html .= '<td class="xls-cell xls-col-rownum">'.($n+1).'</td>';
                    $html .= '<td class="xls-cell" style="font-weight:600">'.htmlspecialchars($r['name']).'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#64748b">'.htmlspecialchars($r['sku']??'').'</td>';
                    $html .= '<td class="xls-cell xls-right">'.number_format((float)$r['qty'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right">KSh '.number_format((float)$r['cost_price'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right">KSh '.number_format((float)$r['selling_price'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="font-weight:600">KSh '.number_format((float)$r['cost_val'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="font-weight:600">KSh '.number_format((float)$r['sell_val'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="'.$mc.'">KSh '.number_format($margin,2).'</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
                break;

            default: // balance
                $sql = "SELECT p.name AS product_name, p.sku, b.name AS branch_name,
                    COALESCE(inv.quantity, 0) AS current_qty,
                    COALESCE(SUM(CASE WHEN m.type='purchase' THEN m.qty_change ELSE 0 END), 0) AS receipts,
                    COALESCE(SUM(CASE WHEN m.type='sale' THEN m.qty_change ELSE 0 END), 0) AS sales,
                    COALESCE(SUM(CASE WHEN m.type='transfer_in' THEN m.qty_change ELSE 0 END), 0) AS trans_in,
                    COALESCE(SUM(CASE WHEN m.type='transfer_out' THEN m.qty_change ELSE 0 END), 0) AS trans_out,
                    COALESCE(SUM(CASE WHEN m.type='adjustment' THEN m.qty_change ELSE 0 END), 0) AS adjustments
                    FROM " . db_prefix() . "pos_products p
                    LEFT JOIN " . db_prefix() . "pos_inventory inv ON inv.product_id = p.id
                    LEFT JOIN " . db_prefix() . "pos_branches b ON b.id = inv.branch_id
                    LEFT JOIN " . db_prefix() . "pos_inventory_movements m
                        ON m.product_id = p.id AND m.branch_id = inv.branch_id
                        AND DATE(m.date_created) >= " . $db->escape($date_from) . "
                        AND DATE(m.date_created) <= " . $db->escape($date_to) .
                    " WHERE p.track_inventory = 1"
                    . ($bid ? " AND inv.branch_id = " . (int)$bid : "") .
                    " GROUP BY p.id, inv.branch_id ORDER BY p.name";
                $rows = $db->query($sql)->result_array();
                if (empty($rows)) {
                    $html = '<div style="padding:40px;text-align:center;color:#94a3b8"><i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>No inventory data for this period.</div>';
                    break;
                }
                $html  = '<div class="xls-wrap" style="max-height:calc(100vh - 320px)">';
                $html .= '<table class="xls-table"><thead><tr>';
                $html .= '<th class="xls-th xls-col-rownum">#</th>';
                foreach ([['Product',160,false],['SKU',100,false],['Branch',120,false],['Opening Qty',100,true],['Receipts (+)',90,true],['Sales Out (-)',90,true],['Transfer In (+)',100,true],['Transfer Out (-)',100,true],['Adjustments',100,true],['Closing Qty',100,true]] as $c) {
                    $r = $c[2] ? ' xls-right' : '';
                    $html .= '<th class="xls-th'.$r.'" style="min-width:'.$c[1].'px">'.$c[0].'</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $n => $r) {
                    $net     = (float)$r['receipts'] + (float)$r['sales'] + (float)$r['trans_in'] + (float)$r['trans_out'] + (float)$r['adjustments'];
                    $opening = (float)$r['current_qty'] - $net;
                    $closing = (float)$r['current_qty'];
                    $cc      = $closing <= 0 ? 'color:#dc2626;font-weight:700' : 'color:#16a34a;font-weight:700';
                    $html .= '<tr class="xls-row">';
                    $html .= '<td class="xls-cell xls-col-rownum">'.($n+1).'</td>';
                    $html .= '<td class="xls-cell" style="font-weight:600">'.htmlspecialchars($r['product_name']).'</td>';
                    $html .= '<td class="xls-cell" style="font-size:11px;color:#64748b">'.htmlspecialchars($r['sku']??'').'</td>';
                    $html .= '<td class="xls-cell">'.htmlspecialchars($r['branch_name']??'—').'</td>';
                    $html .= '<td class="xls-cell xls-right">'.number_format($opening,2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#16a34a">+'.number_format((float)$r['receipts'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#dc2626">'.number_format(abs((float)$r['sales']),2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#2563eb">+'.number_format((float)$r['trans_in'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#d97706">'.number_format(abs((float)$r['trans_out']),2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="color:#7c3aed">'.number_format((float)$r['adjustments'],2).'</td>';
                    $html .= '<td class="xls-cell xls-right" style="'.$cc.'">'.number_format($closing,2).'</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
        }

        echo json_encode(['html' => $html]);
    }

    // ─── Item: check item_code uniqueness ─────────────────────────────────────

    // ─── Item Variations ──────────────────────────────────────────────────────

    public function inv_item_variations_save(): void
    {
        header('Content-Type: application/json');
        if (!$this->input->is_ajax_request()) { echo json_encode(['success'=>false,'error'=>'AJAX only']); return; }
        $product_id = (int)$this->input->post('product_id');
        if (!$product_id) { echo json_encode(['success'=>false,'error'=>'No product ID']); return; }

        $this->load->model('pos_system/Pos_products_model');

        // Save attr_value selections
        $attr_types = ['color','size','style','model'];
        $this->db->where('product_id', $product_id)->delete(db_prefix().'pos_item_attr_values');
        foreach ($attr_types as $at) {
            $ids = $this->input->post('attr_'.$at) ?: [];
            if (!is_array($ids)) $ids = [$ids];
            foreach ($ids as $vid) {
                $vid = (int)$vid;
                if ($vid > 0) {
                    $this->db->insert(db_prefix().'pos_item_attr_values', [
                        'product_id'    => $product_id,
                        'attr_type'     => $at,
                        'attr_value_id' => $vid,
                    ]);
                }
            }
        }

        // Save variant rows (delete removed, upsert existing)
        $variants_json = $this->input->post('variants');
        $variants = $variants_json ? json_decode($variants_json, true) : [];
        if (!is_array($variants)) $variants = [];

        // Keep only variants that were submitted
        $submitted_ids = array_filter(array_column($variants, 'id'), function($v){ return $v > 0; });
        if ($submitted_ids) {
            $this->db->where('product_id', $product_id)->where_not_in('id', $submitted_ids)->delete(db_prefix().'pos_item_variations');
        } else {
            $this->db->where('product_id', $product_id)->delete(db_prefix().'pos_item_variations');
        }

        foreach ($variants as $i => $v) {
            $row = [
                'product_id'     => $product_id,
                'name'           => substr(trim($v['name'] ?? ''), 0, 255),
                'sku'            => $v['sku'] ? substr(trim($v['sku']), 0, 100) : null,
                'barcode'        => $v['barcode'] ? substr(trim($v['barcode']), 0, 100) : null,
                'color_id'       => ($v['color_id'] ?? 0) ? (int)$v['color_id'] : null,
                'size_id'        => ($v['size_id']  ?? 0) ? (int)$v['size_id']  : null,
                'style_id'       => ($v['style_id'] ?? 0) ? (int)$v['style_id'] : null,
                'model_id'       => ($v['model_id'] ?? 0) ? (int)$v['model_id'] : null,
                'price_override' => isset($v['price_override']) && $v['price_override'] !== '' ? (float)$v['price_override'] : null,
                'cost_price'     => isset($v['cost_price']) && $v['cost_price'] !== '' ? (float)$v['cost_price'] : null,
                'is_active'      => isset($v['is_active']) ? (int)(bool)$v['is_active'] : 1,
                'sort_order'     => $i,
            ];
            $vid = (int)($v['id'] ?? 0);
            if ($vid > 0) {
                $this->db->where('id', $vid)->where('product_id', $product_id)->update(db_prefix().'pos_item_variations', $row);
            } else {
                $this->db->insert(db_prefix().'pos_item_variations', $row);
            }
        }

        // Update has_variations flag
        $has = count($variants) > 0 ? 1 : 0;
        $this->db->where('id', $product_id)->update(db_prefix().'pos_products', ['has_variations' => $has]);

        echo json_encode(['success' => true, 'message' => count($variants).' variant(s) saved.']);
    }

    // ─── Variations report page ───────────────────────────────────────────────

    public function inv_variations(): void
    {
        [$branch_id, $branches] = $this->_inv_common_data();
        $attr_colors = $this->db->where('display', 1)->order_by('color_name')->get(db_prefix().'ware_color')->result_array();
        $attr_sizes  = $this->db->where('display', 1)->order_by('size_name')->get(db_prefix().'ware_size_type')->result_array();
        $attr_styles = $this->db->where('display', 1)->order_by('style_name')->get(db_prefix().'ware_style_type')->result_array();
        $attr_models = $this->db->order_by('name')->get(db_prefix().'wh_model')->result_array();
        $this->load->view('pos_system/admin/inventory/variations_report', [
            'title'       => 'Variations Report',
            'inv_section' => 'variations',
            'branch_id'   => $branch_id,
            'branches'    => $branches,
            'attr_colors' => $attr_colors,
            'attr_sizes'  => $attr_sizes,
            'attr_styles' => $attr_styles,
            'attr_models' => $attr_models,
        ]);
    }

    public function inv_item_check_code(): void
    {
        header('Content-Type: application/json');
        $code       = $this->input->get('code');
        $exclude_id = (int)$this->input->get('exclude_id');
        if (!$code) { echo json_encode(['available' => true]); return; }
        $this->load->model('pos_system/Pos_products_model');
        echo json_encode(['available' => $this->pos_products_model->check_code_unique($code, $exclude_id)]);
    }

    // ─── Item: toggle active/inactive via AJAX ────────────────────────────────

    public function inv_item_toggle_status(): void
    {
        header('Content-Type: application/json');
        if (!$this->input->post('id')) { echo json_encode(['success' => false, 'error' => 'Missing ID']); return; }
        $id   = (int)$this->input->post('id');
        $item = $this->db->where('id', $id)->get(db_prefix() . 'pos_products')->row_array();
        if (!$item) { echo json_encode(['success' => false, 'error' => 'Not found']); return; }
        $new = $item['is_active'] ? 0 : 1;
        $this->db->where('id', $id)->update(db_prefix() . 'pos_products', ['is_active' => $new]);
        echo json_encode(['success' => true, 'is_active' => $new]);
    }

    // ─── Item: duplicate ──────────────────────────────────────────────────────

    public function inv_item_duplicate(): void
    {
        header('Content-Type: application/json');
        $id = (int)$this->input->post('id');
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing ID']); return; }
        $this->load->model('pos_system/Pos_products_model');
        $new_id = $this->pos_products_model->duplicate($id);
        if (!$new_id) { echo json_encode(['success' => false, 'error' => 'Duplicate failed']); return; }
        echo json_encode([
            'success'  => true,
            'new_id'   => $new_id,
            'edit_url' => admin_url('pos_system/inventory/item_form/' . $new_id),
        ]);
    }

    // ─── Item: set primary barcode ────────────────────────────────────────────

    public function inv_item_set_primary_barcode(): void
    {
        header('Content-Type: application/json');
        $product_id = (int)$this->input->post('product_id');
        $barcode_id = (int)$this->input->post('barcode_id');
        if (!$product_id || !$barcode_id) { echo json_encode(['success' => false]); return; }
        $this->load->model('pos_system/Pos_products_model');
        $ok = $this->pos_products_model->set_primary_barcode($product_id, $barcode_id);
        echo json_encode(['success' => $ok]);
    }

    // ─── Serial / Batch global settings GET/POST ──────────────────────────────

    public function inv_serial_batch_settings(): void
    {
        header('Content-Type: application/json');
        $this->load->model('pos_system/Pos_products_model');
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $keys = [
                'enable_serial_and_batch_no_for_item',
                'allow_existing_serial_no',
                'do_not_use_batchwise_valuation',
                'auto_create_serial_and_batch_bundle_for_outward',
                'pick_serial_and_batch_based_on',
                'disable_serial_no_and_batch_selector',
                'use_serial_batch_fields',
                'do_not_update_serial_batch_on_creation_of_auto_bundle',
                'allow_negative_stock_for_batch',
                'set_serial_and_batch_bundle_naming_based_on_naming_series',
                'use_naming_series',
            ];
            $data = [];
            foreach ($keys as $k) {
                $data[$k] = $this->input->post($k) ?? '0';
            }
            $this->pos_products_model->save_serial_batch_settings($data);
            echo json_encode(['success' => true, 'message' => 'Serial & Batch settings saved.']);
        } else {
            echo json_encode($this->pos_products_model->get_serial_batch_settings());
        }
    }

    // ─── Reorder rules check (called by cron / scheduled task) ───────────────

    public function inv_reorder_check(): void
    {
        // Accepts key-based auth for cron calls
        $key = $this->input->get('key') ?: $this->input->post('key');
        if ($key !== md5(APP_ENCRYPTION_KEY . 'reorder')) {
            if (!pos_can_access('manager')) { echo json_encode(['error' => 'Forbidden']); return; }
        }
        header('Content-Type: application/json');
        $this->load->model('pos_system/Pos_products_model');
        $triggered = $this->pos_products_model->check_reorder_rules();
        echo json_encode(['success' => true, 'triggered' => count($triggered), 'rules' => $triggered]);
    }

    // ─── Inventory Delete ──────────────────────────────────────────────────────

    public function inv_delete($type, $id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'delete')) {
            echo json_encode(['success' => false, 'error' => 'Forbidden']); return;
        }
        header('Content-Type: application/json');
        $id = (int)$id;
        $table_map = [
            'categories'        => [db_prefix() . 'pos_product_categories',   'id'],
            'product'           => [db_prefix() . 'pos_products',            'id'],
            'unit'              => [db_prefix() . 'pos_inv_units',           'id'],
            'brand'             => [db_prefix() . 'pos_inv_brands',          'id'],
            'supplier'          => [db_prefix() . 'pos_inv_suppliers',       'id'],
            'receipt'           => [db_prefix() . 'pos_inv_receipts',        'id'],
            'delivery'          => [db_prefix() . 'pos_inv_deliveries',      'id'],
            'transfer'          => [db_prefix() . 'pos_inv_transfers',       'id'],
            'adjustment'        => [db_prefix() . 'pos_inv_adjustments',     'id'],
            'return'            => [db_prefix() . 'pos_inv_returns',         'id'],
            'packing'           => [db_prefix() . 'pos_inv_packing_lists',   'id'],
            'stocktake'         => [db_prefix() . 'pos_inv_stock_takes',     'id'],
            // Attribute tables (warehouse module tables)
            'attr_commodity_types'  => [db_prefix() . 'ware_commodity_type', 'commodity_type_id'],
            'attr_commodity_groups' => [db_prefix() . 'items_groups',         'id'],
            'attr_sub_groups'       => [db_prefix() . 'wh_sub_group',         'id'],
            'attr_units'            => [db_prefix() . 'ware_unit_type',       'unit_type_id'],
            'attr_colors'           => [db_prefix() . 'ware_color',           'color_id'],
            'attr_models'           => [db_prefix() . 'wh_model',            'id'],
            'attr_sizes'            => [db_prefix() . 'ware_size_type',       'size_type_id'],
            'attr_styles'           => [db_prefix() . 'ware_style_type',      'style_type_id'],
        ];
        // Handle attr delete: $type = 'attr', $id = record id, and type param in POST
        if ($type === 'attr') {
            $attr_sub = $this->input->post('sub_type');
            $type = 'attr_' . ($attr_sub ?: $this->input->get('sub_type'));
        }
        if (!isset($table_map[$type])) { echo json_encode(['success' => false, 'error' => 'Unknown type']); return; }
        [$table, $col] = $table_map[$type];
        $this->db->where($col, $id)->delete($table);
        echo json_encode(['success' => true]);
    }

    // ─── Inventory view/form stubs (to be expanded) ────────────────────────────

    public function inv_view($type, $id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory')) {
            $this->_pos_deny(); return;
        }
        [$branch_id, $branches] = $this->_inv_common_data();

        if ($type === 'stocktake') {
            $this->_ensure_stocktake_schema();
            $session = $this->db->select('s.*, b.name AS branch_name,
                                           CONCAT(cr.firstname," ",cr.lastname) AS created_by_name,
                                           CONCAT(ap.firstname," ",ap.lastname) AS approved_by_name')
                ->from(db_prefix().'pos_inv_stock_takes s')
                ->join(db_prefix().'pos_branches b', 'b.id = s.branch_id', 'left')
                ->join(db_prefix().'staff cr', 'cr.staffid = s.created_by', 'left')
                ->join(db_prefix().'staff ap', 'ap.staffid = s.approved_by', 'left')
                ->where('s.id', (int)$id)->get()->row_array();
            if (!$session) { show_404(); return; }
            $categories = $this->db->order_by('name')->get(db_prefix().'pos_product_categories')->result_array();
            $products   = $this->db->select('id,name,sku,barcode,cost_price')->where('is_active',1)->order_by('name')
                ->get(db_prefix().'pos_products')->result_array();
            $reason_codes = [
                'stocktake'         => 'Stock Take Adjustment',
                'damaged'           => 'Damaged / Spoiled',
                'shrinkage'         => 'Shrinkage / Theft',
                'administrative'    => 'Administrative Error',
                'found_stock'       => 'Found Stock',
                'expired'           => 'Expired Goods',
                'supplier_error'    => 'Supplier Error',
                'system_error'      => 'System / Count Error',
            ];
            $this->load->view('pos_system/admin/inventory/stocktake_session', [
                'title'        => 'Stocktake — ' . $session['stocktake_number'],
                'inv_section'  => 'stocktake',
                'branch_id'    => $branch_id,
                'branches'     => $branches,
                'session'      => $session,
                'categories'   => $categories,
                'products'     => $products,
                'reason_codes' => $reason_codes,
                'is_manager'   => pos_can_access('manager'),
            ]);
            return;
        }

        $this->load->view('pos_system/admin/inventory/doc_view', [
            'title'       => ucfirst($type) . ' Details',
            'inv_section' => $type . 's',
            'doc_type'    => $type,
            'doc_id'      => (int)$id,
            'branch_id'   => $branch_id,
            'branches'    => $branches,
        ]);
    }

    public function inv_po_items($po_id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'create')) {
            echo json_encode(['error' => 'forbidden']); return;
        }
        $po_id = (int)$po_id;
        if (!$po_id || !$this->db->table_exists(db_prefix() . 'pur_order_detail')) {
            echo json_encode(['items' => []]); return;
        }

        $sql = "SELECT
                    d.id,
                    d.item_code,
                    d.quantity,
                    d.unit_price,
                    d.description,
                    COALESCE(p.id, 0)         AS pos_product_id,
                    COALESCE(p.name, it.description, d.description, '') AS product_name,
                    COALESCE(p.sku, '')        AS sku,
                    COALESCE(p.cost_price, d.unit_price, 0) AS cost_price
                FROM " . db_prefix() . "pur_order_detail d
                LEFT JOIN " . db_prefix() . "items it ON it.id = d.item_code
                LEFT JOIN " . db_prefix() . "pos_products p ON p.perfex_item_id = d.item_code AND p.is_active = 1
                WHERE d.pur_order = " . $po_id . "
                ORDER BY d.id ASC";

        $rows = $this->db->query($sql)->result_array();
        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'pos_product_id' => (int)$r['pos_product_id'],
                'product_name'   => $r['product_name'],
                'sku'            => $r['sku'],
                'quantity'       => (float)$r['quantity'],
                'unit_price'     => (float)$r['unit_price'],
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(['items' => $items]);
    }

    public function inv_form($type, $id = null)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'create')) {
            $this->_pos_deny(); return;
        }
        [$branch_id, $branches] = $this->_inv_common_data();
        $doc_id = (int)$id;

        if ($type === 'receipt') {
            $existing = [];
            if ($doc_id > 0) {
                $existing = $this->db->where('id', $doc_id)->get(db_prefix().'pos_inv_receipts')->row_array() ?: [];
            }
            $staff_list = $this->db->select('staffid, firstname, lastname')
                ->where('active', 1)->order_by('firstname')->get(db_prefix().'staff')->result_array();
            $departments = $this->db->order_by('name')->get(db_prefix().'departments')->result_array();
            // Pull POs from the purchase module if installed
            $purchase_orders = [];
            if ($this->db->table_exists(db_prefix() . 'pur_orders')) {
                $sql = "SELECT o.id,
                               o.pur_order_number AS po_number,
                               o.vendor AS supplier_id,
                               CASE WHEN v.company IS NOT NULL AND v.company != '' THEN v.company
                                    ELSE o.pur_order_name END AS supplier_name
                        FROM " . db_prefix() . "pur_orders o
                        LEFT JOIN " . db_prefix() . "pur_vendor v ON v.userid = o.vendor
                        ORDER BY o.id DESC";
                $purchase_orders = $this->db->query($sql)->result_array();
            }
            // Pull vendors from purchase module if installed, else fall back to pos_inv_suppliers
            $suppliers = [];
            if ($this->db->table_exists(db_prefix() . 'pur_vendor')) {
                $sql2 = "SELECT userid AS id,
                                CASE WHEN company IS NOT NULL AND company != '' THEN company
                                     ELSE 'Unknown' END AS name
                         FROM " . db_prefix() . "pur_vendor
                         WHERE active = 1
                         ORDER BY company ASC";
                $suppliers = $this->db->query($sql2)->result_array();
            }
            if (empty($suppliers)) {
                $suppliers = $this->db->select('id, name')->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_inv_suppliers')->result_array();
            }
            $this->load->view('pos_system/admin/inventory/receipt_form', [
                'title'           => ($doc_id ? 'Edit' : 'New') . ' Inventory Receipt',
                'inv_section'     => 'receiving',
                'doc_type'        => 'receipt',
                'doc_id'          => $doc_id,
                'branch_id'       => $branch_id,
                'branches'        => $branches,
                'existing'        => $existing,
                'suppliers'       => $suppliers,
                'products'        => $this->db->select('id,name,sku,barcode,cost_price')->where('is_active',1)->order_by('name')->get(db_prefix().'pos_products')->result_array(),
                'tax_rates'       => $this->db->where('is_active',1)->get(db_prefix().'pos_tax_rates')->result_array(),
                'staff_list'      => $staff_list,
                'departments'     => $departments,
                'purchase_orders' => $purchase_orders,
            ]);
            return;
        }

        if ($type === 'delivery') {
            $existing = [];
            if ($doc_id > 0) {
                $existing = $this->db->where('id', $doc_id)->get(db_prefix().'pos_inv_deliveries')->row_array() ?: [];
            }
            $staff_list = $this->db->select('staffid, firstname, lastname')
                ->where('active', 1)->order_by('firstname')->get(db_prefix().'staff')->result_array();
            $departments = $this->db->order_by('name')->get(db_prefix().'departments')->result_array();

            // Perfex CRM invoices for linking (exclude drafts where number = -1)
            $invoices = $this->db
                ->select("i.id, i.number, i.prefix, i.status, i.clientid, i.project_id,
                          COALESCE(c.company, i.deleted_customer_name, '') AS customer_name,
                          REPLACE(REPLACE(REPLACE(c.billing_street, '<br />', ', '), '<br/>', ', '), '<br>', ', ') AS billing_street,
                          c.billing_city, c.billing_state, c.billing_zip")
                ->from(db_prefix().'invoices i')
                ->join(db_prefix().'clients c', 'c.userid = i.clientid', 'left')
                ->where('i.number !=', -1)
                ->order_by('i.id', 'DESC')
                ->get()->result_array();
            // Pre-compute display number and clean address for each invoice
            foreach ($invoices as &$inv) {
                $inv['display_number'] = $inv['prefix'] . str_pad($inv['number'], 5, '0', STR_PAD_LEFT);
                $parts = array_filter([
                    trim(strip_tags($inv['billing_street'] ?? '')),
                    trim($inv['billing_city']  ?? ''),
                    trim($inv['billing_state'] ?? ''),
                    trim($inv['billing_zip']   ?? ''),
                ]);
                $inv['full_address'] = implode(', ', $parts);
            }
            unset($inv);

            // Perfex CRM clients for customer / receiver dropdowns
            $clients_raw = $this->db
                ->select("userid, company,
                          REPLACE(REPLACE(REPLACE(billing_street, '<br />', ', '), '<br/>', ', '), '<br>', ', ') AS billing_street,
                          billing_city, billing_state, billing_zip")
                ->where('active', 1)
                ->order_by('company')
                ->get(db_prefix().'clients')->result_array();
            foreach ($clients_raw as &$cl) {
                $parts = array_filter([
                    trim(strip_tags($cl['billing_street'] ?? '')),
                    trim($cl['billing_city']  ?? ''),
                    trim($cl['billing_state'] ?? ''),
                    trim($cl['billing_zip']   ?? ''),
                ]);
                $cl['full_address'] = implode(', ', $parts);
            }
            unset($cl);

            // Perfex CRM projects for project dropdown
            $crm_projects = $this->db->select('id, name')->order_by('name')->get(db_prefix().'projects')->result_array();

            // Current logged-in staff (default Sales Person)
            $current_staff_id   = get_staff_user_id();
            $current_staff_row  = $this->db->where('staffid', $current_staff_id)->get(db_prefix().'staff')->row_array();
            $current_staff_name = $current_staff_row
                ? trim(($current_staff_row['firstname'] ?? '') . ' ' . ($current_staff_row['lastname'] ?? ''))
                : '';

            // Products with selling price
            $products = $this->db->select('id,name,sku,barcode,selling_price,tax_rate_id')
                ->where('is_active',1)->order_by('name')
                ->get(db_prefix().'pos_products')->result_array();
            // Build stock map: "branch_id_product_id" => quantity
            $stock_rows = $this->db->select('branch_id, product_id, quantity')
                ->get(db_prefix().'pos_inventory')->result_array();
            $stock_map = [];
            foreach ($stock_rows as $s) {
                $stock_map[$s['branch_id'] . '_' . $s['product_id']] = (float)$s['quantity'];
            }
            // Sales Orders for linking (confirmed/processing/partially_delivered)
            $so_link_id = (int)$this->input->get('so_id');
            $sales_orders_for_link = $this->db->table_exists(db_prefix().'pos_sales_orders')
                ? $this->db
                    ->select('id, so_number, customer_name, address, project_id, project_name, client_id, sales_person_id, sales_person')
                    ->where_in('status', ['confirmed','processing','partially_delivered'])
                    ->order_by('id', 'DESC')
                    ->get(db_prefix().'pos_sales_orders')->result_array()
                : [];

            // Pre-select SO from GET param
            $preselect_so = null;
            if ($so_link_id) {
                foreach ($sales_orders_for_link as $so) {
                    if ((int)$so['id'] === $so_link_id) { $preselect_so = $so; break; }
                }
            }

            $this->load->view('pos_system/admin/inventory/delivery_form', [
                'title'              => ($doc_id ? 'Edit' : 'New') . ' Inventory Delivery',
                'inv_section'        => 'deliveries',
                'doc_type'           => 'delivery',
                'doc_id'             => $doc_id,
                'branch_id'          => $branch_id,
                'branches'           => $branches,
                'existing'           => $existing,
                'products'           => $products,
                'tax_rates'          => $this->db->where('is_active',1)->get(db_prefix().'pos_tax_rates')->result_array(),
                'staff_list'         => $staff_list,
                'departments'        => $departments,
                'invoices'           => $invoices,
                'clients'            => $clients_raw,
                'crm_projects'       => $crm_projects,
                'current_staff_id'   => $current_staff_id,
                'current_staff_name' => $current_staff_name,
                'stock_map'          => $stock_map,
                'sales_orders'       => $sales_orders_for_link,
                'preselect_so'       => $preselect_so,
                'so_link_id'         => $so_link_id,
            ]);
            return;
        }

        $this->load->view('pos_system/admin/inventory/doc_form', [
            'title'       => ($doc_id ? 'Edit ' : 'New ') . ucfirst($type),
            'inv_section' => $type . 's',
            'doc_type'    => $type,
            'doc_id'      => $doc_id,
            'branch_id'   => $branch_id,
            'branches'    => $branches,
            'suppliers'   => $this->db->where('is_active',1)->get(db_prefix().'pos_inv_suppliers')->result_array(),
            'products'    => $this->db->where('is_active',1)->get(db_prefix().'pos_products')->result_array(),
        ]);
    }

    /**
     * AJAX: sync warehouse items into pos_products catalog.
     */
    public function sync_warehouse()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_inventory', 'edit')) {
            $this->_pos_deny(); return;
        }

        $synced = $this->pos_integrations->sync_warehouse_items_to_pos();
        set_alert('success', _l('pos_sync_complete', $synced));
        redirect(admin_url('pos_system/inventory'));
    }

    // ─── Reports ─────────────────────────────────────────────────────────────

    public function reports()
    {
        if (!pos_can_access('supervisor') && !pos_perm('pos_reports')) {
            $this->_pos_deny(); return;
        }

        // Non-admins are locked to their assigned branch; admins may pick via GET
        $branch_filter = pos_get_auth_branch();
        $branch_id     = ($branch_filter !== null)
            ? $branch_filter
            : ((int)($this->input->get('branch_id') ?: 0) ?: pos_get_staff_branch() ?: 1);
        $from          = $this->input->get('from') ?? date('Y-m-01');
        $to            = $this->input->get('to')   ?? date('Y-m-t');

        $token = $this->pos_auth->issue_token(get_staff_user_id(), $branch_id);

        // Admins can switch branches; non-admins only see their own branch
        $branches = ($branch_filter !== null)
            ? ($branch_filter ? [$this->pos_branches_model->get($branch_filter)] : [])
            : $this->pos_branches_model->get_all(['is_active' => 1]);

        $data = [
            'title'     => _l('pos_reports'),
            'report'    => $this->pos_model->get_period_report($branch_id, $from, $to),
            'from'      => $from,
            'to'        => $to,
            'branches'  => array_filter($branches),
            'api_token' => $token,
            'api_url'   => base_url('pos_system/api'),
            'branch_id' => $branch_id,
        ];

        $this->load->view('pos_system/admin/reports/index', $data);
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    public function settings()
    {
        if (!is_admin()) {
            $this->_pos_deny(); return;
        }

        $branch_id = pos_get_staff_branch();

        if ($this->input->post()) {
            $post = $this->input->post();
            // Normalise toggle/checkbox fields — unchecked boxes are absent from POST
            $bool_fields = [
                'pos_allow_negative_stock', 'pos_auto_print_receipt', 'pos_require_session',
                'pos_warehouse_sync', 'pos_accounting_sync',
                'pos_enable_cash', 'pos_enable_card', 'pos_enable_mpesa', 'pos_enable_airtel',
                'pos_enable_mtn', 'pos_enable_telebirr', 'pos_enable_bank', 'pos_enable_credit',
                'pos_restaurant_mode', 'pos_restaurant_require_table', 'pos_restaurant_auto_deduct',
            ];
            foreach ($bool_fields as $bf) {
                $post[$bf] = isset($post[$bf]) ? '1' : '0';
            }
            // Global settings (restaurant mode, mobile money, etc.) must be saved with
            // branch_id=null so pos_get_setting() finds them without a branch context
            // (e.g., sidebar menu visibility checks use no branch_id).
            $this->pos_model->save_settings($post, null);
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('pos_system/settings'));
        }

        // Load profiles data for the Profiles tab embedded in settings
        $profiles = $this->pos_profiles_model->get_all();
        foreach ($profiles as &$p) {
            $p['assigned_users']     = $this->pos_profiles_model->get_users((int) $p['id']);
            $p['payment_method_ids'] = $this->pos_profiles_model->get_payment_method_ids((int) $p['id']);
        }
        unset($p);

        $staff_list = $this->db->select('staffid AS id, firstname, lastname, email')
            ->where('active', 1)->order_by('firstname')->get(db_prefix() . 'staff')->result_array();

        $data = [
            'title'           => _l('pos_settings'),
            'settings'        => $this->pos_model->get_all_settings(null),
            'branches'        => $this->pos_branches_model->get_all(['is_active' => 1]),
            'profiles'        => $profiles,
            'payment_methods' => $this->_get_all_payment_methods(),
            'print_templates' => $this->pos_profiles_model->get_print_templates(),
            'price_lists'     => $this->pos_profiles_model->get_price_lists(),
            'staff_list'      => $staff_list,
        ];

        $this->load->view('pos_system/admin/settings/index', $data);
    }

    // ─── Restaurant Management ────────────────────────────────────────────────

    public function restaurant($sub = 'index')
    {
        if (!is_admin() && !pos_can_access('manager')) {
            $this->_pos_deny(); return;
        }

        $method = '_restaurant_' . $sub;
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        // Default: restaurant hub / index
        $branch_id = (int) ($this->input->get('branch_id') ?: pos_get_staff_branch());
        $data = [
            'title'     => 'Restaurant Management',
            'branch_id' => $branch_id,
            'branches'  => $this->pos_branches_model->get_all(['is_active' => 1]),
            'areas'     => $this->db->where('branch_id', $branch_id)->where('is_active', 1)->order_by('display_order')->get(db_prefix() . 'pos_restaurant_areas')->result_array(),
            'tables'    => $this->db->where('branch_id', $branch_id)->where('is_active', 1)->order_by('table_number')->get(db_prefix() . 'pos_restaurant_tables')->result_array(),
            'open_kots' => $this->db->where('branch_id', $branch_id)->where_in('status', ['pending', 'preparing'])->order_by('date_created', 'DESC')->get(db_prefix() . 'pos_restaurant_kots')->result_array(),
        ];
        $this->load->view('pos_system/admin/restaurant/index', $data);
    }

    private function _restaurant_areas()
    {
        $branch_id = (int) ($this->input->get('branch_id') ?: pos_get_staff_branch());
        if ($this->input->post()) {
            $id     = (int) $this->input->post('id');
            $fields = [
                'branch_id'     => $branch_id,
                'name'          => $this->input->post('name', true),
                'type'          => $this->input->post('type', true),
                'display_order' => (int) $this->input->post('display_order'),
                'is_active'     => (int) (bool) $this->input->post('is_active'),
            ];
            if ($id) {
                $this->db->where('id', $id)->update(db_prefix() . 'pos_restaurant_areas', $fields);
                $msg = 'Production area updated.';
            } else {
                $this->db->insert(db_prefix() . 'pos_restaurant_areas', $fields);
                $msg = 'Production area added.';
            }
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'message' => $msg]));
                return;
            }
            set_alert('success', $msg);
            redirect(admin_url('pos_system/restaurant/areas'));
        }
        $data = [
            'title'     => 'Production Areas',
            'branch_id' => $branch_id,
            'branches'  => $this->pos_branches_model->get_all(['is_active' => 1]),
            'areas'     => $this->db->where('branch_id', $branch_id)->order_by('display_order', 'ASC')->get(db_prefix() . 'pos_restaurant_areas')->result_array(),
        ];
        $this->load->view('pos_system/admin/restaurant/areas', $data);
    }

    private function _restaurant_tables()
    {
        $branch_id = (int) ($this->input->get('branch_id') ?: pos_get_staff_branch());
        if ($this->input->post()) {
            $id     = (int) $this->input->post('id');
            $fields = [
                'branch_id'    => $branch_id,
                'table_number' => $this->input->post('table_number', true),
                'name'         => $this->input->post('name', true),
                'seats'        => max(1, (int) $this->input->post('seats')),
                'floor'        => $this->input->post('floor', true),
                'is_active'    => (int) (bool) $this->input->post('is_active'),
            ];
            if ($id) {
                $this->db->where('id', $id)->update(db_prefix() . 'pos_restaurant_tables', $fields);
                $msg = 'Table updated.';
            } else {
                $this->db->insert(db_prefix() . 'pos_restaurant_tables', $fields);
                $msg = 'Table added.';
            }
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'message' => $msg]));
                return;
            }
            set_alert('success', $msg);
            redirect(admin_url('pos_system/restaurant/tables'));
        }
        $data = [
            'title'     => 'Table Management',
            'branch_id' => $branch_id,
            'branches'  => $this->pos_branches_model->get_all(['is_active' => 1]),
            'tables'    => $this->db->where('branch_id', $branch_id)->order_by('table_number', 'ASC')->get(db_prefix() . 'pos_restaurant_tables')->result_array(),
        ];
        $this->load->view('pos_system/admin/restaurant/tables', $data);
    }

    private function _restaurant_recipes()
    {
        if ($this->input->post()) {
            $id = (int) $this->input->post('id');
            $fields = [
                'product_id'   => (int) $this->input->post('product_id'),
                'name'         => $this->input->post('name', true),
                'yield_qty'    => (float) $this->input->post('yield_qty') ?: 1,
                'area_id'      => (int) $this->input->post('area_id') ?: null,
                'prep_minutes' => max(0, (int) $this->input->post('prep_minutes')),
                'notes'        => $this->input->post('notes', true),
                'is_active'    => (int) (bool) $this->input->post('is_active'),
            ];
            $this->db->trans_start();
            if ($id) {
                $this->db->where('id', $id)->update(db_prefix() . 'pos_restaurant_recipes', $fields);
            } else {
                $this->db->insert(db_prefix() . 'pos_restaurant_recipes', $fields);
                $id = $this->db->insert_id();
            }
            // Save recipe items
            $this->db->where('recipe_id', $id)->delete(db_prefix() . 'pos_restaurant_recipe_items');
            $ing_ids  = $this->input->post('ingredient_id') ?: [];
            $ing_qtys = $this->input->post('ingredient_qty') ?: [];
            $ing_units= $this->input->post('ingredient_unit_id') ?: [];
            foreach ($ing_ids as $k => $iid) {
                $iid = (int) $iid;
                if (!$iid) continue;
                $this->db->insert(db_prefix() . 'pos_restaurant_recipe_items', [
                    'recipe_id'     => $id,
                    'ingredient_id' => $iid,
                    'quantity'      => (float) ($ing_qtys[$k] ?? 1),
                    'unit_id'       => (int) ($ing_units[$k] ?? 0) ?: null,
                ]);
            }
            $this->db->trans_complete();
            if ($this->input->is_ajax_request()) {
                $ok = $this->db->trans_status();
                $this->output->set_content_type('application/json')->set_output(json_encode(['success' => $ok, 'id' => $id]));
                return;
            }
            set_alert('success', 'Recipe saved.');
            redirect(admin_url('pos_system/restaurant/recipes'));
        }

        $branch_id = (int) pos_get_staff_branch();
        $data = [
            'title'     => 'Recipe Management',
            'products'  => $this->db->select('id, name, sku')->where('is_active', 1)->order_by('name')->get(db_prefix() . 'pos_products')->result_array(),
            'recipes'   => $this->db->select('r.*, p.name AS product_name, a.name AS area_name')
                               ->from(db_prefix() . 'pos_restaurant_recipes r')
                               ->join(db_prefix() . 'pos_products p', 'p.id = r.product_id', 'left')
                               ->join(db_prefix() . 'pos_restaurant_areas a', 'a.id = r.area_id', 'left')
                               ->order_by('p.name')->get()->result_array(),
            'areas'     => $this->db->where('is_active', 1)->get(db_prefix() . 'pos_restaurant_areas')->result_array(),
            'units'     => $this->db->get(db_prefix() . 'pos_inv_units')->result_array(),
            'branch_id' => $branch_id,
        ];
        $this->load->view('pos_system/admin/restaurant/recipes', $data);
    }

    private function _restaurant_kitchen()
    {
        $branch_id = (int) ($this->input->get('branch_id') ?: pos_get_staff_branch());
        $area_id   = (int) $this->input->get('area_id');

        $q = $this->db->select('k.*, a.name AS area_name, t.table_number')
                       ->from(db_prefix() . 'pos_restaurant_kots k')
                       ->join(db_prefix() . 'pos_restaurant_areas a', 'a.id = k.area_id', 'left')
                       ->join(db_prefix() . 'pos_restaurant_tables t', 't.id = k.table_id', 'left')
                       ->where('k.branch_id', $branch_id)
                       ->where_in('k.status', ['pending', 'preparing', 'ready'])
                       ->order_by('k.date_created', 'ASC');
        if ($area_id) $q->where('k.area_id', $area_id);
        $kots = $q->get()->result_array();

        foreach ($kots as &$kot) {
            $kot['items'] = $this->db->where('kot_id', $kot['id'])->get(db_prefix() . 'pos_restaurant_kot_items')->result_array();
        }
        unset($kot);

        $data = [
            'title'     => 'Kitchen Display',
            'branch_id' => $branch_id,
            'area_id'   => $area_id,
            'kots'      => $kots,
            'branches'  => $this->pos_branches_model->get_all(['is_active' => 1]),
            'areas'     => $this->db->where('branch_id', $branch_id)->where('is_active', 1)->order_by('display_order')->get(db_prefix() . 'pos_restaurant_areas')->result_array(),
            'refresh'   => (int) pos_get_setting('pos_restaurant_refresh_secs') ?: 15,
        ];
        $this->load->view('pos_system/admin/restaurant/kitchen', $data);
    }

    public function restaurant_delete_area()
    {
        if (!is_admin() && !pos_can_access('manager')) { $this->_pos_deny(); return; }
        $id = (int) $this->input->post('id');
        if ($id) $this->db->where('id', $id)->delete(db_prefix() . 'pos_restaurant_areas');
        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => (bool)$id]));
    }

    public function restaurant_delete_table()
    {
        if (!is_admin() && !pos_can_access('manager')) { $this->_pos_deny(); return; }
        $id = (int) $this->input->post('id');
        if ($id) $this->db->where('id', $id)->delete(db_prefix() . 'pos_restaurant_tables');
        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => (bool)$id]));
    }

    public function restaurant_delete_recipe()
    {
        if (!is_admin() && !pos_can_access('manager')) { $this->_pos_deny(); return; }
        $id = (int) $this->input->post('id');
        if ($id) $this->db->where('id', $id)->delete(db_prefix() . 'pos_restaurant_recipes');
        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => (bool)$id]));
    }

    public function restaurant_recipe_get($id = 0)
    {
        if (!is_admin() && !pos_can_access('manager')) { $this->_pos_deny(); return; }
        $id = (int) $id;
        $recipe = $this->db->where('id', $id)->get(db_prefix() . 'pos_restaurant_recipes')->row_array();
        if ($recipe) {
            $recipe['items'] = $this->db->where('recipe_id', $id)->get(db_prefix() . 'pos_restaurant_recipe_items')->result_array();
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($recipe ?: []));
    }

    public function kot_update_status()
    {
        if (!is_admin() && !pos_can_access('cashier')) { $this->_pos_deny(); return; }
        $id     = (int) $this->input->post('id');
        $status = $this->input->post('status', true);

        $valid = ['pending', 'preparing', 'ready', 'served', 'cancelled'];
        if (!$id || !in_array($status, $valid, true)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'error' => 'Invalid input']));
            return;
        }

        $upd = ['status' => $status];
        if ($status === 'preparing') $upd['started_at'] = date('Y-m-d H:i:s');
        if ($status === 'ready')     $upd['ready_at']   = date('Y-m-d H:i:s');
        if ($status === 'served')    $upd['served_at']  = date('Y-m-d H:i:s');

        $this->db->where('id', $id)->update(db_prefix() . 'pos_restaurant_kots', $upd);

        // Auto-deduct inventory when order goes to "ready"
        if ($status === 'ready' && pos_get_setting('pos_restaurant_auto_deduct') == '1') {
            $this->_restaurant_deduct_stock($id);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
    }

    private function _restaurant_deduct_stock($kot_id)
    {
        $kot = $this->db->where('id', $kot_id)->get(db_prefix() . 'pos_restaurant_kots')->row_array();
        if (!$kot) return;

        $items = $this->db->where('kot_id', $kot_id)->get(db_prefix() . 'pos_restaurant_kot_items')->result_array();
        foreach ($items as $item) {
            $recipe = $this->db->where('product_id', $item['product_id'])->where('is_active', 1)->get(db_prefix() . 'pos_restaurant_recipes')->row_array();
            if (!$recipe) continue;

            $multiplier = (float) $item['quantity'] / max(1, (float) $recipe['yield_qty']);
            $ingredients = $this->db->where('recipe_id', $recipe['id'])->get(db_prefix() . 'pos_restaurant_recipe_items')->result_array();

            foreach ($ingredients as $ing) {
                $deduct_qty = (float) $ing['quantity'] * $multiplier;
                // Read current qty first
                $inv_row = $this->db->select('quantity')->where('product_id', $ing['ingredient_id'])->where('branch_id', $kot['branch_id'])->get(db_prefix() . 'pos_inventory')->row_array();
                $qty_before = (float) ($inv_row['quantity'] ?? 0);
                $qty_after  = $qty_before - $deduct_qty;

                $this->db->where('product_id', $ing['ingredient_id'])
                         ->where('branch_id', $kot['branch_id'])
                         ->set('quantity', 'quantity - ' . $deduct_qty, false)
                         ->update(db_prefix() . 'pos_inventory');

                $this->db->insert(db_prefix() . 'pos_inventory_movements', [
                    'product_id'     => $ing['ingredient_id'],
                    'branch_id'      => $kot['branch_id'],
                    'type'           => 'sale',
                    'qty_change'     => -$deduct_qty,
                    'qty_before'     => $qty_before,
                    'qty_after'      => $qty_after,
                    'reference_type' => 'kot',
                    'reference_id'   => $kot_id,
                    'staff_id'       => get_staff_user_id() ?? 0,
                    'date_created'   => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    // ─── POS Profiles ────────────────────────────────────────────────────────

    public function profiles()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles')) {
            $this->_pos_deny(); return;
        }

        $staff_list = $this->db
            ->select('staffid as id, firstname, lastname, email')
            ->where('active', 1)
            ->order_by('firstname', 'ASC')
            ->get(db_prefix() . 'staff')->result_array();

        $profiles = $this->pos_profiles_model->get_all();
        foreach ($profiles as &$p) {
            $p['assigned_users']     = $this->pos_profiles_model->get_users((int) $p['id']);
            $p['payment_method_ids'] = $this->pos_profiles_model->get_payment_method_ids((int) $p['id']);
        }
        unset($p);

        $data = [
            'title'           => _l('pos_profiles'),
            'profiles'        => $profiles,
            'branches'        => $this->pos_branches_model->get_all(['is_active' => 1]),
            'price_lists'     => $this->pos_profiles_model->get_price_lists(),
            'item_groups'     => $this->pos_profiles_model->get_item_groups(),
            'customer_groups' => $this->pos_profiles_model->get_customer_groups(),
            'print_templates' => $this->pos_profiles_model->get_print_templates(),
            'payment_methods' => $this->_get_all_payment_methods(),
            'staff_list'      => $staff_list,
        ];

        $this->load->view('pos_system/admin/profiles/index', $data);
    }

    public function profile_save()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles', 'create') && !pos_perm('pos_profiles', 'edit')) {
            $this->_pos_deny(); return;
        }

        $id   = (int) $this->input->post('id');
        $post = $this->input->post();

        $bool_fields = [
            'hide_images', 'hide_unavailable_items', 'auto_add_item_to_cart',
            'validate_stock_on_save', 'print_receipt_on_order_complete',
            'ignore_pricing_rule', 'allow_rate_change', 'allow_discount_change',
            'set_grand_total_to_default_mop', 'allow_partial_payment',
            'enable_cash_rounding', 'disable_rounded_total', 'auto_create_invoice',
        ];
        foreach ($bool_fields as $f) {
            $post[$f] = isset($post[$f]) ? 1 : 0;
        }

        $post['item_group_ids']     = $this->input->post('item_group_ids')     ?? [];
        $post['customer_group_ids'] = $this->input->post('customer_group_ids') ?? [];
        $payment_method_ids         = $this->input->post('payment_method_ids') ?? [];

        if ($id) {
            $this->pos_profiles_model->update($id, $post);
            $this->pos_profiles_model->sync_payment_methods($id, $payment_method_ids);
            set_alert('success', _l('updated_successfully', _l('pos_profile')));
        } else {
            $post['created_by'] = get_staff_user_id();
            $new_id = $this->pos_profiles_model->create($post);
            if ($new_id) {
                $this->pos_profiles_model->sync_payment_methods($new_id, $payment_method_ids);
            }
            set_alert('success', _l('added_successfully', _l('pos_profile')));
        }

        redirect(admin_url('pos_system/profiles'));
    }

    public function profile_delete(int $id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles', 'delete')) {
            $this->_pos_deny(); return;
        }

        $this->pos_profiles_model->delete($id);
        set_alert('success', _l('deleted_successfully', _l('pos_profile')));
        redirect(admin_url('pos_system/profiles'));
    }

    // ─── Profile user AJAX endpoints ─────────────────────────────────────────

    public function profile_users_get(int $profile_id)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles')) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $users = $this->pos_profiles_model->get_users($profile_id);
        $this->output->set_content_type('application/json');
        echo json_encode($users);
    }

    public function profile_user_assign()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles', 'edit')) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $profile_id = (int) $this->input->post('profile_id');
        $staff_id   = (int) $this->input->post('staff_id');
        $is_default = (bool) $this->input->post('is_default');

        if (!$profile_id || !$staff_id) {
            echo json_encode(['success' => false, 'error' => 'Missing params']);
            return;
        }

        $ok = $this->pos_profiles_model->assign_user($profile_id, $staff_id);
        if ($ok && $is_default) {
            $this->pos_profiles_model->set_default_user($profile_id, $staff_id);
        }

        $this->output->set_content_type('application/json');
        echo json_encode(['success' => $ok, 'users' => $this->pos_profiles_model->get_users($profile_id)]);
    }

    public function profile_user_remove()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles', 'edit')) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $profile_id = (int) $this->input->post('profile_id');
        $staff_id   = (int) $this->input->post('staff_id');

        $ok = $this->pos_profiles_model->remove_user($profile_id, $staff_id);
        $this->output->set_content_type('application/json');
        echo json_encode(['success' => $ok, 'users' => $this->pos_profiles_model->get_users($profile_id)]);
    }

    public function profile_user_set_default()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_profiles', 'edit')) {
            $this->output->set_status_header(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $profile_id = (int) $this->input->post('profile_id');
        $staff_id   = (int) $this->input->post('staff_id');

        $ok = $this->pos_profiles_model->set_default_user($profile_id, $staff_id);
        $this->output->set_content_type('application/json');
        echo json_encode(['success' => $ok, 'users' => $this->pos_profiles_model->get_users($profile_id)]);
    }

    // ─── Invoices ────────────────────────────────────────────────────────────

    public function invoices()
    {
        if (!pos_can_access('supervisor') && !pos_perm('pos_invoices')) {
            $this->_pos_deny(); return;
        }

        // Lock non-admins to their own branch; admins may switch via GET
        $branch_filter = pos_get_auth_branch();
        $branch_id     = ($branch_filter !== null)
            ? $branch_filter
            : ((int)($this->input->get('branch_id') ?: 0) ?: pos_get_staff_branch());
        $from          = $this->input->get('from') ?? date('Y-m-01');
        $to            = $this->input->get('to')   ?? date('Y-m-t');

        $branches = ($branch_filter !== null)
            ? ($branch_filter ? [$this->pos_branches_model->get($branch_filter)] : [])
            : $this->pos_branches_model->get_all(['is_active' => 1]);

        $data = [
            'title'    => _l('pos_invoices'),
            'invoices' => $branch_id
                ? $this->pos_invoice_model->get_by_branch($branch_id, [
                    'date_from' => $from,
                    'date_to'   => $to,
                ], 200)
                : [],
            'from'     => $from,
            'to'       => $to,
            'branches' => array_filter($branches),
        ];

        $this->load->view('pos_system/admin/invoices/index', $data);
    }

    // ─── Payment Methods ─────────────────────────────────────────────────────

    public function payment_methods()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_payment_methods')) {
            $this->_pos_deny(); return;
        }

        // Pull Perfex configured payment gateways for mapping
        $perfex_gateways = $this->_get_perfex_gateways();

        $data = [
            'title'            => _l('pos_payment_methods'),
            'payment_methods'  => $this->_get_all_payment_methods(),
            'perfex_gateways'  => $perfex_gateways,
            'branches'         => $this->pos_branches_model->get_all(['is_active' => 1]),
        ];

        $this->load->view('pos_system/admin/payment_methods/index', $data);
    }

    public function payment_method_save()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_payment_methods', 'edit') && !pos_perm('pos_payment_methods', 'create')) {
            $this->_pos_deny(); return;
        }

        $id   = (int) $this->input->post('id');
        $data = $this->input->post([
            'name', 'code', 'type', 'provider', 'perfex_gateway',
            'account_key', 'sort_order',
        ]);
        $data['allow_in_returns'] = $this->input->post('allow_in_returns') ? 1 : 0;
        $data['is_default']       = $this->input->post('is_default')       ? 1 : 0;
        $data['is_active']        = $this->input->post('is_active')        ? 1 : 0;

        if ($id) {
            // If setting as default, clear other defaults first
            if ($data['is_default']) {
                $this->db->set('is_default', 0)->update(db_prefix() . 'pos_payment_methods');
            }
            $this->db->where('id', $id)->update(db_prefix() . 'pos_payment_methods', $data);
            set_alert('success', _l('updated_successfully', _l('pos_payment_method')));
        } else {
            if ($data['is_default']) {
                $this->db->set('is_default', 0)->update(db_prefix() . 'pos_payment_methods');
            }
            $this->db->insert(db_prefix() . 'pos_payment_methods', $data);
            set_alert('success', _l('added_successfully', _l('pos_payment_method')));
        }

        redirect(admin_url('pos_system/payment_methods'));
    }

    // ─── Sales Orders ─────────────────────────────────────────────────────────

    public function sales_orders()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products')) {
            $this->_pos_deny(); return;
        }

        $branch_id = pos_get_auth_branch();
        $status    = $this->input->get('status') ?: '';
        $search    = $this->input->get('search') ?: '';

        // Counts per status for summary cards
        $all_orders = $this->db
            ->select('id, so_number, date, customer_name, sales_person, expected_delivery, total_amount, status')
            ->from(db_prefix().'pos_sales_orders')
            ->order_by('date_created', 'DESC')->get()->result_array();

        $stats = ['draft'=>0,'confirmed'=>0,'processing'=>0,'partially_delivered'=>0,'delivered'=>0,'cancelled'=>0];
        $totals = ['total'=>0,'delivered'=>0];
        foreach ($all_orders as $o) {
            if (isset($stats[$o['status']])) $stats[$o['status']]++;
            $totals['total'] += (float)$o['total_amount'];
            if ($o['status'] === 'delivered') $totals['delivered'] += (float)$o['total_amount'];
        }

        // Filtered list
        $qb = $this->db
            ->select('id, so_number, date, customer_name, sales_person, expected_delivery, total_amount, status')
            ->from(db_prefix().'pos_sales_orders');
        if ($status) $qb->where('status', $status);
        if ($search) $qb->group_start()->like('so_number', $search)->or_like('customer_name', $search)->group_end();
        $orders = $qb->order_by('date_created', 'DESC')->get()->result_array();

        $this->load->view('pos_system/admin/sales_orders/index', [
            'title'         => _l('pos_sales_orders'),
            'orders'        => $orders,
            'stats'         => $stats,
            'totals'        => $totals,
            'current_status'=> $status,
            'current_search'=> $search,
            'total_count'   => count($all_orders),
        ]);
    }

    public function so_form($id = null)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products')) {
            $this->_pos_deny(); return;
        }

        $branch_id = pos_get_staff_branch();
        $order     = null;
        $items     = [];

        if ($id) {
            $order = $this->db->where('id', (int)$id)
                ->get(db_prefix().'pos_sales_orders')->row_array();
            if (!$order) { show_404(); return; }
            $items = $this->db->where('sales_order_id', (int)$id)
                ->order_by('sort_order')
                ->get(db_prefix().'pos_sales_order_items')->result_array();
        }

        // Perfex CRM invoices for linking
        $invoices = $this->db
            ->select("i.id, i.number, i.prefix, i.clientid,
                      COALESCE(c.company, i.deleted_customer_name, '') AS customer_name")
            ->from(db_prefix().'invoices i')
            ->join(db_prefix().'clients c', 'c.userid = i.clientid', 'left')
            ->where('i.number !=', -1)
            ->order_by('i.id', 'DESC')
            ->get()->result_array();
        foreach ($invoices as &$inv) {
            $inv['display_number'] = $inv['prefix'] . str_pad($inv['number'], 5, '0', STR_PAD_LEFT);
        }
        unset($inv);

        // Clients
        $clients = $this->db
            ->select("userid AS id, company AS name,
                      REPLACE(REPLACE(REPLACE(billing_street,'<br />',', '),'<br/>',', '),'<br>',', ') AS billing_street,
                      billing_city, billing_state, billing_zip")
            ->order_by('company', 'ASC')
            ->get(db_prefix().'clients')->result_array();
        foreach ($clients as &$cl) {
            $parts = array_filter([
                trim(strip_tags($cl['billing_street'] ?? '')),
                trim($cl['billing_city'] ?? ''),
                trim($cl['billing_state'] ?? ''),
                trim($cl['billing_zip'] ?? ''),
            ]);
            $cl['full_address'] = implode(', ', $parts);
        }
        unset($cl);

        // Projects
        $projects = $this->db->select('id, name')->order_by('name', 'ASC')
            ->get(db_prefix().'projects')->result_array();

        // Staff
        $staff_list = $this->db->select('staffid AS id, CONCAT(firstname," ",lastname) AS name')
            ->where('active', 1)
            ->order_by('firstname', 'ASC')
            ->get(db_prefix().'staff')->result_array();

        // POS products for line items
        $products = $this->db->select('id, name, sku, selling_price, tax_rate_id')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get(db_prefix().'pos_products')->result_array();

        $current_staff_id   = (int) get_staff_user_id();
        $current_staff_name = '';
        foreach ($staff_list as $s) {
            if ((int)$s['id'] === $current_staff_id) {
                $current_staff_name = $s['name']; break;
            }
        }

        $data = [
            'title'               => $order ? 'Edit Sales Order' : 'New Sales Order',
            'order'               => $order,
            'items'               => $items,
            'invoices'            => $invoices,
            'clients'             => $clients,
            'projects'            => $projects,
            'staff_list'          => $staff_list,
            'products'            => $products,
            'branch_id'           => $branch_id,
            'current_staff_id'    => $current_staff_id,
            'current_staff_name'  => $current_staff_name,
        ];
        $this->load->view('pos_system/admin/sales_orders/form', $data);
    }

    public function so_save()
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products', 'create') && !pos_perm('pos_products', 'edit')) {
            echo json_encode(['success'=>false,'error'=>'Forbidden']); return;
        }
        header('Content-Type: application/json');

        $id          = (int)$this->input->post('id');
        $branch_id   = pos_get_staff_branch() ?: (int)$this->input->post('branch_id');
        $client_id   = (int)$this->input->post('client_id') ?: null;
        $project_id  = (int)$this->input->post('project_id') ?: null;
        $inv_id      = (int)$this->input->post('crm_invoice_id') ?: null;

        // Build SO number for new records
        if (!$id) {
            $branch = $this->db->where('id', $branch_id)->get(db_prefix().'pos_branches')->row_array();
            $code   = strtoupper($branch['code'] ?? 'SO');
            $today  = date('Ymd');
            $last   = $this->db->select_max('id')->get(db_prefix().'pos_sales_orders')->row_array();
            $seq    = str_pad((($last['id'] ?? 0) + 1), 5, '0', STR_PAD_LEFT);
            $so_number = "SO-{$code}-{$today}-{$seq}";
        }

        $header = [
            'branch_id'       => $branch_id,
            'client_id'       => $client_id,
            'customer_name'   => $this->input->post('customer_name'),
            'address'         => $this->input->post('address'),
            'project_id'      => $project_id,
            'project_name'    => $this->input->post('project_name'),
            'sales_person_id' => (int)$this->input->post('sales_person_id') ?: null,
            'sales_person'    => $this->input->post('sales_person'),
            'date'            => $this->input->post('date') ?: date('Y-m-d'),
            'expected_delivery'=> $this->input->post('expected_delivery') ?: null,
            'notes'           => $this->input->post('notes'),
            'subtotal'        => (float)$this->input->post('subtotal'),
            'discount_amount' => (float)$this->input->post('discount_amount'),
            'tax_amount'      => (float)$this->input->post('tax_amount'),
            'shipping_fee'    => (float)$this->input->post('shipping_fee'),
            'total_amount'    => (float)$this->input->post('total_amount'),
            'crm_invoice_id'  => $inv_id,
            'status'          => 'draft',
        ];

        if ($id) {
            unset($header['status']);
            $this->db->where('id', $id)->update(db_prefix().'pos_sales_orders', $header);
        } else {
            $header['so_number'] = $so_number;
            $header['created_by'] = get_staff_user_id();
            $this->db->insert(db_prefix().'pos_sales_orders', $header);
            $id = $this->db->insert_id();
        }

        // Re-insert line items
        $this->db->where('sales_order_id', $id)->delete(db_prefix().'pos_sales_order_items');
        $lines = $this->input->post('lines');
        if (is_array($lines)) {
            $sort = 0;
            foreach ($lines as $line) {
                $qty   = (float)($line['qty'] ?? 1);
                $price = (float)($line['unit_price'] ?? 0);
                $dpct  = (float)($line['discount_pct'] ?? 0);
                $damt  = round($qty * $price * $dpct / 100, 4);
                $sub   = round($qty * $price - $damt, 4);
                $tpct  = (float)($line['tax_rate_pct'] ?? 0);
                $tamt  = round($sub * $tpct / 100, 4);
                $total = round($sub + $tamt, 4);
                $this->db->insert(db_prefix().'pos_sales_order_items', [
                    'sales_order_id'  => $id,
                    'product_id'      => (int)($line['product_id'] ?? 0) ?: null,
                    'branch_id'       => $branch_id,
                    'product_name'    => $line['product_name'] ?? '',
                    'qty_ordered'     => $qty,
                    'qty_delivered'   => 0,
                    'unit_price'      => $price,
                    'discount_pct'    => $dpct,
                    'discount_amount' => $damt,
                    'tax_rate_id'     => (int)($line['tax_rate_id'] ?? 0) ?: null,
                    'tax_rate_pct'    => $tpct,
                    'tax_amount'      => $tamt,
                    'subtotal'        => $sub,
                    'line_total'      => $total,
                    'sort_order'      => $sort++,
                ]);
            }
        }

        $so = $this->db->where('id', $id)->get(db_prefix().'pos_sales_orders')->row_array();
        echo json_encode([
            'success'   => true,
            'id'        => $id,
            'so_number' => $so['so_number'],
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    public function so_action($id = null)
    {
        if (!pos_can_access('manager') && !pos_perm('pos_products', 'edit')) {
            $this->_pos_deny(); return;
        }

        $id     = (int)($id ?: $this->input->post('id'));
        $action = $this->input->post('action') ?: $this->input->get('action');

        $so = $this->db->where('id', $id)->get(db_prefix().'pos_sales_orders')->row_array();
        if (!$so) { show_404(); return; }

        switch ($action) {
            case 'confirm':
                if ($so['status'] === 'draft') {
                    $this->db->where('id', $id)->update(db_prefix().'pos_sales_orders', ['status'=>'confirmed']);
                    set_alert('success', 'Sales Order confirmed.');
                }
                break;
            case 'cancel':
                if (in_array($so['status'], ['draft','confirmed'])) {
                    $this->db->where('id', $id)->update(db_prefix().'pos_sales_orders', ['status'=>'cancelled']);
                    set_alert('success', 'Sales Order cancelled.');
                }
                break;
            case 'delete':
                if ($so['status'] === 'draft') {
                    $this->db->where('sales_order_id', $id)->delete(db_prefix().'pos_sales_order_items');
                    $this->db->where('id', $id)->delete(db_prefix().'pos_sales_orders');
                    set_alert('success', 'Sales Order deleted.');
                    redirect(admin_url('pos_system/sales_orders')); return;
                }
                set_alert('warning', 'Only draft orders can be deleted.');
                break;
        }

        redirect(admin_url('pos_system/sales_orders'));
    }

    public function so_panel($id = null)
    {
        $id = (int)$id;
        if (!$id) { show_404(); return; }

        $so = $this->db->where('id', $id)->get(db_prefix().'pos_sales_orders')->row_array();
        if (!$so) { show_404(); return; }

        $items = $this->db->where('sales_order_id', $id)->order_by('sort_order')
            ->get(db_prefix().'pos_sales_order_items')->result_array();

        $deliveries = $this->db
            ->select('id, delivery_number, delivery_date, status, total_amount')
            ->where('sales_order_id', $id)->order_by('id', 'DESC')
            ->get(db_prefix().'pos_inv_deliveries')->result_array();

        $this->load->view('pos_system/admin/sales_orders/panel', [
            'so'         => $so,
            'items'      => $items,
            'deliveries' => $deliveries,
        ]);
    }

    public function delivery_panel($id = null)
    {
        $id = (int)$id;
        if (!$id) { show_404(); return; }

        $d = $this->db
            ->select('d.*, b.name AS branch_name, b.code AS branch_code')
            ->from(db_prefix().'pos_inv_deliveries d')
            ->join(db_prefix().'pos_branches b', 'b.id = d.branch_id', 'left')
            ->where('d.id', $id)->get()->row_array();
        if (!$d) { show_404(); return; }

        $items = $this->db->where('delivery_id', $id)->order_by('sort_order')
            ->get(db_prefix().'pos_inv_delivery_items')->result_array();

        $so = null;
        if (!empty($d['sales_order_id'])) {
            $so = $this->db->select('id, so_number, status')
                ->where('id', (int)$d['sales_order_id'])
                ->get(db_prefix().'pos_sales_orders')->row_array();
        }

        $this->load->view('pos_system/admin/inventory/delivery_panel', [
            'delivery' => $d,
            'items'    => $items,
            'so'       => $so,
        ]);
    }

    public function delivery_pdf($id = null)
    {
        $id = (int)$id;
        if (!$id) { show_404(); return; }
        if (!pos_can_access('manager') && !pos_perm('pos_inventory')) { $this->_pos_deny(); return; }

        $this->_ensure_delivery_schema();
        $db = $this->db;

        $d = $db->select('d.*, b.name AS branch_name, CONCAT(sp.firstname," ",sp.lastname) AS dispatched_by_name')
                ->from(db_prefix().'pos_inv_deliveries d')
                ->join(db_prefix().'pos_branches b', 'b.id = d.branch_id', 'left')
                ->join(db_prefix().'staff sp', 'sp.staffid = d.dispatched_by', 'left')
                ->where('d.id', $id)
                ->get()->row_array();
        if (!$d) { show_404(); return; }

        $items = $db->select('i.*, p.name AS product_name, p.sku, p.barcode')
                    ->from(db_prefix().'pos_inv_delivery_items i')
                    ->join(db_prefix().'pos_products p', 'p.id = i.product_id', 'left')
                    ->where('i.delivery_id', $id)
                    ->get()->result_array();

        $so = null;
        if (!empty($d['sales_order_id'])) {
            $so = $db->where('id', (int)$d['sales_order_id'])->get(db_prefix().'pos_sales_orders')->row_array();
        }

        // Company info from Perfex settings
        $company = get_option('companyname') ?: 'Company Name';
        $logo    = get_option('company_logo');
        $logo_url = $logo ? base_url('uploads/company/' . $logo) : '';

        $this->load->view('pos_system/admin/inventory/delivery_pdf', [
            'd'        => $d,
            'items'    => $items,
            'so'       => $so,
            'company'  => $company,
            'logo_url' => $logo_url,
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function _get_perfex_gateways(): array
    {
        // Gather active Perfex payment gateways from module settings
        $gateways = [];

        $gateway_modules = ['paypalstandard', 'stripe', 'mollie', 'razorpay', 'gocardless', '2checkout', 'braintree'];

        foreach ($gateway_modules as $slug) {
            $active = get_option($slug . '_active');
            if ($active == '1') {
                $gateways[] = [
                    'slug' => $slug,
                    'name' => ucwords(str_replace(['paypalstandard', '_'], ['PayPal', ' '], $slug)),
                ];
            }
        }

        // Always include offline/bank transfer
        $gateways[] = ['slug' => 'bank_transfer', 'name' => 'Bank Transfer'];
        $gateways[] = ['slug' => 'cash',           'name' => 'Cash'];

        return $gateways;
    }

    private function _get_all_payment_methods(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_payment_methods')) {
            return [];
        }
        return $this->db->order_by('sort_order', 'ASC')->get(db_prefix() . 'pos_payment_methods')->result_array();
    }
}
