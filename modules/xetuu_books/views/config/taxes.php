<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Taxes</span>
                <button class="btn btn-primary xb-btn-primary btn-sm pull-right" data-toggle="modal" data-target="#taxModal" onclick="resetTaxForm()">New Tax</button>
            </div>
            <div class="xb-card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type Use</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($taxes as $tax): ?>
                            <tr>
                                <td><?php echo $tax->name; ?></td>
                                <td><?php echo ucfirst($tax->type_tax_use); ?></td>
                                <td><?php echo $tax->amount; ?>%</td>
                                <td>
                                    <button class="btn btn-default btn-icon btn-sm" onclick='editTax(<?php echo json_encode($tax); ?>)'><i class="fa fa-pencil"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tax Modal -->
<div class="modal fade" id="taxModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('xetuu_books/config/taxes'), ['id'=>'tax-form']); ?>
            <input type="hidden" name="id" id="tax_id">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="taxModalTitle">New Tax</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Tax Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Amount (%)</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tax Scope</label>
                    <select name="type_tax_use" id="type_tax_use" class="form-control selectpicker" required>
                        <option value="sale">Sales</option>
                        <option value="purchase">Purchases</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tax Account</label>
                    <select name="account_id" id="account_id" class="form-control selectpicker">
                        <option value=""></option>
                        <?php foreach($tax_accounts as $acc): ?>
                            <option value="<?php echo $acc->id; ?>"><?php echo $acc->code . ' - ' . $acc->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary xb-btn-primary">Save changes</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
function resetTaxForm() {
    $('#taxModalTitle').text('New Tax');
    $('#tax_id').val('');
    $('#tax-form')[0].reset();
    $('#type_tax_use').selectpicker('refresh');
    $('#account_id').selectpicker('refresh');
}

function editTax(t) {
    $('#taxModalTitle').text('Edit Tax');
    $('#tax_id').val(t.id);
    $('#name').val(t.name);
    $('#amount').val(t.amount);
    $('#type_tax_use').selectpicker('val', t.type_tax_use);
    $('#account_id').selectpicker('val', t.account_id);
    $('#taxModal').modal('show');
}
</script>
