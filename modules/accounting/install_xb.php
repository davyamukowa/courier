<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Xetuu Books (XB) double-entry tables — appended on module activation

// ── Chart of Accounts (separate from legacy acc_accounts) ────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_gl_accounts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_gl_accounts` (
      `id`                    INT AUTO_INCREMENT PRIMARY KEY,
      `code`                  VARCHAR(20) UNIQUE NOT NULL,
      `name`                  VARCHAR(200) NOT NULL,
      `type`                  ENUM(
        "Asset","Current Asset","Fixed Asset","Bank","Cash",
        "Liability","Current Liability","Long-term Liability",
        "Equity","Revenue","Cost of Revenue","Expense",
        "Other Income","Other Expense","Tax","Receivable","Payable"
      ) NOT NULL,
      `currency_id`           INT DEFAULT 1,
      `parent_id`             INT DEFAULT NULL,
      `is_group`              TINYINT(1) DEFAULT 0,
      `allow_reconciliation`  TINYINT(1) DEFAULT 0,
      `deprecated`            TINYINT(1) DEFAULT 0,
      `company_id`            INT DEFAULT 1,
      `notes`                 TEXT,
      `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_type (`type`),
      INDEX idx_parent (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Seed Kenya standard chart of accounts
    $accounts = [
        // Assets
        ['1000','Cash and Cash Equivalents','Asset',1],
        ['1010','Petty Cash','Cash',1],
        ['1020','Bank – KCB','Bank',1],
        ['1030','Bank – I&M KES','Bank',1],
        ['1040','Bank – I&M USD','Bank',1],
        ['1100','Accounts Receivable','Receivable',1],
        ['1200','Inventory','Current Asset',1],
        ['1300','Prepaid Expenses','Current Asset',1],
        ['1500','Fixed Assets','Fixed Asset',1],
        ['1510','Accumulated Depreciation','Fixed Asset',1],
        // Liabilities
        ['2000','Accounts Payable','Payable',1],
        ['2100','VAT Payable','Tax',1],
        ['2200','PAYE Payable','Current Liability',1],
        ['2300','NSSF Payable','Current Liability',1],
        ['2400','NHIF Payable','Current Liability',1],
        ['2500','Short-term Loans','Current Liability',1],
        // Equity
        ['3000','Share Capital','Equity',1],
        ['3100','Retained Earnings','Equity',1],
        ['3200','Current Year Earnings','Equity',1],
        // Revenue
        ['4000','Sales Revenue','Revenue',1],
        ['4100','Service Revenue','Revenue',1],
        ['4200','Other Income','Other Income',1],
        // Cost of Revenue
        ['5000','Cost of Goods Sold','Cost of Revenue',1],
        ['5100','Direct Labour','Cost of Revenue',1],
        // Expenses
        ['6000','Salaries and Wages','Expense',1],
        ['6100','Rent and Utilities','Expense',1],
        ['6200','Marketing and Advertising','Expense',1],
        ['6300','Travel and Entertainment','Expense',1],
        ['6400','Office Supplies','Expense',1],
        ['6500','Depreciation','Expense',1],
        ['6600','Bank Charges','Expense',1],
        ['6700','Professional Fees','Expense',1],
        ['6800','Insurance','Expense',1],
        ['6900','Miscellaneous Expenses','Expense',1],
    ];
    foreach ($accounts as $a) {
        $CI->db->query("INSERT INTO `".db_prefix()."acc_gl_accounts` (code, name, type, currency_id) VALUES ('".$a[0]."', '".addslashes($a[1])."', '".$a[2]."', ".$a[3].")");
    }
}

// ── Journals ──────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_journals')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_journals` (
      `id`                         INT AUTO_INCREMENT PRIMARY KEY,
      `name`                       VARCHAR(200) NOT NULL,
      `type`                       ENUM("Sale","Purchase","Cash","Bank","General","Situation") NOT NULL,
      `code`                       VARCHAR(10) UNIQUE NOT NULL,
      `account_id`                 INT DEFAULT NULL,
      `suspense_account_id`        INT DEFAULT NULL,
      `profit_account_id`          INT DEFAULT NULL,
      `loss_account_id`            INT DEFAULT NULL,
      `currency_id`                INT DEFAULT 1,
      `bank_account_id`            INT DEFAULT NULL,
      `dedicated_refund_sequence`  TINYINT(1) DEFAULT 0,
      `lock_posted_entries`        TINYINT(1) DEFAULT 0,
      `show_on_dashboard`          TINYINT(1) DEFAULT 1,
      `color`                      VARCHAR(20) DEFAULT NULL,
      `sequence`                   INT DEFAULT 10,
      `active`                     TINYINT(1) DEFAULT 1,
      `created_at`                 TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Seed journals
    $journals = [
        ['Customer Invoices','Sale','INV',  'Accounts Receivable'],
        ['Vendor Bills',     'Purchase','BILL','Accounts Payable'],
        ['Cash',             'Cash','CSH',  'Petty Cash'],
        ['Bank – KCB',       'Bank','BNK',  'Bank – KCB'],
        ['Bank – I&M',       'Bank','BNKIM','Bank – I&M KES'],
        ['Miscellaneous',    'General','MISC',null],
    ];
    $account_map = [];
    $rows = $CI->db->query("SELECT id, name FROM `".db_prefix()."acc_gl_accounts`")->result_array();
    foreach ($rows as $r) { $account_map[$r['name']] = $r['id']; }

    foreach ($journals as $j) {
        $acct_id = isset($account_map[$j[3]]) ? $account_map[$j[3]] : 'NULL';
        $CI->db->query("INSERT INTO `".db_prefix()."acc_journals` (name, type, code, account_id) VALUES ('".addslashes($j[0])."', '".$j[1]."', '".$j[2]."', ".$acct_id.")");
    }
}

// ── Journal Moves (Invoices / Bills / Journal Entries) ─────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_moves')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_moves` (
      `id`                     INT AUTO_INCREMENT PRIMARY KEY,
      `name`                   VARCHAR(100) DEFAULT NULL,
      `move_type`              ENUM("entry","out_invoice","out_refund","in_invoice","in_refund","out_receipt","in_receipt") DEFAULT "entry",
      `state`                  ENUM("draft","posted","cancel") DEFAULT "draft",
      `journal_id`             INT NOT NULL,
      `date`                   DATE NOT NULL,
      `invoice_date`           DATE DEFAULT NULL,
      `invoice_date_due`       DATE DEFAULT NULL,
      `partner_id`             INT DEFAULT NULL,
      `partner_type`           ENUM("customer","vendor") DEFAULT NULL,
      `currency_id`            INT DEFAULT 1,
      `exchange_rate`          DECIMAL(15,6) DEFAULT 1,
      `amount_untaxed`         DECIMAL(15,4) DEFAULT 0,
      `amount_tax`             DECIMAL(15,4) DEFAULT 0,
      `amount_total`           DECIMAL(15,4) DEFAULT 0,
      `amount_residual`        DECIMAL(15,4) DEFAULT 0,
      `payment_state`          ENUM("not_paid","in_payment","paid","partial","reversed","invoicing_legacy") DEFAULT "not_paid",
      `invoice_origin`         VARCHAR(200) DEFAULT NULL,
      `ref`                    VARCHAR(200) DEFAULT NULL,
      `narration`              TEXT,
      `invoice_payment_term_id` INT DEFAULT NULL,
      `reversed_entry_id`      INT DEFAULT NULL,
      `auto_post`              ENUM("no","monthly","quarterly","annually") DEFAULT "no",
      `auto_post_until`        DATE DEFAULT NULL,
      `sequence_prefix`        VARCHAR(50) DEFAULT NULL,
      `created_by`             INT DEFAULT NULL,
      `updated_by`             INT DEFAULT NULL,
      `created_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_state (`state`),
      INDEX idx_type (`move_type`),
      INDEX idx_partner (`partner_id`),
      INDEX idx_date (`date`),
      INDEX idx_journal (`journal_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── Journal Move Lines ────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_move_lines')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_move_lines` (
      `id`                INT AUTO_INCREMENT PRIMARY KEY,
      `move_id`           INT NOT NULL,
      `sequence`          INT DEFAULT 10,
      `account_id`        INT NOT NULL,
      `partner_id`        INT DEFAULT NULL,
      `name`              VARCHAR(500) DEFAULT NULL,
      `ref`               VARCHAR(200) DEFAULT NULL,
      `quantity`          DECIMAL(15,4) DEFAULT 1,
      `price_unit`        DECIMAL(15,6) DEFAULT 0,
      `discount`          DECIMAL(5,2) DEFAULT 0,
      `price_subtotal`    DECIMAL(15,4) DEFAULT 0,
      `price_total`       DECIMAL(15,4) DEFAULT 0,
      `debit`             DECIMAL(15,4) DEFAULT 0,
      `credit`            DECIMAL(15,4) DEFAULT 0,
      `balance`           DECIMAL(15,4) DEFAULT 0,
      `amount_currency`   DECIMAL(15,4) DEFAULT 0,
      `currency_id`       INT DEFAULT 1,
      `tax_ids`           VARCHAR(500) DEFAULT NULL,
      `tax_line_id`       INT DEFAULT NULL,
      `analytic_distribution` TEXT,
      `product_id`        INT DEFAULT NULL,
      `date`              DATE DEFAULT NULL,
      `date_maturity`     DATE DEFAULT NULL,
      `reconciled`        TINYINT(1) DEFAULT 0,
      `full_reconcile_id` INT DEFAULT NULL,
      `matching_number`   VARCHAR(100) DEFAULT NULL,
      `blocked`           TINYINT(1) DEFAULT 0,
      `payment_id`        INT DEFAULT NULL,
      `display_type`      ENUM("product","tax","payment_term","line_section","line_note","") DEFAULT "product",
      FOREIGN KEY (move_id) REFERENCES `'.db_prefix().'acc_moves`(id) ON DELETE CASCADE,
      INDEX idx_account (`account_id`),
      INDEX idx_move (`move_id`),
      INDEX idx_reconciled (`reconciled`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── Taxes ─────────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_taxes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_taxes` (
      `id`                  INT AUTO_INCREMENT PRIMARY KEY,
      `name`                VARCHAR(200) NOT NULL,
      `type_tax_use`        ENUM("sale","purchase","none","adjustment") DEFAULT "sale",
      `tax_scope`           ENUM("consu","service","") DEFAULT "",
      `amount_type`         ENUM("percent","fixed","division","group","code") DEFAULT "percent",
      `amount`              DECIMAL(10,4) DEFAULT 0,
      `price_include`       TINYINT(1) DEFAULT 0,
      `include_base_amount` TINYINT(1) DEFAULT 0,
      `account_id`          INT DEFAULT NULL,
      `refund_account_id`   INT DEFAULT NULL,
      `description`         VARCHAR(100) DEFAULT NULL,
      `invoice_label`       VARCHAR(100) DEFAULT NULL,
      `active`              TINYINT(1) DEFAULT 1,
      `sequence`            INT DEFAULT 1,
      `tax_group_id`        INT DEFAULT NULL,
      `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Seed taxes
    $vat_acct = $CI->db->query("SELECT id FROM `".db_prefix()."acc_gl_accounts` WHERE code='2100' LIMIT 1")->row();
    $vat_id = $vat_acct ? $vat_acct->id : 'NULL';
    $CI->db->query("INSERT INTO `".db_prefix()."acc_taxes` (name, type_tax_use, amount_type, amount, account_id, description) VALUES
        ('VAT 16%', 'sale', 'percent', 16, ".$vat_id.", 'VAT16'),
        ('VAT 16% (Purchase)', 'purchase', 'percent', 16, ".$vat_id.", 'VAT16'),
        ('VAT 0%', 'sale', 'percent', 0, NULL, 'VAT0'),
        ('VAT 0% (Purchase)', 'purchase', 'percent', 0, NULL, 'VAT0'),
        ('WHT 5%', 'purchase', 'percent', 5, NULL, 'WHT5'),
        ('WHT 3%', 'purchase', 'percent', 3, NULL, 'WHT3')");
}

// ── Payment Terms ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_payment_terms')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_payment_terms` (
      `id`         INT AUTO_INCREMENT PRIMARY KEY,
      `name`       VARCHAR(200) NOT NULL,
      `note`       TEXT,
      `active`     TINYINT(1) DEFAULT 1,
      `sequence`   INT DEFAULT 10,
      `company_id` INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_terms` (name, sequence) VALUES
        ('Immediate Payment', 1),
        ('Net 15 Days', 2),
        ('Net 30 Days', 3),
        ('Net 60 Days', 4),
        ('2/10 Net 30', 5)");
}

if (!$CI->db->table_exists(db_prefix() . 'acc_payment_term_lines')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_payment_term_lines` (
      `id`              INT AUTO_INCREMENT PRIMARY KEY,
      `payment_term_id` INT NOT NULL,
      `value`           ENUM("balance","percent","fixed") DEFAULT "balance",
      `value_amount`    DECIMAL(10,4) DEFAULT 0,
      `delay_type`      ENUM("days_after","days_after_end_of_month","days_after_end_of_next_month","days_end_of_month_on_the") DEFAULT "days_after",
      `nb_days`         INT DEFAULT 0,
      FOREIGN KEY (payment_term_id) REFERENCES `'.db_prefix().'acc_payment_terms`(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Seed payment term lines
    $terms = $CI->db->query("SELECT id, name FROM `".db_prefix()."acc_payment_terms`")->result_array();
    foreach ($terms as $t) {
        if ($t['name'] == 'Immediate Payment') {
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, nb_days) VALUES (".$t['id'].", 'balance', 0)");
        } elseif ($t['name'] == 'Net 15 Days') {
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, nb_days) VALUES (".$t['id'].", 'balance', 15)");
        } elseif ($t['name'] == 'Net 30 Days') {
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, nb_days) VALUES (".$t['id'].", 'balance', 30)");
        } elseif ($t['name'] == 'Net 60 Days') {
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, nb_days) VALUES (".$t['id'].", 'balance', 60)");
        } elseif ($t['name'] == '2/10 Net 30') {
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, value_amount, nb_days) VALUES (".$t['id'].", 'percent', 2, 10)");
            $CI->db->query("INSERT INTO `".db_prefix()."acc_payment_term_lines` (payment_term_id, value, nb_days) VALUES (".$t['id'].", 'balance', 30)");
        }
    }
}

// ── XB Payments ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_xb_payments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_xb_payments` (
      `id`                                    INT AUTO_INCREMENT PRIMARY KEY,
      `name`                                  VARCHAR(100) DEFAULT NULL,
      `state`                                 ENUM("draft","posted","cancelled","sent","reconciled") DEFAULT "draft",
      `payment_type`                          ENUM("outbound","inbound") NOT NULL,
      `partner_type`                          ENUM("customer","vendor") NOT NULL,
      `partner_id`                            INT DEFAULT NULL,
      `journal_id`                            INT NOT NULL,
      `currency_id`                           INT DEFAULT 1,
      `amount`                                DECIMAL(15,4) NOT NULL,
      `date`                                  DATE NOT NULL,
      `ref`                                   VARCHAR(200) DEFAULT NULL,
      `memo`                                  VARCHAR(500) DEFAULT NULL,
      `destination_account_id`               INT DEFAULT NULL,
      `destination_journal_id`               INT DEFAULT NULL,
      `is_reconciled`                         TINYINT(1) DEFAULT 0,
      `move_id`                               INT DEFAULT NULL,
      `paired_internal_transfer_payment_id`  INT DEFAULT NULL,
      `created_by`                            INT DEFAULT NULL,
      `created_at`                            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_partner (`partner_id`),
      INDEX idx_state (`state`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── Bank Statement Lines ──────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_bank_statement_lines')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_bank_statement_lines` (
      `id`              INT AUTO_INCREMENT PRIMARY KEY,
      `journal_id`      INT NOT NULL,
      `date`            DATE NOT NULL,
      `payment_ref`     VARCHAR(500) DEFAULT NULL,
      `ref`             VARCHAR(200) DEFAULT NULL,
      `partner_id`      INT DEFAULT NULL,
      `partner_name`    VARCHAR(200) DEFAULT NULL,
      `amount`          DECIMAL(15,4) NOT NULL,
      `amount_currency` DECIMAL(15,4) DEFAULT 0,
      `currency_id`     INT DEFAULT 1,
      `running_balance` DECIMAL(15,4) DEFAULT 0,
      `is_reconciled`   TINYINT(1) DEFAULT 0,
      `move_id`         INT DEFAULT NULL,
      `sequence`        INT DEFAULT 1,
      `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_journal (`journal_id`),
      INDEX idx_date (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── Reconciliation ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_full_reconcile')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_full_reconcile` (
      `id`               INT AUTO_INCREMENT PRIMARY KEY,
      `name`             VARCHAR(100) UNIQUE,
      `exchange_move_id` INT DEFAULT NULL,
      `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'acc_partial_reconcile')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_partial_reconcile` (
      `id`                INT AUTO_INCREMENT PRIMARY KEY,
      `debit_move_id`     INT NOT NULL,
      `credit_move_id`    INT NOT NULL,
      `full_reconcile_id` INT DEFAULT NULL,
      `amount`            DECIMAL(15,4) NOT NULL,
      `amount_currency`   DECIMAL(15,4) DEFAULT 0,
      `currency_id`       INT DEFAULT 1,
      `max_date`          DATE DEFAULT NULL,
      `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── Currencies ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_currencies')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_currencies` (
      `id`             INT AUTO_INCREMENT PRIMARY KEY,
      `name`           VARCHAR(10) UNIQUE NOT NULL,
      `symbol`         VARCHAR(10) DEFAULT NULL,
      `full_name`      VARCHAR(100) DEFAULT NULL,
      `rate`           DECIMAL(20,8) DEFAULT 1,
      `rate_date`      DATE DEFAULT NULL,
      `position`       ENUM("before","after") DEFAULT "before",
      `active`         TINYINT(1) DEFAULT 1,
      `rounding`       DECIMAL(10,6) DEFAULT 0.01,
      `decimal_places` INT DEFAULT 2,
      `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query("INSERT INTO `".db_prefix()."acc_currencies` (name, symbol, full_name, rate, position) VALUES
        ('KES','KSh','Kenyan Shilling',1,'before'),
        ('USD','\$','US Dollar',130.5,'before'),
        ('EUR','€','Euro',142.0,'before'),
        ('UGX','USh','Ugandan Shilling',0.035,'before'),
        ('TZS','TSh','Tanzanian Shilling',0.052,'before'),
        ('RWF','RF','Rwandan Franc',0.091,'before'),
        ('ETB','Br','Ethiopian Birr',1.15,'before')");
}

// ── Fiscal Years ──────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_fiscal_years')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_fiscal_years` (
      `id`         INT AUTO_INCREMENT PRIMARY KEY,
      `name`       VARCHAR(100) NOT NULL,
      `date_from`  DATE NOT NULL,
      `date_to`    DATE NOT NULL,
      `state`      ENUM("open","close") DEFAULT "open",
      `company_id` INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $year = date('Y');
    $CI->db->query("INSERT INTO `".db_prefix()."acc_fiscal_years` (name, date_from, date_to, state) VALUES ('FY ".($year-1)."', '".($year-1)."-01-01', '".($year-1)."-12-31', 'close'), ('FY ".$year."', '".$year."-01-01', '".$year."-12-31', 'open')");
}

// ── Lock Dates ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_lock_dates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_lock_dates` (
      `id`                    INT AUTO_INCREMENT PRIMARY KEY,
      `company_id`            INT DEFAULT 1 UNIQUE,
      `period_lock_date`      DATE DEFAULT NULL,
      `fiscalyear_lock_date`  DATE DEFAULT NULL,
      `tax_lock_date`         DATE DEFAULT NULL,
      `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    $CI->db->query("INSERT INTO `".db_prefix()."acc_lock_dates` (company_id) VALUES (1)");
}

// ── Analytic ──────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_analytic_plans')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_analytic_plans` (
      `id`                      INT AUTO_INCREMENT PRIMARY KEY,
      `name`                    VARCHAR(200) NOT NULL,
      `description`             TEXT,
      `parent_id`               INT DEFAULT NULL,
      `default_applicability`   ENUM("optional","mandatory","unavailable") DEFAULT "optional",
      `color`                   VARCHAR(20) DEFAULT NULL,
      `sequence`                INT DEFAULT 10,
      `active`                  TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    $CI->db->query("INSERT INTO `".db_prefix()."acc_analytic_plans` (name, sequence) VALUES ('Departments', 1), ('Projects', 2), ('Cost Centres', 3)");
}

if (!$CI->db->table_exists(db_prefix() . 'acc_analytic_accounts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_analytic_accounts` (
      `id`          INT AUTO_INCREMENT PRIMARY KEY,
      `name`        VARCHAR(200) NOT NULL,
      `code`        VARCHAR(50) DEFAULT NULL,
      `plan_id`     INT NOT NULL,
      `partner_id`  INT DEFAULT NULL,
      `currency_id` INT DEFAULT 1,
      `active`      TINYINT(1) DEFAULT 1,
      `balance`     DECIMAL(15,4) DEFAULT 0,
      `debit`       DECIMAL(15,4) DEFAULT 0,
      `credit`      DECIMAL(15,4) DEFAULT 0,
      `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// ── XB Settings ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_xb_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_xb_settings` (
      `id`            INT AUTO_INCREMENT PRIMARY KEY,
      `setting_key`   VARCHAR(100) UNIQUE NOT NULL,
      `setting_value` TEXT,
      `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $defaults = [
        ['default_currency_id', '1'],
        ['invoice_sequence_prefix', 'INV'],
        ['bill_sequence_prefix', 'BILL'],
        ['payment_sequence_prefix', 'PAY'],
        ['company_name', 'Xetuu Limited'],
        ['company_address', 'Nairobi, Kenya'],
        ['company_vat', ''],
        ['fiscal_year_start', '01-01'],
        ['multi_currency', '0'],
        ['analytic_accounting', '1'],
        ['budget_management', '1'],
        ['asset_management', '1'],
        ['tax_computation', 'exclusive'],
        ['invoice_sequence_counter', '0'],
        ['bill_sequence_counter', '0'],
        ['payment_sequence_counter', '0'],
    ];
    foreach ($defaults as $d) {
        $CI->db->query("INSERT IGNORE INTO `".db_prefix()."acc_xb_settings` (setting_key, setting_value) VALUES ('".addslashes($d[0])."', '".addslashes($d[1])."')");
    }
}

// ── Move sequence counter per journal per year ────────────────────────────────
if (!$CI->db->table_exists(db_prefix() . 'acc_sequences')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'acc_sequences` (
      `id`          INT AUTO_INCREMENT PRIMARY KEY,
      `journal_id`  INT NOT NULL,
      `prefix`      VARCHAR(20) NOT NULL,
      `year`        INT NOT NULL,
      `last_number` INT DEFAULT 0,
      UNIQUE KEY uniq_seq (`journal_id`, `year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

add_option('acc_xb_installed', 1);
