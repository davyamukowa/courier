<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = [
    'pending'  => ['#d97706', '#fef9c3', 'Pending'],
    'approved' => ['#16a34a', '#f0fdf4', 'Approved'],
    'rejected' => ['#dc2626', '#fef2f2', 'Rejected'],
    'used'     => ['#6b7280', '#f3f4f6', 'Used'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.toil-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Header */
.toil-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.toil-header__title { font-size:20px; font-weight:800; color:#111827; margin:0 0 3px; }
.toil-header__sub   { font-size:12px; color:#6b7280; }
.toil-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none; transition:background .15s;
}
.toil-btn--primary { background:#006b2c; color:#fff; }
.toil-btn--primary:hover { background:#005523; color:#fff; }
.toil-btn--outline { background:#fff; border:1.5px solid #d1d5db; color:#374151; }
.toil-btn--outline:hover { background:#f9fafb; border-color:#9ca3af; color:#111827; }

/* Info banner */
.toil-info {
    background: linear-gradient(135deg,#f0fdf4,#dcfce7);
    border:1px solid #bbf7d0; border-radius:12px; padding:14px 18px;
    display:flex; gap:14px; align-items:flex-start; margin-bottom:22px;
}
.toil-info__icon { color:#16a34a; font-size:22px; flex-shrink:0; margin-top:1px; }
.toil-info__title { font-size:13px; font-weight:700; color:#166534; margin-bottom:3px; }
.toil-info__text  { font-size:12px; color:#166534; line-height:1.5; }

/* Stats strip */
.toil-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:22px; }
.toil-stat {
    background:#fff; border-radius:10px; padding:14px 16px;
    box-shadow:0 1px 3px rgba(0,0,0,.06); border-top:3px solid var(--sc,#006b2c);
}
.toil-stat__label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:6px; }
.toil-stat__value { font-size:24px; font-weight:900; color:#111827; }
.toil-stat__sub   { font-size:11px; color:#6b7280; margin-top:2px; }

/* Filter bar */
.toil-filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.toil-filters select, .toil-filters input {
    padding:8px 12px; border:1.5px solid #e5e7eb; border-radius:8px;
    font-size:13px; color:#374151; background:#fff; outline:none;
}
.toil-filters select:focus, .toil-filters input:focus { border-color:#006b2c; }

/* Table card */
.toil-card {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
    overflow:hidden;
}
.toil-card__head {
    padding:14px 18px; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; justify-content:space-between;
}
.toil-card__title { font-size:13px; font-weight:700; color:#111827; }
.toil-table { width:100%; border-collapse:collapse; }
.toil-table thead tr { background:#f9fafb; }
.toil-table th {
    padding:10px 14px; text-align:left; font-size:10.5px;
    font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#6b7280;
    border-bottom:1px solid #f3f4f6;
}
.toil-table td { padding:12px 14px; border-bottom:1px solid #f9fafb; vertical-align:middle; }
.toil-table tbody tr:last-child td { border-bottom:none; }
.toil-table tbody tr:hover { background:#fafafa; }

/* Status badge */
.toil-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700;
}
/* Hours pill */
.toil-hours {
    display:inline-block; padding:3px 10px; border-radius:6px;
    font-size:12px; font-weight:800; background:#f0fdf4; color:#166534;
}

/* Action buttons */
.toil-action-btn {
    display:inline-flex; align-items:center; gap:4px; padding:5px 10px;
    border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer;
    text-decoration:none; transition:background .15s;
}
.toil-action-btn--approve { background:#f0fdf4; color:#16a34a; }
.toil-action-btn--approve:hover { background:#dcfce7; }
.toil-action-btn--reject  { background:#fef2f2; color:#dc2626; }
.toil-action-btn--reject:hover  { background:#fee2e2; }

/* Empty state */
.toil-empty { text-align:center; padding:60px 20px; }
.toil-empty__icon { font-size:48px; color:#d1d5db; margin-bottom:12px; }
.toil-empty__title { font-size:16px; font-weight:700; color:#374151; margin-bottom:6px; }
.toil-empty__sub   { font-size:13px; color:#9ca3af; }

/* Modal */
.toil-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.45);
    z-index:1000; align-items:center; justify-content:center;
}
.toil-overlay.open { display:flex; }
.toil-modal {
    background:#fff; border-radius:16px; width:100%; max-width:480px;
    box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;
}
.toil-modal__head {
    padding:18px 20px 14px; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; justify-content:space-between;
}
.toil-modal__title { font-size:15px; font-weight:800; color:#111827; }
.toil-modal__close { background:none; border:none; cursor:pointer; color:#6b7280; padding:4px; border-radius:6px; }
.toil-modal__close:hover { background:#f3f4f6; }
.toil-modal__body { padding:20px; }
.toil-modal__footer { padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; gap:8px; }

.toil-field { margin-bottom:16px; }
.toil-label { display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; }
.toil-label span { color:#dc2626; }
.toil-input {
    width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:8px;
    font-size:13px; color:#374151; outline:none; box-sizing:border-box;
    transition:border-color .15s;
}
.toil-input:focus { border-color:#006b2c; box-shadow:0 0 0 3px rgba(0,107,44,.08); }
.toil-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
</style>

<div class="toil-page">

    <!-- Header -->
    <div class="toil-header">
        <div>
            <h1 class="toil-header__title">Time Off in Lieu (TOIL)</h1>
            <div class="toil-header__sub">Log overtime worked and convert it to compensatory leave credit.</div>
        </div>
        <button class="toil-btn toil-btn--primary" onclick="openToilModal()">
            <span class="material-symbols-outlined" style="font-size:16px;">add</span>
            Log TOIL Entry
        </button>
    </div>

    <!-- Info banner -->
    <div class="toil-info">
        <span class="material-symbols-outlined toil-info__icon">info</span>
        <div>
            <div class="toil-info__title">How TOIL Works</div>
            <div class="toil-info__text">
                Log the date and hours worked beyond normal working time. Once a manager approves the entry,
                the TOIL hours are credited to your leave balance and can be used as compensatory leave.
            </div>
        </div>
    </div>

    <?php
    $total    = count($toil_entries);
    $pending  = count(array_filter($toil_entries, fn($e) => $e->status === 'pending'));
    $approved = count(array_filter($toil_entries, fn($e) => $e->status === 'approved'));
    $hrs_earned = array_sum(array_map(fn($e) => (float)$e->toil_hours_earned, array_filter($toil_entries, fn($e) => $e->status !== 'rejected')));
    ?>

    <!-- Stats -->
    <div class="toil-stats">
        <div class="toil-stat" style="--sc:#006b2c;">
            <div class="toil-stat__label">Total Entries</div>
            <div class="toil-stat__value"><?php echo $total; ?></div>
            <div class="toil-stat__sub">All time</div>
        </div>
        <div class="toil-stat" style="--sc:#d97706;">
            <div class="toil-stat__label">Pending Approval</div>
            <div class="toil-stat__value"><?php echo $pending; ?></div>
            <div class="toil-stat__sub">Awaiting manager</div>
        </div>
        <div class="toil-stat" style="--sc:#16a34a;">
            <div class="toil-stat__label">Approved</div>
            <div class="toil-stat__value"><?php echo $approved; ?></div>
            <div class="toil-stat__sub">Ready to use</div>
        </div>
        <div class="toil-stat" style="--sc:#2563eb;">
            <div class="toil-stat__label">Hours Earned</div>
            <div class="toil-stat__value"><?php echo number_format($hrs_earned, 1); ?>h</div>
            <div class="toil-stat__sub">Approved + pending</div>
        </div>
    </div>

    <!-- Filter bar -->
    <div class="toil-filters">
        <select onchange="filterTable(this.value,'status')">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="used">Used</option>
        </select>
    </div>

    <!-- Table -->
    <div class="toil-card">
        <div class="toil-card__head">
            <div class="toil-card__title">TOIL Entries</div>
            <div style="font-size:12px;color:#6b7280;"><?php echo $total; ?> record<?php echo $total !== 1 ? 's' : ''; ?></div>
        </div>

        <?php if (empty($toil_entries)): ?>
        <div class="toil-empty">
            <div class="toil-empty__icon"><span class="material-symbols-outlined" style="font-size:48px;">schedule</span></div>
            <div class="toil-empty__title">No TOIL entries yet</div>
            <div class="toil-empty__sub">Log overtime hours worked to start building compensatory leave.</div>
        </div>
        <?php else: ?>
        <table class="toil-table" id="toilTable">
            <thead>
                <tr>
                    <th>Work Date</th>
                    <th>Hours Worked</th>
                    <th>TOIL Earned</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($toil_entries as $entry):
                $sc = $status_colors[$entry->status] ?? ['#6b7280','#f3f4f6','Unknown'];
            ?>
            <tr data-status="<?php echo $entry->status; ?>">
                <td>
                    <div style="font-size:13px;font-weight:600;color:#111827;">
                        <?php echo date('D, d M Y', strtotime($entry->work_date)); ?>
                    </div>
                </td>
                <td>
                    <span class="toil-hours" style="background:#f0f9ff;color:#0369a1;">
                        <?php echo number_format((float)$entry->hours_worked, 1); ?>h worked
                    </span>
                </td>
                <td>
                    <span class="toil-hours">
                        <?php echo number_format((float)$entry->toil_hours_earned, 1); ?>h TOIL
                    </span>
                </td>
                <td>
                    <div style="font-size:12px;color:#374151;max-width:200px;">
                        <?php echo $entry->reason ? htmlspecialchars($entry->reason) : '<span style="color:#9ca3af;">—</span>'; ?>
                    </div>
                </td>
                <td>
                    <span class="toil-badge" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;">
                        <?php echo $sc[2]; ?>
                    </span>
                </td>
                <td>
                    <?php if ($entry->status === 'pending'): ?>
                    <div style="display:flex;gap:6px;">
                        <a href="<?php echo $base; ?>/leave/toil/approve/<?php echo $entry->id; ?>"
                           class="toil-action-btn toil-action-btn--approve"
                           onclick="return confirm('Approve this TOIL entry?')">
                            <span class="material-symbols-outlined" style="font-size:13px;">check</span> Approve
                        </a>
                        <a href="<?php echo $base; ?>/leave/toil/reject/<?php echo $entry->id; ?>"
                           class="toil-action-btn toil-action-btn--reject"
                           onclick="return confirm('Reject this TOIL entry?')">
                            <span class="material-symbols-outlined" style="font-size:13px;">close</span> Reject
                        </a>
                    </div>
                    <?php else: ?>
                    <span style="font-size:11px;color:#9ca3af;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div><!-- /.toil-page -->

<!-- ══ Add TOIL Modal ══════════════════════════════════════ -->
<div class="toil-overlay" id="toilOverlay" onclick="if(event.target===this)closeToilModal()">
    <div class="toil-modal">
        <div class="toil-modal__head">
            <div class="toil-modal__title">Log TOIL Entry</div>
            <button class="toil-modal__close" onclick="closeToilModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="<?php echo $base; ?>/leave/toil">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div class="toil-modal__body">

                <div class="toil-field">
                    <label class="toil-label">Work Date <span>*</span></label>
                    <input type="date" name="work_date" class="toil-input" required
                           max="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="toil-row2">
                    <div class="toil-field">
                        <label class="toil-label">Hours Worked <span>*</span></label>
                        <input type="number" name="hours_worked" class="toil-input"
                               min="0.5" max="24" step="0.5" placeholder="e.g. 3.5" required
                               oninput="calcToilEarned(this.value)">
                    </div>
                    <div class="toil-field">
                        <label class="toil-label">TOIL Hours Earned <span>*</span></label>
                        <input type="number" name="toil_hours_earned" id="toilEarned" class="toil-input"
                               min="0.5" max="24" step="0.5" placeholder="Auto-calculated">
                    </div>
                </div>

                <div class="toil-field">
                    <label class="toil-label">Reason / Description <span>*</span></label>
                    <textarea name="reason" class="toil-input" rows="3" required
                              placeholder="e.g. Worked late to complete product launch preparations..."></textarea>
                </div>

                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;">
                    <strong>Note:</strong> Your manager will review and approve this entry. Once approved, the TOIL hours will be added to your leave balance.
                </div>
            </div>
            <div class="toil-modal__footer">
                <button type="button" class="toil-btn toil-btn--outline" onclick="closeToilModal()">Cancel</button>
                <button type="submit" class="toil-btn toil-btn--primary">
                    <span class="material-symbols-outlined" style="font-size:15px;">save</span>
                    Submit TOIL
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openToilModal()  { document.getElementById('toilOverlay').classList.add('open'); }
function closeToilModal() { document.getElementById('toilOverlay').classList.remove('open'); }

function calcToilEarned(hrs) {
    // Default 1:1 ratio — adjust if your policy differs
    var earned = parseFloat(hrs) || 0;
    document.getElementById('toilEarned').value = earned > 0 ? earned.toFixed(1) : '';
}

function filterTable(val, col) {
    document.querySelectorAll('#toilTable tbody tr').forEach(function(row) {
        if (!val) { row.style.display = ''; return; }
        row.style.display = (row.dataset[col] === val) ? '' : 'none';
    });
}
</script>

<?php init_tail(); ?>
