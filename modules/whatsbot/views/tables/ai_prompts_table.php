<?php

defined('BASEPATH') or exit('No direct script access allowed');

$Columns = [
    'id',
    'name',
    'action',
];

$IndexColumn = 'id';
$Table = db_prefix() . 'wtc_ai_prompts';

$where = [];
if (staff_can('view_own', 'wtc_ai_prompts')) {
    $where[] = "AND `added_from` = " . get_staff_user_id();
}

$result = data_tables_init($Columns, $IndexColumn, $Table, [], $where);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['name'];

    $row[] = $aRow['action'];

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_ai_prompts')) {
        $options .= "<a class='tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 edit-btn' data-id='{$aRow['id']}' data-name='{$aRow['name']}' data-action='{$aRow['action']}'><i class='fa-regular fa-pen-to-square fa-lg'></i></a>";
    }

    if (staff_can('delete', 'wtc_ai_prompts')) {
        $options .= "<a class='tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete delete_btn' data-id='{$aRow['id']}'><i class='fa-regular fa-trash-can fa-lg'></i></a>";
    }

    if (!staff_can('edit', 'wtc_ai_prompts') && !staff_can('delete', 'wtc_ai_prompts')) {
        $options .= '-';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
