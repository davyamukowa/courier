<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<div id="fleet-page-wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <h4 class="no-margin font-bold"><?php echo _l($title); ?></h4>
          <hr />
          <div>
            <?php if(is_admin() || has_permission('fleet_fuel', '', 'create')){ ?>
            <a href="#" class="btn btn-info add-new-fuel mbot15"><?php echo _l('add'); ?></a>
            <?php } ?>
          </div>

          <!-- Filters -->
          <div class="row">
            <div class="col-md-3">
                <?php
                $fuel_type = [
                  ['id' => 'compressed_natural_gas', 'name' => _l('compressed_natural_gas')],
                  ['id' => 'diesel',                 'name' => _l('diesel')],
                  ['id' => 'gasoline',               'name' => _l('gasoline')],
                  ['id' => 'propane',                'name' => _l('propane')],
                ];
                echo render_select('_fuel_type', $fuel_type, array('id', 'name'), 'fuel_type');
                ?>
            </div>
            <div class="col-md-3">
              <?php echo render_date_input('from_date','from_date'); ?>
            </div>
            <div class="col-md-3">
              <?php echo render_date_input('to_date','to_date'); ?>
            </div>
          </div>
          <hr>

          <table class="table table-fuel scroll-responsive">
           <thead>
              <tr>
                 <th><?php echo _l('vehicle'); ?></th>
                 <th><?php echo _l('date'); ?></th>
                 <th><?php echo _l('vendor'); ?></th>
                 <th>Trip Type</th>
                 <th><?php echo _l('odometer'); ?> (Before)</th>
                 <th>Odometer (After)</th>
                 <th><?php echo _l('gallons'); ?></th>
                 <th><?php echo _l('price'); ?></th>
              </tr>
           </thead>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $arrAtt = array();
      $arrAtt['data-type']='currency';
?>

<!-- Fuel Add/Edit Modal -->
<div class="modal fade" id="fuel-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="fuel-modal-title"><?php echo _l('fuel'); ?> Entry</h4>
         </div>
         <?php echo form_open(admin_url('fleet/add_fuel'),array('id'=>'fuel-form'));?>
         <?php echo form_hidden('id'); ?>

         <div class="modal-body">

           <!-- Trip Type -->
           <div class="form-group">
             <label class="control-label">Trip Type <span class="text-danger">*</span></label>
             <select name="trip_type" id="fuel_trip_type" class="form-control" required>
               <option value="regular">Regular / Ad-hoc</option>
               <option value="pre_trip">Pre-Trip (Before Journey)</option>
               <option value="post_trip">Post-Trip (After Journey)</option>
             </select>
             <small class="text-muted">Select "Pre-Trip" to log fuel & odometer before departure; "Post-Trip" to record ending odometer after the journey.</small>
           </div>

           <!-- Vehicle -->
           <?php echo render_select('vehicle_id', $vehicles, array('id','name'), 'vehicle'); ?>

           <!-- Assignment (trip) link -->
           <div class="form-group" id="assignment-row">
             <label class="control-label">Link to Trip Assignment <small class="text-muted">(optional)</small></label>
             <select name="assignment_id" id="fuel_assignment_id" class="form-control select2">
               <option value="">— none —</option>
               <?php foreach ($assignments as $a): ?>
               <option value="<?php echo $a->id; ?>"
                       data-start="<?php echo $a->starting_odometer; ?>"
                       data-driver="<?php echo htmlspecialchars($a->driver_name); ?>"
                       data-vehicle="<?php echo (int)$a->vehicle_id; ?>">
                 #<?php echo $a->id; ?> — <?php echo htmlspecialchars($a->driver_name); ?> /
                 <?php echo htmlspecialchars($a->vehicle_name); ?> —
                 <?php echo $a->start_time ? date('d M Y', strtotime($a->start_time)) : 'open'; ?>
               </option>
               <?php endforeach; ?>
             </select>
           </div>

           <!-- Date/time -->
           <?php echo render_datetime_input('fuel_time','fuel_time'); ?>

           <!-- Odometer BEFORE trip -->
           <div class="form-group" id="odometer-before-row">
             <label class="control-label">
               Odometer Reading — Before Trip <span class="text-danger trip-required" style="display:none;">*</span>
             </label>
             <input type="number" name="odometer" id="fuel_odometer" class="form-control" placeholder="km / miles at start">
             <small class="text-muted odo-hint">Current odometer reading at time of fuelling.</small>
           </div>

           <!-- Odometer AFTER trip (only visible for post_trip) -->
           <div class="form-group" id="odometer-after-row" style="display:none;">
             <label class="control-label">Odometer Reading — After Trip <span class="text-danger">*</span></label>
             <input type="number" name="odometer_after" id="fuel_odometer_after" class="form-control" placeholder="km / miles at end of trip">
             <small class="text-muted">Record the ending odometer to calculate trip distance.</small>
           </div>

           <!-- Fuel details (hidden for pure post-trip odometer capture) -->
           <div id="fuel-details-section">
             <?php echo render_input('gallons', 'gallons') ?>
             <?php echo render_input('price', 'price', '', 'text', $arrAtt) ?>
             <?php echo render_select('fuel_type', $fuel_type, array('id', 'name'), 'fuel_type'); ?>
             <?php echo render_select('vendor_id', $vendors, array('userid', 'company'), 'vendor'); ?>
             <?php echo render_input('reference', 'reference') ?>
           </div>

           <?php echo render_textarea('notes','notes') ?>

         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>
      </div>
   </div>
</div>

<!-- Bulk-actions modal -->
<div class="modal fade bulk_actions" id="fuel_bulk_actions" tabindex="-1" role="dialog" data-table=".table-fuel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
         </div>
         <div class="modal-body">
            <?php if(has_permission('fleet_fuel_history','','detele')){ ?>
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="mass_delete" id="mass_delete">
                  <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
               </div>
            <?php } ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <a href="#" class="btn btn-info" onclick="bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>
</body>
</html>
<?php require 'modules/fleet/assets/js/fuels/manage_js.php'; ?>
