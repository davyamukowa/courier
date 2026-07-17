<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'company',
    'email',
    'phone',
    'created_at',
    'quote_details' // Service Type, Route, Total will be parsed from this JSON
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'courier_client_quotes';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];
    $row[] = $aRow['name'];
    $row[] = $aRow['company'];
    $row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';
    $row[] = '<a href="tel:' . $aRow['phone'] . '">' . $aRow['phone'] . '</a>';
    $row[] = _dt($aRow['created_at']);

    $quote_details = json_decode($aRow['quote_details'], true);
    
    $service_type = '';
    $route = '';
    $total = '';
    
    if ($quote_details) {
        $service_type = isset($quote_details['service_type']) ? ucfirst($quote_details['service_type']) : '';
        $route = isset($quote_details['zone_name']) ? $quote_details['zone_name'] : '';
        if (isset($quote_details['total'])) {
            $total = 'KES ' . number_format($quote_details['total'], 2);
        }
    }

    $row[] = $service_type;
    $row[] = $route;
    $row[] = $total;

    $output['aaData'][] = $row;
}
