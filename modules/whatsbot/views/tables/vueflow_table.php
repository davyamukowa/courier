<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'id',
    'flow_name',
    '1'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_bot_flow';

$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['flow_name'];

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_campaign')) {
        $options .= '<a href="' . admin_url('whatsbot/flow/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="tooltip" data-title=' . _l('edit ') . '><i class="fa-regular fa-pen-to-square fa-lg"></i></a>';
    }

    if (staff_can('delete', 'wtc_campaign')) {
        $options .= '<a href="' . admin_url('whatsbot/bots/delete_flow_builder/' . $aRow['id']) . '" data-id=' . $aRow['id'] . ' class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete" data-toggle="tooltip" data-title=' . _l('delete') . '><i class="fa-regular fa-trash-can fa-lg"></i></a></div>';
    }

    if (!staff_can('edit', 'wtc_campaign') && !staff_can('delete', 'wtc_campaign')) {
        $options .= '-';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}
