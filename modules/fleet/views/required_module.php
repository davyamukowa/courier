<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="fleet-page-wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="panel_s">
          <div class="panel-heading">
            <h4><i class="fa fa-plug"></i> Fleet — Optional Integrations</h4>
          </div>
          <div class="panel-body">
            <p class="text-muted" style="margin-bottom:16px;">
              The Fleet module works standalone. The modules below are optional — when active, they unlock extra features.
            </p>
            <table class="table table-striped no-margin">
              <thead>
                <tr>
                  <th>Module</th>
                  <th>Feature unlocked</th>
                  <th class="text-right">Active?</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $rows = [
                    'purchase' => 'Purchase orders &amp; vendor billing for work orders',
                    'xetuu_hr' => 'Driver training records &amp; HR profiles',
                    'courier'  => 'Trip booking from waybill &amp; shipment linking',
                ];
                foreach ($rows as $key => $desc):
                    $active = isset($optional[$key]) ? $optional[$key] : 0;
                ?>
                <tr>
                  <td><a href="<?php echo site_url('admin/modules'); ?>"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></a></td>
                  <td style="font-size:13px;color:#555;"><?php echo $desc; ?></td>
                  <td class="text-right <?php echo $active ? 'text-success' : 'text-muted'; ?>">
                    <i class="fa <?php echo $active ? 'fa-check-circle' : 'fa-circle-o'; ?>"></i>
                    <?php echo $active ? 'Active' : 'Inactive'; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <div style="margin-top:20px;">
              <a href="<?php echo admin_url('fleet/dashboard'); ?>" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back to Fleet Dashboard
              </a>
              <a href="<?php echo admin_url('modules'); ?>" class="btn btn-default" style="margin-left:8px;">
                <i class="fa fa-puzzle-piece"></i> Manage Modules
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
