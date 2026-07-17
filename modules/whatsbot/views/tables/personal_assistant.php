<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_personal_assistants';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['name'];

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_pa')) {
        $options .= '<a href="' . admin_url('whatsbot/personal_assistants/personal_assistant/' . $aRow['id']) . '" data-toggle="tooltip" data-title="' . _l('edit') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square fa-lg"></i></a>';
    }

    if (staff_can('delete', 'wtc_pa')) {
        $options .= '<a href="' . admin_url('whatsbot/personal_assistants/delete/' . $aRow['id']) . '" data-id="' . $aRow['id'] . '" data-type="text" data-toggle="tooltip" data-title="' . _l('delete') . '" id="delete_message_bot" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"><i class="fa-regular fa-trash-can fa-lg"></i></a>';
    }

    if(!staff_can('edit', 'wtc_pa') && !staff_can('delete', 'wtc_pa')) {
        $options .= '-';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
