<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-list-header">
  <div class="xb-filters">
    <input type="text" class="xb-input xb-input-sm" id="xb_search" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_from" value="<?php echo $filters['date_from'] ?? ''; ?>">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_to" value="<?php echo $filters['date_to'] ?? ''; ?>">
    <select class="xb-select xb-input-sm" id="xb_state">
      <option value="">All Status</option>
      <option value="draft" <?php echo ($filters['state'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
      <option value="posted" <?php echo ($filters['state'] ?? '') === 'posted' ? 'selected' : ''; ?>>Posted</option>
      <option value="cancel" <?php echo ($filters['state'] ?? '') === 'cancel' ? 'selected' : ''; ?>>Cancelled</option>
    </select>
    <button class="xb-btn xb-btn-secondary xb-btn-sm" onclick="xbFilterJE()">Filter</button>
  </div>
  <a href="<?php echo admin_url('xetuu_books/journal_entry_form'); ?>" class="xb-btn xb-btn-primary">+ New Entry</a>
</div>

<table class="xb-table xb-table-hover">
  <thead>
    <tr>
      <th>Reference</th>
      <th>Date</th>
      <th>Journal</th>
      <th>Narration</th>
      <th>Debit</th>
      <th>Credit</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($entries)): ?>
    <tr><td colspan="8" class="xb-empty">No journal entries found.</td></tr>
    <?php else: foreach ($entries as $e): ?>
    <tr>
      <td><a href="<?php echo admin_url('xetuu_books/journal_entry_form/' . $e->id); ?>"><?php echo htmlspecialchars($e->name ?: '(Draft)'); ?></a></td>
      <td><?php echo $e->date ? _d($e->date) : '—'; ?></td>
      <td><?php echo htmlspecialchars($e->journal_name ?? ''); ?></td>
      <td><?php echo htmlspecialchars(mb_strimwidth($e->narration ?? '', 0, 60, '...')); ?></td>
      <td><?php echo app_format_money($e->total_debit ?? 0, get_base_currency()); ?></td>
      <td><?php echo app_format_money($e->total_credit ?? 0, get_base_currency()); ?></td>
      <td><span class="xb-badge xb-badge-<?php echo $e->state === 'posted' ? 'success' : ($e->state === 'cancel' ? 'danger' : 'draft'); ?>"><?php echo ucfirst($e->state); ?></span></td>
      <td class="xb-actions">
        <a href="<?php echo admin_url('xetuu_books/journal_entry_form/' . $e->id); ?>" class="xb-btn-icon">&#9998;</a>
        <?php if ($e->state === 'draft'): ?>
        <a href="#" class="xb-btn-icon xb-text-danger" onclick="xbDeleteEntry(<?php echo $e->id; ?>, this)">&#10005;</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>

<script>
function xbFilterJE() {
    const p = new URLSearchParams({
        search: document.getElementById('xb_search').value,
        date_from: document.getElementById('xb_date_from').value,
        date_to: document.getElementById('xb_date_to').value,
        state: document.getElementById('xb_state').value
    });
    window.location.href = '<?php echo admin_url('xetuu_books/journal_entries'); ?>?' + p.toString();
}
function xbDeleteEntry(id, el) {
    if (!confirm('Delete this draft entry?')) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/delete_move'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(res => { if (res.success) el.closest('tr').remove(); else alert(res.error); });
}
</script>
