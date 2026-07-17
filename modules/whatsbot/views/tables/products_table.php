<?php

$aColumns = [
    'description',
    'long_description',
    db_prefix() . 'items.rate as rate',
    'image_url',
    db_prefix() . 'items.id as id',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'items';

$join = [
    'LEFT JOIN ' . db_prefix() . 'wtc_product_metadata ON ' . db_prefix() . 'wtc_product_metadata.product_id = ' . db_prefix() . 'items.id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [db_prefix() . 'items.id', 'whatsapp_catalog_id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    
    // Product Name with link
    $row[] = $aRow['description'];
    
    // Description
    $row[] = $aRow['long_description'] ? $aRow['long_description'] : '-';
    
    // Price
    $row[] = app_format_money($aRow['rate'], get_base_currency());

    // Status
    $status = '';
    if(!empty($aRow['whatsapp_catalog_id']) && !empty($aRow['image_url'])){
        $status .=  '<span class="label label-success">' . _l('synced') . '</span>';
    }elseif(empty($aRow['whatsapp_catalog_id']) && !empty($aRow['image_url'])){
        $status .=  '<span class="label label-info">' . _l('ready_to_sync') . '</span>';
    }else{
        $status .= '<span class="label label-warning">' . _l('missing_image') . '</span>';
    }
    $row[] = $status;
    
    // Options
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    $options .= staff_can('edit', 'wtc_catalog_sync') ? '<button type="button" data-product-id="' . $aRow['id'] . '" class="edit-product-btn tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="tooltip" title="' . _l('edit') . '" style="border: none; background: none; cursor: pointer; padding: 0;"><i class="fa-regular fa-pen-to-square fa-lg"></i></button>' : '--';

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}