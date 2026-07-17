<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="courier-sidebar">
                    <div class="courier-sidebar-header">
                        <i class="fa fa-cogs"></i> Settings
                    </div>
                    <nav class="courier-sidebar-nav">
                        <a href="<?php echo admin_url('courier/settings/main?group=customization'); ?>" class="courier-nav-item <?php echo ($group ?? '') === 'customization' ? 'active' : ''; ?>">
                            <i class="fa fa-sliders"></i> Customization
                        </a>
                        <a href="<?php echo admin_url('courier/settings/main?group=service_points'); ?>" class="courier-nav-item <?php echo ($group ?? '') === 'service_points' ? 'active' : ''; ?>">
                            <i class="fa fa-map-marker"></i> Service Points
                        </a>
                        <a href="<?php echo admin_url('courier/settings/main?group=tariff'); ?>" class="courier-nav-item <?php echo ($group ?? '') === 'tariff' ? 'active' : ''; ?>">
                            <i class="fa fa-tags"></i> Tariff &amp; Rates
                        </a>
                        <a href="<?php echo admin_url('courier/settings/main?group=international_tariffs'); ?>" class="courier-nav-item <?php echo ($group ?? '') === 'international_tariffs' ? 'active' : ''; ?>">
                            <i class="fa fa-globe"></i> International Tariffs
                        </a>
                        <a href="<?php echo admin_url('courier/settings/main?group=invoice_info'); ?>" class="courier-nav-item <?php echo ($group ?? '') === 'invoice_info' ? 'active' : ''; ?>">
                            <i class="fa fa-file-text-o"></i> Invoice &amp; Receipt Info
                        </a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9">
                <?php echo $group_content ?? " "; ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
