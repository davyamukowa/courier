<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $mc = htmlspecialchars($manifest_color ?? '#37474f', ENT_QUOTES); ?>
<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
#cl-wrapper { max-width: 100%; padding: 0 14px 30px; }

.cl-topbar {
    background: #fff; border-bottom: 2px solid #ccc; padding: 8px 14px;
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    margin-bottom: 14px; position: sticky; top: 0; z-index: 100;
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}
.cl-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border: none; border-radius: 4px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap;
}
.cl-btn:hover { opacity: .85; text-decoration: none; }
.cl-btn-back  { background: #546e7a; color: #fff; }
.cl-btn-print { background: #1565c0; color: #fff; }
.cl-btn-csv   { background: #00838f; color: #fff; }
.cl-btn-email { background: #e65100; color: #fff; }
.cl-btn-pdf   { background: #6a1b9a; color: #fff; }

.cl-filter {
    background: #fafafa; border: 1px solid #ddd; border-radius: 5px;
    padding: 10px 14px 8px; margin-bottom: 14px;
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;
}
.cl-filter label { font-size: 11px; font-weight: 700; color: #555; display: block; margin-bottom: 3px; }
.cl-filter input, .cl-filter select { border: 1px solid #bbb; border-radius: 3px; padding: 5px 8px; font-size: 12px; min-width: 140px; }

/* Document */
.cl-document { background: #fff; border: 1.5px solid #333; }

/* Header */
.cl-head { border-bottom: 2px solid; padding: 10px 14px; display: flex; align-items: center; gap: 14px; }
.cl-head-logo img { max-width: 70px; max-height: 44px; object-fit: contain; }
.cl-head-info { flex: 1; }
.cl-head-info h2 { margin: 0; font-size: 15px; font-weight: 900; text-transform: uppercase; }
.cl-head-info p  { margin: 2px 0 0; font-size: 10px; color: #555; }
.cl-head-meta { text-align: right; font-size: 11px; }
.cl-head-meta strong { display: block; font-size: 13px; font-weight: 800; }
.cl-head       { border-bottom-color: <?php echo $mc; ?>; }
.cl-head-info h2 { color: <?php echo $mc; ?>; }

/* Summary row */
.cl-summary {
    display: flex; flex-wrap: wrap; gap: 0;
    border-bottom: 1px solid #333; font-size: 11px;
}
.cl-sum-cell { flex: 1; padding: 5px 10px; border-right: 1px solid #ddd; text-align: center; }
.cl-sum-cell:last-child { border-right: none; }
.cl-sum-label { font-size: 9px; text-transform: uppercase; font-weight: 700; display: block; color: #777; }
.cl-sum-value { font-size: 14px; font-weight: 800; color: #111; }

/* Compact table */
.cl-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
.cl-table thead th {
    color: #fff; padding: 5px 5px;
    text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .3px;
    white-space: nowrap; border-right: 1px solid rgba(255,255,255,.2);
}
.cl-table thead th:last-child { border-right: none; }
.cl-table thead th { background: <?php echo $mc; ?>; }
.cl-table tbody td {
    padding: 4px 5px; border-bottom: 1px solid #eee;
    border-right: 1px solid #f0f0f0; vertical-align: middle;
    line-height: 1.3;
}
.cl-table tbody td:last-child { border-right: none; }
.cl-table tbody tr:nth-child(even) td { background: #fafafa; }
.cl-table tbody tr:hover td { background: #f0f0f0; }
.cl-empty td { height: 18px; background: #fff !important; }
.cl-table tfoot td {
    padding: 5px 5px; font-weight: 700; color: #fff;
    font-size: 10.5px; border-right: 1px solid rgba(255,255,255,.2);
}
.cl-table tfoot td:last-child { border-right: none; }
.cl-table tfoot td { background: <?php echo $mc; ?>; }

.cl-badge { display:inline-block; padding:1px 5px; border-radius:3px; font-size:8.5px; font-weight:700; white-space:nowrap; }
.cl-ok  { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.cl-mov { background:#fff3e0; color:#e65100; border:1px solid #ffcc80; }
.cl-pnd { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }
.cl-oth { background:#eceff1; color:#546e7a; border:1px solid #cfd8dc; }

/* Footer */
.cl-footer { border-top: 1.5px solid #333; padding: 8px 14px; display: flex; gap: 16px; }
.cl-foot-cell { flex: 1; }
.cl-foot-label { font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #777; display: block; margin-bottom: 3px; }
.cl-sig-line { border-bottom: 1px solid #333; min-height: 20px; margin-top: 3px; }

.mf-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
.mf-modal-overlay.open { display:flex; }
.mf-modal-box { background:#fff; border-radius:8px; width:100%; max-width:460px; box-shadow:0 10px 35px rgba(0,0,0,.25); overflow:hidden; }
.mf-modal-header { background:#e65100; padding:13px 16px; display:flex; align-items:center; justify-content:space-between; }
.mf-modal-header h4 { margin:0; color:#fff; font-size:14px; font-weight:700; }
.mf-modal-close { background:rgba(255,255,255,.2); border:none; border-radius:50%; width:26px; height:26px; cursor:pointer; color:#fff; font-size:13px; }
.mf-modal-body { padding:16px; }
.mf-modal-body label { font-size:12px; font-weight:700; color:#333; display:block; margin-bottom:4px; }
.mf-modal-body input, .mf-modal-body textarea { width:100%; border:1px solid #ddd; border-radius:4px; padding:7px 9px; font-size:13px; box-sizing:border-box; margin-bottom:12px; }
.mf-modal-footer { padding:12px 16px; background:#f5f5f5; border-top:1px solid #eee; display:flex; gap:8px; justify-content:flex-end; }
.mf-alert { padding:8px 12px; border-radius:4px; font-size:12px; margin-top:8px; display:none; }
.mf-alert-success { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.mf-alert-error   { background:#fce4ec; color:#c62828; border:1px solid #ef9a9a; }

@media print {
    .cl-topbar, .cl-filter, .cl-no-print, .mf-modal-overlay { display: none !important; }
    body { background: #fff; }
    #cl-wrapper { padding: 0; }
    .cl-table thead th, .cl-table tfoot td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div id="wrapper">
<div id="cl-wrapper">

    <!-- TOP NAV -->
    <div class="cl-topbar cl-no-print">
        <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="cl-btn cl-btn-back"><i class="fa fa-arrow-left"></i> List</a>
        <span style="font-size:14px; font-weight:800; flex:1; margin-left:6px; color:<?php echo $mc; ?>;"><i class="fa fa-list"></i> Compact Manifest</span>
        <button class="cl-btn cl-btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button class="cl-btn cl-btn-pdf"   onclick="downloadManifestPdf()"><i class="fa fa-file-pdf-o"></i> PDF</button>
        <button class="cl-btn cl-btn-csv"   onclick="exportManifestCSV()"><i class="fa fa-download"></i> CSV</button>
        <button class="cl-btn cl-btn-email" onclick="openClEmailModal()"><i class="fa fa-envelope"></i> Email</button>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="" class="cl-filter cl-no-print">
        <input type="hidden" name="group" value="manifests">
        <div><label>From Date</label><input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from ?? date('Y-m-01')); ?>"></div>
        <div><label>To Date</label><input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to ?? date('Y-m-d')); ?>"></div>
        <div>
            <label>Agent</label>
            <select name="driver_id">
                <option value="">All</option>
                <?php foreach ((array)($all_drivers ?? []) as $drv):
                    $drv_id=(int)($drv['staffid']??0); $drv_nm=trim(($drv['firstname']??'').' '.($drv['lastname']??''));
                    $sel=((string)($filter_driver_id??'')===(string)$drv_id)?'selected':'';
                ?><option value="<?php echo $drv_id; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($drv_nm); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:flex-end;">
            <button type="submit" class="cl-btn" style="background:<?php echo $mc; ?>;color:#fff;"><i class="fa fa-filter"></i> Filter</button>
            <a href="?group=manifests" class="cl-btn cl-btn-back"><i class="fa fa-times"></i> Clear</a>
        </div>
    </form>

    <!-- DOCUMENT -->
    <div class="cl-document" id="cl-document">

        <!-- Header -->
        <div class="cl-head">
            <?php if (!empty($_mf_logo_url)): ?>
                <div><img src="<?php echo $_mf_logo_url; ?>" alt=""></div>
            <?php endif; ?>
            <div class="cl-head-info">
                <h2><?php echo htmlspecialchars(strtoupper($_mf_company)); ?></h2>
                <p>
                    <?php if ($_mf_address): echo htmlspecialchars($_mf_address) . ' &nbsp;|&nbsp; '; endif; ?>
                    <?php if ($_mf_phone): echo htmlspecialchars($_mf_phone); endif; ?>
                </p>
            </div>
            <div class="cl-head-meta">
                <strong>CARGO MANIFEST</strong>
                <span><?php echo $mf_from_label; ?> &mdash; <?php echo $mf_to_label; ?></span><br>
                <span style="font-size:10px; color:#777;">Printed: <?php echo date('d M Y, H:i'); ?></span>
            </div>
        </div>

        <!-- Summary -->
        <div class="cl-summary">
            <div class="cl-sum-cell"><span class="cl-sum-label">Shipments</span><span class="cl-sum-value"><?php echo count($mf_rows); ?></span></div>
            <div class="cl-sum-cell"><span class="cl-sum-label">Pieces</span><span class="cl-sum-value"><?php echo number_format($mf_total_qty); ?></span></div>
            <div class="cl-sum-cell"><span class="cl-sum-label">Weight</span><span class="cl-sum-value"><?php echo number_format($mf_total_weight, 1); ?> kg</span></div>
            <div class="cl-sum-cell"><span class="cl-sum-label">Charges</span><span class="cl-sum-value"><?php echo number_format($mf_total_charges, 2); ?></span></div>
            <div class="cl-sum-cell"><span class="cl-sum-label">Grand Total</span><span class="cl-sum-value"><?php echo number_format($mf_total_amount, 2); ?></span></div>
            <?php if ($mf_driver_label): ?><div class="cl-sum-cell"><span class="cl-sum-label">Agent</span><span class="cl-sum-value" style="font-size:11px;"><?php echo htmlspecialchars($mf_driver_label); ?></span></div><?php endif; ?>
        </div>

        <!-- Compact table -->
        <div style="overflow-x:auto;">
            <table class="cl-table" id="mf-table">
                <thead>
                    <tr>
                        <th style="width:24px; text-align:center;">#</th>
                        <th style="width:68px;">DATE</th>
                        <th style="width:82px;">AWB #</th>
                        <th style="min-width:110px;">SENDER</th>
                        <th style="min-width:110px;">RECIPIENT</th>
                        <th style="width:86px;">PHONE</th>
                        <th style="min-width:80px;">DESCRIPTION</th>
                        <th style="width:30px; text-align:center;">PCS</th>
                        <th style="width:50px; text-align:right;">KGS</th>
                        <th style="width:65px; text-align:right;">AMOUNT</th>
                        <th style="width:65px; text-align:center;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $cl_num = 0;
                if (!empty($mf_rows)):
                    foreach ($mf_rows as $row):
                        $cl_num++;
                        $sl=strtolower($row['status']);
                        if     (strpos($sl,'deliver')!==false)                               $bc='cl-ok';
                        elseif (strpos($sl,'transit')!==false||strpos($sl,'pickup')!==false) $bc='cl-mov';
                        elseif (strpos($sl,'pending')!==false||strpos($sl,'cancel')!==false) $bc='cl-pnd';
                        else                                                                  $bc='cl-oth';
                ?>
                <tr>
                    <td style="text-align:center; color:#aaa; font-weight:700; font-size:9px;"><?php echo $cl_num; ?></td>
                    <td style="font-size:10px; white-space:nowrap;"><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td style="font-size:9.5px; font-weight:700; color:#333;"><?php echo htmlspecialchars($row['waybill']); ?></td>
                    <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['recv_name']); ?></td>
                    <td style="font-size:10px;"><?php echo htmlspecialchars($row['recv_phone']); ?></td>
                    <td style="font-size:9.5px; color:#555;"><?php echo htmlspecialchars(mb_strimwidth($row['pkg_desc'], 0, 35, '..')); ?></td>
                    <td style="text-align:center; font-weight:700;"><?php echo $row['pkg_qty']; ?></td>
                    <td style="text-align:right; font-weight:700;"><?php echo number_format((float)$row['pkg_weight'], 2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$row['total'], 2); ?></td>
                    <td style="text-align:center;"><span class="cl-badge <?php echo $bc; ?>"><?php echo htmlspecialchars(substr($row['status'],0,9)); ?></span></td>
                </tr>
                <?php endforeach;
                    for($b=0;$b<max(0,5-$cl_num);$b++): ?>
                <tr class="cl-empty"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; else: for($b=0;$b<20;$b++): ?>
                <tr class="cl-empty"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:right;">TOTAL — <?php echo $cl_num; ?> shipments</td>
                        <td style="text-align:center;"><?php echo number_format($mf_total_qty); ?></td>
                        <td style="text-align:right;"><?php echo number_format($mf_total_weight, 2); ?></td>
                        <td style="text-align:right; font-weight:800;"><?php echo number_format($mf_total_amount, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="cl-footer">
            <div class="cl-foot-cell" style="flex:2;"><span class="cl-foot-label">Authorized Signature</span><div class="cl-sig-line"></div></div>
            <div class="cl-foot-cell"><span class="cl-foot-label">Date</span><div class="cl-sig-line" style="font-size:11px;padding-top:3px;"><?php echo date('d / m / Y'); ?></div></div>
            <div class="cl-foot-cell"><span class="cl-foot-label">Printed By</span><div class="cl-sig-line" style="font-size:10px;padding-top:3px;"><?php echo htmlspecialchars($mf_printed_by); ?></div></div>
        </div>

    </div><!-- /cl-document -->
</div><!-- /cl-wrapper -->
</div><!-- /wrapper -->

<!-- EMAIL MODAL -->
<div class="mf-modal-overlay" id="cl-email-modal">
    <div class="mf-modal-box">
        <div class="mf-modal-header"><h4><i class="fa fa-envelope"></i> Send Manifest</h4><button class="mf-modal-close" onclick="closeClEmailModal()"><i class="fa fa-times"></i></button></div>
        <div class="mf-modal-body">
            <label>Recipient Email *</label><input type="email" id="cl-to-email" placeholder="email@example.com">
            <label>Subject</label><input type="text" id="cl-subject" value="Compact Manifest — <?php echo $mf_from_label; ?> to <?php echo $mf_to_label; ?>">
            <label>Note</label><textarea id="cl-note" rows="3"></textarea>
            <div class="mf-alert mf-alert-success" id="cl-ok"><span id="cl-ok-msg"></span></div>
            <div class="mf-alert mf-alert-error"   id="cl-err"><span id="cl-err-msg"></span></div>
        </div>
        <div class="mf-modal-footer">
            <button class="cl-btn cl-btn-back" onclick="closeClEmailModal()">Cancel</button>
            <button class="cl-btn cl-btn-email" id="cl-send-btn" onclick="sendClEmail()"><i class="fa fa-paper-plane"></i> Send</button>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function openClEmailModal()  { document.getElementById('cl-email-modal').classList.add('open'); }
function closeClEmailModal() { document.getElementById('cl-email-modal').classList.remove('open'); }
document.getElementById('cl-email-modal').addEventListener('click',function(e){if(e.target===this)closeClEmailModal();});
function sendClEmail(){
    var to=document.getElementById('cl-to-email').value.trim();
    if(!to){document.getElementById('cl-err-msg').textContent='Enter recipient email.';document.getElementById('cl-err').style.display='block';return;}
    var btn=document.getElementById('cl-send-btn');btn.disabled=true;btn.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
    document.getElementById('cl-ok').style.display=document.getElementById('cl-err').style.display='none';
    $.ajax({url:'<?php echo admin_url("courier/shipments/send_manifest_email"); ?>',type:'POST',
        data:{<?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>',to_email:to,subject:document.getElementById('cl-subject').value,
              date_from:'<?php echo htmlspecialchars($filter_date_from ?? date("Y-m-01")); ?>',date_to:'<?php echo htmlspecialchars($filter_date_to ?? date("Y-m-d")); ?>',driver_id:'<?php echo (int)($filter_driver_id??0); ?>'},
        dataType:'json',
        success:function(res){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';
            if(res.success){document.getElementById('cl-ok-msg').textContent=res.message;document.getElementById('cl-ok').style.display='block';setTimeout(closeClEmailModal,2600);}
            else{document.getElementById('cl-err-msg').textContent=res.message;document.getElementById('cl-err').style.display='block';}},
        error:function(){btn.disabled=false;btn.innerHTML='<i class="fa fa-paper-plane"></i> Send';document.getElementById('cl-err-msg').textContent='Network error.';document.getElementById('cl-err').style.display='block';}
    });
}
function exportManifestCSV(){
    var t=document.getElementById('mf-table');if(!t){alert('No data.');return;}
    var csv=[];t.querySelectorAll('tr').forEach(function(r){var l=[];r.querySelectorAll('th,td').forEach(function(c){l.push('"'+c.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim()+'"');});csv.push(l.join(','));});
    var b=new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});var u=URL.createObjectURL(b);var a=document.createElement('a');a.href=u;a.download='compact_manifest_<?php echo date("Ymd"); ?>.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(u);
}
function downloadManifestPdf() {
    var btn = document.querySelector('.cl-btn-pdf');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>…'; }
    var el = document.getElementById('cl-document');
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
