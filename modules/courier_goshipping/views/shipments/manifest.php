<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php
/* ── Company branding ─────────────────────────────────────────────────────── */
// A manifest can span shipments from multiple branches, so there's no single
// "owning" shipment to read branch_id from — use whichever branch the staff
// generating it is currently operating as.
$_ci_mf        = courier_get_invoice_info(courier_get_session_branch_id());
$_mf_company   = $_ci_mf['name'] ?: '';
$_mf_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
$_mf_logo_url  = !empty($_mf_logo_file) ? base_url('uploads/company/' . $_mf_logo_file) : '';
$_mf_address   = $_ci_mf['address'] ?: '';
$_mf_phone     = $_ci_mf['phone']   ?: '';
$_mf_email_co  = $_ci_mf['email']   ?: '';
$_mf_website   = $_ci_mf['website'] ?: '';

/* ── Totals ───────────────────────────────────────────────────────────────── */
$mf_rows          = (array)($manifest_rows ?? []);
$mf_total_qty     = 0;  $mf_total_weight  = 0.0;
$mf_total_charges = 0.0; $mf_total_vat    = 0.0; $mf_total_amount  = 0.0;
foreach ($mf_rows as $_r) {
    $mf_total_qty     += (int)$_r['pkg_qty'];
    $mf_total_weight  += (float)$_r['pkg_weight'];
    $mf_total_charges += (float)$_r['charges'];
    $mf_total_vat     += (float)$_r['vat'];
    $mf_total_amount  += (float)$_r['total'];
}

/* ── Date labels ──────────────────────────────────────────────────────────── */
$mf_from_label = date('d M Y', strtotime($filter_date_from ?? date('Y-m-01')));
$mf_to_label   = date('d M Y', strtotime($filter_date_to   ?? date('Y-m-d')));

/* ── Driver name for header ───────────────────────────────────────────────── */
$mf_driver_label = '';
if (!empty($filter_driver_id)) {
    foreach ((array)($all_drivers ?? []) as $_d) {
        if ((int)($_d['staffid'] ?? 0) === (int)$filter_driver_id) {
            $mf_driver_label = trim(($_d['firstname'] ?? '') . ' ' . ($_d['lastname'] ?? ''));
            break;
        }
    }
}

/* ── Current user ─────────────────────────────────────────────────────────── */
$mf_printed_by = '';
if (function_exists('get_staff_user_id')) {
    $me = $this->db->select('CONCAT(firstname," ",lastname) as full_name')
                   ->where('staffid', get_staff_user_id())
                   ->get(db_prefix() . 'staff')->row();
    $mf_printed_by = $me ? $me->full_name : '';
}

/* ── Template & accent color ──────────────────────────────────────────────── */
$manifest_color  = get_option('courier_manifest_color') ?: '#2e7d32';
$manifest_tpl    = get_option('courier_manifest_template') ?: 'cargo_green';
$_valid_mf_tpls  = ['cargo_green','dubai_air','classic_table','modern_blue','compact_list'];
if (!in_array($manifest_tpl, $_valid_mf_tpls, true)) $manifest_tpl = 'cargo_green';
$_tpl_file = dirname(__FILE__) . '/manifest_templates/' . $manifest_tpl . '.php';
if (!is_file($_tpl_file)) {
    $_tpl_file = dirname(__FILE__) . '/manifest_templates/cargo_green.php';
}
include $_tpl_file;
// Template loaded — stop processing this file.
return;
?>
<style>
/* ═══════════════════════════════════════════════════════════════════════════
   MANIFEST – FULL PAGE STANDALONE STYLES
═══════════════════════════════════════════════════════════════════════════ */
body { background: #f4f6f4; }
#mf-wrapper { max-width: 100%; padding: 0 18px 30px; }

/* ── Top navigation bar ─────────────────────────────────────────────────── */
.mf-topbar {
    background: #fff;
    border-bottom: 2px solid #c8e6c9;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 18px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(46,125,50,.08);
}
.mf-topbar-left  { display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex:1; }
.mf-topbar-right { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.mf-topbar-title {
    font-size: 15px; font-weight: 800; color: #1b5e20;
    letter-spacing: .4px; margin-right: 6px; white-space: nowrap;
}
.mf-topbar-title i { margin-right: 5px; }

/* Buttons */
.mf-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border: none; border-radius: 6px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap;
    transition: opacity .18s, transform .12s;
    line-height: 1;
}
.mf-btn:hover { opacity: .85; text-decoration: none; transform: translateY(-1px); }
.mf-btn:active { transform: translateY(0); }
.mf-btn-back   { background: #546e7a; color: #fff; }
.mf-btn-create { background: linear-gradient(135deg,#2e7d32,#1b5e20); color: #fff; }
.mf-btn-print  { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; }
.mf-btn-csv    { background: linear-gradient(135deg,#00838f,#006064); color: #fff; }
.mf-btn-email  { background: linear-gradient(135deg,#e65100,#bf360c); color: #fff; }

/* ── Filter bar ─────────────────────────────────────────────────────────── */
.mf-filter-bar {
    background: #f1f8e9;
    border: 1.5px solid #aed581;
    border-radius: 8px;
    padding: 12px 16px 10px;
    margin-bottom: 16px;
    display: flex; flex-wrap: wrap;
    align-items: flex-end; gap: 14px;
}
.mf-filter-bar label { font-size: 11px; font-weight: 700; color: #33691e; display: block; margin-bottom: 3px; }
.mf-filter-bar input,
.mf-filter-bar select {
    border: 1px solid #aed581; border-radius: 5px;
    padding: 5px 9px; font-size: 12px; color: #333; min-width: 145px;
}

/* ── Stats strip ────────────────────────────────────────────────────────── */
.mf-stats-strip {
    display: flex; flex-wrap: wrap; gap: 0;
    background: #1b5e20; border-radius: 8px 8px 0 0; overflow: hidden;
}
.mf-stat-card {
    flex: 1; min-width: 100px;
    padding: 10px 14px; border-right: 1px solid rgba(255,255,255,.15); text-align: center;
}
.mf-stat-card:last-child { border-right: none; }
.mf-stat-label { font-size: 9px; color: rgba(255,255,255,.75); text-transform: uppercase; letter-spacing: .5px; }
.mf-stat-value { font-size: 19px; font-weight: 800; color: #fff; line-height: 1.1; margin-top: 2px; }

/* ── Document wrapper ───────────────────────────────────────────────────── */
.mf-document {
    border: 2px solid #2e7d32; border-top: none;
    border-radius: 0 0 8px 8px; background: #fff; overflow: hidden;
}

/* ── Company header ─────────────────────────────────────────────────────── */
.mf-co-header {
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    padding: 14px 20px; display: flex; align-items: center; gap: 18px;
    border-bottom: 3px solid #a5d6a7;
}
.mf-co-logo img { max-width:110px; max-height:60px; background:#fff; border-radius:5px; padding:4px; object-fit:contain; }
.mf-co-logo .no-logo {
    width:110px; height:60px; background:rgba(255,255,255,.12);
    border-radius:5px; display:flex; align-items:center; justify-content:center;
    color:rgba(255,255,255,.6); font-size:11px;
}
.mf-co-info { flex: 1; }
.mf-co-info h2 { margin:0 0 2px; font-size:17px; font-weight:800; color:#fff; }
.mf-co-info p  { margin:0; font-size:11px; color:rgba(255,255,255,.85); line-height:1.5; }
.mf-form-title { text-align: right; }
.mf-form-title h3 { margin:0; font-size:15px; font-weight:800; color:#fff; text-transform:uppercase; letter-spacing:1px; }
.mf-badge-pill {
    display:inline-block; margin-top:5px; background:rgba(255,255,255,.18);
    border-radius:12px; padding:3px 10px; font-size:10px; color:#fff; font-weight:600;
}

/* ── Form fields grid ───────────────────────────────────────────────────── */
.mf-form-grid { border-bottom: 1.5px solid #2e7d32; background: #fff; }
.mf-form-row  { display: flex; border-bottom: 1px solid #c8e6c9; }
.mf-form-row:last-child { border-bottom: none; }
.mf-form-cell { padding: 5px 8px; border-right: 1px solid #c8e6c9; flex: 1; min-height: 30px; }
.mf-form-cell:last-child { border-right: none; }
.mf-form-cell.narrow { flex: 0 0 86px; }
.mf-form-cell.wide   { flex: 2; }
.mf-cell-label {
    font-size: 9px; font-weight: 700; color: #2e7d32;
    text-transform: uppercase; letter-spacing: .4px; display: block; margin-bottom: 1px;
}
.mf-cell-value {
    font-size: 12px; color: #111; font-weight: 600; min-height: 17px; display: block;
    outline: none; border-bottom: 1px dashed #c8e6c9; padding-bottom: 1px;
}
.mf-cell-value:focus { border-bottom-color: #2e7d32; background: #f1f8e9; }

/* ── Cargo table ────────────────────────────────────────────────────────── */
.mf-cargo-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.mf-cargo-table thead th {
    background: #2e7d32; color: #fff; padding: 8px 6px;
    text-align: left; font-weight: 700; font-size: 10px; letter-spacing: .3px;
    white-space: nowrap; border-right: 1px solid rgba(255,255,255,.2);
}
.mf-cargo-table thead th:last-child { border-right: none; }
.mf-cargo-table tbody td {
    padding: 7px 6px; border-bottom: 1px solid #e0e0e0;
    border-right: 1px solid #e8f5e9; vertical-align: top;
}
.mf-cargo-table tbody td:last-child { border-right: none; }
.mf-cargo-table tbody tr:nth-child(even) td { background: #f9fbe7; }
.mf-cargo-table tbody tr:hover td { background: #e8f5e9; }
.mf-empty-row td { height: 24px; background: #fff !important; }
.mf-cargo-table tfoot td {
    padding: 8px 6px; font-weight: 700; background: #1b5e20; color: #fff;
    font-size: 11px; border-right: 1px solid rgba(255,255,255,.2);
}
.mf-cargo-table tfoot td:last-child { border-right: none; }

/* Status badges */
.mf-badge {
    display:inline-block; padding:2px 7px; border-radius:10px;
    font-size:9px; font-weight:700; white-space:nowrap;
}
.badge-delivered { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.badge-transit   { background:#fff3e0; color:#e65100; border:1px solid #ffcc80; }
.badge-pending   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }
.badge-other     { background:#eceff1; color:#546e7a; border:1px solid #cfd8dc; }

/* ── Signature footer ───────────────────────────────────────────────────── */
.mf-footer-section { border-top: 2px solid #2e7d32; background: #f9fbe7; padding: 10px 14px; }
.mf-footer-row  { display: flex; gap: 20px; flex-wrap: wrap; }
.mf-footer-cell { flex: 1; min-width: 160px; }
.mf-footer-label { font-size: 9px; font-weight: 700; color: #2e7d32; text-transform: uppercase; display:block; margin-bottom:4px; }
.mf-sig-line    { border-bottom: 1.5px solid #2e7d32; min-height: 28px; margin-top: 6px; padding-top:4px; }

/* ── Email modal overlay ────────────────────────────────────────────────── */
.mf-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 9999;
    align-items: center; justify-content: center;
}
.mf-modal-overlay.open { display: flex; }
.mf-modal-box {
    background: #fff; border-radius: 10px; width: 100%; max-width: 480px;
    box-shadow: 0 12px 40px rgba(0,0,0,.25); overflow: hidden;
}
.mf-modal-header {
    background: linear-gradient(135deg,#e65100,#bf360c);
    padding: 14px 18px; display:flex; align-items:center; justify-content:space-between;
}
.mf-modal-header h4 { margin:0; color:#fff; font-size:15px; font-weight:700; }
.mf-modal-close {
    background: rgba(255,255,255,.2); border:none; border-radius:50%;
    width:28px; height:28px; cursor:pointer; color:#fff; font-size:14px;
    display:flex; align-items:center; justify-content:center;
}
.mf-modal-close:hover { background:rgba(255,255,255,.35); }
.mf-modal-body  { padding: 20px 18px; }
.mf-modal-body .form-group { margin-bottom: 14px; }
.mf-modal-body label { font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:4px; }
.mf-modal-body input,
.mf-modal-body select,
.mf-modal-body textarea {
    width:100%; border:1px solid #c8e6c9; border-radius:6px;
    padding:8px 10px; font-size:13px; color:#333; box-sizing:border-box;
}
.mf-modal-body input:focus,
.mf-modal-body textarea:focus { border-color:#2e7d32; outline:none; box-shadow:0 0 0 3px rgba(46,125,50,.15); }
.mf-modal-footer {
    padding: 12px 18px; background: #f9fbe7;
    border-top: 1px solid #c8e6c9;
    display: flex; gap: 10px; justify-content: flex-end;
}
.mf-alert {
    padding: 8px 12px; border-radius:6px; font-size:12px; margin-top:10px; display:none;
}
.mf-alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.mf-alert-error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

/* ── Service points checkbox panel ─────────────────────────────────────── */
.sp-checkbox, .rt-stop-checkbox { cursor:pointer; }
/* ── Routes modal tweaks ────────────────────────────────────────────────── */
#mf-routes-modal .mf-modal-body .form-group label { color:#33691e; }
#mf-routes-modal .mf-modal-body input,
#mf-routes-modal .mf-modal-body textarea { border-color:#aed581; }
#mf-routes-modal .mf-modal-body input:focus,
#mf-routes-modal .mf-modal-body textarea:focus { border-color:#2e7d32; box-shadow:0 0 0 2px rgba(46,125,50,.15); outline:none; }

/* ── Print ──────────────────────────────────────────────────────────────── */
@media print {
    .mf-topbar, .mf-filter-bar, .mf-no-print, .mf-modal-overlay { display: none !important; }
    body { background: #fff; }
    #mf-wrapper { padding: 0; }
    .mf-stats-strip, .mf-co-header, .mf-cargo-table thead th, .mf-cargo-table tfoot td {
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .mf-document { border-radius: 0; }
    .mf-cell-value { border-bottom: 1px solid #999; }
}
</style>

<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'documents']); ?>
<div id="mf-wrapper">

    <!-- ══ STICKY TOP NAV BAR ═══════════════════════════════════════════════ -->
    <div class="mf-topbar mf-no-print">
        <!-- Left: nav links -->
        <div class="mf-topbar-left">
            <span class="mf-topbar-title">
                <i class="fa fa-file-text"></i> Cargo Manifest
            </span>
            <a href="<?php echo admin_url('courier_goshipping/shipments?type=domestic'); ?>" class="mf-btn mf-btn-back">
                <i class="fa fa-arrow-left"></i> Shipment List
            </a>
            <a href="<?php echo admin_url('courier_goshipping/shipments/create?type=domestic'); ?>" class="mf-btn mf-btn-create">
                <i class="fa fa-plus"></i> Create Shipment
            </a>
        </div>
        <!-- Right: action buttons -->
        <div class="mf-topbar-right">
            <button class="mf-btn mf-btn-print" onclick="window.print()">
                <i class="fa fa-print"></i> Print Manifest
            </button>
            <button class="mf-btn mf-btn-csv" onclick="exportManifestCSV()">
                <i class="fa fa-download"></i> Export CSV
            </button>
            <button class="mf-btn mf-btn-email" onclick="openEmailModal()">
                <i class="fa fa-envelope"></i> Send to Email
            </button>
        </div>
    </div>

    <!-- ══ FILTER BAR ════════════════════════════════════════════════════════ -->
    <form method="GET" action="" class="mf-filter-bar mf-no-print" id="mf-filter-form">
        <input type="hidden" name="group" value="manifests">
        <div>
            <label>From Date</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from ?? date('Y-m-01')); ?>">
        </div>
        <div>
            <label>To Date</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to ?? date('Y-m-d')); ?>">
        </div>
        <div>
            <label>Driver / Agent</label>
            <select name="driver_id">
                <option value="">All Drivers</option>
                <?php foreach ((array)($all_drivers ?? []) as $drv):
                    $drv_id   = (int)($drv['staffid'] ?? 0);
                    $drv_nm   = trim(($drv['firstname'] ?? '') . ' ' . ($drv['lastname'] ?? ''));
                    $sel      = ((string)($filter_driver_id ?? '') === (string)$drv_id) ? 'selected' : '';
                ?>
                <option value="<?php echo $drv_id; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($drv_nm); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Route picker — auto-checks matching service point stops -->
        <div>
            <label>Route <span style="font-weight:400;color:#777;">(optional)</span></label>
            <select name="route_id" id="mf-route-select" style="min-width:180px;">
                <option value="">— Select Route —</option>
                <?php foreach ((array)($all_routes ?? []) as $rt):
                    $rt_sel = ((int)($filter_route_id ?? 0) === (int)$rt->id) ? 'selected' : '';
                ?>
                <option value="<?php echo (int)$rt->id; ?>" <?php echo $rt_sel; ?>><?php echo htmlspecialchars($rt->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Service Points checkbox panel -->
        <?php
        $mf_active_sps    = (array)($filter_service_points ?? []);
        $mf_all_sps       = (array)($all_service_points   ?? []);
        $mf_sp_sel_count  = count(array_filter($mf_all_sps, function($s) use ($mf_active_sps) {
            return in_array($s['name'], $mf_active_sps);
        }));
        ?>
        <div style="min-width:320px; flex:3;">
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                Service Points / POD
                <button type="button" id="mf-sp-toggle" onclick="toggleSpPanel()"
                        style="font-size:11px; padding:2px 10px; border:1px solid #aed581; border-radius:3px;
                               background:#e8f5e9; color:#2e7d32; cursor:pointer; font-weight:700; line-height:1.4;">
                    <span id="mf-sp-count"><?php echo $mf_sp_sel_count; ?></span> selected
                    <i class="fa fa-chevron-down" id="mf-sp-chevron" style="margin-left:4px;"></i>
                </button>
                <span style="font-size:10px; color:#888; font-weight:400;">← click to pick stops</span>
            </label>
            <div id="mf-sp-panel" style="<?php echo empty($mf_active_sps) && empty($filter_route_id) ? 'display:none;' : ''; ?>
                                          background:#fff; border:1.5px solid #aed581; border-radius:6px;
                                          padding:8px 10px; max-height:160px; overflow-y:auto;">
                <div style="display:flex; gap:8px; margin-bottom:6px; position:sticky; top:0;
                             background:#fff; padding:2px 0 4px; border-bottom:1px solid #e0e0e0; z-index:2;">
                    <button type="button" onclick="checkAllSps()"
                            style="font-size:10px;padding:1px 10px;border:1px solid #aed581;border-radius:3px;background:#e8f5e9;color:#2e7d32;cursor:pointer;font-weight:700;">
                        All
                    </button>
                    <button type="button" onclick="uncheckAllSps()"
                            style="font-size:10px;padding:1px 10px;border:1px solid #ccc;border-radius:3px;background:#f5f5f5;color:#666;cursor:pointer;font-weight:700;">
                        None
                    </button>
                </div>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1px 10px;">
                    <?php foreach ($mf_all_sps as $_msp):
                        $msp_chk = in_array($_msp['name'], $mf_active_sps) ? 'checked' : '';
                    ?>
                    <label style="font-size:11px;font-weight:400;cursor:pointer;display:flex;align-items:center;gap:4px;padding:2px 0;white-space:nowrap;">
                        <input type="checkbox" name="service_points[]"
                               value="<?php echo htmlspecialchars($_msp['name'], ENT_QUOTES); ?>"
                               class="sp-checkbox" <?php echo $msp_chk; ?> onchange="updateSpCount()">
                        <?php echo htmlspecialchars($_msp['name']); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
            <button type="submit" class="mf-btn mf-btn-create" style="padding:7px 16px;">
                <i class="fa fa-filter"></i> Filter
            </button>
            <a href="?group=manifests" class="mf-btn mf-btn-back" style="padding:7px 14px;">
                <i class="fa fa-times"></i> Clear
            </a>
            <?php if (is_admin() || staff_can('edit_manifests', 'courier-manifests')): ?>
            <button type="button" class="mf-btn" onclick="openRoutesModal()"
                    style="background:linear-gradient(135deg,#5c6bc0,#3949ab);color:#fff;padding:7px 12px;">
                <i class="fa fa-road"></i> Manage Routes
            </button>
            <?php endif; ?>
        </div>
    </form>

    <!-- ══ SUMMARY STATS STRIP ═══════════════════════════════════════════════ -->
    <div class="mf-stats-strip">
        <div class="mf-stat-card">
            <div class="mf-stat-label">Shipments</div>
            <div class="mf-stat-value"><?php echo count($mf_rows); ?></div>
        </div>
        <div class="mf-stat-card">
            <div class="mf-stat-label">Total Pieces</div>
            <div class="mf-stat-value"><?php echo number_format($mf_total_qty); ?></div>
        </div>
        <div class="mf-stat-card">
            <div class="mf-stat-label">Total Weight</div>
            <div class="mf-stat-value"><?php echo number_format($mf_total_weight, 1); ?> <span style="font-size:11px;">kg</span></div>
        </div>
        <div class="mf-stat-card">
            <div class="mf-stat-label">Charges</div>
            <div class="mf-stat-value"><?php echo number_format($mf_total_charges, 2); ?></div>
        </div>
        <div class="mf-stat-card">
            <div class="mf-stat-label">VAT</div>
            <div class="mf-stat-value"><?php echo number_format($mf_total_vat, 2); ?></div>
        </div>
        <div class="mf-stat-card">
            <div class="mf-stat-label">Grand Total</div>
            <div class="mf-stat-value"><?php echo number_format($mf_total_amount, 2); ?></div>
        </div>
    </div>

    <!-- ══ MAIN DOCUMENT ═════════════════════════════════════════════════════ -->
    <div class="mf-document" id="mf-document">

        <!-- ── Company header ──────────────────────────────────────────── -->
        <div class="mf-co-header">
            <div class="mf-co-logo">
                <?php if (!empty($_mf_logo_url)): ?>
                    <img src="<?php echo $_mf_logo_url; ?>" alt="<?php echo htmlspecialchars($_mf_company); ?>">
                <?php else: ?>
                    <div class="no-logo"><i class="fa fa-building" style="font-size:26px;"></i></div>
                <?php endif; ?>
            </div>
            <div class="mf-co-info">
                <h2><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></h2>
                <?php if ($_mf_address): ?>
                    <p><i class="fa fa-map-marker"></i>&nbsp;<?php echo nl2br(htmlspecialchars($_mf_address)); ?></p>
                <?php endif; ?>
                <p>
                    <?php if ($_mf_phone): ?>
                        <i class="fa fa-phone"></i>&nbsp;<?php echo htmlspecialchars($_mf_phone); ?>
                    <?php endif; ?>
                    <?php if ($_mf_email_co): ?>
                        &nbsp;|&nbsp;<i class="fa fa-envelope"></i>&nbsp;<?php echo htmlspecialchars($_mf_email_co); ?>
                    <?php endif; ?>
                    <?php if ($_mf_website): ?>
                        &nbsp;|&nbsp;<i class="fa fa-globe"></i>&nbsp;<?php echo htmlspecialchars($_mf_website); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="mf-form-title">
                <h3><i class="fa fa-file-text-o" style="font-size:14px;"></i> Cargo Manifest</h3>
                <div class="mf-badge-pill"><i class="fa fa-calendar"></i>&nbsp;<?php echo $mf_from_label; ?> &mdash; <?php echo $mf_to_label; ?></div>
                <?php if ($mf_driver_label): ?>
                <div class="mf-badge-pill" style="margin-top:4px;"><i class="fa fa-user"></i>&nbsp;<?php echo htmlspecialchars($mf_driver_label); ?></div>
                <?php endif; ?>
                <?php if (!empty($filter_service_points)): ?>
                <div class="mf-badge-pill" style="margin-top:4px;"><i class="fa fa-map-marker"></i>&nbsp;POD: <?php echo htmlspecialchars(implode(', ', array_slice((array)$filter_service_points, 0, 4)) . (count((array)$filter_service_points) > 4 ? '…' : '')); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Operations / route info (editable fields) ───────────────── -->
        <div class="mf-form-grid">
            <div class="mf-form-row">
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Ordering Unit / Department</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo htmlspecialchars($_mf_company); ?></span>
                </div>
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Operations Manager — Name &amp; Phone</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo htmlspecialchars($_mf_phone); ?></span>
                </div>
                <div class="mf-form-cell">
                    <span class="mf-cell-label">Manifest / Project Number</span>
                    <span class="mf-cell-value" contenteditable="true">MF-<?php echo date('Ymd'); ?>-<?php echo str_pad(count($mf_rows), 3, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
            <div class="mf-form-row">
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Carrier Name / Vehicle — Make / Model / License</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo htmlspecialchars($mf_driver_label); ?></span>
                </div>
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Departure Location (Pickup Point)</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo !empty($mf_rows[0]['sender_addr']) ? htmlspecialchars($mf_rows[0]['sender_addr']) : ''; ?></span>
                </div>
                <div class="mf-form-cell narrow">
                    <span class="mf-cell-label">Dep. TIME</span>
                    <span class="mf-cell-value" contenteditable="true"></span>
                </div>
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Arrival Location (Delivery Point)</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo !empty($mf_rows[0]['recv_addr']) ? htmlspecialchars($mf_rows[0]['recv_addr']) : ''; ?></span>
                </div>
                <div class="mf-form-cell narrow">
                    <span class="mf-cell-label">Arr. TIME</span>
                    <span class="mf-cell-value" contenteditable="true"></span>
                </div>
            </div>
            <div class="mf-form-row">
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">Report To (Supervisor / Contact)</span>
                    <span class="mf-cell-value" contenteditable="true"></span>
                </div>
                <div class="mf-form-cell wide">
                    <span class="mf-cell-label">If Delayed, Contact</span>
                    <span class="mf-cell-value" contenteditable="true"><?php echo htmlspecialchars($_mf_phone); ?></span>
                </div>
                <div class="mf-form-cell">
                    <span class="mf-cell-label">Shipping Mode</span>
                    <span class="mf-cell-value" contenteditable="true"><?php
                        $modes = array_column($mf_rows, 'mode');
                        if (!empty($modes)) { $mc = array_count_values($modes); arsort($mc); echo htmlspecialchars(key($mc)); }
                    ?></span>
                </div>
            </div>
        </div>

        <!-- ── Cargo table ─────────────────────────────────────────────── -->
        <div style="overflow-x:auto;">
            <table class="mf-cargo-table" id="mf-table">
                <thead>
                    <tr>
                        <th style="width:28px; text-align:center;">#</th>
                        <th style="min-width:160px;">
                            Cargo Name
                            <div style="font-size:8px; font-weight:400; opacity:.8;">(Sender + contact + item desc.)</div>
                        </th>
                        <th style="min-width:110px;">Agent / Driver</th>
                        <th style="width:60px; text-align:center;">Kgs</th>
                        <th style="min-width:130px;">
                            Client Name
                            <div style="font-size:8px; font-weight:400; opacity:.8;">(Receiver)</div>
                        </th>
                        <th style="min-width:115px;">
                            Client Number
                            <div style="font-size:8px; font-weight:400; opacity:.8;">(Phone / AWB)</div>
                        </th>
                        <th style="width:80px; text-align:right;">Amount</th>
                        <th style="width:60px; text-align:right;">VAT</th>
                        <th style="min-width:160px;">
                            POD
                            <div style="font-size:8px; font-weight:400; opacity:.8;">(Point of Delivery per package)</div>
                        </th>
                        <th style="min-width:85px; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $mf_row_num = 0;
                if (!empty($mf_rows)):
                    foreach ($mf_rows as $i => $row):
                        $sl = strtolower($row['status']);
                        if     (strpos($sl,'deliver')!==false)                                 $bc='badge-delivered';
                        elseif (strpos($sl,'transit')!==false||strpos($sl,'pickup')!==false)   $bc='badge-transit';
                        elseif (strpos($sl,'pending')!==false||strpos($sl,'cancel')!==false)   $bc='badge-pending';
                        else                                                                    $bc='badge-other';

                        $pod_lines = !empty($row['pod_lines']) ? $row['pod_lines'] : [];
                        // Number of rows this shipment will occupy (at least 1)
                        $row_span  = max(1, count($pod_lines));
                        $mf_row_num++;
                        $is_first  = true;
                ?>
                    <?php if (!empty($pod_lines)): ?>
                        <?php foreach ($pod_lines as $pi => $pl):
                            $mf_row_num_display = $is_first ? $mf_row_num : '';
                        ?>
                        <tr<?php if (!$is_first): ?> style="background:<?php echo $pi % 2 === 0 ? '#f9fbe7' : '#fff'; ?>!important;"<?php endif; ?>>
                            <?php if ($is_first): ?>
                            <td style="text-align:center; color:#999; font-weight:700;" rowspan="<?php echo $row_span; ?>"><?php echo $mf_row_num; ?></td>
                            <td rowspan="<?php echo $row_span; ?>">
                                <div style="font-weight:700;"><?php echo htmlspecialchars($row['sender_name']); ?></div>
                                <?php if ($row['sender_phone'] !== '-'): ?>
                                <div style="font-size:10px; color:#555; margin-top:1px;"><i class="fa fa-phone" style="font-size:8px; color:#2e7d32;"></i> <?php echo htmlspecialchars($row['sender_phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:10px;" rowspan="<?php echo $row_span; ?>"><?php echo htmlspecialchars($row['driver_name']); ?></td>
                            <?php endif; ?>
                            <!-- Per-POD cells -->
                            <td style="text-align:center; font-weight:700;"><?php echo number_format($pl['weight'], 2); ?></td>
                            <?php if ($is_first): ?>
                            <td style="font-weight:700;" rowspan="<?php echo $row_span; ?>"><?php echo htmlspecialchars($row['recv_name']); ?></td>
                            <td rowspan="<?php echo $row_span; ?>">
                                <div style="font-size:10px; color:#444;"><?php echo htmlspecialchars($row['recv_phone']); ?></div>
                                <div style="font-size:9px; color:#1b5e20; font-weight:700; margin-top:2px;"><?php echo htmlspecialchars($row['waybill']); ?></div>
                            </td>
                            <td style="text-align:right;" rowspan="<?php echo $row_span; ?>"><?php echo number_format((float)$row['charges'], 2); ?></td>
                            <td style="text-align:right;" rowspan="<?php echo $row_span; ?>"><?php echo number_format((float)$row['vat'], 2); ?></td>
                            <?php endif; ?>
                            <!-- POD cell per package row -->
                            <td style="font-size:11px;">
                                <strong style="color:#1b5e20;"><?php echo htmlspecialchars($pl['pod']); ?></strong>
                                <?php if (!empty($pl['desc'])): ?>
                                    <div style="font-size:9px; color:#777; font-style:italic; margin-top:1px;"><?php echo htmlspecialchars(mb_strimwidth($pl['desc'], 0, 60, '..')); ?></div>
                                    <div style="font-size:9px; color:#888;">Qty: <?php echo $pl['qty']; ?> &bull; <?php echo number_format($pl['weight'],2); ?> kgs</div>
                                <?php endif; ?>
                            </td>
                            <?php if ($is_first): ?>
                            <td style="text-align:center;" rowspan="<?php echo $row_span; ?>"><span class="mf-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <?php endif; ?>
                        </tr>
                        <?php $is_first = false; endforeach; ?>
                    <?php else: // no pod_lines — show single row with recv_addr as POD ?>
                    <tr>
                        <td style="text-align:center; color:#999; font-weight:700;"><?php echo $mf_row_num; ?></td>
                        <td>
                            <div style="font-weight:700;"><?php echo htmlspecialchars($row['sender_name']); ?></div>
                            <?php if ($row['sender_phone'] !== '-'): ?>
                            <div style="font-size:10px; color:#555;"><i class="fa fa-phone" style="font-size:8px; color:#2e7d32;"></i> <?php echo htmlspecialchars($row['sender_phone']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($row['pkg_desc']) && $row['pkg_desc'] !== '-'): ?>
                            <div style="font-size:9px; color:#777; font-style:italic;"><?php echo htmlspecialchars($row['pkg_desc']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:10px;"><?php echo htmlspecialchars($row['driver_name']); ?></td>
                        <td style="text-align:center; font-weight:700;"><?php echo number_format((float)$row['pkg_weight'], 2); ?></td>
                        <td style="font-weight:700;"><?php echo htmlspecialchars($row['recv_name']); ?></td>
                        <td>
                            <div style="font-size:10px; color:#444;"><?php echo htmlspecialchars($row['recv_phone']); ?></div>
                            <div style="font-size:9px; color:#1b5e20; font-weight:700;"><?php echo htmlspecialchars($row['waybill']); ?></div>
                        </td>
                        <td style="text-align:right;"><?php echo number_format((float)$row['charges'], 2); ?></td>
                        <td style="text-align:right;"><?php echo number_format((float)$row['vat'], 2); ?></td>
                        <td style="font-size:10px; color:#444;"><?php echo htmlspecialchars($row['recv_addr']); ?></td>
                        <td style="text-align:center;"><span class="mf-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach;
                    for ($b = 0; $b < max(0, 5 - $mf_row_num); $b++): ?>
                    <tr class="mf-empty-row">
                        <td style="text-align:center; color:#ddd;"><?php echo $mf_row_num + $b + 1; ?></td>
                        <td></td><td></td><td></td><td></td><td></td>
                        <td></td><td></td><td></td><td></td>
                    </tr>
                    <?php endfor;
                else:
                    for ($b = 1; $b <= 23; $b++): ?>
                    <tr class="mf-empty-row">
                        <td style="text-align:center; color:#ddd;"><?php echo $b; ?></td>
                        <td></td><td></td><td></td><td></td><td></td>
                        <td></td><td></td><td></td><td></td>
                    </tr>
                    <?php endfor; ?>
                <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;">
                            Shipments on manifest: <strong><?php echo count($mf_rows); ?></strong>
                        </td>
                        <td style="text-align:center; font-weight:800;"><?php echo number_format($mf_total_weight, 2); ?> kg</td>
                        <td colspan="3" style="text-align:right;">Total Charges + VAT:</td>
                        <td style="text-align:right; font-weight:800;"><?php echo number_format($mf_total_amount, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ── Signature footer ────────────────────────────────────────── -->
        <div class="mf-footer-section">
            <div class="mf-footer-row">
                <div class="mf-footer-cell" style="flex:2;">
                    <span class="mf-footer-label">Signature of Authorized Representative</span>
                    <div class="mf-sig-line"></div>
                </div>
                <div class="mf-footer-cell">
                    <span class="mf-footer-label">Date</span>
                    <div class="mf-sig-line" style="font-size:12px; color:#333; font-weight:600;"><?php echo date('d / m / Y'); ?></div>
                </div>
                <div class="mf-footer-cell">
                    <span class="mf-footer-label">Printed By</span>
                    <div class="mf-sig-line" style="font-size:11px; color:#555;"><?php echo htmlspecialchars($mf_printed_by); ?></div>
                </div>
            </div>
            <div style="margin-top:10px; text-align:center; font-size:9px; color:#aaa; border-top:1px solid #c8e6c9; padding-top:6px;">
                Generated <?php echo date('d M Y, H:i'); ?> &nbsp;|&nbsp; <?php echo htmlspecialchars($_mf_company); ?>
                <?php if ($_mf_phone): ?>&nbsp;|&nbsp; <?php echo htmlspecialchars($_mf_phone); ?><?php endif; ?>
            </div>
        </div>

    </div><!-- /mf-document -->

</div><!-- /mf-wrapper -->
</div><!-- /cgs-page -->
</div><!-- /wrapper -->

<!-- ══ MANAGE ROUTES MODAL ═══════════════════════════════════════════════════ -->
<div class="mf-modal-overlay" id="mf-routes-modal">
    <div class="mf-modal-box" style="max-width:640px;">
        <div class="mf-modal-header" style="background:linear-gradient(135deg,#5c6bc0,#3949ab);">
            <h4><i class="fa fa-road"></i>&nbsp; Manage Routes &amp; Service Points</h4>
            <button class="mf-modal-close" onclick="closeRoutesModal()"><i class="fa fa-times"></i></button>
        </div>
        <div class="mf-modal-body" style="max-height:70vh; overflow-y:auto;">

            <!-- Route list -->
            <div id="rt-list" style="margin-bottom:16px;"></div>

            <!-- Add / Edit Route form -->
            <div style="background:#f1f8e9; border:1.5px solid #aed581; border-radius:8px; padding:14px; margin-bottom:12px;">
                <strong style="font-size:13px; color:#33691e;"><i class="fa fa-plus-circle"></i> Add / Edit Route</strong>
                <input type="hidden" id="rt-edit-id" value="">
                <div class="form-group" style="margin-top:10px; margin-bottom:8px;">
                    <label>Route Name <span style="color:#c62828;">*</span></label>
                    <input type="text" id="rt-name" class="form-control" placeholder="e.g. Nairobi – Busia Route A">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                    <label>Description (optional)</label>
                    <input type="text" id="rt-desc" class="form-control" placeholder="e.g. Via Naivasha, Nakuru, Kericho, Kisumu">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                    <label>
                        Service Points / Stops
                        <button type="button" onclick="checkAllRtStops()"
                                style="font-size:10px;padding:1px 8px;border:1px solid #aed581;border-radius:3px;background:#e8f5e9;color:#2e7d32;cursor:pointer;font-weight:700;margin-left:6px;">All</button>
                        <button type="button" onclick="uncheckAllRtStops()"
                                style="font-size:10px;padding:1px 8px;border:1px solid #ccc;border-radius:3px;background:#f5f5f5;color:#666;cursor:pointer;font-weight:700;">None</button>
                        <span id="rt-stop-count" style="font-size:10px;color:#2e7d32;font-weight:700;margin-left:6px;"></span>
                    </label>
                    <div style="border:1px solid #aed581;border-radius:5px;padding:8px;max-height:180px;overflow-y:auto;background:#fff;">
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2px 8px;">
                            <?php foreach (($all_service_points ?? []) as $_rsp): ?>
                            <label style="font-size:11px;font-weight:400;cursor:pointer;display:flex;align-items:center;gap:4px;padding:1px 0;white-space:nowrap;">
                                <input type="checkbox" class="rt-stop-checkbox"
                                       value="<?php echo htmlspecialchars($_rsp['name'], ENT_QUOTES); ?>"
                                       onchange="updateRtStopCount()">
                                <?php echo htmlspecialchars($_rsp['name']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <button class="mf-btn mf-btn-create" onclick="saveRoute()">
                    <i class="fa fa-save"></i> Save Route
                </button>
                <button class="mf-btn mf-btn-back" onclick="clearRouteForm()" style="margin-left:6px;">
                    <i class="fa fa-times"></i> Clear
                </button>
                <div class="mf-alert mf-alert-success" id="rt-ok" style="margin-top:8px;"></div>
                <div class="mf-alert mf-alert-error"   id="rt-err" style="margin-top:8px;"></div>
            </div>
        </div>
        <div class="mf-modal-footer">
            <button class="mf-btn mf-btn-back" onclick="closeRoutesModal()">
                <i class="fa fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<!-- ══ SEND TO EMAIL MODAL ════════════════════════════════════════════════════ -->
<div class="mf-modal-overlay" id="mf-email-modal">
    <div class="mf-modal-box">
        <div class="mf-modal-header">
            <h4><i class="fa fa-envelope"></i>&nbsp; Send Manifest by Email</h4>
            <button class="mf-modal-close" onclick="closeEmailModal()"><i class="fa fa-times"></i></button>
        </div>
        <div class="mf-modal-body">
            <div class="form-group">
                <label><i class="fa fa-envelope-o"></i>&nbsp; Recipient Email <span style="color:#c62828;">*</span></label>
                <input type="email" id="mf-to-email" placeholder="e.g. agent@company.com">
            </div>
            <div class="form-group">
                <label><i class="fa fa-tag"></i>&nbsp; Subject</label>
                <input type="text" id="mf-subject" value="Cargo Manifest — <?php echo $mf_from_label; ?> to <?php echo $mf_to_label; ?>">
            </div>
            <div class="form-group">
                <label><i class="fa fa-comment"></i>&nbsp; Additional Note (optional)</label>
                <textarea id="mf-note" rows="3" placeholder="Add a message for the recipient…"></textarea>
            </div>
            <div class="mf-alert mf-alert-success" id="mf-email-success">
                <i class="fa fa-check-circle"></i>&nbsp; <span id="mf-email-success-msg"></span>
            </div>
            <div class="mf-alert mf-alert-error" id="mf-email-error">
                <i class="fa fa-exclamation-triangle"></i>&nbsp; <span id="mf-email-error-msg"></span>
            </div>
        </div>
        <div class="mf-modal-footer">
            <button class="mf-btn mf-btn-back" onclick="closeEmailModal()">
                <i class="fa fa-times"></i> Cancel
            </button>
            <button class="mf-btn mf-btn-email" id="mf-send-btn" onclick="sendManifestEmail()">
                <i class="fa fa-paper-plane"></i> Send Email
            </button>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
/* ── Email modal ─────────────────────────────────────────────────────────── */
function openEmailModal() {
    document.getElementById('mf-email-success').style.display = 'none';
    document.getElementById('mf-email-error').style.display   = 'none';
    document.getElementById('mf-email-modal').classList.add('open');
}
function closeEmailModal() {
    document.getElementById('mf-email-modal').classList.remove('open');
}
// Close on backdrop click
document.getElementById('mf-email-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEmailModal();
});

function sendManifestEmail() {
    var toEmail = document.getElementById('mf-to-email').value.trim();
    var subject = document.getElementById('mf-subject').value.trim();

    document.getElementById('mf-email-success').style.display = 'none';
    document.getElementById('mf-email-error').style.display   = 'none';

    if (!toEmail) {
        document.getElementById('mf-email-error-msg').textContent = 'Please enter a recipient email address.';
        document.getElementById('mf-email-error').style.display = 'block';
        return;
    }

    var btn = document.getElementById('mf-send-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending…';

    $.ajax({
        url: '<?php echo admin_url("courier_goshipping/shipments/send_manifest_email"); ?>',
        type: 'POST',
        data: {
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>',
            to_email:  toEmail,
            subject:   subject,
            date_from: '<?php echo htmlspecialchars($filter_date_from ?? date("Y-m-01")); ?>',
            date_to:   '<?php echo htmlspecialchars($filter_date_to   ?? date("Y-m-d")); ?>',
            driver_id: '<?php echo (int)($filter_driver_id ?? 0); ?>'
        },
        dataType: 'json',
        success: function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Email';
            if (res.success) {
                document.getElementById('mf-email-success-msg').textContent = res.message;
                document.getElementById('mf-email-success').style.display = 'block';
                setTimeout(closeEmailModal, 2800);
            } else {
                document.getElementById('mf-email-error-msg').textContent = res.message;
                document.getElementById('mf-email-error').style.display = 'block';
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Email';
            document.getElementById('mf-email-error-msg').textContent = 'Network error. Please try again.';
            document.getElementById('mf-email-error').style.display = 'block';
        }
    });
}

/* ── Route → Service Points auto-check ──────────────────────────────────── */
var ROUTES_URL      = '<?php echo admin_url("courier_goshipping/shipments/get_routes"); ?>';
var STOPS_URL_BASE  = '<?php echo admin_url("courier_goshipping/shipments/get_route_stops"); ?>';
var SAVE_ROUTE_URL  = '<?php echo admin_url("courier_goshipping/shipments/save_route"); ?>';
var DEL_ROUTE_URL   = '<?php echo admin_url("courier_goshipping/shipments/delete_route"); ?>';
var SAVE_STOPS_URL  = '<?php echo admin_url("courier_goshipping/shipments/save_route_stops"); ?>';
var MF_CSRF_KEY     = '<?php echo $this->security->get_csrf_token_name(); ?>';
var MF_CSRF_VAL     = '<?php echo $this->security->get_csrf_hash(); ?>';

function getCsrfVal() {
    var c = document.cookie.split(';');
    for (var i = 0; i < c.length; i++) {
        var p = c[i].trim();
        if (p.indexOf(MF_CSRF_KEY + '=') === 0) return p.substring((MF_CSRF_KEY + '=').length);
    }
    return MF_CSRF_VAL;
}

/* Service points checkbox panel helpers */
function toggleSpPanel() {
    var panel = document.getElementById('mf-sp-panel');
    var chev  = document.getElementById('mf-sp-chevron');
    var open  = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : '';
    chev.className = open ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
}
function updateSpCount() {
    var checked = document.querySelectorAll('.sp-checkbox:checked').length;
    document.getElementById('mf-sp-count').textContent = checked;
}
function checkAllSps() {
    document.querySelectorAll('.sp-checkbox').forEach(function(cb){ cb.checked = true; });
    updateSpCount();
}
function uncheckAllSps() {
    document.querySelectorAll('.sp-checkbox').forEach(function(cb){ cb.checked = false; });
    updateSpCount();
}

/* Route selection → auto-check matching service points */
document.getElementById('mf-route-select').addEventListener('change', function() {
    var routeId = this.value;
    uncheckAllSps();
    if (!routeId) return;
    // Open the panel so user can see what was selected
    var panel = document.getElementById('mf-sp-panel');
    var chev  = document.getElementById('mf-sp-chevron');
    panel.style.display = '';
    chev.className = 'fa fa-chevron-up';
    fetch(STOPS_URL_BASE + '/' + routeId, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success || !res.data) return;
            var stopNames = res.data.map(function(s){ return s.stop_name; });
            document.querySelectorAll('.sp-checkbox').forEach(function(cb) {
                if (stopNames.indexOf(cb.value) !== -1) cb.checked = true;
            });
            updateSpCount();
        });
});

/* Init count on page load */
updateSpCount();

/* ── Routes modal ─────────────────────────────────────────────────────────── */
function openRoutesModal() {
    loadRoutesList();
    clearRouteForm();
    document.getElementById('mf-routes-modal').classList.add('open');
}
function closeRoutesModal() {
    document.getElementById('mf-routes-modal').classList.remove('open');
}
document.getElementById('mf-routes-modal').addEventListener('click', function(e){ if(e.target===this) closeRoutesModal(); });

function loadRoutesList() {
    fetch(ROUTES_URL, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            var el = document.getElementById('rt-list');
            if (!res.data || !res.data.length) { el.innerHTML = '<p style="color:#999; font-size:12px;">No routes yet. Add one below.</p>'; return; }
            var html = '<table style="width:100%; font-size:12px; border-collapse:collapse;">';
            html += '<thead><tr style="background:#e8f5e9;"><th style="padding:6px 8px; text-align:left;">Route Name</th><th style="padding:6px 8px; text-align:left;">Description</th><th style="padding:6px 8px; width:120px;"></th></tr></thead><tbody>';
            res.data.forEach(function(r) {
                html += '<tr style="border-bottom:1px solid #e0e0e0;">';
                html += '<td style="padding:6px 8px; font-weight:700;">' + escHtml(r.name) + '</td>';
                html += '<td style="padding:6px 8px; color:#555;">' + escHtml(r.description || '') + '</td>';
                html += '<td style="padding:4px 8px; text-align:right;">';
                html += '<button class="mf-btn" style="background:#1565c0;color:#fff;padding:3px 9px;font-size:11px;" onclick="editRoute(' + r.id + ',\'' + escAttr(r.name) + '\',\'' + escAttr(r.description||'') + '\')"><i class="fa fa-pencil"></i></button> ';
                html += '<button class="mf-btn" style="background:#c62828;color:#fff;padding:3px 9px;font-size:11px;" onclick="deleteRoute(' + r.id + ')"><i class="fa fa-trash"></i></button>';
                html += '</td></tr>';
            });
            html += '</tbody></table>';
            el.innerHTML = html;
        });
}

/* Routes modal: stop checkboxes */
function updateRtStopCount() {
    var n = document.querySelectorAll('.rt-stop-checkbox:checked').length;
    document.getElementById('rt-stop-count').textContent = n + ' selected';
}
function checkAllRtStops() {
    document.querySelectorAll('.rt-stop-checkbox').forEach(function(cb){ cb.checked = true; });
    updateRtStopCount();
}
function uncheckAllRtStops() {
    document.querySelectorAll('.rt-stop-checkbox').forEach(function(cb){ cb.checked = false; });
    updateRtStopCount();
}

function editRoute(id, name, desc) {
    document.getElementById('rt-edit-id').value = id;
    document.getElementById('rt-name').value = name;
    document.getElementById('rt-desc').value = desc;
    // Uncheck all then check route's stops
    uncheckAllRtStops();
    fetch(STOPS_URL_BASE + '/' + id, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.data) return;
            var stopNames = res.data.map(function(s){ return s.stop_name; });
            document.querySelectorAll('.rt-stop-checkbox').forEach(function(cb) {
                if (stopNames.indexOf(cb.value) !== -1) cb.checked = true;
            });
            updateRtStopCount();
        });
}

function clearRouteForm() {
    document.getElementById('rt-edit-id').value = '';
    document.getElementById('rt-name').value    = '';
    document.getElementById('rt-desc').value    = '';
    uncheckAllRtStops();
    document.getElementById('rt-ok').style.display  = 'none';
    document.getElementById('rt-err').style.display = 'none';
}

function saveRoute() {
    var id   = document.getElementById('rt-edit-id').value;
    var name = document.getElementById('rt-name').value.trim();
    var desc = document.getElementById('rt-desc').value.trim();
    // Collect checked service point checkboxes
    var stops = [];
    document.querySelectorAll('.rt-stop-checkbox:checked').forEach(function(cb){ stops.push(cb.value); });
    var okEl  = document.getElementById('rt-ok');
    var errEl = document.getElementById('rt-err');
    okEl.style.display = errEl.style.display = 'none';
    if (!name) { errEl.textContent = 'Route name is required.'; errEl.style.display = 'block'; return; }
    if (!stops.length) { errEl.textContent = 'Please select at least one service point/stop.'; errEl.style.display = 'block'; return; }

    var fd = new FormData();
    fd.append('id', id);
    fd.append('name', name);
    fd.append('description', desc);
    fd.append(MF_CSRF_KEY, getCsrfVal());

    fetch(SAVE_ROUTE_URL, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success) { errEl.textContent = res.message || 'Error.'; errEl.style.display='block'; return; }
            var routeId = res.id;
            var fd2 = new FormData();
            stops.forEach(function(s){ fd2.append('stops[]', s); });
            fd2.append(MF_CSRF_KEY, getCsrfVal());
            return fetch(SAVE_STOPS_URL + '/' + routeId, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd2});
        })
        .then(function(r){ if (r) return r.json(); })
        .then(function() {
            okEl.textContent = 'Route saved!';
            okEl.style.display = 'block';
            clearRouteForm();
            loadRoutesList();
            refreshRouteDropdown();
        })
        .catch(function() { errEl.textContent = 'Network error.'; errEl.style.display='block'; });
}

function deleteRoute(id) {
    if (!confirm('Delete this route and all its service points?')) return;
    var fd = new FormData();
    fd.append(MF_CSRF_KEY, getCsrfVal());
    fetch(DEL_ROUTE_URL + '/' + id, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
        .then(function(r){ return r.json(); })
        .then(function() { loadRoutesList(); refreshRouteDropdown(); });
}

function refreshRouteDropdown() {
    fetch(ROUTES_URL, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            var sel = document.getElementById('mf-route-select');
            var cur = sel.value;
            // Keep first option
            while (sel.options.length > 1) sel.remove(1);
            (res.data || []).forEach(function(r) {
                var opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name;
                if (String(r.id) === String(cur)) opt.selected = true;
                sel.appendChild(opt);
            });
        });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s).replace(/'/g,"\\'");
}

/* ── CSV export ──────────────────────────────────────────────────────────── */
function exportManifestCSV() {
    var table = document.getElementById('mf-table');
    if (!table) { alert('No data to export.'); return; }
    var rows = table.querySelectorAll('tr');
    var csv  = [];
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('th, td');
        var line = [];
        cols.forEach(function(col) {
            var txt = col.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim();
            line.push('"' + txt + '"');
        });
        csv.push(line.join(','));
    });
    var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url;
    a.download = 'manifest_<?php echo date("Ymd"); ?>.csv';
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
}
</script>
