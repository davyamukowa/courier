<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_logistic/layout/_topnav', ['cgs_active' => 'shipments']); ?>

<style>
    #ilb-items-table th, #ilb-items-table td { vertical-align: middle; }
    #ilb-items-table input { width: 100%; }
    .ilb-remove-row { cursor: pointer; color: #b71c1c; }
</style>

        <div class="row">
            <div class="col-md-12">
                <div class="cgs-card">

                    <div class="cgs-card__header">
                        <h4 class="cgs-card__title">
                            <i class="fa fa-clipboard-list"></i>
                            <?php echo htmlspecialchars($title); ?>
                        </h4>
                        <div class="cgs-card__actions">
                            <a href="<?php echo admin_url('courier_logistic/inventory_log'); ?>"
                               class="cgs-btn cgs-btn--outline cgs-btn--sm">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <?php echo form_open(admin_url('courier_logistic/inventory_log/store'), ['id' => 'ilb-form']); ?>
                    <?php echo form_hidden('id', $log['id'] ?? ''); ?>

                    <div class="row" style="padding:15px;">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" class="form-control"
                                       value="<?php echo htmlspecialchars($log['company_name'] ?? get_option('companyname')); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" name="log_date" id="ilb_log_date" class="form-control"
                                       value="<?php echo !empty($log['log_date']) ? date('Y-m-d', strtotime($log['log_date'])) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Time</label>
                                <input type="text" name="log_time" id="ilb_log_time" class="form-control"
                                       value="<?php echo !empty($log['log_time']) ? date('h:i', strtotime($log['log_time'])) : date('h:i'); ?>">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>AM/PM</label>
                                <select name="am_pm" class="form-control">
                                    <?php $am_pm = $log['am_pm'] ?? date('A'); ?>
                                    <option value="AM" <?php echo $am_pm === 'AM' ? 'selected' : ''; ?>>AM</option>
                                    <option value="PM" <?php echo $am_pm === 'PM' ? 'selected' : ''; ?>>PM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Counted By</label>
                                <input type="text" name="counted_by" class="form-control"
                                       value="<?php echo htmlspecialchars($log['counted_by'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Sheet#</label>
                                <input type="text" name="sheet_number" class="form-control"
                                       value="<?php echo htmlspecialchars($log['sheet_number'] ?? ('SH-' . date('ymd') . '-' . rand(100, 999))); ?>">
                            </div>
                        </div>
                    </div>

                    <div style="padding:0 15px;">
                        <table class="table table-bordered" id="ilb-items-table">
                            <thead>
                                <tr style="background:#eef1f4;">
                                    <th style="width:18%;">Item# / SKU</th>
                                    <th style="width:32%;">Description</th>
                                    <th style="width:10%;">Qty</th>
                                    <th style="width:20%;">Location</th>
                                    <th style="width:16%;">Notes</th>
                                    <th style="width:4%;"></th>
                                </tr>
                            </thead>
                            <tbody id="ilb-items-body">
                            <?php
                            $rows = !empty($items) ? $items : [['item_sku' => '', 'description' => '', 'qty' => '', 'location' => '', 'notes' => '']];
                            foreach ($rows as $row):
                            ?>
                                <tr>
                                    <td><input type="text" name="item_sku[]" class="form-control" value="<?php echo htmlspecialchars($row['item_sku'] ?? ''); ?>"></td>
                                    <td><input type="text" name="description[]" class="form-control" value="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"></td>
                                    <td><input type="text" name="qty[]" class="form-control" value="<?php echo htmlspecialchars($row['qty'] ?? ''); ?>"></td>
                                    <td><input type="text" name="location[]" class="form-control" value="<?php echo htmlspecialchars($row['location'] ?? ''); ?>"></td>
                                    <td><input type="text" name="notes[]" class="form-control" value="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>"></td>
                                    <td class="text-center"><i class="fa fa-times-circle ilb-remove-row"></i></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" id="ilb-add-row" class="btn btn-default btn-sm">
                            <i class="fa fa-plus"></i> Add Row
                        </button>
                    </div>

                    <div class="row" style="padding:15px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Issued By</label>
                                <input type="text" name="issued_by" class="form-control"
                                       placeholder="Name of staff issuing/handing over the count"
                                       value="<?php echo htmlspecialchars($log['issued_by'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Received By</label>
                                <input type="text" name="received_by" class="form-control"
                                       placeholder="Name of staff receiving/confirming the count"
                                       value="<?php echo htmlspecialchars($log['received_by'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div style="padding:20px 15px;">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Log Book
                        </button>
                    </div>

                    <?php echo form_close(); ?>

                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
</div>

<script>
(function () {
    var rowTemplate = '<tr>'
        + '<td><input type="text" name="item_sku[]" class="form-control"></td>'
        + '<td><input type="text" name="description[]" class="form-control"></td>'
        + '<td><input type="text" name="qty[]" class="form-control"></td>'
        + '<td><input type="text" name="location[]" class="form-control"></td>'
        + '<td><input type="text" name="notes[]" class="form-control"></td>'
        + '<td class="text-center"><i class="fa fa-times-circle ilb-remove-row"></i></td>'
        + '</tr>';

    document.getElementById('ilb-add-row').addEventListener('click', function () {
        document.getElementById('ilb-items-body').insertAdjacentHTML('beforeend', rowTemplate);
    });

    document.getElementById('ilb-items-body').addEventListener('click', function (e) {
        if (e.target.classList.contains('ilb-remove-row')) {
            var rows = document.querySelectorAll('#ilb-items-body tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });

    if (typeof $ !== 'undefined' && $.fn.flatpickr) {
        $('#ilb_log_date').flatpickr({ dateFormat: 'Y-m-d' });
        $('#ilb_log_time').flatpickr({ enableTime: true, noCalendar: true, dateFormat: 'h:i', time_24hr: false });
    }
})();
</script>
