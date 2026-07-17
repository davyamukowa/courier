<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Configuration / <span style="color:#111827;">Addons</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payroll Addons</h1>
        </div>
    </div>

    <div class="row">
        <!-- Upload Panel -->
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; margin-bottom:20px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px;">
                    <span class="material-symbols-outlined" style="vertical-align:-4px; color:#9333ea;">upload_file</span> Install Addon
                </div>
                <div style="background:#fdf4ff; border:1px dashed #d8b4fe; border-radius:8px; padding:20px; text-align:center; margin-bottom:16px;">
                    <span class="material-symbols-outlined" style="font-size:36px; color:#9333ea; display:block; margin-bottom:8px;">extension</span>
                    <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">Upload Country Addon ZIP</div>
                    <div style="font-size:11px; color:#9ca3af;">e.g. kenya_payroll_addon.zip</div>
                </div>
                <form action="<?php echo $base.'/payroll/config/addons'; ?>" method="post" enctype="multipart/form-data">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <div class="form-group">
                        <input type="file" name="addon_zip" accept=".zip" class="form-control" required style="padding:8px; font-size:13px;">
                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:4px;">Max 20MB · ZIP format only</span>
                    </div>
                    <button type="submit" name="upload_addon" value="1" class="btn btn-block" style="background:#9333ea; border-color:#9333ea; color:#fff; border-radius:6px; font-size:13px;">
                        <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">upload</span> Upload & Install
                    </button>
                </form>
            </div>

            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:16px;">
                <div style="font-size:12px; font-weight:700; color:#92400e; margin-bottom:8px;">Addon Package Structure</div>
                <pre style="font-size:11px; color:#78350f; background:transparent; border:none; padding:0; margin:0; white-space:pre-wrap;">your_country_addon/
├── manifest.json
├── install.php
├── YourCountryAddon.php
├── rates/
│   └── tax_bands.php
├── reports/
│   └── AnnualReturn.php
└── views/
    └── settings_tab.php</pre>
            </div>
        </div>

        <!-- Installed Addons -->
        <div class="col-md-8">
            <?php if (empty($addons)): ?>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:60px; text-align:center; color:#9ca3af;">
                <span class="material-symbols-outlined" style="font-size:48px; display:block; margin-bottom:12px;">extension_off</span>
                <div style="font-size:15px; font-weight:600; margin-bottom:6px;">No addons installed</div>
                <div style="font-size:13px;">Upload a country payroll addon to enable statutory computations and country-specific reports.</div>
            </div>
            <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($addons as $addon): ?>
            <?php $is_active = $addon->status === 'active'; ?>
            <div style="background:#fff; border:1px solid <?php echo $is_active ? '#86efac' : '#e5e7eb'; ?>; border-radius:10px; padding:20px; display:flex; align-items:flex-start; gap:16px;">
                <!-- Flag / Icon -->
                <div style="width:48px; height:48px; background:<?php echo $is_active ? '#f0fdf4' : '#f9fafb'; ?>; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:22px;">
                    <?php
                    $flags = ['KE'=>'🇰🇪','TZ'=>'🇹🇿','UG'=>'🇺🇬','NG'=>'🇳🇬','GH'=>'🇬🇭','ZA'=>'🇿🇦','MX'=>'🇲🇽','US'=>'🇺🇸'];
                    echo $flags[$addon->country_code] ?? '🌍';
                    ?>
                </div>
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                        <span style="font-size:15px; font-weight:700; color:#111827;"><?php echo htmlspecialchars($addon->name); ?></span>
                        <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $is_active ? '#f0fdf4' : '#f3f4f6'; ?>; color:<?php echo $is_active ? '#16a34a' : '#9ca3af'; ?>;">
                            <?php echo $is_active ? 'ACTIVE' : 'INACTIVE'; ?>
                        </span>
                        <span style="font-size:11px; color:#9ca3af;">v<?php echo $addon->version; ?></span>
                        <?php $atype = strtoupper($addon->addon_type ?? 'php');
                              $tcolor = $atype === 'PHP' ? '#6366f1' : '#0891b2'; ?>
                        <span style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px; background:<?php echo $tcolor; ?>1a; color:<?php echo $tcolor; ?>;"><?php echo $atype; ?></span>
                    </div>
                    <div style="font-size:12px; color:#6b7280; margin-bottom:10px;">
                        Country: <strong><?php echo $addon->country_code; ?></strong> ·
                        Addon ID: <code style="background:#f3f4f6; padding:1px 5px; border-radius:3px; font-size:11px;"><?php echo $addon->addon_id; ?></code> ·
                        Installed: <?php echo date('d M Y', strtotime($addon->installed_at)); ?>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <?php if ($is_active): ?>
                        <a href="<?php echo $base.'/payroll/config/addons/deactivate/'.$addon->id; ?>" class="btn btn-xs btn-warning" style="border-radius:4px;"
                           onclick="return confirm('Deactivate this addon?')">Deactivate</a>
                        <?php else: ?>
                        <a href="<?php echo $base.'/payroll/config/addons/activate/'.$addon->id; ?>" class="btn btn-xs btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:4px;">Activate</a>
                        <?php endif; ?>
                        <a href="<?php echo $base.'/payroll/config/addons/delete/'.$addon->id; ?>" class="btn btn-xs btn-danger" style="border-radius:4px;"
                           onclick="return confirm('Remove this addon? This cannot be undone.')">Remove</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
