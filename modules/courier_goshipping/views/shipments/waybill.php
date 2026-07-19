<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php echo '<script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>'; ?>

<!-- Load Custom CSS Files -->
<?php echo '<link rel="stylesheet" href="' . base_url('modules/courier_goshipping/assets/waybill.css') . '">'; ?>
<?php echo '<link rel="stylesheet" href="' . base_url('modules/courier_goshipping/assets/progress.css') . '">'; ?>

<script>
    function printWaybill() {
        // Create a new iframe for printing
        const printFrame = document.createElement('iframe');
        printFrame.style.position = 'absolute';
        printFrame.style.top = '-1000px';
        document.body.appendChild(printFrame);

        const printDocument = printFrame.contentDocument || printFrame.contentWindow.document;
        const printContents = document.getElementById('waybill-section').innerHTML;

        // Inline CSS with padding for waybill container
        const cssStyles = `<style>
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap');

    body {
        font-family: 'Open Sans', Arial, sans-serif;
    }

    .waybill-container {
        position: relative;
        max-width: 800px;
        margin: auto;
        background: white;
        padding: 20px 30px;
        border: 2px solid #333;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        z-index: 2;
    }
    .watermark {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: fill; /* Stretches to fill vertically and horizontally */
        opacity: 0.1;
        pointer-events: none;
        z-index: -1;
    }

    @media print {
        .watermark{
                    display: block; /* Ensure watermark is visible in print */
        }
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 5px;
        border-bottom: 1px solid black;
        position: relative;
        z-index: 2;
    }

    .header img {
        max-width: 150px;
        height: auto;
    }

    .header .waybill-number {
        font-size: 14px;
        font-weight: bold;
        text-align: center;
    }

    .header .date {
        font-size: 16px;
        font-weight: bold;
    }

    .title {
        text-align: center;
        margin-top: 10px;
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .info-table {
        width: 100%;
        margin-top: 10px;
        border-collapse: collapse;
    }

    .company-title {
        font-size: 10px;
    }

    .info-table th,
    .info-table td {
        padding: 2px;
        border: 1px solid #333;
        text-align: left;
        font-size: 10px;
    }

    .info-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .no-border {
        border: none;
    }

    .shipping-section {
        display: flex;
        flex-direction: column;
        margin-top: 20px;
    }

    .shipping-info {
        width: 100%;
    }

    .shipping-level {
        width: 35%;
        margin-left: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .shipping-level h3 {
        margin-bottom: 10px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
    }

    .shipping-level .checkbox-group {
        display: flex;
        flex-direction: column;
    }

    .shipping-level .checkbox-group div {
        margin-bottom: 10px;
    }

    .shipping-level .checkbox {
        margin-bottom: 10px;
    }

    .shipping-level input {
        margin-right: 10px;
    }

    .international-options {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
    }

    .international-options .checkbox {
        margin-bottom: 10px;
    }

    .international-options .checkbox-sub {
        margin-left: 20px;
    }

    .footer {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        position: relative;
        z-index: 2;
    }

    .company-section {
        margin-top: 20px;
    }

    .company-section h3 {
        margin-bottom: 10px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
    }

    .terms {
        margin-top: 10px;
        font-size: 12px;
        line-height: 1.5;
        border-top: 2px solid #333;
        padding-top: 10px;
    }

    .terms h4 {
        font-weight: bold;
        margin: 0; /* Remove margin */
        padding: 0 5px; /* Add left padding only */
    }

    .terms .content {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap; /* Ensure content wraps in case of overflow */
        margin: 0; /* Remove margin */
        padding: 0; /* Remove padding */
    }

    .terms .column {
        width: 48%; /* Two columns side by side */
        margin: 0; /* Remove margin */
        padding: 0 5px; /* Add left and right padding */
    }

    .terms .column p {
        margin: 0; /* Remove margin between paragraphs */
        padding: 0; /* Remove padding */
    }
</style>
`;

        // Write the contents to the iframe's document
        printDocument.open();
        printDocument.write(`
            <html>
            <head>
                <title>Print Waybill</title>
                ${cssStyles}
            </head>
            <body>
                ${printContents}
            </body>
            </html>
        `);
        printDocument.close();

        // Wait for the content to load before printing
        printFrame.onload = function () {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();

            // Remove the iframe after printing
            document.body.removeChild(printFrame);
        };
    }
</script>

<style>
/* ── Go Shipping corporate theme (scoped to the waybill page only) ──
   Blue = primary brand, Red = accent/action. Overrides the shared green
   courier-sidebar / stepper styling from main.css / progress.css without
   touching those files, so other pages that reuse them are unaffected. */
.gs-waybill-page .courier-sidebar {
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(13, 71, 161, 0.08);
}
.gs-waybill-page .courier-sidebar-header {
    background: linear-gradient(135deg, #1565c0, #0d47a1);
}
.gs-waybill-page .courier-nav-item:hover,
.gs-waybill-page .courier-nav-item.active {
    background: #eef4fc;
    color: #0d47a1;
    border-left-color: #c62828;
}
/* Plain flat list — no button blocks. Every row looks the same at rest;
   red is reserved for whichever one the user is actually hovering/pressing
   right now, instead of some actions being permanently-colored buttons and
   others plain text. */
.gs-waybill-page .gs-action-list {
    padding: 4px 0;
    display: flex;
    flex-direction: column;
}
.gs-waybill-page .gs-action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    color: #32475a !important;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none !important;
    border-bottom: 1px solid #eef1f5;
    transition: background .15s, color .15s;
}
.gs-waybill-page .gs-action-btn i { width: 16px; text-align: center; color: #9aa7b5; font-size: 12px; }
.gs-waybill-page .gs-action-btn:hover,
.gs-waybill-page .gs-action-btn:focus,
.gs-waybill-page .gs-action-btn:active {
    background: #fbeeee;
    color: #c62828 !important;
}
.gs-waybill-page .gs-action-btn:hover i,
.gs-waybill-page .gs-action-btn:focus i,
.gs-waybill-page .gs-action-btn:active i {
    color: #c62828;
}
.gs-waybill-page .courier-nav-divider {
    display: none;
}
.gs-waybill-page .panel_s {
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(13, 71, 161, 0.06);
    border: 1px solid #e3ebf5;
}
.gs-waybill-page .stepper-item .step-counter {
    background: #dbe7f6;
    color: #0d47a1;
    font-weight: 700;
}
.gs-waybill-page .stepper-item.completed .step-counter {
    background: #1565c0;
    color: #fff;
}
.gs-waybill-page .stepper-item.active .step-counter {
    background: #c62828;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(198, 40, 40, 0.18);
}
.gs-waybill-page .stepper-item.completed::after {
    border-bottom-color: #1565c0;
}
.gs-waybill-page .waybill-container {
    border-color: #0d47a1;
    border-top: 4px solid #c62828;
}
</style>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>
    <div class="content gs-waybill-page">
        <div class="row">
            <?php
            $wb_type = $this->session->userdata('type') ?: 'domestic';
            $wb_sid  = $shipment_details['shipment']->id;
            ?>

            <!-- ── Left sidebar ────────────────────────────────────── -->
            <div class="col-md-3">
                <div class="courier-sidebar">
                    <div class="courier-sidebar-header">
                        <i class="fa fa-file-text"></i> Shipment Actions
                    </div>
                    <nav class="courier-sidebar-nav gs-action-list">

                        <?php if (!empty($shipment_details['shipment']->invoice_id)): ?>
                        <a href="<?php echo admin_url('invoices/invoice/' . $shipment_details['shipment']->invoice_id); ?>"
                           class="gs-action-btn">
                            <i class="fa fa-money"></i> Invoice
                        </a>
                        <?php elseif (empty($shipment_details['shipment']->invoice_id) && $shipment_details['shipment']->staff_id == 0): ?>
                        <a href="javascript:void(0);" onclick="openConfirmPortalModal(<?php echo $wb_sid; ?>);"
                           class="gs-action-btn">
                            <i class="fa fa-check-circle"></i> Confirm &amp; Create Invoice
                        </a>
                        <?php endif; ?>

                        <?php if (is_admin()): ?>
                        <?php
                        $ci_url = !empty($shipment_details['shipment']->commercial_invoice_url)
                            ? base_url($shipment_details['shipment']->commercial_invoice_url)
                            : admin_url('courier_goshipping/shipments/commercial_invoice/' . $wb_sid);
                        $ci_target = !empty($shipment_details['shipment']->commercial_invoice_url) ? ' target="_blank"' : '';
                        ?>
                        <a href="<?php echo $ci_url; ?>"<?php echo $ci_target; ?> class="gs-action-btn">
                            <i class="fa fa-file-invoice"></i> Commercial Invoice
                        </a>
                        <?php endif; ?>

                        <?php if (is_admin() || staff_can('view_invoices', 'courier-invoices') || staff_can('view_own_invoices', 'courier-invoices')): ?>
                        <div class="courier-nav-divider"></div>

                        <a href="<?php echo admin_url('courier_goshipping/shipments/courier_invoice/' . $wb_sid); ?>" class="gs-action-btn">
                            <i class="fa fa-file-text"></i> Courier Invoice
                        </a>
                        <?php if (is_admin()): ?>
                        <a href="<?php echo admin_url('courier_goshipping/shipments/consignment_note/' . $wb_sid); ?>" class="gs-action-btn">
                            <i class="fa fa-clipboard"></i> Consignment Note
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (empty($salibay_delivery_link) && is_admin()): ?>
                        <div class="courier-nav-divider"></div>

                        <a href="<?php echo admin_url('fleet/trips/create/' . $wb_sid); ?>" class="gs-action-btn">
                            <i class="fa fa-road"></i> Book a Trip
                        </a>
                        <?php endif; ?>

                        <?php if (is_admin() || staff_can('edit_shipments', 'courier-shipments')): ?>
                        <div class="courier-nav-divider"></div>

                        <a href="#" data-toggle="modal" data-target="#update_status" class="gs-action-btn">
                            <i class="fa fa-refresh"></i> Update Status
                        </a>

                        <a href="#" data-toggle="modal" data-target="#assign_agent_modal" class="gs-action-btn">
                            <i class="fa fa-user-plus"></i> Assign Agent / Staff
                        </a>
                        <?php endif; ?>

                        <?php if (is_admin()): ?>
                        <div class="courier-nav-divider"></div>
                        <a href="<?php echo admin_url('courier_goshipping/shipments/create?type=' . $wb_type); ?>"
                           class="gs-action-btn">
                            <i class="fa fa-plus-circle"></i> New Shipment
                        </a>
                        <?php endif; ?>

                    </nav>
                </div>

                <!-- Quick info card -->
                <div class="courier-sidebar" style="margin-top:12px;">
                    <div class="courier-sidebar-header" style="font-size:13px; padding:10px 14px;">
                        <i class="fa fa-info-circle"></i> Quick Info
                    </div>
                    <div style="padding:10px 14px; font-size:12px; line-height:1.8; color:#444;">
                        <strong>Waybill:</strong> <?php echo htmlspecialchars($shipment_details['shipment']->waybill_number); ?><br>
                        <strong>Status:</strong>
                        <span style="color:#28a745; font-weight:600;">
                            <?php echo htmlspecialchars($shipment_details['shipment']->status_description ?? $shipment_details['shipment']->status_name); ?>
                        </span><br>
                        <strong>Mode:</strong> <?php echo htmlspecialchars($shipment_details['shipment']->shipping_mode); ?><br>
                        <strong>Created:</strong> <?php echo date('d M Y', strtotime($shipment_details['shipment']->created_at)); ?><br>
                        <strong>Assigned to:</strong>
                        <?php
                        $assigned_staff = $this->db->select('firstname, lastname')->where('staffid', $shipment_details['shipment']->staff_id)->get(db_prefix() . 'staff')->row();
                        echo htmlspecialchars($assigned_staff ? ($assigned_staff->firstname . ' ' . $assigned_staff->lastname) : 'Unassigned');
                        ?>
                    </div>
                </div>
            </div>
            <!-- ── End left sidebar ────────────────────────────────── -->

            <!-- ── Main content ───────────────────────────────────── -->
            <div class="col-md-9">
            <?php echo form_open($this->uri->uri_string(), ['id' => 'create-pickup-form']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div style="margin-bottom:20px; display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
                            <a class="custom-button"
                               href="javascript:void(0);" onclick="printWaybill();"
                               style="background:linear-gradient(135deg,#1565c0,#0d47a1); color:#fff; border-color:#0d47a1;">
                                <i class="fa fa-print" style="margin-right:5px;"></i> Print Waybill
                            </a>
                            <a class="custom-button"
                               href="javascript:void(0);" onclick="downloadWaybillPdf();"
                               style="background:linear-gradient(135deg,#c62828,#8e1c1c); color:#fff; border-color:#8e1c1c;">
                                <i class="fa fa-file-pdf-o" style="margin-right:5px;"></i> Download PDF
                            </a>
                            <a class="custom-button" href="javascript:void(0);" onclick="openSendWaybillModal();"
                               style="background:#fff; color:#0d47a1; border-color:#1565c0;">
                                <i class="fa fa-envelope" style="margin-right:5px;"></i> Send by Email
                            </a>
                            <a class="custom-button" href="#" data-toggle="modal" data-target="#update_status"
                               style="background:#c62828; color:#fff; border-color:#c62828;">
                                <i class="fa fa-refresh" style="margin-right:5px;"></i> Update Status
                            </a>
                            <?php if (is_admin() || staff_can('edit_shipments', 'courier-shipments')): ?>
                            <a class="custom-button" href="#" data-toggle="modal" data-target="#assign_agent_modal"
                               style="background:#0d47a1; color:#fff; border-color:#0d47a1;">
                                <i class="fa fa-user-plus" style="margin-right:5px;"></i> <?php echo !empty($salibay_delivery_link) ? 'Assign Rider' : 'Assign Agent/Staff'; ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
                        <script>
                        function downloadWaybillPdf() {
                            html2pdf().set({
                                margin:[10,10,10,10],
                                filename:'waybill-<?php echo htmlspecialchars($shipment_details['shipment']->waybill_number ?? ''); ?>.pdf',
                                image:{type:'jpeg',quality:0.98},
                                html2canvas:{scale:2,useCORS:true},
                                jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
                            }).from(document.getElementById('waybill-section')).save();
                        }
                        </script>

                        <div style="margin-top:20px;" class="stepper-wrapper">
                            <?php
                            $displayCounter = 1; // Initialize a counter for display purposes
                            foreach ($statuses as $status):
                                // Check if we should skip the pickup step
                                if ($status->id != 2 || !empty($shipment_details['shipment']->pickup_id)):
                                    // Adjust the display counter based on the pickup condition
                                    $displayId = (!empty($shipment_details['shipment']->pickup_id) || $status->id != 2) ? $displayCounter : $displayCounter - 1;
                                    ?>
                                    <div class="stepper-item <?= ($status->id <= $shipment_details['shipment']->status_id) ? 'completed' : ''; ?> <?= ($status->id == $shipment_details['shipment']->status_id) ? 'active' : ''; ?>">
                                        <div class="step-counter"><?= $displayId; ?></div>
                                        <div class="step-name"><?= $status->description; ?></div>
                                    </div>
                                    <?php
                                    $displayCounter++; // Increment the display counter for the next step
                                endif;
                            endforeach;
                            ?>
                        </div>

                        <?php
                        $show_live_map = !empty($salibay_delivery_link) && (int) $shipment_details['shipment']->status_id >= 5 && (int) $shipment_details['shipment']->status_id < 8;
                        $dest_recipient = $shipment_details['recipient'] ?? null;
                        $dest_address = $dest_recipient
                            ? trim(($dest_recipient->address ?? '') . ', ' . ($shipment_details['recipient_country']->short_name ?? ''))
                            : '';
                        ?>

                        <?php
                        $company_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
                        $company_logo_url  = !empty($company_logo_file) ? base_url('uploads/company/' . $company_logo_file) : '';
                        $_ci              = courier_get_invoice_info($shipment_details['shipment']->branch_id ?? null);
                        $logistic_company = $_ci['name'] ?: '';
                        ?>
                        <div style="margin-top:60px;" id="waybill-section" class="waybill-container">
                            <?php if ($company_logo_url): ?>
                            <img class="watermark" style="opacity:0.08;"
                                 src="<?php echo $company_logo_url; ?>"
                                 alt="Watermark">
                            <?php endif; ?>

                            <div style="display:flex; justify-content:right; width:100%; margin-top:15px; margin-bottom:5px;"
                                 class="barcode">
                                <img src="<?php echo $barcode; ?>" alt="Barcode">
                            </div>

                            <div class="header">
                                <?php if ($company_logo_url): ?>
                                <img height="60" width="60"
                                     src="<?php echo $company_logo_url; ?>"
                                     alt="Company Logo">
                                <?php else: ?>
                                <span style="font-size:18px; font-weight:bold;"><?php echo htmlspecialchars($logistic_company); ?></span>
                                <?php endif; ?>
                                <div class="waybill-number">Waybill
                                    Number: <?php echo $shipment_details['shipment']->tracking_id; ?></div>
                                <div class="date"><?php echo $current_date ?></div>
                            </div>

                            <table class="info-table">
                                <tr>
                                    <?php
                                    $is_sender_individual = $shipment_details['sender_type'] === 'individual';
                                    $is_recipient_individual = $shipment_details['recipient_type'] === 'individual';

                                    // Sender Information
                                    $sender_label = $is_sender_individual ? 'Sender Name' : 'Sender';
                                    $sender_name = $is_sender_individual
                                        ? $shipment_details['sender']->first_name . ' ' . $shipment_details['sender']->last_name
                                        : 'Company: ' . $shipment_details['sender']->company_name . ' (' . $shipment_details['sender']->contact_person_name . ')';

                                    // Recipient Information
                                    $recipient_label = $is_recipient_individual ? 'Receiver Name' : 'Receiver';
                                    $recipient_name = $is_recipient_individual
                                        ? $shipment_details['recipient']->first_name . ' ' . $shipment_details['recipient']->last_name
                                        : 'Company: ' . $shipment_details['recipient']->recipient_company_name . ' (' . $shipment_details['recipient']->recipient_contact_person_name . ')';
                                    ?>

                                    <th><?php echo $sender_label; ?></th>
                                    <td><?php echo $sender_name; ?></td>

                                    <th><?php echo $recipient_label; ?></th>
                                    <td><?php echo $recipient_name; ?></td>
                                </tr>

                                <tr>
                                    <?php
                                    $is_sender_individual = $shipment_details['sender_type'] === 'individual';
                                    $is_recipient_individual = $shipment_details['recipient_type'] === 'individual';

                                    // Sender Information
                                    $sender_address_label = $is_sender_individual ? 'Sender Address' : 'Contact person Address';
                                    $sender_address = $is_sender_individual
                                        ? (!empty($shipment_details['sender_country'])
                                            ? $shipment_details['sender']->address . ', ' . str_replace('_', ' ', $shipment_details['sender']->address_type) . ' ' . $shipment_details['sender']->zipcode . ', ' . $shipment_details['sender_country']->short_name
                                            : $shipment_details['sender']->address . ', ' . str_replace('_', ' ', $shipment_details['sender']->address_type) . ' ' . $shipment_details['sender']->zipcode)
                                        : (!empty($shipment_details['sender_country'])
                                            ? $shipment_details['sender']->contact_address . ', ' . str_replace('_', ' ', $shipment_details['sender']->contact_address_type) . ' ' . $shipment_details['sender']->contact_zipcode . ', ' . $shipment_details['sender_country']->short_name
                                            : $shipment_details['sender']->contact_address . ', ' . str_replace('_', ' ', $shipment_details['sender']->contact_address_type) . ' ' . $shipment_details['sender']->contact_zipcode);

                                    // Recipient Information


                                    $recipient_address_label = $is_recipient_individual ? 'Recipient Address' : 'Contact person Address';
                                    $recipient_address = $is_recipient_individual
                                        ? (!empty($shipment_details['recipient_country'])
                                            ? $shipment_details['recipient']->address . ', ' . str_replace('_', ' ', $shipment_details['recipient']->address_type) . ' ' . $shipment_details['recipient']->zipcode . ', ' . $shipment_details['recipient_country']->short_name
                                            : $shipment_details['recipient']->address . ', ' . str_replace('_', ' ', $shipment_details['recipient']->address_type) . ' ' . $shipment_details['recipient']->zipcode)
                                        : (!empty($shipment_details['recipient_country'])
                                            ? $shipment_details['recipient']->recipient_contact_address . ', ' . str_replace('_', ' ', $shipment_details['recipient']->recipient_contact_address_type) . ' ' . $shipment_details['recipient']->recipient_contact_zipcode . ', ' . $shipment_details['recipient_country']->short_name
                                            : $shipment_details['recipient']->recipient_contact_address . ', ' . str_replace('_', ' ', $shipment_details['recipient']->recipient_contact_address_type) . ' ' . $shipment_details['recipient']->recipient_contact_zipcode);

                                    ?>
                                    <th><?php echo $sender_address_label; ?></th>
                                    <td><?php echo $sender_address; ?></td>

                                    <th><?php echo $recipient_address_label; ?></th>
                                    <td><?php echo $recipient_address; ?></td>
                                </tr>
                                <tr>
                                    <?php
                                    $is_sender_individual = $shipment_details['sender_type'] === 'individual';
                                    $is_recipient_individual = $shipment_details['recipient_type'] === 'individual';

                                    $sender_phone_label = $is_sender_individual ? 'Sender Number' : 'Contact Person Number';
                                    $sender_phone = $is_sender_individual ? $shipment_details['sender']->phone_number
                                        : $shipment_details['sender']->contact_person_phone_number;

                                    $receiver_phone_label = $is_recipient_individual ? 'Receiver Number' : 'Contact Person Number';
                                    $receiver_phone = $is_recipient_individual ? $shipment_details['recipient']->phone_number
                                        : $shipment_details['recipient']->recipient_contact_person_phone_number;

                                    $sender_phone = str_replace('+254', '0', $sender_phone);
                                    $receiver_phone = str_replace('+254', '0', $receiver_phone);
                                    ?>

                                    <th><?php echo $sender_phone_label; ?></th>
                                    <td><?php echo $sender_phone; ?></td>

                                    <th><?php echo $receiver_phone_label; ?></th>
                                    <td><?php echo $receiver_phone; ?></td>

                                </tr>
                                <tr>
                                    <th>Tracking Number</th>
                                    <td
                                            colspan="3"><?php echo $shipment_details['shipment']->tracking_id; ?></td>
                                </tr>
                                <tr>
                                    <th>Shipping Level</th>
                                    <td><?php echo strtoupper($shipment_details['shipment']->shipping_category); ?></td>
                                    <th>Shipping Mode</th>
                                    <td><?php echo $shipment_details['shipment']->shipping_mode; ?></td>
                                </tr>
                            </table>
                            <div class="shipping-section">
                                <div class="shipping-info">
                                    <table class="info-table">
                                        <tr>
                                            <th>
                                                <?php echo !empty($shipment_details['shipment']->company_type) ? $shipment_details['shipment']->company_type : 'Courier Company'; ?>
                                            </th>
                                            <td><?php echo htmlspecialchars($logistic_company); ?></td>
                                        </tr>
                                        <tr style="border-left:none; border-right:0px; ">
                                            <td colspan="2" class="no-border">
                                                <h4 style="font-weight:bold; margin-bottom:-10px;"
                                                    class="package-title">Package Details</h4>
                                                <?php if ($shipment_details['shipment']->fcl_shipment == 1): ?>
                                                    <table class="info-table no-border">
                                                        <?php $counter = 1; ?>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Quantity (cm)</th>
                                                            <th>Description (cm)</th>
                                                            <th>FCL Option</th>
                                                        </tr>
                                                        <?php foreach ($shipment_details['packages'] as $package): ?>
                                                            <tr>
                                                                <td><?php echo $counter ?></td>
                                                                <td><?php echo $package->quantity ?></td>
                                                                <td><?php echo $package->description ?></td>
                                                                <td><?php echo $package->fcl_option ?></td>
                                                            </tr>
                                                            <?php $counter++; ?>
                                                        <?php endforeach; ?>
                                                    </table>
                                                <?php else: ?>
                                                    <table class="info-table no-border">
                                                        <?php $counter = 1; ?>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Quantity</th>
                                                            <th>Length (cm)</th>
                                                            <th>Width (cm)</th>
                                                            <th>Height (cm)</th>
                                                            <th>Volumetric Weight (kg)</th>
                                                            <th>Gross Weight (kg)</th>
                                                            <th>Chargeable Weight (kg)</th>
                                                        </tr>
                                                        <?php foreach ($shipment_details['packages'] as $package): ?>
                                                            <tr>
                                                                <td><?php echo $counter ?></td>
                                                                <td><?php echo $package->quantity ?></td>
                                                                <td><?php echo $package->length ?></td>
                                                                <td><?php echo $package->width ?></td>
                                                                <td><?php echo $package->height ?></td>
                                                                <td><?php echo $package->weight_volume ?></td>
                                                                <td><?php echo $package->weight ?></td>
                                                                <td><?php echo $package->chargeable_weight ?></td>
                                                            </tr>
                                                            <?php $counter++; ?>
                                                        <?php endforeach; ?>
                                                    </table>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Shipping Notes</th>
                                            <td><?php
                                                $_wb_inst = trim($shipment_details['shipment']->special_instructions ?? '');
                                                echo $_wb_inst ? nl2br(htmlspecialchars($_wb_inst)) : '<span style="color:#aaa;">—</span>';
                                            ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <?php
                            $s = $shipment_details['shipment'];
                            // Round-trip badge only — no amounts shown on waybill
                            if (!empty($s->is_round_trip)):
                            ?>
                            <table class="info-table" style="margin-top:10px;">
                                <tr>
                                    <th colspan="4" style="background:#f5f5f5; font-size:12px; text-transform:uppercase; letter-spacing:.5px;">
                                        Shipment Type &nbsp;<span style="background:#28a745; color:#fff; padding:1px 8px; border-radius:10px; font-size:10px;">Round Trip</span>
                                    </th>
                                </tr>
                            </table>
                            <?php endif; ?>

                            <!-- ── Agent / Created By ───────────────────────── -->
                            <?php
                            // Show agent name if shipment was booked by an agent
                            $agent_row = null;
                            if (!empty($s->staff_id)) {
                                $agent_row = $this->db
                                    ->select('a.id, CONCAT(st.firstname," ",st.lastname) AS agent_name, a.phone_number AS agent_phone')
                                    ->from(db_prefix() . '_agents a')
                                    ->join(db_prefix() . 'staff st', 'st.staffid = a.staff_id', 'left')
                                    ->where('a.staff_id', $s->staff_id)
                                    ->get()->row();
                            }
                            if ($agent_row): ?>
                            <table class="info-table" style="margin-top:6px;">
                                <tr>
                                    <th>Shipped By (Agent)</th>
                                    <td><?php echo htmlspecialchars($agent_row->agent_name); ?>
                                        <?php if (!empty($agent_row->agent_phone)): ?>
                                            &mdash; <?php echo htmlspecialchars($agent_row->agent_phone); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>

                            <link rel="stylesheet" href="path/to/waybill.css">

                            <div class="terms">
                                <h4 style="margin-bottom:5px;">Terms and Conditions</h4>
                                <div class="content">
                                    <div class="column">
                                        <p><strong>1. General Conditions:</strong> Use of our services implies
                                            acceptance of these terms and applicable laws.</p>
                                        <p><strong>2. Delivery Times:</strong> We estimate delivery times but do not
                                            guarantee specific dates. Delays may occur.</p>
                                        <p><strong>3. Package Restrictions:</strong> Ensure package contents comply
                                            with
                                            laws. Some items may be restricted or prohibited.</p>
                                        <p><strong>4. Shipping Charges:</strong> Charges are based on weight,
                                            dimensions, and destination. Additional fees may apply.</p>
                                        <p><strong>5. Claims and Liability:</strong> We are not liable for issues
                                            after
                                            delivery. Claims must be reported within a specified period.</p>
                                    </div>
                                    <div class="column">
                                        <p><strong>6. Customs and Duties:</strong> You are responsible for customs
                                            fees
                                            and taxes for international shipments.</p>
                                        <p><strong>7. Insurance:</strong> Optional insurance covers package value up
                                            to
                                            a limit. Refer to our policy for details.</p>
                                        <p><strong>8. Address Accuracy:</strong> Ensure correct address details to
                                            avoid
                                            delays or issues.</p>
                                        <p><strong>9. Changes to Terms:</strong> Terms may be updated. Review
                                            regularly
                                            for any changes.</p>
                                        <p><strong>10. Contact Information:</strong> For questions or concerns,
                                            contact
                                            our customer service at [contact information].</p>
                                    </div>
                                </div>
                                <p>Thank you for using our services. We strive to provide reliable delivery
                                    solutions.</p>
                            </div>
                            <div class="footer">
                                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($logistic_company); ?>. All rights reserved.
                            </div>
                        </div>

                        <?php if ($show_live_map): ?>
                        <div style="margin-top:20px; background:#fff; border:1px solid #e3ebf5; border-radius:12px; padding:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="margin:0; font-size:15px; font-weight:700; color:#0d47a1;"><i class="fa fa-map-marker"></i> Live Rider Tracking</h4>
                                <span id="live_map_updated" class="text-muted" style="font-size:12px;">Waiting for rider to share location…</span>
                            </div>
                            <div id="live_map" style="width:100%; height:320px; border-radius:8px; background:#eee;"></div>
                        </div>

                        <script>
                        var LATEST_LOCATION_URL = <?php echo json_encode(site_url('admin/courier_goshipping/shipments/latest_location/' . $shipment_details['shipment']->id)); ?>;
                        var DEST_ADDRESS = <?php echo json_encode($dest_address); ?>;
                        var MAP_PROVIDER = <?php echo json_encode($map_provider); ?>;
                        var GOOGLE_API_KEY = <?php echo json_encode($google_api_key); ?>;

                        (function () {
                            var map, marker, polyline, destMarker;
                            var leafletReady = false, googleReady = false;

                            function timeAgo(iso) {
                                var seconds = Math.floor((Date.now() - new Date(iso.replace(' ', 'T')).getTime()) / 1000);
                                if (seconds < 60) return seconds + 's ago';
                                if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
                                return Math.floor(seconds / 3600) + 'h ago';
                            }

                            function geocodeDestination(cb) {
                                if (!DEST_ADDRESS) { return; }
                                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(DEST_ADDRESS))
                                    .then(function (r) { return r.json(); })
                                    .then(function (res) {
                                        if (res && res[0]) { cb(parseFloat(res[0].lat), parseFloat(res[0].lon)); }
                                    }).catch(function () {});
                            }

                            function initLeaflet(lat, lng) {
                                map = L.map('live_map').setView([lat, lng], 13);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap contributors'
                                }).addTo(map);
                                marker = L.marker([lat, lng]).addTo(map).bindPopup('Rider');
                                polyline = L.polyline([], { color: '#1565c0', weight: 4 }).addTo(map);
                                leafletReady = true;
                                geocodeDestination(function (dLat, dLng) {
                                    destMarker = L.marker([dLat, dLng], {
                                        icon: L.icon({ iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png', iconSize: [20, 32], iconAnchor: [10, 32] })
                                    }).addTo(map).bindPopup('Delivery address');
                                });
                            }

                            function updateLeaflet(lat, lng, trail) {
                                marker.setLatLng([lat, lng]);
                                map.panTo([lat, lng]);
                                if (trail && trail.length) {
                                    polyline.setLatLngs(trail.map(function (p) { return [p.latitude, p.longitude]; }));
                                }
                            }

                            function initGoogle(lat, lng) {
                                map = new google.maps.Map(document.getElementById('live_map'), { center: { lat: lat, lng: lng }, zoom: 13 });
                                marker = new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map, label: 'R' });
                                polyline = new google.maps.Polyline({ path: [], strokeColor: '#1565c0', strokeWeight: 4 });
                                polyline.setMap(map);
                                googleReady = true;
                                geocodeDestination(function (dLat, dLng) {
                                    destMarker = new google.maps.Marker({ position: { lat: dLat, lng: dLng }, map: map, label: 'D' });
                                });
                            }

                            function updateGoogle(lat, lng, trail) {
                                var pos = { lat: lat, lng: lng };
                                marker.setPosition(pos);
                                map.panTo(pos);
                                if (trail && trail.length) {
                                    polyline.setPath(trail.map(function (p) { return { lat: p.latitude, lng: p.longitude }; }));
                                }
                            }

                            function poll() {
                                fetch(LATEST_LOCATION_URL).then(function (r) { return r.json(); }).then(function (res) {
                                    if (!res.success) { return; }
                                    document.getElementById('live_map_updated').textContent =
                                        'Updated ' + timeAgo(res.recorded_at) + (res.speed ? ' · ' + Math.round(res.speed * 3.6) + ' km/h' : '');
                                    if (MAP_PROVIDER === 'google' && GOOGLE_API_KEY) {
                                        if (!googleReady) { initGoogle(res.latitude, res.longitude); } else { updateGoogle(res.latitude, res.longitude, res.trail); }
                                    } else {
                                        if (!leafletReady) { initLeaflet(res.latitude, res.longitude); } else { updateLeaflet(res.latitude, res.longitude, res.trail); }
                                    }
                                }).catch(function () {});
                            }

                            function loadLeafletAssets(cb) {
                                if (window.L) { cb(); return; }
                                var css = document.createElement('link');
                                css.rel = 'stylesheet';
                                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                                document.head.appendChild(css);
                                var js = document.createElement('script');
                                js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                                js.onload = cb;
                                document.head.appendChild(js);
                            }

                            function loadGoogleAssets(cb) {
                                if (window.google && window.google.maps) { cb(); return; }
                                var js = document.createElement('script');
                                js.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(GOOGLE_API_KEY);
                                js.onload = cb;
                                js.onerror = function () {
                                    document.getElementById('live_map_updated').textContent = 'Google Maps failed to load — check the API key, or switch to the free map provider.';
                                };
                                document.head.appendChild(js);
                            }

                            if (MAP_PROVIDER === 'google' && GOOGLE_API_KEY) {
                                loadGoogleAssets(function () { poll(); setInterval(poll, 8000); });
                            } else {
                                loadLeafletAssets(function () { poll(); setInterval(poll, 8000); });
                            }
                        })();
                        </script>
                        <?php endif; ?>
                    </div>
                </div>
            <?php echo form_close(); ?>
            </div><!-- /.col-md-9 -->
        </div><!-- /.row -->
    </div><!-- /.content -->
    <?php init_tail(); ?>
</div><!-- /cgs-page -->
</div><!-- /#wrapper -->

<?php
$_wb_is_portal_pending = empty($shipment_details['shipment']->invoice_id)
    && $shipment_details['shipment']->staff_id == 0;
$_wb_quoted = isset($shipment_details['shipment']->quoted_amount) ? (float)$shipment_details['shipment']->quoted_amount : 0;
$_wb_sid    = $shipment_details['shipment']->id;
$_wb_packages = $this->db->get_where(db_prefix() . '_shipment_packages', ['shipment_id' => $_wb_sid])->result_array();
$_wb_sender = !empty($shipment_details['shipment']->sender_id)
    ? $this->db->get_where(db_prefix() . '_shipment_senders', ['id' => $shipment_details['shipment']->sender_id])->row()
    : null;
$_wb_recipient = !empty($shipment_details['shipment']->recipient_id)
    ? $this->db->get_where(db_prefix() . '_shipment_recipients', ['id' => $shipment_details['shipment']->recipient_id])->row()
    : null;
?>

<?php if ($_wb_is_portal_pending): ?>
<!-- ── Portal Request Banner ─────────────────────────────────── -->
<div style="background:#fff8e1;border:2px solid #f57c00;border-radius:8px;padding:16px 20px;margin:0 0 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;"
     id="portal-banner">
    <div>
        <strong style="color:#e65100;font-size:15px;"><i class="fa fa-exclamation-circle"></i> Portal Request — Pending Confirmation</strong>
        <p style="margin:4px 0 0;font-size:13px;color:#555;">This shipment was submitted by a customer from the portal. Review the details, set the unit price, and confirm to generate the official waybill and invoice.</p>
        <?php if ($_wb_quoted > 0): ?>
        <p style="margin:4px 0 0;font-size:13px;color:#2e7d32;"><strong>Customer's price estimate: <?php echo number_format($_wb_quoted, 2); ?></strong></p>
        <?php endif; ?>
    </div>
    <button type="button" onclick="openConfirmPortalModal(<?php echo $_wb_sid; ?>);"
            style="background:#00796b;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:14px;font-weight:700;cursor:pointer;white-space:nowrap;">
        <i class="fa fa-check-circle"></i> Confirm &amp; Create Invoice + Waybill
    </button>
</div>
<?php endif; ?>

<!-- ── Confirm Portal Request Modal ─────────────────────────── -->
<div class="modal fade" id="confirmPortalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#00796b;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirm Portal Request &amp; Create Invoice</h4>
            </div>
            <div class="modal-body">
                <!-- Shipment summary -->
                <div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;padding:14px 18px;margin-bottom:16px;font-size:13px;">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Tracking Ref:</strong> <?php echo htmlspecialchars($shipment_details['shipment']->tracking_id); ?><br>
                            <strong>Mode:</strong> <?php echo htmlspecialchars($shipment_details['shipment']->shipping_mode); ?>
                        </div>
                        <div class="col-sm-6">
                            <?php if ($_wb_sender): ?>
                            <strong>Sender:</strong> <?php echo htmlspecialchars(trim($_wb_sender->first_name . ' ' . $_wb_sender->last_name)); ?><br>
                            <strong>Phone:</strong> <?php echo htmlspecialchars(str_replace('+254', '0', $_wb_sender->phone_number)); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Packages -->
                <?php if (!empty($_wb_packages)): ?>
                <h5 style="margin-bottom:8px;">Packages</h5>
                <table class="table table-condensed table-bordered" style="font-size:13px;">
                    <thead style="background:#f5f5f5;">
                        <tr><th>Description</th><th style="width:60px;">Qty</th><th style="width:90px;">Weight (kg)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_wb_packages as $pkg): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pkg['description'] ?? '—'); ?></td>
                            <td><?php echo (int)($pkg['quantity'] ?? 1); ?></td>
                            <td><?php echo $pkg['weight'] ?? '—'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color:#888;font-size:13px;">No package records — enter the total price below.</p>
                <?php endif; ?>

                <!-- Pricing -->
                <div class="row" style="margin-top:16px;">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label><strong>Unit Price (per line item)</strong> <span style="color:red">*</span></label>
                            <input type="number" id="wb_cpm_unit_price" class="form-control"
                                   value="<?php echo $_wb_quoted > 0 ? $_wb_quoted : ''; ?>"
                                   placeholder="e.g. 600.00" min="0" step="0.01">
                            <?php if ($_wb_quoted > 0): ?>
                            <small class="text-muted">Customer estimate: <strong><?php echo number_format($_wb_quoted, 2); ?></strong></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label><strong>Apply VAT?</strong></label>
                            <div>
                                <label style="font-weight:normal;cursor:pointer;">
                                    <input type="checkbox" id="wb_cpm_vat" value="1" checked>
                                    Apply VAT (<?php echo get_option('courier_parcel_vat_rate') ?: 16; ?>%)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="wb_cpm_alert" style="display:none;" class="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="wb_cpm_submit" class="btn btn-success" style="background:#00796b;border-color:#00796b;">
                    <i class="fa fa-check"></i> Create Invoice &amp; Waybill
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function addPortalCommercialItem() {
    var tbody = document.querySelector('#portalCommercialItemsTable tbody');
    if (!tbody) return;
    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="number" class="form-control input-sm cpm-qty" name="commodity_quantity[]" step="any"></td>
        <td><textarea class="form-control input-sm cpm-desc" name="commodity_description[]" rows="1"></textarea></td>
        <td><input type="number" class="form-control input-sm cpm-val" name="declared_value[]" step="any"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function openConfirmPortalModal(sid) {
    $('#wb_cpm_alert').hide();
    $('#confirmPortalModal').modal('show');
}

$('#wb_cpm_submit').on('click', function () {
    var unitPrice = parseFloat($('#wb_cpm_unit_price').val());
    if (!unitPrice || unitPrice <= 0) {
        $('#wb_cpm_alert').removeClass('alert-success').addClass('alert alert-danger')
            .text('Please enter a valid unit price.').show();
        return;
    }
    
    // Collect commercial items if any
    var commQty = [], commDesc = [], commVal = [];
    $('.cpm-qty').each(function() { commQty.push($(this).val()); });
    $('.cpm-desc').each(function() { commDesc.push($(this).val()); });
    $('.cpm-val').each(function() { commVal.push($(this).val()); });

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating…');
    $('#wb_cpm_alert').hide();

    $.ajax({
        url:      '<?php echo admin_url("courier_goshipping/shipments/confirm_portal_request/" . $_wb_sid); ?>',
        type:     'POST',
        dataType: 'json',
        data: {
            unit_price: unitPrice,
            apply_vat:  $('#wb_cpm_vat').is(':checked') ? 1 : 0,
            commodity_quantity: commQty,
            commodity_description: commDesc,
            declared_value: commVal,
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>',
        },
        success: function (r) {
            if (r.status === 'success') {
                $('#wb_cpm_alert').removeClass('alert-danger').addClass('alert alert-success')
                    .html('<i class="fa fa-check-circle"></i> Waybill <strong>' + r.waybill_number + '</strong> and invoice created! Reloading…').show();
                $btn.html('<i class="fa fa-check"></i> Done!');
                setTimeout(function () { location.reload(); }, 2000);
            } else {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice &amp; Waybill');
                $('#wb_cpm_alert').removeClass('alert-success').addClass('alert alert-danger')
                    .text(r.message || 'Error creating invoice.').show();
            }
        },
        error: function () {
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice &amp; Waybill');
            $('#wb_cpm_alert').removeClass('alert-success').addClass('alert alert-danger')
                .text('Network error. Please try again.').show();
        }
    });
});
</script>

<div class="modal fade" id="update_status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('courier_goshipping/shipments/update_status/' . $shipment_details['shipment']->id), ['id' => 'update-shipment-status-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Update Status</h4>
                <input type="hidden" name="shipment_id" value="<?php echo $shipment_details['shipment']->id; ?>">
                <input type="hidden" value="" name="signature">
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <select onchange="toggleShipmentStops(); toggleDeliveryDetails();" id="status_id"
                                name="status_id"
                                class="custom-select">
                            <?php foreach ($statuses as $status): ?>
                                <?php if ($status->id == 2) continue; ?>
                                <option <?= $status->id == $shipment_details['shipment']->status_id ? "selected" : ""; ?>
                                        value="<?php echo $status->id; ?>">
                                    <?php echo $status->description ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div style="margin-top:20px;" id="delivery_details" class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped"
                                           id="deliveryTable">
                                        <thead>
                                        <tr>
                                            <th>Receiver First Name</th>
                                            <th>Receiver Last Name</th>
                                            <th>Signature</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <?php echo form_input([
                                                    'name' => 'first_name',
                                                    'class' => 'form-control',
                                                    'type' => 'text',
                                                    'step' => 'any',
                                                    'value' => set_value('first_name')
                                                ]); ?>
                                                <?php echo form_error('first_name', '<div class="text-danger">', '</div>'); ?>
                                            </td>
                                            <td>
                                                <?php echo form_input([
                                                    'name' => 'last_name',
                                                    'class' => 'form-control',
                                                    'type' => 'text',
                                                    'step' => 'any',
                                                    'value' => set_value('last_name')
                                                ]); ?>
                                                <?php echo form_error('last_name', '<div class="text-danger">', '</div>'); ?>
                                            </td>
                                            <td>
                                                <div style="margin-bottom:10px;" id="signatureCanvasP"
                                                     class="col-md-12">
                                                    <canvas height="150" id="signature"
                                                            style="margin-top:10px;  border: 1px solid #ddd;"></canvas>
                                                    <br>
                                                    <button style="margin-top:10px;" id="clear-signature"
                                                            class="btn-info btn ">Clear Signature
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($fleet_report['trips'])):
                            $fleet_summary = $fleet_report['summary'] ?? [];
                        ?>
                        <div style="margin-top:20px;" class="row">
                            <div class="col-md-12">
                                <h4 style="font-size:16px; font-weight:600;"><i class="fa fa-truck"></i> Shipment Trip Report</h4>
                                <div class="row" style="margin-bottom:15px;">
                                    <div class="col-md-2 col-sm-4 col-xs-6">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Total Trips</div>
                                                <div style="font-size:22px; font-weight:700;"><?php echo (int) ($fleet_summary['total_trips'] ?? 0); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-6">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Completed</div>
                                                <div style="font-size:22px; font-weight:700; color:#28a745;"><?php echo (int) ($fleet_summary['completed_trips'] ?? 0); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-6">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Active</div>
                                                <div style="font-size:22px; font-weight:700; color:#f39c12;"><?php echo (int) ($fleet_summary['active_trips'] ?? 0); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-6">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Mileage Used</div>
                                                <div style="font-size:18px; font-weight:700;"><?php echo number_format((float) ($fleet_summary['total_distance'] ?? 0), 2); ?> km</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-6">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Fuel Used</div>
                                                <div style="font-size:18px; font-weight:700;"><?php echo number_format((float) ($fleet_summary['total_fuel'] ?? 0), 2); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-12">
                                        <div class="panel panel-default">
                                            <div class="panel-body text-center">
                                                <div style="font-size:12px; color:#777;">Latest Vehicle / Driver</div>
                                                <div style="font-size:13px; font-weight:700;"><?php echo htmlspecialchars(($fleet_summary['latest_vehicle'] ?? '') ?: '-'); ?></div>
                                                <div style="font-size:12px; color:#666;"><?php echo htmlspecialchars(($fleet_summary['latest_driver'] ?? '') ?: '-'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>Trip ID</th>
                                            <th>Date</th>
                                            <th>Route</th>
                                            <th>Vehicle</th>
                                            <th>Driver</th>
                                            <th>Status</th>
                                            <th>Start Odo</th>
                                            <th>End Odo</th>
                                            <th>Mileage Used</th>
                                            <th>Fuel</th>
                                            <th>Offloads</th>
                                            <th>View</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($fleet_report['trips'] as $fleet_trip):
                                            $t = $fleet_trip['trip'];
                                            $assignment = $fleet_trip['assignment'];
                                            $vehicle_label = trim((string) ($t->vehicle_name ?? ''));
                                            if ($vehicle_label === '' && $assignment) {
                                                $vehicle_label = trim((string) (($assignment->vehicle_name ?? '') . ' ' . ($assignment->license_plate ?? '')));
                                            }
                                            $driver_label = trim((string) ($t->driver_name ?? ''));
                                            if ($driver_label === '' && $assignment) {
                                                $driver_label = trim((string) ($assignment->driver_name ?? ''));
                                            }
                                        ?>
                                        <tr>
                                            <td>#<?php echo $t->id; ?></td>
                                            <td><?php echo !empty($t->trip_date) ? _dt($t->trip_date) : '-'; ?></td>
                                            <td><?php echo htmlspecialchars(($t->from_point_name ?? 'Origin') . ' -> ' . ($t->to_point_name ?? 'Destination')); ?></td>
                                            <td><?php echo htmlspecialchars($vehicle_label ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($driver_label ?: '-'); ?></td>
                                            <td>
                                                <span class="label label-<?php echo ($t->status ?? '') === 'completed' ? 'success' : (($t->status ?? '') === 'cancelled' ? 'danger' : 'info'); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $t->status ?? 'pending')); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $fleet_trip['start_odo'] !== null ? number_format((float) $fleet_trip['start_odo'], 2) : '-'; ?></td>
                                            <td><?php echo $fleet_trip['end_odo'] !== null ? number_format((float) $fleet_trip['end_odo'], 2) : '-'; ?></td>
                                            <td><?php echo number_format((float) ($fleet_trip['distance'] ?? 0), 2); ?> km</td>
                                            <td><?php echo number_format((float) ($fleet_trip['fuel_qty'] ?? 0), 2); ?></td>
                                            <td><?php echo (int) ($fleet_trip['offload_count'] ?? 0); ?></td>
                                            <td><a href="<?php echo admin_url('fleet/trips/detail/'.$t->id); ?>" class="btn btn-default btn-xs" target="_blank">View Trip</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Shipment stops Information -->
                        <div style="margin-top:20px;" id="shipment_stops" class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped"
                                           id="shipmentStopsTable">
                                        <thead>
                                        <tr>
                                            <th>Departure Point</th>
                                            <th>Destination Point</th>
                                            <th>Description</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <?php echo form_input([
                                                    'name' => 'departure_points[]',
                                                    'class' => 'form-control',
                                                    'type' => 'text',
                                                    'step' => 'any',
                                                    'value' => set_value('departure_points[]')
                                                ]); ?>
                                                <?php echo form_error('departure_points[]', '<div class="text-danger">', '</div>'); ?>
                                            </td>
                                            <td>
                                                <?php echo form_input([
                                                    'name' => 'destination_points[]',
                                                    'class' => 'form-control',
                                                    'type' => 'text',
                                                    'step' => 'any',
                                                    'value' => set_value('destination_points[]')
                                                ]); ?>
                                                <?php echo form_error('destination_points[]', '<div class="text-danger">', '</div>'); ?>
                                            </td>
                                            <td>
                                                                <textarea name="description[]"
                                                                          class="custom-textarea"
                                                                          rows="3"
                                                                ><?php echo set_value('description[]'); ?></textarea>
                                                <?php echo form_error('description[]', '<div class="text-danger">', '</div>'); ?>
                                            </td>

                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="addShipmentStops()">
                                    Add Stops
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('update status'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        window.toggleShipmentStops = function () {
            const statusId = document.getElementById('status_id');
            const shipmentStops = document.getElementById('shipment_stops');
            const deliveryDetails = document.getElementById('delivery_details');

            if (statusId.value === '5') {
                shipmentStops.style.display = 'block';
            } else {
                shipmentStops.style.display = 'none';
            }
        }


        window.toggleDeliveryDetails = function () {

            const statusId = document.getElementById('status_id');
            const deliveryDetails = document.getElementById('delivery_details');

            if (statusId.value === '8') {
                deliveryDetails.style.display = 'block';
            } else {
                deliveryDetails.style.display = 'none';
            }
        }

        toggleDeliveryDetails();
        toggleShipmentStops();

        function attachRemoveEvent(button) {
            button.addEventListener('click', function () {
                this.closest('tr').remove();
            });
        }

        // Attach event to initial remove buttons
        const packageRemoveButtons = document.getElementsByClassName('remove-shipment-stop');
        for (let i = 0; i < packageRemoveButtons.length; i++) {
            attachRemoveEvent(packageRemoveButtons[i]);
        }

        // Add new row functionality for FCL package
        window.addShipmentStops = function () {
            const packageTable = document.getElementById('shipmentStopsTable').getElementsByTagName('tbody')[0];
            const newRow = packageTable.insertRow();

            newRow.innerHTML = `
            <td><input name="departure_points[]" class="form-control" type="text"></td>
            <td><input name="destination_points[]" class="form-control" type="text"></td>
            <td><textarea name="description[]" class="custom-textarea" rows="3"></textarea></td>
            <td><button type="button" class="btn btn-danger remove-shipment-stop"><i class="fa fa-trash"></i></button></td>
        `;

            attachRemoveEvent(newRow.getElementsByClassName('remove-shipment-stop')[0]);
        }


        let canvas = document.getElementById("signature");
        const signaturePad = new SignaturePad(canvas);

        $('#clear-signature').on('click', function (event) {
            event.preventDefault()
            signaturePad.clear();
        });

        document.getElementById('update-shipment-status-form').addEventListener('submit', function (e) {
            canvas = document.getElementById('signature');
            document.querySelector('input[name="signature"]').value = canvas.toDataURL('image/png');
        });

    })
</script>

<!-- ── Send Waybill by Email Modal ─────────────────────────────── -->
<div class="modal fade" id="send_waybill_email_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-envelope" style="color:#1565c0;"></i> Send Waybill by Email</h4>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:#555;margin-bottom:16px;">
                    An email with the shipment details and a tracking link will be sent to the address below.
                    If the recipient has no email on record, enter one manually.
                </p>
                <div class="form-group">
                    <label for="waybill_send_email"><strong>Recipient Email</strong></label>
                    <input type="email" id="waybill_send_email" class="form-control"
                           placeholder="recipient@example.com"
                           value="<?php
                               $wb_recip = $shipment_details['recipient'] ?? null;
                               $wb_email = '';
                               if (!empty($wb_recip)) {
                                   $wb_email = $wb_recip->email
                                       ?? $wb_recip->recipient_contact_person_email
                                       ?? '';
                               }
                               echo htmlspecialchars($wb_email);
                           ?>">
                    <span class="help-block" style="font-size:12px;">Leave blank to use the stored recipient email.</span>
                </div>
                <div id="waybill_email_alert" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="send_waybill_email_btn" class="btn btn-primary"
                        style="background:#1565c0;border-color:#1565c0;">
                    <i class="fa fa-paper-plane"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Assign Agent/Staff Modal ─────────────────────────────── -->
<div class="modal fade" id="assign_agent_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> <?php echo !empty($salibay_delivery_link) ? 'Assign Rider' : 'Assign Shipment to Agent / Staff'; ?></h4>
            </div>
            <?php echo form_open(admin_url('courier_goshipping/shipments/assign_agent/' . $wb_sid)); ?>
            <div class="modal-body">
                <?php if (!empty($salibay_delivery_link)): ?>
                <div class="form-group">
                    <label for="assigned_staff_id" class="control-label">Select Rider</label>
                    <select name="assigned_staff_id" id="assigned_staff_id" class="selectpicker" data-width="100%" data-none-selected-text="Select Rider" data-live-search="true" required>
                        <option value=""></option>
                        <?php foreach ($salibay_riders as $rider): ?>
                            <option value="<?php echo $rider->staff_id; ?>" <?php echo ((int)$shipment_details['shipment']->staff_id === (int)$rider->staff_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rider->name . ' (' . $rider->phone . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($salibay_riders)): ?>
                    <p class="text-muted" style="margin-top:8px;">No riders are linked to a driver profile yet — see the Riders tab under Salibay Fulfilment.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="assigned_staff_id" class="control-label">Select Agent / Staff</label>
                    <select name="assigned_staff_id" id="assigned_staff_id" class="selectpicker" data-width="100%" data-none-selected-text="Select Agent or Staff" data-live-search="true" required>
                        <option value=""></option>
                        <?php foreach ($staff_members as $staff): ?>
                            <option value="<?php echo $staff->staffid; ?>" <?php echo ((int)$shipment_details['shipment']->staff_id === (int)$staff->staffid) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($staff->firstname . ' ' . $staff->lastname) . (!empty($staff->agent_id) ? ' (Agent)' : ' (Staff)'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Assignment</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
function openSendWaybillModal() {
    $('#waybill_email_alert').hide().html('');
    $('#send_waybill_email_modal').modal('show');
}

$('#send_waybill_email_btn').on('click', function () {
    var $btn  = $(this);
    var email = $('#waybill_send_email').val().trim();

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending…');
    $('#waybill_email_alert').hide();

    $.ajax({
        url: '<?php echo admin_url('courier_goshipping/shipments/send_waybill_email/' . $shipment_details['shipment']->id); ?>',
        type: 'POST',
        dataType: 'json',
        data: { email: email },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (resp) {
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
            if (resp.success) {
                $('#waybill_email_alert')
                    .removeClass('alert-danger').addClass('alert alert-success')
                    .html('<i class="fa fa-check-circle"></i> ' + resp.message)
                    .show();
                setTimeout(function () {
                    $('#send_waybill_email_modal').modal('hide');
                }, 2500);
            } else {
                $('#waybill_email_alert')
                    .removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fa fa-exclamation-circle"></i> ' + resp.message)
                    .show();
            }
        },
        error: function () {
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
            $('#waybill_email_alert')
                .removeClass('alert-success').addClass('alert alert-danger')
                .html('<i class="fa fa-exclamation-circle"></i> Server error. Please try again.')
                .show();
        }
    });
});
</script>
