<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Ensure constants are available
if (!class_exists('Invoices_model', false)) {
    get_instance()->load->model('invoices_model');
}

$today         = date('Y-m-d');
$total_count   = count($invoices);
$paid_count    = 0; $unpaid_count = 0; $partial_count = 0; $overdue_count = 0; $draft_count = 0;
$paid_sum      = 0; $past_due_sum = 0; $outstanding_sum = 0;

foreach ($invoices as $m) {
    $status = (int)($m['status'] ?? 0);
    $total  = (float)($m['total'] ?? 0);

    if ($status == Invoices_model::STATUS_DRAFT) {
        $draft_count++;
    } elseif ($status == Invoices_model::STATUS_PAID) {
        $paid_count++; $paid_sum += $total;
    } elseif ($status == Invoices_model::STATUS_OVERDUE) {
        $overdue_count++; $past_due_sum += $total; $outstanding_sum += $total;
    } elseif ($status == Invoices_model::STATUS_PARTIALLY) {
        $partial_count++; $outstanding_sum += $total;
    } else {
        $unpaid_count++; $outstanding_sum += $total;
    }
}
$paid_pct    = $total_count ? round(($paid_count    / $total_count) * 100, 2) : 0;
$unpaid_pct  = $total_count ? round(($unpaid_count  / $total_count) * 100, 2) : 0;
$partial_pct = $total_count ? round(($partial_count / $total_count) * 100, 2) : 0;
$overdue_pct = $total_count ? round(($overdue_count / $total_count) * 100, 2) : 0;
$draft_pct   = $total_count ? round(($draft_count   / $total_count) * 100, 2) : 0;
?>

<div class="xb-list-page" style="padding: 0 16px 16px 16px;">

    <!-- Header Row -->
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;margin-top:8px;">
        <div>
            <h2 class="xb-list-title" style="font-size:24px;font-weight:600;color:#111827;margin-bottom:0;">
                Invoices
                <span style="font-size:16px;color:#6b7280;font-weight:400;margin-left:8px;">Customer Invoices</span>
            </h2>
        </div>
        <div class="xb-list-topbar-stats">
            <div class="xb-topbar-stat-badge xb-stat-green">
                <span class="xb-topbar-stat-label">Paid</span>
                <span class="xb-topbar-stat-value"><?php echo app_format_money($paid_sum, get_base_currency()->name); ?></span>
            </div>
            <div class="xb-topbar-stat-badge xb-stat-red">
                <span class="xb-topbar-stat-label">Past Due</span>
                <span class="xb-topbar-stat-value"><?php echo app_format_money($past_due_sum, get_base_currency()->name); ?></span>
            </div>
            <div class="xb-topbar-stat-badge xb-stat-yellow">
                <span class="xb-topbar-stat-label">Outstanding</span>
                <span class="xb-topbar-stat-value"><?php echo app_format_money($outstanding_sum, get_base_currency()->name); ?></span>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="xb-invoice-summary-container">
        <div class="xb-summary-card xb-summary-unpaid">
            <div class="xb-summary-card-header">
                <span class="xb-summary-card-title">Unpaid</span>
                <span class="xb-summary-card-percent">(<?php echo number_format($unpaid_pct, 1); ?>%)</span>
            </div>
            <div class="xb-summary-card-value"><?php echo $unpaid_count; ?> / <?php echo $total_count; ?></div>
        </div>
        <div class="xb-summary-card xb-summary-paid">
            <div class="xb-summary-card-header">
                <span class="xb-summary-card-title">Paid</span>
                <span class="xb-summary-card-percent">(<?php echo number_format($paid_pct, 1); ?>%)</span>
            </div>
            <div class="xb-summary-card-value"><?php echo $paid_count; ?> / <?php echo $total_count; ?></div>
        </div>
        <div class="xb-summary-card xb-summary-partial">
            <div class="xb-summary-card-header">
                <span class="xb-summary-card-title">Partially Paid</span>
                <span class="xb-summary-card-percent">(<?php echo number_format($partial_pct, 1); ?>%)</span>
            </div>
            <div class="xb-summary-card-value"><?php echo $partial_count; ?> / <?php echo $total_count; ?></div>
        </div>
        <div class="xb-summary-card xb-summary-overdue">
            <div class="xb-summary-card-header">
                <span class="xb-summary-card-title">Overdue</span>
                <span class="xb-summary-card-percent">(<?php echo number_format($overdue_pct, 1); ?>%)</span>
            </div>
            <div class="xb-summary-card-value"><?php echo $overdue_count; ?> / <?php echo $total_count; ?></div>
        </div>
        <div class="xb-summary-card xb-summary-draft">
            <div class="xb-summary-card-header">
                <span class="xb-summary-card-title">Draft</span>
                <span class="xb-summary-card-percent">(<?php echo number_format($draft_pct, 1); ?>%)</span>
            </div>
            <div class="xb-summary-card-value"><?php echo $draft_count; ?> / <?php echo $total_count; ?></div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="xb-list-actionbar" style="border-top:1px solid #e5e7eb;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;">
        <div class="xb-list-actions-left" style="flex:1;">
            <div class="xb-list-filters">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
                    All Invoices
                </span>
                <span>Unpaid</span>
                <span>Paid</span>
                <span>Overdue</span>
                <span>Draft</span>
            </div>
        </div>
        <div class="xb-list-pagination" style="margin-right:16px;">
            <div class="xb-list-search" style="width:250px;">
                <input type="text" placeholder="Search invoices...">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            </div>
            <div class="xb-list-pager">
                1-<?php echo min(20, $total_count); ?> / <?php echo $total_count; ?>
                <span>&lt;</span>
                <span>&gt;</span>
            </div>
        </div>
        <div class="xb-list-actions-right">
            <a href="<?php echo admin_url('xetuu_books/invoice_form'); ?>" class="xb-btn-list-primary large">NEW INVOICE</a>
        </div>
    </div>

    <!-- Table -->
    <div class="xb-list-table-container" style="border:1px solid #e5e7eb;border-top:none;">
        <table class="xb-list-table">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="width:30px;"><div class="xb-list-checkbox"></div></th>
                    <th>Number</th>
                    <th>Customer</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th class="num">Subtotal</th>
                    <th class="num">Total</th>
                    <th class="col-sticky col-status" style="width:120px;">Status</th>
                    <th class="col-sticky col-options" style="width:30px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:#6b7280;">
                            No invoices found. <a href="<?php echo admin_url('xetuu_books/invoice_form'); ?>" style="color:#16a34a;">Create your first invoice &rarr;</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $m):
                        $inv_obj = (object)$m;
                        $inv_num = format_invoice_number($inv_obj);
                        $status  = (int)$m['status'];
                        $due     = $m['duedate'] ?? null;
                        $is_overdue = ($status == Invoices_model::STATUS_OVERDUE);
                        $due_text = '';
                        if ($due && $status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_DRAFT) {
                            $diff = (strtotime($due) - time()) / 86400;
                            if ($diff < 0) {
                                $due_text = abs(round($diff)) . ' days ago';
                            } else {
                                $due_text = 'In ' . round($diff) . ' days';
                            }
                        }
                    ?>
                    <tr onclick="window.location='<?php echo admin_url('xetuu_books/invoice_form/' . $m['id']); ?>'" style="cursor:pointer;">
                        <td onclick="event.stopPropagation();"><div class="xb-list-checkbox"></div></td>
                        <td>
                            <a href="<?php echo admin_url('xetuu_books/invoice_form/' . $m['id']); ?>"
                               style="color:#374151;font-weight:600;text-decoration:none;">
                                <?php echo $inv_num ?: ('INV-' . $m['id']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($m['client_name'] ?? '—'); ?></td>
                        <td><?php echo $m['date'] ? _d($m['date']) : '—'; ?></td>
                        <td class="<?php echo $is_overdue ? 'xb-list-overdue' : ''; ?>">
                            <?php echo $due ? (_d($due) . ($due_text ? '<br><small style="color:#9ca3af;">' . $due_text . '</small>' : '')) : '—'; ?>
                        </td>
                        <td class="num"><?php echo app_format_money($m['subtotal'] ?? 0, $m['currency_name'] ?? ''); ?></td>
                        <td class="num" style="font-weight:600;"><?php echo app_format_money($m['total'] ?? 0, $m['currency_name'] ?? ''); ?></td>
                        <td class="col-sticky col-status">
                            <?php echo format_invoice_status($status); ?>
                        </td>
                        <td class="col-sticky col-options" onclick="event.stopPropagation();">
                            <div class="dropdown">
                                <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="<?php echo admin_url('xetuu_books/invoice_form/' . $m['id']); ?>"><i class="fa fa-pencil"></i> Edit</a></li>
                                    <li><a href="<?php echo admin_url('invoices/pdf/' . $m['id']); ?>" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
                                    <?php if ($status == Invoices_model::STATUS_DRAFT || $status == Invoices_model::STATUS_UNPAID): ?>
                                    <li class="divider"></li>
                                    <li><a href="<?php echo admin_url('xetuu_books/delete_invoice/' . $m['id']); ?>"
                                           onclick="return confirm('Delete this invoice?');"
                                           style="color:#dc2626;"><i class="fa fa-trash"></i> Delete</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
