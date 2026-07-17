<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">
                        <div class="cgs-card__header">
                            <h4 class="cgs-card__title"><i class="fa fa-file-text-o"></i> Courier Logistic — Manifests</h4>
                        </div>
                        <?php if (!empty($manifests)): ?>
                        <table class="table dt-table cgs-table" data-order-type="desc" data-order-col="3" id="example">
                            <thead class="table-head">
                            <tr>
                                <th>AWB NUMBER</th>
                                <th>Flight NUMBER</th>
                                <th>TOTAL($USD)</th>
                                <th>CREATED AT</th>
                                <th>ACTION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($manifests as $manifest): ?>
                            <tr>
                                <td><?php echo $manifest->manifest_number; ?></td>
                                <td><?php echo $manifest->flight_number; ?></td>
                                <td><?php echo $manifest->total; ?></td>
                                <td><?php echo $manifest->created_at; ?></td>
                                <td>
                                    <a style="margin-right:6px; margin-top:5px;"
                                       href="<?php echo admin_url('courier_logistic/manifests/view/' . $manifest->manifest_number); ?>"
                                       class="cgs-btn cgs-btn--primary cgs-btn--sm"
                                       title="Edit Manifest">
                                        <i class="fa fa-eye" aria-hidden="true"></i> View/Edit
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach;  ?>
                            </tbody>
                            <tfoot class="table-footer">
                            <tr>
                                <th>AWB NUMBER</th>
                                <th>Flight NUMBER</th>
                                <th>CREATED AT</th>
                                <th>TOTAL($USD)</th>
                                <th>ACTION</th>
                            </tr>
                            </tfoot>
                        </table>
                        <?php else: ?>
                            <!-- Show a message when there's no data -->
                            <div class="text-center text-danger">
                                <p>No available manifests</p>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

