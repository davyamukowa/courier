<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin"><?php echo _l('pos_payment_methods'); ?></h3>
          <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#pmModal" onclick="resetPmForm()">
            <i class="fa fa-plus"></i> <?php echo _l('pos_add_payment_method'); ?>
          </button>
        </div>
      </div>
    </div>

    <!-- Perfex gateways info bar -->
    <?php if (!empty($perfex_gateways)): ?>
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-info">
          <strong><i class="fa fa-info-circle"></i> <?php echo _l('pos_perfex_gateways_active'); ?>:</strong>
          <?php foreach ($perfex_gateways as $gw): ?>
            <span class="label label-success" style="margin:2px"><?php echo htmlspecialchars($gw['name']); ?></span>
          <?php endforeach; ?>
          — <?php echo _l('pos_map_gateway_help'); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <table class="table dt-table" id="pm-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th><?php echo _l('name'); ?></th>
                  <th><?php echo _l('pos_pm_type'); ?></th>
                  <th><?php echo _l('pos_pm_provider'); ?></th>
                  <th><?php echo _l('pos_perfex_gateway'); ?></th>
                  <th><?php echo _l('pos_account_key'); ?></th>
                  <th><?php echo _l('pos_allow_in_returns'); ?></th>
                  <th><?php echo _l('pos_pm_default'); ?></th>
                  <th><?php echo _l('status'); ?></th>
                  <th><?php echo _l('pos_actions'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($payment_methods as $pm): ?>
                <tr>
                  <td><?php echo $pm['id']; ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($pm['name']); ?></strong>
                    <?php if ($pm['is_default'] ?? 0): ?><span class="label label-warning">Default</span><?php endif; ?>
                  </td>
                  <td><span class="label label-default"><?php echo ucfirst(str_replace('_', ' ', $pm['type'])); ?></span></td>
                  <td><?php echo htmlspecialchars($pm['provider'] ?? '—'); ?></td>
                  <td><?php echo htmlspecialchars($pm['perfex_gateway'] ?? '—'); ?></td>
                  <td><code><?php echo htmlspecialchars($pm['account_key'] ?? '—'); ?></code></td>
                  <td><?php echo ($pm['allow_in_returns'] ?? 1) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>'; ?></td>
                  <td><?php echo ($pm['is_default']       ?? 0) ? '<i class="fa fa-check text-success"></i>' : ''; ?></td>
                  <td><span class="label label-<?php echo $pm['is_active'] ? 'success' : 'danger'; ?>"><?php echo $pm['is_active'] ? _l('active') : _l('inactive'); ?></span></td>
                  <td>
                    <button class="btn btn-xs btn-default edit-pm"
                            data-pm='<?php echo htmlspecialchars(json_encode($pm), ENT_QUOTES); ?>'>
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="pmModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="<?php echo admin_url('pos_system/payment_method_save'); ?>" id="pmForm">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="pmModalTitle"><?php echo _l('pos_add_payment_method'); ?></h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="pm_id_field">

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label><?php echo _l('name'); ?> <span class="text-danger">*</span></label>
                <input type="text" name="name" id="pm_name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo _l('pos_pm_code'); ?></label>
                <input type="text" name="code" id="pm_code" class="form-control" placeholder="e.g. cash">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_pm_type'); ?></label>
                <select name="type" id="pm_type" class="form-control">
                  <option value="cash">Cash</option>
                  <option value="card">Card / POS Terminal</option>
                  <option value="mobile_money">Mobile Money</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="credit">Credit / Pay Later</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><?php echo _l('pos_pm_provider'); ?> <small class="text-muted">(mobile money)</small></label>
                <select name="provider" id="pm_provider" class="form-control">
                  <option value="">— None —</option>
                  <option value="mpesa">M-Pesa (Safaricom)</option>
                  <option value="airtel">Airtel Money</option>
                  <option value="mtn">MTN Mobile Money</option>
                  <option value="telebirr">Telebirr (Ethio Telecom)</option>
                  <option value="equitel">Equitel</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo _l('pos_perfex_gateway'); ?></label>
            <select name="perfex_gateway" id="pm_perfex_gateway" class="form-control">
              <option value="">— Not linked —</option>
              <?php foreach ($perfex_gateways as $gw): ?>
                <option value="<?php echo htmlspecialchars($gw['slug']); ?>"><?php echo htmlspecialchars($gw['name']); ?></option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted"><?php echo _l('pos_perfex_gateway_help'); ?></small>
          </div>

          <div class="form-group">
            <label><?php echo _l('pos_account_key'); ?></label>
            <input type="text" name="account_key" id="pm_account_key" class="form-control" placeholder="e.g. acc_cash, acc_petty_cash">
            <small class="text-muted"><?php echo _l('pos_account_key_help'); ?></small>
          </div>

          <div class="form-group">
            <label><?php echo _l('pos_sort_order'); ?></label>
            <input type="number" name="sort_order" id="pm_sort_order" class="form-control" value="1" min="1">
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="checkbox"><label>
                <input type="checkbox" name="allow_in_returns" id="pm_allow_in_returns" value="1" checked>
                <?php echo _l('pos_allow_in_returns'); ?>
              </label></div>
            </div>
            <div class="col-md-4">
              <div class="checkbox"><label>
                <input type="checkbox" name="is_default" id="pm_is_default" value="1">
                <?php echo _l('pos_pm_default'); ?>
              </label></div>
            </div>
            <div class="col-md-4">
              <div class="checkbox"><label>
                <input type="checkbox" name="is_active" id="pm_is_active" value="1" checked>
                <?php echo _l('active'); ?>
              </label></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('submit'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function resetPmForm() {
  document.getElementById('pmModalTitle').textContent = '<?php echo _l('pos_add_payment_method'); ?>';
  document.getElementById('pmForm').reset();
  document.getElementById('pm_id_field').value = '';
  document.getElementById('pm_is_active').checked = true;
  document.getElementById('pm_allow_in_returns').checked = true;
}

document.querySelectorAll('.edit-pm').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var pm = JSON.parse(this.dataset.pm);
    document.getElementById('pmModalTitle').textContent = '<?php echo _l('pos_edit_payment_method'); ?>';
    document.getElementById('pm_id_field').value        = pm.id || '';
    document.getElementById('pm_name').value            = pm.name || '';
    document.getElementById('pm_code').value            = pm.code || '';
    document.getElementById('pm_type').value            = pm.type || 'cash';
    document.getElementById('pm_provider').value        = pm.provider || '';
    document.getElementById('pm_perfex_gateway').value  = pm.perfex_gateway || '';
    document.getElementById('pm_account_key').value     = pm.account_key || '';
    document.getElementById('pm_sort_order').value      = pm.sort_order || 1;
    document.getElementById('pm_allow_in_returns').checked = pm.allow_in_returns == 1;
    document.getElementById('pm_is_default').checked    = pm.is_default == 1;
    document.getElementById('pm_is_active').checked     = pm.is_active == 1;
    jQuery('#pmModal').modal('show');
  });
});
</script>

<?php init_tail(); ?>
