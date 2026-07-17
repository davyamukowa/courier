<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-pl{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-pl-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-pl-tbl th{background:#f3f4f6;padding:7px 14px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-top:2px solid #d1d5db;border-bottom:1px solid #d1d5db;white-space:nowrap}
.xb-pl-tbl th.num,.xb-pl-tbl td.num{text-align:right}
.xb-pl-tbl td{padding:6px 14px;border-bottom:1px solid #f3f4f6;color:#111827}
.xb-pl-tbl tr.data-row:hover td{background:#f9fafb}
.xb-pl-tbl .row-idx{color:#d1d5db;font-size:11px;width:28px;text-align:right;padding-right:6px}
.partner-hdr td{background:#1e3a5f;color:#fff;font-weight:700;font-size:13px;padding:9px 14px;border:none}
.partner-hdr a{color:#93c5fd;text-decoration:none}.partner-hdr a:hover{text-decoration:underline}
.partner-total td{background:#f9fafb;font-weight:700;border-top:1px solid #d1d5db;border-bottom:2px solid #d1d5db}
.gap-row td{height:8px;background:#f9fafb;border:none}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-dr{color:#1d4ed8;font-weight:600}.amt-cr{color:#047857;font-weight:600}.amt-neg{color:#dc2626;font-weight:600}.amt-neu{color:#111827}
.jnl-link{color:#7c3aed;text-decoration:none;font-weight:500}.jnl-link:hover{text-decoration:underline}
.vtype{font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;white-space:nowrap;display:inline-block}
.vtype-inv{background:#dbeafe;color:#1e40af}.vtype-bill{background:#fef3c7;color:#92400e}.vtype-jnl{background:#e0e7ff;color:#3730a3}.vtype-pay{background:#d1fae5;color:#065f46}.vtype-cr{background:#fce7f3;color:#9d174d}
@media print{.no-print{display:none!important}}
</style>

<div class="xb-pl xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-users" style="color:#2563eb;margin-right:8px;"></i>Partner Ledger</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=partner_ledger&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export CSV</a>
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

        <?php if (empty($partners)): ?>
            <div class="alert alert-info"><i class="fa fa-info-circle"></i> No partner transactions found for the selected period.</div>
        <?php else: ?>
        <?php
        function xb_pl_voucher_label($move_type) {
            $map = ['out_invoice'=>'Sales Invoice','in_invoice'=>'Vendor Bill','out_refund'=>'Credit Note','in_refund'=>'Vendor Credit','payment'=>'Payment','entry'=>'Journal'];
            return $map[$move_type] ?? 'Journal';
        }
        function xb_pl_voucher_class($move_type) {
            $map = ['out_invoice'=>'vtype-inv','in_invoice'=>'vtype-bill','out_refund'=>'vtype-cr','in_refund'=>'vtype-cr','payment'=>'vtype-pay'];
            return $map[$move_type] ?? 'vtype-jnl';
        }
        ?>
        <div style="overflow-x:auto;">
        <table class="xb-pl-tbl">
            <thead>
                <tr>
                    <th class="row-idx">#</th>
                    <th style="min-width:90px;">Date</th>
                    <th style="min-width:100px;">Voucher Type</th>
                    <th style="min-width:130px;">Voucher No</th>
                    <th style="min-width:200px;">Account</th>
                    <th style="min-width:160px;">Description</th>
                    <th class="num" style="min-width:110px;">Debit</th>
                    <th class="num" style="min-width:110px;">Credit</th>
                    <th class="num" style="min-width:110px;">Balance</th>
                </tr>
            </thead>
            <tbody>
            <?php $row = 1; foreach ($partners as $p): $running = 0; ?>
                <tr class="partner-hdr">
                    <td class="row-idx" style="background:#1e3a5f;color:#60a5fa;"></td>
                    <td colspan="8">
                        <i class="fa fa-user" style="margin-right:6px;color:#60a5fa;"></i>
                        <?php echo htmlspecialchars($p['partner_name']); ?>
                        <span style="font-size:10px;font-weight:400;color:#93c5fd;margin-left:12px;"><?php echo count($p['lines']); ?> entries</span>
                    </td>
                </tr>
                <?php foreach ($p['lines'] as $line):
                    $running += $line->balance;
                    $vt = $line->move_type ?? 'entry';
                ?>
                <tr class="data-row">
                    <td class="row-idx"><?php echo $row++; ?></td>
                    <td style="white-space:nowrap;font-size:12px;"><?php echo $line->move_date; ?></td>
                    <td><span class="vtype <?php echo xb_pl_voucher_class($vt); ?>"><?php echo xb_pl_voucher_label($vt); ?></span></td>
                    <td>
                        <?php if (!empty($line->move_name)): ?>
                        <a href="<?php echo admin_url('xetuu_books/journal_entry/'.$line->move_id); ?>" class="jnl-link" target="_blank"><?php echo htmlspecialchars($line->move_name); ?></a>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7280;"><?php echo htmlspecialchars($line->account_code . ' ' . $line->account_name); ?></td>
                    <td style="font-size:12px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($line->name ?? ''); ?>"><?php echo htmlspecialchars($line->name ?? ''); ?></td>
                    <td class="num"><span class="amt amt-dr"><?php echo $line->debit  > 0 ? xb_format_money($line->debit)  : ''; ?></span></td>
                    <td class="num"><span class="amt amt-cr"><?php echo $line->credit > 0 ? xb_format_money($line->credit) : ''; ?></span></td>
                    <td class="num"><span class="amt <?php echo $running < 0 ? 'amt-neg' : 'amt-neu'; ?>"><?php echo xb_format_money($running); ?></span></td>
                </tr>
                <?php endforeach; ?>
                <tr class="partner-total">
                    <td class="row-idx"></td>
                    <td colspan="5">Total — <?php echo htmlspecialchars($p['partner_name']); ?></td>
                    <td class="num"><span class="amt amt-dr"><?php echo xb_format_money($p['total_debit']); ?></span></td>
                    <td class="num"><span class="amt amt-cr"><?php echo xb_format_money($p['total_credit']); ?></span></td>
                    <td class="num"><span class="amt <?php echo $p['balance'] < 0 ? 'amt-neg' : 'amt-neu'; ?>"><?php echo xb_format_money($p['balance']); ?></span></td>
                </tr>
                <tr class="gap-row"><td colspan="9"></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
