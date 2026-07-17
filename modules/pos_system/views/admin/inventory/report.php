<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/inventory/_nav', [
    'inv_section'   => $inv_section,
    'inv_branches'  => $branches,
    'inv_branch_id' => $branch_id,
]); ?>

<div class="inv-content" style="padding:20px">

<div class="row" style="margin-bottom:16px">
  <div class="col-md-12">
    <div style="display:flex;align-items:center;gap:12px;background:#fff;padding:14px 16px;border-radius:4px;border:1px solid #e8ecef">
      <i class="fa <?php echo $report_icon; ?>" style="font-size:22px;color:#2c3e6a"></i>
      <h4 style="margin:0;font-size:16px;font-weight:600;color:#2c3e50;flex:1"><?php echo $report_title; ?></h4>

      <!-- Filters -->
      <select id="rpt-branch" class="form-control input-sm" style="width:160px">
        <option value="">All Branches</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?php echo $b['id']; ?>" <?php echo (int)$branch_id===(int)$b['id']?'selected':''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" id="rpt-from" class="form-control input-sm" style="width:140px" value="<?php echo date('Y-m-01'); ?>">
      <input type="date" id="rpt-to"   class="form-control input-sm" style="width:140px" value="<?php echo date('Y-m-d'); ?>">
      <button class="btn btn-sm btn-primary" onclick="rptLoad()"><i class="fa fa-sync"></i> Run</button>
      <button class="btn btn-sm btn-default" onclick="rptExport('csv')"><i class="fa fa-download"></i> CSV</button>
    </div>
  </div>
</div>

<div id="rpt-output">
  <div class="text-center" style="padding:60px;color:#95a5a6">
    <i class="fa fa-spinner fa-spin" style="font-size:28px;display:block;margin-bottom:10px"></i>
    Loading report…
  </div>
</div>

</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var RPT_TYPE  = '<?php echo $report_type; ?>';
var RPT_BASE  = '<?php echo admin_url('pos_system/inv_ajax/report_'); ?>';

function rptLoad() {
    var params = {
        type:      RPT_TYPE,
        branch_id: $('#rpt-branch').val(),
        date_from: $('#rpt-from').val(),
        date_to:   $('#rpt-to').val()
    };
    $('#rpt-output').html('<div class="text-center" style="padding:60px;color:#95a5a6"><i class="fa fa-spinner fa-spin" style="font-size:28px;display:block;margin-bottom:10px"></i>Loading…</div>');
    $.getJSON(RPT_BASE + RPT_TYPE, params, function(r) {
        if (r.error) { $('#rpt-output').html('<div class="alert alert-danger">' + r.error + '</div>'); return; }
        $('#rpt-output').html(r.html || '<div class="alert alert-info">No data for this period.</div>');
    }).fail(function() {
        $('#rpt-output').html('<div class="alert alert-warning">Could not load report data.</div>');
    });
}

function rptExport(fmt) {
    var params = new URLSearchParams({
        type:      RPT_TYPE,
        branch_id: $('#rpt-branch').val(),
        date_from: $('#rpt-from').val(),
        date_to:   $('#rpt-to').val(),
        export:    fmt
    });
    window.location = RPT_BASE + RPT_TYPE + '?' + params.toString();
}

$(function() { rptLoad(); });
</script>
