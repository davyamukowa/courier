<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-tb-wrap{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;color:#111827}

/* Scrollable container */
.xb-tb-scroll{
    position:relative;
    width:100%;
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:4px;
    max-height:calc(100vh - 320px);
    min-height:400px;
}

/* Table */
.xb-tb-tbl{
    border-collapse:separate;
    border-spacing:0;
    width:max-content;
    min-width:100%;
    font-size:12.5px;
}

/* Sticky column headers */
.xb-tb-tbl thead tr:first-child th{
    position:sticky;
    top:0;
    z-index:20;
    background:#1e3a5f;
    color:#e2e8f0;
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:8px 10px;
    white-space:nowrap;
    border-bottom:1px solid #2d4a6e;
    border-right:1px solid #2d4a6e;
    text-align:center;
}
/* Sub-header row (Debit / Credit labels) */
.xb-tb-tbl thead tr.sub-hdr th{
    position:sticky;
    top:34px;
    z-index:20;
    background:#2d4a6e;
    color:#93c5fd;
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:5px 10px;
    text-align:right;
    white-space:nowrap;
    border-bottom:2px solid #2563eb;
    border-right:1px solid #2d4a6e;
}
.xb-tb-tbl thead tr.sub-hdr th.left{text-align:left}

/* Sticky left columns */
.xb-tb-tbl th.sc0,.xb-tb-tbl td.sc0{position:sticky;left:0;z-index:10;background:#f9fafb;border-right:1px solid #e5e7eb}
.xb-tb-tbl thead th.sc0{background:#1e3a5f;z-index:30;color:#93c5fd;border-right:1px solid #2d4a6e}
.xb-tb-tbl thead tr.sub-hdr th.sc0{background:#2d4a6e;z-index:30}

.xb-tb-tbl th.sc1,.xb-tb-tbl td.sc1{position:sticky;left:40px;z-index:9;background:#f9fafb}
.xb-tb-tbl thead th.sc1{background:#1e3a5f;z-index:29;color:#93c5fd}
.xb-tb-tbl thead tr.sub-hdr th.sc1{background:#2d4a6e;z-index:29}

.xb-tb-tbl th.sc2,.xb-tb-tbl td.sc2{position:sticky;left:110px;z-index:8;background:#f9fafb;border-right:2px solid #d1d5db}
.xb-tb-tbl thead th.sc2{background:#1e3a5f;z-index:28;color:#93c5fd;border-right:2px solid #2563eb}
.xb-tb-tbl thead tr.sub-hdr th.sc2{background:#2d4a6e;z-index:28;border-right:2px solid #2563eb}

/* Body cells */
.xb-tb-tbl tbody td{
    padding:5px 10px;
    border-bottom:1px solid #f3f4f6;
    border-right:1px solid #f8f8f8;
    vertical-align:middle;
    color:#111827;
}
.xb-tb-tbl tbody td.r{text-align:right;font-family:'Courier New',monospace;white-space:nowrap}
.xb-tb-tbl tbody td.ri{color:#9ca3af;font-size:10px;text-align:right;padding-right:6px}

/* Row types */
.xb-tb-tbl .row-data:hover td{background:#f0f9ff!important}
.xb-tb-tbl .row-data:hover td.sc0,.xb-tb-tbl .row-data:hover td.sc1,.xb-tb-tbl .row-data:hover td.sc2{background:#e0f2fe!important}

/* Type group header */
.xb-tb-tbl .row-type-hdr td{background:#374151!important;color:#fff;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:6px 10px;border-bottom:1px solid #4b5563;border-right:1px solid #4b5563}
.xb-tb-tbl .row-type-hdr td.sc0,.xb-tb-tbl .row-type-hdr td.sc1,.xb-tb-tbl .row-type-hdr td.sc2{background:#374151!important}

/* Total row */
.xb-tb-tbl .row-total td{background:#1e3a5f!important;color:#fff;font-weight:700;font-size:13px;padding:9px 10px;border-top:2px solid #2563eb!important}
.xb-tb-tbl .row-total td.sc0,.xb-tb-tbl .row-total td.sc1,.xb-tb-tbl .row-total td.sc2{background:#1e3a5f!important}

/* Column section dividers */
.xb-tb-tbl td.sect-div,.xb-tb-tbl th.sect-div{border-left:2px solid #d1d5db!important}

/* Amounts */
.dr{color:#1d4ed8;font-weight:600}.cr{color:#047857;font-weight:600}
.zero{color:#d1d5db}

/* Account link */
.acc-link{color:#2563eb;text-decoration:none;font-weight:500}
.acc-link:hover{text-decoration:underline}

/* Hint */
.xb-gl-hint{font-size:10px;color:#9ca3af;margin-bottom:6px}

@media print{
    .xb-tb-scroll{max-height:none!important;overflow:visible!important;border:none}
    .xb-tb-tbl th.sc0,.xb-tb-tbl td.sc0,
    .xb-tb-tbl th.sc1,.xb-tb-tbl td.sc1,
    .xb-tb-tbl th.sc2,.xb-tb-tbl td.sc2{position:relative!important}
    .no-print{display:none!important}
}
</style>

<div class="xb-tb-wrap xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <div>
            <span style="font-size:16px;font-weight:700;color:#111827;"><i class="fa fa-table" style="color:#7c3aed;margin-right:8px;"></i>Trial Balance</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=trial_balance&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body" style="padding:12px 16px;">

        <form class="form-inline mbot10 no-print" method="GET" style="padding-bottom:10px;border-bottom:1px solid #e5e7eb;">
            <div class="form-group"><label style="font-size:12px;font-weight:600;">From:&nbsp;</label>
                <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
            </div>
            <div class="form-group" style="margin-left:10px;"><label style="font-size:12px;font-weight:600;">To:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Update</button>
        </form>

        <!-- Balance check banner -->
        <div style="margin-bottom:10px;padding:7px 14px;background:<?php echo $is_balanced ? '#ecfdf5' : '#fef2f2'; ?>;border-left:4px solid <?php echo $is_balanced ? '#10b981' : '#ef4444'; ?>;border-radius:4px;font-size:12px;font-weight:600;color:<?php echo $is_balanced ? '#065f46' : '#991b1b'; ?>;">
            <?php if ($is_balanced): ?>
            <i class="fa fa-check-circle"></i> Trial Balance is BALANCED — Total Debits = Total Credits = <?php echo xb_format_money($total_debit); ?>
            <?php else: ?>
            <i class="fa fa-exclamation-triangle"></i> OUT OF BALANCE! Variance: <?php echo xb_format_money($variance); ?>
            <?php endif; ?>
        </div>

        <div class="xb-gl-hint no-print"><i class="fa fa-arrows-h"></i> Scroll horizontally for Initial Balance / Period / End Balance &nbsp;|&nbsp; <i class="fa fa-arrows-v"></i> Header rows stay fixed &nbsp;|&nbsp; # / Code / Account columns are pinned</div>

        <?php
        // Group accounts by type for display
        $type_order = ['Asset','Current Asset','Fixed Asset','Bank','Cash','Receivable',
                       'Liability','Current Liability','Long-term Liability','Payable','Tax',
                       'Equity','Revenue','Other Income','Cost of Revenue','Expense','Other Expense'];
        $groups = [];
        foreach ($accounts as $acc) { $groups[$acc->type][] = $acc; }
        uksort($groups, function($a, $b) use ($type_order) {
            $ia = array_search($a, $type_order); $ib = array_search($b, $type_order);
            $ia = ($ia === false) ? 99 : $ia; $ib = ($ib === false) ? 99 : $ib;
            return $ia <=> $ib;
        });
        $gl_base = 'xetuu_books/reports/general_ledger?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to']);
        $i = 1;
        ?>

        <div class="xb-tb-scroll">
        <table class="xb-tb-tbl">
            <thead>
                <tr>
                    <th class="sc0" style="min-width:40px;" rowspan="2">#</th>
                    <th class="sc1" style="min-width:70px;" rowspan="2">Code</th>
                    <th class="sc2" style="min-width:220px;" rowspan="2">Account Name</th>
                    <th colspan="2" style="min-width:260px;border-left:2px solid #2563eb;">Initial Balance</th>
                    <th colspan="2" style="min-width:260px;border-left:2px solid #2563eb;">Period (<?php echo $params['date_from']; ?> — <?php echo $params['date_to']; ?>)</th>
                    <th colspan="2" style="min-width:260px;border-left:2px solid #2563eb;">End Balance</th>
                </tr>
                <tr class="sub-hdr">
                    <th class="sc0" style="display:none;"></th>
                    <th class="sc1" style="display:none;"></th>
                    <th class="sc2" style="display:none;"></th>
                    <th class="sect-div" style="min-width:130px;">Debit</th>
                    <th style="min-width:130px;">Credit</th>
                    <th class="sect-div" style="min-width:130px;">Debit</th>
                    <th style="min-width:130px;">Credit</th>
                    <th class="sect-div" style="min-width:130px;">Debit</th>
                    <th style="min-width:130px;">Credit</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($groups as $type => $type_accounts): ?>
            <tr class="row-type-hdr">
                <td class="sc0 ri"></td>
                <td class="sc1"></td>
                <td class="sc2"><?php echo strtoupper(htmlspecialchars($type)); ?></td>
                <td colspan="6"></td>
            </tr>
            <?php foreach ($type_accounts as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
            ?>
            <tr class="row-data">
                <td class="sc0 ri"><?php echo $i++; ?></td>
                <td class="sc1" style="font-family:'Courier New',monospace;font-size:11px;color:#6b7280;"><?php echo htmlspecialchars($acc->code); ?></td>
                <td class="sc2">
                    <a href="<?php echo $gl_url; ?>" class="acc-link"><?php echo htmlspecialchars($acc->name); ?></a>
                    <a href="<?php echo $gl_url; ?>" style="margin-left:4px;font-size:10px;color:#9ca3af;"><i class="fa fa-external-link"></i></a>
                </td>
                <!-- Initial Balance -->
                <td class="r sect-div"><span class="<?php echo $acc->ob_debit > 0 ? 'dr' : 'zero'; ?>"><?php echo $acc->ob_debit > 0 ? xb_format_money($acc->ob_debit) : '—'; ?></span></td>
                <td class="r"><span class="<?php echo $acc->ob_credit > 0 ? 'cr' : 'zero'; ?>"><?php echo $acc->ob_credit > 0 ? xb_format_money($acc->ob_credit) : '—'; ?></span></td>
                <!-- Period -->
                <td class="r sect-div"><span class="<?php echo $acc->per_debit > 0 ? 'dr' : 'zero'; ?>"><?php echo $acc->per_debit > 0 ? xb_format_money($acc->per_debit) : '—'; ?></span></td>
                <td class="r"><span class="<?php echo $acc->per_credit > 0 ? 'cr' : 'zero'; ?>"><?php echo $acc->per_credit > 0 ? xb_format_money($acc->per_credit) : '—'; ?></span></td>
                <!-- End Balance -->
                <td class="r sect-div"><span class="<?php echo $acc->end_debit > 0 ? 'dr' : 'zero'; ?>"><?php echo $acc->end_debit > 0 ? xb_format_money($acc->end_debit) : '—'; ?></span></td>
                <td class="r"><span class="<?php echo $acc->end_credit > 0 ? 'cr' : 'zero'; ?>"><?php echo $acc->end_credit > 0 ? xb_format_money($acc->end_credit) : '—'; ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- Grand Total -->
            <tr class="row-total">
                <td class="sc0 ri"></td>
                <td class="sc1"></td>
                <td class="sc2">TOTAL</td>
                <td class="r sect-div" style="color:#93c5fd;"><?php echo xb_format_money($total_ob_debit); ?></td>
                <td class="r" style="color:#6ee7b7;"><?php echo xb_format_money($total_ob_credit); ?></td>
                <td class="r sect-div" style="color:#93c5fd;"><?php echo xb_format_money($total_per_debit); ?></td>
                <td class="r" style="color:#6ee7b7;"><?php echo xb_format_money($total_per_credit); ?></td>
                <td class="r sect-div" style="color:#93c5fd;"><?php echo xb_format_money($total_end_debit); ?></td>
                <td class="r" style="color:#6ee7b7;"><?php echo xb_format_money($total_end_credit); ?></td>
            </tr>
            </tbody>
        </table>
        </div><!-- /.xb-tb-scroll -->

    </div>
</div>
</div>
