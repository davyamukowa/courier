<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-list-page" style="padding:0 0 16px;">

    <!-- Action bar -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:12px;margin-top:6px;">
        <a href="<?php echo admin_url('xetuu_books/bill_form'); ?>" class="btn btn-success btn-sm" style="font-weight:600;">
            <i class="fa fa-plus"></i> New Bill
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="xb-kpi-grid" style="margin-bottom:16px;">
        <div class="xb-kpi-card warn">
            <span class="kpi-icon"><i class="fa fa-file-text-o"></i></span>
            <div class="kpi-currency">Total Untaxed</div>
            <div class="kpi-value"><?php echo xb_format_money($list_totals->amount_untaxed ?? 0); ?></div>
            <div class="kpi-label">Excl. Tax</div>
        </div>
        <div class="xb-kpi-card warn">
            <span class="kpi-icon"><i class="fa fa-percent"></i></span>
            <div class="kpi-currency">Total Tax</div>
            <div class="kpi-value"><?php echo xb_format_money($list_totals->amount_tax ?? 0); ?></div>
            <div class="kpi-label">Tax Amount</div>
        </div>
        <div class="xb-kpi-card blue">
            <span class="kpi-icon"><i class="fa fa-money"></i></span>
            <div class="kpi-currency">Total Amount</div>
            <div class="kpi-value"><?php echo xb_format_money($list_totals->amount_total ?? 0); ?></div>
            <div class="kpi-label">Incl. Tax</div>
        </div>
        <div class="xb-kpi-card danger">
            <span class="kpi-icon"><i class="fa fa-exclamation-circle"></i></span>
            <div class="kpi-currency">Amount Due</div>
            <div class="kpi-value"><?php echo xb_format_money($list_totals->amount_residual ?? 0); ?></div>
            <div class="kpi-label">Outstanding</div>
        </div>
    </div>

    <!-- Table -->
    <div class="panel_s">
        <div class="panel-body" style="padding:0;">
            <table class="table table-striped table-hover xb-rpt" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Vendor</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-right">Untaxed</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($moves)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding:32px;color:#6b7280;">No bills found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($moves as $m): ?>
                    <tr>
                        <td><a href="<?php echo admin_url('xetuu_books/bill_form/' . $m->id); ?>"><b><?php echo $m->name ?: 'Draft'; ?></b></a></td>
                        <td><?php echo xb_get_partner_name($m->partner_id, 'vendor'); ?></td>
                        <td><?php echo _d($m->date); ?></td>
                        <td><?php echo _d($m->invoice_date_due); ?></td>
                        <td class="text-right">
                            <?php if (!empty($m->currency_id) && $m->currency_id != 1): ?>
                                <span class="label label-info" style="font-size:10px;margin-right:2px;"><?php echo xb_get_currency_code($m->currency_id); ?></span>
                                <?php echo xb_get_currency_symbol($m->currency_id) . ' ' . number_format($m->amount_untaxed, 2); ?>
                            <?php else: ?>
                                <?php echo xb_format_money($m->amount_untaxed); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right" style="font-weight:600;">
                            <?php if (!empty($m->currency_id) && $m->currency_id != 1): ?>
                                <?php $cur_symbol = xb_get_currency_symbol($m->currency_id); ?>
                                <?php echo $cur_symbol . ' ' . number_format($m->amount_total, 2); ?>
                                <?php $rate = (float)($m->exchange_rate ?? 1); if ($rate > 0): ?>
                                <br><small class="text-muted">≈ <?php echo number_format($m->amount_total * $rate, 2); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo xb_format_money($m->amount_total); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo xb_state_label($m->state); ?></td>
                        <td><?php echo xb_state_label($m->payment_state); ?></td>
                        <td>
                            <a href="<?php echo admin_url('xetuu_books/bill_form/' . $m->id); ?>" class="btn btn-default btn-icon btn-xs"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
