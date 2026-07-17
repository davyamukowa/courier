<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_r   = count($returns);
$total_val = array_sum(array_column($returns, 'total'));
?>

<?php if (empty($returns) && !isset($returns[0])): ?>
<!-- Table doesn't exist or module not installed -->
<div class="alert alert-warning">
    <i class="fa fa-info-circle"></i>
    <strong>Order Returns</strong> â€” The purchase order returns table is not available.
    This feature may not be installed in your version of the Purchase module.
    <a href="<?= admin_url('purchase') ?>" target="_blank" class="alert-link">Open Purchase Module</a>
    to manage order returns.
</div>
<?php else: ?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:4;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-undo"></i></div>
        <div class="kpi-value"><?= $total_r ?></div>
        <div class="kpi-label">Total Returns</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="kpi-value"><?= count(array_filter($returns, fn($r) => $r->status == 1)) ?></div>
        <div class="kpi-label">Pending</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="kpi-value"><?= count(array_filter($returns, fn($r) => $r->status == 2)) ?></div>
        <div class="kpi-label">Approved</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_r ?> return<?= $total_r != 1 ? 's' : '' ?></span>
    <a href="<?= admin_url('purchase/purchase_order_returns') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($returns)): ?>
            <div class="alert alert-info">No order returns found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Return Code</th>
                    <th>Name</th>
                    <th>Vendor</th>
                    <th>Order #</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ret_status_map = [1 => ['warn', 'Pending'], 2 => ['success', 'Approved'], 3 => ['danger', 'Rejected']];
                foreach ($returns as $i => $r):
                    [$sc, $sl] = $ret_status_map[$r->status] ?? ['default', 'Unknown'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($r->return_code) ?></strong></td>
                    <td><?= htmlspecialchars($r->return_name) ?></td>
                    <td><?= htmlspecialchars($r->vendor_name) ?></td>
                    <td><?= htmlspecialchars($r->pur_order_number) ?: 'â€”' ?></td>
                    <td><?= xb_format_money($r->total) ?></td>
                    <td><span class="label label-<?= $sc ?>"><?= $sl ?></span></td>
                    <td><?= _d($r->datecreated) ?></td>
                    <td class="text-right">
                        <a href="<?= admin_url('purchase/purchase_order_returns/view/' . $r->id) ?>" target="_blank"
                           class="btn btn-xs btn-default">
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
<?php endif; ?>


