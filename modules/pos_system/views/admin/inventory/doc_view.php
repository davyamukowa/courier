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
<div class="row">
  <div class="col-md-12">
    <div class="panel_s">
      <div class="panel-body" style="padding:0">
        <!-- Header -->
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #f0f0f0">
          <a href="javascript:history.back()" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
          <h4 style="margin:0;font-size:15px;font-weight:600;color:#2c3e50;flex:1" id="doc-title">
            <i class="fa fa-spinner fa-spin"></i> Loading…
          </h4>
          <span id="doc-status-badge"></span>
          <div id="doc-actions"></div>
        </div>

        <!-- Document header info -->
        <div id="doc-header-info" style="padding:16px;background:#f8f9fa;border-bottom:1px solid #f0f0f0">
          <div class="row" id="doc-info-grid"></div>
        </div>

        <!-- Items table -->
        <div style="padding:16px">
          <h5 style="font-weight:600;color:#2c3e50;margin-bottom:12px">Items</h5>
          <table class="table table-hover" style="margin:0;border:1px solid #e8ecef">
            <thead id="doc-items-head"></thead>
            <tbody id="doc-items-body">
              <tr><td class="text-center" style="padding:20px;color:#95a5a6"><i class="fa fa-spinner fa-spin"></i></td></tr>
            </tbody>
          </table>
        </div>

        <!-- Notes -->
        <div id="doc-notes-section" style="padding:0 16px 16px" class="hidden">
          <h5 style="font-weight:600;color:#2c3e50;margin-bottom:8px">Notes</h5>
          <div id="doc-notes" style="background:#fffbf0;padding:12px;border-radius:4px;font-size:13px;color:#7f8c8d"></div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
var DOC_TYPE = '<?php echo $doc_type; ?>';
var DOC_ID   = <?php echo (int)$doc_id; ?>;
var DOC_AJAX = '<?php echo admin_url('pos_system/inv_ajax/doc_view'); ?>';
var _csrf_n  = '<?php echo $this->security->get_csrf_token_name(); ?>';
var _csrf_v  = '<?php echo $this->security->get_csrf_hash(); ?>';

$(function() {
    $.getJSON(DOC_AJAX, {type: DOC_TYPE, id: DOC_ID}, function(r) {
        if (r.error) { $('#doc-title').text('Error: ' + r.error); return; }
        var d = r.doc;
        $('#doc-title').html('<i class="fa ' + r.icon + '"></i> ' + r.label + ' <small style="color:#7f8c8d">#' + d.ref_number + '</small>');
        $('#doc-status-badge').html('<span class="badge badge-' + d.status + '" style="font-size:12px;padding:4px 10px">' + d.status_label + '</span>');

        // Info grid
        var grid = '';
        $.each(r.info_fields, function(i, f) {
            grid += '<div class="col-md-3 col-sm-6" style="margin-bottom:10px"><div style="font-size:11px;color:#95a5a6;font-weight:600;text-transform:uppercase">' + f.label + '</div><div style="font-size:13px;color:#2c3e50;margin-top:2px">' + (f.value || '—') + '</div></div>';
        });
        $('#doc-info-grid').html(grid);

        // Items
        var head = '<tr style="background:#f8f9fa">';
        $.each(r.item_cols, function(i, c) { head += '<th style="padding:8px 12px;font-size:11px;color:#7f8c8d;font-weight:600">' + c + '</th>'; });
        head += '</tr>';
        $('#doc-items-head').html(head);

        if (r.items && r.items.length) {
            var tbody = '';
            $.each(r.items, function(i, row) {
                tbody += '<tr>';
                $.each(r.item_keys, function(j, k) { tbody += '<td style="padding:8px 12px">' + (row[k] !== undefined ? row[k] : '—') + '</td>'; });
                tbody += '</tr>';
            });
            $('#doc-items-body').html(tbody);
        } else {
            $('#doc-items-body').html('<tr><td colspan="' + r.item_cols.length + '" class="text-center" style="padding:20px;color:#95a5a6">No items.</td></tr>');
        }

        // Notes
        if (d.notes) { $('#doc-notes').text(d.notes); $('#doc-notes-section').removeClass('hidden'); }

        // Action buttons
        var actions = '';
        if (d.status === 'draft') {
            actions += '<button class="btn btn-sm btn-success" onclick="docConfirm()"><i class="fa fa-check"></i> Confirm</button> ';
            actions += '<a href="<?php echo admin_url('pos_system/inv_form/'); ?>' + DOC_TYPE + '/' + DOC_ID + '" class="btn btn-sm btn-default"><i class="fa fa-edit"></i> Edit</a> ';
        }
        actions += '<button class="btn btn-sm btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button>';
        $('#doc-actions').html(actions);
    });
});

function docConfirm() {
    if (!confirm('Confirm this document? Stock will be updated.')) return;
    var d = {}; d[_csrf_n] = _csrf_v; d.type = DOC_TYPE; d.id = DOC_ID; d.action = 'confirm';
    $.post('<?php echo admin_url('pos_system/inv_action'); ?>', d, function(r) {
        if (r.success) { alert_float('success', r.message || 'Confirmed.'); setTimeout(function(){ location.reload(); }, 800); }
        else alert_float('danger', r.error || 'Action failed.');
    }, 'json');
}
</script>
