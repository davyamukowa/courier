<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $mc = htmlspecialchars($manifest_color ?? '#212121', ENT_QUOTES); ?>
<style>
body { background: #f5f5f5; font-family: "Times New Roman", serif; }
#ct-wrapper { max-width: 100%; padding: 0 14px 30px; }

.ct-topbar {
    background: #fff; border-bottom: 2px solid #ccc; padding: 8px 14px;
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    margin-bottom: 14px; position: sticky; top: 0; z-index: 100;
    box-shadow: 0 1px 4px rgba(0,0,0,.1); font-family: Arial, sans-serif;
}
.ct-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border: none; border-radius: 4px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap; font-family: Arial, sans-serif;
}
.ct-btn:hover { opacity: .85; text-decoration: none; }
.ct-btn-back  { background: #546e7a; color: #fff; }
.ct-btn-print { background: #1565c0; color: #fff; }
.ct-btn-csv   { background: #00838f; color: #fff; }
.ct-btn-email { background: #e65100; color: #fff; }
.ct-btn-pdf   { background: #6a1b9a; color: #fff; }

.ct-filter {
    background: #fafafa; border: 1px solid #ccc; border-radius: 5px;
    padding: 10px 14px 8px; margin-bottom: 14px;
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;
    font-family: Arial, sans-serif;
}
.ct-filter label { font-size: 11px; font-weight: 700; color: #555; display: block; margin-bottom: 3px; }
.ct-filter input, .ct-filter select { border: 1px solid #bbb; border-radius: 3px; padding: 5px 8px; font-size: 12px; min-width: 140px; }

/* Document */
.ct-document { background: #fff; border: 2px solid #333; }

.ct-head { padding: 18px 16px 10px; border-bottom: 3px double #333; text-align: center; }
.ct-head h1 { margin: 0 0 4px; font-size: 22px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
.ct-head-sub { font-size: 12px; color: #555; }
.ct-head-meta { display: flex; justify-content: space-between; margin-top: 10px; font-size: 11px; }
.ct-head-meta span { font-weight: 700; }

.ct-company-row { display: flex; border-bottom: 1.5px solid #333; }
.ct-company-cell { flex: 1; padding: 8px 12px; font-size: 11px; }
.ct-company-cell:first-child { border-right: 1px solid #333; display: flex; gap: 10px; align-items: flex-start; }
.ct-company-cell strong { display: block; font-size: 13px; font-weight: 800; margin-bottom: 3px; }
.ct-company-cell p { margin: 0; line-height: 1.6; color: #444; }
.ct-logo { max-width: 80px; max-height: 50px; object-fit: contain; }

/* Table */
.ct-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.ct-table th {
    color: #fff; padding: 7px 6px;
    text-align: left; font-size: 10px; font-weight: 700;
    border-right: 1px solid rgba(255,255,255,.25); white-space: nowrap;
}
.ct-table th:last-child { border-right: none; }
.ct-table td {
    padding: 6px 6px; border-bottom: 1px solid #ddd;
    border-right: 1px solid #eee; font-size: 11px; vertical-align: top;
}
.ct-table td:last-child { border-right: none; }
.ct-table tbody tr:nth-child(even) td { background: #f9f9f9; }
.ct-table tbody tr.ct-empty td { height: 22px; background: #fff !important; }
.ct-table tfoot td {
    padding: 7px 6px; font-weight: 700; color: #fff;
    font-size: 11px; border-right: 1px solid rgba(255,255,255,.25);
}
.ct-table tfoot td:last-child { border-right: none; }

.ct-badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:9px; font-weight:700; border:1px solid; white-space:nowrap; }
.ct-badge-ok  { background:#f0fff0; color:#1a6b1a; border-color:#7cbc7c; }
.ct-badge-mov { background:#fff8e1; color:#7a5c00; border-color:#d4a800; }
.ct-badge-pnd { background:#fff0f0; color:#901c1c; border-color:#e07070; }
.ct-badge-oth { background:#f5f5f5; color:#555; border-color:#bbb; }

/* Dynamic accent */
.ct-table th      { background: <?php echo $mc; ?>; }
.ct-table tfoot td { background: <?php echo $mc; ?>; }

.ct-footer { border-top: 2px solid #333; padding: 10px 14px; display: flex; gap: 20px; font-family: Arial, sans-serif; }
.ct-foot-cell { flex: 1; }
.ct-foot-label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #555; display: block; margin-bottom: 4px; font-family: Arial, sans-serif; }
.ct-sig-line { border-bottom: 1px solid #333; min-height: 24px; margin-top: 4px; }

.mf-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999;
    align-items: center; justify-content: center;
}
.mf-modal-overlay.open { display: flex; }
.mf-modal-box { background: #fff; border-radius: 8px; width: 100%; max-width: 460px; box-shadow: 0 10px 35px rgba(0,0,0,.25); overflow: hidden; font-family: Arial, sans-serif; }
.mf-modal-header { background:#e65100; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
.mf-modal-header h4 { margin:0; color:#fff; font-size:14px; font-weight:700; }
.mf-modal-close { background:rgba(255,255,255,.2); border:none; border-radius:50%; width:26px; height:26px; cursor:pointer; color:#fff; font-size:13px; }
.mf-modal-body { padding:18px; }
.mf-modal-body label { font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:4px; }
.mf-modal-body input, .mf-modal-body textarea { width:100%; border:1px solid #ddd; border-radius:4px; padding:7px 9px; font-size:13px; box-sizing:border-box; margin-bottom:12px; }
.mf-modal-footer { padding:12px 18px; background:#f5f5f5; border-top:1px solid #eee; display:flex; gap:8px; justify-content:flex-end; }
.mf-alert { padding:8px 12px; border-radius:4px; font-size:12px; margin-top:8px; display:none; }
.mf-alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.mf-alert-error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

@media print {
    .ct-topbar, .ct-filter, .ct-no-print, .mf-modal-overlay { display: none !important; }
    body { background: #fff; }
    #ct-wrapper { padding: 0; }
    .ct-table th, .ct-table tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .ct-document { border-width: 1px; }
}
</style>

<div id="wrapper">
<div id="ct-wrapper">

    <!-- TOP NAV -->
    <div class="ct-topbar ct-no-print">
        <a href="<?php echo admin_url('courier_logistic/shipments?type=domestic'); ?>" class="ct-btn ct-btn-back">
            <i class="fa fa-arrow-left"></i> Shipment List
        </a>
        <span style="font-size:14px; font-weight:800; color:#333; flex:1; margin-left:8px; font-family:Arial,sans-serif;">
            <i class="fa fa-file-text"></i> Cargo Manifest
        </span>
        <button class="ct-btn ct-btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button class="ct-btn ct-btn-pdf" onclick="downloadManifestPdf()"><i class="fa fa-file-pdf-o"></i> PDF</button>
        <button class="ct-btn ct-btn-csv" onclick="exportManifestCSV()"><i class="fa fa-download"></i> CSV</button>
        <button class="ct-btn ct-btn-email" onclick="openCtEmailModal()"><i class="fa fa-envelope"></i> Email</button>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="" class="ct-filter ct-no-print">
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
            <label>Agent</label>
            <select name="driver_id">
                <option value="">All</option>
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
            <button type="submit" class="ct-btn" style="background:#333;color:#fff;"><i class="fa fa-filter"></i> Filter</button>
            <a href="?group=manifests" class="ct-btn ct-btn-back"><i class="fa fa-times"></i> Clear</a>
        </div>
    </form>

    <!-- DOCUMENT -->
    <div class="ct-document" id="ct-document">

        <!-- Header -->
        <div class="ct-head">
            <h1><?php echo htmlspecialchars(strtoupper($_mf_company)); ?> — CARGO MANIFEST</h1>
            <div class="ct-head-sub">Official Shipment Record</div>
            <div class="ct-head-meta">
                <div>Period: <span><?php echo $mf_from_label; ?> — <?php echo $mf_to_label; ?></span></div>
                <div>Total Shipments: <span><?php echo count($mf_rows); ?></span></div>
                <div>Total Weight: <span><?php echo number_format($mf_total_weight, 2); ?> kg</span></div>
                <div>Printed: <span><?php echo date('d M Y, H:i'); ?></span></div>
            </div>
        </div>

        <!-- Company row -->
        <div class="ct-company-row">
            <div class="ct-company-cell">
                <?php if (!empty($_mf_logo_url)): ?>
                    <img src="<?php echo $_mf_logo_url; ?>" class="ct-logo" alt="">
                <?php endif; ?>
                <div>
                    <strong><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></strong>
                    <?php if ($_mf_address): ?><p><?php echo nl2br(htmlspecialchars($_mf_address)); ?></p><?php endif; ?>
                    <?php if ($_mf_phone): ?><p>Tel: <?php echo htmlspecialchars($_mf_phone); ?></p><?php endif; ?>
                    <?php if ($_mf_email_co): ?><p><?php echo htmlspecialchars($_mf_email_co); ?></p><?php endif; ?>
                </div>
            </div>
            <div class="ct-company-cell">
                <strong>MANIFEST DETAILS</strong>
                <p>Manifest No.: <strong>MF-<?php echo date('Ymd'); ?>-<?php echo str_pad(count($mf_rows), 3, '0', STR_PAD_LEFT); ?></strong></p>
                <?php if ($mf_driver_label): ?><p>Agent / Driver: <strong><?php echo htmlspecialchars($mf_driver_label); ?></strong></p><?php endif; ?>
                <p>Prepared By: <strong><?php echo htmlspecialchars($mf_printed_by); ?></strong></p>
            </div>
        </div>

        <!-- Table -->
        <div style="overflow-x:auto;">
            <table class="ct-table" id="mf-table">
                <thead>
                    <tr>
                        <th style="width:28px; text-align:center;">#</th>
                        <th style="width:72px;">DATE</th>
                        <th style="min-width:130px;">SENDER</th>
                        <th style="min-width:130px;">RECIPIENT</th>
                        <th style="width:90px;">PHONE</th>
                        <th style="width:90px;">AWB #</th>
                        <th style="min-width:100px;">DESCRIPTION</th>
                        <th style="width:36px; text-align:center;">PCS</th>
                        <th style="width:55px; text-align:right;">KGS</th>
                        <th style="width:70px; text-align:right;">AMOUNT</th>
                        <th style="width:70px; text-align:center;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $ct_num = 0;
                if (!empty($mf_rows)):
                    foreach ($mf_rows as $row):
                        $ct_num++;
                        $sl = strtolower($row['status']);
                        if     (strpos($sl,'deliver')!==false)                                $bc='ct-badge-ok';
                        elseif (strpos($sl,'transit')!==false||strpos($sl,'pickup')!==false)  $bc='ct-badge-mov';
                        elseif (strpos($sl,'pending')!==false||strpos($sl,'cancel')!==false)  $bc='ct-badge-pnd';
                        else                                                                   $bc='ct-badge-oth';
                ?>
                <tr>
                    <td style="text-align:center; color:#999; font-weight:700;"><?php echo $ct_num; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                        <?php if ($row['sender_phone'] !== '-'): ?><br><span style="font-size:9px;color:#666;"><?php echo htmlspecialchars($row['sender_phone']); ?></span><?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['recv_name']); ?></strong>
                        <?php if ($row['recv_phone'] !== '-'): ?><br><span style="font-size:9px;color:#666;"><?php echo htmlspecialchars($row['recv_phone']); ?></span><?php endif; ?>
                    </td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars($row['recv_phone']); ?></td>
                    <td style="font-size:10px; font-weight:700;"><?php echo htmlspecialchars($row['waybill']); ?></td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars(mb_strimwidth($row['pkg_desc'], 0, 50, '..')); ?></td>
                    <td style="text-align:center;"><?php echo $row['pkg_qty']; ?></td>
                    <td style="text-align:right; font-weight:700;"><?php echo number_format((float)$row['pkg_weight'], 2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$row['total'], 2); ?></td>
                    <td style="text-align:center;"><span class="ct-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                </tr>
                <?php endforeach;
                    for ($b = 0; $b < max(0, 5 - $ct_num); $b++): ?>
                <tr class="ct-empty"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor;
                else:
                    for ($b = 0; $b < 15; $b++): ?>
                <tr class="ct-empty"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:right;">TOTALS — <?php echo $ct_num; ?> shipments</td>
                        <td style="text-align:center;"><?php echo number_format($mf_total_qty); ?></td>
                        <td style="text-align:right;"><?php echo number_format($mf_total_weight, 2); ?></td>
                        <td style="text-align:right; font-weight:800;"><?php echo number_format($mf_total_amount, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="ct-footer">
            <div class="ct-foot-cell" style="flex:2;">
                <span class="ct-foot-label">Authorized Signature</span>
                <div class="ct-sig-line"></div>
            </div>
            <div class="ct-foot-cell">
                <span class="ct-foot-label">Date</span>
                <div class="ct-sig-line" style="font-size:12px; padding-top:3px;"><?php echo date('d / m / Y'); ?></div>
            </div>
            <div class="ct-foot-cell">
                <span class="ct-foot-label">Prepared By</span>
                <div class="ct-sig-line" style="font-size:11px; padding-top:3px;"><?php echo htmlspecialchars($mf_printed_by); ?></div>
            </div>
        </div>

    </div><!-- /ct-document -->
</div><!-- /ct-wrapper -->
</div><!-- /wrapper -->

<!-- EMAIL MODAL -->
<div class="mf-modal-overlay" id="ct-email-modal">
    <div class="mf-modal-box">
        <div class="mf-modal-header"><h4><i class="fa fa-envelope"></i> Send Manifest</h4><button class="mf-modal-close" onclick="closeCtEmailModal()"><i class="fa fa-times"></i></button></div>
        <div class="mf-modal-body">
            <label>Recipient Email *</label><input type="email" id="ct-to-email" placeholder="email@example.com">
            <label>Subject</label><input type="text" id="ct-subject" value="Cargo Manifest — <?php echo $mf_from_label; ?> to <?php echo $mf_to_label; ?>">
            <label>Note</label><textarea id="ct-note" rows="3"></textarea>
            <div class="mf-alert mf-alert-success" id="ct-email-ok"><i class="fa fa-check-circle"></i> <span id="ct-ok-msg"></span></div>
            <div class="mf-alert mf-alert-error"   id="ct-email-err"><i class="fa fa-exclamation-triangle"></i> <span id="ct-err-msg"></span></div>
        </div>
        <div class="mf-modal-footer">
            <button class="ct-btn ct-btn-back" onclick="closeCtEmailModal()">Cancel</button>
            <button class="ct-btn ct-btn-email" id="ct-send-btn" onclick="sendCtEmail()"><i class="fa fa-paper-plane"></i> Send</button>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function openCtEmailModal()  { document.getElementById('ct-email-modal').classList.add('open'); }
function closeCtEmailModal() { document.getElementById('ct-email-modal').classList.remove('open'); }
document.getElementById('ct-email-modal').addEventListener('click',function(e){if(e.target===this)closeCtEmailModal();});
function sendCtEmail(){
    var to=document.getElementById('ct-to-email').value.trim();
    if(!to){document.getElementById('ct-err-msg').textContent='Enter recipient email.';document.getElementById('ct-email-err').style.display='block';return;}
    var btn=document.getElementById('ct-send-btn');btn.disabled=true;btn.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
    document.getElementById('ct-email-ok').style.display=document.getElementById('ct-email-err').style.display='none';
    $.ajax({url:'<?php echo admin_url("courier_logistic/shipments/send_manifest_email"); ?>',type:'POST',
        data:{<?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>',to_email:to,subject:document.getElementById('ct-subject').value,
              date_from:'<?php echo htmlspecialchars($filter_date_from ?? date("Y-m-01")); ?>',date_to:'<?php echo htmlspecialchars($filter_date_to ?? date("Y-m-d")); ?>',driver_id:'<?php echo (int)($filter_driver_id??0); ?>'},
        dataType:'json',
        success:function(res){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';
            if(res.success){document.getElementById('ct-ok-msg').textContent=res.message;document.getElementById('ct-email-ok').style.display='block';setTimeout(closeCtEmailModal,2600);}
            else{document.getElementById('ct-err-msg').textContent=res.message;document.getElementById('ct-email-err').style.display='block';}},
        error:function(){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';document.getElementById('ct-err-msg').textContent='Network error.';document.getElementById('ct-email-err').style.display='block';}
    });
}
function exportManifestCSV(){
    var t=document.getElementById('mf-table');if(!t){alert('No data.');return;}
    var csv=[];t.querySelectorAll('tr').forEach(function(r){var l=[];r.querySelectorAll('th,td').forEach(function(c){l.push('"'+c.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim()+'"');});csv.push(l.join(','));});
    var b=new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});var u=URL.createObjectURL(b);var a=document.createElement('a');a.href=u;a.download='manifest_<?php echo date("Ymd"); ?>.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(u);
}
function downloadManifestPdf() {
    var btn = document.querySelector('.ct-btn-pdf');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>…'; }
    var el = document.getElementById('ct-document');
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

