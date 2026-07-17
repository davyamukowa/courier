<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_q     = count($quotations);
$total_val   = array_sum(array_column($quotations, 'total'));
$draft_q     = count(array_filter($quotations, fn($q) => $q->status == 1));
$sent_q      = count(array_filter($quotations, fn($q) => $q->status == 2));
$accepted_q  = count(array_filter($quotations, fn($q) => $q->status == 4));
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:5;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-file-o"></i></div>
        <div class="kpi-value"><?= $total_q ?></div>
        <div class="kpi-label">Total Quotations</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-pencil-square-o"></i></div>
        <div class="kpi-value"><?= $draft_q ?></div>
        <div class="kpi-label">Draft</div>
    </div>
    <div class="xb-kpi-card purple">
        <div class="kpi-icon"><i class="fa fa-paper-plane-o"></i></div>
        <div class="kpi-value"><?= $sent_q ?></div>
        <div class="kpi-label">Sent</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-thumbs-up"></i></div>
        <div class="kpi-value"><?= $accepted_q ?></div>
        <div class="kpi-label">Accepted</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_q ?> quotation<?= $total_q != 1 ? 's' : '' ?></span>
    <a href="<?= admin_url('purchase/purchase_quotations') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i> Open in Purchase Module</a>
    <a href="<?= admin_url('purchase/purchase_quotations') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($quotations)): ?>
            <div class="alert alert-info">No quotations found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Number</th>
                    <th>Vendor</th>
                    <th>PR Code</th>
                    <th>Date</th>
                    <th>Expiry</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_status_map = [1 => ['default', 'Draft'], 2 => ['info', 'Sent'], 3 => ['danger', 'Declined'], 4 => ['success', 'Accepted'], 5 => ['warning', 'Expired']];
                foreach ($quotations as $i => $q):
                    [$sc, $sl] = $q_status_map[$q->status] ?? ['default', 'Unknown'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($q->number) ?></strong></td>
                    <td><?= htmlspecialchars($q->vendor_name) ?></td>
                    <td><?= htmlspecialchars($q->pr_code) ?: '<span class="text-muted">â€”</span>' ?></td>
                    <td><?= $q->date ? _d($q->date) : 'â€”' ?></td>
                    <td><?= $q->expirydate ? _d($q->expirydate) : 'â€”' ?></td>
                    <td><?= xb_format_money($q->total) ?></td>
                    <td><span class="label label-<?= $sc ?>"><?= $sl ?></span></td>
                    <td class="text-right">
                        <a href="<?= admin_url('purchase/purchase_quotations/view/' . $q->id) ?>" target="_blank"
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



