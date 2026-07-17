<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    "id",
    "name",
    "catalog_id",
    "user_message",
    'receiver_id',
    "submit_time",
    "wa_no",
    "type",
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_orders_response';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable);

$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];
    $row[] = $aRow['name'];
    $row[] = $aRow['catalog_id'];
    $row[] = $aRow['user_message'];
    $row[] = $aRow['receiver_id'];
    $row[] = $aRow['submit_time'];
    $row[] = $aRow['wa_no'];

    $color = ('leads' == $aRow['type'] ? '#3a25e9' : ('contacts' == $aRow['type'] ? '#ff4646' : '#7bf565'));
    $row[] = '<span class="label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['type']) . '</span>';

    $output['aaData'][] = $row;
}
