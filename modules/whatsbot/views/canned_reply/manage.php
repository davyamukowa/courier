<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div class="modal fade" id="replyModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open('', ['id' => 'canned-reply-form'], ['id' => '']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= _l('canned_reply'); ?></h4>
            </div>
            <div class="modal-body">
                <?= render_input('title', _l('title'), '', 'text', ['id' => 'cmp_nm'], []); ?>
                <?= render_textarea('description', _l('desc'), ''); ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="save-btn"><?= _l('save'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php
                $tabs = [
                    ['key' => 'custom_label', 'label' => _l('custom_label'), 'icon' => 'fa-solid fa-tags', 'url' => admin_url('whatsbot/custom_label'), 'visible' => staff_can('view', 'wtc_custom_label')],
                    ['key' => 'canned_reply', 'label' => _l('canned_reply'), 'icon' => 'fa-regular fa-comment-dots', 'url' => admin_url('whatsbot/canned_reply'), 'visible' => staff_can('view', 'wtc_canned_reply')],
                    ['key' => 'ai_prompts', 'label' => _l('ai_prompts'), 'icon' => 'fa-solid fa-wand-magic-sparkles', 'url' => admin_url('whatsbot/ai_prompts'), 'visible' => staff_can('view', 'wtc_ai_prompts')],
                    ['key' => 'opt_out', 'label' => _l('opt_out'), 'icon' => 'fa-solid fa-toggle-off', 'url' => admin_url('whatsbot/optout'), 'visible' => staff_can('view', 'opt_out')],
                ];
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'canned_reply']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-s">
                        <div class="panel-body">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <h4 class="tw-my-0 tw-font-semibold">
                                    <?php echo _l('canned_reply'); ?>
                                </h4>
                                <div>
                                    <?php if (staff_can('create', 'wtc_canned_reply')): ?>
                                        <a href="#" id="create-btn" data-toggle="modal" data-target="#replyModel" class=" btn btn-primary"><?php echo _l('create_canned_reply'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-separator">
                            <div class="panel-table-full">
                                <?php
                                render_datatable([
                                    _l('the_number_sign'),
                                    _l('title'),
                                    _l('desc'),
                                    _l('public'),
                                    _l('action')
                                ], 'canned_table');
?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    initDataTable('.table-canned_table', '<?= admin_url(WHATSBOT_MODULE . '/canned_reply/get_table_data'); ?>', [4], [4], undefined, [0, 'DESC']);

    $('#replyModel').on('hide.bs.modal', function() {
        $('#canned-reply-form').trigger('reset');
        $('input[name=id]').val('');
    });

    appValidateForm('#canned-reply-form', {
        title: "required",
        description: "required",
    }, formSubmission);

    function formSubmission(form) {
        $.ajax({
            url: '<?= admin_url(WHATSBOT_MODULE . '/canned_reply/save_reply'); ?>',
            type: 'post',
            dataType: 'json',
            data: $(form).serialize(),
            success: function(res) {
                $('#replyModel').modal('hide');
                alert_float(res.type, res.message);
                $('.table-canned_table').DataTable().ajax.reload();
            }
        });
    }

    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        $('input[name="id"]').val($(this).data('id'));
        $('#title').val($(this).data('title'));
        $('#description').val($(this).data('desc'));
        $('#replyModel').modal('show');
    });

    $(document).on('click', '.delete_btn', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= admin_url(WHATSBOT_MODULE . '/canned_reply/delete'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                id: $(this).data('id')
            },
            success: function(res) {
                alert_float(res.type, res.message);
                $('.table-canned_table').DataTable().ajax.reload();
            }
        });

    });

    $(document).on('change', '.onoffswitch-checkbox', function() {
        var checkbox = $(this);
        var id = checkbox.data('id');
        var status = checkbox.is(':checked') ? 1 : 0;

        $.ajax({
            url: '<?= admin_url(WHATSBOT_MODULE . '/canned_reply/change_status'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                id: id,
                status: status
            },
            success: function(res) {
                if (res.type == 'success') {
                    alert_float(res.type, res.message);
                } else {
                    alert_float(res.type, res.message);
                    checkbox.prop('checked', !status);
                }
            }
        });

    });
</script>
