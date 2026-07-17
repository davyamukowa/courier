<?php
$host = 'localhost';
$db   = 'perfex_crm';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$prefix = 'tbl'; // Default Perfex prefix, usually 'tbl'

// Let's get the actual prefix from app-config.php if possible
$config_file = __DIR__ . '/../../application/config/app-config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    if (preg_match('/\$db_prefix\s*=\s*[\'"]([^\'"]+)[\'"]/', $config_content, $matches)) {
        $prefix = $matches[1];
    }
}

echo "Using prefix: $prefix\n";

// Clear existing accounts and related to start fresh
$pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_move_lines;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_moves;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_accounts;");
$pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

$accounts_data = [
    ["name" => "Application of Funds (Assets) - TK", "type" => "Asset", "is_group" => 1, "parent" => null, "dr" => 0, "cr" => 0],
    ["name" => "Current Assets - TK", "type" => "Current Asset", "is_group" => 1, "parent" => "Application of Funds (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Accounts Receivable - TK", "type" => "Receivable", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Debtors - TK", "type" => "Receivable", "is_group" => 0, "parent" => "Accounts Receivable - TK", "dr" => 0, "cr" => 0],
    ["name" => "Bank Accounts - TK", "type" => "Bank", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Cash In Hand - TK", "type" => "Cash", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Cash - TK", "type" => "Cash", "is_group" => 0, "parent" => "Cash In Hand - TK", "dr" => 141850, "cr" => 0],
    ["name" => "Loans and Advances (Assets) - TK", "type" => "Current Asset", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Employee Advances - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Loans and Advances (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Prepaid Expenses - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Loans and Advances (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Securities and Deposits - TK", "type" => "Current Asset", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Earnest Money - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Securities and Deposits - TK", "dr" => 0, "cr" => 0],
    ["name" => "Short-term Investments - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock Assets - TK", "type" => "Current Asset", "is_group" => 1, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock In Hand - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Stock Assets - TK", "dr" => 24083800, "cr" => 0],
    ["name" => "Tax Assets - TK", "type" => "Current Asset", "is_group" => 0, "parent" => "Current Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Fixed Assets - TK", "type" => "Fixed Asset", "is_group" => 1, "parent" => "Application of Funds (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Accumulated Depreciation - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Buildings - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "CWIP Account - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Capital Equipment - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Electronic Equipment - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Furniture and Fixtures - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Office Equipment - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Plants and Machineries - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Software - TK", "type" => "Fixed Asset", "is_group" => 0, "parent" => "Fixed Assets - TK", "dr" => 0, "cr" => 0],
    ["name" => "Investments - TK", "type" => "Asset", "is_group" => 0, "parent" => "Application of Funds (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Temporary Accounts - TK", "type" => "Asset", "is_group" => 1, "parent" => "Application of Funds (Assets) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Temporary Opening - TK", "type" => "Asset", "is_group" => 0, "parent" => "Temporary Accounts - TK", "dr" => 0, "cr" => 0],
    
    ["name" => "Source of Funds (Liabilities) - TK", "type" => "Liability", "is_group" => 1, "parent" => null, "dr" => 0, "cr" => 0],
    ["name" => "Current Liabilities - TK", "type" => "Current Liability", "is_group" => 1, "parent" => "Source of Funds (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Accounts Payable - TK", "type" => "Payable", "is_group" => 1, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Creditors - TK", "type" => "Payable", "is_group" => 0, "parent" => "Accounts Payable - TK", "dr" => 0, "cr" => 0],
    ["name" => "Payroll Payable - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Accrued Expenses - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Customer Advances - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Duties and Taxes - TK", "type" => "Tax", "is_group" => 1, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "VAT - TK", "type" => "Tax", "is_group" => 0, "parent" => "Duties and Taxes - TK", "dr" => 0, "cr" => 0],
    ["name" => "Loans (Liabilities) - TK", "type" => "Current Liability", "is_group" => 1, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Bank Overdraft Account - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Loans (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Secured Loans - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Loans (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Unsecured Loans - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Loans (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Short-term Provisions - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock Liabilities - TK", "type" => "Current Liability", "is_group" => 1, "parent" => "Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Asset Received But Not Billed - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Stock Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock Received But Not Billed - TK", "type" => "Current Liability", "is_group" => 0, "parent" => "Stock Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Non-Current Liabilities - TK", "type" => "Long-term Liability", "is_group" => 1, "parent" => "Source of Funds (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Employee Benefits Obligation - TK", "type" => "Long-term Liability", "is_group" => 0, "parent" => "Non-Current Liabilities - TK", "dr" => 0, "cr" => 0],
    ["name" => "Long-term Provisions - TK", "type" => "Long-term Liability", "is_group" => 0, "parent" => "Non-Current Liabilities - TK", "dr" => 0, "cr" => 0],
    
    ["name" => "Equity - TK", "type" => "Equity", "is_group" => 1, "parent" => "Source of Funds (Liabilities) - TK", "dr" => 0, "cr" => 0],
    ["name" => "Capital Stock - TK", "type" => "Equity", "is_group" => 0, "parent" => "Equity - TK", "dr" => 0, "cr" => 0],
    ["name" => "Dividends Paid - TK", "type" => "Equity", "is_group" => 0, "parent" => "Equity - TK", "dr" => 0, "cr" => 0],
    ["name" => "Opening Balance Equity - TK", "type" => "Equity", "is_group" => 0, "parent" => "Equity - TK", "dr" => 0, "cr" => 0],
    ["name" => "Retained Earnings - TK", "type" => "Equity", "is_group" => 0, "parent" => "Equity - TK", "dr" => 0, "cr" => 0],
    ["name" => "Revaluation Surplus - TK", "type" => "Equity", "is_group" => 0, "parent" => "Equity - TK", "dr" => 0, "cr" => 0],
    
    ["name" => "Income - TK", "type" => "Revenue", "is_group" => 1, "parent" => null, "dr" => 0, "cr" => 0],
    ["name" => "Direct Income - TK", "type" => "Revenue", "is_group" => 1, "parent" => "Income - TK", "dr" => 0, "cr" => 0],
    ["name" => "Sales - TK", "type" => "Revenue", "is_group" => 0, "parent" => "Direct Income - TK", "dr" => 0, "cr" => 141850],
    ["name" => "Service - TK", "type" => "Revenue", "is_group" => 0, "parent" => "Direct Income - TK", "dr" => 0, "cr" => 0],
    ["name" => "Indirect Income - TK", "type" => "Other Income", "is_group" => 1, "parent" => "Income - TK", "dr" => 0, "cr" => 0],
    ["name" => "Interest Income - TK", "type" => "Other Income", "is_group" => 1, "parent" => "Indirect Income - TK", "dr" => 0, "cr" => 0],
    ["name" => "Interest on Fixed Deposits - TK", "type" => "Other Income", "is_group" => 0, "parent" => "Interest Income - TK", "dr" => 0, "cr" => 0],
    
    ["name" => "Expenses - TK", "type" => "Expense", "is_group" => 1, "parent" => null, "dr" => 0, "cr" => 0],
    ["name" => "Direct Expenses - TK", "type" => "Expense", "is_group" => 1, "parent" => "Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock Expenses - TK", "type" => "Expense", "is_group" => 1, "parent" => "Direct Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Cost of Goods Sold - TK", "type" => "Cost of Revenue", "is_group" => 0, "parent" => "Stock Expenses - TK", "dr" => 142200, "cr" => 0],
    ["name" => "Expenses Included In Asset Valuation - TK", "type" => "Expense", "is_group" => 1, "parent" => "Direct Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Expenses Included In Valuation - TK", "type" => "Expense", "is_group" => 0, "parent" => "Expenses Included In Asset Valuation - TK", "dr" => 0, "cr" => 0],
    ["name" => "Stock Adjustment - TK", "type" => "Expense", "is_group" => 0, "parent" => "Direct Expenses - TK", "dr" => 0, "cr" => 24226000],
    ["name" => "Indirect Expenses - TK", "type" => "Other Expense", "is_group" => 1, "parent" => "Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Administrative Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Bank Charges - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Commission on Sales - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Depreciation - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Entertainment Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Exchange Gain/Loss - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Freight and Forwarding Charges - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Gain/Loss on Asset Disposal - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Impairment - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Interest Expense - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Legal Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Marketing Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Miscellaneous Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Office Maintenance Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Office Rent - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Postal Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Print and Stationery - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Round Off - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Salary - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Sales Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Tax Expense - TK", "type" => "Tax", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Telephone Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Travel Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Utility Expenses - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0],
    ["name" => "Write Off - TK", "type" => "Other Expense", "is_group" => 0, "parent" => "Indirect Expenses - TK", "dr" => 0, "cr" => 0]
];

$account_map = [];

// Insert accounts
$stmt = $pdo->prepare("INSERT INTO {$prefix}acc_accounts (code, name, type, is_group, parent_id) VALUES (?, ?, ?, ?, ?)");
$code_counter = 1000;

foreach ($accounts_data as $acc) {
    $parent_id = null;
    if ($acc['parent']) {
        $parent_id = $account_map[$acc['parent']] ?? null;
    }
    
    $code = str_pad($code_counter++, 4, '0', STR_PAD_LEFT);
    $stmt->execute([
        $code,
        $acc['name'],
        $acc['type'],
        $acc['is_group'],
        $parent_id
    ]);
    
    $id = $pdo->lastInsertId();
    $account_map[$acc['name']] = $id;
}

echo "Inserted " . count($accounts_data) . " accounts.\n";

// Ensure a general journal exists
$stmt = $pdo->prepare("SELECT id FROM {$prefix}acc_journals WHERE code = 'MISC' LIMIT 1");
$stmt->execute();
$journal_id = $stmt->fetchColumn();

if (!$journal_id) {
    $pdo->exec("INSERT INTO {$prefix}acc_journals (name, type, code) VALUES ('Miscellaneous', 'General', 'MISC')");
    $journal_id = $pdo->lastInsertId();
}

// Create Opening Balance Move
$date = date('Y-m-d');
$pdo->exec("INSERT INTO {$prefix}acc_moves (name, move_type, state, journal_id, date, narration) VALUES ('Opening Balance', 'entry', 'posted', $journal_id, '$date', 'Initial Setup Balances')");
$move_id = $pdo->lastInsertId();

$stmt_line = $pdo->prepare("INSERT INTO {$prefix}acc_move_lines (move_id, account_id, name, debit, credit, balance) VALUES (?, ?, ?, ?, ?, ?)");

$total_dr = 0;
$total_cr = 0;

foreach ($accounts_data as $acc) {
    if ($acc['dr'] > 0 || $acc['cr'] > 0) {
        $id = $account_map[$acc['name']];
        $dr = $acc['dr'];
        $cr = $acc['cr'];
        $bal = $dr - $cr;
        $stmt_line->execute([$move_id, $id, 'Opening Balance', $dr, $cr, $bal]);
        
        $total_dr += $dr;
        $total_cr += $cr;
    }
}

// If unbalanced, post to Opening Balance Equity - TK
$diff = $total_dr - $total_cr;
if ($diff != 0) {
    $equity_id = $account_map['Opening Balance Equity - TK'];
    if ($diff > 0) {
        // More Dr, need Cr
        $stmt_line->execute([$move_id, $equity_id, 'Opening Balance Adjustment', 0, $diff, -$diff]);
    } else {
        // More Cr, need Dr
        $stmt_line->execute([$move_id, $equity_id, 'Opening Balance Adjustment', abs($diff), 0, abs($diff)]);
    }
}

echo "Opening balances seeded!\n";
