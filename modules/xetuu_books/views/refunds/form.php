<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_draft     = (!isset($move) || $move->state == 'draft');
$status_color = 'default';
if (isset($move)) {
    if ($move->state == 'draft') $status_color = 'default';
    elseif ($move->state == 'posted' && $move->payment_state == 'paid')   $status_color = 'success';
    elseif ($move->state == 'posted') $status_color = 'primary';
    elseif ($move->state == 'cancel') $status_color = 'danger';
}
?>
<style>
.xb-workspace { margin-top: 0; }
.xb-breadcrumb { padding: 15px 0; font-size: 13px; color: #6b7280; }
.xb-breadcrumb a { color: #1a6b3a; font-weight: 500; }
.xb-header-toolbar { background: #fff; padding: 15px 25px; border-bottom: 1px solid #e5e7eb; margin: 0 -25px 20px -25px; display: flex; justify-content: space-between; align-items: center; }
.xb-header-title h3 { margin: 0; display: inline-block; font-weight: 600; color: #111827; }
.xb-badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; margin-left: 10px; vertical-align: middle; }
.xb-badge-default  { background: #f3f4f6; color: #374151; }
.xb-badge-primary  { background: #e0e7ff; color: #4f46e5; }
.xb-badge-success  { background: #dcfce7; color: #16a34a; }
.xb-badge-danger   { background: #fee2e2; color: #dc2626; }
.xb-badge-warning  { background: #fef3c7; color: #d97706; }
.xb-badge-teal     { background: #ccfbf1; color: #0f766e; }
.xb-tabs { margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
.xb-tabs .nav-tabs { border-bottom: none; }
.xb-tabs .nav-tabs>li>a { color: #6b7280; font-weight: 500; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: 10px 15px; border-radius: 0; }
.xb-tabs .nav-tabs>li.active>a, .xb-tabs .nav-tabs>li.active>a:hover, .xb-tabs .nav-tabs>li.active>a:focus { color: #1a6b3a; border-bottom: 2px solid #1a6b3a; background: transparent; }
.xb-collapsible-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px; }
.xb-section-body { padding: 20px; }
.xb-sidebar { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
.xb-sidebar-block { margin-bottom: 25px; }
.xb-sidebar-block h4 { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-top: 0; margin-bottom: 10px; letter-spacing: 0.05em; }
.xb-info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
.xb-info-label { color: #6b7280; }
.xb-info-val { font-weight: 500; color: #111827; }
.bootstrap-select.btn-group .dropdown-toggle .filter-option { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.xb-totals-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.xb-totals-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 18px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
.xb-totals-row:last-child { border-bottom: none; }
.xb-totals-label { color: #6b7280; font-weight: 500; }
.xb-totals-value { color: #374151; font-weight: 600; font-variant-numeric: tabular-nums; }
.xb-totals-final { background: #f0fdf4; border-top: 2px solid #bbf7d0; padding: 14px 18px; }
.xb-totals-label-final { color: #14532d; font-weight: 700; font-size: 14px; }
.xb-totals-value-final { color: #15803d; font-weight: 700; font-size: 20px; font-variant-numeric: tabular-nums; }
/* Refund highlight — teal accent instead of green */
.xb-refund-accent { border-left: 4px solid #0d9488; background: #f0fdfa; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 13px; color: #134e4a; margin-bottom: 16px; }
</style>

<div class="xb-workspace">

    <!-- Breadcrumbs -->
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="#">Vendors</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/refunds'); ?>">Vendor Credit Notes</a> &rsaquo;
        <?php echo isset($move) ? $move->name : 'New Credit Note'; ?>
    </div>

    <!-- Header Toolbar -->
    <div class="xb-header-toolbar">
        <div class="xb-header-title">
            <h3><?php echo isset($move) ? $move->name : 'New Vendor Credit Note'; ?></h3>
            <span class="xb-badge xb-badge-<?php echo $status_color; ?>">
                <?php echo isset($move) ? strtoupper($move->state) : 'DRAFT'; ?>
            </span>
            <?php if (isset($move) && $move->state == 'posted' && $move->payment_state == 'not_paid'): ?>
            <span class="xb-badge xb-badge-teal">CREDIT DUE</span>
            <?php elseif (isset($move) && $move->state == 'posted' && $move->payment_state == 'partial'): ?>
            <span class="xb-badge xb-badge-warning">PARTIAL</span>
            <?php elseif (isset($move) && $move->state == 'posted' && $move->payment_state == 'paid'): ?>
            <span class="xb-badge xb-badge-success">SETTLED</span>
            <?php endif; ?>
        </div>
        <div class="xb-header-actions">
            <?php if ($is_draft): ?>
            <button type="submit" form="refund-form" class="btn btn-primary" style="padding:8px 24px;font-weight:500;min-width:100px;">Save</button>
            <?php endif; ?>
            <?php if (isset($move)): ?>
                <?php if ($move->state == 'draft'): ?>
                <button type="button" class="btn btn-success" style="padding:8px 24px;font-weight:500;" onclick="confirm_refund(<?php echo $move->id; ?>)">
                    <i class="fa fa-check"></i> Confirm Credit Note
                </button>
                <?php endif; ?>
                <?php if ($move->state == 'posted' && $move->payment_state != 'paid'): ?>
                <button type="button" class="btn btn-info" style="padding:8px 24px;font-weight:500;min-width:170px;" onclick="register_receipt(<?php echo $move->id; ?>, <?php echo (float)$move->amount_residual; ?>)">
                    <i class="fa fa-bank"></i> Register Receipt
                </button>
                <?php endif; ?>
            <?php endif; ?>
            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-ellipsis-v"></i></button>
        </div>
    </div>

    <?php if (!isset($move)): ?>
    <div class="xb-refund-accent">
        <i class="fa fa-info-circle"></i>
        <strong>Vendor Credit Note</strong> — Record a credit note issued by a vendor when they overbilled you, you returned goods, or they issued a discount. Confirming this document will reverse the original expense entry and reduce your Accounts Payable.
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="xb-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#items" role="tab" data-toggle="tab">Credit Note Lines</a></li>
            <?php if (isset($move) && $move->state == 'posted'): ?>
            <li role="presentation"><a href="#journal_items" role="tab" data-toggle="tab">Journal Items</a></li>
            <?php endif; ?>
            <li role="presentation"><a href="#other_info" role="tab" data-toggle="tab">Other Info</a></li>
        </ul>
    </div>

    <?php echo form_open(admin_url('xetuu_books/refund_form/' . (isset($move) ? $move->id : '')), ['id' => 'refund-form']); ?>
    <div class="row">
        <div class="col-md-9">

            <!-- Header Fields -->
            <div class="xb-collapsible-section" style="margin-bottom:20px;">
                <div class="xb-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor <span class="text-danger">*</span></label>
                                <select name="partner_id" id="cn_vendor_id" class="form-control selectpicker" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                    <option value=""></option>
                                    <?php foreach ($vendors as $v):
                                        $selected = (isset($move) && $move->partner_id == $v->userid) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $v->userid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($v->company); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Bill to Reverse</label>
                                <?php if ($is_draft): ?>
                                <select name="source_move_id" id="cn_source_bill" class="form-control selectpicker" data-live-search="true">
                                    <option value="">— Select a Bill (optional) —</option>
                                    <?php foreach ($vendor_bills as $b):
                                        $sel = (isset($move) && $move->source_move_id == $b->id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $b->id; ?>" <?php echo $sel; ?> data-amount="<?php echo $b->amount_residual; ?>">
                                        <?php echo $b->name; ?> — <?php echo xb_format_money($b->amount_residual); ?> outstanding
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="help-block" style="margin-top:4px;font-size:12px;">Selecting a bill will auto-fill the lines and link this credit note to that specific bill.</p>
                                <?php else: ?>
                                <input type="hidden" name="source_move_id" value="<?php echo isset($move) ? (int)($move->source_move_id ?? 0) : 0; ?>">
                                <?php if (isset($source_bill) && $source_bill): ?>
                                <div style="padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;font-size:13px;">
                                    <i class="fa fa-link" style="color:#16a34a;"></i>
                                    Reversal of: <a href="<?php echo admin_url('xetuu_books/bill_form/' . $source_bill->id); ?>" style="font-weight:600;color:#1a6b3a;"><?php echo $source_bill->name; ?></a>
                                    — <?php echo xb_format_money($source_bill->amount_total); ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:13px;">No specific bill linked</span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Vendor Reference</label>
                                <input type="text" name="ref" class="form-control" value="<?php echo isset($move) ? htmlspecialchars($move->ref ?? '') : ''; ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> placeholder="Vendor's own credit note number (optional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Refund Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="<?php echo isset($move) ? $move->date : date('Y-m-d'); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label>Accounting Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="<?php echo isset($move) ? $move->invoice_date : date('Y-m-d'); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content">

                <!-- Refund Lines Tab -->
                <div role="tabpanel" class="tab-pane active" id="items">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body" style="padding:0;">
                            <table class="table" id="refund-lines" style="margin-bottom:0;">
                                <thead style="background:#f9fafb;">
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
                                    if (empty($invoice_lines)) {
                                        $invoice_lines = [(object)[
                                            'product_id' => '', 'name' => '', 'account_id' => '',
                                            'quantity' => 1, 'price_unit' => 0, 'tax_line_id' => '',
                                        ]];
                                    }
                                    $idx = 0;
                                    foreach ($invoice_lines as $line):
                                    ?>
                                    <tr class="item-row">
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?>>
                                                <option value=""></option>
                                                <?php foreach ($items as $group_id => $_items): ?>
                                                <optgroup label="<?php echo $_items[0]['group_name'] ?? 'Items'; ?>">
                                                    <?php foreach ($_items as $item): ?>
                                                    <option value="<?php echo $item['id']; ?>" <?php echo ($line->product_id == $item['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $item['description']; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="lines[<?php echo $idx; ?>][description]" class="form-control xb-item-desc" placeholder="Label" value="<?php echo htmlspecialchars($line->name); ?>" <?php echo !$is_draft ? 'disabled' : ''; ?> required></td>
                                        <td>
                                            <?php $def_acc = !empty($line->account_id) ? $line->account_id : (isset($default_purchase_account) ? $default_purchase_account : ''); ?>
                                            <select name="lines[<?php echo $idx; ?>][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" <?php echo !$is_draft ? 'disabled' : ''; ?> required>
                                                <option value=""></option>
                                                <?php foreach ($accounts as $acc): ?>
                                                <option value="<?php echo $acc->id; ?>" <?php echo ($def_acc == $acc->id || $def_acc == $acc->code) ? 'selected' : ''; ?>>
                                                    <?php echo $acc->code . ' ' . $acc->name; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][qty]" class="form-control row-qty" value="<?php echo (float)$line->quantity; ?>" onchange="calculate_totals()" <?php echo !$is_draft ? 'disabled' : ''; ?> required></td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][price_unit]" class="form-control row-price" value="<?php echo (float)$line->price_unit; ?>" onchange="calculate_totals()" <?php echo !$is_draft ? 'disabled' : ''; ?> required></td>
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][tax_id]" class="form-control selectpicker row-tax" <?php echo !$is_draft ? 'disabled' : ''; ?> onchange="calculate_totals()">
                                                <option value="">No Tax</option>
                                                <?php foreach ($taxes as $tax): ?>
                                                <option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>" <?php echo ($line->tax_line_id == $tax->id) ? 'selected' : ''; ?>>
                                                    <?php echo $tax->name; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <?php if ($is_draft): ?>
                                            <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest('tr').remove(); calculate_totals();"><i class="fa fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $idx++; endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ($is_draft): ?>
                            <div style="padding:10px 20px;">
                                <button type="button" class="btn btn-default btn-sm" onclick="add_refund_line()"><i class="fa fa-plus"></i> Add Line</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row" style="margin-top:20px;">
                        <div class="col-md-5 col-md-offset-7">
                            <div class="xb-totals-card">
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Untaxed Amount</span>
                                    <span class="xb-totals-value" id="total-untaxed">0.00</span>
                                </div>
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Tax</span>
                                    <span class="xb-totals-value" id="total-tax">0.00</span>
                                </div>
                                <div class="xb-totals-row xb-totals-final">
                                    <span class="xb-totals-label-final">Credit Note Total</span>
                                    <span class="xb-totals-value-final" id="total-amount">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Items Tab -->
                <?php if (isset($move) && $move->state == 'posted'): ?>
                <div role="tabpanel" class="tab-pane" id="journal_items">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body" style="padding:0;">
                            <table class="table" style="margin-bottom:0;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th>Account</th>
                                        <th>Label</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($journal_items as $ji): ?>
                                    <tr>
                                        <td><?php echo $ji->account_code . ' ' . $ji->account_name; ?></td>
                                        <td><?php echo htmlspecialchars($ji->name); ?></td>
                                        <td class="text-right"><?php echo number_format($ji->debit, 2); ?></td>
                                        <td class="text-right"><?php echo number_format($ji->credit, 2); ?></td>
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
                                            <?php foreach ($journals as $j): ?>
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

        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="xb-sidebar">
                <div class="xb-sidebar-block">
                    <h4>Document Summary</h4>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Credit Note #</span>
                        <span class="xb-info-val"><?php echo isset($move) ? $move->name : 'Draft'; ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Credit Note Total</span>
                        <span class="xb-info-val"><?php echo xb_format_money(isset($move) ? $move->amount_total : 0); ?></span>
                    </div>
                    <div class="xb-info-row">
                        <span class="xb-info-label">Outstanding Credit</span>
                        <span class="xb-info-val text-success"><?php echo xb_format_money(isset($move) ? $move->amount_residual : 0); ?></span>
                    </div>
                </div>

                <?php if ($is_draft && function_exists('xb_render_analytic_field')): ?>
                <div class="xb-sidebar-block">
                    <?php echo xb_render_analytic_field('refund', isset($move) ? $move->id : 0, 'Analytic Account'); ?>
                </div>
                <?php endif; ?>

                <div class="xb-sidebar-block">
                    <h4>Timeline</h4>
                    <ul style="list-style:none;padding:0;font-size:13px;">
                        <?php if (isset($move)): ?>
                        <li style="border-left:2px solid #e5e7eb;padding-left:15px;margin-bottom:15px;position:relative;">
                            <div style="position:absolute;left:-5px;top:0;width:8px;height:8px;border-radius:50%;background:#e5e7eb;"></div>
                            <strong>Created</strong><br><span class="text-muted"><?php echo _dt($move->created_at); ?></span>
                        </li>
                        <?php if ($move->state == 'posted'): ?>
                        <li style="border-left:2px solid #0d9488;padding-left:15px;margin-bottom:15px;position:relative;">
                            <div style="position:absolute;left:-5px;top:0;width:8px;height:8px;border-radius:50%;background:#0d9488;"></div>
                            <strong>Confirmed</strong><br><span class="text-muted">Reverse journal entry posted</span>
                        </li>
                        <?php endif; ?>
                        <?php if ($move->payment_state == 'partial'): ?>
                        <li style="border-left:2px solid #d97706;padding-left:15px;margin-bottom:15px;position:relative;">
                            <div style="position:absolute;left:-5px;top:0;width:8px;height:8px;border-radius:50%;background:#d97706;"></div>
                            <strong>Partially Settled</strong><br>
                            <span class="text-muted">Outstanding: <?php echo xb_format_money($move->amount_residual); ?></span>
                        </li>
                        <?php elseif ($move->payment_state == 'paid'): ?>
                        <li style="border-left:2px solid #16a34a;padding-left:15px;margin-bottom:15px;position:relative;">
                            <div style="position:absolute;left:-5px;top:0;width:8px;height:8px;border-radius:50%;background:#16a34a;"></div>
                            <strong>Fully Settled</strong><br><span class="text-muted">Credit received</span>
                        </li>
                        <?php endif; ?>
                        <?php else: ?>
                        <li class="text-muted">Not saved yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<!-- Register Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('', ['id' => 'receipt-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Register Receipt</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;margin-bottom:16px;">Record the money received back from the vendor against this refund.</p>
                <input type="hidden" name="move_id" id="receipt_move_id">
                <input type="hidden" name="payment_type" value="inbound">
                <input type="hidden" name="partner_type" value="supplier">
                <input type="hidden" name="partner_id" value="<?php echo isset($move) ? $move->partner_id : ''; ?>">
                <div class="form-group">
                    <label>Bank / Cash Account</label>
                    <select name="journal_id" class="form-control selectpicker" required>
                        <?php foreach ($payment_journals as $j): ?>
                        <option value="<?php echo $j->id; ?>"><?php echo $j->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount Received</label>
                    <input type="number" step="any" name="amount" id="receipt_amount" class="form-control" value="<?php echo isset($move) ? $move->amount_residual : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Receipt Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Memo</label>
                    <input type="text" name="ref" class="form-control" value="<?php echo isset($move) ? $move->name : ''; ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Confirm Receipt</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
var line_idx = <?php echo isset($idx) ? $idx : 1; ?>;
var default_purchase_account = "<?php echo isset($default_purchase_account) ? $default_purchase_account : ''; ?>";

function register_receipt(id, residual) {
    $('#receipt_move_id').val(id);
    $('#receipt_amount').val(residual !== undefined ? residual : '');
    $('#receiptModal').modal('show');
}

function confirm_refund(id) {
    if (confirm('Confirm this vendor credit note? A reverse journal entry will be posted.')) {
        var btn = $(event.target);
        btn.button('loading');
        $.post(admin_url + 'xetuu_books/post_bill/' + id, function (res) {
            var data = JSON.parse(res);
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
                btn.button('reset');
            }
        });
    }
}

function xb_refresh_bill_select(options_html) {
    var $bill_sel = $('#cn_source_bill');
    if (!$bill_sel.length) return;
    // Destroy, rebuild options, reinitialize — the only reliable way with selectpicker 1.13
    $bill_sel.selectpicker('destroy');
    $bill_sel.html(options_html);
    $bill_sel.selectpicker({ liveSearch: true });
}

document.addEventListener('DOMContentLoaded', function () {

    // 'changed.bs.select' fires once after every Bootstrap-Select user pick
    $(document).on('changed.bs.select', '#cn_vendor_id', function () {
        var vendor_id = $(this).val();
        if (!vendor_id) {
            xb_refresh_bill_select('<option value="">— Select Vendor First —</option>');
            return;
        }

        xb_refresh_bill_select('<option value="">Loading bills...</option>');

        $.ajax({
            url: admin_url + 'xetuu_books/get_vendor_bills/' + vendor_id,
            type: 'GET',
            success: function (raw) {
                var res;
                try { res = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch(e) { res = null; }
                var html = '<option value="">— Select a Bill (optional) —</option>';
                if (res && res.success && res.data && res.data.length) {
                    $.each(res.data, function (i, b) {
                        var outstanding = parseFloat(b.amount_residual || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        html += '<option value="' + b.id + '" data-amount="' + b.amount_residual + '">' +
                            b.name + '  —  ' + outstanding + ' outstanding</option>';
                    });
                } else {
                    html += '<option value="" disabled>No outstanding bills for this vendor</option>';
                }
                xb_refresh_bill_select(html);
            },
            error: function () {
                xb_refresh_bill_select('<option value="">— Error loading bills —</option>');
            }
        });
    });

    // When a bill is chosen: fetch its lines and auto-fill the table
    $(document).on('changed.bs.select', '#cn_source_bill', function () {
        var bill_id = $(this).val();
        if (!bill_id) return;

        $.ajax({
            url: admin_url + 'xetuu_books/get_bill_lines_ajax/' + bill_id,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res || !res.success || !res.lines || !res.lines.length) return;

                $('#refund-lines tbody').empty();
                line_idx = 0;

                $.each(res.lines, function (i, line) {
                    var html = '<tr class="item-row">';
                    html += '<td><select name="lines[' + line_idx + '][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true"><option value=""></option>';
                    <?php foreach ($items as $group_id => $_items): ?>
                        html += '<optgroup label="<?php echo addslashes($_items[0]['group_name'] ?? 'Items'); ?>">';
                        <?php foreach ($_items as $item): ?>
                            html += '<option value="<?php echo $item['id']; ?>" ' + (line.product_id == <?php echo $item['id']; ?> ? 'selected' : '') + '><?php echo addslashes($item['description']); ?></option>';
                        <?php endforeach; ?>
                        html += '</optgroup>';
                    <?php endforeach; ?>
                    html += '</select></td>';
                    html += '<td><input type="text" name="lines[' + line_idx + '][description]" class="form-control xb-item-desc" placeholder="Label" value="' + $('<div>').text(line.name || '').html() + '" required></td>';
                    html += '<td><select name="lines[' + line_idx + '][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" required><option value=""></option>';
                    <?php foreach ($accounts as $acc): ?>
                        html += '<option value="<?php echo $acc->id; ?>" ' + (line.account_id == <?php echo $acc->id; ?> ? 'selected' : '') + '><?php echo addslashes($acc->code . ' ' . $acc->name); ?></option>';
                    <?php endforeach; ?>
                    html += '</select></td>';
                    html += '<td><input type="number" step="any" name="lines[' + line_idx + '][qty]" class="form-control row-qty" value="' + (line.quantity || 1) + '" onchange="calculate_totals()" required></td>';
                    html += '<td><input type="number" step="any" name="lines[' + line_idx + '][price_unit]" class="form-control row-price" value="' + (line.price_unit || 0) + '" onchange="calculate_totals()" required></td>';
                    html += '<td><select name="lines[' + line_idx + '][tax_id]" class="form-control selectpicker row-tax" onchange="calculate_totals()"><option value="">No Tax</option>';
                    <?php foreach ($taxes as $tax): ?>
                        html += '<option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>" ' + (line.tax_line_id == <?php echo $tax->id; ?> ? 'selected' : '') + '><?php echo addslashes($tax->name); ?></option>';
                    <?php endforeach; ?>
                    html += '</select></td>';
                    html += '<td><button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest(\'tr\').remove(); calculate_totals();"><i class="fa fa-trash"></i></button></td>';
                    html += '</tr>';

                    $('#refund-lines tbody').append(html);
                    line_idx++;
                });

                try { $('.selectpicker').selectpicker('refresh'); } catch(e) {}
                calculate_totals();
            }
        });
    });

    $('#receipt-form').on('submit', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.button('loading');
        $.post(admin_url + 'xetuu_books/register_payment', $(this).serialize(), function (res) {
            var data = JSON.parse(res);
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
                btn.button('reset');
            }
        });
    });

    calculate_totals();

    $(document).on('change', '.xb-item-select', function () {
        var item_id = $(this).val();
        var row = $(this).closest('tr');
        if (item_id) {
            $.get(admin_url + 'invoice_items/get_item_by_id/' + item_id, function (response) {
                var item = JSON.parse(response);
                row.find('.xb-item-desc').val(item.description + (item.long_description ? ' - ' + item.long_description : ''));
                row.find('.row-price').val(item.rate);
                if (!row.find('.xb-item-account').val() && default_purchase_account) {
                    row.find('.xb-item-account').val(default_purchase_account).selectpicker('refresh');
                }
                calculate_totals();
            });
        }
    });
});

function add_refund_line() {
    var html = '<tr class="item-row">';
    html += '<td><select name="lines[' + line_idx + '][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true"><option value=""></option>';
    <?php foreach ($items as $group_id => $_items): ?>
        html += '<optgroup label="<?php echo addslashes($_items[0]['group_name'] ?? 'Items'); ?>">';
        <?php foreach ($_items as $item): ?>
            html += '<option value="<?php echo $item['id']; ?>"><?php echo addslashes($item['description']); ?></option>';
        <?php endforeach; ?>
        html += '</optgroup>';
    <?php endforeach; ?>
    html += '</select></td>';
    html += '<td><input type="text" name="lines[' + line_idx + '][description]" class="form-control xb-item-desc" placeholder="Label" required></td>';
    html += '<td><select name="lines[' + line_idx + '][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" required><option value=""></option>';
    <?php foreach ($accounts as $acc): ?>
        html += '<option value="<?php echo $acc->id; ?>" ' + (default_purchase_account == "<?php echo $acc->id; ?>" ? "selected" : "") + '><?php echo addslashes($acc->code . ' ' . $acc->name); ?></option>';
    <?php endforeach; ?>
    html += '</select></td>';
    html += '<td><input type="number" step="any" name="lines[' + line_idx + '][qty]" class="form-control row-qty" value="1" onchange="calculate_totals()" required></td>';
    html += '<td><input type="number" step="any" name="lines[' + line_idx + '][price_unit]" class="form-control row-price" value="0" onchange="calculate_totals()" required></td>';
    html += '<td><select name="lines[' + line_idx + '][tax_id]" class="form-control selectpicker row-tax" onchange="calculate_totals()"><option value="">No Tax</option>';
    <?php foreach ($taxes as $tax): ?>
        html += '<option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>"><?php echo addslashes($tax->name); ?></option>';
    <?php endforeach; ?>
    html += '</select></td>';
    html += '<td><button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest(\'tr\').remove(); calculate_totals();"><i class="fa fa-trash"></i></button></td>';
    html += '</tr>';
    $('#refund-lines tbody').append(html);
    $('.selectpicker').selectpicker('refresh');
    line_idx++;
}

function calculate_totals() {
    var untaxed = 0, tax = 0;
    $('.item-row').each(function () {
        var qty   = parseFloat($(this).find('.row-qty').val()) || 0;
        var price = parseFloat($(this).find('.row-price').val()) || 0;
        var subtotal = qty * price;
        var tax_sel = $(this).find('.row-tax option:selected');
        if (tax_sel.val() !== '') {
            var rate       = Math.abs(parseFloat(tax_sel.data('rate'))) || 0;
            var is_include = tax_sel.data('include') == 1;
            if (is_include) {
                var sub_ex = subtotal / (1 + rate / 100);
                untaxed += sub_ex;
                tax += subtotal - sub_ex;
            } else {
                untaxed += subtotal;
                tax += subtotal * rate / 100;
            }
        } else {
            untaxed += subtotal;
        }
    });
    $('#total-untaxed').text(untaxed.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
    $('#total-tax').text(tax.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
    $('#total-amount').text((untaxed + tax).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
}
</script>
