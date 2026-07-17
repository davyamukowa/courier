<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'pickups']); ?>
<?php echo '<script src="https://cdn.jsdelivr.net/npm/signature_pad"></script>'; ?>

<style>
    /* Excel-style grid classes for the table */
    .excel-grid {
        border-collapse: collapse !important;
        border: 1px solid #ccc !important;
    }
    .excel-grid th, .excel-grid td {
        border: 1px solid #ccc !important;
        padding: 6px 10px !important;
        vertical-align: middle !important;
    }
    .excel-grid thead th {
        background-color: #f3f3f3 !important;
        color: #333 !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #bbb !important;
    }
    .excel-grid tbody tr:hover {
        background-color: #f1f8ff !important;
    }
    .excel-grid tbody tr {
        cursor: pointer;
    }
    
    /* Always visible, stylized horizontal scrollbar */
    .table-responsive {
        overflow-x: auto !important;
        padding-bottom: 15px; /* room for scrollbar */
    }
    .table-responsive::-webkit-scrollbar {
        height: 12px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f4f4f4; 
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #999; 
        border-radius: 8px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #666; 
    }
</style>

        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">
                        <div class="cgs-card__header">
                            <h4 class="cgs-card__title"><i class="fa fa-truck"></i> GO Shipping Cargo — Pickups</h4>
                            <div class="cgs-card__actions">
                                <a class="cgs-btn cgs-btn--outline cgs-btn--sm"
                                   href="<?php echo admin_url('courier_goshipping/pickups/main'); ?>">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Pickup Dashboard
                                </a>
                                <a class="cgs-btn cgs-btn--primary cgs-btn--sm"
                                   href="<?php echo admin_url('courier_goshipping/pickups/create'); ?>">
                                    <i class="fa fa-plus"></i> Create Pickup
                                </a>

                                <?php if (!empty($pickups)): ?>
                                <button id="btn-select-all" class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                    <i class="fa fa-check-square-o"></i> Select All
                                </button>
                                <button id="btn-delete-selected" class="cgs-btn cgs-btn--accent cgs-btn--sm" style="display:none;" disabled>
                                    <i class="fa fa-trash"></i> Delete Selected (<span id="selected-count">0</span>)
                                </button>
                                <button id="btn-delete-all" class="cgs-btn cgs-btn--accent cgs-btn--sm">
                                    <i class="fa fa-trash"></i> Delete All
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Analytics summary bar -->
                        <?php if (!empty($pickups)):
                            $pk_total = count($pickups);
                            $pk_by_st = ['pending'=>0,'picked_up'=>0,'delivered'=>0,'cancelled'=>0];
                            $portal_count = 0;
                            foreach ($pickups as $pk) { 
                                $s = $pk->status ?? 'pending'; 
                                $pk_by_st[$s] = ($pk_by_st[$s] ?? 0) + 1; 
                                if (($pk->source ?? 'system') === 'portal') {
                                    $portal_count++;
                                }
                            }
                            $pk_labels = ['pending'=>'Pending','picked_up'=>'Picked Up','delivered'=>'Delivered','cancelled'=>'Cancelled'];
                            $pk_colors = ['pending'=>'#757575','picked_up'=>'#1565c0','delivered'=>'#2e7d32','cancelled'=>'#c62828'];
                        ?>
                        <div style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:10px 14px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                            <span style="font-weight:700;font-size:13px;color:#333;margin-right:4px;"><i class="fa fa-bar-chart" style="color:#1976d2;margin-right:4px;"></i>Pickups (<?php echo $pk_total; ?>):</span>
                            <?php foreach ($pk_labels as $slug => $lbl): if ($pk_by_st[$slug] > 0): ?>
                            <span style="display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid <?php echo $pk_colors[$slug]; ?>;border-radius:20px;padding:3px 10px;font-size:12px;font-weight:600;color:<?php echo $pk_colors[$slug]; ?>;">
                                <?php echo $lbl; ?> <span style="background:<?php echo $pk_colors[$slug]; ?>;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;"><?php echo $pk_by_st[$slug]; ?></span>
                            </span>
                            <?php endif; endforeach; ?>
                            
                            <!-- Portal Analytics and Filter -->
                            <span style="border-left: 2px solid #ccc; height: 20px; margin: 0 5px;"></span>
                            <span style="font-weight:700;font-size:13px;color:#333;">Portal Requests (<?php echo $portal_count; ?>)</span>
                            <button id="btn-filter-portal" class="cgs-btn cgs-btn--primary cgs-btn--sm" style="border-radius:20px;">
                                <i class="fa fa-filter"></i> Show Portal Only
                            </button>
                            <button id="btn-clear-filter" class="cgs-btn cgs-btn--outline cgs-btn--sm" style="border-radius:20px; display:none;">
                                <i class="fa fa-times"></i> Clear Filter
                            </button>
                        </div>
                        <?php endif; ?>

                        <!-- Check if there is data -->
                        <?php if (!empty($pickups)): ?>
                            <div class="table-responsive">
                            <table class="table dt-table table-bordered table-hover excel-grid cgs-table" id="pickups-table" data-order='[[ 1, "desc" ]]'>
                                <thead class="table-head">
                                <tr>
                                    <th style="width:36px;" data-orderable="false"><input type="checkbox" id="chk-all" title="Select all on this page"></th>
                                    <th>ID</th>
                                    <th>Created At</th>
                                    <th>Pickup Date</th>
                                    <th>Time Window</th>
                                    <th>Vehicle Type</th>
                                    <th>Contact Name</th>
                                    <th>Contact Email</th>
                                    <th>Contact Phone</th>
                                    <th>Country / State</th>
                                    <th>Address</th>
                                    <th>ZIP</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Pickup Signature</th>
                                    <th>Delivery Signature</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pickups as $pickup): ?>
                                    <tr>
                                        <td><input type="checkbox" class="pickup-chk" value="<?php echo $pickup->id; ?>"></td>
                                        <td><?php echo $pickup->id; ?></td>
                                        <td><?php echo htmlspecialchars($pickup->created_at ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($pickup->pickup_date ?? ''); ?></td>
                                        <td style="white-space: nowrap;">
                                            <?php echo htmlspecialchars($pickup->pickup_start_time ?? ''); ?> - <?php echo htmlspecialchars($pickup->pickup_end_time ?? ''); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($pickup->vehicle_type ?? ''); ?></td>
                                        <td><span style="font-weight:bold;"><?php echo htmlspecialchars($pickup->contact_first_name ?? '') . ' ' . htmlspecialchars($pickup->contact_last_name ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($pickup->contact_email ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($pickup->contact_phone_number ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($pickup->country_id ?? '') . ' / ' . htmlspecialchars($pickup->state_id ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($pickup->address ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($pickup->pickup_zip ?? ''); ?></td>
                                        <td>
                                            <?php
                                            $src_badge = ['portal'=>'info', 'shipment'=>'primary', 'system'=>'default'];
                                            $src_cls   = $src_badge[$pickup->source ?? 'system'] ?? 'default';
                                            ?>
                                            <span class="label label-<?php echo $src_cls; ?> source-badge"><?php echo htmlspecialchars(ucfirst($pickup->source ?? 'System')); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $pk_badge = ['pending'=>'default','picked_up'=>'info','delivered'=>'success','cancelled'=>'danger'];
                                            $pk_cls   = $pk_badge[$pickup->status] ?? 'default';
                                            ?>
                                            <span class="label label-<?php echo $pk_cls; ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $pickup->status))); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($pickup->signature_url)): ?>
                                                <img width="40" height="40"
                                                     src="<?php echo base_url('modules/courier_goshipping/' . $pickup->signature_url); ?>"
                                                     alt="pickup signature" style="border:1px solid #ccc; border-radius:4px;">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($pickup->delivery_signature_url)): ?>
                                                <img width="40" height="40"
                                                     src="<?php echo base_url('modules/courier_goshipping/' . $pickup->delivery_signature_url); ?>"
                                                     alt="delivery signature" style="border:1px solid #ccc; border-radius:4px;">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-left" style="white-space: nowrap;">
                                            <div class="d-flex flex-row justify-content-center" style="gap:4px;">
                                                <a href="#"
                                                   data-toggle="modal" data-target="#update_status"
                                                   data-id="<?php echo $pickup->id; ?>"
                                                   data-status="<?php echo htmlspecialchars($pickup->status); ?>"
                                                   class="update-status-btn cgs-btn cgs-btn--outline cgs-btn--sm"
                                                   title="Update Status">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?php echo admin_url('courier_goshipping/pickups/view/' . $pickup->id); ?>"
                                                   class="cgs-btn cgs-btn--primary cgs-btn--sm" title="View Pickup">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="<?php echo admin_url('courier_goshipping/pickups/delete/' . $pickup->id); ?>"
                                                   class="cgs-btn cgs-btn--accent cgs-btn--sm btn-delete-single"
                                                   data-id="<?php echo $pickup->id; ?>" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-footer">
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Created At</th>
                                    <th>Pickup Date</th>
                                    <th>Time Window</th>
                                    <th>Vehicle Type</th>
                                    <th>Contact Name</th>
                                    <th>Contact Email</th>
                                    <th>Contact Phone</th>
                                    <th>Country / State</th>
                                    <th>Address</th>
                                    <th>ZIP</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Pickup Signature</th>
                                    <th>Delivery Signature</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-danger">
                                <p>No available pickups.</p>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="update_status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/courier_goshipping/pickups/update_status/', ['id' => 'update-pickup-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Update Status</h4>
                <input type="hidden" value="" name="pickup_id">
                <input type="hidden" value="" name="signature">
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <select id="status" name="status"
                                class="custom-select">
                            <option value="picked_up">Picked Up</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div id="signatureCanvas" style="display:none;" class="col-md-12">
                        <canvas height="150" id="signature"
                                style="margin-top:10px;  border: 1px solid #ddd;"></canvas>
                        <br>
                        <button style="margin-top:10px;" id="clear-signature" class="cgs-btn cgs-btn--outline cgs-btn--sm">Clear Signature
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cgs-btn cgs-btn--outline" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="cgs-btn cgs-btn--primary"><?php echo _l('update status'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        $('.update-status-btn').on('click', function () {

            let pickupId = $(this).data('id');
            let currentStatus = $(this).data('status');

            $('input[name="pickup_id"]').val(pickupId);

            // Get the status select element
            let statusSelect = $('#status');

            // Clear all options first
            statusSelect.empty();

            // Build available next-statuses based on current status
            if (currentStatus === 'pending') {
                statusSelect.append(new Option('Picked Up', 'picked_up'));
                statusSelect.append(new Option('Cancelled', 'cancelled'));
            } else if (currentStatus === 'picked_up') {
                statusSelect.append(new Option('Delivered', 'delivered'));
                statusSelect.append(new Option('Cancelled', 'cancelled'));
            } else {
                // delivered / cancelled — only allow cancellation reversal or leave as-is
                statusSelect.append(new Option('Cancelled', 'cancelled'));
            }

            toggleSignatureCanvas();

        });

        // Reference to the status select and signature canvas container
        let statusSelect = document.getElementById('status');
        let signatureCanvas = document.getElementById('signatureCanvas');

        // Signature only required for 'picked_up'
        window.toggleSignatureCanvas = function () {
            signatureCanvas.style.display = (statusSelect.value === 'picked_up' || statusSelect.value === 'delivered') ? 'block' : 'none';
        }


        // Event listener for changes in the select dropdown
        statusSelect.addEventListener('change', toggleSignatureCanvas);

        let canvas = document.getElementById("signature");
        const signaturePad = new SignaturePad(canvas);

        $('#clear-signature').on('click', function (event) {
            event.preventDefault()
            signaturePad.clear();
        });

        document.getElementById('update-pickup-form').addEventListener('submit', function (e) {
            canvas = document.getElementById('signature');
            document.querySelector('input[name="signature"]').value = canvas.toDataURL('image/png');
        });

    });

    /* ---- Bulk Delete & Individual Delete ---- */
    $(document).ready(function () {
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        var bulkUrl  = '<?php echo admin_url("courier_goshipping/pickups/bulk_delete"); ?>';

        // Portal filter logic
        $('#btn-filter-portal').on('click', function() {
            var dt = $('#pickups-table').DataTable();
            // The Source column is index 12
            dt.column(12).search('Portal').draw();
            $(this).hide();
            $('#btn-clear-filter').show();
        });

        $('#btn-clear-filter').on('click', function() {
            var dt = $('#pickups-table').DataTable();
            dt.column(12).search('').draw();
            $(this).hide();
            $('#btn-filter-portal').show();
        });

        // Make entire row clickable to open the pickup view
        $('#pickups-table').on('click', 'tbody tr', function(e) {
            // Ignore click if it's on a checkbox, button, or link
            if ($(e.target).closest('input[type="checkbox"], a, button').length > 0) {
                return;
            }
            var id = $(this).find('.pickup-chk').val();
            if (id) {
                window.location.href = '<?php echo admin_url("courier_goshipping/pickups/view/"); ?>' + id;
            }
        });

        if ($('#chk-all').length === 0) { return; } // no pickups rendered

        function getChecked() {
            return $('.pickup-chk:checked').map(function(){ return $(this).val(); }).get();
        }

        function updateToolbar() {
            var ids = getChecked();
            if (ids.length > 0) {
                $('#btn-delete-selected').show().prop('disabled', false);
                $('#selected-count').text(ids.length);
            } else {
                $('#btn-delete-selected').hide().prop('disabled', true);
                $('#selected-count').text('0');
            }
        }

        // Header checkbox — select/deselect all rows (works across DataTables pages)
        $(document).on('change', '#chk-all', function () {
            $('.pickup-chk').prop('checked', this.checked);
            updateToolbar();
        });

        // Per-row checkbox — update header state and toolbar
        $(document).on('change', '.pickup-chk', function () {
            var total   = $('.pickup-chk').length;
            var checked = $('.pickup-chk:checked').length;
            $('#chk-all').prop('checked', total === checked);
            updateToolbar();
        });

        // "Select All" toolbar button — toggle all
        $('#btn-select-all').on('click', function () {
            var allChecked = ($('.pickup-chk').length === $('.pickup-chk:checked').length);
            $('.pickup-chk').prop('checked', !allChecked);
            $('#chk-all').prop('checked', !allChecked);
            updateToolbar();
        });

        function doBulkDelete(ids, confirmMsg) {
            if (!ids.length) { alert('No pickups selected.'); return; }
            if (!confirm(confirmMsg)) return;

            // jQuery $.ajax automatically sends X-Requested-With: XMLHttpRequest
            // so CI3's is_ajax_request() returns true.
            var postData = { ids: ids };
            postData[csrfName] = csrfHash;

            $.ajax({
                url:  bulkUrl,
                type: 'POST',
                data: postData,
                success: function (res) {
                    if (res.success) {
                        alert('Deleted ' + res.deleted + ' pickup(s).');
                        location.reload();
                    } else {
                        alert('Error: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function () {
                    alert('Request failed. Please try again.');
                }
            });
        }

        $('#btn-delete-selected').on('click', function () {
            doBulkDelete(getChecked(), 'Delete the selected pickup(s)? This cannot be undone.');
        });

        $('#btn-delete-all').on('click', function () {
            var all = $('.pickup-chk').map(function(){ return $(this).val(); }).get();
            doBulkDelete(all, 'Delete ALL ' + all.length + ' pickup(s)? This cannot be undone.');
        });

        // Per-row individual delete — confirmation before redirect
        $(document).on('click', '.btn-delete-single', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (confirm('Delete this pickup? This cannot be undone.')) {
                location.href = href;
            }
        });
    });
</script>

