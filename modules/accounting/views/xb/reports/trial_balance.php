<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'trial_balance';
?>
<?php $this->load->view('accounting/xb/reports/_filters'); ?>

<div class="xb-report xb-report-grid" id="xb-report-trial-balance">
  <table class="xb-table xb-report-table">
    <thead>
      <tr>
        <th>Account Code</th>
        <th>Account Name</th>
        <th>Type</th>
        <th class="xb-num">Opening Balance</th>
        <th class="xb-num">Debit</th>
        <th class="xb-num">Credit</th>
        <th class="xb-num">Closing Balance</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $tot_ob = $tot_d = $tot_c = $tot_cl = 0;
      if (empty($rows)): ?>
      <tr><td colspan="7" class="xb-empty">No data for selected period.</td></tr>
      <?php else: foreach ($rows as $r):
        $tot_ob += $r['opening_balance'];
        $tot_d  += $r['debit'];
        $tot_c  += $r['credit'];
        $tot_cl += $r['closing_balance'];
      ?>
      <tr>
        <td><?php echo htmlspecialchars($r['code']); ?></td>
        <td><?php echo htmlspecialchars($r['name']); ?></td>
        <td class="xb-muted"><?php echo htmlspecialchars($r['type']); ?></td>
        <td class="xb-num <?php echo $r['opening_balance'] < 0 ? 'xb-text-danger' : ''; ?>"><?php echo number_format($r['opening_balance'], 2); ?></td>
        <td class="xb-num"><?php echo number_format($r['debit'], 2); ?></td>
        <td class="xb-num"><?php echo number_format($r['credit'], 2); ?></td>
        <td class="xb-num xb-num-strong <?php echo $r['closing_balance'] < 0 ? 'xb-text-danger' : ''; ?>"><?php echo number_format($r['closing_balance'], 2); ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
    <tfoot>
      <tr class="xb-report-total">
        <th colspan="3">Total</th>
        <th class="xb-num"><?php echo number_format($tot_ob, 2); ?></th>
        <th class="xb-num"><?php echo number_format($tot_d, 2); ?></th>
        <th class="xb-num"><?php echo number_format($tot_c, 2); ?></th>
        <th class="xb-num"><?php echo number_format($tot_cl, 2); ?></th>
      </tr>
    </tfoot>
  </table>
</div>
