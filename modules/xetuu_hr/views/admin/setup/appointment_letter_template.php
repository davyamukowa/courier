<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.alt-page  { padding:28px 32px; max-width:1100px; }
.alt-head  { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; }
.alt-head-left h1 { font-size:20px; font-weight:800; color:#111827; margin:0 0 4px; }
.alt-head-left p  { font-size:13px; color:#6b7280; margin:0; }
.alt-btn  { display:inline-flex; align-items:center; gap:6px; padding:9px 18px;
    border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none;
    text-decoration:none; white-space:nowrap; }
.alt-btn-primary { background:#16a34a; color:#fff; }
.alt-btn-primary:hover { background:#15803d; color:#fff; }
.alt-btn-outline { background:#fff; border:1.5px solid #e5e7eb; color:#374151; }
.alt-btn-outline:hover { border-color:#16a34a; color:#16a34a; }
.alt-btn .material-symbols-outlined { font-size:16px; }

.alt-info { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;
    padding:12px 16px; font-size:12.5px; color:#166534; margin-bottom:20px; display:flex; gap:10px; align-items:flex-start; }
.alt-info .material-symbols-outlined { font-size:18px; flex-shrink:0; margin-top:1px; }
.alt-info code { background:#dcfce7; padding:1px 5px; border-radius:4px; font-size:11.5px; color:#15803d; }

.alt-table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
.alt-table { width:100%; border-collapse:collapse; table-layout:fixed; }
.alt-table thead tr { background:#f9fafb; }
.alt-table th { padding:11px 16px; font-size:11px; font-weight:700; color:#6b7280;
    text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.alt-table td { padding:13px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; overflow:hidden; }
.alt-table tr:last-child td { border-bottom:none; }
.alt-table tr:hover td { background:#fafafa; }
.alt-tname  { font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.alt-tname small { display:block; font-weight:400; color:#9ca3af; font-size:11px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.alt-badge  { display:inline-flex; align-items:center; gap:4px; padding:3px 9px;
    border-radius:20px; font-size:11px; font-weight:600;
    background:#f0fdf4; color:#16a34a; }
.alt-actions { display:flex; align-items:center; gap:6px; justify-content:flex-end; }
.alt-icon-btn { width:32px; height:32px; border-radius:8px; border:1.5px solid #e5e7eb;
    background:#fff; display:inline-flex; align-items:center; justify-content:center;
    cursor:pointer; text-decoration:none; color:#374151; font-size:14px; flex-shrink:0; }
.alt-icon-btn:hover { border-color:#16a34a; color:#16a34a; }
.alt-icon-btn.danger:hover { border-color:#fca5a5; color:#dc2626; }
.alt-icon-btn .material-symbols-outlined { font-size:16px; }

.alt-empty { padding:48px; text-align:center; color:#9ca3af; }
.alt-empty .material-symbols-outlined { font-size:40px; display:block; margin-bottom:10px; }
.alt-empty b { display:block; color:#374151; font-size:14px; margin-bottom:4px; }
</style>

<div class="alt-page">

    <div class="alt-head">
        <div class="alt-head-left">
            <h1>Appointment Letter Templates</h1>
            <p>Ready-to-use letter templates for different departments. Duplicate any template to customise it for a specific role.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="<?php echo $base . '/setup/interview_type'; ?>" class="alt-btn alt-btn-outline">
                <span class="material-symbols-outlined">category</span> Interview Types
            </a>
            <a href="<?php echo $base . '/setup/interview_round'; ?>" class="alt-btn alt-btn-outline">
                <span class="material-symbols-outlined">sync</span> Interview Rounds
            </a>
            <button type="button" class="alt-btn alt-btn-primary" onclick="altOpenModal(null)">
                <span class="material-symbols-outlined">add</span> New Template
            </button>
        </div>
    </div>

    <div class="alt-info">
        <span class="material-symbols-outlined">lightbulb</span>
        <div>
            <strong>Placeholders</strong> — replaced automatically when generating a letter:
            <code>{applicant_name}</code> <code>{designation}</code> <code>{salary}</code>
            <code>{joining_date}</code> <code>{company_name}</code> <code>{department}</code> <code>{manager_name}</code><br>
            To create a role-specific version, click <strong>Duplicate</strong> on any template, then edit the copy.
        </div>
    </div>

    <div class="alt-table-wrap">
        <table class="alt-table">
            <colgroup>
                <col style="width:52%">
                <col style="width:18%">
                <col style="width:30%">
            </colgroup>
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Terms / Clauses</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                <tr><td colspan="3">
                    <div class="alt-empty">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <b>No templates yet</b>
                        <span>Click "Load Default Data" in Settings to install 10 ready-made templates, or create one above.</span>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($rows as $r):
                    $term_count = $term_counts[(int)$r->id] ?? 0;
                    $preview    = !empty($r->introduction)
                        ? mb_strimwidth(strip_tags($r->introduction), 0, 90, '…')
                        : 'No introduction set';
                ?>
                <tr>
                    <td>
                        <div class="alt-tname">
                            <?php echo htmlspecialchars($r->name); ?>
                            <small><?php echo htmlspecialchars($preview); ?></small>
                        </div>
                    </td>
                    <td>
                        <?php if ($term_count > 0): ?>
                        <span class="alt-badge">
                            <span class="material-symbols-outlined" style="font-size:13px;">checklist</span>
                            <?php echo $term_count; ?> clause<?php echo $term_count !== 1 ? 's' : ''; ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#d1d5db;font-size:12px;">No clauses</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="alt-actions">
                            <button type="button" class="alt-icon-btn" title="Edit template"
                                    data-row-id="<?php echo (int)$r->id; ?>" onclick="altOpenModal(this.dataset.rowId)">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <a href="<?php echo $base . '/setup/appointment_letter_template/duplicate/' . $r->id; ?>"
                               class="alt-icon-btn" title="Duplicate"
                               onclick="return confirm('Duplicate this template? A copy will be created for you to edit.')">
                                <span class="material-symbols-outlined">content_copy</span>
                            </a>
                            <a href="<?php echo $base . '/setup/appointment_letter_template/delete/' . $r->id; ?>"
                               class="alt-icon-btn danger _delete" title="Delete">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- /.alt-page -->

<!-- Row data stored safely in JS — avoids breaking onclick with HTML/newlines in content -->
<script>
var ALT_ROWS = <?php
    $js_rows = [];
    foreach ($rows as $r) {
        $js_rows[(int)$r->id] = [
            'id'                => (int)$r->id,
            'name'              => (string)($r->name ?? ''),
            'introduction'      => (string)($r->introduction ?? ''),
            'content'           => (string)($r->content ?? ''),
            'closing_statement' => (string)($r->closing_statement ?? ''),
        ];
    }
    echo json_encode($js_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>;
</script>

<!-- ── Add / Edit Modal ───────────────────────────────────────────────────── -->
<div id="alt-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:620px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <form action="<?php echo $base . '/setup/appointment_letter_template'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="alt-id">

            <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #f3f4f6;">
                <h4 id="alt-modal-title" style="font-size:15px;font-weight:700;color:#111827;margin:0;">New Template</h4>
                <button type="button" onclick="altCloseModal()" style="background:none;border:none;font-size:22px;color:#9ca3af;cursor:pointer;line-height:1;">&times;</button>
            </div>

            <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Template Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" id="alt-name" class="form-control" required placeholder="e.g. Engineering Department Appointment Letter">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Introduction Paragraph</label>
                    <textarea name="introduction" id="alt-intro" class="form-control" rows="3"
                        placeholder="Dear {applicant_name}, We are pleased to offer you…"></textarea>
                    <span style="font-size:11px;color:#9ca3af;margin-top:3px;display:block;">Opening paragraph of the letter.</span>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Main Body / Role Description <span style="color:#ef4444;">*</span></label>
                    <textarea name="content" id="alt-content" class="form-control" rows="5" required
                        placeholder="Describe the role, key responsibilities and any role-specific conditions…"></textarea>
                    <span style="font-size:11px;color:#9ca3af;margin-top:3px;display:block;">Placeholders: <strong>{applicant_name} {designation} {salary} {joining_date} {company_name} {department}</strong></span>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px;">Closing Statement</label>
                    <textarea name="closing_statement" id="alt-closing" class="form-control" rows="3"
                        placeholder="Please sign and return a copy to confirm acceptance…"></textarea>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:14px 22px;border-top:1px solid #f3f4f6;">
                <button type="button" onclick="altCloseModal()" class="alt-btn alt-btn-outline">Cancel</button>
                <button type="submit" class="alt-btn alt-btn-primary">
                    <span class="material-symbols-outlined">save</span> Save Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function altOpenModal(rowId) {
    var row = rowId ? ALT_ROWS[parseInt(rowId)] : null;
    document.getElementById('alt-id').value       = row ? row.id : '';
    document.getElementById('alt-name').value     = row ? row.name : '';
    document.getElementById('alt-intro').value    = row ? row.introduction : '';
    document.getElementById('alt-content').value  = row ? row.content : '';
    document.getElementById('alt-closing').value  = row ? row.closing_statement : '';
    document.getElementById('alt-modal-title').textContent = row ? 'Edit Template' : 'New Template';
    document.getElementById('alt-modal').style.display = 'flex';
}
function altCloseModal() {
    document.getElementById('alt-modal').style.display = 'none';
}
document.getElementById('alt-modal').addEventListener('click', function(e) {
    if (e.target === this) altCloseModal();
});
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
