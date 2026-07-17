<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$preselect_type = (int)($this->input->get('type') ?? 0);
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-apply-wrap {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    padding: 24px;
    max-width: 1100px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
@media (max-width: 900px) { .lv-apply-wrap { grid-template-columns: 1fr; } }

/* Type selector */
.lv-type-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: 10px; margin-bottom: 20px; }
.lv-type-pill {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    cursor: pointer;
    transition: all .15s;
    background: #fff;
    position: relative;
}
.lv-type-pill:hover { border-color: var(--tc); box-shadow: 0 2px 10px rgba(0,0,0,.08); }
.lv-type-pill.selected { border-color: var(--tc); background: var(--tc-bg); box-shadow: 0 2px 14px rgba(0,0,0,.1); }
.lv-type-pill.selected::after {
    content: '✓';
    position: absolute; top: 6px; right: 8px;
    font-size: 10px; font-weight: 900;
    color: var(--tc);
}
.lv-type-pill__name { font-size: 11px; font-weight: 700; color: #111827; margin-bottom: 3px; }
.lv-type-pill__bal  { font-size: 18px; font-weight: 900; color: var(--tc); line-height: 1; }
.lv-type-pill__unit { font-size: 10px; color: #6b7280; }

/* Form fields */
.lv-field { margin-bottom: 16px; }
.lv-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: 5px; }
.lv-label .req { color: #dc2626; }

/* Date range */
.lv-date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* Working days badge */
.lv-days-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 8px; padding: 8px 16px;
    font-size: 14px; font-weight: 800;
    margin: 10px 0;
}
.lv-days-badge .material-symbols-outlined { font-size: 18px; }

/* Half-day toggle */
.lv-toggle-wrap { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.lv-toggle { position: relative; width: 40px; height: 22px; }
.lv-toggle input { opacity: 0; width: 0; height: 0; }
.lv-toggle-slider {
    position: absolute; inset: 0;
    background: #d1d5db; border-radius: 11px;
    cursor: pointer; transition: background .2s;
}
.lv-toggle-slider::before {
    content: ''; position: absolute;
    width: 16px; height: 16px; left: 3px; top: 3px;
    background: #fff; border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.lv-toggle input:checked + .lv-toggle-slider { background: #2563eb; }
.lv-toggle input:checked + .lv-toggle-slider::before { transform: translateX(18px); }
.lv-toggle-label { font-size: 13px; font-weight: 600; color: #374151; }

/* Summary card */
.lv-summary-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
    border-radius: 14px;
    padding: 20px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(30,58,95,.3);
}
.lv-summary-card__type { font-size: 12px; opacity: .7; margin-bottom: 4px; }
.lv-summary-card__days { font-size: 40px; font-weight: 900; line-height: 1; }
.lv-summary-card__unit { font-size: 14px; opacity: .8; }
.lv-summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.12); font-size: 12px; }
.lv-summary-row:last-child { border-bottom: none; }
.lv-summary-row__label { opacity: .7; }
.lv-summary-row__val { font-weight: 700; }

/* Submit button */
.lv-submit-btn {
    width: 100%; padding: 14px;
    background: #16a34a; color: #fff;
    border: none; border-radius: 10px;
    font-size: 15px; font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(22,163,74,.3);
    transition: transform .15s, box-shadow .15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.lv-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(22,163,74,.4); }
.lv-submit-btn:disabled { background: #9ca3af; box-shadow: none; transform: none; cursor: not-allowed; }

/* Balance mini bar */
.lv-mini-bar { height: 4px; background: rgba(255,255,255,.2); border-radius: 2px; margin-top: 8px; overflow: hidden; }
.lv-mini-bar__fill { height: 100%; background: #fff; border-radius: 2px; transition: width .4s; }
</style>

<div class="lv-apply-wrap">
    <!-- Left: Form -->
    <div>
        <!-- Breadcrumb -->
        <div style="font-size:11px;color:#6b7280;margin-bottom:14px;">
            <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> / Apply for Leave
        </div>
        <h1 style="font-size:20px;font-weight:800;color:#111827;margin:0 0 20px;">
            Apply for Leave
        </h1>

        <form method="post" action="<?php echo $base; ?>/leave/apply" id="leave-apply-form" enctype="multipart/form-data">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="leave_type_id" id="lf-type-id" value="<?php echo $preselect_type; ?>">
            <input type="hidden" name="total_days" id="lf-total-days" value="0">
            <input type="hidden" name="total_hours" id="lf-total-hours" value="0">

            <?php if (!empty($can_apply_behalf)): ?>
            <!-- On Behalf Of (Admin/HR only) -->
            <div style="background:linear-gradient(135deg,#fffbeb,#fef9c3);border:1px solid #fde68a;border-radius:14px;padding:16px 20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    <span class="material-symbols-outlined" style="font-size:16px;">admin_panel_settings</span>
                    HR / Admin — Apply on Behalf of Employee
                </div>
                <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:flex-end;">
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:4px;">Employee</label>
                        <select name="on_behalf_of_id" id="behalf-emp" class="form-control" style="font-size:13px;" onchange="onBehalfChanged(this.value)">
                            <option value="">— Apply for myself —</option>
                            <?php foreach ($on_behalf_employees as $obemp): ?>
                            <option value="<?php echo $obemp->id; ?>"><?php echo htmlspecialchars($obemp->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (is_admin()): ?>
                    <div id="auto-approve-wrap" style="display:none;padding-bottom:2px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;background:#fff;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;white-space:nowrap;">
                            <input type="checkbox" name="admin_auto_approve" value="1" style="width:14px;height:14px;">
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#92400e;">Skip approval queue</div>
                                <div style="font-size:10px;color:#b45309;">Mark as fully approved</div>
                            </div>
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <div id="behalf-balance-notice" style="display:none;margin-top:10px;font-size:12px;color:#92400e;"></div>
            </div>
            <?php endif; ?>

            <!-- Step 1: Leave Type -->
            <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);margin-bottom:16px;">
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                    <span style="background:#eff6ff;color:#2563eb;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;">1</span>
                    Select Leave Type
                </div>
                <div class="lv-type-grid">
                    <?php if (!empty($leave_types)): ?>
                    <?php foreach ($leave_types as $lt):
                        $bal_row = null;
                        foreach (($balance_summary ?? []) as $b) {
                            if ($b->leave_type_id == $lt->id) { $bal_row = $b; break; }
                        }
                        $remaining = $bal_row ? max(0, ((float)$bal_row->total_days + (float)$bal_row->carried_forward_days) - (float)$bal_row->used_days) : 0;
                        $color = $lt->color ?? '#2563eb';
                        $hex   = ltrim($color,'#');
                        $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b_hex = hexdec(substr($hex,4,2));
                        $bg = "rgba($r,$g,$b_hex,.1)";
                        $selected = ((int)$preselect_type === (int)$lt->id) ? 'selected' : '';
                    ?>
                    <div class="lv-type-pill <?php echo $selected; ?>"
                         style="--tc:<?php echo $color; ?>; --tc-bg:<?php echo $bg; ?>;"
                         onclick="selectType(<?php echo $lt->id; ?>, '<?php echo addslashes($lt->name); ?>', <?php echo $remaining; ?>, '<?php echo $lt->unit; ?>', '<?php echo $color; ?>', <?php echo $lt->allow_half_day ?? 1; ?>)"
                         data-type-id="<?php echo $lt->id; ?>">
                        <div class="lv-type-pill__name"><?php echo htmlspecialchars($lt->name); ?></div>
                        <div class="lv-type-pill__bal"><?php echo number_format($remaining, 1); ?></div>
                        <div class="lv-type-pill__unit"><?php echo $lt->unit === 'hours' ? 'hrs available' : 'days available'; ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div style="grid-column:1/-1;text-align:center;padding:20px;color:#9ca3af;font-size:12px;">
                        No leave types configured. <a href="<?php echo $base; ?>/leave/config/types" style="color:#2563eb;">Set up leave types</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 2: Dates -->
            <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);margin-bottom:16px;">
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                    <span style="background:#eff6ff;color:#2563eb;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;">2</span>
                    Select Dates
                </div>

                <!-- Half-day toggle -->
                <div class="lv-toggle-wrap" id="half-day-row" style="display:none;">
                    <label class="lv-toggle">
                        <input type="checkbox" name="half_day" id="lf-half-day" onchange="toggleHalfDay(this.checked)">
                        <span class="lv-toggle-slider"></span>
                    </label>
                    <span class="lv-toggle-label">Half Day</span>
                </div>
                <div id="half-day-period-row" style="display:none;margin-bottom:14px;">
                    <label class="lv-label">Period</label>
                    <div style="display:flex;gap:10px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="half_day_period" value="morning" checked> Morning
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="half_day_period" value="afternoon"> Afternoon
                        </label>
                    </div>
                </div>

                <div class="lv-date-row">
                    <div class="lv-field">
                        <label class="lv-label">From <span class="req">*</span></label>
                        <input type="date" name="date_from" id="lf-date-from" class="form-control" required
                               min="<?php echo date('Y-m-d'); ?>" onchange="calcDays()">
                    </div>
                    <div class="lv-field" id="date-to-wrap">
                        <label class="lv-label">To <span class="req">*</span></label>
                        <input type="date" name="date_to" id="lf-date-to" class="form-control" required
                               min="<?php echo date('Y-m-d'); ?>" onchange="calcDays()">
                    </div>
                </div>

                <!-- Working days display -->
                <div id="days-preview" style="display:none;">
                    <div class="lv-days-badge">
                        <span class="material-symbols-outlined">event_available</span>
                        <span id="days-count">0</span> <span id="days-unit">working days</span>
                    </div>
                    <div id="holidays-note" style="font-size:11px;color:#6b7280;"></div>
                </div>
            </div>

            <!-- Step 3: Details -->
            <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);margin-bottom:16px;">
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                    <span style="background:#eff6ff;color:#2563eb;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;">3</span>
                    Details &amp; Handover
                </div>

                <div class="lv-field">
                    <label class="lv-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="2" style="font-size:13px;" placeholder="Brief reason for leave..."></textarea>
                </div>

                <div class="lv-field">
                    <label class="lv-label">Handing Over Duties To</label>
                    <select name="handover_employee_id" class="form-control" style="font-size:13px;">
                        <option value="">— Select colleague —</option>
                        <?php foreach (($employees ?? []) as $emp): ?>
                        <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->first_name . ' ' . $emp->last_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="lv-field">
                    <label class="lv-label">Handover Notes</label>
                    <textarea name="handover_notes" class="form-control" rows="2" style="font-size:13px;" placeholder="Key tasks, pending items, contacts..."></textarea>
                </div>

                <div class="lv-field" id="proof-field" style="display:none;">
                    <label class="lv-label">
                        Supporting Document
                        <span style="font-size:10px;font-weight:400;color:#6b7280;text-transform:none;"> (required for this leave type)</span>
                    </label>
                    <input type="file" name="proof_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>

            <button type="submit" class="lv-submit-btn" id="submit-btn" disabled>
                <span class="material-symbols-outlined">send</span>
                Submit Leave Request
            </button>
        </form>
    </div>

    <!-- Right: Summary Sidebar -->
    <div>
        <!-- Selected type summary -->
        <div class="lv-summary-card" id="type-summary" style="opacity:.4;">
            <div class="lv-summary-card__type" id="ss-type-name">Select a leave type</div>
            <div style="display:flex;align-items:baseline;gap:6px;margin:8px 0;">
                <div class="lv-summary-card__days" id="ss-days">—</div>
                <div class="lv-summary-card__unit" id="ss-unit">days available</div>
            </div>
            <div class="lv-mini-bar">
                <div class="lv-mini-bar__fill" id="ss-bar" style="width:0%;"></div>
            </div>
        </div>

        <div id="request-summary" style="background:#fff;border-radius:14px;padding:18px;
                                          box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">Request Summary</div>
            <div class="lv-summary-row"><span class="lv-summary-row__label">Leave Type</span><span class="lv-summary-row__val" id="rs-type">—</span></div>
            <div class="lv-summary-row"><span class="lv-summary-row__label">From</span><span class="lv-summary-row__val" id="rs-from">—</span></div>
            <div class="lv-summary-row"><span class="lv-summary-row__label">To</span><span class="lv-summary-row__val" id="rs-to">—</span></div>
            <div class="lv-summary-row"><span class="lv-summary-row__label">Working Days</span><span class="lv-summary-row__val" id="rs-wdays" style="color:#2563eb;">—</span></div>
            <div class="lv-summary-row"><span class="lv-summary-row__label">Balance After</span><span class="lv-summary-row__val" id="rs-after" style="color:#16a34a;">—</span></div>
        </div>

        <!-- Approval flow info -->
        <div style="background:#f9fafb;border-radius:12px;padding:16px;">
            <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:10px;">Approval Flow</div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <span class="material-symbols-outlined" style="color:#d97706;font-size:18px;">person</span>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#111827;">Step 1 — Line Manager</div>
                    <div style="font-size:11px;color:#6b7280;">Reviews and approves first</div>
                </div>
            </div>
            <div style="width:1px;height:16px;background:#e5e7eb;margin-left:9px;"></div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="material-symbols-outlined" style="color:#9333ea;font-size:18px;">verified_user</span>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#111827;">Step 2 — HR Department</div>
                    <div style="font-size:11px;color:#6b7280;">Final confirmation</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var selectedType = null;
var balanceMap = {};
<?php foreach (($leave_types ?? []) as $lt):
    $bal_row = null;
    foreach (($balance_summary ?? []) as $b) {
        if ($b->leave_type_id == $lt->id) { $bal_row = $b; break; }
    }
    $rem = $bal_row ? max(0, ((float)$bal_row->total_days + (float)$bal_row->carried_forward_days) - (float)$bal_row->used_days) : 0;
?>
balanceMap[<?php echo $lt->id; ?>] = { name:'<?php echo addslashes($lt->name); ?>', remaining:<?php echo $rem; ?>, unit:'<?php echo $lt->unit; ?>', color:'<?php echo $lt->color ?? '#2563eb'; ?>', allow_half_day:<?php echo $lt->allow_half_day ?? 1; ?>, requires_proof:<?php echo $lt->requires_proof ?? 0; ?> };
<?php endforeach; ?>

function selectType(id, name, remaining, unit, color, allowHalfDay) {
    selectedType = id;
    document.querySelectorAll('.lv-type-pill').forEach(function(p){ p.classList.remove('selected'); });
    var pill = document.querySelector('[data-type-id="'+id+'"]');
    if (pill) pill.classList.add('selected');
    document.getElementById('lf-type-id').value = id;

    // Update sidebar
    var card = document.getElementById('type-summary');
    card.style.opacity = '1';
    card.style.background = 'linear-gradient(135deg, '+color+'cc 0%, '+color+' 100%)';
    document.getElementById('ss-type-name').textContent = name;
    document.getElementById('ss-days').textContent = remaining.toFixed(1);
    document.getElementById('ss-unit').textContent = unit === 'hours' ? 'hours available' : 'days available';

    document.getElementById('rs-type').textContent = name;
    document.getElementById('half-day-row').style.display = allowHalfDay ? 'flex' : 'none';

    var pf = document.getElementById('proof-field');
    if (pf) pf.style.display = (balanceMap[id] && balanceMap[id].requires_proof) ? 'block' : 'none';

    calcDays();
}

function toggleHalfDay(checked) {
    document.getElementById('half-day-period-row').style.display = checked ? 'block' : 'none';
    var toWrap = document.getElementById('date-to-wrap');
    if (checked) {
        // same date as from
        document.getElementById('lf-date-to').value = document.getElementById('lf-date-from').value;
        toWrap.style.opacity = '.4';
        toWrap.style.pointerEvents = 'none';
        document.getElementById('lf-total-days').value = 0.5;
        document.getElementById('lf-total-hours').value = 0;
        updateSummary(0.5);
    } else {
        toWrap.style.opacity = '1';
        toWrap.style.pointerEvents = '';
        calcDays();
    }
}

function calcDays() {
    var from = document.getElementById('lf-date-from').value;
    var to   = document.getElementById('lf-date-to').value;
    var typeId = document.getElementById('lf-type-id').value;
    if (!from || !typeId) return;
    if (!to) to = from;

    // Ensure to >= from
    if (to < from) { document.getElementById('lf-date-to').value = from; to = from; }

    document.getElementById('rs-from').textContent = formatDate(from);
    document.getElementById('rs-to').textContent   = formatDate(to);

    fetch('<?php echo $base; ?>/leave/calc_days?date_from='+from+'&date_to='+to+'&leave_type_id='+typeId+'&employee_id=<?php echo $current_employee_id ?? 0; ?>', {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (!d.success) return;
        var days = d.days;
        document.getElementById('lf-total-days').value  = days;
        document.getElementById('lf-total-hours').value = d.hours || 0;

        var preview = document.getElementById('days-preview');
        preview.style.display = 'block';
        document.getElementById('days-count').textContent = days;
        document.getElementById('days-unit').textContent  = d.unit === 'hours' ? 'working hours' : 'working days';

        if (d.holidays_excluded > 0) {
            document.getElementById('holidays-note').textContent = d.holidays_excluded + ' public holiday(s) excluded from count.';
        } else {
            document.getElementById('holidays-note').textContent = '';
        }
        updateSummary(days);
    })
    .catch(function() {
        // Fallback: simple weekday count
        var days = countWeekdays(from, to);
        document.getElementById('lf-total-days').value = days;
        updateSummary(days);
    });
}

function updateSummary(days) {
    document.getElementById('rs-wdays').textContent = days + ' day' + (days !== 1 ? 's' : '');
    var typeId = document.getElementById('lf-type-id').value;
    if (typeId && balanceMap[typeId]) {
        var remaining = balanceMap[typeId].remaining;
        var after = (remaining - days).toFixed(1);
        var afterEl = document.getElementById('rs-after');
        afterEl.textContent = after + ' days';
        afterEl.style.color = parseFloat(after) < 0 ? '#dc2626' : '#16a34a';

        // Progress bar
        var pct = remaining > 0 ? Math.min(100, (days / remaining) * 100) : 100;
        document.getElementById('ss-bar').style.width = Math.min(100, (remaining - days) / (remaining || 1) * 100) + '%';
    }

    // Enable/disable submit
    var btn = document.getElementById('submit-btn');
    btn.disabled = !(typeId && document.getElementById('lf-date-from').value && days > 0);
}

function countWeekdays(from, to) {
    var d1 = new Date(from), d2 = new Date(to), count = 0;
    while (d1 <= d2) {
        if (d1.getDay() !== 0 && d1.getDay() !== 6) count++;
        d1.setDate(d1.getDate() + 1);
    }
    return count;
}

function formatDate(s) {
    if (!s) return '—';
    var d = new Date(s);
    return d.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
}

// Auto-select if preselected
document.addEventListener('DOMContentLoaded', function() {
    var preId = <?php echo (int)($preselect_type ?? 0); ?>;
    if (preId && balanceMap[preId]) {
        var b = balanceMap[preId];
        selectType(preId, b.name, b.remaining, b.unit, b.color, b.allow_half_day);
    }
    // Set default dates to today
    var today = new Date().toISOString().slice(0,10);
    document.getElementById('lf-date-from').value = today;
    document.getElementById('lf-date-to').value   = today;
});

// On behalf of employee change — reload leave type balances via AJAX
function onBehalfChanged(empId) {
    var wrap = document.getElementById('auto-approve-wrap');
    if (wrap) wrap.style.display = empId ? 'block' : 'none';

    var notice = document.getElementById('behalf-balance-notice');
    if (!empId) {
        // Revert to own balances (reload page simpler)
        window.location.href = '<?php echo $base; ?>/leave/apply';
        return;
    }
    if (notice) {
        notice.style.display = 'block';
        notice.textContent = 'Loading leave balances for selected employee…';
    }
    fetch('<?php echo $base; ?>/leave/apply/behalf_balance/' + empId)
        .then(r => r.json())
        .then(data => {
            if (notice) {
                notice.textContent = 'Showing leave balances for: ' + data.employee_name;
            }
            // Rebuild balanceMap with this employee's balances
            window.balanceMap = {};
            (data.balance_summary || []).forEach(b => {
                window.balanceMap[b.leave_type_id] = {
                    name: b.leave_type_name, remaining: b.remaining,
                    unit: b.unit, color: b.color, allow_half_day: b.allow_half_day
                };
            });
            // Refresh displayed balances on the type pills
            document.querySelectorAll('[data-type-id]').forEach(pill => {
                var tid = pill.dataset.typeId;
                var b   = window.balanceMap[tid];
                var bal = pill.querySelector('.lv-type-pill__bal');
                if (bal) bal.textContent = b ? parseFloat(b.remaining).toFixed(1) : '0.0';
            });
        })
        .catch(() => {
            if (notice) notice.textContent = 'Could not load balances. Proceed with caution.';
        });
}
</script>
<?php init_tail(); ?>
