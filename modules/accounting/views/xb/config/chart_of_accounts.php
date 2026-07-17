<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-list-header">
  <div class="xb-filters">
    <input type="text" class="xb-input xb-input-sm" id="coa_search" placeholder="Search code or name..." oninput="xbFilterCOA()">
    <select class="xb-select xb-input-sm" id="coa_type" onchange="xbFilterCOA()">
      <option value="">All Types</option>
      <?php $types = ['Asset','Current Asset','Fixed Asset','Bank','Cash','Liability','Current Liability','Long-term Liability','Equity','Revenue','Cost of Revenue','Expense','Other Income','Other Expense','Tax','Receivable','Payable'];
      foreach ($types as $t): ?>
      <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="xb-btn xb-btn-primary" onclick="xbOpenAccountModal()">+ New Account</button>
</div>

<table class="xb-table xb-table-hover" id="coa-table">
  <thead>
    <tr><th>Code</th><th>Name</th><th>Type</th><th>Parent</th><th>Active</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($accounts ?? [] as $a): ?>
    <tr data-code="<?php echo strtolower($a->code); ?>" data-name="<?php echo strtolower($a->name); ?>" data-type="<?php echo $a->type; ?>">
      <td><?php echo htmlspecialchars($a->code); ?></td>
      <td><?php echo htmlspecialchars($a->name); ?></td>
      <td class="xb-muted"><?php echo htmlspecialchars($a->type); ?></td>
      <td class="xb-muted"><?php echo $a->parent_code ? htmlspecialchars($a->parent_code . ' ' . $a->parent_name) : '—'; ?></td>
      <td><?php echo $a->active ? '<span class="xb-badge xb-badge-success">Active</span>' : '<span class="xb-badge xb-badge-secondary">Inactive</span>'; ?></td>
      <td class="xb-actions">
        <button class="xb-btn-icon" onclick="xbOpenAccountModal(<?php echo htmlspecialchars(json_encode($a)); ?>)">&#9998;</button>
        <button class="xb-btn-icon xb-text-danger" onclick="xbDeleteAccount(<?php echo $a->id; ?>, this)">&#10005;</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Account Modal -->
<div id="xb-account-modal" class="xb-modal" style="display:none">
  <div class="xb-modal-dialog">
    <div class="xb-modal-header">
      <h4 id="xb-acct-modal-title">New Account</h4>
      <button class="xb-modal-close" onclick="document.getElementById('xb-account-modal').style.display='none'">&times;</button>
    </div>
    <div class="xb-modal-body">
      <form id="xb-account-form">
        <input type="hidden" name="id" id="acct_id">
        <div class="xb-form-row">
          <label>Code <span class="xb-req">*</span></label>
          <input type="text" name="code" id="acct_code" class="xb-input" required>
        </div>
        <div class="xb-form-row">
          <label>Name <span class="xb-req">*</span></label>
          <input type="text" name="name" id="acct_name" class="xb-input" required>
        </div>
        <div class="xb-form-row">
          <label>Type <span class="xb-req">*</span></label>
          <select name="type" id="acct_type" class="xb-select" required>
            <?php $types = ['Asset','Current Asset','Fixed Asset','Bank','Cash','Liability','Current Liability','Long-term Liability','Equity','Revenue','Cost of Revenue','Expense','Other Income','Other Expense','Tax','Receivable','Payable'];
            foreach ($types as $t): ?><option value="<?php echo $t; ?>"><?php echo $t; ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="xb-form-row">
          <label>Parent Account</label>
          <select name="parent_id" id="acct_parent" class="xb-select">
            <option value="">— None —</option>
            <?php foreach ($accounts ?? [] as $a): if (!$a->is_group) continue; ?>
            <option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->code . ' ' . $a->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="xb-form-row">
          <label><input type="checkbox" name="is_group" id="acct_is_group" value="1"> Group account (no transactions)</label>
        </div>
      </form>
    </div>
    <div class="xb-modal-footer">
      <button class="xb-btn xb-btn-secondary" onclick="document.getElementById('xb-account-modal').style.display='none'">Cancel</button>
      <button class="xb-btn xb-btn-primary" onclick="xbSaveAccount()">Save</button>
    </div>
  </div>
</div>

<script>
function xbFilterCOA() {
    const s = document.getElementById('coa_search').value.toLowerCase();
    const t = document.getElementById('coa_type').value;
    document.querySelectorAll('#coa-table tbody tr').forEach(row => {
        const matchSearch = !s || row.dataset.code.includes(s) || row.dataset.name.includes(s);
        const matchType   = !t || row.dataset.type === t;
        row.style.display = (matchSearch && matchType) ? '' : 'none';
    });
}

function xbOpenAccountModal(acct) {
    const form = document.getElementById('xb-account-form');
    form.reset();
    document.getElementById('xb-acct-modal-title').textContent = acct ? 'Edit Account' : 'New Account';
    if (acct) {
        document.getElementById('acct_id').value      = acct.id;
        document.getElementById('acct_code').value    = acct.code;
        document.getElementById('acct_name').value    = acct.name;
        document.getElementById('acct_type').value    = acct.type;
        document.getElementById('acct_parent').value  = acct.parent_id || '';
        document.getElementById('acct_is_group').checked = acct.is_group == '1';
    }
    document.getElementById('xb-account-modal').style.display = 'flex';
}

function xbSaveAccount() {
    const form = document.getElementById('xb-account-form');
    const data = Object.fromEntries(new FormData(form));
    data.is_group = form.querySelector('[name=is_group]').checked ? 1 : 0;
    fetch('<?php echo admin_url('xetuu_books/ajax/save_account'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
        if (res.success) window.location.reload();
        else alert(res.error || 'Save failed');
    });
}

function xbDeleteAccount(id, btn) {
    if (!confirm('Delete this account?')) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/delete_account'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(res => {
        if (res.success) btn.closest('tr').remove();
        else alert(res.error || 'Cannot delete');
    });
}
</script>
