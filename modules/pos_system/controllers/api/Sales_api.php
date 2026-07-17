<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * POST /pos_system/api/sales              → create sale
 * GET  /pos_system/api/sales              → list sales for branch
 * GET  /pos_system/api/sales/{id}         → single sale
 * POST /pos_system/api/sales/{id}/refund  → refund sale
 * POST /pos_system/api/sales/sync         → offline batch sync
 */
class Sales_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_sales_model',    'pos_sales_model');
        $this->load->model('pos_system/Pos_sessions_model', 'pos_sessions_model');
    }

    public function index()
    {
        $this->require_auth();

        $pg      = $this->pagination();
        $filters = [
            'status'    => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to'   => $this->input->get('date_to'),
            'search'    => $this->input->get('search'),
        ];

        $sales = $this->pos_sales_model->get_by_branch(
            $this->auth_branch_id,
            $filters,
            $pg['limit'],
            $pg['offset']
        );

        $this->json(['data' => $sales, 'page' => $pg['page'], 'per_page' => $pg['limit']]);
    }

    public function show(int $id)
    {
        $this->require_auth();

        $sale = $this->pos_sales_model->get($id);
        if (!$sale || (int) $sale['branch_id'] !== $this->auth_branch_id) {
            $this->json(['error' => 'Sale not found'], 404);
        }

        $this->json($sale);
    }

    public function create()
    {
        $this->require_auth();

        $body    = $this->json_body();
        $missing = $this->validate_required($body, ['items', 'payments']);
        if ($missing) {
            $this->json(['error' => 'Missing: ' . implode(', ', $missing)], 422);
        }

        if (empty($body['items'])) {
            $this->json(['error' => 'Sale must have at least one item'], 422);
        }

        // Enforce session requirement if configured
        $session_required = pos_get_setting('session_required', $this->auth_branch_id) === '1';
        if ($session_required && empty($body['session_id'])) {
            $session = $this->pos_sessions_model->get_open_session(
                $this->auth_staff_id,
                $this->auth_branch_id
            );
            if (!$session) {
                $this->json(['error' => 'No open POS session. Please open a session first.'], 409);
            }
            $body['session_id'] = $session['id'];
        }

        // Fall back to the branch/global default (walk-in) customer when none selected
        if (empty($body['customer_id'])) {
            $default_cust = pos_get_setting('pos_default_customer_id', $this->auth_branch_id);
            if ($default_cust) {
                $body['customer_id'] = (int) $default_cust;
            }
        }

        $order_type = in_array($body['order_type'] ?? '', ['dine-in', 'takeaway', 'bar'])
            ? $body['order_type'] : 'dine-in';
        $table_id   = !empty($body['table_id']) ? (int) $body['table_id'] : null;

        $sale_data = [
            'branch_id'       => $this->auth_branch_id,
            'staff_id'        => $this->auth_staff_id,
            'session_id'      => $body['session_id'] ?? null,
            'customer_id'     => $body['customer_id'] ?? null,
            'order_type'      => $order_type,
            'table_id'        => $table_id,
            'covers'          => max(1, (int) ($body['covers'] ?? 1)),
            'discount_id'     => $body['discount_id'] ?? null,
            'discount_type'   => $body['discount_type'] ?? null,
            'discount_value'  => $body['discount_value'] ?? 0,
            'discount_amount' => $body['discount_amount'] ?? 0,
            'currency'        => $body['currency'] ?? pos_get_setting('default_currency', $this->auth_branch_id) ?? 'KES',
            'notes'           => $body['notes'] ?? '',
            'sale_uid'        => $body['sale_uid'] ?? pos_uuid(),
            'sync_status'     => $body['is_offline'] ?? false ? 'synced' : 'synced',
        ];

        $result = $this->pos_sales_model->create_sale($sale_data, $body['items'], $body['payments']);

        if (!$result['success']) {
            $this->json(['error' => $result['message'] ?? 'Sale failed'], 500);
        }

        // Auto-KOT: fire a kitchen ticket when restaurant mode + table selected
        if ($table_id && pos_get_setting('pos_restaurant_mode') == '1') {
            try {
                $this->_create_auto_kot($table_id, $body['items'], $sale_data);
            } catch (Exception $e) {
                log_message('error', '[POS] Auto-KOT failed for sale #'
                    . ($result['sale']['receipt_number'] ?? '?') . ': ' . $e->getMessage());
            }
        }

        // Auto-generate Perfex invoice (non-blocking)
        if (pos_get_setting('pos_auto_invoice', $this->auth_branch_id) !== '0') {
            try {
                $this->load->model('pos_system/Pos_invoice_model', 'pos_invoice_model');
                $this->pos_invoice_model->create_from_sale($result['sale'], true);
            } catch (Exception $e) {
                log_message('error', '[POS] Auto-invoice failed for sale #'
                    . ($result['sale']['receipt_number'] ?? '?') . ': ' . $e->getMessage());
            }
        }

        $this->json($result['sale'], 201);
    }

    public function refund(int $id)
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $sale = $this->pos_sales_model->get($id);
        if (!$sale || (int) $sale['branch_id'] !== $this->auth_branch_id) {
            $this->json(['error' => 'Sale not found'], 404);
        }

        if (!in_array($sale['status'], ['completed', 'partial_refund'])) {
            $this->json(['error' => 'Sale cannot be refunded in its current status'], 409);
        }

        $body   = $this->json_body();
        $result = $this->pos_sales_model->refund(
            $id,
            $body['items'] ?? [],
            $body['reason'] ?? ''
        );

        if (!$result['success']) {
            $this->json(['error' => $result['message']], 500);
        }

        $this->json($result['sale']);
    }

    /**
     * Create a KOT in the DB immediately after a sale is committed.
     */
    private function _create_auto_kot(int $table_id, array $items, array $sale_data): void
    {
        $branch_id = $this->auth_branch_id;
        $staff_id  = $this->auth_staff_id;

        $table = $this->db
            ->where('id', $table_id)
            ->where('branch_id', $branch_id)
            ->get(db_prefix() . 'pos_restaurant_tables')
            ->row_array();

        if (!$table) return;

        // Default area
        $area_row = $this->db
            ->select('id')->where('branch_id', $branch_id)->where('is_active', 1)
            ->order_by('id', 'ASC')->limit(1)
            ->get(db_prefix() . 'pos_restaurant_areas')->row_array();
        $area_id = (int) ($area_row['id'] ?? 0);

        // KOT number
        $today_prefix = 'KOT-' . $branch_id . '-' . date('Ymd') . '-';
        $last = $this->db
            ->select('kot_number')->like('kot_number', $today_prefix, 'after')
            ->order_by('id', 'DESC')->limit(1)
            ->get(db_prefix() . 'pos_restaurant_kots')->row_array();
        $seq = 1;
        if ($last) { $parts = explode('-', $last['kot_number']); $seq = (int) end($parts) + 1; }
        $kot_number = $today_prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

        // Waiter
        $staff = $this->db->select('firstname, lastname')
            ->where('staffid', $staff_id)->get(db_prefix() . 'staff')->row_array();
        $waiter_name = $staff ? trim($staff['firstname'] . ' ' . $staff['lastname']) : '';

        // Customer name
        $customer_id   = (int) ($sale_data['customer_id'] ?? 0) ?: null;
        $customer_name = '';
        if ($customer_id) {
            $cust = $this->db->select('name')->where('id', $customer_id)
                ->get(db_prefix() . 'pos_customers')->row_array();
            $customer_name = $cust ? $cust['name'] : '';
        }

        $order_type = in_array($sale_data['order_type'] ?? '', ['dine-in', 'takeaway', 'bar'])
            ? $sale_data['order_type'] : 'dine-in';

        $this->db->insert(db_prefix() . 'pos_restaurant_kots', [
            'kot_number'    => $kot_number,
            'branch_id'     => $branch_id,
            'area_id'       => $area_id,
            'table_id'      => $table_id,
            'table_number'  => $table['table_number'],
            'covers'        => max(1, (int) ($sale_data['covers'] ?? 1)),
            'customer_id'   => $customer_id,
            'customer_name' => substr(trim($customer_name), 0, 200),
            'order_type'    => $order_type,
            'waiter_id'     => $staff_id,
            'waiter_name'   => $waiter_name,
            'status'        => 'pending',
            'notes'         => substr(trim((string) ($sale_data['notes'] ?? '')), 0, 500),
            'date_created'  => date('Y-m-d H:i:s'),
        ]);
        $kot_id = (int) $this->db->insert_id();

        foreach ($items as $item) {
            $this->db->insert(db_prefix() . 'pos_restaurant_kot_items', [
                'kot_id'       => $kot_id,
                'product_id'   => (int) ($item['product_id'] ?? 0),
                'product_name' => substr(trim((string) ($item['product_name'] ?? '')), 0, 255),
                'quantity'     => max(1, (float) ($item['quantity'] ?? 1)),
                'notes'        => substr(trim((string) ($item['notes'] ?? '')), 0, 255),
            ]);
        }

        // Mark table occupied
        $this->db->where('id', $table_id)
                 ->update(db_prefix() . 'pos_restaurant_tables', ['status' => 'occupied']);
    }

    /**
     * Batch sync offline sales from the SPA.
     * Body: { sales: [ { sale, items, payments }, ... ] }
     */
    public function sync()
    {
        $this->require_auth();

        $body = $this->json_body();
        if (empty($body['sales']) || !is_array($body['sales'])) {
            $this->json(['error' => 'No sales provided'], 422);
        }

        $results = $this->pos_sales_model->sync_offline_sales(
            $body['sales'],
            $this->auth_branch_id,
            $this->auth_staff_id
        );

        $this->json($results);
    }
}
