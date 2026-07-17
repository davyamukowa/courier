<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-list-header">
  <button class="xb-btn xb-btn-primary" onclick="xbOpenJournalModal()">+ New Journal</button>
</div>

<table class="xb-table">
  <thead>
    <tr><th>Name</th><th>Code</th><th>Type</th><th>Account</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($journals ?? [] as $j): ?>
    <tr>
      <td><?php echo htmlspecialchars($j->name); ?></td>
      <td><code><?php echo htmlspecialchars($j->code); ?></code></td>
      <td><?php echo ucfirst($j->type); ?></td>
      <td class="xb-muted"><?php echo $j->account_code ? htmlspecialchars($j->account_code . ' ' . $j->account_name) : '—'; ?></td>
      <td class="xb-actions">
        <button class="xb-btn-icon" onclick="xbOpenJournalModal(<?php echo htmlspecialchars(json_encode($j)); ?>)">&#9998;</button>
        <button class="xb-btn-icon xb-text-danger" onclick="xbDeleteJournal(<?php echo $j->id; ?>, this)">&#10005;</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div id="xb-journal-modal" class="xb-modal" style="display:none">
  <div class="xb-modal-dialog">
    <div class="xb-modal-header">
      <h4 id="xb-jnl-title">New Journal</h4>
      <button class="xb-modal-close" onclick="document.getElementById('xb-journal-modal').style.display='none'">&times;</button>
    </div>
    <div class="xb-modal-body">
      <form id="xb-journal-form">
        <input type="hidden" name="id" id="jnl_id">
        <div class="xb-form-row"><label>Name <span class="xb-req">*</span></label><input type="text" name="name" id="jnl_name" class="xb-input" required></div>
        <div class="xb-form-row"><label>Short Code <span class="xb-req">*</span></label><input type="text" name="code" id="jnl_code" class="xb-input" maxlength="10" required></div>
        <div class="xb-form-row">
          <label>Type</label>
          <select name="type" id="jnl_type" class="xb-select">
            <option value="sale">Sales</option>
            <option value="purchase">Purchase</option>
            <option value="bank">Bank</option>
            <option value="cash">Cash</option>
            <option value="general">Miscellaneous</option>
          </select>
        </div>
        <div class="xb-form-row">
          <label>Default Account</label>
          <select name="account_id" id="jnl_acct" class="xb-select">
            <option value="">— None —</option>
            <?php foreach ($gl_accounts ?? [] as $a): ?>
            <option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->code . ' ' . $a->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>
    <div class="xb-modal-footer">
      <button class="xb-btn xb-btn-secondary" onclick="document.getElementById('xb-journal-modal').style.display='none'">Cancel</button>
      <button class="xb-btn xb-btn-primary" onclick="xbSaveJournal()">Save</button>
    </div>
  </div>
</div>

<script>
function xbOpenJournalModal(j) {
    document.getElementById('xb-journal-form').reset();
    document.getElementById('xb-jnl-title').textContent = j ? 'Edit Journal' : 'New Journal';
    if (j) {
        document.getElementById('jnl_id').value   = j.id;
        document.getElementById('jnl_name').value = j.name;
        document.getElementById('jnl_code').value = j.code;
        document.getElementById('jnl_type').value = j.type;
        document.getElementById('jnl_acct').value = j.account_id || '';
    }
    document.getElementById('xb-journal-modal').style.display = 'flex';
}
function xbSaveJournal() {
    const data = Object.fromEntries(new FormData(document.getElementById('xb-journal-form')));
    fetch('<?php echo admin_url('xetuu_books/ajax/save_journal'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => { if (res.success) window.location.reload(); else alert(res.error); });
}
function xbDeleteJournal(id, btn) {
    if (!confirm('Delete journal?')) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/delete_journal'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(res => { if (res.success) btn.closest('tr').remove(); else alert(res.error); });
}
</script>
