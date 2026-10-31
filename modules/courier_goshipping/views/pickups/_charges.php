<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Editable "Pickup Charges" block — shared by the standalone Create Pickup
 * page and the optional pickup section of Create Shipment. Staff add as many
 * custom lines as they need (description + amount); the total updates live.
 * Posted as pickup_charge_desc[] / pickup_charge_amount[]; when the form is
 * re-displayed after a validation error the posted lines are rendered back,
 * so nothing typed is lost.
 */
$_pc_CI    = &get_instance();
$_pc_desc  = $_pc_CI->input->post('pickup_charge_desc');
$_pc_amt   = $_pc_CI->input->post('pickup_charge_amount');
$_pc_rows  = [];
if (is_array($_pc_desc) || is_array($_pc_amt)) {
    $_pc_desc = is_array($_pc_desc) ? array_values($_pc_desc) : [];
    $_pc_amt  = is_array($_pc_amt) ? array_values($_pc_amt) : [];
    for ($_i = 0, $_n = max(count($_pc_desc), count($_pc_amt)); $_i < $_n; $_i++) {
        $_pc_rows[] = ['d' => (string) ($_pc_desc[$_i] ?? ''), 'a' => (string) ($_pc_amt[$_i] ?? '')];
    }
}
if (empty($_pc_rows)) {
    $_pc_rows[] = ['d' => '', 'a' => ''];
}
$_pc_base_cur = function_exists('get_base_currency') ? get_base_currency() : null;
$_pc_cur      = $_pc_base_cur ? $_pc_base_cur->name : '';
$_pc_presets  = ['Pickup fee', 'Fuel surcharge', 'Loading / handling', 'Packaging', 'Waiting time', 'Toll / parking'];
?>
<style>
    .cgs-pkc { margin-top: 10px; }
    .cgs-pkc-table { width: 100%; border-collapse: collapse; }
    .cgs-pkc-table th { font-size: 12px; font-weight: 700; color: #555; padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; }
    .cgs-pkc-table td { padding: 6px 8px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
    .cgs-pkc-table input { width: 100%; }
    .cgs-pkc-amount { text-align: right; }
    .cgs-pkc-remove { background: none; border: 0; color: #c0392b; cursor: pointer; font-size: 16px; padding: 4px 8px; }
    .cgs-pkc-add { margin-top: 10px; background: #fff; border: 1px dashed #1565c0; color: #1565c0; border-radius: 6px; padding: 7px 14px; font-weight: 600; cursor: pointer; }
    .cgs-pkc-add:hover { background: #f1f6fd; }
    .cgs-pkc-total { text-align: right; font-weight: 700; font-size: 14px; padding: 10px 8px 0; }
</style>
<div class="row section-container cgs-pkc" id="cgs-pkc">
    <div class="section-label">Pickup Charges
        <span style="font-weight:normal; font-size:11px; color:#777; margin-left:6px;">Optional &mdash; add any custom charge and edit the amounts as needed</span>
    </div>
    <div class="col-md-12" style="padding:0 20px 10px;">
        <datalist id="cgs-pkc-presets">
            <?php foreach ($_pc_presets as $_p): ?><option value="<?php echo htmlspecialchars($_p); ?>"><?php endforeach; ?>
        </datalist>
        <table class="cgs-pkc-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width:160px; text-align:right;">Amount<?php echo $_pc_cur ? ' (' . htmlspecialchars($_pc_cur) . ')' : ''; ?></th>
                    <th style="width:44px;"></th>
                </tr>
            </thead>
            <tbody id="cgs-pkc-body">
                <?php foreach ($_pc_rows as $_r): ?>
                    <tr class="cgs-pkc-row">
                        <td><input type="text" name="pickup_charge_desc[]" class="custom-input" list="cgs-pkc-presets"
                                   maxlength="120" placeholder="e.g. Pickup fee" autocomplete="off"
                                   value="<?php echo htmlspecialchars($_r['d']); ?>"></td>
                        <td><input type="number" name="pickup_charge_amount[]" class="custom-input cgs-pkc-amount"
                                   step="any" min="0" placeholder="0.00"
                                   value="<?php echo htmlspecialchars($_r['a']); ?>"></td>
                        <td><button type="button" class="cgs-pkc-remove" title="Remove charge"><i class="fa fa-trash"></i></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php echo form_error('pickup_charge_amount[]', '<div class="cgs-field-error-msg error-message">', '</div>'); ?>
        <button type="button" class="cgs-pkc-add" id="cgs-pkc-add"><i class="fa fa-plus"></i> Add charge</button>
        <div class="cgs-pkc-total">Total pickup charges: <?php echo htmlspecialchars($_pc_cur); ?> <span id="cgs-pkc-total">0.00</span></div>
    </div>
</div>
<script>
(function () {
    var body = document.getElementById('cgs-pkc-body');
    if (!body) { return; }

    function recalc() {
        var total = 0;
        body.querySelectorAll('.cgs-pkc-amount').forEach(function (el) {
            var v = parseFloat(el.value);
            if (!isNaN(v) && v > 0) { total += v; }
        });
        document.getElementById('cgs-pkc-total').textContent = total.toFixed(2);
    }

    document.getElementById('cgs-pkc-add').addEventListener('click', function () {
        var first = body.querySelector('.cgs-pkc-row');
        var row = first.cloneNode(true);
        row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        body.appendChild(row);
        row.querySelector('input').focus();
    });

    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.cgs-pkc-remove');
        if (!btn) { return; }
        var rows = body.querySelectorAll('.cgs-pkc-row');
        var row = btn.closest('.cgs-pkc-row');
        if (rows.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        }
        recalc();
    });

    body.addEventListener('input', recalc);
    recalc();
})();
</script>
