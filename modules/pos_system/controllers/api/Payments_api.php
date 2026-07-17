<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /pos_system/api/payments/methods          â†’ list active payment methods
 * POST /pos_system/api/payments/mobile-money     â†’ initiate mobile money push
 * GET  /pos_system/api/payments/mobile-money/{checkout_id}/status â†’ poll status
 * POST /pos_system/api/payments/callback/mpesa   â†’ M-Pesa STK callback (public)
 * POST /pos_system/api/payments/callback/airtel  â†’ Airtel callback (public)
 * POST /pos_system/api/payments/callback/mtn     â†’ MTN callback (public)
 * POST /pos_system/api/payments/callback/telebirr â†’ Telebirr callback (public)
 */
class Payments_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_payments_model', 'pos_payments_model');
        $this->load->library('pos_system/Mobile_money',     'mobile_money');
    }

    public function methods()
    {
        $this->require_auth();

        // Sync any new Perfex payment modes into pos_payment_methods (lazy, idempotent)
        $this->_sync_perfex_payment_modes();

        $methods = $this->pos_payments_model->get_methods($this->auth_branch_id);
        $this->json(['data' => $methods]);
    }

    /**
     * Sync Perfex native payment modes (tblpaymentmodes) into pos_payment_methods.
     * Creates a pos_payment_methods row for each active Perfex mode that doesn't
     * already have one. Idempotent â€” tracks via perfex_mode_id column.
     */
    private function _sync_perfex_payment_modes(): void
    {
        // Only run if the Perfex payment modes table exists
        if (!$this->db->table_exists(db_prefix() . 'paymentmodes')) {
            return;
        }

        // Add perfex_mode_id column if it doesn't exist yet (safe migration)
        if (!$this->db->field_exists('perfex_mode_id', db_prefix() . 'pos_payment_methods')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'pos_payment_methods`
                ADD COLUMN `perfex_mode_id` INT(11) DEFAULT NULL COMMENT \'FK to tblpaymentmodes\'');
        }

        $perfex_modes = $this->db
            ->where('active', 1)
            ->order_by('order', 'ASC')
            ->get(db_prefix() . 'paymentmodes')
            ->result_array();

        foreach ($perfex_modes as $mode) {
            $exists = (int) $this->db
                ->where('perfex_mode_id', (int) $mode['id'])
                ->count_all_results(db_prefix() . 'pos_payment_methods');

            if (!$exists) {
                $type = $this->_detect_payment_type($mode['name']);
                // Generate a safe unique code
                $code = preg_replace('/[^a-z0-9_]/', '_', strtolower($mode['name']));
                $code = 'pf_' . substr($code, 0, 40);

                $this->db->insert(db_prefix() . 'pos_payment_methods', [
                    'name'           => $mode['name'],
                    'code'           => $code,
                    'type'           => $type,
                    'provider'       => $this->_detect_provider($mode['name']),
                    'is_active'      => 1,
                    'sort_order'     => 50 + (int) ($mode['order'] ?? 0),
                    'perfex_mode_id' => (int) $mode['id'],
                ]);
            }
        }
    }

    /**
     * Infer payment type from a payment mode name.
     */
    private function _detect_payment_type(string $name): string
    {
        $l = strtolower($name);
        if (str_contains($l, 'mpesa') || str_contains($l, 'm-pesa')
            || str_contains($l, 'airtel') || str_contains($l, 'mtn')
            || str_contains($l, 'telebirr') || str_contains($l, 'mobile money')) {
            return 'mobile_money';
        }
        if (str_contains($l, 'card') || str_contains($l, 'visa')
            || str_contains($l, 'mastercard') || str_contains($l, 'stripe')
            || str_contains($l, 'pos machine')) {
            return 'card';
        }
        if (str_contains($l, 'bank') || str_contains($l, 'transfer')
            || str_contains($l, 'cheque') || str_contains($l, 'rtgs') || str_contains($l, 'eft')) {
            return 'bank_transfer';
        }
        if (str_contains($l, 'credit') || str_contains($l, 'account')) {
            return 'credit';
        }
        if (str_contains($l, 'cash')) {
            return 'cash';
        }
        return 'other';
    }

    /**
     * Infer mobile money provider from a payment mode name.
     */
    private function _detect_provider(string $name): ?string
    {
        $l = strtolower($name);
        if (str_contains($l, 'mpesa') || str_contains($l, 'm-pesa')) return 'mpesa';
        if (str_contains($l, 'airtel')) return 'airtel';
        if (str_contains($l, 'mtn')) return 'mtn';
        if (str_contains($l, 'telebirr')) return 'telebirr';
        return null;
    }

    /**
     * Initiate a mobile money STK/USSD push.
     * Body: { provider, phone, amount, reference, payment_method_id }
     */
    public function initiate_mobile_money()
    {
        $this->require_auth();

        $body    = $this->json_body();
        $missing = $this->validate_required($body, ['provider', 'phone', 'amount', 'reference', 'payment_method_id']);
        if ($missing) {
            $this->json(['error' => 'Missing: ' . implode(', ', $missing)], 422);
        }

        $result = $this->mobile_money->initiate(
            $body['provider'],
            $body['phone'],
            (float) $body['amount'],
            $body['reference'],
            $this->auth_branch_id
        );

        if (!$result['success']) {
            $this->json(['error' => $result['message'] ?? 'Mobile money initiation failed'], 502);
        }

        // Create a pending payment record
        $payment_id = $this->db->insert(db_prefix() . 'pos_payments', [
            'payment_uid'       => pos_uuid(),
            'sale_id'           => 0,              // Updated when sale is confirmed
            'branch_id'         => $this->auth_branch_id,
            'payment_method_id' => (int) $body['payment_method_id'],
            'amount'            => (float) $body['amount'],
            'currency'          => pos_get_setting('default_currency', $this->auth_branch_id) ?? 'KES',
            'status'            => 'pending',
            'reference'         => $result['checkout_request_id'],
        ]);

        $txn_id = $this->pos_payments_model->record_mobile_money_txn(
            $this->db->insert_id(),
            [
                'provider'             => $body['provider'],
                'phone_number'         => $body['phone'],
                'checkout_request_id'  => $result['checkout_request_id'],
                'merchant_request_id'  => $result['merchant_request_id'] ?? null,
                'amount'               => (float) $body['amount'],
                'currency'             => pos_get_setting('default_currency', $this->auth_branch_id) ?? 'KES',
                'status'               => 'initiated',
                'raw_request'          => json_encode($result['raw'] ?? []),
            ]
        );

        $this->json([
            'checkout_request_id' => $result['checkout_request_id'],
            'message'             => $result['message'],
            'txn_id'              => $txn_id,
        ], 202);
    }

    /**
     * Poll mobile money transaction status.
     */
    public function mobile_money_status(string $checkout_id)
    {
        $this->require_auth();

        $txn = $this->db
            ->where('checkout_request_id', $checkout_id)
            ->get(db_prefix() . 'pos_mobile_money_txns')
            ->row_array();

        if (!$txn) {
            $this->json(['error' => 'Transaction not found'], 404);
        }

        // For providers with direct query support, poll the API
        if (in_array($txn['provider'], ['mpesa', 'mtn']) && $txn['status'] === 'initiated') {
            $api_result = $this->mobile_money->query_status(
                $txn['provider'],
                $checkout_id,
                $this->auth_branch_id
            );

            if ($api_result['status'] !== 'pending') {
                $update = ['status' => $api_result['status']];
                if ($api_result['transaction_id']) {
                    $update['transaction_id'] = $api_result['transaction_id'];
                    $update['completed_at']   = date('Y-m-d H:i:s');
                }
                $this->pos_payments_model->update_mobile_money_txn($checkout_id, $update);
                $txn = array_merge($txn, $update);
            }
        }

        $this->json(['status' => $txn['status'], 'transaction_id' => $txn['transaction_id'] ?? null]);
    }

    // â”€â”€â”€ Callbacks (no auth â€” called by payment provider servers) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function callback_mpesa()
    {
        $raw      = file_get_contents('php://input');
        $callback = json_decode($raw, true) ?? [];
        $this->pos_payments_model->handle_mpesa_callback($callback);
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }

    public function callback_airtel()
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];

        $txn_id  = $data['transaction']['id'] ?? null;
        $status  = strtolower($data['transaction']['status'] ?? '') === 'ts' ? 'completed' : 'failed';

        if ($txn_id) {
            $this->pos_payments_model->update_mobile_money_txn($txn_id, [
                'status'         => $status,
                'transaction_id' => $data['transaction']['airtel_money_id'] ?? null,
                'raw_callback'   => $raw,
                'completed_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        echo '{"status":"OK"}';
        exit;
    }

    public function callback_mtn()
    {
        // MTN sends a notification on the referenceId (UUID we provided)
        $reference_id = $this->uri->segment(6);
        $raw          = file_get_contents('php://input');
        $data         = json_decode($raw, true) ?? [];

        $status_map = ['SUCCESSFUL' => 'completed', 'FAILED' => 'failed'];
        $status     = $status_map[$data['status'] ?? ''] ?? 'pending';

        if ($reference_id) {
            $this->pos_payments_model->update_mobile_money_txn($reference_id, [
                'status'         => $status,
                'transaction_id' => $data['financialTransactionId'] ?? null,
                'raw_callback'   => $raw,
                'completed_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        http_response_code(200);
        exit;
    }

    public function callback_telebirr()
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];

        $trade_no = $data['outTradeNo'] ?? null;
        $status   = ($data['tradeStatus'] ?? '') === 'TRADE_SUCCESS' ? 'completed' : 'failed';

        if ($trade_no) {
            $this->pos_payments_model->update_mobile_money_txn($trade_no, [
                'status'         => $status,
                'transaction_id' => $data['transactionNo'] ?? null,
                'raw_callback'   => $raw,
                'completed_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        echo '{"code":"0"}';
        exit;
    }
}
