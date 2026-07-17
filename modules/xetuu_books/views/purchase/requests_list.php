<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_reqs  = count($requests);
$total_value = array_sum(array_column($requests, 'total'));
$pending     = count(array_filter($requests, fn($r) => $r->status == 1));
$approved    = count(array_filter($requests, fn($r) => $r->status == 2));
$rejected    = count(array_filter($requests, fn($r) => $r->status == 3));
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:5;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="kpi-value"><?= $total_reqs ?></div>
        <div class="kpi-label">Total Requests</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_value) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="kpi-value"><?= $pending ?></div>
        <div class="kpi-label">Pending</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="kpi-value"><?= $approved ?></div>
        <div class="kpi-label">Approved</div>
    </div>
    <div class="xb-kpi-card danger">
        <div class="kpi-icon"><i class="fa fa-times-circle"></i></div>
        <div class="kpi-value"><?= $rejected ?></div>
        <div class="kpi-label">Rejected</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_reqs ?> request<?= $total_reqs != 1 ? 's' : '' ?></span>
    <div>
        <a href="<?= admin_url('xetuu_books/purchase_request_form') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> New Request
        </a>
        <a href="<?= admin_url('purchase/purchase_requests') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i></a>
    </div>
    <a href="<?= admin_url('purchase/purchase_requests') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($requests)): ?>
            <div class="alert alert-info">No purchase requests found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Requester</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $i => $r): ?>
                <?php
                    $status_map = [1 => ['warn', 'Pending'], 2 => ['success', 'Approved'], 3 => ['danger', 'Rejected'], 4 => ['info', 'Ordered']];
                    [$sc, $sl] = $status_map[$r->status] ?? ['default', 'Unknown'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($r->pur_rq_code) ?></strong></td>
                    <td><?= htmlspecialchars($r->pur_rq_name) ?></td>
                    <td><?= htmlspecialchars($r->requester_name) ?></td>
                    <td><?= xb_format_money($r->total) ?></td>
                    <td><span class="label label-<?= $sc ?>"><?= $sl ?></span></td>
                    <td><?= _d($r->request_date) ?></td>
                    <td class="text-right">
                        <a href="<?= admin_url('purchase/purchase_requests/view/' . $r->id) ?>" target="_blank"
                           class="btn btn-xs btn-default" title="View in Purchase Module">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>



