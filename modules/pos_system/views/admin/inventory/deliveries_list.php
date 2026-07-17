<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$dn_status_labels = [
    'draft'     => ['label'=>'Draft',     'class'=>'label-default'],
    'confirmed' => ['label'=>'Confirmed', 'class'=>'label-warning'],
    'delivered' => ['label'=>'Delivered', 'class'=>'label-success'],
    'cancelled' => ['label'=>'Cancelled', 'class'=>'label-danger'],
];
function dn_status_label($s) {
    $map = ['draft'=>'default','confirmed'=>'warning','delivered'=>'success','cancelled'=>'danger'];
    $labels = ['draft'=>'Draft','confirmed'=>'Confirmed','delivered'=>'Delivered','cancelled'=>'Cancelled'];
    return '<span class="label label-'.($map[$s]??'default').'">'.(isset($labels[$s])?$labels[$s]:ucfirst($s)).'</span>';
}
?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => 'deliveries',
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">
<div class="row">

  <!-- Page title + totals badge -->
  <div class="col-md-12 tw-flex tw-items-start tw-justify-between tw-mb-3">
    <div>
      <h4 class="tw-my-0 tw-font-bold tw-text-xl">Delivery Notes</h4>
      <a href="<?php echo admin_url('pos_system/sales_orders'); ?>" class="text-muted" style="font-size:13px">
        &larr; Sales Orders
      </a>
    </div>
    <div class="tw-text-right">
      <div class="md:tw-flex tw-items-center tw-gap-2 tw-flex-wrap tw-justify-end">
        <div class="tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-bg-white tw-px-3 tw-py-1 tw-inline-flex tw-gap-2 tw-text-sm">
          <span class="text-success">Delivered</span>
          <span class="tw-font-medium"><?php echo $dn_stats['delivered']; ?> notes</span>
        </div>
        <div class="tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-bg-white tw-px-3 tw-py-1 tw-inline-flex tw-gap-2 tw-text-sm">
          <span class="text-muted">Total</span>
          <span class="tw-font-medium"><?php echo $total_count; ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick stats cards -->
  <div class="col-md-12 tw-mb-4">
    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-2">
      <?php foreach ($dn_stats as $st => $cnt):
        $info = $dn_status_labels[$st] ?? ['label'=>ucfirst($st),'class'=>'label-default'];
        $pct  = $total_count > 0 ? round(($cnt/$total_count)*100,1) : 0;
      ?>
      <a href="<?php echo admin_url('pos_system/inventory/deliveries?status='.$st); ?>"
         class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2 tw-px-3 tw-rounded-lg tw-no-underline hover:tw-bg-neutral-50 tw-block<?php echo $current_status===$st?' tw-ring-2 tw-ring-teal-500':''; ?>">
        <div class="tw-text-sm <?php echo str_replace('label-','text-',$info['class']); ?>">
          <?php echo $info['label']; ?>
          <span class="tw-text-xs tw-text-neutral-400 tw-ml-1">(<?php echo $pct; ?>%)</span>
        </div>
        <div class="tw-mt-1 tw-text-neutral-700 tw-font-semibold">
          <?php echo $cnt; ?> / <?php echo $total_count; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="col-md-12 tw-mb-3">
    <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
      <div class="_buttons">
        <a href="<?php echo admin_url('pos_system/inv_form/delivery'); ?>" class="btn btn-primary">
          <i class="fa fa-plus"></i> New Delivery Note
        </a>
      </div>
      <div class="tw-ml-auto tw-flex tw-items-center tw-gap-2">
        <form method="GET" action="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="tw-flex tw-gap-2 tw-items-center">
          <input type="text" name="search" class="form-control input-sm" placeholder="Search DN # or customer…"
                 value="<?php echo htmlspecialchars($current_search); ?>" style="width:200px">
          <select name="status" class="form-control input-sm" style="width:140px">
            <option value="">All Status</option>
            <?php foreach ($dn_status_labels as $v=>$info): ?>
            <option value="<?php echo $v; ?>" <?php echo $current_status===$v?'selected':''; ?>><?php echo $info['label']; ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i></button>
          <?php if ($current_search || $current_status): ?>
          <a href="<?php echo admin_url('pos_system/inventory/deliveries'); ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i></a>
          <?php endif; ?>
        </form>
        <button type="button" class="btn btn-default btn-sm btn-with-tooltip"
                data-toggle="tooltip" title="Toggle split view"
                onclick="dnToggleSplit(); return false;">
          <i class="fa fa-angle-double-right" id="dn-split-icon"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- LEFT: table -->
  <div class="col-md-12" id="small-table">
    <div class="panel_s">
      <div class="panel-body panel-table-full">
        <table class="table table-deliveries table-hover" style="margin:0">
          <thead>
            <tr>
              <th>DN #</th>
              <th>Date</th>
              <th>Customer</th>
              <th class="dn-hide-split">Branch</th>
              <th style="text-align:right">Total</th>
              <th style="text-align:center">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($deliveries)): ?>
            <tr>
              <td colspan="6" class="text-center" style="padding:40px;color:#999">
                <i class="fa fa-truck" style="font-size:30px;display:block;margin-bottom:10px"></i>
                No delivery notes found.
                <br><a href="<?php echo admin_url('pos_system/inv_form/delivery'); ?>" class="btn btn-primary btn-sm" style="margin-top:12px">Create First Delivery</a>
              </td>
            </tr>
            <?php else: ?>
            <?php foreach ($deliveries as $d): ?>
            <tr onclick="dnLoadPanel(<?php echo (int)$d['id']; ?>); return false;"
                style="cursor:pointer" id="dn-row-<?php echo (int)$d['id']; ?>">
              <td>
                <a href="#" onclick="dnLoadPanel(<?php echo (int)$d['id']; ?>); return false;" class="tw-font-medium">
                  <?php echo htmlspecialchars($d['delivery_number'] ?: 'DN-'.$d['id']); ?>
                </a>
              </td>
              <td><?php echo $d['delivery_date'] ? date('Y-m-d', strtotime($d['delivery_date'])) : date('Y-m-d', strtotime($d['date_created'])); ?></td>
              <td><?php echo htmlspecialchars($d['customer_name'] ?: '—'); ?></td>
              <td class="dn-hide-split"><?php echo htmlspecialchars($d['branch_name'] ?: '—'); ?></td>
              <td style="text-align:right;font-weight:600"><?php echo number_format((float)$d['total_amount'],2); ?></td>
              <td style="text-align:center"><?php echo dn_status_label($d['status']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="panel-body-footer">
          <span class="text-muted" style="font-size:12px"><?php echo count($deliveries); ?> record(s)</span>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: detail panel -->
  <div class="col-md-7 small-table-right-col hide">
    <div id="dn-detail-panel">
      <div class="text-center" style="padding:80px 20px;color:#aaa">
        <i class="fa fa-truck" style="font-size:40px;margin-bottom:12px;display:block"></i>
        <p>Select a Delivery Note to view details</p>
      </div>
    </div>
  </div>

</div>
</div>
</div>
</div>

<?php init_tail(); ?>
<style>
.table-deliveries > tbody > tr:hover { background:#f0faf8; }
.table-deliveries > tbody > tr.dn-active { background:#e6f7f5; }
@media (min-width:801px) {
  body.small-table .dn-hide-split { display:none; }
  body.small-table .small-table-right-col.col-md-7 { padding-left:0; }
}
</style>
<script>
var DN_PANEL_BASE = '<?php echo admin_url('pos_system/delivery_panel'); ?>';
var DN_ACTIVE_ID  = 0;
var _csrf_n = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v = '<?php echo $this->security->get_csrf_hash(); ?>';

function dnLoadPanel(id) {
    DN_ACTIVE_ID = id;
    $('.table-deliveries tbody tr').removeClass('dn-active');
    $('#dn-row-' + id).addClass('dn-active');

    if (!$('body').hasClass('small-table')) { dnToggleSplit(); }

    $('#dn-detail-panel').html(
        '<div class="text-center" style="padding:60px"><i class="fa fa-spinner fa-spin fa-2x" style="color:#0d9488"></i></div>'
    );
    $('#dn-detail-panel').load(DN_PANEL_BASE + '/' + id, function(resp, status) {
        if (status === 'error') {
            $('#dn-detail-panel').html('<div class="alert alert-danger mbot20">Failed to load details.</div>');
        }
        $('html,body').animate({scrollTop:$('#dn-detail-panel').offset().top - 70}, 400);
    });
}

function dnToggleSplit() {
    var $table  = $('#small-table');
    var $detail = $('.small-table-right-col');
    var $icon   = $('#dn-split-icon');
    $('body').toggleClass('small-table');
    if ($table.hasClass('col-md-5')) {
        $table.removeClass('col-md-5').addClass('col-md-12');
        $detail.addClass('hide');
        $icon.removeClass('fa-angle-double-left').addClass('fa-angle-double-right');
    } else {
        $table.addClass('col-md-5').removeClass('col-md-12');
        $detail.removeClass('hide');
        $icon.removeClass('fa-angle-double-right').addClass('fa-angle-double-left');
    }
    $(window).trigger('resize');
}

function small_table_full_view() {
    var $table  = $('#small-table');
    var $detail = $('.small-table-right-col');
    $('body').removeClass('small-table');
    $table.addClass('hide');
    $detail.removeClass('hide col-md-7').addClass('col-md-12');
    $(window).trigger('resize');
}
</script>
