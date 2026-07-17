<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'aged_receivable';
$buckets     = [0, 30, 60, 90];
?>
<div class="xb-report-filters">
  <form method="get" class="xb-filters">
    <label>As of:</label>
    <input type="date" name="date_to" class="xb-input xb-input-sm" value="<?php echo htmlspecialchars($filters['date_to'] ?? date('Y-m-d')); ?>">
    <button type="submit" class="xb-btn xb-btn-secondary xb-btn-sm">Apply</button>
    <a href="<?php echo admin_url('xetuu_books/report_export?report=aged_receivable&date_to=' . ($filters['date_to'] ?? date('Y-m-d'))); ?>" class="xb-btn xb-btn-secondary xb-btn-sm">Export CSV</a>
  </form>
</div>

<div class="xb-report">
  <h3 class="xb-report-heading">Aged Receivable — As of <?php echo _d($filters['date_to'] ?? date('Y-m-d')); ?></h3>
  <table class="xb-table xb-report-table">
    <thead>
      <tr>
        <th>Customer</th>
        <th class="xb-num">Current</th>
        <th class="xb-num">1–30 days</th>
        <th class="xb-num">31–60 days</th>
        <th class="xb-num">61–90 days</th>
        <th class="xb-num">90+ days</th>
        <th class="xb-num">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $tots = ['b0' => 0, 'b30' => 0, 'b60' => 0, 'b90' => 0, 'b_over' => 0, 'total' => 0];
      if (empty($rows)): ?>
      <tr><td colspan="7" class="xb-empty">No outstanding receivables.</td></tr>
      <?php else: foreach ($rows as $r):
        foreach ($tots as $k => &$v) { $v += $r[$k] ?? 0; } unset($v);
      ?>
      <tr>
        <td><?php echo htmlspecialchars($r['partner_name']); ?></td>
        <td class="xb-num"><?php echo $r['b0'] > 0 ? number_format($r['b0'], 2) : '—'; ?></td>
        <td class="xb-num"><?php echo $r['b30'] > 0 ? number_format($r['b30'], 2) : '—'; ?></td>
        <td class="xb-num"><?php echo $r['b60'] > 0 ? number_format($r['b60'], 2) : '—'; ?></td>
        <td class="xb-num"><?php echo $r['b90'] > 0 ? number_format($r['b90'], 2) : '—'; ?></td>
        <td class="xb-num xb-text-danger"><?php echo ($r['b_over'] ?? 0) > 0 ? number_format($r['b_over'], 2) : '—'; ?></td>
        <td class="xb-num xb-num-strong"><?php echo number_format($r['total'], 2); ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
    <tfoot>
      <tr class="xb-report-total">
        <th>Total</th>
        <th class="xb-num"><?php echo number_format($tots['b0'], 2); ?></th>
        <th class="xb-num"><?php echo number_format($tots['b30'], 2); ?></th>
        <th class="xb-num"><?php echo number_format($tots['b60'], 2); ?></th>
        <th class="xb-num"><?php echo number_format($tots['b90'], 2); ?></th>
        <th class="xb-num xb-text-danger"><?php echo number_format($tots['b_over'], 2); ?></th>
        <th class="xb-num"><?php echo number_format($tots['total'], 2); ?></th>
      </tr>
    </tfoot>
  </table>
</div>
