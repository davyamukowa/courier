<?php
/**
 * Integration / API tests for POS System REST API.
 *
 * Requires: PHPUnit, GuzzleHttp
 * Run with: vendor/bin/phpunit modules/pos_system/tests/api/PosApiTest.php
 *
 * Set env vars before running:
 *   POS_API_URL   = http://localhost/perfex_crm/pos_system/api
 *   POS_EMAIL     = admin@example.com
 *   POS_PASSWORD  = password
 *   POS_BRANCH_ID = 1
 */

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class PosApiTest extends TestCase
{
    private static Client  $http;
    private static string  $token = '';
    private static int     $branch_id;
    private static string  $api_url;
    private static int     $created_product_id = 0;
    private static int     $created_customer_id = 0;
    private static int     $session_id = 0;
    private static string  $last_sale_id = '';

    public static function setUpBeforeClass(): void
    {
        self::$api_url   = rtrim(getenv('POS_API_URL') ?: 'http://localhost/perfex_crm/pos_system/api', '/');
        self::$branch_id = (int) (getenv('POS_BRANCH_ID') ?: 1);
        self::$http      = new Client(['base_uri' => self::$api_url . '/', 'http_errors' => false]);

        // Authenticate
        $resp = self::$http->post('auth/login', ['json' => [
            'email'     => getenv('POS_EMAIL')    ?: 'admin@example.com',
            'password'  => getenv('POS_PASSWORD') ?: 'password',
            'branch_id' => self::$branch_id,
        ]]);

        $body = json_decode((string) $resp->getBody(), true);
        self::$token = $body['data']['token'] ?? $body['token'] ?? '';
    }

    private function auth(): array
    {
        return ['Authorization' => 'Bearer ' . self::$token];
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_login_returns_token()
    {
        $this->assertNotEmpty(self::$token, 'Login should return a non-empty token');
    }

    public function test_me_returns_staff_info()
    {
        $resp = self::$http->get('auth/me', ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('staff', $body['data']);
    }

    public function test_invalid_token_returns_401()
    {
        $resp = self::$http->get('auth/me', ['headers' => ['Authorization' => 'Bearer invalid_token']]);
        $this->assertSame(401, $resp->getStatusCode());
    }

    // ─── Products ─────────────────────────────────────────────────────────────

    public function test_list_products()
    {
        $resp = self::$http->get('products', ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertIsArray($body['data']);
        $this->assertArrayHasKey('total', $body['meta']);
    }

    public function test_list_pos_products_includes_stock()
    {
        $resp = self::$http->get('products/pos', ['headers' => $this->auth()]);
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertSame(200, $resp->getStatusCode());
        if (!empty($body['data'])) {
            $this->assertArrayHasKey('stock_qty', $body['data'][0]);
        }
    }

    public function test_create_product()
    {
        $resp = self::$http->post('products/create', [
            'headers' => $this->auth(),
            'json'    => [
                'name'          => 'Test Maize Flour 2kg',
                'sku'           => 'TEST-MF-' . rand(1000, 9999),
                'selling_price' => 180.00,
                'cost_price'    => 140.00,
                'unit'          => 'kg',
            ],
        ]);

        $this->assertSame(201, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertTrue($body['success']);
        self::$created_product_id = (int) $body['data']['id'];
        $this->assertGreaterThan(0, self::$created_product_id);
    }

    public function test_get_single_product()
    {
        if (!self::$created_product_id) { $this->markTestSkipped(); }
        $resp = self::$http->get('products/' . self::$created_product_id, ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
    }

    // ─── Customers ────────────────────────────────────────────────────────────

    public function test_create_customer()
    {
        $resp = self::$http->post('customers', [
            'headers' => $this->auth(),
            'json'    => ['name' => 'Jane Test Wanjiku', 'phone' => '0700' . rand(100000, 999999)],
        ]);
        $this->assertSame(201, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        self::$created_customer_id = (int) $body['data']['id'];
        $this->assertGreaterThan(0, self::$created_customer_id);
    }

    public function test_search_customer()
    {
        $resp = self::$http->get('customers/search?q=Jane', ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertIsArray($body['data']);
    }

    public function test_duplicate_phone_returns_409()
    {
        if (!self::$created_customer_id) { $this->markTestSkipped(); }
        $customer = json_decode((string) self::$http->get('customers/' . self::$created_customer_id, ['headers' => $this->auth()])->getBody(), true);
        $phone = $customer['data']['phone'];

        $resp = self::$http->post('customers', [
            'headers' => $this->auth(),
            'json'    => ['name' => 'Another Person', 'phone' => $phone],
        ]);
        $this->assertSame(409, $resp->getStatusCode());
    }

    // ─── Sessions ─────────────────────────────────────────────────────────────

    public function test_open_session()
    {
        $resp = self::$http->post('sessions/open', [
            'headers' => $this->auth(),
            'json'    => ['opening_float' => 5000],
        ]);
        $body = json_decode((string) $resp->getBody(), true);

        // Either 201 (opened) or 409 (already open)
        $this->assertContains($resp->getStatusCode(), [201, 409]);

        if ($resp->getStatusCode() === 201) {
            self::$session_id = (int) ($body['data']['session_id'] ?? 0);
        }
    }

    public function test_get_current_session()
    {
        $resp = self::$http->get('sessions/current', ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
    }

    // ─── Sales ────────────────────────────────────────────────────────────────

    public function test_create_sale()
    {
        if (!self::$created_product_id) { $this->markTestSkipped(); }

        $sale_uid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        $resp = self::$http->post('sales/create', [
            'headers' => $this->auth(),
            'json'    => [
                'sale_uid'    => $sale_uid,
                'customer_id' => self::$created_customer_id ?: null,
                'items'       => [[
                    'product_id'   => self::$created_product_id,
                    'product_name' => 'Test Maize Flour 2kg',
                    'quantity'     => 1,
                    'unit_price'   => 180.00,
                    'cost_price'   => 140.00,
                    'discount_pct' => 0,
                    'discount_amt' => 0,
                    'tax_rate'     => 0,
                    'tax_amount'   => 0,
                    'line_total'   => 180.00,
                ]],
                'payments' => [[
                    'payment_method_id' => 1,
                    'amount'            => 180.00,
                    'status'            => 'completed',
                    'currency'          => 'KES',
                ]],
            ],
        ]);

        $body = json_decode((string) $resp->getBody(), true);
        $this->assertContains($resp->getStatusCode(), [201, 409]); // 409 if session required but not open

        if ($resp->getStatusCode() === 201) {
            self::$last_sale_id = (string) ($body['data']['id'] ?? '');
            $this->assertNotEmpty(self::$last_sale_id);
            $this->assertSame('completed', $body['data']['status']);
            $this->assertArrayHasKey('items', $body['data']);
        }
    }

    public function test_idempotent_offline_sync()
    {
        if (!self::$created_product_id) { $this->markTestSkipped(); }

        $sale_uid = 'offline-test-' . uniqid();
        $payload  = [[
            'sale'     => ['sale_uid' => $sale_uid, 'currency' => 'KES', 'notes' => 'offline test'],
            'items'    => [['product_id' => self::$created_product_id, 'product_name' => 'Test', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 80, 'discount_pct' => 0, 'discount_amt' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'line_total' => 100]],
            'payments' => [['payment_method_id' => 1, 'amount' => 100, 'status' => 'completed', 'currency' => 'KES']],
        ]];

        // Sync same sale twice — second should succeed without duplicate
        $r1 = self::$http->post('sales/sync', ['headers' => $this->auth(), 'json' => ['sales' => $payload]]);
        $r2 = self::$http->post('sales/sync', ['headers' => $this->auth(), 'json' => ['sales' => $payload]]);

        $this->assertSame(200, $r1->getStatusCode());
        $this->assertSame(200, $r2->getStatusCode());
        $b2 = json_decode((string) $r2->getBody(), true);
        // Second sync: should be in success (idempotent, no duplicate created)
        $this->assertContains($sale_uid, $b2['data']['success'] ?? []);
    }

    // ─── Reports ─────────────────────────────────────────────────────────────

    public function test_daily_sales_report()
    {
        $from = date('Y-m-01');
        $to   = date('Y-m-t');
        $resp = self::$http->get("reports/daily-sales?from={$from}&to={$to}", ['headers' => $this->auth()]);
        $this->assertSame(200, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertIsArray($body['data']);
    }

    public function test_report_invalid_date_returns_422()
    {
        $resp = self::$http->get('reports/daily-sales?from=not-a-date&to=also-not', ['headers' => $this->auth()]);
        $this->assertSame(422, $resp->getStatusCode());
    }

    // ─── Edge cases ───────────────────────────────────────────────────────────

    public function test_missing_required_field_returns_422()
    {
        $resp = self::$http->post('customers', [
            'headers' => $this->auth(),
            'json'    => ['name' => 'Only Name'],  // missing phone
        ]);
        $this->assertSame(422, $resp->getStatusCode());
        $body = json_decode((string) $resp->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('details', $body['error']);
    }

    public function test_nonexistent_resource_returns_404()
    {
        $resp = self::$http->get('products/99999999', ['headers' => $this->auth()]);
        $this->assertSame(404, $resp->getStatusCode());
    }

    public function test_unauthenticated_request_returns_401()
    {
        $resp = self::$http->get('products');
        $this->assertSame(401, $resp->getStatusCode());
    }

    // ─── Cleanup ─────────────────────────────────────────────────────────────

    public static function tearDownAfterClass(): void
    {
        // Clean up test data via API
        if (self::$created_product_id) {
            self::$http->post('products/' . self::$created_product_id . '/delete',
                ['headers' => ['Authorization' => 'Bearer ' . self::$token]]);
        }
    }
}
