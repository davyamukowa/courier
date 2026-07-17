<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.pt-line-row td { padding: 5px 8px; vertical-align: middle; }
.pt-line-table { width:100%; font-size:13px; }
.pt-badge { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:10px; }
.pt-badge-active { background:#d1fae5; color:#065f46; }
.pt-badge-inactive { background:#fee2e2; color:#991b1b; }
.pt-card { border:1px solid #e5e7eb; border-radius:8px; margin-bottom:12px; overflow:hidden; }
.pt-card-header { background:#f8fafc; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e5e7eb; cursor:pointer; }
.pt-card-header:hover { background:#f0f9ff; }
.pt-card-body { padding:0; display:none; }
.pt-card-body.open { display:block; }
.delay-type-label { font-size:11px; color:#6b7280; }
.add-line-btn { border:1px dashed #d1d5db; background:#f9fafb; width:100%; padding:6px; font-size:12px; color:#374151; cursor:pointer; border-radius:4px; margin-top:6px; }
.add-line-btn:hover { background:#f0f9ff; border-color:#3b82f6; color:#2563eb; }
</style>

<div class="xb-workspace">
    <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Accounting</a> &rsaquo;
        <a href="<?php echo admin_url('xetuu_books/config/settings'); ?>">Configuration</a> &rsaquo; Payment Terms
    </div>

    <div class="xb-header-toolbar">
        <div>
            <h3 style="margin:0;">Payment Terms</h3>
            <p style="margin:4px 0 0;font-size:12px;color:#6b7280;">Define when invoices are due — immediately, net 30, 50% upfront, etc.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary xb-btn-primary btn-sm"
                    onclick="openPtModal(null)">
                <i class="fa fa-plus"></i> New Payment Term
            </button>
        </div>
    </div>

    <div class="alert alert-info" style="margin-bottom:16px;">
        <i class="fa fa-info-circle"></i>
        Payment terms control invoice due dates and partial payment schedules.
        Each term can have one or more lines (e.g., 30% due in 15 days, balance due in 60 days).
        <strong>The last line must always be "Balance".</strong>
    </div>

    <?php if (empty($payment_terms)): ?>
    <div class="xb-card">
        <div class="xb-card-body text-center" style="padding:40px;">
            <i class="fa fa-calendar-o" style="font-size:40px;color:#d1d5db;margin-bottom:12px;display:block;"></i>
            <p style="color:#6b7280;">No payment terms configured. Click <strong>New Payment Term</strong> to add one.</p>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($payment_terms as $pt): ?>
    <div class="pt-card" id="pt-card-<?php echo $pt->id; ?>">
        <div class="pt-card-header" onclick="togglePt(<?php echo $pt->id; ?>)">
            <div style="display:flex;align-items:center;gap:12px;">
                <i class="fa fa-chevron-right" id="pt-chev-<?php echo $pt->id; ?>" style="font-size:11px;color:#9ca3af;transition:transform .2s;"></i>
                <div>
                    <strong style="font-size:14px;"><?php echo htmlspecialchars($pt->name); ?></strong>
                    <?php if (!empty($pt->note)): ?>
                    <span style="font-size:12px;color:#6b7280;margin-left:8px;"><?php echo htmlspecialchars($pt->note); ?></span>
                    <?php endif; ?>
                </div>
                <span class="pt-badge <?php echo $pt->active ? 'pt-badge-active' : 'pt-badge-inactive'; ?>">
                    <?php echo $pt->active ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div style="display:flex;gap:8px;" onclick="event.stopPropagation()">
                <button class="btn btn-default btn-xs" onclick="openPtModal(<?php echo $pt->id; ?>)">
                    <i class="fa fa-pencil"></i> Edit
                </button>
                <button class="btn btn-danger btn-xs" onclick="deletePt(<?php echo $pt->id; ?>, '<?php echo htmlspecialchars(addslashes($pt->name)); ?>')">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </div>
        <div class="pt-card-body" id="pt-body-<?php echo $pt->id; ?>">
            <?php if (empty($pt->lines)): ?>
            <p style="padding:12px 16px;color:#9ca3af;font-size:12px;font-style:italic;">No payment lines — click Edit to add schedule.</p>
            <?php else: ?>
            <table class="pt-line-table" style="border-collapse:collapse;">
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th style="padding:7px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Type</th>
                        <th style="padding:7px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;text-align:right;">Value</th>
                        <th style="padding:7px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Due Date Computation</th>
                        <th style="padding:7px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;text-align:right;">Days</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pt->lines as $line):
                    $delay_labels = [
                        'days_after'                   => 'days after invoice date',
                        'days_after_end_of_month'      => 'days after end of month',
                        'days_after_end_of_next_month' => 'days after end of next month',
                        'days_end_of_month_on_the'     => 'days, end of month on the',
                    ];
                    $value_labels = [
                        'balance' => ['text' => 'Balance', 'color' => '#065f46', 'bg' => '#d1fae5'],
                        'percent' => ['text' => 'Percent', 'color' => '#1e40af', 'bg' => '#dbeafe'],
                        'fixed'   => ['text' => 'Fixed Amount', 'color' => '#92400e', 'bg' => '#fef3c7'],
                    ];
                    $vl = $value_labels[$line->value] ?? $value_labels['balance'];
                ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:8px 16px;">
                        <span style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:600;background:<?php echo $vl['bg']; ?>;color:<?php echo $vl['color']; ?>;">
                            <?php echo $vl['text']; ?>
                        </span>
                    </td>
                    <td style="padding:8px 16px;text-align:right;font-family:'Courier New',monospace;">
                        <?php if ($line->value === 'balance'): ?>
                        <span style="color:#9ca3af;">—</span>
                        <?php elseif ($line->value === 'percent'): ?>
                        <?php echo number_format($line->value_amount, 2); ?>%
                        <?php else: ?>
                        <?php echo number_format($line->value_amount, 2); ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 16px;font-size:12px;color:#374151;">
                        <?php echo $delay_labels[$line->delay_type] ?? $line->delay_type; ?>
                    </td>
                    <td style="padding:8px 16px;text-align:right;font-weight:600;">
                        <?php echo (int)$line->nb_days; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Payment Term Modal -->
<div class="modal fade" id="modal-pt" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a6b3a;color:#fff;border-radius:0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title" id="pt-modal-title">New Payment Term</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pt-id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Payment Term Name</strong> <span class="text-danger">*</span></label>
                            <input type="text" id="pt-name" class="form-control" placeholder="e.g., Net 30 Days">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Active</label>
                            <select id="pt-active" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sequence</label>
                            <input type="number" id="pt-seq" class="form-control" value="10" min="1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Note / Description</label>
                    <input type="text" id="pt-note" class="form-control" placeholder="Optional description shown on invoices">
                </div>

                <hr style="margin:16px 0 12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <label style="margin:0;font-weight:700;font-size:13px;">Payment Lines
                        <span style="font-size:11px;font-weight:400;color:#6b7280;">(define the payment schedule)</span>
                    </label>
                    <button type="button" class="btn btn-default btn-sm" onclick="addPtLine()">
                        <i class="fa fa-plus"></i> Add Line
                    </button>
                </div>
                <table class="table table-bordered" id="pt-lines-table" style="font-size:13px;margin-bottom:6px;">
                    <thead style="background:#f3f4f6;">
                        <tr>
                            <th style="width:130px;">Type</th>
                            <th style="width:110px;">Amount/Pct</th>
                            <th>Due Date Computation</th>
                            <th style="width:70px;">Days</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="pt-lines-body"></tbody>
                </table>
                <div style="font-size:11px;color:#6b7280;background:#f8fafc;padding:8px 12px;border-radius:4px;border-left:3px solid #3b82f6;">
                    <i class="fa fa-lightbulb-o"></i>
                    <strong>Rules:</strong> Last line must be "Balance". For percent split, ensure total = 100%.
                    Example: <em>30% due in 15 days + Balance due in 60 days</em>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary xb-btn-primary" onclick="savePt()">
                    <i class="fa fa-save"></i> Save Payment Term
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var ptData = <?php echo json_encode($payment_terms); ?>;

function togglePt(id) {
    var body = document.getElementById('pt-body-' + id);
    var chev = document.getElementById('pt-chev-' + id);
    var open = body.classList.contains('open');
    body.classList.toggle('open', !open);
    chev.style.transform = open ? '' : 'rotate(90deg)';
}

function openPtModal(id) {
    // Reset modal
    document.getElementById('pt-id').value = '';
    document.getElementById('pt-name').value = '';
    document.getElementById('pt-note').value = '';
    document.getElementById('pt-active').value = '1';
    document.getElementById('pt-seq').value = '10';
    document.getElementById('pt-lines-body').innerHTML = '';
    document.getElementById('pt-modal-title').textContent = 'New Payment Term';

    if (id) {
        var pt = ptData.find(function(p){ return p.id == id; });
        if (pt) {
            document.getElementById('pt-id').value = pt.id;
            document.getElementById('pt-name').value = pt.name;
            document.getElementById('pt-note').value = pt.note || '';
            document.getElementById('pt-active').value = pt.active;
            document.getElementById('pt-seq').value = pt.sequence;
            document.getElementById('pt-modal-title').textContent = 'Edit: ' + pt.name;
            if (pt.lines && pt.lines.length) {
                pt.lines.forEach(function(line) { addPtLine(line); });
            }
        }
    } else {
        // Default: one balance line
        addPtLine({value: 'balance', value_amount: 0, delay_type: 'days_after', nb_days: 0});
    }
    $('#modal-pt').modal('show');
}

function addPtLine(line) {
    line = line || {value:'balance', value_amount:0, delay_type:'days_after', nb_days:0};
    var tbody = document.getElementById('pt-lines-body');
    var tr = document.createElement('tr');
    tr.innerHTML = [
        '<td><select class="form-control input-sm pt-line-type" onchange="updateLineAmtVisibility(this)">',
        '<option value="balance"' + (line.value=='balance'?' selected':'') + '>Balance</option>',
        '<option value="percent"' + (line.value=='percent'?' selected':'') + '>Percent (%)</option>',
        '<option value="fixed"' + (line.value=='fixed'?' selected':'') + '>Fixed Amount</option>',
        '</select></td>',
        '<td><input type="number" class="form-control input-sm pt-line-amt" value="' + (parseFloat(line.value_amount)||0) + '" step="0.01" min="0"' + (line.value=='balance'?' disabled style="background:#f3f4f6;"':'') + '></td>',
        '<td><select class="form-control input-sm pt-line-delay">',
        '<option value="days_after"' + (line.delay_type=='days_after'?' selected':'') + '>Days after invoice date</option>',
        '<option value="days_after_end_of_month"' + (line.delay_type=='days_after_end_of_month'?' selected':'') + '>Days after end of month</option>',
        '<option value="days_after_end_of_next_month"' + (line.delay_type=='days_after_end_of_next_month'?' selected':'') + '>Days after end of next month</option>',
        '<option value="days_end_of_month_on_the"' + (line.delay_type=='days_end_of_month_on_the'?' selected':'') + '>End of month on the day</option>',
        '</select></td>',
        '<td><input type="number" class="form-control input-sm pt-line-days" value="' + (parseInt(line.nb_days)||0) + '" min="0"></td>',
        '<td style="text-align:center;"><button type="button" class="btn btn-danger btn-xs" onclick="this.closest(\'tr\').remove()" title="Remove line"><i class="fa fa-times"></i></button></td>',
    ].join('');
    tbody.appendChild(tr);
}

function updateLineAmtVisibility(sel) {
    var amtInput = sel.closest('tr').querySelector('.pt-line-amt');
    if (sel.value === 'balance') {
        amtInput.disabled = true;
        amtInput.style.background = '#f3f4f6';
        amtInput.value = 0;
    } else {
        amtInput.disabled = false;
        amtInput.style.background = '';
    }
}

function savePt() {
    var name = document.getElementById('pt-name').value.trim();
    if (!name) { alert('Payment term name is required.'); return; }

    var lines = [];
    document.querySelectorAll('#pt-lines-body tr').forEach(function(tr) {
        lines.push({
            value:        tr.querySelector('.pt-line-type').value,
            value_amount: tr.querySelector('.pt-line-amt').value,
            delay_type:   tr.querySelector('.pt-line-delay').value,
            nb_days:      tr.querySelector('.pt-line-days').value,
        });
    });

    // Validate: last line should be balance
    if (lines.length && lines[lines.length - 1].value !== 'balance') {
        if (!confirm('The last line should be "Balance" to cover remaining amount. Continue anyway?')) return;
    }

    var post = {
        id:       document.getElementById('pt-id').value,
        name:     name,
        note:     document.getElementById('pt-note').value,
        active:   document.getElementById('pt-active').value,
        sequence: document.getElementById('pt-seq').value,
        lines:    lines,
    };

    $.ajax({
        url: '<?php echo admin_url('xetuu_books/ajax/save_payment_term'); ?>',
        method: 'POST',
        data: post,
        success: function(res) {
            try { var r = JSON.parse(res); }
            catch(e) { var r = res; }
            if (r.success) { location.reload(); }
            else { alert(r.message || 'Error saving payment term.'); }
        },
        error: function() { alert('Server error. Please try again.'); }
    });
}

function deletePt(id, name) {
    if (!confirm('Delete payment term "' + name + '"? This cannot be undone.')) return;
    $.post('<?php echo admin_url('xetuu_books/ajax/delete_payment_term'); ?>', {id: id}, function(res) {
        try { var r = JSON.parse(res); } catch(e) { var r = {success: false}; }
        if (r.success) {
            var el = document.getElementById('pt-card-' + id);
            if (el) el.remove();
        } else { alert('Could not delete — this term may be in use.'); }
    });
}
</script>
