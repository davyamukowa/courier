<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<!-- Xetuu Books layout -->
<style>
/* ── Overrides ──────────────────────────────────────────── */
#wrapper { min-height: 100%; }
.content { padding: 0 !important; }

/* ── Accounting Nav ──────────────────────────────────────── */
.acc-nav { display: flex; align-items: center; background: #1a6b3a; border-bottom: 1px solid #155730; padding: 0 20px; height: 60px; font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", sans-serif; position: relative; z-index: 1030; }

/* Brand */
.acc-nav-brand { display: flex; align-items: center; gap: 9px; text-decoration: none !important; color: #fff !important; font-weight: 700; font-size: 17px; letter-spacing: -0.3px; white-space: nowrap; flex-shrink: 0; }
.acc-nav-brand:hover { color: #fff !important; text-decoration: none !important; }
.acc-nav-brand svg { width: 22px; height: 22px; fill: currentColor; flex-shrink: 0; }
.acc-nav-separator { width: 1px; height: 28px; background: rgba(255,255,255,.22); margin: 0 14px; flex-shrink: 0; }

/* Nav items row */
.acc-nav-items { display: flex; align-items: center; flex: 1; height: 100%; min-width: 0; }

/* Individual nav item (dropdown trigger) */
.acc-nav-item { position: relative; height: 100%; display: flex; align-items: center; cursor: pointer; }
.acc-nav-item > a,
.acc-nav-label { color: rgba(220,252,231,.9); text-decoration: none !important; font-weight: 500; font-size: 14px; padding: 8px 13px; border-radius: 6px; display: flex; align-items: center; gap: 4px; transition: background .18s, color .18s; white-space: nowrap; user-select: none; }
/* Click state only — no hover dropdown */
.acc-nav-item.open > a,
.acc-nav-item.open .acc-nav-label { background: rgba(0,0,0,.18); color: #fff; }
/* Subtle hover on label only — does NOT open dropdown */
.acc-nav-item:not(.open) > a:hover,
.acc-nav-item:not(.open) .acc-nav-label:hover { background: rgba(0,0,0,.12); color: #fff; }

/* Caret */
.acc-nav-caret { width: 16px; height: 16px; fill: currentColor; opacity: .75; transition: transform .2s; flex-shrink: 0; }
.acc-nav-item.open .acc-nav-caret { transform: rotate(180deg); opacity: 1; }

/* Dropdown panel — hidden by default, shown only via .open class (JS click) */
.acc-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 24px 40px -8px rgba(0,0,0,.16), 0 8px 16px -4px rgba(0,0,0,.08);
    min-width: 260px;
    padding: 10px 0;
    z-index: 1040;
    animation: accDropIn .18s ease;
}
.acc-nav-item.open .acc-dropdown { display: block; }
@keyframes accDropIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

/* Dropdown group label */
.acc-dropdown-group-label { display: block; padding: 10px 18px 4px; font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: #9ca3af; letter-spacing: .06em; }

/* Dropdown items — with icon tile */
.acc-dropdown-item { display: flex; align-items: center; padding: 8px 16px; color: #374151 !important; text-decoration: none !important; font-size: 13.5px; font-weight: 500; transition: background .12s; border-radius: 0; }
.acc-dropdown-item:hover { background: #f0fdf4; color: #15803d !important; text-decoration: none !important; }

/* Icon tile (colored rounded square) */
.acc-item-icon { width: 34px; height: 34px; border-radius: 9px; background: #f0fdf4; fill: #16a34a; padding: 7px; box-sizing: border-box; margin-right: 13px; flex-shrink: 0; transition: background .12s, fill .12s; }
.acc-dropdown-item:hover .acc-item-icon { background: #dcfce7; fill: #15803d; }

/* Separator */
.acc-dropdown-separator { height: 1px; background: #f3f4f6; margin: 6px 0; }

/* ── Two-panel split dropdown ───────────────────────────────────────────── */
.acc-dropdown-split { padding: 0 !important; min-width: 500px; overflow: hidden; }
.acc-nav-item.open .acc-dropdown-split { display: flex !important; }
.acc-split-left { background: #f8faf8; padding: 12px; min-width: 230px; border-right: 1px solid #efefef; }
.acc-split-right { padding: 18px 20px; flex: 1; min-width: 190px; }
.acc-split-item { display: flex; align-items: center; gap: 13px; padding: 9px 10px; border-radius: 10px; text-decoration: none !important; color: #111827 !important; font-size: 14px; font-weight: 600; transition: background .15s; margin-bottom: 1px; }
.acc-split-item:hover { background: rgba(0,0,0,.05); color: #111827 !important; text-decoration: none !important; }
.acc-tile { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.acc-tile svg { width: 17px; height: 17px; fill: white; }
.acc-tile-green  { background: #16a34a; }
.acc-tile-blue   { background: #2563eb; }
.acc-tile-purple { background: #7c3aed; }
.acc-tile-orange { background: #ea580c; }
.acc-tile-red    { background: #dc2626; }
.acc-tile-teal   { background: #0d9488; }
.acc-tile-indigo { background: #4f46e5; }
.acc-tile-cyan   { background: #0891b2; }
.acc-tile-amber  { background: #d97706; }
.acc-split-section + .acc-split-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6; }
.acc-split-section-header { font-size: 10.5px; font-weight: 700; color: #1a6b3a; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 8px; }
.acc-split-link { display: flex; align-items: center; gap: 9px; padding: 7px 0; color: #374151 !important; font-size: 13.5px; font-weight: 500; text-decoration: none !important; transition: color .12s; line-height: 1.3; }
.acc-split-link:hover { color: #1a6b3a !important; text-decoration: none !important; }
.acc-split-link svg { width: 15px; height: 15px; fill: #9ca3af; flex-shrink: 0; transition: fill .12s; }
.acc-split-link:hover svg { fill: #1a6b3a; }

/* Plain nav link (Dashboard — no dropdown) */
.acc-nav-plain { color: rgba(220,252,231,.9) !important; text-decoration: none !important; font-weight: 500; font-size: 14px; padding: 8px 13px; border-radius: 6px; display: flex; align-items: center; transition: background .18s, color .18s; white-space: nowrap; }
.acc-nav-plain:hover { background: rgba(0,0,0,.12); color: #fff !important; text-decoration: none !important; }

/* Dropdowns hidden by default — !important + pointer-events block all hover rules */
.acc-dropdown { display: none !important; pointer-events: none !important; }
.acc-nav-item.open .acc-dropdown { display: block !important; pointer-events: auto !important; }
.acc-nav-item.open .acc-dropdown-split { display: flex !important; }

/* Three-column dropdown */
.acc-dropdown-3col { right: 0 !important; left: auto !important; min-width: 720px; }
.acc-nav-item.open .acc-dropdown-3col { display: flex !important; }
/* Vendor 4-column mega dropdown */
.acc-4col-mega { padding: 0 !important; min-width: 880px; overflow: hidden; }
.acc-nav-item.open .acc-4col-mega { display: flex !important; }

/* Each column */
.acc-mega-col { padding: 12px 10px 14px; flex: 1; min-width: 200px; border-right: 1px solid rgba(0,0,0,.07); }
.acc-mega-col:last-child { border-right: none; }

/* Column colour themes */
.acc-mega-col--accounting { background: #f0f7f0; } /* light green — existing accounting ops */
.acc-mega-col--purchase   { background: #eff6ff; } /* light blue  — purchase workflow */
.acc-mega-col--master     { background: #fafaf9; } /* off-white   — master data */

/* Column label (replaces acc-3col-header) */
.acc-mega-col-label {
  font-size: 10px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
  padding: 3px 10px 8px; margin-bottom: 4px; border-bottom: 1px solid rgba(0,0,0,.08);
}
.acc-mega-col--accounting .acc-mega-col-label { color: #166534; }
.acc-mega-col--purchase   .acc-mega-col-label { color: #1e40af; }
.acc-mega-col--master     .acc-mega-col-label { color: #4b5563; }

/* Legacy 3-col (kept for backward compat) */
.acc-3col-mega { padding: 0 !important; min-width: 760px; overflow: hidden; }
.acc-nav-item.open .acc-3col-mega { display: flex !important; }
.acc-3col-col { background: #f8faf8; padding: 12px 10px; flex: 1; border-right: 1px solid #efefef; min-width: 210px; }
.acc-3col-col:last-child { border-right: none; }
.acc-3col-header { font-size: 10.5px; font-weight: 700; color: #1a6b3a; text-transform: uppercase; letter-spacing: .08em; padding: 4px 10px 10px; margin-bottom: 4px; border-bottom: 1px solid #e5e7eb; }

/* New Invoice quick-action button */
.acc-new-btn { display: flex; align-items: center; gap: 6px; margin-left: auto; flex-shrink: 0; padding: 8px 16px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.32); border-radius: 7px; color: #fff !important; font-size: 13px; font-weight: 600; text-decoration: none !important; white-space: nowrap; transition: background .18s, border-color .18s; }
.acc-new-btn:hover { background: rgba(255,255,255,.25); border-color: rgba(255,255,255,.55); color: #fff !important; text-decoration: none !important; }

/* ── Page chrome ─────────────────────────────────────────── */
.xb-page-header { background: white; padding: 14px 30px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.xb-page-title { font-size: 19px; font-weight: 700; color: #111827; margin: 0; }
.xb-content-wrapper { padding: 16px 20px; }
.xb-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); border: 1px solid #e5e7eb; margin-bottom: 20px; }
.xb-card-header { padding: 14px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151; }
.xb-card-body { padding: 20px; }

/* ── Shared Report Chrome (.xb-rpt context) ─────────────────── */
.xb-rpt .xb-card { box-shadow: 0 2px 8px rgba(0,0,0,.08); border-radius: 10px; }

/* Header bar */
.xb-rpt .xb-card-header {
    padding: 20px 28px !important;
    min-height: 72px;
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    border-bottom: 2px solid #e5e7eb;
    gap: 16px;
    flex-wrap: wrap;
}

/* Header left block (title + period) */
.xb-rpt .xb-card-header > div:first-child {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
/* Title span */
.xb-rpt .xb-card-header > div:first-child > span:first-child {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #111827 !important;
    line-height: 1.2;
}
/* Period span — style as pill badge */
.xb-rpt .xb-card-header > div:first-child > span:last-child {
    display: inline-flex !important;
    align-items: center;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8 !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    padding: 3px 12px !important;
    border-radius: 20px;
    white-space: nowrap;
    letter-spacing: .02em;
    margin-left: 0 !important;
}

/* Action buttons (the .no-print div in the header) */
.xb-rpt .xb-card-header .no-print {
    display: flex !important;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.xb-rpt .xb-card-header .no-print .btn {
    padding: 8px 16px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    height: 38px;
    white-space: nowrap;
}

/* Card body */
.xb-rpt .xb-card-body { padding: 24px 28px !important; }

/* Filter form — works for all reports that use form.form-inline */
.xb-rpt form.form-inline,
.xb-rpt .xb-gl-filters form.form-inline {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 14px 20px !important;
    margin-bottom: 22px !important;
    flex-wrap: wrap;
}
.xb-rpt form.form-inline .form-group,
.xb-rpt .xb-gl-filters .form-group {
    margin: 0 !important;
    display: flex;
    align-items: center;
    gap: 8px;
}
.xb-rpt form.form-inline label,
.xb-rpt .xb-gl-filters label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #374151 !important;
    margin: 0 !important;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.xb-rpt form.form-inline .form-control,
.xb-rpt .xb-gl-filters .form-control {
    height: 36px !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 5px !important;
    background: #fff !important;
    min-width: 130px;
}
.xb-rpt form.form-inline .btn,
.xb-rpt .xb-gl-filters .btn {
    height: 36px !important;
    padding: 6px 18px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    border-radius: 5px !important;
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    margin-left: 0 !important;
}
.xb-rpt form.form-inline select.form-control { min-width: 200px; }

/* ── Global report table breathing room ──────────────────────────────── */
/* Covers profit_loss, balance_sheet, trial_balance, general_ledger, etc. */
.xb-rpt td, .xb-rpt th {
    padding-top: 9px !important;
    padding-bottom: 9px !important;
}
/* Keep left/right padding from each report's own rules */

/* Hint / scroll hint text */
.xb-rpt .xb-gl-hint {
    font-size: 11px;
    color: #9ca3af;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ── KPI cards (shared by reports + list pages) ─────────────────────── */
.xb-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
@media (min-width: 1280px) { .xb-kpi-grid { grid-template-columns: repeat(6, 1fr); } }
@media (max-width: 767px)  { .xb-kpi-grid { grid-template-columns: repeat(2, 1fr); } }

.xb-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0 12px;
    gap: 8px;
}
.xb-action-bar .btn { margin-left: 6px; }

/* ── Purchase / Vendor Form Page Layout ──────────────────────── */
.xb-form-page { margin: -16px -20px; }
.xb-form-header {
    background: #fff; padding: 12px 24px; border-bottom: 1px solid #e5e7eb;
    display: flex; justify-content: space-between; align-items: center; gap: 12px;
}
.xb-form-header-left { display: flex; align-items: center; gap: 10px; }
.xb-form-breadcrumb { font-size: 12px; color: #6b7280; margin: 0 0 2px; }
.xb-form-breadcrumb a { color: #6b7280; text-decoration: none; }
.xb-form-breadcrumb a:hover { color: #1a6b3a; text-decoration: underline; }
.xb-form-breadcrumb span { margin: 0 4px; }
.xb-form-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
.xb-form-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
.xb-save-btn { background: #1a6b3a; color: #fff; border: none; border-radius: 6px; padding: 9px 20px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.xb-save-btn:hover { background: #155a30; color: #fff; }
.xb-save-btn:disabled { opacity: .6; cursor: not-allowed; }

/* Stage bar */
.xb-stage-bar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; }
.xb-stages { display: flex; align-items: center; gap: 0; }
.xb-stage { display: flex; align-items: center; flex: 1; }
.xb-stage-dot {
    width: 34px; height: 34px; border-radius: 50%; border: 2px solid #d1d5db;
    display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;
    color: #9ca3af; background: #fff; flex-shrink: 0; position: relative; z-index: 1;
}
.xb-stage.active .xb-stage-dot { border-color: #1a6b3a; background: #1a6b3a; color: #fff; }
.xb-stage.done .xb-stage-dot { border-color: #1a6b3a; background: #dcfce7; color: #1a6b3a; }
.xb-stage-line { flex: 1; height: 2px; background: #e5e7eb; }
.xb-stage.done .xb-stage-line, .xb-stage.active .xb-stage-line { background: #1a6b3a; }
.xb-stage-label { font-size: 11px; font-weight: 600; color: #9ca3af; margin-top: 4px; white-space: nowrap; text-align: center; }
.xb-stage.active .xb-stage-label { color: #1a6b3a; }
.xb-stage.done .xb-stage-label { color: #16a34a; }
.xb-stage-wrap { display: flex; flex-direction: column; align-items: center; flex: 1; }

/* Main body: left form + right sidebar */
.xb-form-body { display: flex; gap: 0; align-items: flex-start; min-height: calc(100vh - 140px); }
.xb-form-main { flex: 1; padding: 20px 24px; min-width: 0; }
.xb-form-sidebar { width: 290px; flex-shrink: 0; padding: 16px; background: #f9fafb; border-left: 1px solid #e5e7eb; min-height: calc(100vh - 140px); }

/* Sidebar boxes */
.xb-sb-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 14px; overflow: hidden; }
.xb-sb-box-header { font-size: 10px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: .1em; padding: 10px 14px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
.xb-sb-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 14px; border-bottom: 1px solid #f3f4f6; gap: 8px; }
.xb-sb-row:last-child { border-bottom: none; }
.xb-sb-label { font-size: 11.5px; color: #6b7280; flex-shrink: 0; padding-top: 1px; }
.xb-sb-value { font-size: 12.5px; font-weight: 600; color: #111827; text-align: right; }
.xb-sb-nav-link { display: flex; align-items: center; gap: 8px; padding: 9px 14px; font-size: 13px; color: #374151; text-decoration: none; border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.xb-sb-nav-link:last-child { border-bottom: none; }
.xb-sb-nav-link:hover { background: #f0fdf4; color: #1a6b3a; text-decoration: none; }
.xb-sb-nav-link.active { color: #1a6b3a; font-weight: 600; background: #f0fdf4; }
.xb-sb-nav-link svg { width: 15px; height: 15px; fill: currentColor; flex-shrink: 0; opacity: .7; }

/* Form section cards */
.xb-fcard { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 18px; }
.xb-fcard-header { padding: 11px 18px; border-bottom: 1px solid #e5e7eb; font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .05em; background: #f9fafb; border-radius: 8px 8px 0 0; }
.xb-fcard-body { padding: 18px; }
.xb-fcard-body .form-group { margin-bottom: 14px; }
.xb-fcard-body .form-group:last-child { margin-bottom: 0; }
.xb-flabel { font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block; }
.xb-flabel .req { color: #dc2626; }

/* Tabs inside form */
.xb-form-tabs { border-bottom: 2px solid #e5e7eb; margin-bottom: 18px; }
.xb-form-tabs .nav-tabs { border: none; }
.xb-form-tabs .nav-tabs > li > a { border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; color: #6b7280; font-weight: 500; font-size: 14px; padding: 10px 16px; border-radius: 0; }
.xb-form-tabs .nav-tabs > li.active > a,
.xb-form-tabs .nav-tabs > li.active > a:focus,
.xb-form-tabs .nav-tabs > li.active > a:hover { color: #1a6b3a; border-bottom-color: #1a6b3a; background: transparent; }

/* Line items table */
.xb-lineitems { width: 100%; border-collapse: collapse; font-size: 13px; }
.xb-lineitems th { background: #f3f4f6; padding: 8px 10px; font-weight: 600; color: #374151; font-size: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.xb-lineitems td { padding: 6px 6px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
.xb-lineitems tbody tr:last-child td { border-bottom: none; }
.xb-lineitems .form-control { height: 33px; padding: 4px 8px; font-size: 13px; }
.xb-li-add { margin-top: 8px; }
.xb-totals { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; margin-top: 14px; padding-right: 6px; }
.xb-total-row { display: flex; gap: 12px; justify-content: flex-end; align-items: center; font-size: 13px; }
.xb-total-row label { color: #6b7280; min-width: 100px; text-align: right; }
.xb-total-row span { font-weight: 600; min-width: 110px; text-align: right; }
.xb-total-row.grand { font-size: 15px; border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 4px; }
.xb-total-row.grand span { color: #1a6b3a; }
.xb-status-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.xb-status-badge.draft { background: #f3f4f6; color: #6b7280; }
.xb-status-badge.pending { background: #fef3c7; color: #92400e; }
.xb-status-badge.approved { background: #dcfce7; color: #15803d; }
.xb-status-badge.paid { background: #dbeafe; color: #1d4ed8; }
.xb-status-badge.signed { background: #d1fae5; color: #065f46; }

.xb-kpi-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 14px 10px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.xb-kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); transform: translateY(-2px); }
.xb-kpi-card::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
    border-radius: 10px 0 0 10px; background: #16a34a;
}
.xb-kpi-card.warn::before   { background: #d97706; }
.xb-kpi-card.danger::before { background: #dc2626; }
.xb-kpi-card.blue::before   { background: #2563eb; }
.xb-kpi-card.purple::before { background: #7c3aed; }

.kpi-currency { font-size: 9.5px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #9ca3af; margin-bottom: 2px; }
.kpi-icon {
    position: absolute; top: 10px; right: 10px; width: 26px; height: 26px;
    border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px;
}
.xb-kpi-card        .kpi-icon { background: #f0fdf4; color: #16a34a; }
.xb-kpi-card.warn   .kpi-icon { background: #fffbeb; color: #d97706; }
.xb-kpi-card.danger .kpi-icon { background: #fef2f2; color: #dc2626; }
.xb-kpi-card.blue   .kpi-icon { background: #eff6ff; color: #2563eb; }
.xb-kpi-card.purple .kpi-icon { background: #f5f3ff; color: #7c3aed; }

.kpi-value {
    font-size: 15px; font-weight: 800; color: #16a34a;
    line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 4px; padding-right: 30px;
}
.xb-kpi-card.warn   .kpi-value { color: #d97706; }
.xb-kpi-card.danger .kpi-value { color: #dc2626; }
.xb-kpi-card.blue   .kpi-value { color: #2563eb; }
.xb-kpi-card.purple .kpi-value { color: #7c3aed; }

.kpi-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }

/* ── Exec breakdown tables (shared) ─────────────────────────────────── */
.xb-exec-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.xb-exec-tbl th { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; padding: 10px 14px; border-bottom: 2px solid #e5e7eb; }
.xb-exec-tbl td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; }
.xb-exec-tbl tr:last-child td { border-bottom: none; }
.xb-exec-tbl .total-row td { font-weight: 700; background: #f0fdf4; color: #111827; font-size: 13.5px; border-top: 2px solid #d1fae5; }
.xb-exec-tbl .total-row.warn td { background: #fef9ec; border-top-color: #fde68a; }

/* ── Collapsible section (shared) ────────────────────────────────────── */
.xb-collapsible-section { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
.xb-section-header { padding: 12px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 13px; color: #374151; }
.xb-section-body { padding: 12px 16px; }
</style>

<div id="wrapper">
    <div class="content xb-workspace" style="padding: 0; margin-top: 60px;">
        <!-- Top Navigation -->
        <?php $CI = &get_instance(); echo $CI->load->view('xetuu_books/layout/accounting_nav', get_defined_vars(), true); ?>

        <!-- Page Header (list/report/config pages — forms override with their own toolbar) -->
        <?php if (!isset($xb_hide_page_header)): ?>
        <div class="xb-page-header">
            <h1 class="xb-page-title"><?php echo isset($title) ? htmlspecialchars($title) : 'Xetuu Books'; ?></h1>
            <div class="xb-page-actions">
                <?php if (isset($header_actions)) { echo $header_actions; } ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Content Area -->
        <div class="xb-content-wrapper">
            <?php 
            if (isset($xb_content)) {
                echo $xb_content; 
            } elseif (isset($xb_view)) {
                $this->load->view($xb_view);
            }
            ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function () {
    if ($.fn.selectpicker) {
        $('.xb-workspace select:not([data-no-picker])').selectpicker();
    }

    // Single unified click handler — any click that is NOT on a dropdown label closes all menus.
    // This prevents the "hover appears open" bug caused by .open lingering after clicking plain
    // links (Dashboard, page content) that don't go through the label toggle path.
    $(document).on('click', function(e) {
        var $label = $(e.target).closest('.acc-nav-item[data-has-dropdown] .acc-nav-label');

        if ($label.length) {
            // Clicked a dropdown trigger label — toggle that item
            e.stopPropagation();
            var $item   = $label.closest('.acc-nav-item');
            var wasOpen = $item.hasClass('open');
            $('.acc-nav-item').removeClass('open');
            if (!wasOpen) { $item.addClass('open'); }
        } else {
            // Clicked anything else (inside or outside nav, including plain links) — close all
            $('.acc-nav-item').removeClass('open');
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') { $('.acc-nav-item').removeClass('open'); }
    });
});
</script>
