<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-mb-4">Rubber Stamp Configuration</h4>
        <div class="panel_s">
            <div class="panel-body">
                <?php echo form_open(admin_url('xetuu_books/config/stamp')); ?>
                
                <p class="tw-mb-6 tw-text-neutral-500">Configure the text that appears on the circular rubber stamp on the invoice form.</p>
                
                <?php $enabled = $this->xb_config->get_setting('xb_stamp_enabled'); ?>
                <div class="form-group">
                    <label for="xb_stamp_enabled" class="control-label clearfix">Enable Rubber Stamp on Invoices</label>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="y_opt_1_xb_stamp_enabled" name="xb_stamp_enabled" value="1" <?php if($enabled == '1' || $enabled === null){echo 'checked';} ?>>
                        <label for="y_opt_1_xb_stamp_enabled">Yes</label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="y_opt_2_xb_stamp_enabled" name="xb_stamp_enabled" value="0" <?php if($enabled === '0'){echo 'checked';} ?>>
                        <label for="y_opt_2_xb_stamp_enabled">No</label>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_input('xb_stamp_top_text', 'Outer Circle Top Text (e.g. Company Name)', $this->xb_config->get_setting('xb_stamp_top_text') ?: 'OFFICE OF THE MUNICIPAL TREASURER'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('xb_stamp_bottom_text', 'Outer Circle Bottom Text (e.g. Location/Phone)', $this->xb_config->get_setting('xb_stamp_bottom_text') ?: 'MUNICIPALITY OF SAGNAY'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_color_picker('xb_stamp_color', 'Stamp Ink Color', $this->xb_config->get_setting('xb_stamp_color') ?: '#2563eb'); ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info tw-mt-4">
                            <strong>Note:</strong> The status (PAID, UNPAID, PARTIAL, CANCELLED), Date, Time, and user ("By") are generated automatically based on the invoice's current status and its latest payment record.
                        </div>
                    </div>
                </div>

                <div class="tw-flex tw-justify-end tw-mt-6">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
