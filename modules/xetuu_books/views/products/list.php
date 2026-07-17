<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$total_products = count($items);
$total_revenue  = 0;
$total_qty      = 0;
$active_count   = 0;
foreach ($stats as $s) {
    $total_revenue += (float)$s->total_revenue;
    $total_qty     += (float)$s->total_qty;
    if ($s->sale_count > 0) $active_count++;
}
?>

<div class="xb-list-page" style="padding:0 16px 16px;">

    <!-- Action bar -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:12px;margin-top:6px;">
        <a href="<?php echo admin_url('items'); ?>" class="btn btn-default btn-sm" target="_blank">
            <i class="fa fa-external-link"></i> Manage in Perfex
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="xb-kpi-grid" style="margin-bottom:12px;">
        <div class="xb-kpi-card">
            <span class="kpi-icon"><i class="fa fa-cube"></i></span>
            <div class="kpi-currency">Total Products</div>
            <div class="kpi-value"><?php echo number_format($total_products); ?></div>
            <div class="kpi-label">In Catalog</div>
        </div>
        <div class="xb-kpi-card blue">
            <span class="kpi-icon"><i class="fa fa-bar-chart"></i></span>
            <div class="kpi-currency">Active in Sales</div>
            <div class="kpi-value"><?php echo number_format($active_count); ?></div>
            <div class="kpi-label">Sold Items</div>
        </div>
        <div class="xb-kpi-card">
            <span class="kpi-icon"><i class="fa fa-shopping-cart"></i></span>
            <div class="kpi-currency">Units Sold</div>
            <div class="kpi-value"><?php echo number_format($total_qty, 1); ?></div>
            <div class="kpi-label">Qty Sold</div>
        </div>
        <div class="xb-kpi-card purple">
            <span class="kpi-icon"><i class="fa fa-money"></i></span>
            <div class="kpi-currency">Total Revenue</div>
            <div class="kpi-value"><?php echo xb_format_money($total_revenue); ?></div>
            <div class="kpi-label">From Sales</div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" style="margin-bottom:12px;display:flex;gap:8px;">
        <input type="text" name="search" class="form-control input-sm" style="max-width:300px;"
               placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-default btn-sm"><i class="fa fa-search"></i> Search</button>
        <?php if ($search): ?>
        <a href="<?php echo admin_url('xetuu_books/products'); ?>" class="btn btn-default btn-sm">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="panel_s">
        <div class="panel-body" style="padding:0;">
            <table class="table table-striped table-hover xb-rpt" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Product / Service</th>
                        <th>Unit Price</th>
                        <th class="text-right">Times Sold</th>
                        <th class="text-right">Qty Sold</th>
                        <th class="text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding:32px;color:#6b7280;">
                            No products found.
                            <?php if ($search): ?>
                            <a href="<?php echo admin_url('xetuu_books/products'); ?>">Clear search</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: foreach ($items as $item):
                    $s = $stats[$item->id] ?? null;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item->description); ?></strong>
                            <?php if (!empty($item->long_description)): ?>
                            <div style="font-size:11px;color:#9ca3af;margin-top:2px;"><?php echo htmlspecialchars(substr($item->long_description, 0, 80)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo xb_format_money($item->rate); ?></td>
                        <td class="text-right"><?php echo $s ? number_format($s->sale_count) : '—'; ?></td>
                        <td class="text-right"><?php echo $s ? number_format($s->total_qty, 1) : '—'; ?></td>
                        <td class="text-right" style="font-weight:600;"><?php echo $s ? xb_format_money($s->total_revenue) : '—'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
