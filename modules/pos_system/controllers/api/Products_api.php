<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET    /pos_system/api/products          â†’ paginated product list (admin)
 * GET    /pos_system/api/products/pos      â†’ POS-optimised list with stock
 * GET    /pos_system/api/products/{id}     â†’ single product
 * GET    /pos_system/api/products/barcode/{code} â†’ lookup by barcode
 * POST   /pos_system/api/products          â†’ create (manager+)
 * PUT    /pos_system/api/products/{id}     â†’ update (manager+)
 * DELETE /pos_system/api/products/{id}     â†’ soft-delete (manager+)
 * GET    /pos_system/api/products/categories â†’ category list
 */
class Products_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_products_model', 'pos_products_model');
        $this->load->library('pos_system/Pos_integrations',  'pos_integrations');
    }

    public function index()
    {
        $this->require_auth();
        $pg = $this->pagination();
        $filters = [
            'search'    => $this->input->get('search'),
            'is_active' => $this->input->get('is_active') !== null ? (int) $this->input->get('is_active') : 1,
        ];
        $products = $this->pos_products_model->get_all($filters, $pg['limit'], $pg['offset']);
        $total    = $this->pos_products_model->count($filters);

        $this->json(['data' => $products, 'total' => $total, 'page' => $pg['page'], 'per_page' => $pg['limit']]);
    }

    public function pos()
    {
        $this->require_auth();
        $filters = [
            'search'      => $this->input->get('search') ?: null,
            'category_id' => $this->input->get('category_id') ? (int)$this->input->get('category_id') : null,
        ];
        // Read directly from our POS inventory (tblpos_products + tblpos_inventory)
        $products = $this->pos_products_model->get_for_pos($this->auth_branch_id, $filters);
        $this->json(['data' => $products]);
    }

    public function show(int $id)
    {
        $this->require_auth();
        $product = $this->pos_products_model->get($id);
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
        }
        $this->json($product);
    }

    public function barcode(string $code)
    {
        $this->require_auth();
        $result = $this->pos_products_model->find_by_barcode(urldecode($code));
        if (!$result) {
            $this->json(['error' => 'Barcode not found'], 404);
        }
        $this->json($result);
    }

    public function create()
    {
        $this->require_auth();
        $this->require_role('manager');

        $data    = $this->json_body();
        $missing = $this->validate_required($data, ['name', 'selling_price']);
        if ($missing) {
            $this->json(['error' => 'Missing: ' . implode(', ', $missing)], 422);
        }

        $id = $this->pos_products_model->create($data);
        $this->json($this->pos_products_model->get($id), 201);
    }

    public function update(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $product = $this->pos_products_model->get($id);
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
        }

        $this->pos_products_model->update($id, $this->json_body());
        $this->json($this->pos_products_model->get($id));
    }

    public function delete(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $product = $this->pos_products_model->get($id);
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
        }

        $this->pos_products_model->delete($id);
        $this->json(['message' => 'Product deactivated']);
    }

    public function categories()
    {
        $this->require_auth();
        $this->json(['data' => $this->pos_products_model->get_categories()]);
    }
}
