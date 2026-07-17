<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-pl-wrap{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;color:#111827}

/* Scrollable container */
.xb-pl-scroll{
    position:relative;
    width:100%;
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:4px;
    max-height:calc(100vh - 320px);
    min-height:300px;
}

/* Table */
.xb-pl-tbl{
    border-collapse:separate;
    border-spacing:0;
    width:max-content;
    min-width:100%;
    font-size:13px;
}

/* Sticky header */
.xb-pl-tbl thead th{
    position:sticky;
    top:0;
    z-index:20;
    background:#1e3a5f;
    color:#e2e8f0;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:9px 14px;
    white-space:nowrap;
    border-bottom:2px solid #2563eb;
    border-right:1px solid #2d4a6e;
}
.xb-pl-tbl thead th.num{text-align:right}

/* Sticky left col */
.xb-pl-tbl th.sc0,.xb-pl-tbl td.sc0{
    position:sticky;left:0;z-index:10;
    background:#fff;
    border-right:2px solid #e5e7eb;
    min-width:420px;
}
.xb-pl-tbl thead th.sc0{background:#1e3a5f;z-index:30;border-right:2px solid #2563eb;color:#93c5fd}

/* Body cells */
.xb-pl-tbl tbody td{
    padding:0;
    border-bottom:1px solid #f3f4f6;
    border-right:1px solid #f8f8f8;
    vertical-align:middle;
}
.xb-pl-tbl tbody td.num{text-align:right;font-family:'Courier New',monospace;white-space:nowrap;padding:0 14px}

/* ── Row types ── */

/* Section header (INCOME / EXPENSES etc.) */
.xb-pl-tbl .row-section td{
    background:#374151!important;
    color:#fff;
    font-weight:700;
    font-size:12px;
    border-bottom:1px solid #4b5563;
    border-right:1px solid #4b5563;
    padding:0;
}
.xb-pl-tbl .row-section td.sc0{background:#374151!important;border-right:2px solid #4b5563}

/* Section toggle button */
.sec-btn{
    display:flex;
    align-items:center;
    gap:8px;
    width:100%;
    padding:8px 14px;
    background:none;
    border:none;
    color:#fff;
    font-weight:700;
    font-size:12px;
    text-align:left;
    cursor:pointer;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.sec-btn:hover{background:rgba(255,255,255,.05)}
.sec-btn .chev{font-size:11px;transition:transform .2s;color:#9ca3af}
.sec-btn.collapsed .chev{transform:rotate(-90deg)}
.sec-btn .sec-icon{font-size:13px}

/* Account data rows */
.xb-pl-tbl .row-acc td{padding:0}
.xb-pl-tbl .row-acc:hover td{background:#f0f9ff!important}
.xb-pl-tbl .row-acc:hover td.sc0{background:#e0f2fe!important}

/* Account cell inner */
.acc-cell{
    display:flex;
    align-items:center;
    gap:0;
    padding:6px 14px 6px 36px;
    white-space:nowrap;
}
.acc-link{color:#2563eb;text-decoration:none;font-size:13px}
.acc-link:hover{text-decoration:underline}
.acc-type-tag{font-size:10px;color:#9ca3af;margin-left:8px}

/* Subtotal rows */
.xb-pl-tbl .row-sub td{
    background:#f9fafb!important;
    font-weight:700;
    font-size:13px;
    border-top:1px solid #d1d5db;
    border-bottom:2px solid #d1d5db;
    padding:7px 14px;
}
.xb-pl-tbl .row-sub td.sc0{background:#f9fafb!important}

/* Special rows */
.xb-pl-tbl .row-gross td{
    background:#eff6ff!important;
    color:#1e40af;
    font-weight:700;
    font-size:13px;
    border-top:2px solid #3b82f6;
    border-bottom:2px solid #3b82f6;
    padding:8px 14px;
}
.xb-pl-tbl .row-gross td.sc0{background:#eff6ff!important}

.xb-pl-tbl .row-net-profit td{
    background:#ecfdf5!important;
    color:#065f46;
    font-weight:700;
    font-size:14px;
    padding:10px 14px;
    border-top:3px solid #10b981;
}
.xb-pl-tbl .row-net-profit td.sc0{background:#ecfdf5!important}

.xb-pl-tbl .row-net-loss td{
    background:#fef2f2!important;
    color:#991b1b;
    font-weight:700;
    font-size:14px;
    padding:10px 14px;
    border-top:3px solid #ef4444;
}
.xb-pl-tbl .row-net-loss td.sc0{background:#fef2f2!important}

.xb-pl-tbl .row-gap td{height:8px;background:#f9fafb!important;border:none!important}

/* Amounts */
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.amt-pos{color:#065f46;font-weight:600}
.amt-neg{color:#dc2626;font-weight:600}
.amt-neu{color:#111827;font-weight:600}
.zero{color:#9ca3af}

/* Row index */
.ri{color:#9ca3af;font-size:11px;text-align:right;width:36px;padding-right:4px;display:inline-block;flex-shrink:0}

/* Collapsed rows hidden */
.collapse-target{transition:none}
.collapse-target.hidden{display:none}

/* KPI cards */
.xb-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px}
.xb-kpi{border-radius:6px;padding:12px 14px;border-left:4px solid}
.xb-kpi .kpi-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#6b7280}
.xb-kpi .kpi-val{font-size:18px;font-weight:700;margin-top:3px;font-family:'Courier New',monospace}

@media print{
    .xb-pl-scroll{max-height:none!important;overflow:visible!important;border:none}
    .xb-pl-tbl th.sc0,.xb-pl-tbl td.sc0{position:relative!important}
    .no-print{display:none!important}
    .collapse-target.hidden{display:table-row!important}
}
</style>

<div class="xb-pl-wrap xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:10px 16px;">
        <div>
            <span style="font-size:16px;font-weight:700;color:#111827;"><i class="fa fa-line-chart" style="color:#10b981;margin-right:8px;"></i>Profit and Loss Statement</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">Period: <strong><?php echo $params['date_from']; ?></strong> — <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=profit_loss&format=csv&date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
            <button class="btn btn-default btn-sm" onclick="xbPlExpandAll()"><i class="fa fa-expand"></i> Expand All</button>
            <button class="btn btn-default btn-sm" onclick="xbPlCollapseAll()"><i class="fa fa-compress"></i> Collapse All</button>
        </div>
    </div>
    <div class="xb-card-body" style="padding:12px 16px;">

        <!-- Date filter -->
        <form class="form-inline mbot10 no-print" method="GET" style="padding-bottom:10px;border-bottom:1px solid #e5e7eb;">
            <div class="form-group"><label style="font-size:12px;font-weight:600;">From:&nbsp;</label>
                <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
            </div>
            <div class="form-group" style="margin-left:10px;"><label style="font-size:12px;font-weight:600;">To:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Update</button>
        </form>

        <?php
        $net_is_loss = $net_profit < 0;
        $gl_base = 'xetuu_books/reports/general_ledger?date_from='.urlencode($params['date_from']).'&date_to='.urlencode($params['date_to']);
        $sym = get_option('currency_symbol') ?: 'KES';
        ?>

        <!-- KPI cards -->
        <div class="xb-kpi-grid no-print">
            <div class="xb-kpi" style="background:#ecfdf5;border-color:#10b981;">
                <div class="kpi-lbl">Total Revenue</div>
                <div class="kpi-val" style="color:#065f46;"><?php echo xb_format_money($total_revenue); ?></div>
            </div>
            <div class="xb-kpi" style="background:#fef3c7;border-color:#f59e0b;">
                <div class="kpi-lbl">Cost of Revenue</div>
                <div class="kpi-val" style="color:#92400e;"><?php echo xb_format_money($total_cor); ?></div>
            </div>
            <div class="xb-kpi" style="background:#fef2f2;border-color:#ef4444;">
                <div class="kpi-lbl">Total Expenses</div>
                <div class="kpi-val" style="color:#991b1b;"><?php echo xb_format_money($total_expense); ?></div>
            </div>
            <div class="xb-kpi" style="background:<?php echo $net_is_loss?'#fef2f2':'#ecfdf5'; ?>;border-color:<?php echo $net_is_loss?'#ef4444':'#10b981'; ?>;">
                <div class="kpi-lbl">Net <?php echo $net_is_loss ? 'Loss' : 'Profit'; ?></div>
                <div class="kpi-val" style="color:<?php echo $net_is_loss?'#991b1b':'#065f46'; ?>;"><?php echo xb_format_money(abs($net_profit)); ?></div>
            </div>
        </div>

        <div class="xb-pl-scroll">
        <table class="xb-pl-tbl">
            <thead>
                <tr>
                    <th class="sc0">Account</th>
                    <th class="num" style="min-width:200px;"><?php echo $params['date_from'] . ' — ' . $params['date_to']; ?></th>
                </tr>
            </thead>
            <tbody>

            <?php $i = 1; ?>

            <!-- ══ INCOME ══ -->
            <tr class="row-section">
                <td class="sc0">
                    <button class="sec-btn" onclick="xbPlToggle(this,'income')" id="btn-income">
                        <i class="fa fa-chevron-down chev"></i>
                        <i class="fa fa-arrow-up sec-icon" style="color:#10b981;"></i>
                        INCOME (CREDIT)
                        <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:8px;"><?php echo count($revenue); ?> accounts</span>
                    </button>
                </td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money($total_revenue); ?></span></td>
            </tr>
            <?php foreach ($revenue as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
            ?>
            <tr class="row-acc collapse-target" data-group="income">
                <td class="sc0">
                    <div class="acc-cell">
                        <span class="ri"><?php echo $i++; ?></span>
                        <a href="<?php echo $gl_url; ?>" class="acc-link" title="View in General Ledger">
                            <?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?>
                        </a>
                        <span class="acc-type-tag"><?php echo $acc->type; ?></span>
                        <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;" title="Open General Ledger"><i class="fa fa-external-link"></i></a>
                    </div>
                </td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money(abs($acc->balance)); ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($revenue)): ?>
            <tr class="row-acc collapse-target" data-group="income">
                <td class="sc0"><div class="acc-cell"><span class="ri"></span><span style="color:#9ca3af;font-style:italic;">No income in this period</span></div></td>
                <td class="num"><span class="zero">—</span></td>
            </tr>
            <?php endif; ?>
            <tr class="row-sub collapse-target" data-group="income">
                <td class="sc0"><strong>Total Income (Credit)</strong></td>
                <td class="num"><span class="amt amt-pos"><?php echo xb_format_money($total_revenue); ?></span></td>
            </tr>
            <tr class="row-gap"><td colspan="2"></td></tr>

            <!-- ══ COST OF REVENUE ══ -->
            <tr class="row-section">
                <td class="sc0">
                    <button class="sec-btn" onclick="xbPlToggle(this,'cor')" id="btn-cor">
                        <i class="fa fa-chevron-down chev"></i>
                        <i class="fa fa-minus-circle sec-icon" style="color:#f59e0b;"></i>
                        COST OF REVENUE
                        <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:8px;"><?php echo count($cost_of_revenue); ?> accounts</span>
                    </button>
                </td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($total_cor); ?>)</span></td>
            </tr>
            <?php foreach ($cost_of_revenue as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
            ?>
            <tr class="row-acc collapse-target" data-group="cor">
                <td class="sc0">
                    <div class="acc-cell">
                        <span class="ri"><?php echo $i++; ?></span>
                        <a href="<?php echo $gl_url; ?>" class="acc-link"><?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?></a>
                        <span class="acc-type-tag"><?php echo $acc->type; ?></span>
                        <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;"><i class="fa fa-external-link"></i></a>
                    </div>
                </td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($acc->balance); ?>)</span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($cost_of_revenue)): ?>
            <tr class="row-acc collapse-target" data-group="cor">
                <td class="sc0"><div class="acc-cell"><span class="ri"></span><span style="color:#9ca3af;font-style:italic;">No cost of revenue accounts</span></div></td>
                <td class="num"><span class="zero">—</span></td>
            </tr>
            <?php endif; ?>
            <tr class="row-sub collapse-target" data-group="cor">
                <td class="sc0"><strong>Total Cost of Revenue</strong></td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($total_cor); ?>)</span></td>
            </tr>
            <tr class="row-gap"><td colspan="2"></td></tr>

            <!-- ══ GROSS PROFIT ══ -->
            <tr class="row-gross">
                <td class="sc0"><i class="fa fa-equals" style="margin-right:8px;color:#3b82f6;"></i>GROSS PROFIT</td>
                <td class="num"><span class="amt <?php echo $gross_profit < 0 ? 'amt-neg' : 'amt-pos'; ?>"><?php echo $gross_profit < 0 ? '('.xb_format_money(abs($gross_profit)).')' : xb_format_money($gross_profit); ?></span></td>
            </tr>
            <tr class="row-gap"><td colspan="2"></td></tr>

            <!-- ══ OPERATING EXPENSES ══ -->
            <tr class="row-section">
                <td class="sc0">
                    <button class="sec-btn" onclick="xbPlToggle(this,'exp')" id="btn-exp">
                        <i class="fa fa-chevron-down chev"></i>
                        <i class="fa fa-arrow-down sec-icon" style="color:#ef4444;"></i>
                        OPERATING EXPENSES (DEBIT)
                        <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:8px;"><?php echo count($expense); ?> accounts</span>
                    </button>
                </td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($total_expense); ?>)</span></td>
            </tr>
            <?php foreach ($expense as $acc):
                $gl_url = admin_url($gl_base . '&account_id=' . $acc->id);
            ?>
            <tr class="row-acc collapse-target" data-group="exp">
                <td class="sc0">
                    <div class="acc-cell">
                        <span class="ri"><?php echo $i++; ?></span>
                        <a href="<?php echo $gl_url; ?>" class="acc-link"><?php echo htmlspecialchars($acc->code . ' — ' . $acc->name); ?></a>
                        <span class="acc-type-tag"><?php echo $acc->type; ?></span>
                        <a href="<?php echo $gl_url; ?>" style="margin-left:6px;font-size:10px;color:#9ca3af;"><i class="fa fa-external-link"></i></a>
                    </div>
                </td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($acc->balance); ?>)</span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($expense)): ?>
            <tr class="row-acc collapse-target" data-group="exp">
                <td class="sc0"><div class="acc-cell"><span class="ri"></span><span style="color:#9ca3af;font-style:italic;">No expense accounts with activity</span></div></td>
                <td class="num"><span class="zero">—</span></td>
            </tr>
            <?php endif; ?>
            <tr class="row-sub collapse-target" data-group="exp">
                <td class="sc0"><strong>Total Operating Expenses</strong></td>
                <td class="num"><span class="amt amt-neg">(<?php echo xb_format_money($total_expense); ?>)</span></td>
            </tr>
            <tr class="row-gap"><td colspan="2"></td></tr>

            <!-- ══ NET PROFIT / LOSS ══ -->
            <tr class="<?php echo $net_is_loss ? 'row-net-loss' : 'row-net-profit'; ?>">
                <td class="sc0">
                    <i class="fa fa-<?php echo $net_is_loss ? 'exclamation-triangle' : 'check-circle'; ?>" style="margin-right:8px;"></i>
                    <?php echo $net_is_loss ? 'NET LOSS FOR THE PERIOD' : 'NET PROFIT FOR THE PERIOD'; ?>
                </td>
                <td class="num" style="font-size:15px;">
                    <?php if ($net_is_loss): ?>
                    <span class="amt" style="color:#991b1b;">(<?php echo xb_format_money(abs($net_profit)); ?>)</span>
                    <?php else: ?>
                    <span class="amt" style="color:#065f46;"><?php echo xb_format_money($net_profit); ?></span>
                    <?php endif; ?>
                </td>
            </tr>

            </tbody>
        </table>
        </div>

        <?php if ($net_is_loss): ?>
        <div style="margin-top:12px;padding:10px 14px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:4px;font-size:12px;color:#7f1d1d;">
            <strong><i class="fa fa-info-circle"></i> Auditor Note:</strong>
            Total expenses (<?php echo xb_format_money($total_expense); ?>) exceed total revenue (<?php echo xb_format_money($total_revenue); ?>) by <?php echo xb_format_money(abs($net_profit)); ?>.
            Click any expense account above to view its General Ledger detail.
        </div>
        <?php endif; ?>

    </div>
</div>
</div>

<script>
function xbPlToggle(btn, group) {
    var collapsed = btn.classList.contains('collapsed');
    btn.classList.toggle('collapsed', !collapsed);
    document.querySelectorAll('.collapse-target[data-group="' + group + '"]').forEach(function(row) {
        row.classList.toggle('hidden', !collapsed);
    });
}
function xbPlExpandAll() {
    document.querySelectorAll('.sec-btn').forEach(function(btn) {
        btn.classList.remove('collapsed');
    });
    document.querySelectorAll('.collapse-target').forEach(function(row) {
        row.classList.remove('hidden');
    });
}
function xbPlCollapseAll() {
    document.querySelectorAll('.sec-btn').forEach(function(btn) {
        btn.classList.add('collapsed');
    });
    document.querySelectorAll('.collapse-target').forEach(function(row) {
        row.classList.add('hidden');
    });
}
</script>
