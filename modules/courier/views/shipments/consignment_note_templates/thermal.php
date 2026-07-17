<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$_th_total_kg = 0;
foreach ((array)($shipment_details['packages'] ?? []) as $_p) {
    $_th_total_kg += (float)($_p->weight ?? 0);
}
$_th_cn_color  = get_option('courier_cn_color') ?: '#333333';
$_th_ship_inst = trim($s->special_instructions ?? '');
?>
<style>
@media print{.th-action{display:none!important;}body{background:#fff!important;margin:0;}
    .th-wrap{max-width:100%!important;box-shadow:none!important;margin:0!important;border:none!important;}}
.th-action{max-width:700px;margin:12px auto;padding:0 10px;display:flex;gap:8px;}
.th-action button,.th-action a{
    background:#333;color:#fff;border:none;padding:7px 18px;border-radius:3px;
    font-size:12px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;
}
.th-action a{background:#666;}
/* Thermal-style: narrow, monochrome, dense */
.th-wrap{
    max-width:700px;margin:0 auto 20px;
    font-family:'Courier New',Courier,monospace;font-size:11px;color:#000;
    background:#fff;border:1px solid #000;box-shadow:0 1px 6px rgba(0,0,0,.2);
}
.th-hdr{border-bottom:2px solid #000;padding:8px 10px;display:flex;align-items:center;justify-content:space-between;}
.th-logo img{max-height:40px;max-width:100px;}
.th-hdr-center{text-align:center;flex:1;}
.th-title{font-size:16px;font-weight:900;letter-spacing:1px;text-transform:uppercase;}
.th-subtitle{font-size:9px;color:#333;margin-top:2px;}
.th-hdr-right{text-align:right;font-size:10px;line-height:1.8;}
.th-divider{border-top:1px solid #000;margin:0;}
.th-double-line{border-top:3px double #000;}
.th-cn-band{background:<?php echo htmlspecialchars($_th_cn_color); ?>;color:#fff;text-align:center;padding:4px;font-size:13px;font-weight:900;letter-spacing:2px;}
.th-section{padding:6px 10px;border-bottom:1px solid #000;}
.th-section-title{font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:1px;
    border-bottom:1px solid #000;padding-bottom:2px;margin-bottom:5px;}
.th-row{display:flex;gap:6px;margin:2px 0;font-size:11px;}
.th-key{font-weight:700;min-width:70px;flex-shrink:0;}
.th-val{color:#111;}
.th-two-col{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #000;}
.th-col{padding:6px 10px;border-right:1px solid #000;}
.th-col:last-child{border-right:none;}
.th-col-hdr{font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:1px;
    background:#ddd;padding:2px 4px;margin:-6px -10px 6px;border-bottom:1px solid #000;}
.th-tbl{width:100%;border-collapse:collapse;font-size:10px;}
.th-tbl th{background:#000;color:#fff;padding:4px 6px;text-align:left;font-size:9px;white-space:nowrap;}
.th-tbl td{padding:3px 6px;border:1px solid #aaa;font-size:10px;}
.th-tbl td.r{text-align:right;}
.th-tbl td.c{text-align:center;}
.th-total-bar{background:<?php echo htmlspecialchars($_th_cn_color); ?>;color:#fff;display:flex;justify-content:space-between;padding:5px 10px;font-weight:900;font-size:12px;}
.th-sig-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;padding:8px 10px;border-top:1px solid #000;}
.th-sig{font-size:10px;}
.th-sig-lbl{font-size:9px;font-weight:700;margin-bottom:2px;}
.th-sig-line{border-bottom:1px solid #000;margin:14px 0 3px;}
.th-footer{text-align:center;font-size:9px;color:#555;padding:4px 10px;border-top:1px solid #000;}
</style>

<div id="wrapper">
<div class="content">

<div class="th-action">
    <button onclick="window.print();"><i class="fa fa-print"></i> Print</button>
    <button onclick="downloadThPdf();" style="background:#1565c0;"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
    <a href="<?php echo $waybill_back_url; ?>"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadThPdf() {
    html2pdf().set({
        margin:[5,5,5,5],
        filename:'consignment-note-<?php echo htmlspecialchars($s->waybill_number); ?>.pdf',
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,useCORS:true},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
    }).from(document.querySelector('.th-wrap')).save();
}
</script>

<div class="th-wrap">

    <!-- Header -->
    <div class="th-hdr">
        <div class="th-logo">
            <?php if ($company_logo_url): ?>
                <img src="<?php echo $company_logo_url; ?>" alt="Logo">
            <?php else: ?>
                <span style="font-size:13px;font-weight:900;"><?php echo htmlspecialchars($logistic_company); ?></span>
            <?php endif; ?>
        </div>
        <div class="th-hdr-center">
            <div class="th-title">CONSIGNMENT NOTE</div>
            <div class="th-subtitle"><?php echo htmlspecialchars($logistic_company); ?></div>
            <?php if ($_ci['address']): ?><div style="font-size:8px;color:#555;"><?php echo htmlspecialchars(str_replace("\n",' | ',trim($_ci['address']))); ?></div><?php endif; ?>
            <?php if ($_ci['phone'] || $_ci['email']): ?>
            <div style="font-size:8px;color:#555;">
                <?php if ($_ci['phone']): ?>T: <?php echo htmlspecialchars($_ci['phone']); ?><?php endif; ?>
                <?php if ($_ci['email']): ?> &nbsp;<?php echo htmlspecialchars($_ci['email']); ?><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="th-hdr-right">
            <strong><?php echo htmlspecialchars($s->waybill_number); ?></strong><br>
            <?php echo $current_date; ?><br>
            <?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?>
        </div>
    </div>

    <!-- CN Number band -->
    <div class="th-cn-band">CN: <?php echo htmlspecialchars($s->waybill_number); ?></div>

    <!-- Parties -->
    <div class="th-two-col">
        <div class="th-col">
            <div class="th-col-hdr">CONSIGNOR / SHIPPER</div>
            <div style="font-weight:700;font-size:12px;margin-bottom:3px;"><?php echo htmlspecialchars($snd_name); ?></div>
            <?php if ($snd_addr): ?><div style="font-size:10px;"><?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?></div><?php endif; ?>
            <?php if ($snd_phone): ?><div style="font-size:10px;">T: <?php echo htmlspecialchars($snd_phone); ?></div><?php endif; ?>
            <?php if ($snd_email): ?><div style="font-size:10px;"><?php echo htmlspecialchars($snd_email); ?></div><?php endif; ?>
            <div style="border-top:1px dotted #aaa;margin-top:4px;padding-top:3px;font-size:9px;">ID/PASSPORT: _______________________</div>
        </div>
        <div class="th-col">
            <div class="th-col-hdr">CONSIGNEE / RECEIVER</div>
            <div style="font-weight:700;font-size:12px;margin-bottom:3px;"><?php echo htmlspecialchars($rec_name); ?></div>
            <?php if ($rec_addr): ?><div style="font-size:10px;"><?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?></div><?php endif; ?>
            <?php if ($rec_phone): ?><div style="font-size:10px;">T: <?php echo htmlspecialchars($rec_phone); ?></div><?php endif; ?>
            <?php if ($rec_email): ?><div style="font-size:10px;"><?php echo htmlspecialchars($rec_email); ?></div><?php endif; ?>
            <div style="border-top:1px dotted #aaa;margin-top:4px;padding-top:3px;font-size:9px;">ID/PASSPORT: _______________________</div>
        </div>
    </div>

    <!-- Packages -->
    <div class="th-section">
        <div class="th-section-title">Package Details</div>
        <table class="th-tbl">
            <thead>
            <?php if ($s->fcl_shipment == 1): ?>
                <tr><th>#</th><th>Description</th><th>Qty</th><th>FCL</th></tr>
            <?php else: ?>
                <tr><th>#</th><th>Description</th><th>Qty</th><th>L</th><th>W</th><th>H</th><th>Vol.Kg</th><th>Gross Kg</th><th>Chargeable</th></tr>
            <?php endif; ?>
            </thead>
            <tbody>
            <?php
            $n = 1;
            foreach ((array)($shipment_details['packages'] ?? []) as $pkg):
            ?>
            <?php if ($s->fcl_shipment == 1): ?>
                <tr><td><?php echo $n++; ?></td><td><?php echo htmlspecialchars($pkg->description); ?></td><td class="c"><?php echo $pkg->quantity; ?></td><td><?php echo htmlspecialchars($pkg->fcl_option); ?></td></tr>
            <?php else: ?>
                <tr>
                    <td><?php echo $n++; ?></td><td><?php echo htmlspecialchars($pkg->description); ?></td>
                    <td class="c"><?php echo $pkg->quantity; ?></td>
                    <td class="c"><?php echo $pkg->length; ?></td><td class="c"><?php echo $pkg->width; ?></td><td class="c"><?php echo $pkg->height; ?></td>
                    <td class="r"><?php echo $pkg->weight_volume; ?></td>
                    <td class="r"><?php echo $pkg->weight; ?></td>
                    <td class="r"><strong><?php echo $pkg->chargeable_weight; ?></strong></td>
                </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Special instructions (if any) -->
    <?php if ($_th_ship_inst): ?>
    <div style="padding:4px 10px;border-bottom:1px solid #000;font-size:10px;">
        <strong>Special Instructions:</strong> <?php echo nl2br(htmlspecialchars($_th_ship_inst)); ?>
    </div>
    <?php endif; ?>

    <!-- Total -->
    <div class="th-total-bar">
        <span>RECEIVED IN GOOD ORDER &amp; CONDITION</span>
        <span>TOTAL: <?php echo $_th_total_kg > 0 ? number_format($_th_total_kg, 2) . ' KG' : '_____ KG'; ?></span>
    </div>

    <!-- Signatures -->
    <div class="th-sig-row">
        <div class="th-sig">
            <div class="th-sig-lbl">Shipper's Signature &amp; Date</div>
            <div class="th-sig-line"></div>
        </div>
        <div class="th-sig">
            <div class="th-sig-lbl">Driver / Truck No. &amp; Date</div>
            <div class="th-sig-line"></div>
        </div>
        <div class="th-sig">
            <div class="th-sig-lbl">Consignee's Signature &amp; Date</div>
            <div class="th-sig-line"></div>
        </div>
    </div>

    <div class="th-footer">
        <?php echo htmlspecialchars($logistic_company); ?> | CN: <?php echo htmlspecialchars($s->waybill_number); ?> | <?php echo $current_date; ?>
        <?php if ($_ci['phone']): ?> | <?php echo htmlspecialchars($_ci['phone']); ?><?php endif; ?>
    </div>

</div><!-- /.th-wrap -->

</div>
</div>
<?php init_tail(); ?>
