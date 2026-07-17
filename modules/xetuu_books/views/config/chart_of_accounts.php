<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ── COA tree ───────────────────────────────────────────────────────────────── */
#coa-tree { font-size: 13px; }

.coa-row { border-bottom: 1px solid #f0f0f0; }
.coa-row:last-child { border-bottom: none; }

.coa-row-inner {
    display: flex;
    align-items: center;
    padding: 5px 12px;
    min-height: 32px;
    cursor: default;
    gap: 4px;
}
.coa-row-inner:hover { background: #f6f8ff; }
.coa-row-inner:hover .coa-actions { opacity: 1; pointer-events: auto; }

.coa-toggle {
    width: 18px;
    flex-shrink: 0;
    cursor: pointer;
    color: #888;
    text-align: center;
    font-size: 10px;
}
.coa-toggle:hover { color: #333; }
.coa-toggle-empty { width: 18px; flex-shrink: 0; }

.coa-icon { font-size: 13px; flex-shrink: 0; width: 16px; text-align: center; }
.coa-group-icon { color: #f59e0b; }
.coa-leaf-icon  { color: #9ca3af; font-size: 8px; }

/* Name — fills remaining space, truncates if needed */
.coa-name { flex: 1; min-width: 0; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.coa-group-name { font-weight: 600; color: #1f2937; }
.coa-leaf-name  { color: #374151; }

.coa-code {
    font-family: monospace;
    font-size: 11px;
    color: #9ca3af;
    margin-right: 5px;
}

.coa-badge {
    font-size: 10px;
    padding: 1px 5px;
    border-radius: 3px;
    margin-left: 5px;
    background: #e0e7ff;
    color: #4338ca;
    vertical-align: middle;
    font-weight: 500;
}

/* Balance — fixed-width center column so it lands at a consistent horizontal position */
.coa-balance {
    flex: 0 0 160px;
    text-align: center;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    padding: 0 8px;
}
.coa-bal-dr   { color: #16a34a; }   /* green — debit (positive)  */
.coa-bal-cr   { color: #dc2626; }   /* red   — credit (negative) */
.coa-bal-zero { color: #9ca3af; }
.coa-bal-group { font-weight: 700; }

/* Action buttons — hidden until hover, fixed-width slot so balance never jumps */
.coa-actions {
    flex: 0 0 210px;
    text-align: right;
    opacity: 0;
    pointer-events: none;
    transition: opacity .1s;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0;
    white-space: nowrap;
}

.coa-act {
    font-size: 11px;
    color: #4f46e5;
    cursor: pointer;
    padding: 1px 6px;
    border-radius: 3px;
    background: none;
    border: none;
    text-decoration: none;
    display: inline-block;
}
.coa-act:hover { background: #e0e7ff; color: #3730a3; }
.coa-act.danger { color: #dc2626; }
.coa-act.danger:hover { background: #fee2e2; color: #b91c1c; }
.coa-act-sep { color: #d1d5db; font-size: 11px; }

/* ── Group toggle pill ──────────────────────────────────────────────────────── */
.xb-toggle-pill {
    display: inline-flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 3px 14px 3px 10px;
    cursor: pointer;
    user-select: none;
    font-size: 12px;
    color: #888;
    transition: all .15s;
    background: #fff;
    gap: 6px;
}
.xb-toggle-pill.on { border-color: #5c6bc0; background: #eef2ff; color: #3949ab; }
.xb-toggle-pill .pill-dot {
    width: 16px; height: 16px;
    border-radius: 50%; background: #ccc;
    transition: background .15s; flex-shrink: 0;
}
.xb-toggle-pill.on .pill-dot { background: #5c6bc0; }

/* ── Ledger modal table ─────────────────────────────────────────────────────── */
#ledgerModalBody .table { font-size: 12px; margin-bottom: 0; }
#ledgerModalBody .table th { background: #f8fafc; white-space: nowrap; }
#ledgerModalBody code { font-size: 11px; color: #6366f1; }
</style>

<!-- JS data injected before card markup so variables are defined -->
<script>
var allAccounts    = <?php echo json_encode(array_values($accounts)); ?>;
var currencySymbol = <?php echo json_encode($currency_symbol ?? 'Sh'); ?>;
var currentView    = (localStorage && localStorage.getItem('xb_coa_view')) || 'tree';
</script>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center" style="flex-wrap:wrap; gap:8px;">
                <span>Chart of Accounts</span>
                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-default" id="btn-tree-view" onclick="setView('tree')">
                            <i class="fa fa-sitemap"></i> Tree
                        </button>
                        <button type="button" class="btn btn-default" id="btn-list-view" onclick="setView('list')">
                            <i class="fa fa-list"></i> List
                        </button>
                    </div>
                    <button class="btn btn-default btn-sm" onclick="loadDefaultCoa()" title="Seed the default chart of accounts">
                        <i class="fa fa-database"></i> Load Defaults
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="removeLegacyCoa()" title="Remove old flat 4-digit accounts (1000–6999) that have no transactions">
                        <i class="fa fa-trash"></i> Remove Legacy
                    </button>
                    <button class="btn btn-primary xb-btn-primary btn-sm" data-toggle="modal" data-target="#accountModal" onclick="resetAccountForm()">
                        <i class="fa fa-plus"></i> New Account
                    </button>
                </div>
            </div>

            <div class="xb-card-body" style="padding:0; overflow:hidden;">

                <!-- ── Tree view ────────────────────────────────────────── -->
                <div id="tree-view-container">
                    <div id="coa-tree" style="padding:8px 0;"></div>
                </div>

                <!-- ── List view ────────────────────────────────────────── -->
                <div id="list-view-container" style="display:none; padding:12px;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Group</th>
                                    <th style="text-align:right; white-space:nowrap;">Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $acc):
                                    $bal   = (float)$acc->total_debit - (float)$acc->total_credit;
                                    $isDr  = $bal > 0;
                                    $isZ   = $bal == 0;
                                    $bCls  = $isZ ? 'text-muted' : ($isDr ? 'text-success' : 'text-danger');
                                    $bSide = $isDr ? 'Dr' : 'Cr';
                                    $bAmt  = number_format(abs($bal), 2);
                                    $sym   = htmlspecialchars($currency_symbol ?? 'Sh');
                                ?>
                                <tr>
                                    <td style="white-space:nowrap;">
                                        <a href="#" onclick="viewLedger(<?php echo (int)$acc->id; ?>); return false;" title="View ledger">
                                            <code><?php echo htmlspecialchars((string)$acc->code); ?></code>
                                        </a>
                                    </td>
                                    <td><?php
                                        if ($acc->is_group) echo '<strong>';
                                        echo htmlspecialchars($acc->name);
                                        if ($acc->is_group) echo '</strong>';
                                    ?></td>
                                    <td><?php echo htmlspecialchars($acc->type ?? ''); ?></td>
                                    <td><?php echo $acc->is_group ? '<span class="label label-info">Group</span>' : ''; ?></td>
                                    <td style="white-space:nowrap;">
                                        <span class="<?php echo $bCls; ?>" style="font-size:12px; font-weight:600;">
                                            <?php echo $sym; ?> <?php echo $bAmt; ?> <?php echo $bSide; ?>
                                        </span>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <button class="btn btn-default btn-icon btn-xs" onclick="editAccount(<?php echo (int)$acc->id; ?>)" title="Edit"><i class="fa fa-pencil"></i></button>
                                        <button class="btn btn-default btn-icon btn-xs" onclick="viewLedger(<?php echo (int)$acc->id; ?>)" title="View Ledger"><i class="fa fa-book"></i></button>
                                        <button class="btn btn-danger btn-icon btn-xs" onclick="deleteAccount(<?php echo (int)$acc->id; ?>)" title="Delete"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ── New / Edit Account Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="accountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="accountModalTitle">New Account</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="acc_id">

                <div class="form-group">
                    <label>New Account Name <span class="text-danger">*</span></label>
                    <input type="text" id="f_name" class="form-control"
                           placeholder="Note: Please don't create accounts for Customers and Suppliers">
                </div>

                <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" id="f_code" class="form-control" placeholder="e.g. 11110">
                    <small class="text-muted">Number of new Account — will be shown as a prefix in the account name.</small>
                </div>

                <div class="form-group">
                    <label>Parent Account</label>
                    <select id="f_parent_id" class="form-control selectpicker" data-live-search="true">
                        <option value="">— None (Root Account) —</option>
                        <?php foreach($accounts as $acc): ?>
                            <?php if($acc->is_group): ?>
                            <option value="<?php echo (int)$acc->id; ?>">
                                <?php echo htmlspecialchars(($acc->code ? $acc->code . ' - ' : '') . $acc->name); ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Is Group</label>
                    <div style="margin-top:6px;">
                        <div class="xb-toggle-pill" id="group-pill" onclick="toggleGroup()">
                            <span class="pill-dot"></span>
                            <span id="group-pill-label">No</span>
                        </div>
                    </div>
                    <small class="text-muted">Further accounts can be made under Groups, but entries can be made against non-Groups.</small>
                    <input type="hidden" id="f_is_group" value="0">
                </div>

                <div class="form-group">
                    <label>Account Type</label>
                    <select id="f_type" class="form-control selectpicker">
                        <option value="">— Optional —</option>
                        <?php foreach($account_types as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Optional. Used to filter in various transactions.</small>
                </div>

                <div class="form-group">
                    <label>Account Category</label>
                    <input type="text" id="f_account_category" class="form-control" placeholder="Optional. Used with Financial Report Template.">
                </div>

                <div class="form-group">
                    <label>Currency</label>
                    <select id="f_currency_id" class="form-control selectpicker" data-live-search="true">
                        <option value="">— Optional —</option>
                        <?php foreach($currencies as $cur): ?>
                            <option value="<?php echo (int)$cur->id; ?>">
                                <?php echo htmlspecialchars($cur->full_name . ' (' . $cur->name . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Company</label>
                    <input type="text" id="f_company" class="form-control"
                           value="<?php echo htmlspecialchars($company_name ?? ''); ?>">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary xb-btn-primary" onclick="saveAccount()">
                    <i class="fa fa-save"></i> Save Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Ledger Modal ──────────────────────────────────────────────────────────── -->
<div class="modal fade" id="ledgerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-book" style="color:#4f46e5; margin-right:6px;"></i>
                    <span id="ledgerModalTitle">General Ledger</span>
                </h4>
            </div>
            <div class="modal-body" id="ledgerModalBody" style="padding:16px;">
                <div class="text-center" style="padding:30px;">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Account map (id → account) ─────────────────────────────────────────────
var accountMap = {};
allAccounts.forEach(function(a) { accountMap[a.id] = a; });

// ── Number formatter ──────────────────────────────────────────────────────
function fmtNum(n) {
    return parseFloat(n || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// ── HTML escape ───────────────────────────────────────────────────────────
function esc(s) {
    if (s == null) return '';
    return String(s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ── Tree builder — wrapper nodes preserve original objects ────────────────
function buildTree(accounts) {
    var nodes = {}, roots = [];
    accounts.forEach(function(a) { nodes[a.id] = { acc: a, children: [], balance: 0 }; });
    accounts.forEach(function(a) {
        if (a.parent_id && nodes[a.parent_id]) {
            nodes[a.parent_id].children.push(nodes[a.id]);
        } else {
            roots.push(nodes[a.id]);
        }
    });
    function sort(list) {
        list.sort(function(a, b) {
            var ca = a.acc.code || '', cb = b.acc.code || '';
            if (ca && cb) return ca.localeCompare(cb, undefined, {numeric: true});
            return a.acc.name.localeCompare(b.acc.name);
        });
        list.forEach(function(n) { if (n.children.length) sort(n.children); });
    }
    sort(roots);
    return roots;
}

// ── Compute balances recursively ──────────────────────────────────────────
function computeBalances(tnode) {
    if (tnode.children.length === 0) {
        tnode.balance = parseFloat(tnode.acc.total_debit || 0) - parseFloat(tnode.acc.total_credit || 0);
    } else {
        var sum = 0;
        tnode.children.forEach(function(child) { computeBalances(child); sum += child.balance; });
        tnode.balance = sum;
    }
}

// ── Format balance as "Sh 1,234.56 Dr" ───────────────────────────────────
function fmtBal(balance, isGroup) {
    var isDr = balance > 0;
    var isZ  = balance === 0;
    var cls  = 'coa-balance ';
    cls += isZ ? 'coa-bal-zero' : (isDr ? 'coa-bal-dr' : 'coa-bal-cr');  /* green/red */
    if (isGroup) cls += ' coa-bal-group';
    var side = isDr ? 'Dr' : 'Cr';
    return '<span class="' + cls + '">' + esc(currencySymbol) + '&nbsp;' + fmtNum(Math.abs(balance)) + '&nbsp;' + side + '</span>';
}

// ── Recursive renderer ────────────────────────────────────────────────────
function renderNodes(tnodes, depth) {
    var html = '';
    depth = depth || 0;
    var indent = 12 + depth * 22;

    tnodes.forEach(function(tnode) {
        var node    = tnode.acc;
        var hasKids = tnode.children.length > 0;
        var isGroup = parseInt(node.is_group) === 1;
        var nid     = node.id;
        var balance = tnode.balance;

        html += '<div class="coa-row" id="cn-' + nid + '">';
        html += '<div class="coa-row-inner" style="padding-left:' + indent + 'px;">';

        // Expand/collapse toggle
        if (hasKids) {
            html += '<span class="coa-toggle" onclick="toggleNode(' + nid + ')" title="Expand/Collapse">'
                  + '<i class="fa fa-chevron-down"></i></span>';
        } else {
            html += '<span class="coa-toggle-empty"></span>';
        }

        // Folder / leaf icon
        if (isGroup) {
            html += '<span class="coa-icon coa-group-icon"><i class="fa fa-folder' + (hasKids ? '-open' : '') + '"></i></span>';
        } else {
            html += '<span class="coa-icon coa-leaf-icon"><i class="fa fa-circle"></i></span>';
        }

        // Account name + code + badge
        html += '<span class="coa-name ' + (isGroup ? 'coa-group-name' : 'coa-leaf-name') + '">';
        if (node.code) html += '<span class="coa-code">' + esc(node.code) + '</span>';
        html += esc(node.name);
        if (isGroup) html += '<span class="coa-badge">Group</span>';
        html += '</span>';

        // Balance — fixed 160px column, always centred between name and actions
        html += fmtBal(balance, isGroup);

        // Hover action buttons — fixed 210px slot on the right (keeps balance position stable)
        html += '<span class="coa-actions">';
        html += '<a class="coa-act" onclick="editAccount(' + nid + ')">Edit</a>';
        html += '<span class="coa-act-sep">|</span>';
        if (isGroup) {
            html += '<a class="coa-act" onclick="addChildTo(' + nid + ')">Add Child</a>';
            html += '<span class="coa-act-sep">|</span>';
        }
        html += '<a class="coa-act" onclick="viewLedger(' + nid + ')">View Ledger</a>';
        html += '<span class="coa-act-sep">|</span>';
        html += '<a class="coa-act danger" onclick="deleteAccount(' + nid + ')">Delete</a>';
        html += '</span>';

        html += '</div></div>';

        if (hasKids) {
            html += '<div class="coa-children" id="ck-' + nid + '">';
            html += renderNodes(tnode.children, depth + 1);
            html += '</div>';
        }
    });

    return html;
}

function renderCoaTree() {
    var tree = buildTree(allAccounts);
    tree.forEach(function(tnode) { computeBalances(tnode); });
    var html = renderNodes(tree, 0);
    if (!html) {
        html = '<p class="text-muted text-center" style="padding:40px 20px;">'
             + 'No accounts yet. '
             + '<a href="#" onclick="loadDefaultCoa(); return false;">Load Default Chart of Accounts</a>'
             + ' or click <strong>New Account</strong> to add one.</p>';
    }
    document.getElementById('coa-tree').innerHTML = html;
}

// ── View toggle ───────────────────────────────────────────────────────────
function setView(v) {
    currentView = v;
    if (localStorage) localStorage.setItem('xb_coa_view', v);
    if (v === 'tree') {
        $('#tree-view-container').show();
        $('#list-view-container').hide();
        $('#btn-tree-view').addClass('active');
        $('#btn-list-view').removeClass('active');
    } else {
        $('#tree-view-container').hide();
        $('#list-view-container').show();
        $('#btn-tree-view').removeClass('active');
        $('#btn-list-view').addClass('active');
    }
}

// ── Expand / collapse ─────────────────────────────────────────────────────
function toggleNode(id) {
    var $kids = $('#ck-' + id);
    var $icon = $('#cn-' + id + ' > .coa-row-inner .coa-toggle i');
    if ($kids.is(':visible')) {
        $kids.slideUp(150);
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $kids.slideDown(150);
        $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

// ── Group toggle pill ─────────────────────────────────────────────────────
var _groupOn = false;
function toggleGroup() {
    _groupOn = !_groupOn;
    $('#f_is_group').val(_groupOn ? '1' : '0');
    $('#group-pill').toggleClass('on', _groupOn);
    $('#group-pill-label').text(_groupOn ? 'Yes' : 'No');
}
function setGroupState(on) {
    _groupOn = !!on;
    $('#f_is_group').val(_groupOn ? '1' : '0');
    $('#group-pill').toggleClass('on', _groupOn);
    $('#group-pill-label').text(_groupOn ? 'Yes' : 'No');
}

// ── Account form ──────────────────────────────────────────────────────────
function resetAccountForm() {
    $('#accountModalTitle').text('New Account');
    $('#acc_id').val('');
    $('#f_name').val('');
    $('#f_code').val('');
    $('#f_parent_id').selectpicker('val', '');
    $('#f_type').selectpicker('val', '');
    $('#f_currency_id').selectpicker('val', '');
    $('#f_account_category').val('');
    setGroupState(false);
}

function editAccount(id) {
    var acc = accountMap[id];
    if (!acc) { alert('Account not found.'); return; }
    $('#accountModalTitle').text('Edit Account');
    $('#acc_id').val(acc.id);
    $('#f_name').val(acc.name || '');
    $('#f_code').val(acc.code || '');
    $('#f_parent_id').selectpicker('val', acc.parent_id ? String(acc.parent_id) : '');
    $('#f_type').selectpicker('val', acc.type || '');
    $('#f_currency_id').selectpicker('val', acc.currency_id ? String(acc.currency_id) : '');
    $('#f_account_category').val(acc.account_category || '');
    setGroupState(parseInt(acc.is_group) === 1);
    $('#accountModal').modal('show');
}

function addChildTo(parentId) {
    resetAccountForm();
    $('#f_parent_id').selectpicker('val', String(parentId));
    $('#accountModalTitle').text('New Account');
    $('#accountModal').modal('show');
}

function saveAccount() {
    var name = $.trim($('#f_name').val());
    if (!name) { alert('Account Name is required.'); return; }
    var payload = {
        id:               $('#acc_id').val(),
        name:             name,
        code:             $.trim($('#f_code').val()),
        type:             $('#f_type').val(),
        is_group:         $('#f_is_group').val(),
        parent_id:        $('#f_parent_id').val(),
        account_category: $.trim($('#f_account_category').val()),
        currency_id:      $('#f_currency_id').val(),
    };
    $.post('<?php echo admin_url('xetuu_books/ajax/save_account'); ?>', payload, function(res) {
        if (res && res.success) { location.reload(); }
        else { alert('Error saving account. The Account Number must be unique.'); }
    }, 'json').fail(function() { alert('Server error.'); });
}

function deleteAccount(id) {
    if (!confirm('Delete this account? This cannot be undone.')) return;
    $.post('<?php echo admin_url('xetuu_books/ajax/delete_account'); ?>', { id: id }, function(res) {
        if (res && res.success) { location.reload(); }
        else { alert('Cannot delete — account may be in use.'); }
    }, 'json');
}

function loadDefaultCoa() {
    if (!confirm('Load the default hierarchical chart of accounts?\n\nExisting accounts will not be overwritten.')) return;
    var $btn = $('button[onclick="loadDefaultCoa()"]').prop('disabled', true).text('Loading…');
    $.post('<?php echo admin_url('xetuu_books/ajax/seed_default_coa'); ?>', {}, function(res) {
        if (res && res.success) {
            alert('Default chart of accounts loaded (' + res.inserted + ' accounts added).');
            location.reload();
        } else { alert('Failed to load defaults.'); }
    }, 'json').fail(function() { alert('Server error.'); })
      .always(function() { $btn.prop('disabled', false).html('<i class="fa fa-database"></i> Load Defaults'); });
}

function removeLegacyCoa() {
    if (!confirm('Remove the old flat 4-digit accounts (1000–6999)?\n\nOnly accounts with zero transactions will be deleted. This cannot be undone.')) return;
    var $btn = $('button[onclick="removeLegacyCoa()"]').prop('disabled', true).text('Removing…');
    $.post('<?php echo admin_url('xetuu_books/ajax/delete_legacy_flat_accounts'); ?>', {}, function(res) {
        if (res && res.success) {
            alert(res.deleted + ' legacy account(s) removed.');
            location.reload();
        } else { alert('Failed to remove legacy accounts.'); }
    }, 'json').fail(function() { alert('Server error.'); })
      .always(function() { $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Remove Legacy'); });
}

// ── View Ledger ───────────────────────────────────────────────────────────
function viewLedger(id) {
    var acc = accountMap[id];
    if (!acc) return;

    var title = (acc.code ? '<small style="color:#9ca3af;">' + esc(acc.code) + '</small>&ensp;' : '') + esc(acc.name);
    if (parseInt(acc.is_group) === 1) {
        title += '&ensp;<small class="text-muted">(Group — showing all sub-accounts)</small>';
    }
    $('#ledgerModalTitle').html(title);
    $('#ledgerModalBody').html(
        '<div class="text-center" style="padding:40px;">'
        + '<i class="fa fa-spinner fa-spin fa-2x" style="color:#a0aec0;"></i>'
        + '<p class="text-muted" style="margin-top:10px; font-size:12px;">Loading transactions&hellip;</p></div>'
    );
    $('#ledgerModal').modal('show');

    $.get('<?php echo admin_url('xetuu_books/ajax/get_account_ledger'); ?>', {account_id: id}, function(rows) {
        var html = '';

        if (!rows || !rows.length) {
            html = '<div class="text-center" style="padding:40px;">'
                 + '<i class="fa fa-inbox fa-3x" style="color:#d1d5db;"></i>'
                 + '<p class="text-muted" style="margin-top:12px;">No posted transactions found for this account.</p>'
                 + '</div>';
        } else {
            var totalDr = 0, totalCr = 0;

            html += '<div class="table-responsive">'
                  + '<table class="table table-condensed table-striped">'
                  + '<thead><tr>'
                  + '<th>Date</th><th>Entry Ref</th><th>Description</th><th>Journal</th><th>Account</th>'
                  + '<th style="text-align:right;">Debit</th><th style="text-align:right;">Credit</th>'
                  + '</tr></thead><tbody>';

            rows.forEach(function(r) {
                var dr = parseFloat(r.debit  || 0);
                var cr = parseFloat(r.credit || 0);
                totalDr += dr;
                totalCr += cr;

                html += '<tr>';
                html += '<td style="white-space:nowrap;">' + esc(r.date || '') + '</td>';
                html += '<td><code>' + esc(r.entry_ref || '') + '</code></td>';
                html += '<td>' + esc(r.description || r.ref || '') + '</td>';
                html += '<td>' + esc(r.journal_name || '') + '</td>';
                html += '<td>'
                      + (r.account_code ? '<small style="color:#9ca3af;">' + esc(r.account_code) + '</small>&ensp;' : '')
                      + esc(r.account_name || '') + '</td>';
                html += '<td style="text-align:right; font-family:monospace;">' + (dr > 0 ? fmtNum(dr) : '') + '</td>';
                html += '<td style="text-align:right; font-family:monospace;">' + (cr > 0 ? fmtNum(cr) : '') + '</td>';
                html += '</tr>';
            });

            html += '</tbody>';

            // Totals row
            var netBal = totalDr - totalCr;
            var isDr   = netBal > 0;
            html += '<tfoot>'
                  + '<tr style="background:#f1f5f9; font-weight:600;">'
                  + '<td colspan="5" style="text-align:right;">Total</td>'
                  + '<td style="text-align:right; font-family:monospace;">' + fmtNum(totalDr) + '</td>'
                  + '<td style="text-align:right; font-family:monospace;">' + fmtNum(totalCr) + '</td>'
                  + '</tr>'
                  + '<tr style="background:#e0e7ff; font-weight:700;">'
                  + '<td colspan="5" style="text-align:right;">Balance</td>'
                  + '<td colspan="2" style="text-align:right; font-family:monospace; color:' + (isDr ? '#1d4ed8' : '#b45309') + ';">'
                  + esc(currencySymbol) + '&nbsp;' + fmtNum(Math.abs(netBal)) + '&nbsp;' + (isDr ? 'Dr' : 'Cr')
                  + '</td></tr>'
                  + '</tfoot>';

            html += '</table></div>';

            if (rows.length >= 200) {
                html += '<p class="text-muted text-center" style="font-size:11px; margin-top:6px;">'
                      + '<i class="fa fa-info-circle"></i> Showing latest 200 transactions only.</p>';
            }
        }

        $('#ledgerModalBody').html(html);
    }, 'json').fail(function() {
        $('#ledgerModalBody').html(
            '<p class="text-danger text-center" style="padding:20px;">'
            + '<i class="fa fa-exclamation-triangle"></i> Error loading transactions. Please try again.</p>'
        );
    });
}

// ── Init — deferred until jQuery is available ─────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    renderCoaTree();
    setView(currentView);
});
</script>
