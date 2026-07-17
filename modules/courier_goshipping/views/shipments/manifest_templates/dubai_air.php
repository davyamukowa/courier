<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $mc = htmlspecialchars($manifest_color ?? '#1a237e', ENT_QUOTES); ?>
<?php $mf_rate = get_option('courier_rate_air_freight') ?: '-'; ?>
<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
#da-wrapper { max-width: 100%; padding: 0 14px 30px; }

/* ── Topbar ── */
.da-topbar {
    background: #fff; border-bottom: 2px solid #ddd;
    padding: 8px 14px; display: flex; align-items: center;
    flex-wrap: wrap; gap: 8px; margin-bottom: 14px;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 2px 6px rgba(0,0,0,.08);
}
.da-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border: none; border-radius: 5px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap;
}
.da-btn:hover { opacity: .85; text-decoration: none; }
.da-btn-back  { background: #546e7a; color: #fff; }
.da-btn-print { background: #1565c0; color: #fff; }
.da-btn-csv   { background: #00838f; color: #fff; }
.da-btn-email { background: #e65100; color: #fff; }
.da-btn-pdf   { background: #6a1b9a; color: #fff; }

/* ── Filter bar ── */
.da-filter {
    background: #e8eaf6; border: 1.5px solid #9fa8da;
    border-radius: 7px; padding: 10px 14px 8px;
    margin-bottom: 14px; display: flex; flex-wrap: wrap;
    align-items: flex-end; gap: 12px;
}
.da-filter label { font-size: 11px; font-weight: 700; color: #283593; display: block; margin-bottom: 3px; }
.da-filter input, .da-filter select {
    border: 1px solid #9fa8da; border-radius: 4px;
    padding: 5px 8px; font-size: 12px; min-width: 140px;
}

/* ── Document ── */
.da-document { background: #fff; border: 1.5px solid #333; }

/* ── Title header ── */
.da-title-row {
    text-align: center; padding: 12px 10px 6px; border-bottom: 1px solid #333;
}
.da-title-row h2 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: 1px; color: #111; }
.da-title-row .da-awb-row { font-size: 13px; color: #333; margin-top: 4px; font-weight: 600; }
.da-awb-input {
    border: none; border-bottom: 1.5px solid #333; background: transparent;
    font-size: 13px; font-weight: 700; color: #111; text-align: center;
    min-width: 120px; outline: none; padding: 0 4px;
}
@media print { .da-awb-input { border-bottom-color: #999; } }

/* ── Company addresses ── */
.da-addr-row {
    display: flex; border-bottom: 1.5px solid #333;
}
.da-addr-cell {
    flex: 1; padding: 10px 12px; border-right: 1.5px solid #333;
}
.da-addr-cell:last-child { border-right: none; }
.da-addr-logo { max-width: 90px; max-height: 55px; object-fit: contain; margin-bottom: 6px; }
.da-addr-cell strong { display: block; font-size: 13px; font-weight: 800; margin-bottom: 4px; color: #111; }
.da-addr-cell p { margin: 0; font-size: 11px; color: #444; line-height: 1.6; }

/* ── Table ── */
.da-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.da-table th {
    background: #111; color: #fff; padding: 6px 5px;
    text-align: left; font-size: 10px; font-weight: 700;
    letter-spacing: .3px; border-right: 1px solid #444;
    white-space: nowrap;
}
.da-table th:last-child { border-right: none; }
.da-table td {
    padding: 5px 5px; border-bottom: 1px solid #ddd;
    border-right: 1px solid #eee; font-size: 11px; vertical-align: middle;
}
.da-table td:last-child { border-right: none; }
.da-table tbody tr:nth-child(even) td { background: #f9f9f9; }
.da-table tbody tr.da-empty td { height: 22px; background: #fff !important; }
.da-table tfoot td {
    background: #111; color: #fff; padding: 6px 5px;
    font-weight: 700; font-size: 11px; border-right: 1px solid #444;
}
.da-table tfoot td:last-child { border-right: none; }

/* ── Signature footer ── */
.da-footer { border-top: 1.5px solid #333; padding: 10px 14px; display: flex; gap: 20px; }
.da-foot-cell { flex: 1; }
.da-foot-label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #555; display: block; margin-bottom: 4px; }
.da-sig-line { border-bottom: 1px solid #333; min-height: 24px; margin-top: 4px; }

/* ── Modals (shared with cargo_green) ── */
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
.mf-modal-header { background: linear-gradient(135deg,#e65100,#bf360c); padding: 14px 18px; display:flex; align-items:center; justify-content:space-between; }
.mf-modal-header h4 { margin:0; color:#fff; font-size:15px; font-weight:700; }
.mf-modal-close { background:rgba(255,255,255,.2); border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; color:#fff; font-size:14px; display:flex; align-items:center; justify-content:center; }
.mf-modal-body { padding: 18px; }
.mf-modal-body label { font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:4px; }
.mf-modal-body input, .mf-modal-body textarea { width:100%; border:1px solid #ddd; border-radius:5px; padding:7px 9px; font-size:13px; box-sizing:border-box; margin-bottom:12px; }
.mf-modal-footer { padding:12px 18px; background:#f5f5f5; border-top:1px solid #ddd; display:flex; gap:10px; justify-content:flex-end; }
.mf-alert { padding:8px 12px; border-radius:5px; font-size:12px; margin-top:8px; display:none; }
.mf-alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.mf-alert-error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

/* ── Dynamic accent on table header/footer ── */
.da-table th     { background: <?php echo $mc; ?>; }
.da-table tfoot td { background: <?php echo $mc; ?>; }

/* ── Print ── */
@media print {
    .da-topbar, .da-filter, .da-no-print, .mf-modal-overlay { display: none !important; }
    body { background: #fff; }
    #da-wrapper { padding: 0; }
    .da-table th, .da-table tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .da-awb-input { border-bottom: 1px solid #999; }
}
</style>

<div id="wrapper">
<div id="da-wrapper">

    <!-- ══ TOP NAV ══ -->
    <div class="da-topbar da-no-print">
        <a href="<?php echo admin_url('courier_goshipping/shipments?type=domestic'); ?>" class="da-btn da-btn-back">
            <i class="fa fa-arrow-left"></i> Shipment List
        </a>
        <span style="font-size:14px; font-weight:800; color:#283593; flex:1; margin-left:6px;">
            <i class="fa fa-file-text"></i> Air Manifest
        </span>
        <button class="da-btn da-btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> Print Manifest
        </button>
        <button class="da-btn da-btn-pdf" onclick="downloadManifestPdf()">
            <i class="fa fa-file-pdf-o"></i> Download PDF
        </button>
        <button class="da-btn da-btn-csv" onclick="exportManifestCSV()">
            <i class="fa fa-download"></i> Export CSV
        </button>
        <button class="da-btn da-btn-email" onclick="openDaEmailModal()">
            <i class="fa fa-envelope"></i> Send to Email
        </button>
    </div>

    <!-- ══ FILTER BAR ══ -->
    <form method="GET" action="" class="da-filter da-no-print">
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
                <option value="">All Agents</option>
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
            <button type="submit" class="da-btn" style="background:#283593;color:#fff;">
                <i class="fa fa-filter"></i> Filter
            </button>
            <a href="?group=manifests" class="da-btn da-btn-back">
                <i class="fa fa-times"></i> Clear
            </a>
        </div>
    </form>

    <!-- ══ DOCUMENT ══ -->
    <div class="da-document" id="da-document">

        <!-- Title -->
        <div class="da-title-row">
            <h2>
                <input class="da-awb-input" style="font-size:20px;font-weight:900;min-width:320px;"
                       placeholder="MANIFEST TITLE (e.g. DUBAI AIR MANIFEST <?php echo strtoupper(date('F Y')); ?>)"
                       value="AIR MANIFEST <?php echo strtoupper(date('F Y')); ?>">
            </h2>
            <div class="da-awb-row">
                AWB&nbsp;
                <input class="da-awb-input" placeholder="AWB Number" style="min-width:140px;">
                &nbsp;&nbsp;FLIGHT&nbsp;
                <input class="da-awb-input" placeholder="Flight No." style="min-width:100px;">
                &nbsp;&mdash;&nbsp;
                <input class="da-awb-input" placeholder="Date e.g. <?php echo date('j/n/y'); ?>" style="min-width:80px;" value="<?php echo date('j/n/y'); ?>">
            </div>
        </div>

        <!-- Company addresses -->
        <div class="da-addr-row">
            <div class="da-addr-cell" style="display:flex; gap:12px; align-items:flex-start;">
                <div>
                    <?php if (!empty($_mf_logo_url)): ?>
                        <img src="<?php echo $_mf_logo_url; ?>" class="da-addr-logo" alt="">
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></strong>
                    <?php if ($_mf_address): ?>
                        <p><?php echo nl2br(htmlspecialchars($_mf_address)); ?></p>
                    <?php endif; ?>
                    <?php if ($_mf_phone): ?><p>Tel: <?php echo htmlspecialchars($_mf_phone); ?></p><?php endif; ?>
                    <?php if ($_mf_email_co): ?><p><?php echo htmlspecialchars($_mf_email_co); ?></p><?php endif; ?>
                </div>
            </div>
            <div class="da-addr-cell">
                <strong><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></strong>
                <p contenteditable="true" style="outline:none; border-bottom:1px dashed #ccc; min-height:16px;">
                    Destination Office / Agent Name
                </p>
                <p contenteditable="true" style="outline:none; border-bottom:1px dashed #ccc; min-height:16px; margin-top:4px;">
                    City / Country
                </p>
                <p contenteditable="true" style="outline:none; border-bottom:1px dashed #ccc; min-height:16px; margin-top:4px;">
                    Tel:
                </p>
            </div>
        </div>

        <!-- Data table -->
        <div style="overflow-x:auto;">
            <table class="da-table" id="mf-table">
                <thead>
                    <tr>
                        <th style="width:70px;">DATE</th>
                        <th style="min-width:100px;">SENDER</th>
                        <th style="min-width:90px;">RCVR</th>
                        <th style="width:90px;">PHONE</th>
                        <th style="width:90px;">AWB#</th>
                        <th style="min-width:90px;">DESC</th>
                        <th style="width:36px; text-align:center;">PCS</th>
                        <th style="width:50px; text-align:right;">KGS</th>
                        <th style="width:48px; text-align:right;">RATE</th>
                        <th style="width:60px; text-align:right;">USD</th>
                        <th style="width:70px; text-align:center;">STAT</th>
                        <th style="width:55px; text-align:center;">PACK</th>
                        <th style="width:50px; text-align:center;">DEST</th>
                        <th style="min-width:70px;">RMKS</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $da_row_num = 0;
                if (!empty($mf_rows)):
                    foreach ($mf_rows as $row):
                        $da_row_num++;
                        $dest_pod = !empty($row['pod_lines'][0]['pod']) ? $row['pod_lines'][0]['pod'] : $row['recv_addr'];
                        // Shorten destination to 3-char code if possible
                        $dest_short = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dest_pod), 0, 3));
                ?>
                <tr>
                    <td><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                    <td style="font-weight:700;"><?php echo htmlspecialchars($row['sender_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['recv_name']); ?></td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars($row['recv_phone']); ?></td>
                    <td style="font-size:10px; font-weight:700;"><?php echo htmlspecialchars($row['waybill']); ?></td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars(mb_strimwidth($row['pkg_desc'], 0, 40, '..')); ?></td>
                    <td style="text-align:center;"><?php echo $row['pkg_qty']; ?></td>
                    <td style="text-align:right; font-weight:700;"><?php echo number_format((float)$row['pkg_weight'], 2); ?></td>
                    <td style="text-align:right;"><?php echo htmlspecialchars($mf_rate); ?></td>
                    <td style="text-align:right; font-weight:700;"><?php echo number_format((float)$row['total'], 2); ?></td>
                    <td style="text-align:center; font-size:9px; font-weight:700; text-transform:uppercase;"><?php echo htmlspecialchars(substr($row['status'], 0, 8)); ?></td>
                    <td style="text-align:center; font-size:10px;"><?php echo htmlspecialchars(strtoupper($row['mode'])); ?></td>
                    <td style="text-align:center; font-weight:700; font-size:10px;"><?php echo htmlspecialchars($dest_short ?: '-'); ?></td>
                    <td contenteditable="true" style="outline:none; font-size:10px; color:#555;"></td>
                </tr>
                <?php endforeach;
                    for ($b = 0; $b < max(0, 5 - $da_row_num); $b++): ?>
                <tr class="da-empty">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <?php endfor;
                else:
                    for ($b = 0; $b < 15; $b++): ?>
                <tr class="da-empty">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <?php endfor; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align:right;">TOTAL — <?php echo $da_row_num; ?> shipments</td>
                        <td style="text-align:center;"><?php echo number_format($mf_total_qty); ?></td>
                        <td style="text-align:right;"><?php echo number_format($mf_total_weight, 2); ?></td>
                        <td></td>
                        <td style="text-align:right; font-weight:800;"><?php echo number_format($mf_total_amount, 2); ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signature footer -->
        <div class="da-footer">
            <div class="da-foot-cell" style="flex:2;">
                <span class="da-foot-label">Authorized Signature</span>
                <div class="da-sig-line"></div>
            </div>
            <div class="da-foot-cell">
                <span class="da-foot-label">Date</span>
                <div class="da-sig-line" style="font-size:12px; padding-top:3px;"><?php echo date('d / m / Y'); ?></div>
            </div>
            <div class="da-foot-cell">
                <span class="da-foot-label">Printed By</span>
                <div class="da-sig-line" style="font-size:11px; padding-top:3px;"><?php echo htmlspecialchars($mf_printed_by); ?></div>
            </div>
            <div style="flex:0 0 auto; font-size:9px; color:#aaa; align-self:flex-end; text-align:right;">
                Generated <?php echo date('d M Y, H:i'); ?>
            </div>
        </div>

    </div><!-- /da-document -->
</div><!-- /da-wrapper -->
</div><!-- /wrapper -->

<!-- ══ SEND TO EMAIL MODAL ══ -->
<div class="mf-modal-overlay" id="da-email-modal">
    <div class="mf-modal-box">
        <div class="mf-modal-header">
            <h4><i class="fa fa-envelope"></i>&nbsp; Send Manifest by Email</h4>
            <button class="mf-modal-close" onclick="closeDaEmailModal()"><i class="fa fa-times"></i></button>
        </div>
        <div class="mf-modal-body">
            <label>Recipient Email <span style="color:#c62828;">*</span></label>
            <input type="email" id="da-to-email" placeholder="e.g. agent@company.com">
            <label>Subject</label>
            <input type="text" id="da-subject" value="Air Manifest — <?php echo $mf_from_label; ?> to <?php echo $mf_to_label; ?>">
            <label>Note (optional)</label>
            <textarea id="da-note" rows="3" placeholder="Additional message…"></textarea>
            <div class="mf-alert mf-alert-success" id="da-email-ok"><i class="fa fa-check-circle"></i>&nbsp;<span id="da-ok-msg"></span></div>
            <div class="mf-alert mf-alert-error"   id="da-email-err"><i class="fa fa-exclamation-triangle"></i>&nbsp;<span id="da-err-msg"></span></div>
        </div>
        <div class="mf-modal-footer">
            <button class="da-btn da-btn-back" onclick="closeDaEmailModal()">Cancel</button>
            <button class="da-btn da-btn-email" id="da-send-btn" onclick="sendDaEmail()">
                <i class="fa fa-paper-plane"></i> Send
            </button>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
function openDaEmailModal()  { document.getElementById('da-email-modal').classList.add('open'); }
function closeDaEmailModal() { document.getElementById('da-email-modal').classList.remove('open'); }
document.getElementById('da-email-modal').addEventListener('click', function(e){ if(e.target===this) closeDaEmailModal(); });

function sendDaEmail() {
    var toEmail = document.getElementById('da-to-email').value.trim();
    if (!toEmail) { document.getElementById('da-err-msg').textContent='Please enter a recipient email.'; document.getElementById('da-email-err').style.display='block'; return; }
    var btn = document.getElementById('da-send-btn');
    btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending…';
    document.getElementById('da-email-ok').style.display = document.getElementById('da-email-err').style.display = 'none';
    $.ajax({
        url: '<?php echo admin_url("courier_goshipping/shipments/send_manifest_email"); ?>',
        type: 'POST',
        data: {
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>',
            to_email: toEmail,
            subject:  document.getElementById('da-subject').value,
            date_from:'<?php echo htmlspecialchars($filter_date_from ?? date("Y-m-01")); ?>',
            date_to:  '<?php echo htmlspecialchars($filter_date_to   ?? date("Y-m-d")); ?>',
            driver_id:'<?php echo (int)($filter_driver_id ?? 0); ?>'
        },
        dataType:'json',
        success: function(res){
            btn.disabled=false; btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';
            if(res.success){ document.getElementById('da-ok-msg').textContent=res.message; document.getElementById('da-email-ok').style.display='block'; setTimeout(closeDaEmailModal,2600); }
            else { document.getElementById('da-err-msg').textContent=res.message; document.getElementById('da-email-err').style.display='block'; }
        },
        error: function(){ btn.disabled=false; btn.innerHTML='<i class="fa fa-paper-plane"></i> Send'; document.getElementById('da-err-msg').textContent='Network error.'; document.getElementById('da-email-err').style.display='block'; }
    });
}

function exportManifestCSV() {
    var table = document.getElementById('mf-table');
    if (!table) { alert('No data to export.'); return; }
    var csv = [];
    table.querySelectorAll('tr').forEach(function(row){
        var line = [];
        row.querySelectorAll('th,td').forEach(function(c){ line.push('"'+c.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim()+'"'); });
        csv.push(line.join(','));
    });
    var blob = new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href=url; a.download='air_manifest_<?php echo date("Ymd"); ?>.csv';
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
}

function downloadManifestPdf() {
    var btn = document.querySelector('.da-btn-pdf');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating…'; }
    var el = document.getElementById('da-document');
    var from = '<?php echo date('Y-m-d', strtotime($filter_date_from ?? date('Y-m-01'))); ?>';
    var to   = '<?php echo date('Y-m-d', strtotime($filter_date_to   ?? date('Y-m-d'))); ?>';
    html2pdf().set({
        margin: [6, 6, 6, 6],
        filename: 'manifest-' + from + '-to-' + to + '.pdf',
        image: { type: 'jpeg', quality: 0.97 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    }).from(el).save().then(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Download PDF'; }
    });
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
