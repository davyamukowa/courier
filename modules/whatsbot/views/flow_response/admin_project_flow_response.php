<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $id = $this->uri->segment('4'); ?>
<div class="panel_s">
    <div class="panel-body">
    <input type="hidden" name="projectid" value="<?= $id ?>" id="projectid">
        <div class="row">
            <?php $table_data = [
                _l('#'),
                _l('name'),
                _l('receiver'),
                _l('submit_time'),
                _l('wb_number'),
                _l('type'),
                _l('action')
            ];
            render_datatable($table_data, 'relation_flow_response'); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="relation_flow_response_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('flow_responses'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
