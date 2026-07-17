<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.xb-rm-table td, .xb-rm-table th { vertical-align: middle !important; }
.xb-rm-badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }
.xb-rm-badge-button    { background:#eff6ff; color:#2563eb; }
.xb-rm-badge-suggest   { background:#f0fdf4; color:#16a34a; }
.xb-rm-badge-invoice   { background:#f5f3ff; color:#7c3aed; }
.xb-rm-inactive { opacity:.5; }

/* Slide-in drawer */
#rmDrawer { position:fixed; top:60px; right:-520px; width:500px; height:calc(100vh - 60px); background:#fff; box-shadow:-4px 0 24px rgba(0,0,0,.15); z-index:2000; transition:right .28s ease; display:flex; flex-direction:column; }
#rmDrawer.open { right:0; }
#rmDrawer .rm-drawer-head { padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
#rmDrawer .rm-drawer-head h4 { margin:0; font-size:16px; font-weight:700; color:#111827; }
#rmDrawer .rm-drawer-body { padding:24px; overflow-y:auto; flex:1; }
#rmDrawer .rm-drawer-foot { padding:16px 24px; border-top:1px solid #e5e7eb; display:flex; gap:8px; justify-content:flex-end; }
.rm-form-section { margin-bottom:18px; }
.rm-form-section label { display:block; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:5px; }
.rm-form-section .form-control, .rm-form-section select { width:100%; }
.rm-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.rm-toggle-active { display:flex; align-items:center; gap:8px; }

/* How-it-works explainer */
.rm-info-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:12.5px; color:#1e40af; line-height:1.6; }
.rm-info-box b { display:block; margin-bottom:4px; font-size:13px; }
</style>

<div style="padding:0 0 24px;">

    <!-- How it works -->
    <div class="rm-info-box">
        <b><i class="fa fa-info-circle"></i> What are Reconciliation Models?</b>
        Rules that auto-apply during <strong>bank reconciliation</strong> to suggest how unmatched statement lines should be posted.
        When a bank line matches a rule's criteria (amount range, label pattern, transaction direction), the system automatically proposes the configured write-off account —
        e.g., <em>"If label contains 'BANK FEE', post to Account 6000 Bank Charges"</em>.
    </div>

    <!-- Action bar -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
        <button class="btn btn-success btn-sm" onclick="rmOpenDrawer(null)" style="font-weight:600;">
            <i class="fa fa-plus"></i> New Model
        </button>
    </div>

    <!-- Table -->
    <div class="panel_s">
        <div class="panel-body" style="padding:0;">
            <table class="table table-hover xb-rpt xb-rm-table" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Matches</th>
                        <th>Write-off Account</th>
                        <th style="width:80px;">Active</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody id="rmTableBody">
                <?php if (empty($recon_models)): ?>
                    <tr id="rmEmptyRow">
                        <td colspan="7" class="text-center text-muted" style="padding:40px;">
                            No reconciliation models yet.
                            <a href="#" onclick="rmOpenDrawer(null);return false;" style="color:#2563eb;">Create your first rule &rarr;</a>
                        </td>
                    </tr>
                <?php else: foreach ($recon_models as $m):
                    $acc = null;
                    foreach ($gl_accounts as $a) { if ($a->id == $m->account_id) { $acc = $a; break; } }
                    $match_parts = [];
                    if ($m->match_nature !== 'both') $match_parts[] = ucfirst($m->match_nature) . ' only';
                    if ($m->match_amount_type !== 'any') {
                        $amt = $m->match_amount_type;
                        if ($amt === 'between') $amt = 'Between ' . number_format($m->match_amount_min, 2) . '–' . number_format($m->match_amount_max, 2);
                        elseif ($amt === 'is') $amt = '= ' . number_format($m->match_amount_min, 2);
                        else $amt = ucfirst($m->match_amount_type) . ' ' . number_format($m->match_amount_min ?? 0, 2);
                        $match_parts[] = 'Amount ' . $amt;
                    }
                    if ($m->match_label_type !== 'any' && $m->match_label_param) {
                        $match_parts[] = 'Label ' . $m->match_label_type . ' "' . htmlspecialchars($m->match_label_param) . '"';
                    }
                    $type_labels = ['writeoff_button' => ['Write-off Button', 'xb-rm-badge-button'],
                                    'writeoff_suggestion' => ['Auto Suggestion', 'xb-rm-badge-suggest'],
                                    'invoice_matching' => ['Invoice Match', 'xb-rm-badge-invoice']];
                    [$type_label, $type_cls] = $type_labels[$m->rule_type] ?? ['Unknown', ''];
                ?>
                    <tr id="rmRow<?php echo $m->id; ?>" class="<?php echo $m->active ? '' : 'xb-rm-inactive'; ?>">
                        <td style="color:#9ca3af;font-size:12px;"><?php echo $m->sequence; ?></td>
                        <td>
                            <a href="#" onclick="rmOpenDrawer(<?php echo $m->id; ?>);return false;" style="font-weight:600;color:#374151;">
                                <?php echo htmlspecialchars($m->name); ?>
                            </a>
                            <?php if ($m->writeoff_label): ?><div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($m->writeoff_label); ?></div><?php endif; ?>
                        </td>
                        <td><span class="xb-rm-badge <?php echo $type_cls; ?>"><?php echo $type_label; ?></span></td>
                        <td style="font-size:12px;color:#6b7280;"><?php echo $match_parts ? implode(', ', $match_parts) : '<span style="color:#d1d5db;">Any</span>'; ?></td>
                        <td style="font-size:13px;">
                            <?php if ($acc): ?>
                                <code style="font-size:11px;background:#f3f4f6;padding:1px 5px;border-radius:3px;"><?php echo $acc->code; ?></code>
                                <?php echo htmlspecialchars($acc->name); ?>
                            <?php else: echo '<span style="color:#d1d5db;">—</span>'; endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-xs <?php echo $m->active ? 'btn-success' : 'btn-default'; ?>"
                                    onclick="rmToggle(<?php echo $m->id; ?>, this)" title="Toggle active">
                                <?php echo $m->active ? 'On' : 'Off'; ?>
                            </button>
                        </td>
                        <td>
                            <button class="btn btn-default btn-xs" onclick="rmOpenDrawer(<?php echo $m->id; ?>)" title="Edit"><i class="fa fa-pencil"></i></button>
                            <button class="btn btn-danger btn-xs" onclick="rmDelete(<?php echo $m->id; ?>, '<?php echo addslashes($m->name); ?>')" title="Delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Slide-in Drawer ─────────────────────────────────────────────────── -->
<div id="rmDrawer">
    <div class="rm-drawer-head">
        <h4 id="rmDrawerTitle">New Reconciliation Model</h4>
        <button class="btn btn-link" onclick="rmCloseDrawer()" style="padding:0;font-size:18px;color:#6b7280;">&times;</button>
    </div>
    <div class="rm-drawer-body">
        <input type="hidden" id="rmId" value="">

        <div class="rm-form-section">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" id="rmName" class="form-control input-sm" placeholder="e.g. Bank Charges, Interest Income">
        </div>

        <div class="rm-row2">
            <div class="rm-form-section">
                <label>Rule Type</label>
                <select id="rmRuleType" class="form-control input-sm" onchange="rmUpdateTypeHint()">
                    <option value="writeoff_button">Write-off Button</option>
                    <option value="writeoff_suggestion">Auto Suggestion</option>
                    <option value="invoice_matching">Invoice Matching</option>
                </select>
            </div>
            <div class="rm-form-section">
                <label>Sequence (order)</label>
                <input type="number" id="rmSequence" class="form-control input-sm" value="10" min="1" max="999">
            </div>
        </div>

        <div id="rmTypeHint" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:8px 12px;font-size:12px;color:#166534;margin-bottom:16px;"></div>

        <hr style="margin:12px 0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;">Match Criteria</div>

        <div class="rm-row2">
            <div class="rm-form-section">
                <label>Transaction Direction</label>
                <select id="rmNature" class="form-control input-sm">
                    <option value="both">Both (Credit & Debit)</option>
                    <option value="credit">Credit only</option>
                    <option value="debit">Debit only</option>
                </select>
            </div>
            <div class="rm-form-section">
                <label>Amount Condition</label>
                <select id="rmAmountType" class="form-control input-sm" onchange="rmToggleAmountInputs()">
                    <option value="any">Any amount</option>
                    <option value="lower">Lower than</option>
                    <option value="greater">Greater than</option>
                    <option value="between">Between</option>
                    <option value="is">Is exactly</option>
                </select>
            </div>
        </div>

        <div id="rmAmountInputs" style="display:none;margin-bottom:12px;">
            <div class="rm-row2">
                <div class="rm-form-section">
                    <label id="rmAmountMinLabel">Amount</label>
                    <input type="number" id="rmAmountMin" class="form-control input-sm" step="0.01" placeholder="0.00">
                </div>
                <div class="rm-form-section" id="rmAmountMaxGroup" style="display:none;">
                    <label>Max Amount</label>
                    <input type="number" id="rmAmountMax" class="form-control input-sm" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="rm-row2">
            <div class="rm-form-section">
                <label>Label Condition</label>
                <select id="rmLabelType" class="form-control input-sm" onchange="rmToggleLabelInput()">
                    <option value="any">Any label</option>
                    <option value="contains">Contains</option>
                    <option value="is">Is exactly</option>
                    <option value="not_contains">Does not contain</option>
                    <option value="regex">Regex match</option>
                </select>
            </div>
            <div class="rm-form-section" id="rmLabelParamGroup" style="display:none;">
                <label>Label Text</label>
                <input type="text" id="rmLabelParam" class="form-control input-sm" placeholder="e.g. BANK FEE">
            </div>
        </div>

        <hr style="margin:12px 0;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;">Write-off Account</div>

        <div class="rm-form-section">
            <label>Counterpart Account</label>
            <select id="rmAccountId" class="form-control input-sm">
                <option value="">— Select account —</option>
                <?php foreach ($gl_accounts as $a): ?>
                <option value="<?php echo $a->id; ?>"><?php echo $a->code . ' — ' . htmlspecialchars($a->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="rm-row2">
            <div class="rm-form-section">
                <label>Journal (optional)</label>
                <select id="rmJournalId" class="form-control input-sm">
                    <option value="">— Any journal —</option>
                    <?php foreach ($journals as $j): ?>
                    <option value="<?php echo $j->id; ?>"><?php echo htmlspecialchars($j->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rm-form-section">
                <label>Write-off Label</label>
                <input type="text" id="rmWriteoffLabel" class="form-control input-sm" placeholder="e.g. Bank Charges">
            </div>
        </div>

        <div class="rm-form-section">
            <div class="rm-toggle-active">
                <input type="checkbox" id="rmActive" checked style="width:16px;height:16px;">
                <label for="rmActive" style="margin:0;font-size:13px;font-weight:500;text-transform:none;letter-spacing:0;color:#374151;">Active (rule is applied during reconciliation)</label>
            </div>
        </div>
    </div>
    <div class="rm-drawer-foot">
        <button class="btn btn-default btn-sm" onclick="rmCloseDrawer()">Cancel</button>
        <button class="btn btn-success btn-sm" onclick="rmSave()" id="rmSaveBtn" style="font-weight:600;">
            <i class="fa fa-check"></i> Save Model
        </button>
    </div>
</div>
<div id="rmOverlay" onclick="rmCloseDrawer()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:1999;"></div>

<!-- Model data for JS -->
<script>
var XB_RM_MODELS = <?php echo json_encode(array_map(function($m) {
    return [
        'id' => (int)$m->id,
        'name' => $m->name,
        'rule_type' => $m->rule_type,
        'sequence' => (int)$m->sequence,
        'active' => (bool)$m->active,
        'match_nature' => $m->match_nature,
        'match_amount_type' => $m->match_amount_type,
        'match_amount_min' => $m->match_amount_min,
        'match_amount_max' => $m->match_amount_max,
        'match_label_type' => $m->match_label_type,
        'match_label_param' => $m->match_label_param,
        'account_id' => $m->account_id,
        'journal_id' => $m->journal_id,
        'writeoff_label' => $m->writeoff_label,
    ];
}, $recon_models)); ?>;

var XB_RM_AJAX = '<?php echo admin_url("xetuu_books/ajax/"); ?>';
var XB_RM_CSRF = '<?php echo $this->security->get_csrf_token_name(); ?>';
var XB_RM_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';
</script>

<script>
var TYPE_HINTS = {
    'writeoff_button':    '<i class="fa fa-mouse-pointer"></i> <b>Write-off Button</b> — A button appears in the reconciliation wizard. When clicked, it creates a write-off line using the configured account.',
    'writeoff_suggestion':'<i class="fa fa-magic"></i> <b>Auto Suggestion</b> — The system automatically suggests a write-off if all match criteria are met. The user can accept or reject it.',
    'invoice_matching':   '<i class="fa fa-link"></i> <b>Invoice Matching</b> — Looks for open invoices/bills with a matching amount and/or reference to reconcile against the statement line.'
};

function rmUpdateTypeHint() {
    var t = document.getElementById('rmRuleType').value;
    document.getElementById('rmTypeHint').innerHTML = TYPE_HINTS[t] || '';
}

function rmToggleAmountInputs() {
    var t = document.getElementById('rmAmountType').value;
    var wrap = document.getElementById('rmAmountInputs');
    var maxGrp = document.getElementById('rmAmountMaxGroup');
    var minLabel = document.getElementById('rmAmountMinLabel');
    wrap.style.display = (t === 'any') ? 'none' : '';
    maxGrp.style.display = (t === 'between') ? '' : 'none';
    if (t === 'lower') minLabel.textContent = 'Max Amount';
    else if (t === 'greater') minLabel.textContent = 'Min Amount';
    else if (t === 'is') minLabel.textContent = 'Exact Amount';
    else minLabel.textContent = 'Amount';
}

function rmToggleLabelInput() {
    var t = document.getElementById('rmLabelType').value;
    document.getElementById('rmLabelParamGroup').style.display = (t === 'any') ? 'none' : '';
}

function rmOpenDrawer(id) {
    var m = id ? XB_RM_MODELS.find(function(x) { return x.id == id; }) : null;
    document.getElementById('rmDrawerTitle').textContent = m ? 'Edit: ' + m.name : 'New Reconciliation Model';
    document.getElementById('rmId').value = m ? m.id : '';
    document.getElementById('rmName').value = m ? m.name : '';
    document.getElementById('rmRuleType').value = m ? m.rule_type : 'writeoff_button';
    document.getElementById('rmSequence').value = m ? m.sequence : 10;
    document.getElementById('rmActive').checked = m ? m.active : true;
    document.getElementById('rmNature').value = m ? m.match_nature : 'both';
    document.getElementById('rmAmountType').value = m ? m.match_amount_type : 'any';
    document.getElementById('rmAmountMin').value = m && m.match_amount_min ? m.match_amount_min : '';
    document.getElementById('rmAmountMax').value = m && m.match_amount_max ? m.match_amount_max : '';
    document.getElementById('rmLabelType').value = m ? m.match_label_type : 'any';
    document.getElementById('rmLabelParam').value = m && m.match_label_param ? m.match_label_param : '';
    document.getElementById('rmAccountId').value = m && m.account_id ? m.account_id : '';
    document.getElementById('rmJournalId').value = m && m.journal_id ? m.journal_id : '';
    document.getElementById('rmWriteoffLabel').value = m && m.writeoff_label ? m.writeoff_label : '';
    rmUpdateTypeHint();
    rmToggleAmountInputs();
    rmToggleLabelInput();
    document.getElementById('rmDrawer').classList.add('open');
    document.getElementById('rmOverlay').style.display = '';
}

function rmCloseDrawer() {
    document.getElementById('rmDrawer').classList.remove('open');
    document.getElementById('rmOverlay').style.display = 'none';
}

function rmSave() {
    var name = document.getElementById('rmName').value.trim();
    if (!name) { alert('Name is required.'); return; }
    var btn = document.getElementById('rmSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    var data = {
        id:               document.getElementById('rmId').value,
        name:             name,
        rule_type:        document.getElementById('rmRuleType').value,
        sequence:         document.getElementById('rmSequence').value,
        active:           document.getElementById('rmActive').checked ? 1 : 0,
        match_nature:     document.getElementById('rmNature').value,
        match_amount_type:document.getElementById('rmAmountType').value,
        match_amount_min: document.getElementById('rmAmountMin').value,
        match_amount_max: document.getElementById('rmAmountMax').value,
        match_label_type: document.getElementById('rmLabelType').value,
        match_label_param:document.getElementById('rmLabelParam').value,
        account_id:       document.getElementById('rmAccountId').value,
        journal_id:       document.getElementById('rmJournalId').value,
        writeoff_label:   document.getElementById('rmWriteoffLabel').value,
    };
    data[XB_RM_CSRF] = XB_RM_HASH;

    fetch(XB_RM_AJAX + 'save_reconcil_model', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (res.success) { window.location.reload(); }
        else { alert(res.message || 'Save failed.'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Save Model'; }
    });
}

function rmDelete(id, name) {
    if (!confirm('Delete rule "' + name + '"? This cannot be undone.')) return;
    var data = { id: id };
    data[XB_RM_CSRF] = XB_RM_HASH;
    fetch(XB_RM_AJAX + 'delete_reconcil_model', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (res.success) {
            var row = document.getElementById('rmRow' + id);
            if (row) row.remove();
        }
    });
}

function rmToggle(id, btn) {
    var data = { id: id };
    data[XB_RM_CSRF] = XB_RM_HASH;
    fetch(XB_RM_AJAX + 'toggle_reconcil_model', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (res.success) {
            var row = document.getElementById('rmRow' + id);
            btn.textContent = res.active ? 'On' : 'Off';
            btn.className = 'btn btn-xs ' + (res.active ? 'btn-success' : 'btn-default');
            if (row) row.className = res.active ? '' : 'xb-rm-inactive';
            // Update local model data
            var m = XB_RM_MODELS.find(function(x) { return x.id == id; });
            if (m) m.active = res.active;
        }
    });
}

// Init hints on load
document.addEventListener('DOMContentLoaded', function() { rmUpdateTypeHint(); });
</script>
