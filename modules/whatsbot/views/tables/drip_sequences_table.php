<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wtc_drip_sequences.id as id',
    'name',
    'rel_type',
    'steps_tbl.steps',
    'enrollments_tbl.enrolled',
    'is_active',
    'created_at',
    '1',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_drip_sequences';

$where = [];

$join = [
    'LEFT JOIN (SELECT sequence_id, COUNT(*) as steps FROM ' . db_prefix() . 'wtc_drip_steps GROUP BY sequence_id) as steps_tbl ON steps_tbl.sequence_id = ' . db_prefix() . 'wtc_drip_sequences.id',
    'LEFT JOIN (SELECT sequence_id, COUNT(*) as enrolled FROM ' . db_prefix() . 'wtc_drip_enrollments GROUP BY sequence_id) as enrollments_tbl ON enrollments_tbl.sequence_id = ' . db_prefix() . 'wtc_drip_sequences.id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = '<a href="' . admin_url('whatsbot/drip_campaigns/sequence/' . $aRow['id']) . '">' . $aRow['name'] . '</a>';

    $row[] = $aRow['rel_type'];

    $row[] = '<span class="badge">' . $aRow['steps'] . '</span>';

    $row[] = '<span class="badge">' . $aRow['enrolled'] . '</span>';

    $row[] = ($aRow['is_active'] == '1') ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('inactive') . '</span>';

    $row[] = _dt($aRow['created_at']);

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_drip')) {
        $options .= '<a href="' . admin_url('whatsbot/drip_campaigns/sequence/' . $aRow['id']) . '" data-toggle="tooltip" data-title="' . _l('edit') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-solid fa-pen-to-square fa-lg"></i></a>';
    }

    if (staff_can('edit', 'wtc_drip')) {
        $options .= '<a href="' . admin_url('whatsbot/drip_campaigns/enrollments/' . $aRow['id']) . '" data-toggle="tooltip" data-title="' . _l('enroll') . ' '. ucfirst($aRow['rel_type']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-solid fa-users fa-lg"></i></a>';
    }

    if (staff_can('delete', 'wtc_drip')) {
        $options .= '<a href="' . admin_url('whatsbot/drip_campaigns/delete/' . $aRow['id']) . '" data-id=' . $aRow['id'] . ' class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete" data-toggle="tooltip" data-title=' . _l('delete') . '><i class="fa-regular fa-trash-can fa-lg"></i></a></div>';
    }

    if (!staff_can('edit', 'wtc_drip') && !staff_can('delete', 'wtc_drip')) {
        $options .= '-';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}
