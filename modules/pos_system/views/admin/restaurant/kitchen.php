<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kitchen Display — POS</title>
<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/font-awesome/css/all.min.css'); ?>">
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { background:#0f172a; font-family:'Segoe UI',system-ui,sans-serif; color:#e2e8f0; min-height:100vh; }

.kd-header { background:#1e293b; border-bottom:2px solid #16a34a; padding:12px 20px; display:flex; align-items:center; gap:16px; }
.kd-title { font-size:18px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px; }
.kd-title i { color:#16a34a; }
.kd-time  { font-size:13px; color:#64748b; margin-left:auto; }
.kd-clock { font-size:20px; font-weight:700; color:#e2e8f0; }
.kd-filter { display:flex; align-items:center; gap:8px; }
.kd-filter a { padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; text-decoration:none; color:#94a3b8; background:#1e293b; border:1px solid #334155; transition:.15s; }
.kd-filter a.active { background:#16a34a; color:#fff; border-color:#16a34a; }
.kd-filter a:hover:not(.active) { background:#334155; color:#e2e8f0; }

.kd-board { padding:20px; display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }

.kot-card { border-radius:12px; overflow:hidden; border:2px solid; transition:.2s; }
.kot-card.status-pending  { border-color:#d97706; background:#1c1403; }
.kot-card.status-preparing{ border-color:#16a34a; background:#031a05; }
.kot-card.status-ready    { border-color:#f8fafc; background:#0a1609; animation:pulse-white 1.5s ease-in-out infinite; }
@keyframes pulse-white { 0%,100%{box-shadow:0 0 0 0 rgba(255,255,255,.3)} 50%{box-shadow:0 0 0 8px rgba(255,255,255,0)} }

.kot-head { padding:12px 14px; display:flex; align-items:center; gap:8px; border-bottom:1px solid rgba(255,255,255,.07); }
.kot-number { font-size:16px; font-weight:800; }
.kot-card.status-pending   .kot-number { color:#d97706; }
.kot-card.status-preparing .kot-number { color:#16a34a; }
.kot-card.status-ready     .kot-number { color:#f8fafc; }
.kot-meta { font-size:11px; color:#64748b; display:flex; flex-direction:column; gap:2px; flex:1; }
.kot-badge { padding:3px 9px; border-radius:20px; font-size:10px; font-weight:700; }
.badge-pending   { background:#78350f; color:#fcd34d; }
.badge-preparing { background:#14532d; color:#86efac; }
.badge-ready     { background:#1e3a8a; color:#93c5fd; }

.kot-timer { font-size:11px; font-weight:700; }
.kot-timer.urgent { color:#ef4444; }
.kot-timer.warn   { color:#f59e0b; }
.kot-timer.ok     { color:#34d399; }

.kot-items { padding:10px 14px; }
.kot-item  { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid rgba(255,255,255,.04); }
.kot-item:last-child { border-bottom:none; }
.kot-item-qty  { width:28px; height:28px; border-radius:6px; background:#1e293b; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; color:#e2e8f0; flex-shrink:0; }
.kot-item-name { font-size:13px; font-weight:600; color:#e2e8f0; flex:1; }
.kot-item-note { font-size:10px; color:#64748b; margin-top:2px; }

.kot-actions { padding:10px 14px; display:flex; gap:8px; }
.kot-btn { flex:1; padding:9px; border-radius:8px; border:none; font-size:12px; font-weight:700; cursor:pointer; transition:.15s; display:flex; align-items:center; justify-content:center; gap:6px; }
.kot-btn:disabled { opacity:.4; cursor:default; }
.kot-btn-start { background:#15803d; color:#fff; }
.kot-btn-start:hover:not(:disabled) { background:#166534; }
.kot-btn-ready { background:#111827; color:#fff; }
.kot-btn-ready:hover:not(:disabled) { background:#000; }
.kot-btn-served { background:#374151; color:#fff; }
.kot-btn-served:hover:not(:disabled) { background:#1f2937; }

.kd-empty { grid-column:1/-1; text-align:center; padding:80px; color:#334155; }
.kd-empty i { font-size:48px; display:block; margin-bottom:16px; }
.kd-empty p { font-size:15px; }

.kd-status-bar { position:fixed; bottom:0; left:0; right:0; background:#1e293b; border-top:1px solid #334155; padding:8px 20px; display:flex; gap:24px; font-size:12px; color:#64748b; }
.kd-status-bar span strong { color:#e2e8f0; }
</style>
</head>
<body>

<div class="kd-header">
  <div class="kd-title"><i class="fa fa-utensils"></i> Kitchen Display</div>
  <div class="kd-filter">
    <a href="?branch_id=<?php echo $branch_id; ?>" class="<?php echo !$area_id ? 'active' : ''; ?>">All Areas</a>
    <?php foreach ($areas as $a): ?>
    <a href="?branch_id=<?php echo $branch_id; ?>&area_id=<?php echo $a['id']; ?>" class="<?php echo $area_id == $a['id'] ? 'active' : ''; ?>">
      <i class="fa fa-fire-alt"></i> <?php echo htmlspecialchars($a['name']); ?>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="kd-time">
    <div class="kd-clock" id="kd-clock">—</div>
    <div style="font-size:10px;text-align:right"><?php echo date('D, d M Y'); ?></div>
  </div>
</div>

<div class="kd-board" id="kd-board">
<?php if (empty($kots)): ?>
  <div class="kd-empty">
    <i class="fa fa-check-circle" style="color:#16a34a;opacity:.3"></i>
    <p>All clear — no pending orders right now.</p>
    <p style="font-size:12px;margin-top:8px;color:#475569">Auto-refreshes every <?php echo $refresh; ?> seconds</p>
  </div>
<?php else: foreach ($kots as $kot):
  $age_mins = (int) round((time() - strtotime($kot['date_created'])) / 60);
  $timer_class = $age_mins >= 20 ? 'urgent' : ($age_mins >= 10 ? 'warn' : 'ok');
?>
  <div class="kot-card status-<?php echo $kot['status']; ?>" id="kot-<?php echo $kot['id']; ?>">
    <div class="kot-head">
      <div>
        <div class="kot-number"><?php echo htmlspecialchars($kot['kot_number']); ?></div>
        <div class="kot-meta">
          <?php if ($kot['table_number']): ?><span><i class="fa fa-chair" style="margin-right:3px"></i>Table <?php echo htmlspecialchars($kot['table_number']); ?></span><?php endif; ?>
          <?php if ($kot['waiter_name']): ?><span><i class="fa fa-user" style="margin-right:3px"></i><?php echo htmlspecialchars($kot['waiter_name']); ?></span><?php endif; ?>
          <?php if ($kot['area_name']): ?><span><i class="fa fa-fire-alt" style="margin-right:3px"></i><?php echo htmlspecialchars($kot['area_name']); ?></span><?php endif; ?>
        </div>
      </div>
      <div style="text-align:right">
        <span class="kot-badge badge-<?php echo $kot['status']; ?>"><?php echo strtoupper($kot['status']); ?></span>
        <div class="kot-timer <?php echo $timer_class; ?>" style="margin-top:4px"><i class="fa fa-clock"></i> <?php echo $age_mins; ?> min</div>
      </div>
    </div>

    <div class="kot-items">
      <?php foreach ($kot['items'] as $item): ?>
      <div class="kot-item">
        <div class="kot-item-qty"><?php echo (int) $item['quantity']; ?></div>
        <div>
          <div class="kot-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
          <?php if ($item['notes']): ?><div class="kot-item-note"><?php echo htmlspecialchars($item['notes']); ?></div><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($kot['items'])): ?><div style="font-size:12px;color:#475569;text-align:center;padding:8px">No items</div><?php endif; ?>
    </div>

    <div class="kot-actions">
      <?php if ($kot['status'] === 'pending'): ?>
        <button class="kot-btn kot-btn-start" onclick="kotAction(<?php echo $kot['id']; ?>, 'preparing')"><i class="fa fa-fire"></i> Start Preparing</button>
      <?php elseif ($kot['status'] === 'preparing'): ?>
        <button class="kot-btn kot-btn-ready" onclick="kotAction(<?php echo $kot['id']; ?>, 'ready')"><i class="fa fa-check"></i> Mark Ready</button>
      <?php elseif ($kot['status'] === 'ready'): ?>
        <button class="kot-btn kot-btn-served" onclick="kotAction(<?php echo $kot['id']; ?>, 'served')"><i class="fa fa-concierge-bell"></i> Mark Served</button>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; endif; ?>
</div>

<div class="kd-status-bar">
  <span>Branch: <strong><?php echo $branch_id; ?></strong></span>
  <span>Total Active: <strong id="kd-count"><?php echo count($kots); ?></strong></span>
  <span style="margin-left:auto;color:#475569">Auto-refresh: <?php echo $refresh; ?>s &nbsp;|&nbsp; <a href="javascript:void(0)" onclick="location.reload()" style="color:#64748b">Refresh Now</a></span>
</div>

<script>
var KOT_STATUS_URL = '<?php echo admin_url('pos_system/kot_update_status'); ?>';
var KOT_CSRF_N = '<?php echo $this->security->get_csrf_token_name(); ?>';
var KOT_CSRF_V = '<?php echo $this->security->get_csrf_hash(); ?>';
var KOT_REFRESH = <?php echo (int)$refresh * 1000; ?>;

// Live clock
function tick() {
    var now = new Date();
    var h = now.getHours().toString().padStart(2,'0');
    var m = now.getMinutes().toString().padStart(2,'0');
    var s = now.getSeconds().toString().padStart(2,'0');
    document.getElementById('kd-clock').textContent = h + ':' + m + ':' + s;
}
tick(); setInterval(tick, 1000);

// Auto-refresh the board
setInterval(function() { location.reload(); }, KOT_REFRESH);

// Update KOT status via AJAX
function kotAction(id, status) {
    var labels = {preparing:'Preparing…', ready:'Marking Ready…', served:'Marking Served…'};
    var $card = $('#kot-' + id);
    $card.find('.kot-btn').prop('disabled', true).text(labels[status] || '…');

    var data = {};
    data['id'] = id;
    data['status'] = status;
    data[KOT_CSRF_N] = KOT_CSRF_V;

    $.post(KOT_STATUS_URL, data, function(r) {
        if (r.success) {
            // Update card status visually before next reload
            $card.removeClass('status-pending status-preparing status-ready').addClass('status-' + status);
            $card.find('.kot-badge').removeClass('badge-pending badge-preparing badge-ready').addClass('badge-' + status).text(status.toUpperCase());
            // Rebuild action buttons
            var btns = '';
            if (status === 'preparing') {
                btns = '<button class="kot-btn kot-btn-ready" onclick="kotAction(' + id + ', \'ready\')"><i class="fa fa-check"></i> Mark Ready</button>';
            } else if (status === 'ready') {
                btns = '<button class="kot-btn kot-btn-served" onclick="kotAction(' + id + ', \'served\')"><i class="fa fa-concierge-bell"></i> Mark Served</button>';
            } else if (status === 'served') {
                btns = '<span style="font-size:12px;color:#34d399;text-align:center;width:100%;display:block"><i class="fa fa-check-circle"></i> Served</span>';
                setTimeout(function() { $card.fadeOut(500, function(){ $(this).remove(); }); }, 2000);
            }
            $card.find('.kot-actions').html(btns);
        } else {
            $card.find('.kot-btn').prop('disabled', false).text('Retry');
        }
    }, 'json').fail(function() {
        $card.find('.kot-btn').prop('disabled', false).text('Retry');
    });
}
</script>
</body>
</html>
