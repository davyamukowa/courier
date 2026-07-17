<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body text-center" style="padding:40px">
                  <i class="fa fa-spinner fa-spin fa-2x text-success"></i>
                  <p class="text-muted" style="margin-top:12px">Activating API module&hellip;</p>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<?php
$hidden_url    = isset($original_url) ? $original_url : admin_url('modules');
$hidden_module = isset($module_name)  ? $module_name  : 'api';
?>
<form id="auto-activate-form" action="<?php echo $submit_url; ?>" method="post" style="display:none">
   <input type="hidden" name="original_url" value="<?php echo htmlspecialchars($hidden_url); ?>">
   <input type="hidden" name="module_name"  value="<?php echo htmlspecialchars($hidden_module); ?>">
   <input type="hidden" name="purchase_key" value="auto">
   <input type="hidden" name="username"     value="auto">
</form>
<script>
(function () {
   var form = document.getElementById('auto-activate-form');
   var data = new URLSearchParams(new FormData(form)).toString();
   fetch(form.action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data,
   })
   .then(function (r) { return r.json(); })
   .then(function (res) {
      if (res && res.original_url) {
         window.location.href = res.original_url;
      } else {
         window.location.href = '<?php echo admin_url('modules'); ?>';
      }
   })
   .catch(function () {
      window.location.href = '<?php echo admin_url('modules'); ?>';
   });
})();
</script>
