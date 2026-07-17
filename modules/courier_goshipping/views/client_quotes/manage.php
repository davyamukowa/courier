<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'network']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">
                    <div class="cgs-card__header">
                        <h4 class="cgs-card__title"><i class="fa fa-list-alt"></i> <?php echo $title; ?></h4>
                    </div>
                    <?php
                    $table_data = [
                        'ID',
                        'Name',
                        'Company',
                        'Email',
                        'Phone',
                        'Quote Date',
                        'Service Type',
                        'Route',
                        'Total'
                    ];
                    render_datatable($table_data, 'client-quotes', ['cgs-table']);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-client-quotes', window.location.href, [], [], 'undefined', [0, 'desc']);
    });
</script>
</body>
</html>
