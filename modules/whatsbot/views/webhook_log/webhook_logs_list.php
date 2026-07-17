<?php
defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="staff_logged_time">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $tabs = [
                        ['key' => 'campaign_stats', 'label' => _l('campaign_analytics'), 'icon' => 'fa-solid fa-bullhorn', 'url' => admin_url('whatsbot/analytics')],
                        ['key' => 'activity_log', 'label' => _l('activity_log'), 'icon' => 'fa-solid fa-list', 'url' => admin_url('whatsbot/activity_log')],
                        ['key' => 'webhook_logs', 'label' => _l('webhook_logs'), 'icon' => 'fa-solid fa-shield-halved', 'url' => admin_url('whatsbot/webhook_logs')],
                    ];
                    $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'webhook_logs']);
                    ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="">
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('webhook_logs'); ?></h4>
                                    <?php if (staff_can('clear_log', 'wtc_log_activity')) { ?>
                                        <a href="<?php echo admin_url('whatsbot/webhook_logs/clear_log'); ?>" class="btn btn-danger _delete"><?php echo _l('clear_log'); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <div class="clearfix"></div>
                            <?php
                            echo render_datatable([
                                _l('the_number_sign'),
                                _l('webhook_name'),
                                _l('status'),
                                _l('sendtime'),
                                _l('actions'),
                            ], 'webhook_logs');
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="webhook_detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 id="heading_text" class="modal-title"><?= _l('webhook_payload_data'); ?><h4>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div class="container-fluid" id="jsonContainer">
                    <!-- Rows will be appended here -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    "use strict";
    $(function() {
        initDataTable('.table-webhook_logs', `${admin_url}whatsbot/webhook_logs/webhook_logs_table`, [], [], [], [0, 'desc']);
    });

    // Showing webhook payload in modal
    $(document).on('click', '.webhook_detail', function(event) {
        event.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            type: "get",
            url: `${admin_url}whatsbot/webhook_logs/get_webhook/${id}`,
            dataType: "json",
            success: function(response) {
                let prettyJson = '';
                try {
                    function decodeHtmlEntities(str) {
                        var txt = document.createElement('textarea');
                        txt.innerHTML = str;
                        return txt.value;
                    }
                    let rawData = decodeHtmlEntities(response.payload);
                    prettyJson = JSON.stringify(JSON.parse(rawData), null, 4);
                } catch (e) {
                    prettyJson = response.payload;
                }

                var html = `
                <div class="row">
                    <div class="col-md-12 mbot15">
                        <label class="form-label"><?= _l('delivery_url') ?></label>
                        <div class="label label-info"><span>${site_url}whatsbot/get_webhook/${response.webhook_id}/${response.hash}</span></div>
                    </div>
                    <div class="col-md-12 code-toolbar">
                        <pre class="language-json" style="max-height:400px;"><code class="language-json" id="json-block"></code></pre>
                    </div>
                </div>`;
                $('#jsonContainer').html(html);

                document.getElementById("json-block").textContent = prettyJson;
                Prism.highlightAll();

                $('#webhook_detail').modal('show');
            }
        });
    });

</script>
