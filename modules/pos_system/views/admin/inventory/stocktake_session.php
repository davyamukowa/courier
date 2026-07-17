<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$sess       = $session;
$sess_id    = (int)$sess['id'];
$status     = $sess['status'];
$is_active  = in_array($status, ['in_progress','draft']);
$is_done    = $status === 'completed';
$blind      = (int)$sess['blind_counting'];
$threshold  = (float)($sess['variance_threshold'] ?? 0);
$scope_lbl  = ['full'=>'Full Warehouse','category'=>'By Category','product'=>'Specific Products'][$sess['scope'] ?? 'full'] ?? 'Full';
$status_color = ['draft'=>'#64748b','in_progress'=>'#d97706','completed'=>'#16a34a','cancelled'=>'#dc2626'][$status] ?? '#888';
?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'stocktake',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:0">

<style>
/* ── Stocktake Session ──────────────────────────────────────────────────── */
.stk-header    { background:linear-gradient(135deg,#1a3f26 0%,#255836 100%);padding:18px 24px;color:#fff }
.stk-hdr-top   { display:flex;align-items:center;gap:14px;flex-wrap:wrap }
.stk-badge     { display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px }
.stk-meta      { display:flex;gap:24px;flex-wrap:wrap;margin-top:10px;font-size:12px;color:#a0c4b0 }
.stk-meta span i { margin-right:4px;opacity:.7 }
.stk-body      { padding:20px 24px }
.stk-progress-bar { height:10px;background:#e2ece6;border-radius:99px;overflow:hidden;margin:8px 0 }
.stk-progress-fill{ height:100%;border-radius:99px;transition:width .4s }
/* Tabs */
.stk-tabs      { display:flex;gap:2px;border-bottom:2px solid #e2ece6;margin-bottom:20px }
.stk-tab       { padding:10px 20px;font-size:13px;font-weight:600;color:#4a5e54;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;border-radius:6px 6px 0 0 }
.stk-tab:hover { background:#f0faf4;color:#16a34a }
.stk-tab.active{ color:#16a34a;border-bottom-color:#16a34a;background:#f0faf4 }
/* Count grid */
.stk-grid      { width:100%;border-collapse:separate;border-spacing:0;font-size:13px }
.stk-grid thead th { padding:10px 12px;font-size:11px;font-weight:700;color:#4a5e54;text-transform:uppercase;letter-spacing:.5px;background:#f0f7f2;border-bottom:1px solid #e2ece6;white-space:nowrap;position:sticky;top:0;z-index:2 }
.stk-grid tbody td { padding:10px 12px;border-bottom:1px solid #edf5f0;vertical-align:middle }
.stk-grid tbody tr:hover { background:#f8fdf9 }
.stk-row-ok     { background:#f0fdf4!important }
.stk-row-var    { background:#fffbeb!important }
.stk-row-high   { background:#fff1f2!important }
.stk-row-recount{ background:#eff6ff!important }
.qty-input      { width:80px;height:32px;border:1px solid #c8dfd0;border-radius:6px;padding:0 8px;font-size:13px;font-weight:700;text-align:center;outline:none;transition:border .15s,box-shadow .15s }
.qty-input:focus{ border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15) }
.qty-input.dirty{ border-color:#d97706;background:#fffbeb }
.qty-input.saved{ border-color:#16a34a;background:#f0fdf4 }
.stk-var-pill   { display:inline-block;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700;min-width:40px;text-align:center }
.var-pos        { background:#dcfce7;color:#14532d }
.var-neg        { background:#fce7e7;color:#991b1b }
.var-zero       { background:#f0f0f0;color:#666 }
.var-null       { background:#f5f5f5;color:#aaa }
/* Scanner bar */
.stk-scanner    { display:flex;align-items:center;gap:10px;padding:12px 18px;background:linear-gradient(135deg,#1a3f26,#255836);border-radius:9px;margin-bottom:18px }
.stk-scanner label { color:#c8e8d4;font-size:12px;font-weight:700;white-space:nowrap;margin:0 }
.stk-scanner input  { flex:1;height:40px;border:2px solid #4a8a62;border-radius:7px;padding:0 16px;font-size:15px;font-weight:700;color:#1a2520;background:#fff;outline:none;letter-spacing:1px }
.stk-scanner input:focus { border-color:#4ade80;box-shadow:0 0 0 3px rgba(74,222,128,.2) }
.stk-scanner .scan-qty { width:70px;height:40px;border:2px solid #4a8a62;border-radius:7px;padding:0 10px;font-size:15px;font-weight:700;text-align:center;color:#1a2520;background:#fff;outline:none }
.stk-scanner .scan-qty:focus { border-color:#4ade80}
/* Variance report */
.var-summary-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px }
.var-card      { background:#fff;border-radius:10px;border:1px solid #e2ece6;padding:16px;text-align:center }
.var-card-val  { font-size:24px;font-weight:800;line-height:1 }
.var-card-lbl  { font-size:11px;color:#6b7c6a;text-transform:uppercase;letter-spacing:.5px;margin-top:6px }
/* Offline queue badge */
.offline-badge { display:none;background:#ef4444;color:#fff;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;margin-left:6px }
/* Mobile count panel */
.mobile-count-panel { background:#fff;border:1px solid #d4e8db;border-radius:10px;padding:16px;margin-bottom:16px }
</style>

<!-- Session Header -->
<div class="stk-header">
  <div class="stk-hdr-top">
    <a href="<?php echo admin_url('pos_system/inventory/stocktake') ?>" style="color:#a0c4b0;font-size:13px;text-decoration:none">
      <i class="fa fa-arrow-left"></i> All Sessions
    </a>
    <i class="fa fa-clipboard-check" style="font-size:22px;color:#4ade80"></i>
    <h2 style="margin:0;font-size:18px;font-weight:800"><?php echo htmlspecialchars($sess['stocktake_number']) ?></h2>
    <span class="stk-badge" style="background:<?php echo $status_color ?>22;color:<?php echo $status_color ?>;border:1px solid <?php echo $status_color ?>44">
      <i class="fa fa-circle" style="font-size:7px"></i>
      <?php echo ucwords(str_replace('_',' ',$status)) ?>
    </span>
    <?php if ($blind): ?>
    <span class="stk-badge" style="background:#fbbf2422;color:#fbbf24;border:1px solid #fbbf2444">
      <i class="fa fa-eye-slash"></i> BLIND MODE
    </span>
    <?php endif; ?>
    <?php if ($sess['freeze_active']): ?>
    <span class="stk-badge" style="background:#ef444422;color:#ef4444;border:1px solid #ef444444">
      <i class="fa fa-lock"></i> FROZEN
    </span>
    <?php endif; ?>

    <!-- Action buttons -->
    <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <?php if ($is_active && $is_manager): ?>
      <button onclick="openPostModal()" class="btn-inv-primary" style="background:linear-gradient(135deg,#16a34a,#15803d)">
        <i class="fa fa-check-double"></i> Post Adjustments
      </button>
      <?php endif; ?>
      <?php if ($is_active): ?>
      <button onclick="openScannerFocus()" class="btn-inv-secondary" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff">
        <i class="fa fa-barcode"></i> Scanner Mode
      </button>
      <?php endif; ?>
      <button onclick="printCountSheet('visible')" class="btn-inv-secondary" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff">
        <i class="fa fa-print"></i> Print Filtered
      </button>
      <?php if ($is_done && $is_manager): ?>
      <button onclick="exportReport()" class="btn-inv-secondary" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff">
        <i class="fa fa-file-csv"></i> Export CSV
      </button>
      <?php endif; ?>
    </div>
  </div>

  <div class="stk-meta">
    <span><i class="fa fa-building"></i> <?php echo htmlspecialchars($sess['branch_name'] ?: '—') ?></span>
    <span><i class="fa fa-layer-group"></i> <?php echo $scope_lbl ?></span>
    <span><i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($sess['start_date'])) ?></span>
    <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($sess['created_by_name'] ?: '—') ?></span>
    <?php if ($sess['snapshot_timestamp']): ?>
    <span><i class="fa fa-camera"></i> Snapshot: <?php echo date('d M Y H:i', strtotime($sess['snapshot_timestamp'])) ?></span>
    <?php endif; ?>
    <?php if ($threshold > 0): ?>
    <span><i class="fa fa-exclamation-triangle"></i> Alert threshold: <?php echo $threshold ?> units</span>
    <?php endif; ?>
  </div>

  <!-- Progress bar -->
  <div style="margin-top:14px">
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#a0c4b0;margin-bottom:4px">
      <span>Counting Progress</span>
      <span id="progress-text">Loading…</span>
    </div>
    <div class="stk-progress-bar">
      <div class="stk-progress-fill" id="progress-fill" style="width:0%;background:#16a34a"></div>
    </div>
  </div>
</div>

<!-- Body -->
<div class="stk-body">

  <!-- Offline indicator -->
  <div id="offline-bar" style="display:none;background:#ef4444;color:#fff;padding:8px 16px;border-radius:8px;margin-bottom:12px;font-size:12px;font-weight:700;text-align:center">
    <i class="fa fa-wifi"></i> You are offline — counts are queued locally and will sync automatically when reconnected
    <span id="offline-queue-count" style="margin-left:8px;background:rgba(255,255,255,.25);padding:2px 8px;border-radius:99px"></span>
  </div>

  <!-- PDF Count Sheet banner -->
  <div style="background:#f0faf4;border:1.5px solid #b6dfc8;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
      <span style="background:#16a34a;border-radius:8px;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fa fa-print" style="color:#fff;font-size:16px"></i>
      </span>
      <div>
        <div style="font-weight:700;font-size:13px;color:#1a3f26">Printable Stock Count Sheet</div>
        <div style="font-size:11px;color:#4a6a56;margin-top:1px">Print this form and give to warehouse clerks for manual counting. <strong>System quantities are never shown</strong> — clerks count blind and record actual quantities on paper.</div>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
      <button onclick="printCountSheet('uncounted')" style="height:36px;padding:0 16px;background:#16a34a;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px">
        <i class="fa fa-print"></i> Uncounted Items
      </button>
      <button onclick="printCountSheet('all')" style="height:36px;padding:0 16px;background:#fff;color:#1a3f26;border:1.5px solid #b6dfc8;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px">
        <i class="fa fa-list"></i> All Items
      </button>
    </div>
  </div>

  <!-- Tabs -->
  <div class="stk-tabs">
    <div class="stk-tab active" onclick="switchTab('counting')" id="tab-counting">
      <i class="fa fa-clipboard-list"></i> Count Sheet
      <span id="uncounted-badge" class="offline-badge" style="display:inline-block;background:#d97706">0</span>
    </div>
    <?php if ($is_manager): ?>
    <div class="stk-tab" onclick="switchTab('variance')" id="tab-variance">
      <i class="fa fa-chart-bar"></i> Variance Analysis
    </div>
    <div class="stk-tab" onclick="switchTab('audit')" id="tab-audit">
      <i class="fa fa-shield-alt"></i> Audit Log
    </div>
    <?php endif; ?>
  </div>

  <!-- TAB: Count Sheet -->
  <div id="pane-counting">
    <?php if ($is_active): ?>
    <!-- Barcode Scanner Bar -->
    <div class="stk-scanner">
      <i class="fa fa-barcode" style="color:#4ade80;font-size:20px"></i>
      <label>SCAN / TYPE BARCODE OR SKU:</label>
      <input type="text" id="scanner-input" autocomplete="off" autocorrect="off" spellcheck="false"
             placeholder="Scan barcode or type SKU, then press Enter…"
             onkeydown="handleScannerKey(event)">
      <label style="margin-left:8px">QTY:</label>
      <input type="number" id="scanner-qty" class="scan-qty" min="0" step="1" value="1" title="Scan quantity">
      <button onclick="processScan()" style="height:40px;padding:0 16px;background:#16a34a;color:#fff;border:none;border-radius:7px;font-weight:700;cursor:pointer;font-size:13px">
        <i class="fa fa-plus"></i> Add
      </button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
      <input type="text" id="grid-search" class="inv-input" placeholder="Search product/SKU…" style="width:200px" oninput="filterGrid()">
      <select id="grid-filter" class="inv-input" style="width:160px;cursor:pointer" onchange="filterGrid()">
        <option value="">All Items</option>
        <option value="uncounted">Not Yet Counted</option>
        <option value="counted">Counted</option>
        <option value="variance">Has Variance</option>
        <option value="high">High Variance ⚠</option>
        <option value="recount">Recount Requested</option>
        <option value="found">Found Stock</option>
      </select>
      <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
        <span id="grid-count-label" style="font-size:12px;color:#6b7c6a"></span>
        <?php if ($is_active): ?>
        <button onclick="saveAllPending()" class="btn-inv-secondary" id="save-all-btn" style="display:none">
          <i class="fa fa-save"></i> Save All Pending
        </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Count Grid -->
    <div style="overflow:auto;max-height:calc(100vh - 380px);border:1px solid #e2ece6;border-radius:10px">
      <table class="stk-grid">
        <thead>
          <tr>
            <th style="width:32px">#</th>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Bin</th>
            <th style="text-align:center;min-width:110px">Counted Qty</th>
            <th>Reason</th>
            <th style="text-align:center;width:80px">Actions</th>
          </tr>
        </thead>
        <tbody id="count-tbody">
          <tr><td colspan="8" style="text-align:center;padding:40px;color:#aaa">
            <i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Loading items…
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TAB: Variance Analysis (manager only) -->
  <?php if ($is_manager): ?>
  <div id="pane-variance" style="display:none">
    <div class="var-summary-grid" id="var-summary-cards">
      <div class="var-card"><div class="var-card-val" id="vs-total">—</div><div class="var-card-lbl">Total Items</div></div>
      <div class="var-card"><div class="var-card-val" style="color:#d97706" id="vs-var">—</div><div class="var-card-lbl">Items with Variance</div></div>
      <div class="var-card"><div class="var-card-val" style="color:#dc2626" id="vs-loss">—</div><div class="var-card-lbl">Financial Loss</div></div>
      <div class="var-card"><div class="var-card-val" style="color:#16a34a" id="vs-gain">—</div><div class="var-card-lbl">Financial Gain</div></div>
    </div>
    <div id="var-alert-box" style="display:none;background:#fff1f2;border:1px solid #fecdd3;border-radius:9px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#991b1b">
      <i class="fa fa-exclamation-triangle"></i> <strong id="vs-above-threshold">0</strong> items exceed your variance threshold of <strong><?php echo $threshold ?></strong> units.
    </div>
    <div style="overflow:auto;border:1px solid #e2ece6;border-radius:10px">
      <table class="stk-grid" id="var-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>SKU</th>
            <th style="text-align:right">Expected</th>
            <th style="text-align:right">Counted</th>
            <th style="text-align:right">Variance</th>
            <th style="text-align:right">Variance %</th>
            <th style="text-align:right">Unit Cost</th>
            <th style="text-align:right">$ Impact</th>
            <th>Reason</th>
            <th style="text-align:center">Action</th>
          </tr>
        </thead>
        <tbody id="var-tbody"><tr><td colspan="11" style="text-align:center;padding:40px;color:#aaa"><i class="fa fa-spinner fa-spin fa-2x"></i></td></tr></tbody>
      </table>
    </div>
  </div>

  <!-- TAB: Audit Log -->
  <div id="pane-audit" style="display:none">
    <div style="overflow:auto;border:1px solid #e2ece6;border-radius:10px">
      <table class="stk-grid" id="audit-table">
        <thead>
          <tr>
            <th>Product</th>
            <th style="text-align:right">Qty Before</th>
            <th style="text-align:right">Qty After</th>
            <th style="text-align:right">Variance</th>
            <th style="text-align:right">Unit Cost</th>
            <th style="text-align:right">$ Impact</th>
            <th>Reason Code</th>
            <th>Posted By</th>
            <th>Posted At</th>
          </tr>
        </thead>
        <tbody id="audit-tbody"><tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa">Select this tab to load audit records…</td></tr></tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div><!-- stk-body -->
</div><!-- inv-content -->
</div><!-- content -->
</div><!-- wrapper -->

<!-- ── Post Adjustments Confirmation Modal ────────────────────────────────── -->
<div id="post-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;width:480px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <div style="background:#dc2626;padding:16px 22px;border-radius:14px 14px 0 0;color:#fff;display:flex;align-items:center;gap:10px">
      <i class="fa fa-exclamation-triangle" style="font-size:18px"></i>
      <h3 style="margin:0;font-size:15px;font-weight:700">Confirm: Post Stock Adjustments</h3>
    </div>
    <div style="padding:22px">
      <p style="color:#1a2520;font-size:14px;margin-bottom:16px">
        This will <strong>permanently adjust live inventory</strong> based on counted quantities and write an immutable entry to the Audit Ledger. This action <strong>cannot be reversed</strong>.
      </p>
      <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:12px;margin-bottom:18px;font-size:12px;color:#991b1b">
        <strong id="post-summary">Loading summary…</strong>
      </div>
      <div style="margin-bottom:16px">
        <label style="font-size:12px;font-weight:700;color:#3d4f45;display:block;margin-bottom:5px">Type <em>CONFIRM</em> to proceed:</label>
        <input type="text" id="post-confirm-text" class="inv-input" style="width:100%" placeholder="CONFIRM">
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button onclick="closePostModal()" class="btn-inv-secondary">Cancel</button>
        <button onclick="doPostAdjustments()" id="post-submit-btn" class="btn-inv-primary" style="background:linear-gradient(135deg,#dc2626,#b91c1c)">
          <i class="fa fa-check-double"></i> Post Now
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Reason Code Modal (per-item) ──────────────────────────────────────── -->
<div id="reason-modal" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.4);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;width:420px;max-width:95vw;box-shadow:0 16px 48px rgba(0,0,0,.2)">
    <div style="background:#f0f7f2;padding:14px 20px;border-radius:12px 12px 0 0;border-bottom:1px solid #e2ece6;display:flex;align-items:center;gap:8px">
      <i class="fa fa-tag" style="color:#16a34a"></i>
      <h4 style="margin:0;font-size:14px;font-weight:700" id="reason-modal-title">Set Reason Code</h4>
      <button onclick="closeReasonModal()" style="margin-left:auto;background:none;border:none;font-size:16px;cursor:pointer;color:#666">&times;</button>
    </div>
    <div style="padding:18px">
      <input type="hidden" id="reason-item-id">
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Reason Code</label>
        <select id="reason-select" class="inv-input" style="width:100%;height:34px">
          <?php foreach ($reason_codes as $k => $v): ?>
          <option value="<?php echo $k ?>"><?php echo $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="margin-bottom:16px">
        <label style="font-size:11px;font-weight:700;color:#3d4f45;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px">Notes (optional)</label>
        <textarea id="reason-notes" rows="2" class="inv-input" style="width:100%;height:60px;resize:vertical" placeholder="Add notes…"></textarea>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button onclick="closeReasonModal()" class="btn-inv-secondary">Cancel</button>
        <button onclick="saveReason()" class="btn-inv-primary"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
var STK_ID      = <?php echo $sess_id ?>;
var IS_ACTIVE   = <?php echo $is_active ? 'true' : 'false' ?>;
var IS_MANAGER  = <?php echo $is_manager ? 'true' : 'false' ?>;
var IS_BLIND    = <?php echo $blind ?>;
var THRESHOLD   = <?php echo $threshold ?>;
var CSRF_NAME   = '<?php echo $this->security->get_csrf_token_name() ?>';
var CSRF_HASH   = '<?php echo $this->security->get_csrf_hash() ?>';
var AJAX_BASE   = '<?php echo admin_url('pos_system/inv_ajax/') ?>';
var items       = [];       // full items array from server
var pendingRows = {};       // product_id -> {qty, reason, notes}
var offlineQueue = [];      // for offline mode
var isOnline    = navigator.onLine;
var auditLoaded = false;

// ── Offline detection ────────────────────────────────────────────────────────
function checkOnline() {
    isOnline = navigator.onLine;
    document.getElementById('offline-bar').style.display = isOnline ? 'none' : 'block';
    if (isOnline && offlineQueue.length) syncOfflineQueue();
}
window.addEventListener('online',  checkOnline);
window.addEventListener('offline', checkOnline);
checkOnline();

function syncOfflineQueue() {
    if (!offlineQueue.length || !isOnline) return;
    var toSync = offlineQueue.slice();
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    toSync.forEach((e,i) => {
        Object.keys(e).forEach(k => fd.append('counts['+i+']['+k+']', e[k]));
    });
    fetch(AJAX_BASE + 'st_batch_sync', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) { offlineQueue = offlineQueue.filter(e => !toSync.includes(e)); updateOfflineCount(); } });
}
function updateOfflineCount() {
    var el = document.getElementById('offline-queue-count');
    el.textContent = offlineQueue.length ? offlineQueue.length + ' queued' : '';
}

// ── Load items ───────────────────────────────────────────────────────────────
function loadItems() {
    fetch(AJAX_BASE + 'st_session_items?id=' + STK_ID)
        .then(r => r.json())
        .then(data => {
            items = data.items || [];
            updateProgress(data.counted, data.total);
            renderGrid(items);
        });
}

function updateProgress(counted, total) {
    var pct = total > 0 ? Math.round((counted / total) * 100) : 0;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('progress-fill').style.background = pct === 100 ? '#16a34a' : '#d97706';
    document.getElementById('progress-text').textContent = counted + ' / ' + total + ' counted (' + pct + '%)';
    var uncounted = total - counted;
    var badge = document.getElementById('uncounted-badge');
    badge.textContent = uncounted;
    badge.style.display = uncounted > 0 ? 'inline-block' : 'none';
}

// ── Render count grid ────────────────────────────────────────────────────────
function renderGrid(rows) {
    var tbody = document.getElementById('count-tbody');
    var search  = (document.getElementById('grid-search').value || '').toLowerCase();
    var flt     = document.getElementById('grid-filter').value;
    var visible = rows.filter(function(it) {
        if (search && !(it.product_name + ' ' + it.sku + ' ' + (it.barcode||'')).toLowerCase().includes(search)) return false;
        if (flt === 'uncounted')  return it.counted_qty === null;
        if (flt === 'counted')    return it.counted_qty !== null;
        if (flt === 'variance')   return it.variance !== null && it.variance !== 0;
        if (flt === 'high')       return THRESHOLD > 0 && it.variance !== null && Math.abs(it.variance) > THRESHOLD;
        if (flt === 'recount')    return it.recount_requested;
        if (flt === 'found')      return it.is_found_stock;
        return true;
    });

    document.getElementById('grid-count-label').textContent = visible.length + ' of ' + rows.length + ' items';

    if (!visible.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:#aaa"><i class="fa fa-search"></i><br>No matching items</td></tr>';
        return;
    }

    var html = '';
    visible.forEach(function(it, idx) {
        var rowClass = '';
        if (it.recount_requested)   rowClass = 'stk-row-recount';
        else if (it.counted_qty === null) rowClass = '';
        else if (it.variance === 0) rowClass = 'stk-row-ok';
        else if (THRESHOLD > 0 && Math.abs(it.variance) > THRESHOLD) rowClass = 'stk-row-high';
        else if (it.variance !== 0) rowClass = 'stk-row-var';

        var foundBadge   = it.is_found_stock     ? ' <span style="background:#ede9fe;color:#6d28d9;border-radius:99px;padding:1px 6px;font-size:10px;font-weight:700">FOUND</span>'   : '';
        var recountBadge = it.recount_requested  ? ' <span style="background:#dbeafe;color:#1d4ed8;border-radius:99px;padding:1px 6px;font-size:10px;font-weight:700">RECOUNT</span>' : '';

        var pendKey = 'p_' + it.product_id;
        var pending = pendingRows[pendKey];
        var cntVal = pending ? pending.qty : (it.counted_qty !== null ? it.counted_qty : '');
        var inputExtra = '';
        if (pending)           inputExtra = ' dirty';
        else if (it.counted_qty !== null) inputExtra = ' saved';

        var reasonLabel = it.reason_code ? '<span style="font-size:11px;color:#6b7c6a">' + escHtml(it.reason_code.replace(/_/g,' ')) + '</span>' : '<span style="color:#ccc;font-size:11px">—</span>';

        var actions = '';
        if (IS_ACTIVE) {
            actions += '<button class="btn-inv-icon" onclick="openReasonModal(' + it.id + ',' + it.product_id + ')" title="Set reason"><i class="fa fa-tag"></i></button>';
        }
        if (IS_MANAGER && it.counted_qty !== null) {
            actions += '<button class="btn-inv-icon" onclick="requestRecount(' + it.id + ')" title="Request recount"><i class="fa fa-redo"></i></button>';
        }

        html += '<tr class="' + rowClass + '" data-pid="' + it.product_id + '" id="grid-row-' + it.product_id + '">';
        html += '<td style="color:#aaa;font-size:11px">' + (idx+1) + '</td>';
        html += '<td style="font-weight:600">' + escHtml(it.product_name) + foundBadge + recountBadge + '</td>';
        html += '<td style="font-size:12px;color:#5a6a62">' + escHtml(it.sku || '—') + '</td>';
        html += '<td style="font-size:12px;color:#5a6a62">' + escHtml(it.category_name || '—') + '</td>';
        html += '<td style="font-size:12px">' + escHtml(it.bin_location || '—') + '</td>';
        if (IS_ACTIVE) {
            html += '<td style="text-align:center"><input type="number" min="0" step="0.01" class="qty-input' + inputExtra + '" data-pid="' + it.product_id + '" data-iid="' + it.id + '" value="' + cntVal + '" onchange="markDirty(this)" onkeydown="handleQtyKey(event,this)"></td>';
        } else {
            var savedVal = it.counted_qty !== null ? '<span style="font-weight:700;color:#16a34a">' + it.counted_qty.toFixed(2) + '</span>' : '<span style="color:#aaa">—</span>';
            html += '<td style="text-align:center">' + savedVal + '</td>';
        }
        html += '<td>' + reasonLabel + '</td>';
        html += '<td style="text-align:center;white-space:nowrap">' + actions + '</td>';
        html += '</tr>';
    });
    tbody.innerHTML = html;

    // Show save-all btn if there are pending rows
    document.getElementById('save-all-btn') && (document.getElementById('save-all-btn').style.display = Object.keys(pendingRows).length > 0 ? '' : 'none');
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function filterGrid() { renderGrid(items); }

// ── Quantity input handling ──────────────────────────────────────────────────
function markDirty(input) {
    var pid = input.dataset.pid;
    var val = input.value;
    if (val === '') { delete pendingRows['p_'+pid]; input.classList.remove('dirty','saved'); return; }
    pendingRows['p_'+pid] = { qty: parseFloat(val), product_id: pid };
    input.classList.add('dirty');
    input.classList.remove('saved');
    document.getElementById('save-all-btn') && (document.getElementById('save-all-btn').style.display = '');
}

function handleQtyKey(e, input) {
    if (e.key === 'Enter') {
        e.preventDefault();
        submitCount(parseInt(input.dataset.pid), parseInt(input.dataset.iid), parseFloat(input.value), input);
    }
}

function submitCount(productId, itemId, qty, inputEl) {
    if (qty < 0 || isNaN(qty)) { alert('Quantity cannot be negative.'); return; }
    var pending = pendingRows['p_'+productId] || {};
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    fd.append('stock_take_id', STK_ID);
    fd.append('product_id', productId);
    fd.append('counted_qty', qty);
    fd.append('reason_code', pending.reason || '');
    fd.append('notes', pending.notes || '');
    fd.append('is_offline', isOnline ? 0 : 1);

    if (!isOnline) {
        offlineQueue.push({ stock_take_id: STK_ID, product_id: productId, counted_qty: qty,
                            reason_code: pending.reason || '', recorded_at: new Date().toISOString() });
        updateOfflineCount();
        if (inputEl) { inputEl.classList.remove('dirty'); inputEl.classList.add('saved'); }
        updateLocalItem(productId, qty);
        return;
    }

    fetch(AJAX_BASE + 'st_submit_count', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (inputEl) { inputEl.classList.remove('dirty'); inputEl.classList.add('saved'); }
                delete pendingRows['p_'+productId];
                updateLocalItem(productId, qty, data.variance);
                updateProgress(items.filter(i => i.counted_qty !== null).length, items.length);
            } else {
                alert('Error: ' + (data.error || 'Failed to save'));
            }
        })
        .catch(() => {
            // Network error — queue offline
            offlineQueue.push({ stock_take_id: STK_ID, product_id: productId, counted_qty: qty });
            updateOfflineCount();
            if (inputEl) { inputEl.classList.remove('dirty'); inputEl.classList.add('saved'); }
            updateLocalItem(productId, qty);
        });
}

function updateLocalItem(productId, qty, variance) {
    items.forEach(function(it) {
        if (it.product_id == productId) {
            it.counted_qty = qty;
            if (variance !== undefined) it.variance = variance;
            else it.variance = qty - (it.qty_system || 0);
            it.financial_impact = it.variance * it.unit_cost;
        }
    });
    // Re-render the specific row by re-rendering
    filterGrid();
}

function saveAllPending() {
    Object.keys(pendingRows).forEach(function(key) {
        var pid = parseInt(pendingRows[key].product_id);
        var qty = pendingRows[key].qty;
        var row = document.querySelector('.qty-input[data-pid="'+pid+'"]');
        submitCount(pid, 0, qty, row);
    });
}

// ── Barcode Scanner ──────────────────────────────────────────────────────────
function openScannerFocus() {
    document.getElementById('scanner-input').focus();
}
function handleScannerKey(e) {
    if (e.key === 'Enter') { e.preventDefault(); processScan(); }
}
function processScan() {
    var barcode = document.getElementById('scanner-input').value.trim();
    var qty     = parseFloat(document.getElementById('scanner-qty').value) || 1;
    if (!barcode) return;
    // Find item by barcode or SKU
    var found = items.find(i => i.barcode === barcode || i.sku === barcode);
    if (!found) {
        showScanFeedback('Not found: ' + barcode, false);
        document.getElementById('scanner-input').select();
        return;
    }
    var existingQty = found.counted_qty !== null ? found.counted_qty : 0;
    var newQty = existingQty + qty;
    var row = document.querySelector('.qty-input[data-pid="'+found.product_id+'"]');
    if (row) { row.value = newQty; }
    submitCount(found.product_id, found.id, newQty, row);
    showScanFeedback(found.product_name + ' → ' + newQty, true);
    document.getElementById('scanner-input').value = '';
    document.getElementById('scanner-input').focus();
}
function showScanFeedback(msg, ok) {
    var bar = document.getElementById('scanner-input');
    bar.style.borderColor = ok ? '#16a34a' : '#dc2626';
    bar.style.background  = ok ? '#f0fdf4' : '#fff1f2';
    setTimeout(() => { bar.style.borderColor = ''; bar.style.background = ''; }, 1200);
}

// ── Variance Analysis tab ────────────────────────────────────────────────────
function loadVariance() {
    document.getElementById('var-tbody').innerHTML = '<tr><td colspan="11" style="text-align:center;padding:40px;color:#aaa"><i class="fa fa-spinner fa-spin fa-2x"></i></td></tr>';
    fetch(AJAX_BASE + 'st_variance?id=' + STK_ID)
        .then(r => r.json())
        .then(data => {
            var s = data.summary;
            document.getElementById('vs-total').textContent = s.total_items;
            document.getElementById('vs-var').textContent   = s.items_with_variance;
            document.getElementById('vs-loss').textContent  = s.total_loss_impact.toFixed(2);
            document.getElementById('vs-gain').textContent  = '+' + s.total_gain_impact.toFixed(2);
            if (s.items_above_threshold > 0 && THRESHOLD > 0) {
                document.getElementById('var-alert-box').style.display = '';
                document.getElementById('vs-above-threshold').textContent = s.items_above_threshold;
            }
            var rows = data.items.map(function(it, idx) {
                var v  = it.variance || 0;
                var fi = it.financial_impact || 0;
                var flagColor = it.flag === 'high' ? '#991b1b' : (it.flag === 'low' ? '#78350f' : '#166534');
                var flagBg    = it.flag === 'high' ? '#fff1f2' : (it.flag === 'low' ? '#fffbeb' : '#f0fdf4');
                var sign = v > 0 ? '+' : '';
                var fiSign = fi >= 0 ? '+' : '';
                var recountBtn = IS_ACTIVE ? '<button class="btn-inv-icon" onclick="requestRecountByPid('+it.id+')" title="Recount"><i class="fa fa-redo"></i></button>' : '—';
                return '<tr style="background:'+flagBg+'">' +
                    '<td style="color:#aaa;font-size:11px">'+(idx+1)+'</td>' +
                    '<td style="font-weight:600">'+escHtml(it.product_name)+'</td>' +
                    '<td style="font-size:12px;color:#5a6a62">'+escHtml(it.sku||'—')+'</td>' +
                    '<td style="text-align:right">'+parseFloat(it.qty_system).toFixed(2)+'</td>' +
                    '<td style="text-align:right;font-weight:700">'+parseFloat(it.counted_qty||0).toFixed(2)+'</td>' +
                    '<td style="text-align:right"><span class="stk-var-pill '+(v>0?'var-pos':v<0?'var-neg':'var-zero')+'">'+sign+v.toFixed(2)+'</span></td>' +
                    '<td style="text-align:right;color:'+flagColor+';font-weight:700">'+(it.pct_variance!==null?it.pct_variance.toFixed(1)+'%':'N/A')+'</td>' +
                    '<td style="text-align:right">'+parseFloat(it.unit_cost).toFixed(4)+'</td>' +
                    '<td style="text-align:right;font-weight:700;color:'+(fi<0?'#dc2626':fi>0?'#16a34a':'#666')+'">'+fiSign+fi.toFixed(2)+'</td>' +
                    '<td style="font-size:12px">'+escHtml((it.reason_code||'').replace(/_/g,' '))+'</td>' +
                    '<td style="text-align:center">'+recountBtn+'</td>' +
                    '</tr>';
            });
            document.getElementById('var-tbody').innerHTML = rows.length ? rows.join('') :
                '<tr><td colspan="11" style="text-align:center;padding:30px;color:#aaa">No variance items found</td></tr>';
        });
}

// ── Audit Log tab ────────────────────────────────────────────────────────────
function loadAudit() {
    if (auditLoaded) return;
    auditLoaded = true;
    fetch(AJAX_BASE + 'audit_ledger?ref_type=stocktake&ref_id=' + STK_ID)
        .then(r => r.json())
        .then(data => {
            var rows = (data.rows || []).map(function(r) {
                var v = parseFloat(r.qty_variance);
                var fi = parseFloat(r.financial_impact);
                return '<tr>' +
                    '<td>'+escHtml(r.product_name||r.product_id)+'</td>' +
                    '<td style="text-align:right">'+parseFloat(r.qty_before).toFixed(2)+'</td>' +
                    '<td style="text-align:right">'+parseFloat(r.qty_after).toFixed(2)+'</td>' +
                    '<td style="text-align:right"><span class="stk-var-pill '+(v>0?'var-pos':v<0?'var-neg':'var-zero')+'">'+(v>0?'+':'')+v.toFixed(2)+'</span></td>' +
                    '<td style="text-align:right">'+parseFloat(r.unit_cost).toFixed(4)+'</td>' +
                    '<td style="text-align:right;font-weight:700;color:'+(fi<0?'#dc2626':fi>0?'#16a34a':'#666')+'">'+(fi>=0?'+':'')+fi.toFixed(2)+'</td>' +
                    '<td style="font-size:12px">'+escHtml((r.reason_code||'—').replace(/_/g,' '))+'</td>' +
                    '<td style="font-size:12px">'+escHtml(r.posted_by_name||r.posted_by)+'</td>' +
                    '<td style="font-size:12px">'+escHtml(r.posted_at||'—')+'</td>' +
                    '</tr>';
            });
            document.getElementById('audit-tbody').innerHTML = rows.length ? rows.join('') :
                '<tr><td colspan="9" style="text-align:center;padding:30px;color:#aaa">No audit entries yet — adjustments have not been posted</td></tr>';
        })
        .catch(() => {
            document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="9" style="text-align:center;padding:30px;color:#aaa">Could not load audit log</td></tr>';
        });
}

// ── Tab switching ────────────────────────────────────────────────────────────
function switchTab(name) {
    ['counting','variance','audit'].forEach(function(t) {
        var pane = document.getElementById('pane-'+t);
        var tab  = document.getElementById('tab-'+t);
        if (pane) pane.style.display = t === name ? '' : 'none';
        if (tab)  tab.classList.toggle('active', t === name);
    });
    if (name === 'variance') loadVariance();
    if (name === 'audit')   loadAudit();
}

// ── Recount ──────────────────────────────────────────────────────────────────
function requestRecount(itemId) {
    if (!confirm('Flag this item for recount? The counted quantity will be cleared.')) return;
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    fd.append('item_id', itemId);
    fetch(AJAX_BASE + 'st_recount', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) loadItems(); else alert('Error: ' + d.error); });
}
function requestRecountByPid(itemId) { requestRecount(itemId); }

// ── Reason modal ─────────────────────────────────────────────────────────────
var _reasonItemId = null, _reasonPid = null;
function openReasonModal(itemId, productId) {
    _reasonItemId = itemId; _reasonPid = productId;
    var it = items.find(i => i.id == itemId || i.product_id == productId);
    document.getElementById('reason-modal-title').textContent = 'Reason Code — ' + (it ? it.product_name : '');
    document.getElementById('reason-select').value = (it && it.reason_code) ? it.reason_code : 'stocktake';
    document.getElementById('reason-notes').value  = (it && it.notes) ? it.notes : '';
    document.getElementById('reason-modal').style.display = 'flex';
}
function closeReasonModal() { document.getElementById('reason-modal').style.display = 'none'; }
function saveReason() {
    var code  = document.getElementById('reason-select').value;
    var notes = document.getElementById('reason-notes').value;
    if (_reasonPid) {
        if (!pendingRows['p_'+_reasonPid]) pendingRows['p_'+_reasonPid] = { product_id: _reasonPid };
        pendingRows['p_'+_reasonPid].reason = code;
        pendingRows['p_'+_reasonPid].notes  = notes;
        items.forEach(it => { if (it.product_id == _reasonPid || it.id == _reasonItemId) { it.reason_code = code; it.notes = notes; } });
    }
    closeReasonModal();
    filterGrid();
}

// ── Post adjustments ─────────────────────────────────────────────────────────
function openPostModal() {
    var counted = items.filter(i => i.counted_qty !== null).length;
    var withVar = items.filter(i => i.variance !== 0 && i.counted_qty !== null).length;
    document.getElementById('post-summary').textContent = counted + ' items counted, ' + withVar + ' with variances. ' + (items.length - counted) + ' uncounted items will be skipped.';
    document.getElementById('post-confirm-text').value = '';
    document.getElementById('post-modal').style.display = 'flex';
}
function closePostModal() { document.getElementById('post-modal').style.display = 'none'; }
function doPostAdjustments() {
    if (document.getElementById('post-confirm-text').value !== 'CONFIRM') {
        alert('Please type CONFIRM to proceed.'); return;
    }
    var btn = document.getElementById('post-submit-btn');
    btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Posting…';
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    fd.append('id', STK_ID);
    fetch(AJAX_BASE + 'st_post_adjustments', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            closePostModal();
            if (data.success) {
                alert('✓ Adjustments posted successfully. ' + data.adjusted + ' items adjusted.');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                btn.disabled = false; btn.innerHTML = '<i class="fa fa-check-double"></i> Post Now';
            }
        });
}

// ── CSV Export ───────────────────────────────────────────────────────────────
function exportReport() {
    var cols = ['Product','SKU','Category','System Qty','Counted Qty','Variance','Unit Cost','Financial Impact','Reason Code','Bin Location'];
    var rows = items.map(function(it) {
        return [
            '"'+String(it.product_name||'').replace(/"/g,'""')+'"',
            '"'+(it.sku||'')+'","'+(it.category_name||'')+'",',
            it.qty_system, it.counted_qty, it.variance, it.unit_cost, it.financial_impact,
            '"'+(it.reason_code||'').replace(/_/g,' ')+'"',
            '"'+(it.bin_location||'')+'"'
        ].join(',');
    });
    var csv = cols.join(',') + '\n' + rows.join('\n');
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'stocktake_<?php echo $sess['stocktake_number'] ?>_report.csv';
    a.click();
}


// ── Print Count Sheet ────────────────────────────────────────────────────────
function printCountSheet(mode) {
    if (!items.length) { alert('Items not loaded yet. Wait for the list to load and try again.'); return; }

    var toPrint = items;
    if (mode === 'uncounted') toPrint = items.filter(function(i) { return i.counted_qty === null; });
    else if (mode === 'visible') {
        var s = (document.getElementById('grid-search').value || '').toLowerCase();
        var f = document.getElementById('grid-filter').value;
        toPrint = items.filter(function(it) {
            if (s && !(it.product_name + ' ' + (it.sku||'') + ' ' + (it.barcode||'')).toLowerCase().includes(s)) return false;
            if (f === 'uncounted')  return it.counted_qty === null;
            if (f === 'counted')    return it.counted_qty !== null;
            if (f === 'variance')   return it.variance !== null && it.variance !== 0;
            if (f === 'recount')    return it.recount_requested;
            if (f === 'found')      return it.is_found_stock;
            return true;
        });
    }
    if (!toPrint.length) { alert('No items match the selected filter.'); return; }

    var sessNum  = '<?php echo addslashes(htmlspecialchars($sess['stocktake_number'])) ?>';
    var branch   = '<?php echo addslashes(htmlspecialchars($sess['branch_name'] ?: '—')) ?>';
    var scopeLbl = '<?php echo addslashes($scope_lbl) ?>';
    var startDt  = '<?php echo date('d M Y', strtotime($sess['start_date'])) ?>';
    var now      = new Date().toLocaleString();
    var modeLabel = mode === 'uncounted' ? 'Uncounted Items Only' : (mode === 'visible' ? 'Filtered View' : 'All Items');

    var rows = toPrint.map(function(it, idx) {
        return '<tr>' +
            '<td class="num">' + (idx + 1) + '</td>' +
            '<td class="prod">' + escHtml(it.product_name || '') + (it.bin_location ? '<br><span class="sub">Bin: ' + escHtml(it.bin_location) + '</span>' : '') + '</td>' +
            '<td class="code">' + escHtml(it.sku || '—') + '</td>' +
            '<td class="code">' + escHtml(it.barcode || '—') + '</td>' +
            '<td class="cat">' + escHtml(it.category_name || '—') + '</td>' +
            '<td class="qty-cell"></td>' +
            '<td class="qty-cell"></td>' +
            '<td class="notes-cell"></td>' +
            '</tr>';
    }).join('');

    var totalPages = Math.ceil(toPrint.length / 30);

    var html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">' +
        '<title>Count Sheet &mdash; ' + sessNum + '</title>' +
        '<style>' +
        '*{box-sizing:border-box;margin:0;padding:0}' +
        'body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000;background:#fff}' +
        '.page{padding:15mm 12mm;max-width:297mm;margin:0 auto}' +
        '.doc-title{font-size:20px;font-weight:900;text-transform:uppercase;letter-spacing:1px;text-align:center;color:#1a3f26;margin-bottom:2px}' +
        '.doc-sub{font-size:11px;text-align:center;color:#555;margin-bottom:10px}' +
        '.header-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;border:1px solid #c0d4c8;border-radius:6px;padding:10px 14px;margin-bottom:10px;background:#f8fdf9}' +
        '.hf{display:flex;flex-direction:column;gap:2px}' +
        '.hf-label{font-size:9px;text-transform:uppercase;letter-spacing:.5px;color:#6b8c7a;font-weight:700}' +
        '.hf-val{font-size:12px;font-weight:700;color:#1a3f26}' +
        '.signer-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:10px}' +
        '.signer-box{border-bottom:1.5px solid #000;padding-bottom:4px}' +
        '.signer-lbl{font-size:9px;text-transform:uppercase;letter-spacing:.5px;color:#555;margin-bottom:20px;display:block}' +
        '.instructions{background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:8px 12px;margin-bottom:10px;font-size:10px;color:#78350f}' +
        '.instructions strong{display:block;margin-bottom:3px;font-size:11px}' +
        '.instructions ul{margin:0;padding-left:16px}' +
        '.instructions li{margin-bottom:1px}' +
        '.blind-notice{background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;padding:7px 12px;margin-bottom:10px;font-size:10px;color:#1e40af;display:flex;align-items:center;gap:8px}' +
        'table{width:100%;border-collapse:collapse;font-size:10.5px}' +
        'thead th{background:#1a3f26;color:#fff;padding:6px 8px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}' +
        'thead th.r{text-align:center}' +
        'tbody tr{border-bottom:1px solid #d4e8db}' +
        'tbody tr:nth-child(even){background:#f8fdf9}' +
        'tbody td{padding:7px 8px;vertical-align:top}' +
        'td.num{width:28px;color:#999;font-size:10px;text-align:center}' +
        'td.prod{font-weight:600;min-width:140px}' +
        '.sub{font-size:9px;color:#6b8c7a;font-weight:400}' +
        'td.code{font-family:monospace;font-size:10px;color:#333;white-space:nowrap;min-width:70px}' +
        'td.cat{font-size:10px;color:#555;min-width:80px}' +
        'td.qty-cell{width:80px;min-width:80px;border-left:1px dashed #b0ccbc;border-right:1px dashed #b0ccbc}' +
        'td.notes-cell{min-width:100px;border-left:1px dashed #b0ccbc}' +
        '.footer{margin-top:12px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}' +
        '.footer-signer{border-top:1.5px solid #000;padding-top:4px}' +
        '.footer-lbl{font-size:9px;text-transform:uppercase;letter-spacing:.5px;color:#555}' +
        '.footer-space{height:22px}' +
        '.page-info{text-align:center;font-size:9px;color:#999;margin-top:10px}' +
        '@media print{' +
        '@page{size:A4 landscape;margin:10mm}' +
        'body{font-size:10px}' +
        'table{page-break-inside:auto}' +
        'tr{page-break-inside:avoid}' +
        'thead{display:table-header-group}' +
        '}' +
        '</style></head><body>' +
        '<div class="page">' +
        '<div class="doc-title">Physical Inventory Count Sheet</div>' +
        '<div class="doc-sub">Cycle Count &mdash; Stocktake Document</div>' +
        '<div class="header-grid">' +
        '<div class="hf"><span class="hf-label">Session #</span><span class="hf-val">' + sessNum + '</span></div>' +
        '<div class="hf"><span class="hf-label">Branch</span><span class="hf-val">' + branch + '</span></div>' +
        '<div class="hf"><span class="hf-label">Scope</span><span class="hf-val">' + scopeLbl + '</span></div>' +
        '<div class="hf"><span class="hf-label">Count Date</span><span class="hf-val">' + startDt + '</span></div>' +
        '<div class="hf"><span class="hf-label">Total Items</span><span class="hf-val">' + toPrint.length + ' items (' + modeLabel + ')</span></div>' +
        '<div class="hf"><span class="hf-label">Printed</span><span class="hf-val">' + now + '</span></div>' +
        '</div>' +
        '<div class="signer-row">' +
        '<div class="signer-box"><span class="signer-lbl">Counted By (Print Name)</span></div>' +
        '<div class="signer-box"><span class="signer-lbl">Staff ID / Badge #</span></div>' +
        '<div class="signer-box"><span class="signer-lbl">Area / Zone Assigned</span></div>' +
        '</div>' +
        '<div class="blind-notice">&#128065;&#xFE0F; <strong>BLIND COUNT</strong> &mdash; Do NOT refer to previous stock records or system quantities. Count only what you physically see and record the actual quantity below. Variance analysis is done separately by a supervisor.</div>' +
        '<div class="instructions">' +
        '<strong>&#9432; Counting Instructions:</strong>' +
        '<ul>' +
        '<li>Count each item <strong>physically</strong> — do not estimate or use system records.</li>' +
        '<li>Record <strong>whole units</strong> unless the item is sold by weight/volume.</li>' +
        '<li>If you count an item not on this list, write it in the <em>Notes / Remarks</em> column.</li>' +
        '<li>Count each product twice. Use the 1st Count column first, then the 2nd Count column independently.</li>' +
        '<li>Return completed sheet to your supervisor for data entry. Do not alter counts once submitted.</li>' +
        '</ul>' +
        '</div>' +
        '<table>' +
        '<thead><tr>' +
        '<th class="r">#</th>' +
        '<th>Product Name</th>' +
        '<th>SKU / Item Code</th>' +
        '<th>Barcode</th>' +
        '<th>Category</th>' +
        '<th class="r">1st Count</th>' +
        '<th class="r">2nd Count</th>' +
        '<th>Notes / Remarks</th>' +
        '</tr></thead>' +
        '<tbody>' + rows + '</tbody>' +
        '</table>' +
        '<div class="footer">' +
        '<div class="footer-signer"><div class="footer-space"></div><div class="footer-lbl">Counter Signature</div></div>' +
        '<div class="footer-signer"><div class="footer-space"></div><div class="footer-lbl">Supervisor / Verifier Signature</div></div>' +
        '<div class="footer-signer"><div class="footer-space"></div><div class="footer-lbl">Date &amp; Time Completed</div></div>' +
        '</div>' +
        '<div class="page-info">Session: ' + sessNum + ' &nbsp;|&nbsp; Printed: ' + now + ' &nbsp;|&nbsp; ' + toPrint.length + ' items</div>' +
        '</div>' +
        '</body></html>';

    var w = window.open('', '_blank', 'width=1100,height=800,scrollbars=yes,resizable=yes');
    if (!w) { alert('Pop-up was blocked. Please allow pop-ups for this site and try again.'); return; }
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(function() { w.print(); }, 600);
}

// ── Close modals on outside click ────────────────────────────────────────────
['post-modal','reason-modal'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target === el) el.style.display = 'none'; });
});

// ── Init ─────────────────────────────────────────────────────────────────────
loadItems();

// Auto-refresh every 60s if active (picks up counts from other users)
if (IS_ACTIVE) {
    setInterval(function() {
        if (isOnline && !Object.keys(pendingRows).length) loadItems();
    }, 60000);
}
</script>
