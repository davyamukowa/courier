<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'cash_flow';
$data = $report_data ?? ['inflows' => [], 'outflows' => [], 'net' => 0];
?>
<?php $this->load->view('accounting/xb/reports/_filters'); ?>

<div class="xb-report xb-report-cf">
  <h3 class="xb-report-heading">Cash Flow Statement —
    <?php echo _d($filters['date_from'] ?? date('Y-01-01')); ?> to
    <?php echo _d($filters['date_to'] ?? date('Y-m-d')); ?>
  </h3>

  <table class="xb-table xb-report-table">
    <thead><tr><th>Category</th><th class="xb-num">Amount</th></tr></thead>
    <tbody>
      <tr class="xb-report-section-header"><td colspan="2"><strong>Operating Activities</strong></td></tr>
      <?php $in_total = 0; foreach ($data['inflows'] as $r): $in_total += $r->total; ?>
      <tr><td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $r->move_type))); ?> (In)</td><td class="xb-num xb-text-success"><?php echo number_format($r->total, 2); ?></td></tr>
      <?php endforeach; ?>
      <?php $out_total = 0; foreach ($data['outflows'] as $r): $out_total += $r->total; ?>
      <tr><td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $r->move_type))); ?> (Out)</td><td class="xb-num xb-text-danger">(<?php echo number_format($r->total, 2); ?>)</td></tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="xb-report-total">
        <th>Net Cash Flow</th>
        <th class="xb-num <?php echo $data['net'] >= 0 ? 'xb-text-success' : 'xb-text-danger'; ?>"><?php echo number_format($data['net'], 2); ?></th>
      </tr>
    </tfoot>
  </table>
</div>
