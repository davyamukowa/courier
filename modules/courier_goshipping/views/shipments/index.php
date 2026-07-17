<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php

?>

<?php
// Include Flatpickr CSS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';

// Include Select2 CSS
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';

?>

<style>
    .custom-button,
    .btn.btn-primary,
    .btn.btn-info,
    .btn.btn-success,
    .btn.btn-warning,
    .btn.btn-default {
        border-radius: 8px;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-button,
    .btn.btn-primary {
        background: #c62828 !important;
        border-color: #c62828 !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(198, 40, 40, 0.18);
    }

    .custom-button:hover,
    .custom-button:focus,
    .btn.btn-primary:hover,
    .btn.btn-primary:focus {
        background: #a61f1f !important;
        border-color: #a61f1f !important;
        color: #fff !important;
        box-shadow: 0 10px 22px rgba(166, 31, 31, 0.24);
    }

    .btn.btn-default,
    .btn.btn-info,
    .btn.btn-success,
    .btn.btn-warning {
        background: #fff1f1 !important;
        border-color: #d94a4a !important;
        color: #b42323 !important;
    }

    .btn.btn-default:hover,
    .btn.btn-default:focus,
    .btn.btn-info:hover,
    .btn.btn-info:focus,
    .btn.btn-success:hover,
    .btn.btn-success:focus,
    .btn.btn-warning:hover,
    .btn.btn-warning:focus {
        background: #c62828 !important;
        border-color: #c62828 !important;
        color: #fff !important;
    }

    .select2-container .select2-selection--single {
        background-color: #f9fafb;
        border: 1px solid #d1d5db;
        color: #111827;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        padding: 5px 10px 10px 10px;
        width: 100%;
        height: 35px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select2-selection__arrow {
        transform: translateY(30%);
    }

    /* Waybill link in tracking ID column */
    #shipmentTable td:first-child a:hover {
        text-decoration: underline;
    }

    .shipment-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }

    .shipment-table-scroll::-webkit-scrollbar {
        height: 12px;
    }

    .shipment-table-scroll::-webkit-scrollbar-track {
        background: #e5e7eb;
        border-radius: 999px;
    }

    .shipment-table-scroll::-webkit-scrollbar-thumb {
        background: #9ca3af;
        border-radius: 999px;
        border: 2px solid #e5e7eb;
    }

    .shipment-table-scroll::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }

    #shipmentTable {
        min-width: 1550px;
    }

    .shipment-table-hint {
        margin-top: 8px;
        font-size: 12px;
        color: #6b7280;
    }
</style>

<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>

        <div class="row">
            <div class="col-md-12">
                <?php
                $type = $this->session->userdata('type');
                $mode = $this->session->userdata('mode');
                $mode_type = $this->session->userdata('mode_type');


                if ($type == 'domestic') {
                    $mode = null;
                    $mode_type = null;
                }

                ?>
                <?php $this->load->view('courier_goshipping/shipments/_page_header', [
                    'header_context' => 'list',
                    'header_type' => $type,
                    'header_mode' => $mode,
                    'header_mode_type' => $mode_type,
                    'shipment_details' => $shipment_details ?? [],
                ]); ?>
                <div style="background-color:transparent;" class="panel_s">
                    <div style="padding:15px; margin-bottom:10px;">
                        <a style="text-decoration: none; border: 2px solid black;" class="custom-button"
                           href="<?php echo !empty($mode) ? admin_url('courier_goshipping/shipments/create?type=' . $type . '&mode=' . $mode . '&mode_type=' . $mode_type) : admin_url('courier_goshipping/shipments/create?type=' . $type); ?>">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span style="margin-left: 10px;">Create Shipment</span>
                        </a>
                    </div>

                    <div class="panel-body">
                        <div style="margin-bottom:20px; display: flex; justify-content: flex-end;">
                            <a style="text-decoration: none; border: 2px solid black; margin-left: 10px;"
                               class="custom-button"
                               href="<?php echo admin_url('courier_goshipping/shipments/main?type=international'); ?>">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                <span style="margin-left: 10px;">Shipment Dashboard</span>
                            </a>
                            <a style="text-decoration: none; border: 2px solid black; margin-left: 10px;"
                               class="custom-button"
                               href="#" data-toggle="modal" data-target="#generateManifestModal">
                                <span style="margin-left: 10px;">Generate Manifest</span>
                            </a>
                        </div>
                        <?php echo form_open(admin_url('courier_goshipping/shipments/filter_shipments'), ['id' => 'filter-shipments-form']); ?>

                        <!-- Hidden inputs to carry URL parameters -->
                        <input type="hidden" id="type" name="type" value="<?php echo $type ?>">
                        <input type="hidden" id="mode" name="mode" value="<?php echo $mode ?>">
                        <input type="hidden" id="mode_type" name="mode_type" value="<?php echo $mode_type ?>">

                        <div class="row mb-3" style="margin-bottom: 10px;">
                            <!-- Date Range Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filterDateRange">Filter By Date</label>
                                    <input type="text" class="form-control" id="filterDateRange"
                                           value="<?= !empty($this->session->userdata('filterDateRange')) ? $this->session->userdata('filterDateRange') : '' ?>"
                                           name="filterDateRange"
                                           placeholder="Select date range">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status_id">Filter By Status</label>
                                    <select class="form-control" id="status_id" name="status_id">
                                        <option value="0" <?= $this->session->userdata('status_id') == '0' ? 'selected' : '' ?>>
                                            All
                                        </option>

                                        <option value="1" <?= $this->session->userdata('status_id') == '1' ? 'selected' : '' ?>>Created</option>
                                        <option value="2" <?= $this->session->userdata('status_id') == '2' ? 'selected' : '' ?>>Picked Up</option>
                                        <option value="3" <?= $this->session->userdata('status_id') == '3' ? 'selected' : '' ?>>Received</option>
                                        <option value="4" <?= $this->session->userdata('status_id') == '4' ? 'selected' : '' ?>>Dispatched</option>
                                        <option value="5" <?= $this->session->userdata('status_id') == '5' ? 'selected' : '' ?>>In Transit</option>
                                        <option value="6" <?= $this->session->userdata('status_id') == '6' ? 'selected' : '' ?>>Arrived at Destination</option>
                                        <option value="7" <?= $this->session->userdata('status_id') == '7' ? 'selected' : '' ?>>Out for Delivery</option>
                                        <option value="8" <?= $this->session->userdata('status_id') == '8' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="9" <?= $this->session->userdata('status_id') == '9' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="staff_id">Filter By Agent</label>
                                    <select class="form-control" id="staff_id" name="staff_id">
                                        <option value="0" <?= $this->session->userdata('staff_id') == '0' ? 'selected' : '' ?>>
                                            All
                                        </option>
                                        <?php if (!empty($agents)): ?>
                                            <?php foreach ($agents as $agent): ?>
                                                <option value="<?php echo htmlspecialchars($agent->staff_id); ?>" <?= $this->session->userdata('staff_id') == $agent->staff_id ? 'selected' : '' ?>>
                                                    <?php echo htmlspecialchars($agent->firstname . ' ' . $agent->lastname); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No Agents Available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>


                            <!-- Clear Filter Button -->
                            <div style="margin-top: 22px;" class="col-md-3 mt-4 d-flex">
                                <button type="submit" class="btn btn-secondary"
                                        id="filter">Filter
                                </button>
                                <button type="button" class="btn btn-secondary" id="clearFilters">Clear Filter
                                </button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                        <?php if ($no_shipments): ?>
                            <div class="text-center text-danger">
                                <p>No shipment were Found</p>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($shipment_details)): ?>
                                <div class="shipment-table-scroll">
                                <table class="table dt-table cgs-table" data-order-col="6" data-order-type="desc"
                                       id="shipmentTable">
                                    <thead class="table-head">
                                    <tr>
                                        <th>Tracking ID</th>
                                        <th>Sender</th>
                                        <th>Recipient</th>
                                        <th>Assigned To</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($shipment_details as $shipment_detail): ?>
                                        <tr class="data-row">
                                            <td>
                                                <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $shipment_detail['shipment']->id); ?>"
                                                   style="font-weight:700; color:#1976d2; text-decoration:none;"
                                                   title="View Waybill">
                                                    <?php echo htmlspecialchars($shipment_detail['shipment']->waybill_number ?? $shipment_detail['shipment']->tracking_id); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($shipment_detail['sender_type'] === 'individual'): ?>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <p style="font-weight: bold; font-size: 14px;">
                                                            <?php echo $shipment_detail['sender']->first_name . ' ' . $shipment_detail['sender']->last_name; ?>
                                                        </p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->email; ?></p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->phone_number; ?></p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->address . ' ' . $shipment_detail['sender']->zipcode; ?></p>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <p style="font-weight: bold; font-size: 14px;">
                                                            <?php echo $shipment_detail['sender']->contact_person_name; ?>
                                                        </p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->contact_person_email; ?></p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->contact_person_phone_number; ?></p>
                                                        <p class="text-secondary mb-0"><?php echo $shipment_detail['sender']->contact_address . ' ' . $shipment_detail['sender']->contact_zipcode; ?></p>
                                                    </div>
                                                <?php endif; ?>

                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <?php if ($shipment_detail['recipient_type'] === 'individual'): ?>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <p style="font-weight: bold; font-size: 14px;">
                                                                <?php echo $shipment_detail['recipient']->first_name . ' ' . $shipment_detail['recipient']->last_name; ?>
                                                            </p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->email; ?></p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->phone_number; ?></p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->address . ' ' . $shipment_detail['recipient']->zipcode; ?></p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <p style="font-weight: bold; font-size: 14px;">
                                                                <?php echo $shipment_detail['recipient']->recipient_contact_person_name; ?>
                                                            </p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->recipient_contact_person_email; ?></p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->recipient_contact_person_phone_number; ?></p>
                                                            <p class="text-secondary mb-0"><?php echo $shipment_detail['recipient']->recipient_contact_address . ' ' . $shipment_detail['recipient']->recipient_contact_zipcode; ?></p>
                                                        </div>
                                                    <?php endif; ?>


                                            </td>
                                            <td>
                                                <?php
                                                $assigned_name = trim(($shipment_detail['shipment']->assigned_firstname ?? '') . ' ' . ($shipment_detail['shipment']->assigned_lastname ?? ''));
                                                $assigned_is_agent = !empty($shipment_detail['shipment']->assigned_agent_id);
                                                ?>
                                                <?php if (!empty($shipment_detail['shipment']->staff_id) && $assigned_name !== ''): ?>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <p style="font-weight:bold; font-size:14px; margin-bottom:2px;">
                                                            <?php echo htmlspecialchars($assigned_name); ?>
                                                        </p>
                                                        <p class="text-secondary mb-0">
                                                            <?php echo $assigned_is_agent ? 'Agent' : 'Staff'; ?>
                                                        </p>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-danger">Unassigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($shipment_detail['shipment']->shipping_mode); ?></td>
                                            <td>
                                                <?php
                                                $status_name = htmlspecialchars($shipment_detail['shipment']->status_name);
                                                $status_description = htmlspecialchars($shipment_detail['shipment']->status_description);
                                                switch ($status_name) {
                                                    case 'created':
                                                        $badge_class = 'bg-primary';
                                                        break;
                                                    case 'picked_up':
                                                        $badge_class = 'bg-info';
                                                        break;
                                                    case 'in_transit':
                                                    case 'dispatched':
                                                        $badge_class = 'bg-warning';
                                                        break;
                                                    case 'delivered':
                                                    case 'received':
                                                    case 'arrived_destination':
                                                    case 'out_for_delivery':
                                                        $badge_class = 'bg-success';
                                                        break;
                                                    default:
                                                        $badge_class = 'bg-secondary';
                                                        break;
                                                }
                                                $is_portal_pending = !empty($shipment_detail['shipment']->is_portal_request) && empty($shipment_detail['shipment']->invoice_id);
                                                ?>
                                                <?php if ($is_portal_pending): ?>
                                                <span class="badge badge-pill" style="background:#e65100;color:#fff;font-size:10px;">
                                                    <i class="fa fa-globe"></i> Portal Request
                                                </span><br style="margin:2px 0;">
                                                <?php endif; ?>
                                                <span class="badge badge-pill <?php echo $badge_class; ?>">
                                                    <?php echo $status_description; ?>
                                                </span>
                                            </td>
<td data-order="<?php echo strtotime($shipment_detail['shipment']->created_at); ?>">
    <?php echo date('d-m-Y, g:i A', strtotime($shipment_detail['shipment']->created_at)); ?>
</td>
                                            <td class="align-middle">
                                                <?php
                                                $type      = $this->input->get('type');
                                                $mode      = $this->input->get('mode');
                                                $mode_type = $this->input->get('mode_type');
                                                $this->session->set_userdata('type',      $type);
                                                $this->session->set_userdata('mode',      $mode);
                                                $this->session->set_userdata('mode_type', $mode_type);
                                                $sid      = $shipment_detail['shipment']->id;
                                                $waybill  = htmlspecialchars($shipment_detail['shipment']->waybill_number ?? $shipment_detail['shipment']->tracking_id);
                                                $recip    = $shipment_detail['recipient'] ?? null;
                                                $recip_email = '';
                                                if ($recip) {
                                                    $recip_email = $recip->email
                                                        ?? $recip->recipient_contact_person_email
                                                        ?? '';
                                                }
                                                ?>
                                                <div style="display:flex; flex-wrap:nowrap; gap:4px; align-items:center;">

                                                    <!-- View -->
                                                    <a href="<?php echo admin_url('courier_goshipping/shipments/waybill/' . $sid); ?>"
                                                       title="View Waybill"
                                                       style="display:inline-flex;align-items:center;gap:4px;
                                                              padding:5px 10px;font-size:11px;font-weight:600;
                                                              background:#1565c0;color:#fff;border-radius:4px;
                                                              text-decoration:none;white-space:nowrap;">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>

                                                    <!-- Courier Invoice -->
                                                    <a href="<?php echo admin_url('courier_goshipping/shipments/courier_invoice/' . $sid); ?>"
                                                       title="Open Courier Invoice"
                                                       style="display:inline-flex;align-items:center;gap:4px;
                                                              padding:5px 10px;font-size:11px;font-weight:600;
                                                              background:#2e7d32;color:#fff;border-radius:4px;
                                                              text-decoration:none;white-space:nowrap;">
                                                        <i class="fa fa-file-text-o"></i> Courier Invoice
                                                    </a>

                                                    <!-- Commercial Invoice -->
                                                    <?php if (!empty($shipment_detail['shipment']->commercial_invoice_file)): ?>
                                                        <a href="<?php echo base_url('uploads/courier/commercial_invoices/' . $shipment_detail['shipment']->commercial_invoice_file); ?>"
                                                           title="Download Commercial Invoice"
                                                           target="_blank"
                                                           style="display:inline-flex;align-items:center;gap:4px;
                                                                  padding:5px 10px;font-size:11px;font-weight:600;
                                                                  background:#e65100;color:#fff;border-radius:4px;
                                                                  text-decoration:none;white-space:nowrap;">
                                                            <i class="fa fa-download"></i> Commercial Invoice
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo admin_url('courier_goshipping/shipments/commercial_invoice/' . $sid); ?>"
                                                           title="Open Commercial Invoice"
                                                           style="display:inline-flex;align-items:center;gap:4px;
                                                                  padding:5px 10px;font-size:11px;font-weight:600;
                                                                  background:#e65100;color:#fff;border-radius:4px;
                                                                  text-decoration:none;white-space:nowrap;">
                                                            <i class="fa fa-file-text-o"></i> Commercial Invoice
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Confirm Portal Request (admin only, portal shipments without invoice) -->
                                                    <?php if ($is_portal_pending): ?>
                                                    <button type="button"
                                                            class="btn-confirm-portal"
                                                            data-id="<?php echo $sid; ?>"
                                                            data-waybill="<?php echo $waybill; ?>"
                                                            title="Review & Create Invoice + Waybill"
                                                            style="display:inline-flex;align-items:center;gap:4px;
                                                                   padding:5px 10px;font-size:11px;font-weight:600;
                                                                   background:#00796b;color:#fff;border-radius:4px;
                                                                   border:none;cursor:pointer;white-space:nowrap;">
                                                        <i class="fa fa-check-circle"></i> Confirm &amp; Invoice
                                                    </button>
                                                    <?php endif; ?>

                                                    <!-- Delete -->
                                                    <?php if (is_admin() || staff_can('delete_shipments', 'courier-shipments')): ?>
                                                    <form action="<?php echo admin_url('courier_goshipping/shipments/delete'); ?>"
                                                          method="post" style="margin:0;"
                                                          onsubmit="return confirm('Delete shipment <?php echo $waybill; ?> permanently?');">
                                                        <input type="hidden"
                                                               name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                                               value="<?php echo $this->security->get_csrf_hash(); ?>">
                                                        <input type="hidden" name="shipment_id" value="<?php echo $sid; ?>">
                                                        <button type="submit"
                                                                title="Delete Shipment"
                                                                style="display:inline-flex;align-items:center;gap:4px;
                                                                       padding:5px 10px;font-size:11px;font-weight:600;
                                                                       background:#c62828;color:#fff;border-radius:4px;
                                                                       border:none;cursor:pointer;white-space:nowrap;">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-footer">
                                    <tr>
                                        <th>Tracking ID</th>
                                        <th>Sender</th>
                                        <th>Recipient</th>
                                        <th>Assigned To</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                                </div>
                                <div class="shipment-table-hint">Scroll horizontally to see all shipment columns and actions.</div>
                            <?php else: ?>
                                <!-- Show a message when there's no data -->
                                <div class="text-center text-danger">
                                    <p>No available shipments</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <?php init_tail(); ?>
</div>

<!-- ── Send Waybill by Email (from list) ─────────────────────── -->
<div class="modal fade" id="list_send_waybill_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-envelope" style="color:#6a1b9a;"></i>
                    Send Waybill by Email — <span id="list_modal_waybill_num"></span>
                </h4>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:#555;margin-bottom:14px;">
                    An email with the shipment details and a tracking link will be sent.
                </p>
                <div class="form-group">
                    <label><strong>Recipient Email</strong></label>
                    <input type="email" id="list_modal_email" class="form-control"
                           placeholder="recipient@example.com">
                </div>
                <div id="list_modal_alert" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="list_modal_send_btn" class="btn btn-primary"
                        style="background:#6a1b9a;border-color:#6a1b9a;">
                    <i class="fa fa-paper-plane"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var _listEmailShipmentId = null;

function openListEmailModal(shipmentId, waybillNum, recipEmail) {
    _listEmailShipmentId = shipmentId;
    $('#list_modal_waybill_num').text(waybillNum);
    $('#list_modal_email').val(recipEmail || '');
    $('#list_modal_alert').hide().html('');
    $('#list_send_waybill_modal').modal('show');
}

$('#list_modal_send_btn').on('click', function () {
    var $btn  = $(this);
    var email = $('#list_modal_email').val().trim();

    if (!_listEmailShipmentId) return;

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending…');
    $('#list_modal_alert').hide();

    $.ajax({
        url: '<?php echo admin_url('courier_goshipping/shipments/send_waybill_email/'); ?>' + _listEmailShipmentId,
        type: 'POST',
        dataType: 'json',
        data: { email: email },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (resp) {
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
            if (resp.success) {
                $('#list_modal_alert')
                    .removeClass('alert-danger').addClass('alert alert-success')
                    .html('<i class="fa fa-check-circle"></i> ' + resp.message).show();
                setTimeout(function () { $('#list_send_waybill_modal').modal('hide'); }, 2500);
            } else {
                $('#list_modal_alert')
                    .removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fa fa-exclamation-circle"></i> ' + resp.message).show();
            }
        },
        error: function () {
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
            $('#list_modal_alert')
                .removeClass('alert-success').addClass('alert alert-danger')
                .html('<i class="fa fa-exclamation-circle"></i> Server error. Please try again.').show();
        }
    });
});
</script>

<!-- ── Confirm Portal Request Modal ─────────────────────────── -->
<div class="modal fade" id="confirmPortalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#00796b;color:#fff;border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirm Portal Request &amp; Create Invoice</h4>
            </div>
            <div class="modal-body" id="cpm-body">
                <div id="cpm-loading" style="text-align:center;padding:30px;color:#555;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Loading shipment details…
                </div>
                <div id="cpm-content" style="display:none;">
                    <!-- Shipment summary -->
                    <div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;padding:14px 18px;margin-bottom:16px;">
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Tracking Ref:</strong> <span id="cpm-tracking"></span><br>
                                <strong>Mode:</strong> <span id="cpm-mode"></span>
                            </div>
                            <div class="col-sm-6">
                                <strong>Sender:</strong> <span id="cpm-sender"></span><br>
                                <strong>Recipient:</strong> <span id="cpm-recipient"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Packages table -->
                    <h5 style="margin-bottom:8px;">Packages</h5>
                    <table class="table table-condensed table-bordered" id="cpm-pkg-table" style="font-size:13px;">
                        <thead style="background:#f5f5f5;">
                            <tr>
                                <th>Description</th>
                                <th style="width:70px;">Qty</th>
                                <th style="width:90px;">Weight (kg)</th>
                            </tr>
                        </thead>
                        <tbody id="cpm-pkg-rows"></tbody>
                    </table>

                    <!-- Pricing -->
                    <div class="row" style="margin-top:16px;">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><strong>Unit Price (per line item)</strong> <span style="color:red">*</span></label>
                                <input type="number" id="cpm-unit-price" class="form-control"
                                       placeholder="e.g. 600.00" min="0" step="0.01">
                                <small class="text-muted">Customer's quoted estimate: <strong id="cpm-quoted"></strong></small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><strong>Apply VAT?</strong></label>
                                <div>
                                    <label style="font-weight:normal;cursor:pointer;">
                                        <input type="checkbox" id="cpm-vat" value="1" checked> Apply VAT (<?php echo get_option('courier_parcel_vat_rate') ?: 16; ?>%)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commercial Invoice Section (Conditional) -->
                    <div id="cpm-commercial-invoice-section" style="display:none; margin-top:16px; border-top:1px solid #eee; padding-top:16px;">
                        <h5 style="margin-bottom:8px;">Commercial Invoice Information <small class="text-danger">(This information will be used to generate commercial Invoice*)</small></h5>
                        <table class="table table-bordered table-striped" id="cpm-commercialItemsTable">
                            <thead>
                            <tr>
                                <th>Quantity</th>
                                <th>Item Description</th>
                                <th>Declared Value</th>
                                <th style="width:50px;"></th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input name="cpm_commodity_quantity[]" class="form-control" type="number" step="any" required></td>
                                    <td><textarea name="cpm_commodity_description[]" class="form-control" rows="2" required></textarea></td>
                                    <td><input name="cpm_declared_value[]" class="form-control" type="number" step="any" required></td>
                                    <td><button type="button" class="btn btn-primary" onclick="addCpmCommercialItem()"><i class="fa fa-plus"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <script>
                            function addCpmCommercialItem() {
                                const table = document.getElementById('cpm-commercialItemsTable').getElementsByTagName('tbody')[0];
                                const row = table.insertRow();
                                row.innerHTML = `
                                    <td><input name="cpm_commodity_quantity[]" class="form-control" type="number" step="any" required></td>
                                    <td><textarea name="cpm_commodity_description[]" class="form-control" rows="2" required></textarea></td>
                                    <td><input name="cpm_declared_value[]" class="form-control" type="number" step="any" required></td>
                                    <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
                                `;
                            }
                        </script>
                    </div>

                    <div id="cpm-alert" style="display:none;" class="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="cpm-submit-btn" class="btn btn-success" style="background:#00796b;border-color:#00796b;">
                    <i class="fa fa-check"></i> Create Invoice &amp; Waybill
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var _confirmShipmentId = null;
    var _adminBase = '<?php echo admin_url('courier_goshipping/shipments'); ?>';

    // Open modal when "Confirm & Invoice" clicked
    $(document).on('click', '.btn-confirm-portal', function () {
        _confirmShipmentId = $(this).data('id');
        $('#cpm-loading').show();
        $('#cpm-content').hide();
        $('#cpm-alert').hide();
        $('#cpm-submit-btn').prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice &amp; Waybill');
        $('#confirmPortalModal').modal('show');

        $.getJSON(_adminBase + '/portal_request_data/' + _confirmShipmentId, function (r) {
            $('#cpm-loading').hide();
            if (r.status !== 'success') {
                $('#cpm-content').show();
                $('#cpm-alert').addClass('alert-danger').text(r.message || 'Error loading data.').show();
                return;
            }
            var s = r.shipment, sender = r.sender || {}, recip = r.recipient || {};
            var senderName = ((sender.first_name || '') + ' ' + (sender.last_name || '')).trim() || '—';
            var recipName  = ((recip.first_name  || '') + ' ' + (recip.last_name  || '')).trim() || '—';

            $('#cpm-tracking').text(s.tracking_id);
            $('#cpm-mode').text(s.shipping_mode);
            $('#cpm-sender').text(senderName + (sender.phone_number ? ' · ' + sender.phone_number : ''));
            $('#cpm-recipient').text(recipName + (recip.phone_number ? ' · ' + recip.phone_number : ''));

            var quoted = parseFloat(s.quoted_amount) || 0;
            $('#cpm-quoted').text(quoted > 0 ? quoted.toLocaleString('en-KE', {minimumFractionDigits:2}) : 'No estimate provided');
            if (quoted > 0) {
                $('#cpm-unit-price').val(quoted.toFixed(2));
            }

            var $tbody = $('#cpm-pkg-rows').empty();
            if (r.packages && r.packages.length) {
                $.each(r.packages, function (i, p) {
                    $tbody.append('<tr><td>' + (p.description || '—') + '</td><td>' + (p.quantity || 1) + '</td><td>' + (p.weight || '—') + '</td></tr>');
                });
            } else {
                $tbody.append('<tr><td colspan="3" style="color:#999;text-align:center;">No package records — enter total price below.</td></tr>');
            }

            // Show Commercial Invoice Form if no file was uploaded
            if (!s.commercial_invoice_file || s.commercial_invoice_file.trim() === '') {
                $('#cpm-commercial-invoice-section').show();
            } else {
                $('#cpm-commercial-invoice-section').hide();
            }

            $('#cpm-content').show();
        }).fail(function () {
            $('#cpm-loading').hide();
            $('#cpm-content').show();
            $('#cpm-alert').addClass('alert-danger').text('Network error loading shipment data.').show();
        });
    });

    // Submit confirmation
    $('#cpm-submit-btn').on('click', function () {
        if (!_confirmShipmentId) return;
        var unitPrice = parseFloat($('#cpm-unit-price').val());
        if (!unitPrice || unitPrice <= 0) {
            $('#cpm-alert').removeClass('alert-success').addClass('alert alert-danger')
                .text('Please enter a valid unit price.').show();
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating…');
        $('#cpm-alert').hide();

        var payload = {
            unit_price: unitPrice,
            apply_vat:  $('#cpm-vat').is(':checked') ? 1 : 0,
            commodity_quantity: $('input[name="cpm_commodity_quantity[]"]').map(function(){ return $(this).val(); }).get(),
            commodity_description: $('textarea[name="cpm_commodity_description[]"]').map(function(){ return $(this).val(); }).get(),
            declared_value: $('input[name="cpm_declared_value[]"]').map(function(){ return $(this).val(); }).get(),
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>',
        };

        $.ajax({
            url:      _adminBase + '/confirm_portal_request/' + _confirmShipmentId,
            type:     'POST',
            dataType: 'json',
            data: payload,
            success: function (r) {
                if (r.status === 'success') {
                    $('#cpm-alert').removeClass('alert-danger').addClass('alert alert-success')
                        .html('<i class="fa fa-check-circle"></i> Waybill <strong>' + r.waybill_number + '</strong> and invoice created. Reloading…').show();
                    $btn.html('<i class="fa fa-check"></i> Done!');
                    setTimeout(function () { location.reload(); }, 2000);
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice &amp; Waybill');
                    $('#cpm-alert').removeClass('alert-success').addClass('alert alert-danger')
                        .text(r.message || 'Error creating invoice.').show();
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice &amp; Waybill');
                $('#cpm-alert').removeClass('alert-success').addClass('alert alert-danger')
                    .text('Network error. Please try again.').show();
            }
        });
    });
})();
</script>

<div class="modal fade" id="generateManifestModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('courier_goshipping/shipments/generate_manifest'), ['id' => 'generate-manifest-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Generate Manifest</h4>
            </div>
            <div class="modal-body">
                <?php if ($this->session->flashdata('form_errors')): ?>
                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('form_errors'); ?>
                    </div>
                <?php endif; ?>
                <div style="padding-left:20px; padding-right:20px;" class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="dateRange">Select Date Range</label>
                            <input type="text" class="form-control" id="dateRange" name="dateRange"
                                   placeholder="Select date range" required>
                            <?php if (form_error('dateRange')): ?>
                                <div class="error"><?= form_error('company_name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="country_id">Recipient Country</label>
                            <?php
                            // Add "Select All" as the first option with an empty string as the value
                            $country_options = ['' => 'All'] + array_column($countries, 'short_name', 'country_id');

                            echo form_dropdown(
                                'country_id',
                                $country_options,
                                set_value('country_id'),
                                [
                                    'id' => 'country_id',
                                    'class' => 'custom-select',
                                    'style' => 'width:100%',
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
                <div style="border-bottom:0px; margin-top:20px; margin-bottom:-20px; padding-bottom:0px; border-left:0px; border-right:0px; border-radius:0px;"
                     class="row section-container">
                    <div class="section-label">Destination Office</div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_name" class="custom-label">Company Name</label>
                                <?php echo form_input(['id' => 'company_name', 'name' => 'company_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Company Name', 'value' => set_value('company_name')]); ?>
                                <?php if ($this->session->flashdata('company_name')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_name_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="location" class="custom-label">Location</label>
                                <?php echo form_input(['id' => 'location', 'name' => 'location', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Location', 'value' => set_value('location')]); ?>
                                <?php if ($this->session->flashdata('location')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('location_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="address" class="custom-label">Street Address</label>
                                <?php echo form_input(['id' => 'street_address', 'name' => 'street_address', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Address', 'value' => set_value('street_address')]); ?>
                                <?php if ($this->session->flashdata('street_address')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('street_address_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="landmark" class="custom-label">LandMark</label>
                                <?php echo form_input(['id' => 'landmark', 'name' => 'landmark', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'landmark', 'value' => set_value('landmark')]); ?>
                                <?php if ($this->session->flashdata('landmark')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('landmark_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_phone" class="custom-label">Phone Number</label>
                                <?php echo form_input(['id' => 'phone_number', 'name' => 'phone_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'phone_number', 'value' => set_value('phone_number')]); ?>
                                <?php if ($this->session->flashdata('phone_number')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('phone_number_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs to carry URL parameters -->
                <input type="hidden" id="shipment_type" name="shipment_type" value="">
                <input type="hidden" id="shipment_mode" name="shipment_mode" value="">
                <input type="hidden" id="shipment_mode_type" name="shipment_mode_type" value="">
                <input type="hidden" name="form_submitted" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">close</button>
                <button type="submit" class="btn btn-primary"
                        id="generateManifestBtn">Generate Manifest</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Generate Payment Modal ──────────────────────────────────── -->
<div class="modal fade" id="shipmentPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1565c0; color:#fff; border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-credit-card"></i>
                    Record Payment &mdash; <span id="spm_waybill"></span>
                </h4>
            </div>
            <div class="modal-body">

                <!-- Amount -->
                <div class="form-group">
                    <label><strong>Amount</strong></label>
                    <input type="number" class="form-control" id="spm_amount" min="0.01" step="0.01" placeholder="0.00">
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label><strong>Payment Date</strong></label>
                    <input type="date" class="form-control" id="spm_date" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Tabs: Offline / Online -->
                <div style="margin-bottom:6px;"><strong>Payment Type</strong></div>
                <ul class="nav nav-tabs" style="margin-bottom:14px;">
                    <li class="active"><a href="#spm_tab_offline" data-toggle="tab" onclick="spm_tab='offline'">Offline / Cash</a></li>
                    <li><a href="#spm_tab_online" data-toggle="tab" onclick="spm_tab='online'">Online Gateway</a></li>
                </ul>
                <div class="tab-content">

                    <!-- Offline modes -->
                    <div class="tab-pane active" id="spm_tab_offline">
                        <div class="form-group">
                            <label><strong>Payment Mode</strong></label>
                            <select class="form-control" id="spm_offlineMode">
                                <?php if (!empty($offline_modes)): ?>
                                    <?php foreach ($offline_modes as $m): ?>
                                        <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Cash</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Online gateways -->
                    <div class="tab-pane" id="spm_tab_online">
                        <?php if (!empty($online_gateways)): ?>
                            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:10px; margin-bottom:10px;">
                                <?php foreach ($online_gateways as $gw): ?>
                                <div class="spm-gw-card"
                                     data-id="<?php echo htmlspecialchars($gw['id']); ?>"
                                     onclick="selectSpmGateway(this)"
                                     style="border:2px solid #ddd; border-radius:6px; padding:12px 8px; text-align:center;
                                            cursor:pointer; font-size:12px; font-weight:600; color:#444;">
                                    <i class="fa fa-globe" style="font-size:18px; display:block; margin-bottom:4px;"></i>
                                    <?php echo htmlspecialchars($gw['name']); ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="spm_pesapalPhone" style="display:none;">
                                <div class="form-group">
                                    <label><i class="fa fa-phone"></i> <strong>Phone Number (M-Pesa STK Push)</strong></label>
                                    <input type="tel" class="form-control" id="spm_phone" placeholder="e.g. 0712345678">
                                    <small class="text-muted">The customer will receive a payment prompt on this number.</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="font-size:13px; margin-top:10px;">No active payment gateways configured. Go to Setup &rarr; Settings &rarr; Payment Gateways.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reference -->
                <div class="form-group">
                    <label><strong>Reference / Note</strong> <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control" id="spm_note" placeholder="Transaction ref, cheque no, etc.">
                </div>

                <div id="spm_error" class="alert alert-danger" style="display:none;"></div>
                <div id="spm_success" class="alert alert-success" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="spm_submitBtn"
                        style="background:#1565c0; border-color:#1565c0;"
                        onclick="submitSpmPayment()">
                    <i class="fa fa-check"></i> Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Thermal Receipt Modal ──────────────────────────────────── -->
<div class="modal fade" id="shipmentReceiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-print"></i> Payment Receipt</h4>
            </div>
            <div class="modal-body" style="padding:10px;">
                <div id="spm_receiptPrint" style="font-family:'Courier New',monospace; font-size:12px; background:#fff; padding:12px; border:1px dashed #ccc; border-radius:4px;">
                    <div style="text-align:center;">
                        <strong style="font-size:14px;"><?php echo htmlspecialchars(get_option('companyname') ?: 'Our Company'); ?></strong><br>
                        <span style="font-size:10px; color:#555;">PAYMENT RECEIPT</span>
                    </div>
                    <hr style="border-top:1px dashed #888; margin:6px 0;">
                    <div style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Receipt #:</span><span id="spm_rcNum">-</span></div>
                    <div style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Date:</span><span id="spm_rcDate">-</span></div>
                    <hr style="border-top:1px dashed #888; margin:6px 0;">
                    <div style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Waybill:</span><span id="spm_rcWaybill">-</span></div>
                    <hr style="border-top:1px dashed #888; margin:6px 0;">
                    <div style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Amount Paid:</span><span id="spm_rcAmount">-</span></div>
                    <div style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Mode:</span><span id="spm_rcMode">-</span></div>
                    <div id="spm_rcBalanceRow" style="display:flex;justify-content:space-between;margin:2px 0;"><span style="color:#555;">Balance:</span><span id="spm_rcBalance">-</span></div>
                    <div id="spm_rcPaidFull" style="display:none; text-align:center; font-size:16px; font-weight:900; letter-spacing:3px; color:#1a7f1a; border:2px solid #1a7f1a; padding:4px; margin:8px 0; border-radius:3px;">** PAID IN FULL **</div>
                    <hr style="border-top:1px dashed #888; margin:6px 0;">
                    <div style="text-align:center; font-size:10px; color:#777;">Thank you for your business!</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="printSpmReceipt()"><i class="fa fa-print"></i> Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
var spm_shipmentId = null;
var spm_tab        = 'offline';
var spm_gateway    = null;
var spm_payUrl     = '<?php echo admin_url('courier_goshipping/shipments/record_courier_payment/'); ?>';

function openPaymentModal(sid, waybill) {
    spm_shipmentId = sid;
    spm_gateway    = null;
    spm_tab        = 'offline';
    $('#spm_waybill').text(waybill);
    $('#spm_error').hide();
    $('#spm_success').hide();
    $('#spm_note').val('');
    $('#spm_date').val('<?php echo date('Y-m-d'); ?>');
    $('.spm-gw-card').css({'border-color':'#ddd','background':'','color':'#444'});
    $('#spm_pesapalPhone').hide();
    $('#spm_submitBtn').html('<i class="fa fa-check"></i> Record Payment').prop('disabled', false);
    // Reset to offline tab
    $('.nav-tabs a[href="#spm_tab_offline"]').tab('show');
    $('#shipmentPaymentModal').modal('show');
}

function selectSpmGateway(card) {
    $('.spm-gw-card').css({'border-color':'#ddd','background':'','color':'#444'});
    $(card).css({'border-color':'#1565c0','background':'#e3f0ff','color':'#1565c0'});
    spm_gateway = card.dataset.id;
    $('#spm_pesapalPhone').toggle(spm_gateway === 'pesapal');
    var label = (spm_gateway === 'pesapal') ? 'Send STK Push / Pay' : 'Pay Online';
    $('#spm_submitBtn').html('<i class="fa fa-arrow-right"></i> ' + label);
}

function submitSpmPayment() {
    $('#spm_error').hide();
    var amount = parseFloat($('#spm_amount').val());
    var date   = $('#spm_date').val();
    var note   = $('#spm_note').val();
    if (isNaN(amount) || amount <= 0) { $('#spm_error').text('Please enter a valid amount.').show(); return; }
    if (!date) { $('#spm_error').text('Please select a payment date.').show(); return; }
    var paymode;
    if (spm_tab === 'offline') {
        paymode = $('#spm_offlineMode').val();
    } else {
        if (!spm_gateway) { $('#spm_error').text('Please select a payment gateway.').show(); return; }
        paymode = spm_gateway;
    }
    var btn = $('#spm_submitBtn');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    $.ajax({
        url: spm_payUrl + spm_shipmentId,
        type: 'POST',
        dataType: 'json',
        data: { amount: amount, paymentmode: paymode, payment_date: date, note: note },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Record Payment');
            if (res.redirect) { window.location.href = res.redirect; return; }
            if (!res.success) { $('#spm_error').text(res.message || 'Payment failed.').show(); return; }
            $('#shipmentPaymentModal').modal('hide');
            showSpmReceipt(res);
        },
        error: function() {
            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Record Payment');
            $('#spm_error').text('Network error. Please try again.').show();
        }
    });
}

function showSpmReceipt(res) {
    $('#spm_rcNum').text('PMT-' + String(res.payment_id).padStart(6,'0'));
    $('#spm_rcDate').text(res.payment_date || '-');
    $('#spm_rcWaybill').text(res.waybill || '-');
    $('#spm_rcAmount').text(res.amount_paid || '-');
    $('#spm_rcMode').text(res.payment_mode_name || '-');
    var bal = parseFloat(res.balance);
    if (bal <= 0) {
        $('#spm_rcBalanceRow').hide();
        $('#spm_rcPaidFull').show();
    } else {
        $('#spm_rcBalance').text(res.balance);
        $('#spm_rcBalanceRow').show();
        $('#spm_rcPaidFull').hide();
    }
    $('#shipmentReceiptModal').modal('show');
}

function printSpmReceipt() {
    var content = document.getElementById('spm_receiptPrint').innerHTML;
    var w = window.open('', '_blank', 'width=350,height=600');
    w.document.write('<html><head><title>Receipt</title><style>body{font-family:"Courier New",monospace;font-size:12px;padding:12px;}</style></head><body>' + content + '</body></html>');
    w.document.close();
    w.focus();
    w.print();
    w.close();
}
</script>

<!-- Scripts should be at the end of the body -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Add necessary scripts for the modal -->
<script>
    $(document).ready(function () {

        <?php if ($this->session->flashdata('show_modal')): ?>
        $('#generateManifestModal').modal('show');
        <?php endif; ?>

        $('#status_id').select2({});

        $('#staff_id').select2({});

        $('#country_id').select2({
            dropdownParent: $('#generateManifestModal') // Replace #yourModalID with the ID of your modal
        });


        // Array of dates to be disabled (already used dates)
        const disabledDates = [];

        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            disable: disabledDates,
        });

        flatpickr("#filterDateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            disable: disabledDates,
        });

        // Extract query parameters from the current URL
        const urlParams = new URLSearchParams(window.location.search);

        document.getElementById('shipment_type').value = urlParams.get('type');
        document.getElementById('shipment_mode').value = urlParams.get('mode');
        document.getElementById('shipment_mode_type').value = urlParams.get('mode_type');

        document.getElementById('clearFilters').addEventListener('click', function () {
            // Clear the input fields
            document.getElementById('filterDateRange').value = '';
            document.getElementById('status_id').selectedIndex = 0;

            // Get CSRF token
            const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>'; // CSRF Token name
            const csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>'; // CSRF hash

            // AJAX request to clear the filters
            $.ajax({
                url: '<?php echo admin_url("courier_goshipping/shipments/clear_filters"); ?>',
                type: "POST",
                data: {
                    [csrfName]: csrfHash, // Send the CSRF token with the request
                },
                dataType: "json",
                success: function (data) {
                    console.log('Clear filter response:', data);
                    window.location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Log detailed error information
                    console.error('Error clearing filters:', jqXHR.responseText);
                    console.error('Text Status:', textStatus);
                    console.error('Error Thrown:', errorThrown);
                    alert('Error clearing filters. Check the console for more details.');
                }
            });
        });



    });
</script>
