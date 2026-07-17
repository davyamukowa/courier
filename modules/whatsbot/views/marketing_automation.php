<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('whatsbot/partials/bots_automation_tabs', ['active_group' => $active_group ?? 'automation']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="form-group select-placeholder">
                            <label for="flow"><?php echo _l('bulk_pdf_export_select_type'); ?></label>
                            <select name="automation[flow]" required id="flow" class="selectpicker" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php foreach ($flows as $flow) { ?>
                                    <option value="<?php echo $flow['flow_id']; ?>"><?php echo $flow['flow_name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="row action_row base_action_row" id="action_row_0">
                            <div class="form-group col-md-5">
                                <label for="action"><?php echo _l('action'); ?></label>
                                <select name="automation[hook][0][action]" id="action" class="selectpicker action" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <option value="after_ticket_status_changed" data-subtext="<?= _l('after_ticket_status_changed_subtext') ?>"><?= _l("after_ticket_status_changed") ?></option>
                                    <option value="project_status_changed" data-subtext="<?= _l('project_status_changed_subtext') ?>"><?= _l("project_status_changed") ?></option>
                                </select>
                            </div>
                            <div class="form-group col-md-5 status_dropdown project_status_changed">
                                <label for="project_status"><?php echo _l('select_status'); ?></label>
                                <select name="automation[hook][0][project_status]" id="project_status" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach ($project_status as $status) { ?>
                                        <option value="<?= $status['id']; ?>"><?= $status['name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-5 status_dropdown after_ticket_status_changed" style="display:none">
                                <label for="ticket_status"><?php echo _l('select_status'); ?></label>
                                <select name="automation[hook][0][ticket_status]" id="ticket_status" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach ($ticket_status as $status) { ?>
                                        <option value="<?= $status['ticketstatusid']; ?>"><?= $status['name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-2 action">
                                <button type="button" class="btn btn-sm btn-success add_row mtop30"><i class="fa fa-plus mright5"></i><?= _l('add') ?></button>
                            </div>
                        </div>
                        <div class="append_action_row"></div>
                    </div>
                    <div class="panel-footer text-right">
                        <button class="btn btn-primary" type="submit"><?php echo _l('bulk_pdf_export_button'); ?></button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        appValidateForm($('form'), {
            flow: 'required'
        });

        $(document).on('change', 'select.action', function() {
            var val = $(this).val();
            $(this).parents(".action_row").find(".status_dropdown").hide();
            $(this).parents(".action_row").find(`.${val}`).show();
        });

        $(".add_row").on("click", function() {
            var count = $('.action_row').length;
            cloneElement = $(".base_action_row").clone()
            cloneElement.removeClass("base_action_row")
            cloneElement.find(".btn.dropdown-toggle.btn-default").remove()
            cloneElement.attr("id", "action_row_" + count)
            cloneElement.find('select').each(function() {
                $(this).attr("name", $(this).attr("name").replace('[0]', `[${count}]`));
            });
            cloneElement.find(".add_row").replaceWith(function() {
                return `<button type="button" class="btn btn-sm btn-danger remove_row mtop30"><i class="fa fa-minus"></i></button>`;
            });
            $(".append_action_row").append(cloneElement);
            init_selectpicker();
        });

        $(document).on("click", ".remove_row", function() {
            var rowId = $(this).closest('.action_row').attr('id');
            $("#" + rowId).remove();
        });

        $(document).on('change', '#flow', function() {
            $(".append_action_row").html("");
            $.ajax({
                url: `${admin_url}whatsbot/marketing_automation/get_flow_automation`,
                type: 'post',
                data: { flow_id: $('#flow').val()},
                dataType: 'json',
            }).done(function(res) {
                count = 0;
                $.each(res, function(action, status){
                    $.each(status, function(index, status_id){
                        count++;

                        cloneElement = $(".base_action_row").clone()
                        cloneElement.removeClass("base_action_row")
                        cloneElement.find(".btn.dropdown-toggle.btn-default").remove()
                        cloneElement.attr("id", "action_row_" + count)
                        cloneElement.find('select').each(function() {
                            $(this).attr("name", $(this).attr("name").replace('[0]', `[${count}]`));
                        });
                        cloneElement.find('select#action').val(action);
                        if(action == "after_ticket_status_changed"){
                            cloneElement.find('select#ticket_status').val(status_id);
                        }
                        if(action == "project_status_changed"){
                            cloneElement.find('select#project_status').val(status_id);
                        }
                        cloneElement.find(".add_row").replaceWith(function() {
                            return `<button type="button" class="btn btn-sm btn-danger remove_row mtop30"><i class="fa fa-minus"></i></button>`;
                        });
                        $(".append_action_row").append(cloneElement);
                        $("#action_row_" + count).find(".status_dropdown").hide();
                        $("#action_row_" + count).find(`.${action}`).show();
                    });
                });

                // Refresh the selectpickers
                $('.selectpicker').selectpicker('refresh');
            });
        });
    });
</script>
</body>

</html>
