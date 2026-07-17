<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wtc_webhook_logs.id as id',
    db_prefix() . 'wtc_receive_webhook_source.name as webhook_name',
    db_prefix() . 'wtc_webhook_logs.status as status',
    db_prefix() . 'wtc_webhook_logs.sendtime as sendtime',
    '1',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'wtc_receive_webhook_source ON ' . db_prefix() . 'wtc_receive_webhook_source.id = ' . db_prefix() . 'wtc_webhook_logs.webhook_id',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_webhook_logs';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['webhook_name'];

     $color = 'label-default';
    if ($aRow['status'] == 'success') {
        $color = 'label-success';
    }
    if ($aRow['status'] == 'failed') {
        $color = 'label-danger';
    }
    if ($aRow['status'] == 'pending') {
        $color = 'label-warning';
    }
    $row[] = '<span class="label ' . $color . '">' . ucfirst($aRow['status']) . '</span>';

    $row[] = _d($aRow['sendtime']);

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    $options .= '<a href="#" class="btn btn-default btn-icon webhook_detail" data-id="'.$aRow['id'].'"><i class="fa fa-eye"></i></a>';

    if (staff_can('clear_log', 'wtc_log_activity')) {
        $options .= '<a href="' . admin_url('whatsbot/webhook_logs/delete_log/' . $aRow['id']) . '" data-id="' . $aRow['id'] . '" class="btn btn-default btn-icon btn-lg _delete"><i class="fa-regular fa-trash-can"></i></a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
