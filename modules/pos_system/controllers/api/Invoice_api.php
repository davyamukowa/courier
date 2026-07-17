<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /api/invoices                  â†’ list invoices for branch
 * GET  /api/invoices/{id}             â†’ single invoice
 * GET  /api/invoices/sale/{sale_id}   â†’ invoice for a sale
 * POST /api/invoices/generate         â†’ generate invoice from sale
 * POST /api/invoices/{id}/cancel      â†’ cancel invoice
 * POST /api/invoices/{id}/credit-note â†’ create credit note (refund)
 * POST /api/invoices/{id}/push-perfex â†’ push to Perfex CRM
 */
class Invoice_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_invoice_model',  'pos_invoice_model');
        $this->load->model('pos_system/Pos_sales_model',    'pos_sales_model');
    }

    public function index()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $pg      = $this->pagination(50);
        $filters = $this->read_filters(['status', 'date_from', 'date_to', 'search']);

        $invoices = $this->pos_invoice_model->get_by_branch(
            $this->auth_branch_id, $filters, $pg['limit'], $pg['offset']
        );
        $total = $this->pos_invoice_model->count_by_branch($this->auth_branch_id, $filters);

        $this->paginated($invoices, $total, $pg['page'], $pg['per_page']);
    }

    public function show(int $id)
    {
        $this->require_auth();

        $invoice = $this->pos_invoice_model->get($id);
        if (!$invoice || (int)$invoice['branch_id'] !== $this->auth_branch_id) {
            $this->error('Invoice not found', 404, self::ERR_NOT_FOUND);
        }

        $this->ok($invoice);
    }

    public function by_sale(int $sale_id)
    {
        $this->require_auth();

        $invoice = $this->pos_invoice_model->get_by_sale($sale_id);
        if (!$invoice) {
            $this->error('No invoice found for this sale', 404, self::ERR_NOT_FOUND);
        }

        $this->ok($invoice);
    }

    public function generate()
    {
        $this->require_auth();

        $body = $this->json_body();
        $this->validate($body, ['sale_id' => 'required|integer']);

        $sale = $this->pos_sales_model->get((int)$body['sale_id']);
        if (!$sale || (int)$sale['branch_id'] !== $this->auth_branch_id) {
            $this->error('Sale not found', 404, self::ERR_NOT_FOUND);
        }

        $push_to_perfex = !empty($body['push_to_perfex']);
        $result = $this->pos_invoice_model->create_from_sale($sale, $push_to_perfex);

        if (!$result['success']) {
            $this->error($result['message'] ?? 'Invoice creation failed', 500, self::ERR_SERVER);
        }

        $this->log_activity('invoice.generate', ['sale_id' => $body['sale_id'], 'invoice_id' => $result['invoice']['id'] ?? 0]);
        $this->ok($result['invoice'], $result['created'] ? 201 : 200);
    }

    public function cancel(int $id)
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $invoice = $this->pos_invoice_model->get($id);
        if (!$invoice || (int)$invoice['branch_id'] !== $this->auth_branch_id) {
            $this->error('Invoice not found', 404, self::ERR_NOT_FOUND);
        }

        if (in_array($invoice['status'], ['cancelled', 'credit_note'])) {
            $this->error('Invoice is already cancelled', 409, self::ERR_CONFLICT);
        }

        $body   = $this->json_body();
        $reason = $body['reason'] ?? '';

        $this->pos_invoice_model->cancel($id, $reason);
        $this->log_activity('invoice.cancel', ['invoice_id' => $id]);
        $this->ok(['message' => 'Invoice cancelled', 'invoice' => $this->pos_invoice_model->get($id)]);
    }

    public function credit_note(int $id)
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $invoice = $this->pos_invoice_model->get($id);
        if (!$invoice || (int)$invoice['branch_id'] !== $this->auth_branch_id) {
            $this->error('Invoice not found', 404, self::ERR_NOT_FOUND);
        }

        $body   = $this->json_body();
        $result = $this->pos_invoice_model->create_credit_note($id, $body);

        if (!$result['success']) {
            $this->error($result['message'] ?? 'Credit note failed', 409, self::ERR_CONFLICT);
        }

        $this->log_activity('invoice.credit_note', ['original_id' => $id]);
        $this->ok($result['invoice'], 201);
    }

    public function push_perfex(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $invoice = $this->pos_invoice_model->get($id);
        if (!$invoice) {
            $this->error('Invoice not found', 404, self::ERR_NOT_FOUND);
        }

        if ($invoice['perfex_invoice_id']) {
            $this->error('Already pushed to Perfex CRM (ID: ' . $invoice['perfex_invoice_id'] . ')', 409, self::ERR_CONFLICT);
        }

        $sale   = $this->pos_sales_model->get((int)$invoice['sale_id']);
        $result = $this->pos_invoice_model->create_from_sale($sale, true);

        $this->log_activity('invoice.push_perfex', ['invoice_id' => $id]);
        $this->ok($result['invoice'] ?? $invoice);
    }
}
