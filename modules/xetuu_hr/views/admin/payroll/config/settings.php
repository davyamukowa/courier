<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="margin-bottom:20px;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;"><a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Config / <span style="color:#111827;">Settings</span></div>
        <h1 style="font-size:22px; font-weight:700; color:#111827; margin:0;">Payroll Settings</h1>
    </div>
    <form action="<?php echo $base.'/payroll/config/settings'; ?>" method="post">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div class="row">
            <div class="col-md-6">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; margin-bottom:20px;">
                    <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">General</div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Default Payroll Company</label>
                        <select name="default_company_id" class="form-control">
                            <option value="">— None —</option>
                            <?php foreach ($companies as $co): ?>
                            <option value="<?php echo $co->id; ?>"<?php echo $settings['default_company_id'] == $co->id ? ' selected' : ''; ?>><?php echo htmlspecialchars($co->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Payslip PDF Template</label>
                        <select name="payslip_template" class="form-control" id="payslip_template_select">
                            <?php
                            $tpl_options = [
                                'a4_standard' => ['A4 — Standard (Odoo-style)', 'Classic layout: employee info grid, earnings + deductions table, net pay row. Matches accounting payslip format.'],
                                'a4_modern'   => ['A4 — Modern (Two-column)', 'Dark header banner, earnings and deductions side-by-side, bold net pay band at the bottom.'],
                                'a4_minimal'  => ['A4 — Minimal (Clean)', 'No borders, clean lines and dividers, maximises white space. Professional and understated.'],
                                'thermal_80'  => ['Thermal — 80mm Receipt', 'Narrow monospace layout for 80mm thermal printers. Dashed separators, compact receipt style.'],
                                'thermal_58'  => ['Thermal — 58mm Receipt', 'Ultra-compact 58mm thermal layout. Very small font, only essential lines shown.'],
                            ];
                            $selected_tpl = $settings['payslip_template'] ?? 'a4_standard';
                            foreach ($tpl_options as $val => [$label, $desc]): ?>
                            <option value="<?php echo $val; ?>"<?php echo $selected_tpl === $val ? ' selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Template description hint -->
                        <?php
                        $selected_tpl = $settings['payslip_template'] ?? 'a4_standard';
                        foreach ($tpl_options as $val => [$label, $desc]): ?>
                        <span id="tpl_hint_<?php echo $val; ?>" style="font-size:11px; color:#6b7280; display:<?php echo $selected_tpl === $val ? 'block' : 'none'; ?>; margin-top:3px;">
                            <?php echo htmlspecialchars($desc); ?>
                        </span>
                        <?php endforeach; ?>
                        <script>
                        document.getElementById('payslip_template_select').addEventListener('change', function() {
                            document.querySelectorAll('[id^="tpl_hint_"]').forEach(function(el){ el.style.display='none'; });
                            var h = document.getElementById('tpl_hint_' + this.value);
                            if (h) h.style.display = 'block';
                        });
                        </script>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Batch Chunk Size</label>
                        <input type="number" name="chunk_size" class="form-control" min="10" max="500" value="<?php echo $settings['chunk_size'] ?? 200; ?>">
                        <span style="font-size:11px; color:#9ca3af; display:block; margin-top:3px;">Employees processed per AJAX tick (10–500). Lower = safer on shared hosting.</span>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Round To (decimal places)</label>
                        <input type="number" name="round_decimals" class="form-control" min="0" max="4" value="<?php echo $settings['round_decimals'] ?? 2; ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; margin-bottom:20px;">
                    <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Email Payslips</div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; color:#374151; cursor:pointer;">
                            <input type="checkbox" name="email_payslips" value="1"<?php echo !empty($settings['email_payslips']) ? ' checked' : ''; ?>>
                            Send payslips by email after batch confirm
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Email Subject</label>
                        <input type="text" name="email_subject" class="form-control" value="<?php echo htmlspecialchars($settings['email_subject'] ?? 'Your Payslip for {period}'); ?>">
                        <span style="font-size:11px; color:#9ca3af;">Tokens: {period}, {company}, {employee_name}</span>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600; color:#374151; text-transform:uppercase;">Email Body</label>
                        <textarea name="email_body" class="form-control" rows="5"><?php echo htmlspecialchars($settings['email_body'] ?? "Dear {employee_name},\n\nPlease find attached your payslip for {period}.\n\nRegards,\n{company}"); ?></textarea>
                    </div>
                </div>
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px;">
                    <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f3f4f6;">Accounting Integration</div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; color:#374151; cursor:pointer;">
                            <input type="checkbox" name="auto_journal" value="1"<?php echo !empty($settings['auto_journal']) ? ' checked' : ''; ?>>
                            Auto-create journal entry on batch confirm (Xetuu Books)
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; color:#374151; cursor:pointer;">
                            <input type="checkbox" name="auto_payment_entry" value="1"<?php echo !empty($settings['auto_payment_entry']) ? ' checked' : ''; ?>>
                            Auto-create payment entry on Mark as Paid
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top:8px;">
            <button type="submit" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;"><i class="fa fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>
<?php init_tail(); ?>
