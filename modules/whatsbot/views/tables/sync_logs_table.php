<?php

$aColumns = [
    'id',
    'sync_time',
    'direction',
    'status',
    'items_processed',
    'id as details',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wtc_catalog_sync_logs';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Date
    $row[] = _dt($aRow['sync_time']);
    
    // Direction
    $direction = '';
    switch ($aRow['direction']) {
        case 'perfex_to_whatsapp':
            $direction = '<span class="label label-info">' . _l('perfex_to_whatsapp') . '</span>';
            break;
        case 'whatsapp_to_perfex':
            $direction = '<span class="label label-primary">' . _l('whatsapp_to_perfex') . '</span>';
            break;
        case 'bidirectional':
            $direction = '<span class="label label-success">' . _l('bidirectional') . '</span>';
            break;
    }
    $row[] = $direction;
    
    // Status
    $status = '';
    switch ($aRow['status']) {
        case 'success':
            $status = '<span class="label label-success">' . _l('success') . '</span>';
            break;
        case 'partial':
            $status = '<span class="label label-warning">' . _l('partial') . '</span>';
            break;
        case 'failed':
            $status = '<span class="label label-danger">' . _l('failed') . '</span>';
            break;
    }
    $row[] = $status;
    
    // Items Processed
    $processed = json_decode($aRow['items_processed'], true);
    $processedText = sprintf(_l('items_processed_count'), $processed['success'], $processed['failed'], $processed['skipped']);
    $row[] = $processedText;

    // Options
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'wtc_message_bot')) {
        $options .= '<a href="#" onclick="viewSyncDetails(' . $aRow['id'] . '); return false;" data-toggle="tooltip" data-title="'._l('view_details').'" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-eye fa-lg"></i></a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}