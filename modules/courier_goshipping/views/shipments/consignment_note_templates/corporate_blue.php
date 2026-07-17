<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$_cb_total_kg = 0;
foreach ((array)($shipment_details['packages'] ?? []) as $_p) {
    $_cb_total_kg += (float)($_p->weight ?? 0);
}
$_cb_cn_color    = get_option('courier_cn_color') ?: '#1a2e5a';
$_cb_ship_inst   = trim($s->special_instructions ?? '');
?>
<style>
@media print { .cb-action{display:none!important;} body{background:#fff!important;} .cb-wrap{box-shadow:none!important;} }
.cb-action{max-width:960px;margin:12px auto;padding:0 10px;display:flex;gap:8px;}
.cb-action button,.cb-action a{
    background:#1a2e5a;color:#fff;border:none;padding:8px 22px;border-radius:5px;
    font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;
}
.cb-action a{background:#4a5568;}
.cb-wrap{max-width:960px;margin:0 auto 30px;font-family:'Segoe UI',Arial,sans-serif;font-size:11px;color:#1a1a1a;
    box-shadow:0 3px 16px rgba(0,0,0,.15);background:#fff;}
.cb-hdr-band{background:<?php echo htmlspecialchars($_cb_cn_color); ?>;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;}
.cb-hdr-left{color:#fff;}
.cb-co-name{font-size:20px;font-weight:900;letter-spacing:.5px;}
.cb-co-sub{font-size:10px;opacity:.8;margin-top:3px;line-height:1.6;}
.cb-hdr-center{text-align:center;color:#fff;}
.cb-doc-title{font-size:28px;font-weight:900;letter-spacing:2px;text-transform:uppercase;text-shadow:1px 1px 3px rgba(0,0,0,.3);}
.cb-doc-sub{font-size:11px;opacity:.85;margin-top:4px;letter-spacing:.5px;}
.cb-hdr-right{text-align:right;color:#fff;min-width:180px;}
.cb-cn-number{font-size:14px;font-weight:900;letter-spacing:.5px;}
.cb-meta{font-size:10px;opacity:.85;margin-top:4px;line-height:1.7;}
.cb-logo{margin-bottom:6px;}
.cb-logo img{max-height:55px;max-width:130px;filter:brightness(10);}

.cb-parties{display:grid;grid-template-columns:1fr 1fr;border-top:3px solid <?php echo htmlspecialchars($_cb_cn_color); ?>;}
.cb-party{padding:16px 20px;border-right:1px solid #d4ddf0;}
.cb-party:last-child{border-right:none;}
.cb-party-label{font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.8px;
    color:#fff;background:<?php echo htmlspecialchars($_cb_cn_color); ?>;padding:4px 10px;border-radius:3px;margin-bottom:10px;display:inline-block;}
.cb-party-name{font-size:14px;font-weight:800;color:#1a2e5a;margin-bottom:4px;}
.cb-party-info{font-size:11px;color:#444;line-height:1.7;}
.cb-party-info i{width:14px;color:#2c4a9a;}

.cb-table-wrap{padding:0 0;}
.cb-sec-hdr{background:#e8edf7;border-top:1px solid #c4cfe8;border-bottom:1px solid #c4cfe8;
    padding:6px 20px;font-size:11px;font-weight:900;color:#1a2e5a;text-transform:uppercase;letter-spacing:.5px;}
.cb-tbl{width:100%;border-collapse:collapse;font-size:11px;}
.cb-tbl th{background:<?php echo htmlspecialchars($_cb_cn_color); ?>;color:#fff;padding:8px 10px;text-align:left;font-size:10px;font-weight:700;}
.cb-tbl th.ctr{text-align:center;}
.cb-tbl td{padding:7px 10px;border:1px solid #d4ddf0;vertical-align:middle;}
.cb-tbl tr:nth-child(even) td{background:#f4f6fc;}
.cb-tbl td.ctr{text-align:center;}
.cb-total-row td{background:#e8edf7!important;font-weight:800;color:#1a2e5a;}

.cb-bottom{display:grid;grid-template-columns:1fr 200px;border-top:2px solid <?php echo htmlspecialchars($_cb_cn_color); ?>;background:#f4f6fc;}
.cb-decl{padding:16px 20px;border-right:1px solid #c4cfe8;}
.cb-decl-title{font-size:11px;font-weight:900;color:#1a2e5a;margin-bottom:6px;text-transform:uppercase;}
.cb-decl-text{font-size:10px;color:#444;line-height:1.6;}
.cb-weight-box{padding:16px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.cb-wt-label{font-size:10px;font-weight:800;color:#2c4a9a;text-transform:uppercase;letter-spacing:.5px;}
.cb-wt-value{font-size:24px;font-weight:900;color:#1a2e5a;margin-top:4px;}
.cb-wt-unit{font-size:11px;color:#666;}

.cb-sigs{display:grid;grid-template-columns:1fr 1fr 1fr;border-top:2px solid #2c4a9a;padding:16px 20px;gap:16px;}
.cb-sig{border:1px solid #c4cfe8;border-radius:6px;padding:10px 14px;background:#fff;}
.cb-sig-line{border-bottom:1.5px solid #1a2e5a;margin:22px 0 6px;width:80%;margin-left:auto;margin-right:auto;}
.cb-sig-label{text-align:center;font-size:10px;font-weight:700;color:#1a2e5a;}
.cb-sig-sub{text-align:center;font-size:9px;color:#888;margin-top:2px;}

.cb-footer{background:<?php echo htmlspecialchars($_cb_cn_color); ?>;color:rgba(255,255,255,.7);text-align:center;padding:8px;font-size:10px;}
</style>

<div id="wrapper">
<div class="content">

<div class="cb-action">
    <button onclick="window.print();"><i class="fa fa-print"></i> Print</button>
    <button onclick="downloadCbPdf();" style="background:#1565c0;"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
    <a href="<?php echo $waybill_back_url; ?>"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadCbPdf() {
    html2pdf().set({
        margin:[8,8,8,8],
        filename:'consignment-note-<?php echo htmlspecialchars($s->waybill_number); ?>.pdf',
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,useCORS:true},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
    }).from(document.querySelector('.cb-wrap')).save();
}
</script>

<div class="cb-wrap">

    <!-- Header band -->
    <div class="cb-hdr-band">
        <div class="cb-hdr-left">
            <?php if ($company_logo_url): ?>
                <div class="cb-logo"><img src="<?php echo $company_logo_url; ?>" alt="Logo"></div>
            <?php endif; ?>
            <div class="cb-co-name"><?php echo htmlspecialchars($logistic_company); ?></div>
            <div class="cb-co-sub">
                <?php if ($_ci['address']): ?><?php echo htmlspecialchars(str_replace("\n", ' | ', trim($_ci['address']))); ?><br><?php endif; ?>
                <?php if ($_ci['phone']): ?>Tel: <?php echo htmlspecialchars($_ci['phone']); ?><?php endif; ?>
                <?php if ($_ci['email']): ?> &nbsp;|&nbsp; <?php echo htmlspecialchars($_ci['email']); ?><?php endif; ?>
            </div>
        </div>
        <div class="cb-hdr-center">
            <div class="cb-doc-title">Consignment Note</div>
            <div class="cb-doc-sub"><?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?> SHIPMENT</div>
        </div>
        <div class="cb-hdr-right">
            <div class="cb-cn-number">CN # <?php echo htmlspecialchars($s->waybill_number); ?></div>
            <div class="cb-meta">
                Date: <?php echo $current_date; ?><br>
                Status: <?php echo htmlspecialchars($s->status_description ?? $s->status_name); ?>
                <?php if (!empty($s->is_round_trip)): ?><br><span style="background:rgba(255,255,255,.2);padding:1px 6px;border-radius:8px;font-size:9px;">Round Trip</span><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="cb-parties">
        <div class="cb-party">
            <div class="cb-party-label"><i class="fa fa-arrow-up"></i> Shipper / Consignor</div>
            <div class="cb-party-name"><?php echo htmlspecialchars($snd_name); ?></div>
            <div class="cb-party-info">
                <?php if ($snd_addr): ?><div><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?></div><?php endif; ?>
                <?php if ($snd_phone): ?><div><i class="fa fa-phone"></i> <?php echo htmlspecialchars($snd_phone); ?></div><?php endif; ?>
                <?php if ($snd_email): ?><div><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($snd_email); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="cb-party">
            <div class="cb-party-label"><i class="fa fa-arrow-down"></i> Consignee / Receiver</div>
            <div class="cb-party-name"><?php echo htmlspecialchars($rec_name); ?></div>
            <div class="cb-party-info">
                <?php if ($rec_addr): ?><div><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?></div><?php endif; ?>
                <?php if ($rec_phone): ?><div><i class="fa fa-phone"></i> <?php echo htmlspecialchars($rec_phone); ?></div><?php endif; ?>
                <?php if ($rec_email): ?><div><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($rec_email); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Package table -->
    <div class="cb-sec-hdr">Package Details</div>
    <div class="cb-table-wrap">
    <table class="cb-tbl">
        <thead>
        <?php if ($s->fcl_shipment == 1): ?>
            <tr><th>#</th><th>Description</th><th class="ctr">Qty</th><th class="ctr">FCL Option</th></tr>
        <?php else: ?>
            <tr><th>#</th><th>Description</th><th class="ctr">Qty</th><th class="ctr">L(cm)</th><th class="ctr">W(cm)</th><th class="ctr">H(cm)</th><th class="ctr">Vol.Wt(kg)</th><th class="ctr">Gross Wt(kg)</th><th class="ctr">Chargeable(kg)</th></tr>
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

    <!-- Declaration + Weight -->
    <div class="cb-bottom">
        <div class="cb-decl">
            <div class="cb-decl-title">Shipper's Declaration</div>
            <?php if ($_cb_ship_inst): ?>
            <div style="font-size:10px;font-weight:700;color:#c0392b;margin-bottom:6px;">
                <strong>Special Instructions:</strong> <?php echo nl2br(htmlspecialchars($_cb_ship_inst)); ?>
            </div>
            <?php endif; ?>
            <div class="cb-decl-text">I certify that the particulars given above are correct and that this consignment does not contain any dangerous goods, restricted or prohibited items or contraband. The goods are properly packed, marked and labelled in proper condition for carriage.</div>
        </div>
        <div class="cb-weight-box">
            <div class="cb-wt-label">Total Weight</div>
            <div class="cb-wt-value"><?php echo $_cb_total_kg > 0 ? number_format($_cb_total_kg, 2) : '—'; ?></div>
            <div class="cb-wt-unit">kilograms</div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="cb-sigs">
        <div class="cb-sig"><div class="cb-sig-line"></div><div class="cb-sig-label">Shipper's Signature</div><div class="cb-sig-sub">Name &amp; Date</div></div>
        <div class="cb-sig"><div class="cb-sig-line"></div><div class="cb-sig-label">Carrier / Driver</div><div class="cb-sig-sub">ID / Truck No. &amp; Date</div></div>
        <div class="cb-sig"><div class="cb-sig-line"></div><div class="cb-sig-label">Consignee's Signature</div><div class="cb-sig-sub">Name, Signature &amp; Date</div></div>
    </div>

    <div class="cb-footer">
        <?php echo htmlspecialchars($logistic_company); ?> &mdash; CN: <?php echo htmlspecialchars($s->waybill_number); ?> &mdash; <?php echo $current_date; ?>
        <?php if ($_ci['website']): ?> &mdash; <?php echo htmlspecialchars($_ci['website']); ?><?php endif; ?>
    </div>
</div>

</div>
</div>
<?php init_tail(); ?>
