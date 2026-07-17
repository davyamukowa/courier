<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('pos_staff_assignments') ?? 'POS Staff Assignments'; ?></h4>
            <hr class="hr-panel-heading" />

            <div class="alert alert-info">
              Assign staff members to POS Branches. Staff must be assigned to a branch to access the Terminal.
            </div>

            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Assigned Branch</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($staff as $s): ?>
                    <tr>
                      <td><?php echo $s['id']; ?></td>
                      <td><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></td>
                      <td><?php echo htmlspecialchars($s['email']); ?></td>
                      <td>
                        <form method="post" action="<?php echo admin_url('pos_system/save_staff_assignment'); ?>">
                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                          <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                          <div class="input-group">
                            <select name="branch_id" class="form-control">
                              <option value="0">— Unassigned —</option>
                              <?php foreach ($branches as $b): ?>
                                <option value="<?php echo $b['id']; ?>" <?php echo (isset($assigned[$s['id']]) && $assigned[$s['id']] == $b['id']) ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($b['name']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <span class="input-group-btn">
                              <button type="submit" class="btn btn-primary">Save</button>
                            </span>
                          </div>
                        </form>
                      </td>
                      <td>
                        <?php if (isset($assigned[$s['id']])): ?>
                          <span class="label label-success">Assigned</span>
                        <?php else: ?>
                          <span class="label label-default">No Branch</span>
                        <?php endif; ?>
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
</div>

<?php init_tail(); ?>
