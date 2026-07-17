<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$status_colors = [
    'pending'   => ['#d97706','#fef9c3','Pending'],
    'processed' => ['#16a34a','#f0fdf4','Processed'],
    'cancelled' => ['#6b7280','#f3f4f6','Cancelled'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.enc-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Header */
.enc-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.enc-header__title { font-size:20px; font-weight:800; color:#111827; margin:0 0 3px; }
.enc-header__sub   { font-size:12px; color:#6b7280; }
.enc-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none; transition:background .15s;
}
.enc-btn--primary { background:#006b2c; color:#fff; }
.enc-btn--primary:hover { background:#005523; color:#fff; }
.enc-btn--outline { background:#fff; border:1.5px solid #d1d5db; color:#374151; }
.enc-btn--outline:hover { background:#f9fafb; border-color:#9ca3af; color:#111827; }
.enc-btn--danger  { background:#dc2626; color:#fff; }
.enc-btn--danger:hover  { background:#b91c1c; color:#fff; }

/* Info banner */
.enc-info {
    background:linear-gradient(135deg,#fffbeb,#fef9c3);
    border:1px solid #fde68a; border-radius:12px; padding:14px 18px;
    display:flex; gap:14px; align-items:flex-start; margin-bottom:22px;
}
.enc-info__icon  { color:#d97706; font-size:22px; flex-shrink:0; margin-top:1px; }
.enc-info__title { font-size:13px; font-weight:700; color:#92400e; margin-bottom:3px; }
.enc-info__text  { font-size:12px; color:#92400e; line-height:1.5; }

/* Stats strip */
.enc-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:22px; }
.enc-stat {
    background:#fff; border-radius:10px; padding:14px 16px;
    box-shadow:0 1px 3px rgba(0,0,0,.06); border-top:3px solid var(--sc,#006b2c);
}
.enc-stat__label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:6px; }
.enc-stat__value { font-size:24px; font-weight:900; color:#111827; }
.enc-stat__sub   { font-size:11px; color:#6b7280; margin-top:2px; }

/* Table card */
.enc-card {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 3px rgba(0,0,0,.06); overflow:hidden;
}
.enc-card__head {
    padding:14px 18px; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
}
.enc-card__title { font-size:13px; font-weight:700; color:#111827; }
.enc-table { width:100%; border-collapse:collapse; }
.enc-table thead tr { background:#f9fafb; }
.enc-table th {
    padding:10px 14px; text-align:left; font-size:10.5px;
    font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#6b7280;
    border-bottom:1px solid #f3f4f6;
}
.enc-table td { padding:13px 14px; border-bottom:1px solid #f9fafb; vertical-align:middle; }
.enc-table tbody tr:last-child td { border-bottom:none; }
.enc-table tbody tr:hover { background:#fafafa; }

/* Status badge */
.enc-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700;
}

/* Amount display */
.enc-amount {
    font-size:15px; font-weight:800; color:#111827;
}
.enc-days {
    display:inline-block; padding:2px 8px; border-radius:5px;
    background:#f0fdf4; color:#166534; font-size:11px; font-weight:700;
}

/* Empty state */
.enc-empty { text-align:center; padding:60px 20px; }
.enc-empty__icon  { font-size:48px; color:#d1d5db; margin-bottom:12px; }
.enc-empty__title { font-size:16px; font-weight:700; color:#374151; margin-bottom:6px; }
.enc-empty__sub   { font-size:13px; color:#9ca3af; }

/* Modal */
.enc-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.45);
    z-index:1000; align-items:center; justify-content:center;
}
.enc-overlay.open { display:flex; }
.enc-modal {
    background:#fff; border-radius:16px; width:100%; max-width:500px;
    box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;
}
.enc-modal__head {
    padding:18px 20px 14px; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; justify-content:space-between;
}
.enc-modal__title  { font-size:15px; font-weight:800; color:#111827; }
.enc-modal__close  { background:none; border:none; cursor:pointer; color:#6b7280; padding:4px; border-radius:6px; }
.enc-modal__close:hover { background:#f3f4f6; }
.enc-modal__body   { padding:20px; }
.enc-modal__footer { padding:14px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; gap:8px; }

.enc-field { margin-bottom:16px; }
.enc-label { display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; }
.enc-label span { color:#dc2626; }
.enc-input {
    width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:8px;
    font-size:13px; color:#374151; outline:none; box-sizing:border-box;
    transition:border-color .15s;
}
.enc-input:focus { border-color:#006b2c; box-shadow:0 0 0 3px rgba(0,107,44,.08); }
.enc-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

/* Calc preview */
.enc-calc-preview {
    background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;
    padding:12px 16px; margin-top:4px;
}
.enc-calc-preview__row { display:flex; justify-content:space-between; font-size:12px; color:#166534; margin-bottom:4px; }
.enc-calc-preview__row:last-child { margin-bottom:0; font-weight:800; font-size:13px; }
</style>

<div class="enc-page">

    <!-- Header -->
    <div class="enc-header">
        <div>
            <h1 class="enc-header__title">Leave Encashment</h1>
            <div class="enc-header__sub">Convert unused leave balance into a cash payout.</div>
        </div>
        <button class="enc-btn enc-btn--primary" onclick="openEncModal()">
            <span class="material-symbols-outlined" style="font-size:16px;">add</span>
            New Encashment Request
        </button>
    </div>

    <!-- Info banner -->
    <div class="enc-info">
        <span class="material-symbols-outlined enc-info__icon">payments</span>
        <div>
            <div class="enc-info__title">Leave Encashment Policy</div>
            <div class="enc-info__text">
                Employees may encash unused Annual Leave days at the end of the year or upon separation.
                The payout amount is calculated based on the employee's daily rate.
                Encashments are processed through payroll on the next payslip.
            </div>
        </div>
    </div>

    <?php
    $total_enc   = count($encashments);
    $pending_enc = count(array_filter($encashments, fn($e) => $e->status === 'pending'));
    $processed   = count(array_filter($encashments, fn($e) => $e->status === 'processed'));
    $total_days  = array_sum(array_column($encashments, 'days_encashed'));
    $total_amt   = array_sum(array_column($encashments, 'amount'));
    ?>

    <!-- Stats -->
    <div class="enc-stats">
        <div class="enc-stat" style="--sc:#006b2c;">
            <div class="enc-stat__label">Total Requests</div>
            <div class="enc-stat__value"><?php echo $total_enc; ?></div>
            <div class="enc-stat__sub">All time</div>
        </div>
        <div class="enc-stat" style="--sc:#d97706;">
            <div class="enc-stat__label">Pending</div>
            <div class="enc-stat__value"><?php echo $pending_enc; ?></div>
            <div class="enc-stat__sub">Awaiting processing</div>
        </div>
        <div class="enc-stat" style="--sc:#16a34a;">
            <div class="enc-stat__label">Processed</div>
            <div class="enc-stat__value"><?php echo $processed; ?></div>
            <div class="enc-stat__sub">Paid out</div>
        </div>
        <div class="enc-stat" style="--sc:#2563eb;">
            <div class="enc-stat__label">Total Amount</div>
            <div class="enc-stat__value"><?php echo number_format($total_amt, 0); ?></div>
            <div class="enc-stat__sub"><?php echo number_format($total_days, 1); ?> days encashed</div>
        </div>
    </div>

    <!-- Table -->
    <div class="enc-card">
        <div class="enc-card__head">
            <div class="enc-card__title">Encashment Records</div>
            <div style="font-size:12px;color:#6b7280;"><?php echo $total_enc; ?> record<?php echo $total_enc !== 1 ? 's' : ''; ?></div>
        </div>

        <?php if (empty($encashments)): ?>
        <div class="enc-empty">
            <div class="enc-empty__icon"><span class="material-symbols-outlined" style="font-size:48px;">savings</span></div>
            <div class="enc-empty__title">No encashment records yet</div>
            <div class="enc-empty__sub">Create a request to convert unused leave days into a cash payout.</div>
        </div>
        <?php else: ?>
        <table class="enc-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Days Encashed</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($encashments as $enc):
                $sc = $status_colors[$enc->status] ?? ['#6b7280','#f3f4f6','Unknown'];
            ?>
            <tr>
                <td>
                    <?php
                    $name = $enc->employee_name ?? 'Unknown';
                    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $name), 0, 2)));
                    ?>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:#006b2c;
                                    color:#fff;font-size:12px;font-weight:800;display:flex;
                                    align-items:center;justify-content:center;flex-shrink:0;">
                            <?php echo $initials; ?>
                        </div>
                        <div style="font-size:13px;font-weight:600;color:#111827;">
                            <?php echo htmlspecialchars($name); ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size:12px;font-weight:600;color:#374151;">
                        <?php echo htmlspecialchars($enc->leave_type_name ?? '—'); ?>
                    </span>
                </td>
                <td>
                    <span class="enc-days"><?php echo number_format((float)$enc->days_encashed, 1); ?> days</span>
                </td>
                <td>
                    <div class="enc-amount">
                        <?php echo number_format((float)$enc->amount, 2); ?>
                    </div>
                </td>
                <td>
                    <span class="enc-badge" style="color:<?php echo $sc[0]; ?>;background:<?php echo $sc[1]; ?>;">
                        <?php echo $sc[2]; ?>
                    </span>
                </td>
                <td>
                    <div style="font-size:12px;color:#6b7280;max-width:160px;">
                        <?php echo $enc->notes ? htmlspecialchars($enc->notes) : '<span style="color:#d1d5db;">—</span>'; ?>
                    </div>
                </td>
                <td style="font-size:12px;color:#6b7280;white-space:nowrap;">
                    <?php echo date('d M Y', strtotime($enc->date_created)); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div><!-- /.enc-page -->

<!-- ══ Add Encashment Modal ═══════════════════════════════ -->
<div class="enc-overlay" id="encOverlay" onclick="if(event.target===this)closeEncModal()">
    <div class="enc-modal">
        <div class="enc-modal__head">
            <div class="enc-modal__title">New Encashment Request</div>
            <button class="enc-modal__close" onclick="closeEncModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="<?php echo $base; ?>/leave/encashment">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div class="enc-modal__body">

                <div class="enc-field">
                    <label class="enc-label">Leave Type <span>*</span></label>
                    <select name="leave_type_id" class="enc-input" required onchange="updateCalc()">
                        <option value="">— Select leave type —</option>
                        <?php foreach ($leave_types as $lt): ?>
                        <option value="<?php echo $lt->id; ?>"><?php echo htmlspecialchars($lt->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="enc-row2">
                    <div class="enc-field">
                        <label class="enc-label">Days to Encash <span>*</span></label>
                        <input type="number" name="days_encashed" id="encDays" class="enc-input"
                               min="1" max="90" step="0.5" placeholder="e.g. 5" required
                               oninput="updateCalc()">
                    </div>
                    <div class="enc-field">
                        <label class="enc-label">Daily Rate (<?php echo get_base_currency() ? get_base_currency()->symbol : 'KES'; ?>)</label>
                        <input type="number" name="_daily_rate" id="encDailyRate" class="enc-input"
                               min="0" step="0.01" placeholder="e.g. 3500.00"
                               oninput="updateCalc()">
                        <div style="font-size:10px;color:#9ca3af;margin-top:4px;">Used only to compute payout</div>
                    </div>
                </div>

                <!-- Calculation preview -->
                <div class="enc-calc-preview" id="encCalcPreview" style="display:none;">
                    <div class="enc-calc-preview__row">
                        <span>Days × Daily Rate</span>
                        <span id="encCalcDetail">—</span>
                    </div>
                    <div class="enc-calc-preview__row">
                        <span>Estimated Payout</span>
                        <span id="encCalcTotal">—</span>
                    </div>
                </div>

                <!-- Hidden amount field — populated by JS -->
                <input type="hidden" name="amount" id="encAmountHidden" value="0">

                <div class="enc-field" style="margin-top:16px;">
                    <label class="enc-label">Notes</label>
                    <textarea name="notes" class="enc-input" rows="3"
                              placeholder="e.g. Year-end encashment for unused annual leave balance..."></textarea>
                </div>

                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;">
                    <strong>Note:</strong> Encashment requests are reviewed by HR and processed on the next payroll run.
                </div>
            </div>
            <div class="enc-modal__footer">
                <button type="button" class="enc-btn enc-btn--outline" onclick="closeEncModal()">Cancel</button>
                <button type="submit" class="enc-btn enc-btn--primary">
                    <span class="material-symbols-outlined" style="font-size:15px;">save</span>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEncModal()  { document.getElementById('encOverlay').classList.add('open'); }
function closeEncModal() { document.getElementById('encOverlay').classList.remove('open'); }

function updateCalc() {
    var days = parseFloat(document.getElementById('encDays').value) || 0;
    var rate = parseFloat(document.getElementById('encDailyRate').value) || 0;
    var preview = document.getElementById('encCalcPreview');

    if (days > 0 && rate > 0) {
        var total = days * rate;
        document.getElementById('encCalcDetail').textContent = days + ' × ' + rate.toLocaleString('en-KE', {minimumFractionDigits:2});
        document.getElementById('encCalcTotal').textContent  = total.toLocaleString('en-KE', {minimumFractionDigits:2});
        document.getElementById('encAmountHidden').value     = total.toFixed(2);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
        document.getElementById('encAmountHidden').value = 0;
    }
}
</script>

<?php init_tail(); ?>
