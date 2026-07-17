<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Xetuu Books
Description: Enterprise double-entry accounting for Perfex CRM. Invoices, vendor bills, payments, bank reconciliation, and 8 financial reports with Kenya COA seed data.
Version: 1.0.0
Requires at least: 2.3.*
Author: Xetuu Limited
Author URI: https://xetuu.com
*/

define('XB_MODULE_NAME', 'xetuu_books');

register_language_files(XB_MODULE_NAME, [XB_MODULE_NAME]);

// ── Hooks ─────────────────────────────────────────────────────────────────────
hooks()->add_action('admin_init',     'xb_module_init_menu_items');
hooks()->add_action('admin_init',     'xb_module_permissions');
hooks()->add_action('app_admin_head', 'xb_inject_head_assets');

// Accounting Sync Hooks
hooks()->add_action('after_invoice_added', 'xb_hook_after_invoice_added');
hooks()->add_action('after_invoice_updated', 'xb_hook_after_invoice_updated');
hooks()->add_action('invoice_marked_as_cancelled', 'xb_hook_invoice_marked_as_cancelled');
hooks()->add_action('after_payment_added', 'xb_hook_after_payment_added');
hooks()->add_action('after_expense_added', 'xb_hook_after_expense_added');
hooks()->add_action('after_expense_updated', 'xb_hook_after_expense_updated');

// Currency Sync: Perfex CRM does not fire hooks after currency changes, so sync is done
// manually via the "Sync with Perfex" button on the Xetuu Books currencies config page,
// and automatically in the save_currency AJAX handler when a rate is saved from Xetuu Books.

// Analytic Account Field Injection (native Perfex forms)
hooks()->add_action('after_admin_invoice_form_total_field',          'xb_inject_analytic_invoice');
hooks()->add_action('after_admin_last_record_payment_form_field',    'xb_inject_analytic_payment');
hooks()->add_action('before_expense_form_template_close',            'xb_inject_analytic_expense');
hooks()->add_action('after_lead_lead_tabs',                          'xb_inject_analytic_lead_tab');
hooks()->add_action('after_lead_tabs_content',                       'xb_inject_analytic_lead');
hooks()->add_action('app_admin_footer',                              'xb_inject_analytic_footer_js');

// Save analytic assignment after native Perfex records are saved
hooks()->add_action('after_invoice_added',   'xb_save_analytic_after_invoice',   10);
hooks()->add_action('after_invoice_updated', 'xb_save_analytic_after_invoice',   10);
hooks()->add_action('after_expense_added',   'xb_save_analytic_after_expense',   10);
hooks()->add_action('after_expense_updated', 'xb_save_analytic_after_expense',   10);
hooks()->add_action('after_payment_added',   'xb_save_analytic_after_payment',   10);

// ── Activation ────────────────────────────────────────────────────────────────
register_activation_hook(XB_MODULE_NAME, 'xb_module_activation_hook');

function xb_module_activation_hook()
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    try {
        $CI = &get_instance();
        require_once __DIR__ . '/install.php';
    } catch (\Throwable $e) {
        die('<h1>Module Activation Error</h1><p>' . $e->getMessage() . '</p><pre>' . $e->getTraceAsString() . '</pre>');
    }
}

// ── Sidebar menu ──────────────────────────────────────────────────────────────
function xb_module_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('xb_view', '', 'view')) { return; }

    $CI->app_menu->add_sidebar_menu_item('xetuu_books', [
        'name'     => 'Xetuu Books',
        'icon'     => 'fa fa-book',
        'href'     => admin_url('xetuu_books'),
        'position' => 5,
    ]);
}

// ── Permissions ───────────────────────────────────────────────────────────────
function xb_module_permissions()
{
    $capabilities = [
        'xb_view'       => ['view'],
        'xb_invoices'   => ['view', 'create', 'edit', 'delete'],
        'xb_payments'   => ['view', 'create'],
        'xb_journals'   => ['view', 'create', 'edit', 'delete'],
        'xb_reports'    => ['view'],
        'xb_config'     => ['view', 'edit'],
    ];

    foreach ($capabilities as $name => $perms) {
        register_staff_capabilities($name, ['view', 'create', 'edit', 'delete'], _l($name));
    }
}

// ── Inject CSS/JS only on Xetuu Books pages ──────────────────────────────────
function xb_inject_head_assets()
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, 'admin/xetuu_books') !== false) {
        echo '<style>
            .xb-workspace { background-color: #f9fafb; font-family: "Inter", sans-serif; overflow-x: hidden; }
            .xb-workspace select.form-control { appearance: auto; -webkit-appearance: auto; padding-right: 25px; }
            /* Hide CRM default paddings */
            #wrapper { min-height: 100%; }
            .content { padding: 0 !important; }
            /* Accounting Nav CSS */
            .acc-nav { display: flex; align-items: center; background: #1a6b3a; border-bottom: 1px solid #15803d; padding: 0 20px; height: 60px; font-family: "Inter", sans-serif; }
            .acc-nav-brand { display: flex; align-items: center; text-decoration: none; color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: -0.3px; }
            .acc-nav-brand svg { width: 24px; height: 24px; fill: currentColor; margin-right: 8px; }
            .acc-nav-separator { width: 1px; height: 30px; background: #15803d; margin: 0 20px; }
            .acc-nav-items { display: flex; align-items: center; flex: 1; height: 100%; }
            .acc-nav-item { position: relative; height: 100%; display: flex; align-items: center; margin-right: 10px; cursor: pointer; }
            .acc-nav-item > a, .acc-nav-label { color: #dcfce7; text-decoration: none; font-weight: 500; font-size: 14px; padding: 8px 12px; border-radius: 6px; display: flex; align-items: center; transition: all 0.2s; }
            .acc-nav-item:hover > a, .acc-nav-item:hover .acc-nav-label { background: #15803d; color: #ffffff; }
            .acc-nav-caret { width: 16px; height: 16px; fill: currentColor; margin-left: 4px; opacity: 0.7; }
            .acc-dropdown { position: absolute; top: 100%; left: 0; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); min-width: 240px; padding: 8px 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.2s; z-index: 1000; }
            .acc-nav-item:hover .acc-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
            .acc-dropdown-group-label { display: block; padding: 8px 16px 4px; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #9ca3af; letter-spacing: 0.05em; }
            .acc-dropdown-item { display: flex; align-items: center; padding: 8px 16px; color: #374151; text-decoration: none; font-size: 14px; transition: background 0.15s; }
            .acc-dropdown-item:hover { background: #f9fafb; color: #1a6b3a; }
            .acc-item-icon { width: 18px; height: 18px; fill: currentColor; margin-right: 12px; opacity: 0.6; }
            .acc-dropdown-item:hover .acc-item-icon { opacity: 1; color: #1a6b3a; }
            .acc-dropdown-separator { height: 1px; background: #f3f4f6; margin: 8px 0; }
            /* Mega Menu */
            .acc-nav-mega .acc-dropdown { display: flex; width: max-content; padding: 16px; }
            .acc-mega-col { padding: 0 16px; border-right: 1px solid #f3f4f6; min-width: 200px; }
            .acc-mega-col:last-child { border-right: none; }
            .acc-mega-col-header { font-weight: 600; color: #111827; font-size: 14px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #f3f4f6; }
            .acc-mega-sub-header { font-size: 12px; font-weight: 600; color: #6b7280; margin: 12px 0 4px; text-transform: uppercase; }
            .acc-mega-item { display: block; padding: 6px 0; color: #4b5563; text-decoration: none; font-size: 14px; }
            .acc-mega-item:hover { color: #1a6b3a; text-decoration: none; }
            .acc-nav-config .acc-dropdown { right: 0; left: auto; }
            /* Buttons */
            .acc-btn-new { background: #ffffff; color: #1a6b3a; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
            .acc-btn-new:hover { background: #f0fdf4; color: #15803d; }
            /* Fallback layout wrappers */
            .xb-page-header { background: white; padding: 14px 30px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
            .xb-page-title { font-size: 19px; font-weight: 700; color: #111827; margin: 0; }
            .xb-content-wrapper { padding: 30px; }
            .xb-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); border: 1px solid #e5e7eb; margin-bottom: 20px; }
            .xb-card-header { padding: 14px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151; }
            .xb-card-body { padding: 20px; }
            /* ── List page (invoices, bills, journals, etc.) ─────────────── */
            .xb-list-page { background: #fff; }
            .xb-list-title { font-size: 22px; font-weight: 700; color: #111827; margin: 0; }
            /* Top-right stat badges */
            .xb-list-topbar-stats { display: flex; align-items: center; gap: 8px; }
            .xb-topbar-stat-badge { display: flex; flex-direction: column; align-items: center; padding: 6px 14px; border-radius: 6px; border: 1px solid #e5e7eb; font-size: 12px; min-width: 110px; }
            .xb-stat-green { background: #f0fdf4; border-color: #bbf7d0; }
            .xb-stat-red   { background: #fef2f2; border-color: #fecaca; }
            .xb-stat-yellow{ background: #fffbeb; border-color: #fde68a; }
            .xb-topbar-stat-label { font-size: 11px; color: #6b7280; font-weight: 500; white-space: nowrap; }
            .xb-stat-green .xb-topbar-stat-value { color: #16a34a; font-weight: 700; font-size: 14px; }
            .xb-stat-red   .xb-topbar-stat-value { color: #dc2626; font-weight: 700; font-size: 14px; }
            .xb-stat-yellow.xb-topbar-stat-value { color: #d97706; font-weight: 700; font-size: 14px; }
            /* Summary cards */
            .xb-invoice-summary-container { display: flex; gap: 0; border: 1px solid #e5e7eb; border-radius: 0; margin-bottom: 0; overflow: hidden; }
            .xb-summary-card { flex: 1; padding: 14px 20px; border-right: 1px solid #e5e7eb; background: #fff; }
            .xb-summary-card:last-child { border-right: none; }
            .xb-summary-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
            .xb-summary-card-title { font-size: 13px; font-weight: 600; color: #374151; }
            .xb-summary-card-percent { font-size: 12px; color: #9ca3af; }
            .xb-summary-card-value { font-size: 22px; font-weight: 700; color: #111827; }
            .xb-summary-unpaid  { border-top: 3px solid #dc2626; }
            .xb-summary-paid    { border-top: 3px solid #16a34a; }
            .xb-summary-partial { border-top: 3px solid #d97706; }
            .xb-summary-overdue { border-top: 3px solid #7c3aed; }
            .xb-summary-draft   { border-top: 3px solid #9ca3af; }
            /* Action bar */
            .xb-list-actionbar { display: flex; align-items: center; background: #fff; padding: 0 16px; min-height: 44px; }
            .xb-list-actions-left  { display: flex; align-items: center; }
            .xb-list-actions-right { display: flex; align-items: center; padding: 6px 0; }
            .xb-list-filters { display: flex; align-items: center; gap: 4px; font-size: 13px; color: #374151; flex-wrap: wrap; }
            .xb-list-filters span { padding: 6px 10px; cursor: pointer; border-radius: 4px; white-space: nowrap; }
            .xb-list-filters span:hover { background: #f3f4f6; }
            .xb-list-pagination { display: flex; align-items: center; gap: 8px; }
            .xb-list-search { position: relative; display: flex; align-items: center; }
            .xb-list-search input { border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 30px 6px 10px; font-size: 13px; outline: none; width: 100%; }
            .xb-list-search svg { position: absolute; right: 8px; fill: #9ca3af; pointer-events: none; }
            .xb-list-pager { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280; }
            .xb-list-pager span { cursor: pointer; padding: 2px 6px; border: 1px solid #e5e7eb; border-radius: 3px; }
            /* NEW button */
            .xb-btn-list-primary { display: inline-flex; align-items: center; background: #1a6b3a; color: #fff; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; white-space: nowrap; }
            .xb-btn-list-primary:hover { background: #15803d; color: #fff; text-decoration: none; }
            .xb-btn-list-primary.large { padding: 8px 20px; font-size: 14px; letter-spacing: .03em; }
            /* Table */
            .xb-list-table-container { overflow-x: auto; background: #fff; }
            .xb-list-table { width: 100%; border-collapse: collapse; font-size: 13px; }
            .xb-list-table thead th { padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
            .xb-list-table thead th.num { text-align: right; }
            .xb-list-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.1s; }
            .xb-list-table tbody tr:hover { background: #f9fafb; }
            .xb-list-table tbody td { padding: 10px 12px; color: #374151; vertical-align: middle; }
            .xb-list-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
            .xb-list-table .col-sticky { position: sticky; right: 0; background: #fff; }
            .xb-list-table tbody tr:hover .col-sticky { background: #f9fafb; }
            .xb-list-checkbox { width: 16px; height: 16px; border: 1px solid #d1d5db; border-radius: 3px; cursor: pointer; }
            .xb-list-overdue { color: #dc2626; font-weight: 500; }
            /* Email icon pill */
            .xb-list-email-icon { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #9ca3af; padding: 2px 8px; border-radius: 12px; background: #f3f4f6; }
            .xb-list-email-icon svg { fill: currentColor; width: 14px; height: 14px; }
            .xb-list-email-icon.success { color: #16a34a; background: #f0fdf4; }
            /* Status badges */
            .xb-list-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; white-space: nowrap; }
            .xb-list-badge-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
            .xb-list-badge-red   { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
            .xb-list-badge-gray  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
        </style>';
    }
}

// ── Analytic Field Injection Helpers ─────────────────────────────────────────

function xb_analytic_table_exists($table)
{
    $CI = &get_instance();
    $r  = $CI->db->query(
        "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . db_prefix() . $table . "'"
    )->row();
    return $r && (int)$r->cnt > 0;
}

function xb_get_analytic_accounts_for_injection()
{
    if (!xb_analytic_table_exists('acc_analytic_accounts')) { return []; }
    $CI = &get_instance();
    return $CI->db->select('id, complete_name, name, code, plan_id')
        ->from(db_prefix() . 'acc_analytic_accounts')
        ->where('active', 1)
        ->order_by('complete_name', 'ASC')
        ->get()->result();
}

function xb_get_analytic_assignment_for($form_type, $record_id)
{
    if (!$record_id) { return null; }
    if (!xb_analytic_table_exists('acc_analytic_assignments')) { return null; }
    $CI = &get_instance();
    return $CI->db->get_where(db_prefix() . 'acc_analytic_assignments', [
        'form_type' => $form_type, 'record_id' => (int)$record_id,
    ])->row();
}

function xb_save_analytic_assignment($form_type, $record_id, $account_id)
{
    if (!$form_type || !$record_id || !$account_id) { return; }
    if (!xb_analytic_table_exists('acc_analytic_assignments')) { return; }
    $CI = &get_instance();
    $CI->db->delete(db_prefix() . 'acc_analytic_assignments', [
        'form_type' => $form_type, 'record_id' => (int)$record_id,
    ]);
    $CI->db->insert(db_prefix() . 'acc_analytic_assignments', [
        'form_type'           => $form_type,
        'record_id'           => (int)$record_id,
        'analytic_account_id' => (int)$account_id,
    ]);
}

function xb_render_analytic_field($form_type, $record_id, $label = 'Analytic Account')
{
    $accounts   = xb_get_analytic_accounts_for_injection();
    $assignment = xb_get_analytic_assignment_for($form_type, $record_id);
    $current_id = $assignment ? (int)$assignment->analytic_account_id : 0;
    $save_url   = admin_url('xetuu_books/save_analytic_assignment');

    ob_start();
    ?>
    <div class="xb-analytic-field-wrap" style="margin-top:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;">
        <label style="font-size:12px;font-weight:600;color:#166534;margin-bottom:5px;display:block;">
            <i class="fa fa-pie-chart" style="margin-right:5px;"></i><?php echo e($label); ?>
        </label>
        <div style="display:flex;align-items:center;gap:8px;">
            <select name="xb_analytic_account_id"
                    class="form-control xb-analytic-select"
                    data-form-type="<?php echo e($form_type); ?>"
                    data-record-id="<?php echo (int)$record_id; ?>"
                    data-save-url="<?php echo e($save_url); ?>"
                    style="max-width:360px;">
                <option value="">— None —</option>
                <?php foreach ($accounts as $acc): ?>
                <option value="<?php echo $acc->id; ?>" <?php echo $current_id == $acc->id ? 'selected' : ''; ?>>
                    <?php echo e($acc->complete_name ?: $acc->name); ?>
                    <?php echo $acc->code ? ' [' . e($acc->code) . ']' : ''; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($current_id): ?>
            <span class="xb-analytic-saved-badge" style="font-size:11px;color:#16a34a;"><i class="fa fa-check-circle"></i> Saved</span>
            <?php else: ?>
            <span class="xb-analytic-saved-badge" style="display:none;font-size:11px;color:#16a34a;"><i class="fa fa-check-circle"></i> Saved</span>
            <?php endif; ?>
        </div>
        <p style="font-size:11px;color:#6b7280;margin-top:4px;margin-bottom:0;">
            Track this document's spend against a cost centre or project.
        </p>
    </div>
    <script>
    (function(){
        var wrap = document.querySelector('.xb-analytic-field-wrap:last-of-type');
        if (!wrap) return;
        var sel = wrap.querySelector('.xb-analytic-select');
        var badge = wrap.querySelector('.xb-analytic-saved-badge');
        if (!sel) return;

        // For new records (no record_id yet), the form submit will carry xb_analytic_account_id via POST
        // For existing records with an ID, also auto-save via AJAX for immediate feedback
        sel.addEventListener('change', function() {
            var recId = parseInt(sel.dataset.recordId);
            if (recId > 0) {
                // AJAX save for existing records
                var fd = new FormData();
                fd.append('form_type', sel.dataset.formType);
                fd.append('record_id', recId);
                fd.append('account_id', sel.value);
                fetch(sel.dataset.saveUrl, { method: 'POST', body: fd })
                    .then(function(r){ return r.json(); })
                    .then(function(d){
                        if (d.success) {
                            badge.style.display = '';
                            setTimeout(function(){ badge.style.display = 'none'; }, 2000);
                        }
                    });
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ── PHP Hook Injection Callbacks ──────────────────────────────────────────────

function xb_inject_analytic_invoice($invoice)
{
    $CI = &get_instance();
    // Do not render if we are on the Xetuu Books invoice or receipt form (since it's already at the top)
    if ($CI->uri->segment(2) == 'xetuu_books' && in_array($CI->uri->segment(3), ['invoice_form', 'receipt', 'vendor_receipt'])) {
        return;
    }

    $record_id = isset($invoice) && isset($invoice->id) ? (int)$invoice->id : 0;
    echo xb_render_analytic_field('invoice', $record_id, 'Analytic Account (Cost Centre)');
}

function xb_inject_analytic_payment($invoice)
{
    // $invoice here is the invoice the payment is being recorded for
    $record_id = isset($invoice) && isset($invoice->id) ? (int)$invoice->id : 0;
    echo xb_render_analytic_field('payment', $record_id, 'Analytic Account');
}

function xb_inject_analytic_expense($expense)
{
    $record_id = isset($expense) && isset($expense->id) ? (int)$expense->id : 0;
    echo xb_render_analytic_field('expense', $record_id, 'Analytic Account (Cost Centre)');
}

function xb_inject_analytic_lead_tab($lead)
{
    echo '<li role="presentation"><a href="#tab_xb_analytic" aria-controls="tab_xb_analytic" role="tab" data-toggle="tab"><i class="fa fa-pie-chart" style="margin-right:4px;"></i>Analytic</a></li>';
}

function xb_inject_analytic_lead($lead)
{
    $record_id = isset($lead) && isset($lead->id) ? (int)$lead->id : 0;
    $html = xb_render_analytic_field('lead', $record_id, 'Analytic Account (Cost Centre)');
    echo '<div class="tab-pane" id="tab_xb_analytic" style="padding:15px;">' . $html . '</div>';
}

// ── Global JS injection for purchase module and credit notes (no PHP hooks) ──

function xb_inject_analytic_footer_js()
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    // Only inject on relevant pages
    $inject_pages = [
        'purchase/purchase_order',
        'purchase/debit_notes',
        'purchase/invoices',
        'admin/credit_notes',
    ];

    $should_inject = false;
    foreach ($inject_pages as $page) {
        if (strpos($uri, $page) !== false) {
            $should_inject = true;
            break;
        }
    }

    if (!$should_inject) { return; }

    $accounts   = xb_get_analytic_accounts_for_injection();
    $save_url   = admin_url('xetuu_books/save_analytic_assignment');
    $get_url    = admin_url('xetuu_books/get_analytic_assignment');

    $options_html = '<option value="">— None —</option>';
    foreach ($accounts as $acc) {
        $label = htmlspecialchars($acc->complete_name ?: $acc->name);
        if ($acc->code) { $label .= ' [' . htmlspecialchars($acc->code) . ']'; }
        $options_html .= "<option value=\"{$acc->id}\">{$label}</option>";
    }

    ?>
    <script>
    (function() {
        var SAVE_URL = <?php echo json_encode($save_url); ?>;
        var GET_URL  = <?php echo json_encode($get_url); ?>;
        var OPTIONS  = <?php echo json_encode($options_html); ?>;
        var URI = window.location.pathname + window.location.search;

        // Detect form type and record ID from URL
        var formType = null, recordId = 0;

        if (URI.indexOf('purchase_order') !== -1) {
            formType = 'purchase_order';
            var m = URI.match(/purchase_order[\/\?&](?:edit\/)?(\d+)/);
            if (m) recordId = parseInt(m[1]);
        } else if (URI.indexOf('debit_notes') !== -1) {
            formType = 'debit_note';
            var m = URI.match(/debit_notes[\/\?&](?:edit\/)?(\d+)/);
            if (m) recordId = parseInt(m[1]);
        } else if (URI.indexOf('purchase/invoices') !== -1) {
            formType = 'purchase_invoice';
            var m = URI.match(/invoices[\/\?&](?:edit\/)?(\d+)/);
            if (m) recordId = parseInt(m[1]);
        } else if (URI.indexOf('credit_notes') !== -1) {
            formType = 'credit_note';
            var m = URI.match(/credit_notes[\/\?&](?:edit\/)?(\d+)/);
            if (m) recordId = parseInt(m[1]);
        }

        if (!formType) return;

        function buildWidget(currentId) {
            var sel = '<select id="xb_global_analytic_select" style="width:100%;max-width:360px;" class="form-control">' + OPTIONS + '</select>';
            var html = '<div id="xb-global-analytic-wrap" style="margin:14px 0;padding:12px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;">' +
                '<label style="font-size:12px;font-weight:600;color:#166534;margin-bottom:6px;display:block;">' +
                '<i class="fa fa-pie-chart" style="margin-right:5px;"></i>Analytic Account (Cost Centre)</label>' +
                sel +
                '<p style="font-size:11px;color:#6b7280;margin:5px 0 0;">Track this document\'s spend against a cost centre or project.</p>' +
                '<span id="xb-analytic-saved" style="display:none;font-size:11px;color:#16a34a;margin-top:4px;display:block;"><i class="fa fa-check-circle"></i> Saved</span>' +
                '</div>';
            return html;
        }

        function injectWidget(currentId) {
            if (document.getElementById('xb-global-analytic-wrap')) return;
            if (document.querySelector('.xb-analytic-field-wrap')) return;

            var html = buildWidget(currentId);
            var $target = null;

            // Try various insertion points by form type
            if (formType === 'credit_note') {
                $target = jQuery('.credit_note.accounting-template .panel-body').first();
                if ($target.length) {
                    $target.append(html);
                }
            } else {
                // Purchase forms — find the submit button area
                var selectors = ['[name="add_purchase_order"]','[name="add_debit_note"]','[name="add_invoice"]','form .submit-btn','form [type="submit"]'];
                for (var i = 0; i < selectors.length; i++) {
                    $target = jQuery(selectors[i]).closest('form');
                    if ($target.length) {
                        $target.find('[type="submit"]').first().closest('.row,.form-group,.col-md-12').first().before(html);
                        break;
                    }
                }
                // Fallback: append to first form
                if (!$target || !$target.length) {
                    jQuery('form').first().append(html);
                }
            }

            // Set current value
            if (currentId) {
                jQuery('#xb_global_analytic_select').val(currentId);
            }

            // Wire up change handler
            jQuery('#xb_global_analytic_select').on('change', function() {
                var accId = jQuery(this).val();
                var $saved = jQuery('#xb-analytic-saved');

                if (recordId > 0) {
                    // AJAX save immediately for existing records
                    jQuery.post(SAVE_URL, {
                        form_type: formType,
                        record_id: recordId,
                        account_id: accId
                    }, function(resp) {
                        if (resp.success) {
                            $saved.show();
                            setTimeout(function(){ $saved.hide(); }, 2000);
                        }
                    }, 'json');
                }
                // For new records the select name will carry the value via form POST
                // (purchase forms may or may not pick it up — AJAX save handles existing ones)
            });
        }

        function init() {
            if (recordId > 0) {
                // Load existing assignment
                jQuery.get(GET_URL, { form_type: formType, record_id: recordId }, function(resp) {
                    var currentId = (resp.success && resp.data) ? resp.data.analytic_account_id : 0;
                    injectWidget(currentId);
                }, 'json').fail(function(){ injectWidget(0); });
            } else {
                injectWidget(0);
            }
        }

        // Run after DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            setTimeout(init, 300);
        }
    })();
    </script>
    <?php
}

// ── Save Analytic Assignments from POST (native Perfex form submissions) ──────

function xb_save_analytic_after_invoice($invoice_id)
{
    $CI = &get_instance();
    $account_id = (int)$CI->input->post('xb_analytic_account_id');
    if ($account_id) {
        xb_save_analytic_assignment('invoice', $invoice_id, $account_id);
    }
}

function xb_save_analytic_after_expense($expense_id)
{
    $CI = &get_instance();
    $account_id = (int)$CI->input->post('xb_analytic_account_id');
    if ($account_id) {
        xb_save_analytic_assignment('expense', $expense_id, $account_id);
    }
}

function xb_save_analytic_after_payment($payment_id)
{
    $CI = &get_instance();
    $account_id = (int)$CI->input->post('xb_analytic_account_id');
    if ($account_id) {
        xb_save_analytic_assignment('payment', $payment_id, $account_id);
    }
}

hooks()->add_filter('before_payment_recorded', 'xb_remove_analytic_from_data');
hooks()->add_filter('before_invoice_added', 'xb_remove_analytic_from_data');
hooks()->add_filter('before_invoice_updated', 'xb_remove_analytic_from_data');
hooks()->add_filter('before_expense_added', 'xb_remove_analytic_from_data');
hooks()->add_filter('before_expense_updated', 'xb_remove_analytic_from_data');

function xb_remove_analytic_from_data($data) {
    if (isset($data['xb_analytic_account_id'])) {
        unset($data['xb_analytic_account_id']);
    }
    return $data;
}

// ── Currency Sync Hook ────────────────────────────────────────────────────────

/**
 * Called when a currency is added or updated in Perfex native (/admin/currencies).
 * Syncs the change into tblacc_currencies so both tables stay in step.
 *
 * @param int $currency_id  The Perfex tblcurrencies.id that was added or updated
 */
function xb_hook_after_currency_changed($currency_id)
{
    $CI = &get_instance();
    // Guard: table must exist (module may not be fully installed yet)
    $tbl = db_prefix() . 'acc_currencies';
    $check = $CI->db->query(
        "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $tbl . "'"
    )->row();
    if (!$check || (int)$check->cnt === 0) { return; }

    $CI->load->model('xetuu_books/Xb_config_model', 'xb_config');
    $CI->xb_config->sync_perfex_currency_update((int)$currency_id);
}

// ── Sync Hook Callbacks ───────────────────────────────────────────────────────
function xb_hook_after_invoice_added($invoice_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->sync_invoice_to_journal($invoice_id);
}
function xb_hook_after_invoice_updated($invoice_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->sync_invoice_to_journal($invoice_id);
}
function xb_hook_invoice_marked_as_cancelled($invoice_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->cancel_invoice_journal($invoice_id);
}
function xb_hook_after_payment_added($payment_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->sync_payment_to_journal($payment_id);
}
function xb_hook_after_expense_added($expense_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->sync_expense_to_journal($expense_id);
}
function xb_hook_after_expense_updated($expense_id) {
    $CI = &get_instance();
    $CI->load->model('xetuu_books/xb_engine_model');
    $CI->xb_engine_model->sync_expense_to_journal($expense_id);
}
