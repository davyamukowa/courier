<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-workspace { margin-top: 0; }
.xb-vendors-toolbar { background:#fff; padding:15px 25px; border-bottom:1px solid #e5e7eb; margin:0 -25px 0 -25px; display:flex; justify-content:space-between; align-items:center; }
.xb-vendors-toolbar h3 { margin:0; font-weight:600; color:#111827; font-size:18px; }
.xb-table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-top:20px; }
.xb-table-wrap table { margin:0; }
.xb-table-wrap thead th { background:#f9fafb; font-size:12px; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; font-weight:600; border-bottom:2px solid #e5e7eb; padding:12px 16px; }
.xb-table-wrap tbody td { padding:14px 16px; vertical-align:middle; border-bottom:1px solid #f3f4f6; font-size:14px; color:#374151; }
.xb-table-wrap tbody tr:last-child td { border-bottom:none; }
.xb-table-wrap tbody tr:hover td { background:#fafafa; }
.xb-vendor-name { font-weight:600; color:#111827; }
.xb-avatar { width:34px; height:34px; border-radius:50%; background:#dcfce7; color:#15803d; font-weight:700; font-size:13px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; flex-shrink:0; }
.xb-empty { text-align:center; padding:60px 20px; color:#9ca3af; }
.xb-empty svg { width:48px; height:48px; fill:#d1d5db; margin-bottom:12px; display:block; margin-left:auto; margin-right:auto; }
.xb-empty p { font-size:15px; margin:0 0 16px; }
</style>

<div class="xb-workspace">
    <div class="xb-vendors-toolbar">
        <h3>Vendors</h3>
        <a href="<?php echo admin_url('xetuu_books/vendor_form'); ?>" class="btn btn-primary" style="font-weight:500;">
            <i class="fa fa-plus"></i> New Vendor
        </a>
    </div>

    <?php if (empty($vendors)): ?>
    <div class="xb-empty">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        <p>No vendors yet</p>
        <a href="<?php echo admin_url('xetuu_books/vendor_form'); ?>" class="btn btn-primary">Add your first vendor</a>
    </div>
    <?php else: ?>
    <div class="xb-table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Phone</th>
                    <th>VAT / Tax ID</th>
                    <th>Website</th>
                    <th>Since</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendors as $v): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;">
                            <span class="xb-avatar"><?php echo strtoupper(mb_substr($v->company, 0, 1)); ?></span>
                            <span class="xb-vendor-name"><?php echo htmlspecialchars($v->company); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($v->phonenumber ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($v->vat ?: '—'); ?></td>
                    <td>
                        <?php if ($v->website): ?>
                        <a href="<?php echo htmlspecialchars($v->website); ?>" target="_blank" style="color:#1a6b3a;">
                            <?php echo htmlspecialchars($v->website); ?>
                        </a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="color:#9ca3af; font-size:13px;"><?php echo date('M j, Y', strtotime($v->datecreated)); ?></td>
                    <td>
                        <a href="<?php echo admin_url('xetuu_books/vendor_form/' . $v->userid); ?>" class="btn btn-default btn-xs" style="margin-right:4px;" title="Edit"><i class="fa fa-edit"></i></a>
                        <a href="<?php echo admin_url('xetuu_books/delete_vendor/' . $v->userid); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this vendor?');" title="Delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
