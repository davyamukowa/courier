<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel_s"><div class="panel-body text-center">
                            <h3 class="tw-font-bold tw-text-primary-600"><?= $stats['total']; ?></h3>
                            <p class="text-muted"><?= _l('total_enrolled'); ?></p>
                        </div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel_s"><div class="panel-body text-center">
                            <h3 class="tw-font-bold tw-text-success-600"><?= $stats['active']; ?></h3>
                            <p class="text-muted"><?= _l('active'); ?></p>
                        </div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel_s"><div class="panel-body text-center">
                            <h3 class="tw-font-bold tw-text-info-600"><?= $stats['completed']; ?></h3>
                            <p class="text-muted"><?= _l('completed'); ?></p>
                        </div></div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel_s"><div class="panel-body text-center">
                            <h3 class="tw-font-bold tw-text-warning-600"><?= $stats['exited']; ?></h3>
                            <p class="text-muted"><?= _l('exited'); ?></p>
                        </div></div>
                    </div>
                </div>

                <?php if (staff_can('edit', 'wtc_drip') || staff_can('create', 'wtc_drip')) { ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-my-0 tw-font-semibold"><?= _l('enroll_contacts'); ?> - <?= ucfirst($sequence['rel_type']); ?></h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <form id="drip_enroll_form">
                            <input type="hidden" name="sequence_id" value="<?= $sequence['id']; ?>">
                            <div class="row">
                                <?php if ($sequence['rel_type'] === 'leads') { ?>
                                    <div class="col-md-3">
                                        <?= render_select('status', $lead_statuses, ['id', 'name'], 'status'); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= render_select('source', $lead_sources, ['id', 'name'], 'source'); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-md-5">
                                        <?= render_select('groups[]', $customer_groups, ['id', 'name'], 'groups', '', ['multiple' => 1, 'data-actions-box' => 1, 'data-width' => '100%', 'data-live-search' => 1], [], '', '', false); ?>
                                    </div>
                                <?php } ?>
                            
                                <div class="col-md-<?= ($sequence['rel_type'] == 'leads') ? '5' : '6' ?>">
                                    <label><?= ucfirst($sequence['rel_type']); ?></label>
                                    <select name="rel_ids[]" id="drip_rel_ids" class="form-control selectpicker" multiple data-actions-box="true" data-width="100%" data-live-search="true">
                                        <?php foreach ($rel_records as $record) { ?>
                                            <?php
                                            $id = is_array($record) ? $record['id'] : $record->id;
                                            $name = $sequence['rel_type'] === 'leads' ? (is_array($record) ? $record['name'] : $record->name) : trim((is_array($record) ? ($record['firstname'] ?? '') : ($record->firstname ?? '')) . ' ' . (is_array($record) ? ($record['lastname'] ?? '') : ($record->lastname ?? '')));
                                            $phonenumber = (is_array($record) ? $record['phonenumber'] : $record->phonenumber);
                                            ?>
                                            <option value="<?= $id; ?>"
                                                data-content="
                                                    <div class='tw-flex tw-justify-between'>
                                                        <span class='tw-font-medium'><?= $name; ?></span>
                                                        <?php if ($phonenumber): ?>
                                                            <span class='mleft10 text-muted'> (<?= $phonenumber; ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                ">
                                                <?= $name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary mtop25 tw-w-full"><?= _l('enroll'); ?></button>
                                </div>
                            </div>
                        </form>
                        <div id="drip_enroll_summary" class="alert alert-info mtop15 hide"></div>
                    </div>
                </div>
                <?php } ?>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-my-0 tw-font-semibold"><?= _l('drip_enrollments'); ?> - <?= $sequence['name']; ?></h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator">
                        <div class="panel-table-full">
                        <?= render_datatable([
                            _l('id'),
                            _l('phone'),
                            _l('rel_type'),
                            _l('current_step'),
                            _l('status'),
                            _l('next_send_at'),
                            _l('failure_count'),
                            _l('enrolled_at'),
                            _l('last_step_sent'),
                            _l('actions'),
                        ], 'drip_enrollments') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
"use strict";
 $(function() {
        initDataTable('.table-drip_enrollments', admin_url + 'whatsbot/drip_campaigns/get_enrollments_table/<?= $sequence['id']; ?>', 'undefined', 'undefined', 'undefined', [0, 'desc']);
    });
function wb_drip_csrf(data) {
    data = data || {};
    if (typeof csrfData !== 'undefined') {
        data[csrfData.token_name] = csrfData.hash;
    }
    return data;
}

function wb_drip_action(action, id) {
    $.post(admin_url + 'whatsbot/drip_campaigns/' + action + '_enrollment/' + id, wb_drip_csrf(), function(res) {
        var r = typeof res === 'string' ? JSON.parse(res) : res;
        if (r.status) { location.reload(); }
    });
}

function view_drip_enrollment(id){
    $.ajax({
        url: admin_url + 'whatsbot/drip_campaigns/get_enrollment_details/' + id,
        method: 'GET',
        dataType: "json",
        success: function(res) {

            if (!res || !res.enrollment) {
                alert('<?= _l('no_enrollment_found'); ?>');
                return;
            }

            let e = res.enrollment.enrollment;
            let rel = res.enrollment.rel_data || {};

            let relName = '';

            if (e.rel_type === 'leads') {
                relName = rel.name || '';
            } else if (e.rel_type === 'contacts') {
                relName = ((rel.firstname || '') + ' ' + (rel.lastname || '')).trim();
            }

            let nextSend = e.next_send_at 
                ? moment(e.next_send_at).format('YYYY-MM-DD HH:mm:ss') 
                : '-';

            let enrolled = e.enrolled_at 
                ? moment(e.enrolled_at).format('YYYY-MM-DD HH:mm:ss') 
                : '-';

            let lastStep = e.last_step_sent_at 
                ? moment(e.last_step_sent_at).format('YYYY-MM-DD HH:mm:ss') 
                : '-';

            let html = `
            <div class="modal fade" id="enrollmentModal" tabindex="-1">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">
                                Enrollment${relName ? ' - ' + relName : ''}
                            </h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="col-md-6 mbot10">
                                    <p><strong>Phone Number</strong><br>${e.phone_number || '-'}</p>
                                </div>

                                <div class="col-md-6 mbot10">
                                    <p><strong>Status</strong><br>${e.status || '-'}</p>
                                </div>

                                <div class="col-md-6 mbot10">
                                    <p><strong>Current Step</strong><br>${e.current_step || '-'}</p>
                                </div>

                                <div class="col-md-6 mbot10">
                                    <p><strong>Next Send At</strong><br>${nextSend}</p>
                                </div>

                                <div class="col-md-6 mbot10">
                                    <p><strong>Failure Count</strong><br>${e.failure_count ?? 0}</p>
                                </div>

                                <div class="col-md-6 mbot10">
                                    <p><strong>Enrolled At</strong><br>${enrolled}</p>
                                </div>

                                <div class="col-md-12 mbot10">
                                    <p><strong>Last Step Sent At</strong><br>${lastStep}</p>
                                </div>

                            </div>

                            ${
                                e.last_error 
                                ? `
                                <hr>
                                <h5>Last Error</h5>
                                <div class="alert alert-danger">
                                    ${e.last_error}
                                </div>
                                `
                                : ''
                            }

                            ${
                                e.exit_reason 
                                ? `
                                <div class="alert alert-warning">
                                    <strong>Exit Reason:</strong> ${e.exit_reason}
                                </div>
                                `
                                : ''
                            }

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>
            `;

            // Remove old modal if exists
            $('#enrollmentModal').remove();

            // Append and show
            $('body').append(html);
            $('#enrollmentModal').modal('show');
        }
    });
}

function setStatus(status) {
    let cls = 'label-default';

    if (status === 'active') cls = 'label-success';
    else if (status === 'paused') cls = 'label-warning';
    else if (status === 'completed') cls = 'label-info';
    else if (status === 'exited') cls = 'label-danger';

    $('#detailStatus')
        .removeClass()
        .addClass('label ' + cls)
        .text(status);
}

$(function() {
    function refreshEnrollmentRecords() {
        var data = $('#drip_enroll_form').serializeArray();
        if (typeof csrfData !== 'undefined') {
            data.push({ name: csrfData.token_name, value: csrfData.hash });
        }
        $.post(admin_url + 'whatsbot/drip_campaigns/get_enrollment_records/<?= $sequence['id']; ?>', data, function(res) {
            var records = typeof res === 'string' ? JSON.parse(res) : res;
            var select = $('#drip_rel_ids');
            var selected = select.val() || [];
            select.empty();
            $.each(records, function(i, record) {
                var isSelected = selected.includes(String(record.id));
                select.append(`
                    <option ${isSelected ? 'selected' : ''} value="${record.id}"
                        data-content="
                            <div>
                                <div class='tw-font-medium'>${record.name}</div>
                                ${record.phonenumber ? `<small class='text-muted'>${record.phonenumber}</small>` : ''}
                            </div>
                        ">
                        ${record.name}
                    </option>
                `);
            });
            select.selectpicker('refresh');
        });
    }

    $('select[name="status"], select[name="source"], select[name="groups[]"]').on('change', refreshEnrollmentRecords);

    $('#drip_enroll_form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        if (typeof csrfData !== 'undefined') {
            data.push({name: csrfData.token_name, value: csrfData.hash});
        }
        $.post(admin_url + 'whatsbot/drip_campaigns/enroll', data, function(res) {
            var r = typeof res === 'string' ? JSON.parse(res) : res;
            if (r.status) {
                var s = r.summary || {};
                $('#drip_enroll_summary')
                    .removeClass('hide alert-danger')
                    .addClass('alert-info')
                    .html(
                        r.message +
                        '<br><?= _l('selected'); ?>: ' + (s.selected || 0) +
                        '<br><?= _l('already_enrolled'); ?>: ' + (s.already_enrolled || 0) +
                        ' | <?= _l('invalid_phone'); ?>: ' + (s.invalid_phone || 0) +
                        ' | <?= _l('opted_out'); ?>: ' + (s.opted_out || 0)
                    );
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                $('#drip_enroll_summary').removeClass('hide alert-info').addClass('alert-danger').text(r.message || 'Error');
            }
        });
    });
});
</script>
