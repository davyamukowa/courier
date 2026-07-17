<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<style>
.doc-page { max-width:860px; margin:0 auto; }
.doc-card  { background:#fff; border:1px solid #ddd; border-radius:8px; padding:30px 36px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.doc-header{ display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #1b5e20; padding-bottom:16px; margin-bottom:20px; }
.doc-title  { font-size:30px; font-weight:800; color:#1b5e20; letter-spacing:1px; }
.doc-meta   { text-align:right; font-size:13px; color:#555; line-height:1.7; }
.doc-meta strong { color:#222; }
.parties-row{ display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:16px 0; }
.party-box  { border:1px solid #e0e0e0; border-radius:6px; padding:12px 16px; background:#f9f9f9; }
.party-box h5{ margin:0 0 8px; font-size:12px; font-weight:800; text-transform:uppercase; color:#1b5e20; letter-spacing:.5px; }
.party-box p { margin:2px 0; font-size:13px; color:#333; }
.items-table{ width:100%; border-collapse:collapse; margin:18px 0; font-size:13px; }
.items-table th{ background:#1b5e20; color:#fff; padding:9px 12px; text-align:left; }
.items-table td{ padding:8px 12px; border-bottom:1px solid #eee; }
.items-table tr:last-child td { border-bottom:none; }
.totals-table{ width:280px; margin-left:auto; border-collapse:collapse; font-size:13px; margin-top:4px; }
.totals-table td{ padding:6px 10px; }
.totals-table .total-row{ font-weight:800; font-size:15px; background:#1b5e20; color:#fff; }
.vat-badge  { display:inline-block; background:#e8f5e9; color:#1b5e20; border:1px solid #a5d6a7; border-radius:12px; font-size:11px; font-weight:700; padding:2px 10px; margin-left:6px; }
.doc-note   { background:#fffde7; border:1px solid #f9a825; border-radius:6px; padding:12px 16px; font-size:12px; color:#555; margin-top:16px; }
.doc-footer { display:flex; justify-content:space-between; margin-top:30px; padding-top:16px; border-top:1px solid #ddd; font-size:12px; color:#777; }
.sig-block  { text-align:center; }
.sig-line   { border-top:1px solid #666; width:160px; margin:30px auto 4px; }
.action-bar { display:flex; gap:10px; align-items:center; margin-bottom:20px; flex-wrap:wrap; }
.btn-print  { background:#1b5e20; color:#fff; border:none; padding:8px 20px; border-radius:4px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-back   { background:#fff; color:#333; border:1px solid #ccc; padding:8px 18px; border-radius:4px; font-size:13px; font-weight:600; text-decoration:none; }
@media print {
    .cgs-topnav { display:none!important; }
    .action-bar { display:none!important; }
    #wrapper > .cgs-page > .row > div:first-child { display:none!important; }
    .doc-card { box-shadow:none; border:none; }
    body { background:#fff; }
}
</style>

<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>

<div class="row">
<div class="col-md-12">
<div class="doc-page">

    <div class="action-bar">
        <button class="btn-print" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
        <a class="btn-back" href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $shipment_details['shipment']->id); ?>">
            <i class="fa fa-arrow-left"></i> Back to Waybill
        </a>
    </div>

    <div class="doc-card" id="quotation-doc">
        <?php
        /* ── Resolve company ─────────────────────────────────────────── */
        $company_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
        $company_logo_url  = !empty($company_logo_file) ? base_url('uploads/company/' . $company_logo_file) : '';
        $_ci               = courier_get_invoice_info();
        $logistic_company  = $_ci['name'] ?: 'Our Company';
        $s                 = $shipment_details['shipment'];

        /* ── Sender / Recipient ────────────────────────────────────────── */
        $is_sender_ind = $shipment_details['sender_type'] === 'individual';
        $sender        = $shipment_details['sender'];
        $snd_name      = $is_sender_ind ? trim($sender->first_name . ' ' . $sender->last_name)
                                        : $sender->company_name . ' (' . $sender->contact_person_name . ')';
        $snd_addr      = $is_sender_ind ? $sender->address . ', ' . $sender->zipcode
                                        : $sender->contact_address . ', ' . $sender->contact_zipcode;
        $snd_phone     = $is_sender_ind ? $sender->phone_number : $sender->contact_person_phone_number;
        $snd_email     = $is_sender_ind ? ($sender->email ?? '') : ($sender->contact_person_email ?? '');
        $snd_country   = !empty($shipment_details['sender_country']) ? $shipment_details['sender_country']->short_name : '';

        $is_recip_ind  = $shipment_details['recipient_type'] === 'individual';
        $recipient     = $shipment_details['recipient'];
        $rec_name      = $is_recip_ind ? trim($recipient->first_name . ' ' . $recipient->last_name)
                                       : ($recipient->recipient_company_name ?? '') . (!empty($recipient->recipient_contact_person_name) ? ' (' . $recipient->recipient_contact_person_name . ')' : '');
        $rec_addr      = $is_recip_ind ? ($recipient->address ?? '') . ', ' . ($recipient->zipcode ?? '')
                                       : ($recipient->recipient_contact_address ?? '') . ', ' . ($recipient->recipient_contact_zipcode ?? '');
        $rec_phone     = $is_recip_ind ? ($recipient->phone_number ?? '') : ($recipient->recipient_contact_person_phone_number ?? '');
        $rec_email     = $is_recip_ind ? ($recipient->email ?? '') : ($recipient->recipient_contact_person_email ?? '');
        $rec_country   = !empty($shipment_details['recipient_country']) ? $shipment_details['recipient_country']->short_name : '';

        /* ── Pricing ───────────────────────────────────────────────────── */
        $subtotal = 0;
        foreach ($invoice_items as $item) {
            $subtotal += (float)$item->qty * (float)$item->rate;
        }
        $vat_on   = !empty($s->vat_applicable);
        $vat_rate = $vat_on ? (float)$s->vat_rate : 0;
        $vat_amt  = $vat_on ? round($subtotal * ($vat_rate / 100), 2) : 0;
        $total    = $subtotal + $vat_amt;
        ?>

        <!-- Header -->
        <div class="doc-header">
            <div>
                <?php if ($company_logo_url): ?>
                    <img src="<?php echo $company_logo_url; ?>" height="55" alt="Logo" style="margin-bottom:6px;"><br>
                <?php endif; ?>
                <span style="font-size:15px; font-weight:700;"><?php echo htmlspecialchars($logistic_company); ?></span>
            </div>
            <div style="text-align:center;">
                <div class="doc-title">QUOTATION</div>
                <?php if ($vat_on): ?>
                    <span class="vat-badge">VAT APPLICABLE (<?php echo $vat_rate; ?>%)</span>
                <?php else: ?>
                    <span class="vat-badge" style="background:#fff3e0; color:#e65100; border-color:#ffcc80;">VAT NOT CHARGED</span>
                <?php endif; ?>
            </div>
            <div class="doc-meta">
                <strong>Quote #:</strong> Q-<?php echo htmlspecialchars($s->waybill_number); ?><br>
                <strong>Date:</strong> <?php echo $current_date; ?><br>
                <strong>Valid Until:</strong> <?php echo $valid_until; ?><br>
                <strong>AWB/Waybill:</strong> <?php echo htmlspecialchars($s->waybill_number); ?>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties-row">
            <div class="party-box">
                <h5><i class="fa fa-building-o"></i> Quoted For (Bill To)</h5>
                <p><strong><?php echo htmlspecialchars($rec_name); ?></strong></p>
                <?php if ($rec_addr): ?><p><?php echo htmlspecialchars($rec_addr); ?><?php if ($rec_country): ?>, <?php echo htmlspecialchars($rec_country); ?><?php endif; ?></p><?php endif; ?>
                <?php if ($rec_phone): ?><p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($rec_phone); ?></p><?php endif; ?>
                <?php if ($rec_email): ?><p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($rec_email); ?></p><?php endif; ?>
            </div>
            <div class="party-box">
                <h5><i class="fa fa-truck"></i> Service Provider</h5>
                <p><strong><?php echo htmlspecialchars($logistic_company); ?></strong></p>
                <?php if ($_ci['phone']):   ?><p><?php echo htmlspecialchars($_ci['phone']);   ?></p><?php endif; ?>
                <?php if ($_ci['email']):   ?><p><?php echo htmlspecialchars($_ci['email']);   ?></p><?php endif; ?>
                <?php if ($_ci['address']): ?><p><?php echo nl2br(htmlspecialchars($_ci['address'])); ?></p><?php endif; ?>
                <?php if ($_ci['pin']):     ?><p>PIN: <?php echo htmlspecialchars($_ci['pin']); ?></p><?php endif; ?>
            </div>
        </div>

        <!-- Shipment Info -->
        <table class="items-table" style="margin-bottom:6px;">
            <tr>
                <th colspan="4" style="background:#e8f5e9; color:#1b5e20; font-size:12px; letter-spacing:.3px;">Shipment Details</th>
            </tr>
            <tr>
                <td><strong>Shipping Mode</strong></td>
                <td><?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?></td>
                <td><strong>Category</strong></td>
                <td><?php echo htmlspecialchars(strtoupper($s->shipping_category)); ?></td>
            </tr>
            <tr>
                <td><strong>From</strong></td>
                <td><?php echo htmlspecialchars($snd_name . ($snd_country ? ', ' . $snd_country : '')); ?></td>
                <td><strong>To</strong></td>
                <td><?php echo htmlspecialchars($rec_name . ($rec_country ? ', ' . $rec_country : '')); ?></td>
            </tr>
        </table>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description of Service</th>
                    <th style="text-align:right;">Qty / Wt</th>
                    <th style="text-align:right;">Rate</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($invoice_items)): ?>
                <?php $i = 1; foreach ($invoice_items as $item): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td>
                        <?php echo htmlspecialchars($item->description); ?>
                        <?php if (!empty($item->long_description)): ?>
                            <div style="font-size:11px; color:#777; margin-top:3px;"><?php echo nl2br(htmlspecialchars(strip_tags($item->long_description))); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;"><?php echo number_format((float)$item->qty, 2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$item->rate, 2); ?></td>
                    <td style="text-align:right;"><strong><?php echo number_format((float)$item->qty * (float)$item->rate, 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:#999; font-style:italic;">Shipping service — <?php echo htmlspecialchars($s->shipping_mode); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right;"><strong><?php echo number_format($subtotal, 2); ?></strong></td>
            </tr>
            <?php if ($vat_on): ?>
            <tr style="color:#1b5e20;">
                <td>VAT (<?php echo $vat_rate; ?>%)</td>
                <td style="text-align:right;"><strong><?php echo number_format($vat_amt, 2); ?></strong></td>
            </tr>
            <?php else: ?>
            <tr style="color:#999; font-style:italic;">
                <td>VAT</td>
                <td style="text-align:right;">Not charged</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align:right;"><?php echo number_format($total, 2); ?></td>
            </tr>
        </table>

        <!-- Note -->
        <div class="doc-note">
            <strong>Note:</strong> This quotation is valid until <strong><?php echo $valid_until; ?></strong>.
            Prices are subject to change based on actual shipment weight and dimensions.
            <?php if ($vat_on): ?>
                VAT at <strong><?php echo $vat_rate; ?>%</strong> has been included in the total amount.
            <?php else: ?>
                This quotation does not include VAT.
            <?php endif; ?>
        </div>

        <!-- Signature block -->
        <div class="doc-footer">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div>Prepared By</div>
                <div style="margin-top:3px; font-size:11px; color:#999;">Authorized Signature &amp; Stamp</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div>Customer Acceptance</div>
                <div style="margin-top:3px; font-size:11px; color:#999;">Name, Signature &amp; Date</div>
            </div>
        </div>
        <div style="text-align:center; margin-top:16px; font-size:11px; color:#bbb;">
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($logistic_company); ?> &mdash; All rights reserved.
        </div>
    </div><!-- /.doc-card -->

</div><!-- /.doc-page -->
</div>
</div>
</div>
<?php init_tail(); ?>
</div>
