<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $mc = htmlspecialchars($manifest_color ?? '#1565c0', ENT_QUOTES); ?>
<style>
body { background: #e8eaf6; }
#mb-wrapper { max-width: 100%; padding: 0 18px 30px; }

.mb-topbar {
    background: #fff; border-bottom: 3px solid #1565c0; padding: 10px 18px;
    display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
    margin-bottom: 16px; position: sticky; top: 0; z-index: 100;
    box-shadow: 0 3px 10px rgba(21,101,192,.12);
}
.mb-topbar-title { font-size: 16px; font-weight: 900; letter-spacing: .5px; flex: 1; }
.mb-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border: none; border-radius: 6px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap; transition: opacity .15s;
}
.mb-btn:hover { opacity: .85; text-decoration: none; }
.mb-btn-back  { background: #546e7a; color: #fff; }
.mb-btn-print { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; }
.mb-btn-csv   { background: linear-gradient(135deg,#00838f,#006064); color: #fff; }
.mb-btn-email { background: linear-gradient(135deg,#e65100,#bf360c); color: #fff; }
.mb-btn-pdf   { background: linear-gradient(135deg,#6a1b9a,#4a148c); color: #fff; }

.mb-filter {
    background: #e3f2fd; border: 1.5px solid #90caf9; border-radius: 8px;
    padding: 12px 16px 10px; margin-bottom: 16px;
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px;
}
.mb-filter label { font-size: 11px; font-weight: 700; color: #0d47a1; display: block; margin-bottom: 3px; }
.mb-filter input, .mb-filter select {
    border: 1px solid #90caf9; border-radius: 5px;
    padding: 5px 9px; font-size: 12px; min-width: 145px;
}

/* Stats strip */
.mb-stats {
    display: flex; flex-wrap: wrap; gap: 12px;
    margin-bottom: 0; padding: 12px 16px;
    border-radius: 10px 10px 0 0; overflow: hidden;
}
.mb-stat-card {
    flex: 1; min-width: 90px; background: rgba(255,255,255,.22);
    border-radius: 8px; padding: 8px 12px; text-align: center;
    border: 1px solid rgba(255,255,255,.35);
}
.mb-stat-label { font-size: 9px; color: rgba(255,255,255,.8); text-transform: uppercase; letter-spacing: .5px; }
.mb-stat-value { font-size: 18px; font-weight: 800; color: #fff; margin-top: 2px; line-height: 1.1; }

/* Document */
.mb-document { background: #fff; border: 2px solid; border-top: none; border-radius: 0 0 10px 10px; overflow: hidden; }

/* Company header */
.mb-co-header {
    padding: 14px 20px; display: flex; align-items: center; gap: 18px;
    border-bottom: 1px solid #bbdefb;
}
.mb-co-logo img { max-width: 100px; max-height: 55px; object-fit: contain; background: rgba(255,255,255,.15); border-radius: 5px; padding: 4px; }
.mb-co-info { flex: 1; }
.mb-co-info h2 { margin: 0 0 3px; font-size: 16px; font-weight: 800; color: #fff; }
.mb-co-info p  { margin: 0; font-size: 11px; color: rgba(255,255,255,.88); line-height: 1.5; }
.mb-title-side { text-align: right; }
.mb-title-side h3 { margin: 0; font-size: 15px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
.mb-pill {
    display: inline-block; background: rgba(255,255,255,.2); border-radius: 12px;
    padding: 3px 10px; font-size: 10px; color: #fff; font-weight: 600; margin-top: 4px;
}

/* Table */
.mb-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.mb-table thead th {
    color: #fff; padding: 8px 7px;
    text-align: left; font-size: 10px; font-weight: 700; letter-spacing: .3px;
    white-space: nowrap; border-right: 1px solid rgba(255,255,255,.2);
}
.mb-table thead th:last-child { border-right: none; }
.mb-table tbody td {
    padding: 7px 7px; border-bottom: 1px solid #e3f2fd;
    border-right: 1px solid #e3f2fd; vertical-align: top;
}
.mb-table tbody td:last-child { border-right: none; }
.mb-table tbody tr:nth-child(even) td { background: #f3f8ff; }
.mb-table tbody tr:hover td { background: #e3f2fd; }
.mb-empty td { height: 22px; background: #fff !important; }
.mb-table tfoot td {
    padding: 8px 7px; font-weight: 700; color: #fff;
    font-size: 11px; border-right: 1px solid rgba(255,255,255,.2);
}
.mb-table tfoot td:last-child { border-right: none; }

/* Dynamic color */
.mb-stats         { background: <?php echo $mc; ?>; }
.mb-document      { border-color: <?php echo $mc; ?>; }
.mb-co-header     { background: <?php echo $mc; ?>; }
.mb-table thead th { background: <?php echo $mc; ?>; }
.mb-table tfoot td { background: <?php echo $mc; ?>dd; }
.mb-topbar-title  { color: <?php echo $mc; ?>; }
.mb-topbar        { border-bottom-color: <?php echo $mc; ?>; }

/* Badges */
.mb-badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:9px; font-weight:700; white-space:nowrap; }
.badge-delivered { background:#e3f2fd; color:#1565c0; border:1px solid #90caf9; }
.badge-transit   { background:#fff3e0; color:#e65100; border:1px solid #ffcc80; }
.badge-pending   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }
.badge-other     { background:#eceff1; color:#546e7a; border:1px solid #cfd8dc; }

/* Footer */
.mb-footer { border-top: 2px solid; padding: 10px 14px; background: #e3f2fd; }
.mb-footer-row { display: flex; gap: 20px; flex-wrap: wrap; }
.mb-foot-cell { flex: 1; min-width: 140px; }
.mb-foot-label { font-size: 9px; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
.mb-sig-line { border-bottom: 1.5px solid; min-height: 26px; margin-top: 5px; }
.mb-footer      { border-top-color: <?php echo $mc; ?>; }
.mb-foot-label  { color: <?php echo $mc; ?>; }
.mb-sig-line    { border-bottom-color: <?php echo $mc; ?>; }

.mf-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
.mf-modal-overlay.open { display:flex; }
.mf-modal-box { background:#fff; border-radius:10px; width:100%; max-width:470px; box-shadow:0 12px 40px rgba(0,0,0,.25); overflow:hidden; }
.mf-modal-header { background:linear-gradient(135deg,#e65100,#bf360c); padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
.mf-modal-header h4 { margin:0; color:#fff; font-size:15px; font-weight:700; }
.mf-modal-close { background:rgba(255,255,255,.2); border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; color:#fff; font-size:14px; display:flex; align-items:center; justify-content:center; }
.mf-modal-body { padding:20px 18px; }
.mf-modal-body label { font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:4px; }
.mf-modal-body input, .mf-modal-body textarea { width:100%; border:1px solid #ddd; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box; margin-bottom:12px; }
.mf-modal-footer { padding:12px 18px; background:#f5f5f5; border-top:1px solid #ddd; display:flex; gap:10px; justify-content:flex-end; }
.mf-alert { padding:8px 12px; border-radius:5px; font-size:12px; margin-top:8px; display:none; }
.mf-alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.mf-alert-error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

@media print {
    .mb-topbar, .mb-filter, .mb-no-print, .mf-modal-overlay { display: none !important; }
    body { background: #fff; }
    #mb-wrapper { padding: 0; }
    .mb-stats, .mb-co-header, .mb-table thead th, .mb-table tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .mb-document { border-radius: 0; }
}
</style>

<div id="wrapper">
<div id="mb-wrapper">

    <!-- TOP NAV -->
    <div class="mb-topbar mb-no-print">
        <span class="mb-topbar-title"><i class="fa fa-file-text"></i> Cargo Manifest</span>
        <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="mb-btn mb-btn-back">
            <i class="fa fa-arrow-left"></i> Shipment List
        </a>
        <a href="<?php echo admin_url('courier/shipments/create?type=domestic'); ?>" class="mb-btn" style="background:linear-gradient(135deg,<?php echo $mc; ?>,<?php echo $mc; ?>cc);color:#fff;">
            <i class="fa fa-plus"></i> Create Shipment
        </a>
        <button class="mb-btn mb-btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button class="mb-btn mb-btn-pdf"   onclick="downloadManifestPdf()"><i class="fa fa-file-pdf-o"></i> PDF</button>
        <button class="mb-btn mb-btn-csv"   onclick="exportManifestCSV()"><i class="fa fa-download"></i> CSV</button>
        <button class="mb-btn mb-btn-email" onclick="openMbEmailModal()"><i class="fa fa-envelope"></i> Email</button>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="" class="mb-filter mb-no-print">
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
                    $drv_id = (int)($drv['staffid'] ?? 0);
                    $drv_nm = trim(($drv['firstname'] ?? '') . ' ' . ($drv['lastname'] ?? ''));
                    $sel    = ((string)($filter_driver_id ?? '') === (string)$drv_id) ? 'selected' : '';
                ?>
                <option value="<?php echo $drv_id; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($drv_nm); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:flex-end;">
            <button type="submit" class="mb-btn" style="background:<?php echo $mc; ?>;color:#fff; padding:7px 16px;">
                <i class="fa fa-filter"></i> Filter
            </button>
            <a href="?group=manifests" class="mb-btn mb-btn-back" style="padding:7px 14px;">
                <i class="fa fa-times"></i> Clear
            </a>
        </div>
    </form>

    <!-- STATS STRIP -->
    <div class="mb-stats">
        <div class="mb-stat-card"><div class="mb-stat-label">Shipments</div><div class="mb-stat-value"><?php echo count($mf_rows); ?></div></div>
        <div class="mb-stat-card"><div class="mb-stat-label">Pieces</div><div class="mb-stat-value"><?php echo number_format($mf_total_qty); ?></div></div>
        <div class="mb-stat-card"><div class="mb-stat-label">Weight</div><div class="mb-stat-value"><?php echo number_format($mf_total_weight, 1); ?> <span style="font-size:11px;">kg</span></div></div>
        <div class="mb-stat-card"><div class="mb-stat-label">Charges</div><div class="mb-stat-value"><?php echo number_format($mf_total_charges, 2); ?></div></div>
        <div class="mb-stat-card"><div class="mb-stat-label">VAT</div><div class="mb-stat-value"><?php echo number_format($mf_total_vat, 2); ?></div></div>
        <div class="mb-stat-card"><div class="mb-stat-label">Grand Total</div><div class="mb-stat-value"><?php echo number_format($mf_total_amount, 2); ?></div></div>
    </div>

    <!-- DOCUMENT -->
    <div class="mb-document" id="mb-document">

        <!-- Company header -->
        <div class="mb-co-header">
            <div>
                <?php if (!empty($_mf_logo_url)): ?>
                    <img src="<?php echo $_mf_logo_url; ?>" alt="" style="max-width:100px;max-height:55px;object-fit:contain;background:rgba(255,255,255,.15);border-radius:5px;padding:4px;">
                <?php else: ?>
                    <div style="width:80px;height:50px;background:rgba(255,255,255,.12);border-radius:5px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.5);font-size:22px;"><i class="fa fa-building"></i></div>
                <?php endif; ?>
            </div>
            <div class="mb-co-info">
                <h2><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></h2>
                <?php if ($_mf_address): ?><p><i class="fa fa-map-marker"></i> <?php echo nl2br(htmlspecialchars($_mf_address)); ?></p><?php endif; ?>
                <p>
                    <?php if ($_mf_phone): ?><i class="fa fa-phone"></i> <?php echo htmlspecialchars($_mf_phone); ?><?php endif; ?>
                    <?php if ($_mf_email_co): ?> &nbsp;|&nbsp; <i class="fa fa-envelope"></i> <?php echo htmlspecialchars($_mf_email_co); ?><?php endif; ?>
                </p>
            </div>
            <div class="mb-title-side">
                <h3><i class="fa fa-file-text-o"></i> Cargo Manifest</h3>
                <div class="mb-pill"><i class="fa fa-calendar"></i> <?php echo $mf_from_label; ?> &mdash; <?php echo $mf_to_label; ?></div>
                <?php if ($mf_driver_label): ?><div class="mb-pill" style="margin-top:3px;"><i class="fa fa-user"></i> <?php echo htmlspecialchars($mf_driver_label); ?></div><?php endif; ?>
            </div>
        </div>

        <!-- Table -->
        <div style="overflow-x:auto;">
            <table class="mb-table" id="mf-table">
                <thead>
                    <tr>
                        <th style="width:26px; text-align:center;">#</th>
                        <th style="min-width:150px;">Cargo Name / Sender</th>
                        <th style="min-width:100px;">Agent</th>
                        <th style="width:55px; text-align:center;">Kgs</th>
                        <th style="min-width:120px;">Client / Receiver</th>
                        <th style="min-width:110px;">Phone / AWB</th>
                        <th style="width:75px; text-align:right;">Amount</th>
                        <th style="width:55px; text-align:right;">VAT</th>
                        <th style="min-width:140px;">POD / Destination</th>
                        <th style="width:80px; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $mb_num = 0;
                if (!empty($mf_rows)):
                    foreach ($mf_rows as $row):
                        $mb_num++;
                        $sl = strtolower($row['status']);
                        if     (strpos($sl,'deliver')!==false)                                $bc='badge-delivered';
                        elseif (strpos($sl,'transit')!==false||strpos($sl,'pickup')!==false)  $bc='badge-transit';
                        elseif (strpos($sl,'pending')!==false||strpos($sl,'cancel')!==false)  $bc='badge-pending';
                        else                                                                   $bc='badge-other';
                        $pod_lines = !empty($row['pod_lines']) ? $row['pod_lines'] : [];
                        $row_span  = max(1, count($pod_lines));
                        $is_first  = true;
                ?>
                <?php if (!empty($pod_lines)): ?>
                    <?php foreach ($pod_lines as $pi => $pl): ?>
                    <tr<?php if (!$is_first): ?> style="background:<?php echo $pi%2===0?'#f3f8ff':'#fff'; ?>!important;"<?php endif; ?>>
                        <?php if ($is_first): ?>
                        <td style="text-align:center;color:#999;font-weight:700;" rowspan="<?php echo $row_span; ?>"><?php echo $mb_num; ?></td>
                        <td rowspan="<?php echo $row_span; ?>">
                            <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                            <?php if ($row['sender_phone']!=='-'): ?><div style="font-size:10px;color:#555;"><i class="fa fa-phone" style="font-size:8px;"></i> <?php echo htmlspecialchars($row['sender_phone']); ?></div><?php endif; ?>
                        </td>
                        <td style="font-size:10px;" rowspan="<?php echo $row_span; ?>"><?php echo htmlspecialchars($row['driver_name']); ?></td>
                        <?php endif; ?>
                        <td style="text-align:center;font-weight:700;"><?php echo number_format($pl['weight'],2); ?></td>
                        <?php if ($is_first): ?>
                        <td style="font-weight:700;" rowspan="<?php echo $row_span; ?>"><?php echo htmlspecialchars($row['recv_name']); ?></td>
                        <td rowspan="<?php echo $row_span; ?>">
                            <div style="font-size:10px;color:#444;"><?php echo htmlspecialchars($row['recv_phone']); ?></div>
                            <div style="font-size:9px;font-weight:700;margin-top:2px;"><?php echo htmlspecialchars($row['waybill']); ?></div>
                        </td>
                        <td style="text-align:right;" rowspan="<?php echo $row_span; ?>"><?php echo number_format((float)$row['charges'],2); ?></td>
                        <td style="text-align:right;" rowspan="<?php echo $row_span; ?>"><?php echo number_format((float)$row['vat'],2); ?></td>
                        <?php endif; ?>
                        <td>
                            <strong><?php echo htmlspecialchars($pl['pod']); ?></strong>
                            <?php if (!empty($pl['desc'])): ?><div style="font-size:9px;color:#777;font-style:italic;"><?php echo htmlspecialchars(mb_strimwidth($pl['desc'],0,55,'..')); ?></div><div style="font-size:9px;color:#888;">Qty:<?php echo $pl['qty']; ?> &bull; <?php echo number_format($pl['weight'],2); ?>kg</div><?php endif; ?>
                        </td>
                        <?php if ($is_first): ?>
                        <td style="text-align:center;" rowspan="<?php echo $row_span; ?>"><span class="mb-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <?php endif; ?>
                    </tr>
                    <?php $is_first = false; endforeach; ?>
                <?php else: ?>
                <tr>
                    <td style="text-align:center;color:#999;font-weight:700;"><?php echo $mb_num; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['sender_name']); ?></strong><?php if ($row['sender_phone']!=='-'): ?><div style="font-size:10px;color:#555;"><?php echo htmlspecialchars($row['sender_phone']); ?></div><?php endif; ?></td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars($row['driver_name']); ?></td>
                    <td style="text-align:center;font-weight:700;"><?php echo number_format((float)$row['pkg_weight'],2); ?></td>
                    <td style="font-weight:700;"><?php echo htmlspecialchars($row['recv_name']); ?></td>
                    <td><div style="font-size:10px;color:#444;"><?php echo htmlspecialchars($row['recv_phone']); ?></div><div style="font-size:9px;font-weight:700;"><?php echo htmlspecialchars($row['waybill']); ?></div></td>
                    <td style="text-align:right;"><?php echo number_format((float)$row['charges'],2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$row['vat'],2); ?></td>
                    <td style="font-size:10px;color:#444;"><?php echo htmlspecialchars($row['recv_addr']); ?></td>
                    <td style="text-align:center;"><span class="mb-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                </tr>
                <?php endif; ?>
                <?php endforeach;
                    for ($b=0;$b<max(0,5-$mb_num);$b++): ?>
                <tr class="mb-empty"><td style="text-align:center;color:#ddd;"><?php echo $mb_num+$b+1; ?></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; else: for($b=1;$b<=20;$b++): ?>
                <tr class="mb-empty"><td style="text-align:center;color:#ddd;"><?php echo $b; ?></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;">Total — <?php echo $mb_num; ?> shipments</td>
                        <td style="text-align:center;font-weight:800;"><?php echo number_format($mf_total_weight,2); ?> kg</td>
                        <td colspan="3" style="text-align:right;">Charges + VAT:</td>
                        <td style="text-align:right;font-weight:800;"><?php echo number_format($mf_total_amount,2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="mb-footer">
            <div class="mb-footer-row">
                <div class="mb-foot-cell" style="flex:2;">
                    <span class="mb-foot-label">Authorized Signature</span>
                    <div class="mb-sig-line"></div>
                </div>
                <div class="mb-foot-cell">
                    <span class="mb-foot-label">Date</span>
                    <div class="mb-sig-line" style="font-size:12px;padding-top:3px;"><?php echo date('d / m / Y'); ?></div>
                </div>
                <div class="mb-foot-cell">
                    <span class="mb-foot-label">Printed By</span>
                    <div class="mb-sig-line" style="font-size:11px;padding-top:3px;"><?php echo htmlspecialchars($mf_printed_by); ?></div>
                </div>
            </div>
            <div style="margin-top:8px;text-align:center;font-size:9px;color:#90a4ae;border-top:1px solid #bbdefb;padding-top:5px;">
                Generated <?php echo date('d M Y, H:i'); ?> &nbsp;|&nbsp; <?php echo htmlspecialchars($_mf_company); ?>
            </div>
        </div>

    </div><!-- /mb-document -->
</div><!-- /mb-wrapper -->
</div><!-- /wrapper -->

<!-- EMAIL MODAL -->
<div class="mf-modal-overlay" id="mb-email-modal">
    <div class="mf-modal-box">
        <div class="mf-modal-header"><h4><i class="fa fa-envelope"></i> Send Manifest</h4><button class="mf-modal-close" onclick="closeMbEmailModal()"><i class="fa fa-times"></i></button></div>
        <div class="mf-modal-body">
            <label>Recipient Email *</label><input type="email" id="mb-to-email" placeholder="email@example.com">
            <label>Subject</label><input type="text" id="mb-subject" value="Cargo Manifest — <?php echo $mf_from_label; ?> to <?php echo $mf_to_label; ?>">
            <label>Note</label><textarea id="mb-note" rows="3"></textarea>
            <div class="mf-alert mf-alert-success" id="mb-ok"><span id="mb-ok-msg"></span></div>
            <div class="mf-alert mf-alert-error"   id="mb-err"><span id="mb-err-msg"></span></div>
        </div>
        <div class="mf-modal-footer">
            <button class="mb-btn mb-btn-back" onclick="closeMbEmailModal()">Cancel</button>
            <button class="mb-btn mb-btn-email" id="mb-send-btn" onclick="sendMbEmail()"><i class="fa fa-paper-plane"></i> Send</button>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function openMbEmailModal()  { document.getElementById('mb-email-modal').classList.add('open'); }
function closeMbEmailModal() { document.getElementById('mb-email-modal').classList.remove('open'); }
document.getElementById('mb-email-modal').addEventListener('click',function(e){if(e.target===this)closeMbEmailModal();});
function sendMbEmail(){
    var to=document.getElementById('mb-to-email').value.trim();
    if(!to){document.getElementById('mb-err-msg').textContent='Enter recipient email.';document.getElementById('mb-err').style.display='block';return;}
    var btn=document.getElementById('mb-send-btn');btn.disabled=true;btn.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
    document.getElementById('mb-ok').style.display=document.getElementById('mb-err').style.display='none';
    $.ajax({url:'<?php echo admin_url("courier/shipments/send_manifest_email"); ?>',type:'POST',
        data:{<?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>',to_email:to,subject:document.getElementById('mb-subject').value,
              date_from:'<?php echo htmlspecialchars($filter_date_from ?? date("Y-m-01")); ?>',date_to:'<?php echo htmlspecialchars($filter_date_to ?? date("Y-m-d")); ?>',driver_id:'<?php echo (int)($filter_driver_id??0); ?>'},
        dataType:'json',
        success:function(res){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';
            if(res.success){document.getElementById('mb-ok-msg').textContent=res.message;document.getElementById('mb-ok').style.display='block';setTimeout(closeMbEmailModal,2600);}
            else{document.getElementById('mb-err-msg').textContent=res.message;document.getElementById('mb-err').style.display='block';}},
        error:function(){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';document.getElementById('mb-err-msg').textContent='Network error.';document.getElementById('mb-err').style.display='block';}
    });
}
function exportManifestCSV(){
    var t=document.getElementById('mf-table');if(!t){alert('No data.');return;}
    var csv=[];t.querySelectorAll('tr').forEach(function(r){var l=[];r.querySelectorAll('th,td').forEach(function(c){l.push('"'+c.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim()+'"');});csv.push(l.join(','));});
    var b=new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});var u=URL.createObjectURL(b);var a=document.createElement('a');a.href=u;a.download='manifest_<?php echo date("Ymd"); ?>.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(u);
}
function downloadManifestPdf() {
    var btn = document.querySelector('.mb-btn-pdf');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>…'; }
    var el = document.getElementById('mb-document');
    var from = '<?php echo date('Y-m-d', strtotime($filter_date_from ?? date('Y-m-01'))); ?>';
    var to   = '<?php echo date('Y-m-d', strtotime($filter_date_to   ?? date('Y-m-d'))); ?>';
    html2pdf().set({
        margin: [6, 6, 6, 6],
        filename: 'manifest-' + from + '-to-' + to + '.pdf',
        image: { type: 'jpeg', quality: 0.97 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    }).from(el).save().then(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> PDF'; }
    });
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
