<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_vendor = isset($partner_type) && $partner_type === 'vendor';
?>

<div class="xb-list-header">
  <div class="xb-filters">
    <input type="text" class="xb-input xb-input-sm" id="xb_search" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_from" value="<?php echo $filters['date_from'] ?? ''; ?>">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_to" value="<?php echo $filters['date_to'] ?? ''; ?>">
    <select class="xb-select xb-input-sm" id="xb_state">
      <option value="">All</option>
      <option value="posted" <?php echo ($filters['state'] ?? '') === 'posted' ? 'selected' : ''; ?>>Posted</option>
      <option value="draft" <?php echo ($filters['state'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
    </select>
    <button class="xb-btn xb-btn-secondary xb-btn-sm" onclick="xbFilterPayments()">Filter</button>
  </div>
</div>

<table class="xb-table xb-table-hover">
  <thead>
    <tr>
      <th>Reference</th>
      <th><?php echo $is_vendor ? 'Vendor' : 'Customer'; ?></th>
      <th>Date</th>
      <th>Journal</th>
      <th>Amount</th>
      <th>Type</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($payments)): ?>
    <tr><td colspan="7" class="xb-empty">No payments found.</td></tr>
    <?php else: foreach ($payments as $p): ?>
    <tr>
      <td><?php echo htmlspecialchars($p->name); ?></td>
      <td><?php echo htmlspecialchars($p->partner_name ?? '—'); ?></td>
      <td><?php echo $p->date ? _d($p->date) : '—'; ?></td>
      <td><?php echo htmlspecialchars($p->journal_name ?? ''); ?></td>
      <td><?php echo app_format_money($p->amount, get_base_currency()); ?></td>
      <td><?php echo $p->payment_type === 'inbound' ? 'Received' : 'Sent'; ?></td>
      <td><span class="xb-badge xb-badge-<?php echo $p->state === 'posted' ? 'success' : 'draft'; ?>"><?php echo ucfirst($p->state); ?></span></td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>

<script>
function xbFilterPayments() {
    const p = new URLSearchParams({
        search: document.getElementById('xb_search').value,
        date_from: document.getElementById('xb_date_from').value,
        date_to: document.getElementById('xb_date_to').value,
        state: document.getElementById('xb_state').value
    });
    window.location.href = window.location.pathname + '?' + p.toString();
}
</script>
