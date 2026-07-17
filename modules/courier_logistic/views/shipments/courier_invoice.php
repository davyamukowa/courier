<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php
$inv_color      = get_option('courier_invoice_color') ?: '#2e7d32';
$inv_color_2    = get_option('courier_invoice_color_2') ?: $inv_color;
$inv_color_dark = $inv_color; // used for gradient; compute a darker shade via inline style
// Generate a slightly darker shade for gradient (simple: darken by ~15%)
list($r,$g,$b) = sscanf($inv_color, '#%02x%02x%02x');
$dark = sprintf('#%02x%02x%02x', max(0,$r-40), max(0,$g-40), max(0,$b-40));

list($r2,$g2,$b2) = sscanf($inv_color_2, '#%02x%02x%02x');
$dark2 = sprintf('#%02x%02x%02x', max(0,$r2-40), max(0,$g2-40), max(0,$b2-40));

$light_bg   = $inv_color . '18'; // ~10% opacity via hex won't work in CSS, use rgba
$rgba_light = 'rgba(' . $r . ',' . $g . ',' . $b . ',0.08)';
$rgba_border= 'rgba(' . $r . ',' . $g . ',' . $b . ',0.35)';
?>
<style>
:root {
    --ci: <?php echo htmlspecialchars($inv_color); ?>;
    --ci-dark: <?php echo htmlspecialchars($dark); ?>;
    --ci-2: <?php echo htmlspecialchars($inv_color_2); ?>;
    --ci-2-dark: <?php echo htmlspecialchars($dark2); ?>;
}
.doc-page { max-width:860px; margin:0 auto; }
.doc-card  { background:#fff; border:1px solid <?php echo $rgba_border; ?>; border-radius:8px; padding:30px 36px; box-shadow:0 2px 8px rgba(0,0,0,.08); position:relative; }
/* 🚀 Background watermark logo 🚀 */
.doc-watermark { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; z-index:0; overflow:hidden; border-radius:8px; }
.doc-watermark img { width:75%; height:75%; object-fit:contain; opacity:.10; filter:grayscale(10%); transform: none; }
.doc-card > *:not(.doc-watermark) { position:relative; z-index:1; }
/* ── Issuance stamp (SVG round rubber-stamp) ── */
.stamp-wrap     { text-align:center; display:block; margin-bottom:0; }
.issuance-stamp { display:inline-block; transform:rotate(-5deg); filter:drop-shadow(1px 2px 3px rgba(26,58,143,.22)); }
.doc-header{ display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid var(--ci); padding-bottom:16px; margin-bottom:20px; }
.doc-title  { font-size:30px; font-weight:800; color:var(--ci-dark); letter-spacing:1px; }
.doc-meta   { text-align:right; font-size:13px; color:#555; line-height:1.7; }
.doc-meta strong { color:#222; }
.parties-row{ display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:16px 0; }
.party-box  { border:1px solid <?php echo $rgba_border; ?>; border-radius:6px; padding:12px 16px; background:#ffffff; }
.party-box h5{ margin:0 0 8px; font-size:12px; font-weight:800; text-transform:uppercase; color:var(--ci); letter-spacing:.5px; }
.party-box p { margin:2px 0; font-size:13px; color:#333; }
.items-table{ width:100%; border-collapse:collapse; margin:18px 0; font-size:13px; }
.items-table th{ background:linear-gradient(135deg,var(--ci-2),var(--ci-2-dark)); color:#fff; padding:9px 12px; text-align:left; }
.items-table td{ padding:8px 12px; border-bottom:1px solid #eee; }
.items-table tr:last-child td { border-bottom:none; }
.totals-table{ width:300px; margin-left:auto; border-collapse:collapse; font-size:13px; margin-top:4px; }
.totals-table td{ padding:6px 10px; }
.totals-table .total-row{ font-weight:800; font-size:16px; background:linear-gradient(135deg,var(--ci-2),var(--ci-2-dark)); color:#fff; }
.amount-due { background:<?php echo $rgba_light; ?>; border:2px solid var(--ci-2); border-radius:8px; padding:14px 20px; text-align:right; margin-top:12px; }
.vat-badge  { display:inline-block; background:<?php echo $rgba_light; ?>; color:var(--ci); border:1px solid <?php echo $rgba_border; ?>; border-radius:12px; font-size:11px; font-weight:700; padding:2px 10px; margin-left:6px; }
.doc-footer { display:table; width:100%; margin-top:30px; padding-top:16px; border-top:1px solid <?php echo $rgba_border; ?>; font-size:12px; color:#777; break-inside:avoid; page-break-inside:avoid; }
.sig-block  { display:table-cell; vertical-align:bottom; text-align:center; width:50%; }
.sig-line   { border-top:1px solid #666; width:160px; margin:30px auto 4px; }
.status-badge { display:inline-block; padding:3px 12px; border-radius:12px; font-size:11px; font-weight:700; background:<?php echo $rgba_light; ?>; color:var(--ci); }
/* ── Flexbox row so col-md-4 stretches to invoice height (enables sticky) ── */
.invoice-row{display:flex!important;align-items:stretch;flex-wrap:wrap;}
.invoice-row>.col-md-8,.invoice-row>.col-md-4{float:none!important;}
/* ── Sticky right sidebar ── */
.ci-sticky-panel{position:sticky;top:60px;max-height:calc(100vh - 70px);overflow-y:auto;}
.ci-actions-card{background:#fff;border:1px solid <?php echo $rgba_border; ?>;border-radius:8px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;}
.ci-actions-card h6{font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.5px;margin:0 0 10px;}
.ci-action-btn{display:flex;align-items:center;gap:8px;width:100%;padding:9px 14px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;margin-bottom:8px;text-decoration:none;border:none;transition:all .15s;}
.ci-action-btn:last-child{margin-bottom:0;}
.ci-action-btn.primary{background:linear-gradient(135deg,var(--ci),var(--ci-dark));color:#fff;}
.ci-action-btn.primary:hover{opacity:.9;color:#fff;text-decoration:none;}
.ci-action-btn.outline{background:#fff;color:var(--ci);border:2px solid var(--ci);}
.ci-action-btn.outline:hover{background:var(--ci);color:#fff;}
.ci-action-btn.ghost{background:#f7f7f7;color:#555;border:1px solid #ddd;}
.ci-action-btn.ghost:hover{background:#eee;color:#333;text-decoration:none;}
/* ── Payment history (sidebar version) ── */
.ph-card{background:#fff;border:1px solid <?php echo $rgba_border; ?>;border-radius:8px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.ph-title{font-size:13px;font-weight:800;color:var(--ci-dark);border-bottom:2px solid var(--ci);padding-bottom:6px;margin-bottom:10px;}
.ph-row{padding:8px 0;border-bottom:1px solid #f0f0f0;}
.ph-row:last-child{border-bottom:none;}
.ph-row-meta{font-size:11px;color:#777;margin-bottom:3px;}
.ph-row-amount{font-size:14px;font-weight:700;color:var(--ci-dark);}
.btn-reprint{background:#fff;color:var(--ci);border:1.5px solid var(--ci);padding:3px 10px;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;}
.btn-reprint:hover{background:var(--ci);color:#fff;}
.btn-email-receipt{background:#fff;color:#1565c0;border:1.5px solid #1565c0;padding:3px 10px;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;margin-left:4px;}
.btn-email-receipt:hover{background:#1565c0;color:#fff;}
.ph-badge-paid{display:inline-block;background:#e8f5e9;color:#2e7d32;border-radius:10px;font-size:10px;font-weight:700;padding:1px 8px;}
.ph-badge-part{display:inline-block;background:#fff8e1;color:#f57f17;border-radius:10px;font-size:10px;font-weight:700;padding:1px 8px;}
/* ── Email send modal ── */
.ci-email-modal{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9200;display:none;align-items:center;justify-content:center;}
.ci-email-box{background:#fff;border-radius:10px;width:420px;max-width:96vw;padding:0;box-shadow:0 8px 40px rgba(0,0,0,.25);}
.ci-email-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:2px solid var(--ci);background:var(--ci);color:#fff;border-radius:10px 10px 0 0;}
.ci-email-head h4{margin:0;font-size:15px;font-weight:700;}
.ci-email-body{padding:20px 18px;}
.ci-email-foot{display:flex;gap:10px;justify-content:flex-end;padding:12px 18px;border-top:1px solid #eee;}
/* ── Payment status badges (invoice document) ── */
.inv-paid-badge{display:inline-flex;align-items:center;gap:5px;background:#e8f5e9;color:#2e7d32;border:1.5px solid #a5d6a7;border-radius:12px;font-size:12px;font-weight:800;padding:3px 12px;letter-spacing:.4px;}
.inv-partial-badge{display:inline-flex;align-items:center;gap:5px;background:#fff8e1;color:#e65100;border:1.5px solid #ffcc02;border-radius:12px;font-size:12px;font-weight:800;padding:3px 12px;letter-spacing:.4px;}
.inv-paid-watermark{text-align:center;margin-top:10px;}
.inv-paid-stamp{display:inline-block;color:#2e7d32;border:3px solid #2e7d32;border-radius:8px;font-size:22px;font-weight:900;letter-spacing:4px;padding:6px 22px;background:#f1f8f1;transform:rotate(-3deg);}
.inv-partial-stamp{display:inline-block;color:#e65100;border:3px solid #e65100;border-radius:8px;font-size:14px;font-weight:900;letter-spacing:2px;padding:5px 16px;background:#fff8e1;transform:rotate(-2deg);}
.ci-action-btn.disabled-paid{background:#f0faf0;color:#2e7d32;border:2px solid #a5d6a7;cursor:not-allowed;opacity:.85;}
.ci-balance-info{font-size:12px;color:#555;background:#f5f5f5;border-radius:6px;padding:8px 10px;margin-bottom:8px;}
@media print {
    .ci-sticky-panel,.ci-actions-card,.ph-card,.modal,.ci-email-modal { display:none!important; }
    .doc-card { box-shadow:none; border:none; }
    body { background:#fff; }
    .col-md-4{display:none!important;}
    .col-md-8{width:100%!important;}
    .invoice-row>.col-md-8{width:100%!important;flex:0 0 100%!important;max-width:100%!important;}
    .doc-footer { page-break-inside:avoid; break-inside:avoid; margin-top:14px; }
    .issuance-stamp { width:75px!important; height:75px!important; }
    .stamp-wrap { margin-bottom:0; }
    .sig-line { margin-top:10px!important; }
}
</style>

<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>
<div class="content">
<div class="row invoice-row">
<div class="col-md-8">
<div class="doc-page" style="max-width:100%;padding-right:6px;">

    <div class="doc-card" id="courier-invoice-doc">

        <?php
        $company_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
        $company_logo_url  = !empty($company_logo_file) ? base_url('uploads/company/' . $company_logo_file) : '';
        // Pre-encode the logo as a base64 data URI so it survives SVG inside html2canvas and print
        $company_logo_data_uri = '';
        if (!empty($company_logo_file)) {
            $_logo_path = FCPATH . 'uploads/company/' . $company_logo_file;
            if (file_exists($_logo_path)) {
                $_ext  = strtolower(pathinfo($_logo_path, PATHINFO_EXTENSION));
                $_mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml'][$_ext] ?? 'image/png';
                $company_logo_data_uri = 'data:' . $_mime . ';base64,' . base64_encode(file_get_contents($_logo_path));
            }
        }
        $_ci               = courier_get_invoice_info();
        $logistic_company  = $_ci['name'] ?: 'Our Company';
        $s                 = $shipment_details['shipment'];

        $is_sender_ind = $shipment_details['sender_type'] === 'individual';
        $sender        = $shipment_details['sender'];
        $snd_name      = $is_sender_ind ? trim($sender->first_name . ' ' . $sender->last_name)
                                        : $sender->company_name . ' (' . $sender->contact_person_name . ')';

        // Strip calling-code-only zipcodes (e.g. "+254") from address display
        $snd_zip_raw   = $is_sender_ind ? ($sender->zipcode ?? '') : ($sender->contact_zipcode ?? '');
        $snd_zip       = preg_match('/^\+\d+$/', trim($snd_zip_raw)) ? '' : trim($snd_zip_raw);
        $snd_addr_base = $is_sender_ind ? trim($sender->address ?? '') : trim($sender->contact_address ?? '');
        $snd_addr      = $snd_addr_base . ($snd_zip ? ', ' . $snd_zip : '');

        $snd_phone     = $is_sender_ind ? ($sender->phone_number ?? '') : ($sender->contact_person_phone_number ?? '');
        $snd_email     = $is_sender_ind ? ($sender->email ?? '') : ($sender->contact_person_email ?? '');
        $snd_kra_pin   = $sender->kra_pin ?? '';
        $snd_country   = !empty($shipment_details['sender_country']) ? $shipment_details['sender_country']->short_name : '';

        $is_recip_ind  = $shipment_details['recipient_type'] === 'individual';
        $recipient     = $shipment_details['recipient'];
        $rec_name      = $is_recip_ind ? trim($recipient->first_name . ' ' . $recipient->last_name)
                                       : ($recipient->recipient_company_name ?? '') . (!empty($recipient->recipient_contact_person_name) ? ' (' . $recipient->recipient_contact_person_name . ')' : '');

        $rec_zip_raw   = $is_recip_ind ? ($recipient->zipcode ?? '') : ($recipient->recipient_contact_zipcode ?? '');
        $rec_zip       = preg_match('/^\+\d+$/', trim($rec_zip_raw)) ? '' : trim($rec_zip_raw);
        $rec_addr_base = $is_recip_ind ? ($recipient->address ?? '') : ($recipient->recipient_contact_address ?? '');
        $rec_addr      = trim($rec_addr_base) . ($rec_zip ? ', ' . $rec_zip : '');

        $rec_phone     = $is_recip_ind ? ($recipient->phone_number ?? '') : ($recipient->recipient_contact_person_phone_number ?? '');
        $rec_email     = $is_recip_ind ? ($recipient->email ?? '') : ($recipient->recipient_contact_person_email ?? '');
        $rec_country   = !empty($shipment_details['recipient_country']) ? $shipment_details['recipient_country']->short_name : '';

        /* Totals */
        // Filter out zero-rate PACKAGING artifacts stored by old code
        $invoice_items = array_values(array_filter($invoice_items, function($item) {
            return !((float)$item->rate == 0 && strtoupper(trim($item->description)) === 'PACKAGING');
        }));
        $subtotal = 0;
        foreach ($invoice_items as $item) {
            $subtotal += (float)$item->qty * (float)$item->rate;
        }
        $vat_on   = !empty($s->vat_applicable);
        $vat_rate = $vat_on ? (float)$s->vat_rate : 0;
        $vat_amt  = $vat_on ? round($subtotal * ($vat_rate / 100), 2) : 0;
        $total    = $subtotal + $vat_amt;
        $inv_number = 'INV-' . strtoupper($s->waybill_number);

        /* Payment status */
        $total_paid = 0;
        foreach ($invoice_payments as $pmt) {
            $total_paid += (float)$pmt['amount'];
        }
        $balance_remaining = round(max(0, $total - $total_paid), 2);
        $is_fully_paid   = $total_paid > 0 && $balance_remaining <= 0;
        $is_partial_paid = $total_paid > 0 && $balance_remaining > 0;

        $currency_symbol = 'USD';
        $ci = &get_instance();
        $invoice_id = isset($shipment_details['shipment']->invoice_id) ? $shipment_details['shipment']->invoice_id : 0;
        if ($invoice_id) {
            $ci->db->select('currency');
            $ci->db->where('id', $invoice_id);
            $inv = $ci->db->get(db_prefix() . 'invoices')->row();
            if ($inv && $inv->currency) {
                $currency_obj = get_currency($inv->currency);
                if ($currency_obj) {
                    $currency_symbol = $currency_obj->name;
                }
            }
        } else {
            if (isset($shipment_details['shipment']->currency_name) && !empty($shipment_details['shipment']->currency_name)) {
                $currency_symbol = $shipment_details['shipment']->currency_name;
            } elseif (function_exists('get_base_currency')) {
                $base_c = get_base_currency();
                if ($base_c) {
                    $currency_symbol = $base_c->name;
                }
            }
        }
        ?>

        <?php if ($company_logo_data_uri || $company_logo_url): ?>
        <!-- Faded watermark logo — sits behind all content -->
        <div class="doc-watermark">
            <img src="<?php echo htmlspecialchars($company_logo_data_uri ?: $company_logo_url); ?>" alt="">
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="doc-header">
            <div>
                <?php if ($company_logo_data_uri || $company_logo_url): ?>
                    <img src="<?php echo htmlspecialchars($company_logo_data_uri ?: $company_logo_url); ?>" height="55" alt="Logo" style="margin-bottom:6px;"><br>
                <?php endif; ?>
                <span style="font-size:15px; font-weight:700;"><?php echo htmlspecialchars($logistic_company); ?></span>
            </div>
            <div style="text-align:center;">
                <div class="doc-title">INVOICE</div>
                <div style="margin-top:4px;">
                    <?php if ($vat_on): ?>
                        <span class="vat-badge">VAT INCLUSIVE (<?php echo $vat_rate; ?>%)</span>
                    <?php else: ?>
                        <span class="vat-badge" style="background:#fff3e0; color:#e65100; border-color:#ffcc80;">VAT EXEMPT</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="doc-meta">
                <strong>Invoice #:</strong> <?php echo htmlspecialchars($inv_number); ?><br>
                <strong>Invoice Date:</strong> <?php echo $current_date; ?><br>
                <strong>Due Date:</strong> <?php echo $due_date; ?><br>
                <strong>AWB / Waybill:</strong> <?php echo htmlspecialchars($s->waybill_number); ?><br>
                <span class="status-badge"><?php echo htmlspecialchars($s->status_description ?? $s->status_name); ?></span>
                &nbsp;
                <?php if ($is_fully_paid): ?>
                    <span class="inv-paid-badge"><i class="fa fa-check-circle"></i> PAID</span>
                <?php elseif ($is_partial_paid): ?>
                    <span class="inv-partial-badge"><i class="fa fa-clock-o"></i> PARTIALLY PAID</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties-row">
            <div class="party-box">
                <h5><i class="fa fa-user"></i> Bill To</h5>
                <p><strong><?php echo htmlspecialchars($snd_name); ?></strong></p>
                <?php if ($snd_addr): ?><p><?php echo htmlspecialchars($snd_addr); ?><?php if ($snd_country): ?>, <?php echo htmlspecialchars($snd_country); ?><?php endif; ?></p><?php endif; ?>
                <?php if ($snd_phone): ?><p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($snd_phone); ?></p><?php endif; ?>
                <?php if ($snd_email): ?><p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($snd_email); ?></p><?php endif; ?>
                <?php if ($snd_kra_pin): ?><p><strong>KRA PIN:</strong> <?php echo htmlspecialchars($snd_kra_pin); ?></p><?php endif; ?>
            </div>
            <div class="party-box">
                <h5><i class="fa fa-building"></i> From</h5>
                <address style="margin:0; font-size:13px; color:#333; font-style:normal; line-height:1.7;">
                    <?php if ($_ci['name']):    ?><strong><?php echo htmlspecialchars($_ci['name']); ?></strong><br><?php endif; ?>
                    <?php if ($_ci['address']): ?><?php echo nl2br(htmlspecialchars($_ci['address'])); ?><br><?php endif; ?>
                    <?php if ($_ci['phone']):   ?><i class="fa fa-phone"></i> <?php echo htmlspecialchars($_ci['phone']); ?><br><?php endif; ?>
                    <?php if ($_ci['email']):   ?><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($_ci['email']); ?><br><?php endif; ?>
                    <?php if ($_ci['website']): ?><i class="fa fa-globe"></i> <?php echo htmlspecialchars($_ci['website']); ?><br><?php endif; ?>
                    <?php if ($_ci['pin']):     ?>PIN: <?php echo htmlspecialchars($_ci['pin']); ?><br><?php endif; ?>
                </address>
            </div>
        </div>

        <!-- Shipment ref info row -->
        <table class="items-table" style="margin-bottom:4px;">
            <tr>
                <th colspan="4" style="background:<?php echo $rgba_light; ?>; color:var(--ci); font-size:12px; letter-spacing:.3px;">Shipment Reference</th>
            </tr>
            <tr>
                <td><strong>Shipping Mode</strong></td>
                <td><?php echo htmlspecialchars(strtoupper($s->shipping_mode)); ?></td>
                <td><strong>Category</strong></td>
                <td><?php echo htmlspecialchars(strtoupper($s->shipping_category)); ?></td>
            </tr>
            <tr>
                <td><strong>Origin</strong></td>
                <td><?php echo htmlspecialchars($snd_addr_base . ($snd_country ? ', ' . $snd_country : '')); ?></td>
                <td><strong>Destination</strong></td>
                <td><?php echo htmlspecialchars(trim($rec_addr_base) . ($rec_country ? ', ' . $rec_country : '')); ?></td>
            </tr>
            <?php if (!empty($shipment_details['shipment']->goods_declared_value) && (float)$shipment_details['shipment']->goods_declared_value > 0): ?>
            <tr>
                <td><strong>Value of Goods</strong></td>
                <td><?php echo number_format((float)$shipment_details['shipment']->goods_declared_value, 2); ?></td>
                <td><strong>Declared Type</strong></td>
                <td>Domestic Goods Value</td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th style="text-align:right;">Qty / Wt</th>
                    <th style="text-align:right;">Unit Rate</th>
                    <th style="text-align:right;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Packages keyed by their index so we can fall back to POD from the DB
            // when the invoice item's long_description was stored empty (old invoices).
            $inv_packages = array_values($shipment_details['packages'] ?? []);
            ?>
            <?php if (!empty($invoice_items)): ?>
                <?php $item_idx = 0; $row_num = 1; foreach ($invoice_items as $item): ?>
                <tr>
                    <td><?php echo $row_num++; ?></td>
                    <td>
                        <?php echo htmlspecialchars($item->description); ?>
                        <?php
                        $ld = trim(strip_tags($item->long_description ?? ''));
                        if ($ld !== ''):
                        ?>
                            <div style="font-size:11px; color:#777; margin-top:2px;"><?php echo nl2br(htmlspecialchars($ld)); ?></div>
                        <?php
                        elseif (isset($inv_packages[$item_idx]) && !empty($inv_packages[$item_idx]->pod)):
                            // Fallback: show POD from packages table for old invoices with empty long_description
                        ?>
                            <div style="font-size:11px; color:#777; margin-top:2px;">
                                POD: <?php echo htmlspecialchars(strtoupper($inv_packages[$item_idx]->pod)); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item->unit)): ?>
                            <span style="font-size:11px; color:#aaa;">(<?php echo htmlspecialchars($item->unit); ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;"><?php echo number_format((float)$item->qty, 2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$item->rate, 2); ?></td>
                    <td style="text-align:right;"><strong><?php echo number_format((float)$item->qty * (float)$item->rate, 2); ?></strong></td>
                </tr>
                <?php $item_idx++; endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:#999; font-style:italic;">Shipping service — <?php echo htmlspecialchars($s->shipping_mode); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right;"><?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php if ($vat_on): ?>
            <tr style="color:var(--ci);">
                <td>VAT @ <?php echo $vat_rate; ?>%</td>
                <td style="text-align:right;"><?php echo number_format($vat_amt, 2); ?></td>
            </tr>
            <?php else: ?>
            <tr style="color:#999; font-style:italic;">
                <td>VAT</td>
                <td style="text-align:right;">Exempt</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>AMOUNT DUE</td>
                <td style="text-align:right;"><?php echo htmlspecialchars($currency_symbol); ?> <?php echo number_format($total, 2); ?></td>
            </tr>
        </table>

        <!-- Amount due highlight -->
        <div class="amount-due">
            <span style="font-size:13px; color:#555;">Total Amount Due by <?php echo $due_date; ?></span><br>
            <span style="font-size:24px; font-weight:800; color:var(--ci-dark);"><?php echo htmlspecialchars($currency_symbol); ?> <?php echo number_format($total, 2); ?></span>
            <?php if ($vat_on): ?>
                <span style="font-size:12px; color:#888; margin-left:8px;">(includes VAT <?php echo $vat_rate; ?>%)</span>
            <?php else: ?>
                <span style="font-size:12px; color:#888; margin-left:8px;">(VAT exempt)</span>
            <?php endif; ?>
            <?php if ($is_fully_paid): ?>
                <div class="inv-paid-watermark"><span class="inv-paid-stamp">&#10003; PAID IN FULL</span></div>
            <?php elseif ($is_partial_paid): ?>
                <div class="inv-paid-watermark">
                    <span class="inv-partial-stamp">PARTIALLY PAID</span>
                    <div style="font-size:12px;color:#e65100;margin-top:6px;">
                        Paid: <strong><?php echo number_format($total_paid, 2); ?></strong> &nbsp;&bull;&nbsp;
                        Balance: <strong><?php echo number_format($balance_remaining, 2); ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Terms -->
        <?php $payment_terms = trim($s->payment_terms ?? ''); ?>
        <?php if ($payment_terms): ?>
        <div style="background:#f5f5f5; border-radius:6px; padding:12px 16px; font-size:12px; color:#555; margin-top:14px;">
            <strong>Payment Terms:</strong> <?php echo nl2br(htmlspecialchars($payment_terms)); ?>
        </div>
        <?php endif; ?>

        <?php
        // Stamp — dedicated stamp settings, falling back to invoice info
        $_ci_stamp   = courier_get_invoice_info();
        $stamp_name  = strtoupper(trim(get_option('courier_stamp_name')  ?: ($_ci_stamp['name']  ?: '')));
        $stamp_pobox = strtoupper(trim(get_option('courier_stamp_pobox') ?: ''));
        $stamp_phone = strtoupper(trim(get_option('courier_stamp_phone') ?: ($_ci_stamp['phone'] ?: '')));
        if ($stamp_phone && stripos($stamp_phone, 'TEL') === false) {
            $stamp_phone = 'TEL: ' . $stamp_phone;
        }
        // Use the invoice date for the stamp, not today's date
        $stamp_date = strtoupper(date('d M Y', strtotime($current_date)));
        ?>
        <!-- Signature / stamp block -->
        <div class="doc-footer">
            <div class="sig-block">
                <!-- Round rubber stamp (SVG — fixed size regardless of text length) -->
                <div class="stamp-wrap" style="position:relative; display:inline-block;">
                    <svg class="issuance-stamp" viewBox="0 0 200 200" width="110" height="110"
                         xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs>
                            <!-- Top arc: baseline at r=84, 4px above inner ring so text doesn't touch it -->
                            <path id="sTopArc"  d="M 16,100 A 84,84 0 0,1 184,100"/>
                            <!-- Bottom arc 1 (PO Box): baseline at r=93 so text sits between the two rings -->
                            <path id="sBotArc1" d="M 7,100 A 93,93 0 0,0 193,100"/>
                            <!-- Bottom arc 2 (Phone): inside the inner ring -->
                            <path id="sBotArc2" d="M 32,100 A 68,68 0 0,0 168,100"/>
                            <!-- Clip rect for company logo thumbnail inside stamp -->
                            <clipPath id="sLogoClip">
                                <rect x="55" y="32" width="90" height="60" rx="4"/>
                            </clipPath>
                        </defs>

                        <!-- Outer ring -->
                        <circle cx="100" cy="100" r="96" fill="rgba(255,255,255,0.94)" stroke="#1a3a8f" stroke-width="3.5"/>
                        <!-- Inner decorative ring -->
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#1a3a8f" stroke-width="2"/>

                        <!-- Company name – top arc (baseline on inner ring, letters toward outer ring) -->
                        <text font-size="13" font-weight="900" fill="#1a3a8f"
                              font-family="Arial,Helvetica,sans-serif" letter-spacing="2">
                            <textPath href="#sTopArc" xlink:href="#sTopArc" startOffset="50%" text-anchor="middle"><?php echo htmlspecialchars($stamp_name); ?></textPath>
                        </text>

                        <!-- Stars between the two rings at the equatorial line (left & right dividers) -->
                        <text x="15"  y="103" font-size="12" fill="#1a3a8f" text-anchor="middle">&#9733;</text>
                        <text x="185" y="103" font-size="12" fill="#1a3a8f" text-anchor="middle">&#9733;</text>


                        <!-- Date – large red centre -->
                        <text x="100" y="124" font-size="14" font-weight="900" fill="#cc0000"
                              text-anchor="middle" letter-spacing="2"
                              font-family="Arial,Helvetica,sans-serif"><?php echo $stamp_date; ?></text>

                        <!-- PO Box – bottom outer arc (same ring radius as company name) -->
                        <?php if ($stamp_pobox): ?>
                        <text font-size="12" font-weight="900" fill="#1a3a8f"
                              font-family="Arial,Helvetica,sans-serif" letter-spacing="1.5">
                            <textPath href="#sBotArc1" xlink:href="#sBotArc1" startOffset="50%" text-anchor="middle"><?php echo htmlspecialchars($stamp_pobox); ?></textPath>
                        </text>
                        <?php endif; ?>

                        <!-- Phone – bottom inner arc -->
                        <?php if ($stamp_phone): ?>
                        <text font-size="10.5" font-weight="700" fill="#1a3a8f"
                              font-family="Arial,Helvetica,sans-serif" letter-spacing="1">
                            <textPath href="#sBotArc2" xlink:href="#sBotArc2" startOffset="50%" text-anchor="middle"><?php echo htmlspecialchars($stamp_phone); ?></textPath>
                        </text>
                        <?php endif; ?>
                    </svg>
                    <?php if ($company_logo_data_uri || $company_logo_url): ?>
                        <div style="position:absolute; left:27%; top:18%; width:46%; height:24%; display:flex; align-items:center; justify-content:center; overflow:hidden; transform:rotate(-5deg); opacity:0.9;">
                            <img src="<?php echo htmlspecialchars($company_logo_data_uri ?: $company_logo_url); ?>"
                                 style="max-width:100%; max-height:100%; width:auto; height:auto; object-fit:contain;" alt="Logo"/>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="sig-line" style="margin-top:2px;"></div>
                <div>Issued By</div>
                <div style="margin-top:3px; font-size:11px; color:#999;">Authorized Signature &amp; Stamp</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div>Received By</div>
                <div style="margin-top:3px; font-size:11px; color:#999;">Name, Signature &amp; Date</div>
            </div>
        </div>
        <div style="text-align:center; margin-top:16px; font-size:11px; color:#bbb;">
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($logistic_company); ?> &mdash; All rights reserved.
        </div>
    </div>

</div><!-- /doc-page -->
</div><!-- /col-md-8 -->

<!-- ── Sticky right panel ─────────────────────────────────── -->
<div class="col-md-4">
<div class="ci-sticky-panel">

<?php
$shipment_id_js  = (int)$shipment_details['shipment']->id;
$invoice_id_js   = (int)($shipment_details['shipment']->invoice_id ?? 0);
$logistic_js     = addslashes($logistic_company);
$inv_number_js   = addslashes($inv_number);
$rec_name_js     = addslashes($rec_name);
$waybill_js      = addslashes($s->waybill_number);
$total_js        = round($total, 2);
$logo_url_js     = addslashes($company_logo_url);
$pay_url_js      = admin_url('courier_logistic/shipments/record_courier_payment/' . $shipment_id_js);
$inv_email_url   = admin_url('courier_logistic/shipments/send_invoice_email/' . $shipment_id_js);
$rcp_email_url   = admin_url('courier_logistic/shipments/send_payment_receipt_email/' . $shipment_id_js);
$default_email_js = addslashes($rec_email ?? '');
// Pre-fill payment amount with balance remaining (not full total) for partial payments
$pay_amount_js   = $is_partial_paid ? round($balance_remaining, 2) : round($total, 2);
?>

<!-- Action Buttons -->
<div class="ci-actions-card">
    <h6><i class="fa fa-cog"></i> Actions</h6>

    <?php if ($is_fully_paid): ?>
        <div style="background:#e8f5e9;border:1.5px solid #a5d6a7;border-radius:7px;padding:8px 12px;margin-bottom:10px;font-size:12px;font-weight:700;color:#2e7d32;text-align:center;">
            <i class="fa fa-check-circle"></i> Invoice Fully Paid
        </div>
    <?php elseif ($is_partial_paid): ?>
        <div class="ci-balance-info">
            <i class="fa fa-info-circle" style="color:#e65100;"></i>
            <strong style="color:#e65100;">Partially Paid</strong><br>
            Paid: <strong><?php echo number_format($total_paid, 2); ?></strong><br>
            Balance Due: <strong style="color:#c62828;"><?php echo number_format($balance_remaining, 2); ?></strong>
        </div>
    <?php endif; ?>

    <button class="ci-action-btn primary" onclick="window.print();">
        <i class="fa fa-print"></i> Print Invoice
    </button>
    <button class="ci-action-btn outline" onclick="downloadInvoicePdf();">
        <i class="fa fa-file-pdf-o"></i> Download Invoice PDF
    </button>
    <button class="ci-action-btn outline" onclick="openInvoiceEmailModal();">
        <i class="fa fa-envelope"></i> Send Invoice by Email
    </button>

    <?php if ($is_fully_paid): ?>
    <button class="ci-action-btn disabled-paid" disabled title="Invoice is fully paid — no further payment needed.">
        <i class="fa fa-check-circle"></i> Fully Paid
    </button>
    <?php elseif (is_admin() || staff_can('generate_payment', 'courier-invoices')): ?>
    <button class="ci-action-btn outline" id="btnGeneratePayment" onclick="document.getElementById('paymentModal').style.display='flex';">
        <i class="fa fa-credit-card"></i> Generate Payment
    </button>
    <?php endif; ?>

    <a class="ci-action-btn ghost" href="<?php echo admin_url('courier_logistic/shipments/waybill/' . $shipment_details['shipment']->id); ?>">
        <i class="fa fa-arrow-left"></i> Back to Waybill
    </a>
</div>

<!-- Payment History (sticky alongside invoice) -->
<?php if (!empty($invoice_payments)):
    $ph_running = 0;
?>
<div class="ph-card" id="paymentHistoryCard">
    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid var(--ci);padding-bottom:6px;margin-bottom:10px;">
        <div style="font-size:13px;font-weight:800;color:var(--ci-dark);"><i class="fa fa-history"></i> Payment History &mdash; <?php echo htmlspecialchars($s->waybill_number); ?></div>
        <button onclick="downloadHistoryPdf()" title="Download History PDF" style="background:#fff;color:var(--ci);border:1.5px solid var(--ci);padding:3px 9px;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;flex-shrink:0;"><i class="fa fa-file-pdf-o"></i> PDF</button>
    </div>

    <?php foreach ($invoice_payments as $pmt):
        $ph_running += (float)$pmt['amount'];
        $ph_bal      = max(0, $total - $ph_running);
        $ph_mode     = !empty($pmt['name']) ? $pmt['name'] : 'Cash';
        $ph_date     = date('d M Y', strtotime($pmt['date']));
        $ph_ref      = !empty($pmt['note']) ? $pmt['note'] : ($pmt['transactionid'] ?? '');
        $ph_pid      = (int)$pmt['paymentid'];
        $ph_paid_str = number_format((float)$pmt['amount'], 2);
        $ph_bal_str  = number_format($ph_bal, 2);
    ?>
    <div class="ph-row">
        <div class="ph-row-meta">
            <span style="font-weight:700;color:var(--ci);">PMT-<?php echo str_pad($ph_pid, 6, '0', STR_PAD_LEFT); ?></span>
            &bull; <?php echo $ph_date; ?> &bull; <?php echo htmlspecialchars($ph_mode); ?>
            <?php if ($ph_ref): ?><br><span style="color:#999;"><?php echo htmlspecialchars($ph_ref); ?></span><?php endif; ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
            <div>
                <span class="ph-row-amount"><?php echo $ph_paid_str; ?></span>
                &nbsp;
                <?php if ($ph_bal <= 0): ?>
                    <span class="ph-badge-paid">Paid</span>
                <?php else: ?>
                    <span class="ph-badge-part">Bal: <?php echo $ph_bal_str; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <button class="btn-reprint"
                    data-pid="<?php echo $ph_pid; ?>"
                    data-amount="<?php echo $ph_paid_str; ?>"
                    data-balance="<?php echo $ph_bal_str; ?>"
                    data-mode="<?php echo htmlspecialchars($ph_mode, ENT_QUOTES); ?>"
                    data-date="<?php echo htmlspecialchars($ph_date, ENT_QUOTES); ?>"
                    onclick="reprintFromBtn(this)">
                    <i class="fa fa-print"></i>
                </button>
                <button class="btn-email-receipt"
                    data-pid="<?php echo $ph_pid; ?>"
                    data-amount="<?php echo $ph_paid_str; ?>"
                    data-balance="<?php echo $ph_bal_str; ?>"
                    data-mode="<?php echo htmlspecialchars($ph_mode, ENT_QUOTES); ?>"
                    data-date="<?php echo htmlspecialchars($ph_date, ENT_QUOTES); ?>"
                    onclick="openReceiptEmailModal(this)">
                    <i class="fa fa-envelope"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:10px;padding-top:8px;border-top:1px solid #eee;font-size:12px;color:#555;text-align:right;">
        <strong>Total Paid:</strong> <?php echo number_format($ph_running, 2); ?><br>
        <strong>Balance:</strong> <?php echo number_format(max(0, $total - $ph_running), 2); ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /ci-sticky-panel -->
</div><!-- /col-md-4 -->

</div><!-- /row -->
</div><!-- /content -->
</div><!-- /cgs-page -->
</div><!-- /wrapper -->

<?php
/* JS vars are set above — nothing else needed here */
?>

<!-- ═══════════════════════════════════════════════════════════
     INVOICE EMAIL MODAL
═══════════════════════════════════════════════════════════ -->
<div id="invoiceEmailModal" class="ci-email-modal">
<div class="ci-email-box">
    <div class="ci-email-head">
        <h4><i class="fa fa-envelope"></i> &nbsp;Send Invoice by Email</h4>
        <button onclick="closeInvoiceEmailModal()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">&times;</button>
    </div>
    <div class="ci-email-body">
        <p style="font-size:13px;color:#555;margin:0 0 14px;">
            Send invoice <strong><?php echo htmlspecialchars($inv_number); ?></strong> to the recipient's email address.
        </p>
        <div class="ci-form-row">
            <label>Recipient Email</label>
            <input type="email" id="invEmailTo" placeholder="recipient@example.com" value="<?php echo htmlspecialchars($rec_email ?? ''); ?>">
        </div>
        <div id="invEmailError" style="display:none;color:#c62828;background:#ffebee;border-radius:6px;padding:8px 12px;font-size:13px;margin-top:4px;"></div>
        <div id="invEmailSuccess" style="display:none;color:#2e7d32;background:#e8f5e9;border-radius:6px;padding:8px 12px;font-size:13px;margin-top:4px;"></div>
    </div>
    <div class="ci-email-foot">
        <button class="btn-ci-cancel" onclick="closeInvoiceEmailModal()">Cancel</button>
        <button class="btn-ci-submit" id="btnSendInvEmail" onclick="submitInvoiceEmail()">
            <i class="fa fa-paper-plane"></i> Send Invoice
        </button>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     RECEIPT EMAIL MODAL
═══════════════════════════════════════════════════════════ -->
<div id="receiptEmailModal" class="ci-email-modal">
<div class="ci-email-box">
    <div class="ci-email-head">
        <h4><i class="fa fa-envelope"></i> &nbsp;Send Receipt by Email</h4>
        <button onclick="closeReceiptEmailModal()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">&times;</button>
    </div>
    <div class="ci-email-body">
        <p style="font-size:13px;color:#555;margin:0 0 4px;">
            Send payment receipt <strong id="rcpModalId"></strong> to the recipient.
        </p>
        <p style="font-size:12px;color:#888;margin:0 0 14px;">Amount: <strong id="rcpModalAmount"></strong> &bull; Mode: <span id="rcpModalMode"></span></p>
        <div class="ci-form-row">
            <label>Recipient Email</label>
            <input type="email" id="rcpEmailTo" placeholder="recipient@example.com" value="<?php echo htmlspecialchars($rec_email ?? ''); ?>">
        </div>
        <input type="hidden" id="rcpPaymentId" value="">
        <div id="rcpEmailError" style="display:none;color:#c62828;background:#ffebee;border-radius:6px;padding:8px 12px;font-size:13px;margin-top:4px;"></div>
        <div id="rcpEmailSuccess" style="display:none;color:#2e7d32;background:#e8f5e9;border-radius:6px;padding:8px 12px;font-size:13px;margin-top:4px;"></div>
    </div>
    <div class="ci-email-foot">
        <button class="btn-ci-cancel" onclick="closeReceiptEmailModal()">Cancel</button>
        <button class="btn-ci-submit" id="btnSendRcpEmail" onclick="submitReceiptEmail()">
            <i class="fa fa-paper-plane"></i> Send Receipt
        </button>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     PAYMENT MODAL
═══════════════════════════════════════════════════════════ -->
<style>
.ci-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;display:none;align-items:center;justify-content:center;}
.ci-modal-box{background:#fff;border-radius:10px;width:520px;max-width:96vw;max-height:92vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.25);}
.ci-modal-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:2px solid var(--ci);background:var(--ci);color:#fff;border-radius:10px 10px 0 0;}
.ci-modal-head h4{margin:0;font-size:16px;font-weight:700;}
.ci-modal-body{padding:24px 20px;}
.ci-modal-foot{display:flex;gap:10px;justify-content:flex-end;padding:14px 20px;border-top:1px solid #eee;}
.ci-form-row{margin-bottom:16px;}
.ci-form-row label{display:block;font-size:12px;font-weight:700;color:#444;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.ci-form-row input,.ci-form-row select,.ci-form-row textarea{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;box-sizing:border-box;}
.ci-form-row input:focus,.ci-form-row select:focus{outline:none;border-color:var(--ci);box-shadow:0 0 0 2px rgba(0,0,0,.06);}
.mode-tabs{display:flex;gap:0;border:1px solid #ddd;border-radius:6px;overflow:hidden;margin-bottom:16px;}
.mode-tab{flex:1;padding:8px 12px;text-align:center;font-size:13px;font-weight:600;cursor:pointer;background:#f7f7f7;border:none;color:#555;transition:all .2s;}
.mode-tab.active{background:var(--ci);color:#fff;}
.btn-ci-submit{background:var(--ci);color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;}
.btn-ci-submit:hover{opacity:.9;}
.btn-ci-cancel{background:#fff;color:#555;border:1px solid #ccc;padding:10px 18px;border-radius:6px;font-size:13px;cursor:pointer;}
.gateway-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-top:8px;}
.gateway-card{border:2px solid #eee;border-radius:8px;padding:12px 8px;text-align:center;cursor:pointer;font-size:12px;font-weight:600;color:#444;transition:all .2s;}
.gateway-card:hover,.gateway-card.selected{border-color:var(--ci);background:<?php echo $rgba_light; ?>;color:var(--ci);}
.gateway-card i{display:block;font-size:20px;margin-bottom:4px;}
#pesapalPhone{display:none;margin-top:12px;}
</style>

<div id="paymentModal" class="ci-modal-overlay">
<div class="ci-modal-box">
    <div class="ci-modal-head">
        <h4><i class="fa fa-credit-card"></i> &nbsp;Record Payment</h4>
        <button onclick="closePay()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">&times;</button>
    </div>
    <div class="ci-modal-body">

        <!-- Amount -->
        <div class="ci-form-row">
            <label>Amount<?php if ($is_partial_paid): ?> <span style="color:#e65100;font-size:11px;font-weight:600;">(Balance Due: <?php echo number_format($balance_remaining,2); ?>)</span><?php endif; ?></label>
            <input type="number" id="payAmount" value="<?php echo $pay_amount_js; ?>" min="0.01" step="0.01" max="<?php echo $pay_amount_js; ?>">
        </div>

        <!-- Date -->
        <div class="ci-form-row">
            <label>Payment Date</label>
            <input type="date" id="payDate" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <!-- Mode tabs -->
        <div style="margin-bottom:8px;font-size:12px;font-weight:700;color:#444;text-transform:uppercase;letter-spacing:.4px;">Payment Type</div>
        <div class="mode-tabs">
            <button class="mode-tab active" onclick="switchTab('offline',this)">Offline / Cash</button>
            <button class="mode-tab" onclick="switchTab('online',this)">Online Gateway</button>
        </div>

        <!-- Offline modes -->
        <div id="tab-offline">
            <div class="ci-form-row">
                <label>Payment Mode</label>
                <select id="offlineMode">
                    <?php if (!empty($offline_modes)): ?>
                        <?php foreach ($offline_modes as $mode): ?>
                            <option value="<?php echo (int)$mode['id']; ?>"><?php echo htmlspecialchars($mode['name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Cash</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Online gateways -->
        <div id="tab-online" style="display:none;">
            <div class="gateway-grid" id="gatewayGrid">
                <?php if (!empty($online_gateways)): ?>
                    <?php foreach ($online_gateways as $gw): ?>
                    <div class="gateway-card" data-id="<?php echo htmlspecialchars($gw['id']); ?>" onclick="selectGateway(this)">
                        <i class="fa fa-globe"></i>
                        <?php echo htmlspecialchars($gw['name']); ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#999; font-size:13px;">No active payment gateways configured.</p>
                <?php endif; ?>
            </div>
            <!-- Pesapal phone (shown when Pesapal selected) -->
            <div id="pesapalPhone">
                <div class="ci-form-row" style="margin-top:14px;">
                    <label><i class="fa fa-phone"></i> M-Pesa / Mobile Phone Number</label>
                    <input type="tel" id="payPhone" placeholder="e.g. 0712345678">
                    <small style="color:#999; font-size:11px; margin-top:3px; display:block;">Enter the phone number to receive the STK Push prompt.</small>
                </div>
            </div>
        </div>

        <!-- Reference / Note -->
        <div class="ci-form-row" style="margin-top:4px;">
            <label>Reference / Note (optional)</label>
            <input type="text" id="payNote" placeholder="Transaction ref, cheque no, etc.">
        </div>

        <div id="payError" style="display:none;color:#c62828;background:#ffebee;border-radius:6px;padding:10px 12px;font-size:13px;margin-top:8px;"></div>
    </div>
    <div class="ci-modal-foot">
        <button class="btn-ci-cancel" onclick="closePay()">Cancel</button>
        <button class="btn-ci-submit" id="btnSubmitPay" onclick="submitPayment()">
            <i class="fa fa-check"></i> Record Payment
        </button>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     RECEIPT MODAL
═══════════════════════════════════════════════════════════ -->
<style>
.receipt-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9500;display:none;align-items:center;justify-content:center;}
.receipt-wrapper{background:#fff;border-radius:8px;padding:20px;max-width:520px;width:96vw;box-shadow:0 8px 40px rgba(0,0,0,.3);max-height:90vh;overflow-y:auto;}
.receipt-actions{display:flex;gap:10px;justify-content:center;margin-bottom:14px;}
.receipt-body{font-family:'Courier New',monospace;font-size:11.5px;background:#fff;padding:16px;border:1px dashed #ccc;border-radius:4px;line-height:1.5;}
.rc-center{text-align:center;}
.rc-logo-img{max-height:48px;max-width:140px;margin-bottom:4px;}
.rc-co-name{font-size:13px;font-weight:900;letter-spacing:1px;}
.rc-co-sub{font-size:10px;color:#555;}
.rc-title{font-size:16px;font-weight:900;letter-spacing:3px;margin:6px 0 2px;}
.rc-divider{border-top:1px dashed #888;margin:6px 0;}
.rc-divider-solid{border-top:1px solid #555;margin:6px 0;}
.rc-row{display:flex;justify-content:space-between;margin:2px 0;}
.rc-row .lbl{color:#555;white-space:nowrap;margin-right:6px;}
.rc-row .val{text-align:right;word-break:break-word;}
.rc-section-hdr{font-weight:900;font-size:10px;letter-spacing:1px;text-transform:uppercase;margin:6px 0 2px;border-bottom:1px solid #ccc;padding-bottom:2px;}
.rc-tbl{width:100%;border-collapse:collapse;margin:4px 0;}
.rc-tbl th{font-size:10px;font-weight:900;border-bottom:1px solid #555;padding:2px 3px;text-align:left;}
.rc-tbl th:last-child,.rc-tbl td:last-child{text-align:right;}
.rc-tbl th:nth-child(3),.rc-tbl td:nth-child(3){text-align:right;}
.rc-tbl td{font-size:11px;padding:2px 3px;border-bottom:1px dashed #e0e0e0;}
.rc-totals-row{display:flex;justify-content:flex-end;gap:40px;margin:2px 0;}
.rc-totals-row .lbl{color:#555;}
.rc-grand{font-weight:900;font-size:13px;}
.rc-paid-stamp{text-align:center;font-size:18px;font-weight:900;letter-spacing:3px;margin:8px 0;color:#1a7f1a;border:2px solid #1a7f1a;padding:4px;border-radius:4px;}
.rc-terms{font-size:9.5px;color:#555;margin:4px 0;}
.rc-footer-row{font-size:10px;color:#555;margin:2px 0;}
@media print{
    /* Thermal roll paper: auto height = no page breaks */
    @page{size:80mm auto;margin:0;}
    body.printing-receipt *{visibility:hidden!important;}
    body.printing-receipt #thermalReceipt,body.printing-receipt #thermalReceipt *{visibility:visible!important;}
    body.printing-receipt #thermalReceipt{
        position:absolute;top:0;left:0;
        width:80mm;max-width:80mm;
        padding:4mm 3mm;
        background:#fff;
        page-break-inside:avoid;
        break-inside:avoid;
    }
    body.printing-receipt .receipt-overlay{display:block!important;background:none!important;position:static!important;}
    body.printing-receipt .receipt-wrapper{box-shadow:none!important;border:none!important;padding:0!important;max-width:80mm;overflow:visible!important;}
    body.printing-receipt .receipt-actions{display:none!important;}
    body.printing-receipt .action-bar{display:none!important;}
    /* prevent any child from forcing a new page */
    body.printing-receipt #thermalReceipt *{page-break-inside:avoid!important;break-inside:avoid!important;}
}
</style>

<?php
// Precompute values needed in receipt — use courier invoice info (with global fallbacks)
$_ci_r              = courier_get_invoice_info();
$rc_company_name    = $_ci_r['name']    ?: $logistic_company;
$rc_company_addr    = $_ci_r['address'] ?: '';
$rc_company_city    = get_option('invoice_company_city')        ?: (get_option('company_city') ?: '');
$rc_company_state   = get_option('company_state')               ?: '';
$rc_company_country = get_option('invoice_company_country_code') ?: '';
$rc_company_zip     = get_option('invoice_company_postal_code') ?: (get_option('company_zip') ?: '');
$rc_company_phone   = $_ci_r['phone']   ?: '';
$rc_company_vat     = $_ci_r['pin']     ?: (get_option('company_vat') ?: '');
$rc_snd_id          = $is_sender_ind ? ($sender->id_number ?? '') : '';
$rc_rec_id          = $is_recip_ind  ? ($recipient->id_number ?? '') : '';
// Safety defaults in case controller didn't pass these (fresh page loads before upgrade)
if (!isset($issued_by))    $issued_by    = get_staff_full_name(get_staff_user_id());
if (!isset($agent_station)) $agent_station = '';
?>

<div id="receiptModal" class="receipt-overlay">
<div class="receipt-wrapper">
    <div class="receipt-actions">
        <button onclick="printReceipt()" style="background:var(--ci);color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;"><i class="fa fa-print"></i> Print Receipt</button>
        <button onclick="downloadReceiptPdf()" style="background:#1565c0;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
        <button onclick="closeReceipt()" style="background:#fff;color:#555;border:1px solid #ccc;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer;">Close</button>
    </div>
    <div class="receipt-body" id="thermalReceipt">

        <!-- Company Header -->
        <div class="rc-center">
            <?php if ($company_logo_data_uri || $company_logo_url): ?>
                <div><img src="<?php echo htmlspecialchars($company_logo_data_uri ?: $company_logo_url); ?>" class="rc-logo-img" alt="Logo"></div>
            <?php endif; ?>
            <div class="rc-co-name"><?php echo htmlspecialchars($rc_company_name); ?></div>
            <?php if ($rc_company_addr): ?>
                <div class="rc-co-sub"><?php echo htmlspecialchars($rc_company_addr); ?></div>
            <?php endif; ?>
            <?php
            $rc_location = trim(implode(', ', array_filter([$rc_company_city, $rc_company_state, $rc_company_zip, $rc_company_country])));
            if ($rc_location): ?>
                <div class="rc-co-sub"><?php echo htmlspecialchars($rc_location); ?></div>
            <?php endif; ?>
            <?php if ($rc_company_phone): ?>
                <div class="rc-co-sub">Tel: <?php echo htmlspecialchars($rc_company_phone); ?></div>
            <?php endif; ?>
            <?php if ($rc_company_vat): ?>
                <div class="rc-co-sub">VAT No: <?php echo htmlspecialchars($rc_company_vat); ?></div>
            <?php endif; ?>
            <div class="rc-title">RECEIPT</div>
        </div>

        <div class="rc-divider-solid"></div>

        <!-- Receipt Meta -->
        <?php
        $rc_declared_value = (float) ($shipment_details['shipment']->goods_declared_value ?? 0);
        if (!empty($shipment_details['commercial_details'])) {
            $rc_declared_value = 0;
            foreach ($shipment_details['commercial_details'] as $cd) {
                $rc_declared_value += (float)$cd->declared_value;
            }
        } elseif ($rc_declared_value <= 0 && !empty($shipment_details['packages'])) {
            foreach ($shipment_details['packages'] as $pkg) {
                if (!empty($pkg->unit_price)) {
                    $rc_declared_value += (float)$pkg->unit_price * max(1, (int)($pkg->quantity ?? 1));
                }
            }
        }
        ?>
        <div class="rc-row"><span class="lbl">Receipt #:</span><span class="val" id="rcReceiptNum">-</span></div>
        <div class="rc-row"><span class="lbl">Date:</span><span class="val" id="rcDate">-</span></div>
        <div class="rc-row"><span class="lbl">Waybill / Tracking:</span><span class="val"><?php echo htmlspecialchars($s->waybill_number); ?></span></div>
        <div class="rc-row"><span class="lbl">Invoice Ref:</span><span class="val"><?php echo htmlspecialchars($inv_number); ?></span></div>
        <?php if ($rc_declared_value > 0): ?>
        <div class="rc-row"><span class="lbl">Value of Goods:</span><span class="val"><?php echo number_format($rc_declared_value, 2); ?></span></div>
        <?php endif; ?>

        <div class="rc-divider"></div>

        <!-- Sender -->
        <div class="rc-section-hdr">Sender Details</div>
        <div class="rc-row"><span class="lbl">Name:</span><span class="val"><?php echo htmlspecialchars($snd_name); ?></span></div>
        <div class="rc-row"><span class="lbl">Phone:</span><span class="val"><?php echo htmlspecialchars($snd_phone); ?></span></div>
        <?php if ($rc_snd_id): ?><div class="rc-row"><span class="lbl">ID No.:</span><span class="val"><?php echo htmlspecialchars($rc_snd_id); ?></span></div><?php endif; ?>
        <div class="rc-row"><span class="lbl">From:</span><span class="val"><?php echo htmlspecialchars(trim($snd_addr . ($snd_country ? ', '.$snd_country : ''), ', ')); ?></span></div>

        <div class="rc-divider"></div>

        <!-- Receiver -->
        <div class="rc-section-hdr">Receiver Details</div>
        <div class="rc-row"><span class="lbl">Name:</span><span class="val"><?php echo htmlspecialchars($rec_name); ?></span></div>
        <div class="rc-row"><span class="lbl">Phone:</span><span class="val"><?php echo htmlspecialchars($rec_phone); ?></span></div>
        <?php if ($rc_rec_id): ?><div class="rc-row"><span class="lbl">ID No.:</span><span class="val"><?php echo htmlspecialchars($rc_rec_id); ?></span></div><?php endif; ?>
        <div class="rc-row"><span class="lbl">To:</span><span class="val"><?php echo htmlspecialchars(trim($rec_addr . ($rec_country ? ', '.$rec_country : ''), ', ')); ?></span></div>

        <div class="rc-divider"></div>

        <!-- Items / Value — same data source as "Value of Goods" above -->
        <div class="rc-row" style="font-weight:700; border-top:1px solid #d9d9d9; border-bottom:1px solid #d9d9d9; padding:6px 0; margin:6px 0 8px;">
            <span class="lbl" style="font-weight:700; color:#000; text-transform:uppercase; letter-spacing:1px;">Items / Value</span>
            <span class="val" style="font-weight:700; color:#000;"><?php echo number_format((float) $rc_declared_value, 2); ?></span>
        </div>
        <?php if (false): ?>
        <?php
        // Mirror the exact priority used to compute $rc_declared_value above:
        // 1. commercial_details with a declared_value  (international / commercial shipments)
        // 2. shipment goods_declared_value             (domestic declared total)
        // 3. packages with unit_price > 0              (legacy fallback)
        // 4. packages regardless                       (at least show what was shipped)
        $rc_cd   = $shipment_details['commercial_details'] ?? [];
        $rc_pkgs = $shipment_details['packages'] ?? [];
        $shipment_goods_value = (float) ($shipment_details['shipment']->goods_declared_value ?? 0);

        $cd_total  = array_sum(array_map(function($r){ return (float)($r->declared_value ?? 0); }, $rc_cd));
        $pkg_total = array_sum(array_map(function($r){ return (float)($r->unit_price ?? 0) * max(1,(int)($r->quantity ?? 1)); }, $rc_pkgs));

        if (!empty($rc_cd) && $cd_total > 0):
        ?>
        <table class="rc-tbl">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach ($rc_cd as $cd):
                $cd_qty   = max(1, (int)($cd->quantity ?? 1));
                $cd_price = (float)($cd->declared_value ?? 0);
                $cd_desc  = trim($cd->description ?? $cd->item_name ?? 'Goods');
                if ($cd_desc === '') $cd_desc = 'Goods';
            ?>
            <tr>
                <td><?php echo $cd_qty; ?></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($cd_desc, 0, 45, '..')); ?></td>
                <td><?php echo number_format($cd_price, 2); ?></td>
                <td><?php echo number_format($cd_qty * $cd_price, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($shipment_goods_value > 0): ?>
        <table class="rc-tbl">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
            <tr>
                <td>1</td>
                <td><?php echo htmlspecialchars(!empty($rc_pkgs[0]->description) ? mb_strimwidth(trim($rc_pkgs[0]->description), 0, 45, '..') : 'Declared Value of Goods'); ?></td>
                <td><?php echo number_format($shipment_goods_value, 2); ?></td>
                <td><?php echo number_format($shipment_goods_value, 2); ?></td>
            </tr>
            </tbody>
        </table>
        <?php elseif (!empty($rc_pkgs) && $pkg_total > 0): ?>
        <table class="rc-tbl">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach ($rc_pkgs as $pkg):
                $pkg_qty   = max(1, (int)($pkg->quantity ?? 1));
                $pkg_price = (float)($pkg->unit_price ?? 0);
                $pkg_desc  = trim($pkg->description ?? '');
                if ($pkg_desc === '') $pkg_desc = 'Package';
            ?>
            <tr>
                <td><?php echo $pkg_qty; ?></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($pkg_desc, 0, 45, '..')); ?></td>
                <td><?php echo number_format($pkg_price, 2); ?></td>
                <td><?php echo number_format($pkg_qty * $pkg_price, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif (!empty($rc_pkgs)): ?>
        <!-- Packages exist but no declared value — show descriptions at least -->
        <table class="rc-tbl">
            <thead><tr><th>Qty</th><th>Description</th><th>Weight (kg)</th><th>Vol. Wt</th></tr></thead>
            <tbody>
            <?php foreach ($rc_pkgs as $pkg):
                $pkg_qty  = max(1, (int)($pkg->quantity ?? 1));
                $pkg_desc = trim($pkg->description ?? '');
                if ($pkg_desc === '') $pkg_desc = 'Package';
            ?>
            <tr>
                <td><?php echo $pkg_qty; ?></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($pkg_desc, 0, 45, '..')); ?></td>
                <td><?php echo number_format((float)($pkg->weight ?? 0), 2); ?></td>
                <td><?php echo number_format((float)($pkg->weight_volume ?? 0), 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="font-size:10px;color:#999;font-style:italic;margin:4px 0 6px;">No package details recorded.</div>
        <?php endif; ?>

        <?php endif; ?>

        <!-- Shipping Charge -->
        <?php if (!empty($invoice_items)): ?>
        <div class="rc-section-hdr" style="margin-top:6px;">Shipping Charge</div>
        <table class="rc-tbl">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Rate</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach ($invoice_items as $li): ?>
            <tr>
                <td><?php echo number_format((float)$li->qty, 0); ?></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($li->description, 0, 45, '..')); ?></td>
                <td><?php echo number_format((float)$li->rate, 2); ?></td>
                <td><?php echo number_format((float)$li->qty * (float)$li->rate, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Totals -->
        <div class="rc-divider"></div>
        <div class="rc-row"><span class="lbl">Amount:</span><span class="val"><?php echo number_format($subtotal, 2); ?></span></div>
        <?php if ($vat_on): ?>
            <div class="rc-row"><span class="lbl">VAT (<?php echo $vat_rate; ?>%):</span><span class="val"><?php echo number_format($vat_amt, 2); ?></span></div>
        <?php endif; ?>
        <div class="rc-row rc-grand"><span class="lbl">Total Amount:</span><span class="val"><?php echo number_format($total, 2); ?></span></div>

        <div class="rc-divider"></div>

        <!-- Payment -->
        <div class="rc-row"><span class="lbl">Amount Paid:</span><span class="val" id="rcAmountPaid">-</span></div>
        <div class="rc-row"><span class="lbl">Mode:</span><span class="val" id="rcMode">-</span></div>
        <div class="rc-row" id="rcBalanceRow"><span class="lbl">Balance Due:</span><span class="val" id="rcBalance">-</span></div>

        <div id="rcPaidStamp" class="rc-paid-stamp" style="display:none;">** PAID IN FULL **</div>

        <div class="rc-divider-solid"></div>

        <!-- Terms -->
        <div class="rc-section-hdr">Terms &amp; Conditions</div>
        <div class="rc-terms">1. Claims for loss or damage must be reported within 24 hours of delivery.</div>
        <div class="rc-terms">2. The company is not liable for delays caused by factors beyond its control (customs, weather, acts of God).</div>
        <div class="rc-terms">3. Prohibited items including firearms, narcotics, and hazardous materials are not accepted.</div>
        <div class="rc-terms">4. This receipt serves as proof of payment only. Goods are subject to inspection.</div>
        <div class="rc-terms">5. All disputes are subject to the jurisdiction of local courts.</div>

        <div class="rc-divider"></div>

        <!-- Footer -->
        <div class="rc-footer-row"><strong>Issued by:</strong> <?php echo htmlspecialchars($issued_by); ?><?php if ($agent_station): ?> &nbsp;&nbsp; <strong>Station:</strong> <?php echo htmlspecialchars($agent_station); ?><?php endif; ?></div>
        <div class="rc-footer-row"><strong>Printed on:</strong> <span id="rcPrintDate"></span></div>

        <div class="rc-divider"></div>
        <div class="rc-center" style="font-size:10px;color:#777;">Thank you for choosing <?php echo htmlspecialchars($rc_company_name); ?>!<br>Keep this receipt for your records.</div>

    </div><!-- /receipt-body -->
</div><!-- /receipt-wrapper -->
</div><!-- /receiptModal -->

<script>
var CI_PAY_URL  = '<?php echo addslashes($pay_url_js); ?>';
var CI_CSRF_KEY = '<?php echo $this->security->get_csrf_token_name(); ?>';
var activeTab   = 'offline';
var selGateway  = null;

// Always read the latest CSRF hash from the cookie (CI regenerates it after each POST)
function getCsrfVal() {
    var name = CI_CSRF_KEY + '=';
    var parts = document.cookie.split(';');
    for (var i = 0; i < parts.length; i++) {
        var c = parts[i].trim();
        if (c.indexOf(name) === 0) return c.substring(name.length);
    }
    return '<?php echo $this->security->get_csrf_hash(); ?>';
}

function closePay() { document.getElementById('paymentModal').style.display='none'; }
function closeReceipt() {
    document.getElementById('receiptModal').style.display='none';
    // Reload so payment history, invoice status badge, and button state all update
    window.location.reload();
}

function switchTab(tab, el) {
    activeTab = tab;
    document.querySelectorAll('.mode-tab').forEach(function(t){ t.classList.remove('active'); });
    el.classList.add('active');
    document.getElementById('tab-offline').style.display  = (tab === 'offline') ? '' : 'none';
    document.getElementById('tab-online').style.display   = (tab === 'online')  ? '' : 'none';
}

function selectGateway(card) {
    document.querySelectorAll('.gateway-card').forEach(function(c){ c.classList.remove('selected'); });
    card.classList.add('selected');
    selGateway = card.dataset.id;
    document.getElementById('pesapalPhone').style.display = (selGateway === 'pesapal') ? '' : 'none';
    var btnLabel = (selGateway === 'pesapal') ? 'Send STK Push / Pay' : 'Pay Online';
    document.getElementById('btnSubmitPay').innerHTML = '<i class="fa fa-arrow-right"></i> ' + btnLabel;
}

function submitPayment() {
    var errEl = document.getElementById('payError');
    errEl.style.display = 'none';

    var amount = parseFloat(document.getElementById('payAmount').value);
    var date   = document.getElementById('payDate').value;
    var note   = document.getElementById('payNote').value;

    if (isNaN(amount) || amount <= 0) { errEl.textContent='Please enter a valid amount.'; errEl.style.display=''; return; }
    if (!date) { errEl.textContent='Please select a payment date.'; errEl.style.display=''; return; }

    var paymode;
    if (activeTab === 'offline') {
        paymode = document.getElementById('offlineMode').value;
    } else {
        if (!selGateway) { errEl.textContent='Please select a payment gateway.'; errEl.style.display=''; return; }
        paymode = selGateway;
    }

    var btn = document.getElementById('btnSubmitPay');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

    var fd = new FormData();
    fd.append('amount', amount);
    fd.append('paymentmode', paymode);
    fd.append('payment_date', date);
    fd.append('note', note);
    fd.append(CI_CSRF_KEY, getCsrfVal());

    fetch(CI_PAY_URL, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Record Payment';
            if (res.redirect) { window.location.href = res.redirect; return; }
            if (!res.success) { errEl.textContent = res.message || 'Payment failed.'; errEl.style.display=''; return; }
            closePay();
            showReceipt(res, amount, date, paymode);
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Record Payment';
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display='';
        });
}

function showReceipt(res, amount, date, paymode) {
    document.getElementById('rcReceiptNum').textContent = 'PMT-' + String(res.payment_id).padStart(6,'0');
    document.getElementById('rcDate').textContent       = res.payment_date || date;
    document.getElementById('rcAmountPaid').textContent = res.amount_paid;
    document.getElementById('rcMode').textContent       = res.payment_mode_name;

    var bal = parseFloat(res.balance);
    if (bal <= 0) {
        document.getElementById('rcBalanceRow').style.display = 'none';
        document.getElementById('rcPaidStamp').style.display  = '';
    } else {
        document.getElementById('rcBalance').textContent      = res.balance;
        document.getElementById('rcBalanceRow').style.display = '';
        document.getElementById('rcPaidStamp').style.display  = 'none';
    }

    // Stamp current date/time on the receipt
    var now = new Date();
    var dateStr = now.toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'});
    var timeStr = now.toLocaleTimeString('en-GB', {hour:'2-digit',minute:'2-digit'});
    document.getElementById('rcPrintDate').textContent = dateStr + '  Time: ' + timeStr;

    document.getElementById('receiptModal').style.display = 'flex';
}

function printReceipt() {
    var el = document.getElementById('thermalReceipt');
    // Open an isolated popup so @page applies at root level (Chrome ignores it inside @media print on the main page)
    var win = window.open('', '_blank', 'width=360,height=700,toolbar=0,scrollbars=1,resizable=1');
    win.document.write(
        '<!DOCTYPE html><html><head><meta charset="utf-8">' +
        '<style>' +
        '@page{size:80mm auto;margin:0;}' +
        'html,body{margin:0;padding:0;background:#fff;}' +
        'body{font-family:"Courier New",monospace;font-size:11.5px;width:80mm;padding:4mm 3mm;box-sizing:border-box;line-height:1.5;}' +
        'img{max-width:100%;}' +
        '.rc-center{text-align:center;}' +
        '.rc-logo-img{max-height:48px;max-width:140px;margin-bottom:4px;}' +
        '.rc-co-name{font-size:13px;font-weight:900;letter-spacing:1px;}' +
        '.rc-co-sub{font-size:10px;color:#555;}' +
        '.rc-title{font-size:16px;font-weight:900;letter-spacing:3px;margin:6px 0 2px;}' +
        '.rc-divider{border-top:1px dashed #888;margin:6px 0;}' +
        '.rc-divider-solid{border-top:1px solid #555;margin:6px 0;}' +
        '.rc-row{display:flex;justify-content:space-between;margin:2px 0;}' +
        '.rc-row .lbl{color:#555;white-space:nowrap;margin-right:6px;}' +
        '.rc-row .val{text-align:right;word-break:break-word;}' +
        '.rc-section-hdr{font-weight:900;font-size:10px;letter-spacing:1px;text-transform:uppercase;margin:6px 0 2px;border-bottom:1px solid #ccc;padding-bottom:2px;}' +
        '.rc-tbl{width:100%;border-collapse:collapse;margin:4px 0;}' +
        '.rc-tbl th{font-size:10px;font-weight:900;border-bottom:1px solid #555;padding:2px 3px;text-align:left;}' +
        '.rc-tbl th:last-child,.rc-tbl td:last-child{text-align:right;}' +
        '.rc-tbl th:nth-child(3),.rc-tbl td:nth-child(3){text-align:right;}' +
        '.rc-tbl td{font-size:11px;padding:2px 3px;border-bottom:1px dashed #e0e0e0;}' +
        '.rc-totals-row{display:flex;justify-content:flex-end;gap:40px;margin:2px 0;}' +
        '.rc-totals-row .lbl{color:#555;}' +
        '.rc-grand{font-weight:900;font-size:13px;}' +
        '.rc-paid-stamp{text-align:center;font-size:18px;font-weight:900;letter-spacing:3px;margin:8px 0;color:#1a7f1a;border:2px solid #1a7f1a;padding:4px;border-radius:4px;}' +
        '.rc-terms{font-size:9.5px;color:#555;margin:4px 0;}' +
        '.rc-footer-row{font-size:10px;color:#555;margin:2px 0;}' +
        '</style></head><body>' +
        el.innerHTML +
        '</body></html>'
    );
    win.document.close();
    win.focus();
    // Wait for images to load before printing
    win.onload = function(){ win.print(); };
    setTimeout(function(){ win.print(); }, 800);
}

function reprintFromBtn(btn) {
    var data = {
        payment_id:        btn.dataset.pid,
        amount_paid:       btn.dataset.amount,
        balance:           btn.dataset.balance,
        payment_mode_name: btn.dataset.mode,
        payment_date:      btn.dataset.date,
    };
    showReceipt(data);
}

/* ── Invoice email modal ─────────────────────────────────── */
function openInvoiceEmailModal() {
    document.getElementById('invEmailError').style.display   = 'none';
    document.getElementById('invEmailSuccess').style.display = 'none';
    document.getElementById('btnSendInvEmail').disabled = false;
    document.getElementById('invoiceEmailModal').style.display = 'flex';
}
function closeInvoiceEmailModal() {
    document.getElementById('invoiceEmailModal').style.display = 'none';
}
function submitInvoiceEmail() {
    var email   = document.getElementById('invEmailTo').value.trim();
    var errEl   = document.getElementById('invEmailError');
    var okEl    = document.getElementById('invEmailSuccess');
    var btn     = document.getElementById('btnSendInvEmail');
    errEl.style.display = 'none';
    okEl.style.display  = 'none';
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errEl.textContent = 'Please enter a valid email address.';
        errEl.style.display = 'block';
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
    var fd = new FormData();
    fd.append('email', email);
    fd.append(CI_CSRF_KEY, getCsrfVal());
    fetch('<?php echo $inv_email_url; ?>', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Invoice';
            if (res.success) {
                okEl.textContent = res.message;
                okEl.style.display = 'block';
            } else {
                errEl.textContent = res.message || 'Failed to send email.';
                errEl.style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Invoice';
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display = 'block';
        });
}

/* ── Receipt email modal ─────────────────────────────────── */
function openReceiptEmailModal(btn) {
    document.getElementById('rcpPaymentId').value      = btn.dataset.pid;
    document.getElementById('rcpModalId').textContent  = 'RCP-' + String(btn.dataset.pid).padStart(6,'0');
    document.getElementById('rcpModalAmount').textContent = btn.dataset.amount;
    document.getElementById('rcpModalMode').textContent   = btn.dataset.mode;
    document.getElementById('rcpEmailError').style.display   = 'none';
    document.getElementById('rcpEmailSuccess').style.display = 'none';
    document.getElementById('btnSendRcpEmail').disabled = false;
    document.getElementById('receiptEmailModal').style.display = 'flex';
}
function closeReceiptEmailModal() {
    document.getElementById('receiptEmailModal').style.display = 'none';
}
function submitReceiptEmail() {
    var email     = document.getElementById('rcpEmailTo').value.trim();
    var paymentId = document.getElementById('rcpPaymentId').value;
    var errEl     = document.getElementById('rcpEmailError');
    var okEl      = document.getElementById('rcpEmailSuccess');
    var btn       = document.getElementById('btnSendRcpEmail');
    errEl.style.display = 'none';
    okEl.style.display  = 'none';
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errEl.textContent = 'Please enter a valid email address.';
        errEl.style.display = 'block';
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
    var fd = new FormData();
    fd.append('email', email);
    fd.append('payment_id', paymentId);
    fd.append(CI_CSRF_KEY, getCsrfVal());
    fetch('<?php echo $rcp_email_url; ?>', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Receipt';
            if (res.success) {
                okEl.textContent = res.message;
                okEl.style.display = 'block';
            } else {
                errEl.textContent = res.message || 'Failed to send email.';
                errEl.style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Receipt';
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display = 'block';
        });
}

/* ── PDF download helpers ── */
function downloadInvoicePdf() {
    var el  = document.getElementById('courier-invoice-doc');
    var inv = '<?php echo addslashes($inv_number); ?>';

    // Save scroll position and reset to top to avoid html2canvas capturing blank space
    var currentScrollY = window.scrollY;
    var currentScrollX = window.scrollX;
    window.scrollTo(0, 0);

    // Build a list of fixed/sticky element IDs/selectors from the live DOM
    // so we can hide them in the html2canvas clone (prevents navbar overlay at top)
    var fixedSelectors = [];
    document.querySelectorAll('*').forEach(function (node) {
        if (node === el || el.contains(node)) return;
        try {
            var pos = window.getComputedStyle(node).position;
            if ((pos === 'fixed' || pos === 'sticky') && node.id) {
                fixedSelectors.push('#' + node.id);
            }
        } catch (e) {}
    });

    html2pdf().set({
        margin:      [10, 10, 10, 10],
        filename:    'Invoice-' + inv + '.pdf',
        image:       { type: 'jpeg', quality: 0.96 },
        html2canvas: {
            scale:        2,
            useCORS:      true,
            logging:      false,
            scrollY:      0,
            onclone: function (clonedDoc) {
                // Remove fixed/sticky admin chrome from the clone so they don't
                // overlay the invoice and add blank space at the top of the PDF
                fixedSelectors.forEach(function (sel) {
                    try {
                        var node = clonedDoc.querySelector(sel);
                        if (node) node.parentNode.removeChild(node);
                    } catch (e) {}
                });
            }
        },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] }
    }).from(el).save().then(function() {
        window.scrollTo(currentScrollX, currentScrollY);
    });
}

function downloadReceiptPdf() {
    var el  = document.getElementById('thermalReceipt');
    var pid = document.getElementById('rcReceiptNum').textContent || 'Receipt';

    // Measure actual rendered height so the PDF is exactly one page (no fixed height guess)
    var heightPx = el.scrollHeight;
    // Convert px → mm at 96 dpi, add 8mm margin buffer
    var heightMm = Math.ceil((heightPx / 96) * 25.4) + 8;

    var currentScrollY = window.scrollY;
    var currentScrollX = window.scrollX;
    window.scrollTo(0, 0);

    html2pdf().set({
        margin:      [4, 4, 4, 4],
        filename:    pid + '.pdf',
        image:       { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, scrollY: 0 },
        jsPDF:       { unit: 'mm', format: [80, heightMm], orientation: 'portrait' }
    }).from(el).save().then(function() {
        window.scrollTo(currentScrollX, currentScrollY);
    });
}

function downloadHistoryPdf() {
    var el = document.getElementById('paymentHistoryCard');
    var wb = '<?php echo addslashes($s->waybill_number); ?>';
    
    var currentScrollY = window.scrollY;
    var currentScrollX = window.scrollX;
    window.scrollTo(0, 0);

    html2pdf().set({
        margin:      [8, 8, 8, 8],
        filename:    'PaymentHistory-' + wb + '.pdf',
        image:       { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, logging: false, scrollY: 0 },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(el).save().then(function() {
        window.scrollTo(currentScrollX, currentScrollY);
    });
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<?php init_tail(); ?>

