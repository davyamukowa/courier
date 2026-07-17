<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $is_posted = (isset($move) && $move->state == 'posted'); ?>


<style>
/* ── Page shell ─────────────────────────────────────────────── */
.xb-content-wrapper { padding: 0 !important; }
.xb-inv-page { display: flex; flex-direction: column; min-height: calc(100vh - 60px); background: #f3f4f6; }

/* ── Top header bar ─────────────────────────────────────────── */
.xb-inv-header {
    background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
}
.xb-inv-header-left { display: flex; align-items: center; gap: 10px; }
.xb-inv-back-btn {
    display: flex; align-items: center; gap: 6px; color: #6b7280; font-size: 13px; font-weight: 500; text-decoration: none; padding: 6px 10px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; transition: all .15s;
}
.xb-inv-back-btn:hover { background: #f9fafb; color: #374151; text-decoration: none; }
.xb-inv-header-title { display: flex; flex-direction: column; gap: 1px; }
.xb-inv-breadcrumb { font-size: 11px; color: #9ca3af; }
.xb-inv-breadcrumb a { color: #9ca3af; text-decoration: none; }
.xb-inv-breadcrumb a:hover { color: #1a6b3a; }
.xb-inv-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px; }
.xb-inv-header-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.xb-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: #1a6b3a; color: #fff; border: none; border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
.xb-btn-primary:hover { background: #155a30; color: #fff; }
.xb-btn-outline { display: inline-flex; align-items: center; gap: 6px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 7px; padding: 8px 14px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s; }
.xb-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; color: #111827; text-decoration: none; }

/* ── Status badge ───────────────────────────────────────────── */
.xb-inv-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }
.xb-inv-badge.paid { background: #dcfce7; color: #15803d; }
.xb-inv-badge.draft { background: #f3f4f6; color: #6b7280; }

/* ── KPI stats strip ────────────────────────────────────────── */
.xb-inv-stats-strip { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 24px; display: flex; align-items: stretch; gap: 0; overflow-x: auto; }
.xb-inv-stat { display: flex; flex-direction: column; justify-content: center; padding: 12px 24px 12px 0; margin-right: 24px; border-right: 1px solid #f0f0f0; gap: 2px; min-width: 120px; flex-shrink: 0; }
.xb-inv-stat:last-child { border-right: none; }
.xb-stat-label { font-size: 10.5px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.xb-stat-val   { font-size: 20px; font-weight: 800; color: #111827; line-height: 1.1; }
.xb-stat-sub   { font-size: 11px; color: #6b7280; margin-top: 1px; }
.xb-stat-trend-up   { color: #16a34a; font-weight: 600; font-size: 11px; }
.xb-stat-trend-down { color: #dc2626; font-weight: 600; font-size: 11px; }

/* Metric row (conversion rates) */
.xb-metric-row { margin-bottom: 14px; }
.xb-metric-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.xb-metric-label { font-size: 12px; font-weight: 600; color: #374151; }
.xb-metric-pct { font-size: 13px; font-weight: 800; color: #111827; }
.xb-metric-track { height: 6px; background: #f3f4f6; border-radius: 4px; overflow: hidden; margin-bottom: 3px; }
.xb-metric-fill { height: 100%; border-radius: 4px; transition: width .4s ease; }
.xb-metric-fill.green  { background: #16a34a; }
.xb-metric-fill.blue   { background: #3b82f6; }
.xb-metric-sub { font-size: 10.5px; color: #9ca3af; }

/* Decline rate hero card */
.xb-decline-card {
    background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
    border: 1px solid #fed7aa; border-radius: 10px; padding: 14px;
    display: flex; align-items: center; justify-content: space-between;
}
.xb-decline-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 2px; }
.xb-decline-val   { font-size: 26px; font-weight: 800; color: #dc2626; line-height: 1; }
.xb-decline-trend { font-size: 11px; color: #16a34a; font-weight: 600; margin-top: 2px; }
.xb-decline-icon  { font-size: 28px; color: #fed7aa; }

/* ── Main content layout ────────────────────────────────────── */
.xb-inv-body { display: flex; align-items: flex-start; flex: 1; min-height: 0; }
.xb-inv-main { flex: 1; min-width: 0; padding: 20px 24px; }

/* ── Right sidebar ──────────────────────────────────────────── */
.xb-inv-sidebar { width: 280px; flex-shrink: 0; background: #fff; border-left: 1px solid #e5e7eb; padding: 16px; min-height: calc(100vh - 140px); display: flex; flex-direction: column; gap: 14px; }
.xb-sw { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.xb-sw-head { background: #f9fafb; padding: 12px 14px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; }
.xb-sw-title { font-size: 12px; font-weight: 700; color: #374151; margin: 0; text-transform: uppercase; letter-spacing: .05em; }
.xb-sw-body { padding: 14px; background: #fff; }

.xb-collapsible-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px; }
.xb-section-body { padding: 20px; }

/* Sticky bottom toolbar */
.btn-bottom-toolbar {
    position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
    padding: 12px 24px; border-top: 1px solid #e5e7eb; display: flex;
    justify-content: flex-end; gap: 10px; z-index: 50; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
}
.btn-bottom-toolbar.hide { display: none !important; }
.xb-tabs { margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
.xb-tabs .nav-tabs { border-bottom: none; }
.xb-tabs .nav-tabs>li>a { color: #6b7280; font-weight: 500; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: 10px 15px; border-radius: 0; }
.xb-tabs .nav-tabs>li.active>a { color: #1a6b3a; border-bottom: 2px solid #1a6b3a; background: transparent; }
.xb-totals-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.xb-totals-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 18px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
.xb-totals-row:last-child { border-bottom: none; }
.xb-totals-label { color: #6b7280; font-weight: 500; }
.xb-totals-value { color: #374151; font-weight: 600; }
.xb-totals-final { background: #f0fdf4; border-top: 2px solid #bbf7d0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
.xb-totals-label-final { color: #14532d; font-weight: 700; font-size: 14px; }
.xb-totals-value-final { color: #15803d; font-weight: 700; font-size: 20px; }
.xb-receipt-accent { border-left: 4px solid #0ea5e9; background: #f0f9ff; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 13px; color: #0c4a6e; margin-bottom: 16px; }
</style>

<div class="xb-inv-page">
    <div class="xb-inv-header">
        <div class="xb-inv-header-left">
            <a href="<?php echo admin_url('xetuu_books/vendor_receipts'); ?>" class="xb-inv-back-btn">
                <i class="fa fa-arrow-left"></i> <?= _l('back'); ?>
            </a>
            <div class="xb-inv-header-title">
                <div class="xb-inv-breadcrumb">
                    <a href="<?php echo admin_url('xetuu_books'); ?>">Xetuu Books</a> &rsaquo; 
                    <a href="<?php echo admin_url('xetuu_books/vendor_receipts'); ?>">Purchase Receipts</a>
                </div>
                <h1 class="xb-inv-title">
                    <?php echo isset($move) ? $move->name : 'New Purchase Receipt'; ?>
                    <?php if ($is_posted): ?>
                        <span class="xb-inv-badge paid"><i class="fa fa-check-circle"></i> Paid</span>
                    <?php else: ?>
                        <span class="xb-inv-badge draft"><i class="fa fa-pencil"></i> Draft</span>
                    <?php endif; ?>
                </h1>
            </div>
        </div>
        <div class="xb-inv-header-actions">
            <?php if (!$is_posted): ?>
            <button type="button" onclick="$('#receipt-form').submit();" class="xb-btn-primary">
                <i class="fa fa-floppy-o"></i> Save &amp; Post
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Strip -->
    <div class="xb-inv-stats-strip">
        <div class="xb-inv-stat">
            <span class="xb-stat-label">Draft</span>
            <span class="xb-stat-val">0</span>
        </div>
        <div class="xb-inv-stat">
            <span class="xb-stat-label">Posted</span>
            <span class="xb-stat-val">0</span>
        </div>
    </div>

    <div class="xb-inv-body">
        <div class="xb-inv-main">
<?php echo form_open(admin_url('xetuu_books/vendor_receipt/' . (isset($move) ? $move->id : '')), ['id' => 'receipt-form']); ?>

            <!-- Header Fields -->
            <div class="xb-collapsible-section" style="margin-bottom:20px;">
                <div class="xb-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor <small class="text-muted">(optional)</small></label>
                                <select name="partner_id" class="form-control selectpicker" data-live-search="true" <?php echo $is_posted ? 'disabled' : ''; ?>>
                                    <option value="">— Cash / Unknown Vendor —</option>
                                    <?php foreach ($vendors as $v):
                                        $selected = (isset($move) && $move->partner_id == $v->userid) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $v->userid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($v->company); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Description / Reference</label>
                                <input type="text" name="ref" class="form-control" value="<?php echo isset($move) ? htmlspecialchars($move->ref ?? '') : ''; ?>" <?php echo $is_posted ? 'disabled' : ''; ?> placeholder="e.g. Fuel purchase, Shop receipt #123">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="<?php echo isset($move) ? $move->date : date('Y-m-d'); ?>" <?php echo $is_posted ? 'disabled' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label>Payment Account <span class="text-danger">*</span></label>
                                <select name="payment_journal_id" class="form-control selectpicker" <?php echo $is_posted ? 'disabled' : ''; ?> required>
                                    <option value="">— Select Cash/Bank Account —</option>
                                    <?php foreach ($payment_journals as $j):
                                        $selected = (isset($move) && $move->journal_id == $j->id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $j->id; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($j->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="help-block" style="font-size:12px;margin-top:4px;">Which account was used to pay for this purchase?</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content">

                <!-- Purchase Lines Tab -->
                <div class="tab-pane active" id="tab-lines">
                    <div class="xb-collapsible-section">
                        <div class="xb-section-body" style="padding:0;">
                            <table class="table" id="receipt-lines" style="margin-bottom:0;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th width="20%">Item</th>
                                        <th width="20%">Description</th>
                                        <th width="20%">Expense Account</th>
                                        <th width="10%">Qty</th>
                                        <th width="12%">Price</th>
                                        <th width="13%">Tax</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $lines_to_show = !empty($invoice_lines) ? $invoice_lines : [(object)[
                                        'product_id' => '', 'name' => '', 'account_id' => '',
                                        'quantity' => 1, 'price_unit' => 0, 'tax_line_id' => '',
                                    ]];
                                    $idx = 0;
                                    foreach ($lines_to_show as $line):
                                    ?>
                                    <tr class="item-row">
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][item_id]" class="form-control selectpicker xb-item-select" data-live-search="true" <?php echo $is_posted ? 'disabled' : ''; ?>>
                                                <option value=""></option>
                                                <?php foreach ($items as $group_id => $_items): ?>
                                                <optgroup label="<?php echo $_items[0]['group_name'] ?? 'Items'; ?>">
                                                    <?php foreach ($_items as $item): ?>
                                                    <option value="<?php echo $item['id']; ?>" <?php echo ($line->product_id == $item['id']) ? 'selected' : ''; ?>><?php echo $item['description']; ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="lines[<?php echo $idx; ?>][description]" class="form-control xb-item-desc" value="<?php echo htmlspecialchars($line->name); ?>" placeholder="What was purchased" <?php echo $is_posted ? 'disabled' : ''; ?> required></td>
                                        <td>
                                            <?php $def_acc = !empty($line->account_id) ? $line->account_id : (isset($default_purchase_account) ? $default_purchase_account : ''); ?>
                                            <select name="lines[<?php echo $idx; ?>][account_id]" class="form-control selectpicker xb-item-account" data-live-search="true" <?php echo $is_posted ? 'disabled' : ''; ?> required>
                                                <option value=""></option>
                                                <?php foreach ($accounts as $acc): ?>
                                                <option value="<?php echo $acc->id; ?>" <?php echo ($def_acc == $acc->id || $def_acc == $acc->code) ? 'selected' : ''; ?>><?php echo $acc->code . ' ' . $acc->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][qty]" class="form-control row-qty" value="<?php echo (float)$line->quantity; ?>" onchange="calculate_totals()" <?php echo $is_posted ? 'disabled' : ''; ?> required></td>
                                        <td><input type="number" step="any" name="lines[<?php echo $idx; ?>][price_unit]" class="form-control row-price" value="<?php echo (float)$line->price_unit; ?>" onchange="calculate_totals()" <?php echo $is_posted ? 'disabled' : ''; ?> required></td>
                                        <td>
                                            <select name="lines[<?php echo $idx; ?>][tax_id]" class="form-control selectpicker row-tax" <?php echo $is_posted ? 'disabled' : ''; ?> onchange="calculate_totals()">
                                                <option value="">No Tax</option>
                                                <?php foreach ($taxes as $tax): ?>
                                                <option value="<?php echo $tax->id; ?>" data-rate="<?php echo $tax->amount; ?>" data-include="<?php echo $tax->price_include; ?>" <?php echo ($line->tax_line_id == $tax->id) ? 'selected' : ''; ?>><?php echo $tax->name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <?php if (!$is_posted): ?>
                                            <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="$(this).closest('tr').remove(); calculate_totals();"><i class="fa fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $idx++; endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (!$is_posted): ?>
                            <div style="padding:10px 20px;">
                                <button type="button" class="btn btn-default btn-sm" onclick="add_receipt_line()"><i class="fa fa-plus"></i> Add Line</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row" style="margin-top:20px;">
                        <div class="col-md-5 col-md-offset-7">
                            <div class="xb-totals-card">
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Subtotal</span>
                                    <span class="xb-totals-value" id="total-untaxed">0.00</span>
                                </div>
                                <div class="xb-totals-row">
                                    <span class="xb-totals-label">Tax</span>
                                    <span class="xb-totals-value" id="total-tax">0.00</span>
                                </div>
                                <div class="xb-totals-final">
                                    <span class="xb-totals-label-final">Total Paid</span>
                                    <span class="xb-totals-value-final" id="total-amount">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_posted): ?>
                <div class="tab-pane" id="tab-journal">
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

            </div>
        </div>

                <!-- We must close the form BEFORE the sidebar so our button submit works? No, let's keep form open until the end -->
        <div class="btn-bottom-toolbar text-right">
            <button class="btn btn-default" type="button" onclick="window.location.href='<?php echo admin_url('xetuu_books/vendor_receipts'); ?>'"><?php echo _l('cancel'); ?></button>
            <?php if (!$is_posted): ?>
            <button class="btn btn-info" type="button" onclick="$('#receipt-form').submit();"><?php echo _l('submit'); ?></button>
            <?php endif; ?>
        </div>
        
        </div> <!-- end xb-inv-main -->

        <div class="xb-inv-sidebar">
            
            <!-- Overdue Rate hero -->
            <div class="xb-decline-card">
                <div>
                <div class="xb-decline-label">Overdue Rate</div>
                <div class="xb-decline-val"><?php echo $xb_stat_overdue_rate ?? 0; ?>%</div>
                <div class="xb-decline-trend">Overall overdue percentage</div>
                </div>
                <div class="xb-decline-icon"><i class="fa fa-exclamation-circle" style="color:#f97316;"></i></div>
            </div>

            <!-- Conversion metrics -->
            <div class="xb-sw">
                <div class="xb-sw-head">
                Receipt Velocity
                <span style="font-size:10px;font-weight:500;color:#9ca3af;">Last 30 days</span>
                </div>
                <div class="xb-sw-body">

                <div class="xb-metric-row">
                    <div class="xb-metric-top">
                    <span class="xb-metric-label">Draft to Posted</span>
                    <span class="xb-stat-trend-up"></span>
                    </div>
                    <div class="xb-metric-track"><div class="xb-metric-fill green" style="width:<?php echo $xb_stat_draft_to_sent ?? 0; ?>%"></div></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="xb-metric-sub">Percent converted</span>
                    <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_draft_to_sent ?? 0; ?>%</span>
                    </div>
                </div>

                <div class="xb-metric-row">
                    <div class="xb-metric-top">
                    <span class="xb-metric-label">Avg Days to Post</span>
                    <span class="xb-stat-trend-down"></span>
                    </div>
                    <div class="xb-metric-track"><div class="xb-metric-fill blue" style="width:<?php echo $xb_stat_avg_days ?? 0; ?>%"></div></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="xb-metric-sub">Average time</span>
                    <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_avg_days ?? 0; ?> days</span>
                    </div>
                </div>

                </div>
            </div>
            
            <!-- Analytic Account -->
            <div class="xb-sw">
                <div class="xb-sw-head">
                    <i class="fa fa-pie-chart text-muted"></i>
                    <h3 class="xb-sw-title">Analytic Account</h3>
                </div>
                <div class="xb-sw-body" id="xb-analytic-account-container" style="background: #fdfdfc;">
                    <div class="text-center text-muted" style="padding: 20px 0;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>

            <!-- Rubber Stamp Preview -->
            <div class="xb-sw">
                <div class="xb-sw-head">
                    <i class="fa fa-certificate text-muted"></i>
                    <h3 class="xb-sw-title">Stamp Preview</h3>
                </div>
                <div class="xb-sw-body" style="background:#fdfdfc; display:flex; justify-content:center; padding:30px 10px;">
                    <?php 
                    // Render the stamp
                    $stamp_color = '#15803d'; // Green for Paid
                    if (!$is_posted) {
                        $stamp_color = '#6b7280'; // Gray for Draft
                    }
                    $stamp_status = $is_posted ? 'PAID' : 'DRAFT';
                    $stamp_text = get_option('invoice_company_name'); 
                    if(empty($stamp_text)) $stamp_text = 'COMPANY NAME';
                    ?>
                    
                    <div style="position:relative; width:160px; height:160px; border:4px solid <?= $stamp_color; ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; transform:rotate(-15deg); opacity:0.8; padding:10px;">
                        <div style="position:absolute; inset:5px; border:1px solid <?= $stamp_color; ?>; border-radius:50%;"></div>
                        <svg viewBox="0 0 160 160" style="position:absolute; width:100%; height:100%; top:0; left:0; animation: xb-spin-slow 20s linear infinite;">
                            <defs>
                                <path id="curve" d="M 20 80 a 60 60 0 1 1 120 0 a 60 60 0 1 1 -120 0" />
                            </defs>
                            <text fill="<?= $stamp_color; ?>" font-size="14" font-weight="bold" letter-spacing="2">
                                <textPath href="#curve" startOffset="50%" text-anchor="middle">
                                    <?= e($stamp_text); ?> • <?= e($stamp_text); ?> •
                                </textPath>
                            </text>
                        </svg>
                        <div style="text-align:center; z-index:2;">
                            <div style="color:<?= $stamp_color; ?>; font-size:24px; font-weight:900; letter-spacing:1px; line-height:1;">
                                <?= $stamp_status; ?>
                            </div>
                            <div style="color:<?= $stamp_color; ?>; font-size:10px; font-weight:600; margin-top:4px;">
                                <?= isset($move) ? _d(date('Y-m-d')) : _d(date('Y-m-d')); ?>
                            </div>
                            <div style="color:<?= $stamp_color; ?>; font-size:9px; font-weight:600; margin-top:2px;">
                                BY <?= e(get_staff_full_name()); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legacy timeline / summary from old sidebar -->
            <div class="xb-sw">
                <div class="xb-sw-head">
                    <i class="fa fa-info-circle text-muted"></i>
                    <h3 class="xb-sw-title">Details</h3>
                </div>
                <div class="xb-sw-body" style="font-size: 13px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span class="text-muted">Total Paid</span>
                        <strong><?php echo xb_format_money(isset($move) ? $move->amount_total : 0); ?></strong>
                    </div>
                    <?php if ($is_posted): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                        <h4 style="font-size:11px; text-transform:uppercase; color:#9ca3af; margin-top:0;">Accounting</h4>
                        <div style="font-size:12px;color:#6b7280;">
                            <div><strong style="color:#111;">DR</strong> Expense Account</div>
                            <div style="padding-left:16px;color:#374151;"><?php echo xb_format_money($move->amount_total); ?></div>
                            <div style="margin-top:6px;"><strong style="color:#111;">CR</strong> <?php echo htmlspecialchars($move->journal_name ?? 'Bank/Cash'); ?></div>
                            <div style="padding-left:16px;color:#374151;"><?php echo xb_format_money($move->amount_total); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div> <!-- end xb-inv-sidebar -->
    </div> <!-- end xb-inv-body -->
    <?php echo form_close(); ?>
</div>


</div>

<script>
var line_idx = <?php echo $idx ?? 1; ?>;
var default_purchase_account = "<?php echo isset($default_purchase_account) ? $default_purchase_account : ''; ?>";

function add_receipt_line() {
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
    html += '<td><input type="text" name="lines[' + line_idx + '][description]" class="form-control xb-item-desc" placeholder="What was purchased" required></td>';
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
    $('#receipt-lines tbody').append(html);
    $('.selectpicker').selectpicker('refresh');
    line_idx++;
}

function calculate_totals() {
    var untaxed = 0, tax = 0;
    $('.item-row').each(function () {
        var qty   = parseFloat($(this).find('.row-qty').val()) || 0;
        var price = parseFloat($(this).find('.row-price').val()) || 0;
        var subtotal = qty * price;
        var tax_sel  = $(this).find('.row-tax option:selected');
        if (tax_sel.val()) {
            var rate      = Math.abs(parseFloat(tax_sel.data('rate'))) || 0;
            var is_incl   = tax_sel.data('include') == 1;
            if (is_incl) {
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

document.addEventListener('DOMContentLoaded', function () {
    calculate_totals();

    $(document).on('change', '.xb-item-select', function () {
        var item_id = $(this).val();
        var row = $(this).closest('tr');
        if (item_id) {
            $.get(admin_url + 'invoice_items/get_item_by_id/' + item_id, function (res) {
                var item = JSON.parse(res);
                row.find('.xb-item-desc').val(item.description + (item.long_description ? ' — ' + item.long_description : ''));
                row.find('.row-price').val(item.rate);
                if (!row.find('.xb-item-account').val() && default_purchase_account) {
                    row.find('.xb-item-account').val(default_purchase_account).selectpicker('refresh');
                }
                calculate_totals();
            });
        }
    });
});
</script>

<?php
$recordIdForAnalytic = isset($move) ? $move->id : 0;
?>

<script>
(function(){
    var GET_URL = admin_url + 'xetuu_books/get_analytic_assignment';
    var WIDGET_URL = admin_url + 'xetuu_books/render_analytic_widget';
    var recordId = <?php echo $recordIdForAnalytic; ?>;
    var formType = 'vendor_receipt';

    function injectWidget(accountId) {
        var params = { form_type: formType, record_id: recordId };
        if (accountId) {
            params.analytic_account_id = accountId;
        }
        
        $.get(WIDGET_URL, params, function(html) {
            $('#xb-analytic-account-container').html(html);
        }, 'html');
    }

    function init() {
        if (recordId > 0) {
            $.get(GET_URL, { form_type: formType, record_id: recordId }, function(resp) {
                var currentId = (resp.success && resp.data) ? resp.data.analytic_account_id : 0;
                injectWidget(currentId);
            }, 'json').fail(function(){ injectWidget(0); });
        } else {
            injectWidget(0);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 300);
    }
})();
</script>