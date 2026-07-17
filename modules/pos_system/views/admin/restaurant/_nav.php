<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Override Bootstrap btn-primary to green for all restaurant pages */
.btn-primary { background-color:#16a34a !important; border-color:#15803d !important; color:#fff !important; }
.btn-primary:hover,.btn-primary:focus { background-color:#15803d !important; border-color:#14532d !important; }
.rest-nav { background:#1e293b; border-bottom:1px solid #334155; padding:0 20px; display:flex; align-items:center; gap:0; overflow-x:auto; }
.rest-nav a { display:flex; align-items:center; gap:7px; padding:13px 16px; font-size:13px; font-weight:500; color:#94a3b8; text-decoration:none; white-space:nowrap; border-bottom:3px solid transparent; transition:all .15s; }
.rest-nav a:hover { color:#e2e8f0; background:rgba(255,255,255,.05); }
.rest-nav a.active { color:#fff; border-bottom-color:#16a34a; }
.rest-nav a i { font-size:13px; }
.rest-nav-brand { font-size:14px; font-weight:700; color:#fff; margin-right:20px; display:flex; align-items:center; gap:8px; flex-shrink:0; }
.rest-nav-brand i { color:#16a34a; }
.rest-nav-spacer { flex:1; }
</style>
<div class="rest-nav">
  <div class="rest-nav-brand"><i class="fa fa-utensils"></i> Restaurant</div>
  <a href="<?php echo admin_url('pos_system/restaurant'); ?>" class="<?php echo $rest_section==='index'?'active':''; ?>"><i class="fa fa-tachometer-alt"></i> Overview</a>
  <a href="<?php echo admin_url('pos_system/restaurant/tables'); ?>" class="<?php echo $rest_section==='tables'?'active':''; ?>"><i class="fa fa-chair"></i> Tables</a>
  <a href="<?php echo admin_url('pos_system/restaurant/areas'); ?>" class="<?php echo $rest_section==='areas'?'active':''; ?>"><i class="fa fa-fire-alt"></i> Production Areas</a>
  <a href="<?php echo admin_url('pos_system/restaurant/recipes'); ?>" class="<?php echo $rest_section==='recipes'?'active':''; ?>"><i class="fa fa-book-open"></i> Recipes</a>
  <a href="<?php echo admin_url('pos_system/restaurant/kitchen'); ?>" class="<?php echo $rest_section==='kitchen'?'active':''; ?>" target="_blank"><i class="fa fa-tv"></i> Kitchen Display</a>
  <div class="rest-nav-spacer"></div>
  <a href="<?php echo admin_url('pos_system/settings#tab-restaurant'); ?>" style="font-size:11px;color:#64748b"><i class="fa fa-cog"></i> Settings</a>
</div>
