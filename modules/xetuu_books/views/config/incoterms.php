<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.inc-code-badge { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:8px; background:#1e3a5f; color:#fff; font-size:13px; font-weight:700; font-family:'Courier New',monospace; flex-shrink:0; }
.inc-card { display:flex; align-items:flex-start; gap:14px; padding:14px 16px; border-bottom:1px solid #f3f4f6; transition:background .1s; }
.inc-card:last-child { border-bottom:none; }
.inc-card:hover { background:#f8fafc; }
.inc-desc { font-size:12px; color:#6b7280; margin-top:3px; line-height:1.5; }
.inc-inactive .inc-code-badge { background:#9ca3af; }
.inc-inactive .inc-name { color:#9ca3af; text-decoration:line-through; }
</style>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Configuration</a> &rsaquo; Incoterms
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3 style="margin:0;">Incoterms</h3>
            <p style="margin:4px 0 0;font-size:12px;color:#6b7280;">International Commercial Terms (Incoterms® 2020) define risk and cost transfer points in trade.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary xb-btn-primary btn-sm"
                    onclick="openIncModal(null)">
                <i class="fa fa-plus"></i> New Incoterm
            </button>
        </div>
    </div>

    <div class="alert alert-info" style="margin-bottom:16px;">
        <i class="fa fa-info-circle"></i>
        Incoterms specify where risk and responsibility transfer from seller to buyer in a shipment.
        These are used on <strong>Sales Orders</strong> and <strong>Vendor Bills</strong>.
        The standard Incoterms 2020 are pre-loaded below.
    </div>

    <div class="xb-card">
        <div class="xb-card-body" style="padding:0;">

            <!-- Group: Any Mode -->
            <div style="padding:10px 16px 6px;background:#f8fafc;border-bottom:2px solid #e5e7eb;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;">
                <i class="fa fa-globe" style="color:#3b82f6;margin-right:6px;"></i> Any Mode of Transport
                <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:8px;">(EXW, FCA, CPT, CIP, DAP, DPU, DDP)</span>
            </div>

            <?php
            $any_mode = ['EXW','FCA','CPT','CIP','DAP','DPU','DDP'];
            $sea_mode = ['FAS','FOB','CFR','CIF'];
            foreach ($incoterms as $inc):
                if (!in_array($inc->code, $any_mode)) continue;
            ?>
            <div class="inc-card <?php echo $inc->active ? '' : 'inc-inactive'; ?>" id="inc-row-<?php echo $inc->id; ?>">
                <div class="inc-code-badge"><?php echo htmlspecialchars($inc->code); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <strong class="inc-name"><?php echo htmlspecialchars($inc->name); ?></strong>
                        <?php if (!$inc->active): ?>
                        <span class="label label-default" style="font-size:10px;">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="inc-desc"><?php echo htmlspecialchars($inc->description); ?></div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button class="btn btn-default btn-xs" onclick="openIncModal(<?php echo $inc->id; ?>)" title="Edit">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button class="btn btn-<?php echo $inc->active ? 'warning' : 'success'; ?> btn-xs"
                            onclick="toggleIncActive(<?php echo $inc->id; ?>, <?php echo $inc->active ? 0 : 1; ?>)"
                            title="<?php echo $inc->active ? 'Deactivate' : 'Activate'; ?>">
                        <i class="fa fa-<?php echo $inc->active ? 'eye-slash' : 'eye'; ?>"></i>
                    </button>
                    <button class="btn btn-danger btn-xs" onclick="deleteInc(<?php echo $inc->id; ?>, '<?php echo htmlspecialchars(addslashes($inc->code)); ?>')" title="Delete">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Group: Sea Only -->
            <div style="padding:10px 16px 6px;background:#f8fafc;border-bottom:2px solid #e5e7eb;border-top:2px solid #e5e7eb;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;">
                <i class="fa fa-ship" style="color:#0369a1;margin-right:6px;"></i> Sea and Inland Waterway Only
                <span style="font-size:10px;font-weight:400;color:#9ca3af;margin-left:8px;">(FAS, FOB, CFR, CIF)</span>
            </div>

            <?php foreach ($incoterms as $inc):
                if (!in_array($inc->code, $sea_mode)) continue;
            ?>
            <div class="inc-card <?php echo $inc->active ? '' : 'inc-inactive'; ?>" id="inc-row-<?php echo $inc->id; ?>">
                <div class="inc-code-badge" style="background:#0369a1;"><?php echo htmlspecialchars($inc->code); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <strong class="inc-name"><?php echo htmlspecialchars($inc->name); ?></strong>
                        <?php if (!$inc->active): ?>
                        <span class="label label-default" style="font-size:10px;">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="inc-desc"><?php echo htmlspecialchars($inc->description); ?></div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button class="btn btn-default btn-xs" onclick="openIncModal(<?php echo $inc->id; ?>)" title="Edit">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button class="btn btn-<?php echo $inc->active ? 'warning' : 'success'; ?> btn-xs"
                            onclick="toggleIncActive(<?php echo $inc->id; ?>, <?php echo $inc->active ? 0 : 1; ?>)"
                            title="<?php echo $inc->active ? 'Deactivate' : 'Activate'; ?>">
                        <i class="fa fa-<?php echo $inc->active ? 'eye-slash' : 'eye'; ?>"></i>
                    </button>
                    <button class="btn btn-danger btn-xs" onclick="deleteInc(<?php echo $inc->id; ?>, '<?php echo htmlspecialchars(addslashes($inc->code)); ?>')" title="Delete">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Custom / Other -->
            <?php
            $standard = array_merge($any_mode, $sea_mode);
            $has_custom = false;
            foreach ($incoterms as $inc) { if (!in_array($inc->code, $standard)) { $has_custom = true; break; } }
            if ($has_custom):
            ?>
            <div style="padding:10px 16px 6px;background:#f8fafc;border-bottom:2px solid #e5e7eb;border-top:2px solid #e5e7eb;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;">
                <i class="fa fa-plus-circle" style="color:#6b7280;margin-right:6px;"></i> Custom Terms
            </div>
            <?php foreach ($incoterms as $inc):
                if (in_array($inc->code, $standard)) continue;
            ?>
            <div class="inc-card <?php echo $inc->active ? '' : 'inc-inactive'; ?>" id="inc-row-<?php echo $inc->id; ?>">
                <div class="inc-code-badge" style="background:#6b7280;"><?php echo htmlspecialchars($inc->code); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <strong class="inc-name"><?php echo htmlspecialchars($inc->name); ?></strong>
                    </div>
                    <div class="inc-desc"><?php echo htmlspecialchars($inc->description); ?></div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button class="btn btn-default btn-xs" onclick="openIncModal(<?php echo $inc->id; ?>)"><i class="fa fa-pencil"></i></button>
                    <button class="btn btn-danger btn-xs" onclick="deleteInc(<?php echo $inc->id; ?>, '<?php echo htmlspecialchars(addslashes($inc->code)); ?>')"><i class="fa fa-trash-o"></i></button>
                </div>
            </div>
            <?php endforeach; endif; ?>

        </div>
    </div>
</div>

<!-- Incoterm Modal -->
<div class="modal fade" id="modal-inc" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e3a5f;color:#fff;border-radius:0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title" id="inc-modal-title">New Incoterm</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inc-id">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Code</strong> <span class="text-danger">*</span></label>
                            <input type="text" id="inc-code" class="form-control" maxlength="5" placeholder="EXW" style="font-family:'Courier New',monospace;font-weight:700;font-size:15px;text-transform:uppercase;">
                            <p class="help-block" style="font-size:11px;">2–5 uppercase letters</p>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label><strong>Full Name</strong> <span class="text-danger">*</span></label>
                            <input type="text" id="inc-name" class="form-control" placeholder="e.g., Ex Works">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="inc-desc" class="form-control" rows="3" placeholder="Brief explanation of risk/cost transfer point..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" id="inc-order" class="form-control" value="10" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Active</label>
                            <select id="inc-active" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary xb-btn-primary" onclick="saveInc()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var incData = <?php echo json_encode($incoterms); ?>;

function openIncModal(id) {
    document.getElementById('inc-id').value   = '';
    document.getElementById('inc-code').value = '';
    document.getElementById('inc-name').value = '';
    document.getElementById('inc-desc').value = '';
    document.getElementById('inc-order').value = '10';
    document.getElementById('inc-active').value = '1';
    document.getElementById('inc-modal-title').textContent = 'New Incoterm';

    if (id) {
        var inc = incData.find(function(i){ return i.id == id; });
        if (inc) {
            document.getElementById('inc-id').value    = inc.id;
            document.getElementById('inc-code').value  = inc.code;
            document.getElementById('inc-name').value  = inc.name;
            document.getElementById('inc-desc').value  = inc.description || '';
            document.getElementById('inc-order').value = inc.sort_order;
            document.getElementById('inc-active').value = inc.active;
            document.getElementById('inc-modal-title').textContent = 'Edit: ' + inc.code + ' — ' + inc.name;
        }
    }
    $('#modal-inc').modal('show');
}

function saveInc() {
    var code = document.getElementById('inc-code').value.trim().toUpperCase();
    var name = document.getElementById('inc-name').value.trim();
    if (!code || !name) { alert('Code and Name are required.'); return; }

    $.post('<?php echo admin_url('xetuu_books/ajax/save_incoterm'); ?>', {
        id:          document.getElementById('inc-id').value,
        code:        code,
        name:        name,
        description: document.getElementById('inc-desc').value,
        sort_order:  document.getElementById('inc-order').value,
        active:      document.getElementById('inc-active').value,
    }, function(res) {
        try { var r = JSON.parse(res); } catch(e) { var r = {success:false}; }
        if (r.success) { location.reload(); }
        else { alert(r.message || 'Error saving.'); }
    });
}

function toggleIncActive(id, newVal) {
    $.post('<?php echo admin_url('xetuu_books/ajax/save_incoterm'); ?>', {
        id: id, active: newVal,
        // pass code+name so save_incoterm doesn't reject (it requires them)
        code: incData.find(function(i){ return i.id == id; }).code,
        name: incData.find(function(i){ return i.id == id; }).name,
    }, function() { location.reload(); });
}

function deleteInc(id, code) {
    if (!confirm('Delete incoterm "' + code + '"? This cannot be undone.')) return;
    $.post('<?php echo admin_url('xetuu_books/ajax/delete_incoterm'); ?>', {id: id}, function(res) {
        try { var r = JSON.parse(res); } catch(e) { var r = {success:false}; }
        if (r.success) {
            var el = document.getElementById('inc-row-' + id);
            if (el) el.remove();
        } else { alert('Could not delete.'); }
    });
}

// Auto-uppercase the code field
document.getElementById('inc-code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
