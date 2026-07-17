<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$s = $cn_stats;
$total        = (int)($s->total ?? 0);
$total_amount = (float)($s->total_amount ?? 0);
$open_count   = (int)($s->open_count ?? 0);
$open_amount  = (float)($s->open_amount ?? 0);
$closed_count = (int)($s->closed_count ?? 0);
?>

<div class="xb-list-page" style="padding:0 16px 16px;">

    <!-- Action bar -->
    <?php if (staff_can('create', 'credit_notes')): ?>
    <div style="display:flex;justify-content:flex-end;margin-bottom:12px;margin-top:6px;">
        <a href="<?php echo admin_url('xetuu_books/credit_note_form'); ?>" class="btn btn-success btn-sm" style="font-weight:600;">
            <i class="fa fa-plus"></i> New Credit Note
        </a>
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="xb-kpi-grid" style="margin-bottom:12px;">
        <div class="xb-kpi-card blue">
            <span class="kpi-icon"><i class="fa fa-file-text-o"></i></span>
            <div class="kpi-currency">Total Notes</div>
            <div class="kpi-value"><?php echo number_format($total); ?></div>
            <div class="kpi-label">All Records</div>
        </div>
        <div class="xb-kpi-card">
            <span class="kpi-icon"><i class="fa fa-money"></i></span>
            <div class="kpi-currency">Total Value</div>
            <div class="kpi-value"><?php echo app_format_money($total_amount, get_base_currency()->name); ?></div>
            <div class="kpi-label">Issued Amount</div>
        </div>
        <div class="xb-kpi-card warn">
            <span class="kpi-icon"><i class="fa fa-hourglass-half"></i></span>
            <div class="kpi-currency">Open</div>
            <div class="kpi-value"><?php echo number_format($open_count); ?></div>
            <div class="kpi-label">Unused Balance</div>
        </div>
        <div class="xb-kpi-card warn">
            <span class="kpi-icon"><i class="fa fa-exclamation-triangle"></i></span>
            <div class="kpi-currency">Open Amount</div>
            <div class="kpi-value"><?php echo app_format_money($open_amount, get_base_currency()->name); ?></div>
            <div class="kpi-label">Remaining Credit</div>
        </div>
        <div class="xb-kpi-card">
            <span class="kpi-icon"><i class="fa fa-check-circle"></i></span>
            <div class="kpi-currency">Closed</div>
            <div class="kpi-value"><?php echo number_format($closed_count); ?></div>
            <div class="kpi-label">Fully Applied</div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="row">
        <div class="col-md-12" id="small-table">
            <div class="panel_s">
                <div class="panel-body panel-table-full">
                    <?php echo form_hidden('credit_note_id', $credit_note_id); ?>
                    <?php $this->load->view('admin/credit_notes/table_html'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-7 small-table-right-col">
            <div id="credit_note" class="hide"></div>
        </div>
    </div>
</div>

<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
var hidden_columns = [4, 5, 6, 7];
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initDataTable('.table-credit-notes', admin_url + 'credit_notes/table', ['undefined'], ['undefined'], {},
        [[1, 'desc'], [0, 'desc']]);
    init_credit_note();
});
</script>
