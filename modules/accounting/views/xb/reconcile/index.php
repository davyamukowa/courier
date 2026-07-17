<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-reconcile">
  <div class="xb-reconcile-filters">
    <div class="xb-field">
      <label>Bank / Cash Journal</label>
      <select id="rec_journal" class="xb-select" onchange="xbLoadStatement()">
        <option value="">— Select Journal —</option>
        <?php foreach ($bank_journals ?? [] as $j): ?>
        <option value="<?php echo $j->id; ?>" <?php echo ($selected_journal ?? '') == $j->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($j->name); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="xb-field">
      <label>Statement End Date</label>
      <input type="date" id="rec_date" class="xb-input" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <button class="xb-btn xb-btn-secondary" onclick="xbLoadStatement()">Load</button>
    <label class="xb-btn xb-btn-secondary" style="cursor:pointer">
      Import CSV
      <input type="file" id="rec_csv" accept=".csv" style="display:none" onchange="xbImportCSV(this)">
    </label>
  </div>

  <div class="xb-reconcile-body" id="rec-body">
    <!-- Loaded dynamically -->
    <?php if (!empty($unreconciled)): ?>
    <div class="xb-reconcile-columns">
      <div class="xb-rec-col">
        <h4>Bank Statements <span class="xb-badge xb-badge-secondary"><?php echo count($statement_lines ?? []); ?></span></h4>
        <table class="xb-table xb-table-compact">
          <thead><tr><th></th><th>Date</th><th>Description</th><th>Amount</th></tr></thead>
          <tbody id="rec-statement-lines">
            <?php foreach ($statement_lines ?? [] as $sl): ?>
            <tr data-id="<?php echo $sl->id; ?>" data-amount="<?php echo $sl->amount; ?>">
              <td><input type="checkbox" class="rec-stmt-chk" value="<?php echo $sl->id; ?>"></td>
              <td><?php echo _d($sl->date); ?></td>
              <td><?php echo htmlspecialchars(mb_strimwidth($sl->name, 0, 50, '...')); ?></td>
              <td class="<?php echo $sl->amount >= 0 ? 'xb-text-success' : 'xb-text-danger'; ?>"><?php echo app_format_money($sl->amount, get_base_currency()); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="xb-rec-col">
        <h4>Journal Items <span class="xb-badge xb-badge-secondary"><?php echo count($unreconciled ?? []); ?></span></h4>
        <table class="xb-table xb-table-compact">
          <thead><tr><th></th><th>Date</th><th>Reference</th><th>Debit</th><th>Credit</th></tr></thead>
          <tbody id="rec-journal-lines">
            <?php foreach ($unreconciled ?? [] as $ul): ?>
            <tr data-id="<?php echo $ul->id; ?>" data-debit="<?php echo $ul->debit; ?>" data-credit="<?php echo $ul->credit; ?>">
              <td><input type="checkbox" class="rec-jnl-chk" value="<?php echo $ul->id; ?>"></td>
              <td><?php echo _d($ul->move_date); ?></td>
              <td><?php echo htmlspecialchars($ul->move_name ?: $ul->move_ref ?: ''); ?></td>
              <td><?php echo $ul->debit > 0 ? app_format_money($ul->debit, get_base_currency()) : '—'; ?></td>
              <td><?php echo $ul->credit > 0 ? app_format_money($ul->credit, get_base_currency()) : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="xb-rec-actions">
      <div class="xb-rec-diff">
        Difference: <span id="rec-diff">0.00</span>
      </div>
      <button class="xb-btn xb-btn-primary" onclick="xbReconcileSelected()">Reconcile Selected</button>
      <button class="xb-btn xb-btn-secondary" onclick="xbAutoReconcile()">Auto-Reconcile All</button>
    </div>
    <?php else: ?>
    <div class="xb-empty-state">
      <p>Select a journal above to load unreconciled items.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function xbLoadStatement() {
    const jid  = document.getElementById('rec_journal').value;
    const date = document.getElementById('rec_date').value;
    if (!jid) return;
    window.location.href = `<?php echo admin_url('xetuu_books/reconcile'); ?>?journal_id=${jid}&date_to=${date}`;
}

function xbImportCSV(input) {
    const jid = document.getElementById('rec_journal').value;
    if (!jid) { alert('Select a journal first.'); return; }
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('journal_id', jid);
    fd.append(<?php echo json_encode($this->security->get_csrf_token_name()); ?>, <?php echo json_encode($this->security->get_csrf_hash()); ?>);
    fetch('<?php echo admin_url('xetuu_books/import_statement'); ?>', {method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.json()).then(res => { if (res.success) window.location.reload(); else alert(res.error); });
}

function xbReconcileSelected() {
    const stmtIds = [...document.querySelectorAll('.rec-stmt-chk:checked')].map(c => c.value);
    const jnlIds  = [...document.querySelectorAll('.rec-jnl-chk:checked')].map(c => c.value);
    if (!stmtIds.length && !jnlIds.length) { alert('Select items to reconcile.'); return; }
    fetch('<?php echo admin_url('xetuu_books/do_reconcile'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({statement_ids: stmtIds, line_ids: jnlIds})
    }).then(r => r.json()).then(res => { if (res.success) window.location.reload(); else alert(res.error); });
}

function xbAutoReconcile() {
    const jid = document.getElementById('rec_journal').value;
    if (!jid) return;
    fetch('<?php echo admin_url('xetuu_books/ajax/auto_reconcile'); ?>', {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({journal_id: jid})
    }).then(r => r.json()).then(res => { alert(res.message || 'Done'); window.location.reload(); });
}

// Live diff
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('rec-stmt-chk') || e.target.classList.contains('rec-jnl-chk')) {
        let stmtTotal = 0, jnlTotal = 0;
        document.querySelectorAll('.rec-stmt-chk:checked').forEach(c => { stmtTotal += parseFloat(c.closest('tr').dataset.amount) || 0; });
        document.querySelectorAll('.rec-jnl-chk:checked').forEach(c => { jnlTotal += (parseFloat(c.closest('tr').dataset.debit) || 0) - (parseFloat(c.closest('tr').dataset.credit) || 0); });
        const diff = Math.abs(stmtTotal - jnlTotal);
        const el = document.getElementById('rec-diff');
        el.textContent = diff.toFixed(2);
        el.className = diff < 0.01 ? 'xb-text-success' : 'xb-text-danger';
    }
});
</script>
