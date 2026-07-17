<?php
defined('BASEPATH') or exit('No direct script access allowed');

function _t58_dt($v,$f='d/m/Y',$fb='—'){if(empty($v))return $fb;$t=strtotime($v);return($t&&$t>strtotime('1900-01-01'))?date($f,$t):$fb;}
function _t58_m($v,$c){return $c.number_format((float)$v,2);}

$currency     = get_option('default_currency') ?: 'KSh';
$company_name = get_option('invoice_company_name') ?: '';
$company_city = get_option('invoice_company_city') ?: '';
$company_phone= get_option('invoice_company_phonenumber') ?: '';
$C = $currency;

$period      = _t58_dt($payslip->date_from,'M Y');
$period_from = _t58_dt($payslip->date_from,'d/m/Y');
$period_to   = _t58_dt($payslip->date_to,  'd/m/Y');

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
$h .= 'td{font-size:6.5pt;font-family:Courier,"Courier New",monospace;padding:1px 1px;border:none;}';
$h .= '.c{text-align:center;} .r{text-align:right;}';
$h .= '.s{border-top:1px dashed #000;padding-top:2px;}';
$h .= '.d{border-top:1px solid #000;font-weight:bold;padding-top:2px;}';
$h .= '.w{border-top:2px solid #000;font-size:8.5pt;font-weight:bold;padding-top:3px;}';
$h .= '.g{color:#555;font-size:6pt;}';
$h .= '</style>';

$h .= '<table><tr><td class="c" style="font-size:8pt;font-weight:bold;">' . htmlspecialchars($company_name) . '</td></tr></table>';
if ($company_city) $h .= '<table><tr><td class="c g">' . htmlspecialchars($company_city) . '</td></tr></table>';
if ($company_phone) $h .= '<table><tr><td class="c g">' . htmlspecialchars($company_phone) . '</td></tr></table>';
$h .= '<table style="margin-top:2px;"><tr><td class="c s" style="font-size:6pt;letter-spacing:0.5px;">SALARY SLIP ' . strtoupper($period) . '</td></tr></table>';
$h .= '<table style="margin-top:2px;"><tr><td style="font-weight:bold;" colspan="2">' . htmlspecialchars($payslip->employee_name ?? '') . '</td></tr>';
if (!empty($contract->id_number)) $h .= '<tr><td class="g" colspan="2">ID: ' . htmlspecialchars($contract->id_number) . '</td></tr>';
$h .= '<tr><td class="g" colspan="2">' . $period_from . ' &ndash; ' . $period_to . '</td></tr></table>';

if ($earn_lines) {
    $h .= '<table style="margin-top:2px;"><tr><td colspan="2" class="s g" style="font-size:6pt;">EARNINGS</td></tr>';
    foreach ($earn_lines as $ln) {
        $h .= '<tr><td width="58%">' . htmlspecialchars($ln['rule_name']) . '</td><td class="r">' . _t58_m($ln['amount'],$C) . '</td></tr>';
    }
    $h .= '<tr><td class="d">Gross</td><td class="d r">' . _t58_m($gross,$C) . '</td></tr></table>';
}

if ($ded_lines || $tax_lines) {
    $h .= '<table style="margin-top:2px;"><tr><td colspan="2" class="s g" style="font-size:6pt;">DEDUCTIONS</td></tr>';
    foreach (array_merge($ded_lines, $tax_lines) as $ln) {
        $h .= '<tr><td width="58%">' . htmlspecialchars($ln['rule_name']) . '</td><td class="r">' . _t58_m($ln['amount'],$C) . '</td></tr>';
    }
    $h .= '<tr><td class="d">Total</td><td class="d r">' . _t58_m($total_ded+$total_tax,$C) . '</td></tr></table>';
}

$h .= '<table style="margin-top:4px;"><tr><td class="w" width="48%">NET PAY</td><td class="w r">' . _t58_m($net,$C) . '</td></tr></table>';
if (!empty($contract->bank_account)) {
    $h .= '<table style="margin-top:3px;"><tr><td class="s g" style="padding-top:2px;">Pay: ' . htmlspecialchars($contract->bank_account) . '</td></tr></table>';
}
$h .= '<table style="margin-top:5px;"><tr><td class="c g" style="border-top:1px solid #000;padding-top:3px;font-size:6pt;">' . htmlspecialchars($company_name) . ' &mdash; Computer Generated</td></tr></table>';

$pdf->writeHTML($h, true, false, true, false, '');
