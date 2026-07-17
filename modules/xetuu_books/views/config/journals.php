<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Journals</span>
                <button class="btn btn-primary xb-btn-primary btn-sm pull-right" data-toggle="modal" data-target="#journalModal" onclick="resetJournalForm()">New Journal</button>
            </div>
            <div class="xb-card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Default Account</th>
                                <th>Dashboard</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($journals as $j): ?>
                            <tr>
                                <td><?php echo $j->code; ?></td>
                                <td><?php echo $j->name; ?></td>
                                <td><?php echo $j->type; ?></td>
                                <td><?php echo $j->default_account_name; ?></td>
                                <td><?php echo $j->show_on_dashboard ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <button class="btn btn-default btn-icon btn-sm" onclick='editJournal(<?php echo json_encode($j); ?>)'><i class="fa fa-pencil"></i></button>
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

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('xetuu_books/config/journals'), ['id'=>'journal-form']); ?>
            <input type="hidden" name="id" id="journal_id">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="journalModalTitle">New Journal</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Journal Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Short Code</label>
                    <input type="text" name="code" id="code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="type" class="form-control selectpicker" required>
                        <option value="Sale">Sale</option>
                        <option value="Purchase">Purchase</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank">Bank</option>
                        <option value="General">General</option>
                        <option value="Situation">Situation</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Default Account (Optional)</label>
                    <select name="account_id" id="account_id" class="form-control selectpicker" data-live-search="true">
                        <option value=""></option>
                        <?php foreach($gl_accounts as $acc): ?>
                            <option value="<?php echo $acc->id; ?>"><?php echo $acc->code . ' - ' . $acc->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="show_on_dashboard" id="show_on_dashboard" value="1" checked> Show on Dashboard
                    </label>
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
function resetJournalForm() {
    $('#journalModalTitle').text('New Journal');
    $('#journal_id').val('');
    $('#journal-form')[0].reset();
    $('#type').selectpicker('refresh');
    $('#account_id').selectpicker('refresh');
}

function editJournal(j) {
    $('#journalModalTitle').text('Edit Journal');
    $('#journal_id').val(j.id);
    $('#name').val(j.name);
    $('#code').val(j.code);
    $('#type').selectpicker('val', j.type);
    $('#account_id').selectpicker('val', j.account_id);
    $('#show_on_dashboard').prop('checked', j.show_on_dashboard == 1);
    $('#journalModal').modal('show');
}
</script>
