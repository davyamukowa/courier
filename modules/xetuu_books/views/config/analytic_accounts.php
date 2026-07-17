<?php defined('BASEPATH') or exit('No direct script access allowed');
$filter_plan = $this->input->get('plan_id') ?: '';
?>
<style>
.acc-tree-row { transition: background 0.1s; }
.acc-tree-row:hover { background: #f0fdf4 !important; }
.acc-indent { display: inline-block; }
.acc-plan-badge { display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;color:#fff; }
.acc-level-0 { font-weight:700; font-size:13px; }
.acc-level-1 { font-size:13px; }
.acc-level-2 { font-size:12px; color:#374151; }
.acc-level-3 { font-size:12px; color:#6b7280; }
</style>

<div class="xb-config-section">
<div class="row">
  <div class="col-md-12">
    <div class="xb-card">
      <div class="xb-card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 style="margin:0;font-weight:700;">Cost Centres &amp; Analytic Accounts</h4>
          <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Hierarchical tree: Company → Region → Branch → Department. Each account belongs to a Plan (dimension).</p>
        </div>
        <div>
          <!-- Filter by plan -->
          <select onchange="location.href='<?php echo admin_url('xetuu_books/config/analytic_accounts'); ?>?plan_id='+this.value" class="form-control form-control-sm" style="display:inline-block;width:auto;margin-right:8px;">
            <option value="">All Plans</option>
            <?php foreach ($plans as $p): ?>
            <option value="<?php echo $p->id; ?>" <?php echo $filter_plan == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-success btn-sm" onclick="open_account_modal()">
            <i class="fa fa-plus"></i> New Account
          </button>
        </div>
      </div>
      <div class="xb-card-body" style="padding:0;">
        <table class="table table-hover" style="margin:0;">
          <thead style="background:#f9fafb;">
            <tr>
              <th>Account / Cost Centre</th>
              <th>Code</th>
              <th>Plan</th>
              <th>Parent</th>
              <th>Status</th>
              <th width="120"></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $filtered = $filter_plan
                ? array_filter($accounts, fn($a) => $a->plan_id == $filter_plan)
                : $accounts;
            if (empty($filtered)):
            ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">
              <?php if ($filter_plan): ?>
                No accounts in this plan. <a href="#" onclick="open_account_modal(); return false;">Create one</a>.
              <?php else: ?>
                No analytic accounts yet. <a href="#" onclick="open_account_modal(); return false;">Create your first cost centre</a>.
              <?php endif; ?>
            </td></tr>
            <?php else: ?>
            <?php foreach ($filtered as $a): ?>
            <tr class="acc-tree-row">
              <td>
                <span class="acc-indent" style="width:<?php echo ($a->level * 24); ?>px;"></span>
                <?php if ($a->level > 0): ?>
                <span style="color:#d1d5db;margin-right:4px;">└</span>
                <?php endif; ?>
                <span class="acc-level-<?php echo min($a->level, 3); ?>">
                  <?php if ($a->level == 0): ?><i class="fa fa-building-o" style="color:#1a6b3a;margin-right:4px;"></i><?php endif; ?>
                  <?php if ($a->level == 1): ?><i class="fa fa-map-marker" style="color:#0ea5e9;margin-right:4px;"></i><?php endif; ?>
                  <?php if ($a->level >= 2): ?><i class="fa fa-circle" style="font-size:6px;color:#9ca3af;margin-right:6px;vertical-align:middle;"></i><?php endif; ?>
                  <?php echo htmlspecialchars($a->name); ?>
                </span>
                <?php if ($a->complete_name && $a->complete_name !== $a->name): ?>
                <br><small class="text-muted" style="margin-left:<?php echo ($a->level * 24 + 16); ?>px;"><?php echo htmlspecialchars($a->complete_name); ?></small>
                <?php endif; ?>
              </td>
              <td><code><?php echo htmlspecialchars($a->code ?? ''); ?></code></td>
              <td>
                <?php if ($a->plan_id): ?>
                <span class="acc-plan-badge" style="background:<?php echo htmlspecialchars($a->plan_color ?? '#6b7280'); ?>;">
                  <?php echo htmlspecialchars($a->plan_name ?? ''); ?>
                </span>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td><span class="text-muted"><?php echo htmlspecialchars($a->parent_name ?? '—'); ?></span></td>
              <td><?php echo $a->active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
              <td class="text-right">
                <button class="btn btn-xs btn-default" onclick="open_account_modal(<?php echo htmlspecialchars(json_encode((object)[
                    'id' => $a->id, 'name' => $a->name, 'code' => $a->code,
                    'plan_id' => $a->plan_id, 'parent_id' => $a->parent_id,
                    'currency_id' => $a->currency_id, 'note' => $a->note,
                    'active' => $a->active,
                ])); ?>)">Edit</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this account?');">
                  <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                  <?php echo form_hidden('action','delete_account'); ?>
                  <?php echo form_hidden('id', $a->id); ?>
                  <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Account Modal -->
<div class="modal fade" id="account-modal" tabindex="-1">
  <div class="modal-dialog" style="max-width:560px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="account-modal-title">New Cost Centre / Analytic Account</h4>
      </div>
      <form method="post">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php echo form_hidden('action','save_account'); ?>
        <input type="hidden" name="id" id="acc_id_field">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="acc_name_field" class="form-control" placeholder="e.g. Nairobi Branch, Project Alpha, HR Dept" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" id="acc_code_field" class="form-control" placeholder="e.g. BR-001">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Plan (Dimension) <span class="text-danger">*</span></label>
                <select name="plan_id" id="acc_plan_field" class="form-control selectpicker" data-live-search="true" required onchange="refresh_parent_dropdown(this.value)">
                  <option value="">— Select Plan —</option>
                  <?php foreach ($plans as $p): ?>
                  <option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Parent Account <small class="text-muted">(leave blank = root)</small></label>
                <select name="parent_id" id="acc_parent_field" class="form-control selectpicker" data-live-search="true">
                  <option value="">— Root (no parent) —</option>
                  <?php foreach ($accounts as $a): ?>
                  <option value="<?php echo $a->id; ?>" data-plan="<?php echo $a->plan_id; ?>">
                    <?php echo str_repeat('— ', $a->level) . htmlspecialchars($a->name); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Currency</label>
                <select name="currency_id" id="acc_currency_field" class="form-control">
                  <?php foreach ($currencies as $cur): ?>
                  <option value="<?php echo $cur->id; ?>"><?php echo $cur->name . ' (' . $cur->symbol . ')'; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Active</label>
                <select name="active" id="acc_active_field" class="form-control">
                  <option value="1">Yes</option>
                  <option value="0">No (archived)</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Notes</label>
            <textarea name="note" id="acc_note_field" class="form-control" rows="2" placeholder="Optional description or notes"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var all_accounts = <?php echo json_encode(array_values(array_map(function($a) {
    return ['id' => $a->id, 'name' => $a->name, 'plan_id' => $a->plan_id, 'level' => $a->level];
}, $accounts))); ?>;

function open_account_modal(acc) {
    $('#acc_id_field').val(acc ? acc.id : '');
    $('#acc_name_field').val(acc ? acc.name : '');
    $('#acc_code_field').val(acc ? (acc.code || '') : '');
    $('#acc_plan_field').val(acc ? acc.plan_id : '<?php echo $filter_plan; ?>').selectpicker('refresh');
    $('#acc_note_field').val(acc ? (acc.note || '') : '');
    $('#acc_active_field').val(acc ? String(acc.active) : '1');
    if (acc && acc.currency_id) { $('#acc_currency_field').val(acc.currency_id); }
    $('#account-modal-title').text(acc ? 'Edit: ' + acc.name : 'New Cost Centre / Analytic Account');

    var plan_id = acc ? acc.plan_id : '<?php echo $filter_plan; ?>';
    refresh_parent_dropdown(plan_id, acc ? acc.parent_id : null);
    $('#account-modal').modal('show');
}

function refresh_parent_dropdown(plan_id, selected_id) {
    var $sel = $('#acc_parent_field');
    $sel.selectpicker('destroy');
    var html = '<option value="">— Root (no parent) —</option>';
    all_accounts.forEach(function(a) {
        if (!plan_id || a.plan_id == plan_id) {
            var dash = '— '.repeat(a.level);
            var sel = (selected_id && a.id == selected_id) ? ' selected' : '';
            html += '<option value="' + a.id + '"' + sel + '>' + dash + a.name + '</option>';
        }
    });
    $sel.html(html).selectpicker({ liveSearch: true });
}
</script>
