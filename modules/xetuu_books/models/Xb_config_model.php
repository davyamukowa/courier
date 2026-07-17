<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_config_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_setting($key)
    {
        $this->db->where('setting_key', $key);
        $res = $this->db->get('acc_settings')->row();
        return $res ? $res->setting_value : null;
    }

    public function set_setting($key, $value)
    {
        $this->db->where('setting_key', $key);
        if ($this->db->get('acc_settings')->num_rows() > 0) {
            $this->db->where('setting_key', $key);
            return $this->db->update('acc_settings', ['setting_value' => $value]);
        } else {
            return $this->db->insert('acc_settings', [
                'setting_key'   => $key,
                'setting_value' => $value
            ]);
        }
    }

    public function save_settings($settings)
    {
        foreach ($settings as $key => $value) {
            $this->set_setting($key, $value);
        }
        return true;
    }

    public function get_all_settings()
    {
        $res = $this->db->get('acc_settings')->result();
        $settings = [];
        foreach ($res as $row) {
            $settings[$row->setting_key] = $row->setting_value;
        }
        return $settings;
    }

    public function get_accounts($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        $this->db->order_by('code', 'ASC');
        return $this->db->get('acc_accounts')->result();
    }

    public function get_accounts_with_balances()
    {
        $p   = $this->db->dbprefix;
        $sql = "SELECT a.*,
            COALESCE(SUM(CASE WHEN m.state = 'posted' THEN ml.debit  ELSE 0 END), 0) AS total_debit,
            COALESCE(SUM(CASE WHEN m.state = 'posted' THEN ml.credit ELSE 0 END), 0) AS total_credit
            FROM {$p}acc_accounts a
            LEFT JOIN {$p}acc_move_lines ml ON ml.account_id = a.id
            LEFT JOIN {$p}acc_moves      m  ON m.id = ml.move_id
            GROUP BY a.id
            ORDER BY a.code ASC, a.name ASC";
        return $this->db->query($sql)->result();
    }

    public function get_account_ledger($account_id)
    {
        $ids = $this->_get_account_subtree_ids((int)$account_id);
        if (empty($ids)) return [];
        $p       = $this->db->dbprefix;
        $id_list = implode(',', array_map('intval', $ids));
        $sql = "SELECT ml.id, COALESCE(ml.date, m.date) AS date, ml.name AS description, ml.ref,
            m.name AS entry_ref,
            COALESCE(j.name, '') AS journal_name,
            a.code AS account_code, a.name AS account_name,
            ml.debit, ml.credit
            FROM {$p}acc_move_lines ml
            INNER JOIN {$p}acc_moves    m ON m.id = ml.move_id AND m.state = 'posted'
            LEFT  JOIN {$p}acc_journals j ON j.id = m.journal_id
            LEFT  JOIN {$p}acc_accounts a ON a.id = ml.account_id
            WHERE ml.account_id IN ({$id_list})
            ORDER BY COALESCE(ml.date, m.date) DESC, ml.id DESC
            LIMIT 200";
        return $this->db->query($sql)->result();
    }

    private function _get_account_subtree_ids($account_id, $depth = 0)
    {
        if ($depth > 10) return [];
        $p    = $this->db->dbprefix;
        $ids  = [$account_id];
        $rows = $this->db->query(
            "SELECT id FROM {$p}acc_accounts WHERE parent_id = " . (int)$account_id
        )->result();
        foreach ($rows as $row) {
            $ids = array_merge($ids, $this->_get_account_subtree_ids((int)$row->id, $depth + 1));
        }
        return array_unique($ids);
    }

    // Delete old flat 4-digit accounts (1000-6999) that have no posted transactions
    public function delete_legacy_flat_accounts()
    {
        $p = $this->db->dbprefix;

        // Collect IDs of 4-digit legacy accounts (1000–6999) with zero transactions
        $rows = $this->db->query(
            "SELECT id FROM `{$p}acc_accounts`
             WHERE CHAR_LENGTH(code) = 4
               AND CAST(code AS UNSIGNED) BETWEEN 1000 AND 6999
               AND id NOT IN (
                   SELECT DISTINCT account_id FROM `{$p}acc_move_lines`
                   WHERE account_id IS NOT NULL
               )"
        )->result();

        if (empty($rows)) return 0;

        $ids = implode(',', array_map(function ($r) { return (int)$r->id; }, $rows));

        // Detach any accounts whose parent_id points to one of the legacy accounts
        $this->db->query("UPDATE `{$p}acc_accounts` SET parent_id = NULL WHERE parent_id IN ({$ids})");

        // Delete the legacy accounts
        $this->db->query("DELETE FROM `{$p}acc_accounts` WHERE id IN ({$ids})");

        return count($rows);
    }

    // Fix parent_id for the original flat 4-digit accounts (idempotent — only updates where parent_id IS NULL)
    public function fix_account_parents()
    {
        $p = $this->db->dbprefix;
        $pairs = [
            '1010' => '1000',
            '1020' => '1000',
            '1030' => '1000',
            '1040' => '1000',
            '1510' => '1500',
        ];
        foreach ($pairs as $child_code => $parent_code) {
            $this->db->query(
                "UPDATE `{$p}acc_accounts` c
                 INNER JOIN `{$p}acc_accounts` p ON p.code = '{$parent_code}'
                 SET c.parent_id = p.id
                 WHERE c.code = '{$child_code}' AND c.parent_id IS NULL"
            );
        }
    }

    public function search_accounts($q)
    {
        $this->db->like('code', $q);
        $this->db->or_like('name', $q);
        $this->db->order_by('code', 'ASC');
        $this->db->limit(20);
        $res = $this->db->get('acc_accounts')->result();
        
        $data = [];
        foreach ($res as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->code . ' - ' . $row->name
            ];
        }
        return $data;
    }

    public function get_account_types()
    {
        return [
            'Asset','Current Asset','Fixed Asset','Bank','Cash',
            'Liability','Current Liability','Long-term Liability',
            'Equity','Revenue','Cost of Revenue','Expense',
            'Other Income','Other Expense','Tax','Receivable','Payable'
        ];
    }

    public function save_account($data, $id = null)
    {
        if (empty($data['name'])) {
            return false;
        }

        $account_data = [
            'name'                 => $data['name'],
            'type'                 => !empty($data['type']) ? $data['type'] : 'Asset',
            'is_group'             => !empty($data['is_group']) ? 1 : 0,
            'allow_reconciliation' => !empty($data['allow_reconciliation']) ? 1 : 0,
            'deprecated'           => !empty($data['deprecated']) ? 1 : 0,
        ];

        if (!empty($data['code'])) {
            $account_data['code'] = trim($data['code']);
        }

        if (isset($data['parent_id']) && $data['parent_id'] !== '') {
            $account_data['parent_id'] = (int)$data['parent_id'] ?: null;
        }

        if (array_key_exists('account_category', $data)) {
            $account_data['account_category'] = $data['account_category'] !== '' ? $data['account_category'] : null;
        }

        if (!empty($data['currency_id'])) {
            $account_data['currency_id'] = (int)$data['currency_id'];
        }

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('acc_accounts', $account_data);
            return $id;
        } else {
            // Auto-generate code if not provided (code column allows NULL, but keep unique)
            if (empty($account_data['code'])) {
                $account_data['code'] = null;
            }
            $this->db->insert('acc_accounts', $account_data);
            return $this->db->insert_id();
        }
    }

    public function seed_default_coa()
    {
        // Build existing code→id map so we can resolve parent references
        $existing = $this->db->get('acc_accounts')->result();
        $map = [];
        foreach ($existing as $acc) {
            if ($acc->code !== null) {
                $map[$acc->code] = (int)$acc->id;
            }
        }

        // [code, name, type, is_group, parent_code]
        $coa = [
            // Root groups
            ['10000', 'Application of Funds (Assets)', 'Asset', 1, null],
            ['20000', 'Source of Funds (Liabilities)', 'Liability', 1, null],
            ['30000', 'Equity', 'Equity', 1, null],
            ['40000', 'Income', 'Revenue', 1, null],
            ['50000', 'Expenses', 'Expense', 1, null],
            // Assets level 2
            ['11000', 'Current Assets', 'Current Asset', 1, '10000'],
            ['12000', 'Fixed Assets', 'Fixed Asset', 1, '10000'],
            ['13000', 'Investments', 'Asset', 1, '10000'],
            ['14000', 'Temporary Accounts', 'Asset', 1, '10000'],
            // Current Assets level 3
            ['11100', 'Accounts Receivable', 'Receivable', 1, '11000'],
            ['11200', 'Bank Accounts', 'Bank', 1, '11000'],
            ['11300', 'Cash In Hand', 'Cash', 1, '11000'],
            ['11400', 'Loans and Advances (Assets)', 'Current Asset', 1, '11000'],
            ['11500', 'Prepaid Expenses', 'Current Asset', 0, '11000'],
            ['11600', 'Securities and Deposits', 'Current Asset', 1, '11000'],
            ['11700', 'Short-term Investments', 'Current Asset', 0, '11000'],
            ['11800', 'Stock Assets', 'Current Asset', 1, '11000'],
            ['11900', 'Tax Assets', 'Tax', 1, '11000'],
            // Fixed Assets level 3
            ['12100', 'Accumulated Depreciation', 'Fixed Asset', 0, '12000'],
            ['12200', 'Buildings', 'Fixed Asset', 0, '12000'],
            ['12300', 'CWIP Account', 'Fixed Asset', 0, '12000'],
            ['12400', 'Capital Equipment', 'Fixed Asset', 0, '12000'],
            ['12500', 'Electronic Equipment', 'Fixed Asset', 0, '12000'],
            ['12600', 'Furniture and Fixtures', 'Fixed Asset', 0, '12000'],
            ['12700', 'Office Equipment', 'Fixed Asset', 0, '12000'],
            ['12800', 'Plants and Machineries', 'Fixed Asset', 0, '12000'],
            ['12900', 'Software', 'Fixed Asset', 0, '12000'],
            // Temporary level 3
            ['14100', 'Temporary Opening', 'Asset', 0, '14000'],
            // Liabilities level 2
            ['21000', 'Current Liabilities', 'Current Liability', 1, '20000'],
            ['22000', 'Non-Current Liabilities', 'Liability', 1, '20000'],
            // Current Liabilities level 3
            ['21100', 'Accounts Payable', 'Payable', 1, '21000'],
            ['21200', 'Payroll Payable', 'Current Liability', 0, '21000'],
            ['21300', 'Accrued Expenses', 'Current Liability', 0, '21000'],
            ['21400', 'Customer Advances', 'Current Liability', 0, '21000'],
            ['21500', 'Duties and Taxes', 'Current Liability', 1, '21000'],
            ['21600', 'Loans (Liabilities)', 'Current Liability', 1, '21000'],
            ['21700', 'Short-term Provisions', 'Current Liability', 0, '21000'],
            ['21800', 'Stock Liabilities', 'Current Liability', 1, '21000'],
            // Non-Current Liabilities level 3
            ['22100', 'Employee Benefits Obligation', 'Long-term Liability', 0, '22000'],
            ['22200', 'Long-term Provisions', 'Long-term Liability', 0, '22000'],
            // Equity level 2
            ['30100', 'Capital Stock', 'Equity', 0, '30000'],
            ['30200', 'Dividends Paid', 'Equity', 0, '30000'],
            ['30300', 'Opening Balance Equity', 'Equity', 0, '30000'],
            ['30400', 'Retained Earnings', 'Equity', 0, '30000'],
            ['30500', 'Revaluation Surplus', 'Equity', 0, '30000'],
            // Income level 2
            ['41000', 'Direct Income', 'Revenue', 1, '40000'],
            ['42000', 'Indirect Income', 'Revenue', 1, '40000'],
            // Income level 3
            ['41100', 'Sales', 'Revenue', 0, '41000'],
            ['41200', 'Service', 'Revenue', 0, '41000'],
            ['42100', 'Interest Income', 'Other Income', 1, '42000'],
            // Income level 4
            ['42110', 'Interest on Fixed Deposits', 'Other Income', 0, '42100'],
            // Expenses level 2
            ['51000', 'Direct Expenses', 'Expense', 1, '50000'],
            ['52000', 'Indirect Expenses', 'Expense', 1, '50000'],
            // Direct Expenses level 3
            ['51100', 'Stock Expenses', 'Expense', 1, '51000'],
            // Stock Expenses level 4
            ['51110', 'Cost of Goods Sold', 'Cost of Revenue', 0, '51100'],
            ['51120', 'Expenses Included In Asset Valuation', 'Expense', 1, '51100'],
            ['51130', 'Stock Adjustment', 'Expense', 0, '51100'],
            // Level 5
            ['51121', 'Expenses Included In Valuation', 'Expense', 0, '51120'],
            // Indirect Expenses level 3
            ['52100', 'Administrative Expenses', 'Expense', 1, '52000'],
            // Administrative Expenses level 4
            ['52110', 'Bank Charges', 'Expense', 0, '52100'],
            ['52120', 'Commission on Sales', 'Expense', 0, '52100'],
            ['52130', 'Depreciation', 'Expense', 0, '52100'],
            ['52140', 'Entertainment Expenses', 'Expense', 0, '52100'],
            ['52150', 'Exchange Gain/Loss', 'Other Expense', 0, '52100'],
            ['52160', 'Freight and Forwarding Charges', 'Expense', 0, '52100'],
            ['52170', 'Gain/Loss on Asset Disposal', 'Other Expense', 0, '52100'],
            ['52180', 'Impairment', 'Expense', 0, '52100'],
            ['52190', 'Interest Expense', 'Expense', 0, '52100'],
            ['52200', 'Legal Expenses', 'Expense', 0, '52100'],
            ['52210', 'Marketing Expenses', 'Expense', 0, '52100'],
            ['52220', 'Miscellaneous Expenses', 'Expense', 0, '52100'],
            ['52230', 'Office Maintenance Expenses', 'Expense', 0, '52100'],
            ['52240', 'Office Rent', 'Expense', 0, '52100'],
            ['52250', 'Postal Expenses', 'Expense', 0, '52100'],
            ['52260', 'Print and Stationery', 'Expense', 0, '52100'],
            ['52270', 'Round Off', 'Expense', 0, '52100'],
            ['52280', 'Salary', 'Expense', 0, '52100'],
            ['52290', 'Sales Expenses', 'Expense', 0, '52100'],
            ['52300', 'Tax Expense', 'Expense', 0, '52100'],
            ['52310', 'Telephone Expenses', 'Expense', 0, '52100'],
            ['52320', 'Travel Expenses', 'Expense', 0, '52100'],
            ['52330', 'Utility Expenses', 'Expense', 0, '52100'],
            ['52340', 'Write Off', 'Expense', 0, '52100'],
            // AR/AP leaf accounts level 4
            ['11110', 'Debtors', 'Receivable', 0, '11100'],
            ['11310', 'Cash', 'Cash', 0, '11300'],
            ['11410', 'Employee Advances', 'Current Asset', 0, '11400'],
            ['11610', 'Earnest Money', 'Current Asset', 0, '11600'],
            ['11810', 'Stock In Hand', 'Current Asset', 0, '11800'],
            ['21110', 'Creditors', 'Payable', 0, '21100'],
            ['21510', 'VAT', 'Tax', 0, '21500'],
            ['21610', 'Bank Overdraft Account', 'Current Liability', 0, '21600'],
            ['21620', 'Secured Loans', 'Current Liability', 0, '21600'],
            ['21630', 'Unsecured Loans', 'Current Liability', 0, '21600'],
            ['21810', 'Asset Received But Not Billed', 'Current Liability', 0, '21800'],
            ['21820', 'Stock Received But Not Billed', 'Current Liability', 0, '21800'],
        ];

        $inserted = 0;
        foreach ($coa as $row) {
            [$code, $name, $type, $is_group, $parent_code] = $row;

            if (isset($map[$code])) {
                continue; // already exists
            }

            $parent_id = ($parent_code && isset($map[$parent_code])) ? $map[$parent_code] : null;

            $this->db->insert('acc_accounts', [
                'code'      => $code,
                'name'      => $name,
                'type'      => $type,
                'is_group'  => $is_group,
                'parent_id' => $parent_id,
            ]);

            $new_id = $this->db->insert_id();
            $map[$code] = $new_id;
            $inserted++;
        }

        return $inserted;
    }

    public function generate_coa($template)
    {
        $p = $this->db->dbprefix;

        // 1. Delete accounts & move lines (DELETE is safer than TRUNCATE for permissions)
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        
        // Disable strict mode so that any missing NOT NULL columns from the core module get implicit defaults
        $this->db->query("SET SESSION sql_mode = ''");
        
        $this->db->query("DELETE FROM `{$p}acc_move_lines` WHERE id > 0");
        $this->db->query("DELETE FROM `{$p}acc_moves` WHERE id > 0");
        $this->db->query("DELETE FROM `{$p}acc_accounts` WHERE id > 0");
        $this->db->query("ALTER TABLE `{$p}acc_accounts` AUTO_INCREMENT = 1");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        
        $err = $this->db->error();
        if ($err['code'] !== 0) {
            $this->db->db_debug = $db_debug;
            throw new \Exception("DB Error during cleanup: " . $err['message']);
        }
        $this->db->db_debug = $db_debug;

        // 2. Base Chart of Accounts
        $coa = [
            // Root groups
            ['10000', 'Application of Funds (Assets)', 'Asset', 1, null],
            ['20000', 'Source of Funds (Liabilities)', 'Liability', 1, null],
            ['30000', 'Equity', 'Equity', 1, null],
            ['40000', 'Income', 'Revenue', 1, null],
            ['50000', 'Expenses', 'Expense', 1, null],
            // Assets level 2
            ['11000', 'Current Assets', 'Current Asset', 1, '10000'],
            ['12000', 'Fixed Assets', 'Fixed Asset', 1, '10000'],
            ['13000', 'Investments', 'Asset', 1, '10000'],
            ['14000', 'Temporary Accounts', 'Asset', 1, '10000'],
            // Current Assets level 3
            ['11100', 'Accounts Receivable', 'Receivable', 1, '11000'],
            ['11200', 'Bank Accounts', 'Bank', 1, '11000'],
            ['11300', 'Cash In Hand', 'Cash', 1, '11000'],
            ['11400', 'Loans and Advances (Assets)', 'Current Asset', 1, '11000'],
            ['11500', 'Prepaid Expenses', 'Current Asset', 0, '11000'],
            ['11600', 'Securities and Deposits', 'Current Asset', 1, '11000'],
            ['11700', 'Short-term Investments', 'Current Asset', 0, '11000'],
            ['11800', 'Stock Assets', 'Current Asset', 1, '11000'],
            ['11900', 'Tax Assets', 'Tax', 1, '11000'],
            // Fixed Assets level 3
            ['12100', 'Accumulated Depreciation', 'Fixed Asset', 0, '12000'],
            ['12200', 'Buildings', 'Fixed Asset', 0, '12000'],
            ['12300', 'CWIP Account', 'Fixed Asset', 0, '12000'],
            ['12400', 'Capital Equipment', 'Fixed Asset', 0, '12000'],
            ['12500', 'Electronic Equipment', 'Fixed Asset', 0, '12000'],
            ['12600', 'Furniture and Fixtures', 'Fixed Asset', 0, '12000'],
            ['12700', 'Office Equipment', 'Fixed Asset', 0, '12000'],
            ['12800', 'Plants and Machineries', 'Fixed Asset', 0, '12000'],
            ['12900', 'Software', 'Fixed Asset', 0, '12000'],
            // Temporary level 3
            ['14100', 'Temporary Opening', 'Asset', 0, '14000'],
            // Liabilities level 2
            ['21000', 'Current Liabilities', 'Current Liability', 1, '20000'],
            ['22000', 'Non-Current Liabilities', 'Liability', 1, '20000'],
            // Current Liabilities level 3
            ['21100', 'Accounts Payable', 'Payable', 1, '21000'],
            ['21200', 'Payroll Payable', 'Current Liability', 0, '21000'],
            ['21300', 'Accrued Expenses', 'Current Liability', 0, '21000'],
            ['21400', 'Customer Advances', 'Current Liability', 0, '21000'],
            ['21500', 'Duties and Taxes', 'Current Liability', 1, '21000'],
            ['21600', 'Loans (Liabilities)', 'Current Liability', 1, '21000'],
            ['21700', 'Short-term Provisions', 'Current Liability', 0, '21000'],
            ['21800', 'Stock Liabilities', 'Current Liability', 1, '21000'],
            // Non-Current Liabilities level 3
            ['22100', 'Employee Benefits Obligation', 'Long-term Liability', 0, '22000'],
            ['22200', 'Long-term Provisions', 'Long-term Liability', 0, '22000'],
            // Equity level 2
            ['30100', 'Capital Stock', 'Equity', 0, '30000'],
            ['30200', 'Dividends Paid', 'Equity', 0, '30000'],
            ['30300', 'Opening Balance Equity', 'Equity', 0, '30000'],
            ['30400', 'Retained Earnings', 'Equity', 0, '30000'],
            ['30500', 'Revaluation Surplus', 'Equity', 0, '30000'],
            // Income level 2
            ['41000', 'Direct Income', 'Revenue', 1, '40000'],
            ['42000', 'Indirect Income', 'Revenue', 1, '40000'],
            // Income level 3
            ['41100', 'Sales', 'Revenue', 0, '41000'],
            ['41200', 'Service', 'Revenue', 0, '41000'],
            ['42100', 'Interest Income', 'Other Income', 1, '42000'],
            // Income level 4
            ['42110', 'Interest on Fixed Deposits', 'Other Income', 0, '42100'],
            // Expenses level 2
            ['51000', 'Direct Expenses', 'Expense', 1, '50000'],
            ['52000', 'Indirect Expenses', 'Expense', 1, '50000'],
            // Direct Expenses level 3
            ['51100', 'Stock Expenses', 'Expense', 1, '51000'],
            // Stock Expenses level 4
            ['51110', 'Cost of Goods Sold', 'Cost of Revenue', 0, '51100'],
            ['51120', 'Expenses Included In Asset Valuation', 'Expense', 1, '51100'],
            ['51130', 'Stock Adjustment', 'Expense', 0, '51100'],
            // Level 5
            ['51121', 'Expenses Included In Valuation', 'Expense', 0, '51120'],
            // Indirect Expenses level 3
            ['52100', 'Administrative Expenses', 'Expense', 1, '52000'],
            // Administrative Expenses level 4
            ['52110', 'Bank Charges', 'Expense', 0, '52100'],
            ['52120', 'Commission on Sales', 'Expense', 0, '52100'],
            ['52130', 'Depreciation', 'Expense', 0, '52100'],
            ['52140', 'Entertainment Expenses', 'Expense', 0, '52100'],
            ['52150', 'Exchange Gain/Loss', 'Other Expense', 0, '52100'],
            ['52160', 'Freight and Forwarding Charges', 'Expense', 0, '52100'],
            ['52170', 'Gain/Loss on Asset Disposal', 'Other Expense', 0, '52100'],
            ['52180', 'Impairment', 'Expense', 0, '52100'],
            ['52190', 'Interest Expense', 'Expense', 0, '52100'],
            ['52200', 'Legal Expenses', 'Expense', 0, '52100'],
            ['52210', 'Marketing Expenses', 'Expense', 0, '52100'],
            ['52220', 'Miscellaneous Expenses', 'Expense', 0, '52100'],
            ['52230', 'Office Maintenance Expenses', 'Expense', 0, '52100'],
            ['52240', 'Office Rent', 'Expense', 0, '52100'],
            ['52250', 'Postal Expenses', 'Expense', 0, '52100'],
            ['52260', 'Print and Stationery', 'Expense', 0, '52100'],
            ['52270', 'Round Off', 'Expense', 0, '52100'],
            ['52280', 'Salary', 'Expense', 0, '52100'],
            ['52290', 'Sales Expenses', 'Expense', 0, '52100'],
            ['52300', 'Tax Expense', 'Expense', 0, '52100'],
            ['52310', 'Telephone Expenses', 'Expense', 0, '52100'],
            ['52320', 'Travel Expenses', 'Expense', 0, '52100'],
            ['52330', 'Utility Expenses', 'Expense', 0, '52100'],
            ['52340', 'Write Off', 'Expense', 0, '52100'],
            // AR/AP leaf accounts level 4
            ['11110', 'Debtors', 'Receivable', 0, '11100'],
            ['11310', 'Cash', 'Cash', 0, '11300'],
            ['11410', 'Employee Advances', 'Current Asset', 0, '11400'],
            ['11610', 'Earnest Money', 'Current Asset', 0, '11600'],
            ['11810', 'Stock In Hand', 'Current Asset', 0, '11800'],
            ['21110', 'Creditors', 'Payable', 0, '21100'],
            ['21510', 'VAT', 'Tax', 0, '21500'],
            ['21610', 'Bank Overdraft Account', 'Current Liability', 0, '21600'],
            ['21620', 'Secured Loans', 'Current Liability', 0, '21600'],
            ['21630', 'Unsecured Loans', 'Current Liability', 0, '21600'],
            ['21810', 'Asset Received But Not Billed', 'Current Liability', 0, '21800'],
            ['21820', 'Stock Received But Not Billed', 'Current Liability', 0, '21800'],
            ['21250', 'Net Wages Payable', 'Current Liability', 0, '21200'],

            // Dedicated leaf accounts for settings fields
            ['11210', 'Bank', 'Bank', 0, '11200'],
            ['51010', 'Purchases', 'Expense', 0, '51000'],
            ['41120', 'Sales Returns', 'Revenue', 0, '41000'],
            ['41130', 'Sales Discounts', 'Revenue', 0, '41000'],
            ['21520', 'VAT Input', 'Tax', 0, '21500'],
            ['21530', 'VAT Output', 'Tax', 0, '21500'],
            ['21540', 'Withholding Tax Payable', 'Tax', 0, '21500'],
            ['21550', 'Withholding VAT Payable', 'Tax', 0, '21500'],
            ['21210', 'PAYE Payable', 'Current Liability', 0, '21200'],
            ['21220', 'NSSF Payable', 'Current Liability', 0, '21200'],
            ['21230', 'SHA Payable', 'Current Liability', 0, '21200'],
            ['21240', 'Housing Levy Payable', 'Current Liability', 0, '21200'],
            ['42120', 'Realized Exchange Gain', 'Other Income', 0, '42100'],
            ['52155', 'Realized Exchange Loss', 'Other Expense', 0, '52100'],
            ['42130', 'Unrealized Exchange Gain', 'Other Income', 0, '42100'],
            ['52156', 'Unrealized Exchange Loss', 'Other Expense', 0, '52100'],
            ['42140', 'Cash Discount Gain', 'Other Income', 0, '42100'],
            ['52345', 'Cash Discount Loss', 'Other Expense', 0, '52100'],
            ['52346', 'Settlement Discount', 'Other Expense', 0, '52100'],
        ];

        // 3. Add Template Specific accounts
        if ($template === 'manufacturing') {
            $coa[] = ['11820', 'Raw Materials Stock', 'Current Asset', 0, '11800'];
            $coa[] = ['11830', 'Work-in-Progress Stock', 'Current Asset', 0, '11800'];
            $coa[] = ['11840', 'Finished Goods Stock', 'Current Asset', 0, '11800'];
            $coa[] = ['51200', 'Direct Labor Expenses', 'Expense', 0, '51000'];
            $coa[] = ['51300', 'Factory Overhead Expenses', 'Expense', 0, '51000'];
        } elseif ($template === 'retail') {
            $coa[] = ['11850', 'Merchandise Inventory', 'Current Asset', 0, '11800'];
            $coa[] = ['41300', 'Retail Sales Revenue', 'Revenue', 0, '41000'];
            $coa[] = ['51115', 'Cost of Retail Sales', 'Cost of Revenue', 0, '51100'];
        } elseif ($template === 'service') {
            $coa[] = ['41210', 'Professional Services Income', 'Revenue', 0, '41200'];
            $coa[] = ['41220', 'Consulting Revenue', 'Revenue', 0, '41200'];
            $coa[] = ['51210', 'Direct Service Labor', 'Expense', 0, '51000'];
        } elseif ($template === 'non_profit') {
            $coa[] = ['41400', 'Donations and Contributions', 'Revenue', 0, '40000'];
            $coa[] = ['41500', 'Grant Revenue', 'Revenue', 0, '40000'];
            $coa[] = ['53000', 'Program Expenses', 'Expense', 0, '50000'];
            $coa[] = ['54000', 'Fundraising Expenses', 'Expense', 0, '50000'];
        }

        // 4. Insert Accounts and build Code -> Id map
        $map = [];
        $inserted = 0;
        
        $db_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        
        foreach ($coa as $row) {
            [$code, $name, $type, $is_group, $parent_code] = $row;
            
            $parent_id = ($parent_code && isset($map[$parent_code])) ? $map[$parent_code] : null;

            $this->db->insert('acc_accounts', [
                'code'      => $code,
                'name'      => $name,
                'type'      => $type,
                'is_group'  => $is_group,
                'parent_id' => $parent_id,
            ]);
            
            $err = $this->db->error();
            if ($err['code'] !== 0) {
                $this->db->db_debug = $db_debug;
                throw new \Exception("DB Error during insert: " . $err['message'] . " (Code: $code)");
            }

            $new_id = $this->db->insert_id();
            $map[$code] = $new_id;
            $inserted++;
        }
        $this->db->db_debug = $db_debug;

        // 5. Define Settings Map
        $settings_map = [
            'coa_template' => $template,
            'default_bank_account' => '11210',
            'default_cash_account' => '11310',
            'default_receivable_account' => '11110',
            'default_payable_account' => '21110',
            'default_income_account' => '41100',
            'default_expense_account' => '52220',
            'default_cogs_account' => '51110',
            
            'sales_revenue_account' => '41100',
            'sales_discount_account' => '41130',
            'sales_returns_account' => '41120',
            'sales_receivable_account' => '11110',
            'customer_advance_account' => '21400',
            'bad_debt_account' => '52340',
            'revenue_recognition_account' => '21400',
            
            'purchase_account' => '51010',
            'purchase_expense_account' => '52220',
            'purchase_payable_account' => '21110',
            'supplier_advance_account' => '11410',
            'purchase_discount_account' => '42000',
            'accrued_expense_account' => '21300',
            'grni_account' => '21820',
            
            'bank_suspense_account' => '11210',
            'outstanding_receipts_account' => '11210',
            'outstanding_payments_account' => '11210',
            'internal_transfer_account' => '14100',
            'bank_charges_account' => '52110',
            'interest_income_account' => '42110',
            'interest_expense_account' => '52190',
            
            'vat_output_account' => '21530',
            'vat_input_account' => '21520',
            'wht_payable_account' => '21540',
            'wh_vat_payable_account' => '21550',
            'paye_payable_account' => '21210',
            'nssf_payable_account' => '21220',
            'sha_payable_account' => '21230',
            'housing_levy_payable_account' => '21240',
            
            'realized_exchange_gain_account' => '42120',
            'realized_exchange_loss_account' => '52155',
            'unrealized_exchange_gain_account' => '42130',
            'unrealized_exchange_loss_account' => '52156',
            
            'default_write_off_account' => '52340',
            'cash_discount_gain_account' => '42140',
            'cash_discount_loss_account' => '52345',
            'settlement_discount_account' => '52346',
            'rounding_difference_account' => '52270',
            
            'adv_customer_account' => '21400',
            'adv_supplier_account' => '11410',
            'enable_separate_advance_accounts' => '0',
            'advance_reconciliation_method' => 'oldest',
            
            'fa_asset_account' => '12000',
            'fa_accumulated_depreciation_account' => '12100',
            'fa_depreciation_expense_account' => '52130',
            'fa_cwip_account' => '12300',
            'fa_received_not_billed_account' => '21810',
            'fa_disposal_gain_account' => '42000',
            'fa_disposal_loss_account' => '52170',
            'fa_revaluation_reserve_account' => '30500',
            
            'deferred_revenue_account' => '21400',
            'deferred_expense_account' => '11500',

            'payroll_salary_account'       => '52280',
            'payroll_net_payable_account'  => '21250',
            'payroll_nssf_payable_account' => '21220',
            'payroll_paye_payable_account' => '21210',
            'payroll_shif_payable_account' => '21230',
            'payroll_ahl_payable_account'  => '21240',
            'payroll_auto_post'            => '1',

            'allow_budget_override' => '0',
            'budget_warning_threshold' => '90',
            'allow_backdated_entries' => '1',
            'require_approval_before_posting' => '0',
            'auto_generate_journals' => '1',
            'auto_post_journals' => '0',
        ];

        // Override specific defaults depending on template
        if ($template === 'manufacturing') {
            $settings_map['default_income_account'] = '41100';
            $settings_map['sales_revenue_account'] = '41100';
            $settings_map['purchase_account'] = '51200'; // Direct labor
            $settings_map['default_cogs_account'] = '51110';
        } elseif ($template === 'retail') {
            $settings_map['default_income_account'] = '41300'; // Retail sales
            $settings_map['sales_revenue_account'] = '41300';
            $settings_map['default_cogs_account'] = '51115'; // Cost of retail sales
        } elseif ($template === 'service') {
            $settings_map['default_income_account'] = '41210'; // Professional services
            $settings_map['sales_revenue_account'] = '41210';
            $settings_map['default_cogs_account'] = '51210'; // Direct service labor
        } elseif ($template === 'non_profit') {
            $settings_map['default_income_account'] = '41400'; // Donations
            $settings_map['sales_revenue_account'] = '41400';
            $settings_map['default_expense_account'] = '53000'; // Program expense
            $settings_map['default_cogs_account'] = '';
        }

        // Save these settings
        foreach ($settings_map as $key => $code) {
            $this->set_setting($key, $code);
        }

        // Auto-create standard journals (idempotent by code)
        $default_journals = [
            ['code' => 'MISC',    'name' => 'Miscellaneous Operations', 'type' => 'General',  'show_on_dashboard' => 0],
            ['code' => 'BANK',    'name' => 'Bank',                     'type' => 'Bank',     'show_on_dashboard' => 1],
            ['code' => 'CASH',    'name' => 'Cash',                     'type' => 'Cash',     'show_on_dashboard' => 1],
            ['code' => 'INV',     'name' => 'Customer Invoices',        'type' => 'Sale',     'show_on_dashboard' => 1],
            ['code' => 'BILL',    'name' => 'Vendor Bills',             'type' => 'Purchase', 'show_on_dashboard' => 0],
            ['code' => 'PAY',     'name' => 'Payments',                 'type' => 'General',  'show_on_dashboard' => 0],
            ['code' => 'PAYROLL', 'name' => 'Payroll',                  'type' => 'General',  'show_on_dashboard' => 0],
        ];
        foreach ($default_journals as $j) {
            $exists = $this->db->where('code', $j['code'])->count_all_results('acc_journals');
            if (!$exists) {
                $this->db->insert('acc_journals', [
                    'name'              => $j['name'],
                    'type'              => $j['type'],
                    'code'              => $j['code'],
                    'show_on_dashboard' => $j['show_on_dashboard'],
                ]);
            }
        }
        // Save payroll journal ID into settings
        $payroll_journal = $this->db->where('code', 'PAYROLL')->get('acc_journals')->row();
        if ($payroll_journal) {
            $this->set_setting('payroll_journal_id', $payroll_journal->id);
        }

        return $inserted;
    }

    public function delete_account($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('acc_accounts');
    }

    public function get_taxes($type = null)
    {
        if ($type) {
            $this->db->where('type_tax_use', $type);
        }
        $this->db->order_by('sequence', 'ASC');
        return $this->db->get('acc_taxes')->result();
    }

    public function get_taxes_by_ids($ids)
    {
        if (empty($ids)) return [];
        $this->db->where_in('id', $ids);
        return $this->db->get('acc_taxes')->result();
    }

    public function save_tax($data, $id = null)
    {
        if (empty($data['name']) || !isset($data['amount'])) {
            return false;
        }

        $tax_data = [
            'name'         => $data['name'],
            'amount'       => $data['amount'],
            'type_tax_use' => $data['type_tax_use'] ?? 'sale',
            'account_id'   => !empty($data['account_id']) ? $data['account_id'] : null,
        ];

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('acc_taxes', $tax_data);
            return $id;
        } else {
            $this->db->insert('acc_taxes', $tax_data);
            return $this->db->insert_id();
        }
    }

    public function delete_tax($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('acc_taxes');
    }

    public function get_currencies($active_only = false)
    {
        if ($active_only) { $this->db->where('active', 1); }
        $this->db->order_by('id', 'ASC');
        $rows       = $this->db->get('acc_currencies')->result();
        $default_id = (int)($this->get_setting('default_currency') ?: 1);
        foreach ($rows as $row) {
            $row->isdefault = ((int)$row->id === $default_id) ? 1 : 0;
        }
        return $rows;
    }

    /**
     * Sync currencies FROM Perfex native tblcurrencies TO tblacc_currencies.
     * Adds any Perfex currency that doesn't yet exist in acc_currencies.
     * Also updates symbol/full_name if they differ.
     *
     * @return array ['added'=>n, 'updated'=>n]
     */
    public function sync_from_perfex_currencies()
    {
        $p = db_prefix();
        $perfex_currencies = $this->db->get(db_prefix() . 'currencies')->result();
        $added   = 0;
        $updated = 0;

        foreach ($perfex_currencies as $pc) {
            $code = strtoupper(trim($pc->name));
            $existing = $this->db->where('name', $code)->get('acc_currencies')->row();

            if (!$existing) {
                // Add to acc_currencies — rate defaults to 1 (will need manual update)
                $this->db->insert('acc_currencies', [
                    'name'     => $code,
                    'symbol'   => $pc->symbol,
                    'full_name' => $code, // no full_name in tblcurrencies
                    'rate'     => 1.0,
                    'active'   => 1,
                    'position' => $pc->placement ?? 'before',
                ]);
                $added++;
            } else {
                // Update symbol if it differs
                if ($existing->symbol !== $pc->symbol) {
                    $this->db->where('id', $existing->id)->update('acc_currencies', ['symbol' => $pc->symbol]);
                    $updated++;
                }
            }
        }

        return ['added' => $added, 'updated' => $updated];
    }

    /**
     * Sync currencies FROM tblacc_currencies TO Perfex native tblcurrencies.
     * Adds any Xetuu Books currency that doesn't yet exist in tblcurrencies.
     *
     * @return array ['added'=>n]
     */
    public function sync_to_perfex_currencies()
    {
        $p = db_prefix();
        $acc_currencies = $this->db->get('acc_currencies')->result();
        $added = 0;

        foreach ($acc_currencies as $ac) {
            $code = strtoupper(trim($ac->name));
            $existing = $this->db->where('name', $code)->get(db_prefix() . 'currencies')->row();

            if (!$existing) {
                $this->db->insert(db_prefix() . 'currencies', [
                    'name'              => $code,
                    'symbol'            => $ac->symbol ?? $code,
                    'decimal_separator' => '.',
                    'thousand_separator'=> ',',
                    'placement'         => $ac->position ?? 'before',
                    'isdefault'         => 0,
                ]);
                $added++;
            }
        }

        return ['added' => $added];
    }

    /**
     * Seed ALL ISO 4217 world currencies into acc_currencies (skips existing by code).
     * Also seeds them into tblcurrencies if not present.
     *
     * @return array ['acc_added'=>n, 'perfex_added'=>n]
     */
    public function seed_world_currencies()
    {
        // Comprehensive ISO 4217 currency list: [code, symbol, full_name, position]
        $world_currencies = [
            ['AED','د.إ','UAE Dirham','before'],
            ['AFN','؋','Afghan Afghani','before'],
            ['ALL','L','Albanian Lek','before'],
            ['AMD','֏','Armenian Dram','before'],
            ['ANG','ƒ','Netherlands Antillean Guilder','before'],
            ['AOA','Kz','Angolan Kwanza','before'],
            ['ARS','$','Argentine Peso','before'],
            ['AUD','A$','Australian Dollar','before'],
            ['AWG','ƒ','Aruban Florin','before'],
            ['AZN','₼','Azerbaijani Manat','before'],
            ['BAM','KM','Bosnia-Herzegovina Convertible Mark','before'],
            ['BBD','Bds$','Barbadian Dollar','before'],
            ['BDT','৳','Bangladeshi Taka','before'],
            ['BGN','лв','Bulgarian Lev','before'],
            ['BHD','BD','Bahraini Dinar','before'],
            ['BIF','Fr','Burundian Franc','before'],
            ['BMD','$','Bermudian Dollar','before'],
            ['BND','B$','Brunei Dollar','before'],
            ['BOB','Bs.','Bolivian Boliviano','before'],
            ['BRL','R$','Brazilian Real','before'],
            ['BSD','B$','Bahamian Dollar','before'],
            ['BTN','Nu','Bhutanese Ngultrum','before'],
            ['BWP','P','Botswanan Pula','before'],
            ['BYN','Br','Belarusian Ruble','before'],
            ['BZD','BZ$','Belizean Dollar','before'],
            ['CAD','CA$','Canadian Dollar','before'],
            ['CDF','Fr','Congolese Franc','before'],
            ['CHF','Fr','Swiss Franc','before'],
            ['CLP','$','Chilean Peso','before'],
            ['CNY','¥','Chinese Yuan','before'],
            ['COP','$','Colombian Peso','before'],
            ['CRC','₡','Costa Rican Colón','before'],
            ['CUP','$','Cuban Peso','before'],
            ['CVE','$','Cape Verdean Escudo','before'],
            ['CZK','Kč','Czech Koruna','after'],
            ['DJF','Fr','Djiboutian Franc','before'],
            ['DKK','kr','Danish Krone','before'],
            ['DOP','RD$','Dominican Peso','before'],
            ['DZD','دج','Algerian Dinar','before'],
            ['EGP','E£','Egyptian Pound','before'],
            ['ERN','Nfk','Eritrean Nakfa','before'],
            ['ETB','Br','Ethiopian Birr','before'],
            ['EUR','€','Euro','before'],
            ['FJD','FJ$','Fijian Dollar','before'],
            ['FKP','£','Falkland Islands Pound','before'],
            ['GBP','£','British Pound Sterling','before'],
            ['GEL','₾','Georgian Lari','before'],
            ['GHS','GH₵','Ghanaian Cedi','before'],
            ['GIP','£','Gibraltar Pound','before'],
            ['GMD','D','Gambian Dalasi','before'],
            ['GNF','Fr','Guinean Franc','before'],
            ['GTQ','Q','Guatemalan Quetzal','before'],
            ['GYD','$','Guyanese Dollar','before'],
            ['HKD','HK$','Hong Kong Dollar','before'],
            ['HNL','L','Honduran Lempira','before'],
            ['HRK','kn','Croatian Kuna','before'],
            ['HTG','G','Haitian Gourde','before'],
            ['HUF','Ft','Hungarian Forint','after'],
            ['IDR','Rp','Indonesian Rupiah','before'],
            ['ILS','₪','Israeli New Shekel','before'],
            ['INR','₹','Indian Rupee','before'],
            ['IQD','ع.د','Iraqi Dinar','before'],
            ['IRR','﷼','Iranian Rial','before'],
            ['ISK','kr','Icelandic Króna','after'],
            ['JMD','J$','Jamaican Dollar','before'],
            ['JOD','JD','Jordanian Dinar','before'],
            ['JPY','¥','Japanese Yen','before'],
            ['KES','KSh','Kenyan Shilling','before'],
            ['KGS','с','Kyrgyzstani Som','before'],
            ['KHR','៛','Cambodian Riel','before'],
            ['KMF','Fr','Comorian Franc','before'],
            ['KPW','₩','North Korean Won','before'],
            ['KRW','₩','South Korean Won','before'],
            ['KWD','KD','Kuwaiti Dinar','before'],
            ['KYD','$','Cayman Islands Dollar','before'],
            ['KZT','₸','Kazakhstani Tenge','before'],
            ['LAK','₭','Laotian Kip','before'],
            ['LBP','L£','Lebanese Pound','before'],
            ['LKR','₨','Sri Lankan Rupee','before'],
            ['LRD','$','Liberian Dollar','before'],
            ['LSL','L','Lesotho Loti','before'],
            ['LYD','LD','Libyan Dinar','before'],
            ['MAD','MAD','Moroccan Dirham','before'],
            ['MDL','L','Moldovan Leu','before'],
            ['MGA','Ar','Malagasy Ariary','before'],
            ['MKD','ден','Macedonian Denar','before'],
            ['MMK','K','Myanmar Kyat','before'],
            ['MNT','₮','Mongolian Tögrög','before'],
            ['MOP','P','Macanese Pataca','before'],
            ['MRU','UM','Mauritanian Ouguiya','before'],
            ['MUR','₨','Mauritian Rupee','before'],
            ['MVR','Rf','Maldivian Rufiyaa','before'],
            ['MWK','MK','Malawian Kwacha','before'],
            ['MXN','$','Mexican Peso','before'],
            ['MYR','RM','Malaysian Ringgit','before'],
            ['MZN','MT','Mozambican Metical','before'],
            ['NAD','N$','Namibian Dollar','before'],
            ['NGN','₦','Nigerian Naira','before'],
            ['NIO','C$','Nicaraguan Córdoba','before'],
            ['NOK','kr','Norwegian Krone','before'],
            ['NPR','₨','Nepalese Rupee','before'],
            ['NZD','NZ$','New Zealand Dollar','before'],
            ['OMR','ر.ع.','Omani Rial','before'],
            ['PAB','B/.','Panamanian Balboa','before'],
            ['PEN','S/.','Peruvian Sol','before'],
            ['PGK','K','Papua New Guinean Kina','before'],
            ['PHP','₱','Philippine Peso','before'],
            ['PKR','₨','Pakistani Rupee','before'],
            ['PLN','zł','Polish Złoty','after'],
            ['PYG','₲','Paraguayan Guaraní','before'],
            ['QAR','ر.ق','Qatari Rial','before'],
            ['RON','lei','Romanian Leu','after'],
            ['RSD','din','Serbian Dinar','after'],
            ['RUB','₽','Russian Ruble','before'],
            ['RWF','RF','Rwandan Franc','before'],
            ['SAR','ر.س','Saudi Riyal','before'],
            ['SBD','$','Solomon Islands Dollar','before'],
            ['SCR','₨','Seychellois Rupee','before'],
            ['SDG','ج.س.','Sudanese Pound','before'],
            ['SEK','kr','Swedish Krona','after'],
            ['SGD','S$','Singapore Dollar','before'],
            ['SHP','£','Saint Helenian Pound','before'],
            ['SLL','Le','Sierra Leonean Leone','before'],
            ['SOS','Sh','Somali Shilling','before'],
            ['SRD','$','Surinamese Dollar','before'],
            ['SSP','£','South Sudanese Pound','before'],
            ['STN','Db','São Tomé & Príncipe Dobra','before'],
            ['SVC','₡','Salvadoran Colón','before'],
            ['SYP','£','Syrian Pound','before'],
            ['SZL','L','Swazi Lilangeni','before'],
            ['THB','฿','Thai Baht','before'],
            ['TJS','SM','Tajikistani Somoni','before'],
            ['TMT','T','Turkmenistani Manat','before'],
            ['TND','DT','Tunisian Dinar','before'],
            ['TOP','T$','Tongan Paʻanga','before'],
            ['TRY','₺','Turkish Lira','before'],
            ['TTD','TT$','Trinidad & Tobago Dollar','before'],
            ['TWD','NT$','New Taiwan Dollar','before'],
            ['TZS','TSh','Tanzanian Shilling','before'],
            ['UAH','₴','Ukrainian Hryvnia','before'],
            ['UGX','USh','Ugandan Shilling','before'],
            ['USD','$','US Dollar','before'],
            ['UYU','$U','Uruguayan Peso','before'],
            ['UZS','сум','Uzbekistani Som','before'],
            ['VES','Bs.S','Venezuelan Bolívar','before'],
            ['VND','₫','Vietnamese Dong','after'],
            ['VUV','Vt','Vanuatu Vatu','before'],
            ['WST','T','Samoan Tala','before'],
            ['XAF','Fr','Central African CFA Franc','before'],
            ['XCD','$','East Caribbean Dollar','before'],
            ['XOF','Fr','West African CFA Franc','before'],
            ['XPF','Fr','CFP Franc','before'],
            ['YER','﷼','Yemeni Rial','before'],
            ['ZAR','R','South African Rand','before'],
            ['ZMW','ZK','Zambian Kwacha','before'],
            ['ZWL','$','Zimbabwean Dollar','before'],
        ];

        $acc_added    = 0;
        $perfex_added = 0;

        // Get existing codes in acc_currencies
        $existing_acc = [];
        foreach ($this->db->select('name')->get('acc_currencies')->result() as $r) {
            $existing_acc[strtoupper($r->name)] = true;
        }

        // Get existing codes in tblcurrencies
        $existing_perfex = [];
        foreach ($this->db->select('name')->get(db_prefix() . 'currencies')->result() as $r) {
            $existing_perfex[strtoupper($r->name)] = true;
        }

        foreach ($world_currencies as [$code, $symbol, $full_name, $position]) {
            // Add to acc_currencies if missing
            if (!isset($existing_acc[$code])) {
                $this->db->insert('acc_currencies', [
                    'name'      => $code,
                    'symbol'    => $symbol,
                    'full_name' => $full_name,
                    'rate'      => 1.0,
                    'active'    => 1,
                    'position'  => $position,
                ]);
                $acc_added++;
            }

            // Add to tblcurrencies if missing
            if (!isset($existing_perfex[$code])) {
                $this->db->insert(db_prefix() . 'currencies', [
                    'name'              => $code,
                    'symbol'            => $symbol,
                    'decimal_separator' => '.',
                    'thousand_separator'=> ',',
                    'placement'         => $position,
                    'isdefault'         => 0,
                ]);
                $perfex_added++;
            }
        }

        return ['acc_added' => $acc_added, 'perfex_added' => $perfex_added];
    }

    /**
     * When a currency rate is updated in acc_currencies, the update_at timestamp is already
     * set by the DB trigger. No equivalent rate field in tblcurrencies (it only has symbol/name).
     *
     * When a currency symbol is updated in tblcurrencies, sync that symbol to acc_currencies.
     * Called via hook: 'after_currency_updated' from Perfex core.
     */
    public function sync_perfex_currency_update($currency_id)
    {
        $pc = $this->db->where('id', $currency_id)->get(db_prefix() . 'currencies')->row();
        if (!$pc) return false;

        $code = strtoupper(trim($pc->name));
        $existing = $this->db->where('name', $code)->get('acc_currencies')->row();

        if ($existing) {
            $this->db->where('id', $existing->id)->update('acc_currencies', [
                'symbol'   => $pc->symbol,
                'position' => $pc->placement ?? 'before',
            ]);
        } else {
            // New currency added in Perfex — add to acc_currencies too
            $this->db->insert('acc_currencies', [
                'name'      => $code,
                'symbol'    => $pc->symbol,
                'full_name' => $code,
                'rate'      => 1.0,
                'active'    => 1,
                'position'  => $pc->placement ?? 'before',
            ]);
        }
        return true;
    }

    /**
     * Step 22: Fetch live exchange rates from exchangerate-api.com (free, no API key).
     * Rates stored as "units of base currency per 1 unit of foreign currency".
     * Base currency = KES (id=1) or whatever currency has rate=1.
     *
     * Returns ['updated' => n, 'skipped' => n, 'error' => string|null]
     */
    public function fetch_exchange_rates()
    {
        $base = 'USD';
        $url  = 'https://api.exchangerate-api.com/v4/latest/' . $base;

        $json = $this->_http_get($url);
        if (!$json) {
            return ['updated' => 0, 'skipped' => 0, 'error' => 'Could not reach exchange rate API. Check internet connection.'];
        }

        $payload = json_decode($json, true);
        if (empty($payload['rates'])) {
            return ['updated' => 0, 'skipped' => 0, 'error' => 'Invalid response from exchange rate API.'];
        }

        $api_rates = $payload['rates'];
        $kes_rate  = isset($api_rates['KES']) ? (float)$api_rates['KES'] : 0;
        if ($kes_rate <= 0) {
            return ['updated' => 0, 'skipped' => 0, 'error' => 'KES not found in API response.'];
        }

        $currencies = $this->db->get('acc_currencies')->result();
        $updated = 0;
        $skipped = 0;

        foreach ($currencies as $cur) {
            $code = strtoupper(trim($cur->name));
            if (!isset($api_rates[$code])) { $skipped++; continue; }

            // Rate = how many KES per 1 unit of this currency
            $new_rate = ($code === 'KES') ? 1 : round($kes_rate / (float)$api_rates[$code], 6);
            if ($new_rate <= 0) { $skipped++; continue; }

            $this->db->where('id', $cur->id)->update('acc_currencies', ['rate' => $new_rate]);
            $updated++;
        }

        // Log the update
        $this->set_setting('exchange_rates_last_updated', date('Y-m-d H:i:s'));

        return ['updated' => $updated, 'skipped' => $skipped, 'error' => null, 'base' => $base, 'date' => $payload['date'] ?? date('Y-m-d')];
    }

    private function _http_get($url)
    {
        // Try curl first (more reliable in WAMP/IIS environments)
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'XetuuBooks/1.0',
            ]);
            $body = curl_exec($ch);
            $err  = curl_errno($ch);
            curl_close($ch);
            if (!$err && $body) return $body;
        }

        // Fallback: file_get_contents
        if (ini_get('allow_url_fopen')) {
            $ctx  = stream_context_create(['http' => ['timeout' => 10]]);
            $body = @file_get_contents($url, false, $ctx);
            if ($body) return $body;
        }

        return null;
    }

    public function get_payment_terms($with_lines = false)
    {
        $this->db->order_by('sequence', 'ASC');
        $terms = $this->db->get('acc_payment_terms')->result();
        
        if ($with_lines) {
            foreach ($terms as $term) {
                $this->db->where('payment_term_id', $term->id);
                $term->lines = $this->db->get('acc_payment_term_lines')->result();
            }
        }
        
        return $terms;
    }

    public function save_payment_term($data, $id = null)
    {
        if (empty($data['name'])) return false;

        $term_data = ['name' => $data['name']];

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('acc_payment_terms', $term_data);
        } else {
            $this->db->insert('acc_payment_terms', $term_data);
            $id = $this->db->insert_id();
        }

        return $id;
    }

    public function get_fiscal_years()
    {
        return $this->db->get('acc_fiscal_years')->result();
    }

    public function get_lock_dates()
    {
        return $this->db->get('acc_lock_dates')->row();
    }

    public function search_partners($q, $type = 'any')
    {
        $data = [];

        // Search Perfex CRM clients (tblclients)
        if ($type !== 'vendor') {
            $this->db->select('userid as id, company as name');
            $this->db->from('tblclients');
            $this->db->where('active', 1);
            if ($q) {
                $this->db->group_start();
                $this->db->like('company', $q);
                $this->db->group_end();
            }
            $this->db->limit(15);
            $clients = $this->db->get()->result();
            foreach ($clients as $c) {
                $data[] = ['id' => 'c_' . $c->id, 'text' => $c->name];
            }
        }

        // Search Perfex CRM contacts (tblcontacts) — can act as vendors
        if ($type !== 'customer') {
            $this->db->select('id, CONCAT(firstname, \' \', lastname) as name, email');
            $this->db->from('tblcontacts');
            if ($q) {
                $this->db->group_start();
                $this->db->like('firstname', $q);
                $this->db->or_like('lastname', $q);
                $this->db->or_like('email', $q);
                $this->db->group_end();
            }
            $this->db->where('active', 1);
            $this->db->limit(10);
            $contacts = $this->db->get()->result();
            foreach ($contacts as $c) {
                $data[] = ['id' => 'p_' . $c->id, 'text' => $c->name . ($c->email ? ' (' . $c->email . ')' : '')];
            }
        }

        return array_slice($data, 0, 20);
    }
}
