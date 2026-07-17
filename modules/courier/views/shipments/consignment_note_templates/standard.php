<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$_cn_color     = get_option('courier_cn_color') ?: '#2e7d32';
// Derive a slightly darker shade for gradients (simple darkening via inline style)
$_cn_color_dark = $_cn_color; // used as-is; gradient adds depth
?>
<style>
:root { --cn: <?php echo htmlspecialchars($_cn_color); ?>; }
.doc-page  { max-width:900px; margin:0 auto; }
.doc-card  { background:#fff; border:2px solid var(--cn); border-radius:8px; padding:24px 30px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.doc-header{ display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid var(--cn); padding-bottom:14px; margin-bottom:16px; }
.doc-title  { font-size:26px; font-weight:800; color:var(--cn); letter-spacing:1px; text-align:center; }
.doc-subtitle{ font-size:12px; color:#888; text-align:center; letter-spacing:.5px; }
.cn-grid   { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin:12px 0; }
.cn-box    { border:1px solid var(--cn); border-radius:6px; padding:12px 14px; }
.cn-box h5 { margin:0 0 8px; font-size:11px; font-weight:800; text-transform:uppercase; color:var(--cn); letter-spacing:.5px; background:rgba(0,0,0,.04); padding:4px 8px; border-radius:4px; }
.cn-box p  { margin:3px 0; font-size:12px; color:#333; }
.cn-table  { width:100%; border-collapse:collapse; margin:12px 0; font-size:12px; }
.cn-table th{ background:var(--cn); color:#fff; padding:7px 10px; text-align:left; }
.cn-table td{ padding:7px 10px; border:1px solid #ddd; }
.cn-table tr:nth-child(even) td { background:rgba(0,0,0,.03); }
.vat-badge{ display:inline-block; background:rgba(0,0,0,.05); color:var(--cn); border:1px solid var(--cn); border-radius:12px; font-size:11px; font-weight:700; padding:2px 10px; }
.sig-grid  { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:20px; }
.sig-block { border:1px solid var(--cn); border-radius:6px; padding:10px; text-align:center; }
.sig-line  { border-top:1px solid #777; width:120px; margin:28px auto 6px; }
.sig-label { font-size:11px; color:#555; font-weight:600; }
.sig-sub   { font-size:10px; color:#aaa; margin-top:2px; }
.decl-box  { background:rgba(0,0,0,.03); border:1px solid var(--cn); border-radius:6px; padding:10px 14px; font-size:11px; color:var(--cn); margin:10px 0; }
.cn-action-bar { display:flex; gap:10px; align-items:center; margin-bottom:20px; flex-wrap:wrap; max-width:900px; margin-left:auto; margin-right:auto; }
.btn-print { background:var(--cn); color:#fff; border:none; padding:8px 20px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-pdf   { background:#1565c0; color:#fff; border:none; padding:8px 20px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-back  { background:#fff; color:var(--cn); border:1px solid var(--cn); padding:8px 18px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; }
.btn-back:hover { background:rgba(0,0,0,.04); text-decoration:none; }
@media print { .cn-action-bar { display:none!important; } .doc-card { box-shadow:none; } body { background:#fff; } }
</style>

<div id="wrapper">
<div class="content">
<div class="row">
<div class="col-md-12">
<div class="doc-page">

    <div class="cn-action-bar">
        <button class="btn-print" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
        <button class="btn-pdf" onclick="downloadCnPdf();"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
        <a class="btn-back" href="<?php echo $waybill_back_url; ?>"><i class="fa fa-arrow-left"></i> Back to Waybill</a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    function downloadCnPdf() {
        var el = document.querySelector('.doc-card');
        html2pdf().set({
            margin: [10,10,10,10],
            filename: 'consignment-note-<?php echo htmlspecialchars($s->waybill_number); ?>.pdf',
            image: { type:'jpeg', quality:0.98 },
            html2canvas: { scale:2, useCORS:true },
            jsPDF: { unit:'mm', format:'a4', orientation:'portrait' }
        }).from(el).save();
    }
    </script>

    <div class="doc-card">
        <!-- Header -->
        <div class="doc-header">
            <div>
                <?php if ($company_logo_url): ?>
                    <img src="<?php echo $company_logo_url; ?>" height="50" alt="Logo">
                <?php else: ?>
                    <span style="font-size:16px; font-weight:700;"><?php echo htmlspecialchars($logistic_company); ?></span>
                <?php endif; ?>
            </div>
            <div style="text-align:center; flex:1;">
                <div class="doc-title">CONSIGNMENT NOTE</div>
                <div class="doc-subtitle"><?php echo htmlspecialchars($logistic_company); ?></div>
                <div style="font-size:11px; color:#666; margin-top:4px; line-height:1.6;">
                    <?php if (!empty($_ci['address'])): ?><?php echo nl2br(htmlspecialchars($_ci['address'])); ?><br><?php endif; ?>
                    <?php if (!empty($_ci['phone'])): ?><i class="fa fa-phone"></i> <?php echo htmlspecialchars($_ci['phone']); ?> &nbsp;<?php endif; ?>
                    <?php if (!empty($_ci['email'])): ?><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($_ci['email']); ?><?php endif; ?>
                    <?php if (!empty($_ci['website'])): ?><br><i class="fa fa-globe"></i> <?php echo htmlspecialchars($_ci['website']); ?><?php endif; ?>
                    <?php if (!empty($_ci['pin'])): ?> &nbsp; PIN: <?php echo htmlspecialchars($_ci['pin']); ?><?php endif; ?>
                </div>
                <?php if (!empty($s->is_round_trip)): ?>
                    <span class="vat-badge" style="margin-top:6px; display:inline-block;">Round Trip</span>
                <?php endif; ?>
            </div>
            <div style="text-align:right; font-size:12px; color:#555; line-height:1.8; min-width:180px;">
                <strong>CN #:</strong> <?php echo htmlspecialchars($s->waybill_number); ?><br>
                <strong>Date:</strong> <?php echo $current_date; ?><br>
                <strong>Mode:</strong> <?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?><br>
                <strong>Status:</strong> <?php echo htmlspecialchars($s->status_description ?? $s->status_name); ?>
            </div>
        </div>

        <!-- Shipper / Consignee -->
        <div class="cn-grid">
            <div class="cn-box">
                <h5><i class="fa fa-arrow-up"></i> Shipper (From)</h5>
                <p><strong><?php echo htmlspecialchars($snd_name); ?></strong></p>
                <?php if ($snd_addr): ?><p><?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?></p><?php endif; ?>
                <?php if ($snd_phone): ?><p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($snd_phone); ?></p><?php endif; ?>
                <?php if ($snd_email): ?><p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($snd_email); ?></p><?php endif; ?>
            </div>
            <div class="cn-box">
                <h5><i class="fa fa-arrow-down"></i> Consignee (To)</h5>
                <p><strong><?php echo htmlspecialchars($rec_name); ?></strong></p>
                <?php if ($rec_addr): ?><p><?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?></p><?php endif; ?>
                <?php if ($rec_phone): ?><p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($rec_phone); ?></p><?php endif; ?>
                <?php if ($rec_email): ?><p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($rec_email); ?></p><?php endif; ?>
            </div>
        </div>

        <!-- Package details -->
        <?php if (!empty($shipment_details['packages'])): ?>
        <table class="cn-table">
            <thead>
            <?php if ($s->fcl_shipment == 1): ?>
                <tr><th>#</th><th>Description</th><th>Qty</th><th>FCL Option</th></tr>
            <?php else: ?>
                <tr><th>#</th><th>Description</th><th>Qty</th><th>L(cm)</th><th>W(cm)</th><th>H(cm)</th><th>Vol.Wt(kg)</th><th>Gross Wt(kg)</th><th>Chargeable(kg)</th></tr>
            <?php endif; ?>
            </thead>
            <tbody>
            <?php $n = 1; foreach ($shipment_details['packages'] as $pkg): ?>
                <?php if ($s->fcl_shipment == 1): ?>
                <tr><td><?php echo $n++; ?></td><td><?php echo htmlspecialchars($pkg->description); ?></td><td><?php echo $pkg->quantity; ?></td><td><?php echo htmlspecialchars($pkg->fcl_option); ?></td></tr>
                <?php else: ?>
                <tr>
                    <td><?php echo $n++; ?></td>
                    <td><?php echo htmlspecialchars($pkg->description); ?></td>
                    <td><?php echo $pkg->quantity; ?></td>
                    <td><?php echo $pkg->length; ?></td><td><?php echo $pkg->width; ?></td><td><?php echo $pkg->height; ?></td>
                    <td><?php echo $pkg->weight_volume; ?></td>
                    <td><?php echo $pkg->weight; ?></td>
                    <td><strong><?php echo $pkg->chargeable_weight; ?></strong></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="cn-box" style="margin-top:10px;">
            <h5><i class="fa fa-info-circle"></i> Special Instructions</h5>
            <?php
            $_cn_sys_notes = trim(get_option('courier_cn_special_notes') ?: '');
            $_cn_ship_inst = trim($s->special_instructions ?? '');
            if ($_cn_ship_inst): ?>
            <p style="font-weight:600; color:#333;"><i class="fa fa-exclamation-triangle" style="color:#e65100;"></i> <?php echo nl2br(htmlspecialchars($_cn_ship_inst)); ?></p>
            <?php elseif ($_cn_sys_notes):
                $notes_arr = array_values(array_filter(array_map('trim', explode("\n", $_cn_sys_notes))));
                foreach ($notes_arr as $n): ?>
                <p style="font-size:11px; color:#333;"><?php echo htmlspecialchars($n); ?></p>
            <?php endforeach;
            else: ?>
            <p style="color:#e65100; font-weight:600;"><i class="fa fa-exclamation-triangle"></i> Handle with care. Fragile items.</p>
            <p>Keep dry and upright. Do not stack above marked weight.</p>
            <?php endif; ?>
            <?php if (!empty($s->is_round_trip)): ?>
            <p style="color:#1565c0; font-weight:600;"><i class="fa fa-refresh"></i> Round Trip Shipment</p>
            <?php endif; ?>
        </div>

        <div class="decl-box">
            <strong>Shipper's Declaration:</strong> I certify that the particulars given above are correct and that this consignment does not contain any dangerous goods, restricted items or contraband.
        </div>

        <div class="sig-grid">
            <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Shipper's Signature</div><div class="sig-sub">Name &amp; Date</div></div>
            <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Carrier's Signature</div><div class="sig-sub">Driver / Agent &amp; Date</div></div>
            <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Consignee's Signature</div><div class="sig-sub">Name, Signature &amp; Date</div></div>
        </div>

        <div style="text-align:center; margin-top:14px; font-size:11px; color:#bbb;">
            Consignment Note &mdash; <?php echo htmlspecialchars($logistic_company); ?> &mdash;
            CN: <?php echo htmlspecialchars($s->waybill_number); ?> &mdash; <?php echo $current_date; ?>
        </div>
    </div>

</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
