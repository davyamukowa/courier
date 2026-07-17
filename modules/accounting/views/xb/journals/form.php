<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_new      = empty($entry);
$is_readonly = !$is_new && isset($entry) && $entry->state !== 'draft';
$form_action = admin_url('xetuu_books/journal_entry_form' . ($is_new ? '' : '/' . $entry->id));
?>

<form id="xb-je-form" method="post" action="<?php echo $form_action; ?>">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

  <div class="xb-status-bar">
    <div class="xb-status-steps">
      <?php if ($is_new): ?>
        <div class="xb-status-step active">Draft</div>
        <div class="xb-status-step">Posted</div>
        <div class="xb-status-step">Cancelled</div>
      <?php else: ?>
        <?php foreach (['draft' => 'Draft', 'posted' => 'Posted', 'cancel' => 'Cancelled'] as $s => $label): ?>
        <div class="xb-status-step <?php echo $entry->state === $s ? 'active' : ''; ?>"><?php echo $label; ?></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="xb-doc-actions">
      <?php if ($is_new || $entry->state === 'draft'): ?>
      <button class="xb-btn xb-btn-primary" type="submit">Save</button>
      <?php if (!$is_new): ?>
      <a href="<?php echo admin_url('xetuu_books/post_entry/' . $entry->id); ?>" class="xb-btn xb-btn-success" onclick="return confirm('Post this entry? Lines cannot be changed after posting.')">Post Entry</a>
      <a href="<?php echo admin_url('xetuu_books/delete_entry/' . $entry->id); ?>" class="xb-btn xb-btn-danger-outline" onclick="return confirm('Delete?')">Delete</a>
      <?php else: ?>
      <button type="submit" name="post_after_save" value="1" class="xb-btn xb-btn-success">Save &amp; Post</button>
      <?php endif; ?>
      <?php elseif ($entry->state === 'posted'): ?>
      <a href="<?php echo admin_url('xetuu_books/cancel_entry/' . $entry->id); ?>" class="xb-btn xb-btn-danger-outline" onclick="return confirm('Cancel? A reversal will be created.')">Cancel</a>
      <?php endif; ?>
      <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="xb-btn xb-btn-secondary">Close</a>
    </div>
  </div>

  <div class="xb-workspace">
    <div class="xb-workspace-main">
      <div class="xb-form" style="border:none; border-bottom-left-radius:0; border-bottom-right-radius:0; padding-bottom: 8px;">
        <div class="xb-form-cols">
          <div class="xb-form-col">
            <div class="xb-field">
              <label>Journal <span class="xb-req">*</span></label>
              <select name="journal_id" class="xb-select" <?php echo $is_readonly ? 'disabled' : ''; ?> required>
                <?php foreach ($all_journals ?? [] as $j): ?>
                <option value="<?php echo $j->id; ?>" <?php echo ($entry->journal_id ?? '') == $j->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($j->name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="xb-field">
              <label>Narration / Reference</label>
              <input type="text" name="narration" class="xb-input" value="<?php echo htmlspecialchars($entry->narration ?? ''); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?>>
            </div>
          </div>
          <div class="xb-form-col xb-form-col-right">
            <div class="xb-field">
              <label>Reference</label>
              <input type="text" class="xb-input xb-input-muted" value="<?php echo htmlspecialchars($entry->name ?? '(Auto on post)'); ?>" disabled>
            </div>
            <div class="xb-field">
              <label>Date <span class="xb-req">*</span></label>
              <input type="date" name="date" class="xb-input" value="<?php echo $entry->date ?? date('Y-m-d'); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?> required>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Tabs -->
      <div class="xb-tabs">
        <div class="xb-tab active" onclick="xbSwitchTab('lines')">Journal Items</div>
        <div class="xb-tab" onclick="xbSwitchTab('other')">Other Info</div>
      </div>
      
      <div id="tab-lines" class="xb-tab-content active" style="background:#fff; border:1px solid var(--xb-border); border-top:none; border-radius:0 0 var(--xb-radius) var(--xb-radius);">
        <table class="xb-lines-table" id="xb-je-lines-table" style="margin-bottom:20px;">
          <thead>
            <tr>
              <th style="width:25%">Account</th>
              <th style="width:20%">Partner</th>
              <th style="width:20%">Label</th>
              <th style="width:15%">Debit</th>
              <th style="width:15%">Credit</th>
              <?php if (!$is_readonly): ?><th style="width:5%"></th><?php endif; ?>
            </tr>
          </thead>
          <tbody id="xb-je-lines-body">
            <?php
            $je_lines = $entry_lines ?? [null, null];
            foreach ($je_lines as $i => $line):
            ?>
            <tr class="xb-je-line-row" data-idx="<?php echo $i; ?>">
              <td>
                <div style="position:relative">
                  <input type="text" class="xb-input xb-je-account-search" data-idx="<?php echo $i; ?>" placeholder="Account..."
                    value="<?php echo htmlspecialchars(($line->account_code ?? '') . (!empty($line->account_name) ? ' ' . $line->account_name : '')); ?>"
                    <?php echo $is_readonly ? 'readonly' : ''; ?>>
                  <input type="hidden" name="lines[<?php echo $i; ?>][account_id]" class="xb-je-account-id" value="<?php echo $line->account_id ?? ''; ?>">
                </div>
              </td>
              <td>
                <input type="text" name="lines[<?php echo $i; ?>][partner_label]" class="xb-input" placeholder="Partner (optional)"
                  value="<?php echo htmlspecialchars($line->partner_label ?? ''); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?>>
                <input type="hidden" name="lines[<?php echo $i; ?>][partner_id]" value="<?php echo $line->partner_id ?? ''; ?>">
              </td>
              <td><input type="text" name="lines[<?php echo $i; ?>][name]" class="xb-input" value="<?php echo htmlspecialchars($line->name ?? ''); ?>" placeholder="Label" <?php echo $is_readonly ? 'readonly' : ''; ?>></td>
              <td><input type="number" name="lines[<?php echo $i; ?>][debit]" class="xb-input xb-je-debit" value="<?php echo $line->debit ?? ''; ?>" step="0.01" min="0" <?php echo $is_readonly ? 'readonly' : ''; ?> onchange="xbJeBalance()"></td>
              <td><input type="number" name="lines[<?php echo $i; ?>][credit]" class="xb-input xb-je-credit" value="<?php echo $line->credit ?? ''; ?>" step="0.01" min="0" <?php echo $is_readonly ? 'readonly' : ''; ?> onchange="xbJeBalance()"></td>
              <?php if (!$is_readonly): ?><td><button type="button" class="xb-btn-icon xb-text-danger" onclick="xbJeRemoveLine(this)">&#10005;</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="xb-je-totals-row">
              <td colspan="3" class="xb-text-right"><strong>Totals</strong></td>
              <td id="xb-je-total-debit" class="xb-text-right"><strong>0.00</strong></td>
              <td id="xb-je-total-credit" class="xb-text-right"><strong>0.00</strong></td>
              <?php if (!$is_readonly): ?><td></td><?php endif; ?>
            </tr>
            <tr id="xb-je-diff-row" style="display:none">
              <td colspan="5"><span class="xb-text-danger" id="xb-je-diff-msg"></span></td>
              <?php if (!$is_readonly): ?><td></td><?php endif; ?>
            </tr>
          </tfoot>
        </table>
        <?php if (!$is_readonly): ?>
        <button type="button" class="xb-btn xb-btn-secondary xb-btn-sm" onclick="xbJeAddLine()">+ Add Line</button>
        <?php endif; ?>
      </div>
      
      <div id="tab-other" class="xb-tab-content" style="background:#fff; border:1px solid var(--xb-border); border-top:none; border-radius:0 0 var(--xb-radius) var(--xb-radius);">
         <p class="xb-muted">No additional information.</p>
      </div>

    </div>
    
    <div class="xb-workspace-sidebar">
      <div class="xb-panel">
        <div class="xb-panel-header">Journal Entry Summary</div>
        <div class="xb-panel-body">
          <div class="xb-panel-item"><span class="xb-panel-label">Total Debit</span><span class="xb-panel-value" id="side-debit">0.00</span></div>
          <div class="xb-panel-item"><span class="xb-panel-label">Total Credit</span><span class="xb-panel-value" id="side-credit">0.00</span></div>
          <div class="xb-panel-divider"></div>
          <div class="xb-panel-item"><span class="xb-panel-label">Status</span><span class="xb-panel-value" style="font-weight:700"><?php echo ucfirst($entry->state ?? 'draft'); ?></span></div>
        </div>
      </div>
      
      <div class="xb-panel">
        <div class="xb-panel-header">Workflow &amp; Activity</div>
        <div class="xb-panel-body">
          <div class="xb-muted">No recent activity.</div>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
// -- tab switching
function xbSwitchTab(tabId) {
    document.querySelectorAll('.xb-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.xb-tab-content').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

let xbJeIdx = <?php echo max(count($entry_lines ?? []), 2); ?>;

function xbJeAddLine() {
    const idx  = xbJeIdx++;
    const tbody = document.getElementById('xb-je-lines-body');
    const first = tbody.querySelector('.xb-je-line-row');
    const row   = first.cloneNode(true);
    row.dataset.idx = idx;
    row.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/lines\[\d+\]/, `lines[${idx}]`);
        if (el.tagName === 'INPUT') el.value = '';
    });
    row.querySelectorAll('.xb-je-account-search').forEach(el => { el.value = ''; el.dataset.idx = idx; });
    tbody.appendChild(row);
    xbBindJeAccountSearch(row.querySelector('.xb-je-account-search'));
    xbJeBalance();
}

function xbJeRemoveLine(btn) {
    const rows = document.querySelectorAll('.xb-je-line-row');
    if (rows.length <= 2) return;
    btn.closest('tr').remove();
    xbJeBalance();
}

function xbJeBalance() {
    let td = 0, tc = 0;
    document.querySelectorAll('.xb-je-line-row').forEach(row => {
        td += parseFloat(row.querySelector('.xb-je-debit').value) || 0;
        tc += parseFloat(row.querySelector('.xb-je-credit').value) || 0;
    });
    const formattedTd = td.toFixed(2);
    const formattedTc = tc.toFixed(2);
    document.getElementById('xb-je-total-debit').innerHTML  = `<strong>${formattedTd}</strong>`;
    document.getElementById('xb-je-total-credit').innerHTML = `<strong>${formattedTc}</strong>`;
    document.getElementById('side-debit').textContent = formattedTd;
    document.getElementById('side-credit').textContent = formattedTc;
    
    const diff = Math.abs(td - tc);
    const diffRow = document.getElementById('xb-je-diff-row');
    if (diff > 0.001) {
        diffRow.style.display = '';
        document.getElementById('xb-je-diff-msg').textContent = `Difference: ${diff.toFixed(2)} — entry must be balanced to post.`;
    } else {
        diffRow.style.display = 'none';
    }
}

function xbBindJeAccountSearch(input) {
    if (!input || input.readOnly) return;
    let timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        const td_cell = this.closest('td');
        if (q.length < 2) { td_cell.querySelector('.xb-autocomplete')?.remove(); return; }
        timer = setTimeout(() => {
            fetch(`<?php echo admin_url('xetuu_books/ajax/search_accounts'); ?>?q=${encodeURIComponent(q)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => r.json()).then(res => {
                    let box = td_cell.querySelector('.xb-autocomplete');
                    if (!box) { box = document.createElement('div'); box.className = 'xb-autocomplete'; td_cell.style.position = 'relative'; td_cell.appendChild(box); }
                    box.innerHTML = '';
                    (res.data || []).forEach(a => {
                        const item = document.createElement('div');
                        item.className = 'xb-suggestion-item';
                        item.textContent = a.code + ' ' + a.name;
                        item.addEventListener('click', () => {
                            input.closest('tr').querySelector('.xb-je-account-id').value = a.id;
                            input.value = a.code + ' ' + a.name;
                            box.innerHTML = '';
                        });
                        box.appendChild(item);
                    });
                });
        }, 250);
    });
}

document.querySelectorAll('.xb-je-account-search').forEach(xbBindJeAccountSearch);
xbJeBalance();
</script>
