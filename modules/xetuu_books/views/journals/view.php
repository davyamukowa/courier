<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-je-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.xb-je-meta { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px; }
.xb-je-meta-item { background: #f9fafb; border-radius: 6px; padding: 12px 14px; }
.xb-je-meta-item label { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
.xb-je-meta-item span { font-size: 14px; font-weight: 600; color: #111827; }
.xb-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 700; }
.xb-badge-posted  { background: #d1fae5; color: #065f46; }
.xb-badge-draft   { background: #e0e7ff; color: #3730a3; }
.xb-badge-cancel  { background: #f3f4f6; color: #6b7280; }
.xb-je-lines { width: 100%; border-collapse: collapse; margin-top: 8px; }
.xb-je-lines th { background: #f9fafb; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
.xb-je-lines td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
.xb-je-lines .total-row td { font-weight: 700; background: #f0fdf4; border-top: 2px solid #1a6b3a; font-size: 14px; }
.xb-je-lines .balanced td { color: #065f46; }
.xb-je-lines .unbalanced td { color: #991b1b; }
.xb-debit { color: #1e40af; }
.xb-credit { color: #065f46; }
.xb-warn { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 4px; padding: 8px 14px; margin-bottom: 12px; font-size: 13px; }
.xb-ok { background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 4px; padding: 8px 14px; margin-bottom: 12px; font-size: 13px; }
</style>

<div class="xb-card">
    <div class="xb-card-body">

        <!-- Header -->
        <div class="xb-je-header">
            <div>
                <h3 style="margin:0 0 6px;font-size:20px;font-weight:700;color:#111827;">
                    <?php echo htmlspecialchars($entry->name ?? ('Move #' . $entry->id)); ?>
                    <span class="xb-badge xb-badge-<?php echo $entry->state; ?>" style="margin-left:10px;vertical-align:middle;">
                        <?php echo ucfirst($entry->state); ?>
                    </span>
                </h3>
                <div class="text-muted" style="font-size:13px;"><?php echo ucwords(str_replace('_', ' ', $entry->move_type)); ?></div>
            </div>
            <div>
                <?php if ($entry->state === 'posted'): ?>
                    <a href="<?php echo admin_url('xetuu_books/journal_entry_form/' . $entry->id); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                <?php endif; ?>
                <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                <a href="<?php echo admin_url('xetuu_books/reports/general_ledger?date_from='.urlencode($entry->date).'&date_to='.urlencode($entry->date)); ?>" class="btn btn-default btn-sm" target="_blank">
                    <i class="fa fa-list"></i> View in GL
                </a>
            </div>
        </div>

        <!-- Meta -->
        <div class="xb-je-meta">
            <div class="xb-je-meta-item">
                <label>Date</label>
                <span><?php echo _d($entry->date); ?></span>
            </div>
            <div class="xb-je-meta-item">
                <label>Reference</label>
                <span><?php echo htmlspecialchars($entry->ref ?? '—'); ?></span>
            </div>
            <?php if (!empty($entry->invoice_date_due)): ?>
            <div class="xb-je-meta-item">
                <label>Due Date</label>
                <span><?php echo _d($entry->invoice_date_due); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($entry->amount_total)): ?>
            <div class="xb-je-meta-item">
                <label>Total Amount</label>
                <span><?php echo xb_format_money($entry->amount_total); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($entry->payment_state)): ?>
            <div class="xb-je-meta-item">
                <label>Payment Status</label>
                <span><?php echo ucwords(str_replace('_', ' ', $entry->payment_state)); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($entry->amount_residual) && $entry->amount_residual != 0): ?>
            <div class="xb-je-meta-item" style="border-left: 3px solid #f59e0b;">
                <label>Outstanding</label>
                <span style="color:#b45309;"><?php echo xb_format_money($entry->amount_residual); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($entry->narration)): ?>
        <div style="margin-bottom:16px;padding:10px 14px;background:#fffbeb;border-left:3px solid #f59e0b;font-size:13px;color:#78350f;">
            <strong>Note:</strong> <?php echo htmlspecialchars($entry->narration); ?>
        </div>
        <?php endif; ?>

        <!-- Balance check -->
        <?php
        $total_debit  = 0;
        $total_credit = 0;
        foreach ($entry_lines as $l) {
            $total_debit  += (float)$l->debit;
            $total_credit += (float)$l->credit;
        }
        $is_balanced = round(abs($total_debit - $total_credit), 4) < 0.01;
        ?>
        <?php if (!$is_balanced): ?>
        <div class="xb-warn"><i class="fa fa-exclamation-triangle"></i> This entry is <strong>not balanced</strong> — Debit <?php echo xb_format_money($total_debit); ?> ≠ Credit <?php echo xb_format_money($total_credit); ?></div>
        <?php else: ?>
        <div class="xb-ok"><i class="fa fa-check-circle"></i> Entry is balanced — Total Debit = Total Credit = <?php echo xb_format_money($total_debit); ?></div>
        <?php endif; ?>

        <!-- Journal Lines -->
        <?php if (empty($entry_lines)): ?>
            <div class="alert alert-info">No journal lines found for this entry.</div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="xb-je-lines">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account</th>
                        <th>Partner</th>
                        <th>Description</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($entry_lines as $l): ?>
                    <tr>
                        <td class="text-muted"><?php echo $i++; ?></td>
                        <td>
                            <a href="<?php echo admin_url('xetuu_books/reports/general_ledger?account_id='.$l->account_id.'&date_from='.urlencode($entry->date).'&date_to='.urlencode($entry->date)); ?>" target="_blank" style="color:#1e40af;text-decoration:none;">
                                <?php echo htmlspecialchars($l->account_code . ' ' . $l->account_name); ?>
                            </a>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($l->partner_name ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($l->name ?? ''); ?></td>
                        <td class="text-right xb-debit"><?php echo $l->debit  > 0 ? xb_format_money($l->debit)  : ''; ?></td>
                        <td class="text-right xb-credit"><?php echo $l->credit > 0 ? xb_format_money($l->credit) : ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row <?php echo $is_balanced ? 'balanced' : 'unbalanced'; ?>">
                        <td colspan="4" class="text-right">TOTAL</td>
                        <td class="text-right xb-debit"><?php echo xb_format_money($total_debit); ?></td>
                        <td class="text-right xb-credit"><?php echo xb_format_money($total_credit); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>
