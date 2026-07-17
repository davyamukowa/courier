<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_ord   = count($orders);
$total_val   = array_sum(array_column($orders, 'total'));
$pending_ord = count(array_filter($orders, fn($o) => $o->approve_status == 0));
$approved    = count(array_filter($orders, fn($o) => $o->approve_status == 1));
$invoiced    = count(array_filter($orders, fn($o) => $o->invoice_count > 0));
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:5;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-shopping-cart"></i></div>
        <div class="kpi-value"><?= $total_ord ?></div>
        <div class="kpi-label">Total Orders</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-clock-o"></i></div>
        <div class="kpi-value"><?= $pending_ord ?></div>
        <div class="kpi-label">Pending Approval</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="kpi-value"><?= $approved ?></div>
        <div class="kpi-label">Approved</div>
    </div>
    <div class="xb-kpi-card purple">
        <div class="kpi-icon"><i class="fa fa-file-text"></i></div>
        <div class="kpi-value"><?= $invoiced ?></div>
        <div class="kpi-label">Invoiced</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_ord ?> order<?= $total_ord != 1 ? 's' : '' ?></span>
    <div>
        <a href="<?= admin_url('xetuu_books/purchase_order_form') ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> New Order</a>
        <a href="<?= admin_url('purchase/purchase_orders') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i></a>
    </div>
    <a href="<?= admin_url('purchase/purchase_orders') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No purchase orders found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order #</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Approval</th>
                    <th>Delivery</th>
                    <th>GL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $appr_map = [0 => ['warn','Pending'], 1 => ['success','Approved'], 2 => ['danger','Rejected']];
                $delv_map = [0 => ['default','Not Started'], 1 => ['info','Partial'], 2 => ['success','Complete']];
                foreach ($orders as $i => $o):
                    [$ac, $al] = $appr_map[$o->approve_status] ?? ['default','â€”'];
                    [$dc, $dl] = $delv_map[$o->delivery_status] ?? ['default','â€”'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($o->pur_order_number) ?></strong></td>
                    <td><?= htmlspecialchars($o->vendor_name) ?></td>
                    <td><?= _d($o->order_date) ?></td>
                    <td><?= xb_format_money($o->total) ?></td>
                    <td><span class="label label-<?= $ac ?>"><?= $al ?></span></td>
                    <td><span class="label label-<?= $dc ?>"><?= $dl ?></span></td>
                    <td>
                        <?php if ($o->invoice_count > 0): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Invoiced (<?= $o->invoice_count ?>)</span>
                        <?php else: ?>
                            <span class="text-muted">â€”</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <a href="<?= admin_url('purchase/purchase_orders/view/' . $o->id) ?>" target="_blank"
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



