<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>
<?php /* Standalone Riders page (Users / Riders / Agents) — deliberately NOT inside fulfilment/main.php, which adds Salibay-only header + KPI cards. */ ?>
<div style="padding:0 15px;">
    <?php echo $group_content ?? ''; ?>
</div>
<?php init_tail(); ?>
