<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_edit   = isset($invoice) && $invoice;
$title_str = $is_edit ? 'Edit Invoice — ' . htmlspecialchars($invoice->number) : 'New Purchase Invoice';
$save_url  = admin_url('xetuu_books/save_purchase_invoice' . ($is_edit ? '/' . $invoice->id : ''));
$stage_num = $is_edit ? ($invoice->payment_status === 'paid' ? 4 : ($invoice->payment_status === 'partially_paid' ? 3 : 2)) : 1;

// Stage labels: 1=Draft, 2=Submitted, 3=Partial, 4=Paid
$stages = ['Draft', 'Submitted', 'Partial Pay', 'Paid'];
?>

<div class="xb-form-page">

  <!-- ── Header ─────────────────────────────────────────────── -->
  <div class="xb-form-header">
    <div class="xb-form-header-left">
      <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="btn btn-default btn-sm">
        <i class="fa fa-arrow-left"></i>
      </a>
      <div>
        <p class="xb-form-breadcrumb">
          <a href="<?= admin_url('xetuu_books') ?>">Xetuu Books</a>
          <span>/</span>
          <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>">Purchase Invoices</a>
          <span>/</span> <?= $is_edit ? htmlspecialchars($invoice->number) : 'New' ?>
        </p>
        <h1 class="xb-form-title"><?= $title_str ?></h1>
      </div>
    </div>
    <div class="xb-form-actions">
      <?php if ($is_edit && $invoice->payment_status !== 'paid'): ?>
      <a href="<?= admin_url('xetuu_books/purchase_invoice_payment/' . $invoice->id) ?>"
         class="btn btn-success btn-sm"><i class="fa fa-credit-card"></i> Record Payment</a>
      <?php endif; ?>
      <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="btn btn-default">Cancel</a>
      <button type="submit" form="pur-inv-form" class="xb-save-btn">
        <i class="fa fa-floppy-o"></i> Save Invoice
      </button>
    </div>
  </div>

  <!-- ── Stage bar ──────────────────────────────────────────── -->
  <div class="xb-stage-bar">
    <div class="xb-stages">
      <?php foreach ($stages as $i => $lbl): ?>
      <?php $n = $i + 1; $cls = $n < $stage_num ? 'done' : ($n == $stage_num ? 'active' : ''); ?>
      <div class="xb-stage <?= $cls ?>">
        <div class="xb-stage-wrap">
          <div class="xb-stage-dot"><?= $n < $stage_num ? '✓' : $n ?></div>
          <div class="xb-stage-label"><?= $lbl ?></div>
        </div>
        <?php if ($i < count($stages) - 1): ?><div class="xb-stage-line"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Form body ──────────────────────────────────────────── -->
  <?= form_open($save_url, ['id' => 'pur-inv-form', 'autocomplete' => 'off']) ?>
  <div class="xb-form-body">

    <!-- LEFT: main form -->
    <div class="xb-form-main">

      <!-- Tabs -->
      <div class="xb-form-tabs">
        <ul class="nav nav-tabs">
          <li class="active"><a data-toggle="tab" href="#tab-details">Details</a></li>
          <li><a data-toggle="tab" href="#tab-lines">Line Items</a></li>
          <li><a data-toggle="tab" href="#tab-notes">Notes &amp; Terms</a></li>
        </ul>
      </div>

      <div class="tab-content">

        <!-- Tab 1: Details -->
        <div class="tab-pane active" id="tab-details">
          <div class="xb-fcard">
            <div class="xb-fcard-header">Invoice Information</div>
            <div class="xb-fcard-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Vendor <span class="req">*</span></label>
                    <select name="vendor" class="form-control" required>
                      <option value="">— Select Vendor —</option>
                      <?php foreach ($vendors as $v): ?>
                      <option value="<?= $v->userid ?>"
                        <?= ($is_edit && $invoice->vendor == $v->userid) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v->company) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Vendor Invoice # <small class="text-muted">(their reference)</small></label>
                    <input type="text" name="vendor_invoice_number" class="form-control"
                           placeholder="Vendor's invoice number"
                           value="<?= $is_edit ? htmlspecialchars($invoice->vendor_invoice_number) : '' ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="xb-flabel">Invoice Date <span class="req">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" required
                           value="<?= $is_edit ? $invoice->invoice_date : date('Y-m-d') ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="xb-flabel">Due Date</label>
                    <input type="date" name="duedate" class="form-control"
                           value="<?= $is_edit ? $invoice->duedate : date('Y-m-d', strtotime('+30 days')) ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="xb-flabel">Linked Purchase Order</label>
                    <select name="pur_order" class="form-control">
                      <option value="">— None —</option>
                      <?php foreach ($purchase_orders as $po): ?>
                      <option value="<?= $po->id ?>"
                        <?= ($is_edit && $invoice->pur_order == $po->id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($po->pur_order_number) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Currency</label>
                    <select name="currency" class="form-control">
                      <?php foreach ($currencies as $c): ?>
                      <option value="<?= htmlspecialchars($c->iso_code) ?>"
                        <?= ($is_edit && $invoice->currency == $c->iso_code) ? 'selected' : ((!$is_edit && $c->isdefault) ? 'selected' : '') ?>>
                        <?= htmlspecialchars($c->iso_code) ?> — <?= htmlspecialchars($c->name) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Payment Terms</label>
                    <input type="text" name="terms" class="form-control" placeholder="e.g. Net 30"
                           value="<?= $is_edit ? htmlspecialchars($invoice->terms) : '' ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- /tab-details -->

        <!-- Tab 2: Line Items -->
        <div class="tab-pane" id="tab-lines">
          <div class="xb-fcard">
            <div class="xb-fcard-header">Invoice Line Items</div>
            <div class="xb-fcard-body" style="padding:14px;">
              <table class="xb-lineitems" id="li-table">
                <thead>
                  <tr>
                    <th style="width:35%">Description / Item</th>
                    <th style="width:8%">Qty</th>
                    <th style="width:13%">Unit Price</th>
                    <th style="width:14%">Tax</th>
                    <th style="width:13%">Amount</th>
                    <th style="width:4%"></th>
                  </tr>
                </thead>
                <tbody id="li-body">
                  <?php if ($is_edit && !empty($line_items)): ?>
                    <?php foreach ($line_items as $idx => $li): ?>
                    <tr class="li-row">
                      <td>
                        <input type="text" name="lines[<?= $idx ?>][description]" class="form-control li-desc"
                               value="<?= htmlspecialchars($li->item_name ?: $li->description) ?>" placeholder="Description">
                      </td>
                      <td><input type="number" name="lines[<?= $idx ?>][quantity]" class="form-control li-qty" step="0.01" min="0.01" value="<?= $li->quantity ?>" required></td>
                      <td><input type="number" name="lines[<?= $idx ?>][unit_price]" class="form-control li-price" step="0.01" min="0" value="<?= $li->unit_price ?>" required></td>
                      <td>
                        <select name="lines[<?= $idx ?>][tax_rate]" class="form-control li-tax">
                          <option value="0">No Tax</option>
                          <?php foreach ($taxes as $t): ?>
                          <option value="<?= $t->taxrate ?>" <?= $li->tax_rate == $t->taxrate ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t->name) ?> (<?= $t->taxrate ?>%)
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </td>
                      <td><span class="li-total text-right" style="font-weight:600;display:block;padding:6px 4px;"><?= number_format($li->total, 2) ?></span>
                          <input type="hidden" name="lines[<?= $idx ?>][total]" class="li-total-input" value="<?= $li->total ?>">
                      </td>
                      <td><button type="button" class="btn btn-xs btn-danger li-remove" title="Remove"><i class="fa fa-trash"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr class="li-row">
                      <td><input type="text" name="lines[0][description]" class="form-control li-desc" placeholder="Description / item name"></td>
                      <td><input type="number" name="lines[0][quantity]" class="form-control li-qty" step="0.01" min="0.01" value="1"></td>
                      <td><input type="number" name="lines[0][unit_price]" class="form-control li-price" step="0.01" min="0" value="0.00"></td>
                      <td>
                        <select name="lines[0][tax_rate]" class="form-control li-tax">
                          <option value="0">No Tax</option>
                          <?php foreach ($taxes as $t): ?>
                          <option value="<?= $t->taxrate ?>"><?= htmlspecialchars($t->name) ?> (<?= $t->taxrate ?>%)</option>
                          <?php endforeach; ?>
                        </select>
                      </td>
                      <td><span class="li-total text-right" style="font-weight:600;display:block;padding:6px 4px;">0.00</span>
                          <input type="hidden" name="lines[0][total]" class="li-total-input" value="0">
                      </td>
                      <td><button type="button" class="btn btn-xs btn-danger li-remove" title="Remove"><i class="fa fa-trash"></i></button></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
              <div class="xb-li-add">
                <button type="button" class="btn btn-default btn-sm" id="add-line">
                  <i class="fa fa-plus"></i> Add Line
                </button>
              </div>

              <!-- Totals -->
              <div class="xb-totals">
                <div class="xb-total-row">
                  <label>Subtotal</label>
                  <span id="tot-subtotal">0.00</span>
                </div>
                <div class="xb-total-row">
                  <label>Tax</label>
                  <span id="tot-tax">0.00</span>
                </div>
                <div class="xb-total-row grand">
                  <label><strong>Total</strong></label>
                  <span id="tot-total">0.00</span>
                </div>
              </div>

              <input type="hidden" name="subtotal" id="inp-subtotal" value="0">
              <input type="hidden" name="tax" id="inp-tax" value="0">
              <input type="hidden" name="total" id="inp-total" value="0">
            </div>
          </div>
        </div><!-- /tab-lines -->

        <!-- Tab 3: Notes & Terms -->
        <div class="tab-pane" id="tab-notes">
          <div class="xb-fcard">
            <div class="xb-fcard-header">Notes &amp; Terms</div>
            <div class="xb-fcard-body">
              <div class="form-group">
                <label class="xb-flabel">Vendor Note</label>
                <textarea name="vendor_note" class="form-control" rows="3" placeholder="Note visible to vendor"><?= $is_edit ? htmlspecialchars($invoice->vendor_note) : '' ?></textarea>
              </div>
              <div class="form-group">
                <label class="xb-flabel">Internal Note</label>
                <textarea name="adminnote" class="form-control" rows="3" placeholder="Internal memo (not visible to vendor)"><?= $is_edit ? htmlspecialchars($invoice->adminnote) : '' ?></textarea>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /tab-content -->
    </div><!-- /xb-form-main -->

    <!-- RIGHT: sidebar -->
    <div class="xb-form-sidebar">

      <!-- Document Summary -->
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Document Summary</div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Status</span>
          <span class="xb-sb-value">
            <?php if ($is_edit): ?>
              <?php
              $ps_map = ['paid'=>['paid','Paid'], 'partially_paid'=>['pending','Partially Paid'], 'unpaid'=>['draft','Unpaid']];
              [$bc, $bl] = $ps_map[$invoice->payment_status] ?? ['draft','Draft'];
              ?>
              <span class="xb-status-badge <?= $bc ?>"><?= $bl ?></span>
            <?php else: ?>
              <span class="xb-status-badge draft">New Draft</span>
            <?php endif; ?>
          </span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Vendor</span>
          <span class="xb-sb-value" id="sb-vendor"><?= $is_edit ? htmlspecialchars($invoice->vendor_name ?? '—') : '—' ?></span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Invoice Date</span>
          <span class="xb-sb-value"><?= $is_edit ? _d($invoice->invoice_date) : date_format(date_create(), 'd M Y') ?></span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Due Date</span>
          <span class="xb-sb-value" id="sb-due"><?= $is_edit && $invoice->duedate ? _d($invoice->duedate) : '—' ?></span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Total</span>
          <span class="xb-sb-value" id="sb-total" style="color:#1a6b3a;"><?= $is_edit ? xb_format_money($invoice->total) : '—' ?></span>
        </div>
        <?php if ($is_edit && $invoice->pur_order_number): ?>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Purchase Order</span>
          <span class="xb-sb-value"><?= htmlspecialchars($invoice->pur_order_number) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Purchase Workflow navigation -->
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Purchase Workflow</div>
        <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
          Purchase Requests
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_quotations') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm1 7h-4V7.5L14.5 9H15zm-2 9H7v-2h6v2zm2-4H7v-2h8v2z"/></svg>
          Quotations
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
          Purchase Orders
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="xb-sb-nav-link active">
          <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>
          Purchase Invoices
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2z"/></svg>
          Contracts
        </a>
      </div>

      <!-- GL Info (edit only) -->
      <?php if ($is_edit && $invoice->gl_state): ?>
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Accounting / GL</div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Journal Status</span>
          <span class="xb-sb-value">
            <?php $gs_map = ['draft'=>'draft','posted'=>'approved','cancel'=>'pending']; ?>
            <span class="xb-status-badge <?= $gs_map[$invoice->gl_state] ?? 'draft' ?>"><?= ucfirst($invoice->gl_state) ?></span>
          </span>
        </div>
        <?php if ($invoice->gl_ref): ?>
        <div class="xb-sb-row">
          <span class="xb-sb-label">GL Reference</span>
          <span class="xb-sb-value"><?= htmlspecialchars($invoice->gl_ref) ?></span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div><!-- /xb-form-sidebar -->
  </div><!-- /xb-form-body -->
  </form>
</div><!-- /xb-form-page -->

<script>
(function() {
  var rowIdx = <?= $is_edit && !empty($line_items) ? count($line_items) : 1 ?>;

  function calcRow(row) {
    var qty   = parseFloat($(row).find('.li-qty').val())   || 0;
    var price = parseFloat($(row).find('.li-price').val()) || 0;
    var tax   = parseFloat($(row).find('.li-tax').val())   || 0;
    var sub   = qty * price;
    var taxAmt = sub * tax / 100;
    var total = sub + taxAmt;
    $(row).find('.li-total').text(total.toFixed(2));
    $(row).find('.li-total-input').val(total.toFixed(2));
    calcTotals();
  }

  function calcTotals() {
    var subtotal = 0, taxTotal = 0;
    $('#li-body .li-row').each(function() {
      var qty   = parseFloat($(this).find('.li-qty').val())   || 0;
      var price = parseFloat($(this).find('.li-price').val()) || 0;
      var tax   = parseFloat($(this).find('.li-tax').val())   || 0;
      var sub   = qty * price;
      subtotal += sub;
      taxTotal += sub * tax / 100;
    });
    var total = subtotal + taxTotal;
    $('#tot-subtotal').text(subtotal.toFixed(2));
    $('#tot-tax').text(taxTotal.toFixed(2));
    $('#tot-total').text(total.toFixed(2));
    $('#inp-subtotal').val(subtotal.toFixed(2));
    $('#inp-tax').val(taxTotal.toFixed(2));
    $('#inp-total').val(total.toFixed(2));
    $('#sb-total').text(total.toFixed(2));
  }

  function bindRow(row) {
    $(row).find('.li-qty, .li-price, .li-tax').on('input change', function() { calcRow(row); });
    $(row).find('.li-remove').on('click', function() {
      if ($('#li-body .li-row').length > 1) { $(row).remove(); calcTotals(); }
    });
    calcRow(row);
  }

  // Bind existing rows
  $('#li-body .li-row').each(function() { bindRow(this); });

  // Add line
  $('#add-line').on('click', function() {
    var taxOpts = <?= json_encode(array_map(fn($t) => ['rate' => (float)$t->taxrate, 'name' => $t->name . ' (' . $t->taxrate . '%)'], $taxes)) ?>;
    var opts = '<option value="0">No Tax</option>';
    taxOpts.forEach(function(t) { opts += '<option value="'+t.rate+'">'+t.name+'</option>'; });
    var row = '<tr class="li-row">' +
      '<td><input type="text" name="lines['+rowIdx+'][description]" class="form-control li-desc" placeholder="Description"></td>' +
      '<td><input type="number" name="lines['+rowIdx+'][quantity]" class="form-control li-qty" step="0.01" min="0.01" value="1"></td>' +
      '<td><input type="number" name="lines['+rowIdx+'][unit_price]" class="form-control li-price" step="0.01" min="0" value="0.00"></td>' +
      '<td><select name="lines['+rowIdx+'][tax_rate]" class="form-control li-tax">'+opts+'</select></td>' +
      '<td><span class="li-total text-right" style="font-weight:600;display:block;padding:6px 4px;">0.00</span>' +
          '<input type="hidden" name="lines['+rowIdx+'][total]" class="li-total-input" value="0"></td>' +
      '<td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td>' +
      '</tr>';
    var $row = $(row).appendTo('#li-body');
    bindRow($row[0]);
    rowIdx++;
  });

  // Vendor selector → update sidebar
  $('select[name="vendor"]').on('change', function() {
    var txt = $(this).find('option:selected').text();
    $('#sb-vendor').text(txt || '—');
  });

  // Due date → update sidebar
  $('input[name="duedate"]').on('change', function() {
    $('#sb-due').text($(this).val() || '—');
  });

  calcTotals();

  // Switch to line items tab if no items
  <?php if (!$is_edit): ?>
  $('[data-toggle="tab"][href="#tab-lines"]').tab('show');
  <?php endif; ?>
})();
</script>
