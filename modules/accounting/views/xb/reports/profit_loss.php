<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'profit_loss';
$data = $report_data ?? [];
?>
<?php $this->load->view('accounting/xb/reports/_filters'); ?>

<div class="xb-report xb-report-pl">
  <h3 class="xb-report-heading">Profit &amp; Loss —
    <?php echo _d($filters['date_from'] ?? date('Y-01-01')); ?> to
    <?php echo _d($filters['date_to'] ?? date('Y-m-d')); ?>
  </h3>

  <?php foreach (['Revenue' => true, 'Cost of Revenue' => false, 'Expense' => false] as $section => $is_rev): ?>
  <?php $sec = $data[$section] ?? ['lines' => [], 'total' => 0]; ?>
  <div class="xb-pl-section">
    <h4 class="xb-pl-section-title"><?php echo htmlspecialchars($section); ?></h4>
    <table class="xb-table xb-report-table">
      <tbody>
        <?php foreach ($sec['lines'] as $line): ?>
        <tr>
          <td><?php echo htmlspecialchars($line['code']); ?> &nbsp; <?php echo htmlspecialchars($line['name']); ?></td>
          <td class="xb-num <?php echo $line['amount'] < 0 ? 'xb-text-danger' : ''; ?>"><?php echo number_format($line['amount'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="xb-report-total">
          <th>Total <?php echo htmlspecialchars($section); ?></th>
          <th class="xb-num"><?php echo number_format($sec['total'], 2); ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php if ($section === 'Cost of Revenue'): ?>
  <div class="xb-pl-subtotal">
    <span>Gross Profit</span>
    <span class="xb-num <?php echo ($data['gross_profit'] ?? 0) < 0 ? 'xb-text-danger' : 'xb-text-success'; ?>">
      <?php echo number_format($data['gross_profit'] ?? 0, 2); ?>
    </span>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>

  <div class="xb-pl-net">
    <span>Net Income</span>
    <span class="xb-num <?php echo ($data['net_income'] ?? 0) < 0 ? 'xb-text-danger' : 'xb-text-success'; ?>">
      <?php echo number_format($data['net_income'] ?? 0, 2); ?>
    </span>
  </div>
</div>
