<?php defined('BASEPATH') or exit('No direct script access allowed');

function xb_gl_voucher_label($move_type) {
    $map = [
        'out_invoice' => 'Sales Invoice',
        'in_invoice'  => 'Vendor Bill',
        'out_refund'  => 'Credit Note',
        'in_refund'   => 'Vendor Credit',
        'in_receipt'  => 'Cash Receipt',
        'out_receipt' => 'Cash Receipt',
        'entry'       => 'Journal Entry',
        'payment'     => 'Payment Entry',
    ];
    return $map[$move_type] ?? 'Journal Entry';
}
?>
<style>
/* ── GL Wrapper ── */
.xb-gl-wrap{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;color:#111827}
.xb-gl-filters{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:10px 14px;margin-bottom:10px}
.xb-gl-filters .form-group{margin-right:10px}
.xb-gl-filters label{font-size:12px;font-weight:600;margin-bottom:2px}
.xb-gl-filters .form-control{font-size:12px}

/* ── Scrollable container — fixed height, both scrollbars ── */
.xb-gl-scroll{
    position:relative;
    width:100%;
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:4px;
    max-height:calc(100vh - 320px);
    min-height:400px;
}

/* ── Table ── */
.xb-gl-tbl{
    border-collapse:separate;
    border-spacing:0;
    width:max-content;
    min-width:100%;
    font-size:12.5px;
}

/* ── Sticky column header row ── */
.xb-gl-tbl thead th{
    position:sticky;
    top:0;
    z-index:20;
    background:#1e3a5f;
    color:#e2e8f0;
    padding:8px 10px;
    font-size:10.5px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    white-space:nowrap;
    border-bottom:2px solid #2563eb;
    border-right:1px solid #2d4a6e;
}
.xb-gl-tbl thead th.num{text-align:right}

/* ── Sticky left columns (row#, date, account) ── */
.xb-gl-tbl th.sc0, .xb-gl-tbl td.sc0{position:sticky;left:0;z-index:10;background:#f9fafb}
.xb-gl-tbl thead th.sc0{background:#1e3a5f;z-index:30;color:#93c5fd}
.xb-gl-tbl th.sc1, .xb-gl-tbl td.sc1{position:sticky;left:40px;z-index:9;background:#f9fafb}
.xb-gl-tbl thead th.sc1{background:#1e3a5f;z-index:29;color:#93c5fd}
.xb-gl-tbl th.sc2, .xb-gl-tbl td.sc2{position:sticky;left:130px;z-index:8;background:#f9fafb}
.xb-gl-tbl thead th.sc2{background:#1e3a5f;z-index:28;color:#93c5fd}
/* Shadow on last sticky col */
.xb-gl-tbl td.sc2, .xb-gl-tbl thead th.sc2{border-right:2px solid #e5e7eb}

/* ── Body cells ── */
.xb-gl-tbl tbody td{
    padding:5px 10px;
    border-bottom:1px solid #f3f4f6;
    border-right:1px solid #f8f8f8;
    white-space:nowrap;
    color:#111827;
    vertical-align:middle;
}
.xb-gl-tbl tbody td.num{text-align:right;font-family:'Courier New',monospace}

/* ── Row types ── */
.xb-gl-tbl .row-data:hover td{background:#f0f9ff!important}
.xb-gl-tbl .row-data:hover td.sc0,
.xb-gl-tbl .row-data:hover td.sc1,
.xb-gl-tbl .row-data:hover td.sc2{background:#e0f2fe!important}

/* Account header row */
.xb-gl-tbl .row-acc-hdr td{
    background:#374151!important;
    color:#fff;
    font-weight:700;
    font-size:12px;
    padding:7px 10px;
    border-bottom:1px solid #4b5563;
    border-right:1px solid #4b5563;
}
.xb-gl-tbl .row-acc-hdr td.sc0,
.xb-gl-tbl .row-acc-hdr td.sc1,
.xb-gl-tbl .row-acc-hdr td.sc2{background:#374151!important}

/* Opening balance row */
.xb-gl-tbl .row-open td{background:#fffbeb!important;color:#78350f;font-style:italic;font-size:12px}
.xb-gl-tbl .row-open td.sc0,
.xb-gl-tbl .row-open td.sc1,
.xb-gl-tbl .row-open td.sc2{background:#fffbeb!important}

/* Closing balance row */
.xb-gl-tbl .row-close td{background:#ecfdf5!important;color:#065f46;font-weight:700;border-top:1px solid #6ee7b7}
.xb-gl-tbl .row-close td.sc0,
.xb-gl-tbl .row-close td.sc1,
.xb-gl-tbl .row-close td.sc2{background:#ecfdf5!important}

/* Grand total row */
.xb-gl-tbl .row-grand td{background:#1e3a5f!important;color:#fff;font-weight:700;font-size:13px;padding:9px 10px;border-top:2px solid #2563eb!important}
.xb-gl-tbl .row-grand td.sc0,
.xb-gl-tbl .row-grand td.sc1,
.xb-gl-tbl .row-grand td.sc2{background:#1e3a5f!important}

/* Gap row */
.xb-gl-tbl .row-gap td{height:4px;background:#f3f4f6!important;border:none!important}

/* Row index column */
.xb-gl-tbl .ri{color:#9ca3af;font-size:10px;text-align:right;width:40px;padding-right:6px}

/* Amounts */
.dr{color:#1d4ed8;font-weight:600}
.cr{color:#047857;font-weight:600}
.bal{color:#111827;font-weight:600}
.bal-neg{color:#dc2626;font-weight:600}
.zero{color:#9ca3af}

/* Voucher type badge */
.vt{font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;display:inline-block;white-space:nowrap}
.vt-inv{background:#dbeafe;color:#1e40af}
.vt-bill{background:#fef3c7;color:#92400e}
.vt-jnl{background:#e0e7ff;color:#3730a3}
.vt-pay{background:#d1fae5;color:#065f46}
.vt-cr{background:#fce7f3;color:#9d174d}

/* Links */
.jnl-link{color:#7c3aed;text-decoration:none;font-weight:500}
.jnl-link:hover{text-decoration:underline}
.acc-link{color:#2563eb;text-decoration:none;font-weight:600}
.acc-link:hover{text-decoration:underline}

/* ── Stats bar ── */
.xb-gl-stats{display:flex;gap:12px;margin-bottom:10px;flex-wrap:wrap}
.xb-gl-stat{background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:8px 14px;min-width:130px}
.xb-gl-stat .lbl{font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.xb-gl-stat .val{font-size:15px;font-weight:700;color:#111827;font-family:'Courier New',monospace;margin-top:2px}

/* Column resize hint */
.xb-gl-hint{font-size:10px;color:#9ca3af;margin-bottom:6px}

@media print{
    .xb-gl-filters,.xb-gl-stats,.no-print{display:none!important}
    .xb-gl-scroll{max-height:none!important;overflow:visible!important;border:none}
    .xb-gl-tbl th.sc0,.xb-gl-tbl td.sc0,
    .xb-gl-tbl th.sc1,.xb-gl-tbl td.sc1,
    .xb-gl-tbl th.sc2,.xb-gl-tbl td.sc2{position:relative!important}
}
</style>

<div class="xb-gl-wrap xb-rpt">
<div class="xb-card">
    <!-- Header -->
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <div>
            <span style="font-size:16px;font-weight:700;color:#111827;"><i class="fa fa-list-alt" style="color:#2563eb;margin-right:8px;"></i>General Ledger</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=general_ledger&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>

    <div class="xb-card-body" style="padding:12px 16px;">

        <!-- Filters -->
        <div class="xb-gl-filters no-print">
            <form class="form-inline" method="GET">
                <div class="form-group">
                    <label>From</label>
                    <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
                </div>
                <div class="form-group">
                    <label>To</label>
                    <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
                </div>
                <?php
                $all_accounts = $this->xb_config->get_accounts();
                $filter_acc   = (int)($params['account_id'] ?? 0);
                ?>
                <div class="form-group">
                    <label>Account</label>
                    <select name="account_id" class="form-control input-sm" style="min-width:200px;">
                        <option value="">— All Accounts —</option>
                        <?php foreach ($all_accounts as $a): ?>
                            <option value="<?php echo $a->id; ?>" <?php echo $filter_acc == $a->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a->code . ' ' . $a->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply</button>
                <?php if ($filter_acc): ?>
                <a href="<?php echo admin_url('xetuu_books/reports/general_ledger?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($ledger)): ?>
            <div class="alert alert-info"><i class="fa fa-info-circle"></i> No posted journal entries found for the selected period.</div>
        <?php else: ?>

        <?php
        /* ── Pre-compute grand totals ── */
        $grand_dr = 0; $grand_cr = 0; $row_num = 0; $total_entries = 0;
        foreach ($ledger as $acc_data) { $total_entries += count($acc_data['lines']); $grand_dr += $acc_data['total_debit']; $grand_cr += $acc_data['total_credit']; }
        ?>

        <!-- Stats bar -->
        <div class="xb-gl-stats no-print">
            <div class="xb-gl-stat"><div class="lbl">Accounts</div><div class="val"><?php echo count($ledger); ?></div></div>
            <div class="xb-gl-stat"><div class="lbl">Entries</div><div class="val"><?php echo $total_entries; ?></div></div>
            <div class="xb-gl-stat"><div class="lbl">Total Debit</div><div class="val dr"><?php echo xb_format_money($grand_dr); ?></div></div>
            <div class="xb-gl-stat"><div class="lbl">Total Credit</div><div class="val cr"><?php echo xb_format_money($grand_cr); ?></div></div>
            <div class="xb-gl-stat"><div class="lbl">Net</div><div class="val <?php echo ($grand_dr-$grand_cr)<0?'bal-neg':'bal'; ?>"><?php echo xb_format_money($grand_dr - $grand_cr); ?></div></div>
        </div>

        <div class="xb-gl-hint no-print"><i class="fa fa-arrows-h"></i> Scroll horizontally to see more columns &nbsp;|&nbsp; <i class="fa fa-arrows-v"></i> Scroll vertically — header stays fixed &nbsp;|&nbsp; First 3 columns (# / Date / Account) are pinned</div>

        <!-- Scrollable table -->
        <div class="xb-gl-scroll">
        <table class="xb-gl-tbl">
            <thead>
                <tr>
                    <th class="sc0 ri">#</th>
                    <th class="sc1" style="min-width:90px;">Posting Date</th>
                    <th class="sc2" style="min-width:220px;">Account</th>
                    <th style="min-width:180px;">Description / Narration</th>
                    <th style="min-width:110px;">Journal</th>
                    <th style="min-width:130px;" class="num">Debit (<?php echo get_option('currency_symbol') ?: 'KES'; ?>)</th>
                    <th style="min-width:130px;" class="num">Credit (<?php echo get_option('currency_symbol') ?: 'KES'; ?>)</th>
                    <th style="min-width:140px;" class="num">Balance (<?php echo get_option('currency_symbol') ?: 'KES'; ?>)</th>
                    <th style="min-width:120px;">Voucher Type</th>
                    <th style="min-width:150px;">Voucher No</th>
                    <th style="min-width:140px;">Party / Partner</th>
                    <th style="min-width:120px;">Reference</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ledger as $acc_key => $acc_data):
                $ob       = (float)$acc_data['opening_balance'];
                $acc_id   = $acc_data['account_id'];
                $running  = $ob;
                $gl_url   = admin_url('xetuu_books/reports/general_ledger?account_id='.$acc_id.'&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to']));
            ?>
                <!-- Account header -->
                <tr class="row-acc-hdr">
                    <td class="sc0 ri"></td>
                    <td class="sc1" colspan="2" style="position:sticky;left:40px;z-index:8;background:#374151;">
                        <a href="<?php echo $gl_url; ?>" class="acc-link" style="color:#93c5fd;">
                            <?php echo htmlspecialchars($acc_data['account_code'] . ' — ' . $acc_data['account_name']); ?>
                        </a>
                        <span style="font-size:10px;color:#9ca3af;font-weight:400;margin-left:12px;"><?php echo count($acc_data['lines']); ?> entries</span>
                    </td>
                    <td colspan="9" style="background:#374151;border-right:1px solid #4b5563;"></td>
                </tr>

                <!-- Opening balance -->
                <tr class="row-open">
                    <td class="sc0 ri"></td>
                    <td class="sc1"><?php echo $params['date_from']; ?></td>
                    <td class="sc2" style="color:#78350f;font-weight:600;">Opening Balance</td>
                    <td colspan="7" style="color:#78350f;font-style:italic;font-size:11px;">Brought forward from prior period</td>
                    <td class="num" colspan="1" style="color:#78350f;font-style:normal;font-weight:700;"><?php echo xb_format_money($ob); ?></td>
                </tr>

                <!-- Transaction lines -->
                <?php foreach ($acc_data['lines'] as $line):
                    $row_num++;
                    $running  += ((float)$line->debit - (float)$line->credit);
                    $vtype    = $line->move_type ?? 'entry';
                    $vl       = xb_gl_voucher_label($vtype);
                    $vc       = match($vtype) {
                        'out_invoice'            => 'vt-inv',
                        'in_invoice'             => 'vt-bill',
                        'out_refund','in_refund' => 'vt-cr',
                        'payment'                => 'vt-pay',
                        default                  => 'vt-jnl',
                    };
                ?>
                <tr class="row-data">
                    <td class="sc0 ri"><?php echo $row_num; ?></td>
                    <td class="sc1" style="font-size:12px;color:#374151;"><?php echo $line->move_date; ?></td>
                    <td class="sc2" style="font-size:11px;max-width:220px;overflow:hidden;text-overflow:ellipsis;">
                        <a href="<?php echo admin_url('xetuu_books/reports/general_ledger?account_id='.$line->account_id.'&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="acc-link" style="font-size:11px;" title="Filter GL by this account">
                            <?php echo htmlspecialchars($line->account_code . ' ' . $line->account_name); ?>
                        </a>
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($line->name ?? ''); ?>">
                        <?php echo htmlspecialchars($line->name ?? ''); ?>
                    </td>
                    <td style="font-size:11px;color:#6b7280;"><?php echo htmlspecialchars($line->journal_name ?? ''); ?></td>
                    <td class="num">
                        <span class="<?php echo $line->debit  > 0 ? 'dr'   : 'zero'; ?>"><?php echo $line->debit  > 0 ? xb_format_money($line->debit)  : '—'; ?></span>
                        <?php if ($line->debit > 0 && !empty($line->currency_id) && $line->currency_id != 1 && !empty($line->amount_currency) && $line->amount_currency > 0): ?>
                        <br><span style="font-size:9px;color:#9ca3af;"><?php echo xb_get_currency_symbol($line->currency_id) . ' ' . number_format($line->amount_currency, 2); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="num">
                        <span class="<?php echo $line->credit > 0 ? 'cr'   : 'zero'; ?>"><?php echo $line->credit > 0 ? xb_format_money($line->credit) : '—'; ?></span>
                        <?php if ($line->credit > 0 && !empty($line->currency_id) && $line->currency_id != 1 && !empty($line->amount_currency) && $line->amount_currency < 0): ?>
                        <br><span style="font-size:9px;color:#9ca3af;"><?php echo xb_get_currency_symbol($line->currency_id) . ' ' . number_format(abs($line->amount_currency), 2); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="num"><span class="<?php echo $running < 0 ? 'bal-neg' : 'bal'; ?>"><?php echo xb_format_money($running); ?></span></td>
                    <td><span class="vt <?php echo $vc; ?>"><?php echo $vl; ?></span></td>
                    <td>
                        <?php if (!empty($line->move_name)): ?>
                        <a href="<?php echo admin_url('xetuu_books/journal_entry/'.$line->move_id); ?>" class="jnl-link" target="_blank">
                            <?php echo htmlspecialchars($line->move_name); ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:11px;color:#6b7280;max-width:140px;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($line->partner_name ?? ''); ?>">
                        <?php echo htmlspecialchars($line->partner_name ?? ''); ?>
                    </td>
                    <td style="font-size:11px;color:#6b7280;max-width:120px;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($line->move_ref ?? ''); ?>">
                        <?php echo htmlspecialchars($line->move_ref ?? ''); ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <!-- Closing balance -->
                <tr class="row-close">
                    <td class="sc0 ri"></td>
                    <td class="sc1" style="font-size:12px;"><?php echo $params['date_to']; ?></td>
                    <td class="sc2" style="font-weight:700;">Closing Balance</td>
                    <td style="font-size:11px;color:#065f46;"><?php echo htmlspecialchars($acc_data['account_code']); ?> — <?php echo htmlspecialchars($acc_data['account_name']); ?></td>
                    <td class="num"><span class="dr"><?php echo xb_format_money($acc_data['total_debit']); ?></span></td>
                    <td class="num"><span class="cr"><?php echo xb_format_money($acc_data['total_credit']); ?></span></td>
                    <td class="num"><span class="<?php echo $running < 0 ? 'bal-neg' : 'bal'; ?>"><?php echo xb_format_money($running); ?></span></td>
                    <td colspan="4" style="font-size:11px;color:#6b7280;"></td>
                </tr>
                <tr class="row-gap"><td colspan="12"></td></tr>

            <?php endforeach; ?>

            <!-- Grand total -->
            <tr class="row-grand">
                <td class="sc0 ri"></td>
                <td class="sc1"></td>
                <td class="sc2">GRAND TOTAL</td>
                <td>
                    <span style="font-size:10px;font-weight:400;color:#93c5fd;"><?php echo $row_num; ?> entries across <?php echo count($ledger); ?> accounts</span>
                </td>
                <td class="num" style="color:#93c5fd;"><?php echo xb_format_money($grand_dr); ?></td>
                <td class="num" style="color:#6ee7b7;"><?php echo xb_format_money($grand_cr); ?></td>
                <td class="num" style="color:<?php echo ($grand_dr-$grand_cr)<0?'#fca5a5':'#fde68a'; ?>;"><?php echo xb_format_money($grand_dr - $grand_cr); ?></td>
                <td colspan="4"></td>
            </tr>
            </tbody>
        </table>
        </div><!-- /.xb-gl-scroll -->

        <?php endif; ?>
    </div>
</div>
</div>
