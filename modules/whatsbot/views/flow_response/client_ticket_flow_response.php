<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="row">
    <div class="col-md-4 ticket-info mtop30">
        <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-inline-flex tw-items-center">
            <?= _l('flow_response') ?>
        </h4>
    </div>

    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <table class="table dt-table dt-inline dataTable no-footer" data-order-col="3">
                    <thead>
                        <tr>
                            <th><?php echo _l('#'); ?></th>
                            <th><?php echo _l('name'); ?></th>
                            <th><?php echo _l('receiver'); ?></th>
                            <th><?php echo _l('submit_time'); ?></th>
                            <th><?php echo  _l('wb_number'); ?></th>
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

                                $condition = isset($flow_token['rel_data']['id']) && $flow_token['rel_data']['id'] === $ticket_id && $flow_token['rel_data']['relation_type'] === 'ticket';
                            
                                    if ($condition) { ?>
                                        <tr>
                                            <td><?= $flow_response['id'] ?></td>

                                            <td><?= $flow_response['flow_name'] ?></td>

                                            <td><?= $flow_response['receiver_id'] ?></td>

                                            <td><?= $flow_response['submit_time'] ?></td>

                                            <td><?= $flow_response['wa_no'] ?></td>

                                            <td><span class="label" style="color:<?= $color ?>;border:1px solid <?= adjust_hex_brightness($color, 0.4) ?>;background:<?= adjust_hex_brightness($color, 0.04) ?>"> <?= _l($flow_response['type'])  ?></span></td>

                                            <td><a href="javascript:void(0)" data-id="<?= $flow_response['id'] ?>" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" data-toggle="modal" data-target="#client_ticket_flow_response_modal"><i class="fa-solid fa-eye fa-lg"></i></a></td>
                                        </tr>
                        <?php       
                                }
                            }
                        endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="client_ticket_flow_response_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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

<script>
    $(function() {
        $('#client_ticket_flow_response_modal').on('show.bs.modal', function(e) {
            var invoker = $(e.relatedTarget);
            var res_id = $(invoker).data('id');
            $.get(`${admin_url}whatsbot/flows/flow_review/${res_id}`, function(data) {
                $('#client_ticket_flow_response_modal .modal-body').html(data)
            });
        });
    })
</script>