<?php
defined('BASEPATH') or exit('No direct script access allowed');

$period = date('M Y', strtotime($payslip->date_from));
$computed_on = date('d/m/Y', strtotime($payslip->created_at ?? date('Y-m-d')));

// Fetch company information
$company_name = get_option('invoice_company_name');
$company_address = get_option('invoice_company_address');
$company_city = get_option('invoice_company_city');
$company_country = get_option('invoice_company_country_code');
$company_zip = get_option('invoice_company_postal_code');
$company_phone = get_option('invoice_company_phonenumber');
$company_info = format_organization_info(); 

// Prepare lines
$rule_lines = [];
$basic_wage = 0.0;
foreach ($lines as $ln) {
    if ($ln['appears_on_payslip'] == 1 || $ln['rule_code'] === 'BASIC' || $ln['category'] === 'NET') {
        $rule_lines[] = $ln;
    }
    if ($ln['rule_code'] === 'BASIC') {
        $basic_wage = (float)$ln['amount'];
    }
}

// Attendance calculation
$worked_days = (float)($payslip->worked_days ?: 0);
$hours_per_day = 8;
$worked_hours = $worked_days * $hours_per_day;

// Odoo layout HTML
$html = '
<style>
    table { width: 100%; border-collapse: collapse; font-family: "Helvetica", "Arial", sans-serif; font-size: 9pt; }
    th { border: 1px solid #6b7280; font-weight: bold; background-color: #f3f4f6; padding: 5px; }
    td { border: 1px solid #d1d5db; padding: 5px; }
    .header-table { border: none; }
    .header-table td { border: none; padding: 0; }
    .title { font-size: 16pt; color: #374151; margin-bottom: 20px; }
    .net-row { background-color: #0c4a6e; color: #ffffff; font-weight: bold; }
    .net-row td { border: 1px solid #0c4a6e; }
    .amount-col { text-align: right; }
    .text-gray { color: #6b7280; }
    .text-blue { color: #0284c7; }
</style>

<table class="header-table" width="100%">
    <tr>
        <td width="50%">
            ' . pdf_logo_url() . '
        </td>
        <td width="50%" align="right" style="color: #4b5563; font-size: 9pt;">
            <strong>' . htmlspecialchars($company_name) . '</strong><br>
            ' . nl2br(htmlspecialchars(strip_tags($company_info))) . '
        </td>
    </tr>
</table>

<br><br>

<div class="title">Salary Slip - ' . htmlspecialchars($payslip->employee_name) . ' - ' . $period . '</div>

<table cellpadding="4">
    <tr>
        <td width="25%"><strong>Employee</strong></td>
        <td width="25%"><strong>Marital Status</strong></td>
        <td width="25%"><strong>Designation</strong></td>
        <td width="25%"><strong>Pay Period</strong></td>
    </tr>
    <tr>
        <td>' . htmlspecialchars($payslip->employee_name) . '</td>
        <td>Single</td>
        <td>' . htmlspecialchars($contract->job_title ?? '') . '</td>
        <td>' . date('d/m/Y', strtotime($payslip->date_from)) . ' - ' . date('d/m/Y', strtotime($payslip->date_to)) . '</td>
    </tr>
    <tr>
        <td class="text-gray"></td>
        <td><strong>Person in charge</strong></td>
        <td><strong>Identification</strong></td>
        <td><strong>Computed on</strong></td>
    </tr>
    <tr>
        <td class="text-gray">' . htmlspecialchars($contract->employee_email ?? '') . '</td>
        <td>0</td>
        <td>' . htmlspecialchars($contract->id_number ?? '') . '</td>
        <td>' . $computed_on . '</td>
    </tr>
    <tr>
        <td><strong>Contract Start Date</strong></td>
        <td><strong>Contract Type</strong></td>
        <td><strong>Working Schedule</strong></td>
        <td></td>
    </tr>
    <tr>
        <td>' . date('d/m/Y', strtotime($contract->date_start ?? $payslip->date_from)) . '</td>
        <td>Permanent</td>
        <td>40.0</td>
        <td></td>
    </tr>
</table>

<br>

<table cellpadding="4">
    <tr>
        <td width="25%"><strong>Basic Salary</strong></td>
        <td width="25%" class="text-gray">' . number_format($basic_wage, 2) . ' KSh</td>
        <td width="50%" colspan="2"></td>
    </tr>
</table>

<br><br>

<table cellpadding="4">
    <tr style="background-color: #f9fafb;">
        <th width="40%" align="center">NAME</th>
        <th width="20%" align="center">NUMBER OF HOURS</th>
        <th width="20%" align="center">NUMBER OF DAYS</th>
        <th width="20%" align="center">AMOUNT</th>
    </tr>
    <tr>
        <td>Paid Time Off</td>
        <td>0.0</td>
        <td>0.0</td>
        <td align="right">0.00 KSh</td>
    </tr>
    <tr>
        <td>Sick Time Off</td>
        <td>0.0</td>
        <td>0.0</td>
        <td align="right">0.00 KSh</td>
    </tr>
    <tr>
        <td>Attendance</td>
        <td>' . number_format($worked_hours, 1) . '</td>
        <td>' . number_format($worked_days, 1) . '</td>
        <td align="right">' . number_format($basic_wage, 2) . ' KSh</td>
    </tr>';

foreach ($rule_lines as $ln) {
    if ($ln['category'] === 'NET') {
        $html .= '
        <tr class="net-row">
            <td colspan="3"><strong>' . htmlspecialchars($ln['rule_name']) . '</strong></td>
            <td align="right"><strong>' . number_format((float)$ln['amount'], 2) . ' KSh</strong></td>
        </tr>';
    } else {
        $color = ($ln['category'] === 'EARN' || $ln['category'] === 'TAX' || $ln['rule_code'] === 'GROSS') ? 'text-blue' : '';
        $html .= '
        <tr>
            <td colspan="3" class="' . $color . '">' . htmlspecialchars($ln['rule_name']) . '</td>
            <td align="right" class="' . $color . '">' . number_format((float)$ln['amount'], 2) . ' KSh</td>
        </tr>';
    }
}

$net = 0;
foreach ($rule_lines as $ln) {
    if ($ln['category'] === 'NET') $net = (float)$ln['amount'];
}

$html .= '
</table>

<br><br>
<div>
    To pay on <strong>' . htmlspecialchars($contract->bank_account ?? 'Cash/Cheque') . '</strong> of <em>' . htmlspecialchars($payslip->employee_name) . '</em>: ' . number_format($net, 2) . ' KSh
</div>
';

echo $html;
