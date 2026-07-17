<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">

                        <div class="cgs-card__header">
                            <h4 class="cgs-card__title">
                                <i class="fa fa-file-invoice"></i>
                                Commercial Invoices
                            </h4>
                            <div class="cgs-card__actions">
                            <a href="<?php echo admin_url('courier_goshipping/shipments/main'); ?>"
                               class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                <i class="fa fa-arrow-left"></i> Back to Shipments
                            </a>
                            </div>
                        </div>

                        <?php if (empty($commercial_invoices)): ?>
                            <div class="text-center" style="padding:60px 20px; color:#999;">
                                <i class="fa fa-file-invoice" style="font-size:48px; margin-bottom:16px; display:block; opacity:.3;"></i>
                                <p style="font-size:16px;">No commercial invoices found.</p>
                                <p style="font-size:13px;">Commercial invoices are created when you add goods/cargo details to a shipment.</p>
                            </div>
                        <?php else: ?>
                            <table class="table dt-table cgs-table" id="ciTable"
                                   data-order-col="4" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th>Waybill / Tracking</th>
                                        <th>Sender</th>
                                        <th>Recipient</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($commercial_invoices as $ci): ?>
                                    <?php
                                    $waybill     = htmlspecialchars($ci->waybill_number ?: $ci->tracking_id);
                                    $sender_name = $ci->sender_id
                                        ? htmlspecialchars(trim($ci->sender_first . ' ' . $ci->sender_last))
                                        : htmlspecialchars($ci->sender_company ?? '—');
                                    $recip_name  = $ci->recipient_id
                                        ? htmlspecialchars(trim($ci->recip_first . ' ' . $ci->recip_last))
                                        : htmlspecialchars($ci->recip_company ?? '—');
                                    $ci_url = !empty($ci->commercial_invoice_url)
                                        ? base_url($ci->commercial_invoice_url)
                                        : admin_url('courier_goshipping/shipments/commercial_invoice/' . $ci->id);
                                    $ci_target = !empty($ci->commercial_invoice_url) ? ' target="_blank"' : '';
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $ci->id); ?>"
                                               style="font-weight:700; color:#1565c0; text-decoration:none;"
                                               title="Open Waybill">
                                                <?php echo $waybill; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $sender_name; ?></td>
                                        <td><?php echo $recip_name; ?></td>
                                        <td><?php echo htmlspecialchars(strtoupper($ci->shipping_mode)); ?></td>
                                        <td>
                                            <span class="badge badge-pill bg-primary">
                                                <?php echo htmlspecialchars($ci->status_description ?? '—'); ?>
                                            </span>
                                        </td>
                                        <td data-order="<?php echo strtotime($ci->created_at); ?>">
                                            <?php echo date('d M Y', strtotime($ci->created_at)); ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-wrap:wrap; gap:5px; align-items:center;">

                                                <!-- View Commercial Invoice -->
                                                <a href="<?php echo $ci_url; ?>"<?php echo $ci_target; ?>
                                                   style="display:inline-flex;align-items:center;gap:4px;
                                                          padding:5px 12px;font-size:12px;font-weight:600;
                                                          background:#e65100;color:#fff;border-radius:4px;
                                                          text-decoration:none;white-space:nowrap;">
                                                    <i class="fa fa-file-invoice"></i> View Invoice
                                                </a>

                                                <!-- Print (open in new tab) -->
                                                <a href="<?php echo $ci_url; ?>" target="_blank"
                                                   style="display:inline-flex;align-items:center;gap:4px;
                                                          padding:5px 12px;font-size:12px;font-weight:600;
                                                          background:#2e7d32;color:#fff;border-radius:4px;
                                                          text-decoration:none;white-space:nowrap;">
                                                    <i class="fa fa-print"></i> Print
                                                </a>

                                                <!-- View Waybill -->
                                                <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $ci->id); ?>"
                                                   style="display:inline-flex;align-items:center;gap:4px;
                                                          padding:5px 12px;font-size:12px;font-weight:600;
                                                          background:#1565c0;color:#fff;border-radius:4px;
                                                          text-decoration:none;white-space:nowrap;">
                                                    <i class="fa fa-file-text"></i> Waybill
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Waybill / Tracking</th>
                                        <th>Sender</th>
                                        <th>Recipient</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
</div>
