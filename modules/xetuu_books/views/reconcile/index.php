<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .xb-recon-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 8px 8px 0 0;
    }
    .xb-recon-table th { background: #f1f5f9; text-transform: uppercase; font-size: 11px; color: #64748b; }
    .xb-recon-row { cursor: pointer; transition: background 0.2s; }
    .xb-recon-row:hover { background: #f8fafc; }
    .xb-recon-row.selected { background: #e0f2fe; }
    .xb-recon-panel { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; }
    
    .xb-action-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: #1e293b; color: white; padding: 15px 30px;
        z-index: 9999; display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        transform: translateY(100%); transition: transform 0.3s ease;
    }
    .xb-action-bar.visible { transform: translateY(0); }
    .xb-diff { font-size: 20px; font-weight: 700; font-family: monospace; }
    .xb-diff.match { color: #10b981; }
    .xb-diff.unmatch { color: #ef4444; }
</style>

<div class="row" style="padding-bottom: 80px;">
    <div class="col-md-12">
        <div class="xb-card">
            <div class="xb-card-header d-flex justify-content-between align-items-center">
                <span>Bank Reconciliation</span>
                <div class="pull-right">
                    <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#importStatementModal"><i class="fa fa-upload"></i> Import Statement</button>
                    <button class="btn btn-info btn-sm" id="btn-auto-match"><i class="fa fa-magic"></i> Auto-Match Exact</button>
                </div>
            </div>
            <div class="xb-card-body" style="background: #f8fafc; padding: 20px;">
                <form class="form-inline mbot20" method="GET">
                    <div class="form-group">
                        <label>Select Bank Journal: </label>
                        <select name="journal_id" class="form-control selectpicker" onchange="this.form.submit()">
                            <?php foreach($bank_journals as $j): ?>
                                <option value="<?php echo $j->id; ?>" <?php echo $selected_journal == $j->id ? 'selected' : ''; ?>><?php echo $j->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <div class="row">
                    <!-- Left Side: Bank Statement Lines -->
                    <div class="col-md-6">
                        <div class="xb-recon-panel">
                            <div class="xb-recon-header">
                                <h4 class="m-0"><i class="fa fa-university text-primary"></i> Bank Statement Lines</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover xb-recon-table m-0">
                                    <thead>
                                        <tr>
                                            <th width="50">Select</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($statement_lines)): ?>
                                        <tr><td colspan="4" class="text-center text-muted" style="padding: 30px;">No unreconciled statement lines</td></tr>
                                        <?php else: ?>
                                            <?php foreach($statement_lines as $st): ?>
                                            <tr class="xb-recon-row st-row" data-id="<?php echo $st->id; ?>" data-amount="<?php echo $st->amount; ?>" data-date="<?php echo $st->date; ?>">
                                                <td><input type="checkbox" class="st-cb"></td>
                                                <td><?php echo _d($st->date); ?></td>
                                                <td><?php echo $st->payment_ref ?: 'Bank Transaction'; ?></td>
                                                <td class="text-right font-weight-bold <?php echo $st->amount < 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <?php echo app_format_money($st->amount, get_base_currency()); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Accounting Ledger Lines -->
                    <div class="col-md-6">
                        <div class="xb-recon-panel">
                            <div class="xb-recon-header">
                                <h4 class="m-0"><i class="fa fa-book text-info"></i> Accounting Entries</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover xb-recon-table m-0">
                                    <thead>
                                        <tr>
                                            <th width="50">Select</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-right">Amount (Db - Cr)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($unreconciled)): ?>
                                        <tr><td colspan="4" class="text-center text-muted" style="padding: 30px;">No unreconciled accounting lines in this journal</td></tr>
                                        <?php else: ?>
                                            <?php foreach($unreconciled as $ll): 
                                                $net_amt = $ll->debit - $ll->credit;
                                            ?>
                                            <tr class="xb-recon-row ll-row" data-id="<?php echo $ll->id; ?>" data-amount="<?php echo $net_amt; ?>" data-date="<?php echo $ll->date; ?>">
                                                <td><input type="checkbox" class="ll-cb"></td>
                                                <td><?php echo _d($ll->date); ?></td>
                                                <td><?php echo $ll->name ?: 'Ledger Entry'; ?> <br><small class="text-muted"><?php echo $ll->account_name; ?></small></td>
                                                <td class="text-right font-weight-bold <?php echo $net_amt < 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <?php echo app_format_money($net_amt, get_base_currency()); ?>
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
        </div>
    </div>
</div>

<!-- Floating Action Bar -->
<div class="xb-action-bar" id="recon-action-bar">
    <div>
        <span style="font-size: 14px; opacity: 0.8; margin-right: 20px;">Selected Bank: <b id="tot-bank">0.00</b></span>
        <span style="font-size: 14px; opacity: 0.8;">Selected Ledger: <b id="tot-ledger">0.00</b></span>
    </div>
    <div style="display: flex; align-items: center;">
        <div style="margin-right: 30px; text-align: right;">
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7;">Difference</div>
            <div class="xb-diff unmatch" id="diff-display">0.00</div>
        </div>
        <button class="btn btn-success" id="btn-reconcile" disabled><i class="fa fa-check-circle"></i> Validate Reconciliation</button>
    </div>
</div>

<!-- Import Modal (Unchanged) -->
<div class="modal fade" id="importStatementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart(admin_url('xetuu_books/import_statement'), ['id'=>'import-statement-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Import Bank Statement</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Journal</label>
                    <select name="journal_id" class="form-control selectpicker">
                        <?php foreach($bank_journals as $j): ?>
                            <option value="<?php echo $j->id; ?>" <?php echo $selected_journal == $j->id ? 'selected' : ''; ?>><?php echo $j->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>File (CSV, OFX, QIF)</label>
                    <input type="file" name="statement_file" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary xb-btn-primary">Import</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let st_selected = [];
    let ll_selected = [];

    function updateReconState() {
        let st_tot = 0;
        let ll_tot = 0;
        
        st_selected = [];
        ll_selected = [];

        $('.st-cb:checked').each(function() {
            let row = $(this).closest('tr');
            st_tot += parseFloat(row.data('amount'));
            st_selected.push(row.data('id'));
        });

        $('.ll-cb:checked').each(function() {
            let row = $(this).closest('tr');
            ll_tot += parseFloat(row.data('amount'));
            ll_selected.push(row.data('id'));
        });

        $('#tot-bank').text(st_tot.toFixed(2));
        $('#tot-ledger').text(ll_tot.toFixed(2));

        let diff = Math.abs(st_tot - ll_tot);
        $('#diff-display').text(diff.toFixed(2));

        if (st_selected.length > 0 || ll_selected.length > 0) {
            $('#recon-action-bar').addClass('visible');
        } else {
            $('#recon-action-bar').removeClass('visible');
        }

        if (st_selected.length > 0 && ll_selected.length > 0 && diff < 0.01) {
            $('#diff-display').removeClass('unmatch').addClass('match');
            $('#btn-reconcile').prop('disabled', false);
        } else {
            $('#diff-display').removeClass('match').addClass('unmatch');
            $('#btn-reconcile').prop('disabled', true);
        }
    }

    $('.xb-recon-row').on('click', function(e) {
        if(e.target.type !== 'checkbox') {
            let cb = $(this).find('input[type="checkbox"]');
            cb.prop('checked', !cb.prop('checked'));
        }
        $(this).toggleClass('selected', $(this).find('input[type="checkbox"]').prop('checked'));
        updateReconState();
    });

    $('#btn-reconcile').on('click', function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validating...');
        
        $.post(admin_url + 'xetuu_books/do_bank_reconcile', {
            statement_line_ids: st_selected,
            ledger_line_ids: ll_selected
        }, function(res) {
            if(res.success) {
                alert_float('success', 'Reconciliation successful!');
                window.location.reload();
            } else {
                alert_float('danger', res.message);
                btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Validate Reconciliation');
            }
        }, 'json');
    });

    $('#btn-auto-match').on('click', function() {
        // Auto-match exact amounts & dates (1-to-1)
        $('.st-cb').prop('checked', false);
        $('.ll-cb').prop('checked', false);
        $('.xb-recon-row').removeClass('selected');

        let matched_st = {};
        let matched_ll = {};

        $('.st-row').each(function() {
            let st_row = $(this);
            let amt = parseFloat(st_row.data('amount')).toFixed(2);
            let date = st_row.data('date');

            if (matched_st[st_row.data('id')]) return; // Already matched

            // Find matching ledger
            $('.ll-row').each(function() {
                let ll_row = $(this);
                if (matched_ll[ll_row.data('id')]) return;

                if (parseFloat(ll_row.data('amount')).toFixed(2) === amt && ll_row.data('date') === date) {
                    st_row.find('.st-cb').prop('checked', true);
                    st_row.addClass('selected');
                    ll_row.find('.ll-cb').prop('checked', true);
                    ll_row.addClass('selected');
                    
                    matched_st[st_row.data('id')] = true;
                    matched_ll[ll_row.data('id')] = true;
                    return false; // Break inner loop
                }
            });
        });
        
        updateReconState();
        if (Object.keys(matched_st).length > 0) {
            alert_float('success', Object.keys(matched_st).length + ' exact matches found!');
        } else {
            alert_float('warning', 'No exact matches found.');
        }
    });
});
</script>
