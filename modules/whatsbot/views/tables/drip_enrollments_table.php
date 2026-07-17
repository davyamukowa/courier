<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wtc_drip_enrollments.id as id',
    'phone_number',
    'rel_type',
    'current_step',
    'status',
    'next_send_at',
    'failure_count',
    'last_error',
    'exit_reason',
    'enrolled_at',
    'last_step_sent_at',
    'status',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'wtc_drip_enrollments';

$where = [
    'AND ' . db_prefix() . 'wtc_drip_enrollments.sequence_id = ' . $this->ci->db->escape_str($sequence_id),
];

$join = [
    'LEFT JOIN (SELECT sequence_id, COUNT(*) as total_steps FROM ' . db_prefix() . 'wtc_drip_steps GROUP BY sequence_id) as steps_tbl ON steps_tbl.sequence_id = ' . db_prefix() . 'wtc_drip_enrollments.sequence_id'
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['steps_tbl.total_steps as total_steps']);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    
    $row = [];

    $row[] = $aRow['id'];

    $row[] = $aRow['phone_number'];

    $row[] = $aRow['rel_type'];

    $row[] = $aRow['current_step'].' / ' . $aRow['total_steps'];

    $label_class = ['active' => 'success', 'paused' => 'warning', 'processing' => 'default', 'completed' => 'info', 'exited' => 'default'];                              
    $row[] = "<span class='label label-". ($label_class[$aRow['status']] ?? 'default') ."'>". ucfirst($aRow['status']) . "</span>";
    
    $row[] = $aRow['next_send_at'] ? _dt($aRow['next_send_at']) : '-';

    $row[] = $aRow['failure_count'];

    $row[] = _dt($aRow['enrolled_at']);
     
    $row[] = $aRow['last_step_sent_at'] ? _dt($aRow['last_step_sent_at']) : '-';;

    $actions = '';

    if ($aRow['status'] == 'active') {
        $actions = 
            icon_btn('#', 'fa fa-eye', 'btn-default btn-sm', [
                'onclick' => "view_drip_enrollment(" . $aRow['id'] . "); return false;",
                'title' => 'View'
            ]) . ' ' .

            icon_btn('#', 'fa fa-pause', 'btn-default btn-sm', [
                'onclick' => "wb_drip_action('pause', " . $aRow['id'] . "); return false;",
                'title'   => 'Pause'
            ]) . ' ' .

            icon_btn('#', 'fa fa-times', 'btn-default btn-sm', [
                'onclick' => "wb_drip_action('exit', " . $aRow['id'] . "); return false;",
                'title'   => 'Exit'
            ]);
    } elseif ($aRow['status'] == 'paused') {
        $actions = 
            icon_btn('#', 'fa fa-eye', 'btn-default btn-sm', [
                'onclick' => "view_drip_enrollment(" . $aRow['id'] . "); return false;",
                'title' => 'View'
            ]) . ' ' .

            icon_btn('#', 'fa fa-play', 'btn-default btn-sm', [
                'onclick' => "wb_drip_action('resume', " . $aRow['id'] . "); return false;",
                'title'   => 'Resume'
            ]) . ' ' .

            icon_btn('#', 'fa fa-times', 'btn-default btn-sm', [
                'onclick' => "wb_drip_action('exit', " . $aRow['id'] . "); return false;",
                'title'   => 'Exit'
            ]);
    } else{
        $actions = 
           icon_btn('#', 'fa fa-eye', 'btn-default btn-sm', [
                'onclick' => "view_drip_enrollment(" . $aRow['id'] . "); return false;",
                'title' => 'View'
            ]);
    }

    $row[] = $actions;

    $output['aaData'][] = $row;
}
