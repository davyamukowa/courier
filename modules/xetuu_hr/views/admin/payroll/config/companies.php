<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Configuration / <span style="color:#111827;">Companies</span>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payroll Companies</h1>
        </div>
        <a href="<?php echo $base.'/payroll/config/companies/add'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ New Company</a>
    </div>

    <div class="row">
        <div class="col-md-<?php echo $show_form ? '8' : '12'; ?>">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:12px 16px;">Company Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Linked Client</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Country</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Currency</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Addon</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php if (empty($companies)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No payroll companies yet. <a href="<?php echo $base.'/payroll/config/companies/add'; ?>" style="color:#2563eb;">Add one</a>.</td></tr>
                    <?php else: ?>
                    <?php foreach ($companies as $co): ?>
                    <tr>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($co->name); ?></td>
                        <td style="font-size:12px; color:#6b7280;"><?php echo htmlspecialchars($co->client_name ?? '—'); ?></td>
                        <td style="font-size:12px; color:#374151;"><?php echo $co->country_code; ?></td>
                        <td style="font-size:12px; color:#374151;"><?php echo $co->currency; ?></td>
                        <td>
                            <?php $addon = array_filter($addons, fn($a) => $a->id == $co->payroll_addon_id); $addon = reset($addon); ?>
                            <?php if ($addon): ?>
                            <span style="font-size:11px; background:#f0fdf4; color:#16a34a; border-radius:4px; padding:2px 8px;"><?php echo htmlspecialchars($addon->name); ?></span>
                            <?php else: ?>
                            <span style="font-size:11px; color:#9ca3af;">No addon</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $co->active ? '#f0fdf4' : '#fef2f2'; ?>; color:<?php echo $co->active ? '#16a34a' : '#dc2626'; ?>;">
                                <?php echo $co->active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td style="text-align:right; padding-right:16px;">
                            <a href="<?php echo $base.'/payroll/config/companies/'.$co->id.'/edit'; ?>" class="btn btn-xs btn-default">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($show_form): ?>
        <div class="col-md-4">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; position:sticky; top:20px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">
                    <?php echo $edit_company ? 'Edit Company' : 'New Payroll Company'; ?>
                </div>
                <form action="<?php echo $base.'/payroll/config/companies'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <?php if ($edit_company): ?>
                    <input type="hidden" name="company_id" value="<?php echo $edit_company->id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Company Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_company->name ?? ''); ?>" placeholder="e.g. Nairobi Plastic Ltd">
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Link to Perfex Client</label>
                        <select name="client_id" class="form-control">
                            <option value="">— Own Company / None —</option>
                            <?php foreach ($clients as $cl): ?>
                            <option value="<?php echo $cl->userid; ?>"<?php echo ($edit_company->client_id ?? '') == $cl->userid ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($cl->company ?: $cl->firstname.' '.$cl->lastname); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:3px;">HR consultancies: select the client company you manage</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Country Code *</label>
                                <input type="text" name="country_code" class="form-control" maxlength="5" required value="<?php echo htmlspecialchars($edit_company->country_code ?? 'KE'); ?>" placeholder="KE">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Currency *</label>
                                <input type="text" name="currency" class="form-control" maxlength="10" required value="<?php echo htmlspecialchars($edit_company->currency ?? 'KES'); ?>" placeholder="KES">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Payroll Addon</label>
                        <select name="payroll_addon_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($addons as $a): ?>
                            <option value="<?php echo $a->id; ?>"<?php echo ($edit_company->payroll_addon_id ?? '') == $a->id ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($a->name); ?> (<?php echo $a->country_code; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;"><?php echo $addon_labels['tax_reg_number'] ?? 'Tax Registration No.'; ?></label>
                                <input type="text" name="tax_reg_number" class="form-control" value="<?php echo htmlspecialchars($edit_company->tax_reg_number ?? ''); ?>" placeholder="e.g. VAT/PIN/EIN">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;"><?php echo $addon_labels['social_sec_number'] ?? 'Social Security No.'; ?></label>
                                <input type="text" name="social_sec_number" class="form-control" value="<?php echo htmlspecialchars($edit_company->social_sec_number ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;"><?php echo $addon_labels['health_fund_number'] ?? 'Health Fund No.'; ?></label>
                                <input type="text" name="health_fund_number" class="form-control" value="<?php echo htmlspecialchars($edit_company->health_fund_number ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Active</label>
                                <select name="active" class="form-control">
                                    <option value="1"<?php echo ($edit_company->active ?? 1) == 1 ? ' selected' : ''; ?>>Yes</option>
                                    <option value="0"<?php echo ($edit_company->active ?? 1) == 0 ? ' selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:4px;">
                        <button type="submit" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
                            <i class="fa fa-save"></i> <?php echo $edit_company ? 'Update' : 'Create'; ?>
                        </button>
                        <a href="<?php echo $base.'/payroll/config/companies'; ?>" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php init_tail(); ?>
