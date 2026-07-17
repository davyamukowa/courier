<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin">
            <?php echo _l('pos_products'); ?>
            <small class="text-muted">— pulled from Items / Warehouse</small>
          </h3>
          <div class="pull-right">
            <a href="<?php echo admin_url('pos_system/sync_warehouse'); ?>"
               class="btn btn-default"
               onclick="return confirm('Sync all sellable items from Warehouse / Items into the POS catalogue?');">
              <i class="fa fa-sync"></i> Sync from Warehouse
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#productModal" onclick="resetProductForm()">
              <i class="fa fa-plus"></i> Add POS-only Product
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <form method="GET" action="<?php echo admin_url('pos_system/products'); ?>" class="row">
              <div class="col-md-5">
                <input type="text" name="search" class="form-control"
                       placeholder="Search name, SKU, barcode…"
                       value="<?php echo htmlspecialchars((string)$current_search); ?>">
              </div>
              <div class="col-md-3">
                <select name="category" class="form-control">
                  <option value="">— All categories —</option>
                  <?php foreach ($item_groups as $g): ?>
                    <option value="<?php echo (int)$g['id']; ?>"
                      <?php echo ((string)$current_category === (string)$g['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($g['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <select name="status" class="form-control">
                  <option value="1" <?php echo ($current_status === 1) ? 'selected' : ''; ?>>Active</option>
                  <option value="0" <?php echo ($current_status === 0) ? 'selected' : ''; ?>>Inactive</option>
                  <option value="" <?php echo ($current_status === '') ? 'selected' : ''; ?>>All</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-default btn-block">
                  <i class="fa fa-filter"></i> Filter
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Items table -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php if (empty($items)): ?>
              <div class="alert alert-info no-margin">
                <i class="fa fa-info-circle"></i>
                No sellable items found. Add items in <strong>Sales &gt; Items</strong> (or the Warehouse module),
                make sure they're marked <em>active</em> and <em>Can be sold</em>, and they'll appear here automatically.
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped" id="products-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>SKU / Code</th>
                      <th>Barcode</th>
                      <th>Category</th>
                      <th class="text-right">Price</th>
                      <th class="text-right">Stock</th>
                      <th>Source</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $it):
                      $is_pos_only      = !empty($it['source']) && $it['source'] === 'pos_only';
                      $pos_product_id   = (int) ($it['pos_product_id'] ?? 0);
                      $price            = $it['final_price'] ?? $it['selling_price'] ?? 0;
                      $stock            = $it['stock_qty'] ?? 0;
                      $is_visible_in_pos= ($it['is_pos_visible'] ?? 1) ? 1 : 0;
                    ?>
                      <tr>
                        <td><?php echo (int) $it['id']; ?></td>
                        <td>
                          <strong><?php echo htmlspecialchars($it['name'] ?? ''); ?></strong>
                          <?php if (!empty($it['unit'])): ?>
                            <small class="text-muted">(<?php echo htmlspecialchars($it['unit']); ?>)</small>
                          <?php endif; ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($it['sku'] ?? ''); ?></code></td>
                        <td><?php echo htmlspecialchars($it['barcode'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($it['category_name'] ?? '—'); ?></td>
                        <td class="text-right"><?php echo number_format((float)$price, 2); ?></td>
                        <td class="text-right">
                          <?php if ($stock > 0): ?>
                            <span class="label label-success"><?php echo (float)$stock; ?></span>
                          <?php elseif ($stock < 0): ?>
                            <span class="label label-danger"><?php echo (float)$stock; ?></span>
                          <?php else: ?>
                            <span class="label label-default">0</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($is_pos_only): ?>
                            <span class="label label-info">POS only</span>
                          <?php else: ?>
                            <span class="label label-primary">
                              <?php echo htmlspecialchars($it['source'] ?? 'warehouse'); ?>
                            </span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="label label-<?php echo $is_visible_in_pos ? 'success' : 'default'; ?>">
                            <?php echo $is_visible_in_pos ? 'Visible' : 'Hidden'; ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($is_pos_only && $pos_product_id): ?>
                            <button class="btn btn-xs btn-default edit-product"
                                    data-product='<?php echo htmlspecialchars(json_encode($it), ENT_QUOTES); ?>'>
                              <i class="fa fa-edit"></i>
                            </button>
                            <a href="<?php echo admin_url('pos_system/product_delete/' . $pos_product_id); ?>"
                               class="btn btn-xs btn-danger"
                               onclick="return confirm('Deactivate this POS product?');">
                              <i class="fa fa-trash"></i>
                            </a>
                          <?php else: ?>
                            <a href="<?php echo admin_url('items/edit/' . (int)$it['id']); ?>"
                               class="btn btn-xs btn-default" title="Edit in Items module">
                              <i class="fa fa-edit"></i> Item
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Add / Edit POS-only Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="<?php echo admin_url('pos_system/product_save'); ?>" id="productForm">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="productModalTitle">Add POS-only Product</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_product_id" value="">

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="p_name" class="form-control" required>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" id="p_sku" class="form-control">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Barcode</label>
                    <input type="text" name="barcode" id="p_barcode" class="form-control">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Selling Price <span class="text-danger">*</span></label>
                    <input type="number" name="selling_price" id="p_selling_price"
                           step="0.01" min="0" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Cost Price</label>
                    <input type="number" name="cost_price" id="p_cost_price"
                           step="0.01" min="0" class="form-control">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="p_category_id" class="form-control">
                      <option value="">— No Category —</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>">
                          <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Unit</label>
                    <select name="unit" id="p_unit" class="form-control">
                      <option value="pcs">Pieces</option>
                      <option value="kg">Kilograms</option>
                      <option value="g">Grams</option>
                      <option value="l">Litres</option>
                      <option value="ml">Millilitres</option>
                      <option value="m">Metres</option>
                      <option value="box">Box</option>
                      <option value="pair">Pair</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Track Inventory</label>
                    <select name="track_inventory" id="p_track_inventory" class="form-control">
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Allow Negative Stock</label>
                    <select name="allow_negative" id="p_allow_negative" class="form-control">
                      <option value="0">No</option>
                      <option value="1">Yes</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Reorder Point</label>
                    <input type="number" name="reorder_point" id="p_reorder_point"
                           step="0.01" min="0" class="form-control" placeholder="10">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="p_description" class="form-control" rows="2"></textarea>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select name="is_active" id="p_is_active" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <div class="form-group">
                <label>Show on POS</label>
                <select name="is_pos_visible" id="p_is_pos_visible" class="form-control">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="form-group">
                <label>Product Image URL</label>
                <input type="text" name="image" id="p_image" class="form-control" placeholder="https://...">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function resetProductForm() {
  var f = document.getElementById('productForm');
  if (f) f.reset();
  var title = document.getElementById('productModalTitle');
  if (title) title.textContent = 'Add POS-only Product';
  var idField = document.getElementById('edit_product_id');
  if (idField) idField.value = '';
}

document.addEventListener('DOMContentLoaded', function () {
  var buttons = document.querySelectorAll('.edit-product');
  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      try {
        var p = JSON.parse(this.getAttribute('data-product'));
        document.getElementById('productModalTitle').textContent = 'Edit POS-only Product';
        document.getElementById('edit_product_id').value   = p.pos_product_id || '';
        document.getElementById('p_name').value            = p.name || '';
        document.getElementById('p_sku').value             = p.sku  || '';
        document.getElementById('p_barcode').value         = p.barcode || '';
        document.getElementById('p_selling_price').value   = p.final_price || p.selling_price || '';
        document.getElementById('p_cost_price').value      = p.cost_price || '';
        document.getElementById('p_category_id').value     = p.category_id || '';
        document.getElementById('p_is_active').value       = (p.is_active === undefined ? 1 : p.is_active);
        document.getElementById('p_is_pos_visible').value  = (p.is_pos_visible === undefined ? 1 : p.is_pos_visible);
        document.getElementById('p_image').value           = p.image || '';
        if (window.jQuery) {
          jQuery('#productModal').modal('show');
        } else {
          document.getElementById('productModal').style.display = 'block';
        }
      } catch (e) { console.error('Failed to load product into modal', e); }
    });
  });
});
</script>

<?php init_tail(); ?>
