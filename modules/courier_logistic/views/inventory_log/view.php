<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">

                    <div class="cgs-card__header">
                        <h4 class="cgs-card__title">
                            <i class="fa fa-clipboard-list"></i>
                            Inventory Log Book — Sheet# <?php echo htmlspecialchars($log['sheet_number'] ?: $log['id']); ?>
                        </h4>
                        <div class="cgs-card__actions" style="display:flex; gap:8px;">
                            <a href="<?php echo admin_url('courier_logistic/inventory_log'); ?>"
                               class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="<?php echo admin_url('courier_logistic/inventory_log/edit/' . $log['id']); ?>"
                               class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <a href="<?php echo admin_url('courier_logistic/inventory_log/print_form/' . $log['id']); ?>" target="_blank"
                               class="cgs-btn cgs-btn--sm" style="background:#2e7d32;color:#fff;">
                                <i class="fa fa-print"></i> Print / Save as PDF
                            </a>
                        </div>
                    </div>

                    <div class="row" style="padding:15px 15px 0;align-items:center;">
                        <div class="col-md-2">
                            <?php if (!empty($company['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($company['logo_url']); ?>" alt="Logo" style="max-height:60px;max-width:100%;">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-10" style="font-size:13px;color:#444;">
                            <?php if (!empty($company['name'])): ?><strong style="font-size:15px;color:#222;"><?php echo htmlspecialchars($company['name']); ?></strong><br><?php endif; ?>
                            <?php
                            // The P.O. Box setting is sometimes mis-filled with the email
                            // address (this tenant's data) — don't show it twice.
                            $pobox_is_dupe = !empty($company['pobox']) && !empty($company['email'])
                                && strcasecmp(trim($company['pobox']), trim($company['email'])) === 0;
                            $contact_bits = array_filter([
                                !empty($company['phone']) ? 'Phone: ' . $company['phone'] : '',
                                (!empty($company['pobox']) && !$pobox_is_dupe) ? 'P.O. Box: ' . $company['pobox'] : '',
                                !empty($company['email']) ? 'Email: ' . $company['email'] : '',
                            ]);
                            ?>
                            <?php echo htmlspecialchars(implode('   |   ', $contact_bits)); ?>
                        </div>
                    </div>
                    <hr style="margin:12px 0 0;">

                    <div class="row" style="padding:15px;">
                        <div class="col-md-4"><strong>Company Name:</strong> <?php echo htmlspecialchars($log['company_name'] ?: '—'); ?></div>
                        <div class="col-md-2"><strong>Date:</strong> <?php echo !empty($log['log_date']) ? date('d M Y', strtotime($log['log_date'])) : '—'; ?></div>
                        <div class="col-md-2"><strong>Time:</strong> <?php echo !empty($log['log_time']) ? date('h:i', strtotime($log['log_time'])) . ' ' . htmlspecialchars($log['am_pm']) : '—'; ?></div>
                        <div class="col-md-2"><strong>Counted By:</strong> <?php echo htmlspecialchars($log['counted_by'] ?: '—'); ?></div>
                        <div class="col-md-2"><strong>Sheet#:</strong> <?php echo htmlspecialchars($log['sheet_number'] ?: '—'); ?></div>
                    </div>

                    <div style="padding:0 15px 20px;">
                        <table class="table table-bordered">
                            <thead>
                                <tr style="background:#eef1f4;">
                                    <th>Item# / SKU</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Location</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['item_sku'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['qty'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No items recorded.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="padding:15px 15px 30px;">
                        <div class="col-md-6" style="font-size:13px;">
                            Issued by <strong><?php echo !empty($log['issued_by']) ? htmlspecialchars($log['issued_by']) : '..........................................'; ?></strong>
                            &nbsp; Sign ....................
                        </div>
                        <div class="col-md-6" style="font-size:13px;">
                            Received by <strong><?php echo !empty($log['received_by']) ? htmlspecialchars($log['received_by']) : '..........................................'; ?></strong>
                            &nbsp; Sign ....................
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
</div>
