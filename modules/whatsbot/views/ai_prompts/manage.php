<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Modal -->
<div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= form_open('', ['id' => 'ai-prompts-form'], ['id' => '']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= _l('ai_prompts'); ?></h4>
            </div>
            <div class="modal-body">
                <?= render_input('name', _l('prompt_name'), '', 'text', ['id' => 'prompt_name'], []); ?>
                <?= render_textarea('action', _l('prompt_action'), ''); ?>
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
                $this->load->view('whatsbot/partials/whatsbot_tabs', ['tabs' => $tabs, 'active_tab' => $active_group ?? 'ai_prompts']);
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
                                    <?php echo _l('ai_prompts'); ?>
                                </h4>
                                <div>
                                    <?php if (staff_can('create', 'wtc_ai_prompts')): ?>
                                        <a href="#" id="create-btn" data-toggle="modal" data-target="#insertModal" class="btn btn-primary"><?php echo _l('create_ai_prompts'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-separator">
                            <div class="panel-table-full">
                                <?php
                                render_datatable([
                                    _l('the_number_sign'),
                                    _l('name'),
                                    _l('prompt_action'),
                                    _l('action')
                                ], 'ai_prompts_tbl');
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
    initDataTable('.table-ai_prompts_tbl', '<?= admin_url(WHATSBOT_MODULE . '/ai_prompts/get_table_data'); ?>', [3], [3], undefined, [0, 'DESC']);

    // Resetting form on modal hide event 
    $('#insertModal').on('hide.bs.modal', function() {
        $('#ai-prompts-form').trigger('reset');
        $('div.form-group').removeClass('has-error');
        $('input[name=id]').val('');
        $('p.text-danger').remove();
    });

    appValidateForm('#ai-prompts-form', {
            name: {
                required: true,
                remote: {
                    url: '<?= admin_url(WHATSBOT_MODULE . '/ai_prompts/is_prompt_name_exists'); ?>',
                    type: 'post',
                    data: {
                        name: function() {
                            return $('input[name="name"]').val();
                        },
                        id: function() {
                            return $('input[name="id"]').val();
                        }
                    }
                }
            },
            action: {
                required: true,
            }
        },
        formSubmission, {
            name: {
                remote: "Prompt name is already exists."
            },
        }
    );

    function formSubmission(form) {
        $.ajax({
            url: '<?= admin_url(WHATSBOT_MODULE . '/ai_prompts/save_prompt'); ?>',
            type: 'post',
            dataType: 'json',
            data: $(form).serialize(),
            success: function(res) {
                $('#insertModal').modal('hide');
                alert_float(res.type, res.message);
                $('.table-ai_prompts_tbl').DataTable().ajax.reload();
            }
        });
    }

    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        $('input[name="id"]').val($(this).data('id'));
        $('#name').val($(this).data('name'));
        $('#action').val($(this).data('action'));
        $('#insertModal').modal('show');
    });

    $(document).on('click', '.delete_btn', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= admin_url(WHATSBOT_MODULE . '/ai_prompts/delete'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                id: $(this).data('id')
            },
            success: function(res) {
                alert_float(res.type, res.message);
                $('.table-ai_prompts_tbl').DataTable().ajax.reload();
            }
        });

    });
</script>
