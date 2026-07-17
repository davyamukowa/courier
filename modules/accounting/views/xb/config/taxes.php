<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-list-header">
  <button class="xb-btn xb-btn-primary" onclick="xbOpenTaxModal()">+ New Tax</button>
</div>

<table class="xb-table">
  <thead>
    <tr><th>Name</th><th>Type</th><th>Amount Type</th><th>Rate</th><th>Account</th><th>Status</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($taxes ?? [] as $tax): ?>
    <tr>
      <td><?php echo htmlspecialchars($tax->name); ?></td>
      <td><?php echo $tax->type_tax_use === 'sale' ? 'Sales' : 'Purchase'; ?></td>
      <td><?php echo ucfirst($tax->amount_type); ?></td>
      <td><?php echo $tax->amount; ?>%</td>
      <td class="xb-muted"><?php echo $tax->account_code ? htmlspecialchars($tax->account_code . ' ' . $tax->account_name) : '—'; ?></td>
      <td><?php echo $tax->active ? '<span class="xb-badge xb-badge-success">Active</span>' : '<span class="xb-badge xb-badge-secondary">Inactive</span>'; ?></td>
      <td class="xb-actions">
        <button class="xb-btn-icon" onclick="xbOpenTaxModal(<?php echo htmlspecialchars(json_encode($tax)); ?>)">&#9998;</button>
        <button class="xb-btn-icon xb-text-danger" onclick="xbDeleteTax(<?php echo $tax->id; ?>, this)">&#10005;</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Tax Modal -->
<div id="xb-tax-modal" class="xb-modal" style="display:none">
  <div class="xb-modal-dialog">
    <div class="xb-modal-header">
      <h4 id="xb-tax-modal-title">New Tax</h4>
      <button class="xb-modal-close" onclick="document.getElementById('xb-tax-modal').style.display='none'">&times;</button>
    </div>
    <div class="xb-modal-body">
      <form id="xb-tax-form">
        <input type="hidden" name="id" id="tax_id">
        <div class="xb-form-row"><label>Name <span class="xb-req">*</span></label><input type="text" name="name" id="tax_name" class="xb-input" required></div>
        <div class="xb-form-row">
          <label>Tax Type</label>
          <select name="type_tax_use" id="tax_use" class="xb-select">
            <option value="sale">Sales</option>
            <option value="purchase">Purchase</option>
          </select>
        </div>
        <div class="xb-form-row">
          <label>Computation</label>
          <select name="amount_type" class="xb-select">
            <option value="percent">Percentage</option>
            <option value="fixed">Fixed Amount</option>
          </select>
        </div>
        <div class="xb-form-row"><label>Rate / Amount</label><input type="number" name="amount" id="tax_amount" class="xb-input" step="0.01" min="0" required></div>
        <div class="xb-form-row">
          <label>Tax Account</label>
          <select name="account_id" class="xb-select">
            <option value="">— None —</option>
            <?php foreach ($tax_accounts ?? [] as $a): ?>
            <option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->code . ' ' . $a->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="xb-form-row">
          <label><input type="checkbox" name="price_include" id="tax_incl" value="1"> Price includes tax</label>
        </div>
        <div class="xb-form-row">
          <label><input type="checkbox" name="active" id="tax_active" value="1" checked> Active</label>
        </div>
      </form>
    </div>
    <div class="xb-modal-footer">
      <button class="xb-btn xb-btn-secondary" onclick="document.getElementById('xb-tax-modal').style.display='none'">Cancel</button>
      <button class="xb-btn xb-btn-primary" onclick="xbSaveTax()">Save</button>
    </div>
  </div>
</div>

<script>
function xbOpenTaxModal(tax) {
    const form = document.getElementById('xb-tax-form');
    form.reset();
    document.getElementById('xb-tax-modal-title').textContent = tax ? 'Edit Tax' : 'New Tax';
    document.getElementById('tax_active').checked = true;
    if (tax) {
        document.getElementById('tax_id').value     = tax.id;
        document.getElementById('tax_name').value   = tax.name;
        document.getElementById('tax_use').value    = tax.type_tax_use;
        document.getElementById('tax_amount').value = tax.amount;
        document.getElementById('tax_incl').checked = tax.price_include == '1';
        document.getElementById('tax_active').checked = tax.active == '1';
    }
    document.getElementById('xb-tax-modal').style.display = 'flex';
}
function xbSaveTax() {
    const form = document.getElementById('xb-tax-form');
    const data = Object.fromEntries(new FormData(form));
    data.price_include = form.querySelector('[name=price_include]').checked ? 1 : 0;
    data.active = form.querySelector('[name=active]').checked ? 1 : 0;
    fetch('<?php echo admin_url('xetuu_books/ajax/save_tax'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => { if (res.success) window.location.reload(); else alert(res.error); });
}
function xbDeleteTax(id, btn) {
    if (!confirm('Delete?')) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/delete_tax'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(res => { if (res.success) btn.closest('tr').remove(); else alert(res.error); });
}
</script>
