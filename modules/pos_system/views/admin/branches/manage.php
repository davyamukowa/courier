<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
/* ── Palette ───────────────────────────────────────────────────────────── */
:root {
  --g50:#f0fdf4;--g100:#dcfce7;--g200:#bbf7d0;--g300:#86efac;
  --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;
  --sl50:#f8fafc;--sl100:#f1f5f9;--sl200:#e2e8f0;--sl300:#cbd5e1;
  --sl400:#94a3b8;--sl500:#64748b;--sl600:#475569;--sl700:#334155;--sl800:#1e293b;
  --amb:#f59e0b;--blu:#3b82f6;--red:#ef4444;
}

/* ── Hero ──────────────────────────────────────────────────────────────── */
.mb-hero {
  background: linear-gradient(135deg, #0f4c2a 0%, #15803d 45%, #166534 100%);
  border-radius: 16px;
  padding: 40px 40px 36px;
  margin-bottom: 28px;
  position: relative;
  overflow: hidden;
}
.mb-hero::before {
  content: '';
  position: absolute; top: -60px; right: -60px;
  width: 260px; height: 260px;
  border-radius: 50%;
  background: rgba(255,255,255,.06);
  pointer-events: none;
}
.mb-hero::after {
  content: '';
  position: absolute; bottom: -80px; left: 30%;
  width: 340px; height: 200px;
  border-radius: 50%;
  background: rgba(255,255,255,.04);
  pointer-events: none;
}
.mb-hero-tag {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 20px;
  padding: 4px 14px;
  font-size: 11px; font-weight: 600; color: #86efac;
  text-transform: uppercase; letter-spacing: .07em;
  margin-bottom: 14px;
}
.mb-hero h1 {
  font-size: 28px; font-weight: 800; color: #fff;
  margin: 0 0 10px; letter-spacing: -.5px; line-height: 1.2;
}
.mb-hero h1 span { color: #86efac; }
.mb-hero p {
  color: rgba(255,255,255,.8); font-size: 14px;
  line-height: 1.65; max-width: 560px; margin: 0 0 24px;
}
.mb-hero-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.btn-hero-primary {
  background: #fff; color: #15803d; border: none;
  border-radius: 9px; padding: 10px 22px;
  font-size: 13px; font-weight: 700; cursor: pointer;
  display: inline-flex; align-items: center; gap: 7px;
  transition: all .15s; text-decoration: none;
}
.btn-hero-primary:hover { background: #f0fdf4; color: #15803d; text-decoration: none; transform: translateY(-1px); }
.btn-hero-secondary {
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);
  color: #fff; border-radius: 9px; padding: 10px 18px;
  font-size: 13px; font-weight: 600; cursor: pointer;
  display: inline-flex; align-items: center; gap: 7px;
  transition: all .15s; text-decoration: none;
}
.btn-hero-secondary:hover { background: rgba(255,255,255,.2); color: #fff; text-decoration: none; }
.mb-features {
  display: flex; gap: 8px; flex-wrap: wrap;
  margin-top: 22px; padding-top: 22px;
  border-top: 1px solid rgba(255,255,255,.12);
}
.mb-feat {
  display: flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
  border-radius: 7px; padding: 6px 12px;
  color: rgba(255,255,255,.9); font-size: 12px; font-weight: 500;
}
.mb-feat .check { color: #86efac; font-size: 13px; }

/* ── Summary strip ──────────────────────────────────────────────────────── */
.mb-summary {
  display: grid; grid-template-columns: repeat(3,1fr);
  gap: 16px; margin-bottom: 28px;
}
.mb-stat-card {
  background: #fff; border: 1.5px solid var(--sl200);
  border-radius: 12px; padding: 20px 22px;
  display: flex; align-items: center; gap: 16px;
}
.mb-stat-icon {
  width: 44px; height: 44px; border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; flex-shrink: 0;
}
.mb-stat-icon.green  { background: var(--g100);  color: var(--g700); }
.mb-stat-icon.amber  { background: #fef3c7;       color: #b45309; }
.mb-stat-icon.blue   { background: var(--g100);   color: var(--g700); }
.mb-stat-val { font-size: 24px; font-weight: 800; color: var(--sl800); line-height: 1; }
.mb-stat-lbl { font-size: 11px; color: var(--sl400); text-transform: uppercase; letter-spacing: .06em; margin-top: 3px; }

/* ── Section header ─────────────────────────────────────────────────────── */
.mb-section-hdr {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.mb-section-hdr h4 { font-size: 15px; font-weight: 700; color: var(--sl800); margin: 0; }
.mb-section-hdr span { font-size: 12px; color: var(--sl400); }

/* ── Benefits ────────────────────────────────────────────────────────────── */
.mb-benefits {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px,1fr));
  gap: 14px; margin-bottom: 32px;
}
.mb-benefit {
  background: #fff; border: 1.5px solid var(--sl200);
  border-radius: 12px; padding: 18px 16px;
  transition: border-color .15s, box-shadow .15s;
}
.mb-benefit:hover { border-color: var(--g300); box-shadow: 0 4px 16px rgba(22,163,74,.08); }
.mb-benefit-icon {
  width: 36px; height: 36px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px; margin-bottom: 12px;
}
.mb-benefit h5 { font-size: 13px; font-weight: 700; color: var(--sl800); margin: 0 0 5px; }
.mb-benefit p  { font-size: 11.5px; color: var(--sl500); margin: 0; line-height: 1.55; }

/* ── Branch grid ────────────────────────────────────────────────────────── */
.mb-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px,1fr));
  gap: 18px; margin-bottom: 32px;
}
.mb-card {
  background: #fff; border: 1.5px solid var(--sl200);
  border-radius: 14px; overflow: hidden;
  transition: box-shadow .2s, border-color .2s;
}
.mb-card:hover { border-color: var(--g300); box-shadow: 0 6px 24px rgba(22,163,74,.1); }
.mb-card.inactive { opacity: .65; }

.mb-card-top {
  background: linear-gradient(135deg, var(--g700) 0%, var(--g600) 100%);
  padding: 20px 20px 16px; position: relative;
}
.mb-card-top .badge-status {
  position: absolute; top: 14px; right: 14px;
  font-size: 10px; font-weight: 700;
  padding: 3px 10px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: .05em;
}
.badge-status.active   { background: rgba(255,255,255,.25); color: #fff; }
.badge-status.inactive { background: rgba(0,0,0,.25); color: rgba(255,255,255,.7); }
.mb-card-avatar {
  width: 44px; height: 44px; border-radius: 11px;
  background: rgba(255,255,255,.2); border: 2px solid rgba(255,255,255,.35);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 10px;
}
.mb-card-name { font-size: 17px; font-weight: 800; color: #fff; margin-bottom: 4px; line-height: 1.2; }
.mb-card-code {
  display: inline-block;
  background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.25);
  border-radius: 5px; padding: 2px 8px;
  font-size: 11px; color: rgba(255,255,255,.9);
  font-family: monospace; font-weight: 600; margin-right: 6px;
}
.mb-card-location {
  font-size: 12px; color: rgba(255,255,255,.75);
  margin-top: 7px; display: flex; align-items: center; gap: 5px;
}

.mb-card-body { padding: 16px 20px; }
.mb-card-stats {
  display: grid; grid-template-columns: repeat(3,1fr);
  gap: 1px; background: var(--sl100);
  border-radius: 9px; overflow: hidden; margin-bottom: 16px;
}
.mb-cs { background: #fff; padding: 12px 10px; text-align: center; }
.mb-cs .cv {
  font-size: 15px; font-weight: 800; color: var(--sl800);
  font-family: monospace; display: block; line-height: 1; margin-bottom: 3px;
}
.mb-cs .cl { font-size: 9px; color: var(--sl400); text-transform: uppercase; letter-spacing: .06em; }

.mb-card-meta { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.mb-meta-row { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--sl500); }
.mb-meta-row i { color: var(--sl400); width: 14px; text-align: center; }
.mb-meta-row strong { color: var(--sl700); font-weight: 600; }

.mb-card-actions {
  display: flex; gap: 8px;
  padding-top: 12px; border-top: 1px solid var(--sl100);
}
.btn-action {
  flex: 1; padding: 8px 10px; border-radius: 8px;
  font-size: 12px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 5px;
  border: 1.5px solid; transition: all .15s; text-decoration: none;
}
.btn-act-edit   { color: var(--sl600); border-color: var(--sl200); background: var(--sl50); }
.btn-act-edit:hover { background: var(--sl100); color: var(--sl800); text-decoration: none; }
.btn-act-assign { color: var(--g700); border-color: var(--g200); background: var(--g50); }
.btn-act-assign:hover { background: var(--g100); color: var(--g800); text-decoration: none; }
.btn-act-delete { color: var(--red); border-color: #fecaca; background: #fff5f5; }
.btn-act-delete:hover { background: #fee2e2; text-decoration: none; }

/* ── Empty state ─────────────────────────────────────────────────────────── */
.mb-empty { text-align: center; padding: 60px 20px; background: #fff; border: 2px dashed var(--sl200); border-radius: 14px; }
.mb-empty-icon {
  width: 64px; height: 64px; background: var(--g50); border: 2px solid var(--g200);
  border-radius: 16px; display: flex; align-items: center; justify-content: center;
  font-size: 28px; margin: 0 auto 16px;
}
.mb-empty h4 { font-size: 16px; font-weight: 700; color: var(--sl700); margin-bottom: 6px; }
.mb-empty p  { font-size: 13px; color: var(--sl400); max-width: 340px; margin: 0 auto 20px; }
</style>

<div id="wrapper">
  <div class="content">

    <!-- ══ Hero ══════════════════════════════════════════════════════════ -->
    <div class="mb-hero">
      <div class="mb-hero-tag">
        <i class="fa fa-building"></i> Multi-Branch Add-On
      </div>
      <h1>Master Your <span>Multi-Store Enterprise</span></h1>
      <p>Tired of juggling multiple store systems? Bring everything together with centralized control — sync inventory, pricing, and customer data across all locations in real-time from a single dashboard.</p>
      <div class="mb-hero-actions">
        <button type="button" class="btn-hero-primary" data-toggle="modal" data-target="#branchModal" onclick="resetBranchForm()">
          <i class="fa fa-plus"></i> Add New Branch
        </button>
        <a href="<?php echo admin_url('pos_system'); ?>" class="btn-hero-secondary">
          <i class="fa fa-tachometer"></i> POS Dashboard
        </a>
      </div>
      <div class="mb-features">
        <div class="mb-feat"><span class="check">✅</span> Centralized Control</div>
        <div class="mb-feat"><span class="check">✅</span> Real-Time Sync</div>
        <div class="mb-feat"><span class="check">✅</span> Unified Reporting</div>
        <div class="mb-feat"><span class="check">✅</span> Streamlined Operations</div>
        <div class="mb-feat"><span class="check">✅</span> Scalable Growth</div>
      </div>
    </div>

    <!-- ══ Summary Strip ═════════════════════════════════════════════════ -->
    <div class="mb-summary">
      <div class="mb-stat-card">
        <div class="mb-stat-icon green"><i class="fa fa-building"></i></div>
        <div>
          <div class="mb-stat-val"><?php echo count($branches); ?></div>
          <div class="mb-stat-lbl"><?php echo count($branches) === 1 ? 'Active Branch' : 'Total Branches'; ?></div>
        </div>
      </div>
      <div class="mb-stat-card">
        <div class="mb-stat-icon amber"><i class="fa fa-users"></i></div>
        <div>
          <div class="mb-stat-val"><?php echo $total_staff; ?></div>
          <div class="mb-stat-lbl">Staff Assigned</div>
        </div>
      </div>
      <div class="mb-stat-card">
        <div class="mb-stat-icon blue"><i class="fa fa-line-chart"></i></div>
        <div>
          <div class="mb-stat-val"><?php echo pos_format_currency($total_revenue); ?></div>
          <div class="mb-stat-lbl">All-Time Revenue</div>
        </div>
      </div>
    </div>

    <!-- ══ Key Benefits ═══════════════════════════════════════════════════ -->
    <div class="mb-section-hdr">
      <h4><i class="fa fa-star" style="color:var(--amb);margin-right:7px"></i>Key Benefits</h4>
      <span>Mix and match features that fit your enterprise</span>
    </div>
    <div class="mb-benefits">
      <div class="mb-benefit">
        <div class="mb-benefit-icon" style="background:var(--g100);color:var(--g700)"><i class="fa fa-sitemap"></i></div>
        <h5>Centralized Control</h5>
        <p>Manage all branches from one admin panel — no need to log into each location separately.</p>
      </div>
      <div class="mb-benefit">
        <div class="mb-benefit-icon" style="background:#dcfce7;color:#15803d"><i class="fa fa-refresh"></i></div>
        <h5>Real-Time Sync</h5>
        <p>Instant updates on inventory, sales, and customer data across every branch the moment it happens.</p>
      </div>
      <div class="mb-benefit">
        <div class="mb-benefit-icon" style="background:#fef3c7;color:#b45309"><i class="fa fa-bar-chart"></i></div>
        <h5>Unified Reporting</h5>
        <p>Get a complete view of your business performance consolidated across all your locations.</p>
      </div>
      <div class="mb-benefit">
        <div class="mb-benefit-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fa fa-cogs"></i></div>
        <h5>Streamlined Operations</h5>
        <p>Simplify stock transfers, pricing updates, and user permissions at scale — zero duplication.</p>
      </div>
      <div class="mb-benefit">
        <div class="mb-benefit-icon" style="background:#fff1f2;color:#be123c"><i class="fa fa-expand"></i></div>
        <h5>Scalable Growth</h5>
        <p>Easily add new locations as your business expands — the system scales with you effortlessly.</p>
      </div>
    </div>

    <!-- ══ Branch Cards ═══════════════════════════════════════════════════ -->
    <div class="mb-section-hdr">
      <h4><i class="fa fa-map-marker" style="color:var(--g600);margin-right:7px"></i>Your Branches</h4>
      <span><?php echo count($branches); ?> location<?php echo count($branches) !== 1 ? 's' : ''; ?> configured</span>
    </div>

    <?php if (empty($branches)): ?>
    <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:16px 20px;margin-bottom:18px;display:flex;gap:14px;align-items:flex-start">
      <i class="fa fa-info-circle" style="color:#b45309;font-size:18px;flex-shrink:0;margin-top:1px"></i>
      <div style="font-size:13px;color:#78350f;line-height:1.65">
        <strong>How the first branch works:</strong> When you create your first branch, the system will automatically
        create a second branch using your company name (<strong><?php echo htmlspecialchars(get_option('companyname') ?: 'your company'); ?></strong>).
        All your existing data will be assigned to this auto-created branch. It is protected and cannot be deleted,
        ensuring your historical data always has a home.
      </div>
    </div>
    <?php endif; ?>

    <?php if (empty($branches)): ?>
    <div class="mb-empty">
      <div class="mb-empty-icon"><i class="fa fa-building-o" style="color:var(--g600)"></i></div>
      <h4>No branches yet</h4>
      <p>Add your first branch to start managing multiple locations from a single dashboard.</p>
      <button type="button" class="btn-hero-primary" data-toggle="modal" data-target="#branchModal" onclick="resetBranchForm()" style="display:inline-flex;margin:0 auto">
        <i class="fa fa-plus"></i> Add First Branch
      </button>
    </div>
    <?php else: ?>
    <div class="mb-grid">
      <?php foreach ($branches as $b): ?>
      <?php
        $initial  = strtoupper(substr($b['name'], 0, 1));
        $active   = (bool)($b['is_active'] ?? 1);
        $currency = htmlspecialchars($b['currency'] ?? 'KES');
        $city     = htmlspecialchars($b['city'] ?? '');
        $country  = htmlspecialchars($b['country'] ?? '');
        $location = trim(implode(', ', array_filter([$city, $country])));
        $rev      = (float)$b['total_revenue'];
        if ($rev >= 1000000)     $rev_short = number_format($rev/1000000, 1).'M';
        elseif ($rev >= 1000)    $rev_short = number_format($rev/1000, 1).'K';
        else                     $rev_short = number_format($rev);
      ?>
      <div class="mb-card<?php echo $active ? '' : ' inactive'; ?>">
        <div class="mb-card-top">
          <span class="badge-status <?php echo $active ? 'active' : 'inactive'; ?>">
            <?php echo $active ? '● Active' : '○ Inactive'; ?>
          </span>
          <?php if (!empty($b['is_protected'])): ?>
          <span style="position:absolute;top:14px;left:14px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:12px;padding:2px 9px;font-size:10px;font-weight:700;color:#fff;letter-spacing:.04em">
            <i class="fa fa-lock" style="margin-right:3px;font-size:9px"></i>AUTO
          </span>
          <?php endif; ?>
          <div class="mb-card-avatar"><?php echo $initial; ?></div>
          <div class="mb-card-name"><?php echo htmlspecialchars($b['name']); ?></div>
          <div>
            <span class="mb-card-code"><?php echo htmlspecialchars($b['code']); ?></span>
            <span style="font-size:11px;color:rgba(255,255,255,.7)"><?php echo $currency; ?></span>
          </div>
          <?php if ($location): ?>
          <div class="mb-card-location">
            <i class="fa fa-map-marker"></i> <?php echo $location; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="mb-card-body">
          <div class="mb-card-stats">
            <div class="mb-cs">
              <span class="cv"><?php echo $b['staff_count']; ?></span>
              <span class="cl">Staff</span>
            </div>
            <div class="mb-cs">
              <span class="cv"><?php echo number_format($b['total_sales']); ?></span>
              <span class="cl">Sales</span>
            </div>
            <div class="mb-cs">
              <span class="cv" title="<?php echo pos_format_currency($b['total_revenue']); ?>"><?php echo $rev_short; ?></span>
              <span class="cl">Revenue</span>
            </div>
          </div>

          <div class="mb-card-meta">
            <?php if (!empty($b['phone'])): ?>
            <div class="mb-meta-row"><i class="fa fa-phone"></i> <span><?php echo htmlspecialchars($b['phone']); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($b['email'])): ?>
            <div class="mb-meta-row"><i class="fa fa-envelope-o"></i> <span><?php echo htmlspecialchars($b['email']); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($b['timezone'])): ?>
            <div class="mb-meta-row"><i class="fa fa-clock-o"></i> <span><?php echo htmlspecialchars($b['timezone']); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($b['tax_pin'])): ?>
            <div class="mb-meta-row"><i class="fa fa-id-card-o"></i> <strong>Tax PIN:</strong> <span><?php echo htmlspecialchars($b['tax_pin']); ?></span></div>
            <?php endif; ?>
            <div class="mb-meta-row">
              <i class="fa fa-calendar-o"></i>
              <span>Today: <strong style="color:var(--g700)"><?php echo pos_format_currency($b['today_revenue']); ?></strong></span>
            </div>
          </div>

          <div class="mb-card-actions">
            <button class="btn-action btn-act-edit edit-branch"
                    data-branch='<?php echo htmlspecialchars(json_encode($b), ENT_QUOTES); ?>'>
              <i class="fa fa-pencil"></i> Edit
            </button>
            <button class="btn-action btn-act-assign manage-staff-btn"
                    data-branch-id="<?php echo $b['id']; ?>"
                    data-branch-name="<?php echo htmlspecialchars($b['name']); ?>"
                    title="Manage staff assigned to this branch">
              <i class="fa fa-users"></i> Staff
            </button>
            <?php if (!empty($b['is_protected'])): ?>
            <span class="btn-action" title="This branch is auto-created and protected — it cannot be deleted."
                  style="color:var(--sl400);border-color:var(--sl200);background:var(--sl50);cursor:default;flex:0.6">
              <i class="fa fa-lock"></i>
            </span>
            <?php else: ?>
            <a href="<?php echo admin_url('pos_system/branch_delete/' . $b['id']); ?>"
               class="btn-action btn-act-delete"
               onclick="return confirm('Delete this branch? This cannot be undone.')">
              <i class="fa fa-trash"></i>
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Add branch tile -->
      <div class="mb-card" style="border-style:dashed;cursor:pointer;display:flex;align-items:center;justify-content:center;min-height:280px"
           data-toggle="modal" data-target="#branchModal" onclick="resetBranchForm()">
        <div style="text-align:center;padding:30px 20px">
          <div style="width:52px;height:52px;border-radius:13px;background:var(--g50);border:2px solid var(--g200);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:22px;color:var(--g600)">
            <i class="fa fa-plus"></i>
          </div>
          <div style="font-size:14px;font-weight:700;color:var(--sl700);margin-bottom:5px">Add New Branch</div>
          <div style="font-size:12px;color:var(--sl400)">Expand your enterprise to a new location</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ══ Analytics Tables ═══════════════════════════════════════════════════ -->
<div id="wrapper-analytics">
 <div class="content">

  <?php
  $cur_year = date('Y');
  $years = range($cur_year, max(2023, $cur_year - 4));
  $year_select = '<form method="get" style="display:inline-flex;align-items:center;gap:8px;margin:0">'
    . '<label style="font-size:12px;color:var(--sl500);margin:0">Year</label>'
    . '<select name="year" onchange="this.form.submit()" style="border:1.5px solid var(--sl200);border-radius:7px;padding:4px 10px;font-size:13px;font-weight:600;color:var(--sl700);background:#fff;cursor:pointer">';
  foreach ($years as $y) {
    $sel = ($y == $report_year) ? ' selected' : '';
    $year_select .= "<option value=\"$y\"$sel>$y</option>";
  }
  $year_select .= '</select></form>';
  ?>

  <!-- ── Row 1: Sales + Purchases ──────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

    <!-- Branch Wise Sales -->
    <div style="background:#fff;border:1.5px solid var(--sl200);border-radius:14px;overflow:hidden">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid var(--sl100)">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--sl800);display:flex;align-items:center;gap:8px">
            <span style="width:28px;height:28px;border-radius:7px;background:var(--g100);color:var(--g700);display:inline-flex;align-items:center;justify-content:center;font-size:13px"><i class="fa fa-shopping-cart"></i></span>
            Branch Wise Sales
          </div>
          <div style="font-size:11px;color:var(--sl400);margin-top:2px">Revenue breakdown by location</div>
        </div>
        <?php echo $year_select; ?>
      </div>
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
          <thead>
            <tr style="background:var(--sl50)">
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">#</th>
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Branch</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Total Sales</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Paid</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Due</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($sales_rows)): ?>
            <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--sl400)">No sales data for <?php echo $report_year; ?></td></tr>
          <?php else: foreach ($sales_rows as $i => $r): ?>
            <tr style="border-bottom:1px solid var(--sl100)" onmouseover="this.style.background='var(--sl50)'" onmouseout="this.style.background=''">
              <td style="padding:10px 12px;color:var(--sl400);font-weight:500"><?php echo $i+1; ?></td>
              <td style="padding:10px 12px">
                <div style="font-weight:600;color:var(--sl800)"><?php echo htmlspecialchars($r['branch_name']); ?></div>
                <div style="font-size:11px;color:var(--sl400)"><?php echo number_format((int)$r['total_sales']); ?> transactions</div>
              </td>
              <td style="padding:10px 12px;text-align:right;font-weight:700;color:var(--sl800);font-family:monospace"><?php echo pos_format_currency($r['total_amount']); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:600;color:var(--g700);font-family:monospace"><?php echo pos_format_currency($r['paid']); ?></td>
              <td style="padding:10px 12px;text-align:right;font-family:monospace">
                <?php if ((float)$r['due'] > 0): ?>
                  <span style="color:var(--red);font-weight:700"><?php echo pos_format_currency($r['due']); ?></span>
                <?php else: ?>
                  <span style="color:var(--sl300)">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
          <?php
            $s_total = array_sum(array_column($sales_rows, 'total_amount'));
            $s_paid  = array_sum(array_column($sales_rows, 'paid'));
            $s_due   = array_sum(array_column($sales_rows, 'due'));
          ?>
          <?php if (!empty($sales_rows)): ?>
          <tfoot>
            <tr style="background:var(--g50);border-top:2px solid var(--g200)">
              <td colspan="2" style="padding:10px 12px;font-weight:700;color:var(--g800);font-size:12px">TOTAL</td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--sl800);font-family:monospace"><?php echo pos_format_currency($s_total); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--g700);font-family:monospace"><?php echo pos_format_currency($s_paid); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--red);font-family:monospace"><?php echo $s_due > 0 ? pos_format_currency($s_due) : '—'; ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Branch Wise Purchases -->
    <div style="background:#fff;border:1.5px solid var(--sl200);border-radius:14px;overflow:hidden">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid var(--sl100)">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--sl800);display:flex;align-items:center;gap:8px">
            <span style="width:28px;height:28px;border-radius:7px;background:#dcfce7;color:#15803d;display:inline-flex;align-items:center;justify-content:center;font-size:13px"><i class="fa fa-truck"></i></span>
            Branch Wise Purchases
          </div>
          <div style="font-size:11px;color:var(--sl400);margin-top:2px">Stock received by location</div>
        </div>
        <?php echo $year_select; ?>
      </div>
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
          <thead>
            <tr style="background:var(--sl50)">
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">#</th>
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Branch</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Total Purchase</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Paid</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Due</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($purchase_rows)): ?>
            <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--sl400)">No purchase data for <?php echo $report_year; ?></td></tr>
          <?php else: foreach ($purchase_rows as $i => $r): ?>
            <tr style="border-bottom:1px solid var(--sl100)" onmouseover="this.style.background='var(--sl50)'" onmouseout="this.style.background=''">
              <td style="padding:10px 12px;color:var(--sl400);font-weight:500"><?php echo $i+1; ?></td>
              <td style="padding:10px 12px">
                <div style="font-weight:600;color:var(--sl800)"><?php echo htmlspecialchars($r['branch_name']); ?></div>
                <div style="font-size:11px;color:var(--sl400)"><?php echo number_format((int)$r['total_purchases']); ?> batch<?php echo (int)$r['total_purchases'] !== 1 ? 'es' : ''; ?> received</div>
              </td>
              <td style="padding:10px 12px;text-align:right;font-weight:700;color:var(--sl800);font-family:monospace"><?php echo pos_format_currency($r['total_amount']); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:600;color:var(--g700);font-family:monospace"><?php echo pos_format_currency($r['paid']); ?></td>
              <td style="padding:10px 12px;text-align:right;font-family:monospace">
                <?php if ((float)$r['due'] > 0): ?>
                  <span style="color:var(--red);font-weight:700"><?php echo pos_format_currency($r['due']); ?></span>
                <?php else: ?>
                  <span style="color:var(--sl300)">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
          <?php
            $p_total = array_sum(array_column($purchase_rows, 'total_amount'));
            $p_paid  = array_sum(array_column($purchase_rows, 'paid'));
            $p_due   = array_sum(array_column($purchase_rows, 'due'));
          ?>
          <?php if (!empty($purchase_rows)): ?>
          <tfoot>
            <tr style="background:#eff6ff;border-top:2px solid #bfdbfe">
              <td colspan="2" style="padding:10px 12px;font-weight:700;color:#15803d;font-size:12px">TOTAL</td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--sl800);font-family:monospace"><?php echo pos_format_currency($p_total); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--g700);font-family:monospace"><?php echo pos_format_currency($p_paid); ?></td>
              <td style="padding:10px 12px;text-align:right;font-weight:800;color:var(--red);font-family:monospace"><?php echo $p_due > 0 ? pos_format_currency($p_due) : '—'; ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- ── Row 2: Expired Products + Employee Overview ────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:40px">

    <!-- Expired Products -->
    <div style="background:#fff;border:1.5px solid var(--sl200);border-radius:14px;overflow:hidden">
      <div style="padding:16px 20px 12px;border-bottom:1px solid var(--sl100)">
        <div style="font-size:14px;font-weight:700;color:var(--sl800);display:flex;align-items:center;gap:8px">
          <span style="width:28px;height:28px;border-radius:7px;background:#fff1f2;color:#be123c;display:inline-flex;align-items:center;justify-content:center;font-size:13px"><i class="fa fa-exclamation-triangle"></i></span>
          Expired Products
        </div>
        <div style="font-size:11px;color:var(--sl400);margin-top:2px">Stock past expiry date — action required</div>
      </div>
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
          <thead>
            <tr style="background:var(--sl50)">
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">#</th>
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Branch</th>
              <th style="padding:9px 12px;text-align:center;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Products</th>
              <th style="padding:9px 12px;text-align:right;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Qty</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $any_expired = false;
            foreach ($expired_rows as $i => $r):
              $has = (float)$r['total_qty'] > 0;
              if ($has) $any_expired = true;
          ?>
            <tr style="border-bottom:1px solid var(--sl100)" onmouseover="this.style.background='var(--sl50)'" onmouseout="this.style.background=''">
              <td style="padding:10px 12px;color:var(--sl400);font-weight:500"><?php echo $i+1; ?></td>
              <td style="padding:10px 12px;font-weight:600;color:var(--sl800)"><?php echo htmlspecialchars($r['branch_name']); ?></td>
              <td style="padding:10px 12px;text-align:center">
                <?php if ($has): ?>
                  <span style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;border-radius:12px;padding:2px 10px;font-size:11px;font-weight:700"><?php echo (int)$r['product_count']; ?> SKU<?php echo (int)$r['product_count'] !== 1 ? 's' : ''; ?></span>
                <?php else: ?>
                  <span style="color:var(--g600);font-size:12px;font-weight:600">&#10003; None</span>
                <?php endif; ?>
              </td>
              <td style="padding:10px 12px;text-align:right;font-family:monospace;font-weight:700;color:<?php echo $has ? '#be123c' : 'var(--sl300)'; ?>">
                <?php echo $has ? number_format((float)$r['total_qty'], 2) : '—'; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($expired_rows)): ?>
            <tr><td colspan="4" style="padding:24px;text-align:center;color:var(--sl400)">No branch data available</td></tr>
          <?php elseif (!$any_expired): ?>
            <tr>
              <td colspan="4" style="padding:20px;text-align:center">
                <span style="color:var(--g700);font-weight:600;font-size:13px">All clear — no expired stock</span>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Employee Overview -->
    <div style="background:#fff;border:1.5px solid var(--sl200);border-radius:14px;overflow:hidden">
      <div style="padding:16px 20px 12px;border-bottom:1px solid var(--sl100)">
        <div style="font-size:14px;font-weight:700;color:var(--sl800);display:flex;align-items:center;gap:8px">
          <span style="width:28px;height:28px;border-radius:7px;background:#fef3c7;color:#b45309;display:inline-flex;align-items:center;justify-content:center;font-size:13px"><i class="fa fa-users"></i></span>
          Employee Overview
        </div>
        <div style="font-size:11px;color:var(--sl400);margin-top:2px">Staff headcount and roles by branch</div>
      </div>
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
          <thead>
            <tr style="background:var(--sl50)">
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">#</th>
              <th style="padding:9px 12px;text-align:left;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Branch</th>
              <th style="padding:9px 12px;text-align:center;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Staffs</th>
              <th style="padding:9px 12px;text-align:center;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Cashiers</th>
              <th style="padding:9px 12px;text-align:center;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Supervisors</th>
              <th style="padding:9px 12px;text-align:center;font-weight:600;color:var(--sl500);font-size:11px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--sl100)">Managers</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($staff_rows)): ?>
            <tr><td colspan="6" style="padding:24px;text-align:center;color:var(--sl400)">No branch data available</td></tr>
          <?php else: foreach ($staff_rows as $i => $r): ?>
            <tr style="border-bottom:1px solid var(--sl100)" onmouseover="this.style.background='var(--sl50)'" onmouseout="this.style.background=''">
              <td style="padding:10px 12px;color:var(--sl400);font-weight:500"><?php echo $i+1; ?></td>
              <td style="padding:10px 12px;font-weight:600;color:var(--sl800)"><?php echo htmlspecialchars($r['branch_name']); ?></td>
              <td style="padding:10px 12px;text-align:center">
                <span style="background:var(--sl100);color:var(--sl700);border-radius:12px;padding:3px 11px;font-weight:800;font-size:13px"><?php echo (int)$r['total_staff']; ?></span>
              </td>
              <td style="padding:10px 12px;text-align:center">
                <?php if ((int)$r['cashiers'] > 0): ?>
                  <span style="background:var(--g50);color:var(--g700);border:1px solid var(--g200);border-radius:10px;padding:2px 9px;font-size:12px;font-weight:600"><?php echo (int)$r['cashiers']; ?></span>
                <?php else: ?><span style="color:var(--sl300)">—</span><?php endif; ?>
              </td>
              <td style="padding:10px 12px;text-align:center">
                <?php if ((int)$r['supervisors'] > 0): ?>
                  <span style="background:#dcfce7;color:#15803d;border:1px solid #bfdbfe;border-radius:10px;padding:2px 9px;font-size:12px;font-weight:600"><?php echo (int)$r['supervisors']; ?></span>
                <?php else: ?><span style="color:var(--sl300)">—</span><?php endif; ?>
              </td>
              <td style="padding:10px 12px;text-align:center">
                <?php if ((int)$r['managers'] > 0): ?>
                  <span style="background:#fef3c7;color:#b45309;border:1px solid #fcd34d;border-radius:10px;padding:2px 9px;font-size:12px;font-weight:600"><?php echo (int)$r['managers']; ?></span>
                <?php else: ?><span style="color:var(--sl300)">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($staff_rows)): ?>
          <tfoot>
            <tr style="background:#fef3c7;border-top:2px solid #fcd34d">
              <td colspan="2" style="padding:10px 12px;font-weight:700;color:#b45309;font-size:12px">TOTAL</td>
              <td style="padding:10px 12px;text-align:center;font-weight:800;color:var(--sl800);font-size:14px"><?php echo array_sum(array_column($staff_rows,'total_staff')); ?></td>
              <td style="padding:10px 12px;text-align:center;font-weight:700;color:var(--g700)"><?php echo array_sum(array_column($staff_rows,'cashiers')); ?></td>
              <td style="padding:10px 12px;text-align:center;font-weight:700;color:#15803d"><?php echo array_sum(array_column($staff_rows,'supervisors')); ?></td>
              <td style="padding:10px 12px;text-align:center;font-weight:700;color:#b45309"><?php echo array_sum(array_column($staff_rows,'managers')); ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>

  </div>
 </div>
</div>

<!-- ══ Branch Modal ════════════════════════════════════════════════════════ -->
<div class="modal fade" id="branchModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="<?php echo admin_url('pos_system/branch_save'); ?>" id="branchForm">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="modal-content">
        <div class="modal-header" style="background:var(--g700);border-radius:4px 4px 0 0">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
          <h4 class="modal-title" id="branchModalTitle" style="color:#fff;font-weight:700">
            <i class="fa fa-building" style="margin-right:8px"></i><?php echo _l('pos_add_branch'); ?>
          </h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="branch_id_field" value="">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label><?php echo _l('pos_branch_name'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="name" id="branch_name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo _l('pos_branch_code'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="code" id="branch_code" class="form-control" required placeholder="NBI-01" style="text-transform:uppercase">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label><?php echo _l('pos_address'); ?></label>
            <input type="text" name="address" id="branch_address" class="form-control">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_city'); ?></label>
                <input type="text" name="city" id="branch_city" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_country'); ?> <span class="text-danger">*</span></label>
                <select name="country" id="branch_country" class="form-control" onchange="updateTaxLabel(this.value)">
                  <option value="">— Select Country —</option>
                  <option value="Kenya">Kenya</option>
                  <option value="Tanzania">Tanzania</option>
                  <option value="Uganda">Uganda</option>
                  <option value="Rwanda">Rwanda</option>
                  <option value="Ethiopia">Ethiopia</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_phone'); ?></label>
                <input type="text" name="phone" id="branch_phone" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_email'); ?></label>
                <input type="email" name="email" id="branch_email" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_currency'); ?></label>
                <select name="currency" id="branch_currency" class="form-control">
                  <option value="KES">KES — Kenyan Shilling</option>
                  <option value="UGX">UGX — Ugandan Shilling</option>
                  <option value="TZS">TZS — Tanzanian Shilling</option>
                  <option value="RWF">RWF — Rwandan Franc</option>
                  <option value="ETB">ETB — Ethiopian Birr</option>
                  <option value="USD">USD — US Dollar</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_timezone'); ?></label>
                <select name="timezone" id="branch_timezone" class="form-control">
                  <option value="Africa/Nairobi">Africa/Nairobi (EAT +3)</option>
                  <option value="Africa/Kampala">Africa/Kampala (EAT +3)</option>
                  <option value="Africa/Dar_es_Salaam">Africa/Dar_es_Salaam (EAT +3)</option>
                  <option value="Africa/Kigali">Africa/Kigali (CAT +2)</option>
                  <option value="Africa/Addis_Ababa">Africa/Addis_Ababa (EAT +3)</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label id="tax_pin_label"><?php echo _l('pos_tax_pin'); ?></label>
            <input type="text" name="tax_pin" id="branch_tax_pin" class="form-control" placeholder="e.g. P051234567X">
            <small class="text-muted" id="tax_pin_help"><?php echo _l('pos_tax_pin_help'); ?></small>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_receipt_header'); ?></label>
                <textarea name="receipt_header" id="branch_receipt_header" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_receipt_footer'); ?></label>
                <textarea name="receipt_footer" id="branch_receipt_footer" class="form-control" rows="2"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-primary" style="background:var(--g600);border-color:var(--g700)">
            <i class="fa fa-save"></i> <?php echo _l('submit'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
var TAX_LABELS = {
  'Kenya':    { label: 'KRA PIN',   help: 'Kenya Revenue Authority Personal Identification Number' },
  'Tanzania': { label: 'TRA TIN',   help: 'Tanzania Revenue Authority Taxpayer Identification Number' },
  'Uganda':   { label: 'URA TIN',   help: 'Uganda Revenue Authority Taxpayer Identification Number' },
  'Rwanda':   { label: 'RRA TIN',   help: 'Rwanda Revenue Authority Taxpayer Identification Number' },
  'Ethiopia': { label: 'ERCA TIN',  help: 'Ethiopian Revenue and Customs Authority Taxpayer Identification Number' },
  'Other':    { label: 'Tax ID',    help: 'Tax identification number issued by your country\'s revenue authority' }
};

function updateTaxLabel(country) {
  var info = TAX_LABELS[country] || { label: 'TAX PIN / TIN', help: 'Tax identification number' };
  document.getElementById('tax_pin_label').textContent = info.label;
  document.getElementById('tax_pin_help').textContent  = info.help;
}

function resetBranchForm() {
  document.getElementById('branchModalTitle').innerHTML =
    '<i class="fa fa-building" style="margin-right:8px"></i><?php echo _l('pos_add_branch'); ?>';
  document.getElementById('branch_id_field').value = '';
  document.getElementById('branchForm').reset();
  updateTaxLabel('Kenya');
  document.getElementById('branch_country').value = 'Kenya';
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.edit-branch').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var b = JSON.parse(this.dataset.branch);
      document.getElementById('branchModalTitle').innerHTML =
        '<i class="fa fa-pencil" style="margin-right:8px"></i><?php echo _l('pos_edit_branch'); ?>';
      document.getElementById('branch_id_field').value       = b.id;
      document.getElementById('branch_name').value           = b.name || '';
      document.getElementById('branch_code').value           = b.code || '';
      document.getElementById('branch_address').value        = b.address || '';
      document.getElementById('branch_city').value           = b.city || '';
      document.getElementById('branch_country').value        = b.country || '';
      document.getElementById('branch_phone').value          = b.phone || '';
      document.getElementById('branch_email').value          = b.email || '';
      document.getElementById('branch_currency').value       = b.currency || 'KES';
      document.getElementById('branch_timezone').value       = b.timezone || 'Africa/Nairobi';
      document.getElementById('branch_tax_pin').value        = b.tax_pin || '';
      document.getElementById('branch_receipt_header').value = b.receipt_header || '';
      document.getElementById('branch_receipt_footer').value = b.receipt_footer || '';
      updateTaxLabel(b.country || 'Kenya');
      jQuery('#branchModal').modal('show');
    });
  });

  updateTaxLabel('Kenya');
});
</script>

<!-- ── Branch Staff Management Modal ─────────────────────────────────────── -->
<div class="modal fade" id="branchStaffModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#f8f9fc;border-bottom:1px solid #e5e9f2;padding:14px 20px">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="bsModalTitle" style="font-size:16px;font-weight:700">
          <i class="fa fa-users" style="margin-right:8px;color:#3d5af1"></i>
          Branch Staff — <span id="bsModalBranchName"></span>
        </h4>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="margin-bottom:14px;font-size:13px">
          Assign staff to this branch and set their POS role. Staff only appear in the POS menu if they have a branch assignment.
          <strong>Roles:</strong> Cashier (terminal only), Supervisor (+ reports), Manager (+ products/inventory/profiles).
        </p>

        <!-- Add staff row -->
        <div class="row" style="margin-bottom:16px;background:#f8f9fc;padding:12px 10px;border-radius:8px;border:1px solid #e5e9f2">
          <div class="col-md-5">
            <select id="bsStaffSelect" class="form-control form-control-sm">
              <option value="">— Select Staff Member —</option>
              <?php
              $CI = &get_instance();
              $all_staff = $CI->db->select('staffid as id, firstname, lastname, email')
                                  ->where('active', 1)->order_by('firstname','ASC')
                                  ->get(db_prefix() . 'staff')->result_array();
              foreach ($all_staff as $s):
              ?>
              <option value="<?php echo $s['id']; ?>">
                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?> &lt;<?php echo htmlspecialchars($s['email']); ?>&gt;
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select id="bsRoleSelect" class="form-control form-control-sm">
              <option value="cashier">Cashier (Terminal only)</option>
              <option value="supervisor">Supervisor (+ Reports)</option>
              <option value="manager">Manager (+ Products)</option>
            </select>
          </div>
          <div class="col-md-2">
            <div class="checkbox" style="margin:6px 0 0">
              <label style="font-size:12px"><input type="checkbox" id="bsIsDefault"> Set as Default Branch</label>
            </div>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-success btn-block btn-sm" id="bsAddBtn">
              <i class="fa fa-plus"></i> Assign
            </button>
          </div>
        </div>

        <!-- Staff table -->
        <table class="table table-bordered table-condensed" id="bsStaffTable" style="font-size:13px">
          <thead>
            <tr style="background:#f8f9fc">
              <th style="width:40px">#</th>
              <th>Staff Member</th>
              <th style="width:130px">POS Role</th>
              <th style="width:90px;text-align:center">Default</th>
              <th style="width:70px;text-align:center">Remove</th>
            </tr>
          </thead>
          <tbody id="bsStaffTbody">
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px">Loading…</td></tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer" style="background:#f8f9fc;border-top:1px solid #e5e9f2">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var CSRF_NAME    = '<?php echo $CI->security->get_csrf_token_name(); ?>';
  var CSRF_HASH    = '<?php echo $CI->security->get_csrf_hash(); ?>';
  var BASE_URL     = '<?php echo rtrim(admin_url('pos_system'), '/'); ?>';
  var currentBranchId = null;

  var ROLE_LABELS = {
    cashier:    '<span class="label label-default">Cashier</span>',
    supervisor: '<span class="label label-info">Supervisor</span>',
    manager:    '<span class="label label-primary">Manager</span>',
    admin:      '<span class="label label-danger">Admin</span>',
  };

  // Open modal
  document.querySelectorAll('.manage-staff-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      currentBranchId = parseInt(this.dataset.branchId, 10);
      document.getElementById('bsModalBranchName').textContent = this.dataset.branchName;
      document.getElementById('bsStaffSelect').value = '';
      document.getElementById('bsRoleSelect').value  = 'cashier';
      document.getElementById('bsIsDefault').checked = false;
      renderTable([]);
      loadStaff();
      jQuery('#branchStaffModal').modal('show');
    });
  });

  function loadStaff() {
    post(BASE_URL + '/branch_staff_get', {branch_id: currentBranchId}, function(res) {
      renderTable(res.staff || []);
    });
  }

  function renderTable(staff) {
    var tbody = document.getElementById('bsStaffTbody');
    if (!staff || staff.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding:20px">No staff assigned to this branch yet</td></tr>';
      return;
    }
    var rows = staff.map(function(s, i) {
      var name = ((s.firstname || '') + ' ' + (s.lastname || '')).trim();
      var roleLabel = ROLE_LABELS[s.role] || s.role;
      var defBtn = s.is_default
        ? '<span class="label label-success">Default</span>'
        : '<button type="button" class="btn btn-xs btn-default" onclick="setDefault(' + s.id + ')" title="Set as default">Set</button>';
      return '<tr>' +
        '<td>' + (i+1) + '</td>' +
        '<td><strong>' + esc(name) + '</strong><br><small class="text-muted">' + esc(s.email||'') + '</small></td>' +
        '<td>' +
          '<select class="form-control input-sm" onchange="changeRole(' + s.id + ', this.value)">' +
            '<option value="cashier"' + (s.role==='cashier'?' selected':'') + '>Cashier</option>' +
            '<option value="supervisor"' + (s.role==='supervisor'?' selected':'') + '>Supervisor</option>' +
            '<option value="manager"' + (s.role==='manager'?' selected':'') + '>Manager</option>' +
          '</select>' +
        '</td>' +
        '<td style="text-align:center">' + defBtn + '</td>' +
        '<td style="text-align:center">' +
          '<button type="button" class="btn btn-xs btn-danger" onclick="removeStaff(' + s.id + ')" title="Remove">' +
            '<i class="fa fa-times"></i></button>' +
        '</td>' +
        '</tr>';
    });
    tbody.innerHTML = rows.join('');
  }

  document.getElementById('bsAddBtn').addEventListener('click', function() {
    var staffId = parseInt(document.getElementById('bsStaffSelect').value, 10);
    var role    = document.getElementById('bsRoleSelect').value;
    var isDef   = document.getElementById('bsIsDefault').checked;
    if (!staffId) { alert('Please select a staff member.'); return; }
    post(BASE_URL + '/branch_staff_save', {
      branch_id: currentBranchId, staff_id: staffId, role: role, is_default: isDef ? 1 : 0
    }, function(res) {
      if (res.success) {
        renderTable(res.staff);
        document.getElementById('bsStaffSelect').value = '';
        document.getElementById('bsIsDefault').checked = false;
        // Update staff count on the card
        updateCardStat(currentBranchId, res.staff.length);
      } else { alert(res.error || 'Failed to assign.'); }
    });
  });

  window.removeStaff = function(staffId) {
    if (!confirm('Remove this staff member from the branch?')) return;
    post(BASE_URL + '/branch_staff_remove', {
      branch_id: currentBranchId, staff_id: staffId
    }, function(res) {
      if (res.success) { renderTable(res.staff); updateCardStat(currentBranchId, res.staff.length); }
    });
  };

  window.changeRole = function(staffId, role) {
    post(BASE_URL + '/branch_staff_save', {
      branch_id: currentBranchId, staff_id: staffId, role: role, is_default: 0
    }, function(res) {
      if (!res.success) { alert(res.error || 'Failed to update role.'); loadStaff(); }
    });
  };

  window.setDefault = function(staffId) {
    post(BASE_URL + '/branch_staff_save', {
      branch_id: currentBranchId, staff_id: staffId, role: null, is_default: 1
    }, function(res) {
      if (res.success) renderTable(res.staff);
    });
  };

  function updateCardStat(branchId, count) {
    // Update the staff count shown on the branch card without reload
    document.querySelectorAll('.manage-staff-btn').forEach(function(btn) {
      if (parseInt(btn.dataset.branchId, 10) === branchId) {
        var card = btn.closest('.mb-card');
        if (card) {
          var cv = card.querySelector('.mb-cs .cv');
          if (cv) cv.textContent = count;
        }
      }
    });
  }

  function post(url, data, cb) {
    data[CSRF_NAME] = CSRF_HASH;
    jQuery.post(url, data, cb, 'json').fail(function(xhr) {
      console.error(xhr.responseText);
      alert('Request failed.');
    });
  }

  function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
})();
</script>

<?php init_tail(); ?>
