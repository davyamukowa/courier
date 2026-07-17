<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="courier-sidebar">
                    <div class="courier-sidebar-header">
                        <i class="fa fa-building"></i> Companies
                    </div>
                    <nav class="courier-sidebar-nav">
                        <a href="<?php echo admin_url('courier/companies/main?group=dashboard'); ?>" class="courier-nav-item">
                            <i class="fa fa-tachometer"></i> Dashboard
                        </a>
                        <a href="<?php echo admin_url('courier/companies/main?group=create_company'); ?>" class="courier-nav-item">
                            <i class="fa fa-plus-circle"></i> Create Company
                        </a>
                        <div class="courier-nav-divider"></div>
                        <a href="<?php echo admin_url('courier/companies/main?group=list_companies'); ?>" class="courier-nav-item">
                            <i class="fa fa-list"></i> List of Companies
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
