<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$xhr_active = 'employees';

/* ── Flatten tree for search & stats ── */
function _oc_flat($nodes, &$out = []) {
    foreach ($nodes as $n) { $out[] = $n; if (!empty($n->children)) _oc_flat($n->children, $out); }
    return $out;
}

/* ── Serialize tree to JSON ── */
function _oc_json($nodes) {
    $r = [];
    foreach ($nodes as $n) {
        $r[] = [
            'id'       => (int)$n->id,
            'name'     => $n->first_name . ' ' . $n->last_name,
            'desig'    => $n->designation_name ?? '',
            'dept'     => $n->department_name  ?? '',
            'photo'    => !empty($n->photo) ? base_url($n->photo) : '',
            'url'      => admin_url('xetuu_hr/employees/' . $n->id),
            'empno'    => $n->employee_number ?? '',
            'children' => _oc_json($n->children ?? []),
        ];
    }
    return $r;
}

$all_flat   = _oc_flat($tree);
$total_emps = count($all_flat);

/* ── Department → colour mapping ── */
$palette  = ['#16a34a','#2563eb','#9333ea','#d97706','#dc2626','#0891b2','#c026d3','#65a30d','#ea580c','#0284c7'];
$dept_map = []; $ci = 0;
foreach ($all_flat as $e) {
    $d = $e->department_name ?? '';
    if ($d && !isset($dept_map[$d])) { $dept_map[$d] = $palette[$ci++ % count($palette)]; }
}
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Reset / page shell ──────────────────────────────────────────────── */
.oc-page { display:flex; flex-direction:column; height:calc(100vh - 116px); overflow:hidden; }

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.oc-bar {
    display:flex; align-items:center; gap:12px; padding:12px 20px;
    background:#fff; border-bottom:1px solid #e5e7eb; flex-shrink:0;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.oc-bar-title { flex:1; }
.oc-bar-title h1 { font-size:17px; font-weight:700; color:#111827; margin:0; }
.oc-bar-title p  { font-size:11px; color:#9ca3af; margin:1px 0 0; }

.oc-search-wrap { position:relative; }
.oc-search { border:1.5px solid #e5e7eb; border-radius:8px; padding:7px 10px 7px 34px;
    font-size:13px; width:210px; outline:none; background:#f9fafb; }
.oc-search:focus { border-color:#16a34a; background:#fff; }
.oc-search-ico { position:absolute; left:9px; top:50%; transform:translateY(-50%);
    font-size:17px; color:#9ca3af; pointer-events:none; }
.oc-drop { position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff;
    border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1);
    z-index:300; max-height:240px; overflow-y:auto; display:none; }
.oc-drop.open { display:block; }
.oc-drop-item { display:flex; align-items:center; gap:10px; padding:8px 12px;
    cursor:pointer; font-size:12px; color:#374151; }
.oc-drop-item:hover { background:#f0fdf4; }
.oc-drop-av { width:28px; height:28px; border-radius:50%; background:#e5e7eb;
    display:flex; align-items:center; justify-content:center; font-size:10px;
    font-weight:700; color:#374151; overflow:hidden; flex-shrink:0; }
.oc-drop-av img { width:100%; height:100%; object-fit:cover; }

/* toolbar buttons */
.oc-tbtn { display:flex; align-items:center; gap:5px; padding:7px 14px; border-radius:8px;
    border:1.5px solid #e5e7eb; background:#fff; font-size:12px; font-weight:600;
    color:#374151; cursor:pointer; white-space:nowrap; }
.oc-tbtn:hover { border-color:#16a34a; color:#16a34a; }
.oc-tbtn.primary { background:#16a34a; color:#fff; border-color:#16a34a; }
.oc-tbtn.primary:hover { background:#15803d; border-color:#15803d; }
.oc-tbtn .material-symbols-outlined { font-size:16px; }
.oc-sep { width:1px; height:24px; background:#e5e7eb; }

/* ── Viewport / canvas ───────────────────────────────────────────────── */
#oc-vp {
    flex:1; overflow:hidden; position:relative; cursor:grab;
    background:#f1f5f9;
    background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
    background-size:24px 24px;
}
#oc-vp.grabbing { cursor:grabbing; }
#oc-world { position:absolute; top:0; left:0; transform-origin:0 0; will-change:transform; }
#oc-svg   { position:absolute; top:0; left:0; overflow:visible; pointer-events:none; }
#oc-nodes { position:absolute; top:0; left:0; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.oc-card {
    position:absolute; width:186px;
    background:#fff; border-radius:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.08), 0 0 0 1.5px rgba(0,0,0,.06);
    overflow:visible; cursor:pointer; transition:box-shadow .15s;
    user-select:none;
}
.oc-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.14), 0 0 0 1.5px #16a34a44; z-index:10; }
.oc-card.dragging { box-shadow:0 16px 40px rgba(0,0,0,.22), 0 0 0 2px #16a34a; z-index:200; cursor:grabbing; }
.oc-card.highlighted { box-shadow:0 0 0 3px #f59e0b, 0 8px 24px rgba(245,158,11,.3) !important; }
.oc-card.collapsed-root > .oc-card-body > .oc-card-foot { opacity:.5; }

/* colour ribbon top */
.oc-ribbon { height:4px; border-radius:16px 16px 0 0; }

/* card body */
.oc-card-body { padding:14px 14px 12px; text-align:center; }
.oc-av-wrap { width:56px; height:56px; margin:0 auto 8px; border-radius:50%;
    padding:2px; position:relative; }
.oc-av-wrap img, .oc-av-wrap .oc-ini {
    width:100%; height:100%; border-radius:50%; object-fit:cover;
    background:#f3f4f6; display:flex; align-items:center; justify-content:center;
    font-size:17px; font-weight:700; color:#374151; }
.oc-card-name  { font-size:12.5px; font-weight:700; color:#111827; line-height:1.3; margin-bottom:3px; }
.oc-card-desig { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    margin-bottom:6px; }
.oc-dept-chip  { display:inline-block; font-size:9.5px; font-weight:600;
    padding:2px 9px; border-radius:20px; }

/* card footer */
.oc-card-foot { display:flex; align-items:center; justify-content:space-between;
    padding:6px 12px 10px; font-size:10px; color:#9ca3af; }
.oc-reports    { display:flex; align-items:center; gap:3px; }

/* drag handle */
.oc-drag { position:absolute; top:8px; right:8px; width:20px; height:20px;
    border-radius:5px; background:#f3f4f6; display:flex; align-items:center;
    justify-content:center; cursor:grab; opacity:0; transition:opacity .15s;
    font-size:14px; color:#9ca3af; z-index:5; }
.oc-card:hover .oc-drag { opacity:1; }
.oc-drag:hover { background:#e5e7eb; color:#374151; }

/* collapse toggle */
.oc-toggle { position:absolute; bottom:-12px; left:50%; transform:translateX(-50%);
    width:22px; height:22px; border-radius:50%; background:#16a34a; color:#fff;
    border:2px solid #fff; font-size:11px; line-height:18px; text-align:center;
    cursor:pointer; z-index:20; box-shadow:0 1px 4px rgba(0,0,0,.2); }
.oc-toggle:hover { background:#15803d; }
.oc-toggle.coll { transform:translateX(-50%) rotate(-90deg); }

/* ── SVG connector style ──────────────────────────────────────────────── */
.oc-edge { fill:none; stroke:#94a3b8; stroke-width:1.5; transition:stroke .15s; }
.oc-edge:hover { stroke:#16a34a; stroke-width:2; }

/* ── FABs ────────────────────────────────────────────────────────────── */
.oc-fabs { position:absolute; bottom:20px; right:20px; display:flex; flex-direction:column;
    gap:8px; z-index:100; }
.oc-fab { width:38px; height:38px; border-radius:50%; background:#fff;
    border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,.1);
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    font-size:18px; color:#374151; transition:all .15s; }
.oc-fab:hover { background:#f0fdf4; color:#16a34a; border-color:#16a34a; }
.oc-fab.green { background:#16a34a; color:#fff; border-color:#16a34a; }
.oc-fab.green:hover { background:#15803d; }

/* ── Scale indicator ─────────────────────────────────────────────────── */
.oc-scl { position:absolute; bottom:24px; left:50%; transform:translateX(-50%);
    background:rgba(255,255,255,.92); backdrop-filter:blur(4px); border:1px solid #e5e7eb;
    border-radius:20px; padding:4px 12px; font-size:11px; color:#6b7280; z-index:100; }

/* ── Stats bar bottom-left ───────────────────────────────────────────── */
.oc-stats { position:absolute; bottom:20px; left:20px; background:#fff;
    border:1px solid #e5e7eb; border-radius:10px; padding:8px 14px;
    font-size:12px; color:#374151; box-shadow:0 2px 8px rgba(0,0,0,.06); z-index:100;
    display:flex; gap:14px; align-items:center; }
.oc-stats b { color:#16a34a; }

/* ── Legend ─────────────────────────────────────────────────────────── */
.oc-legend { position:absolute; top:12px; right:12px; background:#fff;
    border:1px solid #e5e7eb; border-radius:10px; padding:8px 12px;
    font-size:10.5px; color:#374151; box-shadow:0 2px 8px rgba(0,0,0,.06); z-index:100;
    max-height:180px; overflow-y:auto; }
.oc-legend-item { display:flex; align-items:center; gap:6px; margin-bottom:4px; }
.oc-legend-dot  { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

@media print {
    .oc-bar .oc-tbtn, .oc-fabs, .oc-stats, .oc-legend, .oc-scl { display:none !important; }
    #oc-vp { height:auto !important; overflow:visible !important; }
    #oc-world { transform:none !important; }
}
</style>

<div class="oc-page">

<!-- ── Toolbar ─────────────────────────────────────────────────────── -->
<div class="oc-bar">
    <div class="oc-bar-title">
        <h1>Organisational Structure</h1>
        <p><?php echo (int)$total_emps; ?> employees &middot; <?php echo count($dept_map); ?> departments</p>
    </div>

    <div class="oc-search-wrap" id="oc-sw">
        <span class="material-symbols-outlined oc-search-ico">search</span>
        <input type="text" class="oc-search" id="oc-search" placeholder="Find employee…" autocomplete="off">
        <div class="oc-drop" id="oc-drop"></div>
    </div>

    <div class="oc-sep"></div>

    <button class="oc-tbtn" onclick="ocExpandAll()">
        <span class="material-symbols-outlined">unfold_more</span> Expand All
    </button>
    <button class="oc-tbtn" onclick="ocCollapseAll()">
        <span class="material-symbols-outlined">unfold_less</span> Collapse
    </button>
    <button class="oc-tbtn" onclick="ocReset()">
        <span class="material-symbols-outlined">center_focus_strong</span> Reset
    </button>
    <div class="oc-sep"></div>
    <button class="oc-tbtn primary" onclick="window.print()">
        <span class="material-symbols-outlined">download</span> Export
    </button>
</div>

<!-- ── Viewport ─────────────────────────────────────────────────────── -->
<div id="oc-vp">
    <div id="oc-world">
        <svg id="oc-svg"></svg>
        <div id="oc-nodes"></div>
    </div>

    <!-- FABs -->
    <div class="oc-fabs">
        <div class="oc-fab" onclick="ocZoom(.15)" title="Zoom In"><span class="material-symbols-outlined">add</span></div>
        <div class="oc-fab" onclick="ocZoom(-.15)" title="Zoom Out"><span class="material-symbols-outlined">remove</span></div>
        <div class="oc-fab" onclick="ocFitAll()" title="Fit All"><span class="material-symbols-outlined">fit_screen</span></div>
        <div class="oc-fab green" onclick="ocExpandAll()" title="Expand All"><span class="material-symbols-outlined">account_tree</span></div>
    </div>

    <!-- Scale indicator -->
    <div class="oc-scl" id="oc-scl">100%</div>

    <!-- Stats -->
    <div class="oc-stats">
        <span><b><?php echo (int)$total_emps; ?></b> Employees</span>
        <?php if (count($dept_map)): ?>
        <span><b><?php echo count($dept_map); ?></b> Departments</span>
        <?php endif; ?>
    </div>

    <!-- Dept legend -->
    <?php if ($dept_map): ?>
    <div class="oc-legend">
        <?php foreach ($dept_map as $dname => $dcolor): ?>
        <div class="oc-legend-item">
            <div class="oc-legend-dot" style="background:<?php echo htmlspecialchars($dcolor); ?>;"></div>
            <span><?php echo htmlspecialchars($dname); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div><!-- /#oc-vp -->

</div><!-- /.oc-page -->
</div><!-- /.xhr-page -->
</div><!-- #wrapper -->

<script>
/* ── Data ────────────────────────────────────────────────────────────── */
var OC_TREE   = <?php echo json_encode(_oc_json($tree), JSON_UNESCAPED_UNICODE); ?>;
var OC_COLORS = <?php echo json_encode($dept_map, JSON_UNESCAPED_UNICODE); ?>;

/* ── Constants ───────────────────────────────────────────────────────── */
var NW = 186, NH = 152, HG = 56, VG = 88;

/* ── State ───────────────────────────────────────────────────────────── */
var _nodes   = {};   // id → { data, x, y, collapsed }
var _roots   = [];   // root node ids
var _scale   = 1, _tx = 0, _ty = 0;
var _panDrag = false, _px, _py;

/* ── DOM refs ────────────────────────────────────────────────────────── */
var VP    = document.getElementById('oc-vp');
var WORLD = document.getElementById('oc-world');
var SVG   = document.getElementById('oc-svg');
var NDIV  = document.getElementById('oc-nodes');

/* ══════════════════════════════════════════════════════════════════════
   LAYOUT — Reingold-Tilford simplified
═══════════════════════════════════════════════════════════════════════ */
function subtreeWidth(node) {
    if (!node.children || !node.children.length) return NW;
    if (_nodes[node.id] && _nodes[node.id].collapsed) return NW;
    var sum = node.children.reduce(function(acc, c) { return acc + subtreeWidth(c) + HG; }, -HG);
    return Math.max(NW, sum);
}

function layoutNode(node, cx, y) {
    var st = _nodes[node.id] || {};
    // Only overwrite position if not manually dragged
    if (!st.pinned) { st.x = cx - NW / 2; st.y = y; }
    st.data = node;
    _nodes[node.id] = st;

    if (!node.children || !node.children.length || st.collapsed) return;
    var total = node.children.reduce(function(a, c) { return a + subtreeWidth(c) + HG; }, -HG);
    var startX = cx - total / 2;
    node.children.forEach(function(child) {
        var cw = subtreeWidth(child);
        layoutNode(child, startX + cw / 2, y + NH + VG);
        startX += cw + HG;
    });
}

function runLayout() {
    var cx = 0;
    OC_TREE.forEach(function(root) {
        var sw = subtreeWidth(root);
        layoutNode(root, cx + sw / 2, 0);
        cx += sw + HG * 2;
    });
}

/* ══════════════════════════════════════════════════════════════════════
   COLOUR helpers
═══════════════════════════════════════════════════════════════════════ */
var _DEF_COLOR = '#16a34a';
function deptColor(dept) { return OC_COLORS[dept] || _DEF_COLOR; }

function hex2rgba(hex, a) {
    var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    return 'rgba('+r+','+g+','+b+','+a+')';
}

/* ══════════════════════════════════════════════════════════════════════
   RENDER
═══════════════════════════════════════════════════════════════════════ */
function initials(name) {
    return name.split(' ').map(function(w){ return w[0]||''; }).join('').slice(0,2).toUpperCase();
}

function makeCard(node) {
    var st    = _nodes[node.id];
    var col   = deptColor(node.dept);
    var ini   = initials(node.name);
    var hasC  = node.children && node.children.length > 0;
    var nRep  = hasC ? node.children.length : 0;

    var card = document.createElement('div');
    card.className = 'oc-card';
    card.id = 'oc-card-' + node.id;
    card.style.left = st.x + 'px';
    card.style.top  = st.y + 'px';

    var avatarInner = node.photo
        ? '<img src="' + node.photo + '" alt="" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">'
          + '<div class="oc-ini" style="display:none;">' + ini + '</div>'
        : '<div class="oc-ini">' + ini + '</div>';

    card.innerHTML =
        '<div class="oc-ribbon" style="background:' + col + ';"></div>' +
        '<div class="oc-drag" data-id="' + node.id + '" title="Drag to reposition">' +
            '<span class="material-symbols-outlined" style="font-size:14px;">drag_indicator</span></div>' +
        '<div class="oc-card-body">' +
            '<div class="oc-av-wrap" style="background:linear-gradient(135deg,'+hex2rgba(col,.15)+','+hex2rgba(col,.05)+')">' +
                avatarInner +
            '</div>' +
            '<div class="oc-card-name">' + escH(node.name) + '</div>' +
            (node.desig ? '<div class="oc-card-desig" style="color:' + col + ';">' + escH(node.desig) + '</div>' : '') +
            (node.dept  ? '<div class="oc-dept-chip" style="background:'+hex2rgba(col,.12)+';color:'+col+';">' + escH(node.dept) + '</div>' : '') +
        '</div>' +
        '<div class="oc-card-foot">' +
            (node.empno ? '<span style="color:#d1d5db;font-size:9px;">#' + escH(node.empno) + '</span>' : '<span></span>') +
            (nRep > 0 ? '<span class="oc-reports"><span class="material-symbols-outlined" style="font-size:12px;">groups</span>' + nRep + '</span>' : '') +
        '</div>' +
        (hasC ? '<div class="oc-toggle" data-id="' + node.id + '" title="Collapse / Expand">' +
                (st.collapsed ? '&#9654;' : '&#9660;') + '</div>' : '');

    // Navigate on click (not on drag or toggle)
    card.addEventListener('click', function(e) {
        if (e.target.closest('.oc-toggle') || e.target.closest('.oc-drag')) return;
        window.location.href = node.url;
    });

    // Drag handle
    var handle = card.querySelector('.oc-drag');
    if (handle) initCardDrag(handle, node.id);

    // Toggle collapse
    var toggle = card.querySelector('.oc-toggle');
    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = parseInt(this.getAttribute('data-id'));
            toggleCollapse(id);
        });
    }

    return card;
}

function renderAll() {
    NDIV.innerHTML = '';
    SVG.innerHTML  = '';

    function renderNode(node) {
        var st = _nodes[node.id];
        if (!st) return;
        NDIV.appendChild(makeCard(node));
        if (!node.children || !node.children.length || st.collapsed) return;
        node.children.forEach(renderNode);
    }

    OC_TREE.forEach(renderNode);
    drawEdges();
    updateWorldSize();
}

/* ══════════════════════════════════════════════════════════════════════
   SVG BEZIER EDGES
═══════════════════════════════════════════════════════════════════════ */
function drawEdges() {
    SVG.innerHTML = '';
    function drawNode(node) {
        var st = _nodes[node.id];
        if (!st || !node.children || !node.children.length || st.collapsed) return;
        node.children.forEach(function(child) {
            var cst = _nodes[child.id];
            if (!cst) return;

            var x1 = st.x  + NW / 2;
            var y1 = st.y  + NH;
            var x2 = cst.x + NW / 2;
            var y2 = cst.y;
            var cp = (y2 - y1) * 0.5;

            var col = deptColor(child.dept || node.dept);
            var path = document.createElementNS('http://www.w3.org/2000/svg','path');
            path.setAttribute('class','oc-edge');
            path.setAttribute('stroke', col + '88');
            path.setAttribute('d',
                'M ' + x1 + ' ' + y1 +
                ' C ' + x1 + ' ' + (y1+cp) +
                '   ' + x2 + ' ' + (y2-cp) +
                '   ' + x2 + ' ' + y2
            );
            SVG.appendChild(path);
            drawNode(child);
        });
    }
    OC_TREE.forEach(drawNode);
}

function redrawEdgesOnly() {
    drawEdges();
}

/* ══════════════════════════════════════════════════════════════════════
   WORLD SIZE
═══════════════════════════════════════════════════════════════════════ */
function updateWorldSize() {
    var maxX = 0, maxY = 0;
    Object.values(_nodes).forEach(function(st) {
        maxX = Math.max(maxX, st.x + NW);
        maxY = Math.max(maxY, st.y + NH);
    });
    WORLD.style.width  = (maxX + 120) + 'px';
    WORLD.style.height = (maxY + 120) + 'px';
    SVG.setAttribute('width',  maxX + 120);
    SVG.setAttribute('height', maxY + 120);
}

/* ══════════════════════════════════════════════════════════════════════
   CARD DRAG (each card independently draggable)
═══════════════════════════════════════════════════════════════════════ */
function initCardDrag(handle, nodeId) {
    var dragging = false, startMX, startMY, startCX, startCY;

    handle.addEventListener('mousedown', function(e) {
        e.stopPropagation();
        dragging = true;
        startMX = e.clientX; startMY = e.clientY;
        var st = _nodes[nodeId];
        startCX = st.x; startCY = st.y;
        var card = document.getElementById('oc-card-' + nodeId);
        if (card) card.classList.add('dragging');
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    function onMove(e) {
        if (!dragging) return;
        var dx = (e.clientX - startMX) / _scale;
        var dy = (e.clientY - startMY) / _scale;
        var st = _nodes[nodeId];
        st.x = startCX + dx;
        st.y = startCY + dy;
        st.pinned = true;
        var card = document.getElementById('oc-card-' + nodeId);
        if (card) { card.style.left = st.x + 'px'; card.style.top = st.y + 'px'; }
        redrawEdgesOnly();
    }

    function onUp() {
        if (!dragging) return;
        dragging = false;
        var card = document.getElementById('oc-card-' + nodeId);
        if (card) card.classList.remove('dragging');
        updateWorldSize();
        savePins();
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
    }
}

/* ══════════════════════════════════════════════════════════════════════
   PAN / ZOOM (canvas-level)
═══════════════════════════════════════════════════════════════════════ */
function applyTransform() {
    WORLD.style.transform = 'translate('+_tx+'px,'+_ty+'px) scale('+_scale+')';
    document.getElementById('oc-scl').textContent = Math.round(_scale*100)+'%';
}

function ocZoom(delta, cx, cy) {
    var prev = _scale;
    _scale = Math.min(2, Math.max(.18, _scale + delta));
    if (cx == null) { cx = VP.clientWidth / 2; cy = VP.clientHeight / 2; }
    _tx = cx - (_tx ? (cx - _tx) * (_scale / prev) : 0);
    _ty = cy - (_ty ? (cy - _ty) * (_scale / prev) : 0);
    // simpler recalc
    var ratio = _scale / prev;
    _tx = cx - ratio * (cx - _tx);
    _ty = cy - ratio * (cy - _ty);
    applyTransform();
}

function ocReset() { _scale=1; _tx=60; _ty=60; applyTransform(); }
function ocFitAll() {
    var maxX=0, maxY=0;
    Object.values(_nodes).forEach(function(s){ maxX=Math.max(maxX,s.x+NW); maxY=Math.max(maxY,s.y+NH); });
    if (!maxX) return;
    var sx = (VP.clientWidth-80)  / maxX;
    var sy = (VP.clientHeight-80) / maxY;
    _scale = Math.min(1.2, Math.min(sx, sy));
    _tx = (VP.clientWidth  - maxX * _scale) / 2;
    _ty = (VP.clientHeight - maxY * _scale) / 2;
    applyTransform();
}

/* pan */
VP.addEventListener('mousedown', function(e) {
    if (e.target.closest('.oc-card') || e.target.closest('.oc-fabs') ||
        e.target.closest('.oc-stats') || e.target.closest('.oc-legend') ||
        e.target.closest('.oc-scl')) return;
    _panDrag=true; _px=e.clientX-_tx; _py=e.clientY-_ty;
    VP.classList.add('grabbing');
});
document.addEventListener('mousemove', function(e) {
    if (!_panDrag) return;
    _tx=e.clientX-_px; _ty=e.clientY-_py; applyTransform();
});
document.addEventListener('mouseup', function() { _panDrag=false; VP.classList.remove('grabbing'); });

/* scroll zoom */
VP.addEventListener('wheel', function(e) {
    e.preventDefault();
    ocZoom(e.deltaY < 0 ? 0.1 : -0.1, e.clientX - VP.getBoundingClientRect().left,
           e.clientY - VP.getBoundingClientRect().top);
}, { passive:false });

/* touch */
var _lastDist=null, _touchDrag=false, _tpx, _tpy;
VP.addEventListener('touchstart', function(e) {
    if (e.touches.length===2) {
        _lastDist=Math.hypot(e.touches[0].clientX-e.touches[1].clientX, e.touches[0].clientY-e.touches[1].clientY);
    } else if (e.touches.length===1 && !e.target.closest('.oc-card')) {
        _touchDrag=true; _tpx=e.touches[0].clientX-_tx; _tpy=e.touches[0].clientY-_ty;
    }
}, {passive:true});
VP.addEventListener('touchmove', function(e) {
    if (e.touches.length===2 && _lastDist) {
        var d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX, e.touches[0].clientY-e.touches[1].clientY);
        ocZoom((d-_lastDist)*0.005); _lastDist=d;
    } else if (_touchDrag && e.touches.length===1) {
        _tx=e.touches[0].clientX-_tpx; _ty=e.touches[0].clientY-_tpy; applyTransform();
    }
}, {passive:true});
VP.addEventListener('touchend', function() { _touchDrag=false; _lastDist=null; });

/* ══════════════════════════════════════════════════════════════════════
   COLLAPSE / EXPAND
═══════════════════════════════════════════════════════════════════════ */
function toggleCollapse(id) {
    var st = _nodes[id];
    if (!st) return;
    st.collapsed = !st.collapsed;
    // reset pinned positions of descendants so they re-layout
    if (!st.collapsed) unpinDescendants(st.data);
    runLayout();
    renderAll();
    applyTransform();
}

function unpinDescendants(node) {
    if (!node || !node.children) return;
    node.children.forEach(function(c) {
        if (_nodes[c.id]) { _nodes[c.id].pinned = false; }
        unpinDescendants(c);
    });
}

function ocCollapseAll() {
    OC_TREE.forEach(function(root) {
        if (root.children && root.children.length) {
            if (_nodes[root.id]) _nodes[root.id].collapsed = true;
        }
    });
    runLayout(); renderAll(); applyTransform();
}

function ocExpandAll() {
    Object.values(_nodes).forEach(function(st) { st.collapsed = false; st.pinned = false; });
    runLayout(); renderAll(); applyTransform();
}

/* ══════════════════════════════════════════════════════════════════════
   SEARCH
═══════════════════════════════════════════════════════════════════════ */
var OC_FLAT = [];
function buildFlat(nodes) {
    nodes.forEach(function(n) { OC_FLAT.push(n); if (n.children) buildFlat(n.children); });
}
buildFlat(OC_TREE);

var srch = document.getElementById('oc-search');
var drop  = document.getElementById('oc-drop');

srch.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    drop.innerHTML='';
    if (!q) { drop.classList.remove('open'); return; }
    var hits = OC_FLAT.filter(function(e) {
        return e.name.toLowerCase().includes(q) || e.desig.toLowerCase().includes(q) || e.dept.toLowerCase().includes(q);
    }).slice(0,10);
    if (!hits.length) { drop.classList.remove('open'); return; }
    hits.forEach(function(emp) {
        var ini = initials(emp.name);
        var d = document.createElement('div'); d.className='oc-drop-item';
        d.innerHTML = (emp.photo
            ? '<div class="oc-drop-av"><img src="'+emp.photo+'" alt=""></div>'
            : '<div class="oc-drop-av">'+ini+'</div>') +
            '<div><div style="font-weight:600;">'+escH(emp.name)+'</div>' +
            '<div style="font-size:10px;color:#9ca3af;">'+escH(emp.desig||emp.dept||'')+'</div></div>';
        d.addEventListener('click', function() {
            srch.value = emp.name;
            drop.classList.remove('open');
            focusNode(emp.id);
        });
        drop.appendChild(d);
    });
    drop.classList.add('open');
});
document.addEventListener('click', function(e) {
    if (!e.target.closest('#oc-sw')) drop.classList.remove('open');
});

function focusNode(id) {
    // Ensure not collapsed
    expandAncestors(id, OC_TREE);
    runLayout(); renderAll(); applyTransform();
    setTimeout(function() {
        var card = document.getElementById('oc-card-' + id);
        var st   = _nodes[id];
        if (!card || !st) return;
        // Remove previous highlights
        document.querySelectorAll('.oc-card.highlighted').forEach(function(c){ c.classList.remove('highlighted'); });
        card.classList.add('highlighted');
        // Center on node
        var nx = st.x + NW/2, ny = st.y + NH/2;
        _tx = VP.clientWidth/2  - nx * _scale;
        _ty = VP.clientHeight/2 - ny * _scale;
        applyTransform();
        setTimeout(function(){ if(card) card.classList.remove('highlighted'); }, 3000);
    }, 50);
}

function expandAncestors(id, nodes) {
    for (var i=0; i<nodes.length; i++) {
        var n = nodes[i];
        if (n.id === id) return true;
        if (n.children && expandAncestors(id, n.children)) {
            if (_nodes[n.id]) _nodes[n.id].collapsed = false;
            return true;
        }
    }
    return false;
}

/* ══════════════════════════════════════════════════════════════════════
   PERSIST DRAGGED POSITIONS (localStorage)
═══════════════════════════════════════════════════════════════════════ */
var LS_KEY = 'oc_pins_<?php echo md5(site_url()); ?>';
function savePins() {
    try {
        var pins = {};
        Object.keys(_nodes).forEach(function(id) {
            var s = _nodes[id];
            if (s.pinned) pins[id] = { x: Math.round(s.x), y: Math.round(s.y) };
        });
        localStorage.setItem(LS_KEY, JSON.stringify(pins));
    } catch(e) {}
}
function loadPins() {
    try {
        var pins = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
        Object.keys(pins).forEach(function(id) {
            if (_nodes[id]) { _nodes[id].x = pins[id].x; _nodes[id].y = pins[id].y; _nodes[id].pinned = true; }
        });
    } catch(e) {}
}

/* ── Context menu: right-click card to reset position ── */
document.addEventListener('contextmenu', function(e) {
    var card = e.target.closest('.oc-card');
    if (!card) return;
    var id = parseInt(card.id.replace('oc-card-',''));
    e.preventDefault();
    if (_nodes[id]) { _nodes[id].pinned = false; }
    runLayout();
    renderAll();
    applyTransform();
    savePins();
});

/* ══════════════════════════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════════════════════════ */
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══════════════════════════════════════════════════════════════════════
   INIT
═══════════════════════════════════════════════════════════════════════ */
(function init() {
    if (!OC_TREE || !OC_TREE.length) {
        NDIV.innerHTML = '<div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;color:#9ca3af;">' +
            '<span class="material-symbols-outlined" style="font-size:48px;display:block;margin-bottom:12px;">account_tree</span>' +
            '<b style="color:#374151;font-size:15px;">No employees yet</b>' +
            '<p style="margin:6px 0 0;font-size:12px;">Add employees and set "Reports To" to build the chart.</p>' +
            '<a href="<?php echo admin_url('xetuu_hr/employees/add'); ?>" style="display:inline-block;margin-top:14px;padding:8px 18px;background:#16a34a;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">Add Employee</a></div>';
        return;
    }

    // Prime _nodes map with empty state
    OC_FLAT.forEach(function(n) { _nodes[n.id] = _nodes[n.id] || {}; });

    runLayout();
    loadPins();
    renderAll();

    // Initial view: fit the chart
    setTimeout(ocFitAll, 60);
})();
</script>

<?php init_tail(); ?>
