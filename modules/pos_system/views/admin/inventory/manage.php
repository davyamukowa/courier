<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin"><?php echo _l('pos_inventory'); ?></h3>
          <div class="pull-right">
            <?php if (count($branches) > 1): ?>
            <form method="get" class="form-inline" style="display:inline-block;margin-right:8px">
              <select name="branch_id" class="form-control input-sm" onchange="this.form.submit()">
                <?php foreach ($branches as $b): ?>
                  <option value="<?php echo $b['id']; ?>" <?php echo (int)$branch_id === (int)$b['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($b['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
            <?php endif; ?>
            <button class="btn btn-default btn-sm" id="btn-sync-warehouse">
              <i class="fa fa-sync"></i> <?php echo _l('pos_sync_warehouse'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <div class="col-md-4">
                <input type="text" id="inv-search" class="form-control" placeholder="<?php echo _l('search'); ?>...">
              </div>
              <div class="col-md-3">
                <select id="inv-filter-stock" class="form-control">
                  <option value=""><?php echo _l('pos_all_stock'); ?></option>
                  <option value="low"><?php echo _l('pos_low_stock'); ?></option>
                  <option value="out"><?php echo _l('pos_out_of_stock'); ?></option>
                </select>
              </div>
              <div class="col-md-2">
                <select id="inv-filter-source" class="form-control">
                  <option value=""><?php echo _l('pos_all_sources'); ?></option>
                  <option value="warehouse"><?php echo _l('pos_warehouse'); ?></option>
                  <option value="pos"><?php echo _l('pos_pos_only'); ?></option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body no-padding">
            <table class="table table-hover no-margin" id="inv-table">
              <thead>
                <tr>
                  <th><?php echo _l('pos_product'); ?></th>
                  <th><?php echo _l('pos_sku'); ?></th>
                  <th><?php echo _l('pos_category'); ?></th>
                  <th class="text-right"><?php echo _l('pos_selling_price'); ?></th>
                  <th class="text-center"><?php echo _l('pos_warehouse_stock'); ?></th>
                  <th class="text-center"><?php echo _l('pos_pos_stock'); ?></th>
                  <th><?php echo _l('pos_source'); ?></th>
                  <th><?php echo _l('pos_actions'); ?></th>
                </tr>
              </thead>
              <tbody id="inv-table-body">
                <tr><td colspan="8" class="text-center text-muted"><?php echo _l('loading'); ?>...</td></tr>
              </tbody>
            </table>
          </div>
          <div class="panel-footer clearfix">
            <div class="pull-left" id="inv-count"></div>
            <div class="pull-right" id="inv-pagination"></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php echo _l('pos_adjust_stock'); ?></h4>
      </div>
      <form id="adjust-stock-form">
        <div class="modal-body">
          <input type="hidden" id="adj-product-id">
          <input type="hidden" id="adj-source">
          <div class="form-group">
            <label><?php echo _l('pos_product'); ?></label>
            <p class="form-control-static bold" id="adj-product-name"></p>
          </div>
          <div class="form-group">
            <label><?php echo _l('pos_current_qty'); ?></label>
            <p class="form-control-static" id="adj-current-qty"></p>
          </div>
          <div class="form-group">
            <label><?php echo _l('pos_adjustment_type'); ?></label>
            <select id="adj-type" class="form-control">
              <option value="add"><?php echo _l('pos_add_stock'); ?></option>
              <option value="remove"><?php echo _l('pos_remove_stock'); ?></option>
              <option value="set"><?php echo _l('pos_set_quantity'); ?></option>
            </select>
          </div>
          <div class="form-group">
            <label><?php echo _l('pos_quantity'); ?> <span class="text-danger">*</span></label>
            <input type="number" id="adj-qty" class="form-control" min="0" step="0.001" required>
          </div>
          <div class="form-group">
            <label><?php echo _l('pos_reason'); ?></label>
            <input type="text" id="adj-reason" class="form-control" placeholder="e.g. Damage, Theft, Correction...">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-primary"><?php echo _l('pos_save_adjustment'); ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
(function(){
  var API_URL   = '<?php echo admin_url("pos_system/api"); ?>';
  var BRANCH_ID = <?php echo (int) $branch_id; ?>;
  var page      = 1;
  var perPage   = 50;
  var allItems  = [];

  function statusBadge(wh, pos) {
    var qty = wh + pos;
    if (qty <= 0) return '<span class="label label-danger">Out of Stock</span>';
    if (qty <= 5) return '<span class="label label-warning">' + qty + ' Low</span>';
    return '<span class="label label-success">' + qty + '</span>';
  }

  function sourceBadge(source) {
    return source === 'warehouse'
      ? '<span class="label label-info"><i class="fa fa-warehouse"></i> Warehouse</span>'
      : '<span class="label label-default">POS Only</span>';
  }

  function renderTable(items) {
    var tbody = document.getElementById('inv-table-body');
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No items found</td></tr>';
      return;
    }
    tbody.innerHTML = items.map(function(r) {
      var wh  = parseFloat(r.warehouse_qty || 0);
      var pos = parseFloat(r.pos_qty || 0);
      return '<tr data-source="' + (r.source||'pos') + '">'
        + '<td><strong>' + escHtml(r.name) + '</strong></td>'
        + '<td><code>' + escHtml(r.sku || '') + '</code></td>'
        + '<td>' + escHtml(r.category_name || '-') + '</td>'
        + '<td class="text-right">' + escHtml(r.selling_price_fmt || '') + '</td>'
        + '<td class="text-center">' + (wh > 0 ? wh : '<span class="text-muted">—</span>') + '</td>'
        + '<td class="text-center">' + statusBadge(wh, pos) + '</td>'
        + '<td>' + sourceBadge(r.source || 'pos') + '</td>'
        + '<td>'
        +   '<button class="btn btn-xs btn-default btn-adj" '
        +     'data-id="' + r.id + '" data-name="' + escHtml(r.name) + '" '
        +     'data-qty="' + (pos) + '" data-source="' + (r.source||'pos') + '">'
        +     '<i class="fa fa-edit"></i> Adjust</button>'
        + '</td>'
        + '</tr>';
    }).join('');
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function loadInventory() {
    var search = document.getElementById('inv-search').value;
    var stockF = document.getElementById('inv-filter-stock').value;
    var sourceF= document.getElementById('inv-filter-source').value;

    var url = API_URL + '/inventory?branch_id=' + BRANCH_ID
            + '&per_page=' + perPage + '&page=' + page;
    if (search)  url += '&search='    + encodeURIComponent(search);
    if (stockF)  url += '&stock='     + encodeURIComponent(stockF);
    if (sourceF) url += '&source='    + encodeURIComponent(sourceF);

    var token = '<?php echo isset($api_token) ? $api_token : ''; ?>';

    fetch(url, { headers: token ? { Authorization: 'Bearer ' + token } : {} })
      .then(function(r){ return r.json(); })
      .then(function(resp) {
        if (resp.success) {
          allItems = resp.data;
          renderTable(allItems);
          document.getElementById('inv-count').textContent =
            (resp.meta ? resp.meta.total : allItems.length) + ' items';
        }
      })
      .catch(function(){ renderTable([]); });
  }

  // Debounced search
  var searchTimer;
  document.getElementById('inv-search').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadInventory, 300);
  });
  document.getElementById('inv-filter-stock').addEventListener('change', loadInventory);
  document.getElementById('inv-filter-source').addEventListener('change', loadInventory);

  // Adjust modal
  document.getElementById('inv-table-body').addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-adj');
    if (!btn) return;
    document.getElementById('adj-product-id').value  = btn.dataset.id;
    document.getElementById('adj-product-name').textContent = btn.dataset.name;
    document.getElementById('adj-current-qty').textContent  = btn.dataset.qty;
    document.getElementById('adj-source').value = btn.dataset.source;
    document.getElementById('adj-qty').value = '';
    document.getElementById('adj-reason').value = '';
    $('#adjustStockModal').modal('show');
  });

  document.getElementById('adjust-stock-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var token = '<?php echo isset($api_token) ? $api_token : ''; ?>';
    fetch(API_URL + '/inventory/adjust', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
      body: JSON.stringify({
        product_id : document.getElementById('adj-product-id').value,
        branch_id  : BRANCH_ID,
        type       : document.getElementById('adj-type').value,
        quantity   : document.getElementById('adj-qty').value,
        reason     : document.getElementById('adj-reason').value,
      })
    })
    .then(function(r){ return r.json(); })
    .then(function(resp) {
      if (resp.success) {
        $('#adjustStockModal').modal('hide');
        loadInventory();
      } else {
        alert(resp.error ? resp.error.message : 'Error saving adjustment');
      }
    });
  });

  // Sync warehouse button
  document.getElementById('btn-sync-warehouse').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spin fa-spinner"></i> Syncing...';
    var token = '<?php echo isset($api_token) ? $api_token : ''; ?>';
    fetch(API_URL + '/inventory/sync_warehouse', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
      body: JSON.stringify({ branch_id: BRANCH_ID })
    })
    .then(function(r){ return r.json(); })
    .then(function(resp) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-sync"></i> <?php echo _l("pos_sync_warehouse"); ?>';
      if (resp.success) {
        loadInventory();
        alert('Sync complete: ' + (resp.data.synced || 0) + ' items updated');
      } else {
        alert(resp.error ? resp.error.message : 'Sync failed');
      }
    });
  });

  loadInventory();
})();
</script>
