<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$state_map = [
    'draft'     => ['label'=>'NEW',       'cls'=>'default'],
    'computing' => ['label'=>'COMPUTING', 'cls'=>'warning'],
    'computed'  => ['label'=>'COMPUTED',  'cls'=>'info'],
    'confirmed' => ['label'=>'CONFIRMED', 'cls'=>'primary'],
    'done'      => ['label'=>'DONE',      'cls'=>'success'],
    'paid'      => ['label'=>'PAID',      'cls'=>'success'],
    'cancelled' => ['label'=>'CANCELLED', 'cls'=>'danger'],
];
$sm = $state_map[$run->state] ?? ['label'=>strtoupper($run->state),'cls'=>'default'];
$currency = $run->currency ?? 'KES';
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>

<div class="xhr-setup-page" style="padding:24px;">

    <!-- Breadcrumb + State pipeline -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> /
                <a href="<?php echo $base.'/payroll/batches'; ?>" style="color:#6b7280; text-decoration:none;">Payslip Batches</a> /
                <span style="color:#111827; font-weight:500;"><?php echo htmlspecialchars($run->name); ?></span>
            </div>
            <h1 style="font-size:20px; font-weight:700; color:#111827; margin:0;"><?php echo htmlspecialchars($run->name); ?></h1>
        </div>
        <!-- State stepper (Odoo style) -->
        <div style="display:flex; align-items:center; gap:0;">
            <?php
            $steps = ['draft'=>'NEW','computed'=>'CONFIRMED','done'=>'DONE','paid'=>'PAID'];
            $reached = false;
            foreach ($steps as $key => $label):
                $is_active = ($run->state === $key || ($run->state === 'confirmed' && $key === 'computed'));
                $is_past   = !$reached && !$is_active;
                if ($is_active) $reached = true;
            ?>
            <div style="padding:8px 20px; background:<?php echo $is_active ? '#1e3a5f' : ($is_past ? '#e5e7eb' : '#f3f4f6'); ?>;
                        color:<?php echo $is_active ? '#fff' : '#9ca3af'; ?>;
                        font-size:12px; font-weight:700; position:relative;
                        clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, 0 100%, 10px 50%);">
                <?php echo $label; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
        <?php if ($run->state === 'draft'): ?>
        <button onclick="startCompute(<?php echo $run->id; ?>)" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; border-radius:6px;">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">play_circle</span> Compute All
        </button>
        <?php endif; ?>
        <?php if ($run->state === 'computed'): ?>
        <a href="<?php echo $base.'/payroll/batches/confirm/'.$run->id; ?>" class="btn btn-primary" style="border-radius:6px;"
           onclick="return confirm('Confirm all <?php echo $run->employee_count; ?> payslips?')">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">check_circle</span> Confirm All
        </a>
        <?php endif; ?>
        <?php if (in_array($run->state, ['confirmed','done'])): ?>
        <button onclick="startEmailQueue(<?php echo $run->id; ?>)" class="btn btn-default" style="border-radius:6px;">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">email</span> Send by Email
        </button>
        <a href="<?php echo $base.'/payroll/reporting/bank_list?run_id='.$run->id; ?>" class="btn btn-default" style="border-radius:6px;">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">account_balance</span> Export Bank File
        </a>
        <a href="<?php echo $base.'/payroll/batches/mark_paid/'.$run->id; ?>" class="btn btn-warning" style="border-radius:6px;"
           onclick="return confirm('Mark batch as Paid?')">
            <span class="material-symbols-outlined" style="font-size:15px; vertical-align:-2px;">paid</span> Mark as Paid
        </a>
        <?php endif; ?>
    </div>

    <!-- Compute Progress Bar (hidden by default) -->
    <div id="compute-progress-wrap" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:20px;">
        <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px;">Computing payslips…</div>
        <div style="background:#f3f4f6; border-radius:999px; height:12px; overflow:hidden;">
            <div id="compute-progress-bar" style="background:#16a34a; height:100%; width:0%; transition:width .3s; border-radius:999px;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:6px;">
            <span id="compute-progress-label" style="font-size:12px; color:#6b7280;">0 / <?php echo $run->employee_count; ?> payslips</span>
            <span id="compute-progress-pct" style="font-size:12px; font-weight:700; color:#16a34a;">0%</span>
        </div>
    </div>

    <div class="row">
        <!-- Batch Info Card -->
        <div class="col-md-3">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:16px;">
                <div style="font-size:12px; font-weight:700; color:#9ca3af; text-transform:uppercase; margin-bottom:14px;">Batch Info</div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div>
                        <div style="font-size:11px; color:#9ca3af;">Batch Name</div>
                        <div style="font-size:14px; font-weight:700; color:#111827;"><?php echo htmlspecialchars($run->name); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#9ca3af;">Company</div>
                        <div style="font-size:13px; font-weight:600; color:#374151;"><?php echo htmlspecialchars($run->company_name ?? '—'); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#9ca3af;">Period</div>
                        <div style="font-size:13px; color:#374151;"><?php echo date('d/m/Y',strtotime($run->date_from)); ?> – <?php echo date('d/m/Y',strtotime($run->date_to)); ?></div>
                    </div>
                    <div style="border-top:1px solid #f3f4f6; padding-top:10px;">
                        <div style="font-size:22px; font-weight:800; color:#111827;"><?php echo number_format($run->employee_count); ?></div>
                        <div style="font-size:11px; color:#9ca3af;">Payslips</div>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div style="background:#1e3a5f; border-radius:10px; padding:20px; color:#fff;">
                <div style="font-size:12px; font-weight:700; color:#93c5fd; text-transform:uppercase; margin-bottom:14px;">Payroll Totals</div>
                <?php $tots = [
                    ['Gross Pay', $run->total_gross],
                    ['Total Deductions', $run->total_deductions],
                    ['Net Pay', $run->total_net],
                    ['Employer Cost', $run->total_employer],
                ]; ?>
                <?php foreach ($tots as [$lbl,$val]): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; <?php echo $lbl==='Net Pay'?'border-top:1px solid #3b5f8a;padding-top:8px;font-weight:800;':''; ?>">
                    <span style="font-size:12px; color:#93c5fd;"><?php echo $lbl; ?></span>
                    <span style="font-size:13px; font-weight:600;"><?php echo $currency.' '.number_format((float)$val,2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Payslips Table -->
        <div class="col-md-9">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:14px; font-weight:700; color:#111827;">
                        <span class="material-symbols-outlined" style="vertical-align:-4px; color:#2563eb; font-size:18px;">receipt</span>
                        <?php echo number_format(count($payslips)); ?> Payslips
                    </span>
                    <input type="text" id="slip-search" placeholder="Search employee…" class="form-control"
                           style="width:220px; border-radius:6px; font-size:13px;"
                           oninput="filterSlips(this.value)">
                </div>
                <table class="table table-hover" id="slips-table" style="margin:0;">
                    <thead><tr style="background:#f9fafb;">
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280; padding:10px 16px;"><input type="checkbox"></th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Employee</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Gross</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Deductions</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Tax</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Net Pay</th>
                        <th style="font-size:11px; text-transform:uppercase; color:#6b7280;">Status</th>
                        <th></th>
                    </tr></thead>
                    <tbody id="slips-tbody">
                    <?php
                    $sl_colors = ['draft'=>'#9ca3af','computed'=>'#2563eb','confirmed'=>'#16a34a','paid'=>'#15803d','cancelled'=>'#dc2626'];
                    foreach ($payslips as $slip):
                        $sc = $sl_colors[$slip->state] ?? '#9ca3af';
                    ?>
                    <tr data-name="<?php echo strtolower(htmlspecialchars($slip->employee_name ?? '')); ?>">
                        <td style="padding:10px 16px;" onclick="event.stopPropagation()"><input type="checkbox"></td>
                        <td style="font-size:13px;">
                            <span style="font-weight:600; color:#111827;"><?php echo htmlspecialchars($slip->employee_name ?? '—'); ?></span>
                            <span style="font-size:11px; color:#9ca3af; display:block;"><?php echo htmlspecialchars($slip->employee_number ?? ''); ?></span>
                        </td>
                        <td style="font-size:13px; color:#374151;"><?php echo number_format($slip->gross_wage, 2); ?></td>
                        <td style="font-size:13px; color:#dc2626;"><?php echo number_format($slip->total_deductions, 2); ?></td>
                        <td style="font-size:13px; color:#9333ea;"><?php echo number_format($slip->total_tax, 2); ?></td>
                        <td style="font-size:13px; font-weight:700; color:#111827;"><?php echo number_format($slip->net_wage, 2); ?></td>
                        <td>
                            <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px; background:<?php echo $sc; ?>20; color:<?php echo $sc; ?>; text-transform:uppercase;">
                                <?php echo $slip->state; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo $base.'/payroll/payslips/view/'.$slip->id; ?>" class="btn btn-xs btn-default" style="border-radius:4px;">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterSlips(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#slips-tbody tr').forEach(function(r) {
        r.style.display = r.dataset.name.includes(q) ? '' : 'none';
    });
}

function startCompute(runId) {
    document.getElementById('compute-progress-wrap').style.display = 'block';
    pollChunk(runId);
}

function pollChunk(runId) {
    fetch('<?php echo admin_url('xetuu_hr/payroll/batches/compute_chunk/'); ?>' + runId, {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(d => {
        var pct = d.progress || 0;
        document.getElementById('compute-progress-bar').style.width = pct + '%';
        document.getElementById('compute-progress-pct').textContent = pct + '%';
        document.getElementById('compute-progress-label').textContent = (d.computed||0) + ' / ' + (d.total||0) + ' payslips';
        if (!d.done) {
            setTimeout(function(){ pollChunk(runId); }, 1500);
        } else {
            document.getElementById('compute-progress-label').textContent = 'Complete! Reloading…';
            setTimeout(function(){ location.reload(); }, 1200);
        }
    });
}

function startEmailQueue(runId) {
    alert('Email queue feature coming soon. Payslips will be sent to each employee\'s email address.');
}
</script>
<?php init_tail(); ?>
