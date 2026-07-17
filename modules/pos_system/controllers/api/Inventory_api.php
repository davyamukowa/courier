<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /api/inventory                    â†’ stock levels (paginated)
 * GET  /api/inventory/low-stock          â†’ low stock alerts
 * GET  /api/inventory/expiring           â†’ expiring within 30 days
 * GET  /api/inventory/movements          â†’ movement history
 * GET  /api/inventory/valuation          â†’ stock valuation report
 * POST /api/inventory/adjust             â†’ manual adjustment (supervisor+)
 * POST /api/inventory/transfer           â†’ inter-branch transfer (manager+)
 * POST /api/inventory/receive            â†’ receive stock batch (supervisor+)
 */
class Inventory_api extends Pos_api
{
    protected int $cache_ttl = 60; // 60-second cache for read endpoints

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_inventory_model', 'pos_inventory_model');
        $this->load->model('pos_system/Pos_branches_model', 'pos_branches_model');
        $this->load->library('pos_system/Pos_integrations', 'pos_integrations');
    }

    public function index()
    {
        $this->require_auth();

        // Allow admin override of branch
        $branch_id = (int) ($this->input->get('branch_id') ?: $this->auth_branch_id);

        $pg      = $this->pagination(100);
        $filters = $this->read_filters(['search', 'category_id', 'stock', 'source']);

        $cache_key = "inventory_list_{$branch_id}_" . md5(http_build_query($filters));
        $cached    = $this->cache_get($cache_key);
        if ($cached) {
            $this->ok($cached['data'], 200, $cached['meta']);
        }

        $total = $this->pos_integrations->count_sellable_items($branch_id, $filters);
        $items = $this->pos_integrations->get_sellable_items($branch_id, $filters, $pg['limit'], $pg['offset']);

        // Add formatted price for display
        $currency = pos_get_setting('pos_currency', $branch_id) ?: 'KES';
        foreach ($items as &$item) {
            $item['selling_price_fmt'] = pos_format_currency($item['final_price'] ?? $item['selling_price'], $currency);
        }
        unset($item);

        $this->cache_set($cache_key, ['data' => $items, 'meta' => ['total' => $total]]);
        $this->paginated($items, $total, $pg['page'], $pg['per_page']);
    }

    /**
     * POST /api/inventory/sync_warehouse â€” import sellable items from warehouse into pos_products.
     */
    public function sync_warehouse()
    {
        $this->require_auth();
        $this->require_role('manager');

        $synced = $this->pos_integrations->sync_warehouse_items_to_pos();
        $this->cache_bust('inventory');
        $this->log_activity('inventory.sync_warehouse', ['synced' => $synced]);
        $this->ok(['synced' => $synced, 'message' => $synced . ' items synced from warehouse']);
    }

    public function low_stock()
    {
        $this->require_auth();

        $threshold = (int) ($this->input->get('threshold') ?? 0);
        $data      = $this->pos_inventory_model->get_low_stock($this->auth_branch_id, $threshold);
        $this->ok($data);
    }

    public function expiring()
    {
        $this->require_auth();

        $days = (int) ($this->input->get('days') ?? 30);
        $data = $this->pos_inventory_model->get_expiring_soon($this->auth_branch_id, $days);
        $this->ok($data);
    }

    public function movements()
    {
        $this->require_auth();

        $pg      = $this->pagination(50);
        $filters = $this->read_filters(['product_id', 'type', 'date_from', 'date_to']);

        $data = $this->pos_inventory_model->get_movements(
            $this->auth_branch_id,
            $filters,
            $pg['limit'],
            $pg['offset']
        );

        $this->ok($data);
    }

    public function valuation()
    {
        $this->require_auth();
        $this->require_role('manager');

        $data = $this->pos_inventory_model->valuation_report($this->auth_branch_id);
        $this->ok($data);
    }

    public function adjust()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $body = $this->json_body();
        $this->validate($body, [
            'product_id' => 'required|integer',
            'quantity'   => 'required|positive',
        ]);

        // Translate type+quantity â†’ signed adjustment delta
        $qty  = (float) $body['quantity'];
        $type = $body['type'] ?? 'add';
        if ($type === 'remove') {
            $delta = -$qty;
        } elseif ($type === 'set') {
            // For 'set', pass 0 and let the model handle absolute set
            $delta = $qty; // model will overwrite; flag via context
        } else {
            $delta = $qty; // add
        }

        $result = $this->pos_inventory_model->adjust(
            (int)($body['branch_id'] ?? $this->auth_branch_id),
            (int) $body['product_id'],
            $delta,
            $body['reason'] ?? 'Manual adjustment',
            !empty($body['variation_id']) ? (int) $body['variation_id'] : null,
            (float) ($body['unit_cost'] ?? 0),
            $this->auth_staff_id
        );

        $this->cache_bust('inventory');
        $this->log_activity('inventory.adjust', $body);
        $this->ok($result, 200);
    }

    public function transfer()
    {
        $this->require_auth();
        $this->require_role('manager');

        $body = $this->json_body();
        $this->validate($body, [
            'to_branch_id' => 'required|integer',
            'product_id'   => 'required|integer',
            'quantity'     => 'required|positive',
        ]);

        $to_branch = $this->pos_branches_model->get((int) $body['to_branch_id']);
        if (!$to_branch) {
            $this->error('Destination branch not found', 404, self::ERR_NOT_FOUND);
        }

        $result = $this->pos_inventory_model->transfer(
            $this->auth_branch_id,
            (int) $body['to_branch_id'],
            (int) $body['product_id'],
            (float) $body['quantity'],
            !empty($body['variation_id']) ? (int) $body['variation_id'] : null,
            (float) ($body['unit_cost'] ?? 0),
            $this->auth_staff_id
        );

        if (!$result['success']) {
            $this->error($result['message'], 409, self::ERR_CONFLICT);
        }

        $this->cache_bust('inventory');
        $this->log_activity('inventory.transfer', $body);
        $this->ok($result);
    }

    public function receive()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $body = $this->json_body();
        $this->validate($body, [
            'product_id'   => 'required|integer',
            'quantity'     => 'required|positive',
            'cost_price'   => 'required|positive',
            'batch_number' => 'required',
        ]);

        $batch_id = $this->pos_inventory_model->receive_batch(
            $this->auth_branch_id,
            (int) $body['product_id'],
            (float) $body['quantity'],
            (float) $body['cost_price'],
            $body['batch_number'],
            $body['expiry_date'] ?? null,
            !empty($body['variation_id']) ? (int) $body['variation_id'] : null,
            $this->auth_staff_id
        );

        $this->cache_bust('inventory');
        $this->log_activity('inventory.receive', ['batch_id' => $batch_id]);
        $this->ok(['batch_id' => $batch_id, 'message' => 'Stock received'], 201);
    }
}
