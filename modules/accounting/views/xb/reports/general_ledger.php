<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'general_ledger';
?>
<?php $this->load->view('accounting/xb/reports/_filters'); ?>

<div class="xb-report" id="xb-report-gl">
  <?php if (empty($ledger_data)): ?>
  <p class="xb-empty">No posted entries for selected period.</p>
  <?php else: foreach ($ledger_data as $group): ?>
  <div class="xb-gl-account-block">
    <div class="xb-gl-account-header">
      <span class="xb-gl-code"><?php echo htmlspecialchars($group['account']['code']); ?></span>
      <span class="xb-gl-name"><?php echo htmlspecialchars($group['account']['name']); ?></span>
      <span class="xb-gl-type xb-muted"><?php echo htmlspecialchars($group['account']['type']); ?></span>
    </div>
    <table class="xb-table xb-report-table xb-table-compact">
      <thead>
        <tr><th>Date</th><th>Journal Entry</th><th>Label</th><th class="xb-num">Debit</th><th class="xb-num">Credit</th><th class="xb-num">Balance</th></tr>
      </thead>
      <tbody>
        <?php foreach ($group['lines'] as $l): ?>
        <tr>
          <td><?php echo _d($l->move_date); ?></td>
          <td><?php echo htmlspecialchars($l->move_name ?: $l->move_ref ?: ''); ?></td>
          <td><?php echo htmlspecialchars($l->name); ?></td>
          <td class="xb-num"><?php echo $l->debit > 0 ? number_format($l->debit, 2) : ''; ?></td>
          <td class="xb-num"><?php echo $l->credit > 0 ? number_format($l->credit, 2) : ''; ?></td>
          <td class="xb-num xb-num-strong <?php echo $l->running < 0 ? 'xb-text-danger' : ''; ?>"><?php echo number_format($l->running, 2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endforeach; endif; ?>
</div>
