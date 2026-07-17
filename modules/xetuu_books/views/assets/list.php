<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/assets'); ?>">Accounting</a> &rsaquo; Fixed Assets
    </div>

    <div class="xb-header-toolbar">
        <div><h3>Fixed Assets</h3></div>
        <div>
            <a href="<?php echo admin_url('xetuu_books/asset_form'); ?>" class="btn btn-primary xb-btn-primary btn-sm">
                <i class="fa fa-plus"></i> New Asset
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/depreciation_schedule'); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-table"></i> Depreciation Schedule
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php
    $total_orig = array_sum(array_column($assets, 'original_value'));
    $total_book = array_sum(array_column($assets, 'book_value'));
    $total_dep  = $total_orig - $total_book;
    ?>
    <div class="row mbot20">
        <div class="col-md-4">
            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#16a34a;"><?php echo xb_format_money($total_orig); ?></div>
                <div style="font-size:12px;color:#6b7280;">Total Original Value</div>
            </div>
        </div>
        <div class="col-md-4">
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#dc2626;"><?php echo xb_format_money($total_dep); ?></div>
                <div style="font-size:12px;color:#6b7280;">Total Depreciation</div>
            </div>
        </div>
        <div class="col-md-4">
            <div style="background:#e0e7ff;border:1px solid #a5b4fc;border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#4f46e5;"><?php echo xb_format_money($total_book); ?></div>
                <div style="font-size:12px;color:#6b7280;">Net Book Value</div>
            </div>
        </div>
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>Asset Name</th>
                        <th>Model / Category</th>
                        <th>Method</th>
                        <th>Acquired</th>
                        <th class="text-right">Original Value</th>
                        <th class="text-right">Book Value</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($assets)): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">
                        No assets created yet. <a href="<?php echo admin_url('xetuu_books/asset_form'); ?>">Add your first asset.</a>
                    </td></tr>
                    <?php else: foreach($assets as $a):
                    $state_colors = ['draft'=>'default','open'=>'success','paused'=>'warning','close'=>'primary','cancelled'=>'danger'];
                    ?>
                    <tr>
                        <td><a href="<?php echo admin_url('xetuu_books/asset_form/'.$a->id); ?>" class="bold"><?php echo htmlspecialchars($a->name); ?></a></td>
                        <td><?php echo htmlspecialchars($a->model_name ?? '—'); ?></td>
                        <td><?php echo ucfirst($a->method); ?> / <?php echo $a->method_number; ?> yrs</td>
                        <td><?php echo $a->acquisition_date ?? '—'; ?></td>
                        <td class="text-right"><?php echo xb_format_money($a->original_value); ?></td>
                        <td class="text-right"><strong><?php echo xb_format_money($a->book_value); ?></strong></td>
                        <td class="text-center">
                            <span class="label label-<?php echo $state_colors[$a->state]??'default'; ?>"><?php echo ucfirst($a->state); ?></span>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo admin_url('xetuu_books/asset_form/'.$a->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                            <a href="<?php echo admin_url('xetuu_books/delete_asset/'.$a->id); ?>"
                               class="btn btn-danger btn-xs"
                               onclick="return confirm('Delete this asset?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
