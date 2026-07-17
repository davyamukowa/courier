<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$p  = db_prefix();
$cs = $CI->db->char_set;

// ── Migration helper: add a column only if it doesn't exist ──────────────────
if (!function_exists('xetuu_hr_add_column')) {
    function xetuu_hr_add_column($table, $column, $definition)
    {
        $CI  = &get_instance();
        $res = $CI->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if ($res->num_rows() === 0) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }
}

// ── Companies (Employer of Record entities) ──────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_companies')) {
    $CI->db->query("CREATE TABLE `{$p}hr_companies` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `email`        VARCHAR(150) DEFAULT NULL,
        `phone`        VARCHAR(50)  DEFAULT NULL,
        `country`      VARCHAR(100) DEFAULT NULL,
        `logo`         VARCHAR(255) DEFAULT NULL,
        `is_eor`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Employer of Record flag',
        `active`       TINYINT(1) NOT NULL DEFAULT 1,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Branches ─────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_branches')) {
    $CI->db->query("CREATE TABLE `{$p}hr_branches` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`   INT(11) NOT NULL DEFAULT 0,
        `name`         VARCHAR(200) NOT NULL,
        `address`      TEXT DEFAULT NULL,
        `city`         VARCHAR(100) DEFAULT NULL,
        `country`      VARCHAR(100) DEFAULT NULL,
        `active`       TINYINT(1) NOT NULL DEFAULT 1,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Departments ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_departments')) {
    $CI->db->query("CREATE TABLE `{$p}hr_departments` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`   INT(11) NOT NULL DEFAULT 0,
        `name`         VARCHAR(200) NOT NULL,
        `parent_id`    INT(11) DEFAULT NULL,
        `head_id`      INT(11) DEFAULT NULL COMMENT 'Employee ID of dept head',
        `active`       TINYINT(1) NOT NULL DEFAULT 1,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Designations ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_designations')) {
    $CI->db->query("CREATE TABLE `{$p}hr_designations` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `description`  TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Employee Groups ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_employee_groups')) {
    $CI->db->query("CREATE TABLE `{$p}hr_employee_groups` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `description`  TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Employee Grades ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_employee_grades')) {
    $CI->db->query("CREATE TABLE `{$p}hr_employee_grades` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(100) NOT NULL,
        `min_salary`   DECIMAL(15,2) DEFAULT 0.00,
        `max_salary`   DECIMAL(15,2) DEFAULT 0.00,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Employees (master record) ─────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_employees')) {
    $CI->db->query("CREATE TABLE `{$p}hr_employees` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `employee_number`     VARCHAR(30) NOT NULL COMMENT 'HR-EMP-00001',
        `staff_id`            INT(11) DEFAULT NULL COMMENT 'Link to tblstaff',
        -- Basic info
        `salutation`          VARCHAR(20) DEFAULT NULL,
        `first_name`          VARCHAR(100) NOT NULL,
        `middle_name`         VARCHAR(100) DEFAULT NULL,
        `last_name`           VARCHAR(100) NOT NULL,
        `gender`              ENUM('Male','Female','Other','Prefer not to say') DEFAULT NULL,
        `dob`                 DATE DEFAULT NULL,
        `photo`               VARCHAR(255) DEFAULT NULL,
        -- Company placement
        `company_id`          INT(11) NOT NULL DEFAULT 0 COMMENT 'Employer of Record',
        `client_id`           INT(11) NOT NULL DEFAULT 0 COMMENT 'Client company (consultancy mode)',
        `branch_id`           INT(11) DEFAULT NULL,
        `department_id`       INT(11) DEFAULT NULL,
        `designation_id`      INT(11) DEFAULT NULL,
        `employee_group_id`   INT(11) DEFAULT NULL,
        `grade_id`            INT(11) DEFAULT NULL,
        `employment_type`     ENUM('Full-Time','Part-Time','Contract','Intern','Casual','Consultant') DEFAULT 'Full-Time',
        `reports_to`          INT(11) DEFAULT NULL COMMENT 'Employee ID',
        -- Dates
        `date_of_joining`     DATE DEFAULT NULL,
        `offer_date`          DATE DEFAULT NULL,
        `confirmation_date`   DATE DEFAULT NULL,
        `probation_start`     DATE DEFAULT NULL,
        `probation_end`       DATE DEFAULT NULL,
        `retirement_date`     DATE DEFAULT NULL,
        `notice_days`         INT(5) DEFAULT 30,
        -- Contact
        `mobile`              VARCHAR(50) DEFAULT NULL,
        `personal_email`      VARCHAR(150) DEFAULT NULL,
        `company_email`       VARCHAR(150) DEFAULT NULL,
        `preferred_email`     ENUM('personal','company') DEFAULT 'company',
        -- Attendance
        `attendance_device_id` VARCHAR(100) DEFAULT NULL,
        `rfid_number`          VARCHAR(100) DEFAULT NULL,
        `biometric_id`         VARCHAR(100) DEFAULT NULL,
        `default_shift`        INT(11) DEFAULT NULL,
        -- Approvers
        `leave_approver`      INT(11) DEFAULT NULL,
        `expense_approver`    INT(11) DEFAULT NULL,
        `shift_approver`      INT(11) DEFAULT NULL,
        -- Salary (summary fields; detail in contracts)
        `salary_currency`     VARCHAR(10) DEFAULT 'KES',
        `salary_mode`         ENUM('Bank Transfer','Cash','Cheque','Mobile Money') DEFAULT 'Bank Transfer',
        -- Personal details
        `marital_status`      ENUM('Single','Married','Divorced','Widowed','Other') DEFAULT NULL,
        `blood_group`         VARCHAR(10) DEFAULT NULL,
        `nationality`         VARCHAR(100) DEFAULT NULL,
        `religion`            VARCHAR(100) DEFAULT NULL,
        `disability_status`   TINYINT(1) DEFAULT 0,
        -- Statutory (country-agnostic; addon injects country-specific labels)
        `social_sec_number`   VARCHAR(50) DEFAULT NULL,
        `health_fund_number`  VARCHAR(50) DEFAULT NULL,
        `tax_id`              VARCHAR(50) DEFAULT NULL,
        `passport_number`     VARCHAR(50) DEFAULT NULL,
        `passport_expiry`     DATE DEFAULT NULL,
        -- Status
        `status`              ENUM('Active','Inactive','On Leave','Terminated','Resigned','Retired') DEFAULT 'Active',
        `active`              TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`        DATETIME NOT NULL,
        `date_modified`       DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `employee_number` (`employee_number`),
        KEY `company_id` (`company_id`),
        KEY `client_id` (`client_id`),
        KEY `department_id` (`department_id`),
        KEY `reports_to` (`reports_to`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Migrate hr_employees: add columns that older installs are missing ─────────
$t = $p . 'hr_employees';
xetuu_hr_add_column($t, 'employee_number',      "VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'HR-EMP-00001'");
xetuu_hr_add_column($t, 'salutation',            "VARCHAR(20) DEFAULT NULL AFTER `employee_number`");
xetuu_hr_add_column($t, 'middle_name',           "VARCHAR(100) DEFAULT NULL AFTER `first_name`");
xetuu_hr_add_column($t, 'gender',                "ENUM('Male','Female','Other','Prefer not to say') DEFAULT NULL");
xetuu_hr_add_column($t, 'dob',                   "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'photo',                 "VARCHAR(255) DEFAULT NULL");
xetuu_hr_add_column($t, 'client_id',             "INT(11) NOT NULL DEFAULT 0 COMMENT 'Client company (consultancy mode)'");
xetuu_hr_add_column($t, 'branch_id',             "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'department_id',         "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'designation_id',        "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'employee_group_id',     "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'grade_id',              "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'employment_type',       "ENUM('Full-Time','Part-Time','Contract','Intern','Casual','Consultant') DEFAULT 'Full-Time'");
xetuu_hr_add_column($t, 'reports_to',            "INT(11) DEFAULT NULL COMMENT 'Employee ID'");
xetuu_hr_add_column($t, 'date_of_joining',       "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'offer_date',            "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'confirmation_date',     "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'probation_start',       "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'probation_end',         "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'retirement_date',       "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'notice_days',           "INT(5) DEFAULT 30");
xetuu_hr_add_column($t, 'mobile',                "VARCHAR(50) DEFAULT NULL");
xetuu_hr_add_column($t, 'personal_email',        "VARCHAR(150) DEFAULT NULL");
xetuu_hr_add_column($t, 'company_email',         "VARCHAR(150) DEFAULT NULL");
xetuu_hr_add_column($t, 'preferred_email',       "ENUM('personal','company') DEFAULT 'company'");
xetuu_hr_add_column($t, 'attendance_device_id',  "VARCHAR(100) DEFAULT NULL");
xetuu_hr_add_column($t, 'rfid_number',           "VARCHAR(100) DEFAULT NULL");
xetuu_hr_add_column($t, 'biometric_id',          "VARCHAR(100) DEFAULT NULL");
xetuu_hr_add_column($t, 'default_shift',         "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'leave_approver',        "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'expense_approver',      "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'shift_approver',        "INT(11) DEFAULT NULL");
xetuu_hr_add_column($t, 'salary_currency',       "VARCHAR(10) DEFAULT 'KES'");
xetuu_hr_add_column($t, 'salary_mode',           "ENUM('Bank Transfer','Cash','Cheque','Mobile Money') DEFAULT 'Bank Transfer'");
xetuu_hr_add_column($t, 'marital_status',        "ENUM('Single','Married','Divorced','Widowed','Other') DEFAULT NULL");
xetuu_hr_add_column($t, 'blood_group',           "VARCHAR(10) DEFAULT NULL");
xetuu_hr_add_column($t, 'nationality',           "VARCHAR(100) DEFAULT NULL");
xetuu_hr_add_column($t, 'religion',              "VARCHAR(100) DEFAULT NULL");
xetuu_hr_add_column($t, 'disability_status',     "TINYINT(1) DEFAULT 0");
xetuu_hr_add_column($t, 'social_sec_number',     "VARCHAR(50) DEFAULT NULL");
xetuu_hr_add_column($t, 'health_fund_number',    "VARCHAR(50) DEFAULT NULL");
xetuu_hr_add_column($t, 'tax_id',               "VARCHAR(50) DEFAULT NULL");
xetuu_hr_add_column($t, 'passport_number',       "VARCHAR(50) DEFAULT NULL");
xetuu_hr_add_column($t, 'passport_expiry',       "DATE DEFAULT NULL");
xetuu_hr_add_column($t, 'status',                "ENUM('Active','Inactive','On Leave','Terminated','Resigned','Retired') DEFAULT 'Active'");
xetuu_hr_add_column($t, 'date_modified',         "DATETIME DEFAULT NULL");
unset($t);

// ── Migrate hr_companies: add columns that older installs are missing ─────────
$tc = $p . 'hr_companies';
xetuu_hr_add_column($tc, 'address', "TEXT DEFAULT NULL AFTER `phone`");
unset($tc);

// ── Migrate hr_departments: add extended columns ──────────────────────────────
$td = $p . 'hr_departments';
xetuu_hr_add_column($td, 'manager_id',          "INT(11) DEFAULT NULL COMMENT 'tblstaff.staffid'");
xetuu_hr_add_column($td, 'is_group',             "TINYINT(1) NOT NULL DEFAULT 0");
xetuu_hr_add_column($td, 'disabled',             "TINYINT(1) NOT NULL DEFAULT 0");
xetuu_hr_add_column($td, 'payroll_cost_center',  "VARCHAR(200) DEFAULT NULL");
xetuu_hr_add_column($td, 'leave_block_list',     "VARCHAR(200) DEFAULT NULL");
unset($td);

// ── Department Approvers ──────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_dept_approvers')) {
    $CI->db->query("CREATE TABLE `{$p}hr_dept_approvers` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `dept_id`     INT(11) NOT NULL,
        `type`        ENUM('shift_request','leave','expense') NOT NULL,
        `approver_id` INT(11) NOT NULL COMMENT 'tblstaff.staffid',
        PRIMARY KEY (`id`),
        KEY `dept_id` (`dept_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Employee Addresses ────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_employee_addresses')) {
    $CI->db->query("CREATE TABLE `{$p}hr_employee_addresses` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`  INT(11) NOT NULL,
        `type`         ENUM('Permanent','Postal','Emergency','Work') DEFAULT 'Permanent',
        `address_line1` VARCHAR(255) DEFAULT NULL,
        `address_line2` VARCHAR(255) DEFAULT NULL,
        `city`         VARCHAR(100) DEFAULT NULL,
        `state`        VARCHAR(100) DEFAULT NULL,
        `country`      VARCHAR(100) DEFAULT NULL,
        `postal_code`  VARCHAR(20) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Emergency Contacts ────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_emergency_contacts')) {
    $CI->db->query("CREATE TABLE `{$p}hr_emergency_contacts` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`  INT(11) NOT NULL,
        `name`         VARCHAR(200) NOT NULL,
        `relationship` VARCHAR(100) DEFAULT NULL,
        `phone`        VARCHAR(50) NOT NULL,
        `email`        VARCHAR(150) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Bank Details ──────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_bank_details')) {
    $CI->db->query("CREATE TABLE `{$p}hr_bank_details` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`   INT(11) NOT NULL,
        `bank_name`     VARCHAR(200) NOT NULL,
        `bank_branch`   VARCHAR(200) DEFAULT NULL,
        `account_number` VARCHAR(50) NOT NULL,
        `account_name`  VARCHAR(200) NOT NULL,
        `swift_code`    VARCHAR(20) DEFAULT NULL,
        `is_primary`    TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Contracts ─────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_contracts')) {
    $CI->db->query("CREATE TABLE `{$p}hr_contracts` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`        INT(11) NOT NULL,
        `company_id`         INT(11) NOT NULL DEFAULT 0,
        `client_id`          INT(11) NOT NULL DEFAULT 0,
        `contract_number`    VARCHAR(50) DEFAULT NULL,
        `contract_type`      ENUM('Permanent','Fixed-Term','Casual','Internship','Consultancy') DEFAULT 'Permanent',
        `working_schedule`   VARCHAR(100) DEFAULT NULL,
        `wage_type`          ENUM('Monthly','Daily','Hourly') DEFAULT 'Monthly',
        `monthly_salary`     DECIMAL(15,2) DEFAULT 0.00,
        `annual_cost`        DECIMAL(15,2) DEFAULT 0.00,
        `currency`           VARCHAR(10) DEFAULT 'KES',
        `start_date`         DATE NOT NULL,
        `end_date`           DATE DEFAULT NULL,
        -- Benefits
        `medical`            DECIMAL(15,2) DEFAULT 0.00,
        `life_insurance`     DECIMAL(15,2) DEFAULT 0.00,
        `housing`            DECIMAL(15,2) DEFAULT 0.00,
        `transport`          DECIMAL(15,2) DEFAULT 0.00,
        `food_allowance`     DECIMAL(15,2) DEFAULT 0.00,
        `airtime`            DECIMAL(15,2) DEFAULT 0.00,
        -- Deductions
        `nssf`               DECIMAL(15,2) DEFAULT 0.00,
        `nhif_sha`           DECIMAL(15,2) DEFAULT 0.00,
        `tax_deduction`      DECIMAL(15,2) DEFAULT 0.00,
        `loan_deduction`     DECIMAL(15,2) DEFAULT 0.00,
        -- Meta
        `status`             ENUM('Active','Expired','Terminated','Draft') DEFAULT 'Active',
        `date_created`       DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── HR Client Organizations (Consultancy Mode) ────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_clients')) {
    $CI->db->query("CREATE TABLE `{$p}hr_clients` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `name`             VARCHAR(200) NOT NULL,
        `industry`         VARCHAR(100) DEFAULT NULL,
        `contact_person`   VARCHAR(200) DEFAULT NULL,
        `email`            VARCHAR(150) DEFAULT NULL,
        `phone`            VARCHAR(50)  DEFAULT NULL,
        `contract_start`   DATE DEFAULT NULL,
        `contract_end`     DATE DEFAULT NULL,
        `billing_model`    ENUM('Per Employee','Per Attendance','Per Payroll','Per Recruitment','Monthly Retainer') DEFAULT 'Monthly Retainer',
        `assigned_hr_mgr`  INT(11) DEFAULT NULL COMMENT 'Staff ID',
        `active`           TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Recruitment: Job Openings ─────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_job_openings')) {
    $CI->db->query("CREATE TABLE `{$p}hr_job_openings` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`           INT(11) NOT NULL DEFAULT 0,
        `department_id`        INT(11) DEFAULT NULL,
        `designation_id`       INT(11) DEFAULT NULL,
        `job_requisition_id`   INT(11) DEFAULT NULL,
        `title`                VARCHAR(200) NOT NULL,
        `description`          LONGTEXT DEFAULT NULL,
        `no_of_positions`      INT(5) DEFAULT 1,
        `expected_salary`      DECIMAL(15,2) DEFAULT NULL,
        `close_date`           DATE DEFAULT NULL,
        `status`               ENUM('Open','Closed','On Hold') DEFAULT 'Open',
        `publish_on_website`   TINYINT(1) NOT NULL DEFAULT 0,
        `date_created`         DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}
// Migrate: add new columns to existing hr_job_openings table
$_jo_new_cols = [
    'job_requisition_id' => "ALTER TABLE `{$p}hr_job_openings` ADD COLUMN `job_requisition_id` INT(11) DEFAULT NULL AFTER `designation_id`",
    'publish_on_website' => "ALTER TABLE `{$p}hr_job_openings` ADD COLUMN `publish_on_website` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`",
];
foreach ($_jo_new_cols as $_col => $_sql) {
    if ($CI->db->table_exists($p . 'hr_job_openings') && !$CI->db->field_exists($_col, $p . 'hr_job_openings')) {
        $CI->db->query($_sql);
    }
}

// ── Recruitment: Applicants ───────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_applicants')) {
    $CI->db->query("CREATE TABLE `{$p}hr_applicants` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `applicant_number` VARCHAR(40) DEFAULT NULL,
        `job_opening_id`  INT(11) DEFAULT NULL,
        `first_name`      VARCHAR(100) NOT NULL,
        `last_name`       VARCHAR(100) NOT NULL,
        `email`           VARCHAR(150) DEFAULT NULL,
        `phone`           VARCHAR(50) DEFAULT NULL,
        `source`          VARCHAR(50) DEFAULT NULL,
        `source_name`     VARCHAR(200) DEFAULT NULL,
        `cover_letter`    LONGTEXT DEFAULT NULL,
        `resume`          VARCHAR(255) DEFAULT NULL,
        `stage`           ENUM('Applied','Screening','Interview','Offer','Hired','Rejected') DEFAULT 'Applied',
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `job_opening_id` (`job_opening_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}
// Migrate: add new columns to existing hr_applicants table
$_app_new_cols = [
    'applicant_number' => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `applicant_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
    'source'           => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `source` VARCHAR(50) DEFAULT NULL AFTER `phone`",
    'source_name'      => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `source_name` VARCHAR(200) DEFAULT NULL AFTER `source`",
    'cover_letter'     => "ALTER TABLE `{$p}hr_applicants` ADD COLUMN `cover_letter` LONGTEXT DEFAULT NULL AFTER `source_name`",
];
foreach ($_app_new_cols as $_col => $_sql) {
    if ($CI->db->table_exists($p . 'hr_applicants') && !$CI->db->field_exists($_col, $p . 'hr_applicants')) {
        $CI->db->query($_sql);
    }
}

// ── Shift Types ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_shift_types')) {
    $CI->db->query("CREATE TABLE `{$p}hr_shift_types` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `name`           VARCHAR(200) NOT NULL,
        `start_time`     TIME NOT NULL,
        `end_time`       TIME NOT NULL,
        `working_hours`  DECIMAL(5,2) DEFAULT 8.00,
        `company_id`     INT(11) DEFAULT 0,
        `active`         TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Attendance ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_attendance')) {
    $CI->db->query("CREATE TABLE `{$p}hr_attendance` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`     INT(11) NOT NULL,
        `shift_id`        INT(11) DEFAULT NULL,
        `attendance_date` DATE NOT NULL,
        `check_in`        DATETIME DEFAULT NULL,
        `check_out`       DATETIME DEFAULT NULL,
        `working_hours`   DECIMAL(5,2) DEFAULT NULL,
        `source`          ENUM('Biometric','RFID','Mobile','Web','Manual') DEFAULT 'Manual',
        `status`          ENUM('Present','Absent','Half Day','On Leave','Holiday') DEFAULT 'Present',
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `attendance_date` (`attendance_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Performance Appraisal Cycles ──────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_appraisal_cycles')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appraisal_cycles` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `start_date`   DATE NOT NULL,
        `end_date`     DATE NOT NULL,
        `status`       ENUM('Draft','Active','Closed') DEFAULT 'Draft',
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Appraisals ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_appraisals')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appraisals` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `cycle_id`     INT(11) NOT NULL,
        `employee_id`  INT(11) NOT NULL,
        `reviewer_id`  INT(11) DEFAULT NULL,
        `score`        DECIMAL(4,2) DEFAULT NULL,
        `rating`       ENUM('Outstanding','Exceeds Expectations','Meets Expectations','Below Expectations','Unsatisfactory') DEFAULT NULL,
        `comments`     TEXT DEFAULT NULL,
        `status`       ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `cycle_id` (`cycle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Performance: Appraisal Templates ─────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_appraisal_templates')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appraisal_templates` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `description`  TEXT DEFAULT NULL,
        `active`       TINYINT(1) DEFAULT 1,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_appraisal_template_criteria')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appraisal_template_criteria` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `template_id`  INT(11) NOT NULL,
        `name`         VARCHAR(200) NOT NULL,
        `description`  TEXT DEFAULT NULL,
        `category`     VARCHAR(100) DEFAULT NULL,
        `weight`       DECIMAL(5,2) DEFAULT 100.00,
        `max_score`    DECIMAL(5,2) DEFAULT 5.00,
        `sort_order`   INT(11) DEFAULT 0,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `template_id` (`template_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_appraisal_scores')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appraisal_scores` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `appraisal_id`    INT(11) NOT NULL,
        `criteria_id`     INT(11) NOT NULL,
        `self_score`      DECIMAL(5,2) DEFAULT NULL,
        `manager_score`   DECIMAL(5,2) DEFAULT NULL,
        `final_score`     DECIMAL(5,2) DEFAULT NULL,
        `self_comment`    TEXT DEFAULT NULL,
        `manager_comment` TEXT DEFAULT NULL,
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `appraisal_id` (`appraisal_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Performance: Goals / OKRs ─────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_goals')) {
    $CI->db->query("CREATE TABLE `{$p}hr_goals` (
        `id`                     INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`            INT(11) NOT NULL,
        `title`                  VARCHAR(255) NOT NULL,
        `description`            TEXT DEFAULT NULL,
        `category`               ENUM('Individual','Team','Company') DEFAULT 'Individual',
        `type`                   ENUM('OKR','KPI','Target') DEFAULT 'KPI',
        `priority`               ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
        `target_value`           DECIMAL(15,4) DEFAULT NULL,
        `current_value`          DECIMAL(15,4) DEFAULT 0.0000,
        `unit`                   VARCHAR(50) DEFAULT NULL,
        `start_date`             DATE DEFAULT NULL,
        `due_date`               DATE DEFAULT NULL,
        `linked_appraisal_cycle` INT(11) DEFAULT NULL,
        `status`                 ENUM('Draft','Active','Completed','Cancelled','Overdue') DEFAULT 'Active',
        `completion_pct`         DECIMAL(5,2) DEFAULT 0.00,
        `date_created`           DATETIME NOT NULL,
        `date_modified`          DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_goal_updates')) {
    $CI->db->query("CREATE TABLE `{$p}hr_goal_updates` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `goal_id`        INT(11) NOT NULL,
        `updated_by`     INT(11) NOT NULL,
        `previous_value` DECIMAL(15,4) DEFAULT NULL,
        `new_value`      DECIMAL(15,4) DEFAULT NULL,
        `note`           TEXT DEFAULT NULL,
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `goal_id` (`goal_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Performance: 360° Feedback ────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_feedback_360')) {
    $CI->db->query("CREATE TABLE `{$p}hr_feedback_360` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `appraisee_id`  INT(11) NOT NULL,
        `cycle_id`      INT(11) DEFAULT NULL,
        `title`         VARCHAR(255) NOT NULL,
        `anonymous`     TINYINT(1) DEFAULT 0,
        `deadline`      DATE DEFAULT NULL,
        `status`        ENUM('Draft','Sent','Completed','Closed') DEFAULT 'Draft',
        `date_created`  DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `appraisee_id` (`appraisee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_feedback_reviewers')) {
    $CI->db->query("CREATE TABLE `{$p}hr_feedback_reviewers` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `feedback_id`         INT(11) NOT NULL,
        `reviewer_type`       ENUM('Self','Peer','Manager','Subordinate','Client') DEFAULT 'Peer',
        `reviewer_employee_id` INT(11) DEFAULT NULL,
        `reviewer_name`       VARCHAR(200) DEFAULT NULL,
        `reviewer_email`      VARCHAR(200) DEFAULT NULL,
        `token`               VARCHAR(64) DEFAULT NULL,
        `submitted`           TINYINT(1) DEFAULT 0,
        `submitted_at`        DATETIME DEFAULT NULL,
        `date_created`        DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `feedback_id` (`feedback_id`),
        KEY `token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_feedback_questions')) {
    $CI->db->query("CREATE TABLE `{$p}hr_feedback_questions` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `feedback_id`   INT(11) NOT NULL,
        `question`      TEXT NOT NULL,
        `question_type` ENUM('rating','text','yes_no') DEFAULT 'rating',
        `sort_order`    INT(11) DEFAULT 0,
        `date_created`  DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `feedback_id` (`feedback_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_feedback_responses')) {
    $CI->db->query("CREATE TABLE `{$p}hr_feedback_responses` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `feedback_id`   INT(11) NOT NULL,
        `reviewer_id`   INT(11) NOT NULL,
        `question_id`   INT(11) NOT NULL,
        `rating`        DECIMAL(3,1) DEFAULT NULL,
        `text_response` TEXT DEFAULT NULL,
        `date_created`  DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `feedback_id` (`feedback_id`),
        KEY `reviewer_id` (`reviewer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Performance: Promotions ───────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_promotions')) {
    $CI->db->query("CREATE TABLE `{$p}hr_promotions` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`          INT(11) NOT NULL,
        `from_designation_id`  INT(11) DEFAULT NULL,
        `to_designation_id`    INT(11) DEFAULT NULL,
        `from_grade_id`        INT(11) DEFAULT NULL,
        `to_grade_id`          INT(11) DEFAULT NULL,
        `from_department_id`   INT(11) DEFAULT NULL,
        `to_department_id`     INT(11) DEFAULT NULL,
        `effective_date`       DATE NOT NULL,
        `salary_before`        DECIMAL(15,2) DEFAULT NULL,
        `salary_after`         DECIMAL(15,2) DEFAULT NULL,
        `reason`               TEXT DEFAULT NULL,
        `approved_by`          INT(11) DEFAULT NULL,
        `status`               ENUM('Draft','Approved','Applied','Cancelled') DEFAULT 'Draft',
        `date_created`         DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Expense Claims ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_expense_claims')) {
    $CI->db->query("CREATE TABLE `{$p}hr_expense_claims` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`  INT(11) NOT NULL,
        `title`        VARCHAR(200) NOT NULL,
        `total_amount` DECIMAL(15,2) DEFAULT 0.00,
        `currency`     VARCHAR(10) DEFAULT 'KES',
        `approved_by`  INT(11) DEFAULT NULL,
        `status`       ENUM('Draft','Submitted','Approved','Rejected','Paid') DEFAULT 'Draft',
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Expense Claim Items ───────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_expense_items')) {
    $CI->db->query("CREATE TABLE `{$p}hr_expense_items` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `claim_id`        INT(11) NOT NULL,
        `category`        VARCHAR(100) DEFAULT NULL,
        `description`     VARCHAR(255) DEFAULT NULL,
        `amount`          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `expense_date`    DATE DEFAULT NULL,
        `receipt`         VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `claim_id` (`claim_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Onboarding ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_onboarding')) {
    $CI->db->query("CREATE TABLE `{$p}hr_onboarding` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`  INT(11) NOT NULL,
        `start_date`   DATE DEFAULT NULL,
        `status`       ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
        `notes`        TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Employee Exit ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_employee_exits')) {
    $CI->db->query("CREATE TABLE `{$p}hr_employee_exits` (
        `id`                INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`       INT(11) NOT NULL,
        `exit_date`         DATE NOT NULL,
        `reason`            VARCHAR(255) DEFAULT NULL,
        `clearance_done`    TINYINT(1) DEFAULT 0,
        `assets_returned`   TINYINT(1) DEFAULT 0,
        `final_settlement`  DECIMAL(15,2) DEFAULT 0.00,
        `interview_notes`   TEXT DEFAULT NULL,
        `date_created`      DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Grievances ────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_grievances')) {
    $CI->db->query("CREATE TABLE `{$p}hr_grievances` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`  INT(11) NOT NULL,
        `type`         VARCHAR(100) DEFAULT NULL,
        `description`  TEXT DEFAULT NULL,
        `status`       ENUM('Open','In Review','Resolved','Closed') DEFAULT 'Open',
        `resolved_by`  INT(11) DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Settings ──────────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_settings')) {
    $CI->db->query("CREATE TABLE `{$p}hr_settings` (
        `id`    INT(11) NOT NULL AUTO_INCREMENT,
        `name`  VARCHAR(100) NOT NULL,
        `value` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");

    // Default settings
    $CI->db->insert($p . 'hr_settings', ['name' => 'employee_number_prefix', 'value' => 'HR-EMP-']);
    $CI->db->insert($p . 'hr_settings', ['name' => 'employee_number_digits', 'value' => '5']);
    $CI->db->insert($p . 'hr_settings', ['name' => 'default_currency', 'value' => 'KES']);
    $CI->db->insert($p . 'hr_settings', ['name' => 'consultancy_mode', 'value' => '0']);
}

// ── Recruitment Schema Additions (ERPNext style) ──────────────────────────────

if (!$CI->db->table_exists($p . 'hr_staffing_plans')) {
    $CI->db->query("CREATE TABLE `{$p}hr_staffing_plans` (
        `id`                     INT(11) NOT NULL AUTO_INCREMENT,
        `name`                   VARCHAR(150) NOT NULL,
        `company_id`             INT(11) NOT NULL,
        `department_id`          INT(11) DEFAULT NULL,
        `from_date`              DATE NOT NULL,
        `to_date`                DATE NOT NULL,
        `total_estimated_budget` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `date_created`           DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_staffing_plan_details')) {
    $CI->db->query("CREATE TABLE `{$p}hr_staffing_plan_details` (
        `id`                           INT(11) NOT NULL AUTO_INCREMENT,
        `staffing_plan_id`             INT(11) NOT NULL,
        `designation_id`               INT(11) NOT NULL,
        `vacancies`                    INT(5) NOT NULL DEFAULT 0,
        `estimated_cost_per_position`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_estimated_cost`         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `number_of_positions`          INT(5) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `staffing_plan_id` (`staffing_plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_job_requisitions')) {
    $CI->db->query("CREATE TABLE `{$p}hr_job_requisitions` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `requisition_number` VARCHAR(40) DEFAULT NULL,
        `company_id`         INT(11) DEFAULT NULL,
        `department_id`      INT(11) DEFAULT NULL,
        `designation_id`     INT(11) DEFAULT NULL,
        `staffing_plan_id`   INT(11) DEFAULT NULL,
        `requested_by`       INT(11) DEFAULT NULL,
        `no_of_positions`    INT(5) NOT NULL DEFAULT 1,
        `expected_salary`    DECIMAL(15,2) DEFAULT NULL,
        `job_description`    LONGTEXT DEFAULT NULL,
        `reason`             TEXT DEFAULT NULL,
        `status`             VARCHAR(30) NOT NULL DEFAULT 'Pending',
        `posting_date`       DATE DEFAULT NULL,
        `expected_by_date`   DATE DEFAULT NULL,
        `date_created`       DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}
// Migrate: add new columns to existing hr_job_requisitions table
$_jreq_new_cols = [
    'requisition_number' => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `requisition_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
    'company_id'         => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `company_id` INT(11) DEFAULT NULL AFTER `requisition_number`",
    'job_description'    => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `job_description` LONGTEXT DEFAULT NULL AFTER `expected_salary`",
    'posting_date'       => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `posting_date` DATE DEFAULT NULL AFTER `status`",
    'expected_by_date'   => "ALTER TABLE `{$p}hr_job_requisitions` ADD COLUMN `expected_by_date` DATE DEFAULT NULL AFTER `posting_date`",
];
foreach ($_jreq_new_cols as $_col => $_sql) {
    if ($CI->db->table_exists($p . 'hr_job_requisitions') && !$CI->db->field_exists($_col, $p . 'hr_job_requisitions')) {
        $CI->db->query($_sql);
    }
}

if (!$CI->db->table_exists($p . 'hr_interview_types')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interview_types` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(100) NOT NULL,
        `description` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_interview_rounds')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interview_rounds` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(100) NOT NULL,
        `description` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_interview_round_skills')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interview_round_skills` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `round_id`     INT(11) NOT NULL,
        `skill_name`   VARCHAR(200) NOT NULL,
        `sort_order`   INT(5) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `round_id` (`round_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_interview_feedback')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interview_feedback` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `feedback_number` VARCHAR(40) DEFAULT NULL,
        `interview_id`    INT(11) NOT NULL,
        `interviewer_id`  INT(11) DEFAULT NULL,
        `result`          VARCHAR(50) NOT NULL DEFAULT 'To Be Discussed',
        `feedback`        TEXT DEFAULT NULL,
        `status`          VARCHAR(20) NOT NULL DEFAULT 'Draft',
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `interview_id` (`interview_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_interview_feedback_skills')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interview_feedback_skills` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `feedback_id` INT(11) NOT NULL,
        `skill_name`  VARCHAR(200) NOT NULL,
        `rating`      TINYINT(1) NOT NULL DEFAULT 0,
        `sort_order`  INT(5) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `feedback_id` (`feedback_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_interviews')) {
    $CI->db->query("CREATE TABLE `{$p}hr_interviews` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `interview_number`   VARCHAR(40) DEFAULT NULL,
        `applicant_id`       INT(11) NOT NULL,
        `job_opening_id`     INT(11) DEFAULT NULL,
        `interview_type_id`  INT(11) DEFAULT NULL,
        `interview_round_id` INT(11) DEFAULT NULL,
        `interviewer_id`     INT(11) DEFAULT NULL,
        `interview_date`     DATE DEFAULT NULL,
        `from_time`          TIME DEFAULT NULL,
        `to_time`            TIME DEFAULT NULL,
        `resume_link`        VARCHAR(500) DEFAULT NULL,
        `rating`             TINYINT(1) DEFAULT NULL,
        `status`             VARCHAR(30) NOT NULL DEFAULT 'Scheduled',
        `result`             ENUM('Pending', 'Pass', 'Fail') DEFAULT 'Pending',
        `comments`           TEXT DEFAULT NULL,
        `date_created`       DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}
// Migrate: add new columns to existing hr_interviews table
$_int_new_cols = [
    'interview_number' => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `interview_number` VARCHAR(40) DEFAULT NULL AFTER `id`",
    'job_opening_id'   => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `job_opening_id` INT(11) DEFAULT NULL AFTER `applicant_id`",
    'from_time'        => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `from_time` TIME DEFAULT NULL AFTER `interview_date`",
    'to_time'          => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `to_time` TIME DEFAULT NULL AFTER `from_time`",
    'resume_link'      => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `resume_link` VARCHAR(500) DEFAULT NULL AFTER `to_time`",
    'rating'           => "ALTER TABLE `{$p}hr_interviews` ADD COLUMN `rating` TINYINT(1) DEFAULT NULL AFTER `resume_link`",
];
foreach ($_int_new_cols as $_col => $_sql) {
    if ($CI->db->table_exists($p . 'hr_interviews') && !$CI->db->field_exists($_col, $p . 'hr_interviews')) {
        $CI->db->query($_sql);
    }
}

if (!$CI->db->table_exists($p . 'hr_job_offers')) {
    $CI->db->query("CREATE TABLE `{$p}hr_job_offers` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `applicant_id`   INT(11) NOT NULL,
        `job_opening_id`  INT(11) DEFAULT NULL,
        `designation_id` INT(11) DEFAULT NULL,
        `salary_offered` DECIMAL(15,2) DEFAULT NULL,
        `joining_date`   DATE DEFAULT NULL,
        `status`         ENUM('Draft', 'Sent', 'Accepted', 'Declined') DEFAULT 'Draft',
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_appointment_letter_templates')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appointment_letter_templates` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(100) NOT NULL,
        `content`      TEXT NOT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

if (!$CI->db->table_exists($p . 'hr_appointment_letters')) {
    $CI->db->query("CREATE TABLE `{$p}hr_appointment_letters` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `applicant_id`   INT(11) NOT NULL,
        `template_id`    INT(11) DEFAULT NULL,
        `letter_content` TEXT NOT NULL,
        `signed_date`    DATE DEFAULT NULL,
        `status`         ENUM('Draft', 'Sent', 'Signed') DEFAULT 'Draft',
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ═══════════════════════════════════════════════════════════════════════════════
// SHIFT & ATTENDANCE TABLES
// ═══════════════════════════════════════════════════════════════════════════════

// ── Migrate hr_shift_types: full version with grace periods, night shift ──────
xetuu_hr_add_column($p . 'hr_shift_types', 'code',              "VARCHAR(20) DEFAULT NULL AFTER `name`");
xetuu_hr_add_column($p . 'hr_shift_types', 'grace_in_mins',     "INT(5) NOT NULL DEFAULT 15 COMMENT 'Minutes late before marked Late'");
xetuu_hr_add_column($p . 'hr_shift_types', 'grace_out_mins',    "INT(5) NOT NULL DEFAULT 15 COMMENT 'Minutes early before marked Early Exit'");
xetuu_hr_add_column($p . 'hr_shift_types', 'min_hours_half_day',"DECIMAL(4,2) NOT NULL DEFAULT 4.00");
xetuu_hr_add_column($p . 'hr_shift_types', 'min_hours_full_day',"DECIMAL(4,2) NOT NULL DEFAULT 8.00");
xetuu_hr_add_column($p . 'hr_shift_types', 'is_night_shift',    "TINYINT(1) NOT NULL DEFAULT 0");
xetuu_hr_add_column($p . 'hr_shift_types', 'night_start',       "TIME DEFAULT NULL");
xetuu_hr_add_column($p . 'hr_shift_types', 'night_end',         "TIME DEFAULT NULL");
xetuu_hr_add_column($p . 'hr_shift_types', 'night_allowance',   "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Flat allowance per night shift'");
xetuu_hr_add_column($p . 'hr_shift_types', 'color',             "VARCHAR(10) DEFAULT '#2563eb'");
xetuu_hr_add_column($p . 'hr_shift_types', 'branch_id',         "INT(11) DEFAULT NULL");
xetuu_hr_add_column($p . 'hr_shift_types', 'description',       "TEXT DEFAULT NULL");

// ── Shift Schedules (recurring patterns: fixed / rotating / flexible) ─────────
if (!$CI->db->table_exists($p . 'hr_shift_schedules')) {
    $CI->db->query("CREATE TABLE `{$p}hr_shift_schedules` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `name`            VARCHAR(200) NOT NULL,
        `type`            ENUM('Fixed','Rotating','Flexible') NOT NULL DEFAULT 'Fixed',
        `rotation_weeks`  TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Number of weeks per rotation cycle',
        `description`     TEXT DEFAULT NULL,
        `branch_id`       INT(11) DEFAULT NULL,
        `active`          TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Shift Schedule Slots (day × week × shift_type) ────────────────────────────
if (!$CI->db->table_exists($p . 'hr_shift_schedule_slots')) {
    $CI->db->query("CREATE TABLE `{$p}hr_shift_schedule_slots` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `schedule_id`     INT(11) NOT NULL,
        `week_number`     TINYINT(1) NOT NULL DEFAULT 1,
        `day_of_week`     TINYINT(1) NOT NULL COMMENT '0=Sun 1=Mon 2=Tue 3=Wed 4=Thu 5=Fri 6=Sat',
        `shift_type_id`   INT(11) DEFAULT NULL COMMENT 'NULL = day off',
        PRIMARY KEY (`id`),
        KEY `schedule_id` (`schedule_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Shift Roster (who works which shift on which date) ────────────────────────
if (!$CI->db->table_exists($p . 'hr_shift_roster')) {
    $CI->db->query("CREATE TABLE `{$p}hr_shift_roster` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`     INT(11) NOT NULL,
        `branch_id`       INT(11) NOT NULL DEFAULT 0,
        `shift_type_id`   INT(11) NOT NULL,
        `schedule_id`     INT(11) DEFAULT NULL,
        `roster_date`     DATE NOT NULL,
        `status`          ENUM('Scheduled','Cancelled','Swapped') DEFAULT 'Scheduled',
        `notes`           TEXT DEFAULT NULL,
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_employee_date` (`employee_id`, `roster_date`),
        KEY `roster_date` (`roster_date`),
        KEY `branch_id`   (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Attendance Logs (raw punch events — one row per IN or OUT event) ──────────
if (!$CI->db->table_exists($p . 'hr_attendance_logs')) {
    $CI->db->query("CREATE TABLE `{$p}hr_attendance_logs` (
        `id`            INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`   INT(11) NOT NULL,
        `branch_id`     INT(11) NOT NULL DEFAULT 0,
        `log_datetime`  DATETIME NOT NULL,
        `log_type`      ENUM('IN','OUT') NOT NULL DEFAULT 'IN',
        `method`        ENUM('Manual','Mobile','Biometric','RFID','Excel Import') DEFAULT 'Manual',
        `device_id`     VARCHAR(100) DEFAULT NULL,
        `import_id`     INT(11) DEFAULT NULL COMMENT 'tblhr_timesheet_imports.id',
        `notes`         TEXT DEFAULT NULL,
        `date_created`  DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id`  (`employee_id`),
        KEY `log_datetime` (`log_datetime`),
        KEY `branch_id`    (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Daily Attendance (computed record: one row per employee per day) ───────────
if (!$CI->db->table_exists($p . 'hr_daily_attendance')) {
    $CI->db->query("CREATE TABLE `{$p}hr_daily_attendance` (
        `id`                    INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`           INT(11) NOT NULL,
        `branch_id`             INT(11) NOT NULL DEFAULT 0,
        `attendance_date`       DATE NOT NULL,
        `shift_type_id`         INT(11) DEFAULT NULL,
        `check_in`              DATETIME DEFAULT NULL,
        `check_out`             DATETIME DEFAULT NULL,
        `working_hours`         DECIMAL(5,2) DEFAULT 0.00,
        `late_minutes`          INT(5) DEFAULT 0,
        `early_departure_mins`  INT(5) DEFAULT 0,
        `overtime_hours`        DECIMAL(5,2) DEFAULT 0.00,
        `overtime_type_id`      INT(11) DEFAULT NULL,
        `status`                VARCHAR(30) NOT NULL DEFAULT 'Absent'
                                COMMENT 'Present|Late|Absent|Half Day|On Leave|Holiday|Weekend|No Show',
        `leave_id`              INT(11) DEFAULT NULL,
        `source`                ENUM('Auto','Manual','Excel Import','Bulk Tool') DEFAULT 'Auto',
        `notes`                 TEXT DEFAULT NULL,
        `date_created`          DATETIME NOT NULL,
        `date_modified`         DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_emp_date` (`employee_id`, `attendance_date`),
        KEY `attendance_date` (`attendance_date`),
        KEY `branch_id`       (`branch_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Overtime Types ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_overtime_types')) {
    $CI->db->query("CREATE TABLE `{$p}hr_overtime_types` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `name`                 VARCHAR(200) NOT NULL,
        `applicable_on`        SET('Weekday','Saturday','Sunday','Holiday','Night','On-Call')
                               NOT NULL DEFAULT 'Weekday',
        `multiplier`           DECIMAL(4,2) NOT NULL DEFAULT 1.50
                               COMMENT 'Pay multiplier e.g. 1.5 = time-and-a-half',
        `min_threshold_mins`   INT(5) NOT NULL DEFAULT 30
                               COMMENT 'Must work at least this many mins beyond shift to qualify',
        `max_hours_per_day`    DECIMAL(4,2) DEFAULT NULL COMMENT 'NULL = no cap',
        `max_hours_per_week`   DECIMAL(5,2) DEFAULT NULL,
        `max_hours_per_month`  DECIMAL(6,2) DEFAULT NULL,
        `toil_enabled`         TINYINT(1) NOT NULL DEFAULT 0
                               COMMENT 'Can OT be compensated as Time Off in Lieu?',
        `toil_multiplier`      DECIMAL(4,2) DEFAULT 1.00
                               COMMENT '1 hr OT = toil_multiplier hrs leave credit',
        `description`          TEXT DEFAULT NULL,
        `active`               TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`         DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");

    // Seed standard OT types
    $now = date('Y-m-d H:i:s');
    $ot_seeds = [
        ['name'=>'Weekday Overtime',    'applicable_on'=>'Weekday',           'multiplier'=>1.50, 'min_threshold_mins'=>30, 'toil_enabled'=>1, 'toil_multiplier'=>1.50],
        ['name'=>'Saturday Overtime',   'applicable_on'=>'Saturday',          'multiplier'=>1.50, 'min_threshold_mins'=>0,  'toil_enabled'=>1, 'toil_multiplier'=>1.50],
        ['name'=>'Sunday Overtime',     'applicable_on'=>'Sunday',            'multiplier'=>2.00, 'min_threshold_mins'=>0,  'toil_enabled'=>1, 'toil_multiplier'=>2.00],
        ['name'=>'Public Holiday OT',   'applicable_on'=>'Holiday',           'multiplier'=>2.00, 'min_threshold_mins'=>0,  'toil_enabled'=>0, 'toil_multiplier'=>1.00],
        ['name'=>'Night Differential',  'applicable_on'=>'Night',             'multiplier'=>1.25, 'min_threshold_mins'=>60, 'toil_enabled'=>0, 'toil_multiplier'=>1.00],
        ['name'=>'On-Call Overtime',    'applicable_on'=>'On-Call',           'multiplier'=>1.50, 'min_threshold_mins'=>0,  'toil_enabled'=>1, 'toil_multiplier'=>1.00],
    ];
    foreach ($ot_seeds as $ot) {
        $CI->db->insert($p . 'hr_overtime_types', array_merge($ot, [
            'max_hours_per_day'=>null, 'max_hours_per_week'=>null, 'max_hours_per_month'=>null,
            'description'=>null, 'active'=>1, 'date_created'=>$now,
        ]));
    }
}

// ── Overtime Slips ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_overtime_slips')) {
    $CI->db->query("CREATE TABLE `{$p}hr_overtime_slips` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `slip_number`         VARCHAR(40) DEFAULT NULL,
        `employee_id`         INT(11) NOT NULL,
        `branch_id`           INT(11) NOT NULL DEFAULT 0,
        `overtime_date`       DATE NOT NULL,
        `shift_type_id`       INT(11) DEFAULT NULL,
        `overtime_type_id`    INT(11) NOT NULL,
        `regular_hours`       DECIMAL(5,2) DEFAULT 0.00,
        `overtime_hours`      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `rate_multiplier`     DECIMAL(4,2) NOT NULL DEFAULT 1.50,
        `compensation_mode`   ENUM('Pay','TOIL','Both') DEFAULT 'Pay',
        `toil_hours_credited` DECIMAL(5,2) DEFAULT 0.00,
        `daily_attendance_id` INT(11) DEFAULT NULL,
        `status`              ENUM('Draft','Pending','Approved','Rejected','Paid') DEFAULT 'Draft',
        `approved_by`         INT(11) DEFAULT NULL,
        `approved_on`         DATETIME DEFAULT NULL,
        `notes`               TEXT DEFAULT NULL,
        `date_created`        DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id`    (`employee_id`),
        KEY `overtime_date`  (`overtime_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Shift Requests (swap / change / day-off request) ─────────────────────────
if (!$CI->db->table_exists($p . 'hr_shift_requests')) {
    $CI->db->query("CREATE TABLE `{$p}hr_shift_requests` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`      INT(11) NOT NULL,
        `request_type`     ENUM('Swap','Change','Day Off') NOT NULL DEFAULT 'Change',
        `from_shift_id`    INT(11) DEFAULT NULL,
        `to_shift_id`      INT(11) DEFAULT NULL,
        `request_date`     DATE NOT NULL,
        `to_date`          DATE DEFAULT NULL COMMENT 'For multi-day requests',
        `swap_with_emp_id` INT(11) DEFAULT NULL COMMENT 'For Swap requests',
        `reason`           TEXT DEFAULT NULL,
        `status`           ENUM('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
        `approved_by`      INT(11) DEFAULT NULL,
        `approved_on`      DATETIME DEFAULT NULL,
        `notes`            TEXT DEFAULT NULL,
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id`  (`employee_id`),
        KEY `request_date` (`request_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Attendance Requests (employee correction) ─────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_attendance_requests')) {
    $CI->db->query("CREATE TABLE `{$p}hr_attendance_requests` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`          INT(11) NOT NULL,
        `attendance_date`      DATE NOT NULL,
        `daily_attendance_id`  INT(11) DEFAULT NULL,
        `requested_check_in`   DATETIME DEFAULT NULL,
        `requested_check_out`  DATETIME DEFAULT NULL,
        `requested_status`     VARCHAR(30) DEFAULT NULL,
        `reason`               TEXT NOT NULL,
        `document_path`        VARCHAR(255) DEFAULT NULL,
        `status`               ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        `approved_by`          INT(11) DEFAULT NULL,
        `approved_on`          DATETIME DEFAULT NULL,
        `notes`                TEXT DEFAULT NULL,
        `date_created`         DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id`     (`employee_id`),
        KEY `attendance_date` (`attendance_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Excel Timesheet Imports ───────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_timesheet_imports')) {
    $CI->db->query("CREATE TABLE `{$p}hr_timesheet_imports` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `import_number`    VARCHAR(40) DEFAULT NULL,
        `filename`         VARCHAR(255) NOT NULL,
        `original_name`    VARCHAR(255) DEFAULT NULL,
        `branch_id`        INT(11) NOT NULL DEFAULT 0,
        `pay_period_month` TINYINT(2) NOT NULL,
        `pay_period_year`  SMALLINT(4) NOT NULL,
        `uploaded_by`      INT(11) NOT NULL,
        `imported_at`      DATETIME NOT NULL,
        `total_rows`       INT(6) DEFAULT 0,
        `success_rows`     INT(6) DEFAULT 0,
        `warning_rows`     INT(6) DEFAULT 0,
        `error_rows`       INT(6) DEFAULT 0,
        `column_mapping`   TEXT DEFAULT NULL COMMENT 'JSON: user column → system field',
        `status`           ENUM('Pending','Processing','Completed','Failed') DEFAULT 'Pending',
        `notes`            TEXT DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Excel Import Row Log ──────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_timesheet_import_rows')) {
    $CI->db->query("CREATE TABLE `{$p}hr_timesheet_import_rows` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `import_id`        INT(11) NOT NULL,
        `row_number`       INT(6) NOT NULL,
        `employee_id`      INT(11) DEFAULT NULL,
        `raw_employee`     VARCHAR(200) DEFAULT NULL COMMENT 'Original value from Excel',
        `attendance_date`  DATE DEFAULT NULL,
        `check_in`         TIME DEFAULT NULL,
        `check_out`        TIME DEFAULT NULL,
        `hours_worked`     DECIMAL(5,2) DEFAULT NULL,
        `ot_hours`         DECIMAL(5,2) DEFAULT NULL,
        `project`          VARCHAR(200) DEFAULT NULL,
        `notes`            TEXT DEFAULT NULL,
        `status`           ENUM('Success','Warning','Error','Skipped') DEFAULT 'Skipped',
        `error_message`    TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `import_id` (`import_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Attendance Settings defaults ──────────────────────────────────────────────
$att_defaults = [
    'att_working_days'          => 'Mon,Tue,Wed,Thu,Fri',
    'att_working_hours'         => '8',
    'att_grace_in_mins'         => '15',
    'att_grace_out_mins'        => '15',
    'att_auto_compute'          => '1',
    'att_absent_alert'          => '1',
    'att_absent_alert_hour'     => '10',
    'att_ot_weekday_mult'       => '1.5',
    'att_ot_weekend_mult'       => '2.0',
    'att_ot_holiday_mult'       => '2.0',
    'att_ot_min_threshold_mins' => '30',
    'att_toil_enabled'          => '1',
];
foreach ($att_defaults as $k => $v) {
    $exists = $CI->db->where('name', $k)->get($p . 'hr_settings')->num_rows();
    if (!$exists) {
        $CI->db->insert($p . 'hr_settings', ['name' => $k, 'value' => $v]);
    }
}

// Seeding happens once, at the end of this file, after every table (including
// hr_leave_types below) has been created — see the require_once at EOF.
// A duplicate early require_once here used to crash on hr_leave_types, which
// isn't created until later in this same file.

// ═══════════════════════════════════════════════════════════════════════════════
// PAYROLL SUB-MODULE TABLES
// ═══════════════════════════════════════════════════════════════════════════════

// ── Payroll Companies (linked to tblclients) ──────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_companies')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_companies` (
        `id`                    INT(11) NOT NULL AUTO_INCREMENT,
        `client_id`             INT(11) DEFAULT NULL COMMENT 'Links to tblclients.userid',
        `name`                  VARCHAR(200) NOT NULL,
        `reg_number`            VARCHAR(100) DEFAULT NULL,
        `country_code`          VARCHAR(5) NOT NULL DEFAULT 'KE',
        `currency`              VARCHAR(10) NOT NULL DEFAULT 'KES',
        `payroll_addon_id`      INT(11) DEFAULT NULL,
        `xetuu_books_company_id` INT(11) DEFAULT NULL COMMENT 'Xetuu Books link',
        `tax_reg_number`        VARCHAR(100) DEFAULT NULL COMMENT 'Company tax registration (any country)',
        `social_sec_number`     VARCHAR(100) DEFAULT NULL COMMENT 'Social security / pension fund reg',
        `health_fund_number`    VARCHAR(100) DEFAULT NULL COMMENT 'Health insurance / fund reg',
        `settings`              TEXT DEFAULT NULL COMMENT 'JSON — addon-specific settings injected by country addon',
        `active`                TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`          DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `client_id` (`client_id`),
        KEY `payroll_addon_id` (`payroll_addon_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Pay Frequencies ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_pay_frequencies')) {
    $CI->db->query("CREATE TABLE `{$p}hr_pay_frequencies` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`     INT(11) NOT NULL DEFAULT 0 COMMENT '0 = global',
        `name`           VARCHAR(100) NOT NULL,
        `interval_type`  ENUM('month','week','biweek') NOT NULL DEFAULT 'month',
        `interval_count` TINYINT(3) NOT NULL DEFAULT 1,
        `pay_day`        TINYINT(3) NOT NULL DEFAULT 28 COMMENT 'Day of month or week (1=Mon)',
        `active`         TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
    // Seed defaults
    $CI->db->insert($p . 'hr_pay_frequencies', ['company_id'=>0,'name'=>'Monthly','interval_type'=>'month','interval_count'=>1,'pay_day'=>28,'active'=>1]);
    $CI->db->insert($p . 'hr_pay_frequencies', ['company_id'=>0,'name'=>'Weekly','interval_type'=>'week','interval_count'=>1,'pay_day'=>5,'active'=>1]);
    $CI->db->insert($p . 'hr_pay_frequencies', ['company_id'=>0,'name'=>'Bi-Weekly','interval_type'=>'biweek','interval_count'=>2,'pay_day'=>5,'active'=>1]);
}

// ── Salary Structures ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_salary_structures')) {
    $CI->db->query("CREATE TABLE `{$p}hr_salary_structures` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`       INT(11) NOT NULL DEFAULT 0 COMMENT '0 = global',
        `name`             VARCHAR(200) NOT NULL,
        `code`             VARCHAR(50) NOT NULL,
        `pay_frequency_id` INT(11) NOT NULL DEFAULT 1,
        `description`      TEXT DEFAULT NULL,
        `active`           TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`),
        KEY `pay_frequency_id` (`pay_frequency_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Salary Rules ──────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_salary_rules')) {
    $CI->db->query("CREATE TABLE `{$p}hr_salary_rules` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `structure_id`       INT(11) NOT NULL,
        `code`               VARCHAR(50) NOT NULL,
        `name`               VARCHAR(200) NOT NULL,
        `sequence`           INT(6) NOT NULL DEFAULT 10,
        `category`           ENUM('EARN','DED','TAX','NET','EMPLOYER') NOT NULL DEFAULT 'EARN',
        `condition_formula`  TEXT DEFAULT NULL COMMENT 'Symfony expression; empty = always',
        `amount_formula`     TEXT NOT NULL COMMENT 'Symfony expression',
        `appears_on_payslip` TINYINT(1) NOT NULL DEFAULT 1,
        `is_addon_rule`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Injected by country addon',
        `addon_id`           INT(11) DEFAULT NULL,
        `active`             TINYINT(1) NOT NULL DEFAULT 1,
        `date_created`       DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `structure_id` (`structure_id`),
        UNIQUE KEY `uq_struct_code` (`structure_id`,`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Contracts (payroll-extended) ──────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_contracts')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_contracts` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`      INT(11) NOT NULL,
        `company_id`       INT(11) NOT NULL DEFAULT 0,
        `wage`             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `pay_frequency_id` INT(11) NOT NULL DEFAULT 1,
        `structure_id`     INT(11) DEFAULT NULL,
        `employment_type`  ENUM('permanent','contract','casual','internship') NOT NULL DEFAULT 'permanent',
        `date_start`       DATE NOT NULL,
        `date_end`         DATE DEFAULT NULL,
        `status`           ENUM('draft','active','expired','cancelled') NOT NULL DEFAULT 'active',
        `payment_method`   ENUM('bank','mpesa','cash') NOT NULL DEFAULT 'bank',
        `bank_name`        VARCHAR(100) DEFAULT NULL,
        `bank_account`     VARCHAR(100) DEFAULT NULL,
        `bank_branch`      VARCHAR(100) DEFAULT NULL,
        `mpesa_number`     VARCHAR(20) DEFAULT NULL,
        `tax_id`           VARCHAR(50) DEFAULT NULL COMMENT 'Employee tax ID — KRA PIN / TIN / SSN / RFC etc.',
        `working_days`     TINYINT(1) NOT NULL DEFAULT 5,
        `notes`            TEXT DEFAULT NULL,
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `company_id` (`company_id`),
        KEY `structure_id` (`structure_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Migrate: add generic fields to contracts if missing ───────────────────────
if ($CI->db->table_exists($p . 'hr_payroll_contracts')) {
    $fields      = $CI->db->field_data($p . 'hr_payroll_contracts');
    $field_names = array_map(fn($f) => $f->name, $fields);
    // Rename kra_pin → tax_id if the old column still exists
    if (in_array('kra_pin', $field_names) && !in_array('tax_id', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_contracts` CHANGE COLUMN `kra_pin` `tax_id` VARCHAR(50) DEFAULT NULL COMMENT 'Employee tax ID — KRA PIN / TIN / SSN / RFC etc.'");
    } elseif (!in_array('tax_id', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_contracts` ADD COLUMN `tax_id` VARCHAR(50) DEFAULT NULL COMMENT 'Employee tax ID' AFTER `mpesa_number`");
    }
    if (!in_array('working_days', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_contracts` ADD COLUMN `working_days` TINYINT(1) NOT NULL DEFAULT 5 AFTER `tax_id`");
    }
}
// ── Migrate: payroll_companies — replace Kenya columns with generic ones ───────
if ($CI->db->table_exists($p . 'hr_payroll_companies')) {
    $fields      = $CI->db->field_data($p . 'hr_payroll_companies');
    $field_names = array_map(fn($f) => $f->name, $fields);
    if (in_array('kra_pin', $field_names))        { $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` DROP COLUMN `kra_pin`"); }
    if (in_array('nssf_number', $field_names))    { $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` DROP COLUMN `nssf_number`"); }
    if (in_array('nhif_sha_number', $field_names)){ $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` DROP COLUMN `nhif_sha_number`"); }
    if (in_array('nita_number', $field_names))    { $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` DROP COLUMN `nita_number`"); }
    if (!in_array('tax_reg_number', $field_names)){
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` ADD COLUMN `tax_reg_number` VARCHAR(100) DEFAULT NULL COMMENT 'Company tax reg (any country)'");
    }
    if (!in_array('social_sec_number', $field_names)){
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` ADD COLUMN `social_sec_number` VARCHAR(100) DEFAULT NULL");
    }
    if (!in_array('health_fund_number', $field_names)){
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_companies` ADD COLUMN `health_fund_number` VARCHAR(100) DEFAULT NULL");
    }
}
// ── Migrate: payroll_addons — add addon_type if missing ───────────────────────
if ($CI->db->table_exists($p . 'hr_payroll_addons')) {
    $fields      = $CI->db->field_data($p . 'hr_payroll_addons');
    $field_names = array_map(fn($f) => $f->name, $fields);
    if (!in_array('addon_type', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_payroll_addons` ADD COLUMN `addon_type` ENUM('php','excel','csv') NOT NULL DEFAULT 'php' AFTER `country_name`");
    }
}
// ── Migrate: hr_employees — rename Kenya-specific columns to generic ones ─────
if ($CI->db->table_exists($p . 'hr_employees')) {
    $fields      = $CI->db->field_data($p . 'hr_employees');
    $field_names = array_map(fn($f) => $f->name, $fields);
    // nssf_number → social_sec_number
    if (in_array('nssf_number', $field_names) && !in_array('social_sec_number', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` CHANGE COLUMN `nssf_number` `social_sec_number` VARCHAR(50) DEFAULT NULL");
    } elseif (!in_array('social_sec_number', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` ADD COLUMN `social_sec_number` VARCHAR(50) DEFAULT NULL AFTER `disability_status`");
    }
    // nhif_sha_number → health_fund_number
    if (in_array('nhif_sha_number', $field_names) && !in_array('health_fund_number', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` CHANGE COLUMN `nhif_sha_number` `health_fund_number` VARCHAR(50) DEFAULT NULL");
    } elseif (!in_array('health_fund_number', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` ADD COLUMN `health_fund_number` VARCHAR(50) DEFAULT NULL AFTER `social_sec_number`");
    }
    // tax_pin → tax_id
    if (in_array('tax_pin', $field_names) && !in_array('tax_id', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` CHANGE COLUMN `tax_pin` `tax_id` VARCHAR(50) DEFAULT NULL");
    } elseif (!in_array('tax_id', $field_names)) {
        $CI->db->query("ALTER TABLE `{$p}hr_employees` ADD COLUMN `tax_id` VARCHAR(50) DEFAULT NULL AFTER `health_fund_number`");
    }
}

// ── Contract Lines (benefits + deductions per contract) ───────────────────────
if (!$CI->db->table_exists($p . 'hr_contract_lines')) {
    $CI->db->query("CREATE TABLE `{$p}hr_contract_lines` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `contract_id`     INT(11) NOT NULL,
        `line_type`       ENUM('benefit','deduction') NOT NULL DEFAULT 'benefit',
        `name`            VARCHAR(200) NOT NULL,
        `code`            VARCHAR(50) DEFAULT NULL COMMENT 'e.g. HOUSE, TRANS, HELB',
        `amount`          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `is_recurring`    TINYINT(1) NOT NULL DEFAULT 1,
        `statutory_ref`   VARCHAR(50) DEFAULT NULL COMMENT 'HELB|SACCO|LOAN|INSURANCE|OTHER',
        `notes`           VARCHAR(255) DEFAULT NULL,
        `active`          TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `contract_id` (`contract_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Periods ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_periods')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_periods` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`       INT(11) NOT NULL DEFAULT 0,
        `name`             VARCHAR(100) NOT NULL,
        `pay_frequency_id` INT(11) NOT NULL DEFAULT 1,
        `date_from`        DATE NOT NULL,
        `date_to`          DATE NOT NULL,
        `status`           ENUM('open','closed') NOT NULL DEFAULT 'open',
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`),
        KEY `date_from` (`date_from`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Runs (batch header) ───────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_runs')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_runs` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`          INT(11) NOT NULL DEFAULT 0,
        `period_id`           INT(11) DEFAULT NULL,
        `name`                VARCHAR(200) NOT NULL,
        `date_from`           DATE NOT NULL,
        `date_to`             DATE NOT NULL,
        `state`               ENUM('draft','computing','computed','confirmed','done','paid','cancelled') NOT NULL DEFAULT 'draft',
        `employee_count`      INT(6) NOT NULL DEFAULT 0,
        `computed_count`      INT(6) NOT NULL DEFAULT 0,
        `computing_chunk`     INT(6) NOT NULL DEFAULT 0,
        `computing_started_at` DATETIME DEFAULT NULL,
        `locked_by`           INT(11) DEFAULT NULL COMMENT 'Staff ID currently computing',
        `total_gross`         DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        `total_net`           DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        `total_deductions`    DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        `total_employer`      DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        `journal_entry_id`    INT(11) DEFAULT NULL,
        `payment_journal_id`  INT(11) DEFAULT NULL,
        `notes`               TEXT DEFAULT NULL,
        `created_by`          INT(11) NOT NULL DEFAULT 0,
        `confirmed_by`        INT(11) DEFAULT NULL,
        `paid_by`             INT(11) DEFAULT NULL,
        `confirmed_at`        DATETIME DEFAULT NULL,
        `paid_at`             DATETIME DEFAULT NULL,
        `date_created`        DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`),
        KEY `period_id` (`period_id`),
        KEY `state` (`state`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Individual Payslips ───────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payslips')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payslips` (
        `id`                    INT(11) NOT NULL AUTO_INCREMENT,
        `run_id`                INT(11) DEFAULT NULL COMMENT 'NULL = single payslip',
        `company_id`            INT(11) NOT NULL DEFAULT 0,
        `employee_id`           INT(11) NOT NULL,
        `contract_id`           INT(11) DEFAULT NULL,
        `structure_id`          INT(11) DEFAULT NULL,
        `period_id`             INT(11) DEFAULT NULL,
        `date_from`             DATE NOT NULL,
        `date_to`               DATE NOT NULL,
        `state`                 ENUM('draft','computed','confirmed','done','paid','cancelled') NOT NULL DEFAULT 'draft',
        `gross_wage`            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `net_wage`              DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_deductions`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_employer`        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_tax`             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `working_days`          DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `worked_days`           DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `email_sent`            TINYINT(1) NOT NULL DEFAULT 0,
        `email_sent_at`         DATETIME DEFAULT NULL,
        `pdf_path`              VARCHAR(500) DEFAULT NULL,
        `notes`                 TEXT DEFAULT NULL,
        `date_created`          DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `run_id` (`run_id`),
        KEY `employee_id` (`employee_id`),
        KEY `company_id` (`company_id`),
        KEY `date_from` (`date_from`),
        UNIQUE KEY `uq_run_employee` (`run_id`,`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// Auto-patch hr_payslips: add columns added after initial release
$_payslip_patches = [
    'journal_entry_id' => 'INT(11) DEFAULT NULL',
    'reference'        => "VARCHAR(50) DEFAULT NULL",
];
foreach ($_payslip_patches as $_col => $_def) {
    if (!$CI->db->field_exists($_col, $p . 'hr_payslips')) {
        $CI->db->query("ALTER TABLE `{$p}hr_payslips` ADD COLUMN `{$_col}` {$_def}");
    }
}
unset($_payslip_patches, $_col, $_def);

// ── Payslip Lines (computed rule results) ─────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payslip_lines')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payslip_lines` (
        `id`                 INT(11) NOT NULL AUTO_INCREMENT,
        `payslip_id`         INT(11) NOT NULL,
        `rule_id`            INT(11) DEFAULT NULL,
        `rule_code`          VARCHAR(50) NOT NULL,
        `rule_name`          VARCHAR(200) NOT NULL,
        `category`           ENUM('EARN','DED','TAX','NET','EMPLOYER','LOAN') NOT NULL,
        `sequence`           INT(6) NOT NULL DEFAULT 10,
        `quantity`           DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
        `rate`               DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
        `amount`             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `appears_on_payslip` TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `payslip_id` (`payslip_id`),
        KEY `category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Work Entries (attendance/leave inputs for payroll) ────────────────────────
if (!$CI->db->table_exists($p . 'hr_work_entries')) {
    $CI->db->query("CREATE TABLE `{$p}hr_work_entries` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`     INT(11) NOT NULL,
        `company_id`      INT(11) NOT NULL DEFAULT 0,
        `period_id`       INT(11) DEFAULT NULL,
        `entry_type`      ENUM('attendance','leave','sick','overtime','unpaid','public_holiday') NOT NULL DEFAULT 'attendance',
        `date_from`       DATE NOT NULL,
        `date_to`         DATE NOT NULL,
        `days`            DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `hours`           DECIMAL(7,2) NOT NULL DEFAULT 0.00,
        `source`          ENUM('manual','attendance_module','leave_module','upload') NOT NULL DEFAULT 'manual',
        `upload_batch_id` INT(11) DEFAULT NULL,
        `approved`        TINYINT(1) NOT NULL DEFAULT 0,
        `approved_by`     INT(11) DEFAULT NULL,
        `approved_at`     DATETIME DEFAULT NULL,
        `notes`           TEXT DEFAULT NULL,
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `period_id` (`period_id`),
        KEY `date_from` (`date_from`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Timesheet Uploads ──────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_uploads')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_uploads` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `company_id`     INT(11) NOT NULL DEFAULT 0,
        `period_id`      INT(11) DEFAULT NULL,
        `filename`       VARCHAR(255) NOT NULL,
        `original_name`  VARCHAR(255) DEFAULT NULL,
        `uploaded_by`    INT(11) NOT NULL,
        `records_total`  INT(6) NOT NULL DEFAULT 0,
        `records_valid`  INT(6) NOT NULL DEFAULT 0,
        `records_error`  INT(6) NOT NULL DEFAULT 0,
        `status`         ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
        `error_log`      TEXT DEFAULT NULL COMMENT 'JSON array of errors',
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Addons Registry ────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_addons')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_addons` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `addon_id`     VARCHAR(100) NOT NULL COMMENT 'e.g. kenya_payroll',
        `name`         VARCHAR(200) NOT NULL,
        `version`      VARCHAR(20) NOT NULL DEFAULT '1.0.0',
        `country_code` VARCHAR(5) NOT NULL,
        `country_name` VARCHAR(100) DEFAULT NULL,
        `file_path`    VARCHAR(500) DEFAULT NULL,
        `manifest`     TEXT DEFAULT NULL COMMENT 'JSON manifest',
        `addon_type`   ENUM('php','excel','csv') NOT NULL DEFAULT 'php' COMMENT 'php=developer class; excel/csv=tax table upload',
        `status`       ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
        `installed_at` DATETIME DEFAULT NULL,
        `installed_by` INT(11) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_addon_id` (`addon_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Addon Settings ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_addon_settings')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_addon_settings` (
        `id`         INT(11) NOT NULL AUTO_INCREMENT,
        `addon_id`   VARCHAR(100) NOT NULL,
        `company_id` INT(11) NOT NULL DEFAULT 0,
        `key`        VARCHAR(100) NOT NULL,
        `value`      TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_addon_company_key` (`addon_id`,`company_id`,`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Email Queue ────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_email_queue')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_email_queue` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `payslip_id`  INT(11) NOT NULL,
        `employee_id` INT(11) NOT NULL,
        `email_to`    VARCHAR(200) NOT NULL,
        `status`      ENUM('pending','sent','failed','bounced') NOT NULL DEFAULT 'pending',
        `attempts`    TINYINT(3) NOT NULL DEFAULT 0,
        `sent_at`     DATETIME DEFAULT NULL,
        `error_msg`   TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `payslip_id` (`payslip_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Payroll Accounting Journals ────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_payroll_journals')) {
    $CI->db->query("CREATE TABLE `{$p}hr_payroll_journals` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `run_id`              INT(11) NOT NULL,
        `xetuu_books_entry_id` INT(11) DEFAULT NULL,
        `entry_type`          ENUM('expense','payment') NOT NULL DEFAULT 'expense',
        `total_amount`        DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        `currency`            VARCHAR(10) NOT NULL DEFAULT 'KES',
        `date_posted`         DATE NOT NULL,
        `posted_by`           INT(11) NOT NULL,
        `notes`               TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `run_id` (`run_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");}

// ── Employee Loans ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_loans')) {
    $CI->db->query("CREATE TABLE `{$p}hr_loans` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`          INT(11) NOT NULL,
        `company_id`           INT(11) NOT NULL DEFAULT 0,
        `loan_type`            ENUM('salary_advance','helb','equipment','emergency','other') NOT NULL DEFAULT 'other',
        `loan_reference`       VARCHAR(100) DEFAULT NULL,
        `description`          TEXT DEFAULT NULL,
        `principal_amount`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `interest_rate`        DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Annual percentage. 0 = interest-free.',
        `monthly_installment`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_repayable`      DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Principal + projected total interest',
        `balance_remaining`    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `disbursement_date`    DATE DEFAULT NULL,
        `start_deduction_date` DATE DEFAULT NULL COMMENT 'First payroll month to deduct',
        `expected_end_date`    DATE DEFAULT NULL,
        `status`               ENUM('active','suspended','paid','written_off','cancelled') NOT NULL DEFAULT 'active',
        `suspension_reason`    TEXT DEFAULT NULL,
        `suspended_by`         INT(11) DEFAULT NULL,
        `suspended_at`         DATETIME DEFAULT NULL,
        `notes`                TEXT DEFAULT NULL,
        `created_by`           INT(11) DEFAULT NULL,
        `date_created`         DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `company_id` (`company_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Loan Repayments ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_loan_repayments')) {
    $CI->db->query("CREATE TABLE `{$p}hr_loan_repayments` (
        `id`                INT(11) NOT NULL AUTO_INCREMENT,
        `loan_id`           INT(11) NOT NULL,
        `employee_id`       INT(11) NOT NULL,
        `payslip_id`        INT(11) DEFAULT NULL COMMENT 'NULL for manual payments',
        `amount`            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `principal_portion` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `interest_portion`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `balance_before`    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `balance_after`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `repayment_date`    DATE NOT NULL,
        `repayment_type`    ENUM('payroll','manual','lump_sum') NOT NULL DEFAULT 'payroll',
        `notes`             VARCHAR(255) DEFAULT NULL,
        `created_by`        INT(11) DEFAULT NULL,
        `date_created`      DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `loan_id` (`loan_id`),
        KEY `payslip_id` (`payslip_id`),
        KEY `employee_id` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── cash_payable column on payslips (post-loan net) ───────────────────────────
xetuu_hr_add_column($p . 'hr_payslips', 'cash_payable', 'DECIMAL(15,2) DEFAULT NULL COMMENT "net_wage minus loan repayments"');

// ── Expand payslip_lines.category ENUM to include LOAN ────────────────────────
// Safe on existing installs; no-ops if already set.
$CI->db->query("ALTER TABLE `{$p}hr_payslip_lines`
    MODIFY COLUMN `category` ENUM('EARN','DED','TAX','NET','EMPLOYER','LOAN') NOT NULL");

// ── Payroll addon folder ───────────────────────────────────────────────────────
$addon_dir = APPPATH . '../modules/xetuu_hr/payroll_addons/';
if (!is_dir($addon_dir)) { mkdir($addon_dir, 0755, true); }
$uploads_dir = APPPATH . '../modules/xetuu_hr/payroll_addons/uploads/';
if (!is_dir($uploads_dir)) { mkdir($uploads_dir, 0755, true); }

// ══════════════════════════════════════════════════════════════════════════════
// Leave Management Tables
// ══════════════════════════════════════════════════════════════════════════════

// ── Holiday Lists ─────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_holiday_lists')) {
    $CI->db->query("CREATE TABLE `{$p}hr_holiday_lists` (
        `id`           INT(11) NOT NULL AUTO_INCREMENT,
        `name`         VARCHAR(200) NOT NULL,
        `country_code` VARCHAR(5) DEFAULT NULL,
        `year`         YEAR NOT NULL,
        `company_id`   INT(11) NOT NULL DEFAULT 0,
        `branch_id`    INT(11) DEFAULT NULL,
        `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
        `created_by`   INT(11) DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `country_code` (`country_code`),
        KEY `year` (`year`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Public Holidays ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_public_holidays')) {
    $CI->db->query("CREATE TABLE `{$p}hr_public_holidays` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `holiday_list_id` INT(11) NOT NULL,
        `name`            VARCHAR(200) NOT NULL,
        `holiday_date`    DATE NOT NULL,
        `description`     VARCHAR(500) DEFAULT NULL,
        `type`            ENUM('national','regional','company') NOT NULL DEFAULT 'national',
        `date_created`    DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `holiday_list_id` (`holiday_list_id`),
        KEY `holiday_date` (`holiday_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Types ───────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_types')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_types` (
        `id`                       INT(11) NOT NULL AUTO_INCREMENT,
        `name`                     VARCHAR(200) NOT NULL,
        `code`                     VARCHAR(50) NOT NULL,
        `color`                    VARCHAR(20) NOT NULL DEFAULT '#2563eb',
        `unit`                     ENUM('days','hours') NOT NULL DEFAULT 'days',
        `default_days`             DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `is_paid`                  TINYINT(1) NOT NULL DEFAULT 1,
        `gender_restriction`       ENUM('none','male','female') NOT NULL DEFAULT 'none',
        `requires_proof`           TINYINT(1) NOT NULL DEFAULT 0,
        `allow_half_day`           TINYINT(1) NOT NULL DEFAULT 1,
        `carry_forward`            TINYINT(1) NOT NULL DEFAULT 0,
        `max_carry_forward`        DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `carry_forward_expiry_days` INT(4) NOT NULL DEFAULT 0 COMMENT 'days after Jan 1',
        `allow_negative`           TINYINT(1) NOT NULL DEFAULT 0,
        `approval_levels`          TINYINT(1) NOT NULL DEFAULT 2,
        `cascade_to_type_id`       INT(11) DEFAULT NULL,
        `encashable`               TINYINT(1) NOT NULL DEFAULT 0,
        `include_public_holidays`  TINYINT(1) NOT NULL DEFAULT 0,
        `include_weekends`         TINYINT(1) NOT NULL DEFAULT 0,
        `notice_days_required`     INT(4) NOT NULL DEFAULT 0,
        `max_consecutive_days`     DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `description`              TEXT DEFAULT NULL,
        `status`                   ENUM('active','inactive') NOT NULL DEFAULT 'active',
        `created_by`               INT(11) DEFAULT NULL,
        `date_created`             DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Policies ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_policies')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_policies` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(200) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `company_id`  INT(11) NOT NULL DEFAULT 0,
        `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        `created_by`  INT(11) DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Policy Lines ────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_policy_lines')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_policy_lines` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `policy_id`      INT(11) NOT NULL,
        `leave_type_id`  INT(11) NOT NULL,
        `annual_days`    DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `accrual_plan_id` INT(11) DEFAULT NULL,
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `policy_id` (`policy_id`),
        KEY `leave_type_id` (`leave_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Accrual Plans ───────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_accrual_plans')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_accrual_plans` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(200) NOT NULL,
        `frequency`   ENUM('monthly','quarterly','half_yearly','annually') NOT NULL DEFAULT 'monthly',
        `method`      ENUM('fixed','tenure_based') NOT NULL DEFAULT 'fixed',
        `description` TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Accrual Tiers ───────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_accrual_tiers')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_accrual_tiers` (
        `id`                  INT(11) NOT NULL AUTO_INCREMENT,
        `accrual_plan_id`     INT(11) NOT NULL,
        `min_service_months`  INT(4) NOT NULL DEFAULT 0,
        `max_service_months`  INT(4) DEFAULT NULL COMMENT 'NULL = no upper limit',
        `days_per_period`     DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
        `date_created`        DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `accrual_plan_id` (`accrual_plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Allocations ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_allocations')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_allocations` (
        `id`                    INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`           INT(11) NOT NULL,
        `leave_type_id`         INT(11) NOT NULL,
        `policy_id`             INT(11) DEFAULT NULL,
        `company_id`            INT(11) NOT NULL DEFAULT 0,
        `leave_year`            YEAR NOT NULL,
        `date_from`             DATE DEFAULT NULL,
        `date_to`               DATE DEFAULT NULL,
        `total_days`            DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `carried_forward_days`  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `carry_forward_expiry`  DATE DEFAULT NULL,
        `used_days`             DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `encashed_days`         DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `status`                ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
        `notes`                 TEXT DEFAULT NULL,
        `created_by`            INT(11) DEFAULT NULL,
        `date_created`          DATETIME NOT NULL,
        `date_modified`         DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `leave_type_id` (`leave_type_id`),
        KEY `leave_year` (`leave_year`),
        KEY `policy_id` (`policy_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}
// Add policy_id column to existing allocations table
$cols = $CI->db->query("SHOW COLUMNS FROM `{$p}hr_leave_allocations` LIKE 'policy_id'")->result();
if (empty($cols)) {
    $CI->db->query("ALTER TABLE `{$p}hr_leave_allocations` ADD COLUMN `policy_id` INT(11) DEFAULT NULL AFTER `leave_type_id`, ADD KEY `policy_id` (`policy_id`)");
}

// ── Leave Requests ────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_requests')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_requests` (
        `id`                   INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`          INT(11) NOT NULL,
        `leave_type_id`        INT(11) NOT NULL,
        `company_id`           INT(11) NOT NULL DEFAULT 0,
        `date_from`            DATE NOT NULL,
        `date_to`              DATE NOT NULL,
        `half_day`             TINYINT(1) NOT NULL DEFAULT 0,
        `half_day_period`      ENUM('morning','afternoon') NOT NULL DEFAULT 'morning',
        `total_days`           DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `total_hours`          DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `reason`               TEXT DEFAULT NULL,
        `handover_employee_id` INT(11) DEFAULT NULL,
        `handover_notes`       TEXT DEFAULT NULL,
        `proof_document`       VARCHAR(500) DEFAULT NULL,
        `status`               ENUM('draft','pending_manager','pending_hr','approved','rejected','cancelled','cancel_requested') NOT NULL DEFAULT 'draft',
        `manager_comment`      TEXT DEFAULT NULL,
        `hr_comment`           TEXT DEFAULT NULL,
        `rejection_reason`     TEXT DEFAULT NULL,
        `date_created`         DATETIME NOT NULL,
        `date_modified`        DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `status` (`status`),
        KEY `date_from` (`date_from`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Approvals ───────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_approvals')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_approvals` (
        `id`          INT(11) NOT NULL AUTO_INCREMENT,
        `request_id`  INT(11) NOT NULL,
        `approver_id` INT(11) NOT NULL,
        `level`       ENUM('manager','hr') NOT NULL,
        `action`      ENUM('approve','reject','cancel_approve','cancel_reject') NOT NULL,
        `comment`     TEXT DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `request_id` (`request_id`),
        KEY `approver_id` (`approver_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Leave Encashments ─────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_leave_encashments')) {
    $CI->db->query("CREATE TABLE `{$p}hr_leave_encashments` (
        `id`             INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`    INT(11) NOT NULL,
        `leave_type_id`  INT(11) NOT NULL,
        `allocation_id`  INT(11) DEFAULT NULL,
        `days_encashed`  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `amount`         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `payslip_id`     INT(11) DEFAULT NULL,
        `status`         ENUM('pending','processed','cancelled') NOT NULL DEFAULT 'pending',
        `notes`          TEXT DEFAULT NULL,
        `created_by`     INT(11) DEFAULT NULL,
        `date_created`   DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── TOIL Entries ──────────────────────────────────────────────────────────────
if (!$CI->db->table_exists($p . 'hr_toil_entries')) {
    $CI->db->query("CREATE TABLE `{$p}hr_toil_entries` (
        `id`               INT(11) NOT NULL AUTO_INCREMENT,
        `employee_id`      INT(11) NOT NULL,
        `company_id`       INT(11) NOT NULL DEFAULT 0,
        `work_date`        DATE NOT NULL,
        `hours_worked`     DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        `toil_hours_earned` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        `reason`           VARCHAR(500) DEFAULT NULL,
        `approved_by`      INT(11) DEFAULT NULL,
        `status`           ENUM('pending','approved','rejected','used') NOT NULL DEFAULT 'pending',
        `date_created`     DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `employee_id` (`employee_id`),
        KEY `work_date` (`work_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$cs};");
}

// ── Auto-seed default data on fresh install ───────────────────────────────────
// Seeds are idempotent (only insert when tables are empty) so safe to always run.
require_once FCPATH . 'modules/xetuu_hr/data/seeds.php';
