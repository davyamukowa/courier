<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'id',
    'flow_name',
    'category',
    'status',
];

$where = [];
// $where[] = ' AND status = "PUBLISHED"';

$sIndexColumn = 'id';
$sTable = db_prefix().'wtc_flows';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['flow_id']);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['flow_name'];

    $row[] = implode(", ", json_decode($aRow['category'], true));

    $status = '<span class="inline-block label label-warning">'.$aRow['status'].'</span>';
    if ('PUBLISHED' == $aRow['status']) {
        $status = '<span class="inline-block label label-success">'.$aRow['status'].'</span>';
    }
    $row[] = $status;

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    
    $options .= '<a href="#" class="flow_preview tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-id="' . $aRow['flow_id'] . '">
        <i class="fa-regular fa-eye fa-lg"></i>
    </a>';

    $options .= '<a href="'.admin_url('whatsbot/flows/flows_statistics/'.$aRow['flow_id']).'" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-id="' . $aRow['flow_id'] . '">
        <i class="fa-solid fa-chart-column fa-lg"></i>
    </a>';

    $options .= '</div>';

    $row[] = $options;
    
    $output['aaData'][] = $row;
}
