<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_vendor   = ($move_type === 'in_receipt');
$title_main  = $is_vendor ? 'Purchase Receipts' : 'Sales Receipts';
$title_sub   = $is_vendor ? 'Vendor Cash Purchases' : 'Customer Cash Sales';
$create_url  = admin_url('xetuu_books/' . ($is_vendor ? 'vendor_receipt' : 'receipt'));
$edit_url    = admin_url('xetuu_books/' . ($is_vendor ? 'vendor_receipt/' : 'receipt/'));

$total_count   = count($moves);
$total_amount  = 0.0;
$posted_count  = 0;
$draft_count   = 0;
$journal_totals = [];

foreach ($moves as $m) {
    $total_amount += (float)$m->amount_total;
    if ($m->state === 'posted') $posted_count++;
    else $draft_count++;
    $jname = $m->journal_name ?? 'Unknown';
    if (!isset($journal_totals[$jname])) $journal_totals[$jname] = 0.0;
    $journal_totals[$jname] += (float)$m->amount_total;
}
arsort($journal_totals);
?>

<div class="xb-list-page" style="padding:0 16px 16px;">

    <!-- Action bar -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:12px;margin-top:6px;">
        <a href="<?php echo $create_url; ?>" class="btn btn-success btn-sm" style="font-weight:600;">
            <i class="fa fa-plus"></i> New Receipt
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="xb-kpi-grid" style="margin-bottom:12px;">
        <div class="xb-kpi-card <?php echo $is_vendor ? 'warn' : ''; ?>">
            <span class="kpi-icon"><i class="fa fa-file-text-o"></i></span>
            <div class="kpi-currency">Total Receipts</div>
            <div class="kpi-value"><?php echo number_format($total_count); ?></div>
            <div class="kpi-label">All Records</div>
        </div>
        <div class="xb-kpi-card <?php echo $is_vendor ? 'warn' : ''; ?>">
            <span class="kpi-icon"><i class="fa fa-money"></i></span>
            <div class="kpi-currency">Total Value</div>
            <div class="kpi-value"><?php echo xb_format_money($total_amount); ?></div>
            <div class="kpi-label"><?php echo $is_vendor ? 'Purchased' : 'Collected'; ?></div>
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
        <!-- Receipts Table -->
        <div class="col-md-8">
            <div class="panel_s">
                <div class="panel-body" style="padding:0;">
                    <table class="table table-striped table-hover xb-rpt" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th><?php echo $is_vendor ? 'Vendor' : 'Customer'; ?></th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-right">Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($moves)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding:32px;color:#6b7280;">
                                    No receipts found.
                                    <a href="<?php echo $create_url; ?>" style="color:#16a34a;">Create your first receipt &rarr;</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($moves as $m): ?>
                            <tr onclick="window.location='<?php echo $edit_url . $m->id; ?>'" style="cursor:pointer;">
                                <td>
                                    <a href="<?php echo $edit_url . $m->id; ?>" style="color:#374151;font-weight:600;text-decoration:none;">
                                        <?php echo $m->name ?: 'DRAFT'; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($m->partner_name ?? '—'); ?></td>
                                <td><?php echo _d($m->date); ?></td>
                                <td><?php echo htmlspecialchars($m->journal_name ?? '—'); ?></td>
                                <td class="text-right"><?php echo xb_format_money($m->amount_untaxed); ?></td>
                                <td class="text-right" style="font-weight:600;"><?php echo xb_format_money($m->amount_total); ?></td>
                                <td><span class="label label-success">Paid</span></td>
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
