<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.xb-report-table{width:100%;border-collapse:collapse}
.xb-report-table th,.xb-report-table td{padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:13px}
.xb-report-table th{background:#f9fafb;font-weight:600}
.xb-report-table .total-row td{font-weight:700;background:#f0fdf4;border-top:2px solid #1a6b3a}
.badge-open{background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:10px;font-size:11px}
.badge-close{background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px}
.badge-draft{background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:10px;font-size:11px}
</style>

<div class="xb-card">
    <div class="xb-card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-building-o"></i> Depreciation Schedule</span>
        <div>
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="xb-card-body">

        <?php
        // Load assets for this report
        $CI =& get_instance();
        $CI->db->select('acc_assets.*, acc_asset_models.name as model_name');
        $CI->db->from('acc_assets');
        $CI->db->join('acc_asset_models','acc_asset_models.id = acc_assets.model_id','left');
        $CI->db->order_by('acc_assets.acquisition_date','ASC');
        $assets_list = $CI->db->get()->result();
        ?>

        <?php if (empty($assets_list)): ?>
            <div class="alert alert-info">No fixed assets configured. <a href="<?php echo admin_url('xetuu_books/asset_form'); ?>">Add an asset</a>.</div>
        <?php else: ?>
        <table class="xb-report-table">
            <thead>
                <tr>
                    <th>Asset Name</th>
                    <th>Method</th>
                    <th>Acquired</th>
                    <th class="text-right">Original Value</th>
                    <th class="text-right">Depreciated</th>
                    <th class="text-right">Book Value</th>
                    <th class="text-right">Salvage Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $total_original = 0; $total_dep = 0; $total_book = 0;
            foreach ($assets_list as $a):
                $depreciated = $a->original_value - $a->book_value;
                $total_original += $a->original_value;
                $total_dep      += $depreciated;
                $total_book     += $a->book_value;
            ?>
            <tr>
                <td><a href="<?php echo admin_url('xetuu_books/asset_form/'.$a->id); ?>"><?php echo htmlspecialchars($a->name); ?></a>
                    <?php if ($a->model_name): ?><br><small class="text-muted"><?php echo htmlspecialchars($a->model_name); ?></small><?php endif; ?>
                </td>
                <td><?php echo ucfirst($a->method); ?> / <?php echo $a->method_number; ?> yrs</td>
                <td><?php echo $a->acquisition_date ?? '—'; ?></td>
                <td class="text-right"><?php echo xb_format_money($a->original_value); ?></td>
                <td class="text-right text-danger"><?php echo xb_format_money($depreciated); ?></td>
                <td class="text-right"><?php echo xb_format_money($a->book_value); ?></td>
                <td class="text-right text-muted"><?php echo xb_format_money($a->salvage_value); ?></td>
                <td><span class="badge-<?php echo $a->state; ?>"><?php echo ucfirst($a->state); ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">TOTAL</td>
                    <td class="text-right"><?php echo xb_format_money($total_original); ?></td>
                    <td class="text-right text-danger"><?php echo xb_format_money($total_dep); ?></td>
                    <td class="text-right"><?php echo xb_format_money($total_book); ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>
