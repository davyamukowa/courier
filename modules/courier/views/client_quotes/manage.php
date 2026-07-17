<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
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
                        render_datatable($table_data, 'client-quotes');
                        ?>
                    </div>
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
