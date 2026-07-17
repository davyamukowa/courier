<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'label',
    'color'
];

$where  = [];
$join = [];

$groupBy = '';

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wtc_custom_label';

$additionalSelect = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect, $groupBy);
$output  = $result['output'];
$rResult = $result['rResult'];

$srno = 1;

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $total_labels = total_rows(db_prefix() . 'wtc_interactions', ['label' => $aRow['id']]);
    $row[] = '<span class="text-dark">' . $aRow['label'] . '</span><br><span class="text-muted">' . _l('total_label_used', $total_labels) . '</span>';

    $row[] = '<span style="background-color:' . $aRow['color'] . ';width:100%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    if(staff_can('edit', 'wtc_custom_label')){
        $options .= '<a href="javascript:void(0)" onclick="editCustomLabel(' . $aRow['id'] . ')" data-id=' . $aRow['id'] . ' class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="tooltip" data-title=' . _l('edit ') . '><i class="fa-regular fa-pen-to-square fa-lg"></i></a>';
    }

    /* Not deleting label if it already assigned to any interaction */
    if ($total_labels == 0 && staff_can('delete', 'wtc_custom_label')) {
        $options .= '<a href="' . admin_url('whatsbot/custom_label/delete/' . $aRow['id']) . '" data-id=' . $aRow['id'] . ' class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete" data-toggle="tooltip" data-title=' . _l('delete') . '><i class="fa-regular fa-trash-can fa-lg"></i></a></div>';
    }

    if(staff_cant('edit', 'wtc_custom_label') && staff_cant('delete', 'wtc_custom_label')){
        $options .= '--';
    }

    $total_labels = 0;

    $row[] = $options;

    $output['aaData'][] = $row;
}
