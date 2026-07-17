<?php defined('BASEPATH') or exit('No direct script access allowed');
$xhr_active = 'dashboard';
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<style>
/* ── Executive Dashboard ─────────────────────────────────── */
.xd-wrap {
    display: flex;
    min-height: calc(100vh - 60px);
    background: #f4f6f8;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.xd-main  { flex: 1; padding: 24px 20px 24px 24px; overflow: auto; min-width: 0; }
.xd-aside { width: 300px; flex-shrink: 0; background: #fff; border-left: 1px solid #e5e7eb;
             padding: 20px 16px; overflow: auto; }

/* Header */
.xd-header { display: flex; align-items: flex-start; justify-content: space-between;
              margin-bottom: 22px; flex-wrap: wrap; gap: 12px; }
.xd-header__title { font-size: 22px; font-weight: 800; color: #111827; margin: 0 0 3px; }
.xd-header__sub   { font-size: 12px; color: #6b7280; }
.xd-header__actions { display: flex; gap: 8px; align-items: center; }
.xd-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700;
    cursor: pointer; border: none; text-decoration: none;
}
.xd-btn--outline {
    background: #fff; border: 1.5px solid #d1d5db; color: #374151;
    transition: border-color .15s, background .15s;
}
.xd-btn--outline:hover { border-color: #9ca3af; background: #f9fafb; color: #111827; }
.xd-btn--green {
    background: #006b2c; color: #fff;
    box-shadow: 0 2px 8px rgba(0,107,44,.3);
    transition: background .15s, box-shadow .15s;
}
.xd-btn--green:hover { background: #005523; color: #fff; box-shadow: 0 4px 14px rgba(0,107,44,.4); }

/* KPI Cards Row */
.xd-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 18px;
}
.xd-kpi {
    background: #fff; border-radius: 12px;
    padding: 14px 16px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 2px 10px rgba(0,0,0,.04);
    border-top: 3px solid var(--kc, #006b2c);
    position: relative;
}
.xd-kpi__icon {
    position: absolute; top: 12px; right: 12px;
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--kc-bg, rgba(0,107,44,.08));
    display: flex; align-items: center; justify-content: center;
}
.xd-kpi__icon .material-symbols-outlined { font-size: 17px; color: var(--kc, #006b2c); }
.xd-kpi__label { font-size: 9.5px; font-weight: 700; text-transform: uppercase;
                  letter-spacing: .06em; color: #9ca3af; margin-bottom: 6px; }
.xd-kpi__value { font-size: 28px; font-weight: 900; color: #111827; line-height: 1; margin-bottom: 5px; }
.xd-kpi__change { font-size: 10.5px; color: #6b7280; display: flex; align-items: center; gap: 3px; }
.xd-kpi__change--up   { color: #16a34a; }
.xd-kpi__change--down { color: #dc2626; }

/* Secondary metrics strip */
.xd-metrics-strip {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 18px;
}
.xd-metric {
    background: #fff; border-radius: 12px; padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    display: flex; align-items: center; gap: 14px;
}
.xd-metric__circle {
    width: 52px; height: 52px; border-radius: 50%;
    background: conic-gradient(var(--mc,#006b2c) var(--pct,0turn), #e5e7eb 0);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    position: relative;
}
.xd-metric__circle-inner {
    width: 36px; height: 36px; border-radius: 50%; background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 800; color: #111827;
    position: absolute;
}
.xd-metric__icon-box {
    width: 44px; height: 44px; border-radius: 10px;
    background: var(--mc-bg, rgba(0,107,44,.08));
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.xd-metric__icon-box .material-symbols-outlined { font-size: 22px; color: var(--mc, #006b2c); }
.xd-metric__label { font-size: 11px; color: #6b7280; margin-bottom: 3px; }
.xd-metric__value { font-size: 20px; font-weight: 800; color: #111827; line-height: 1; }
.xd-metric__sub   { font-size: 10px; color: #9ca3af; margin-top: 2px; }

/* Charts row */
.xd-charts-row { display: grid; grid-template-columns: 1fr 280px; gap: 14px; margin-bottom: 18px; }
.xd-chart-card {
    background: #fff; border-radius: 12px; padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.xd-chart-card__head {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
}
.xd-chart-card__title { font-size: 13px; font-weight: 700; color: #111827; }
.xd-chart-toggle { display: flex; gap: 0; background: #f3f4f6; border-radius: 6px; padding: 2px; }
.xd-chart-toggle button {
    padding: 4px 10px; font-size: 11px; font-weight: 600; border: none; border-radius: 5px;
    background: none; color: #6b7280; cursor: pointer; transition: all .15s;
}
.xd-chart-toggle button.active { background: #fff; color: #006b2c; box-shadow: 0 1px 3px rgba(0,0,0,.1); }

/* Diversity metrics */
.xd-diversity { background: #fff; border-radius: 12px; padding: 16px 18px;
                 box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.xd-div-bar-row { margin-bottom: 10px; }
.xd-div-bar-row__label { display: flex; justify-content: space-between;
                           font-size: 11px; color: #374151; margin-bottom: 4px; }
.xd-div-bar { height: 8px; border-radius: 4px; background: #e5e7eb; overflow: hidden; }
.xd-div-bar-fill { height: 100%; border-radius: 4px; background: #006b2c; }

/* === ASIDE === */
.xd-aside-section { margin-bottom: 22px; }
.xd-aside-title {
    font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
    color: #6b7280; margin: 0 0 12px; display: flex; align-items: center; justify-content: space-between;
}
.xd-aside-title a { font-size: 10px; color: #2563eb; text-decoration: none; font-weight: 600; }

/* Tasks */
.xd-task { display: flex; gap: 10px; padding: 9px 0; border-bottom: 1px solid #f3f4f6; align-items: flex-start; }
.xd-task:last-child { border-bottom: none; }
.xd-task__check { width: 16px; height: 16px; border: 2px solid #d1d5db; border-radius: 4px; flex-shrink: 0; margin-top: 1px; cursor: pointer; }
.xd-task__name { font-size: 12px; font-weight: 600; color: #111827; margin-bottom: 2px; }
.xd-task__due  { font-size: 10px; color: #9ca3af; }

/* Activity */
.xd-activity { display: flex; gap: 10px; padding: 8px 0; align-items: flex-start; }
.xd-activity__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.xd-activity__text { font-size: 11.5px; color: #374151; line-height: 1.4; }
.xd-activity__time { font-size: 10px; color: #9ca3af; margin-top: 2px; }

/* Employee of month */
.xd-eom {
    background: linear-gradient(135deg, #006b2c 0%, #00873a 100%);
    border-radius: 12px; padding: 16px 14px;
    color: #fff; position: relative; overflow: hidden;
}
.xd-eom::before {
    content: ''; position: absolute; top: -20px; right: -20px;
    width: 100px; height: 100px; border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.xd-eom__badge { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
                  background: rgba(255,255,255,.2); display: inline-block;
                  padding: 2px 8px; border-radius: 4px; margin-bottom: 10px; }
.xd-eom__info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.xd-eom__avatar {
    width: 42px; height: 42px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.4);
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.xd-eom__name { font-size: 14px; font-weight: 800; }
.xd-eom__dept { font-size: 10px; opacity: .75; }
.xd-eom__quote { font-size: 11px; opacity: .85; font-style: italic; line-height: 1.5; margin-bottom: 12px; }
.xd-eom__btn {
    width: 100%; padding: 8px; border-radius: 7px; font-size: 12px; font-weight: 700;
    background: #fff; color: #006b2c; border: none; cursor: pointer;
    transition: background .15s;
}
.xd-eom__btn:hover { background: #f0fdf4; }

/* Pending badge */
.xd-pending-badge {
    background: #dc2626; color: #fff; font-size: 9px; font-weight: 800;
    padding: 1px 6px; border-radius: 999px; margin-left: 6px;
}
</style>

<div class="xd-wrap">

<!-- ═══════════ MAIN CONTENT ═══════════ -->
<div class="xd-main">

    <!-- Header -->
    <div class="xd-header">
        <div>
            <h1 class="xd-header__title">Executive Dashboard</h1>
            <div class="xd-header__sub">Real-time overview of workforce health and talent acquisition.</div>
        </div>
        <div class="xd-header__actions">
            <a href="#" class="xd-btn xd-btn--outline">
                <span class="material-symbols-outlined" style="font-size:15px;color:#6b7280;">calendar_today</span>
                Last 30 Days
            </a>
            <a href="<?php echo $base; ?>/employees" class="xd-btn xd-btn--green">
                <span class="material-symbols-outlined" style="font-size:15px;">download</span>
                Export PDF
            </a>
        </div>
    </div>

    <!-- KPI Grid (4 cards) -->
    <?php
    $total    = $stats['total_employees']     ?? 0;
    $active   = $stats['active_employees']    ?? 0;
    $new_hire = $stats['new_hires']           ?? 0;
    $resign   = $stats['resignations']        ?? 0;
    $attend   = $stats['attendance_today']    ?? 0;
    $openrec  = $stats['open_jobs']           ?? 0;
    $appraise = $stats['pending_appraisals']  ?? 0;
    $claims   = $stats['pending_claims']      ?? 0;
    ?>
    <div class="xd-kpi-grid">
        <div class="xd-kpi" style="--kc:#006b2c;--kc-bg:rgba(0,107,44,.08);">
            <div class="xd-kpi__icon"><span class="material-symbols-outlined">groups</span></div>
            <div class="xd-kpi__label">Total Employees</div>
            <div class="xd-kpi__value"><?php echo number_format($total); ?></div>
            <div class="xd-kpi__change xd-kpi__change--up">
                <span class="material-symbols-outlined" style="font-size:13px;">trending_up</span>+2.4% vs last month
            </div>
        </div>
        <div class="xd-kpi" style="--kc:#2563eb;--kc-bg:rgba(37,99,235,.08);">
            <div class="xd-kpi__icon"><span class="material-symbols-outlined" style="color:#2563eb;">person_check</span></div>
            <div class="xd-kpi__label">Active Employees</div>
            <div class="xd-kpi__value"><?php echo number_format($active); ?></div>
            <div class="xd-kpi__change">
                <span style="color:#16a34a;font-weight:700;"><?php echo $total > 0 ? round($active/$total*100,1) : 0; ?>%</span>&nbsp;retention rate
            </div>
        </div>
        <div class="xd-kpi" style="--kc:#0891b2;--kc-bg:rgba(8,145,178,.08);">
            <div class="xd-kpi__icon"><span class="material-symbols-outlined" style="color:#0891b2;">person_add</span></div>
            <div class="xd-kpi__label">New Hires</div>
            <div class="xd-kpi__value"><?php echo number_format($new_hire); ?></div>
            <div class="xd-kpi__change">
                <span class="material-symbols-outlined" style="font-size:13px;color:#16a34a;">trending_up</span>onboarding this week
            </div>
        </div>
        <div class="xd-kpi" style="--kc:#dc2626;--kc-bg:rgba(220,38,38,.08);">
            <div class="xd-kpi__icon"><span class="material-symbols-outlined" style="color:#dc2626;">person_remove</span></div>
            <div class="xd-kpi__label">Resignations</div>
            <div class="xd-kpi__value"><?php echo number_format($resign); ?></div>
            <div class="xd-kpi__change xd-kpi__change--down">
                <span class="material-symbols-outlined" style="font-size:13px;">trending_down</span>-1.2% attrition decrease
            </div>
        </div>
    </div>

    <!-- Secondary metrics -->
    <div class="xd-metrics-strip">
        <div class="xd-metric" style="--mc:#006b2c;--mc-bg:rgba(0,107,44,.08);">
            <?php $atten_pct = $total > 0 ? min(100,round($attend/$active*100)) : 0; ?>
            <div class="xd-metric__circle" style="--pct:<?php echo $atten_pct; ?>%;
                background:conic-gradient(#006b2c <?php echo $atten_pct; ?>%,#e5e7eb 0);">
                <div class="xd-metric__circle-inner"><?php echo $atten_pct; ?>%</div>
            </div>
            <div>
                <div class="xd-metric__label">Attendance Today</div>
                <div class="xd-metric__value"><?php echo number_format($attend); ?></div>
                <div class="xd-metric__sub">Present</div>
            </div>
        </div>
        <div class="xd-metric" style="--mc:#d97706;--mc-bg:rgba(217,119,6,.08);">
            <div class="xd-metric__icon-box"><span class="material-symbols-outlined" style="color:#d97706;">work_outline</span></div>
            <div>
                <div class="xd-metric__label">Open Recruitment</div>
                <div class="xd-metric__value"><?php echo number_format($openrec); ?></div>
                <div class="xd-metric__sub">Active Roles</div>
            </div>
        </div>
        <div class="xd-metric" style="--mc:#9333ea;--mc-bg:rgba(147,51,234,.08);">
            <div class="xd-metric__icon-box"><span class="material-symbols-outlined" style="color:#9333ea;">rate_review</span></div>
            <div>
                <div class="xd-metric__label">Pending Appraisals</div>
                <div class="xd-metric__value"><?php echo number_format($appraise); ?></div>
                <div class="xd-metric__sub">Reviews Due</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="xd-charts-row">

        <!-- Headcount Trend (SVG line chart) -->
        <div class="xd-chart-card">
            <div class="xd-chart-card__head">
                <div class="xd-chart-card__title">Headcount Trend</div>
                <div class="xd-chart-toggle">
                    <button class="active" onclick="this.classList.add('active');this.nextSibling.classList.remove('active')">Monthly</button>
                    <button onclick="this.classList.add('active');this.previousSibling.classList.remove('active')">Quarterly</button>
                </div>
            </div>
            <?php
            // Generate smooth headcount data based on total employees
            $base_val = max(100, $total - 200);
            $months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct'];
            $vals     = [];
            $v = $base_val;
            foreach ($months as $m) { $v += rand(10,40); $vals[] = $v; }
            if ($total > 0) $vals[count($vals)-1] = $total;
            $min_v = min($vals); $max_v = max($vals);
            $range = max(1, $max_v - $min_v);
            $cw = 480; $ch = 160; $pad = 30;
            $aw = $cw - $pad*2; $ah = $ch - $pad*2;
            $n  = count($vals);
            $pts = [];
            foreach ($vals as $i => $val) {
                $x = $pad + ($i / ($n-1)) * $aw;
                $y = $pad + (1 - ($val - $min_v) / $range) * $ah;
                $pts[] = [$x, $y];
            }
            // Build smooth polyline
            $poly = implode(' ', array_map(fn($p) => round($p[0]).','.round($p[1]), $pts));
            // Fill area
            $area = 'M '.$pts[0][0].','.$pts[0][1];
            for ($i=1;$i<$n;$i++) $area .= ' L '.$pts[$i][0].','.$pts[$i][1];
            $area .= ' L '.$pts[$n-1][0].','.($ch-$pad).' L '.$pts[0][0].','.($ch-$pad).' Z';
            ?>
            <svg viewBox="0 0 <?php echo $cw; ?> <?php echo $ch; ?>" style="width:100%;height:<?php echo $ch; ?>px;">
                <defs>
                    <linearGradient id="hcGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#006b2c" stop-opacity=".15"/>
                        <stop offset="100%" stop-color="#006b2c" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <!-- Grid lines -->
                <?php for ($gl=0;$gl<=4;$gl++): $gy = $pad + $gl*($ah/4); ?>
                <line x1="<?php echo $pad; ?>" y1="<?php echo $gy; ?>" x2="<?php echo $cw-$pad; ?>" y2="<?php echo $gy; ?>" stroke="#f3f4f6" stroke-width="1"/>
                <text x="<?php echo $pad-4; ?>" y="<?php echo $gy+4; ?>" font-size="9" fill="#9ca3af" text-anchor="end">
                    <?php echo number_format($max_v - $gl*$range/4); ?>
                </text>
                <?php endfor; ?>
                <!-- Area fill -->
                <path d="<?php echo $area; ?>" fill="url(#hcGrad)"/>
                <!-- Line -->
                <polyline points="<?php echo $poly; ?>" fill="none" stroke="#006b2c" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                <!-- Dots -->
                <?php foreach ($pts as $i => $p): ?>
                <circle cx="<?php echo round($p[0]); ?>" cy="<?php echo round($p[1]); ?>" r="4" fill="#fff" stroke="#006b2c" stroke-width="2"/>
                <text x="<?php echo round($p[0]); ?>" y="<?php echo $ch-4; ?>" font-size="9" fill="#9ca3af" text-anchor="middle"><?php echo $months[$i]; ?></text>
                <?php endforeach; ?>
            </svg>
        </div>

        <!-- Department Distribution (donut) -->
        <div class="xd-chart-card">
            <div class="xd-chart-card__head">
                <div class="xd-chart-card__title">Department Distribution</div>
            </div>
            <?php
            $dept_data = $dept_distribution ?? [];
            if (empty($dept_data)) {
                $dept_data = [
                    ['name'=>'Engineering','count'=>round($active*0.36),'color'=>'#006b2c'],
                    ['name'=>'Marketing',  'count'=>round($active*0.17),'color'=>'#2563eb'],
                    ['name'=>'Sales',      'count'=>round($active*0.25),'color'=>'#d97706'],
                    ['name'=>'Operations', 'count'=>max(1,$active - round($active*0.36) - round($active*0.17) - round($active*0.25)),'color'=>'#9ca3af'],
                ];
            }
            $total_dept = array_sum(array_column($dept_data,'count'));
            // Draw donut
            $cx=80; $cy=80; $r=60; $ri=38; $angle=0;
            $paths = [];
            foreach ($dept_data as $d) {
                $pct  = $total_dept > 0 ? $d['count'] / $total_dept : 0;
                $deg  = $pct * 360;
                $rad1 = deg2rad($angle - 90);
                $rad2 = deg2rad($angle + $deg - 90);
                $x1o = $cx + $r * cos($rad1); $y1o = $cy + $r * sin($rad1);
                $x2o = $cx + $r * cos($rad2); $y2o = $cy + $r * sin($rad2);
                $x1i = $cx + $ri * cos($rad2); $y1i = $cy + $ri * sin($rad2);
                $x2i = $cx + $ri * cos($rad1); $y2i = $cy + $ri * sin($rad1);
                $large = $deg > 180 ? 1 : 0;
                $paths[] = ['d'=>"M $x1o $y1o A $r $r 0 $large 1 $x2o $y2o L $x1i $y1i A $ri $ri 0 $large 0 $x2i $y2i Z",
                            'color'=>$d['color'],'name'=>$d['name'],'count'=>$d['count'],'pct'=>round($pct*100)];
                $angle += $deg;
            }
            ?>
            <svg viewBox="0 0 160 160" style="width:160px;height:160px;display:block;margin:0 auto 10px;">
                <?php foreach ($paths as $p): ?>
                <path d="<?php echo $p['d']; ?>" fill="<?php echo $p['color']; ?>" stroke="#fff" stroke-width="2"/>
                <?php endforeach; ?>
                <text x="80" y="77" font-size="18" font-weight="800" fill="#111827" text-anchor="middle"><?php echo number_format($active); ?></text>
                <text x="80" y="92" font-size="9" fill="#9ca3af" text-anchor="middle">Employees</text>
            </svg>
            <div style="display:flex;flex-direction:column;gap:5px;">
            <?php foreach ($paths as $p): ?>
            <div style="display:flex;align-items:center;gap:7px;font-size:11px;color:#374151;">
                <div style="width:10px;height:10px;border-radius:3px;background:<?php echo $p['color']; ?>;flex-shrink:0;"></div>
                <span><?php echo htmlspecialchars($p['name']); ?></span>
                <span style="margin-left:auto;font-weight:700;color:#111827;"><?php echo number_format($p['count']); ?></span>
                <span style="color:#9ca3af;">(<?php echo $p['pct']; ?>%)</span>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Diversity & Inclusion -->
    <div class="xd-diversity">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="font-size:13px;font-weight:700;color:#111827;">Diversity &amp; Inclusion Metrics</div>
            <div style="font-size:11px;color:#6b7280;">Goal: 50/50 Balance by 2025</div>
        </div>
        <?php
        $div_items = [
            ['label'=>'Gender Balance (Female)', 'val'=>46, 'color'=>'#2563eb'],
            ['label'=>'Youth (Under 35)',         'val'=>58, 'color'=>'#16a34a'],
            ['label'=>'Persons with Disability',  'val'=>8,  'color'=>'#9333ea'],
            ['label'=>'Minority Ethnic Groups',   'val'=>22, 'color'=>'#d97706'],
        ];
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px 24px;">
        <?php foreach ($div_items as $di): ?>
        <div class="xd-div-bar-row">
            <div class="xd-div-bar-row__label">
                <span><?php echo $di['label']; ?></span>
                <span style="font-weight:700;color:<?php echo $di['color']; ?>"><?php echo $di['val']; ?>%</span>
            </div>
            <div class="xd-div-bar">
                <div class="xd-div-bar-fill" style="width:<?php echo $di['val']; ?>%;background:<?php echo $di['color']; ?>;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

</div><!-- /.xd-main -->


<!-- ═══════════ RIGHT SIDEBAR ═══════════ -->
<div class="xd-aside">

    <!-- Upcoming Tasks -->
    <div class="xd-aside-section">
        <div class="xd-aside-title">
            Upcoming Tasks <span class="xd-pending-badge">4 Pending</span>
            <a href="#">View All</a>
        </div>
        <?php
        $tasks = [
            ['name'=>'Approve Q3 Budget',          'due'=>'Due by Friday',        'color'=>'#9333ea'],
            ['name'=>'New Hire Orientation',        'due'=>'Tomorrow at 10:00 AM', 'color'=>'#2563eb'],
            ['name'=>'Review Contractor Docs',      'due'=>'Oct 30, 2023',         'color'=>'#d97706'],
            ['name'=>'Performance Review — Engineering', 'due'=>'Next week',       'color'=>'#16a34a'],
        ];
        foreach ($tasks as $task):
        ?>
        <div class="xd-task">
            <div class="xd-task__check" style="border-color:<?php echo $task['color']; ?>;"></div>
            <div>
                <div class="xd-task__name"><?php echo htmlspecialchars($task['name']); ?></div>
                <div class="xd-task__due"><?php echo $task['due']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Activity -->
    <div class="xd-aside-section">
        <div class="xd-aside-title">Recent Activity <a href="#">View All</a></div>
        <?php
        $activities = [
            ['dot'=>'#16a34a','text'=>'<strong>Sarah Jenkins</strong> joined the Engineering team.','time'=>'2 hours ago'],
            ['dot'=>'#2563eb','text'=>'<strong>Marcus Thorne</strong> promoted to Senior Designer.','time'=>'4 hours ago'],
            ['dot'=>'#9ca3af','text'=>'New policy document <strong>Hybrid_Work_v2</strong> uploaded.','time'=>'Yesterday, 4:30 PM'],
            ['dot'=>'#dc2626','text'=>'<strong>Annual Retreat</strong> venue confirmed.','time'=>'Yesterday, 11:20 AM'],
        ];
        foreach ($activities as $act):
        ?>
        <div class="xd-activity">
            <div class="xd-activity__dot" style="background:<?php echo $act['dot']; ?>;"></div>
            <div>
                <div class="xd-activity__text"><?php echo $act['text']; ?></div>
                <div class="xd-activity__time"><?php echo $act['time']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Employee of the Month -->
    <div class="xd-eom">
        <div class="xd-eom__badge">Employee of the Month</div>
        <?php
        $eom_name  = $employee_of_month->name ?? 'David A.';
        $eom_dept  = $employee_of_month->department ?? 'Engineering';
        $eom_quote = $employee_of_month->achievement ?? '"Exceeded project delivery targets by 20%, while maintaining stellar team feedback."';
        $init2     = strtoupper(substr($eom_name,0,1)).strtoupper(substr(strstr($eom_name,' '),1,1) ?: 'A');
        ?>
        <div class="xd-eom__info">
            <div class="xd-eom__avatar"><?php echo $init2; ?></div>
            <div>
                <div class="xd-eom__name"><?php echo htmlspecialchars($eom_name); ?></div>
                <div class="xd-eom__dept"><?php echo htmlspecialchars($eom_dept); ?></div>
            </div>
        </div>
        <div class="xd-eom__quote"><?php echo htmlspecialchars($eom_quote); ?></div>
        <button class="xd-eom__btn">Send Kudos</button>
    </div>

    <!-- Quick Links -->
    <div class="xd-aside-section" style="margin-top:18px;">
        <div class="xd-aside-title">Quick Actions</div>
        <div style="display:flex;flex-direction:column;gap:6px;">
            <?php
            $links = [
                [admin_url('xetuu_hr/employees/add'),'person_add','Add Employee','#eff6ff','#2563eb'],
                [admin_url('xetuu_hr/payroll/batches'),'receipt','Run Payroll','#f0fdf4','#16a34a'],
                [admin_url('xetuu_hr/leave/apply'),'beach_access','Apply Leave','#fdf4ff','#9333ea'],
                [admin_url('xetuu_hr/attendance'),'today','Mark Attendance','#fff7ed','#d97706'],
            ];
            foreach ($links as [$url,$icon,$label,$bg,$color]):
            ?>
            <a href="<?php echo $url; ?>" style="display:flex;align-items:center;gap:10px;padding:9px 12px;
               background:<?php echo $bg; ?>;border-radius:8px;text-decoration:none;transition:opacity .15s;"
               onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                <span class="material-symbols-outlined" style="font-size:18px;color:<?php echo $color; ?>;"><?php echo $icon; ?></span>
                <span style="font-size:12px;font-weight:600;color:#374151;"><?php echo $label; ?></span>
                <span class="material-symbols-outlined" style="font-size:14px;color:#9ca3af;margin-left:auto;">arrow_forward</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</div><!-- /.xd-aside -->

</div><!-- /.xd-wrap -->

<?php init_tail(); ?>
