<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-jr{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-jr-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-jr-tbl th{background:#f3f4f6;padding:7px 14px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-top:2px solid #d1d5db;border-bottom:1px solid #d1d5db;white-space:nowrap}
.xb-jr-tbl th.num,.xb-jr-tbl td.num{text-align:right}
.xb-jr-tbl td{padding:5px 14px;border-bottom:1px solid #f3f4f6;color:#111827}
.xb-jr-tbl tr.data-row:hover td{background:#f9fafb}
.xb-jr-tbl .row-idx{color:#d1d5db;font-size:11px;width:28px;text-align:right;padding-right:6px}
.jnl-hdr td{background:#374151;color:#fff;font-weight:700;font-size:12px;padding:8px 14px;border:none}
.jnl-hdr a{color:#d1d5db;text-decoration:none}.jnl-hdr a:hover{text-decoration:underline;color:#fff}
.jnl-subtotal td{background:#f9fafb;font-weight:700;border-top:1px solid #d1d5db;border-bottom:2px solid #d1d5db;font-size:12px}
.jnl-grand td{background:#1e3a5f;color:#fff;font-weight:700;font-size:13px;padding:10px 14px}
.gap-row td{height:8px;background:#f9fafb;border:none}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-dr{color:#1d4ed8;font-weight:600}.amt-cr{color:#047857;font-weight:600}
.jnl-link{color:#7c3aed;text-decoration:none;font-weight:500}.jnl-link:hover{text-decoration:underline}
.vtype{font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;white-space:nowrap;display:inline-block}
.vtype-inv{background:#dbeafe;color:#1e40af}.vtype-bill{background:#fef3c7;color:#92400e}.vtype-jnl{background:#e0e7ff;color:#3730a3}.vtype-pay{background:#d1fae5;color:#065f46}.vtype-cr{background:#fce7f3;color:#9d174d}
@media print{.no-print{display:none!important}}
</style>

<div class="xb-jr xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-book" style="color:#374151;margin-right:8px;"></i>Journal Report</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=general_ledger&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export CSV</a>
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

        <?php if (empty($ledger)): ?>
            <div class="alert alert-info"><i class="fa fa-info-circle"></i> No journal entries found for the selected period.</div>
        <?php else: ?>
        <?php
        function xb_jr_voucher_label($move_type) {
            $map = ['out_invoice'=>'Sales Invoice','in_invoice'=>'Vendor Bill','out_refund'=>'Credit Note','in_refund'=>'Vendor Credit','payment'=>'Payment','entry'=>'Journal Entry'];
            return $map[$move_type] ?? 'Journal Entry';
        }
        function xb_jr_voucher_class($move_type) {
            $map = ['out_invoice'=>'vtype-inv','in_invoice'=>'vtype-bill','out_refund'=>'vtype-cr','in_refund'=>'vtype-cr','payment'=>'vtype-pay'];
            return $map[$move_type] ?? 'vtype-jnl';
        }
        $grand_dr = 0; $grand_cr = 0;
        ?>
        <div style="overflow-x:auto;">
        <table class="xb-jr-tbl">
            <thead>
                <tr>
                    <th class="row-idx">#</th>
                    <th style="min-width:90px;">Date</th>
                    <th style="min-width:100px;">Voucher Type</th>
                    <th style="min-width:130px;">Voucher No</th>
                    <th style="min-width:200px;">Account</th>
                    <th style="min-width:160px;">Description</th>
                    <th class="num" style="min-width:120px;">Debit</th>
                    <th class="num" style="min-width:120px;">Credit</th>
                </tr>
            </thead>
            <tbody>
            <?php $row = 1; foreach ($ledger as $acc_key => $acc_data):
                $vt = $acc_data['lines'][0]->move_type ?? 'entry';
            ?>
                <tr class="jnl-hdr">
                    <td class="row-idx" style="background:#374151;color:#9ca3af;"></td>
                    <td colspan="7">
                        <span class="vtype <?php echo xb_jr_voucher_class($vt); ?>" style="margin-right:8px;"><?php echo xb_jr_voucher_label($vt); ?></span>
                        <a href="<?php echo admin_url('xetuu_books/journal_entry/'.($acc_data['lines'][0]->move_id ?? 0)); ?>" class="jnl-link" style="color:#d1d5db;" target="_blank">
                            <?php echo htmlspecialchars($acc_key); ?>
                        </a>
                        <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:12px;"><?php echo count($acc_data['lines']); ?> lines</span>
                    </td>
                </tr>
                <?php foreach ($acc_data['lines'] as $line): $grand_dr += (float)$line->debit; $grand_cr += (float)$line->credit; ?>
                <tr class="data-row">
                    <td class="row-idx"><?php echo $row++; ?></td>
                    <td style="white-space:nowrap;font-size:12px;"><?php echo $line->move_date; ?></td>
                    <td><span class="vtype <?php echo xb_jr_voucher_class($line->move_type ?? 'entry'); ?>"><?php echo xb_jr_voucher_label($line->move_type ?? 'entry'); ?></span></td>
                    <td>
                        <a href="<?php echo admin_url('xetuu_books/journal_entry/'.$line->move_id); ?>" class="jnl-link" target="_blank"><?php echo htmlspecialchars($line->move_name ?? ''); ?></a>
                    </td>
                    <td style="font-size:12px;color:#374151;"><?php echo htmlspecialchars($line->account_code . ' ' . $line->account_name); ?></td>
                    <td style="font-size:12px;color:#6b7280;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($line->name ?? ''); ?>"><?php echo htmlspecialchars($line->name ?? ''); ?></td>
                    <td class="num"><span class="amt amt-dr"><?php echo $line->debit  > 0 ? xb_format_money($line->debit)  : ''; ?></span></td>
                    <td class="num"><span class="amt amt-cr"><?php echo $line->credit > 0 ? xb_format_money($line->credit) : ''; ?></span></td>
                </tr>
                <?php endforeach; ?>
                <tr class="jnl-subtotal">
                    <td class="row-idx"></td>
                    <td colspan="5">Subtotal — <?php echo htmlspecialchars($acc_key); ?></td>
                    <td class="num"><span class="amt amt-dr"><?php echo xb_format_money($acc_data['total_debit']); ?></span></td>
                    <td class="num"><span class="amt amt-cr"><?php echo xb_format_money($acc_data['total_credit']); ?></span></td>
                </tr>
                <tr class="gap-row"><td colspan="8"></td></tr>
            <?php endforeach; ?>
            <tr class="jnl-grand">
                <td class="row-idx" style="background:#1e3a5f;"></td>
                <td colspan="5">GRAND TOTAL</td>
                <td class="num" style="color:#93c5fd;"><span class="amt"><?php echo xb_format_money($grand_dr); ?></span></td>
                <td class="num" style="color:#6ee7b7;"><span class="amt"><?php echo xb_format_money($grand_cr); ?></span></td>
            </tr>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
