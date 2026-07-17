<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-ar{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
.xb-ar-tbl{width:100%;border-collapse:collapse;font-size:13px}
.xb-ar-tbl th{background:#f3f4f6;padding:7px 14px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-top:2px solid #d1d5db;border-bottom:1px solid #d1d5db;white-space:nowrap;text-align:right}
.xb-ar-tbl th:first-child,.xb-ar-tbl th.left{text-align:left}
.xb-ar-tbl td{padding:6px 14px;border-bottom:1px solid #f3f4f6;text-align:right}
.xb-ar-tbl td:first-child,.xb-ar-tbl td.left{text-align:left}
.xb-ar-tbl .partner-row{cursor:pointer;font-weight:600}
.xb-ar-tbl .partner-row:hover td{background:#f0f9ff}
.xb-ar-tbl .partner-row td{color:#111827}
.xb-ar-tbl .detail-row{display:none}
.xb-ar-tbl .detail-row.open{display:table-row}
.xb-ar-tbl .detail-row td{padding:0;border:none}
.detail-inner{padding:10px 24px;background:#f9fafb;border-bottom:1px solid #e5e7eb}
.detail-tbl{width:100%;font-size:12px;border-collapse:collapse}
.detail-tbl th{background:#e5e7eb;padding:5px 10px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase}
.detail-tbl th.r,.detail-tbl td.r{text-align:right}
.detail-tbl td{padding:5px 10px;border-bottom:1px solid #f3f4f6}
.total-row td{background:#1e3a5f;color:#fff;font-weight:700;font-size:13px;padding:10px 14px}
.b-cur{color:#16a34a}.b-30{color:#ca8a04}.b-60{color:#d97706}.b-90{color:#ea580c}.b-ov{color:#dc2626;font-weight:700}
.amt{font-family:'Courier New',monospace;white-space:nowrap}
.jnl-link{color:#7c3aed;text-decoration:none;font-weight:500}.jnl-link:hover{text-decoration:underline}
@media print{.no-print{display:none!important}}
</style>

<div class="xb-ar xb-rpt">
<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center" style="padding:12px 16px;">
        <div>
            <span style="font-size:17px;font-weight:700;color:#111827;"><i class="fa fa-clock-o" style="color:#16a34a;margin-right:8px;"></i>Aged Receivable</span>
            <span style="font-size:11px;color:#6b7280;margin-left:10px;">As of: <strong><?php echo $params['date_to']; ?></strong></span>
        </div>
        <div class="no-print">
            <a href="<?php echo admin_url('xetuu_books/report_export?report=aged_receivable&format=csv&date_to='.urlencode($params['date_to'])); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export CSV</a>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body" style="padding:14px 16px;">

        <form class="form-inline mbot15 no-print" method="GET" style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <div class="form-group"><label style="font-size:12px;font-weight:600;">As of Date:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fa fa-refresh"></i> Update</button>
        </form>

        <?php if (empty($partners)): ?>
            <div style="padding:20px;text-align:center;color:#16a34a;font-weight:600;">
                <i class="fa fa-check-circle" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                No outstanding receivables as of <?php echo $params['date_to']; ?>.
            </div>
        <?php else: ?>

        <!-- Summary cards -->
        <div class="row mbot15 no-print">
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#f0fdf4;border-radius:6px;border-top:3px solid #16a34a;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Current</div><div style="font-size:15px;font-weight:700;color:#16a34a;"><?php echo xb_format_money($totals['current']); ?></div></div></div>
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#fefce8;border-radius:6px;border-top:3px solid #ca8a04;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">1–30 Days</div><div style="font-size:15px;font-weight:700;color:#ca8a04;"><?php echo xb_format_money($totals['1_30']); ?></div></div></div>
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#fffbeb;border-radius:6px;border-top:3px solid #d97706;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">31–60 Days</div><div style="font-size:15px;font-weight:700;color:#d97706;"><?php echo xb_format_money($totals['31_60']); ?></div></div></div>
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#fff7ed;border-radius:6px;border-top:3px solid #ea580c;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">61–90 Days</div><div style="font-size:15px;font-weight:700;color:#ea580c;"><?php echo xb_format_money($totals['61_90']); ?></div></div></div>
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#fef2f2;border-radius:6px;border-top:3px solid #dc2626;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">91–120 / 120+</div><div style="font-size:15px;font-weight:700;color:#dc2626;"><?php echo xb_format_money($totals['91_120'] + $totals['over_120']); ?></div></div></div>
            <div class="col-sm-2"><div style="text-align:center;padding:10px;background:#f0f9ff;border-radius:6px;border-top:3px solid #2563eb;"><div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Total Outstanding</div><div style="font-size:15px;font-weight:700;color:#2563eb;"><?php echo xb_format_money($totals['total']); ?></div></div></div>
        </div>

        <div style="overflow-x:auto;">
        <table class="xb-ar-tbl">
            <thead>
                <tr>
                    <th class="left" style="min-width:180px;">Customer</th>
                    <th class="b-cur" style="min-width:110px;">Current</th>
                    <th class="b-30" style="min-width:110px;">1–30 Days</th>
                    <th class="b-60" style="min-width:110px;">31–60 Days</th>
                    <th class="b-90" style="min-width:110px;">61–90 Days</th>
                    <th class="b-ov" style="min-width:110px;">91–120 Days</th>
                    <th class="b-ov" style="min-width:110px;">120+ Days</th>
                    <th style="min-width:120px;color:#2563eb;">Total Balance</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($partners as $p): ?>
                <tr class="partner-row" onclick="xbToggle(this)">
                    <td class="left">
                        <i class="fa fa-chevron-right xb-chev" style="font-size:10px;margin-right:6px;color:#9ca3af;"></i>
                        <?php echo htmlspecialchars($p['partner_name']); ?>
                    </td>
                    <td><span class="amt b-cur"><?php echo $p['current']   ? xb_format_money($p['current'])   : '—'; ?></span></td>
                    <td><span class="amt b-30" ><?php echo $p['1_30']     ? xb_format_money($p['1_30'])     : '—'; ?></span></td>
                    <td><span class="amt b-60" ><?php echo $p['31_60']    ? xb_format_money($p['31_60'])    : '—'; ?></span></td>
                    <td><span class="amt b-90" ><?php echo $p['61_90']    ? xb_format_money($p['61_90'])    : '—'; ?></span></td>
                    <td><span class="amt b-ov" ><?php echo $p['91_120']   ? xb_format_money($p['91_120'])   : '—'; ?></span></td>
                    <td><span class="amt b-ov" ><?php echo $p['over_120'] ? xb_format_money($p['over_120']) : '—'; ?></span></td>
                    <td><span class="amt" style="color:#2563eb;font-weight:700;"><?php echo xb_format_money($p['total']); ?></span></td>
                </tr>
                <tr class="detail-row">
                    <td colspan="8">
                        <div class="detail-inner">
                            <table class="detail-tbl">
                                <thead><tr><th class="left">Invoice #</th><th>Due Date</th><th class="r">Open Balance</th></tr></thead>
                                <tbody>
                                <?php foreach ($p['lines'] as $line): ?>
                                <tr>
                                    <td class="left">
                                        <a href="<?php echo admin_url('xetuu_books/journal_entry/'.$line->move_id); ?>" class="jnl-link" target="_blank"><?php echo htmlspecialchars($line->move_name); ?></a>
                                    </td>
                                    <td style="color:#6b7280;"><?php echo $line->due_date ? _d($line->due_date) : '—'; ?></td>
                                    <td class="r"><span class="amt" style="color:#dc2626;font-weight:600;"><?php echo xb_format_money($line->balance); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td class="left">GRAND TOTAL</td>
                <td><span class="amt"><?php echo xb_format_money($totals['current']);  ?></span></td>
                <td><span class="amt"><?php echo xb_format_money($totals['1_30']);    ?></span></td>
                <td><span class="amt"><?php echo xb_format_money($totals['31_60']);   ?></span></td>
                <td><span class="amt"><?php echo xb_format_money($totals['61_90']);   ?></span></td>
                <td><span class="amt"><?php echo xb_format_money($totals['91_120']);  ?></span></td>
                <td><span class="amt"><?php echo xb_format_money($totals['over_120']);?></span></td>
                <td><span class="amt" style="color:#93c5fd;"><?php echo xb_format_money($totals['total']); ?></span></td>
            </tr>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<script>
function xbToggle(row) {
    var dr = row.nextElementSibling;
    var ic = row.querySelector('.xb-chev');
    if (dr.classList.contains('open')) {
        dr.classList.remove('open');
        ic.classList.remove('fa-chevron-down');
        ic.classList.add('fa-chevron-right');
    } else {
        dr.classList.add('open');
        ic.classList.remove('fa-chevron-right');
        ic.classList.add('fa-chevron-down');
    }
}
</script>
