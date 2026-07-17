<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- at admin side tab content -->
<input type="hidden" name="ticketid" value="<?= $tickets->ticketid ?>" id="ticketid">
<div role="tabpanel" class="tab-pane" id="flow_response">
    <div class="row">
        <div class="col-md-12">
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 id="heading_text" class="modal-title"><?= _l('flow_response'); ?><h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
