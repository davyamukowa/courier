<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_c   = count($contracts);
$signed    = count(array_filter($contracts, fn($c) => $c->signed == 1));
$unsigned  = $total_c - $signed;
$expired   = count(array_filter($contracts, fn($c) => $c->end_date && strtotime($c->end_date) < time()));
$total_val = array_sum(array_column($contracts, 'contract_value'));
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:5;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-file-text"></i></div>
        <div class="kpi-value"><?= $total_c ?></div>
        <div class="kpi-label">Total Contracts</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Value</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-pencil"></i></div>
        <div class="kpi-value"><?= $signed ?></div>
        <div class="kpi-label">Signed</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="kpi-value"><?= $unsigned ?></div>
        <div class="kpi-label">Awaiting Signature</div>
    </div>
    <div class="xb-kpi-card danger">
        <div class="kpi-icon"><i class="fa fa-calendar-times-o"></i></div>
        <div class="kpi-value"><?= $expired ?></div>
        <div class="kpi-label">Expired</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_c ?> contract<?= $total_c != 1 ? 's' : '' ?></span>
    <div>
        <a href="<?= admin_url('xetuu_books/purchase_contract_form') ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> New Contract</a>
        <a href="<?= admin_url('purchase/purchase_contracts') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i></a>
    </div>
    <a href="<?= admin_url('purchase/purchase_contracts') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($contracts)): ?>
            <div class="alert alert-info">No contracts found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Contract #</th>
                    <th>Name</th>
                    <th>Vendor</th>
                    <th>Value</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Signature</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $i => $c): ?>
                <?php
                    $is_expired  = $c->end_date && strtotime($c->end_date) < time();
                    $is_signed   = (int)$c->signed === 1;
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($c->contract_number) ?></strong></td>
                    <td><?= htmlspecialchars($c->contract_name) ?></td>
                    <td><?= htmlspecialchars($c->vendor_name) ?></td>
                    <td><?= xb_format_money($c->contract_value) ?></td>
                    <td><?= $c->start_date ? _d($c->start_date) : 'â€”' ?></td>
                    <td>
                        <?php if ($c->end_date): ?>
                            <span class="<?= $is_expired ? 'text-danger' : '' ?>"><?= _d($c->end_date) ?></span>
                            <?php if ($is_expired): ?><br><small class="text-danger">Expired</small><?php endif; ?>
                        <?php else: ?><span class="text-muted">â€”</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($is_signed): ?>
                            <span class="label label-success">
                                <i class="fa fa-check"></i> Signed
                            </span>
                            <?php if ($c->signer_name): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($c->signer_name) ?></small>
                            <?php endif; ?>
                            <?php if ($c->signed_date): ?>
                                <br><small class="text-muted"><?= _d($c->signed_date) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="label label-warning">
                                <i class="fa fa-clock-o"></i> Unsigned
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php if (!$is_signed && has_permission('purchase_contracts', '', 'edit')): ?>
                        <a href="#" class="btn btn-xs btn-success xb-sign-contract"
                           data-id="<?= $c->id ?>"
                           data-num="<?= htmlspecialchars($c->contract_number) ?>"
                           title="Sign this contract">
                            <i class="fa fa-pencil"></i> Sign
                        </a>
                        <?php endif; ?>
                        <a href="<?= admin_url('purchase/purchase_contracts/view/' . $c->id) ?>" target="_blank"
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

<!-- Sign Contract Confirmation Modal -->
<div class="modal fade" id="signContractModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a7431;color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Sign Contract</h4>
            </div>
            <div class="modal-body">
                <p>You are about to sign contract <strong id="signContractNum"></strong>.</p>
                <p class="text-muted">This action will be recorded with your name and the current date/time and cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a href="#" id="signContractConfirm" class="btn btn-success">
                    <i class="fa fa-pencil"></i> Confirm Signature
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $('.xb-sign-contract').on('click', function(e) {
        e.preventDefault();
        var id  = $(this).data('id');
        var num = $(this).data('num');
        $('#signContractNum').text(num);
        $('#signContractConfirm').attr('href', '<?= admin_url('xetuu_books/sign_purchase_contract/') ?>' + id);
        $('#signContractModal').modal('show');
    });
});
</script>



