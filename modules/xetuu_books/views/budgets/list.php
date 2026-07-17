<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/budgets'); ?>">Accounting</a> &rsaquo; Budgets
    </div>

    <div class="xb-header-toolbar">
        <div><h3>Budgets</h3></div>
        <div>
            <a href="<?php echo admin_url('xetuu_books/budget_form'); ?>" class="btn btn-primary xb-btn-primary btn-sm">
                <i class="fa fa-plus"></i> New Budget
            </a>
        </div>
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">
            <table class="table table-hover" style="margin:0;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th>Budget Name</th>
                        <th>Period From</th>
                        <th>Period To</th>
                        <th class="text-right">Total Planned</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($budgets)): ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">
                        No budgets created yet. <a href="<?php echo admin_url('xetuu_books/budget_form'); ?>">Create your first budget.</a>
                    </td></tr>
                    <?php else: foreach($budgets as $b): ?>
                    <?php
                    // Get planned total for this budget
                    $CI =& get_instance();
                    $CI->db->select('COALESCE(SUM(planned_amount),0) as total');
                    $CI->db->where('budget_id', $b->id);
                    $total_row = $CI->db->get('acc_budget_lines')->row();
                    $planned   = $total_row ? $total_row->total : 0;
                    $state_colors = ['draft'=>'default','confirm'=>'info','validate'=>'success','done'=>'primary','cancel'=>'danger'];
                    ?>
                    <tr>
                        <td><a href="<?php echo admin_url('xetuu_books/budget_form/'.$b->id); ?>" class="bold"><?php echo htmlspecialchars($b->name); ?></a></td>
                        <td><?php echo $b->date_from; ?></td>
                        <td><?php echo $b->date_to; ?></td>
                        <td class="text-right"><?php echo xb_format_money($planned); ?></td>
                        <td class="text-center">
                            <span class="label label-<?php echo $state_colors[$b->state]??'default'; ?>"><?php echo ucfirst($b->state); ?></span>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo admin_url('xetuu_books/budget_form/'.$b->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                            <a href="<?php echo admin_url('xetuu_books/delete_budget/'.$b->id); ?>"
                               class="btn btn-danger btn-xs"
                               onclick="return confirm('Delete this budget?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
