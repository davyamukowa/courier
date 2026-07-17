<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="courier-sidebar">
                    <div class="courier-sidebar-header">
                        <i class="fa fa-globe"></i> Shipments
                    </div>
                    <nav class="courier-sidebar-nav">
                        <a href="<?php echo admin_url('courier/shipments/main?group=dashboard'); ?>" class="courier-nav-item">
                            <i class="fa fa-tachometer"></i> Dashboard
                        </a>

                        <div class="courier-nav-divider"></div>

                        <!-- Create Shipments group -->
                        <div class="courier-nav-group-toggle" onclick="courierToggle(this,'create-sub')">
                            <i class="fa fa-plus-circle"></i>
                            <span>Create Shipment</span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="courier-nav-submenu" id="create-sub">
                            <a href="<?php echo admin_url('courier/shipments/create?type=domestic'); ?>" class="courier-nav-sub-item">
                                <i class="fa fa-home"></i> Domestic
                            </a>
                            <!-- International sub-group -->
                            <div class="courier-nav-group-toggle" style="padding-left:40px;" onclick="courierToggle(this,'create-intl-sub')">
                                <i class="fa fa-globe"></i>
                                <span>International</span>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="courier-nav-submenu" id="create-intl-sub">
                                <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=road&mode_type=none'); ?>" class="courier-nav-sub2-item">
                                    <i class="fa fa-truck"></i> Road
                                </a>
                                <!-- Sea sub-group -->
                                <div class="courier-nav-group-toggle" style="padding-left:58px;" onclick="courierToggle(this,'create-sea-sub')">
                                    <i class="fa fa-ship"></i>
                                    <span>Sea</span>
                                    <i class="fa fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="courier-nav-submenu" id="create-sea-sub">
                                    <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=sea&mode_type=fcl'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">FCL</a>
                                    <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=sea&mode_type=lcl'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">LCL</a>
                                    <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=sea&mode_type=sea_consolidation'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Consolidation</a>
                                </div>
                                <!-- Air sub-group -->
                                <div class="courier-nav-group-toggle" style="padding-left:58px;" onclick="courierToggle(this,'create-air-sub')">
                                    <i class="fa fa-plane"></i>
                                    <span>Air</span>
                                    <i class="fa fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="courier-nav-submenu" id="create-air-sub">
                                    <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=air&mode_type=air_freight'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Air Freight</a>
                                    <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=air&mode_type=air_consolidation'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Air Consolidation</a>
                                </div>
                                <a href="<?php echo admin_url('courier/shipments/create?type=international&mode=courier&mode_type=none'); ?>" class="courier-nav-sub2-item">
                                    <i class="fa fa-road"></i> Courier
                                </a>
                            </div>
                        </div>

                        <div class="courier-nav-divider"></div>

                        <!-- List Shipments group -->
                        <div class="courier-nav-group-toggle" onclick="courierToggle(this,'list-sub')">
                            <i class="fa fa-list"></i>
                            <span>List Shipments</span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="courier-nav-submenu" id="list-sub">
                            <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="courier-nav-sub-item">
                                <i class="fa fa-home"></i> Domestic
                            </a>
                            <!-- International sub-group -->
                            <div class="courier-nav-group-toggle" style="padding-left:40px;" onclick="courierToggle(this,'list-intl-sub')">
                                <i class="fa fa-globe"></i>
                                <span>International</span>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="courier-nav-submenu" id="list-intl-sub">
                                <a href="<?php echo admin_url('courier/shipments?type=international&mode=road&mode_type=none'); ?>" class="courier-nav-sub2-item">
                                    <i class="fa fa-truck"></i> Road
                                </a>
                                <div class="courier-nav-group-toggle" style="padding-left:58px;" onclick="courierToggle(this,'list-sea-sub')">
                                    <i class="fa fa-ship"></i>
                                    <span>Sea</span>
                                    <i class="fa fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="courier-nav-submenu" id="list-sea-sub">
                                    <a href="<?php echo admin_url('courier/shipments?type=international&mode=sea&mode_type=fcl'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">FCL</a>
                                    <a href="<?php echo admin_url('courier/shipments?type=international&mode=sea&mode_type=lcl'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">LCL</a>
                                    <a href="<?php echo admin_url('courier/shipments?type=international&mode=sea&mode_type=sea_consolidation'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Consolidation</a>
                                </div>
                                <div class="courier-nav-group-toggle" style="padding-left:58px;" onclick="courierToggle(this,'list-air-sub')">
                                    <i class="fa fa-plane"></i>
                                    <span>Air</span>
                                    <i class="fa fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="courier-nav-submenu" id="list-air-sub">
                                    <a href="<?php echo admin_url('courier/shipments?type=international&mode=air&mode_type=air_freight'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Air Freight</a>
                                    <a href="<?php echo admin_url('courier/shipments?type=international&mode=air&mode_type=air_consolidation'); ?>" class="courier-nav-sub2-item" style="padding-left:74px;">Air Consolidation</a>
                                </div>
                                <a href="<?php echo admin_url('courier/shipments?type=international&mode=courier&mode_type=none'); ?>" class="courier-nav-sub2-item">
                                    <i class="fa fa-road"></i> Courier
                                </a>
                            </div>
                        </div>

                        <div class="courier-nav-divider"></div>

                        <!-- All Shipments (flat list) -->
                        <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="courier-nav-item">
                            <i class="fa fa-list-alt"></i> Shipment List
                        </a>

                        <div class="courier-nav-divider"></div>

                        <!-- Documents & Reports group -->
                        <div class="courier-nav-group-toggle" onclick="courierToggle(this,'docs-sub')">
                            <i class="fa fa-folder-open"></i>
                            <span>Documents</span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="courier-nav-submenu" id="docs-sub">
                            <a href="<?php echo admin_url('courier/shipments/list_invoices'); ?>" class="courier-nav-sub-item">
                                <i class="fa fa-money"></i> Invoices
                            </a>
                            <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="courier-nav-sub-item"
                               title="Open any shipment and click Waybill">
                                <i class="fa fa-file-text"></i> Waybills
                            </a>
                            <a href="<?php echo admin_url('courier/shipments/list_commercial_invoices'); ?>" class="courier-nav-sub-item"
                               title="View all commercial invoices">
                                <i class="fa fa-file-invoice"></i> Commercial Invoices
                            </a>
                        </div>

                        <div class="courier-nav-divider"></div>

                        <!-- Manifests shortcut -->
                        <a href="<?php echo admin_url('courier/shipments/main?group=manifests'); ?>" class="courier-nav-item">
                            <i class="fa fa-book"></i> Manifests
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

<script>
function courierToggle(el, subId) {
    el.classList.toggle('open');
    const sub = document.getElementById(subId);
    if (sub) sub.classList.toggle('open');
}
</script>

<?php init_tail(); ?>
