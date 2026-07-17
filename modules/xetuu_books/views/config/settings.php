<?php defined('BASEPATH') or exit('No direct script access allowed'); 

function xb_render_account_select($name, $label, $accounts, $settings, $required = false) {
    $val = $settings[$name] ?? '';
    $req = $required ? 'required' : '';
    $html = '<div class="form-group">';
    $html .= '<label>' . $label . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';
    $html .= '<select name="' . $name . '" class="form-control selectpicker" data-live-search="true" ' . $req . '>';
    $html .= '<option value=""></option>';
    foreach ($accounts as $acc) {
        $sel = ($val == $acc->code) ? 'selected' : '';
        $html .= '<option value="' . $acc->code . '" ' . $sel . '>' . $acc->code . ' - ' . htmlspecialchars($acc->name) . '</option>';
    }
    $html .= '</select></div>';
    return $html;
}

function xb_render_select($name, $label, $options, $settings, $required = false) {
    $val = $settings[$name] ?? '';
    $req = $required ? 'required' : '';
    $html = '<div class="form-group">';
    $html .= '<label>' . $label . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';
    $html .= '<select name="' . $name . '" class="form-control selectpicker" ' . $req . '>';
    foreach ($options as $k => $v) {
        $sel = ($val == $k) ? 'selected' : '';
        $html .= '<option value="' . $k . '" ' . $sel . '>' . htmlspecialchars($v) . '</option>';
    }
    $html .= '</select></div>';
    return $html;
}
?>

<style>
.settings-tabs .nav-pills > li {
    width: 100%;
}
.settings-tabs .nav-pills > li > a {
    border-radius: 0;
    color: #4b5563;
    padding: 12px 15px;
    border-left: 3px solid transparent;
    font-size: 14px;
    margin-bottom: 0;
}
.settings-tabs .nav-pills > li.active > a,
.settings-tabs .nav-pills > li.active > a:hover,
.settings-tabs .nav-pills > li.active > a:focus {
    background-color: #f3f4f6;
    color: #111827;
    border-left-color: #3b82f6;
    font-weight: 500;
}
.settings-tabs .nav-pills > li > a:hover {
    background-color: #f9fafb;
}
.tab-content-panel {
    padding: 25px;
    background: #fff;
    min-height: 600px;
}
.tab-content-panel h4 {
    margin-top: 0;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 18px;
    font-weight: 500;
    color: #111827;
}
.save-bar {
    background: #fff;
    padding: 15px 20px;
    border-top: 1px solid #e5e7eb;
    margin-top: 20px;
    border-radius: 0 0 4px 4px;
    text-align: right;
}
.p-0 { padding: 0 !important; }
</style>

<div class="row">
    <div class="col-md-12">
        <form id="accounting-settings-form">
            <div class="row">
                <!-- Left Vertical Tabs -->
                <div class="col-md-3 settings-tabs">
                    <div class="xb-card" style="margin-bottom: 0;">
                        <div class="xb-card-body p-0">
                            <ul class="nav nav-pills nav-stacked" role="tablist">
                                <li role="presentation" class="active"><a href="#tab_general" role="tab" data-toggle="tab">General</a></li>
                                <li role="presentation"><a href="#tab_coa" role="tab" data-toggle="tab">Chart of Accounts</a></li>
                                <li role="presentation"><a href="#tab_defaults" role="tab" data-toggle="tab">Core Default Accounts</a></li>
                                <li role="presentation"><a href="#tab_sales" role="tab" data-toggle="tab">Sales Posting</a></li>
                                <li role="presentation"><a href="#tab_purchase" role="tab" data-toggle="tab">Purchase Posting</a></li>
                                <li role="presentation"><a href="#tab_banking" role="tab" data-toggle="tab">Banking</a></li>
                                <li role="presentation"><a href="#tab_taxes" role="tab" data-toggle="tab">Tax Accounts</a></li>
                                <li role="presentation"><a href="#tab_exchange" role="tab" data-toggle="tab">Exchange Gain/Loss</a></li>
                                <li role="presentation"><a href="#tab_discounts" role="tab" data-toggle="tab">Write Off & Discounts</a></li>
                                <li role="presentation"><a href="#tab_advances" role="tab" data-toggle="tab">Advance Payments</a></li>
                                <li role="presentation"><a href="#tab_assets" role="tab" data-toggle="tab">Fixed Assets</a></li>
                                <li role="presentation"><a href="#tab_deferred" role="tab" data-toggle="tab">Deferred Accounting</a></li>
                                <li role="presentation"><a href="#tab_cost_centers" role="tab" data-toggle="tab">Cost Centers</a></li>
                                <li role="presentation"><a href="#tab_books" role="tab" data-toggle="tab">Finance Books</a></li>
                                <li role="presentation"><a href="#tab_budget" role="tab" data-toggle="tab">Budget Controls</a></li>
                                <li role="presentation"><a href="#tab_audit" role="tab" data-toggle="tab">Audit & Controls</a></li>
                                <li role="presentation"><a href="#tab_payroll" role="tab" data-toggle="tab">Payroll</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="col-md-9">
                    <div class="xb-card" style="margin-bottom: 0;">
                        <div class="xb-card-body tab-content-panel">
                            <div class="tab-content">
                                
                                <!-- General -->
                                <div role="tabpanel" class="tab-pane active" id="tab_general">
                                    <h4>General Settings</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Default Currency</label>
                                                <select name="default_currency_id" class="form-control selectpicker">
                                                    <?php foreach($currencies as $c): ?>
                                                        <option value="<?php echo $c->id; ?>" <?php echo (isset($settings['default_currency_id']) && $settings['default_currency_id'] == $c->id) ? 'selected' : ''; ?>><?php echo $c->name; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Fiscal Year Start (MM-DD)</label>
                                                <input type="text" name="fiscal_year_start" class="form-control" value="<?php echo $settings['fiscal_year_start'] ?? '01-01'; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Invoice Prefix</label>
                                                <input type="text" name="invoice_sequence_prefix" class="form-control" value="<?php echo $settings['invoice_sequence_prefix'] ?? 'INV'; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Bill Prefix</label>
                                                <input type="text" name="bill_sequence_prefix" class="form-control" value="<?php echo $settings['bill_sequence_prefix'] ?? 'BILL'; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart of Accounts -->
                                <div role="tabpanel" class="tab-pane" id="tab_coa">
                                    <h4>Chart of Accounts Template</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_select('coa_template', 'Select Template', [
                                                'standard' => 'Standard',
                                                'manufacturing' => 'Manufacturing',
                                                'retail' => 'Retail',
                                                'service' => 'Service',
                                                'non_profit' => 'Non-Profit',
                                                'custom' => 'Custom'
                                            ], $settings); ?>
                                            
                                            <button type="button" class="btn btn-primary" id="btn-generate-coa">Generate Chart of Accounts</button>
                                        </div>
                                    </div>

                                    <hr>
                                    <h4>Re-sync Transactions</h4>
                                    <p class="text-muted" style="font-size:13px;">Use this after generating a new Chart of Accounts to re-post all system invoices, payments, expenses, and purchase bills into the accounting ledger. Safe to run multiple times.</p>
                                    <button type="button" class="btn btn-warning" id="btn-resync-all">
                                        <i class="fa fa-refresh"></i> Re-sync All Transactions
                                    </button>
                                    <span id="resync-status" style="margin-left:10px;font-size:13px;"></span>
                                </div>

                                <!-- Core Default Accounts -->
                                <div role="tabpanel" class="tab-pane" id="tab_defaults">
                                    <h4>Core Default Accounts</h4>
                                    <div class="alert alert-info">These accounts are mandatory for basic system operation.</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('default_bank_account', 'Default Bank Account', $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('default_cash_account', 'Default Cash Account', $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('default_receivable_account', 'Default Receivable Account', $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('default_payable_account', 'Default Payable Account', $gl_accounts, $settings, true); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('default_income_account', 'Default Income Account', $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('default_expense_account', 'Default Expense Account', $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('default_cogs_account', 'Default Cost of Goods Sold Account', $gl_accounts, $settings, false); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sales Posting -->
                                <div role="tabpanel" class="tab-pane" id="tab_sales">
                                    <h4>Sales Posting Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('sales_revenue_account', 'Default Revenue Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('sales_discount_account', 'Sales Discount Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('sales_returns_account', 'Sales Returns Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('sales_receivable_account', 'Accounts Receivable Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('customer_advance_account', 'Customer Advance Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('bad_debt_account', 'Bad Debt Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('revenue_recognition_account', 'Revenue Recognition Account', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purchase Posting -->
                                <div role="tabpanel" class="tab-pane" id="tab_purchase">
                                    <h4>Purchase Posting Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('purchase_account', 'Default Purchase Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('purchase_expense_account', 'Default Expense Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('purchase_payable_account', 'Accounts Payable Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('supplier_advance_account', 'Supplier Advance Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('purchase_discount_account', 'Purchase Discount Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('accrued_expense_account', 'Accrued Expense Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('grni_account', 'Goods Received Not Invoiced (GRNI)', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Banking -->
                                <div role="tabpanel" class="tab-pane" id="tab_banking">
                                    <h4>Banking Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('bank_suspense_account', 'Bank Suspense Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('outstanding_receipts_account', 'Outstanding Receipts Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('outstanding_payments_account', 'Outstanding Payments Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('internal_transfer_account', 'Internal Transfer Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('bank_charges_account', 'Bank Charges Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('interest_income_account', 'Interest Income Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('interest_expense_account', 'Interest Expense Account', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax Accounts -->
                                <div role="tabpanel" class="tab-pane" id="tab_taxes">
                                    <h4>Tax Accounts Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('vat_output_account', 'VAT Output Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('vat_input_account', 'VAT Input Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('wht_payable_account', 'Withholding Tax Payable', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('wh_vat_payable_account', 'Withholding VAT Payable', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('paye_payable_account', 'PAYE Payable', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('nssf_payable_account', 'NSSF Payable', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('sha_payable_account', 'SHA Payable', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('housing_levy_payable_account', 'Housing Levy Payable', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Exchange Gain/Loss -->
                                <div role="tabpanel" class="tab-pane" id="tab_exchange">
                                    <h4>Exchange Gain/Loss Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Exchange Difference Journal</label>
                                                <select name="exchange_difference_journal_id" class="form-control selectpicker">
                                                    <option value=""></option>
                                                    <!-- Journals not passed yet, keep text input for now or omit -->
                                                    <option value="1">Miscellaneous Operations</option>
                                                </select>
                                            </div>
                                            <?php echo xb_render_account_select('realized_exchange_gain_account', 'Realized Exchange Gain', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('realized_exchange_loss_account', 'Realized Exchange Loss', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('unrealized_exchange_gain_account', 'Unrealized Exchange Gain', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('unrealized_exchange_loss_account', 'Unrealized Exchange Loss', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Write Off & Discounts -->
                                <div role="tabpanel" class="tab-pane" id="tab_discounts">
                                    <h4>Write Off & Discounts</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('default_write_off_account', 'Write Off Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('cash_discount_gain_account', 'Cash Discount Gain', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('cash_discount_loss_account', 'Cash Discount Loss', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('settlement_discount_account', 'Settlement Discount Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('rounding_difference_account', 'Rounding Difference Account', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advance Payments -->
                                <div role="tabpanel" class="tab-pane" id="tab_advances">
                                    <h4>Advance Payments</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('adv_customer_account', 'Customer Advances Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('adv_supplier_account', 'Supplier Advances Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_select('enable_separate_advance_accounts', 'Enable Separate Advance Accounts', ['0'=>'No', '1'=>'Yes'], $settings); ?>
                                            <?php echo xb_render_select('advance_reconciliation_method', 'Reconciliation Method', ['oldest'=>'Oldest First', 'newest'=>'Newest First', 'manual'=>'Manual'], $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fixed Assets -->
                                <div role="tabpanel" class="tab-pane" id="tab_assets">
                                    <h4>Fixed Assets Configuration</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('fa_asset_account', 'Asset Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_accumulated_depreciation_account', 'Accumulated Depreciation Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_depreciation_expense_account', 'Depreciation Expense Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_cwip_account', 'CWIP Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('fa_received_not_billed_account', 'Asset Received But Not Billed', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_disposal_gain_account', 'Asset Disposal Gain Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_disposal_loss_account', 'Asset Disposal Loss Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('fa_revaluation_reserve_account', 'Asset Revaluation Reserve', $gl_accounts, $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deferred Accounting -->
                                <div role="tabpanel" class="tab-pane" id="tab_deferred">
                                    <h4>Deferred Accounting</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('deferred_revenue_account', 'Deferred Revenue Account', $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('deferred_expense_account', 'Deferred Expense Account', $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- placeholder for journal selects -->
                                            <div class="form-group"><label>Revenue Recognition Journal</label><select class="form-control" disabled></select></div>
                                            <div class="form-group"><label>Expense Recognition Journal</label><select class="form-control" disabled></select></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Centers, Books, Budget, Audit -->
                                <!-- We just put placeholders for these or minimal fields as per spec -->
                                <div role="tabpanel" class="tab-pane" id="tab_cost_centers">
                                    <h4>Cost Centers</h4>
                                    <div class="alert alert-warning">Cost Center module is disabled.</div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_books">
                                    <h4>Finance Books</h4>
                                    <div class="alert alert-warning">Multi-book accounting is disabled.</div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_budget">
                                    <h4>Budget Controls</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_select('allow_budget_override', 'Allow Budget Override', ['0'=>'No', '1'=>'Yes'], $settings); ?>
                                            <div class="form-group">
                                                <label>Budget Warning Threshold %</label>
                                                <input type="number" name="budget_warning_threshold" class="form-control" value="<?php echo $settings['budget_warning_threshold'] ?? '90'; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_audit">
                                    <h4>Audit & Controls</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo xb_render_select('allow_backdated_entries', 'Allow Backdated Entries', ['0'=>'No', '1'=>'Yes'], $settings); ?>
                                            <?php echo xb_render_select('require_approval_before_posting', 'Require Approval Before Posting', ['0'=>'No', '1'=>'Yes'], $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_select('auto_generate_journals', 'Auto Generate Journals', ['1'=>'Yes', '0'=>'No'], $settings); ?>
                                            <?php echo xb_render_select('auto_post_journals', 'Auto Post Journals', ['0'=>'No', '1'=>'Yes'], $settings); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payroll Integration -->
                                <div role="tabpanel" class="tab-pane" id="tab_payroll">
                                    <h4>Payroll Integration</h4>
                                    <p style="color:#6b7280; font-size:13px; margin-bottom:20px;">
                                        Map payroll categories to accounts in your Chart of Accounts.
                                        When a payslip is <strong>confirmed</strong> in Xetuu HR, a balanced journal entry is automatically posted here.
                                    </p>
                                    <div class="alert alert-info" style="font-size:12px;">
                                        <strong>Journal entry structure:</strong><br>
                                        Dr Salary Expense &nbsp;(total gross) &nbsp;|&nbsp;
                                        Cr NSSF Payable &nbsp;|&nbsp; Cr PAYE Payable &nbsp;|&nbsp;
                                        Cr SHIF Payable &nbsp;|&nbsp; Cr Housing Levy Payable &nbsp;|&nbsp;
                                        Cr Net Wages Payable
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payroll Journal <span class="text-danger">*</span></label>
                                                <select name="payroll_journal_id" class="form-control selectpicker" data-live-search="true">
                                                    <option value=""></option>
                                                    <?php foreach ($journals as $j): ?>
                                                    <option value="<?php echo $j->id; ?>" <?php echo ($settings['payroll_journal_id'] ?? '') == $j->id ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($j->name); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <span class="help-block" style="font-size:11px;">Select or create a "Payroll" journal in Journals config</span>
                                            </div>
                                            <?php echo xb_render_account_select('payroll_salary_account',    'Salary Expense Account (Dr)',          $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('payroll_net_payable_account', 'Net Wages Payable Account (Cr)',       $gl_accounts, $settings, true); ?>
                                            <?php echo xb_render_account_select('payroll_nssf_payable_account', 'NSSF Payable Account (Cr)',           $gl_accounts, $settings); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php echo xb_render_account_select('payroll_paye_payable_account', 'PAYE Payable Account (Cr)',           $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('payroll_shif_payable_account', 'SHIF Payable Account (Cr)',           $gl_accounts, $settings); ?>
                                            <?php echo xb_render_account_select('payroll_ahl_payable_account',  'Housing Levy Payable Account (Cr)',   $gl_accounts, $settings); ?>
                                            <div class="form-group">
                                                <label>Auto-post journal on confirm?</label>
                                                <select name="payroll_auto_post" class="form-control">
                                                    <option value="1" <?php echo ($settings['payroll_auto_post'] ?? '1') == '1' ? 'selected' : ''; ?>>Yes — post immediately</option>
                                                    <option value="0" <?php echo ($settings['payroll_auto_post'] ?? '1') == '0' ? 'selected' : ''; ?>>No — save as draft</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="save-bar">
                            <button type="submit" class="btn btn-primary xb-btn-primary" id="btn-save-settings">Save All Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── COA / Resync Confirm Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="xbConfirmModal" tabindex="-1" role="dialog" aria-labelledby="xbConfirmModalLabel">
    <div class="modal-dialog modal-dialog-centered xb-confirm-dialog" role="document">
        <div class="modal-content xb-confirm-content">
            <div class="xb-confirm-header">
                <span class="xb-confirm-icon" id="xbConfirmIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <button type="button" class="xb-confirm-close" data-dismiss="modal" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body xb-confirm-body">
                <h4 class="xb-confirm-title" id="xbConfirmTitle"></h4>
                <div class="xb-confirm-text" id="xbConfirmText"></div>
                <ul class="xb-confirm-list" id="xbConfirmList"></ul>
            </div>
            <div class="xb-confirm-footer">
                <button type="button" class="btn xb-btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn xb-btn-confirm" id="xbConfirmOk"></button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Modal wrapper ─────────────────────────────────────────────────────── */
.xb-confirm-dialog {
    max-width: 460px;
    margin: 60px auto;
}
.xb-confirm-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    overflow: hidden;
}

/* ── Header strip ─────────────────────────────────────────────────────── */
.xb-confirm-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 28px 28px 0;
}
.xb-confirm-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.xb-confirm-icon svg { width: 24px; height: 24px; }
.xb-confirm-icon.icon-warning { background: #fff7ed; color: #ea580c; }
.xb-confirm-icon.icon-info    { background: #eff6ff; color: #2563eb; }
.xb-confirm-close {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #9ca3af;
    line-height: 1;
    margin-top: -2px;
}
.xb-confirm-close:hover { color: #374151; }
.xb-confirm-close svg { width: 18px; height: 18px; display: block; }

/* ── Body ─────────────────────────────────────────────────────────────── */
.xb-confirm-body { padding: 16px 28px 4px; }
.xb-confirm-title {
    font-size: 17px;
    font-weight: 700;
    color: #111827;
    margin: 12px 0 8px;
    line-height: 1.3;
}
.xb-confirm-text {
    font-size: 13.5px;
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 10px;
}
.xb-confirm-list {
    margin: 0 0 6px;
    padding-left: 0;
    list-style: none;
}
.xb-confirm-list li {
    font-size: 13px;
    color: #374151;
    padding: 5px 0 5px 22px;
    position: relative;
    border-bottom: 1px solid #f3f4f6;
}
.xb-confirm-list li:last-child { border-bottom: none; }
.xb-confirm-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #16a34a;
}

/* ── Footer ───────────────────────────────────────────────────────────── */
.xb-confirm-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px 28px 24px;
    background: #f9fafb;
    border-top: 1px solid #f0f0f0;
    margin-top: 14px;
}
.xb-btn-cancel {
    background: #fff;
    border: 1px solid #d1d5db;
    color: #374151;
    font-size: 13.5px;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 8px;
}
.xb-btn-cancel:hover { background: #f3f4f6; }
.xb-btn-confirm {
    font-size: 13.5px;
    font-weight: 600;
    padding: 8px 22px;
    border-radius: 8px;
    border: none;
    color: #fff;
}
.xb-btn-confirm.btn-orange  { background: #ea580c; }
.xb-btn-confirm.btn-orange:hover  { background: #c2410c; }
.xb-btn-confirm.btn-blue    { background: #2563eb; }
.xb-btn-confirm.btn-blue:hover    { background: #1d4ed8; }
</style>

<script>
window.addEventListener('DOMContentLoaded', function() {

    /* ── Reusable confirm modal ─────────────────────────────────────────── */
    function xbConfirm(opts, onConfirm) {
        // opts: { title, text, items[], btnLabel, btnClass, iconClass }
        var $m = $('#xbConfirmModal');
        $m.find('#xbConfirmTitle').text(opts.title || 'Are you sure?');
        $m.find('#xbConfirmText').text(opts.text || '');
        var $icon = $m.find('#xbConfirmIcon').attr('class', 'xb-confirm-icon ' + (opts.iconClass || 'icon-warning'));
        var $list = $m.find('#xbConfirmList').empty();
        if (opts.items && opts.items.length) {
            opts.items.forEach(function(item) {
                $list.append('<li>' + item + '</li>');
            });
        }
        var $ok = $m.find('#xbConfirmOk')
            .text(opts.btnLabel || 'Continue')
            .attr('class', 'btn xb-btn-confirm ' + (opts.btnClass || 'btn-orange'))
            .off('click').on('click', function() {
                $m.modal('hide');
                onConfirm();
            });
        $m.modal('show');
    }
    $('#accounting-settings-form').on('submit', function(e) {
        e.preventDefault();
        $('#btn-save-settings').prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: admin_url + 'xetuu_books/ajax/save_settings',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $('#btn-save-settings').prop('disabled', false).text('Save All Settings');
                if(res.success) {
                    alert_float('success', 'Settings saved successfully');
                } else {
                    alert_float('danger', 'Failed to save settings');
                }
            },
            error: function() {
                $('#btn-save-settings').prop('disabled', false).text('Save All Settings');
                alert_float('danger', 'An error occurred.');
            }
        });
    });

    function xbRunResync($btn, $status, onDone) {
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Syncing data...');
        if ($status) $status.css('color','#888').text('Scanning and posting all transactions...');
        $.ajax({
            url: admin_url + 'xetuu_books/ajax/resync_all',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    if ($status) $status.css('color','#27ae60').text(res.message);
                    alert_float('success', res.message);
                } else {
                    if ($status) $status.css('color','#e74c3c').text(res.message || 'Sync failed.');
                    alert_float('danger', res.message || 'Sync failed.');
                }
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Re-sync All Transactions');
                if (typeof onDone === 'function') onDone(res);
            },
            error: function(xhr) {
                if ($status) $status.css('color','#e74c3c').text('Server error.');
                alert_float('danger', 'Server error during sync (HTTP ' + xhr.status + ').');
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Re-sync All Transactions');
                if (typeof onDone === 'function') onDone(null);
            }
        });
    }

    $(document).on('click', '#btn-generate-coa', function(e) {
        e.preventDefault();
        var template = $('select[name="coa_template"]').val();
        if (!template) { alert_float('warning', 'Please select a template first'); return; }

        var $btn = $(this);
        var $status = $('#resync-status');

        xbConfirm({
            title: 'Generate Chart of Accounts',
            text: 'This will reset all account records to the selected template and clear existing journal entries. Your invoices, payments, expenses, and purchase bills will be automatically re-mapped into the new chart.',
            items: [
                'All existing accounts will be replaced',
                'All existing journal entries will be cleared',
                'All system transactions will be re-posted automatically'
            ],
            btnLabel: 'Generate & Re-sync',
            btnClass: 'btn-orange',
            iconClass: 'icon-warning'
        }, function() {
        $btn.prop('disabled', true).text('Generating accounts...');
        $status.css('color','#888').text('');

        $.ajax({
            url: admin_url + 'xetuu_books/ajax/generate_coa',
            type: 'POST',
            data: { template: template },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert_float('success', 'Chart of Accounts generated — ' + (res.inserted || 0) + ' accounts created. Now syncing your data...');
                    // Auto-run full re-sync after COA generation
                    xbRunResync($('#btn-resync-all'), $status, function() {
                        setTimeout(function() { window.location.reload(); }, 2000);
                    });
                } else {
                    $btn.prop('disabled', false).text('Generate Chart of Accounts');
                    alert_float('danger', res.message || 'Failed to generate Chart of Accounts');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Generate Chart of Accounts');
                var msg = 'Server error.';
                try { var r = JSON.parse(xhr.responseText); msg = r.message || r.error || msg; } catch(e2) {}
                alert_float('danger', msg + ' (HTTP ' + xhr.status + ')');
            }
        });
        }); // end xbConfirm
    });

    // Manual re-sync button (can be run any time independently)
    $('#btn-resync-all').on('click', function() {
        var $btn = $(this);
        xbConfirm({
            title: 'Re-sync All Transactions',
            text: 'The system will scan and post all transactions into the accounting ledger.',
            items: [
                'System invoices & payments',
                'Expenses',
                'Purchase bills & payments',
                'Warehouse goods receipts'
            ],
            btnLabel: 'Re-sync Now',
            btnClass: 'btn-blue',
            iconClass: 'icon-info'
        }, function() {
            xbRunResync($btn, $('#resync-status'), null);
        });
    });
});
</script>
