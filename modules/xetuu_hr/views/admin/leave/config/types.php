<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.lv-type-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 14px; }
.lv-type-card {
    background: #fff; border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.lv-type-card:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(0,0,0,.1); }
.lv-type-card__top {
    height: 6px;
    background: var(--lc, #e5e7eb);
}
.lv-type-card__body { padding: 16px 18px; }
.lv-type-card__name { font-size: 15px; font-weight: 800; color: #111827; margin-bottom: 6px; }
.lv-type-card__rows { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
.lv-type-card__row  { display: flex; justify-content: space-between; font-size: 11px; }
.lv-type-card__lbl  { color: #9ca3af; }
.lv-type-card__val  { color: #374151; font-weight: 600; }
.lv-type-card__foot {
    padding: 10px 16px;
    background: #f9fafb;
    display: flex; gap: 8px; align-items: center; justify-content: space-between;
    border-top: 1px solid #f3f4f6;
}
.lv-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; }
</style>

<div class="lv-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:11px;color:#6b7280;margin-bottom:4px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> /
                <a href="<?php echo $base; ?>/leave/config" style="color:#6b7280;text-decoration:none;">Configuration</a> / Leave Types
            </div>
            <h1 style="font-size:20px;font-weight:800;color:#111827;margin:0;">Leave Types</h1>
        </div>
        <button onclick="openTypeModal()" class="btn btn-primary"
                style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:16px;">add</span> New Leave Type
        </button>
    </div>

    <?php if (empty($leave_types)): ?>
    <div style="background:#fff;border-radius:14px;padding:60px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <span class="material-symbols-outlined" style="font-size:52px;color:#d1d5db;display:block;margin-bottom:14px;">category</span>
        <div style="font-size:16px;font-weight:700;color:#374151;margin-bottom:6px;">No leave types defined</div>
        <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Create leave types like Annual Leave, Sick Leave, Maternity Leave…</div>
        <button onclick="openTypeModal()" class="btn btn-primary" style="border-radius:8px;">Create First Leave Type</button>
    </div>
    <?php else: ?>
    <div class="lv-type-grid">
    <?php foreach ($leave_types as $lt):
        $color  = $lt->color ?? '#2563eb';
        $hex    = ltrim($color,'#');
        $r2=hexdec(substr($hex,0,2)); $g2=hexdec(substr($hex,2,2)); $b2=hexdec(substr($hex,4,2));
        $bg2    = "rgba($r2,$g2,$b2,.12)";
    ?>
    <div class="lv-type-card" style="--lc:<?php echo $color; ?>;">
        <div class="lv-type-card__top"></div>
        <div class="lv-type-card__body">
            <div class="lv-type-card__name"><?php echo htmlspecialchars($lt->name); ?></div>
            <?php if ($lt->description): ?>
            <div style="font-size:11px;color:#6b7280;margin-bottom:10px;"><?php echo htmlspecialchars($lt->description); ?></div>
            <?php endif; ?>
            <div class="lv-type-card__rows">
                <div class="lv-type-card__row">
                    <span class="lv-type-card__lbl">Tracked in</span>
                    <span class="lv-type-card__val"><?php echo ucfirst($lt->unit ?? 'days'); ?></span>
                </div>
                <div class="lv-type-card__row">
                    <span class="lv-type-card__lbl">Default entitlement</span>
                    <span class="lv-type-card__val"><?php echo number_format((float)($lt->default_days ?? 0), 1); ?> <?php echo $lt->unit ?? 'days'; ?>/year</span>
                </div>
                <div class="lv-type-card__row">
                    <span class="lv-type-card__lbl">Approval levels</span>
                    <span class="lv-type-card__val"><?php echo $lt->approval_levels ?? 2; ?></span>
                </div>
                <div class="lv-type-card__row">
                    <span class="lv-type-card__lbl">Carry forward</span>
                    <span class="lv-type-card__val">
                        <?php if (!empty($lt->carry_forward)): ?>
                        Up to <?php echo $lt->max_carry_forward ?? '∞'; ?> days
                        <?php else: echo 'No'; endif; ?>
                    </span>
                </div>
                <div class="lv-type-card__row">
                    <span class="lv-type-card__lbl">Negative balance</span>
                    <span class="lv-type-card__val"><?php echo !empty($lt->allow_negative) ? 'Allowed' : 'Not allowed'; ?></span>
                </div>
            </div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <?php if ($lt->requires_proof): ?>
                <span class="lv-badge" style="background:#eff6ff;color:#2563eb;">Proof required</span>
                <?php endif; ?>
                <?php if ($lt->is_paid): ?>
                <span class="lv-badge" style="background:#f0fdf4;color:#16a34a;">Paid</span>
                <?php else: ?>
                <span class="lv-badge" style="background:#fef2f2;color:#dc2626;">Unpaid</span>
                <?php endif; ?>
                <?php if (($lt->status ?? 'active') !== 'active'): ?>
                <span class="lv-badge" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="lv-type-card__foot" style="justify-content:space-between;">
            <button onclick="openTypeModal(<?php echo htmlspecialchars(json_encode($lt)); ?>)"
                    class="btn btn-xs btn-default" style="border-radius:6px;font-size:11px;padding:4px 12px;">Edit</button>

            <?php $is_active = ($lt->status ?? 'active') === 'active'; ?>
            <!-- Toggle switch -->
            <div id="tog-<?php echo $lt->id; ?>"
                 onclick="toggleLeaveType(<?php echo $lt->id; ?>, this)"
                 data-status="<?php echo $lt->status ?? 'active'; ?>"
                 title="<?php echo $is_active ? 'Click to disable this leave type' : 'Click to enable this leave type'; ?>"
                 style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
                <!-- Pill track -->
                <div class="lt-tog-track lt-tog-track--<?php echo $is_active ? 'on' : 'off'; ?>"
                     style="width:44px;height:24px;border-radius:12px;position:relative;
                            transition:background .25s;flex-shrink:0;
                            background:<?php echo $is_active ? '#16a34a' : '#d1d5db'; ?>;">
                    <!-- Knob -->
                    <div style="position:absolute;top:3px;
                                left:<?php echo $is_active ? '23px' : '3px'; ?>;
                                width:18px;height:18px;border-radius:50%;background:#fff;
                                box-shadow:0 1px 4px rgba(0,0,0,.25);
                                transition:left .25s;"></div>
                </div>
                <span style="font-size:12px;font-weight:700;color:<?php echo $is_active ? '#16a34a' : '#6b7280'; ?>;
                             min-width:52px;transition:color .25s;">
                    <?php echo $is_active ? 'Enabled' : 'Disabled'; ?>
                </span>
            </div>

            <form method="post" action="<?php echo $base; ?>/leave/config/types/delete/<?php echo $lt->id; ?>"
                  style="display:inline;" onsubmit="return confirm('Delete this leave type? Existing allocations/requests will be unaffected.')">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-xs" style="border-radius:6px;font-size:11px;padding:4px 10px;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="type-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;overflow:auto;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:560px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);margin:20px auto;">
        <h3 id="type-modal-title" style="font-size:16px;font-weight:700;margin:0 0 16px;color:#111827;">New Leave Type</h3>
        <form id="type-form" method="post" action="<?php echo $base; ?>/leave/config/types/save">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="id" id="lt-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div style="grid-column:1/-1;">
                    <label class="lv-lbl">Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="name" id="lt-name" class="form-control" required placeholder="e.g. Annual Leave">
                </div>
                <div style="grid-column:1/-1;">
                    <label class="lv-lbl">Description</label>
                    <input type="text" name="description" id="lt-desc" class="form-control" placeholder="Short description">
                </div>
                <div>
                    <label class="lv-lbl">Color</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="color" id="lt-color" value="#2563eb" style="width:38px;height:30px;padding:1px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;">
                        <span style="font-size:11px;color:#6b7280;">Pick a colour for the calendar</span>
                    </div>
                </div>
                <div>
                    <label class="lv-lbl">Tracked In</label>
                    <select name="unit" id="lt-unit" class="form-control">
                        <option value="days">Days</option>
                        <option value="hours">Hours</option>
                    </select>
                </div>
                <div>
                    <label class="lv-lbl">Default Entitlement (per year) <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="default_days" id="lt-days" class="form-control" min="0" step="0.5" required placeholder="21">
                </div>
                <div>
                    <label class="lv-lbl">Approval Levels</label>
                    <select name="approval_levels" id="lt-appr" class="form-control">
                        <option value="1">1 — Manager only</option>
                        <option value="2" selected>2 — Manager + HR</option>
                    </select>
                </div>
                <div>
                    <label class="lv-lbl">Max Carry Forward (days)</label>
                    <input type="number" name="max_carry_forward" id="lt-maxcf" class="form-control" min="0" step="0.5" placeholder="0 = no carry forward">
                </div>
                <div>
                    <label class="lv-lbl">Carryforward Expiry (days after Jan 1)</label>
                    <input type="number" name="carry_forward_expiry_days" id="lt-cfexp" class="form-control" min="0" placeholder="0 = never expires">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                <?php
                $toggles = [
                    ['is_paid','lt-paid','Paid Leave','Leave is paid at normal rate'],
                    ['carry_forward','lt-cf','Allow Carry Forward','Unused days roll to next year'],
                    ['allow_negative','lt-neg','Allow Negative Balance','Employee can take leave in advance'],
                    ['requires_proof','lt-proof','Requires Proof','Upload required (e.g. medical certificate)'],
                    ['allow_half_day','lt-half','Allow Half Day','Employee can take half-day leave'],
                    ['is_active','lt-active','Active','Type is available for new requests'],
                ];
                foreach ($toggles as [$field,$id,$label,$hint]):
                ?>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;background:#f9fafb;border-radius:8px;padding:8px 10px;">
                    <input type="checkbox" name="<?php echo $field; ?>" id="<?php echo $id; ?>" value="1"
                           style="width:14px;height:14px;cursor:pointer;"
                           <?php echo in_array($field,['is_paid','is_active']) ? 'checked' : ''; ?>>
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#374151;"><?php echo $label; ?></div>
                        <div style="font-size:10px;color:#9ca3af;"><?php echo $hint; ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;border-top:1px solid #f3f4f6;padding-top:14px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('type-modal').style.display='none'"
                        class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius:6px;">Save Leave Type</button>
            </div>
        </form>
    </div>
</div>

<style>
.lv-lbl { font-size:11px; font-weight:700; color:#374151; text-transform:uppercase; display:block; margin-bottom:5px; }
</style>

<script>
function openTypeModal(lt) {
    const isEdit = lt && lt.id;
    document.getElementById('type-modal-title').textContent = isEdit ? 'Edit Leave Type' : 'New Leave Type';
    const fields = { 'lt-id': '', 'lt-name': '', 'lt-desc': '', 'lt-color': '#2563eb', 'lt-unit': 'days',
                     'lt-days': '', 'lt-appr': '2', 'lt-maxcf': '', 'lt-cfexp': '' };
    const checks = { 'lt-paid': true, 'lt-cf': false, 'lt-neg': false, 'lt-proof': false, 'lt-half': true, 'lt-active': true };
    if (isEdit) {
        fields['lt-id']    = lt.id;
        fields['lt-name']  = lt.name;
        fields['lt-desc']  = lt.description || '';
        fields['lt-color'] = lt.color || '#2563eb';
        fields['lt-unit']  = lt.unit || 'days';
        fields['lt-days']  = lt.default_days || '';
        fields['lt-appr']  = lt.approval_levels || '2';
        fields['lt-maxcf'] = lt.max_carry_forward || '';
        fields['lt-cfexp'] = lt.carry_forward_expiry_days || '';
        checks['lt-paid']  = !!parseInt(lt.is_paid);
        checks['lt-cf']    = !!parseInt(lt.carry_forward);
        checks['lt-neg']   = !!parseInt(lt.allow_negative);
        checks['lt-proof'] = !!parseInt(lt.requires_proof);
        checks['lt-half']  = !!parseInt(lt.allow_half_day);
        checks['lt-active'] = (lt.status === 'active');
    }
    Object.entries(fields).forEach(([k,v]) => { const el = document.getElementById(k); if (el) el.value = v; });
    Object.entries(checks).forEach(([k,v]) => { const el = document.getElementById(k); if (el) el.checked = v; });
    document.getElementById('type-modal').style.display = 'flex';
}

function toggleLeaveType(id, wrap) {
    if (wrap.dataset.loading) return;
    wrap.dataset.loading = '1';
    wrap.style.opacity = '0.6';

    fetch('<?php echo admin_url('xetuu_hr'); ?>/leave/config/types/toggle/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
    })
    .then(r => r.json())
    .then(data => {
        delete wrap.dataset.loading;
        wrap.style.opacity = '1';
        if (!data.success) return;

        const on    = data.status === 'active';
        const track = wrap.querySelector('div');   // the pill track
        const knob  = track.querySelector('div');  // the knob
        const label = wrap.querySelector('span');

        track.style.background = on ? '#16a34a' : '#d1d5db';
        knob.style.left        = on ? '23px' : '3px';
        label.textContent      = on ? 'Enabled' : 'Disabled';
        label.style.color      = on ? '#16a34a' : '#6b7280';
        wrap.dataset.status    = data.status;
        wrap.title             = on ? 'Click to disable this leave type' : 'Click to enable this leave type';
    })
    .catch(() => {
        delete wrap.dataset.loading;
        wrap.style.opacity = '1';
    });
}
</script>
<?php init_tail(); ?>
