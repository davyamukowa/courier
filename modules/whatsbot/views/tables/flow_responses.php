<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'id',
    '1',
    'receiver_id',
    'submit_time',
    'wa_no',
    'type'
];

$where = [];

$where[] = 'AND flow_id = ' . $flow_id;

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_flows_response';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = get_flow_name_by_flow_id($flow_id);

    $row[] = $aRow['receiver_id'];

    $row[] = _d($aRow['submit_time']);

    $row[] = $aRow['wa_no'];

    $color = ('leads' == $aRow['type'] ? '#3a25e9' : ('contacts' == $aRow['type'] ? '#ff4646' : '#7bf565'));
    $row[] = '<span class="label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['type']) . '</span>';

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    // admin_url('whatsbot/flows/flow_review/'.$aRow['id'])
    $options .= '<a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="modal" data-target="#response_preview" data-id="' . $aRow['id'] . '">
        <i class="fa-solid fa-eye fa-lg"></i>
    </a>';

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
