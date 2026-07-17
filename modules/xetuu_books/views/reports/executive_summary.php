<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card xb-rpt">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-dashboard" style="color:#2563eb;margin-right:8px;"></i> Executive Summary</span>
                <form class="form-inline" method="GET" style="display:flex;align-items:center;gap:8px;">
                    <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
                    <span style="color:#9ca3af;font-size:12px;">to</span>
                    <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
                    <button class="btn btn-primary btn-sm" style="white-space:nowrap;"><i class="fa fa-refresh"></i> Apply</button>
                </form>
            </div>
            <div class="xb-card-body">

                <!-- KPI Cards -->
                <div class="xb-kpi-grid">
                    <div class="xb-kpi-card">
                        <span class="kpi-icon"><i class="fa fa-arrow-up"></i></span>
                        <div class="kpi-currency">Total Revenue</div>
                        <div class="kpi-value"><?php echo xb_format_money($total_revenue ?? 0); ?></div>
                        <div class="kpi-label">Revenue</div>
                    </div>
                    <div class="xb-kpi-card warn">
                        <span class="kpi-icon"><i class="fa fa-arrow-down"></i></span>
                        <div class="kpi-currency">Total Expenses</div>
                        <div class="kpi-value"><?php echo xb_format_money($total_expense ?? 0); ?></div>
                        <div class="kpi-label">Expenses</div>
                    </div>
                    <div class="xb-kpi-card <?php echo ($net_profit??0) >= 0 ? '' : 'danger'; ?>">
                        <span class="kpi-icon"><i class="fa fa-line-chart"></i></span>
                        <div class="kpi-currency">Net Profit</div>
                        <div class="kpi-value"><?php echo xb_format_money($net_profit ?? 0); ?></div>
                        <div class="kpi-label"><?php echo ($net_profit??0) >= 0 ? 'Profit' : 'Loss'; ?></div>
                    </div>
                    <div class="xb-kpi-card blue">
                        <span class="kpi-icon"><i class="fa fa-university"></i></span>
                        <div class="kpi-currency">Total Assets</div>
                        <div class="kpi-value"><?php echo xb_format_money($total_assets ?? 0); ?></div>
                        <div class="kpi-label">Assets</div>
                    </div>
                    <div class="xb-kpi-card purple">
                        <span class="kpi-icon"><i class="fa fa-inbox"></i></span>
                        <div class="kpi-currency">Accounts Receivable</div>
                        <div class="kpi-value"><?php echo xb_format_money($unpaid_ar ?? 0); ?></div>
                        <div class="kpi-label">Receivable</div>
                    </div>
                    <div class="xb-kpi-card warn">
                        <span class="kpi-icon"><i class="fa fa-credit-card"></i></span>
                        <div class="kpi-currency">Accounts Payable</div>
                        <div class="kpi-value"><?php echo xb_format_money($unpaid_ap ?? 0); ?></div>
                        <div class="kpi-label">Payable</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="xb-collapsible-section">
                            <div class="xb-section-header"><span>Revenue Breakdown</span></div>
                            <div class="xb-section-body" style="padding:0;">
                                <table class="xb-exec-tbl">
                                    <thead><tr>
                                        <th>Account</th>
                                        <th class="text-right">Amount</th>
                                    </tr></thead>
                                    <tbody>
                                    <?php if (!empty($revenue)): foreach ($revenue as $acc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($acc->name); ?></td>
                                        <td class="text-right"><?php echo xb_format_money(abs($acc->balance)); ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="2" class="text-muted text-center" style="padding:18px;">No revenue in this period.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                    <tr class="total-row">
                                        <td>Total Revenue</td>
                                        <td class="text-right"><?php echo xb_format_money($total_revenue ?? 0); ?></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="xb-collapsible-section">
                            <div class="xb-section-header"><span>Top Expense Categories</span></div>
                            <div class="xb-section-body" style="padding:0;">
                                <table class="xb-exec-tbl">
                                    <thead><tr>
                                        <th>Category</th>
                                        <th class="text-right">Amount</th>
                                    </tr></thead>
                                    <tbody>
                                    <?php if (!empty($expense)): foreach (array_slice($expense, 0, 8) as $acc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($acc->name); ?></td>
                                        <td class="text-right"><?php echo xb_format_money(abs($acc->balance)); ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="2" class="text-muted text-center" style="padding:18px;">No expenses in this period.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                    <tr class="total-row warn">
                                        <td>Total Expenses</td>
                                        <td class="text-right"><?php echo xb_format_money($total_expense ?? 0); ?></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
