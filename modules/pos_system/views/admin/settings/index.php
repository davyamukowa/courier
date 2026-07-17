<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.sett-wrap    { display:flex; gap:0; background:#fff; border:1px solid #e4e9f0; border-radius:12px; overflow:hidden; min-height:600px; }
.sett-sidebar { width:230px; flex-shrink:0; background:#f5f7fa; border-right:1px solid #e4e9f0; padding:10px 0; }
.sett-sidebar .sett-group-lbl { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.1em; padding:14px 18px 5px; }
.sett-sidebar a { display:flex; align-items:center; gap:9px; padding:9px 18px; font-size:13px; color:#4a5568; font-weight:500; text-decoration:none; border-left:3px solid transparent; transition:all .15s; cursor:pointer; }
.sett-sidebar a:hover { background:#edf2f7; color:#1a202c; }
.sett-sidebar a.active { background:#dcfce7; color:#14532d; font-weight:700; border-left-color:#16a34a; }
.sett-sidebar a i { width:16px; text-align:center; font-size:12px; color:#6b9e7a; }
.sett-sidebar a.active i { color:#16a34a; }
.sett-panels { flex:1; overflow:hidden; }
.sett-panel  { display:none; padding:28px 32px; }
.sett-panel.active { display:block; }
.sett-panel h5 { font-size:17px; font-weight:700; color:#1a202c; margin:0 0 4px; }
.sett-panel .sett-panel-sub { font-size:12px; color:#94a3b8; margin:0 0 24px; }
.sett-section { margin-bottom:28px; padding-bottom:24px; border-bottom:1px solid #f1f5f9; }
.sett-section:last-child { border-bottom:none; margin-bottom:0; }
.sett-section-title { font-size:13px; font-weight:700; color:#374151; margin-bottom:14px; display:flex; align-items:center; gap:7px; }
.sett-section-title i { color:#16a34a; }
.sett-row { display:flex; align-items:flex-start; gap:24px; margin-bottom:14px; flex-wrap:wrap; }
.sett-field { flex:1; min-width:200px; }
.sett-field label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; }
.sett-field small { display:block; font-size:11px; color:#94a3b8; margin-top:3px; }
.sett-field input[type=text], .sett-field input[type=number], .sett-field input[type=password], .sett-field select, .sett-field textarea { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:7px 10px; font-size:13px; color:#374151; background:#fff; }
.sett-field input:focus, .sett-field select:focus, .sett-field textarea:focus { outline:none; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.08); }
.toggle-wrap { display:flex; align-items:center; gap:10px; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; }
/* Only the text label stretches — NOT the .sett-toggle label */
.toggle-wrap label:not(.sett-toggle) { margin:0; font-size:13px; font-weight:600; color:#374151; cursor:pointer; flex:1; }
.toggle-wrap small { font-size:11px; color:#94a3b8; display:block; font-weight:400; }
/* Toggle switch — fixed size, never flex-grow */
.sett-toggle { position:relative; width:40px !important; height:22px !important; flex:0 0 40px !important; display:block; cursor:pointer; }
.sett-toggle input { position:absolute; opacity:0; width:0; height:0; margin:0; }
.sett-toggle-slider { position:absolute; top:0; left:0; right:0; bottom:0; background:#d1d5db; border-radius:22px; transition:.2s; cursor:pointer; }
.sett-toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.sett-toggle input:checked + .sett-toggle-slider { background:#16a34a; }
.sett-toggle input:checked + .sett-toggle-slider:before { transform:translateX(18px); }
.sett-save-bar { padding:18px 32px; border-top:1px solid #e4e9f0; background:#fafbfd; display:flex; align-items:center; gap:12px; }
.sett-tag { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.sett-tag.on  { background:#dcfce7; color:#14532d; }
.sett-tag.off { background:#f1f5f9; color:#64748b; }
</style>

<div id="wrapper">
<div class="content">

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px">
  <h3 style="margin:0; font-size:20px; font-weight:700; color:#1a2332">
    <i class="fa fa-cog" style="color:#16a34a; margin-right:8px"></i> POS Settings
  </h3>
</div>

<form method="post" action="<?php echo admin_url('pos_system/settings'); ?>" id="settingsForm">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
<div class="sett-wrap">

  <!-- ── Sidebar nav ── -->
  <div class="sett-sidebar">
    <div class="sett-group-lbl">General</div>
    <a href="#" data-tab="general" class="active"><i class="fa fa-sliders-h"></i> General</a>
    <a href="#" data-tab="payments"><i class="fa fa-credit-card"></i> Payment Methods</a>
    <a href="#" data-tab="mobile_money"><i class="fa fa-mobile-alt"></i> Mobile Money</a>

    <div class="sett-group-lbl">Operations</div>
    <a href="#" data-tab="receipt"><i class="fa fa-receipt"></i> Receipt &amp; Invoice</a>
    <a href="#" data-tab="sessions"><i class="fa fa-clock"></i> Sessions</a>

    <div class="sett-group-lbl">Integrations</div>
    <a href="#" data-tab="warehouse"><i class="fa fa-warehouse"></i> Warehouse</a>
    <a href="#" data-tab="accounting"><i class="fa fa-book"></i> Accounting</a>

    <div class="sett-group-lbl">Terminal</div>
    <a href="#" data-tab="profiles"><i class="fa fa-id-card"></i> Profiles</a>

    <div class="sett-group-lbl">Add-ons</div>
    <a href="#" data-tab="restaurant" id="restaurant-tab-link">
      <i class="fa fa-utensils"></i> Restaurant POS
      <?php
      $rest_on = ($settings['pos_restaurant_mode'] ?? '0') == '1';
      echo $rest_on ? '<span class="sett-tag on" style="margin-left:auto">ON</span>' : '<span class="sett-tag off" style="margin-left:auto">OFF</span>';
      ?>
    </a>
  </div>

  <!-- ── Tab panels ── -->
  <div class="sett-panels">

    <!-- GENERAL -->
    <div class="sett-panel active" id="tab-general">
      <h5><i class="fa fa-sliders-h" style="color:#16a34a;margin-right:6px"></i> General Settings</h5>
      <p class="sett-panel-sub">Core POS behaviour — currency, stock rules, and low-stock alerts.</p>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-dollar-sign"></i> Currency &amp; Tax</div>
        <div class="sett-row">
          <div class="sett-field" style="max-width:220px">
            <label>Default Currency</label>
            <select name="pos_currency">
              <?php
              $currencies = ['KES'=>'KES — Kenyan Shilling','UGX'=>'UGX — Ugandan Shilling','TZS'=>'TZS — Tanzanian Shilling','RWF'=>'RWF — Rwandan Franc','ETB'=>'ETB — Ethiopian Birr','USD'=>'USD — US Dollar'];
              $cur = $settings['pos_currency'] ?? 'KES';
              foreach ($currencies as $code => $label): ?>
                <option value="<?php echo $code; ?>" <?php echo $cur === $code ? 'selected' : ''; ?>><?php echo $label; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sett-field">
            <label>Tax Inclusive Pricing</label>
            <div style="display:flex;gap:16px;margin-top:4px">
              <label style="font-weight:400;display:flex;align-items:center;gap:6px">
                <input type="radio" name="pos_tax_inclusive" value="1" <?php echo ($settings['pos_tax_inclusive'] ?? '1') == '1' ? 'checked' : ''; ?>> Yes — prices include tax
              </label>
              <label style="font-weight:400;display:flex;align-items:center;gap:6px">
                <input type="radio" name="pos_tax_inclusive" value="0" <?php echo ($settings['pos_tax_inclusive'] ?? '1') == '0' ? 'checked' : ''; ?>> No — add tax on top
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-boxes"></i> Stock &amp; Inventory</div>
        <div class="sett-row">
          <div class="sett-field" style="max-width:160px">
            <label>Low Stock Threshold</label>
            <input type="number" name="pos_low_stock_threshold" value="<?php echo htmlspecialchars($settings['pos_low_stock_threshold'] ?? '5'); ?>" min="0">
            <small>Units before low-stock alert triggers</small>
          </div>
        </div>
        <div class="toggle-wrap" style="max-width:480px">
          <label>
            Allow Negative Stock
            <small>Let sales proceed even when qty = 0</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_allow_negative_stock" value="1" <?php echo ($settings['pos_allow_negative_stock'] ?? '0') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- PAYMENT METHODS -->
    <div class="sett-panel" id="tab-payments">
      <h5><i class="fa fa-credit-card" style="color:#16a34a;margin-right:6px"></i> Payment Methods</h5>
      <p class="sett-panel-sub">Enable or disable payment methods available to cashiers at the terminal.</p>
      <?php
      $pm_config = [
        ['key'=>'pos_enable_cash',     'label'=>'Cash',            'desc'=>'Physical notes and coins',           'default'=>'1'],
        ['key'=>'pos_enable_card',     'label'=>'Card / POS Machine','desc'=>'Credit & debit card swipe',        'default'=>'1'],
        ['key'=>'pos_enable_mpesa',    'label'=>'M-Pesa',          'desc'=>'Safaricom STK push (Kenya)',          'default'=>'1'],
        ['key'=>'pos_enable_airtel',   'label'=>'Airtel Money',    'desc'=>'Airtel mobile money',                 'default'=>'0'],
        ['key'=>'pos_enable_mtn',      'label'=>'MTN MoMo',        'desc'=>'MTN Mobile Money (Uganda/Rwanda)',    'default'=>'0'],
        ['key'=>'pos_enable_telebirr', 'label'=>'Telebirr',        'desc'=>'Ethio Telecom mobile money',          'default'=>'0'],
        ['key'=>'pos_enable_bank',     'label'=>'Bank Transfer',   'desc'=>'Direct bank / EFT transfer',          'default'=>'0'],
        ['key'=>'pos_enable_credit',   'label'=>'Credit / Debt',   'desc'=>'Allow customers to buy on credit',    'default'=>'0'],
      ];
      foreach ($pm_config as $pm): ?>
      <div class="toggle-wrap" style="margin-bottom:8px">
        <label>
          <?php echo $pm['label']; ?>
          <small><?php echo $pm['desc']; ?></small>
        </label>
        <label class="sett-toggle">
          <input type="checkbox" name="<?php echo $pm['key']; ?>" value="1" <?php echo ($settings[$pm['key']] ?? $pm['default']) == '1' ? 'checked' : ''; ?>>
          <span class="sett-toggle-slider"></span>
        </label>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- MOBILE MONEY -->
    <div class="sett-panel" id="tab-mobile_money">
      <h5><i class="fa fa-mobile-alt" style="color:#16a34a;margin-right:6px"></i> Mobile Money Configuration</h5>
      <p class="sett-panel-sub">API credentials for mobile payment gateways. Keep these secure.</p>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-sim-card"></i> M-Pesa (Safaricom — Kenya)</div>
        <div class="sett-row">
          <div class="sett-field">
            <label>Consumer Key</label>
            <input type="text" name="mpesa_consumer_key" value="<?php echo htmlspecialchars($settings['mpesa_consumer_key'] ?? ''); ?>" placeholder="From Safaricom developer portal">
          </div>
          <div class="sett-field">
            <label>Consumer Secret</label>
            <input type="password" name="mpesa_consumer_secret" value="<?php echo htmlspecialchars($settings['mpesa_consumer_secret'] ?? ''); ?>">
          </div>
        </div>
        <div class="sett-row">
          <div class="sett-field">
            <label>Paybill / Till Number</label>
            <input type="text" name="mpesa_shortcode" value="<?php echo htmlspecialchars($settings['mpesa_shortcode'] ?? ''); ?>" placeholder="174379">
          </div>
          <div class="sett-field">
            <label>Passkey</label>
            <input type="password" name="mpesa_passkey" value="<?php echo htmlspecialchars($settings['mpesa_passkey'] ?? ''); ?>">
          </div>
        </div>
        <div class="sett-field" style="max-width:180px">
          <label>Environment</label>
          <select name="mpesa_env">
            <option value="sandbox" <?php echo ($settings['mpesa_env']??'sandbox')==='sandbox'?'selected':''; ?>>Sandbox (Testing)</option>
            <option value="live"    <?php echo ($settings['mpesa_env']??'sandbox')==='live'   ?'selected':''; ?>>Live (Production)</option>
          </select>
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-sim-card"></i> MTN Mobile Money (Uganda / Rwanda)</div>
        <div class="sett-row">
          <div class="sett-field">
            <label>API User</label>
            <input type="text" name="mtn_api_user" value="<?php echo htmlspecialchars($settings['mtn_api_user'] ?? ''); ?>">
          </div>
          <div class="sett-field">
            <label>API Key</label>
            <input type="password" name="mtn_api_key" value="<?php echo htmlspecialchars($settings['mtn_api_key'] ?? ''); ?>">
          </div>
          <div class="sett-field">
            <label>Subscription Key</label>
            <input type="text" name="mtn_subscription_key" value="<?php echo htmlspecialchars($settings['mtn_subscription_key'] ?? ''); ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- RECEIPT -->
    <div class="sett-panel" id="tab-receipt">
      <h5><i class="fa fa-receipt" style="color:#16a34a;margin-right:6px"></i> Receipt &amp; Invoice</h5>
      <p class="sett-panel-sub">Customise what appears on printed receipts and PDF invoices.</p>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-align-left"></i> Header &amp; Footer Text</div>
        <div class="sett-row">
          <div class="sett-field">
            <label>Receipt Header</label>
            <textarea name="pos_receipt_header" rows="3" placeholder="Business name, address, tax PIN…"><?php echo htmlspecialchars($settings['pos_receipt_header'] ?? ''); ?></textarea>
          </div>
          <div class="sett-field">
            <label>Receipt Footer</label>
            <textarea name="pos_receipt_footer" rows="3" placeholder="Thank you message, return policy…"><?php echo htmlspecialchars($settings['pos_receipt_footer'] ?? 'Thank you for your purchase!'); ?></textarea>
          </div>
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-print"></i> Print Options</div>
        <div class="toggle-wrap" style="max-width:480px">
          <label>
            Auto-Print Receipt after Sale
            <small>Immediately opens print dialog when a sale is completed</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_auto_print_receipt" value="1" <?php echo ($settings['pos_auto_print_receipt'] ?? '0') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- SESSIONS -->
    <div class="sett-panel" id="tab-sessions">
      <h5><i class="fa fa-clock" style="color:#16a34a;margin-right:6px"></i> Session Settings</h5>
      <p class="sett-panel-sub">Control how cashier sessions (shift opens/closes) are managed.</p>
      <div class="sett-section">
        <div class="toggle-wrap" style="max-width:480px;margin-bottom:12px">
          <label>
            Require Session Before Selling
            <small>Cashier must open a session with an opening float before transacting</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_require_session" value="1" <?php echo ($settings['pos_require_session'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
        <div class="sett-field" style="max-width:180px">
          <label>Session Auto-Close After (Hours)</label>
          <input type="number" name="pos_session_timeout_hours" value="<?php echo htmlspecialchars($settings['pos_session_timeout_hours'] ?? '12'); ?>" min="1" max="72">
          <small>0 = never auto-close</small>
        </div>
      </div>
    </div>

    <!-- WAREHOUSE -->
    <div class="sett-panel" id="tab-warehouse">
      <h5><i class="fa fa-warehouse" style="color:#16a34a;margin-right:6px"></i> Warehouse Integration</h5>
      <p class="sett-panel-sub">Sync POS stock movements with the Warehouse module.</p>
      <div class="sett-section">
        <div class="toggle-wrap" style="max-width:480px;margin-bottom:16px">
          <label>
            Enable Warehouse Sync
            <small>POS sales and receipts will update warehouse stock quantities</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_warehouse_sync" value="1" <?php echo ($settings['pos_warehouse_sync'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
        <?php if (!empty($branches)): ?>
        <div class="sett-section-title"><i class="fa fa-code-branch"></i> Branch → Warehouse Mapping</div>
        <?php foreach ($branches as $branch): ?>
        <div class="sett-field" style="max-width:320px;margin-bottom:10px">
          <label><?php echo htmlspecialchars($branch['name']); ?> — Warehouse ID</label>
          <input type="number" name="pos_branch_warehouse_<?php echo $branch['id']; ?>"
                 value="<?php echo htmlspecialchars($settings['pos_branch_warehouse_' . $branch['id']] ?? ''); ?>"
                 placeholder="Leave blank to use all warehouses" min="1">
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ACCOUNTING -->
    <div class="sett-panel" id="tab-accounting">
      <h5><i class="fa fa-book" style="color:#16a34a;margin-right:6px"></i> Accounting Integration</h5>
      <p class="sett-panel-sub">Post POS transactions to the Accounting module general ledger.</p>
      <div class="sett-section">
        <div class="toggle-wrap" style="max-width:480px;margin-bottom:16px">
          <label>
            Enable Accounting Sync
            <small>Sales, payments and returns are journaled automatically</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_accounting_sync" value="1" <?php echo ($settings['pos_accounting_sync'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
        <div class="sett-row">
          <div class="sett-field">
            <label>Revenue Account Key</label>
            <input type="text" name="pos_revenue_account_key" value="<?php echo htmlspecialchars($settings['pos_revenue_account_key'] ?? 'acc_sales_of_product_income'); ?>" placeholder="acc_sales_of_product_income">
            <small>The GL account code that receives sales revenue</small>
          </div>
          <div class="sett-field">
            <label>Cash Account Key</label>
            <input type="text" name="pos_cash_account_key" value="<?php echo htmlspecialchars($settings['pos_cash_account_key'] ?? 'acc_petty_cash'); ?>" placeholder="acc_petty_cash">
            <small>The GL account debited for cash receipts</small>
          </div>
        </div>
      </div>
    </div>

    <!-- RESTAURANT -->
    <div class="sett-panel" id="tab-restaurant">
      <h5><i class="fa fa-utensils" style="color:#16a34a;margin-right:6px"></i> Restaurant POS Mode</h5>
      <p class="sett-panel-sub">Extends the terminal with table management, Kitchen Order Tickets (KOT), and recipe-based inventory deduction.</p>

      <div class="sett-section">
        <div class="toggle-wrap" style="max-width:560px;background:#f0fdf4;border-color:#bbf7d0">
          <label>
            Enable Restaurant Mode
            <small>Adds table selection, production areas, KOT workflow, and recipe management to the POS</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_restaurant_mode" id="restaurantModeToggle" value="1" <?php echo ($settings['pos_restaurant_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-chair"></i> Table Management</div>
        <div class="sett-row">
          <div class="sett-field">
            <label>Default Seats per Table</label>
            <input type="number" name="pos_restaurant_default_seats" value="<?php echo htmlspecialchars($settings['pos_restaurant_default_seats'] ?? '4'); ?>" min="1" max="50" style="max-width:80px">
          </div>
        </div>
        <div class="toggle-wrap" style="max-width:480px;margin-bottom:8px">
          <label>
            Require Table Selection
            <small>Cashier must select a table before sending a KOT</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_restaurant_require_table" value="1" <?php echo ($settings['pos_restaurant_require_table'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-fire"></i> Kitchen &amp; Production</div>
        <div class="toggle-wrap" style="max-width:480px;margin-bottom:8px">
          <label>
            Auto-Deduct Stock on KOT Ready
            <small>When chef marks an order ready, recipe ingredients are deducted from inventory</small>
          </label>
          <label class="sett-toggle">
            <input type="checkbox" name="pos_restaurant_auto_deduct" value="1" <?php echo ($settings['pos_restaurant_auto_deduct'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="sett-toggle-slider"></span>
          </label>
        </div>
        <div class="sett-field" style="max-width:200px;margin-top:12px">
          <label>Kitchen Display Refresh (seconds)</label>
          <input type="number" name="pos_restaurant_refresh_secs" value="<?php echo htmlspecialchars($settings['pos_restaurant_refresh_secs'] ?? '15'); ?>" min="5" max="120">
        </div>
      </div>

      <div class="sett-section">
        <div class="sett-section-title"><i class="fa fa-link"></i> Quick Links</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a href="<?php echo admin_url('pos_system/restaurant/areas'); ?>" class="btn btn-default btn-sm"><i class="fa fa-fire-alt"></i> Manage Production Areas</a>
          <a href="<?php echo admin_url('pos_system/restaurant/tables'); ?>" class="btn btn-default btn-sm"><i class="fa fa-chair"></i> Manage Tables</a>
          <a href="<?php echo admin_url('pos_system/restaurant/recipes'); ?>" class="btn btn-default btn-sm"><i class="fa fa-book-open"></i> Manage Recipes</a>
          <a href="<?php echo admin_url('pos_system/restaurant/kitchen'); ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-tv"></i> Open Kitchen Display</a>
        </div>
      </div>
    </div>

    <!-- PROFILES -->
    <div class="sett-panel" id="tab-profiles">
      <h5><i class="fa fa-id-card" style="color:#16a34a;margin-right:6px"></i> POS Profiles</h5>
      <p class="sett-panel-sub">Profiles define terminal behaviour, assigned staff, and payment methods per group or branch.</p>

      <div style="margin-bottom:16px">
        <button type="button" class="btn btn-primary btn-sm" onclick="openProfileModal(null)" style="background:#16a34a;border-color:#16a34a">
          <i class="fa fa-plus"></i> Add Profile
        </button>
      </div>

      <?php if (empty($profiles)): ?>
      <div style="text-align:center;padding:40px 20px;background:#f8fafc;border-radius:10px;border:1.5px dashed #d1d5db">
        <i class="fa fa-id-card" style="font-size:32px;color:#d1d5db;display:block;margin-bottom:10px"></i>
        <p style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px">No profiles yet</p>
        <p style="font-size:12px;color:#9ca3af;margin-bottom:14px">Create a profile to customise the terminal for different cashier roles.</p>
        <button type="button" class="btn btn-primary btn-sm" onclick="openProfileModal(null)" style="background:#16a34a;border-color:#16a34a">
          <i class="fa fa-plus"></i> Create First Profile
        </button>
      </div>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
        <?php foreach ($profiles as $p): ?>
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;overflow:hidden">
          <div style="padding:14px 16px 10px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px">
              <strong style="font-size:14px;color:#111827"><?php echo htmlspecialchars($p['name']); ?></strong>
              <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:<?php echo $p['is_active'] ? '#dcfce7' : '#fee2e2'; ?>;color:<?php echo $p['is_active'] ? '#14532d' : '#991b1b'; ?>">
                <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
              </span>
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-bottom:10px">
              <?php if ($p['branch_id']): foreach ($branches as $b): if ($b['id'] == $p['branch_id']): ?>
                <i class="fa fa-code-fork"></i> <?php echo htmlspecialchars($b['name']); ?> &nbsp;&middot;&nbsp;
              <?php endif; endforeach; else: ?>
                <i class="fa fa-globe"></i> All Branches &nbsp;&middot;&nbsp;
              <?php endif; ?>
              <i class="fa fa-users"></i> <?php echo count($p['assigned_users']); ?> user<?php echo count($p['assigned_users']) !== 1 ? 's' : ''; ?>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:5px">
              <?php
              $perms = [
                'allow_rate_change'     => 'Rate Change',
                'allow_discount_change' => 'Discount',
                'allow_partial_payment' => 'Partial Pay',
              ];
              foreach ($perms as $field => $label): ?>
              <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;padding:2px 7px;border-radius:20px;background:<?php echo $p[$field] ? '#f0fdf4' : '#fef2f2'; ?>;color:<?php echo $p[$field] ? '#15803d' : '#dc2626'; ?>">
                <i class="fa fa-<?php echo $p[$field] ? 'check' : 'times'; ?>"></i> <?php echo $label; ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>
          <div style="padding:8px 12px;background:#f9fafb;border-top:1px solid #f3f4f6;display:flex;align-items:center;justify-content:flex-end;gap:8px">
            <a href="<?php echo admin_url('pos_system/profile_delete/'.$p['id']); ?>"
               onclick="return confirm('Deactivate this profile?')"
               style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#fff;border:1px solid #fecaca;color:#dc2626;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none">
              <i class="fa fa-trash"></i>
            </a>
            <button type="button" class="manage-profile-btn-s"
                    data-profile='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>'
                    style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;background:#16a34a;border:none;color:#fff;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer">
              <i class="fa fa-sliders"></i> Manage
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div><!-- /tab-profiles -->

  </div><!-- /sett-panels -->
</div><!-- /sett-wrap -->

<div class="sett-save-bar">
  <button type="submit" class="btn btn-primary" style="padding:9px 28px">
    <i class="fa fa-save"></i> Save All Settings
  </button>
  <span style="font-size:12px;color:#94a3b8">Changes apply immediately after saving.</span>
</div>
</form>

</div>
</div>

<?php init_tail(); ?>
<script>
$('.sett-sidebar a[data-tab]').on('click', function(e) {
    e.preventDefault();
    var t = $(this).data('tab');
    $('.sett-sidebar a').removeClass('active');
    $(this).addClass('active');
    $('.sett-panel').removeClass('active');
    $('#tab-' + t).addClass('active');
});

// Preserve active tab after form submit
var hash = window.location.hash;
if (hash && hash.startsWith('#tab-')) {
    var tab = hash.replace('#tab-','');
    $('.sett-sidebar a[data-tab="' + tab + '"]').trigger('click');
}
$('.sett-sidebar a[data-tab]').on('click', function() {
    window.history.replaceState(null, null, '#tab-' + $(this).data('tab'));
});

// Update restaurant ON/OFF badge live
$('#restaurantModeToggle').on('change', function() {
    var $lnk = $('#restaurant-tab-link');
    $lnk.find('.sett-tag').remove();
    $lnk.append(this.checked ? '<span class="sett-tag on" style="margin-left:auto">ON</span>' : '<span class="sett-tag off" style="margin-left:auto">OFF</span>');
});
</script>

<!-- ─── Profile Modal (embedded in Settings) ─────────────────────────────── -->
<style>
#sProfileModal .nav-tabs>li>a { color:#5a6480; border:none; border-bottom:2px solid transparent; margin-bottom:-2px; padding:11px 15px; font-size:13px; font-weight:600; }
#sProfileModal .nav-tabs>li.active>a,#sProfileModal .nav-tabs>li>a:hover { color:#16a34a; border-bottom-color:#16a34a; background:transparent; }
#sProfileModal .tab-content { padding:18px 22px; }
#sProfileModal .sec-ttl { font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6; }
#sProfileModal .sec-ttl:first-child { margin-top:0; }
</style>

<div class="modal fade" id="sProfileModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog" style="width:820px;max-width:95vw">
    <div class="modal-content">
      <div class="modal-header" style="background:#111827;border-bottom:3px solid #16a34a;padding:14px 20px">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;margin-top:-2px">&times;</button>
        <h4 class="modal-title" id="sProfileModalTitle" style="font-size:16px;font-weight:700;color:#fff">Add Profile</h4>
      </div>
      <div class="modal-body" style="padding:0">
        <ul class="nav nav-tabs" style="padding:0 20px;margin:0;background:#f9fafb;border-bottom:2px solid #e5e7eb">
          <li class="active"><a href="#sptab-general"   data-toggle="tab"><i class="fa fa-cog"></i> General</a></li>
          <li>              <a href="#sptab-users"      data-toggle="tab"><i class="fa fa-users"></i> Users</a></li>
          <li>              <a href="#sptab-payments"   data-toggle="tab"><i class="fa fa-credit-card"></i> Payments</a></li>
          <li>              <a href="#sptab-accounting" data-toggle="tab"><i class="fa fa-calculator"></i> Accounting</a></li>
        </ul>
        <form method="POST" action="<?php echo admin_url('pos_system/profile_save'); ?>" id="sProfileForm">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="id" id="spfId">
          <div class="tab-content">
            <!-- General -->
            <div class="tab-pane active" id="sptab-general">
              <div class="sec-ttl">Profile Info</div>
              <div class="row">
                <div class="col-md-6"><div class="form-group">
                  <label>Profile Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" id="spfName" class="form-control" required placeholder="e.g. Retail Cashier">
                </div></div>
                <div class="col-md-4"><div class="form-group">
                  <label>Branch</label>
                  <select name="branch_id" id="spfBranchId" class="form-control">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div></div>
                <div class="col-md-2"><div class="form-group">
                  <label>Status</label>
                  <div class="checkbox" style="margin-top:8px"><label><input type="checkbox" name="is_active" id="spfIsActive" value="1" checked> Active</label></div>
                </div></div>
              </div>
              <div class="sec-ttl">Invoice Settings</div>
              <div class="row">
                <div class="col-md-4"><div class="form-group">
                  <label>Action on New Invoice</label>
                  <select name="action_on_new_invoice" id="spfActionOnNewInvoice" class="form-control">
                    <option value="ask">Ask</option><option value="new">Always New</option><option value="continue">Continue</option>
                  </select>
                </div></div>
                <div class="col-md-4"><div class="form-group">
                  <label>Invoice Prefix</label>
                  <input type="text" name="invoice_prefix" id="spfInvoicePrefix" class="form-control" value="POS-">
                </div></div>
                <div class="col-md-4"><div class="form-group">
                  <label>Print Template</label>
                  <select name="print_template_id" id="spfPrintTemplateId" class="form-control">
                    <option value="">— Default Thermal —</option>
                    <?php foreach ($print_templates as $pt): ?>
                    <option value="<?php echo $pt['id']; ?>"><?php echo htmlspecialchars($pt['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div></div>
              </div>
              <div class="sec-ttl">Behaviour</div>
              <div class="row">
                <?php foreach ([
                  ['allow_rate_change','Allow Rate Change'],['allow_discount_change','Allow Discount'],
                  ['allow_partial_payment','Allow Partial Payment'],['hide_images','Hide Images'],
                  ['auto_add_item_to_cart','Auto Add to Cart'],['validate_stock_on_save','Validate Stock'],
                  ['print_receipt_on_order_complete','Auto Print Receipt'],['ignore_pricing_rule','Ignore Pricing Rule'],
                  ['auto_create_invoice','Auto Create Invoice'],
                ] as [$field, $label]): ?>
                <div class="col-md-4"><div class="form-group"><div class="checkbox">
                  <label><input type="checkbox" name="<?php echo $field; ?>" id="spf_<?php echo $field; ?>" value="1"> <?php echo $label; ?></label>
                </div></div></div>
                <?php endforeach; ?>
              </div>
            </div>
            <!-- Users -->
            <div class="tab-pane" id="sptab-users">
              <div id="spUsersNoProfile" class="alert alert-info" style="display:none"><i class="fa fa-info-circle"></i> Save the profile first to assign users.</div>
              <div id="spUsersMgmt">
                <div class="row" style="margin-bottom:12px">
                  <div class="col-md-6">
                    <select id="spAddUserSelect" class="form-control"><option value="">— Select Staff —</option>
                      <?php foreach ($staff_list as $s): ?>
                      <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['firstname'].' '.$s['lastname']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4"><div class="checkbox" style="margin:7px 0 0"><label><input type="checkbox" id="spAddUserDefault"> Set as Default</label></div></div>
                  <div class="col-md-2"><button type="button" class="btn btn-success btn-block" id="spAddUserBtn"><i class="fa fa-plus"></i> Add</button></div>
                </div>
                <table class="table table-bordered table-condensed" id="spUsersTable">
                  <thead><tr><th style="width:42px;text-align:center">No.</th><th style="width:70px;text-align:center">Default</th><th>User</th><th style="width:70px;text-align:center">Remove</th></tr></thead>
                  <tbody id="spUsersTbody"><tr><td colspan="4" class="text-center text-muted" style="padding:20px">No users assigned</td></tr></tbody>
                </table>
              </div>
            </div>
            <!-- Payments -->
            <div class="tab-pane" id="sptab-payments">
              <p class="text-muted" style="margin-bottom:14px">Select payment methods available when this profile is active.</p>
              <?php if (empty($payment_methods)): ?>
              <div class="alert alert-warning"><i class="fa fa-warning"></i> No payment methods configured yet.</div>
              <?php else: ?>
              <div class="row">
                <?php foreach ($payment_methods as $pm): ?>
                <div class="col-md-4" style="margin-bottom:8px"><div class="checkbox"><label style="font-weight:normal">
                  <input type="checkbox" name="payment_method_ids[]" value="<?php echo $pm['id']; ?>" class="sp-pm-checkbox">
                  <strong><?php echo htmlspecialchars($pm['name']); ?></strong>
                  <br><small class="text-muted" style="padding-left:20px"><?php echo ucfirst($pm['type'] ?? 'cash'); ?></small>
                </label></div></div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
            <!-- Accounting -->
            <div class="tab-pane" id="sptab-accounting">
              <div class="sec-ttl">Currency</div>
              <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Default Currency</label>
                  <select name="default_currency" id="spfCurrency" class="form-control">
                    <?php foreach (['KES'=>'KES — Kenyan Shilling','UGX'=>'UGX — Ugandan Shilling','TZS'=>'TZS — Tanzanian Shilling','RWF'=>'RWF — Rwandan Franc','ETB'=>'ETB — Ethiopian Birr','USD'=>'USD — US Dollar'] as $code => $lbl): ?>
                    <option value="<?php echo $code; ?>"><?php echo $lbl; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div></div>
                <div class="col-md-4"><div class="form-group"><label>Apply Discount On</label>
                  <select name="apply_discount_on" id="spfApplyDiscountOn" class="form-control">
                    <option value="grand_total">Grand Total</option><option value="net_total">Net Total</option>
                  </select>
                </div></div>
                <div class="col-md-4"><div class="form-group"><label>Price List</label>
                  <select name="price_list_id" id="spfPriceListId" class="form-control">
                    <option value="">— Standard —</option>
                    <?php foreach ($price_lists as $pl): ?>
                    <option value="<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div></div>
              </div>
              <div class="sec-ttl">Accounts</div>
              <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Income Account</label><input type="text" name="income_account" id="spfIncomeAccount" class="form-control" placeholder="e.g. Sales Revenue"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Expense Account</label><input type="text" name="expense_account" id="spfExpenseAccount" class="form-control" placeholder="e.g. Cost of Goods Sold"></div></div>
              </div>
            </div>
          </div><!-- /tab-content -->
        </form>
      </div>
      <div class="modal-footer" style="background:#f9fafb;border-top:1px solid #e5e7eb">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="spSaveProfileBtn" style="background:#16a34a;border-color:#16a34a">
          <i class="fa fa-save"></i> Save Profile
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var CSRF_N = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var CSRF_H = '<?php echo $this->security->get_csrf_hash(); ?>';
  var BASE   = '<?php echo rtrim(admin_url('pos_system'), '/'); ?>';
  var curId  = null;

  function openProfileModal(p) {
    $('#sProfileForm')[0].reset();
    $('#spfId').val('');
    $('#spfIsActive').prop('checked', true);
    $('#spfCurrency').val('KES');
    $('#sProfileModal #spUsersTbody').html('<tr><td colspan="4" class="text-center text-muted" style="padding:20px">No users assigned</td></tr>');
    $('#sProfileModal .nav-tabs a:first').tab('show');

    if (p && p.id) {
      curId = p.id;
      $('#sProfileModalTitle').text('Manage: ' + p.name);
      $('#spfId').val(p.id);
      $('#spfName').val(p.name || '');
      $('#spfBranchId').val(p.branch_id || '');
      $('#spfIsActive').prop('checked', p.is_active == 1);
      $('#spfActionOnNewInvoice').val(p.action_on_new_invoice || 'ask');
      $('#spfInvoicePrefix').val(p.invoice_prefix || 'POS-');
      $('#spfPrintTemplateId').val(p.print_template_id || '');
      $('#spfCurrency').val(p.default_currency || 'KES');
      $('#spfApplyDiscountOn').val(p.apply_discount_on || 'grand_total');
      $('#spfPriceListId').val(p.price_list_id || '');
      $('#spfIncomeAccount').val(p.income_account || '');
      $('#spfExpenseAccount').val(p.expense_account || '');
      ['allow_rate_change','allow_discount_change','allow_partial_payment','hide_images',
       'auto_add_item_to_cart','validate_stock_on_save','print_receipt_on_order_complete',
       'ignore_pricing_rule','auto_create_invoice'].forEach(function(f){
        var el = document.getElementById('spf_'+f);
        if (el) el.checked = p[f] == 1;
      });
      var pmIds = (p.payment_method_ids||[]).map(Number);
      $('.sp-pm-checkbox').each(function(){ this.checked = pmIds.indexOf(parseInt(this.value,10)) !== -1; });
      $('#spUsersNoProfile').hide(); $('#spUsersMgmt').show();
      renderSpUsers(p.assigned_users || []);
    } else {
      curId = null;
      $('#sProfileModalTitle').text('Add Profile');
      $('#spUsersNoProfile').show(); $('#spUsersMgmt').hide();
    }
    $('#sProfileModal').modal('show');
  }
  window.openProfileModal = openProfileModal;

  document.querySelectorAll('.manage-profile-btn-s').forEach(function(btn){
    btn.addEventListener('click', function(){ openProfileModal(JSON.parse(this.dataset.profile)); });
  });

  $('#spSaveProfileBtn').on('click', function(){ $('#sProfileForm').submit(); });

  function renderSpUsers(users){
    var tbody = document.getElementById('spUsersTbody');
    if (!users || !users.length){ tbody.innerHTML='<tr><td colspan="4" class="text-center text-muted" style="padding:20px">No users assigned</td></tr>'; return; }
    tbody.innerHTML = users.map(function(u,i){
      var nm = ((u.firstname||'')+' '+(u.lastname||'')).trim();
      var df = parseInt(u.is_default,10)===1;
      return '<tr><td style="text-align:center">'+(i+1)+'</td><td style="text-align:center">'+
        '<button type="button" style="background:none;border:none;cursor:pointer;font-size:15px;color:'+(df?'#f59e0b':'#d1d5db')+'" onclick="spSetDefault('+u.id+')"><i class="fa fa-star"></i></button></td>'+
        '<td><strong>'+esc(nm)+'</strong><br><small class="text-muted">'+esc(u.email||'')+'</small></td>'+
        '<td style="text-align:center"><button type="button" class="btn btn-xs btn-danger" onclick="spRemoveUser('+u.id+')"><i class="fa fa-times"></i></button></td></tr>';
    }).join('');
  }

  $('#spAddUserBtn').on('click', function(){
    if (!curId){ alert('Save the profile first.'); return; }
    var sid = parseInt($('#spAddUserSelect').val(),10);
    if (!sid){ alert('Select a staff member.'); return; }
    var df  = $('#spAddUserDefault').prop('checked');
    var d = {}; d[CSRF_N]=CSRF_H; d.profile_id=curId; d.staff_id=sid; d.is_default=df?1:0;
    $.post(BASE+'/profile_user_assign', d, function(r){ if (r&&r.success){ renderSpUsers(r.users); $('#spAddUserSelect').val(''); $('#spAddUserDefault').prop('checked',false); } else { alert((r&&r.error)||'Error'); } }, 'json');
  });

  window.spRemoveUser = function(sid){
    if (!curId||!confirm('Remove user?')) return;
    var d={}; d[CSRF_N]=CSRF_H; d.profile_id=curId; d.staff_id=sid;
    $.post(BASE+'/profile_user_remove', d, function(r){ if(r&&r.success) renderSpUsers(r.users); }, 'json');
  };
  window.spSetDefault = function(sid){
    if (!curId) return;
    var d={}; d[CSRF_N]=CSRF_H; d.profile_id=curId; d.staff_id=sid;
    $.post(BASE+'/profile_user_set_default', d, function(r){ if(r&&r.success) renderSpUsers(r.users); }, 'json');
  };

  function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
})();
</script>
