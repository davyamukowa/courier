<div class="row">
    <?php echo form_open('admin/courier/settings/general', ['id' => 'set-general-settings-form']); ?>
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Courier Operation Type</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Select the type of courier services you operate. This affects how shipment invoices are calculated
                    and what information appears on the create shipment form.
                </p>
                <div class="mb-3">
                    <div style="display:flex; gap:30px; align-items:flex-start;">
                        <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                            <input type="radio" name="courier_type" value="local"
                                   <?php echo ($courier_type ?? 'international') === 'local' ? 'checked' : ''; ?>>
                            <strong>Local Courier</strong>
                            <small class="text-muted" style="display:block; font-weight:400;">(e.g. Nairobi → Nakuru, local transport). Invoice = Unit Price &times; Quantity.</small>
                        </label>
                        <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                            <input type="radio" name="courier_type" value="international"
                                   <?php echo ($courier_type ?? 'international') === 'international' ? 'checked' : ''; ?>>
                            <strong>International / Freight</strong>
                            <small class="text-muted" style="display:block; font-weight:400;">(Air, Sea, Road freight with volumetric weight). Invoice = Chargeable Weight &times; Rate.</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">General Settings</h4></div>
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label">Logistic Company Name</label>
                    <input class="form-control" name="courier_logistic_company"
                           value="<?php echo htmlspecialchars($courier_logistic_company ?? ''); ?>"
                           type="text" placeholder="e.g. Xetuu">
                    <small class="text-muted">Appears on waybills as the logistic company.</small>
                </div>
                <div style="margin-top:15px;" class="mb-3">
                    <label class="form-label">Waybill Number Prefix</label>
                    <input class="form-control" name="courier_waybill_prefix"
                           value="<?php echo htmlspecialchars($courier_waybill_prefix ?? ''); ?>"
                           type="text" maxlength="10" placeholder="e.g. XETU">
                    <small class="text-muted">Short unique identifier that appears at the start of every waybill number (e.g. <strong>XETU</strong>220518).</small>
                </div>
                <div style="margin-top:15px;" class="mb-3">
                    <label class="form-label">Courier Invoice Theme Color</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_invoice_color"
                               value="<?php echo htmlspecialchars(get_option('courier_invoice_color') ?: '#2e7d32'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Primary color (applied to header borders, titles, etc).</span>
                    </div>
                </div>
                <div style="margin-top:15px;" class="mb-3">
                    <label class="form-label">Courier Invoice Theme Color 2</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_invoice_color_2"
                               value="<?php echo htmlspecialchars(get_option('courier_invoice_color_2') ?: get_option('courier_invoice_color') ?: '#2e7d32'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Secondary color (applied to table headers and totals section).</span>
                    </div>
                </div>
                <button style="margin-top:20px;" type="submit" class="btn btn-success">Save General Settings</button>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Payment Terms on Invoice</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Choose how payment terms appear on courier invoices. <strong>Automatic</strong> applies the same default terms to every invoice.
                    <strong>Manual</strong> lets you set unique terms per shipment at the time of creation.
                </p>
                <?php
                $pt_mode    = get_option('courier_payment_terms_mode') ?: 'manual';
                $pt_default = get_option('courier_default_payment_terms') ?: '';
                ?>
                <div style="display:flex; gap:30px; align-items:flex-start; margin-bottom:16px;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                        <input type="radio" name="courier_payment_terms_mode" value="manual"
                               <?php echo $pt_mode === 'manual' ? 'checked' : ''; ?>
                               onchange="togglePtMode(this.value)">
                        <strong>Manual</strong>
                        <small class="text-muted" style="display:block; font-weight:400;">Staff enter custom terms for each shipment.</small>
                    </label>
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                        <input type="radio" name="courier_payment_terms_mode" value="automatic"
                               <?php echo $pt_mode === 'automatic' ? 'checked' : ''; ?>
                               onchange="togglePtMode(this.value)">
                        <strong>Automatic</strong>
                        <small class="text-muted" style="display:block; font-weight:400;">One fixed set of terms auto-filled on every invoice.</small>
                    </label>
                </div>
                <div id="pt-default-wrap" style="<?php echo $pt_mode === 'automatic' ? '' : 'display:none;'; ?>">
                    <label class="form-label">Default Payment Terms</label>
                    <textarea name="courier_default_payment_terms" rows="4" class="form-control"
                              placeholder="e.g. Payment is due within 30 days of invoice date. Late payments attract a 2% monthly surcharge."><?php echo htmlspecialchars($pt_default); ?></textarea>
                    <small class="text-muted">This text will appear on every courier invoice automatically.</small>
                </div>
                <button style="margin-top:20px;" type="submit" class="btn btn-success">Save General Settings</button>
            </div>
        </div>
    </div>

    <!-- ── Consignment Note Template ───────────────────────────────────── -->
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Consignment Note Template</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Choose the print layout used when generating a Consignment Note for any shipment.
                </p>
                <?php
                $cur_cn_tpl = get_option('courier_cn_template') ?: 'standard';
                $cn_templates = [
                    'standard'       => ['label' => 'Standard Green',     'icon' => 'fa-file-text-o', 'desc' => 'Clean modern design with green accents. Grid layout for shipper/consignee. Default template.'],
                    'shavan'         => ['label' => 'Classic Form',        'icon' => 'fa-wpforms',     'desc' => 'Physical-form style: black borders, charge checkboxes, onforwarding table, special notes. Ideal for road freight.'],
                    'corporate_blue' => ['label' => 'Corporate Blue',      'icon' => 'fa-building',    'desc' => 'Navy blue header band with logo. Professional and formal — suited for international shipments.'],
                    'express_red'    => ['label' => 'Express Red',         'icon' => 'fa-bolt',        'desc' => 'Bold red top-strip with route diagram. High-visibility design for express courier services.'],
                    'thermal'        => ['label' => 'Thermal / Compact',   'icon' => 'fa-barcode',     'desc' => 'Monochrome narrow layout. Prints cleanly on thermal or A5 printers. Courier-style font.'],
                ];
                ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px;margin-bottom:20px;" id="cn-tpl-grid">
                    <?php foreach ($cn_templates as $slug => $tpl): ?>
                    <label style="cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 12px;
                        border:2px solid <?php echo $cur_cn_tpl === $slug ? '#1a2e5a' : '#ddd'; ?>;
                        border-radius:8px;background:<?php echo $cur_cn_tpl === $slug ? '#eef2ff' : '#fafafa'; ?>;
                        transition:.2s;" id="cn-card-<?php echo $slug; ?>">
                        <input type="radio" name="courier_cn_template" value="<?php echo $slug; ?>"
                               <?php echo $cur_cn_tpl === $slug ? 'checked' : ''; ?>
                               style="display:none;" onchange="selectCnCard('<?php echo $slug; ?>')">
                        <i class="fa <?php echo $tpl['icon']; ?>" style="font-size:26px;color:<?php echo $cur_cn_tpl === $slug ? '#1a2e5a' : '#888'; ?>;" id="cn-icon-<?php echo $slug; ?>"></i>
                        <strong style="font-size:13px;color:<?php echo $cur_cn_tpl === $slug ? '#1a2e5a' : '#333'; ?>;text-align:center;" id="cn-label-<?php echo $slug; ?>"><?php echo $tpl['label']; ?></strong>
                        <small class="text-muted" style="font-size:11px;text-align:center;line-height:1.4;"><?php echo $tpl['desc']; ?></small>
                        <span style="font-size:10px;font-weight:700;color:<?php echo $cur_cn_tpl === $slug ? '#1a2e5a' : '#aaa'; ?>;background:<?php echo $cur_cn_tpl === $slug ? '#c3cfe8' : 'transparent'; ?>;border-radius:10px;padding:2px 10px;" id="cn-active-<?php echo $slug; ?>"><?php echo $cur_cn_tpl === $slug ? '&#10003; Active' : '&nbsp;'; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-success">Save General Settings</button>
            </div>
        </div>
    </div>

    <!-- ── Consignment Note Color ───────────────────────────────────────── -->
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Consignment Note Accent Color</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    This color is applied to headers, table bands, and border accents on all consignment note templates.
                </p>
                <div style="margin-top:10px;">
                    <label class="form-label">CN Accent Color</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_cn_color"
                               value="<?php echo htmlspecialchars(get_option('courier_cn_color') ?: '#2e7d32'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Applied to header bands, table headers, and box borders on all CN templates.</span>
                    </div>
                </div>
                <button style="margin-top:16px;" type="submit" class="btn btn-success">Save General Settings</button>
            </div>
        </div>
    </div>

    <!-- ── Consignment Note Extra Settings ──────────────────────────────── -->
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Consignment Note Content</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    These fields appear on the <strong>Classic Form</strong> template. Leave blank to use built-in defaults.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Other Offices / Branch Locations</label>
                            <textarea class="form-control" name="courier_other_offices" rows="4"
                                      placeholder="e.g. Mombasa, Kisumu, Nakuru, Busia, Eldoret&#10;Naivasha, Mumias, Bungoma, Kitale"><?php echo htmlspecialchars(get_option('courier_other_offices')); ?></textarea>
                            <small class="text-muted">Shown in the "Other offices" row. One line per branch or a comma-separated list.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Special Notes (one per line)</label>
                            <textarea class="form-control" name="courier_cn_special_notes" rows="4"
                                      placeholder="Leave blank to use built-in defaults."><?php echo htmlspecialchars(get_option('courier_cn_special_notes')); ?></textarea>
                            <small class="text-muted">Each line becomes a numbered note. Leave blank to show the default 5-point disclaimer.</small>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top:10px;">Save General Settings</button>
            </div>
        </div>
    </div>

    <!-- ── Manifest Template ─────────────────────────────────────────────── -->
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Manifest Template</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Choose the print layout for the Cargo Manifest page. Each tenant can pick a different design.
                </p>
                <?php
                $cur_tpl = get_option('courier_manifest_template') ?: 'cargo_green';
                $templates = [
                    'cargo_green'   => ['label' => 'Cargo Green',      'icon' => 'fa-file-text',   'desc'  => 'Full-featured: stats strip, route management, multi-POD table. Green accent.'],
                    'dubai_air'     => ['label' => 'Dubai Air Manifest','icon' => 'fa-plane',        'desc'  => 'Airline-style tabular layout with AWB/Flight fields. Good for air freight.'],
                    'classic_table' => ['label' => 'Classic Table',     'icon' => 'fa-table',        'desc'  => 'Professional black &amp; white formal document. Clean serif layout.'],
                    'modern_blue'   => ['label' => 'Modern Blue',       'icon' => 'fa-th-large',     'desc'  => 'Same structure as Cargo Green but with a blue accent and card-style stats.'],
                    'compact_list'  => ['label' => 'Compact List',      'icon' => 'fa-list-ul',      'desc'  => 'High-density single-line rows. Ideal for large shipment batches.'],
                ];
                ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; margin-bottom:20px;" id="manifest-tpl-grid">
                    <?php foreach ($templates as $slug => $tpl): ?>
                    <label style="cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 12px; border:2px solid <?php echo $cur_tpl === $slug ? '#2e7d32' : '#ddd'; ?>; border-radius:8px; background:<?php echo $cur_tpl === $slug ? '#f1f8e9' : '#fafafa'; ?>; transition:.2s;" id="tpl-card-<?php echo $slug; ?>">
                        <input type="radio" name="courier_manifest_template" value="<?php echo $slug; ?>" <?php echo $cur_tpl === $slug ? 'checked' : ''; ?> style="display:none;" onchange="selectTplCard('<?php echo $slug; ?>')">
                        <i class="fa <?php echo $tpl['icon']; ?>" style="font-size:26px; color:<?php echo $cur_tpl === $slug ? '#2e7d32' : '#888'; ?>;" id="tpl-icon-<?php echo $slug; ?>"></i>
                        <strong style="font-size:13px; color:<?php echo $cur_tpl === $slug ? '#2e7d32' : '#333'; ?>; text-align:center;" id="tpl-label-<?php echo $slug; ?>"><?php echo $tpl['label']; ?></strong>
                        <small class="text-muted" style="font-size:11px; text-align:center; line-height:1.4;"><?php echo $tpl['desc']; ?></small>
                        <?php if ($cur_tpl === $slug): ?>
                        <span style="font-size:10px; font-weight:700; color:#2e7d32; background:#c8e6c9; border-radius:10px; padding:2px 10px;" id="tpl-active-<?php echo $slug; ?>">&#10003; Active</span>
                        <?php else: ?>
                        <span style="font-size:10px; color:#aaa; visibility:hidden;" id="tpl-active-<?php echo $slug; ?>">&#10003; Active</span>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:10px;">
                    <label class="form-label">Manifest Accent Color</label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <input type="color" name="courier_manifest_color"
                               value="<?php echo htmlspecialchars(get_option('courier_manifest_color') ?: '#2e7d32'); ?>"
                               style="width:50px; height:36px; border:1px solid #ccc; border-radius:4px; padding:2px; cursor:pointer;">
                        <span class="text-muted" style="font-size:13px;">Applied to header, table header/footer, and borders on all manifest templates.</span>
                    </div>
                </div>

                <button style="margin-top:20px;" type="submit" class="btn btn-success">Save General Settings</button>
            </div>
        </div>
    </div>

    <?php echo form_close(); ?>
<script>
var _cnSlugs = ['standard','shavan','corporate_blue','express_red','thermal'];
function selectCnCard(slug) {
    _cnSlugs.forEach(function(s) {
        var card  = document.getElementById('cn-card-'   + s);
        var icon  = document.getElementById('cn-icon-'   + s);
        var label = document.getElementById('cn-label-'  + s);
        var badge = document.getElementById('cn-active-' + s);
        var active = (s === slug);
        card.style.borderColor  = active ? '#1a2e5a' : '#ddd';
        card.style.background   = active ? '#eef2ff' : '#fafafa';
        icon.style.color        = active ? '#1a2e5a' : '#888';
        label.style.color       = active ? '#1a2e5a' : '#333';
        badge.style.color       = active ? '#1a2e5a' : '#aaa';
        badge.style.background  = active ? '#c3cfe8' : 'transparent';
        badge.innerHTML         = active ? '&#10003; Active' : '&nbsp;';
    });
}
var _tplSlugs = ['cargo_green','dubai_air','classic_table','modern_blue','compact_list'];
function selectTplCard(slug) {
    _tplSlugs.forEach(function(s) {
        var card  = document.getElementById('tpl-card-' + s);
        var icon  = document.getElementById('tpl-icon-' + s);
        var label = document.getElementById('tpl-label-' + s);
        var badge = document.getElementById('tpl-active-' + s);
        var active = (s === slug);
        card.style.borderColor  = active ? '#2e7d32' : '#ddd';
        card.style.background   = active ? '#f1f8e9' : '#fafafa';
        icon.style.color        = active ? '#2e7d32' : '#888';
        label.style.color       = active ? '#2e7d32' : '#333';
        badge.style.visibility  = active ? 'visible' : 'hidden';
        badge.textContent       = '✓ Active';
    });
}
</script>

<script>
function togglePtMode(val) {
    document.getElementById('pt-default-wrap').style.display = (val === 'automatic') ? '' : 'none';
}
</script>

    <?php echo form_open('admin/courier/settings/dimensional_factor', ['id' => 'set-dimensional-factor-form']); ?>
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">Dimensional Weight Factors</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    <strong>How package weight is calculated:</strong><br>
                    Volumetric Weight = (Length &times; Width &times; Height) &divide; Dimensional Factor<br>
                    Chargeable Weight = the greater of Gross Weight vs Volumetric Weight<br>
                    Different shipping modes use different factors — a higher factor means air/sea freight charges less for bulky but light packages.
                </p>
                <div class="mb-3">
                    <label class="form-label">Domestic / Courier Dimensional Factor</label>
                    <input class="form-control" value="<?php echo $dimensional_factor[0]->value; ?>" name="default"
                           type="number" id="formFile">
                </div>
                <div style="margin-top:20px;" class="mb-3">
                    <label class="form-label">Air Consolidation Dimensional Factor</label>
                    <input class="form-control" value="<?php echo $dimensional_factor[1]->value; ?>"
                           name="air_consolidation" type="number">
                </div>
                <div style="margin-top:20px;" class="mb-3">
                    <label class="form-label">Air Freight Dimensional Factor</label>
                    <input class="form-control" value="<?php echo $dimensional_factor[2]->value; ?>" name="air_freight"
                           type="number">
                </div>
                <div style="margin-top:20px;" class="mb-3">
                    <label class="form-label">Sea LCL Dimensional Factor</label>
                    <input class="form-control" value="<?php echo $dimensional_factor[3]->value; ?>" name="sea_lcl"
                           type="number">
                </div>
                <button style="margin-top:20px;" type="submit" class="btn btn-success">Save Weight Factors</button>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>

    <?php echo form_open('admin/courier/settings/freight_rates', ['id' => 'set-freight-rates-form']); ?>
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-heading"><h4 class="panel-title">International / Freight Rates</h4></div>
            <div class="panel-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Rates used when the courier type is set to <strong>International / Freight</strong>.
                    Invoice total = Total Chargeable Weight &times; Rate + Packaging Charges.
                    Update these whenever your pricing changes.
                </p>

                <h5 style="margin:0 0 10px; color:#444; border-bottom:1px solid #eee; padding-bottom:6px;">Road / Domestic / Courier</h5>
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-4">
                        <label class="form-label">Rate per KG</label>
                        <input class="form-control" name="courier_rate_road" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_road') ?: '1.00'); ?>">
                        <small class="text-muted">Applies to Domestic and International Road / Courier shipments.</small>
                    </div>
                </div>

                <h5 style="margin:0 0 10px; color:#444; border-bottom:1px solid #eee; padding-bottom:6px;">Sea LCL &amp; Sea Consolidation</h5>
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-4">
                        <label class="form-label">Sea LCL — Rate per KG</label>
                        <input class="form-control" name="courier_rate_sea_lcl" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_sea_lcl') ?: '1.00'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sea Consolidation — Rate per CBM</label>
                        <input class="form-control" name="courier_rate_sea_consolidation" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_sea_consolidation') ?: '1.00'); ?>">
                    </div>
                </div>

                <h5 style="margin:0 0 10px; color:#444; border-bottom:1px solid #eee; padding-bottom:6px;">Sea FCL — Rate per Container</h5>
                <div class="row" style="margin-bottom:20px;">
                    <?php
                    $fcl_labels = [
                        '20dv' => "20' DV",
                        '40dv' => "40' DV",
                        '20hc' => "20' HC",
                        '40hc' => "40' HC",
                        '20rf' => "20' RF",
                        '40rf' => "40' RF",
                        '20fr' => "20' FR",
                        '40fr' => "40' FR",
                        'roro' => 'RoRo',
                    ];
                    foreach ($fcl_labels as $key => $label): ?>
                    <div class="col-md-3" style="margin-bottom:14px;">
                        <label class="form-label"><?php echo htmlspecialchars($label); ?></label>
                        <input class="form-control" name="courier_rate_sea_fcl_<?php echo $key; ?>" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_sea_fcl_' . $key) ?: '1.00'); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <h5 style="margin:0 0 10px; color:#444; border-bottom:1px solid #eee; padding-bottom:6px;">Air Freight &amp; Air Consolidation</h5>
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-4">
                        <label class="form-label">Air Freight — Rate per KG</label>
                        <input class="form-control" name="courier_rate_air_freight" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_air_freight') ?: '1.00'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Air Consolidation — Rate per KG</label>
                        <input class="form-control" name="courier_rate_air_consolidation" type="number" step="0.01" min="0"
                               value="<?php echo htmlspecialchars(get_option('courier_rate_air_consolidation') ?: '1.00'); ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Save Freight Rates</button>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
