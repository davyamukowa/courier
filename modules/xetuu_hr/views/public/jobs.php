<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$all_depts = array_unique(array_filter(array_column($openings, 'department_name')));
sort($all_depts);
?>

<style>
/* ── Hero ── */
.cj-hero{background:linear-gradient(135deg,#14532d 0%,#16a34a 60%,#22c55e 100%);margin:-20px -20px 28px;padding:52px 32px 44px;text-align:center;position:relative;overflow:hidden;}
.cj-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/svg%3E") repeat;}
.cj-eyebrow{display:inline-block;background:rgba(255,255,255,.15);color:#dcfce7;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;}
.cj-hero h1{font-size:clamp(26px,4vw,42px);font-weight:900;color:#fff;line-height:1.15;margin:0 0 10px;position:relative;}
.cj-hero p{font-size:15px;color:rgba(255,255,255,.82);max-width:480px;margin:0 auto 26px;line-height:1.6;position:relative;}
.cj-search-wrap{max-width:480px;margin:0 auto;position:relative;}
.cj-search-wrap input{width:100%;padding:13px 18px 13px 44px;border-radius:10px;border:none;font-size:14px;font-family:inherit;box-shadow:0 4px 20px rgba(0,0,0,.15);outline:none;color:#0f172a;}
.cj-search-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:18px;font-family:'Material Symbols Outlined';}
.cj-hero-stats{display:flex;gap:28px;justify-content:center;margin-top:28px;position:relative;}
.cj-stat-val{font-size:26px;font-weight:900;color:#fff;}
.cj-stat-lbl{font-size:11px;color:rgba(255,255,255,.65);font-weight:500;margin-top:1px;}

/* ── Filters ── */
.cj-filters{display:flex;align-items:center;gap:4px;margin-bottom:22px;overflow-x:auto;padding-bottom:4px;}
.cj-filter-btn{padding:7px 14px;font-size:12.5px;font-weight:600;white-space:nowrap;border:1.5px solid #e2e8f0;border-radius:20px;background:#fff;cursor:pointer;color:#64748b;transition:all .15s;font-family:inherit;}
.cj-filter-btn:hover{border-color:#16a34a;color:#16a34a;}
.cj-filter-btn.active{background:#16a34a;border-color:#16a34a;color:#fff;}

/* ── Results bar ── */
.cj-results-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.cj-results-bar span{font-size:13px;color:#64748b;font-weight:500;}

/* ── Grid ── */
.cj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;}
.cj-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .2s,transform .2s,border-color .2s;text-decoration:none;color:inherit;}
.cj-card:hover{box-shadow:0 6px 24px rgba(22,163,74,.1);transform:translateY(-2px);border-color:#bbf7d0;}
.cj-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
.cj-card-icon{width:40px;height:40px;border-radius:9px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Material Symbols Outlined';font-size:20px;color:#16a34a;}
.cj-open-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:10.5px;font-weight:700;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.cj-open-badge::before{content:'';width:5px;height:5px;background:#22c55e;border-radius:50%;display:inline-block;}
.cj-card-title{font-size:16px;font-weight:800;color:#0f172a;line-height:1.3;}
.cj-card-meta{display:flex;flex-wrap:wrap;gap:6px;}
.cj-chip{display:inline-flex;align-items:center;gap:3px;font-size:11px;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;padding:2px 8px;border-radius:5px;font-weight:500;}
.cj-chip-icon{font-family:'Material Symbols Outlined';font-size:12px;}
.cj-card-footer{display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid #f1f5f9;margin-top:auto;}
.cj-deadline{font-size:11px;color:#94a3b8;display:flex;align-items:center;gap:3px;}
.cj-deadline-icon{font-family:'Material Symbols Outlined';font-size:12px;}
.cj-apply-btn{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1.5px solid #bbf7d0;padding:5px 12px;border-radius:7px;transition:all .15s;}
.cj-card:hover .cj-apply-btn{background:#16a34a;color:#fff;border-color:#16a34a;}
.cj-apply-arrow{font-family:'Material Symbols Outlined';font-size:13px;}

/* ── Empty ── */
.cj-empty{text-align:center;padding:56px 24px;background:#fff;border-radius:12px;border:1px dashed #e2e8f0;}
.cj-empty-icon{font-family:'Material Symbols Outlined';font-size:48px;color:#cbd5e1;display:block;margin-bottom:14px;}
.cj-empty h3{font-size:17px;font-weight:700;color:#334155;margin:0 0 6px;}
.cj-empty p{font-size:13px;color:#94a3b8;margin:0;}
</style>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">

<!-- Hero -->
<div class="cj-hero">
    <div class="cj-eyebrow">We're Hiring</div>
    <h1>Build something great with us</h1>
    <p>Explore our open roles and find the right fit for your next career move.</p>
    <div class="cj-search-wrap">
        <span class="cj-search-icon">search</span>
        <input type="text" id="cj-search" placeholder="Search by job title…" autocomplete="off">
    </div>
    <div class="cj-hero-stats">
        <div>
            <div class="cj-stat-val"><?php echo count($openings); ?></div>
            <div class="cj-stat-lbl">Open Roles</div>
        </div>
        <div>
            <div class="cj-stat-val"><?php echo count($all_depts); ?></div>
            <div class="cj-stat-lbl">Departments</div>
        </div>
        <div>
            <div class="cj-stat-val"><?php echo array_sum(array_column($openings, 'no_of_positions')); ?></div>
            <div class="cj-stat-lbl">Total Positions</div>
        </div>
    </div>
</div>

<!-- Filters -->
<?php if (!empty($all_depts)): ?>
<div class="cj-filters">
    <button class="cj-filter-btn active" data-filter="all">All Departments</button>
    <?php foreach ($all_depts as $dept): ?>
    <button class="cj-filter-btn" data-filter="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></button>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Results bar -->
<div class="cj-results-bar">
    <span id="cj-count"><?php echo count($openings); ?> open position<?php echo count($openings) !== 1 ? 's' : ''; ?></span>
</div>

<!-- Cards -->
<?php if (empty($openings)): ?>
<div class="cj-empty">
    <span class="cj-empty-icon">work_off</span>
    <h3>No open positions right now</h3>
    <p>We don't have any active openings at the moment — check back soon!</p>
</div>
<?php else: ?>
<div class="cj-grid" id="cj-grid">
    <?php foreach ($openings as $o): ?>
    <a href="<?php echo site_url('xetuu_hr/jobs/detail/' . $o->id); ?>"
       class="cj-card"
       data-dept="<?php echo htmlspecialchars($o->department_name ?? ''); ?>"
       data-title="<?php echo strtolower(htmlspecialchars($o->title)); ?>">
        <div class="cj-card-top">
            <div class="cj-card-icon">work</div>
            <span class="cj-open-badge">Open</span>
        </div>
        <div class="cj-card-title"><?php echo htmlspecialchars($o->title); ?></div>
        <div class="cj-card-meta">
            <?php if ($o->department_name): ?>
            <span class="cj-chip"><span class="cj-chip-icon">corporate_fare</span><?php echo htmlspecialchars($o->department_name); ?></span>
            <?php endif; ?>
            <?php if ($o->designation_name): ?>
            <span class="cj-chip"><span class="cj-chip-icon">badge</span><?php echo htmlspecialchars($o->designation_name); ?></span>
            <?php endif; ?>
            <span class="cj-chip"><span class="cj-chip-icon">group</span><?php echo (int)$o->no_of_positions; ?> position<?php echo $o->no_of_positions != 1 ? 's' : ''; ?></span>
        </div>
        <div class="cj-card-footer">
            <span class="cj-deadline">
                <span class="cj-deadline-icon">event</span>
                <?php echo $o->close_date ? 'By ' . date('d M Y', strtotime($o->close_date)) : 'Rolling'; ?>
            </span>
            <span class="cj-apply-btn">View & Apply <span class="cj-apply-arrow">arrow_forward</span></span>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<div id="cj-no-results" style="display:none;">
    <div class="cj-empty">
        <span class="cj-empty-icon">search_off</span>
        <h3>No matching positions</h3>
        <p>Try a different keyword or department filter.</p>
    </div>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.cj-filter-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
        document.querySelectorAll('.cj-filter-btn').forEach(function(b){b.classList.remove('active');});
        this.classList.add('active');
        filterCards();
    });
});
var srch = document.getElementById('cj-search');
if(srch) srch.addEventListener('input', filterCards);

function filterCards(){
    var q = (srch ? srch.value : '').toLowerCase().trim();
    var f = (document.querySelector('.cj-filter-btn.active')||{dataset:{filter:'all'}}).dataset.filter;
    var cards = document.querySelectorAll('#cj-grid .cj-card');
    var v=0;
    cards.forEach(function(c){
        var show = (f==='all'||c.dataset.dept===f) && (!q||c.dataset.title.includes(q)||c.dataset.dept.toLowerCase().includes(q));
        c.style.display = show ? '' : 'none';
        if(show) v++;
    });
    var el=document.getElementById('cj-count');
    if(el) el.textContent=v+' open position'+(v!==1?'s':'');
    var nr=document.getElementById('cj-no-results');
    if(nr) nr.style.display = (cards.length>0&&v===0)?'block':'none';
}
</script>
