<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
.profile-card{border-radius:8px;overflow:hidden;border:1px solid #e5e9f2;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px}
.profile-card .panel-body{padding:18px 20px 14px}
.profile-card .panel-footer{background:#f8f9fc;border-top:1px solid #e5e9f2;padding:10px 16px;display:flex;align-items:center;justify-content:space-between}
.profile-hdr{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px}
.profile-hdr h4{margin:0;font-size:15px;font-weight:700;color:#1a2340}
.profile-meta{font-size:12px;color:#7e8a9a;margin-bottom:12px}
.profile-meta i{margin-right:3px}
.pf-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;letter-spacing:.4px}
.pf-badge.on{background:#e8f8f0;color:#1a9d5a}
.pf-badge.off{background:#fde8e8;color:#c0392b}
.pf-toggles{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px}
.pf-tog{display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 8px;border-radius:20px}
.pf-tog.on{background:#e9f7ef;color:#27ae60}
.pf-tog.off{background:#fdf3f3;color:#e74c3c}
.pf-tog i{font-size:10px}
.user-stat{font-size:12px;color:#7e8a9a;display:flex;align-items:center;gap:4px}
#profileModal .modal-body{padding:0}
#profileModal .nav-tabs{padding:0 20px;margin:0;background:#f8f9fc;border-bottom:2px solid #e5e9f2}
#profileModal .nav-tabs>li>a{padding:12px 16px;font-size:13px;font-weight:600;color:#5a6480;border:none;border-bottom:2px solid transparent;margin-bottom:-2px}
#profileModal .nav-tabs>li.active>a,#profileModal .nav-tabs>li>a:hover{color:#3d5af1;border-bottom-color:#3d5af1;background:transparent}
#profileModal .tab-content{padding:20px 24px}
.sec-ttl{font-size:12px;font-weight:700;color:#5a6480;text-transform:uppercase;letter-spacing:.6px;margin:18px 0 12px;padding-bottom:6px;border-bottom:1px solid #e5e9f2}
.sec-ttl:first-child{margin-top:0}
#profileUsersTable th{font-size:12px;color:#5a6480;font-weight:600;background:#f8f9fc}
#profileUsersTable td{vertical-align:middle;font-size:13px}
.btn-star{background:none;border:none;padding:2px 6px;cursor:pointer;font-size:16px;line-height:1;color:#ccc}
.btn-star.on{color:#f39c12}
.btn-star:hover{color:#f39c12}
</style>

<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin"><?php echo _l('pos_profiles'); ?></h3>
          <button class="btn btn-primary pull-right" onclick="openProfileModal(null)">
            <i class="fa fa-plus"></i> <?php echo _l('pos_add_profile'); ?>
          </button>
        </div>
      </div>
    </div>

    <div class="row" id="profiles-grid">
      <?php foreach ($profiles as $p): ?>
      <div class="col-md-4 col-sm-6">
        <div class="profile-card">
          <div class="panel-body">
            <div class="profile-hdr">
              <h4><?php echo htmlspecialchars($p['name']); ?></h4>
              <span class="pf-badge <?php echo $p['is_active'] ? 'on' : 'off'; ?>">
                <?php echo $p['is_active'] ? _l('active') : _l('inactive'); ?>
              </span>
            </div>
            <div class="profile-meta">
              <?php if ($p['branch_id']): ?>
                <?php foreach ($branches as $b): if ($b['id'] == $p['branch_id']): ?>
                  <i class="fa fa-code-fork"></i> <?php echo htmlspecialchars($b['name']); ?> &nbsp;&middot;&nbsp;
                <?php endif; endforeach; ?>
              <?php else: ?>
                <i class="fa fa-globe"></i> All Branches &nbsp;&middot;&nbsp;
              <?php endif; ?>
              <i class="fa fa-money"></i> <?php echo htmlspecialchars($p['default_currency'] ?? 'KES'); ?>
              &nbsp;&middot;&nbsp;
              <i class="fa fa-users"></i> <?php echo count($p['assigned_users']); ?> user<?php echo count($p['assigned_users']) !== 1 ? 's' : ''; ?>
            </div>
            <div class="pf-toggles">
              <span class="pf-tog <?php echo $p['allow_rate_change']     ? 'on':'off'; ?>"><i class="fa fa-<?php echo $p['allow_rate_change']     ? 'check':'times'; ?>"></i> Rate Change</span>
              <span class="pf-tog <?php echo $p['allow_discount_change'] ? 'on':'off'; ?>"><i class="fa fa-<?php echo $p['allow_discount_change'] ? 'check':'times'; ?>"></i> Discount</span>
              <span class="pf-tog <?php echo $p['allow_partial_payment'] ? 'on':'off'; ?>"><i class="fa fa-<?php echo $p['allow_partial_payment'] ? 'check':'times'; ?>"></i> Partial Pay</span>
              <?php if (!empty($p['payment_method_ids'])): ?>
              <span class="pf-tog on"><i class="fa fa-credit-card"></i> <?php echo count($p['payment_method_ids']); ?> payment<?php echo count($p['payment_method_ids']) !== 1 ? 's':''; ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="panel-footer">
            <span class="user-stat">
              <?php if (!empty($p['assigned_users'])):
                $def = null;
                foreach ($p['assigned_users'] as $u) { if ($u['is_default']) { $def = $u; break; } }
              ?>
                <?php if ($def): ?>
                  <i class="fa fa-star" style="color:#f39c12"></i> <?php echo htmlspecialchars($def['firstname'] . ' ' . $def['lastname']); ?>
                <?php else: ?>
                  <i class="fa fa-users" style="color:#3498db"></i> <?php echo count($p['assigned_users']); ?> assigned
                <?php endif; ?>
              <?php else: ?>
                <i class="fa fa-user-plus" style="color:#ccc"></i> <span style="color:#aaa">No users</span>
              <?php endif; ?>
            </span>
            <div>
              <a href="<?php echo admin_url('pos_system/profile_delete/' . $p['id']); ?>"
                 class="btn btn-xs btn-danger"
                 onclick="return confirm('<?php echo _l('pos_deactivate_profile_confirm'); ?>')"
                 title="Deactivate">
                <i class="fa fa-trash"></i>
              </a>
              &nbsp;
              <button type="button" class="btn btn-sm btn-primary manage-profile-btn"
                      data-profile='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>'>
                <i class="fa fa-sliders"></i> Manage
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if (empty($profiles)): ?>
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body text-center" style="padding:50px 20px">
            <i class="fa fa-sliders fa-3x" style="color:#ccc;margin-bottom:14px;display:block"></i>
            <h4 style="color:#5a6480;margin-bottom:6px">No POS Profiles Yet</h4>
            <p class="text-muted" style="margin-bottom:20px">Profiles define terminal settings, assigned users, payment methods, and accounting per branch.</p>
            <button class="btn btn-primary" onclick="openProfileModal(null)">
              <i class="fa fa-plus"></i> <?php echo _l('pos_add_profile'); ?>
            </button>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- ─── Profile Manage Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="profileModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog" style="width:820px;max-width:95vw">
    <div class="modal-content">

      <div class="modal-header" style="background:#f8f9fc;border-bottom:1px solid #e5e9f2;padding:14px 20px">
        <button type="button" class="close" data-dismiss="modal" style="margin-top:-2px">&times;</button>
        <h4 class="modal-title" id="profileModalTitle" style="font-size:16px;font-weight:700"><?php echo _l('pos_add_profile'); ?></h4>
      </div>

      <div class="modal-body" style="padding:0">

        <ul class="nav nav-tabs" id="profileTabs">
          <li class="active"><a href="#ptab-general"   data-toggle="tab"><i class="fa fa-cog"></i> General</a></li>
          <li>              <a href="#ptab-users"      data-toggle="tab"><i class="fa fa-users"></i> Applicable for Users</a></li>
          <li>              <a href="#ptab-payments"   data-toggle="tab"><i class="fa fa-credit-card"></i> Payment Methods</a></li>
          <li>              <a href="#ptab-accounting" data-toggle="tab"><i class="fa fa-calculator"></i> Accounting</a></li>
        </ul>

        <form method="POST" action="<?php echo admin_url('pos_system/profile_save'); ?>" id="profileForm">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="id" id="pfId">

          <div class="tab-content">

            <!-- TAB: General ─────────────────────────────────────────────── -->
            <div class="tab-pane active" id="ptab-general">

              <div class="sec-ttl">Profile Info</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label><?php echo _l('pos_profile_name'); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="pfName" class="form-control" required placeholder="e.g. Retail Cashier">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label><?php echo _l('pos_branch'); ?></label>
                    <select name="branch_id" id="pfBranchId" class="form-control">
                      <option value=""><?php echo _l('pos_all_branches'); ?></option>
                      <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label><?php echo _l('status'); ?></label>
                    <div class="checkbox" style="margin-top:8px">
                      <label><input type="checkbox" name="is_active" id="pfIsActive" value="1" checked> <?php echo _l('active'); ?></label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="sec-ttl"><?php echo _l('pos_invoice_settings'); ?></div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label><?php echo _l('pos_action_on_new_invoice'); ?></label>
                    <select name="action_on_new_invoice" id="pfActionOnNewInvoice" class="form-control">
                      <option value="ask"><?php echo _l('pos_invoice_action_ask'); ?></option>
                      <option value="new"><?php echo _l('pos_invoice_action_new'); ?></option>
                      <option value="continue"><?php echo _l('pos_invoice_action_continue'); ?></option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label><?php echo _l('pos_invoice_prefix'); ?></label>
                    <input type="text" name="invoice_prefix" id="pfInvoicePrefix" class="form-control" value="POS-" placeholder="POS-">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label><?php echo _l('pos_print_template'); ?></label>
                    <select name="print_template_id" id="pfPrintTemplateId" class="form-control">
                      <option value="">— Default Thermal —</option>
                      <?php foreach ($print_templates as $pt): ?>
                        <option value="<?php echo $pt['id']; ?>"><?php echo htmlspecialchars($pt['name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="sec-ttl"><?php echo _l('pos_behaviour_settings'); ?></div>
              <div class="row">
                <?php
                $toggles = [
                  ['hide_images',                    'pos_hide_images'],
                  ['hide_unavailable_items',          'pos_hide_unavailable_items'],
                  ['auto_add_item_to_cart',           'pos_auto_add_item_to_cart'],
                  ['validate_stock_on_save',          'pos_validate_stock_on_save'],
                  ['print_receipt_on_order_complete', 'pos_print_receipt_on_order_complete'],
                  ['ignore_pricing_rule',             'pos_ignore_pricing_rule'],
                  ['allow_rate_change',               'pos_allow_rate_change'],
                  ['allow_discount_change',           'pos_allow_discount_change'],
                  ['set_grand_total_to_default_mop',  'pos_set_grand_total_to_default_mop'],
                  ['allow_partial_payment',           'pos_allow_partial_payment'],
                  ['auto_create_invoice',             'pos_auto_create_invoice'],
                ];
                foreach ($toggles as [$field, $lang_key]):
                ?>
                <div class="col-md-4">
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="<?php echo $field; ?>" id="pf_<?php echo $field; ?>" value="1"> <?php echo _l($lang_key); ?></label>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <div class="sec-ttl"><?php echo _l('pos_cash_handling'); ?></div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="checkbox" style="margin-top:6px">
                      <label><input type="checkbox" name="enable_cash_rounding" id="pfEnableCashRounding" value="1"> <?php echo _l('pos_enable_cash_rounding'); ?></label>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label><?php echo _l('pos_cash_rounding_increment'); ?></label>
                    <input type="number" step="0.01" name="cash_rounding_increment" id="pfCashRoundingIncrement" class="form-control" value="0.05">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label><?php echo _l('pos_cash_rounding_type'); ?></label>
                    <select name="cash_rounding_type" id="pfCashRoundingType" class="form-control">
                      <option value="nearest"><?php echo _l('pos_rounding_nearest'); ?></option>
                      <option value="up"><?php echo _l('pos_rounding_up'); ?></option>
                      <option value="down"><?php echo _l('pos_rounding_down'); ?></option>
                    </select>
                  </div>
                </div>
              </div>

            </div><!-- /ptab-general -->

            <!-- TAB: Applicable for Users ──────────────────────────────── -->
            <div class="tab-pane" id="ptab-users">
              <p class="text-muted" style="margin-bottom:16px">
                Staff who can use this profile at the POS terminal.
                The <i class="fa fa-star" style="color:#f39c12"></i> star marks the default user pre-selected when this profile loads.
              </p>

              <div id="usersNoProfileNotice" class="alert alert-info" style="display:none">
                <i class="fa fa-info-circle"></i> Save the profile first, then you can assign users.
              </div>
              <div id="usersMgmt">
                <div class="row" style="margin-bottom:15px">
                  <div class="col-md-6">
                    <select id="addUserSelect" class="form-control">
                      <option value="">— Select Staff Member —</option>
                      <?php foreach ($staff_list as $s): ?>
                        <option value="<?php echo $s['id']; ?>">
                          <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?> &lt;<?php echo htmlspecialchars($s['email']); ?>&gt;
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <div class="checkbox" style="margin:7px 0 0">
                      <label><input type="checkbox" id="addUserDefault"> Set as Default User</label>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-block" id="addUserBtn">
                      <i class="fa fa-plus"></i> Add
                    </button>
                  </div>
                </div>

                <table class="table table-bordered table-condensed" id="profileUsersTable">
                  <thead>
                    <tr>
                      <th style="width:42px;text-align:center">No.</th>
                      <th style="width:70px;text-align:center">Default</th>
                      <th>User</th>
                      <th style="width:70px;text-align:center">Remove</th>
                    </tr>
                  </thead>
                  <tbody id="profileUsersTbody">
                    <tr><td colspan="4" class="text-center text-muted" style="padding:20px">No users assigned</td></tr>
                  </tbody>
                </table>
              </div>
            </div><!-- /ptab-users -->

            <!-- TAB: Payment Methods ─────────────────────────────────────── -->
            <div class="tab-pane" id="ptab-payments">
              <p class="text-muted" style="margin-bottom:16px">
                Select which payment methods are available when this profile is active at the terminal.
                <a href="<?php echo admin_url('pos_system/payment_methods'); ?>" target="_blank">Manage payment methods &rarr;</a>
              </p>
              <?php if (empty($payment_methods)): ?>
              <div class="alert alert-warning">
                <i class="fa fa-warning"></i> No payment methods configured.
                <a href="<?php echo admin_url('pos_system/payment_methods'); ?>">Set up payment methods first &rarr;</a>
              </div>
              <?php else: ?>
              <div class="row">
                <?php foreach ($payment_methods as $pm): ?>
                <div class="col-md-4" style="margin-bottom:8px">
                  <div class="checkbox">
                    <label style="font-weight:normal">
                      <input type="checkbox" name="payment_method_ids[]" value="<?php echo $pm['id']; ?>" class="pm-checkbox">
                      <strong><?php echo htmlspecialchars($pm['name']); ?></strong>
                      <br><small class="text-muted" style="padding-left:20px"><?php echo ucfirst($pm['type'] ?? 'cash'); ?></small>
                    </label>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div><!-- /ptab-payments -->

            <!-- TAB: Accounting ─────────────────────────────────────────── -->
            <div class="tab-pane" id="ptab-accounting">
              <p class="text-muted" style="margin-bottom:16px">
                Link POS transactions from this profile to your chart of accounts.
              </p>

              <div class="sec-ttl">Pricing &amp; Currency</div>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label><?php echo _l('pos_price_list'); ?></label>
                    <select name="price_list_id" id="pfPriceListId" class="form-control">
                      <option value="">— Standard (No override) —</option>
                      <?php foreach ($price_lists as $pl): ?>
                        <option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label><?php echo _l('pos_currency'); ?></label>
                    <select name="default_currency" id="pfCurrency" class="form-control">
                      <option value="KES">KES — Kenyan Shilling</option>
                      <option value="UGX">UGX — Ugandan Shilling</option>
                      <option value="TZS">TZS — Tanzanian Shilling</option>
                      <option value="RWF">RWF — Rwandan Franc</option>
                      <option value="ETB">ETB — Ethiopian Birr</option>
                      <option value="USD">USD — US Dollar</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label><?php echo _l('pos_apply_discount_on'); ?></label>
                    <select name="apply_discount_on" id="pfApplyDiscountOn" class="form-control">
                      <option value="grand_total"><?php echo _l('pos_grand_total'); ?></option>
                      <option value="net_total"><?php echo _l('pos_net_total'); ?></option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="sec-ttl">Write Off</div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Write Off Account</label>
                    <input type="text" name="write_off_account" id="pfWriteOffAccount" class="form-control" placeholder="e.g. Write-Off Expenses">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Write Off Cost Center</label>
                    <input type="text" name="write_off_cost_center" id="pfWriteOffCostCenter" class="form-control" placeholder="e.g. Main Cost Center">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Write Off Limit</label>
                    <input type="number" step="0.01" name="write_off_limit" id="pfWriteOffLimit" class="form-control" value="0" placeholder="0.00">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Account for Change Amount</label>
                    <input type="text" name="account_for_change" id="pfAccountForChange" class="form-control" placeholder="e.g. Cash Rounding Suspense">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group" style="padding-top:24px">
                    <div class="checkbox">
                      <label><input type="checkbox" name="disable_rounded_total" id="pfDisableRoundedTotal" value="1"> <?php echo _l('pos_disable_rounded_total'); ?></label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="sec-ttl">Income &amp; Expense Accounts</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Income Account</label>
                    <input type="text" name="income_account" id="pfIncomeAccount" class="form-control" placeholder="e.g. Sales Revenue">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Expense Account</label>
                    <input type="text" name="expense_account" id="pfExpenseAccount" class="form-control" placeholder="e.g. Cost of Goods Sold">
                  </div>
                </div>
              </div>

              <div class="sec-ttl">Taxes</div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Taxes and Charges</label>
                    <input type="text" name="taxes_and_charges" id="pfTaxesAndCharges" class="form-control" placeholder="e.g. VAT 16%">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Tax Category</label>
                    <input type="text" name="tax_category" id="pfTaxCategory" class="form-control" placeholder="e.g. Standard">
                  </div>
                </div>
              </div>

            </div><!-- /ptab-accounting -->

          </div><!-- /tab-content -->
        </form>

      </div><!-- /modal-body -->

      <div class="modal-footer" style="background:#f8f9fc;border-top:1px solid #e5e9f2">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="button" class="btn btn-primary" id="saveProfileBtn">
          <i class="fa fa-save"></i> <?php echo _l('submit'); ?>
        </button>
      </div>

    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var CSRF_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';
  var BASE_URL  = '<?php echo rtrim(admin_url('pos_system'), '/'); ?>';
  var currentProfileId = null;

  /* ── Open / populate modal ─────────────────────────────────────────────── */
  function openProfileModal(p) {
    resetForm();
    jQuery('#profileTabs a:first').tab('show');

    if (p && p.id) {
      currentProfileId = p.id;
      document.getElementById('profileModalTitle').textContent = 'Manage: ' + p.name;
      document.getElementById('pfId').value = p.id;

      // General tab
      document.getElementById('pfName').value                  = p.name || '';
      document.getElementById('pfBranchId').value              = p.branch_id || '';
      document.getElementById('pfIsActive').checked            = p.is_active == 1;
      document.getElementById('pfActionOnNewInvoice').value    = p.action_on_new_invoice || 'ask';
      document.getElementById('pfInvoicePrefix').value         = p.invoice_prefix || 'POS-';
      document.getElementById('pfPrintTemplateId').value       = p.print_template_id || '';
      document.getElementById('pfEnableCashRounding').checked  = p.enable_cash_rounding == 1;
      document.getElementById('pfCashRoundingIncrement').value = p.cash_rounding_increment || '0.05';
      document.getElementById('pfCashRoundingType').value      = p.cash_rounding_type || 'nearest';

      ['hide_images','hide_unavailable_items','auto_add_item_to_cart',
       'validate_stock_on_save','print_receipt_on_order_complete','ignore_pricing_rule',
       'allow_rate_change','allow_discount_change','set_grand_total_to_default_mop',
       'allow_partial_payment','auto_create_invoice'].forEach(function(f) {
        var el = document.getElementById('pf_' + f);
        if (el) el.checked = p[f] == 1;
      });

      // Accounting tab
      document.getElementById('pfPriceListId').value        = p.price_list_id        || '';
      document.getElementById('pfCurrency').value           = p.default_currency      || 'KES';
      document.getElementById('pfApplyDiscountOn').value    = p.apply_discount_on     || 'grand_total';
      document.getElementById('pfWriteOffAccount').value    = p.write_off_account     || '';
      document.getElementById('pfWriteOffCostCenter').value = p.write_off_cost_center || '';
      document.getElementById('pfWriteOffLimit').value      = p.write_off_limit       || '0';
      document.getElementById('pfAccountForChange').value   = p.account_for_change    || '';
      document.getElementById('pfDisableRoundedTotal').checked = p.disable_rounded_total == 1;
      document.getElementById('pfIncomeAccount').value      = p.income_account        || '';
      document.getElementById('pfExpenseAccount').value     = p.expense_account       || '';
      document.getElementById('pfTaxesAndCharges').value    = p.taxes_and_charges     || '';
      document.getElementById('pfTaxCategory').value        = p.tax_category          || '';

      // Payment methods checkboxes
      var pmIds = (p.payment_method_ids || []).map(Number);
      document.querySelectorAll('.pm-checkbox').forEach(function(cb) {
        cb.checked = pmIds.indexOf(parseInt(cb.value, 10)) !== -1;
      });

      // Users tab — show management, load assigned users
      document.getElementById('usersNoProfileNotice').style.display = 'none';
      document.getElementById('usersMgmt').style.display = '';
      renderUsersTable(p.assigned_users || []);

    } else {
      currentProfileId = null;
      document.getElementById('profileModalTitle').textContent = '<?php echo _l('pos_add_profile'); ?>';
      document.getElementById('usersNoProfileNotice').style.display = '';
      document.getElementById('usersMgmt').style.display = 'none';
    }

    jQuery('#profileModal').modal('show');
  }

  window.openProfileModal = openProfileModal;

  function resetForm() {
    document.getElementById('profileForm').reset();
    document.getElementById('pfId').value = '';
    document.getElementById('pfIsActive').checked = true;
    document.getElementById('pfCurrency').value = 'KES';
    document.getElementById('pfApplyDiscountOn').value = 'grand_total';
    renderUsersTable([]);
  }

  /* ── Save profile ──────────────────────────────────────────────────────── */
  document.getElementById('saveProfileBtn').addEventListener('click', function() {
    document.getElementById('profileForm').submit();
  });

  /* ── Card manage buttons ───────────────────────────────────────────────── */
  document.querySelectorAll('.manage-profile-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      openProfileModal(JSON.parse(this.dataset.profile));
    });
  });

  /* ── Users tab ─────────────────────────────────────────────────────────── */
  function renderUsersTable(users) {
    var tbody = document.getElementById('profileUsersTbody');
    if (!users || users.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding:20px">No users assigned to this profile</td></tr>';
      return;
    }
    var rows = users.map(function(u, i) {
      var name  = ((u.firstname || '') + ' ' + (u.lastname || '')).trim();
      var isdef = parseInt(u.is_default, 10) === 1;
      return '<tr>' +
        '<td style="text-align:center">' + (i + 1) + '</td>' +
        '<td style="text-align:center">' +
          '<button type="button" class="btn-star ' + (isdef ? 'on':'off') + '" ' +
            'title="' + (isdef ? 'Default user':'Set as default') + '" ' +
            'onclick="setDefaultUser(' + parseInt(u.id, 10) + ')">' +
            '<i class="fa fa-star"></i>' +
          '</button>' +
        '</td>' +
        '<td><strong>' + esc(name) + '</strong><br>' +
             '<small class="text-muted">' + esc(u.email || '') + '</small></td>' +
        '<td style="text-align:center">' +
          '<button type="button" class="btn btn-xs btn-danger" ' +
            'onclick="removeUser(' + parseInt(u.id, 10) + ')" title="Remove">' +
            '<i class="fa fa-times"></i></button>' +
        '</td>' +
        '</tr>';
    });
    tbody.innerHTML = rows.join('');
  }

  document.getElementById('addUserBtn').addEventListener('click', function() {
    if (!currentProfileId) { alert('Save the profile first, then assign users.'); return; }
    var sel     = document.getElementById('addUserSelect');
    var staffId = parseInt(sel.value, 10);
    if (!staffId) { alert('Please select a staff member.'); return; }
    var isDef   = document.getElementById('addUserDefault').checked;

    post(BASE_URL + '/profile_user_assign', {
      profile_id: currentProfileId, staff_id: staffId, is_default: isDef ? 1 : 0
    }, function(res) {
      if (res && res.success) {
        renderUsersTable(res.users);
        sel.value = '';
        document.getElementById('addUserDefault').checked = false;
      } else {
        alert((res && res.error) || 'Could not assign user.');
      }
    });
  });

  window.removeUser = function(staffId) {
    if (!currentProfileId || !confirm('Remove this user from the profile?')) return;
    post(BASE_URL + '/profile_user_remove', {
      profile_id: currentProfileId, staff_id: staffId
    }, function(res) { if (res && res.success) renderUsersTable(res.users); });
  };

  window.setDefaultUser = function(staffId) {
    if (!currentProfileId) return;
    post(BASE_URL + '/profile_user_set_default', {
      profile_id: currentProfileId, staff_id: staffId
    }, function(res) { if (res && res.success) renderUsersTable(res.users); });
  };

  /* ── Helpers ───────────────────────────────────────────────────────────── */
  function post(url, data, cb) {
    data[CSRF_NAME] = CSRF_HASH;
    jQuery.post(url, data, cb, 'json').fail(function(xhr) {
      console.error(xhr.responseText);
      alert('Request failed. Check console for details.');
    });
  }

  function esc(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

})();
</script>

<?php init_tail(); ?>
