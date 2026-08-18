<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Log Book<?php echo $log ? ' — Sheet# ' . htmlspecialchars($log['sheet_number']) : ''; ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background: #dbe9f0;
            margin: 0;
            padding: 30px 40px 50px;
        }
        .sheet {
            max-width: 780px;
            margin: 0 auto;
        }
        h1 {
            font-size: 42px;
            font-weight: 700;
            margin: 0 0 14px 0;
        }
        .company-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #9fb3bd;
        }
        .company-header img {
            max-height: 55px;
            max-width: 180px;
        }
        .company-header__name {
            font-size: 16px;
            font-weight: 700;
        }
        .company-header__text {
            font-size: 12px;
            color: #333;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 36px;
            font-size: 12px;
        }
        .signatures span { flex: 1; }
        .sig-img { max-height: 55px; vertical-align: middle; margin-left: 6px; }
        .company-line {
            font-size: 12px;
            margin-bottom: 6px;
        }
        table.header-fields {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.header-fields td {
            font-size: 12px;
            padding: 3px 8px 8px 0;
            white-space: nowrap;
        }
        table.header-fields .fill-line {
            border-bottom: 1px solid #444;
            display: inline-block;
            min-width: 70px;
            height: 13px;
        }
        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.sheet-table th, table.sheet-table td {
            border: 1px solid #9fb3bd;
            font-size: 11px;
            padding: 6px 8px;
            text-align: left;
            height: 22px;
        }
        table.sheet-table th {
            background: rgba(255,255,255,.45);
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
        }
        .col-item   { width: 16%; }
        .col-desc   { width: 34%; }
        .col-qty    { width: 10%; }
        .col-loc    { width: 18%; }
        .col-notes  { width: 22%; }

        .print-bar {
            max-width: 780px;
            margin: 0 auto 16px;
        }
        .print-bar button {
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
        }

        @media print {
            .print-bar { display: none; }
            body { padding: 14px 20px; }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <button onclick="window.print();">Print / Save as PDF</button>
    </div>

    <div class="sheet">

        <?php if (!empty($company['logo_url']) || !empty($company['name'])): ?>
        <div class="company-header">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($company['logo_url']); ?>" alt="Logo">
            <?php endif; ?>
            <div class="company-header__text">
                <?php if (!empty($company['name'])): ?><div class="company-header__name"><?php echo htmlspecialchars($company['name']); ?></div><?php endif; ?>
                <?php
                // The P.O. Box setting is sometimes mis-filled with the email
                // address (this tenant's data) — don't show it twice.
                $pobox_is_dupe = !empty($company['pobox']) && !empty($company['email'])
                    && strcasecmp(trim($company['pobox']), trim($company['email'])) === 0;
                $contact_bits = array_filter([
                    !empty($company['phone']) ? 'Phone: ' . $company['phone'] : '',
                    (!empty($company['pobox']) && !$pobox_is_dupe) ? 'P.O. Box: ' . $company['pobox'] : '',
                    !empty($company['email']) ? 'Email: ' . $company['email'] : '',
                ]);
                ?>
                <?php if (!empty($contact_bits)): ?><div><?php echo htmlspecialchars(implode('   |   ', $contact_bits)); ?></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <h1>Inventory Log Book</h1>
        <div class="company-line">
            COMPANY NAME: <?php echo $log ? htmlspecialchars($log['company_name']) : '<span class="fill-line" style="min-width:220px;"></span>'; ?>
        </div>

        <table class="header-fields">
            <tr>
                <td>
                    <strong>DATE:</strong>
                    <?php if ($log && !empty($log['log_date'])): ?>
                        <?php echo date('m / d / Y', strtotime($log['log_date'])); ?>
                    <?php else: ?>
                        <span class="fill-line" style="min-width:20px;"></span> /
                        <span class="fill-line" style="min-width:20px;"></span> /
                        <span class="fill-line" style="min-width:30px;"></span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong>TIME:</strong>
                    <?php if ($log && !empty($log['log_time'])): ?>
                        <?php echo date('h:i', strtotime($log['log_time'])) . ' ' . htmlspecialchars($log['am_pm']); ?>
                    <?php else: ?>
                        <span class="fill-line" style="min-width:50px;"></span>
                        A.M. / P.M.
                    <?php endif; ?>
                </td>
                <td><strong>COUNTED BY:</strong> <span class="fill-line" style="min-width:<?php echo $log ? '0' : '120'; ?>px;"><?php echo $log ? htmlspecialchars($log['counted_by']) : ''; ?></span></td>
                <td><strong>SHEET#:</strong> <span class="fill-line" style="min-width:<?php echo $log ? '0' : '80'; ?>px;"><?php echo $log ? htmlspecialchars($log['sheet_number']) : ''; ?></span></td>
            </tr>
        </table>

        <table class="sheet-table">
            <thead>
            <tr>
                <th class="col-item">Item# / SKU</th>
                <th class="col-desc">Description</th>
                <th class="col-qty">Qty</th>
                <th class="col-loc">Location</th>
                <th class="col-notes">Notes</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_sku'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['qty'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php
            // Pad out to a full page of rows, blank for hand-filling — matches
            // the original paper log book's row count either way.
            $blank_rows = $log ? max(0, 30 - count($items)) : 32;
            for ($i = 0; $i < $blank_rows; $i++):
            ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="signatures">
            <span>
                Issued by <?php echo (!empty($log) && !empty($log['issued_by'])) ? htmlspecialchars($log['issued_by']) : '..........................................'; ?>
                &nbsp; Sign
                <?php if (!empty($log) && !empty($log['issued_by_signature'])): ?>
                    <img src="<?php echo base_url($log['issued_by_signature']); ?>" alt="Signature" class="sig-img">
                <?php else: ?>
                    ....................
                <?php endif; ?>
            </span>
            <span>
                Received by <?php echo (!empty($log) && !empty($log['received_by'])) ? htmlspecialchars($log['received_by']) : '..........................................'; ?>
                &nbsp; Sign
                <?php if (!empty($log) && !empty($log['received_by_signature'])): ?>
                    <img src="<?php echo base_url($log['received_by_signature']); ?>" alt="Signature" class="sig-img">
                <?php else: ?>
                    ....................
                <?php endif; ?>
            </span>
        </div>
    </div>

</body>
</html>
