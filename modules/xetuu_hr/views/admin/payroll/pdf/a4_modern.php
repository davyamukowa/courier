<?php
defined('BASEPATH') or exit('No direct script access allowed');

function _pm_date($v,$f='d M Y',$fb='—'){if(empty($v))return $fb;$t=strtotime($v);return($t&&$t>strtotime('1900-01-01'))?date($f,$t):$fb;}
function _pm_money($v,$c){return $c.' '.number_format((float)$v,2);}

$currency     = get_option('default_currency') ?: 'KSh';
$company_name = get_option('invoice_company_name') ?: '';
$company_addr = get_option('invoice_company_address') ?: '';
$company_city = get_option('invoice_company_city') ?: '';
$company_phone= get_option('invoice_company_phonenumber') ?: '';
$C = $currency;

$period      = _pm_date($payslip->date_from, 'F Y');
$period_from = _pm_date($payslip->date_from, 'd M Y');
$period_to   = _pm_date($payslip->date_to,   'd M Y');

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
$h .= 'td,th{font-size:9pt;font-family:Helvetica,Arial,sans-serif;padding:5px 8px;}';
$h .= '.hdr-left{background-color:#1e3a5f;padding:14px;}';
$h .= '.hdr-right{background-color:#0f2444;padding:14px;text-align:right;vertical-align:middle;}';
$h .= '.meta td{border:1px solid #e2e8f0;vertical-align:top;padding:8px 10px;}';
$h .= '.mlbl{font-size:7.5pt;color:#64748b;font-weight:bold;text-transform:uppercase;}';
$h .= '.mval{font-size:9.5pt;color:#1e293b;font-weight:bold;}';
$h .= '.earn-tbl th{background-color:#dcfce7;color:#166534;border:1px solid #bbf7d0;font-size:8pt;}';
$h .= '.earn-tbl td{border:1px solid #d1fae5;}';
$h .= '.ded-tbl  th{background-color:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-size:8pt;}';
$h .= '.ded-tbl  td{border:1px solid #fde8e8;}';
$h .= '.sum-row td{background-color:#f1f5f9;font-weight:bold;border-top:2px solid #94a3b8;}';
$h .= '.net-band td{background-color:#1e3a5f;color:#ffffff;font-weight:bold;padding:10px 14px;}';
$h .= '.right{text-align:right;} .center{text-align:center;}';
$h .= '</style>';

// Header band
$h .= '<table style="margin-bottom:12px;">';
$h .= '<tr>';
$h .= '<td class="hdr-left" width="60%">';
if ($logo) $h .= $logo . '<br>';
$h .= '<b style="font-size:13pt;color:#ffffff;">' . htmlspecialchars($company_name) . '</b>';
$addr_parts = array_filter([$company_addr, $company_city]);
if ($addr_parts) $h .= '<br><span style="font-size:8pt;color:#93c5fd;">' . htmlspecialchars(implode(', ', $addr_parts)) . '</span>';
$h .= '</td>';
$h .= '<td class="hdr-right" width="40%">';
$h .= '<span style="font-size:13pt;font-weight:bold;color:#ffffff;">PAYSLIP</span><br>';
$h .= '<span style="font-size:9pt;color:#93c5fd;">' . htmlspecialchars($period) . '</span><br>';
$h .= '<span style="font-size:8pt;color:#60a5fa;">' . $period_from . ' &ndash; ' . $period_to . '</span>';
$h .= '</td>';
$h .= '</tr></table>';

// Employee meta
$tax_id_pm = $contract->tax_id ?? $payslip->employee_tax_id ?? '';
$h .= '<table class="meta" style="margin-bottom:8px;">';
$h .= '<tr>';
$h .= '<td width="33%"><div class="mlbl">Employee</div><div class="mval">' . htmlspecialchars($payslip->employee_name ?? '') . '</div>';
if (!empty($contract->id_number)) $h .= '<div style="font-size:7.5pt;color:#64748b;">ID: ' . htmlspecialchars($contract->id_number) . '</div>';
$h .= '</td>';
$h .= '<td width="33%"><div class="mlbl">Designation</div><div class="mval">' . htmlspecialchars($contract->job_title ?? '&mdash;') . '</div>';
if (!empty($contract->contract_type)) $h .= '<div style="font-size:7.5pt;color:#64748b;">' . htmlspecialchars($contract->contract_type) . '</div>';
$h .= '</td>';
$h .= '<td width="34%"><div class="mlbl">Basic Salary</div><div class="mval" style="color:#1e3a5f;">' . _pm_money($basic_wage, $C) . '</div>';
$h .= '<div style="font-size:7.5pt;color:#64748b;">' . $period_from . ' &ndash; ' . $period_to . '</div>';
$h .= '</td>';
$h .= '</tr></table>';

// Statutory row
$stat_pm = [];
if (!empty($payslip->social_sec_number))  $stat_pm[] = 'NSSF: ' . htmlspecialchars($payslip->social_sec_number);
if (!empty($payslip->health_fund_number)) $stat_pm[] = 'SHA: '  . htmlspecialchars($payslip->health_fund_number);
if (!empty($tax_id_pm))                   $stat_pm[] = 'KRA: '  . htmlspecialchars($tax_id_pm);
if (!empty($payslip->passport_number))    $stat_pm[] = 'Passport: ' . htmlspecialchars($payslip->passport_number);
if ($stat_pm) {
    $h .= '<p style="font-size:7.5pt;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;padding:4px 8px;border-radius:3px;margin-bottom:10px;">';
    $h .= implode('&nbsp;&nbsp;&middot;&nbsp;&nbsp;', $stat_pm);
    $h .= '</p>';
}

// Two-column: Earnings | Deductions
$h .= '<table style="margin-bottom:10px;">';
$h .= '<tr>';

// Left: Earnings
$h .= '<td width="50%" style="vertical-align:top;padding-right:6px;">';
$h .= '<p style="font-size:10pt;font-weight:bold;color:#1e3a5f;border-bottom:2px solid #1e3a5f;padding-bottom:3px;margin-bottom:6px;">Earnings</p>';
$h .= '<table class="earn-tbl"><tr><th width="70%">Description</th><th width="30%" style="text-align:right;">Amount</th></tr>';
foreach ($earn_lines as $ln) {
    $h .= '<tr><td>' . htmlspecialchars($ln['rule_name']) . '</td><td style="text-align:right;">' . _pm_money($ln['amount'], $C) . '</td></tr>';
}
$h .= '<tr class="sum-row"><td>Gross Pay</td><td style="text-align:right;">' . _pm_money($gross, $C) . '</td></tr>';
$h .= '</table>';
$h .= '</td>';

// Right: Deductions & Tax
$h .= '<td width="50%" style="vertical-align:top;padding-left:6px;">';
$h .= '<p style="font-size:10pt;font-weight:bold;color:#1e3a5f;border-bottom:2px solid #1e3a5f;padding-bottom:3px;margin-bottom:6px;">Deductions &amp; Tax</p>';
$h .= '<table class="ded-tbl"><tr><th width="70%">Description</th><th width="30%" style="text-align:right;">Amount</th></tr>';
foreach ($ded_lines as $ln) {
    $h .= '<tr><td>' . htmlspecialchars($ln['rule_name']) . '</td><td style="text-align:right;">' . _pm_money($ln['amount'], $C) . '</td></tr>';
}
foreach ($tax_lines as $ln) {
    $h .= '<tr><td>' . htmlspecialchars($ln['rule_name']) . '</td><td style="text-align:right;">' . _pm_money($ln['amount'], $C) . '</td></tr>';
}
$h .= '<tr class="sum-row"><td>Total Deductions</td><td style="text-align:right;">' . _pm_money($total_ded + $total_tax, $C) . '</td></tr>';
$h .= '</table>';
$h .= '</td>';
$h .= '</tr></table>';

// Net Pay band
$h .= '<table class="net-band" style="margin-bottom:10px;">';
$h .= '<tr><td width="50%" style="border:none;"><span style="font-size:11pt;">Net Pay</span><br><span style="font-size:8pt;color:#93c5fd;">' . $period_from . ' &ndash; ' . $period_to . '</span></td>';
$h .= '<td width="50%" style="border:none;text-align:right;font-size:15pt;">' . _pm_money($net, $C) . '</td></tr>';
$h .= '</table>';

if (!empty($contract->bank_account)) {
    $h .= '<p style="font-size:8.5pt;color:#374151;"><b>Payment to:</b> ' . htmlspecialchars($contract->bank_account);
    if (!empty($contract->bank_name)) $h .= ' &mdash; ' . htmlspecialchars($contract->bank_name);
    $h .= '</p>';
}
if (!empty($payslip->notes)) {
    $h .= '<p style="font-size:8pt;color:#64748b;font-style:italic;">' . htmlspecialchars($payslip->notes) . '</p>';
}

$footer_parts = array_filter([$company_name, $company_addr, $company_city, $company_phone]);
$h .= '<p style="font-size:7.5pt;color:#94a3b8;text-align:center;border-top:1px solid #e2e8f0;padding-top:5px;">' . htmlspecialchars(implode(' · ', $footer_parts)) . '</p>';

$pdf->writeHTML($h, true, false, true, false, '');
