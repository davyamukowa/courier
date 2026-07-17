<?php
defined('BASEPATH') or exit('No direct script access allowed');

function _t80_dt($v,$f='d/m/Y',$fb='—'){if(empty($v))return $fb;$t=strtotime($v);return($t&&$t>strtotime('1900-01-01'))?date($f,$t):$fb;}
function _t80_m($v,$c){return $c.number_format((float)$v,2);}

$currency     = get_option('default_currency') ?: 'KSh';
$company_name = get_option('invoice_company_name') ?: '';
$company_addr = get_option('invoice_company_address') ?: '';
$company_city = get_option('invoice_company_city') ?: '';
$company_phone= get_option('invoice_company_phonenumber') ?: '';
$C = $currency;

$period      = _t80_dt($payslip->date_from,'M Y');
$period_from = _t80_dt($payslip->date_from,'d/m/Y');
$period_to   = _t80_dt($payslip->date_to,  'd/m/Y');

$earn_lines = $ded_lines = $tax_lines = [];
$basic_wage = $gross = $net = 0.0;
foreach ($lines as $ln) {
    if (!($ln['appears_on_payslip']??1)) continue;
    $amt = (float)$ln['amount'];
    if (abs($amt)<0.001) continue;
    switch ($ln['category']) {
        case 'EARN': $earn_lines[]=$ln;$gross+=$amt;if($ln['rule_code']==='BASIC')$basic_wage=$amt;break;
        case 'DED':  $ded_lines[]=$ln;break;
        case 'TAX':  $tax_lines[]=$ln;break;
        case 'NET':  $net=$amt;break;
    }
}
$total_ded=array_sum(array_column($ded_lines,'amount'));
$total_tax=array_sum(array_column($tax_lines,'amount'));
$net=$net?:($gross-$total_ded-$total_tax);

$h  = '<style>';
$h .= 'table{border-collapse:collapse;width:100%;}';
$h .= 'td{font-size:7.5pt;font-family:Courier,"Courier New",monospace;padding:1px 2px;border:none;}';
$h .= '.center{text-align:center;} .right{text-align:right;}';
$h .= '.dashed{border-top:1px dashed #000;padding-top:3px;}';
$h .= '.solid{border-top:1px solid #000;font-weight:bold;padding-top:2px;}';
$h .= '.double{border-top:2px solid #000;font-size:9.5pt;font-weight:bold;padding-top:4px;}';
$h .= '.gray{color:#555555;font-size:7pt;}';
$h .= '</style>';

// Company header
$h .= '<table><tr><td class="center" style="font-size:9pt;font-weight:bold;">' . htmlspecialchars($company_name) . '</td></tr></table>';
$addr = array_filter([$company_addr, $company_city]);
if ($addr) $h .= '<table><tr><td class="center gray">' . htmlspecialchars(implode(', ', $addr)) . '</td></tr></table>';
if ($company_phone) $h .= '<table><tr><td class="center gray">Tel: ' . htmlspecialchars($company_phone) . '</td></tr></table>';

// Period title
$h .= '<table style="margin-top:3px;"><tr><td class="center dashed" style="letter-spacing:1px;font-size:7pt;">PAYSLIP &mdash; ' . strtoupper($period) . '</td></tr></table>';

// Employee
$h .= '<table style="margin-top:3px;">';
$h .= '<tr><td class="gray" width="38%">Employee:</td><td style="font-weight:bold;">' . htmlspecialchars($payslip->employee_name ?? '') . '</td></tr>';
if (!empty($contract->job_title)) $h .= '<tr><td class="gray">Position:</td><td>' . htmlspecialchars($contract->job_title) . '</td></tr>';
if (!empty($contract->id_number)) $h .= '<tr><td class="gray">ID No:</td><td>' . htmlspecialchars($contract->id_number) . '</td></tr>';
$h .= '<tr><td class="gray">Period:</td><td>' . $period_from . ' &ndash; ' . $period_to . '</td></tr>';
$h .= '</table>';

// Earnings
if ($earn_lines) {
    $h .= '<table style="margin-top:4px;"><tr><td colspan="2" class="dashed gray" style="font-size:7pt;letter-spacing:0.5px;">EARNINGS</td></tr>';
    foreach ($earn_lines as $ln) {
        $h .= '<tr><td width="62%">' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">' . _t80_m($ln['amount'], $C) . '</td></tr>';
    }
    $h .= '<tr><td class="solid">Gross Pay</td><td class="solid right">' . _t80_m($gross, $C) . '</td></tr>';
    $h .= '</table>';
}

// Deductions & Tax
if ($ded_lines || $tax_lines) {
    $h .= '<table style="margin-top:4px;"><tr><td colspan="2" class="dashed gray" style="font-size:7pt;letter-spacing:0.5px;">DEDUCTIONS &amp; TAX</td></tr>';
    foreach ($ded_lines as $ln) {
        $h .= '<tr><td width="62%">' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">' . _t80_m($ln['amount'], $C) . '</td></tr>';
    }
    foreach ($tax_lines as $ln) {
        $h .= '<tr><td width="62%">' . htmlspecialchars($ln['rule_name']) . '</td><td class="right">' . _t80_m($ln['amount'], $C) . '</td></tr>';
    }
    $h .= '<tr><td class="solid">Total Deductions</td><td class="solid right">' . _t80_m($total_ded + $total_tax, $C) . '</td></tr>';
    $h .= '</table>';
}

// Net Pay
$h .= '<table style="margin-top:5px;"><tr><td class="double" width="50%">NET PAY</td><td class="double right">' . _t80_m($net, $C) . '</td></tr></table>';

if (!empty($contract->bank_account)) {
    $h .= '<table style="margin-top:5px;"><tr><td class="dashed gray" style="padding-top:3px;">Pay to: ' . htmlspecialchars($contract->bank_account) . '</td></tr></table>';
}
if (!empty($payslip->notes)) {
    $h .= '<table><tr><td class="gray" style="font-style:italic;">' . htmlspecialchars($payslip->notes) . '</td></tr></table>';
}
$h .= '<table style="margin-top:8px;"><tr><td class="center gray" style="border-top:1px solid #000;padding-top:4px;font-size:6.5pt;">' . htmlspecialchars($company_name) . ' &mdash; Computer Generated</td></tr></table>';

$pdf->writeHTML($h, true, false, true, false, '');
