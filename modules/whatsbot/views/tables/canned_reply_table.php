<?php

defined('BASEPATH') or exit('No direct script access allowed');

$Columns = [
    'id',
    'title',
    'description',
    'is_public',
];

$IndexColumn = 'id';
$Table = db_prefix() . 'wtc_canned_reply';
$where = [];

if (is_admin()) {
    $where[] = "";
} elseif (staff_can('view_own', 'wtc_canned_reply')) {
    $where[] = "AND (`added_from` = " . get_staff_user_id() . " OR `is_public` = 1)";
}

$result = data_tables_init($Columns, $IndexColumn, $Table, [], $where);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['title'];

    $row[] = $aRow['description'];

    $is_checked = $aRow['is_public'] == 1 ? 'checked' : '';

    $switch_btn = '<div class="onoffswitch">
    <input type="checkbox"  name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . ($aRow['is_public'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['id'] . '"></label>
    </div>';


    $row[] = $switch_btn;

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_canned_reply')) {
        $options .= "<a class='tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 edit-btn' data-id='{$aRow['id']}' data-title='{$aRow['title']}' data-desc='{$aRow['description']}'><i class='fa-regular fa-pen-to-square fa-lg'></i></a>";
    }

    if (staff_can('delete', 'wtc_canned_reply')) {
        $options .= "<a class='tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 delete-btn _delete delete_btn' data-id='{$aRow['id']}'><i class='fa-regular fa-trash-can fa-lg'></i></a>";
    }

    if (!staff_can('edit', 'wtc_canned_reply') && !staff_can('delete', 'wtc_canned_reply')) {
        $options .= '-';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
