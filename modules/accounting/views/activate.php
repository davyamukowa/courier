<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
               <h4>Accounting Module Activation</h4>
               <hr class="hr-panel-heading">
               <p>The accounting module now activates automatically in this development build.</p>
               <p class="text-muted">Redirecting back to the module activation page...</p>
               </div>
            </div>
         </div>
         <div class="col-md-6">
		 </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
   setTimeout(function() {
      window.location.href = <?php echo json_encode(isset($original_url) ? $original_url : admin_url('modules')); ?>;
   }, 800);
</script>
