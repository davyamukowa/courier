<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Note <?php echo htmlspecialchars($d['delivery_number']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#111;background:#fff;padding:20px}
.page{max-width:780px;margin:0 auto}
/* Header */
.dn-header{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:16px;border-bottom:3px solid #1a3f26;margin-bottom:16px}
.dn-logo img{max-height:60px;max-width:160px}
.dn-logo-text{font-size:22px;font-weight:900;color:#1a3f26}
.dn-title-block{text-align:right}
.dn-doc-title{font-size:22px;font-weight:900;text-transform:uppercase;color:#1a3f26;letter-spacing:1px}
.dn-doc-num{font-size:14px;font-weight:700;color:#333;margin-top:4px}
.dn-status-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-top:6px}
.badge-shipped{background:#dcfce7;color:#14532d;border:1px solid #86efac}
.badge-validated{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd}
.badge-draft{background:#f3f4f6;color:#6b7280;border:1px solid #d1d5db}
/* Info grid */
.dn-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.dn-info-box{background:#f8fdf9;border:1px solid #d4e8db;border-radius:6px;padding:12px}
.dn-info-box h4{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#4a6a56;font-weight:700;margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid #e2ece6}
.dn-info-row{display:flex;gap:6px;margin-bottom:4px;font-size:11px}
.dn-info-lbl{font-weight:700;color:#4a5e54;white-space:nowrap;min-width:100px}
.dn-info-val{color:#1a2520}
/* Items table */
table{width:100%;border-collapse:collapse;margin-bottom:16px;font-size:11px}
thead th{background:#1a3f26;color:#fff;padding:7px 8px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
thead th.r{text-align:right}
thead th.c{text-align:center}
tbody td{padding:7px 8px;border-bottom:1px solid #e2ece6;vertical-align:top}
tbody tr:nth-child(even) td{background:#f8fdf9}
tbody td.r{text-align:right}
tbody td.c{text-align:center}
tfoot td{padding:7px 8px;font-weight:700;border-top:2px solid #1a3f26}
/* Totals */
.dn-totals{margin-left:auto;width:300px;margin-bottom:20px}
.dn-total-row{display:flex;justify-content:space-between;padding:5px 10px;font-size:12px}
.dn-total-row.final{background:#1a3f26;color:#fff;border-radius:5px;font-size:13px;font-weight:700;margin-top:4px;padding:8px 10px}
/* Shipping */
.dn-ship-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:11px}
.dn-ship-box h4{font-size:10px;text-transform:uppercase;color:#1e40af;font-weight:700;margin-bottom:6px}
/* Signatures */
.dn-sig-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:24px}
.dn-sig-box{border-top:1.5px solid #333;padding-top:6px}
.dn-sig-lbl{font-size:9px;text-transform:uppercase;letter-spacing:.5px;color:#555}
.dn-sig-space{height:28px}
/* Drop-ship notice */
.drop-ship-tag{background:#ede9fe;color:#5b21b6;border-radius:99px;padding:1px 7px;font-size:9px;font-weight:700;margin-left:4px}
/* Footer */
.dn-footer{text-align:center;font-size:9px;color:#999;border-top:1px solid #e2ece6;padding-top:10px;margin-top:20px}
@media print{
  @page{size:A4 portrait;margin:12mm}
  .no-print{display:none!important}
  body{padding:0}
}
</style>
</head>
<body>
<div class="page">

  <!-- Print button (hidden on print) -->
  <div class="no-print" style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px">
    <button onclick="window.print()" style="background:#1a3f26;color:#fff;border:none;border-radius:6px;padding:8px 20px;font-size:13px;font-weight:700;cursor:pointer">
      &#128438; Print / Save as PDF
    </button>
    <button onclick="window.close()" style="background:#f3f4f6;color:#333;border:1px solid #d1d5db;border-radius:6px;padding:8px 16px;font-size:13px;cursor:pointer">
      Close
    </button>
  </div>

  <!-- Document header -->
  <div class="dn-header">
    <div class="dn-logo">
      <?php if ($logo_url): ?>
      <img src="<?php echo $logo_url; ?>" alt="<?php echo htmlspecialchars($company); ?>">
      <?php else: ?>
      <div class="dn-logo-text"><?php echo htmlspecialchars($company); ?></div>
      <?php endif; ?>
    </div>
    <div class="dn-title-block">
      <div class="dn-doc-title">Delivery Note</div>
      <div class="dn-doc-num"><?php echo htmlspecialchars($d['delivery_number']); ?></div>
      <?php
        $fs = $d['fulfillment_status'] ?? $d['status'] ?? 'draft';
        $badge_class = $fs === 'shipped' ? 'badge-shipped' : ($fs === 'validated' ? 'badge-validated' : 'badge-draft');
      ?>
      <span class="dn-status-badge <?php echo $badge_class; ?>"><?php echo ucfirst($fs); ?></span>
    </div>
  </div>

  <!-- Info grid -->
  <div class="dn-info-grid">
    <div class="dn-info-box">
      <h4><i>&#128209;</i> Document Details</h4>
      <div class="dn-info-row"><span class="dn-info-lbl">Delivery Date:</span><span class="dn-info-val"><?php echo $d['delivery_date'] ? date('d M Y', strtotime($d['delivery_date'])) : '—'; ?></span></div>
      <div class="dn-info-row"><span class="dn-info-lbl">Accounting Date:</span><span class="dn-info-val"><?php echo $d['accounting_date'] ? date('d M Y', strtotime($d['accounting_date'])) : '—'; ?></span></div>
      <div class="dn-info-row"><span class="dn-info-lbl">Type:</span><span class="dn-info-val"><?php echo ucfirst($d['type'] ?? 'Standard'); ?></span></div>
      <div class="dn-info-row"><span class="dn-info-lbl">Warehouse:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['branch_name'] ?? '—'); ?></span></div>
      <?php if ($d['sales_order_id'] ?? null): ?>
      <div class="dn-info-row"><span class="dn-info-lbl">Sales Order:</span><span class="dn-info-val"><?php echo htmlspecialchars($so['so_number'] ?? '#'.$d['sales_order_id']); ?></span></div>
      <?php endif; ?>
      <?php if ($d['invoice_number'] ?? null): ?>
      <div class="dn-info-row"><span class="dn-info-lbl">Invoice #:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['invoice_number']); ?></span></div>
      <?php endif; ?>
    </div>
    <div class="dn-info-box">
      <h4><i>&#128101;</i> Deliver To</h4>
      <div class="dn-info-row"><span class="dn-info-lbl">Customer:</span><span class="dn-info-val" style="font-weight:700"><?php echo htmlspecialchars($d['customer_name'] ?? '—'); ?></span></div>
      <div class="dn-info-row"><span class="dn-info-lbl">Receiver:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['receiver'] ?? '—'); ?></span></div>
      <div class="dn-info-row"><span class="dn-info-lbl">Address:</span><span class="dn-info-val"><?php echo nl2br(htmlspecialchars($d['address'] ?? '—')); ?></span></div>
      <?php if ($d['department'] ?? null): ?>
      <div class="dn-info-row"><span class="dn-info-lbl">Department:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['department']); ?></span></div>
      <?php endif; ?>
      <?php if ($d['requester'] ?? null): ?>
      <div class="dn-info-row"><span class="dn-info-lbl">Requester:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['requester']); ?></span></div>
      <?php endif; ?>
      <?php if ($d['project'] ?? null): ?>
      <div class="dn-info-row"><span class="dn-info-lbl">Project:</span><span class="dn-info-val"><?php echo htmlspecialchars($d['project']); ?></span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Shipping / tracking box (shown when dispatched) -->
  <?php if (!empty($d['shipped_at'])): ?>
  <div class="dn-ship-box">
    <h4>&#128666; Dispatch Information</h4>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
      <div><strong>Shipped At:</strong><br><?php echo date('d M Y H:i', strtotime($d['shipped_at'])); ?></div>
      <div><strong>Tracking #:</strong><br><?php echo htmlspecialchars($d['tracking_number'] ?: '—'); ?></div>
      <div><strong>Carrier:</strong><br><?php echo htmlspecialchars($d['carrier_info'] ?: '—'); ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Line items -->
  <table>
    <thead>
      <tr>
        <th style="width:28px" class="c">#</th>
        <th>Product / Item</th>
        <th>SKU</th>
        <th>Batch / Serial</th>
        <th class="c">Drop Ship</th>
        <th class="r">Qty</th>
        <th class="r">Unit Price</th>
        <th class="r">Tax</th>
        <th class="r">Discount</th>
        <th class="r">Total</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr>
      <td class="c" style="color:#999"><?php echo $i + 1; ?></td>
      <td style="font-weight:600">
        <?php echo htmlspecialchars($item['product_name'] ?? '—'); ?>
        <?php if (!empty($item['is_drop_ship'])): ?><span class="drop-ship-tag">DROP SHIP</span><?php endif; ?>
      </td>
      <td style="font-family:monospace;font-size:10px;color:#555"><?php echo htmlspecialchars($item['sku'] ?? '—'); ?></td>
      <td style="font-size:10px;color:#555">
        <?php echo htmlspecialchars($item['batch_no'] ?? ''); ?>
        <?php if ($item['serial_no'] ?? null): ?><br><span style="color:#888"><?php echo htmlspecialchars($item['serial_no']); ?></span><?php endif; ?>
        <?php if (!($item['batch_no'] ?? null) && !($item['serial_no'] ?? null)): ?>—<?php endif; ?>
      </td>
      <td class="c"><?php echo !empty($item['is_drop_ship']) ? '&#10003;' : ''; ?></td>
      <td class="r" style="font-weight:700"><?php echo number_format((float)$item['quantity'], 2); ?></td>
      <td class="r"><?php echo number_format((float)$item['unit_price'], 2); ?></td>
      <td class="r"><?php echo number_format((float)($item['tax_amount'] ?? 0), 2); ?></td>
      <td class="r" style="color:#dc2626"><?php echo (float)($item['discount_amount'] ?? 0) > 0 ? '-'.number_format((float)$item['discount_amount'], 2) : '—'; ?></td>
      <td class="r" style="font-weight:700"><?php echo number_format((float)($item['line_total'] ?? 0), 2); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <tr><td colspan="10" style="text-align:center;padding:20px;color:#999">No items</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="dn-totals">
    <div class="dn-total-row"><span>Subtotal:</span><span>Ksh <?php echo number_format((float)($d['subtotal'] ?? 0), 2); ?></span></div>
    <?php if ((float)($d['discount_amount'] ?? 0) > 0): ?>
    <div class="dn-total-row" style="color:#dc2626"><span>Discount:</span><span>- Ksh <?php echo number_format((float)$d['discount_amount'], 2); ?></span></div>
    <?php endif; ?>
    <?php if ((float)($d['shipping_fee'] ?? 0) > 0): ?>
    <div class="dn-total-row"><span>Shipping Fee:</span><span>Ksh <?php echo number_format((float)$d['shipping_fee'], 2); ?></span></div>
    <?php endif; ?>
    <div class="dn-total-row final"><span>Total:</span><span>Ksh <?php echo number_format((float)($d['total_amount'] ?? 0), 2); ?></span></div>
  </div>

  <!-- Notes -->
  <?php if ($d['note'] ?? null): ?>
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:11px">
    <strong style="display:block;margin-bottom:4px;color:#92400e">Notes:</strong>
    <?php echo nl2br(htmlspecialchars($d['note'])); ?>
  </div>
  <?php endif; ?>

  <!-- Signature blocks -->
  <div class="dn-sig-grid">
    <div class="dn-sig-box">
      <div class="dn-sig-space"></div>
      <div class="dn-sig-lbl">Prepared / Dispatched By</div>
      <?php if ($d['dispatched_by_name'] ?? null): ?>
      <div style="font-size:11px;margin-top:3px;color:#555"><?php echo htmlspecialchars($d['dispatched_by_name']); ?></div>
      <?php endif; ?>
    </div>
    <div class="dn-sig-box">
      <div class="dn-sig-space"></div>
      <div class="dn-sig-lbl">Received By (Customer / Receiver)</div>
    </div>
    <div class="dn-sig-box">
      <div class="dn-sig-space"></div>
      <div class="dn-sig-lbl">Authorized Signature &amp; Date</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="dn-footer">
    <?php echo htmlspecialchars($company); ?> &nbsp;|&nbsp;
    Delivery Note: <?php echo htmlspecialchars($d['delivery_number']); ?> &nbsp;|&nbsp;
    Generated: <?php echo date('d M Y H:i'); ?>
    <?php if ($d['sales_person'] ?? null): ?>&nbsp;|&nbsp; Sales: <?php echo htmlspecialchars($d['sales_person']); ?><?php endif; ?>
  </div>

</div>
</body>
</html>
