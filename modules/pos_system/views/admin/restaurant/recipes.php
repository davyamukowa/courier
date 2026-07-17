<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/restaurant/_nav', ['rest_section'=>'recipes']); ?>
<div style="padding:24px">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <h4 style="margin:0;font-size:17px;font-weight:700"><i class="fa fa-book-open" style="color:#16a34a;margin-right:6px"></i> Recipe Management</h4>
  <button class="btn btn-primary btn-sm" onclick="recipeModal()"><i class="fa fa-plus"></i> New Recipe</button>
</div>

<?php if (empty($recipes)): ?>
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:48px;text-align:center;color:#94a3b8">
    <i class="fa fa-book-open" style="font-size:32px;display:block;margin-bottom:12px;color:#16a34a;opacity:.4"></i>
    <p>No recipes yet. Create recipes to enable automatic ingredient deduction when orders are fulfilled.</p>
    <button class="btn btn-primary" onclick="recipeModal()"><i class="fa fa-plus"></i> Create First Recipe</button>
  </div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
<?php foreach ($recipes as $r): ?>
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;overflow:hidden">
    <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:flex-start;justify-content:space-between">
      <div>
        <div style="font-weight:700;color:#1e293b"><?php echo htmlspecialchars($r['name']); ?></div>
        <div style="font-size:11px;color:#64748b;margin-top:2px">
          <i class="fa fa-tag" style="color:#16a34a"></i> <?php echo htmlspecialchars($r['product_name'] ?? '—'); ?>
          <?php if ($r['area_name']): ?> &nbsp;·&nbsp; <i class="fa fa-fire-alt"></i> <?php echo htmlspecialchars($r['area_name']); ?><?php endif; ?>
          <?php if ($r['prep_minutes']): ?> &nbsp;·&nbsp; <i class="fa fa-clock"></i> <?php echo $r['prep_minutes']; ?> min<?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:6px">
        <button class="btn btn-xs btn-default" onclick="loadRecipeEdit(<?php echo $r['id']; ?>)"><i class="fa fa-edit"></i></button>
        <button class="btn btn-xs btn-danger"  onclick="recipeDelete(<?php echo $r['id']; ?>)"><i class="fa fa-trash"></i></button>
      </div>
    </div>
    <div id="recipe-items-<?php echo $r['id']; ?>" style="padding:10px 16px;font-size:12px;color:#64748b">
      Yield: <strong><?php echo $r['yield_qty']; ?></strong> portion(s)
      <?php if ($r['notes']): ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($r['notes']); ?><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</div>
</div>

<!-- Recipe Modal -->
<div class="modal fade" id="recipeModal" tabindex="-1">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header" style="background:#1e293b;color:#fff">
     <button class="close" data-dismiss="modal" style="color:#fff;opacity:.8">&times;</button>
     <h4 class="modal-title"><i class="fa fa-book-open" style="margin-right:8px"></i><span id="recipeModalTitle">New Recipe</span></h4>
   </div>
   <div class="modal-body">
    <form id="recipeForm">
      <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
      <input type="hidden" name="id" id="recipeId">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Recipe Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="recipeName" class="form-control" required placeholder="e.g. Classic Burger">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Menu Item (Product) <span class="text-danger">*</span></label>
            <select name="product_id" id="recipeProductId" class="form-control" required>
              <option value="">— Select product —</option>
              <?php foreach ($products as $p): ?>
              <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?><?php echo $p['sku'] ? ' ('.$p['sku'].')' : ''; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Yield (Portions)</label>
            <input type="number" name="yield_qty" id="recipeYield" class="form-control" value="1" min="0.01" step="0.01">
            <small class="text-muted">How many portions this recipe makes</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Default Production Area</label>
            <select name="area_id" id="recipeAreaId" class="form-control">
              <option value="">— None —</option>
              <?php foreach ($areas as $a): ?>
              <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Prep Time (minutes)</label>
            <input type="number" name="prep_minutes" id="recipePrepMins" class="form-control" value="15" min="0">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Notes</label>
        <input type="text" name="notes" id="recipeNotes" class="form-control" placeholder="Optional cooking notes">
      </div>

      <hr>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <strong><i class="fa fa-list" style="color:#16a34a;margin-right:6px"></i> Ingredients</strong>
        <button type="button" class="btn btn-default btn-xs" onclick="addIngredientRow()"><i class="fa fa-plus"></i> Add Ingredient</button>
      </div>
      <div id="ingredientsTable">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:6px;font-size:11px;font-weight:600;color:#94a3b8">
          <div>INGREDIENT</div><div>QUANTITY</div><div>UNIT</div><div></div>
        </div>
        <div id="ingredientRows"></div>
      </div>

      <div class="checkbox" style="margin-top:12px"><label>
        <input type="checkbox" name="is_active" id="recipeActive" value="1" checked> Active
      </label></div>
    </form>
   </div>
   <div class="modal-footer">
     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
     <button type="button" class="btn btn-primary" onclick="recipeSave()"><i class="fa fa-save"></i> Save Recipe</button>
   </div>
  </div>
 </div>
</div>

<?php init_tail(); ?>
<script>
var RECIPE_URL   = '<?php echo admin_url('pos_system/restaurant/recipes'); ?>';
var ALL_PRODUCTS = <?php echo json_encode(array_values($products)); ?>;
var ALL_UNITS    = <?php echo json_encode(array_values($units)); ?>;

function buildProductOptions(selected) {
    var html = '<option value="">— Select ingredient —</option>';
    ALL_PRODUCTS.forEach(function(p) {
        html += '<option value="' + p.id + '"' + (selected == p.id ? ' selected' : '') + '>' + $('<div>').text(p.name).html() + (p.sku ? ' (' + p.sku + ')' : '') + '</option>';
    });
    return html;
}

function buildUnitOptions(selected) {
    var html = '<option value="">— Unit —</option>';
    ALL_UNITS.forEach(function(u) {
        html += '<option value="' + u.id + '"' + (selected == u.id ? ' selected' : '') + '>' + $('<div>').text(u.name + ' (' + u.symbol + ')').html() + '</option>';
    });
    return html;
}

function addIngredientRow(data) {
    data = data || {};
    var row = '<div class="ingredient-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center">';
    row += '<select name="ingredient_id[]" class="form-control input-sm">' + buildProductOptions(data.ingredient_id) + '</select>';
    row += '<input type="number" name="ingredient_qty[]" class="form-control input-sm" value="' + (data.quantity || 1) + '" step="0.001" min="0.001">';
    row += '<select name="ingredient_unit_id[]" class="form-control input-sm">' + buildUnitOptions(data.unit_id) + '</select>';
    row += '<button type="button" class="btn btn-danger btn-xs" onclick="$(this).closest(\'.ingredient-row\').remove()"><i class="fa fa-times"></i></button>';
    row += '</div>';
    $('#ingredientRows').append(row);
}

function recipeModal() {
    $('#recipeModalTitle').text('New Recipe');
    $('#recipeId').val('');
    $('#recipeName').val('');
    $('#recipeProductId').val('');
    $('#recipeYield').val(1);
    $('#recipeAreaId').val('');
    $('#recipePrepMins').val(15);
    $('#recipeNotes').val('');
    $('#recipeActive').prop('checked', true);
    $('#ingredientRows').empty();
    addIngredientRow();
    $('#recipeModal').modal('show');
}

function loadRecipeEdit(id) {
    $.getJSON('<?php echo admin_url('pos_system/restaurant_recipe_get'); ?>/' + id, function(r) {
        if (!r.id) { alert_float('danger', 'Could not load recipe.'); return; }
        $('#recipeModalTitle').text('Edit Recipe');
        $('#recipeId').val(r.id);
        $('#recipeName').val(r.name);
        $('#recipeProductId').val(r.product_id);
        $('#recipeYield').val(r.yield_qty);
        $('#recipeAreaId').val(r.area_id);
        $('#recipePrepMins').val(r.prep_minutes);
        $('#recipeNotes').val(r.notes);
        $('#recipeActive').prop('checked', r.is_active != '0');
        $('#ingredientRows').empty();
        (r.items || []).forEach(function(ing) { addIngredientRow(ing); });
        if (!r.items || !r.items.length) addIngredientRow();
        $('#recipeModal').modal('show');
    });
}

function recipeSave() {
    $.post(RECIPE_URL, $('#recipeForm').serialize(), function(r) {
        if (r.success) { $('#recipeModal').modal('hide'); alert_float('success', 'Recipe saved.'); setTimeout(function(){location.reload();}, 800); }
        else alert_float('danger', r.error || 'Save failed.');
    }, 'json');
}

function recipeDelete(id) {
    if (!confirm('Delete this recipe? This will remove all ingredient definitions.')) return;
    $.post('<?php echo admin_url('pos_system/restaurant_delete_recipe'); ?>', {id: id, <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'}, function(r) {
        if (r.success) { alert_float('success', 'Deleted.'); setTimeout(function(){location.reload();}, 600); }
        else alert_float('danger', r.error || 'Delete failed.');
    }, 'json');
}
</script>
