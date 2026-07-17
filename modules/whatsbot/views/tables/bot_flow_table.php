<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'id',
    'flow_name',
    'is_active',
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

    $checked = '';
    if (1 == $aRow['is_active']) {
        $checked = 'checked';
    }

    $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url('whatsbot/bots/change_active_status/bot_flow') . '" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_bot_flow')) {
        $options .= '<a href="' . admin_url('whatsbot/bot_flow/flow/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="tooltip" data-title=' . _l('flow') . '><i class="fa-solid fa-code-fork fa-lg"></i></a>';
    }

    if (staff_can('edit', 'wtc_bot_flow')) {
        $options .= '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="' . $aRow['flow_name'] . '" class="edit_flow tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="tooltip" data-title=' . _l('edit') . '><i class="fa-regular fa-pen-to-square fa-lg"></i></a>';
    }

    if (staff_can('delete', 'wtc_bot_flow')) {
        $options .= '<a href="' . admin_url('whatsbot/bot_flow/delete/' . $aRow['id']) . '" data-id=' . $aRow['id'] . ' class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete" data-toggle="tooltip" data-title=' . _l('delete') . '><i class="fa-regular fa-trash-can fa-lg"></i></a></div>';
    }

    if (!staff_can('edit', 'wtc_bot_flow') && !staff_can('delete', 'wtc_bot_flow')) {
        $options .= '-';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}
