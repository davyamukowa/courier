<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pos_payments_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_methods(?int $branch_id = null): array
    {
        $this->db->where('is_active', 1);

        if ($branch_id) {
            $this->db->group_start()
                     ->where('branch_id IS NULL')
                     ->or_where('branch_id', $branch_id)
                     ->group_end();
        } else {
            $this->db->where('branch_id IS NULL');
        }

        return $this->db->order_by('sort_order', 'ASC')
                        ->get(db_prefix() . 'pos_payment_methods')
                        ->result_array();
    }

    public function get_method(int $id): ?array
    {
        $row = $this->db->where('id', $id)->get(db_prefix() . 'pos_payment_methods')->row_array();
        // Never expose raw config credentials to client
        unset($row['config']);
        return $row ?: null;
    }

    public function record_mobile_money_txn(int $payment_id, array $data): int
    {
        $data['payment_id']    = $payment_id;
        $data['initiated_at']  = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'pos_mobile_money_txns', $data);
        return $this->db->insert_id();
    }

    public function update_mobile_money_txn(string $checkout_request_id, array $data): bool
    {
        return (bool) $this->db
            ->where('checkout_request_id', $checkout_request_id)
            ->update(db_prefix() . 'pos_mobile_money_txns', $data);
    }

    public function handle_mpesa_callback(array $callback): array
    {
        $body       = $callback['Body']['stkCallback'] ?? [];
        $req_id     = $body['CheckoutRequestID'] ?? null;
        $result_code = $body['ResultCode'] ?? -1;

        if (!$req_id) {
            return ['success' => false];
        }

        $txn = $this->db
            ->where('checkout_request_id', $req_id)
            ->get(db_prefix() . 'pos_mobile_money_txns')
            ->row_array();

        if (!$txn) {
            return ['success' => false];
        }

        if ((int) $result_code === 0) {
            $metadata = [];
            foreach ($body['CallbackMetadata']['Item'] ?? [] as $item) {
                $metadata[$item['Name']] = $item['Value'] ?? null;
            }

            $this->db->where('id', $txn['id'])->update(db_prefix() . 'pos_mobile_money_txns', [
                'status'         => 'completed',
                'transaction_id' => $metadata['MpesaReceiptNumber'] ?? null,
                'raw_callback'   => json_encode($callback),
                'completed_at'   => date('Y-m-d H:i:s'),
            ]);

            $this->db->where('id', $txn['payment_id'])
                     ->update(db_prefix() . 'pos_payments', ['status' => 'completed', 'reference' => $metadata['MpesaReceiptNumber'] ?? null]);
        } else {
            $this->db->where('id', $txn['id'])->update(db_prefix() . 'pos_mobile_money_txns', [
                'status'         => 'failed',
                'failure_reason' => $body['ResultDesc'] ?? 'Unknown',
                'raw_callback'   => json_encode($callback),
            ]);

            $this->db->where('id', $txn['payment_id'])
                     ->update(db_prefix() . 'pos_payments', ['status' => 'failed']);
        }

        return ['success' => true];
    }

    public function get_session_summary(int $session_id): array
    {
        return $this->db
            ->select('pm.name, pm.type, SUM(p.amount) AS total, COUNT(*) AS count')
            ->from(db_prefix() . 'pos_payments p')
            ->join(db_prefix() . 'pos_payment_methods pm', 'pm.id = p.payment_method_id')
            ->where('p.session_id', $session_id)
            ->where('p.status', 'completed')
            ->group_by('pm.id')
            ->get()
            ->result_array();
    }
}
