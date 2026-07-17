<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_bill     = isset($move) && in_array($move->move_type, ['in_invoice', 'in_refund']);
$is_new      = empty($move);
$is_readonly = !$is_new && isset($move) && $move->state === 'posted';
$form_action = $is_bill
    ? admin_url('xetuu_books/bill_form' . ($is_new ? '' : '/' . $move->id))
    : admin_url('xetuu_books/invoice_form' . ($is_new ? '' : '/' . $move->id));
$post_url    = $is_bill
    ? admin_url('xetuu_books/post_bill/' . ($move->id ?? ''))
    : admin_url('xetuu_books/post_invoice/' . ($move->id ?? ''));
$cancel_url  = $is_bill ? admin_url('xetuu_books/bills') : admin_url('xetuu_books/invoices');
?>

<!-- Inline payment form (posted invoices) -->
<?php if (!$is_new && isset($move) && $move->state === 'posted' && $move->payment_state !== 'paid'): ?>
<div id="xb-inline-pay-form" class="xb-inline-pay" style="display:none">
  <h4 class="xb-inline-pay-title">Register Payment</h4>
  <form method="post" action="<?php echo admin_url('xetuu_books/register_payment'); ?>">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="move_id" value="<?php echo $move->id; ?>">
    <div class="xb-form-row-inline">
      <div class="xb-field">
        <label>Journal</label>
        <select name="journal_id" class="xb-select" required>
          <?php foreach ($payment_journals ?? [] as $j): ?>
          <option value="<?php echo $j->id; ?>"><?php echo htmlspecialchars($j->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="xb-field">
        <label>Amount</label>
        <input type="number" name="amount" value="<?php echo $move->amount_residual; ?>" step="0.01" class="xb-input" required>
      </div>
      <div class="xb-field">
        <label>Date</label>
        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="xb-input" required>
      </div>
      <div class="xb-field">
        <label>Memo</label>
        <input type="text" name="memo" class="xb-input" placeholder="Payment memo">
      </div>
      <div class="xb-field xb-field-btn">
        <button type="submit" class="xb-btn xb-btn-primary">Pay</button>
        <button type="button" class="xb-btn xb-btn-secondary" onclick="this.closest('.xb-inline-pay').style.display='none'">Cancel</button>
      </div>
    </div>
  </form>
</div>
<?php endif; ?>

<form id="xb-invoice-form" method="post" action="<?php echo $form_action; ?>">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
  <?php if (!$is_bill): ?><input type="hidden" name="move_type" value="out_invoice"><?php else: ?><input type="hidden" name="move_type" value="in_invoice"><?php endif; ?>

  <!-- Status bar -->
  <div class="xb-status-bar">
    <div class="xb-status-steps">
      <?php if ($is_new): ?>
        <div class="xb-status-step active">Draft</div>
        <div class="xb-status-step">Posted</div>
        <div class="xb-status-step">Paid</div>
      <?php else: ?>
        <?php foreach (['draft' => 'Draft', 'posted' => 'Posted', 'paid' => 'Paid'] as $s => $label): ?>
        <div class="xb-status-step <?php echo ($move->state === $s || ($s === 'paid' && $move->payment_state === 'paid')) ? 'active' : ''; ?>">
          <?php echo $label; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="xb-doc-actions">
      <?php if ($is_new || $move->state === 'draft'): ?>
        <button class="xb-btn xb-btn-primary" type="submit">Save</button>
        <?php if (!$is_new): ?>
        <a href="<?php echo $post_url; ?>" class="xb-btn xb-btn-success" onclick="return confirm('Post this entry? This cannot be undone.')">Confirm &amp; Post</a>
        <a href="<?php echo admin_url('xetuu_books/delete_' . ($is_bill ? 'bill' : 'invoice') . '/' . $move->id); ?>" class="xb-btn xb-btn-danger-outline" onclick="return confirm('Delete this draft?')">Delete</a>
        <?php else: ?>
        <button type="submit" name="post_after_save" value="1" class="xb-btn xb-btn-success">Save &amp; Post</button>
        <?php endif; ?>
      <?php elseif ($move->state === 'posted'): ?>
        <?php if ($move->payment_state !== 'paid'): ?>
        <button type="button" class="xb-btn xb-btn-success" onclick="document.getElementById('xb-inline-pay-form').style.display='block'">Register Payment</button>
        <?php endif; ?>
        <a href="<?php echo admin_url('xetuu_books/cancel_' . ($is_bill ? 'bill' : 'invoice') . '/' . $move->id); ?>" class="xb-btn xb-btn-danger-outline" onclick="return confirm('Cancel this entry? A reversal entry will be created.')">Cancel</a>
        <a href="<?php echo admin_url('xetuu_books/ajax/print_invoice?id=' . $move->id); ?>" class="xb-btn xb-btn-secondary" target="_blank">Print / PDF</a>
      <?php endif; ?>
      <a href="<?php echo $cancel_url; ?>" class="xb-btn xb-btn-secondary">Close</a>
    </div>
  </div>

  <div class="xb-workspace">
    <div class="xb-workspace-main">
      <div class="xb-form" style="border:none; border-bottom-left-radius:0; border-bottom-right-radius:0; padding-bottom: 8px;">
        <div class="xb-form-cols">
          <!-- Left column -->
          <div class="xb-form-col">
            <div class="xb-field">
              <label><?php echo $is_bill ? 'Vendor' : 'Customer'; ?> <span class="xb-req">*</span></label>
              <div class="xb-partner-search">
                <input type="text" id="partner_name_display" class="xb-input" placeholder="Search <?php echo $is_bill ? 'vendor' : 'customer'; ?>..."
                  value="<?php echo htmlspecialchars($move->partner_name ?? ''); ?>" <?php echo $is_readonly ? 'disabled' : ''; ?> autocomplete="off">
                <input type="hidden" name="partner_id" id="partner_id" value="<?php echo $move->partner_id ?? ''; ?>" required>
                <input type="hidden" name="partner_type" id="partner_type" value="<?php echo $is_bill ? 'vendor' : 'customer'; ?>">
                <div class="xb-autocomplete" id="partner-suggestions"></div>
              </div>
            </div>

            <div class="xb-field">
              <label><?php echo $is_bill ? 'Bill Reference' : 'Invoice Origin'; ?></label>
              <input type="text" name="invoice_origin" class="xb-input" value="<?php echo htmlspecialchars($move->invoice_origin ?? ''); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?>>
            </div>
          </div>

          <!-- Right column -->
          <div class="xb-form-col xb-form-col-right">
            <div class="xb-field">
              <label>Number</label>
              <input type="text" class="xb-input xb-input-muted" value="<?php echo htmlspecialchars($move->name ?? '(Auto-assigned on post)'); ?>" disabled>
            </div>
            <div class="xb-field">
              <label><?php echo $is_bill ? 'Bill Date' : 'Invoice Date'; ?> <span class="xb-req">*</span></label>
              <input type="date" name="date" class="xb-input" value="<?php echo $move->date ?? date('Y-m-d'); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?> required>
            </div>
            <div class="xb-field">
              <label>Due Date</label>
              <input type="date" name="invoice_date_due" class="xb-input" value="<?php echo $move->invoice_date_due ?? ''; ?>" <?php echo $is_readonly ? 'readonly' : ''; ?>>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="xb-tabs">
        <div class="xb-tab active" onclick="xbSwitchTab('lines')">Invoice Lines</div>
        <div class="xb-tab" onclick="xbSwitchTab('other')">Other Info</div>
      </div>
      
      <!-- Tab Content -->
      <div id="tab-lines" class="xb-tab-content active" style="background:#fff; border:1px solid var(--xb-border); border-top:none; border-radius:0 0 var(--xb-radius) var(--xb-radius);">
        <table class="xb-lines-table" id="xb-lines-table" style="margin-bottom: 20px;">
          <thead>
            <tr>
              <th style="width:35%">Description</th>
              <th style="width:12%">Account</th>
              <th style="width:8%">Qty</th>
              <th style="width:10%">Unit Price</th>
              <th style="width:10%">Tax</th>
              <th style="width:10%">Discount %</th>
              <th style="width:10%">Subtotal</th>
              <?php if (!$is_readonly): ?><th style="width:5%"></th><?php endif; ?>
            </tr>
          </thead>
          <tbody id="xb-lines-body">
            <?php
            $lines = $invoice_lines ?? [];
            if (empty($lines) && !$is_readonly) {
                $lines = [null]; // one empty row
            }
            foreach ($lines as $i => $line):
              $idx = $i;
            ?>
            <tr class="xb-line-row" data-idx="<?php echo $idx; ?>">
              <td><input type="text" name="lines[<?php echo $idx; ?>][name]" class="xb-input" value="<?php echo htmlspecialchars($line->name ?? ''); ?>" placeholder="Description" <?php echo $is_readonly ? 'readonly' : ''; ?>></td>
              <td>
                <input type="text" class="xb-input xb-account-search" data-idx="<?php echo $idx; ?>" placeholder="Account..."
                  value="<?php echo htmlspecialchars(($line->account_code ?? '') . (!empty($line->account_name) ? ' ' . $line->account_name : '')); ?>"
                  <?php echo $is_readonly ? 'readonly' : ''; ?>>
                <input type="hidden" name="lines[<?php echo $idx; ?>][account_id]" class="xb-account-id" value="<?php echo $line->account_id ?? ''; ?>">
              </td>
              <td><input type="number" name="lines[<?php echo $idx; ?>][quantity]" class="xb-input xb-line-qty" value="<?php echo $line->quantity ?? 1; ?>" step="0.001" min="0" <?php echo $is_readonly ? 'readonly' : ''; ?> onchange="xbRecomputeLine(this)"></td>
              <td><input type="number" name="lines[<?php echo $idx; ?>][price_unit]" class="xb-input xb-line-price" value="<?php echo $line->price_unit ?? ''; ?>" step="0.01" min="0" <?php echo $is_readonly ? 'readonly' : ''; ?> onchange="xbRecomputeLine(this)"></td>
              <td>
                <select name="lines[<?php echo $idx; ?>][tax_id]" class="xb-select xb-line-tax" <?php echo $is_readonly ? 'disabled' : ''; ?> onchange="xbRecomputeLine(this)">
                  <option value="">No Tax</option>
                  <?php foreach ($taxes ?? [] as $tax): ?>
                  <option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-incl="<?php echo $tax->price_include; ?>"
                    <?php echo ($line->tax_id ?? '') == $tax->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($tax->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="number" name="lines[<?php echo $idx; ?>][discount]" class="xb-input xb-line-discount" value="<?php echo $line->discount ?? 0; ?>" step="0.01" min="0" max="100" <?php echo $is_readonly ? 'readonly' : ''; ?> onchange="xbRecomputeLine(this)"></td>
              <td class="xb-line-subtotal"><?php echo app_format_money(($line->price_subtotal ?? 0), get_base_currency()); ?></td>
              <?php if (!$is_readonly): ?><td><button type="button" class="xb-btn-icon xb-text-danger" onclick="xbRemoveLine(this)">&#10005;</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$is_readonly): ?>
        <button type="button" class="xb-btn xb-btn-secondary xb-btn-sm" id="xb-add-line" onclick="xbAddLine()">+ Add Line</button>
        <?php endif; ?>

        <!-- Totals under lines -->
        <div class="xb-totals-block">
          <div class="xb-notes-block">
            <label>Notes</label>
            <textarea name="narration" class="xb-input" rows="3" <?php echo $is_readonly ? 'readonly' : ''; ?>><?php echo htmlspecialchars($move->narration ?? ''); ?></textarea>
          </div>
          <div class="xb-totals-grid" style="flex: 0 0 240px">
            <div class="xb-totals-row"><span>Untaxed Amount</span><span id="xb-total-untaxed"><?php echo app_format_money($move->amount_untaxed ?? 0, get_base_currency()); ?></span></div>
            <div id="xb-tax-lines"></div>
            <div class="xb-totals-row xb-totals-total"><span>Total</span><span id="xb-total-amount"><?php echo app_format_money($move->amount_total ?? 0, get_base_currency()); ?></span></div>
            <?php if (!$is_new && isset($move) && $move->state === 'posted'): ?>
            <div class="xb-totals-row"><span>Amount Due</span><span id="xb-total-due" class="xb-text-danger"><?php echo app_format_money($move->amount_residual ?? 0, get_base_currency()); ?></span></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div id="tab-other" class="xb-tab-content" style="background:#fff; border:1px solid var(--xb-border); border-top:none; border-radius:0 0 var(--xb-radius) var(--xb-radius);">
        <div class="xb-form-cols">
          <div class="xb-form-col">
            <div class="xb-field">
              <label>Payment Terms</label>
              <select name="invoice_payment_term_id" class="xb-select" <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <option value="">— None —</option>
                <?php foreach ($payment_terms ?? [] as $pt): ?>
                <option value="<?php echo $pt->id; ?>" <?php echo ($move->invoice_payment_term_id ?? '') == $pt->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($pt->name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="xb-field">
              <label>Journal</label>
              <select name="journal_id" class="xb-select" <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <?php foreach ($journals ?? [] as $j): ?>
                <option value="<?php echo $j->id; ?>" <?php echo ($move->journal_id ?? '') == $j->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($j->name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="xb-workspace-sidebar">
      <div class="xb-panel">
        <div class="xb-panel-header">Document Summary</div>
        <div class="xb-panel-body">
          <div class="xb-panel-item"><span class="xb-panel-label">Untaxed</span><span class="xb-panel-value" id="side-untaxed"><?php echo app_format_money($move->amount_untaxed ?? 0, get_base_currency()); ?></span></div>
          <div class="xb-panel-item"><span class="xb-panel-label">Tax</span><span class="xb-panel-value" id="side-tax"><?php echo app_format_money(($move->amount_total ?? 0) - ($move->amount_untaxed ?? 0), get_base_currency()); ?></span></div>
          <div class="xb-panel-divider"></div>
          <div class="xb-panel-item"><span class="xb-panel-label" style="color:#111;font-weight:700">Total</span><span class="xb-panel-value" style="font-size:16px;font-weight:800" id="side-total"><?php echo app_format_money($move->amount_total ?? 0, get_base_currency()); ?></span></div>
          <?php if (!$is_new && isset($move) && $move->state === 'posted'): ?>
          <div class="xb-panel-item"><span class="xb-panel-label">Due</span><span class="xb-panel-value" style="color:var(--xb-danger)"><?php echo app_format_money($move->amount_residual ?? 0, get_base_currency()); ?></span></div>
          <?php endif; ?>
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

// -- line item management
let xbLineIdx = <?php echo max(count($invoice_lines ?? []), 1); ?>;

function xbAddLine() {
    const idx = xbLineIdx++;
    const row = document.querySelector('.xb-line-row[data-idx="0"]').cloneNode(true);
    row.dataset.idx = idx;
    row.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/lines\[\d+\]/, `lines[${idx}]`);
        if (el.tagName === 'INPUT') el.value = el.type === 'number' ? (el.classList.contains('xb-line-qty') ? 1 : '') : '';
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    row.querySelectorAll('.xb-account-search').forEach(el => { el.value = ''; el.dataset.idx = idx; });
    row.querySelector('.xb-line-subtotal').textContent = '0.00';
    document.getElementById('xb-lines-body').appendChild(row);
    xbBindAccountSearch(row.querySelector('.xb-account-search'));
}

function xbRemoveLine(btn) {
    const rows = document.querySelectorAll('.xb-line-row');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    xbRecomputeTotals();
}

function xbRecomputeLine(el) {
    const row   = el.closest('tr');
    const qty   = parseFloat(row.querySelector('.xb-line-qty').value) || 0;
    const price = parseFloat(row.querySelector('.xb-line-price').value) || 0;
    const disc  = parseFloat(row.querySelector('.xb-line-discount').value) || 0;
    const tax   = row.querySelector('.xb-line-tax');
    const rate  = tax.selectedOptions[0]?.dataset.rate || 0;
    const incl  = tax.selectedOptions[0]?.dataset.incl == '1';
    let sub = qty * price * (1 - disc / 100);
    if (!incl && rate > 0) sub = sub; // subtotal before tax
    row.querySelector('.xb-line-subtotal').textContent = sub.toFixed(2);
    xbRecomputeTotals();
}

function xbRecomputeTotals() {
    let untaxed = 0, tax_total = 0;
    const taxMap = {};
    document.querySelectorAll('.xb-line-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.xb-line-qty').value) || 0;
        const price = parseFloat(row.querySelector('.xb-line-price').value) || 0;
        const disc  = parseFloat(row.querySelector('.xb-line-discount').value) || 0;
        const tax   = row.querySelector('.xb-line-tax');
        const rate  = parseFloat(tax.selectedOptions[0]?.dataset.rate || 0);
        const incl  = tax.selectedOptions[0]?.dataset.incl == '1';
        const taxNm = tax.selectedOptions[0]?.text || '';
        let sub = qty * price * (1 - disc / 100);
        if (incl && rate > 0) {
            const taxAmt = sub - sub / (1 + rate / 100);
            untaxed += sub - taxAmt;
            if (rate > 0) { taxMap[taxNm] = (taxMap[taxNm] || 0) + taxAmt; tax_total += taxAmt; }
        } else {
            untaxed += sub;
            if (rate > 0) { const t = sub * rate / 100; taxMap[taxNm] = (taxMap[taxNm] || 0) + t; tax_total += t; }
        }
    });
    
    const formattedUntaxed = untaxed.toFixed(2);
    const formattedTotal = (untaxed + tax_total).toFixed(2);
    const formattedTax = tax_total.toFixed(2);
    
    document.getElementById('xb-total-untaxed').textContent = formattedUntaxed;
    document.getElementById('xb-total-amount').textContent = formattedTotal;
    
    document.getElementById('side-untaxed').textContent = formattedUntaxed;
    document.getElementById('side-tax').textContent = formattedTax;
    document.getElementById('side-total').textContent = formattedTotal;
    
    let taxHtml = '';
    for (const [name, amt] of Object.entries(taxMap)) {
        taxHtml += `<div class="xb-totals-row"><span>${name}</span><span>${amt.toFixed(2)}</span></div>`;
    }
    document.getElementById('xb-tax-lines').innerHTML = taxHtml;
}

// -- partner autocomplete
function xbBindPartnerSearch() {
    const input = document.getElementById('partner_name_display');
    if (!input || input.disabled) return;
    let timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { document.getElementById('partner-suggestions').innerHTML = ''; return; }
        timer = setTimeout(() => {
            fetch(`<?php echo admin_url('xetuu_books/ajax/search_partners'); ?>?q=${encodeURIComponent(q)}&type=<?php echo $is_bill ? 'vendor' : 'customer'; ?>`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => r.json()).then(res => {
                    const box = document.getElementById('partner-suggestions');
                    box.innerHTML = '';
                    (res.data || []).forEach(p => {
                        const item = document.createElement('div');
                        item.className = 'xb-suggestion-item';
                        item.textContent = p.name;
                        item.addEventListener('click', () => {
                            document.getElementById('partner_id').value = p.id;
                            document.getElementById('partner_type').value = p.partner_type;
                            input.value = p.name;
                            box.innerHTML = '';
                        });
                        box.appendChild(item);
                    });
                });
        }, 250);
    });
}

// -- account autocomplete
function xbBindAccountSearch(input) {
    if (!input || input.readOnly) return;
    let timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        const row = this.closest('tr');
        if (q.length < 2) { row.querySelector('.xb-account-suggestions')?.remove(); return; }
        timer = setTimeout(() => {
            fetch(`<?php echo admin_url('xetuu_books/ajax/search_accounts'); ?>?q=${encodeURIComponent(q)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => r.json()).then(res => {
                    let box = row.querySelector('.xb-account-suggestions');
                    if (!box) { box = document.createElement('div'); box.className = 'xb-autocomplete xb-account-suggestions'; input.parentNode.style.position = 'relative'; input.parentNode.appendChild(box); }
                    box.innerHTML = '';
                    (res.data || []).forEach(a => {
                        const item = document.createElement('div');
                        item.className = 'xb-suggestion-item';
                        item.textContent = a.code + ' ' + a.name;
                        item.addEventListener('click', () => {
                            row.querySelector('.xb-account-id').value = a.id;
                            input.value = a.code + ' ' + a.name;
                            box.innerHTML = '';
                        });
                        box.appendChild(item);
                    });
                });
        }, 250);
    });
}

document.querySelectorAll('.xb-account-search').forEach(xbBindAccountSearch);
xbBindPartnerSearch();
xbRecomputeTotals();
</script>
