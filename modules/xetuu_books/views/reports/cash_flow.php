<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-cf{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-cf-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-cf-tbl td{padding:7px 14px;border-bottom:1px solid #f3f4f6;color:#111827}
.sec-hdr td{background:#374151;color:#fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.08em;padding:8px 14px;border:none}
.detail-row td{padding-left:36px;font-size:12px;color:#374151}
.subtotal-row td{background:#f9fafb;font-weight:700;border-top:1px solid #d1d5db;border-bottom:2px solid #d1d5db}
.gap-row td{height:10px;background:#f9fafb;border:none}
.net-row td{background:#eff6ff;color:#1e40af;font-weight:700;font-size:14px;padding:10px 14px;border-top:2px solid #3b82f6;border-bottom:2px solid #3b82f6}
.closing-row td{background:#1e3a5f;color:#fff;font-weight:700;font-size:14px;padding:12px 14px;border-top:3px solid #2563eb}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-pos{color:#065f46;font-weight:600}.amt-neg{color:#dc2626;font-weight:600}.amt-neu{color:#111827}
.cf-link{color:#2563eb;text-decoration:none;font-size:12px}.cf-link:hover{text-decoration:underline}
.num-col{text-align:right;min-width:160px}
@media print{.no-print{display:none!important}}
</style>

<div class="xb-cf xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-exchange" style="color:#2563eb;margin-right:8px;"></i>Cash Flow Statement</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
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
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Apply</button>
        </form>

        <?php
        $gl_base = admin_url('xetuu_books/reports/general_ledger?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to']));
        $pl_url  = admin_url('xetuu_books/reports/profit_and_loss?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to']));
        ?>

        <!-- KPI cards -->
        <div class="row mbot15 no-print">
            <div class="col-sm-3"><div style="background:#ecfdf5;border-radius:6px;padding:12px;border-left:4px solid #10b981;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Operating CF</div><div style="font-size:17px;font-weight:700;color:<?php echo $operating_cf>=0?'#065f46':'#991b1b'; ?>;"><?php echo xb_format_money($operating_cf); ?></div></div></div>
            <div class="col-sm-3"><div style="background:#eff6ff;border-radius:6px;padding:12px;border-left:4px solid #3b82f6;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Investing CF</div><div style="font-size:17px;font-weight:700;color:<?php echo $investing_cf>=0?'#1e40af':'#991b1b'; ?>;"><?php echo xb_format_money($investing_cf); ?></div></div></div>
            <div class="col-sm-3"><div style="background:#fef3c7;border-radius:6px;padding:12px;border-left:4px solid #f59e0b;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Financing CF</div><div style="font-size:17px;font-weight:700;color:#92400e;"><?php echo xb_format_money($financing_cf); ?></div></div></div>
            <div class="col-sm-3"><div style="background:<?php echo $net_change>=0?'#ecfdf5':'#fef2f2'; ?>;border-radius:6px;padding:12px;border-left:4px solid <?php echo $net_change>=0?'#10b981':'#ef4444'; ?>;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Closing Cash</div><div style="font-size:17px;font-weight:700;color:<?php echo $closing_cash>=0?'#065f46':'#991b1b'; ?>;"><?php echo xb_format_money($closing_cash); ?></div></div></div>
        </div>

        <div style="overflow-x:auto;">
        <table class="xb-cf-tbl">

            <!-- A. OPERATING -->
            <tr class="sec-hdr"><td><i class="fa fa-cogs" style="margin-right:6px;"></i>A. Cash Flows from Operating Activities</td><td class="num-col"></td></tr>
            <tr>
                <td>Net <?php echo $net_profit >= 0 ? 'Profit' : 'Loss'; ?> for the Period &nbsp;<a href="<?php echo $pl_url; ?>" class="cf-link" target="_blank">(view P&amp;L <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $net_profit>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($net_profit); ?></span></td>
            </tr>
            <tr class="detail-row">
                <td>Add: Depreciation &amp; Amortisation &nbsp;<a href="<?php echo $gl_base; ?>" class="cf-link" target="_blank">(GL <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $depreciation>0?'amt-pos':'amt-neu'; ?>"><?php echo xb_format_money($depreciation); ?></span></td>
            </tr>
            <tr class="detail-row">
                <td>Change in Accounts Receivable (Debtors) &nbsp;<a href="<?php echo $gl_base.'&account_type=Receivable'; ?>" class="cf-link" target="_blank">(GL <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $delta_ar>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($delta_ar); ?></span></td>
            </tr>
            <tr class="detail-row">
                <td>Change in Accounts Payable (Creditors) &nbsp;<a href="<?php echo $gl_base.'&account_type=Payable'; ?>" class="cf-link" target="_blank">(GL <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $delta_ap>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($delta_ap); ?></span></td>
            </tr>
            <tr class="detail-row">
                <td>Change in Inventory / Current Assets &nbsp;<a href="<?php echo $gl_base; ?>" class="cf-link" target="_blank">(GL <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $delta_inv>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($delta_inv); ?></span></td>
            </tr>
            <tr class="subtotal-row">
                <td>Net Cash from Operating Activities (A)</td>
                <td class="num-col"><span class="amt <?php echo $operating_cf>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($operating_cf); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="2"></td></tr>

            <!-- B. INVESTING -->
            <tr class="sec-hdr"><td><i class="fa fa-building" style="margin-right:6px;"></i>B. Cash Flows from Investing Activities</td><td class="num-col"></td></tr>
            <tr class="detail-row">
                <td>Net Fixed Asset Movements (Purchases / Disposals) &nbsp;<a href="<?php echo admin_url('xetuu_books/reports/depreciation_schedule'); ?>" class="cf-link" target="_blank">(Depreciation Schedule <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $investing_cf>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($investing_cf); ?></span></td>
            </tr>
            <tr class="subtotal-row">
                <td>Net Cash from Investing Activities (B)</td>
                <td class="num-col"><span class="amt <?php echo $investing_cf>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($investing_cf); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="2"></td></tr>

            <!-- C. FINANCING -->
            <tr class="sec-hdr"><td><i class="fa fa-money" style="margin-right:6px;"></i>C. Cash Flows from Financing Activities</td><td class="num-col"></td></tr>
            <tr class="detail-row">
                <td>Net Equity &amp; Long-term Liability Changes &nbsp;<a href="<?php echo $gl_base; ?>" class="cf-link" target="_blank">(GL <i class="fa fa-external-link"></i>)</a></td>
                <td class="num-col"><span class="amt <?php echo $financing_cf>=0?'amt-pos':'amt-neu'; ?>"><?php echo xb_format_money($financing_cf); ?></span></td>
            </tr>
            <tr class="subtotal-row">
                <td>Net Cash from Financing Activities (C)</td>
                <td class="num-col"><span class="amt <?php echo $financing_cf>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($financing_cf); ?></span></td>
            </tr>
            <tr class="gap-row"><td colspan="2"></td></tr>

            <!-- NET CHANGE -->
            <tr class="net-row">
                <td><i class="fa fa-equals" style="margin-right:8px;"></i>Net Increase / (Decrease) in Cash (A + B + C)</td>
                <td class="num-col" style="font-size:15px;"><span class="amt <?php echo $net_change>=0?'amt-pos':'amt-neg'; ?>"><?php echo xb_format_money($net_change); ?></span></td>
            </tr>
            <tr>
                <td>Opening Cash Balance (start of period)</td>
                <td class="num-col"><span class="amt amt-neu"><?php echo xb_format_money($opening_cash); ?></span></td>
            </tr>
            <tr class="closing-row">
                <td><i class="fa fa-bank" style="margin-right:8px;"></i>CLOSING CASH BALANCE (End of Period)</td>
                <td class="num-col" style="font-size:15px;color:<?php echo $closing_cash>=0?'#6ee7b7':'#fca5a5'; ?>;"><span class="amt"><?php echo xb_format_money($closing_cash); ?></span></td>
            </tr>

        </table>
        </div>

        <div style="margin-top:14px;padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;font-size:11px;color:#78350f;">
            <i class="fa fa-info-circle"></i> <strong>Note:</strong> This statement uses the indirect method. All figures are derived from posted journal entries only. Click account links to drill into the General Ledger.
        </div>
    </div>
</div>
</div>
