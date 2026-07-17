<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_edit   = isset($request) && $request;
$title_str = $is_edit ? 'Edit Request — ' . htmlspecialchars($request->pur_rq_code) : 'New Purchase Request';
$save_url  = admin_url('xetuu_books/save_purchase_request' . ($is_edit ? '/' . $request->id : ''));
$stage_num = $is_edit ? (int)$request->status : 1;
$stages    = ['Draft', 'Pending', 'Approved', 'Ordered'];
?>

<div class="xb-form-page">

  <div class="xb-form-header">
    <div class="xb-form-header-left">
      <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i></a>
      <div>
        <p class="xb-form-breadcrumb">
          <a href="<?= admin_url('xetuu_books') ?>">Xetuu Books</a> <span>/</span>
          <a href="<?= admin_url('xetuu_books/purchase_requests') ?>">Purchase Requests</a> <span>/</span>
          <?= $is_edit ? htmlspecialchars($request->pur_rq_code) : 'New' ?>
        </p>
        <h1 class="xb-form-title"><?= $title_str ?></h1>
      </div>
    </div>
    <div class="xb-form-actions">
      <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="btn btn-default">Cancel</a>
      <button type="submit" form="pur-req-form" class="xb-save-btn"><i class="fa fa-floppy-o"></i> Save Request</button>
    </div>
  </div>

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

  <?= form_open($save_url, ['id' => 'pur-req-form']) ?>
  <div class="xb-form-body">

    <div class="xb-form-main">
      <div class="xb-form-tabs">
        <ul class="nav nav-tabs">
          <li class="active"><a data-toggle="tab" href="#rq-details">Details</a></li>
          <li><a data-toggle="tab" href="#rq-items">Items Needed</a></li>
        </ul>
      </div>

      <div class="tab-content">
        <div class="tab-pane active" id="rq-details">
          <div class="xb-fcard">
            <div class="xb-fcard-header">Request Information</div>
            <div class="xb-fcard-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Request Name <span class="req">*</span></label>
                    <input type="text" name="pur_rq_name" class="form-control" required
                           placeholder="e.g. Office Supplies Q3"
                           value="<?= $is_edit ? htmlspecialchars($request->pur_rq_name) : '' ?>">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="xb-flabel">Request Date</label>
                    <input type="date" name="request_date" class="form-control"
                           value="<?= $is_edit ? $request->request_date : date('Y-m-d') ?>">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="xb-flabel">Status</label>
                    <select name="status" class="form-control">
                      <option value="1" <?= $is_edit && $request->status == 1 ? 'selected' : (!$is_edit ? 'selected' : '') ?>>Pending</option>
                      <option value="2" <?= $is_edit && $request->status == 2 ? 'selected' : '' ?>>Approved</option>
                      <option value="3" <?= $is_edit && $request->status == 3 ? 'selected' : '' ?>>Rejected</option>
                      <option value="4" <?= $is_edit && $request->status == 4 ? 'selected' : '' ?>>Ordered</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Requester</label>
                    <select name="requester" class="form-control">
                      <option value="">— Select Staff —</option>
                      <?php foreach ($staff as $s): ?>
                      <option value="<?= $s->staffid ?>"
                        <?= ($is_edit && $request->requester == $s->staffid) ? 'selected' : ((!$is_edit && $s->staffid == get_staff_user_id()) ? 'selected' : '') ?>>
                        <?= htmlspecialchars($s->firstname . ' ' . $s->lastname) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="xb-flabel">Description / Justification</label>
                    <textarea name="rq_description" class="form-control" rows="3"
                              placeholder="Explain why these items are needed"><?= $is_edit ? htmlspecialchars($request->rq_description) : '' ?></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="tab-pane" id="rq-items">
          <div class="xb-fcard">
            <div class="xb-fcard-header">Items Requested</div>
            <div class="xb-fcard-body" style="padding:14px;">
              <table class="xb-lineitems">
                <thead>
                  <tr>
                    <th style="width:40%">Item / Description</th>
                    <th style="width:10%">Qty</th>
                    <th style="width:15%">Est. Unit Price</th>
                    <th style="width:12%">Tax %</th>
                    <th style="width:14%">Est. Total</th>
                    <th style="width:5%"></th>
                  </tr>
                </thead>
                <tbody id="rq-li-body">
                  <?php if ($is_edit && !empty($line_items)): ?>
                    <?php foreach ($line_items as $idx => $li): ?>
                    <tr class="li-row">
                      <td><input type="text" name="lines[<?= $idx ?>][item_text]" class="form-control li-desc" value="<?= htmlspecialchars($li->item_text) ?>" placeholder="Item description"></td>
                      <td><input type="number" name="lines[<?= $idx ?>][quantity]" class="form-control li-qty" step="0.01" value="<?= $li->quantity ?>"></td>
                      <td><input type="number" name="lines[<?= $idx ?>][unit_price]" class="form-control li-price" step="0.01" value="<?= $li->unit_price ?>"></td>
                      <td><input type="number" name="lines[<?= $idx ?>][tax_rate]" class="form-control li-taxp" step="0.01" value="<?= $li->tax_rate ?>" placeholder="0"></td>
                      <td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;">0.00</span>
                          <input type="hidden" name="lines[<?= $idx ?>][total]" class="li-total-input" value="<?= $li->total ?>"></td>
                      <td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr class="li-row">
                      <td><input type="text" name="lines[0][item_text]" class="form-control li-desc" placeholder="Item or service needed"></td>
                      <td><input type="number" name="lines[0][quantity]" class="form-control li-qty" step="0.01" value="1"></td>
                      <td><input type="number" name="lines[0][unit_price]" class="form-control li-price" step="0.01" value="0.00"></td>
                      <td><input type="number" name="lines[0][tax_rate]" class="form-control li-taxp" step="0.01" placeholder="0" value="0"></td>
                      <td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;">0.00</span>
                          <input type="hidden" name="lines[0][total]" class="li-total-input" value="0"></td>
                      <td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
              <button type="button" class="btn btn-default btn-sm" id="rq-add-line" style="margin-top:8px;"><i class="fa fa-plus"></i> Add Item</button>
              <div class="xb-totals">
                <div class="xb-total-row grand">
                  <label><strong>Estimated Total</strong></label>
                  <span id="rq-grand">0.00</span>
                </div>
              </div>
              <input type="hidden" name="subtotal" id="rq-subtotal" value="0">
              <input type="hidden" name="total_tax" id="rq-tax" value="0">
              <input type="hidden" name="total" id="rq-total" value="0">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="xb-form-sidebar">
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Document Summary</div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Status</span>
          <span class="xb-sb-value">
            <?php if ($is_edit): ?>
              <?php $sm = [1=>['pending','Pending'], 2=>['approved','Approved'], 3=>['pending','Rejected'], 4=>['paid','Ordered']]; [$bc,$bl] = $sm[$request->status]??['draft','Draft']; ?>
              <span class="xb-status-badge <?= $bc ?>"><?= $bl ?></span>
            <?php else: ?>
              <span class="xb-status-badge draft">New</span>
            <?php endif; ?>
          </span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Requester</span>
          <span class="xb-sb-value"><?= $is_edit && $request->requester_name ? htmlspecialchars($request->requester_name) : get_staff_full_name(get_staff_user_id()) ?></span>
        </div>
        <div class="xb-sb-row">
          <span class="xb-sb-label">Est. Total</span>
          <span class="xb-sb-value" id="rq-sb-total" style="color:#1a6b3a;"><?= $is_edit ? xb_format_money($request->total) : '—' ?></span>
        </div>
      </div>

      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Purchase Workflow</div>
        <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="xb-sb-nav-link active">
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
          Purchase Requests
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_quotations') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6z"/></svg>
          Quotations
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
          Purchase Orders
        </a>
        <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="xb-sb-nav-link">
          <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>
          Purchase Invoices
        </a>
      </div>
    </div>
  </div>
  </form>
</div>

<script>
(function() {
  var idx = <?= $is_edit && !empty($line_items) ? count($line_items) : 1 ?>;
  function calcRow(row) {
    var q = parseFloat($(row).find('.li-qty').val())||0;
    var p = parseFloat($(row).find('.li-price').val())||0;
    var t = parseFloat($(row).find('.li-taxp').val())||0;
    var sub = q*p; var tot = sub + sub*t/100;
    $(row).find('.li-total').text(tot.toFixed(2));
    $(row).find('.li-total-input').val(tot.toFixed(2));
    calcTotals();
  }
  function calcTotals() {
    var total = 0;
    $('#rq-li-body .li-row').each(function() {
      total += parseFloat($(this).find('.li-total-input').val())||0;
    });
    $('#rq-grand').text(total.toFixed(2));
    $('#rq-sb-total').text(total.toFixed(2));
    $('#rq-total').val(total.toFixed(2));
  }
  function bindRow(row) {
    $(row).find('.li-qty,.li-price,.li-taxp').on('input change', function(){ calcRow(row); });
    $(row).find('.li-remove').on('click', function(){
      if ($('#rq-li-body .li-row').length > 1) { $(row).remove(); calcTotals(); }
    });
    calcRow(row);
  }
  $('#rq-li-body .li-row').each(function(){ bindRow(this); });
  $('#rq-add-line').on('click', function(){
    var row = '<tr class="li-row"><td><input type="text" name="lines['+idx+'][item_text]" class="form-control li-desc" placeholder="Item description"></td><td><input type="number" name="lines['+idx+'][quantity]" class="form-control li-qty" step="0.01" value="1"></td><td><input type="number" name="lines['+idx+'][unit_price]" class="form-control li-price" step="0.01" value="0.00"></td><td><input type="number" name="lines['+idx+'][tax_rate]" class="form-control li-taxp" step="0.01" placeholder="0" value="0"></td><td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;">0.00</span><input type="hidden" name="lines['+idx+'][total]" class="li-total-input" value="0"></td><td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td></tr>';
    bindRow($(row).appendTo('#rq-li-body')[0]);
    idx++;
  });
  calcTotals();
})();
</script>
