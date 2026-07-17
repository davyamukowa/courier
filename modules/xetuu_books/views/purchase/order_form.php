<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_edit   = isset($order) && $order;
$title_str = $is_edit ? 'Edit Order — ' . htmlspecialchars($order->pur_order_number) : 'New Purchase Order';
$save_url  = admin_url('xetuu_books/save_purchase_order' . ($is_edit ? '/' . $order->id : ''));
$ap_map    = [0=>'Draft', 1=>'Confirmed', 2=>'Rejected'];
$stage_num = $is_edit ? ($order->approve_status + 1) : 1;
$stages    = ['Draft', 'Confirmed', 'Receiving', 'Invoiced'];
?>

<div class="xb-form-page">
  <div class="xb-form-header">
    <div class="xb-form-header-left">
      <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i></a>
      <div>
        <p class="xb-form-breadcrumb">
          <a href="<?= admin_url('xetuu_books') ?>">Xetuu Books</a> <span>/</span>
          <a href="<?= admin_url('xetuu_books/purchase_orders') ?>">Purchase Orders</a> <span>/</span>
          <?= $is_edit ? htmlspecialchars($order->pur_order_number) : 'New' ?>
        </p>
        <h1 class="xb-form-title"><?= $title_str ?></h1>
      </div>
    </div>
    <div class="xb-form-actions">
      <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="btn btn-default">Cancel</a>
      <button type="submit" form="pur-ord-form" class="xb-save-btn"><i class="fa fa-floppy-o"></i> Save Order</button>
    </div>
  </div>

  <div class="xb-stage-bar">
    <div class="xb-stages">
      <?php foreach ($stages as $i => $lbl): $n=$i+1; $cls=$n<$stage_num?'done':($n==$stage_num?'active':''); ?>
      <div class="xb-stage <?= $cls ?>">
        <div class="xb-stage-wrap">
          <div class="xb-stage-dot"><?= $n<$stage_num?'✓':$n ?></div>
          <div class="xb-stage-label"><?= $lbl ?></div>
        </div>
        <?php if ($i < count($stages)-1): ?><div class="xb-stage-line"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?= form_open($save_url, ['id'=>'pur-ord-form']) ?>
  <div class="xb-form-body">
    <div class="xb-form-main">
      <div class="xb-form-tabs"><ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#po-details">Details</a></li>
        <li><a data-toggle="tab" href="#po-lines">Line Items</a></li>
        <li><a data-toggle="tab" href="#po-notes">Notes</a></li>
      </ul></div>

      <div class="tab-content">
        <div class="tab-pane active" id="po-details">
          <div class="xb-fcard"><div class="xb-fcard-header">Order Information</div><div class="xb-fcard-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Vendor <span class="req">*</span></label>
                  <select name="vendor" class="form-control" required>
                    <option value="">— Select Vendor —</option>
                    <?php foreach ($vendors as $v): ?>
                    <option value="<?= $v->userid ?>" <?= $is_edit && $order->vendor==$v->userid?'selected':'' ?>><?= htmlspecialchars($v->company) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Linked Quotation</label>
                  <select name="estimate" class="form-control">
                    <option value="">— None —</option>
                    <?php foreach ($quotations as $q): ?>
                    <option value="<?= $q->id ?>" <?= $is_edit && isset($order->estimate) && $order->estimate==$q->id?'selected':'' ?>>#<?= htmlspecialchars($q->number) ?> — <?= htmlspecialchars($q->vendor_name) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">Order Date</label>
                  <input type="date" name="order_date" class="form-control" value="<?= $is_edit ? $order->order_date : date('Y-m-d') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">Delivery Date</label>
                  <input type="date" name="delivery_date" class="form-control" value="<?= $is_edit ? ($order->delivery_date??'') : date('Y-m-d', strtotime('+14 days')) ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">Approval Status</label>
                  <select name="approve_status" class="form-control">
                    <option value="0" <?= $is_edit && $order->approve_status==0?'selected':(!$is_edit?'selected':'') ?>>Pending</option>
                    <option value="1" <?= $is_edit && $order->approve_status==1?'selected':'' ?>>Approved</option>
                    <option value="2" <?= $is_edit && $order->approve_status==2?'selected':'' ?>>Rejected</option>
                  </select>
                </div>
              </div>
            </div>
          </div></div>
        </div>

        <div class="tab-pane" id="po-lines">
          <div class="xb-fcard"><div class="xb-fcard-header">Order Items</div><div class="xb-fcard-body" style="padding:14px;">
            <table class="xb-lineitems">
              <thead><tr>
                <th style="width:35%">Item / Description</th>
                <th style="width:8%">Qty</th>
                <th style="width:13%">Unit Price</th>
                <th style="width:14%">Tax</th>
                <th style="width:13%">Total</th>
                <th style="width:4%"></th>
              </tr></thead>
              <tbody id="po-li-body">
                <?php if ($is_edit && !empty($line_items)): ?>
                  <?php foreach ($line_items as $idx=>$li): ?>
                  <tr class="li-row">
                    <td><input type="text" name="lines[<?=$idx?>][description]" class="form-control li-desc" value="<?= htmlspecialchars($li->item_name?:$li->description??'') ?>"></td>
                    <td><input type="number" name="lines[<?=$idx?>][quantity]" class="form-control li-qty" step="0.01" value="<?=$li->quantity?>"></td>
                    <td><input type="number" name="lines[<?=$idx?>][unit_price]" class="form-control li-price" step="0.01" value="<?=$li->unit_price?>"></td>
                    <td><select name="lines[<?=$idx?>][tax_rate]" class="form-control li-tax">
                      <option value="0">No Tax</option>
                      <?php foreach ($taxes as $t): ?>
                      <option value="<?=$t->taxrate?>" <?=$li->tax_rate==$t->taxrate?'selected':''?>><?= htmlspecialchars($t->name) ?> (<?=$t->taxrate?>%)</option>
                      <?php endforeach; ?>
                    </select></td>
                    <td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;"><?=number_format($li->total,2)?></span>
                        <input type="hidden" name="lines[<?=$idx?>][total]" class="li-total-input" value="<?=$li->total?>"></td>
                    <td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="li-row">
                    <td><input type="text" name="lines[0][description]" class="form-control li-desc" placeholder="Item description"></td>
                    <td><input type="number" name="lines[0][quantity]" class="form-control li-qty" step="0.01" value="1"></td>
                    <td><input type="number" name="lines[0][unit_price]" class="form-control li-price" step="0.01" value="0.00"></td>
                    <td><select name="lines[0][tax_rate]" class="form-control li-tax"><option value="0">No Tax</option><?php foreach ($taxes as $t): ?><option value="<?=$t->taxrate?>"><?=htmlspecialchars($t->name)?> (<?=$t->taxrate?>%)</option><?php endforeach; ?></select></td>
                    <td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;">0.00</span><input type="hidden" name="lines[0][total]" class="li-total-input" value="0"></td>
                    <td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
            <button type="button" class="btn btn-default btn-sm" id="po-add-line" style="margin-top:8px;"><i class="fa fa-plus"></i> Add Item</button>
            <div class="xb-totals">
              <div class="xb-total-row"><label>Subtotal</label><span id="po-sub">0.00</span></div>
              <div class="xb-total-row"><label>Tax</label><span id="po-tax">0.00</span></div>
              <div class="xb-total-row grand"><label><strong>Total</strong></label><span id="po-tot">0.00</span></div>
            </div>
            <input type="hidden" name="subtotal" id="po-inp-sub" value="0">
            <input type="hidden" name="total_tax" id="po-inp-tax" value="0">
            <input type="hidden" name="total" id="po-inp-tot" value="0">
          </div></div>
        </div>

        <div class="tab-pane" id="po-notes">
          <div class="xb-fcard"><div class="xb-fcard-header">Notes</div><div class="xb-fcard-body">
            <div class="form-group">
              <label class="xb-flabel">Vendor Note</label>
              <textarea name="vendornote" class="form-control" rows="3"><?= $is_edit ? htmlspecialchars($order->vendornote??'') : '' ?></textarea>
            </div>
            <div class="form-group">
              <label class="xb-flabel">Terms &amp; Conditions</label>
              <textarea name="terms" class="form-control" rows="3"><?= $is_edit ? htmlspecialchars($order->terms??'') : '' ?></textarea>
            </div>
          </div></div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="xb-form-sidebar">
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Document Summary</div>
        <div class="xb-sb-row"><span class="xb-sb-label">Status</span>
          <span class="xb-sb-value"><?php if ($is_edit): ?>
            <?php $am=[0=>['pending','Pending'],1=>['approved','Approved'],2=>['draft','Rejected']]; [$bc,$bl]=$am[$order->approve_status]??['draft','Draft']; ?>
            <span class="xb-status-badge <?=$bc?>"><?=$bl?></span>
          <?php else: ?><span class="xb-status-badge draft">New Draft</span><?php endif; ?>
          </span>
        </div>
        <div class="xb-sb-row"><span class="xb-sb-label">Vendor</span><span class="xb-sb-value" id="po-sb-vendor"><?= $is_edit ? htmlspecialchars($order->vendor_name??'—') : '—' ?></span></div>
        <div class="xb-sb-row"><span class="xb-sb-label">Total</span><span class="xb-sb-value" id="po-sb-tot" style="color:#1a6b3a;"><?= $is_edit ? xb_format_money($order->total) : '—' ?></span></div>
      </div>
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Purchase Workflow</div>
        <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>Purchase Requests</a>
        <a href="<?= admin_url('xetuu_books/purchase_quotations') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6z"/></svg>Quotations</a>
        <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="xb-sb-nav-link active"><svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>Purchase Orders</a>
        <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>Purchase Invoices</a>
        <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2z"/></svg>Contracts</a>
      </div>
    </div>
  </div>
  </form>
</div>

<script>
(function(){
  var idx = <?= $is_edit&&!empty($line_items)?count($line_items):1 ?>;
  var taxes = <?= json_encode(array_map(fn($t)=>['rate'=>(float)$t->taxrate,'name'=>$t->name.' ('.$t->taxrate.'%)'], $taxes)) ?>;
  function calcRow(row){
    var q=parseFloat($(row).find('.li-qty').val())||0, p=parseFloat($(row).find('.li-price').val())||0, t=parseFloat($(row).find('.li-tax').val())||0;
    var s=q*p, tot=s+s*t/100;
    $(row).find('.li-total').text(tot.toFixed(2)); $(row).find('.li-total-input').val(tot.toFixed(2)); calcTotals();
  }
  function calcTotals(){
    var sub=0,tax=0;
    $('#po-li-body .li-row').each(function(){
      var q=parseFloat($(this).find('.li-qty').val())||0, p=parseFloat($(this).find('.li-price').val())||0, t=parseFloat($(this).find('.li-tax').val())||0;
      sub+=q*p; tax+=q*p*t/100;
    });
    var tot=sub+tax;
    $('#po-sub').text(sub.toFixed(2)); $('#po-tax').text(tax.toFixed(2)); $('#po-tot').text(tot.toFixed(2));
    $('#po-inp-sub').val(sub.toFixed(2)); $('#po-inp-tax').val(tax.toFixed(2)); $('#po-inp-tot').val(tot.toFixed(2));
    $('#po-sb-tot').text(tot.toFixed(2));
  }
  function bindRow(row){
    $(row).find('.li-qty,.li-price,.li-tax').on('input change',function(){calcRow(row);});
    $(row).find('.li-remove').on('click',function(){if($('#po-li-body .li-row').length>1){$(row).remove();calcTotals();}});
    calcRow(row);
  }
  $('#po-li-body .li-row').each(function(){bindRow(this);});
  $('#po-add-line').on('click',function(){
    var opts='<option value="0">No Tax</option>'; taxes.forEach(function(t){opts+='<option value="'+t.rate+'">'+t.name+'</option>';});
    var row='<tr class="li-row"><td><input type="text" name="lines['+idx+'][description]" class="form-control li-desc" placeholder="Item description"></td><td><input type="number" name="lines['+idx+'][quantity]" class="form-control li-qty" step="0.01" value="1"></td><td><input type="number" name="lines['+idx+'][unit_price]" class="form-control li-price" step="0.01" value="0.00"></td><td><select name="lines['+idx+'][tax_rate]" class="form-control li-tax">'+opts+'</select></td><td><span class="li-total" style="font-weight:600;display:block;padding:6px 4px;">0.00</span><input type="hidden" name="lines['+idx+'][total]" class="li-total-input" value="0"></td><td><button type="button" class="btn btn-xs btn-danger li-remove"><i class="fa fa-trash"></i></button></td></tr>';
    bindRow($(row).appendTo('#po-li-body')[0]); idx++;
  });
  $('select[name="vendor"]').on('change',function(){$('#po-sb-vendor').text($(this).find('option:selected').text()||'—');});
  calcTotals();
})();
</script>
