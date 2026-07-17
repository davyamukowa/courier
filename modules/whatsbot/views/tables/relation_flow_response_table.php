<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wtc_flows_response.id as response_id',
    db_prefix() . 'wtc_flows.flow_name as flow_name',
    'receiver_id',
    'submit_time',
    'wa_no',
    'type',
    'response_data'
];

$sIndexColumn =  'id';
$sTable       = db_prefix() . 'wtc_flows_response';

$join[] =   'JOIN ' . db_prefix() . 'wtc_flows ON ' . db_prefix() . 'wtc_flows_response.flow_id = ' . db_prefix() . 'wtc_flows.flow_id';

$projectId = $this->ci->input->post('projectId');
$ticketId = $this->ci->input->post('ticketId');

$relation_type = !empty($projectId) ? 'project' : 'ticket';
$relation_id = !empty($projectId) ? $projectId : $ticketId;

$where = [];
$where[] = 'AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(response_data, "$.flow_token")),"$.rel_data.relation_type")),JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(response_data, "$.flow_token")),"$.relation_type"))) = "' . $relation_type . '"';
$where[] = ' AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(response_data, "$.flow_token")),"$.rel_data.relation_id")),JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(response_data, "$.flow_token")),"$.relation_id"))) = "' . $relation_id . '"';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];

    $row[] = $aRow['response_id'];

    $row[] = $aRow['flow_name'];

    $row[] = $aRow['receiver_id'];

    $row[] = $aRow['submit_time'];

    $row[] = $aRow['wa_no'];

    $color = ('leads' == $aRow['type'] ? '#3a25e9' : ('contacts' == $aRow['type'] ? '#ff4646' : '#7bf565'));
    $row[] = '<span class="label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['type']) . '</span>';

    $row[] = '<a href="#" data-id="' . $aRow['response_id'] . '" data-toggle="modal" data-target="#relation_flow_response_modal" ><i class="fa-solid fa-eye fa-lg"></i></a>';

    $output['aaData'][] = $row;
}
