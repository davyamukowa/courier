<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Journal Entries</span>
                <a href="<?php echo admin_url('xetuu_books/journal_entry_form'); ?>" class="btn btn-primary xb-btn-primary btn-sm">New Entry</a>
            </div>
            <div class="xb-card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Partner</th>
                                <th>Reference</th>
                                <th>Journal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entries)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No journal entries found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($entries as $e): ?>
                                    <tr>
                                        <td><?php echo _d($e->date); ?></td>
                                        <td><a href="<?php echo admin_url('xetuu_books/journal_entry_form/' . $e->id); ?>"><b><?php echo $e->name ? $e->name : 'Draft'; ?></b></a></td>
                                        <td><?php echo xb_get_partner_name($e->partner_id, 'any'); ?></td>
                                        <td><?php echo $e->ref; ?></td>
                                        <td><?php echo $e->journal_name; ?></td>
                                        <td><?php echo xb_format_money($e->amount_total); ?></td>
                                        <td><?php echo xb_state_label($e->state); ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('xetuu_books/journal_entry_form/' . $e->id); ?>" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a>
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
