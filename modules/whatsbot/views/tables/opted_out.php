<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumnsContacts = [
    "1",
    "CONCAT(firstname, ' ', lastname) AS name",
    "email",
    "phonenumber",
    "'contacts' AS rel_type",
    "1",
];

$sIndexColumnContact = 'id';
$sTableContact = db_prefix() . 'contacts';

$whereContact = [
    'AND ' . db_prefix() . 'contacts.is_opted_out=1'
];

$additionalSelectContact = [
    'id'
];

$resultContact = data_tables_init($aColumnsContacts, $sIndexColumnContact, $sTableContact, [], $whereContact, $additionalSelectContact);

$aColumnsLeads = [
    "1",
    "name",
    "email",
    "phonenumber",
    "'leads' AS rel_type",
    "1",
];

$sIndexColumnLeads = 'id';
$sTableLeads = db_prefix() . 'leads';

$whereLeads = [
    'AND ' . db_prefix() . 'leads.is_opted_out=1'
];

$additionalSelectLeads = [
    'id'
];

$resultLeads = data_tables_init($aColumnsLeads, $sIndexColumnLeads, $sTableLeads, [], $whereLeads, $additionalSelectLeads);

// Merge only the `rResult` part
$combinedRResult = array_merge($resultContact['rResult'], $resultLeads['rResult']);

// Optional: update the output array
$combinedResult = [
    'rResult' => $combinedRResult,
    'output' => [
        'draw' => $this->ci->input->post('draw'),
        'iTotalRecords' => count($combinedRResult),
        'iTotalDisplayRecords' => count($combinedRResult),
        'aaData' => []
    ]
];

$output = $combinedResult['output'];
$rResult = $combinedResult['rResult'];
$i = 0;
foreach ($rResult as $aRow) {
    $row = [];

    $row[] = ++$i;

    $row[] = $aRow['name'];
    $row[] = $aRow['email'];
    $row[] = $aRow['phonenumber'];
    $color = ('leads' == $aRow['rel_type'] ? '#3a25e9' : ('contacts' == $aRow['rel_type'] ? '#ff4646' : '#7bf565'));
    $row[] = '<span class="label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['rel_type']) . '</span>';

    // Toggle enable/disable optout
    $row[] = staff_can('delete', 'opt_out', '') ? '<div class="onoffswitch">
                <input type="checkbox" name="onoffswitch" class="optout_checkbox onoffswitch-checkbox" id="c_' . $i . '" data-id="' . $aRow['id'] . '" data-type="' . $aRow['rel_type'] . '" checked="">
                <label class="onoffswitch-label" for="c_' . $i . '"></label>
            </div><span class="hide">Yes</span>':"-";

    $output['aaData'][] = $row;
}
