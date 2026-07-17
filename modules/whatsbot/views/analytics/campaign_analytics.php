<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'campaign_stats', 'label' => _l('campaign_analytics'), 'icon' => 'fa-solid fa-bullhorn', 'url' => admin_url('whatsbot/analytics')],
                    ['key' => 'activity_log', 'label' => _l('activity_log'), 'icon' => 'fa-solid fa-list', 'url' => admin_url('whatsbot/activity_log')],
                    ['key' => 'webhook_logs', 'label' => _l('webhook_logs'), 'icon' => 'fa-solid fa-shield-halved', 'url' => admin_url('whatsbot/webhook_logs')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'campaign_stats']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- Date Filter -->
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-w-full">
                            <form method="GET" class="tw-flex tw-w-full tw-items-end tw-gap-4 tw-flex-wrap md:tw-flex-nowrap">
                                <div class="form-group no-margin tw-flex tw-flex-col tw-gap-2 tw-flex-1 tw-min-w-0">
                                    <label class="no-margin tw-font-medium"><?= _l('from'); ?></label>
                                    <input type="date" name="date_from" class="form-control tw-min-w-[220px]" style="height: 38px; padding: 6px 12px;" value="<?= $date_from; ?>">
                                </div>
                                <div class="form-group no-margin tw-flex tw-flex-col tw-gap-2 tw-flex-1 tw-min-w-0">
                                    <label class="no-margin tw-font-medium"><?= _l('to'); ?></label>
                                    <input type="date" name="date_to" class="form-control tw-min-w-[220px]" style="height: 38px; padding: 6px 12px;" value="<?= $date_to; ?>">
                                </div>
                                <div class="tw-flex tw-items-center tw-gap-3 tw-shrink-0">
                                    <button type="submit" class="btn btn-primary tw-h-[38px] tw-inline-flex tw-items-center"><?= _l('filter'); ?></button>
                                    <a href="<?= admin_url('whatsbot/analytics') ?>" class="btn btn-default tw-h-[38px] tw-inline-flex tw-items-center"><?= _l('reset'); ?></a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php
                $total = max($overall['total'] ?? 1, 1);
                $delivery_rate = round(($overall['delivered'] ?? 0) / $total * 100, 1);
                $read_rate = round(($overall['msg_read'] ?? 0) / $total * 100, 1);
                ?>
                <div class="row">
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #4e73df;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#4e73df;"><?= count($campaigns); ?></h3>
                                <p class="text-muted no-margin"><?= _l('campaigns'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #1cc88a;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#1cc88a;"><?= $overall['sent'] ?? 0; ?></h3>
                                <p class="text-muted no-margin"><?= _l('messages_sent'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #36b9cc;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#36b9cc;"><?= $delivery_rate; ?>%</h3>
                                <p class="text-muted no-margin"><?= _l('delivery_rate'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #f6c23e;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#f6c23e;"><?= $read_rate; ?>%</h3>
                                <p class="text-muted no-margin"><?= _l('read_rate'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #e74a3b;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#e74a3b;"><?= $overall['failed'] ?? 0; ?></h3>
                                <p class="text-muted no-margin"><?= _l('failed'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel_s" style="border-left: 4px solid #858796;">
                            <div class="panel-body text-center">
                                <h3 class="tw-font-bold" style="color:#858796;"><?= $overall['msg_read'] ?? 0; ?></h3>
                                <p class="text-muted no-margin"><?= _l('read'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Message Status Pie Chart -->
                    <div class="col-md-4">
                        <div class="panel_s">
                            <div class="panel-heading">
                                <h4 class="no-margin"><?= _l('message_status_distribution'); ?></h4>
                            </div>
                            <div class="panel-body"><canvas id="statusPieChart" height="200"></canvas></div>
                        </div>
                    </div>
                    <!-- Daily Volume Line Chart -->
                    <div class="col-md-8">
                        <div class="panel_s">
                            <div class="panel-heading">
                                <h4 class="no-margin"><?= _l('daily_message_volume'); ?></h4>
                            </div>
                            <div class="panel-body"><canvas id="dailyVolumeChart" height="100"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Template Performance -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="no-margin"><?= _l('template_performance'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($template_perf)) { ?>
                            <p class="text-center text-muted no-margin"><?= _l('no_data_available'); ?></p>
                        <?php } else { ?>
                        <div class="panel-table-full">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?= _l('template'); ?></th>
                                        <th><?= _l('category'); ?></th>
                                        <th><?= _l('total_sent'); ?></th>
                                        <th><?= _l('total_read'); ?></th>
                                        <th><?= _l('read_rate'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($template_perf as $tp) { ?>
                                        <tr>
                                            <td><?= $tp['template_name']; ?></td>
                                            <td><span class="label label-info"><?= $tp['category']; ?></span></td>
                                            <td><?= $tp['total_sent']; ?></td>
                                            <td><?= $tp['total_read']; ?></td>
                                            <td><?= $tp['read_rate']; ?>%</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Campaign Table -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="no-margin"><?= _l('campaigns'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($campaigns)) { ?>
                            <p class="text-center text-muted no-margin"><?= _l('no_data_available'); ?></p>
                        <?php } else { ?>
                        <div class="panel-table-full">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?= _l('name'); ?></th>
                                        <th><?= _l('template'); ?></th>
                                        <th><?= _l('total'); ?></th>
                                        <th><?= _l('sent'); ?></th>
                                        <th><?= _l('delivered'); ?></th>
                                        <th><?= _l('read'); ?></th>
                                        <th><?= _l('failed'); ?></th>
                                        <th><?= _l('delivery_rate'); ?></th>
                                        <th><?= _l('read_rate'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campaigns as $c) {
                                        $c_total = max($c['total'], 1);
                                        $c_del = round($c['delivered'] / $c_total * 100, 1);
                                        $c_read = round($c['msg_read'] / $c_total * 100, 1);
                                    ?>
                                        <tr>
                                            <td><?= $c['name']; ?></td>
                                            <td><?= $c['template_name'] ?? '-'; ?></td>
                                            <td><?= $c['total']; ?></td>
                                            <td><?= $c['sent']; ?></td>
                                            <td><?= $c['delivered']; ?></td>
                                            <td><?= $c['msg_read']; ?></td>
                                            <td><span class="<?= $c['failed'] > 0 ? 'text-danger tw-font-bold' : ''; ?>"><?= $c['failed']; ?></span></td>
                                            <td><?= $c_del; ?>%</td>
                                            <td><?= $c_read; ?>%</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?= base_url('assets/plugins/Chart.js/Chart.min.js'); ?>"></script>
<script>
    // Pie chart
    var pieData = <?= json_encode($overall); ?>;
    new Chart(document.getElementById('statusPieChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Sent', 'Delivered', 'Read', 'Failed'],
            datasets: [{
                data: [
                    (pieData.sent || 0) - (pieData.delivered || 0),
                    (pieData.delivered || 0) - (pieData.msg_read || 0),
                    pieData.msg_read || 0,
                    pieData.failed || 0
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#e74a3b']
            }]
        },
        options: {
            responsive: true
        }
    });

    // Line chart
    var dailyData = <?= json_encode($daily_volume); ?>;
    new Chart(document.getElementById('dailyVolumeChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: '<?= _l("messages_sent"); ?>',
                data: dailyData.map(d => d.count),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
