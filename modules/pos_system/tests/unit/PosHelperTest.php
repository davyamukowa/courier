<?php
/**
 * Unit tests for POS helper functions.
 * Run with: vendor/bin/phpunit modules/pos_system/tests/unit/PosHelperTest.php
 */

use PHPUnit\Framework\TestCase;

// Stub CI environment for unit testing outside CodeIgniter
if (!function_exists('db_prefix')) {
    function db_prefix() { return 'tbl'; }
}

if (!function_exists('get_staff_user_id')) {
    function get_staff_user_id() { return 1; }
}

require_once __DIR__ . '/../../helpers/pos_helper.php';

class PosHelperTest extends TestCase
{
    // ─── pos_format_currency ─────────────────────────────────────────────────

    public function test_format_currency_kes()
    {
        $this->assertSame('KSh 1,234.50', pos_format_currency(1234.5, 'KES'));
    }

    public function test_format_currency_ugx_no_decimals()
    {
        $this->assertSame('USh 50,000', pos_format_currency(50000, 'UGX'));
    }

    public function test_format_currency_rwf_no_decimals()
    {
        $this->assertSame('RWF 10,000', pos_format_currency(10000, 'RWF'));
    }

    public function test_format_currency_unknown_uses_code()
    {
        $this->assertStringContainsString('USD', pos_format_currency(99.99, 'USD'));
    }

    public function test_format_currency_zero()
    {
        $this->assertSame('KSh 0.00', pos_format_currency(0, 'KES'));
    }

    // ─── pos_uuid ─────────────────────────────────────────────────────────────

    public function test_uuid_format()
    {
        $uuid = pos_uuid();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
    }

    public function test_uuid_uniqueness()
    {
        $ids = array_map(fn() => pos_uuid(), range(1, 100));
        $this->assertCount(100, array_unique($ids));
    }

    // ─── pos_get_json_input ───────────────────────────────────────────────────

    public function test_pos_get_json_input_empty_returns_empty_array()
    {
        $result = pos_get_json_input();
        $this->assertIsArray($result);
    }
}
