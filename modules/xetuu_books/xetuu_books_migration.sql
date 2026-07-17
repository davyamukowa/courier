-- CHART OF ACCOUNTS
CREATE TABLE IF NOT EXISTS acc_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL UNIQUE,
  name VARCHAR(200) NOT NULL,
  type ENUM(
    'Asset','Current Asset','Fixed Asset','Bank','Cash',
    'Liability','Current Liability','Long-term Liability',
    'Equity','Revenue','Cost of Revenue','Expense',
    'Other Income','Other Expense','Tax','Receivable','Payable'
  ) NOT NULL,
  currency_id INT DEFAULT 1,
  parent_id INT,
  is_group TINYINT(1) DEFAULT 0,
  allow_reconciliation TINYINT(1) DEFAULT 0,
  deprecated TINYINT(1) DEFAULT 0,
  company_id INT DEFAULT 1,
  account_category VARCHAR(100) DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- JOURNALS
CREATE TABLE IF NOT EXISTS acc_journals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  type ENUM('Sale','Purchase','Cash','Bank','General','Situation') NOT NULL,
  code VARCHAR(10) UNIQUE NOT NULL,
  account_id INT,
  suspense_account_id INT,
  profit_account_id INT,
  loss_account_id INT,
  currency_id INT DEFAULT 1,
  bank_account_id INT,
  sequence_id INT,
  dedicated_refund_sequence TINYINT(1) DEFAULT 0,
  lock_posted_entries TINYINT(1) DEFAULT 0,
  restrict_mode_hash_table TINYINT(1) DEFAULT 0,
  show_on_dashboard TINYINT(1) DEFAULT 1,
  color VARCHAR(20),
  sequence INT DEFAULT 10,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- JOURNAL ENTRIES (MOVES)
CREATE TABLE IF NOT EXISTS acc_moves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  move_type ENUM(
    'entry','out_invoice','out_refund','in_invoice','in_refund',
    'out_receipt','in_receipt'
  ) DEFAULT 'entry',
  state ENUM('draft','posted','cancel') DEFAULT 'draft',
  journal_id INT NOT NULL,
  date DATE NOT NULL,
  invoice_date DATE,
  invoice_date_due DATE,
  partner_id INT,
  partner_type ENUM('customer','vendor'),
  currency_id INT DEFAULT 1,
  exchange_rate DECIMAL(15,6) DEFAULT 1,
  amount_untaxed DECIMAL(15,4) DEFAULT 0,
  amount_tax DECIMAL(15,4) DEFAULT 0,
  amount_total DECIMAL(15,4) DEFAULT 0,
  amount_residual DECIMAL(15,4) DEFAULT 0,
  payment_state ENUM(
    'not_paid','in_payment','paid','partial','reversed','invoicing_legacy'
  ) DEFAULT 'not_paid',
  invoice_origin VARCHAR(200),
  ref VARCHAR(200),
  narration TEXT,
  fiscal_position_id INT,
  invoice_payment_term_id INT,
  reversed_entry_id INT,
  auto_post ENUM('no','monthly','quarterly','annually') DEFAULT 'no',
  auto_post_until DATE,
  sequence_prefix VARCHAR(50),
  quick_edit_mode TINYINT(1) DEFAULT 0,
  extract_state VARCHAR(50),
  created_by INT,
  updated_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_state (state),
  INDEX idx_type (move_type),
  INDEX idx_partner (partner_id),
  INDEX idx_date (date),
  INDEX idx_journal (journal_id)
);

-- JOURNAL ENTRY LINES
CREATE TABLE IF NOT EXISTS acc_move_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  move_id INT NOT NULL,
  sequence INT DEFAULT 10,
  account_id INT NOT NULL,
  partner_id INT,
  name VARCHAR(500),
  ref VARCHAR(200),
  quantity DECIMAL(15,4) DEFAULT 1,
  price_unit DECIMAL(15,6) DEFAULT 0,
  discount DECIMAL(5,2) DEFAULT 0,
  price_subtotal DECIMAL(15,4) DEFAULT 0,
  price_total DECIMAL(15,4) DEFAULT 0,
  debit DECIMAL(15,4) DEFAULT 0,
  credit DECIMAL(15,4) DEFAULT 0,
  balance DECIMAL(15,4) DEFAULT 0,
  amount_currency DECIMAL(15,4) DEFAULT 0,
  currency_id INT DEFAULT 1,
  tax_ids VARCHAR(500),
  tax_line_id INT,
  analytic_distribution TEXT,
  product_id INT,
  product_uom_id INT,
  date DATE,
  date_maturity DATE,
  reconciled TINYINT(1) DEFAULT 0,
  full_reconcile_id INT,
  matching_number VARCHAR(100),
  blocked TINYINT(1) DEFAULT 0,
  payment_id INT,
  statement_line_id INT,
  tax_base_amount DECIMAL(15,4) DEFAULT 0,
  display_type ENUM('product','tax','payment_term','line_section','line_note',''),
  FOREIGN KEY (move_id) REFERENCES acc_moves(id) ON DELETE CASCADE,
  INDEX idx_account (account_id),
  INDEX idx_move (move_id),
  INDEX idx_reconciled (reconciled)
);

-- TAXES
CREATE TABLE IF NOT EXISTS acc_taxes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  type_tax_use ENUM('sale','purchase','none','adjustment') DEFAULT 'sale',
  tax_scope ENUM('consu','service','') DEFAULT '',
  amount_type ENUM('percent','fixed','division','group','code') DEFAULT 'percent',
  amount DECIMAL(10,4) DEFAULT 0,
  price_include TINYINT(1) DEFAULT 0,
  include_base_amount TINYINT(1) DEFAULT 0,
  is_base_affected TINYINT(1) DEFAULT 1,
  account_id INT,
  refund_account_id INT,
  description VARCHAR(100),
  invoice_label VARCHAR(100),
  active TINYINT(1) DEFAULT 1,
  sequence INT DEFAULT 1,
  tax_group_id INT,
  country_id INT,
  fiscal_country_codes VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TAX GROUPS
CREATE TABLE IF NOT EXISTS acc_tax_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  sequence INT DEFAULT 10,
  tax_payable_account_id INT,
  tax_receivable_account_id INT,
  advance_tax_payment_account_id INT,
  country_id INT
);

-- PAYMENT TERMS
CREATE TABLE IF NOT EXISTS acc_payment_terms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  note TEXT,
  active TINYINT(1) DEFAULT 1,
  sequence INT DEFAULT 10,
  company_id INT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS acc_payment_term_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_term_id INT NOT NULL,
  value ENUM('balance','percent','fixed') DEFAULT 'balance',
  value_amount DECIMAL(10,4) DEFAULT 0,
  delay_type ENUM('days_after','days_after_end_of_month',
                  'days_after_end_of_next_month','days_end_of_month_on_the') DEFAULT 'days_after',
  nb_days INT DEFAULT 0,
  FOREIGN KEY (payment_term_id) REFERENCES acc_payment_terms(id) ON DELETE CASCADE
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS acc_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  state ENUM('draft','posted','cancelled','sent','reconciled') DEFAULT 'draft',
  payment_type ENUM('outbound','inbound') NOT NULL,
  partner_type ENUM('customer','vendor') NOT NULL,
  partner_id INT,
  journal_id INT NOT NULL,
  currency_id INT DEFAULT 1,
  amount DECIMAL(15,4) NOT NULL,
  date DATE NOT NULL,
  ref VARCHAR(200),
  memo VARCHAR(500),
  destination_account_id INT,
  destination_journal_id INT,
  payment_method_line_id INT,
  is_reconciled TINYINT(1) DEFAULT 0,
  is_matched TINYINT(1) DEFAULT 0,
  move_id INT,
  paired_internal_transfer_payment_id INT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_partner (partner_id),
  INDEX idx_state (state)
);

-- BANK ACCOUNTS
CREATE TABLE IF NOT EXISTS acc_bank_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  account_number VARCHAR(100),
  bank_name VARCHAR(200),
  bank_bic VARCHAR(50),
  currency_id INT DEFAULT 1,
  journal_id INT,
  partner_id INT,
  account_id INT,
  active TINYINT(1) DEFAULT 1,
  show_on_dashboard TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BANK STATEMENT LINES
CREATE TABLE IF NOT EXISTS acc_bank_statement_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  journal_id INT NOT NULL,
  date DATE NOT NULL,
  payment_ref VARCHAR(500),
  ref VARCHAR(200),
  partner_id INT,
  partner_name VARCHAR(200),
  amount DECIMAL(15,4) NOT NULL,
  amount_currency DECIMAL(15,4) DEFAULT 0,
  currency_id INT DEFAULT 1,
  running_balance DECIMAL(15,4) DEFAULT 0,
  is_reconciled TINYINT(1) DEFAULT 0,
  move_id INT,
  statement_id INT,
  sequence INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- RECONCILIATION
CREATE TABLE IF NOT EXISTS acc_full_reconcile (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE,
  exchange_move_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS acc_partial_reconcile (
  id INT AUTO_INCREMENT PRIMARY KEY,
  debit_move_id INT NOT NULL,
  credit_move_id INT NOT NULL,
  full_reconcile_id INT,
  amount DECIMAL(15,4) NOT NULL,
  amount_currency DECIMAL(15,4) DEFAULT 0,
  currency_id INT DEFAULT 1,
  max_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CURRENCIES
CREATE TABLE IF NOT EXISTS acc_currencies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(10) UNIQUE NOT NULL,
  symbol VARCHAR(10),
  full_name VARCHAR(100),
  rate DECIMAL(20,8) DEFAULT 1,
  rate_date DATE,
  position ENUM('before','after') DEFAULT 'before',
  active TINYINT(1) DEFAULT 1,
  rounding DECIMAL(10,6) DEFAULT 0.01,
  decimal_places INT DEFAULT 2,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO acc_currencies (id, name, symbol, full_name, rate, position) VALUES
(1, 'KES', 'KSh', 'Kenyan Shilling', 1, 'before'),
(2, 'USD', '$', 'US Dollar', 130.5, 'before'),
(3, 'EUR', '€', 'Euro', 142.0, 'before'),
(4, 'UGX', 'USh', 'Ugandan Shilling', 0.035, 'before'),
(5, 'TZS', 'TSh', 'Tanzanian Shilling', 0.052, 'before'),
(6, 'RWF', 'RF', 'Rwandan Franc', 0.091, 'before'),
(7, 'ETB', 'Br', 'Ethiopian Birr', 1.15, 'before');

-- FISCAL YEARS & PERIODS
CREATE TABLE IF NOT EXISTS acc_fiscal_years (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  state ENUM('open','close') DEFAULT 'open',
  company_id INT DEFAULT 1
);

-- LOCK DATES
CREATE TABLE IF NOT EXISTS acc_lock_dates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT DEFAULT 1 UNIQUE,
  period_lock_date DATE,
  fiscalyear_lock_date DATE,
  tax_lock_date DATE,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ANALYTIC ACCOUNTS
CREATE TABLE IF NOT EXISTS acc_analytic_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  parent_id INT,
  default_applicability ENUM('optional','mandatory','unavailable') DEFAULT 'optional',
  applicability_ids TEXT,
  color VARCHAR(20),
  sequence INT DEFAULT 10,
  active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS acc_analytic_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  code VARCHAR(50),
  plan_id INT NOT NULL,
  partner_id INT,
  currency_id INT DEFAULT 1,
  active TINYINT(1) DEFAULT 1,
  balance DECIMAL(15,4) DEFAULT 0,
  debit DECIMAL(15,4) DEFAULT 0,
  credit DECIMAL(15,4) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (plan_id) REFERENCES acc_analytic_plans(id) ON DELETE CASCADE
);

-- BUDGETS
CREATE TABLE IF NOT EXISTS acc_budgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  state ENUM('draft','confirm','validate','done','cancel') DEFAULT 'draft',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS acc_budget_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  budget_id INT NOT NULL,
  analytic_account_id INT,
  account_id INT,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  planned_amount DECIMAL(15,4) DEFAULT 0,
  practical_amount DECIMAL(15,4) DEFAULT 0,
  percentage DECIMAL(10,2) DEFAULT 0,
  FOREIGN KEY (budget_id) REFERENCES acc_budgets(id) ON DELETE CASCADE
);

-- ASSETS
CREATE TABLE IF NOT EXISTS acc_asset_models (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  method ENUM('linear','degressive','degressive_then_linear') DEFAULT 'linear',
  method_number INT DEFAULT 5,
  method_period INT DEFAULT 12,
  method_progress_factor DECIMAL(5,2) DEFAULT 0.5,
  prorata_computation_type ENUM('none','constant_periods','daily_computation') DEFAULT 'none',
  account_asset_id INT,
  account_depreciation_id INT,
  account_depreciation_expense_id INT,
  journal_id INT,
  active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS acc_assets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  state ENUM('draft','open','paused','close','cancelled') DEFAULT 'draft',
  model_id INT,
  account_asset_id INT,
  account_depreciation_id INT,
  account_depreciation_expense_id INT,
  journal_id INT,
  original_move_line_id INT,
  original_value DECIMAL(15,4) DEFAULT 0,
  book_value DECIMAL(15,4) DEFAULT 0,
  salvage_value DECIMAL(15,4) DEFAULT 0,
  method ENUM('linear','degressive','degressive_then_linear') DEFAULT 'linear',
  method_number INT DEFAULT 5,
  method_period INT DEFAULT 12,
  prorata_date DATE,
  acquisition_date DATE,
  disposal_date DATE,
  currency_id INT DEFAULT 1,
  partner_id INT,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS acc_asset_lines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  asset_id INT NOT NULL,
  name VARCHAR(200),
  sequence INT DEFAULT 1,
  move_id INT,
  amount DECIMAL(15,4) DEFAULT 0,
  remaining_value DECIMAL(15,4) DEFAULT 0,
  depreciated_value DECIMAL(15,4) DEFAULT 0,
  date DATE,
  reversed TINYINT(1) DEFAULT 0,
  FOREIGN KEY (asset_id) REFERENCES acc_assets(id) ON DELETE CASCADE
);

-- DEFERRED REVENUE / EXPENSE
CREATE TABLE IF NOT EXISTS acc_deferred (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  type ENUM('revenue','expense') NOT NULL,
  state ENUM('draft','in_progress','closed') DEFAULT 'draft',
  move_ids TEXT,
  account_id INT,
  partner_id INT,
  amount_total DECIMAL(15,4) DEFAULT 0,
  date_from DATE,
  date_to DATE,
  journal_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FOLLOW-UP LEVELS
CREATE TABLE IF NOT EXISTS acc_followup_levels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  delay INT NOT NULL,
  description TEXT,
  send_email TINYINT(1) DEFAULT 1,
  send_letter TINYINT(1) DEFAULT 0,
  block_account TINYINT(1) DEFAULT 0,
  sequence INT DEFAULT 10,
  color VARCHAR(20)
);

-- ACCOUNTING SETTINGS
CREATE TABLE IF NOT EXISTS acc_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO acc_settings (setting_key, setting_value) VALUES
('default_currency_id', '1'),
('default_sale_journal_id', '1'),
('default_purchase_journal_id', '2'),
('default_cash_journal_id', '3'),
('default_receivable_account', '11110'),
('default_payable_account', '21110'),
('tax_lock_date', NULL),
('lock_date', NULL),
('invoice_sequence_prefix', 'INV'),
('bill_sequence_prefix', 'BILL'),
('payment_sequence_prefix', 'PAY'),
('company_name', 'Xetuu Limited'),
('company_address', 'Nairobi, Kenya'),
('company_vat', ''),
('fiscal_year_start', '01-01'),
('bank_reconciliation_start', NULL),
('multi_currency', '0'),
('analytic_accounting', '1'),
('budget_management', '1'),
('asset_management', '1'),
('deferred_revenue', '1'),
('tax_computation', 'exclusive')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- SEED DATA (Kenya defaults)

-- CHART OF ACCOUNTS (hierarchical 5-digit structure)
-- Root groups
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('10000', 'Application of Funds (Assets)', 'Asset', 1, NULL),
('20000', 'Source of Funds (Liabilities)', 'Liability', 1, NULL),
('30000', 'Equity', 'Equity', 1, NULL),
('40000', 'Income', 'Revenue', 1, NULL),
('50000', 'Expenses', 'Expense', 1, NULL);

-- Assets: level 2
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('11000', 'Current Assets', 'Current Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='10000' LIMIT 1) AS t)),
('12000', 'Fixed Assets', 'Fixed Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='10000' LIMIT 1) AS t)),
('13000', 'Investments', 'Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='10000' LIMIT 1) AS t)),
('14000', 'Temporary Accounts', 'Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='10000' LIMIT 1) AS t));

-- Current Assets: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('11100', 'Accounts Receivable', 'Receivable', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11200', 'Bank Accounts', 'Bank', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11300', 'Cash In Hand', 'Cash', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11400', 'Loans and Advances (Assets)', 'Current Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11500', 'Prepaid Expenses', 'Current Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11600', 'Securities and Deposits', 'Current Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11700', 'Short-term Investments', 'Current Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11800', 'Stock Assets', 'Current Asset', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t)),
('11900', 'Tax Assets', 'Tax', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11000' LIMIT 1) AS t));

-- Fixed Assets: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('12100', 'Accumulated Depreciation', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12200', 'Buildings', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12300', 'CWIP Account', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12400', 'Capital Equipment', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12500', 'Electronic Equipment', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12600', 'Furniture and Fixtures', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12700', 'Office Equipment', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12800', 'Plants and Machineries', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t)),
('12900', 'Software', 'Fixed Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='12000' LIMIT 1) AS t));

-- Temporary Accounts: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('14100', 'Temporary Opening', 'Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='14000' LIMIT 1) AS t));

-- Liabilities: level 2
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('21000', 'Current Liabilities', 'Current Liability', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='20000' LIMIT 1) AS t)),
('22000', 'Non-Current Liabilities', 'Liability', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='20000' LIMIT 1) AS t));

-- Current Liabilities: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('21100', 'Accounts Payable', 'Payable', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21200', 'Payroll Payable', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21300', 'Accrued Expenses', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21400', 'Customer Advances', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21500', 'Duties and Taxes', 'Current Liability', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21600', 'Loans (Liabilities)', 'Current Liability', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21700', 'Short-term Provisions', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t)),
('21800', 'Stock Liabilities', 'Current Liability', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21000' LIMIT 1) AS t));

-- Non-Current Liabilities: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('22100', 'Employee Benefits Obligation', 'Long-term Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='22000' LIMIT 1) AS t)),
('22200', 'Long-term Provisions', 'Long-term Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='22000' LIMIT 1) AS t));

-- Equity: level 2
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('30100', 'Capital Stock', 'Equity', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='30000' LIMIT 1) AS t)),
('30200', 'Dividends Paid', 'Equity', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='30000' LIMIT 1) AS t)),
('30300', 'Opening Balance Equity', 'Equity', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='30000' LIMIT 1) AS t)),
('30400', 'Retained Earnings', 'Equity', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='30000' LIMIT 1) AS t)),
('30500', 'Revaluation Surplus', 'Equity', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='30000' LIMIT 1) AS t));

-- Income: level 2
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('41000', 'Direct Income', 'Revenue', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='40000' LIMIT 1) AS t)),
('42000', 'Indirect Income', 'Revenue', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='40000' LIMIT 1) AS t));

-- Income: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('41100', 'Sales', 'Revenue', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='41000' LIMIT 1) AS t)),
('41200', 'Service', 'Revenue', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='41000' LIMIT 1) AS t)),
('42100', 'Interest Income', 'Other Income', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='42000' LIMIT 1) AS t));

-- Income: level 4
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('42110', 'Interest on Fixed Deposits', 'Other Income', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='42100' LIMIT 1) AS t));

-- Expenses: level 2
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('51000', 'Direct Expenses', 'Expense', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='50000' LIMIT 1) AS t)),
('52000', 'Indirect Expenses', 'Expense', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='50000' LIMIT 1) AS t));

-- Direct Expenses: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('51100', 'Stock Expenses', 'Expense', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='51000' LIMIT 1) AS t));

-- Stock Expenses: level 4
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('51110', 'Cost of Goods Sold', 'Cost of Revenue', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='51100' LIMIT 1) AS t)),
('51120', 'Expenses Included In Asset Valuation', 'Expense', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='51100' LIMIT 1) AS t)),
('51130', 'Stock Adjustment', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='51100' LIMIT 1) AS t));

-- Asset Valuation: level 5
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('51121', 'Expenses Included In Valuation', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='51120' LIMIT 1) AS t));

-- Indirect Expenses: level 3
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('52100', 'Administrative Expenses', 'Expense', 1, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52000' LIMIT 1) AS t));

-- Administrative Expenses: level 4
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('52110', 'Bank Charges', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52120', 'Commission on Sales', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52130', 'Depreciation', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52140', 'Entertainment Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52150', 'Exchange Gain/Loss', 'Other Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52160', 'Freight and Forwarding Charges', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52170', 'Gain/Loss on Asset Disposal', 'Other Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52180', 'Impairment', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52190', 'Interest Expense', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52200', 'Legal Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52210', 'Marketing Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52220', 'Miscellaneous Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52230', 'Office Maintenance Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52240', 'Office Rent', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52250', 'Postal Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52260', 'Print and Stationery', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52270', 'Round Off', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52280', 'Salary', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52290', 'Sales Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52300', 'Tax Expense', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52310', 'Telephone Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52320', 'Travel Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52330', 'Utility Expenses', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t)),
('52340', 'Write Off', 'Expense', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='52100' LIMIT 1) AS t));

-- Leaf accounts: Receivable, Bank, Cash, other current assets
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('11110', 'Debtors', 'Receivable', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11100' LIMIT 1) AS t)),
('11210', 'Bank', 'Bank', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11200' LIMIT 1) AS t)),
('11220', 'Bank – KCB', 'Bank', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11200' LIMIT 1) AS t)),
('11230', 'Bank – I&M KES', 'Bank', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11200' LIMIT 1) AS t)),
('11240', 'Bank – I&M USD', 'Bank', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11200' LIMIT 1) AS t)),
('11310', 'Cash', 'Cash', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11300' LIMIT 1) AS t)),
('11320', 'Petty Cash', 'Cash', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11300' LIMIT 1) AS t)),
('11410', 'Employee Advances', 'Current Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11400' LIMIT 1) AS t)),
('11610', 'Earnest Money', 'Current Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11600' LIMIT 1) AS t)),
('11810', 'Stock In Hand', 'Current Asset', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11800' LIMIT 1) AS t));

-- Leaf accounts: Payable, tax, payroll, loans, stock liabilities
INSERT IGNORE INTO acc_accounts (code, name, type, is_group, parent_id) VALUES
('21110', 'Creditors', 'Payable', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21100' LIMIT 1) AS t)),
('21210', 'PAYE Payable', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21200' LIMIT 1) AS t)),
('21220', 'NSSF Payable', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21200' LIMIT 1) AS t)),
('21230', 'NHIF / SHA Payable', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21200' LIMIT 1) AS t)),
('21240', 'Net Wages Payable', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21200' LIMIT 1) AS t)),
('21510', 'VAT Payable', 'Tax', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21500' LIMIT 1) AS t)),
('21520', 'Withholding Tax Payable', 'Tax', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21500' LIMIT 1) AS t)),
('21610', 'Bank Overdraft Account', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21600' LIMIT 1) AS t)),
('21620', 'Secured Loans', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21600' LIMIT 1) AS t)),
('21630', 'Unsecured Loans', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21600' LIMIT 1) AS t)),
('21810', 'Asset Received But Not Billed', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21800' LIMIT 1) AS t)),
('21820', 'Stock Received But Not Billed', 'Current Liability', 0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21800' LIMIT 1) AS t));

-- JOURNALS
-- ON DUPLICATE KEY UPDATE ensures reinstalling corrects wrong mappings on existing databases
INSERT INTO acc_journals (id, name, type, code, account_id, show_on_dashboard, active) VALUES
(1, 'Customer Invoices', 'Sale',     'INV',    (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11110' LIMIT 1) AS t), 1, 1),
(2, 'Vendor Bills',      'Purchase', 'BILL',   (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21110' LIMIT 1) AS t), 1, 1),
(3, 'Cash',              'Cash',     'CSH',    (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11310' LIMIT 1) AS t), 1, 1),
(4, 'Bank – KCB',        'Bank',     'BNK',    (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11220' LIMIT 1) AS t), 1, 1),
(5, 'Bank – I&M',        'Bank',     'BNKIM',  (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='11230' LIMIT 1) AS t), 1, 1),
(6, 'Miscellaneous',     'General',  'MISC',   NULL,                                                                           0, 1)
ON DUPLICATE KEY UPDATE
    account_id       = VALUES(account_id),
    name             = VALUES(name),
    type             = VALUES(type),
    show_on_dashboard = VALUES(show_on_dashboard);

-- TAXES
-- VAT posts to 21510 VAT Payable; withholding tax posts to 21520 Withholding Tax Payable
INSERT IGNORE INTO acc_taxes (name, type_tax_use, amount_type, amount, account_id, refund_account_id) VALUES
('VAT 16%',           'sale',     'percent',  16, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t2)),
('VAT 16%',           'purchase', 'percent',  16, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t2)),
('VAT 0%',            'sale',     'percent',   0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t2)),
('VAT 0%',            'purchase', 'percent',   0, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21510' LIMIT 1) AS t2)),
('Withholding Tax 5%','purchase', 'percent',  -5, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21520' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21520' LIMIT 1) AS t2)),
('Withholding Tax 3%','purchase', 'percent',  -3, (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21520' LIMIT 1) AS t), (SELECT id FROM (SELECT id FROM acc_accounts WHERE code='21520' LIMIT 1) AS t2));

-- PAYMENT TERMS
INSERT IGNORE INTO acc_payment_terms (id, name) VALUES
(1, 'Immediate Payment'),
(2, 'Net 15 days'),
(3, 'Net 30 days'),
(4, 'Net 60 days'),
(5, '2/10 Net 30');

INSERT IGNORE INTO acc_payment_term_lines (payment_term_id, value, value_amount, delay_type, nb_days) VALUES
(1, 'balance', 0, 'days_after', 0),
(2, 'balance', 0, 'days_after', 15),
(3, 'balance', 0, 'days_after', 30),
(4, 'balance', 0, 'days_after', 60),
(5, 'percent', 100, 'days_after', 10), -- Simplified 2/10 setup for seeding
(5, 'balance', 0, 'days_after', 30);

-- ACTIVITY LOGS (enterprise workspace — comments and state changes)
CREATE TABLE IF NOT EXISTS acc_activity_logs (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    doc_type    VARCHAR(50)  NOT NULL,
    doc_id      INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL DEFAULT 0,
    user_name   VARCHAR(150) NOT NULL DEFAULT '',
    action      VARCHAR(100) NOT NULL DEFAULT 'note',
    description TEXT,
    comment     TEXT,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_doc (doc_type, doc_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ATTACHMENTS (enterprise workspace — drag-drop file uploads)
CREATE TABLE IF NOT EXISTS acc_attachments (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    doc_type      VARCHAR(50)  NOT NULL,
    doc_id        INT UNSIGNED NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size     INT UNSIGNED NOT NULL DEFAULT 0,
    mime_type     VARCHAR(100) NOT NULL DEFAULT '',
    uploaded_by   INT UNSIGNED NOT NULL DEFAULT 0,
    uploaded_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_doc (doc_type, doc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- RECONCILIATION MODELS (auto-matching rules for bank reconciliation)
CREATE TABLE IF NOT EXISTS acc_reconcil_models (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(150) NOT NULL,
    rule_type        ENUM('writeoff_button','writeoff_suggestion','invoice_matching') NOT NULL DEFAULT 'writeoff_button',
    sequence         INT NOT NULL DEFAULT 10,
    active           TINYINT(1) NOT NULL DEFAULT 1,
    match_nature     ENUM('both','debit','credit') NOT NULL DEFAULT 'both',
    match_amount_type ENUM('any','lower','greater','between','is') NOT NULL DEFAULT 'any',
    match_amount_min  DECIMAL(15,2) DEFAULT NULL,
    match_amount_max  DECIMAL(15,2) DEFAULT NULL,
    match_label_type  ENUM('any','contains','is','not_contains','regex') NOT NULL DEFAULT 'any',
    match_label_param VARCHAR(255) DEFAULT NULL,
    account_id       INT DEFAULT NULL,
    journal_id       INT DEFAULT NULL,
    writeoff_label   VARCHAR(150) DEFAULT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_seq (active, sequence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
