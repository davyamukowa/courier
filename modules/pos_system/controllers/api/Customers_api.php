<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET    /api/customers                  â†’ list (paginated)
 * GET    /api/customers/search?q=...     â†’ quick search for POS autocomplete
 * GET    /api/customers/{id}             â†’ single + purchase history
 * POST   /api/customers                  â†’ create
 * PUT    /api/customers/{id}             â†’ update
 * DELETE /api/customers/{id}             â†’ delete (supervisor+)
 * GET    /api/customers/{id}/history     â†’ purchase history
 * POST   /api/customers/{id}/redeem      â†’ redeem loyalty points
 */
class Customers_api extends Pos_api
{
    protected int $cache_ttl = 0; // No caching for customer data

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_customers_model', 'pos_customers_model');
    }

    public function index()
    {
        $this->require_auth();

        $pg      = $this->pagination(50);
        $filters = $this->read_filters(['search']) + ['branch_id' => $this->auth_branch_id];

        $data  = $this->pos_customers_model->get_all($filters, $pg['limit'], $pg['offset']);
        $total = $this->pos_customers_model->count($filters);

        $this->paginated($data, $total, $pg['page'], $pg['per_page']);
    }

    public function search()
    {
        $this->require_auth();

        $q = trim($this->input->get('q') ?? '');
        if (strlen($q) < 2) {
            $this->ok([]);
            return;
        }

        $results = $this->pos_customers_model->search($q, $this->auth_branch_id, 15);
        $this->ok($results);
    }

    public function show(int $id)
    {
        $this->require_auth();

        $customer = $this->pos_customers_model->get($id);
        if (!$customer) {
            $this->error('Customer not found', 404, self::ERR_NOT_FOUND);
        }

        $customer['recent_purchases'] = $this->pos_customers_model->get_purchase_history($id, 5);
        $this->ok($customer);
    }

    public function create()
    {
        $this->require_auth();

        $data = $this->json_body();
        $this->validate($data, [
            'name'  => 'required|max:200',
            'phone' => 'required|max:30',
            'email' => 'email',
        ]);

        // Prevent duplicate phone
        $existing = $this->pos_customers_model->get_by_phone($data['phone']);
        if ($existing) {
            $this->error('A customer with this phone number already exists', 409, self::ERR_CONFLICT);
        }

        $data['branch_id']  = $data['branch_id'] ?? $this->auth_branch_id;
        $id                 = $this->pos_customers_model->create($data);
        $this->log_activity('customer.create', ['customer_id' => $id]);

        $this->ok($this->pos_customers_model->get($id), 201);
    }

    public function update(int $id)
    {
        $this->require_auth();

        $customer = $this->pos_customers_model->get($id);
        if (!$customer) {
            $this->error('Customer not found', 404, self::ERR_NOT_FOUND);
        }

        $data = $this->json_body();
        $this->pos_customers_model->update($id, $data);
        $this->log_activity('customer.update', ['customer_id' => $id]);

        $this->ok($this->pos_customers_model->get($id));
    }

    public function delete(int $id)
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $customer = $this->pos_customers_model->get($id);
        if (!$customer) {
            $this->error('Customer not found', 404, self::ERR_NOT_FOUND);
        }

        $this->pos_customers_model->delete($id);
        $this->log_activity('customer.delete', ['customer_id' => $id]);
        $this->ok(['message' => 'Customer deleted']);
    }

    public function history(int $id)
    {
        $this->require_auth();

        $customer = $this->pos_customers_model->get($id);
        if (!$customer) {
            $this->error('Customer not found', 404, self::ERR_NOT_FOUND);
        }

        $pg      = $this->pagination(20);
        $history = $this->pos_customers_model->get_purchase_history($id, $pg['limit']);
        $this->ok($history);
    }

    public function redeem(int $id)
    {
        $this->require_auth();

        $customer = $this->pos_customers_model->get($id);
        if (!$customer) {
            $this->error('Customer not found', 404, self::ERR_NOT_FOUND);
        }

        $body   = $this->json_body();
        $this->validate($body, ['points' => 'required|integer|positive']);

        $rate   = (float) (pos_get_setting('loyalty_rate', $this->auth_branch_id) ?? 1.0);
        $result = $this->pos_customers_model->redeem_points($id, (int) $body['points'], $rate);

        if (!$result['success']) {
            $this->error($result['message'], 409, self::ERR_CONFLICT);
        }

        $this->log_activity('customer.redeem_points', ['customer_id' => $id, 'points' => $body['points']]);
        $this->ok($result);
    }
}
