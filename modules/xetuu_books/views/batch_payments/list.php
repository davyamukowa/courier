<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_inbound  = ($payment_type === 'inbound');
$page_title  = $is_inbound ? 'Customer Batch Payments' : 'Vendor Batch Payments';
$page_sub    = $is_inbound ? 'Group Inbound Receipts' : 'Group Outbound Disbursements';
$new_url     = admin_url('xetuu_books/' . ($is_inbound ? 'batch_payment' : 'vendor_batch_payment'));
$btn_label   = 'New ' . ($is_inbound ? 'Customer' : 'Vendor') . ' Batch Payment';

$total_count  = count($batches);
$total_amount = 0.0;
$posted_count = 0;
$draft_count  = 0;
$journal_totals = [];

foreach ($batches as $b) {
    $total_amount += (float)$b->amount;
    if ($b->state === 'posted') $posted_count++;
    else $draft_count++;
    $jname = $b->journal_name ?? 'Unknown';
    if (!isset($journal_totals[$jname])) $journal_totals[$jname] = 0.0;
    $journal_totals[$jname] += (float)$b->amount;
}
arsort($journal_totals);
?>

<div class="xb-list-page" style="padding:0 16px 16px;">

    <!-- Action bar -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:12px;margin-top:6px;">
        <a href="<?php echo $new_url; ?>" class="btn btn-success btn-sm" style="font-weight:600;">
            <i class="fa fa-plus"></i> <?php echo $btn_label; ?>
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="xb-kpi-grid" style="margin-bottom:12px;">
        <div class="xb-kpi-card <?php echo $is_inbound ? '' : 'warn'; ?>">
            <span class="kpi-icon"><i class="fa fa-layer-group"></i></span>
            <div class="kpi-currency">Total Batches</div>
            <div class="kpi-value"><?php echo number_format($total_count); ?></div>
            <div class="kpi-label">Batch Groups</div>
        </div>
        <div class="xb-kpi-card <?php echo $is_inbound ? '' : 'warn'; ?>">
            <span class="kpi-icon"><i class="fa fa-money"></i></span>
            <div class="kpi-currency">Total Amount</div>
            <div class="kpi-value"><?php echo xb_format_money($total_amount); ?></div>
            <div class="kpi-label"><?php echo $is_inbound ? 'Collected' : 'Disbursed'; ?></div>
        </div>
        <div class="xb-kpi-card blue">
            <span class="kpi-icon"><i class="fa fa-check-circle"></i></span>
            <div class="kpi-currency">Posted</div>
            <div class="kpi-value"><?php echo number_format($posted_count); ?></div>
            <div class="kpi-label">Confirmed</div>
        </div>
        <div class="xb-kpi-card warn">
            <span class="kpi-icon"><i class="fa fa-clock-o"></i></span>
            <div class="kpi-currency">Draft</div>
            <div class="kpi-value"><?php echo number_format($draft_count); ?></div>
            <div class="kpi-label">Pending</div>
        </div>
    </div>

    <div class="row">
        <!-- Batch Table -->
        <div class="col-md-8">
            <div class="panel_s">
                <div class="panel-body" style="padding:0;">
                    <table class="table table-striped table-hover xb-rpt" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Batch Name</th>
                                <th>Journal</th>
                                <th class="text-right">Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding:32px;color:#6b7280;">
                                    No batch payments recorded yet.
                                    <a href="<?php echo $new_url; ?>" style="color:#16a34a;"><?php echo $btn_label; ?> &rarr;</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                            <tr>
                                <td><?php echo _d($b->date); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('xetuu_books/' . ($is_inbound ? 'batch_payment/' : 'vendor_batch_payment/') . $b->id); ?>" style="font-weight:600;color:#374151;">
                                        <?php echo htmlspecialchars($b->name); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($b->journal_name ?? '—'); ?></td>
                                <td class="text-right" style="font-weight:600;"><?php echo xb_format_money($b->amount); ?></td>
                                <td><?php echo xb_state_label($b->state); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar: by Journal -->
        <div class="col-md-4">
            <div class="xb-collapsible-section">
                <div class="xb-section-header"><span>By Journal</span></div>
                <div class="xb-section-body" style="padding:0;">
                    <table class="xb-exec-tbl">
                        <thead><tr>
                            <th>Journal</th>
                            <th class="text-right">Amount</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($journal_totals)): ?>
                            <tr><td colspan="2" class="text-muted text-center" style="padding:18px;">No data.</td></tr>
                        <?php else: foreach ($journal_totals as $jname => $jamount): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($jname); ?></td>
                                <td class="text-right"><?php echo xb_format_money($jamount); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                        <?php if (!empty($journal_totals)): ?>
                        <tfoot>
                            <tr class="total-row">
                                <td>Total</td>
                                <td class="text-right"><?php echo xb_format_money($total_amount); ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
