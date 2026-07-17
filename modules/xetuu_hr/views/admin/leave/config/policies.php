<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
.lv-pol-card {
    background: #fff; border-radius: 14px; overflow: hidden; margin-bottom: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
}
.lv-pol-card:hover { transform: translateY(-1px); box-shadow: 0 6px 28px rgba(0,0,0,.1); }
.lv-pol-card__head {
    padding: 16px 20px 14px; border-bottom: 1px solid #f3f4f6;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
}
.lv-pol-card__body { padding: 14px 20px; }
.lv-pol-lines { display: flex; flex-wrap: wrap; gap: 8px; }
.lv-pol-line {
    display: flex; align-items: center; gap: 7px;
    padding: 6px 12px; border-radius: 8px;
    background: #f9fafb; border: 1px solid #e5e7eb;
    font-size: 13px; color: #374151;
}
.lv-pol-line__dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

/* Modal */
.pol-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 9999;
    align-items: center; justify-content: center; overflow: auto;
}
.pol-modal-overlay.open { display: flex; }
.pol-modal-box {
    background: #fff; border-radius: 16px; padding: 28px;
    width: 640px; max-width: 95vw;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); margin: 20px auto;
}
.pol-modal-title { font-size: 18px; font-weight: 800; margin: 0 0 20px; color: #111827; }
.pol-label {
    font-size: 13px; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .04em;
    display: block; margin-bottom: 6px;
}
.pol-label span { color: #dc2626; }
.pol-ctrl {
    width: 100%; padding: 10px 12px;
    border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 14px; color: #374151; outline: none;
    transition: border-color .15s;
}
.pol-ctrl:focus { border-color: #006b2c; box-shadow: 0 0 0 3px rgba(0,107,44,.08); }
.pol-toggle {
    display: flex; align-items: center; gap: 10px; cursor: pointer;
    background: #f9fafb; border-radius: 10px; padding: 10px 14px;
}
.pol-toggle input { width: 16px; height: 16px; cursor: pointer; }
.pol-toggle__label { font-size: 14px; font-weight: 700; color: #374151; }
.pol-toggle__hint  { font-size: 12px; color: #9ca3af; }

/* Line rows inside modal */
.pol-line-row {
    display: grid;
    grid-template-columns: 2fr 130px 100px 36px;
    gap: 8px; align-items: center;
    background: #f9fafb; border-radius: 10px; padding: 10px 12px;
}
.pol-line-row select,
.pol-line-row input[type="number"] {
    width: 100%; padding: 8px 10px;
    border: 1.5px solid #e5e7eb; border-radius: 7px;
    font-size: 13px; color: #374151; outline: none;
}
.pol-line-row select:focus,
.pol-line-row input[type="number"]:focus { border-color: #006b2c; }
.pol-carry-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; color: #374151; cursor: pointer; white-space: nowrap;
}
.pol-carry-label input { width: 15px; height: 15px; cursor: pointer; }
.pol-remove-btn {
    background: none; border: none; cursor: pointer;
    color: #9ca3af; font-size: 20px; line-height: 1;
    display: flex; align-items: center; justify-content: center;
    border-radius: 6px; padding: 2px 6px;
    transition: color .15s, background .15s;
}
.pol-remove-btn:hover { color: #dc2626; background: #fef2f2; }

.pol-add-line-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 700;
    background: #f3f4f6; border: 1.5px solid #e5e7eb; color: #374151;
    cursor: pointer; transition: background .15s;
}
.pol-add-line-btn:hover { background: #e5e7eb; }

.pol-footer { display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid #f3f4f6; padding-top: 16px; margin-top: 4px; }
.pol-btn {
    padding: 9px 22px; border-radius: 8px; font-size: 14px; font-weight: 700;
    border: none; cursor: pointer; transition: background .15s;
}
.pol-btn--cancel  { background: #f3f4f6; color: #374151; }
.pol-btn--cancel:hover  { background: #e5e7eb; }
.pol-btn--save    { background: #006b2c; color: #fff; }
.pol-btn--save:hover    { background: #005523; }
</style>

<div class="lv-page">

    <!-- Page header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:13px;color:#6b7280;margin-bottom:4px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> /
                <a href="<?php echo $base; ?>/leave/config" style="color:#6b7280;text-decoration:none;">Configuration</a> / Leave Policies
            </div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;margin:0;">Leave Policies</h1>
        </div>
        <button onclick="openPolModal()" class="btn btn-primary"
                style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;font-size:14px;padding:9px 18px;">
            <span class="material-symbols-outlined" style="font-size:17px;">add</span> New Policy
        </button>
    </div>

    <!-- Info banner -->
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:13px 16px;margin-bottom:18px;font-size:13px;color:#78350f;">
        <span class="material-symbols-outlined" style="font-size:17px;vertical-align:middle;margin-right:6px;color:#d97706;">info</span>
        <strong>Policies</strong> define which leave types apply to a group (e.g. Full-Time Staff, Contract Workers).
        Assign a policy to employees on their contracts to auto-allocate entitlements at the start of each year.
    </div>

    <?php if (empty($policies)): ?>
    <div style="background:#fff;border-radius:14px;padding:60px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <span class="material-symbols-outlined" style="font-size:52px;color:#d1d5db;display:block;margin-bottom:14px;">policy</span>
        <div style="font-size:17px;font-weight:700;color:#374151;margin-bottom:6px;">No policies yet</div>
        <div style="font-size:14px;color:#9ca3af;margin-bottom:16px;">Create a policy to group leave types and entitlements for different employee categories.</div>
        <button onclick="openPolModal()" class="btn btn-primary" style="border-radius:8px;font-size:14px;">Create First Policy</button>
    </div>
    <?php else: ?>
    <?php foreach ($policies as $pol): ?>
    <div class="lv-pol-card">
        <div class="lv-pol-card__head">
            <div>
                <div style="font-size:16px;font-weight:800;color:#111827;"><?php echo htmlspecialchars($pol->name); ?></div>
                <?php if ($pol->description): ?>
                <div style="font-size:13px;color:#6b7280;margin-top:3px;"><?php echo htmlspecialchars($pol->description); ?></div>
                <?php endif; ?>
                <div style="font-size:12px;color:#9ca3af;margin-top:5px;">
                    <?php echo count($pol->lines ?? []); ?> leave type<?php echo count($pol->lines ?? []) !== 1 ? 's' : ''; ?>
                    <?php if (($pol->status ?? 'active') !== 'active'): ?>
                    · <span style="color:#dc2626;font-weight:700;">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                <button onclick="openPolModal(<?php echo htmlspecialchars(json_encode($pol)); ?>)"
                        class="btn btn-xs btn-default" style="border-radius:6px;font-size:13px;padding:5px 12px;">Edit</button>
                <form method="post" action="<?php echo $base; ?>/leave/config/policies/delete/<?php echo $pol->id; ?>"
                      style="display:inline;" onsubmit="return confirm('Delete this policy?')">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <button type="submit" class="btn btn-xs" style="border-radius:6px;font-size:13px;padding:5px 12px;color:#dc2626;background:#fff5f5;border:1px solid #fecaca;">Delete</button>
                </form>
            </div>
        </div>
        <?php if (!empty($pol->lines)): ?>
        <div class="lv-pol-card__body">
            <div style="font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Leave Entitlements</div>
            <div class="lv-pol-lines">
            <?php foreach ($pol->lines as $line): ?>
            <div class="lv-pol-line">
                <div class="lv-pol-line__dot" style="background:<?php echo $line->leave_type_color ?? '#6b7280'; ?>;"></div>
                <strong><?php echo htmlspecialchars($line->leave_type_name ?? ''); ?></strong>
                <span style="color:#6b7280;"><?php echo number_format((float)($line->annual_days ?? 0), 1); ?> days/yr</span>
                <?php if (!empty($line->allow_carryforward)): ?>
                <span style="color:#2563eb;font-size:12px;font-weight:600;">+carry</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div><!-- /.lv-page -->

<!-- ══ Policy Modal ═══════════════════════════════════════ -->
<div class="pol-modal-overlay" id="pol-modal" onclick="if(event.target===this)closePolModal()">
    <div class="pol-modal-box">
        <h3 class="pol-modal-title" id="pol-modal-title">New Leave Policy</h3>
        <form id="pol-form" method="post" action="<?php echo $base; ?>/leave/config/policies/save">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="pol-id">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div style="grid-column:1/-1;">
                    <label class="pol-label">Policy Name <span>*</span></label>
                    <input type="text" name="name" id="pol-name" class="pol-ctrl" required placeholder="e.g. Full-Time Staff Policy">
                </div>
                <div style="grid-column:1/-1;">
                    <label class="pol-label">Description</label>
                    <input type="text" name="description" id="pol-desc" class="pol-ctrl" placeholder="Applies to…">
                </div>
                <div>
                    <label class="pol-toggle">
                        <input type="checkbox" name="is_active" id="pol-active" value="1" checked>
                        <div>
                            <div class="pol-toggle__label">Active</div>
                            <div class="pol-toggle__hint">Available for assignment to employees</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Leave Type Lines -->
            <div style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <label class="pol-label" style="margin:0;">Leave Type Entitlements</label>
                    <button type="button" onclick="addPolLine()" class="pol-add-line-btn">
                        <span class="material-symbols-outlined" style="font-size:15px;">add</span> Add Line
                    </button>
                </div>
                <div id="pol-lines" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            <div class="pol-footer">
                <button type="button" onclick="closePolModal()" class="pol-btn pol-btn--cancel">Cancel</button>
                <button type="submit" class="pol-btn pol-btn--save">Save Policy</button>
            </div>
        </form>
    </div>
</div>

<script>
const leaveTypes = <?php echo json_encode(array_map(function($lt){
    return ['id'=>$lt->id,'name'=>$lt->name,'color'=>$lt->color??'#6b7280','default_days'=>(float)($lt->default_days??0)];
}, $leave_types)); ?>;

let polLineIdx = 0;

function addPolLine(data) {
    const idx = polLineIdx++;
    const div = document.createElement('div');
    div.className = 'pol-line-row';
    div.innerHTML = `
        <select name="lines[${idx}][leave_type_id]" required>
            <option value="">Select leave type...</option>
            ${leaveTypes.map(lt => `<option value="${lt.id}" ${data&&data.leave_type_id==lt.id?'selected':''}>${lt.name}</option>`).join('')}
        </select>
        <input type="number" name="lines[${idx}][days_per_year]" min="0.5" step="0.5"
               placeholder="Days/yr" required value="${data ? data.days_per_year : ''}">
        <label class="pol-carry-label">
            <input type="checkbox" name="lines[${idx}][allow_carryforward]" value="1" ${data&&data.allow_carryforward?'checked':''}>
            Carry fwd
        </label>
        <button type="button" onclick="this.closest('.pol-line-row').remove()" class="pol-remove-btn">×</button>
    `;
    document.getElementById('pol-lines').appendChild(div);
}

function openPolModal(pol) {
    document.getElementById('pol-modal-title').textContent = pol ? 'Edit Leave Policy' : 'New Leave Policy';
    document.getElementById('pol-id').value    = pol ? pol.id : '';
    document.getElementById('pol-name').value  = pol ? pol.name : '';
    document.getElementById('pol-desc').value  = pol ? (pol.description || '') : '';
    document.getElementById('pol-active').checked = pol ? (pol.status === 'active') : true;
    document.getElementById('pol-lines').innerHTML = '';
    polLineIdx = 0;
    if (pol && pol.lines && pol.lines.length) {
        pol.lines.forEach(l => addPolLine(l));
    } else {
        addPolLine();
    }
    document.getElementById('pol-modal').classList.add('open');
}

function closePolModal() {
    document.getElementById('pol-modal').classList.remove('open');
}
</script>

<?php init_tail(); ?>
