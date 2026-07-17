<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php if (!empty($template)) { ?>
    <?php $allowed_extension = wb_get_allowed_extension(); ?>
    <?php if (!empty($template['header_data_format']) && $template['header_params_count'] > 0) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('header'); ?></h5>
        <?php if ('TEXT' === $template['header_data_format']) { ?>
            <?php for ($i = 1; $i <= $template['header_params_count']; ++$i) { ?>
                <?php echo render_input('steps[' . $index . '][header_params][' . $i . '][value]', _l('variable') . ' ' . $i, $header_params->$i->value ?? '', 'text', ['autocomplete' => 'off', 'data-part' => 'header', 'data-number' => $i], [], '', 'drip-param-input drip-header-input mentionable'); ?>
            <?php } ?>
        <?php } else { ?>
            <div class="alert alert-danger"><?php echo _l('currently_type_not_supported', $template['header_data_format']); ?></div>
        <?php } ?>
        <hr>
    <?php } ?>

    <?php if (!empty($template['header_data_format']) && 'IMAGE' === $template['header_data_format']) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('image'); ?></h5>
        <input type="hidden" class="drip-max-file-size" value="<?= $allowed_extension['image']['size']; ?>">
        <?php if (!empty($step['filename'])) { ?>
            <div class="drip-current-media mtop10 mbot10">
                <input type="hidden" class="drip-existing-media-url" value="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>">
                <img src="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" class="img img-responsive" style="max-width: 220px;">
            </div>
        <?php } ?>
        <label class="control-label">
            <?= _l('select_image'); ?>
            <small class="text-muted">(<?= _l('allowed_file_types') . $allowed_extension['image']['extension']; ?>)</small>
        </label>
        <input type="file" name="steps[<?= $index; ?>][image]" accept="<?= $allowed_extension['image']['extension']; ?>" class="form-control drip-media-input" data-media-type="image">
        <hr>
    <?php } ?>

    <?php if (!empty($template['header_data_format']) && 'VIDEO' === $template['header_data_format']) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('video'); ?></h5>
        <input type="hidden" class="drip-max-file-size" value="<?= $allowed_extension['video']['size']; ?>">
        <?php if (!empty($step['filename'])) { ?>
            <div class="drip-current-media mtop10 mbot10">
                <input type="hidden" class="drip-existing-media-url" value="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>">
                <video controls src="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" class="img img-responsive" style="max-width: 220px;"></video>
            </div>
        <?php } ?>
        <label class="control-label">
            <?= _l('select_video'); ?>
            <small class="text-muted">(<?= _l('allowed_file_types') . $allowed_extension['video']['extension']; ?>)</small>
        </label>
        <input type="file" name="steps[<?= $index; ?>][video]" accept="<?= $allowed_extension['video']['extension']; ?>" class="form-control drip-media-input" data-media-type="video">
        <hr>
    <?php } ?>

    <?php if (!empty($template['header_data_format']) && 'DOCUMENT' === $template['header_data_format']) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('document'); ?></h5>
        <input type="hidden" class="drip-max-file-size" value="<?= $allowed_extension['document']['size']; ?>">
        <?php if (!empty($step['filename'])) { ?>
            <div class="drip-current-media mtop10 mbot10">
                <input type="hidden" class="drip-existing-media-url" value="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>">
                <a href="<?= base_url(get_upload_path_by_type('drip') . $step['filename']); ?>" target="_blank" class="btn btn-default"><?= $step['filename']; ?></a>
            </div>
        <?php } ?>
        <label class="control-label">
            <?= _l('select_document'); ?>
            <small class="text-muted">(<?= _l('allowed_file_types') . $allowed_extension['document']['extension']; ?>)</small>
        </label>
        <input type="file" name="steps[<?= $index; ?>][document]" accept="<?= $allowed_extension['document']['extension']; ?>" class="form-control drip-media-input" data-media-type="document">
        <hr>
    <?php } ?>

    <?php if (!empty($template['body_params_count']) && $template['body_params_count'] > 0) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('body'); ?></h5>
        <?php for ($i = 1; $i <= $template['body_params_count']; ++$i) { ?>
            <?php echo render_input('steps[' . $index . '][body_params][' . $i . '][value]', _l('variable') . ' ' . $i, $body_params->$i->value ?? '', 'text', ['autocomplete' => 'off', 'data-part' => 'body', 'data-number' => $i], [], '', 'drip-param-input drip-body-input mentionable'); ?>
        <?php } ?>
        <hr>
    <?php } ?>

    <?php if (!empty($template['footer_params_count']) && $template['footer_params_count'] > 0) { ?>
        <h5 class="tw-mt-0 tw-font-semibold"><?php echo _l('footer'); ?></h5>
        <?php for ($i = 1; $i <= $template['footer_params_count']; ++$i) { ?>
            <?php echo render_input('steps[' . $index . '][footer_params][' . $i . '][value]', _l('variable') . ' ' . $i, $footer_params->$i->value ?? '', 'text', ['autocomplete' => 'off', 'data-part' => 'footer', 'data-number' => $i], [], '', 'drip-param-input drip-footer-input mentionable'); ?>
        <?php } ?>
        <hr>
    <?php } ?>
<?php } ?>
