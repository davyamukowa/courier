<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$cat_colors = ['EARN'=>'#16a34a','DED'=>'#dc2626','TAX'=>'#9333ea','NET'=>'#2563eb','EMPLOYER'=>'#d97706'];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> /
                <a href="<?php echo $base.'/payroll/config/structures'; ?>" style="color:#6b7280; text-decoration:none;">Structures</a> /
                <span style="color:#111827;"><?php echo htmlspecialchars($structure->name); ?></span>
            </div>
            <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;"><?php echo htmlspecialchars($structure->name); ?> — Salary Rules</h1>
        </div>
        <a href="<?php echo $base.'/payroll/config/structures/'.$structure->id.'/add_rule'; ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">+ Add Rule</a>
    </div>

    <div class="row">
        <div class="col-md-<?php echo $show_rule_form ? '7' : '12'; ?>">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:16px;">
                <div style="padding:12px 16px; background:#f9fafb; border-bottom:1px solid #f3f4f6; font-size:12px; color:#6b7280;">
                    Rules execute in <strong>sequence order</strong>. Available: <code>BASIC</code>, <code>GROSS</code>, <code>TAXABLE</code>, <code>TOTAL_DED</code>, <code>TOTAL_TAX</code>, <code>PRORATION</code>, <code>DAYS</code>, <code>benefit["FOOD"]</code>, <code>deduction["MORTGAGE"]</code> · Kenya addon: <code>NSSF_RATE</code>, <code>NSSF_UEL</code>, <code>SHIF_RATE</code>, <code>SHIF_MIN</code>, <code>AHL_RATE</code>, <code>PERSONAL_RELIEF</code>, <code>PAYE_BANDS</code>, <code>graduated_paye(TAXABLE, PAYE_BANDS)</code>
                </div>
                <table class="table table-hover" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;">Seq</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Code</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Name</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Category</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Formula</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">On Slip</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; text-align:center;">Active</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php if (empty($rules)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No rules yet. <a href="<?php echo $base.'/payroll/config/structures/'.$structure->id.'/add_rule'; ?>" style="color:#2563eb;">Add first rule</a>.</td></tr>
                    <?php else: ?>
                    <?php foreach ($rules as $r): $cc = $cat_colors[$r->category] ?? '#6b7280'; ?>
                    <tr>
                        <td style="padding:10px 16px; font-size:12px; color:#9ca3af; font-weight:700;"><?php echo $r->sequence; ?></td>
                        <td><code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px;"><?php echo $r->code; ?></code></td>
                        <td style="font-size:13px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($r->name); ?></td>
                        <td><span style="font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px; background:<?php echo $cc; ?>20; color:<?php echo $cc; ?>;"><?php echo $r->category; ?></span></td>
                        <td style="max-width:220px;"><code style="font-size:11px; color:#374151; word-break:break-all;"><?php echo htmlspecialchars(substr($r->amount_formula,0,80)); ?><?php echo strlen($r->amount_formula)>80?'…':''; ?></code></td>
                        <td style="text-align:center;"><?php echo $r->appears_on_payslip ? '<span style="color:#16a34a;">✓</span>' : '<span style="color:#d1d5db;">—</span>'; ?></td>
                        <td style="text-align:center;">
                            <div class="onoffswitch" style="margin: 0 auto;">
                                <input type="checkbox" data-switch-url="<?php echo $base.'/payroll/config/structures/'.$structure->id.'/toggle_rule'; ?>" name="onoffswitch" class="onoffswitch-checkbox" id="c_<?php echo $r->id; ?>" data-id="<?php echo $r->id; ?>" <?php if($r->active == 1){echo 'checked';} ?>>
                                <label class="onoffswitch-label" for="c_<?php echo $r->id; ?>"></label>
                            </div>
                        </td>
                        <td style="text-align:right; padding-right:12px;">
                            <a href="<?php echo $base.'/payroll/config/structures/'.$structure->id.'/edit/'.$r->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Category legend -->
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php foreach ($cat_colors as $cat => $cc): ?>
                <span style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:4px; background:<?php echo $cc; ?>20; color:<?php echo $cc; ?>;"><?php echo $cat; ?></span>
                <?php endforeach; ?>
                <span style="font-size:11px; color:#9ca3af; align-self:center;">· EARN = Earnings · DED = Deductions · TAX = Tax · NET = Net Pay · EMPLOYER = Employer Cost</span>
            </div>
        </div>

        <?php if ($show_rule_form): ?>
        <div class="col-md-5">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; position:sticky; top:20px;">
                <div style="font-size:14px; font-weight:700; color:#111827; margin-bottom:16px;"><?php echo $edit_rule ? 'Edit Rule' : 'New Salary Rule'; ?></div>
                <form action="<?php echo $base.'/payroll/config/structures'; ?>" method="post">
                    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="structure_id" value="<?php echo $structure->id; ?>">
                    <?php if ($edit_rule): ?><input type="hidden" name="rule_id" value="<?php echo $edit_rule->id; ?>"><?php endif; ?>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_rule->name ?? ''); ?>" placeholder="e.g. Basic Salary">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Code *</label>
                                <input type="text" name="code" class="form-control" required value="<?php echo htmlspecialchars($edit_rule->code ?? ''); ?>" placeholder="BASIC" style="text-transform:uppercase;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Category *</label>
                                <select name="category" class="form-control">
                                    <?php foreach (['EARN','DED','TAX','NET','EMPLOYER'] as $cat): ?>
                                    <option value="<?php echo $cat; ?>"<?php echo ($edit_rule->category ?? 'EARN') === $cat ? ' selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Sequence</label>
                                <input type="number" name="sequence" class="form-control" value="<?php echo $edit_rule->sequence ?? 10; ?>" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Amount Formula *
                            <span style="font-weight:400; color:#9ca3af; text-transform:none; margin-left:4px;">Symfony expression</span>
                        </label>
                        <textarea name="amount_formula" class="form-control" rows="2" required
                                  style="font-family:monospace; font-size:12px;"
                                  placeholder="e.g. BASIC or benefit['HOUSE'] or BASIC * 0.06"><?php echo htmlspecialchars($edit_rule->amount_formula ?? ''); ?></textarea>
                        <span style="font-size:11px; color:#9ca3af;">Available: BASIC, GROSS, TAXABLE, TOTAL_DED, TOTAL_TAX, PRORATION, DAYS, benefit["CODE"], deduction["CODE"] · Kenya: NSSF_RATE, NSSF_UEL, SHIF_RATE, SHIF_MIN, AHL_RATE, PERSONAL_RELIEF, PAYE_BANDS, graduated_paye(TAXABLE, PAYE_BANDS)</span>
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px; font-weight:600; color:#374151; text-transform:uppercase;">Condition Formula
                            <span style="font-weight:400; color:#9ca3af; text-transform:none;">(optional — empty = always run)</span>
                        </label>
                        <input type="text" name="condition_formula" class="form-control"
                               style="font-family:monospace; font-size:12px;"
                               value="<?php echo htmlspecialchars($edit_rule->condition_formula ?? ''); ?>"
                               placeholder="e.g. BASIC > 0">
                    </div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#374151; cursor:pointer;">
                            <input type="checkbox" name="appears_on_payslip" value="1"<?php echo ($edit_rule->appears_on_payslip ?? 1) ? ' checked' : ''; ?>>
                            Show on payslip
                        </label>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="save_rule" value="1" class="btn btn-success btn-sm" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
                            <i class="fa fa-save"></i> <?php echo $edit_rule ? 'Update Rule' : 'Add Rule'; ?>
                        </button>
                        <a href="<?php echo $base.'/payroll/config/structures/'.$structure->id.'/rules'; ?>" class="btn btn-default btn-sm" style="border-radius:6px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    // Handle on/off switch toggle
    $('.onoffswitch-checkbox').on('change', function(){
        var id = $(this).data('id');
        var active = $(this).prop('checked') ? 1 : 0;
        var url = $(this).data('switch-url');
        $.post(url, {
            id: id,
            active: active,
            [csrfData.token_name]: csrfData.hash
        }).fail(function(){
            alert_float('danger', 'Failed to toggle rule state.');
        });
    });
});
</script>
</body>
</html>
