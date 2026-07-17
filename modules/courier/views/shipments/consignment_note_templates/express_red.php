<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$_ex_total_kg = 0;
foreach ((array)($shipment_details['packages'] ?? []) as $_p) {
    $_ex_total_kg += (float)($_p->weight ?? 0);
}
$_ex_cn_color  = get_option('courier_cn_color') ?: '#c0392b';
$_ex_ship_inst = trim($s->special_instructions ?? '');
?>
<style>
@media print{.ex-action{display:none!important;}body{background:#fff!important;}.ex-wrap{box-shadow:none!important;max-width:100%!important;}}
.ex-action{max-width:980px;margin:12px auto;padding:0 10px;display:flex;gap:8px;}
.ex-action button,.ex-action a{
    background:#c0392b;color:#fff;border:none;padding:8px 22px;border-radius:4px;
    font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:700;
}
.ex-action a{background:#555;}
.ex-wrap{max-width:980px;margin:0 auto 30px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#111;
    background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.2);border-top:6px solid <?php echo htmlspecialchars($_ex_cn_color); ?>;}
/* Top strip */
.ex-topstrip{background:<?php echo htmlspecialchars($_ex_cn_color); ?>;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;}
.ex-badge{background:#fff;color:<?php echo htmlspecialchars($_ex_cn_color); ?>;font-size:22px;font-weight:900;letter-spacing:3px;
    padding:6px 20px;border-radius:4px;text-transform:uppercase;}
.ex-strip-right{text-align:right;color:#fff;}
.ex-cn-num{font-size:18px;font-weight:900;letter-spacing:1px;}
.ex-cn-meta{font-size:10px;opacity:.9;margin-top:3px;line-height:1.6;}
/* Company bar */
.ex-co-bar{background:#222;color:#fff;padding:8px 24px;display:flex;align-items:center;justify-content:space-between;}
.ex-co-info{font-size:11px;line-height:1.6;}
.ex-co-name{font-size:14px;font-weight:900;margin-bottom:2px;}
/* Route banner */
.ex-route{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;background:#fff7f7;
    border-top:1px solid #ffb3ae;border-bottom:2px solid #c0392b;padding:14px 24px;gap:10px;}
.ex-point{padding:10px 14px;border-radius:6px;border:1.5px solid #ddd;}
.ex-point-label{font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.8px;color:#c0392b;margin-bottom:4px;}
.ex-point-name{font-size:15px;font-weight:900;color:#111;}
.ex-point-sub{font-size:10px;color:#555;line-height:1.6;margin-top:2px;}
.ex-arrow{text-align:center;color:#c0392b;font-size:28px;font-weight:900;}
/* Info cards */
.ex-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-bottom:2px solid #ddd;}
.ex-card{padding:12px 20px;border-right:1px solid #eee;}
.ex-card:last-child{border-right:none;}
.ex-card-title{font-size:10px;font-weight:900;text-transform:uppercase;color:<?php echo htmlspecialchars($_ex_cn_color); ?>;letter-spacing:.5px;margin-bottom:6px;
    padding-bottom:4px;border-bottom:2px solid <?php echo htmlspecialchars($_ex_cn_color); ?>;display:inline-block;}
.ex-card-row{font-size:11px;color:#333;margin:2px 0;line-height:1.5;}
.ex-card-row strong{color:#111;}
/* Table */
.ex-tbl-wrap{padding:0;}
.ex-sec{background:#f7f7f7;border-top:1px solid #ddd;border-bottom:1px solid #ddd;
    padding:5px 20px;font-size:11px;font-weight:900;color:#333;text-transform:uppercase;letter-spacing:.4px;}
.ex-tbl{width:100%;border-collapse:collapse;font-size:11px;}
.ex-tbl th{background:#333;color:#fff;padding:7px 10px;text-align:left;font-size:10px;}
.ex-tbl th.ctr{text-align:center;}
.ex-tbl td{padding:7px 10px;border:1px solid #eee;}
.ex-tbl tr:nth-child(even) td{background:#fff7f7;}
.ex-tbl td.ctr{text-align:center;}
/* Urgency banner */
.ex-urgent{background:<?php echo htmlspecialchars($_ex_cn_color); ?>;color:#fff;padding:10px 24px;display:flex;align-items:center;justify-content:space-between;font-weight:700;}
.ex-urgent-left{font-size:12px;}
.ex-urgent-right{font-size:20px;font-weight:900;}
/* Sigs */
.ex-sigs{display:grid;grid-template-columns:1fr 1fr 1fr;padding:16px 20px;gap:14px;border-top:2px solid #ddd;}
.ex-sig{background:#f9f9f9;border:1.5px solid #ddd;border-top:3px solid <?php echo htmlspecialchars($_ex_cn_color); ?>;padding:10px;border-radius:4px;}
.ex-sig-line{border-bottom:1.5px solid #555;margin:20px 0 6px;}
.ex-sig-lbl{font-size:10px;font-weight:700;color:#333;text-align:center;}
.ex-sig-sub{font-size:9px;color:#888;text-align:center;margin-top:2px;}
.ex-footer{background:#222;color:rgba(255,255,255,.6);text-align:center;padding:8px;font-size:10px;}
</style>

<div id="wrapper">
<div class="content">

<div class="ex-action">
    <button onclick="window.print();"><i class="fa fa-print"></i> Print</button>
    <button onclick="downloadExPdf();" style="background:#1565c0;"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
    <a href="<?php echo $waybill_back_url; ?>"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadExPdf() {
    html2pdf().set({
        margin:[8,8,8,8],
        filename:'consignment-note-<?php echo htmlspecialchars($s->waybill_number); ?>.pdf',
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,useCORS:true},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
    }).from(document.querySelector('.ex-wrap')).save();
}
</script>

<div class="ex-wrap">

    <!-- Top strip -->
    <div class="ex-topstrip">
        <div>
            <?php if ($company_logo_url): ?><img src="<?php echo $company_logo_url; ?>" style="max-height:40px;max-width:120px;filter:brightness(10);margin-bottom:4px;" alt="Logo"><br><?php endif; ?>
            <span class="ex-badge">EXPRESS CONSIGNMENT</span>
        </div>
        <div class="ex-strip-right">
            <div class="ex-cn-num">CN # <?php echo htmlspecialchars($s->waybill_number); ?></div>
            <div class="ex-cn-meta">
                <?php echo $current_date; ?><br>
                <?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?> &nbsp;|&nbsp;
                <?php echo htmlspecialchars($s->status_description ?? $s->status_name); ?>
                <?php if (!empty($s->is_round_trip)): ?> &nbsp;|&nbsp; ROUND TRIP<?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Company bar -->
    <div class="ex-co-bar">
        <div class="ex-co-info">
            <div class="ex-co-name"><?php echo htmlspecialchars($logistic_company); ?></div>
            <?php if ($_ci['phone']): ?><span>Tel: <?php echo htmlspecialchars($_ci['phone']); ?></span><?php endif; ?>
            <?php if ($_ci['email']): ?> &nbsp;|&nbsp; <span><?php echo htmlspecialchars($_ci['email']); ?></span><?php endif; ?>
            <?php if ($_ci['website']): ?> &nbsp;|&nbsp; <span><?php echo htmlspecialchars($_ci['website']); ?></span><?php endif; ?>
        </div>
        <?php if ($_ci['pin']): ?><div style="font-size:10px;opacity:.7;">PIN: <?php echo htmlspecialchars($_ci['pin']); ?></div><?php endif; ?>
    </div>

    <!-- Route -->
    <div class="ex-route">
        <div class="ex-point">
            <div class="ex-point-label"><i class="fa fa-arrow-circle-up"></i> From (Shipper)</div>
            <div class="ex-point-name"><?php echo htmlspecialchars($snd_name); ?></div>
            <div class="ex-point-sub">
                <?php if ($snd_addr): ?><?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?><br><?php endif; ?>
                <?php if ($snd_phone): ?>Tel: <?php echo htmlspecialchars($snd_phone); ?><?php endif; ?>
            </div>
        </div>
        <div class="ex-arrow">&#8594;</div>
        <div class="ex-point">
            <div class="ex-point-label"><i class="fa fa-arrow-circle-down"></i> To (Consignee)</div>
            <div class="ex-point-name"><?php echo htmlspecialchars($rec_name); ?></div>
            <div class="ex-point-sub">
                <?php if ($rec_addr): ?><?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?><br><?php endif; ?>
                <?php if ($rec_phone): ?>Tel: <?php echo htmlspecialchars($rec_phone); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Package table -->
    <div class="ex-sec">Parcel / Package Details</div>
    <div class="ex-tbl-wrap">
    <table class="ex-tbl">
        <thead>
        <?php if ($s->fcl_shipment == 1): ?>
            <tr><th>#</th><th>Description</th><th class="ctr">Qty</th><th class="ctr">FCL Option</th></tr>
        <?php else: ?>
            <tr><th>#</th><th>Description</th><th class="ctr">Qty</th><th class="ctr">L(cm)</th><th class="ctr">W(cm)</th><th class="ctr">H(cm)</th><th class="ctr">Vol.Wt(kg)</th><th class="ctr">Gross Wt</th><th class="ctr">Chargeable</th></tr>
        <?php endif; ?>
        </thead>
        <tbody>
        <?php $n = 1; foreach ((array)($shipment_details['packages'] ?? []) as $pkg): ?>
        <?php if ($s->fcl_shipment == 1): ?>
            <tr><td><?php echo $n++; ?></td><td><?php echo htmlspecialchars($pkg->description); ?></td><td class="ctr"><?php echo $pkg->quantity; ?></td><td class="ctr"><?php echo htmlspecialchars($pkg->fcl_option); ?></td></tr>
        <?php else: ?>
            <tr>
                <td><?php echo $n++; ?></td><td><?php echo htmlspecialchars($pkg->description); ?></td>
                <td class="ctr"><?php echo $pkg->quantity; ?></td>
                <td class="ctr"><?php echo $pkg->length; ?></td><td class="ctr"><?php echo $pkg->width; ?></td><td class="ctr"><?php echo $pkg->height; ?></td>
                <td class="ctr"><?php echo $pkg->weight_volume; ?></td>
                <td class="ctr"><?php echo $pkg->weight; ?></td>
                <td class="ctr"><strong><?php echo $pkg->chargeable_weight; ?></strong></td>
            </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Urgency / total banner -->
    <div class="ex-urgent">
        <div class="ex-urgent-left">
            <?php if ($_ex_ship_inst): ?>
            <i class="fa fa-exclamation-triangle"></i> &nbsp;<?php echo htmlspecialchars($_ex_ship_inst); ?> &nbsp;|&nbsp;
            <?php else: ?>
            <i class="fa fa-exclamation-triangle"></i> &nbsp;Handle with care &nbsp;|&nbsp;
            <?php endif; ?>
            <i class="fa fa-shield"></i> &nbsp;Received in good order &amp; condition
        </div>
        <div class="ex-urgent-right">
            <?php echo $_ex_total_kg > 0 ? number_format($_ex_total_kg, 2) . ' KG' : 'TOTAL WEIGHT'; ?>
        </div>
    </div>

    <!-- Signatures -->
    <div class="ex-sigs">
        <div class="ex-sig"><div class="ex-sig-line"></div><div class="ex-sig-lbl">Shipper's Signature</div><div class="ex-sig-sub">Name &amp; Date</div></div>
        <div class="ex-sig"><div class="ex-sig-line"></div><div class="ex-sig-lbl">Driver / Carrier</div><div class="ex-sig-sub">ID / Truck No. &amp; Date</div></div>
        <div class="ex-sig"><div class="ex-sig-line"></div><div class="ex-sig-lbl">Consignee's Signature</div><div class="ex-sig-sub">Name, Signature &amp; Date</div></div>
    </div>

    <div class="ex-footer">
        <?php echo htmlspecialchars($logistic_company); ?> — CN <?php echo htmlspecialchars($s->waybill_number); ?> — <?php echo $current_date; ?>
    </div>
</div>

</div>
</div>
<?php init_tail(); ?>
