<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$is_draft = (!isset($move) || $move->state == 'draft');
$status_color = 'default';
if(isset($move)) {
    if($move->state == 'draft') $status_color = 'default';
    else if($move->state == 'posted' && $move->payment_state == 'paid') $status_color = 'success';
    else if($move->state == 'posted') $status_color = 'primary';
    else if($move->state == 'cancel') $status_color = 'danger';
}
?>
<style>
/* Enterprise Workspace Layout Styles */
.xb-workspace { margin-top: 0; }
.xb-breadcrumb { padding: 15px 0; font-size: 13px; color: #6b7280; }
.xb-breadcrumb a { color: #1a6b3a; font-weight: 500; }
.xb-header-toolbar { background: #fff; padding: 15px 25px; border-bottom: 1px solid #e5e7eb; margin: 0 -25px 20px -25px; display: flex; justify-content: space-between; align-items: center; }
.xb-header-title h3 { margin: 0; display: inline-block; font-weight: 600; color: #111827; }
.xb-badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; margin-left: 10px; vertical-align: middle; }
.xb-badge-default { background: #f3f4f6; color: #374151; }
.xb-badge-primary { background: #e0e7ff; color: #4f46e5; }
.xb-badge-success { background: #dcfce7; color: #16a34a; }
.xb-badge-danger { background: #fee2e2; color: #dc2626; }
.xb-badge-warning { background: #fef3c7; color: #d97706; }
.xb-tabs { margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
.xb-tabs .nav-tabs { border-bottom: none; }
.xb-tabs .nav-tabs>li>a { color: #6b7280; font-weight: 500; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: 10px 15px; border-radius: 0; }
.xb-tabs .nav-tabs>li.active>a, .xb-tabs .nav-tabs>li.active>a:hover, .xb-tabs .nav-tabs>li.active>a:focus { color: #1a6b3a; border-bottom: 2px solid #1a6b3a; background: transparent; }
.xb-collapsible-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px; }
.xb-section-header { padding: 12px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; border-radius: 6px 6px 0 0; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; }
.xb-section-body { padding: 20px; }
.xb-sidebar { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
.xb-sidebar-block { margin-bottom: 25px; }
.xb-sidebar-block h4 { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-top: 0; margin-bottom: 10px; letter-spacing: 0.05em; }
.xb-info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
.xb-info-label { color: #6b7280; }
.xb-info-val { font-weight: 500; color: #111827; }

/* Item Row Selects */
.bootstrap-select.btn-group .dropdown-toggle .filter-option { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Totals Card */
.xb-totals-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.xb-totals-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 18px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
}
.xb-totals-row:last-child { border-bottom: none; }
.xb-totals-label { color: #6b7280; font-weight: 500; }
.xb-totals-value { color: #374151; font-weight: 600; font-variant-numeric: tabular-nums; }
.xb-totals-final {
    background: #f0fdf4;
    border-top: 2px solid #bbf7d0;
    padding: 14px 18px;
}
.xb-totals-label-final { color: #14532d; font-weight: 700; font-size: 14px; }
.xb-totals-value-final { color: #15803d; font-weight: 700; font-size: 20px; font-variant-numeric: tabular-nums; }
</style>

<div class="xb-workspace">
    
    <!-- Breadcrumbs -->
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> > 
        <a href="#">Vendors</a> > 
        <a href="<?php echo admin_url('xetuu_books/bills'); ?>">Bills</a> > 
        <?php echo isset($move) ? $move->name : 'New Bill'; ?>
    </div>

    <!-- Header Toolbar -->
    <div class="xb-header-toolbar">
        <div class="xb-header-title">
            <h3><?php echo isset($move) ? $move->name : 'New Bill'; ?></h3>
            <span class="xb-badge xb-badge-<?php echo $status_color; ?>">
                <?php echo isset($move) ? strtoupper($move->state) : 'DRAFT'; ?>
            </span>
            <?php if(isset($move) && $move->state == 'posted' && $move->payment_state == 'not_paid'): ?>
            <span class="xb-badge xb-badge-danger">UNPAID</span>
            <?php elseif(isset($move) && $move->state == 'posted' && $move->payment_state == 'partial'): ?>
            <span class="xb-badge xb-badge-warning">PARTIAL</span>
            <?php endif; ?>
        </div>
        <div class="xb-header-actions">
                <?php if($is_draft): ?>
                    <button type="submit" form="bill-form" class="btn btn-primary" style="padding: 8px 24px; font-weight: 500; min-width: 100px;">Save</button>
                <?php endif; ?>
                
                <?php if(isset($move)): ?>
                    <?php if($move->state == 'draft'): ?>
                        <button type="button" class="btn btn-success" style="padding: 8px 24px; font-weight: 500; min-width: 100px;" onclick="confirm_bill(<?php echo $move->id; ?>)">Confirm</button>
                    <?php endif; ?>
                    
                    <?php if($move->state == 'posted' && $move->payment_state != 'paid'): ?>
                        <button type="button" class="btn btn-info" style="padding: 8px 24px; font-weight: 500; min-width: 160px;" onclick="register_payment(<?php echo $move->id; ?>, <?php echo (float)$move->amount_residual; ?>)"><i class="fa fa-money"></i> Register Payment</button>
                    <?php endif; ?>
                <?php endif; ?>
            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-ellipsis-v"></i></button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="xb-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#items" aria-controls="items" role="tab" data-toggle="tab">Invoice Lines</a></li>
            <?php if(isset($move) && $move->state == 'posted'): ?>
            <li role="presentation"><a href="#journal_items" aria-controls="journal_items" role="tab" data-toggle="tab">Journal Items</a></li>
            <?php endif; ?>
            <li role="presentation"><a href="#other_info" aria-controls="other_info" role="tab" data-toggle="tab">Other Info</a></li>
        </ul>
    </div>

    <?php echo form_open(admin_url('xetuu_books/bill_form/'.(isset($move) ? $move->id : '')), ['id'=>'bill-form']); ?>
    <div class="row">
        <!-- MAIN WORKSPACE -->
        <div class="col-md-9">
            <!-- Basic Information (Always visible above tabs) -->
            <div class="xb-collapsible-section" style="margin-bottom: 20px;">
                <div class="xb-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor <span class="text-danger">*</span></label>
                                <select name="partner_id" class="form-control selectpicker" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                    <option value=""></option>
                                    <?php foreach($vendors as $v):
                                        $selected = (isset($move) && $move->partner_id == $v->userid) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $v->userid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($v->company); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Bill Reference</label>
                                <input type="text" name="ref" class="form-control" value="<?php echo isset($move) ? $move->ref : ''; ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> placeholder="e.g. Vendor Invoice Number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bill Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="<?php echo isset($move) ? $move->date : date('Y-m-d'); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label>Accounting Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="<?php echo isset($move) ? $move->invoice_date : date('Y-m-d'); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="invoice_date_due" class="form-control" value="<?php echo isset($move) ? $move->invoice_date_due : date('Y-m-d', strtotime('+30 days')); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" id="bill-currency" class="form-control selectpicker" <?php echo !$is_draft ? 'disabled' : ''; ?> onchange="xbOnCurrencyChange()">
                                    <?php foreach($currencies as $cur):
                                        $cur_selected = (isset($move) && $move->currency_id == $cur->id) ? 'selected' : ((!isset($move) && $cur->isdefault) ? 'selected' : '');
                                    ?>
                                    <option value="<?php echo $cur->id; ?>" data-rate="<?php echo $cur->rate; ?>" data-symbol="<?php echo htmlspecialchars($cur->symbol); ?>" data-name="<?php echo htmlspecialchars($cur->name); ?>" <?php echo $cur_selected; ?>>
                                        <?php echo $cur->name; ?> — <?php echo htmlspecialchars($cur->symbol); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" id="xb-exrate-row" style="display:none;">
                                <label>Exchange Rate
                                    <small class="text-muted" id="xb-exrate-hint"></small>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="any" name="exchange_rate" id="bill-exchange-rate" class="form-control"
                                           value="<?php echo isset($move) && $move->exchange_rate ? $move->exchange_rate : 1; ?>"
                                           <?php echo !$is_draft ? 'disabled' : ''; ?> onchange="calculate_totals()">
                                    <span class="input-group-addon">KES per unit</span>
                                </div>
                                <p class="help-block" style="font-size:11px;margin-top:4px;">1 <span id="xb-cur-code">USD</span> = this many KES. Journal entries will be converted at this rate.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content">
                
                <!-- Items Tab -->
                <div role="tabpanel" class="tab-pane active" id="items">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body" style="padding: 0;">
                            <table class="table" id="bill-lines" style="margin-bottom: 0;">
                                <thead style="background: #f9fafb;">
                                    <tr>
                                        <th width="20%">Product</th>
                                        <th width="20%">Label</th>
                                        <th width="20%">Account</th>
                                        <th width="10%">Quantity</th>
                                        <th width="12%">Price</th>
                                        <th width="13%">Tax</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(empty($invoice_lines)) {
                                        $invoice_lines = [ (object)[
                                            'product_id' => '', 'name' => '', 'account_id' => '', 
                                            'quantity' => 1, 'price_unit' => 0, 'tax_line_id' => ''
                                        ] ];
                                    }
                                    $idx = 0;
                                    foreach($invoice_lines as $line): 
                                    ?>
                                    <tr class="item-row">
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true" data-container="body" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                                                <option value=""></option>
                                                <?php foreach($items as $group_id => $_items): ?>
                                                    <optgroup label="<?php echo $_items[0]['group_name'] ?? 'Items'; ?>">
                                                    <?php foreach($_items as $item): ?>
                                                        <option value="<?php echo $item['id']; ?>" <?php echo ($line->product_id == $item['id']) ? 'selected' : ''; ?> data-subtext="<?php echo $item['long_description']; ?>">
                                                            <?php echo $item['description']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="lines[<?php echo $idx; ?>][description]" class="form-control xb-item-desc" placeholder="Label" value="<?php echo htmlspecialchars($line->name); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                        </td>
                                        <td>
                                            <?php $default_account_for_row = !empty($line->account_id) ? $line->account_id : (isset($default_purchase_account) ? $default_purchase_account : ''); ?>
                                            <select name="lines[<?php echo $idx; ?>][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" data-container="body" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                                <option value=""></option>
                                                <?php foreach($accounts as $acc): ?>
                                                    <option value="<?php echo $acc->id; ?>" <?php echo ($default_account_for_row == $acc->id || $default_account_for_row == $acc->code) ? 'selected' : ''; ?>><?php echo $acc->code . ' ' . $acc->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][qty]" class="form-control row-qty" value="<?php echo (float)$line->quantity; ?>" onchange="calculate_totals()" <?php echo !$is_draft ? 'disabled' : ''; ?> required></td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][price_unit]" class="form-control row-price" value="<?php echo (float)$line->price_unit; ?>" onchange="calculate_totals()" <?php echo !$is_draft ? 'disabled' : ''; ?> required></td>
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][tax_id]" class="form-control selectpicker row-tax" data-container="body" <?php echo !$is_draft ? 'disabled' : ''; ?> onchange="calculate_totals()">
                                                <option value="">No Tax</option>
                                                <?php foreach($taxes as $tax): ?>
                                                    <option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>" <?php echo ($line->tax_line_id == $tax->id) ? 'selected' : ''; ?>><?php echo $tax->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <?php $dist_val = !empty($line->analytic_distribution) ? $line->analytic_distribution : '{}'; ?>
                                            <input type="hidden" name="lines[<?php echo $idx; ?>][analytic_distribution]" class="row-analytic-dist" value="<?php echo htmlspecialchars($dist_val); ?>">
                                            <button type="button" class="btn btn-xs btn-analytic row-analytic-btn" title="Set Analytic Distribution" onclick="open_analytic_modal(this)" style="background:transparent;border:1px dashed #d1d5db;padding:3px 6px;">
                                                <i class="fa fa-pie-chart" style="color:#6b7280;"></i>
                                            </button>
                                            <?php if($is_draft): ?>
                                            <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest('tr').remove(); calculate_totals();"><i class="fa fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $idx++; endforeach; ?>
                                </tbody>
                            </table>
                            <?php if($is_draft): ?>
                            <div style="padding: 10px 20px;">
                                <button type="button" class="btn btn-default btn-sm" onclick="add_bill_line()"><i class="fa fa-plus"></i> Add Line</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-5 col-md-offset-7">
                            <div class="xb-totals-card">
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Untaxed Amount</span>
                                    <span class="xb-totals-value"><span id="total-untaxed">0.00</span> <span id="total-untaxed-kes" class="text-muted" style="font-size:11px;display:none;"></span></span>
                                </div>
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Tax</span>
                                    <span class="xb-totals-value"><span id="total-tax">0.00</span> <span id="total-tax-kes" class="text-muted" style="font-size:11px;display:none;"></span></span>
                                </div>
                                <div class="xb-totals-row xb-totals-final">
                                    <span class="xb-totals-label-final">Total</span>
                                    <span class="xb-totals-value-final">
                                        <span id="total-amount">0.00</span>
                                        <div id="total-kes-equiv" style="display:none;font-size:13px;font-weight:500;color:#6b7280;margin-top:2px;"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Items Tab (Only visible if posted) -->
                <?php if(isset($move) && $move->state == 'posted'): ?>
                <div role="tabpanel" class="tab-pane" id="journal_items">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body" style="padding: 0;">
                            <table class="table" style="margin-bottom: 0;">
                                <thead style="background: #f9fafb;">
                                    <tr>
                                        <th>Account</th>
                                        <th>Label</th>
                                        <th class="text-right">Debit (KES)</th>
                                        <th class="text-right">Credit (KES)</th>
                                        <?php if(isset($move) && $move->currency_id != 1): ?>
                                        <th class="text-right">Foreign Amount</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $show_fc = isset($move) && $move->currency_id != 1;
                                    $fc_sym  = $show_fc ? xb_get_currency_symbol($move->currency_id) : '';
                                    foreach($journal_items as $ji): ?>
                                    <tr>
                                        <td><?php echo $ji->account_code . ' ' . $ji->account_name; ?></td>
                                        <td><?php echo htmlspecialchars($ji->name); ?></td>
                                        <td class="text-right"><?php echo number_format($ji->debit, 2); ?></td>
                                        <td class="text-right"><?php echo number_format($ji->credit, 2); ?></td>
                                        <?php if($show_fc): ?>
                                        <td class="text-right text-muted" style="font-size:12px;">
                                            <?php if (!empty($ji->amount_currency)): ?>
                                                <?php echo $fc_sym . ' ' . number_format(abs($ji->amount_currency), 2); ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Other Info Tab -->
                <div role="tabpanel" class="tab-pane" id="other_info">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Journal <span class="text-danger">*</span></label>
                                        <select name="journal_id" class="form-control selectpicker" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                            <?php foreach($journals as $j): ?>
                                                <option value="<?php echo $j->id; ?>" <?php echo (isset($move) && $move->journal_id == $j->id) ? 'selected' : ''; ?>><?php echo $j->name; ?></option>
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

        <!-- RIGHT SIDEBAR (Chatter & Summary) -->
        <div class="col-md-3">
            <div class="xb-sidebar">
                <div class="xb-sidebar-block">
                    <h4>Document Summary</h4>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Bill Number</span>
                        <span class="xb-info-val"><?php echo isset($move) ? $move->name : 'Draft'; ?></span>
                    </div>
                    <?php
                        $cur_id = isset($move) ? (int)($move->currency_id ?? 1) : 1;
                        $cur_sym = ($cur_id !== 1) ? xb_get_currency_symbol($cur_id) : 'KSh';
                        $cur_code = ($cur_id !== 1) ? xb_get_currency_code($cur_id) : 'KES';
                        $rate = isset($move) ? (float)($move->exchange_rate ?? 1) : 1;
                    ?>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Currency</span>
                        <span class="xb-info-val">
                            <?php echo $cur_code; ?>
                            <?php if ($cur_id !== 1 && $rate > 0): ?>
                            <small class="text-muted" style="font-size:10px;display:block;">Rate: <?php echo $rate; ?> KES</small>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Bill Total</span>
                        <span class="xb-info-val">
                            <?php echo $cur_sym . ' ' . number_format(isset($move) ? $move->amount_total : 0, 2); ?>
                            <?php if ($cur_id !== 1 && $rate > 0): ?>
                            <small class="text-muted" style="font-size:10px;display:block;">≈ KSh <?php echo number_format((isset($move) ? $move->amount_total : 0) * $rate, 2); ?></small>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Outstanding Balance</span>
                        <span class="xb-info-val text-danger">
                            <?php echo $cur_sym . ' ' . number_format(isset($move) ? $move->amount_residual : 0, 2); ?>
                        </span>
                    </div>
                </div>

                <div class="xb-sidebar-block">
                    <h4>Timeline</h4>
                    <ul style="list-style: none; padding: 0; font-size: 13px;">
                        <?php if(isset($move)): ?>
                        <li style="border-left: 2px solid #e5e7eb; padding-left: 15px; margin-bottom: 15px; position: relative;">
                            <div style="position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #e5e7eb;"></div>
                            <strong>Created</strong><br>
                            <span class="text-muted"><?php echo _dt($move->created_at); ?></span>
                        </li>
                        <?php if($move->state == 'posted'): ?>
                        <li style="border-left: 2px solid #1a6b3a; padding-left: 15px; margin-bottom: 15px; position: relative;">
                            <div style="position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #1a6b3a;"></div>
                            <strong>Confirmed (Posted)</strong><br>
                            <span class="text-muted">Status changed to Unpaid</span>
                        </li>
                        <?php endif; ?>
                        <?php if($move->payment_state == 'partial'): ?>
                        <li style="border-left: 2px solid #d97706; padding-left: 15px; margin-bottom: 15px; position: relative;">
                            <div style="position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #d97706;"></div>
                            <strong>Partially Paid</strong><br>
                            <span class="text-muted">Outstanding: <?php echo xb_format_money($move->amount_residual); ?></span>
                        </li>
                        <?php elseif($move->payment_state == 'paid'): ?>
                        <li style="border-left: 2px solid #16a34a; padding-left: 15px; margin-bottom: 15px; position: relative;">
                            <div style="position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></div>
                            <strong>Fully Paid</strong><br>
                            <span class="text-muted">Payment registered</span>
                        </li>
                        <?php endif; ?>
                        <?php else: ?>
                        <li class="text-muted">Not saved yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if (isset($credit_notes) && !empty($credit_notes)): ?>
                <div class="xb-sidebar-block">
                    <h4>Credit Notes Applied</h4>
                    <?php foreach ($credit_notes as $cn): ?>
                    <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:5px;padding:8px 10px;margin-bottom:8px;font-size:12px;">
                        <a href="<?php echo admin_url('xetuu_books/refund_form/' . $cn->id); ?>" style="font-weight:600;color:#0f766e;"><?php echo $cn->name ?: 'Draft'; ?></a><br>
                        <span class="text-muted"><?php echo _d($cn->date); ?></span>
                        <span style="float:right;font-weight:600;color:#0f766e;">−<?php echo xb_format_money($cn->amount_total); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php echo form_close(); ?>

</div>

<!-- Register Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('', ['id' => 'payment-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Register Payment</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="move_id" id="pay_move_id">
                <input type="hidden" name="payment_type" value="outbound">
                <input type="hidden" name="partner_id" value="<?php echo isset($move) ? $move->partner_id : ''; ?>">
                <div class="form-group">
                    <label>Journal (Bank/Cash)</label>
                    <select name="journal_id" class="form-control selectpicker" required>
                        <?php foreach($payment_journals as $j): ?>
                            <option value="<?php echo $j->id; ?>"><?php echo $j->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" step="any" name="amount" id="pay_amount" class="form-control" value="<?php echo isset($move) ? $move->amount_residual : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Memo</label>
                    <input type="text" name="ref" class="form-control" value="<?php echo isset($move) ? $move->name : ''; ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Create Payment</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
var line_idx = <?php echo isset($idx) ? $idx : 1; ?>;
var default_purchase_account = "<?php echo isset($default_purchase_account) ? $default_purchase_account : ''; ?>";

function register_payment(id, residual) {
    $('#pay_move_id').val(id);
    $('#pay_amount').val(residual !== undefined ? residual : '');
    $('#paymentModal').modal('show');
}

function confirm_bill(id) {
    if(confirm('Are you sure you want to confirm this bill?')) {
        var btn = $(event.target);
        btn.button('loading');
        $.post(admin_url + 'xetuu_books/post_bill/' + id, function(res) {
            var data = JSON.parse(res);
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message);
                btn.button('reset');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.button('loading');
        $.post(admin_url + 'xetuu_books/register_payment', $(this).serialize(), function(res) {
            var data = JSON.parse(res);
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message);
                btn.button('reset');
            }
        });
    });

    calculate_totals();
    
    // Auto-fill when product is selected
    $(document).on('change', '.xb-item-select', function() {
        var item_id = $(this).val();
        var row = $(this).closest('tr');
        if(item_id) {
            $.get(admin_url + 'invoice_items/get_item_by_id/' + item_id, function(response) {
                var item = JSON.parse(response);
                row.find('.xb-item-desc').val(item.description + (item.long_description ? ' - ' + item.long_description : ''));
                row.find('.row-price').val(item.rate);
                
                // Set default account if not already set manually
                if(!row.find('.xb-item-account').val() && default_purchase_account) {
                    row.find('.xb-item-account').val(default_purchase_account).selectpicker('refresh');
                }
                
                calculate_totals();
            });
        }
    });
});

function add_bill_line() {
    var html = '<tr class="item-row">';
    
    // Product Select
    html += '<td><select name="lines['+line_idx+'][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true" data-container="body">';
    html += '<option value=""></option>';
    <?php foreach($items as $group_id => $_items): ?>
        html += '<optgroup label="<?php echo addslashes($_items[0]['group_name'] ?? 'Items'); ?>">';
        <?php foreach($_items as $item): ?>
            html += '<option value="<?php echo $item['id']; ?>" data-subtext="<?php echo addslashes($item['long_description']); ?>"><?php echo addslashes($item['description']); ?></option>';
        <?php endforeach; ?>
        html += '</optgroup>';
    <?php endforeach; ?>
    html += '</select></td>';
    
    // Label
    html += '<td><input type="text" name="lines['+line_idx+'][description]" class="form-control xb-item-desc" placeholder="Label" required></td>';
    
    // Account Select
    html += '<td><select name="lines['+line_idx+'][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" data-container="body" required>';
    html += '<option value=""></option>';
    <?php foreach($accounts as $acc): ?>
        html += '<option value="<?php echo $acc->id; ?>" ' + (default_purchase_account == "<?php echo $acc->id; ?>" ? "selected" : "") + '><?php echo addslashes($acc->code . ' ' . $acc->name); ?></option>';
    <?php endforeach; ?>
    html += '</select></td>';
    
    html += '<td><input type="number" step="any" name="lines['+line_idx+'][qty]" class="form-control row-qty" value="1" onchange="calculate_totals()" required></td>';
    html += '<td><input type="number" step="any" name="lines['+line_idx+'][price_unit]" class="form-control row-price" value="0" onchange="calculate_totals()" required></td>';
    
    // Tax Select
    html += '<td><select name="lines['+line_idx+'][tax_id]" class="form-control selectpicker row-tax" data-container="body" onchange="calculate_totals()">';
    html += '<option value="">No Tax</option>';
    <?php foreach($taxes as $tax): ?>
        html += '<option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>"><?php echo addslashes($tax->name); ?></option>';
    <?php endforeach; ?>
    html += '</select></td>';
    
    html += '<td style="white-space:nowrap;">';
    html += '<input type="hidden" name="lines['+line_idx+'][analytic_distribution]" class="row-analytic-dist" value="{}">';
    html += '<button type="button" class="btn btn-xs btn-analytic row-analytic-btn" title="Set Analytic Distribution" onclick="open_analytic_modal(this)" style="background:transparent;border:1px dashed #d1d5db;padding:3px 6px;"><i class="fa fa-pie-chart" style="color:#6b7280;"></i></button>';
    html += '<button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest(\'tr\').remove(); calculate_totals();"><i class="fa fa-trash"></i></button>';
    html += '</td>';
    html += '</tr>';

    $('#bill-lines tbody').append(html);
    $('.selectpicker').selectpicker('refresh');
    line_idx++;
}

function xbGetCurrencyInfo() {
    var $sel = $('#bill-currency option:selected');
    return {
        id:     parseInt($sel.val()) || 1,
        name:   $sel.data('name') || 'KES',
        symbol: $sel.data('symbol') || 'KSh',
        rate:   parseFloat($('#bill-exchange-rate').val()) || parseFloat($sel.data('rate')) || 1,
    };
}

function xbOnCurrencyChange() {
    var $sel = $('#bill-currency option:selected');
    var curName = $sel.data('name') || 'KES';
    var curId   = parseInt($sel.val()) || 1;
    var isBase  = (curId === 1);

    if (isBase) {
        $('#xb-exrate-row').hide();
        $('#bill-exchange-rate').val(1);
        calculate_totals();
        return;
    }

    $('#xb-cur-code').text(curName);
    $('#xb-exrate-hint').text('(' + curName + ' → KES)');
    $('#xb-exrate-row').show();

    // Fetch live rate from DB
    $.get(admin_url + 'xetuu_books/ajax/get_currency_rate', {id: curId}, function(res) {
        var data;
        try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { data = null; }
        if (data && data.rate) {
            $('#bill-exchange-rate').val(parseFloat(data.rate).toFixed(4));
        }
        calculate_totals();
    }).fail(function() { calculate_totals(); });
}

function calculate_totals() {
    var untaxed = 0;
    var tax = 0;

    $('.item-row').each(function() {
        var qty = parseFloat($(this).find('.row-qty').val()) || 0;
        var price = parseFloat($(this).find('.row-price').val()) || 0;
        var subtotal = qty * price;

        var tax_sel = $(this).find('.row-tax option:selected');
        if(tax_sel.val() !== "") {
            var rate = Math.abs(parseFloat(tax_sel.data('rate'))) || 0;
            var is_include = tax_sel.data('include') == 1;

            if(is_include) {
                var sub_ex = subtotal / (1 + rate / 100);
                untaxed += sub_ex;
                tax += (subtotal - sub_ex);
            } else {
                untaxed += subtotal;
                tax += (subtotal * rate / 100);
            }
        } else {
            untaxed += subtotal;
        }
    });

    var cur = xbGetCurrencyInfo();
    var fmt = function(n) { return n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

    $('#total-untaxed').text(cur.symbol + ' ' + fmt(untaxed));
    $('#total-tax').text(cur.symbol + ' ' + fmt(tax));
    $('#total-amount').text(cur.symbol + ' ' + fmt(untaxed + tax));

    if (cur.id !== 1 && cur.rate > 0) {
        var kesUntaxed = untaxed * cur.rate;
        var kesTax     = tax * cur.rate;
        var kesTotal   = (untaxed + tax) * cur.rate;
        $('#total-untaxed-kes').text('≈ KSh ' + fmt(kesUntaxed)).show();
        $('#total-tax-kes').text('≈ KSh ' + fmt(kesTax)).show();
        $('#total-kes-equiv').html('<i class="fa fa-exchange" style="font-size:10px;margin-right:4px;"></i>≈ KSh ' + fmt(kesTotal) + ' at ' + cur.rate + ' rate').show();
    } else {
        $('#total-untaxed-kes').hide();
        $('#total-tax-kes').hide();
        $('#total-kes-equiv').hide();
    }
}

$(document).ready(function() {
    // Init currency display on page load
    var $curSel = $('#bill-currency option:selected');
    var initCurId = parseInt($curSel.val()) || 1;
    if (initCurId !== 1) {
        $('#xb-cur-code').text($curSel.data('name') || 'USD');
        $('#xb-exrate-hint').text('(' + ($curSel.data('name') || 'USD') + ' → KES)');
        $('#xb-exrate-row').show();
    }
    calculate_totals();
});

function save_draft() {
    $('#bill-form').submit();
}

function post_bill(id) {
    if(confirm('Are you sure you want to confirm this bill?')) {
        $.post(admin_url + 'xetuu_books/post_bill/' + id, function(res) {
            var data = JSON.parse(res);
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

// ─── Analytic Distribution Widget ───────────────────────────────────────────
var _analytic_plans    = <?php echo json_encode(isset($analytic_plans) ? $analytic_plans : []); ?>;
var _analytic_accounts = <?php echo json_encode(isset($analytic_accounts) ? $analytic_accounts : []); ?>;
var _current_analytic_btn = null;

function open_analytic_modal(btn) {
    _current_analytic_btn = btn;
    var $row   = $(btn).closest('tr');
    var $hidden = $row.find('.row-analytic-dist');
    var current = {};
    try { current = JSON.parse($hidden.val() || '{}'); } catch(e) {}

    var html = '';
    if (_analytic_plans.length === 0) {
        html = '<div class="alert alert-info">No analytic plans configured. Go to <a href="' + admin_url + 'xetuu_books/config/analytic_plans" target="_blank">Settings → Analytic Plans</a> to create your dimensions first.</div>';
    } else {
        _analytic_plans.forEach(function(plan) {
            var plan_accounts = _analytic_accounts.filter(function(a) { return a.plan_id == plan.id; });
            if (plan_accounts.length === 0) return;

            html += '<div class="xb-analytic-plan-block" style="margin-bottom:18px;">';
            html += '<div style="display:flex;align-items:center;margin-bottom:8px;">';
            html += '<span style="width:10px;height:10px;border-radius:50%;background:' + (plan.color || '#1a6b3a') + ';display:inline-block;margin-right:6px;"></span>';
            html += '<strong style="font-size:13px;">' + plan.name + '</strong>';
            html += '<span style="margin-left:auto;font-size:11px;color:#9ca3af;">' + (plan.default_applicability === 'mandatory' ? '* Required' : 'Optional') + '</span>';
            html += '</div>';

            plan_accounts.forEach(function(acc) {
                var current_pct = current[acc.id] || '';
                html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">';
                html += '<div style="flex:1;font-size:13px;padding-left:' + (acc.level * 12) + 'px;">';
                if (acc.code) html += '<code style="font-size:11px;color:#6b7280;">[' + acc.code + ']</code> ';
                html += acc.name;
                html += '</div>';
                html += '<div style="width:90px;display:flex;align-items:center;gap:4px;">';
                html += '<input type="number" min="0" max="100" step="any" class="form-control form-control-sm analytic-pct-input" style="width:65px;text-align:right;" data-account-id="' + acc.id + '" data-plan-id="' + plan.id + '" value="' + current_pct + '" placeholder="0">';
                html += '<span style="color:#6b7280;font-size:13px;">%</span>';
                html += '</div>';
                html += '</div>';
            });
            html += '<div class="analytic-plan-total" data-plan-id="' + plan.id + '" style="text-align:right;font-size:11px;color:#6b7280;padding-top:4px;border-top:1px solid #f3f4f6;"></div>';
            html += '</div>';
        });
    }

    $('#analytic-modal-body').html(html);
    $('#analytic-modal').modal('show');
    update_analytic_totals();

    $(document).off('input.analytic').on('input.analytic', '.analytic-pct-input', function() {
        update_analytic_totals();
    });
}

function update_analytic_totals() {
    _analytic_plans.forEach(function(plan) {
        var total = 0;
        $('.analytic-pct-input[data-plan-id="' + plan.id + '"]').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        var $total_el = $('.analytic-plan-total[data-plan-id="' + plan.id + '"]');
        $total_el.text('Total: ' + total.toFixed(1) + '%');
        $total_el.css('color', Math.abs(total - 100) < 0.01 || total === 0 ? '#16a34a' : '#dc2626');
    });
}

function save_analytic_distribution() {
    var dist = {};
    $('.analytic-pct-input').each(function() {
        var pct = parseFloat($(this).val()) || 0;
        if (pct > 0) {
            dist[$(this).data('account-id')] = pct;
        }
    });

    if (_current_analytic_btn) {
        var $row    = $(_current_analytic_btn).closest('tr');
        var $hidden = $row.find('.row-analytic-dist');
        $hidden.val(JSON.stringify(dist));

        // Update button appearance
        var count = Object.keys(dist).length;
        var $btn = $(_current_analytic_btn);
        if (count > 0) {
            $btn.css({'background': '#f0fdf4', 'border-color': '#1a6b3a'});
            $btn.find('i').css('color', '#1a6b3a');
            $btn.attr('title', count + ' analytic account(s) set');
        } else {
            $btn.css({'background': 'transparent', 'border-color': '#d1d5db'});
            $btn.find('i').css('color', '#6b7280');
            $btn.attr('title', 'Set Analytic Distribution');
        }
    }
    $('#analytic-modal').modal('hide');
}

// Initialise existing line analytic buttons on page load
$(document).ready(function() {
    $('.row-analytic-dist').each(function() {
        var val = $(this).val();
        if (val && val !== '{}') {
            try {
                var dist = JSON.parse(val);
                if (Object.keys(dist).length > 0) {
                    var $btn = $(this).siblings('.row-analytic-btn');
                    $btn.css({'background': '#f0fdf4', 'border-color': '#1a6b3a'});
                    $btn.find('i').css('color', '#1a6b3a');
                }
            } catch(e) {}
        }
    });
});
</script>

<!-- Analytic Distribution Modal -->
<div class="modal fade" id="analytic-modal" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-pie-chart" style="color:#1a6b3a;margin-right:6px;"></i>Analytic Distribution</h4>
      </div>
      <div class="modal-body" id="analytic-modal-body" style="max-height:420px;overflow-y:auto;padding:20px;">
      </div>
      <div class="modal-footer">
        <small class="text-muted pull-left">Each plan's percentages should total 100% (or 0% to skip).</small>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="save_analytic_distribution()">Apply Distribution</button>
      </div>
    </div>
  </div>
</div>
