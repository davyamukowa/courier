<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'pickups']); ?>
        <div class="row">
            <div class="col-md-3">
                <div class="courier-sidebar">
                    <div class="courier-sidebar-header">
                        <i class="fa fa-truck"></i> Pickups
                    </div>
                    <nav class="courier-sidebar-nav">
                        <?php if ($user_role !== 'Fleet: Driver'): ?>
                        <a href="<?php echo admin_url('courier_logistic/pickups/main?group=dashboard'); ?>" class="courier-nav-item">
                            <i class="fa fa-tachometer"></i> Dashboard
                        </a>
                        <a href="<?php echo admin_url('courier_logistic/pickups/create'); ?>" class="courier-nav-item">
                            <i class="fa fa-plus-circle"></i> Create Pickup
                        </a>
                        <div class="courier-nav-divider"></div>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('courier_logistic/pickups'); ?>" class="courier-nav-item">
                            <i class="fa fa-list"></i> List of Pickups
                        </a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9">
                <?php echo $group_content ?? ' '; ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

