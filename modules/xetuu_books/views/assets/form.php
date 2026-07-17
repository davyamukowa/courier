<?php defined('BASEPATH') or exit('No direct script access allowed');
$state_colors = ['draft'=>'default','open'=>'success','paused'=>'warning','close'=>'primary','cancelled'=>'danger'];
$s_cls = $state_colors[$asset->state ?? 'draft'] ?? 'default';
$is_editable = !isset($asset) || $asset->state === 'draft';
?>
<style>
.xb-workspace{margin-top:-15px}
.xb-breadcrumb{padding:12px 0;font-size:13px;color:#6b7280}.xb-breadcrumb a{color:#1a6b3a;font-weight:500}
.xb-header-toolbar{background:#fff;padding:14px 24px;border-bottom:1px solid #e5e7eb;margin:0 -25px 20px;display:flex;justify-content:space-between;align-items:center}
.xb-badge{display:inline-block;padding:3px 10px;font-size:11px;font-weight:600;border-radius:4px;margin-left:8px;vertical-align:middle}
.xb-badge-default{background:#f3f4f6;color:#374151}.xb-badge-success{background:#dcfce7;color:#16a34a}
.xb-tabs .nav-tabs>li>a{color:#6b7280;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;padding:9px 14px;border-radius:0}
.xb-tabs .nav-tabs>li.active>a{color:#1a6b3a;border-bottom:2px solid #1a6b3a;background:transparent}
.xb-tabs{margin-bottom:20px;border-bottom:2px solid #e5e7eb}
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
        <a href="<?php echo admin_url('xetuu_books/assets'); ?>">Fixed Assets</a> &rsaquo;
        <?php echo isset($asset) ? htmlspecialchars($asset->name) : 'New Asset'; ?>
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3 style="margin:0;display:inline;font-weight:600;"><?php echo $title; ?></h3>
            <span class="xb-badge xb-badge-<?php echo $s_cls; ?>"><?php echo strtoupper($asset->state ?? 'DRAFT'); ?></span>
        </div>
        <div>
            <?php if($is_editable): ?>
            <button type="button" class="btn btn-default btn-sm" onclick="$('#asset-form').submit()">Save</button>
            <?php if(isset($asset) && $asset->state === 'draft'): ?>
            <a href="<?php echo admin_url('xetuu_books/confirm_asset/'.$asset->id); ?>"
               class="btn btn-success btn-sm"
               onclick="return confirm('Confirm asset as running?')">Confirm (Set Running)</a>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo admin_url('xetuu_books/assets'); ?>" class="btn btn-link btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="xb-tabs">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#ast-details"      data-toggle="tab">Details</a></li>
            <li><a href="#ast-depreciation" data-toggle="tab">Depreciation</a></li>
            <li><a href="#ast-accounting"   data-toggle="tab">Accounting</a></li>
        </ul>
    </div>

    <?php echo form_open(admin_url('xetuu_books/asset_form/'.(isset($asset)?$asset->id:'')), ['id'=>'asset-form']); ?>
    <div class="row">
        <div class="col-md-9">
            <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="ast-details">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Asset Information</span></div>
                        <div class="xb-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Asset Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                               value="<?php echo isset($asset)?htmlspecialchars($asset->name):''; ?>"
                                               <?php echo !$is_editable?'disabled':''; ?>>
                                    </div>
                                    <div class="form-group">
                                        <label>Asset Model</label>
                                        <select name="model_id" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="">— None —</option>
                                            <?php foreach($asset_models as $m): ?>
                                            <option value="<?php echo $m->id; ?>" <?php echo (isset($asset)&&$asset->model_id==$m->id)?'selected':''; ?>>
                                                <?php echo htmlspecialchars($m->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Acquisition Date</label>
                                        <input type="date" name="acquisition_date" class="form-control"
                                               value="<?php echo isset($asset)?$asset->acquisition_date:date('Y-m-d'); ?>"
                                               <?php echo !$is_editable?'disabled':''; ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Original Value</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">KSh</span>
                                            <input type="number" step="0.01" name="original_value" class="form-control"
                                                   id="original_value" onchange="calcBookValue()"
                                                   value="<?php echo isset($asset)?$asset->original_value:0; ?>"
                                                   <?php echo !$is_editable?'disabled':''; ?>>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Salvage Value</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">KSh</span>
                                            <input type="number" step="0.01" name="salvage_value" class="form-control"
                                                   id="salvage_value" onchange="calcBookValue()"
                                                   value="<?php echo isset($asset)?$asset->salvage_value:0; ?>"
                                                   <?php echo !$is_editable?'disabled':''; ?>>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Net Book Value</label>
                                        <input type="text" class="form-control" id="book_value_display"
                                               value="<?php echo number_format(isset($asset)?$asset->book_value:0,2); ?>" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="ast-depreciation">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Depreciation Settings</span></div>
                        <div class="xb-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Depreciation Method</label>
                                        <select name="method" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="linear"    <?php echo (isset($asset)&&$asset->method==='linear')?'selected':''; ?>>Straight Line (Linear)</option>
                                            <option value="degressive" <?php echo (isset($asset)&&$asset->method==='degressive')?'selected':''; ?>>Declining Balance</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Useful Life (Years)</label>
                                        <input type="number" name="method_number" class="form-control" min="1" max="99"
                                               value="<?php echo isset($asset)?$asset->method_number:5; ?>"
                                               <?php echo !$is_editable?'disabled':''; ?>>
                                    </div>
                                    <div class="form-group">
                                        <label>Computation Period</label>
                                        <select name="method_period" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="1"  <?php echo (isset($asset)&&$asset->method_period==1)?'selected':''; ?>>Monthly</option>
                                            <option value="3"  <?php echo (isset($asset)&&$asset->method_period==3)?'selected':''; ?>>Quarterly</option>
                                            <option value="12" <?php echo (!isset($asset)||$asset->method_period==12)?'selected':''; ?>>Annually</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Depreciation Lines -->
                    <?php if (!empty($asset_lines)): ?>
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Depreciation Schedule</span></div>
                        <div class="xb-section-body" style="padding:0;">
                            <table class="table" style="margin:0;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-right">Depreciation</th>
                                        <th class="text-right">Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($asset_lines as $i => $line): ?>
                                    <tr>
                                        <td><?php echo $i+1; ?></td>
                                        <td><?php echo $line->date; ?></td>
                                        <td><?php echo htmlspecialchars($line->name ?? ''); ?></td>
                                        <td class="text-right"><?php echo xb_format_money($line->amount); ?></td>
                                        <td class="text-right"><?php echo xb_format_money($line->remaining_value); ?></td>
                                        <td>
                                            <?php if ($line->move_id): ?>
                                            <span class="label label-success">Posted</span>
                                            <?php else: ?>
                                            <span class="label label-default">Scheduled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div role="tabpanel" class="tab-pane" id="ast-accounting">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-header"><span>Accounting Accounts</span></div>
                        <div class="xb-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Asset Account</label>
                                        <select name="account_asset_id" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="">— Select Account —</option>
                                            <?php foreach($accounts as $acc): ?>
                                            <option value="<?php echo $acc->id; ?>" <?php echo (isset($asset)&&$asset->account_asset_id==$acc->id)?'selected':''; ?>>
                                                <?php echo $acc->code.' '.$acc->name; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Depreciation Account (Balance Sheet)</label>
                                        <select name="account_depreciation_id" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="">— Select Account —</option>
                                            <?php foreach($accounts as $acc): ?>
                                            <option value="<?php echo $acc->id; ?>" <?php echo (isset($asset)&&$asset->account_depreciation_id==$acc->id)?'selected':''; ?>>
                                                <?php echo $acc->code.' '.$acc->name; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Depreciation Expense Account (P&L)</label>
                                        <select name="account_depreciation_expense_id" class="form-control" <?php echo !$is_editable?'disabled':''; ?>>
                                            <option value="">— Select Account —</option>
                                            <?php foreach($accounts as $acc): ?>
                                            <option value="<?php echo $acc->id; ?>" <?php echo (isset($asset)&&$asset->account_depreciation_expense_id==$acc->id)?'selected':''; ?>>
                                                <?php echo $acc->code.' '.$acc->name; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="xb-sidebar">
                <div class="xb-sidebar-block">
                    <h4>Asset Summary</h4>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Status</span>
                        <span class="xb-info-val"><?php echo ucfirst($asset->state ?? 'Draft'); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Original Value</span>
                        <span class="xb-info-val"><?php echo xb_format_money($asset->original_value ?? 0); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Book Value</span>
                        <span class="xb-info-val" style="color:#1a6b3a;"><?php echo xb_format_money($asset->book_value ?? 0); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Depreciation</span>
                        <span class="xb-info-val text-danger"><?php echo xb_format_money(($asset->original_value??0)-($asset->book_value??0)); ?></span>
                    </div>
                </div>
                <div class="xb-sidebar-block">
                    <h4>Quick Links</h4>
                    <a href="<?php echo admin_url('xetuu_books/reports/depreciation_schedule'); ?>"
                       class="btn btn-default btn-sm btn-block">
                        <i class="fa fa-table"></i> Depreciation Schedule
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
function calcBookValue() {
    var orig = parseFloat($('#original_value').val()) || 0;
    var salv = parseFloat($('#salvage_value').val()) || 0;
    $('#book_value_display').val((orig - salv).toFixed(2));
}
$(document).ready(function(){ calcBookValue(); });
</script>
