<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-report-table{width:100%;border-collapse:collapse}
.xb-report-table th,.xb-report-table td{padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:13px}
.xb-report-table th{background:#f9fafb;font-weight:600}
.xb-report-table .total-row td{font-weight:700;background:#f0fdf4;border-top:2px solid #1a6b3a}
.stat-pill{display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:600}
.pill-paid{background:#dcfce7;color:#16a34a}
.pill-partial{background:#fef9ec;color:#ca8a04}
.pill-not_paid{background:#fee2e2;color:#dc2626}
.pill-draft{background:#f3f4f6;color:#6b7280}
</style>

<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-bar-chart"></i> Invoice Analysis</span>
        <div>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body">
        <form class="form-inline mbot20" method="GET">
            <div class="form-group"><label>From:&nbsp;</label>
                <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $params['date_from']; ?>">
            </div>
            <div class="form-group" style="margin-left:10px;"><label>To:&nbsp;</label>
                <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $params['date_to']; ?>">
            </div>
            <button class="btn btn-primary xb-btn-primary btn-sm" style="margin-left:10px;">Apply</button>
        </form>

        <?php
        $CI =& get_instance();
        $CI->db->select('am.*, aj.name as journal_name');
        $CI->db->from('acc_moves am');
        $CI->db->join('acc_journals aj','aj.id = am.journal_id','left');
        $CI->db->where('am.move_type','out_invoice');
        $CI->db->where('am.date >=', $params['date_from']);
        $CI->db->where('am.date <=', $params['date_to']);
        $CI->db->order_by('am.date','DESC');
        $invoices = $CI->db->get()->result();

        $total_invoiced  = 0; $total_paid = 0; $total_outstanding = 0;
        $count_paid = 0; $count_partial = 0; $count_unpaid = 0;
        foreach ($invoices as $inv) {
            $total_invoiced    += $inv->amount_total;
            $total_paid        += ($inv->amount_total - $inv->amount_residual);
            $total_outstanding += $inv->amount_residual;
            if ($inv->payment_state === 'paid')    $count_paid++;
            elseif ($inv->payment_state === 'partial') $count_partial++;
            else $count_unpaid++;
        }
        ?>

        <!-- Summary Boxes -->
        <div class="row mbot20">
            <div class="col-md-3">
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:14px;text-align:center;">
                    <div style="font-size:20px;font-weight:700;color:#16a34a;"><?php echo xb_format_money($total_invoiced); ?></div>
                    <div style="font-size:12px;color:#6b7280;">Total Invoiced</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#e0e7ff;border:1px solid #a5b4fc;border-radius:6px;padding:14px;text-align:center;">
                    <div style="font-size:20px;font-weight:700;color:#4f46e5;"><?php echo xb_format_money($total_paid); ?></div>
                    <div style="font-size:12px;color:#6b7280;">Collected</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:14px;text-align:center;">
                    <div style="font-size:20px;font-weight:700;color:#dc2626;"><?php echo xb_format_money($total_outstanding); ?></div>
                    <div style="font-size:12px;color:#6b7280;">Outstanding</div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:14px;text-align:center;">
                    <div style="font-size:20px;font-weight:700;"><?php echo count($invoices); ?></div>
                    <div style="font-size:12px;color:#6b7280;">Total Invoices</div>
                </div>
            </div>
        </div>

        <!-- Invoice List -->
        <?php if (empty($invoices)): ?>
            <div class="alert alert-info">No invoices found for the selected period.</div>
        <?php else: ?>
        <table class="xb-report-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Journal</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr>
                <td><a href="<?php echo admin_url('xetuu_books/invoice_form/'.$inv->id); ?>"><?php echo htmlspecialchars($inv->name ?? 'Draft'); ?></a></td>
                <td><?php echo $inv->date; ?></td>
                <td><?php echo $inv->invoice_date_due ?? '—'; ?></td>
                <td><?php echo htmlspecialchars($inv->journal_name ?? ''); ?></td>
                <td class="text-right"><?php echo xb_format_money($inv->amount_total); ?></td>
                <td class="text-right"><?php echo xb_format_money($inv->amount_total - $inv->amount_residual); ?></td>
                <td class="text-right <?php echo $inv->amount_residual > 0 ? 'text-danger' : ''; ?>"><?php echo xb_format_money($inv->amount_residual); ?></td>
                <td><span class="stat-pill pill-<?php echo $inv->state === 'draft' ? 'draft' : ($inv->payment_state ?? 'not_paid'); ?>">
                    <?php echo $inv->state === 'draft' ? 'Draft' : ucfirst(str_replace('_',' ',$inv->payment_state ?? 'Unpaid')); ?>
                </span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4">TOTAL (<?php echo count($invoices); ?> invoices)</td>
                    <td class="text-right"><?php echo xb_format_money($total_invoiced); ?></td>
                    <td class="text-right"><?php echo xb_format_money($total_paid); ?></td>
                    <td class="text-right"><?php echo xb_format_money($total_outstanding); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>
