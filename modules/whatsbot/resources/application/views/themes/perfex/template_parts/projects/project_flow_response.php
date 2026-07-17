<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <?php
    $flow_response_data = get_flow_responses();
    ?>
    <table class="table dt-table" data-order-col="3">
        <thead>
            <tr>
                <th><?php echo _l('#'); ?></th>
                <th><?php echo _l('name'); ?></th>
                <th><?php echo _l('receiver'); ?></th>
                <th><?php echo _l('submit_time'); ?></th>
                <th><?php echo  _l('wa_number'); ?></th>
                <th><?php echo _l('type'); ?></th>
                <th><?php echo  _l('action'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flow_response_data as $flow_response) : ?>
                <?php $color = ('leads' == $flow_response['type'] ? '#3a25e9' : ('contacts' == $flow_response['type'] ? '#ff4646' : '#7bf565'));

                $responseData = $flow_response['response_data'];

                $responseDataArray = json_decode($responseData, true);

                if (isset($responseDataArray['flow_token'])) {

                    $flow_token = json_decode($responseDataArray['flow_token'], true);

                    $condition = isset($flow_token['rel_data']['id']) && $flow_token['rel_data']['id'] === $project->id && $flow_token['rel_data']['relation_type'] === 'project';

                        if ($condition) {
                ?>
                            <tr>
                                <td><?= $flow_response['id'] ?></td>

                                <td><?= $flow_response['flow_name'] ?></td>

                                <td><?= $flow_response['receiver_id'] ?></td>

                                <td><?= $flow_response['submit_time'] ?></td>

                                <td><?= $flow_response['wa_no'] ?></td>

                                <td><span class="label" style="color:<?= $color ?>;border:1px solid <?= adjust_hex_brightness($color, 0.4) ?>;background:<?= adjust_hex_brightness($color, 0.04) ?>"> <?= _l($flow_response['type'])  ?></span></td>

                                <td><a href="javascript:void(0)" data-id="<?= $flow_response['id'] ?>" data-toggle="modal" data-target="#client_project_flow_response"><i class="fa-solid fa-eye fa-lg"></i></a></td>
                            </tr>
            <?php         
                    }
                }
            endforeach ?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="client_project_flow_response" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
<script>
    $(function() {
        $('#client_project_flow_response').on('show.bs.modal', function(e) {
            var invoker = $(e.relatedTarget);
            var res_id = $(invoker).data('id');
            $.get(`${admin_url}whatsbot/flows/flow_review/${res_id}`, function(data) {
                $('#client_project_flow_response .modal-body').html(data)
            });
        });
    })
</script>