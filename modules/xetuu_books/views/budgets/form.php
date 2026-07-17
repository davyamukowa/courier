<?php defined('BASEPATH') or exit('No direct script access allowed');
$state_colors = ['draft'=>'default','confirm'=>'info','validate'=>'success','done'=>'primary','cancel'=>'danger'];
$s_cls = $state_colors[$budget->state ?? 'draft'] ?? 'default';
$is_editable = !isset($budget) || in_array($budget->state, ['draft','confirm']);
?>
<style>
.xb-workspace{margin-top:-15px}
.xb-breadcrumb{padding:12px 0;font-size:13px;color:#6b7280}.xb-breadcrumb a{color:#1a6b3a;font-weight:500}
.xb-header-toolbar{background:#fff;padding:14px 24px;border-bottom:1px solid #e5e7eb;margin:0 -25px 20px;display:flex;justify-content:space-between;align-items:center}
.xb-badge{display:inline-block;padding:3px 10px;font-size:11px;font-weight:600;border-radius:4px;margin-left:8px;vertical-align:middle}
.xb-badge-default{background:#f3f4f6;color:#374151}.xb-badge-info{background:#dbeafe;color:#1d4ed8}.xb-badge-success{background:#dcfce7;color:#16a34a}
.xb-collapsible-section{background:#fff;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px}
.xb-section-header{padding:11px 18px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;display:flex;justify-content:space-between}
.xb-section-body{padding:18px}
.xb-sidebar{background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:18px;margin-bottom:16px}
.xb-sidebar-block{margin-bottom:20px}
.xb-sidebar-block h4{font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:700;margin-top:0;margin-bottom:8px}
.xb-info-row{display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px}
.xb-info-label{color:#6b7280}.xb-info-val{font-weight:500}
</style>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/budgets'); ?>">Budgets</a> &rsaquo;
        <?php echo isset($budget) ? htmlspecialchars($budget->name) : 'New Budget'; ?>
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3 style="margin:0;display:inline;font-weight:600;"><?php echo $title; ?></h3>
            <span class="xb-badge xb-badge-<?php echo $s_cls; ?>"><?php echo strtoupper($budget->state ?? 'DRAFT'); ?></span>
        </div>
        <div>
            <?php if($is_editable): ?>
            <button type="button" class="btn btn-default btn-sm" onclick="$('#budget-form').submit()">Save</button>
            <?php if(isset($budget) && $budget->state === 'draft'): ?>
            <a href="<?php echo admin_url('xetuu_books/confirm_budget/'.$budget->id); ?>"
               class="btn btn-primary xb-btn-primary btn-sm"
               onclick="return confirm('Confirm this budget?')">Confirm</a>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo admin_url('xetuu_books/budgets'); ?>" class="btn btn-link btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <?php echo form_open(admin_url('xetuu_books/budget_form/'.(isset($budget)?$budget->id:'')), ['id'=>'budget-form']); ?>
    <div class="row">
        <div class="col-md-9">
            <!-- Header -->
            <div class="xb-collapsible-section">
                <div class="xb-section-header"><span>Budget Information</span></div>
                <div class="xb-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Budget Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo isset($budget)?htmlspecialchars($budget->name):''; ?>"
                                       <?php echo !$is_editable?'disabled':''; ?>>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From <span class="text-danger">*</span></label>
                                <input type="date" name="date_from" class="form-control" required
                                       value="<?php echo isset($budget)?$budget->date_from:date('Y-01-01'); ?>"
                                       <?php echo !$is_editable?'disabled':''; ?>>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To <span class="text-danger">*</span></label>
                                <input type="date" name="date_to" class="form-control" required
                                       value="<?php echo isset($budget)?$budget->date_to:date('Y-12-31'); ?>"
                                       <?php echo !$is_editable?'disabled':''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Lines -->
            <div class="xb-collapsible-section">
                <div class="xb-section-header"><span>Budget Lines</span></div>
                <div class="xb-section-body" style="padding:0;">
                    <table class="table" id="budget-lines" style="margin:0;">
                        <thead style="background:#f9fafb;">
                            <tr>
                                <th>Account</th>
                                <th class="text-right">Planned Amount</th>
                                <th class="text-right">Actual (YTD)</th>
                                <th class="text-right">Variance</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $lines = $budget_lines ?? [];
                            if (empty($lines)) $lines = [null];
                            foreach ($lines as $i => $line):
                            ?>
                            <tr class="bgt-row">
                                <td>
                                    <select name="lines[<?php echo $i; ?>][account_id]" class="form-control input-sm" <?php echo !$is_editable?'disabled':''; ?>>
                                        <?php foreach($accounts as $acc): ?>
                                        <option value="<?php echo $acc->id; ?>" <?php echo (isset($line)&&$line->account_id==$acc->id)?'selected':''; ?>><?php echo $acc->code.' '.$acc->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[<?php echo $i; ?>][planned_amount]"
                                           class="form-control input-sm bgt-planned text-right"
                                           value="<?php echo isset($line)?$line->planned_amount:0; ?>"
                                           onchange="bgtCalc()" <?php echo !$is_editable?'disabled':''; ?>>
                                </td>
                                <td class="text-right text-muted"><?php echo xb_format_money($line->practical_amount ?? 0); ?></td>
                                <td class="text-right <?php echo (($line->practical_amount??0)>($line->planned_amount??0))?'text-danger':'text-success'; ?>">
                                    <?php echo xb_format_money(($line->planned_amount??0)-($line->practical_amount??0)); ?>
                                </td>
                                <td>
                                    <?php if($is_editable): ?>
                                    <button type="button" class="btn btn-danger btn-xs" onclick="$(this).closest('tr').remove();bgtCalc()"><i class="fa fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background:#f0fdf4;font-weight:700;">
                            <tr>
                                <td>Total</td>
                                <td class="text-right" id="bgt-total">0.00</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php if($is_editable): ?>
                    <div style="padding:10px 18px;">
                        <button type="button" class="btn btn-default btn-sm" onclick="bgtAddLine()"><i class="fa fa-plus"></i> Add Line</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="xb-sidebar">
                <div class="xb-sidebar-block">
                    <h4>Budget Info</h4>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Status</span>
                        <span class="xb-info-val"><?php echo ucfirst($budget->state ?? 'Draft'); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Created By</span>
                        <span class="xb-info-val"><?php echo isset($budget->created_by)?'Staff #'.$budget->created_by:'—'; ?></span>
                    </div>
                </div>
                <div class="xb-sidebar-block">
                    <h4>Quick Links</h4>
                    <a href="<?php echo admin_url('xetuu_books/reports/profit_loss'); ?>" class="btn btn-default btn-sm btn-block">
                        <i class="fa fa-bar-chart"></i> P&amp;L Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
var bgtIdx = <?php echo max(count($budget_lines ?? [null]), 1); ?>;
var bgtAccounts = <?php echo json_encode(array_map(fn($a)=>['id'=>$a->id,'text'=>$a->code.' '.$a->name], $accounts)); ?>;

function bgtAddLine() {
    var opts = bgtAccounts.map(a=>'<option value="'+a.id+'">'+a.text+'</option>').join('');
    var html = '<tr class="bgt-row">'
        +'<td><select name="lines['+bgtIdx+'][account_id]" class="form-control input-sm">'+opts+'</select></td>'
        +'<td><input type="number" step="0.01" name="lines['+bgtIdx+'][planned_amount]" class="form-control input-sm bgt-planned text-right" value="0" onchange="bgtCalc()"></td>'
        +'<td class="text-right text-muted">0.00</td>'
        +'<td class="text-right text-success">0.00</td>'
        +'<td><button type="button" class="btn btn-danger btn-xs" onclick="$(this).closest(\'tr\').remove();bgtCalc()"><i class="fa fa-trash"></i></button></td>'
        +'</tr>';
    $('#budget-lines tbody').append(html);
    bgtIdx++;
}

function bgtCalc() {
    var total = 0;
    $('.bgt-planned').each(function(){ total += parseFloat($(this).val())||0; });
    $('#bgt-total').text(total.toFixed(2));
}

$(document).ready(function(){ bgtCalc(); });
</script>
