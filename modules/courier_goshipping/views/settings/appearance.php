<div class="row">
    <?php echo form_open('admin/courier_goshipping/settings/save_appearance', ['id' => 'set-appearance-settings-form']); ?>
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Theme Colors</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Sets the primary and secondary colors used across the GO Shipping Cargo
                    navbar, buttons, and highlights. Useful when white-labeling this module
                    for a different client/logo.
                </p>
                <div class="mb-3">
                    <label class="form-label">Primary Color</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_theme_primary_color"
                               value="<?php echo htmlspecialchars($courier_theme_primary_color ?? '#3a6ea5'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Navbar background and primary buttons.</span>
                    </div>
                </div>
                <div style="margin-top:15px;" class="mb-3">
                    <label class="form-label">Secondary Color</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_theme_secondary_color"
                               value="<?php echo htmlspecialchars($courier_theme_secondary_color ?? '#c1272d'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Active-tab underline and accent buttons.</span>
                    </div>
                </div>
                <button style="margin-top:20px;" type="submit" class="btn btn-success">Save Appearance Settings</button>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
