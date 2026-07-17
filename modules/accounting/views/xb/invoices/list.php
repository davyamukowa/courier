<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_bill = isset($move_type) && in_array($move_type, ['in_invoice', 'in_refund']);
$base_url = $is_bill ? admin_url('xetuu_books/bills') : admin_url('xetuu_books/invoices');
$form_url = $is_bill ? admin_url('xetuu_books/bill_form') : admin_url('xetuu_books/invoice_form');
?>

<div class="xb-list-header">
  <div class="xb-filters" id="xb-filter-form">
    <input type="text" class="xb-input xb-input-sm" id="xb_search" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_from" value="<?php echo $filters['date_from'] ?? ''; ?>" placeholder="From">
    <input type="date" class="xb-input xb-input-sm" id="xb_date_to" value="<?php echo $filters['date_to'] ?? ''; ?>" placeholder="To">
    <select class="xb-select xb-input-sm" id="xb_state">
      <option value="">All Status</option>
      <option value="draft" <?php echo ($filters['state'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
      <option value="posted" <?php echo ($filters['state'] ?? '') === 'posted' ? 'selected' : ''; ?>>Posted</option>
      <option value="cancel" <?php echo ($filters['state'] ?? '') === 'cancel' ? 'selected' : ''; ?>>Cancelled</option>
    </select>
    <button class="xb-btn xb-btn-secondary xb-btn-sm" onclick="xbFilterList()">Filter</button>
    <a href="<?php echo $base_url; ?>" class="xb-btn xb-btn-secondary xb-btn-sm">Reset</a>
  </div>
  <a href="<?php echo $form_url; ?>" class="xb-btn xb-btn-primary">
    + New <?php echo $is_bill ? 'Bill' : 'Invoice'; ?>
  </a>
</div>

<!-- Totals strip -->
<?php if (!empty($list_totals)): ?>
<div class="xb-totals-strip">
  <span class="xb-totals-item">
    <span class="xb-totals-label">Total</span>
    <span class="xb-totals-value"><?php echo app_format_money($list_totals->total ?? 0, get_base_currency()); ?></span>
  </span>
  <span class="xb-totals-item">
    <span class="xb-totals-label">Outstanding</span>
    <span class="xb-totals-value"><?php echo app_format_money($list_totals->residual ?? 0, get_base_currency()); ?></span>
  </span>
  <span class="xb-totals-item">
    <span class="xb-totals-label">Overdue</span>
    <span class="xb-totals-value xb-text-danger"><?php echo app_format_money($list_totals->overdue ?? 0, get_base_currency()); ?></span>
  </span>
</div>
<?php endif; ?>

<table class="xb-table xb-table-hover" id="xb-invoice-list">
  <thead>
    <tr>
      <th><input type="checkbox" id="xb-chk-all" onchange="xbSelectAll(this)"></th>
      <th>Number</th>
      <th><?php echo $is_bill ? 'Vendor' : 'Customer'; ?></th>
      <th>Date</th>
      <?php if (!$is_bill): ?><th>Due Date</th><?php else: ?><th>Bill Date</th><?php endif; ?>
      <th>Amount</th>
      <th>Outstanding</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($moves)): ?>
    <tr><td colspan="9" class="xb-empty">No <?php echo $is_bill ? 'bills' : 'invoices'; ?> found.</td></tr>
    <?php else: foreach ($moves as $m): ?>
    <tr data-id="<?php echo $m->id; ?>">
      <td><input type="checkbox" class="xb-row-chk" value="<?php echo $m->id; ?>"></td>
      <td>
        <a href="<?php echo $form_url . '/' . $m->id; ?>" class="xb-link-primary">
          <?php echo htmlspecialchars($m->name ?: '(Draft)'); ?>
        </a>
        <?php if ($m->invoice_origin): ?>
        <small class="xb-muted"><?php echo htmlspecialchars($m->invoice_origin); ?></small>
        <?php endif; ?>
      </td>
      <td><?php echo htmlspecialchars($m->partner_name ?? '—'); ?></td>
      <td><?php echo $m->date ? _d($m->date) : '—'; ?></td>
      <td><?php echo $m->invoice_date_due ? _d($m->invoice_date_due) : '—'; ?></td>
      <td><?php echo app_format_money($m->amount_total, get_base_currency()); ?></td>
      <td>
        <?php if ($m->payment_state === 'paid'): ?>
        <span class="xb-text-success">—</span>
        <?php else: ?>
        <?php echo app_format_money($m->amount_residual, get_base_currency()); ?>
        <?php endif; ?>
      </td>
      <td>
        <?php
        $badge_map = ['paid' => 'success', 'partial' => 'warning', 'not_paid' => 'secondary', 'in_payment' => 'info', 'reversed' => 'danger'];
        $label_map = ['paid' => 'Paid', 'partial' => 'Partial', 'not_paid' => 'Unpaid', 'in_payment' => 'In Payment', 'reversed' => 'Reversed'];
        $state_str  = $m->state === 'draft' ? 'draft' : ($m->payment_state ?? 'not_paid');
        $badge_cls  = $m->state === 'draft' ? 'xb-badge-draft' : ('xb-badge-' . ($badge_map[$state_str] ?? 'secondary'));
        $state_lbl  = $m->state === 'draft' ? 'Draft' : ($label_map[$state_str] ?? ucfirst($state_str));
        ?>
        <span class="xb-badge <?php echo $badge_cls; ?>"><?php echo $state_lbl; ?></span>
      </td>
      <td class="xb-actions">
        <a href="<?php echo $form_url . '/' . $m->id; ?>" class="xb-btn-icon" title="Open">&#9998;</a>
        <?php if ($m->state === 'posted' && $m->payment_state !== 'paid'): ?>
        <a href="#" class="xb-btn-icon xb-pay-btn" data-id="<?php echo $m->id; ?>" data-amount="<?php echo $m->amount_residual; ?>" title="Register Payment">&#36;</a>
        <?php endif; ?>
        <?php if ($m->state === 'draft'): ?>
        <a href="#" class="xb-btn-icon xb-text-danger" onclick="xbDeleteMove(<?php echo $m->id; ?>, this)" title="Delete">&#10005;</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>

<!-- Payment modal -->
<div id="xb-pay-modal" class="xb-modal" style="display:none">
  <div class="xb-modal-dialog">
    <div class="xb-modal-header">
      <h4>Register Payment</h4>
      <button class="xb-modal-close" onclick="document.getElementById('xb-pay-modal').style.display='none'">&times;</button>
    </div>
    <div class="xb-modal-body">
      <form id="xb-pay-form">
        <input type="hidden" name="move_id" id="pay_move_id">
        <div class="xb-form-row">
          <label>Journal</label>
          <select name="journal_id" class="xb-select" required>
            <?php foreach ($payment_journals ?? [] as $j): ?>
            <option value="<?php echo $j->id; ?>"><?php echo htmlspecialchars($j->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="xb-form-row">
          <label>Amount</label>
          <input type="number" name="amount" id="pay_amount" step="0.01" min="0.01" class="xb-input" required>
        </div>
        <div class="xb-form-row">
          <label>Date</label>
          <input type="date" name="date" class="xb-input" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="xb-form-row">
          <label>Memo</label>
          <input type="text" name="memo" class="xb-input" placeholder="Payment memo">
        </div>
      </form>
    </div>
    <div class="xb-modal-footer">
      <button class="xb-btn xb-btn-secondary" onclick="document.getElementById('xb-pay-modal').style.display='none'">Cancel</button>
      <button class="xb-btn xb-btn-primary" onclick="xbSubmitPayment()">Register Payment</button>
    </div>
  </div>
</div>

<script>
function xbFilterList() {
    const params = new URLSearchParams({
        search: document.getElementById('xb_search').value,
        date_from: document.getElementById('xb_date_from').value,
        date_to: document.getElementById('xb_date_to').value,
        state: document.getElementById('xb_state').value
    });
    window.location.href = '<?php echo $base_url; ?>?' + params.toString();
}
document.querySelectorAll('.xb-pay-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('pay_move_id').value = this.dataset.id;
        document.getElementById('pay_amount').value = parseFloat(this.dataset.amount).toFixed(2);
        document.getElementById('xb-pay-modal').style.display = 'flex';
    });
});
function xbSubmitPayment() {
    const form = document.getElementById('xb-pay-form');
    const data = Object.fromEntries(new FormData(form));
    fetch('<?php echo admin_url('xetuu_books/ajax/register_payment'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
        if (res.success) { window.location.reload(); }
        else { alert(res.error || 'Payment failed'); }
    });
}
function xbDeleteMove(id, el) {
    if (!confirm('Delete this draft?')) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/delete_move'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id: id})
    }).then(r => r.json()).then(res => {
        if (res.success) { el.closest('tr').remove(); }
        else { alert(res.error || 'Delete failed'); }
    });
}
function xbSelectAll(chk) {
    document.querySelectorAll('.xb-row-chk').forEach(c => c.checked = chk.checked);
}
</script>
