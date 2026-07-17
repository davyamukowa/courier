<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
?>
<div class="xb-config-section">
<div class="row">
  <div class="col-md-12">
    <div class="xb-card">
      <div class="xb-card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 style="margin:0;font-weight:700;">Analytic Plans <span class="text-muted" style="font-size:13px;font-weight:400;">(Dimensions)</span></h4>
          <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Each plan is a dimension — e.g. Branches, Projects, Departments. You can have multiple active plans simultaneously.</p>
        </div>
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#plan-modal" onclick="open_plan_modal()">
          <i class="fa fa-plus"></i> New Plan
        </button>
      </div>
      <div class="xb-card-body" style="padding:0;">
        <table class="table table-hover" style="margin:0;">
          <thead style="background:#f9fafb;">
            <tr>
              <th width="50">Seq</th>
              <th width="30">Color</th>
              <th>Plan Name</th>
              <th>Default on Transactions</th>
              <th>Accounts</th>
              <th>Status</th>
              <th width="120"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($plans)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No analytic plans yet. Create your first dimension.</td></tr>
            <?php else: ?>
            <?php foreach ($plans as $p):
                $acc_count = (int)$CI->db->where('plan_id', $p->id)->count_all_results('acc_analytic_accounts');
            ?>
            <tr>
              <td><span style="font-size:12px;color:#9ca3af;"><?php echo $p->sequence; ?></span></td>
              <td><span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:<?php echo htmlspecialchars($p->color ?? '#1a6b3a'); ?>;"></span></td>
              <td>
                <strong><?php echo htmlspecialchars($p->name); ?></strong>
                <?php if ($p->description): ?><br><small class="text-muted"><?php echo htmlspecialchars($p->description); ?></small><?php endif; ?>
              </td>
              <td>
                <?php
                $app_map = ['optional' => ['info','Optional'], 'mandatory' => ['success','Mandatory'], 'unavailable' => ['default','Disabled']];
                [$cls, $lbl] = $app_map[$p->default_applicability] ?? ['default', $p->default_applicability];
                ?>
                <span class="label label-<?php echo $cls; ?>"><?php echo $lbl; ?></span>
              </td>
              <td><a href="<?php echo admin_url('xetuu_books/config/analytic_accounts?plan_id=' . $p->id); ?>"><?php echo $acc_count; ?> account<?php echo $acc_count != 1 ? 's' : ''; ?></a></td>
              <td><?php echo $p->active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
              <td class="text-right">
                <button class="btn btn-xs btn-default" onclick="open_plan_modal(<?php echo htmlspecialchars(json_encode($p)); ?>)">Edit</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this plan?');">
                  <?php echo form_hidden('action','delete_plan'); ?>
                  <?php echo form_hidden('id', $p->id); ?>
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

    <!-- Info cards -->
    <div class="row mtop20">
      <div class="col-md-4">
        <div class="panel panel-default" style="border-left:4px solid #1a6b3a;">
          <div class="panel-body" style="font-size:13px;">
            <strong><i class="fa fa-sitemap" style="color:#1a6b3a;"></i> Branches Plan</strong><br>
            <span class="text-muted">Tag costs to Nairobi, Kampala, Dar es Salaam branches for branch-level P&L reports.</span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel panel-default" style="border-left:4px solid #0ea5e9;">
          <div class="panel-body" style="font-size:13px;">
            <strong><i class="fa fa-folder-open" style="color:#0ea5e9;"></i> Projects Plan</strong><br>
            <span class="text-muted">Track costs per project. See how much Nairobi Road Phase 2 spent on fuel this month.</span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel panel-default" style="border-left:4px solid #f59e0b;">
          <div class="panel-body" style="font-size:13px;">
            <strong><i class="fa fa-users" style="color:#f59e0b;"></i> Departments Plan</strong><br>
            <span class="text-muted">Finance, HR, Operations — slice any P&L by department across all branches.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Plan Modal -->
<div class="modal fade" id="plan-modal" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="plan-modal-title">New Analytic Plan</h4>
      </div>
      <form method="post">
        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <?php echo form_hidden('action','save_plan'); ?>
        <input type="hidden" name="id" id="plan_id_field">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Plan Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="plan_name_field" class="form-control" placeholder="e.g. Branches, Projects, Departments" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Color</label>
                <input type="color" name="color" id="plan_color_field" class="form-control" value="#1a6b3a" style="height:38px;padding:3px;">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <input type="text" name="description" id="plan_desc_field" class="form-control" placeholder="Short description">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Default on Transactions</label>
                <select name="default_applicability" id="plan_app_field" class="form-control">
                  <option value="optional">Optional — users can choose</option>
                  <option value="mandatory">Mandatory — required on every line</option>
                  <option value="unavailable">Disabled — not shown</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Sequence</label>
                <input type="number" name="sequence" id="plan_seq_field" class="form-control" value="10" min="1">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Active</label>
                <select name="active" id="plan_active_field" class="form-control">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function open_plan_modal(plan) {
    $('#plan_id_field').val(plan ? plan.id : '');
    $('#plan_name_field').val(plan ? plan.name : '');
    $('#plan_desc_field').val(plan ? plan.description : '');
    $('#plan_color_field').val(plan ? (plan.color || '#1a6b3a') : '#1a6b3a');
    $('#plan_app_field').val(plan ? plan.default_applicability : 'optional');
    $('#plan_seq_field').val(plan ? plan.sequence : 10);
    $('#plan_active_field').val(plan ? String(plan.active) : '1');
    $('#plan-modal-title').text(plan ? 'Edit Plan: ' + plan.name : 'New Analytic Plan');
    $('#plan-modal').modal('show');
}
</script>
