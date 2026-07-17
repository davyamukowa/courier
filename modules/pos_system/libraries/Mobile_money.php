<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Mobile_money – Unified gateway for East African mobile money providers.
 *
 * Supported: M-Pesa (Kenya), Airtel Money (UG/TZ/RW), MTN MoMo (UG/RW), Telebirr (ET)
 *
 * All initiate() calls return:
 *   ['success'=>bool, 'checkout_request_id'=>string|null, 'message'=>string, 'raw'=>array]
 *
 * All query_status() calls return:
 *   ['success'=>bool, 'status'=>'pending|completed|failed', 'transaction_id'=>string|null]
 */
class Mobile_money
{
    private $CI;
    private array $config;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Load provider config from pos_payment_methods.config (JSON column).
     */
    public function load_config(string $provider, int $branch_id): void
    {
        $row = $this->CI->db
            ->where('provider', $provider)
            ->where('is_active', 1)
            ->get(db_prefix() . 'pos_payment_methods')
            ->row();

        $global_config = $row ? (array) json_decode($row->config ?? '{}', true) : [];

        // Branch-level overrides stored in pos_settings as JSON
        $branch_cfg = pos_get_setting("mobile_money_{$provider}_config", $branch_id);
        $branch_arr = $branch_cfg ? (array) json_decode($branch_cfg, true) : [];

        $this->config = array_merge($global_config, $branch_arr);
        $this->config['provider'] = $provider;
    }

    /**
     * Initiate a payment request (STK Push / USSD Push).
     */
    public function initiate(string $provider, string $phone, float $amount, string $reference, int $branch_id): array
    {
        $this->load_config($provider, $branch_id);

        $phone = $this->normalise_phone($phone, $provider);

        switch ($provider) {
            case 'mpesa':
                return $this->mpesa_stk_push($phone, $amount, $reference);
            case 'airtel':
                return $this->airtel_ussd_push($phone, $amount, $reference);
            case 'mtn':
                return $this->mtn_request_to_pay($phone, $amount, $reference);
            case 'telebirr':
                return $this->telebirr_initiate($phone, $amount, $reference);
            default:
                return ['success' => false, 'message' => 'Unknown provider: ' . $provider];
        }
    }

    /**
     * Query transaction status (for polling after initiation).
     */
    public function query_status(string $provider, string $checkout_request_id, int $branch_id): array
    {
        $this->load_config($provider, $branch_id);

        switch ($provider) {
            case 'mpesa':
                return $this->mpesa_query($checkout_request_id);
            case 'mtn':
                return $this->mtn_query($checkout_request_id);
            default:
                // Airtel & Telebirr are callback-driven; no direct query
                return ['success' => true, 'status' => 'pending', 'transaction_id' => null];
        }
    }

    // ─── M-Pesa (Safaricom Kenya) ─────────────────────────────────────────────

    private function mpesa_stk_push(string $phone, float $amount, string $reference): array
    {
        $timestamp = date('YmdHis');
        $password  = base64_encode(
            $this->cfg('business_short_code') .
            $this->cfg('passkey') .
            $timestamp
        );

        $token = $this->mpesa_get_token();
        if (!$token) {
            return ['success' => false, 'message' => 'M-Pesa auth failed'];
        }

        $payload = [
            'BusinessShortCode' => $this->cfg('business_short_code'),
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) ceil($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->cfg('business_short_code'),
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->cfg('callback_url'),
            'AccountReference'  => $reference,
            'TransactionDesc'   => 'POS Payment',
        ];

        $response = $this->http_post(
            'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            $payload,
            ['Authorization: Bearer ' . $token]
        );

        $success = isset($response['CheckoutRequestID']);

        return [
            'success'             => $success,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'message'             => $response['CustomerMessage'] ?? ($response['errorMessage'] ?? 'Error'),
            'raw'                 => $response,
        ];
    }

    private function mpesa_query(string $checkout_request_id): array
    {
        $timestamp = date('YmdHis');
        $password  = base64_encode(
            $this->cfg('business_short_code') .
            $this->cfg('passkey') .
            $timestamp
        );

        $token = $this->mpesa_get_token();
        if (!$token) {
            return ['success' => false, 'status' => 'failed', 'transaction_id' => null];
        }

        $response = $this->http_post(
            'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
            [
                'BusinessShortCode' => $this->cfg('business_short_code'),
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkout_request_id,
            ],
            ['Authorization: Bearer ' . $token]
        );

        $result_code = $response['ResultCode'] ?? null;

        return [
            'success'        => true,
            'status'         => $result_code === '0' ? 'completed' : ($result_code === null ? 'pending' : 'failed'),
            'transaction_id' => $response['MpesaReceiptNumber'] ?? null,
            'raw'            => $response,
        ];
    }

    private function mpesa_get_token(): ?string
    {
        $credentials = base64_encode($this->cfg('consumer_key') . ':' . $this->cfg('consumer_secret'));

        $response = $this->http_get(
            'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            ['Authorization: Basic ' . $credentials]
        );

        return $response['access_token'] ?? null;
    }

    // ─── Airtel Money ─────────────────────────────────────────────────────────

    private function airtel_ussd_push(string $phone, float $amount, string $reference): array
    {
        $token = $this->airtel_get_token();
        if (!$token) {
            return ['success' => false, 'message' => 'Airtel auth failed'];
        }

        $txn_id  = 'POS-' . strtoupper(substr(md5($reference . time()), 0, 12));
        $payload = [
            'reference' => $reference,
            'subscriber' => ['country' => $this->cfg('country', 'KE'), 'currency' => $this->cfg('currency', 'KES'), 'msisdn' => $phone],
            'transaction' => ['amount' => $amount, 'country' => $this->cfg('country', 'KE'), 'currency' => $this->cfg('currency', 'KES'), 'id' => $txn_id],
        ];

        $response = $this->http_post(
            'https://openapi.airtel.africa/merchant/v1/payments/',
            $payload,
            [
                'Authorization: Bearer ' . $token,
                'X-Country: '   . $this->cfg('country', 'KE'),
                'X-Currency: '  . $this->cfg('currency', 'KES'),
                'Content-Type: application/json',
            ]
        );

        $success = ($response['status']['success'] ?? false) === true
                || ($response['status']['code'] ?? '') === 'ESB000010';

        return [
            'success'             => $success,
            'checkout_request_id' => $txn_id,
            'message'             => $response['status']['message'] ?? 'Initiated',
            'raw'                 => $response,
        ];
    }

    private function airtel_get_token(): ?string
    {
        $response = $this->http_post(
            'https://openapi.airtel.africa/auth/oauth2/token',
            [
                'client_id'     => $this->cfg('client_id'),
                'client_secret' => $this->cfg('client_secret'),
                'grant_type'    => 'client_credentials',
            ]
        );

        return $response['access_token'] ?? null;
    }

    // ─── MTN Mobile Money ─────────────────────────────────────────────────────

    private function mtn_request_to_pay(string $phone, float $amount, string $reference): array
    {
        $txn_id  = pos_uuid();
        $headers = [
            'Authorization: Bearer ' . $this->cfg('api_key'),
            'X-Reference-Id: '       . $txn_id,
            'X-Target-Environment: ' . $this->cfg('environment', 'sandbox'),
            'Ocp-Apim-Subscription-Key: ' . $this->cfg('subscription_key'),
            'Content-Type: application/json',
        ];

        $payload = [
            'amount'     => (string) $amount,
            'currency'   => $this->cfg('currency', 'UGX'),
            'externalId' => $reference,
            'payer'      => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
            'payerMessage' => 'POS Payment',
            'payeeNote'    => $reference,
        ];

        $response = $this->http_post(
            'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay',
            $payload,
            $headers
        );

        return [
            'success'             => true,   // MTN returns 202 on success (no body)
            'checkout_request_id' => $txn_id,
            'message'             => 'Payment initiated',
            'raw'                 => $response,
        ];
    }

    private function mtn_query(string $txn_id): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->cfg('api_key'),
            'X-Target-Environment: ' . $this->cfg('environment', 'sandbox'),
            'Ocp-Apim-Subscription-Key: ' . $this->cfg('subscription_key'),
        ];

        $response = $this->http_get(
            'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/' . $txn_id,
            $headers
        );

        $status_map = ['SUCCESSFUL' => 'completed', 'FAILED' => 'failed', 'PENDING' => 'pending'];
        $status     = $status_map[$response['status'] ?? ''] ?? 'pending';

        return [
            'success'        => true,
            'status'         => $status,
            'transaction_id' => $response['financialTransactionId'] ?? null,
            'raw'            => $response,
        ];
    }

    // ─── Telebirr (Ethio Telecom, Ethiopia) ───────────────────────────────────

    private function telebirr_initiate(string $phone, float $amount, string $reference): array
    {
        $timestamp  = (string) (time() * 1000);
        $nonce      = bin2hex(random_bytes(8));
        $out_trade_no = 'POS' . $timestamp;

        $plaintext = implode('&', [
            'appId='         . $this->cfg('app_id'),
            'appKey='        . $this->cfg('app_key'),
            'nonce='         . $nonce,
            'notifyUrl='     . $this->cfg('notify_url'),
            'outTradeNo='    . $out_trade_no,
            'receiverIdentifier=' . $phone,
            'shortCode='     . $this->cfg('short_code'),
            'subject=POS Payment',
            'timeoutExpress=30',
            'timestamp='     . $timestamp,
            'totalAmount='   . number_format($amount, 2, '.', ''),
        ]);

        $sign = $this->telebirr_sign($plaintext);

        $response = $this->http_post(
            $this->cfg('endpoint', 'https://196.188.120.4:443/payment/v1/merchant/preOrder'),
            ['appid' => $this->cfg('app_id'), 'sign' => $sign, 'ussd' => $this->telebirr_encrypt($plaintext)],
            ['Content-Type: application/json']
        );

        $success = ($response['code'] ?? '') === '0';

        return [
            'success'             => $success,
            'checkout_request_id' => $out_trade_no,
            'message'             => $response['msg'] ?? 'Initiated',
            'raw'                 => $response,
        ];
    }

    private function telebirr_sign(string $data): string
    {
        $private_key = $this->cfg('merchant_private_key');
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function telebirr_encrypt(string $data): string
    {
        $public_key = $this->cfg('telebirr_public_key');
        openssl_public_encrypt($data, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING);
        return base64_encode($encrypted);
    }

    // ─── HTTP Helpers ─────────────────────────────────────────────────────────

    private function http_post(string $url, array $data, array $headers = []): array
    {
        $headers = array_merge(['Content-Type: application/json'], $headers);
        $ch      = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,   // TODO: enable in production
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body ?: '{}', true) ?? [];
    }

    private function http_get(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body ?: '{}', true) ?? [];
    }

    private function cfg(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    private function normalise_phone(string $phone, string $provider): string
    {
        // Strip spaces, dashes, leading + or 0
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        $country_codes = [
            'mpesa'    => '254',
            'airtel'   => '254',
            'mtn'      => '256',
            'telebirr' => '251',
        ];

        $code = $country_codes[$provider] ?? '254';

        if (strpos($phone, $code) === 0) {
            return $phone;
        }

        // Strip leading 0
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }

        return $code . $phone;
    }
}
