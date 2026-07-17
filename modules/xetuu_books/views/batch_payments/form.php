<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header">
                <span><?php echo $title; ?></span>
                <a href="<?php echo admin_url('xetuu_books/' . ($payment_type === 'inbound' ? 'batch_payments' : 'vendor_batch_payments')); ?>" class="btn btn-default btn-sm pull-right">
                    Back to List
                </a>
            </div>
            <div class="xb-card-body">
                <?php if (isset($batch)): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Batch Name:</strong> <?php echo $batch->name; ?></p>
                            <p><strong>Date:</strong> <?php echo _d($batch->date); ?></p>
                            <p><strong>Journal:</strong> <?php echo $batch->journal_name; ?></p>
                            <p><strong>Status:</strong> <?php echo xb_state_label($batch->state); ?></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h3 class="text-success mtop5">Total Amount: <?php echo xb_format_money($batch->amount); ?></h3>
                        </div>
                    </div>
                    <hr>
                    <h4>Individual Payments Included</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Invoice/Bill</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($batch->payments)): ?>
                                <?php foreach ($batch->payments as $p): ?>
                                    <tr>
                                        <td><?php echo $p->partner_name; ?></td>
                                        <td><?php echo $p->invoice_number; ?></td>
                                        <td><?php echo xb_format_money($p->amount); ?></td>
                                        <td><?php echo _d($p->date); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No payments found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <?php echo form_open(admin_url('xetuu_books/' . ($payment_type === 'inbound' ? 'batch_payment' : 'vendor_batch_payment'))); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="journal_id" class="control-label">Payment Journal</label>
                                <select name="journal_id" id="journal_id" class="selectpicker" data-width="100%" required>
                                    <option value=""></option>
                                    <?php foreach ($payment_journals as $j): ?>
                                        <option value="<?php echo $j->id; ?>"><?php echo $j->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_date_input('date', 'Payment Date', _d(date('Y-m-d')), ['required' => 'true']); ?>
                        </div>
                    </div>
                    
                    <hr>
                    <h4>Select <?php echo $payment_type === 'inbound' ? 'Invoices to Deposit' : 'Bills to Pay'; ?></h4>
                    <p class="text-muted">Select the documents you want to include in this batch.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="batch-items-table">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="check-all" /></th>
                                    <th><?php echo $payment_type === 'inbound' ? 'Customer' : 'Vendor'; ?></th>
                                    <th>Document Number</th>
                                    <th>Date</th>
                                    <th>Amount Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($unpaid_invoices)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No unpaid documents found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($unpaid_invoices as $inv): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="invoice_ids[]" class="batch-checkbox" value="<?php echo $inv->id; ?>" data-amount="<?php echo $inv->amount_residual; ?>" />
                                            </td>
                                            <td><?php echo $inv->partner_name; ?></td>
                                            <td><?php echo $inv->name; ?></td>
                                            <td><?php echo _d($inv->date); ?></td>
                                            <td><?php echo xb_format_money($inv->amount_residual); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total Selected:</strong></td>
                                    <td><strong id="batch-total">0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <?php if (function_exists('xb_render_analytic_field')): ?>
                    <div class="row mtop20">
                        <div class="col-md-6">
                            <?php echo xb_render_analytic_field($payment_type === 'inbound' ? 'batch_payment' : 'vendor_batch_payment', 0, 'Analytic Account (Cost Centre)'); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row mtop20">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-success" id="btn-save-batch" disabled>Generate Batch Payment</button>
                        </div>
                    </div>

                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.batch-checkbox');
    const totalEl = document.getElementById('batch-total');
    const btnSave = document.getElementById('btn-save-batch');

    function updateTotal() {
        let total = 0;
        let checkedCount = 0;
        
        checkboxes.forEach(function(cb) {
            if (cb.checked) {
                total += parseFloat(cb.getAttribute('data-amount')) || 0;
                checkedCount++;
            }
        });
        
        totalEl.innerText = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(total);
        btnSave.disabled = checkedCount === 0;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(function(cb) {
                cb.checked = isChecked;
            });
            updateTotal();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateTotal);
    });
});
</script>
