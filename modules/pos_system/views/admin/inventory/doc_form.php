<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$type_configs = [
    'receipt'    => ['label'=>'Inventory Receipt',       'icon'=>'fa-truck-loading', 'section'=>'receiving',   'party_label'=>'Supplier',  'party_field'=>'supplier_id'],
    'delivery'   => ['label'=>'Inventory Delivery',      'icon'=>'fa-truck',         'section'=>'deliveries',  'party_label'=>'Customer',  'party_field'=>'customer_id'],
    'transfer'   => ['label'=>'Internal Transfer',       'icon'=>'fa-exchange-alt',  'section'=>'transfers',   'party_label'=>null, 'party_field'=>null],
    'adjustment' => ['label'=>'Loss & Adjustment',       'icon'=>'fa-sliders-h',     'section'=>'adjustments', 'party_label'=>null, 'party_field'=>null],
    'return'     => ['label'=>'Return Order',            'icon'=>'fa-undo-alt',       'section'=>'returns',     'party_label'=>null, 'party_field'=>null],
    'packing'    => ['label'=>'Packing List',            'icon'=>'fa-box',            'section'=>'packing',     'party_label'=>null, 'party_field'=>null],
    'stocktake'  => ['label'=>'Physical Stock Take',     'icon'=>'fa-clipboard-check','section'=>'stocktake',  'party_label'=>null, 'party_field'=>null],
];
$cfg = $type_configs[$doc_type] ?? ['label'=>ucfirst($doc_type), 'icon'=>'fa-file', 'section'=>$doc_type.'s', 'party_label'=>null, 'party_field'=>null];
$is_edit = $doc_id > 0;
?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => $inv_section,
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>
<div class="inv-content" style="padding:20px">
<div class="row">
  <div class="col-md-12">
    <div class="panel_s">
      <div class="panel-body" style="padding:0">

        <!-- Form header -->
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #f0f0f0">
          <a href="<?php echo admin_url('pos_system/inventory/'.$cfg['section']); ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
          <h4 style="margin:0;font-size:15px;font-weight:600;color:#2c3e50;flex:1">
            <i class="fa <?php echo $cfg['icon']; ?>" style="color:#3498db;margin-right:6px"></i>
            <?php echo $is_edit ? 'Edit' : 'New'; ?> <?php echo $cfg['label']; ?>
          </h4>
          <button class="btn btn-sm btn-primary" onclick="docSave('draft')"><i class="fa fa-save"></i> Save Draft</button>
          <button class="btn btn-sm btn-success" onclick="docSave('confirmed')"><i class="fa fa-check"></i> Confirm</button>
        </div>

        <div style="padding:20px">
          <input type="hidden" id="df-doc-id" value="<?php echo $doc_id; ?>">
          <input type="hidden" id="df-doc-type" value="<?php echo $doc_type; ?>">

          <!-- Top fields -->
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Branch / Warehouse <span class="text-danger">*</span></label>
                <select id="df-branch" class="form-control">
                  <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>" <?php echo (int)$branch_id===(int)$b['id']?'selected':''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if ($doc_type === 'transfer'): ?>
            <div class="col-md-3">
              <div class="form-group">
                <label>To Branch <span class="text-danger">*</span></label>
                <select id="df-to-branch" class="form-control">
                  <option value="">— Select —</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php elseif ($cfg['party_label']): ?>
            <div class="col-md-3">
              <div class="form-group">
                <label><?php echo $cfg['party_label']; ?></label>
                <?php if ($cfg['party_field'] === 'supplier_id'): ?>
                <select id="df-party" class="form-control">
                  <option value="">— Select Supplier —</option>
                  <?php foreach ($suppliers as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option><?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="text" id="df-party" class="form-control" placeholder="Customer name">
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
              <div class="form-group">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" id="df-date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
            <?php if ($doc_type === 'adjustment'): ?>
            <div class="col-md-3">
              <div class="form-group">
                <label>Type <span class="text-danger">*</span></label>
                <select id="df-adj-type" class="form-control">
                  <option value="loss">Loss</option>
                  <option value="gain">Gain</option>
                  <option value="correction">Correction</option>
                </select>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($doc_type === 'return'): ?>
            <div class="col-md-3">
              <div class="form-group">
                <label>Return Type <span class="text-danger">*</span></label>
                <select id="df-return-type" class="form-control">
                  <option value="customer">Customer Return</option>
                  <option value="supplier">Supplier Return</option>
                </select>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Items table -->
          <div style="border:1px solid #e8ecef;border-radius:4px;margin-top:10px">
            <div style="display:flex;align-items:center;padding:10px 14px;border-bottom:1px solid #e8ecef;background:#f8f9fa">
              <strong style="font-size:13px;color:#2c3e50;flex:1">Line Items</strong>
              <button type="button" class="btn btn-xs btn-primary" onclick="dfAddLine()"><i class="fa fa-plus"></i> Add Product</button>
            </div>
            <table class="table" style="margin:0">
              <thead><tr style="background:#fafafa">
                <th style="padding:8px 12px;font-size:11px;color:#7f8c8d;width:35%">PRODUCT</th>
                <th style="padding:8px 12px;font-size:11px;color:#7f8c8d">BATCH / LOT</th>
                <th style="padding:8px 12px;font-size:11px;color:#7f8c8d">QTY</th>
                <th style="padding:8px 12px;font-size:11px;color:#7f8c8d">UNIT COST</th>
                <th style="padding:8px 12px;font-size:11px;color:#7f8c8d;text-align:right">TOTAL</th>
                <th style="width:40px"></th>
              </tr></thead>
              <tbody id="df-lines">
                <tr id="df-no-lines"><td colspan="6" class="text-center" style="padding:20px;color:#95a5a6;font-size:12px">
                  <i class="fa fa-plus-circle" style="margin-right:4px"></i> Click "Add Product" to add items.
                </td></tr>
              </tbody>
              <tfoot>
                <tr style="background:#f8f9fa">
                  <td colspan="4" style="padding:8px 12px;text-align:right;font-weight:600;font-size:13px">Total Amount:</td>
                  <td style="padding:8px 12px;text-align:right;font-weight:700;font-size:14px;color:#2c3e50" id="df-grand-total">KSh 0.00</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Notes -->
          <div class="form-group" style="margin-top:16px">
            <label>Notes / Reference</label>
            <textarea id="df-notes" class="form-control" rows="2" placeholder="Optional notes or reference number…"></textarea>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var DF_PRODUCTS  = <?php echo json_encode(array_map(function($p){ return ['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku']??'','cost_price'=>$p['cost_price']??0]; }, $products)); ?>;
var DF_SAVE_URL  = '<?php echo admin_url('pos_system/inv_save/doc'); ?>';
var _csrf_n      = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v      = '<?php echo $this->security->get_csrf_hash(); ?>';
var df_line_idx  = 0;

var prodMap = {};
DF_PRODUCTS.forEach(function(p){ prodMap[p.id] = p; });

var prodOptions = '<option value="">— Select Product —</option>';
DF_PRODUCTS.forEach(function(p){ prodOptions += '<option value="'+p.id+'">'+p.name+(p.sku?' ('+p.sku+')':'')+'</option>'; });

function dfAddLine() {
    $('#df-no-lines').remove();
    var idx = df_line_idx++;
    var html = '<tr id="df-line-'+idx+'">';
    html += '<td style="padding:6px 8px"><select class="form-control input-sm df-product" data-idx="'+idx+'" onchange="dfProductChange('+idx+')">'+prodOptions+'</select></td>';
    html += '<td style="padding:6px 8px"><input type="text" class="form-control input-sm df-batch" data-idx="'+idx+'" placeholder="Batch #"></td>';
    html += '<td style="padding:6px 8px"><input type="number" class="form-control input-sm df-qty" data-idx="'+idx+'" value="1" min="0.01" step="any" onchange="dfRecalc('+idx+')" style="width:80px"></td>';
    html += '<td style="padding:6px 8px"><input type="number" class="form-control input-sm df-cost" data-idx="'+idx+'" value="0" min="0" step="any" onchange="dfRecalc('+idx+')" style="width:100px"></td>';
    html += '<td style="padding:6px 8px;text-align:right;font-weight:600" id="df-line-total-'+idx+'">0.00</td>';
    html += '<td style="padding:6px 8px;text-align:center"><button class="btn btn-xs btn-danger" onclick="dfRemoveLine('+idx+')"><i class="fa fa-times"></i></button></td>';
    html += '</tr>';
    $('#df-lines').append(html);
}

function dfProductChange(idx) {
    var pid = $('.df-product[data-idx="'+idx+'"]').val();
    if (pid && prodMap[pid]) {
        $('.df-cost[data-idx="'+idx+'"]').val(prodMap[pid].cost_price || 0);
        dfRecalc(idx);
    }
}

function dfRecalc(idx) {
    var qty  = parseFloat($('.df-qty[data-idx="'+idx+'"]').val())  || 0;
    var cost = parseFloat($('.df-cost[data-idx="'+idx+'"]').val()) || 0;
    var total = qty * cost;
    $('#df-line-total-' + idx).text(total.toFixed(2));
    dfUpdateGrandTotal();
}

function dfUpdateGrandTotal() {
    var grand = 0;
    $('[id^="df-line-total-"]').each(function() { grand += parseFloat($(this).text()) || 0; });
    $('#df-grand-total').text('KSh ' + grand.toLocaleString('en-KE', {minimumFractionDigits: 2}));
}

function dfRemoveLine(idx) {
    $('#df-line-' + idx).remove();
    dfUpdateGrandTotal();
    if (!$('#df-lines tr').length) {
        $('#df-lines').append('<tr id="df-no-lines"><td colspan="6" class="text-center" style="padding:20px;color:#95a5a6;font-size:12px"><i class="fa fa-plus-circle" style="margin-right:4px"></i> Click "Add Product" to add items.</td></tr>');
    }
}

function dfSave(status) {
    var lines = [];
    var ok = true;
    $('tr[id^="df-line-"]:not(#df-no-lines)').each(function() {
        var idx = $(this).attr('id').replace('df-line-','');
        var pid = $('.df-product[data-idx="'+idx+'"]').val();
        if (!pid) { ok = false; alert('Select a product for each line.'); return false; }
        lines.push({
            product_id: pid,
            batch_number: $('.df-batch[data-idx="'+idx+'"]').val(),
            quantity: $('.df-qty[data-idx="'+idx+'"]').val(),
            unit_cost: $('.df-cost[data-idx="'+idx+'"]').val()
        });
    });
    if (!ok) return;
    if (!lines.length) { alert('Add at least one line item.'); return; }

    var d = {};
    d[_csrf_n]    = _csrf_v;
    d.doc_type    = $('#df-doc-type').val();
    d.doc_id      = $('#df-doc-id').val();
    d.branch_id   = $('#df-branch').val();
    d.date        = $('#df-date').val();
    d.notes       = $('#df-notes').val();
    d.status      = status;
    d.lines       = JSON.stringify(lines);
    <?php if ($doc_type === 'transfer'): ?>
    d.to_branch_id = $('#df-to-branch').val();
    <?php elseif ($cfg['party_field']): ?>
    d.<?php echo $cfg['party_field']; ?> = $('#df-party').val();
    <?php endif; ?>
    <?php if ($doc_type === 'adjustment'): ?>
    d.type = $('#df-adj-type').val();
    <?php endif; ?>
    <?php if ($doc_type === 'return'): ?>
    d.type = $('#df-return-type').val();
    <?php endif; ?>

    $.post(DF_SAVE_URL, d, function(r) {
        if (r.success) {
            alert_float('success', r.message || 'Saved.');
            if (r.redirect) setTimeout(function(){ window.location = r.redirect; }, 600);
        } else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

// Expose to button clicks
window.docSave = dfSave;
</script>
