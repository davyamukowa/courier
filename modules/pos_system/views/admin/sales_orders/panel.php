<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$status_colors = [
    'draft'               => 'default',
    'confirmed'           => 'warning',
    'processing'          => 'info',
    'partially_delivered' => 'primary',
    'delivered'           => 'success',
    'cancelled'           => 'danger',
];
$status_labels = [
    'draft'               => 'Draft',
    'confirmed'           => 'Confirmed',
    'processing'          => 'Processing',
    'partially_delivered' => 'Partially Delivered',
    'delivered'           => 'Delivered',
    'cancelled'           => 'Cancelled',
];
$sc  = $status_colors[$so['status']] ?? 'default';
$sl  = $status_labels[$so['status']] ?? ucfirst($so['status']);
$can_confirm  = $so['status'] === 'draft';
$can_deliver  = in_array($so['status'], ['confirmed','processing','partially_delivered']);
$can_cancel   = in_array($so['status'], ['draft','confirmed','processing']);
$can_delete   = $so['status'] === 'draft';
?>

<div class="panel_s">
  <div class="panel-body" style="padding:0">

    <!-- Scrollable tabs (Perfex pattern) -->
    <div class="horizontal-scrollable-tabs preview-tabs-top panel-full-width-tabs">
      <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
      <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
      <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
          <li role="presentation" class="active">
            <a href="#so_tab_overview" aria-controls="so_tab_overview" role="tab" data-toggle="tab">
              <?php echo $so['so_number']; ?>
            </a>
          </li>
          <li role="presentation">
            <a href="#so_tab_items" role="tab" data-toggle="tab">
              Items <span class="badge"><?php echo count($items); ?></span>
            </a>
          </li>
          <?php if (!empty($deliveries)): ?>
          <li role="presentation">
            <a href="#so_tab_deliveries" role="tab" data-toggle="tab">
              Deliveries <span class="badge"><?php echo count($deliveries); ?></span>
            </a>
          </li>
          <?php endif; ?>
          <li role="presentation" class="tab-separator">
            <a href="#" onclick="small_table_full_view(); return false;" data-toggle="tooltip" title="Expand">
              <i class="fa fa-expand"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Status + action buttons -->
    <div class="row mbot20" style="padding:0 15px">
      <div class="col-md-4">
        <span class="label label-<?php echo $sc; ?> label-as-badge" style="font-size:12px;padding:4px 10px">
          <?php echo $sl; ?>
        </span>
      </div>
      <div class="col-md-8 _buttons sm:tw-space-x-1 tw-flex tw-items-center tw-flex-wrap tw-justify-end tw-gap-1">

        <!-- Edit -->
        <a href="<?php echo admin_url('pos_system/so_form/'.$so['id']); ?>"
           class="btn btn-default btn-with-tooltip sm:!tw-px-3"
           data-toggle="tooltip" title="Edit Sales Order">
          <i class="fa fa-pencil"></i>
        </a>

        <!-- Print dropdown -->
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">
            <i class="fa fa-print"></i> <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="javascript:window.print()">Print</a></li>
          </ul>
        </div>

        <!-- More dropdown -->
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">
            More <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
            <?php if ($can_confirm): ?>
            <li>
              <a href="<?php echo admin_url('pos_system/so_action/'.$so['id'].'?action=confirm'); ?>">
                <i class="fa fa-check text-success" style="width:16px;margin-right:4px"></i>Confirm Order
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_deliver): ?>
            <li>
              <a href="<?php echo admin_url('pos_system/inv_form/delivery?so_id='.$so['id']); ?>">
                <i class="fa fa-truck" style="width:16px;margin-right:4px"></i>Create Delivery Note
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_cancel): ?>
            <li>
              <a href="<?php echo admin_url('pos_system/so_action/'.$so['id'].'?action=cancel'); ?>">
                <i class="fa fa-ban text-warning" style="width:16px;margin-right:4px"></i>Cancel Order
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_delete || $can_cancel): ?><li class="divider"></li><?php endif; ?>
            <?php if ($can_delete): ?>
            <li>
              <a href="<?php echo admin_url('pos_system/so_action/'.$so['id'].'?action=delete'); ?>"
                 class="text-danger"
                 onclick="return confirm('Delete this Sales Order permanently?')">
                <i class="fa fa-trash" style="width:16px;margin-right:4px"></i>Delete
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Primary: Create Delivery -->
        <?php if ($can_deliver): ?>
        <a href="<?php echo admin_url('pos_system/inv_form/delivery?so_id='.$so['id']); ?>"
           class="btn btn-success mleft5">
          <i class="fa fa-truck"></i> Create Delivery
        </a>
        <?php endif; ?>

        <?php if ($can_confirm): ?>
        <a href="<?php echo admin_url('pos_system/so_action/'.$so['id'].'?action=confirm'); ?>"
           class="btn btn-success mleft5"
           onclick="return confirm('Confirm this Sales Order?')">
          <i class="fa fa-check"></i> Confirm
        </a>
        <?php endif; ?>

      </div>
    </div>

    <!-- Tab content -->
    <div class="tab-content" style="padding:0 20px 20px">

      <!-- ── Overview ──────────────────────────── -->
      <div id="so_tab_overview" class="tab-pane fade in active">

        <!-- Header: SO number + Bill To -->
        <div class="row mbot20">
          <div class="col-md-6">
            <h3 style="margin:0 0 2px;font-weight:800;color:#16a34a;font-size:18px">
              <?php echo htmlspecialchars($so['so_number']); ?>
            </h3>
          </div>
          <div class="col-md-6 text-right">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Sold To</div>
            <div style="font-weight:700;font-size:15px;margin-top:3px">
              <?php echo htmlspecialchars($so['customer_name'] ?: '—'); ?>
            </div>
            <?php if ($so['address']): ?>
            <div class="text-muted" style="font-size:12px;margin-top:3px;line-height:1.6">
              <?php echo nl2br(htmlspecialchars($so['address'])); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Meta info row -->
        <div class="row" style="border-top:1px solid #f0f0f0;padding-top:14px;margin-bottom:20px">
          <div class="col-md-6">
            <table style="font-size:13px">
              <tr>
                <td class="text-muted" style="padding-right:14px;padding-bottom:6px;white-space:nowrap">Order Date:</td>
                <td><strong><?php echo date('d M Y', strtotime($so['date'])); ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted" style="padding-right:14px;white-space:nowrap">Expected Delivery:</td>
                <td><strong><?php echo $so['expected_delivery'] ? date('d M Y', strtotime($so['expected_delivery'])) : '—'; ?></strong></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6 text-right">
            <table style="font-size:13px;margin-left:auto">
              <tr>
                <td class="text-muted" style="padding-right:14px;padding-bottom:6px;white-space:nowrap">Sales Person:</td>
                <td><strong><?php echo htmlspecialchars($so['sales_person'] ?: '—'); ?></strong></td>
              </tr>
              <tr>
                <td class="text-muted" style="padding-right:14px;white-space:nowrap">Project:</td>
                <td><strong><?php echo htmlspecialchars($so['project_name'] ?: '—'); ?></strong></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Items table -->
        <table class="table table-bordered" style="font-size:13px;margin-bottom:0">
          <thead>
            <tr style="background:#f8f9fa">
              <th style="width:28px">#</th>
              <th>Item</th>
              <th style="text-align:right;width:60px">Qty</th>
              <th style="text-align:right;width:100px">Rate</th>
              <th style="text-align:right;width:60px">Tax</th>
              <th style="text-align:right;width:110px">Amount</th>
              <th style="text-align:right;width:90px">Delivered</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:20px">No items.</td></tr>
            <?php else: ?>
            <?php foreach ($items as $i => $item): ?>
            <tr>
              <td class="text-muted"><?php echo $i+1; ?></td>
              <td>
                <strong><?php echo htmlspecialchars($item['product_name'] ?: '—'); ?></strong>
              </td>
              <td style="text-align:right"><?php echo (float)$item['qty_ordered']; ?></td>
              <td style="text-align:right"><?php echo number_format((float)$item['unit_price'], 2); ?></td>
              <td style="text-align:right"><?php echo (float)($item['tax_rate_pct']??0) > 0 ? (float)$item['tax_rate_pct'].'%' : '0%'; ?></td>
              <td style="text-align:right;font-weight:600"><?php echo number_format((float)$item['line_total'], 2); ?></td>
              <td style="text-align:right;color:#0d9488;font-weight:600">
                <?php echo (float)$item['qty_delivered']; ?> / <?php echo (float)$item['qty_ordered']; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Totals -->
        <div class="row mtop10">
          <div class="col-md-5 col-md-offset-7">
            <table class="table table-condensed" style="font-size:13px">
              <tr>
                <td class="text-muted">Subtotal</td>
                <td style="text-align:right"><?php echo number_format((float)$so['subtotal'],2); ?></td>
              </tr>
              <?php if ((float)$so['discount_amount'] > 0): ?>
              <tr>
                <td class="text-muted">Discount</td>
                <td style="text-align:right;color:#dc2626">- <?php echo number_format((float)$so['discount_amount'],2); ?></td>
              </tr>
              <?php endif; ?>
              <?php if ((float)$so['tax_amount'] > 0): ?>
              <tr>
                <td class="text-muted">Tax</td>
                <td style="text-align:right"><?php echo number_format((float)$so['tax_amount'],2); ?></td>
              </tr>
              <?php endif; ?>
              <?php if ((float)$so['shipping_fee'] > 0): ?>
              <tr>
                <td class="text-muted">Shipping</td>
                <td style="text-align:right"><?php echo number_format((float)$so['shipping_fee'],2); ?></td>
              </tr>
              <?php endif; ?>
              <tr style="border-top:2px solid #ddd">
                <td style="font-weight:700;font-size:14px">Total</td>
                <td style="text-align:right;font-weight:800;font-size:16px;color:#16a34a">
                  <?php echo number_format((float)$so['total_amount'],2); ?>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <?php if (!empty($so['notes'])): ?>
        <div style="background:#f8f9fa;border-radius:4px;padding:12px;font-size:13px;color:#555;margin-top:10px">
          <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($so['notes'])); ?>
        </div>
        <?php endif; ?>

      </div><!-- /overview -->

      <!-- ── Items tab ─────────────────────────── -->
      <div id="so_tab_items" class="tab-pane fade">
        <table class="table table-hover" style="font-size:13px">
          <thead>
            <tr>
              <th>#</th><th>Product</th>
              <th style="text-align:right">Ordered</th>
              <th style="text-align:right">Delivered</th>
              <th style="text-align:right">Remaining</th>
              <th style="text-align:right">Unit Price</th>
              <th style="text-align:right">Line Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $it): ?>
            <?php $rem = max(0, (float)$it['qty_ordered'] - (float)$it['qty_delivered']); ?>
            <tr>
              <td class="text-muted"><?php echo $i+1; ?></td>
              <td><strong><?php echo htmlspecialchars($it['product_name']??'—'); ?></strong></td>
              <td style="text-align:right"><?php echo (float)$it['qty_ordered']; ?></td>
              <td style="text-align:right;color:#0d9488;font-weight:600"><?php echo (float)$it['qty_delivered']; ?></td>
              <td style="text-align:right;<?php echo $rem>0?'color:#f59e0b':'color:#16a34a'; ?>;font-weight:600"><?php echo $rem; ?></td>
              <td style="text-align:right"><?php echo number_format((float)$it['unit_price'],2); ?></td>
              <td style="text-align:right;font-weight:600"><?php echo number_format((float)$it['line_total'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:20px">No items.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- ── Deliveries tab ────────────────────── -->
      <?php if (!empty($deliveries)): ?>
      <div id="so_tab_deliveries" class="tab-pane fade">
        <table class="table table-hover" style="font-size:13px">
          <thead>
            <tr><th>DN #</th><th>Date</th><th>Status</th><th style="text-align:right">Amount</th></tr>
          </thead>
          <tbody>
            <?php $dn_colors = ['draft'=>'default','confirmed'=>'warning','delivered'=>'success','cancelled'=>'danger']; ?>
            <?php foreach ($deliveries as $dn): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($dn['delivery_number']??'—'); ?></strong></td>
              <td><?php echo $dn['delivery_date'] ? date('d M Y', strtotime($dn['delivery_date'])) : '—'; ?></td>
              <td><span class="label label-<?php echo $dn_colors[$dn['status']]??'default'; ?>"><?php echo ucfirst($dn['status']); ?></span></td>
              <td style="text-align:right;font-weight:600"><?php echo number_format((float)$dn['total_amount'],2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if ($can_deliver): ?>
        <div class="mtop10">
          <a href="<?php echo admin_url('pos_system/inv_form/delivery?so_id='.$so['id']); ?>" class="btn btn-success">
            <i class="fa fa-truck"></i> Create Another Delivery
          </a>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div><!-- /tab-content -->
  </div><!-- /panel-body -->
</div><!-- /panel_s -->
