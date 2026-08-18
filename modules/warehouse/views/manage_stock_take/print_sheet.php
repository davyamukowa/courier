<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 24px 30px;
        }
        h1 {
            font-size: 30px;
            margin: 0 0 14px 0;
        }
        .meta-line {
            font-size: 12px;
            margin-bottom: 14px;
        }
        table.header-fields {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.header-fields td {
            font-size: 12px;
            padding: 4px 6px 4px 0;
            white-space: nowrap;
        }
        table.header-fields .fill-line {
            border-bottom: 1px solid #666;
            width: 100%;
            display: inline-block;
            min-width: 90px;
            height: 14px;
        }
        table.sheet {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.sheet th, table.sheet td {
            border: 1px solid #999;
            font-size: 11px;
            padding: 5px 6px;
            text-align: left;
        }
        table.sheet th {
            background: #eef1f4;
            font-weight: bold;
        }
        table.sheet td.blank-row {
            height: 22px;
        }
        .col-item   { width: 16%; }
        .col-desc   { width: 32%; }
        .col-qty    { width: 10%; }
        .col-loc    { width: 18%; }
        .col-notes  { width: 24%; }

        .print-bar {
            margin-bottom: 18px;
        }
        .print-bar button {
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
        }

        @media print {
            .print-bar { display: none; }
            body { padding: 10px 16px; }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <button onclick="window.print();"><?php echo _l('print'); ?></button>
    </div>

    <h1><?php echo _l('stock_take'); ?> — <?php echo _l('_inventory'); ?> Log Book</h1>
    <div class="meta-line"><?php echo htmlspecialchars(get_option('companyname')); ?> &mdash; <?php echo htmlspecialchars($warehouse->warehouse_name); ?><?php echo !empty($warehouse->warehouse_address) ? ' (' . htmlspecialchars($warehouse->warehouse_address) . ')' : ''; ?></div>

    <table class="header-fields">
        <tr>
            <td><strong>DATE:</strong> <span class="fill-line"></span></td>
            <td><strong>TIME:</strong> <span class="fill-line"></span></td>
            <td><strong>COUNTED BY:</strong> <span class="fill-line"></span></td>
            <td><strong>SHEET#:</strong> <span class="fill-line"></span></td>
        </tr>
    </table>

    <table class="sheet">
        <thead>
        <tr>
            <th class="col-item">ITEM# / SKU</th>
            <th class="col-desc">DESCRIPTION</th>
            <th class="col-qty">QTY</th>
            <th class="col-loc">LOCATION</th>
            <th class="col-notes">NOTES</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['commodity_code']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td class="blank-row"></td>
                    <td class="blank-row"></td>
                    <td class="blank-row"></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php
        // Pad with a few fully-blank rows so items added after this sheet was
        // printed (or anything the item list missed) can still be counted.
        for ($i = 0; $i < 15; $i++) {
        ?>
            <tr>
                <td class="blank-row"></td>
                <td class="blank-row"></td>
                <td class="blank-row"></td>
                <td class="blank-row"></td>
                <td class="blank-row"></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</body>
</html>
