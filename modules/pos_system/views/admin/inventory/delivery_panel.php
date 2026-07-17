<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$sc_map = ['draft'=>'default','confirmed'=>'warning','delivered'=>'success','cancelled'=>'danger'];
$sl_map = ['draft'=>'Draft','confirmed'=>'Confirmed','delivered'=>'Delivered','cancelled'=>'Cancelled'];
$sc = $sc_map[$delivery['status']] ?? 'default';
$sl = $sl_map[$delivery['status']] ?? ucfirst($delivery['status']);
$can_confirm = $delivery['status'] === 'draft';
$can_edit    = $delivery['status'] === 'draft';
$can_delete  = $delivery['status'] === 'draft';
?>

<div class="panel_s">
  <div class="panel-body" style="padding:0">

    <!-- Tabs -->
    <div class="horizontal-scrollable-tabs preview-tabs-top panel-full-width-tabs">
      <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
      <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
      <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
          <li role="presentation" class="active">
            <a href="#dn_tab_overview" role="tab" data-toggle="tab">
              <?php echo htmlspecialchars($delivery['delivery_number'] ?: 'DN-'.$delivery['id']); ?>
            </a>
          </li>
          <li role="presentation">
            <a href="#dn_tab_items" role="tab" data-toggle="tab">
              Items <span class="badge"><?php echo count($items); ?></span>
            </a>
          </li>
          <li role="presentation" class="tab-separator">
            <a href="#" onclick="small_table_full_view(); return false;" data-toggle="tooltip" title="Expand">
              <i class="fa fa-expand"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Status + actions -->
    <div class="row mbot20" style="padding:0 15px">
      <div class="col-md-4">
        <span class="label label-<?php echo $sc; ?> label-as-badge" style="font-size:12px;padding:4px 10px">
          <?php echo $sl; ?>
        </span>
      </div>
      <div class="col-md-8 _buttons sm:tw-space-x-1 tw-flex tw-items-center tw-flex-wrap tw-justify-end tw-gap-1">

        <?php if ($can_edit): ?>
        <a href="<?php echo admin_url('pos_system/inv_form/delivery/'.$delivery['id']); ?>"
           class="btn btn-default btn-with-tooltip sm:!tw-px-3" data-toggle="tooltip" title="Edit">
          <i class="fa fa-pencil"></i>
        </a>
        <?php endif; ?>

        <!-- Print dropdown -->
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-print"></i> <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="javascript:window.print()">Print</a></li>
          </ul>
        </div>

        <!-- More dropdown -->
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            More <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
            <?php if ($so): ?>
            <li>
              <a href="<?php echo admin_url('pos_system/so_form/'.$so['id']); ?>">
                <i class="fa fa-file-text text-success" style="width:16px;margin-right:4px"></i>
                View Sales Order (<?php echo htmlspecialchars($so['so_number']); ?>)
              </a>
            </li>
            <li class="divider"></li>
            <?php endif; ?>
            <?php if ($can_delete): ?>
            <li>
              <a href="#" class="text-danger"
                 onclick="dnDeleteRecord(<?php echo (int)$delivery['id']; ?>); return false;">
                <i class="fa fa-trash" style="width:16px;margin-right:4px"></i>Delete
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>

        <?php if ($can_confirm): ?>
        <a href="#" onclick="dnConfirmRecord(<?php echo (int)$delivery['id']; ?>); return false;"
           class="btn btn-success mleft5">
          <i class="fa fa-check"></i> Confirm
        </a>
        <?php endif; ?>

      </div>
    </div>

    <!-- Tab content -->
    <div class="tab-content" style="padding:0 20px 20px">

      <!-- Overview -->
      <div id="dn_tab_overview" class="tab-pane fade in active">

        <div class="row mbot20">
          <div class="col-md-6">
            <h3 style="margin:0 0 2px;font-weight:800;color:#0d9488;font-size:18px">
              <?php echo htmlspecialchars($delivery['delivery_number'] ?: 'DN-'.$delivery['id']); ?>
            </h3>
            <?php if ($so): ?>
            <div style="font-size:12px;color:#777;margin-top:4px">
              Sales Order: <a href="<?php echo admin_url('pos_system/so_form/'.$so['id']); ?>" style="color:#16a34a;font-weight:600">
                <?php echo htmlspecialchars($so['so_number']); ?>
              </a>
            </div>
            <?php endif; ?>
          </div>
          <div class="col-md-6 text-right">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Deliver To</div>
            <div style="font-weight:700;font-size:15px;margin-top:3px">
              <?php echo htmlspecialchars($delivery['customer_name'] ?: '—'); ?>
            </div>
            <?php if (!empty($delivery['delivery_address'])): ?>
            <div class="text-muted" style="font-size:12px;margin-top:3px;line-height:1.6">
              <?php echo nl2br(htmlspecialchars($delivery['delivery_address'])); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="row" style="border-top:1px solid #f0f0f0;padding-top:14px;margin-bottom:20px">
          <div class="col-md-6">
            <table style="font-size:13px">
              <tr>
                <td class="text-muted" style="padding-right:14px;padding-bottom:6px;white-space:nowrap">Delivery Date:</td>
                <td><strong><?php echo $delivery['delivery_date'] ? date('d M Y', strtotime($delivery['delivery_date'])) : '—'; ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted" style="padding-right:14px;white-space:nowrap">Branch:</td>
                <td><strong><?php echo htmlspecialchars($delivery['branch_name'] ?: '—'); ?></strong></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6 text-right">
            <?php if (!empty($delivery['receiver_name'])): ?>
            <table style="font-size:13px;margin-left:auto">
              <tr>
                <td class="text-muted" style="padding-right:14px;white-space:nowrap">Receiver:</td>
                <td><strong><?php echo htmlspecialchars($delivery['receiver_name']); ?></strong></td>
              </tr>
            </table>
            <?php endif; ?>
          </div>
        </div>

        <!-- Items table -->
        <table class="table table-bordered" style="font-size:13px;margin-bottom:0">
          <thead>
            <tr style="background:#f8f9fa">
              <th style="width:28px">#</th>
              <th>Item</th>
              <th style="text-align:right;width:70px">Qty</th>
              <th style="text-align:right;width:110px">Unit Price</th>
              <th style="text-align:right;width:110px">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px">No items.</td></tr>
            <?php else: ?>
            <?php foreach ($items as $i => $it): ?>
            <tr>
              <td class="text-muted"><?php echo $i+1; ?></td>
              <td>
                <strong><?php echo htmlspecialchars($it['product_name'] ?: '—'); ?></strong>
                <?php if (!empty($it['sku'])): ?>
                <br><small class="text-muted"><?php echo htmlspecialchars($it['sku']); ?></small>
                <?php endif; ?>
              </td>
              <td style="text-align:right"><?php echo (float)$it['qty']; ?></td>
              <td style="text-align:right"><?php echo number_format((float)$it['unit_price'],2); ?></td>
              <td style="text-align:right;font-weight:600">
                <?php echo number_format((float)$it['qty'] * (float)$it['unit_price'],2); ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Total -->
        <div class="row mtop10">
          <div class="col-md-4 col-md-offset-8">
            <table class="table table-condensed" style="font-size:13px">
              <tr style="border-top:2px solid #ddd">
                <td style="font-weight:700;font-size:14px">Total</td>
                <td style="text-align:right;font-weight:800;font-size:16px;color:#0d9488">
                  <?php echo number_format((float)$delivery['total_amount'],2); ?>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <?php if (!empty($delivery['notes'])): ?>
        <div style="background:#f8f9fa;border-radius:4px;padding:12px;font-size:13px;color:#555;margin-top:10px">
          <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($delivery['notes'])); ?>
        </div>
        <?php endif; ?>

      </div><!-- /overview -->

      <!-- Items tab -->
      <div id="dn_tab_items" class="tab-pane fade">
        <table class="table table-hover" style="font-size:13px">
          <thead>
            <tr><th>#</th><th>Product</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Amount</th></tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $it): ?>
            <tr>
              <td class="text-muted"><?php echo $i+1; ?></td>
              <td><?php echo htmlspecialchars($it['product_name']??'—'); ?></td>
              <td style="text-align:right"><?php echo (float)$it['qty']; ?></td>
              <td style="text-align:right"><?php echo number_format((float)$it['unit_price'],2); ?></td>
              <td style="text-align:right;font-weight:600"><?php echo number_format((float)$it['qty']*(float)$it['unit_price'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px">No items.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div><!-- /tab-content -->
  </div><!-- /panel-body -->
</div><!-- /panel_s -->

<script>
var _csrf_n = _csrf_n || '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v = _csrf_v || '<?php echo $this->security->get_csrf_hash(); ?>';

function dnConfirmRecord(id) {
    if (!confirm('Confirm this Delivery Note? Stock levels will be updated.')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.type = 'delivery'; d.id = id; d.action = 'confirm';
    $.post('<?php echo admin_url('pos_system/inv_action'); ?>', d, function(r) {
        if (r.success) {
            alert_float('success', r.message || 'Confirmed.');
            setTimeout(function(){ location.reload(); }, 800);
        } else {
            alert_float('danger', r.error || 'Failed.');
        }
    }, 'json');
}

function dnDeleteRecord(id) {
    if (!confirm('Delete this Delivery Note?')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.type = 'delivery'; d.id = id; d.action = 'delete';
    $.post('<?php echo admin_url('pos_system/inv_action'); ?>', d, function(r) {
        if (r.success) {
            alert_float('success', 'Deleted.');
            setTimeout(function(){ location.reload(); }, 600);
        } else {
            alert_float('danger', r.error || 'Failed.');
        }
    }, 'json');
}
</script>
