<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Vendor Credit Notes <small class="text-muted" style="font-weight:400;font-size:13px;">— credit notes issued by your vendors</small></span>
                <a href="<?php echo admin_url('xetuu_books/refund_form'); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> New Credit Note
                </a>
            </div>
            <div class="xb-card-body">

                <!-- Summary Totals -->
                <div class="row mbot15">
                    <div class="col-md-3">
                        <div class="well text-center" style="padding:10px;">
                            <h5 class="text-muted">Total Untaxed</h5>
                            <h4><?php echo xb_format_money($list_totals->amount_untaxed ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="well text-center" style="padding:10px;">
                            <h5 class="text-muted">Total Tax</h5>
                            <h4><?php echo xb_format_money($list_totals->amount_tax ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="well text-center" style="padding:10px;">
                            <h5 class="text-muted">Total Amount</h5>
                            <h4><?php echo xb_format_money($list_totals->amount_total ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="well text-center" style="padding:10px;">
                            <h5 class="text-muted">Outstanding Credit</h5>
                            <h4 class="text-success"><?php echo xb_format_money($list_totals->amount_residual ?? 0); ?></h4>
                        </div>
                    </div>
                </div>

                <!-- Info banner -->
                <div class="alert alert-info" style="font-size:13px;margin-bottom:16px;">
                    <i class="fa fa-info-circle"></i>
                    <strong>What is a Vendor Credit Note?</strong>
                    A vendor credit note is issued by a vendor when they owe you money — e.g. you returned goods, they overbilled you, or they issued a discount after payment. It reverses the original bill and reduces your Accounts Payable.
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Credit Note #</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th>Ref / Original Bill</th>
                                <th>Total</th>
                                <th>Outstanding Credit</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($moves)): ?>
                            <tr>
                                <td colspan="9" class="text-center" style="padding:40px 0;color:#9ca3af;">
                                    <i class="fa fa-inbox fa-2x" style="display:block;margin-bottom:8px;"></i>
                                    No vendor credit notes yet. Click <strong>New Credit Note</strong> to record one.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($moves as $m): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo admin_url('xetuu_books/refund_form/' . $m->id); ?>" style="font-weight:600;">
                                        <?php echo $m->name ?: 'Draft'; ?>
                                    </a>
                                </td>
                                <td><?php echo xb_get_partner_name($m->partner_id, 'vendor'); ?></td>
                                <td><?php echo _d($m->date); ?></td>
                                <td><?php echo htmlspecialchars($m->ref ?: '—'); ?></td>
                                <td><?php echo xb_format_money($m->amount_total); ?></td>
                                <td class="text-success"><?php echo xb_format_money($m->amount_residual); ?></td>
                                <td><?php echo xb_state_label($m->state); ?></td>
                                <td><?php echo xb_state_label($m->payment_state); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('xetuu_books/refund_form/' . $m->id); ?>" class="btn btn-default btn-icon btn-xs"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
