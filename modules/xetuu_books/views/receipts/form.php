<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ── Page shell ─────────────────────────────────────────────── */
.xb-content-wrapper { padding: 0 !important; }
.xb-inv-page { display: flex; flex-direction: column; min-height: calc(100vh - 60px); background: #f3f4f6; }

/* ── Top header bar ─────────────────────────────────────────── */
.xb-inv-header {
    background: #fff;
    padding: 14px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.xb-inv-header-left { display: flex; align-items: center; gap: 10px; }
.xb-inv-back-btn {
    display: flex; align-items: center; gap: 6px;
    color: #6b7280; font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 10px; border-radius: 6px;
    border: 1px solid #e5e7eb; background: #fff; transition: all .15s;
}
.xb-inv-back-btn:hover { background: #f9fafb; color: #374151; text-decoration: none; }
.xb-inv-header-title { display: flex; flex-direction: column; gap: 1px; }
.xb-inv-breadcrumb { font-size: 11px; color: #9ca3af; }
.xb-inv-breadcrumb a { color: #9ca3af; text-decoration: none; }
.xb-inv-breadcrumb a:hover { color: #1a6b3a; }
.xb-inv-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px; }
.xb-inv-header-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.xb-btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #1a6b3a; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.xb-btn-primary:hover { background: #155a30; color: #fff; }
.xb-btn-outline {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 7px;
    padding: 8px 14px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s;
}
.xb-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; color: #111827; text-decoration: none; }

/* ── Status badge ───────────────────────────────────────────── */
.xb-inv-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    background: #f3f4f6; color: #6b7280;
}
.xb-inv-badge.paid     { background: #dcfce7; color: #15803d; }
.xb-inv-badge.draft    { background: #f3f4f6; color: #6b7280; }

/* ── KPI stats strip ────────────────────────────────────────── */
.xb-inv-stats-strip {
    background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 24px;
    display: flex; align-items: stretch; gap: 0; overflow-x: auto;
}
.xb-inv-stat {
    display: flex; flex-direction: column; justify-content: center;
    padding: 12px 24px 12px 0; margin-right: 24px; border-right: 1px solid #f0f0f0; gap: 2px; min-width: 120px; flex-shrink: 0;
}
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

/* ── Invoice form overrides ─────────────────────────────────── */
.xb-inv-main .panel_s { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.xb-inv-main .panel_s .panel-body { padding: 20px; }

/* ── Main content layout ────────────────────────────────────── */
.xb-inv-body { display: flex; align-items: flex-start; flex: 1; min-height: 0; }
.xb-inv-main { flex: 1; min-width: 0; padding: 20px 24px; }

/* ── Right sidebar ──────────────────────────────────────────── */
.xb-inv-sidebar {
    width: 280px; flex-shrink: 0; background: #fff; border-left: 1px solid #e5e7eb;
    padding: 16px; min-height: calc(100vh - 140px); display: flex; flex-direction: column; gap: 14px;
}
.xb-sw { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.xb-sw-head { background: #f9fafb; padding: 12px 14px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; }
.xb-sw-title { font-size: 12px; font-weight: 700; color: #374151; margin: 0; text-transform: uppercase; letter-spacing: .05em; }
.xb-sw-body { padding: 14px; background: #fff; }

/* Sticky bottom toolbar */
.btn-bottom-toolbar {
    position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
    padding: 12px 24px; border-top: 1px solid #e5e7eb; display: flex;
    justify-content: flex-end; gap: 10px; z-index: 50; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
}
.btn-bottom-toolbar.hide { display: none !important; }

/* Hide unnecessary fields for receipts */
.xb-inv-main [app-field-wrapper="duedate"] { display: none !important; }
.xb-inv-main [app-field-wrapper="allowed_payment_modes[]"] { display: none !important; }
.xb-inv-main .form-group[data-title*="create_recurring_from_child"] { display: none !important; }
.xb-inv-main .recurring-cycles { display: none !important; }
</style>

<!-- Hidden elements to move into Perfex DOM via JS -->
<div id="xb-custom-journal-field" style="display:none;">
    <div class="form-group select-placeholder">
        <label for="payment_journal_id" class="control-label">Payment Journal *</label>
        <select name="payment_journal_id" id="payment_journal_id" class="selectpicker" data-width="100%" required>
            <?php foreach($payment_journals as $j): ?>
                <option value="<?php echo $j->id; ?>"><?php echo htmlspecialchars($j->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php 
$is_vendor = ($move_type === 'in_receipt');
$list_url  = admin_url('xetuu_books/' . ($is_vendor ? 'vendor_receipts' : 'receipts'));
$title_lbl = $is_vendor ? 'Purchase Receipt' : 'Sales Receipt';
$list_lbl  = $is_vendor ? 'Purchase Receipts' : 'Sales Receipts';
?>

<div class="xb-inv-page">
    
    <!-- Top Header Bar -->
    <div class="xb-inv-header">
        <div class="xb-inv-header-left">
            <a href="<?= $list_url; ?>" class="xb-inv-back-btn">
                <i class="fa fa-arrow-left"></i> <?= _l('back'); ?>
            </a>
            <div class="xb-inv-header-title">
                <div class="xb-inv-breadcrumb">
                    <a href="<?= admin_url('xetuu_books/dashboard'); ?>">Xetuu Books</a> &rsaquo; 
                    <a href="<?= $list_url; ?>"><?= $list_lbl; ?></a>
                </div>
                <h1 class="xb-inv-title">
                    <?= isset($move) ? e($move->name ?: 'DRAFT') : ('New ' . $title_lbl); ?>
                    <?php if (isset($move)): ?>
                        <span class="xb-inv-badge paid"><i class="fa fa-check-circle"></i> Paid</span>
                    <?php else: ?>
                        <span class="xb-inv-badge draft"><i class="fa fa-pencil"></i> Draft</span>
                    <?php endif; ?>
                </h1>
            </div>
        </div>

        <div class="xb-inv-header-actions">
            <?php if (isset($move)): ?>
                <a href="<?= admin_url('invoices/pdf/' . $move->id . '?print=true'); ?>" target="_blank" class="xb-btn-outline">
                    <i class="fa fa-print"></i> <?= _l('print'); ?>
                </a>
                <a href="<?= admin_url('invoices/pdf/' . $move->id); ?>" class="xb-btn-outline">
                    <i class="fa fa-file-pdf-o"></i> <?= _l('view_pdf'); ?>
                </a>
            <?php endif; ?>
            
            <button type="button" onclick="$('#invoice-form').submit();" class="xb-btn-primary">
                <i class="fa fa-floppy-o"></i> <?= isset($move) ? _l('save') : _l('save'); ?>
            </button>
            <button type="button" onclick="$('#invoice-form').append('<input type=\'hidden\' name=\'save_and_send_later\' value=\'1\'>'); $('#invoice-form').submit();" class="xb-btn-primary" style="background: #3b82f6;">
                <i class="fa fa-paper-plane"></i> Save & Send
            </button>
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

    <!-- Main Content & Sidebar -->
    <div class="xb-inv-body">
        
        <!-- Main Form Area -->
        <div class="xb-inv-main">
            <?php echo form_open($this->uri->uri_string(), ['id' => 'invoice-form', 'class' => '_transaction_form invoice-form']); ?>
            <?php if (isset($move)) { echo form_hidden('isedit', 'true'); } ?>
            
            <?php 
            ob_start();
            $this->load->view('admin/invoices/invoice_template', ['invoice' => $move ?? null]); 
            $html = ob_get_clean();
            
            // Strip out ajax-search from the customer select so Perfex CRM's main.js ignores it entirely
            $html = str_replace('id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search', 'id="clientid" name="clientid" data-width="100%" class="', $html);
            
            echo $html;
            ?>

            <!-- We are inside form, so we must add our sticky toolbar inside the form to capture submits properly -->
            <div class="btn-bottom-toolbar text-right">
                <button class="btn btn-default" type="button" onclick="window.location.href='<?= $list_url; ?>'"><?= _l('cancel'); ?></button>
                <button class="btn btn-primary" type="button" onclick="$('#invoice-form').append('<input type=\'hidden\' name=\'save_and_send_later\' value=\'1\'>'); $('#invoice-form').submit();">Save & Send</button>
                <button class="btn btn-info" type="submit"><?= _l('submit'); ?></button>
            </div>
            
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>

        <!-- Right Sidebar -->
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
                    if (!isset($move)) {
                        $stamp_color = '#6b7280'; // Gray for Draft
                    }
                    $stamp_status = isset($move) ? 'PAID' : 'DRAFT';
                    $stamp_text = get_option('invoice_company_name'); 
                    if(empty($stamp_text)) $stamp_text = 'COMPANY NAME';
                    ?>
                    
                    <div style="position:relative; width:160px; height:160px; border:4px solid <?= $stamp_color; ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; transform:rotate(-15deg); opacity:0.8; padding:10px;">
                        
                        <div style="position:absolute; inset:5px; border:1px solid <?= $stamp_color; ?>; border-radius:50%;"></div>
                        
                        <!-- Circular Text SVG -->
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

                        <!-- Center Content -->
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
            
        </div>
        <!-- End Right Sidebar -->

    </div>

</div>

<!-- Customer picker scripts from original receipt form -->
<style>
/* ── Xetuu Books Customer Picker ──────────────────────────── */
.xb-cp-wrap { position: relative; }
.xb-cp-trigger {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 12px; border: 1px solid #ccd0d8; border-radius: 4px;
    background: #fff; cursor: pointer; min-height: 34px;
    transition: border-color .15s, box-shadow .15s;
}
.xb-cp-trigger:hover, .xb-cp-wrap.open .xb-cp-trigger {
    border-color: #1a6b3a; box-shadow: 0 0 0 3px rgba(26,107,58,.1);
}
.xb-cp-display { flex: 1; font-size: 14px; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.xb-cp-display.placeholder { color: #aaa; }
.xb-cp-icons { display: flex; align-items: center; gap: 4px; margin-left: 8px; flex-shrink: 0; }
.xb-cp-clear-btn {
    display: none; width: 18px; height: 18px; border-radius: 50%; background: #d1d5db;
    color: #6b7280; font-size: 11px; align-items: center; justify-content: center;
    line-height: 1; cursor: pointer; border: none; padding: 0; transition: background .15s;
}
.xb-cp-clear-btn:hover { background: #ef4444; color: #fff; }
.xb-cp-wrap.has-val .xb-cp-clear-btn { display: flex; }
.xb-cp-caret { color: #9ca3af; font-size: 10px; transition: transform .2s; }
.xb-cp-wrap.open .xb-cp-caret { transform: rotate(180deg); }

.xb-cp-drop {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
    box-shadow: 0 12px 28px rgba(0,0,0,.13); z-index: 9999; overflow: hidden;
}
.xb-cp-wrap.open .xb-cp-drop { display: block; }

.xb-cp-search-row { padding: 8px 8px 6px; border-bottom: 1px solid #f3f4f6; background: #fafafa; }
.xb-cp-search-row input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 5px;
    padding: 6px 10px 6px 32px; font-size: 13px; outline: none; background: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='%23aaa'%3E%3Cpath d='M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: 10px center;
}
.xb-cp-search-row input:focus { border-color: #1a6b3a; }

.xb-cp-list { max-height: 210px; overflow-y: auto; }
.xb-cp-item {
    display: flex; align-items: center; padding: 8px 14px;
    font-size: 13px; color: #374151; cursor: pointer;
    transition: background .1s; gap: 6px;
}
.xb-cp-item:hover { background: #f0fdf4; }
.xb-cp-item.xb-cp-sel { background: #dcfce7; font-weight: 600; color: #166534; }
.xb-cp-item-email { font-size: 11px; color: #9ca3af; margin-left: auto; }
.xb-cp-item.xb-cp-sel .xb-cp-item-email { color: #6ee7b7; }
.xb-cp-check { color: #16a34a; font-size: 11px; margin-left: auto; }
.xb-cp-empty { padding: 14px; text-align: center; font-size: 13px; color: #aaa; display: none; }
.xb-cp-footer { border-top: 1px solid #f0f0f0; }
.xb-cp-create-btn {
    display: flex; align-items: center; gap: 8px; width: 100%; border: none; background: none;
    padding: 9px 14px; font-size: 13px; font-weight: 600; color: #16a34a; cursor: pointer;
    transition: background .15s;
}
.xb-cp-create-btn:hover { background: #f0fdf4; }
.xb-cp-create-btn .xb-spin {
    display: none; width: 14px; height: 14px; border: 2px solid #a7f3d0;
    border-top-color: #16a34a; border-radius: 50%;
    animation: xbspin .6s linear infinite;
}
.xb-cp-create-btn.xb-creating .xb-spin { display: inline-block; }
.xb-cp-create-btn.xb-creating .xb-plus-icon { display: none; }
@keyframes xbspin { to { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inject the payment journal dropdown where duedate used to be
    var $dueDateParent = $('[app-field-wrapper="duedate"]').parent();
    if ($dueDateParent.length) {
        $dueDateParent.html($('#xb-custom-journal-field').html());
        $('.selectpicker').selectpicker('refresh');
    }

    try { xbInitCustomerPicker(); } catch(e) { console.error(e); }

    try { validate_invoice_form(); } catch(e) {}
    try { init_currency(); } catch(e) {}
    try { init_ajax_project_search_by_customer_id(); } catch(e) {}
    try { init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search'); } catch(e) {}
    
    // Move Analytic Account widget above Customer field
    var $analyticContainer = $('#xb-analytic-account-container');
    var $wrapper = $('.xb-sw').has($analyticContainer);
    var $clientGroup = $('.f_client_id');
    if ($clientGroup.length === 0) {
        $clientGroup = $('#clientid').closest('.form-group');
    }
    if ($analyticContainer.length && $clientGroup.length) {
        $analyticContainer.insertBefore($clientGroup);
        $wrapper.remove(); // Remove the old sidebar box completely
    }
});

function xbInitCustomerPicker() {
    var $orig     = $('#clientid');
    var allList   = [];
    var selId     = $orig.val() || '';
    var selName   = selId ? ($orig.find('option:selected').text() || '') : '';

    $orig.removeClass('ajax-search');
    try { $orig.selectpicker('destroy'); } catch(e) {}
    $orig.closest('.form-group').find('.bootstrap-select').remove();

    var $wrap = $('<div class="xb-cp-wrap" tabindex="0"></div>');
    var $trigger = $('<div class="xb-cp-trigger"></div>');
    var $display = $('<div class="xb-cp-display placeholder">Select Customer...</div>');
    var $icons = $('<div class="xb-cp-icons"></div>');
    var $clear = $('<button type="button" class="xb-cp-clear-btn" title="Clear selection"><i class="fa fa-times"></i></button>');
    var $caret = $('<i class="fa fa-chevron-down xb-cp-caret"></i>');
    
    $icons.append($clear).append($caret);
    $trigger.append($display).append($icons);
    
    var $drop = $('<div class="xb-cp-drop"></div>');
    var $searchRow = $('<div class="xb-cp-search-row"><input type="text" placeholder="Search customers..." autocomplete="off"></div>');
    var $searchInput = $searchRow.find('input');
    var $list = $('<div class="xb-cp-list"></div>');
    var $empty = $('<div class="xb-cp-empty">No results found</div>');
    var $footer = $('<div class="xb-cp-footer"></div>');
    var $createBtn = $('<button type="button" class="xb-cp-create-btn"><i class="fa fa-plus xb-plus-icon"></i><div class="xb-spin"></div><span>Create Customer "<strong class="xb-new-name"></strong>"</span></button>');
    
    $footer.append($createBtn).hide();
    $drop.append($searchRow).append($list).append($empty).append($footer);
    $wrap.append($trigger).append($drop);
    
    $orig.after($wrap);
    $orig.hide();
    
    function setVal(id, name) {
        if (!id) {
            $orig.val('').trigger('change');
            $display.text('Select Customer...').addClass('placeholder');
            $wrap.removeClass('has-val');
        } else {
            if ($orig.find('option[value="'+id+'"]').length === 0) {
                $orig.append(new Option(name, id, true, true));
            }
            $orig.val(id).trigger('change');
            $display.text(name).removeClass('placeholder');
            $wrap.addClass('has-val');
        }
        selId = id; selName = name;
        renderList();
    }
    
    if (selId) setVal(selId, selName);
    
    $trigger.on('click', function(e) {
        if ($(e.target).closest('.xb-cp-clear-btn').length) return;
        var isOpen = $wrap.hasClass('open');
        $('.xb-cp-wrap').removeClass('open');
        if (!isOpen) {
            $wrap.addClass('open');
            $searchInput.val('').focus();
            fetchData('');
        }
    });
    
    $clear.on('click', function(e) {
        e.stopPropagation();
        setVal('', '');
        $wrap.removeClass('open');
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest($wrap).length) {
            $wrap.removeClass('open');
        }
    });
    
    var searchTimeout;
    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        var q = $(this).val();
        searchTimeout = setTimeout(function() { fetchData(q); }, 300);
        
        if (q.trim().length > 0) {
            $footer.show();
            $createBtn.find('.xb-new-name').text(q.trim());
        } else {
            $footer.hide();
        }
    });
    
    function renderList() {
        $list.empty();
        if (allList.length === 0) {
            $empty.show();
        } else {
            $empty.hide();
            allList.forEach(function(c) {
                var isSel = (c.userid == selId);
                var $item = $('<div class="xb-cp-item '+(isSel?'xb-cp-sel':'')+'"></div>');
                $item.append('<span>'+c.company+'</span>');
                if (c.email) $item.append('<span class="xb-cp-item-email">'+c.email+'</span>');
                if (isSel) $item.append('<i class="fa fa-check xb-cp-check"></i>');
                
                $item.on('click', function() {
                    setVal(c.userid, c.company);
                    $wrap.removeClass('open');
                });
                $list.append($item);
            });
        }
    }
    
    function fetchData(q) {
        $.post(admin_url + 'misc/get_relation_data', {type: 'customer', q: q}, function(res) {
            allList = [];
            if (res && res.length) {
                res.forEach(function(r) {
                    if (r.id && r.name) {
                        var emailMatch = r.subtext ? r.subtext.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/) : null;
                        var email = emailMatch ? emailMatch[1] : '';
                        allList.push({userid: r.id, company: r.name, email: email});
                    }
                });
            }
            renderList();
        }, 'json');
    }
    
    $createBtn.on('click', function() {
        if ($createBtn.hasClass('xb-creating')) return;
        var name = $searchInput.val().trim();
        if (!name) return;
        
        $createBtn.addClass('xb-creating');
        window.location.href = admin_url + 'clients/client';
    });
}
</script>

<?php
// Fix Analytic widget URL logic since receipts use 'receipts' endpoint instead of 'invoices'
$formTypeForAnalytic = isset($move) ? (($move_type === 'in_receipt') ? 'vendor_receipt' : 'receipt') : (($move_type === 'in_receipt') ? 'vendor_receipt' : 'receipt');
$recordIdForAnalytic = isset($move) ? $move->id : 0;
?>

<script>
(function(){
    var GET_URL = admin_url + 'xetuu_books/get_analytic_assignment';
    var WIDGET_URL = admin_url + 'xetuu_books/render_analytic_widget';
    var recordId = <?php echo $recordIdForAnalytic; ?>;
    var formType = '<?php echo $formTypeForAnalytic; ?>';

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
</script><?php if ($this->session->has_userdata('send_later')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof alert_float === 'function') {
            alert_float('success', 'Receipt saved and marked for sending.');
        }
    });
</script>
<?php $this->session->unset_userdata('send_later'); endif; ?>
