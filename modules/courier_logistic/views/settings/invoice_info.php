<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open('admin/courier/settings/save_invoice_info', ['id' => 'invoice-info-form']); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-file-text-o"></i> Invoice &amp; Receipt Company Information
        </h4>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="font-size:13px;margin-bottom:20px;">
            This information appears in the header and footer of all courier invoices, receipts, quotations, waybills, and manifests.
            Leave a field blank to inherit the value from
            <a href="<?php echo admin_url('settings?group=company'); ?>" target="_blank">global company settings</a>.
        </p>

        <div class="row">
            <!-- Left column -->
            <div class="col-md-6">

                <div class="form-group">
                    <label class="control-label">Company / Logistics Name</label>
                    <input type="text" class="form-control" name="courier_inv_company_name"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_company_name')); ?>"
                           placeholder="e.g. Shavan Logistics Ltd">
                    <small class="text-muted">Printed as the issuing company on every courier document.</small>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="control-label">Email Address</label>
                    <input type="email" class="form-control" name="courier_inv_email"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_email')); ?>"
                           placeholder="e.g. info@shavan.co.ke">
                    <small class="text-muted">Appears on invoices, receipts, and outbound quote emails.</small>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="control-label">Phone Number</label>
                    <input type="text" class="form-control" name="courier_inv_phone"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_phone')); ?>"
                           placeholder="e.g. +254 700 000 000">
                    <small class="text-muted">Contact number shown on all printed documents.</small>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="control-label">Website</label>
                    <input type="text" class="form-control" name="courier_inv_website"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_website')); ?>"
                           placeholder="e.g. www.shavan.co.ke">
                </div>

            </div>

            <!-- Right column -->
            <div class="col-md-6">

                <div class="form-group">
                    <label class="control-label">Physical Address</label>
                    <textarea class="form-control" name="courier_inv_address" rows="3"
                              placeholder="e.g. 2nd Floor, Westlands Square&#10;Westlands, Nairobi"><?php echo htmlspecialchars(get_option('courier_inv_address')); ?></textarea>
                    <small class="text-muted">Street address or P.O. Box, printed on invoices and receipts.</small>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="control-label">PIN / Tax Number</label>
                    <input type="text" class="form-control" name="courier_inv_pin"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_pin')); ?>"
                           placeholder="e.g. P051234567T">
                    <small class="text-muted">KRA PIN or VAT registration number. Shown on tax invoices.</small>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="control-label">Footer Tagline <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control" name="courier_inv_tagline"
                           value="<?php echo htmlspecialchars(get_option('courier_inv_tagline')); ?>"
                           placeholder="e.g. Thank you for choosing Shavan Logistics!">
                    <small class="text-muted">Short message printed at the bottom of invoices and receipts.</small>
                </div>

            </div>
        </div>

        <!-- Live preview -->
        <hr style="margin:24px 0 18px;">
        <h5 style="margin-bottom:12px;"><i class="fa fa-eye"></i> Document Header Preview</h5>
        <div id="inv-preview" style="border:1px solid #ddd;border-radius:6px;overflow:hidden;max-width:560px;">
            <div id="prev-header" style="background:#1a5276;color:#fff;padding:18px 22px;display:flex;align-items:center;gap:16px;">
                <div>
                    <div id="prev-name" style="font-size:18px;font-weight:700;"></div>
                    <div id="prev-tagline" style="font-size:11px;opacity:.75;margin-top:2px;"></div>
                </div>
            </div>
            <div style="background:#f9f9f9;padding:12px 22px;font-size:12px;color:#555;display:flex;flex-wrap:wrap;gap:14px;" id="prev-contact-row">
                <span id="prev-email" style="display:none;"><i class="fa fa-envelope-o"></i> <span class="prev-val"></span></span>
                <span id="prev-phone" style="display:none;"><i class="fa fa-phone"></i> <span class="prev-val"></span></span>
                <span id="prev-address" style="display:none;"><i class="fa fa-map-marker"></i> <span class="prev-val"></span></span>
                <span id="prev-website" style="display:none;"><i class="fa fa-globe"></i> <span class="prev-val"></span></span>
                <span id="prev-pin" style="display:none;"><i class="fa fa-id-card-o"></i> PIN: <span class="prev-val"></span></span>
            </div>
        </div>

        <div style="margin-top:22px;">
            <button type="submit" class="cgs-btn cgs-btn--primary"><i class="fa fa-save"></i> Save Invoice Info</button>
            <a href="<?php echo admin_url('settings?group=company'); ?>" class="cgs-btn cgs-btn--outline" style="margin-left:8px;" target="_blank">
                <i class="fa fa-external-link"></i> View Global Company Settings
            </a>
        </div>

    </div>
</div>

<?php echo form_close(); ?>

<!-- ── Stamp Settings ─────────────────────────────────────────────────── -->
<?php echo form_open('admin/courier/settings/save_stamp_info', ['id' => 'stamp-info-form']); ?>

<div class="panel panel-default" style="margin-top:24px;">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-circle-o"></i> Invoice Stamp Settings
        </h4>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="font-size:13px;margin-bottom:18px;">
            These fields control what appears inside the round rubber stamp on courier invoices.
            Keep them short — they must fit inside a circle.
        </p>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Stamp Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="courier_stamp_name"
                           id="si_name"
                           value="<?php echo htmlspecialchars(get_option('courier_stamp_name')); ?>"
                           placeholder="e.g. SHAVAN LOGISTICS LTD"
                           maxlength="30">
                    <small class="text-muted">Curved at the top of the stamp. Max ~25 characters.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">PO Box / Short Address</label>
                    <input type="text" class="form-control" name="courier_stamp_pobox"
                           id="si_pobox"
                           value="<?php echo htmlspecialchars(get_option('courier_stamp_pobox')); ?>"
                           placeholder="e.g. P.O. BOX 1341-00100, NAIROBI"
                           maxlength="40">
                    <small class="text-muted">Curved at the outer bottom arc (same ring as company name).</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Phone Number</label>
                    <input type="text" class="form-control" name="courier_stamp_phone"
                           id="si_phone"
                           value="<?php echo htmlspecialchars(get_option('courier_stamp_phone')); ?>"
                           placeholder="e.g. 0725 971 240"
                           maxlength="25">
                    <small class="text-muted">Curved at the inner bottom arc. "TEL: " is added automatically.</small>
                </div>
            </div>
        </div>

        <!-- Live SVG stamp preview -->
        <hr style="margin:20px 0 16px;">
        <h5 style="margin-bottom:12px;"><i class="fa fa-eye"></i> Stamp Preview</h5>
        <div style="display:flex;align-items:center;gap:30px;flex-wrap:wrap;">
            <div id="stamp-preview-wrap" style="transform:rotate(-5deg);filter:drop-shadow(1px 2px 3px rgba(26,58,143,.2));">
                <svg id="stamp-svg" viewBox="0 0 200 200" width="164" height="164"
                     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                        <path id="pvTopArc"  d="M 16,100 A 84,84 0 0,1 184,100"/>
                        <path id="pvBotArc1" d="M 7,100 A 93,93 0 0,0 193,100"/>
                        <path id="pvBotArc2" d="M 32,100 A 68,68 0 0,0 168,100"/>
                    </defs>
                    <circle cx="100" cy="100" r="96" fill="rgba(255,255,255,0.94)" stroke="#1a3a8f" stroke-width="3.5"/>
                    <circle cx="100" cy="100" r="80" fill="none" stroke="#1a3a8f" stroke-width="2"/>
                    <text font-size="13" font-weight="900" fill="#1a3a8f" font-family="Arial,sans-serif" letter-spacing="2">
                        <textPath href="#pvTopArc" xlink:href="#pvTopArc" startOffset="50%" text-anchor="middle" id="pvName"></textPath>
                    </text>
                    <!-- Stars between the two rings at the equatorial line -->
                    <text x="15"  y="103" font-size="12" fill="#1a3a8f" text-anchor="middle">&#9733;</text>
                    <text x="185" y="103" font-size="12" fill="#1a3a8f" text-anchor="middle">&#9733;</text>
                    <text id="pvDate" x="100" y="106" font-size="16" font-weight="900" fill="#cc0000"
                          text-anchor="middle" letter-spacing="2" font-family="Arial,sans-serif"></text>
                    <!-- PO Box on outer bottom arc (same ring as company name) -->
                    <text font-size="12" font-weight="900" fill="#1a3a8f" font-family="Arial,sans-serif" letter-spacing="1.5">
                        <textPath href="#pvBotArc1" xlink:href="#pvBotArc1" startOffset="50%" text-anchor="middle" id="pvPobox"></textPath>
                    </text>
                    <!-- Phone on inner bottom arc -->
                    <text font-size="10.5" font-weight="700" fill="#1a3a8f" font-family="Arial,sans-serif" letter-spacing="1">
                        <textPath href="#pvBotArc2" xlink:href="#pvBotArc2" startOffset="50%" text-anchor="middle" id="pvPhone"></textPath>
                    </text>
                </svg>
            </div>
            <div style="font-size:12px;color:#888;max-width:260px;">
                <p><i class="fa fa-info-circle"></i> The stamp is rendered at this exact size on the invoice.</p>
                <p style="margin-top:8px;">The date is always the current date at time of printing.</p>
            </div>
        </div>

        <div style="margin-top:22px;">
            <button type="submit" class="cgs-btn cgs-btn--primary"><i class="fa fa-save"></i> Save Stamp Settings</button>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script>
(function () {
    var months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
    var now    = new Date();
    var today  = String(now.getDate()).padStart(2,'0') + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    document.getElementById('pvDate').textContent = today;

    function updateStampPreview() {
        var name  = (document.getElementById('si_name').value  || '').toUpperCase();
        var phone = (document.getElementById('si_phone').value || '').toUpperCase();
        var pobox = (document.getElementById('si_pobox').value || '').toUpperCase();
        if (phone && phone.indexOf('TEL') === -1) phone = 'TEL: ' + phone;
        document.getElementById('pvName').textContent  = name;
        document.getElementById('pvPhone').textContent = phone;
        document.getElementById('pvPobox').textContent = pobox;
    }

    ['si_name','si_phone','si_pobox'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', updateStampPreview);
    });
    updateStampPreview();
})();
</script>

<script>
(function () {
    var fields = {
        name:    'courier_inv_company_name',
        email:   'courier_inv_email',
        phone:   'courier_inv_phone',
        address: 'courier_inv_address',
        website: 'courier_inv_website',
        pin:     'courier_inv_pin',
        tagline: 'courier_inv_tagline',
    };

    function updatePreview() {
        var vals = {};
        Object.keys(fields).forEach(function(k) {
            var el = document.querySelector('[name="' + fields[k] + '"]');
            vals[k] = el ? el.value.trim() : '';
        });

        document.getElementById('prev-name').textContent    = vals.name    || '(Company name)';
        document.getElementById('prev-tagline').textContent = vals.tagline || '';

        ['email','phone','website','pin'].forEach(function(k) {
            var wrap = document.getElementById('prev-' + k);
            if (!wrap) return;
            if (vals[k]) {
                wrap.querySelector('.prev-val').textContent = vals[k];
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }
        });

        var addrWrap = document.getElementById('prev-address');
        if (vals.address) {
            addrWrap.querySelector('.prev-val').textContent = vals.address.replace(/\n/g, ', ');
            addrWrap.style.display = '';
        } else {
            addrWrap.style.display = 'none';
        }
    }

    Object.values(fields).forEach(function(name) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el) el.addEventListener('input', updatePreview);
    });

    updatePreview();
})();
</script>
