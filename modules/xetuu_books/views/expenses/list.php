<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/expenses'); ?>">Vendors</a> &rsaquo; Employee Expenses
    </div>

    <div class="xb-header-toolbar">
        <div class="xb-header-title"><h3>Employee Expenses</h3></div>
        <div>
            <?php if(has_permission('accounting_bills','','create')): ?>
            <a href="<?php echo admin_url('xetuu_books/expense_form'); ?>" class="btn btn-primary xb-btn-primary btn-sm">
                <i class="fa fa-plus"></i> New Expense
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="xb-card" style="margin-bottom:12px;">
        <div class="xb-card-body" style="padding:12px 20px;">
            <form class="form-inline" method="GET">
                <div class="form-group">
                    <input type="date" name="date_from" class="form-control input-sm" value="<?php echo $filters['date_from']??''; ?>">
                </div>
                <div class="form-group" style="margin-left:6px;">
                    <input type="date" name="date_to" class="form-control input-sm" value="<?php echo $filters['date_to']??''; ?>">
                </div>
                <div class="form-group" style="margin-left:6px;">
                    <select name="state" class="form-control input-sm">
                        <option value="">All States</option>
                        <option value="draft"  <?php echo (($filters['state'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="posted" <?php echo (($filters['state'] ?? '') === 'posted') ? 'selected' : ''; ?>>Posted</option>
                    </select>
                </div>
                <div class="form-group" style="margin-left:6px;">
                    <input type="text" name="search" class="form-control input-sm" value="<?php echo $filters['search']??''; ?>" placeholder="Search...">
                </div>
                <button type="submit" class="btn btn-default btn-sm" style="margin-left:6px;"><i class="fa fa-filter"></i> Filter</button>
                <a href="<?php echo current_url(); ?>" class="btn btn-link btn-sm">Clear</a>
            </form>
        </div>
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Employee / Vendor</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($moves)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">
                        No expenses found. <a href="<?php echo admin_url('xetuu_books/expense_form'); ?>">Create one now.</a>
                    </td></tr>
                    <?php else: foreach($moves as $m): ?>
                    <tr>
                        <td><a href="<?php echo admin_url('xetuu_books/expense_form/'.$m->id); ?>" class="bold">
                            <?php echo $m->name ?: '<span class="text-muted">Draft</span>'; ?></a></td>
                        <td><?php echo $m->date; ?></td>
                        <td><?php echo $m->partner_id ? '#'.$m->partner_id : '—'; ?></td>
                        <td><?php echo htmlspecialchars($m->ref ?? ''); ?></td>
                        <td class="text-right"><?php echo xb_format_money($m->amount_total); ?></td>
                        <td class="text-center">
                            <?php $cls=$m->state==='posted'?'success':($m->state==='cancel'?'danger':'default'); ?>
                            <span class="label label-<?php echo $cls; ?>"><?php echo ucfirst($m->state); ?></span>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo admin_url('xetuu_books/expense_form/'.$m->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if(!empty($list_totals)): ?>
                <tfoot style="background:#f0fdf4;font-weight:700;">
                    <tr>
                        <td colspan="4">Total</td>
                        <td class="text-right"><?php echo xb_format_money($list_totals->amount_total??0); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
