<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * The manifest table — shared by the live preview and the generated document.
 * Needs: $rows (manifest row arrays), $totals.
 * Columns: Waybill/BL · Shipper · Consignee · Origin · Destination · Type · Amount · Weight/CBM/KG · Status
 */
$cm_pill = function ($status) {
    $s = strtolower((string) $status);
    if (preg_match('/deliver|complete|received|cleared/', $s)) { return 'cm-pill--ok'; }
    if (preg_match('/cancel|reject|fail|return|lost/', $s)) { return 'cm-pill--bad'; }
    if (preg_match('/transit|dispatch|out for|local delivery|picked|arrived|airport|flight|loaded|shipped/', $s)) { return 'cm-pill--move'; }
    if (preg_match('/pending|created|hold|await|process/', $s)) { return 'cm-pill--warn'; }
    return '';
};
?>
<div class="cm-table-wrap cm-sheet">
<table class="cm-table">
    <thead>
    <tr>
        <th style="width:42px;">#</th>
        <th>Waybill / BL</th>
        <th>Shipper</th>
        <th>Consignee</th>
        <th>Origin Country</th>
        <th>Destination</th>
        <th>Shipment Type</th>
        <th class="cm-num">Amount</th>
        <th class="cm-num">Weight / CBM / KG</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
        <tr><td></td><td colspan="9" class="cm-empty-row">No shipments match these filters &mdash; widen the date range or clear a filter.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><span class="cm-wb"><?php echo htmlspecialchars($r['waybill']); ?></span>
                <span class="cm-sub"><?php echo htmlspecialchars(date('d M Y', strtotime($r['date']))); ?></span></td>
            <td><?php echo htmlspecialchars($r['shipper']); ?></td>
            <td><?php echo htmlspecialchars($r['consignee']); ?></td>
            <td><?php echo htmlspecialchars($r['origin']); ?></td>
            <td><?php echo htmlspecialchars($r['dest']); ?>
                <?php if (!empty($r['dest_town'])): ?><span class="cm-sub"><?php echo htmlspecialchars($r['dest_town']); ?></span><?php endif; ?></td>
            <td><span class="cm-type <?php echo $r['type_key'] === 'local' ? 'cm-type--local' : ''; ?>"><?php echo htmlspecialchars($r['type']); ?></span></td>
            <td class="cm-num"><?php echo $r['currency'] ? '<span class="cm-sub" style="display:inline;margin-right:4px;">' . htmlspecialchars($r['currency']) . '</span>' : ''; ?><strong><?php echo number_format($r['amount'], 2); ?></strong></td>
            <td class="cm-num">
                <?php if ($r['kg'] > 0 || $r['cbm'] > 0): ?>
                    <strong><?php echo number_format($r['kg'], 2); ?></strong> kg
                    <span class="cm-sub"><?php echo number_format($r['cbm'], 3); ?> CBM<?php echo $r['qty'] ? ' · ' . (int) $r['qty'] . ' pcs' : ''; ?></span>
                <?php elseif (!empty($r['fcl'])): ?>
                    <strong><?php echo (int) $r['qty']; ?></strong> × <?php echo htmlspecialchars($r['fcl']); ?>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td><span class="cm-pill <?php echo $cm_pill($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="7" style="text-align:right;">TOTAL &mdash; <?php echo (int) $totals['count']; ?> shipment<?php echo $totals['count'] == 1 ? '' : 's'; ?></td>
        <td class="cm-num">
            <?php foreach ($totals['amounts'] as $cur => $amt): ?>
                <div><?php echo htmlspecialchars($cur); ?> <?php echo number_format($amt, 2); ?></div>
            <?php endforeach; ?>
        </td>
        <td class="cm-num"><?php echo number_format($totals['kg'], 2); ?> kg<span class="cm-sub"><?php echo number_format($totals['cbm'], 3); ?> CBM</span></td>
        <td></td>
    </tr>
    </tfoot>
</table>
</div>
