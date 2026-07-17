<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_draft    = (!isset($entry) || $entry->state === 'draft');
$entry_name  = isset($entry) ? htmlspecialchars($entry->name ?? 'Draft') : 'New Journal Entry';
$entry_state = isset($entry) ? strtoupper($entry->state) : 'DRAFT';
$entry_id    = isset($entry) ? (int)$entry->id : 0;
$lines_to_render = !empty($entry_lines) ? $entry_lines : [
    (object)['id'=>'','account_id'=>'','partner_id'=>'','name'=>'','debit'=>0,'credit'=>0],
    (object)['id'=>'','account_id'=>'','partner_id'=>'','name'=>'','debit'=>0,'credit'=>0],
];
$state_class = 'xbj-badge-draft';
if(isset($entry)){
    if($entry->state==='posted') $state_class='xbj-badge-posted';
    if($entry->state==='cancel') $state_class='xbj-badge-cancel';
}
$top5 = array_slice((array)$recent_entries, 0, 5);
?>

<style>
.xbj-workspace {
    display: flex;
    gap: 0;
    min-height: calc(100vh - 110px);
    background: #f0fdf4;
    margin: -15px -25px -25px -25px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 13px;
    color: #374151;
}

/* ── CENTER FORM ───────────────────────────────────────────────── */
.xbj-center {
    flex: 1;
    min-width: 0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.xbj-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 11px 18px;
    border-radius: 8px;
    border: 1px solid #dcfce7;
}
.xbj-back-link {
    display: flex; align-items: center; gap: 7px;
    color: #166534; font-weight: 600; font-size: 13px;
    text-decoration: none;
}
.xbj-back-link:hover { color: #14532d; text-decoration: none; }
.xbj-topbar-title {
    font-size: 15px; font-weight: 700; color: #111827;
    margin-left: 14px; border-left: 2px solid #e5e7eb;
    padding-left: 14px; display: flex; align-items: center; gap: 9px;
}
.xbj-badge {
    display: inline-block; padding: 3px 10px;
    font-size: 10px; font-weight: 700; border-radius: 20px; letter-spacing: 0.05em;
}
.xbj-badge-draft  { background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; }
.xbj-badge-posted { background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; }
.xbj-badge-cancel { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }

.xbj-topbar-actions { display: flex; gap: 8px; align-items: center; }
.xbj-tb-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 6px;
    font-weight: 600; font-size: 12px; cursor: pointer; border: none;
    text-decoration: none;
}
.xbj-tb-btn-save  { background:#16a34a; color:#fff; }
.xbj-tb-btn-save:hover  { background:#15803d; color:#fff; }
.xbj-tb-btn-post  { background:#0f766e; color:#fff; }
.xbj-tb-btn-post:hover  { background:#0d6b63; color:#fff; }
.xbj-tb-btn-ghost { background:#fff; color:#374151; border:1px solid #d1d5db; }
.xbj-tb-btn-ghost:hover { background:#f9fafb; color:#374151; }

.xbj-card {
    background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;
}
.xbj-card-header {
    background: #f9fafb; padding: 11px 18px;
    font-size: 13px; font-weight: 700; color: #166534;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid #e5e7eb;
}
.xbj-card-body { padding: 18px; }

.xbj-field-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.xbj-label {
    display: block; font-size: 10px; font-weight: 700; color: #4b5563;
    text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.05em;
}
.xbj-label.req::after { content: ' *'; color: #dc2626; }
.xbj-input {
    width: 100%; border: 1px solid #d1d5db; border-radius: 6px;
    padding: 7px 11px; font-size: 13px; color: #111827;
    background: #fff; box-sizing: border-box;
}
.xbj-input:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 1px #16a34a; }
.xbj-input[disabled] { background: #f9fafb; color: #6b7280; cursor: not-allowed; }

.xbj-lines-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.xbj-lines-tbl thead th {
    background: #111827; color: #fff; padding: 10px 12px;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; border: none;
}
.xbj-lines-tbl tbody tr { border-bottom: 1px solid #f3f4f6; }
.xbj-lines-tbl tbody tr:hover { background: #fafafa; }
.xbj-lines-tbl td { padding: 7px 8px; vertical-align: middle; }
.xbj-lines-tbl td .xbj-input { padding: 6px 9px; font-size: 12px; }

.xbj-totals {
    background: #f0fdf4; border: 1px solid #dcfce7;
    border-radius: 8px; padding: 16px 20px;
}
.xbj-trow { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #374151; }
.xbj-trow.grand { font-size: 15px; font-weight: 700; margin-top: 12px; padding-top: 12px; border-top: 2px solid #bbf7d0; color: #14532d; }
.xbj-bal-bad { color: #dc2626 !important; }

/* ── RIGHT PANEL ───────────────────────────────────────────────── */
.xbj-right {
    width: 270px;
    min-width: 270px;
    background: #fff;
    border-left: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
}
.xbj-right-section-title {
    padding: 14px 16px 10px;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.07em; color: #9ca3af;
    border-bottom: 1px solid #f3f4f6;
}
.xbj-entry-item {
    display: block; padding: 10px 14px;
    border-bottom: 1px solid #f3f4f6;
    text-decoration: none; transition: background .12s;
}
.xbj-entry-item:hover { background: #f0fdf4; text-decoration: none; }
.xbj-entry-item.xbj-active { background: #f0fdf4; border-left: 3px solid #16a34a; padding-left: 11px; }
.xbj-ei-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
.xbj-ei-name { font-weight: 700; font-size: 12px; color: #111827; }
.xbj-ei-date { font-size: 11px; color: #9ca3af; }
.xbj-ei-journal { font-size: 11px; color: #6b7280; margin-bottom: 2px; }
.xbj-ei-bottom { display: flex; justify-content: space-between; align-items: center; }
.xbj-ei-ref { font-size: 11px; color: #9ca3af; }
.xbj-ei-badge {
    display: inline-block; padding: 1px 7px;
    border-radius: 10px; font-size: 10px; font-weight: 700;
}
.xbj-ei-posted { background:#dcfce7; color:#16a34a; }
.xbj-ei-draft  { background:#f3f4f6; color:#6b7280; }
.xbj-ei-cancel { background:#fee2e2; color:#dc2626; }
.xbj-see-all {
    display: block; padding: 10px 14px;
    font-size: 12px; font-weight: 600; color: #16a34a;
    text-decoration: none; border-bottom: 1px solid #f3f4f6;
}
.xbj-see-all:hover { background: #f0fdf4; text-decoration: none; color: #15803d; }

.xbj-quick-link {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 14px; color: #374151; text-decoration: none;
    font-size: 12px; font-weight: 500;
    border-bottom: 1px solid #f3f4f6;
    transition: background .12s;
}
.xbj-quick-link:hover { background: #f0fdf4; color: #15803d; text-decoration: none; }
.xbj-quick-link i { color: #16a34a; width: 14px; text-align: center; }

.xbj-empty { padding: 20px 14px; text-align: center; color: #9ca3af; font-size: 12px; }
.xbj-empty i { font-size: 24px; display: block; margin-bottom: 6px; color: #d1d5db; }
</style>

<div class="xbj-workspace">

    <!-- ═══════════════════════════ FORM (LEFT) ═══════════════════════════ -->
    <div class="xbj-center">

        <?php echo form_open(admin_url('xetuu_books/journal_entry_form/'.($entry_id ?: '')), ['id'=>'journal-entry-form']); ?>

        <!-- Topbar -->
        <div class="xbj-topbar">
            <div style="display:flex; align-items:center;">
                <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="xbj-back-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    Journal Entries
                </a>
                <div class="xbj-topbar-title">
                    <i class="fa fa-book" style="color:#16a34a;"></i>
                    <?php echo $entry_name; ?>
                    <span class="xbj-badge <?php echo $state_class; ?>"><?php echo $entry_state; ?></span>
                </div>
            </div>
            <?php if($is_draft): ?>
            <div class="xbj-topbar-actions">
                <button type="submit" class="xbj-tb-btn xbj-tb-btn-save" name="save_action" value="draft">
                    <i class="fa fa-save"></i> Save Draft
                </button>
                <?php if($entry_id): ?>
                <button type="button" class="xbj-tb-btn xbj-tb-btn-post" onclick="xbj_post(<?php echo $entry_id; ?>)">
                    <i class="fa fa-check-circle"></i> Post Entry
                </button>
                <?php endif; ?>
                <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="xbj-tb-btn xbj-tb-btn-ghost">
                    <i class="fa fa-times"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Entry Details -->
        <div class="xbj-card">
            <div class="xbj-card-header">
                <i class="fa fa-file-text"></i> Entry Details
            </div>
            <div class="xbj-card-body">
                <div class="xbj-field-grid-3">
                    <div>
                        <label class="xbj-label req">Journal</label>
                        <select name="journal_id" class="xbj-input selectpicker" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            <option value="">— Select —</option>
                            <?php foreach($all_journals as $j): ?>
                                <option value="<?php echo $j->id; ?>"
                                    <?php echo (isset($entry) && $entry->journal_id == $j->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($j->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="xbj-label req">Accounting Date</label>
                        <input type="date" name="date" class="xbj-input"
                               value="<?php echo isset($entry) ? htmlspecialchars($entry->date) : date('Y-m-d'); ?>"
                               <?php echo !$is_draft ? 'disabled' : ''; ?>>
                    </div>
                    <div>
                        <label class="xbj-label">Reference</label>
                        <input type="text" name="ref" class="xbj-input"
                               value="<?php echo isset($entry) ? htmlspecialchars($entry->ref) : ''; ?>"
                               placeholder="e.g. INV/2025/001"
                               <?php echo !$is_draft ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="xbj-label">Currency</label>
                        <select name="currency_id" id="xbj-currency" class="xbj-input selectpicker" <?php echo !$is_draft ? 'disabled' : ''; ?> onchange="xbjOnCurrencyChange()">
                            <?php
                            $entry_cur_id = isset($entry) ? (int)($entry->currency_id ?? 1) : 1;
                            foreach($currencies as $cur):
                                $cur_sel = ($cur->id == $entry_cur_id) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $cur->id; ?>" data-rate="<?php echo $cur->rate; ?>" data-name="<?php echo htmlspecialchars($cur->name); ?>" data-symbol="<?php echo htmlspecialchars($cur->symbol); ?>" <?php echo $cur_sel; ?>>
                                <?php echo $cur->name; ?> — <?php echo htmlspecialchars($cur->symbol); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:10px;color:#9ca3af;margin-top:4px;">Debit/Credit amounts are entered in this currency</p>
                    </div>
                    <div id="xbj-exrate-wrap" style="<?php echo $entry_cur_id !== 1 ? '' : 'display:none;'; ?>">
                        <label class="xbj-label">Exchange Rate <small id="xbj-exrate-hint" style="font-weight:400;text-transform:none;color:#9ca3af;">(per KES)</small></label>
                        <input type="number" step="any" name="exchange_rate" id="xbj-exchange-rate" class="xbj-input"
                               value="<?php echo isset($entry) && $entry->exchange_rate > 0 ? $entry->exchange_rate : 1; ?>"
                               <?php echo !$is_draft ? 'disabled' : ''; ?> oninput="xbj_calc()">
                        <p style="font-size:10px;color:#9ca3af;margin-top:4px;">1 <span id="xbj-cur-code"><?php echo isset($entry) ? xb_get_currency_code($entry_cur_id) : 'USD'; ?></span> = this many KES. Used when posting journal entries.</p>
                    </div>
                </div>
                <div>
                    <label class="xbj-label">Narration</label>
                    <textarea name="narration" class="xbj-input" style="min-height:56px; resize:vertical;"
                              placeholder="Optional notes..."
                              <?php echo !$is_draft ? 'disabled' : ''; ?>><?php echo isset($entry) ? htmlspecialchars($entry->narration ?? '') : ''; ?></textarea>
                </div>
                <?php if (function_exists('xb_render_analytic_field')): ?>
                <div style="margin-top:12px;">
                    <?php echo xb_render_analytic_field('journal_entry', $entry_id, 'Analytic Account (Cost Centre)'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Journal Lines -->
        <div class="xbj-card">
            <div class="xbj-card-header">
                <i class="fa fa-list"></i> Journal Items
            </div>
            <div style="overflow-x:auto;">
                <table class="xbj-lines-tbl" id="xbj-lines-table">
                    <thead>
                        <tr>
                            <th width="28%">Account</th>
                            <th width="20%">Partner</th>
                            <th width="22%">Label</th>
                            <th width="12%" style="text-align:right;">Debit</th>
                            <th width="12%" style="text-align:right;">Credit</th>
                            <th width="6%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lines_to_render as $idx => $line): ?>
                        <tr class="xbj-line-row">
                            <td>
                                <?php if(!empty($line->id)): ?>
                                    <input type="hidden" name="lines[<?php echo $idx; ?>][id]" value="<?php echo $line->id; ?>">
                                <?php endif; ?>
                                <select name="lines[<?php echo $idx; ?>][account_id]" class="xbj-input selectpicker" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                                    <option value=""></option>
                                    <?php foreach($accounts as $acc): ?>
                                        <option value="<?php echo $acc->id; ?>"
                                            <?php echo (!empty($line->account_id) && $line->account_id == $acc->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($acc->code . ' – ' . $acc->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="lines[<?php echo $idx; ?>][partner_id]" class="xbj-input selectpicker" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                                    <option value=""></option>
                                    <?php if(!empty($line->partner_id)): ?>
                                        <option value="<?php echo $line->partner_id; ?>" selected>
                                            <?php echo htmlspecialchars(xb_get_partner_name($line->partner_id)); ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="lines[<?php echo $idx; ?>][name]" class="xbj-input"
                                       value="<?php echo isset($line->name) ? htmlspecialchars($line->name) : ''; ?>"
                                       placeholder="Label" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="number" name="lines[<?php echo $idx; ?>][debit]" class="xbj-input xbj-dr"
                                       value="<?php echo isset($line->debit) ? (float)$line->debit : 0; ?>"
                                       step="0.01" min="0" style="text-align:right;"
                                       oninput="xbj_calc()" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="number" name="lines[<?php echo $idx; ?>][credit]" class="xbj-input xbj-cr"
                                       value="<?php echo isset($line->credit) ? (float)$line->credit : 0; ?>"
                                       step="0.01" min="0" style="text-align:right;"
                                       oninput="xbj_calc()" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </td>
                            <td style="text-align:center;">
                                <?php if($is_draft): ?>
                                <button type="button" class="btn btn-danger btn-icon btn-sm"
                                        onclick="$(this).closest('tr').remove(); xbj_calc();" title="Remove">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if($is_draft): ?>
            <div style="padding:12px 16px; border-top:1px solid #e5e7eb;">
                <button type="button" onclick="xbj_add_line()"
                        style="background:#16a34a; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-weight:700; font-size:12px; cursor:pointer;">
                    <i class="fa fa-plus-circle" style="margin-right:5px;"></i> Add Line
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Totals -->
        <div style="display:flex; justify-content:flex-end; padding-bottom:20px;">
            <div class="xbj-totals" style="min-width:260px;">
                <div class="xbj-trow">
                    <span>Total Debit</span>
                    <strong id="xbj-total-dr">0.00</strong>
                </div>
                <div class="xbj-trow">
                    <span>Total Credit</span>
                    <strong id="xbj-total-cr">0.00</strong>
                </div>
                <div class="xbj-trow grand">
                    <span>Balance</span>
                    <strong id="xbj-balance">0.00</strong>
                </div>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>

    <!-- ═══════════════════════════ RIGHT PANEL ═══════════════════════════ -->
    <div class="xbj-right">

        <!-- Recent Entries (top 5) -->
        <div class="xbj-right-section-title">Recent Entries</div>

        <?php if(empty($top5)): ?>
            <div class="xbj-empty">
                <i class="fa fa-inbox"></i>
                No entries yet
            </div>
        <?php else: ?>
            <?php foreach($top5 as $e): ?>
            <?php
                $active = ($entry_id && $entry_id == $e->id) ? ' xbj-active' : '';
                $ebc = 'xbj-ei-draft';
                if($e->state==='posted') $ebc='xbj-ei-posted';
                if($e->state==='cancel') $ebc='xbj-ei-cancel';
            ?>
            <a href="<?php echo admin_url('xetuu_books/journal_entry_form/'.$e->id); ?>"
               class="xbj-entry-item<?php echo $active; ?>">
                <div class="xbj-ei-top">
                    <span class="xbj-ei-name"><?php echo htmlspecialchars($e->name ?? '—'); ?></span>
                    <span class="xbj-ei-date"><?php echo date('d M', strtotime($e->date)); ?></span>
                </div>
                <div class="xbj-ei-journal"><?php echo htmlspecialchars($e->journal_name ?? '—'); ?></div>
                <div class="xbj-ei-bottom">
                    <span class="xbj-ei-ref"><?php echo !empty($e->ref) ? htmlspecialchars($e->ref) : '—'; ?></span>
                    <span class="xbj-ei-badge <?php echo $ebc; ?>"><?php echo strtoupper($e->state); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="xbj-see-all">
            <i class="fa fa-arrow-right" style="margin-right:6px;"></i> See all entries
        </a>

        <!-- Quick Links -->
        <div class="xbj-right-section-title" style="margin-top:4px;">Quick Links</div>

        <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="xbj-quick-link">
            <i class="fa fa-list"></i> All Entries
        </a>
        <a href="<?php echo admin_url('xetuu_books/journal_entry_form'); ?>" class="xbj-quick-link">
            <i class="fa fa-plus-circle"></i> New Entry
        </a>
        <a href="<?php echo admin_url('xetuu_books/chart_of_accounts'); ?>" class="xbj-quick-link">
            <i class="fa fa-sitemap"></i> Chart of Accounts
        </a>
        <a href="<?php echo admin_url('xetuu_books/journals_config'); ?>" class="xbj-quick-link">
            <i class="fa fa-cog"></i> Manage Journals
        </a>

    </div>

</div>

<script>
var xbj_idx = <?php echo count($lines_to_render); ?>;
var xbj_accounts_html = '';
<?php foreach($accounts as $acc): ?>
xbj_accounts_html += '<option value="<?php echo $acc->id; ?>"><?php echo addslashes(htmlspecialchars($acc->code . " – " . $acc->name)); ?></option>';
<?php endforeach; ?>

function xbj_add_line() {
    var i = xbj_idx;
    var row = '<tr class="xbj-line-row">'
        + '<td><select name="lines['+i+'][account_id]" class="xbj-input selectpicker" data-live-search="true"><option value=""></option>'
        + xbj_accounts_html + '</select></td>'
        + '<td><select name="lines['+i+'][partner_id]" class="xbj-input selectpicker" data-live-search="true"><option value=""></option></select></td>'
        + '<td><input type="text" name="lines['+i+'][name]" class="xbj-input" placeholder="Label"></td>'
        + '<td><input type="number" name="lines['+i+'][debit]" class="xbj-input xbj-dr" value="0" step="0.01" min="0" style="text-align:right;" oninput="xbj_calc()"></td>'
        + '<td><input type="number" name="lines['+i+'][credit]" class="xbj-input xbj-cr" value="0" step="0.01" min="0" style="text-align:right;" oninput="xbj_calc()"></td>'
        + '<td style="text-align:center;"><button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest(\'tr\').remove();xbj_calc();" title="Remove"><i class="fa fa-trash"></i></button></td>'
        + '</tr>';
    var $row = $(row);
    $('#xbj-lines-table tbody').append($row);
    if ($.fn.selectpicker) { $row.find('.selectpicker').selectpicker(); }
    xbj_idx++;
}

function xbjOnCurrencyChange() {
    var $sel = $('#xbj-currency option:selected');
    var curId   = parseInt($sel.val()) || 1;
    var curName = $sel.data('name') || 'KES';
    var isBase  = (curId === 1);

    if (isBase) {
        $('#xbj-exrate-wrap').hide();
        $('#xbj-exchange-rate').val(1);
        xbj_calc();
        return;
    }

    $('#xbj-cur-code').text(curName);
    $('#xbj-exrate-hint').text('(1 ' + curName + ' = ? KES)');
    $('#xbj-exrate-wrap').show();

    $.get(admin_url + 'xetuu_books/ajax/get_currency_rate', {id: curId}, function(res) {
        var data;
        try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { data = null; }
        if (data && data.rate) {
            $('#xbj-exchange-rate').val(parseFloat(data.rate).toFixed(4));
        }
        xbj_calc();
    }).fail(function() { xbj_calc(); });
}

function xbj_calc() {
    var dr = 0, cr = 0;
    $('.xbj-dr').each(function() { dr += parseFloat($(this).val()) || 0; });
    $('.xbj-cr').each(function() { cr += parseFloat($(this).val()) || 0; });
    var bal = dr - cr;

    var curId   = parseInt($('#xbj-currency').val()) || 1;
    var $curOpt = $('#xbj-currency option:selected');
    var sym     = $curOpt.data('symbol') || 'KSh';
    var rate    = parseFloat($('#xbj-exchange-rate').val()) || 1;
    var fmt     = function(n) { return sym + ' ' + n.toFixed(2); };

    $('#xbj-total-dr').text(fmt(dr));
    $('#xbj-total-cr').text(fmt(cr));
    $('#xbj-balance').text(Math.abs(bal).toFixed(2)).toggleClass('xbj-bal-bad', Math.abs(bal) > 0.005);

    if (curId !== 1 && rate !== 1) {
        var kesLabel = ' <small style="color:#9ca3af;font-weight:400;font-size:10px;">(≈ KSh ' + (dr * rate).toFixed(2) + ')</small>';
        $('#xbj-total-dr').html(fmt(dr) + kesLabel);
        kesLabel = ' <small style="color:#9ca3af;font-weight:400;font-size:10px;">(≈ KSh ' + (cr * rate).toFixed(2) + ')</small>';
        $('#xbj-total-cr').html(fmt(cr) + kesLabel);
    }
}

function xbj_post(id) {
    if (!confirm('Post this journal entry? It cannot be edited after posting.')) return;
    $.post(admin_url + 'xetuu_books/post_entry/' + id, {}, function(raw) {
        try {
            var res = typeof raw === 'object' ? raw : JSON.parse(raw);
            if (res.success) { window.location.reload(); }
            else { alert(res.message || 'Failed to post entry.'); }
        } catch(e) { alert('Unexpected response.'); }
    });
}

$(function() { xbj_calc(); });
</script>
