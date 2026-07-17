<?php
defined('BASEPATH') or exit('No direct script access allowed');

function _mn2_date($v,$f='d/m/Y',$fb='—'){if(empty($v))return $fb;$t=strtotime($v);return($t&&$t>strtotime('1900-01-01'))?date($f,$t):$fb;}
function _mn2_money($v,$c){return $c.' '.number_format((float)$v,2);}

$currency     = get_option('default_currency') ?: 'KSh';
$company_name = get_option('invoice_company_name') ?: '';
$company_addr = get_option('invoice_company_address') ?: '';
$company_city = get_option('invoice_company_city') ?: '';
$C = $currency;

$period      = _mn2_date($payslip->date_from, 'F Y');
$period_from = _mn2_date($payslip->date_from, 'd/m/Y');
$period_to   = _mn2_date($payslip->date_to,   'd/m/Y');

$earn_lines = $ded_lines = $tax_lines = [];
$basic_wage = $gross = $net = 0.0;
foreach ($lines as $ln) {
    if (!($ln['appears_on_payslip'] ?? 1)) continue;
    $amt = (float)$ln['amount'];
    if (abs($amt) < 0.001) continue;
    switch ($ln['category']) {
        case 'EARN': $earn_lines[] = $ln; $gross += $amt;
                     if ($ln['rule_code']==='BASIC') $basic_wage = $amt; break;
        case 'DED':  $ded_lines[] = $ln; break;
        case 'TAX':  $tax_lines[]  = $ln; break;
        case 'NET':  $net = $amt; break;
    }
}
$total_ded = array_sum(array_column($ded_lines,'amount'));
$total_tax = array_sum(array_column($tax_lines,'amount'));
$net       = $net ?: ($gross - $total_ded - $total_tax);

$logo = pdf_logo_url();

$h  = '<style>';
$h .= 'table{border-collapse:collapse;width:100%;}';
$h .= 'td,th{font-size:9pt;font-family:Helvetica,Arial,sans-serif;padding:4px 0;}';
$h .= '.no-bdr td{border:none;}';
$h .= '.line-row td{border-bottom:1px solid #f3f4f6;padding:5px 2px;}';
$h .= '.cat-lbl{font-size:7.5pt;color:#94a3b8;text-transform:uppercase;font-weight:bold;letter-spacing:0.5px;padding:8px 2px 3px 2px;}';
$h .= '.sub-row td{border-top:1px solid #9ca3af;font-weight:bold;padding:5px 2px;}';
$h .= '.net-row td{border-top:2px solid #1e3a5f;font-size:12pt;font-weight:bold;color:#1e3a5f;padding:7px 2px;}';
$h .= '.right{text-align:right;}';
$h .= '</style>';

// Header
$h .= '<table class="no-bdr" style="margin-bottom:16px;">';
$h .= '<tr>';
$h .= '<td width="55%" style="border:none;">';
if ($logo) $h .= $logo . '<br>';
$h .= '<b style="font-size:10.5pt;">' . htmlspecialchars($company_name) . '</b>';
$addr = array_filter([$company_addr, $company_city]);
if ($addr) $h .= '<br><span style="font-size:8pt;color:#94a3b8;">' . htmlspecialchars(implode(', ', $addr)) . '</span>';
$h .= '</td>';
$h .= '<td width="45%" style="border:none;text-align:right;vertical-align:top;">';
$h .= '<b style="font-size:14pt;">PAYSLIP</b><br>';
$h .= '<span style="font-size:9pt;color:#374151;">' . htmlspecialchars($period) . '</span><br>';
$h .= '<span style="font-size:8pt;color:#94a3b8;">' . $period_from . ' to ' . $period_to . '</span>';
$h .= '</td>';
$h .= '</tr></table>';

// Employee
$tax_id_mn = $contract->tax_id ?? $payslip->employee_tax_id ?? '';
$h .= '<table class="no-bdr" style="margin-bottom:8px;border-top:2px solid #111827;padding-top:8px;">';
$h .= '<tr>';
$h .= '<td width="55%" style="border:none;">';
$h .= '<span style="font-size:8pt;color:#94a3b8;">Employee</span><br>';
$h .= '<b style="font-size:10.5pt;">' . htmlspecialchars($payslip->employee_name ?? '') . '</b>';
if (!empty($contract->job_title)) $h .= '<br><span style="font-size:8pt;color:#6b7280;">' . htmlspecialchars($contract->job_title) . '</span>';
if (!empty($contract->id_number)) $h .= '<br><span style="font-size:8pt;color:#94a3b8;">ID: ' . htmlspecialchars($contract->id_number) . '</span>';
$h .= '</td>';
$h .= '<td width="45%" style="border:none;text-align:right;">';
$h .= '<span style="font-size:8pt;color:#94a3b8;">Basic Salary</span><br>';
$h .= '<b style="font-size:10.5pt;">' . _mn2_money($basic_wage, $C) . '</b><br>';
$h .= '<span style="font-size:8pt;color:#94a3b8;">' . htmlspecialchars($contract->contract_type ?? 'Permanent') . '</span>';
$h .= '</td>';
$h .= '</tr></table>';

// Statutory
$stat_mn = [];
if (!empty($payslip->social_sec_number))  $stat_mn[] = 'NSSF: ' . htmlspecialchars($payslip->social_sec_number);
if (!empty($payslip->health_fund_number)) $stat_mn[] = 'SHA: '  . htmlspecialchars($payslip->health_fund_number);
if (!empty($tax_id_mn))                   $stat_mn[] = 'PIN: '  . htmlspecialchars($tax_id_mn);
if ($stat_mn) {
    $h .= '<p style="font-size:7.5pt;color:#94a3b8;margin-bottom:10px;">' . implode('&nbsp;&nbsp;·&nbsp;&nbsp;', $stat_mn) . '</p>';
}

// Lines
$h .= '<table>';
$h .= '<tr><td style="font-size:8pt;color:#94a3b8;border-bottom:1px solid #d1d5db;padding-bottom:3px;">DESCRIPTION</td>';
$h .= '<td class="right" style="font-size:8pt;color:#94a3b8;border-bottom:1px solid #d1d5db;padding-bottom:3px;">AMOUNT</td></tr>';

if ($earn_lines) {
    $h .= '<tr><td colspan="2" class="cat-lbl">Earnings</td></tr>';
    foreach ($earn_lines as $ln) {
        $h .= '<tr class="line-row"><td>' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">' . _mn2_money($ln['amount'], $C) . '</td></tr>';
    }
    $h .= '<tr class="sub-row"><td>Gross Pay</td><td class="right">' . _mn2_money($gross, $C) . '</td></tr>';
}
if ($ded_lines || $tax_lines) {
    $h .= '<tr><td colspan="2" class="cat-lbl">Deductions &amp; Tax</td></tr>';
    foreach ($ded_lines as $ln) {
        $h .= '<tr class="line-row"><td>' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">&minus;' . _mn2_money($ln['amount'], $C) . '</td></tr>';
    }
    foreach ($tax_lines as $ln) {
        $h .= '<tr class="line-row"><td>' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">&minus;' . _mn2_money($ln['amount'], $C) . '</td></tr>';
    }
    $h .= '<tr class="sub-row"><td>Total Deductions</td><td class="right">&minus;' . _mn2_money($total_ded + $total_tax, $C) . '</td></tr>';
}
$h .= '<tr class="net-row"><td>NET PAY</td><td class="right">' . _mn2_money($net, $C) . '</td></tr>';
$h .= '</table>';

if (!empty($contract->bank_account)) {
    $h .= '<p style="font-size:8.5pt;color:#374151;margin-top:8px;">Payable to: <b>' . htmlspecialchars($contract->bank_account) . '</b></p>';
}
if (!empty($payslip->notes)) {
    $h .= '<p style="font-size:8pt;color:#94a3b8;font-style:italic;">' . htmlspecialchars($payslip->notes) . '</p>';
}
$h .= '<p style="font-size:7.5pt;color:#c4c4c4;text-align:center;margin-top:12px;">' . htmlspecialchars($company_name) . ' &mdash; Confidential Payroll Document</p>';

$pdf->writeHTML($h, true, false, true, false, '');
