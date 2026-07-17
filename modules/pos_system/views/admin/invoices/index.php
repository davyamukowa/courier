<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin"><?php echo _l('pos_invoices'); ?></h3>
        </div>
      </div>
    </div>

    <!-- Date filter -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <form method="GET" class="form-inline">
              <div class="form-group">
                <label><?php echo _l('from'); ?></label>
                <input type="date" name="from" class="form-control" value="<?php echo $from; ?>">
              </div>
              &nbsp;
              <div class="form-group">
                <label><?php echo _l('to'); ?></label>
                <input type="date" name="to" class="form-control" value="<?php echo $to; ?>">
              </div>
              &nbsp;
              <button type="submit" class="btn btn-default"><i class="fa fa-filter"></i> <?php echo _l('filter'); ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <table class="table dt-table" id="invoices-table">
              <thead>
                <tr>
                  <th><?php echo _l('pos_invoice_number'); ?></th>
                  <th><?php echo _l('pos_customer'); ?></th>
                  <th><?php echo _l('pos_total'); ?></th>
                  <th><?php echo _l('pos_amount_paid'); ?></th>
                  <th><?php echo _l('pos_currency'); ?></th>
                  <th><?php echo _l('status'); ?></th>
                  <th><?php echo _l('pos_perfex_invoice'); ?></th>
                  <th><?php echo _l('date_created'); ?></th>
                  <th><?php echo _l('pos_actions'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($invoices as $inv): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                  <td><?php echo htmlspecialchars($inv['customer_name'] ?? 'Walk-In'); ?></td>
                  <td><?php echo pos_format_currency($inv['total'], $inv['currency']); ?></td>
                  <td><?php echo pos_format_currency($inv['amount_paid'], $inv['currency']); ?></td>
                  <td><?php echo htmlspecialchars($inv['currency']); ?></td>
                  <td>
                    <?php
                      $status_colors = [
                          'draft'       => 'default',
                          'submitted'   => 'info',
                          'paid'        => 'success',
                          'partial'     => 'warning',
                          'cancelled'   => 'danger',
                          'credit_note' => 'primary',
                      ];
                      $sc = $status_colors[$inv['status']] ?? 'default';
                    ?>
                    <span class="label label-<?php echo $sc; ?>"><?php echo ucfirst(str_replace('_', ' ', $inv['status'])); ?></span>
                  </td>
                  <td>
                    <?php if ($inv['perfex_invoice_id']): ?>
                      <a href="<?php echo admin_url('invoices/list_invoices/' . $inv['perfex_invoice_id']); ?>" target="_blank" class="btn btn-xs btn-info">
                        <i class="fa fa-link"></i> #<?php echo $inv['perfex_invoice_id']; ?>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo _dt($inv['date_created']); ?></td>
                  <td>
                    <?php if (!in_array($inv['status'], ['cancelled', 'credit_note'])): ?>
                      <button class="btn btn-xs btn-default view-invoice"
                              data-id="<?php echo $inv['id']; ?>"
                              data-invoice='<?php echo htmlspecialchars(json_encode($inv), ENT_QUOTES); ?>'
                              title="<?php echo _l('view'); ?>">
                        <i class="fa fa-eye"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($invoices)): ?>
                <tr><td colspan="9" class="text-center text-muted"><?php echo _l('pos_no_invoices_found'); ?></td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Invoice Detail Modal -->
<div class="modal fade" id="invoiceDetailModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php echo _l('pos_invoice_details'); ?></h4>
      </div>
      <div class="modal-body" id="invoiceDetailBody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.view-invoice').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var inv = JSON.parse(this.dataset.invoice);
    var html = '<table class="table table-bordered">'
      + '<tr><td><strong>Invoice #</strong></td><td>' + inv.invoice_number + '</td></tr>'
      + '<tr><td><strong>Customer</strong></td><td>' + (inv.customer_name || 'Walk-In') + '</td></tr>'
      + '<tr><td><strong>Subtotal</strong></td><td>' + inv.subtotal + '</td></tr>'
      + '<tr><td><strong>Tax</strong></td><td>'     + inv.tax_amount + '</td></tr>'
      + '<tr><td><strong>Discount</strong></td><td>'+ inv.discount_amount + '</td></tr>'
      + '<tr><td><strong>Total</strong></td><td><strong>' + inv.total + '</strong></td></tr>'
      + '<tr><td><strong>Paid</strong></td><td>'    + inv.amount_paid + '</td></tr>'
      + '<tr><td><strong>Currency</strong></td><td>'+ inv.currency + '</td></tr>'
      + '<tr><td><strong>Status</strong></td><td>'  + inv.status + '</td></tr>'
      + '<tr><td><strong>Created</strong></td><td>' + inv.date_created + '</td></tr>'
      + '</table>';
    document.getElementById('invoiceDetailBody').innerHTML = html;
    jQuery('#invoiceDetailModal').modal('show');
  });
});
</script>

<?php init_tail(); ?>
