<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.it-page { padding:22px 28px; max-width:960px; }
.it-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; }
.it-head-left h1 { font-size:18px; font-weight:800; color:#111827; margin:0 0 2px; }
.it-head-left p  { font-size:12px; color:#6b7280; margin:0; }

.it-toolbar { display:flex; align-items:center; gap:8px; padding:6px 8px;
    background:#f9fafb; border:1px solid #e5e7eb; border-bottom:none;
    border-radius:8px 8px 0 0; }
.it-toolbar-btn { display:inline-flex; align-items:center; gap:5px; padding:4px 11px;
    border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;
    border:1px solid #e5e7eb; background:#fff; color:#374151; text-decoration:none; white-space:nowrap; }
.it-toolbar-btn:hover { border-color:#16a34a; color:#16a34a; }
.it-toolbar-btn.primary { background:#16a34a; border-color:#16a34a; color:#fff; }
.it-toolbar-btn.primary:hover { background:#15803d; }
.it-toolbar-btn .material-symbols-outlined { font-size:14px; }
.it-row-count { margin-left:auto; font-size:11px; color:#9ca3af; }

/* Excel grid */
.it-grid-wrap { border:1px solid #d1d5db; border-radius:0 0 8px 8px; overflow:hidden; }
.it-grid { width:100%; border-collapse:collapse; font-family:'Segoe UI',system-ui,sans-serif; }

.it-grid .hdr th {
    background:#f3f4f6; font-size:11px; font-weight:700; color:#6b7280;
    text-transform:uppercase; letter-spacing:.4px;
    padding:5px 8px; border:1px solid #d1d5db; user-select:none; white-space:nowrap;
}
.it-grid .hdr th:first-child { width:36px; text-align:center; background:#f9fafb; }

.it-grid .dr td {
    padding:6px 8px; border:1px solid #e5e7eb;
    font-size:12.5px; color:#1f2937; vertical-align:top;
    /* allow full text — no truncation */
}
.it-grid .dr:hover td { background:#f0fdf4; }
.it-grid .dr td:first-child { text-align:center; color:#9ca3af; font-size:11px; background:#f9fafb; width:36px; user-select:none; vertical-align:middle; }
.it-grid .dr td.name-cell { font-weight:600; color:#111827; width:30%; white-space:nowrap; vertical-align:middle; }
.it-grid .dr td.desc-cell { color:#4b5563; line-height:1.5; }
.it-grid .dr td.act-cell  { width:80px; text-align:right; white-space:nowrap; vertical-align:middle; }

.cell-btn { display:inline-flex; align-items:center; justify-content:center;
    width:24px; height:24px; border-radius:4px; border:none; background:transparent;
    cursor:pointer; color:#9ca3af; }
.cell-btn:hover.edit { color:#16a34a; background:#f0fdf4; }
.cell-btn:hover.del  { color:#dc2626; background:#fef2f2; }
.cell-btn .material-symbols-outlined { font-size:14px; }

.it-grid .empty-row td { text-align:center; padding:32px; color:#9ca3af; font-size:13px; border:1px solid #e5e7eb; }

.it-add-row td { padding:3px 6px !important; border-top:2px solid #16a34a !important; vertical-align:middle !important; }
.it-add-row input, .it-add-row textarea {
    width:100%; border:none; outline:none; background:transparent;
    font-size:12.5px; font-family:inherit; resize:none; padding:1px 2px;
}
.it-add-row textarea { height:26px; line-height:1.4; overflow:hidden; }
</style>

<div class="it-page">

    <div class="it-head">
        <div class="it-head-left">
            <h1>Interview Types</h1>
            <p>Categories of assessments used during recruitment (e.g. Technical Test, HR Screening).</p>
        </div>
        <div style="display:flex;gap:6px;">
            <a href="<?php echo $base . '/setup/appointment_letter_template'; ?>" class="it-toolbar-btn">
                <span class="material-symbols-outlined">receipt_long</span> Letter Templates
            </a>
            <a href="<?php echo $base . '/setup/interview_round'; ?>" class="it-toolbar-btn">
                <span class="material-symbols-outlined">sync</span> Interview Rounds
            </a>
        </div>
    </div>

    <div class="it-toolbar">
        <button type="button" class="it-toolbar-btn primary" onclick="itAddRow()">
            <span class="material-symbols-outlined">add</span> Add Row
        </button>
        <div style="width:1px;height:20px;background:#e5e7eb;margin:0 2px;"></div>
        <button type="button" class="it-toolbar-btn" onclick="itOpenModal(null)">
            <span class="material-symbols-outlined">edit_note</span> Form Editor
        </button>
        <span class="it-row-count"><?php echo count($rows); ?> type<?php echo count($rows) !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="it-grid-wrap">
        <table class="it-grid">
            <thead>
                <tr class="hdr">
                    <th>#</th>
                    <th style="width:30%;">Interview Type</th>
                    <th>Description</th>
                    <th style="width:80px;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="it-tbody">
                <?php if (empty($rows)): ?>
                <tr class="empty-row" id="it-empty">
                    <td colspan="4">No interview types yet. Click <strong>Add Row</strong> above to add one.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $i => $r): ?>
                <tr class="dr" data-id="<?php echo $r->id; ?>">
                    <td><?php echo $i + 1; ?></td>
                    <td class="name-cell"><?php echo htmlspecialchars($r->name); ?></td>
                    <td class="desc-cell"><?php echo nl2br(htmlspecialchars($r->description ?? '')); ?></td>
                    <td class="act-cell">
                        <button class="cell-btn edit" title="Edit" onclick="itOpenModal(<?php echo $r->id; ?>)">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <a href="<?php echo $base . '/setup/interview_type/delete/' . $r->id; ?>"
                           class="cell-btn del _delete" title="Delete">
                            <span class="material-symbols-outlined">delete</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- /.it-page -->

<script>
var IT_ROWS = <?php
    $js = [];
    foreach ($rows as $r) {
        $js[(int)$r->id] = ['id' => (int)$r->id, 'name' => (string)$r->name, 'description' => (string)($r->description ?? '')];
    }
    echo json_encode($js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>;
</script>

<!-- Modal -->
<div id="it-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:480px;box-shadow:0 20px 50px rgba(0,0,0,.2);overflow:hidden;">
        <form action="<?php echo $base . '/setup/interview_type'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="it-m-id">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6;">
                <h4 id="it-m-title" style="margin:0;font-size:14px;font-weight:700;color:#111827;">Add Interview Type</h4>
                <button type="button" onclick="itCloseModal()" style="background:none;border:none;font-size:20px;color:#9ca3af;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Type Name *</label>
                    <input type="text" name="name" id="it-m-name" class="form-control" required placeholder="e.g. Technical Coding Test">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Description</label>
                    <textarea name="description" id="it-m-desc" class="form-control" rows="4" placeholder="Brief description of what this interview type involves…"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;padding:10px 18px;border-top:1px solid #f3f4f6;">
                <button type="button" onclick="itCloseModal()" style="padding:7px 14px;border-radius:7px;border:1.5px solid #e5e7eb;background:#fff;font-size:12px;font-weight:600;cursor:pointer;color:#374151;">Cancel</button>
                <button type="submit" style="padding:7px 16px;border-radius:7px;border:none;background:#16a34a;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<form id="it-add-form" action="<?php echo $base . '/setup/interview_type'; ?>" method="post" style="display:none;">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" value="">
</form>

<script>
function itOpenModal(id) {
    var row = id ? IT_ROWS[id] : null;
    document.getElementById('it-m-id').value   = row ? row.id : '';
    document.getElementById('it-m-name').value  = row ? row.name : '';
    document.getElementById('it-m-desc').value  = row ? row.description : '';
    document.getElementById('it-m-title').textContent = row ? 'Edit Interview Type' : 'Add Interview Type';
    document.getElementById('it-modal').style.display = 'flex';
    setTimeout(function(){ document.getElementById('it-m-name').focus(); }, 80);
}
function itCloseModal() { document.getElementById('it-modal').style.display = 'none'; }
document.getElementById('it-modal').addEventListener('click', function(e){ if(e.target===this) itCloseModal(); });

function itAddRow() {
    var empty = document.getElementById('it-empty');
    if (empty) empty.remove();
    var existing = document.getElementById('it-inline-row');
    if (existing) { existing.remove(); return; }

    var tbody = document.getElementById('it-tbody');
    var rowNum = tbody.querySelectorAll('tr.dr').length + 1;
    var tr = document.createElement('tr');
    tr.id = 'it-inline-row'; tr.className = 'it-add-row';
    tr.innerHTML = [
        '<td style="text-align:center;color:#9ca3af;font-size:11px;background:#f9fafb;width:36px;">' + rowNum + '</td>',
        '<td><input type="text" id="it-inline-name" placeholder="Interview type name" required></td>',
        '<td><textarea id="it-inline-desc" placeholder="Description (optional)" rows="1"></textarea></td>',
        '<td style="text-align:right;white-space:nowrap;">',
        '<button type="button" onclick="itSaveInline()" title="Save" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:5px;border:none;background:#16a34a;color:#fff;cursor:pointer;"><span class="material-symbols-outlined" style="font-size:14px;">check</span></button> ',
        '<button type="button" onclick="itCancelInline()" title="Cancel" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:5px;border:none;background:#f3f4f6;color:#6b7280;cursor:pointer;"><span class="material-symbols-outlined" style="font-size:14px;">close</span></button>',
        '</td>',
    ].join('');
    tbody.appendChild(tr);
    document.getElementById('it-inline-name').focus();
}
function itSaveInline() {
    var name = document.getElementById('it-inline-name').value.trim();
    if (!name) { document.getElementById('it-inline-name').focus(); return; }
    var form = document.getElementById('it-add-form');
    var nInp = document.createElement('input'); nInp.type='hidden'; nInp.name='name'; nInp.value=name;
    var dInp = document.createElement('input'); dInp.type='hidden'; dInp.name='description'; dInp.value=document.getElementById('it-inline-desc').value.trim();
    form.appendChild(nInp); form.appendChild(dInp);
    form.submit();
}
function itCancelInline() {
    var tr = document.getElementById('it-inline-row'); if (tr) tr.remove();
    var tbody = document.getElementById('it-tbody');
    if (!tbody.querySelector('tr.dr')) {
        var e = document.createElement('tr'); e.id='it-empty'; e.className='empty-row';
        e.innerHTML='<td colspan="4">No interview types yet. Click <strong>Add Row</strong> above to add one.</td>';
        tbody.appendChild(e);
    }
}
document.addEventListener('keydown', function(e) {
    if (e.target.id==='it-inline-name' && e.key==='Enter') { e.preventDefault(); document.getElementById('it-inline-desc').focus(); }
    if (e.target.id==='it-inline-desc' && e.key==='Enter' && !e.shiftKey) { e.preventDefault(); itSaveInline(); }
    if (e.key==='Escape') { itCancelInline(); itCloseModal(); }
});
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
