<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <i class="fa fa-globe"></i> International Tariffs for: <strong><?php echo htmlspecialchars($origin); ?></strong>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php if (empty($matrices)): ?>
                            <div class="alert alert-info">No rates uploaded for this origin country yet.</div>
                        <?php else: ?>
                            <!-- Nav tabs for multiple service types -->
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($matrices as $index => $m): ?>
                                    <li role="presentation" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                                        <a href="#service_<?php echo md5($m['service']); ?>" aria-controls="service_<?php echo md5($m['service']); ?>" role="tab" data-toggle="tab">
                                            Service: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $m['service']))); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content" style="padding-top: 15px;">
                                <?php foreach ($matrices as $index => $m): ?>
                                    <div role="tabpanel" class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" id="service_<?php echo md5($m['service']); ?>">
                                        
                                        <!-- Excel-like scrolling container -->
                                        <div style="overflow: auto; max-height: 600px; width: 100%; border: 1px solid #ddd;">
                                            <table class="table table-bordered table-condensed table-hover" style="white-space: nowrap; font-size: 13px; margin-bottom: 0;">
                                                <thead style="background: #f5f5f5; position: sticky; top: 0; z-index: 10;">
                                                    <tr>
                                                        <th style="position: sticky; left: 0; background: #f5f5f5; z-index: 11; border-right: 2px solid #ccc;">Weight / Destination</th>
                                                        <?php foreach ($m['destinations'] as $dest): ?>
                                                            <th class="text-center"><?php echo htmlspecialchars($dest); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($m['weights'] as $w): ?>
                                                        <tr>
                                                            <td style="position: sticky; left: 0; background: #fff; font-weight: bold; border-right: 2px solid #ccc; z-index: 5;">
                                                                <?php echo htmlspecialchars($w); ?> <?php echo is_numeric($w) ? 'kg' : ''; ?>
                                                            </td>
                                                            <?php foreach ($m['destinations'] as $dest): ?>
                                                                <?php 
                                                                    $rate = isset($m['data'][$w][$dest]) ? $m['data'][$w][$dest] : ''; 
                                                                ?>
                                                                <td class="text-center" style="<?php echo $rate === '' ? 'background-color:#fefefe;color:#ccc;' : 'background-color:#f9fff9;'; ?>">
                                                                    <?php echo $rate !== '' ? htmlspecialchars($rate) : '-'; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
