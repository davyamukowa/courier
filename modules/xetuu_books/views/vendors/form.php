<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-workspace { margin-top:0; }
.xb-header-toolbar { background:#fff; padding:15px 25px; border-bottom:1px solid #e5e7eb; margin:0 -25px 24px -25px; display:flex; justify-content:space-between; align-items:center; }
.xb-header-toolbar h3 { margin:0; font-weight:600; color:#111827; font-size:18px; }
.xb-vtabs { border-bottom:2px solid #e5e7eb; margin-bottom:24px; }
.xb-vtabs .nav-tabs { border-bottom:none; }
.xb-vtabs .nav-tabs>li>a { color:#6b7280; font-weight:500; border:none; border-bottom:2px solid transparent; margin-bottom:-2px; padding:10px 18px; border-radius:0; font-size:14px; }
.xb-vtabs .nav-tabs>li.active>a,
.xb-vtabs .nav-tabs>li.active>a:hover,
.xb-vtabs .nav-tabs>li.active>a:focus { color:#1a6b3a; border-bottom:2px solid #1a6b3a; background:transparent; }
.xb-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:20px; }
.xb-card-header { padding:13px 20px; border-bottom:1px solid #e5e7eb; font-weight:600; color:#374151; font-size:13px; background:#f9fafb; border-radius:8px 8px 0 0; text-transform:uppercase; letter-spacing:.04em; }
.xb-card-body { padding:22px; }
.xb-form-label { font-size:13px; font-weight:500; color:#374151; margin-bottom:6px; display:block; }
.xb-form-label .req { color:#dc2626; margin-left:2px; }
.form-control { border-color:#d1d5db; border-radius:6px; font-size:14px; }
.form-control:focus { border-color:#1a6b3a; box-shadow:0 0 0 3px rgba(26,107,58,.1); outline:none; }
.xb-addr-head { font-weight:600; color:#111827; font-size:15px; margin:0 0 4px; }
.xb-addr-link { font-size:12px; color:#1a6b3a; text-decoration:none; float:right; font-weight:500; }
.xb-addr-link:hover { text-decoration:underline; }
hr.xb-hr { border-color:#e5e7eb; margin:10px 0 16px; }
</style>

<div class="xb-workspace">

    <!-- Toolbar -->
    <div class="xb-header-toolbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="<?php echo admin_url('xetuu_books/vendors'); ?>" class="btn btn-default btn-sm" title="Back to vendors"><i class="fa fa-arrow-left"></i></a>
            <h3><?php echo isset($vendor) ? htmlspecialchars($vendor->company ?: 'Edit Vendor') : 'New Vendor'; ?></h3>
        </div>
        <div>
            <a href="<?php echo admin_url('xetuu_books/vendors'); ?>" class="btn btn-default" style="margin-right:8px;">Cancel</a>
            <button type="submit" form="vendor-form" class="btn btn-primary" style="font-weight:500;min-width:100px;">Save</button>
        </div>
    </div>

    <?php echo form_open(admin_url('xetuu_books/vendor_form/' . (isset($vendor) ? $vendor->userid : '')), ['id' => 'vendor-form', 'autocomplete' => 'off']); ?>

    <!-- Tab Navigation -->
    <div class="xb-vtabs">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab-detail" role="tab" data-toggle="tab">Vendor Detail</a></li>
            <li role="presentation"><a href="#tab-billing" role="tab" data-toggle="tab">Billing &amp; Shipping</a></li>
            <li role="presentation"><a href="#tab-returns" role="tab" data-toggle="tab">Return Policies</a></li>
        </ul>
    </div>

    <div class="tab-content">

        <!-- ══════════════════════════════════════════════════
             TAB 1 — VENDOR DETAIL
        ══════════════════════════════════════════════════ -->
        <div role="tabpanel" class="tab-pane active" id="tab-detail">
            <div class="row">
                <!-- Left column -->
                <div class="col-md-6">
                    <div class="xb-card">
                        <div class="xb-card-header">Profile</div>
                        <div class="xb-card-body">
                            <div class="form-group">
                                <label class="xb-form-label">Vendor Code</label>
                                <input type="text" name="vendor_code" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->vendor_code : ''); ?>"
                                    placeholder="e.g. VND-001">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Company <span class="req">*</span></label>
                                <input type="text" name="company" class="form-control" required autofocus
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->company : ''); ?>"
                                    placeholder="Company name">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">VAT Number</label>
                                <input type="text" name="vat" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->vat : ''); ?>"
                                    placeholder="e.g. P051234567X">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Phone</label>
                                <input type="text" name="phonenumber" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->phonenumber : ''); ?>"
                                    placeholder="+254 700 000 000">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Website</label>
                                <input type="text" name="website" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->website : ''); ?>"
                                    placeholder="https://example.com">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Vendor Category</label>
                                <select name="category[]" class="form-control selectpicker" data-live-search="true" multiple
                                    data-none-selected-text="— None —" data-width="100%">
                                    <?php
                                    $selected_cats = [];
                                    if (isset($vendor) && !empty($vendor->category)) {
                                        $selected_cats = explode(',', $vendor->category);
                                    }
                                    foreach ($vendor_categories as $vc):
                                    ?>
                                    <option value="<?php echo $vc['id']; ?>"
                                        <?php echo in_array($vc['id'], $selected_cats) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vc['category_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Currency</label>
                                <select name="default_currency" class="form-control selectpicker" data-none-selected-text="— System Default —">
                                    <option value="0">— System Default —</option>
                                    <?php foreach ($currencies as $cur):
                                        $sel = (isset($vendor) && $vendor->default_currency == $cur->id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $cur->id; ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($cur->name . ' (' . $cur->symbol . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Default Language</label>
                                <select name="default_language" class="form-control selectpicker" data-none-selected-text="— System Default —">
                                    <option value="">— System Default —</option>
                                    <?php foreach ($languages as $lang):
                                        $sel = (isset($vendor) && $vendor->default_language == $lang) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $lang; ?>" <?php echo $sel; ?>><?php echo ucfirst($lang); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="col-md-6">
                    <div class="xb-card">
                        <div class="xb-card-header">Address</div>
                        <div class="xb-card-body">
                            <div class="form-group">
                                <label class="xb-form-label">Street</label>
                                <textarea name="address" class="form-control" rows="3"
                                    placeholder="Street, building, suite…"><?php echo htmlspecialchars(isset($vendor) ? $vendor->address : ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">City</label>
                                <input type="text" name="city" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->city : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">State</label>
                                <input type="text" name="state" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->state : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Zip Code</label>
                                <input type="text" name="zip" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->zip : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Country</label>
                                <select name="country" class="form-control selectpicker" data-live-search="true"
                                    data-none-selected-text="— Select Country —">
                                    <option value="0">— Select Country —</option>
                                    <?php foreach ($countries as $c):
                                        $sel = (isset($vendor) && $vendor->country == $c->country_id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $c->country_id; ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($c->short_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="xb-card">
                        <div class="xb-card-header">Banking &amp; Terms</div>
                        <div class="xb-card-body">
                            <div class="form-group">
                                <label class="xb-form-label">Bank Details</label>
                                <textarea name="bank_detail" class="form-control" rows="3"
                                    placeholder="Bank name, account number, branch…"><?php echo htmlspecialchars(isset($vendor) ? $vendor->bank_detail : ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Payment Terms</label>
                                <textarea name="payment_terms" class="form-control" rows="3"
                                    placeholder="e.g. Net 30 days"><?php echo htmlspecialchars(isset($vendor) ? $vendor->payment_terms : ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /tab-detail -->


        <!-- ══════════════════════════════════════════════════
             TAB 2 — BILLING & SHIPPING
        ══════════════════════════════════════════════════ -->
        <div role="tabpanel" class="tab-pane" id="tab-billing">
            <div class="row">
                <!-- Billing -->
                <div class="col-md-6">
                    <div class="xb-card">
                        <div class="xb-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Billing Address</span>
                            <a href="#" class="xb-addr-link" id="billing-same-as-profile">Same as Vendor Info</a>
                        </div>
                        <div class="xb-card-body">
                            <div class="form-group">
                                <label class="xb-form-label">Street</label>
                                <textarea name="billing_street" id="billing_street" class="form-control" rows="3"><?php echo htmlspecialchars(isset($vendor) ? $vendor->billing_street : ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">City</label>
                                <input type="text" name="billing_city" id="billing_city" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->billing_city : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">State</label>
                                <input type="text" name="billing_state" id="billing_state" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->billing_state : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Zip Code</label>
                                <input type="text" name="billing_zip" id="billing_zip" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->billing_zip : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Country</label>
                                <select name="billing_country" id="billing_country" class="form-control selectpicker" data-live-search="true"
                                    data-none-selected-text="— Select Country —">
                                    <option value="0">— Select Country —</option>
                                    <?php foreach ($countries as $c):
                                        $sel = (isset($vendor) && $vendor->billing_country == $c->country_id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $c->country_id; ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($c->short_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping -->
                <div class="col-md-6">
                    <div class="xb-card">
                        <div class="xb-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Shipping Address</span>
                            <a href="#" class="xb-addr-link" id="copy-billing-to-shipping">Copy Billing Address</a>
                        </div>
                        <div class="xb-card-body">
                            <div class="form-group">
                                <label class="xb-form-label">Street</label>
                                <textarea name="shipping_street" id="shipping_street" class="form-control" rows="3"><?php echo htmlspecialchars(isset($vendor) ? $vendor->shipping_street : ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">City</label>
                                <input type="text" name="shipping_city" id="shipping_city" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->shipping_city : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">State</label>
                                <input type="text" name="shipping_state" id="shipping_state" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->shipping_state : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Zip Code</label>
                                <input type="text" name="shipping_zip" id="shipping_zip" class="form-control"
                                    value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->shipping_zip : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Country</label>
                                <select name="shipping_country" id="shipping_country" class="form-control selectpicker" data-live-search="true"
                                    data-none-selected-text="— Select Country —">
                                    <option value="0">— Select Country —</option>
                                    <?php foreach ($countries as $c):
                                        $sel = (isset($vendor) && $vendor->shipping_country == $c->country_id) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $c->country_id; ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($c->short_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /tab-billing -->


        <!-- ══════════════════════════════════════════════════
             TAB 3 — RETURN POLICIES
        ══════════════════════════════════════════════════ -->
        <div role="tabpanel" class="tab-pane" id="tab-returns">
            <div class="row">
                <div class="col-md-8">
                    <div class="xb-card">
                        <div class="xb-card-header">Return Policies</div>
                        <div class="xb-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="xb-form-label">Return request must be placed within X days after delivery</label>
                                        <input type="number" name="return_within_day" class="form-control" min="1"
                                            value="<?php echo htmlspecialchars(isset($vendor) && $vendor->return_within_day !== null ? $vendor->return_within_day : get_option('pur_return_request_within_x_day')); ?>"
                                            placeholder="e.g. 30">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="xb-form-label">Fee for Return Order</label>
                                        <input type="number" name="return_order_fee" class="form-control" step="any" min="0"
                                            value="<?php echo htmlspecialchars(isset($vendor) ? $vendor->return_order_fee : ''); ?>"
                                            placeholder="e.g. 500">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="xb-form-label">Return Policies Information</label>
                                <textarea name="return_policies" class="form-control" rows="6"
                                    placeholder="Describe the return policy terms and conditions…"><?php echo htmlspecialchars(isset($vendor) ? $vendor->return_policies : ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="xb-card" style="border-color:#fef3c7;">
                        <div class="xb-card-body" style="font-size:13px;color:#92400e;background:#fffbeb;border-radius:8px;">
                            <strong>About Return Policies</strong>
                            <p style="margin-top:8px;margin-bottom:0;">These settings override the global return policy defaults for this vendor. Leave blank to use the system defaults.</p>
                        </div>
                    </div>
                    <?php if (isset($vendor)): ?>
                    <div class="xb-card" style="border-color:#fee2e2;">
                        <div class="xb-card-body" style="padding:16px;">
                            <p style="margin:0 0 10px;font-size:13px;color:#6b7280;">Removing a vendor is permanent and cannot be undone.</p>
                            <a href="<?php echo admin_url('xetuu_books/delete_vendor/' . $vendor->userid); ?>"
                               class="btn btn-danger btn-block"
                               onclick="return confirm('Delete this vendor permanently?');">
                               <i class="fa fa-trash"></i> Delete Vendor
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /tab-returns -->

    </div><!-- /tab-content -->

    <?php echo form_close(); ?>
</div>

<script>
$(function () {
    // Init selectpickers
    if ($.fn.selectpicker) {
        $('#vendor-form .form-control.selectpicker').selectpicker();
    }

    // "Same as Vendor Info" — copies profile address fields into billing
    $('#billing-same-as-profile').on('click', function (e) {
        e.preventDefault();
        $('[name="billing_street"]').val($('[name="address"]').val());
        $('[name="billing_city"]').val($('[name="city"]').val());
        $('[name="billing_state"]').val($('[name="state"]').val());
        $('[name="billing_zip"]').val($('[name="zip"]').val());
        var countryVal = $('[name="country"]').val();
        $('[name="billing_country"]').val(countryVal);
        if ($.fn.selectpicker) { $('[name="billing_country"]').selectpicker('val', countryVal); }
    });

    // "Copy Billing Address" — copies billing into shipping
    $('#copy-billing-to-shipping').on('click', function (e) {
        e.preventDefault();
        $('[name="shipping_street"]').val($('[name="billing_street"]').val());
        $('[name="shipping_city"]').val($('[name="billing_city"]').val());
        $('[name="shipping_state"]').val($('[name="billing_state"]').val());
        $('[name="shipping_zip"]').val($('[name="billing_zip"]').val());
        var countryVal = $('[name="billing_country"]').val();
        $('[name="shipping_country"]').val(countryVal);
        if ($.fn.selectpicker) { $('[name="shipping_country"]').selectpicker('val', countryVal); }
    });
});
</script>
