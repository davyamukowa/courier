<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/* Compute total weight */
$_shvn_total_kg = 0;
foreach ((array)($shipment_details['packages'] ?? []) as $_p) {
    $_shvn_total_kg += (float)($_p->weight ?? 0);
}
$_shvn_other_offices  = get_option('courier_other_offices') ?: '';
$_shvn_special_notes  = get_option('courier_cn_special_notes') ?: '';
$_shvn_ship_inst      = trim($s->special_instructions ?? '');
$_shvn_cn_color       = get_option('courier_cn_color') ?: '#2e7d32';
?>
<style>
@media print {
    .shvn-action { display:none!important; }
    body { background:#fff!important; margin:0; }
    .shvn-wrap { box-shadow:none!important; max-width:100%!important; }
}
.shvn-action {
    max-width:940px; margin:10px auto; padding:0 10px;
    display:flex; gap:10px;
}
.shvn-action button, .shvn-action a {
    background:#222; color:#fff; border:none;
    padding:7px 20px; border-radius:4px; font-size:13px;
    cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px;
}
.shvn-action a { background:#555; }

.shvn-wrap {
    max-width:940px; margin:0 auto 30px;
    font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#000;
    border:2px solid #000;
    box-shadow:0 2px 12px rgba(0,0,0,.15);
}
/* ── Header ───────────────────────────────────────────────────────────── */
.shvn-hdr {
    display:grid; grid-template-columns:1fr 1.1fr 160px;
    border-bottom:2px solid #000;
}
.shvn-hdr-co { padding:8px 12px; border-right:1px solid #000; }
.shvn-hdr-title { padding:8px 12px; text-align:center; border-right:1px solid #000; }
.shvn-hdr-logo { padding:8px 12px; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.shvn-co-name { font-size:16px; font-weight:900; letter-spacing:.3px; }
.shvn-co-addr { font-size:9.5px; line-height:1.6; margin-top:3px; }
.shvn-doc-title { font-size:22px; font-weight:900; letter-spacing:1.5px; margin-bottom:4px; }
.shvn-cn-no { font-size:11px; font-weight:700; }
/* ── Other offices bar ─────────────────────────────────────────────── */
.shvn-offices {
    border-bottom:1px solid #000;
    display:grid; grid-template-columns:160px 1fr 160px;
    font-size:9.5px;
}
.shvn-offices-mid { padding:4px 10px; text-align:center; border-left:1px solid #000; border-right:1px solid #000; }
.shvn-offices-side { padding:4px 10px; font-size:9px; color:#333; }
/* ── Form boxes ────────────────────────────────────────────────────── */
.shvn-form-row {
    display:grid; grid-template-columns:1fr 1fr 190px;
    border-bottom:1px solid #000; min-height:90px;
}
.shvn-form-col { border-right:1px solid #000; }
.shvn-form-col:last-child { border-right:none; }
.shvn-col-hdr {
    background:<?php echo htmlspecialchars($_shvn_cn_color); ?>; color:#fff;
    font-size:10px; font-weight:900; text-transform:uppercase;
    padding:3px 8px; display:flex; justify-content:space-between; align-items:center;
}
.shvn-chk { width:13px; height:13px; border:1.5px solid #fff; display:inline-block; }
.shvn-col-body { padding:6px 8px; }
.shvn-field-name { font-size:12px; font-weight:700; }
.shvn-field-line { font-size:10px; color:#333; line-height:1.6; }
.shvn-chargeto-hdr { background:<?php echo htmlspecialchars($_shvn_cn_color); ?>; color:#fff; font-size:10px; font-weight:900; padding:3px 8px; }
.shvn-chargeto-body { padding:5px 8px; }
.shvn-chargeto-must { font-size:9px; font-weight:800; }
.shvn-chargeto-warn { font-size:9px; font-weight:800; color:#cc0000; text-transform:uppercase; line-height:1.4; margin:3px 0 6px; }
.shvn-dotline { border-bottom:1px dotted #777; min-height:15px; margin:4px 0; }
/* ── ID row ──────────────────────────────────────────────────────── */
.shvn-id-row {
    display:grid; grid-template-columns:1fr 1fr 190px;
    border-bottom:1px solid #000;
}
.shvn-id-cell { padding:4px 8px; font-size:10px; font-weight:700; border-right:1px solid #000; }
.shvn-id-cell:last-child { border-right:none; font-weight:400; color:#555; }
/* ── Details table ───────────────────────────────────────────────── */
.shvn-sec-title {
    background:#ddd; border-bottom:1px solid #000;
    font-size:11px; font-weight:900; text-transform:uppercase;
    padding:4px 8px; letter-spacing:.3px;
}
.shvn-tbl { width:100%; border-collapse:collapse; font-size:10px; }
.shvn-tbl th {
    background:<?php echo htmlspecialchars($_shvn_cn_color); ?>; color:#fff;
    padding:4px 6px; text-align:left;
    border:1px solid <?php echo htmlspecialchars($_shvn_cn_color); ?>; font-weight:700; font-size:9.5px;
}
.shvn-tbl th.ctr { text-align:center; }
.shvn-tbl td { padding:3px 6px; border:1px solid #aaa; height:18px; vertical-align:top; }
/* ── Bottom row ──────────────────────────────────────────────────── */
.shvn-bot {
    display:grid; grid-template-columns:230px 1fr 150px;
    border-top:1px solid #000; min-height:80px;
}
.shvn-bot-col { border-right:1px solid #000; padding:6px 8px; }
.shvn-bot-col:last-child { border-right:none; text-align:center; }
.shvn-bot-lbl { font-size:10px; font-weight:800; text-transform:uppercase; margin-bottom:5px; }
.shvn-comments-box { border:1px solid #bbb; min-height:52px; }
.shvn-received { display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:900; text-align:center; text-transform:uppercase; line-height:1.4; }
/* ── Special notes ───────────────────────────────────────────────── */
.shvn-notes { border-top:2px solid #000; padding:8px 12px; }
.shvn-notes-title { font-size:15px; font-weight:900; margin-bottom:5px; letter-spacing:.3px; }
.shvn-notes ol { margin:0; padding-left:18px; }
.shvn-notes li { font-size:10px; margin:3px 0; line-height:1.5; }
/* ── Signature rows ──────────────────────────────────────────────── */
.shvn-sig1, .shvn-sig2 {
    border-top:1px solid #000;
    display:grid; padding:6px 10px; gap:12px; font-size:10px;
}
.shvn-sig1 { grid-template-columns:1fr 1fr 1fr; }
.shvn-sig2 { grid-template-columns:1.2fr 0.6fr 1.2fr 0.6fr; }
.shvn-sf { }
.shvn-sf-lbl { font-weight:700; margin-bottom:2px; }
.shvn-sf-line { border-bottom:1px solid #000; min-height:20px; }
</style>

<div id="wrapper">
<div class="content">

<div class="shvn-action">
    <button onclick="window.print();"><i class="fa fa-print"></i> Print</button>
    <button onclick="downloadShvnPdf();" style="background:#1565c0;"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
    <a href="<?php echo $waybill_back_url; ?>"><i class="fa fa-arrow-left"></i> Back to Waybill</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadShvnPdf() {
    html2pdf().set({
        margin:[8,8,8,8],
        filename:'consignment-note-<?php echo htmlspecialchars($s->waybill_number); ?>.pdf',
        image:{type:'jpeg',quality:0.98},
        html2canvas:{scale:2,useCORS:true},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
    }).from(document.querySelector('.shvn-wrap')).save();
}
</script>

<div class="shvn-wrap">

    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <div class="shvn-hdr" style="border-bottom:2px solid <?php echo htmlspecialchars($_shvn_cn_color); ?>;">
        <div class="shvn-hdr-co">
            <div class="shvn-co-name" style="color:<?php echo htmlspecialchars($_shvn_cn_color); ?>;"><?php echo htmlspecialchars($logistic_company); ?></div>
            <?php if ($_ci['address']): ?>
            <div class="shvn-co-addr"><?php echo nl2br(htmlspecialchars($_ci['address'])); ?></div>
            <?php endif; ?>
            <?php if ($_ci['phone']): ?><div style="font-size:10px;">TEL: <?php echo htmlspecialchars($_ci['phone']); ?></div><?php endif; ?>
            <?php if ($_ci['email']): ?><div style="font-size:10px;">Email: <?php echo htmlspecialchars($_ci['email']); ?></div><?php endif; ?>
            <?php if ($_ci['website']): ?><div style="font-size:10px;"><?php echo htmlspecialchars($_ci['website']); ?></div><?php endif; ?>
            <?php if ($_ci['pin']): ?><div style="font-size:10px;">PIN: <?php echo htmlspecialchars($_ci['pin']); ?></div><?php endif; ?>
        </div>
        <div class="shvn-hdr-title">
            <div class="shvn-doc-title">CONSIGNMENT NOTE</div>
            <div class="shvn-cn-no">CONSIGNMENT No: <strong><?php echo htmlspecialchars($s->waybill_number); ?></strong></div>
            <div style="font-size:10px; color:#444; margin-top:4px;"><?php echo $current_date; ?> &nbsp;|&nbsp; <?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?></div>
            <?php if ($_ci['tagline']): ?><div style="font-size:9px; font-style:italic; margin-top:5px; color:#555;"><?php echo htmlspecialchars($_ci['tagline']); ?></div><?php endif; ?>
        </div>
        <div class="shvn-hdr-logo">
            <?php if ($company_logo_url): ?>
                <img src="<?php echo $company_logo_url; ?>" style="max-width:140px; max-height:65px;" alt="Logo">
            <?php else: ?>
                <div style="font-size:13px; font-weight:900; color:#000; text-align:center;"><?php echo htmlspecialchars($logistic_company); ?></div>
            <?php endif; ?>
            <?php if ($_ci['tagline']): ?><div style="font-size:8px; font-style:italic; color:#555; margin-top:4px; text-align:center;"><?php echo htmlspecialchars($_ci['tagline']); ?></div><?php endif; ?>
        </div>
    </div>

    <!-- ── Other offices ───────────────────────────────────────────────── -->
    <?php if ($_shvn_other_offices): ?>
    <div class="shvn-offices">
        <div class="shvn-offices-side"></div>
        <div class="shvn-offices-mid">
            <strong>Other offices</strong><br>
            <?php echo nl2br(htmlspecialchars($_shvn_other_offices)); ?>
        </div>
        <div class="shvn-offices-side"></div>
    </div>
    <?php endif; ?>

    <!-- ── Consignor / Consignee / Charge To ───────────────────────────── -->
    <div class="shvn-form-row">
        <div class="shvn-form-col">
            <div class="shvn-col-hdr">
                <span>CONSIGNOR/SENDER</span>
                <span>CHARGE &nbsp;<span class="shvn-chk"></span></span>
            </div>
            <div class="shvn-col-body">
                <div class="shvn-field-name"><?php echo htmlspecialchars($snd_name); ?></div>
                <?php if ($snd_addr): ?><div class="shvn-field-line"><?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?></div><?php endif; ?>
                <?php if ($snd_phone): ?><div class="shvn-field-line">Tel: <?php echo htmlspecialchars($snd_phone); ?></div><?php endif; ?>
                <?php if ($snd_email): ?><div class="shvn-field-line"><?php echo htmlspecialchars($snd_email); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="shvn-form-col">
            <div class="shvn-col-hdr">
                <span>CONSIGNEE/RECEIVER</span>
                <span>CHARGE &nbsp;<span class="shvn-chk"></span></span>
            </div>
            <div class="shvn-col-body">
                <div class="shvn-field-name"><?php echo htmlspecialchars($rec_name); ?></div>
                <?php if ($rec_addr): ?><div class="shvn-field-line"><?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?></div><?php endif; ?>
                <?php if ($rec_phone): ?><div class="shvn-field-line">Tel: <?php echo htmlspecialchars($rec_phone); ?></div><?php endif; ?>
                <?php if ($rec_email): ?><div class="shvn-field-line"><?php echo htmlspecialchars($rec_email); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="shvn-form-col">
            <div class="shvn-chargeto-hdr">CHARGE TO</div>
            <div class="shvn-chargeto-body">
                <div class="shvn-chargeto-must">(MUST BE COMPLETED)</div>
                <div class="shvn-chargeto-warn">FRAGILE/DANGEROUS AND HAZARDOUS<br>MUST BE DECLARED</div>
                <div class="shvn-dotline"></div>
                <div class="shvn-dotline"></div>
                <div class="shvn-dotline"></div>
            </div>
        </div>
    </div>

    <!-- ── ID row ───────────────────────────────────────────────────────── -->
    <div class="shvn-id-row">
        <div class="shvn-id-cell">ID/PASSPORT NO. &nbsp;_______________________</div>
        <div class="shvn-id-cell">ID/PASSPORT NO. &nbsp;_______________________</div>
        <div class="shvn-id-cell">Status: <?php echo htmlspecialchars($s->status_description ?? $s->status_name ?? ''); ?></div>
    </div>

    <!-- ── Onforwarding details ─────────────────────────────────────────── -->
    <div class="shvn-sec-title">ONFORWARDING DETAILS</div>
    <table class="shvn-tbl">
        <thead>
            <tr>
                <th rowspan="2" style="width:130px;">Consignor's/Sender's<br>Reference</th>
                <th rowspan="2" style="width:55px;" class="ctr">No. of<br>Items</th>
                <th rowspan="2">Description</th>
                <th colspan="3" class="ctr" style="width:135px;">MEASUREMENTS</th>
                <th colspan="2" class="ctr" style="width:120px;">WEIGHT</th>
            </tr>
            <tr>
                <th class="ctr" style="width:45px;">L(cm)</th>
                <th class="ctr" style="width:45px;">W(cm)</th>
                <th class="ctr" style="width:45px;">H(cm)</th>
                <th class="ctr" style="width:55px;">Tonnes</th>
                <th class="ctr" style="width:55px;">Kilograms</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rows_shown = 0;
        if (!empty($shipment_details['packages'])) {
            foreach ($shipment_details['packages'] as $pkg) {
                $rows_shown++;
                ?>
                <tr>
                    <td></td>
                    <td style="text-align:center;"><?php echo intval($pkg->quantity); ?></td>
                    <td><?php echo htmlspecialchars($pkg->description); ?></td>
                    <?php if ($s->fcl_shipment == 1): ?>
                        <td colspan="3" style="text-align:center;"><?php echo htmlspecialchars($pkg->fcl_option); ?></td>
                    <?php else: ?>
                        <td style="text-align:center;"><?php echo $pkg->length; ?></td>
                        <td style="text-align:center;"><?php echo $pkg->width; ?></td>
                        <td style="text-align:center;"><?php echo $pkg->height; ?></td>
                    <?php endif; ?>
                    <td></td>
                    <td style="text-align:center;"><?php echo $pkg->weight; ?></td>
                </tr>
                <?php
            }
        }
        for ($i = $rows_shown; $i < 6; $i++) {
            echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        }
        ?>
        </tbody>
    </table>

    <!-- ── Bottom: Feedback / Received / Total ─────────────────────────── -->
    <div class="shvn-bot">
        <div class="shvn-bot-col">
            <div class="shvn-bot-lbl" style="color:#cc0000;">FEEDBACK / COMMENTS</div>
            <div class="shvn-comments-box"></div>
        </div>
        <div class="shvn-bot-col shvn-received">
            RECEIVED GOODS IN GOOD ORDER &amp; CONDITION
        </div>
        <div class="shvn-bot-col">
            <div style="font-size:11px; font-weight:900; margin-bottom:6px;">TOTAL<br>WEIGHT</div>
            <div style="font-size:18px; font-weight:900;"><?php echo $_shvn_total_kg > 0 ? number_format($_shvn_total_kg, 2) . ' kg' : ''; ?></div>
        </div>
    </div>

    <!-- ── Special notes ───────────────────────────────────────────────── -->
    <div class="shvn-notes">
        <div class="shvn-notes-title">SPECIAL NOTES</div>
        <?php if ($_shvn_ship_inst): ?>
        <p style="font-size:11px;font-weight:700;color:#cc0000;margin:4px 0 8px;">
            <strong>Shipment Instructions:</strong> <?php echo nl2br(htmlspecialchars($_shvn_ship_inst)); ?>
        </p>
        <?php endif; ?>
        <?php if ($_shvn_special_notes): ?>
            <?php
            $notes_list = array_values(array_filter(array_map('trim', explode("\n", trim($_shvn_special_notes)))));
            echo '<ol>';
            foreach ($notes_list as $note) {
                echo '<li>' . nl2br(htmlspecialchars($note)) . '</li>';
            }
            echo '</ol>';
            ?>
        <?php else: ?>
        <ol>
            <li>No responsibility is held by us for any damages, leakages or theft for all goods carried by us.</li>
            <li>Cash payment <strong style="color:#cc0000;">IS NOT ACCEPTED</strong> at any given time and no employee/agent should ask for cash payment; all payment should be made through authorised Bank Accounts or Till/Paybill Numbers.</li>
            <li>The company will not accept any liability exceeding Kshs. 500 per consignment. Goods whose value is more than Kshs 500 should be insured by the owner.</li>
            <li>Storage charges will commence on the 2nd day of arrival.</li>
            <li>No claims (all claims must be made in writing) will be entertained whatsoever after 7 days from the day of delivery.</li>
        </ol>
        <?php endif; ?>
    </div>

    <!-- ── Signatures row 1 ────────────────────────────────────────────── -->
    <div class="shvn-sig1">
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Driver:</div>
            <div class="shvn-sf-line"></div>
        </div>
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Truck No.</div>
            <div class="shvn-sf-line"></div>
        </div>
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Consignee (Receiver's) Signature:</div>
            <div class="shvn-sf-line"></div>
        </div>
    </div>

    <!-- ── Signatures row 2 ────────────────────────────────────────────── -->
    <div class="shvn-sig2">
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Consignor's (Sender's) Signature</div>
            <div class="shvn-sf-line"></div>
        </div>
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Date &nbsp; _____ / _____ / _____</div>
            <div class="shvn-sf-line"></div>
        </div>
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Driver's Signature / ID / PASSPORT NO:</div>
            <div class="shvn-sf-line"></div>
        </div>
        <div class="shvn-sf">
            <div class="shvn-sf-lbl">Date &nbsp; _____ / _____ / _____</div>
            <div class="shvn-sf-line"></div>
        </div>
    </div>

</div><!-- /.shvn-wrap -->

</div>
</div>
<?php init_tail(); ?>
