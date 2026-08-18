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
                            Inventory Log Book
                        </h4>
                        <div class="cgs-card__actions" style="display:flex; gap:8px;">
                            <a href="<?php echo admin_url('courier_logistic/inventory_log/print_form'); ?>" target="_blank"
                               class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                <i class="fa fa-print"></i> Print Blank Form
                            </a>
                            <a href="<?php echo admin_url('courier_logistic/inventory_log/create'); ?>"
                               class="cgs-btn cgs-btn--sm" style="background:#c62828;color:#fff;">
                                <i class="fa fa-plus"></i> New Log Book
                            </a>
                        </div>
                    </div>

                    <?php if (empty($logs)): ?>
                        <div class="text-center" style="padding:60px 20px; color:#999;">
                            <i class="fa fa-clipboard-list" style="font-size:48px; margin-bottom:16px; display:block; opacity:.3;"></i>
                            <p style="font-size:16px;">No inventory log books yet.</p>
                            <p style="font-size:13px;">Fill one in digitally, or print a blank form for a manual count.</p>
                        </div>
                    <?php else: ?>
                        <table class="table dt-table cgs-table" id="invLogTable"
                               data-order-col="0" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th>Sheet#</th>
                                    <th>Company Name</th>
                                    <th>Date</th>
                                    <th>Counted By</th>
                                    <th># Items</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                $item_count = 0;
                                $decoded = json_decode($log['items_json'] ?: '[]', true);
                                if (is_array($decoded)) { $item_count = count($decoded); }
                                $log_date_display = $log['log_date'] ? date('d M Y', strtotime($log['log_date'])) : '—';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['sheet_number'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars($log['company_name'] ?: '—'); ?></td>
                                    <td><?php echo $log_date_display; ?></td>
                                    <td><?php echo htmlspecialchars($log['counted_by'] ?: '—'); ?></td>
                                    <td><?php echo (int) $item_count; ?></td>
                                    <td data-order="<?php echo strtotime($log['created_at']); ?>">
                                        <?php echo date('d M Y, g:i A', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; flex-wrap:wrap; gap:5px; align-items:center;">
                                            <a href="<?php echo admin_url('courier_logistic/inventory_log/view/' . $log['id']); ?>"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;font-size:12px;font-weight:600;background:#1565c0;color:#fff;border-radius:4px;text-decoration:none;white-space:nowrap;">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            <a href="<?php echo admin_url('courier_logistic/inventory_log/edit/' . $log['id']); ?>"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;font-size:12px;font-weight:600;background:#455a64;color:#fff;border-radius:4px;text-decoration:none;white-space:nowrap;">
                                                <i class="fa fa-pencil"></i> Edit
                                            </a>
                                            <a href="<?php echo admin_url('courier_logistic/inventory_log/print_form/' . $log['id']); ?>" target="_blank"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;font-size:12px;font-weight:600;background:#2e7d32;color:#fff;border-radius:4px;text-decoration:none;white-space:nowrap;">
                                                <i class="fa fa-print"></i> Print
                                            </a>
                                            <a href="<?php echo admin_url('courier_logistic/inventory_log/delete/' . $log['id']); ?>"
                                               onclick="return confirm('Delete this inventory log book?');"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;font-size:12px;font-weight:600;background:#b71c1c;color:#fff;border-radius:4px;text-decoration:none;white-space:nowrap;">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
</div>
