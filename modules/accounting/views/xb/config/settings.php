<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<form method="post" action="<?php echo admin_url('xetuu_books/config/settings'); ?>" class="xb-form">
  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

  <div class="xb-settings-section">
    <h4 class="xb-section-title">Company</h4>
    <div class="xb-form-row">
      <label>Company Name</label>
      <input type="text" name="company_name" class="xb-input" value="<?php echo htmlspecialchars($settings['company_name'] ?? get_option('companyname')); ?>">
    </div>
    <div class="xb-form-row">
      <label>VAT / Tax Number</label>
      <input type="text" name="company_vat" class="xb-input" value="<?php echo htmlspecialchars($settings['company_vat'] ?? ''); ?>">
    </div>
    <div class="xb-form-row">
      <label>Company Address</label>
      <textarea name="company_address" class="xb-input" rows="3"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
    </div>
  </div>

  <div class="xb-settings-section">
    <h4 class="xb-section-title">Default Accounts</h4>
    <?php
    $acct_fields = [
        'default_ar_account'      => 'Accounts Receivable',
        'default_ap_account'      => 'Accounts Payable',
        'default_income_account'  => 'Default Income Account',
        'default_cogs_account'    => 'Cost of Revenue',
        'default_bank_account'    => 'Default Bank Account',
        'default_cash_account'    => 'Default Cash Account',
        'default_tax_account'     => 'Output VAT',
        'default_input_tax'       => 'Input VAT',
        'suspense_account'        => 'Suspense / Bank Difference',
    ];
    foreach ($acct_fields as $key => $label): ?>
    <div class="xb-form-row">
      <label><?php echo $label; ?></label>
      <select name="<?php echo $key; ?>" class="xb-select">
        <option value="">— None —</option>
        <?php foreach ($gl_accounts ?? [] as $a): ?>
        <option value="<?php echo $a->id; ?>" <?php echo ($settings[$key] ?? '') == $a->id ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($a->code . ' ' . $a->name); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="xb-settings-section">
    <h4 class="xb-section-title">Currency &amp; Fiscal Year</h4>
    <div class="xb-form-row">
      <label>Base Currency</label>
      <select name="base_currency_id" class="xb-select">
        <?php foreach ($currencies ?? [] as $c): ?>
        <option value="<?php echo $c->id; ?>" <?php echo ($settings['base_currency_id'] ?? '') == $c->id ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($c->symbol . ' ' . $c->name); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="xb-form-row">
      <label>Fiscal Year Start (MM-DD)</label>
      <input type="text" name="fiscal_year_start" class="xb-input" placeholder="01-01" value="<?php echo htmlspecialchars($settings['fiscal_year_start'] ?? '01-01'); ?>" maxlength="5">
    </div>
  </div>

  <div class="xb-settings-section">
    <h4 class="xb-section-title">Lock Dates</h4>
    <div class="xb-form-row">
      <label>Period Lock Date <small class="xb-muted">(advisors can still post)</small></label>
      <input type="date" name="period_lock_date" class="xb-input" value="<?php echo htmlspecialchars($lock_dates->period_lock_date ?? ''); ?>">
    </div>
    <div class="xb-form-row">
      <label>Hard Lock Date <small class="xb-muted">(no one can post before this date)</small></label>
      <input type="date" name="hard_lock_date" class="xb-input" value="<?php echo htmlspecialchars($lock_dates->hard_lock_date ?? ''); ?>">
    </div>
  </div>

  <div class="xb-settings-section">
    <h4 class="xb-section-title">Invoice Options</h4>
    <div class="xb-form-row">
      <label>Invoice Footer Text</label>
      <textarea name="invoice_footer_text" class="xb-input" rows="3"><?php echo htmlspecialchars($settings['invoice_footer_text'] ?? ''); ?></textarea>
    </div>
    <div class="xb-form-row">
      <label>Default Payment Terms</label>
      <select name="default_payment_term_id" class="xb-select">
        <option value="">— None —</option>
        <?php foreach ($payment_terms ?? [] as $pt): ?>
        <option value="<?php echo $pt->id; ?>" <?php echo ($settings['default_payment_term_id'] ?? '') == $pt->id ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($pt->name); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="xb-form-footer">
    <button type="submit" class="xb-btn xb-btn-primary">Save Settings</button>
  </div>
</form>
