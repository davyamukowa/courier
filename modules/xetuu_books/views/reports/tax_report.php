<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-tax{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-tx-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-tx-tbl th{background:#f3f4f6;padding:7px 14px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-top:2px solid #d1d5db;border-bottom:1px solid #d1d5db;white-space:nowrap}
.xb-tx-tbl th.num,.xb-tx-tbl td.num{text-align:right}
.xb-tx-tbl td{padding:6px 14px;border-bottom:1px solid #f3f4f6;color:#111827}
.xb-tx-tbl tr:hover td{background:#f9fafb}
.sec-hdr td{background:#374151;color:#fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.08em;padding:8px 14px;border:none}
.subtotal-row td{background:#f9fafb;font-weight:700;border-top:1px solid #d1d5db;border-bottom:2px solid #d1d5db}
.net-row-payable td{background:#ecfdf5;color:#065f46;font-weight:700;font-size:14px;padding:12px 14px;border-top:3px solid #10b981}
.net-row-credit td{background:#fef2f2;color:#991b1b;font-weight:700;font-size:14px;padding:12px 14px;border-top:3px solid #ef4444}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-pos{color:#065f46}.amt-neg{color:#dc2626;font-weight:600}
.gap-row td{height:8px;background:#f9fafb;border:none}
@media print{.no-print{display:none!important}}
</style>

<div class="xb-tax xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-percent" style="color:#dc2626;margin-right:8px;"></i>Tax Report (VAT Return)</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=tax_report&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export CSV</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body" style="padding:14px 16px;">

        <form class="form-inline mbot15 no-print" method="GET" style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <div class="form-group"><label style="font-size:12px;font-weight:600;">From:&nbsp;</label>
                <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
            </div>
            <div class="form-group" style="margin-left:10px;"><label style="font-size:12px;font-weight:600;">To:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Update</button>
        </form>

        <!-- VAT position summary cards -->
        <div class="row mbot15 no-print">
            <div class="col-sm-4">
                <div style="background:#eff6ff;border-radius:6px;padding:12px 14px;border-left:4px solid #3b82f6;">
                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;">Output VAT (Sales)</div>
                    <div style="font-size:20px;font-weight:700;color:#1e40af;"><?php echo xb_format_money($total_output); ?></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div style="background:#fef3c7;border-radius:6px;padding:12px 14px;border-left:4px solid #f59e0b;">
                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;">Input VAT (Purchases)</div>
                    <div style="font-size:20px;font-weight:700;color:#92400e;"><?php echo xb_format_money($total_input); ?></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div style="background:<?php echo $net_payable >= 0 ? '#ecfdf5' : '#fef2f2'; ?>;border-radius:6px;padding:12px 14px;border-left:4px solid <?php echo $net_payable >= 0 ? '#10b981' : '#ef4444'; ?>;">
                    <div style="font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;">Net VAT <?php echo $net_payable >= 0 ? 'Payable' : 'Refundable'; ?></div>
                    <div style="font-size:20px;font-weight:700;color:<?php echo $net_payable >= 0 ? '#065f46' : '#991b1b'; ?>;"><?php echo xb_format_money(abs($net_payable)); ?></div>
                </div>
            </div>
        </div>

        <div style="overflow-x:auto;">
        <table class="xb-tx-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tax Name</th>
                    <th class="num">Tax Rate (%)</th>
                    <th class="num">Taxable Base Amount</th>
                    <th class="num">Tax Amount</th>
                </tr>
            </thead>
            <tbody>

            <!-- OUTPUT VAT (Sales) -->
            <tr class="sec-hdr"><td></td><td colspan="4"><i class="fa fa-arrow-up" style="margin-right:6px;"></i>OUTPUT VAT — Collected on Sales</td></tr>
            <?php $i = 1; foreach ($output_tax as $row): ?>
            <tr>
                <td style="color:#d1d5db;font-size:11px;"><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row->tax_name ?? 'Unnamed Tax'); ?></td>
                <td class="num"><span class="amt"><?php echo number_format((float)$row->tax_rate, 1); ?>%</span></td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money($row->base_amount); ?></span></td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money($row->tax_amount); ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($output_tax)): ?>
            <tr><td></td><td colspan="4" style="color:#9ca3af;font-style:italic;">No output VAT entries in this period</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td></td>
                <td colspan="3"><strong>Total Output VAT</strong></td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money($total_output); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="5"></td></tr>

            <!-- INPUT VAT (Purchases) -->
            <tr class="sec-hdr"><td></td><td colspan="4"><i class="fa fa-arrow-down" style="margin-right:6px;"></i>INPUT VAT — Paid on Purchases (Reclaimable)</td></tr>
            <?php foreach ($input_tax as $row): ?>
            <tr>
                <td style="color:#d1d5db;font-size:11px;"><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row->tax_name ?? 'Unnamed Tax'); ?></td>
                <td class="num"><span class="amt"><?php echo number_format((float)$row->tax_rate, 1); ?>%</span></td>
                <td class="num"><span class="amt" style="color:#92400e;"><?php echo xb_format_money($row->base_amount); ?></span></td>
                <td class="num"><span class="amt" style="color:#92400e;"><?php echo xb_format_money($row->tax_amount); ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($input_tax)): ?>
            <tr><td></td><td colspan="4" style="color:#9ca3af;font-style:italic;">No input VAT entries in this period</td></tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td></td>
                <td colspan="3"><strong>Total Input VAT</strong></td>
                <td class="num"><span class="amt" style="color:#92400e;">(<?php echo xb_format_money($total_input); ?>)</span></td>
            </tr>
            <tr class="gap-row"><td colspan="5"></td></tr>

            <!-- NET PAYABLE -->
            <tr class="<?php echo $net_payable >= 0 ? 'net-row-payable' : 'net-row-credit'; ?>">
                <td></td>
                <td colspan="3">
                    <i class="fa fa-<?php echo $net_payable >= 0 ? 'check-circle' : 'exclamation-triangle'; ?>" style="margin-right:8px;"></i>
                    NET VAT <?php echo $net_payable >= 0 ? 'PAYABLE TO TAX AUTHORITY' : 'REFUNDABLE FROM TAX AUTHORITY'; ?>
                </td>
                <td class="num" style="font-size:15px;">
                    <span class="amt <?php echo $net_payable >= 0 ? 'amt-pos' : 'amt-neg'; ?>">
                        <?php echo $net_payable < 0 ? '('.xb_format_money(abs($net_payable)).')' : xb_format_money($net_payable); ?>
                    </span>
                </td>
            </tr>

            </tbody>
        </table>
        </div>

        <div style="margin-top:14px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;font-size:11px;color:#64748b;">
            <i class="fa fa-info-circle"></i> <strong>Note:</strong> Output VAT is collected from customers on sales invoices and must be remitted to the tax authority.
            Input VAT is paid to suppliers on purchase invoices and can be offset against Output VAT.
            The Net VAT <?php echo $net_payable >= 0 ? 'payable is due to the tax authority.' : 'credit is claimable from the tax authority.'; ?>
        </div>

    </div>
</div>
</div>
