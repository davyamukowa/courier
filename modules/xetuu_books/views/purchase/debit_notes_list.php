<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_dn  = count($debit_notes);
$total_val = array_sum(array_column($debit_notes, 'total'));
$draft_dn  = count(array_filter($debit_notes, fn($d) => $d->status == 1));
$posted_dn = count(array_filter($debit_notes, fn($d) => $d->status == 2));
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:4;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-file-text-o"></i></div>
        <div class="kpi-value"><?= $total_dn ?></div>
        <div class="kpi-label">Total Debit Notes</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-pencil-square-o"></i></div>
        <div class="kpi-value"><?= $draft_dn ?></div>
        <div class="kpi-label">Draft</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="kpi-value"><?= $posted_dn ?></div>
        <div class="kpi-label">Posted</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_dn ?> debit note<?= $total_dn != 1 ? 's' : '' ?></span>
    <a href="<?= admin_url('purchase/purchase_debit_notes') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i> Open in Purchase Module</a>
    <a href="<?= admin_url('purchase/purchase_debit_notes') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($debit_notes)): ?>
            <div class="alert alert-info">No debit notes found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Number</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dn_status_map = [1 => ['default', 'Draft'], 2 => ['success', 'Posted'], 3 => ['danger', 'Cancelled']];
                foreach ($debit_notes as $i => $dn):
                    [$sc, $sl] = $dn_status_map[$dn->status] ?? ['default', 'Unknown'];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($dn->number) ?></strong></td>
                    <td><?= htmlspecialchars($dn->vendor_name) ?></td>
                    <td><?= $dn->date ? _d($dn->date) : 'â€”' ?></td>
                    <td><?= xb_format_money($dn->total) ?></td>
                    <td><span class="label label-<?= $sc ?>"><?= $sl ?></span></td>
                    <td class="text-right">
                        <a href="<?= admin_url('purchase/purchase_debit_notes/view/' . $dn->id) ?>" target="_blank"
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



