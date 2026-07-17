<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-fs{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-fs-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-fs-tbl th{background:#f3f4f6;padding:7px 14px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-top:2px solid #d1d5db;border-bottom:1px solid #d1d5db}
.xb-fs-tbl td{padding:6px 14px;border-bottom:1px solid #f3f4f6;color:#111827}
.xb-fs-tbl .row-idx{color:#d1d5db;font-size:11px;width:32px;text-align:right;padding-right:8px}
.sec-hdr td{background:#374151;color:#fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.08em;padding:8px 14px;border:none}
.acc-row:hover td{background:#f0f9ff}
.subtotal-row td{background:#f9fafb;font-weight:700;color:#111827;border-top:1px solid #d1d5db;border-bottom:2px solid #d1d5db;font-size:13px}
.balance-check-row td{background:#1e3a5f;color:#fff;font-weight:700;font-size:14px;padding:12px 14px;border-top:3px solid #2563eb}
.gap-row td{height:10px;background:#f9fafb;border:none}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-pos{color:#065f46}.amt-neg{color:#dc2626;font-weight:600}.amt-neu{color:#111827;font-weight:600}
.acc-link{color:#2563eb;text-decoration:none}
.acc-link:hover{text-decoration:underline}
@media print{.no-print{display:none!important}.xb-fs-tbl{font-size:11px}}
</style>

<div class="xb-fs xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-balance-scale" style="color:#2563eb;margin-right:8px;"></i>Balance Sheet</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">As of: <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=balance_sheet&format=csv&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export CSV</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body" style="padding:14px 16px;">

        <!-- Date filter -->
        <form class="form-inline mbot15 no-print" method="GET" style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <div class="form-group"><label style="font-size:12px;font-weight:600;">As of Date:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Update</button>
        </form>

        <?php
        $gl_base  = 'xetuu_books/reports/general_ledger?date_from=1970-01-01&date_to='.urlencode($params['date_to']);
        $is_balanced = abs($total_assets - ($total_liabilities + $total_equity)) < 1;
        ?>

        <!-- Balance check banner -->
        <div style="margin-bottom:14px;padding:8px 14px;background:<?php echo $is_balanced ? '#ecfdf5' : '#fef2f2'; ?>;border-left:4px solid <?php echo $is_balanced ? '#10b981' : '#ef4444'; ?>;border-radius:4px;font-size:12px;font-weight:600;color:<?php echo $is_balanced ? '#065f46' : '#991b1b'; ?>;">
            <?php if ($is_balanced): ?>
            <i class="fa fa-check-circle"></i> Balance Sheet is BALANCED &nbsp;—&nbsp; Assets = Liabilities + Equity = <?php echo xb_format_money($total_assets); ?>
            <?php else: ?>
            <i class="fa fa-exclamation-triangle"></i> UNBALANCED! Assets (<?php echo xb_format_money($total_assets); ?>) ≠ Liabilities + Equity (<?php echo xb_format_money($total_liabilities + $total_equity); ?>). Difference: <?php echo xb_format_money(abs($total_assets - ($total_liabilities + $total_equity))); ?>
            <?php endif; ?>
        </div>

        <div style="overflow-x:auto;">
        <table class="xb-fs-tbl">
            <thead>
                <tr>
                    <th class="row-idx">#</th>
                    <th style="min-width:340px;">Account</th>
                    <th style="text-align:right;min-width:160px;">Balance (as of <?php echo $params['date_to']; ?>)</th>
                </tr>
            </thead>
            <tbody>

            <?php /* ─── ASSETS ─── */ $i = 1; ?>
            <tr class="sec-hdr"><td class="row-idx"></td><td colspan="2"><i class="fa fa-university" style="margin-right:6px;"></i>ASSETS</td></tr>
            <?php foreach ($assets as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
                $bal    = (float)$acc->balance;
            ?>
            <tr class="acc-row">
                <td class="row-idx"><?php echo $i++; ?></td>
                <td style="padding-left:28px;">
                    <a href="<?php echo $gl_url; ?>" class="acc-link">
                        <?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?>
                    </a>
                    <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;" title="General Ledger"><i class="fa fa-external-link"></i></a>
                    <span style="font-size:10px;margin-left:6px;color:#9ca3af;"><?php echo $acc->type; ?></span>
                </td>
                <td style="text-align:right;">
                    <span class="amt <?php echo $bal < 0 ? 'amt-neg' : 'amt-neu'; ?>"><?php echo $bal < 0 ? '('.xb_format_money(abs($bal)).')' : xb_format_money($bal); ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($assets)): ?>
            <tr><td></td><td style="color:#9ca3af;padding-left:28px;font-style:italic;">No asset accounts with activity</td><td style="text-align:right;">0.00</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td class="row-idx"></td>
                <td>TOTAL ASSETS</td>
                <td style="text-align:right;font-size:14px;"><span class="amt <?php echo $total_assets < 0 ? 'amt-neg' : 'amt-pos'; ?>"><?php echo xb_format_money($total_assets); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="3"></td></tr>

            <?php /* ─── LIABILITIES ─── */ ?>
            <tr class="sec-hdr"><td class="row-idx"></td><td colspan="2"><i class="fa fa-credit-card" style="margin-right:6px;"></i>LIABILITIES</td></tr>
            <?php foreach ($liabilities as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
                $bal    = abs((float)$acc->balance);
            ?>
            <tr class="acc-row">
                <td class="row-idx"><?php echo $i++; ?></td>
                <td style="padding-left:28px;">
                    <a href="<?php echo $gl_url; ?>" class="acc-link">
                        <?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?>
                    </a>
                    <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;"><i class="fa fa-external-link"></i></a>
                    <span style="font-size:10px;margin-left:6px;color:#9ca3af;"><?php echo $acc->type; ?></span>
                </td>
                <td style="text-align:right;"><span class="amt amt-neg"><?php echo xb_format_money($bal); ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($liabilities)): ?>
            <tr><td></td><td style="color:#9ca3af;padding-left:28px;font-style:italic;">No liability accounts with activity</td><td style="text-align:right;">0.00</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td class="row-idx"></td>
                <td>TOTAL LIABILITIES</td>
                <td style="text-align:right;font-size:14px;"><span class="amt amt-neg"><?php echo xb_format_money($total_liabilities); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="3"></td></tr>

            <?php /* ─── EQUITY ─── */ ?>
            <tr class="sec-hdr"><td class="row-idx"></td><td colspan="2"><i class="fa fa-pie-chart" style="margin-right:6px;"></i>EQUITY</td></tr>
            <?php foreach ($equity as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
                $bal    = abs((float)$acc->balance);
            ?>
            <tr class="acc-row">
                <td class="row-idx"><?php echo $i++; ?></td>
                <td style="padding-left:28px;">
                    <a href="<?php echo $gl_url; ?>" class="acc-link">
                        <?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?>
                    </a>
                    <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;"><i class="fa fa-external-link"></i></a>
                </td>
                <td style="text-align:right;"><span class="amt amt-neu"><?php echo xb_format_money($bal); ?></span></td>
            </tr>
            <?php endforeach; ?>
            <!-- Retained Earnings (Net Profit / Loss) -->
            <tr class="acc-row">
                <td class="row-idx"><?php echo $i++; ?></td>
                <td style="padding-left:28px;">
                    <a href="<?php echo admin_url('xetuu_books/reports/profit_and_loss?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="acc-link">
                        Retained Earnings (Current Year Net <?php echo $retained_earnings < 0 ? 'Loss' : 'Profit'; ?>)
                    </a>
                    <a href="<?php echo admin_url('xetuu_books/reports/profit_and_loss?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;" title="View P&L"><i class="fa fa-external-link"></i></a>
                </td>
                <td style="text-align:right;">
                    <span class="amt <?php echo $retained_earnings < 0 ? 'amt-neg' : 'amt-pos'; ?>">
                        <?php echo $retained_earnings < 0 ? '('.xb_format_money(abs($retained_earnings)).')' : xb_format_money($retained_earnings); ?>
                    </span>
                </td>
            </tr>
            <tr class="subtotal-row">
                <td class="row-idx"></td>
                <td>TOTAL EQUITY</td>
                <td style="text-align:right;font-size:14px;">
                    <span class="amt <?php echo $total_equity < 0 ? 'amt-neg' : 'amt-pos'; ?>"><?php echo $total_equity < 0 ? '('.xb_format_money(abs($total_equity)).')' : xb_format_money($total_equity); ?></span>
                </td>
            </tr>
            <tr class="gap-row"><td colspan="3"></td></tr>

            <?php /* ─── TOTAL L + E ─── */ ?>
            <tr class="balance-check-row">
                <td class="row-idx" style="background:#1e3a5f;color:#fff;"></td>
                <td>
                    TOTAL LIABILITIES &amp; EQUITY
                    <?php if ($is_balanced): ?>
                    &nbsp;<span style="font-size:11px;font-weight:400;color:#6ee7b7;"><i class="fa fa-check-circle"></i> Balanced</span>
                    <?php else: ?>
                    &nbsp;<span style="font-size:11px;font-weight:400;color:#fca5a5;"><i class="fa fa-exclamation-triangle"></i> Unbalanced</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;font-size:15px;">
                    <span class="amt" style="color:<?php echo $is_balanced ? '#6ee7b7' : '#fca5a5'; ?>;">
                        <?php echo xb_format_money($total_liabilities + $total_equity); ?>
                    </span>
                </td>
            </tr>

            </tbody>
        </table>
        </div>

    </div>
</div>
</div>
