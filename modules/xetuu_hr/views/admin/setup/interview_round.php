<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'setup';
$base       = admin_url('xetuu_hr');

$total     = (int)($stats_interviews_total ?? 0);
$scheduled = (int)($stats_interviews_scheduled ?? 0);
$completed = (int)($stats_interviews_completed ?? 0);
$cancelled = (int)($stats_interviews_cancelled ?? 0);
$passed    = (int)($stats_interviews_passed ?? 0);
$failed    = (int)($stats_interviews_failed ?? 0);
$pass_rate = $completed > 0 ? round(($passed / $completed) * 100) : 0;

$round_counts = [];
foreach (($stats_by_round ?? []) as $rb) {
    $round_counts[$rb->round_name] = (int)$rb->total;
}
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Page layout ── */
.ir-outer { display:flex; gap:20px; padding:22px 28px; align-items:flex-start; }
.ir-main  { flex:1; min-width:0; }
.ir-side  { width:260px; flex-shrink:0; display:flex; flex-direction:column; gap:14px; }

/* ── Head ── */
.ir-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:18px; }
.ir-head h1 { font-size:18px; font-weight:800; color:#111827; margin:0 0 2px; }
.ir-head p  { font-size:12px; color:#6b7280; margin:0; }

/* ── Toolbar ── */
.ir-toolbar { display:flex; align-items:center; gap:8px; padding:6px 8px;
    background:#f9fafb; border:1px solid #e5e7eb; border-bottom:none;
    border-radius:8px 8px 0 0; }
.ir-tbtn { display:inline-flex; align-items:center; gap:5px; padding:4px 11px;
    border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;
    border:1px solid #e5e7eb; background:#fff; color:#374151; text-decoration:none; white-space:nowrap; }
.ir-tbtn:hover { border-color:#16a34a; color:#16a34a; }
.ir-tbtn.primary { background:#16a34a; border-color:#16a34a; color:#fff; }
.ir-tbtn.primary:hover { background:#15803d; }
.ir-tbtn .material-symbols-outlined { font-size:14px; }
.ir-row-count { margin-left:auto; font-size:11px; color:#9ca3af; }

/* ── Excel grid ── */
.ir-grid-wrap { border:1px solid #d1d5db; border-radius:0 0 8px 8px; overflow:hidden; }
.ir-grid { width:100%; border-collapse:collapse; font-family:'Segoe UI',system-ui,sans-serif; }

.ir-grid .hdr th {
    background:#f3f4f6; font-size:11px; font-weight:700; color:#6b7280;
    text-transform:uppercase; letter-spacing:.4px;
    padding:5px 8px; border:1px solid #d1d5db; user-select:none; white-space:nowrap;
}
.ir-grid .hdr th:first-child { width:36px; text-align:center; background:#f9fafb; }

.ir-grid .dr td {
    padding:6px 8px; border:1px solid #e5e7eb;
    font-size:12.5px; color:#1f2937; vertical-align:top;
}
.ir-grid .dr:hover td { background:#f0fdf4; }
.ir-grid .dr td:first-child  { text-align:center; color:#9ca3af; font-size:11px; background:#f9fafb; width:36px; user-select:none; vertical-align:middle; }
.ir-grid .dr td.name-cell    { font-weight:600; color:#111827; width:30%; white-space:nowrap; vertical-align:middle; }
.ir-grid .dr td.desc-cell    { color:#4b5563; line-height:1.5; }
.ir-grid .dr td.usage-cell   { width:72px; text-align:center; vertical-align:middle; white-space:nowrap; }
.ir-grid .dr td.act-cell     { width:80px; text-align:right; white-space:nowrap; vertical-align:middle; }

.usage-badge { display:inline-block; padding:1px 9px; border-radius:12px; font-size:11px; font-weight:600; }

.cell-btn { display:inline-flex; align-items:center; justify-content:center;
    width:24px; height:24px; border-radius:4px; border:none; background:transparent;
    cursor:pointer; color:#9ca3af; text-decoration:none; }
.cell-btn:hover.edit { color:#16a34a; background:#f0fdf4; }
.cell-btn:hover.del  { color:#dc2626; background:#fef2f2; }
.cell-btn .material-symbols-outlined { font-size:14px; }

.ir-grid .empty-row td { text-align:center; padding:32px; color:#9ca3af; font-size:13px; border:1px solid #e5e7eb; }

.ir-add-row td { padding:3px 6px !important; border-top:2px solid #16a34a !important; vertical-align:middle !important; }
.ir-add-row input, .ir-add-row textarea {
    width:100%; border:none; outline:none; background:transparent;
    font-size:12.5px; font-family:inherit; resize:none; padding:1px 2px;
}
.ir-add-row textarea { height:26px; line-height:1.4; overflow:hidden; }

/* ── Sidebar cards ── */
.side-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
.side-card-head { padding:10px 14px; border-bottom:1px solid #f3f4f6;
    font-size:11px; font-weight:700; color:#374151;
    text-transform:uppercase; letter-spacing:.05em; display:flex; justify-content:space-between; align-items:center; }
.side-card-body { padding:12px 14px; }

/* Progress bar rows */
.stat-row { margin-bottom:11px; }
.stat-row:last-child { margin-bottom:0; }
.stat-row-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:3px; }
.stat-row-head span { font-size:12px; color:#374151; font-weight:500; }
.stat-bar { height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden; }
.stat-bar-fill { height:100%; border-radius:3px; transition:width .4s; }
.stat-sub { font-size:10px; color:#9ca3af; margin-top:2px; }

/* Pass/Fail boxes */
.pf-boxes { display:flex; gap:6px; margin-top:12px; }
.pf-box { flex:1; border-radius:7px; padding:8px 6px; text-align:center; }
.pf-box .pf-val { font-size:18px; font-weight:800; line-height:1; }
.pf-box .pf-lbl { font-size:10px; margin-top:2px; opacity:.8; }

/* Most used rounds */
.usage-row { display:flex; align-items:center; gap:8px; padding:5px 0; border-bottom:1px solid #f9fafb; }
.usage-row:last-child { border-bottom:none; }
.usage-bar { flex:1; height:4px; background:#f3f4f6; border-radius:2px; }
.usage-bar-fill { height:100%; background:#16a34a; border-radius:2px; }

/* Recent interviews */
.recent-row { padding:7px 0; border-bottom:1px solid #f9fafb; }
.recent-row:last-child { border-bottom:none; }
.status-pip { display:inline-block; padding:1px 7px; border-radius:10px; font-size:10px; font-weight:600; }

/* Quick links */
.qlink { display:flex; align-items:center; gap:8px; padding:7px 14px;
    text-decoration:none; font-size:13px; color:#4b5563; }
.qlink:hover { background:#f9fafb; }
.qlink.active { color:#16a34a; font-weight:600; background:#f0fdf4; }
.qlink .material-symbols-outlined { font-size:16px; color:#9ca3af; }
.qlink.active .material-symbols-outlined { color:#16a34a; }
</style>

<div class="ir-outer">

    <!-- ── Left: main grid ─────────────────────────────────────────────── -->
    <div class="ir-main">

        <div class="ir-head">
            <div>
                <h1>Interview Rounds</h1>
                <p>Evaluation stages candidates go through (e.g. Round 1 – Technical, Final – HR).</p>
            </div>
        </div>

        <div class="ir-toolbar">
            <button type="button" class="ir-tbtn primary" onclick="irAddRow()">
                <span class="material-symbols-outlined">add</span> Add Row
            </button>
            <div style="width:1px;height:20px;background:#e5e7eb;margin:0 2px;"></div>
            <button type="button" class="ir-tbtn" onclick="irOpenModal(null)">
                <span class="material-symbols-outlined">edit_note</span> Form Editor
            </button>
            <span class="ir-row-count"><?php echo count($rows); ?> round<?php echo count($rows) !== 1 ? 's' : ''; ?></span>
        </div>

        <div class="ir-grid-wrap">
            <table class="ir-grid">
                <thead>
                    <tr class="hdr">
                        <th>#</th>
                        <th>Round Name</th>
                        <th>Description</th>
                        <th style="text-align:center;">Used</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="ir-tbody">
                    <?php if (empty($rows)): ?>
                    <tr class="empty-row" id="ir-empty">
                        <td colspan="5">No interview rounds yet. Click <strong>Add Row</strong> to add one.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $i => $r):
                        $cnt = $round_counts[$r->name] ?? 0;
                    ?>
                    <tr class="dr" data-id="<?php echo $r->id; ?>">
                        <td><?php echo $i + 1; ?></td>
                        <td class="name-cell"><?php echo htmlspecialchars($r->name); ?></td>
                        <td class="desc-cell"><?php echo nl2br(htmlspecialchars($r->description ?? '')); ?></td>
                        <td class="usage-cell">
                            <span class="usage-badge" style="background:<?php echo $cnt > 0 ? '#dcfce7' : '#f3f4f6'; ?>;color:<?php echo $cnt > 0 ? '#16a34a' : '#9ca3af'; ?>;">
                                <?php echo $cnt; ?>
                            </span>
                        </td>
                        <td class="act-cell">
                            <button class="cell-btn edit" title="Edit" onclick="irOpenModal(<?php echo $r->id; ?>)">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <a href="<?php echo $base . '/setup/interview_round/delete/' . $r->id; ?>"
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

    </div><!-- /.ir-main -->

    <!-- ── Right: sidebar ─────────────────────────────────────────────── -->
    <div class="ir-side">

        <!-- Interview Status -->
        <div class="side-card">
            <div class="side-card-head">Interview Status</div>
            <div class="side-card-body">
                <?php
                $stat_rows = [
                    ['Scheduled', $scheduled, '#854d0e', '#fef9c3'],
                    ['Completed', $completed, '#16a34a', '#dcfce7'],
                    ['Cancelled', $cancelled, '#dc2626', '#fee2e2'],
                ];
                foreach ($stat_rows as [$slabel, $sval, $scolor, $sbg]):
                    $spct = $total > 0 ? round(($sval / $total) * 100) : 0;
                ?>
                <div class="stat-row">
                    <div class="stat-row-head">
                        <span><?php echo $slabel; ?></span>
                        <span style="font-weight:700;color:<?php echo $scolor; ?>;"><?php echo $sval; ?></span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?php echo $spct; ?>%;background:<?php echo $scolor; ?>;"></div>
                    </div>
                    <div class="stat-sub"><?php echo $spct; ?>% of <?php echo $total; ?> total</div>
                </div>
                <?php endforeach; ?>

                <!-- Pass / Fail -->
                <div style="border-top:1px solid #f3f4f6;padding-top:10px;margin-top:4px;">
                    <div style="font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Results (Completed)</div>
                    <div class="pf-boxes">
                        <div class="pf-box" style="background:#f0fdf4;">
                            <div class="pf-val" style="color:#16a34a;"><?php echo $passed; ?></div>
                            <div class="pf-lbl" style="color:#15803d;">Pass</div>
                        </div>
                        <div class="pf-box" style="background:#fef2f2;">
                            <div class="pf-val" style="color:#dc2626;"><?php echo $failed; ?></div>
                            <div class="pf-lbl" style="color:#b91c1c;">Fail</div>
                        </div>
                        <div class="pf-box" style="background:#f9fafb;">
                            <div class="pf-val" style="color:#374151;"><?php echo $pass_rate; ?>%</div>
                            <div class="pf-lbl" style="color:#6b7280;">Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Used Rounds -->
        <?php if (!empty($stats_by_round)): ?>
        <div class="side-card">
            <div class="side-card-head">Most Used Rounds</div>
            <div class="side-card-body" style="padding:8px 14px;">
                <?php foreach ($stats_by_round as $rb):
                    $upct = $total > 0 ? round(($rb->total / $total) * 100) : 0;
                ?>
                <div class="usage-row">
                    <span class="material-symbols-outlined" style="font-size:13px;color:#16a34a;flex-shrink:0;">sync</span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($rb->round_name ?? 'Unassigned'); ?>
                        </div>
                        <div class="usage-bar"><div class="usage-bar-fill" style="width:<?php echo $upct; ?>%;"></div></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:#16a34a;flex-shrink:0;"><?php echo $rb->total; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Interviews -->
        <?php if (!empty($stats_recent)): ?>
        <div class="side-card">
            <div class="side-card-head">
                Recent Interviews
                <a href="<?php echo $base . '/recruitment/interviews'; ?>" style="font-size:11px;color:#16a34a;text-decoration:none;font-weight:500;text-transform:none;letter-spacing:0;">View all</a>
            </div>
            <div style="padding:4px 0;">
                <?php foreach ($stats_recent as $ri):
                    $ri_color = ['Scheduled' => '#854d0e', 'Completed' => '#16a34a', 'Cancelled' => '#dc2626'][$ri->status] ?? '#6b7280';
                    $ri_bg    = ['Scheduled' => '#fef9c3', 'Completed' => '#dcfce7', 'Cancelled' => '#fee2e2'][$ri->status] ?? '#f3f4f6';
                ?>
                <div class="recent-row" style="padding:7px 14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">
                        <div style="font-size:12px;font-weight:600;color:#111827;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($ri->applicant_name ?? '—'); ?>
                        </div>
                        <span class="status-pip" style="background:<?php echo $ri_bg; ?>;color:<?php echo $ri_color; ?>;flex-shrink:0;">
                            <?php echo $ri->status; ?>
                        </span>
                    </div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                        <?php echo $ri->round_name ? htmlspecialchars($ri->round_name) : 'No round'; ?>
                        · <?php echo date('d M', strtotime($ri->interview_date)); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Links -->
        <div class="side-card">
            <div class="side-card-head">Interview Setup</div>
            <div style="padding:4px 0;">
                <?php
                $qlinks = [
                    ['Interview Types',  'category',          $base . '/setup/interview_type',           false],
                    ['Interview Rounds', 'sync',              $base . '/setup/interview_round',          true],
                    ['Letter Templates', 'receipt_long',      $base . '/setup/appointment_letter_template', false],
                    ['All Interviews',   'record_voice_over', $base . '/recruitment/interviews',         false],
                ];
                foreach ($qlinks as [$ql, $qi, $qu, $qa]): ?>
                <a href="<?php echo $qu; ?>" class="qlink <?php echo $qa ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined"><?php echo $qi; ?></span>
                    <?php echo $ql; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- /.ir-side -->

</div><!-- /.ir-outer -->

<!-- JS row data -->
<script>
var IR_ROWS = <?php
    $js = [];
    foreach ($rows as $r) {
        $skills = [];
        if (!empty($skills_by_round[$r->id])) $skills = $skills_by_round[$r->id];
        $js[(int)$r->id] = [
            'id' => (int)$r->id,
            'name' => (string)$r->name,
            'description' => (string)($r->description ?? ''),
            'skills' => $skills,
        ];
    }
    echo json_encode($js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>;
</script>

<!-- Modal (form editor) -->
<div id="ir-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.2);">
        <form action="<?php echo $base . '/setup/interview_round'; ?>" method="post">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="ir-m-id">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6;">
                <h4 id="ir-m-title" style="margin:0;font-size:14px;font-weight:700;color:#111827;">Add Interview Round</h4>
                <button type="button" onclick="irCloseModal()" style="background:none;border:none;font-size:20px;color:#9ca3af;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Round Name *</label>
                    <input type="text" name="name" id="ir-m-name" class="form-control" required placeholder="e.g. Round 1 – Technical">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Description</label>
                    <textarea name="description" id="ir-m-desc" class="form-control" rows="2" placeholder="What this round evaluates…"></textarea>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">
                        Expected Skillset
                        <span style="text-transform:none;font-weight:400;font-size:10px;color:#9ca3af;"> — one per line</span>
                    </label>
                    <textarea name="skills_text" id="ir-m-skills" class="form-control" rows="4"
                        style="font-family:monospace;font-size:12px;"
                        placeholder="Python&#10;SQL&#10;Problem-solving&#10;Communication"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;padding:10px 18px;border-top:1px solid #f3f4f6;">
                <button type="button" onclick="irCloseModal()" style="padding:7px 14px;border-radius:7px;border:1.5px solid #e5e7eb;background:#fff;font-size:12px;font-weight:600;cursor:pointer;color:#374151;">Cancel</button>
                <button type="submit" style="padding:7px 16px;border-radius:7px;border:none;background:#16a34a;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Inline add form -->
<form id="ir-add-form" action="<?php echo $base . '/setup/interview_round'; ?>" method="post" style="display:none;">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <input type="hidden" name="id" value="">
</form>

<script>
function irOpenModal(id) {
    var row = id ? IR_ROWS[id] : null;
    document.getElementById('ir-m-id').value    = row ? row.id : '';
    document.getElementById('ir-m-name').value  = row ? row.name : '';
    document.getElementById('ir-m-desc').value  = row ? row.description : '';
    document.getElementById('ir-m-skills').value = row ? (row.skills || []).join('\n') : '';
    document.getElementById('ir-m-title').textContent = row ? 'Edit Interview Round' : 'Add Interview Round';
    document.getElementById('ir-modal').style.display = 'flex';
    setTimeout(function(){ document.getElementById('ir-m-name').focus(); }, 80);
}
function irCloseModal() { document.getElementById('ir-modal').style.display = 'none'; }
document.getElementById('ir-modal').addEventListener('click', function(e){ if(e.target===this) irCloseModal(); });

function irAddRow() {
    var empty = document.getElementById('ir-empty'); if (empty) empty.remove();
    var existing = document.getElementById('ir-inline-row'); if (existing) { existing.remove(); return; }
    var tbody = document.getElementById('ir-tbody');
    var rowNum = tbody.querySelectorAll('tr.dr').length + 1;
    var tr = document.createElement('tr');
    tr.id = 'ir-inline-row'; tr.className = 'ir-add-row';
    tr.innerHTML = [
        '<td style="text-align:center;color:#9ca3af;font-size:11px;background:#f9fafb;width:36px;">' + rowNum + '</td>',
        '<td><input type="text" id="ir-inline-name" placeholder="Round name" required></td>',
        '<td><textarea id="ir-inline-desc" placeholder="Description (optional)" rows="1"></textarea></td>',
        '<td></td>',
        '<td style="text-align:right;white-space:nowrap;">',
        '<button type="button" onclick="irSaveInline()" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:5px;border:none;background:#16a34a;color:#fff;cursor:pointer;"><span class="material-symbols-outlined" style="font-size:14px;">check</span></button> ',
        '<button type="button" onclick="irCancelInline()" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:5px;border:none;background:#f3f4f6;color:#6b7280;cursor:pointer;"><span class="material-symbols-outlined" style="font-size:14px;">close</span></button>',
        '</td>',
    ].join('');
    tbody.appendChild(tr);
    document.getElementById('ir-inline-name').focus();
}
function irSaveInline() {
    var name = document.getElementById('ir-inline-name').value.trim();
    if (!name) { document.getElementById('ir-inline-name').focus(); return; }
    var form = document.getElementById('ir-add-form');
    var nInp = document.createElement('input'); nInp.type='hidden'; nInp.name='name'; nInp.value=name;
    var dInp = document.createElement('input'); dInp.type='hidden'; dInp.name='description'; dInp.value=document.getElementById('ir-inline-desc').value.trim();
    form.appendChild(nInp); form.appendChild(dInp);
    form.submit();
}
function irCancelInline() {
    var tr = document.getElementById('ir-inline-row'); if (tr) tr.remove();
    var tbody = document.getElementById('ir-tbody');
    if (!tbody.querySelector('tr.dr')) {
        var e = document.createElement('tr'); e.id='ir-empty'; e.className='empty-row';
        e.innerHTML='<td colspan="5">No interview rounds yet. Click <strong>Add Row</strong> to add one.</td>';
        tbody.appendChild(e);
    }
}
document.addEventListener('keydown', function(e) {
    if (e.target.id==='ir-inline-name' && e.key==='Enter') { e.preventDefault(); document.getElementById('ir-inline-desc').focus(); }
    if (e.target.id==='ir-inline-desc' && e.key==='Enter' && !e.shiftKey) { e.preventDefault(); irSaveInline(); }
    if (e.key==='Escape') { irCancelInline(); irCloseModal(); }
});
</script>

</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<?php init_tail(); ?>
