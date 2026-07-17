<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_inv   = count($invoices);
$total_val   = array_sum(array_column($invoices, 'total'));
$paid        = count(array_filter($invoices, fn($i) => $i->payment_status === 'paid'));
$partial     = count(array_filter($invoices, fn($i) => $i->payment_status === 'partially_paid'));
$unpaid      = count(array_filter($invoices, fn($i) => $i->payment_status === 'unpaid' || !$i->payment_status));
$gl_posted   = count(array_filter($invoices, fn($i) => $i->gl_state === 'posted'));
$unpaid_val  = array_sum(array_map(fn($i) => ($i->payment_status !== 'paid') ? $i->total : 0, $invoices));

// Payment modal
$pay_modal_open = false;
?>

<!-- KPI Cards -->
<div class="xb-kpi-grid" style="--kpi-cols:6;">
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-file-text"></i></div>
        <div class="kpi-value"><?= $total_inv ?></div>
        <div class="kpi-label">Total Invoices</div>
    </div>
    <div class="xb-kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-money"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($total_val) ?></div>
        <div class="kpi-label">Total Billed</div>
    </div>
    <div class="xb-kpi-card danger">
        <div class="kpi-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="kpi-value kpi-currency"><?= xb_format_money($unpaid_val) ?></div>
        <div class="kpi-label">Unpaid Amount</div>
    </div>
    <div class="xb-kpi-card">
        <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
        <div class="kpi-value"><?= $paid ?></div>
        <div class="kpi-label">Paid</div>
    </div>
    <div class="xb-kpi-card warn">
        <div class="kpi-icon"><i class="fa fa-adjust"></i></div>
        <div class="kpi-value"><?= $partial ?></div>
        <div class="kpi-label">Partial</div>
    </div>
    <div class="xb-kpi-card purple">
        <div class="kpi-icon"><i class="fa fa-book"></i></div>
        <div class="kpi-value"><?= $gl_posted ?></div>
        <div class="kpi-label">GL Posted</div>
    </div>
</div>

<!-- Action Bar -->
<div class="xb-action-bar">
    <span class="text-muted"><?= $total_inv ?> invoice<?= $total_inv != 1 ? 's' : '' ?></span>
    <div>
        <a href="<?= admin_url('xetuu_books/purchase_invoice_form') ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> New Invoice</a>
        <a href="<?= admin_url('purchase/purchase_invoices') ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i></a>
    </div>
    <a href="<?= admin_url('purchase/purchase_invoices') ?>" target="_blank"
       class="btn btn-default btn-sm">
        <i class="fa fa-external-link"></i> Open in Purchase Module
    </a>
</div>

<!-- Table -->
<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($invoices)): ?>
            <div class="alert alert-info">No purchase invoices found.</div>
        <?php else: ?>
        <table class="table xb-exec-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice #</th>
                    <th>Vendor</th>
                    <th>Order #</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>GL Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pay_map = [
                    'unpaid'         => ['danger', 'Unpaid'],
                    'partially_paid' => ['warning', 'Partial'],
                    'paid'           => ['success', 'Paid'],
                ];
                $gl_map = [
                    'draft'   => ['default', 'Draft'],
                    'posted'  => ['success', 'Posted'],
                    'cancel'  => ['danger', 'Cancelled'],
                ];
                foreach ($invoices as $i => $inv):
                    [$pc, $pl] = $pay_map[$inv->payment_status] ?? ['default', 'Unknown'];
                    [$gc, $gl] = $inv->gl_state ? ($gl_map[$inv->gl_state] ?? ['default', $inv->gl_state]) : ['default', 'Not Posted'];
                    $is_overdue = $inv->payment_status !== 'paid' && $inv->duedate && strtotime($inv->duedate) < time();
                ?>
                <tr class="<?= $is_overdue ? 'danger' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($inv->number) ?></strong></td>
                    <td><?= htmlspecialchars($inv->vendor_name) ?></td>
                    <td><?= $inv->pur_order_number ? htmlspecialchars($inv->pur_order_number) : '<span class="text-muted">&mdash;</span>' ?></td>
                    <td><?= $inv->invoice_date ? _d($inv->invoice_date) : 'â€”' ?></td>
                    <td>
                        <?php if ($inv->duedate): ?>
                            <span class="<?= $is_overdue ? 'text-danger' : '' ?>"><?= _d($inv->duedate) ?></span>
                            <?php if ($is_overdue): ?><br><small class="text-danger">Overdue</small><?php endif; ?>
                        <?php else: ?><span class="text-muted">â€”</span><?php endif; ?>
                    </td>
                    <td><?= xb_format_money($inv->total) ?></td>
                    <td><span class="label label-<?= $pc ?>"><?= $pl ?></span></td>
                    <td>
                        <span class="label label-<?= $gc ?>"><?= $gl ?></span>
                        <?php if ($inv->gl_ref): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($inv->gl_ref) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right" style="white-space:nowrap;">
                        <?php if ($inv->payment_status !== 'paid' && has_permission('purchase_invoices', '', 'edit')): ?>
                        <button class="btn btn-xs btn-success xb-pay-inv"
                                data-id="<?= $inv->id ?>"
                                data-num="<?= htmlspecialchars($inv->number) ?>"
                                data-total="<?= $inv->total ?>"
                                title="Record Payment">
                            <i class="fa fa-credit-card"></i> Pay
                        </button>
                        <?php endif; ?>
                        <a href="<?= admin_url('purchase/purchase_invoices/view/' . $inv->id) ?>" target="_blank"
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

<!-- Record Payment Modal -->
<div class="modal fade" id="payInvModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a7431;color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-credit-card"></i> Record Payment â€” <span id="payInvNum"></span></h4>
            </div>
            <form id="payInvForm" method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="amount" id="payInvAmount" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="paymentmode" class="form-control">
                            <option value="Bank">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Note <small class="text-muted">(optional)</small></label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="alert alert-info" style="font-size:12px;margin-bottom:0;">
                        <i class="fa fa-info-circle"></i>
                        Payment will be recorded in the Purchase module <strong>and</strong> posted to the accounting ledger.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    $('.xb-pay-inv').on('click', function() {
        var id    = $(this).data('id');
        var num   = $(this).data('num');
        var total = $(this).data('total');
        $('#payInvNum').text(num);
        $('#payInvAmount').val(parseFloat(total).toFixed(2));
        $('#payInvForm').attr('action', '<?= admin_url('xetuu_books/purchase_invoice_payment/') ?>' + id);
        $('#payInvModal').modal('show');
    });
});
</script>



