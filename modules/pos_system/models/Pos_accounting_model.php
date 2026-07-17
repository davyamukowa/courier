<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_accounting_model
 *
 * Auto-generates double-entry journal entries for every POS sale.
 * Integrates with Perfex CRM's accounting module (if installed) and
 * maintains its own lightweight journal as a fallback.
 *
 * Journal entries per sale:
 *   DR  Cash/Bank/Mobile Money      total paid
 *   CR  Sales Revenue               subtotal (excl. tax)
 *   CR  VAT Payable                 tax amount
 *   DR  COGS                        cost of goods sold
 *   CR  Inventory                   cost of goods sold
 */
class Pos_accounting_model extends App_Model
{
    // East African VAT account codes
    private const VAT_ACCOUNTS = [
        'KES' => ['code' => 'VAT-KE-16', 'name' => 'VAT Payable Kenya 16%',    'rate' => 0.1600],
        'UGX' => ['code' => 'VAT-UG-18', 'name' => 'VAT Payable Uganda 18%',   'rate' => 0.1800],
        'TZS' => ['code' => 'VAT-TZ-18', 'name' => 'VAT Payable Tanzania 18%', 'rate' => 0.1800],
        'RWF' => ['code' => 'VAT-RW-18', 'name' => 'VAT Payable Rwanda 18%',   'rate' => 0.1800],
        'ETB' => ['code' => 'VAT-ET-15', 'name' => 'VAT Payable Ethiopia 15%', 'rate' => 0.1500],
    ];

    // Payment method → asset account mapping
    private const PAYMENT_ACCOUNTS = [
        'cash'         => ['code' => 'CASH',     'name' => 'Cash in Hand'],
        'card'         => ['code' => 'BANK-CARD', 'name' => 'Card / POS Account'],
        'mpesa'        => ['code' => 'MM-MPESA',  'name' => 'M-Pesa Float Account'],
        'airtel'       => ['code' => 'MM-AIRTEL', 'name' => 'Airtel Money Account'],
        'mtn'          => ['code' => 'MM-MTN',    'name' => 'MTN Mobile Money Account'],
        'telebirr'     => ['code' => 'MM-TELEBIRR','name'=> 'Telebirr Account'],
        'bank'         => ['code' => 'BANK',      'name' => 'Bank Account'],
        'credit'       => ['code' => 'AR',        'name' => 'Accounts Receivable'],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Record journal entries for a completed sale.
     * Called from Pos_sales_model::create_sale() after commit.
     */
    public function post_sale_journal(array $sale, array $items, array $payments): int
    {
        $journal_id  = $this->_create_journal([
            'ref_type'   => 'pos_sale',
            'ref_id'     => $sale['id'],
            'description' => "POS Sale #{$sale['receipt_number']}",
            'currency'   => $sale['currency'] ?? 'KES',
            'branch_id'  => $sale['branch_id'],
            'date'       => date('Y-m-d', strtotime($sale['date_created'])),
        ]);

        $subtotal   = (float) $sale['subtotal'];
        $tax_amount = (float) $sale['tax_amount'];
        $cogs       = 0.0;

        foreach ($items as $item) {
            $cogs += (float) $item['cost_price'] * (float) $item['quantity'];
        }

        $currency = $sale['currency'] ?? 'KES';
        $vat      = self::VAT_ACCOUNTS[$currency] ?? self::VAT_ACCOUNTS['KES'];

        // 1) DR each payment method account
        foreach ($payments as $payment) {
            $method_code = $this->_get_payment_code((int) $payment['payment_method_id']);
            $account     = self::PAYMENT_ACCOUNTS[$method_code] ?? self::PAYMENT_ACCOUNTS['cash'];
            $this->_add_entry($journal_id, 'debit',  $account['code'], $account['name'],  (float) $payment['amount']);
        }

        // 2) CR Revenue (subtotal)
        $this->_add_entry($journal_id, 'credit', 'REVENUE',     'Sales Revenue', $subtotal);

        // 3) CR VAT Payable
        if ($tax_amount > 0) {
            $this->_add_entry($journal_id, 'credit', $vat['code'], $vat['name'], $tax_amount);
        }

        // 4) DR COGS / CR Inventory
        if ($cogs > 0) {
            $this->_add_entry($journal_id, 'debit',  'COGS',       'Cost of Goods Sold', $cogs);
            $this->_add_entry($journal_id, 'credit', 'INVENTORY',  'Inventory Asset',    $cogs);
        }

        // 5) DR Discount Expense if applicable
        $disc = (float) $sale['discount_amount'];
        if ($disc > 0) {
            $this->_add_entry($journal_id, 'debit', 'DISC-EXP', 'Sales Discount Expense', $disc);
        }

        // 6) If Perfex accounting module is active, push invoice
        if (module_exists('accounting')) {
            $this->_push_to_accounting_module($sale, $journal_id);
        }

        return $journal_id;
    }

    /**
     * Reverse journal entries on refund.
     */
    public function post_refund_journal(array $sale, float $refund_amount): int
    {
        $journal_id = $this->_create_journal([
            'ref_type'    => 'pos_refund',
            'ref_id'      => $sale['id'],
            'description' => "POS Refund #{$sale['receipt_number']}",
            'currency'    => $sale['currency'] ?? 'KES',
            'branch_id'   => $sale['branch_id'],
            'date'        => date('Y-m-d'),
        ]);

        $currency = $sale['currency'] ?? 'KES';
        $vat_rate = self::VAT_ACCOUNTS[$currency]['rate'] ?? 0.16;
        $vat      = round($refund_amount * $vat_rate / (1 + $vat_rate), 4);
        $net      = $refund_amount - $vat;

        // Reverse: CR Cash (refund given), DR Revenue (reduce), DR VAT
        $this->_add_entry($journal_id, 'credit', 'CASH',     'Cash in Hand',   $refund_amount);
        $this->_add_entry($journal_id, 'debit',  'REVENUE',  'Sales Revenue',  $net);
        $this->_add_entry($journal_id, 'debit',  self::VAT_ACCOUNTS[$currency]['code'] ?? 'VAT', 'VAT Payable', $vat);

        return $journal_id;
    }

    /**
     * Get journal entries for a sale.
     */
    public function get_journal(int $sale_id): array
    {
        $journal = $this->db
            ->where('ref_type', 'pos_sale')
            ->where('ref_id', $sale_id)
            ->get(db_prefix() . 'pos_journals')
            ->row_array();

        if (!$journal) {
            return [];
        }

        $journal['entries'] = $this->db
            ->where('journal_id', $journal['id'])
            ->get(db_prefix() . 'pos_journal_entries')
            ->result_array();

        return $journal;
    }

    /**
     * General ledger export for a branch and period.
     */
    public function general_ledger(int $branch_id, string $from, string $to): array
    {
        return $this->db->query("
            SELECT
                e.account_code,
                e.account_name,
                SUM(CASE WHEN e.type = 'debit'  THEN e.amount ELSE 0 END) AS total_debits,
                SUM(CASE WHEN e.type = 'credit' THEN e.amount ELSE 0 END) AS total_credits,
                SUM(CASE WHEN e.type = 'debit'  THEN e.amount ELSE 0 END) -
                SUM(CASE WHEN e.type = 'credit' THEN e.amount ELSE 0 END) AS balance
            FROM `" . db_prefix() . "pos_journal_entries` e
            JOIN `" . db_prefix() . "pos_journals` j ON j.id = e.journal_id
            WHERE j.branch_id = ?
              AND j.date BETWEEN ? AND ?
            GROUP BY e.account_code
            ORDER BY e.account_code
        ", [$branch_id, $from, $to])->result_array();
    }

    // ─── Schema additions (called from install.php) ───────────────────────────

    public static function create_tables(): void
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'pos_journals')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_journals` (
                `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref_type`    VARCHAR(50)      NOT NULL,
                `ref_id`      INT(11)          NOT NULL,
                `description` VARCHAR(255)     NOT NULL,
                `currency`    VARCHAR(10)      NOT NULL DEFAULT \'KES\',
                `branch_id`   INT(11) UNSIGNED NOT NULL,
                `date`        DATE             NOT NULL,
                `date_created` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_jrn_ref`    (`ref_type`, `ref_id`),
                INDEX `idx_jrn_branch` (`branch_id`),
                INDEX `idx_jrn_date`   (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }

        if (!$CI->db->table_exists(db_prefix() . 'pos_journal_entries')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_journal_entries` (
                `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `journal_id`   INT(11) UNSIGNED NOT NULL,
                `type`         ENUM(\'debit\',\'credit\') NOT NULL,
                `account_code` VARCHAR(30)      NOT NULL,
                `account_name` VARCHAR(100)     NOT NULL,
                `amount`       DECIMAL(15,4)    NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_je_journal` (`journal_id`),
                INDEX `idx_je_account` (`account_code`),
                FOREIGN KEY (`journal_id`) REFERENCES `' . db_prefix() . 'pos_journals`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }

        if (!$CI->db->table_exists(db_prefix() . 'pos_activity_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'pos_activity_logs` (
                `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `staff_id`   INT(11)          NOT NULL,
                `branch_id`  INT(11)          NOT NULL,
                `action`     VARCHAR(100)     NOT NULL,
                `entity`     VARCHAR(50)      DEFAULT NULL,
                `entity_id`  INT(11)          DEFAULT NULL,
                `context`    JSON             DEFAULT NULL,
                `ip_address` VARCHAR(45)      DEFAULT NULL,
                `user_agent` VARCHAR(255)     DEFAULT NULL,
                `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_al_staff`  (`staff_id`),
                INDEX `idx_al_branch` (`branch_id`),
                INDEX `idx_al_date`   (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function _create_journal(array $data): int
    {
        $this->db->insert(db_prefix() . 'pos_journals', $data);
        return $this->db->insert_id();
    }

    private function _add_entry(int $journal_id, string $type, string $code, string $name, float $amount): void
    {
        $this->db->insert(db_prefix() . 'pos_journal_entries', [
            'journal_id'   => $journal_id,
            'type'         => $type,
            'account_code' => $code,
            'account_name' => $name,
            'amount'       => $amount,
        ]);
    }

    private function _get_payment_code(int $method_id): string
    {
        $row = $this->db->select('code, provider')->where('id', $method_id)
                        ->get(db_prefix() . 'pos_payment_methods')->row();
        return $row ? ($row->provider ?: $row->code) : 'cash';
    }

    private function _push_to_accounting_module(array $sale, int $journal_id): void
    {
        // Bridge to Perfex accounting module — create an income entry
        try {
            $CI = &get_instance();
            $CI->load->model('accounting/Accounting_model');
            // Pass sale data to accounting model (implementation depends on accounting module version)
            // This is an integration hook — accounting module handles its own logic
            hooks()->do_action('pos_sale_accounting_sync', [
                'sale'       => $sale,
                'journal_id' => $journal_id,
            ]);
        } catch (Exception $e) {
            log_message('error', '[POS Accounting] Sync to accounting module failed: ' . $e->getMessage());
        }
    }
}
