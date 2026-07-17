<?php defined('BASEPATH') or exit('No direct script access allowed');
$type_label = ($type === 'revenue') ? 'Revenue' : 'Expense';
?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        Deferred <?php echo $type_label; ?>s
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3><?php echo $title; ?></h3>
        </div>
        <div>
            <a href="<?php echo admin_url('xetuu_books/deferred?type=revenue'); ?>"
               class="btn <?php echo $type==='revenue'?'btn-primary xb-btn-primary':'btn-default'; ?> btn-sm">Revenue</a>
            <a href="<?php echo admin_url('xetuu_books/deferred?type=expense'); ?>"
               class="btn <?php echo $type==='expense'?'btn-primary xb-btn-primary':'btn-default'; ?> btn-sm">Expense</a>
        </div>
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-right">Total Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($deferred)): ?>
                    <tr><td colspan="5" class="text-center text-muted" style="padding:30px;">
                        No deferred <?php echo strtolower($type_label); ?>s found.
                    </td></tr>
                    <?php else: foreach($deferred as $d):
                    $s_colors = ['draft'=>'default','in_progress'=>'info','closed'=>'success'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d->name); ?></td>
                        <td><?php echo $d->date_from; ?></td>
                        <td><?php echo $d->date_to; ?></td>
                        <td class="text-right"><?php echo xb_format_money($d->amount_total); ?></td>
                        <td class="text-center">
                            <span class="label label-<?php echo $s_colors[$d->state]??'default'; ?>"><?php echo ucwords(str_replace('_',' ',$d->state)); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
