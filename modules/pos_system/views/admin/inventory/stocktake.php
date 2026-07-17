<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'stocktake',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
  <?php
  $stat_cfg = [
    'draft'      => ['Draft',       '#64748b', 'fa-pencil-alt'],
    'in_progress'=> ['In Progress', '#d97706', 'fa-clipboard-check'],
    'completed'  => ['Completed',   '#16a34a', 'fa-check-circle'],
    'cancelled'  => ['Cancelled',   '#dc2626', 'fa-times-circle'],
  ];
  foreach ($stat_cfg as $k => [$lbl,$clr,$ico]):
  ?>
  <div class="inv-stat">
    <div class="inv-stat-icon" style="background:<?php echo $clr ?>20;color:<?php echo $clr ?>">
      <i class="fa <?php echo $ico ?>"></i>
    </div>
    <div class="inv-stat-val" style="color:<?php echo $clr ?>"><?php echo $stats[$k] ?? 0 ?></div>
    <div class="inv-stat-lbl"><?php echo $lbl ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Toolbar -->
<div class="inv-card" style="margin-bottom:16px;overflow:visible">
  <div class="inv-filter-bar">
    <h4 class="inv-card-title" style="font-size:15px;margin:0;flex:1">
      <i class="fa fa-clipboard-check" style="color:#16a34a"></i> Physical Inventory Sessions
    </h4>
    <div style="position:relative">
      <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:12px;pointer-events:none"></i>
      <input type="text" id="stk-search" class="inv-input" placeholder="Search sessions…" style="width:200px;padding-left:30px">
    </div>
    <select id="stk-status-filter" class="inv-input" style="width:140px;cursor:pointer">
      <option value="">All Status</option>
      <option value="draft">Draft</option>
      <option value="in_progress">In Progress</option>
      <option value="completed">Completed</option>
      <option value="cancelled">Cancelled</option>
    </select>
    <button onclick="openNewSessionModal()" class="btn-inv-primary">
      <i class="fa fa-plus"></i> New Stock Take
    </button>
  </div>

  <!-- Sessions table -->
  <div style="overflow-x:auto">
    <table class="inv-table" id="stk-table">
      <thead>
        <tr>
          <th>Ref #</th>
          <th>Branch</th>
          <th>Scope</th>
          <th>Start Date</th>
          <th>Progress</th>
          <th>Blind Mode</th>
          <th>Status</th>
          <th>Created By</th>
          <th style="text-align:center;width:110px">Actions</th>
        </tr>
      </thead>
      <tbody id="stk-tbody">
      <?php if (empty($sessions)): ?>
        <tr><td colspan="9" class="text-center inv-empty" style="padding:50px">
          <i class="fa fa-clipboard-check"></i>
          <p>No stocktake sessions yet. Click <strong>New Stock Take</strong> to begin.</p>
        </td></tr>
      <?php else: foreach ($sessions as $s):
        $pct = $s['total_items'] > 0 ? min(100, round(($s['items_counted']/$s['total_items'])*100)) : 0;
        $status_colors = ['draft'=>'#64748b','in_progress'=>'#d97706','completed'=>'#16a34a','cancelled'=>'#dc2626'];
        $sc = $status_colors[$s['status']] ?? '#888';
      ?>
        <tr class="stk-row" data-status="<?php echo $s['status'] ?>" data-search="<?php echo strtolower($s['stocktake_number'].' '.$s['branch_name'].' '.($s['created_by_name']??'')) ?>">
          <td>
            <a href="<?php echo admin_url('pos_system/inv_view/stocktake/'.$s['id']) ?>" style="font-weight:700;color:#0369a1">
              <?php echo htmlspecialchars($s['stocktake_number']) ?>
            </a>
          </td>
          <td><?php echo htmlspecialchars($s['branch_name'] ?: '—') ?></td>
          <td>
            <span style="font-size:11px;text-transform:uppercase;font-weight:600;color:#4a5e54">
              <?php echo ucfirst($s['scope'] ?: 'full') ?>
            </span>
          </td>
          <td><?php echo $s['start_date'] ? date('d M Y', strtotime($s['start_date'])) : '—' ?></td>
          <td style="min-width:120px">
            <?php if ($s['total_items'] > 0): ?>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="flex:1;height:6px;background:#e2ece6;border-radius:99px;overflow:hidden">
                <div style="height:100%;width:<?php echo $pct ?>%;background:<?php echo $pct==100?'#16a34a':'#d97706' ?>;border-radius:99px;transition:width .3s"></div>
              </div>
              <span style="font-size:11px;font-weight:700;color:#4a5e54;white-space:nowrap"><?php echo $s['items_counted'] ?>/<?php echo $s['total_items'] ?></span>
            </div>
            <?php else: ?>
              <span style="color:#aaa;font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center">
            <?php if ($s['blind_counting']): ?>
              <span style="background:#fef3c7;color:#92400e;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:700">BLIND</span>
            <?php else: ?>
              <span style="color:#aaa;font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="ibadge ibadge-<?php echo $s['status'] ?>" style="background:<?php echo $sc ?>18;color:<?php echo $sc ?>">
              <?php echo ucwords(str_replace('_',' ',$s['status'])) ?>
            </span>
          </td>
          <td style="font-size:12px"><?php echo htmlspecialchars($s['created_by_name'] ?: '—') ?></td>
          <td style="text-align:center">
            <a href="<?php echo admin_url('pos_system/inv_view/stocktake/'.$s['id']) ?>"
               class="btn-inv-icon" title="Open Session"><i class="fa fa-folder-open"></i></a>
            <?php if (in_array($s['status'],['draft','in_progress'])): ?>
            <button onclick="cancelSession(<?php echo $s['id'] ?>,'<?php echo addslashes($s['stocktake_number']) ?>')"
                    class="btn-inv-icon danger" title="Cancel"><i class="fa fa-times"></i></button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div><!-- inv-content -->
</div><!-- content -->
</div><!-- wrapper -->

<!-- ── New Session Modal ──────────────────────────────────────────────────── -->
<div id="new-session-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;width:580px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25)">
    <div style="background:linear-gradient(135deg,#b8ddc8,#9ecfb2);padding:18px 24px;border-radius:14px 14px 0 0;display:flex;align-items:center;gap:12px">
      <i class="fa fa-clipboard-check" style="font-size:20px;color:#0d2818"></i>
      <h3 style="margin:0;color:#0d2818;font-size:16px;font-weight:700">Initiate New Stocktake Session</h3>
      <button onclick="closeNewSessionModal()" style="margin-left:auto;background:none;border:none;font-size:18px;color:#0d2818;cursor:pointer">&times;</button>
    </div>
    <div style="padding:24px">
      <form id="new-session-form">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
          <div>
            <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Branch / Warehouse <span style="color:#dc2626">*</span></label>
            <select id="ns-branch" class="df-select" required style="width:100%;height:34px;border:1px solid #c8dfd0;border-radius:7px;padding:0 11px">
              <?php foreach ($branches as $b): ?>
                <option value="<?php echo $b['id'] ?>" <?php echo (int)$b['id']==(int)$branch_id?'selected':'' ?>>
                  <?php echo htmlspecialchars($b['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Scope</label>
            <select id="ns-scope" class="df-select" onchange="toggleScopeFilter()" style="width:100%;height:34px;border:1px solid #c8dfd0;border-radius:7px;padding:0 11px">
              <option value="full">Full Warehouse (All Products)</option>
              <option value="category">By Category</option>
            </select>
          </div>
        </div>

        <div id="ns-category-wrap" style="display:none;margin-bottom:16px">
          <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Select Categories</label>
          <div style="border:1px solid #c8dfd0;border-radius:7px;padding:10px;max-height:140px;overflow-y:auto;background:#fafdfb">
            <?php foreach ($categories as $cat): ?>
            <label style="display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer;font-size:13px">
              <input type="checkbox" class="ns-cat-cb" value="<?php echo $cat['id'] ?>" style="cursor:pointer">
              <?php echo htmlspecialchars($cat['name']) ?>
            </label>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <p style="color:#aaa;font-size:12px;margin:0">No categories found.</p>
            <?php endif; ?>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
          <div>
            <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Variance Alert Threshold (units)</label>
            <input type="number" id="ns-threshold" min="0" step="0.01" value="0" class="df-input"
                   style="width:100%;height:34px;border:1px solid #c8dfd0;border-radius:7px;padding:0 11px;font-size:13px"
                   placeholder="0 = no threshold">
          </div>
          <div style="display:flex;align-items:flex-end;padding-bottom:2px">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;font-weight:600;color:#1a2520">
              <input type="checkbox" id="ns-blind" style="width:16px;height:16px;cursor:pointer">
              <span>
                <strong>Blind Counting Mode</strong><br>
                <span style="font-size:11px;color:#6b7c6a;font-weight:400">Hides expected qty from floor workers</span>
              </span>
            </label>
          </div>
        </div>

        <div style="margin-bottom:16px">
          <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Notes (optional)</label>
          <textarea id="ns-notes" rows="2" class="df-textarea"
                    style="width:100%;border:1px solid #c8dfd0;border-radius:7px;padding:8px 11px;font-size:13px;resize:vertical"
                    placeholder="e.g. End-of-quarter stocktake for Nairobi branch…"></textarea>
        </div>

        <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;padding:12px 14px;margin-bottom:20px;display:flex;gap:10px">
          <i class="fa fa-exclamation-triangle" style="color:#d97706;margin-top:2px"></i>
          <div style="font-size:12px;color:#78350f">
            <strong>Initiating a stocktake will:</strong><br>
            • Snapshot current inventory levels at this moment<br>
            • Allow floor workers to enter physical counts<br>
            • Post adjustments will update live inventory quantities
          </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" onclick="closeNewSessionModal()" class="btn-inv-secondary">Cancel</button>
          <button type="submit" id="ns-submit-btn" class="btn-inv-primary">
            <i class="fa fa-play-circle"></i> Initiate Stocktake
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
var STK_AJAX = '<?php echo admin_url('pos_system/inv_ajax/') ?>';
var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name() ?>';
var CSRF_HASH = '<?php echo $this->security->get_csrf_hash() ?>';

function openNewSessionModal() {
    document.getElementById('new-session-modal').style.display = 'flex';
}
function closeNewSessionModal() {
    document.getElementById('new-session-modal').style.display = 'none';
}
function toggleScopeFilter() {
    var scope = document.getElementById('ns-scope').value;
    document.getElementById('ns-category-wrap').style.display = scope === 'category' ? '' : 'none';
}

document.getElementById('new-session-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('ns-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Initiating…';

    var scope = document.getElementById('ns-scope').value;
    var scopeFilter = '';
    if (scope === 'category') {
        var cats = Array.from(document.querySelectorAll('.ns-cat-cb:checked')).map(c => c.value);
        if (!cats.length) { alert('Please select at least one category.'); btn.disabled=false; btn.innerHTML='<i class="fa fa-play-circle"></i> Initiate Stocktake'; return; }
        scopeFilter = cats.join(',');
    }

    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    fd.append('branch_id', document.getElementById('ns-branch').value);
    fd.append('scope', scope);
    fd.append('scope_filter', scopeFilter);
    fd.append('blind_counting', document.getElementById('ns-blind').checked ? 1 : 0);
    fd.append('variance_threshold', document.getElementById('ns-threshold').value);
    fd.append('notes', document.getElementById('ns-notes').value);

    fetch(STK_AJAX + 'st_initiate', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-play-circle"></i> Initiate Stocktake';
            }
        })
        .catch(() => {
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-play-circle"></i> Initiate Stocktake';
        });
});

function cancelSession(id, number) {
    if (!confirm('Cancel stocktake session ' + number + '? This cannot be undone.')) return;
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    fd.append('id', id);
    fetch(STK_AJAX + 'st_cancel', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Error: ' + data.error); });
}

// Client-side search + status filter
function filterTable() {
    var s = document.getElementById('stk-search').value.toLowerCase();
    var st = document.getElementById('stk-status-filter').value;
    document.querySelectorAll('.stk-row').forEach(function(row) {
        var matchS = !s || row.dataset.search.includes(s);
        var matchSt = !st || row.dataset.status === st;
        row.style.display = matchS && matchSt ? '' : 'none';
    });
}
document.getElementById('stk-search').addEventListener('input', filterTable);
document.getElementById('stk-status-filter').addEventListener('change', filterTable);

// Close modal on outside click
document.getElementById('new-session-modal').addEventListener('click', function(e) {
    if (e.target === this) closeNewSessionModal();
});
</script>
