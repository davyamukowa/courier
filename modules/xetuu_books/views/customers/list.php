<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div style="padding:0 0 16px;">

    <!-- Action bar -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;margin-top:6px;">
        <form class="form-inline" method="GET" style="margin:0;">
            <input type="text" name="search" class="form-control input-sm" style="width:220px;"
                   value="<?php echo htmlspecialchars($search); ?>" placeholder="Search customers...">
            <button type="submit" class="btn btn-default btn-sm" style="margin-left:4px;"><i class="fa fa-search"></i></button>
            <?php if ($search): ?><a href="<?php echo admin_url('xetuu_books/customers'); ?>" class="btn btn-link btn-sm">Clear</a><?php endif; ?>
        </form>
        <a href="<?php echo admin_url('clients/client'); ?>" class="btn btn-success btn-sm" style="font-weight:600;">
            <i class="fa fa-plus"></i> New Customer
        </a>
    </div>

    <!-- Table -->
    <div class="panel_s">
        <div class="panel-body" style="padding:0;">
            <table class="table table-hover xb-rpt" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-right">Invoices</th>
                        <th class="text-right">Total Invoiced</th>
                        <th class="text-right">Outstanding</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:32px;">No customers found.</td></tr>
                    <?php else: foreach ($customers as $c): ?>
                    <tr>
                        <td><a href="<?php echo admin_url('clients/client/' . $c->userid); ?>" class="bold"><?php echo htmlspecialchars($c->company); ?></a></td>
                        <td><?php echo htmlspecialchars($c->email ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c->phonenumber ?? ''); ?></td>
                        <td class="text-right"><?php echo number_format($c->invoice_count); ?></td>
                        <td class="text-right"><?php echo xb_format_money($c->total_invoiced ?? 0); ?></td>
                        <td class="text-right <?php echo ($c->total_outstanding ?? 0) > 0 ? 'text-danger' : ''; ?>">
                            <?php echo xb_format_money($c->total_outstanding ?? 0); ?>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo admin_url('xetuu_books/invoices?partner_id=' . $c->userid); ?>" class="btn btn-default btn-xs" title="Invoices"><i class="fa fa-file-text-o"></i></a>
                            <a href="<?php echo admin_url('xetuu_books/reports/partner_ledger?partner_id=' . $c->userid); ?>" class="btn btn-default btn-xs" title="Ledger"><i class="fa fa-list"></i></a>
                            <a href="<?php echo admin_url('clients/client/' . $c->userid); ?>" class="btn btn-default btn-xs" title="Profile"><i class="fa fa-user"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
