<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
.lv-page { padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.lv-hol-cal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px,1fr)); gap: 14px; margin-bottom: 24px; }
.lv-hol-cal-card {
    background: #fff; border-radius: 14px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.lv-hol-cal-card:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(0,0,0,.1); }
.lv-hol-cal-card__accent { height: 5px; background: linear-gradient(90deg, #2563eb, #9333ea); }
.lv-hol-cal-card__body { padding: 14px 16px; }
.lv-hol-cal-card__name { font-size: 13px; font-weight: 800; color: #111827; margin-bottom: 4px; }
.lv-hol-cal-card__meta { font-size: 11px; color: #6b7280; margin-bottom: 10px; }
.lv-hol-cal-card__foot {
    padding: 8px 14px; background: #f9fafb; border-top: 1px solid #f3f4f6;
    display: flex; gap: 6px; align-items: center;
}
.lv-hol-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
.lv-hol-tbl th { background:#f9fafb;font-size:10px;font-weight:700;text-transform:uppercase;
                  color:#6b7280;padding:9px 14px;border-bottom:1px solid #e5e7eb; }
.lv-hol-tbl td { padding:10px 14px;font-size:12px;color:#374151;border-bottom:1px solid #f3f4f6; }
.lv-hol-tbl tr:last-child td { border-bottom: none; }
.lv-hol-tbl tr:hover td { background: #f9fafb; }
.lv-import-box {
    background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
    border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 18px;
    margin-bottom: 20px;
}
</style>

<div class="lv-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:11px;color:#6b7280;margin-bottom:4px;">
                <a href="<?php echo $base; ?>/leave" style="color:#6b7280;text-decoration:none;">Leave</a> /
                Configuration / Holiday Calendars
            </div>
            <h1 style="font-size:20px;font-weight:800;color:#111827;margin:0;">Holiday Calendars</h1>
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="document.getElementById('add-hol-modal').style.display='flex'"
                    class="btn btn-default" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;font-size:12px;">
                <span class="material-symbols-outlined" style="font-size:16px;">add</span> Add Holiday
            </button>
            <button onclick="document.getElementById('import-modal').style.display='flex'"
                    class="btn btn-primary" style="border-radius:8px;display:flex;align-items:center;gap:6px;font-weight:700;font-size:12px;">
                <span class="material-symbols-outlined" style="font-size:16px;">cloud_download</span> Import Public Holidays
            </button>
        </div>
    </div>

    <!-- API Banner -->
    <div class="lv-import-box">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <span class="material-symbols-outlined" style="font-size:24px;color:#2563eb;flex-shrink:0;">public</span>
            <div>
                <div style="font-size:13px;font-weight:700;color:#1e40af;margin-bottom:3px;">Automatic Public Holiday Import via Nager.Date API</div>
                <div style="font-size:12px;color:#374151;">
                    Import official public holidays for <strong>Kenya (KE), Uganda (UG), Tanzania (TZ), Ethiopia (ET), Rwanda (RW), Nigeria (NG)</strong>
                    and 100+ countries — free, no API key required.
                </div>
            </div>
        </div>
    </div>

    <!-- Holiday Calendars Grid -->
    <?php if (empty($holiday_lists)): ?>
    <div style="background:#fff;border-radius:14px;padding:60px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <span class="material-symbols-outlined" style="font-size:52px;color:#d1d5db;display:block;margin-bottom:14px;">event_busy</span>
        <div style="font-size:16px;font-weight:700;color:#374151;margin-bottom:6px;">No holiday calendars yet</div>
        <div style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Import public holidays or add them manually.</div>
        <button onclick="document.getElementById('import-modal').style.display='flex'" class="btn btn-primary" style="border-radius:8px;">Import Public Holidays</button>
    </div>
    <?php else: ?>
    <div class="lv-hol-cal-grid">
    <?php foreach ($holiday_lists as $list): ?>
    <div class="lv-hol-cal-card" onclick="loadHolidays(<?php echo $list->id; ?>,'<?php echo addslashes(htmlspecialchars($list->name)); ?>')">
        <div class="lv-hol-cal-card__accent"></div>
        <div class="lv-hol-cal-card__body">
            <div class="lv-hol-cal-card__name"><?php echo htmlspecialchars($list->name); ?></div>
            <div class="lv-hol-cal-card__meta">
                <?php if ($list->country_code): ?>
                <span style="background:#eff6ff;color:#2563eb;padding:2px 7px;border-radius:4px;font-weight:700;font-size:10px;"><?php echo $list->country_code; ?></span>
                <?php endif; ?>
                <?php if ($list->year): ?>&nbsp;<?php echo $list->year; ?><?php endif; ?>
                <span style="float:right;font-weight:700;color:#374151;"><?php echo $list->holiday_count ?? '?'; ?> days</span>
            </div>
        </div>
        <div class="lv-hol-cal-card__foot" onclick="event.stopPropagation()">
            <button onclick="reimport('<?php echo $list->id; ?>','<?php echo addslashes($list->country_code); ?>','<?php echo $list->year; ?>')"
                    class="btn btn-xs btn-default" style="border-radius:4px;font-size:10px;display:flex;align-items:center;gap:3px;">
                <span class="material-symbols-outlined" style="font-size:12px;">refresh</span> Re-import
            </button>
            <form method="post" action="<?php echo $base; ?>/leave/config/holidays/delete/<?php echo $list->id; ?>"
                  style="display:inline;margin-left:auto;" onsubmit="return confirm('Delete this entire holiday calendar?')">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <button type="submit" class="btn btn-xs" style="border-radius:4px;font-size:10px;color:#dc2626;background:none;border:1px solid #fecaca;">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- Holidays detail panel (loaded dynamically) -->
    <div id="hol-detail" style="display:none;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;">
        <div style="padding:12px 16px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:10px;">
            <span class="material-symbols-outlined" style="color:#2563eb;">calendar_month</span>
            <strong id="hol-detail-title" style="font-size:13px;color:#111827;"></strong>
            <button onclick="document.getElementById('hol-detail').style.display='none'"
                    style="margin-left:auto;background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;">×</button>
        </div>
        <div id="hol-detail-body" style="padding:14px;min-height:80px;">
            <div style="text-align:center;color:#9ca3af;font-size:12px;padding:20px;">Loading…</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Import Modal -->
<div id="import-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:440px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <span class="material-symbols-outlined" style="font-size:24px;color:#2563eb;">cloud_download</span>
            <h3 style="font-size:16px;font-weight:700;margin:0;color:#111827;">Import Public Holidays</h3>
        </div>
        <form method="post" action="<?php echo $base; ?>/leave/config/holidays/import" id="import-form">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <input type="hidden" name="list_id" id="import-list-id" value="">
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Calendar Name</label>
                <input type="text" name="list_name" id="import-list-name" class="form-control" placeholder="Auto-generated if blank">
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Country <span style="color:#dc2626;">*</span></label>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                    <?php foreach(['KE'=>'Kenya','UG'=>'Uganda','TZ'=>'Tanzania','ET'=>'Ethiopia','RW'=>'Rwanda','NG'=>'Nigeria'] as $cc=>$nm): ?>
                    <button type="button" onclick="selectCC('<?php echo $cc; ?>')" id="cc-<?php echo $cc; ?>"
                            class="cc-btn" style="padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;border:1px solid #e5e7eb;background:#f9fafb;cursor:pointer;">
                        <?php echo $cc; ?> <span style="font-weight:400;color:#6b7280;"><?php echo $nm; ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="country_code" id="import-cc" class="form-control" required maxlength="2"
                       placeholder="Or type: GB, US, DE…" style="text-transform:uppercase;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Year <span style="color:#dc2626;">*</span></label>
                <select name="year" class="form-control" required>
                    <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                    <option value="<?php echo date('Y') - 1; ?>"><?php echo date('Y') - 1; ?></option>
                </select>
            </div>
            <div id="import-spinner" style="display:none;margin-bottom:10px;font-size:12px;color:#16a34a;padding:8px 12px;background:#f0fdf4;border-radius:8px;">
                <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;animation:spin 1s linear infinite;">refresh</span>
                Fetching from Nager.Date API…
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('import-modal').style.display='none'" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</button>
                <button type="submit" id="import-btn" class="btn btn-primary btn-sm" style="border-radius:6px;display:flex;align-items:center;gap:5px;">
                    <span class="material-symbols-outlined" style="font-size:14px;">cloud_download</span> Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Holiday Manually Modal -->
<div id="add-hol-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:16px;font-weight:700;margin:0 0 16px;color:#111827;">Add Holiday Manually</h3>
        <form method="post" action="<?php echo $base; ?>/leave/config/holidays/add_manual">
            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Calendar</label>
                <select name="list_id" class="form-control">
                    <option value="">— Create new calendar —</option>
                    <?php foreach ($holiday_lists as $hl): ?>
                    <option value="<?php echo $hl->id; ?>"><?php echo htmlspecialchars($hl->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Calendar Name (if new)</label>
                <input type="text" name="list_name" class="form-control" placeholder="e.g. Kenya Public Holidays 2026">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" name="holiday_date" class="form-control" required>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Type</label>
                    <select name="type" class="form-control">
                        <option value="public">Public Holiday</option>
                        <option value="optional">Optional</option>
                        <option value="regional">Regional</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;display:block;margin-bottom:5px;">Holiday Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Madaraka Day">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('add-hol-modal').style.display='none'" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius:6px;">Add Holiday</button>
            </div>
        </form>
    </div>
</div>

<style>
.cc-btn.active-cc { background:#eff6ff !important; border-color:#93c5fd !important; color:#2563eb !important; }
@keyframes spin { from { transform:rotate(0); } to { transform:rotate(360deg); } }
</style>

<script>
var baseUrl = '<?php echo $base; ?>';

function selectCC(cc) {
    document.querySelectorAll('.cc-btn').forEach(b => b.classList.remove('active-cc'));
    var btn = document.getElementById('cc-' + cc);
    if (btn) btn.classList.add('active-cc');
    document.getElementById('import-cc').value = cc;
}

function reimport(listId, cc, year) {
    if (!confirm('Re-import holidays for ' + cc + ' ' + year + '?')) return;
    document.getElementById('import-list-id').value = listId;
    document.getElementById('import-cc').value = cc;
    document.querySelector('#import-form select[name=year]').value = year;
    document.getElementById('import-modal').style.display = 'flex';
}

document.getElementById('import-form').addEventListener('submit', function() {
    document.getElementById('import-spinner').style.display = 'block';
    document.getElementById('import-btn').disabled = true;
});

function loadHolidays(listId, name) {
    var panel = document.getElementById('hol-detail');
    document.getElementById('hol-detail-title').textContent = name;
    document.getElementById('hol-detail-body').innerHTML = '<div style="text-align:center;color:#9ca3af;font-size:12px;padding:20px;">Loading…</div>';
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    fetch(baseUrl + '/leave/config/holidays/list_detail/' + listId)
        .then(r => r.json())
        .then(data => {
            if (!data.holidays || !data.holidays.length) {
                document.getElementById('hol-detail-body').innerHTML = '<div style="text-align:center;color:#9ca3af;padding:20px;font-size:12px;">No holidays in this calendar.</div>';
                return;
            }
            var html = '<table style="width:100%;border-collapse:collapse;">' +
                '<thead><tr>' +
                '<th style="text-align:left;padding:6px 10px;font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;background:#f9fafb;">Date</th>' +
                '<th style="text-align:left;padding:6px 10px;font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;background:#f9fafb;">Name</th>' +
                '<th style="text-align:left;padding:6px 10px;font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;background:#f9fafb;">Type</th>' +
                '</tr></thead><tbody>';
            data.holidays.forEach(function(h) {
                html += '<tr>' +
                    '<td style="padding:8px 10px;font-size:12px;color:#2563eb;font-weight:700;border-bottom:1px solid #f3f4f6;">' + h.date + '</td>' +
                    '<td style="padding:8px 10px;font-size:12px;color:#374151;border-bottom:1px solid #f3f4f6;">' + h.name + '</td>' +
                    '<td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;"><span style="font-size:10px;background:#eff6ff;color:#2563eb;padding:2px 7px;border-radius:4px;font-weight:700;">' + (h.type || 'public') + '</span></td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            document.getElementById('hol-detail-body').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('hol-detail-body').innerHTML = '<div style="text-align:center;color:#dc2626;padding:20px;font-size:12px;">Failed to load holidays.</div>';
        });
}
</script>
<?php init_tail(); ?>
