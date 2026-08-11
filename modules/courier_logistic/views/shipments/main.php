<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>
<style>
.cgs-ship-content {
    min-width: 0;
}
</style>

        <section class="cgs-ship-content">
            <?php echo $group_content ?? ' '; ?>
        </section>
    </div>
</div>

<?php init_tail(); ?>

