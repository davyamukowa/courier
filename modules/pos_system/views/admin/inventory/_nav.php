<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Shared Odoo-style top navigation for the POS Inventory sub-module.
 * Expects: $inv_section (string) — current active section slug
 *          $inv_branches (array) — all active branches (used as warehouses)
 *          $inv_branch_id (int)  — currently selected branch
 */
$s   = $inv_section  ?? 'overview';
$bid = $inv_branch_id ?? 0;

$_ops  = ['receiving','deliveries','transfers','stocktake','adjustments','returns','packing'];
$_prods= ['items','categories','stock_levels','batches','variations'];
$_rpts = ['report_summary','report_balance','report_movements','report_valuation','history'];
$_cfg  = ['config_units','config_brands','config_suppliers','config_settings','config_attributes'];

$_in_ops   = in_array($s, $_ops);
$_in_prods = in_array($s, $_prods);
$_in_rpts  = in_array($s, $_rpts);
$_in_cfg   = in_array($s, $_cfg);
?>
<style>
/* ═══════════════════════════════════════════════════════════
   INVENTORY — Odoo-style top navigation  (green theme)
   ═══════════════════════════════════════════════════════════ */
:root {
    --inv-bg:      #b8ddc8;
    --inv-bg2:     #a4d0b6;
    --inv-accent:  #16a34a;
    --inv-text:    #1a2520;
    --inv-muted:   #2d4a38;
    --inv-hover:   rgba(0,0,0,.07);
    --inv-active:  rgba(0,0,0,.12);
    --inv-border:  #8ec4a4;
    --inv-drop:    #fff;
}

.inv-nav {
    background: linear-gradient(135deg, #b8ddc8 0%, #9ecfb2 100%);
    margin: 0 -25px 20px -25px;
    position: sticky;
    top: 0;
    z-index: 200;
    box-shadow: 0 2px 12px rgba(13,40,24,.18);
    border-bottom: 2px solid #7dba96;
}
.inv-nav-inner {
    display: flex;
    align-items: stretch;
    min-height: 52px;
}

/* Brand */
.inv-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 22px;
    color: #0d2818;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .3px;
    text-decoration: none !important;
    border-right: 1px solid #7dba96;
    white-space: nowrap;
    background: rgba(0,0,0,.08);
    transition: background .2s;
}
.inv-brand:hover { background: rgba(0,0,0,.14); color: #0d2818; }
.inv-brand .inv-brand-icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--inv-accent), #4ade80);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: #fff;
    box-shadow: 0 2px 8px rgba(22,163,74,.40);
}

/* Menu */
ul.inv-menu {
    list-style: none; margin: 0; padding: 0;
    display: flex; flex-wrap: wrap;
}
ul.inv-menu > li { position: relative; }
ul.inv-menu > li > a {
    display: flex; align-items: center; gap: 7px;
    padding: 0 18px;
    min-height: 52px;
    color: #0d2818;
    font-size: 13.5px; font-weight: 600;
    text-decoration: none !important;
    white-space: nowrap;
    transition: background .15s, color .15s;
    border-bottom: 2px solid transparent;
}
ul.inv-menu > li > a:hover {
    color: #0d2818;
    background: rgba(0,0,0,.07);
    text-decoration: none;
}
ul.inv-menu > li.inv-active > a {
    color: #0d2818;
    background: rgba(0,0,0,.12);
    border-bottom-color: #0d2818;
    font-weight: 700;
}
ul.inv-menu > li > a .inv-caret {
    font-size: 9px;
    opacity: .55;
    margin-left: 2px;
    transition: transform .2s;
}
ul.inv-menu > li.inv-drop-open > a .inv-caret { transform: rotate(180deg); opacity: .9; }

/* Dropdown */
.inv-drop {
    display: none;
    position: absolute;
    top: calc(100% + 1px);
    left: 0;
    background: #fff;
    min-width: 240px;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 10px 30px rgba(13,40,24,.14);
    z-index: 600;
    padding: 8px 0;
    border: 1px solid #c0d9c9;
    border-top: 2px solid var(--inv-accent);
}
ul.inv-menu > li.inv-drop-open .inv-drop { display: block; }
.inv-drop a {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 18px;
    color: #2d3d33;
    font-size: 13px; font-weight: 500;
    text-decoration: none !important;
    transition: background .12s, color .12s;
}
.inv-drop a i { width: 16px; text-align: center; font-size: 12px; color: #16a34a; opacity: .80; }
.inv-drop a:hover, .inv-drop a.inv-on {
    color: #14532d;
    background: #edf7f1;
    text-decoration: none;
}
.inv-drop a.inv-on { font-weight: 700; color: #14532d; }
.inv-sep { border-top: 1px solid #dceae2; margin: 6px 0; }

/* Branch selector */
.inv-branch-wrap {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 16px;
    border-left: 1px solid var(--inv-border);
}
.inv-branch-wrap label { color: #0d2818; font-size: 12px; margin: 0; white-space: nowrap; font-weight: 600; }
.inv-branch-sel {
    background: rgba(255,255,255,.45);
    border: 1px solid #7dba96;
    color: #0d2818;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 13px;
    height: 32px;
    outline: none;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.inv-branch-sel:hover, .inv-branch-sel:focus { background: rgba(255,255,255,.70); border-color: #16a34a; }
.inv-branch-sel option { color: #1a2520; background: #fff; }

/* Page content */
.inv-content { padding: 24px; }

/* ═══ Status badges ════════════════════════════════════════ */
.ibadge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
}
.ibadge-draft      { background: #edf0f4; color: #6b7c93; }
.ibadge-confirmed  { background: #d4edda; color: #155724; }
.ibadge-cancelled  { background: #fce8e8; color: #911b1b; }
.ibadge-delivered  { background: #d1ecf1; color: #0c5460; }
.ibadge-in_transit { background: #fff3cd; color: #856404; }
.ibadge-completed  { background: #d4edda; color: #155724; }
.ibadge-in_progress{ background: #fff3cd; color: #856404; }
.ibadge-packed     { background: #e8d4f9; color: #4a1577; }
.ibadge-dispatched { background: #ccf5f0; color: #0d6b5a; }
.ibadge-loss       { background: #fce8e8; color: #911b1b; }
.ibadge-gain       { background: #d4edda; color: #155724; }
.ibadge-correction { background: #d1ecf1; color: #0c5460; }
.ibadge-active     { background: #dcfce7; color: #14532d; }
.ibadge-inactive   { background: #edf0f4; color: #6b7c93; }

/* ═══ Modern card / table styles ══════════════════════════ */
.inv-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(13,40,24,.08);
    border: 1px solid #e2ece6;
    overflow: hidden;
    margin-bottom: 20px;
}
.inv-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid #edf5f0;
    background: #f8fdf9;
}
.inv-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1a2520;
    margin: 0;
    flex: 1;
}
.inv-card-title i { color: var(--inv-accent); margin-right: 6px; }

/* stat cards */
.inv-stat {
    background: #fff;
    border-radius: 12px;
    padding: 18px 16px;
    box-shadow: 0 2px 12px rgba(13,40,24,.07);
    border: 1px solid #e2ece6;
    text-align: center;
    transition: transform .2s, box-shadow .2s;
}
.inv-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13,40,24,.12); }
.inv-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff;
    margin: 0 auto 10px;
}
.inv-stat-val  { font-size: 26px; font-weight: 800; color: #1a2520; line-height: 1; }
.inv-stat-lbl  { font-size: 11px; color: #4a5e54; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }

/* table */
.inv-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.inv-table thead th {
    padding: 11px 16px;
    font-size: 11px;
    font-weight: 700;
    color: #4a5e54;
    text-transform: uppercase;
    letter-spacing: .6px;
    background: #f0f7f2;
    border-bottom: 1px solid #e2ece6;
    white-space: nowrap;
}
.inv-table tbody td {
    padding: 12px 16px;
    font-size: 13.5px;
    color: #1a2520;
    border-bottom: 1px solid #edf5f0;
    vertical-align: middle;
}
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr { transition: background .12s; }
.inv-table tbody tr:hover { background: #f0faf4; }

/* buttons */
.btn-inv-primary {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff !important;
    border: none;
    border-radius: 7px;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .12s;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 2px 8px rgba(22,163,74,.40);
}
.btn-inv-primary:hover { opacity: .9; transform: translateY(-1px); color: #fff; text-decoration: none; }

.btn-inv-secondary {
    background: #f0faf4;
    color: #166534 !important;
    border: 1px solid #bbf7d0;
    border-radius: 7px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-inv-secondary:hover { background: #dcfce7; color: #14532d; text-decoration: none; }

.btn-inv-icon {
    width: 30px; height: 30px;
    border-radius: 7px;
    border: 1px solid #d1e8d8;
    background: #fff;
    color: #5a8a6a;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: background .12s, color .12s, border-color .12s;
    text-decoration: none !important;
}
.btn-inv-icon:hover { background: #f0fdf4; color: #16a34a; border-color: #86efac; text-decoration: none; }
.btn-inv-icon.danger:hover { background: #fef2f2; color: #ef4444; border-color: #fca5a5; }

/* filter controls */
.inv-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-bottom: 1px solid #edf5f0;
    flex-wrap: wrap;
    background: #fff;
}
.inv-input {
    height: 34px;
    border: 1px solid #c0d9c9;
    border-radius: 7px;
    padding: 0 12px;
    font-size: 13px;
    color: #1a2520;
    background: #f0f7f2;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.inv-input:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.14); background: #fff; }

/* pagination */
.inv-pagination { display: flex; gap: 4px; flex-wrap: wrap; }
.inv-pagination a {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    border-radius: 7px;
    border: 1px solid #c0d9c9;
    font-size: 12px; color: #4a5e54;
    text-decoration: none !important;
    background: #f0f7f2;
    transition: all .12s;
}
.inv-pagination a:hover, .inv-pagination a.active { background: #16a34a; color: #fff; border-color: #16a34a; text-decoration: none; }

/* empty state */
.inv-empty {
    padding: 48px 20px;
    text-align: center;
    color: #56665e;
}
.inv-empty i { font-size: 40px; display: block; margin-bottom: 12px; color: #8ec8a0; }
.inv-empty p { font-size: 14px; margin: 0; font-weight: 500; }

/* Page-level faded green background */
.inv-content { background: #f5fbf7; min-height: 100vh; }

/* ═════════════════════════════════════════════════════════════
   EXCEL-STYLE SPREADSHEET LIST TABLE
   ═════════════════════════════════════════════════════════════ */
.xls-wrap {
    overflow: auto;
    max-height: calc(100vh - 248px);
    background: #fff;
    position: relative;
}
.xls-table {
    border-collapse: separate;
    border-spacing: 0;
    width: max-content;
    min-width: 100%;
    font-size: 12px;
}
.xls-th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #1a3f26;
    color: #c8e8d4;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
    padding: 0;
    border-right: 1px solid rgba(255,255,255,.1);
    border-bottom: 2px solid #16a34a;
    cursor: pointer;
    user-select: none;
    min-width: 90px;
    overflow: visible;
}
.xls-th:hover { background: #255836; }
.xls-th.xls-col-rownum:hover, .xls-th.xls-col-actions:hover { background: #162f1e; }
.xls-th-label {
    display: inline-block;
    padding: 10px 22px 10px 12px;
    pointer-events: none;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
    white-space: nowrap;
}
.xls-sort-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.45;
    font-size: 9px;
    pointer-events: none;
}
.xls-resize-handle {
    position: absolute;
    right: -2px;
    top: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
    z-index: 10;
    border-right: 2px solid transparent;
    box-sizing: border-box;
}
.xls-resize-handle:hover { border-right-color: rgba(255,255,255,0.55); }
.xls-th.xls-col-rownum {
    position: sticky !important;
    left: 0;
    z-index: 4 !important;
    width: 48px !important;
    min-width: 48px !important;
    max-width: 48px !important;
    text-align: center;
    cursor: default;
    color: #7ec89a;
    border-right: 2px solid rgba(255,255,255,.15) !important;
    background: #162f1e !important;
}
.xls-th.xls-col-actions {
    position: sticky !important;
    right: 0;
    z-index: 4 !important;
    width: 108px !important;
    min-width: 108px !important;
    max-width: 108px !important;
    text-align: center;
    cursor: default;
    background: #162f1e !important;
    box-shadow: -4px 0 10px rgba(0,20,8,0.3);
}
.xls-cell {
    padding: 7px 12px;
    border-right: 1px solid #e6ecea;
    border-bottom: 1px solid #e6ecea;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12.5px;
    color: #2a3a2e;
    background: #fff;
    vertical-align: middle;
    max-width: 240px;
}
.xls-cell.xls-col-rownum {
    position: sticky !important;
    left: 0;
    z-index: 1;
    background: #f0f6f2 !important;
    color: #7da08a;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
    border-right: 2px solid #d4e5da !important;
    width: 48px !important;
    min-width: 48px !important;
    max-width: 48px !important;
}
.xls-cell.xls-col-actions {
    position: sticky !important;
    right: 0;
    z-index: 1;
    background: #fff !important;
    white-space: nowrap;
    text-align: center;
    box-shadow: -3px 0 8px rgba(0,0,0,0.07);
    width: 108px !important;
    min-width: 108px !important;
    max-width: 108px !important;
}
.xls-row:nth-child(even) .xls-cell { background: #f6fbf8; }
.xls-row:nth-child(even) .xls-cell.xls-col-rownum { background: #eaf3ed !important; }
.xls-row:nth-child(even) .xls-cell.xls-col-actions { background: #f6fbf8 !important; }
.xls-row:hover .xls-cell { background: #dff2e7 !important; }
.xls-row:hover .xls-cell.xls-col-rownum { background: #d0ebda !important; }
.xls-row:hover .xls-cell.xls-col-actions { background: #dff2e7 !important; }
body.xls-resizing, body.xls-resizing * {
    cursor: col-resize !important;
    user-select: none !important;
    pointer-events: none !important;
}
body.xls-resizing .xls-resize-handle { pointer-events: auto !important; }
.xls-right { text-align: right !important; }
.xls-th.xls-right { text-align: right !important; }
</style>

<nav class="inv-nav">
  <div class="inv-nav-inner">

    <a href="<?php echo admin_url('pos_system/inventory'); ?>" class="inv-brand">
      <div class="inv-brand-icon"><i class="fa fa-boxes"></i></div>
      Inventory
    </a>

    <ul class="inv-menu">

      <!-- Overview -->
      <li class="<?php echo $s === 'overview' ? 'inv-active' : ''; ?>">
        <a href="<?php echo admin_url('pos_system/inventory'); ?>">
          <i class="fa fa-th-large"></i> Overview
        </a>
      </li>

      <!-- Operations -->
      <li class="<?php echo $_in_ops ? 'inv-active' : ''; ?>">
        <a href="#" onclick="return false;">
          <i class="fa fa-tasks"></i> Operations
          <i class="fa fa-chevron-down inv-caret"></i>
        </a>
        <div class="inv-drop">
          <a href="<?php echo admin_url('pos_system/inventory/receiving'); ?>" class="<?php echo $s==='receiving'?'inv-on':''; ?>">
            <i class="fa fa-truck-loading"></i> Receipts
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="<?php echo $s==='deliveries'?'inv-on':''; ?>">
            <i class="fa fa-truck"></i> Deliveries
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/transfers'); ?>" class="<?php echo $s==='transfers'?'inv-on':''; ?>">
            <i class="fa fa-exchange-alt"></i> Internal Transfers
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/stocktake'); ?>" class="<?php echo $s==='stocktake'?'inv-on':''; ?>">
            <i class="fa fa-clipboard-check"></i> Physical Inventory
          </a>
          <div class="inv-sep"></div>
          <a href="<?php echo admin_url('pos_system/inventory/adjustments'); ?>" class="<?php echo $s==='adjustments'?'inv-on':''; ?>">
            <i class="fa fa-sliders-h"></i> Loss &amp; Adjustment
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/returns'); ?>" class="<?php echo $s==='returns'?'inv-on':''; ?>">
            <i class="fa fa-undo-alt"></i> Return Orders
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/packing'); ?>" class="<?php echo $s==='packing'?'inv-on':''; ?>">
            <i class="fa fa-box"></i> Packing Lists
          </a>
        </div>
      </li>

      <!-- Products -->
      <li class="<?php echo $_in_prods ? 'inv-active' : ''; ?>">
        <a href="#" onclick="return false;">
          <i class="fa fa-tag"></i> Products
          <i class="fa fa-chevron-down inv-caret"></i>
        </a>
        <div class="inv-drop">
          <a href="<?php echo admin_url('pos_system/inventory/items'); ?>" class="<?php echo $s==='items'?'inv-on':''; ?>">
            <i class="fa fa-cube"></i> Products
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/categories'); ?>" class="<?php echo $s==='categories'?'inv-on':''; ?>">
            <i class="fa fa-folder-open"></i> Product Categories
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/stock_levels'); ?>" class="<?php echo $s==='stock_levels'?'inv-on':''; ?>">
            <i class="fa fa-warehouse"></i> Current Stock Levels
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/batches'); ?>" class="<?php echo $s==='batches'?'inv-on':''; ?>">
            <i class="fa fa-layer-group"></i> Batch / Serial / LOT Register
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/variations'); ?>" class="<?php echo $s==='variations'?'inv-on':''; ?>">
            <i class="fa fa-th-large"></i> Variations Report
          </a>
        </div>
      </li>

      <!-- Reporting -->
      <li class="<?php echo $_in_rpts ? 'inv-active' : ''; ?>">
        <a href="#" onclick="return false;">
          <i class="fa fa-chart-bar"></i> Reporting
          <i class="fa fa-chevron-down inv-caret"></i>
        </a>
        <div class="inv-drop">
          <a href="<?php echo admin_url('pos_system/inventory/report_summary'); ?>" class="<?php echo $s==='report_summary'?'inv-on':''; ?>">
            <i class="fa fa-chart-pie"></i> Stock Summary
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/report_balance'); ?>" class="<?php echo $s==='report_balance'?'inv-on':''; ?>">
            <i class="fa fa-balance-scale"></i> Stock Balance
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/report_movements'); ?>" class="<?php echo $s==='report_movements'?'inv-on':''; ?>">
            <i class="fa fa-arrows-alt-v"></i> Stock Movements
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/report_valuation'); ?>" class="<?php echo $s==='report_valuation'?'inv-on':''; ?>">
            <i class="fa fa-coins"></i> Inventory Valuation
          </a>
          <div class="inv-sep"></div>
          <a href="<?php echo admin_url('pos_system/inventory/history'); ?>" class="<?php echo $s==='history'?'inv-on':''; ?>">
            <i class="fa fa-history"></i> Activity Log / History
          </a>
        </div>
      </li>

      <!-- Configuration -->
      <li class="<?php echo $_in_cfg ? 'inv-active' : ''; ?>">
        <a href="#" onclick="return false;">
          <i class="fa fa-cog"></i> Configuration
          <i class="fa fa-chevron-down inv-caret"></i>
        </a>
        <div class="inv-drop">
          <a href="<?php echo admin_url('pos_system/branches'); ?>">
            <i class="fa fa-building"></i> Warehouses / Branches
          </a>
          <div class="inv-sep"></div>
          <a href="<?php echo admin_url('pos_system/inventory/config_units'); ?>" class="<?php echo $s==='config_units'?'inv-on':''; ?>">
            <i class="fa fa-ruler"></i> Units of Measure
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/config_brands'); ?>" class="<?php echo $s==='config_brands'?'inv-on':''; ?>">
            <i class="fa fa-trademark"></i> Brands
          </a>
          <a href="<?php echo admin_url('pos_system/inventory/config_suppliers'); ?>" class="<?php echo $s==='config_suppliers'?'inv-on':''; ?>">
            <i class="fa fa-handshake"></i> Suppliers
          </a>
          <div class="inv-sep"></div>
          <a href="<?php echo admin_url('pos_system/inventory/config_settings'); ?>" class="<?php echo $s==='config_settings'?'inv-on':''; ?>">
            <i class="fa fa-sliders-h"></i> Settings
          </a>
          <div class="inv-sep"></div>
          <a href="<?php echo admin_url('pos_system/inventory/config_attributes'); ?>" class="<?php echo $s==='config_attributes'?'inv-on':''; ?>">
            <i class="fa fa-tags"></i> Commodity Types / Colors / Sizes
          </a>
        </div>
      </li>

    </ul>

    <!-- Branch selector (right side) -->
    <?php if (!empty($inv_branches) && count($inv_branches) > 1): ?>
    <div class="inv-branch-wrap">
      <label><i class="fa fa-building"></i></label>
      <form method="get" style="margin:0">
        <select name="branch_id" class="inv-branch-sel" onchange="this.form.submit()">
          <?php foreach ($inv_branches as $b): ?>
            <option value="<?php echo $b['id']; ?>" <?php echo (int)$bid===(int)$b['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($b['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <?php endif; ?>

  </div>
</nav>
<script>
(function(){
  // Click-to-open inventory nav dropdowns; close on outside click
  var menuItems = document.querySelectorAll('.inv-menu > li > a');
  menuItems.forEach(function(a) {
    var li = a.parentElement;
    if (!li.querySelector('.inv-drop')) return; // no dropdown
    a.addEventListener('click', function(e) {
      e.preventDefault();
      var wasOpen = li.classList.contains('inv-drop-open');
      // close all
      document.querySelectorAll('.inv-menu > li.inv-drop-open').forEach(function(el){
        el.classList.remove('inv-drop-open');
      });
      if (!wasOpen) li.classList.add('inv-drop-open');
    });
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.inv-menu')) {
      document.querySelectorAll('.inv-menu > li.inv-drop-open').forEach(function(el){
        el.classList.remove('inv-drop-open');
      });
    }
  });
})();
</script>
