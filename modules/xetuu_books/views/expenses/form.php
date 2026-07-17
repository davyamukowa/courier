<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_draft = (!isset($move) || $move->state == 'draft');
$s_map = ['draft'=>'default','posted'=>'success','cancel'=>'danger'];
$s_cls = $s_map[$move->state ?? 'draft'] ?? 'default';
?>
<style>
.xb-workspace{margin-top:-15px}
.xb-breadcrumb{padding:12px 0;font-size:13px;color:#6b7280}.xb-breadcrumb a{color:#1a6b3a;font-weight:500}
.xb-header-toolbar{background:#fff;padding:14px 24px;border-bottom:1px solid #e5e7eb;margin:0 -25px 20px;display:flex;justify-content:space-between;align-items:center}
.xb-badge{display:inline-block;padding:3px 10px;font-size:11px;font-weight:600;border-radius:4px;margin-left:8px;vertical-align:middle}
.xb-badge-default{background:#f3f4f6;color:#374151}.xb-badge-success{background:#dcfce7;color:#16a34a}.xb-badge-danger{background:#fee2e2;color:#dc2626}
.xb-tabs .nav-tabs>li>a{color:#6b7280;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;padding:9px 14px;border-radius:0}
.xb-tabs .nav-tabs>li.active>a{color:#1a6b3a;border-bottom:2px solid #1a6b3a;background:transparent}
.xb-tabs{margin-bottom:20px;border-bottom:2px solid #e5e7eb}
.xb-collapsible-section{background:#fff;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px}
.xb-section-header{padding:11px 18px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;cursor:pointer;display:flex;justify-content:space-between}
.xb-section-body{padding:18px}
.xb-sidebar{background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:18px;margin-bottom:16px}
.xb-sidebar-block{margin-bottom:20px}
.xb-sidebar-block h4{font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:700;margin-top:0;margin-bottom:8px;letter-spacing:.05em}
.xb-info-row{display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px}
.xb-info-label{color:#6b7280}.xb-info-val{font-weight:500}
</style>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/expenses'); ?>">Expenses</a> &rsaquo;
        <?php echo isset($move) ? ($move->name ?? 'Draft') : 'New Expense'; ?>
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3 style="margin:0;display:inline;font-weight:600;"><?php echo $title; ?></h3>
            <span class="xb-badge xb-badge-<?php echo $s_cls; ?>"><?php echo strtoupper($move->state ?? 'DRAFT'); ?></span>
        </div>
        <div>
            <?php if($is_draft): ?>
            <button type="button" class="btn btn-default btn-sm" onclick="$('#exp-form').submit()">Save Draft</button>
            <?php if(isset($move)): ?>
            <button type="button" class="btn btn-primary xb-btn-primary btn-sm"
                    onclick="if(confirm('Post this expense?')) $.post('<?php echo admin_url('xetuu_books/post_expense/'.$move->id); ?>',function(r){var d=JSON.parse(r);if(d.success)location.reload();else alert(d.message);})">
                Post
            </button>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo admin_url('xetuu_books/expenses'); ?>" class="btn btn-link btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="xb-tabs">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#exp-details" data-toggle="tab">Details</a></li>
            <li><a href="#exp-items"   data-toggle="tab">Lines</a></li>
            <li><a href="#exp-notes"   data-toggle="tab">Notes</a></li>
        </ul>
    </div>

    <?php echo form_open(admin_url('xetuu_books/expense_form/'.(isset($move)?$move->id:'')), ['id'=>'exp-form']); ?>
    <div class="row">
        <div class="col-md-9">
            <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="exp-details">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header" onclick="$(this).next().slideToggle()">
                            <span>Expense Details</span><i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="xb-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employee / Vendor</label>
                                        <input type="text" name="partner_id" class="form-control"
                                               value="<?php echo isset($move)?$move->partner_id:''; ?>"
                                               placeholder="Partner ID" <?php echo !$is_draft?'disabled':''; ?>>
                                    </div>
                                    <div class="form-group">
                                        <label>Expense Date</label>
                                        <input type="date" name="date" class="form-control"
                                               value="<?php echo isset($move)?$move->date:date('Y-m-d'); ?>"
                                               <?php echo !$is_draft?'disabled':''; ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reference</label>
                                        <input type="text" name="ref" class="form-control"
                                               value="<?php echo isset($move)?htmlspecialchars($move->ref??''):''; ?>"
                                               placeholder="Receipt / Invoice #" <?php echo !$is_draft?'disabled':''; ?>>
                                    </div>
                                    <div class="form-group">
                                        <label>Journal</label>
                                        <select name="journal_id" class="form-control" <?php echo !$is_draft?'disabled':''; ?>>
                                            <?php foreach($journals as $j): ?>
                                            <option value="<?php echo $j->id; ?>" <?php echo (isset($move)&&$move->journal_id==$j->id)?'selected':''; ?>><?php echo $j->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="exp-items">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Expense Lines</span></div>
                        <div class="xb-section-body" style="padding:0;">
                            <table class="table" id="exp-lines" style="margin:0;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th>Expense Account</th>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $lines = $invoice_lines ?? [];
                                    if (empty($lines)) $lines = [null];
                                    foreach ($lines as $i => $line):
                                    ?>
                                    <tr class="exp-row">
                                        <td>
                                            <select name="lines[<?php echo $i; ?>][account_id]" class="form-control input-sm" <?php echo !$is_draft?'disabled':''; ?>>
                                                <?php foreach($expense_accounts as $acc): ?>
                                                <option value="<?php echo $acc->id; ?>" <?php echo (isset($line)&&$line->account_id==$acc->id)?'selected':''; ?>><?php echo $acc->code.' '.$acc->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="lines[<?php echo $i; ?>][name]" class="form-control input-sm"
                                                   value="<?php echo isset($line)?htmlspecialchars($line->name):''; ?>"
                                                   <?php echo !$is_draft?'disabled':''; ?>></td>
                                        <td><input type="number" step="0.01" name="lines[<?php echo $i; ?>][price_unit]" class="form-control input-sm exp-amount"
                                                   value="<?php echo isset($line)?$line->price_unit:0; ?>" onchange="expCalc()" <?php echo !$is_draft?'disabled':''; ?>></td>
                                        <td>
                                            <?php if($is_draft): ?>
                                            <button type="button" class="btn btn-danger btn-xs" onclick="$(this).closest('tr').remove();expCalc()"><i class="fa fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if($is_draft): ?>
                            <div style="padding:10px 18px;">
                                <button type="button" class="btn btn-default btn-sm" onclick="expAddLine()"><i class="fa fa-plus"></i> Add Line</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-md-offset-8">
                            <table class="table text-right" style="border:1px solid #e5e7eb;background:#fff;border-radius:6px;">
                                <tr style="font-weight:700;background:#f9fafb;">
                                    <td>Total</td>
                                    <td><h4 id="exp-total" style="margin:0;">0.00</h4></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="exp-notes">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Notes</span></div>
                        <div class="xb-section-body">
                            <textarea name="narration" class="form-control" rows="4"
                                      placeholder="Internal notes..." <?php echo !$is_draft?'disabled':''; ?>><?php echo isset($move)?htmlspecialchars($move->narration??''):''; ?></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="xb-sidebar">
                <div class="xb-sidebar-block">
                    <h4>Summary</h4>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Reference</span>
                        <span class="xb-info-val"><?php echo isset($move)&&$move->name?$move->name:'Draft'; ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Total</span>
                        <span class="xb-info-val"><?php echo xb_format_money(isset($move)?$move->amount_total:0); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Status</span>
                        <span class="xb-info-val"><?php echo ucfirst($move->state ?? 'Draft'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
var expIdx = <?php echo max(count($invoice_lines ?? [null]), 1); ?>;

function expAddLine() {
    var accounts = <?php echo json_encode(array_map(fn($a)=>['id'=>$a->id,'text'=>$a->code.' '.$a->name], $expense_accounts)); ?>;
    var opts = accounts.map(a=>'<option value="'+a.id+'">'+a.text+'</option>').join('');
    var html = '<tr class="exp-row">'
        +'<td><select name="lines['+expIdx+'][account_id]" class="form-control input-sm">'+opts+'</select></td>'
        +'<td><input type="text" name="lines['+expIdx+'][name]" class="form-control input-sm" placeholder="Description"></td>'
        +'<td><input type="number" step="0.01" name="lines['+expIdx+'][price_unit]" class="form-control input-sm exp-amount" value="0" onchange="expCalc()"></td>'
        +'<td><button type="button" class="btn btn-danger btn-xs" onclick="$(this).closest(\'tr\').remove();expCalc()"><i class="fa fa-trash"></i></button></td>'
        +'</tr>';
    $('#exp-lines tbody').append(html);
    expIdx++;
}

function expCalc() {
    var total = 0;
    $('.exp-amount').each(function(){ total += parseFloat($(this).val())||0; });
    $('#exp-total').text(total.toFixed(2));
}

$(document).ready(function(){ expCalc(); });
</script>
