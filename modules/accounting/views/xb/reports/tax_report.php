<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'tax_report';
?>
<?php $this->load->view('accounting/xb/reports/_filters'); ?>

<div class="xb-report">
  <h3 class="xb-report-heading">Tax Report —
    <?php echo _d($filters['date_from'] ?? date('Y-01-01')); ?> to
    <?php echo _d($filters['date_to'] ?? date('Y-m-d')); ?>
  </h3>
  <table class="xb-table xb-report-table">
    <thead>
      <tr>
        <th>Tax Name</th>
        <th>Type</th>
        <th class="xb-num">Rate %</th>
        <th class="xb-num">Net Amount</th>
        <th class="xb-num">Tax Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $tot_net = $tot_tax = 0;
      if (empty($rows)): ?>
      <tr><td colspan="5" class="xb-empty">No tax transactions found.</td></tr>
      <?php else: foreach ($rows as $r):
        $tot_net += $r['net_amount'];
        $tot_tax += $r['tax_amount'];
      ?>
      <tr>
        <td><?php echo htmlspecialchars($r['tax_name']); ?></td>
        <td><?php echo $r['type'] === 'sale' ? 'Sales Tax' : 'Purchase Tax'; ?></td>
        <td class="xb-num"><?php echo $r['rate']; ?>%</td>
        <td class="xb-num"><?php echo number_format($r['net_amount'], 2); ?></td>
        <td class="xb-num xb-num-strong"><?php echo number_format($r['tax_amount'], 2); ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
    <tfoot>
      <tr class="xb-report-total">
        <th colspan="3">Total</th>
        <th class="xb-num"><?php echo number_format($tot_net, 2); ?></th>
        <th class="xb-num"><?php echo number_format($tot_tax, 2); ?></th>
      </tr>
    </tfoot>
  </table>
</div>
