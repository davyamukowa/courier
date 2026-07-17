<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kenya Payroll Addon — installer
 * Seeds FY 2024/25 statutory rates and salary structures.
 * Idempotent: safe to re-run on re-upload.
 * Cleans up old duplicate rule codes from prior installs.
 */

$CI = isset($CI) ? $CI : get_instance();
$CI->load->model('xetuu_hr/Xr_payroll_model', 'payroll_mdl');
$p = db_prefix();

$ADDON_ID = 'kenya_payroll';

// ── 1. Statutory rate settings (never overwrite if already set by accountant) ──
$defaults = [
    'paye_bands' => json_encode([
        ['to' => 24000,  'rate' => 10],
        ['to' => 32333,  'rate' => 25],
        ['to' => 500000, 'rate' => 30],
        ['to' => 800000, 'rate' => 32.5],
        ['to' => null,   'rate' => 35],
    ]),
    'personal_relief'   => 2400,
    'nssf_rate'         => 6,
    'nssf_tier2_limit'  => 36000,
    'shif_rate'         => 2.75,
    'shif_min'          => 300,
    'housing_levy_rate' => 1.5,
];
foreach ($defaults as $key => $value) {
    if ($CI->payroll_mdl->get_addon_setting($ADDON_ID, $key, 0, null) === null) {
        $CI->payroll_mdl->set_addon_setting($ADDON_ID, $key, $value, 0);
    }
}

// ── 2. Salary structures ──────────────────────────────────────────────────────
$monthly = $CI->db->where('name', 'Monthly')->where('active', 1)
                   ->get($p . 'hr_pay_frequencies')->row();
$freq_id = $monthly ? (int)$monthly->id : 1;

$structures = [
    'KE_REGULAR' => [
        'name'        => 'Kenya: Regular Pay',
        'description' => 'Kenya statutory structure for permanent staff (NSSF / SHIF / Housing Levy / PAYE).',
    ],
    'KE_CASUAL' => [
        'name'        => 'Kenya: Casual Pay',
        'description' => 'Kenya statutory structure for casual workers (no PAYE / NSSF).',
    ],
];

$struct_ids = [];
foreach ($structures as $code => $data) {
    $struct = $CI->db->where('code', $code)->get($p . 'hr_salary_structures')->row();
    if (!$struct) {
        $CI->db->insert($p . 'hr_salary_structures', [
            'company_id'       => 0,
            'name'             => $data['name'],
            'code'             => $code,
            'pay_frequency_id' => $freq_id,
            'description'      => $data['description'],
            'active'           => 1,
            'date_created'     => date('Y-m-d H:i:s'),
        ]);
        $struct_ids[$code] = (int)$CI->db->insert_id();
    } else {
        $struct_ids[$code] = (int)$struct->id;
    }
}

// ── 3. Remove old/duplicate rule codes left by prior installs ─────────────────
// These were superseded by the canonical codes below.
$obsolete_codes = [
    'NSSF_AMOUNT', 'SHIF_AMOUNT', 'Housing_Levy',
    'UNTAXED_FOOD_ALLOWANCE', 'UNTAXED_AIRTIME_ALLOWANCE',
    'UNTAXED_PENSION_ALLOWANCE', 'UNTAXED_ALLOWANCE',
    'TAXED_FOOD_ALLOWANCE', 'TAXED_AIRTIME_ALLOWANCE',
    'TAXED_PENSION_ALLOWANCE', 'TAXED_ALLOWANCE',
    'Loans_Advance', 'MED_INSURANCE', 'ATTACH_SALARY',
    'ASSIG_SALARY', 'CHILD_SUPPORT',
];
foreach ($struct_ids as $sid) {
    if ($obsolete_codes) {
        $CI->db->where('structure_id', $sid)
               ->where_in('code', $obsolete_codes)
               ->delete($p . 'hr_salary_rules');
    }
}

// ── 4. Canonical salary rules ─────────────────────────────────────────────────
// Format: [code, name, category, sequence, formula, appears_on_payslip]
// Formulas use context vars: BASIC GROSS TAXABLE TOTAL_DED PRORATION DAYS
//   benefit["KEY"]  deduction["KEY"]
//   NSSF_RATE  SHIF_RATE  SHIF_MIN  AHL_RATE  PERSONAL_RELIEF  PAYE_BANDS
//   graduated_paye(TAXABLE, PAYE_BANDS)

$rules_regular = [
    // ── Earnings ──────────────────────────────────────────────── seq 10-90
    ['BASIC',       'Basic Salary',          'EARN',  10, 'BASIC',                   1],
    ['HOUSE',       'House Allowance',       'EARN',  20, 'benefit["HOUSE"]',        1],
    ['TRANSPORT',   'Transport Allowance',   'EARN',  30, 'benefit["TRANSPORT"]',    1],
    ['FOOD',        'Food Allowance',        'EARN',  40, 'benefit["FOOD"]',         1],
    ['AIRTIME',     'Airtime Allowance',     'EARN',  50, 'benefit["AIRTIME"]',      1],
    ['COMMISSION',  'Commission',            'EARN',  60, 'benefit["COMMISSION"]',   1],
    ['PENSION_EARN','Pension Allowance',     'EARN',  70, 'benefit["PENSION"]',      1],
    ['ALLOWANCE',   'Other Allowances',      'EARN',  80, 'benefit["ALLOWANCE"]',    1],
    ['OTHER_EARN',  'Other Earnings',        'EARN',  90, 'benefit["OTHER"]',        1],

    // ── Pre-tax statutory deductions ──────────────────────────── seq 100-130
    // NSSF: 6% of gross (Kenya NSSF Act 2013, no tier cap)
    ['NSSF',  'NSSF (Employee 6%)',    'DED', 100, 'round(GROSS * NSSF_RATE / 100, 2)',                   1],
    // SHIF: 2.75% of gross, minimum KES 300
    ['SHIF',  'SHIF (2.75%)',          'DED', 110, 'max(round(GROSS * SHIF_RATE / 100, 2), SHIF_MIN)',    1],
    // Affordable Housing Levy: 1.5% of gross
    ['AHL',   'Housing Levy (1.5%)',   'DED', 120, 'round(GROSS * AHL_RATE / 100, 2)',                    1],
    // Mortgage interest relief reduces taxable pay
    ['MORTGAGE', 'Mortgage Interest Relief', 'DED', 130, 'deduction["MORTGAGE"]',                         1],

    // ── Tax ───────────────────────────────────────────────────── seq 140
    ['PAYE', 'P.A.Y.E.', 'TAX', 140,
        'max(0, graduated_paye(TAXABLE, PAYE_BANDS) - PERSONAL_RELIEF)',
        1],

    // ── Post-tax deductions ───────────────────────────────────── seq 200-210
    ['LOAN',      'Loans and Advances',  'DED', 200, 'deduction["LOAN"]',    1],
    ['HELB',      'HELB',               'DED', 201, 'deduction["HELB"]',    1],
    ['PENSION',   'Pension Contribution','DED', 202, 'deduction["PENSION"]', 1],
    ['OTHER_DED', 'Other Deductions',   'DED', 210, 'deduction["OTHER"]',   1],

    // ── Employer contributions (hidden on payslip) ─────────────── seq 300+
    ['NSSF_ER', 'NSSF (Employer 6%)',        'EMPLOYER', 300, 'round(GROSS * NSSF_RATE / 100, 2)',  0],
    ['AHL_ER',  'Housing Levy (Employer)',    'EMPLOYER', 310, 'round(GROSS * AHL_RATE / 100, 2)',  0],
];

$rules_casual = [
    ['BASIC',       'Basic Salary',    'EARN', 10, 'BASIC',                 1],
    ['ALLOWANCE',   'Allowances',      'EARN', 20, 'benefit["ALLOWANCE"]',  1],
    ['REIMBURSEMENT','Reimbursement',  'EARN', 30, 'benefit["OTHER"]',      1],
    ['OTHER_DED',   'Other Deductions','DED',  40, 'deduction["OTHER"]',    1],
];

$all_rules = [
    'KE_REGULAR' => $rules_regular,
    'KE_CASUAL'  => $rules_casual,
];

foreach ($all_rules as $struct_code => $rules) {
    $sid = $struct_ids[$struct_code];
    foreach ($rules as [$code, $name, $cat, $seq, $formula, $on_payslip]) {
        $existing = $CI->db->where('structure_id', $sid)
                            ->where('code', $code)
                            ->get($p . 'hr_salary_rules')->row();
        if ($existing) {
            // Update formula and name in case they changed
            $CI->db->where('id', $existing->id)->update($p . 'hr_salary_rules', [
                'name'               => $name,
                'sequence'           => $seq,
                'amount_formula'     => $formula,
                'appears_on_payslip' => $on_payslip,
                'active'             => 1,
            ]);
        } else {
            $CI->db->insert($p . 'hr_salary_rules', [
                'structure_id'       => $sid,
                'code'               => $code,
                'name'               => $name,
                'sequence'           => $seq,
                'category'           => $cat,
                'amount_formula'     => $formula,
                'appears_on_payslip' => $on_payslip,
                'is_addon_rule'      => 1,
                'active'             => 1,
                'date_created'       => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
